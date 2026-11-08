<?php
declare(strict_types=1);

namespace App\Modules\CartCheckout\Models;

use InvalidArgumentException;
use PDO;
use Throwable;

/**
 * Model đơn hàng online cho module Giỏ hàng & Thanh toán.
 * Tự tạo bảng nếu chưa có để chạy được trên volume MySQL cũ.
 */
class CheckoutModel
{
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const SHIPPING_OPTIONS = [
        'economy' => ['label' => 'Tiết kiệm', 'fee' => 15000],
        'standard' => ['label' => 'Tiêu chuẩn', 'fee' => 30000],
        'express' => ['label' => 'Hỏa tốc', 'fee' => 50000],
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = \Database::getConnection();
        $this->ensureSchema();
        $this->ensureVoucherColumns();
        $this->ensureShippingColumn();
        $this->ensureDeliveryColumns();
    }

    private function ensureSchema(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS DON_HANG_ONLINE (
                MaDon VARCHAR(20) NOT NULL PRIMARY KEY,
                MaKH INT NULL,
                TenKH VARCHAR(100) NOT NULL,
                SDT VARCHAR(20) NOT NULL,
                Email VARCHAR(120) NULL,
                DiaChi VARCHAR(255) NOT NULL,
                GhiChu VARCHAR(255) NULL,
                TongTienHang DECIMAL(18,2) NOT NULL DEFAULT 0,
                ChietKhau DECIMAL(18,2) NOT NULL DEFAULT 0,
                Voucher DECIMAL(18,2) NOT NULL DEFAULT 0,
                MaVoucher INT NULL,
                MaCodeVoucher VARCHAR(50) NULL,
                PhiVanChuyen DECIMAL(18,2) NOT NULL DEFAULT 0,
                MaVanChuyen VARCHAR(30) NOT NULL DEFAULT 'standard',
                TongThanhToan DECIMAL(18,2) NOT NULL DEFAULT 0,
                PhuongThucThanhToan VARCHAR(20) NOT NULL DEFAULT 'vietqr',
                TrangThai VARCHAR(30) NOT NULL DEFAULT 'cho_thanh_toan',
                MaGiaoDichNH VARCHAR(120) NULL,
                NoiDungCK VARCHAR(120) NULL,
                NgayBatDauGiao DATETIME NULL,
                NgayGiaoThanhCong DATETIME NULL,
                XacNhanGiaoBoi VARCHAR(20) NULL,
                HoaHongDaCong TINYINT(1) NOT NULL DEFAULT 0,
                NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                NgayCapNhat DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_dhonline_trangthai (TrangThai),
                INDEX idx_dhonline_sdt (SDT)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS CT_DON_HANG_ONLINE (
                MaCT INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                MaDon VARCHAR(20) NOT NULL,
                MaVai VARCHAR(60) NULL,
                TenSP VARCHAR(255) NOT NULL,
                SoMet DECIMAL(10,2) NOT NULL,
                DonGia DECIMAL(18,2) NOT NULL,
                ChietKhau DECIMAL(18,2) NOT NULL DEFAULT 0,
                ThanhTien DECIMAL(18,2) NOT NULL,
                RefCode VARCHAR(60) NULL,
                HinhAnh TEXT NULL,
                DinhLuong VARCHAR(120) NULL,
                MauSac VARCHAR(120) NULL,
                KhoVai VARCHAR(120) NULL,
                CONSTRAINT FK_CTDHONLINE_DON FOREIGN KEY (MaDon)
                    REFERENCES DON_HANG_ONLINE(MaDon) ON DELETE CASCADE,
                CONSTRAINT CK_CTDHONLINE_SOMET CHECK (SoMet > 0),
                CONSTRAINT CK_CTDHONLINE_DONGIA CHECK (DonGia >= 0)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    /**
     * Bổ sung cột voucher cho bảng đã tồn tại trên volume MySQL cũ.
     */
    private function ensureVoucherColumns(): void
    {
        try {
            $columns = $this->db->query('SHOW COLUMNS FROM DON_HANG_ONLINE')
                ->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('MaVoucher', $columns, true)) {
                $this->db->exec('ALTER TABLE DON_HANG_ONLINE ADD COLUMN MaVoucher INT NULL AFTER Voucher');
            }
            if (!in_array('MaCodeVoucher', $columns, true)) {
                $this->db->exec('ALTER TABLE DON_HANG_ONLINE ADD COLUMN MaCodeVoucher VARCHAR(50) NULL AFTER MaVoucher');
            }
        } catch (Throwable $ignored) {
            // Không chặn luồng thanh toán nếu tài khoản DB không có quyền ALTER.
        }
    }

    private function ensureShippingColumn(): void
    {
        try {
            $columns = $this->db->query('SHOW COLUMNS FROM DON_HANG_ONLINE')
                ->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('MaVanChuyen', $columns, true)) {
                $this->db->exec("ALTER TABLE DON_HANG_ONLINE ADD COLUMN MaVanChuyen VARCHAR(30) NOT NULL DEFAULT 'standard' AFTER PhiVanChuyen");
            }
        } catch (Throwable $ignored) {
            // Giữ luồng thanh toán chạy được trên DB cũ nếu không có quyền ALTER.
        }
    }

    private function ensureDeliveryColumns(): void
    {
        try {
            $columns = $this->db->query('SHOW COLUMNS FROM DON_HANG_ONLINE')
                ->fetchAll(PDO::FETCH_COLUMN);
            $definitions = [
                'NgayBatDauGiao' => 'DATETIME NULL',
                'NgayGiaoThanhCong' => 'DATETIME NULL',
                'XacNhanGiaoBoi' => 'VARCHAR(20) NULL',
            ];
            foreach ($definitions as $column => $definition) {
                if (!in_array($column, $columns, true)) {
                    $this->db->exec("ALTER TABLE DON_HANG_ONLINE ADD COLUMN {$column} {$definition}");
                }
            }
        } catch (Throwable $ignored) {
            // Không làm hỏng luồng cửa hàng khi DB cũ không cho phép cập nhật schema.
        }
    }

    /**
     * @return array{MaDon:string,TongTienHang:float,ChietKhau:float,Voucher:float,PhiVanChuyen:float,TongThanhToan:float,PhuongThucThanhToan:string,TrangThai:string,NgayTao:string}
     */
    public function createOrder(array $customer, array $items, string $method, string $voucherCode = '', string $shippingMethod = 'standard'): array
    {
        $tenKH = trim((string) ($customer['tenKH'] ?? $customer['TenKH'] ?? ''));
        $sdt = trim((string) ($customer['sdt'] ?? $customer['SDT'] ?? ''));
        $diaChi = trim((string) ($customer['diaChi'] ?? $customer['DiaChi'] ?? ''));
        $email = trim((string) ($customer['email'] ?? $customer['Email'] ?? ''));
        $ghiChu = trim((string) ($customer['ghiChu'] ?? $customer['GhiChu'] ?? ''));
        $maKH = (int) ($customer['maKH'] ?? $customer['MaKH'] ?? 0);

        if ($tenKH === '' || mb_strlen($tenKH) < 2) {
            throw new InvalidArgumentException('Vui lòng nhập họ tên người nhận.');
        }
        if (!preg_match('/^[0-9+().\-\s]{8,20}$/', $sdt)) {
            throw new InvalidArgumentException('Số điện thoại chưa hợp lệ.');
        }
        if (mb_strlen($diaChi) < 8) {
            throw new InvalidArgumentException('Vui lòng nhập địa chỉ nhận hàng chi tiết.');
        }

        $normalized = $this->normalizeItems($items);

        $tongHang = 0.0;
        $chietKhau = 0.0;
        foreach ($normalized as $item) {
            $tongHang += $item['donGia'] * $item['soMet'];
            $chietKhau += $item['chietKhau'];
        }
        $tongHang = round($tongHang, 2);
        $chietKhau = round($chietKhau, 2);

        $subtotal = round(max(0, $tongHang - $chietKhau), 2);
        $voucherAmount = 0.0;
        $voucherId = null;
        $voucherCodeStored = null;
        $voucherCode = strtoupper(trim($voucherCode));
        if ($voucherCode !== '') {
            $voucher = $this->validateVoucher($voucherCode, $subtotal);
            $voucherAmount = (float) $voucher['discount'];
            $voucherId = (int) $voucher['maVoucher'];
            $voucherCodeStored = (string) $voucher['maCode'];
        }

        if (!isset(self::SHIPPING_OPTIONS[$shippingMethod])) {
            throw new InvalidArgumentException('Vui lòng chọn phương thức vận chuyển hợp lệ.');
        }
        $phiVanChuyen = (float) self::SHIPPING_OPTIONS[$shippingMethod]['fee'];
        $tongThanhToan = round(max(0, $subtotal - $voucherAmount + $phiVanChuyen), 2);

        $code = $this->generateCode();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO DON_HANG_ONLINE
                    (MaDon, MaKH, TenKH, SDT, Email, DiaChi, GhiChu, TongTienHang, ChietKhau, Voucher,
                     MaVoucher, MaCodeVoucher, PhiVanChuyen, MaVanChuyen, TongThanhToan, PhuongThucThanhToan, TrangThai, NoiDungCK)
                 VALUES
                    (:maDon, :maKH, :tenKH, :sdt, :email, :diaChi, :ghiChu, :tongHang, :chietKhau, :voucher,
                     :maVoucher, :maCodeVoucher, :phiVanChuyen, :maVanChuyen, :tongThanhToan, :method, :trangThai, :noiDung)'
            );
            $stmt->execute([
                ':maDon' => $code,
                ':maKH' => $maKH > 0 ? $maKH : null,
                ':tenKH' => mb_substr($tenKH, 0, 100),
                ':sdt' => mb_substr($sdt, 0, 20),
                ':email' => $email !== '' ? mb_substr($email, 0, 120) : null,
                ':diaChi' => mb_substr($diaChi, 0, 255),
                ':ghiChu' => $ghiChu !== '' ? mb_substr($ghiChu, 0, 255) : null,
                ':tongHang' => $tongHang,
                ':chietKhau' => $chietKhau,
                ':voucher' => $voucherAmount,
                ':maVoucher' => $voucherId,
                ':maCodeVoucher' => $voucherCodeStored,
                ':phiVanChuyen' => $phiVanChuyen,
                ':maVanChuyen' => $shippingMethod,
                ':tongThanhToan' => $tongThanhToan,
                ':method' => $method === 'cod' ? 'cod' : 'vietqr',
                ':trangThai' => $method === 'cod' ? 'dang_xu_ly' : 'cho_thanh_toan',
                ':noiDung' => 'Thanh toan don ' . $code,
            ]);

            $itemStmt = $this->db->prepare(
                'INSERT INTO CT_DON_HANG_ONLINE
                    (MaDon, MaVai, TenSP, SoMet, DonGia, ChietKhau, ThanhTien, RefCode, HinhAnh, DinhLuong, MauSac, KhoVai)
                 VALUES
                    (:maDon, :maVai, :tenSP, :soMet, :donGia, :chietKhau, :thanhTien, :refCode, :hinhAnh, :dinhLuong, :mauSac, :khoVai)'
            );
            foreach ($normalized as $item) {
                $itemStmt->execute([
                    ':maDon' => $code,
                    ':maVai' => $item['maVai'] !== '' ? $item['maVai'] : null,
                    ':tenSP' => $item['tenSP'],
                    ':soMet' => $item['soMet'],
                    ':donGia' => $item['donGia'],
                    ':chietKhau' => $item['chietKhau'],
                    ':thanhTien' => $item['thanhTien'],
                    ':refCode' => $item['refCode'] !== '' ? $item['refCode'] : null,
                    ':hinhAnh' => $item['hinhAnh'] !== '' ? $item['hinhAnh'] : null,
                    ':dinhLuong' => $item['dinhLuong'] !== '' ? $item['dinhLuong'] : null,
                    ':mauSac' => $item['mauSac'] !== '' ? $item['mauSac'] : null,
                    ':khoVai' => $item['khoVai'] !== '' ? $item['khoVai'] : null,
                ]);
            }

            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }

