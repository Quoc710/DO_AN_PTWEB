-- Development data that makes both partner and customer Lookbook paths usable immediately.
USE QuanLyCuaHangVai;
SET NAMES utf8mb4;

INSERT INTO NHAN_VIEN (TenNV, ViTri, SDT, SoTaiKhoan, NganHang, TenTiemMay, CapBac, AffiliateCode)
SELECT 'Nguyễn Thị Minh Anh', 'Thợ may', '0901000002', '0123456789', 'Vietcombank', 'Tiệm may Minh Anh', 'Đối tác vàng', 'minhanh'
WHERE NOT EXISTS (SELECT 1 FROM NHAN_VIEN WHERE AffiliateCode = 'minhanh');

INSERT INTO NHAN_VIEN (TenNV, ViTri, SDT, SoTaiKhoan, NganHang, TenTiemMay, CapBac, AffiliateCode)
SELECT 'Trần Hoàng Nam', 'Thợ may', '0901000003', '0987654321', 'Techcombank', 'Nhà may Hoàng Nam', 'Đối tác bạc', 'hoangnam'
WHERE NOT EXISTS (SELECT 1 FROM NHAN_VIEN WHERE AffiliateCode = 'hoangnam');

INSERT IGNORE INTO VI_HOA_HONG (MaNV, SoTienHoaHong, TongTichLuy)
SELECT MaNV, 0, 0 FROM NHAN_VIEN;

UPDATE BIEN_THE_SP
SET MaVaiCode = COALESCE(MaVaiCode, CONCAT('#', MaSKU)),
    HinhAnh = COALESCE(HinhAnh, HinhAnhMacro);

UPDATE BANG_GIA SET TyLeHoaHong = 5 WHERE TyLeHoaHong = 0;

INSERT INTO LOOKBOOK (TenLookBook, URLHinhAnh, DanhMuc, MaBienThe, DinhLuongVai, MoTa, TrangThaiDuyet)
SELECT 'Đầm Linen Suông Dáng Dài', 'https://images.unsplash.com/photo-1539109136881-3be0616acf4f?w=600', 'Đầm & Váy', 1, '2.2m vải', 'Bài mẫu đã duyệt để giới thiệu vải linen trên Lookbook.', 'Đã duyệt'
WHERE NOT EXISTS (SELECT 1 FROM LOOKBOOK WHERE TenLookBook = 'Đầm Linen Suông Dáng Dài');

INSERT INTO LOOKBOOK (TenLookBook, URLHinhAnh, DanhMuc, MaBienThe, DinhLuongVai, MoTa, TrangThaiDuyet)
SELECT 'Áo kiểu lụa thêu hoa', 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?w=600', 'Áo dài truyền thống', 2, '2.5m vải', 'Bài mẫu chờ Admin thẩm định.', 'Chờ duyệt'
WHERE NOT EXISTS (SELECT 1 FROM LOOKBOOK WHERE TenLookBook = 'Áo kiểu lụa thêu hoa');

INSERT IGNORE INTO NHANVIEN_LOOKBOOK (MaNV, MaLookBook)
SELECT nv.MaNV, lb.MaLookBook
FROM NHAN_VIEN nv JOIN LOOKBOOK lb ON lb.TenLookBook = 'Đầm Linen Suông Dáng Dài'
WHERE nv.AffiliateCode = 'minhanh';

INSERT IGNORE INTO NHANVIEN_LOOKBOOK (MaNV, MaLookBook)
SELECT nv.MaNV, lb.MaLookBook
FROM NHAN_VIEN nv JOIN LOOKBOOK lb ON lb.TenLookBook = 'Áo kiểu lụa thêu hoa'
WHERE nv.AffiliateCode = 'hoangnam';