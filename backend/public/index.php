<?php
// Diem vao cua backend.
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    require __DIR__ . '/../src/autoload.php';
}

header('Content-Type: application/json; charset=utf-8');

try {
    $db = \App\Config\Database::getConnection();
    echo json_encode([
        'status' => 'ok',
        'message' => 'Backend PHP và CSDL MySQL QuanLyCuaHangVai đã kết nối thành công!',
        'server_time' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
