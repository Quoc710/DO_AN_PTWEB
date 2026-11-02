<?php

namespace App\Config;

use App\Core\Database as CoreDatabase;
use PDO;

/**
 * App\Config\Database — Wrapper tương thích ngược cho các Module sử dụng
 * \App\Config\Database::getConnection() (ví dụ: Lookbook&Affiliate, Admin)
 *
 * Mọi cấu hình thực sự đều nằm trong App\Core\Database (đọc từ .env).
 * Class này chỉ là alias để không phải sửa code các module cũ.
 */
class Database
{
    public static function getConnection(): PDO
    {
        return CoreDatabase::getConnection();
    }
}
