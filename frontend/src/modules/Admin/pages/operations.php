<section class="space-y-5" data-purpose="lookbook-affiliate-admin" id="admin-overview">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <p class="text-xs font-bold tracking-wider text-indigo-600 uppercase">SYS.MONITOR • VẬN HÀNH THỜI GIAN THỰC</p>
      <h2 class="mt-1 text-xl font-bold tracking-tight text-slate-900">Trung tâm Điều hành &amp; Duyệt Nghiệp Vụ</h2>
      <p class="mt-1 text-xs text-slate-500">Lookbook đối tác, đơn hàng và hoa hồng thợ may.</p>
    </div>
    <a href="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>#admin-overview"
      class="inline-flex items-center gap-2 self-start rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
      <i class="fa-solid fa-rotate"></i> Làm mới
    </a>
  </div>

  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-semibold text-slate-500">DOANH THU ĐƠN HÀNG</p>
      <p class="mt-2 text-2xl font-extrabold text-slate-900"><?= htmlspecialchars((string) ($stats['doanh_thu_formatted'] ?? '0₫'), ENT_QUOTES, 'UTF-8') ?></p>
      <p class="mt-2 text-xs font-medium text-emerald-600"><i class="fa-solid fa-arrow-trend-up mr-1"></i>Thống kê từ dữ liệu đơn hàng</p>
    </article>
    <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
      <p class="text-xs font-semibold text-slate-500">ĐƠN HÀNG</p>
      <p class="mt-2 text-2xl font-extrabold text-slate-900"><?= (int) ($stats['don_hang_moi'] ?? 0) ?> <span class="text-sm font-medium text-slate-500">đơn</span></p>
      <p class="mt-2 text-xs font-medium text-indigo-600"><?= (int) ($stats['don_cho_duyet'] ?? 0) ?> đơn sẵn sàng xuất kho</p>
    </article>
    <a href="#bang-rut-tien" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-amber-300">
      <p class="text-xs font-semibold text-slate-500">YÊU CẦU RÚT TIỀN</p>
      <p class="mt-2 text-2xl font-extrabold text-slate-900"><?= count($pendingWithdrawals ?? []) ?> <span class="text-sm font-medium text-slate-500">yêu cầu</span></p>
      <p class="mt-2 text-xs font-medium text-amber-600">Bấm để xem và xử lý</p>
    </a>
    <a href="#bang-lookbook" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-rose-300">
      <p class="text-xs font-semibold text-slate-500">LOOKBOOK CHỜ DUYỆT</p>
      <p class="mt-2 text-2xl font-extrabold text-rose-600"><?= count($pendingLookbooks ?? []) ?> <span class="text-sm font-medium text-slate-500">bài đăng</span></p>
      <p class="mt-2 text-xs font-medium text-rose-600">Bấm để thẩm định bài đăng</p>
    </a>
  </div>

  <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" id="bang-lookbook">
    <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="font-bold text-slate-900"><i class="fa-solid fa-camera mr-2 text-indigo-600"></i>Duyệt bài Lookbook đối tác</h3>
        <p class="mt-1 text-xs text-slate-500">Thẩm định ảnh sản phẩm do thợ may gửi và mã vải bán chéo.</p>
      </div>
      <span class="w-fit rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-600"><?= count($pendingLookbooks ?? []) ?> bài chờ thẩm định</span>
    </div>
    <?php if (empty($pendingLookbooks)): ?>
      <div class="p-8 text-center text-sm text-slate-400"><i class="fa-solid fa-circle-check mb-2 block text-2xl text-emerald-500"></i>Hiện không có bài Lookbook nào chờ duyệt.</div>
    <?php else: ?>
      <div class="divide-y divide-slate-100">
        <?php foreach ($pendingLookbooks as $item): ?>
          <?php $lookbookImage = trim((string) ($item['URLHinhAnh'] ?? '')); ?>
          <article class="flex flex-col gap-4 p-4 transition-colors hover:bg-slate-50/70 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-4">
              <?php if ($lookbookImage !== ''): ?>
                <img src="<?= htmlspecialchars($lookbookImage, ENT_QUOTES, 'UTF-8') ?>" alt="Ảnh Lookbook" loading="lazy" class="h-20 w-16 shrink-0 rounded-lg border border-slate-200 object-cover" onerror="this.hidden=true">
              <?php else: ?>
                <div class="flex h-20 w-16 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-400"><i class="fa-regular fa-image"></i></div>
              <?php endif; ?>
              <div class="min-w-0">
                <h4 class="truncate text-sm font-bold text-slate-900"><?= htmlspecialchars((string) ($item['TenLookBook'] ?? 'Lookbook'), ENT_QUOTES, 'UTF-8') ?></h4>
                <p class="mt-1 text-xs text-slate-500">Bởi: <span class="font-semibold text-slate-700"><?= htmlspecialchars((string) (($item['TenTiemMay'] ?? '') ?: ($item['TenNV'] ?? 'Thợ may')), ENT_QUOTES, 'UTF-8') ?></span><?php if (!empty($item['SDT'])): ?> · <?= htmlspecialchars((string) $item['SDT'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                  <span class="rounded border border-indigo-100 bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700">Mã vải: <?= htmlspecialchars((string) (($item['MaVaiCode'] ?? '') ?: 'Chưa gắn mã'), ENT_QUOTES, 'UTF-8') ?></span>
                  <?php if (!empty($item['DinhLuongVai'])): ?><span class="text-xs text-slate-500"><?= htmlspecialchars((string) $item['DinhLuongVai'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                </div>
              </div>
            </div>
            <div class="flex shrink-0 items-center gap-2 self-end sm:self-center">
              <form method="post" action="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="approveLookbook"><input type="hidden" name="ma_lookbook" value="<?= (int) $item['MaLookBook'] ?>">
                <button type="submit" class="rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-700"><i class="fa-solid fa-check mr-1"></i>Duyệt bài</button>
              </form>
              <form method="post" action="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="rejectLookbook"><input type="hidden" name="ma_lookbook" value="<?= (int) $item['MaLookBook'] ?>">
                <button type="submit" class="rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">Từ chối</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" id="bang-rut-tien">
    <div class="flex flex-col gap-2 border-b border-slate-100 bg-slate-50/60 p-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h3 class="font-bold text-slate-900"><i class="fa-solid fa-wallet mr-2 text-indigo-600"></i>Duyệt lệnh rút tiền thợ may</h3>
        <p class="mt-1 text-xs text-slate-500">Xem thông tin nhận tiền và xử lý số dư hoa hồng.</p>
      </div>
      <span class="w-fit rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700"><?= count($pendingWithdrawals ?? []) ?> lệnh chờ duyệt</span>
    </div>
    <?php if (empty($pendingWithdrawals)): ?>
      <div class="p-8 text-center text-sm text-slate-400"><i class="fa-solid fa-circle-check mb-2 block text-2xl text-emerald-500"></i>Không có lệnh rút tiền nào đang chờ xử lý.</div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="border-b border-slate-100 bg-slate-50 text-slate-600"><tr><th class="p-3.5 pl-5">THỢ MAY / TÀI KHOẢN</th><th class="p-3.5">SỐ TIỀN</th><th class="p-3.5">HÌNH THỨC</th><th class="p-3.5 pr-5 text-right">THAO TÁC</th></tr></thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($pendingWithdrawals as $withdrawal): ?>
              <tr class="hover:bg-slate-50/70">
                <td class="p-3.5 pl-5"><div class="text-sm font-bold text-slate-900"><?= htmlspecialchars((string) (($withdrawal['TenTiemMay'] ?? '') ?: ($withdrawal['TenNV'] ?? 'Thợ may')), ENT_QUOTES, 'UTF-8') ?></div><div class="mt-1 text-[11px] text-slate-500"><?= htmlspecialchars((string) ($withdrawal['ThongTinNhan'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div><?php if (!empty($withdrawal['SoTaiKhoan'])): ?><div class="text-[11px] text-slate-400"><?= htmlspecialchars((string) ($withdrawal['NganHang'] ?? ''), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $withdrawal['SoTaiKhoan'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></td>
                <td class="whitespace-nowrap p-3.5"><span class="text-sm font-extrabold text-slate-900"><?= number_format((float) $withdrawal['SoTien'], 0, ',', '.') ?>₫</span><div class="mt-1 text-[11px] text-slate-500">Ví: <?= number_format((float) ($withdrawal['SoTienHoaHong'] ?? 0), 0, ',', '.') ?>₫</div></td>
                <td class="whitespace-nowrap p-3.5"><?php if (($withdrawal['HinhThuc'] ?? '') === 'MuaVaiAura'): ?><span class="rounded-full border border-purple-200 bg-purple-50 px-2.5 py-1 text-[11px] font-bold text-purple-700">Đổi voucher vải (+10%)</span><?php else: ?><span class="rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">Chuyển khoản ngân hàng</span><?php endif; ?></td>
                <td class="p-3.5 pr-5"><div class="flex justify-end gap-2">
                  <form method="post" action="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="approveWithdrawal"><input type="hidden" name="ma_yeu_cau" value="<?= (int) $withdrawal['MaYeuCau'] ?>"><button type="submit" class="whitespace-nowrap rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700"><i class="fa-solid fa-check mr-1 text-emerald-400"></i>Duyệt</button></form>
                  <form method="post" action="<?= htmlspecialchars($routerUrl, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="action" value="rejectWithdrawal"><input type="hidden" name="ma_yeu_cau" value="<?= (int) $withdrawal['MaYeuCau'] ?>"><button type="submit" class="whitespace-nowrap rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 hover:bg-rose-50 hover:text-rose-600">Từ chối</button></form>
                </div></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
</section>