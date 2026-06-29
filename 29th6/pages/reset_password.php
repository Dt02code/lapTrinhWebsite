<?php
session_start();
require '../connect.php';

// Nếu không có session chờ đổi mật khẩu thì quay về trang quên mật khẩu
if (!isset($_SESSION['forgot_user_id'])) {
    header('Location: forgot_password.php');
    exit;
}

$error = '';

if (isset($_POST['btn_reset'])) {
    $otp_input = trim($_POST['otp'] ?? '');
    $new_pass  = trim($_POST['new_pass'] ?? '');
    $userId    = $_SESSION['forgot_user_id'];

    if ($otp_input === '' || $new_pass === '') {
        $error = 'Vui lòng nhập đầy đủ mã OTP và mật khẩu mới.';
    } elseif (strlen($new_pass) < 4) {
        $error = 'Mật khẩu mới phải từ 4 ký tự trở lên.';
    } else {
        // Kiểm tra OTP dựa trên cấu trúc chuẩn: cột otp_id thay vì id
        $now = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT otp_id FROM otp_logs WHERE ma_khach = ? AND otp_code = ? AND otp_type = 'forgot' AND is_used = 0 AND expired_at > ? LIMIT 1");
        $stmt->bind_param('iss', $userId, $otp_input, $now);
        $stmt->execute();
        $otp_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Kiểm tra an toàn xem mã nhập vào có khớp dữ liệu nào không
        if (!$otp_row || empty($otp_row['otp_id'])) {
            $error = 'Mã OTP không chính xác hoặc đã hết hạn!';
        } else {
            $otp_id = $otp_row['otp_id'];

            // 1. Đánh dấu OTP này đã được sử dụng theo cột otp_id
            $conn->query("UPDATE otp_logs SET is_used = 1 WHERE otp_id = {$otp_id}");

            // 2. Cập nhật mật khẩu mới vào bảng khách hàng (Trường MaKhachHang, MatKhau)
            $update = $conn->prepare("UPDATE khachhang SET MatKhau = ? WHERE MaKhachHang = ?");
            $update->bind_param('si', $new_pass, $userId);
            $update->execute();
            $update->close();

            // Xóa session tạm thời sau khi hoàn thành để bảo mật
            unset($_SESSION['forgot_user_id']);

            echo "<script>alert('Đổi mật khẩu thành công! Vui lòng đăng nhập lại.'); window.location='login.php';</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu – CellPhoneK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shop.css">
    <style>
        /* CSS tạo giao diện ô vuông nhập OTP */
        .otp-container {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-top: 8px;
            margin-bottom: 20px;
        }

        .otp-field {
            width: 48px;
            height: 48px;
            font-size: 22px;
            font-weight: 600;
            text-align: center;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            outline: none;
            transition: all 0.2s ease;
        }

        .otp-field:focus {
            border-color: #d70018;
            /* Màu đỏ thương hiệu CellPhoneK */
            box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.15);
        }

        /* CSS hộp thông báo lỗi */
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
            border: 1px solid #fca5a5;
        }
    </style>
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
                <div class="header-actions">
                    <a href="cart.php" class="header-cart">&#128722; Giỏ hàng</a>
                </div>
            </div>
        </div>
    </header>

    <main class="page-main">
        <div class="container" style="max-width:440px; padding-top:32px; padding-bottom:32px; margin: 0 auto;">
            <h1 class="page-title" style="text-align: center;">Đặt lại mật khẩu</h1>
            <div class="card">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="resetForm">
                    <div class="form-group">
                        <label style="display: block; text-align: center; margin-bottom: 8px; font-weight: 500;">Mã OTP (Xem trong Email)</label>

                        <div class="otp-container">
                            <input type="text" class="otp-field" maxlength="1" required pattern="\d*">
                            <input type="text" class="otp-field" maxlength="1" required pattern="\d*">
                            <input type="text" class="otp-field" maxlength="1" required pattern="\d*">
                            <input type="text" class="otp-field" maxlength="1" required pattern="\d*">
                            <input type="text" class="otp-field" maxlength="1" required pattern="\d*">
                            <input type="text" class="otp-field" maxlength="1" required pattern="\d*">
                        </div>

                        <input type="hidden" name="otp" id="real_otp">
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_pass" required placeholder="Nhập mật khẩu mới ít nhất 4 ký tự">
                    </div>

                    <button type="submit" name="btn_reset" class="btn btn-primary btn-block">Xác nhận thay đổi</button>
                </form>

                <p style="text-align:center; margin-top:16px; font-size:14px;">
                    <a href="forgot_password.php" style="color: #6b7280;">← Yêu cầu lại mã khác</a>
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

    <script>
        const fields = document.querySelectorAll('.otp-field');
        const realOtp = document.getElementById('real_otp');

        fields.forEach((field, index) => {
            // 1. Tự nhảy sang ô kế tiếp khi vừa gõ xong 1 ký tự số
            field.addEventListener('input', (e) => {
                field.value = field.value.replace(/[^0-9]/g, '');

                if (field.value.length === 1 && index < fields.length - 1) {
                    fields[index + 1].focus();
                }
                updateRealOtp();
            });

            // 2. Tự quay lại ô trước đó nếu nhấn nút Backspace (Xóa) khi ô đang trống
            field.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && field.value.length === 0 && index > 0) {
                    fields[index - 1].focus();
                }
            });
        });

        // Hàm gộp dữ liệu từ 6 ô vuông nhỏ vào ô input ẩn hidden thực tế để gửi đi
        function updateRealOtp() {
            let otpValue = "";
            fields.forEach(field => otpValue += field.value);
            realOtp.value = otpValue;
        }
    </script>

</body>

</html>