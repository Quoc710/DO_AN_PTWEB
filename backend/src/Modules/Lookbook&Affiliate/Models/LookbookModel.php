<?php
namespace App\Modules\LookbookAffiliate\Models;

use PDO;

class LookbookModel {
    private $db;

    public function __construct() {
        $this->db = \Database::getConnection();
    }

    // Lấy danh sách Lookbook (mặc định chỉ lấy bài đã duyệt cho khách xem)
    public function getLookbooks($danhMuc = null, $onlyApproved = true) {
        $sql = "SELECT lb.*, 
                       sp.TenSP, bt.MaVaiCode, bt.MauSac, bt.ChatLieuSize, bt.HinhAnh AS HinhAnhVai,
                       bg.DonGia, bg.TyLeHoaHong,
                       nv.TenNV, nv.TenTiemMay, nv.SDT, nv.AffiliateCode, nv.CapBac
                FROM LOOKBOOK lb
                LEFT JOIN BIEN_THE_SP bt ON lb.MaBienThe = bt.MaBienThe
                LEFT JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                LEFT JOIN BANG_GIA bg ON bt.MaBienThe = bg.MaBienThe AND bg.LoaiKhach IN ('Khách lẻ', 'Bán lẻ Web')
                LEFT JOIN NHANVIEN_LOOKBOOK nvlb ON lb.MaLookBook = nvlb.MaLookBook
                LEFT JOIN NHAN_VIEN nv ON nvlb.MaNV = nv.MaNV
                WHERE 1=1";

        $params = [];
        if ($onlyApproved) {
            $sql .= " AND lb.TrangThaiDuyet = 'Đã duyệt'";
        }
        if ($danhMuc && $danhMuc !== 'Tất cả mẫu') {
            $sql .= " AND lb.DanhMuc = :danhMuc";
            $params[':danhMuc'] = $danhMuc;
        }

        $sql .= " ORDER BY lb.MaLookBook DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Lấy bài đăng chờ duyệt (dành cho Admin)
    public function getPendingLookbooks() {
        $sql = "SELECT lb.*, 
                       sp.TenSP, bt.MaVaiCode, bt.MauSac, bt.ChatLieuSize,
                       bg.DonGia,
                       nv.TenNV, nv.TenTiemMay, nv.SDT, nv.CapBac
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

    // Thợ may gửi bài mẫu Lookbook mới đính kèm tag mã vải
    public function createLookbook($maNV, $data) {
        $sql = "INSERT INTO LOOKBOOK (TenLookBook, URLHinhAnh, DanhMuc, MaBienThe, DinhLuongVai, MoTa, TrangThaiDuyet) 
                VALUES (:ten, :url, :danhMuc, :maBienThe, :dinhLuong, :moTa, 'Chờ duyệt')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':ten' => $data['tenLookbook'],
            ':url' => $data['urlHinhAnh'] ?: 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600',
            ':danhMuc' => $data['danhMuc'] ?: 'Đầm & Váy',
            ':maBienThe' => $data['maBienThe'] ?: null,
            ':dinhLuong' => $data['dinhLuong'] ?: '2.0m vải',
            ':moTa' => $data['moTa'] ?: ''
        ]);

        $maLookBook = $this->db->lastInsertId();

        // Gắn vào bảng nối nhân viên / thợ may
        $sqlJoin = "INSERT INTO NHANVIEN_LOOKBOOK (MaNV, MaLookBook) VALUES (:maNV, :maLB)";
        $stmtJoin = $this->db->prepare($sqlJoin);
        $stmtJoin->execute([
            ':maNV' => $maNV,
            ':maLB' => $maLookBook
        ]);

        return $maLookBook;
    }

    // Admin duyệt bài
    public function updateStatus($maLookBook, $status) {
        $stmt = $this->db->prepare("UPDATE LOOKBOOK SET TrangThaiDuyet = :status WHERE MaLookBook = :id");
        return $stmt->execute([':status' => $status, ':id' => $maLookBook]);
    }
}

