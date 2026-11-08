<?php
declare(strict_types=1);

// 0. Chuyển tiếp các API request (/api/...) tới backend public router
$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
$path = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '');
$queryUrl = (string) ($_GET['url'] ?? '');
if (str_starts_with($path, '/api/') || str_starts_with($queryUrl, 'api/')) {
    require __DIR__ . '/backend/public/index.php';
    exit;
}

$module = (string) ($_GET['module'] ?? 'admin');

// 1. Phân hệ Cửa hàng & Lookbook / Cổng Thợ May
if ($module === 'lookbook') {
    require_once __DIR__ . '/backend/src/Modules/Lookbook&Affiliate/routes.php';
    dispatchLookbookAffiliateModule();
    exit;
}

// 2. Phân hệ Tài khoản: Đăng nhập & Đăng ký (auth)
if ($module === 'auth' || $module === 'login') {
    $_GET['view'] = 'auth';
    require __DIR__ . '/frontend/src/modules/Tai_khoan/pages/index.php';
    exit;
}

// 3. Phân hệ Tài khoản: Hồ sơ cá nhân (dashboard)
if ($module === 'dashboard' || $module === 'profile') {
    $_GET['view'] = 'dashboard';
    require __DIR__ . '/frontend/src/modules/Tai_khoan/pages/index.php';
    exit;
}

// 4. Phân hệ Tài khoản chung
if ($module === 'tai-khoan' || $module === 'tai_khoan') {
    require __DIR__ . '/frontend/src/modules/Tai_khoan/pages/index.php';
    exit;
}

// 5. Phân hệ Đơn hàng của tôi
if ($module === 'don-hang' || $module === 'don_hang' || $module === 'orders') {
    require __DIR__ . '/frontend/src/modules/Don_hang/pages/index.php';
    exit;
}

// 6. Phân hệ Giỏ hàng & Thanh toán
if ($module === 'cart' || $module === 'gio-hang' || $module === 'gio_hang') {
    require __DIR__ . '/frontend/src/modules/Gio_hang&Thanh_toan/pages/cart.php';
    exit;
}
if ($module === 'checkout' || $module === 'thanh-toan' || $module === 'thanh_toan') {
    require __DIR__ . '/frontend/src/modules/Gio_hang&Thanh_toan/pages/checkout.php';
    exit;
}
if ($module === 'success' || $module === 'thanh-cong' || $module === 'dat-hang-thanh-cong') {
    require __DIR__ . '/frontend/src/modules/Gio_hang&Thanh_toan/pages/success.php';
    exit;
}

// 7. Mặc định: Trung tâm Quản trị Admin
require_once __DIR__ . '/backend/src/Modules/Admin/routes.php';
dispatchAdminModuleRoutes();
