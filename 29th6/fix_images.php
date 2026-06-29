<?php
/**
 * fix_images.php – Chạy 1 lần để cập nhật đường dẫn ảnh trong DB
 * URL: http://localhost/FullCode/output/fix_images.php
 * 
 * Ảnh có sẵn trong output/img/:
 *   samsung/ (Dong_A, Dong_S, Dong_M, Dong_Z)
 *   iphone/  (13_SERIES, 14_SERIES, 15_SERIES, 16_SERIES, 17_SERIES)
 *   placeholder/ (tự tạo SVG cho Xiaomi, POCO, OPPO, realme)
 */
require 'connect.php';

// =====================================================================
// BƯỚC 1: TẠO ẢNH PLACEHOLDER SVG CHO CÁC HÃNG CHƯA CÓ ẢNH THỰC
// =====================================================================
$placeholder_dir = __DIR__ . '/img/PLACEHOLDER';
if (!is_dir($placeholder_dir)) mkdir($placeholder_dir, 0777, true);

$brands_svg = [
    'xiaomi'  => ['#FF6900', '#FFFFFF', 'Xiaomi'],
    'poco'    => ['#191919', '#F6DC3B', 'POCO'],
    'oppo'    => ['#1B5E20', '#FFFFFF', 'OPPO'],
    'realme'  => ['#FEE500', '#1B1B1B', 'realme'],
    'redmi'   => ['#FF6900', '#FFFFFF', 'Redmi'],
    'default' => ['#2D3748', '#FFFFFF', 'Phone'],
];

foreach ($brands_svg as $name => [$bg, $color, $label]) {
    $svg_file = $placeholder_dir . '/' . $name . '.svg';
    if (!file_exists($svg_file)) {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="400" viewBox="0 0 400 400">
  <rect width="400" height="400" fill="$bg" rx="20"/>
  <rect x="140" y="80" width="120" height="200" rx="15" ry="15" fill="none" stroke="$color" stroke-width="6"/>
  <rect x="156" y="100" width="88" height="140" fill="$color" opacity="0.15" rx="4"/>
  <circle cx="200" cy="258" r="10" fill="$color" opacity="0.6"/>
  <line x1="175" y1="92" x2="225" y2="92" stroke="$color" stroke-width="5" stroke-linecap="round"/>
  <text x="200" y="330" font-family="Arial,sans-serif" font-size="38" font-weight="bold"
        text-anchor="middle" fill="$color">$label</text>
  <text x="200" y="365" font-family="Arial,sans-serif" font-size="18"
        text-anchor="middle" fill="$color" opacity="0.7">Smartphone</text>
</svg>
SVG;
        file_put_contents($svg_file, $svg);
    }
}

// =====================================================================
// BƯỚC 2: MAP MaSanPham => đường dẫn ảnh thực tế
// Dựa theo cellphone_k.sql: Samsung 1-10, Xiaomi/Redmi 11-20,
// OPPO 21-30, realme 31-40, iPhone 41-50
// =====================================================================
$product_image_map = [
    // ===== SAMSUNG S SERIES =====
    1  => 'img/SAMSUNG/Dong_S/dien_thoai_S24_ULTRA.jpg',
    2  => 'img/SAMSUNG/Dong_S/samsung-galaxy-s22-ultra.jpg',
    3  => 'img/SAMSUNG/Dong_S/samsung-s23.jpg',
    // ===== SAMSUNG Z SERIES =====
    4  => 'img/SAMSUNG/Dong_Z/samsung-galaxy-z-fold-4.jpg',
    // ===== SAMSUNG A SERIES =====
    5  => 'img/SAMSUNG/Dong_A/samsung-galaxy-a55.jpg',
    6  => 'img/SAMSUNG/Dong_A/dien-thoai-samsung-galaxy-a36.jpg',
    7  => 'img/SAMSUNG/Dong_A/samsung-galaxy-a55.jpg',
    8  => 'img/SAMSUNG/Dong_A/dien-thoai-samsung-galaxy-a06.jpg',
    9  => 'img/SAMSUNG/Dong_A/samsung-galaxy-a07-5g-2_3.jpg',
    10 => 'img/SAMSUNG/Dong_A/dien-thoai-samsung-galaxy-a26.jpg',
    // ===== XIAOMI / REDMI (placeholder) =====
    11 => 'img/PLACEHOLDER/xiaomi.svg',
    12 => 'img/PLACEHOLDER/xiaomi.svg',
    13 => 'img/PLACEHOLDER/xiaomi.svg',
    14 => 'img/PLACEHOLDER/xiaomi.svg',
    15 => 'img/PLACEHOLDER/xiaomi.svg',
    16 => 'img/PLACEHOLDER/redmi.svg',
    17 => 'img/PLACEHOLDER/redmi.svg',
    18 => 'img/PLACEHOLDER/redmi.svg',
    19 => 'img/PLACEHOLDER/redmi.svg',
    20 => 'img/PLACEHOLDER/poco.svg',
    // ===== OPPO (placeholder) =====
    21 => 'img/PLACEHOLDER/oppo.svg',
    22 => 'img/PLACEHOLDER/oppo.svg',
    23 => 'img/PLACEHOLDER/oppo.svg',
    24 => 'img/PLACEHOLDER/oppo.svg',
    25 => 'img/PLACEHOLDER/oppo.svg',
    26 => 'img/PLACEHOLDER/oppo.svg',
    27 => 'img/PLACEHOLDER/oppo.svg',
    28 => 'img/PLACEHOLDER/oppo.svg',
    29 => 'img/PLACEHOLDER/oppo.svg',
    30 => 'img/PLACEHOLDER/oppo.svg',
    // ===== REALME (placeholder) =====
    31 => 'img/PLACEHOLDER/realme.svg',
    32 => 'img/PLACEHOLDER/realme.svg',
    33 => 'img/PLACEHOLDER/realme.svg',
    34 => 'img/PLACEHOLDER/realme.svg',
    35 => 'img/PLACEHOLDER/realme.svg',
    36 => 'img/PLACEHOLDER/realme.svg',
    37 => 'img/PLACEHOLDER/realme.svg',
    38 => 'img/PLACEHOLDER/realme.svg',
    39 => 'img/PLACEHOLDER/realme.svg',
    40 => 'img/PLACEHOLDER/realme.svg',
    // ===== IPHONE =====
    41 => 'img/IPHONE/13_SERIES/iphone-13.jpg',
    42 => 'img/IPHONE/13_SERIES/iphone-13-pro.jpg',
    43 => 'img/IPHONE/13_SERIES/iphone-13-pro-max.jpg',
    44 => 'img/IPHONE/14_SERIES/iphone-14.jpg',
    45 => 'img/IPHONE/14_SERIES/iphone-14-pro.jpg',
    46 => 'img/IPHONE/14_SERIES/iphone-14-plus.jpg',
    47 => 'img/IPHONE/14_SERIES/iphone-14-pro_max.jpg',
    48 => 'img/IPHONE/15_SERIES/iphone-15.jpg',
    49 => 'img/IPHONE/15_SERIES/iphone-15-pro.jpg',
    50 => 'img/IPHONE/15_SERIES/iphone-15-pro-max.jpg',
];

