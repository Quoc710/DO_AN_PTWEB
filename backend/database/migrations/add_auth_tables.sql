USE QuanLyCuaHangVai;
SET NAMES utf8mb4;

-- =====================================================================
-- MIGRATION: Thêm auth cho KHACH_HANG, mở rộng TAI_KHOAN, tạo DANG_KY_AFFILIATE
-- =====================================================================

-- 1. Thêm cột auth vào KHACH_HANG
ALTER TABLE KHACH_HANG
    ADD COLUMN Email        VARCHAR(150) NULL,
    ADD COLUMN MatKhauHash  VARCHAR(255) NULL,
    ADD COLUMN TenDangNhap  VARCHAR(50)  NULL,
    ADD COLUMN TrangThai    VARCHAR(30)  NOT NULL DEFAULT 'Chưa kích hoạt',
    ADD COLUMN TokenKichHoat VARCHAR(255) NULL,
    ADD COLUMN HanKichHoat  DATETIME NULL,
    ADD UNIQUE KEY UQ_KH_Email       (Email),
    ADD UNIQUE KEY UQ_KH_TenDangNhap (TenDangNhap);

-- 1b. Bổ sung cột TrangThai cho PHIEU_BAN (nếu CSDL mới chỉ có TrangThaiERP)
ALTER TABLE PHIEU_BAN
    ADD COLUMN TrangThai VARCHAR(50) NULL DEFAULT 'Chờ xác nhận';

-- 2. Mở rộng TAI_KHOAN (nhân viên/admin)
ALTER TABLE TAI_KHOAN
    ADD COLUMN Email    VARCHAR(150) NULL,
    ADD COLUMN TrangThai VARCHAR(30) NOT NULL DEFAULT 'Hoạt động',
    ADD COLUMN LoaiTK   VARCHAR(20)  NOT NULL DEFAULT 'nhan_vien';

-- 3. Bảng đăng ký Affiliate chờ duyệt
CREATE TABLE IF NOT EXISTS DANG_KY_AFFILIATE (
    MaDKAff     INT AUTO_INCREMENT PRIMARY KEY,
    TenKH       VARCHAR(100) NOT NULL,
    Email       VARCHAR(150) NOT NULL,
    SDT         VARCHAR(15)  NULL,
    TenDangNhap VARCHAR(50)  NOT NULL,
    MatKhauHash VARCHAR(255) NOT NULL,
    TrangThai   VARCHAR(30)  NOT NULL DEFAULT 'Chờ duyệt',
    NgayDangKy  DATETIME DEFAULT CURRENT_TIMESTAMP,
    GhiChu      VARCHAR(500) NULL,
    CONSTRAINT UQ_DKA_Email       UNIQUE (Email),
    CONSTRAINT UQ_DKA_TenDangNhap UNIQUE (TenDangNhap)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Seed tài khoản Admin
-- username: admin | password: Admin@123
-- (hash được tạo bằng password_hash('Admin@123', PASSWORD_BCRYPT))

-- 4a. Thêm nhân viên admin nếu chưa có
INSERT INTO NHAN_VIEN (TenNV, ViTri)
SELECT 'Administrator', 'Quản trị viên'
WHERE NOT EXISTS (SELECT 1 FROM NHAN_VIEN WHERE ViTri = 'Quản trị viên');

-- 4b. Thêm vai trò Admin nếu chưa có
INSERT INTO VAI_TRO (TenVaiTro, HanMucGiam)
SELECT 'Admin', 100.00
WHERE NOT EXISTS (SELECT 1 FROM VAI_TRO WHERE TenVaiTro = 'Admin');

-- 4c. Tạo tài khoản TAI_KHOAN cho admin
INSERT INTO TAI_KHOAN (TenTK, MatKhauHash, MaNV, TrangThai, LoaiTK)
SELECT
    'admin',
    '$2y$10$sb8sAfup3p.OLmbVrtE1qu103/ClIGAKh9YW0JxO1B5ZpQJbab7pm',
    (SELECT MaNV FROM NHAN_VIEN WHERE ViTri = 'Quản trị viên' LIMIT 1),
    'Hoạt động',
    'admin'
WHERE NOT EXISTS (SELECT 1 FROM TAI_KHOAN WHERE TenTK = 'admin');

-- 4d. Gán vai trò Admin cho tài khoản admin
INSERT INTO TAIKHOAN_VAITRO (MaTK, MaVaiTro)
SELECT
    (SELECT MaTK FROM TAI_KHOAN WHERE TenTK = 'admin' LIMIT 1),
    (SELECT MaVaiTro FROM VAI_TRO WHERE TenVaiTro = 'Admin' LIMIT 1)
WHERE NOT EXISTS (
    SELECT 1 FROM TAIKHOAN_VAITRO
    WHERE MaTK = (SELECT MaTK FROM TAI_KHOAN WHERE TenTK = 'admin' LIMIT 1)
);

-- Kiểm tra kết quả
SELECT 'admin account' AS info, MaTK, TenTK, LoaiTK, TrangThai FROM TAI_KHOAN WHERE TenTK = 'admin';
