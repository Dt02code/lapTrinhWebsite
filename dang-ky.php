<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

if (is_logged_in()) {
    redirect('tai-khoan.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['ho_ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $sdt = trim($_POST['so_dien_thoai'] ?? '');
    $password = $_POST['mat_khau'] ?? '';
    $confirm = $_POST['xac_nhan'] ?? '';

    if ($hoTen === '' || $email === '' || strlen($password) < 6) {
        flash('error', 'Vui lòng điền đủ thông tin. Mật khẩu tối thiểu 6 ký tự.');
        redirect('dang-ky.php');
    }
    if ($password !== $confirm) {
        flash('error', 'Mật khẩu xác nhận không khớp.');
        redirect('dang-ky.php');
    }

    try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = db()->prepare(
            'INSERT INTO nguoi_dung (ho_ten, email, mat_khau, so_dien_thoai) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$hoTen, $email, $hash, $sdt ?: null]);
        $_SESSION['user_id'] = (int) db()->lastInsertId();
        flash('success', 'Đăng ký thành công!');
        redirect('index.php');
    } catch (PDOException $e) {
        flash('error', 'Email đã được sử dụng hoặc lỗi hệ thống.');
        redirect('dang-ky.php');
    }
}

$pageTitle = 'Đăng ký';
$activeNav = 'account';
require __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width:420px;">
    <h1 class="page-title">Đăng ký</h1>
    <div class="card">
        <p style="margin-bottom:16px;font-size:14px;color:var(--muted);">
            Chỉ đăng ký nếu bạn muốn lưu thông tin và theo dõi đơn hàng.
        </p>
        <form method="post">
            <div class="form-group">
                <label>Họ và tên</label>
                <input type="text" name="ho_ten" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" name="so_dien_thoai">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="mat_khau" required minlength="6">
            </div>
            <div class="form-group">
                <label>Xác nhận mật khẩu</label>
                <input type="password" name="xac_nhan" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Đăng ký</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:14px;">
            Đã có tài khoản? <a href="dang-nhap.php" style="color:var(--brand);font-weight:600;">Đăng nhập</a>
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
