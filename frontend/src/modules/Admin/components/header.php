<?php
/**
 * Header & Sidebar Component cho Module Admin
 * Vị trí: frontend/src/modules/Admin/components/header.php
 */
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = '';
if (($pos = strpos($scriptName, '/backend/')) !== false) {
  $baseUrl = substr($scriptName, 0, $pos);
} elseif (($pos = strpos($scriptName, '/frontend/')) !== false) {
  $baseUrl = substr($scriptName, 0, $pos);
} else {
  $baseUrl = rtrim(dirname($scriptName), '/');
}
$routerUrl = ($baseUrl !== '' ? $baseUrl : '') . '/index.php';
$assetsUrl = ($baseUrl !== '' ? $baseUrl : '') . '/frontend/public/assets';
$projectRootDir = dirname(__DIR__, 5);
?>
<!DOCTYPE html>

<html class="light" lang="vi">

<head>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Aura Admin Portal - Phân hệ Điều hành &amp; Quản trị Web</title>
  <!-- Tailwind CSS CDN with Plugins -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <!-- Tailwind Configuration for Light Theme matching inspiration -->
  <script>
    window.AURA_ROUTER_URL = <?= json_encode($routerUrl, JSON_UNESCAPED_SLASHES) ?>;
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#ecfdf5',
              100: '#d1fae5',
              400: '#34d399',
              500: '#10b981',
              600: '#059669',
              700: '#047857'
            },
            primaryDark: '#0f172a',
            surface: {
              base: '#f8fafc',
              card: '#ffffff',
              hover: '#f1f5f9',
              border: '#e2e8f0',
              borderSubtle: '#f1f5f9'
            }
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
          }
        }
      }
    }
  </script>
  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap"
    rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <!-- Custom Styles for Scrollbars & Refined UI -->
  <link
    href="<?= htmlspecialchars($assetsUrl, ENT_QUOTES, 'UTF-8') ?>/css/style.css?v=<?= @filemtime($projectRootDir . '/frontend/public/assets/css/style.css') ?: time() ?>"
    rel="stylesheet" />
</head>

