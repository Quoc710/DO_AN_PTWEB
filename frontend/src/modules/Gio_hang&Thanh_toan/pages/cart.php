<?php
$pageTitle = 'Giỏ hàng vải - Aura Fabrics';
$activeNav = 'cart';
require __DIR__ . '/../components/head.php';
require __DIR__ . '/../components/header.php';
?>
<main class="w-full pt-20 bg-background max-w-7xl mx-auto px-gutter">
    <div class="flex flex-col w-full pb-16">
        <!-- Freeship banner -->
        <section class="w-full mb-space-md">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-md flex flex-col md:flex-row items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-sm flex-1 w-full">
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-secondary-fixed text-on-secondary-fixed font-label-md text-label-md uppercase tracking-wider shrink-0">
                        <span class="material-symbols-outlined text-[16px]">local_shipping</span>
                        <span>Freeship Xtra</span>
                    </div>
                    <div class="flex-1 min-w-0 pr-space-md">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-label-lg text-label-lg text-on-surface truncate">
                                Đã chọn <strong class="text-secondary font-bold" id="shippingMeterText">0m</strong> / 15m để miễn phí bọc kiện
                            </span>
                            <span class="font-body-sm text-body-sm text-outline shrink-0 ml-2">Tiết kiệm 30.000đ</span>
                        </div>
                        <div class="w-full bg-surface-container h-2 rounded-full overflow-hidden">
                            <div class="bg-secondary h-full rounded-full transition-all duration-300" id="freeshipProgressBar" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
                <a class="font-label-md text-label-md text-secondary hover:underline flex items-center gap-0.5 shrink-0" href="/index.php?module=lookbook&tab=lookbook">
                    <span>Xem thêm ưu đãi kho</span>
                    <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                </a>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-lg items-start">
            <!-- LEFT: cart items -->
            <div class="lg:col-span-8 flex flex-col gap-space-md">
                <div class="bg-surface-container-lowest rounded-xl shadow-sm px-space-md py-3.5 flex items-center text-outline font-label-md text-label-md uppercase tracking-wider">
                    <div class="w-8 flex items-center justify-center">
                        <input class="w-4 h-4 rounded text-primary focus:ring-0 cursor-pointer accent-primary" id="selectAllCheckbox" type="checkbox">
                    </div>
                    <div class="flex-1 pl-2">
                        <label class="cursor-pointer font-label-md text-label-md text-on-surface" for="selectAllCheckbox">
                            Chọn tất cả (<span id="totalItemsCount">0</span> sản phẩm)
                        </label>
                    </div>
                    <div class="w-24 text-right pr-4 hidden sm:block">Đơn giá</div>
                    <div class="w-36 text-center">Số lượng (m)</div>
                    <div class="w-28 text-right pr-2">Số tiền</div>
                    <div class="w-12 text-center">Xóa</div>
                </div>

                <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                    <div class="px-space-md py-3 bg-surface-container-low flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <input class="w-4 h-4 rounded text-primary cursor-pointer accent-primary" id="selectShopCheckbox" type="checkbox">
                            <span class="px-2 py-0.5 rounded bg-primary text-on-primary font-label-sm text-label-sm uppercase font-bold tracking-wider">Mall</span>
                            <span class="font-label-lg text-label-lg text-on-surface">AURA Fabrics Kho Tổng - TP.HCM</span>
                            <span class="material-symbols-outlined text-outline text-[18px]">verified</span>
                        </div>
                        <span class="font-label-md text-label-md text-secondary hidden sm:flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">confirmation_number</span>
                            Áp dụng voucher ở khung bên phải
                        </span>
                    </div>

                    <div class="flex flex-col" id="cartItemsContainer"></div>

                    <div id="cartEmptyState" class="hidden p-space-xl text-center">
                        <span class="material-symbols-outlined text-[44px] text-outline">shopping_bag</span>
                        <p class="font-headline-sm text-headline-sm text-on-surface mt-2">Giỏ hàng đang trống</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant mt-1">Hãy chọn mét vải từ Lookbook để bắt đầu đơn may đo của bạn.</p>
                        <a href="/index.php?module=lookbook&tab=lookbook"
                           class="inline-flex mt-4 px-4 py-2 rounded-lg bg-primary text-on-primary font-label-md items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                            Tiếp tục chọn vải
                        </a>
                    </div>
                </div>

                <div class="bg-surface-container-lowest rounded-xl shadow-sm px-space-md py-3 flex flex-wrap items-center justify-between gap-space-sm">
                    <div class="flex items-center gap-space-md">
                        <button type="button" class="font-label-md text-label-md text-outline hover:text-error flex items-center gap-1 transition-colors" id="btnDeleteSelected">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                            <span>Xóa các mục đã chọn</span>
                        </button>
                        <a href="/index.php?module=lookbook&tab=lookbook" class="font-label-md text-label-md text-primary hover:text-secondary flex items-center gap-1 transition-colors">
                            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                            <span>Tiếp tục xem và chọn thêm vải</span>
                        </a>
                    </div>
                    <span class="font-label-sm text-label-sm text-outline flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">lock</span>
                        Giá đã bao gồm VAT
                    </span>
                </div>
            </div>

            <!-- RIGHT: summary -->
            <div class="lg:col-span-4 flex flex-col gap-space-md lg:sticky lg:top-24">
                <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-md flex flex-col gap-space-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 font-label-lg text-label-lg text-on-surface">
                            <span class="material-symbols-outlined text-secondary text-[20px]">local_offer</span>
                            <span>Aura Voucher &amp; Mã Giảm Giá</span>
                        </div>
                        <span id="voucherAppliedBadge" class="hidden px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-label-sm text-[10px] font-bold"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="voucherInput" type="text" autocomplete="off"
                               class="flex-1 min-w-0 px-3 py-2 rounded-lg border border-surface-container-high bg-white text-sm font-semibold uppercase tracking-wide focus:outline-none focus:border-slate-900"
                               placeholder="Nhập mã voucher">
                        <button type="button" id="btnApplyVoucher"
                                class="shrink-0 px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-label-sm text-[11px] font-bold uppercase transition-colors">
                            Áp dụng
                        </button>
                        <button type="button" id="btnRemoveVoucher"
                                class="hidden shrink-0 px-3 py-2 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-label-sm text-[11px] font-bold transition-colors">
                            Bỏ
                        </button>
                    </div>
                    <p id="voucherMessage" class="hidden"></p>
                    <div id="voucherList" class="flex flex-col gap-1.5">
                        <p class="font-body-sm text-[12px] text-outline">Đang tải voucher...</p>
                    </div>
                </div>

                <div class="bg-surface-container-lowest rounded-xl shadow-sm p-space-md flex flex-col gap-space-md">
                    <div class="flex items-center justify-between pb-space-sm border-b border-surface-container">
                        <h2 class="font-headline-sm text-headline-sm text-on-surface">Tóm Tắt Đơn Hàng</h2>
                        <span class="font-label-sm text-label-sm text-outline uppercase tracking-wider" id="selectedMetersSummary">0m · 0 mặt hàng</span>
                    </div>
                    <div class="flex flex-col gap-2.5 font-body-sm text-body-sm">
                        <div class="flex items-center justify-between text-on-surface-variant">
                            <span>Tổng tiền hàng (giá niêm yết):</span>
                            <span class="font-label-lg text-label-lg text-on-surface" id="summaryRawTotal">0đ</span>
                        </div>
                        <div class="flex items-center justify-between text-secondary">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">trending_down</span>
                                <span>Chiết khấu sỉ cuộn (&gt;15m):</span>
                            </span>
                            <span class="font-label-lg text-label-lg font-bold" id="summaryWholesaleDiscount">-0đ</span>
                        </div>
                        <div class="flex items-center justify-between text-secondary">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">loyalty</span>
                                <span>Voucher giảm giá Aura:</span>
                            </span>
                            <span class="font-label-lg text-label-lg font-bold" id="summaryVoucherDiscount">-0đ</span>
                        </div>
                        <div class="flex items-center justify-between text-on-surface-variant">
                            <span>Phí vận chuyển bọc kiện:</span>
                            <div class="text-right">
                                <span class="line-through text-outline font-label-sm text-label-sm mr-1">30.000đ</span>
                                <span class="text-secondary font-label-lg text-label-lg font-semibold">Miễn phí</span>
                            </div>
                        </div>
                    </div>
                    <div class="pt-space-sm border-t border-surface-container flex flex-col gap-1">
                        <div class="flex items-baseline justify-between">
                            <span class="font-headline-sm text-headline-sm text-on-surface uppercase tracking-tight">Tổng thanh toán:</span>
                            <div class="text-right">
                                <span class="font-display text-display text-primary font-bold tracking-tight" id="summaryFinalTotal">0đ</span>
                                <p class="font-label-sm text-label-sm text-outline">(Đã bao gồm VAT &amp; bảo hiểm kiện vải)</p>
                            </div>
                        </div>
                        <div class="mt-1 flex items-center justify-end gap-1 text-secondary font-label-md text-label-md">
                            <span class="material-symbols-outlined text-[16px]">savings</span>
                            <span>Tiết kiệm tổng cộng: <strong id="summaryTotalSavings">0đ</strong></span>
                        </div>
                    </div>
                    <button type="button" id="btnCheckout"
                            class="w-full py-3.5 px-space-md rounded-xl bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg uppercase tracking-wider font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 group cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="checkoutBtnLabel">MUA HÀNG (0 sản phẩm)</span>
                        <span class="material-symbols-outlined text-[20px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
                    <div class="flex items-center justify-between font-label-sm text-label-sm text-outline">
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">lock</span> SSL 256-bit</span>
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">qr_code_2</span> VietQR</span>
                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">receipt_long</span> HĐ VAT</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="/frontend/src/modules/Gio_hang%26Thanh_toan/api/cart.js?v=1"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
