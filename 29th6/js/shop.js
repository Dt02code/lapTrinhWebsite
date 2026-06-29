/* ============================================
   SHOP.JS – CellPhoneK E-commerce
   ============================================ */

'use strict';

/* ============================================
   SCROLL TO TOP ON REFRESH
   ============================================ */
if (history.scrollRestoration) {
    history.scrollRestoration = 'manual';
}
window.onbeforeunload = function () {
    window.scrollTo(0, 0);
};

/* ============================================
   TOAST NOTIFICATION
   ============================================ */
function showToast(message, type = 'success', duration = 3000) {
  const toast = document.getElementById('toastMessage');
  if (!toast) return;
  toast.textContent = message;
  toast.className   = 'toast ' + type;
  clearTimeout(toast._timer);
  toast._timer = setTimeout(() => {
    toast.className = 'toast hidden';
  }, duration);
}

/* ============================================
   UPDATE CART BADGE IN NAVBAR
   ============================================ */
function updateCartBadge(count) {
  const badges = document.querySelectorAll('.cart-badge');
  badges.forEach(b => {
    if (count > 0) {
      b.textContent = count;
      b.style.display = 'flex';
    } else {
      b.style.display = 'none';
    }
  });
  // Also update cart count label on cart page
  const label = document.querySelector('.cart-count-label');
  if (label) {
    const rows = document.querySelectorAll('.cart-row');
    label.textContent = '(' + rows.length + ' sản phẩm)';
  }
}

/* ============================================
   INDEX PAGE – QUICK ADD TO CART
   (Thêm nhanh với variant mặc định – giá thấp nhất)
   ============================================ */
function quickAddToCart(btn) {
  const productId = btn.dataset.id;

  // Lấy MaGia mặc định (giá thấp nhất) từ server
  const formData = new FormData();
  formData.append('action',     'get_default_variant');
  formData.append('product_id', productId);

  // Thêm loading state
  btn.disabled   = true;
  btn.textContent = '⏳ Đang thêm...';

  fetch('pages/cart_action.php', {
    method: 'POST',
    body:   formData,
  })
    .then(r => r.json())
    .then(data => {
      if (data.success && data.ma_gia) {
        addToCart(data.ma_gia, 1, function(res) {
          btn.disabled    = false;
          btn.textContent = '🛒 Thêm vào giỏ';
          if (res.success) {
            showToast(res.message, 'success');
            updateCartBadge(res.cart_count);
          } else {
            showToast(res.message, 'error');
          }
        });
      } else {
        // Nếu sản phẩm có nhiều biến thể, chuyển tới trang chi tiết
        window.location.href = 'pages/detail.php?id=' + productId;
      }
    })
    .catch(() => {
      btn.disabled    = false;
      btn.textContent = '🛒 Thêm vào giỏ';
      showToast('Vui lòng xem chi tiết để chọn biến thể.', 'info');
      setTimeout(() => {
        window.location.href = 'pages/detail.php?id=' + productId;
      }, 1200);
    });
}

/* ============================================
   CORE ADD TO CART (AJAX)
   ============================================ */
