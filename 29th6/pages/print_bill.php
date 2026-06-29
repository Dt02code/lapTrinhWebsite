<?php
session_start();
require '../connect.php';

$maHD = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($maHD <= 0) die("Mã hóa đơn không hợp lệ.");

// Lấy thông tin hóa đơn
$stmt = $conn->prepare("SELECT * FROM hoadon WHERE MaHoaDon = ?");
$stmt->bind_param('i', $maHD);
$stmt->execute();
$hd = $stmt->get_result()->fetch_assoc();
if (!$hd) die("Không tìm thấy hóa đơn.");

// Kiểm tra quyền: Admin có thể in mọi hóa đơn, Khách chỉ in được hóa đơn của mình
$isAdmin = isset($_SESSION['admin_logged_in']);
if (!$isAdmin && (!isset($_SESSION['user']) || $_SESSION['user'] !== $hd['TenDangNhap'])) {
    die("Bạn không có quyền xem hóa đơn này.");
}

// Lấy chi tiết
$stmt2 = $conn->prepare("
    SELECT ct.*, sp.TenSanPham 
    FROM chitiethoadon ct 
    JOIN sanpham sp ON ct.MaSanPham = sp.MaSanPham 
    WHERE ct.MaHoaDon = ?
");
$stmt2->bind_param('i', $maHD);
$stmt2->execute();
$items = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn #<?= $maHD ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; background: #fff; color: #000; padding: 20px; font-size: 14px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #000; padding-bottom: 20px; }
        .invoice-header h1 { margin: 0; font-size: 28px; }
        .invoice-header .company-info { text-align: right; }
        .invoice-details { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f8f8; font-weight: bold; }
        .table .right { text-align: right; }
        .table .center { text-align: center; }
        .total-row th, .total-row td { border-top: 2px solid #000; font-size: 16px; font-weight: bold; }
        .print-btn { display: block; width: 100%; max-width: 200px; margin: 0 auto 30px; padding: 10px; background: #0ea5e9; color: #fff; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold; cursor: pointer; border: none; }
        @media print {
            .print-btn { display: none; }
            .invoice-box { border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ In Hóa Đơn</button>

    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <h1>HÓA ĐƠN MUA HÀNG</h1>
                <p style="margin: 5px 0; color: #555;">Mã đơn: <strong>#DH-<?= str_pad($maHD, 5, '0', STR_PAD_LEFT) ?></strong></p>
                <p style="margin: 5px 0; color: #555;">Ngày lập: <?= date('d/m/Y H:i', strtotime($hd['NgayLap'])) ?></p>
            </div>
            <div class="company-info">
                <h2 style="margin:0;">CellPhoneK</h2>
                <p style="margin: 5px 0; color: #555;">Hotline: 1900 1234</p>
                <p style="margin: 5px 0; color: #555;">Website: cellphonek.com</p>
            </div>
        </div>

        <div class="invoice-details">
            <div>
                <h3 style="margin-top:0;">THÔNG TIN KHÁCH HÀNG:</h3>
                <p>Họ tên: <strong><?= htmlspecialchars($hd['HoTenNhan']) ?></strong></p>
                <p>Điện thoại: <strong><?= htmlspecialchars($hd['SoDienThoaiNhan']) ?></strong></p>
                <p>Địa chỉ: <strong><?= htmlspecialchars($hd['DiaChiNhan']) ?></strong></p>
            </div>
            <div style="text-align: right;">
                <h3 style="margin-top:0;">THÔNG TIN THANH TOÁN:</h3>
                <p>Phương thức: <strong><?= $hd['PhuongThucThanhToan'] ?></strong></p>
                <p>Trạng thái đơn: <strong><?= htmlspecialchars($hd['TrangThai']) ?></strong></p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Phiên bản</th>
                    <th class="center">SL</th>
                    <th class="right">Đơn giá</th>
                    <th class="right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; while($item = $items->fetch_assoc()): 
                    $donGia = $item['ThanhTien'] / $item['SoLuong'];
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($item['TenSanPham']) ?></td>
                    <td><?= htmlspecialchars($item['TenMau']) ?> / <?= htmlspecialchars($item['KichThuoc']) ?></td>
                    <td class="center"><?= $item['SoLuong'] ?></td>
                    <td class="right"><?= number_format($donGia, 0, ',', '.') ?>đ</td>
                    <td class="right"><?= number_format($item['ThanhTien'], 0, ',', '.') ?>đ</td>
                </tr>
                <?php endwhile; ?>
                <tr class="total-row">
                    <td colspan="5" class="right">TỔNG CỘNG:</td>
                    <td class="right"><?= number_format($hd['TongTien'], 0, ',', '.') ?>đ</td>
                </tr>
            </tbody>
        </table>

        <div style="text-align: center; margin-top: 50px; color: #555;">
            <p><i>Cảm ơn quý khách đã mua sắm tại CellPhoneK!</i></p>
        </div>
    </div>
</body>
</html>
