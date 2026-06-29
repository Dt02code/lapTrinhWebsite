<?php
session_start();
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Gọi thư viện PHPMailer
require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập một địa chỉ email hợp lệ trước khi gửi mã!']);
    exit;
}

$otp = rand(100000, 999999);
$_SESSION['otp_code'] = (string)$otp;
$_SESSION['otp_email'] = $email;
$_SESSION['otp_time'] = time();

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // ==========================================
    // CẬP NHẬT EMAIL VÀ MẬT KHẨU APP PASSWORD TẠI ĐÂY
    // Gmail hiện không chấp nhận mật khẩu tài khoản thông thường.
    // Bạn cần: 1) bật 2 bước xác thực, 2) tạo App Password cho Mail.
    // ==========================================
    $mail->Username   = 'lapmo843@gmail.com';
    $mail->Password   = 'pjpn jdvm ufvd cyjx';

    if ($mail->Username === 'YOUR_GMAIL@gmail.com') {
        echo json_encode([
            'success' => true,
            'message' => 'Đã gửi mã! (Chế độ TEST: Mã OTP của bạn là ' . $otp . ')'
        ]);
        exit;
    }

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom($mail->Username, 'CellPhoneK System');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Mã xác thực đăng ký CellPhoneK';
    $mail->Body    = "<h2>Chào bạn,</h2>
                      <p>Mã xác thực (OTP) đăng ký tài khoản của bạn là: <strong style='font-size:24px;color:#d70018;'>{$otp}</strong></p>
                      <p>Mã này có hiệu lực trong 5 phút. Vui lòng không chia sẻ mã này cho bất kỳ ai.</p>";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Đã gửi mã OTP thành công! Vui lòng kiểm tra Hộp thư đến (hoặc Thư rác) trong Gmail của bạn.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi không thể gửi email: ' . $mail->ErrorInfo]);
}
?>
