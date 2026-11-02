<?php
namespace App\Modules\LookbookAffiliate\Models;

use App\Config\Database;
use PDO;

class AffiliateModel {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // Lấy thông tin thợ may & ví hoa hồng
    public function getTailorProfile($maNV = 1) {
        $sql = "SELECT nv.*, vhh.SoTienHoaHong, vhh.TongTichLuy,
                       (SELECT COUNT(*) FROM LICH_SU_HOA_HONG ls WHERE ls.MaNV = nv.MaNV AND ls.SoTien > 0) AS SoDonHangAffiliate
                FROM NHAN_VIEN nv
                LEFT JOIN VI_HOA_HONG vhh ON nv.MaNV = vhh.MaNV
                WHERE nv.MaNV = :maNV";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maNV' => $maNV]);
        $profile = $stmt->fetch();
        if (!$profile) {
            // Mặc định thợ may 1
            $stmtDefault = $this->db->query("SELECT * FROM NHAN_VIEN LIMIT 1");
            return $stmtDefault->fetch();
        }
        return $profile;
    }

    // Lấy danh sách mẫu vải kèm mã link Affiliate cá nhân
    public function getFabricsForAffiliate($affiliateCode = 'thayhoang') {
        $sql = "SELECT bt.MaBienThe, bt.MaVaiCode, bt.MauSac, bt.ChatLieuSize, bt.HinhAnh,
                       sp.TenSP, sp.NhomSP,
                       bg.DonGia, bg.TyLeHoaHong,
                       ROUND(bg.DonGia * (bg.TyLeHoaHong / 100), 0) AS HoaHongNhanMoiMet
                FROM BIEN_THE_SP bt
                JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
                JOIN BANG_GIA bg ON bt.MaBienThe = bg.MaBienThe AND bg.LoaiKhach = 'Khách lẻ'
                ORDER BY bt.MaBienThe ASC";
        $stmt = $this->db->query($sql);
        $fabrics = $stmt->fetchAll();

        foreach ($fabrics as &$fb) {
            $codeClean = str_replace('#', '', $fb['MaVaiCode']);
            $fb['AffiliateLink'] = "lookbook.php?tab=lookbook&fabric=" . urlencode($codeClean) . "&ref=" . urlencode($affiliateCode);
            $fb['AffiliateFullUrl'] = "http://localhost/Web/DoANCK/lookbook.php?tab=lookbook&fabric=" . urlencode($codeClean) . "&ref=" . urlencode($affiliateCode);
        }
        return $fabrics;
    }

    // Tự động trích 5% hoa hồng khi khách mua thành công qua link Affiliate
    public function awardCommission($refCode, $maVaiCode, $soMet = 2.4, $donGia = 185000) {
        $stmt = $this->db->prepare("SELECT * FROM NHAN_VIEN WHERE AffiliateCode = :code");
        $stmt->execute([':code' => $refCode]);
        $tailor = $stmt->fetch();

        if (!$tailor) {
            // Mặc định thợ may 1
            $stmt = $this->db->query("SELECT * FROM NHAN_VIEN WHERE MaNV = 1");
            $tailor = $stmt->fetch();
        }

        $tongTien = $soMet * $donGia;
        $hoaHong = round($tongTien * 0.05);
        $orderCode = '#ORD-' . rand(1000, 9999);

        // Cộng tiền vào ví hoa hồng và tổng tích lũy
        $this->db->prepare("UPDATE VI_HOA_HONG SET SoTienHoaHong = SoTienHoaHong + :hh1, TongTichLuy = TongTichLuy + :hh2 WHERE MaNV = :maNV")
                 ->execute([':hh1' => $hoaHong, ':hh2' => $hoaHong, ':maNV' => $tailor['MaNV']]);

        // Ghi lịch sử hoa hồng
        $this->db->prepare("INSERT INTO LICH_SU_HOA_HONG (MaNV, MaDonHangCode, LoaiGiaoDich, SoTien, TrangThai) 
                            VALUES (:maNV, :orderCode, :loaiGD, :hh, 'Thành công')")
                 ->execute([
                     ':maNV' => $tailor['MaNV'],
                     ':orderCode' => $orderCode,
                     ':loaiGD' => 'Hoa hồng 5% đơn mua vải ' . $maVaiCode . ' (' . $soMet . 'm)',
                     ':hh' => $hoaHong
                 ]);

        return [
            'success' => true,
            'orderCode' => $orderCode,
            'tongTien' => $tongTien,
            'hoaHong' => $hoaHong,
            'tailor' => $tailor
        ];
    }

    // Danh sách đơn hàng qua link Affiliate của thợ may
    public function getAffiliateOrders($maNV = 1) {
        $sql = "SELECT * FROM LICH_SU_HOA_HONG WHERE MaNV = :maNV AND SoTien > 0 ORDER BY MaLS DESC LIMIT 15";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maNV' => $maNV]);
        return $stmt->fetchAll();
    }

    // Biến động số dư ví hoa hồng của thợ may
    public function getRecentTransactions($maNV = 1) {
        $sql = "SELECT * FROM LICH_SU_HOA_HONG WHERE MaNV = :maNV ORDER BY MaLS DESC LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':maNV' => $maNV]);
        return $stmt->fetchAll();
    }

    // Thợ may gửi yêu cầu rút tiền
    public function createWithdrawalRequest($maNV, $soTien, $hinhThuc, $thongTinNhan) {
        // Kiểm tra số dư ví
        $stmtCheck = $this->db->prepare("SELECT SoTienHoaHong FROM VI_HOA_HONG WHERE MaNV = :maNV");
        $stmtCheck->execute([':maNV' => $maNV]);
        $vi = $stmtCheck->fetch();

        if (!$vi || $vi['SoTienHoaHong'] < $soTien) {
            return ['success' => false, 'message' => 'Số dư ví hoa hồng không đủ để thực hiện yêu cầu!'];
        }

        // Tạo yêu cầu rút tiền
        $sql = "INSERT INTO YEU_CAU_RUT_TIEN (MaNV, SoTien, HinhThuc, ThongTinNhan, TrangThai) 
                VALUES (:maNV, :soTien, :hinhThuc, :thongTinNhan, 'Chờ duyệt')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':maNV' => $maNV,
            ':soTien' => $soTien,
            ':hinhThuc' => $hinhThuc,
            ':thongTinNhan' => $thongTinNhan
        ]);

