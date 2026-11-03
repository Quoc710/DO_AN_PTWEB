<?php
declare(strict_types=1);

/** PDO MySQL singleton. Connection settings can be overridden by Docker environment variables. */
class Database
{
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'QuanLyCuaHangVai';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                getenv('DB_HOST') ?: self::DB_HOST,
                getenv('DB_PORT') ?: self::DB_PORT,
                getenv('DB_NAME') ?: self::DB_NAME,
                self::DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ];
            try {
                self::$instance = new PDO(
                    $dsn,
                    getenv('DB_USER') ?: self::DB_USER,
                    getenv('DB_PASS') ?: self::DB_PASS,
                    $options
                );
            } catch (PDOException $error) {
                throw new RuntimeException(
                    'Không thể kết nối CSDL MySQL (QuanLyCuaHangVai): ' . $error->getMessage(),
                    (int) $error->getCode(),
                    $error
                );
            }
        }
        return self::$instance;
    }
}