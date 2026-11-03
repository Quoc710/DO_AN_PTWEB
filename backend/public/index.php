<?php
declare(strict_types=1);

$module = (string) ($_GET['module'] ?? 'admin');
if ($module === 'lookbook') {
    require_once __DIR__ . '/../src/Modules/Lookbook&Affiliate/routes.php';
    dispatchLookbookAffiliateModule();
    exit;
}

require_once __DIR__ . '/../src/Modules/Admin/routes.php';
dispatchAdminModuleRoutes();