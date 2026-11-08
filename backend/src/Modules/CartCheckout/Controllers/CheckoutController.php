<?php
declare(strict_types=1);

namespace App\Modules\CartCheckout\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Middleware\AuthMiddleware;
use App\Modules\CartCheckout\Models\CheckoutModel;
use InvalidArgumentException;
use Throwable;

require_once __DIR__ . '/../../../Config/database.php';

/**
 * API đơn hàng online + thanh toán VietQR ngân hàng.
 *
 * POST /api/gio-hang/dat-hang                 -> tạo đơn, trả QR VietQR
 * GET  /api/gio-hang/don-hang/{code}          -> tra cứu trạng thái đơn (polling)
 * POST /api/gio-hang/don-hang/{code}/xac-nhan -> xác nhận demo "đã chuyển khoản"
 * POST /api/gio-hang/webhook/chuyen-khoan     -> webhook ngân hàng (SePay/Casso)
 * GET  /api/gio-hang/cau-hinh                 -> thông tin tài khoản nhận tiền
 * GET  /api/gio-hang/voucher                  -> danh sách voucher đang hiệu lực
 * POST /api/gio-hang/voucher/kiem-tra         -> kiểm tra mã voucher + tính giảm giá
 */
class CheckoutController
{
    private CheckoutModel $model;

    public function __construct()
    {
        $this->model = new CheckoutModel();
    }

    public function create(): void
    {
        try {
            $body = Request::body();
            $customer = is_array($body['customer'] ?? null) ? $body['customer'] : [];
            $sessionUser = Session::get('user');
            // Gắn đơn với khách đã đăng nhập; không nhận MaKH do trình duyệt tự gửi.
            $customer['maKH'] = is_array($sessionUser) && !empty($sessionUser['MaKH'])
                ? (int) $sessionUser['MaKH']
                : 0;
            $items = is_array($body['items'] ?? null) ? $body['items'] : [];
            $shippingMethod = strtolower((string) ($body['shippingMethod'] ?? 'standard'));
            $method = strtolower((string) ($body['method'] ?? 'vietqr'));
            if (!in_array($method, ['vietqr', 'cod'], true)) {
                $method = 'vietqr';
            }

            // Không tạo QR nếu chủ shop chưa nhập tài khoản nhận tiền của mình.
            $bank = $method === 'vietqr' ? $this->bankConfig() : [];
            if ($method === 'vietqr' && (
                $bank['bankId'] === '' || $bank['accountNo'] === '' || $bank['accountName'] === ''
            )) {
                throw new InvalidArgumentException(
                    'Chưa cấu hình tài khoản ngân hàng nhận tiền. Hãy điền BANK_ID, BANK_ACCOUNT và BANK_ACCOUNT_NAME trong .env.docker.example rồi khởi động lại Docker.'
                );
            }

            $order = $this->model->createOrder(
                $customer,
                $items,
                $method,
                (string) ($body['voucherCode'] ?? $body['voucher'] ?? ''),
                $shippingMethod
            );
            if (!$order) {
                throw new \RuntimeException('Không đọc lại được đơn hàng vừa tạo.');
            }

            $qrUrl = $method === 'vietqr'
                ? $this->buildQrUrl((string) $order['MaDon'], (float) $order['TongThanhToan'], $bank)
                : null;

            Response::success([
                'order' => $this->publicOrder($order),
                'bank' => $bank,
                'qrUrl' => $qrUrl,
            ], 'Tạo đơn hàng thành công');
        } catch (InvalidArgumentException $error) {
            Response::error($error->getMessage(), 422);
        } catch (Throwable $error) {
            Response::error('Không tạo được đơn hàng: ' . $error->getMessage(), 500);
        }
    }

    public function myOrders(): void
    {
        try {
            $user = AuthMiddleware::check();
            $maKH = (int) ($user['MaKH'] ?? 0);
            if ($maKH <= 0) {
                Response::error('Đăng nhập bằng tài khoản khách hàng để xem đơn mua.', 403);
                return;
            }
            Response::success([
                'orders' => $this->model->getOrdersByCustomer($maKH),
            ], 'Danh sách đơn hàng của bạn');
        } catch (Throwable $error) {
            Response::error('Không tải được đơn hàng: ' . $error->getMessage(), 500);
        }
    }

    public function adminOrders(): void
    {
        try {
            $this->requireAdmin();
            Response::success(['orders' => $this->model->getAllOrdersForAdmin()], 'Danh sách đơn hàng online');
        } catch (Throwable $error) {
            Response::error('Không tải được đơn hàng online: ' . $error->getMessage(), 500);
        }
    }