        return $this->getOrder($code) ?? [];
    }

    /** @return array<int, array<string, mixed>> */
    public function getOrdersByCustomer(int $maKH): array
    {
        $this->autoConfirmExpiredDeliveries();
        $stmt = $this->db->prepare(
            'SELECT * FROM DON_HANG_ONLINE WHERE MaKH = :maKH ORDER BY NgayTao DESC, MaDon DESC'
        );
        $stmt->execute([':maKH' => $maKH]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($orders as &$order) {
            $items = $this->db->prepare('SELECT * FROM CT_DON_HANG_ONLINE WHERE MaDon = :code ORDER BY MaCT ASC');
            $items->execute([':code' => $order['MaDon']]);
            $order['items'] = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach (['TongTienHang', 'ChietKhau', 'Voucher', 'PhiVanChuyen', 'TongThanhToan'] as $amount) {
                $order[$amount] = (float) ($order[$amount] ?? 0);
            }
        }
        unset($order);
        return $orders;
    }

    /** @return array<int, array<string, mixed>> */
    public function getAllOrdersForAdmin(): array
    {
        $this->autoConfirmExpiredDeliveries();
        $orders = $this->db->query(
            'SELECT * FROM DON_HANG_ONLINE ORDER BY NgayTao DESC, MaDon DESC LIMIT 500'
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($orders as &$order) {
            $items = $this->db->prepare('SELECT * FROM CT_DON_HANG_ONLINE WHERE MaDon = :code ORDER BY MaCT ASC');
            $items->execute([':code' => $order['MaDon']]);
            $order['items'] = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach (['TongTienHang', 'ChietKhau', 'Voucher', 'PhiVanChuyen', 'TongThanhToan'] as $amount) {
                $order[$amount] = (float) ($order[$amount] ?? 0);
            }
        }
        unset($order);
        return $orders;
    }

    public function startDelivery(string $code): array
    {
        $code = strtoupper(trim($code));
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT TrangThai FROM DON_HANG_ONLINE WHERE MaDon = :code FOR UPDATE');
            $stmt->execute([':code' => $code]);
            $status = $stmt->fetchColumn();
            if ($status === false) {
                throw new InvalidArgumentException('Không tìm thấy đơn hàng ' . $code . '.');
            }
            if ($status === 'dang_giao') {
                $this->db->commit();
                return $this->getOrder($code) ?? [];
            }
            if (!in_array($status, ['da_thanh_toan', 'dang_xu_ly'], true)) {
                throw new InvalidArgumentException('Chỉ có thể bắt đầu giao đơn đã thanh toán hoặc đơn COD đang xử lý.');
            }
            $this->db->prepare(
                "UPDATE DON_HANG_ONLINE SET TrangThai = 'dang_giao', NgayBatDauGiao = NOW(), NgayGiaoThanhCong = NULL, XacNhanGiaoBoi = NULL WHERE MaDon = :code"
            )->execute([':code' => $code]);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
        return $this->getOrder($code) ?? [];
    }

    public function confirmDelivered(string $code, string $confirmedBy, ?int $maKH = null): array
    {
        $code = strtoupper(trim($code));
        $this->db->beginTransaction();
        try {
            $sql = 'SELECT TrangThai, PhuongThucThanhToan, MaVoucher FROM DON_HANG_ONLINE WHERE MaDon = :code';
            if ($maKH !== null) $sql .= ' AND MaKH = :maKH';
            $sql .= ' FOR UPDATE';
            $stmt = $this->db->prepare($sql);
            $params = [':code' => $code];
            if ($maKH !== null) $params[':maKH'] = $maKH;
            $stmt->execute($params);
            $orderState = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$orderState) {
                throw new InvalidArgumentException($maKH === null
                    ? 'Không tìm thấy đơn hàng ' . $code . '.'
                    : 'Không tìm thấy đơn hàng thuộc tài khoản của bạn.');
            }
            $status = (string) $orderState['TrangThai'];
            if ($status === 'da_giao') {
                $this->db->commit();
                return $this->getOrder($code) ?? [];
            }
            if ($status !== 'dang_giao') {
                throw new InvalidArgumentException('Đơn hàng chưa ở trạng thái đang giao.');
            }
            $confirmedBy = in_array($confirmedBy, ['admin', 'khach_hang', 'tu_dong'], true) ? $confirmedBy : 'admin';
            $this->db->prepare(
                "UPDATE DON_HANG_ONLINE SET TrangThai = 'da_giao', NgayGiaoThanhCong = NOW(), XacNhanGiaoBoi = :actor WHERE MaDon = :code"
            )->execute([':actor' => $confirmedBy, ':code' => $code]);
            // COD được thu khi giao thành công nên ghi nhận voucher ở thời điểm này.
            if (($orderState['PhuongThucThanhToan'] ?? '') === 'cod' && !empty($orderState['MaVoucher'])) {
                $this->db->prepare(
                    'UPDATE VOUCHER SET SoLuotDaDung = SoLuotDaDung + 1 WHERE MaVoucher = :id'
                )->execute([':id' => (int) $orderState['MaVoucher']]);
            }
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
        return $this->getOrder($code) ?? [];
    }

    public function cancelDelivery(string $code, int $maKH): array
    {
        $code = strtoupper(trim($code));
        $this->autoConfirmExpiredDeliveries();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT TrangThai FROM DON_HANG_ONLINE WHERE MaDon = :code AND MaKH = :maKH FOR UPDATE');
            $stmt->execute([':code' => $code, ':maKH' => $maKH]);
            $status = $stmt->fetchColumn();
            if ($status === false) {
                throw new InvalidArgumentException('Không tìm thấy đơn hàng thuộc tài khoản của bạn.');
            }
            if ($status !== 'dang_giao') {
                throw new InvalidArgumentException('Chỉ có thể hủy đơn khi đơn đang được giao.');
            }
            $this->db->prepare(
                "UPDATE DON_HANG_ONLINE SET TrangThai = 'da_huy', XacNhanGiaoBoi = NULL WHERE MaDon = :code"
            )->execute([':code' => $code]);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
        return $this->getOrder($code) ?? [];
    }

    public function autoConfirmExpiredDeliveries(): int
    {
        $stmt = $this->db->prepare(
            "UPDATE DON_HANG_ONLINE
                SET TrangThai = 'da_giao', NgayGiaoThanhCong = NOW(), XacNhanGiaoBoi = 'tu_dong'
              WHERE TrangThai = 'dang_giao'
                AND NgayBatDauGiao IS NOT NULL
                AND CASE MaVanChuyen
                    WHEN 'economy' THEN DATE_ADD(NgayBatDauGiao, INTERVAL 6 DAY)
                    WHEN 'express' THEN DATE_ADD(NgayBatDauGiao, INTERVAL 2 DAY)
                    ELSE DATE_ADD(NgayBatDauGiao, INTERVAL 4 DAY)
                END <= NOW()"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function getOrder(string $code): ?array
    {
        $this->autoConfirmExpiredDeliveries();
        $code = strtoupper(trim($code));
        $stmt = $this->db->prepare('SELECT * FROM DON_HANG_ONLINE WHERE MaDon = :code LIMIT 1');
        $stmt->execute([':code' => $code]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) {
            return null;
        }

        $items = $this->db->prepare('SELECT * FROM CT_DON_HANG_ONLINE WHERE MaDon = :code ORDER BY MaCT ASC');
        $items->execute([':code' => $code]);
        $order['items'] = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $order['TongTienHang'] = (float) $order['TongTienHang'];
        $order['ChietKhau'] = (float) $order['ChietKhau'];
        $order['Voucher'] = (float) $order['Voucher'];
        $order['PhiVanChuyen'] = (float) $order['PhiVanChuyen'];
        $order['TongThanhToan'] = (float) $order['TongThanhToan'];
        return $order;
    }

    public function findOrderByContent(string $content): ?string
    {
        $upper = strtoupper($content);
        if (!preg_match('/AUR[0-9A-Z]{6}/', $upper, $match)) {
            return null;
        }
        $code = $match[0];
        $stmt = $this->db->prepare('SELECT MaDon FROM DON_HANG_ONLINE WHERE MaDon = :code LIMIT 1');
        $stmt->execute([':code' => $code]);
        $found = $stmt->fetchColumn();
        return $found ? (string) $found : null;
    }

    /**
     * Danh sách voucher đang hiệu lực để khách hàng chọn.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listActiveVouchers(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT MaVoucher, MaCode, TenChuongTrinh, MoTaDieuKien, LoaiGiam,
                        GiaTriVoucher, HanSuDung, GioiHanLuot, SoLuotDaDung
                   FROM VOUCHER
                  WHERE TrangThai = 1
                    AND (HanSuDung IS NULL OR HanSuDung >= CURDATE())
                    AND SoLuotDaDung < GioiHanLuot
                  ORDER BY MaVoucher DESC"
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            return array_map(fn (array $row): array => $this->formatVoucher($row), $rows);
        } catch (Throwable $ignored) {
            return [];
        }
    }

    /**
     * Kiểm tra mã voucher và tính số tiền giảm trên tạm tính.
     *
     * @return array<string, mixed>
     */
    public function validateVoucher(string $code, float $subtotal): array
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            throw new InvalidArgumentException('Vui lòng nhập mã voucher.');
        }

        $stmt = $this->db->prepare('SELECT * FROM VOUCHER WHERE UPPER(MaCode) = :code LIMIT 1');
        $stmt->execute([':code' => $code]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$voucher) {
            throw new InvalidArgumentException('Mã voucher "' . $code . '" không tồn tại.');
        }
        if ((int) ($voucher['TrangThai'] ?? 0) !== 1) {
            throw new InvalidArgumentException('Mã voucher "' . $code . '" đang tạm ngưng.');
        }
        $expiry = trim((string) ($voucher['HanSuDung'] ?? ''));
        if ($expiry !== '' && $expiry < date('Y-m-d')) {
            throw new InvalidArgumentException('Mã voucher "' . $code . '" đã hết hạn sử dụng.');
        }
        $limit = (int) ($voucher['GioiHanLuot'] ?? 0);
        $used = (int) ($voucher['SoLuotDaDung'] ?? 0);
        if ($limit > 0 && $used >= $limit) {
            throw new InvalidArgumentException('Mã voucher "' . $code . '" đã hết lượt sử dụng.');
        }
        if ($subtotal <= 0) {
            throw new InvalidArgumentException('Chưa có sản phẩm để áp dụng voucher.');
        }

        return $this->applyVoucher($voucher, $subtotal);
    }

    /**
     * @param array<string, mixed> $voucher
     * @return array<string, mixed>
     */
    private function applyVoucher(array $voucher, float $subtotal): array
    {
        $formatted = $this->formatVoucher($voucher);
        $subtotal = round(max(0, $subtotal), 2);

        if ($formatted['loaiGiam'] === '%') {
            $discount = $subtotal * ($formatted['giaTri'] / 100);
        } else {
            $discount = $formatted['giaTri'];
        }

        $formatted['discount'] = round(min($discount, $subtotal), 2);
        return $formatted;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatVoucher(array $row): array
    {
        $type = (string) ($row['LoaiGiam'] ?? '%') === '%' ? '%' : 'VND';
        return [
            'maVoucher' => (int) ($row['MaVoucher'] ?? 0),
            'maCode' => (string) ($row['MaCode'] ?? ''),
            'tenChuongTrinh' => (string) ($row['TenChuongTrinh'] ?? ''),
            'moTaDieuKien' => (string) ($row['MoTaDieuKien'] ?? ''),
            'loaiGiam' => $type,
            'giaTri' => (float) ($row['GiaTriVoucher'] ?? 0),
            'hanSuDung' => $row['HanSuDung'] ?? null,
            'gioiHanLuot' => (int) ($row['GioiHanLuot'] ?? 0),
            'soLuotDaDung' => (int) ($row['SoLuotDaDung'] ?? 0),
            'discount' => 0.0,
        ];
    }

    /**
     * Đánh dấu đã thanh toán (idempotent) và cộng hoa hồng affiliate.
     */
    public function markPaid(string $code, string $bankRef = '', ?float $paidAmount = null): array
    {
        $code = strtoupper(trim($code));
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare('SELECT * FROM DON_HANG_ONLINE WHERE MaDon = :code FOR UPDATE');
            $stmt->execute([':code' => $code]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order) {
                throw new InvalidArgumentException('Không tìm thấy đơn hàng ' . $code . '.');
            }
            if (in_array($order['TrangThai'], ['da_thanh_toan', 'dang_giao', 'da_giao', 'hoan_tat', 'da_huy'], true)) {
                $this->db->commit();
                return $this->getOrder($code) ?? [];
            }
            if ($paidAmount !== null && $paidAmount > 0 && $paidAmount + 0.5 < (float) $order['TongThanhToan']) {
                throw new InvalidArgumentException(
                    'Số tiền chuyển khoản (' . number_format($paidAmount, 0, ',', '.') . 'đ) nhỏ hơn giá trị đơn hàng.'
                );
            }

            $update = $this->db->prepare(
                "UPDATE DON_HANG_ONLINE
                    SET TrangThai = 'da_thanh_toan', MaGiaoDichNH = :bankRef, NgayCapNhat = NOW()
                  WHERE MaDon = :code"
            );
            $update->execute([
                ':bankRef' => $bankRef !== '' ? mb_substr($bankRef, 0, 120) : ('BANK-' . date('YmdHis')),
                ':code' => $code,
            ]);

            // Chỉ tính lượt voucher khi đơn chuyển sang đã thanh toán.
            // Việc đóng hộp thoại QR không gọi markPaid nên không làm mất lượt.
            if (!empty($order['MaVoucher'])) {
                $this->db->prepare(
                    'UPDATE VOUCHER SET SoLuotDaDung = SoLuotDaDung + 1 WHERE MaVoucher = :id'
                )->execute([':id' => (int) $order['MaVoucher']]);
            }
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }

        $this->awardCommissions($code);
        return $this->getOrder($code) ?? [];
    }

    private function awardCommissions(string $code): void
    {
        $order = $this->getOrder($code);
        if (!$order || (int) ($order['HoaHongDaCong'] ?? 0) === 1) {
            return;
        }

        require_once __DIR__ . '/../../../Config/database.php';
        require_once __DIR__ . '/../../Lookbook&Affiliate/Models/AffiliateModel.php';
        $affiliate = new \App\Modules\LookbookAffiliate\Models\AffiliateModel();

        foreach ($order['items'] as $item) {
            $refCode = trim((string) ($item['RefCode'] ?? ''));
            if ($refCode === '') {
                continue;
            }
            try {
                $affiliate->awardCommission(
                    $refCode,
                    (string) ($item['MaVai'] ?? ''),
                    (float) $item['SoMet'],
                    (float) $item['DonGia']
                );
            } catch (Throwable $ignored) {
                // Mã giới thiệu không hợp lệ không được làm hỏng luồng thanh toán.
            }
        }

        $this->db->prepare('UPDATE DON_HANG_ONLINE SET HoaHongDaCong = 1 WHERE MaDon = :code')
            ->execute([':code' => $code]);
    }

    private function normalizeItems(array $items): array
    {
        $result = [];
        foreach ($items as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $soMet = round((float) ($raw['soMet'] ?? 0), 2);
            $donGia = round((float) ($raw['donGia'] ?? 0), 2);
            if ($soMet < 0.5 || $donGia <= 0) {
                continue;
            }

            $threshold = (float) ($raw['wholesaleThreshold'] ?? 15);
            $discount = (float) ($raw['wholesaleDiscount'] ?? 0.10);
            if ($threshold <= 0) {
                $threshold = 15;
            }
            $discount = max(0, min(0.6, $discount));

            $rawTotal = $soMet * $donGia;
            $thanhTien = $soMet >= $threshold ? $rawTotal * (1 - $discount) : $rawTotal;
            $thanhTien = round($thanhTien, 2);

            $result[] = [
                'maVai' => mb_substr(trim((string) ($raw['maVai'] ?? '')), 0, 60),
                'tenSP' => mb_substr(trim((string) ($raw['tenSP'] ?? 'Vải Aura Fabrics')), 0, 255),
                'soMet' => $soMet,
                'donGia' => $donGia,
                'chietKhau' => round($rawTotal - $thanhTien, 2),
                'thanhTien' => $thanhTien,
                'refCode' => mb_substr(trim((string) ($raw['refCode'] ?? '')), 0, 60),
                'hinhAnh' => mb_substr(trim((string) ($raw['img'] ?? $raw['hinhAnh'] ?? '')), 0, 2000),
                'dinhLuong' => mb_substr(trim((string) ($raw['dinhLuong'] ?? '')), 0, 120),
                'mauSac' => mb_substr(trim((string) ($raw['mauSac'] ?? '')), 0, 120),
                'khoVai' => mb_substr(trim((string) ($raw['khoVai'] ?? $raw['chatLieuSize'] ?? '')), 0, 120),
            ];
        }

        if (!$result) {
            throw new InvalidArgumentException('Giỏ hàng không có sản phẩm hợp lệ (số mét tối thiểu 0.5m).');
        }
        if (count($result) > 50) {
            throw new InvalidArgumentException('Một đơn hàng tối đa 50 dòng sản phẩm.');
        }
        return $result;
    }

    private function generateCode(): string
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $code = 'AUR';
            for ($i = 0; $i < 6; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
            $stmt = $this->db->prepare('SELECT 1 FROM DON_HANG_ONLINE WHERE MaDon = :code LIMIT 1');
            $stmt->execute([':code' => $code]);
            if (!$stmt->fetchColumn()) {
                return $code;
            }
        }
        return 'AUR' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
