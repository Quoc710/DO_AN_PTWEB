<?php

namespace App\Modules\Don_hang\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Modules\Don_hang\Services\DonHangService;
use Exception;

class DonHangController
{
    private DonHangService $service;

    public function __construct()
    {
        $this->service = new DonHangService();
    }

    /**
     * API lấy danh sách phiếu bán (đơn hàng) từ DB
     * GET /api/don-hang?q=...&date=...
     */
    public function index(): void
    {
        try {
            $keyword = Request::query('q', null);
            $date = Request::query('date', null);
            $status = Request::query('status', null);

            $orders = $this->service->getOrders($keyword, $date, $status);
            $stats = $this->service->getOrderStatistics();

            Response::success([
                'orders' => $orders,
                'stats' => $stats,
                'total' => count($orders)
            ], 'Lấy danh sách phiếu bán thành công');
        } catch (Exception $e) {
            Response::error('Lỗi truy xuất cơ sở dữ liệu: ' . $e->getMessage(), 500);
        }
    }
}
