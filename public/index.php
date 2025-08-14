<?php

declare(strict_types=1);

// Bootstrap file for CNA Upholstery Management System
define('ROOT_DIR', dirname(__DIR__));
define('SRC_DIR', ROOT_DIR . '/src');
define('PUBLIC_DIR', __DIR__);
define('STORAGE_DIR', ROOT_DIR . '/storage');
define('UPLOADS_DIR', ROOT_DIR . '/uploads');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoloader - basic implementation
spl_autoload_register(function ($class) {
    $prefix = 'CNA\\';
    $base_dir = SRC_DIR . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Try to load Composer autoloader if available
if (file_exists(ROOT_DIR . '/vendor/autoload.php')) {
    require_once ROOT_DIR . '/vendor/autoload.php';
}

// Load configuration
use CNA\Config\Config;
use CNA\Utils\Router;
use CNA\Utils\Session;
use CNA\Utils\Security;

try {
    // Load environment configuration
    Config::load();
    
    // Set timezone
    date_default_timezone_set(Config::get('app.timezone', 'UTC'));
    
    // Start session
    Session::start();
    
    // Security headers
    Security::setSecurityHeaders();
    
    // Initialize router
    $router = new Router();
    
    // Load routes
    require_once SRC_DIR . '/routes.php';
    
    // Handle request
    $router->handleRequest();
    
} catch (Throwable $e) {
    // Error handling
    if (Config::get('app.debug', false)) {
        echo '<h1>Application Error</h1>';
        echo '<pre>' . htmlspecialchars($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo '<h1>Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
    
    // Log error
    error_log("Application Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}