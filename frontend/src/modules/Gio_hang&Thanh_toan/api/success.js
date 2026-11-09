/**
 * Aura Fabrics - Order success page
 * Đọc mã đơn từ window.AURA_SUCCESS_ORDER_CODE (hoặc ?code=), tải chi tiết đơn
 * từ GET /api/gio-hang/don-hang/{code} và hiển thị kết quả thanh toán.
 */
(function () {
  'use strict';

  const params = new URLSearchParams(window.location.search);
  const codeFromUrl = (params.get('code') || '').toUpperCase().trim();
  const code = (window.AURA_SUCCESS_ORDER_CODE || codeFromUrl || '').toUpperCase().trim();

  const state = { order: null, pollTimer: null };

  const $ = (id) => document.getElementById(id);
  const vnd = (n) => new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(Number(n) || 0))) + 'đ';
  const esc = (value) => String(value == null ? '' : value).replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
  const safeImg = (value) => /^(https?:\/\/|data:image\/|\/)/i.test(String(value || '').trim())
    ? String(value).trim()
    : '';

  const STATUS = {
    cho_thanh_toan: { label: 'Chờ thanh toán', cls: 'bg-amber-50 text-amber-700 border-amber-200' },
    dang_xu_ly: { label: 'Đang xử lý', cls: 'bg-blue-50 text-blue-700 border-blue-200' },
    dang_giao: { label: 'Đang giao', cls: 'bg-violet-50 text-violet-700 border-violet-200' },
    da_giao: { label: 'Đã giao thành công', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    da_thanh_toan: { label: 'Đã thanh toán', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    hoan_tat: { label: 'Hoàn tất', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    da_huy: { label: 'Đã hủy', cls: 'bg-rose-50 text-rose-700 border-rose-200' }
  };

  function statusInfo(status) {
    return STATUS[status] || { label: status || 'Không xác định', cls: 'bg-slate-100 text-slate-700 border-slate-200' };
  }

  function formatDate(value) {
    if (!value) return '—';
    const date = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleString('vi-VN', {
      day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
  }

  function showError(message) {
    if ($('successLoading')) $('successLoading').classList.add('hidden');
    if ($('successContent')) $('successContent').classList.add('hidden');
    if ($('successError')) $('successError').classList.remove('hidden');
    if ($('successErrorText')) $('successErrorText').textContent = message;
  }

  function renderItems(items) {
    const box = $('successItems');
    if (!box) return;

    if (!Array.isArray(items) || !items.length) {
      box.innerHTML = '<div class="p-space-md text-center text-outline font-body-sm text-body-sm">Đơn hàng chưa có dòng sản phẩm.</div>';
      return;
    }

    box.innerHTML = items.map((item) => {
      const img = safeImg(item.HinhAnh);
      const meta = [item.MaVai, item.MauSac, item.KhoVai].filter(Boolean).join(' · ');
      const meters = Number(item.SoMet) || 0;
      const line = Number(item.ThanhTien) || 0;

      return `
        <div class="px-space-md py-space-sm flex items-center gap-space-sm">
          <div class="w-14 h-16 rounded-lg overflow-hidden bg-surface-container flex-shrink-0 border border-surface-container">
            ${img
              ? `<img class="w-full h-full object-cover" src="${esc(img)}" alt="${esc(item.TenSP)}">`
              : `<div class="w-full h-full flex items-center justify-center text-outline"><span class="material-symbols-outlined">texture</span></div>`}
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-headline-sm text-headline-sm text-on-surface truncate">${esc(item.TenSP || 'Vải Aura Fabrics')}</div>
            <div class="font-body-sm text-body-sm text-outline truncate">${esc(meta || 'Vải Aura Fabrics')}</div>
            ${item.DinhLuong ? `<div class="font-label-sm text-label-sm text-outline truncate">${esc(item.DinhLuong)}</div>` : ''}
          </div>
          <div class="text-right flex-shrink-0">
            <div class="font-label-lg text-label-lg text-on-surface font-semibold">${meters} m</div>
            <div class="font-body-sm text-body-sm text-on-surface-variant">${vnd(item.DonGia)}/m</div>
            <div class="font-label-lg text-label-lg text-primary font-bold mt-0.5">${vnd(line)}</div>
          </div>
        </div>`;
    }).join('');
  }

  function renderOrder(order, bank) {
    state.order = order;

    if ($('successLoading')) $('successLoading').classList.add('hidden');
    if ($('successError')) $('successError').classList.add('hidden');
    if ($('successContent')) $('successContent').classList.remove('hidden');

    const status = statusInfo(order.TrangThai);
    if ($('successStatus')) {
      $('successStatus').textContent = status.label;
      $('successStatus').className = 'px-space-sm py-1 rounded-full font-label-sm text-label-sm border ' + status.cls;
    }

    if ($('successOrderCode')) $('successOrderCode').textContent = order.MaDon || '—';
    if ($('successCustomerName')) $('successCustomerName').textContent = order.TenKH || '—';
    if ($('successCustomerPhone')) $('successCustomerPhone').textContent = order.SDT || '—';
    if ($('successCustomerAddress')) $('successCustomerAddress').textContent = order.DiaChi || '—';
    if ($('successCreatedAt')) $('successCreatedAt').textContent = formatDate(order.NgayTao);
    if ($('successNote')) $('successNote').textContent = order.GhiChu || '—';
    if ($('successMethod')) {
      $('successMethod').textContent = String(order.PhuongThucThanhToan || '').toLowerCase() === 'cod'
        ? 'Thanh toán khi nhận hàng (COD)'
        : 'Chuyển khoản VietQR';
    }
    if ($('successShippingMethod')) {
      const shippingNames = { economy: 'Tiết kiệm', standard: 'Tiêu chuẩn', express: 'Hỏa tốc' };
      $('successShippingMethod').textContent = shippingNames[order.MaVanChuyen] || 'Tiêu chuẩn';
    }

    const raw = Number(order.TongTienHang) || 0;
    const discount = Number(order.ChietKhau) || 0;
    const voucher = Number(order.Voucher) || 0;
    const shipping = Number(order.PhiVanChuyen) || 0;

    if ($('successRawTotal')) $('successRawTotal').textContent = vnd(raw);
    if ($('successWholesaleDiscount')) $('successWholesaleDiscount').textContent = '-' + vnd(discount);
    if ($('successVoucherDiscount')) $('successVoucherDiscount').textContent = '-' + vnd(voucher);
    if ($('successShipping')) $('successShipping').textContent = shipping > 0 ? vnd(shipping) : 'Miễn phí';
    if ($('successFinalTotal')) $('successFinalTotal').textContent = vnd(order.TongThanhToan);

    renderItems(order.items);

    const paid = ['da_thanh_toan', 'dang_giao', 'da_giao', 'hoan_tat'].includes(order.TrangThai);
    const paymentBox = $('successPaymentBox');
    if (paymentBox) paymentBox.classList.toggle('hidden', order.TrangThai !== 'cho_thanh_toan');

    if (bank) {
      if ($('successBankName')) $('successBankName').textContent = bank.bankName || bank.bankId || '—';
      if ($('successBankAccount')) $('successBankAccount').textContent = bank.accountNo || '—';
      if ($('successBankAccountName')) $('successBankAccountName').textContent = bank.accountName || '—';
    }
    if ($('successTransferContent')) $('successTransferContent').textContent = order.MaDon || '—';

    if ($('successPaymentHint')) {
      $('successPaymentHint').textContent = order.TrangThai === 'da_huy'
        ? 'Đơn hàng đã được hủy trong thời gian giao.'
        : paid
        ? 'Đơn hàng đã được thanh toán. Aura Fabrics sẽ liên hệ để xác nhận thời gian giao vải.'
        : order.TrangThai === 'dang_xu_ly'
          ? 'Đơn hàng đang được xử lý. Bạn có thể thanh toán khi nhận hàng (COD) hoặc chuyển khoản theo thông tin bên dưới.'
          : 'Đơn hàng đang chờ thanh toán. Vui lòng chuyển khoản đúng nội dung bên dưới để hệ thống xác nhận tự động.';
    }

    // Poll trạng thái khi đơn còn chờ thanh toán.
    if (!paid && order.TrangThai !== 'da_huy') {
      startPolling();
    } else {
      stopPolling();
    }
  }

  async function loadOrder() {
    if (!code) {
      showError('Không tìm thấy mã đơn hàng. Vui lòng kiểm tra lại đường dẫn hoặc quay về giỏ hàng.');
      return;
    }

    try {
      const response = await fetch('/api/gio-hang/don-hang/' + encodeURIComponent(code), {
        headers: { 'Accept': 'application/json' }
      });
      const json = await response.json();
      if (!response.ok || json.status !== 'success' || !json.data || !json.data.order) {
        throw new Error(json.message || 'Không tìm thấy đơn hàng ' + code + '.');
      }
      renderOrder(json.data.order, json.data.bank || null);
    } catch (error) {
      showError(error.message || 'Không tải được thông tin đơn hàng.');
    }
  }

  function startPolling() {
    stopPolling();
    state.pollTimer = window.setInterval(loadOrder, 8000);
  }

  function stopPolling() {
    if (state.pollTimer) {
      window.clearInterval(state.pollTimer);
      state.pollTimer = null;
    }
  }

  function bind() {
    document.querySelectorAll('[data-copy]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.getAttribute('data-copy'));
        if (!target) return;
        const text = target.textContent.trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).catch(() => {});
        }
        btn.textContent = 'Đã chép';
        window.setTimeout(() => { btn.textContent = 'Chép'; }, 1600);
      });
    });

    if ($('successRefreshBtn')) {
      $('successRefreshBtn').addEventListener('click', () => {
        if ($('successLoading')) $('successLoading').classList.remove('hidden');
        loadOrder();
      });
    }
  }

  function init() {
    bind();
    loadOrder();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
