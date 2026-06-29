<?php
session_start();
require '../connect.php';

// 1. Kiểm tra đăng nhập 
if (!isset($_SESSION['user_id']) || empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$username = $_SESSION['user'];

//  2. Xử lý đăng xuất 
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

//  3. AJAX: Cập nhật thông tin cá nhân 
if (isset($_POST['action']) && $_POST['action'] === 'update_info') {
    header('Content-Type: application/json');

    $ho_ten    = trim($_POST['ho_ten']    ?? '');
    $email     = trim($_POST['email']     ?? '');
    $sdt       = trim($_POST['sdt']       ?? '');
    $dia_chi   = trim($_POST['dia_chi']   ?? '');
    $gioi_tinh = trim($_POST['gioi_tinh'] ?? '');
    $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');

    // Validate phía server
    if (empty($ho_ten)) {
        echo json_encode(['success' => false, 'message' => 'Họ tên không được để trống!']);
        exit;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ!']);
        exit;
    }
    if (!empty($sdt) && !preg_match('/^(0[0-9]{9,10})$/', $sdt)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ (10-11 số, bắt đầu bằng 0)!']);
        exit;
    }

    // Chuẩn hóa ngày sinh
    $ngay_sinh_val = !empty($ngay_sinh) ? $ngay_sinh : null;

    $stmt = $conn->prepare(
        "UPDATE khachhang
         SET HoTen=?, Email=?, SoDienThoai=?, DiaChi=?, GioiTinh=?, NgaySinh=?
         WHERE TenDangNhap=?"
    );
    $stmt->bind_param('sssssss', $ho_ten, $email, $sdt, $dia_chi, $gioi_tinh, $ngay_sinh_val, $username);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL. Vui lòng thử lại!']);
    }
    exit;
}

// 4. AJAX: Đổi mật khẩu 
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    header('Content-Type: application/json');

    $old_pass     = $_POST['old_pass']     ?? '';
    $new_pass     = $_POST['new_pass']     ?? '';
    $confirm_pass = $_POST['confirm_pass'] ?? '';

    if (strlen($new_pass) < 8) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 8 ký tự!']);
        exit;
    }
    if ($new_pass !== $confirm_pass) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu xác nhận không khớp!']);
        exit;
    }

    $stmt = $conn->prepare("SELECT MatKhau FROM khachhang WHERE TenDangNhap = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tài khoản!']);
        exit;
    }
    $row = $result->fetch_assoc();
    if ($row['MatKhau'] !== $old_pass) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng!']);
        exit;
    }
    if ($old_pass === $new_pass) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu mới không được trùng mật khẩu cũ!']);
        exit;
    }

    $stmt2 = $conn->prepare("UPDATE khachhang SET MatKhau = ? WHERE TenDangNhap = ?");
    $stmt2->bind_param('ss', $new_pass, $username);

    if ($stmt2->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi cập nhật CSDL. Vui lòng thử lại!']);
    }
    exit;
}

// 5. AJAX: Xem chi tiết đơn hàng 
if (isset($_POST['action']) && $_POST['action'] === 'order_detail') {
    header('Content-Type: application/json');

    $ma_hd = intval($_POST['ma_hd'] ?? 0);
    if ($ma_hd <= 0) {
        echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ!']);
        exit;
    }

    // Kiểm tra đơn hàng thuộc về user đang đăng nhập
    $chk = $conn->prepare("SELECT MaHoaDon, NgayLap, TongTien, TrangThai FROM hoadon WHERE MaHoaDon = ? AND TenDangNhap = ?");
    $chk->bind_param('is', $ma_hd, $username);
    $chk->execute();
    $hd = $chk->get_result()->fetch_assoc();

    if (!$hd) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng!']);
        exit;
    }

    // Lấy chi tiết sản phẩm trong đơn
    $stmt = $conn->prepare(
        "SELECT ct.MaCTHD, sp.TenSanPham, ct.TenMau, ct.KichThuoc, ct.SoLuong, ct.ThanhTien
         FROM chitiethoadon ct
         JOIN sanpham sp ON ct.MaSanPham = sp.MaSanPham
         WHERE ct.MaHoaDon = ?"
    );
    $stmt->bind_param('i', $ma_hd);
    $stmt->execute();
    $items = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode([
        'success' => true,
        'hoadon'  => [
            'MaHoaDon' => '#DH-' . str_pad($hd['MaHoaDon'], 5, '0', STR_PAD_LEFT),
            'NgayLap'  => $hd['NgayLap'] ? date('d/m/Y', strtotime($hd['NgayLap'])) : '—',
            'TongTien' => number_format(floatval($hd['TongTien']), 0, ',', '.') . '₫',
            'TrangThai' => $hd['TrangThai'],
            'RawTrangThai' => $hd['TrangThai'],
            'RawMaHD' => $hd['MaHoaDon']
        ],
        'items' => $items,
    ]);
    exit;
}