function addToCart(maGia, quantity, callback) {
  const formData = new FormData();
  formData.append('action',   'add');
  formData.append('ma_gia',   maGia);
  formData.append('quantity', quantity);

  // Detect relative path (index vs pages/)
  const actionUrl = _getCartActionUrl();

  fetch(actionUrl, { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => { if (callback) callback(data); })
    .catch(() => {
      if (callback) callback({ success: false, message: 'Lỗi kết nối máy chủ.' });
    });
}

/* ============================================
   DETAIL PAGE – VARIANT SELECTION
   ============================================ */
let selectedRam   = null;
let selectedColor = null;

// Khởi tạo sau khi page load (chỉ chạy trên detail.php)
document.addEventListener('DOMContentLoaded', function () {
  if (typeof VARIANTS === 'undefined') return;

  // Set default selections
  if (VARIANTS.length > 0) {
    selectedRam   = String(VARIANTS[0].MaRam);
    selectedColor = String(VARIANTS[0].MaMau);
  }
  _refreshVariantDisplay();
});

function selectOption(type, btn) {
  // Bỏ selected khỏi group
  const group = btn.closest('.option-btns');
  group.querySelectorAll('.opt-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');

  if (type === 'ram') {
    selectedRam = btn.dataset.ram;
    // Lọc màu sắc hợp lệ theo RAM mới
    _filterColorsByRam(selectedRam);
  } else {
    selectedColor = btn.dataset.color;
    // Đổi ảnh chính theo màu
    if (typeof IMAGES !== 'undefined' && IMAGES[selectedColor]) {
      const base = (typeof BASE_URL !== 'undefined') ? BASE_URL : '../';
      document.getElementById('mainProductImg').src = base + IMAGES[selectedColor];
      // Cập nhật thumbnail active
      document.querySelectorAll('.thumb-img').forEach(t => {
        t.classList.toggle('active', t.dataset.color === selectedColor);
      });
    }
  }
  _refreshVariantDisplay();
}

function selectThumbnail(img) {
  const color = img.dataset.color;
  // Click color button tương ứng
  const colorBtn = document.querySelector('#colorOptions .opt-btn[data-color="' + color + '"]');
  if (colorBtn) selectOption('color', colorBtn);
  else {
    // Chỉ đổi ảnh
    document.getElementById('mainProductImg').src = img.src;
    document.querySelectorAll('.thumb-img').forEach(t => t.classList.remove('active'));
    img.classList.add('active');
  }
}

function _filterColorsByRam(ramId) {
  // Tìm các màu hợp lệ với RAM đã chọn
  const validColors = new Set(
    VARIANTS.filter(v => String(v.MaRam) === String(ramId)).map(v => String(v.MaMau))
  );
  const colorBtns = document.querySelectorAll('#colorOptions .opt-btn');
  let hasSelected = false;
  colorBtns.forEach(btn => {
    const valid = validColors.has(btn.dataset.color);
    btn.disabled = !valid;
    btn.style.opacity = valid ? '1' : '0.35';
    if (btn.classList.contains('selected') && valid) hasSelected = true;
  });
  // Nếu màu đang chọn không hợp lệ, chọn màu đầu tiên hợp lệ
  if (!hasSelected) {
    colorBtns.forEach(btn => {
      if (!btn.disabled && validColors.has(btn.dataset.color)) {
        btn.classList.add('selected');
        selectedColor = btn.dataset.color;
        return;
      }
    });
  }
}

function _refreshVariantDisplay() {
  if (typeof VARIANTS === 'undefined') return;

  const v = VARIANTS.find(
    v => String(v.MaRam) === String(selectedRam) && String(v.MaMau) === String(selectedColor)
  ) || VARIANTS.find(v => String(v.MaRam) === String(selectedRam))
    || VARIANTS[0];

  if (!v) return;

  // Cập nhật giá
  const newP = document.getElementById('displayPriceNew');
  const oldP = document.getElementById('displayPriceOld');
  const disc = document.getElementById('displayDiscount');
  if (newP) newP.textContent = _fmt(v.GiaMoi) + 'đ';
  if (oldP) {
    if (v.GiaCu > v.GiaMoi) {
      oldP.textContent  = _fmt(v.GiaCu) + 'đ';
      oldP.style.display = '';
    } else {
      oldP.style.display = 'none';
    }
  }
  if (disc) {
    if (v.GiaCu > v.GiaMoi) {
      const pct = Math.round((1 - v.GiaMoi / v.GiaCu) * 100);
      disc.textContent = '-' + pct + '%';
      disc.style.display = '';
    } else {
      disc.style.display = 'none';
    }
  }

  // Cập nhật tồn kho
  const stockInfo = document.getElementById('stockInfo');
  const stockQty  = document.getElementById('stockQty');
  if (stockQty) stockQty.textContent = v.SoLuong;
  if (stockInfo) {
    stockInfo.innerHTML = v.SoLuong > 0
      ? '<span class="in-stock">✅ Còn hàng (<span id="stockQty">' + v.SoLuong + '</span> sản phẩm)</span>'
      : '<span class="out-stock">❌ Hết hàng</span>';
  }

  // Cập nhật hidden inputs
  _setHidden('selectedMaGia',   v.MaGia);
  _setHidden('selectedMaRam',   v.MaRam);
  _setHidden('selectedMaMau',   v.MaMau);
  _setHidden('selectedStock',   v.SoLuong);

  // Cập nhật max của qty input
  const qtyInput = document.getElementById('qtyInput');
  if (qtyInput) {
    qtyInput.max = v.SoLuong;
    if (parseInt(qtyInput.value) > v.SoLuong) qtyInput.value = v.SoLuong;
    if (parseInt(qtyInput.value) < 1)         qtyInput.value = 1;
  }

  // Disable nút nếu hết hàng
  const addBtn = document.getElementById('btnAddCart');
  if (addBtn) {
    addBtn.disabled  = v.SoLuong <= 0;
    addBtn.textContent = v.SoLuong <= 0 ? '❌ Hết hàng' : '🛒 Thêm vào giỏ hàng';
  }
}

/* ============================================
   DETAIL PAGE – QUANTITY CONTROL
   ============================================ */
function changeQty(delta) {
  const input    = document.getElementById('qtyInput');
  const stock    = parseInt(document.getElementById('selectedStock')?.value || 999);
  let   newValue = parseInt(input.value || 1) + delta;
  if (newValue < 1)     newValue = 1;
  if (newValue > stock) newValue = stock;
  input.value = newValue;
}

function validateQtyInput(input) {
  const stock = parseInt(document.getElementById('selectedStock')?.value || 999);
  let   val   = parseInt(input.value);
  if (isNaN(val) || val < 1) val = 1;
  if (val > stock)            val = stock;
  input.value = val;
}

/* ============================================
   DETAIL PAGE – ADD TO CART BUTTON
   ============================================ */
function addToCartDetail() {
  const maGia   = document.getElementById('selectedMaGia')?.value;
  const stock   = parseInt(document.getElementById('selectedStock')?.value || 0);
  const qty     = parseInt(document.getElementById('qtyInput')?.value || 1);
  const btn     = document.getElementById('btnAddCart');

  if (!maGia || maGia === '0') {
    showToast('Vui lòng chọn biến thể sản phẩm.', 'error'); return;
  }
  if (stock <= 0) {
    showToast('Sản phẩm này đã hết hàng.', 'error'); return;
  }
  if (qty <= 0) {
    showToast('Số lượng phải lớn hơn 0.', 'error'); return;
  }

  if (btn) { btn.disabled = true; btn.textContent = '⏳ Đang thêm...'; }

  addToCart(maGia, qty, function (data) {
    if (btn) { btn.disabled = false; btn.textContent = '🛒 Thêm vào giỏ hàng'; }
    if (data.success) {
      showToast(data.message, 'success');
      updateCartBadge(data.cart_count);
    } else {
      showToast(data.message, 'error');
    }
  });
}

function buyNowDetail() {
  const maGia   = document.getElementById('selectedMaGia')?.value;
  const stock   = parseInt(document.getElementById('selectedStock')?.value || 0);
  const qty     = parseInt(document.getElementById('qtyInput')?.value || 1);
  const btn     = document.getElementById('btnBuyNow');

  if (!maGia || maGia === '0') {
    showToast('Vui lòng chọn biến thể sản phẩm.', 'error'); return;
  }
  if (stock <= 0) {
    showToast('Sản phẩm này đã hết hàng.', 'error'); return;
  }
  if (qty <= 0) {
    showToast('Số lượng phải lớn hơn 0.', 'error'); return;
  }

  if (btn) { btn.disabled = true; btn.textContent = '⏳ Đang xử lý...'; }

  addToCart(maGia, qty, function (data) {
    if (data.success) {
      // Thành công, điều hướng ngay sang trang checkout
      window.location.href = 'checkout.php';
    } else {
      if (btn) { btn.disabled = false; btn.textContent = '⚡ Mua ngay'; }
      showToast(data.message, 'error');
    }
  });
}

/* ============================================
   CART PAGE – UPDATE QUANTITY
   ============================================ */
function updateCartQty(key, delta) {
  const input = document.getElementById('qty-' + key);
  if (!input) return;
  const stock   = parseInt(input.max || 999);
  let   newQty  = parseInt(input.value || 1) + delta;
  if (newQty < 1)     newQty = 1;
  if (newQty > stock) {
    showToast('Số lượng vượt quá tồn kho (' + stock + ' sp).', 'error');
    newQty = stock;
  }
  input.value = newQty;
  _sendUpdateCart(key, newQty);
}

function onQtyChange(input) {
  const key   = input.dataset.key;
  const stock = parseInt(input.max || 999);
  let   qty   = parseInt(input.value);
  if (isNaN(qty) || qty < 1) { qty = 1; input.value = 1; }
  if (qty > stock) {
    showToast('Số lượng vượt quá tồn kho (' + stock + ' sp).', 'error');
    qty = stock; input.value = stock;
  }
  _sendUpdateCart(key, qty);
}

function _sendUpdateCart(key, qty) {
  const formData = new FormData();
  formData.append('action',   'update');
  formData.append('key',      key);
  formData.append('quantity', qty);

  fetch(_getCartActionUrl(), { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Cập nhật thành tiền hàng
        const subtotalEl = document.getElementById('subtotal-' + key);
        if (subtotalEl) subtotalEl.textContent = data.subtotal;
        // Cập nhật tổng
        const totalEls = document.querySelectorAll('#summarySubtotal, #summaryTotal');
        totalEls.forEach(el => { el.textContent = data.grand_total; });
        updateCartBadge(data.cart_count);
      } else {
        showToast(data.message, 'error');
      }
    })
    .catch(() => showToast('Lỗi kết nối.', 'error'));
}

