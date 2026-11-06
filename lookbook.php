<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Lookbook & Cửa Hàng</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">

    <!-- Header Navigation -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 sticky top-0 z-30 flex justify-between items-center shadow-xs">
        <div class="flex items-center space-x-3">
            <div class="bg-indigo-600 p-2.5 rounded-xl text-white shadow-md shadow-indigo-100">
                <i class="fa-solid fa-shirt"></i>
            </div>
            <div>
                <h1 class="font-bold text-base text-slate-900 leading-none">Aura Lookbook & Cửa Hàng</h1>
                <span class="text-[11px] text-indigo-600 font-semibold tracking-wide uppercase">Phân hệ Vải & Đồ May Sẵn</span>
            </div>
        </div>
        <div>
            <a href="http://localhost:8082" class="text-xs font-semibold text-slate-600 hover:text-indigo-600 border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Về Admin Portal
            </a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- CỘT TRÁI: CÔNG CỤ TÍNH MÉT VẢI THEO TÊN SẢN PHẨM -->
            <aside class="lg:col-span-1 space-y-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center">
                        <i class="fa-solid fa-calculator mr-2 text-indigo-600"></i> Công cụ tính vải cần mua
                    </h3>
                    <p class="text-xs text-slate-500">Nhập tên sản phẩm để tính chính xác số mét vải cần mua.</p>
                    
                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Tên sản phẩm muốn may</label>
                            <input id="calc-item-name" type="text" placeholder="VD: Mùng mền, Áo sơ mi, Rèm..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-800 focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="font-semibold text-slate-700 block mb-1">Loại định mức ước tính</label>
                            <select id="calc-category" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-slate-800 focus:outline-indigo-500">
                                <option value="1.5">Áo sơ mi / Áo kiểu (1.5 mét)</option>
                                <option value="1.8">Quần tây / Váy dài (1.8 mét)</option>
                                <option value="2.2">Bộ chăn gối nhỏ (2.2 mét)</option>
                                <option value="4.0">Bộ drap mùng mền lớn (4.0 mét)</option>
                                <option value="2.8">Rèm cửa sổ tiêu chuẩn (2.8 mét)</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="font-semibold text-slate-700 block mb-1">Số lượng cái</label>
                                <input id="calc-quantity" type="number" value="1" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-800">
                            </div>
                            <div>
                                <label class="font-semibold text-slate-700 block mb-1">Dự phòng mép (%)</label>
                                <input id="calc-buffer" type="number" value="10" min="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-slate-800">
                            </div>
                        </div>

                        <button type="button" onclick="calculateFabricUsage()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl transition shadow-sm cursor-pointer">
                            Tính số mét vải
                        </button>
                        
                        <div id="calc-result-box" class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-950 font-medium hidden text-center"></div>
                    </div>
                </div>

                <!-- Box Chống Review Ảo -->
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-2 text-xs">
                    <h4 class="font-bold text-slate-900 flex items-center">
                        <i class="fa-solid fa-shield-halved mr-2 text-emerald-600"></i> Đánh giá đã xác thực
                    </h4>
                    <p class="text-slate-500 leading-relaxed">Chỉ khách hàng có đơn hàng trạng thái <strong class="text-emerald-700">Đã giao</strong> mới được quyền gửi bài đánh giá sản phẩm.</p>
                </div>
            </aside>

            <!-- CỘT PHẢI: BỘ LỌC VẢI / ĐỒ CÓ SẴN + GRID SẢN PHẨM -->
            <main class="lg:col-span-3 space-y-6">
                <!-- Nút lọc 2 nhóm -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-bold uppercase text-slate-400 tracking-wider mr-2">Phân loại:</span>
                        <button id="btn-filter-all" onclick="filterCategory('all')" class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm transition cursor-pointer">Tất cả</button>
                        <button id="btn-filter-vai" onclick="filterCategory('vai')" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition cursor-pointer flex items-center space-x-1">
                            <i class="fa-solid fa-scroll text-[11px]"></i>
                            <span>Vải thời trang</span>
                        </button>
                        <button id="btn-filter-dosan" onclick="filterCategory('do_co_san')" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition cursor-pointer flex items-center space-x-1">
                            <i class="fa-solid fa-bed text-[11px]"></i>
                            <span>Đồ có sẵn (Mùng, Mền, Gối)</span>
                        </button>
                    </div>
                </div>

                <!-- Grid hiển thị danh sách sản phẩm có hình ảnh và trạng thái tồn kho -->
                <div id="product-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs col-span-3">
                        Đang tải danh sách sản phẩm...
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const BACKEND_API_URL = 'http://localhost:8002/api/v1/storefront/products';
        let currentFilter = 'all';

        // Dữ liệu ban đầu đồng bộ cấu trúc SQL
        const mockProducts = [
            {
                MaSP: 'SP01',
                TenSP: 'Vải Lụa Tơ Tằm Satin Tự Nhiên',
                NhomSP: 'Vải',
                HinhAnh: 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=500',
                MauSac: 'Xanh Navy',
                ChatLieuSize: 'Khổ 1.5m',
                GiaBan: 185000,
                TrangThaiHang: 'Còn hàng',
                DoDaiLienMachToiDa: 18
            },
            {
                MaSP: 'SP02',
                TenSP: 'Bộ Drap Mùng Mền Cotton Thắng Lợi',
                NhomSP: 'Đồ có sẵn',
                HinhAnh: 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=500',
                MauSac: 'Họa tiết hoa xanh',
                ChatLieuSize: '1.8m x 2.0m',
                GiaBan: 450000,
                TrangThaiHang: 'Còn hàng',
                DoDaiLienMachToiDa: null
            },
            {
                MaSP: 'SP03',
                TenSP: 'Vải Kaki Thun Co Giãn May Quần',
                NhomSP: 'Vải',
                HinhAnh: 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=500',
                MauSac: 'Đen Tuyển',
                ChatLieuSize: 'Khổ 1.6m',
                GiaBan: 120000,
                TrangThaiHang: 'Hết hàng',
                DoDaiLienMachToiDa: 0
            },
            {
                MaSP: 'SP04',
                TenSP: 'Ruột Gối Nằm Cao Su Thiên Nhiên',
                NhomSP: 'Đồ có sẵn',
                HinhAnh: 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?w=500',
                MauSac: 'Trắng Sữa',
                ChatLieuSize: '40cm x 60cm',
                GiaBan: 160000,
                TrangThaiHang: 'Còn hàng',
                DoDaiLienMachToiDa: null
            },
            {
                MaSP: 'SP05',
                TenSP: 'Vải Linen Tưng Tự Nhiên May Đầm',
                NhomSP: 'Vải',
                HinhAnh: 'https://images.unsplash.com/photo-1607344645866-009c320c5ab8?w=500',
                MauSac: 'Be Mộc',
                ChatLieuSize: 'Khổ 1.4m',
                GiaBan: 155000,
                TrangThaiHang: 'Còn hàng',
                DoDaiLienMachToiDa: 25
            },
            {
                MaSP: 'SP06',
                TenSP: 'Chiếu Trúc Hạt Đen Cao Cấp',
                NhomSP: 'Đồ có sẵn',
                HinhAnh: 'https://images.unsplash.com/photo-1582533561751-ef6f6ab93a2e?w=500',
                MauSac: 'Nâu Cổ Điển',
                ChatLieuSize: '1.6m x 2.0m',
                GiaBan: 680000,
                TrangThaiHang: 'Hết hàng',
                DoDaiLienMachToiDa: null
            }
        ];

        document.addEventListener('DOMContentLoaded', () => {
            loadProducts();
        });

        // 1. Tải và hiển thị sản phẩm
        function loadProducts() {
            fetch(`${BACKEND_API_URL}?nhom=${currentFilter}`)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data && res.data.length > 0) {
                        renderProductList(res.data);
                    } else {
                        renderLocalFiltered();
                    }
                })
                .catch(() => {
                    renderLocalFiltered();
                });
        }

        // 2. Lọc sản phẩm theo Vải hoặc Đồ có sẵn
        function renderLocalFiltered() {
            let filtered = mockProducts;
            if (currentFilter === 'vai') {
                filtered = mockProducts.filter(p => p.NhomSP.includes('Vải'));
            } else if (currentFilter === 'do_co_san') {
                filtered = mockProducts.filter(p => !p.NhomSP.includes('Vải'));
            }
            renderProductList(filtered);
        }

        function renderProductList(items) {
            const grid = document.getElementById('product-grid');
            if (!items || items.length === 0) {
                grid.innerHTML = `<div class="bg-white p-8 rounded-2xl border border-slate-200 text-center text-slate-400 text-xs col-span-3">Không có sản phẩm nào thuộc phân loại này.</div>`;
                return;
            }

            grid.innerHTML = '';
            items.forEach(item => {
                const isAvailable = item.TrangThaiHang === 'Còn hàng';
                const badgeClass = isAvailable 
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                    : 'bg-rose-50 text-rose-700 border-rose-200';

                grid.innerHTML += `
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-md transition">
                        <div class="relative h-44 overflow-hidden bg-slate-100">
                            <img src="${item.HinhAnh}" alt="${item.TenSP}" class="w-full h-full object-cover">
                            <span class="absolute top-2.5 left-2.5 text-[10px] font-bold px-2.5 py-1 rounded-lg border ${badgeClass}">
                                ${item.TrangThaiHang}
                            </span>
                        </div>
                        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider">${item.NhomSP}</span>
                                <h4 class="font-bold text-slate-900 text-sm line-clamp-1">${item.TenSP}</h4>
                                <p class="text-xs text-slate-500 mt-1">Màu: ${item.MauSac || 'Tiêu chuẩn'} • Kích thước/Khổ: ${item.ChatLieuSize || 'Mặc định'}</p>
                            </div>

                            ${item.DoDaiLienMachToiDa ? `
                                <div class="bg-amber-50 border border-amber-200 text-amber-900 text-[11px] p-2 rounded-xl">
                                    Cuộn lẻ dài tối đa: <strong>${item.DoDaiLienMachToiDa} mét</strong>
                                </div>
                            ` : ''}

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-indigo-600 font-extrabold text-sm">${item.GiaBan ? item.GiaBan.toLocaleString() + ' đ' : 'Liên hệ'}</span>
                                <button ${!isAvailable ? 'disabled' : ''} class="${isAvailable ? 'bg-slate-900 hover:bg-indigo-600' : 'bg-slate-200 cursor-not-allowed'} text-white text-xs font-semibold px-3 py-2 rounded-xl transition">
                                    ${isAvailable ? 'Mua ngay' : 'Hết hàng'}
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        // Chuyển đổi trạng thái nút lọc
        function filterCategory(type) {
            currentFilter = type;
            const btnAll = document.getElementById('btn-filter-all');
            const btnVai = document.getElementById('btn-filter-vai');
            const btnDoSan = document.getElementById('btn-filter-dosan');

            [btnAll, btnVai, btnDoSan].forEach(b => {
                b.className = "px-4 py-2 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition cursor-pointer";
            });

            if (type === 'all') btnAll.className = "px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm transition cursor-pointer";
            if (type === 'vai') btnVai.className = "px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm transition cursor-pointer";
            if (type === 'do_co_san') btnDoSan.className = "px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm transition cursor-pointer";

            loadProducts();
        }

        // 3. Tính toán mét vải theo tên sản phẩm người dùng nhập
        function calculateFabricUsage() {
            const name = document.getElementById('calc-item-name').value.trim() || 'Sản phẩm';
            const baseMeter = parseFloat(document.getElementById('calc-category').value);
            const quantity = parseInt(document.getElementById('calc-quantity').value) || 1;
            const buffer = parseFloat(document.getElementById('calc-buffer').value) || 0;

            const totalMeter = (baseMeter * quantity * (1 + buffer / 100)).toFixed(2);
            
            const resultBox = document.getElementById('calc-result-box');
            resultBox.innerHTML = `Mẫu: <strong>${name}</strong> (${quantity} cái)<br>Dự toán vải cần mua: <strong class="text-indigo-600 text-sm">${totalMeter} mét</strong>`;
            resultBox.classList.remove('hidden');
        }
    </script>
</body>
</html>