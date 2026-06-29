<?php

/**
 * LOGIN.PHP - Giữ nguyên giao diện gốc, thêm luồng OTP
 */

/*
session_start();
require '../connect.php';

if (isset($_SESSION['user_id'])) {
header('Location: ../index.php');
exit;
}

$error = '';

// XỬ LÝ ĐĂNG NHẬP + GỬI OTP
if (isset($_POST['btn'])) {
$u = trim($_POST['user'] ?? '');
$p = trim($_POST['pass'] ?? '');

if ($u === '' || $p === '') {
$error = 'Vui lòng nhập đầy đủ tài khoản và mật khẩu.';
} else {
$stmt = $conn->prepare(
"SELECT MaKhachHang, TenDangNhap, HoTen, MatKhau, Email
FROM khachhang WHERE TenDangNhap = ? LIMIT 1"
);
$stmt->bind_param('s', $u);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || $user['MatKhau'] !== $p) {
$error = 'Sai tài khoản hoặc mật khẩu!';
} elseif (empty($user['Email'])) {
// Không có email → đăng nhập thẳng không OTP
$_SESSION['user_id'] = $user['MaKhachHang'];
$_SESSION['username'] = $user['HoTen'] ?: $user['TenDangNhap'];
$_SESSION['user'] = $user['TenDangNhap'];
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$redirect = '../index.php';
if (isset($_GET['redirect'])) {
$rd = basename($_GET['redirect']);
if (in_array($rd, ['checkout.php','cart.php','profile.php'])) $redirect = $rd;
}
echo "<script>
    alert('Chào mừng " . htmlspecialchars($user['
        HoTen '] ?: $u, ENT_QUOTES) . "!');
    window.location = '$redirect';
</script>";
exit;
} else {
// Credentials hợp lệ → Tạo OTP và gửi email
$otp = (string)random_int(100000, 999999);
$expiredAt = date('Y-m-d H:i:s', time() + 300);

// Vô hiệu hóa OTP cũ
$conn->query("UPDATE otp_logs SET is_used=1 WHERE ma_khach={$user['MaKhachHang']} AND otp_type='login' AND is_used=0");

// Lưu OTP mới
$ins = $conn->prepare(
"INSERT INTO otp_logs (ma_khach, otp_code, otp_type, expired_at, ip_address)
VALUES (?, ?, 'login', ?, ?)"
);
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ins->bind_param('isss', $user['MaKhachHang'], $otp, $expiredAt, $ip);
$ins->execute();
$ins->close();

// Gửi email OTP qua PHPMailer
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
$sent = false;
try {
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'vonambang123@gmail.com';
$mail->Password = 'jacdzjsdcyssaumq';
$mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
$mail->Port = 465;
$mail->CharSet = 'UTF-8';
$mail->setFrom($mail->Username, 'CellPhoneK');
$mail->addAddress($user['Email'], $user['HoTen'] ?? $u);
$mail->isHTML(true);
$mail->Subject = 'Mã xác thực đăng nhập CellPhoneK';
$mail->Body = "<h2>Xin chào {$user['HoTen']},</h2>
<p>Mã OTP đăng nhập của bạn là: <strong style='font-size:28px;color:#d70018;letter-spacing:4px;'>{$otp}</strong></p>
<p>Mã có hiệu lực trong <strong>5 phút</strong>. Vui lòng không chia sẻ mã này.</p>";
$mail->send();
$sent = true;
} catch (\Exception $e) {
$sent = false;
}

if ($sent) {
// Lưu session tạm chờ OTP
$_SESSION['otp_pending_id'] = $user['MaKhachHang'];
$_SESSION['otp_pending_name'] = $user['HoTen'] ?: $user['TenDangNhap'];
$_SESSION['otp_pending_user'] = $user['TenDangNhap'];
$parts = explode('@', $user['Email']);
$n = $parts[0]; $masked = substr($n,0,min(2,strlen($n))) . str_repeat('*', max(0,strlen($n)-2)) . '@' . ($parts[1]??'');
$_SESSION['otp_email_masked'] = $masked;
$_SESSION['otp_expires'] = time() + 300;

$redirect = '';
if (isset($_GET['redirect'])) $redirect = '?redirect='.urlencode($_GET['redirect']);
header('Location: verify_otp.php' . $redirect);
exit;
} else {
$error = 'Không thể gửi email OTP. Vui lòng thử lại sau.';
}
}
}
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập – CellPhoneK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
</head>

<body>

    <!-- HEADER -->
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

    <!-- MAIN CONTENT-->
    <main class="page-main">
        <div class="container" style="max-width:440px; padding-top:32px; padding-bottom:32px; margin: 0 auto;">
            <h1 class="page-title">Đăng nhập</h1>
            <div class="card">
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
                    <p style="text-align: right; margin-top: -8px; margin-bottom: 16px; font-size: 13px;">
                        <a href="forgot_password.php" style="color: #6b7280;">Quên mật khẩu?</a>
                    </p>
                    <button type="submit" name="btn" class="btn btn-primary btn-block">Đăng nhập</button>
                </form>
                <p style="text-align:center; margin-top:16px; font-size:14px; color:#6b7280;">
                    🔐 Sau khi đăng nhập, mã OTP sẽ được gửi đến email của bạn để xác thực.
                </p>
                <p style="text-align:center; margin-top:12px; font-size:14px;">
                    Chưa có tài khoản? <a href="register.php" style="color:var(--brand); font-weight:600;">Đăng ký</a>
                </p>
                <p style="text-align:center; margin-top:8px; font-size:14px;">
                    <a href="../index.php">← Tiếp tục mua không đăng nhập</a>
                </p>
            </div>
        </div>
    </main>

    <!-- FOOTER-->
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

</html>
*/

