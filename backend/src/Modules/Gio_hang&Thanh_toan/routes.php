<?php

use App\Core\Router;
use App\Modules\CartCheckout\Controllers\CheckoutController;

/**
 * Routes module Giỏ hàng & Thanh toán.
 * Controller đặt tại backend/src/Modules/CartCheckout (namespace hợp lệ, không chứa '&').
 *
 * @var Router $router
 */
$router->post('/api/gio-hang/dat-hang', [CheckoutController::class, 'create']);
$router->get('/api/gio-hang/don-hang/{code}', [CheckoutController::class, 'show']);
$router->post('/api/gio-hang/don-hang/{code}/xac-nhan', [CheckoutController::class, 'confirm']);
$router->post('/api/gio-hang/webhook/chuyen-khoan', [CheckoutController::class, 'webhook']);
$router->get('/api/gio-hang/cau-hinh', [CheckoutController::class, 'config']);
$router->get('/api/gio-hang/don-hang-cua-toi', [CheckoutController::class, 'myOrders']);
$router->post('/api/gio-hang/don-hang/{code}/khach-xac-nhan-giao', [CheckoutController::class, 'customerConfirmDelivery']);
$router->post('/api/gio-hang/don-hang/{code}/khach-huy', [CheckoutController::class, 'customerCancelDelivery']);
$router->get('/api/admin/don-hang-online', [CheckoutController::class, 'adminOrders']);
$router->post('/api/admin/don-hang-online/{code}/bat-dau-giao', [CheckoutController::class, 'adminStartDelivery']);
$router->post('/api/admin/don-hang-online/{code}/xac-nhan-giao', [CheckoutController::class, 'adminConfirmDelivery']);
$router->get('/api/gio-hang/voucher', [CheckoutController::class, 'vouchers']);
$router->post('/api/gio-hang/voucher/kiem-tra', [CheckoutController::class, 'checkVoucher']);
