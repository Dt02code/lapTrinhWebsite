<?php
declare(strict_types=1);

function get_products(?string $categorySlug = null, bool $featuredOnly = false): array
{
    $sql = 'SELECT sp.*, dm.slug AS danh_muc_slug, dm.ten AS danh_muc_ten
            FROM san_pham sp
            JOIN danh_muc dm ON sp.danh_muc_id = dm.id
            WHERE 1=1';
    $params = [];

    if ($categorySlug) {
        $sql .= ' AND dm.slug = ?';
        $params[] = $categorySlug;
    }
    if ($featuredOnly) {
        $sql .= ' AND sp.noi_bat = 1';
    }
    $sql .= ' ORDER BY sp.noi_bat DESC, sp.id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_product_by_id(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT sp.*, dm.slug AS danh_muc_slug, dm.ten AS danh_muc_ten
         FROM san_pham sp
         JOIN danh_muc dm ON sp.danh_muc_id = dm.id
         WHERE sp.id = ?'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function add_to_cart(int $productId, int $qty = 1): void
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + $qty;
}

function update_cart_qty(int $productId, int $qty): void
{
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = $qty;
}

function get_cart_items(): array
{
    $cart = $_SESSION['cart'] ?? [];
    if ($cart === []) {
        return [];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM san_pham WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $items = [];
    foreach ($products as $p) {
        $qty = (int) ($cart[$p['id']] ?? 0);
        if ($qty > 0) {
            $p['so_luong'] = $qty;
            $p['thanh_tien'] = $qty * (float) $p['gia'];
            $items[] = $p;
        }
    }
    return $items;
}

function cart_total(): float
{
    $total = 0;
    foreach (get_cart_items() as $item) {
        $total += $item['thanh_tien'];
    }
    return $total;
}

function render_product_card(array $product): string
{
    $price = format_price($product['gia']);
    $old = $product['gia_cu'] ? '<span class="price-old">' . format_price($product['gia_cu']) . '</span>' : '';
    $img = htmlspecialchars($product['hinh_anh'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($product['ten'], ENT_QUOTES, 'UTF-8');
    $id = (int) $product['id'];

    return <<<HTML
    <article class="product-card">
        <a href="san-pham.php?id={$id}" class="product-card__image">
            <img src="{$img}" alt="{$name}" loading="lazy" onerror="this.src='assets/img/placeholder.svg'">
        </a>
        <div class="product-card__body">
            <h3><a href="san-pham.php?id={$id}">{$name}</a></h3>
            <div class="product-card__prices">
                <span class="price-current">{$price}</span>
                {$old}
            </div>
            <form method="post" action="gio-hang.php" class="add-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="{$id}">
                <button type="submit" class="btn btn-primary btn-sm">Thêm giỏ hàng</button>
            </form>
        </div>
    </article>
    HTML;
}
