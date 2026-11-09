<?php
$pageTitle = 'Đặt hàng thành công - Aura Fabrics';
$activeNav = 'success';
require __DIR__ . '/../components/head.php';
require __DIR__ . '/../components/header.php';

$orderCode = strtoupper(trim((string) ($_GET['code'] ?? '')));
if ($orderCode !== '' && !preg_match('/^AUR[0-9A-Z]{6}$/', $orderCode)) {
    $orderCode = '';
}
?>
<main class="w-full pt-20 bg-background max-w-5xl mx-auto px-gutter">
    <div class="flex flex-col w-full pb-16 gap-space-md">

        <!-- Success hero -->
        <section class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
            <div class="h-1.5 w-full bg-secondary"></div>
            <div class="p-space-xl flex flex-col items-center text-center gap-space-sm">
                <div class="w-20 h-20 rounded-full bg-secondary-fixed text-on-secondary-fixed flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-[46px]">check_circle</span>
                </div>
                <h1 class="font-display text-display text-primary tracking-tight">Đặt hàng thành công</h1>
                <p class="font-body-md text-body-md text-on-surface-variant max-w-xl">
                    Cảm ơn bạn đã tin chọn Aura Fabrics. Đơn vải của bạn đã được ghi nhận và đang chờ xử lý tại kho tổng.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2 mt-space-xs">
                    <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Mã đơn hàng</span>
                    <span id="successOrderCode" class="px-space-sm py-1 rounded-lg bg-surface-container font-mono font-bold text-on-surface">
                        <?= $orderCode !== '' ? htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') : '—' ?>
                    </span>
                    <button type="button" class="text-[11px] font-bold text-secondary" data-copy="successOrderCode">Chép</button>
                </div>
            </div>
        </section>

        <!-- Loading / error -->
        <div id="successLoading" class="bg-surface-container-lowest rounded-2xl shadow-sm p-space-lg flex items-center justify-center gap-2 text-on-surface-variant font-body-sm text-body-sm">
            <span class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
            Đang tải thông tin đơn hàng...
        </div>
        <div id="successError" class="hidden bg-error-container text-on-error-container rounded-2xl shadow-sm p-space-md flex items-start gap-2 font-body-sm text-body-sm">
            <span class="material-symbols-outlined text-[20px]">error</span>
            <span id="successErrorText">Không tải được thông tin đơn hàng.</span>
        </div>

        <!-- Order detail -->
        <div id="successContent" class="hidden flex flex-col gap-space-md">
            <section class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
                <div class="px-space-md py-space-sm border-b border-surface-container flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-headline-sm text-headline-sm text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary text-[20px]">receipt_long</span>
                        Chi tiết đơn hàng
                    </h2>
                    <span id="successStatus" class="px-space-sm py-1 rounded-full bg-amber-50 text-amber-700 font-label-sm text-label-sm border border-amber-200">
                        Đang xử lý
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-space-md p-space-md border-b border-surface-container">
                    <div class="flex flex-col gap-1">
                        <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Người nhận</span>
                        <strong id="successCustomerName" class="font-headline-sm text-headline-sm text-on-surface">—</strong>
                        <span id="successCustomerPhone" class="font-body-sm text-body-sm text-on-surface-variant">—</span>
                        <span id="successCustomerAddress" class="font-body-sm text-body-sm text-on-surface-variant leading-relaxed">—</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider">Thông tin đơn</span>
                        <div class="flex items-center justify-between font-body-sm text-body-sm text-on-surface-variant">
                            <span>Ngày tạo</span>
                            <strong id="successCreatedAt" class="text-on-surface">—</strong>
                        </div>
                        <div class="flex items-center justify-between font-body-sm text-body-sm text-on-surface-variant">
                            <span>Phương thức</span>
                            <strong id="successMethod" class="text-on-surface">VietQR</strong>
                        </div>
                        <div class="flex items-center justify-between font-body-sm text-body-sm text-on-surface-variant">
                            <span>Giao hàng</span>
                            <strong id="successShippingMethod" class="text-on-surface">Tiêu chuẩn</strong>
                        </div>
                        <div class="flex items-center justify-between font-body-sm text-body-sm text-on-surface-variant">
                            <span>Ghi chú</span>
                            <strong id="successNote" class="text-on-surface truncate max-w-[180px]">—</strong>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col divide-y divide-surface-container" id="successItems"></div>

                <div class="p-space-md flex flex-col items-end gap-1.5">
                    <div class="w-full max-w-sm flex flex-col gap-2 font-body-sm text-body-sm text-on-surface-variant">
                        <div class="flex items-center justify-between"><span>Tổng tiền hàng:</span><span id="successRawTotal" class="text-on-surface">0đ</span></div>
                        <div class="flex items-center justify-between"><span>Chiết khấu sỉ:</span><span id="successWholesaleDiscount" class="text-secondary font-semibold">-0đ</span></div>
                        <div class="flex items-center justify-between"><span>Voucher Aura:</span><span id="successVoucherDiscount" class="text-secondary font-semibold">-0đ</span></div>
                        <div class="flex items-center justify-between"><span>Phí vận chuyển:</span><span id="successShipping" class="text-on-surface">Miễn phí</span></div>
                        <div class="flex items-baseline justify-between pt-2 border-t border-surface-container">
                            <span class="font-headline-sm text-headline-sm text-on-surface">Tổng thanh toán:</span>
                            <span id="successFinalTotal" class="font-display text-display text-primary font-bold">0đ</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Bank / payment reminder -->
            <section id="successPaymentBox" class="bg-surface-container-lowest rounded-2xl shadow-sm p-space-md flex flex-col gap-space-sm">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-900 text-[22px]">account_balance</span>
                    <h2 class="font-headline-sm text-headline-sm text-on-surface">Thông tin chuyển khoản</h2>
                </div>
                <p id="successPaymentHint" class="font-body-sm text-body-sm text-on-surface-variant">
                    Đơn hàng đang chờ thanh toán. Vui lòng chuyển khoản đúng nội dung bên dưới để hệ thống xác nhận tự động.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-space-sm font-body-sm text-body-sm">
                    <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-surface-container-low">
                        <span class="text-outline">Ngân hàng</span>
                        <strong id="successBankName" class="text-on-surface text-right">—</strong>
                    </div>
                    <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-surface-container-low">
                        <span class="text-outline">Số tài khoản</span>
                        <span class="flex items-center gap-2">
                            <strong id="successBankAccount" class="text-on-surface font-mono">—</strong>
                            <button type="button" class="text-[11px] font-bold text-slate-900" data-copy="successBankAccount">Chép</button>
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-surface-container-low">
                        <span class="text-outline">Chủ tài khoản</span>
                        <strong id="successBankAccountName" class="text-on-surface text-right">—</strong>
                    </div>
                    <div class="flex items-center justify-between gap-2 p-3 rounded-lg bg-slate-100">
                        <span class="text-slate-900 font-semibold">Nội dung CK</span>
                        <span class="flex items-center gap-2">
                            <strong id="successTransferContent" class="text-slate-900 font-mono">—</strong>
                            <button type="button" class="text-[11px] font-bold text-slate-900" data-copy="successTransferContent">Chép</button>
                        </span>
                    </div>
                </div>
            </section>

            <!-- Actions -->
            <section class="flex flex-col sm:flex-row items-center justify-center gap-space-sm">
                <a href="/index.php?module=lookbook&tab=lookbook"
                   class="w-full sm:w-auto px-space-lg py-3 rounded-xl bg-primary text-on-primary font-label-lg text-label-lg font-bold text-center flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    Tiếp tục chọn vải
                </a>
                <a href="/index.php?module=don-hang"
                   class="w-full sm:w-auto px-space-lg py-3 rounded-xl bg-surface-container-lowest border border-surface-container-high text-on-surface font-label-lg text-label-lg font-bold text-center flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    Đơn hàng của tôi
                </a>
                <button type="button" id="successRefreshBtn"
                        class="w-full sm:w-auto px-space-lg py-3 rounded-xl bg-surface-container text-on-surface font-label-lg text-label-lg font-bold flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">refresh</span>
                    Kiểm tra thanh toán
                </button>
            </section>
        </div>
    </div>
</main>

<script>
    window.AURA_SUCCESS_ORDER_CODE = <?= json_encode($orderCode, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="/frontend/src/modules/Gio_hang%26Thanh_toan/api/success.js?v=1"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
