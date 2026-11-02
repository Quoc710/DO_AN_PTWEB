<?php

namespace App\Core;

use App\Config\Env;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Database Connection — MySQL (PDO Singleton)
 * Đọc cấu hình từ file .env thông qua App\Config\Env
 *
 * Biến .env cần thiết:
 *   DB_HOST     — máy chủ MySQL (mặc định: db khi dùng Docker, localhost khi dev)
 *   DB_PORT     — cổng MySQL (mặc định: 3306)
 *   DB_NAME     — tên cơ sở dữ liệu (mặc định: QuanLyCuaHangVai)
 *   DB_USER     — tên đăng nhập (mặc định: root)
 *   DB_PASS     — mật khẩu (mặc định: rỗng)
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    private static function createConnection(): PDO
    {
        $host     = Env::get('DB_HOST', 'db');
        $port     = Env::get('DB_PORT', '3306');
        $database = Env::get('DB_NAME', 'QuanLyCuaHangVai');
        $username = Env::get('DB_USER', 'root');
        $password = Env::get('DB_PASS', '');

        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException(
                "Lỗi kết nối MySQL [$host:$port/$database]: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }
}
