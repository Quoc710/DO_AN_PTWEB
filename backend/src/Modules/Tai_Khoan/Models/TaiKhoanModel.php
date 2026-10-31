<?php

namespace App\Modules\Tai_Khoan\Models;

use App\Core\Database;
use PDO;
use Exception;

class TaiKhoanModel
{
    private ?PDO $db = null;

    private function getDb(): PDO
    {
        if ($this->db === null) {
            $this->db = Database::getConnection();
        }
        return $this->db;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT tk.MaTK, tk.MaNV, tk.TenTK, tk.MatKhauHash, tk.Email, tk.TrangThai, tk.TokenKichHoat, tk.HanKichHoat, nv.TenNV, nv.ViTri
                FROM TAI_KHOAN tk
                JOIN NHAN_VIEN nv ON tk.MaNV = nv.MaNV
                WHERE tk.TenTK = ? OR tk.Email = ?";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        $user['roles'] = $this->getRolesByMaTK((int)$user['MaTK']);
        $user['permissions'] = $this->getPermissionsByMaTK((int)$user['MaTK']);

        return $user;
    }

    public function findById(int $maTK): ?array
    {
        $sql = "SELECT tk.MaTK, tk.MaNV, tk.TenTK, tk.Email, tk.TrangThai, nv.TenNV, nv.ViTri
                FROM TAI_KHOAN tk
                JOIN NHAN_VIEN nv ON tk.MaNV = nv.MaNV
                WHERE tk.MaTK = ?";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$maTK]);
        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }

        $user['roles'] = $this->getRolesByMaTK($maTK);
        $user['permissions'] = $this->getPermissionsByMaTK($maTK);

