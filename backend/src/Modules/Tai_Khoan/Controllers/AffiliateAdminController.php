<?php

namespace App\Modules\Tai_Khoan\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Modules\Tai_Khoan\Models\AffiliateRequestModel;
use App\Modules\Tai_Khoan\Models\KhachHangModel;
use App\Core\Database;

class AffiliateAdminController
{
    private AffiliateRequestModel $affModel;
    private KhachHangModel $khModel;

    public function __construct()
    {
        $this->affModel = new AffiliateRequestModel();
        $this->khModel  = new KhachHangModel();
    }

    /** GET /api/admin/affiliate-requests */
    public function listRequests(): void
    {
        $trangThai = $_GET['trangThai'] ?? 'Chờ duyệt';
        $list = $this->affModel->findAll($trangThai);
        Response::success($list, 'Danh sách đăng ký affiliate');
    }

    /** POST /api/admin/affiliate-requests/approve */
    public function approve(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID không hợp lệ.', 400);
            return;
        }

        $aff = $this->affModel->findById($id);
        if (!$aff) {
            Response::error('Không tìm thấy đăng ký.', 404);
            return;
        }
        if ($aff['TrangThai'] !== 'Chờ duyệt') {
            Response::error('Đăng ký này đã được xử lý rồi.', 400);
            return;
        }

        // Tạo tài khoản KHACH_HANG với LoaiKhach = 'Affiliate'
        $db = Database::getConnection();
        $stmt = $db->prepare(
            "INSERT INTO KHACH_HANG (TenKH, SDT, LoaiKhach, TongNo, Email, TenDangNhap, MatKhauHash, TrangThai)
             VALUES (?, ?, 'Affiliate', 0, ?, ?, ?, 'Hoạt động')"
        );
        $stmt->execute([
            $aff['TenKH'],
            $aff['SDT'],
            $aff['Email'],
            $aff['TenDangNhap'],
            $aff['MatKhauHash'],
        ]);

        $this->affModel->approve($id);

        // Gửi email thông báo chúc mừng tới Thợ May
        try {
            $mailService = new \App\Services\MailService();
            $mailService->sendAffiliateApprovedEmail($aff['Email'], $aff['TenKH']);
        } catch (\Throwable $e) {}

        Response::success(['MaKH' => (int)$db->lastInsertId()], 'Đã duyệt, tạo tài khoản và gửi email thông báo cho thợ may thành công.');
    }

    /** POST /api/admin/affiliate-requests/reject */
    public function reject(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $body = Request::body();
        $ghiChu = $body['ghiChu'] ?? '';

        if ($id <= 0) {
            Response::error('ID không hợp lệ.', 400);
            return;
        }

        $aff = $this->affModel->findById($id);
        if (!$aff) {
            Response::error('Không tìm thấy đăng ký.', 404);
            return;
        }

        $this->affModel->reject($id, $ghiChu);

        // Gửi email thông báo từ chối
        try {
            $mailService = new \App\Services\MailService();
            $mailService->sendAffiliateRejectedEmail($aff['Email'], $aff['TenKH'], (string)$ghiChu);
        } catch (\Throwable $e) {}

        Response::success(null, 'Đã từ chối đơn đăng ký và gửi email thông báo.');
    }
}
