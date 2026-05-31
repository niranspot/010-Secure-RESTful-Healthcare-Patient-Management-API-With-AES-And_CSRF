<?php
// Simple .env parser helper
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Define accessible configuration constants
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? '');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');


define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'default_secret');
define('AES_KEY', $_ENV['AES_KEY'] ?? 'default_aes_key');


define('CSRF_EXPIRY', (int)($_ENV['CSRF_EXPIRY'] ?? 3600));
define('JWT_EXPIRY', (int)($_ENV['JWT_EXPIRY'] ?? 900));
define('REFRESH_TOKEN_EXPIRY', (int)($_ENV['REFRESH_TOKEN_EXPIRY'] ?? 172800));