<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../Config/database.php';
require_once __DIR__ . '/../Models/LookbookModel.php';
require_once __DIR__ . '/../Models/AffiliateModel.php';

use App\Modules\LookbookAffiliate\Models\AffiliateModel;
use App\Modules\LookbookAffiliate\Models\LookbookModel;

class LookbookAffiliateController
{
    private string $projectRoot;
    private LookbookModel $lookbookModel;
    private AffiliateModel $affiliateModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->projectRoot = dirname(__DIR__, 5);
        $this->lookbookModel = new LookbookModel();
        $this->affiliateModel = new AffiliateModel();
    }

    public function dispatch(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
            return;
        }

        $tab = (string) ($_GET['tab'] ?? 'lookbook');
        if ($tab === 'admin') {
            header('Location: index.php');
            exit;
        }

        // KIỂM TRA PHÂN QUYỀN TRUY CẬP CỔNG THỢ MAY (TAB TAILOR)
        $sessionUser = $_SESSION['user'] ?? null;
        $loaiTK = strtolower((string)($sessionUser['LoaiTK'] ?? $sessionUser['LoaiKhach'] ?? ''));
        $isAffiliate = ($loaiTK === 'admin' 
            || strpos($loaiTK, 'affiliate') !== false 
            || strpos($loaiTK, 'thợ may') !== false 
            || strpos($loaiTK, 'tho may') !== false 
            || strpos($loaiTK, 'nhà may') !== false 
            || strpos($loaiTK, 'nha may') !== false);

        if ($tab === 'tailor' && !$isAffiliate) {
            $_SESSION['flash_msg'] = '⛔ QUYỀN TRUY CẬP BỊ TỪ CHỐI: Bạn đang là tài khoản Khách Hàng. Chỉ đối tác Thợ May (Affiliate) đã được Admin duyệt mới được vào Cổng Thợ May!';
            $_SESSION['flash_type'] = 'warning';
            header('Location: ?module=lookbook&tab=lookbook');
            exit;
        }

        if (!in_array($tab, ['tailor', 'lookbook'], true)) {
            $tab = 'lookbook';
        }

        $defaultTailorId = $tab === 'tailor' ? 2 : 1;
        $currentTailorId = max(1, (int) ($_GET['tailor_id'] ?? $defaultTailorId));
        $category = (string) ($_GET['category'] ?? 'Tất cả mẫu');
        $refCode = trim((string) ($_GET['ref'] ?? ''));
        $fabricFilter = trim((string) ($_GET['fabric'] ?? ''));
        $referralTailor = null;
        $lookbooks = [];
        $allTailors = [];
        $tailor = null;
        $fabrics = [];
        $transactions = [];
        $affiliateOrders = [];

        try {
            $db = \Database::getConnection();
            if ($refCode !== '') {
                $stmt = $db->prepare('SELECT * FROM NHAN_VIEN WHERE AffiliateCode = :code LIMIT 1');
                $stmt->execute([':code' => $refCode]);
                $referralTailor = $stmt->fetch() ?: null;
            }

            if ($tab === 'lookbook') {
                if ($referralTailor) {
                    $currentTailorId = (int) $referralTailor['MaNV'];
                } elseif ($refCode === '') {
                    $defaultTailor = $db->query("SELECT MaNV FROM NHAN_VIEN WHERE AffiliateCode = 'minhanh' LIMIT 1")->fetch();
                    if ($defaultTailor) {
                        $currentTailorId = (int) $defaultTailor['MaNV'];
                    }
                }
                $tailor = $this->affiliateModel->getTailorProfile($currentTailorId);
                $lookbooks = $this->lookbookModel->getLookbooks($category, true);
                if ($fabricFilter !== '') {
                    $filtered = array_values(array_filter($lookbooks, static function (array $item) use ($fabricFilter): bool {
                        return stripos(str_replace('#', '', (string) ($item['MaVaiCode'] ?? '')), str_replace('#', '', $fabricFilter)) !== false;
                    }));
                    if ($filtered !== []) {
                        $lookbooks = $filtered;
                    }
                }
            } else {
                $allTailors = $db->query(
                    "SELECT MaNV, TenNV, TenTiemMay, AffiliateCode FROM NHAN_VIEN
                     WHERE ViTri LIKE '%Thợ may%' OR TenTiemMay IS NOT NULL ORDER BY MaNV ASC"
                )->fetchAll();
                $tailor = $this->affiliateModel->getTailorProfile($currentTailorId);
                if ($tailor && !empty($tailor['MaNV'])) {
                    $currentTailorId = (int) $tailor['MaNV'];
                }
                $fabrics = $this->affiliateModel->getFabricsForAffiliate((string) ($tailor['AffiliateCode'] ?? ''));
                $transactions = $this->affiliateModel->getRecentTransactions($currentTailorId);
                $affiliateOrders = $this->affiliateModel->getAffiliateOrders($currentTailorId);
            }
        } catch (Throwable $error) {
            http_response_code(500);
            $flashMsg = 'Không tải được dữ liệu Lookbook/Affiliate: ' . $error->getMessage();
            $flashType = 'error';
        }

        $flashMsg = $flashMsg ?? ($_SESSION['lookbook_flash_message'] ?? null);
        $flashType = $flashType ?? ($_SESSION['lookbook_flash_type'] ?? 'success');
        unset($_SESSION['lookbook_flash_message'], $_SESSION['lookbook_flash_type']);

        require $this->projectRoot . '/frontend/src/modules/Lookbook&Affiliate/pages/index.php';
    }

    private function handlePost(): void
    {
        $action = trim((string) ($_POST['action'] ?? ''));
        $currentTailorId = max(1, (int) ($_POST['maNV'] ?? $_GET['tailor_id'] ?? 1));
        $flashType = 'success';

        try {
            switch ($action) {
                case 'submit_lookbook':
                    $imageUrl = trim((string) ($_POST['urlHinhAnh'] ?? ''));
                    if (isset($_FILES['anhLookbook']) && $_FILES['anhLookbook']['error'] === UPLOAD_ERR_OK) {
                        $extension = strtolower(pathinfo((string) $_FILES['anhLookbook']['name'], PATHINFO_EXTENSION));
                        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                            throw new InvalidArgumentException('Ảnh Lookbook phải là JPG, PNG hoặc WebP.');
                        }
                        $uploadDir = $this->projectRoot . '/frontend/public/uploads/lookbooks/';
                        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
                            throw new RuntimeException('Không tạo được thư mục lưu ảnh Lookbook.');
                        }
                        $fileName = 'lookbook_' . bin2hex(random_bytes(8)) . '.' . $extension;
                        if (!move_uploaded_file((string) $_FILES['anhLookbook']['tmp_name'], $uploadDir . $fileName)) {
                            throw new RuntimeException('Không lưu được ảnh Lookbook đã tải lên.');
                        }
                        $imageUrl = 'frontend/public/uploads/lookbooks/' . $fileName;
                    }

                    $data = [
                        'tenLookbook' => trim((string) ($_POST['tenLookbook'] ?? 'Thiết kế mới')),
                        'urlHinhAnh' => $imageUrl ?: 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600',
                        'danhMuc' => (string) ($_POST['danhMuc'] ?? 'Đầm & Váy'),
                        'maBienThe' => max(1, (int) ($_POST['maBienThe'] ?? 1)),
                        'dinhLuong' => trim((string) ($_POST['dinhLuong'] ?? '2.0m vải')),
                        'moTa' => trim((string) ($_POST['moTa'] ?? '')),
                    ];
                    $this->lookbookModel->createLookbook($currentTailorId, $data);
                    $this->flash('Đã gửi bài Lookbook để Admin thẩm định.');
                    break;

                case 'request_withdraw':
                    $rawAmount = str_replace(['.', ',', ' '], '', (string) ($_POST['soTien'] ?? '0'));
                    $amount = (float) $rawAmount;
                    if ($amount <= 0) {
                        throw new InvalidArgumentException('Số tiền rút phải lớn hơn 0.');
                    }
                    $method = (string) ($_POST['hinhThuc'] ?? 'NganHang');
                    if (!in_array($method, ['NganHang', 'MuaVaiAura'], true)) {
                        throw new InvalidArgumentException('Hình thức nhận tiền không hợp lệ.');
                    }
                    $tailor = $this->affiliateModel->getTailorProfile($currentTailorId);
                    $recipient = $method === 'MuaVaiAura'
                        ? 'Đổi sang số dư mua vải (+10% voucher)'
                        : (($tailor['NganHang'] ?? 'Ngân hàng') . ' - ' . ($tailor['SoTaiKhoan'] ?? 'Chưa có số tài khoản') . ' (' . mb_strtoupper((string) ($tailor['TenNV'] ?? 'Thợ may')) . ')');
                    $result = $this->affiliateModel->createWithdrawalRequest($currentTailorId, $amount, $method, $recipient);
                    $flashType = !empty($result['success']) ? 'success' : 'error';
                    $this->flash((string) ($result['message'] ?? 'Không gửi được yêu cầu rút tiền.'), $flashType);
                    break;

                case 'buy_via_affiliate':
                    $refCode = trim((string) ($_POST['refCode'] ?? ''));
                    $fabricCode = trim((string) ($_POST['maVaiCode'] ?? ''));
                    $meters = (float) ($_POST['soMet'] ?? 0);
                    $unitPrice = (float) ($_POST['donGia'] ?? 0);
                    if ($meters <= 0 || $unitPrice <= 0) {
                        throw new InvalidArgumentException('Số mét vải và đơn giá phải lớn hơn 0.');
                    }
                    $result = $this->affiliateModel->awardCommission($refCode, $fabricCode, $meters, $unitPrice);
                    $flashType = !empty($result['success']) ? 'success' : 'error';
                    $message = !empty($result['success'])
                        ? 'Đã ghi nhận đơn ' . ($result['orderCode'] ?? '') . ' và hoa hồng ' . number_format((float) ($result['hoaHong'] ?? 0), 0, ',', '.') . '₫ cho ' . ($result['tailor']['TenTiemMay'] ?? $result['tailor']['TenNV'] ?? 'thợ may') . '.'
                        : (string) ($result['message'] ?? 'Không ghi nhận được đơn mua.');
                    $this->flash($message, $flashType);
                    break;

                default:
                    throw new InvalidArgumentException('Thao tác Lookbook/Affiliate không hợp lệ.');
            }
        } catch (Throwable $error) {
            $this->flash($error->getMessage(), 'error');
        }

        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        if ($requestedWith === 'xmlhttprequest' || strpos($accept, 'application/json') !== false) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => ($_SESSION['lookbook_flash_type'] ?? 'success') === 'success',
                'message' => $_SESSION['lookbook_flash_message'] ?? 'Đã xử lý xong.',
                'hoaHong' => (float) ($result['hoaHong'] ?? 0),
            ], JSON_UNESCAPED_UNICODE);
            unset($_SESSION['lookbook_flash_message'], $_SESSION['lookbook_flash_type']);
            return;
        }

        $tab = (string) ($_GET['tab'] ?? 'tailor');
        if (!in_array($tab, ['tailor', 'lookbook'], true)) {
            $tab = 'tailor';
        }
        $query = ['module' => 'lookbook', 'tab' => $tab, 'tailor_id' => $currentTailorId];
        foreach (['ref', 'fabric', 'category'] as $key) {
            if (!empty($_GET[$key])) {
                $query[$key] = (string) $_GET[$key];
            }
        }
        header('Location: index.php?' . http_build_query($query));
        exit;
    }

    private function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['lookbook_flash_message'] = $message;
        $_SESSION['lookbook_flash_type'] = $type;
    }
}