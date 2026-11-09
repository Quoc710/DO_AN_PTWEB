/**
 * Aura Fabrics - Cart page controller
 * Nguồn dữ liệu: localStorage['aura_lookbook_cart'] (do Lookbook ghi vào).
 * Khi bấm MUA HÀNG: lưu các dòng đang chọn vào aura_checkout_cart rồi sang checkout.
 */
(function () {
  'use strict';

  const CART_KEY = 'aura_lookbook_cart';
  const CHECKOUT_KEY = 'aura_checkout_cart';
  const VOUCHER_KEY = 'aura_voucher';
  const FREESHIP_METERS = 15;
  const PACK_SAVE = 30000;
  const DEFAULT_THRESHOLD = 15;
  const DEFAULT_DISCOUNT = 0.10;
  const CHECKOUT_URL = '/index.php?module=checkout';
  const DEMO_MODE = new URLSearchParams(window.location.search).get('demo') === '1';

  let hydrated = false;
  let appliedVoucher = null;

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

  const DEMO_ITEMS = [
    {
      maVai: '#LN-55', tenSP: 'Vải Linen Tưng Premium - Trắng Kem', soMet: 2.8, donGia: 180000,
      dinhLuong: 'Cắt liền nguyên kiện 2.8m', mauSac: 'Trắng Kem', chatLieuSize: 'Khổ 1.45m',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCIkY3Q3wx26hWt7Y0Cm37Afzurc2DWIKq9tXPEKT8OfcUvGltWL2cE2o-7uzsnaHxeuAHLaJTN9KR0eOVo7PteiZPatI_019diw_zG7BNfMHJ9puf3EPMl5o-tS8ucfjByYEIyN74D3X6DLppRrTU-9VIPjbzc-FzWJZe6PKl7f9eroEQ9TF9_9giTZUdwlKYgPVpv4jsxrqYco9uxBhL9eVcsVm24Ia9vhezq6G94oYCtVh1rRevz',
      refCode: '', wholesaleThreshold: 15, wholesaleDiscount: 0.10, checked: true
    },
    {
      maVai: '#SATIN-01', tenSP: 'Vải Lụa Cát Satin Tơ Tằm - Đen Tuyền', soMet: 16.5, donGia: 220000,
      dinhLuong: 'Đóng gói: Cây cuộn tròn nguyên khổ', mauSac: 'Đen Tuyền', chatLieuSize: 'Khổ 1.50m',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuA9VaV5MJM8btl5hi8GAxaOcq2ocyXaChZpSmcFVLqmJguaMq01FGaVpCzy0XudyT1_gnq8D0a9A85jDQ17CoQRra2ogpi6r6Igvt5vMKOPhYdNP5EN6fUypdqsEh16Xs2x2iLboPbDmpNZzuAVgnmezAcmV4JjhhjO3Lm-VuWudUQUj4Ntf5-lo4fwhJHUUmNNnE-fKUS99k9uuStXkK-kezqpOhO6i5QITApH1koYqpl0VucUqfGP',
      refCode: '', wholesaleThreshold: 15, wholesaleDiscount: 0.10, checked: true
    },
    {
      maVai: '#CT-KR-09', tenSP: 'Vải Cotton Hàn Họa Tiết Floral - Pastel Mint', soMet: 5.0, donGia: 135000,
      dinhLuong: 'Khổ chuẩn may đầm', mauSac: 'Xanh Bạc Hà', chatLieuSize: 'Khổ 1.60m',
      img: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAfk9nOjQ2GHH2LxjczXEBBL1Y0JJ8DnmhZj8B1LDiLPnBZ4SMxtS2QXFEaBu6zrYxEhWCbvjW5aR9i8nBkMHITtLwy2LPVV5lLiz3ACK1A53VhaGNwhhmI8L4PwFC8q9T4MgFInmkiy72iawFMVf19LsFmURCgjaNRNjltDk-x7DCTLq9f7kukXbL8ojqIKZYFhFjHjW43nveOlLI8tj76fzRfis6Ix9WPx1hY2n8QSJeiL8lHSS6j',
      refCode: '', wholesaleThreshold: 15, wholesaleDiscount: 0.1259259259, checked: false
    }
  ];

  function readCart() {
    try {
      const data = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
      return Array.isArray(data) ? data : [];
    } catch (e) {
      return [];
    }
  }

  function writeCart(items) {
    try { localStorage.setItem(CART_KEY, JSON.stringify(items)); } catch (e) {}
  }

  function readVoucher() {
    try {
      const data = JSON.parse(localStorage.getItem(VOUCHER_KEY) || 'null');
      return data && data.maCode ? data : null;
    } catch (e) {
      return null;
    }
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
    if (!appliedVoucher || subtotal <= 0) return 0;
    const value = num(appliedVoucher.giaTri, 0);
    const raw = appliedVoucher.loaiGiam === '%' ? subtotal * (value / 100) : value;
    return Math.max(0, Math.min(Math.round(raw), Math.round(subtotal)));
  }

  function currentSubtotal() {
    let subtotal = 0;
    rows().forEach((row) => {
      const checkbox = row.querySelector('.item-checkbox');
      if (checkbox && !checkbox.checked) return;
      const price = num(row.dataset.basePrice, 0);
      const threshold = num(row.dataset.wholesaleThreshold, DEFAULT_THRESHOLD);
      const discount = num(row.dataset.wholesaleDiscount, DEFAULT_DISCOUNT);
      const input = row.querySelector('.qty-input');
      const meters = Math.max(0.5, round1(num(input && input.value, 0.5)));
      const wholesale = meters >= threshold && discount > 0;
      const unit = wholesale ? price * (1 - discount) : price;
      subtotal += unit * meters;
    });
    return Math.round(subtotal);
  }

  function normalize(raw, index) {
    const meters = Math.max(0.5, round1(num(raw.soMet, 2.4)));
    return {
      id: String(raw.maVai || raw.id || ('lookbook-' + index)),
      code: String(raw.maVai || ''),
      name: String(raw.tenSP || 'Vải Aura Fabrics'),
      img: safeImg(raw.img),
      color: String(raw.mauSac || 'Tiêu chuẩn'),
      width: String(raw.chatLieuSize || raw.khoVai || raw.dinhLuong || 'Khổ tiêu chuẩn'),
      packing: String(raw.dinhLuong || ('Cắt liền ' + meters + 'm')),
      ref: String(raw.refCode || ''),
      price: num(raw.donGia, 0),
      meters: meters,
      threshold: num(raw.wholesaleThreshold, DEFAULT_THRESHOLD),
      discount: num(raw.wholesaleDiscount, DEFAULT_DISCOUNT),
      checked: raw.checked !== false
    };
  }

  function unitPrice(item) {
    const wholesale = item.meters >= item.threshold && item.discount > 0;
    return wholesale ? item.price * (1 - item.discount) : item.price;
  }

  function rowHtml(item) {
    const wholesale = item.meters >= item.threshold && item.discount > 0;
    const unit = unitPrice(item);
    const line = unit * item.meters;
    const save = (item.price - unit) * item.meters;

    return `
      <div class="cart-item px-space-md py-space-md flex flex-col gap-space-sm bg-surface-container-lowest hover:bg-surface-container-low/40 transition-colors"
           data-id="${esc(item.id)}" data-code="${esc(item.code)}" data-name="${esc(item.name)}"
           data-ref="${esc(item.ref)}" data-color="${esc(item.color)}" data-width="${esc(item.width)}"
           data-dinh-luong="${esc(item.packing)}" data-base-price="${item.price}"
           data-wholesale-threshold="${item.threshold}" data-wholesale-discount="${item.discount}">
        <div class="flex items-start gap-space-sm">
          <div class="w-8 pt-6 flex items-center justify-center shrink-0">
            <input ${item.checked ? 'checked' : ''} class="item-checkbox w-4 h-4 rounded cursor-pointer accent-primary" type="checkbox">
          </div>
          <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-lg overflow-hidden bg-surface-container shrink-0">
            ${item.img
              ? `<img class="w-full h-full object-cover" src="${esc(item.img)}" alt="${esc(item.name)}">`
              : `<div class="w-full h-full flex items-center justify-center text-outline"><span class="material-symbols-outlined">texture</span></div>`}
            <span class="absolute bottom-0 inset-x-0 bg-primary/75 text-on-primary font-label-sm text-[9px] text-center py-0.5 truncate px-1">
              ${esc(item.code || 'AURA')} · ${esc(item.width)}
            </span>
          </div>
          <div class="flex-1 min-w-0 flex flex-col justify-between self-stretch pr-2">
            <div>
              <div class="flex flex-wrap items-center gap-1.5 mb-1">
                ${wholesale
                  ? `<span class="px-1.5 py-0.5 rounded bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-[9px] uppercase font-bold">Giảm ${Math.round(item.discount * 100)}% sỉ &gt;${item.threshold}m</span>`
                  : `<span class="px-1.5 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed font-label-sm text-[9px] uppercase font-bold">Freeship Xtra</span>`}
                <h3 class="font-headline-sm text-headline-sm text-on-surface truncate">${esc(item.name)}</h3>
              </div>
              <div class="flex flex-wrap items-center gap-2 mt-1">
                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-container font-label-md text-label-md text-on-surface-variant">
                  <span>Màu: ${esc(item.color)} | Khổ: ${esc(item.width)}</span>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-2 mt-2">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-surface-container-high font-label-sm text-label-sm text-on-surface-variant">
                <span class="material-symbols-outlined text-[13px] text-secondary">inventory_2</span>
                <span>Quy cách: ${esc(item.packing)}</span>
              </span>
            </div>
          </div>
          <div class="w-24 text-right pt-2 shrink-0 hidden sm:block">
            <p class="item-base-price-old font-body-sm text-body-sm text-outline line-through ${wholesale ? '' : 'hidden'}">${vnd(item.price)}</p>
            <p class="item-unit-price font-label-lg text-label-lg ${wholesale ? 'text-secondary' : 'text-on-surface'} font-semibold">${vnd(unit)}</p>
            <p class="font-label-sm text-label-sm text-outline">/ mét<span class="item-wholesale-label">${wholesale ? ' (Giá sỉ)' : ''}</span></p>
          </div>
          <div class="w-36 flex flex-col items-center justify-center shrink-0 pt-1">
            <div class="flex items-center bg-surface-container-low rounded-lg p-0.5 shadow-xs">
              <button type="button" class="qty-btn-decrease w-7 h-7 flex items-center justify-center rounded bg-surface-container-lowest text-on-surface hover:bg-surface-container-high transition-colors font-bold" title="Giảm 0.1m">
                <span class="material-symbols-outlined text-[15px]">remove</span>
              </button>
              <div class="relative flex items-center">
                <input class="qty-input w-16 text-center bg-transparent font-label-lg text-label-lg text-on-surface font-bold focus:outline-none"
                       min="0.5" step="0.1" type="number" value="${item.meters}">
                <span class="font-label-sm text-label-sm text-outline absolute right-1 pointer-events-none">m</span>
              </div>
              <button type="button" class="qty-btn-increase w-7 h-7 flex items-center justify-center rounded bg-surface-container-lowest text-on-surface hover:bg-surface-container-high transition-colors font-bold" title="Tăng 0.1m">
                <span class="material-symbols-outlined text-[15px]">add</span>
              </button>
            </div>
            <span class="font-label-sm text-[10px] ${wholesale ? 'text-secondary font-semibold' : 'text-outline'} mt-1">
              ${wholesale ? 'Đã kích hoạt giá sỉ' : 'Cắt chuẩn lẻ 0.1m'}
            </span>
          </div>
          <div class="w-28 text-right pt-2 shrink-0">
            <p class="item-line-total font-headline-sm text-headline-sm text-on-surface font-bold text-primary">${vnd(line)}</p>
            <p class="item-savings-text font-label-sm text-label-sm text-secondary ${save > 0 ? '' : 'hidden'}">Tiết kiệm ${vnd(save)}</p>
          </div>
          <div class="w-12 flex flex-col items-center justify-center pt-2 shrink-0">
            <button type="button" class="btn-delete text-outline hover:text-error transition-colors p-1 rounded hover:bg-error-container" title="Xóa vải này">
              <span class="material-symbols-outlined text-[20px]">delete_outline</span>
            </button>
            <a href="/index.php?module=lookbook&tab=lookbook" class="text-outline hover:text-on-surface transition-colors p-1 rounded" title="Tiếp tục chọn vải">
              <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
            </a>
          </div>
        </div>
      </div>`;
  }

  function rows() {
    return Array.from(document.querySelectorAll('.cart-item'));
  }

  function rowData(row) {
    const qty = row.querySelector('.qty-input');
    const checkbox = row.querySelector('.item-checkbox');
    const img = row.querySelector('img');
    const title = row.querySelector('h3');
    return {
      maVai: row.dataset.code || row.dataset.id || '',
      tenSP: row.dataset.name || (title ? title.textContent.trim() : ''),
      soMet: Math.max(0.5, round1(num(qty && qty.value, 0.5))),
      donGia: num(row.dataset.basePrice, 0),
      img: img ? img.getAttribute('src') : '',
      refCode: row.dataset.ref || '',
      dinhLuong: row.dataset.dinhLuong || '',
      mauSac: row.dataset.color || '',
      chatLieuSize: row.dataset.width || '',
      wholesaleThreshold: num(row.dataset.wholesaleThreshold, DEFAULT_THRESHOLD),
      wholesaleDiscount: num(row.dataset.wholesaleDiscount, DEFAULT_DISCOUNT),
      checked: checkbox ? checkbox.checked : true
    };
  }

  function updateRow(row) {
    const price = num(row.dataset.basePrice, 0);
    const threshold = num(row.dataset.wholesaleThreshold, DEFAULT_THRESHOLD);
    const discount = num(row.dataset.wholesaleDiscount, DEFAULT_DISCOUNT);
    const input = row.querySelector('.qty-input');
    const meters = Math.max(0.5, round1(num(input && input.value, 0.5)));
    if (input) input.value = meters;

    const wholesale = meters >= threshold && discount > 0;
    const unit = wholesale ? price * (1 - discount) : price;
    const line = unit * meters;
    const save = (price - unit) * meters;

    const totalEl = row.querySelector('.item-line-total');
    const saveEl = row.querySelector('.item-savings-text');
    const unitEl = row.querySelector('.item-unit-price');
    const oldEl = row.querySelector('.item-base-price-old');
    const wsLabel = row.querySelector('.item-wholesale-label');
    const checkbox = row.querySelector('.item-checkbox');

    if (totalEl) totalEl.textContent = vnd(line);
    if (saveEl) {
      saveEl.textContent = 'Tiết kiệm ' + vnd(save);
      saveEl.classList.toggle('hidden', save <= 0);
    }
    if (unitEl) unitEl.textContent = vnd(unit);
    if (oldEl) oldEl.classList.toggle('hidden', !wholesale);
    if (wsLabel) wsLabel.textContent = wholesale ? ' (Giá sỉ)' : '';

    return { price, meters, save, checked: checkbox ? checkbox.checked : true };
  }

  function persist() {
    if (!hydrated || DEMO_MODE) return;
    writeCart(rows().map(rowData));
  }

  function calc() {
    const list = rows();
    let count = 0;
    let meters = 0;
    let raw = 0;
    let wholesaleSave = 0;

    list.forEach((row) => {
      const data = updateRow(row);
      if (data.checked) {
        count += 1;
        meters += data.meters;
        raw += data.price * data.meters;
        wholesaleSave += data.save;
      }
    });

    const metersText = round1(meters);
    const subtotal = Math.max(0, raw - wholesaleSave);
    const voucher = count > 0 ? voucherDiscountFor(subtotal) : 0;
    const finalTotal = Math.max(0, subtotal - voucher);
    const totalSave = wholesaleSave + voucher + (metersText >= FREESHIP_METERS && count > 0 ? PACK_SAVE : 0);

    if ($('totalItemsCount')) $('totalItemsCount').textContent = list.length;
    if ($('shippingMeterText')) $('shippingMeterText').textContent = metersText + 'm';
    if ($('freeshipProgressBar')) $('freeshipProgressBar').style.width = Math.min(100, Math.round((metersText / FREESHIP_METERS) * 100)) + '%';
    if ($('summaryRawTotal')) $('summaryRawTotal').textContent = vnd(raw);
    if ($('summaryWholesaleDiscount')) $('summaryWholesaleDiscount').textContent = '-' + vnd(wholesaleSave);
    if ($('summaryVoucherDiscount')) $('summaryVoucherDiscount').textContent = '-' + vnd(voucher);
    if ($('summaryFinalTotal')) $('summaryFinalTotal').textContent = vnd(finalTotal);
    if ($('summaryTotalSavings')) $('summaryTotalSavings').textContent = vnd(totalSave);
    if ($('selectedMetersSummary')) $('selectedMetersSummary').textContent = metersText + 'm · ' + count + ' mặt hàng';
    if ($('checkoutBtnLabel')) $('checkoutBtnLabel').textContent = 'MUA HÀNG (' + count + ' sản phẩm)';
    if ($('btnCheckout')) $('btnCheckout').disabled = count === 0;
    if ($('headerCartCount')) $('headerCartCount').textContent = list.length;

    const all = list.length > 0 && list.every((row) => {
      const cb = row.querySelector('.item-checkbox');
      return cb && cb.checked;
    });
    const some = list.some((row) => {
      const cb = row.querySelector('.item-checkbox');
      return cb && cb.checked;
    });
    [$('selectAllCheckbox'), $('selectShopCheckbox')].forEach((cb) => {
      if (cb) {
        cb.checked = all;
        cb.indeterminate = some && !all;
      }
    });

    persist();
  }

  function checkEmpty() {
    const empty = rows().length === 0;
    if ($('cartEmptyState')) $('cartEmptyState').classList.toggle('hidden', !empty);
    if ($('cartItemsContainer')) $('cartItemsContainer').classList.toggle('hidden', empty);
  }

  function toast(message) {
    let el = document.getElementById('cartToast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'cartToast';
      el.className = 'fixed bottom-5 right-5 z-[60] px-4 py-3 rounded-xl shadow-xl bg-slate-900 text-white text-xs font-bold';
      document.body.appendChild(el);
    }
    el.textContent = message;
    el.style.display = 'block';
    window.clearTimeout(el._timer);
    el._timer = window.setTimeout(() => { el.style.display = 'none'; }, 2600);
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
      const active = appliedVoucher && String(appliedVoucher.maCode || '').toUpperCase() === String(v.maCode || '').toUpperCase();
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
      if (appliedVoucher) {
        badge.textContent = 'Đang dùng: ' + appliedVoucher.maCode;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
        badge.textContent = '';
      }
    }

    const removeBtn = $('btnRemoveVoucher');
    if (removeBtn) removeBtn.classList.toggle('hidden', !appliedVoucher);

    const input = $('voucherInput');
    if (input && appliedVoucher && document.activeElement !== input) {
      input.value = appliedVoucher.maCode;
    }

    renderVoucherList(voucherCache);
  }

  function loadVouchers() {
    const listEl = $('voucherList');
    if (!listEl) return;

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

    const subtotal = currentSubtotal();
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

      appliedVoucher = json.data.voucher;
      saveVoucher(appliedVoucher);
      if (!silent) {
        setVoucherMessage('Đã áp dụng ' + appliedVoucher.maCode + ' · giảm ' + vnd(voucherDiscountFor(subtotal)) + '.', 'success');
      } else {
        setVoucherMessage('', 'info');
      }
      refreshVoucherUI();
      calc();
    } catch (error) {
      appliedVoucher = null;
      saveVoucher(null);
      if (!silent) setVoucherMessage(error.message || 'Mã voucher không hợp lệ.', 'error');
      refreshVoucherUI();
      calc();
    }
  }

  function clearVoucher() {
    appliedVoucher = null;
    saveVoucher(null);
    const input = $('voucherInput');
    if (input) input.value = '';
    setVoucherMessage('Đã bỏ áp dụng voucher.', 'info');
    refreshVoucherUI();
    calc();
  }

  function hydrate() {
    const container = $('cartItemsContainer');
    if (!container) return;

    const stored = readCart();
    let items = [];

    if (stored.length) {
      items = stored.map(normalize);
      hydrated = true;
    } else if (DEMO_MODE) {
      items = DEMO_ITEMS.map(normalize);
    }

    if (items.length) {
      container.innerHTML = items.map(rowHtml).join('');
    }
  }

  function bind() {
    const box = $('cartItemsContainer') || document.body;

    box.addEventListener('change', (event) => {
      if (event.target.matches('.item-checkbox')) {
        calc();
      } else if (event.target.matches('.qty-input')) {
        event.target.value = Math.max(0.5, round1(num(event.target.value, 0.5)));
        calc();
      }
    });

    box.addEventListener('click', (event) => {
      const row = event.target.closest('.cart-item');
      if (!row) return;

      if (event.target.closest('.qty-btn-decrease') || event.target.closest('.qty-btn-increase')) {
        const input = row.querySelector('.qty-input');
        const step = num(input && input.step, 0.1) || 0.1;
        const delta = event.target.closest('.qty-btn-increase') ? step : -step;
        input.value = Math.max(0.5, round1(num(input.value, 0.5) + delta));
        calc();
        return;
      }

      if (event.target.closest('.btn-delete')) {
        row.remove();
        calc();
        checkEmpty();
        toast('Đã xóa dòng vải khỏi giỏ hàng.');
      }
    });

    [$('selectAllCheckbox'), $('selectShopCheckbox')].forEach((cb) => {
      if (!cb) return;
      cb.addEventListener('change', (event) => {
        rows().forEach((row) => {
          const itemCb = row.querySelector('.item-checkbox');
          if (itemCb) itemCb.checked = event.target.checked;
        });
        calc();
      });
    });

    if ($('btnDeleteSelected')) {
      $('btnDeleteSelected').addEventListener('click', () => {
        const selected = rows().filter((row) => {
          const cb = row.querySelector('.item-checkbox');
          return cb && cb.checked;
        });
        if (!selected.length) {
          toast('Vui lòng chọn ít nhất 1 dòng để xóa.');
          return;
        }
        selected.forEach((row) => row.remove());
        calc();
        checkEmpty();
        toast('Đã xóa các dòng đã chọn.');
      });
    }

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
        if (appliedVoucher && String(appliedVoucher.maCode || '').toUpperCase() === code.toUpperCase()) {
          clearVoucher();
        } else {
          applyVoucher(code);
        }
      });
    }

    if ($('btnRemoveVoucher')) {
      $('btnRemoveVoucher').addEventListener('click', clearVoucher);
    }

    if ($('btnCheckout')) {
      $('btnCheckout').addEventListener('click', () => {
        const items = rows()
          .filter((row) => {
            const cb = row.querySelector('.item-checkbox');
            return cb && cb.checked;
          })
          .map(rowData);

        if (!items.length) {
          toast('Vui lòng chọn ít nhất 1 sản phẩm trước khi thanh toán.');
          return;
        }

        try {
          localStorage.setItem(CHECKOUT_KEY, JSON.stringify({
            items: items,
            voucherCode: appliedVoucher ? appliedVoucher.maCode : '',
            voucherDiscount: voucherDiscountFor(currentSubtotal()),
            createdAt: Date.now()
          }));
        } catch (e) {
          toast('Trình duyệt không cho lưu giỏ hàng. Vui lòng thử lại.');
          return;
        }

        window.location.href = CHECKOUT_URL;
      });
    }
  }

  function init() {
    appliedVoucher = readVoucher();
    hydrate();
    bind();
    loadVouchers();
    calc();
    checkEmpty();
    refreshVoucherUI();
    if (appliedVoucher) applyVoucher(appliedVoucher.maCode, true);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