    public function adminStartDelivery(string $code): void
    {
        try {
            $this->requireAdmin();
            Response::success(['order' => $this->model->startDelivery($code)], 'Đã chuyển đơn sang trạng thái đang giao.');
        } catch (InvalidArgumentException $error) {
            Response::error($error->getMessage(), 422);
        } catch (Throwable $error) {
            Response::error('Không cập nhật được trạng thái giao hàng: ' . $error->getMessage(), 500);
        }
    }

    public function adminConfirmDelivery(string $code): void
    {
        try {
            $this->requireAdmin();
            Response::success(['order' => $this->model->confirmDelivered($code, 'admin')], 'Đã ghi nhận giao hàng thành công.');
        } catch (InvalidArgumentException $error) {
            Response::error($error->getMessage(), 422);
        } catch (Throwable $error) {
            Response::error('Không xác nhận được giao hàng: ' . $error->getMessage(), 500);
        }
    }

    public function customerConfirmDelivery(string $code): void
    {
        try {
            $user = AuthMiddleware::check();
            $maKH = (int) ($user['MaKH'] ?? 0);
            if ($maKH <= 0) Response::error('Chỉ tài khoản khách hàng sở hữu đơn mới xác nhận được.', 403);
            Response::success([
                'order' => $this->model->confirmDelivered($code, 'khach_hang', $maKH),
            ], 'Cảm ơn bạn đã xác nhận nhận hàng.');
        } catch (InvalidArgumentException $error) {
            Response::error($error->getMessage(), 422);
        } catch (Throwable $error) {
            Response::error('Không xác nhận được đơn hàng: ' . $error->getMessage(), 500);
        }
    }

    public function customerCancelDelivery(string $code): void
    {
        try {
            $user = AuthMiddleware::check();
            $maKH = (int) ($user['MaKH'] ?? 0);
            if ($maKH <= 0) Response::error('Chỉ tài khoản khách hàng sở hữu đơn mới được hủy.', 403);
            Response::success([
                'order' => $this->model->cancelDelivery($code, $maKH),
            ], 'Đơn hàng đã được hủy trong thời gian giao.');
        } catch (InvalidArgumentException $error) {
            Response::error($error->getMessage(), 422);
        } catch (Throwable $error) {
            Response::error('Không hủy được đơn hàng: ' . $error->getMessage(), 500);
        }
    }

    private function requireAdmin(): array
    {
        $user = AuthMiddleware::check();
        if (!in_array(strtolower((string) ($user['LoaiTK'] ?? '')), ['admin', 'super_admin'], true)) {
            Response::error('Bạn không có quyền quản lý đơn hàng.', 403);
        }
        return $user;
    }

    public function show(string $code): void
    {
        try {
            $order = $this->model->getOrder($code);
            if (!$order) {
                Response::error('Không tìm thấy đơn hàng ' . $code . '.', 404);
            }
            Response::success([
                'order' => $this->publicOrder($order),
                'bank' => $this->bankConfig(),
            ], 'Lấy thông tin đơn hàng thành công');
        } catch (Throwable $error) {
            Response::error('Lỗi tra cứu đơn hàng: ' . $error->getMessage(), 500);
        }
    }

    /**
     * Demo xác nhận chuyển khoản (dùng khi chưa cấu hình webhook ngân hàng).
     * Có thể tắt bằng biến môi trường PAYMENT_DEMO_CONFIRM=false.
     */
    public function confirm(string $code): void
    {
        try {
            if (strtolower((string) $this->env('PAYMENT_DEMO_CONFIRM', 'true')) === 'false') {
                Response::error('Chức năng xác nhận thủ công đang bị tắt.', 403);
            }
            // Chế độ demo: chỉ cần đúng mã đơn (không lộ khóa webhook ra trình duyệt).
            // Khi tắt PAYMENT_DEMO_CONFIRM, endpoint này bị khóa hoàn toàn.

            $order = $this->model->markPaid($code, 'DEMO-CONFIRM-' . date('YmdHis'));
            Response::success(['order' => $this->publicOrder($order)], 'Đã ghi nhận thanh toán cho đơn ' . $code . '.');
        } catch (InvalidArgumentException $error) {
            Response::error($error->getMessage(), 422);
        } catch (Throwable $error) {
            Response::error('Không xác nhận được thanh toán: ' . $error->getMessage(), 500);
        }
    }

