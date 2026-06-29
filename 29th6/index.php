<?php
session_start();
require 'connect.php';

// =========================================
// XỬ LÝ PHÂN TRANG & TÌM KIẾM
// =========================================
$limit        = 12;
$page         = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset       = ($page - 1) * $limit;
$search       = isset($_GET['search']) ? trim($_GET['search']) : '';
$brand_filter = isset($_GET['hang'])   ? trim($_GET['hang'])   : '';
$sort         = isset($_GET['sort'])   ? trim($_GET['sort'])   : '';

// =========================================
// ĐẾM TỔNG SỐ SẢN PHẨM (để phân trang)
// =========================================
$where_clauses = ["1=1"];
$params        = [];
$types         = '';

if ($search !== '') {
    $where_clauses[] = "sp.TenSanPham LIKE ?";
    $params[]        = "%$search%";
    $types          .= 's';
}
if ($brand_filter !== '') {
    $where_clauses[] = "sp.Hang = ?";
    $params[]        = $brand_filter;
    $types          .= 's';
}
$where_sql = implode(' AND ', $where_clauses);

$count_sql  = "SELECT COUNT(DISTINCT sp.MaSanPham) as total
               FROM sanpham sp
               INNER JOIN giasanpham gsp ON sp.MaSanPham = gsp.MaSanPham
               INNER JOIN image img ON img.MaSanPham = sp.MaSanPham
               WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);
