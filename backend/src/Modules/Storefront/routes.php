<?php
// Khai bao route cua module Auth (nguoi phu trach tu viet)
// Vi du: $router->get('/api/auth', [AuthController::class, 'index']);
<?php

use App\Modules\Storefront\Controllers\StorefrontController; // Hoặc namespace tương ứng với cấu trúc project của bạn

// Định nghĩa các Route API cho Storefront
$router->get('/api/v1/storefront/products', [StorefrontController::class, 'getProducts']);
$router->get('/api/v1/storefront/check-review', [StorefrontController::class, 'checkReviewPermission']);
$router->post('/api/v1/storefront/reviews', [StorefrontController::class, 'storeReview']);  
use Illuminate\Support\Facades\DB;

