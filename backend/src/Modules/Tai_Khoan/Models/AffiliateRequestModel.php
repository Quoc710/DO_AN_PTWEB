<?php

namespace App\Modules\Tai_Khoan\Models;

use App\Core\Database;
use PDO;

class AffiliateRequestModel
{
    private ?PDO $db = null;

    private function getDb(): PDO
    {
        if ($this->db === null) {
            $this->db = Database::getConnection();
        }
        return $this->db;
    }

    public function existsByEmailOrUsername(string $email, string $username): bool
    {
        $stmt = $this->getDb()->prepare(
            "SELECT COUNT(*) AS total FROM DANG_KY_AFFILIATE WHERE Email = ? OR TenDangNhap = ?"
        );
        $stmt->execute([$email, $username]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0) > 0;
    }

    public function create(string $tenKH, string $email, string $tenDangNhap, string $matKhauHash, ?string $sdt = null): array
    {
        $db = $this->getDb();
        $stmt = $db->prepare(
            "INSERT INTO DANG_KY_AFFILIATE (TenKH, Email, SDT, TenDangNhap, MatKhauHash, TrangThai)
             VALUES (?, ?, ?, ?, ?, 'Chờ duyệt')"
        );
        $stmt->execute([$tenKH, $email, $sdt, $tenDangNhap, $matKhauHash]);
        $id = (int)$db->lastInsertId();

        return [
            'MaDKAff' => $id,
            'TenKH' => $tenKH,
            'Email' => $email,
            'TenDangNhap' => $tenDangNhap,
            'TrangThai' => 'Chờ duyệt',
        ];
    }

    public function findAll(string $trangThai = 'Chờ duyệt'): array
    {
        $stmt = $this->getDb()->prepare(
            "SELECT MaDKAff, TenKH, Email, SDT, TenDangNhap, TrangThai, NgayDangKy, GhiChu
             FROM DANG_KY_AFFILIATE WHERE TrangThai = ? ORDER BY NgayDangKy DESC"
        );
        $stmt->execute([$trangThai]);
        return $stmt->fetchAll() ?: [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM DANG_KY_AFFILIATE WHERE MaDKAff = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function approve(int $id): bool
    {
        $stmt = $this->getDb()->prepare(
            "UPDATE DANG_KY_AFFILIATE SET TrangThai = 'Đã duyệt' WHERE MaDKAff = ?"
        );
        return $stmt->execute([$id]);
    }

    public function reject(int $id, string $ghiChu = ''): bool
    {
        $stmt = $this->getDb()->prepare(
            "UPDATE DANG_KY_AFFILIATE SET TrangThai = 'Từ chối', GhiChu = ? WHERE MaDKAff = ?"
        );
        return $stmt->execute([$ghiChu, $id]);
    }
}
