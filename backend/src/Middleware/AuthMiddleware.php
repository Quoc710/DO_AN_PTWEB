<?php

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Modules\Tai_Khoan\Models\TaiKhoanModel;
use App\Modules\Tai_Khoan\Models\KhachHangModel;

class AuthMiddleware
{
    public static function check(): array
    {
        // 1. Kiểm tra PHP Session
        $user = Session::get('user');
        if ($user) {
            return $user;
        }

        // 2. Fallback: Kiểm tra Authorization header (Bearer token)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = $matches[1];
            $decoded = json_decode(base64_decode($token), true);

            // 2a. Token của Nhân viên/Admin (có MaTK)
            if (isset($decoded['MaTK'])) {
                $model = new TaiKhoanModel();
                $freshUser = $model->findById((int)$decoded['MaTK']);
                if ($freshUser) {
                    Session::set('user', $freshUser);
                    return $freshUser;
                }
            }

            // 2b. Token của Khách hàng / Affiliate (có MaKH)
            if (isset($decoded['MaKH'])) {
                $khModel = new KhachHangModel();
                $freshUser = $khModel->findById((int)$decoded['MaKH']);
                if ($freshUser) {
                    // Xác định loại TK cho session
                    $loaiTK = (($freshUser['LoaiKhach'] ?? '') === 'Affiliate') ? 'affiliate' : 'khach_hang';
                    $freshUser['LoaiTK'] = $loaiTK;
                    unset($freshUser['MatKhauHash']);
                    Session::set('user', $freshUser);
                    return $freshUser;
                }
            }
        }

        Response::error('Bạn chưa đăng nhập hoặc phiên làm việc đã hết hạn.', 401);
    }
}
