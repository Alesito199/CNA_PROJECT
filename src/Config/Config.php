<?php

namespace CNA\Config;

use Dotenv\Dotenv;

class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Load environment variables
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->safeLoad();

        self::$config = [
            'app' => [
                'name' => $_ENV['APP_NAME'] ?? 'CNA Upholstery Management System',
                'env' => $_ENV['APP_ENV'] ?? 'production',
                'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'url' => $_ENV['APP_URL'] ?? 'http://localhost',
                'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/New_York',
                'key' => $_ENV['APP_KEY'] ?? '',
            ],
            'database' => [
                'connection' => $_ENV['DB_CONNECTION'] ?? 'pgsql',
                'host' => $_ENV['DB_HOST'] ?? 'localhost',
                'port' => $_ENV['DB_PORT'] ?? '5432',
                'database' => $_ENV['DB_DATABASE'] ?? '',
                'username' => $_ENV['DB_USERNAME'] ?? '',
                'password' => $_ENV['DB_PASSWORD'] ?? '',
            ],
            'jwt' => [
                'secret' => $_ENV['JWT_SECRET'] ?? '',
                'expire' => 86400, // 24 hours
            ],
            'aws' => [
                'access_key_id' => $_ENV['AWS_ACCESS_KEY_ID'] ?? '',
                'secret_access_key' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? '',
                'region' => $_ENV['AWS_DEFAULT_REGION'] ?? 'us-east-1',
                'bucket' => $_ENV['AWS_BUCKET'] ?? '',
                'url' => $_ENV['AWS_URL'] ?? '',
            ],
            'mail' => [
                'mailer' => $_ENV['MAIL_MAILER'] ?? 'smtp',
                'host' => $_ENV['MAIL_HOST'] ?? '',
                'port' => $_ENV['MAIL_PORT'] ?? '587',
                'username' => $_ENV['MAIL_USERNAME'] ?? '',
                'password' => $_ENV['MAIL_PASSWORD'] ?? '',
                'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
                'from_address' => $_ENV['MAIL_FROM_ADDRESS'] ?? '',
                'from_name' => $_ENV['MAIL_FROM_NAME'] ?? '',
            ],
            'business' => [
                'name' => $_ENV['BUSINESS_NAME'] ?? 'CNA Upholstery',
                'address' => $_ENV['BUSINESS_ADDRESS'] ?? '',
                'phone' => $_ENV['BUSINESS_PHONE'] ?? '',
                'email' => $_ENV['BUSINESS_EMAIL'] ?? '',
                'tax_rate' => (float)($_ENV['TAX_RATE'] ?? '6.625'),
            ],
            'language' => [
                'default' => $_ENV['DEFAULT_LANGUAGE'] ?? 'en',
                'supported' => explode(',', $_ENV['SUPPORTED_LANGUAGES'] ?? 'en,es'),
            ],
            'upload' => [
                'max_size' => (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760),
                'allowed_types' => explode(',', $_ENV['ALLOWED_IMAGE_TYPES'] ?? 'jpg,jpeg,png,gif,webp'),
            ],
            'security' => [
                'csrf_expire' => (int)($_ENV['CSRF_TOKEN_EXPIRE'] ?? 3600),
                'rate_limit_requests' => (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 100),
                'rate_limit_minutes' => (int)($_ENV['RATE_LIMIT_MINUTES'] ?? 60),
                'session_lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
                'session_secure' => filter_var($_ENV['SESSION_SECURE'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'session_http_only' => filter_var($_ENV['SESSION_HTTP_ONLY'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
                'session_same_site' => $_ENV['SESSION_SAME_SITE'] ?? 'strict',
            ],
        ];

        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config;
    }
}