<?php

namespace CNA\Utils;

use CNA\Config\Config;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $config = Config::get('security', []);

        // Configure session parameters
        ini_set('session.cookie_lifetime', $config['session_lifetime'] * 60);
        ini_set('session.cookie_secure', $config['session_secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', $config['session_http_only'] ? '1' : '0');
        ini_set('session.cookie_samesite', $config['session_same_site']);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.gc_maxlifetime', $config['session_lifetime'] * 60);

        session_start();
        self::$started = true;

        // Regenerate session ID periodically for security
        if (!self::has('_last_regeneration')) {
            self::regenerate();
        } elseif (time() - self::get('_last_regeneration', 0) > 300) { // 5 minutes
            self::regenerate();
        }
    }

    public static function regenerate(bool $deleteOld = true): void
    {
        session_regenerate_id($deleteOld);
        self::put('_last_regeneration', time());
    }

    public static function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            self::$started = false;
        }
    }

    public static function flash(string $key, $value = null)
    {
        if ($value === null) {
            // Get flash message
            $message = self::get('_flash_' . $key);
            self::remove('_flash_' . $key);
            return $message;
        }

        // Set flash message
        self::put('_flash_' . $key, $value);
    }

    public static function csrf(): string
    {
        if (!self::has('_csrf_token')) {
            self::put('_csrf_token', bin2hex(random_bytes(32)));
        }

        return self::get('_csrf_token');
    }

    public static function verifyCsrf(string $token): bool
    {
        return hash_equals(self::csrf(), $token);
    }

    public static function user(): ?array
    {
        return self::get('user');
    }

    public static function setUser(array $user): void
    {
        self::put('user', $user);
        self::put('_authenticated', true);
    }

    public static function isAuthenticated(): bool
    {
        return self::get('_authenticated', false) && self::has('user');
    }

    public static function logout(): void
    {
        self::remove('user');
        self::remove('_authenticated');
        self::regenerate();
    }
}