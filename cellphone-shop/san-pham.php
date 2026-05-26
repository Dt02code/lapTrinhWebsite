<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('index.php');
}

try {
    $product = get_product_by_id($id);
} catch (PDOException) {
    $product = null;
}

if (!$product) {
    flash('error', 'Không tìm thấy sản phẩm.');
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    add_to_cart($id, max(1, (int) ($_POST['qty'] ?? 1)));
    flash('success', 'Đã thêm vào giỏ hàng!');
    redirect('gio-hang.php');
}

$pageTitle = $product['ten'];
$activeNav = $product['danh_muc_slug'] === 'laptop' ? 'laptop' : 'phone';

require __DIR__ . '/includes/header.php';
$img = htmlspecialchars($product['hinh_anh'], ENT_QUOTES, 'UTF-8');
?>

<div class="container product-detail">
    <div class="product-detail__image">
        <img src="<?= $img ?>" alt="<?= htmlspecialchars($product['ten']) ?>" onerror="this.src='assets/img/placeholder.svg'">
    </div>
    <div>
        <p class="muted" style="color:var(--muted);margin-bottom:8px;"><?= htmlspecialchars($product['danh_muc_ten']) ?></p>
        <h1 style="font-size:1.5rem;margin-bottom:12px;"><?= htmlspecialchars($product['ten']) ?></h1>
        <p class="price-current" style="font-size:1.75rem;margin-bottom:8px;"><?= format_price($product['gia']) ?></p>
        <?php if ($product['gia_cu']): ?>
            <p class="price-old"><?= format_price($product['gia_cu']) ?></p>
        <?php endif; ?>
        <p style="margin:16px 0;"><?= nl2br(htmlspecialchars($product['mo_ta'] ?? '')) ?></p>
        <?php if ($product['thong_so']): ?>
            <div class="specs">⚙️ <?= htmlspecialchars($product['thong_so']) ?></div>
        <?php endif; ?>
        <form method="post" style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="add">
            <label>Số lượng:
                <input type="number" name="qty" value="1" min="1" max="<?= (int) $product['ton_kho'] ?>" style="width:70px;margin-left:8px;padding:8px;">
            </label>
            <button type="submit" class="btn btn-primary">Thêm vào giỏ hàng</button>
            <a href="thanh-toan.php" class="btn btn-outline-dark">Mua ngay</a>
        </form>
        <p style="margin-top:12px;font-size:13px;color:var(--muted);">Còn <?= (int) $product['ton_kho'] ?> sản phẩm</p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