        return ['success' => true, 'message' => 'Đã gửi yêu cầu rút tiền thành công, Admin sẽ xử lý sớm!'];
    }

    // Lấy danh sách yêu cầu rút tiền chờ duyệt (cho Admin)
    public function getPendingWithdrawals() {
        $sql = "SELECT yc.*, nv.TenNV, nv.TenTiemMay, nv.SDT, nv.SoTaiKhoan, nv.NganHang, vhh.SoTienHoaHong
                FROM YEU_CAU_RUT_TIEN yc
                JOIN NHAN_VIEN nv ON yc.MaNV = nv.MaNV
                LEFT JOIN VI_HOA_HONG vhh ON nv.MaNV = vhh.MaNV
                WHERE yc.TrangThai = 'Chờ duyệt'
                ORDER BY yc.MaYeuCau DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Admin duyệt lệnh rút tiền (trừ tiền khỏi ví thật)
    public function approveWithdrawal($maYeuCau) {
        $stmt = $this->db->prepare("SELECT * FROM YEU_CAU_RUT_TIEN WHERE MaYeuCau = :id");
        $stmt->execute([':id' => $maYeuCau]);
        $yc = $stmt->fetch();

        if (!$yc || $yc['TrangThai'] !== 'Chờ duyệt') {
            return false;
        }

        // Cập nhật trạng thái
        $this->db->prepare("UPDATE YEU_CAU_RUT_TIEN SET TrangThai = 'Đã chuyển khoản' WHERE MaYeuCau = :id")
                 ->execute([':id' => $maYeuCau]);

        // Trừ tiền khỏi ví
        $this->db->prepare("UPDATE VI_HOA_HONG SET SoTienHoaHong = SoTienHoaHong - :soTien WHERE MaNV = :maNV")
                 ->execute([':soTien' => $yc['SoTien'], ':maNV' => $yc['MaNV']]);

        // Thêm vào lịch sử giao dịch
        $loaiGD = ($yc['HinhThuc'] === 'MuaVaiAura') ? 'Đổi voucher mua vải (+10%)' : 'Rút tiền về tài khoản ngân hàng';
        $this->db->prepare("INSERT INTO LICH_SU_HOA_HONG (MaNV, MaDonHangCode, LoaiGiaoDich, SoTien, TrangThai) 
                            VALUES (:maNV, CONCAT('#WD-', :id), :loaiGD, :soTienAm, 'Thành công')")
                 ->execute([
                     ':maNV' => $yc['MaNV'],
                     ':id' => $yc['MaYeuCau'],
                     ':loaiGD' => $loaiGD,
                     ':soTienAm' => -1 * $yc['SoTien']
                 ]);

        return true;
    }

    public function rejectWithdrawal($maYeuCau) {
        $stmt = $this->db->prepare("UPDATE YEU_CAU_RUT_TIEN SET TrangThai = 'Từ chối' WHERE MaYeuCau = :id");
        return $stmt->execute([':id' => $maYeuCau]);
    }
}

