<?php
session_start();
require 'connect.php';

// 1. XỬ LÝ ĐĂNG NHẬP ADMIN
if (isset($_POST['admin_login'])) {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM admin_inf WHERE TenDangNhap = ? AND MatKhau = ?");
    $stmt->bind_param('ss', $user, $pass);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $admin = $res->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['MaAdmin'];
        $_SESSION['admin_user'] = $admin['TenDangNhap'];
        $_SESSION['admin_name'] = $admin['HoTen'];
        $_SESSION['admin_role'] = $admin['Role'] ?? 'SuperAdmin';
        header('Location: admin.php');
        exit;
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
    }
}

// 2. XỬ LÝ ĐĂNG XUẤT
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in'], $_SESSION['admin_user'], $_SESSION['admin_name']);
    header('Location: admin.php');
    exit;
}

// 3. NẾU CHƯA ĐĂNG NHẬP -> HIỂN THỊ FORM LOGIN
if (!isset($_SESSION['admin_logged_in'])) {
?>
    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <title>Đăng nhập Admin</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
        <style>
            body {
                background: #f8fafc;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .login-box {
                background: #fff;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
            }
        </style>
    </head>

    <body>
        <div class="login-box">
            <h3 class="text-center mb-4">Quản trị viên</h3>
            <?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" required value="admin">
                </div>
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required value="admin123">
                </div>
                <button type="submit" name="admin_login" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
        </div>
    </body>

    </html>
<?php
    exit;
}

