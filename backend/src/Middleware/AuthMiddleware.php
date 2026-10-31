<?php

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Modules\Tai_Khoan\Models\TaiKhoanModel;

class AuthMiddleware
{
    public static function check(): array
    {
        // 1. Kiểm tra PHP Session
        $user = Session::get('user');
        if ($user) {
            return $user;
        }

        // 2. Fallback: Kiểm tra Authorization header (hỗ trợ cross-origin / Live Server / file://)
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            $token = $matches[1];
            $decoded = json_decode(base64_decode($token), true);
            if (isset($decoded['MaTK'])) {
                $model = new TaiKhoanModel();
                $freshUser = $model->findById((int)$decoded['MaTK']);
                if ($freshUser) {
                    Session::set('user', $freshUser);
                    return $freshUser;
                }
            }
        }

        Response::error('Bạn chưa đăng nhập hoặc phiên làm việc đã hết hạn.', 401);
    }
}
