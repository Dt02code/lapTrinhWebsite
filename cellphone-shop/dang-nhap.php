<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('tai-khoan.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['mat_khau'] ?? '';

    $stmt = db()->prepare('SELECT * FROM nguoi_dung WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mat_khau'])) {
        $_SESSION['user_id'] = $user['id'];
        flash('success', 'Đăng nhập thành công!');
        redirect($_GET['redirect'] ?? 'index.php');
    }
    flash('error', 'Email hoặc mật khẩu không đúng.');
}

$pageTitle = 'Đăng nhập';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:420px;">
    <h1 class="page-title">Đăng nhập</h1>
    <div class="card">
        <p style="margin-bottom:16px;font-size:14px;color:var(--muted);">
            <strong>Không bắt buộc.</strong> Bạn có thể mua hàng mà không cần tài khoản.
        </p>
        <form method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="mat_khau" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:14px;">
            Chưa có tài khoản? <a href="dang-ky.php" style="color:var(--brand);font-weight:600;">Đăng ký</a>
        </p>
        <p style="text-align:center;margin-top:8px;">
            <a href="index.php">← Tiếp tục mua không đăng nhập</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
