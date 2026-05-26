<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

if (!is_logged_in()) {
    redirect('dang-nhap.php');
}

$user = current_user();
$orders = [];

try {
    $stmt = db()->prepare(
        'SELECT * FROM don_hang WHERE nguoi_dung_id = ? ORDER BY created_at DESC LIMIT 20'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException) {
    // ignore
}

$pageTitle = 'Tài khoản';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 class="page-title">👤 Tài khoản</h1>
    <div class="card">
        <p><strong><?= htmlspecialchars($user['ho_ten']) ?></strong></p>
        <p style="color:var(--muted);font-size:14px;"><?= htmlspecialchars($user['email']) ?></p>
        <?php if ($user['so_dien_thoai']): ?>
            <p style="font-size:14px;">📞 <?= htmlspecialchars($user['so_dien_thoai']) ?></p>
        <?php endif; ?>
        <a href="dang-xuat.php" class="btn btn-outline-dark btn-sm" style="margin-top:12px;">Đăng xuất</a>
    </div>

    <h2 style="margin:20px 0 12px;font-size:1.1rem;">Đơn hàng của bạn</h2>
    <?php if ($orders === []): ?>
        <div class="card empty-state">Chưa có đơn hàng nào.</div>
    <?php else: ?>
        <?php foreach ($orders as $o): ?>
            <div class="card" style="font-size:14px;">
                <strong>Đơn #<?= (int) $o['id'] ?></strong>
                — <?= format_price($o['tong_tien']) ?>
                <br>
                <span style="color:var(--muted);"><?= htmlspecialchars($o['created_at']) ?></span>
                — TT: <?= htmlspecialchars($o['trang_thai']) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
