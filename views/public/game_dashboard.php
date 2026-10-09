<?php
require_once 'config/helpers.php';

$game_slug = $_GET['game'] ?? 'badminton';

// Fetch current game details
$stmt = $pdo->prepare("SELECT * FROM games WHERE slug = ?");
$stmt->execute([$game_slug]);
$game = $stmt->fetch();

if (!$game) {
    // Fallback to first game
    $game = $pdo->query("SELECT * FROM games ORDER BY id ASC LIMIT 1")->fetch();
    $game_slug = $game['slug'];
}

$title = htmlspecialchars($game['name']) . " Dashboard | HPCL Tournament 2026";

// Fetch all games for the Master Selector (FR-42)
$allGames = $pdo->query("SELECT id, name, slug, icon, category FROM games ORDER BY id ASC")->fetchAll();

// Fetch matches for this game
$stmt = $pdo->prepare("
    SELECT m.*, 
           t1.name as team1_name, u1.short_code as u1_code, u1.color_code as u1_color,
           t2.name as team2_name, u2.short_code as u2_code, u2.color_code as u2_color,
           tw.name as winner_name,
           f.name as facility_name, f.location
    FROM matches m
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    LEFT JOIN teams tw ON m.winner_id = tw.id
    LEFT JOIN facilities f ON m.facility_id = f.id
    WHERE m.game_id = ? AND m.is_published = 1
    ORDER BY FIELD(m.status, 'in_progress', 'scheduled', 'completed'), m.match_date ASC, m.start_time ASC
");
$stmt->execute([$game['id']]);
$gameMatches = $stmt->fetchAll();

// Compute Sport-Tailored Standings (FR-20, Venue Requirements)
$sportCategoryType = get_scorecard_type($game_slug);
if ($sportCategoryType === 'swimming') {
    $swimmingStandings = get_swimming_discipline_standings($pdo, $game['id']);
} elseif ($sportCategoryType === 'chess') {
    $chessStandings = get_chess_discipline_standings($pdo, $game['id']);
} elseif ($sportCategoryType === 'bridge') {
    $bridgeStandings = get_bridge_discipline_standings($pdo, $game['id']);
    $bridgeSwissData = get_bridge_swiss_matrix($pdo, $game['id']);
} else {
    $poolStandings = get_pool_standings($pdo, $game['id']);
}

// Fetch registered teams and athletes for this game (FR-21)
$stmt = $pdo->prepare("
    SELECT t.name as team_name, u.short_code as unit_code, u.color_code,
           COUNT(p.id) as player_count
    FROM teams t
    JOIN units u ON t.unit_id = u.id
    LEFT JOIN players p ON t.id = p.team_id
    WHERE t.game_id = ?
    GROUP BY t.id
    ORDER BY u.short_code ASC
");
$stmt->execute([$game['id']]);
$teamsList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Outfit -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    
    <style>
        /* High-Contrast Design System for Discipline Dashboard */
        body {
            background-color: #060d19 !important;
            color: #f8fafc !important;
        }

        .dashboard-glass-card {
            background: rgba(15, 23, 42, 0.94) !important;
            border: 1px solid rgba(255, 255, 255, 0.14) !important;
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
        }

        .match-card-wrapper {
            background: #0f172a !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .match-card-wrapper:hover {
            border-color: rgba(56, 189, 248, 0.45) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5) !important;
        }

        .match-card-live {
            border-left: 5px solid #ef4444 !important;
        }
        .match-card-final {
            border-left: 5px solid #10b981 !important;
        }
        .match-card-sched {
            border-left: 5px solid #0284c7 !important;
        }

        .match-header-bar {
            background: rgba(30, 41, 59, 0.85) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            padding: 10px 16px;
        }
        .match-footer-bar {
            background: rgba(30, 41, 59, 0.65) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
            padding: 9px 16px;
        }

        .vs-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.08);
            color: #fbbf24;
            font-weight: 800;
            font-size: 0.85rem;
            padding: 4px 12px;
            border-radius: 20px;
            border: 1px solid rgba(251, 191, 36, 0.35);
            letter-spacing: 1px;
        }

        .score-pill-box {
            background: rgba(30, 41, 59, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 8px 12px;
        }

        /* High Contrast Standings Table */
        .standings-table {
            width: 100%;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0 4px;
        }
        .standings-table thead th {
            background: rgba(30, 41, 59, 0.95) !important;
            color: #cbd5e1 !important;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            border: none !important;
            padding: 11px 12px;
        }
        .standings-table tbody tr {
            background: rgba(30, 41, 59, 0.6) !important;
            border-radius: 6px;
            transition: background 0.2s ease;
        }
        .standings-table tbody tr:hover {
            background: rgba(51, 65, 85, 0.85) !important;
        }
        .standings-table tbody tr td {
            border: none !important;
            padding: 11px 12px;
            vertical-align: middle;
            color: #f8fafc !important;
            font-size: 0.9rem;
        }
        .standings-table tbody tr.leader-row {
            background: rgba(245, 158, 11, 0.15) !important;
            border-left: 4px solid #f59e0b !important;
        }
        .standings-table tbody tr.leader-row td {
            color: #ffffff !important;
            font-weight: 600;
        }

        .stat-win {
            color: #34d399 !important;
            font-weight: 700;
        }
        .stat-loss {
            color: #f87171 !important;
            font-weight: 600;
        }
        .stat-sets {
            color: #cbd5e1 !important;
            font-weight: 500;
        }
        .stat-pts {
            color: #fbbf24 !important;
            font-weight: 800;
            font-size: 1.05rem;
        }

        /* Unit Cards */
        .unit-card-box {
            background: rgba(30, 41, 59, 0.8) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 8px;
            padding: 9px 12px;
            transition: border-color 0.2s ease;
        }
        .unit-card-box:hover {
            border-color: rgba(56, 189, 248, 0.5) !important;
        }

        /* Discipline Switcher Buttons */
        .sport-pill-btn {
            background: rgba(30, 41, 59, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: #e2e8f0 !important;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        .sport-pill-btn:hover {
            background: rgba(51, 65, 85, 0.95) !important;
            color: #ffffff !important;
            border-color: #38bdf8 !important;
            transform: translateY(-2px);
        }
        .sport-pill-btn.active {
            background: linear-gradient(135deg, #0284c7, #2563eb) !important;
            border-color: #38bdf8 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.45);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Official Bridge Swiss Scoreboard Styles */
        .bridge-matrix-card {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45);
            backdrop-filter: blur(12px);
        }
        .bridge-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .bridge-matrix-tbl {
            width: 100%;
            border-collapse: collapse;
            color: #f1f5f9;
            margin-bottom: 0;
            min-width: 860px;
        }
        .bridge-matrix-tbl th {
            background: linear-gradient(180deg, #1e293b, #0f172a);
            color: #e2e8f0;
            font-size: 0.82rem;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.05em;
            padding: 12px 10px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.12);
            white-space: nowrap;
        }
        .bridge-matrix-tbl td {
            padding: 8px 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            vertical-align: middle;
            background: rgba(15, 23, 42, 0.45);
        }
        .bridge-matrix-tbl tr:hover td {
            background: rgba(30, 41, 59, 0.7);
        }
        .bridge-matrix-tbl tr.leader-row td {
            background: rgba(234, 179, 8, 0.08);
        }
        .bridge-cell-split {
            position: relative;
            width: 82px;
            height: 54px;
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 6px;
            margin: 0 auto;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .bridge-cell-split:hover {
            transform: scale(1.05);
            border-color: #38bdf8;
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.25);
            z-index: 2;
        }
        .bridge-cell-split::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, transparent calc(50% - 1px), rgba(255, 255, 255, 0.35) 50%, transparent calc(50% + 1px));
            pointer-events: none;
        }
        .bridge-cell-cum-vp {
            position: absolute;
            top: 3px;
            left: 6px;
            font-weight: 800;
            font-size: 0.95rem;
            color: #38bdf8;
            font-family: monospace;
            line-height: 1;
        }
        .bridge-cell-opp-no {
            position: absolute;
            bottom: 3px;
            right: 6px;
            font-weight: 800;
            font-size: 0.8rem;
            color: #f59e0b;
            background: rgba(245, 158, 11, 0.15);
            padding: 1px 5px;
            border-radius: 3px;
            line-height: 1;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .bridge-cell-empty {
            position: relative;
            width: 82px;
            height: 54px;
            background: rgba(15, 23, 42, 0.35);
            border: 1px dashed rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            margin: 0 auto;
        }
        .bridge-cell-empty::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, transparent calc(50% - 1px), rgba(255, 255, 255, 0.15) 50%, transparent calc(50% + 1px));
        }
        .bridge-legend-badge {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand text-white d-flex align-items-center fw-bold" href="<?= BASE_URL ?>">
                <i class="fas fa-arrow-left me-2 text-warning"></i> HPCL Master Board
            </a>
            
            <div class="dropdown ms-auto me-3">
                <button class="btn btn-outline-warning btn-sm dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
                    <i class="<?= $game['icon'] ?> me-1"></i> Switch Sport
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg" style="border: 1px solid rgba(255,255,255,0.15);">
                    <?php foreach ($allGames as $g): ?>
                        <li>
                            <a class="dropdown-item <?= $g['slug'] === $game_slug ? 'active' : '' ?>" href="<?= BASE_URL ?>/<?= $g['slug'] ?>">
                                <i class="<?= $g['icon'] ?> me-2 text-warning"></i> <?= htmlspecialchars($g['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <button id="themeToggle" class="btn btn-sm btn-outline-light rounded-circle" style="width: 35px; height: 35px; padding: 0;">
                <i class="fas fa-sun"></i>
            </button>
        </div>
    </nav>

    <!-- Header Section -->
    <div class="container" style="margin-top: 100px; padding-bottom: 60px;">
        
        <div class="row mb-4">
            <div class="col-12 text-center">
                <span class="badge bg-warning text-dark text-uppercase px-3 py-2 fw-bold mb-3 shadow-sm" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                    <?= htmlspecialchars($game['category']) ?> &bull; <?= strtoupper(str_replace('_', ' ', $game['format'])) ?>
                </span>
                <h1 class="display-4 fw-bold text-uppercase text-white mb-2" style="text-shadow: 0 4px 20px rgba(0,0,0,0.6); letter-spacing: 1.5px;">
                    <?= htmlspecialchars($game['name']) ?>
                </h1>
                <p class="lead max-w-700 mx-auto" style="color: #cbd5e1; font-size: 1.05rem; line-height: 1.6;">
                    <?= htmlspecialchars($game['rules_summary']) ?>
                </p>
            </div>
        </div>

        <!-- Master Game Switcher Tabs -->
        <div class="d-flex flex-wrap justify-content-center gap-2 mb-5">
            <?php foreach ($allGames as $g): ?>
                <a href="<?= BASE_URL ?>/<?= $g['slug'] ?>" class="sport-pill-btn <?= $g['slug'] === $game_slug ? 'active' : '' ?>">
                    <i class="<?= $g['icon'] ?> me-2"></i> <?= htmlspecialchars($g['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($game_slug === 'swimming'): ?>
        <!-- Official Swimming Championship Day Schedule (FR-08 / Venue Protocol) -->
        <div class="dashboard-glass-card mb-4 p-0" style="overflow: hidden; border: 2px solid #0082c8; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <div class="d-flex justify-content-between align-items-center p-3" style="background: linear-gradient(135deg, #0082c8 0%, #005a9c 100%);">
                <div class="d-flex align-items-center">
                    <div class="me-3 p-2 bg-white bg-opacity-20 rounded-circle text-white">
                        <i class="fas fa-swimming-pool fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-white"><i class="fas fa-calendar-alt me-2"></i> Official Swimming Tournament Schedule</h5>
                        <small class="text-white-50">Day 1 Protocol: Reporting, Heats, Grand Finals & Medal Ceremony</small>
                    </div>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2 fw-bold font-monospace">12 PROGRAMME BLOCKS</span>
            </div>
            
            <style>
                .swim-table-exact {
                    width: 100%;
                    border-collapse: collapse;
                    background-color: #ffffff;
                    color: #000000;
                    font-size: 0.95rem;
                }
                .swim-table-exact th {
                    background-color: #0082c8 !important;
                    color: #ffffff !important;
                    font-weight: 600;
                    font-size: 0.95rem;
                    padding: 10px 16px;
                    border: 1px solid #0070ad;
                }
                .swim-table-exact td {
                    padding: 9px 16px;
                    border: 1px solid #bfdbfe;
                    color: #000000 !important;
                    vertical-align: middle;
                }
                .swim-table-exact tr.row-white {
                    background-color: #ffffff !important;
                }
                .swim-table-exact tr.row-blue {
                    background-color: #e2f1fc !important;
                }
                .swim-table-exact tr.row-yellow {
                    background-color: #fae57c !important;
                }
            </style>

            <div class="table-responsive">
                <table class="swim-table-exact">
                    <thead>
                        <tr>
                            <th style="width: 22%; text-align: center;">Time</th>
                            <th style="width: 38%; text-align: left;">Event / Race</th>
                            <th style="width: 24%; text-align: left;">Participants</th>
                            <th style="width: 16%; text-align: left;">Stage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="row-white">
                            <td style="text-align: center; font-weight: 600;">09:00 AM – 09:30 AM</td>
                            <td style="font-weight: 600;">Reporting & Warm-up</td>
                            <td style="font-weight: 600;">All Swimmers</td>
                            <td>Pool Area</td>
                        </tr>
                        <tr class="row-white">
                            <td style="text-align: center; font-weight: 600;">09:30 AM – 09:50 AM</td>
                            <td style="font-weight: 600;">50m Freestyle (Men) - Heat 1 (8 Swimmers)</td>
                            <td>Swimmers #1 to #8</td>
                            <td>Heats</td>
                        </tr>
                        <tr class="row-white">
                            <td style="text-align: center; font-weight: 600;">09:50 AM – 10:10 AM</td>
                            <td style="font-weight: 600;">50m Freestyle (Men) - Heat 2 (8 Swimmers)</td>
                            <td>Swimmers #9 to #16</td>
                            <td>Heats</td>
                        </tr>
                        <tr class="row-blue">
                            <td style="text-align: center; font-weight: 600;">10:15 AM – 10:35 AM</td>
                            <td style="font-weight: 600;">50m Freestyle (Women) - Direct Final</td>
                            <td style="font-weight: 600;">All 4 Swimmers</td>
                            <td style="font-weight: 600;">Final</td>
                        </tr>
                        <tr class="row-white">
                            <td style="text-align: center; font-weight: 600;">10:45 AM – 11:05 AM</td>
                            <td style="font-weight: 600;">50m Breaststroke (Men) - Heat 1 (5 Swimmers)</td>
                            <td>Swimmers #1 to #5</td>
                            <td>Heats</td>
                        </tr>
                        <tr class="row-white">
                            <td style="text-align: center; font-weight: 600;">11:05 AM – 11:25 AM</td>
                            <td style="font-weight: 600;">50m Breaststroke (Men) - Heat 2 (5 Swimmers)</td>
                            <td>Swimmers #6 to #10</td>
                            <td>Heats</td>
                        </tr>
                        <tr class="row-blue">
                            <td style="text-align: center; font-weight: 600;">11:30 AM – 11:45 AM</td>
                            <td style="font-weight: 600;">50m Breaststroke (Women) - Direct Final</td>
                            <td style="font-weight: 600;">All 2 Swimmers</td>
                            <td style="font-weight: 600;">Final</td>
                        </tr>
                        <tr class="row-blue">
                            <td style="text-align: center; font-weight: 600;">11:45 AM – 12:15 PM</td>
                            <td style="font-weight: 600;">Intermission / Buffer</td>
                            <td>Pool Open for Recovery</td>
                            <td>Buffer</td>
                        </tr>
                        <tr class="row-blue">
                            <td style="text-align: center; font-weight: 600;">12:15 PM – 12:35 PM</td>
                            <td style="font-weight: 600;">50m Freestyle (Men) - FINAL</td>
                            <td style="font-weight: 600;">Top Qualifiers</td>
                            <td style="font-weight: 600;">Final</td>
                        </tr>
                        <tr class="row-blue">
                            <td style="text-align: center; font-weight: 600;">12:40 PM – 01:00 PM</td>
                            <td style="font-weight: 600;">50m Breaststroke (Men) - FINAL</td>
                            <td style="font-weight: 600;">Top Qualifiers</td>
                            <td style="font-weight: 600;">Final</td>
                        </tr>
                        <tr class="row-yellow">
                            <td style="text-align: center; font-weight: 700;">01:00 PM – 02:00 PM</td>
                            <td style="font-weight: 700;">LUNCH BREAK</td>
                            <td style="font-weight: 700;">All Participants</td>
                            <td style="font-weight: 700;">Dining Hall</td>
                        </tr>
                        <tr class="row-white">
                            <td style="text-align: center; font-weight: 600;">02:00 PM – 02:30 PM</td>
                            <td style="font-weight: 600;">Medal Ceremony & Presentation</td>
                            <td style="font-weight: 600;">All Winners</td>
                            <td>Podium</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4">

            <?php if ($game_slug === 'bridge'): ?>
                <?php
                    $matrixRows = $bridgeSwissData['matrix'] ?? [];
                    $fixturesByRound = $bridgeSwissData['fixtures'] ?? [];
                    
                    // Sort by team_no 1 to 10 for the official scoreboard sheet view
                    usort($matrixRows, function($a, $b) {
                        return $a['team_no'] <=> $b['team_no'];
                    });

                    // Top leaders for highlight cards
                    $leaderList = $matrixRows;
                    usort($leaderList, function($a, $b) {
                        return $a['rank'] <=> $b['rank'];
                    });
                    $leader1 = $leaderList[0] ?? null;
                    $leader2 = $leaderList[1] ?? null;
                    $leader3 = $leaderList[2] ?? null;
                ?>
                <div class="col-12 mb-2">
                    <!-- Top Podium Highlights -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="dashboard-glass-card p-3 border-warning border-opacity-50 text-center">
                                <span class="badge bg-warning text-dark px-3 py-1 mb-2 fw-bold"><i class="fas fa-crown me-1"></i> Current Leader (Rank 1)</span>
                                <h4 class="fw-bold text-white mb-0"><?= htmlspecialchars($leader1['name'] ?? 'TBD') ?></h4>
                                <span class="fs-4 fw-bold text-warning"><?= number_format($leader1['total_vp'] ?? 0, 2) ?> <small class="fs-6 text-white-50">VPs</small></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-glass-card p-3 border-secondary border-opacity-50 text-center">
                                <span class="badge bg-secondary text-white px-3 py-1 mb-2 fw-bold"><i class="fas fa-medal me-1"></i> 2nd Position</span>
                                <h4 class="fw-bold text-white mb-0"><?= htmlspecialchars($leader2['name'] ?? 'TBD') ?></h4>
                                <span class="fs-4 fw-bold text-info"><?= number_format($leader2['total_vp'] ?? 0, 2) ?> <small class="fs-6 text-white-50">VPs</small></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="dashboard-glass-card p-3 border-danger border-opacity-50 text-center">
                                <span class="badge bg-danger text-white px-3 py-1 mb-2 fw-bold"><i class="fas fa-award me-1"></i> 3rd Position</span>
                                <h4 class="fw-bold text-white mb-0"><?= htmlspecialchars($leader3['name'] ?? 'TBD') ?></h4>
                                <span class="fs-4 fw-bold text-light"><?= number_format($leader3['total_vp'] ?? 0, 2) ?> <small class="fs-6 text-white-50">VPs</small></span>
                            </div>
                        </div>
                    </div>

                    <!-- Master Consolidated Scoreboard Card -->
                    <div class="bridge-matrix-card p-4 mb-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                            <div>
                                <span class="badge bg-warning text-dark text-uppercase px-3 py-1 fw-bold mb-1">Official Tournament Matrix</span>
                                <h3 class="fw-bold text-white mb-0">
                                    <i class="fas fa-clone text-info me-2"></i> HPCL ALL INDIA INTER UNIT SPORTS & GAMES TOURNAMENT 2026
                                </h3>
                                <div class="text-white-50 small mt-1">BRIDGE CONSOLIDATED SCOREBOARD • WBF Continuous 20-VP Scale</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="bridge-legend-badge">
                                    <span class="text-info fw-bold me-1">Top-Left:</span> Cumulative VPs
                                </span>
                                <span class="bridge-legend-badge">
                                    <span class="text-warning fw-bold me-1">Bottom-Right:</span> Opponent Team #
                                </span>
                                <span class="bridge-legend-badge">
                                    <span class="text-success fw-bold me-1">Match VP Sum:</span> 20.00
                                </span>
                            </div>
                        </div>

                        <div class="bridge-table-wrapper">
                            <table class="bridge-matrix-tbl">
                                <thead>
                                    <tr>
                                        <th style="width: 75px;">Team No.</th>
                                        <th style="text-align: left; min-width: 170px;">Team Name</th>
                                        <th style="width: 110px;">R-I</th>
                                        <th style="width: 110px;">R-II</th>
                                        <th style="width: 110px;">R-III</th>
                                        <th style="width: 110px;">R-IV</th>
                                        <th style="width: 110px;">R-V</th>
                                        <th style="width: 110px;">TOTAL</th>
                                        <th style="width: 80px;">RANK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matrixRows as $row): 
                                        $isFirst = ($row['rank'] === 1);
                                    ?>
                                        <tr class="<?= $isFirst ? 'leader-row' : '' ?>">
                                            <td class="text-center font-monospace fw-bold text-warning fs-5">
                                                <?= $row['team_no'] ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge me-2 px-2 py-1" style="background-color: <?= $row['color_code'] ?>; color: #fff; font-size: 0.78rem; font-weight: 700;">
                                                        <?= htmlspecialchars($row['short_code']) ?>
                                                    </span>
                                                    <div>
                                                        <div class="fw-bold text-white"><?= htmlspecialchars($row['name']) ?></div>
                                                        <div class="text-white-50 small" style="font-size: 0.75rem;"><?= htmlspecialchars($row['full_name']) ?></div>
                                                    </div>
                                                </div>
                                            </td>

                                            <?php for ($r = 1; $r <= 5; $r++): 
                                                $rnd = $row['rounds'][$r];
                                                $hasScore = ($rnd['cum_vp'] !== null);
                                            ?>
                                                <td class="text-center">
                                                    <?php if ($hasScore): ?>
                                                        <div class="bridge-cell-split" title="Round <?= $r ?>: <?= $rnd['round_vp'] !== null ? '+' . number_format($rnd['round_vp'], 2) . ' VP' : '' ?> vs Team #<?= $rnd['opp_no'] ?>">
                                                            <div class="bridge-cell-cum-vp"><?= number_format($rnd['cum_vp'], 2) ?></div>
                                                            <div class="bridge-cell-opp-no"><?= $rnd['opp_no'] ?></div>
                                                        </div>
                                                    <?php elseif ($rnd['opp_no']): ?>
                                                        <div class="bridge-cell-split" title="Scheduled: vs Team #<?= $rnd['opp_no'] ?>">
                                                            <div class="bridge-cell-cum-vp" style="color: #64748b;">-</div>
                                                            <div class="bridge-cell-opp-no"><?= $rnd['opp_no'] ?></div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="bridge-cell-empty" title="Round <?= $r ?> pending"></div>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endfor; ?>

                                            <td class="text-center">
                                                <span class="fs-5 fw-bold font-monospace text-info">
                                                    <?= number_format($row['total_vp'], 2) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($row['rank'] === 1): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1 fs-6 fw-bold"><i class="fas fa-crown"></i> 1</span>
                                                <?php elseif ($row['rank'] === 2): ?>
                                                    <span class="badge bg-secondary text-white px-2 py-1 fs-6 fw-bold"><i class="fas fa-medal"></i> 2</span>
                                                <?php elseif ($row['rank'] === 3): ?>
                                                    <span class="badge bg-danger text-white px-2 py-1 fs-6 fw-bold"><i class="fas fa-award"></i> 3</span>
                                                <?php else: ?>
                                                    <span class="text-white-50 fw-bold fs-6"><?= $row['rank'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Round-Wise Encounters / Matches Tabs -->
                    <div class="dashboard-glass-card p-4 mb-4">
                        <h4 class="fw-bold text-white mb-3">
                            <i class="fas fa-stream text-primary me-2"></i> Round-by-Round Table Encounters
                        </h4>
                        
                        <ul class="nav nav-pills mb-3 gap-2" id="bridgeRoundPills" role="tablist">
                            <?php 
                            $rRoman = [1 => 'R-I', 2 => 'R-II', 3 => 'R-III', 4 => 'R-IV', 5 => 'R-V'];
                            for ($r = 1; $r <= 5; $r++): 
                                $isCompleted = false;
                                foreach ($fixturesByRound[$r] ?? [] as $fix) {
                                    if ($fix['status'] === 'completed') $isCompleted = true;
                                }
                            ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link <?= $r === 1 ? 'active' : '' ?> fw-bold px-3 py-2" id="pill-round-<?= $r ?>-tab" data-bs-toggle="pill" data-bs-target="#pill-round-<?= $r ?>" type="button">
                                        <?= $rRoman[$r] ?> 
                                        <?php if ($isCompleted): ?>
                                            <span class="badge bg-success ms-1"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-1">5 Tables</span>
                                        <?php endif; ?>
                                    </button>
                                </li>
                            <?php endfor; ?>
                        </ul>

                        <div class="tab-content" id="bridgeRoundPillsContent">
                            <?php for ($r = 1; $r <= 5; $r++): ?>
                                <div class="tab-pane fade <?= $r === 1 ? 'show active' : '' ?>" id="pill-round-<?= $r ?>">
                                    <div class="row g-3">
                                        <?php foreach ($fixturesByRound[$r] ?? [] as $f): 
                                            $isDone = ($f['status'] === 'completed');
                                        ?>
                                            <div class="col-md-6 col-lg-4">
                                                <div class="p-3 rounded border" style="background: rgba(15, 23, 42, 0.6); border-color: rgba(255,255,255,0.1) !important;">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="badge bg-primary">Table <?= $f['table_no'] ?></span>
                                                        <?php if ($isDone): ?>
                                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i> Concluded</span>
                                                        <?php elseif ($f['status'] === 'in_progress'): ?>
                                                            <span class="badge bg-danger pulse-animation">Live Scoring</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Scheduled</span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center text-center my-2">
                                                        <div style="width: 44%;">
                                                            <span class="badge mb-1 px-2 py-1" style="background-color: <?= $f['team1_color'] ?>;">
                                                                #<?= $f['team1_num'] ?: '?' ?> <?= htmlspecialchars($f['team1_code']) ?>
                                                            </span>
                                                            <div class="fw-bold text-white small text-truncate"><?= htmlspecialchars($f['team1_name']) ?></div>
                                                            <?php if ($isDone): ?>
                                                                <div class="fs-5 fw-bold text-info font-monospace mt-1"><?= number_format($f['vps_a'] ?? 0, 2) ?> VP</div>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="text-white-50 fw-bold small">VS</div>

                                                        <div style="width: 44%;">
                                                            <span class="badge mb-1 px-2 py-1" style="background-color: <?= $f['team2_color'] ?>;">
                                                                #<?= $f['team2_num'] ?: '?' ?> <?= htmlspecialchars($f['team2_code']) ?>
                                                            </span>
                                                            <div class="fw-bold text-white small text-truncate"><?= htmlspecialchars($f['team2_name']) ?></div>
                                                            <?php if ($isDone): ?>
                                                                <div class="fs-5 fw-bold text-info font-monospace mt-1"><?= number_format($f['vps_b'] ?? 0, 2) ?> VP</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <?php if ($isDone && $f['winner_id']): ?>
                                                        <div class="text-center mt-2 pt-2 border-top border-secondary border-opacity-25 small text-warning fw-semibold">
                                                            <i class="fas fa-trophy me-1"></i> Winner: <?= $f['winner_id'] == $f['team1_id'] ? $f['team1_name'] : $f['team2_name'] ?>
                                                        </div>
                                                    <?php elseif ($isDone && !$f['winner_id']): ?>
                                                        <div class="text-center mt-2 pt-2 border-top border-secondary border-opacity-25 small text-info fw-semibold">
                                                            <i class="fas fa-handshake me-1"></i> Match Tied (10.00 - 10.00 VPs)
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Registered Teams Roster -->
                    <div class="dashboard-glass-card p-4">
                        <h5 class="fw-bold mb-3 text-white">
                            <i class="fas fa-users text-info me-2"></i> Participating Units & Teams in Bridge
                        </h5>
                        <div class="row g-2">
                            <?php foreach ($matrixRows as $tm): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="unit-card-box d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center text-truncate me-1">
                                            <span class="badge me-2" style="background-color: <?= $tm['color_code'] ?>; color: #fff; font-size: 0.75rem; font-weight: 700;">
                                                #<?= $tm['team_no'] ?> <?= htmlspecialchars($tm['short_code']) ?>
                                            </span>
                                            <span class="small text-white fw-semibold text-truncate"><?= htmlspecialchars($tm['name']) ?></span>
                                        </div>
                                        <span class="badge bg-secondary px-2 py-1 font-monospace" style="font-size: 0.75rem;">
                                            Rank <?= $tm['rank'] ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Standard 2-Column Layout for Other Disciplines -->
                <!-- Left Column: Matches & Brackets (Live, Scheduled, Completed) -->
                <div class="col-lg-7">
                    <div class="dashboard-glass-card mb-4">
                        <?php
                            $isSwim = ($game_slug === 'swimming');
                            $isChess = ($game_slug === 'chess');
                            $leftTitle = $isSwim ? 'Swimming Events & Heat Timesheets' : 
                                        ($isChess ? 'Chess Board Encounters' : 'Fixtures & Results');
                            $leftIcon = $isSwim ? 'fas fa-swimmer text-info' : 
                                       ($isChess ? 'fas fa-chess text-warning' : 'fas fa-stream text-primary');
                            $unitLabel = $isSwim ? 'Events' : ($isChess ? 'Encounters' : 'Matches');
                            $emptyTitle = $isSwim ? 'No swimming events published yet.' : 'No matches published for this discipline yet.';
                            $emptySub = $isSwim ? 'Event heats, lane allocations, and timesheets will appear here once scheduled.' : 'Draws will be published live following the Team Managers\' Meeting.';
                        ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0 fw-bold text-white">
                            <i class="<?= $leftIcon ?> me-2"></i> <?= htmlspecialchars($leftTitle) ?>
                        </h4>
                        <span class="badge bg-primary px-3 py-2 fw-bold font-monospace" style="font-size: 0.85rem;">
                            <?= count($gameMatches) ?> <?= $unitLabel ?>
                        </span>
                    </div>

                    <?php if (empty($gameMatches)): ?>
                        <div class="text-center py-5" style="color: #94a3b8;">
                            <i class="fas fa-calendar-times fa-3x mb-3 text-secondary"></i>
                            <h5 class="text-white"><?= htmlspecialchars($emptyTitle) ?></h5>
                            <p class="small" style="color: #cbd5e1;"><?= htmlspecialchars($emptySub) ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($gameMatches as $m): ?>
                            <?php 
                              $scores = json_decode($m['scores_json'] ?? '{}', true);
                              $summary = $scores['summary'] ?? ($m['status'] === 'in_progress' ? 'Match Live' : 'Scheduled');
                              $cardClass = $m['status'] === 'in_progress' ? 'match-card-live' : ($m['status'] === 'completed' ? 'match-card-final' : 'match-card-sched');
                            ?>
                            <div class="match-card-wrapper mb-3 <?= $cardClass ?>">
                                
                                <!-- Card Header with Date, Time, and Status -->
                                <div class="match-header-bar d-flex justify-content-between align-items-center">
                                    <span style="color: #e2e8f0; font-size: 0.85rem; font-weight: 500;">
                                        <i class="far fa-calendar-alt text-info me-1"></i> 
                                        <?= date('d M', strtotime($m['match_date'])) ?> &bull; 
                                        <?= date('H:i', strtotime($m['start_time'])) ?> – <?= date('H:i', strtotime($m['end_time'])) ?>
                                    </span>
                                    <div>
                                        <span class="badge px-2 py-1 me-1" style="background: rgba(255,255,255,0.15); color: #f8fafc; font-weight: 600;">
                                            <?= htmlspecialchars($m['round']) ?>
                                        </span>
                                        <?php if ($m['status'] === 'in_progress'): ?>
                                            <span class="badge bg-danger pulse-animation px-2 py-1 fw-bold">LIVE</span>
                                        <?php elseif ($m['status'] === 'completed'): ?>
                                            <span class="badge bg-success px-2 py-1 fw-bold">FINAL</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark px-2 py-1 fw-bold">SCHEDULED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Card Body with Team Names or Swimming Event Details -->
                                <div class="card-body p-3">
                                    <?php if ($isSwim): ?>
                                        <div class="swimming-event-box p-3 mb-2 rounded" style="background: rgba(14, 165, 233, 0.08); border: 1px solid rgba(56, 189, 248, 0.25);">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="badge bg-info text-dark fw-bold me-2"><i class="fas fa-swimmer me-1"></i> <?= htmlspecialchars($m['pool_name'] ?: 'Heats') ?></span>
                                                    <span class="text-white fw-bold" style="font-size: 1.05rem;"><?= htmlspecialchars($m['round']) ?></span>
                                                </div>
                                                <span class="badge px-2 py-1 font-monospace" style="background: rgba(255,255,255,0.15); color: #38bdf8;">
                                                    <i class="fas fa-users me-1"></i> <?= htmlspecialchars($scores['participants'] ?? 'Multi-zone') ?>
                                                </span>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center text-muted small gap-3">
                                                <span><i class="fas fa-users-cog text-info me-1"></i> Lineup: <strong class="text-white"><?= htmlspecialchars($scores['participants'] ?? 'All Qualified Swimmers') ?></strong></span>
                                                <span><i class="fas fa-water text-primary me-1"></i> Facility: <strong class="text-white"><?= htmlspecialchars($m['facility_name'] ?: 'Olympic Swimming Complex') ?></strong></span>
                                                <span><i class="fas fa-award text-warning me-1"></i> Stage: <strong class="text-white"><?= htmlspecialchars($m['pool_name'] ?: 'Race') ?></strong></span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="row align-items-center">
                                            <div class="col-5 text-center">
                                                <span class="badge mb-2 px-2 py-1" style="background-color: <?= $m['u1_color'] ?>; color: #fff; font-weight: 700; font-size: 0.8rem;">
                                                    <?= htmlspecialchars($m['u1_code']) ?>
                                                </span>
                                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem; line-height: 1.3;">
                                                    <?= htmlspecialchars($m['team1_name'] ?: 'TBD') ?>
                                                </h6>
                                            </div>
                                            <div class="col-2 text-center">
                                                <span class="vs-badge">VS</span>
                                            </div>
                                            <div class="col-5 text-center">
                                                <span class="badge mb-2 px-2 py-1" style="background-color: <?= $m['u2_color'] ?>; color: #fff; font-weight: 700; font-size: 0.8rem;">
                                                    <?= htmlspecialchars($m['u2_code']) ?>
                                                </span>
                                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.95rem; line-height: 1.3;">
                                                    <?= htmlspecialchars($m['team2_name'] ?: 'TBD') ?>
                                                </h6>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Digital Scorecard (Venue Specification) -->
                                    <?php $m['game_slug'] = $game['slug']; ?>
                                    <?= render_digital_scorecard($m, $scores) ?>
                                </div>

                                <!-- Card Footer with Venue & Scorecard Link -->
                                <div class="match-footer-bar d-flex justify-content-between align-items-center">
                                    <span style="color: #cbd5e1; font-size: 0.85rem;">
                                        <i class="fas fa-map-marker-alt text-warning me-1"></i> 
                                        <?= htmlspecialchars($m['facility_name'] ?: 'Balewadi Sports Complex') ?>
                                    </span>
                                    <?php if ($m['status'] === 'completed'): ?>
                                        <a href="<?= BASE_URL ?>/social" class="fw-bold text-decoration-none" style="color: #fbbf24; font-size: 0.85rem;">
                                            <i class="fas fa-camera me-1"></i> Create Scorecard
                                        </a>
                                    <?php endif; ?>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Auto-computed Standings & Discipline Stats -->
            <div class="col-lg-5">
                
                <!-- Discipline Standings / Medal Tally -->
                <div class="dashboard-glass-card mb-4">
                    <?php if ($game_slug === 'swimming'): ?>
                        <!-- SWIMMING: Medal Tally & Points Leaderboard -->
                        <h4 class="mb-2 fw-bold text-white">
                            <i class="fas fa-medal text-warning me-2"></i> Swimming Medal Tally & Points
                        </h4>
                        <p class="small mb-3" style="color: #94a3b8; line-height: 1.4;">
                            Ranked by 🥇 Gold, 🥈 Silver, 🥉 Bronze (5-3-1 Olympic Scheme for finals)
                        </p>
                        <div class="table-responsive">
                            <table class="standings-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>Unit / Zone</th>
                                        <th class="text-center" style="width: 50px;">🥇 G</th>
                                        <th class="text-center" style="width: 50px;">🥈 S</th>
                                        <th class="text-center" style="width: 50px;">🥉 B</th>
                                        <th class="text-end" style="width: 60px;">Pts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sIdx = 1; foreach ($swimmingStandings as $z => $s): ?>
                                        <tr class="<?= $sIdx === 1 ? 'leader-row' : '' ?>">
                                            <td class="text-center fw-bold">
                                                <?php if ($sIdx === 1): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-crown"></i> 1</span>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8;"><?= $sIdx ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge me-2 px-2 py-1" style="background-color: <?= $s['color_code'] ?>; color: #fff; font-size: 0.75rem; font-weight: 700;">
                                                        <?= htmlspecialchars($s['short_code']) ?>
                                                    </span>
                                                    <span class="fw-semibold text-white text-truncate" style="max-width: 170px;">
                                                        <?= htmlspecialchars($s['name']) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center text-warning fw-bold"><?= $s['gold'] ?></td>
                                            <td class="text-center text-light fw-bold"><?= $s['silver'] ?></td>
                                            <td class="text-center fw-bold" style="color: #f59e0b;"><?= $s['bronze'] ?></td>
                                            <td class="text-end stat-pts"><?= $s['points'] ?></td>
                                        </tr>
                                    <?php $sIdx++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($game_slug === 'chess'): ?>
                        <!-- CHESS: Match Points & Game Points -->
                        <h4 class="mb-2 fw-bold text-white">
                            <i class="fas fa-chess text-warning me-2"></i> Chess Standings & Board Points
                        </h4>
                        <p class="small mb-3" style="color: #94a3b8; line-height: 1.4;">
                            Auto-computed official standings (1.0 pt for Win, 0.5 pt for Draw, 0.0 for Loss)
                        </p>
                        <div class="table-responsive">
                            <table class="standings-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>Unit / Team</th>
                                        <th class="text-center" style="width: 45px;">P</th>
                                        <th class="text-center" style="width: 45px;">W</th>
                                        <th class="text-center" style="width: 45px;">D</th>
                                        <th class="text-center" style="width: 45px;">L</th>
                                        <th class="text-end" style="width: 60px;">Pts</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $cIdx = 1; foreach ($chessStandings as $c): ?>
                                        <tr class="<?= $cIdx === 1 ? 'leader-row' : '' ?>">
                                            <td class="text-center fw-bold">
                                                <?php if ($cIdx === 1): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-crown"></i> 1</span>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8;"><?= $cIdx ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge me-2 px-2 py-1" style="background-color: <?= $c['color_code'] ?>; color: #fff; font-size: 0.75rem; font-weight: 700;">
                                                        <?= htmlspecialchars($c['short_code']) ?>
                                                    </span>
                                                    <span class="fw-semibold text-white text-truncate" style="max-width: 170px;">
                                                        <?= htmlspecialchars($c['name']) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center" style="color: #e2e8f0; font-weight: 600;"><?= $c['played'] ?></td>
                                            <td class="text-center stat-win"><?= $c['won'] ?></td>
                                            <td class="text-center text-info fw-bold"><?= $c['drawn'] ?></td>
                                            <td class="text-center stat-loss"><?= $c['lost'] ?></td>
                                            <td class="text-end stat-pts"><?= number_format($c['points'], 1) ?></td>
                                        </tr>
                                    <?php $cIdx++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php elseif ($game_slug === 'bridge'): ?>
                        <!-- BRIDGE: IMPs & Victory Points -->
                        <h4 class="mb-2 fw-bold text-white">
                            <i class="fas fa-clone text-info me-2"></i> Bridge Leaderboard (IMPs & VPs)
                        </h4>
                        <p class="small mb-3" style="color: #94a3b8; line-height: 1.4;">
                            Ranked by Victory Points (VPs) and International Match Points (IMPs difference)
                        </p>
                        <div class="table-responsive">
                            <table class="standings-table">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th>Unit / Team</th>
                                        <th class="text-center" style="width: 65px;">Sessions</th>
                                        <th class="text-center" style="width: 75px;">IMPs Diff</th>
                                        <th class="text-end" style="width: 65px;">VPs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $bIdx = 1; foreach ($bridgeStandings as $b): 
                                        $diff = $b['imps_for'] - $b['imps_against'];
                                    ?>
                                        <tr class="<?= $bIdx === 1 ? 'leader-row' : '' ?>">
                                            <td class="text-center fw-bold">
                                                <?php if ($bIdx === 1): ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-crown"></i> 1</span>
                                                <?php else: ?>
                                                    <span style="color: #94a3b8;"><?= $bIdx ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge me-2 px-2 py-1" style="background-color: <?= $b['color_code'] ?>; color: #fff; font-size: 0.75rem; font-weight: 700;">
                                                        <?= htmlspecialchars($b['short_code']) ?>
                                                    </span>
                                                    <span class="fw-semibold text-white text-truncate" style="max-width: 170px;">
                                                        <?= htmlspecialchars($b['name']) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-center" style="color: #e2e8f0; font-weight: 600;"><?= $b['sessions'] ?></td>
                                            <td class="text-center fw-bold <?= $diff > 0 ? 'stat-win' : ($diff < 0 ? 'stat-loss' : 'text-muted') ?>">
                                                <?= $diff > 0 ? "+$diff" : $diff ?>
                                            </td>
                                            <td class="text-end stat-pts"><?= number_format($b['vps'], 2) ?></td>
                                        </tr>
                                    <?php $bIdx++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <!-- Standard Racket Sports (Badminton, Table Tennis, Lawn Tennis, Carrom) -->
                        <h4 class="mb-2 fw-bold text-white">
                            <i class="fas fa-list-ol text-warning me-2"></i> Current Pool Standings
                        </h4>
                        <p class="small mb-3" style="color: #94a3b8; line-height: 1.4;">
                            Auto-computed from completed match scores (2 pts for Win, Sets difference tiebreak)
                        </p>

                        <?php if (empty($poolStandings)): ?>
                            <p class="small" style="color: #cbd5e1;">Standings will activate as pool matches conclude.</p>
                        <?php else: ?>
                            <?php foreach ($poolStandings as $poolName => $teams): ?>
                                <h6 class="text-warning fw-bold text-uppercase mt-4 mb-2 d-flex align-items-center" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-trophy me-2"></i> Pool <?= htmlspecialchars($poolName) ?> Standings
                                </h6>
                                <div class="table-responsive">
                                    <table class="standings-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;" class="text-center">#</th>
                                                <th>Unit / Team</th>
                                                <th class="text-center" style="width: 45px;">P</th>
                                                <th class="text-center" style="width: 45px;">W</th>
                                                <th class="text-center" style="width: 45px;">L</th>
                                                <th class="text-center" style="width: 70px;">Sets</th>
                                                <th class="text-end" style="width: 55px;">Pts</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($teams as $idx => $t): ?>
                                                <tr class="<?= $idx === 0 ? 'leader-row' : '' ?>">
                                                    <td class="text-center fw-bold">
                                                        <?php if ($idx === 0): ?>
                                                            <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-crown"></i> 1</span>
                                                        <?php else: ?>
                                                            <span style="color: #94a3b8;"><?= $idx + 1 ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge me-2 px-2 py-1" style="background-color: <?= $t['color_code'] ?>; color: #fff; font-size: 0.75rem; font-weight: 700;">
                                                                <?= htmlspecialchars($t['short_code']) ?>
                                                            </span>
                                                            <span class="fw-semibold text-white text-truncate" style="max-width: 170px;">
                                                                <?= htmlspecialchars($t['name']) ?>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center" style="color: #e2e8f0; font-weight: 600;"><?= $t['played'] ?></td>
                                                    <td class="text-center stat-win"><?= $t['won'] ?></td>
                                                    <td class="text-center stat-loss"><?= $t['lost'] ?></td>
                                                    <td class="text-center stat-sets"><?= $t['sets_for'] ?>:<?= $t['sets_against'] ?></td>
                                                    <td class="text-end stat-pts"><?= $t['points'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Participating Units & Roster Info -->
                <div class="dashboard-glass-card">
                    <h5 class="fw-bold mb-3 text-white">
                        <i class="fas fa-users text-info me-2"></i> Registered Units in <?= htmlspecialchars($game['name']) ?>
                    </h5>
                    <div class="row g-2">
                        <?php foreach ($teamsList as $tm): ?>
                            <div class="col-6">
                                <div class="unit-card-box d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center text-truncate me-1">
                                        <span class="badge me-2" style="background-color: <?= $tm['color_code'] ?>; color: #fff; font-size: 0.75rem; font-weight: 700;">
                                            <?= htmlspecialchars($tm['unit_code']) ?>
                                        </span>
                                        <span class="small text-white fw-semibold text-truncate"><?= htmlspecialchars($tm['team_name']) ?></span>
                                    </div>
                                    <span class="badge bg-primary px-2 py-1 font-monospace" style="font-size: 0.75rem; white-space: nowrap;">
                                        <?= $tm['player_count'] ?> athletes
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            <?php endif; ?>

        </div>
        
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Toggle -->
    <script>
        const themeToggleBtn = document.getElementById('themeToggle');
        const body = document.body;
        const icon = themeToggleBtn.querySelector('i');

        if (localStorage.getItem('theme') === 'light') {
            body.classList.add('light-mode');
            icon.classList.replace('fa-sun', 'fa-moon');
            themeToggleBtn.classList.replace('btn-outline-light', 'btn-outline-dark');
        }

        themeToggleBtn.addEventListener('click', () => {
            body.classList.toggle('light-mode');
            if (body.classList.contains('light-mode')) {
                localStorage.setItem('theme', 'light');
                icon.classList.replace('fa-sun', 'fa-moon');
                themeToggleBtn.classList.replace('btn-outline-light', 'btn-outline-dark');
            } else {
                localStorage.setItem('theme', 'dark');
                icon.classList.replace('fa-moon', 'fa-sun');
                themeToggleBtn.classList.replace('btn-outline-dark', 'btn-outline-light');
            }
        });
    </script>
</body>
</html>