/* ============================================
   CART PAGE – REMOVE ITEM
   ============================================ */
function removeCartItem(key) {
  if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) return;

  const formData = new FormData();
  formData.append('action', 'remove');
  formData.append('key',    key);

  fetch(_getCartActionUrl(), { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Xóa hàng khỏi DOM
        const row = document.getElementById('row-' + key);
        if (row) {
          row.style.opacity    = '0';
          row.style.transition = 'opacity .3s';
          setTimeout(() => {
            row.remove();
            // Nếu giỏ trống, reload để hiển thị trang trống
            if (data.cart_empty) location.reload();
          }, 300);
        }
        // Cập nhật tổng
        const totalEls = document.querySelectorAll('#summarySubtotal, #summaryTotal');
        totalEls.forEach(el => { el.textContent = data.grand_total; });
        updateCartBadge(data.cart_count);
        showToast('Đã xóa sản phẩm khỏi giỏ hàng.', 'info');
      } else {
        showToast(data.message, 'error');
      }
    })
    .catch(() => showToast('Lỗi kết nối.', 'error'));
}

/* ============================================
   HELPER: xác định URL cart_action.php
   ============================================ */
function _getCartActionUrl() {
  const path = window.location.pathname;
  if (path.includes('/pages/')) {
    return 'cart_action.php';
  }
  return 'pages/cart_action.php';
}

/* ============================================
   HELPER: format số tiền
   ============================================ */
function _fmt(num) {
  return parseInt(num).toLocaleString('vi-VN');
}

/* ============================================
   HELPER: set hidden input
   ============================================ */
function _setHidden(id, value) {
  const el = document.getElementById(id);
  if (el) el.value = value;
}
