<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/database.php';

const SITE_NAME = 'PhoneShop';
const BRAND_COLOR = '#d70018';
const BASE_URL = '';

function format_price(int|float $price): string
{
    return number_format((float) $price, 0, ',', '.') . '₫';
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, ho_ten, email, so_dien_thoai FROM nguoi_dung WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function cart_count(): int
{
    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    foreach ($cart as $qty) {
        $total += (int) $qty;
    }
    return $total;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}
