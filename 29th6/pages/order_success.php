<?php
/**
 * TRANG ĐẶT HÀNG THÀNH CÔNG
 */
session_start();
require '../connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$maHoaDon    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tenDangNhap = $_SESSION['user'] ?? '';

if ($maHoaDon <= 0) {
    header('Location: ../index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM hoadon WHERE MaHoaDon = ? AND TenDangNhap = ?");
$stmt->bind_param('is', $maHoaDon, $tenDangNhap);
$stmt->execute();
$hoaDon = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$hoaDon) {
    header('Location: ../index.php');
    exit;
}

function vietqr_tlv($id, $value) {
    return str_pad($id, 2, '0', STR_PAD_LEFT) . str_pad(strlen($value), 2, '0', STR_PAD_LEFT) . $value;
}

function vietqr_crc16($data) {
    $crc = 0xFFFF;
    $poly = 0x1021;
    for ($i = 0; $i < strlen($data); $i++) {
        $crc ^= ord($data[$i]) << 8;
        for ($j = 0; $j < 8; $j++) {
            if ($crc & 0x8000) {
                $crc = (($crc << 1) ^ $poly) & 0xFFFF;
            } else {
                $crc = ($crc << 1) & 0xFFFF;
            }
        }
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

if ($hoaDon['PhuongThucThanhToan'] === 'BANK') {
    $accountNumber = '0123456789';
    $merchantName = 'CellPhoneK';
    $merchantCity = 'HO CHI MINH';
    $amount = number_format($hoaDon['TongTien'], 0, '', '');
    $orderReference = 'DH-' . str_pad($hoaDon['MaHoaDon'], 5, '0', STR_PAD_LEFT);

    $merchantInfo = vietqr_tlv('00', 'A000000727')
                  . vietqr_tlv('01', $accountNumber)
                  . vietqr_tlv('02', $merchantName)
                  . vietqr_tlv('03', $merchantCity);

    $qrPayload = ''
        . vietqr_tlv('00', '01')
        . vietqr_tlv('01', '11')
        . vietqr_tlv('26', $merchantInfo)
        . vietqr_tlv('52', '0000')
        . vietqr_tlv('53', '704')
        . vietqr_tlv('54', $amount)
        . vietqr_tlv('58', 'VN')
        . vietqr_tlv('59', $merchantName)
        . vietqr_tlv('60', $merchantCity)
        . vietqr_tlv('62', vietqr_tlv('01', $orderReference))
        . '6304';

    $crc = vietqr_crc16($qrPayload);
    $qrPayload .= $crc;
    $vietqrPayload = $qrPayload;
    $vietqrImgUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($vietqrPayload) . '&choe=UTF-8';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công – CellPhoneK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
    <style>
        .success-wrap { min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .success-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); padding: 48px 40px; max-width: 560px; width: 100%; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,.08); }
        .success-icon { width: 80px; height: 80px; background: linear-gradient(135deg, var(--success), #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px; }
        .success-card h1 { font-size: 1.8rem; font-weight: 800; color: var(--text); margin-bottom: 8px; }
        .success-card .sub { color: var(--text-muted); font-size: .95rem; margin-bottom: 32px; }
        .info-box { background: var(--bg3); border-radius: 10px; padding: 20px; text-align: left; margin-bottom: 24px; }
        .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid var(--border); font-size: .88rem; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--text-muted); }
        .info-value { font-weight: 600; color: var(--text); }
        .info-value.highlight { color: var(--accent); font-size: 1rem; }
        .bank-info { background: rgba(14,165,233,.08); border: 1px solid rgba(14,165,233,.2); border-radius: 10px; padding: 16px; text-align: left; margin-bottom: 24px; font-size: .85rem; }
        .bank-info h3 { color: var(--primary); margin-bottom: 8px; }
        .bank-info p { margin: 4px 0; color: var(--text-muted); }
        .action-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
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
                <a href="../pages/cart.php">Giỏ hàng</a>
            </nav>
            <form class="search-form" action="../index.php" method="get">
                <input type="search" name="search" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Tìm kiếm">
                <button type="submit" aria-label="Tìm">&#128269;</button>
            </form>
            <div class="header-actions">
                <a href="../pages/cart.php" class="header-cart">
                    &#128722; Giỏ hàng
                    <?php
                    $__cartCount = 0;
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $__item) { $__cartCount += $__item['quantity']; }
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

<div class="success-wrap">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h1>Đặt hàng thành công!</h1>
        <p class="sub">Cảm ơn bạn đã mua hàng tại CellPhoneK. Chúng tôi sẽ liên hệ sớm.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Mã đơn hàng:</span>
                <span class="info-value highlight">#DH-<?= str_pad($hoaDon['MaHoaDon'], 5, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày đặt:</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($hoaDon['NgayLap'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Người nhận:</span>
                <span class="info-value"><?= htmlspecialchars($hoaDon['HoTenNhan']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value"><?= htmlspecialchars($hoaDon['SoDienThoaiNhan']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Địa chỉ:</span>
                <span class="info-value"><?= htmlspecialchars($hoaDon['DiaChiNhan']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Thanh toán:</span>
                <span class="info-value"><?= $hoaDon['PhuongThucThanhToan'] === 'COD' ? 'Tiền mặt (COD)' : 'Chuyển khoản' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tổng tiền:</span>
                <span class="info-value highlight"><?= number_format($hoaDon['TongTien'], 0, ',', '.') ?>đ</span>
            </div>
            <div class="info-row">
                <span class="info-label">Trạng thái:</span>
                <span class="info-value"><?= htmlspecialchars($hoaDon['TrangThai']) ?></span>
            </div>
        </div>

        <?php if ($hoaDon['PhuongThucThanhToan'] === 'BANK'): ?>
        <div class="bank-info">
            <h3>💳 Thông tin chuyển khoản</h3>
            <p>Ngân hàng: <b>Vietcombank</b></p>
            <p>Số tài khoản: <b>0123456789</b></p>
            <p>Chủ tài khoản: <b>CellPhoneK</b></p>
            <p>Nội dung: <b>#<?= $hoaDon['MaHoaDon'] ?> <?= htmlspecialchars($hoaDon['HoTenNhan']) ?></b></p>
            
           
            <details style="margin-top:10px; color:var(--text-muted); font-size:13px;">
                <summary style="cursor:pointer;">Hiện payload QR</summary>
                <code style="display:block; margin-top:10px; word-break:break-all; background:#f8fafc; padding:10px; border-radius:10px; border:1px solid rgba(15,23,42,.08);"><?= htmlspecialchars($vietqrPayload) ?></code>
            </details>
        </div>
        <?php endif; ?>

        <div class="action-btns">
            <a href="print_bill.php?id=<?= $hoaDon['MaHoaDon'] ?>" target="_blank" class="btn-primary" style="background:#0ea5e9;border-color:#0ea5e9;"><i class="fa-solid fa-print"></i> In hóa đơn</a>
            <a href="profile.php?tab=orders" class="btn-primary">📋 Xem đơn hàng của tôi</a>
            <a href="../index.php" style="display:inline-block;padding:12px 28px;border-radius:10px;border:1px solid var(--border);color:var(--text-muted);font-weight:600;transition:.2s;">Tiếp tục mua sắm</a>
        </div>
    </div>
</div>

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



<nav class="bottom-nav" aria-label="Menu chinh">
    <a href="../index.php"><span class="icon">&#127968;</span><span>Trang chu</span></a>
    <a href="../index.php"><span class="icon">&#128241;</span><span>San pham</span></a>
    <a href="../pages/cart.php"><span class="icon">&#128722;</span><span>Gio hang</span></a>
    <a href="<?= isset($_SESSION['user_id']) ? '../pages/profile.php' : '../pages/login.php' ?>"><span class="icon">&#128100;</span><span><?= isset($_SESSION['user_id']) ? 'Tai khoan' : 'Dang nhap' ?></span></a>
</nav>
</body>
</html>
