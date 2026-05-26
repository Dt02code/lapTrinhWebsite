<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');
$products = [];

if ($q !== '') {
    $stmt = db()->prepare(
        'SELECT sp.*, dm.slug AS danh_muc_slug, dm.ten AS danh_muc_ten
         FROM san_pham sp
         JOIN danh_muc dm ON sp.danh_muc_id = dm.id
         WHERE sp.ten LIKE ? OR sp.mo_ta LIKE ?
         ORDER BY sp.ten'
    );
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
    $products = $stmt->fetchAll();
}

$pageTitle = 'Tìm kiếm: ' . $q;
$activeNav = 'home';
require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 class="page-title">Kết quả: "<?= htmlspecialchars($q) ?>"</h1>
    <div class="product-grid">
        <?php foreach ($products as $p): ?>
            <?= render_product_card($p) ?>
        <?php endforeach; ?>
        <?php if ($q !== '' && $products === []): ?>
            <p class="empty-state">Không tìm thấy sản phẩm.</p>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
