<?php
session_start();
require '../connect.php';

// =========================================
// VALIDATE & LẤY ID SẢN PHẨM
// =========================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}
$product_id = (int)$_GET['id'];

// =========================================
// TRUY VẤN THÔNG TIN SẢN PHẨM (Prepared Statement)
// =========================================
$sql = "
    SELECT
        sp.MaSanPham,
        sp.TenSanPham,
        sp.Hang,
        sp.NgayNhap,
        ct.KichThuocManHinh,
        ct.CongNgheManHinh,
        ct.DoPhanGiaiManHinh,
        ct.TinhNangManHinh,
        ct.CameraSau,
        ct.QuayVideoSau,
        ct.CameraTruoc,
        ct.QuayVideoTruoc,
        ct.ChipSet,
        ct.Pin,
        ct.CongNgheSac,
        ct.HeDieuHanh,
        ct.KhangNuocBui,
        ct.Bluetooth,
        ct.Wifi
    FROM sanpham sp
    LEFT JOIN chitietsanpham ct ON sp.MaSanPham = ct.MaSanPham
    WHERE sp.MaSanPham = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: ../index.php');
    exit;
}

// =========================================
// TRUY VẤN TẤT CẢ BIẾN THỂ GIÁ + TÊN RAM
// =========================================
$sql_variants = "
    SELECT
        gsp.MaGia,
        gsp.MaRam,
        gsp.MaMau,
        gsp.GiaCu,
        gsp.GiaMoi,
        gsp.SoLuong,
        rr.KichThuoc  AS TenRam,
        c.TenMau
    FROM giasanpham gsp
    INNER JOIN ram_rom_option rr ON gsp.MaRam = rr.MaRam
    INNER JOIN colors c          ON gsp.MaMau = c.MaMau
    WHERE gsp.MaSanPham = ?
    ORDER BY gsp.GiaMoi ASC
";
$stmt2 = $conn->prepare($sql_variants);
$stmt2->bind_param('i', $product_id);
$stmt2->execute();
$variants_result = $stmt2->get_result();
$variants = [];
$ram_options  = [];
$color_options = [];
while ($v = $variants_result->fetch_assoc()) {
    $variants[] = $v;
    $ram_options[$v['MaRam']]    = $v['TenRam'];
    $color_options[$v['MaMau']] = $v['TenMau'];
}
$stmt2->close();

// =========================================
// TRUY VẤN TẤT CẢ ẢNH THEO MÀU
// =========================================
$sql_images = "
    SELECT img.MaMau, img.DiaChiAnh, c.TenMau
    FROM image img
    INNER JOIN colors c ON img.MaMau = c.MaMau
    WHERE img.MaSanPham = ?
    ORDER BY img.MaMau ASC
";
$stmt3 = $conn->prepare($sql_images);
$stmt3->bind_param('i', $product_id);
$stmt3->execute();
$images_result = $stmt3->get_result();
$images_by_color = [];
$first_image = '';
while ($img = $images_result->fetch_assoc()) {
    if ($first_image === '') $first_image = $img['DiaChiAnh'];
    $images_by_color[$img['MaMau']] = $img['DiaChiAnh'];
}
$stmt3->close();

// Encode variants thành JSON để dùng ở JS
$variants_json = json_encode($variants, JSON_UNESCAPED_UNICODE);
$images_json   = json_encode($images_by_color, JSON_UNESCAPED_UNICODE);

// Mặc định hiển thị variant đầu tiên
$default = !empty($variants) ? $variants[0] : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['TenSanPham']) ?> – CellPhoneK</title>
    <meta name="description" content="Xem chi tiết <?= htmlspecialchars($product['TenSanPham']) ?> – thông số, giá cả, cấu hình đầy đủ.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
                <a href="../pages/cart.php">Giỏ hàng</a>
            </nav>
            <form class="search-form" action="../index.php" method="get">
                <input type="search" name="search" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Tìm kiếm">
                <button type="submit" aria-label="Tìm">&#128269;</button>
            </form>
            <div class="header-actions">
                <a href="../pages/cart.php" class="header-cart">
                    &#128722; Giỏ hàng
                    <?php
                    $__cartCount = 0;
                    if (!empty($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $__item) { $__cartCount += $__item['quantity']; }
                    }
                    if ($__cartCount > 0): ?>
                        <span class="badge"><?= $__cartCount ?></span>
                    <?php endif; ?>
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="../pages/profile.php" class="btn btn-outline btn-sm hide-mobile">&#128100; <?= htmlspecialchars($_SESSION['username'] ?? 'Tài khoản') ?></a>
                    <a href="../pages/logout.php" class="btn btn-outline btn-sm hide-mobile">Đăng xuất</a>
                <?php else: ?>
                    <a href="../pages/login.php" class="btn btn-outline btn-sm hide-mobile">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- ===== BREADCRUMB ===== -->
