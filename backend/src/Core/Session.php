<?php

namespace App\Core;

class Session
{
    private static bool $started = false;

    public static function init(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        if (headers_sent()) {
            return;
        }

        // Cấu hình cookie session an toàn
        $params = session_get_cookie_params();
        session_set_cookie_params([
            'lifetime' => 86400 * 7, // 7 ngày
            'path' => '/',
            'domain' => $params['domain'],
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        self::$started = true;
    }

    public static function set(string $key, mixed $value): void
    {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::init();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::init();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        self::$started = false;
    }
}
