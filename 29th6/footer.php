<?php
// Footer fragment: page footer + scripts
?>
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
            <h4>Hệ thống cửa hàng (<?php echo htmlspecialchars($location); ?>)</h4>
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
            <h4>Phương thức thanh toán</h4>
            <p>Chúng tôi hỗ trợ nhiều hình thức thanh toán tiện lợi và an toàn nhất:</p>
            <div class="payment-methods">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/200px-Visa_Inc._logo.svg.png" alt="Visa">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/200px-Mastercard-logo.svg.png" alt="MasterCard">
                <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo">
                <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418186387.png" alt="VNPAY">
            </div>
            <p style="margin-top: 15px;">Thanh toán khi nhận hàng (COD), quẹt thẻ tận nhà.</p>
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

<script src="js/shop.js?v=<?= filemtime(__DIR__ . '/js/shop.js') ?>"></script>
<nav class="bottom-nav" aria-label="Menu chinh">
    <a href="index.php"><span>Trang chủ</span></a>
    <a href="index.php"><span>Sản phẩm</span></a>
    <a href="pages/cart.php"><span>Giỏ hàng</span></a>
    <a href="<?= isset($_SESSION['user_id']) ? 'pages/profile.php' : 'pages/login.php' ?>"><span><?= isset($_SESSION['user_id']) ? 'Tài khoản' : 'Đăng nhập' ?></span></a>
</nav>
</body>

</html>