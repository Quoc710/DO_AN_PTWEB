<?php
declare(strict_types=1);

require_once __DIR__ . '/../Models/ProductModel.php';
require_once __DIR__ . '/../Models/AdminOperationsModel.php';

/**
 * ProductController - Điều phối toàn bộ luồng nghiệp vụ cho Module 4 (Aura Admin Portal)
 * Vị trí: backend/src/Modules/Admin/Controllers/ProductController.php
 */
class ProductController
{
    private ProductModel $productModel;
    private AdminOperationsModel $adminOperationsModel;
    private string $projectRoot;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->projectRoot = dirname(__DIR__, 5);
        $this->productModel = new ProductModel();
        $this->adminOperationsModel = new AdminOperationsModel();
    }

    /**
     * Hiển thị trang Dashboard Trung tâm Điều hành (Module 4)
     * Lấy toàn bộ dữ liệu: 4 thẻ thống kê, đơn hàng Odoo, mẫu vải CRUD, voucher, đánh giá
     */
    public function index(): void
    {
        try {
            $orderFilters = [
                'from_date' => $_GET['from_date'] ?? null,
                'to_date'   => $_GET['to_date'] ?? null,
                'status'    => $_GET['order_status'] ?? 'ready',
                'range'     => $_GET['range'] ?? 'today'
            ];

            $reviewFilter = (string) ($_GET['review_filter'] ?? 'all');

            // Lấy dữ liệu từ ProductModel
            $stats        = $this->productModel->getDashboardStats();
            $orders       = $this->productModel->getOdooOrders($orderFilters);
            $fabrics      = $this->productModel->getAllFabrics();
            $vouchers     = $this->productModel->getAllVouchers();
            $reviews      = $this->productModel->getAllReviews($reviewFilter);
            $reviewCounts = $this->productModel->getReviewCounts();
            $pendingLookbooks = $this->adminOperationsModel->getPendingLookbooks();
            $pendingWithdrawals = $this->adminOperationsModel->getPendingWithdrawals();

            // Nếu đang chọn sửa 1 mẫu vải cụ thể trên giao diện
            $editingFabric = null;
            if (!empty($_GET['edit_id'])) {
                $editingFabric = $this->productModel->getFabricById((int) $_GET['edit_id']);
            }

            // Lấy thông báo Flash từ Session (nếu có)
            $flashSuccess = $_SESSION['flash_success'] ?? null;
            $flashError   = $_SESSION['flash_error'] ?? null;
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);

            // Truyền dữ liệu sang View tại frontend/src/modules/Admin/pages/index.php
            $viewFile = $this->projectRoot . '/frontend/src/modules/Admin/pages/index.php';
            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                require $this->projectRoot . '/code.html';
            }
        } catch (Throwable $e) {
            $this->handleError('Lỗi tải dữ liệu trang quản trị: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý thêm mẫu vải mới (POST ?action=storeFabric)
     */
    public function storeFabric(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Phương thức truy cập không hợp lệ (Yêu cầu POST).');
            }

            $hinhAnhMacro = trim((string) ($_POST['hinh_anh_macro'] ?? 'linen_macroscopic_ivory.jpg'));
            if (!empty($_FILES['macro_file']['name']) && $_FILES['macro_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = $this->projectRoot . '/frontend/public/uploads/fabrics/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $safeFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename((string) $_FILES['macro_file']['name']));
                if (move_uploaded_file((string) $_FILES['macro_file']['tmp_name'], $uploadDir . $safeFileName)) {
                    $hinhAnhMacro = $safeFileName;
                }
            }

            $payload = [
                'ma_sku'         => $_POST['ma_sku'] ?? '',
                'ten_sp'         => $_POST['ten_sp'] ?? '',
                'kho_vai'        => $_POST['kho_vai'] ?? '1m50',
                'dinh_luong_gsm' => $_POST['dinh_luong_gsm'] ?? '220 gsm',
                'mau_sac'        => $_POST['mau_sac'] ?? 'Be Mộc',
                'ma_mau_hex'     => $_POST['ma_mau_hex'] ?? '#e8dec8',
                'don_gia'        => $_POST['don_gia'] ?? 0,
                'so_luong_ton'   => $_POST['so_luong_ton'] ?? 0,
                'trang_thai_web' => $_POST['trang_thai_web'] ?? null,
                'hinh_anh_macro' => $hinhAnhMacro
            ];

            $newId = $this->productModel->createFabric($payload);

            $this->respondSuccess(
                sprintf('Đã thêm mẫu vải mới (SKU: %s, ID: #%d) và đồng bộ lên Web Store!', htmlspecialchars((string) $payload['ma_sku']), $newId),
                '#quan-ly-vai',
                ['ma_bien_the' => $newId]
            );
        } catch (Throwable $e) {
            $this->respondError($e->getMessage(), '#quan-ly-vai');
        }
    }

    /**
     * Xử lý cập nhật mẫu vải (POST ?action=updateFabric)
     */
    public function updateFabric(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Phương thức truy cập không hợp lệ (Yêu cầu POST).');
            }

            $maBienThe = (int) ($_POST['ma_bien_the'] ?? $_GET['id'] ?? 0);
            if ($maBienThe <= 0) {
                throw new InvalidArgumentException('Thiếu mã định danh mẫu vải (MaBienThe) cần cập nhật.');
            }

            $payload = [
                'ma_sku'         => $_POST['ma_sku'] ?? null,
                'ten_sp'         => $_POST['ten_sp'] ?? null,
                'kho_vai'        => $_POST['kho_vai'] ?? null,
                'dinh_luong_gsm' => $_POST['dinh_luong_gsm'] ?? null,
                'mau_sac'        => $_POST['mau_sac'] ?? null,
                'ma_mau_hex'     => $_POST['ma_mau_hex'] ?? null,
                'don_gia'        => $_POST['don_gia'] ?? null,
                'so_luong_ton'   => $_POST['so_luong_ton'] ?? null,
                'trang_thai_web' => $_POST['trang_thai_web'] ?? null,
                'hinh_anh_macro' => $_POST['hinh_anh_macro'] ?? null
            ];

            if (!empty($_FILES['macro_file']['name']) && $_FILES['macro_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = $this->projectRoot . '/frontend/public/uploads/fabrics/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $safeFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename((string) $_FILES['macro_file']['name']));
                if (move_uploaded_file((string) $_FILES['macro_file']['tmp_name'], $uploadDir . $safeFileName)) {
                    $payload['hinh_anh_macro'] = $safeFileName;
                }
            }

            // Loại bỏ các phần tử null để giữ nguyên dữ liệu cũ nếu không truyền lên
            $filteredPayload = array_filter($payload, static fn($val) => $val !== null);

            $this->productModel->updateFabric($maBienThe, $filteredPayload);

            $this->respondSuccess(
                sprintf('Đã cập nhật thông số mẫu vải #%d thành công!', $maBienThe),
                '#quan-ly-vai',
                ['ma_bien_the' => $maBienThe]
            );
        } catch (Throwable $e) {
            $this->respondError($e->getMessage(), '#quan-ly-vai');
        }
    }

    /**
     * Xử lý xóa mẫu vải (POST/GET ?action=deleteFabric)
     */
    public function deleteFabric(): void
    {
        try {
            $maBienThe = (int) ($_POST['ma_bien_the'] ?? $_GET['id'] ?? 0);
            if ($maBienThe <= 0) {
                throw new InvalidArgumentException('Không xác định được mẫu vải cần xóa.');
            }

            $this->productModel->deleteFabric($maBienThe);

            $this->respondSuccess(
                sprintf('Đã xóa mẫu vải #%d khỏi danh mục Web Store.', $maBienThe),
                '#quan-ly-vai',
                ['ma_bien_the' => $maBienThe]
            );
        } catch (Throwable $e) {
            $this->respondError($e->getMessage(), '#quan-ly-vai');
        }
    }

    /**
     * Xuất file .csv (UTF-8 BOM mở trực tiếp bằng Microsoft Excel không lỗi font tiếng Việt)
     * Chuẩn cấu trúc sales.order của Odoo ERP:
     * External ID, Order Reference, Customer, Phone, Order Date, Order Lines/Product SKU, Order Lines/Quantity (m), Total Amount
     */
    /** Approve a pending partner Lookbook post. */
    public function approveLookbook(): void
    {
        $this->processLookbookDecision('Đã duyệt');
    }

    /** Reject a pending partner Lookbook post. */
    public function rejectLookbook(): void
    {
        $this->processLookbookDecision('Từ chối');
    }

    private function processLookbookDecision(string $status): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Thao tác duyệt Lookbook cần dùng POST.');
            }
            $lookbookId = (int) ($_POST['ma_lookbook'] ?? 0);
            $this->adminOperationsModel->updateLookbookStatus($lookbookId, $status);
            $message = $status === 'Đã duyệt'
                ? "Đã duyệt bài Lookbook #{$lookbookId}."
                : "Đã từ chối bài Lookbook #{$lookbookId}.";
            $this->respondSuccess($message, '#admin-overview');
        } catch (Throwable $e) {
            $this->respondError($e->getMessage(), '#admin-overview');
        }
    }

    /** Approve a withdrawal and atomically update the wallet and ledger. */
    public function approveWithdrawal(): void
    {
        $this->processWithdrawalDecision(true);
    }

    /** Reject a withdrawal request. */
    public function rejectWithdrawal(): void
    {
        $this->processWithdrawalDecision(false);
    }

    private function processWithdrawalDecision(bool $approved): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Thao tác xử lý rút tiền cần dùng POST.');
            }
            $requestId = (int) ($_POST['ma_yeu_cau'] ?? 0);
            if ($approved) {
                $this->adminOperationsModel->approveWithdrawal($requestId);
                $message = "Đã duyệt lệnh rút tiền #{$requestId}; số dư ví và lịch sử đã được cập nhật.";
            } else {
                $this->adminOperationsModel->rejectWithdrawal($requestId);
                $message = "Đã từ chối lệnh rút tiền #{$requestId}.";
            }
            $this->respondSuccess($message, '#admin-overview');
        } catch (Throwable $e) {
            $this->respondError($e->getMessage(), '#admin-overview');
        }
    }
    public function exportOdoo(): void
    {
        try {
            $filters = [
                'from_date' => $_GET['from_date'] ?? $_POST['from_date'] ?? null,
                'to_date'   => $_GET['to_date'] ?? $_POST['to_date'] ?? null,
                'status'    => $_GET['order_status'] ?? $_POST['order_status'] ?? 'ready'
            ];

            $exportRows = $this->productModel->getOdooExportData($filters);
            $dateStamp = date('Ymd_His');
            $fileName = "ODOO_EXPORT_{$dateStamp}.csv";

            // Xóa bộ đệm đầu ra nếu có để tránh làm hỏng file CSV
            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'wb');
            if ($output === false) {
                throw new RuntimeException('Không thể khởi tạo luồng xuất file CSV.');
            }

            // Ghi UTF-8 BOM để Microsoft Excel nhận diện đúng bảng mã tiếng Việt
            fwrite($output, "\xEF\xBB\xBF");

            // Ghi dòng tiêu đề chuẩn Odoo ERP (sales.order)
            $headers = [
                'External ID',
                'Order Reference',
                'Customer',
                'Phone',
                'Order Date',
                'Order Lines/Product SKU',
                'Order Lines/Quantity (m)',
                'Total Amount'
            ];
            fputcsv($output, $headers);

            // Ghi từng dòng đơn hàng
            foreach ($exportRows as $row) {
                fputcsv($output, [
                    $row['External ID'],
                    $row['Order Reference'],
                    $row['Customer'],
                    $row['Phone'],
                    $row['Order Date'],
                    $row['Order Lines/Product SKU'],
                    $row['Order Lines/Quantity (m)'],
                    $row['Total Amount']
                ]);
            }

            fclose($output);
            exit;
        } catch (Throwable $e) {
            $this->respondError('Lỗi khi xuất file Odoo CSV: ' . $e->getMessage(), '#dong-bo-odoo');
        }
    }

    /**
     * Xử lý tạo mã giảm giá Voucher mới (POST ?action=storeVoucher)
     */
    public function storeVoucher(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Phương thức truy cập không hợp lệ (Yêu cầu POST).');
            }

            $payload = [
                'ma_code'          => $_POST['ma_code'] ?? '',
                'gia_tri'          => $_POST['gia_tri'] ?? 0,
                'loai_giam'        => $_POST['loai_giam'] ?? '%',
                'gioi_han_luot'    => $_POST['gioi_han_luot'] ?? 100,
                'han_su_dung'      => $_POST['han_su_dung'] ?? '2025-12-31',
                'ten_chuong_trinh' => $_POST['ten_chuong_trinh'] ?? null,
                'mo_ta_dieu_kien'  => $_POST['mo_ta_dieu_kien'] ?? null
            ];

            $voucherId = $this->productModel->createVoucher($payload);

            $this->respondSuccess(
                sprintf('Đã lưu & kích hoạt mã giảm giá %s thành công!', strtoupper(trim((string) $payload['ma_code']))),
                '#voucher-khuyen-mai',
                ['ma_voucher' => $voucherId]
            );
        } catch (Throwable $e) {
            $this->respondError($e->getMessage(), '#voucher-khuyen-mai');
        }
    }

    /**
     * Xử lý kiểm duyệt đánh giá khách hàng (POST/GET ?action=moderateReview)
     * Hỗ trợ 4 thao tác: approve (Duyệt hiển thị), hide (Ẩn đánh giá), delete (Xóa vĩnh viễn), pin (Ghim lên đầu)
     */
    public function moderateReview(): void
    {
        try {
            $maDanhGia = (int) ($_POST['ma_danh_gia'] ?? $_GET['id'] ?? 0);
            $modAction = trim((string) ($_POST['mod_action'] ?? $_GET['mod_action'] ?? ''));

            if ($maDanhGia <= 0 || $modAction === '') {
                throw new InvalidArgumentException('Thiếu thông tin mã đánh giá hoặc hành động kiểm duyệt.');
            }

            $this->productModel->moderateReview($maDanhGia, $modAction);

            $messages = [
                'approve' => 'Đã duyệt hiển thị đánh giá #' . $maDanhGia . ' lên trang sản phẩm.',
                'hide'    => 'Đã ẩn đánh giá #' . $maDanhGia . ' khỏi trang Web.',
                'delete'  => 'Đã xóa vĩnh viễn đánh giá vi phạm #' . $maDanhGia . ' khỏi cơ sở dữ liệu.',
                'pin'     => 'Đã cập nhật trạng thái ghim đánh giá #' . $maDanhGia . ' lên đầu.'
            ];

            $message = $messages[$modAction] ?? 'Đã cập nhật trạng thái đánh giá thành công.';

            $this->respondSuccess(
                $message,
                '#kiem-duyet-danh-gia',
                ['ma_danh_gia' => $maDanhGia, 'mod_action' => $modAction]
            );
        } catch (Throwable $e) {
            $this->respondError($e->getMessage(), '#kiem-duyet-danh-gia');
        }
    }

    /* =====================================================================
       CÁC HÀM HỖ TRỢ PHẢN HỒI HTTP / JSON / REDIRECT
       ===================================================================== */

    /**
     * Lấy đường dẫn script điều hướng hiện tại (hỗ trợ cả chạy từ gốc hoặc từ backend/public)
     */
    private function getRedirectScript(): string
    {
        $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        return $scriptName !== '' ? $scriptName : 'index.php';
    }

    /**
     * Kiểm tra request có phải là AJAX / Fetch JSON hay không
     */
    private function isAjaxRequest(): bool
    {
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        return $requestedWith === 'xmlhttprequest' || strpos($accept, 'application/json') !== false;
    }

    /**
     * Trả kết quả thành công (JSON cho AJAX hoặc Redirect kèm Flash Session cho Form)
     *
     * @param string $message
     * @param string $anchor
     * @param array<string, mixed> $extraData
     */
    private function respondSuccess(string $message, string $anchor = '', array $extraData = []): void
    {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array_merge([
                'success' => true,
                'message' => $message
            ], $extraData), JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash_success'] = $message;
        header('Location: ' . $this->getRedirectScript() . $anchor);
        exit;
    }

    /**
     * Trả kết quả lỗi (JSON cho AJAX hoặc Redirect kèm Flash Error cho Form)
     *
     * @param string $errorMessage
     * @param string $anchor
     */
    private function respondError(string $errorMessage, string $anchor = ''): void
    {
        if ($this->isAjaxRequest()) {
            http_response_code(400);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $_SESSION['flash_error'] = $errorMessage;
        header('Location: ' . $this->getRedirectScript() . $anchor);
        exit;
    }

    /**
     * Hiển thị thông báo lỗi hệ thống thân thiện nếu kết nối DB gặp sự cố
     */
    private function handleError(string $message): void
    {
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="vi"><head><meta charset="utf-8"><title>Lỗi Hệ Thống - Aura Admin Portal</title>';
        echo '<script src="https://cdn.tailwindcss.com"></script></head>';
        echo '<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">';
        echo '<div class="max-w-lg w-full bg-white border border-rose-200 rounded-xl p-6 shadow-sm space-y-4">';
        echo '<div class="flex items-center space-x-3 text-rose-600 font-bold text-lg">';
        echo '<span>Cảnh báo Kết nối Hệ thống</span></div>';
        echo '<p class="text-sm text-slate-600 leading-relaxed">' . htmlspecialchars($message) . '</p>';
        echo '<div class="text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-200">';
        echo 'Hãy đảm bảo dịch vụ MySQL trong XAMPP đang chạy và bạn đã nạp file <code>backend/database/migrations/schema_mysql.sql</code>.';
        echo '</div>';
        echo '<a href="' . htmlspecialchars($this->getRedirectScript()) . '" class="inline-block px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-lg">Thử tải lại trang</a>';
        echo '</div></body></html>';
        exit;
    }
}

