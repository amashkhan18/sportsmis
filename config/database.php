<?php
// Smart Dual Environment Configuration (Localhost / LAN & Live Production)
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$host_only = explode(':', $http_host)[0];

// Detect production vs local environment
$is_production = str_contains($host_only, 'abhilashaindia.com') || getenv('HOSTINGER_ENV') || getenv('PRODUCTION_ENV');
$is_local = !$is_production && (
    in_array($host_only, ['localhost', '127.0.0.1', '::1', '']) ||
    str_starts_with($host_only, '192.168.') ||
    str_starts_with($host_only, '172.') ||
    str_starts_with($host_only, '10.')
);

$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
$protocol = $is_https ? 'https://' : 'http://';

if ($is_local) {
    // Local XAMPP Environment (supports localhost, 127.0.0.1, and Wi-Fi / Hotspot LAN IPs)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'sportsmis');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
    define('BASE_URL', $protocol . ($http_host ?: 'localhost') . '/sportsmis');
} else {
    // Live Production Environment (Hostinger hp.abhilashaindia.com or custom domain)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'u930713328_hp');
    define('DB_USER', getenv('DB_USER') ?: 'u930713328_hp');
    define('DB_PASS', getenv('DB_PASS') ?: 'Amzoo@2010');

    // Auto-detect if deployed at root or in a subfolder
    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $subPath = ($scriptDir === '' || $scriptDir === '.') ? '' : '/' . $scriptDir;
    define('BASE_URL', $protocol . ($http_host ?: 'hp.abhilashaindia.com') . $subPath);
}

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>