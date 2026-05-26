<?php
declare(strict_types=1);
/** @var string $pageTitle */
/** @var string $activeNav */
$pageTitle = $pageTitle ?? SITE_NAME;
$activeNav = $activeNav ?? 'home';
$user = current_user();
$cartBadge = cart_count();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="top-bar">
        <div class="container top-bar__inner">
            <span>Miễn phí vận chuyển đơn từ 300.000₫</span>
            <span>Hàng chính hãng 100%</span>
            <span class="hide-mobile">Hotline: <strong>1800.2097</strong></span>
        </div>
    </div>
    <div class="main-header">
        <div class="container main-header__inner">
            <a href="index.php" class="logo">phone<span>S</span></a>
            <nav class="desktop-nav hide-mobile">
                <a href="index.php">Trang chủ</a>
                <a href="dien-thoai.php">Điện thoại</a>
                <a href="laptop.php">Laptop</a>
            </nav>
            <form class="search-form" action="tim-kiem.php" method="get">
                <input type="search" name="q" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Tìm kiếm">
                <button type="submit" aria-label="Tìm">🔍</button>
            </form>
            <div class="header-actions">
                <a href="gio-hang.php" class="header-cart">
                    🛒 Giỏ hàng
                    <?php if ($cartBadge > 0): ?>
                        <span class="badge"><?= $cartBadge ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($user): ?>
                    <span class="user-greeting hide-mobile">Xin chào, <?= htmlspecialchars($user['ho_ten']) ?></span>
                    <a href="dang-xuat.php" class="btn btn-outline btn-sm hide-mobile">Đăng xuất</a>
                <?php else: ?>
                    <a href="dang-nhap.php" class="btn btn-outline btn-sm hide-mobile">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<?php if ($msg = flash('success')): ?>
    <div class="alert alert-success container"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
    <div class="alert alert-error container"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<main class="page-main">
