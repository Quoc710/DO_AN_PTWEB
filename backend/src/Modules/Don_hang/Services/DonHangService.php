<?php

namespace App\Modules\Don_hang\Services;

use App\Core\Database;
use PDO;
use Exception;

class DonHangService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy danh sách phiếu bán (đơn hàng) từ CSDL QuanLyCuaHangVai
     */
    public function getOrders(?string $keyword = null, ?string $ngayBan = null, ?string $status = null): array
    {
        $sql = "
            SELECT 
                pb.MaPhieuBan,
                pb.MaKH,
                pb.NgayBan,
                pb.TongTien,
                COALESCE(pb.TrangThai, N'Chờ xác nhận') AS TrangThai,
                kh.TenKH,
                kh.SDT,
                kh.LoaiKhach,
                kh.TongNo
            FROM PHIEU_BAN pb
            INNER JOIN KHACH_HANG kh ON pb.MaKH = kh.MaKH
            WHERE 1=1
        ";

        $params = [];

        if (!empty($status) && $status !== 'all') {
            if ($status === 'pending') {
                $sql .= " AND (pb.TrangThai = N'Chờ xác nhận' OR pb.TrangThai LIKE '%x%c%nh%n%' OR pb.TrangThai LIKE '%Ch%')";
            } elseif ($status === 'shipping') {
                $sql .= " AND (pb.TrangThai = N'Đang giao' OR (pb.TrangThai LIKE '%giao%' AND pb.TrangThai NOT LIKE N'%Đã%'))";
            } elseif ($status === 'completed') {
                $sql .= " AND (pb.TrangThai = N'Đã giao' OR (pb.TrangThai LIKE '%giao%' AND (pb.TrangThai LIKE N'%Đã%' OR pb.TrangThai LIKE '%d%')))";
            } elseif ($status === 'cancelled') {
                $sql .= " AND (pb.TrangThai = N'Đã hủy' OR pb.TrangThai LIKE '%h%y%')";
            } else {
                $sql .= " AND pb.TrangThai = :status";
                $params['status'] = $status;
            }
        }

        if (!empty($keyword)) {
            $keyword = trim($keyword);
            if (is_numeric($keyword)) {
                $sql .= " AND (pb.MaPhieuBan = :maPB OR kh.SDT LIKE :kwSdt)";
                $params['maPB'] = (int)$keyword;
                $params['kwSdt'] = "%$keyword%";
            } else {
                $sql .= " AND (kh.TenKH LIKE :kwTen OR kh.SDT LIKE :kwSdt OR kh.LoaiKhach LIKE :kwLoai)";
                $params['kwTen'] = "%$keyword%";
                $params['kwSdt'] = "%$keyword%";
                $params['kwLoai'] = "%$keyword%";
            }
        }

        if (!empty($ngayBan)) {
            $sql .= " AND pb.NgayBan = :ngayBan";
            $params['ngayBan'] = $ngayBan;
        }

        $sql .= " ORDER BY pb.MaPhieuBan DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Nạp chi tiết hàng hóa (CT_PHIEU_BAN, BIEN_THE_SP, SAN_PHAM), Voucher và Phiếu trả
        foreach ($orders as &$order) {
            $order['TrangThai'] = self::normalizeStatus($order['TrangThai'] ?? null);
            $order['ChiTiet'] = $this->getOrderItems((int)$order['MaPhieuBan']);
            $order['Vouchers'] = $this->getOrderVouchers((int)$order['MaPhieuBan']);
            $order['PhieuTra'] = $this->getOrderReturns((int)$order['MaPhieuBan']);
        }

        return $orders;
    }

    /**
     * Chuẩn hóa trạng thái đơn hàng để tránh lỗi mã hóa ký tự từ CSDL
     */
    public static function normalizeStatus(?string $status): string
    {
        if (empty($status)) {
            return 'Chờ xác nhận';
        }
        $clean = preg_replace('/[^\x20-\x7E]/', '?', $status);
        $lower = strtolower((string)$clean);

        if (strpos($lower, 'giao') !== false && strpos($lower, 'da') === false && strpos($lower, 'd?') === false) {
            return 'Đang giao';
        }
        if (strpos($lower, 'giao') !== false && (strpos($lower, 'da') !== false || strpos($lower, 'd?') !== false)) {
            return 'Đã giao';
        }
        if (strpos($lower, 'huy') !== false || strpos($lower, 'h?y') !== false) {
            return 'Đã hủy';
        }
        if (strpos($lower, 'ch') !== false || strpos($lower, 'xac') !== false || strpos($lower, 'x?c') !== false) {
            return 'Chờ xác nhận';
        }

        if (!mb_check_encoding($status, 'UTF-8')) {
            return mb_convert_encoding($status, 'UTF-8', 'Windows-1258, Windows-1252, ISO-8859-1');
        }

        return $status;
    }

    /**
     * Lấy chi tiết các mặt hàng trong phiếu bán
     */
    public function getOrderItems(int $maPhieuBan): array
    {
        $sql = "
            SELECT 
                ct.MaCTPhieuBan,
                ct.MaPhieuBan,
                ct.MaBienThe,
                ct.SoLuongBan,
                ct.DonGiaBan,
                (ct.SoLuongBan * ct.DonGiaBan) AS ThanhTien,
                sp.MaSP,
                sp.TenSP,
                sp.NhomSP,
                bt.DonViTinh,
                bt.MauSac,
                bt.ChatLieuSize
            FROM CT_PHIEU_BAN ct
            INNER JOIN BIEN_THE_SP bt ON ct.MaBienThe = bt.MaBienThe
            INNER JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
            WHERE ct.MaPhieuBan = :maPhieuBan
            ORDER BY ct.MaCTPhieuBan ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maPhieuBan' => $maPhieuBan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách Voucher áp dụng cho phiếu bán
     */
    public function getOrderVouchers(int $maPhieuBan): array
    {
        $sql = "
            SELECT 
                v.MaVoucher,
                v.GiaTriVoucher,
                v.HanSuDung
            FROM PHIEU_BAN_VOUCHER pbv
            INNER JOIN VOUCHER v ON pbv.MaVoucher = v.MaVoucher
            WHERE pbv.MaPhieuBan = :maPhieuBan
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maPhieuBan' => $maPhieuBan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin phiếu trả hàng liên quan (nếu có)
     */
    public function getOrderReturns(int $maPhieuBan): array
    {
        $sql = "
            SELECT 
                pt.MaPhieuTra,
                pt.MaPhieuBanGoc,
                pt.LyDoTra,
                ct.MaCTTraHang,
                ct.MaBienThe,
                ct.SoLuongTra,
                ct.TinhTrangHang,
                ct.NhapLaiKho,
                sp.TenSP,
                bt.MauSac,
                bt.ChatLieuSize,
                bt.DonViTinh
            FROM PHIEU_TRA_HANG pt
            LEFT JOIN CT_TRA_HANG ct ON pt.MaPhieuTra = ct.MaPhieuTra
            LEFT JOIN BIEN_THE_SP bt ON ct.MaBienThe = bt.MaBienThe
            LEFT JOIN SAN_PHAM sp ON bt.MaSP = sp.MaSP
            WHERE pt.MaPhieuBanGoc = :maPhieuBan
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['maPhieuBan' => $maPhieuBan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Thống kê tổng quan từ cơ sở dữ liệu
     */
    public function getOrderStatistics(): array
    {
        $stat = $this->db->query("
            SELECT 
                COUNT(pb.MaPhieuBan) AS TongSoDon,
                COALESCE(SUM(pb.TongTien), 0) AS TongDoanhThu,
                COALESCE((SELECT SUM(SoLuongBan) FROM CT_PHIEU_BAN), 0) AS TongSoLuongSanPham,
                (SELECT COUNT(*) FROM KHACH_HANG) AS TongSoKhachHang
            FROM PHIEU_BAN pb
        ")->fetch(PDO::FETCH_ASSOC);

        $statusCounts = [
            'all' => (int)($stat['TongSoDon'] ?? 0),
            'pending' => 0,
            'shipping' => 0,
            'completed' => 0,
            'cancelled' => 0,
        ];

        try {
            $rows = $this->db->query("
                SELECT TrangThai, COUNT(*) AS cnt 
                FROM PHIEU_BAN 
                GROUP BY TrangThai
            ")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $st = self::normalizeStatus($r['TrangThai'] ?? '');
                $cnt = (int)$r['cnt'];
                if ($st === 'Chờ xác nhận') $statusCounts['pending'] += $cnt;
                elseif ($st === 'Đang giao') $statusCounts['shipping'] += $cnt;
                elseif ($st === 'Đã giao') $statusCounts['completed'] += $cnt;
                elseif ($st === 'Đã hủy') $statusCounts['cancelled'] += $cnt;
            }
        } catch (\Throwable $e) {}

        return [
            'TongSoDon' => (int)($stat['TongSoDon'] ?? 0),
            'TongDoanhThu' => (float)($stat['TongDoanhThu'] ?? 0),
            'TongSoLuongSanPham' => (float)($stat['TongSoLuongSanPham'] ?? 0),
            'TongSoKhachHang' => (int)($stat['TongSoKhachHang'] ?? 0),
            'statusCounts' => $statusCounts,
        ];
    }
}
