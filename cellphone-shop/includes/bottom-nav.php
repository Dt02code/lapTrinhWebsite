<?php declare(strict_types=1); ?>
<nav class="bottom-nav" aria-label="Menu chính">
    <a href="index.php" class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">
        <span class="icon">🏠</span>
        <span>Trang chủ</span>
    </a>
    <a href="dien-thoai.php" class="<?= ($activeNav ?? '') === 'phone' ? 'active' : '' ?>">
        <span class="icon">📱</span>
        <span>Điện thoại</span>
    </a>
    <a href="laptop.php" class="<?= ($activeNav ?? '') === 'laptop' ? 'active' : '' ?>">
        <span class="icon">💻</span>
        <span>Laptop</span>
    </a>
    <a href="gio-hang.php" class="<?= ($activeNav ?? '') === 'cart' ? 'active' : '' ?>">
        <span class="icon">🛒</span>
        <span>Giỏ hàng</span>
        <?php if (cart_count() > 0): ?><em class="nav-badge"><?= cart_count() ?></em><?php endif; ?>
    </a>
    <a href="<?= is_logged_in() ? 'tai-khoan.php' : 'dang-nhap.php' ?>" class="<?= ($activeNav ?? '') === 'account' ? 'active' : '' ?>">
        <span class="icon">👤</span>
        <span><?= is_logged_in() ? 'Tài khoản' : 'Đăng nhập' ?></span>
    </a>
</nav>
