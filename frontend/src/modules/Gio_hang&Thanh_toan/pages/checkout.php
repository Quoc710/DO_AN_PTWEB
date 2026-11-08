<?php
$pageTitle = 'Thanh toán đơn vải - Aura Fabrics';
$activeNav = 'checkout';
require __DIR__ . '/../components/head.php';
require __DIR__ . '/../components/header.php';
?>
<main class="w-full pt-20 bg-background max-w-7xl mx-auto px-gutter">
    <div class="flex flex-col w-full pb-16">
        <div class="flex items-center gap-2 mb-space-md">
            <a href="/index.php?module=cart" class="flex items-center gap-1 font-label-md text-label-md text-outline hover:text-primary">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span> Giỏ hàng
            </a>
            <span class="text-outline-variant">/</span>
            <span class="font-label-md text-label-md text-primary">Thanh toán</span>
        </div>

        <!-- 1. Delivery address -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm mb-3 overflow-hidden">
            <div class="h-1 bg-[repeating-linear-gradient(45deg,#0f172a,#0f172a_30px,#fff_30px,#fff_40px,#6366f1_40px,#6366f1_70px,#fff_70px,#fff_80px)] w-full"></div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-slate-900 font-semibold text-[16px] mb-4">
                    <span class="material-symbols-outlined text-[20px]">location_on</span>
                    <span>Địa chỉ nhận hàng</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1" for="ckName">Họ tên người nhận *</label>
                        <input id="ckName" type="text" class="w-full border border-surface-container-high rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-900" placeholder="Võ Minh Quân">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1" for="ckPhone">Số điện thoại *</label>
                        <input id="ckPhone" type="tel" class="w-full border border-surface-container-high rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-900" placeholder="0988 242 868">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 mb-1" for="ckAddress">Địa chỉ chi tiết *</label>
                        <input id="ckAddress" type="text" class="w-full border border-surface-container-high rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-900" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1" for="ckEmail">Email (không bắt buộc)</label>
                        <input id="ckEmail" type="email" class="w-full border border-surface-container-high rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-900" placeholder="ban@email.com">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1" for="ckNote">Lời nhắn cho xưởng</label>
                        <input id="ckNote" type="text" class="w-full border border-surface-container-high rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-slate-900" placeholder="Bọc màng PE chống nước, dán thẻ mã màu...">
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-1.5 text-[12px] text-secondary">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    <span>Được đồng kiểm tra khổ vải và mác nhãn khi nhận hàng</span>
                </div>
            </div>
        </section>

        <!-- 1b. Shipping method -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm mb-3 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2 text-slate-900 font-semibold text-[16px]">
                        <span class="material-symbols-outlined text-[22px]">local_shipping</span>
                        <span>Chọn cách giao hàng</span>
                    </div>
                    <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 px-3 py-1 text-[11px] font-semibold">
                        <span class="material-symbols-outlined text-[15px]">verified</span> Đóng gói an toàn
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="shipping-option relative flex gap-3 p-4 border-2 border-surface-container-high rounded-xl cursor-pointer hover:border-emerald-300 transition-all">
                        <input type="radio" name="shippingMethod" value="economy" class="mt-1 accent-emerald-700">
                        <span class="material-symbols-outlined text-emerald-700">eco</span>
                        <span class="flex-1 min-w-0"><strong class="block text-sm text-on-surface">Tiết kiệm</strong><span class="block text-[11px] text-outline mt-1">4–6 ngày · Gói gọn nhẹ</span><span class="block text-xs font-bold text-emerald-700 mt-2">15.000đ</span></span>
                    </label>
                    <label class="shipping-option relative flex gap-3 p-4 border-2 border-emerald-700 bg-emerald-50/60 rounded-xl cursor-pointer hover:border-emerald-700 transition-all">
                        <input type="radio" name="shippingMethod" value="standard" class="mt-1 accent-emerald-700" checked>
                        <span class="material-symbols-outlined text-emerald-700">local_shipping</span>
                        <span class="flex-1 min-w-0"><strong class="block text-sm text-on-surface">Tiêu chuẩn</strong><span class="block text-[11px] text-outline mt-1">2–4 ngày · Phổ biến nhất</span><span class="block text-xs font-bold text-emerald-700 mt-2">30.000đ</span></span>
                        <span class="absolute top-2 right-2 text-[9px] rounded-full bg-emerald-700 text-white px-2 py-0.5 font-bold">ĐỀ XUẤT</span>
                    </label>
                    <label class="shipping-option relative flex gap-3 p-4 border-2 border-surface-container-high rounded-xl cursor-pointer hover:border-amber-300 transition-all">
                        <input type="radio" name="shippingMethod" value="express" class="mt-1 accent-amber-600">
                        <span class="material-symbols-outlined text-amber-600">bolt</span>
                        <span class="flex-1 min-w-0"><strong class="block text-sm text-on-surface">Hỏa tốc</strong><span class="block text-[11px] text-outline mt-1">1–2 ngày · Ưu tiên xử lý</span><span class="block text-xs font-bold text-amber-700 mt-2">50.000đ</span></span>
                    </label>
                </div>
            </div>
        </section>

        <!-- 2. Products -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm mb-3">
            <div class="px-6 py-4 border-b border-surface-container flex items-center justify-between text-[13px] text-outline font-medium">
                <div class="w-1/2 text-on-surface font-semibold text-[15px]">Sản phẩm (<span id="checkoutItemCount">0</span>)</div>
                <div class="w-1/6 text-center hidden sm:block">Đơn giá</div>
                <div class="w-1/6 text-center">Số lượng</div>
                <div class="w-1/6 text-right">Thành tiền</div>
            </div>
            <div class="px-6 py-3 border-b border-surface-container flex items-center gap-2">
                <span class="bg-slate-900 text-white text-[11px] font-bold px-1.5 py-0.5 rounded">Mall</span>
                <span class="font-semibold text-on-surface text-[14px]">AURA FABRICS - KHO VẢI SỈ &amp; ATELIER</span>
            </div>
            <div id="checkoutItems"></div>
            <div id="checkoutEmpty" class="hidden p-10 text-center">
                <span class="material-symbols-outlined text-[40px] text-outline">shopping_bag</span>
                <p class="font-headline-sm text-headline-sm text-on-surface mt-2">Chưa có sản phẩm để thanh toán</p>
                <a href="/index.php?module=lookbook&tab=lookbook" class="inline-flex mt-4 px-4 py-2 rounded-lg bg-primary text-on-primary font-label-md">Chọn vải ngay</a>
            </div>
            <div class="px-6 py-3 border-b border-surface-container flex items-center justify-between bg-surface-container-low/40 text-[14px]">
                <div class="flex items-center gap-2 text-on-surface">
                    <span class="material-symbols-outlined text-slate-900 text-[20px]">confirmation_number</span>
                    <span>Voucher của Shop</span>
                </div>
                <span id="ckVoucherBadge" class="text-slate-500 text-[12px] border border-surface-container-high bg-slate-50 px-2 py-0.5 rounded font-medium">Chưa áp dụng</span>
            </div>
            <div class="px-6 py-4 flex items-center justify-end gap-3 text-[14px] bg-[#fdfdfd]">
                <span class="text-outline">Tổng số tiền hàng:</span>
                <span class="text-slate-900 font-bold text-[18px]" id="ckRawTotal">0đ</span>
            </div>
        </section>

        <!-- 2b. Voucher -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm mb-3 p-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2 text-slate-900 font-bold text-[16px]">
                    <span class="material-symbols-outlined text-[20px]">local_offer</span>
                    <span>Voucher &amp; Mã giảm giá</span>
                </div>
                <span id="voucherAppliedBadge" class="hidden text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200"></span>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <input id="voucherInput" type="text" autocomplete="off"
                       class="flex-1 border border-surface-container-high rounded-lg px-3 py-2.5 text-sm font-semibold uppercase tracking-wide focus:outline-none focus:border-slate-900"
                       placeholder="Nhập mã voucher (VD: FABRIC20)">
                <button type="button" id="btnApplyVoucher"
                        class="px-4 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase transition-colors">
                    Áp dụng
                </button>
                <button type="button" id="btnRemoveVoucher"
                        class="hidden px-4 py-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface text-xs font-bold transition-colors">
                    Bỏ chọn
                </button>
            </div>
            <p id="voucherMessage" class="hidden mt-2 text-[12px]"></p>
            <div id="voucherList" class="mt-3 flex flex-col gap-1.5">
                <p class="font-body-sm text-[12px] text-outline">Đang tải voucher...</p>
            </div>
        </section>

        <!-- 3. Payment method -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm mb-3">
            <div class="px-6 py-5 border-b border-surface-container">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-slate-900 text-[22px]">account_balance</span>
                    <span class="text-[16px] font-bold text-on-surface">Phương thức thanh toán</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="payment-option flex items-start gap-3 border-2 border-slate-900 bg-slate-100 rounded-xl p-4 cursor-pointer transition-all">
                        <input type="radio" name="paymentMethod" value="vietqr" class="mt-1 accent-slate-900" checked>
                        <span class="flex flex-col gap-1">
                            <span class="flex items-center gap-2 font-semibold text-on-surface">
                                <span class="material-symbols-outlined text-[20px] text-slate-900">qr_code_2</span>
                                Chuyển khoản VietQR (ngân hàng)
                                <span class="bg-secondary/20 text-secondary text-[10px] font-bold px-1.5 py-0.5 rounded">Khớp lệnh tự động</span>
                            </span>
                            <span class="text-[12px] text-on-surface-variant">Quét QR bằng mọi app ngân hàng. Đơn được xác nhận ngay khi nhận tiền.</span>
                        </span>
                    </label>
                    <label class="payment-option flex items-start gap-3 border-2 border-surface-container-high rounded-xl p-4 cursor-pointer transition-all">
                        <input type="radio" name="paymentMethod" value="cod" class="mt-1 accent-slate-900">
                        <span class="flex flex-col gap-1">
                            <span class="flex items-center gap-2 font-semibold text-on-surface">
                                <span class="material-symbols-outlined text-[20px] text-emerald-700">local_shipping</span>
                                Thanh toán khi nhận hàng (COD)
                            </span>
                            <span class="text-[12px] text-on-surface-variant">Kiểm tra vải rồi mới thanh toán cho nhân viên giao hàng.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="p-6 border-b border-surface-container bg-[#fcfdfe]">
                <div class="flex flex-col md:flex-row items-center gap-6 max-w-3xl">
                    <div class="w-32 h-32 bg-white p-2 border border-surface-container rounded shadow-sm flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[64px] text-slate-900">qr_code_2</span>
                    </div>
                    <div class="flex flex-col gap-1 text-[13px] text-on-surface-variant flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-on-surface text-[14px]">VietQR tự động qua mọi App Ngân Hàng</span>
                        </div>
                        <p class="text-outline text-[12px]">Mã QR được tạo riêng cho từng đơn hàng, đúng số tiền và đúng nội dung chuyển khoản.</p>
                        <p class="text-outline text-[12px]">Bấm <strong>Đặt hàng</strong> để nhận mã QR và thông tin tài khoản nhận tiền.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 border-b border-surface-container flex flex-col items-end">
                <div class="w-full max-w-sm flex flex-col gap-2.5 text-[14px] text-outline">
                    <div class="flex items-center justify-between"><span>Tổng tiền hàng:</span><span class="text-on-surface" id="ckSummaryRaw">0đ</span></div>
                    <div class="flex items-center justify-between"><span>Vận chuyển (<span id="ckShippingLabel">Tiêu chuẩn</span>):</span><span class="text-on-surface" id="ckShippingFee">30.000đ</span></div>
                    <div class="flex items-center justify-between"><span>Chiết khấu sỉ vải (&gt;15m):</span><span class="text-slate-900 font-medium" id="ckWholesaleDiscount">-0đ</span></div>
                    <div class="flex items-center justify-between"><span>Tổng cộng Voucher giảm giá:</span><span class="text-slate-900 font-medium" id="ckVoucherDiscount">-0đ</span></div>
                    <div class="flex items-baseline justify-between pt-3 border-t border-surface-container mt-1">
                        <span class="text-on-surface font-semibold text-[15px]">Tổng thanh toán:</span>
                        <span class="text-slate-900 font-bold text-[28px] leading-none" id="ckFinalTotal">0đ</span>
                    </div>
                </div>
            </div>

            <div class="px-6 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-[13px] text-outline">Nhấn "Đặt hàng" đồng nghĩa với việc bạn đồng ý tuân theo <a class="text-indigo-600 hover:underline" href="#">Điều khoản Dịch vụ Aura Fabrics</a>.</p>
                <button type="button" id="btnPlaceOrder"
                        class="w-full md:w-64 py-3.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[15px] uppercase rounded shadow tracking-wide transition-colors flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="btnPlaceOrderLabel">ĐẶT HÀNG</span>
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </button>
            </div>
        </section>
    </div>
