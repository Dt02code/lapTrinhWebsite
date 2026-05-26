<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/functions.php';

$items = get_cart_items();
$total = cart_total();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $items !== []) {
    $hoTen = trim($_POST['ho_ten'] ?? '');
    $sdt = trim($_POST['so_dien_thoai'] ?? '');
    $diaChi = trim($_POST['dia_chi'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pttt = $_POST['phuong_thuc'] ?? 'cod';

    if ($hoTen === '' || $sdt === '' || $diaChi === '') {
        flash('error', 'Vui lòng điền đầy đủ họ tên, SĐT và địa chỉ.');
        redirect('thanh-toan.php');
    }

    $allowed = ['cod', 'chuyen_khoan', 'momo', 'vnpay'];
    if (!in_array($pttt, $allowed, true)) {
        $pttt = 'cod';
    }

    try {
        db()->beginTransaction();
        $stmt = db()->prepare(
            'INSERT INTO don_hang (nguoi_dung_id, ho_ten, email, so_dien_thoai, dia_chi, phuong_thuc_thanh_toan, tong_tien)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $user['id'] ?? null,
            $hoTen,
            $email ?: null,
            $sdt,
            $diaChi,
            $pttt,
            $total,
        ]);
        $orderId = (int) db()->lastInsertId();

        $detail = db()->prepare(
            'INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, so_luong, don_gia) VALUES (?, ?, ?, ?)'
        );
        foreach ($items as $item) {
            $detail->execute([$orderId, $item['id'], $item['so_luong'], $item['gia']]);
        }
        db()->commit();

        $_SESSION['cart'] = [];
        flash('success', 'Đặt hàng thành công! Mã đơn #' . $orderId);
        redirect('don-hang-thanh-cong.php?id=' . $orderId);
    } catch (PDOException $e) {
        db()->rollBack();
        flash('error', 'Không thể đặt hàng. Kiểm tra database đã import chưa.');
        redirect('thanh-toan.php');
    }
}

$pageTitle = 'Thanh toán';
$activeNav = 'cart';

require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <h1 class="page-title">💳 Thanh toán</h1>

    <?php if ($items === []): ?>
        <div class="card empty-state">
            <p>Giỏ hàng trống, chưa thể thanh toán.</p>
            <a href="index.php" class="btn btn-primary" style="margin-top:12px;">Mua sắm ngay</a>
        </div>
    <?php else: ?>
        <form method="post" class="card">
            <h2 style="margin-bottom:16px;font-size:1.1rem;">Thông tin giao hàng</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Họ và tên *</label>
                    <input type="text" name="ho_ten" required value="<?= htmlspecialchars($user['ho_ten'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Số điện thoại *</label>
                    <input type="tel" name="so_dien_thoai" required value="<?= htmlspecialchars($user['so_dien_thoai'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Email (tùy chọn)</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Địa chỉ nhận hàng *</label>
                <textarea name="dia_chi" rows="3" required placeholder="Số nhà, đường, phường, quận, tỉnh..."></textarea>
            </div>

            <h2 style="margin:20px 0 12px;font-size:1.1rem;">Phương thức thanh toán</h2>
            <div class="payment-options">
                <label class="payment-option">
                    <input type="radio" name="phuong_thuc" value="cod" checked>
                    <div>
                        <strong>💵 Thanh toán khi nhận hàng (COD)</strong>
                        <p style="font-size:13px;color:var(--muted);">Trả tiền mặt khi shipper giao hàng</p>
                    </div>
                </label>
                <label class="payment-option">
                    <input type="radio" name="phuong_thuc" value="chuyen_khoan">
                    <div>
                        <strong>🏦 Chuyển khoản ngân hàng</strong>
                        <p style="font-size:13px;color:var(--muted);">Vietcombank — 0123456789 — NGUYEN VAN A</p>
                    </div>
                </label>
                <label class="payment-option">
                    <input type="radio" name="phuong_thuc" value="momo">
                    <div>
                        <strong>📱 Ví MoMo</strong>
                        <p style="font-size:13px;color:var(--muted);">Quét QR hoặc chuyển qua SĐT đăng ký MoMo</p>
                    </div>
                </label>
                <label class="payment-option">
                    <input type="radio" name="phuong_thuc" value="vnpay">
                    <div>
                        <strong>💳 VNPay / Thẻ ngân hàng</strong>
                        <p style="font-size:13px;color:var(--muted);">Demo — thực tế cần tích hợp API VNPay</p>
                    </div>
                </label>
            </div>

            <div class="cart-summary" style="margin:20px 0;">
                Tổng thanh toán: <strong><?= format_price($total) ?></strong>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Xác nhận đặt hàng</button>
            <p style="text-align:center;margin-top:12px;font-size:13px;color:var(--muted);">
                Chưa có tài khoản? Không sao — vẫn đặt hàng được.
                <a href="dang-ky.php" style="color:var(--brand);">Đăng ký</a> nếu muốn lưu đơn hàng.
            </p>
        </form>

        <div class="card" style="margin-top:16px;">
            <h3 style="margin-bottom:10px;">Đơn hàng của bạn</h3>
            <ul style="list-style:none;font-size:14px;">
                <?php foreach ($items as $item): ?>
                    <li style="padding:6px 0;border-bottom:1px solid #eee;">
                        <?= htmlspecialchars($item['ten']) ?> × <?= (int) $item['so_luong'] ?>
                        — <?= format_price($item['thanh_tien']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