        return $user;
    }

    public function existsByUsername(string $username): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM TAI_KHOAN WHERE TenTK = ? OR Email = ?";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$username, $username]);
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0) > 0;
    }

    public function createStaffAccount(
        string $tenNV,
        string $tenTK,
        string $matKhauHash,
        string $email,
        string $tokenKichHoat,
        string $hanKichHoat,
        string $viTri = 'Nhân viên',
        string $tenVaiTro = 'Nhân viên bán hàng'
    ): array {
        $db = $this->getDb();
        $db->beginTransaction();

        try {
            // 1. Thêm nhân viên
            $maNV = null;
            try {
                $sqlNV = "INSERT INTO NHAN_VIEN (TenNV, ViTri) OUTPUT INSERTED.MaNV VALUES (?, ?)";
                $stmtNV = $db->prepare($sqlNV);
                $stmtNV->execute([$tenNV, $viTri]);
                $rowNV = $stmtNV->fetch();
                $maNV = $rowNV['MaNV'] ?? null;
            } catch (Exception) {
                $sqlNV = "INSERT INTO NHAN_VIEN (TenNV, ViTri) VALUES (?, ?)";
                $stmtNV = $db->prepare($sqlNV);
                $stmtNV->execute([$tenNV, $viTri]);
                $maNV = $db->lastInsertId();
            }

            if (!$maNV) {
                throw new Exception("Không thể tạo bản ghi nhân viên.");
            }

            // 2. Thêm tài khoản (Trạng thái mặc định: Chưa kích hoạt)
            $maTK = null;
            $trangThai = 'Chưa kích hoạt';
            try {
                $sqlTK = "INSERT INTO TAI_KHOAN (MaNV, TenTK, MatKhauHash, Email, TrangThai, TokenKichHoat, HanKichHoat) 
                          OUTPUT INSERTED.MaTK VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtTK = $db->prepare($sqlTK);
                $stmtTK->execute([$maNV, $tenTK, $matKhauHash, $email, $trangThai, $tokenKichHoat, $hanKichHoat]);
                $rowTK = $stmtTK->fetch();
                $maTK = $rowTK['MaTK'] ?? null;
            } catch (Exception) {
                $sqlTK = "INSERT INTO TAI_KHOAN (MaNV, TenTK, MatKhauHash, Email, TrangThai, TokenKichHoat, HanKichHoat) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtTK = $db->prepare($sqlTK);
                $stmtTK->execute([$maNV, $tenTK, $matKhauHash, $email, $trangThai, $tokenKichHoat, $hanKichHoat]);
                $maTK = $db->lastInsertId();
            }

            if (!$maTK) {
                throw new Exception("Không thể tạo bản ghi tài khoản.");
            }

            // 3. Gán vai trò mặc định
            $maVaiTro = $this->getOrCreateRole($tenVaiTro, 5.0);
            if ($maVaiTro) {
                $sqlVT = "INSERT INTO TAIKHOAN_VAITRO (MaTK, MaVaiTro) VALUES (?, ?)";
                $stmtVT = $db->prepare($sqlVT);
                $stmtVT->execute([$maTK, $maVaiTro]);
            }

            $db->commit();

            return [
                'MaTK' => (int)$maTK,
                'MaNV' => (int)$maNV,
                'TenNV' => $tenNV,
                'TenTK' => $tenTK,
                'Email' => $email,
                'TrangThai' => $trangThai,
                'ViTri' => $viTri,
                'Role' => $tenVaiTro,
            ];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function findByActivationToken(string $token): ?array
    {
        $sql = "SELECT tk.MaTK, tk.TenTK, tk.Email, tk.TrangThai, tk.HanKichHoat, nv.TenNV
                FROM TAI_KHOAN tk
                JOIN NHAN_VIEN nv ON tk.MaNV = nv.MaNV
                WHERE tk.TokenKichHoat = ?";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function activateAccount(int $maTK): bool
    {
        $sql = "UPDATE TAI_KHOAN 
                SET TrangThai = N'Đã kích hoạt' 
                WHERE MaTK = ?";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute([$maTK]);
    }

    public function updateActivationToken(int $maTK, string $tokenKichHoat, string $hanKichHoat, ?string $newPasswordHash = null): bool
    {
        if ($newPasswordHash) {
            $sql = "UPDATE TAI_KHOAN 
                    SET TokenKichHoat = ?, HanKichHoat = ?, MatKhauHash = ? 
                    WHERE MaTK = ?";
            $stmt = $this->getDb()->prepare($sql);
            return $stmt->execute([$tokenKichHoat, $hanKichHoat, $newPasswordHash, $maTK]);
        }

        $sql = "UPDATE TAI_KHOAN 
                SET TokenKichHoat = ?, HanKichHoat = ? 
                WHERE MaTK = ?";
        $stmt = $this->getDb()->prepare($sql);
        return $stmt->execute([$tokenKichHoat, $hanKichHoat, $maTK]);
    }

    public function getRolesByMaTK(int $maTK): array
    {
        $sql = "SELECT vt.MaVaiTro, vt.TenVaiTro, vt.HanMucGiam
                FROM TAIKHOAN_VAITRO tv
                JOIN VAI_TRO vt ON tv.MaVaiTro = vt.MaVaiTro
                WHERE tv.MaTK = ?";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$maTK]);
        return $stmt->fetchAll() ?: [];
    }

    public function getPermissionsByMaTK(int $maTK): array
    {
        $sql = "SELECT DISTINCT q.MaQuyen, q.TenQuyen
                FROM TAIKHOAN_VAITRO tv
                JOIN VAITRO_QUYEN vq ON tv.MaVaiTro = vq.MaVaiTro
                JOIN QUYEN q ON vq.MaQuyen = q.MaQuyen
                WHERE tv.MaTK = ?";
        $stmt = $this->getDb()->prepare($sql);
        $stmt->execute([$maTK]);
        return $stmt->fetchAll() ?: [];
    }

    private function getOrCreateRole(string $tenVaiTro, float $hanMucGiam = 0.0): ?int
    {
        $db = $this->getDb();
        $sql = "SELECT MaVaiTro FROM VAI_TRO WHERE TenVaiTro = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$tenVaiTro]);
        $row = $stmt->fetch();

        if ($row) {
            return (int)$row['MaVaiTro'];
        }

        try {
            $insertSql = "INSERT INTO VAI_TRO (TenVaiTro, HanMucGiam) OUTPUT INSERTED.MaVaiTro VALUES (?, ?)";
            $insertStmt = $db->prepare($insertSql);
            $insertStmt->execute([$tenVaiTro, $hanMucGiam]);
            $inserted = $insertStmt->fetch();
            return isset($inserted['MaVaiTro']) ? (int)$inserted['MaVaiTro'] : null;
        } catch (Exception) {
            $insertSql = "INSERT INTO VAI_TRO (TenVaiTro, HanMucGiam) VALUES (?, ?)";
            $insertStmt = $db->prepare($insertSql);
            $insertStmt->execute([$tenVaiTro, $hanMucGiam]);
            $id = $db->lastInsertId();
            return $id ? (int)$id : null;
        }
    }
}
