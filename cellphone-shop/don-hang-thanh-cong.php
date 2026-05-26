<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$orderId = (int) ($_GET['id'] ?? 0);
$pageTitle = 'Đặt hàng thành công';
$activeNav = 'cart';

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="card" style="text-align:center;padding:40px 20px;">
        <div style="font-size:48px;margin-bottom:12px;">✅</div>
        <h1 style="margin-bottom:8px;">Cảm ơn bạn!</h1>
        <p>Đơn hàng <?= $orderId > 0 ? '#' . $orderId : '' ?> đã được ghi nhận.</p>
        <p style="color:var(--muted);margin:12px 0;font-size:14px;">Chúng tôi sẽ liên hệ xác nhận trong thời gian sớm nhất.</p>
        <a href="index.php" class="btn btn-primary" style="margin-top:16px;">Về trang chủ</a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