    /**
     * Webhook ngân hàng: nhận nội dung chuyển khoản, tách mã AURxxxxxx và đánh dấu đã trả.
     * Tương thích payload SePay: { content, transferAmount, referenceCode }.
     */
    public function webhook(): void
    {
        try {
            $this->assertWebhookKey(
                Request::body()['key']
                    ?? Request::body()['apiKey']
                    ?? Request::query('key')
            );

            $body = Request::body();
            $content = (string) (
                $body['content']
                    ?? $body['description']
                    ?? $body['transferContent']
                    ?? $body['transaction_content']
                    ?? ''
            );
            $amount = (float) (
                $body['transferAmount']
                    ?? $body['amount']
                    ?? $body['transfer_amount']
                    ?? 0
            );
            $bankRef = (string) (
                $body['referenceCode']
                    ?? $body['transactionId']
                    ?? $body['reference_code']
                    ?? ('WEBHOOK-' . date('YmdHis'))
            );

            $code = $this->model->findOrderByContent($content);
            if (!$code) {
                Response::json(['success' => false, 'message' => 'Không tìm thấy mã đơn AUR trong nội dung chuyển khoản.'], 200);
            }

            $order = $this->model->markPaid($code, $bankRef, $amount > 0 ? $amount : null);
            Response::json([
                'success' => true,
                'message' => 'Đã xác nhận thanh toán đơn ' . $code . '.',
                'order' => $this->publicOrder($order),
            ], 200);
        } catch (InvalidArgumentException $error) {
            Response::json(['success' => false, 'message' => $error->getMessage()], 422);
        } catch (Throwable $error) {
            Response::json(['success' => false, 'message' => $error->getMessage()], 500);
        }
    }

    public function config(): void
    {
        Response::success([
            'bank' => $this->bankConfig(),
            'demoConfirm' => strtolower((string) $this->env('PAYMENT_DEMO_CONFIRM', 'true')) !== 'false',
        ], 'Cấu hình thanh toán');
    }

    /**
     * Danh sách voucher đang hiệu lực để khách hàng chọn nhanh.
     */
    public function vouchers(): void
    {
        try {
            Response::success([
                'vouchers' => $this->model->listActiveVouchers(),
            ], 'Danh sách voucher đang hiệu lực');
        } catch (Throwable $error) {
            Response::error('Không tải được danh sách voucher: ' . $error->getMessage(), 500);
        }
    }

    /**
     * Kiểm tra mã voucher khách nhập và trả về số tiền giảm trên tạm tính.
     */
    public function checkVoucher(): void
    {
        try {
            $body = Request::body();
            $code = (string) ($body['code'] ?? $body['voucherCode'] ?? '');
            $subtotal = (float) ($body['subtotal'] ?? 0);
            $voucher = $this->model->validateVoucher($code, $subtotal);
            Response::success(['voucher' => $voucher], 'Áp dụng voucher thành công');
        } catch (InvalidArgumentException $error) {
            Response::error($error->getMessage(), 422);
        } catch (Throwable $error) {
            Response::error('Không kiểm tra được voucher: ' . $error->getMessage(), 500);
        }
    }

    private function publicOrder(array $order): array
    {
        unset($order['HoaHongDaCong']);
        return $order;
    }

    private function bankConfig(): array
    {
        $bankId = trim($this->env('BANK_ID', ''));
        $accountNo = trim($this->env('BANK_ACCOUNT', ''));
        $accountName = trim($this->env('BANK_ACCOUNT_NAME', ''));
        return [
            'bankId' => $bankId,
            'bankName' => (string) $this->env('BANK_NAME', $bankId),
            'accountNo' => $accountNo,
            'accountName' => $accountName,
            'template' => (string) $this->env('BANK_QR_TEMPLATE', 'compact2'),
        ];
    }

    private function buildQrUrl(string $orderCode, float $amount, array $bank): string
    {
        return 'https://img.vietqr.io/image/'
            . rawurlencode($bank['bankId']) . '-' . rawurlencode($bank['accountNo'])
            . '-' . rawurlencode($bank['template']) . '.png'
            . '?amount=' . (int) round($amount)
            . '&addInfo=' . rawurlencode($orderCode)
            . '&accountName=' . rawurlencode($bank['accountName']);
    }

    private function assertWebhookKey(mixed $provided): void
    {
        $expected = (string) $this->env('BANK_WEBHOOK_KEY', 'aura-demo-key');
        if ($expected === '') {
            return;
        }

        $provided = trim((string) ($provided ?? ''));
        if ($provided === '') {
            $auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
            if (stripos($auth, 'Apikey ') === 0) {
                $provided = trim(substr($auth, 7));
            } elseif (stripos($auth, 'Bearer ') === 0) {
                $provided = trim(substr($auth, 7));
            }
        }
        $headerKey = (string) ($_SERVER['HTTP_X_WEBHOOK_KEY'] ?? '');
        if ($provided === '' && $headerKey !== '') {
            $provided = trim($headerKey);
        }

        if (!hash_equals($expected, $provided)) {
            throw new InvalidArgumentException('Sai khóa xác thực webhook thanh toán.');
        }
    }

    private function env(string $key, string $default): string
    {
        $value = getenv($key);
        if ($value === false || $value === '') {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? '';
        }
        return $value !== '' ? (string) $value : $default;
    }
}