<body
  class="bg-[#f8fafc] text-slate-800 font-sans antialiased h-screen overflow-hidden flex flex-col selection:bg-slate-900 selection:text-white">
  <!-- BEGIN: TopHeader -->
  <header
    class="relative h-16 bg-white border-b border-slate-200 flex-shrink-0 z-50 px-6 flex items-center justify-between"
    data-purpose="top-navigation">
    <!-- Brand & Section Breadcrumb -->
    <div class="flex items-center space-x-6">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center font-bold text-white shadow-sm">
          <i class="fa-solid fa-shapes text-sm"></i>
        </div>
        <div>
          <div class="flex items-center space-x-1.5">
            <span class="font-bold tracking-tight text-sm text-slate-900 uppercase">AURA</span>
            <span class="text-xs text-slate-500 font-medium">PORTAL</span>
          </div>
          <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider block -mt-0.5">Phân hệ điều
            hành</span>
        </div>
      </div>
      <div class="hidden md:flex items-center text-xs text-slate-400 space-x-2 pl-6 border-l border-slate-200">
        <span class="text-slate-500">SYS.MONITOR / VẬN HÀNH THỜI GIAN THỰC</span>
        <span class="text-slate-300">/</span>
        <span class="text-slate-700 font-medium">Module 4: Quản trị Vải &amp; Đồng bộ Odoo</span>
      </div>
    </div>
    <!-- Quick Status & Profile Area -->
    <div class="flex items-center space-x-5">
      <!-- Odoo Sync Pulse Badge -->
      <div
        class="hidden sm:flex items-center space-x-2 bg-emerald-50 border border-emerald-200/80 px-3 py-1.5 rounded-full text-xs">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span class="text-emerald-700 font-medium text-[11px]">Đồng bộ Odoo: Sẵn sàng</span>
      </div>
      <!-- Live Clock -->
      <div
        class="hidden lg:flex items-center space-x-1.5 text-xs text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
        <i class="fa-regular fa-clock text-slate-400 text-xs"></i>
        <span class="text-slate-600 font-mono text-[11px]">Cập nhật: <strong class="font-semibold text-slate-800"
            id="live-clock">--:--:--</strong></span>
      </div>
      <!-- Action Buttons (Thông báo & Bộ lọc / Cài đặt nhanh) -->
      <?php
      $notifSpamCount = (int) ($reviewCounts['spam'] ?? 0);
      $notifLowStockCount = (int) ($stats['ton_kho_canh_bao'] ?? 0);
      $notifReadyOrdersCount = (int) ($stats['don_cho_duyet'] ?? 0);
      $notifActiveVouchersCount = isset($vouchers) ? count($vouchers) : 0;
      $totalUnreadNotifs = ($notifSpamCount > 0 ? 1 : 0) + ($notifLowStockCount > 0 ? 1 : 0) + ($notifReadyOrdersCount > 0 ? 1 : 0);
      ?>
      <div class="relative flex items-center space-x-2" id="header-actions-container">
        <!-- Button 1: Thông báo hệ thống -->
        <div class="relative">
          <button aria-expanded="false"
            class="relative p-2 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 hover:text-slate-900 transition focus:outline-none cursor-pointer"
            id="btn-notifications"
            onclick="window.toggleHeaderDropdown && window.toggleHeaderDropdown('notifications', event)"
            title="Thông báo hệ thống" type="button">
            <i class="fa-regular fa-bell text-sm pointer-events-none"></i>
            <?php if ($totalUnreadNotifs > 0): ?>
              <span
                class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white pointer-events-none"
                id="notif-badge-dot"></span>
            <?php endif; ?>
          </button>

          <!-- Dropdown Panel: Thông báo hệ thống -->
          <div
            class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden"
            id="dropdown-notifications">
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <i class="fa-solid fa-bell text-xs text-slate-700"></i>
                <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Thông báo vận hành</span>
                <span class="px-1.5 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-700 rounded-full"
                  id="notif-count-pill"><?= $totalUnreadNotifs ?> mới</span>
              </div>
              <button class="text-[11px] text-emerald-600 hover:text-emerald-800 font-medium transition"
                id="btn-mark-all-read" type="button">
                Đánh dấu đã đọc
              </button>
            </div>

            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto text-xs">
              <!-- Thông báo 1: Cảnh báo Spam đánh giá -->
              <a class="notif-item flex items-start space-x-3 p-3.5 hover:bg-slate-50 transition cursor-pointer bg-rose-50/30"
                data-notif-target="kiem-duyet-danh-gia" href="#kiem-duyet-danh-gia">
                <div
                  class="w-8 h-8 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="fa-solid fa-shield-virus text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-900">Cảnh báo Spam Bình luận</p>
                    <span class="notif-unread-dot w-2 h-2 rounded-full bg-rose-500 flex-shrink-0"></span>
                  </div>
                  <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                    Phát hiện <strong class="text-rose-600"><?= $notifSpamCount ?> đánh giá</strong> chứa link rác / từ
                    ngữ vi phạm cần kiểm duyệt ngay.
                  </p>
                  <span class="text-[10px] text-slate-400 mt-1 block">Mục 4 • Kiểm duyệt đánh giá</span>
                </div>
              </a>

              <!-- Thông báo 2: Cảnh báo tồn kho vải -->
              <a class="notif-item flex items-start space-x-3 p-3.5 hover:bg-slate-50 transition cursor-pointer bg-amber-50/20"
                data-notif-target="quan-ly-vai" href="#quan-ly-vai">
                <div
                  class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="fa-solid fa-box-open text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-900">Tồn kho Vải chạm ngưỡng</p>
                    <span class="notif-unread-dot w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                  </div>
                  <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                    Hiện có <strong class="text-amber-700"><?= $notifLowStockCount ?> mã SKU</strong> có số mét tồn kho
                    dưới 20m hoặc tạm hết hàng.
                  </p>
                  <span class="text-[10px] text-slate-400 mt-1 block">Mục 2 • Quản lý mẫu vải (CRUD)</span>
                </div>
              </a>

              <!-- Thông báo 3: Đơn hàng chờ xuất Odoo -->
              <a class="notif-item flex items-start space-x-3 p-3.5 hover:bg-slate-50 transition cursor-pointer"
                data-notif-target="dong-bo-odoo" href="#dong-bo-odoo">
                <div
                  class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="fa-solid fa-file-excel text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold text-slate-900">Đồng bộ Đơn hàng Odoo ERP</p>
                    <span class="notif-unread-dot w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                  </div>
                  <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                    Có <strong class="text-emerald-700"><?= $notifReadyOrdersCount ?> đơn hàng</strong> đã xác nhận, sẵn
                    sàng kết xuất file <code class="font-mono text-[10px]">sales.order</code>.
                  </p>
                  <span class="text-[10px] text-slate-400 mt-1 block">Mục 1 • Đồng bộ Odoo Core</span>
                </div>
              </a>

              <!-- Thông báo 4: Chiến dịch Voucher -->
              <a class="notif-item flex items-start space-x-3 p-3.5 hover:bg-slate-50 transition cursor-pointer"
                data-notif-target="voucher-khuyen-mai" href="#voucher-khuyen-mai">
                <div
                  class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="fa-solid fa-ticket text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-semibold text-slate-900">Chiến dịch Voucher đang chạy</p>
                  <p class="text-[11px] text-slate-600 mt-0.5 leading-snug">
                    Hệ thống đang kích hoạt <strong><?= $notifActiveVouchersCount ?> mã khuyến mãi</strong> trên
                    Storefront.
                  </p>
                  <span class="text-[10px] text-slate-400 mt-1 block">Mục 3 • Quản lý Voucher</span>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- Button 2: Bộ lọc nhanh & Tùy chỉnh hiển thị -->
        <div class="relative">
          <button aria-expanded="false"
            class="p-2 rounded-lg bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 hover:text-slate-900 transition focus:outline-none cursor-pointer"
            id="btn-quick-filter" onclick="window.toggleHeaderDropdown && window.toggleHeaderDropdown('filter', event)"
            title="Bộ lọc nhanh &amp; Tùy chỉnh hiển thị" type="button">
            <i class="fa-solid fa-sliders text-sm pointer-events-none"></i>
          </button>

          <!-- Dropdown Panel: Bộ lọc nhanh & Cài đặt hiển thị -->
          <div
            class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden"
            id="dropdown-quick-filter">
            <div class="px-4 py-3 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <i class="fa-solid fa-sliders text-xs text-slate-700"></i>
                <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Bộ lọc &amp; Tùy chỉnh hiển
                  thị</span>
              </div>
              <button class="text-[11px] text-slate-500 hover:text-slate-900 font-medium transition"
                id="btn-reset-quick-filter" type="button">
                <i class="fa-solid fa-rotate-left mr-0.5"></i> Đặt lại
              </button>
            </div>

            <div class="p-4 space-y-4 text-xs">
              <!-- Công cụ 1: Tìm kiếm nhanh trên toàn trang -->
              <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1.5" for="quick-search-input">
                  1. Tìm nhanh trên bảng (SKU, Tên vải, Mã đơn, Khách hàng):
                </label>
                <div class="relative">
                  <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                  <input
                    class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:ring-0"
                    id="quick-search-input" placeholder="Nhập từ khóa để lọc tức thì..." type="text" />
                </div>
                <p class="text-[10px] text-slate-400 mt-1" id="quick-search-status">Đang hiển thị toàn bộ dữ liệu trên
                  trang.</p>
              </div>

              <!-- Công cụ 2: Lọc nhanh trạng thái Mẫu vải -->
              <div class="pt-2 border-t border-slate-100">
                <span class="block text-[11px] font-semibold text-slate-700 mb-1.5">
                  2. Lọc nhanh bảng Mẫu Vải (Mục 2):
                </span>
                <div class="grid grid-cols-3 gap-1.5" id="quick-fabric-status-group">
                  <button
                    class="quick-fabric-filter-btn px-2 py-1.5 rounded-lg text-[11px] font-medium bg-slate-900 text-white transition"
                    data-status="all" type="button">Tất cả</button>
                  <button
                    class="quick-fabric-filter-btn px-2 py-1.5 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition"
                    data-status="active" type="button">Đang bán Web</button>
                  <button
                    class="quick-fabric-filter-btn px-2 py-1.5 rounded-lg text-[11px] font-medium bg-rose-50 text-rose-700 hover:bg-rose-100 transition"
                    data-status="low" type="button">Sắp hết / Hết</button>
                </div>
              </div>

              <!-- Công cụ 3: Bật / Tắt hiển thị các phân khu -->
              <div class="pt-2 border-t border-slate-100">
                <span class="block text-[11px] font-semibold text-slate-700 mb-1.5">
                  3. Ẩn / Hiện các phân khu trên màn hình:
                </span>
                <div class="space-y-1.5 text-slate-600">
                  <label class="flex items-center justify-between py-1 px-2 rounded hover:bg-slate-50 cursor-pointer">
                    <span>Thẻ thống kê tổng quan (4 Cards)</span>
                    <input checked=""
                      class="toggle-section-checkbox rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                      data-section-id="top-stats-grid" type="checkbox" />
                  </label>
                  <label class="flex items-center justify-between py-1 px-2 rounded hover:bg-slate-50 cursor-pointer">
                    <span>1. Đồng bộ Đơn hàng &amp; Xuất Odoo</span>
                    <input checked=""
                      class="toggle-section-checkbox rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                      data-section-id="dong-bo-odoo" type="checkbox" />
                  </label>
                  <label class="flex items-center justify-between py-1 px-2 rounded hover:bg-slate-50 cursor-pointer">
                    <span>2. Quản lý Mẫu Vải Mới (CRUD)</span>
                    <input checked=""
                      class="toggle-section-checkbox rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                      data-section-id="quan-ly-vai" type="checkbox" />
                  </label>
                  <label class="flex items-center justify-between py-1 px-2 rounded hover:bg-slate-50 cursor-pointer">
                    <span>3. Quản lý Voucher &amp; Khuyến Mãi</span>
                    <input checked=""
                      class="toggle-section-checkbox rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                      data-section-id="voucher-khuyen-mai" type="checkbox" />
                  </label>
                  <label class="flex items-center justify-between py-1 px-2 rounded hover:bg-slate-50 cursor-pointer">
                    <span>4. Duyệt Đánh Giá &amp; Bình Luận</span>
                    <input checked=""
                      class="toggle-section-checkbox rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                      data-section-id="kiem-duyet-danh-gia" type="checkbox" />
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Script trực tiếp cho 2 nút Thông báo & Bộ lọc trên Header (chống cache trình duyệt 100%) -->
      <script>
        (function () {
          window.closeAllHeaderDropdowns = function () {
            var dn = document.getElementById('dropdown-notifications');
            var df = document.getElementById('dropdown-quick-filter');
            var bn = document.getElementById('btn-notifications');
            var bf = document.getElementById('btn-quick-filter');
            if (dn) dn.classList.add('hidden');
            if (df) df.classList.add('hidden');
            if (bn) {
              bn.classList.remove('bg-slate-900', 'text-white', 'border-slate-900');
              bn.classList.add('bg-slate-50', 'text-slate-600', 'border-slate-200');
            }
            if (bf) {
              bf.classList.remove('bg-slate-900', 'text-white', 'border-slate-900');
              bf.classList.add('bg-slate-50', 'text-slate-600', 'border-slate-200');
            }
          };

          window.toggleHeaderDropdown = function (type, e) {
            if (e) {
              e.preventDefault();
              e.stopPropagation();
            }
            var dn = document.getElementById('dropdown-notifications');
            var df = document.getElementById('dropdown-quick-filter');
            var bn = document.getElementById('btn-notifications');
            var bf = document.getElementById('btn-quick-filter');

            if (type === 'notifications' && dn && bn) {
              var willOpen = dn.classList.contains('hidden');
              window.closeAllHeaderDropdowns();
              if (willOpen) {
                dn.classList.remove('hidden');
                bn.classList.remove('bg-slate-50', 'text-slate-600', 'border-slate-200');
                bn.classList.add('bg-slate-900', 'text-white', 'border-slate-900');
              }
            } else if (type === 'filter' && df && bf) {
              var willOpenFilter = df.classList.contains('hidden');
              window.closeAllHeaderDropdowns();
              if (willOpenFilter) {
                df.classList.remove('hidden');
                bf.classList.remove('bg-slate-50', 'text-slate-600', 'border-slate-200');
                bf.classList.add('bg-slate-900', 'text-white', 'border-slate-900');
                var searchInput = document.getElementById('quick-search-input');
                if (searchInput) {
                  setTimeout(function () { searchInput.focus(); }, 50);
                }
              }
            }
          };

          document.addEventListener('DOMContentLoaded', function () {
            var headerActionsContainer = document.getElementById('header-actions-container');

            document.addEventListener('click', function (e) {
              if (headerActionsContainer && !headerActionsContainer.contains(e.target)) {
                window.closeAllHeaderDropdowns();
              }
            });

            document.addEventListener('keydown', function (e) {
              if (e.key === 'Escape') {
                window.closeAllHeaderDropdowns();
              }
            });

            // Nút "Đánh dấu đã đọc"
            var btnMarkAllRead = document.getElementById('btn-mark-all-read');
            var notifBadgeDot = document.getElementById('notif-badge-dot');
            var notifCountPill = document.getElementById('notif-count-pill');
            if (btnMarkAllRead) {
              btnMarkAllRead.addEventListener('click', function (e) {
                e.stopPropagation();
                if (notifBadgeDot) notifBadgeDot.classList.add('hidden');
                if (notifCountPill) {
                  notifCountPill.textContent = '0 mới';
                  notifCountPill.classList.remove('bg-rose-100', 'text-rose-700');
                  notifCountPill.classList.add('bg-slate-100', 'text-slate-500');
                }
                document.querySelectorAll('.notif-unread-dot').forEach(function (dot) {
                  dot.classList.add('hidden');
                });
                document.querySelectorAll('.notif-item').forEach(function (item) {
                  item.classList.remove('bg-rose-50/30', 'bg-amber-50/20');
                });
              });
            }

            // Click vào từng thông báo -> cuộn tới section tương ứng
            document.querySelectorAll('.notif-item').forEach(function (item) {
              item.addEventListener('click', function (e) {
                e.preventDefault();
                var targetId = item.getAttribute('data-notif-target');
                var dot = item.querySelector('.notif-unread-dot');
                if (dot) dot.classList.add('hidden');
                item.classList.remove('bg-rose-50/30', 'bg-amber-50/20');
                window.closeAllHeaderDropdowns();

                if (targetId) {
                  var matchingSidebarLink = document.querySelector('.sidebar-nav-link[data-target="' + targetId + '"]');
                  if (matchingSidebarLink && typeof setActiveSidebarLink === 'function') {
                    setActiveSidebarLink(matchingSidebarLink);
                  }
                  if (typeof scrollWorkspaceToSection === 'function') {
                    scrollWorkspaceToSection(targetId, true);
                  } else {
                    var el = document.getElementById(targetId);
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                  }
                  var targetSection = document.getElementById(targetId);
                  if (targetSection) {
                    targetSection.classList.add('ring-2', 'ring-slate-900');
                    setTimeout(function () {
                      targetSection.classList.remove('ring-2', 'ring-slate-900');
                    }, 1800);
                  }
                }
              });
            });

            // Bộ lọc nhanh trong Dropdown
            var currentQuickFabricStatus = 'all';
            var quickSearchInput = document.getElementById('quick-search-input');
            var quickSearchStatus = document.getElementById('quick-search-status');
            var quickFabricFilterBtns = document.querySelectorAll('.quick-fabric-filter-btn');
            var toggleSectionCheckboxes = document.querySelectorAll('.toggle-section-checkbox');
            var btnResetQuickFilter = document.getElementById('btn-reset-quick-filter');

            function applyQuickFilters() {
              var keyword = (quickSearchInput ? quickSearchInput.value : '').trim().toLowerCase();
              var totalMatched = 0;

              document.querySelectorAll('.fabric-row-item').forEach(function (row) {
                var text = (row.textContent || '').toLowerCase();
                var rowStatus = row.getAttribute('data-fabric-status') || 'active';
                var matchKeyword = !keyword || text.includes(keyword);
                var matchStatus = currentQuickFabricStatus === 'all' || rowStatus === currentQuickFabricStatus;

                if (matchKeyword && matchStatus) {
                  row.classList.remove('hidden');
                  totalMatched++;
                } else {
                  row.classList.add('hidden');
                }
              });

              ['.order-row-item', '.voucher-card-item', '.review-card-item'].forEach(function (selector) {
                document.querySelectorAll(selector).forEach(function (el) {
                  var text = (el.textContent || '').toLowerCase();
                  if (!keyword || text.includes(keyword)) {
                    el.classList.remove('hidden');
                    totalMatched++;
                  } else {
                    el.classList.add('hidden');
                  }
                });
              });

              if (quickSearchStatus) {
                if (!keyword && currentQuickFabricStatus === 'all') {
                  quickSearchStatus.textContent = 'Đang hiển thị toàn bộ dữ liệu trên trang.';
                } else {
                  quickSearchStatus.textContent = 'Tìm thấy ' + totalMatched + ' mục khớp với bộ lọc nhanh.';
                }
              }
            }

            if (quickSearchInput) {
              quickSearchInput.addEventListener('input', applyQuickFilters);
            }

            quickFabricFilterBtns.forEach(function (btn) {
              btn.addEventListener('click', function () {
                currentQuickFabricStatus = btn.getAttribute('data-status') || 'all';
                quickFabricFilterBtns.forEach(function (b) {
                  b.classList.remove('bg-slate-900', 'text-white');
                  if (b.getAttribute('data-status') === 'low') {
                    b.classList.add('bg-rose-50', 'text-rose-700');
                  } else {
                    b.classList.add('bg-slate-100', 'text-slate-700');
                  }
                });
                btn.classList.remove('bg-slate-100', 'text-slate-700', 'bg-rose-50', 'text-rose-700');
                btn.classList.add('bg-slate-900', 'text-white');

                applyQuickFilters();
                if (typeof scrollWorkspaceToSection === 'function') {
                  scrollWorkspaceToSection('quan-ly-vai', true);
                }
              });
            });

            toggleSectionCheckboxes.forEach(function (checkbox) {
              checkbox.addEventListener('change', function () {
                var sectionId = checkbox.getAttribute('data-section-id');
                if (!sectionId) return;
                var sectionEl = document.getElementById(sectionId);
                if (sectionEl) {
                  sectionEl.classList.toggle('hidden', !checkbox.checked);
                }
              });
            });

            if (btnResetQuickFilter) {
              btnResetQuickFilter.addEventListener('click', function () {
                if (quickSearchInput) quickSearchInput.value = '';
                currentQuickFabricStatus = 'all';

                quickFabricFilterBtns.forEach(function (b) {
                  var st = b.getAttribute('data-status');
                  b.classList.remove('bg-slate-900', 'text-white', 'bg-slate-100', 'text-slate-700', 'bg-rose-50', 'text-rose-700');
                  if (st === 'all') {
                    b.classList.add('bg-slate-900', 'text-white');
                  } else if (st === 'low') {
                    b.classList.add('bg-rose-50', 'text-rose-700');
                  } else {
                    b.classList.add('bg-slate-100', 'text-slate-700');
                  }
                });

                toggleSectionCheckboxes.forEach(function (cb) {
                  cb.checked = true;
                  var sectionId = cb.getAttribute('data-section-id');
                  var sectionEl = sectionId ? document.getElementById(sectionId) : null;
                  if (sectionEl) sectionEl.classList.remove('hidden');
                });

                applyQuickFilters();
              });
            }
          });
        })();
      </script>
      <!-- Admin Profile Avatar -->
      <div class="flex items-center space-x-3 pl-3 border-l border-slate-200">
        <div
          class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-sm">
          AD
        </div>
        <div class="hidden xl:block text-left">
          <p class="text-xs font-semibold leading-tight text-slate-900">Trần Minh Quyền</p>
          <p class="text-[10px] text-slate-400">Quản trị viên Cấp cao</p>
        </div>
      </div>
    </div>
  </header>
  <!-- END: TopHeader -->
  <!-- BEGIN: MainContainerLayout -->
  <div class="flex flex-1 overflow-hidden" data-purpose="dashboard-container">
    <!-- BEGIN: SidebarNav -->
    <aside
      class="w-64 h-full bg-white border-r border-slate-200 flex-shrink-0 flex flex-col justify-between hidden md:flex select-none"
      data-purpose="sidebar-menu">
      <div class="py-4 overflow-y-auto">
        <div class="px-5 mb-2.5">
          <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Mục lục quản trị</span>
        </div>
        <nav class="space-y-1 px-3" id="sidebar-nav">
          <!-- Item: Dashboard -->
          <a class="sidebar-nav-link flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition text-xs font-medium"
            data-target="tong-quan" href="#tong-quan">
            <i class="nav-icon fa-solid fa-chart-pie w-4 text-center text-slate-400"
              data-default-color="text-slate-400"></i>
            <span>Tổng quan</span>
          </a>
          <!-- Item 1: Đồng bộ Odoo -->
          <a class="sidebar-nav-link flex items-center justify-between px-3.5 py-2.5 rounded-lg bg-slate-900 text-white font-medium text-xs shadow-sm transition"
            data-target="dong-bo-odoo" href="#dong-bo-odoo">
            <div class="flex items-center space-x-3">
              <i class="nav-icon fa-solid fa-file-excel w-4 text-center text-emerald-400"
                data-default-color="text-emerald-600"></i>
              <span>1. Xuất Đơn &amp; Odoo Core</span>
            </div>
            <span
              class="text-[10px] bg-rose-50 text-rose-600 border border-rose-200 px-1.5 py-0.5 rounded font-semibold">Lõi</span>
          </a>
          <!-- Item 2: Quản lý Sản phẩm vải (CRUD) -->
          <a class="sidebar-nav-link flex items-center justify-between px-3.5 py-2.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition text-xs font-medium"
            data-target="quan-ly-vai" href="#quan-ly-vai">
            <div class="flex items-center space-x-3">
              <i class="nav-icon fa-solid fa-scroll w-4 text-center text-slate-400"
                data-default-color="text-slate-400"></i>
              <span>2. Mẫu Vải Mới (CRUD)</span>
            </div>
            <span
              class="nav-badge-count text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-mono"><?= isset($fabrics) ? count($fabrics) : (int) ($stats['tong_mau_vai'] ?? 0) ?></span>
          </a>
          <!-- Item 3: Voucher -->
          <a class="sidebar-nav-link flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition text-xs font-medium"
            data-target="voucher-khuyen-mai" href="#voucher-khuyen-mai">
            <i class="nav-icon fa-solid fa-ticket w-4 text-center text-amber-500"
              data-default-color="text-amber-500"></i>
            <span>3. Quản lý Voucher</span>
          </a>
          <!-- Item 4: Duyệt đánh giá -->
          <a class="sidebar-nav-link flex items-center justify-between px-3.5 py-2.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition text-xs font-medium"
            data-target="kiem-duyet-danh-gia" href="#kiem-duyet-danh-gia">
            <div class="flex items-center space-x-3">
              <i class="nav-icon fa-solid fa-shield-halved w-4 text-center text-sky-500"
                data-default-color="text-sky-500"></i>
              <span>4. Duyệt Đánh Giá</span>
            </div>
            <span
              class="text-[10px] bg-amber-50 text-amber-700 border border-amber-200 px-1.5 py-0.5 rounded font-medium"><?= (int) ($reviewCounts['pending'] ?? 0) ?>
              Cần xem</span>
          </a>
          <div class="pt-5 px-2 pb-1.5">
            <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Báo cáo &amp; Cài đặt</span>
          </div>
          <a class="sidebar-nav-link flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition text-xs font-medium"
            data-target="quan-ly-vai" href="#quan-ly-vai">
            <i class="nav-icon fa-solid fa-warehouse w-4 text-center text-slate-400"
              data-default-color="text-slate-400"></i>
            <span>Kho &amp; Sản phẩm</span>
          </a>
          <a class="sidebar-nav-link flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition text-xs font-medium"
            data-target="voucher-khuyen-mai" href="#voucher-khuyen-mai">
            <i class="nav-icon fa-solid fa-share-nodes w-4 text-center text-slate-400"
              data-default-color="text-slate-400"></i>
            <span>Mạng lưới Affiliate</span>
          </a>
          <a class="sidebar-nav-link flex items-center space-x-3 px-3.5 py-2.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition text-xs font-medium"
            data-target="dong-bo-odoo" href="#dong-bo-odoo">
            <i class="nav-icon fa-solid fa-gear w-4 text-center text-slate-400" data-default-color="text-slate-400"></i>
            <span>Cấu hình Cổng Odoo 17</span>
          </a>
        </nav>
      </div>
      <!-- Footer Quick Status in Sidebar -->
      <div class="p-3.5 border-t border-slate-200 bg-slate-50/60 flex-shrink-0">
        <div class="p-3 rounded-lg bg-white border border-slate-200 shadow-sm text-xs">
          <div class="flex items-center justify-between text-slate-500 mb-1.5">
            <span class="font-medium text-slate-600">Server Sync Worker</span>
            <span class="text-emerald-600 font-mono text-[10px] font-semibold">99.98% OK</span>
          </div>
          <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: 92%"></div>
          </div>
          <p class="text-[10px] text-slate-400 mt-2">IP ERP: 192.168.10.88 (Odoo v16/17)</p>
        </div>
      </div>
    </aside>
    <!-- END: SidebarNav -->

