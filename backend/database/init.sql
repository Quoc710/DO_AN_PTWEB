-- ============================================================
-- QuanLyCuaHangVai — MySQL 8.0 Init Schema
-- Gộp từ: QuanLyCuaHangVai.sql (MSSQL) + schema_mysql.sql (Admin)
-- Charset: utf8mb4_unicode_ci
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `QuanLyCuaHangVai`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `QuanLyCuaHangVai`;

-- ============================================================
-- 1. NHÓM DANH MỤC SẢN PHẨM
-- ============================================================

CREATE TABLE IF NOT EXISTS NHOM_SAN_PHAM (
    MaNhomSP    INT AUTO_INCREMENT PRIMARY KEY,
    TenNhomSP   VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS SAN_PHAM (
    MaSP        INT AUTO_INCREMENT PRIMARY KEY,
    MaNhomSP    INT NOT NULL,
    TenSP       VARCHAR(200) NOT NULL,
    MoTa        TEXT NULL,
    CONSTRAINT FK_SP_NHOM FOREIGN KEY (MaNhomSP) REFERENCES NHOM_SAN_PHAM(MaNhomSP)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS BIEN_THE_SP (
    MaBienThe       INT AUTO_INCREMENT PRIMARY KEY,
    MaSP            INT NOT NULL,
    NhomSP          VARCHAR(50)  NULL,
    MauSac          VARCHAR(50)  NULL,
    ChatLieuSize    VARCHAR(200) NULL,
    MaSKU           VARCHAR(50)  NULL,
    KhoChuanBao     VARCHAR(50)  NULL,
    TrongLuong_gsm  DECIMAL(10,2) NULL,
    MaHexMau        VARCHAR(10)  NULL,
    IconClass       VARCHAR(100) NULL,
    BadgeClass      VARCHAR(200) NULL,
    AnhDaiDien      VARCHAR(500) NULL,
    TrangThaiWeb    VARCHAR(50)  NULL DEFAULT 'Đang bán Web',
    CONSTRAINT FK_BIENTHE_SP FOREIGN KEY (MaSP) REFERENCES SAN_PHAM(MaSP) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS BANG_GIA (
    MaBangGia   INT AUTO_INCREMENT PRIMARY KEY,
    MaBienThe   INT NOT NULL,
    LoaiKhach   VARCHAR(50)   NULL,
    DonGia      DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_BANG_GIA_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE CASCADE,
    CONSTRAINT CK_BANG_GIA_DonGia CHECK (DonGia >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. NHÓM NHÂN VIÊN & PHÂN QUYỀN
-- ============================================================

CREATE TABLE IF NOT EXISTS VAI_TRO (
    MaVaiTro    INT AUTO_INCREMENT PRIMARY KEY,
    TenVaiTro   VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS NHAN_VIEN (
    MaNV        INT AUTO_INCREMENT PRIMARY KEY,
    HoTen       VARCHAR(100) NOT NULL,
    Email       VARCHAR(150) NULL,
    SDT         VARCHAR(15)  NULL,
    MaVaiTro    INT NOT NULL,
    CONSTRAINT FK_NV_VAITRO FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS QUYEN (
    MaQuyen     INT AUTO_INCREMENT PRIMARY KEY,
    TenQuyen    VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS VAITRO_QUYEN (
    MaVaiTro    INT NOT NULL,
    MaQuyen     INT NOT NULL,
    PRIMARY KEY (MaVaiTro, MaQuyen),
    CONSTRAINT FK_VTQ_VT FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro) ON DELETE CASCADE,
    CONSTRAINT FK_VTQ_Q  FOREIGN KEY (MaQuyen)  REFERENCES QUYEN(MaQuyen)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. NHÓM TÀI KHOẢN NGƯỜI DÙNG
-- ============================================================

CREATE TABLE IF NOT EXISTS TAI_KHOAN (
    MaTaiKhoan      INT AUTO_INCREMENT PRIMARY KEY,
    Email           VARCHAR(150) NOT NULL UNIQUE,
    MatKhauHash     VARCHAR(255) NOT NULL,
    HoTen           VARCHAR(100) NULL,
    SDT             VARCHAR(15)  NULL,
    MaVaiTro        INT NULL,
    TrangThai       VARCHAR(30)  NOT NULL DEFAULT 'active',
    NgayTao         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ResetToken      VARCHAR(255) NULL,
    ResetTokenExpiry DATETIME   NULL,
    CONSTRAINT FK_TAIKHOAN_VAITRO FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. NHÓM LOOKBOOK & AFFILIATE
-- ============================================================

CREATE TABLE IF NOT EXISTS LOOKBOOK (
    MaLookBook  INT AUTO_INCREMENT PRIMARY KEY,
    TenLookBook VARCHAR(150) NOT NULL,
    URLHinhAnh  VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS NHANVIEN_LOOKBOOK (
    MaNV        INT NOT NULL,
    MaLookBook  INT NOT NULL,
    PRIMARY KEY (MaNV, MaLookBook),
    CONSTRAINT FK_NVLB_NV FOREIGN KEY (MaNV)       REFERENCES NHAN_VIEN(MaNV)      ON DELETE CASCADE,
    CONSTRAINT FK_NVLB_LB FOREIGN KEY (MaLookBook) REFERENCES LOOKBOOK(MaLookBook) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS VI_HOA_HONG (
    MaHoaHong       INT AUTO_INCREMENT PRIMARY KEY,
    MaNV            INT NOT NULL UNIQUE,
    SoTienHoaHong   DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    TrangThai       VARCHAR(30)   NULL,
    CONSTRAINT FK_VI_HOA_HONG_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV),
    CONSTRAINT CK_VI_HOA_HONG_SoTien CHECK (SoTienHoaHong >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. NHÓM KIỂM KÊ - TỒN KHO
-- ============================================================

CREATE TABLE IF NOT EXISTS CUON_VAI (
    MaCuonVai       INT AUTO_INCREMENT PRIMARY KEY,
    MaBienThe       INT NOT NULL,
    DoDaiBanDau     DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    DoDaiConLai     DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT FK_CUON_VAI_BT  FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CUON_VAI_DoDai CHECK (DoDaiBanDau >= 0 AND DoDaiConLai >= 0 AND DoDaiConLai <= DoDaiBanDau)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS TON_KHO (
    MaTonKho        INT AUTO_INCREMENT PRIMARY KEY,
    MaCuonVai       INT NOT NULL,
    SoLuongTon      DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT FK_TON_KHO_CV  FOREIGN KEY (MaCuonVai) REFERENCES CUON_VAI(MaCuonVai),
    CONSTRAINT CK_TON_KHO_SL  CHECK (SoLuongTon >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PHIEU_KIEM_KE (
    MaPhieuKK   INT AUTO_INCREMENT PRIMARY KEY,
    MaNV        INT NOT NULL,
    NgayKK      DATE NOT NULL DEFAULT (CURRENT_DATE),
    TrangThai   VARCHAR(30) NULL,
    CONSTRAINT FK_PHIEU_KIEM_KE_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CT_KIEM_KE (
    MaCTKiemKe  INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuKK   INT NOT NULL,
    MaTonKho    INT NOT NULL,
    TonLyThuyet DECIMAL(18,2) NOT NULL,
    TonThucTe   DECIMAL(18,2) NOT NULL,
    ChenhLech   DECIMAL(18,2) GENERATED ALWAYS AS (TonThucTe - TonLyThuyet) STORED,
    UNIQUE KEY UQ_CT_KIEM_KE (MaPhieuKK, MaTonKho),
    CONSTRAINT FK_CT_KIEM_KE_PKK FOREIGN KEY (MaPhieuKK) REFERENCES PHIEU_KIEM_KE(MaPhieuKK) ON DELETE CASCADE,
    CONSTRAINT FK_CT_KIEM_KE_TK  FOREIGN KEY (MaTonKho)  REFERENCES TON_KHO(MaTonKho),
    CONSTRAINT CK_CT_KIEM_KE_Ton CHECK (TonLyThuyet >= 0 AND TonThucTe >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. NHÓM KHÁCH HÀNG
-- ============================================================

CREATE TABLE IF NOT EXISTS KHACH_HANG (
    MaKH        INT AUTO_INCREMENT PRIMARY KEY,
    TenKH       VARCHAR(100)  NOT NULL,
    SDT         VARCHAR(15)   NULL,
    LoaiKhach   VARCHAR(30)   NULL,
    TongNo      DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT CK_KHACH_HANG_TongNo CHECK (TongNo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS DANH_GIA_KHACH_HANG (
    MaDanhGia       INT AUTO_INCREMENT PRIMARY KEY,
    MaKH            INT NOT NULL,
    SoSao           TINYINT NOT NULL,
    NoiDung         TEXT NULL,
    NgayDanhGia     DATE NOT NULL DEFAULT (CURRENT_DATE),
    MaBienThe       INT NULL,
    IPKhach         VARCHAR(50) NULL,
    LoaiDanhGia     VARCHAR(100) NULL,
    TrangThaiDuyet  VARCHAR(50) NULL DEFAULT 'Chờ duyệt',
    IsGhim          TINYINT(1) NOT NULL DEFAULT 0,
    SoAnhDinhKem    INT NOT NULL DEFAULT 0,
    CONSTRAINT FK_DANH_GIA_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT FK_DANH_GIA_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_DANH_GIA_SoSao CHECK (SoSao BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PHIEU_THU_NO (
    MaPhieuThu  INT AUTO_INCREMENT PRIMARY KEY,
    MaKH        INT NOT NULL,
    HinhThucThu VARCHAR(50)   NULL,
    SoTienThu   DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_PHIEU_THU_NO_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT CK_PHIEU_THU_SoTien CHECK (SoTienThu > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. NHÓM BÁN HÀNG — VOUCHER — TRẢ HÀNG
-- ============================================================

CREATE TABLE IF NOT EXISTS VOUCHER (
    MaVoucher       INT AUTO_INCREMENT PRIMARY KEY,
    GiaTriVoucher   DECIMAL(18,2) NOT NULL,
    HanSuDung       DATE NULL,
    MaCode          VARCHAR(50)   NULL UNIQUE,
    TenChuongTrinh  VARCHAR(200)  NULL,
    MoTaDieuKien    TEXT NULL,
    LoaiGiam        VARCHAR(10)   NULL DEFAULT '%',
    GioiHanLuot     INT NOT NULL DEFAULT 0,
    SoLuotDaDung    INT NOT NULL DEFAULT 0,
    TrangThai       TINYINT(1)    NOT NULL DEFAULT 1,
    CONSTRAINT CK_VOUCHER_GiaTri CHECK (GiaTriVoucher > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PHIEU_BAN (
    MaPhieuBan      INT AUTO_INCREMENT PRIMARY KEY,
    MaKH            INT NOT NULL,
    NgayBan         DATE     NOT NULL DEFAULT (CURRENT_DATE),
    TongTien        DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    MaDonCode       VARCHAR(30)   NULL,
    ThoiGianBan     TIME NULL,
    TrangThai       VARCHAR(50)   NULL DEFAULT 'Chờ xác nhận',
    TrangThaiERP    VARCHAR(100)  NULL,
    CONSTRAINT FK_PHIEU_BAN_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT CK_PHIEU_BAN_TongTien CHECK (TongTien >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CT_PHIEU_BAN (
    MaCTPhieuBan    INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuBan      INT NOT NULL,
    MaBienThe       INT NOT NULL,
    SoLuongBan      DECIMAL(18,2) NOT NULL,
    DonGiaBan       DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_PB_PB FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_CT_PB_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CT_PB_SL  CHECK (SoLuongBan > 0),
    CONSTRAINT CK_CT_PB_Gia CHECK (DonGiaBan  >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PHIEU_BAN_VOUCHER (
    MaPhieuBan  INT NOT NULL,
    MaVoucher   INT NOT NULL,
    PRIMARY KEY (MaPhieuBan, MaVoucher),
    CONSTRAINT FK_PBV_PB FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_PBV_V  FOREIGN KEY (MaVoucher)  REFERENCES VOUCHER(MaVoucher)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS PHIEU_TRA_HANG (
    MaPhieuTra      INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuBanGoc   INT NOT NULL,
    LyDoTra         VARCHAR(255) NULL,
    CONSTRAINT FK_PHIEU_TRA_PB FOREIGN KEY (MaPhieuBanGoc) REFERENCES PHIEU_BAN(MaPhieuBan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CT_TRA_HANG (
    MaCTTraHang     INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuTra      INT NOT NULL,
    MaBienThe       INT NOT NULL,
    SoLuongTra      DECIMAL(18,2) NOT NULL,
    TinhTrangHang   VARCHAR(100)  NULL,
    NhapLaiKho      TINYINT(1)    NOT NULL DEFAULT 0,
    CONSTRAINT FK_CT_TRA_PT FOREIGN KEY (MaPhieuTra) REFERENCES PHIEU_TRA_HANG(MaPhieuTra) ON DELETE CASCADE,
    CONSTRAINT FK_CT_TRA_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CT_TRA_SL CHECK (SoLuongTra > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. NHÓM ĐỊA CHỈ & ĐƠN HÀNG (từ module Đơn Hàng)
-- ============================================================

CREATE TABLE IF NOT EXISTS DON_HANG (
    MaDonHang       INT AUTO_INCREMENT PRIMARY KEY,
    MaTaiKhoan      INT NOT NULL,
    NgayDat         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    TongTien        DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    TrangThai       VARCHAR(50) NOT NULL DEFAULT 'Chờ xử lý',
    DiaChiGiao      TEXT NULL,
    GhiChu          TEXT NULL,
    CONSTRAINT FK_DONHANG_TK FOREIGN KEY (MaTaiKhoan) REFERENCES TAI_KHOAN(MaTaiKhoan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CT_DON_HANG (
    MaCTDonHang     INT AUTO_INCREMENT PRIMARY KEY,
    MaDonHang       INT NOT NULL,
    MaBienThe       INT NOT NULL,
    SoLuong         DECIMAL(18,2) NOT NULL,
    DonGia          DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_DH_DH FOREIGN KEY (MaDonHang) REFERENCES DON_HANG(MaDonHang) ON DELETE CASCADE,
    CONSTRAINT FK_CT_DH_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CT_DH_SL  CHECK (SoLuong > 0),
    CONSTRAINT CK_CT_DH_Gia CHECK (DonGia >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Kiểm tra: SELECT COUNT(*) AS SoBang FROM information_schema.tables
--           WHERE table_schema = 'QuanLyCuaHangVai';
-- ============================================================
