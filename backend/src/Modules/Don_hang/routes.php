<?php

use App\Core\Router;
use App\Modules\Don_hang\Controllers\DonHangController;

/**
 * Khai báo các routes của module Đơn Hàng (Don_hang)
 * @var Router $router
 */
$router->get('/api/don-hang', [DonHangController::class, 'index']);
$router->get('/api/tai-khoan/don-hang', [DonHangController::class, 'index']);

