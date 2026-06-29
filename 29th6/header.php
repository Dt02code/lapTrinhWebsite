<?php
// Header fragment: includes <head> and site header markup
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CellPhone Store – Mua điện thoại chính hãng</title>
    <meta name="description" content="Cửa hàng điện thoại chính hãng – Samsung, iPhone, Xiaomi, Oppo, Realme với giá tốt nhất.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/shop.css?v=<?= filemtime(__DIR__ . '/css/shop.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" />
    <?php /* Inline styles preserved when needed in page files */ ?>
</head>

<body>

    <header class="site-header">
        <div class="top-bar" style="background-color: #000000 !important; color: #ffffff !important; padding: 8px 0;">
            <div class="container top-bar__inner">
                <span style="color: #ffffff !important;">Miễn phí vận chuyển đơn từ 300.000đ</span>
                <span style="color: #ffffff !important;">Hàng chính hãng 100%</span>
                <span class="hide-mobile" style="color: #ffffff !important;">Hotline: <strong style="color: #ffffff !important;">1800.2097</strong></span>
            </div>
        </div>
        <div class="main-header">
            <div class="container main-header__inner">
                <a href="index.php" class="logo">CellPhone<span>K</span></a>

                <nav class="desktop-nav hide-mobile">
                    <a href="index.php" class="nav-link nav-active">Trang chủ</a>
                    <a href="index.php" class="nav-link">Sản phẩm</a>
                </nav>

                <!-- Phần chọn khu vực -->
                <form action="index.php" method="get" class="hide-mobile location-form">
                    <select name="location" class="location-select" onchange="this.form.submit()">
                        <option value="Hồ Chí Minh" <?php if (isset($location) && $location == "Hồ Chí Minh") echo "selected"; ?>>📍 Hồ Chí Minh</option>
                        <option value="Hà Nội" <?php if (isset($location) && $location == "Hà Nội") echo "selected"; ?>>📍 Hà Nội</option>
                        <option value="Đà Nẵng" <?php if (isset($location) && $location == "Đà Nẵng") echo "selected"; ?>>📍 Đà Nẵng</option>
                        <option value="Gia Lai" <?php if (isset($location) && $location == "Gia Lai") echo "selected"; ?>>📍 Gia Lai</option>
                        <option value="Nha Trang" <?php if (isset($location) && $location == "Nha Trang") echo "selected"; ?>>📍 Nha Trang</option>
                    </select>
                </form>

                <form class="search-form" action="index.php" method="get">
                    <input type="search" name="search" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Tìm kiếm" value="<?php echo isset($search) ? htmlspecialchars($search) : '' ?>">
                    <button type="submit" aria-label="Tìm" class="search-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </button>
                </form>
                <div class="header-actions">
                    <a href="pages/cart.php" class="header-cart">
                        Giỏ hàng
                        <?php
                        $__cartCount = 0;
                        if (!empty($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $__item) {
                                $__cartCount += $__item['quantity'];
                            }
                        }
                        if ($__cartCount > 0): ?>
                            <span class="cart-badge badge"><?= $__cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="pages/profile.php" class="btn btn-outline btn-sm hide-mobile"><?= htmlspecialchars($_SESSION['username'] ?? 'Tài khoản') ?></a>
                        <a href="pages/logout.php" class="btn btn-outline btn-sm btn-logout hide-mobile">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" y1="12" x2="9" y2="12" />
                            </svg>
                            Đăng xuất
                        </a>
                    <?php else: ?>
                        <a href="pages/login.php" class="btn btn-outline btn-sm hide-mobile">Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>