/**
 * LOGIN.PHP - Đăng nhập bằng Tên đăng nhập HOẶC Email + Xác thực OTP
 */
session_start();
require '../connect.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = '';

// XỬ LÝ ĐĂNG NHẬP + GỬI OTP
if (isset($_POST['btn'])) {
    $account_input = trim($_POST['account'] ?? ''); // Ô nhập liệu chung cho cả User và Email
    $p = trim($_POST['pass'] ?? '');

    if ($account_input === '' || $p === '') {
        $error = 'Vui lòng nhập đầy đủ tài khoản/email và mật khẩu.';
    } else {
        // ĐỔI CHỖ NÀY: Dùng OR để kiểm tra xem chuỗi nhập vào khớp với tên đăng nhập HOẶC email
        $stmt = $conn->prepare(
            "SELECT MaKhachHang, TenDangNhap, HoTen, MatKhau, Email
             FROM khachhang WHERE TenDangNhap = ? OR Email = ? LIMIT 1"
        );
        // Truyền biến $account_input vào cả 2 dấu chấm hỏi
        $stmt->bind_param('ss', $account_input, $account_input);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user || $user['MatKhau'] !== $p) {
            $error = 'Sai tài khoản, email hoặc mật khẩu!';
        } elseif (empty($user['Email'])) {
            // Không có email → đăng nhập thẳng không OTP
            $_SESSION['user_id']  = $user['MaKhachHang'];
            $_SESSION['username'] = $user['HoTen'] ?: $user['TenDangNhap'];
            $_SESSION['user']     = $user['TenDangNhap'];
            $_SESSION['cart']     = $_SESSION['cart'] ?? [];
            $redirect = '../index.php';
            if (isset($_GET['redirect'])) {
                $rd = basename($_GET['redirect']);
                if (in_array($rd, ['checkout.php', 'cart.php', 'profile.php'])) $redirect = $rd;
            }
            echo "<script>alert('Chào mừng " . htmlspecialchars($user['HoTen'] ?: $account_input, ENT_QUOTES) . "!'); window.location='$redirect';</script>";
            exit;
        } else {
            // Tài khoản hợp lệ và có Email → Tạo OTP và gửi email
            $otp       = (string)random_int(100000, 999999);
            $expiredAt = date('Y-m-d H:i:s', time() + 300);

            // Vô hiệu hóa OTP cũ
            $conn->query("UPDATE otp_logs SET is_used=1 WHERE ma_khach={$user['MaKhachHang']} AND otp_type='login' AND is_used=0");

            // Lưu OTP mới
            $ins = $conn->prepare(
                "INSERT INTO otp_logs (ma_khach, otp_code, otp_type, expired_at, ip_address)
                 VALUES (?, ?, 'login', ?, ?)"
            );
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ins->bind_param('isss', $user['MaKhachHang'], $otp, $expiredAt, $ip);
            $ins->execute();
            $ins->close();

            // Gửi email OTP qua PHPMailer
            require_once '../PHPMailer/src/Exception.php';
            require_once '../PHPMailer/src/PHPMailer.php';
            require_once '../PHPMailer/src/SMTP.php';
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $sent = false;
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'vonambang123@gmail.com';
                $mail->Password   = 'jacdzjsdcyssaumq';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';
                $mail->setFrom($mail->Username, 'CellPhoneK');
                $mail->addAddress($user['Email'], $user['HoTen'] ?? $account_input);
                $mail->isHTML(true);
                $mail->Subject = 'Mã xác thực đăng nhập CellPhoneK';
                $mail->Body    = "<h2>Xin chào {$user['HoTen']},</h2>
                    <p>Mã OTP đăng nhập của bạn là: <strong style='font-size:28px;color:#d70018;letter-spacing:4px;'>{$otp}</strong></p>
                    <p>Mã có hiệu lực trong <strong>5 phút</strong>. Vui lòng không chia sẻ mã này.</p>";
                $mail->send();
                $sent = true;
            } catch (\Exception $e) {
                $sent = false;
            }

            if ($sent) {
                // Lưu session tạm chờ OTP
                $_SESSION['otp_pending_id']   = $user['MaKhachHang'];
                $_SESSION['otp_pending_name'] = $user['HoTen'] ?: $user['TenDangNhap'];
                $_SESSION['otp_pending_user'] = $user['TenDangNhap'];
                $parts = explode('@', $user['Email']);
                $n = $parts[0];
                $masked = substr($n, 0, min(2, strlen($n))) . str_repeat('*', max(0, strlen($n) - 2)) . '@' . ($parts[1] ?? '');
                $_SESSION['otp_email_masked'] = $masked;
                $_SESSION['otp_expires']      = time() + 300;

                $redirect = '';
                if (isset($_GET['redirect'])) $redirect = '?redirect=' . urlencode($_GET['redirect']);
                header('Location: verify_otp.php' . $redirect);
                exit;
            } else {
                $error = 'Không thể gửi email OTP. Vui lòng thử lại sau.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập – CellPhoneK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
</head>

<body>

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
                
            </div>
        </div>
    </header>

    <main class="page-main">
        <div class="container" style="max-width:440px; padding-top:32px; padding-bottom:32px; margin: 0 auto;">
            <h1 class="page-title">Đăng nhập</h1>
            <div class="card">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Tài khoản hoặc Email</label>
                        <input type="text" name="account" required value="<?= htmlspecialchars($_POST['account'] ?? '') ?>" placeholder="Nhập tên đăng nhập hoặc email">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="pass" required placeholder="Nhập mật khẩu">
                    </div>

                    <p style="text-align: right; margin-top: -8px; margin-bottom: 16px; font-size: 13px;">
                        <a href="forgot_password.php" style="color: #6b7280; text-decoration: none;">Quên mật khẩu?</a>
                    </p>

                    <button type="submit" name="btn" class="btn btn-primary btn-block">Đăng nhập</button>
                </form>
                <p style="text-align:center; margin-top:16px; font-size:14px; color:#6b7280;">
                    🔐 Sau khi đăng nhập, mã OTP sẽ được gửi đến email của bạn để xác thực.
                </p>
                <p style="text-align:center; margin-top:12px; font-size:14px;">
                    Chưa có tài khoản? <a href="register.php" style="color:var(--brand); font-weight:600;">Đăng ký</a>
                </p>
                <p style="text-align:center; margin-top:8px; font-size:14px;">
                    <a href="../index.php">← Tiếp tục mua không đăng nhập</a>
                </p>
            </div>
        </div>
    </main>

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

</body>

</html>