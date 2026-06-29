<?php
/**
 * AJAX endpoint – Kiểm tra và áp mã giảm giá
 * POST: code, order_total
 * Response JSON: { success, discount_amount, final_total, message, coupon_id, discount_type, discount_value }
 */
session_start();
require '../connect.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
    exit;
}

$code        = strtoupper(trim($_POST['code'] ?? ''));
$orderTotal  = (float)($_POST['order_total'] ?? 0);

if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá.']);
    exit;
}

$today = date('Y-m-d');

$stmt = $conn->prepare(
    "SELECT * FROM coupons
     WHERE Code = ?
       AND IsActive = 1
       AND StartDate <= ?
       AND EndDate >= ?"
);
$stmt->bind_param('sss', $code, $today, $today);
$stmt->execute();
$res = $stmt->get_result();
$coupon = $res->fetch_assoc();
$stmt->close();

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.']);
    exit;
}

if ($orderTotal < (float)$coupon['MinOrderValue']) {
    $min = number_format((float)$coupon['MinOrderValue'], 0, ',', '.');
    echo json_encode(['success' => false, 'message' => "Đơn hàng phải đạt tối thiểu {$min}đ để dùng mã này."]);
    exit;
}

// Tính số tiền giảm
$discountAmount = 0;
if ($coupon['DiscountType'] === 'percent') {
    $discountAmount = $orderTotal * ((float)$coupon['DiscountValue'] / 100);
} else {
    $discountAmount = (float)$coupon['DiscountValue'];
}
$discountAmount = min($discountAmount, $orderTotal); // không giảm âm
$finalTotal = $orderTotal - $discountAmount;

echo json_encode([
    'success'        => true,
    'message'        => 'Áp mã thành công! Bạn được giảm ' .
                        ($coupon['DiscountType'] === 'percent'
                            ? $coupon['DiscountValue'] . '%'
                            : number_format($coupon['DiscountValue'], 0, ',', '.') . 'đ'),
    'coupon_id'      => (int)$coupon['CouponID'],
    'discount_type'  => $coupon['DiscountType'],
    'discount_value' => (float)$coupon['DiscountValue'],
    'discount_amount'=> $discountAmount,
    'final_total'    => $finalTotal,
]);