<div class="breadcrumb-bar">
    <div class="container">
        <a href="../index.php">Trang chủ</a>
        <span>›</span>
        <a href="../index.php?hang=<?= urlencode($product['Hang']) ?>"><?= htmlspecialchars($product['Hang']) ?></a>
        <span>›</span>
        <span><?= htmlspecialchars($product['TenSanPham']) ?></span>
    </div>
</div>

<!-- ===== TOAST NOTIFICATION ===== -->
<div id="toastMessage" class="toast hidden"></div>

<!-- ===== DETAIL MAIN ===== -->
<main class="container detail-main">

    <!-- === LEFT: ẢNH === -->
    <div class="detail-gallery">
        <div class="main-image-wrap">
            <img id="mainProductImg"
                 src="<?= !empty($first_image) ? '../' . htmlspecialchars($first_image) : '../img/no-image.svg' ?>"
                 alt="<?= htmlspecialchars($product['TenSanPham']) ?>"
                 onerror="this.src='../img/no-image.svg'">
        </div>
        <!-- Thumbnail màu -->
        <div class="thumbnail-list" id="thumbnailList">
            <?php foreach ($images_by_color as $maMau => $diaChiAnh): ?>
                <img src="../<?= htmlspecialchars($diaChiAnh) ?>"
                     class="thumb-img <?= $maMau === array_key_first($images_by_color) ? 'active' : '' ?>"
                     data-color="<?= $maMau ?>"
                     alt="Màu <?= $maMau ?>"
                     onclick="selectThumbnail(this)"
                     onerror="this.src='../img/no-image.svg'">
            <?php endforeach; ?>
        </div>
    </div>

    <!-- === RIGHT: THÔNG TIN === -->
    <div class="detail-info">
        <span class="detail-brand-tag"><?= htmlspecialchars($product['Hang']) ?></span>
        <h1 class="detail-title"><?= htmlspecialchars($product['TenSanPham']) ?></h1>

        <!-- GIÁ HIỂN THỊ -->
        <div class="detail-price-block" id="priceBlock">
            <?php if ($default): ?>
                <span class="price-new-lg" id="displayPriceNew">
                    <?= number_format($default['GiaMoi'], 0, ',', '.') ?>đ
                </span>
                <?php if ($default['GiaCu'] > $default['GiaMoi']): ?>
                    <span class="price-old-lg" id="displayPriceOld">
                        <?= number_format($default['GiaCu'], 0, ',', '.') ?>đ
                    </span>
                    <span class="discount-badge" id="displayDiscount">
                        -<?= round((1 - $default['GiaMoi'] / $default['GiaCu']) * 100) ?>%
                    </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- TỒN KHO -->
        <div class="stock-info" id="stockInfo">
            <?php if ($default): ?>
                <?php if ($default['SoLuong'] > 0): ?>
                    <span class="in-stock">✅ Còn hàng (<span id="stockQty"><?= $default['SoLuong'] ?></span> sản phẩm)</span>
                <?php else: ?>
                    <span class="out-stock">❌ Hết hàng</span>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- CHỌN DUNG LƯỢNG RAM/ROM -->
        <?php if (!empty($ram_options)): ?>
        <div class="option-group">
            <label class="option-label">💾 Dung lượng:</label>
            <div class="option-btns" id="ramOptions">
                <?php
                $rendered_rams = [];
                foreach ($variants as $v):
                    if (in_array($v['MaRam'], $rendered_rams)) continue;
                    $rendered_rams[] = $v['MaRam'];
                ?>
                    <button class="opt-btn <?= $v === reset($variants) ? 'selected' : '' ?>"
                            data-ram="<?= $v['MaRam'] ?>"
                            onclick="selectOption('ram', this)">
                        <?= htmlspecialchars($v['TenRam']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CHỌN MÀU -->
        <?php if (!empty($color_options)): ?>
        <div class="option-group">
            <label class="option-label">🎨 Màu sắc:</label>
            <div class="option-btns" id="colorOptions">
                <?php
                $rendered_colors = [];
                foreach ($variants as $v):
                    if (in_array($v['MaMau'], $rendered_colors)) continue;
                    $rendered_colors[] = $v['MaMau'];
                ?>
                    <button class="opt-btn color-btn <?= $v === reset($variants) ? 'selected' : '' ?>"
                            data-color="<?= $v['MaMau'] ?>"
                            onclick="selectOption('color', this)">
                        <?= htmlspecialchars($v['TenMau']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- SỐ LƯỢNG MUA -->
        <div class="qty-group">
            <label class="option-label">📦 Số lượng:</label>
            <div class="qty-control">
                <button class="qty-btn" id="btnMinus" onclick="changeQty(-1)">−</button>
                <input type="number" id="qtyInput" value="1" min="1" max="<?= $default['SoLuong'] ?? 1 ?>"
                       oninput="validateQtyInput(this)">
                <button class="qty-btn" id="btnPlus" onclick="changeQty(1)">+</button>
            </div>
        </div>

        <!-- NÚT THÊM VÀO GIỎ -->
        <div class="detail-actions">
            <button class="btn-add-cart-lg" id="btnAddCart" onclick="addToCartDetail()">
                🛒 Thêm vào giỏ hàng
            </button>
            <button class="btn-buy-now" id="btnBuyNow" onclick="buyNowDetail()">⚡ Mua ngay</button>
        </div>

        <!-- ID variant đang chọn (ẩn) -->
        <input type="hidden" id="selectedMaGia"  value="<?= $default['MaGia']  ?? 0 ?>">
        <input type="hidden" id="selectedMaSanPham" value="<?= $product['MaSanPham'] ?>">
        <input type="hidden" id="selectedMaRam"  value="<?= $default['MaRam']  ?? 0 ?>">
        <input type="hidden" id="selectedMaMau"  value="<?= $default['MaMau']  ?? 0 ?>">
        <input type="hidden" id="selectedStock"  value="<?= $default['SoLuong'] ?? 0 ?>">
    </div>
</main>

<!-- ===== THÔNG SỐ KỸ THUẬT ===== -->
<section class="container specs-section">
    <h2 class="section-title">📋 Thông số kỹ thuật</h2>
    <div class="specs-table-wrap">
        <table class="specs-table">
            <tbody>
                <?php
                $specs = [
                    ['🖥️ Kích thước màn hình', $product['KichThuocManHinh']],
                    ['💡 Công nghệ màn hình',  $product['CongNgheManHinh']],
                    ['🔍 Độ phân giải',        $product['DoPhanGiaiManHinh']],
                    ['✨ Tính năng màn hình',  $product['TinhNangManHinh']],
                    ['📷 Camera sau',          $product['CameraSau']],
                    ['🎬 Quay video sau',      $product['QuayVideoSau']],
                    ['🤳 Camera trước',        $product['CameraTruoc']],
                    ['🎥 Quay video trước',    $product['QuayVideoTruoc']],
                    ['⚙️ Chip xử lý',          $product['ChipSet']],
                    ['🔋 Dung lượng pin',      $product['Pin']],
                    ['⚡ Công nghệ sạc',       $product['CongNgheSac']],
                    ['📱 Hệ điều hành',        $product['HeDieuHanh']],
                    ['💧 Kháng nước/bụi',     $product['KhangNuocBui']],
                    ['📶 Bluetooth',            $product['Bluetooth']],
                    ['📡 Wi-Fi',               $product['Wifi']],
                ];
                foreach ($specs as $spec):
                    if (empty($spec[1])) continue;
                ?>
                <tr>
                    <th><?= $spec[0] ?></th>
                    <td><?= htmlspecialchars($spec[1]) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

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
            <a href="../pages/cart.php">Giỏ hàng</a>
            <a href="../pages/checkout.php">Thanh toán</a>
        </div>
    </div>
    <p class="footer-copy">© 2026 CellPhoneK. All rights reserved.</p>
</footer>




<!-- Data cho JS -->
<script>
    const VARIANTS     = <?= $variants_json ?>;
    const IMAGES       = <?= $images_json ?>;
    const BASE_URL     = '../';
    const PRODUCT_NAME = <?= json_encode($product['TenSanPham'], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../js/shop.js"></script>
<nav class="bottom-nav" aria-label="Menu chinh">
    <a href="../index.php"><span class="icon">&#127968;</span><span>Trang chu</span></a>
    <a href="../index.php"><span class="icon">&#128241;</span><span>San pham</span></a>
    <a href="../pages/cart.php"><span class="icon">&#128722;</span><span>Gio hang</span></a>
    <a href="<?= isset($_SESSION['user_id']) ? '../pages/profile.php' : '../pages/login.php' ?>"><span class="icon">&#128100;</span><span><?= isset($_SESSION['user_id']) ? 'Tai khoan' : 'Dang nhap' ?></span></a>
</nav>
</body>
</html>
