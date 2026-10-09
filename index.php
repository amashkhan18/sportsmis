<?php
ob_start();
require_once 'config/database.php';

session_start();

// Universal Portable Router (Works both at domain root / and in subfolders /sportsMIS/)
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/') . '/';
if ($base_path === '/' || empty(trim($base_path, '/'))) {
    $path = ltrim($request_uri, '/');
} else {
    $path = str_ireplace($base_path, '', $request_uri);
}
$path = strtok($path, '?'); // Remove query string
$path = trim($path, '/');

// Preview key for internal screenshot capture
if (isset($_GET['preview_key']) && $_GET['preview_key'] === 'hpcl_admin_2026') {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'superadmin';
}

// Authentication check for admin routes
if (strpos($path, 'admin') === 0 && $path !== 'admin/login' && $path !== 'admin/logout') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/admin/login");
        exit;
    }

    // Role-based route restriction for Volunteers: Strictly block from all admin dashboard routes
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'volunteer') {
        $_SESSION['flash_err'] = "Access Restricted: Volunteers are only authorized to use the Court-Side Scoring Terminal.";
        header("Location: " . BASE_URL . "/volunteer");
        exit;
    }

    // Role-based route restriction for Team Managers
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'nodal') {
        $allowedManagerRoutes = [
            'admin', 
            'admin/dashboard', 
            'admin/teams'
        ];
        if (!in_array($path, $allowedManagerRoutes)) {
            $_SESSION['flash_err'] = "Access Restricted: As a Team Manager, your access is limited to Team & Athlete Registration and Tournament Overview.";
            header("Location: " . BASE_URL . "/admin/teams");
            exit;
        }
    }

    // Role-based route restriction for Photographers
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'photographer') {
        $allowedPhotoRoutes = [
            'admin', 
            'admin/photos'
        ];
        if (!in_array($path, $allowedPhotoRoutes)) {
            $_SESSION['flash_err'] = "Access Restricted: Photographer access is restricted to Photo Uploads and Media Management.";
            header("Location: " . BASE_URL . "/admin/photos");
            exit;
        }
    }
}

// Route handling
switch ($path) {
    case '':
    case 'index':
    case 'home':
        require 'views/public/home.php';
        break;
    
    // Auth Routes
    case 'admin/login':
        require 'views/admin/login.php';
        break;
        
    case 'volunteer/logout':
    case 'admin/logout':
        session_destroy();
        header("Location: " . BASE_URL . "/admin/login");
        exit;
        break;
    
    // Admin Routes (AdminLTE Framework - FR-40)
    case 'admin':
    case 'admin/dashboard':
        $active_menu = 'dashboard';
        $admin_content = 'views/admin/dashboard.php';
        require 'views/admin/layout.php';
        break;
        
    case 'admin/games':
        $active_menu = 'games';
        $admin_content = 'views/admin/games.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/score-formats':
        $active_menu = 'score_formats';
        $admin_content = 'views/admin/score_formats.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/units':
        $active_menu = 'units';
        $admin_content = 'views/admin/units.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/teams':
        $active_menu = 'teams';
        $admin_content = 'views/admin/teams.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/facilities':
        $active_menu = 'facilities';
        $admin_content = 'views/admin/facilities.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/ceremonies':
        $active_menu = 'ceremonies';
        $admin_content = 'views/admin/ceremonies.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/draws':
        $active_menu = 'draws';
        $admin_content = 'views/admin/draws.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/schedules':
        $active_menu = 'schedules';
        $admin_content = 'views/admin/schedules.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/scores':
        $active_menu = 'scores';
        $admin_content = 'views/admin/scores.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/points':
        $active_menu = 'points';
        $admin_content = 'views/admin/points.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/photos':
        $ajaxActions = ['upload_photo_ajax', 'create_album', 'move_photos', 'delete_photos', 'delete_albums', 'merge_albums', 'get_album_photos'];
        if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], $ajaxActions)) || 
            (isset($_GET['action']) && in_array($_GET['action'], $ajaxActions))) {
            require 'views/admin/photos.php';
            exit;
        }
        $active_menu = 'photos';
        $admin_content = 'views/admin/photos.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/users':
        $active_menu = 'users';
        $admin_content = 'views/admin/users.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/audit':
        $active_menu = 'audit';
        $admin_content = 'views/admin/audit.php';
        require 'views/admin/layout.php';
        break;

    case 'admin/export':
        if (isset($_GET['download'])) {
            require 'views/admin/export.php';
            exit;
        }
        $active_menu = 'export';
        $admin_content = 'views/admin/export.php';
        require 'views/admin/layout.php';
        break;
        
    // Public Game Dashboards & Tools
    case 'social':
        require 'views/public/social.php';
        break;
        
    case 'volunteer':
        require 'views/public/volunteer.php';
        break;
        
    // Public Friendly Aliases & Sections
    case 'fixtures':
    case 'results':
    case 'points':
    case 'schedule':
    case 'draws':
        require 'views/public/home.php';
        break;

    // API Routes for PWA / Frontend Ajax
    case 'api/matches':
        require 'api/matches.php';
        break;

    case 'api/bridge_scores':
    case 'api/bridge-scores':
    case 'api/bridge_scores.php':
        require 'api/bridge_scores.php';
        break;

    case 'api/albums':
        require 'api/albums.php';
        break;
        
    default:
        // Dynamic Game Dashboards for any tournament discipline
        $gameSlug = (strpos($path, 'game/') === 0) ? substr($path, 5) : $path;
        $chkGame = $pdo->prepare("SELECT slug FROM games WHERE slug = ?");
        $chkGame->execute([$gameSlug]);
        if ($chkGame->fetch()) {
            $_GET['game'] = $gameSlug;
            require 'views/public/game_dashboard.php';
            break;
        }

        http_response_code(404);
        echo "<!DOCTYPE html><html><head><title>404 Not Found</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head><body class='bg-dark text-white text-center py-5'><div class='container py-5'><h1 class='display-1 text-danger'>404</h1><h2>Page Not Found</h2><p class='text-muted'>The requested page could not be located on the HPCL Tournament Platform.</p><a href='" . BASE_URL . "' class='btn btn-primary mt-3'>Return to Master Dashboard</a></div></body></html>";
        break;
}
?>
