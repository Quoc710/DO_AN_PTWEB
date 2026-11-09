<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/Config/database.php';

try {
    $model = new \App\Modules\CartCheckout\Models\CheckoutModel();
    $count = $model->autoConfirmExpiredDeliveries();
    if ($count > 0) {
        fwrite(STDOUT, sprintf("Auto-confirmed %d delivered order(s) at %s\n", $count, date('c')));
    }
} catch (Throwable $error) {
    fwrite(STDERR, 'Delivery auto-confirm failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