// 5B. AJAX: Hủy bớt số lượng sản phẩm (Partial Cancel) 
if (isset($_POST['action']) && $_POST['action'] === 'reduce_item_qty') {
    header('Content-Type: application/json');
    $maCTHD = (int)($_POST['ma_cthd'] ?? 0);
    $qtyToCancel = (int)($_POST['qty_cancel'] ?? 0);

    if ($maCTHD <= 0 || $qtyToCancel <= 0) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ.']);
        exit;
    }

    // Kiểm tra CTHD và Hóa đơn
    $stmt = $conn->prepare("SELECT ct.*, hd.TrangThai, hd.TenDangNhap FROM chitiethoadon ct JOIN hoadon hd ON ct.MaHoaDon = hd.MaHoaDon WHERE ct.MaCTHD = ?");
    $stmt->bind_param('i', $maCTHD);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if (!$item || $item['TenDangNhap'] !== $username) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm hoặc không có quyền.']);
        exit;
    }

    if ($item['TrangThai'] !== 'Chưa xác nhận' && $item['TrangThai'] !== 'Đã xác nhận') {
        echo json_encode(['success' => false, 'message' => 'Chỉ có thể hủy khi đơn hàng chưa đóng gói/vận chuyển.']);
        exit;
    }

    if ($qtyToCancel > $item['SoLuong']) {
        echo json_encode(['success' => false, 'message' => 'Số lượng hủy không được lớn hơn số lượng đã đặt.']);
        exit;
    }

    $donGia = $item['ThanhTien'] / $item['SoLuong'];
    $tienGiam = $donGia * $qtyToCancel;
    $newSoLuong = $item['SoLuong'] - $qtyToCancel;
    $newThanhTien = $item['ThanhTien'] - $tienGiam;

    $conn->begin_transaction();
    try {
        if ($newSoLuong <= 0) {
            $conn->query("DELETE FROM chitiethoadon WHERE MaCTHD = $maCTHD");
        } else {
            $conn->query("UPDATE chitiethoadon SET SoLuong = $newSoLuong, ThanhTien = $newThanhTien WHERE MaCTHD = $maCTHD");
        }

        // Cập nhật tổng tiền hóa đơn
        $conn->query("UPDATE hoadon SET TongTien = TongTien - $tienGiam WHERE MaHoaDon = {$item['MaHoaDon']}");

        // Hoàn lại kho
        $maSP = $item['MaSanPham'];
        $mau = $item['TenMau'];
        $ram = $item['KichThuoc'];
        // Thử tìm MaGia
        $findGia = $conn->prepare("
            SELECT g.MaGia FROM giasanpham g 
            LEFT JOIN colors c ON g.MaMau = c.MaMau 
            LEFT JOIN ram_rom_option r ON g.MaRam = r.MaRam 
            WHERE g.MaSanPham = ? AND (c.TenMau = ? OR (?='' AND c.TenMau IS NULL)) AND (r.KichThuoc = ? OR (?='' AND r.KichThuoc IS NULL))
            LIMIT 1
        ");
        $findGia->bind_param('issss', $maSP, $mau, $mau, $ram, $ram);
        $findGia->execute();
        $gRow = $findGia->get_result()->fetch_assoc();
        if ($gRow) {
            $conn->query("UPDATE giasanpham SET SoLuong = SoLuong + $qtyToCancel WHERE MaGia = {$gRow['MaGia']}");
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Đã hủy bớt sản phẩm thành công!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// ── 5C. AJAX: Hủy toàn bộ đơn hàng (Cancel Order) ────────
if (isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    header('Content-Type: application/json');
    $maHD = (int)($_POST['ma_hd'] ?? 0);

    if ($maHD <= 0) {
        echo json_encode(['success' => false, 'message' => 'Mã đơn hàng không hợp lệ.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT TrangThai, TenDangNhap FROM hoadon WHERE MaHoaDon = ?");
    $stmt->bind_param('i', $maHD);
    $stmt->execute();
    $hdInfo = $stmt->get_result()->fetch_assoc();

    if (!$hdInfo || $hdInfo['TenDangNhap'] !== $username) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng hoặc không có quyền.']);
        exit;
    }

    if ($hdInfo['TrangThai'] !== 'Chưa xác nhận' && $hdInfo['TrangThai'] !== 'Đã xác nhận') {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng đang xử lý, không thể hủy.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Lấy toàn bộ CTHD để hoàn kho
        $qCT = $conn->query("SELECT MaSanPham, TenMau, KichThuoc, SoLuong FROM chitiethoadon WHERE MaHoaDon = $maHD");
        while ($item = $qCT->fetch_assoc()) {
            $maSP = $item['MaSanPham'];
            $mau = $item['TenMau'];
            $ram = $item['KichThuoc'];
            $qty = $item['SoLuong'];

            $findGia = $conn->prepare("
                SELECT g.MaGia FROM giasanpham g 
                LEFT JOIN colors c ON g.MaMau = c.MaMau 
                LEFT JOIN ram_rom_option r ON g.MaRam = r.MaRam 
                WHERE g.MaSanPham = ? AND (c.TenMau = ? OR (?='' AND c.TenMau IS NULL)) AND (r.KichThuoc = ? OR (?='' AND r.KichThuoc IS NULL))
                LIMIT 1
            ");
            $findGia->bind_param('issss', $maSP, $mau, $mau, $ram, $ram);
            $findGia->execute();
            $gRow = $findGia->get_result()->fetch_assoc();
            if ($gRow) {
                $conn->query("UPDATE giasanpham SET SoLuong = SoLuong + $qty WHERE MaGia = {$gRow['MaGia']}");
            }
        }

        // Cập nhật trạng thái hóa đơn thành Đã hủy
        $conn->query("UPDATE hoadon SET TrangThai = 'Đã hủy' WHERE MaHoaDon = $maHD");

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
    }
    exit;
}

// ── 6. AJAX: Xóa tài khoản ──────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    header('Content-Type: application/json');

    $confirm_pass = $_POST['confirm_pass'] ?? '';
    if (empty($confirm_pass)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mật khẩu để xác nhận!']);
        exit;
    }

    // Xác thực mật khẩu
    $stmt = $conn->prepare("SELECT MatKhau FROM khachhang WHERE TenDangNhap = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || $row['MatKhau'] !== $confirm_pass) {
        echo json_encode(['success' => false, 'message' => 'Mật khẩu không đúng! Xóa tài khoản thất bại.']);
        exit;
    }

    // Xóa tài khoản
    $del = $conn->prepare("DELETE FROM khachhang WHERE TenDangNhap = ?");
    $del->bind_param('s', $username);

    if ($del->execute()) {
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Tài khoản đã được xóa vĩnh viễn.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi xóa tài khoản. Vui lòng thử lại!']);
    }
    exit;
}

// ── 6. Load thông tin user từ DB ────────────────────────────
$stmt = $conn->prepare(
    "SELECT HoTen, Email, SoDienThoai, DiaChi, GioiTinh, NgaySinh FROM khachhang WHERE TenDangNhap = ?"
);
$stmt->bind_param('s', $username);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

if (!$userData) {
    // Tài khoản không tồn tại trong DB → đăng xuất
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$user = [
    'ho_ten'    => $userData['HoTen']        ?? '',
    'email'     => $userData['Email']         ?? '',
    'sdt'       => $userData['SoDienThoai']   ?? '',
    'ngay_sinh' => $userData['NgaySinh']      ?? '',
    'gioi_tinh' => strtolower($userData['GioiTinh'] ?? 'nam'),
    'dia_chi'   => $userData['DiaChi']        ?? '',
];

// ── 6. Load đơn hàng của user ────────────────────────────────
$orders = [];
$stmt2 = $conn->prepare(
    "SELECT MaHoaDon, NgayLap, TongTien, TrangThai FROM hoadon WHERE TenDangNhap = ? ORDER BY NgayLap DESC"
);
$stmt2->bind_param('s', $username);
$stmt2->execute();
$ordersResult = $stmt2->get_result();
while ($o = $ordersResult->fetch_assoc()) {
    $orders[] = $o;
}

$badge = [
    'Chưa xác nhận'   => ['class' => 'badge-warning', 'icon' => 'fa-clock'],
    'Đã xác nhận'     => ['class' => 'badge-info',    'icon' => 'fa-clipboard-check'],
    'Đang đóng gói'   => ['class' => 'badge-info',    'icon' => 'fa-box-open'],
    'Đang vận chuyển' => ['class' => 'badge-primary', 'icon' => 'fa-truck-fast'],
    'Đã giao'         => ['class' => 'badge-success', 'icon' => 'fa-circle-check'],
    'Đã hủy'          => ['class' => 'badge-danger',  'icon' => 'fa-circle-xmark'],
];
$defaultBadge = ['class' => 'badge-secondary', 'icon' => 'fa-circle-info'];

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Quản lý hồ sơ cá nhân – cập nhật thông tin, đổi mật khẩu và xem lịch sử mua hàng.">
    <title>Hồ sơ của tôi | Shop Online</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Profile CSS -->
    <link rel="stylesheet" href="../css/profile.css">
    <link rel="stylesheet" href="../css/shop.css">

    <style>
        .profile-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            display: flex;
            gap: 24px;
            padding: 0 15px;
        }

        .profile-sidebar {
            position: static;
            height: auto;
            transform: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            border: 1px solid #eee;
            width: 280px;
            flex-shrink: 0;
        }

        .profile-main {
            margin-left: 0;
            min-height: 50vh;
            border-radius: 12px;
            border: 1px solid #eee;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            flex: 1;
            overflow: hidden;
            background: #fff;
        }

        @media (max-width: 768px) {
            .profile-wrapper {
                flex-direction: column;
            }

            .profile-sidebar {
                width: 100%;
            }
        }

        .hamburger-btn {
            display: none;
        }

        .topbar {
            background: #f8fafc;
            border-bottom: 1px solid #eee;
        }

        body {
            background: #f1f5f9;
        }
    </style>
</head>

<body>
    <header class="site-header">
        <div class="top-bar">
            <div class="container top-bar__inner">
                <span>Miễn phí vận chuyển đơn từ 300.000đ</span>
                <span>Hàng chính hãng 100%</span>
                <span class="hide-mobile">Hotline: <strong>1800.2097</strong></span>
            </div>
        </div>
        <div class="main-header">
            <div class="container main-header__inner">
                <a href="../index.php" class="logo">CellPhone<span>K</span></a>
                <nav class="desktop-nav hide-mobile">
                    <a href="../index.php">Trang chủ</a>
                    <a href="../index.php">Sản phẩm</a>

                </nav>
                <form class="search-form" action="index.php" method="get">
                    <input type="search" name="search" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Tìm kiếm">
                    <button type="submit" aria-label="Tìm">&#128269;</button>
                </form>
                <div class="header-actions">
                    <a href="../pages/cart.php" class="header-cart">
                        &#128722; Giỏ hàng
                        <?php
                        $__cartCount = 0;
                        if (!empty($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $__item) {
                                $__cartCount += $__item['quantity'];
                            }
                        }
                        if ($__cartCount > 0): ?>
                            <span class="badge"><?= $__cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="../pages/profile.php" class="btn btn-outline btn-sm hide-mobile">&#128100; <?= htmlspecialchars($_SESSION['username'] ?? 'Tài khoản') ?></a>
                        <a href="../pages/logout.php" class="btn btn-outline btn-sm hide-mobile">Đăng xuất</a>
                    <?php else: ?>
                        <a href="../pages/login.php" class="btn btn-outline btn-sm hide-mobile">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>


    <!-- Hamburger (mobile) -->
    <button class="hamburger-btn" id="hamburgerBtn" aria-label="Mở menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="profile-wrapper">

        <!-- ==================== SIDEBAR ==================== -->
        <aside class="profile-sidebar" id="profileSidebar">

            <div class="sidebar-header">
                <div class="avatar-wrapper">
                    <div class="avatar-placeholder">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span class="avatar-badge"></span>
                </div>
                <div class="sidebar-name"><?= htmlspecialchars($user['ho_ten']) ?></div>
                <div class="sidebar-email"><?= htmlspecialchars($user['email']) ?></div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="#" class="nav-link active" data-target="section-info" id="navInfo">
                            <span class="nav-icon"><i class="fa-solid fa-circle-user"></i></span>
                            <span class="nav-label">Thông tin cá nhân</span>
                            <span class="nav-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link" data-target="section-password" id="navPassword">
                            <span class="nav-icon"><i class="fa-solid fa-lock"></i></span>
                            <span class="nav-label">Đổi mật khẩu</span>
                            <span class="nav-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link" data-target="section-orders" id="navOrders">
                            <span class="nav-icon"><i class="fa-solid fa-bag-shopping"></i></span>
                            <span class="nav-label">Lịch sử mua hàng</span>
                            <span class="nav-arrow"><i class="fa-solid fa-chevron-right"></i></span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <a href="?action=logout" class="btn-logout">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a>
                <button class="btn-delete-account" id="btnOpenDeleteModal">
                    <i class="fa-solid fa-trash-can"></i> Xóa tài khoản
                </button>
            </div>
        </aside>

        <!-- ==================== MAIN ==================== -->
        <main class="profile-main">

            <div class="topbar">
                <div>
                    <div class="topbar-title">Hồ sơ <span>cá nhân</span></div>
                    <div class="topbar-breadcrumb">
                        <a href="../index.php">Trang chủ</a> / Hồ sơ
                    </div>
                </div>
            </div>

            <!-- ---- SECTION 1: Thông tin cá nhân ---- -->
            <section class="content-section" id="section-info">
                <div class="section-header">
                    <div class="section-header-icon"><i class="fa-solid fa-circle-user"></i></div>
                    <div>
                        <h2>Thông tin cá nhân</h2>
                        <p>Cập nhật ảnh đại diện và các thông tin của bạn</p>
                    </div>
                </div>

                <form method="POST" id="formInfo" autocomplete="off">
                    <div class="form-grid">
                        <!-- Họ tên -->
                        <div class="form-group">
                            <label for="ho_ten">Họ và tên <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-user"></i>
                                <input type="text" id="ho_ten" name="ho_ten" class="form-input"
                                    value="<?= htmlspecialchars($user['ho_ten']) ?>" placeholder="Nhập họ tên" required>
                            </div>
                        </div>
                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-input"
                                    value="<?= htmlspecialchars($user['email']) ?>" placeholder="example@gmail.com" required>
                            </div>
                        </div>
                        <!-- SĐT -->
                        <div class="form-group">
                            <label for="sdt">Số điện thoại</label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-phone"></i>
                                <input type="tel" id="sdt" name="sdt" class="form-input"
                                    value="<?= htmlspecialchars($user['sdt']) ?>" placeholder="09xx xxx xxx">
                            </div>
                        </div>
                        <!-- Ngày sinh -->
                        <div class="form-group">
                            <label for="ngay_sinh">Ngày sinh</label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-calendar-days"></i>
                                <input type="date" id="ngay_sinh" name="ngay_sinh" class="form-input"
                                    value="<?= htmlspecialchars($user['ngay_sinh']) ?>">
                            </div>
                        </div>
                        <!-- Giới tính -->
                        <div class="form-group">
                            <label for="gioi_tinh">Giới tính</label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-venus-mars"></i>
                                <select id="gioi_tinh" name="gioi_tinh" class="form-input">
                                    <option value="nam" <?= $user['gioi_tinh'] === 'nam'  ? 'selected' : '' ?>>Nam</option>
                                    <option value="nu" <?= $user['gioi_tinh'] === 'nu'   ? 'selected' : '' ?>>Nữ</option>
                                    <option value="khac" <?= $user['gioi_tinh'] === 'khac' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>
                        </div>
                        <!-- Địa chỉ -->
                        <div class="form-group full-width">
                            <label for="dia_chi">Địa chỉ giao hàng</label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-location-dot"></i>
                                <input type="text" id="dia_chi" name="dia_chi" class="form-input"
                                    value="<?= htmlspecialchars($user['dia_chi']) ?>" placeholder="Số nhà, đường, quận, tỉnh/thành">
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary" id="btnSaveInfo">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fa-solid fa-rotate-left"></i> Đặt lại
                        </button>
                    </div>
                </form>
            </section>

            <!-- ---- SECTION 2: Đổi mật khẩu ---- -->
            <section class="content-section" id="section-password" style="display:none;">
                <div class="section-header">
                    <div class="section-header-icon"><i class="fa-solid fa-lock"></i></div>
                    <div>
                        <h2>Đổi mật khẩu</h2>
                        <p>Bảo mật tài khoản bằng mật khẩu mạnh</p>
                    </div>
                </div>

                <form method="POST" id="formPassword" autocomplete="off">
                    <div class="form-grid">
                        <!-- Mật khẩu cũ -->
                        <div class="form-group full-width">
                            <label for="old_pass">Mật khẩu hiện tại <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-key"></i>
                                <input type="password" id="old_pass" name="old_pass" class="form-input"
                                    placeholder="Nhập mật khẩu hiện tại" required>
                                <button type="button" class="input-toggle-btn" data-target="old_pass">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <hr class="form-divider">
                        <!-- Mật khẩu mới -->
                        <div class="form-group">
                            <label for="new_pass">Mật khẩu mới <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-lock"></i>
                                <input type="password" id="new_pass" name="new_pass" class="form-input"
                                    placeholder="Tối thiểu 8 ký tự" required>
                                <button type="button" class="input-toggle-btn" data-target="new_pass">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text" id="strengthText">Nhập mật khẩu để kiểm tra độ mạnh</div>
                            </div>
                            <span class="input-hint">Dùng chữ hoa, chữ thường, số và ký tự đặc biệt.</span>
                        </div>
                        <!-- Nhập lại -->
                        <div class="form-group">
                            <label for="confirm_pass">Xác nhận mật khẩu mới <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <i class="input-icon fa-solid fa-shield-halved"></i>
                                <input type="password" id="confirm_pass" name="confirm_pass" class="form-input"
                                    placeholder="Nhập lại mật khẩu mới" required>
                                <button type="button" class="input-toggle-btn" data-target="confirm_pass">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <span class="input-hint" id="matchHint"></span>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary" id="btnSavePass">
                            <i class="fa-solid fa-shield-halved"></i> Cập nhật mật khẩu
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fa-solid fa-rotate-left"></i> Xóa trắng
                        </button>
                    </div>
                </form>
            </section>

            <!-- ---- SECTION 3: Lịch sử đơn hàng ---- -->
            <section class="content-section" id="section-orders" style="display:none;">
                <div class="section-header">
                    <div class="section-header-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                    <div>
                        <h2>Lịch sử mua hàng</h2>
                        <p>Theo dõi toàn bộ đơn hàng của bạn</p>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="stat-cards">
                    <div class="stat-card">
                        <div class="stat-icon purple"><i class="fa-solid fa-receipt"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?= count($orders) ?></div>
                            <div class="stat-label">Tổng đơn hàng</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon emerald"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?= count(array_filter($orders, fn($o) => $o['TrangThai'] === 'Đã giao')) ?></div>
                            <div class="stat-label">Đã hoàn thành</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon cyan"><i class="fa-solid fa-truck"></i></div>
                        <div class="stat-info">
                            <div class="stat-value"><?= count(array_filter($orders, fn($o) => in_array($o['TrangThai'], ['Đang vận chuyển', 'Đang đóng gói', 'Đã xác nhận', 'Chưa xác nhận']))) ?></div>
                            <div class="stat-label">Đang xử lý</div>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="table-toolbar">
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="orderSearch" placeholder="Tìm theo mã đơn hàng...">
                    </div>
                    <button class="btn btn-secondary" id="btnExport">
                        <i class="fa-solid fa-file-export"></i> Xuất CSV
                    </button>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="orders-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-hashtag"></i> Mã đơn hàng</th>
                                <th><i class="fa-regular fa-calendar"></i> Ngày đặt</th>
                                <th><i class="fa-solid fa-coins"></i> Tổng tiền</th>
                                <th><i class="fa-solid fa-circle-half-stroke"></i> Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tongChiTieu = 0;
                            if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center;padding:40px;color:#94a3b8;">
                                        <i class="fa-solid fa-bag-shopping" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
                                        Bạn chưa có đơn hàng nào.
                                    </td>
                                </tr>
                                <?php else: foreach ($orders as $o):
                                    $tongChiTieu += floatval($o['TongTien']);
                                    $b = $badge[$o['TrangThai']] ?? $defaultBadge;
                                    $maHD = '#DH-' . str_pad($o['MaHoaDon'], 5, '0', STR_PAD_LEFT);
                                    $ngay = $o['NgayLap'] ? date('d/m/Y', strtotime($o['NgayLap'])) : '—';
                                    $tien = number_format(floatval($o['TongTien']), 0, ',', '.');
                                ?>
                                    <tr>
                                        <td><span class="order-id"><?= $maHD ?></span></td>
                                        <td><span class="order-date"><i class="fa-regular fa-clock"></i> <?= $ngay ?></span></td>
                                        <td><span class="order-amount"><?= $tien ?>₫</span></td>
                                        <td>
                                            <span class="badge <?= $b['class'] ?>">
                                                <i class="fa-solid <?= $b['icon'] ?>"></i> <?= htmlspecialchars($o['TrangThai']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn-icon btn-view-order" title="Xem chi tiết" data-mahd="<?= $o['MaHoaDon'] ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <a href="print_bill.php?id=<?= $o['MaHoaDon'] ?>" target="_blank" class="btn-icon" title="In hóa đơn" style="color:#0ea5e9;">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                            <?php if ($o['TrangThai'] === 'Chưa xác nhận' || $o['TrangThai'] === 'Đã xác nhận'): ?>
                                                <button class="btn-icon btn-cancel-order" title="Hủy toàn bộ đơn hàng" data-mahd="<?= $o['MaHoaDon'] ?>" style="color:#ef4444;">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php endforeach;
                            endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-summary">
                    <span id="rowCount">Hiển thị <?= count($orders) ?> đơn hàng</span>
                    <span class="table-total">Tổng chi tiêu: <strong><?= number_format($tongChiTieu, 0, ',', '.') ?>₫</strong></span>
                </div>
            </section>

        </main><!-- /.profile-main -->
    </div><!-- /.profile-wrapper -->

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- ==================== MODAL XÓA TÀI KHOẢN ==================== -->
    <div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <div class="modal-box">
            <!-- Bước 1: Cảnh báo -->
            <div id="deleteStep1">
                <div class="modal-icon danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="modal-title" id="deleteModalTitle">Xóa tài khoản vĩnh viễn?</h3>
                <p class="modal-desc">
                    Hành động này <strong>không thể hoàn tác</strong>. Toàn bộ thông tin cá nhân,
                    lịch sử đơn hàng và dữ liệu liên quan sẽ bị xóa vĩnh viễn khỏi hệ thống.
                </p>
                <ul class="modal-warn-list">
                    <li><i class="fa-solid fa-xmark"></i> Mất toàn bộ lịch sử mua hàng</li>
                    <li><i class="fa-solid fa-xmark"></i> Không thể khôi phục tài khoản</li>
                    <li><i class="fa-solid fa-xmark"></i> Các đơn hàng đang xử lý có thể bị ảnh hưởng</li>
                </ul>
                <div class="modal-actions">
                    <button class="btn btn-secondary" id="btnCancelDelete">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </button>
                    <button class="btn btn-danger-solid" id="btnGoStep2">
                        <i class="fa-solid fa-trash-can"></i> Tôi muốn xóa
                    </button>
                </div>
            </div>

            <!-- Bước 2: Nhập mật khẩu xác nhận -->
            <div id="deleteStep2" style="display:none;">
                <div class="modal-icon danger">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h3 class="modal-title">Xác nhận bằng mật khẩu</h3>
                <p class="modal-desc">Nhập mật khẩu hiện tại của bạn để xác nhận xóa tài khoản <strong><?= htmlspecialchars($username) ?></strong>.</p>
                <div class="form-group" style="margin:20px 0;">
                    <label for="deletePassInput" style="font-size:.8rem;font-weight:600;color:#64748b;display:block;margin-bottom:6px;">Mật khẩu xác nhận <span style="color:#ef4444">*</span></label>
                    <div class="input-with-icon">
                        <i class="input-icon fa-solid fa-lock"></i>
                        <input type="password" id="deletePassInput" class="form-input"
                            placeholder="Nhập mật khẩu của bạn" autocomplete="current-password">
                        <button type="button" class="input-toggle-btn" data-target="deletePassInput">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div id="deletePassError" style="color:#ef4444;font-size:.78rem;margin-top:5px;min-height:18px;"></div>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" id="btnBackStep1">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại
                    </button>
                    <button class="btn btn-danger-solid" id="btnConfirmDelete">
                        <i class="fa-solid fa-trash-can"></i> Xóa vĩnh viễn
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL CHI TIẾT ĐƠN HÀNG ==================== -->
    <div class="modal-overlay" id="orderDetailModal" role="dialog" aria-modal="true" aria-labelledby="orderDetailTitle">
        <div class="modal-box modal-box-lg">
            <div class="modal-detail-header">
                <div>
                    <h3 class="modal-title" id="orderDetailTitle" style="text-align:left;">
                        <i class="fa-solid fa-receipt" style="color:var(--primary);margin-right:8px;"></i>
                        Chi tiết đơn hàng
                    </h3>
                    <p id="orderDetailSubtitle" style="font-size:.82rem;color:var(--text-sub);margin-top:4px;"></p>
                </div>
                <button class="btn-icon" id="btnCloseOrderDetail" title="Đóng" style="width:36px;height:36px;flex-shrink:0;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Thông tin đơn -->
            <div class="order-detail-meta" id="orderDetailMeta"></div>

            <!-- Bảng sản phẩm -->
            <div class="table-responsive" style="margin-top:16px;">
                <table class="orders-table" id="orderDetailTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><i class="fa-solid fa-mobile-screen-button"></i> Sản phẩm</th>
                            <th><i class="fa-solid fa-palette"></i> Phiên bản</th>
                            <th><i class="fa-solid fa-hashtag"></i> Số lượng</th>
                            <th><i class="fa-solid fa-coins"></i> Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="orderDetailBody">
                        <tr>
                            <td colspan="5" style="text-align:center;padding:30px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tổng -->
            <div class="order-detail-footer" id="orderDetailFooter"></div>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Profile JS -->
    <script src="../js/profile.js"></script>


    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <h4>CellPhoneK</h4>
                <p>Website bán điện thoại &amp; laptop chính hãng.</p>
            </div>
            <div>
                <h4>Danh mục</h4>
                <a href="../index.php">Điện thoại</a>
                <a href="../index.php">Laptop</a>
            </div>
            <div>
                <h4>Hỗ trợ</h4>
                <a href="../pages/cart.php">Giỏ hàng</a>
                <a href="../pages/checkout.php">Thanh toán</a>
            </div>
        </div>
        <p class="footer-copy">© 2026 CellPhoneK. All rights reserved.</p>
    </footer>

</body>

</html>