<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Studio - Affiliate & Fabric Network</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Ẩn hoàn toàn mũi tên tăng giảm số */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
        input[type=number] { 
            -moz-appearance: textfield; 
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Notification Banner if any -->
    <?php if ($flashMsg): ?>
    <div id="toast" class="fixed top-4 right-4 z-50 flex items-center p-4 rounded-xl shadow-xl transition-all duration-300 <?= $flashType === 'success' ? 'bg-emerald-600 text-white' : ($flashType === 'warning' ? 'bg-amber-600 text-white' : 'bg-rose-600 text-white') ?>">
        <i class="fa-solid <?= $flashType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?> text-xl mr-3"></i>
        <span class="font-medium"><?= htmlspecialchars($flashMsg) ?></span>
        <button onclick="document.getElementById('toast').remove()" class="ml-4 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <?php endif; ?>

    <!-- HEADER / NAVIGATION PHÂN HỆ -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                        A
                    </div>
                    <div>
                        <div class="font-extrabold text-slate-900 tracking-tight leading-tight">Aura Studio</div>
                        <div class="text-[11px] font-semibold text-indigo-600 tracking-wider uppercase">Affiliate & Fabric Network</div>
                    </div>
                </div>

                <!-- Navigation Switch Tabs (3 Phân Hệ) -->
                <nav class="flex items-center space-x-1 bg-slate-100 p-1.5 rounded-xl border border-slate-200">
                    <?php if ($tab !== 'tailor'): ?>

<?php endif; ?>
                    <a href="index.php?module=lookbook&amp;tab=tailor" class="flex items-center px-4 py-2 rounded-lg text-sm font-semibold transition-all <?= $tab === 'tailor' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">
                        <i class="fa-solid fa-scissors mr-2"></i> 2. Cổng Thợ May
                    </a>
                    <a href="index.php?module=lookbook&amp;tab=lookbook" class="flex items-center px-4 py-2 rounded-lg text-sm font-semibold transition-all <?= $tab === 'lookbook' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">
                        <i class="fa-solid fa-images mr-2"></i> 3. Storefront
                    </a>
                </nav>

                <!-- User status right side -->
                <div class="flex items-center space-x-3">
                    <!-- Nút Giỏ Hàng Lookbook -->
                    <button onclick="openCartModal()" class="relative flex items-center px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm">
                        <i class="fa-solid fa-cart-shopping mr-1.5 text-indigo-400"></i>
                        <span>Giỏ hàng</span>
                        <span id="cartCountBadge" class="ml-1.5 px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-extrabold min-w-[18px] text-center">0</span>
                    </button>

                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span> MySQL Connected
                    </span>
                    <div class="text-right hidden sm:block">
                        <div class="text-xs font-bold text-slate-900">Admin Aura Studio</div>
                        <div class="text-[11px] text-slate-500">Quản trị viên trưởng</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN BODY CONTENT -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- =================================================================================== -->
        <!-- TAB 2: CỔNG THỢ MAY (AFFILIATE & RÚT TIỀN & UPLOAD LOOKBOOK) -->
        <!-- =================================================================================== -->
        <?php if ($tab === 'tailor'): ?>
        <div>
            <!-- Tailor Header Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">PORTAL THỢ MAY</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">ĐỐI TÁC CHIẾN LƯỢC</span>
                    </div>
                    <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Bàn Làm Việc Thợ May - <?= htmlspecialchars($tailor['TenTiemMay'] ?: $tailor['TenNV']) ?></h1>
                    <p class="text-xs text-slate-500 mt-1">Theo dõi hoa hồng, phát hành link tiếp thị riêng, và đăng tải thiết kế may đo lên hệ thống catalogue Aura.</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
                    <div class="text-slate-500 font-medium">TÀI KHOẢN THỤ HƯỞNG</div>
                    <div class="font-extrabold text-slate-900 text-sm mt-0.5"><?= htmlspecialchars($tailor['NganHang'] ?: 'Vietcombank') ?>: **** <?= substr($tailor['SoTaiKhoan'] ?? '9354', -4) ?></div>
                    <div class="text-[11px] text-slate-500">Chủ TK: <?= htmlspecialchars($tailor['TenNV']) ?></div>
                </div>
            </div>

            <!-- KPI Statistics -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-xs font-semibold text-slate-500 mb-1">TỔNG HOA HỒNG TÍCH LŨY</div>
                    <div class="text-3xl font-extrabold text-slate-900"><?= number_format($tailor['TongTichLuy'], 0, ',', '.') ?>₫</div>
                    <div class="text-xs text-emerald-600 font-semibold mt-2">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i> Tự động ghi nhận 5% trực tiếp trên mỗi mét vải
                    </div>
                </div>
                <div class="bg-white p-6 rounded-2xl border-2 border-indigo-600/30 shadow-sm relative overflow-hidden">
                    <div class="absolute top-3 right-3 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">SẴN SÀNG RÚT</div>
                    <div class="text-xs font-semibold text-slate-500 mb-1">SỐ DƯ VÍ KHẢ DỤNG</div>
                    <div class="text-3xl font-extrabold text-indigo-600"><?= number_format($tailor['SoTienHoaHong'], 0, ',', '.') ?>₫</div>
                    <div class="text-xs text-slate-500 font-medium mt-2">
                        Tương đương <span class="font-bold text-slate-800"><?= number_format($tailor['SoTienHoaHong'] * 1.1, 0, ',', '.') ?>₫</span> ví vải (+10%)
                    </div>
                </div>
                <div onclick="openTailorOrdersModal()" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm cursor-pointer hover:border-indigo-400 hover:shadow-md transition-all group">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-xs font-semibold text-slate-500">ĐƠN HÀNG QUA LINK AFFILIATE</div>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px] text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                    </div>
                    <div class="text-3xl font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors"><?= count($affiliateOrders) ?> <span class="text-base font-normal text-slate-500">Đơn</span></div>
                    <div class="text-xs text-indigo-600 font-semibold mt-2 flex items-center">
                        <i class="fa-solid fa-receipt mr-1"></i> Bấm để xem danh sách đơn đã cộng hoa hồng 5% &rarr;
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- FORM TẠO LỆNH RÚT TIỀN -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                    <h2 class="font-bold text-slate-900 text-base flex items-center mb-1">
                        <i class="fa-solid fa-money-bill-transfer mr-2 text-indigo-600"></i> Tạo lệnh rút tiền hoa hồng
                    </h2>
                    <p class="text-xs text-slate-500 mb-4">Hệ thống đối soát tự động duyệt vào 11:30 & 16:30 hàng ngày</p>

                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="request_withdraw">
                        <input type="hidden" name="maNV" value="<?= $currentTailorId ?>">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">SỐ TIỀN MUỐN RÚT (VNĐ)</label>
                            <div class="relative">
                                <input type="text" id="withdrawAmount" name="soTien" value="2.000.000" oninput="formatCurrencyInput(this)" placeholder="Nhập số tiền muốn rút..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 font-extrabold text-slate-900 text-base focus:outline-none focus:ring-2 focus:ring-indigo-600 pr-12">
                                <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">VNĐ</span>
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button type="button" onclick="setWithdrawAmount(2000000)" class="px-2.5 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-700">2.000.000₫</button>
                                <button type="button" onclick="setWithdrawAmount(5000000)" class="px-2.5 py-1 text-xs font-semibold bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-700">5.000.000₫</button>
                                <button type="button" onclick="setWithdrawAmount(<?= intval($tailor['SoTienHoaHong']) ?>)" class="px-2.5 py-1 text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg">Toàn bộ số dư (<?= number_format($tailor['SoTienHoaHong'], 0, ',', '.') ?>₫)</button>
                            </div>
                        </div>

                        <div class="space-y-2 pt-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">HÌNH THỨC NHẬN HOA HỒNG</label>
                            <label class="flex items-start p-3 rounded-xl border border-slate-200 hover:border-indigo-400 cursor-pointer bg-slate-50/50">
                                <input type="radio" name="hinhThuc" value="NganHang" checked class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 text-xs">
                                    <div class="font-bold text-slate-900">Rút tiền mặt về tài khoản ngân hàng</div>
                                    <div class="text-slate-500"><?= htmlspecialchars($tailor['NganHang'] ?: 'Vietcombank') ?> - <?= htmlspecialchars($tailor['SoTaiKhoan'] ?: '0491000288192') ?> (Phí giao dịch: 0₫ • Chuyển 24/7)</div>
                                </div>
                            </label>
                            <label class="flex items-start p-3 rounded-xl border border-slate-200 hover:border-indigo-400 cursor-pointer bg-slate-50/50">
                                <input type="radio" name="hinhThuc" value="MuaVaiAura" class="mt-1 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-3 text-xs">
                                    <div class="font-bold text-purple-700 flex items-center">
                                        Đổi thành số dư mua vải Aura <span class="ml-2 px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 text-[10px] font-extrabold">+10% BONUS</span>
                                    </div>
                                    <div class="text-slate-500">Cộng thẳng vào tài khoản đặt vải may cho tiệm với chiết khấu độc quyền.</div>
                                </div>
                            </label>
                        </div>

                        <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Gửi yêu cầu rút tiền
                        </button>
                    </form>
                </div>

                <!-- LỊCH SỬ BIẾN ĐỘNG SỐ DƯ -->
                <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900 text-base flex items-center mb-1">
                            <i class="fa-solid fa-clock-rotate-left mr-2 text-indigo-600"></i> Lịch sử biến động số dư & Lệnh gần đây
                        </h2>
                        <p class="text-xs text-slate-500 mb-4">Các giao dịch nhận hoa hồng 5% và rút tiền của tiệm</p>

                        <div class="space-y-3">
                            <?php foreach ($transactions as $tx): ?>
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div>
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($tx['LoaiGiaoDich']) ?></div>
                                    <div class="text-[11px] text-slate-400"><?= htmlspecialchars($tx['MaDonHangCode']) ?> • <?= date('d/m/Y H:i', strtotime($tx['NgayTao'])) ?></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-extrabold text-sm <?= $tx['SoTien'] > 0 ? 'text-emerald-600' : 'text-slate-800' ?>">
                                        <?= $tx['SoTien'] > 0 ? '+' : '' ?><?= number_format($tx['SoTien'], 0, ',', '.') ?>₫
                                    </div>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Thành công</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BẢNG LINK TIẾP THỊ LIÊN KẾT (AFFILIATE) CHO TỪNG LOẠI VẢI -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
                    <div>
                        <h2 class="font-bold text-slate-900 text-base flex items-center">
                            <i class="fa-solid fa-link mr-2 text-indigo-600"></i> Tiếp thị liên kết & Mã Vải của Tiệm
                        </h2>
                        <p class="text-xs text-slate-500">Chia sẻ link hoặc mã QR trực tiếp cho khách may. Đơn hàng thành công tự động trích 5% hoa hồng vào ví tiệm.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-100">
                            <tr>
                                <th class="p-3.5 pl-6">MẪU VẢI & CHI TIẾT KHỔ</th>
                                <th class="p-3.5">GIÁ BÁN LẺ</th>
                                <th class="p-3.5">HOA HỒNG NHẬN (5%)</th>
                                <th class="p-3.5">LINK LIÊN KẾT CÁ NHÂN</th>
                                <th class="p-3.5 pr-6 text-right">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php foreach ($fabrics as $fb): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="p-3.5 pl-6">
                                    <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($fb['TenSP']) ?></div>
                                    <div class="text-slate-400 text-[11px]">Mã: <?= htmlspecialchars($fb['MaVaiCode']) ?> • Màu: <?= htmlspecialchars($fb['MauSac']) ?> • <?= htmlspecialchars($fb['ChatLieuSize']) ?></div>
                                </td>
                                <td class="p-3.5 font-bold text-slate-900">
                                    <?= number_format($fb['DonGia'], 0, ',', '.') ?>₫ <span class="font-normal text-slate-500">/m</span>
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                        +<?= number_format($fb['HoaHongNhanMoiMet'], 0, ',', '.') ?>₫ /m
                                    </span>
                                </td>
                                <td class="p-3.5 whitespace-nowrap">
                                    <a href="<?= htmlspecialchars($fb['AffiliateLink']) ?>" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-[11px] font-mono font-bold transition-colors">
                                        <i class="fa-solid fa-arrow-up-right-from-square mr-1.5 text-[10px]"></i> <?= htmlspecialchars($fb['AffiliateLink']) ?>
                                    </a>
                                </td>
                                <td class="p-3.5 pr-6 text-right whitespace-nowrap">
                                    <a href="<?= htmlspecialchars($fb['AffiliateLink']) ?>" class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm mr-1 inline-flex items-center">
                                        <i class="fa-solid fa-eye mr-1"></i> Xem thử
                                    </a>
                                    <button onclick="navigator.clipboard.writeText('<?= $fb['AffiliateFullUrl'] ?>'); alert('🎉 ĐÃ SAO CHÉP LINK THẬT:\n\n<?= $fb['AffiliateFullUrl'] ?>\n\nGửi link này cho khách hàng để xem đúng mẫu vải và tự động nhận 5% hoa hồng khi khách mua!');" class="px-2.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all shadow-sm mr-1 inline-flex items-center">
                                        <i class="fa-solid fa-copy mr-1"></i> Sao chép Link
                                    </button>
                                    <button onclick="showQrModal('<?= addslashes($fb['TenSP']) ?>', '<?= $fb['AffiliateFullUrl'] ?>');" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-bold transition-all inline-flex items-center shadow-sm">
                                        <i class="fa-solid fa-qrcode mr-1"></i> Mã QR
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FORM GỬI MẪU LOOKBOOK MỚI (GẮN TAG MÃ VẢI) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <div class="mb-4">
                    <h2 class="font-bold text-slate-900 text-base flex items-center">
                        <i class="fa-solid fa-cloud-arrow-up mr-2 text-indigo-600"></i> Gửi mẫu Lookbook mới (Gắn Tag Mã Vải Tiệm)
                    </h2>
                    <p class="text-xs text-slate-500">Mẫu trang phục hoàn thiện được duyệt sẽ xuất hiện trên Lookbook chính thức kèm thông tin tiệm may của bạn.</p>
                </div>

                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <input type="hidden" name="action" value="submit_lookbook">
                    <input type="hidden" name="maNV" value="<?= $currentTailorId ?>">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">TÊN THIẾT KẾ MAY</label>
                            <input type="text" name="tenLookbook" required placeholder="Ví dụ: Đầm xòe cổ yếm linen cao cấp" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">DANH MỤC THIẾT KẾ</label>
                            <select name="danhMuc" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                <option value="Đầm & Váy">Đầm & Váy</option>
                                <option value="Áo dài truyền thống">Áo dài truyền thống</option>
                                <option value="Vest & Blazer">Vest & Blazer</option>
                                <option value="Áo sơ mi & Công sở">Áo sơ mi & Công sở</option>
                                <option value="Trang phục nam">Trang phục nam</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">MÃ VẢI ĐÃ DÙNG TỪ TIỆM (TAG)</label>
                                <select name="maBienThe" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                                    <?php foreach ($fabrics as $fb): ?>
                                    <option value="<?= $fb['MaBienThe'] ?>"><?= $fb['MaVaiCode'] ?> - <?= $fb['TenSP'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">ĐỊNH LƯỢNG VẢI TIÊU HAO</label>
                                <input type="text" name="dinhLuong" placeholder="Ví dụ: 2.2m (Khổ 1.45m)" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-600">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">ẢNH THÀNH PHẨM (TẢI LÊN TỪ MÁY TÍNH / ĐIỆN THOẠI)</label>
                            <div class="border-2 border-dashed border-slate-300 hover:border-indigo-500 rounded-xl p-4 text-center cursor-pointer bg-slate-50/50 hover:bg-slate-50 transition-all relative" onclick="document.getElementById('anhLookbookInput').click()">
                                <input type="file" name="anhLookbook" id="anhLookbookInput" accept="image/*" class="hidden" onchange="previewUploadImage(event)">
                                <div id="uploadPlaceholder">
                                    <i class="fa-solid fa-cloud-arrow-up text-2xl text-indigo-600 mb-2"></i>
                                    <div class="text-xs font-bold text-slate-700">Bấm vào đây để chọn ảnh từ máy tính</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">Hỗ trợ JPG, PNG, WEBP (Ảnh chụp thực tế tại tiệm)</div>
                                </div>
                                <div id="uploadPreviewBox" class="hidden flex flex-col items-center justify-center">
                                    <img id="uploadPreviewImg" src="" class="max-h-36 rounded-lg object-contain shadow-sm border border-slate-200 mb-1.5" alt="Ảnh xem trước">
                                    <span class="text-[11px] text-indigo-600 font-bold hover:underline">Bấm để đổi ảnh khác</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">MÔ TẢ FORM DÁNG & LỜI KHUYÊN CỦA THỢ MAY</label>
                            <textarea name="moTa" rows="3" placeholder="Chia sẻ cách may, lưu ý giặt là giúp khách mặc đẹp hơn..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-600"></textarea>
                        </div>

                        <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center">
                            <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Gửi duyệt lên Lookbook Tiệm
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- =================================================================================== -->
        <!-- TAB 3: LOOKBOOK KHÁCH HÀNG (GALLERY & TÍNH MÉT VẢI & LỌC THEO MÀU SẮC & MUA VẢI) -->
        <!-- =================================================================================== -->
        <?php if ($tab === 'lookbook'): ?>
        <?php
            $optMauSac = $filterOptions['mau_sac'] ?? [];
            if (empty($optMauSac)) {
                foreach (($lookbooks ?? []) as $r) {
                    $m = trim((string)($r['MauSac'] ?? ''));
                    if ($m !== '') {
                        $optMauSac[$m] = $m;
                    }
                }
                $optMauSac = array_values($optMauSac);
            }
            $selKeyword = trim((string)($_GET['keyword'] ?? ''));
            $selMauSac  = trim((string)($_GET['mau_sac'] ?? ''));
            $selLoaiSp  = trim((string)($_GET['loai_sp'] ?? ''));
        ?>
        <div>
            <?php if ($referralTailor): ?>
            <!-- AFFILIATE REFERRAL BANNER -->
            <div class="mb-8 p-5 rounded-2xl bg-gradient-to-r from-indigo-50 via-purple-50 to-pink-50 border border-indigo-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 uppercase tracking-wide">Giới thiệu bởi Đối tác May</span>
                            <span class="text-sm font-extrabold text-slate-900"><?= htmlspecialchars($referralTailor['TenTiemMay'] ?: $referralTailor['TenNV']) ?></span>
                            <span class="text-xs font-mono font-bold text-indigo-700 bg-white px-2 py-0.5 rounded border border-indigo-200">Mã ref: <?= htmlspecialchars($referralTailor['AffiliateCode']) ?></span>
                        </div>
                        <div class="text-xs text-slate-600 mt-1">
                            👋 Chào bạn! Bạn đang xem mẫu vải qua link giới thiệu của <strong><?= htmlspecialchars($referralTailor['TenTiemMay'] ?: $referralTailor['TenNV']) ?></strong>. Khi đặt mua vải tại đây, tiệm may sẽ tự động nhận 5% hoa hồng và hỗ trợ tư vấn may đo miễn phí!
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($fabricFilter): ?>
                    <a href="index.php?module=lookbook&amp;tab=lookbook&amp;ref=<?= urlencode($refCode) ?>" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold transition-all shadow-sm flex items-center whitespace-nowrap">
                        <i class="fa-solid fa-layer-group mr-1.5 text-slate-400"></i> Xem tất cả mẫu vải
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Banner Header -->
            <div class="text-center max-w-3xl mx-auto mb-8">
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">LOOKBOOK THIẾT KẾ • CẢM HỨNG MAY ĐO</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mt-3 tracking-tight">Thư viện Mẫu May & Vải Thực Tế</h1>
                <p class="text-sm text-slate-500 mt-2">
                    Khám phá các thiết kế may đo thực tế từ mạng lưới thợ may Aura. Mua trực tiếp mét vải chính hãng đã may theo mẫu hoặc tự do biến tấu form dáng.
                </p>
                <div class="flex items-center justify-center gap-6 mt-4 text-xs font-semibold text-slate-600">
                    <span><i class="fa-solid fa-store mr-1 text-indigo-600"></i> 148+ Đối tác may</span>
                    <span><i class="fa-solid fa-layer-group mr-1 text-indigo-600"></i> 320 loại vải sẵn sàng</span>
                    <span><i class="fa-solid fa-scissors mr-1 text-indigo-600"></i> Kho hàng mở cắt lẻ từ 0.5m</span>
                </div>
            </div>

            <!-- 1. CÔNG THỨC TÍNH ĐỊNH MỨC LƯỢNG VẢI CẦN MUA -->
            <div class="max-w-5xl mx-auto bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-6">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h2 class="font-bold text-slate-900 text-xs sm:text-sm flex items-center">
                        <i class="fa-solid fa-calculator mr-2 text-indigo-600"></i> Công thức tính định mức lượng vải cần mua
                    </h2>
                    <span class="text-[11px] text-slate-500">Công thức: <strong>Mét vải = Định mức mẫu × Hệ số khổ vải × Số lượng × (1 + % hao hụt)</strong></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-600 block mb-1">Loại trang phục</label>
                        <select id="calcGarmentSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-800 font-semibold">
                            <option value="2.5">Áo dài (2.5m)</option>
                            <option value="2.2">Đầm &amp; Váy (2.2m)</option>
                            <option value="3.0">Vest / Suit (3.0m)</option>
                            <option value="1.6">Áo sơ mi (1.6m)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-600 block mb-1">Khổ vải</label>
                        <select id="calcWidthFactor" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-800 font-semibold">
                            <option value="1.0">Khổ lớn 1.5m (×1.0)</option>
                            <option value="1.3">Khổ nhỏ 1.15m (×1.3)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-600 block mb-1">Số lượng bộ</label>
                        <input id="calcGarmentQty" type="number" value="1" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-800 font-bold">
                    </div>
                    <div>
                        <label class="font-bold text-slate-600 block mb-1">Dự phòng hao hụt (%)</label>
                        <input id="calcGarmentBuffer" type="number" value="10" min="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-800 font-bold">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="calculateLookbookFormula()" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-2.5 px-4 rounded-xl transition cursor-pointer">
                            Tính mét vải
                        </button>
                    </div>
                </div>
                <div id="calcFormulaResult" class="hidden mt-3 p-3 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-950 text-xs font-semibold text-center"></div>
            </div>

            <!-- 2. BỘ LỌC THEO MÀU SẮC TỪ DATABASE -->
            <!-- 2. BỘ LỌC SẢN PHẨM (TÌM KIẾM + PHÂN LOẠI + MÀU SẮC) -->
            <form method="GET" action="index.php" class="max-w-5xl mx-auto bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-8">
                <input type="hidden" name="module" value="lookbook">
                <input type="hidden" name="tab" value="lookbook">
                <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? 'Tất cả mẫu') ?>">
                <?php if (!empty($refCode)): ?>
                    <input type="hidden" name="ref" value="<?= htmlspecialchars($refCode) ?>">
                <?php endif; ?>

                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h2 class="font-bold text-slate-900 text-xs sm:text-sm flex items-center">
                        <i class="fa-solid fa-filter mr-2 text-indigo-600"></i> Lọc sản phẩm (Database)
                    </h2>
                    <span class="text-xs text-slate-500">
                        Đang hiển thị: <strong class="text-indigo-600 text-sm"><?= count($lookbooks ?? []) ?></strong> mẫu
                    </span>
                </div>

                <!-- Đổi grid thành 4 cột để chứa thêm mục Phân loại -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 text-xs">
                    <div>
                        <label class="font-bold text-slate-600 block mb-1">Tìm tên mẫu / Mã vải</label>
                        <input type="text" name="keyword" value="<?= htmlspecialchars($selKeyword) ?>" placeholder="VD: Linen, #LN-55..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 focus:outline-indigo-600">
                    </div>
                    
                    <!-- MỤC GIAO DIỆN MỚI: PHÂN LOẠI -->
                    <div>
                        <label class="font-bold text-slate-600 block mb-1">Phân loại</label>
                        <select name="loai_sp" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 font-semibold focus:outline-indigo-600">
                            <option value="">-- Tất cả --</option>
                            <option value="vai" <?= $selLoaiSp === 'vai' ? 'selected' : '' ?>>Vải (Kho nguyên cuộn)</option>
                            <option value="lookbook" <?= $selLoaiSp === 'lookbook' ? 'selected' : '' ?>>Đồ may sẵn (Lookbook)</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold text-slate-600 block mb-1">Màu sắc (CSDL)</label>
                        <select name="mau_sac" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 font-semibold focus:outline-indigo-600">
                            <option value="">-- Tất cả Màu sắc --</option>
                            <?php foreach ($optMauSac as $ms): ?>
                                <option value="<?= htmlspecialchars((string)$ms) ?>" <?= $selMauSac === (string)$ms ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)$ms) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl cursor-pointer transition">
                            <i class="fa-solid fa-magnifying-glass mr-1"></i> Lọc
                        </button>
                        <a href="index.php?module=lookbook&amp;tab=lookbook&amp;category=<?= urlencode($category ?? 'Tất cả mẫu') ?><?= !empty($refCode) ? '&amp;ref=' . urlencode($refCode) : '' ?>" class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition text-center">
                            Đặt lại
                        </a>
                    </div>
                </div>
            </form>

            <!-- Categories Filter -->
            <div class="flex items-center justify-center flex-wrap gap-2 mb-8">
                <?php
                $cats = ['Tất cả mẫu', 'Đầm & Váy', 'Áo dài truyền thống', 'Vest & Blazer', 'Áo sơ mi & Công sở', 'Trang phục nam'];
                foreach ($cats as $cat):
                    $active = ($category === $cat);
                ?>
                <a href="index.php?module=lookbook&amp;tab=lookbook&amp;category=<?= urlencode($cat) ?><?= $selMauSac !== '' ? '&amp;mau_sac=' . urlencode($selMauSac) : '' ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition-all <?= $active ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                    <?= $cat ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Lookbook Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($lookbooks as $lb): ?>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow group">
                    <!-- Photo with Badges (Click để xem chi tiết mẫu may) -->
                    <div onclick="openDetailModal('<?= addslashes($lb['TenLookBook']) ?>', '<?= addslashes($lb['URLHinhAnh']) ?>', '<?= addslashes($lb['TenTiemMay'] ?: $lb['TenNV']) ?>', '<?= addslashes($lb['SDT'] ?: '') ?>', '<?= addslashes($lb['MaVaiCode']) ?>', '<?= addslashes($lb['TenSP']) ?>', '<?= addslashes($lb['DinhLuongVai']) ?>', '<?= addslashes($lb['MoTa']) ?>', <?= $lb['DonGia'] ?: 185000 ?>)" class="relative aspect-[3/4] overflow-hidden bg-slate-100 cursor-pointer group/img">
                        <img src="<?= htmlspecialchars($lb['URLHinhAnh']) ?>" class="w-full h-full object-cover group-hover/img:scale-105 transition-transform duration-500" alt="<?= htmlspecialchars($lb['TenLookBook']) ?>">
                        
                        <!-- Hover hint overlay -->
                        <div class="absolute inset-0 bg-slate-900/30 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="px-3.5 py-1.5 rounded-xl bg-white/95 text-xs font-bold text-slate-900 shadow-md backdrop-blur-sm flex items-center">
                                <i class="fa-solid fa-magnifying-glass-plus mr-1.5 text-indigo-600"></i> Xem chi tiết
                            </span>
                        </div>

                        <div class="absolute top-3 left-3 flex gap-2">
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-white/90 backdrop-blur-sm text-slate-800 shadow-sm">
                                <?= htmlspecialchars($lb['DanhMuc']) ?>
                            </span>
                            <?php if (!empty($lb['MauSac'])): ?>
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-indigo-600/90 text-white shadow-sm">
                                Màu: <?= htmlspecialchars($lb['MauSac']) ?>
                            </span>
                            <?php endif; ?>
                        </div>

                        <div class="absolute bottom-3 left-3 right-3 p-3 rounded-xl bg-white/95 backdrop-blur-sm border border-slate-200/60 shadow-lg">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold">
                                        <?= strtoupper(substr($lb['TenTiemMay'] ?: $lb['TenNV'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($lb['TenTiemMay'] ?: $lb['TenNV']) ?></div>
                                        <div class="text-[10px] text-slate-500">Đối tác may Aura</div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">Ref: #LK-<?= $lb['MaLookBook'] ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 onclick="openDetailModal('<?= addslashes($lb['TenLookBook']) ?>', '<?= addslashes($lb['URLHinhAnh']) ?>', '<?= addslashes($lb['TenTiemMay'] ?: $lb['TenNV']) ?>', '<?= addslashes($lb['SDT'] ?: '') ?>', '<?= addslashes($lb['MaVaiCode']) ?>', '<?= addslashes($lb['TenSP']) ?>', '<?= addslashes($lb['DinhLuongVai']) ?>', '<?= addslashes($lb['MoTa']) ?>', <?= $lb['DonGia'] ?: 185000 ?>)" class="font-bold text-slate-900 text-base leading-snug cursor-pointer hover:text-indigo-600 transition-colors">
                                <?= htmlspecialchars($lb['TenLookBook']) ?>
                            </h3>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2"><?= htmlspecialchars($lb['MoTa']) ?></p>

                            <!-- TAG SẢN PHẨM MÃ VẢI -->
                            <div class="mt-4 p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">VẢI ĐƯỢC DÙNG</span>
                                    <span class="text-[11px] font-bold text-slate-500"><?= htmlspecialchars($lb['ChatLieuSize'] ?: 'Khổ 1.45m') ?></span>
                                </div>
                                <div class="flex items-center justify-between font-bold text-slate-900">
                                    <span class="text-indigo-700"><?= htmlspecialchars($lb['MaVaiCode'] ?: '#LN-55') ?> <?= htmlspecialchars($lb['TenSP']) ?></span>
                                    <span class="text-sm"><?= number_format($lb['DonGia'] ?: 185000, 0, ',', '.') ?>₫<span class="text-[11px] text-slate-400 font-normal">/m</span></span>
                                </div>
                                <div class="text-[11px] text-slate-500 flex items-center">
                                    <i class="fa-solid fa-ruler-combined mr-1 text-slate-400"></i> Gợi ý định lượng: <strong class="ml-1 text-slate-700"><?= htmlspecialchars($lb['DinhLuongVai']) ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons (3 nút: Mua ngay, + Giỏ, Chi tiết) -->
                        <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-2">
                            <button onclick="openAffiliateBuyModal('<?= addslashes($lb['TenLookBook']) ?>', '<?= addslashes($lb['URLHinhAnh']) ?>', '<?= addslashes($lb['MaVaiCode']) ?>', '<?= addslashes($lb['TenSP']) ?>', <?= $lb['DonGia'] ?: 185000 ?>, '<?= addslashes($lb['DinhLuongVai']) ?>', '<?= addslashes($refCode ?: ($tailor['AffiliateCode'] ?? '')) ?>', '<?= addslashes($referralTailor['TenTiemMay'] ?? ($tailor['TenTiemMay'] ?? 'Nhà May Hoàng Kim')) ?>')" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center">
                                <i class="fa-solid fa-bolt mr-1.5 text-amber-400"></i> Mua ngay
                            </button>

                            <button onclick="addToCartItem('<?= addslashes($lb['MaVaiCode']) ?>', '<?= addslashes($lb['TenSP']) ?>', '<?= addslashes($lb['DinhLuongVai']) ?>', <?= $lb['DonGia'] ?: 185000 ?>, '<?= addslashes($lb['URLHinhAnh']) ?>', '<?= addslashes($refCode ?: ($tailor['AffiliateCode'] ?? '')) ?>')" class="px-3 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center whitespace-nowrap" title="Thêm mét vải vào giỏ hàng">
                                <i class="fa-solid fa-cart-plus mr-1"></i> + Giỏ
                            </button>

                            <button onclick="openDetailModal('<?= addslashes($lb['TenLookBook']) ?>', '<?= addslashes($lb['URLHinhAnh']) ?>', '<?= addslashes($lb['TenTiemMay'] ?: $lb['TenNV']) ?>', '<?= addslashes($lb['SDT'] ?: '') ?>', '<?= addslashes($lb['MaVaiCode']) ?>', '<?= addslashes($lb['TenSP']) ?>', '<?= addslashes($lb['DinhLuongVai']) ?>', '<?= addslashes($lb['MoTa']) ?>', <?= $lb['DonGia'] ?: 185000 ?>)" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                                Chi tiết
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            © 2026 Aura Studio & Tailor Affiliate Network • Đồ Án Chuyên Đề Web (PHP Thuần & MySQL) • Module Lookbook & Affiliate
        </div>
    </footer>

    <!-- POPUP MODAL CHI TIẾT MẪU LOOKBOOK -->
    <div id="detailModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white max-w-2xl w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col md:flex-row max-h-[90vh]">
            <div class="md:w-1/2 bg-slate-100">
                <img id="modalImg" src="" class="w-full h-full object-cover min-h-[300px]" alt="Mẫu may">
            </div>
            <div class="md:w-1/2 p-6 flex flex-col justify-between overflow-y-auto">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-indigo-600 uppercase">Chi tiết mẫu may đo</span>
                        <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <h3 id="modalTitle" class="text-lg font-bold text-slate-900 leading-snug"></h3>
                    <p id="modalDesc" class="text-xs text-slate-500 mt-2"></p>

                    <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs space-y-1">
                        <div class="text-[11px] font-bold text-slate-400 uppercase">THÔNG TIN TIỆM MAY</div>
                        <div id="modalTailor" class="font-bold text-slate-900"></div>
                        <div id="modalPhone" class="text-slate-500"></div>
                    </div>

                    <div class="mt-3 p-3 rounded-xl bg-indigo-50/60 border border-indigo-100 text-xs space-y-1">
                        <div class="text-[11px] font-bold text-indigo-500 uppercase">VẢI GẮN THẺ (TAG)</div>
                        <div id="modalFabric" class="font-bold text-indigo-900"></div>
                        <div id="modalMetric" class="text-indigo-700"></div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex gap-2">
                    <button id="modalBuyBtn" class="flex-1 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-all flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-bolt mr-1.5 text-amber-400"></i> Mua vải ngay
                    </button>
                    <button id="modalAddCartBtn" class="px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center whitespace-nowrap">
                        <i class="fa-solid fa-cart-plus mr-1"></i> + Giỏ
                    </button>
                    <button onclick="document.getElementById('detailModal').classList.add('hidden')" class="px-3.5 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200 transition-all">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- POPUP MODAL GIỎ HÀNG LOOKBOOK (KẾT NỐI MODULE 2 CỦA BẠN TÂY) -->
    <div id="cartModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white max-w-lg w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                <div>
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">GIỎ HÀNG VẢI LOOKBOOK</span>
                    <h3 class="text-base font-bold text-slate-900 flex items-center mt-0.5">
                        <i class="fa-solid fa-cart-shopping mr-2 text-indigo-600"></i> Các Mét Vải Đã Chọn Mua (<span id="cartModalHeaderCount">0</span>)
                    </h3>
                </div>
                <button onclick="closeCartModal()" class="text-slate-400 hover:text-slate-600 text-lg w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-3 flex-1" id="cartItemsList"></div>

            <div class="p-5 border-t border-slate-100 bg-slate-50 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Tổng tiền vải:</span>
                    <span id="cartTotalPrice" class="text-lg font-extrabold text-indigo-600">0₫</span>
                </div>
                <div class="p-2.5 rounded-xl bg-indigo-50/80 border border-indigo-100 text-[11px] text-indigo-800 flex items-center">
                    <i class="fa-solid fa-link mr-2 text-indigo-600 text-sm"></i>
                    <span>Giỏ hàng tự động đồng bộ sang <strong>Module 2 (Giỏ hàng & Thanh toán của Tây)</strong>.</span>
                </div>
                <div class="flex gap-2">
                    <button onclick="checkoutCart()" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center">
                        <i class="fa-solid fa-credit-card mr-2 text-emerald-400"></i> Tiến hành Thanh toán (Module 2) &rarr;
                    </button>
                    <button onclick="closeCartModal()" class="px-4 py-3 bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 rounded-xl text-xs font-semibold transition-all">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- POPUP MODAL MUA VẢI TRỰC TIẾP TỪ LOOKBOOK & TÍNH HOA HỒNG AFFILIATE 5% -->
    <div id="buyModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white max-w-lg w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                <div>
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">MUA VẢI CHÍNH HÃNG THEO THIẾT KẾ</span>
                    <h3 class="text-base font-bold text-slate-900 flex items-center mt-0.5">
                        <i class="fa-solid fa-cart-shopping mr-2 text-indigo-600"></i> Đặt Mua Vải Trực Tiếp Từ Ảnh
                    </h3>
                </div>
                <button onclick="closeBuyModal()" class="text-slate-400 hover:text-slate-600 text-lg w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="buy_via_affiliate">
                <input type="hidden" name="refCode" id="buyRefCode" value="<?= htmlspecialchars($refCode ?: ($tailor['AffiliateCode'] ?? '')) ?>">
                <input type="hidden" name="maVaiCode" id="buyMaVaiCode" value="#LN-55">
                <input type="hidden" name="donGia" id="buyDonGia" value="185000">

                <div class="flex items-center gap-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                    <img id="buyModalImg" src="" class="w-16 h-20 object-cover rounded-lg border border-slate-200 shadow-sm" alt="Mẫu vải">
                    <div class="flex-1 min-w-0">
                        <div id="buyModalTitle" class="font-bold text-slate-900 text-sm truncate"></div>
                        <div id="buyModalFabric" class="text-xs font-extrabold text-indigo-600 mt-0.5"></div>
                        <div class="text-xs text-slate-500 mt-1">
                            Đơn giá: <span id="buyModalPriceDisplay" class="font-bold text-slate-900"></span>/m
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">SỐ MÉT VẢI CẦN CẮT</label>
                        <input type="number" step="0.1" min="0.5" id="buySoMet" name="soMet" value="2.4" oninput="calculateBuyTotal()" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
                        <div class="text-[11px] text-slate-400 mt-1">Hỗ trợ cắt lẻ từ 0.5m</div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TỔNG TIỀN THANH TOÁN</label>
                        <div id="buyTotalDisplay" class="px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 font-extrabold text-indigo-600 text-sm">
                            444.000₫
                        </div>
                        <div class="text-[11px] text-emerald-600 font-bold mt-1" id="buyCommissionDisplay">
                            +22.200₫ hoa hồng cho thợ may
                        </div>
                    </div>
                </div>

                <!-- Box cơ chế Affiliate 5% -->
                <div class="p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-100 text-xs space-y-1.5">
                    <div class="flex items-center justify-between font-bold text-indigo-900">
                        <span class="flex items-center"><i class="fa-solid fa-gift mr-1.5 text-indigo-600"></i> Cơ chế Affiliate (5%):</span>
                        <span id="buyTailorNameDisplay" class="text-indigo-700 font-bold"><?= htmlspecialchars($referralTailor['TenTiemMay'] ?? ($tailor['TenTiemMay'] ?? 'Nhà May Hoàng Kim')) ?></span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Đơn mua vải này được ghi nhận qua mã giới thiệu của thợ may đối tác. Khi hoàn tất, hệ thống tự động trích <strong>5% giá trị đơn vải</strong> cộng ngay vào ví của thợ may.
                    </p>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center">
                        <i class="fa-solid fa-check-circle mr-2 text-emerald-400"></i> Xác nhận đặt mua vải ngay
                    </button>
                    <button type="button" onclick="closeBuyModal()" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all">
                        Đóng
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- POPUP MODAL ĐƠN HÀNG QUA LINK AFFILIATE CỦA THỢ MAY -->
    <div id="tailorOrdersModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white max-w-3xl w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                <div>
                    <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">PORTAL THỢ MAY (MODULE 5)</span>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center mt-0.5">
                        <i class="fa-solid fa-receipt mr-2 text-indigo-600"></i> Đơn Hàng Mua Vải Qua Link Tiếp Thị - <?= htmlspecialchars($tailor['TenTiemMay'] ?: $tailor['TenNV']) ?>
                    </h3>
                </div>
                <button onclick="closeTailorOrdersModal()" class="text-slate-400 hover:text-slate-600 text-lg w-8 h-8 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4">
                <div class="flex items-center justify-between p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-100 text-xs">
                    <div>
                        <span class="text-slate-600">Mã giới thiệu của tiệm:</span>
                        <strong class="text-indigo-700 ml-1 font-mono"><?= htmlspecialchars($tailor['AffiliateCode'] ?: '') ?></strong>
                    </div>
                    <div>
                        <span class="text-slate-600">Tỷ lệ hoa hồng:</span>
                        <strong class="text-emerald-700 ml-1">5% / mét vải bán ra</strong>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 font-bold text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-3.5 pl-4 whitespace-nowrap">MÃ ĐƠN HÀNG</th>
                                <th class="p-3.5">NỘI DUNG MUA VẢI</th>
                                <th class="p-3.5 whitespace-nowrap">HOA HỒNG NHẬN (5%)</th>
                                <th class="p-3.5 whitespace-nowrap">THỜI GIAN</th>
                                <th class="p-3.5 pr-4 text-center whitespace-nowrap">TRẠNG THÁI</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (empty($affiliateOrders)): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    <i class="fa-solid fa-box-open text-2xl mb-1 text-slate-300"></i>
                                    <p class="text-xs font-semibold">Chưa có đơn hàng nào qua link tiếp thị của tiệm!</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($affiliateOrders as $ord): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3.5 pl-4 font-bold text-indigo-600 whitespace-nowrap"><?= htmlspecialchars($ord['MaDonHangCode']) ?></td>
                                <td class="p-3.5">
                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($ord['LoaiGiaoDich']) ?></div>
                                    <div class="text-[11px] text-slate-400">Khách mua trực tiếp từ Lookbook</div>
                                </td>
                                <td class="p-3.5 font-extrabold text-emerald-600 whitespace-nowrap">
                                    +<?= number_format($ord['SoTien'], 0, ',', '.') ?>₫
                                </td>
                                <td class="p-3.5 text-slate-500 whitespace-nowrap">
                                    <?= date('d/m/Y H:i', strtotime($ord['NgayTao'])) ?>
                                </td>
                                <td class="p-3.5 pr-4 text-center whitespace-nowrap">
                                    <span class="inline-block whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-check mr-1"></i> Đã cộng ví
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-4 border-t border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="text-xs font-semibold text-slate-600">
                    Tổng cộng: <strong class="text-indigo-600"><?= count($affiliateOrders) ?></strong> đơn hàng qua link affiliate
                </div>
                <button onclick="closeTailorOrdersModal()" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition-all shadow-sm">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- POPUP MODAL MÃ QR TIẾP THỊ LIÊN KẾT -->
    <div id="qrModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white max-w-sm w-full rounded-2xl overflow-hidden shadow-2xl flex flex-col items-center p-6 text-center">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-3">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <h3 class="text-base font-bold text-slate-900" id="qrModalTitle">Mã QR Tiếp Thị Vải</h3>
            <p class="text-xs text-slate-500 mt-1 mb-4">Khách quét mã sẽ mở ngay trang Lookbook mẫu vải này và tự động gắn mã giới thiệu của bạn.</p>

            <div class="p-3 rounded-2xl border-2 border-slate-200 bg-white shadow-inner mb-4 inline-block">
                <img id="qrModalImg" src="" alt="Mã QR" class="w-48 h-48 object-contain">
            </div>

            <div class="w-full text-left bg-slate-50 p-2.5 rounded-xl border border-slate-200 mb-4 text-[11px] font-mono text-slate-600 truncate" id="qrModalUrlText">
            </div>

            <div class="w-full flex gap-2">
                <button id="copyQrUrlBtn" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shadow-sm flex items-center justify-center">
                    <i class="fa-solid fa-copy mr-1.5"></i> Sao chép Link
                </button>
                <button onclick="closeQrModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition-all">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT XỬ LÝ CLICK TƯƠNG TÁC -->
    <script>
        // Công thức tính định mức lượng vải cần mua
        function calculateLookbookFormula() {
            const rate   = parseFloat(document.getElementById('calcGarmentSelect').value) || 2.0;
            const factor = parseFloat(document.getElementById('calcWidthFactor').value) || 1.0;
            const qty    = Math.max(1, parseInt(document.getElementById('calcGarmentQty').value, 10) || 1);
            const buf    = Math.max(0, parseFloat(document.getElementById('calcGarmentBuffer').value) || 0);
            const total  = (rate * factor * qty * (1 + buf / 100)).toFixed(2);

            const box = document.getElementById('calcFormulaResult');
            box.innerHTML = `Công thức: ${rate}m × ${factor} (khổ vải) × ${qty} bộ + ${buf}% dự phòng = <strong class="text-indigo-700 text-sm">${total} mét vải cần mua</strong>`;
            box.classList.remove('hidden');
        }

        function openDetailModal(title, img, tailor, phone, code, fabric, metric, desc, donGia) {
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalImg').src = img;
            document.getElementById('modalTailor').innerText = tailor;
            document.getElementById('modalPhone').innerText = phone ? ('Hotline / Zalo: ' + phone) : 'Hotline: 0912.334.881';
            document.getElementById('modalFabric').innerText = code + ' - ' + fabric;
            document.getElementById('modalMetric').innerText = 'Khuyến nghị tiêu hao: ' + metric;
            document.getElementById('modalDesc').innerText = desc || 'Chưa có mô tả chi tiết.';
            
            const buyBtn = document.getElementById('modalBuyBtn');
            if (buyBtn) {
                buyBtn.onclick = function() {
                    document.getElementById('detailModal').classList.add('hidden');
                    openAffiliateBuyModal(title, img, code, fabric, donGia || 185000, metric, document.getElementById('buyRefCode').value, tailor);
                };
            }

            const addCartBtn = document.getElementById('modalAddCartBtn');
            if (addCartBtn) {
                addCartBtn.onclick = function() {
                    addToCartItem(code, fabric, metric, donGia || 185000, img, document.getElementById('buyRefCode').value);
                    document.getElementById('detailModal').classList.add('hidden');
                };
            }

            document.getElementById('detailModal').classList.remove('hidden');
        }

        // ==========================================
        // QUẢN LÝ GIỎ HÀNG LOOKBOOK (KẾT NỐI MODULE 2)
        // ==========================================
        function getCart() {
            try { return JSON.parse(localStorage.getItem('aura_lookbook_cart') || '[]'); }
            catch (e) { return []; }
        }

        function saveCart(cart) {
            localStorage.setItem('aura_lookbook_cart', JSON.stringify(cart));
            updateCartBadge();
        }

        function updateCartBadge() {
            const cart = getCart();
            const count = cart.length;
            const badge = document.getElementById('cartCountBadge');
            if (badge) {
                badge.innerText = count;
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            }
            const headerCount = document.getElementById('cartModalHeaderCount');
            if (headerCount) headerCount.innerText = count;
        }

        function addToCartItem(maVai, tenSP, dinhLuong, donGia, img, refCode) {
            const cart = getCart();
            let soMet = 2.4;
            if (dinhLuong) {
                const match = dinhLuong.match(/([0-9]+(\.[0-9]+)?)/);
                if (match) soMet = parseFloat(match[0]);
            }
            const tongTien = Math.round(soMet * donGia);

            cart.push({ maVai, tenSP, soMet, dinhLuong, donGia, tongTien, img, refCode: refCode || '' });
            saveCart(cart);
            showQuickToast(`🛒 Đã thêm ${soMet}m vải ${maVai} (${tenSP}) vào giỏ hàng!`);
        }

        function removeFromCart(index) {
            const cart = getCart();
            if (index >= 0 && index < cart.length) {
                cart.splice(index, 1);
                saveCart(cart);
                renderCartItems();
            }
        }

        function renderCartItems() {
            const cart = getCart();
            const listEl = document.getElementById('cartItemsList');
            const totalEl = document.getElementById('cartTotalPrice');
            if (!listEl) return;

            if (cart.length === 0) {
                listEl.innerHTML = `
                    <div class="py-12 text-center text-slate-400">
                        <i class="fa-solid fa-basket-shopping text-4xl mb-2 text-slate-300"></i>
                        <div class="font-bold text-slate-700 text-sm">Giỏ hàng Lookbook đang trống</div>
                        <div class="text-xs text-slate-400 mt-1">Hãy bấm "+ Giỏ" ở các mẫu Lookbook để thêm vải may đo nhé!</div>
                    </div>
                `;
                if (totalEl) totalEl.innerText = '0₫';
                return;
            }

            let total = 0;
            let html = '';
            cart.forEach((item, idx) => {
                total += item.tongTien;
                html += `
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                        <div class="flex items-center space-x-3 min-w-0">
                            <img src="${item.img}" class="w-12 h-14 object-cover rounded-lg border border-slate-200" alt="${item.tenSP}">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900 truncate">${item.maVai} ${item.tenSP}</div>
                                <div class="text-slate-500 text-[11px]">Định lượng: <strong class="text-slate-800">${item.soMet}m</strong> • ${new Intl.NumberFormat('vi-VN').format(item.donGia)}₫/m</div>
                                <div class="text-[10px] text-indigo-600 font-semibold flex items-center mt-0.5">
                                    <i class="fa-solid fa-tag mr-1 text-[9px]"></i> Ref: ${item.refCode} (Hoa hồng 5%)
                                </div>
                            </div>
                        </div>
                        <div class="text-right whitespace-nowrap pl-2">
                            <div class="font-extrabold text-slate-900 text-sm">${new Intl.NumberFormat('vi-VN').format(item.tongTien)}₫</div>
                            <button onclick="removeFromCart(${idx})" class="text-[11px] text-rose-500 hover:text-rose-700 font-semibold mt-1">
                                <i class="fa-solid fa-trash-can mr-0.5"></i> Xóa
                            </button>
                        </div>
                    </div>
                `;
            });

            listEl.innerHTML = html;
            if (totalEl) totalEl.innerText = new Intl.NumberFormat('vi-VN').format(total) + '₫';
        }

        function openCartModal() {
            renderCartItems();
            document.getElementById('cartModal').classList.remove('hidden');
        }

        function closeCartModal() { document.getElementById('cartModal').classList.add('hidden'); }

        async function checkoutCart() {
            const cart = getCart();
            if (cart.length === 0) {
                alert('Giỏ hàng của bạn đang trống! Hãy chọn ít nhất 1 mẫu vải trước khi thanh toán.');
                return;
            }

            let completed = 0;
            let commissionTotal = 0;
            let failureMessage = '';
            for (const item of cart) {
                const body = new URLSearchParams({
                    action: 'buy_via_affiliate',
                    refCode: item.refCode || '',
                    maVaiCode: item.maVai,
                    soMet: String(item.soMet),
                    donGia: String(item.donGia)
                });
                try {
                    const response = await fetch(window.location.pathname + window.location.search, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        failureMessage = result.message || 'Không thể ghi nhận đơn hàng.';
                        break;
                    }
                    completed++;
                    commissionTotal += Number(result.hoaHong || 0);
                } catch (error) {
                    failureMessage = 'Không kết nối được máy chủ để hoàn tất đơn hàng.';
                    break;
                }
            }

            if (completed > 0) saveCart(cart.slice(completed));
            if (failureMessage) {
                showQuickToast(`Đã hoàn tất ${completed}/${cart.length} đơn. ${failureMessage}`);
            } else {
                closeCartModal();
                showQuickToast(`Đặt mua thành công ${completed} sản phẩm. Hoa hồng ${new Intl.NumberFormat('vi-VN').format(commissionTotal)}₫ đã cộng vào ví thợ may.`);
            }
        }

        function showQuickToast(msg) {
            let toast = document.getElementById('quickToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'quickToast';
                toast.className = 'fixed bottom-5 right-5 z-50 flex items-center p-3.5 rounded-xl shadow-xl bg-slate-900 text-white text-xs font-bold transition-all duration-300';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-400 mr-2 text-sm"></i> <span>${msg}</span>`;
            toast.style.display = 'flex';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }

        document.addEventListener('DOMContentLoaded', updateCartBadge);

        function openTailorOrdersModal() { document.getElementById('tailorOrdersModal').classList.remove('hidden'); }
        function closeTailorOrdersModal() { document.getElementById('tailorOrdersModal').classList.add('hidden'); }

        function showQrModal(title, url) {
            document.getElementById('qrModalTitle').innerText = 'Mã QR - ' + title;
            document.getElementById('qrModalImg').src = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' + encodeURIComponent(url);
            document.getElementById('qrModalUrlText').innerText = url;
            document.getElementById('copyQrUrlBtn').onclick = function() {
                navigator.clipboard.writeText(url);
                alert('🎉 Đã sao chép liên kết:\n\n' + url);
            };
            document.getElementById('qrModal').classList.remove('hidden');
        }

        function closeQrModal() { document.getElementById('qrModal').classList.add('hidden'); }

        function previewUploadImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('uploadPlaceholder').classList.add('hidden');
                    const previewBox = document.getElementById('uploadPreviewBox');
                    previewBox.classList.remove('hidden');
                    document.getElementById('uploadPreviewImg').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        let currentBuyDonGia = 185000;

        function openAffiliateBuyModal(title, img, fabricCode, fabricName, donGia, metricStr, refCode, tailorName) {
            document.getElementById('buyModalTitle').innerText = title;
            document.getElementById('buyModalImg').src = img;
            document.getElementById('buyModalFabric').innerText = fabricCode + ' - ' + fabricName;
            document.getElementById('buyMaVaiCode').value = fabricCode;
            document.getElementById('buyDonGia').value = donGia;
            currentBuyDonGia = donGia;

            document.getElementById('buyModalPriceDisplay').innerText = new Intl.NumberFormat('vi-VN').format(donGia) + '₫';

            let parsedMet = 2.4;
            if (metricStr) {
                const match = metricStr.match(/([0-9]+(\.[0-9]+)?)/);
                if (match) parsedMet = parseFloat(match[0]);
            }
            document.getElementById('buySoMet').value = parsedMet;
            if (refCode) document.getElementById('buyRefCode').value = refCode;
            if (tailorName) document.getElementById('buyTailorNameDisplay').innerText = tailorName;

            calculateBuyTotal();
            document.getElementById('buyModal').classList.remove('hidden');
        }

        function closeBuyModal() { document.getElementById('buyModal').classList.add('hidden'); }

        function calculateBuyTotal() {
            const met = parseFloat(document.getElementById('buySoMet').value) || 0;
            const total = Math.round(met * currentBuyDonGia);
            const commission = Math.round(total * 0.05);

            document.getElementById('buyTotalDisplay').innerText = new Intl.NumberFormat('vi-VN').format(total) + '₫';
            document.getElementById('buyCommissionDisplay').innerText = '+' + new Intl.NumberFormat('vi-VN').format(commission) + '₫ hoa hồng cho thợ may';
        }

        function formatCurrencyInput(input) {
            let val = input.value.replace(/\D/g, '');
            input.value = val === '' ? '' : new Intl.NumberFormat('vi-VN').format(parseInt(val, 10));
        }

        function setWithdrawAmount(amount) {
            const input = document.getElementById('withdrawAmount');
            if (input) input.value = new Intl.NumberFormat('vi-VN').format(amount);
        }
    </script>
</body>
</html>