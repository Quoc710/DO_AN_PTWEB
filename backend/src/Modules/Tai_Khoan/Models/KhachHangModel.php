<?php

namespace App\Modules\Tai_Khoan\Models;

use App\Core\Database;
use PDO;
use Exception;

class KhachHangModel
{
    private ?PDO $db = null;

    private function getDb(): PDO
    {
        if ($this->db === null) {
            $this->db = Database::getConnection();
        }
        return $this->db;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->getDb()->prepare(
            "SELECT * FROM KHACH_HANG WHERE Email = ? OR TenDangNhap = ? LIMIT 1"
        );
        $stmt->execute([$email, $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function existsByEmailOrUsername(string $email, string $username): bool
    {
        $stmt = $this->getDb()->prepare(
            "SELECT COUNT(*) AS total FROM KHACH_HANG WHERE Email = ? OR TenDangNhap = ?"
        );
        $stmt->execute([$email, $username]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0) > 0;
    }

    public function create(
        string $tenKH, 
        string $email, 
        string $tenDangNhap, 
        string $matKhauHash, 
        ?string $sdt = null,
        ?string $tokenKichHoat = null,
        ?string $hanKichHoat = null
    ): array {
        $db = $this->getDb();
        $trangThai = $tokenKichHoat ? 'Chưa kích hoạt' : 'Hoạt động';
        $stmt = $db->prepare(
            "INSERT INTO KHACH_HANG (TenKH, SDT, LoaiKhach, TongNo, Email, TenDangNhap, MatKhauHash, TrangThai, TokenKichHoat, HanKichHoat)
             VALUES (?, ?, 'Khách lẻ', 0, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$tenKH, $sdt, $email, $tenDangNhap, $matKhauHash, $trangThai, $tokenKichHoat, $hanKichHoat]);
        $maKH = (int)$db->lastInsertId();

        return [
            'MaKH' => $maKH,
            'TenKH' => $tenKH,
            'Email' => $email,
            'TenDangNhap' => $tenDangNhap,
            'LoaiKhach' => 'Khách lẻ',
            'TrangThai' => $trangThai,
        ];
    }

    public function findByActivationToken(string $token): ?array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM KHACH_HANG WHERE TokenKichHoat = ? LIMIT 1");
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function activateAccount(int $maKH): bool
    {
        $stmt = $this->getDb()->prepare("UPDATE KHACH_HANG SET TrangThai = 'Đã kích hoạt' WHERE MaKH = ?");
        return $stmt->execute([$maKH]);
    }

    public function updateActivationToken(int $maKH, string $tokenKichHoat, string $hanKichHoat): bool
    {
        $stmt = $this->getDb()->prepare("UPDATE KHACH_HANG SET TokenKichHoat = ?, HanKichHoat = ? WHERE MaKH = ?");
        return $stmt->execute([$tokenKichHoat, $hanKichHoat, $maKH]);
    }

    public function findById(int $maKH): ?array
    {
        $stmt = $this->getDb()->prepare("SELECT * FROM KHACH_HANG WHERE MaKH = ? LIMIT 1");
        $stmt->execute([$maKH]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
