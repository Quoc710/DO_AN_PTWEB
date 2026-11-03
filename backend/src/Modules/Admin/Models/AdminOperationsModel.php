<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Config/database.php';

/** Data access for Lookbook moderation and tailor withdrawals in the Admin module. */
class AdminOperationsModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getPendingLookbooks(): array
    {
        $sql = "SELECT lb.MaLookBook, lb.TenLookBook, lb.URLHinhAnh, lb.DinhLuongVai,
                       bt.MaVaiCode, nv.TenNV, nv.TenTiemMay, nv.SDT
                FROM LOOKBOOK lb
                LEFT JOIN BIEN_THE_SP bt ON lb.MaBienThe = bt.MaBienThe
                LEFT JOIN NHANVIEN_LOOKBOOK nvlb ON lb.MaLookBook = nvlb.MaLookBook
                LEFT JOIN NHAN_VIEN nv ON nvlb.MaNV = nv.MaNV
                WHERE lb.TrangThaiDuyet = 'Chờ duyệt'
                ORDER BY lb.MaLookBook DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function updateLookbookStatus(int $lookbookId, string $status): void
    {
        if ($lookbookId < 1 || !in_array($status, ['Đã duyệt', 'Từ chối'], true)) {
            throw new InvalidArgumentException('Thông tin duyệt Lookbook không hợp lệ.');
        }

        $stmt = $this->db->prepare(
            "UPDATE LOOKBOOK SET TrangThaiDuyet = :status WHERE MaLookBook = :id AND TrangThaiDuyet = 'Chờ duyệt'"
        );
        $stmt->execute([':status' => $status, ':id' => $lookbookId]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Bài Lookbook không tồn tại hoặc đã được xử lý.');
        }
    }

    public function getPendingWithdrawals(): array
    {
        $sql = "SELECT yc.MaYeuCau, yc.SoTien, yc.HinhThuc, yc.ThongTinNhan, yc.NgayTao,
                       nv.TenNV, nv.TenTiemMay, nv.SDT, nv.SoTaiKhoan, nv.NganHang,
                       vhh.SoTienHoaHong
                FROM YEU_CAU_RUT_TIEN yc
                INNER JOIN NHAN_VIEN nv ON yc.MaNV = nv.MaNV
                LEFT JOIN VI_HOA_HONG vhh ON nv.MaNV = vhh.MaNV
                WHERE yc.TrangThai = 'Chờ duyệt'
                ORDER BY yc.MaYeuCau DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function approveWithdrawal(int $requestId): void
    {
        if ($requestId < 1) {
            throw new InvalidArgumentException('Mã yêu cầu rút tiền không hợp lệ.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT * FROM YEU_CAU_RUT_TIEN WHERE MaYeuCau = :id FOR UPDATE');
            $stmt->execute([':id' => $requestId]);
            $request = $stmt->fetch();
            if (!$request || $request['TrangThai'] !== 'Chờ duyệt') {
                throw new RuntimeException('Yêu cầu không tồn tại hoặc đã được xử lý.');
            }

            $walletStmt = $this->db->prepare('SELECT SoTienHoaHong FROM VI_HOA_HONG WHERE MaNV = :employee FOR UPDATE');
            $walletStmt->execute([':employee' => $request['MaNV']]);
            $wallet = $walletStmt->fetch();
            if (!$wallet || (float) $wallet['SoTienHoaHong'] < (float) $request['SoTien']) {
                throw new RuntimeException('Số dư ví hoa hồng không đủ để duyệt yêu cầu này.');
            }

            $updateRequest = $this->db->prepare(
                "UPDATE YEU_CAU_RUT_TIEN SET TrangThai = 'Đã chuyển khoản' WHERE MaYeuCau = :id AND TrangThai = 'Chờ duyệt'"
            );
            $updateRequest->execute([':id' => $requestId]);
            if ($updateRequest->rowCount() !== 1) {
                throw new RuntimeException('Yêu cầu đã được xử lý bởi thao tác khác.');
            }

            $this->db->prepare(
                'UPDATE VI_HOA_HONG SET SoTienHoaHong = SoTienHoaHong - :amount WHERE MaNV = :employee'
            )->execute([':amount' => $request['SoTien'], ':employee' => $request['MaNV']]);

            $transactionType = $request['HinhThuc'] === 'MuaVaiAura'
                ? 'Đổi voucher mua vải (+10%)'
                : 'Rút tiền về tài khoản ngân hàng';
            $this->db->prepare(
                "INSERT INTO LICH_SU_HOA_HONG (MaNV, MaDonHangCode, LoaiGiaoDich, SoTien, TrangThai)
                 VALUES (:employee, :code, :type, :amount, 'Thành công')"
            )->execute([
                ':employee' => $request['MaNV'],
                ':code' => '#WD-' . $requestId,
                ':type' => $transactionType,
                ':amount' => -1 * (float) $request['SoTien'],
            ]);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function rejectWithdrawal(int $requestId): void
    {
        if ($requestId < 1) {
            throw new InvalidArgumentException('Mã yêu cầu rút tiền không hợp lệ.');
        }
        $stmt = $this->db->prepare(
            "UPDATE YEU_CAU_RUT_TIEN SET TrangThai = 'Từ chối' WHERE MaYeuCau = :id AND TrangThai = 'Chờ duyệt'"
        );
        $stmt->execute([':id' => $requestId]);
        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException('Yêu cầu không tồn tại hoặc đã được xử lý.');
        }
    }
}