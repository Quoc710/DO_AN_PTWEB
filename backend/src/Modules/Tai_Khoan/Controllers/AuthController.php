<?php

namespace App\Modules\Tai_Khoan\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Middleware\AuthMiddleware;
use App\Modules\Tai_Khoan\Services\AuthService;
use Exception;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    private function attachToken(array $user): array
    {
        $payload = [
            'MaTK' => $user['MaTK'],
            'TenTK' => $user['TenTK'],
            'time' => time(),
        ];
        $user['token'] = base64_encode(json_encode($payload));
        return $user;
    }

    public function login(): void
    {
        $body = Request::body();
        $username = $body['username'] ?? $body['email'] ?? $body['TenTK'] ?? '';
        $password = $body['password'] ?? $body['matKhau'] ?? '';

        try {
            $user = $this->authService->login((string)$username, (string)$password);
            $userWithToken = $this->attachToken($user);
            Response::success($userWithToken, 'Đăng nhập thành công');
        } catch (Exception $e) {
            Response::error($e->getMessage(), 401);
        }
    }

    public function register(): void
    {
        $body = Request::body();
        $fullname = $body['fullname'] ?? $body['TenNV'] ?? '';
        $username = $body['username'] ?? $body['email'] ?? $body['TenTK'] ?? '';
        $email = $body['email'] ?? (filter_var($username, FILTER_VALIDATE_EMAIL) ? $username : null);
        $password = $body['password'] ?? $body['matKhau'] ?? '';
        $viTri = $body['viTri'] ?? $body['position'] ?? 'Nhân viên bán hàng';

        try {
            $result = $this->authService->register(
                (string)$fullname,
                (string)$username,
                (string)$password,
                (string)$viTri,
                $email ? (string)$email : null
            );
            Response::success($result, $result['message'], 201);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function resendActivation(): void
    {
        $body = Request::body();
        $username = $body['username'] ?? $body['email'] ?? '';

        try {
            $result = $this->authService->resendActivation((string)$username);
            Response::success($result, $result['message']);
        } catch (Exception $e) {
            Response::error($e->getMessage(), 400);
        }
    }

    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';

        try {
            $account = $this->authService->verifyEmail((string)$token);

            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                Response::success($account, 'Tài khoản đã được kích hoạt thành công!');
                return;
            }

            $alreadyActivated = !empty($account['already_activated']);
            $this->renderVerificationSuccessPage($account['TenNV'], $account['TenTK'], $alreadyActivated);
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                Response::error($e->getMessage(), 400);
                return;
            }

            $this->renderVerificationErrorPage($e->getMessage());
        }
    }

    private function renderVerificationSuccessPage(string $tenNV, string $tenTK, bool $alreadyActivated = false): void
    {
        $safeName = htmlspecialchars($tenNV, ENT_QUOTES, 'UTF-8');
        $safeUser = htmlspecialchars($tenTK, ENT_QUOTES, 'UTF-8');

        $title = $alreadyActivated ? 'Tài khoản đã kích hoạt' : 'Kích hoạt thành công!';
        $desc = $alreadyActivated 
            ? "Tài khoản <span class=\"font-mono text-xs bg-[#f5f2ff] px-2 py-1 rounded\">@{$safeUser}</span> của bạn đã được kích hoạt trước đó và sẵn sàng để đăng nhập."
            : "Xin chào <strong class=\"text-black\">{$safeName}</strong>!<br>Tài khoản <span class=\"font-mono text-xs bg-[#f5f2ff] px-2 py-1 rounded\">@{$safeUser}</span> của bạn đã được kích hoạt thành công và sẵn sàng để đăng nhập.";

        $loginUrl = '/frontend/src/modules/Tai_khoan/pages/auth.html?verified=1';
        echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{$title} - AURA MINIMAL</title>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fbf8ff] font-['Be_Vietnam_Pro'] text-[#1a1a28] min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded-2xl p-8 shadow-sm border border-[#e2e1f4] text-center flex flex-col items-center">
    <div class="w-16 h-16 rounded-full bg-green-50 border border-green-200 flex items-center justify-center mb-5 text-green-600">
      <span class="material-symbols-outlined text-3xl">check_circle</span>
    </div>
    <div class="flex items-center gap-2 mb-3">
      <span class="w-2 h-2 rounded-full bg-black inline-block"></span>
      <span class="text-xs uppercase tracking-[0.2em] font-semibold">AURA MINIMAL</span>
    </div>
    <h1 class="text-2xl font-bold mb-2">{$title}</h1>
    <p class="text-sm text-[#5b5e6b] mb-6 leading-relaxed">
      {$desc}
    </p>
    <a href="{$loginUrl}" onclick="return goBackToLogin()" class="w-full py-3 px-4 bg-black text-white font-medium text-sm rounded-lg flex items-center justify-center gap-2 hover:bg-[#2f2f3e] transition-colors">
      <span>Đến màn hình Đăng nhập</span>
      <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
    </a>
  </div>
  <script>
    function goBackToLogin() {
      if (window.opener && !window.opener.closed) {
        try {
          window.opener.location.href = '/frontend/src/modules/Tai_khoan/pages/auth.html?verified=1';
          window.close();
          return false;
        } catch (e) {}
      }
      window.location.href = '/frontend/src/modules/Tai_khoan/pages/auth.html?verified=1';
      return false;
    }
  </script>
</body>
</html>
HTML;
        exit;
    }

    private function renderVerificationErrorPage(string $errorMessage): void
    {
        $safeMsg = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
        $loginUrl = '/frontend/src/modules/Tai_khoan/pages/auth.html';
        echo <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lỗi kích hoạt tài khoản - AURA MINIMAL</title>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#fbf8ff] font-['Be_Vietnam_Pro'] text-[#1a1a28] min-h-screen flex items-center justify-center p-4">
  <div class="w-full max-w-md bg-white rounded-2xl p-8 shadow-sm border border-[#e2e1f4] text-center flex flex-col items-center">
    <div class="w-16 h-16 rounded-full bg-red-50 border border-red-200 flex items-center justify-center mb-5 text-red-600">
      <span class="material-symbols-outlined text-3xl">error</span>
    </div>
    <h1 class="text-xl font-bold mb-2">Không thể kích hoạt tài khoản</h1>
    <p class="text-sm text-red-600 bg-red-50 p-3 rounded-lg w-full mb-6 leading-relaxed">
      {$safeMsg}
    </p>
    <a href="{$loginUrl}" class="w-full py-3 px-4 bg-black text-white font-medium text-sm rounded-lg flex items-center justify-center gap-2 hover:bg-[#2f2f3e] transition-colors">
      <span>Đến màn hình Đăng nhập</span>
    </a>
  </div>
</body>
</html>
HTML;
        exit;
    }

    public function me(): void
    {
        $user = AuthMiddleware::check();
        $freshUser = $this->authService->getCurrentUser() ?? $user;
        $userWithToken = $this->attachToken($freshUser);
        Response::success($userWithToken, 'Thông tin tài khoản hiện tại');
    }

    public function logout(): void
    {
        $this->authService->logout();
        Response::success(null, 'Đăng xuất thành công');
    }
}
