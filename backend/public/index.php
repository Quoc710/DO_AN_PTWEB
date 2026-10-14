<?php
// Diem vao cua backend. Chay thu: php -S localhost:8000 -t public
require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => 'Backend is running']);
