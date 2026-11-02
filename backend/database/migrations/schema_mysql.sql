/* =====================================================================
   HỆ THỐNG QUẢN LÝ CỬA HÀNG VẢI & AURA ADMIN PORTAL (MODULE 4)
   Chuẩn CSDL: MySQL / MariaDB (Engine=InnoDB, Charset=utf8mb4_unicode_ci)
   Chuyển đổi trọn vẹn 30 bảng từ QLCHV.txt + Mở rộng cột cho code.html
   ===================================================================== */

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS QuanLyCuaHangVai
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE QuanLyCuaHangVai;

/* ---------------------------------------------------------------------
   XÓA BẢNG NẾU ĐÃ TỒN TẠI (ĐỂ CHẠY LẠI AN TOÀN)
   --------------------------------------------------------------------- */
DROP TABLE IF EXISTS CT_TRA_HANG;
DROP TABLE IF EXISTS PHIEU_TRA_HANG;
DROP TABLE IF EXISTS PHIEU_BAN_VOUCHER;
DROP TABLE IF EXISTS CT_PHIEU_BAN;
DROP TABLE IF EXISTS PHIEU_BAN;
DROP TABLE IF EXISTS VOUCHER;
DROP TABLE IF EXISTS PHIEU_THU_NO;
DROP TABLE IF EXISTS DANH_GIA_KHACH_HANG;
DROP TABLE IF EXISTS KHACH_HANG;
DROP TABLE IF EXISTS CT_KIEM_KE;
DROP TABLE IF EXISTS PHIEU_KIEM_KE;
DROP TABLE IF EXISTS TON_KHO;
DROP TABLE IF EXISTS NHANVIEN_LOOKBOOK;
DROP TABLE IF EXISTS LOOKBOOK;
DROP TABLE IF EXISTS VI_HOA_HONG;
DROP TABLE IF EXISTS VAITRO_QUYEN;
DROP TABLE IF EXISTS TAIKHOAN_VAITRO;
DROP TABLE IF EXISTS TAI_KHOAN;
DROP TABLE IF EXISTS QUYEN;
DROP TABLE IF EXISTS VAI_TRO;
DROP TABLE IF EXISTS NHAN_VIEN;
DROP TABLE IF EXISTS LICH_SU_GIA;
DROP TABLE IF EXISTS YEU_CAU_DUYET_GIA;
DROP TABLE IF EXISTS BANG_GIA;
DROP TABLE IF EXISTS CUON_VAI;
DROP TABLE IF EXISTS CT_PHIEU_NHAP;
DROP TABLE IF EXISTS BIEN_THE_SP;
DROP TABLE IF EXISTS SAN_PHAM;
DROP TABLE IF EXISTS PHIEU_NHAP;
DROP TABLE IF EXISTS NHA_CUNG_CAP;

SET FOREIGN_KEY_CHECKS = 1;

/* =====================================================================
   1. NHÓM NHẬP HÀNG (2 BẢNG)
   ===================================================================== */

