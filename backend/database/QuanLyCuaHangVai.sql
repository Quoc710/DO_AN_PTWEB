/* =====================================================================
   HỆ THỐNG QUẢN LÝ CỬA HÀNG VẢI - SQL Server (T-SQL)
   Sinh từ sơ đồ ER: 26 bảng thực thể + 4 bảng nối (quan hệ n-n) = 30 bảng
   Cách chạy: mở trong SSMS -> Execute (F5). Chạy một lần trên server trống.
   ===================================================================== */

IF DB_ID(N'QuanLyCuaHangVai') IS NULL
    CREATE DATABASE QuanLyCuaHangVai;
GO
USE QuanLyCuaHangVai;
GO

/* ---------------------------------------------------------------------
   1. NHÓM NHẬP HÀNG
   --------------------------------------------------------------------- */
CREATE TABLE NHA_CUNG_CAP (
    MaNCC       INT IDENTITY(1,1) CONSTRAINT PK_NHA_CUNG_CAP PRIMARY KEY,
    TenNCC      NVARCHAR(150) NOT NULL,
    PhanLoai    NVARCHAR(50)  NULL,
    SDT         VARCHAR(15)   NULL,
    DiaChi      NVARCHAR(255) NULL
);
GO

CREATE TABLE PHIEU_NHAP (
    MaPhieuNhap   INT IDENTITY(1,1) CONSTRAINT PK_PHIEU_NHAP PRIMARY KEY,
    MaNCC         INT NOT NULL,                          -- CUNGCAP (1,1)
    NgayNhap      DATE NOT NULL CONSTRAINT DF_PHIEU_NHAP_NgayNhap DEFAULT (CONVERT(date, GETDATE())),
    HanThanhToan  DATE NULL,
    TongTien      DECIMAL(18,2) NOT NULL CONSTRAINT DF_PHIEU_NHAP_TongTien DEFAULT (0),
    TrangThai     NVARCHAR(30) NULL,
    CONSTRAINT FK_PHIEU_NHAP_NCC FOREIGN KEY (MaNCC) REFERENCES NHA_CUNG_CAP(MaNCC),
    CONSTRAINT CK_PHIEU_NHAP_TongTien CHECK (TongTien >= 0),
    CONSTRAINT CK_PHIEU_NHAP_Han CHECK (HanThanhToan IS NULL OR HanThanhToan >= NgayNhap)
);
GO

/* ---------------------------------------------------------------------
   2. NHÓM SẢN PHẨM
   --------------------------------------------------------------------- */
CREATE TABLE SAN_PHAM (
    MaSP     INT IDENTITY(1,1) CONSTRAINT PK_SAN_PHAM PRIMARY KEY,
    TenSP    NVARCHAR(150) NOT NULL,
    NhomSP   NVARCHAR(100) NULL
);
GO

CREATE TABLE BIEN_THE_SP (
    MaBienThe     INT IDENTITY(1,1) CONSTRAINT PK_BIEN_THE_SP PRIMARY KEY,
    MaSP          INT NOT NULL,                          -- BAOGOM
    DonViTinh     NVARCHAR(20) NOT NULL,
    MauSac        NVARCHAR(50) NULL,
    ChatLieuSize  NVARCHAR(100) NULL,                    -- thuộc tính "ChatLieu/Size"
    CONSTRAINT FK_BIEN_THE_SP_SP FOREIGN KEY (MaSP) REFERENCES SAN_PHAM(MaSP)
);
GO

CREATE TABLE CT_PHIEU_NHAP (
    MaCTPhieuNhap  INT IDENTITY(1,1) CONSTRAINT PK_CT_PHIEU_NHAP PRIMARY KEY,
    MaPhieuNhap    INT NOT NULL,                         -- GOM
    MaBienThe      INT NOT NULL,                         -- THAMCHIEU
    SoLuong        DECIMAL(18,2) NOT NULL,
    DonGiaNhap     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_PHIEU_NHAP_PN FOREIGN KEY (MaPhieuNhap) REFERENCES PHIEU_NHAP(MaPhieuNhap) ON DELETE CASCADE,
    CONSTRAINT FK_CT_PHIEU_NHAP_BT FOREIGN KEY (MaBienThe)   REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CT_PHIEU_NHAP_SL  CHECK (SoLuong > 0),
    CONSTRAINT CK_CT_PHIEU_NHAP_Gia CHECK (DonGiaNhap >= 0)
);
GO

