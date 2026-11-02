-- =====================================================================
-- HỆ THỐNG QUẢN LÝ CỬA HÀNG VẢI - MySQL (Chạy chuẩn 100% trên XAMPP)
-- Sinh từ sơ đồ ER: 30 bảng chuẩn + bổ sung bảng nghiệp vụ Lookbook & Affiliate
-- =====================================================================

CREATE DATABASE IF NOT EXISTS QuanLyCuaHangVai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QuanLyCuaHangVai;

-- 1. NHÓM NHẬP HÀNG
CREATE TABLE IF NOT EXISTS NHA_CUNG_CAP (
    MaNCC       INT AUTO_INCREMENT PRIMARY KEY,
    TenNCC      VARCHAR(150) NOT NULL,
    PhanLoai    VARCHAR(50)  NULL,
    SDT         VARCHAR(15)   NULL,
    DiaChi      VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS PHIEU_NHAP (
    MaPhieuNhap   INT AUTO_INCREMENT PRIMARY KEY,
    MaNCC         INT NOT NULL,
    NgayNhap      DATE NOT NULL DEFAULT (CURRENT_DATE),
    HanThanhToan  DATE NULL,
    TongTien      DECIMAL(18,2) NOT NULL DEFAULT 0,
    TrangThai     VARCHAR(30) NULL,
    CONSTRAINT FK_PHIEU_NHAP_NCC FOREIGN KEY (MaNCC) REFERENCES NHA_CUNG_CAP(MaNCC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. NHÓM SẢN PHẨM & BIẾN THỂ
CREATE TABLE IF NOT EXISTS SAN_PHAM (
    MaSP     INT AUTO_INCREMENT PRIMARY KEY,
    TenSP    VARCHAR(150) NOT NULL,
    NhomSP   VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS BIEN_THE_SP (
    MaBienThe     INT AUTO_INCREMENT PRIMARY KEY,
    MaSP          INT NOT NULL,
    DonViTinh     VARCHAR(20) NOT NULL,
    MauSac        VARCHAR(50) NULL,
    ChatLieuSize  VARCHAR(100) NULL,
    MaVaiCode     VARCHAR(50) NULL,
    HinhAnh       VARCHAR(500) NULL,
    CONSTRAINT FK_BIEN_THE_SP_SP FOREIGN KEY (MaSP) REFERENCES SAN_PHAM(MaSP)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CT_PHIEU_NHAP (
    MaCTPhieuNhap  INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuNhap    INT NOT NULL,
    MaBienThe      INT NOT NULL,
    SoLuong        DECIMAL(18,2) NOT NULL,
    DonGiaNhap     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_PHIEU_NHAP_PN FOREIGN KEY (MaPhieuNhap) REFERENCES PHIEU_NHAP(MaPhieuNhap) ON DELETE CASCADE,
    CONSTRAINT FK_CT_PHIEU_NHAP_BT FOREIGN KEY (MaBienThe)   REFERENCES BIEN_THE_SP(MaBienThe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CUON_VAI (
    MaCuonVai     INT AUTO_INCREMENT PRIMARY KEY,
    MaBienThe     INT NOT NULL,
    DoDaiBanDau   DECIMAL(10,2) NOT NULL,
    DoDaiConLai   DECIMAL(10,2) NOT NULL,
    CONSTRAINT FK_CUON_VAI_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. NHÓM GIÁ
CREATE TABLE IF NOT EXISTS BANG_GIA (
    MaBangGia  INT AUTO_INCREMENT PRIMARY KEY,
    MaBienThe  INT NOT NULL,
    LoaiKhach  VARCHAR(30) NOT NULL,
    DonGia     DECIMAL(18,2) NOT NULL,
    TyLeHoaHong DECIMAL(5,2) DEFAULT 5.00,
    CONSTRAINT FK_BANG_GIA_BT FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe),
    CONSTRAINT UQ_BANG_GIA_BT_Loai UNIQUE (MaBienThe, LoaiKhach)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS YEU_CAU_DUYET_GIA (
    MaYeuCau        INT AUTO_INCREMENT PRIMARY KEY,
    TrangThaiDuyet  VARCHAR(30) NOT NULL DEFAULT 'Chờ duyệt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS LICH_SU_GIA (
    MaLSGia    INT AUTO_INCREMENT PRIMARY KEY,
    MaBangGia  INT NOT NULL,
    MaYeuCau   INT NULL,
    GiaCu      DECIMAL(18,2) NULL,
    GiaMoi     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_LICH_SU_GIA_BG FOREIGN KEY (MaBangGia) REFERENCES BANG_GIA(MaBangGia) ON DELETE CASCADE,
    CONSTRAINT FK_LICH_SU_GIA_YC FOREIGN KEY (MaYeuCau)  REFERENCES YEU_CAU_DUYET_GIA(MaYeuCau)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. NHÓM NHÂN SỰ - THỢ MAY - PHÂN QUYỀN
CREATE TABLE IF NOT EXISTS NHAN_VIEN (
    MaNV        INT AUTO_INCREMENT PRIMARY KEY,
    TenNV       VARCHAR(100) NOT NULL,
    ViTri       VARCHAR(50)  NULL,
    SDT         VARCHAR(15)  NULL,
    Email       VARCHAR(100) NULL,
    SoTaiKhoan  VARCHAR(50)  NULL,
    NganHang    VARCHAR(100) NULL,
    TenTiemMay  VARCHAR(150) NULL,
    CapBac      VARCHAR(50)  DEFAULT 'Bạch Kim',
    AffiliateCode VARCHAR(50) UNIQUE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS VAI_TRO (
    MaVaiTro    INT AUTO_INCREMENT PRIMARY KEY,
    TenVaiTro   VARCHAR(50) NOT NULL UNIQUE,
    HanMucGiam  DECIMAL(5,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS QUYEN (
    MaQuyen   INT AUTO_INCREMENT PRIMARY KEY,
    TenQuyen  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS TAI_KHOAN (
    MaTK         INT AUTO_INCREMENT PRIMARY KEY,
    MaNV         INT NOT NULL UNIQUE,
    TenTK        VARCHAR(50) NOT NULL UNIQUE,
    MatKhauHash  VARCHAR(255) NOT NULL,
    CONSTRAINT FK_TAI_KHOAN_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS TAIKHOAN_VAITRO (
    MaTK      INT NOT NULL,
    MaVaiTro  INT NOT NULL,
    PRIMARY KEY (MaTK, MaVaiTro),
    CONSTRAINT FK_TKVT_TK FOREIGN KEY (MaTK)     REFERENCES TAI_KHOAN(MaTK)  ON DELETE CASCADE,
    CONSTRAINT FK_TKVT_VT FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS VAITRO_QUYEN (
    MaVaiTro  INT NOT NULL,
    MaQuyen   INT NOT NULL,
    PRIMARY KEY (MaVaiTro, MaQuyen),
    CONSTRAINT FK_VTQ_VT FOREIGN KEY (MaVaiTro) REFERENCES VAI_TRO(MaVaiTro) ON DELETE CASCADE,
    CONSTRAINT FK_VTQ_Q  FOREIGN KEY (MaQuyen)  REFERENCES QUYEN(MaQuyen)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS VI_HOA_HONG (
    MaHoaHong       INT AUTO_INCREMENT PRIMARY KEY,
    MaNV            INT NOT NULL UNIQUE,
    SoTienHoaHong   DECIMAL(18,2) NOT NULL DEFAULT 0,
    TongTichLuy     DECIMAL(18,2) NOT NULL DEFAULT 0,
    TrangThai       VARCHAR(30) DEFAULT 'Hoat_dong',
    CONSTRAINT FK_VI_HOA_HONG_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. NHÓM LOOKBOOK (ĐÃ TỐI ƯU CHO TASK LOOKBOOK & AFFILIATE)
CREATE TABLE IF NOT EXISTS LOOKBOOK (
    MaLookBook   INT AUTO_INCREMENT PRIMARY KEY,
    TenLookBook  VARCHAR(150) NOT NULL,
    URLHinhAnh   VARCHAR(500) NULL,
    DanhMuc      VARCHAR(100) DEFAULT 'Đầm & Váy',
    MaBienThe    INT NULL,
    DinhLuongVai VARCHAR(50) NULL,
    MoTa         TEXT NULL,
    TrangThaiDuyet VARCHAR(30) DEFAULT 'Chờ duyệt',
    NgayDang     DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_LOOKBOOK_BIENTHE FOREIGN KEY (MaBienThe) REFERENCES BIEN_THE_SP(MaBienThe) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS NHANVIEN_LOOKBOOK (
    MaNV        INT NOT NULL,
    MaLookBook  INT NOT NULL,
    PRIMARY KEY (MaNV, MaLookBook),
    CONSTRAINT FK_NVLB_NV FOREIGN KEY (MaNV)       REFERENCES NHAN_VIEN(MaNV)    ON DELETE CASCADE,
    CONSTRAINT FK_NVLB_LB FOREIGN KEY (MaLookBook) REFERENCES LOOKBOOK(MaLookBook) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng lệnh rút tiền của Thợ may
CREATE TABLE IF NOT EXISTS YEU_CAU_RUT_TIEN (
    MaYeuCau        INT AUTO_INCREMENT PRIMARY KEY,
    MaNV            INT NOT NULL,
    SoTien          DECIMAL(18,2) NOT NULL,
    HinhThuc        VARCHAR(50) NOT NULL, -- 'NganHang' hoặc 'MuaVaiAura'
    ThongTinNhan    VARCHAR(255) NULL,
    TrangThai       VARCHAR(30) DEFAULT 'Chờ duyệt', -- 'Chờ duyệt', 'Đã chuyển khoản', 'Từ chối'
    NgayTao         DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_RUTTIEN_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bảng lịch sử hoa hồng Affiliate
CREATE TABLE IF NOT EXISTS LICH_SU_HOA_HONG (
    MaLS            INT AUTO_INCREMENT PRIMARY KEY,
    MaNV            INT NOT NULL,
    MaDonHangCode   VARCHAR(50) NULL,
    LoaiGiaoDich    VARCHAR(100) NOT NULL,
    SoTien          DECIMAL(18,2) NOT NULL,
    TrangThai       VARCHAR(30) DEFAULT 'Thành công',
    NgayTao         DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_LSHH_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. NHÓM TỒN KHO - KIỂM KÊ
CREATE TABLE IF NOT EXISTS TON_KHO (
    MaTonKho     INT AUTO_INCREMENT PRIMARY KEY,
    MaCuonVai    INT NOT NULL,
    SoLuongTon   DECIMAL(18,2) NOT NULL DEFAULT 0,
    CONSTRAINT FK_TON_KHO_CV FOREIGN KEY (MaCuonVai) REFERENCES CUON_VAI(MaCuonVai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS PHIEU_KIEM_KE (
    MaPhieuKK  INT AUTO_INCREMENT PRIMARY KEY,
    MaNV       INT NOT NULL,
    NgayKK     DATE NOT NULL DEFAULT (CURRENT_DATE),
    TrangThai  VARCHAR(30) NULL,
    CONSTRAINT FK_PHIEU_KIEM_KE_NV FOREIGN KEY (MaNV) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CT_KIEM_KE (
    MaCTKiemKe   INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuKK    INT NOT NULL,
    MaTonKho     INT NOT NULL,
    TonLyThuyet  DECIMAL(18,2) NOT NULL,
    TonThucTe    DECIMAL(18,2) NOT NULL,
    ChenhLech    DECIMAL(18,2) GENERATED ALWAYS AS (TonThucTe - TonLyThuyet) STORED,
    CONSTRAINT FK_CT_KIEM_KE_PKK FOREIGN KEY (MaPhieuKK) REFERENCES PHIEU_KIEM_KE(MaPhieuKK) ON DELETE CASCADE,
    CONSTRAINT FK_CT_KIEM_KE_TK  FOREIGN KEY (MaTonKho)  REFERENCES TON_KHO(MaTonKho),
    CONSTRAINT UQ_CT_KIEM_KE UNIQUE (MaPhieuKK, MaTonKho)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. NHÓM KHÁCH HÀNG & BÁN HÀNG
CREATE TABLE IF NOT EXISTS KHACH_HANG (
    MaKH       INT AUTO_INCREMENT PRIMARY KEY,
    TenKH      VARCHAR(100) NOT NULL,
    SDT        VARCHAR(15) NULL,
    LoaiKhach  VARCHAR(30) NULL,
    TongNo     DECIMAL(18,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS DANH_GIA_KHACH_HANG (
    MaDanhGia    INT AUTO_INCREMENT PRIMARY KEY,
    MaKH         INT NOT NULL,
    SoSao        TINYINT NOT NULL,
    NoiDung      TEXT NULL,
    NgayDanhGia  DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT FK_DANH_GIA_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS PHIEU_THU_NO (
    MaPhieuThu    INT AUTO_INCREMENT PRIMARY KEY,
    MaKH          INT NOT NULL,
    HinhThucThu   VARCHAR(50) NULL,
    SoTienThu     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_PHIEU_THU_NO_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS VOUCHER (
    MaVoucher     INT AUTO_INCREMENT PRIMARY KEY,
    GiaTriVoucher DECIMAL(18,2) NOT NULL,
    HanSuDung     DATE NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS PHIEU_BAN (
    MaPhieuBan    INT AUTO_INCREMENT PRIMARY KEY,
    MaKH          INT NOT NULL,
    MaNV_Affiliate INT NULL,
    NgayBan       DATE NOT NULL DEFAULT (CURRENT_DATE),
    TongTien      DECIMAL(18,2) NOT NULL DEFAULT 0,
    CONSTRAINT FK_PHIEU_BAN_KH FOREIGN KEY (MaKH) REFERENCES KHACH_HANG(MaKH),
    CONSTRAINT FK_PHIEU_BAN_AFF FOREIGN KEY (MaNV_Affiliate) REFERENCES NHAN_VIEN(MaNV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CT_PHIEU_BAN (
    MaCTPhieuBan  INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuBan    INT NOT NULL,
    MaBienThe     INT NOT NULL,
    SoLuongBan    DECIMAL(18,2) NOT NULL,
    DonGiaBan     DECIMAL(18,2) NOT NULL,
    CONSTRAINT FK_CT_PHIEU_BAN_PB FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_CT_PHIEU_BAN_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS PHIEU_BAN_VOUCHER (
    MaPhieuBan  INT NOT NULL,
    MaVoucher   INT NOT NULL,
    PRIMARY KEY (MaPhieuBan, MaVoucher),
    CONSTRAINT FK_PBV_PB FOREIGN KEY (MaPhieuBan) REFERENCES PHIEU_BAN(MaPhieuBan) ON DELETE CASCADE,
    CONSTRAINT FK_PBV_V  FOREIGN KEY (MaVoucher)  REFERENCES VOUCHER(MaVoucher)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS PHIEU_TRA_HANG (
    MaPhieuTra       INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuBanGoc    INT NOT NULL,
    LyDoTra          VARCHAR(255) NULL,
    CONSTRAINT FK_PHIEU_TRA_HANG_PB FOREIGN KEY (MaPhieuBanGoc) REFERENCES PHIEU_BAN(MaPhieuBan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS CT_TRA_HANG (
    MaCTTraHang     INT AUTO_INCREMENT PRIMARY KEY,
    MaPhieuTra      INT NOT NULL,
    MaBienThe       INT NOT NULL,
    SoLuongTra      DECIMAL(18,2) NOT NULL,
    TinhTrangHang   VARCHAR(100) NULL,
    NhapLaiKho      TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT FK_CT_TRA_HANG_PT FOREIGN KEY (MaPhieuTra) REFERENCES PHIEU_TRA_HANG(MaPhieuTra) ON DELETE CASCADE,
    CONSTRAINT FK_CT_TRA_HANG_BT FOREIGN KEY (MaBienThe)  REFERENCES BIEN_THE_SP(MaBienThe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- DỮ LIỆU MẪU ĐỂ CHẠY THỬ NGAY (KHỚP HOÀN TOÀN VỚI GIAO DIỆN MOCKUP)
-- =====================================================================

-- 1. Thợ may đối tác / Nhân viên
INSERT INTO NHAN_VIEN (MaNV, TenNV, ViTri, SDT, SoTaiKhoan, NganHang, TenTiemMay, CapBac, AffiliateCode) VALUES
(1, 'Nguyễn Hoàng Kim', 'Thợ may đối tác', '0903.882.119', '0491000288192', 'Vietcombank', 'Nhà May Hoàng Kim', 'Bạch Kim', 'thayhoang'),
(2, 'Trần Ý Lan', 'Thợ may đối tác', '0918.421.902', '1903456789012', 'Techcombank', 'Tiệm May Ý Lan', 'Kim Cương', 'tiemylan'),
(3, 'Nguyễn Thanh Tân', 'Thợ may đối tác', '0932.190.443', '82901239999', 'MBBank', 'Xưởng May Thanh Tân', 'Bạch Kim', 'thanhtan'),
(4, 'Trần Hùng', 'Thợ may đối tác', '0977.301.229', '1029283746', 'VietinBank', 'Atelier Trần Hùng', 'Vàng', 'tranhung');

-- 2. Ví hoa hồng cho thợ may
INSERT INTO VI_HOA_HONG (MaNV, SoTienHoaHong, TongTichLuy) VALUES
(1, 6820000, 18450000),
(2, 12000000, 25000000),
(3, 4050000, 9500000),
(4, 3200000, 8000000);

-- 3. Vải và Biến thể sản phẩm
INSERT INTO SAN_PHAM (MaSP, TenSP, NhomSP) VALUES
(1, 'Vải Linen Tưng Premium', 'Linen'),
(2, 'Lụa Tơ Tằm Bảo Lộc', 'Lụa'),
(3, 'Dạ Wool Tweed Cao Cấp', 'Dạ - Len'),
(4, 'Cotton Poplin Nhật', 'Cotton'),
(5, 'Lụa Satin Cao Cấp', 'Lụa'),
(6, 'Vải Tencel Dày Dặn', 'Tencel');

INSERT INTO BIEN_THE_SP (MaBienThe, MaSP, DonViTinh, MauSac, ChatLieuSize, MaVaiCode, HinhAnh) VALUES
(1, 1, 'Mét', 'Kem Mộc', 'Khổ 1.45m', '#LN-55', 'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=500'),
(2, 2, 'Mét', 'Trắng Ngà', 'Khổ 1.40m', '#LK-02', 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500'),
(3, 3, 'Mét', 'Dệt Nổi Xám', 'Khổ 1.50m', '#WL-19', 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=500'),
(4, 4, 'Mét', 'Trắng Sứ', 'Khổ 1.50m', '#COT-44', 'https://images.unsplash.com/photo-1607344645866-009c320b5ab8?w=500'),
(5, 5, 'Mét', 'Hồng Đỗ', 'Khổ 1.45m', '#SAT-12', 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=500'),
(6, 6, 'Mét', 'Đen Co Giãn', 'Khổ 1.40m', '#TC-05', 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=500');

-- 4. Bảng giá & Hoa hồng (5%)
INSERT INTO BANG_GIA (MaBienThe, LoaiKhach, DonGia, TyLeHoaHong) VALUES
(1, 'Khách lẻ', 185000, 5.00),
(2, 'Khách lẻ', 320000, 5.00),
(3, 'Khách lẻ', 450000, 5.00),
(4, 'Khách lẻ', 145000, 5.00),
(5, 'Khách lẻ', 210000, 5.00),
(6, 'Khách lẻ', 195000, 5.00);

-- 5. Bài đăng Lookbook (đã duyệt để khách xem & chờ duyệt để admin duyệt)
INSERT INTO LOOKBOOK (MaLookBook, TenLookBook, URLHinhAnh, DanhMuc, MaBienThe, DinhLuongVai, MoTa, TrangThaiDuyet) VALUES
(1, 'Đầm Maxi Linen Cổ Yếm', 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600', 'Đầm & Váy', 1, '2.4m vải', 'Đường cắt may tối giản, tôn bờ vai thanh mảnh và độ rũ mềm mại tự nhiên của sợi lanh tự nhiên.', 'Đã duyệt'),
(2, 'Áo Dài Lụa Tơ Cổ Cao Dáng Xưa', 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?w=600', 'Áo dài truyền thống', 2, '3.0m (Áo + Quần)', 'Dáng suông nhẹ nhàng, tay raglan ôm mềm, may thủ công giấu chỉ xác xảo chuẩn mực truyền thống.', 'Đã duyệt'),
(3, 'Blazer Dáng Hộp Wool Tweed', 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=600', 'Vest & Blazer', 3, '1.8m vải', 'Cấu trúc vai vuông nhẹ nhàng, lớp lót lụa thoáng tạo cảm giác sang trọng không gò bó.', 'Đã duyệt'),
(4, 'Sơ Mi Cotton Poplin Oversized', 'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600', 'Áo sơ mi & Công sở', 4, '1.6m vải', 'Cổ áo vát nhọn tinh tế, form rộng phóng khoáng, độ đanh mặt vải giữ dáng chuẩn cả ngày dài.', 'Đã duyệt'),
(5, 'Set Đồ Bộ Lụa Satin Mềm Mại', 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=600', 'Trang phục nữ', 5, '2.8m (Cả set)', 'Độ bóng mờ sang trọng, mặt dệt chạm vào da mát rượi, thích hợp mặc thư giãn hoặc dạo phố nhẹ nhàng.', 'Đã duyệt'),
(6, 'Quần Tây Pleated Dáng Rộng Tencel', 'https://images.unsplash.com/photo-1506630448388-4e683c67ddb0?w=600', 'Trang phục nam', 6, '1.4m vải', 'Nếp ly đôi xếp dứt khoát, độ rủ thẳng thắn tự nhiên từ chất Tencel cao cấp giúp tôn dáng tuyệt đối.', 'Đã duyệt'),
(7, 'Đầm Linen Suông Dáng Dài', 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=600', 'Đầm & Váy', 1, '2.2m vải', 'Bộ sưu tập Hạ 2026 - Thợ may gửi duyệt đính kèm tag mã vải Linen.', 'Chờ duyệt'),
(8, 'Áo Dài Lụa Tơ Tằm Hà Đông', 'https://images.unsplash.com/photo-1583391733975-5207c4273ecb?w=600', 'Áo dài truyền thống', 2, '3.2m vải', 'May đo cưới hỏi cao cấp.', 'Chờ duyệt');

INSERT INTO NHANVIEN_LOOKBOOK (MaNV, MaLookBook) VALUES
(1, 1),
(2, 2),
(4, 3),
(1, 4),
(2, 5),
(3, 6),
(1, 7),
(2, 8);

-- 6. Yêu cầu rút tiền mẫu (Cho màn hình Admin & Thợ may)
INSERT INTO YEU_CAU_RUT_TIEN (MaNV, SoTien, HinhThuc, ThongTinNhan, TrangThai) VALUES
(1, 8450000, 'NganHang', 'Vietcombank - 0491000288192 (HOANG KIM)', 'Chờ duyệt'),
(2, 12000000, 'MuaVaiAura', 'Đổi thành tiền mua vải (+1.200.000đ voucher)', 'Chờ duyệt'),
(3, 4050000, 'NganHang', 'MBBank - 82901239999 (NGUYEN THANH TAN)', 'Chờ duyệt');

-- 7. Lịch sử hoa hồng mẫu
INSERT INTO LICH_SU_HOA_HONG (MaNV, MaDonHangCode, LoaiGiaoDich, SoTien, TrangThai) VALUES
(1, '#ORD-5521', 'Hoa hồng đơn hàng Vải Linen Tưng', 240000, 'Thành công'),
(1, '#WD-88029', 'Rút tiền về Vietcombank', -4500000, 'Thành công'),
(1, '#ORD-5799', 'Hoa hồng đơn hàng Lụa Satin Cao Cấp', 185000, 'Thành công'),
(1, '#VO-10023', 'Đổi voucher vải Aura (+10%)', -1000000, 'Thành công');

