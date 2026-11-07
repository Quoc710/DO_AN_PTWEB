<?php
declare(strict_types=1);

/**
 * Module Tài Khoản - Giao diện Đăng nhập / Đăng ký & Hồ sơ cá nhân
 * Vị trí: frontend/src/modules/Tai_khoan/pages/index.php
 */
$view = (string) ($_GET['view'] ?? $_GET['action'] ?? '');

if ($view === 'dashboard' || $view === 'profile') {
    require __DIR__ . '/dashboard.html';
    exit;
}

// Mặc định hoặc view=auth: trang Đăng nhập & Đăng ký
require __DIR__ . '/auth.html';