function logAdminAction($conn, $action, $targetType = '', $targetId = '', $notes = '')
{
    $adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    $stmt = $conn->prepare("INSERT INTO admin_logs (MaAdmin, ActionType, TargetType, TargetID, Notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issss', $adminId, $action, $targetType, $targetId, $notes);
    $stmt->execute();
}

// 4. XỬ LÝ CÁC ACTION TỪ FORM AJAX/POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Cập nhật trạng thái đơn hàng
    if ($action === 'update_order_status') {
        $maHD = (int)$_POST['ma_hd'];
        $status = $_POST['status'];
        $oldStatus = '';
        $resOld = $conn->query("SELECT TrangThai FROM hoadon WHERE MaHoaDon = $maHD");
        if ($resOld && $rowOld = $resOld->fetch_assoc()) $oldStatus = $rowOld['TrangThai'];
        $stmt = $conn->prepare("UPDATE hoadon SET TrangThai = ? WHERE MaHoaDon = ?");
        $stmt->bind_param('si', $status, $maHD);
        if ($stmt->execute()) {
            $adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
            $stmtHist = $conn->prepare("INSERT INTO order_status_history (MaHoaDon, OldStatus, NewStatus, ChangedBy) VALUES (?, ?, ?, ?)");
            $stmtHist->bind_param('issi', $maHD, $oldStatus, $status, $adminId);
            $stmtHist->execute();
            logAdminAction($conn, 'update_order_status', 'order', $maHD, "Từ $oldStatus sang $status");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    // Xóa sản phẩm
    if ($action === 'delete_product') {
        $maSP = (int)$_POST['ma_sp'];
        // Tạm thời chỉ xóa sp trong bảng sanpham, các bảng con sẽ bị xóa do ràng buộc ON DELETE CASCADE (nếu có)
        // Lưu ý: Nếu DB chưa set ON DELETE CASCADE, có thể gặp lỗi ràng buộc khóa ngoại.
        // Để an toàn, xóa các bảng con trước:
        $conn->query("DELETE FROM image WHERE MaSanPham = $maSP");
        $conn->query("DELETE FROM video WHERE MaSanPham = $maSP");
        $conn->query("DELETE FROM giasanpham WHERE MaSanPham = $maSP");
        $conn->query("DELETE FROM chitiethoadon WHERE MaSanPham = $maSP");
        $conn->query("DELETE FROM colors WHERE MaSanPham = $maSP");
        $conn->query("DELETE FROM ram_rom_option WHERE MaSanPham = $maSP");
        $conn->query("DELETE FROM chitietsanpham WHERE MaSanPham = $maSP");

        if ($conn->query("DELETE FROM sanpham WHERE MaSanPham = $maSP")) {
            logAdminAction($conn, 'delete_product', 'product', $maSP, 'Xóa sản phẩm');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'save_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['category_desc'] ?? '');
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Tên danh mục không được để trống']);
            exit;
        }
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET TenDanhMuc = ?, MoTa = ? WHERE MaDanhMuc = ?");
            $stmt->bind_param('ssi', $name, $desc, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (TenDanhMuc, MoTa) VALUES (?, ?)");
            $stmt->bind_param('ss', $name, $desc);
        }
        if ($stmt->execute()) {
            logAdminAction($conn, 'save_category', 'category', $id > 0 ? $id : $conn->insert_id, $name);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($conn->query("DELETE FROM categories WHERE MaDanhMuc = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'save_coupon') {
        $id = (int)($_POST['coupon_id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $discountType = $_POST['discount_type'] ?? 'percent';
        $value = (float)($_POST['discount_value'] ?? 0);
        $minOrder = (float)($_POST['min_order_value'] ?? 0);
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? date('Y-m-d');
        $active = ($_POST['is_active'] ?? '0') === '1' ? 1 : 0;
        if ($code === '') {
            echo json_encode(['success' => false, 'message' => 'Mã giảm giá không được để trống']);
            exit;
        }
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE coupons SET Code = ?, Description = ?, DiscountType = ?, DiscountValue = ?, MinOrderValue = ?, StartDate = ?, EndDate = ?, IsActive = ? WHERE CouponID = ?");
            $stmt->bind_param('sssdssiii', $code, $description, $discountType, $value, $minOrder, $startDate, $endDate, $active, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO coupons (Code, Description, DiscountType, DiscountValue, MinOrderValue, StartDate, EndDate, IsActive) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssdsssi', $code, $description, $discountType, $value, $minOrder, $startDate, $endDate, $active);
        }
        if ($stmt->execute()) {
            logAdminAction($conn, $id > 0 ? 'update_coupon' : 'create_coupon', 'coupon', $id > 0 ? $id : $conn->insert_id, $code);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'toggle_coupon') {
        $id = (int)($_POST['coupon_id'] ?? 0);
        $active = ($_POST['active'] ?? '0') === '1' ? 1 : 0;
        if ($conn->query("UPDATE coupons SET IsActive = $active WHERE CouponID = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'save_supplier') {
        $id = (int)($_POST['supplier_id'] ?? 0);
        $name = trim($_POST['supplier_name'] ?? '');
        $email = trim($_POST['supplier_email'] ?? '');
        $phone = trim($_POST['supplier_phone'] ?? '');
        $address = trim($_POST['supplier_address'] ?? '');
        $notes = trim($_POST['supplier_notes'] ?? '');
        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Tên nhà cung cấp không được để trống']);
            exit;
        }
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE suppliers SET TenNCC = ?, Email = ?, DienThoai = ?, DiaChi = ?, GhiChu = ? WHERE MaNCC = ?");
            $stmt->bind_param('sssssi', $name, $email, $phone, $address, $notes, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO suppliers (TenNCC, Email, DienThoai, DiaChi, GhiChu) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $name, $email, $phone, $address, $notes);
        }
        if ($stmt->execute()) {
            logAdminAction($conn, $id > 0 ? 'update_supplier' : 'create_supplier', 'supplier', $id > 0 ? $id : $conn->insert_id, $name);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'save_purchase_order') {
        $supplier = (int)($_POST['po_supplier'] ?? 0);
        $product = (int)($_POST['po_product'] ?? 0);
        $quantity = (int)($_POST['po_quantity'] ?? 0);
        $cost = (float)($_POST['po_cost'] ?? 0);
        $date = $_POST['po_date'] ?? date('Y-m-d');
        $notes = trim($_POST['po_notes'] ?? '');
        if ($supplier <= 0 || $product <= 0 || $quantity <= 0 || $cost <= 0) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin phiếu nhập kho']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO purchase_orders (MaNCC, MaSanPham, SoLuongNhap, GiaNhap, NgayNhap, GhiChu) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iidiss', $supplier, $product, $quantity, $cost, $date, $notes);
        if ($stmt->execute()) {
            logAdminAction($conn, 'create_purchase_order', 'purchase_order', $conn->insert_id, "NCC:$supplier SP:$product Qty:$quantity");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'save_setting') {
        $key = trim($_POST['setting_key'] ?? '');
        $value = trim($_POST['setting_value'] ?? '');
        if ($key === '') {
            echo json_encode(['success' => false, 'message' => 'Khóa cấu hình trống']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO system_settings (SettingKey, SettingValue) VALUES (?, ?) ON DUPLICATE KEY UPDATE SettingValue = VALUES(SettingValue)");
        $stmt->bind_param('ss', $key, $value);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'toggle_feedback') {
        $id = (int)($_POST['feedback_id'] ?? 0);
        $visible = ($_POST['visible'] ?? '0') === '1' ? 1 : 0;
        if ($conn->query("UPDATE feedback SET is_visible = $visible WHERE FeedbackID = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'save_staff') {
        $id = (int)($_POST['staff_id'] ?? 0);
        $username = trim($_POST['staff_username'] ?? '');
        $password = trim($_POST['staff_password'] ?? '');
        $name = trim($_POST['staff_name'] ?? '');
        $email = trim($_POST['staff_email'] ?? '');
        $role = trim($_POST['staff_role'] ?? 'Admin');
        if ($username === '' || $name === '') {
            echo json_encode(['success' => false, 'message' => 'Tên đăng nhập và họ tên không được để trống']);
            exit;
        }
        if ($id > 0) {
            if ($password !== '') {
                $stmt = $conn->prepare("UPDATE admin_inf SET TenDangNhap = ?, MatKhau = ?, HoTen = ?, Email = ?, Role = ? WHERE MaAdmin = ?");
                $stmt->bind_param('sssssi', $username, $password, $name, $email, $role, $id);
            } else {
                $stmt = $conn->prepare("UPDATE admin_inf SET TenDangNhap = ?, HoTen = ?, Email = ?, Role = ? WHERE MaAdmin = ?");
                $stmt->bind_param('ssssi', $username, $name, $email, $role, $id);
            }
        } else {
            if ($password === '') $password = 'admin123';
            $stmt = $conn->prepare("INSERT INTO admin_inf (TenDangNhap, MatKhau, HoTen, Email, Role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('sssss', $username, $password, $name, $email, $role);
        }
        if ($stmt->execute()) {
            logAdminAction($conn, $id > 0 ? 'update_staff' : 'create_staff', 'staff', $id > 0 ? $id : $conn->insert_id, $username);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
        exit;
    }

    // Lấy tồn kho của 1 sản phẩm
    if ($action === 'get_stock') {
        $maSP = (int)$_POST['ma_sp'];
        $sql = "SELECT g.MaGia, r.KichThuoc as Ram, c.TenMau as Mau, g.SoLuong 
                FROM giasanpham g 
                LEFT JOIN ram_rom_option r ON g.MaRam = r.MaRam 
                LEFT JOIN colors c ON g.MaMau = c.MaMau 
                WHERE g.MaSanPham = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $maSP);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // Cập nhật tồn kho (mảng các MaGia => SoLuong)
    if ($action === 'update_stock_bulk') {
        $stocks = $_POST['stocks'] ?? [];
        foreach ($stocks as $maGia => $soLuong) {
            $maGia = (int)$maGia;
            $soLuong = (int)$soLuong;
            $conn->query("UPDATE giasanpham SET SoLuong = $soLuong WHERE MaGia = $maGia");
        }
        echo json_encode(['success' => true]);
        exit;
    }

    // Thêm/Sửa sản phẩm cơ bản (bảng sanpham + image)
    if ($action === 'save_product') {
        $maSP = (int)$_POST['ma_sp'];
        $tenSP = $_POST['ten_sp'];
        $hang = $_POST['hang'];
        $category = trim($_POST['category'] ?? 'Điện thoại');
        $imgUrl = trim($_POST['img_url'] ?? 'img/no-image.svg');
        if ($imgUrl === '') $imgUrl = 'img/no-image.svg';
        $ngayNhap = date('Y-m-d');

        $conn->begin_transaction();
        try {
            if ($maSP > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE sanpham SET TenSanPham = ?, Hang = ?, DanhMuc = ? WHERE MaSanPham = ?");
                $stmt->bind_param('sssi', $tenSP, $hang, $category, $maSP);
                $stmt->execute();

                // Update image (chỉ cập nhật ảnh đầu tiên hoặc chèn thêm nếu chưa có)
                $chkImg = $conn->query("SELECT MaHinhAnh FROM image WHERE MaSanPham = $maSP ORDER BY MaHinhAnh ASC LIMIT 1");
                if ($chkImg->num_rows > 0) {
                    $mha = $chkImg->fetch_assoc()['MaHinhAnh'];
                    $stmtImg = $conn->prepare("UPDATE image SET DiaChiAnh = ? WHERE MaHinhAnh = ?");
                    $stmtImg->bind_param('si', $imgUrl, $mha);
                    $stmtImg->execute();
                } else {
                    $stmtImg = $conn->prepare("INSERT INTO image (MaSanPham, DiaChiAnh) VALUES (?, ?)");
                    $stmtImg->bind_param('is', $maSP, $imgUrl);
                    $stmtImg->execute();
                }
                logAdminAction($conn, 'update_product', 'product', $maSP, "Tên:$tenSP Hãng:$hang DanhMuc:$category");
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO sanpham (TenSanPham, Hang, NgayNhap, DanhMuc) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('ssss', $tenSP, $hang, $ngayNhap, $category);
                $stmt->execute();
                $newMaSP = $conn->insert_id;

                // Tạo 1 bản ghi giá mặc định
                $conn->query("INSERT INTO giasanpham (MaSanPham, GiaCu, GiaMoi, SoLuong) VALUES ($newMaSP, 0, 0, 0)");

                // Tạo bản ghi ảnh
                $stmtImg = $conn->prepare("INSERT INTO image (MaSanPham, DiaChiAnh) VALUES (?, ?)");
                $stmtImg->bind_param('is', $newMaSP, $imgUrl);
                $stmtImg->execute();
                logAdminAction($conn, 'create_product', 'product', $newMaSP, "Tên:$tenSP Hãng:$hang DanhMuc:$category");
            }
            $conn->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// ==========================================
// LẤY DỮ LIỆU HIỂN THỊ
// ==========================================
// 1. Lấy đơn hàng và tính doanh thu
$orders = [];
$totalRevenue = 0;
$totalOrders = 0;
$deliveredOrders = 0;
$res = $conn->query("
    SELECT h.*, 
           (SELECT GROUP_CONCAT(CONCAT(sp.TenSanPham, ' (x', ct.SoLuong, ')') SEPARATOR '<br>') 
            FROM chitiethoadon ct 
            JOIN sanpham sp ON ct.MaSanPham = sp.MaSanPham 
            WHERE ct.MaHoaDon = h.MaHoaDon) as ChiTietSanPham
    FROM hoadon h 
    ORDER BY h.MaHoaDon DESC
");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $orders[] = $r;
        $totalOrders++;
        if ($r['TrangThai'] === 'Đã giao') {
            $totalRevenue += $r['TongTien'];
            $deliveredOrders++;
        }
    }
}

// 2. Lấy sản phẩm và ảnh đại diện
$products = [];
$res = $conn->query("
    SELECT sp.*, 
           (SELECT img.DiaChiAnh FROM image img WHERE img.MaSanPham = sp.MaSanPham ORDER BY img.MaHinhAnh ASC LIMIT 1) as DiaChiAnh 
    FROM sanpham sp 
    ORDER BY sp.MaSanPham DESC
");
if ($res) {
    while ($r = $res->fetch_assoc()) $products[] = $r;
}

// 2.1 Danh mục sản phẩm
$categories = [];
$res = $conn->query("SELECT * FROM categories ORDER BY TenDanhMuc ASC");
if ($res) while ($r = $res->fetch_assoc()) $categories[] = $r;

// 2.2 Danh sách coupon
$coupons = [];
$res = $conn->query("SELECT * FROM coupons ORDER BY CreatedAt DESC");
if ($res) while ($r = $res->fetch_assoc()) $coupons[] = $r;

// 2.3 Nhà cung cấp và phiếu nhập kho
$suppliers = [];
$res = $conn->query("SELECT * FROM suppliers ORDER BY TenNCC ASC");
if ($res) while ($r = $res->fetch_assoc()) $suppliers[] = $r;

$purchaseOrders = [];
$res = $conn->query(
    "SELECT po.*, s.TenNCC, sp.TenSanPham, sp.DanhMuc 
     FROM purchase_orders po 
     LEFT JOIN suppliers s ON po.MaNCC = s.MaNCC 
     LEFT JOIN sanpham sp ON po.MaSanPham = sp.MaSanPham 
     ORDER BY po.NgayNhap DESC LIMIT 25"
);
if ($res) while ($r = $res->fetch_assoc()) $purchaseOrders[] = $r;

// 2.4 Cấu hình hệ thống
$settings = [];
$res = $conn->query("SELECT SettingKey, SettingValue FROM system_settings");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $settings[$r['SettingKey']] = $r['SettingValue'];
    }
}

// 2.5 Phản hồi / đánh giá
$feedbackItems = [];
$res = $conn->query(
    "SELECT f.*, kh.HoTen AS CustomerName, sp.TenSanPham 
     FROM feedback f 
     LEFT JOIN khachhang kh ON f.MaKhachHang = kh.MaKhachHang 
     LEFT JOIN sanpham sp ON f.MaSanPham = sp.MaSanPham 
     ORDER BY f.Ngay DESC"
);
if ($res) while ($r = $res->fetch_assoc()) $feedbackItems[] = $r;

// 2.6 Lịch sử khách hàng
$customerList = [];
$res = $conn->query(
    "SELECT kh.*, IFNULL(SUM(h.TongTien), 0) AS TotalSpent, COUNT(h.MaHoaDon) AS TotalOrders, MAX(h.NgayLap) AS LastOrder 
     FROM khachhang kh 
     LEFT JOIN hoadon h ON kh.TenDangNhap = h.TenDangNhap 
     GROUP BY kh.MaKhachHang 
     ORDER BY TotalSpent DESC LIMIT 50"
);
if ($res) while ($r = $res->fetch_assoc()) $customerList[] = $r;

// 2.7 Cảnh báo tồn kho thấp
$lowStock = [];
$res = $conn->query(
    "SELECT g.MaGia, g.MaSanPham, g.SoLuong, sp.TenSanPham, sp.DanhMuc 
     FROM giasanpham g 
     JOIN sanpham sp ON g.MaSanPham = sp.MaSanPham 
     WHERE g.SoLuong <= 5 
     ORDER BY g.SoLuong ASC LIMIT 20"
);
if ($res) while ($r = $res->fetch_assoc()) $lowStock[] = $r;

// 2.8 Dữ liệu nhân viên/admin
$staffUsers = [];
$res = $conn->query("SELECT MaAdmin, TenDangNhap, HoTen, Email, Role FROM admin_inf ORDER BY MaAdmin ASC");
if ($res) while ($r = $res->fetch_assoc()) $staffUsers[] = $r;

// 2.9 Dữ liệu biểu đồ doanh thu 6 tháng
$revenueChart = [];
$res = $conn->query(
    "SELECT DATE_FORMAT(h.NgayLap, '%Y-%m') AS ChartMonth, SUM(h.TongTien) AS MonthRevenue 
     FROM hoadon h 
     WHERE h.TrangThai = 'Đã giao' AND h.NgayLap >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH) 
     GROUP BY ChartMonth 
     ORDER BY ChartMonth ASC"
);
if ($res) while ($r = $res->fetch_assoc()) $revenueChart[] = $r;


$adminRole = $_SESSION['admin_role'] ?? 'SuperAdmin';
$isSuperAdmin = $adminRole === 'SuperAdmin';

// 3. Thống kê sản phẩm bán chạy
$topProducts = ['month' => [], 'quarter' => [], 'year' => []];

// Tháng này
$res = $conn->query("
    SELECT p.TenSanPham, SUM(c.SoLuong) as TotalSold, SUM(c.ThanhTien) as TotalRev
    FROM chitiethoadon c
    JOIN hoadon h ON c.MaHoaDon = h.MaHoaDon
    JOIN sanpham p ON c.MaSanPham = p.MaSanPham
    WHERE h.TrangThai = 'Đã giao' AND MONTH(h.NgayLap) = MONTH(CURRENT_DATE()) AND YEAR(h.NgayLap) = YEAR(CURRENT_DATE())
    GROUP BY p.MaSanPham, p.TenSanPham
    ORDER BY TotalSold DESC LIMIT 5
");
if ($res) while ($r = $res->fetch_assoc()) $topProducts['month'][] = $r;

// Quý này
$res = $conn->query("
    SELECT p.TenSanPham, SUM(c.SoLuong) as TotalSold, SUM(c.ThanhTien) as TotalRev
    FROM chitiethoadon c
    JOIN hoadon h ON c.MaHoaDon = h.MaHoaDon
    JOIN sanpham p ON c.MaSanPham = p.MaSanPham
    WHERE h.TrangThai = 'Đã giao' AND QUARTER(h.NgayLap) = QUARTER(CURRENT_DATE()) AND YEAR(h.NgayLap) = YEAR(CURRENT_DATE())
    GROUP BY p.MaSanPham, p.TenSanPham
    ORDER BY TotalSold DESC LIMIT 5
");
if ($res) while ($r = $res->fetch_assoc()) $topProducts['quarter'][] = $r;

// Năm nay
$res = $conn->query("
    SELECT p.TenSanPham, SUM(c.SoLuong) as TotalSold, SUM(c.ThanhTien) as TotalRev
    FROM chitiethoadon c
    JOIN hoadon h ON c.MaHoaDon = h.MaHoaDon
    JOIN sanpham p ON c.MaSanPham = p.MaSanPham
    WHERE h.TrangThai = 'Đã giao' AND YEAR(h.NgayLap) = YEAR(CURRENT_DATE())
    GROUP BY p.MaSanPham, p.TenSanPham
    ORDER BY TotalSold DESC LIMIT 5
");
if ($res) while ($r = $res->fetch_assoc()) $topProducts['year'][] = $r;

// 4. Khách hàng VIP (xếp hạng mua nhiều)
$vipCustomers = [];
$res = $conn->query("
    SELECT kh.HoTen, kh.SoDienThoai, SUM(h.TongTien) as TotalSpent, COUNT(h.MaHoaDon) as TotalOrders
    FROM hoadon h
    JOIN khachhang kh ON h.TenDangNhap = kh.TenDangNhap
    WHERE h.TrangThai = 'Đã giao'
    GROUP BY kh.MaKhachHang, kh.HoTen, kh.SoDienThoai
    ORDER BY TotalSpent DESC LIMIT 10
");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $r['Tier'] = 'Thành viên';
        if ($r['TotalSpent'] >= 50000000) $r['Tier'] = 'Kim Cương';
        elseif ($r['TotalSpent'] >= 30000000) $r['Tier'] = 'Vàng';
        elseif ($r['TotalSpent'] >= 10000000) $r['Tier'] = 'Bạc';
        $vipCustomers[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản trị hệ thống - CellPhoneK</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: #f1f5f9;
            font-family: 'Inter', sans-serif;
        }

        .sidebar {
            width: 250px;
            background: #1e293b;
            color: #fff;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            padding: 20px 0;
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #38bdf8;
        }

        .nav-link {
            color: #cbd5e1;
            padding: 12px 24px;
            display: block;
            text-decoration: none;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #334155;
            color: #fff;
        }

        .nav-link i {
            width: 25px;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background: #f8fafc;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-mobile-screen-button"></i> CellPhoneK
        </div>
        <a href="#orders" class="nav-link active" data-bs-toggle="tab"><i class="fa-solid fa-cart-shopping"></i> Quản lý Đơn hàng</a>
        <a href="#products" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-box"></i> Quản lý Sản phẩm</a>
        <a href="#categories" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-list"></i> Quản lý Danh mục</a>
        <a href="#suppliers" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-truck-fast"></i> Kho & Nhà cung cấp</a>
        <a href="#coupons" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-ticket"></i> Khuyến mãi</a>
        <a href="#stats" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-chart-line"></i> Báo cáo thống kê</a>
        <a href="#crm" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-users"></i> Lịch sử khách hàng</a>
        <a href="#feedback" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-comments"></i> Đánh giá</a>
        <?php if ($isSuperAdmin): ?>
            <a href="#staff" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-user-gear"></i> Quản lý Nhân viên</a>
            <a href="#settings" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-gear"></i> Cấu hình Website</a>
        <?php endif; ?>
        <a href="#vip" class="nav-link" data-bs-toggle="tab"><i class="fa-solid fa-crown"></i> Khách hàng VIP</a>
        <a href="?action=logout" class="nav-link" style="margin-top:auto;"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dashboard Quản trị</h2>
            <div>Xin chào, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></div>
        </div>

        <div class="tab-content">
            <!-- TAB QUẢN LÝ ĐƠN HÀNG -->
            <div class="tab-pane fade show active" id="orders">
                <!-- Thống kê doanh thu -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card p-3 bg-primary text-white">
                            <h5>Doanh thu thực tế (Đã giao)</h5>
                            <h3><?= number_format($totalRevenue, 0, ',', '.') ?>đ</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 bg-success text-white">
                            <h5>Tổng số đơn hàng</h5>
                            <h3><?= $totalOrders ?> đơn</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3 bg-info text-white">
                            <h5>Đơn đã hoàn thành</h5>
                            <h3><?= $deliveredOrders ?> đơn</h3>
                        </div>
                    </div>
                </div>

                <div class="card p-4">
                    <h4 class="mb-3">Danh sách đơn hàng</h4>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>SĐT</th>
                                    <th>Ngày đặt</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td>#DH-<?= $o['MaHoaDon'] ?></td>
                                        <td><?= htmlspecialchars($o['HoTenNhan']) ?></td>
                                        <td style="font-size:13px; max-width:220px; line-height:1.4;"><?= $o['ChiTietSanPham'] ?></td>
                                        <td><?= htmlspecialchars($o['SoDienThoaiNhan']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($o['NgayLap'])) ?></td>
                                        <td class="text-danger fw-bold"><?= number_format($o['TongTien'], 0, ',', '.') ?>đ</td>
                                        <td>
                                            <select class="form-select form-select-sm status-select" data-id="<?= $o['MaHoaDon'] ?>">
                                                <option value="Chưa xác nhận" <?= $o['TrangThai'] == 'Chưa xác nhận' ? 'selected' : '' ?>>Chưa xác nhận</option>
                                                <option value="Đã xác nhận" <?= $o['TrangThai'] == 'Đã xác nhận' ? 'selected' : '' ?>>Đã xác nhận</option>
                                                <option value="Đang đóng gói" <?= $o['TrangThai'] == 'Đang đóng gói' ? 'selected' : '' ?>>Đang đóng gói</option>
                                                <option value="Đang vận chuyển" <?= $o['TrangThai'] == 'Đang vận chuyển' ? 'selected' : '' ?>>Đang vận chuyển</option>
                                                <option value="Đã giao" <?= $o['TrangThai'] == 'Đã giao' ? 'selected' : '' ?>>Đã giao</option>
                                                <option value="Đã hủy" <?= $o['TrangThai'] == 'Đã hủy' ? 'selected' : '' ?>>Đã hủy</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-success btn-update-status" data-id="<?= $o['MaHoaDon'] ?>">Lưu</button>
                                            <a href="pages/print_bill.php?id=<?= $o['MaHoaDon'] ?>" target="_blank" class="btn btn-sm btn-secondary"><i class="fa-solid fa-print"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB QUẢN LÝ SẢN PHẨM -->
            <div class="tab-pane fade" id="products">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="m-0">Danh sách Sản phẩm</h4>
                        <button class="btn btn-primary" onclick="openProductModal()"><i class="fa-solid fa-plus"></i> Thêm mới</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Hãng</th>
                                    <th>Danh mục</th>
                                    <th>Ngày Nhập</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><?= $p['MaSanPham'] ?></td>
                                        <td><?= htmlspecialchars($p['TenSanPham']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['Hang']) ?></span></td>
                                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($p['DanhMuc'] ?: 'Điện thoại') ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($p['NgayNhap'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info text-white" onclick="openStockModal(<?= $p['MaSanPham'] ?>, '<?= htmlspecialchars($p['TenSanPham'], ENT_QUOTES) ?>')"><i class="fa-solid fa-boxes-stacked"></i> Kho</button>
                                            <button class="btn btn-sm btn-warning" onclick="openProductModal(<?= $p['MaSanPham'] ?>, '<?= htmlspecialchars($p['TenSanPham'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['Hang'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['DanhMuc'] ?? 'Điện thoại', ENT_QUOTES) ?>', '<?= htmlspecialchars($p['DiaChiAnh'] ?? '', ENT_QUOTES) ?>')"><i class="fa-solid fa-pen"></i></button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $p['MaSanPham'] ?>)"><i class="fa-solid fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB QUẢN LÝ DANH MỤC -->
            <div class="tab-pane fade" id="categories">
                <div class="row gy-4">
                    <div class="col-lg-5">
                        <div class="card p-4">
                            <h4 class="mb-3">Thêm / sửa danh mục</h4>
                            <input type="hidden" id="category_id" value="0">
                            <div class="mb-3">
                                <label>Tên danh mục</label>
                                <input type="text" id="category_name" class="form-control" placeholder="Ví dụ: iPhone, Samsung">
                            </div>
                            <div class="mb-3">
                                <label>Mô tả</label>
                                <textarea id="category_desc" class="form-control" rows="3" placeholder="Mô tả ngắn"></textarea>
                            </div>
                            <button class="btn btn-primary" onclick="saveCategory()"><i class="fa-solid fa-floppy-disk"></i> Lưu danh mục</button>
                            <button class="btn btn-secondary" type="button" onclick="resetCategoryForm()">Làm mới</button>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card p-4">
                            <h4 class="mb-3">Danh sách danh mục</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên danh mục</th>
                                            <th>Mô tả</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($categories)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Chưa có danh mục.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($categories as $cat): ?>
                                                <tr>
                                                    <td><?= $cat['MaDanhMuc'] ?></td>
                                                    <td><?= htmlspecialchars($cat['TenDanhMuc']) ?></td>
                                                    <td><?= htmlspecialchars($cat['MoTa']) ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" onclick="editCategory(<?= $cat['MaDanhMuc'] ?>, '<?= htmlspecialchars($cat['TenDanhMuc'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cat['MoTa'], ENT_QUOTES) ?>')"><i class="fa-solid fa-pen"></i></button>
                                                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $cat['MaDanhMuc'] ?>)"><i class="fa-solid fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB QUẢN LÝ KHO VÀ NHÀ CUNG CẤP -->
            <div class="tab-pane fade" id="suppliers">
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="card p-4 mb-4">
                            <h4 class="mb-3">Danh sách Nhà cung cấp</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên NCC</th>
                                            <th>Điện thoại</th>
                                            <th>Email</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($suppliers)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Chưa có nhà cung cấp.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($suppliers as $sup): ?>
                                                <tr>
                                                    <td><?= $sup['MaNCC'] ?></td>
                                                    <td><?= htmlspecialchars($sup['TenNCC']) ?></td>
                                                    <td><?= htmlspecialchars($sup['DienThoai']) ?></td>
                                                    <td><?= htmlspecialchars($sup['Email']) ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" onclick="editSupplier(<?= $sup['MaNCC'] ?>, '<?= htmlspecialchars($sup['TenNCC'], ENT_QUOTES) ?>', '<?= htmlspecialchars($sup['Email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($sup['DienThoai'], ENT_QUOTES) ?>', '<?= htmlspecialchars($sup['DiaChi'], ENT_QUOTES) ?>', '<?= htmlspecialchars($sup['GhiChu'], ENT_QUOTES) ?>')"><i class="fa-solid fa-pen"></i></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card p-4 mb-4">
                            <h4 class="mb-3">Thêm / sửa Nhà cung cấp</h4>
                            <input type="hidden" id="supplier_id" value="0">
                            <div class="mb-3">
                                <label>Tên nhà cung cấp</label>
                                <input type="text" id="supplier_name" class="form-control" placeholder="Tên nhà cung cấp">
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" id="supplier_email" class="form-control" placeholder="Email liên hệ">
                            </div>
                            <div class="mb-3">
                                <label>Điện thoại</label>
                                <input type="text" id="supplier_phone" class="form-control" placeholder="Số điện thoại">
                            </div>
                            <div class="mb-3">
                                <label>Địa chỉ</label>
                                <input type="text" id="supplier_address" class="form-control" placeholder="Địa chỉ">
                            </div>
                            <div class="mb-3">
                                <label>Ghi chú</label>
                                <textarea id="supplier_notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button class="btn btn-primary" onclick="saveSupplier()"><i class="fa-solid fa-floppy-disk"></i> Lưu nhà cung cấp</button>
                            <button class="btn btn-secondary" type="button" onclick="resetSupplierForm()">Làm mới</button>
                        </div>
                        <div class="card p-4">
                            <h4 class="mb-3">Tạo phiếu nhập kho</h4>
                            <form id="poForm" onsubmit="event.preventDefault(); savePurchaseOrder();">
                                <div class="mb-3">
                                    <label>Nhà cung cấp</label>
                                    <select id="po_supplier" class="form-select">
                                        <option value="0">Chọn nhà cung cấp</option>
                                        <?php foreach ($suppliers as $sup): ?>
                                            <option value="<?= $sup['MaNCC'] ?>"><?= htmlspecialchars($sup['TenNCC']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Sản phẩm</label>
                                    <select id="po_product" class="form-select">
                                        <option value="0">Chọn sản phẩm</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= $p['MaSanPham'] ?>"><?= htmlspecialchars($p['TenSanPham']) ?> (<?= htmlspecialchars($p['Hang']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="row gx-3">
                                    <div class="col-md-4 mb-3">
                                        <label>Số lượng</label>
                                        <input type="number" id="po_quantity" class="form-control" min="1" value="1">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Giá nhập</label>
                                        <input type="number" id="po_cost" class="form-control" min="0" step="0.01" value="0">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label>Ngày nhập</label>
                                        <input type="date" id="po_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label>Ghi chú</label>
                                    <textarea id="po_notes" class="form-control" rows="2"></textarea>
                                </div>
                                <button class="btn btn-primary">Lưu phiếu nhập</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card p-4">
                            <h4 class="mb-3">Phiếu nhập kho mới nhất</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Ngày</th>
                                            <th>Nhà cung cấp</th>
                                            <th>Sản phẩm</th>
                                            <th>SL nhập</th>
                                            <th>Giá nhập</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($purchaseOrders)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Chưa có phiếu nhập.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($purchaseOrders as $po): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y', strtotime($po['NgayNhap'])) ?></td>
                                                    <td><?= htmlspecialchars($po['TenNCC']) ?></td>
                                                    <td><?= htmlspecialchars($po['TenSanPham']) ?></td>
                                                    <td><?= $po['SoLuongNhap'] ?></td>
                                                    <td><?= number_format($po['GiaNhap'], 0, ',', '.') ?>đ</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB KHUYẾN MÃI -->
            <div class="tab-pane fade" id="coupons">
                <div class="row gy-4">
                    <div class="col-lg-5">
                        <div class="card p-4">
                            <h4 class="mb-3">Tạo / cập nhật mã giảm giá</h4>
                            <input type="hidden" id="coupon_id" value="0">
                            <div class="mb-3">
                                <label>Mã Coupon</label>
                                <input type="text" id="coupon_code" class="form-control" placeholder="VD: GIAM10">
                            </div>
                            <div class="mb-3">
                                <label>Mô tả</label>
                                <textarea id="coupon_description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="row gx-3">
                                <div class="col-md-6 mb-3">
                                    <label>Loại giảm giá</label>
                                    <select id="discount_type" class="form-select">
                                        <option value="percent">Phần trăm</option>
                                        <option value="fixed">Cố định</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Giá trị</label>
                                    <input type="number" id="discount_value" class="form-control" min="0" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="row gx-3">
                                <div class="col-md-6 mb-3">
                                    <label>Giá tối thiểu</label>
                                    <input type="number" id="min_order_value" class="form-control" min="0" step="0.01" value="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Hiệu lực từ</label>
                                    <input type="date" id="start_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label>Hết hạn</label>
                                <input type="date" id="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="coupon_active" checked>
                                <label class="form-check-label" for="coupon_active">Kích hoạt</label>
                            </div>
                            <button class="btn btn-primary" onclick="saveCoupon()"><i class="fa-solid fa-floppy-disk"></i> Lưu Coupon</button>
                            <button class="btn btn-secondary" type="button" onclick="resetCouponForm()">Làm mới</button>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card p-4">
                            <h4 class="mb-3">Danh sách Coupon</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Mã</th>
                                            <th>Giảm</th>
                                            <th>Trạng thái</th>
                                            <th>Thời gian</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($coupons)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có coupon.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($coupons as $cp): ?>
                                                <tr>
                                                    <td><?= $cp['CouponID'] ?></td>
                                                    <td><?= htmlspecialchars($cp['Code']) ?></td>
                                                    <td><?= $cp['DiscountType'] === 'percent' ? $cp['DiscountValue'] . '%' : number_format($cp['DiscountValue'], 0, ',', '.') . 'đ' ?></td>
                                                    <td><?= $cp['IsActive'] ? '<span class="badge bg-success">Đang hoạt động</span>' : '<span class="badge bg-secondary">Tạm dừng</span>' ?></td>
                                                    <td><?= date('d/m/Y', strtotime($cp['StartDate'])) . ' - ' . date('d/m/Y', strtotime($cp['EndDate'])) ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-warning" onclick="editCoupon(<?= $cp['CouponID'] ?>, '<?= htmlspecialchars($cp['Code'], ENT_QUOTES) ?>', '<?= htmlspecialchars($cp['Description'], ENT_QUOTES) ?>', '<?= $cp['DiscountType'] ?>', <?= $cp['DiscountValue'] ?>, <?= $cp['MinOrderValue'] ?>, '<?= $cp['StartDate'] ?>', '<?= $cp['EndDate'] ?>', <?= $cp['IsActive'] ?>)"><i class="fa-solid fa-pen"></i></button>
                                                        <button class="btn btn-sm btn-<?= $cp['IsActive'] ? 'secondary' : 'success' ?>" onclick="toggleCoupon(<?= $cp['CouponID'] ?>, <?= $cp['IsActive'] ? 0 : 1 ?>)"><i class="fa-solid fa-power-off"></i></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB LỊCH SỬ KHÁCH HÀNG -->
            <div class="tab-pane fade" id="crm">
                <div class="row gy-4">
                    <div class="col-lg-8">
                        <div class="card p-4">
                            <h4 class="mb-3">Lịch sử khách hàng</h4>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Khách hàng</th>
                                            <th>Điện thoại</th>
                                            <th>Đơn hàng</th>
                                            <th>Tổng chi</th>
                                            <th>Đơn gần nhất</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($customerList)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có dữ liệu khách hàng.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($customerList as $idx => $cust): ?>
                                                <tr>
                                                    <td><?= $idx + 1 ?></td>
                                                    <td><?= htmlspecialchars($cust['HoTen']) ?></td>
                                                    <td><?= htmlspecialchars($cust['SoDienThoai']) ?></td>
                                                    <td><?= $cust['TotalOrders'] ?></td>
                                                    <td class="text-danger fw-bold"><?= number_format($cust['TotalSpent'], 0, ',', '.') ?>đ</td>
                                                    <td><?= $cust['LastOrder'] ? date('d/m/Y', strtotime($cust['LastOrder'])) : 'Chưa có' ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card p-4 mb-4">
                            <h4 class="mb-3">Cảnh báo tồn kho thấp</h4>
                            <?php if (empty($lowStock)): ?>
                                <div class="alert alert-warning">Không có sản phẩm nào sắp hết hàng.</div>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($lowStock as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div><strong><?= htmlspecialchars($item['TenSanPham']) ?></strong><br><small><?= htmlspecialchars($item['DanhMuc']) ?></small></div>
                                            <span class="badge bg-danger"><?= $item['SoLuong'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card p-4">
                            <h4 class="mb-3">Thông tin nhanh</h4>
                            <p><strong>Số khách hàng VIP:</strong> <?= count($vipCustomers) ?></p>
                            <p><strong>Khách hàng đang hoạt động:</strong> <?= count($customerList) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB ĐÁNH GIÁ -->
            <div class="tab-pane fade" id="feedback">
                <div class="card p-4">
                    <h4 class="mb-3">Danh sách đánh giá / phản hồi</h4>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Sản phẩm</th>
                                    <th>Nội dung</th>
                                    <th>Ngày</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($feedbackItems)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">Chưa có phản hồi.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($feedbackItems as $fb): ?>
                                        <tr>
                                            <td><?= $fb['FeedbackID'] ?></td>
                                            <td><?= htmlspecialchars($fb['CustomerName']) ?></td>
                                            <td><?= htmlspecialchars($fb['TenSanPham']) ?></td>
                                            <td><?= htmlspecialchars($fb['NoiDung']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($fb['Ngay'])) ?></td>
                                            <td><?= $fb['is_visible'] ? '<span class="badge bg-success">Đã hiển thị</span>' : '<span class="badge bg-secondary">Đang ẩn</span>' ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-<?= $fb['is_visible'] ? 'secondary' : 'success' ?>" onclick="toggleFeedback(<?= $fb['FeedbackID'] ?>, <?= $fb['is_visible'] ? 0 : 1 ?>)"><i class="fa-solid fa-eye<?= $fb['is_visible'] ? '-slash' : '' ?>"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($isSuperAdmin): ?>
                <!-- TAB QUẢN LÝ NHÂN VIÊN -->
                <div class="tab-pane fade" id="staff">
                    <div class="row gy-4">
                        <div class="col-lg-5">
                            <div class="card p-4">
                                <h4 class="mb-3">Thêm / sửa tài khoản nhân viên</h4>
                                <input type="hidden" id="staff_id" value="0">
                                <div class="mb-3">
                                    <label>Tên đăng nhập</label>
                                    <input type="text" id="staff_username" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Mật khẩu</label>
                                    <input type="password" id="staff_password" class="form-control" placeholder="Để trống nếu không đổi">
                                </div>
                                <div class="mb-3">
                                    <label>Họ tên</label>
                                    <input type="text" id="staff_name" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Email</label>
                                    <input type="email" id="staff_email" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Phân quyền</label>
                                    <select id="staff_role" class="form-select">
                                        <option value="NhanVien">Nhân viên</option>
                                        <option value="QuanLy">Quản lý</option>
                                        <option value="Admin">Admin</option>
                                        <option value="SuperAdmin">SuperAdmin</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary" onclick="saveStaff()"><i class="fa-solid fa-floppy-disk"></i> Lưu nhân viên</button>
                                <button class="btn btn-secondary" type="button" onclick="resetStaffForm()">Làm mới</button>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card p-4">
                                <h4 class="mb-3">Danh sách nhân viên</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tài khoản</th>
                                                <th>Họ tên</th>
                                                <th>Email</th>
                                                <th>Phân quyền</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($staffUsers)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Chưa có tài khoản.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($staffUsers as $st): ?>
                                                    <tr>
                                                        <td><?= $st['MaAdmin'] ?></td>
                                                        <td><?= htmlspecialchars($st['TenDangNhap']) ?></td>
                                                        <td><?= htmlspecialchars($st['HoTen']) ?></td>
                                                        <td><?= htmlspecialchars($st['Email']) ?></td>
                                                        <td><?= htmlspecialchars($st['Role']) ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-warning" onclick="editStaff(<?= $st['MaAdmin'] ?>, '<?= htmlspecialchars($st['TenDangNhap'], ENT_QUOTES) ?>', '<?= htmlspecialchars($st['HoTen'], ENT_QUOTES) ?>', '<?= htmlspecialchars($st['Email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($st['Role'], ENT_QUOTES) ?>')"><i class="fa-solid fa-pen"></i></button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB CẤU HÌNH WEBSITE -->
                <div class="tab-pane fade" id="settings">
                    <div class="row gy-4">
                        <div class="col-lg-5">
                            <div class="card p-4">
                                <h4 class="mb-3">Cập nhật cấu hình</h4>
                                <div class="mb-3">
                                    <label>Khóa cấu hình</label>
                                    <input type="text" id="setting_key" class="form-control" placeholder="site_name">
                                </div>
                                <div class="mb-3">
                                    <label>Giá trị</label>
                                    <textarea id="setting_value" class="form-control" rows="3"></textarea>
                                </div>
                                <button class="btn btn-primary" onclick="saveSetting()"><i class="fa-solid fa-floppy-disk"></i> Lưu cấu hình</button>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="card p-4">
                                <h4 class="mb-3">Danh sách cấu hình</h4>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Khóa</th>
                                                <th>Giá trị</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($settings)): ?>
                                                <tr>
                                                    <td colspan="2" class="text-center">Chưa có cấu hình.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($settings as $key => $value): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($key) ?></td>
                                                        <td><?= htmlspecialchars($value) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB BÁO CÁO THỐNG KÊ -->
            <div class="tab-pane fade" id="stats">
                <div class="row gy-4">
                    <div class="col-lg-6">
                        <div class="card p-4">
                            <h4 class="mb-3">Biểu đồ doanh thu 6 tháng</h4>
                            <div class="mb-3">
                                <ul class="list-group">
                                    <?php if (empty($revenueChart)): ?>
                                        <li class="list-group-item">Chưa có doanh thu.</li>
                                    <?php else: ?>
                                        <?php foreach ($revenueChart as $row): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($row['ChartMonth']) ?></span>
                                                <span class="fw-semibold text-success"><?= number_format($row['MonthRevenue'], 0, ',', '.') ?>đ</span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="card p-4">
                            <h4 class="mb-3">Top sản phẩm bán chạy</h4>
                            <ul class="list-group">
                                <?php if (empty($topProducts['month'])): ?>
                                    <li class="list-group-item">Chưa có dữ liệu bán chạy.</li>
                                <?php else: ?>
                                    <?php foreach ($topProducts['month'] as $idx => $tp): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><?= $idx + 1 ?>. <?= htmlspecialchars($tp['TenSanPham']) ?></span>
                                            <span class="badge bg-primary"><?= $tp['TotalSold'] ?> bán</span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card p-4">
                            <h4 class="mb-3">Tổng quan</h4>
                            <p><strong>Doanh thu tổng:</strong> <?= number_format($totalRevenue, 0, ',', '.') ?>đ</p>
                            <p><strong>Số đơn đã giao:</strong> <?= $deliveredOrders ?></p>
                            <p><strong>Số đơn đặt:</strong> <?= $totalOrders ?></p>
                            <p><strong>Số lượng sản phẩm tồn kho thấp:</strong> <?= count($lowStock) ?></p>
                        </div>
                        <div class="card p-4">
                            <h4 class="mb-3">Sản phẩm tồn kho thấp</h4>
                            <?php if (empty($lowStock)): ?>
                                <div class="alert alert-success">Không có sản phẩm sắp hết kho.</div>
                            <?php else: ?>
                                <ul class="list-group">
                                    <?php foreach ($lowStock as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($item['TenSanPham']) ?></strong><br>
                                                <small><?= htmlspecialchars($item['DanhMuc']) ?></small>
                                            </div>
                                            <span class="badge bg-danger"><?= $item['SoLuong'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB KHÁCH HÀNG VIP -->
            <div class="tab-pane fade" id="vip">
                <div class="card p-4">
                    <h4 class="mb-3">Bảng xếp hạng Khách Hàng VIP</h4>
                    <div class="alert alert-info">
                        <strong>Hạng thành viên:</strong> Bạc (≥ 10Tr) | Vàng (≥ 30Tr) | Kim Cương (≥ 50Tr)
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Khách hàng</th>
                                    <th>Số điện thoại</th>
                                    <th>Số đơn hàng</th>
                                    <th>Tổng chi tiêu</th>
                                    <th>Hạng VIP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($vipCustomers)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Chưa có dữ liệu khách hàng.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($vipCustomers as $idx => $vip): ?>
                                        <tr>
                                            <td><strong><?= $idx + 1 ?></strong></td>
                                            <td><?= htmlspecialchars($vip['HoTen']) ?></td>
                                            <td><?= htmlspecialchars($vip['SoDienThoai']) ?></td>
                                            <td><?= $vip['TotalOrders'] ?></td>
                                            <td class="text-danger fw-bold"><?= number_format($vip['TotalSpent'], 0, ',', '.') ?>đ</td>
                                            <td>
                                                <?php if ($vip['Tier'] === 'Kim Cương'): ?>
                                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-gem"></i> Kim Cương</span>
                                                <?php elseif ($vip['Tier'] === 'Vàng'): ?>
                                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-star"></i> Vàng</span>
                                                <?php elseif ($vip['Tier'] === 'Bạc'): ?>
                                                    <span class="badge bg-secondary"><i class="fa-solid fa-medal"></i> Bạc</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark">Thành viên</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL THÊM/SỬA SẢN PHẨM -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pm_id" value="0">
                    <div class="mb-3">
                        <label>Tên Sản Phẩm</label>
                        <input type="text" id="pm_name" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Hãng (Brand)</label>
                        <input type="text" id="pm_brand" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Danh mục</label>
                        <select id="pm_category" class="form-select">
                            <?php if (empty($categories)): ?>
                                <option>Điện thoại</option>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['TenDanhMuc'], ENT_QUOTES) ?>"><?= htmlspecialchars($cat['TenDanhMuc']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Đường dẫn Ảnh (VD: img/XIAOMI/dt1.jpg)</label>
                        <input type="text" id="pm_img" class="form-control" placeholder="Để trống sẽ dùng ảnh mặc định">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary" onclick="saveProduct()">Lưu thông tin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL QUẢN LÝ KHO (SỐ LƯỢNG) -->
    <div class="modal fade" id="stockModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quản lý kho: <strong id="stockTitle"></strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Phiên bản (RAM/ROM)</th>
                                <th>Màu sắc</th>
                                <th style="width: 150px;">Số lượng kho</th>
                            </tr>
                        </thead>
                        <tbody id="stockBody">
                            <!-- Dữ liệu render bằng JS -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-success" onclick="saveStock()">Cập nhật Số Lượng</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- CHUYỂN TAB ---
        const triggerTabList = document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="tab"]')

        function switchTab(triggerEl) {
            // Remove active from all sidebar links
            document.querySelectorAll('.sidebar .nav-link').forEach(l => l.classList.remove('active'));
            triggerEl.classList.add('active');

            // Hide all panes
            document.querySelectorAll('.tab-pane').forEach(p => {
                p.classList.remove('show', 'active');
            });
            // Show target pane
            const href = triggerEl.getAttribute('href');
            const target = document.querySelector(href);
            if (target) target.classList.add('show', 'active');

            // Save to localStorage
            localStorage.setItem('adminActiveTab', href);
        }

        triggerTabList.forEach(triggerEl => {
            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                switchTab(triggerEl);
            })
        });

        // Restore tab on load
        document.addEventListener('DOMContentLoaded', () => {
            const savedTab = localStorage.getItem('adminActiveTab');
            if (savedTab) {
                const trigger = document.querySelector(`.sidebar .nav-link[href="${savedTab}"]`);
                if (trigger) switchTab(trigger);
            }
        });

        // --- ĐƠN HÀNG ---
        document.querySelectorAll('.btn-update-status').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const select = document.querySelector('.status-select[data-id="' + id + '"]');
                const status = select.value;

                this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

                const fd = new FormData();
                fd.append('action', 'update_order_status');
                fd.append('ma_hd', id);
                fd.append('status', status);

                fetch('admin.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(r => r.json())
                    .then(res => {
                        this.innerHTML = 'Lưu';
                        if (res.success) alert('Cập nhật trạng thái thành công!');
                        else alert('Lỗi: ' + res.message);
                    });
            });
        });

        // --- SẢN PHẨM ---
        const productModal = new bootstrap.Modal(document.getElementById('productModal'));
        const stockModal = new bootstrap.Modal(document.getElementById('stockModal'));

        function openProductModal(id = 0, name = '', brand = '', category = 'Điện thoại', imgUrl = '') {
            document.getElementById('pm_id').value = id;
            document.getElementById('pm_name').value = name;
            document.getElementById('pm_brand').value = brand;
            document.getElementById('pm_category').value = category;
            document.getElementById('pm_img').value = imgUrl;
            document.getElementById('productModalTitle').innerText = id === 0 ? 'Thêm Sản Phẩm Mới' : 'Sửa Thông Tin Sản Phẩm';
            productModal.show();
        }

        function saveProduct() {
            const id = document.getElementById('pm_id').value;
            const name = document.getElementById('pm_name').value;
            const brand = document.getElementById('pm_brand').value;
            const category = document.getElementById('pm_category').value;
            const imgUrl = document.getElementById('pm_img').value;

            if (!name || !brand) {
                alert('Vui lòng nhập đủ thông tin (Tên và Hãng)!');
                return;
            }

            const fd = new FormData();
            fd.append('action', 'save_product');
            fd.append('ma_sp', id);
            fd.append('ten_sp', name);
            fd.append('hang', brand);
            fd.append('category', category);
            fd.append('img_url', imgUrl);

            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Lỗi: ' + res.message);
                    }
                });
        }

        function deleteProduct(id) {
            if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Mọi dữ liệu liên quan sẽ bị xóa!')) return;

            const fd = new FormData();
            fd.append('action', 'delete_product');
            fd.append('ma_sp', id);

            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Lỗi: ' + res.message);
                    }
                });
        }

        function resetCategoryForm() {
            document.getElementById('category_id').value = 0;
            document.getElementById('category_name').value = '';
            document.getElementById('category_desc').value = '';
        }

        function editCategory(id, name, desc) {
            document.getElementById('category_id').value = id;
            document.getElementById('category_name').value = name;
            document.getElementById('category_desc').value = desc;
            window.location.hash = '#categories';
        }

        function saveCategory() {
            const id = document.getElementById('category_id').value;
            const name = document.getElementById('category_name').value.trim();
            const desc = document.getElementById('category_desc').value.trim();
            if (!name) {
                alert('Vui lòng nhập tên danh mục');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'save_category');
            fd.append('category_id', id);
            fd.append('category_name', name);
            fd.append('category_desc', desc);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function deleteCategory(id) {
            if (!confirm('Xóa danh mục sẽ không ảnh hưởng đến sản phẩm hiện tại. Tiếp tục?')) return;
            const fd = new FormData();
            fd.append('action', 'delete_category');
            fd.append('category_id', id);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function resetSupplierForm() {
            document.getElementById('supplier_id').value = 0;
            document.getElementById('supplier_name').value = '';
            document.getElementById('supplier_email').value = '';
            document.getElementById('supplier_phone').value = '';
            document.getElementById('supplier_address').value = '';
            document.getElementById('supplier_notes').value = '';
        }

        function editSupplier(id, name, email, phone, address, notes) {
            document.getElementById('supplier_id').value = id;
            document.getElementById('supplier_name').value = name;
            document.getElementById('supplier_email').value = email;
            document.getElementById('supplier_phone').value = phone;
            document.getElementById('supplier_address').value = address;
            document.getElementById('supplier_notes').value = notes;
            window.location.hash = '#suppliers';
        }

        function saveSupplier() {
            const id = document.getElementById('supplier_id').value;
            const name = document.getElementById('supplier_name').value.trim();
            const email = document.getElementById('supplier_email').value.trim();
            const phone = document.getElementById('supplier_phone').value.trim();
            const address = document.getElementById('supplier_address').value.trim();
            const notes = document.getElementById('supplier_notes').value.trim();
            if (!name) {
                alert('Vui lòng nhập tên nhà cung cấp');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'save_supplier');
            fd.append('supplier_id', id);
            fd.append('supplier_name', name);
            fd.append('supplier_email', email);
            fd.append('supplier_phone', phone);
            fd.append('supplier_address', address);
            fd.append('supplier_notes', notes);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function savePurchaseOrder() {
            const supplier = document.getElementById('po_supplier').value;
            const product = document.getElementById('po_product').value;
            const quantity = document.getElementById('po_quantity').value;
            const cost = document.getElementById('po_cost').value;
            const date = document.getElementById('po_date').value;
            const notes = document.getElementById('po_notes').value.trim();
            if (supplier == 0 || product == 0 || quantity <= 0 || cost <= 0) {
                alert('Vui lòng nhập đầy đủ thông tin phiếu nhập.');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'save_purchase_order');
            fd.append('po_supplier', supplier);
            fd.append('po_product', product);
            fd.append('po_quantity', quantity);
            fd.append('po_cost', cost);
            fd.append('po_date', date);
            fd.append('po_notes', notes);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function resetCouponForm() {
            document.getElementById('coupon_id').value = 0;
            document.getElementById('coupon_code').value = '';
            document.getElementById('coupon_description').value = '';
            document.getElementById('discount_type').value = 'percent';
            document.getElementById('discount_value').value = 0;
            document.getElementById('min_order_value').value = 0;
            document.getElementById('start_date').value = '<?= date('Y-m-d') ?>';
            document.getElementById('end_date').value = '<?= date('Y-m-d', strtotime('+7 days')) ?>';
            document.getElementById('coupon_active').checked = true;
        }

        function editCoupon(id, code, description, type, value, minOrder, startDate, endDate, active) {
            document.getElementById('coupon_id').value = id;
            document.getElementById('coupon_code').value = code;
            document.getElementById('coupon_description').value = description;
            document.getElementById('discount_type').value = type;
            document.getElementById('discount_value').value = value;
            document.getElementById('min_order_value').value = minOrder;
            document.getElementById('start_date').value = startDate;
            document.getElementById('end_date').value = endDate;
            document.getElementById('coupon_active').checked = Boolean(active);
            window.location.hash = '#coupons';
        }

        function saveCoupon() {
            const id = document.getElementById('coupon_id').value;
            const code = document.getElementById('coupon_code').value.trim();
            const description = document.getElementById('coupon_description').value.trim();
            const type = document.getElementById('discount_type').value;
            const value = document.getElementById('discount_value').value;
            const minOrder = document.getElementById('min_order_value').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const active = document.getElementById('coupon_active').checked ? 1 : 0;
            if (!code) {
                alert('Vui lòng nhập mã coupon');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'save_coupon');
            fd.append('coupon_id', id);
            fd.append('code', code);
            fd.append('description', description);
            fd.append('discount_type', type);
            fd.append('discount_value', value);
            fd.append('min_order_value', minOrder);
            fd.append('start_date', startDate);
            fd.append('end_date', endDate);
            fd.append('is_active', active);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function toggleCoupon(id, active) {
            const fd = new FormData();
            fd.append('action', 'toggle_coupon');
            fd.append('coupon_id', id);
            fd.append('active', active);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function toggleFeedback(id, visible) {
            const fd = new FormData();
            fd.append('action', 'toggle_feedback');
            fd.append('feedback_id', id);
            if (visible) fd.append('visible', 1);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function resetStaffForm() {
            document.getElementById('staff_id').value = 0;
            document.getElementById('staff_username').value = '';
            document.getElementById('staff_password').value = '';
            document.getElementById('staff_name').value = '';
            document.getElementById('staff_email').value = '';
            document.getElementById('staff_role').value = 'NhanVien';
        }

        function editStaff(id, username, fullname, email, role) {
            document.getElementById('staff_id').value = id;
            document.getElementById('staff_username').value = username;
            document.getElementById('staff_password').value = '';
            document.getElementById('staff_name').value = fullname;
            document.getElementById('staff_email').value = email;
            document.getElementById('staff_role').value = role;
            window.location.hash = '#staff';
        }

        function saveStaff() {
            const id = document.getElementById('staff_id').value;
            const username = document.getElementById('staff_username').value.trim();
            const password = document.getElementById('staff_password').value;
            const name = document.getElementById('staff_name').value.trim();
            const email = document.getElementById('staff_email').value.trim();
            const role = document.getElementById('staff_role').value;
            if (!username || !name) {
                alert('Vui lòng nhập tài khoản và họ tên');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'save_staff');
            fd.append('staff_id', id);
            fd.append('staff_username', username);
            fd.append('staff_password', password);
            fd.append('staff_name', name);
            fd.append('staff_email', email);
            fd.append('staff_role', role);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        function saveSetting() {
            const key = document.getElementById('setting_key').value.trim();
            const value = document.getElementById('setting_value').value.trim();
            if (!key) {
                alert('Vui lòng nhập khóa cấu hình');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'save_setting');
            fd.append('setting_key', key);
            fd.append('setting_value', value);
            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) location.reload();
                    else alert('Lỗi: ' + res.message);
                });
        }

        // --- QUẢN LÝ KHO ---
        function openStockModal(id, name) {
            document.getElementById('stockTitle').innerText = name;
            document.getElementById('stockBody').innerHTML = '<tr><td colspan="3">Đang tải...</td></tr>';
            stockModal.show();

            const fd = new FormData();
            fd.append('action', 'get_stock');
            fd.append('ma_sp', id);

            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        const data = res.data;
                        let html = '';
                        if (data.length === 0) {
                            html = '<tr><td colspan="3" class="text-danger">Sản phẩm này chưa có biến thể (RAM/Màu). Hãy thiết lập trong CSDL.</td></tr>';
                        } else {
                            data.forEach(item => {
                                const ram = item.Ram ? item.Ram : 'Mặc định';
                                const mau = item.Mau ? item.Mau : 'Mặc định';
                                html += `
                    <tr>
                        <td>${ram}</td>
                        <td>${mau}</td>
                        <td>
                            <input type="number" class="form-control stock-input" data-magia="${item.MaGia}" value="${item.SoLuong}" min="0">
                        </td>
                    </tr>`;
                            });
                        }
                        document.getElementById('stockBody').innerHTML = html;
                    }
                });
        }

        function saveStock() {
            const inputs = document.querySelectorAll('.stock-input');
            const fd = new FormData();
            fd.append('action', 'update_stock_bulk');

            inputs.forEach(input => {
                const maGia = input.getAttribute('data-magia');
                const qty = input.value;
                fd.append('stocks[' + maGia + ']', qty);
            });

            fetch('admin.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        alert('Đã cập nhật số lượng kho thành công!');
                        stockModal.hide();
                    } else {
                        alert('Lỗi cập nhật kho.');
                    }
                });
        }
    </script>
</body>

</html>