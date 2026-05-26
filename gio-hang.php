<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($action === 'add' && $productId > 0) {
        add_to_cart($productId, max(1, (int) ($_POST['qty'] ?? 1)));
        flash('success', 'Đã thêm sản phẩm vào giỏ!');
        redirect($_SERVER['HTTP_REFERER'] ?? 'gio-hang.php');
    }
    if ($action === 'update' && $productId > 0) {
        update_cart_qty($productId, (int) ($_POST['qty'] ?? 0));
        redirect('gio-hang.php');
    }
    if ($action === 'remove' && $productId > 0) {
        update_cart_qty($productId, 0);
        flash('success', 'Đã xóa sản phẩm khỏi giỏ.');
        redirect('gio-hang.php');
    }
}

$pageTitle = 'Giỏ hàng';
$activeNav = 'cart';
$items = get_cart_items();
$total = cart_total();

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 class="page-title">🛒 Giỏ hàng</h1>

    <?php if ($items === []): ?>
        <div class="card empty-state">
            <p>Giỏ hàng trống.</p>
            <a href="index.php" class="btn btn-primary" style="margin-top:16px;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="card" style="overflow-x:auto;">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>SL</th>
                        <th>Tạm tính</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <a href="san-pham.php?id=<?= (int) $item['id'] ?>" style="display:flex;align-items:center;gap:10px;">
                                <img src="<?= htmlspecialchars($item['hinh_anh']) ?>" alt="" onerror="this.src='assets/img/placeholder.svg'">
                                <?= htmlspecialchars($item['ten']) ?>
                            </a>
                        </td>
                        <td><?= format_price($item['gia']) ?></td>
                        <td>
                            <form method="post" style="display:flex;gap:4px;align-items:center;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <input type="number" name="qty" value="<?= (int) $item['so_luong'] ?>" min="1" style="width:60px;padding:6px;">
                                <button type="submit" class="btn btn-sm btn-outline-dark">OK</button>
                            </form>
                        </td>
                        <td><?= format_price($item['thanh_tien']) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <button type="submit" class="btn btn-sm" style="color:var(--brand);">Xóa</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="cart-summary">Tổng: <strong><?= format_price($total) ?></strong></p>
            <a href="thanh-toan.php" class="btn btn-primary btn-block" style="margin-top:16px;">Thanh toán</a>
        </div>
        <p style="text-align:center;font-size:13px;color:var(--muted);margin-top:8px;">
            Bạn không cần đăng nhập để mua hàng. Đăng ký chỉ khi muốn lưu lịch sử đơn.
        </p>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
