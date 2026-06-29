<?php
session_start();
require '../connect.php';
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$total = 0;
foreach ($_SESSION['cart'] as $item) $total += $item['price'] * $item['quantity'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng – CellPhoneK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
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

<div id="toastMessage" class="toast hidden"></div>

<main class="container cart-main">
    <h1 class="cart-title">🛒 Giỏ hàng của bạn
        <span class="cart-count-label">(<?= count($_SESSION['cart']) ?> sản phẩm)</span>
    </h1>

    <?php if (empty($_SESSION['cart'])): ?>
    <div class="cart-empty">
        <div class="empty-icon">🛒</div>
        <h2>Giỏ hàng trống!</h2>
        <p>Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm.</p>
        <a href="../index.php" class="btn btn-primary" style="display:inline-block; margin-top: 12px; padding: 10px 20px; font-weight: 600;">Khám phá sản phẩm</a>
    </div>
    <?php else: ?>
    <div class="cart-layout">
        <div class="cart-table-wrap">
            <table class="cart-table" id="cartTable">
                <thead>
                    <tr>
                        <th colspan="2">Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Xóa</th>
                    </tr>
                </thead>
                <tbody id="cartBody">
                    <?php foreach ($_SESSION['cart'] as $key => $item): ?>
                    <tr class="cart-row" id="row-<?= htmlspecialchars($key) ?>">
                        <td class="cart-img-cell">
                            <img src="../<?= htmlspecialchars($item['image']) ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 class="cart-product-img"
                                 onerror="this.src='../img/no-image.svg'">
                        </td>
                        <td class="cart-name-cell">
                            <a href="detail.php?id=<?= $item['product_id'] ?>" class="cart-product-name">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                            <div class="cart-variant">
                                <span>🎨 <?= htmlspecialchars($item['color']) ?></span>
                                <span>💾 <?= htmlspecialchars($item['ram']) ?></span>
                            </div>
                        </td>
                        <td class="cart-price-cell" data-price="<?= $item['price'] ?>">
                            <?= number_format($item['price'], 0, ',', '.') ?>đ
                        </td>
                        <td class="cart-qty-cell">
                            <div class="qty-control-mini">
                                <button class="qty-btn-mini" onclick="updateCartQty('<?= htmlspecialchars($key, ENT_QUOTES) ?>', -1)">−</button>
                                <input type="number" class="qty-input-mini"
                                       id="qty-<?= htmlspecialchars($key) ?>"
                                       value="<?= (int)$item['quantity'] ?>"
                                       min="1" max="<?= (int)$item['stock'] ?>"
                                       data-key="<?= htmlspecialchars($key, ENT_QUOTES) ?>"
                                       onchange="onQtyChange(this)">
                                <button class="qty-btn-mini" onclick="updateCartQty('<?= htmlspecialchars($key, ENT_QUOTES) ?>', 1)">+</button>
                            </div>
                            <div class="stock-note">Còn <?= (int)$item['stock'] ?> sp</div>
                        </td>
                        <td class="cart-subtotal-cell" id="subtotal-<?= htmlspecialchars($key) ?>">
                            <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                        </td>
                        <td class="cart-remove-cell">
                            <button class="btn-remove-cart"
                                    onclick="removeCartItem('<?= htmlspecialchars($key, ENT_QUOTES) ?>')"
                                    title="Xóa sản phẩm">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-summary">
            <div class="summary-card">
                <h3 class="summary-title">📊 Tóm tắt đơn hàng</h3>
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span id="summarySubtotal"><?= number_format($total, 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span class="free-ship">Miễn phí</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Tổng cộng:</span>
                    <span id="summaryTotal"><?= number_format($total, 0, ',', '.') ?>đ</span>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="checkout.php" class="btn-checkout" id="btnCheckout">
                        💳 Tiến hành thanh toán
                    </a>
                <?php else: ?>
                    <div class="checkout-login-notice">
                        <p>⚠️ Vui lòng đăng nhập để thanh toán</p>
                        <a href="login.php?redirect=cart.php" class="btn-checkout-login">🔐 Đăng nhập ngay</a>
                    </div>
                <?php endif; ?>
                <a href="../index.php" class="btn-continue-shop">← Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

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



<script src="../js/shop.js"></script>
<nav class="bottom-nav" aria-label="Menu chinh">
    <a href="../index.php"><span class="icon">&#127968;</span><span>Trang chu</span></a>
    <a href="../index.php"><span class="icon">&#128241;</span><span>San pham</span></a>
    <a href="../pages/cart.php"><span class="icon">&#128722;</span><span>Gio hang</span></a>
    <a href="<?= isset($_SESSION['user_id']) ? '../pages/profile.php' : '../pages/login.php' ?>"><span class="icon">&#128100;</span><span><?= isset($_SESSION['user_id']) ? 'Tai khoan' : 'Dang nhap' ?></span></a>
</nav>
</body>
</html>
