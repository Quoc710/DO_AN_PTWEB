/**
 * Aura Admin Portal - Module 4 Interactive Scripts
 * Vị trí: frontend/public/assets/js/script.js
 * Bao gồm:
 * 1. Đồng hồ thời gian thực (#live-clock)
 * 2. Điều hướng Sidebar cố định (Active Pill chuyển theo mục được chọn & chỉ cuộn khung phải #main-workspace)
 * 3. Đổ dữ liệu Form CRUD Mẫu Vải khi bấm nút "Chỉnh sửa"
 * 4. Hiệu ứng kết xuất & tải file CSV/Excel chuẩn Odoo (#btn-export-odoo)
 */

function getRouterUrl() {
  return window.AURA_ROUTER_URL || 'index.php';
}

// 1. Đồng hồ thời gian thực (#live-clock)
function updateLiveClock() {
  const clockElement = document.getElementById('live-clock');
  if (clockElement) {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    clockElement.textContent = `${hours}:${minutes}:${seconds}`;
  }
}

setInterval(updateLiveClock, 1000);
updateLiveClock();

// 2. Hàm cập nhật trạng thái Active (thanh màu đen) trên Sidebar Menu
function setActiveSidebarLink(activeLink) {
  if (!activeLink) return;

  const allLinks = document.querySelectorAll('.sidebar-nav-link');
  allLinks.forEach((link) => {
    // Đưa tất cả mục về trạng thái bình thường
    link.classList.remove('bg-slate-900', 'text-white', 'shadow-sm');
    link.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-50');

    // Khôi phục màu icon mặc định
    const icon = link.querySelector('.nav-icon');
    if (icon) {
      const defaultColor = icon.getAttribute('data-default-color') || 'text-slate-400';
      icon.classList.remove('text-white', 'text-slate-200', 'text-emerald-400');
      icon.classList.add(defaultColor);
    }

    // Khôi phục badge đếm số mẫu vải (nếu có)
    const countBadge = link.querySelector('.nav-badge-count');
    if (countBadge) {
      countBadge.classList.remove('bg-white/20', 'text-white');
      countBadge.classList.add('bg-slate-100', 'text-slate-700');
    }
  });

  // Kích hoạt thanh màu đen cố định cho mục vừa chọn
  activeLink.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-50');
  activeLink.classList.add('bg-slate-900', 'text-white', 'shadow-sm');

  const activeIcon = activeLink.querySelector('.nav-icon');
  if (activeIcon) {
    const defaultColor = activeIcon.getAttribute('data-default-color') || 'text-slate-400';
    activeIcon.classList.remove(defaultColor);
    activeIcon.classList.add(defaultColor === 'text-emerald-600' ? 'text-emerald-400' : 'text-slate-200');
  }

  const activeCountBadge = activeLink.querySelector('.nav-badge-count');
  if (activeCountBadge) {
    activeCountBadge.classList.remove('bg-slate-100', 'text-slate-700');
    activeCountBadge.classList.add('bg-white/20', 'text-white');
  }
}

// 3. Hàm cuộn mượt CHỈ RIÊNG khung bên phải (#main-workspace) tới section được chọn
function scrollWorkspaceToSection(targetId, smooth = true) {
  const workspace = document.getElementById('main-workspace');
  if (!workspace) return;

  if (!targetId || targetId === 'tong-quan') {
    workspace.scrollTo({
      top: 0,
      behavior: smooth ? 'smooth' : 'auto'
    });
    return;
  }

  const targetEl = document.getElementById(targetId);
  if (targetEl) {
    const workspaceRect = workspace.getBoundingClientRect();
    const targetRect = targetEl.getBoundingClientRect();
    const offsetTop = targetRect.top - workspaceRect.top + workspace.scrollTop - 16;

    workspace.scrollTo({
      top: Math.max(0, offsetTop),
      behavior: smooth ? 'smooth' : 'auto'
    });
  }
}

