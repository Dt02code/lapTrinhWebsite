<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Điện thoại';
$activeNav = 'phone';

try {
    $products = get_products('dien-thoai');
} catch (PDOException) {
    $products = [];
    $dbError = true;
}

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 class="page-title">📱 Điện thoại</h1>
    <?php if (!empty($dbError)): ?>
        <div class="alert alert-error">Lỗi kết nối database. Kiểm tra file SQL đã import chưa.</div>
    <?php endif; ?>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <?= render_product_card($p) ?>
        <?php endforeach; ?>
        <?php if ($products === []): ?>
            <p class="empty-state">Chưa có điện thoại trong CSDL.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
