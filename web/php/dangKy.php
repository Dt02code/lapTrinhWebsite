<?php
require 'connect.php';

if (isset($_POST['btn'])) {
    $u = $_POST['user'];
    $p = $_POST['pass'];
    $e = $_POST['email'];

    $sql = "INSERT INTO khachhang (TenDangNhap, MatKhau, Email) VALUES ('$u', '$p', '$e')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Đăng ký xong rồi đó!'); window.location='dangNhap.php';</script>";
    } else {
        echo "Lỗi: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Đăng ký thành viên</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <form method="POST">
        <h2 style="color:#e74c3c">ĐĂNG KÝ</h2>
        <input type="text" name="user" placeholder="Tên đăng nhập" required>
        <input type="password" name="pass" placeholder="Mật khẩu" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit" name="btn" class="btn-red">XÁC NHẬN ĐĂNG KÝ</button>
        <p>Đã có tài khoản? <a href="dangNhap.php">Đăng nhập ngay</a></p>
    </form>
</body>

</html>