// =====================================================================
// BƯỚC 3: CẬP NHẬT BẢNG image (tất cả hàng có MaSanPham đó)
// =====================================================================
$stmt    = $conn->prepare("UPDATE image SET DiaChiAnh = ? WHERE MaSanPham = ?");
$updated = 0;
$errors  = 0;
$log     = [];

foreach ($product_image_map as $ma_san_pham => $path) {
    $stmt->bind_param('si', $path, $ma_san_pham);
    $stmt->execute();
    if ($stmt->errno) {
        $errors++;
        $log[] = "❌ SP #$ma_san_pham: " . $stmt->error;
    } else {
        $rows    = $stmt->affected_rows;
        $updated += max(0, $rows);
        $status  = $rows > 0 ? "✅" : "⚠️ (0 hàng)";
        $log[]   = "$status SP #$ma_san_pham → <code>$path</code>";
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Fix Image Paths – CellPhoneK</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; max-width: 960px; margin: 40px auto; padding: 20px; background: #f8fafc; color: #1e293b; }
  h1 { color: #2563eb; }
  .summary { background: #fff; padding: 16px 24px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #10b981; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
  .log { background: #fff; padding: 16px 24px; border-radius: 10px; max-height: 400px; overflow-y: auto; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
  .log p { margin: 4px 0; font-size: 13px; }
  code { background: #f1f5f9; padding: 1px 5px; border-radius: 4px; font-size: .85em; }
  .check-section { margin-top: 24px; background: #fff; padding: 16px 24px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
  .file-ok { color: #059669; } .file-miss { color: #dc2626; }
  .btns { margin-top: 20px; display: flex; gap: 10px; }
  a.btn { display: inline-block; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; }
  .btn-blue { background: #2563eb; color: #fff; }
  .btn-orange { background: #f97316; color: #fff; }
</style>
</head>
<body>
<h1>🔧 Fix Image Paths – CellPhoneK</h1>

<div class="summary">
    <h2>📊 Kết quả</h2>
    <p>✅ Đã cập nhật: <strong><?= $updated ?></strong> bản ghi</p>
    <p>🖼️ Ảnh placeholder SVG đã tạo trong <code>img/PLACEHOLDER/</code></p>
    <?php if ($errors): ?>
        <p style="color:#dc2626">❌ Lỗi: <strong><?= $errors ?></strong></p>
    <?php endif; ?>
</div>

<div class="log">
    <h3>Chi tiết cập nhật:</h3>
    <?php foreach ($log as $line): ?>
        <p><?= $line ?></p>
    <?php endforeach; ?>
</div>

<div class="check-section">
    <h3>🔍 Kiểm tra file ảnh tồn tại:</h3>
    <?php
    $checked = [];
    foreach ($product_image_map as $path) {
        if (in_array($path, $checked)) continue;
        $checked[] = $path;
        $full   = __DIR__ . '/' . $path;
        $exists = file_exists($full);
        $cls    = $exists ? 'file-ok' : 'file-miss';
        $icon   = $exists ? '✅' : '❌';
        echo "<p class='$cls'>$icon <code>$path</code></p>";
    }
    ?>
</div>

<div class="btns">
    <a href="index.php" class="btn btn-blue">← Về trang chủ</a>
    <a href="fix_images.php" class="btn btn-orange">🔄 Chạy lại</a>
</div>
</body>
</html>