// 4. Hàm đổ dữ liệu mẫu vải lên Form (#add-fabric-panel) khi bấm nút "Chỉnh sửa"
function populateEditFabric(fabricData) {
  const panel = document.getElementById('add-fabric-panel');
  const form = document.getElementById('fabric-crud-form');
  if (!panel || !form || !fabricData) {
    return;
  }

  form.action = getRouterUrl() + '?action=updateFabric';

  const idInput = document.getElementById('input-ma-bien-the');
  const skuInput = document.getElementById('input-ma-sku');
  const nameInput = document.getElementById('input-ten-sp');
  const widthInput = document.getElementById('input-kho-vai');
  const gsmInput = document.getElementById('input-dinh-luong-gsm');
  const priceInput = document.getElementById('input-don-gia');
  const stockInput = document.getElementById('input-so-luong-ton');
  const colorNameInput = document.getElementById('input-mau-sac');
  const colorHexInput = document.getElementById('input-ma-mau-hex');
  const macroHiddenInput = document.getElementById('input-hinh-anh-macro');
  const macroLabel = document.getElementById('selected-macro-label');
  const formTitle = document.getElementById('fabric-form-title');
  const submitBtn = document.getElementById('btn-submit-fabric');

  if (idInput) idInput.value = fabricData.MaBienThe || '';
  if (skuInput) skuInput.value = fabricData.MaSKU || '';
  if (nameInput) nameInput.value = fabricData.TenSP || '';
  if (widthInput) widthInput.value = String(fabricData.KhoVai || '').replace(/^Khổ\s+/i, '');
  if (gsmInput) gsmInput.value = fabricData.DinhLuongGSM || '';
  if (priceInput) priceInput.value = Math.round(parseFloat(fabricData.DonGia || 0));
  if (stockInput) stockInput.value = parseFloat(fabricData.SoLuongTon || 0);
  if (colorNameInput) colorNameInput.value = fabricData.MauSac || 'Be Mộc';
  if (colorHexInput) colorHexInput.value = fabricData.MaMauHex || '#e8dec8';
  if (macroHiddenInput) macroHiddenInput.value = fabricData.HinhAnhMacro || 'linen_macroscopic_ivory.jpg';
  if (macroLabel) {
    macroLabel.textContent = 'Đang dùng: ' + (fabricData.HinhAnhMacro || 'linen_macroscopic_ivory.jpg');
  }
  if (formTitle) {
    formTitle.innerHTML = '<i class="fa-solid fa-pen-to-square text-amber-600"></i> Chỉnh Sửa Thông Số Mẫu Vải (SKU: ' + (fabricData.MaSKU || '') + ')';
  }
  if (submitBtn) {
    submitBtn.textContent = 'Cập Nhật Lên Web Store';
  }

  // Đồng bộ thanh chọn trên Sidebar sang mục 2. Mẫu Vải Mới (CRUD)
  const crudSidebarLink = document.querySelector('.sidebar-nav-link[data-target="quan-ly-vai"]');
  if (crudSidebarLink) {
    setActiveSidebarLink(crudSidebarLink);
  }

  scrollWorkspaceToSection('quan-ly-vai', true);
  panel.classList.add('ring-2', 'ring-slate-900');
  if (nameInput) {
    nameInput.focus();
  }
}

// 5. Hàm đặt lại Form về chế độ "Thêm Mẫu Vải Mới"
function resetFabricForm() {
  const panel = document.getElementById('add-fabric-panel');
  const form = document.getElementById('fabric-crud-form');
  if (!form) {
    return;
  }

  form.action = getRouterUrl() + '?action=storeFabric';
  form.reset();

  const idInput = document.getElementById('input-ma-bien-the');
  const skuInput = document.getElementById('input-ma-sku');
  const widthInput = document.getElementById('input-kho-vai');
  const gsmInput = document.getElementById('input-dinh-luong-gsm');
  const priceInput = document.getElementById('input-don-gia');
  const stockInput = document.getElementById('input-so-luong-ton');
  const colorNameInput = document.getElementById('input-mau-sac');
  const colorHexInput = document.getElementById('input-ma-mau-hex');
  const macroHiddenInput = document.getElementById('input-hinh-anh-macro');
  const macroLabel = document.getElementById('selected-macro-label');
  const formTitle = document.getElementById('fabric-form-title');
  const submitBtn = document.getElementById('btn-submit-fabric');

  if (idInput) idInput.value = '';
  if (skuInput) skuInput.value = 'VAI-LINEN-PREMIUM';
  if (widthInput) widthInput.value = '1m50';
  if (gsmInput) gsmInput.value = '220 gsm';
  if (priceInput) priceInput.value = '185000';
  if (stockInput) stockInput.value = '350';
  if (colorNameInput) colorNameInput.value = 'Be Mộc';
  if (colorHexInput) colorHexInput.value = '#e8dec8';
  if (macroHiddenInput) macroHiddenInput.value = 'linen_macroscopic_ivory.jpg';
  if (macroLabel) {
    macroLabel.textContent = 'Đã chọn: linen_macroscopic_ivory.jpg (1.8MB)';
  }
  if (formTitle) {
    formTitle.innerHTML = '<i class="fa-solid fa-pen-to-square text-slate-600"></i> Nhập Thông Số Mẫu Vải Mới Cần Đăng Tải';
  }
  if (submitBtn) {
    submitBtn.textContent = 'Đăng Lên Web Store';
  }
  if (panel) {
    panel.classList.remove('ring-2', 'ring-slate-900');
  }
}

