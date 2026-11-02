<?php
declare(strict_types=1);

/**
 * Định tuyến (Routes) cho Module 4: Phân hệ Điều hành & Quản trị Vải - Đồng bộ Odoo (Admin)
 * Vị trí: backend/src/Modules/Admin/routes.php
 */

require_once __DIR__ . '/Controllers/ProductController.php';

/**
 * Điều phối HTTP Request tới phương thức tương ứng của ProductController
 */
function dispatchAdminModuleRoutes(): void
{
    date_default_timezone_set('Asia/Ho_Chi_Minh');

    try {
        $controller = new ProductController();

        // Hỗ trợ lấy action qua query string (?action=...) hoặc qua đường dẫn tĩnh từ .htaccess (?url=...)
        $action = trim((string) ($_GET['action'] ?? $_POST['action'] ?? ''));

        if ($action === '' && !empty($_GET['url'])) {
            $segments = explode('/', trim((string) $_GET['url'], '/'));
            $action = $segments[0] ?? 'index';
        }

        if ($action === '') {
            $action = 'index';
        }

        // Ánh xạ các định danh route (cả camelCase và kebab-case) tới phương thức của ProductController
        $routeMap = [
            'index'           => 'index',
            'dashboard'       => 'index',
            'storeFabric'     => 'storeFabric',
            'store-fabric'    => 'storeFabric',
            'updateFabric'    => 'updateFabric',
            'update-fabric'   => 'updateFabric',
            'deleteFabric'    => 'deleteFabric',
            'delete-fabric'   => 'deleteFabric',
            'exportOdoo'      => 'exportOdoo',
            'export-odoo'     => 'exportOdoo',
            'storeVoucher'    => 'storeVoucher',
            'store-voucher'   => 'storeVoucher',
            'moderateReview'  => 'moderateReview',
            'moderate-review' => 'moderateReview'
        ];

        $method = $routeMap[$action] ?? 'index';

        if (!method_exists($controller, $method)) {
            throw new BadMethodCallException("Action '{$action}' không tồn tại trong ProductController.");
        }

        $controller->{$method}();
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<h3>Lỗi Điều Hướng Hệ Thống (Admin Module Router Error):</h3>';
        echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    }
}

return [
    'module'     => 'Admin',
    'controller' => ProductController::class,
    'routes'     => [
        'GET /'                       => [ProductController::class, 'index'],
        'POST ?action=storeFabric'    => [ProductController::class, 'storeFabric'],
        'POST ?action=updateFabric'   => [ProductController::class, 'updateFabric'],
        'GET|POST ?action=deleteFabric' => [ProductController::class, 'deleteFabric'],
        'GET ?action=exportOdoo'      => [ProductController::class, 'exportOdoo'],
        'POST ?action=storeVoucher'   => [ProductController::class, 'storeVoucher'],
        'GET|POST ?action=moderateReview' => [ProductController::class, 'moderateReview'],
    ]
];