CREATE TABLE CUON_VAI (
    MaCuonVai     INT IDENTITY(1,1) CONSTRAINT PK_CUON_VAI PRIMARY KEY,
    MaBienThe     INT NOT NULL,                          -- THAMCHIEU
    DoDaiBanDau   DECIMAL(10,2) NOT NULL,
    DoDaiConLai   DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_CUON_VAI_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CUON_VAI_DoDai CHECK (DoDaiBanDau > 0 AND DoDaiConLai >= 0 AND DoDaiConLai <= DoDaiBanDau)
);
GO

/* ---------------------------------------------------------------------
   3. NHÓM GIÁ
   --------------------------------------------------------------------- */
CREATE TABLE BANG_GIA (
    MaBangGia  INT IDENTITY(1,1) CONSTRAINT PK_BANG_GIA PRIMARY KEY,
    MaBienThe  INT NOT NULL,                             -- DINHGIA
    LoaiKhach  NVARCHAR(30) NOT NULL,
    DonGia     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_BANG_GIA_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_BANG_GIA_DonGia CHECK (DonGia >= 0),
    CONSTRAINT UQ_BANG_GIA_BT_Loai UNIQUE (MaBienThe, LoaiKhach)
);
GO

CREATE TABLE YEU_CAU_DUYET_GIA (
    MaYeuCau        INT IDENTITY(1,1) CONSTRAINT PK_YEU_CAU_DUYET_GIA PRIMARY KEY,
    TrangThaiDuyet  NVARCHAR(30) NOT NULL CONSTRAINT DF_YEU_CAU_TrangThai DEFAULT (N'Chờ duyệt')
);
GO

CREATE TABLE LICH_SU_GIA (
    MaLSGia    INT IDENTITY(1,1) CONSTRAINT PK_LICH_SU_GIA PRIMARY KEY,
    MaBangGia  INT NOT NULL,                             -- LUUGIA
    MaYeuCau   INT NULL,                                 -- DUYET (0,1)-(0,1)
    GiaCu      DECIMAL(18,2) NULL,
    GiaMoi     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_LICH_SU_GIA_BG FOREIGN KEY (MaBangGia) REFERENCES BANG_GIA(MaBangGia) ON DELETE CASCADE,
    CONSTRAINT FK_LICH_SU_GIA_YC FOREIGN KEY (MaYeuCau)  REFERENCES YEU_CAU_DUYET_GIA(MaYeuCau),
    CONSTRAINT CK_LICH_SU_GIA_Gia CHECK ((GiaCu IS NULL OR GiaCu >= 0) AND GiaMoi >= 0)
);
GO
-- Quan hệ DUYET là 1-1 tùy chọn: một yêu cầu chỉ gắn với tối đa một dòng lịch sử giá
CREATE UNIQUE INDEX UX_LICH_SU_GIA_MaYeuCau ON LICH_SU_GIA(MaYeuCau) WHERE MaYeuCau IS NOT NULL;
GO

/* ---------------------------------------------------------------------
   4. NHÓM NHÂN SỰ - TÀI KHOẢN - PHÂN QUYỀN
   --------------------------------------------------------------------- */
CREATE TABLE NHAN_VIEN (
    MaNV    INT IDENTITY(1,1) CONSTRAINT PK_NHAN_VIEN PRIMARY KEY,
    TenNV   NVARCHAR(100) NOT NULL,
    ViTri   NVARCHAR(50)  NULL
);
GO

CREATE TABLE VAI_TRO (
    MaVaiTro    INT IDENTITY(1,1) CONSTRAINT PK_VAI_TRO PRIMARY KEY,
    TenVaiTro   NVARCHAR(50) NOT NULL CONSTRAINT UQ_VAI_TRO_Ten UNIQUE,   -- cột thêm, ER chưa có
    HanMucGiam  DECIMAL(5,2) NOT NULL CONSTRAINT DF_VAI_TRO_HanMucGiam DEFAULT (0),
    CONSTRAINT CK_VAI_TRO_HanMucGiam CHECK (HanMucGiam BETWEEN 0 AND 100)  -- % giảm giá tối đa
);
GO

CREATE TABLE QUYEN (
    MaQuyen   INT IDENTITY(1,1) CONSTRAINT PK_QUYEN PRIMARY KEY,
    TenQuyen  NVARCHAR(100) NOT NULL CONSTRAINT UQ_QUYEN_Ten UNIQUE
);
GO

