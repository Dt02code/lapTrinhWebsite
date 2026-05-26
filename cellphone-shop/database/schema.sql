-- Tạo database và bảng cho web bán điện thoại / laptop
-- Chạy file này trong phpMyAdmin hoặc MySQL Workbench

CREATE DATABASE IF NOT EXISTS cellphone_shop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cellphone_shop;

-- Danh mục: điện thoại, laptop
CREATE TABLE IF NOT EXISTS danh_muc (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ten VARCHAR(100) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Sản phẩm
CREATE TABLE IF NOT EXISTS san_pham (
  id INT AUTO_INCREMENT PRIMARY KEY,
  danh_muc_id INT NOT NULL,
  ten VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  gia DECIMAL(12,0) NOT NULL,
  gia_cu DECIMAL(12,0) NULL,
  hinh_anh VARCHAR(500) NOT NULL DEFAULT '',
  mo_ta TEXT,
  thong_so TEXT,
  ton_kho INT NOT NULL DEFAULT 10,
  noi_bat TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id)
) ENGINE=InnoDB;

-- Người dùng (đăng ký tùy chọn)
CREATE TABLE IF NOT EXISTS nguoi_dung (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ho_ten VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mat_khau VARCHAR(255) NOT NULL,
  so_dien_thoai VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Đơn hàng
CREATE TABLE IF NOT EXISTS don_hang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nguoi_dung_id INT NULL,
  ho_ten VARCHAR(150) NOT NULL,
  email VARCHAR(150),
  so_dien_thoai VARCHAR(20) NOT NULL,
  dia_chi TEXT NOT NULL,
  phuong_thuc_thanh_toan ENUM('cod','chuyen_khoan','momo','vnpay') NOT NULL DEFAULT 'cod',
  tong_tien DECIMAL(12,0) NOT NULL,
  trang_thai ENUM('cho','dang_giao','hoan_thanh','huy') NOT NULL DEFAULT 'cho',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chi_tiet_don_hang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  don_hang_id INT NOT NULL,
  san_pham_id INT NOT NULL,
  so_luong INT NOT NULL,
  don_gia DECIMAL(12,0) NOT NULL,
  FOREIGN KEY (don_hang_id) REFERENCES don_hang(id),
  FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
) ENGINE=InnoDB;
