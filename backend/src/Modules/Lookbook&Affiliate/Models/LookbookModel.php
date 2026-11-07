<?php
namespace App\Modules\LookbookAffiliate\Models;

use PDO;

class LookbookModel {
    private $db;

    public function __construct() {
        $this->db = \Database::getConnection();
    }

    /**
     * Lấy danh sách Màu sắc (BIEN_THE_SP.MauSac) trực tiếp từ CSDL
     */
    public function getFilterOptions(): array
    {
        $mauSac = [];
        try {
            $mauSac = $this->db->query("
                SELECT DISTINCT TRIM(MauSac) AS val 
                FROM BIEN_THE_SP 
                WHERE MauSac IS NOT NULL AND TRIM(MauSac) != '' 
                ORDER BY val ASC
            ")->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable $e) {
            $mauSac = [];
        }

        return [
            'mau_sac' => $mauSac,
        ];
    }

    /**
     * Gộp cả bảng LOOKBOOK và bảng BIEN_THE_SP + SAN_PHAM để không bao giờ mất sản phẩm,
     * đồng thời lọc chuẩn xác theo Danh mục, Màu sắc & Từ khóa từ CSDL
     */
    public function getLookbooks($category = null, bool $onlyApproved = false, array $filters = []): array
    {
        $sql = "SELECT * FROM (
                    /* NGUỒN 1: Các mẫu thiết kế trong bảng LOOKBOOK */
                    SELECT 
                        lb.MaLookBook,
                        COALESCE(lb.MaBienThe, 0) AS MaBienThe,
                        sp.MaSP,
                        COALESCE(bt.MaVaiCode, '#LN-55') AS MaVaiCode,
                        lb.TenLookBook,
                        COALESCE(sp.TenSP, 'Vải Linen Tưng Premium Kem Mộc') AS TenSP,
                        COALESCE(NULLIF(TRIM(bt.MauSac), ''), 'Kem mộc') AS MauSac,
                        COALESCE(NULLIF(lb.URLHinhAnh, ''), NULLIF(bt.HinhAnhMacro, ''), NULLIF(bt.HinhAnh, ''), '') AS URLHinhAnh,
                        COALESCE(NULLIF(TRIM(lb.DanhMuc), ''), 'Đầm & Váy') AS DanhMuc,
                        COALESCE(NULLIF(nv.TenTiemMay, ''), NULLIF(nv.TenNV, ''), 'Nhà May Hoàng Kim') AS TenTiemMay,
                        COALESCE(nv.SDT, '0912.334.881') AS SDT,
                        CONCAT(COALESCE(bt.ChatLieuSize, 'Khổ 1.45m'), ' • ', COALESCE(bt.MauSac, 'Kem mộc')) AS ChatLieuSize,
                        COALESCE(bg.DonGia, 185000) AS DonGia,
                        COALESCE(NULLIF(lb.DinhLuongVai, ''), '2.4m (Khổ 1.45m)') AS DinhLuongVai,
                        COALESCE(NULLIF(lb.MoTa, ''), 'Thiết kế may đo thực tế từ mạng lưới thợ may Aura.') AS MoTa,
                        1 AS isAvailable
                    FROM LOOKBOOK lb
                    LEFT JOIN BIEN_THE_SP bt ON lb.MaBienThe = bt.MaBienThe
                    LEFT JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                    LEFT JOIN (
                        SELECT MaBienThe, MIN(DonGia) AS DonGia FROM BANG_GIA GROUP BY MaBienThe
                    ) bg ON bt.MaBienThe = bg.MaBienThe
                    LEFT JOIN NHANVIEN_LOOKBOOK nvlb ON lb.MaLookBook = nvlb.MaLookBook
                    LEFT JOIN NHAN_VIEN nv ON nvlb.MaNV = nv.MaNV
                    WHERE (lb.TrangThaiDuyet = 'Đã duyệt' OR lb.TrangThaiDuyet IS NULL OR lb.TrangThaiDuyet = '')

                    UNION ALL

                    /* NGUỒN 2: Các mẫu vải thực tế trong bảng BIEN_THE_SP + SAN_PHAM */
                    SELECT 
                        (10000 + bt.MaBienThe) AS MaLookBook,
                        bt.MaBienThe,
                        sp.MaSP,
                        COALESCE(bt.MaVaiCode, CONCAT('#VAI-', bt.MaBienThe)) AS MaVaiCode,
                        COALESCE(sp.TenSP, bt.MaVaiCode, 'Mẫu vải Aura') AS TenLookBook,
                        COALESCE(sp.TenSP, bt.MaVaiCode, 'Vải chất lượng cao') AS TenSP,
                        COALESCE(NULLIF(TRIM(bt.MauSac), ''), 'Tiêu chuẩn') AS MauSac,
                        COALESCE(NULLIF(bt.HinhAnhMacro, ''), NULLIF(bt.HinhAnh, ''), '') AS URLHinhAnh,
                        CASE 
                            WHEN sp.TenSP LIKE '%Áo dài%' OR sp.TenSP LIKE '%Lụa%' THEN 'Áo dài truyền thống'
                            WHEN sp.TenSP LIKE '%Vest%' OR sp.TenSP LIKE '%Blazer%' OR sp.TenSP LIKE '%Tweed%' OR sp.TenSP LIKE '%Dạ%' THEN 'Vest & Blazer'
                            WHEN sp.TenSP LIKE '%Sơ mi%' OR sp.TenSP LIKE '%Cotton%' OR sp.TenSP LIKE '%Poplin%' THEN 'Áo sơ mi & Công sở'
                            WHEN sp.TenSP LIKE '%Nam%' OR sp.TenSP LIKE '%Quần tây%' THEN 'Trang phục nam'
                            ELSE 'Đầm & Váy'
                        END AS DanhMuc,
                        'Kho Vải Aura Studio' AS TenTiemMay,
                        '0912.334.881' AS SDT,
                        CONCAT(COALESCE(bt.ChatLieuSize, 'Khổ 1.45m'), ' • ', COALESCE(bt.MauSac, 'Tiêu chuẩn')) AS ChatLieuSize,
                        COALESCE(bg.DonGia, 185000) AS DonGia,
                        CONCAT('Tồn kho: ', COALESCE(cv.TongMetTon, 0), 'm') AS DinhLuongVai,
                        'Chất liệu vải thực tế có sẵn tại kho Aura Studio, hỗ trợ cắt lẻ từ 0.5m.' AS MoTa,
                        CASE WHEN COALESCE(cv.TongMetTon, 0) > 0 THEN 1 ELSE 0 END AS isAvailable
                    FROM BIEN_THE_SP bt
                    LEFT JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                    LEFT JOIN (
                        SELECT MaBienThe, MIN(DonGia) AS DonGia FROM BANG_GIA GROUP BY MaBienThe
                    ) bg ON bt.MaBienThe = bg.MaBienThe
                    LEFT JOIN (
                        SELECT MaBienThe, SUM(DoDaiConLai) AS TongMetTon FROM CUON_VAI GROUP BY MaBienThe
                    ) cv ON bt.MaBienThe = cv.MaBienThe
                ) AS all_items
                WHERE 1=1";

        $params = [];

        // Lọc theo Danh mục
        $cleanCat = trim((string)$category);
        if ($cleanCat !== '' && $cleanCat !== 'Tất cả mẫu') {
            $catMap = [
                'Áo dài truyền thống' => ['Áo dài truyền thống', 'Áo dài'],
                'Vest & Blazer'       => ['Vest & Blazer', 'Vest / Suit', 'Vest'],
                'Áo sơ mi & Công sở'  => ['Áo sơ mi & Công sở', 'Áo sơ mi'],
            ];
            $matchedCats = $catMap[$cleanCat] ?? [$cleanCat];
            $placeholders = [];
            foreach ($matchedCats as $idx => $cName) {
                $key = ':cat_' . $idx;
                $placeholders[] = $key;
                $params[$key] = $cName;
            }
            $sql .= " AND TRIM(all_items.DanhMuc) IN (" . implode(', ', $placeholders) . ")";
        }

        // Lọc theo Màu sắc (BIEN_THE_SP.MauSac)
        if (!empty($filters['mau_sac'])) {
            $sql .= " AND TRIM(all_items.MauSac) = :mau_sac";
            $params[':mau_sac'] = trim((string)$filters['mau_sac']);
        }

        // Lọc theo Từ khóa
        if (!empty($filters['keyword'])) {
            $sql .= " AND (all_items.TenLookBook LIKE :kw OR all_items.TenSP LIKE :kw OR all_items.MaVaiCode LIKE :kw)";
            $params[':kw'] = '%' . trim((string)$filters['keyword']) . '%';
        }

        $sql .= " ORDER BY all_items.MaLookBook ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getPendingLookbooks() {
        $sql = "SELECT lb.*, sp.TenSP, bt.MaVaiCode, bt.MauSac, bt.ChatLieuSize, bg.DonGia, nv.TenNV, nv.TenTiemMay, nv.SDT, nv.CapBac
                FROM LOOKBOOK lb
                LEFT JOIN BIEN_THE_SP bt ON lb.MaBienThe = bt.MaBienThe
                LEFT JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                LEFT JOIN BANG_GIA bg ON bt.MaBienThe = bg.MaBienThe AND bg.LoaiKhach IN ('Khách lẻ', 'Bán lẻ Web')
                LEFT JOIN NHANVIEN_LOOKBOOK nvlb ON lb.MaLookBook = nvlb.MaLookBook
                LEFT JOIN NHAN_VIEN nv ON nvlb.MaNV = nv.MaNV
                WHERE lb.TrangThaiDuyet = 'Chờ duyệt'
                ORDER BY lb.MaLookBook DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function createLookbook($maNV, $data) {
        $sql = "INSERT INTO LOOKBOOK (TenLookBook, URLHinhAnh, DanhMuc, MaBienThe, DinhLuongVai, MoTa, TrangThaiDuyet) 
                VALUES (:ten, :url, :danhMuc, :maBienThe, :dinhLuong, :moTa, 'Chờ duyệt')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ten' => $data['tenLookbook'],
            ':url' => $data['urlHinhAnh'] ?: '',
            ':danhMuc' => $data['danhMuc'] ?: 'Đầm & Váy',
            ':maBienThe' => $data['maBienThe'] ?: null,
            ':dinhLuong' => $data['dinhLuong'] ?: '2.0m vải',
            ':moTa' => $data['moTa'] ?: ''
        ]);
        $maLookBook = $this->db->lastInsertId();
        $stmtJoin = $this->db->prepare("INSERT INTO NHANVIEN_LOOKBOOK (MaNV, MaLookBook) VALUES (:maNV, :maLB)");
        $stmtJoin->execute([':maNV' => $maNV, ':maLB' => $maLookBook]);
        return $maLookBook;
    }

    public function updateStatus($maLookBook, $status) {
        $stmt = $this->db->prepare("UPDATE LOOKBOOK SET TrangThaiDuyet = :status WHERE MaLookBook = :id");
        return $stmt->execute([':status' => $status, ':id' => $maLookBook]);
    }
}