CREATE TABLE TAI_KHOAN (
    MaTK           INT IDENTITY(1,1) CONSTRAINT PK_TAI_KHOAN PRIMARY KEY,
    MaNV           INT NOT NULL CONSTRAINT UQ_TAI_KHOAN_NV UNIQUE,         -- DANGNHAP 1-1
    TenTK          VARCHAR(50) NOT NULL CONSTRAINT UQ_TAI_KHOAN_Ten UNIQUE,
    MatKhauHash    VARCHAR(255) NOT NULL,                                  -- cột thêm, lưu mật khẩu đã băm
    Email          VARCHAR(150) NULL,
    TrangThai      NVARCHAR(30) NOT NULL CONSTRAINT DF_TAI_KHOAN_TrangThai DEFAULT (N'Chưa kích hoạt'),
    TokenKichHoat  VARCHAR(255) NULL,
    HanKichHoat    DATETIME NULL,
    CONSTRAINT FK_TAI_KHOAN_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
);
GO

-- GANVAITRO: Tài khoản (1,n) - (1,n) Vai trò
CREATE TABLE TAIKHOAN_VAITRO (
    MaTK      INT NOT NULL,
    MaVaiTro  INT NOT NULL,
    CONSTRAINT PK_TAIKHOAN_VAITRO PRIMARY KEY (MaTK, MaVaiTro),
    CONSTRAINT FK_TKVT_TK FOREIGN KEY (MaTK)     REFERENCES TAI_KHOAN(MaTK)  ON DELETE CASCADE,
    CONSTRAINT FK_TKVT_VT FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro) ON DELETE CASCADE
);
GO

-- CAPQUYEN: Vai trò - Quyền (dùng bảng nối theo mô hình phân quyền chuẩn)
CREATE TABLE VAITRO_QUYEN (
    MaVaiTro  INT NOT NULL,
    MaQuyen   INT NOT NULL,
    CONSTRAINT PK_VAITRO_QUYEN PRIMARY KEY (MaVaiTro, MaQuyen),
    CONSTRAINT FK_VTQ_VT FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro) ON DELETE CASCADE,
    CONSTRAINT FK_VTQ_Q  FOREIGN KEY (MaQuyen)  REFERENCES QUYEN(MaQuyen)    ON DELETE CASCADE
);
GO

CREATE TABLE VI_HOA_HONG (
    MaHoaHong       INT IDENTITY(1,1) CONSTRAINT PK_VI_HOA_HONG PRIMARY KEY,
    MaNV            INT NOT NULL CONSTRAINT UQ_VI_HOA_HONG_NV UNIQUE,    -- CO 1-1
    SoTienHoaHong   DECIMAL(18,2) NOT NULL CONSTRAINT DF_VI_HOA_HONG_SoTien DEFAULT (0),
    TrangThai       NVARCHAR(30) NULL,
    CONSTRAINT FK_VI_HOA_HONG_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV),
    CONSTRAINT CK_VI_HOA_HONG_SoTien CHECK (SoTienHoaHong >= 0)
);
GO

CREATE TABLE LOOKBOOK (
    MaLookBook   INT IDENTITY(1,1) CONSTRAINT PK_LOOKBOOK PRIMARY KEY,
    TenLookBook  NVARCHAR(150) NOT NULL,
    URLHinhAnh   VARCHAR(500) NULL
);
GO

-- LUU: Nhân viên (0,n) - (1,n) Lookbook
CREATE TABLE NHANVIEN_LOOKBOOK (
    MaNV        INT NOT NULL,
    MaLookBook  INT NOT NULL,
    CONSTRAINT PK_NHANVIEN_LOOKBOOK PRIMARY KEY (MaNV, MaLookBook),
    CONSTRAINT FK_NVLB_NV FOREIGN KEY (MaNV)       REFERENCES NHAN_VIEN(MaNV)    ON DELETE CASCADE,
    CONSTRAINT FK_NVLB_LB FOREIGN KEY (MaLookBook) REFERENCES LOOKBOOK(MaLookBook) ON DELETE CASCADE
);
GO

/* ---------------------------------------------------------------------
   5. NHÓM KIỂM KÊ - TỒN KHO
   --------------------------------------------------------------------- */
CREATE TABLE TON_KHO (
    MaTonKho     INT IDENTITY(1,1) CONSTRAINT PK_TON_KHO PRIMARY KEY,
    MaCuonVai    INT NOT NULL,                           -- THAMCHIEU
    SoLuongTon   DECIMAL(18,2) NOT NULL CONSTRAINT DF_TON_KHO_SoLuong DEFAULT (0),
    CONSTRAINT FK_TON_KHO_CV FOREIGN KEY (MaCuonVai) REFERENCES CUON_VAI(MaCuonVai),
    CONSTRAINT CK_TON_KHO_SoLuong CHECK (SoLuongTon >= 0)
);
GO

