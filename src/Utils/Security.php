<?php

namespace CNA\Utils;

use CNA\Config\Config;

class Security
{
    public static function setSecurityHeaders(): void
    {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https: " . Config::get('aws.url', '') . "; " .
               "connect-src 'self'";
        
        header("Content-Security-Policy: {$csp}");
        
        // HTTPS enforcement in production
        if (Config::get('app.env') === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validatePhone(string $phone): bool
    {
        // Basic phone validation - remove all non-digits and check length
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function rateLimit(string $identifier, int $maxRequests = 100, int $timeWindow = 3600): bool
    {
        $key = 'rate_limit_' . md5($identifier);
        $current = Session::get($key, ['count' => 0, 'reset' => time() + $timeWindow]);
        
        // Reset if time window has passed
        if (time() >= $current['reset']) {
            $current = ['count' => 0, 'reset' => time() + $timeWindow];
        }
        
        $current['count']++;
        Session::put($key, $current);
        
        return $current['count'] <= $maxRequests;
    }

    public static function logSecurityEvent(string $event, array $data = [], bool $success = true): void
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => Session::user()['id'] ?? null,
            'success' => $success,
            'data' => $data
        ];
        
        // Log to file (in production, this should go to database)
        $logFile = STORAGE_DIR . '/logs/security.log';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }

    public static function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function preventXSS(string $input): string
    {
        // Remove script tags and event handlers
        $input = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $input);
        $input = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        
        return $input;
    }

    public static function generateUUID(): string
    {
        // Simple UUID v4 generation
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}