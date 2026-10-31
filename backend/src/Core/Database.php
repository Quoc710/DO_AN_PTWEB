<?php

namespace App\Core;

use App\Config\Env;
use PDO;
use PDOException;
use RuntimeException;

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
        $driver = strtolower(Env::get('DB_DRIVER', 'sqlsrv'));
        $host = Env::get('DB_HOST', 'localhost');
        $port = Env::get('DB_PORT', '1433');
        $database = Env::get('DB_NAME', 'QuanLyCuaHangVai');
        $username = Env::get('DB_USER', 'sa');
        $password = Env::get('DB_PASS', '');

        $availableDrivers = PDO::getAvailableDrivers();

        try {
            if (($driver === 'sqlsrv' || $driver === 'sqlserver') && in_array('sqlsrv', $availableDrivers, true)) {
                $dsn = "sqlsrv:Server=$host" . ($port ? ",$port" : "") . ";Database=$database;TrustServerCertificate=true";
                return new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            }

            if (in_array('odbc', $availableDrivers, true)) {
                $driverName = "{ODBC Driver 17 for SQL Server}";
                $isTrusted = Env::get('DB_TRUSTED_CONNECTION', '') === 'true' || ($username === 'sa' && $password === '');
                $authStr = $isTrusted ? "Trusted_Connection=yes;" : "Uid=$username;Pwd=$password;";
                $serverTarget = ($host === 'localhost' || $host === '127.0.0.1') ? $host : ($port ? "$host,$port" : $host);
                $dsn = "odbc:Driver=$driverName;Server=$serverTarget;Database=$database;$authStr";

                try {
                    return new PDO($dsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                } catch (PDOException $e) {
                    // Thử lại với Windows Authentication nếu xác thực SQL sa thất bại
                    if (!$isTrusted) {
                        try {
                            $trustedDsn = "odbc:Driver=$driverName;Server=$serverTarget;Database=$database;Trusted_Connection=yes;";
                            return new PDO($trustedDsn, null, null, [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            ]);
                        } catch (PDOException) {
                            // Tiếp tục fallback bên dưới
                        }
                    }

                    // Thử driver SQL Server mặc định của Windows
                    $fallbackDsn = "odbc:Driver={SQL Server};Server=$serverTarget;Database=$database;" . ($isTrusted ? "Trusted_Connection=yes;" : "Uid=$username;Pwd=$password;");
                    return new PDO($fallbackDsn, $username, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                }
            }

            if ($driver === 'mysql' || in_array('mysql', $availableDrivers, true)) {
                $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
                return new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            }

            throw new RuntimeException("Không tìm thấy PDO driver phù hợp. Các driver hiện có: " . implode(', ', $availableDrivers));
        } catch (PDOException $e) {
            throw new RuntimeException("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
