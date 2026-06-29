<?php
session_start();
require '../connect.php';

if (isset($_POST['btn'])) {
    $u     = trim($_POST['user']  ?? '');
    $p     = trim($_POST['pass']  ?? '');
    $e     = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $otp   = trim($_POST['otp'] ?? '');

    $messages = [];
    
    if (!isset($_SESSION['otp_code']) || $otp !== $_SESSION['otp_code'] || $e !== $_SESSION['otp_email']) {
        $messages[] = 'Mã xác thực (OTP) không chính xác hoặc không khớp với Email đã nhận mã!';
    }

    // Dùng Prepared Statements – chống SQL Injection
    $chkUser = $conn->prepare("SELECT 1 FROM khachhang WHERE TenDangNhap = ? LIMIT 1");
    $chkUser->bind_param('s', $u);
    $chkUser->execute();
    if ($chkUser->get_result()->num_rows > 0) {
        $messages[] = 'Tên đăng nhập này đã được đăng ký.';
    }
    $chkUser->close();

    $chkEmail = $conn->prepare("SELECT 1 FROM khachhang WHERE Email = ? LIMIT 1");
    $chkEmail->bind_param('s', $e);
    $chkEmail->execute();
    if ($chkEmail->get_result()->num_rows > 0) {
        $messages[] = 'Email này đã được đăng ký.';
    }
    $chkEmail->close();

    $chkPhone = $conn->prepare("SELECT 1 FROM khachhang WHERE SoDienThoai = ? LIMIT 1");
    $chkPhone->bind_param('s', $phone);
    $chkPhone->execute();
    if ($chkPhone->get_result()->num_rows > 0) {
        $messages[] = 'Số điện thoại này đã được đăng ký.';
    }
    $chkPhone->close();

    if (!empty($messages)) {
        $error = implode(' ', $messages);
    } else {
        $ins = $conn->prepare("INSERT INTO khachhang (TenDangNhap, MatKhau, Email, SoDienThoai) VALUES (?, ?, ?, ?)");
        $ins->bind_param('ssss', $u, $p, $e, $phone);
        if ($ins->execute()) {
            echo "<script>alert('Đăng ký thành công!'); window.location='login.php';</script>";
            exit;
        } else {
            $error = 'Lỗi đăng ký: ' . $conn->error;
        }
        $ins->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký – CellPhoneK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
</head>
<body>

<!-- ===== HEADER ===== -->
<header class="site-header">
    <div class="top-bar">
        <div class="container top-bar__inner">
            <span>Miễn phí vận chuyển đơn từ 300.000đ</span>
            <span>Hàng chính hãng 100%</span>
            <span class="hide-mobile">Hotline: <strong>1800.2097</strong></span>
        </div>
    </div>
    <div class="main-header">
        <div class="container main-header__inner">
            <a href="../index.php" class="logo">CellPhone<span>K</span></a>
            <nav class="desktop-nav hide-mobile">
                <a href="../index.php">Trang chủ</a>
                <a href="../index.php">Sản phẩm</a>
                <a href="cart.php">Giỏ hàng</a>
            </nav>
            <div class="header-actions">
                <a href="cart.php" class="header-cart">&#128722; Giỏ hàng</a>
            </div>
        </div>
    </div>
</header>

<!-- ===== MAIN CONTENT ===== -->
<main class="page-main">
<div class="container" style="max-width:440px; padding-top:32px; padding-bottom:32px;">
    <h1 class="page-title">Đăng ký</h1>
    <div class="card">
        <p style="margin-bottom:16px; font-size:14px; color:var(--muted);">
            Tạo tài khoản để theo dõi đơn hàng và nhận ưu đãi.
        </p>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Tên đăng nhập</label>
                <input type="text" name="user" required value="<?= htmlspecialchars($_POST['user'] ?? '') ?>" placeholder="Nhập tên đăng nhập">
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="pass" required placeholder="Nhập mật khẩu">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="emailInput" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Nhập email">
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" id="phoneInput" name="phone" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
            </div>
            <div class="form-group" style="display:flex; gap:10px; align-items:flex-end;">
                <div style="flex:1;">
                    <label>Mã xác thực</label>
                    <input type="text" name="otp" required placeholder="Nhập mã xác thực (6 số)">
                </div>
                <button type="button" id="btnSendOtp" style="background:#e2e8f0; color:#334155; border:none; padding:10px 15px; border-radius:8px; font-weight:600; cursor:pointer; white-space:nowrap; height:41.6px;">Gửi mã</button>
            </div>
            
            <button type="submit" name="btn" class="btn btn-primary btn-block" style="margin-top:10px;">Xác nhận đăng ký</button>
        </form>
        <p style="text-align:center; margin-top:16px; font-size:14px;">
            Đã có tài khoản? <a href="login.php" style="color:var(--brand); font-weight:600;">Đăng nhập ngay</a>
        </p>
        <p style="text-align:center; margin-top:8px; font-size:14px;">
            <a href="../index.php">← Quay về trang chủ</a>
        </p>
    </div>
</div>
</main>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4>CellPhoneK</h4>
            <p>Website bán điện thoại &amp; laptop chính hãng.</p>
        </div>
        <div>
            <h4>Danh mục</h4>
            <a href="../index.php">Điện thoại</a>
            <a href="../index.php">Laptop</a>
        </div>
        <div>
            <h4>Hỗ trợ</h4>
            <a href="cart.php">Giỏ hàng</a>
            <a href="checkout.php">Thanh toán</a>
        </div>
    </div>
    <p class="footer-copy">© 2026 CellPhoneK. All rights reserved.</p>
</footer>

<nav class="bottom-nav" aria-label="Menu chinh">
    <a href="../index.php"><span class="icon">&#127968;</span><span>Trang chu</span></a>
    <a href="../index.php"><span class="icon">&#128241;</span><span>San pham</span></a>
    <a href="../pages/cart.php"><span class="icon">&#128722;</span><span>Gio hang</span></a>
    <a href="<?= isset($_SESSION['user_id']) ? '../pages/profile.php' : '../pages/login.php' ?>"><span class="icon">&#128100;</span><span><?= isset($_SESSION['user_id']) ? 'Tai khoan' : 'Dang nhap' ?></span></a>
</nav>

</body>
<script>
document.getElementById('btnSendOtp').addEventListener('click', function() {
    var email = document.getElementById('emailInput').value.trim();
    if (!email) {
        alert("Vui lòng nhập Email trước khi gửi mã!");
        return;
    }
    
    var btn = this;
    btn.disabled = true;
    btn.textContent = "Đang gửi...";
    
    var formData = new FormData();
    formData.append('email', email);
    
    fetch('send_otp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            var count = 60;
            var timer = setInterval(function() {
                count--;
                btn.textContent = "Gửi lại (" + count + "s)";
                if (count <= 0) {
                    clearInterval(timer);
                    btn.disabled = false;
                    btn.textContent = "Gửi mã";
                }
            }, 1000);
        } else {
            alert("Lỗi: " + data.message);
            btn.disabled = false;
            btn.textContent = "Gửi mã";
        }
    })
    .catch(error => {
        alert("Lỗi kết nối đến máy chủ!");
        console.error(error);
        btn.disabled = false;
        btn.textContent = "Gửi mã";
    });
});
</script>
</html>
</html>
