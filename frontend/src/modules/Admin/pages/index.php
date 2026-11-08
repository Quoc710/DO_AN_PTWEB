<?php
/**
 * View chính của Module 4: Phân hệ Điều hành & Quản trị Vải - Đồng bộ Odoo (Aura Admin Portal)
 * Vị trí: frontend/src/modules/Admin/pages/index.php
 */
if (!isset($stats)) {
  require_once dirname(__DIR__, 5) . '/backend/src/Modules/Admin/routes.php';
  dispatchAdminModuleRoutes();
  return;
}

require __DIR__ . '/../components/header.php';

$fromDateVal = htmlspecialchars((string) ($_GET['from_date'] ?? '2025-05-18'), ENT_QUOTES, 'UTF-8');
$toDateVal = htmlspecialchars((string) ($_GET['to_date'] ?? '2025-05-20'), ENT_QUOTES, 'UTF-8');
$statusFilter = (string) ($_GET['order_status'] ?? 'all');
$rangeFilter = (string) ($_GET['range'] ?? 'today');
$currentReviewFilter = (string) ($_GET['review_filter'] ?? 'all');

$totalOrdersShown = count($orders ?? []);
$totalAmountShown = 0.0;
if (!empty($orders)) {
  foreach ($orders as $oItem) {
    $totalAmountShown += (float) ($oItem['TongTien'] ?? 0);
  }
}
?>
<!-- BEGIN: MainContentWorkspace -->
<main class="flex-1 h-full overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6 bg-[#f8fafc] scroll-smooth"
  data-purpose="main-workspace" id="main-workspace">
  <div id="tong-quan"></div>
  <?php if (!empty($flashSuccess)): ?>
    <div
      class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs text-emerald-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
        <span class="font-medium"><?= htmlspecialchars((string) $flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <button class="text-emerald-600 hover:text-emerald-900" onclick="this.parentElement.remove()" type="button"><i
          class="fa-solid fa-xmark"></i></button>
    </div>
  <?php endif; ?>
  <?php if (!empty($flashError)): ?>
    <div
      class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-xs text-rose-800 flex items-center justify-between shadow-sm">
      <div class="flex items-center space-x-2">
        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
        <span class="font-medium"><?= htmlspecialchars((string) $flashError, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <button class="text-rose-600 hover:text-rose-900" onclick="this.parentElement.remove()" type="button"><i
          class="fa-solid fa-xmark"></i></button>
    </div>
  <?php endif; ?>
  <?php require __DIR__ . '/operations.php'; ?>
  <!-- Top Title Bar of Workspace -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
        <span>Quản trị cửa hàng Aura</span>
      </h1>
      <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
        Tổng quan Lookbook & hoa hồng, cùng các công cụ quản lý vải, Odoo, voucher và đánh giá.
      </p>
    </div>
    <div class="flex items-center space-x-3 bg-white px-3.5 py-2 rounded-lg border border-slate-200 shadow-sm">
      <span class="text-xs font-medium text-slate-600">Đồng bộ tự động:</span>
      <label class="relative inline-flex items-center cursor-pointer">
        <input checked="" class="sr-only peer" type="checkbox" />
        <div
          class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600">
        </div>
      </label>
    </div>
  </div>
  <!-- TOP STATS CARDS (Matching Inspiration Card Style - Hiển thị đúng 100% dữ liệu thực tế) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="top-stats-grid">
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
        <span class="font-semibold uppercase tracking-wider text-[11px] text-slate-400">DOANH THU ĐƠN HÀNG</span>
        <span
          class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-slate-600 border border-slate-100"><i
            class="fa-solid fa-wallet text-xs"></i></span>
      </div>
      <div class="text-xl font-bold text-slate-900">
        <?= htmlspecialchars((string) ($stats['doanh_thu_formatted'] ?? '0₫'), ENT_QUOTES, 'UTF-8') ?>
      </div>
      <div class="text-[11px] text-emerald-600 font-medium mt-1 flex items-center gap-1">
        <i class="fa-solid fa-arrow-up text-[10px]"></i> Tổng từ <?= (int) ($stats['don_hang_moi'] ?? 0) ?> đơn trên hệ
        thống
      </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
        <span class="font-semibold uppercase tracking-wider text-[11px] text-slate-400">ĐƠN HÀNG HIỆN CÓ</span>
        <span
          class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-slate-600 border border-slate-100"><i
            class="fa-solid fa-bag-shopping text-xs"></i></span>
      </div>
      <div class="text-xl font-bold text-slate-900"><?= (int) ($stats['don_hang_moi'] ?? 0) ?></div>
      <div class="text-[11px] text-emerald-600 font-medium mt-1 flex items-center gap-1">
        <i class="fa-solid fa-circle-check text-[10px]"></i> <?= (int) ($stats['don_cho_duyet'] ?? 0) ?> đơn sẵn sàng
        xuất kho
      </div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
        <span class="font-semibold uppercase tracking-wider text-[11px] text-slate-400">TỒN KHO CẢNH BÁO</span>
        <span
          class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 border border-rose-100"><i
            class="fa-solid fa-box-open text-xs"></i></span>
      </div>
      <div class="text-xl font-bold text-rose-600">
        <?= htmlspecialchars((string) ($stats['ton_kho_canh_bao_label'] ?? '00 SKU'), ENT_QUOTES, 'UTF-8') ?>
      </div>
      <div class="text-[11px] text-slate-500 mt-1">Các mẫu vải tồn kho &lt; 20 mét</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
      <div class="flex items-center justify-between text-slate-500 text-xs mb-2">
        <span class="font-semibold uppercase tracking-wider text-[11px] text-slate-400">TỔNG MẪU VẢI WEB</span>
        <span
          class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-slate-600 border border-slate-100"><i
            class="fa-solid fa-scroll text-xs"></i></span>
      </div>
      <div class="text-xl font-bold text-slate-900"><?= (int) ($stats['tong_mau_vai_online'] ?? 0) ?> /
        <?= (int) ($stats['tong_mau_vai'] ?? 0) ?>
      </div>
      <div class="text-[11px] text-emerald-600 font-medium mt-1">
        <?= htmlspecialchars((string) ($stats['ty_le_online'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>% đang bán •
        <?= (int) ($stats['so_mau_het_hang'] ?? 0) ?> mẫu hết hàng
      </div>
    </div>
  </div>
  <section id="don-hang-online" class="rounded-xl border border-indigo-200 bg-white p-5 shadow-sm sm:p-6">
    <div class="mb-4 flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-indigo-600">VẬN HÀNH ĐƠN ONLINE</p>
        <h2 class="mt-1 text-lg font-bold text-slate-900">Quản lý đơn hàng khách đặt</h2>
        <p class="mt-1 text-xs text-slate-500">Bắt đầu giao sau khi đơn đã thanh toán hoặc chọn COD. Đơn đang giao sẽ tự hoàn tất khi hết thời hạn vận chuyển.</p>
      </div>
      <div class="flex gap-2">
        <select id="adminOrderFilter" class="rounded-lg border-slate-200 text-xs font-semibold text-slate-700">
          <option value="all">Tất cả trạng thái</option><option value="cho_thanh_toan">Chờ thanh toán</option><option value="dang_xu_ly">Đơn COD mới</option><option value="da_thanh_toan">Đã thanh toán</option><option value="dang_giao">Đang giao</option><option value="da_giao">Đã giao</option><option value="da_huy">Đã hủy</option>
        </select>
        <button id="refreshOnlineOrders" type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700"><i class="fa-solid fa-rotate mr-1"></i>Làm mới</button>
      </div>
    </div>
    <div id="adminOnlineOrders" class="space-y-3"><div class="rounded-lg bg-slate-50 p-6 text-center text-xs text-slate-500">Đang tải đơn hàng online…</div></div>
  </section>
  <!-- ========================================================================= -->
  <!-- SECTION A: TÍNH NĂNG LÕI: 1. ĐỒNG BỘ ĐƠN HÀNG & XUẤT EXCEL CHO ODOO -->
  <!-- ========================================================================= -->
  <!-- BEGIN: CoreFeatureOdooSync -->
  <section class="rounded-xl bg-white border border-slate-200 p-5 sm:p-6 shadow-sm" data-purpose="odoo-export-core"
    id="dong-bo-odoo">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5 border-b border-slate-100 pb-5">
      <!-- Left Info & Logic Steps -->
      <div class="space-y-3 max-w-2xl">
        <div class="flex items-center space-x-2.5">
          <span
            class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wide">
            <i class="fa-solid fa-bolt mr-1"></i> Tính năng lõi
          </span>
          <h2 class="text-lg font-bold text-slate-900 tracking-tight">1. Đồng bộ Đơn hàng &amp; Xuất File Chuẩn Odoo ERP
          </h2>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed">
          Quy trình kết nối cuối ngày: Lọc đơn hàng hợp lệ → Bấm xuất file Excel cấu trúc <code
            class="bg-slate-100 text-emerald-700 px-1.5 py-0.5 rounded font-mono font-medium border border-slate-200">sales.order</code>
          → Import trực tiếp vào Odoo để cấn trừ số mét vải và ghi nhận chứng từ tự động.
        </p>
        <!-- 2-Step Instruction Flow Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
          <div class="bg-slate-50/80 border border-slate-200 p-3.5 rounded-lg flex items-start space-x-3">
            <div
              class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
              1</div>
            <div>
              <h4 class="text-xs font-semibold text-slate-900">Xuất File Cuối Ngày</h4>
              <p class="text-[11px] text-slate-500 leading-normal mt-0.5">Hệ thống tổng hợp <?= $totalOrdersShown ?> đơn
                đang hiển thị, kiểm tra mã SKU &amp; khách hàng ra file .xlsx chuẩn.</p>
            </div>
          </div>
          <div class="bg-slate-50/80 border border-slate-200 p-3.5 rounded-lg flex items-start space-x-3">
            <div
              class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
              2</div>
            <div>
              <h4 class="text-xs font-semibold text-slate-900">Import Trực Tiếp Trên Odoo</h4>
              <p class="text-[11px] text-slate-500 leading-normal mt-0.5">Mở Odoo → Bấm "Import File", hệ thống tự tạo
                đơn bán và cấn trừ tồn kho chính xác.</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Right: Key Summary & Action CTA -->
      <div
        class="flex flex-col items-start lg:items-end justify-between self-stretch bg-slate-50/90 p-4.5 rounded-xl border border-slate-200 min-w-[280px]">
        <div class="text-left lg:text-right mb-3">
          <span class="text-[11px] text-slate-500 uppercase font-semibold tracking-wider">Đơn hàng khớp bộ lọc</span>
          <div class="flex items-baseline lg:justify-end gap-2 mt-0.5">
            <span class="text-2xl font-extrabold text-slate-900"><?= $totalOrdersShown ?></span>
            <span class="text-xs text-emerald-600 font-semibold">đơn hàng</span>
          </div>
          <p class="text-xs text-slate-500 font-mono mt-0.5">Tổng trị giá: <strong
              class="text-slate-900 font-bold"><?= number_format($totalAmountShown, 0, ',', '.') ?>₫</strong></p>
        </div>
        <!-- Primary Action Button (Sleek Dark Navy / Black matching inspiration) -->
        <button
          class="w-full lg:w-auto inline-flex items-center justify-center space-x-2 px-5 py-2.5 rounded-lg font-semibold text-xs uppercase tracking-wider bg-slate-900 hover:bg-slate-800 text-white transition duration-150 shadow-sm active:scale-95"
          data-total-orders="<?= $totalOrdersShown ?>" id="btn-export-odoo" type="button">
          <i class="fa-solid fa-file-excel text-sm text-emerald-400"></i>
          <span>Xuất File Excel Chuẩn Odoo (.xlsx)</span>
        </button>
      </div>
    </div>
    <!-- Filter Tools Bar (Đã bỏ icon cuốn lịch dư thừa bên trái) -->
    <form action="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>#dong-bo-odoo" class="mt-4 pt-1 flex flex-wrap items-center justify-between gap-3 text-xs"
      method="GET">
      <div class="flex flex-wrap items-center gap-2">
        <span class="text-slate-500 font-medium mr-1 flex items-center"><i
            class="fa-solid fa-filter mr-1.5 text-slate-400"></i> Bộ lọc:</span>
        <a class="px-2.5 py-1.5 rounded-lg <?= $rangeFilter === 'today' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200' ?> font-medium transition"
          href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?range=today&amp;from_date=2025-05-20&amp;to_date=2025-05-20#dong-bo-odoo">Hôm nay</a>
        <a class="px-2.5 py-1.5 rounded-lg <?= $rangeFilter === '7days' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-600 border border-slate-200' ?> transition font-medium"
          href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?range=7days&amp;from_date=2025-05-14&amp;to_date=2025-05-20#dong-bo-odoo">7 ngày gần nhất</a>
        <div
          class="flex items-center space-x-2 bg-white border border-slate-200 px-3 py-1 rounded-lg text-slate-600 shadow-sm">
          <input class="bg-transparent border-0 text-xs p-0 text-slate-700 focus:ring-0 cursor-pointer"
            id="filter-from-date" name="from_date" onchange="this.form.submit()" type="date"
            value="<?= $fromDateVal ?>" />
          <span class="text-slate-400 font-medium">đến</span>
          <input class="bg-transparent border-0 text-xs p-0 text-slate-700 focus:ring-0 cursor-pointer"
            id="filter-to-date" name="to_date" onchange="this.form.submit()" type="date" value="<?= $toDateVal ?>" />
        </div>
        <select
          class="bg-white border border-slate-200 text-xs rounded-lg text-slate-700 py-1.5 px-3 focus:ring-slate-900 focus:border-slate-900 shadow-sm"
          id="filter-order-status" name="order_status" onchange="this.form.submit()">
          <option <?= $statusFilter === 'all' ? 'selected' : '' ?> value="all">Tất cả trạng thái</option>
          <option <?= $statusFilter === 'ready' ? 'selected' : '' ?> value="ready">Trạng thái: Đã xác nhận &amp; Chờ xuất
            kho</option>
          <option <?= $statusFilter === 'voucher_only' ? 'selected' : '' ?> value="voucher_only">Chỉ đơn có voucher giảm
            giá</option>
        </select>
      </div>
      <div class="text-[11px] text-slate-500 flex items-center space-x-1.5">
        <i class="fa-solid fa-circle-check text-emerald-500"></i>
        <span>Dữ liệu schema Odoo Sales/Inventory: <strong class="text-slate-800 font-semibold">Khớp
            100%</strong></span>
      </div>
    </form>
    <!-- Order Preview Table (Có thanh cuộn mượt khi nhiều đơn hàng) -->
    <div
      class="mt-4 overflow-x-auto max-h-[280px] overflow-y-auto rounded-lg border border-slate-200 shadow-sm bg-white">
      <table class="w-full text-left text-xs text-slate-600" data-purpose="odoo-preview-table">
        <thead
          class="bg-slate-50 sticky top-0 z-10 uppercase text-[10px] text-slate-500 font-semibold border-b border-slate-200 tracking-wider">
          <tr>
            <th class="py-3 px-3.5 whitespace-nowrap">Mã Đơn</th>
            <th class="py-3 px-3.5">Khách Hàng / SĐT</th>
            <th class="py-3 px-3.5">Chi Tiết Vải (SKU &amp; Số Mét)</th>
            <th class="py-3 px-3.5 text-right whitespace-nowrap">Tổng Tiền</th>
            <th class="py-3 px-3.5 whitespace-nowrap">Ngày / Giờ</th>
            <th class="py-3 px-3.5 whitespace-nowrap min-w-[125px]">Trạng Thái ERP</th>
            <th class="py-3 px-3.5 text-center whitespace-nowrap">Kiểm Tra Cột</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
          <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
              <?php
              $isReadyERP = ((string) $order['TrangThaiERP']) === 'Sẵn sàng xuất';
              $erpBadgeClass = $isReadyERP
                ? 'bg-blue-50 text-blue-700 border-blue-200'
                : 'bg-emerald-50 text-emerald-700 border-emerald-200';
              ?>
              <tr class="order-row-item hover:bg-slate-50/70 transition">
                <td class="py-3 px-3.5 font-semibold text-slate-900 whitespace-nowrap">
                  <?= htmlspecialchars((string) $order['MaDonCode'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td class="py-3 px-3.5 font-sans"><span
                    class="font-semibold text-slate-800"><?= htmlspecialchars((string) $order['TenKH'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span
                    class="text-slate-400 block text-[10px]"><?= htmlspecialchars((string) ($order['SDT_Masked'] ?? $order['SDT']), ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td class="py-3 px-3.5 font-sans"><span
                    class="text-slate-800"><?= htmlspecialchars((string) $order['TenSP'], ENT_QUOTES, 'UTF-8') ?>
                    (<?= htmlspecialchars((string) $order['MaSKU'], ENT_QUOTES, 'UTF-8') ?>)</span> <span
                    class="text-emerald-700 block font-mono font-medium"><?= number_format((float) $order['SoLuongBan'], 1, '.', '') ?>
                    mét</span></td>
                <td class="py-3 px-3.5 text-right font-sans font-bold text-slate-900 whitespace-nowrap">
                  <?= number_format((float) $order['TongTien'], 0, ',', '.') ?>₫
                </td>
                <td class="py-3 px-3.5 text-slate-500 whitespace-nowrap">
                  <?= htmlspecialchars((string) $order['ThoiGianBan'], ENT_QUOTES, 'UTF-8') ?> <span
                    class="block text-[10px] text-slate-400"><?= date('d/m/Y', strtotime((string) $order['NgayBan'])) ?></span>
                </td>
                <td class="py-3 px-3.5 whitespace-nowrap min-w-[125px]"><span
                    class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-[10px] font-sans font-medium border <?= $erpBadgeClass ?>"><?= htmlspecialchars((string) $order['TrangThaiERP'], ENT_QUOTES, 'UTF-8') ?></span>
                </td>
                <td class="py-3 px-3.5 text-center whitespace-nowrap"><i class="fa-solid fa-check text-emerald-600"
                    title="Đầy đủ thông tin đối tác và mã kho"></i></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td class="py-6 px-3.5 text-center text-slate-400 font-sans" colspan="7">Không tìm thấy đơn hàng nào khớp
                với điều kiện lọc hiện tại.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  <!-- END: CoreFeatureOdooSync -->
  <!-- ========================================================================= -->
  <!-- SECTION B: 2. QUẢN LÝ SẢN PHẨM MẪU VẢI MỚI (CRUD) -->
  <!-- ========================================================================= -->
  <!-- BEGIN: ProductFabricCRUD -->
  <section class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 space-y-5 shadow-sm"
    data-purpose="fabric-crud-management" id="quan-ly-vai">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
      <div>
        <div class="flex items-center space-x-2">
          <span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span>
          <h3 class="text-base font-bold text-slate-900 uppercase tracking-wide">2. Quản lý Mẫu Vải Mới (Thêm / Sửa /
            Xóa)</h3>
        </div>
        <p class="text-xs text-slate-500 mt-0.5">Giao diện cập nhật danh mục chất liệu, khổ vải, định lượng gsm và đồng
          bộ đăng tải lên Web Storefront (Đang có <?= count($fabrics ?? []) ?> mẫu vải).</p>
      </div>
      <!-- Buttons Group -->
      <div class="flex items-center space-x-2.5">
        <a class="px-3 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-xs rounded-lg text-slate-700 font-medium transition flex items-center space-x-1.5 shadow-sm"
          href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=exportOdoo">
          <i class="fa-solid fa-file-import text-slate-400"></i>
          <span>Import Excel</span>
        </a>
        <button
          class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-lg transition shadow-sm flex items-center space-x-1.5"
          id="btn-open-add-fabric" type="button">
          <i class="fa-solid fa-plus text-xs"></i>
          <span>Thêm Mẫu Vải Mới</span>
        </button>
      </div>
    </div>
    <!-- Quick Form Drawer/Panel for CRUD (Collapsible / Demo ready) -->
    <form action="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=<?= !empty($editingFabric) ? 'updateFabric' : 'storeFabric' ?>"
      class="bg-slate-50/70 border border-slate-200 rounded-xl p-4.5 space-y-3.5 transition"
      enctype="multipart/form-data" id="fabric-crud-form" method="POST">
      <div class="space-y-3.5" id="add-fabric-panel">
        <input id="input-ma-bien-the" name="ma_bien_the" type="hidden"
          value="<?= !empty($editingFabric) ? (int) $editingFabric['MaBienThe'] : '' ?>" />
        <input id="input-mau-sac" name="mau_sac" type="hidden"
          value="<?= !empty($editingFabric) ? htmlspecialchars((string) $editingFabric['MauSac'], ENT_QUOTES, 'UTF-8') : 'Be Mộc' ?>" />
        <input id="input-ma-mau-hex" name="ma_mau_hex" type="hidden"
          value="<?= !empty($editingFabric) ? htmlspecialchars((string) $editingFabric['MaMauHex'], ENT_QUOTES, 'UTF-8') : '#e8dec8' ?>" />
        <input id="input-hinh-anh-macro" name="hinh_anh_macro" type="hidden"
          value="<?= !empty($editingFabric) ? htmlspecialchars((string) $editingFabric['HinhAnhMacro'], ENT_QUOTES, 'UTF-8') : 'linen_macroscopic_ivory.jpg' ?>" />
        <input accept="image/*" class="hidden" id="input-macro-file" name="macro_file" type="file" />
        <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
          <span class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5"
            id="fabric-form-title">
            <i class="fa-solid fa-pen-to-square text-slate-600"></i>
            <?= !empty($editingFabric) ? 'Chỉnh Sửa Thông Số Mẫu Vải (SKU: ' . htmlspecialchars((string) $editingFabric['MaSKU'], ENT_QUOTES, 'UTF-8') . ')' : 'Nhập Thông Số Mẫu Vải Mới Cần Đăng Tải' ?>
          </span>
          <span class="text-[11px] text-slate-500">Mẫu vải sẽ tự sinh mã SKU định dạng quốc tế</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
          <div>
            <label class="block text-[11px] font-medium text-slate-600 mb-1">Mã Vải (SKU)</label>
            <input
              class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 text-xs font-mono focus:border-slate-900 focus:ring-0 shadow-sm"
              id="input-ma-sku" name="ma_sku" required="" type="text"
              value="<?= !empty($editingFabric) ? htmlspecialchars((string) $editingFabric['MaSKU'], ENT_QUOTES, 'UTF-8') : 'VAI-LINEN-PREMIUM' ?>" />
          </div>
          <div>
            <label class="block text-[11px] font-medium text-slate-600 mb-1">Tên Mẫu Vải &amp; Chất Liệu</label>
            <input
              class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 text-xs focus:border-slate-900 focus:ring-0 shadow-sm"
              id="input-ten-sp" name="ten_sp" placeholder="VD: Linen Tưng Tự Nhiên Mộc" required="" type="text"
              value="<?= !empty($editingFabric) ? htmlspecialchars((string) $editingFabric['TenSP'], ENT_QUOTES, 'UTF-8') : '' ?>" />
          </div>
          <div>
            <label class="block text-[11px] font-medium text-slate-600 mb-1">Khổ Vải (Rộng)</label>
            <input
              class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 text-xs focus:border-slate-900 focus:ring-0 shadow-sm"
              id="input-kho-vai" name="kho_vai" required="" type="text"
              value="<?= !empty($editingFabric) ? htmlspecialchars(preg_replace('/^Khổ\s+/i', '', (string) $editingFabric['KhoVai']), ENT_QUOTES, 'UTF-8') : '1m50' ?>" />
          </div>
          <div>
            <label class="block text-[11px] font-medium text-slate-600 mb-1">Định Lượng (GSM)</label>
            <input
              class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 text-xs focus:border-slate-900 focus:ring-0 shadow-sm"
              id="input-dinh-luong-gsm" name="dinh_luong_gsm" required="" type="text"
              value="<?= !empty($editingFabric) ? htmlspecialchars((string) $editingFabric['DinhLuongGSM'], ENT_QUOTES, 'UTF-8') : '220 gsm' ?>" />
          </div>
          <div>
            <label class="block text-[11px] font-medium text-slate-600 mb-1">Giá Bán / Mét (VNĐ)</label>
            <input
              class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 text-xs font-semibold focus:border-slate-900 focus:ring-0 shadow-sm"
              id="input-don-gia" min="0" name="don_gia" required="" step="1000" type="number"
              value="<?= !empty($editingFabric) ? (int) $editingFabric['DonGia'] : 185000 ?>" />
          </div>
          <div>
            <label class="block text-[11px] font-medium text-slate-600 mb-1">Tồn Kho Ban Đầu (Mét)</label>
            <input
              class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-800 text-xs font-mono focus:border-slate-900 focus:ring-0 shadow-sm"
              id="input-so-luong-ton" min="0" name="so_luong_ton" required="" step="0.5" type="number"
              value="<?= !empty($editingFabric) ? (float) $editingFabric['SoLuongTon'] : 350 ?>" />
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
          <div class="flex items-center space-x-3 text-xs">
            <button
              class="px-3 py-1.5 border border-dashed border-slate-300 hover:border-slate-500 rounded-lg text-slate-600 hover:text-slate-900 bg-white flex items-center space-x-1.5 transition shadow-sm"
              id="btn-pick-macro" type="button">
              <i class="fa-solid fa-camera text-slate-400"></i>
              <span>Tải ảnh chụp cận sợi vải (Macro 1:1)</span>
            </button>
            <span class="text-[11px] text-slate-400" id="selected-macro-label">Đã chọn:
              <?= !empty($editingFabric['HinhAnhMacro']) ? htmlspecialchars((string) $editingFabric['HinhAnhMacro'], ENT_QUOTES, 'UTF-8') : 'linen_macroscopic_ivory.jpg (1.8MB)' ?></span>
          </div>
          <div class="flex items-center space-x-2">
            <button
              class="px-3.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-medium rounded-lg text-xs border border-slate-200"
              onclick="resetFabricForm()" type="button">Hủy</button>
            <button
              class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs shadow-sm"
              id="btn-submit-fabric"
              type="submit"><?= !empty($editingFabric) ? 'Cập Nhật Lên Web Store' : 'Đăng Lên Web Store' ?></button>
          </div>
        </div>
      </div>
    </form>
    <!-- Fabrics Table (CRUD Display - Có thanh cuộn mượt với Header cố định khi nhiều mẫu vải) -->
    <div class="overflow-x-auto max-h-[380px] overflow-y-auto rounded-lg border border-slate-200 shadow-sm">
      <table class="w-full text-left text-xs" data-purpose="fabric-list-table">
        <thead
          class="bg-slate-50 sticky top-0 z-10 uppercase text-[10px] text-slate-500 font-semibold border-b border-slate-200 tracking-wider">
          <tr>
            <th class="py-3 px-3.5 whitespace-nowrap">Mã SKU</th>
            <th class="py-3 px-3.5">Tên Chất Liệu &amp; Họa Tiết</th>
            <th class="py-3 px-3.5 whitespace-nowrap">Thông Số Kỹ Thuật</th>
            <th class="py-3 px-3.5 whitespace-nowrap">Màu Sắc</th>
            <th class="py-3 px-3.5 whitespace-nowrap">Giá Bán / Mét</th>
            <th class="py-3 px-3.5 whitespace-nowrap">Tồn Kho (Mét)</th>
            <th class="py-3 px-3.5 whitespace-nowrap min-w-[130px]">Trạng Thái</th>
            <th class="py-3 px-3.5 text-right whitespace-nowrap">Thao Tác</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
          <?php if (!empty($fabrics)): ?>
            <?php foreach ($fabrics as $fabric): ?>
              <?php
              $tonKho = (float) ($fabric['SoLuongTon'] ?? 0);
              $trangThaiWeb = (string) ($fabric['TrangThaiWeb'] ?? 'Đang bán Web');
              $maMauHex = (string) ($fabric['MaMauHex'] ?: '#e8dec8');
              $badgeClass = (string) ($fabric['BadgeMauClass'] ?: 'bg-amber-50 border-amber-200 text-amber-700');
              $iconClass = (string) ($fabric['IconClass'] ?: 'fa-solid fa-rug');
              $dotBorderClass = strtolower($maMauHex) === '#334155' ? 'border-slate-400' : 'border-slate-300';
              $jsonFabric = htmlspecialchars(json_encode($fabric, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
              $fabricQuickStatus = ($trangThaiWeb === 'Đang bán Web' && $tonKho >= 20) ? 'active' : 'low';
              ?>
              <tr class="fabric-row-item hover:bg-slate-50/70 transition" data-fabric-status="<?= $fabricQuickStatus ?>">
                <td class="py-3 px-3.5 font-mono font-bold text-slate-900 whitespace-nowrap">
                  <?= htmlspecialchars((string) $fabric['MaSKU'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td class="py-3 px-3.5 font-medium">
                  <div class="flex items-center space-x-2.5">
                    <span
                      class="w-6 h-6 rounded-md border flex-shrink-0 flex items-center justify-center text-[10px] <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>"><i
                        class="<?= htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                    <span
                      class="font-medium text-slate-900"><?= htmlspecialchars((string) $fabric['TenSP'], ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                </td>
                <td class="py-3 px-3.5 text-slate-500 whitespace-nowrap">
                  <?= htmlspecialchars((string) $fabric['KhoVai'], ENT_QUOTES, 'UTF-8') ?> •
                  <?= htmlspecialchars((string) $fabric['DinhLuongGSM'], ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td class="py-3 px-3.5 whitespace-nowrap">
                  <span
                    class="inline-flex items-center whitespace-nowrap space-x-1.5 px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200 text-[11px] text-slate-700">
                    <span class="w-2.5 h-2.5 rounded-full inline-block flex-shrink-0 border <?= $dotBorderClass ?>"
                      style="background-color: <?= htmlspecialchars($maMauHex, ENT_QUOTES, 'UTF-8') ?>;"></span>
                    <span><?= htmlspecialchars((string) $fabric['MauSac'], ENT_QUOTES, 'UTF-8') ?></span>
                  </span>
                </td>
                <td class="py-3 px-3.5 font-semibold text-slate-900 whitespace-nowrap">
                  <?= number_format((float) $fabric['DonGia'], 0, ',', '.') ?>₫
                </td>
                <?php if ($tonKho <= 0): ?>
                  <td class="py-3 px-3.5 font-mono text-rose-600 font-medium whitespace-nowrap">0.0 m</td>
                <?php elseif ($tonKho < 20): ?>
                  <td class="py-3 px-3.5 font-mono text-amber-700 font-medium whitespace-nowrap"><?= number_format($tonKho, 1, '.', '') ?> m
                    <span class="text-[10px] text-amber-600">(Sắp hết)</span>
                  </td>
                <?php else: ?>
                  <td class="py-3 px-3.5 font-mono text-emerald-700 font-medium whitespace-nowrap"><?= number_format($tonKho, 1, '.', '') ?> m
                  </td>
                <?php endif; ?>
                <td class="py-3 px-3.5 whitespace-nowrap min-w-[130px]">
                  <?php if ($trangThaiWeb === 'Đang bán Web' && $tonKho > 0): ?>
                    <span
                      class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Đang bán Web</span>
                  <?php else: ?>
                    <span
                      class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200"><?= htmlspecialchars($trangThaiWeb !== 'Đang bán Web' ? $trangThaiWeb : 'Tạm Hết Hàng', ENT_QUOTES, 'UTF-8') ?></span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-3.5 text-right space-x-1 whitespace-nowrap">
                  <button class="p-1.5 hover:bg-slate-100 rounded text-slate-500 hover:text-slate-800"
                    onclick="alert('Xem trước mẫu vải trên Storefront: <?= htmlspecialchars((string) $fabric['TenSP'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $fabric['MaSKU'], ENT_QUOTES, 'UTF-8') ?>)');"
                    title="Xem trước Web Store" type="button"><i
                      class="fa-solid fa-arrow-up-right-from-square"></i></button>
                  <button class="p-1.5 hover:bg-slate-100 rounded text-slate-700 hover:text-slate-900"
                    onclick='populateEditFabric(<?= $jsonFabric ?>)' title="Chỉnh sửa" type="button"><i
                      class="fa-solid fa-pen"></i></button>
                  <a class="inline-block p-1.5 hover:bg-rose-50 rounded text-rose-500 hover:text-rose-700"
                    href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=deleteFabric&amp;id=<?= (int) $fabric['MaBienThe'] ?>"
                    onclick="return confirm('Bạn có chắc chắn muốn xóa mẫu vải <?= htmlspecialchars((string) $fabric['MaSKU'], ENT_QUOTES, 'UTF-8') ?> khỏi hệ thống?');"
                    title="Xóa mẫu vải"><i class="fa-solid fa-trash"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td class="py-6 px-3.5 text-center text-slate-400" colspan="8">Chưa có mẫu vải nào trong danh mục Web Store.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
  <!-- END: ProductFabricCRUD -->
  <!-- ========================================================================= -->
  <!-- SECTION C & D: VOUCHER & MODERATION (2-COLUMN GRID) -->
  <!-- ========================================================================= -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- BEGIN: VoucherManagement -->
    <!-- SECTION C: 3. QUẢN LÝ VOUCHER & KHUYẾN MÃI -->
    <section class="bg-white border border-slate-200 rounded-xl p-5 space-y-4 shadow-sm flex flex-col"
      data-purpose="voucher-management" id="voucher-khuyen-mai">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div>
          <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">3. Quản lý Voucher &amp; Khuyến Mãi
            </h3>
          </div>
          <p class="text-[11px] text-slate-500 mt-0.5">Tạo mã giảm giá theo %, định mức tiền và giới hạn lượt sử dụng.
          </p>
        </div>
        <span class="text-xs bg-amber-50 text-amber-800 px-2.5 py-1 rounded-full font-medium border border-amber-200">
          <i class="fa-solid fa-tags mr-1"></i> <?= count($vouchers ?? []) ?> Mã đang chạy
        </span>
      </div>
      <!-- Quick Voucher Creator Box (Có ô chọn ngày Hết hạn trực tiếp) -->
      <form action="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=storeVoucher"
        class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200 space-y-3" method="POST">
        <span class="text-xs font-semibold text-slate-800 block">Tạo mã giảm giá mới:</span>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
          <div>
            <label class="block text-[10px] font-medium text-slate-500 mb-0.5">Mã Code</label>
            <input
              class="w-full uppercase font-mono bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 shadow-sm focus:border-slate-900 focus:ring-0"
              name="ma_code" placeholder="VD: FABRIC20" required="" type="text" />
          </div>
          <div>
            <label class="block text-[10px] font-medium text-slate-500 mb-0.5">Mức giảm (% hoặc VNĐ)</label>
            <div class="flex">
              <input
                class="w-full bg-white border border-slate-200 rounded-l-lg px-2.5 py-1.5 text-xs text-slate-900 shadow-sm focus:border-slate-900 focus:ring-0"
                min="1" name="gia_tri" placeholder="20" required="" step="any" type="number" />
              <select aria-label="Loại giảm giá"
                class="bg-slate-100 border border-l-0 border-slate-200 px-2 py-1.5 text-xs text-slate-700 rounded-r-lg font-medium focus:ring-0 focus:border-slate-900"
                name="loai_giam">
                <option value="%">%</option>
                <option value="VNĐ">₫</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-[10px] font-medium text-slate-500 mb-0.5">Giới hạn số lượt</label>
            <input
              class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs text-slate-900 shadow-sm focus:border-slate-900 focus:ring-0"
              min="1" name="gioi_han_luot" placeholder="100 lượt" required="" type="number" value="100" />
          </div>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
          <div class="text-[11px] text-slate-600 flex items-center gap-2">
            <label class="font-medium text-slate-500 flex items-center gap-1" for="input-voucher-expiry">
              <i class="fa-regular fa-clock text-slate-400"></i>
              <span>Hạn dùng:</span>
            </label>
            <input
              class="bg-white border border-slate-200 rounded-lg px-2.5 py-1 text-xs text-slate-800 shadow-sm focus:border-slate-900 focus:ring-0 cursor-pointer"
              id="input-voucher-expiry" name="han_su_dung" type="date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" />
          </div>
          <button
            class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition shadow-sm"
            type="submit">
            + Lưu &amp; Kích Hoạt Mã
          </button>
        </div>
      </form>
      <!-- Active Vouchers List (Có thanh cuộn dọc khi nhiều Voucher) -->
      <div class="space-y-2.5">
        <span class="text-xs font-semibold text-slate-500 block">Danh sách mã đang kích hoạt
          (<?= count($vouchers ?? []) ?>):</span>
        <div class="space-y-2.5 max-h-[340px] overflow-y-auto pr-1">
          <?php if (!empty($vouchers)): ?>
            <?php foreach ($vouchers as $voucher): ?>
              <?php
              $gioiHan = max(1, (int) ($voucher['GioiHanLuot'] ?? 100));
              $daDung = (int) ($voucher['SoLuotDaDung'] ?? 0);
              $percent = min(100, round(($daDung / $gioiHan) * 100, 1));
              $isPercentType = ((string) ($voucher['LoaiGiam'] ?? '%')) === '%';
              $codeBadgeClass = $isPercentType
                ? 'bg-amber-50 text-amber-800 border-amber-200'
                : 'bg-emerald-50 text-emerald-800 border-emerald-200';
              $counterTextClass = $isPercentType ? 'text-amber-700' : 'text-emerald-700';
              $barColorClass = $isPercentType ? 'bg-amber-500' : 'bg-emerald-500';
              ?>
              <div
                class="voucher-card-item p-3.5 rounded-xl bg-white border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm hover:border-slate-300 transition">
                <div class="space-y-1">
                  <div class="flex items-center space-x-2">
                    <span
                      class="px-2 py-0.5 font-mono font-bold text-xs rounded border <?= $codeBadgeClass ?>"><?= htmlspecialchars((string) $voucher['MaCode'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span
                      class="text-xs font-semibold text-slate-900"><?= htmlspecialchars((string) $voucher['TenChuongTrinh'], ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                  <div class="text-[11px] text-slate-500">
                    <?= htmlspecialchars((string) $voucher['MoTaDieuKien'], ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </div>
                <!-- Usage Progress -->
                <div class="w-full sm:w-44 space-y-1">
                  <div class="flex justify-between text-[11px]">
                    <span class="text-slate-500">Đã dùng:</span>
                    <span class="font-mono <?= $counterTextClass ?> font-semibold"><?= $daDung ?> / <?= $gioiHan ?></span>
                  </div>
                  <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div class="<?= $barColorClass ?> h-1.5 rounded-full" style="width: <?= $percent ?>%"></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <!-- END: VoucherManagement -->
    <!-- BEGIN: ReviewModeration -->
    <!-- SECTION D: 4. DUYỆT ĐÁNH GIÁ & KIỂM DUYỆT BÌNH LUẬN -->
    <section class="bg-white border border-slate-200 rounded-xl p-5 space-y-4 shadow-sm flex flex-col"
      data-purpose="review-moderation" id="kiem-duyet-danh-gia">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div>
          <div class="flex items-center space-x-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">4. Duyệt Đánh Giá &amp; Kiểm Duyệt Bình
              Luận</h3>
          </div>
          <p class="text-[11px] text-slate-500 mt-0.5">Phát hiện từ ngữ thô tục, spam link và hình ảnh rác trên trang
            sản phẩm.</p>
        </div>
        <div class="flex items-center space-x-1.5 text-xs">
          <?php if ((int) ($reviewCounts['spam'] ?? 0) > 0): ?>
            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
            <span class="text-rose-600 font-semibold"><?= (int) $reviewCounts['spam'] ?> Vi phạm cần gỡ</span>
          <?php else: ?>
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-emerald-600 font-semibold">0 Vi phạm</span>
          <?php endif; ?>
        </div>
      </div>
      <!-- Filter Pill Tabs (Hiển thị số đếm chính xác 100% từ CSDL) -->
      <div class="flex items-center space-x-2 text-xs">
        <a class="px-2.5 py-1 rounded-lg <?= $currentReviewFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?> font-medium transition"
          href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?review_filter=all#kiem-duyet-danh-gia">Tất cả (<?= (int) ($reviewCounts['all'] ?? 0) ?>)</a>
        <a class="px-2.5 py-1 rounded-lg <?= $currentReviewFilter === 'spam' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100' ?> font-medium flex items-center gap-1 transition"
          href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?review_filter=spam#kiem-duyet-danh-gia">
          <i
            class="fa-solid fa-triangle-exclamation <?= $currentReviewFilter === 'spam' ? 'text-white' : 'text-rose-500' ?>"></i>
          Nghi vấn Spam (<?= (int) ($reviewCounts['spam'] ?? 0) ?>)
        </a>
        <a class="px-2.5 py-1 rounded-lg <?= $currentReviewFilter === 'with_photos' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?> font-medium transition"
          href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?review_filter=with_photos#kiem-duyet-danh-gia">Có ảnh thật
          (<?= (int) ($reviewCounts['with_photos'] ?? 0) ?>)</a>
      </div>
      <!-- Moderation Cards Stream (Có thanh cuộn dọc mượt khi nhiều đánh giá) -->
      <div class="space-y-3 max-h-[430px] overflow-y-auto pr-1">
        <?php if (!empty($reviews)): ?>
          <?php foreach ($reviews as $review): ?>
            <?php
            $loaiDanhGia = (string) ($review['LoaiDanhGia'] ?? '');
            $trangThaiDuyet = (string) ($review['TrangThaiDuyet'] ?? 'Chờ duyệt');
            $isSpamCard = stripos($loaiDanhGia, 'Spam') !== false || $trangThaiDuyet === 'Vi phạm' || (int) $review['SoSao'] <= 2;
            $soSao = max(1, min(5, (int) $review['SoSao']));
            $soAnh = (int) ($review['SoAnhDinhKem'] ?? 0);
            $isGhim = (int) ($review['IsGhim'] ?? 0) === 1;
            $tenSPHienThi = (string) ($review['TenSP'] ?? '');
            if ($isSpamCard && stripos($tenSPHienThi, 'Lụa Tơ Tằm Thượng Hạng') !== false) {
              $tenSPHienThi = 'Lụa Tơ Tằm Thượng Hạng';
            }
            ?>
            <?php if ($isSpamCard): ?>
              <!-- Review Card: Flagged for Spam/Offensive -->
              <div class="review-card-item p-3.5 rounded-xl bg-rose-50/50 border border-rose-200 space-y-2.5">
                <div class="flex items-start justify-between">
                  <div>
                    <div class="flex items-center space-x-2">
                      <span
                        class="text-xs font-bold text-slate-900"><?= htmlspecialchars((string) $review['TenKH'], ENT_QUOTES, 'UTF-8') ?><?= !empty($review['IPKhach']) ? ' (IP ' . htmlspecialchars((string) $review['IPKhach'], ENT_QUOTES, 'UTF-8') . ')' : '' ?></span>
                      <span
                        class="px-1.5 py-0.5 rounded text-[10px] bg-rose-100 text-rose-700 font-bold uppercase border border-rose-200"><?= htmlspecialchars($loaiDanhGia ?: 'Spam Link / Thô tục', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Sản phẩm: <span
                        class="text-slate-800 font-medium"><?= htmlspecialchars($tenSPHienThi, ENT_QUOTES, 'UTF-8') ?></span>
                      • Đánh giá <?= $soSao ?> sao</p>
                  </div>
                  <div class="text-[10px] text-slate-400"><?= date('d/m/Y', strtotime((string) $review['NgayDanhGia'])) ?>
                  </div>
                </div>
                <div class="p-2.5 bg-white rounded-lg border border-rose-200 text-xs text-rose-800 font-medium">
                  "<?= htmlspecialchars((string) $review['NoiDung'], ENT_QUOTES, 'UTF-8') ?>"
                </div>
                <!-- Action Control Group -->
                <div class="flex items-center justify-between pt-1">
                  <span class="text-[11px] text-rose-600 font-medium"><i class="fa-solid fa-ban mr-1"></i> AI đánh dấu vi phạm
                    98%</span>
                  <div class="flex items-center space-x-2 text-xs">
                    <a class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-medium transition shadow-sm"
                      href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=moderateReview&amp;id=<?= (int) $review['MaDanhGia'] ?>&amp;mod_action=delete"
                      onclick="return confirm('Xóa vĩnh viễn đánh giá vi phạm này khỏi cơ sở dữ liệu?');"
                      title="Xóa vĩnh viễn khỏi cơ sở dữ liệu">
                      <i class="fa-solid fa-trash mr-1"></i> Xóa vĩnh viễn
                    </a>
                    <a class="px-3 py-1 bg-white hover:bg-slate-50 text-slate-700 rounded-lg font-medium border border-slate-200 shadow-sm"
                      href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=moderateReview&amp;id=<?= (int) $review['MaDanhGia'] ?>&amp;mod_action=hide"
                      title="Ẩn hiển thị trên Web">
                      <i class="fa-solid fa-eye-slash mr-1 text-slate-400"></i> Ẩn đánh giá
                    </a>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <!-- Review Card: Valid Customer Review with Photos -->
              <div class="review-card-item p-3.5 rounded-xl bg-white border border-slate-200 space-y-2.5 shadow-sm">
                <div class="flex items-start justify-between">
                  <div>
                    <div class="flex items-center space-x-2">
                      <span
                        class="text-xs font-bold text-slate-900"><?= htmlspecialchars((string) $review['TenKH'], ENT_QUOTES, 'UTF-8') ?></span>
                      <span
                        class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-700 font-medium border border-emerald-200"><?= htmlspecialchars($loaiDanhGia ?: 'Đã mua hàng thật', ENT_QUOTES, 'UTF-8') ?></span>
                      <?php if ($isGhim): ?>
                        <span
                          class="px-1.5 py-0.5 rounded text-[10px] bg-amber-50 text-amber-700 font-medium border border-amber-200"><i
                            class="fa-solid fa-thumbtack mr-0.5"></i> Đã ghim</span>
                      <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-1 text-amber-400 text-xs mt-0.5">
                      <?php for ($i = 0; $i < $soSao; $i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
                      <span
                        class="text-[11px] text-slate-400 ml-1"><?= htmlspecialchars($tenSPHienThi, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                  </div>
                  <div class="text-[10px] text-slate-400"><?= date('d/m/Y', strtotime((string) $review['NgayDanhGia'])) ?>
                  </div>
                </div>
                <p class="text-xs text-slate-600">
                  "<?= htmlspecialchars((string) $review['NoiDung'], ENT_QUOTES, 'UTF-8') ?>"
                </p>
                <?php if ($soAnh > 0): ?>
                  <!-- Image attachment tags -->
                  <div class="flex items-center space-x-2 pt-0.5">
                    <?php for ($imgIdx = 0; $imgIdx < $soAnh; $imgIdx++): ?>
                      <div
                        class="w-11 h-11 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-xs text-slate-400 hover:border-slate-400 cursor-pointer">
                        <i class="fa-solid fa-image"></i>
                      </div>
                    <?php endfor; ?>
                    <span class="text-[11px] text-slate-400">(<?= $soAnh ?> ảnh sạch, không chứa rác)</span>
                  </div>
                <?php endif; ?>
                <!-- Action Control Group (Matching "Duyệt" pill action button from inspiration) -->
                <div class="flex items-center justify-end space-x-2 pt-1 border-t border-slate-100">
                  <a class="px-3.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-full text-xs font-medium transition shadow-sm flex items-center gap-1.5"
                    href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=moderateReview&amp;id=<?= (int) $review['MaDanhGia'] ?>&amp;mod_action=approve">
                    <i class="fa-solid fa-check text-emerald-400 text-[10px]"></i>
                    <?= $trangThaiDuyet === 'Đã duyệt' ? 'Đã Duyệt Hiển Thị' : 'Duyệt Hiển Thị' ?>
                  </a>
                  <a class="px-3 py-1 bg-white hover:bg-slate-50 text-slate-600 rounded-lg text-xs border border-slate-200 font-medium"
                    href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>?action=moderateReview&amp;id=<?= (int) $review['MaDanhGia'] ?>&amp;mod_action=pin">
                    <?= $isGhim ? 'Bỏ Ghim' : 'Ghim Lên Đầu' ?>
                  </a>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
            Không có đánh giá nào trong danh mục lọc này.
          </div>
        <?php endif; ?>
      </div>
    </section>
    <!-- END: ReviewModeration -->
    <!-- SECTION 5: DUYỆT ĐĂNG KÝ THỢ MAY (AFFILIATE PARTNERS) -->
    <section class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4 shadow-sm" id="section-affiliate-requests">
      <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div class="flex items-center space-x-2.5">
          <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
          <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">5. Duyệt Đăng Ký Thợ May (Affiliate Network)</h3>
          <span id="badgeAffCount" class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">0 Đơn chờ</span>
        </div>
        <button onclick="loadAffiliateRequests()" class="px-3 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-1.5">
          <i class="fa-solid fa-arrows-rotate text-[11px]"></i> Làm mới
        </button>
      </div>

      <!-- Bảng danh sách đơn chờ duyệt -->
      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[11px] border-b border-slate-200">
            <tr>
              <th class="px-4 py-3">Mã ĐK</th>
              <th class="px-4 py-3">Họ Tên Thợ May</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Số Điện Thoại</th>
              <th class="px-4 py-3">Ngày Đăng Ký</th>
              <th class="px-4 py-3">Trạng Thái</th>
              <th class="px-4 py-3 text-right">Thao Tác</th>
            </tr>
          </thead>
          <tbody id="affiliateTableBody" class="divide-y divide-slate-100">
            <tr>
              <td colspan="7" class="px-4 py-6 text-center text-slate-400">
                <i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang tải danh sách đăng ký thợ may...
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <script>
      (() => {
        const box = document.getElementById('adminOnlineOrders');
        const filter = document.getElementById('adminOrderFilter');
        if (!box || !filter) return;
        let onlineOrders = [];
        const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const money = value => new Intl.NumberFormat('vi-VN').format(Math.round(Number(value) || 0)) + 'đ';
        const statusNames = {cho_thanh_toan:'Chờ thanh toán',dang_xu_ly:'Đơn COD mới',da_thanh_toan:'Đã thanh toán',dang_giao:'Đang giao',da_giao:'Đã giao',da_huy:'Đã hủy'};
        const statusClasses = {cho_thanh_toan:'bg-amber-50 text-amber-700 border-amber-200',dang_xu_ly:'bg-blue-50 text-blue-700 border-blue-200',da_thanh_toan:'bg-indigo-50 text-indigo-700 border-indigo-200',dang_giao:'bg-violet-50 text-violet-700 border-violet-200',da_giao:'bg-emerald-50 text-emerald-700 border-emerald-200',da_huy:'bg-rose-50 text-rose-700 border-rose-200'};
        const shipping = {economy:['Tiết kiệm',6],standard:['Tiêu chuẩn',4],express:['Hỏa tốc',2]};
        function headers(json = false) {
          const token = localStorage.getItem('aura_token');
          return {...(json ? {'Content-Type':'application/json'} : {}),Accept:'application/json',...(token ? {Authorization:'Bearer '+token} : {})};
        }
        function render() {
          const selected = filter.value;
          const visible = onlineOrders.filter(order => selected === 'all' || order.TrangThai === selected);
          if (!visible.length) {
            box.innerHTML = '<div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 p-8 text-center text-xs text-slate-500">Không có đơn hàng ở trạng thái này.</div>';
            return;
          }
          box.innerHTML = visible.map(order => {
            const status = order.TrangThai || '';
            const carrier = shipping[order.MaVanChuyen] || shipping.standard;
            const items = (order.items || []).map(item => `${esc(item.TenSP)} · ${Number(item.SoMet)||0}m`).join(' / ');
            let action = '';
            if (['da_thanh_toan','dang_xu_ly'].includes(status)) action = `<button data-order-action="start" data-code="${esc(order.MaDon)}" class="rounded-lg bg-indigo-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-indigo-700"><i class="fa-solid fa-truck-fast mr-1"></i>Bắt đầu giao</button>`;
            if (status === 'dang_giao') action = `<button data-order-action="delivered" data-code="${esc(order.MaDon)}" class="rounded-lg bg-emerald-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-emerald-700"><i class="fa-solid fa-circle-check mr-1"></i>Xác nhận giao thành công</button>`;
            let deadline = '';
            if (status === 'dang_giao' && order.NgayBatDauGiao) {
              const date = new Date(String(order.NgayBatDauGiao).replace(' ','T'));
              date.setDate(date.getDate() + carrier[1]);
              deadline = ` · Tự hoàn tất sau ${carrier[1]} ngày (dự kiến ${date.toLocaleDateString('vi-VN')})`;
            }
            const actorNames = {admin:'Admin',khach_hang:'Khách hàng',tu_dong:'Hệ thống tự động'};
            const record = status === 'da_giao'
              ? ` · Ghi nhận giao thành công ${esc(order.NgayGiaoThanhCong || '')} (${actorNames[order.XacNhanGiaoBoi] || 'đã ghi nhận'})`
              : status === 'da_huy' ? ` · Đơn hủy ${esc(order.NgayCapNhat || '')}` : '';
            return `<article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
              <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0 flex-1">
                  <div class="flex flex-wrap items-center gap-2"><strong class="font-mono text-sm text-slate-900">#${esc(order.MaDon)}</strong><span class="rounded-full border px-2.5 py-1 text-[10px] font-bold ${statusClasses[status] || 'bg-slate-100 text-slate-600 border-slate-200'}">${statusNames[status] || esc(status)}</span><span class="text-[10px] text-slate-400">${esc(order.NgayTao || '')}</span></div>
                  <div class="mt-2 text-xs font-semibold text-slate-800">${esc(order.TenKH)} · ${esc(order.SDT)}</div>
                  <div class="mt-1 truncate text-[11px] text-slate-500" title="${esc(order.DiaChi)}">${esc(order.DiaChi)}</div>
                  <div class="mt-1 text-[11px] text-slate-500">${items || 'Không có dòng sản phẩm'} · ${esc(carrier[0])} (${money(order.PhiVanChuyen)})${deadline}${record}</div>
                </div>
                <div class="flex items-center justify-between gap-3 lg:justify-end"><strong class="whitespace-nowrap text-sm text-slate-900">${money(order.TongThanhToan)}</strong>${action}</div>
              </div>
            </article>`;
          }).join('');
        }
        async function load() {
          box.innerHTML = '<div class="rounded-lg bg-slate-50 p-6 text-center text-xs text-slate-500">Đang tải và cập nhật thời hạn giao…</div>';
          try {
            const response = await fetch('/api/admin/don-hang-online', {credentials:'same-origin',headers:headers()});
            const result = await response.json();
            if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Không tải được đơn hàng.');
            onlineOrders = result.data.orders || [];
            render();
          } catch (error) {
            box.innerHTML = `<div class="rounded-lg border border-rose-200 bg-rose-50 p-5 text-center text-xs text-rose-700">${esc(error.message)}<br><span class="mt-1 inline-block text-[10px]">Vui lòng đăng nhập tài khoản Admin rồi tải lại trang.</span></div>`;
          }
        }
        box.addEventListener('click', async event => {
          const button = event.target.closest('[data-order-action]');
          if (!button) return;
          const start = button.dataset.orderAction === 'start';
          const label = start ? 'bắt đầu giao' : 'xác nhận giao thành công';
          if (!window.confirm(`Bạn muốn ${label} đơn ${button.dataset.code}?`)) return;
          button.disabled = true;
          const action = start ? 'bat-dau-giao' : 'xac-nhan-giao';
          try {
            const response = await fetch(`/api/admin/don-hang-online/${encodeURIComponent(button.dataset.code)}/${action}`, {method:'POST',credentials:'same-origin',headers:headers(true),body:'{}'});
            const result = await response.json();
            if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Không cập nhật được đơn.');
            await load();
          } catch (error) { window.alert(error.message); button.disabled = false; }
        });
        filter.addEventListener('change', render);
        document.getElementById('refreshOnlineOrders')?.addEventListener('click', load);
        load();
        window.setInterval(load, 60000);
      })();

      async function loadAffiliateRequests() {
        const tbody = document.getElementById('affiliateTableBody');
        const badge = document.getElementById('badgeAffCount');
        try {
          const res = await fetch('/api/admin/affiliate-requests?trangThai=Chờ duyệt');
          const result = await res.json();
          if (!res.ok || result.status !== 'success') {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-4 text-center text-rose-500">Lỗi: ${result.message || 'Không thể tải danh sách'}</td></tr>`;
            return;
          }

          const data = result.data || [];
          badge.textContent = `${data.length} Đơn chờ`;

          if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-6 text-center text-slate-400"><i class="fa-regular fa-circle-check text-emerald-500 text-base mr-1"></i> Không có đơn đăng ký thợ may nào đang chờ duyệt.</td></tr>`;
            return;
          }

          tbody.innerHTML = data.map(item => `
            <tr class="hover:bg-slate-50 transition">
              <td class="px-4 py-3 font-mono font-bold text-slate-900">#AFF-${item.MaDKAff}</td>
              <td class="px-4 py-3 font-bold text-slate-900">${escapeHtml(item.TenKH)}</td>
              <td class="px-4 py-3 font-mono text-indigo-600">${escapeHtml(item.Email)}</td>
              <td class="px-4 py-3 font-mono">${escapeHtml(item.SDT || '—')}</td>
              <td class="px-4 py-3 text-slate-500">${item.NgayDangKy ? item.NgayDangKy.substring(0, 16) : '—'}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Chờ duyệt</span>
              </td>
              <td class="px-4 py-3 text-right space-x-1.5 whitespace-nowrap">
                <button onclick="approveAffiliate(${item.MaDKAff}, '${escapeHtml(item.TenKH)}')" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition inline-flex items-center gap-1">
                  <i class="fa-solid fa-check"></i> Duyệt
                </button>
                <button onclick="rejectAffiliate(${item.MaDKAff}, '${escapeHtml(item.TenKH)}')" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 transition inline-flex items-center gap-1">
                  <i class="fa-solid fa-xmark"></i> Từ chối
                </button>
              </td>
            </tr>
          `).join('');
        } catch (e) {
          tbody.innerHTML = `<tr><td colspan="7" class="px-4 py-4 text-center text-rose-500">Lỗi kết nối máy chủ khi tải thợ may.</td></tr>`;
        }
      }

      async function approveAffiliate(id, name) {
        if (!confirm(`Xác nhận DUYỆT cho thợ may: "${name}"?\nSau khi duyệt, tài khoản này sẽ chính thức trở thành Affiliate và có thể đăng nhập vào Cổng Thợ May.`)) return;

        try {
          const res = await fetch(`/api/admin/affiliate-requests/approve?id=${id}`, { method: 'POST' });
          const result = await res.json();
          if (res.ok && result.status === 'success') {
            alert(`🎉 Đã duyệt thành công cho thợ may: ${name}!`);
            loadAffiliateRequests();
          } else {
            alert('Lỗi: ' + (result.message || 'Không thể duyệt'));
          }
        } catch (e) {
          alert('Lỗi kết nối máy chủ.');
        }
      }

      async function rejectAffiliate(id, name) {
        const reason = prompt(`Nhập lý do từ chối thợ may "${name}":`, 'Không đủ điều kiện đối tác');
        if (reason === null) return;

        try {
          const res = await fetch(`/api/admin/affiliate-requests/reject?id=${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ghiChu: reason })
          });
          const result = await res.json();
          if (res.ok && result.status === 'success') {
            alert(`Đã từ chối đơn của ${name}.`);
            loadAffiliateRequests();
          } else {
            alert('Lỗi: ' + (result.message || 'Không thể từ chối'));
          }
        } catch (e) {
          alert('Lỗi kết nối máy chủ.');
        }
      }

      function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, function(m) {
          return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
      }

      document.addEventListener('DOMContentLoaded', loadAffiliateRequests);
    </script>
  </div>
</main>
<!-- END: MainContentWorkspace -->
<?php require __DIR__ . '/../components/footer.php'; ?>
