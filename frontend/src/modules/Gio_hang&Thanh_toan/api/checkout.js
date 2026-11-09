/**
 * Aura Fabrics - Checkout controller
 * - Đọc các dòng đã chọn từ localStorage['aura_checkout_cart']
 * - Tạo đơn qua POST /api/gio-hang/dat-hang
 * - Thanh toán VietQR ngân hàng: hiển thị QR, polling trạng thái, xác nhận chuyển khoản
 */
(function () {
  'use strict';

  const CART_KEY = 'aura_lookbook_cart';
  const CHECKOUT_KEY = 'aura_checkout_cart';
  const VOUCHER_KEY = 'aura_voucher';
  const DEFAULT_THRESHOLD = 15;
  const DEFAULT_DISCOUNT = 0.10;
  const SUCCESS_URL = '/index.php?module=success&code=';
  const SHIPPING = {
    economy: { label: 'Tiết kiệm', fee: 15000 },
    standard: { label: 'Tiêu chuẩn', fee: 30000 },
    express: { label: 'Hỏa tốc', fee: 50000 }
  };

  const state = {
    items: [],
    method: 'vietqr',
    shippingMethod: 'standard',
    voucher: null,
    order: null,
    pollTimer: null,
    countdownTimer: null,
    secondsLeft: 15 * 60,
    polling: false
  };

  const $ = (id) => document.getElementById(id);
  const num = (value, fallback) => {
    const n = parseFloat(String(value == null ? '' : value).replace(',', '.'));
    return Number.isFinite(n) ? n : fallback;
  };
  const round1 = (n) => Math.round(n * 10) / 10;
  const vnd = (n) => new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(n))) + 'đ';
  const esc = (value) => String(value == null ? '' : value).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
  const safeImg = (value) => /^(https?:\/\/|data:image\/|\/)/i.test(String(value || '').trim())
    ? String(value).trim()
    : '';
  const readJson = (key, fallback) => {
    try {
      const data = JSON.parse(localStorage.getItem(key) || 'null');
      return data == null ? fallback : data;
    } catch (e) {
      return fallback;
    }
  };

  function readVoucher() {
    const data = readJson(VOUCHER_KEY, null);
    return data && data.maCode ? data : null;
  }

  function saveVoucher(voucher) {
    try {
      if (voucher && voucher.maCode) {
        localStorage.setItem(VOUCHER_KEY, JSON.stringify(voucher));
      } else {
        localStorage.removeItem(VOUCHER_KEY);
      }
    } catch (e) {}
  }

  function voucherDiscountFor(subtotal) {
    if (!state.voucher || subtotal <= 0) return 0;
    const value = num(state.voucher.giaTri, 0);
    const raw = state.voucher.loaiGiam === '%' ? subtotal * (value / 100) : value;
    return Math.max(0, Math.min(Math.round(raw), Math.round(subtotal)));
  }

  function normalize(raw, index) {
    return {
      maVai: String(raw.maVai || raw.id || ('item-' + index)),
      tenSP: String(raw.tenSP || raw.name || 'Vải Aura Fabrics'),
      soMet: Math.max(0.5, round1(num(raw.soMet, 2.4))),
      donGia: num(raw.donGia, 0),
      img: safeImg(raw.img || raw.hinhAnh),
      dinhLuong: String(raw.dinhLuong || ''),
      mauSac: String(raw.mauSac || raw.color || ''),
      chatLieuSize: String(raw.chatLieuSize || raw.khoVai || raw.width || ''),
      refCode: String(raw.refCode || ''),
      wholesaleThreshold: num(raw.wholesaleThreshold, DEFAULT_THRESHOLD),
      wholesaleDiscount: num(raw.wholesaleDiscount, DEFAULT_DISCOUNT)
    };
  }

  function loadItems() {
    const checkout = readJson(CHECKOUT_KEY, null);
    let source = checkout && Array.isArray(checkout.items) ? checkout.items : null;

    if (!source) {
      const cart = readJson(CART_KEY, []);
      source = Array.isArray(cart) ? cart.filter((item) => item.checked !== false) : [];
    }

    state.items = source.map(normalize).filter((item) => item.donGia > 0);
    computeTotals();
  }

  function lineData(item) {
    const wholesale = item.soMet >= item.wholesaleThreshold && item.wholesaleDiscount > 0;
    const unit = wholesale ? item.donGia * (1 - item.wholesaleDiscount) : item.donGia;
    return {
      unit: unit,
      line: unit * item.soMet,
      save: (item.donGia - unit) * item.soMet,
      wholesale: wholesale
    };
  }

  function computeTotals() {
    let raw = 0;
    let discount = 0;
    let meters = 0;

    state.items.forEach((item) => {
      const data = lineData(item);
      raw += item.donGia * item.soMet;
      discount += data.save;
      meters += item.soMet;
    });

    const subtotal = Math.max(0, raw - discount);
    const voucher = state.items.length ? voucherDiscountFor(subtotal) : 0;
    const shipping = SHIPPING[state.shippingMethod] || SHIPPING.standard;
    const finalTotal = Math.max(0, subtotal - voucher + shipping.fee);

    if ($('ckRawTotal')) $('ckRawTotal').textContent = vnd(raw);
    if ($('ckSummaryRaw')) $('ckSummaryRaw').textContent = vnd(raw);
    if ($('ckWholesaleDiscount')) $('ckWholesaleDiscount').textContent = '-' + vnd(discount);
    if ($('ckVoucherDiscount')) $('ckVoucherDiscount').textContent = '-' + vnd(voucher);
    if ($('ckShippingFee')) $('ckShippingFee').textContent = vnd(shipping.fee);
    if ($('ckShippingLabel')) $('ckShippingLabel').textContent = shipping.label;
    if ($('ckFinalTotal')) $('ckFinalTotal').textContent = vnd(finalTotal);
    if ($('checkoutItemCount')) $('checkoutItemCount').textContent = state.items.length;

    const badge = $('ckVoucherBadge');
    if (badge) {
      if (state.voucher) {
        badge.textContent = state.voucher.maCode + ' · -' + vnd(voucher);
        badge.className = 'text-emerald-700 text-[12px] border border-emerald-200 bg-emerald-50 px-2 py-0.5 rounded font-semibold';
      } else {
        badge.textContent = 'Chưa áp dụng';
        badge.className = 'text-slate-500 text-[12px] border border-surface-container-high bg-slate-50 px-2 py-0.5 rounded font-medium';
      }
    }

    if ($('checkoutEmpty')) $('checkoutEmpty').classList.toggle('hidden', state.items.length > 0);
    if ($('checkoutItems')) $('checkoutItems').classList.toggle('hidden', state.items.length === 0);
    if ($('btnPlaceOrder')) $('btnPlaceOrder').disabled = state.items.length === 0;

    state.totals = { raw, discount, voucher, shipping: shipping.fee, meters, finalTotal };
  }

  function rowHtml(item) {
    const data = lineData(item);
    const meta = [item.mauSac, item.chatLieuSize].filter(Boolean).join(' · ');

    return `
      <div class="px-6 py-4 border-b border-surface-container flex items-center justify-between gap-3 text-[14px]">
        <div class="w-1/2 flex items-center gap-3 min-w-0">
          <div class="w-14 h-14 rounded border border-surface-container overflow-hidden flex-shrink-0 bg-surface-container">
            ${item.img
              ? `<img alt="${esc(item.tenSP)}" class="w-full h-full object-cover" src="${esc(item.img)}">`
              : `<div class="w-full h-full flex items-center justify-center text-outline"><span class="material-symbols-outlined">texture</span></div>`}
          </div>
          <div class="flex flex-col min-w-0">
            <span class="text-on-surface font-medium leading-snug line-clamp-1">${esc(item.tenSP)}</span>
            <span class="text-[12px] text-outline mt-0.5 truncate">${esc(item.maVai)}${meta ? ' · ' + esc(meta) : ''}</span>
            ${item.dinhLuong ? `<span class="text-[11px] text-outline mt-0.5 truncate">${esc(item.dinhLuong)}</span>` : ''}
          </div>
        </div>
        <div class="w-1/6 text-center text-outline text-[13px] hidden sm:block">
          ${data.wholesale ? `<span class="line-through block text-[11px]">${vnd(item.donGia)}</span>` : ''}
          <span>${vnd(data.unit)}</span>
        </div>
        <div class="w-1/6 text-center text-on-surface font-medium whitespace-nowrap">${item.soMet} m</div>
        <div class="w-1/6 text-right text-on-surface font-medium whitespace-nowrap">${vnd(data.line)}</div>
      </div>`;
  }

  function renderItems() {
    if ($('checkoutItems')) {
      $('checkoutItems').innerHTML = state.items.map(rowHtml).join('');
    }
    computeTotals();
  }

  function fillCustomer() {
    const user = readJson('aura_user', null) || {};
    const demo = new URLSearchParams(window.location.search).get('demo') === '1';

    const name = user.TenKH || user.TenNV || (demo ? 'Võ Minh Quân' : '');
    const phone = user.SDT || user.SoDienThoai || (demo ? '0988242868' : '');
    const address = user.DiaChi || (demo ? '128 Đường D3, Phường Tân Hưng, Quận 7, TP. Hồ Chí Minh' : '');
    const email = user.Email || (demo ? 'minhquan@aura.vn' : '');

    if ($('ckName') && !$('ckName').value) $('ckName').value = name;
    if ($('ckPhone') && !$('ckPhone').value) $('ckPhone').value = phone;
    if ($('ckAddress') && !$('ckAddress').value) $('ckAddress').value = address;
    if ($('ckEmail') && !$('ckEmail').value) $('ckEmail').value = email;
  }

  function selectedMethod() {
    const radio = document.querySelector('input[name="paymentMethod"]:checked');
    return radio ? radio.value : 'vietqr';
  }

  function syncPaymentOptions() {
    document.querySelectorAll('.payment-option').forEach((label) => {
      const radio = label.querySelector('input[name="paymentMethod"]');
      const active = radio && radio.checked;
      label.classList.toggle('border-slate-900', active);
      label.classList.toggle('bg-slate-100', active);
      label.classList.toggle('border-surface-container-high', !active);
    });
  }

  function syncShippingOptions() {
    document.querySelectorAll('.shipping-option').forEach((label) => {
      const radio = label.querySelector('input[name="shippingMethod"]');
      const active = radio && radio.checked;
      const express = active && radio.value === 'express';
      label.classList.toggle('border-emerald-700', active && !express);
      label.classList.toggle('bg-emerald-50/60', active && !express);
      label.classList.toggle('border-amber-500', express);
      label.classList.toggle('bg-amber-50/60', express);
      label.classList.toggle('border-surface-container-high', !active);
    });
  }

  function toast(message, type) {
    let el = document.getElementById('checkoutToast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'checkoutToast';
      el.className = 'fixed bottom-5 right-5 z-[80] max-w-sm px-4 py-3 rounded-xl shadow-xl text-white text-xs font-bold';
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.style.background = type === 'error' ? '#ba1a1a' : '#0f172a';
    el.style.display = 'block';
    window.clearTimeout(el._timer);
    el._timer = window.setTimeout(() => { el.style.display = 'none'; }, 4000);
  }

  let voucherCache = [];

  function setVoucherMessage(text, type) {
    const el = $('voucherMessage');
    if (!el) return;
    if (!text) {
      el.classList.add('hidden');
      el.textContent = '';
      return;
    }
    el.textContent = text;
    el.className = 'mt-2 text-[12px] font-medium ' + (
      type === 'success' ? 'text-emerald-700' : type === 'error' ? 'text-rose-600' : 'text-slate-500'
    );
  }

  function renderVoucherList(vouchers) {
    const listEl = $('voucherList');
    if (!listEl) return;
    voucherCache = Array.isArray(vouchers) ? vouchers : [];

    if (!voucherCache.length) {
      listEl.innerHTML = '<p class="font-body-sm text-[12px] text-outline">Hiện chưa có voucher nào đang hiệu lực. Hãy quay lại sau nhé.</p>';
      return;
    }

    listEl.innerHTML = voucherCache.map((v) => {
      const active = state.voucher && String(state.voucher.maCode || '').toUpperCase() === String(v.maCode || '').toUpperCase();
      const valueLabel = v.loaiGiam === '%'
        ? 'Giảm ' + v.giaTri + '%'
        : 'Giảm ' + vnd(v.giaTri);
      const expiry = v.hanSuDung ? 'HSD ' + v.hanSuDung : 'Không giới hạn';
      return `
        <div class="flex items-center justify-between gap-2 p-2 rounded-lg border ${active ? 'border-emerald-300 bg-emerald-50/50' : 'border-surface-container'}">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-label-sm text-[10px] tracking-wider uppercase font-bold">${esc(v.maCode)}</span>
              <span class="font-label-md text-label-md text-on-surface truncate">${esc(v.tenChuongTrinh || valueLabel)}</span>
            </div>
            <p class="font-body-sm text-[11px] text-outline mt-0.5 truncate">${esc(valueLabel)} · ${esc(expiry)}${v.moTaDieuKien ? ' · ' + esc(v.moTaDieuKien) : ''}</p>
          </div>
          <button type="button" data-voucher-code="${esc(v.maCode)}"
                  class="shrink-0 px-2.5 py-1 rounded-lg text-[11px] font-bold ${active ? 'bg-emerald-600 text-white' : 'bg-surface-container text-on-surface hover:bg-surface-container-high'}">
            ${active ? 'Đang dùng' : 'Dùng'}
          </button>
        </div>`;
    }).join('');
  }

  function refreshVoucherUI() {
    const badge = $('voucherAppliedBadge');
    if (badge) {
      if (state.voucher) {
        badge.textContent = 'Đang dùng: ' + state.voucher.maCode;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
        badge.textContent = '';
      }
    }

    const removeBtn = $('btnRemoveVoucher');
    if (removeBtn) removeBtn.classList.toggle('hidden', !state.voucher);

    const input = $('voucherInput');
    if (input && state.voucher && document.activeElement !== input) {
      input.value = state.voucher.maCode;
    }

    renderVoucherList(voucherCache);
  }

  function loadVouchers() {
    if (!$('voucherList')) return;

    fetch('/api/gio-hang/voucher', { headers: { 'Accept': 'application/json' } })
      .then((response) => response.json())
      .then((json) => {
        const vouchers = json && json.status === 'success' && json.data ? (json.data.vouchers || []) : [];
        renderVoucherList(vouchers);
      })
      .catch(() => renderVoucherList([]));
  }

  async function applyVoucher(code, silent) {
    code = String(code || '').trim().toUpperCase();
    if (!code) {
      if (!silent) setVoucherMessage('Vui lòng nhập mã voucher.', 'error');
      return;
    }

    const subtotal = state.totals ? Math.max(0, state.totals.raw - state.totals.discount) : 0;
    if (subtotal <= 0) {
      if (!silent) setVoucherMessage('Chưa có sản phẩm để áp dụng voucher.', 'error');
      return;
    }

    try {
      const response = await fetch('/api/gio-hang/voucher/kiem-tra', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ code: code, subtotal: subtotal })
      });
      const json = await response.json();
      if (!response.ok || json.status !== 'success' || !json.data) {
        throw new Error(json.message || 'Mã voucher không hợp lệ.');
      }

      state.voucher = json.data.voucher;
      saveVoucher(state.voucher);
      if (!silent) {
        setVoucherMessage('Đã áp dụng ' + state.voucher.maCode + ' · giảm ' + vnd(voucherDiscountFor(subtotal)) + '.', 'success');
      } else {
        setVoucherMessage('', 'info');
      }
      refreshVoucherUI();
      computeTotals();
    } catch (error) {
      state.voucher = null;
      saveVoucher(null);
      if (!silent) setVoucherMessage(error.message || 'Mã voucher không hợp lệ.', 'error');
      refreshVoucherUI();
      computeTotals();
    }
  }

  function clearVoucher() {
    state.voucher = null;
    saveVoucher(null);
    const input = $('voucherInput');
    if (input) input.value = '';
    setVoucherMessage('Đã bỏ áp dụng voucher.', 'info');
    refreshVoucherUI();
    computeTotals();
  }

  function setBusy(busy, label) {
    const btn = $('btnPlaceOrder');
    const labelEl = $('btnPlaceOrderLabel');
    if (btn) btn.disabled = busy || state.items.length === 0;
    if (labelEl) labelEl.textContent = busy ? (label || 'ĐANG XỬ LÝ...') : 'ĐẶT HÀNG';
  }

  async function placeOrder() {
    const customer = {
      tenKH: ($('ckName') || {}).value || '',
      sdt: ($('ckPhone') || {}).value || '',
      diaChi: ($('ckAddress') || {}).value || '',
      email: ($('ckEmail') || {}).value || '',
      ghiChu: ($('ckNote') || {}).value || ''
    };

    if (customer.tenKH.trim().length < 2) return toast('Vui lòng nhập họ tên người nhận.', 'error');
    if (!/^[0-9+().\-\s]{8,20}$/.test(customer.sdt.trim())) return toast('Số điện thoại chưa hợp lệ.', 'error');
    if (customer.diaChi.trim().length < 8) return toast('Vui lòng nhập địa chỉ nhận hàng chi tiết.', 'error');
    if (!state.items.length) return toast('Chưa có sản phẩm nào để đặt hàng.', 'error');

    state.method = selectedMethod();
    setBusy(true, state.method === 'cod' ? 'ĐANG TẠO ĐƠN...' : 'ĐANG TẠO MÃ QR...');

    try {
      const response = await fetch('/api/gio-hang/dat-hang', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          customer: customer,
          method: state.method,
          shippingMethod: state.shippingMethod,
          voucherCode: state.voucher ? state.voucher.maCode : '',
          items: state.items.map((item) => ({
            maVai: item.maVai,
            tenSP: item.tenSP,
            soMet: item.soMet,
            donGia: item.donGia,
            img: item.img,
            dinhLuong: item.dinhLuong,
            mauSac: item.mauSac,
            chatLieuSize: item.chatLieuSize,
            refCode: item.refCode,
            wholesaleThreshold: item.wholesaleThreshold,
            wholesaleDiscount: item.wholesaleDiscount
          }))
        })
      });
      const json = await response.json();
      if (!response.ok || json.status !== 'success' || !json.data) {
        throw new Error(json.message || 'Không tạo được đơn hàng.');
      }

      const data = json.data;
      state.order = data.order;

      if (state.method === 'cod') {
        finishAndRedirect(state.order);
        return;
      }

      showPayment(data.order, data.bank, data.qrUrl);
      startPolling();
    } catch (error) {
      toast(error.message || 'Có lỗi khi tạo đơn hàng.', 'error');
      setBusy(false);
    }
  }

  function showPayment(order, bank, qrUrl) {
    if ($('qrImage')) {
      $('qrImage').src = qrUrl || '';
      $('qrImage').alt = 'VietQR ' + order.MaDon;
    }
    if ($('qrAmount')) $('qrAmount').textContent = vnd(order.TongThanhToan);
    if ($('bankName')) $('bankName').textContent = bank.bankName || bank.bankId || 'Vietcombank';
    if ($('bankAccount')) $('bankAccount').textContent = bank.accountNo || '';
    if ($('bankAccountName')) $('bankAccountName').textContent = bank.accountName || '';
    if ($('transferContent')) $('transferContent').textContent = order.MaDon;
    if ($('paymentOrderCode')) $('paymentOrderCode').textContent = '#' + order.MaDon;

    const panel = $('paymentPanel');
    if (panel) {
      panel.classList.remove('hidden');
      panel.classList.add('flex');
    }
    document.body.style.overflow = 'hidden';
    startCountdown();
  }

  function closePayment() {
    const panel = $('paymentPanel');
    if (panel) {
      panel.classList.add('hidden');
      panel.classList.remove('flex');
    }
    document.body.style.overflow = '';
    setBusy(false);
  }

  function startCountdown() {
    state.secondsLeft = 15 * 60;
    window.clearInterval(state.countdownTimer);
    updateCountdown();
    state.countdownTimer = window.setInterval(() => {
      state.secondsLeft -= 1;
      updateCountdown();
      if (state.secondsLeft <= 0) {
        window.clearInterval(state.countdownTimer);
        if (state.polling) {
          state.polling = false;
          window.clearInterval(state.pollTimer);
        }
        setPaymentStatus('Mã QR đã hết hạn hiển thị. Bạn vẫn có thể chuyển khoản đúng nội dung hoặc bấm "Tôi đã chuyển khoản".', 'warning');
      }
    }, 1000);
  }

  function updateCountdown() {
    const el = $('qrCountdown');
    if (!el) return;
    const seconds = Math.max(0, state.secondsLeft);
    const mm = String(Math.floor(seconds / 60)).padStart(2, '0');
    const ss = String(seconds % 60).padStart(2, '0');
    el.textContent = mm + ':' + ss;
  }

  function setPaymentStatus(message, type) {
    const el = $('paymentStatus');
    if (!el) return;
    el.className = 'rounded-xl text-[12px] px-3 py-2.5 flex items-start gap-2 ' + (
      type === 'success'
        ? 'bg-emerald-50 border border-emerald-200 text-emerald-800'
        : type === 'warning'
          ? 'bg-amber-50 border border-amber-200 text-amber-800'
          : 'bg-slate-50 border border-slate-200 text-slate-700'
    );
    el.innerHTML = '<span class="material-symbols-outlined text-[18px]">'
      + (type === 'success' ? 'check_circle' : type === 'warning' ? 'error' : 'sync')
      + '</span><span>' + esc(message) + '</span>';
  }

  function startPolling() {
    if (!state.order) return;
    state.polling = true;
    window.clearInterval(state.pollTimer);
    state.pollTimer = window.setInterval(checkOrderStatus, 5000);
    checkOrderStatus();
  }

  async function checkOrderStatus() {
    if (!state.order || !state.polling) return;
    try {
      const response = await fetch('/api/gio-hang/don-hang/' + encodeURIComponent(state.order.MaDon), {
        headers: { 'Accept': 'application/json' }
      });
      const json = await response.json();
      if (!response.ok || json.status !== 'success' || !json.data) return;

      const order = json.data.order;
      if (['da_thanh_toan', 'dang_giao', 'da_giao', 'hoan_tat'].includes(order.TrangThai)) {
        state.polling = false;
        window.clearInterval(state.pollTimer);
        window.clearInterval(state.countdownTimer);
        state.order = order;
        setPaymentStatus('Đã nhận được thanh toán cho đơn #' + order.MaDon + '. Đang chuyển sang trang kết quả...', 'success');
        if ($('btnGoSuccess')) $('btnGoSuccess').classList.remove('hidden');
        if ($('btnConfirmTransfer')) $('btnConfirmTransfer').classList.add('hidden');
        window.setTimeout(() => finishAndRedirect(order), 900);
      } else if (order.TrangThai === 'da_huy') {
        state.polling = false;
        window.clearInterval(state.pollTimer);
        setPaymentStatus('Đơn hàng đã bị hủy. Vui lòng liên hệ hotline 0988.242.868.', 'warning');
      }
    } catch (e) {
      // Bỏ qua lỗi mạng tạm thời, lần poll sau sẽ thử lại.
    }
  }

  async function confirmTransfer() {
    if (!state.order) return;
    const btn = $('btnConfirmTransfer');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'ĐANG XÁC NHẬN...';
    }
    try {
      const response = await fetch('/api/gio-hang/don-hang/' + encodeURIComponent(state.order.MaDon) + '/xac-nhan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ key: '' })
      });
      const json = await response.json();
      if (!response.ok || json.status !== 'success') {
        throw new Error(json.message || 'Chưa xác nhận được chuyển khoản.');
      }
      state.order = json.data.order;
      state.polling = false;
      window.clearInterval(state.pollTimer);
      window.clearInterval(state.countdownTimer);
      setPaymentStatus('Đã ghi nhận thanh toán cho đơn #' + state.order.MaDon + '.', 'success');
      if ($('btnGoSuccess')) $('btnGoSuccess').classList.remove('hidden');
      if (btn) btn.classList.add('hidden');
      window.setTimeout(() => finishAndRedirect(state.order), 900);
    } catch (error) {
      toast(error.message || 'Không xác nhận được chuyển khoản.', 'error');
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Tôi đã chuyển khoản';
      }
    }
  }

  function removePurchasedFromCart() {
    const cart = readJson(CART_KEY, []);
    if (!Array.isArray(cart) || !cart.length) return;

    const purchased = state.items.map((item) => ({
      maVai: String(item.maVai || ''),
      soMet: Number(item.soMet)
    }));

    const remaining = cart.filter((item) => !purchased.some((bought) =>
      String(item.maVai || '') === bought.maVai &&
      Math.abs(Number(item.soMet || 0) - bought.soMet) < 0.001
    ));

    try {
      localStorage.setItem(CART_KEY, JSON.stringify(remaining));
      localStorage.removeItem(CHECKOUT_KEY);
      localStorage.removeItem(VOUCHER_KEY);
    } catch (e) {}
  }

  function finishAndRedirect(order) {
    removePurchasedFromCart();
    window.location.href = SUCCESS_URL + encodeURIComponent(order.MaDon);
  }

  function bind() {
    document.querySelectorAll('input[name="shippingMethod"]').forEach((radio) => {
      radio.addEventListener('change', () => {
        state.shippingMethod = radio.value;
        syncShippingOptions();
        computeTotals();
      });
    });

    if ($('btnApplyVoucher')) {
      $('btnApplyVoucher').addEventListener('click', () => {
        applyVoucher($('voucherInput') ? $('voucherInput').value : '');
      });
    }

    if ($('voucherInput')) {
      $('voucherInput').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
          event.preventDefault();
          applyVoucher(event.target.value);
        }
      });
    }

    if ($('voucherList')) {
      $('voucherList').addEventListener('click', (event) => {
        const btn = event.target.closest('[data-voucher-code]');
        if (!btn) return;
        const code = btn.getAttribute('data-voucher-code') || '';
        if (state.voucher && String(state.voucher.maCode || '').toUpperCase() === code.toUpperCase()) {
          clearVoucher();
        } else {
          applyVoucher(code);
        }
      });
    }

    if ($('btnRemoveVoucher')) {
      $('btnRemoveVoucher').addEventListener('click', clearVoucher);
    }

    document.querySelectorAll('input[name="paymentMethod"]').forEach((radio) => {
      radio.addEventListener('change', () => {
        state.method = selectedMethod();
        syncPaymentOptions();
      });
    });

    if ($('btnPlaceOrder')) $('btnPlaceOrder').addEventListener('click', placeOrder);
    if ($('btnConfirmTransfer')) $('btnConfirmTransfer').addEventListener('click', confirmTransfer);
    if ($('btnGoSuccess')) {
      $('btnGoSuccess').addEventListener('click', () => {
        if (state.order) finishAndRedirect(state.order);
      });
    }
    [$('btnClosePayment'), $('btnClosePayment2')].forEach((btn) => {
      if (btn) btn.addEventListener('click', closePayment);
    });

    document.querySelectorAll('[data-copy]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-copy'));
        if (!target) return;
        const text = target.textContent.trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(() => toast('Đã sao chép: ' + text));
        } else {
          toast(text);
        }
      });
    });
  }

  function init() {
    state.voucher = readVoucher();
    const selectedShipping = document.querySelector('input[name="shippingMethod"]:checked');
    state.shippingMethod = selectedShipping && SHIPPING[selectedShipping.value] ? selectedShipping.value : 'standard';
    loadItems();
    renderItems();
    fillCustomer();
    syncPaymentOptions();
    syncShippingOptions();
    bind();
    loadVouchers();
    refreshVoucherUI();
    if (state.voucher) applyVoucher(state.voucher.maCode, true);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
