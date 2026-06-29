<?php
/**
 * XỬ LÝ ĐẶT HÀNG (AJAX endpoint)
 * Sử dụng session cart, lưu vào bảng hoadon + chitiethoadon
 */
session_start();
require '../connect.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResp($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    jsonResp(['success' => false, 'message' => 'Bạn cần đăng nhập trước khi đặt hàng.']);
}
$user_id     = (int)$_SESSION['user_id'];
$tenDangNhap = $_SESSION['user'] ?? '';

// 2. Kiểm tra request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResp(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
}

// 3. Kiểm tra giỏ hàng session
if (empty($_SESSION['cart'])) {
    jsonResp(['success' => false, 'message' => 'Giỏ hàng trống, không thể đặt hàng.']);
}

// 4. Lấy & validate dữ liệu
$hoTen       = trim($_POST['hoTen']       ?? '');
$soDienThoai = trim($_POST['soDienThoai'] ?? '');
$diaChi      = trim($_POST['diaChi']      ?? '');
$ghiChu      = trim($_POST['ghiChu']      ?? '');
$phuongThuc  = trim($_POST['phuongThuc']  ?? 'COD');
$couponId    = (int)($_POST['coupon_id']  ?? 0);
$couponCode  = strtoupper(trim($_POST['coupon_code'] ?? ''));
$clientDiscount = (float)($_POST['discount_amount'] ?? 0);

$errors = [];
if ($hoTen === '' || mb_strlen($hoTen) < 2)
    $errors[] = 'Vui lòng nhập họ tên người nhận (ít nhất 2 ký tự).';
if ($soDienThoai === '' || !preg_match('/^(0|\+84)[0-9]{9,10}$/', $soDienThoai))
    $errors[] = 'Số điện thoại không đúng định dạng.';
if ($diaChi === '' || mb_strlen($diaChi) < 10)
    $errors[] = 'Địa chỉ quá ngắn, vui lòng nhập đầy đủ.';
if (!in_array($phuongThuc, ['COD', 'BANK']))
    $phuongThuc = 'COD';

if (!empty($errors)) {
    jsonResp(['success' => false, 'message' => implode(' ', $errors)]);
}

// 5. Kiểm tra tồn kho cho từng item trong session cart
$cartItems = $_SESSION['cart'];
$tongTienGoc = 0;

foreach ($cartItems as $key => $item) {
    $maGia = (int)($item['ma_gia'] ?? 0);
    if ($maGia <= 0) continue;

    $stmt = $conn->prepare("SELECT SoLuong FROM giasanpham WHERE MaGia = ?");
    $stmt->bind_param('i', $maGia);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        jsonResp(['success' => false, 'message' => "Sản phẩm \"{$item['name']}\" không còn tồn tại."]);
    }
    if ((int)$row['SoLuong'] < (int)$item['quantity']) {
        jsonResp(['success' => false, 'message' => "Sản phẩm \"{$item['name']}\" chỉ còn {$row['SoLuong']} sp."]);
    }
    $tongTienGoc += $item['price'] * $item['quantity'];
}

// 5b. Xác thực coupon phía server (nếu có)
$discountAmount = 0;
$appliedCouponId = 0;

if ($couponId > 0 && $couponCode !== '') {
    $today = date('Y-m-d');
    $stmt = $conn->prepare(
        "SELECT * FROM coupons WHERE CouponID = ? AND Code = ? AND IsActive = 1
          AND StartDate <= ? AND EndDate >= ?"
    );
    $stmt->bind_param('isss', $couponId, $couponCode, $today, $today);
    $stmt->execute();
    $couponRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($couponRow && $tongTienGoc >= (float)$couponRow['MinOrderValue']) {
        if ($couponRow['DiscountType'] === 'percent') {
            $discountAmount = $tongTienGoc * ((float)$couponRow['DiscountValue'] / 100);
        } else {
            $discountAmount = (float)$couponRow['DiscountValue'];
        }
        $discountAmount = min($discountAmount, $tongTienGoc);
        $appliedCouponId = (int)$couponRow['CouponID'];
    }
}

$tongTien = $tongTienGoc - $discountAmount;

// 6. Lưu đơn hàng (Transaction)
$conn->begin_transaction();

try {
    // 6a. INSERT hoadon
    $trangThai = 'Chưa xác nhận';
    $ngayLap   = date('Y-m-d');
    $sql_hd = "INSERT INTO hoadon
               (TenDangNhap, HoTenNhan, SoDienThoaiNhan, DiaChiNhan, GhiChu, PhuongThucThanhToan, NgayLap, TongTien, TrangThai)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_hd);
    $stmt->bind_param('sssssssds',
        $tenDangNhap, $hoTen, $soDienThoai, $diaChi, $ghiChu, $phuongThuc, $ngayLap, $tongTien, $trangThai
    );
    if (!$stmt->execute()) throw new Exception('Lỗi tạo đơn hàng: ' . $conn->error);
    $maHoaDon = $conn->insert_id;
    $stmt->close();

    // 6b. INSERT chitiethoadon
    $sql_ct = "INSERT INTO chitiethoadon (MaHoaDon, MaSanPham, TenMau, KichThuoc, SoLuong, ThanhTien) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_ct = $conn->prepare($sql_ct);

    // 6c. UPDATE tồn kho
    $sql_stock = "UPDATE giasanpham SET SoLuong = SoLuong - ? WHERE MaGia = ? AND SoLuong >= ?";
    $stmt_stock = $conn->prepare($sql_stock);

    foreach ($cartItems as $key => $item) {
        $maGia     = (int)($item['ma_gia'] ?? 0);
        $productId = (int)$item['product_id'];
        $color     = $item['color'] ?? '';
        $ram       = $item['ram']   ?? '';
        $qty       = (int)$item['quantity'];
        $thanh_tien = $item['price'] * $qty;

        $stmt_ct->bind_param('iissid', $maHoaDon, $productId, $color, $ram, $qty, $thanh_tien);
        if (!$stmt_ct->execute()) throw new Exception('Lỗi lưu chi tiết đơn: ' . $conn->error);

        if ($maGia > 0) {
            $stmt_stock->bind_param('iii', $qty, $maGia, $qty);
            if (!$stmt_stock->execute()) throw new Exception('Lỗi cập nhật tồn kho: ' . $conn->error);
            if ($stmt_stock->affected_rows === 0) throw new Exception("Sản phẩm \"{$item['name']}\" vừa hết hàng.");
        }
    }
    $stmt_ct->close();
    $stmt_stock->close();

    $conn->commit();

    // Xóa giỏ hàng session sau khi đặt thành công
    $_SESSION['cart'] = [];

    jsonResp(['success' => true, 'message' => 'Đặt hàng thành công!', 'MaHoaDon' => $maHoaDon]);

} catch (Exception $e) {
    $conn->rollback();
    jsonResp(['success' => false, 'message' => 'Đặt hàng thất bại: ' . $e->getMessage()]);
}
?>
