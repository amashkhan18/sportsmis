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

    // Auto-migration safeguard for athlete names in open categories
    try {
        $colCheck = $pdo->query("SHOW COLUMNS FROM matches LIKE 'athlete1_name'")->fetch();
        if (!$colCheck) {
            $pdo->exec("ALTER TABLE matches ADD COLUMN athlete1_name VARCHAR(150) NULL AFTER team2_id, ADD COLUMN athlete2_name VARCHAR(150) NULL AFTER athlete1_name");
        }
    } catch (Exception $e) {
        // Suppress migration errors if already present or restricted
    }

    // Auto-migration safeguard for swimming relay matches
    try {
        $rCheck = $pdo->query("SELECT COUNT(*) FROM matches WHERE game_id = 781 AND round LIKE '%Relay%'")->fetchColumn();
        if ($rCheck == 0) {
            $sqlFile = __DIR__ . '/../database/insert_swimming_relay_results.sql';
            if (file_exists($sqlFile)) {
                $queries = array_filter(array_map('trim', explode(';', file_get_contents($sqlFile))));
                foreach ($queries as $q) {
                    if (!empty($q)) {
                        $pdo->exec($q);
                    }
                }
            }
        }
    } catch (Exception $e) {
        // Suppress migration errors
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>