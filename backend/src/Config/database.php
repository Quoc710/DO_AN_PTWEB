<?php
declare(strict_types=1);

/**
 * Cấu hình và khởi tạo kết nối PDO MySQL
 * Vị trí: backend/src/Config/database.php
 * Cơ sở dữ liệu: QuanLyCuaHangVai (UTF-8 Unicode)
 */
class Database
{
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'QuanLyCuaHangVai';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    private static ?PDO $instance = null;

    /**
     * Ngăn khởi tạo trực tiếp từ bên ngoài (Singleton Pattern)
     */
    private function __construct()
    {
    }

    /**
     * Ngăn nhân bản đối tượng
     */
    private function __clone()
    {
    }

    /**
     * Lấy đối tượng kết nối PDO duy nhất
     *
     * @return PDO
     * @throws RuntimeException Nếu kết nối thất bại
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_PORT,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                throw new RuntimeException(
                    'Không thể kết nối CSDL MySQL (QuanLyCuaHangVai): ' . $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$instance;
    }
}

