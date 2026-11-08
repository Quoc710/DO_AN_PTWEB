/* =====================================================================
   MODULE: GIỎ HÀNG & THANH TOÁN (đơn hàng online + VietQR ngân hàng)
   Chạy tự động qua docker-entrypoint (lần đầu tạo volume) hoặc import tay.
   Model CheckoutModel cũng tự CREATE TABLE IF NOT EXISTS khi gọi API,
   nên không bắt buộc phải rebuild volume.
   ===================================================================== */

SET NAMES utf8mb4;

USE QuanLyCuaHangVai;

CREATE TABLE IF NOT EXISTS DON_HANG_ONLINE (
    MaDon                 VARCHAR(20)    NOT NULL PRIMARY KEY,
    MaKH                  INT            NULL,
    TenKH                 VARCHAR(100)   NOT NULL,
    SDT                   VARCHAR(20)    NOT NULL,
    Email                 VARCHAR(120)   NULL,
    DiaChi                VARCHAR(255)   NOT NULL,
    GhiChu                VARCHAR(255)   NULL,
    TongTienHang          DECIMAL(18,2)  NOT NULL DEFAULT 0,
    ChietKhau             DECIMAL(18,2)  NOT NULL DEFAULT 0,
    Voucher               DECIMAL(18,2)  NOT NULL DEFAULT 0,
    MaVoucher             INT            NULL,
    MaCodeVoucher         VARCHAR(50)    NULL,
    PhiVanChuyen          DECIMAL(18,2)  NOT NULL DEFAULT 0,
    MaVanChuyen           VARCHAR(30)    NOT NULL DEFAULT 'standard',
    TongThanhToan         DECIMAL(18,2)  NOT NULL DEFAULT 0,
    PhuongThucThanhToan   VARCHAR(20)    NOT NULL DEFAULT 'vietqr',
    TrangThai             VARCHAR(30)    NOT NULL DEFAULT 'cho_thanh_toan',
    MaGiaoDichNH          VARCHAR(120)   NULL,
    NoiDungCK             VARCHAR(120)   NULL,
    NgayBatDauGiao         DATETIME       NULL,
    NgayGiaoThanhCong      DATETIME       NULL,
    XacNhanGiaoBoi         VARCHAR(20)    NULL,
    HoaHongDaCong         TINYINT(1)     NOT NULL DEFAULT 0,
    NgayTao               DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_dhonline_trangthai (TrangThai),
    INDEX idx_dhonline_sdt (SDT)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS CT_DON_HANG_ONLINE (
    MaCT         INT            NOT NULL AUTO_INCREMENT PRIMARY KEY,
    MaDon        VARCHAR(20)    NOT NULL,
    MaVai        VARCHAR(60)    NULL,
    TenSP        VARCHAR(255)   NOT NULL,
    SoMet        DECIMAL(10,2)  NOT NULL,
    DonGia       DECIMAL(18,2)  NOT NULL,
    ChietKhau    DECIMAL(18,2)  NOT NULL DEFAULT 0,
    ThanhTien    DECIMAL(18,2)  NOT NULL,
    RefCode      VARCHAR(60)    NULL,
    HinhAnh      TEXT           NULL,
    DinhLuong    VARCHAR(120)   NULL,
    MauSac       VARCHAR(120)   NULL,
    KhoVai       VARCHAR(120)   NULL,
    CONSTRAINT FK_CTDHONLINE_DON FOREIGN KEY (MaDon)
        REFERENCES DON_HANG_ONLINE(MaDon) ON DELETE CASCADE,
    CONSTRAINT CK_CTDHONLINE_SOMET CHECK (SoMet > 0),
    CONSTRAINT CK_CTDHONLINE_DONGIA CHECK (DonGia >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
