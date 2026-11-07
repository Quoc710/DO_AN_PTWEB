<?php

use App\Core\Router;
use App\Modules\Tai_Khoan\Controllers\AuthController;
use App\Modules\Tai_Khoan\Controllers\AffiliateAdminController;

/**
 * Khai báo các routes của module Tài Khoản
 * @var Router $router
 */
$router->post('/api/tai-khoan/login', [AuthController::class, 'login']);
$router->post('/api/tai-khoan/register', [AuthController::class, 'register']);
$router->post('/api/tai-khoan/resend-activation', [AuthController::class, 'resendActivation']);
$router->get('/api/tai-khoan/verify-email', [AuthController::class, 'verifyEmail']);
$router->get('/api/tai-khoan/me', [AuthController::class, 'me']);
$router->post('/api/tai-khoan/logout', [AuthController::class, 'logout']);

// Khách hàng & Affiliate
$router->post('/api/tai-khoan/register/khach-hang', [AuthController::class, 'registerKhachHang']);
$router->post('/api/tai-khoan/register/affiliate', [AuthController::class, 'registerAffiliate']);
$router->post('/api/tai-khoan/login/khach-hang', [AuthController::class, 'loginKhachHang']);

// Admin duyệt Affiliate
$router->get('/api/admin/affiliate-requests', [AffiliateAdminController::class, 'listRequests']);
$router->post('/api/admin/affiliate-requests/approve', [AffiliateAdminController::class, 'approve']);
$router->post('/api/admin/affiliate-requests/reject', [AffiliateAdminController::class, 'reject']);




