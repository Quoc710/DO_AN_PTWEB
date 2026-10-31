<?php

namespace App\Modules\Tai_Khoan\Services;

use App\Core\Session;
use App\Modules\Tai_Khoan\Models\TaiKhoanModel;
use Exception;

class AuthService
{
    private TaiKhoanModel $model;

    public function __construct()
    {
        $this->model = new TaiKhoanModel();
    }

    public function login(string $username, string $password): array
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            throw new Exception('Vui lòng nhập đầy đủ tên tài khoản và mật khẩu.');
        }

        $user = $this->model->findByUsername($username);
        if (!$user) {
            throw new Exception('Tên tài khoản hoặc mật khẩu không chính xác.');
        }

        if (!password_verify($password, $user['MatKhauHash'])) {
            throw new Exception('Tên tài khoản hoặc mật khẩu không chính xác.');
        }

        // Kiểm tra trạng thái kích hoạt qua Email
        if (($user['TrangThai'] ?? '') === 'Chưa kích hoạt') {
            throw new Exception('Tài khoản chưa được kích hoạt. Vui lòng kiểm tra email của bạn để kích hoạt tài khoản trước khi đăng nhập.');
        }

        unset($user['MatKhauHash']);

        Session::set('user', $user);

        return $user;
    }

    public function register(string $fullname, string $username, string $password, string $viTri = 'Nhân viên bán hàng', ?string $email = null): array
    {
        $fullname = trim($fullname);
        $username = trim($username);

        if ($fullname === '') {
            throw new Exception('Vui lòng nhập họ và tên nhân viên.');
        }

        if (mb_strlen($username) < 3) {
            throw new Exception('Tên tài khoản phải có ít nhất 3 ký tự.');
        }

        if (strlen($password) < 8) {
            throw new Exception('Mật khẩu phải có ít nhất 8 ký tự.');
        }

        // Xác định email
        $userEmail = $email ? trim($email) : (filter_var($username, FILTER_VALIDATE_EMAIL) ? $username : $username . '@auraminimal.vn');

        $existing = $this->model->findByUsername($username);
        if ($existing) {
            if (($existing['TrangThai'] ?? '') === 'Chưa kích hoạt') {
                $tokenKichHoat = bin2hex(random_bytes(32));
                $hanKichHoat = date('Y-m-d H:i:s', strtotime('+24 hours'));
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);
                $this->model->updateActivationToken((int)$existing['MaTK'], $tokenKichHoat, $hanKichHoat, $passwordHash);

                $mailService = new \App\Services\MailService();
                $displayName = $fullname ?: ($existing['TenNV'] ?? $username);
                $mailResult = $mailService->sendActivationEmail($userEmail, $displayName, $tokenKichHoat);

                return [
                    'MaTK' => $existing['MaTK'],
                    'TenTK' => $existing['TenTK'],
                    'TenNV' => $displayName,
                    'Email' => $userEmail,
                    'TrangThai' => 'Chưa kích hoạt',
                    'activation_link' => $mailResult['link'] ?? null,
                    'sent_real' => $mailResult['sent_real'] ?? false,
                    'is_resend' => true,
                    'message' => 'Tài khoản này đã đăng ký nhưng chưa kích hoạt. Hệ thống đã gửi lại email kích hoạt mới.',
                ];
            }

            throw new Exception("Tên tài khoản hoặc email '$username' đã tồn tại trong hệ thống.");
        }

        // Tạo token kích hoạt và hạn 24 giờ
        $tokenKichHoat = bin2hex(random_bytes(32));
        $hanKichHoat = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $createdUser = $this->model->createStaffAccount(
            $fullname,
            $username,
            $passwordHash,
            $userEmail,
            $tokenKichHoat,
            $hanKichHoat,
            $viTri
        );

        // Gửi email xác thực kèm link kích hoạt
        $mailService = new \App\Services\MailService();
        $mailResult = $mailService->sendActivationEmail($userEmail, $fullname, $tokenKichHoat);

        return [
            'MaTK' => $createdUser['MaTK'],
            'TenTK' => $createdUser['TenTK'],
            'TenNV' => $createdUser['TenNV'],
            'Email' => $userEmail,
            'TrangThai' => 'Chưa kích hoạt',
            'activation_link' => $mailResult['link'] ?? null,
            'sent_real' => $mailResult['sent_real'] ?? false,
            'message' => 'Đăng ký thành công! Vui lòng kiểm tra hộp thư email để kích hoạt tài khoản.',
        ];
    }

    public function resendActivation(string $identifier): array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            throw new Exception('Vui lòng nhập tài khoản hoặc email cần gửi lại mã kích hoạt.');
        }

        $user = $this->model->findByUsername($identifier);
        if (!$user) {
            throw new Exception("Không tìm thấy tài khoản tương ứng với '$identifier'.");
        }

        if (($user['TrangThai'] ?? '') === 'Đã kích hoạt') {
            throw new Exception('Tài khoản này đã được kích hoạt rồi. Bạn có thể đăng nhập ngay.');
        }

        $tokenKichHoat = bin2hex(random_bytes(32));
        $hanKichHoat = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $this->model->updateActivationToken((int)$user['MaTK'], $tokenKichHoat, $hanKichHoat);

        $toEmail = $user['Email'] ?: $user['TenTK'];
        $toName = $user['TenNV'] ?: $user['TenTK'];

        $mailService = new \App\Services\MailService();
        $mailResult = $mailService->sendActivationEmail($toEmail, $toName, $tokenKichHoat);

        return [
            'sent_real' => $mailResult['sent_real'] ?? false,
            'activation_link' => $mailResult['link'] ?? null,
            'email' => $toEmail,
            'message' => 'Hệ thống đã gửi lại email kích hoạt tới ' . $toEmail,
        ];
    }

    public function verifyEmail(string $token): array
    {
        $token = trim($token);
        if ($token === '') {
            throw new Exception('Mã kích hoạt không hợp lệ.');
        }

        $account = $this->model->findByActivationToken($token);
        if (!$account) {
            throw new Exception('Liên kết kích hoạt không tồn tại hoặc đã hết hiệu lực.');
        }

        if (($account['TrangThai'] ?? '') === 'Đã kích hoạt') {
            return [
                'MaTK' => $account['MaTK'],
                'TenTK' => $account['TenTK'],
                'TenNV' => $account['TenNV'],
                'Email' => $account['Email'],
                'TrangThai' => 'Đã kích hoạt',
                'already_activated' => true,
            ];
        }

        if (!empty($account['HanKichHoat']) && strtotime($account['HanKichHoat']) < time()) {
            throw new Exception('Liên kết kích hoạt đã hết hạn (24 giờ). Vui lòng gửi lại email kích hoạt.');
        }

        $this->model->activateAccount((int)$account['MaTK']);

        return [
            'MaTK' => $account['MaTK'],
            'TenTK' => $account['TenTK'],
            'TenNV' => $account['TenNV'],
            'Email' => $account['Email'],
            'TrangThai' => 'Đã kích hoạt',
        ];
    }

    public function logout(): void
    {
        Session::destroy();
    }

    public function getCurrentUser(): ?array
    {
        $sessionUser = Session::get('user');
        if (!$sessionUser) {
            return null;
        }

        // Lấy lại dữ liệu mới nhất từ CSDL
        $freshUser = $this->model->findById((int)$sessionUser['MaTK']);
        if ($freshUser) {
            Session::set('user', $freshUser);
            return $freshUser;
        }

        return $sessionUser;
    }
}