if ($types !== '') {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_rows  = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$count_stmt->close();

// =========================================
// TRUY VẤN DANH SÁCH SẢN PHẨM
// =========================================
$list_sql = "
    SELECT
        sp.MaSanPham,
        sp.TenSanPham,
        sp.Hang,
        MIN(gsp.GiaMoi)  AS GiaMoi,
        MIN(gsp.GiaCu)   AS GiaCu,
        SUM(gsp.SoLuong) AS TongTonKho,
        (
            SELECT img2.DiaChiAnh
            FROM image img2
            WHERE img2.MaSanPham = sp.MaSanPham
            ORDER BY img2.MaHinhAnh ASC
            LIMIT 1
        ) AS DiaChiAnh
    FROM sanpham sp
    INNER JOIN giasanpham gsp ON sp.MaSanPham = gsp.MaSanPham
    INNER JOIN image img     ON img.MaSanPham  = sp.MaSanPham
    WHERE $where_sql
    GROUP BY sp.MaSanPham, sp.TenSanPham, sp.Hang
    ORDER BY %s
    LIMIT ? OFFSET ?
";

$param_types = $types . 'ii';
$all_params  = array_merge($params, [$limit, $offset]);

$order_by = 'sp.MaSanPham ASC';
if ($sort === 'price_asc') {
    $order_by = 'MIN(gsp.GiaMoi) ASC';
} elseif ($sort === 'price_desc') {
    $order_by = 'MIN(gsp.GiaMoi) DESC';
}
$list_sql = sprintf($list_sql, $order_by);
$list_stmt = $conn->prepare($list_sql);
$list_stmt->bind_param($param_types, ...$all_params);
$list_stmt->execute();
$products = $list_stmt->get_result();
$list_stmt->close();

// =========================================
// LẤY DANH SÁCH HÃNG ĐỂ LỌC
// =========================================
$brands_result = $conn->query("SELECT DISTINCT Hang FROM sanpham ORDER BY Hang ASC");
$brands = [];
while ($b = $brands_result->fetch_assoc()) {
    $brands[] = $b['Hang'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CellPhone Store – Mua điện thoại chính hãng</title>
    <meta name="description" content="Cửa hàng điện thoại chính hãng – Samsung, iPhone, Xiaomi, Oppo, Realme với giá tốt nhất.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/shop.css">
</head>
<body>

<!-- ===== HEADER / NAVBAR ===== -->
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
            <a href="index.php" class="logo">CellPhone<span>K</span></a>
            <nav class="desktop-nav hide-mobile">
                <a href="index.php">Trang chủ</a>
                <a href="index.php">Sản phẩm</a>
                
            </nav>
            <form class="search-form" action="index.php" method="get">
                <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Tìm kiếm">
                <?php if ($brand_filter !== ''): ?>
                    <input type="hidden" name="hang" value="<?= htmlspecialchars($brand_filter) ?>">
                <?php endif; ?>
                <?php if ($sort !== ''): ?>
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <?php endif; ?>
                <button type="submit" aria-label="Tìm">&#128269;</button>
            </form>
            <div class="header-actions">
                <a href="pages/cart.php" class="header-cart">
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
                    <a href="pages/profile.php" class="btn btn-outline btn-sm hide-mobile">&#128100; <?= htmlspecialchars($_SESSION['username'] ?? 'Tài khoản') ?></a>
                    <a href="pages/logout.php" class="btn btn-outline btn-sm hide-mobile">Đăng xuất</a>
                <?php else: ?>
                    <a href="pages/login.php" class="btn btn-outline btn-sm hide-mobile">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- ===== HERO BANNER ===== -->
<section class="hero">
    <div class="container hero-content">
        <h1>Điện Thoại Chính Hãng</h1>
        <p>Khám phá hàng ngàn mẫu điện thoại với giá tốt nhất thị trường</p>
        <div class="hero-stats">
            <div class="stat"><strong><?= $total_rows ?>+</strong><span>Sản phẩm</span></div>
            <div class="stat"><strong>50+</strong><span>Thương hiệu</span></div>
            <div class="stat"><strong>100%</strong><span>Chính hãng</span></div>
        </div>
    </div>
</section>

<!-- ===== MAIN CONTENT ===== -->
<main class="container main-layout">

    <!-- Sidebar lọc -->
    <aside class="sidebar">
        <div class="filter-card">
            <h3 class="filter-title">🏷️ Lọc theo hãng</h3>
            <ul class="brand-list">
                <li>
                    <?php
                    $all_brands_href = 'index.php';
                    $query_parts = [];
                    if ($search)       $query_parts[] = 'search=' . urlencode($search);
                    if ($sort)         $query_parts[] = 'sort=' . urlencode($sort);
                    if (!empty($query_parts)) {
                        $all_brands_href .= '?' . implode('&', $query_parts);
                    }
                    ?>
                    <a href="<?= $all_brands_href ?>"
                       class="brand-item <?= $brand_filter === '' ? 'active' : '' ?>">
                        Tất cả hãng
                    </a>
                </li>
                <?php foreach ($brands as $brand): ?>
                    <li>
                        <?php
                        $href_parts = ['hang=' . urlencode($brand)];
                        if ($search) $href_parts[] = 'search=' . urlencode($search);
                        if ($sort)   $href_parts[] = 'sort=' . urlencode($sort);
                        $href = 'index.php?' . implode('&', $href_parts);
                        ?>
                        <a href="<?= $href ?>"
                           class="brand-item <?= $brand_filter === $brand ? 'active' : '' ?>">
                            <?= htmlspecialchars($brand) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <!-- Khu vực sản phẩm -->
    <section class="products-area">
        <!-- Thông báo -->
        <div id="toastMessage" class="toast hidden"></div>

        <!-- Tiêu đề + bộ lọc -->
        <div class="products-header">
            <div class="results-info">
                <?php if ($search || $brand_filter): ?>
                    <span>
                        Kết quả cho
                        <?php if ($search): ?> "<strong><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
                        <?php if ($brand_filter): ?> hãng <strong><?= htmlspecialchars($brand_filter) ?></strong><?php endif; ?>
                        <?php if ($sort === 'price_asc'): ?> theo giá <strong>thấp → cao</strong><?php endif; ?>
                        <?php if ($sort === 'price_desc'): ?> theo giá <strong>cao → thấp</strong><?php endif; ?>
                        — <strong><?= $total_rows ?></strong> sản phẩm
                    </span>
                <?php else: ?>
                    <span>Hiển thị <strong><?= min($offset + $limit, $total_rows) ?></strong> / <strong><?= $total_rows ?></strong> sản phẩm</span>
                <?php endif; ?>
            </div>
            <form class="sort-form" action="index.php" method="get">
                <?php if ($search !== ''): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php endif; ?>
                <?php if ($brand_filter !== ''): ?>
                    <input type="hidden" name="hang" value="<?= htmlspecialchars($brand_filter) ?>">
                <?php endif; ?>
                <label for="sort" class="sort-label">Sắp xếp giá:</label>
                <select id="sort" name="sort" onchange="this.form.submit()">
                    <option value="" <?= $sort === '' ? 'selected' : '' ?>>Mặc định</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Thấp đến cao</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Cao đến thấp</option>
                </select>
            </form>
        </div>

        <!-- Grid sản phẩm -->
        <?php if ($products->num_rows === 0): ?>
            <div class="no-products">
                <p>😕 Không tìm thấy sản phẩm nào phù hợp.</p>
                <a href="index.php" class="btn-primary">Xem tất cả sản phẩm</a>
            </div>
        <?php else: ?>
        <div class="product-grid" id="productGrid">
            <?php while ($p = $products->fetch_assoc()): ?>
                <?php
                $imgPath = !empty($p['DiaChiAnh'])
                    ? htmlspecialchars($p['DiaChiAnh'])
                    : 'img/no-image.svg';
                $discount = ($p['GiaCu'] > 0 && $p['GiaCu'] > $p['GiaMoi'])
                    ? round((1 - $p['GiaMoi'] / $p['GiaCu']) * 100)
                    : 0;
                ?>
                <div class="product-card" data-id="<?= $p['MaSanPham'] ?>">
                    <?php if ($discount > 0): ?>
                        <span class="badge-discount">-<?= $discount ?>%</span>
                    <?php endif; ?>

                    <a href="pages/detail.php?id=<?= $p['MaSanPham'] ?>" class="product-img-wrap">
                        <img src="<?= $imgPath ?>"
                             alt="<?= htmlspecialchars($p['TenSanPham']) ?>"
                             loading="lazy"
                             onerror="this.src='img/no-image.svg'">
                    </a>

                    <div class="product-info">
                        <span class="product-brand"><?= htmlspecialchars($p['Hang']) ?></span>
                        <h2 class="product-name">
                            <a href="pages/detail.php?id=<?= $p['MaSanPham'] ?>">
                                <?= htmlspecialchars($p['TenSanPham']) ?>
                            </a>
                        </h2>
                        <div class="product-price">
                            <span class="price-new"><?= number_format($p['GiaMoi'], 0, ',', '.') ?>đ</span>
                            <?php if ($p['GiaCu'] > $p['GiaMoi']): ?>
                                <span class="price-old"><?= number_format($p['GiaCu'], 0, ',', '.') ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:0.8rem; color:var(--text-muted); margin-top:5px;">
                            <i class="fa-solid fa-boxes-stacked"></i> Còn lại: <?= $p['TongTonKho'] ?: 0 ?> sản phẩm
                        </div>
                    </div>

                    <div class="product-actions">
                        <a href="pages/detail.php?id=<?= $p['MaSanPham'] ?>" class="btn-detail">
                            Xem chi tiết
                        </a>
                        <button class="btn-add-cart"
                                data-id="<?= $p['MaSanPham'] ?>"
                                data-name="<?= htmlspecialchars($p['TenSanPham'], ENT_QUOTES) ?>"
                                onclick="quickAddToCart(this)">
                            🛒 Thêm vào giỏ
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- PHÂN TRANG -->
        <?php if ($total_pages > 1): ?>
            <nav class="pagination" aria-label="Phân trang">
                <?php
                $query_base = '';
                if ($search)       $query_base .= '&search=' . urlencode($search);
                if ($brand_filter) $query_base .= '&hang='   . urlencode($brand_filter);
                if ($sort)         $query_base .= '&sort='   . urlencode($sort);
                ?>

                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $query_base ?>" class="page-btn">‹ Trước</a>
                <?php endif; ?>

                <?php
                $start = max(1, $page - 2);
                $end   = min($total_pages, $page + 2);
                if ($start > 1): ?>
                    <a href="?page=1<?= $query_base ?>" class="page-btn">1</a>
                    <?php if ($start > 2): ?><span class="page-ellipsis">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?= $i ?><?= $query_base ?>"
                       class="page-btn <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?><span class="page-ellipsis">…</span><?php endif; ?>
                    <a href="?page=<?= $total_pages ?><?= $query_base ?>" class="page-btn"><?= $total_pages ?></a>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $query_base ?>" class="page-btn">Sau ›</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4>Về CellPhoneK</h4>
            <p>Thành lập từ năm 2026, CellPhoneK là hệ thống bán lẻ điện thoại di động, laptop, phụ kiện công nghệ uy tín hàng đầu. Cam kết 100% hàng chính hãng, bảo hành trọn đời.</p>
            <p><strong>Hotline:</strong> 1800.2097 (Miễn phí)</p>
            <p><strong>Email:</strong> cskh@cellphonek.com</p>
        </div>

        <div class="footer-addresses">
            
            <div class="address-list">
                <p>📍 123 Lê Lợi, Phường Bến Nghé, Quận 1, TP. HCM</p>
                <p>📍 456 Trần Hưng Đạo, Hoàn Kiếm, Hà Nội</p>
                <p>📍 789 Nguyễn Văn Linh, Hải Châu, Đà Nẵng</p>
                <p>📍 101 Hùng Vương, Pleiku, Gia Lai</p>
            </div>
            <p class="address-note"><em>(Xem thêm 50 siêu thị khác trên toàn quốc)</em></p>
        </div>

        <div>
            <h4>Chính sách & Hỗ trợ</h4>
            <a href="#">Chính sách bảo hành</a>
            <a href="#">Chính sách đổi trả 30 ngày</a>
            <a href="#">Chính sách giao hàng tận nơi</a>
            <a href="#">Hướng dẫn mua hàng trả góp</a>
            <a href="#">Điều khoản bảo mật thông tin</a>
        </div>



        <div>
            <h4>CellPhoneK</h4>
            <p>Website bán điện thoại &amp; laptop chính hãng.</p>
        </div>
        <div>
            <h4>Danh mục</h4>
            <a href="index.php">Điện thoại</a>
            <a href="index.php">Laptop</a>
        </div>


        <div>
            <h4>Hỗ trợ</h4>
            <a href="pages/cart.php">Giỏ hàng</a>
            <a href="pages/checkout.php">Thanh toán</a>
        </div>

        <div style="border-top: 1px solid #334155; padding-top: 20px; text-align: center;">
            <p>© 2026 Bản quyền thuộc về Công ty TNHH CellPhoneK. Thiết kế bởi Sinh Viên.</p>
        </div>
    </div>
    <p class="footer-copy">© 2026 CellPhoneK. All rights reserved.</p>
</footer>




<script src="js/shop.js"></script>
<nav class="bottom-nav" aria-label="Menu chinh">
    <a href="index.php"><span class="icon">&#127968;</span><span>Trang chu</span></a>
    <a href="index.php"><span class="icon">&#128241;</span><span>San pham</span></a>
    <a href="pages/cart.php"><span class="icon">&#128722;</span><span>Gio hang</span></a>
    <a href="<?= isset($_SESSION['user_id']) ? 'pages/profile.php' : 'pages/login.php' ?>"><span class="icon">&#128100;</span><span><?= isset($_SESSION['user_id']) ? 'Tai khoan' : 'Dang nhap' ?></span></a>
</nav>
</body>
</html>
