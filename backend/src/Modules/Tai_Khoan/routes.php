<?php

use App\Core\Router;
use App\Modules\Tai_Khoan\Controllers\AuthController;

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



