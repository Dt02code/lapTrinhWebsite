<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Trang chủ';
$activeNav = 'home';

try {
    $featured = get_products(null, true);
    $phones = get_products('dien-thoai');
    $laptops = get_products('laptop');
} catch (PDOException $e) {
    $dbError = true;
    $featured = $phones = $laptops = [];
}

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($dbError)): ?>
<div class="container">
    <div class="alert alert-error">
        Chưa kết nối được database. Vui lòng import file SQL trong thư mục <code>database/</code> rồi cấu hình <code>config/database.php</code>.
    </div>
</div>
<?php endif; ?>

<section class="container hero">
    <div>
        <h1>Điện thoại & Laptop chính hãng</h1>
        <p>Giá tốt mỗi ngày — Trả góp 0% — Giao nhanh toàn quốc. Mua không cần đăng nhập!</p>
        <div class="hero-actions">
            <a href="dien-thoai.php" class="btn btn-primary">Xem điện thoại</a>
            <a href="laptop.php" class="btn btn-outline">Xem laptop</a>
        </div>
    </div>
    <div class="hero-banner" aria-hidden="true">📱💻</div>
</section>

<div class="container quick-links">
    <a href="dien-thoai.php" class="quick-link"><span class="icon">🔥</span>Deal điện thoại</a>
    <a href="laptop.php" class="quick-link"><span class="icon">💻</span>Laptop giảm giá</a>
    <a href="gio-hang.php" class="quick-link"><span class="icon">🛒</span>Giỏ hàng</a>
    <a href="thanh-toan.php" class="quick-link"><span class="icon">💳</span>Thanh toán</a>
</div>

<section class="container section">
    <div class="section-header">
        <h2>Sản phẩm nổi bật</h2>
        <a href="dien-thoai.php">Xem tất cả →</a>
    </div>
    <div class="product-grid">
        <?php foreach ($featured as $p): ?>
            <?= render_product_card($p) ?>
        <?php endforeach; ?>
        <?php if ($featured === [] && empty($dbError)): ?>
            <p class="empty-state">Chưa có sản phẩm nổi bật.</p>
        <?php endif; ?>
    </div>
</section>

<section class="container section">
    <div class="section-header">
        <h2>📱 Điện thoại</h2>
        <a href="dien-thoai.php">Xem tất cả →</a>
    </div>
    <div class="product-grid">
        <?php foreach (array_slice($phones, 0, 4) as $p): ?>
            <?= render_product_card($p) ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="container section">
    <div class="section-header">
        <h2>💻 Laptop</h2>
        <a href="laptop.php">Xem tất cả →</a>
    </div>
    <div class="product-grid">
        <?php foreach (array_slice($laptops, 0, 4) as $p): ?>
            <?= render_product_card($p) ?>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