CREATE TABLE PHIEU_KIEM_KE (
    MaPhieuKK  INT IDENTITY(1,1) CONSTRAINT PK_PHIEU_KIEM_KE PRIMARY KEY,
    MaNV       INT NOT NULL,                             -- LAP
    NgayKK     DATE NOT NULL CONSTRAINT DF_PHIEU_KIEM_KE_Ngay DEFAULT (CONVERT(date, GETDATE())),
    TrangThai  NVARCHAR(30) NULL,
    CONSTRAINT FK_PHIEU_KIEM_KE_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
);
GO

CREATE TABLE CT_KIEM_KE (
    MaCTKiemKe   INT IDENTITY(1,1) CONSTRAINT PK_CT_KIEM_KE PRIMARY KEY,   -- cột thêm làm khóa chính
    MaPhieuKK    INT NOT NULL,                           -- GOM
    MaTonKho     INT NOT NULL,                           -- DOICHIEU
    TonLyThuyet  DECIMAL(18,2) NOT NULL,
    TonThucTe    DECIMAL(18,2) NOT NULL,
    ChenhLech    AS (TonThucTe - TonLyThuyet) PERSISTED, -- tự tính
    CONSTRAINT FK_CT_KIEM_KE_PKK FOREIGN KEY (MaPhieuKK) REFERENCES PHIEU_KIEM_KE(MaPhieuKK) ON DELETE CASCADE,
    CONSTRAINT FK_CT_KIEM_KE_TK  FOREIGN KEY (MaTonKho)  REFERENCES TON_KHO(MaTonKho),
    CONSTRAINT UQ_CT_KIEM_KE UNIQUE (MaPhieuKK, MaTonKho),
    CONSTRAINT CK_CT_KIEM_KE_Ton CHECK (TonLyThuyet >= 0 AND TonThucTe >= 0)
);
GO

/* ---------------------------------------------------------------------
   6. NHÓM KHÁCH HÀNG
   --------------------------------------------------------------------- */
CREATE TABLE KHACH_HANG (
    MaKH       INT IDENTITY(1,1) CONSTRAINT PK_KHACH_HANG PRIMARY KEY,
    TenKH      NVARCHAR(100) NOT NULL,
    SDT        VARCHAR(15) NULL,
    LoaiKhach  NVARCHAR(30) NULL,
    TongNo     DECIMAL(18,2) NOT NULL CONSTRAINT DF_KHACH_HANG_TongNo DEFAULT (0),
    CONSTRAINT CK_KHACH_HANG_TongNo CHECK (TongNo >= 0)
);
GO

CREATE TABLE DANH_GIA_KHACH_HANG (
    MaDanhGia    INT IDENTITY(1,1) CONSTRAINT PK_DANH_GIA_KHACH_HANG PRIMARY KEY,
    MaKH         INT NOT NULL,                           -- CO (1,1)
    SoSao        TINYINT NOT NULL,
    NoiDung      NVARCHAR(1000) NULL,
    NgayDanhGia  DATE NOT NULL CONSTRAINT DF_DANH_GIA_Ngay DEFAULT (CONVERT(date, GETDATE())),
    CONSTRAINT FK_DANH_GIA_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT CK_DANH_GIA_SoSao CHECK (SoSao BETWEEN 1 AND 5)
);
GO

CREATE TABLE PHIEU_THU_NO (
    MaPhieuThu    INT IDENTITY(1,1) CONSTRAINT PK_PHIEU_THU_NO PRIMARY KEY,
    MaKH          INT NOT NULL,                          -- THUNO (1,1)
    HinhThucThu   NVARCHAR(50) NULL,
    SoTienThu     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_PHIEU_THU_NO_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT CK_PHIEU_THU_NO_SoTien CHECK (SoTienThu > 0)
);
GO

/* ---------------------------------------------------------------------
   7. NHÓM BÁN HÀNG - VOUCHER - TRẢ HÀNG
   --------------------------------------------------------------------- */
CREATE TABLE VOUCHER (
    MaVoucher     INT IDENTITY(1,1) CONSTRAINT PK_VOUCHER PRIMARY KEY,
    GiaTriVoucher DECIMAL(18,2) NOT NULL,
    HanSuDung     DATE NULL,
    CONSTRAINT CK_VOUCHER_GiaTri CHECK (GiaTriVoucher > 0)
);
GO