</main>

<!-- QR bank payment modal -->
<div id="paymentPanel" class="fixed inset-0 z-[70] bg-black/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col">
        <div class="px-5 py-4 border-b border-surface-container flex items-center justify-between bg-slate-100">
            <div>
                <div class="text-[11px] font-bold text-slate-900 uppercase tracking-wider">Thanh toán chuyển khoản ngân hàng</div>
                <h3 class="text-base font-bold text-slate-900 mt-0.5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-900">qr_code_2</span>
                    Quét VietQR để hoàn tất đơn hàng
                </h3>
            </div>
            <button type="button" id="btnClosePayment" class="w-8 h-8 rounded-lg text-slate-500 hover:bg-white flex items-center justify-center" title="Đóng">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div class="p-5 overflow-y-auto flex flex-col gap-4">
            <div class="flex flex-col items-center">
                <div class="w-56 h-56 bg-white border-2 border-slate-200 rounded-2xl p-2 shadow-inner flex items-center justify-center">
                    <img id="qrImage" src="" alt="Mã QR VietQR" class="w-full h-full object-contain">
                </div>
                <div class="mt-3 text-center">
                    <div class="text-[11px] uppercase tracking-wider text-outline font-bold">Số tiền cần chuyển</div>
                    <div id="qrAmount" class="text-2xl font-extrabold text-slate-900">0đ</div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 divide-y divide-slate-100 text-[13px]">
                <div class="flex items-center justify-between p-3">
                    <span class="text-outline">Ngân hàng</span>
                    <strong id="bankName" class="text-on-surface text-right">Vietcombank</strong>
                </div>
                <div class="flex items-center justify-between p-3">
                    <span class="text-outline">Số tài khoản</span>
                    <span class="flex items-center gap-2">
                        <strong id="bankAccount" class="text-on-surface font-mono">1049536311</strong>
                        <button type="button" class="text-slate-900 text-[11px] font-bold" data-copy="bankAccount">Chép</button>
                    </span>
                </div>
                <div class="flex items-center justify-between p-3">
                    <span class="text-outline">Chủ tài khoản</span>
                    <strong id="bankAccountName" class="text-on-surface text-right">AURA FABRICS</strong>
                </div>
                <div class="flex items-center justify-between p-3 bg-slate-100">
                    <span class="text-slate-900 font-semibold">Nội dung chuyển khoản</span>
                    <span class="flex items-center gap-2">
                        <strong id="transferContent" class="text-slate-900 font-mono text-[15px]">AUR000000</strong>
                        <button type="button" class="text-slate-900 text-[11px] font-bold" data-copy="transferContent">Chép</button>
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between text-[12px] text-outline">
                <span>Mã đơn: <strong id="paymentOrderCode" class="text-on-surface font-mono">#AUR000000</strong></span>
                <span>Hết hạn sau <strong id="qrCountdown" class="text-[#ba1a1a] font-mono">15:00</strong></span>
            </div>

            <div id="paymentStatus" class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[12px] px-3 py-2.5 flex items-start gap-2">
                <span class="material-symbols-outlined text-[18px]">hourglass_top</span>
                <span>Đang chờ ngân hàng xác nhận. Hệ thống tự kiểm tra mỗi 5 giây, bạn không cần tải lại trang.</span>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <button type="button" id="btnConfirmTransfer" class="flex-1 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold uppercase tracking-wide">
                    Tôi đã chuyển khoản
                </button>
                <button type="button" id="btnGoSuccess" class="hidden flex-1 py-3 rounded-xl bg-secondary hover:bg-[#005137] text-white text-xs font-bold uppercase tracking-wide">
                    Xem đơn đã thanh toán
                </button>
                <button type="button" id="btnClosePayment2" class="px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                    Đóng
                </button>
            </div>

            <p class="text-[11px] text-outline leading-relaxed">
                Nếu chuyển khoản sai nội dung, đơn sẽ chưa được xác nhận tự động. Bạn có thể bấm "Tôi đã chuyển khoản" để ghi nhận thủ công (chế độ demo) hoặc liên hệ hotline 0988.242.868.
            </p>
        </div>
    </div>
</div>

<script src="/frontend/src/modules/Gio_hang%26Thanh_toan/api/checkout.js?v=2"></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
