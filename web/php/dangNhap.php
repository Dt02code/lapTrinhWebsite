<?php
require 'connect.php';
session_start();

if (isset($_POST['btn'])) {
    $u = $_POST['user'];
    $p = $_POST['pass'];

    $sql = "SELECT * FROM khachhang WHERE TenDangNhap='$u' AND MatKhau='$p'";
    $kq = mysqli_query($conn, $sql);

    if (mysqli_num_rows($kq) > 0) {
        $_SESSION['user'] = $u;
        echo "<script>alert('Chào mừng bạn quay lại!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Sai tài khoản hoặc mật khẩu!');</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Đăng nhập hệ thống</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <form method="POST">
        <h2 style="color:#3498db">ĐĂNG NHẬP</h2>
        <input type="text" name="user" placeholder="Tên đăng nhập" required>
        <input type="password" name="pass" placeholder="Mật khẩu" required>
        <button type="submit" name="btn" class="btn-blue">VÀO HỆ THỐNG</button>
        <p>Chưa có nick? <a href="dangKy.php">Đăng ký đi bạn</a></p>
    </form>
</body>

</html>