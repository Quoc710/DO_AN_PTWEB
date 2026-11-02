<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Config/database.php';

/**
 * ProductModel - Xử lý toàn bộ nghiệp vụ CSDL cho Module 4 (Aura Admin Portal)
 * Bao gồm: Thống kê Dashboard thực tế, CRUD Mẫu Vải, Đồng bộ Đơn hàng Odoo, Quản lý Voucher, Kiểm duyệt Đánh giá
 */
class ProductModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->ensureRichSeedData();
    }

    /* =====================================================================
       0. TỰ ĐỘNG BỔ SUNG DỮ LIỆU MẪU PHONG PHÚ (ĐỂ TEST CUỘN & THỐNG KÊ THẬT)
       ===================================================================== */

    /**
     * Tự động kiểm tra và nạp bổ sung dữ liệu mẫu (Mẫu vải, Đơn hàng, Voucher, Đánh giá)
     * nếu CSDL hiện tại chỉ mới có bộ dữ liệu tối thiểu ban đầu.
     */
    private function ensureRichSeedData(): void
    {
        try {
            // Kiểm tra xem đã nạp bộ dữ liệu mở rộng chưa dựa trên mã SKU VAI-COTTON-04
            $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM BIEN_THE_SP WHERE MaSKU = 'VAI-COTTON-04'");
            $stmtCheck->execute();
            if ((int) $stmtCheck->fetchColumn() > 0) {
                return;
            }

            $this->db->beginTransaction();

            // 1. Bổ sung thêm 7 Mẫu Vải CRUD (Tổng cộng 10 mẫu vải trên bảng CRUD)
            $extraFabrics = [
                ['Cotton Lụa Ai Cập Dệt Mật Độ Cao', 'Vải Cotton', 'VAI-COTTON-04', 'Khổ 1m60', '165 gsm', 'Xanh Navy', '#1e3a8a', 'fa-solid fa-shirt', 'bg-blue-50 border-blue-200 text-blue-700', 'cotton_egypt_navy.jpg', 'Đang bán Web', 240000, 315.0],
                ['Nhung Tuyết Pháp Ánh Kim Cổ Điển', 'Vải Nhung', 'VAI-VELVET-05', 'Khổ 1m50', '340 gsm', 'Đỏ Rượu Vang', '#881337', 'fa-solid fa-crown', 'bg-rose-50 border-rose-200 text-rose-700', 'velvet_burgundy.jpg', 'Đang bán Web', 390000, 85.5],
                ['Denim Selvedge Nhật Bản Nguyên Bản', 'Vải Denim', 'VAI-DENIM-06', 'Khổ 1m50', '410 gsm', 'Chàm Đậm', '#1e293b', 'fa-solid fa-layer-group', 'bg-slate-100 border-slate-300 text-slate-700', 'denim_selvedge_indigo.jpg', 'Đang bán Web', 295000, 14.5],
                ['Voan Tơ Chiffon Hàn Quốc Rủ Mềm', 'Vải Voan', 'VAI-CHIFFON-07', 'Khổ 1m48', '95 gsm', 'Hồng Phấn', '#fce7f3', 'fa-solid fa-wind', 'bg-pink-50 border-pink-200 text-pink-700', 'chiffon_pastel_pink.jpg', 'Đang bán Web', 155000, 260.0],
                ['Gấm Hoa Văn Hoàng Gia Dệt Nổi', 'Vải Gấm', 'VAI-BROCADE-09', 'Khổ 1m55', '310 gsm', 'Vàng Đồng', '#d97706', 'fa-solid fa-gem', 'bg-amber-50 border-amber-200 text-amber-700', 'brocade_royal_gold.jpg', 'Đang bán Web', 480000, 12.0],
                ['Sợi Khuynh Diệp Tencel Sinh Học Mát Lạnh', 'Vải Tencel', 'VAI-TENCEL-10', 'Khổ 1m60', '180 gsm', 'Xanh Bạc Hà', '#a7f3d0', 'fa-solid fa-leaf', 'bg-emerald-50 border-emerald-200 text-emerald-700', 'tencel_mint_eco.jpg', 'Đang bán Web', 265000, 190.0],
                ['Organza Ánh Ngọc Trai Dựng Phom Váy Cưới', 'Vải Organza', 'VAI-ORGANZA-11', 'Khổ 1m50', '110 gsm', 'Kem Tuyết', '#fffbeb', 'fa-solid fa-wand-magic-sparkles', 'bg-purple-50 border-purple-200 text-purple-700', 'organza_pearl_bridal.jpg', 'Tạm Hết Hàng', 350000, 0.0],
            ];

            $stmtSP = $this->db->prepare("INSERT INTO SAN_PHAM (TenSP, NhomSP) VALUES (:tenSP, :nhomSP)");
            $stmtBT = $this->db->prepare(
                "INSERT INTO BIEN_THE_SP (MaSP, DonViTinh, MauSac, ChatLieuSize, MaSKU, KhoVai, DinhLuongGSM, MaMauHex, IconClass, BadgeMauClass, HinhAnhMacro, TrangThaiWeb)
                 VALUES (:maSP, 'Mét', :mauSac, :chatLieuSize, :maSKU, :khoVai, :dinhLuongGSM, :maMauHex, :iconClass, :badgeMauClass, :hinhAnhMacro, :trangThaiWeb)"
            );
            $stmtBG = $this->db->prepare("INSERT INTO BANG_GIA (MaBienThe, LoaiKhach, DonGia) VALUES (:maBienThe, 'Bán lẻ Web', :donGia)");
            $stmtCV = $this->db->prepare("INSERT INTO CUON_VAI (MaBienThe, DoDaiBanDau, DoDaiConLai) VALUES (:maBienThe, :banDau, :conLai)");
            $stmtTK = $this->db->prepare("INSERT INTO TON_KHO (MaCuonVai, SoLuongTon) VALUES (:maCuonVai, :ton)");

            $newBienTheMap = [];
            foreach ($extraFabrics as $f) {
                $stmtSP->execute([':tenSP' => $f[0], ':nhomSP' => $f[1]]);
                $maSP = (int) $this->db->lastInsertId();

                $stmtBT->execute([
                    ':maSP'          => $maSP,
                    ':mauSac'        => $f[5],
                    ':chatLieuSize'  => $f[0] . ' • ' . $f[3] . ' • ' . $f[4],
                    ':maSKU'         => $f[2],
                    ':khoVai'        => $f[3],
                    ':dinhLuongGSM'  => $f[4],
                    ':maMauHex'      => $f[6],
                    ':iconClass'     => $f[7],
                    ':badgeMauClass' => $f[8],
                    ':hinhAnhMacro'  => $f[9],
                    ':trangThaiWeb'  => $f[10]
                ]);
                $maBienThe = (int) $this->db->lastInsertId();
                $newBienTheMap[$f[2]] = $maBienThe;

                $stmtBG->execute([':maBienThe' => $maBienThe, ':donGia' => $f[11]]);

                $banDau = max((float) $f[12], 100.0);
                $stmtCV->execute([':maBienThe' => $maBienThe, ':banDau' => $banDau, ':conLai' => $f[12]]);
                $maCuonVai = (int) $this->db->lastInsertId();

                $stmtTK->execute([':maCuonVai' => $maCuonVai, ':ton' => $f[12]]);
            }

            // 2. Bổ sung thêm Khách hàng & Đơn hàng Odoo (Tổng cộng 7 đơn hàng)
            $extraCustomers = [
                ['Đặng Ngọc Minh Châu', '0945222333', 'Đối tác Nhà may'],
                ['Phạm Quốc Bảo', '0977888999', 'Khách VIP'],
                ['Hoàng Mai Linh', '0903444555', 'Khách thân thiết'],
                ['Trịnh Gia Huy', '0918666777', 'Khách lẻ']
            ];
            $stmtKH = $this->db->prepare("INSERT INTO KHACH_HANG (TenKH, SDT, LoaiKhach, TongNo) VALUES (:ten, :sdt, :loai, 0)");
            $customerIds = [];
            foreach ($extraCustomers as $c) {
                $stmtKH->execute([':ten' => $c[0], ':sdt' => $c[1], ':loai' => $c[2]]);
                $customerIds[] = (int) $this->db->lastInsertId();
            }

            $extraOrders = [
                [$customerIds[0], '2025-05-20', 4800000.00, '#ORD-9879', '09:52:18', 'Sẵn sàng xuất', $newBienTheMap['VAI-COTTON-04'], 20.0, 240000.00],
                [$customerIds[1], '2025-05-20', 5850000.00, '#ORD-9878', '09:30:44', 'Sẵn sàng xuất', $newBienTheMap['VAI-VELVET-05'], 15.0, 390000.00],
                [$customerIds[2], '2025-05-19', 3100000.00, '#ORD-9877', '16:45:10', 'Sẵn sàng xuất', $newBienTheMap['VAI-CHIFFON-07'], 20.0, 155000.00],
                [$customerIds[3], '2025-05-18', 4800000.00, '#ORD-9876', '14:15:00', 'Đã đồng bộ Odoo', $newBienTheMap['VAI-BROCADE-09'], 10.0, 480000.00]
            ];

            $stmtPB = $this->db->prepare(
                "INSERT INTO PHIEU_BAN (MaKH, NgayBan, TongTien, MaDonCode, ThoiGianBan, TrangThaiERP)
                 VALUES (:maKH, :ngayBan, :tongTien, :maDonCode, :thoiGianBan, :trangThaiERP)"
            );
            $stmtCTPB = $this->db->prepare(
                "INSERT INTO CT_PHIEU_BAN (MaPhieuBan, MaBienThe, SoLuongBan, DonGiaBan)
                 VALUES (:maPB, :maBT, :soLuong, :donGia)"
            );

            foreach ($extraOrders as $o) {
                $stmtPB->execute([
                    ':maKH'         => $o[0],
                    ':ngayBan'      => $o[1],
                    ':tongTien'     => $o[2],
                    ':maDonCode'    => $o[3],
                    ':thoiGianBan'  => $o[4],
                    ':trangThaiERP' => $o[5]
                ]);
                $maPB = (int) $this->db->lastInsertId();
                $stmtCTPB->execute([
                    ':maPB'    => $maPB,
                    ':maBT'    => $o[6],
                    ':soLuong' => $o[7],
                    ':donGia'  => $o[8]
                ]);
            }

            // 3. Bổ sung thêm 4 Mã Voucher (Tổng cộng 6 mã Voucher)
            $extraVouchers = [
                [50000.00, '2025-08-15', 'LINEN50K', 'Giảm 50.000₫ cho đơn từ 300k', 'Hạn dùng: Đến 15/08/2025 • Áp dụng dòng Linen & Cotton', 'VNĐ', 200, 145, 1],
                [30.00, '2025-09-30', 'VIPTAILOR30', 'Chiết khấu 30% Đối tác Nhà may', 'Hạn dùng: Đến 30/09/2025 • Đơn sỉ từ 20 mét trở lên', '%', 50, 42, 1],
                [15.00, '2025-07-31', 'SUMMER15', 'Ưu đãi Bộ sưu tập Hè 15%', 'Hạn dùng: Đến 31/07/2025 • Áp dụng Tencel & Voan Chiffon', '%', 300, 189, 1],
                [150000.00, '2025-12-31', 'BRIDAL150K', 'Giảm 150.000₫ dòng Vải Cưới & Gấm', 'Hạn dùng: Đến 31/12/2025 • Đơn từ 1.500.000₫', 'VNĐ', 100, 28, 1]
            ];

            $stmtVoucher = $this->db->prepare(
                "INSERT IGNORE INTO VOUCHER (GiaTriVoucher, HanSuDung, MaCode, TenChuongTrinh, MoTaDieuKien, LoaiGiam, GioiHanLuot, SoLuotDaDung, TrangThai)
                 VALUES (:giaTri, :han, :code, :ten, :moTa, :loai, :gioiHan, :daDung, :trangThai)"
            );
            foreach ($extraVouchers as $v) {
                $stmtVoucher->execute([
                    ':giaTri'    => $v[0],
                    ':han'       => $v[1],
                    ':code'      => $v[2],
                    ':ten'       => $v[3],
                    ':moTa'      => $v[4],
                    ':loai'      => $v[5],
                    ':gioiHan'   => $v[6],
                    ':daDung'    => $v[7],
                    ':trangThai' => $v[8]
                ]);
            }

            // 4. Bổ sung thêm Đánh giá khách hàng (Đảm bảo có cả đánh giá Spam và đánh giá hợp lệ có ảnh để cuộn)
            // Khôi phục đánh giá Spam mẫu số 1 nếu trước đó người dùng đã bấm xóa khi test
            $stmtCheckSpam1 = $this->db->query("SELECT COUNT(*) FROM DANH_GIA_KHACH_HANG WHERE LoaiDanhGia LIKE '%Spam%'");
            if ((int) $stmtCheckSpam1->fetchColumn() === 0) {
                $this->db->exec(
                    "INSERT INTO DANH_GIA_KHACH_HANG (MaKH, SoSao, NoiDung, NgayDanhGia, MaBienThe, IPKhach, LoaiDanhGia, TrangThaiDuyet, IsGhim, SoAnhDinhKem)
                     VALUES (4, 1, 'Vải dở ẹc, mọi người qua trang bit.ly/mua-vai-re này xem hàng chuẩn hơn gấp trăm lần nha...', '2025-05-20', 2, '118.69.•••', 'Spam Link / Thô tục', 'Vi phạm', 0, 0)"
                );
            }

            $extraReviews = [
                [$customerIds[0], 5, 'Mình đặt 20m Cotton Lụa Ai Cập về may sơ mi cho khách, mình vải mịn mát, độ bóng tự nhiên cực kỳ sang.', '2025-05-20', $newBienTheMap['VAI-COTTON-04'], '113.161.44.12', 'Đã mua hàng thật', 'Đã duyệt', 1, 3],
                [4, 1, 'Nhận làm bằng giả, chạy quảng cáo giá rẻ liên hệ zalo 0999xxx ngay nhé http://spam-ads-link.xyz', '2025-05-20', $newBienTheMap['VAI-VELVET-05'], '171.244.•••', 'Spam Link / Thô tục', 'Vi phạm', 0, 0],
                [$customerIds[1], 5, 'Nhung tuyết Pháp lên đầm dạ hội đứng phom xuất sắc, tuyết nhung mịn đều, cắt không bị rụng lông.', '2025-05-19', $newBienTheMap['VAI-VELVET-05'], '27.72.88.101', 'Đã mua hàng thật', 'Chờ duyệt', 0, 2],
                [$customerIds[2], 4, 'Voan Chiffon Hàn màu hồng phấn ngọt nhẹ, vải mềm rủ, đóng gói cuộn cây rất kỹ không bị gãy nếp.', '2025-05-19', $newBienTheMap['VAI-CHIFFON-07'], '14.232.19.55', 'Đã mua hàng thật', 'Chờ duyệt', 0, 1],
                [4, 2, 'Shop làm ăn chán quá, bấm vào link t.me/nhom_vai_lau_gia_si để lấy sỉ rẻ bằng nửa giá shop!', '2025-05-18', $newBienTheMap['VAI-DENIM-06'], '42.112.•••', 'Spam Link / Thô tục', 'Vi phạm', 0, 0],
                [$customerIds[3], 5, 'Vải Tencel sinh học sờ mát lạnh tay, may bộ chăn ga khách khen tấm tắc. Sẽ ủng hộ kho dài lâu!', '2025-05-18', $newBienTheMap['VAI-TENCEL-10'], '115.78.12.90', 'Đã mua hàng thật', 'Đã duyệt', 0, 2],
                [2, 5, 'Gấm hoa văn dệt nổi sắc nét từng chi tiết, lên áo dài truyền thống cực kỳ tôn dáng.', '2025-05-17', $newBienTheMap['VAI-BROCADE-09'], '125.235.4.18', 'Đã mua hàng thật', 'Đã duyệt', 0, 0]
            ];

            $stmtDG = $this->db->prepare(
                "INSERT INTO DANH_GIA_KHACH_HANG (MaKH, SoSao, NoiDung, NgayDanhGia, MaBienThe, IPKhach, LoaiDanhGia, TrangThaiDuyet, IsGhim, SoAnhDinhKem)
                 VALUES (:maKH, :soSao, :noiDung, :ngay, :maBT, :ip, :loai, :trangThai, :isGhim, :soAnh)"
            );

            foreach ($extraReviews as $r) {
                $stmtDG->execute([
                    ':maKH'      => $r[0],
                    ':soSao'     => $r[1],
                    ':noiDung'   => $r[2],
                    ':ngay'      => $r[3],
                    ':maBT'      => $r[4],
                    ':ip'        => $r[5],
                    ':loai'      => $r[6],
                    ':trangThai' => $r[7],
                    ':isGhim'    => $r[8],
                    ':soAnh'     => $r[9]
                ]);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
        }
    }

    /* =====================================================================
       1. THỐNG KÊ TỔNG QUAN (TOP STATS CARDS) - ĐÚNG 100% DỮ LIỆU THẬT TRONG DB
       ===================================================================== */

    /**
     * Lấy 4 chỉ số thống kê trên Top Stats Cards khớp chính xác 100% với số lượng thực tế trong CSDL
     *
     * @return array<string, mixed>
     */
    public function getDashboardStats(): array
    {
        try {
            // 1. Doanh thu & số đơn thực tế từ bảng PHIEU_BAN
            $sqlOrders = "SELECT 
                            COUNT(*) AS so_don_db,
                            SUM(CASE WHEN TrangThaiERP = 'Sẵn sàng xuất' THEN 1 ELSE 0 END) AS don_san_sang,
                            COALESCE(SUM(TongTien), 0) AS tong_tien_db
                          FROM PHIEU_BAN";
            $orderRow = $this->db->query($sqlOrders)->fetch() ?: [
                'so_don_db'    => 0,
                'don_san_sang' => 0,
                'tong_tien_db' => 0
            ];

            $soDonDb = (int) $orderRow['so_don_db'];
            $donSanSang = (int) $orderRow['don_san_sang'];
            $tongTienDb = (float) $orderRow['tong_tien_db'];

            // 2. Thống kê mẫu vải Web & tồn kho cảnh báo thực tế
            $sqlFabrics = "SELECT 
                            COUNT(bt.MaBienThe) AS total_web_db,
                            SUM(CASE WHEN bt.TrangThaiWeb = 'Đang bán Web' AND COALESCE(tk.TongTon, 0) > 0 THEN 1 ELSE 0 END) AS online_web_db,
                            SUM(CASE WHEN bt.TrangThaiWeb != 'Đang bán Web' OR COALESCE(tk.TongTon, 0) <= 0 THEN 1 ELSE 0 END) AS out_of_stock_db,
                            SUM(CASE WHEN COALESCE(tk.TongTon, 0) < 20 THEN 1 ELSE 0 END) AS low_stock_db
                           FROM BIEN_THE_SP bt
                           LEFT JOIN (
                               SELECT cv.MaBienThe, SUM(t.SoLuongTon) AS TongTon
                               FROM CUON_VAI cv
                               JOIN TON_KHO t ON cv.MaCuonVai = t.MaCuonVai
                               GROUP BY cv.MaBienThe
                           ) tk ON bt.MaBienThe = tk.MaBienThe
                           WHERE bt.TrangThaiWeb IS NULL OR bt.TrangThaiWeb != 'Chỉ đồng bộ ERP'";
            $fabricRow = $this->db->query($sqlFabrics)->fetch() ?: [
                'total_web_db'    => 0,
                'online_web_db'   => 0,
                'out_of_stock_db' => 0,
                'low_stock_db'    => 0
            ];

            $totalWebDb = (int) $fabricRow['total_web_db'];
            $onlineWebDb = (int) $fabricRow['online_web_db'];
            $outOfStockDb = (int) $fabricRow['out_of_stock_db'];
            $lowStockDb = (int) $fabricRow['low_stock_db'];
            $tyLeOnline = $totalWebDb > 0 ? round(($onlineWebDb / $totalWebDb) * 100, 1) : 0.0;

            return [
                'doanh_thu_hom_nay'       => $tongTienDb,
                'doanh_thu_formatted'     => number_format($tongTienDb, 0, ',', '.') . '₫',
                'doanh_thu_thuc_db'       => $tongTienDb,
                'don_hang_moi'            => $soDonDb,
                'don_hang_thuc_db'        => $soDonDb,
                'don_cho_duyet'           => $donSanSang,
                'ton_kho_canh_bao'        => $lowStockDb,
                'ton_kho_canh_bao_label'  => str_pad((string) $lowStockDb, 2, '0', STR_PAD_LEFT) . ' SKU',
                'tong_mau_vai_online'     => $onlineWebDb,
                'tong_mau_vai'            => $totalWebDb,
                'so_mau_het_hang'         => $outOfStockDb,
                'ty_le_online'            => $tyLeOnline
            ];
        } catch (PDOException $e) {
            throw new RuntimeException('Lỗi khi truy vấn thống kê Dashboard: ' . $e->getMessage(), 0, $e);
        }
    }

    /* =====================================================================
       2. QUẢN LÝ MẪU VẢI MỚI - CRUD (SAN_PHAM, BIEN_THE_SP, BANG_GIA, CUON_VAI, TON_KHO)
       ===================================================================== */

    /**
     * Lấy danh sách mẫu vải hiển thị trên bảng CRUD
     * JOIN giữa SAN_PHAM, BIEN_THE_SP, BANG_GIA, CUON_VAI, TON_KHO
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllFabrics(): array
    {
        try {
            $sql = "SELECT 
                        bt.MaBienThe,
                        bt.MaSP,
                        sp.TenSP,
                        sp.NhomSP,
                        bt.DonViTinh,
                        bt.MauSac,
                        bt.ChatLieuSize,
                        bt.MaSKU,
                        bt.KhoVai,
                        bt.DinhLuongGSM,
                        bt.MaMauHex,
                        bt.IconClass,
                        bt.BadgeMauClass,
                        bt.HinhAnhMacro,
                        bt.TrangThaiWeb,
                        COALESCE(bg.DonGia, 0) AS DonGia,
                        COALESCE(SUM(cv.DoDaiBanDau), 0) AS DoDaiBanDau,
                        COALESCE(SUM(cv.DoDaiConLai), 0) AS DoDaiConLai,
                        COALESCE(SUM(tk.SoLuongTon), 0) AS SoLuongTon
                    FROM BIEN_THE_SP bt
                    INNER JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                    LEFT JOIN BANG_GIA bg ON bt.MaBienThe = bg.MaBienThe AND bg.LoaiKhach = 'Bán lẻ Web'
                    LEFT JOIN CUON_VAI cv ON bt.MaBienThe = cv.MaBienThe
                    LEFT JOIN TON_KHO tk ON cv.MaCuonVai = tk.MaCuonVai
                    WHERE bt.TrangThaiWeb IS NULL OR bt.TrangThaiWeb != 'Chỉ đồng bộ ERP'
                    GROUP BY 
                        bt.MaBienThe, bt.MaSP, sp.TenSP, sp.NhomSP, bt.DonViTinh,
                        bt.MauSac, bt.ChatLieuSize, bt.MaSKU, bt.KhoVai, bt.DinhLuongGSM,
                        bt.MaMauHex, bt.IconClass, bt.BadgeMauClass, bt.HinhAnhMacro,
                        bt.TrangThaiWeb, bg.DonGia
                    ORDER BY bt.MaBienThe ASC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Lỗi khi lấy danh sách mẫu vải: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Lấy chi tiết 1 mẫu vải theo MaBienThe
     *
     * @param int $maBienThe
     * @return array<string, mixed>|null
     */
    public function getFabricById(int $maBienThe): ?array
    {
        try {
            $sql = "SELECT 
                        bt.MaBienThe,
                        bt.MaSP,
                        sp.TenSP,
                        sp.NhomSP,
                        bt.DonViTinh,
                        bt.MauSac,
                        bt.ChatLieuSize,
                        bt.MaSKU,
                        bt.KhoVai,
                        bt.DinhLuongGSM,
                        bt.MaMauHex,
                        bt.IconClass,
                        bt.BadgeMauClass,
                        bt.HinhAnhMacro,
                        bt.TrangThaiWeb,
                        COALESCE(bg.DonGia, 0) AS DonGia,
                        COALESCE(SUM(tk.SoLuongTon), 0) AS SoLuongTon
                    FROM BIEN_THE_SP bt
                    INNER JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                    LEFT JOIN BANG_GIA bg ON bt.MaBienThe = bg.MaBienThe AND bg.LoaiKhach = 'Bán lẻ Web'
                    LEFT JOIN CUON_VAI cv ON bt.MaBienThe = cv.MaBienThe
                    LEFT JOIN TON_KHO tk ON cv.MaCuonVai = tk.MaCuonVai
                    WHERE bt.MaBienThe = :maBienThe
                    GROUP BY 
                        bt.MaBienThe, bt.MaSP, sp.TenSP, sp.NhomSP, bt.DonViTinh,
                        bt.MauSac, bt.ChatLieuSize, bt.MaSKU, bt.KhoVai, bt.DinhLuongGSM,
                        bt.MaMauHex, bt.IconClass, bt.BadgeMauClass, bt.HinhAnhMacro,
                        bt.TrangThaiWeb, bg.DonGia";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':maBienThe' => $maBienThe]);
            $row = $stmt->fetch();

            return $row !== false ? $row : null;
        } catch (PDOException $e) {
            throw new RuntimeException('Lỗi khi lấy thông tin mẫu vải: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Thêm mẫu vải mới vào đầy đủ 5 bảng: SAN_PHAM, BIEN_THE_SP, BANG_GIA, CUON_VAI, TON_KHO
     * Sử dụng PDO Transaction để đảm bảo tính toàn vẹn dữ liệu
     *
     * @param array<string, mixed> $data
     * @return int MaBienThe vừa tạo
     */
    public function createFabric(array $data): int
    {
        $tenSP = trim((string) ($data['ten_sp'] ?? ''));
        $maSKU = strtoupper(trim((string) ($data['ma_sku'] ?? '')));
        $khoVai = trim((string) ($data['kho_vai'] ?? '1m50'));
        $dinhLuongGSM = trim((string) ($data['dinh_luong_gsm'] ?? '220 gsm'));
        $mauSac = trim((string) ($data['mau_sac'] ?? 'Be Mộc'));
        $maMauHex = trim((string) ($data['ma_mau_hex'] ?? '#e8dec8'));
        $donGia = max(0.0, (float) ($data['don_gia'] ?? 0));
        $soLuongTon = max(0.0, (float) ($data['so_luong_ton'] ?? 0));
        $hinhAnhMacro = trim((string) ($data['hinh_anh_macro'] ?? 'linen_macroscopic_ivory.jpg'));

        if ($tenSP === '') {
            throw new InvalidArgumentException('Tên mẫu vải & chất liệu không được để trống.');
        }

        if ($maSKU === '') {
            $maSKU = 'VAI-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
        }

        if ($khoVai !== '' && stripos($khoVai, 'Khổ') === false) {
            $khoVai = 'Khổ ' . $khoVai;
        }
        if ($dinhLuongGSM !== '' && stripos($dinhLuongGSM, 'gsm') === false) {
            $dinhLuongGSM .= ' gsm';
        }

        $nhomSP = $this->detectFabricGroup($tenSP);
        [$iconClass, $badgeMauClass] = $this->detectIconAndBadge($tenSP, $data);
        $trangThaiWeb = !empty($data['trang_thai_web'])
            ? trim((string) $data['trang_thai_web'])
            : ($soLuongTon > 0 ? 'Đang bán Web' : 'Tạm Hết Hàng');
        $chatLieuSize = sprintf('%s • %s • %s', $tenSP, $khoVai, $dinhLuongGSM);

        try {
            $this->db->beginTransaction();

            $stmtSP = $this->db->prepare(
                "INSERT INTO SAN_PHAM (TenSP, NhomSP) VALUES (:tenSP, :nhomSP)"
            );
            $stmtSP->execute([
                ':tenSP'  => $tenSP,
                ':nhomSP' => $nhomSP
            ]);
            $maSP = (int) $this->db->lastInsertId();

            $stmtBT = $this->db->prepare(
                "INSERT INTO BIEN_THE_SP (
                    MaSP, DonViTinh, MauSac, ChatLieuSize,
                    MaSKU, KhoVai, DinhLuongGSM, MaMauHex, IconClass, BadgeMauClass, HinhAnhMacro, TrangThaiWeb
                ) VALUES (
                    :maSP, 'Mét', :mauSac, :chatLieuSize,
                    :maSKU, :khoVai, :dinhLuongGSM, :maMauHex, :iconClass, :badgeMauClass, :hinhAnhMacro, :trangThaiWeb
                )"
            );
            $stmtBT->execute([
                ':maSP'          => $maSP,
                ':mauSac'        => $mauSac,
                ':chatLieuSize'  => $chatLieuSize,
                ':maSKU'         => $maSKU,
                ':khoVai'        => $khoVai,
                ':dinhLuongGSM'  => $dinhLuongGSM,
                ':maMauHex'      => $maMauHex,
                ':iconClass'     => $iconClass,
                ':badgeMauClass' => $badgeMauClass,
                ':hinhAnhMacro'  => $hinhAnhMacro,
                ':trangThaiWeb'  => $trangThaiWeb
            ]);
            $maBienThe = (int) $this->db->lastInsertId();

            $stmtBG = $this->db->prepare(
                "INSERT INTO BANG_GIA (MaBienThe, LoaiKhach, DonGia)
                 VALUES (:maBienThe, 'Bán lẻ Web', :donGia)"
            );
            $stmtBG->execute([
                ':maBienThe' => $maBienThe,
                ':donGia'    => $donGia
            ]);

            $doDaiBanDau = max($soLuongTon, 100.00);
            $stmtCV = $this->db->prepare(
                "INSERT INTO CUON_VAI (MaBienThe, DoDaiBanDau, DoDaiConLai)
                 VALUES (:maBienThe, :doDaiBanDau, :doDaiConLai)"
            );
            $stmtCV->execute([
                ':maBienThe'   => $maBienThe,
                ':doDaiBanDau' => $doDaiBanDau,
                ':doDaiConLai' => $soLuongTon
            ]);
            $maCuonVai = (int) $this->db->lastInsertId();

            $stmtTK = $this->db->prepare(
                "INSERT INTO TON_KHO (MaCuonVai, SoLuongTon)
                 VALUES (:maCuonVai, :soLuongTon)"
            );
            $stmtTK->execute([
                ':maCuonVai'  => $maCuonVai,
                ':soLuongTon' => $soLuongTon
            ]);

            $this->db->commit();
            return $maBienThe;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new RuntimeException('Không thể thêm mẫu vải mới: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Cập nhật thông tin mẫu vải trên cả 5 bảng bằng Transaction
     *
     * @param int $maBienThe
     * @param array<string, mixed> $data
     * @return bool
     */
    public function updateFabric(int $maBienThe, array $data): bool
    {
        $existing = $this->getFabricById($maBienThe);
        if ($existing === null) {
            throw new InvalidArgumentException('Không tìm thấy mẫu vải cần cập nhật (ID: ' . $maBienThe . ').');
        }

        $maSP = (int) $existing['MaSP'];
        $tenSP = trim((string) ($data['ten_sp'] ?? $existing['TenSP']));
        $maSKU = strtoupper(trim((string) ($data['ma_sku'] ?? $existing['MaSKU'])));
        $khoVai = trim((string) ($data['kho_vai'] ?? $existing['KhoVai']));
        $dinhLuongGSM = trim((string) ($data['dinh_luong_gsm'] ?? $existing['DinhLuongGSM']));
        $mauSac = trim((string) ($data['mau_sac'] ?? $existing['MauSac']));
        $maMauHex = trim((string) ($data['ma_mau_hex'] ?? $existing['MaMauHex']));
        $donGia = isset($data['don_gia']) ? max(0.0, (float) $data['don_gia']) : (float) $existing['DonGia'];
        $soLuongTon = isset($data['so_luong_ton']) ? max(0.0, (float) $data['so_luong_ton']) : (float) $existing['SoLuongTon'];
        $hinhAnhMacro = trim((string) ($data['hinh_anh_macro'] ?? $existing['HinhAnhMacro']));

        if ($khoVai !== '' && stripos($khoVai, 'Khổ') === false) {
            $khoVai = 'Khổ ' . $khoVai;
        }
        if ($dinhLuongGSM !== '' && stripos($dinhLuongGSM, 'gsm') === false) {
            $dinhLuongGSM .= ' gsm';
        }

        $nhomSP = $this->detectFabricGroup($tenSP);
        $trangThaiWeb = !empty($data['trang_thai_web'])
            ? trim((string) $data['trang_thai_web'])
            : ($soLuongTon > 0 ? 'Đang bán Web' : 'Tạm Hết Hàng');
        $chatLieuSize = sprintf('%s • %s • %s', $tenSP, $khoVai, $dinhLuongGSM);

        try {
            $this->db->beginTransaction();

            $stmtSP = $this->db->prepare(
                "UPDATE SAN_PHAM SET TenSP = :tenSP, NhomSP = :nhomSP WHERE MaSP = :maSP"
            );
            $stmtSP->execute([
                ':tenSP'  => $tenSP,
                ':nhomSP' => $nhomSP,
                ':maSP'   => $maSP
            ]);

            $stmtBT = $this->db->prepare(
                "UPDATE BIEN_THE_SP
                 SET MauSac       = :mauSac,
                     ChatLieuSize = :chatLieuSize,
                     MaSKU        = :maSKU,
                     KhoVai       = :khoVai,
                     DinhLuongGSM = :dinhLuongGSM,
                     MaMauHex     = :maMauHex,
                     HinhAnhMacro = :hinhAnhMacro,
                     TrangThaiWeb = :trangThaiWeb
                 WHERE MaBienThe = :maBienThe"
            );
            $stmtBT->execute([
                ':mauSac'       => $mauSac,
                ':chatLieuSize' => $chatLieuSize,
                ':maSKU'        => $maSKU,
                ':khoVai'       => $khoVai,
                ':dinhLuongGSM' => $dinhLuongGSM,
                ':maMauHex'     => $maMauHex,
                ':hinhAnhMacro' => $hinhAnhMacro,
                ':trangThaiWeb' => $trangThaiWeb,
                ':maBienThe'    => $maBienThe
            ]);

            $stmtCheckBG = $this->db->prepare(
                "SELECT MaBangGia FROM BANG_GIA WHERE MaBienThe = :maBienThe AND LoaiKhach = 'Bán lẻ Web' LIMIT 1"
            );
            $stmtCheckBG->execute([':maBienThe' => $maBienThe]);
            $bgRow = $stmtCheckBG->fetch();

            if ($bgRow) {
                $stmtUpdateBG = $this->db->prepare(
                    "UPDATE BANG_GIA SET DonGia = :donGia WHERE MaBangGia = :maBangGia"
                );
                $stmtUpdateBG->execute([
                    ':donGia'    => $donGia,
                    ':maBangGia' => $bgRow['MaBangGia']
                ]);
            } else {
                $stmtInsertBG = $this->db->prepare(
                    "INSERT INTO BANG_GIA (MaBienThe, LoaiKhach, DonGia) VALUES (:maBienThe, 'Bán lẻ Web', :donGia)"
                );
                $stmtInsertBG->execute([
                    ':maBienThe' => $maBienThe,
                    ':donGia'    => $donGia
                ]);
            }

            $stmtCheckCV = $this->db->prepare(
                "SELECT MaCuonVai, DoDaiBanDau FROM CUON_VAI WHERE MaBienThe = :maBienThe ORDER BY MaCuonVai ASC LIMIT 1"
            );
            $stmtCheckCV->execute([':maBienThe' => $maBienThe]);
            $cvRow = $stmtCheckCV->fetch();

            if ($cvRow) {
                $maCuonVai = (int) $cvRow['MaCuonVai'];
                $doDaiBanDau = max((float) $cvRow['DoDaiBanDau'], $soLuongTon, 100.00);

                $stmtUpdateCV = $this->db->prepare(
                    "UPDATE CUON_VAI 
                     SET DoDaiBanDau = :doDaiBanDau, DoDaiConLai = :doDaiConLai 
                     WHERE MaCuonVai = :maCuonVai"
                );
                $stmtUpdateCV->execute([
                    ':doDaiBanDau' => $doDaiBanDau,
                    ':doDaiConLai' => $soLuongTon,
                    ':maCuonVai'   => $maCuonVai
                ]);

                $stmtUpdateTK = $this->db->prepare(
                    "UPDATE TON_KHO SET SoLuongTon = :soLuongTon WHERE MaCuonVai = :maCuonVai"
                );
                $stmtUpdateTK->execute([
                    ':soLuongTon' => $soLuongTon,
                    ':maCuonVai'  => $maCuonVai
                ]);
            } else {
                $doDaiBanDau = max($soLuongTon, 100.00);
                $stmtInsertCV = $this->db->prepare(
                    "INSERT INTO CUON_VAI (MaBienThe, DoDaiBanDau, DoDaiConLai) VALUES (:maBienThe, :doDaiBanDau, :doDaiConLai)"
                );
                $stmtInsertCV->execute([
                    ':maBienThe'   => $maBienThe,
                    ':doDaiBanDau' => $doDaiBanDau,
                    ':doDaiConLai' => $soLuongTon
                ]);
                $maCuonVai = (int) $this->db->lastInsertId();

                $stmtInsertTK = $this->db->prepare(
                    "INSERT INTO TON_KHO (MaCuonVai, SoLuongTon) VALUES (:maCuonVai, :soLuongTon)"
                );
                $stmtInsertTK->execute([
                    ':maCuonVai'  => $maCuonVai,
                    ':soLuongTon' => $soLuongTon
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new RuntimeException('Không thể cập nhật mẫu vải: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Xóa mẫu vải khỏi hệ thống bằng Transaction
     *
     * @param int $maBienThe
     * @return bool
     */
    public function deleteFabric(int $maBienThe): bool
    {
        $existing = $this->getFabricById($maBienThe);
        if ($existing === null) {
            throw new InvalidArgumentException('Mẫu vải không tồn tại hoặc đã bị xóa.');
        }

        $maSP = (int) $existing['MaSP'];

        try {
            $this->db->beginTransaction();

            $stmtUnlinkReviews = $this->db->prepare(
                "UPDATE DANH_GIA_KHACH_HANG SET MaBienThe = NULL WHERE MaBienThe = :maBienThe"
            );
            $stmtUnlinkReviews->execute([':maBienThe' => $maBienThe]);

            $stmtDeleteTK = $this->db->prepare(
                "DELETE tk FROM TON_KHO tk
                 INNER JOIN CUON_VAI cv ON tk.MaCuonVai = cv.MaCuonVai
                 WHERE cv.MaBienThe = :maBienThe"
            );
            $stmtDeleteTK->execute([':maBienThe' => $maBienThe]);

            $stmtDeleteCV = $this->db->prepare("DELETE FROM CUON_VAI WHERE MaBienThe = :maBienThe");
            $stmtDeleteCV->execute([':maBienThe' => $maBienThe]);

            $stmtDeleteBG = $this->db->prepare("DELETE FROM BANG_GIA WHERE MaBienThe = :maBienThe");
            $stmtDeleteBG->execute([':maBienThe' => $maBienThe]);

            $stmtDeleteBT = $this->db->prepare("DELETE FROM BIEN_THE_SP WHERE MaBienThe = :maBienThe");
            $stmtDeleteBT->execute([':maBienThe' => $maBienThe]);

            $stmtCountBT = $this->db->prepare("SELECT COUNT(*) FROM BIEN_THE_SP WHERE MaSP = :maSP");
            $stmtCountBT->execute([':maSP' => $maSP]);
            if ((int) $stmtCountBT->fetchColumn() === 0) {
                $stmtDeleteSP = $this->db->prepare("DELETE FROM SAN_PHAM WHERE MaSP = :maSP");
                $stmtDeleteSP->execute([':maSP' => $maSP]);
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new RuntimeException('Không thể xóa mẫu vải: ' . $e->getMessage(), 0, $e);
        }
    }

    /* =====================================================================
       3. ĐỒNG BỘ ĐƠN HÀNG & XUẤT EXCEL/CSV CHUẨN ODOO ERP
       ===================================================================== */

    /**
     * Lấy danh sách đơn hàng hiển thị ở bảng đồng bộ Odoo (có hỗ trợ lọc ngày & trạng thái)
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getOdooOrders(array $filters = []): array
    {
        try {
            $where = [];
            $params = [];

            if (!empty($filters['from_date'])) {
                $where[] = 'pb.NgayBan >= :from_date';
                $params[':from_date'] = $filters['from_date'];
            }
            if (!empty($filters['to_date'])) {
                $where[] = 'pb.NgayBan <= :to_date';
                $params[':to_date'] = $filters['to_date'];
            }

            $statusFilter = $filters['status'] ?? 'all';
            if ($statusFilter === 'ready') {
                $where[] = "pb.TrangThaiERP = 'Sẵn sàng xuất'";
            } elseif ($statusFilter === 'voucher_only') {
                $where[] = 'EXISTS (SELECT 1 FROM PHIEU_BAN_VOUCHER pbv WHERE pbv.MaPhieuBan = pb.MaPhieuBan)';
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $sql = "SELECT 
                        pb.MaPhieuBan,
                        pb.MaDonCode,
                        pb.NgayBan,
                        pb.ThoiGianBan,
                        pb.TongTien,
                        pb.TrangThaiERP,
                        kh.MaKH,
                        kh.TenKH,
                        kh.SDT,
                        ctpb.MaCTPhieuBan,
                        ctpb.SoLuongBan,
                        ctpb.DonGiaBan,
                        bt.MaBienThe,
                        bt.MaSKU,
                        sp.TenSP
                    FROM PHIEU_BAN pb
                    INNER JOIN KHACH_HANG kh ON pb.MaKH = kh.MaKH
                    INNER JOIN CT_PHIEU_BAN ctpb ON pb.MaPhieuBan = ctpb.MaPhieuBan
                    INNER JOIN BIEN_THE_SP bt ON ctpb.MaBienThe = bt.MaBienThe
                    INNER JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                    {$whereClause}
                    ORDER BY pb.MaPhieuBan ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            foreach ($rows as &$row) {
                $row['SDT_Masked'] = $this->maskPhoneNumber((string) ($row['SDT'] ?? ''));
            }
            unset($row);

            return $rows;
        } catch (PDOException $e) {
            throw new RuntimeException('Lỗi khi lấy danh sách đơn hàng Odoo: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Lấy dữ liệu chuẩn cấu trúc sales.order của Odoo ERP để xuất file CSV/Excel
     *
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function getOdooExportData(array $filters = []): array
    {
        $orders = $this->getOdooOrders($filters);
        $exportRows = [];

        foreach ($orders as $order) {
            $rawCode = ltrim((string) $order['MaDonCode'], '#');
            $externalId = 'aura_sale_' . strtolower(str_replace('-', '_', $rawCode));

            $exportRows[] = [
                'External ID'              => $externalId,
                'Order Reference'          => (string) $order['MaDonCode'],
                'Customer'                 => (string) $order['TenKH'],
                'Phone'                    => (string) $order['SDT'],
                'Order Date'               => trim((string) $order['NgayBan'] . ' ' . (string) $order['ThoiGianBan']),
                'Order Lines/Product SKU'  => (string) $order['MaSKU'],
                'Order Lines/Quantity (m)' => number_format((float) $order['SoLuongBan'], 1, '.', ''),
                'Total Amount'             => number_format((float) $order['TongTien'], 0, '.', '')
            ];
        }

        return $exportRows;
    }

    /* =====================================================================
       4. QUẢN LÝ VOUCHER & KHUYẾN MÃI (VOUCHER)
       ===================================================================== */

    /**
     * Lấy danh sách tất cả các mã Voucher đang có
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllVouchers(): array
    {
        try {
            $sql = "SELECT 
                        MaVoucher,
                        GiaTriVoucher,
                        HanSuDung,
                        MaCode,
                        TenChuongTrinh,
                        MoTaDieuKien,
                        LoaiGiam,
                        GioiHanLuot,
                        SoLuotDaDung,
                        TrangThai
                    FROM VOUCHER
                    ORDER BY MaVoucher ASC";
            $stmt = $this->db->query($sql);
            $vouchers = $stmt->fetchAll();

            foreach ($vouchers as &$voucher) {
                $gioiHan = max(1, (int) $voucher['GioiHanLuot']);
                $daDung = (int) $voucher['SoLuotDaDung'];
                $voucher['PhanTramDaDung'] = min(100, round(($daDung / $gioiHan) * 100, 1));
            }
            unset($voucher);

            return $vouchers;
        } catch (PDOException $e) {
            throw new RuntimeException('Lỗi khi lấy danh sách Voucher: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Tạo mã giảm giá (Voucher) mới có chọn hạn dùng tùy chỉnh
     *
     * @param array<string, mixed> $data
     * @return int MaVoucher vừa tạo
     */
    public function createVoucher(array $data): int
    {
        $maCode = strtoupper(trim((string) ($data['ma_code'] ?? '')));
        $giaTri = (float) ($data['gia_tri'] ?? 0);
        $loaiGiam = trim((string) ($data['loai_giam'] ?? '%'));
        $gioiHanLuot = max(1, (int) ($data['gioi_han_luot'] ?? 100));
        $hanSuDung = !empty($data['han_su_dung']) ? trim((string) $data['han_su_dung']) : null;

        if ($maCode === '') {
            throw new InvalidArgumentException('Mã Code Voucher không được để trống.');
        }
        if ($giaTri <= 0) {
            throw new InvalidArgumentException('Mức giảm giá phải lớn hơn 0.');
        }

        if (!in_array($loaiGiam, ['%', 'VNĐ'], true)) {
            $loaiGiam = $giaTri > 100 ? 'VNĐ' : '%';
        }

        if (!empty($data['ten_chuong_trinh'])) {
            $tenChuongTrinh = trim((string) $data['ten_chuong_trinh']);
        } else {
            $tenChuongTrinh = $loaiGiam === '%'
                ? sprintf('Giảm %s%% cho đơn hàng vải', rtrim(rtrim(number_format($giaTri, 2, '.', ''), '0'), '.'))
                : sprintf('Giảm trực tiếp %s₫', number_format($giaTri, 0, ',', '.'));
        }

        if (!empty($data['mo_ta_dieu_kien'])) {
            $moTaDieuKien = trim((string) $data['mo_ta_dieu_kien']);
        } else {
            if ($hanSuDung !== null && $hanSuDung !== '') {
                $hanFormatted = date('d/m/Y', strtotime($hanSuDung));
                $moTaDieuKien = sprintf('Hạn dùng: Đến %s • Áp dụng toàn bộ mẫu vải Web', $hanFormatted);
            } else {
                $moTaDieuKien = 'Hạn dùng: Không giới hạn • Áp dụng toàn bộ mẫu vải Web';
                $hanSuDung = null;
            }
        }

        try {
            $sql = "INSERT INTO VOUCHER (
                        GiaTriVoucher, HanSuDung, MaCode, TenChuongTrinh,
                        MoTaDieuKien, LoaiGiam, GioiHanLuot, SoLuotDaDung, TrangThai
                    ) VALUES (
                        :giaTri, :hanSuDung, :maCode, :tenChuongTrinh,
                        :moTaDieuKien, :loaiGiam, :gioiHanLuot, 0, 1
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':giaTri'         => $giaTri,
                ':hanSuDung'      => $hanSuDung,
                ':maCode'         => $maCode,
                ':tenChuongTrinh' => $tenChuongTrinh,
                ':moTaDieuKien'   => $moTaDieuKien,
                ':loaiGiam'       => $loaiGiam,
                ':gioiHanLuot'    => $gioiHanLuot
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new RuntimeException('Không thể tạo mã Voucher mới (Mã có thể đã tồn tại): ' . $e->getMessage(), 0, $e);
        }
    }

    /* =====================================================================
       5. DUYỆT ĐÁNH GIÁ & KIỂM DUYỆT BÌNH LUẬN (DANH_GIA_KHACH_HANG)
       ===================================================================== */

    /**
     * Đếm chính xác số lượng đánh giá theo từng phân loại (Tất cả, Nghi vấn Spam, Có ảnh thật, Cần xem)
     *
     * @return array<string, int>
     */
    public function getReviewCounts(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_count,
                        SUM(CASE WHEN (LoaiDanhGia LIKE '%Spam%' OR TrangThaiDuyet = 'Vi phạm' OR SoSao <= 2) THEN 1 ELSE 0 END) AS spam_count,
                        SUM(CASE WHEN SoAnhDinhKem > 0 THEN 1 ELSE 0 END) AS photo_count,
                        SUM(CASE WHEN TrangThaiDuyet IN ('Chờ duyệt', 'Vi phạm') THEN 1 ELSE 0 END) AS pending_count
                    FROM DANH_GIA_KHACH_HANG
                    WHERE TrangThaiDuyet != 'Đã ẩn'";
            $row = $this->db->query($sql)->fetch() ?: [
                'total_count'   => 0,
                'spam_count'    => 0,
                'photo_count'   => 0,
                'pending_count' => 0
            ];

            return [
                'all'         => (int) $row['total_count'],
                'spam'        => (int) $row['spam_count'],
                'with_photos' => (int) $row['photo_count'],
                'pending'     => (int) $row['pending_count']
            ];
        } catch (PDOException $e) {
            return ['all' => 0, 'spam' => 0, 'with_photos' => 0, 'pending' => 0];
        }
    }

    /**
     * Lấy danh sách đánh giá khách hàng kèm tên khách hàng và tên sản phẩm vải
     *
     * @param string $filter ('all' | 'spam' | 'with_photos')
     * @return array<int, array<string, mixed>>
     */
    public function getAllReviews(string $filter = 'all'): array
    {
        try {
            $where = ["dg.TrangThaiDuyet != 'Đã ẩn'"];

            if ($filter === 'spam') {
                $where[] = "(dg.LoaiDanhGia LIKE '%Spam%' OR dg.TrangThaiDuyet = 'Vi phạm' OR dg.SoSao <= 2)";
            } elseif ($filter === 'with_photos') {
                $where[] = 'dg.SoAnhDinhKem > 0';
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);

            $sql = "SELECT 
                        dg.MaDanhGia,
                        dg.MaKH,
                        kh.TenKH,
                        dg.SoSao,
                        dg.NoiDung,
                        dg.NgayDanhGia,
                        dg.MaBienThe,
                        dg.IPKhach,
                        dg.LoaiDanhGia,
                        dg.TrangThaiDuyet,
                        dg.IsGhim,
                        dg.SoAnhDinhKem,
                        COALESCE(sp.TenSP, 'Sản phẩm vải Aura') AS TenSP,
                        bt.MaSKU
                    FROM DANH_GIA_KHACH_HANG dg
                    INNER JOIN KHACH_HANG kh ON dg.MaKH = kh.MaKH
                    LEFT JOIN BIEN_THE_SP bt ON dg.MaBienThe = bt.MaBienThe
                    LEFT JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                    {$whereClause}
                    ORDER BY dg.IsGhim DESC, dg.MaDanhGia ASC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new RuntimeException('Lỗi khi lấy danh sách đánh giá: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Xử lý các thao tác kiểm duyệt đánh giá:
     * - approve: Duyệt Hiển Thị
     * - hide: Ẩn đánh giá
     * - delete: Xóa vĩnh viễn khỏi CSDL
     * - pin: Ghim Lên Đầu (hoặc bỏ ghim)
     *
     * @param int $maDanhGia
     * @param string $action
     * @return bool
     */
    public function moderateReview(int $maDanhGia, string $action): bool
    {
        try {
            switch ($action) {
                case 'approve':
                    $stmt = $this->db->prepare(
                        "UPDATE DANH_GIA_KHACH_HANG 
                         SET TrangThaiDuyet = 'Đã duyệt',
                             LoaiDanhGia = CASE WHEN LoaiDanhGia LIKE '%Spam%' THEN 'Đã kiểm duyệt' ELSE LoaiDanhGia END
                         WHERE MaDanhGia = :id"
                    );
                    return $stmt->execute([':id' => $maDanhGia]);

                case 'hide':
                    $stmt = $this->db->prepare(
                        "UPDATE DANH_GIA_KHACH_HANG 
                         SET TrangThaiDuyet = 'Đã ẩn'
                         WHERE MaDanhGia = :id"
                    );
                    return $stmt->execute([':id' => $maDanhGia]);

                case 'delete':
                    $stmt = $this->db->prepare(
                        "DELETE FROM DANH_GIA_KHACH_HANG WHERE MaDanhGia = :id"
                    );
                    return $stmt->execute([':id' => $maDanhGia]);

                case 'pin':
                    $stmt = $this->db->prepare(
                        "UPDATE DANH_GIA_KHACH_HANG 
                         SET IsGhim = CASE WHEN IsGhim = 1 THEN 0 ELSE 1 END,
                             TrangThaiDuyet = 'Đã duyệt'
                         WHERE MaDanhGia = :id"
                    );
                    return $stmt->execute([':id' => $maDanhGia]);

                default:
                    throw new InvalidArgumentException('Hành động kiểm duyệt không hợp lệ: ' . $action);
            }
        } catch (PDOException $e) {
            throw new RuntimeException('Lỗi khi cập nhật trạng thái đánh giá: ' . $e->getMessage(), 0, $e);
        }
    }

    /* =====================================================================
       6. CÁC HÀM TIỆN ÍCH NỘI BỘ (PRIVATE HELPERS)
       ===================================================================== */

    private function maskPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/\s+/', '', $phone) ?? '';
        if (strlen($clean) >= 7) {
            return substr($clean, 0, 4) . '•••' . substr($clean, -3);
        }
        return $clean;
    }

    private function detectFabricGroup(string $tenSP): string
    {
        $lower = mb_strtolower($tenSP, 'UTF-8');
        if (strpos($lower, 'linen') !== false || strpos($lower, 'lanh') !== false) {
            return 'Vải Linen';
        }
        if (strpos($lower, 'lụa') !== false || strpos($lower, 'silk') !== false || strpos($lower, 'satin') !== false) {
            return 'Vải Lụa';
        }
        if (strpos($lower, 'wool') !== false || strpos($lower, 'dạ') !== false || strpos($lower, 'tweed') !== false) {
            return 'Vải Dạ Tweed';
        }
        if (strpos($lower, 'cotton') !== false) {
            return 'Vải Cotton';
        }
        return 'Vải Thiết Kế Cao Cấp';
    }

    private function detectIconAndBadge(string $tenSP, array $data): array
    {
        if (!empty($data['icon_class']) && !empty($data['badge_mau_class'])) {
            return [(string) $data['icon_class'], (string) $data['badge_mau_class']];
        }

        $lower = mb_strtolower($tenSP, 'UTF-8');
        if (strpos($lower, 'lụa') !== false || strpos($lower, 'silk') !== false) {
            return ['fa-solid fa-feather', 'bg-purple-50 border-purple-200 text-purple-700'];
        }
        if (strpos($lower, 'wool') !== false || strpos($lower, 'dạ') !== false || strpos($lower, 'tweed') !== false) {
            return ['fa-solid fa-layer-group', 'bg-stone-100 border-stone-200 text-stone-700'];
        }
        return ['fa-solid fa-rug', 'bg-amber-50 border-amber-200 text-amber-700'];
    }
}
