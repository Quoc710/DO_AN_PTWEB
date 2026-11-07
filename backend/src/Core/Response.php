<?php

namespace App\Core;

class Response
{
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
        if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
            $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
        }

        $json = json_encode($data, $flags);
        if ($json === false) {
            // Fallback: Làm sạch đệ quy ký tự UTF-8 không hợp lệ
            $cleaned = self::cleanUtf8($data);
            $json = json_encode($cleaned, $flags);
            if ($json === false) {
                $json = json_encode([
                    'status' => 'error',
                    'message' => 'Lỗi mã hóa dữ liệu JSON: ' . json_last_error_msg()
                ]);
            }
        }

        echo $json;
        exit;
    }

    private static function cleanUtf8(mixed $data): mixed
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $cleanKey = is_string($key) ? mb_convert_encoding($key, 'UTF-8', 'UTF-8') : $key;
                $result[$cleanKey] = self::cleanUtf8($value);
            }
            return $result;
        }
        if (is_string($data)) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        return $data;
    }

    public static function success(mixed $data = [], string $message = 'Thành công', int $statusCode = 200): void
    {
        self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    public static function error(string $message = 'Đã có lỗi xảy ra', int $statusCode = 400, array $errors = []): void
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        self::json($payload, $statusCode);
    }
}
