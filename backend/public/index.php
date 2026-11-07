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

// Hàm điều hướng chung cho trang chủ hoặc các modules giao diện
$handleHomeOrLookbook = function () {
    $module = (string) ($_GET['module'] ?? '');
    if ($module === 'lookbook') {
        require_once __DIR__ . '/../src/Modules/Lookbook&Affiliate/routes.php';
        dispatchLookbookAffiliateModule();
        exit;
    }

    if ($module === 'auth' || $module === 'login') {
        $_GET['view'] = 'auth';
        require __DIR__ . '/../../frontend/src/modules/Tai_khoan/pages/index.php';
        exit;
    }

    if ($module === 'dashboard' || $module === 'profile') {
        $_GET['view'] = 'dashboard';
        require __DIR__ . '/../../frontend/src/modules/Tai_khoan/pages/index.php';
        exit;
    }

    if ($module === 'tai-khoan' || $module === 'tai_khoan') {
        require __DIR__ . '/../../frontend/src/modules/Tai_khoan/pages/index.php';
        exit;
    }

    if ($module === 'don-hang' || $module === 'don_hang' || $module === 'orders') {
        require __DIR__ . '/../../frontend/src/modules/Don_hang/pages/index.php';
        exit;
    }

    if ($module === 'admin') {
        if (function_exists('dispatchAdminModuleRoutes')) {
            dispatchAdminModuleRoutes();
        } else {
            require_once __DIR__ . '/../src/Modules/Admin/routes.php';
            dispatchAdminModuleRoutes();
        }
        exit;
    }

    Response::json([
        'status' => 'ok',
        'message' => 'Backend Cửa hàng vải đang hoạt động.',
        'time' => date('Y-m-d H:i:s'),
    ]);
};

// Đăng ký cho cả route '/' và '/index.php'
$router->get('/', $handleHomeOrLookbook);
$router->get('/index.php', $handleHomeOrLookbook);
$router->post('/index.php', $handleHomeOrLookbook);

// Clean URL routes
$router->get('/auth', function () {
    $_GET['view'] = 'auth';
    require __DIR__ . '/../../frontend/src/modules/Tai_khoan/pages/index.php';
    exit;
});
$router->get('/don-hang', function () {
    require __DIR__ . '/../../frontend/src/modules/Don_hang/pages/index.php';
    exit;
});
$router->get('/dashboard', function () {
    $_GET['view'] = 'dashboard';
    require __DIR__ . '/../../frontend/src/modules/Tai_khoan/pages/index.php';
    exit;
});

$router->get('/api/status', function () {
    Response::json([
        'status' => 'ok',
        'app' => 'QuanLyCuaHangVai',
        'env' => Env::get('APP_ENV', 'local'),
    ]);
});

// Route mở trang Quản trị Admin (Module 4)
$router->get('/admin', function () {
    if (function_exists('dispatchAdminModuleRoutes')) {
        dispatchAdminModuleRoutes();
    } else {
        require_once __DIR__ . '/../src/Modules/Admin/routes.php';
        dispatchAdminModuleRoutes();
    }
    exit;
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
