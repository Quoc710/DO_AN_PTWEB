<?php
declare(strict_types=1);

/**
 * Cổng vào chính (Root Entry Point) của dự án DO_AN_PTWEB
 * Chuyển tiếp điều hướng vào Backend Module Admin (backend/src/Modules/Admin/routes.php)
 */
require_once __DIR__ . '/backend/src/Modules/Admin/routes.php';

dispatchAdminModuleRoutes();
