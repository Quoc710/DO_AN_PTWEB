<?php
declare(strict_types=1);

/**
 * Điểm vào của Backend (backend/public/index.php)
 * Điều hướng tới Module Admin (Phân hệ Điều hành & Quản trị Vải - Đồng bộ Odoo)
 */
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

require_once __DIR__ . '/../src/Modules/Admin/routes.php';

dispatchAdminModuleRoutes();
