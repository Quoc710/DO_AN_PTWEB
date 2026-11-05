<?php
declare(strict_types=1);

require_once __DIR__ . '/Controllers/LookbookAffiliateController.php';

function dispatchLookbookAffiliateModule(): void
{
    try {
        (new LookbookAffiliateController())->dispatch();
    } catch (Throwable $error) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<h1>Lỗi module Lookbook/Affiliate</h1><p>' . htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    }
}