CREATE TABLE PHIEU_BAN (
    MaPhieuBan  INT IDENTITY(1,1) CONSTRAINT PK_PHIEU_BAN PRIMARY KEY,
    MaKH        INT NOT NULL,                            -- MUA (1,1)
    NgayBan     DATE NOT NULL CONSTRAINT DF_PHIEU_BAN_Ngay DEFAULT (CONVERT(date, GETDATE())),
    TongTien    DECIMAL(18,2) NOT NULL CONSTRAINT DF_PHIEU_BAN_TongTien DEFAULT (0),
    TrangThai   NVARCHAR(50) NULL CONSTRAINT DF_PHIEU_BAN_TrangThai DEFAULT (N'Chờ xác nhận'), -- 'Chờ xác nhận', 'Đang giao', 'Đã giao', 'Đã hủy'
    CONSTRAINT FK_PHIEU_BAN_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT CK_PHIEU_BAN_TongTien CHECK (TongTien >= 0)
);
GO

CREATE TABLE CT_PHIEU_BAN (
    MaCTPhieuBan  INT IDENTITY(1,1) CONSTRAINT PK_CT_PHIEU_BAN PRIMARY KEY,
    MaPhieuBan    INT NOT NULL,                          -- GOM
    MaBienThe     INT NOT NULL,                          -- THAMCHIEU
    SoLuongBan    DECIMAL(18,2) NOT NULL,
    DonGiaBan     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_PHIEU_BAN_PB FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_CT_PHIEU_BAN_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CT_PHIEU_BAN_SL  CHECK (SoLuongBan > 0),
    CONSTRAINT CK_CT_PHIEU_BAN_Gia CHECK (DonGiaBan >= 0)
);
GO

-- APDUNG: Phiếu bán (1,n) - (1,n) Voucher
CREATE TABLE PHIEU_BAN_VOUCHER (
    MaPhieuBan  INT NOT NULL,
    MaVoucher   INT NOT NULL,
    CONSTRAINT PK_PHIEU_BAN_VOUCHER PRIMARY KEY (MaPhieuBan, MaVoucher),
    CONSTRAINT FK_PBV_PB FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_PBV_V  FOREIGN KEY (MaVoucher)  REFERENCES VOUCHER(MaVoucher)    ON DELETE CASCADE
);
GO

CREATE TABLE PHIEU_TRA_HANG (
    MaPhieuTra       INT IDENTITY(1,1) CONSTRAINT PK_PHIEU_TRA_HANG PRIMARY KEY,
    MaPhieuBanGoc    INT NOT NULL,                       -- phiếu bán gốc
    LyDoTra          NVARCHAR(255) NULL,
    CONSTRAINT FK_PHIEU_TRA_HANG_PB FOREIGN KEY (MaPhieuBanGoc) REFERENCES PHIEU_BAN(MaPhieuBan)
);
GO

CREATE TABLE CT_TRA_HANG (
    MaCTTraHang     INT IDENTITY(1,1) CONSTRAINT PK_CT_TRA_HANG PRIMARY KEY,
    MaPhieuTra      INT NOT NULL,                        -- CHITIET
    MaBienThe       INT NOT NULL,                        -- THAMCHIEU
    SoLuongTra      DECIMAL(18,2) NOT NULL,
    TinhTrangHang   NVARCHAR(100) NULL,
    NhapLaiKho      BIT NOT NULL CONSTRAINT DF_CT_TRA_HANG_NhapLai DEFAULT (0),
    CONSTRAINT FK_CT_TRA_HANG_PT FOREIGN KEY (MaPhieuTra) REFERENCES PHIEU_TRA_HANG(MaPhieuTra) ON DELETE CASCADE,
    CONSTRAINT FK_CT_TRA_HANG_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT CK_CT_TRA_HANG_SL CHECK (SoLuongTra > 0)
);
GO

/* ---------------------------------------------------------------------
   Kiểm tra nhanh: phải ra 30 bảng
   --------------------------------------------------------------------- */
SELECT COUNT(*) AS SoBang FROM sys.tables;
GO


-- Cột ChenhLech là cột tự tính bằng thực tế trừ lý thuyết. Ngoài ra mình có thêm ràng buộc CHECK (số lượng dương, số sao 1–5, độ dài còn lại không vượt ban đầu) và ON DELETE CASCADE cho bảng chi tiết và bảng nối.