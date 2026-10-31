<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use App\Config\Env;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Middleware\CorsMiddleware;

// 1. Nạp biến môi trường
Env::load(__DIR__ . '/../.env');

// 2. Thiết lập CORS
CorsMiddleware::handle();

// 3. Khởi động Session
Session::init();

// 4. Khởi tạo Router
$router = new Router();

// Route kiểm tra máy chủ
$router->get('/', function () {
    Response::json([
        'status' => 'ok',
        'message' => 'Backend Cửa hàng vải đang hoạt động.',
        'time' => date('Y-m-d H:i:s'),
    ]);
});

$router->get('/api/status', function () {
    Response::json([
        'status' => 'ok',
        'app' => 'QuanLyCuaHangVai',
        'env' => Env::get('APP_ENV', 'local'),
    ]);
});

// 5. Nạp routes của các Modules
$moduleRoutes = [
    __DIR__ . '/../src/Modules/Tai_Khoan/routes.php',
    __DIR__ . '/../src/Modules/Admin/routes.php',
    __DIR__ . '/../src/Modules/Storefront/routes.php',
    __DIR__ . '/../src/Modules/Gio_hang&Thanh_toan/routes.php',
    __DIR__ . '/../src/Modules/Lookbook&Affiliate/routes.php',
    __DIR__ . '/../src/Modules/Don_hang/routes.php',
];

foreach ($moduleRoutes as $routeFile) {
    if (file_exists($routeFile)) {
        require_once $routeFile;
    }
}

// 6. Điều hướng Request
try {
    $router->dispatch();
} catch (\Throwable $e) {
    Response::error('Lỗi máy chủ nội bộ: ' . $e->getMessage(), 500);
}
