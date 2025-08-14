#!/usr/bin/env php
<?php

/**
 * Simple development server for CNA Upholstery Management System
 * Usage: php server.php [port]
 */

$port = $argv[1] ?? 8000;
$host = 'localhost';

// Check if port is available
$socket = @fsockopen($host, $port, $errno, $errstr, 1);
if ($socket) {
    fclose($socket);
    echo "Port {$port} is already in use.\n";
    exit(1);
}

$docroot = __DIR__ . '/public';
$router = __DIR__ . '/public/index.php';

echo "CNA Upholstery Management System\n";
echo "Development Server starting...\n";
echo "Document root: {$docroot}\n";
echo "Listening on: http://{$host}:{$port}\n";
echo "Press Ctrl+C to stop the server\n\n";

// Start the built-in PHP server
passthru("php -S {$host}:{$port} -t {$docroot} {$router}");