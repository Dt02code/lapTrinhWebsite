<?php

session_start();
require '../connect.php';

if (!isset($_SESSION['otp_pending_id'])) {
    header('Location: login.php');
    exit;
}

$error        = '';
$success      = '';
$customerId   = (int)$_SESSION['otp_pending_id'];
$maskedEmail  = $_SESSION['otp_email_masked'] ?? '***@***.com';
$otpExpires   = $_SESSION['otp_expires'] ?? 0;
$secondsLeft  = max(0, $otpExpires - time());

// XÁC MINH OTP
if (isset($_POST['btn_verify'])) {
    $inputOtp = trim($_POST['otp_code'] ?? '');

    if (strlen($inputOtp) !== 6 || !ctype_digit($inputOtp)) {
        $error = 'Mã OTP phải là 6 chữ số.';
    } else {
        $currentTime = date('Y-m-d H:i:s');
        $stmt = $conn->prepare(
            "SELECT otp_id, attempts FROM otp_logs
             WHERE ma_khach = ? AND otp_code = ? AND otp_type = 'login'
               AND is_used = 0 AND expired_at > ?
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->bind_param('iss', $customerId, $inputOtp, $currentTime);
        $stmt->execute();
        $otp = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($otp) {
            $conn->query("UPDATE otp_logs SET is_used=1 WHERE otp_id={$otp['otp_id']}");

            $_SESSION['user_id']  = $customerId;
            $_SESSION['username'] = $_SESSION['otp_pending_name'];
            $_SESSION['user']     = $_SESSION['otp_pending_user'];
            $_SESSION['cart']     = $_SESSION['cart'] ?? [];

            $pendingName = $_SESSION['otp_pending_name'];
            unset($_SESSION['otp_pending_id'], $_SESSION['otp_pending_name'],
                  $_SESSION['otp_pending_user'], $_SESSION['otp_email_masked'],
                  $_SESSION['otp_expires']);

            $redirect = '../index.php';
            if (isset($_GET['redirect'])) {
                $rd = basename(urldecode($_GET['redirect']));
                if (in_array($rd, ['checkout.php','cart.php','profile.php'])) $redirect = $rd;
            }
            echo "<script>alert('Chào mừng " . htmlspecialchars($pendingName, ENT_QUOTES) . "!'); window.location='$redirect';</script>";
            exit;
        } else {
            // Tăng attempts
            $conn->query("UPDATE otp_logs SET attempts=attempts+1
                          WHERE ma_khach=$customerId AND otp_type='login' AND is_used=0
                          ORDER BY created_at DESC LIMIT 1");
            $rs = $conn->query("SELECT attempts FROM otp_logs
                                WHERE ma_khach=$customerId AND otp_type='login' AND is_used=0
                                ORDER BY created_at DESC LIMIT 1");
            $att = $rs ? $rs->fetch_assoc() : null;
            if ($att && $att['attempts'] >= 5) {
                $conn->query("UPDATE otp_logs SET is_used=1 WHERE ma_khach=$customerId AND otp_type='login' AND is_used=0");
                unset($_SESSION['otp_pending_id']);
                header('Location: login.php?error=locked');
                exit;
            }
            $error = 'Mã OTP không đúng hoặc đã hết hạn. Vui lòng thử lại.';
        }
    }
}

// GỬI LẠI OTP
if (isset($_GET['resend'])) {
    $rs  = $conn->query("SELECT created_at FROM otp_logs WHERE ma_khach=$customerId AND otp_type='login' ORDER BY created_at DESC LIMIT 1");
    $last = $rs ? $rs->fetch_assoc() : null;
    $canResend = !$last || (time() - strtotime($last['created_at'])) >= 60;

    if ($canResend) {
        require_once '../PHPMailer/src/Exception.php';
        require_once '../PHPMailer/src/PHPMailer.php';
        require_once '../PHPMailer/src/SMTP.php';

        $newOtp    = (string)random_int(100000, 999999);
        $expiredAt = date('Y-m-d H:i:s', time() + 300);
        $conn->query("UPDATE otp_logs SET is_used=1 WHERE ma_khach=$customerId AND otp_type='login' AND is_used=0");
        $ins = $conn->prepare("INSERT INTO otp_logs (ma_khach, otp_code, otp_type, expired_at) VALUES (?,?,'login',?)");
        $ins->bind_param('iss', $customerId, $newOtp, $expiredAt);
        $ins->execute(); $ins->close();

        $rs2 = $conn->query("SELECT Email, HoTen FROM khachhang WHERE MaKhachHang=$customerId LIMIT 1");
        $kh  = $rs2->fetch_assoc();

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP(); $mail->Host = 'smtp.gmail.com'; $mail->SMTPAuth = true;
            $mail->Username = 'vonambang123@gmail.com'; 
            $mail->Password = 'jacdzjsdcyssaumq';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465; 
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($mail->Username, 'CellPhoneK');
            $mail->addAddress($kh['Email'], $kh['HoTen']);
            $mail->isHTML(true);
            $mail->Subject = 'Mã OTP mới – CellPhoneK';
            $mail->Body = "<p>Mã OTP mới: <strong style='font-size:28px;color:#d70018;letter-spacing:4px;'>$newOtp</strong></p><p>Hiệu lực 5 phút.</p>";
            $mail->send();
        } catch (\Exception $e) {}

        $_SESSION['otp_expires'] = time() + 300;
        $secondsLeft = 300;
        $success = 'Đã gửi lại mã OTP mới qua email!';
    } else {
        $error = 'Vui lòng đợi ít nhất 60 giây trước khi gửi lại.';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh OTP – CellPhoneK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
    <style>
        .page-main-otp {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: calc(100vh - 300px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .otp-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            padding: 48px 40px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.8);
            transition: transform 0.3s ease;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }
        .otp-icon-wrapper {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
        }
        .otp-icon-wrapper svg {
            width: 36px;
            height: 36px;
            color: #fff;
        }
        .otp-title {
            font-size: 26px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 12px;
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.5px;
        }
        .otp-desc {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .otp-desc strong {
            color: #1f2937;
            font-weight: 600;
        }
        .otp-box-wrap { 
            display: flex; 
            gap: 12px; 
            justify-content: center; 
            margin: 0 0 28px; 
        }
        .otp-box-wrap input {
            width: 52px; 
            height: 64px; 
            text-align: center; 
            font-size: 28px; 
            font-weight: 700;
            border: 2px solid #e5e7eb; 
            border-radius: 14px; 
            outline: none;
            color: #111827; 
            background: #f9fafb;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            font-family: 'Inter', sans-serif;
        }
        .otp-box-wrap input:focus { 
            border-color: #ef4444; 
            background: #fff; 
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
            transform: translateY(-2px);
        }
        .otp-box-wrap input.filled { 
            border-color: #10b981; 
            background: #fff;
        }
        .otp-countdown { 
            font-size: 14px; 
            color: #6b7280; 
            margin-bottom: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .otp-countdown span { 
            color: #ef4444; 
            font-weight: 700;
            background: #fee2e2;
            padding: 4px 12px;
            border-radius: 20px;
            font-variant-numeric: tabular-nums;
        }
        .btn-verify {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.35);
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        .btn-verify svg {
            width: 20px;
            height: 20px;
        }
        .resend-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #6b7280;
            font-size: 15px;
            font-weight: 500;
            text-decoration: none;
            margin-top: 24px;
            transition: color 0.2s;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .resend-link:hover:not(:disabled) {
            color: #ef4444;
        }
        .resend-link:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .back-link {
            display: inline-block;
            margin-top: 28px;
            color: #9ca3af;
            font-size: 14px;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 500;
        }
        .back-link:hover {
            color: #4b5563;
        }
    </style>
</head>
<body>

<!--  HEADER  -->
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

<!-- MAIN CONTENT -->
<main class="page-main page-main-otp">
<div class="container">
    <div class="otp-card">
        <div class="otp-icon-wrapper">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
        </div>
        <h1 class="otp-title">Xác minh OTP</h1>
        <p class="otp-desc">
            Mã OTP 6 số đã được gửi đến email<br>
            <strong><?= htmlspecialchars($maskedEmail) ?></strong>
        </p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error" style="border-radius:12px; margin-bottom:20px; font-size:14px; display:flex; align-items:center; justify-content:center; gap:8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert" style="background:#d1fae5;color:#065f46;border:1px solid #34d399;border-radius:12px;padding:12px 16px;font-size:14px;margin-bottom:20px; display:flex; align-items:center; justify-content:center; gap:8px; font-weight:500;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="otpForm">
            <!-- 6 ô nhập OTP -->
            <div class="otp-box-wrap" id="otpBoxes">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="o1" autocomplete="one-time-code" autofocus>
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="o2">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="o3">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="o4">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="o5">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" id="o6">
            </div>
            <!-- Hidden field gộp 6 chữ số -->
            <input type="hidden" name="otp_code" id="otpHidden">

            <div class="otp-countdown">
                Mã hết hạn sau: <span id="timer"><?= sprintf('%02d:%02d', intdiv($secondsLeft,60), $secondsLeft%60) ?></span>
            </div>

            <button type="submit" name="btn_verify" class="btn-verify">
                Xác minh ngay
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </form>

        <form method="GET" style="display:inline-block; width:100%;">
            <?php if (isset($_GET['redirect'])): ?>
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($_GET['redirect']) ?>">
            <?php endif; ?>
            <button type="submit" name="resend" class="resend-link" id="btnResend"
                    <?= $secondsLeft > 240 ? 'disabled' : '' ?>>
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Gửi lại mã OTP
            </button>
        </form>

        <a href="login.php" class="back-link">
            &larr; Quay lại trang đăng nhập
        </a>
    </div>
</div>
</main>

<!-- FOOTER -->
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
    <a href="login.php"><span class="icon">&#128100;</span><span>Dang nhap</span></a>
</nav>

<script>
// Countdown 
let secs = <?= $secondsLeft ?>;
const timerEl  = document.getElementById('timer');
const btnResend = document.getElementById('btnResend');
const cd = setInterval(() => {
    secs--;
    if (secs <= 0) {
        clearInterval(cd);
        timerEl.textContent = '00:00';
        timerEl.style.color = '#9ca3af';
        btnResend.disabled = false;
    } else {
        const m = String(Math.floor(secs/60)).padStart(2,'0');
        const s = String(secs%60).padStart(2,'0');
        timerEl.textContent = `${m}:${s}`;
        if (secs <= 60) btnResend.disabled = false;
    }
}, 1000);

// ô OIP
const boxes = document.querySelectorAll('#otpBoxes input');
boxes.forEach((box, i) => {
    box.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g,'').slice(-1);
        this.classList.toggle('filled', this.value !== '');
        if (this.value && i < boxes.length - 1) boxes[i+1].focus();
        updateHidden();
    });
    box.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && i > 0) boxes[i-1].focus();
    });
    box.addEventListener('paste', function(e) {
        e.preventDefault();
        const txt = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'');
        [...txt.slice(0,6)].forEach((ch, j) => {
            if (boxes[j]) { boxes[j].value = ch; boxes[j].classList.add('filled'); }
        });
        boxes[Math.min(txt.length, 5)].focus();
        updateHidden();
    });
});
boxes[0].focus();

function updateHidden() {
    document.getElementById('otpHidden').value = [...boxes].map(b => b.value).join('');
}

// Auto-submit khi đủ 6 số
function checkAutoSubmit() {
    if ([...boxes].every(b => b.value !== '')) {
        updateHidden();
        setTimeout(() => document.querySelector('[name=btn_verify]').click(), 300);
    }
}
boxes.forEach(b => b.addEventListener('input', checkAutoSubmit));

document.getElementById('otpForm').addEventListener('submit', function() {
    updateHidden();
});
</script>
</body>
</html>
