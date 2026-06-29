/* =========================================================
   profile.js — JavaScript cho trang hồ sơ người dùng
   Yêu cầu: jQuery 3.x (nạp trước file này)
   ========================================================= */
$(function () {
    /* =========================================================
       SCROLL TO TOP ON REFRESH
       ========================================================= */
    if (history.scrollRestoration) {
        history.scrollRestoration = 'manual';
    }
    window.onbeforeunload = function () {
        window.scrollTo(0, 0);
    };

    /* =========================================================
       0. AUTO SWITCH TAB FROM URL OR LOCALSTORAGE
       ========================================================= */
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('tab') === 'orders') {
        setTimeout(() => { $('#navOrders').click(); }, 50);
    } else {
        const savedTab = localStorage.getItem('profileActiveTab');
        if (savedTab) {
            setTimeout(() => { $('#' + savedTab).click(); }, 50);
        }
    }

    /* =========================================================
       1. TAB NAVIGATION
       ========================================================= */
    $('.nav-link').on('click', function (e) {
        e.preventDefault();

        const target = '#' + $(this).data('target');
        
        // Save to localStorage
        localStorage.setItem('profileActiveTab', $(this).attr('id'));

        // Active class on nav
        $('.nav-link').removeClass('active');
        $(this).addClass('active');

        // Switch section with fade
        $('.content-section').fadeOut(180, function () {
            $(target).fadeIn(220);
        });

        // Close sidebar on mobile
        if ($(window).width() <= 768) closeSidebar();
    });

    /* =========================================================
       2. HAMBURGER MENU (Mobile)
       ========================================================= */
    function openSidebar() {
        $('#profileSidebar').addClass('open');
        $('#sidebarOverlay').addClass('show');
    }
    function closeSidebar() {
        $('#profileSidebar').removeClass('open');
        $('#sidebarOverlay').removeClass('show');
    }

    $('#hamburgerBtn').on('click', openSidebar);
    $('#sidebarOverlay').on('click', closeSidebar);

    /* =========================================================
       3. TOGGLE PASSWORD VISIBILITY
       ========================================================= */
    $('.input-toggle-btn').on('click', function () {
        const fieldId = $(this).data('target');
        const $field  = $('#' + fieldId);
        const $icon   = $(this).find('i');

        if ($field.attr('type') === 'password') {
            $field.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $field.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    /* =========================================================
       4. PASSWORD STRENGTH METER
       ========================================================= */
    $('#new_pass').on('input', function () {
        const val = $(this).val();
        let score = 0;
        if (val.length >= 8)           score++;
        if (/[A-Z]/.test(val))         score++;
        if (/[0-9]/.test(val))         score++;
        if (/[^A-Za-z0-9]/.test(val))  score++;

        const configs = [
            { w: '0%',   bg: '#e2e8f0', text: 'Nhập mật khẩu để kiểm tra độ mạnh' },
            { w: '25%',  bg: '#ef4444', text: '⚠️ Rất yếu' },
            { w: '50%',  bg: '#f59e0b', text: '⚡ Trung bình' },
            { w: '75%',  bg: '#06b6d4', text: '✅ Mạnh' },
            { w: '100%', bg: '#10b981', text: '🔐 Rất mạnh' },
        ];
        const c = val.length === 0 ? configs[0] : configs[score];
        $('#strengthFill').css({ width: c.w, background: c.bg });
        $('#strengthText').text(c.text);
    });

    /* =========================================================
       5. PASSWORD MATCH CHECK
       ========================================================= */
    $('#confirm_pass').on('input', function () {
        const newPass  = $('#new_pass').val();
        const confPass = $(this).val();
        const $hint    = $('#matchHint');

        if (confPass === '') {
            $hint.text('').css('color', '');
            return;
        }
        if (newPass === confPass) {
            $hint.text('✅ Mật khẩu khớp!').css('color', '#10b981');
        } else {
            $hint.text('❌ Mật khẩu không khớp').css('color', '#ef4444');
        }
    });

    /* =========================================================
       6. TOAST NOTIFICATION HELPER
       ========================================================= */
    function showToast(msg, type = 'success') {
        const icons = {
            success: 'fa-circle-check',
            error:   'fa-circle-xmark',
            warn:    'fa-triangle-exclamation'
        };
        const colorMap = { success: '#10b981', error: '#ef4444', warn: '#f59e0b' };
        const $toast = $(`
            <div class="toast toast-${type}">
                <i class="fa-solid ${icons[type] || icons.success}" style="color:${colorMap[type] || colorMap.success}"></i>
                <span>${msg}</span>
            </div>
        `);
        $('#toastContainer').append($toast);
        setTimeout(() => $toast.fadeOut(400, function () { $(this).remove(); }), 3000);
    }

    /* =========================================================
       7. FORM SUBMIT — Thông tin cá nhân
       ========================================================= */
    $('#formInfo').on('submit', function (e) {
        e.preventDefault();

        // Validate phía client trước khi gửi
        const hoTen = $.trim($('#ho_ten').val());
        const email = $.trim($('#email').val());
        const sdt   = $.trim($('#sdt').val());

        if (!hoTen) {
            showToast('Họ tên không được để trống!', 'error'); return;
        }
        const emailReg = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailReg.test(email)) {
            showToast('Email không hợp lệ!', 'error'); return;
        }
        if (sdt && !/^0[0-9]{9,10}$/.test(sdt)) {
            showToast('Số điện thoại không hợp lệ (10-11 số, bắt đầu bằng 0)!', 'warn'); return;
        }

        const $btn = $('#btnSaveInfo');
        $btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...').prop('disabled', true);

        $.ajax({
            url: 'profile.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action:    'update_info',
                ho_ten:    hoTen,
                email:     email,
                sdt:       sdt,
                dia_chi:   $('#dia_chi').val(),
                gioi_tinh: $('#gioi_tinh').val(),
                ngay_sinh: $('#ngay_sinh').val()
            },
            success: function (res) {
                $btn.html('<i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi').prop('disabled', false);
                showToast((res.success ? '✅ ' : '❌ ') + res.message, res.success ? 'success' : 'error');
            },
            error: function () {
                $btn.html('<i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi').prop('disabled', false);
                showToast('Lỗi kết nối máy chủ. Vui lòng thử lại!', 'error');
            }
        });
    });

    /* =========================================================
       8. FORM SUBMIT — Đổi mật khẩu
       ========================================================= */
    $('#formPassword').on('submit', function (e) {
        e.preventDefault();
        const oldP  = $('#old_pass').val();
        const newP  = $('#new_pass').val();
        const confP = $('#confirm_pass').val();

        // Client-side kiểm tra nhanh
        if (newP !== confP) {
            showToast('Mật khẩu xác nhận không khớp!', 'error'); return;
        }
        if (newP.length < 8) {
            showToast('Mật khẩu phải có ít nhất 8 ký tự!', 'warn'); return;
        }

        const $btn = $('#btnSavePass');
        $btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Đang cập nhật...').prop('disabled', true);

        $.ajax({
            url: 'profile.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action:       'change_password',
                old_pass:     oldP,
                new_pass:     newP,
                confirm_pass: confP
            },
            success: function (res) {
                $btn.html('<i class="fa-solid fa-shield-halved"></i> Cập nhật mật khẩu').prop('disabled', false);
                if (res.success) {
                    $('#formPassword')[0].reset();
                    $('#strengthFill').css({ width: '0%' });
                    $('#strengthText').text('Nhập mật khẩu để kiểm tra độ mạnh');
                    $('#matchHint').text('');
                    showToast('🔐 ' + res.message, 'success');
                } else {
                    showToast('❌ ' + res.message, 'error');
                }
            },
            error: function () {
                $btn.html('<i class="fa-solid fa-shield-halved"></i> Cập nhật mật khẩu').prop('disabled', false);
                showToast('Lỗi kết nối máy chủ. Vui lòng thử lại!', 'error');
            }
        });
    });

    /* =========================================================
       9. ORDER TABLE SEARCH FILTER
       ========================================================= */
    $('#orderSearch').on('input', function () {
        const keyword = $(this).val().toLowerCase();
        let count = 0;
        $('#ordersTable tbody tr').each(function () {
            const text = $(this).find('.order-id').text().toLowerCase();
            if (text.includes(keyword)) {
                $(this).show(); count++;
            } else {
                $(this).hide();
            }
        });
        $('#rowCount').text('Hiển thị ' + count + ' đơn hàng');
    });

    /* =========================================================
       10. EXPORT CSV (demo)
       ========================================================= */
    $('#btnExport').on('click', function () {
        showToast('📥 Đang xuất file CSV...', 'warn');
    });

    /* =========================================================
       11. ORDER DETAIL MODAL
       ========================================================= */
    const badgeMap = {
        'Chưa xác nhận':   { cls: 'badge-warning', icon: 'fa-clock' },
        'Đã xác nhận':     { cls: 'badge-info',    icon: 'fa-clipboard-check' },
        'Đang đóng gói':   { cls: 'badge-info',    icon: 'fa-box-open' },
        'Đang vận chuyển': { cls: 'badge-primary', icon: 'fa-truck-fast' },
        'Đã giao':         { cls: 'badge-success', icon: 'fa-circle-check' },
        'Đã hủy':          { cls: 'badge-danger',  icon: 'fa-circle-xmark' },
    };

    function openOrderDetail(maHD) {
        // Reset
        $('#orderDetailBody').html('<tr><td colspan="5" style="text-align:center;padding:30px;color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</td></tr>');
        $('#orderDetailMeta, #orderDetailFooter, #orderDetailSubtitle').html('');
        $('#orderDetailModal').addClass('show');
        $('body').css('overflow', 'hidden');

        $.ajax({
            url: 'profile.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'order_detail', ma_hd: maHD },
            success: function (res) {
                if (!res.success) {
                    $('#orderDetailBody').html(`<tr><td colspan="5" style="text-align:center;padding:30px;color:#ef4444;"><i class="fa-solid fa-circle-xmark"></i> ${res.message}</td></tr>`);
                    return;
                }

                const hd = res.hoadon;
                const b  = badgeMap[hd.TrangThai] || { cls: 'badge-warning', icon: 'fa-clock' };

                // Subtitle
                $('#orderDetailSubtitle').text(`Mã đơn: ${hd.MaHoaDon} • Ngày đặt: ${hd.NgayLap}`);

                // Meta
                $('#orderDetailMeta').html(`
                    <div class="order-meta-item">
                        <span class="order-meta-label"><i class="fa-solid fa-hashtag"></i> Mã đơn hàng</span>
                        <span class="order-meta-value order-id">${hd.MaHoaDon}</span>
                    </div>
                    <div class="order-meta-item">
                        <span class="order-meta-label"><i class="fa-regular fa-calendar"></i> Ngày đặt</span>
                        <span class="order-meta-value">${hd.NgayLap}</span>
                    </div>
                    <div class="order-meta-item">
                        <span class="order-meta-label"><i class="fa-solid fa-circle-half-stroke"></i> Trạng thái</span>
                        <span class="order-meta-value">
                            <span class="badge ${b.cls}"><i class="fa-solid ${b.icon}"></i> ${hd.TrangThai}</span>
                        </span>
                    </div>
                `);

                // Table rows
                if (res.items.length === 0) {
                    $('#orderDetailBody').html('<tr><td colspan="5" style="text-align:center;padding:30px;color:#94a3b8;">Không có sản phẩm trong đơn hàng này.</td></tr>');
                } else {
                    let rows = '';
                    const canCancel = (hd.RawTrangThai === 'Chưa xác nhận' || hd.RawTrangThai === 'Đã xác nhận');
                    
                    res.items.forEach((item, i) => {
                        const tien = Number(item.ThanhTien).toLocaleString('vi-VN') + '₫';
                        const phienBan = [item.TenMau, item.KichThuoc].filter(Boolean).join(' / ');
                        
                        let qtyHtml = item.SoLuong;
                        if (canCancel) {
                            qtyHtml += ` <br><a href="#" class="btn-reduce-qty" data-macthd="${item.MaCTHD}" data-max="${item.SoLuong}" style="font-size:0.75rem; color:#ef4444; text-decoration:none;"><i class="fa-solid fa-minus-circle"></i> Hủy bớt</a>`;
                        }

                        rows += `
                            <tr>
                                <td style="font-weight:600;color:var(--text-muted);">${i + 1}</td>
                                <td style="font-weight:500;">${item.TenSanPham}</td>
                                <td><span style="background:#f1f5f9;padding:3px 8px;border-radius:6px;font-size:.8rem;">${phienBan || '—'}</span></td>
                                <td style="text-align:center;font-weight:600;">${qtyHtml}</td>
                                <td><span class="order-amount">${tien}</span></td>
                            </tr>
                        `;
                    });
                    $('#orderDetailBody').html(rows);
                }

                // Footer
                $('#orderDetailFooter').html(`
                    <span style="color:var(--text-muted);font-size:.85rem;">${res.items.length} sản phẩm</span>
                    <div style="text-align:right;">
                        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:2px;">Tổng thanh toán</div>
                        <div style="font-size:1.2rem;font-weight:700;color:var(--primary);">${hd.TongTien}</div>
                    </div>
                `);
            },
            error: function () {
                $('#orderDetailBody').html('<tr><td colspan="5" style="text-align:center;padding:30px;color:#ef4444;"><i class="fa-solid fa-circle-xmark"></i> Lỗi kết nối máy chủ!</td></tr>');
            }
        });
    }

    function closeOrderDetail() {
        $('#orderDetailModal').removeClass('show');
        $('body').css('overflow', '');
    }

    // Click nút mắt ở bảng đơn hàng
    $(document).on('click', '.btn-view-order', function () {
        const maHD = $(this).data('mahd');
        openOrderDetail(maHD);
    });

    $('#btnCloseOrderDetail').on('click', closeOrderDetail);
    $('#orderDetailModal').on('click', function (e) {
        if ($(e.target).is('#orderDetailModal')) closeOrderDetail();
    });
    $(document).on('keydown.orderModal', function (e) {
        if (e.key === 'Escape' && $('#orderDetailModal').hasClass('show')) closeOrderDetail();
    });

    // Xử lý Hủy bớt số lượng
    $(document).on('click', '.btn-reduce-qty', function(e) {
        e.preventDefault();
        const maCTHD = $(this).data('macthd');
        const maxQty = $(this).data('max');
        
        const qtyToCancel = prompt(`Nhập số lượng muốn hủy (Tối đa: ${maxQty}):\nLưu ý: Hủy xong không thể khôi phục lại.`);
        if (qtyToCancel === null || qtyToCancel.trim() === '') return;
        
        const qty = parseInt(qtyToCancel);
        if (isNaN(qty) || qty <= 0 || qty > maxQty) {
            alert('Số lượng không hợp lệ!');
            return;
        }
        
        if (!confirm(`Bạn chắc chắn muốn hủy bớt ${qty} sản phẩm này chứ?`)) return;
        
        $.ajax({
            url: 'profile.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'reduce_item_qty', ma_cthd: maCTHD, qty_cancel: qty },
            success: function (res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    // Reload the modal by simulating a click on the view button if we can find it, 
                    // or just reload page since TongTien in main table also needs update
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function () {
                showToast('Lỗi kết nối máy chủ!', 'error');
            }
        });
    });

    // Xử lý Hủy toàn bộ đơn hàng (ngoài danh sách)
    $(document).on('click', '.btn-cancel-order', function(e) {
        e.preventDefault();
        const maHD = $(this).data('mahd');
        if (!confirm('Bạn có chắc chắn muốn hủy TOÀN BỘ đơn hàng này không? Hành động này không thể khôi phục.')) return;
        
        $.ajax({
            url: 'profile.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'cancel_order', ma_hd: maHD },
            success: function (res) {
                if (res.success) {
                    showToast(res.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(res.message, 'error');
                }
            },
            error: function () {
                showToast('Lỗi kết nối máy chủ!', 'error');
            }
        });
    });

    /* =========================================================
       12. DELETE ACCOUNT MODAL
       ========================================================= */
    // Mở modal
    $('#btnOpenDeleteModal').on('click', function () {
        $('#deleteStep1').show();
        $('#deleteStep2').hide();
        $('#deletePassInput').val('');
        $('#deletePassError').text('');
        $('#deleteModal').addClass('show');
        $('body').css('overflow', 'hidden');
    });

    // Đóng modal
    function closeDeleteModal() {
        $('#deleteModal').removeClass('show');
        $('body').css('overflow', '');
    }

    $('#btnCancelDelete').on('click', closeDeleteModal);

    // Click ngoài modal để đóng
    $('#deleteModal').on('click', function (e) {
        if ($(e.target).is('#deleteModal')) closeDeleteModal();
    });

    // ESC để đóng
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') closeDeleteModal();
    });

    // Bước 1 → Bước 2
    $('#btnGoStep2').on('click', function () {
        $('#deleteStep1').hide();
        $('#deleteStep2').show();
        setTimeout(() => $('#deletePassInput').focus(), 100);
    });

    // Bước 2 → Bước 1
    $('#btnBackStep1').on('click', function () {
        $('#deleteStep2').hide();
        $('#deleteStep1').show();
        $('#deletePassInput').val('');
        $('#deletePassError').text('');
    });

    // Xác nhận xóa
    $('#btnConfirmDelete').on('click', function () {
        const pass = $.trim($('#deletePassInput').val());
        if (!pass) {
            $('#deletePassError').text('Vui lòng nhập mật khẩu!');
            $('#deletePassInput').focus();
            return;
        }
        $('#deletePassError').text('');

        const $btn = $(this);
        $btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Đang xử lý...').prop('disabled', true);

        $.ajax({
            url: 'profile.php',
            method: 'POST',
            dataType: 'json',
            data: { action: 'delete_account', confirm_pass: pass },
            success: function (res) {
                $btn.html('<i class="fa-solid fa-trash-can"></i> Xóa vĩnh viễn').prop('disabled', false);
                if (res.success) {
                    closeDeleteModal();
                    $('body').append(`
                        <div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;
                            display:flex;align-items:center;justify-content:center;">
                            <div style="background:#fff;border-radius:16px;padding:40px 32px;
                                text-align:center;max-width:380px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
                                <div style="font-size:3rem;margin-bottom:16px;">🗑️</div>
                                <h3 style="color:#1e293b;margin-bottom:10px;">Tài khoản đã xóa</h3>
                                <p style="color:#64748b;font-size:.9rem;">${res.message}<br>Đang chuyển về trang đăng nhập...</p>
                            </div>
                        </div>
                    `);
                    setTimeout(() => { window.location.href = 'login.php'; }, 2500);
                } else {
                    $('#deletePassError').text(res.message);
                }
            },
            error: function () {
                $btn.html('<i class="fa-solid fa-trash-can"></i> Xóa vĩnh viễn').prop('disabled', false);
                $('#deletePassError').text('Lỗi kết nối máy chủ. Vui lòng thử lại!');
            }
        });
    });

});
