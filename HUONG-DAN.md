# PhoneShop — Web bán điện thoại & laptop (giống CellphoneS)

## Các trang có sẵn

| Trang | File | Mô tả |
|-------|------|--------|
| Trang chủ | `index.php` | Banner + sản phẩm nổi bật |
| Điện thoại | `dien-thoai.php` | Danh sách điện thoại |
| Laptop | `laptop.php` | Danh sách laptop |
| Chi tiết SP | `san-pham.php?id=1` | Thông tin + thêm giỏ |
| Giỏ hàng | `gio-hang.php` | Xem/sửa giỏ |
| Thanh toán | `thanh-toan.php` | COD, chuyển khoản, MoMo, VNPay |
| Đăng nhập | `dang-nhap.php` | **Tùy chọn** |
| Đăng ký | `dang-ky.php` | **Tùy chọn** |

Menu dưới (mobile): Trang chủ · Điện thoại · Laptop · Giỏ hàng · Đăng nhập

---

## Cách gửi file SQL cho mình / Cursor hỗ trợ

### 1. Kéo thả vào Cursor (dễ nhất)
1. Mở thư mục project: `C:\Users\Admin\cellphone-shop`
2. Kéo file `.sql` của bạn vào thư mục `database\` (ví dụ: `database\data-cua-toi.sql`)
3. Chat lại: *"Mình đã gửi file SQL trong database, giúp nối vào web"*

### 2. Mở folder làm workspace
1. Cursor → **File → Open Folder**
2. Chọn `C:\Users\Admin\cellphone-shop`
3. Đính kèm file SQL trong chat (biểu tượng 📎) hoặc kéo vào ô chat

### 3. Copy nội dung SQL
- Mở file `.sql` bằng Notepad
- Copy toàn bộ → dán vào chat (file nhỏ < vài trăm dòng)

---

## Cài đặt trên máy Windows

### Bước 1: Cài XAMPP
- Tải: https://www.apachefriends.org/
- Cài Apache + MySQL

### Bước 2: Copy project
Copy folder `cellphone-shop` vào:
`C:\xampp\htdocs\cellphone-shop`

### Bước 3: Import database
1. Bật **Apache** và **MySQL** trong XAMPP Control Panel
2. Mở http://localhost/phpmyadmin
3. Tab **Import** → chọn file:
   - `database/schema.sql` (tạo bảng)
   - `database/sample_data.sql` (dữ liệu mẫu)
   - **HOẶC** file SQL của bạn (nếu đã có sẵn)

### Bước 4: Cấu hình kết nối
Mở `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cellphone_shop');  // đổi nếu DB bạn tên khác
define('DB_USER', 'root');
define('DB_PASS', '');  // XAMPP mặc định để trống
```

### Bước 5: Chạy web
Mở trình duyệt: **http://localhost/cellphone-shop/**

---

## Nếu CSDL của bạn khác tên bảng/cột

Gửi file SQL hoặc chụp cấu trúc bảng, mình sẽ chỉnh `includes/functions.php` cho khớp.

Bảng chuẩn project dùng:
- `danh_muc` (slug: `dien-thoai`, `laptop`)
- `san_pham`
- `nguoi_dung`
- `don_hang`, `chi_tiet_don_hang`