// 6. Sự kiện DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
  // Gắn sự kiện click cho các mục trên thanh công cụ Mục lục quản trị (Sidebar)
  const sidebarLinks = document.querySelectorAll('.sidebar-nav-link');
  sidebarLinks.forEach((link, idx) => {
    link.addEventListener('click', (e) => {
      e.preventDefault();
      const targetId = link.getAttribute('data-target') || link.getAttribute('href')?.replace('#', '') || 'tong-quan';

      // Đổi thanh màu đen cố định sang đúng mục vừa bấm
      setActiveSidebarLink(link);
      sessionStorage.setItem('aura_active_nav_index', String(idx));

      // Cập nhật URL hash mà không làm giật/cuộn thanh Sidebar
      if (history.replaceState) {
        history.replaceState(null, '', '#' + targetId);
      }

      // Chỉ cuộn mượt khung nội dung bên phải (#main-workspace) tới phần được chọn
      scrollWorkspaceToSection(targetId, true);
    });
  });

  // Khôi phục mục đang chọn từ URL hash (sau khi submit form) hoặc từ sessionStorage
  const currentHash = window.location.hash ? window.location.hash.substring(1) : '';
  if (currentHash) {
    const matchingLink = document.querySelector(`.sidebar-nav-link[data-target="${currentHash}"]`);
    if (matchingLink) {
      setActiveSidebarLink(matchingLink);
      setTimeout(() => scrollWorkspaceToSection(currentHash, false), 50);
    }
  } else {
    const savedIndex = sessionStorage.getItem('aura_active_nav_index');
    if (savedIndex !== null && sidebarLinks[Number(savedIndex)]) {
      setActiveSidebarLink(sidebarLinks[Number(savedIndex)]);
    }
  }

  // Xử lý nút Xuất File Excel/CSV Chuẩn Odoo (#btn-export-odoo)
  const exportBtn = document.getElementById('btn-export-odoo');
  if (exportBtn) {
    exportBtn.addEventListener('click', () => {
      const originalText = exportBtn.innerHTML;
      const totalOrders = exportBtn.getAttribute('data-total-orders') || '142';
      exportBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> Đang kết xuất ${totalOrders} đơn...`;
      exportBtn.classList.add('opacity-75');

      const fromDateEl = document.getElementById('filter-from-date');
      const toDateEl = document.getElementById('filter-to-date');
      const statusEl = document.getElementById('filter-order-status');

      const params = new URLSearchParams({ action: 'exportOdoo' });
      if (fromDateEl && fromDateEl.value) params.set('from_date', fromDateEl.value);
      if (toDateEl && toDateEl.value) params.set('to_date', toDateEl.value);
      if (statusEl && statusEl.value) params.set('order_status', statusEl.value);

      setTimeout(() => {
        window.location.href = getRouterUrl() + '?' + params.toString();

        exportBtn.innerHTML = '<i class="fa-solid fa-check mr-2 text-emerald-400"></i> Đã tải: ODOO_EXPORT_20250520.xlsx';
        exportBtn.classList.remove('opacity-75');
        exportBtn.classList.add('bg-emerald-600');

        setTimeout(() => {
          exportBtn.innerHTML = originalText;
          exportBtn.classList.remove('bg-emerald-600');
        }, 3500);
      }, 800);
    });
  }

  // Xử lý nút "Thêm Mẫu Vải Mới" (#btn-open-add-fabric)
  const addFabricBtn = document.getElementById('btn-open-add-fabric');
  const addFabricPanel = document.getElementById('add-fabric-panel');
  if (addFabricBtn && addFabricPanel) {
    addFabricBtn.addEventListener('click', () => {
      resetFabricForm();
      const crudSidebarLink = document.querySelector('.sidebar-nav-link[data-target="quan-ly-vai"]');
      if (crudSidebarLink) {
        setActiveSidebarLink(crudSidebarLink);
      }
      scrollWorkspaceToSection('quan-ly-vai', true);
      addFabricPanel.classList.toggle('ring-2');
      addFabricPanel.classList.toggle('ring-slate-900');
      const nameInput = document.getElementById('input-ten-sp');
      if (nameInput) {
        nameInput.focus();
      }
    });
  }

  // Xử lý chọn file ảnh chụp cận sợi vải (Macro 1:1)
  const btnPickMacro = document.getElementById('btn-pick-macro');
  const fileMacroInput = document.getElementById('input-macro-file');
  const selectedMacroLabel = document.getElementById('selected-macro-label');
  const hiddenMacroInput = document.getElementById('input-hinh-anh-macro');

  if (btnPickMacro && fileMacroInput) {
    btnPickMacro.addEventListener('click', () => {
      fileMacroInput.click();
    });

    fileMacroInput.addEventListener('change', () => {
      if (fileMacroInput.files && fileMacroInput.files.length > 0) {
        const file = fileMacroInput.files[0];
        const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
        if (selectedMacroLabel) {
          selectedMacroLabel.textContent = `Đã chọn: ${file.name} (${sizeMB}MB)`;
        }
        if (hiddenMacroInput) {
          hiddenMacroInput.value = file.name;
        }
      }
    });
  }
});
