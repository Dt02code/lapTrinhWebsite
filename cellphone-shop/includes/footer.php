<?php declare(strict_types=1); ?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4><?= SITE_NAME ?></h4>
            <p>Website bán điện thoại & laptop — dự án học tập.</p>
        </div>
        <div>
            <h4>Danh mục</h4>
            <a href="dien-thoai.php">Điện thoại</a>
            <a href="laptop.php">Laptop</a>
        </div>
        <div>
            <h4>Hỗ trợ</h4>
            <a href="thanh-toan.php">Phương thức thanh toán</a>
            <a href="gio-hang.php">Giỏ hàng</a>
        </div>
    </div>
    <p class="footer-copy">© <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
</footer>
<?php include __DIR__ . '/bottom-nav.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>