-- Bảng 1: NHA_CUNG_CAP
CREATE TABLE NHA_CUNG_CAP (
    MaNCC       INT AUTO_INCREMENT PRIMARY KEY,
    TenNCC      VARCHAR(150) NOT NULL,
    PhanLoai    VARCHAR(50)  NULL,
    SDT         VARCHAR(15)  NULL,
    DiaChi      VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 2: PHIEU_NHAP
CREATE TABLE PHIEU_NHAP (
    MaPhieuNhap   INT AUTO_INCREMENT PRIMARY KEY,
    MaNCC         INT NOT NULL,                          -- CUNGCAP (1,1)
    NgayNhap      DATE NOT NULL DEFAULT (CURRENT_DATE),
    HanThanhToan  DATE NULL,
    TongTien      DECIMAL(18,2) NOT NULL DEFAULT 0,
    TrangThai     VARCHAR(30) NULL,
    CONSTRAINT FK_PHIEU_NHAP_NCC FOREIGN KEY (MaNCC) REFERENCES NHA_CUNG_CAP(MaNCC),
    CONSTRAINT CK_PHIEU_NHAP_TongTien CHECK (TongTien >= 0),
    CONSTRAINT CK_PHIEU_NHAP_Han CHECK (HanThanhToan IS NULL OR HanThanhToan >= NgayNhap)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* =====================================================================
   2. NHÓM SẢN PHẨM (4 BẢNG)
   ===================================================================== */

-- Bảng 3: SAN_PHAM
CREATE TABLE SAN_PHAM (
    MaSP     INT AUTO_INCREMENT PRIMARY KEY,
    TenSP    VARCHAR(150) NOT NULL,
    NhomSP   VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 4: BIEN_THE_SP (Đã mở rộng các cột hiển thị trên code.html)
CREATE TABLE BIEN_THE_SP (
    MaBienThe      INT AUTO_INCREMENT PRIMARY KEY,
    MaSP           INT NOT NULL,                         -- BAOGOM
    DonViTinh      VARCHAR(20) NOT NULL,
    MauSac         VARCHAR(50) NULL,
    ChatLieuSize   VARCHAR(100) NULL,                    -- thuộc tính "ChatLieu/Size"
    -- Các cột mở rộng cho giao diện Quản lý Mẫu Vải Mới (CRUD) & Đồng bộ Odoo
    MaSKU          VARCHAR(50) UNIQUE,
    KhoVai         VARCHAR(30) NULL,
    DinhLuongGSM   VARCHAR(30) NULL,
    MaMauHex       VARCHAR(10) NULL,
    IconClass      VARCHAR(50) NULL,
    BadgeMauClass  VARCHAR(100) NULL,
    HinhAnhMacro   VARCHAR(255) NULL,
    TrangThaiWeb   VARCHAR(30) DEFAULT 'Đang bán Web',
    CONSTRAINT FK_BIEN_THE_SP_SP FOREIGN KEY (MaSP) REFERENCES SAN_PHAM(MaSP) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 5: CT_PHIEU_NHAP
CREATE TABLE CT_PHIEU_NHAP (
    MaCTPhieuNhap  INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuNhap    INT NOT NULL,                         -- GOM
    MaBienThe      INT NOT NULL,                         -- THAMCHIEU
    SoLuong        DECIMAL(18,2) NOT NULL,
    DonGiaNhap     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_PHIEU_NHAP_PN FOREIGN KEY (MaPhieuNhap) REFERENCES PHIEU_NHAP(MaPhieuNhap) ON DELETE CASCADE,
    CONSTRAINT FK_CT_PHIEU_NHAP_BT FOREIGN KEY (MaBienThe)   REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE CASCADE,
    CONSTRAINT CK_CT_PHIEU_NHAP_SL  CHECK (SoLuong > 0),
    CONSTRAINT CK_CT_PHIEU_NHAP_Gia CHECK (DonGiaNhap >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 6: CUON_VAI
CREATE TABLE CUON_VAI (
    MaCuonVai     INT AUTO_INCREMENT PRIMARY KEY,
    MaBienThe     INT NOT NULL,                          -- THAMCHIEU
    DoDaiBanDau   DECIMAL(10,2) NOT NULL,
    DoDaiConLai   DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_CUON_VAI_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE CASCADE,
    CONSTRAINT CK_CUON_VAI_DoDai CHECK (DoDaiBanDau > 0 AND DoDaiConLai >= 0 AND DoDaiConLai <= DoDaiBanDau)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* =====================================================================
   3. NHÓM GIÁ (3 BẢNG)
   ===================================================================== */

-- Bảng 7: BANG_GIA
CREATE TABLE BANG_GIA (
    MaBangGia  INT AUTO_INCREMENT PRIMARY KEY,
    MaBienThe  INT NOT NULL,                             -- DINHGIA
    LoaiKhach  VARCHAR(30) NOT NULL,
    DonGia     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_BANG_GIA_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE CASCADE,
    CONSTRAINT CK_BANG_GIA_DonGia CHECK (DonGia >= 0),
    CONSTRAINT UQ_BANG_GIA_BT_Loai UNIQUE (MaBienThe, LoaiKhach)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 8: YEU_CAU_DUYET_GIA
CREATE TABLE YEU_CAU_DUYET_GIA (
    MaYeuCau        INT AUTO_INCREMENT PRIMARY KEY,
    TrangThaiDuyet  VARCHAR(30) NOT NULL DEFAULT 'Chờ duyệt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 9: LICH_SU_GIA
CREATE TABLE LICH_SU_GIA (
    MaLSGia    INT AUTO_INCREMENT PRIMARY KEY,
    MaBangGia  INT NOT NULL,                             -- LUUGIA
    MaYeuCau   INT NULL,                                 -- DUYET (0,1)-(0,1)
    GiaCu      DECIMAL(18,2) NULL,
    GiaMoi     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_LICH_SU_GIA_BG FOREIGN KEY (MaBangGia) REFERENCES BANG_GIA(MaBangGia) ON DELETE CASCADE,
    CONSTRAINT FK_LICH_SU_GIA_YC FOREIGN KEY (MaYeuCau)  REFERENCES YEU_CAU_DUYET_GIA(MaYeuCau),
    CONSTRAINT CK_LICH_SU_GIA_Gia CHECK ((GiaCu IS NULL OR GiaCu >= 0) AND GiaMoi >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quan hệ DUYET là 1-1 tùy chọn: trong MySQL/InnoDB, UNIQUE INDEX cho phép nhiều giá trị NULL
CREATE UNIQUE INDEX UX_LICH_SU_GIA_MaYeuCau ON LICH_SU_GIA(MaYeuCau);

/* =====================================================================
   4. NHÓM NHÂN SỰ - TÀI KHOẢN - PHÂN QUYỀN (9 BẢNG)
   ===================================================================== */

-- Bảng 10: NHAN_VIEN
CREATE TABLE NHAN_VIEN (
    MaNV    INT AUTO_INCREMENT PRIMARY KEY,
    TenNV   VARCHAR(100) NOT NULL,
    ViTri   VARCHAR(50)  NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 11: VAI_TRO
CREATE TABLE VAI_TRO (
    MaVaiTro    INT AUTO_INCREMENT PRIMARY KEY,
    TenVaiTro   VARCHAR(50) NOT NULL,
    HanMucGiam  DECIMAL(5,2) NOT NULL DEFAULT 0,
    CONSTRAINT UQ_VAI_TRO_Ten UNIQUE (TenVaiTro),
    CONSTRAINT CK_VAI_TRO_HanMucGiam CHECK (HanMucGiam BETWEEN 0 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 12: QUYEN
CREATE TABLE QUYEN (
    MaQuyen   INT AUTO_INCREMENT PRIMARY KEY,
    TenQuyen  VARCHAR(100) NOT NULL,
    CONSTRAINT UQ_QUYEN_Ten UNIQUE (TenQuyen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 13: TAI_KHOAN
CREATE TABLE TAI_KHOAN (
    MaTK         INT AUTO_INCREMENT PRIMARY KEY,
    MaNV         INT NOT NULL,                           -- DANGNHAP 1-1
    TenTK        VARCHAR(50) NOT NULL,
    MatKhauHash  VARCHAR(255) NOT NULL,
    CONSTRAINT UQ_TAI_KHOAN_NV  UNIQUE (MaNV),
    CONSTRAINT UQ_TAI_KHOAN_Ten UNIQUE (TenTK),
    CONSTRAINT FK_TAI_KHOAN_NV  FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 14: TAIKHOAN_VAITRO (Tài khoản n - n Vai trò)
CREATE TABLE TAIKHOAN_VAITRO (
    MaTK      INT NOT NULL,
    MaVaiTro  INT NOT NULL,
    CONSTRAINT PK_TAIKHOAN_VAITRO PRIMARY KEY (MaTK, MaVaiTro),
    CONSTRAINT FK_TKVT_TK FOREIGN KEY (MaTK)     REFERENCES TAI_KHOAN(MaTK)  ON DELETE CASCADE,
    CONSTRAINT FK_TKVT_VT FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 15: VAITRO_QUYEN (Vai trò n - n Quyền)
CREATE TABLE VAITRO_QUYEN (
    MaVaiTro  INT NOT NULL,
    MaQuyen   INT NOT NULL,
    CONSTRAINT PK_VAITRO_QUYEN PRIMARY KEY (MaVaiTro, MaQuyen),
    CONSTRAINT FK_VTQ_VT FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro) ON DELETE CASCADE,
    CONSTRAINT FK_VTQ_Q  FOREIGN KEY (MaQuyen)  REFERENCES QUYEN(MaQuyen)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 16: VI_HOA_HONG
CREATE TABLE VI_HOA_HONG (
    MaHoaHong       INT AUTO_INCREMENT PRIMARY KEY,
    MaNV            INT NOT NULL,                        -- CO 1-1
    SoTienHoaHong   DECIMAL(18,2) NOT NULL DEFAULT 0,
    TrangThai       VARCHAR(30) NULL,
    CONSTRAINT UQ_VI_HOA_HONG_NV     UNIQUE (MaNV),
    CONSTRAINT FK_VI_HOA_HONG_NV     FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV),
    CONSTRAINT CK_VI_HOA_HONG_SoTien CHECK (SoTienHoaHong >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 17: LOOKBOOK
CREATE TABLE LOOKBOOK (
    MaLookBook   INT AUTO_INCREMENT PRIMARY KEY,
    TenLookBook  VARCHAR(150) NOT NULL,
    URLHinhAnh   VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 18: NHANVIEN_LOOKBOOK (Nhân viên n - n Lookbook)
CREATE TABLE NHANVIEN_LOOKBOOK (
    MaNV        INT NOT NULL,
    MaLookBook  INT NOT NULL,
    CONSTRAINT PK_NHANVIEN_LOOKBOOK PRIMARY KEY (MaNV, MaLookBook),
    CONSTRAINT FK_NVLB_NV FOREIGN KEY (MaNV)       REFERENCES NHAN_VIEN(MaNV)    ON DELETE CASCADE,
    CONSTRAINT FK_NVLB_LB FOREIGN KEY (MaLookBook) REFERENCES LOOKBOOK(MaLookBook) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* =====================================================================
   5. NHÓM KIỂM KÊ - TỒN KHO (3 BẢNG)
   ===================================================================== */

-- Bảng 19: TON_KHO
CREATE TABLE TON_KHO (
    MaTonKho     INT AUTO_INCREMENT PRIMARY KEY,
    MaCuonVai    INT NOT NULL,                           -- THAMCHIEU
    SoLuongTon   DECIMAL(18,2) NOT NULL DEFAULT 0,
    CONSTRAINT FK_TON_KHO_CV      FOREIGN KEY (MaCuonVai) REFERENCES CUON_VAI(MaCuonVai) ON DELETE CASCADE,
    CONSTRAINT CK_TON_KHO_SoLuong CHECK (SoLuongTon >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 20: PHIEU_KIEM_KE
CREATE TABLE PHIEU_KIEM_KE (
    MaPhieuKK  INT AUTO_INCREMENT PRIMARY KEY,
    MaNV       INT NOT NULL,                             -- LAP
    NgayKK     DATE NOT NULL DEFAULT (CURRENT_DATE),
    TrangThai  VARCHAR(30) NULL,
    CONSTRAINT FK_PHIEU_KIEM_KE_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 21: CT_KIEM_KE
CREATE TABLE CT_KIEM_KE (
    MaCTKiemKe   INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuKK    INT NOT NULL,                           -- GOM
    MaTonKho     INT NOT NULL,                           -- DOICHIEU
    TonLyThuyet  DECIMAL(18,2) NOT NULL,
    TonThucTe    DECIMAL(18,2) NOT NULL,
    ChenhLech    DECIMAL(18,2) GENERATED ALWAYS AS (TonThucTe - TonLyThuyet) STORED,
    CONSTRAINT FK_CT_KIEM_KE_PKK FOREIGN KEY (MaPhieuKK) REFERENCES PHIEU_KIEM_KE(MaPhieuKK) ON DELETE CASCADE,
    CONSTRAINT FK_CT_KIEM_KE_TK  FOREIGN KEY (MaTonKho)  REFERENCES TON_KHO(MaTonKho) ON DELETE CASCADE,
    CONSTRAINT UQ_CT_KIEM_KE     UNIQUE (MaPhieuKK, MaTonKho),
    CONSTRAINT CK_CT_KIEM_KE_Ton CHECK (TonLyThuyet >= 0 AND TonThucTe >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* =====================================================================
   6. NHÓM KHÁCH HÀNG (3 BẢNG)
   ===================================================================== */

-- Bảng 22: KHACH_HANG
CREATE TABLE KHACH_HANG (
    MaKH       INT AUTO_INCREMENT PRIMARY KEY,
    TenKH      VARCHAR(100) NOT NULL,
    SDT        VARCHAR(15) NULL,
    LoaiKhach  VARCHAR(30) NULL,
    TongNo     DECIMAL(18,2) NOT NULL DEFAULT 0,
    CONSTRAINT CK_KHACH_HANG_TongNo CHECK (TongNo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 23: DANH_GIA_KHACH_HANG (Đã mở rộng các cột cho Kiểm duyệt Bình luận)
CREATE TABLE DANH_GIA_KHACH_HANG (
    MaDanhGia       INT AUTO_INCREMENT PRIMARY KEY,
    MaKH            INT NOT NULL,                        -- CO (1,1)
    SoSao           TINYINT NOT NULL,
    NoiDung         VARCHAR(1000) NULL,
    NgayDanhGia     DATE NOT NULL DEFAULT (CURRENT_DATE),
    -- Các cột mở rộng cho mục 4: Duyệt Đánh Giá & Kiểm Duyệt Bình Luận
    MaBienThe       INT NULL,
    IPKhach         VARCHAR(45) NULL,
    LoaiDanhGia     VARCHAR(50) NULL,
    TrangThaiDuyet  VARCHAR(30) DEFAULT 'Chờ duyệt',
    IsGhim          TINYINT(1) DEFAULT 0,
    SoAnhDinhKem    INT DEFAULT 0,
    CONSTRAINT FK_DANH_GIA_KH    FOREIGN KEY (MaKH)      REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT FK_DANH_GIA_BT    FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE SET NULL,
    CONSTRAINT CK_DANH_GIA_SoSao CHECK (SoSao BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 24: PHIEU_THU_NO
CREATE TABLE PHIEU_THU_NO (
    MaPhieuThu    INT AUTO_INCREMENT PRIMARY KEY,
    MaKH          INT NOT NULL,                          -- THUNO (1,1)
    HinhThucThu   VARCHAR(50) NULL,
    SoTienThu     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_PHIEU_THU_NO_KH     FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT CK_PHIEU_THU_NO_SoTien CHECK (SoTienThu > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* =====================================================================
   7. NHÓM BÁN HÀNG - VOUCHER - TRẢ HÀNG (6 BẢNG)
   ===================================================================== */

-- Bảng 25: VOUCHER (Đã mở rộng các cột cho Quản lý Voucher & Khuyến Mãi)
CREATE TABLE VOUCHER (
    MaVoucher       INT AUTO_INCREMENT PRIMARY KEY,
    GiaTriVoucher   DECIMAL(18,2) NOT NULL,
    HanSuDung       DATE NULL,
    -- Các cột mở rộng cho mục 3: Quản lý Voucher & Khuyến Mãi
    MaCode          VARCHAR(50) UNIQUE,
    TenChuongTrinh  VARCHAR(150) NULL,
    MoTaDieuKien    VARCHAR(255) NULL,
    LoaiGiam        VARCHAR(10) DEFAULT '%',
    GioiHanLuot     INT DEFAULT 100,
    SoLuotDaDung    INT DEFAULT 0,
    TrangThai       TINYINT(1) DEFAULT 1,
    CONSTRAINT CK_VOUCHER_GiaTri CHECK (GiaTriVoucher > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 26: PHIEU_BAN (Đã mở rộng các cột cho Đồng bộ Đơn hàng & Xuất Excel Odoo)
CREATE TABLE PHIEU_BAN (
    MaPhieuBan    INT AUTO_INCREMENT PRIMARY KEY,
    MaKH          INT NOT NULL,                          -- MUA (1,1)
    NgayBan       DATE NOT NULL DEFAULT (CURRENT_DATE),
    TongTien      DECIMAL(18,2) NOT NULL DEFAULT 0,
    -- Các cột mở rộng cho mục 2: Đồng bộ Đơn hàng & Xuất File Chuẩn Odoo ERP
    MaDonCode     VARCHAR(20) UNIQUE,
    ThoiGianBan   TIME DEFAULT (CURRENT_TIME),
    TrangThaiERP  VARCHAR(50) DEFAULT 'Sẵn sàng xuất',
    CONSTRAINT FK_PHIEU_BAN_KH       FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT CK_PHIEU_BAN_TongTien CHECK (TongTien >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 27: CT_PHIEU_BAN
CREATE TABLE CT_PHIEU_BAN (
    MaCTPhieuBan  INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuBan    INT NOT NULL,                          -- GOM
    MaBienThe     INT NOT NULL,                          -- THAMCHIEU
    SoLuongBan    DECIMAL(18,2) NOT NULL,
    DonGiaBan     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_PHIEU_BAN_PB  FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_CT_PHIEU_BAN_BT  FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE CASCADE,
    CONSTRAINT CK_CT_PHIEU_BAN_SL  CHECK (SoLuongBan > 0),
    CONSTRAINT CK_CT_PHIEU_BAN_Gia CHECK (DonGiaBan >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 28: PHIEU_BAN_VOUCHER (Phiếu bán n - n Voucher)
CREATE TABLE PHIEU_BAN_VOUCHER (
    MaPhieuBan  INT NOT NULL,
    MaVoucher   INT NOT NULL,
    CONSTRAINT PK_PHIEU_BAN_VOUCHER PRIMARY KEY (MaPhieuBan, MaVoucher),
    CONSTRAINT FK_PBV_PB FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_PBV_V  FOREIGN KEY (MaVoucher)  REFERENCES VOUCHER(MaVoucher)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 29: PHIEU_TRA_HANG
CREATE TABLE PHIEU_TRA_HANG (
    MaPhieuTra     INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuBanGoc  INT NOT NULL,                         -- phiếu bán gốc
    LyDoTra        VARCHAR(255) NULL,
    CONSTRAINT FK_PHIEU_TRA_HANG_PB FOREIGN KEY (MaPhieuBanGoc) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng 30: CT_TRA_HANG
CREATE TABLE CT_TRA_HANG (
    MaCTTraHang    INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuTra     INT NOT NULL,                         -- CHITIET
    MaBienThe      INT NOT NULL,                         -- THAMCHIEU
    SoLuongTra     DECIMAL(18,2) NOT NULL,
    TinhTrangHang  VARCHAR(100) NULL,
    NhapLaiKho     TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT FK_CT_TRA_HANG_PT FOREIGN KEY (MaPhieuTra) REFERENCES PHIEU_TRA_HANG(MaPhieuTra) ON DELETE CASCADE,
    CONSTRAINT FK_CT_TRA_HANG_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE CASCADE,
    CONSTRAINT CK_CT_TRA_HANG_SL CHECK (SoLuongTra > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/* =====================================================================
   DỮ LIỆU MẪU (SEED DATA) KHỚP 100% GIAO DIỆN code.html
   ===================================================================== */

-- 1. NHÂN VIÊN & TÀI KHOẢN QUẢN TRỊ (Header: Võ Minh Quân - Quản trị viên Cấp cao)
INSERT INTO NHAN_VIEN (MaNV, TenNV, ViTri) VALUES
(1, 'Võ Minh Quân', 'Quản trị viên Cấp cao');

INSERT INTO VAI_TRO (MaVaiTro, TenVaiTro, HanMucGiam) VALUES
(1, 'Super Admin', 50.00);

INSERT INTO QUYEN (MaQuyen, TenQuyen) VALUES
(1, 'QUAN_LY_VAI_CRUD'),
(2, 'XUAT_EXCEL_ODOO'),
(3, 'QUAN_LY_VOUCHER'),
(4, 'KIEM_DUYET_DANH_GIA');

INSERT INTO TAI_KHOAN (MaTK, MaNV, TenTK, MatKhauHash) VALUES
(1, 1, 'admin.quan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO TAIKHOAN_VAITRO (MaTK, MaVaiTro) VALUES (1, 1);
INSERT INTO VAITRO_QUYEN (MaVaiTro, MaQuyen) VALUES (1, 1), (1, 2), (1, 3), (1, 4);

-- 2. NHÀ CUNG CẤP
INSERT INTO NHA_CUNG_CAP (MaNCC, TenNCC, PhanLoai, SDT, DiaChi) VALUES
(1, 'Nhà máy Dệt Lanh & Tơ Tằm Bảo Lộc', 'Nội địa Cao cấp', '02633868888', 'KCN Lộc Sơn, Bảo Lộc, Lâm Đồng'),
(2, 'Aura Textile Mills Korea', 'Nhập khẩu Hàn Quốc', '02839109999', 'Quận 1, TP. Hồ Chí Minh');

-- 3. SẢN PHẨM & BIẾN THỂ MẪU VẢI (10 mẫu trong bảng CRUD + 3 SKU trong bảng Đơn hàng Odoo)
INSERT INTO SAN_PHAM (MaSP, TenSP, NhomSP) VALUES
(1, 'Linen Bột Tự Nhiên Dệt Thưa', 'Vải Linen'),
(2, 'Lụa Tơ Tằm Thượng Hạng Bóng Nhẹ', 'Vải Lụa'),
(3, 'Dạ Wool Tweed Dệt Kẻ Cổ Điển', 'Vải Dạ Tweed'),
(4, 'Linen Bột Tự Nhiên', 'Vải Linen'),
(5, 'Lụa Satin Cao Cấp', 'Vải Lụa'),
(6, 'Cotton Hàn Gân Nổi', 'Vải Cotton'),
(7, 'Cotton Lụa Ai Cập Dệt Mật Độ Cao', 'Vải Cotton'),
(8, 'Nhung Tuyết Pháp Ánh Kim Cổ Điển', 'Vải Nhung'),
(9, 'Denim Selvedge Nhật Bản Nguyên Bản', 'Vải Denim'),
(10, 'Voan Tơ Chiffon Hàn Quốc Rủ Mềm', 'Vải Voan'),
(11, 'Gấm Hoa Văn Hoàng Gia Dệt Nổi', 'Vải Gấm'),
(12, 'Sợi Khuynh Diệp Tencel Sinh Học Mát Lạnh', 'Vải Tencel'),
(13, 'Organza Ánh Ngọc Trai Dựng Phom Váy Cưới', 'Vải Organza');

INSERT INTO BIEN_THE_SP (
    MaBienThe, MaSP, DonViTinh, MauSac, ChatLieuSize,
    MaSKU, KhoVai, DinhLuongGSM, MaMauHex, IconClass, BadgeMauClass, HinhAnhMacro, TrangThaiWeb
) VALUES
-- 10 mẫu vải hiển thị trong bảng CRUD (Mục 2: Quản lý Mẫu Vải Mới)
(1, 1, 'Mét', 'Be Mộc', 'Linen Bột Tự Nhiên • Khổ 1m50 • 210 gsm',
    'VAI-LINEN-01', 'Khổ 1m50', '210 gsm', '#e8dec8', 'fa-solid fa-rug', 'bg-amber-50 border-amber-200 text-amber-700', 'linen_macroscopic_ivory.jpg', 'Đang bán Web'),
(2, 2, 'Mét', 'Trắng Ngà', 'Lụa Tơ Tằm Thượng Hạng • Khổ 1m45 • 120 gsm',
    'VAI-SILK-08', 'Khổ 1m45', '120 gsm', '#f8fafc', 'fa-solid fa-feather', 'bg-purple-50 border-purple-200 text-purple-700', 'silk_mulberry_macro.jpg', 'Đang bán Web'),
(3, 3, 'Mét', 'Xám Than Dạ', 'Dạ Wool Tweed Cổ Điển • Khổ 1m55 • 380 gsm',
    'VAI-WOOL-03', 'Khổ 1m55', '380 gsm', '#334155', 'fa-solid fa-layer-group', 'bg-stone-100 border-stone-200 text-stone-700', 'wool_tweed_charcoal.jpg', 'Tạm Hết Hàng'),
-- 3 biến thể SKU khớp chính xác với 3 đơn hàng đầu tiên chờ đồng bộ Odoo
(4, 4, 'Mét', 'Be Mộc', 'Linen Bột Tự Nhiên • Khổ 1m50 • 210 gsm',
    'VAI-LN-01', 'Khổ 1m50', '210 gsm', '#e8dec8', 'fa-solid fa-rug', 'bg-amber-50 border-amber-200 text-amber-700', 'linen_ln01.jpg', 'Chỉ đồng bộ ERP'),
(5, 5, 'Mét', 'Trắng Ngọc Trai', 'Lụa Satin Cao Cấp • Khổ 1m45 • 140 gsm',
    'VAI-ST-04', 'Khổ 1m45', '140 gsm', '#f8fafc', 'fa-solid fa-feather', 'bg-purple-50 border-purple-200 text-purple-700', 'satin_st04.jpg', 'Chỉ đồng bộ ERP'),
(6, 6, 'Mét', 'Kem Sáng', 'Cotton Hàn Gân Nổi • Khổ 1m50 • 240 gsm',
    'VAI-CT-12', 'Khổ 1m50', '240 gsm', '#fef3c7', 'fa-solid fa-scroll', 'bg-emerald-50 border-emerald-200 text-emerald-700', 'cotton_ct12.jpg', 'Chỉ đồng bộ ERP'),
-- 7 mẫu vải bổ sung để kiểm thử thanh cuộn & quản lý kho
(7, 7, 'Mét', 'Xanh Navy', 'Cotton Lụa Ai Cập Dệt Mật Độ Cao • Khổ 1m60 • 165 gsm',
    'VAI-COTTON-04', 'Khổ 1m60', '165 gsm', '#1e3a8a', 'fa-solid fa-shirt', 'bg-blue-50 border-blue-200 text-blue-700', 'cotton_egypt_navy.jpg', 'Đang bán Web'),
(8, 8, 'Mét', 'Đỏ Rượu Vang', 'Nhung Tuyết Pháp Ánh Kim Cổ Điển • Khổ 1m50 • 340 gsm',
    'VAI-VELVET-05', 'Khổ 1m50', '340 gsm', '#881337', 'fa-solid fa-crown', 'bg-rose-50 border-rose-200 text-rose-700', 'velvet_burgundy.jpg', 'Đang bán Web'),
(9, 9, 'Mét', 'Chàm Đậm', 'Denim Selvedge Nhật Bản Nguyên Bản • Khổ 1m50 • 410 gsm',
    'VAI-DENIM-06', 'Khổ 1m50', '410 gsm', '#1e293b', 'fa-solid fa-layer-group', 'bg-slate-100 border-slate-300 text-slate-700', 'denim_selvedge_indigo.jpg', 'Đang bán Web'),
(10, 10, 'Mét', 'Hồng Phấn', 'Voan Tơ Chiffon Hàn Quốc Rủ Mềm • Khổ 1m48 • 95 gsm',
    'VAI-CHIFFON-07', 'Khổ 1m48', '95 gsm', '#fce7f3', 'fa-solid fa-wind', 'bg-pink-50 border-pink-200 text-pink-700', 'chiffon_pastel_pink.jpg', 'Đang bán Web'),
(11, 11, 'Mét', 'Vàng Đồng', 'Gấm Hoa Văn Hoàng Gia Dệt Nổi • Khổ 1m55 • 310 gsm',
    'VAI-BROCADE-09', 'Khổ 1m55', '310 gsm', '#d97706', 'fa-solid fa-gem', 'bg-amber-50 border-amber-200 text-amber-700', 'brocade_royal_gold.jpg', 'Đang bán Web'),
(12, 12, 'Mét', 'Xanh Bạc Hà', 'Sợi Khuynh Diệp Tencel Sinh Học Mát Lạnh • Khổ 1m60 • 180 gsm',
    'VAI-TENCEL-10', 'Khổ 1m60', '180 gsm', '#a7f3d0', 'fa-solid fa-leaf', 'bg-emerald-50 border-emerald-200 text-emerald-700', 'tencel_mint_eco.jpg', 'Đang bán Web'),
(13, 13, 'Mét', 'Kem Tuyết', 'Organza Ánh Ngọc Trai Dựng Phom Váy Cưới • Khổ 1m50 • 110 gsm',
    'VAI-ORGANZA-11', 'Khổ 1m50', '110 gsm', '#fffbeb', 'fa-solid fa-wand-magic-sparkles', 'bg-purple-50 border-purple-200 text-purple-700', 'organza_pearl_bridal.jpg', 'Tạm Hết Hàng');

-- 4. BẢNG GIÁ BÁN LẺ CHO CÁC MẪU VẢI
INSERT INTO BANG_GIA (MaBangGia, MaBienThe, LoaiKhach, DonGia) VALUES
(1, 1, 'Bán lẻ Web', 180000.00),
(2, 2, 'Bán lẻ Web', 320000.00),
(3, 3, 'Bán lẻ Web', 450000.00),
(4, 4, 'Bán lẻ Web', 180000.00),
(5, 5, 'Bán lẻ Web', 220000.00),
(6, 6, 'Bán lẻ Web', 145000.00),
(7, 7, 'Bán lẻ Web', 240000.00),
(8, 8, 'Bán lẻ Web', 390000.00),
(9, 9, 'Bán lẻ Web', 295000.00),
(10, 10, 'Bán lẻ Web', 155000.00),
(11, 11, 'Bán lẻ Web', 480000.00),
(12, 12, 'Bán lẻ Web', 265000.00),
(13, 13, 'Bán lẻ Web', 350000.00);

-- 5. CUỘN VẢI & TỒN KHO
INSERT INTO CUON_VAI (MaCuonVai, MaBienThe, DoDaiBanDau, DoDaiConLai) VALUES
(1, 1, 500.00, 420.50),
(2, 2, 100.00, 18.00),
(3, 3, 100.00, 0.00),
(4, 4, 300.00, 180.00),
(5, 5, 300.00, 150.00),
(6, 6, 200.00, 95.00),
(7, 7, 400.00, 315.00),
(8, 8, 150.00, 85.50),
(9, 9, 100.00, 14.50),
(10, 10, 300.00, 260.00),
(11, 11, 100.00, 12.00),
(12, 12, 250.00, 190.00),
(13, 13, 100.00, 0.00);

INSERT INTO TON_KHO (MaTonKho, MaCuonVai, SoLuongTon) VALUES
(1, 1, 420.50),
(2, 2, 18.00),
(3, 3, 0.00),
(4, 4, 180.00),
(5, 5, 150.00),
(6, 6, 95.00),
(7, 7, 315.00),
(8, 8, 85.50),
(9, 9, 14.50),
(10, 10, 260.00),
(11, 11, 12.00),
(12, 12, 190.00),
(13, 13, 0.00);

-- 6. KHÁCH HÀNG
INSERT INTO KHACH_HANG (MaKH, TenKH, SDT, LoaiKhach, TongNo) VALUES
(1, 'Võ Minh Quân', '0908111112', 'Khách VIP', 0.00),
(2, 'Lê Hoàng Nam', '0912111890', 'Đối tác Nhà may', 0.00),
(3, 'Nguyễn Thu Thảo', '0987111432', 'Khách lẻ', 0.00),
(4, 'Khách hàng ẩn danh', NULL, 'Khách vãng lai', 0.00),
(5, 'Trần Bích Phương', '0938555777', 'Khách thân thiết', 0.00),
(6, 'Đặng Ngọc Minh Châu', '0945222333', 'Đối tác Nhà may', 0.00),
(7, 'Phạm Quốc Bảo', '0977888999', 'Khách VIP', 0.00),
(8, 'Hoàng Mai Linh', '0903444555', 'Khách thân thiết', 0.00),
(9, 'Trịnh Gia Huy', '0918666777', 'Khách lẻ', 0.00);

-- 7. ĐƠN HÀNG (PHIEU_BAN & CT_PHIEU_BAN) - 7 đơn hàng
INSERT INTO PHIEU_BAN (MaPhieuBan, MaKH, NgayBan, TongTien, MaDonCode, ThoiGianBan, TrangThaiERP) VALUES
(1, 1, '2025-05-20', 2790000.00, '#ORD-9882', '10:38:12', 'Sẵn sàng xuất'),
(2, 2, '2025-05-20', 6600000.00, '#ORD-9881', '10:24:05', 'Sẵn sàng xuất'),
(3, 3, '2025-05-20', 1160000.00, '#ORD-9880', '10:11:40', 'Sẵn sàng xuất'),
(4, 6, '2025-05-20', 4800000.00, '#ORD-9879', '09:52:18', 'Sẵn sàng xuất'),
(5, 7, '2025-05-20', 5850000.00, '#ORD-9878', '09:30:44', 'Sẵn sàng xuất'),
(6, 8, '2025-05-19', 3100000.00, '#ORD-9877', '16:45:10', 'Sẵn sàng xuất'),
(7, 9, '2025-05-18', 4800000.00, '#ORD-9876', '14:15:00', 'Đã đồng bộ Odoo');

INSERT INTO CT_PHIEU_BAN (MaCTPhieuBan, MaPhieuBan, MaBienThe, SoLuongBan, DonGiaBan) VALUES
(1, 1, 4, 15.50, 180000.00),
(2, 2, 5, 30.00, 220000.00),
(3, 3, 6,  8.00, 145000.00),
(4, 4, 7, 20.00, 240000.00),
(5, 5, 8, 15.00, 390000.00),
(6, 6, 10, 20.00, 155000.00),
(7, 7, 11, 10.00, 480000.00);

-- 8. VOUCHER KHUYẾN MÃI - 6 mã Voucher đang kích hoạt
INSERT INTO VOUCHER (
    MaVoucher, GiaTriVoucher, HanSuDung,
    MaCode, TenChuongTrinh, MoTaDieuKien, LoaiGiam, GioiHanLuot, SoLuotDaDung, TrangThai
) VALUES
(1, 20.00, '2025-06-30',
    'FABRIC20', 'Giảm 20% cho đơn vải Linen', 'Hạn dùng: Đến 30/06/2025 • Áp dụng đơn từ 500k', '%', 100, 68, 1),
(2, 100000.00, NULL,
    'NEWSEASON', 'Giảm trực tiếp 100.000₫', 'Hạn dùng: Không giới hạn • Đơn khách mới', 'VNĐ', 500, 212, 1),
(3, 50000.00, '2025-08-15',
    'LINEN50K', 'Giảm 50.000₫ cho đơn từ 300k', 'Hạn dùng: Đến 15/08/2025 • Áp dụng dòng Linen & Cotton', 'VNĐ', 200, 145, 1),
(4, 30.00, '2025-09-30',
    'VIPTAILOR30', 'Chiết khấu 30% Đối tác Nhà may', 'Hạn dùng: Đến 30/09/2025 • Đơn sỉ từ 20 mét trở lên', '%', 50, 42, 1),
(5, 15.00, '2025-07-31',
    'SUMMER15', 'Ưu đãi Bộ sưu tập Hè 15%', 'Hạn dùng: Đến 31/07/2025 • Áp dụng Tencel & Voan Chiffon', '%', 300, 189, 1),
(6, 150000.00, '2025-12-31',
    'BRIDAL150K', 'Giảm 150.000₫ dòng Vải Cưới & Gấm', 'Hạn dùng: Đến 31/12/2025 • Đơn từ 1.500.000₫', 'VNĐ', 100, 28, 1);

-- 9. ĐÁNH GIÁ KHÁCH HÀNG - 9 đánh giá (3 Spam + 6 đánh giá mua hàng thật)
INSERT INTO DANH_GIA_KHACH_HANG (
    MaDanhGia, MaKH, SoSao, NoiDung, NgayDanhGia,
    MaBienThe, IPKhach, LoaiDanhGia, TrangThaiDuyet, IsGhim, SoAnhDinhKem
) VALUES
(1, 4, 1, 'Vải dở ẹc, mọi người qua trang bit.ly/mua-vai-re này xem hàng chuẩn hơn gấp trăm lần nha...', '2025-05-20',
    2, '118.69.•••', 'Spam Link / Thô tục', 'Vi phạm', 0, 0),
(2, 5, 5, 'Chất vải linen rất đanh và không bị xù sau khi giặt thử. Màu sắc ngoài đời be mộc đúng chuẩn ảnh chụp.', '2025-05-20',
    1, '14.161.22.89', 'Đã mua hàng thật', 'Chờ duyệt', 0, 2),
(3, 6, 5, 'Mình đặt 20m Cotton Lụa Ai Cập về may sơ mi cho khách, mình vải mịn mát, độ bóng tự nhiên cực kỳ sang.', '2025-05-20',
    7, '113.161.44.12', 'Đã mua hàng thật', 'Đã duyệt', 1, 3),
(4, 4, 1, 'Nhận làm bằng giả, chạy quảng cáo giá rẻ liên hệ zalo 0999xxx ngay nhé http://spam-ads-link.xyz', '2025-05-20',
    8, '171.244.•••', 'Spam Link / Thô tục', 'Vi phạm', 0, 0),
(5, 7, 5, 'Nhung tuyết Pháp lên đầm dạ hội đứng phom xuất sắc, tuyết nhung mịn đều, cắt không bị rụng lông.', '2025-05-19',
    8, '27.72.88.101', 'Đã mua hàng thật', 'Chờ duyệt', 0, 2),
(6, 8, 4, 'Voan Chiffon Hàn màu hồng phấn ngọt nhẹ, vải mềm rủ, đóng gói cuộn cây rất kỹ không bị gãy nếp.', '2025-05-19',
    10, '14.232.19.55', 'Đã mua hàng thật', 'Chờ duyệt', 0, 1),
(7, 4, 2, 'Shop làm ăn chán quá, bấm vào link t.me/nhom_vai_lau_gia_si để lấy sỉ rẻ bằng nửa giá shop!', '2025-05-18',
    9, '42.112.•••', 'Spam Link / Thô tục', 'Vi phạm', 0, 0),
(8, 9, 5, 'Vải Tencel sinh học sờ mát lạnh tay, may bộ chăn ga khách khen tấm tắc. Sẽ ủng hộ kho dài lâu!', '2025-05-18',
    12, '115.78.12.90', 'Đã mua hàng thật', 'Đã duyệt', 0, 2),
(9, 2, 5, 'Gấm hoa văn dệt nổi sắc nét từng chi tiết, lên áo dài truyền thống cực kỳ tôn dáng.', '2025-05-17',
    11, '125.235.4.18', 'Đã mua hàng thật', 'Đã duyệt', 0, 0);
