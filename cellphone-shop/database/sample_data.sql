USE cellphone_shop;

INSERT INTO danh_muc (ten, slug) VALUES
('Điện thoại', 'dien-thoai'),
('Laptop', 'laptop')
ON DUPLICATE KEY UPDATE ten = VALUES(ten);

INSERT INTO san_pham (danh_muc_id, ten, slug, gia, gia_cu, hinh_anh, mo_ta, thong_so, ton_kho, noi_bat) VALUES
(1, 'iPhone 17 Pro Max 256GB', 'iphone-17-pro-max', 34990000, 36990000,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/i/p/iphone-17-pro-max.png',
 'iPhone 17 Pro Max với chip A19 Pro, camera 48MP, pin trâu cả ngày.',
 'Màn hình 6.9" | RAM 8GB | Bộ nhớ 256GB | 5G', 15, 1),
(1, 'Samsung Galaxy S26 Ultra', 'galaxy-s26-ultra', 30490000, 32990000,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/s/a/samsung-galaxy-s26-ultra.png',
 'Galaxy S26 Ultra flagship với S Pen, zoom 200x.',
 'Màn hình 6.8" Dynamic AMOLED | Snapdragon | 256GB', 12, 1),
(1, 'Xiaomi 15 Ultra', 'xiaomi-15-ultra', 22990000, 24990000,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/x/i/xiaomi-15-ultra.png',
 'Camera Leica, sạc nhanh 90W.',
 '6.73" AMOLED | 16GB RAM | 512GB', 20, 1),
(1, 'OPPO Find X9 Pro', 'oppo-find-x9-pro', 19990000, NULL,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/o/p/oppo-find-x9.png',
 'Thiết kế mỏng nhẹ, pin 6000mAh.',
 '6.7" | 12GB | 256GB', 18, 0),
(2, 'MacBook Air M4 13 inch', 'macbook-air-m4', 27990000, 29990000,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/m/a/macbook-air-m4.png',
 'MacBook Air M4 siêu mỏng, pin 18 giờ.',
 'Chip M4 | 16GB RAM | SSD 256GB | 13.6"', 8, 1),
(2, 'MacBook Pro 14 M4 Pro', 'macbook-pro-14-m4', 42990000, 45990000,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/m/a/macbook-pro-14.png',
 'Hiệu năng chuyên nghiệp cho dev và designer.',
 'M4 Pro | 24GB | 512GB SSD', 5, 1),
(2, 'ASUS ROG Zephyrus G16', 'asus-rog-g16', 35990000, 38990000,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/l/a/laptop-gaming-asus.png',
 'Laptop gaming RTX 5070, màn 240Hz.',
 'Intel Core Ultra 9 | RTX 5070 | 32GB | 1TB', 6, 1),
(2, 'Dell XPS 14', 'dell-xps-14', 31990000, NULL,
 'https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/d/e/dell-xps.png',
 'Laptop cao cấp cho văn phòng.',
 'Core Ultra 7 | 16GB | 512GB | OLED 3.2K', 10, 0);
