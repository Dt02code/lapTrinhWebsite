<?php
session_start();
require '../connect.php';

header('Content-Type: application/json; charset=utf-8');

// KHỞI TẠO GIỎ HÀNG
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // THÊM VÀO GIỎ HÀNG
    case 'add':
        $ma_gia      = isset($_POST['ma_gia'])   ? (int)$_POST['ma_gia']   : 0;
        $quantity    = isset($_POST['quantity'])  ? (int)$_POST['quantity'] : 1;

        if ($ma_gia <= 0) {
            echo json_encode(['success' => false, 'message' => 'Biến thể sản phẩm không hợp lệ.']);
            exit;
        }
        if ($quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Số lượng phải lớn hơn 0.']);
            exit;
        }

        // Lấy thông tin biến thể từ DB (Prepared Statement)
        $sql = "
            SELECT
                gsp.MaGia, gsp.MaSanPham, gsp.MaMau, gsp.MaRam,
                gsp.GiaMoi, gsp.SoLuong,
                sp.TenSanPham,
                c.TenMau,
                rr.KichThuoc AS TenRam,
                (
                    SELECT img.DiaChiAnh FROM image img
                    WHERE img.MaSanPham = gsp.MaSanPham
                      AND img.MaMau     = gsp.MaMau
                    LIMIT 1
                ) AS DiaChiAnh
            FROM giasanpham gsp
            INNER JOIN sanpham        sp  ON sp.MaSanPham = gsp.MaSanPham
            INNER JOIN colors         c   ON c.MaMau      = gsp.MaMau
            INNER JOIN ram_rom_option rr  ON rr.MaRam     = gsp.MaRam
            WHERE gsp.MaGia = ?
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $ma_gia);
        $stmt->execute();
        $variant = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$variant) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy sản phẩm.']);
            exit;
        }

        // KEY duy nhất: MaSanPham_MaMau_MaRam
        $cart_key = $variant['MaSanPham'] . '_' . $variant['MaMau'] . '_' . $variant['MaRam'];

        // Tính tổng số lượng sau khi thêm
        $current_qty  = isset($_SESSION['cart'][$cart_key]) ? $_SESSION['cart'][$cart_key]['quantity'] : 0;
        $new_qty      = $current_qty + $quantity;

        // Kiểm tra tồn kho
        if ($new_qty > $variant['SoLuong']) {
            $remain = $variant['SoLuong'] - $current_qty;
            if ($remain <= 0) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm này đã đạt giới hạn tồn kho trong giỏ hàng (' . $variant['SoLuong'] . ' sp).']);
            } else {
                echo json_encode(['success' => false, 'message' => "Chỉ còn {$remain} sản phẩm có thể thêm (tồn kho: {$variant['SoLuong']})."]);
            }
            exit;
        }

        // Thêm hoặc cập nhật giỏ hàng
        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key]['quantity'] = $new_qty;
        } else {
            $_SESSION['cart'][$cart_key] = [
                'ma_gia'     => $variant['MaGia'],
                'product_id' => $variant['MaSanPham'],
                'name'       => $variant['TenSanPham'],
                'color'      => $variant['TenMau'],
                'ram'        => $variant['TenRam'],
                'price'      => (int)$variant['GiaMoi'],
                'stock'      => (int)$variant['SoLuong'],
                'image'      => $variant['DiaChiAnh'] ?? 'img/no-image.png',
                'quantity'   => $new_qty,
            ];
        }

        $cart_total_count = 0;
        foreach ($_SESSION['cart'] as $it) $cart_total_count += $it['quantity'];

        echo json_encode([
            'success'    => true,
            'message'    => '✅ Đã thêm "' . $variant['TenSanPham'] . '" vào giỏ hàng!',
            'cart_count' => $cart_total_count,
        ]);
        break;

    // CẬP NHẬT SỐ LƯỢNG
    case 'update':
        $cart_key = $_POST['key']      ?? '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

        if (!isset($_SESSION['cart'][$cart_key])) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.']);
            exit;
        }
        if ($quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Số lượng phải lớn hơn 0.']);
            exit;
        }

        $stock = $_SESSION['cart'][$cart_key]['stock'];
        if ($quantity > $stock) {
            echo json_encode(['success' => false, 'message' => "Số lượng vượt quá tồn kho ($stock sp)."]);
            exit;
        }

        $_SESSION['cart'][$cart_key]['quantity'] = $quantity;
        $subtotal = $_SESSION['cart'][$cart_key]['price'] * $quantity;

        // Tính lại tổng tiền
        $grand_total = 0;
        foreach ($_SESSION['cart'] as $it) $grand_total += $it['price'] * $it['quantity'];

        $cart_total_count = 0;
        foreach ($_SESSION['cart'] as $it) $cart_total_count += $it['quantity'];

        echo json_encode([
            'success'     => true,
            'subtotal'    => number_format($subtotal, 0, ',', '.') . 'đ',
            'grand_total' => number_format($grand_total, 0, ',', '.') . 'đ',
            'cart_count'  => $cart_total_count,
        ]);
        break;

    // XÓA SẢN PHẨM
    case 'remove':
        $cart_key = $_POST['key'] ?? '';

        if (!isset($_SESSION['cart'][$cart_key])) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại trong giỏ hàng.']);
            exit;
        }

        unset($_SESSION['cart'][$cart_key]);

        $grand_total = 0;
        foreach ($_SESSION['cart'] as $it) $grand_total += $it['price'] * $it['quantity'];

        $cart_total_count = 0;
        foreach ($_SESSION['cart'] as $it) $cart_total_count += $it['quantity'];

        echo json_encode([
            'success'     => true,
            'grand_total' => number_format($grand_total, 0, ',', '.') . 'đ',
            'cart_count'  => $cart_total_count,
            'cart_empty'  => empty($_SESSION['cart']),
        ]);
        break;

    // LẤY SỐ LƯỢNG GIỎ HÀNG (dùng cho badge)
    case 'get_count':
        $count = 0;
        foreach ($_SESSION['cart'] as $it) $count += $it['quantity'];
        echo json_encode(['success' => true, 'cart_count' => $count]);
        break;

    // LẤY VARIANT MẶC ĐỊNH (giá thấp nhất) CHO QUICK ADD
    case 'get_default_variant':
        $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        if ($pid <= 0) {
            echo json_encode(['success' => false]);
            exit;
        }
        $sql = "SELECT MaGia FROM giasanpham WHERE MaSanPham = ? ORDER BY GiaMoi ASC LIMIT 1";
        $st  = $conn->prepare($sql);
        $st->bind_param('i', $pid);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();
        if ($row) {
            echo json_encode(['success' => true, 'ma_gia' => $row['MaGia']]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ.']);
        break;
}
