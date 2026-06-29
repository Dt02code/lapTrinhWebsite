<?php

/**
 * FORGOT_PASSWORD.PHP - Yêu cầu đổi mật khẩu bằng Email
 */
session_start();
require '../connect.php';

$error = '';

if (isset($_POST['btn_forgot'])) {
    $email_input = trim($_POST['email'] ?? '');

    if ($email_input === '') {
        $error = 'Vui lòng nhập địa chỉ Email.';
    } else {
        // THAY ĐỔI: Tìm tài khoản bằng Email trong database
        $stmt = $conn->prepare("SELECT MaKhachHang, TenDangNhap, HoTen, Email FROM khachhang WHERE Email = ? LIMIT 1");
        $stmt->bind_param('s', $email_input);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $error = 'Email này không tồn tại trên hệ thống!';
        } else {
            // Tạo mã OTP quên mật khẩu (6 số)
            $otp = (string)random_int(100000, 999999);
            $expiredAt = date('Y-m-d H:i:s', time() + 300); // Có hiệu lực trong 5 phút

            // Hủy các OTP quên mật khẩu cũ chưa dùng của khách này
            $conn->query("UPDATE otp_logs SET is_used=1 WHERE ma_khach={$user['MaKhachHang']} AND otp_type='forgot' AND is_used=0");

            // Lưu OTP mới vào bảng dựa trên cột chuẩn otp_id
            $ins = $conn->prepare("INSERT INTO otp_logs (ma_khach, otp_code, otp_type, expired_at, ip_address) VALUES (?, ?, 'forgot', ?, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ins->bind_param('isss', $user['MaKhachHang'], $otp, $expiredAt, $ip);
            $ins->execute();
            $ins->close();

            // Gửi Email qua PHPMailer
            require_once '../PHPMailer/src/Exception.php';
            require_once '../PHPMailer/src/PHPMailer.php';
            require_once '../PHPMailer/src/SMTP.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'vonambang123@gmail.com'; // Email của bạn
                $mail->Password   = 'jacdzjsdcyssaumq';        // Mật khẩu ứng dụng gmail
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom($mail->Username, 'CellPhoneK');
                $mail->addAddress($user['Email'], $user['HoTen'] ?? $email_input);
                $mail->isHTML(true);

                $mail->Subject = 'Mã xác thực khôi phục mật khẩu CellPhoneK';
                $mail->Body    = "<h2>Xin chào {$user['HoTen']},</h2>
                                  <p>Bạn đã yêu cầu đặt lại mật khẩu. Mã OTP của bạn là: <strong style='font-size:28px;color:#d70018;letter-spacing:4px;'>{$otp}</strong></p>
                                  <p>Mã này có hiệu lực trong <strong>5 phút</strong>. Tuyệt đối không chia sẻ mã này cho bất kỳ ai.</p>";
                $mail->send();

                // Lưu ID khách vào session để trang reset_password.php lấy xử lý tiếp
                $_SESSION['forgot_user_id'] = $user['MaKhachHang'];

                echo "<script>alert('Mã OTP đã được gửi đến email của bạn.'); window.location='reset_password.php';</script>";
                exit;
            } catch (\Exception $e) {
                $error = 'Lỗi gửi email: ' . $mail->ErrorInfo;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu – CellPhoneK</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
</head>

<body>
    <main class="page-main" style="padding-top: 60px;">
        <div class="container" style="max-width:440px; margin: 0 auto;">
            <h1 class="page-title" style="text-align:center;">Quên mật khẩu</h1>
            <div class="card">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; border: 1px solid #fca5a5;">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Nhập Email tài khoản của bạn</label>
                        <input type="email" name="email" required placeholder="Ví dụ: nguyenvan@gmail.com">
                    </div>
                    <button type="submit" name="btn_forgot" class="btn btn-primary btn-block">Gửi mã xác thực (OTP)</button>
                </form>
                <p style="text-align:center; margin-top:16px; font-size:14px;">
                    <a href="login.php" style="color:var(--brand); font-weight:600;">← Quay lại đăng nhập</a>
                </p>
            </div>
        </div>
    </main>
</body>

</html>