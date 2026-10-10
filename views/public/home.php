<?php
require_once 'config/helpers.php';

$title = "HPCL 20th All India Tournament | Master Public Dashboard";

// Live Leaderboard (FR-22)
$leaderboard = get_overall_championship_leaderboard($pdo);
$leaderboardMode = get_system_setting($pdo, 'leaderboard_mode', 'auto');

// Live & In-Progress Matches across all games
$liveMatches = $pdo->query("
    SELECT m.*, g.name as game_name, g.slug as game_slug,
           COALESCE(
               CASE 
                   WHEN m.winner_id = m.team1_id AND m.athlete1_name IS NOT NULL AND m.athlete1_name != '' THEN m.athlete1_name
                   WHEN m.winner_id = m.team2_id AND m.athlete2_name IS NOT NULL AND m.athlete2_name != '' THEN m.athlete2_name
                   ELSE tw.name
               END
           ) as winner_name,
           COALESCE(NULLIF(m.athlete1_name, ''), t1.name) as team1_name, u1.short_code as u1_code, u1.color_code as u1_color,
           COALESCE(NULLIF(m.athlete2_name, ''), t2.name) as team2_name, u2.short_code as u2_code, u2.color_code as u2_color,
           f.name as facility_name
    FROM matches m
    JOIN games g ON m.game_id = g.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    LEFT JOIN teams tw ON m.winner_id = tw.id
    LEFT JOIN facilities f ON m.facility_id = f.id
    WHERE m.status IN ('in_progress', 'completed')
    ORDER BY FIELD(m.status, 'in_progress', 'completed'), m.match_date DESC, m.start_time DESC
    LIMIT 6
")->fetchAll();

// Master Ceremonies & Events (FR-06)
$masterEvents = $pdo->query("SELECT * FROM master_events ORDER BY date ASC, start_time ASC")->fetchAll();

// All Games
$allGames = $pdo->query("SELECT * FROM games ORDER BY id ASC")->fetchAll();

// Tournament Albums (FR-30, FR-32) - Batch collections with progressive dual-stream
$galleryAlbums = $pdo->query("
    SELECT a.*, g.name as game_name,
           COUNT(p.id) as photo_count,
           COALESCE(cp.file_path, MIN(p.file_path)) as cover_file_path,
           COALESCE(cp.compressed_path, MIN(p.compressed_path)) as cover_compressed_path
    FROM albums a
    LEFT JOIN games g ON a.game_id = g.id
    LEFT JOIN photos cp ON a.cover_photo_id = cp.id
    LEFT JOIN photos p ON a.id = p.album_id
    GROUP BY a.id
    ORDER BY a.created_at DESC, a.id DESC
    LIMIT 24
")->fetchAll();

// Tournament Photo Gallery (FR-30, FR-32) - Multi-category photos with progressive loading
$galleryPhotos = $pdo->query("
    SELECT p.*, g.name as game_name, m.round as match_round,
           t1.name as t1_name, t2.name as t2_name,
           u1.short_code as u1_code, u2.short_code as u2_code
    FROM photos p
    LEFT JOIN games g ON p.game_id = g.id
    LEFT JOIN matches m ON p.match_id = m.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    ORDER BY p.uploaded_at DESC
    LIMIT 32
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    
    <!-- PWA Manifest & Theme Color -->
    <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
    <meta name="theme-color" content="#003366">
    <link rel="apple-touch-icon" href="https://upload.wikimedia.org/wikipedia/en/thumb/9/90/Hindustan_Petroleum_Logo.svg/192px-Hindustan_Petroleum_Logo.svg.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        .ticker-wrap {
            width: 100%;
            overflow: hidden;
            background: rgba(0, 20, 50, 0.95);
            border-bottom: 2px solid rgba(255, 215, 0, 0.4);
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
        .ticker-inner {
            display: flex;
            white-space: nowrap;
            animation: ticker 35s linear infinite;
        }
        .ticker-item {
            padding: 8px 30px;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }
        @keyframes ticker {
            0% { transform: translate3d(0, 0, 0); }
            100% { transform: translate3d(-50%, 0, 0); }
        }
        .gold-glow {
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.6);
        }
        .rank-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Premium Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>">
                <i class="fas fa-medal text-warning me-2" style="font-size: 1.4rem;"></i>
                <span class="text-white font-weight-bold">HPCL Tournament 2026</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#live-scores">Live Scores</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#games">Games</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#leaderboard">Leaderboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#schedule">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#gallery"><i class="fas fa-camera text-warning me-1"></i> Gallery</a></li>
                    <li class="nav-item"><a class="nav-link text-warning" href="<?= BASE_URL ?>/social"><i class="fas fa-magic me-1"></i> Social Studio</a></li>
                    <li class="nav-item ms-lg-3 mt-2 mt-lg-0 d-flex align-items-center">
                        <button id="themeToggle" class="btn btn-sm btn-outline-light rounded-circle me-3" style="width: 35px; height: 35px; padding: 0;" title="Toggle Light/Dark Mode">
                            <i class="fas fa-sun"></i>
                        </button>
                        <a class="custom-btn btn-primary-custom btn-sm" href="<?= BASE_URL ?>/admin">
                            <i class="fas fa-lock me-1"></i> Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section utilizing the provided Design.jpeg -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container hero-content">
            <div class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-3">
                <i class="fas fa-fire me-1"></i> Official Spectator Portal
            </div>
            <h1 class="hero-title">20th All India Inter Unit<br>Sports & Games</h1>
            <p class="hero-subtitle">Witness Corporate Athletics, Camaraderie & Championship Glory</p>
            <div class="hero-date">
                <i class="far fa-calendar-alt me-2 text-warning"></i> 8th – 10th October 2026 &nbsp;|&nbsp; 
                <i class="fas fa-map-marker-alt ms-2 me-1 text-danger"></i> Shree Shiv Chhatrapati Sports Complex, Balewadi, Pune
            </div>
            <div class="mt-4 d-flex justify-content-center gap-3">
                <a href="#live-scores" class="custom-btn btn-primary-custom">
                    <i class="fas fa-broadcast-tower me-2"></i> Watch Live Scores
                </a>
                <a href="#leaderboard" class="custom-btn btn-outline-custom">
                    <i class="fas fa-trophy me-2 text-warning"></i> Overall Championship
                </a>
            </div>
        </div>
    </section>

    <!-- LIVE TICKER -->
    <div class="ticker-wrap">
        <div class="ticker-inner text-white">
            <?php foreach ($liveMatches as $lm): ?>
                <?php 
                  $scores = json_decode($lm['scores_json'] ?? '{}', true);
                  $sum = $scores['summary'] ?? ($lm['status'] === 'in_progress' ? 'Match Live' : 'Completed');
                ?>
                <div class="ticker-item">
                    <?php if ($lm['status'] === 'in_progress'): ?>
                        <span class="badge bg-danger me-2 pulse-animation">LIVE</span>
                    <?php else: ?>
                        <span class="badge bg-success me-2">FINAL</span>
                    <?php endif; ?>
                    <strong class="text-warning me-2"><?= htmlspecialchars($lm['game_name']) ?>:</strong>
                    <span><?= htmlspecialchars($lm['u1_code']) ?> vs <?= htmlspecialchars($lm['u2_code']) ?></span>
                    <span class="ms-2 badge bg-dark text-info border border-secondary"><?= htmlspecialchars($sum) ?></span>
                </div>
            <?php endforeach; ?>
            <!-- Repeated for seamless loop -->
            <?php foreach ($liveMatches as $lm): ?>
                <?php 
                  $scores = json_decode($lm['scores_json'] ?? '{}', true);
                  $sum = $scores['summary'] ?? ($lm['status'] === 'in_progress' ? 'Match Live' : 'Completed');
                ?>
                <div class="ticker-item">
                    <?php if ($lm['status'] === 'in_progress'): ?>
                        <span class="badge bg-danger me-2 pulse-animation">LIVE</span>
                    <?php else: ?>
                        <span class="badge bg-success me-2">FINAL</span>
                    <?php endif; ?>
                    <strong class="text-warning me-2"><?= htmlspecialchars($lm['game_name']) ?>:</strong>
                    <span><?= htmlspecialchars($lm['u1_code']) ?> vs <?= htmlspecialchars($lm['u2_code']) ?></span>
                    <span class="ms-2 badge bg-dark text-info border border-secondary"><?= htmlspecialchars($sum) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Features / Quick Links Section -->
    <section class="features-section py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="glass-card">
                        <div class="icon icon-live"><i class="fas fa-broadcast-tower"></i></div>
                        <h4>Real-Time Scoring</h4>
                        <p>Follow court-by-court set scores, chess boards, swimming heat times, and brackets updated instantly.</p>
                        <a href="#live-scores" class="custom-btn btn-outline-custom">Live Matches</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card">
                        <div class="icon icon-trophy"><i class="fas fa-trophy"></i></div>
                        <h4>5-3-1 Trophy Race</h4>
                        <p>Real-time Overall Championship leaderboard aggregating points across all 8 HPCL units.</p>
                        <a href="#leaderboard" class="custom-btn btn-outline-custom">View Leaderboard</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="glass-card">
                        <div class="icon icon-social"><i class="fas fa-camera-retro"></i></div>
                        <h4>Social Content Studio</h4>
                        <p>Self-service tool to generate branded match scorecards with photo overlays, crop/drag, and custom taglines.</p>
                        <a href="<?= BASE_URL ?>/social" class="custom-btn btn-outline-custom">Generate Scorecard</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LIVE & RECENT MATCHES SECTION -->
    <section id="live-scores" class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1"><i class="fas fa-broadcast-tower text-danger me-2"></i> Live & Featured Results</h2>
                    <p class="text-muted mb-0">Synced in real-time from the Balewadi Court scoring officials</p>
                </div>
                <a href="#games" class="btn btn-outline-light btn-sm">All Disciplines <i class="fas fa-arrow-right ms-1"></i></a>
            </div>

            <div class="row g-4">
                <?php if (empty($liveMatches)): ?>
                    <div class="col-12 text-center py-4 text-muted">No live matches currently in progress.</div>
                <?php else: ?>
                    <?php foreach ($liveMatches as $m): ?>
                        <?php 
                          $scores = json_decode($m['scores_json'] ?? '{}', true);
                          $summary = $scores['summary'] ?? ($m['status'] === 'in_progress' ? 'In Play' : 'Scheduled');
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card bg-dark border-secondary h-100 shadow-sm" style="border-radius: 12px; overflow: hidden; background: rgba(15, 23, 42, 0.8) !important; backdrop-filter: blur(10px);">
                                <div class="card-header border-secondary d-flex justify-content-between align-items-center py-2" style="background: rgba(255,255,255,0.03);">
                                    <strong class="text-warning small text-uppercase"><i class="fas fa-medal me-1"></i> <?= htmlspecialchars($m['game_name']) ?></strong>
                                    <?php if ($m['status'] === 'in_progress'): ?>
                                        <span class="badge bg-danger pulse-animation">LIVE</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">COMPLETED</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="text-center" style="width: 45%;">
                                            <span class="badge px-2 py-1 mb-1" style="background-color: <?= $m['u1_color'] ?>;"><?= htmlspecialchars($m['u1_code']) ?></span>
                                            <h6 class="fw-bold text-white mb-0 text-truncate"><?= htmlspecialchars($m['team1_name']) ?></h6>
                                        </div>
                                        <div class="text-center text-muted fw-bold">VS</div>
                                        <div class="text-center" style="width: 45%;">
                                            <span class="badge px-2 py-1 mb-1" style="background-color: <?= $m['u2_color'] ?>;"><?= htmlspecialchars($m['u2_code']) ?></span>
                                            <h6 class="fw-bold text-white mb-0 text-truncate"><?= htmlspecialchars($m['team2_name']) ?></h6>
                                        </div>
                                    </div>
                                    <?= render_digital_scorecard($m, $scores) ?>
                                </div>
                                <div class="card-footer border-secondary py-2 small text-muted d-flex justify-content-between">
                                    <span><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($m['facility_name'] ?: 'Court 1') ?></span>
                                    <span><?= htmlspecialchars($m['round']) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- OVERALL CHAMPIONSHIP LEADERBOARD SECTION -->
    <section id="leaderboard" class="py-5" style="background: rgba(0,0,0,0.3);">
        <div class="container">
            <div class="text-center mb-5">
                <?php if ($leaderboardMode === 'manual'): ?>
                    <span class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-2">
                        <i class="fas fa-trophy me-1"></i> Official Championship Standings
                    </span>
                    <h2 class="display-5 fw-bold text-white">Overall Championship Leaderboard</h2>
                    <p class="text-muted">Official Tournament Standings & Points as ratified by Central Sports Committee</p>
                <?php else: ?>
                    <span class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-2">
                        <i class="fas fa-trophy me-1"></i> Official Points Scheme (5-3-1)
                    </span>
                    <h2 class="display-5 fw-bold text-white">Overall Championship Leaderboard</h2>
                    <p class="text-muted">Rankings computed across Men's, Women's, and Individual disciplines with tiebreaker cascade</p>
                <?php endif; ?>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="glass-card p-0 shadow-lg" style="overflow: hidden;">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0 align-middle">
                                <thead style="background: rgba(0, 30, 70, 0.8);">
                                    <tr class="text-uppercase small text-muted border-bottom border-secondary">
                                        <th class="ps-4">Rank</th>
                                        <th>HPCL Unit</th>
                                        <th class="text-center"><i class="fas fa-medal text-warning"></i> Gold</th>
                                        <th class="text-center"><i class="fas fa-medal text-secondary"></i> Silver</th>
                                        <th class="text-center"><i class="fas fa-medal" style="color: #cd7f32;"></i> Bronze</th>
                                        <th class="text-end pe-4">Total Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $hasAnyPoints = false;
                                        foreach ($leaderboard as $checkRow) {
                                            if (!empty($checkRow['total_points']) && $checkRow['total_points'] > 0) {
                                                $hasAnyPoints = true;
                                                break;
                                            }
                                        }
                                    ?>
                                    <?php foreach ($leaderboard as $idx => $r): ?>
                                        <?php 
                                            $hasRank = !empty($r['rank']);
                                            $rankVal = $r['rank'] ?? null;
                                            $isTop = ($hasRank && $rankVal === 1);
                                        ?>
                                        <tr class="<?= $isTop ? 'bg-gradient-warning text-dark' : '' ?>" style="<?= $isTop ? 'background: linear-gradient(90deg, rgba(255,215,0,0.15), rgba(255,215,0,0.05));' : '' ?>">
                                            <td class="ps-4">
                                                <?php if (!$hasRank): ?>
                                                    <span class="text-muted fw-bold" style="font-size: 1.1rem; opacity: 0.5;">—</span>
                                                <?php elseif ($rankVal === 1): ?>
                                                    <span class="rank-badge bg-warning text-dark"><i class="fas fa-crown"></i></span>
                                                <?php elseif ($rankVal === 2): ?>
                                                    <span class="rank-badge bg-secondary text-white">2</span>
                                                <?php elseif ($rankVal === 3): ?>
                                                    <span class="rank-badge text-white" style="background: #cd7f32;">3</span>
                                                <?php else: ?>
                                                    <span class="rank-badge text-muted">#<?= $rankVal ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge me-2" style="background-color: <?= $r['color_code'] ?>; color: #fff; font-size: 0.85rem;">
                                                        <?= htmlspecialchars($r['short_code']) ?>
                                                    </span>
                                                    <strong class="text-white"><?= htmlspecialchars($r['name']) ?></strong>
                                                </div>
                                            </td>
                                            <td class="text-center fw-bold text-warning"><?= $r['gold'] ?></td>
                                            <td class="text-center fw-bold text-light"><?= $r['silver'] ?></td>
                                            <td class="text-center fw-bold" style="color: #e09858;"><?= $r['bronze'] ?></td>
                                            <td class="text-end pe-4">
                                                <span class="badge bg-primary px-3 py-2 fs-6 fw-bold">
                                                    <?= $r['total_points'] ?> Pts
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (!$hasAnyPoints): ?>
                                <div class="text-center py-3 bg-dark border-top border-secondary text-muted small">
                                    <i class="fas fa-info-circle text-info me-1"></i> Tournament in progress &bull; Official rankings will activate as match results and points are recorded.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- DEDICATED PER-GAME DASHBOARDS SELECTOR -->
    <section id="games" class="py-5">
        <div class="container text-center py-5">
            <h2 class="fw-bold mb-3" style="font-size: 2.5rem; text-transform: uppercase; letter-spacing: 1px;">Tournament Disciplines</h2>
            <p class="text-muted mb-5">Click any sport to open its dedicated dashboard with pool tables, brackets, and court schedules</p>
            
            <div class="row g-4">
                <?php foreach ($allGames as $g): ?>
                    <div class="col-md-4 col-sm-6">
                        <a href="<?= BASE_URL ?>/<?= $g['slug'] ?>" class="text-decoration-none">
                            <div class="glass-card h-100 p-4 transition-hover text-start">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <i class="<?= $g['icon'] ?> fa-2x text-warning"></i>
                                    <span class="badge bg-secondary text-uppercase"><?= $g['category'] ?></span>
                                </div>
                                <h4 class="text-white fw-bold mb-2"><?= htmlspecialchars($g['name']) ?></h4>
                                <p class="text-muted small mb-3"><?= htmlspecialchars($g['rules_summary']) ?></p>
                                <span class="custom-btn btn-outline-custom btn-sm w-100 text-center">
                                    Open <?= htmlspecialchars($g['name']) ?> Board <i class="fas fa-arrow-right ms-1"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- MASTER CEREMONIES & EVENTS TIMELINE -->
    <section id="schedule" class="py-5" style="background: rgba(0,0,0,0.2);">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold mb-2">Master Protocol</span>
                <h2 class="display-6 fw-bold text-white">Ceremonies & Official Schedule</h2>
                <p class="text-muted">Key events across the 3 tournament days at Shree Shiv Chhatrapati Sports Complex</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php foreach ($masterEvents as $ev): ?>
                        <div class="card bg-dark border-secondary mb-3 shadow-sm" style="border-radius: 10px;">
                            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                                <div class="mb-2 mb-md-0">
                                    <span class="badge bg-warning text-dark text-uppercase mb-1"><?= htmlspecialchars($ev['event_type']) ?></span>
                                    <h5 class="fw-bold text-white mb-1"><?= htmlspecialchars($ev['event_name']) ?></h5>
                                    <p class="small text-muted mb-0"><?= htmlspecialchars($ev['description']) ?></p>
                                </div>
                                <div class="text-md-end text-light mt-2 mt-md-0">
                                    <strong class="d-block text-warning"><?= date('D, d M Y', strtotime($ev['date'])) ?></strong>
                                    <span class="badge bg-secondary"><?= date('h:i A', strtotime($ev['start_time'])) ?> – <?= date('h:i A', strtotime($ev['end_time'])) ?></span>
                                    <div class="small text-muted mt-1"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($ev['location']) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- TOURNAMENT PHOTO GALLERY & ALBUMS (FR-30, FR-32) -->
    <section id="gallery" class="py-5">
        <div class="container">
            <div class="text-center mb-4">
                <span class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-2">
                    <i class="fas fa-camera me-1"></i> Live Visual Repository
                </span>
                <h2 class="display-6 fw-bold text-white">Tournament Media & Albums</h2>
                <p class="text-muted">High-resolution match action, ceremonies, and unit moments from Balewadi &bull; Progressive Dual-Stream Delivery</p>
            </div>

            <!-- View Switcher & Category Filters -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <!-- View Mode: Albums vs All Photos -->
                <div class="btn-group shadow-sm">
                    <button type="button" class="btn btn-sm btn-primary active fw-bold" id="btnToggleAlbumsView" onclick="switchHomeGalleryMode('albums')">
                        <i class="fas fa-folder-open me-1"></i> Albums (<?= count($galleryAlbums) ?>)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light fw-bold" id="btnTogglePhotosView" onclick="switchHomeGalleryMode('photos')">
                        <i class="fas fa-images me-1"></i> All Photos (<?= count($galleryPhotos) ?>)
                    </button>
                </div>

                <!-- Category Filter Pills -->
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-primary active gallery-filter-btn" onclick="filterHomeGallery('all', this)">
                        <i class="fas fa-th-large me-1"></i> All Moments
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light gallery-filter-btn" onclick="filterHomeGallery('Game', this)">
                        <i class="fas fa-trophy me-1"></i> Game Action
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light gallery-filter-btn" onclick="filterHomeGallery('Ceremony', this)">
                        <i class="fas fa-flag-checkered me-1"></i> Ceremonies
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light gallery-filter-btn" onclick="filterHomeGallery('Meetings', this)">
                        <i class="fas fa-users-cog me-1"></i> Meetings
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light gallery-filter-btn" onclick="filterHomeGallery('General', this)">
                        <i class="fas fa-camera-retro me-1"></i> General
                    </button>
                </div>
            </div>

            <!-- 1. ALBUMS GRID (DEFAULT) -->
            <div id="homeAlbumsContainer">
                <?php if (empty($galleryAlbums)): ?>
                    <div class="text-center py-5 glass-card rounded-4">
                        <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                        <h5 class="text-white">Tournament Albums Awaiting Upload</h5>
                        <p class="text-muted mb-0">Official photographer batches and match albums will appear live here.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4" id="homeAlbumsGrid">
                        <?php foreach ($galleryAlbums as $alb): ?>
                            <?php
                                $badgeClass = 'bg-secondary';
                                if ($alb['event_type'] === 'Game') $badgeClass = 'bg-primary';
                                elseif ($alb['event_type'] === 'Ceremony') $badgeClass = 'bg-success';
                                elseif ($alb['event_type'] === 'Meetings') $badgeClass = 'bg-warning text-dark';
                                elseif ($alb['event_type'] === 'General') $badgeClass = 'bg-info text-dark';

                                $coverImg = !empty($alb['cover_compressed_path']) && file_exists($alb['cover_compressed_path']) 
                                            ? $alb['cover_compressed_path'] 
                                            : (!empty($alb['cover_file_path']) && file_exists($alb['cover_file_path']) ? $alb['cover_file_path'] : 'public/images/default-album.jpg');
                            ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 home-album-item" data-category="<?= htmlspecialchars($alb['event_type']) ?>">
                                <div class="glass-card h-100 p-0 overflow-hidden shadow transition-hover position-relative" style="cursor: pointer;" onclick="openHomeAlbumViewer(<?= $alb['id'] ?>)">
                                    <div style="height: 220px; overflow: hidden; background: #000; position: relative;">
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($coverImg) ?>"
                                             class="w-100 h-100"
                                             style="object-fit: cover; transition: transform 0.4s ease;"
                                             onmouseover="this.style.transform='scale(1.05)'"
                                             onmouseout="this.style.transform='scale(1)'"
                                             alt="<?= htmlspecialchars($alb['title']) ?>">

                                        <!-- Category Pill -->
                                        <span class="badge <?= $badgeClass ?> position-absolute text-uppercase fw-bold shadow-sm" style="top: 10px; left: 10px;">
                                            <?= htmlspecialchars($alb['event_type']) ?>
                                        </span>
                                        
                                        <!-- Photo Count Badge -->
                                        <span class="badge bg-dark position-absolute fw-bold" style="bottom: 10px; right: 10px; background: rgba(0,0,0,0.75) !important;">
                                            <i class="fas fa-images text-warning me-1"></i> <?= $alb['photo_count'] ?> Photos
                                        </span>

                                        <!-- Day Badge -->
                                        <span class="badge bg-dark position-absolute fw-bold" style="top: 10px; right: 10px; background: rgba(0,0,0,0.7) !important;">
                                            Day <?= $alb['day'] ?>
                                        </span>
                                    </div>

                                    <div class="p-3 text-start">
                                        <h6 class="text-white fw-bold mb-1 text-truncate" title="<?= htmlspecialchars($alb['title']) ?>">
                                            <?= htmlspecialchars($alb['title']) ?>
                                        </h6>
                                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary border-opacity-25">
                                            <span class="small text-muted text-truncate">
                                                <?= $alb['game_name'] ? '<i class="fas fa-trophy text-primary me-1"></i>' . htmlspecialchars($alb['game_name']) : '<i class="fas fa-camera text-info me-1"></i> Tournament Event' ?>
                                            </span>
                                            <span class="btn btn-xs btn-outline-primary text-uppercase fw-bold" style="font-size: 0.75rem;">
                                                View Album <i class="fas fa-arrow-right ms-1"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 2. ALL PHOTOS GRID (TOGGLED) -->
            <div id="homePhotosContainer" style="display: none;">
                <?php if (empty($galleryPhotos)): ?>
                    <div class="text-center py-5 glass-card rounded-4">
                        <i class="fas fa-camera-retro fa-3x mb-3 text-secondary"></i>
                        <h5 class="text-white">No photos uploaded yet.</h5>
                    </div>
                <?php else: ?>
                    <div class="row g-4" id="homePhotosGrid">
                        <?php foreach ($galleryPhotos as $ph): ?>
                            <?php
                                $badgeClass = 'bg-secondary';
                                if ($ph['event_type'] === 'Game') $badgeClass = 'bg-primary';
                                elseif ($ph['event_type'] === 'Ceremony') $badgeClass = 'bg-success';
                                elseif ($ph['event_type'] === 'Meetings') $badgeClass = 'bg-warning text-dark';
                                elseif ($ph['event_type'] === 'General') $badgeClass = 'bg-info text-dark';

                                $displayThumb = !empty($ph['compressed_path']) && file_exists($ph['compressed_path']) ? $ph['compressed_path'] : $ph['file_path'];
                                $fullOriginal = $ph['file_path'];
                                $cardTitle = $ph['title'] ?: ($ph['game_name'] ? $ph['game_name'] . ' Match' : 'HPCL Tournament 2026');
                            ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 home-photo-item" data-category="<?= htmlspecialchars($ph['event_type']) ?>">
                                <div class="glass-card h-100 p-0 overflow-hidden shadow transition-hover position-relative" style="cursor: pointer;" onclick="openHomeAlbumViewer(<?= (int)$ph['album_id'] ?>, <?= (int)$ph['id'] ?>)">
                                    <div style="height: 220px; overflow: hidden; background: #000; position: relative;">
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($displayThumb) ?>"
                                             data-original="<?= BASE_URL ?>/<?= htmlspecialchars($fullOriginal) ?>"
                                             class="progressive-photo-img w-100 h-100"
                                             style="object-fit: cover; transition: transform 0.4s ease;"
                                             alt="<?= htmlspecialchars($cardTitle) ?>">

                                        <span class="badge <?= $badgeClass ?> position-absolute text-uppercase fw-bold shadow-sm" style="top: 10px; left: 10px;">
                                            <?= htmlspecialchars($ph['event_type']) ?>
                                        </span>
                                        <span class="badge bg-dark position-absolute fw-bold" style="top: 10px; right: 10px; background: rgba(0,0,0,0.7) !important;">
                                            Day <?= $ph['day'] ?>
                                        </span>
                                    </div>

                                    <div class="p-3 text-start">
                                        <h6 class="text-white fw-bold mb-1 text-truncate" title="<?= htmlspecialchars($cardTitle) ?>">
                                            <?= htmlspecialchars($cardTitle) ?>
                                        </h6>
                                        <?php if ($ph['caption']): ?>
                                            <p class="text-muted small mb-0 text-truncate" title="<?= htmlspecialchars($ph['caption']) ?>">
                                                <i class="fas fa-quote-left me-1 text-secondary"></i><?= htmlspecialchars($ph['caption']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <!-- FULLSCREEN ALBUM CAROUSEL VIEWER MODAL (SWIPE + BUTTONS + PROGRESSIVE 1S LOAD) -->
    <div class="modal fade" id="publicAlbumViewerModal" tabindex="-1" style="background: rgba(0,0,0,0.94);">
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw;">
            <div class="modal-content bg-transparent border-0 text-white">
                
                <!-- Viewer Header -->
                <div class="d-flex justify-content-between align-items-center p-3" style="background: rgba(0,0,0,0.7); border-radius: 12px 12px 0 0;">
                    <div class="d-flex align-items-center">
                        <span id="pubViewerBadge" class="badge bg-primary text-uppercase me-2"></span>
                        <span id="pubViewerDay" class="badge bg-secondary me-2"></span>
                        <h5 id="pubViewerAlbumTitle" class="modal-title fw-bold text-white mb-0 text-truncate" style="max-width: 60vw;"></h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <span id="pubViewerCounter" class="badge bg-warning text-dark px-3 py-2 fw-bold me-3">Photo 0 of 0</span>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <!-- Main Stage with Left/Right Buttons & Touch Swipe Area -->
                <div class="position-relative text-center p-0" id="pubViewerStage" style="background: #000; min-height: 540px; display: flex; align-items: center; justify-content: center; overflow: hidden; user-select: none;">
                    
                    <!-- Left Navigation Button -->
                    <button type="button" class="btn position-absolute text-white" id="pubBtnPrev" onclick="prevPubViewerPhoto()" 
                            style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; width: 55px; height: 55px; border-radius: 50%; background: rgba(0,0,0,0.6); border: 2px solid rgba(255,255,255,0.4); font-size: 1.5rem; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(13,110,253,0.9)'; this.style.borderColor='#fff';"
                            onmouseout="this.style.background='rgba(0,0,0,0.6)'; this.style.borderColor='rgba(255,255,255,0.4)';"
                            title="Previous Photo (Left Arrow or Swipe Right)">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <!-- Main Image (Progressive Dual-Stream Loading: compressed first, 1 second later high-res) -->
                    <div id="pubViewerImgContainer" style="width: 100%; height: 65vh; display: flex; align-items: center; justify-content: center; position: relative;">
                        <img id="pubViewerMainImg" src="" 
                             style="max-width: 100%; max-height: 100%; object-fit: contain; transition: filter 0.4s ease, opacity 0.3s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.8);" 
                             alt="HPCL Tournament Photo">
                        
                        <div id="pubViewerSpinner" class="spinner-border text-primary position-absolute" style="display: none; width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <!-- Right Navigation Button -->
                    <button type="button" class="btn position-absolute text-white" id="pubBtnNext" onclick="nextPubViewerPhoto()" 
                            style="right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; width: 55px; height: 55px; border-radius: 50%; background: rgba(0,0,0,0.6); border: 2px solid rgba(255,255,255,0.4); font-size: 1.5rem; transition: all 0.2s;"
                            onmouseover="this.style.background='rgba(13,110,253,0.9)'; this.style.borderColor='#fff';"
                            onmouseout="this.style.background='rgba(0,0,0,0.6)'; this.style.borderColor='rgba(255,255,255,0.4)';"
                            title="Next Photo (Right Arrow or Swipe Left)">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    <div class="position-absolute text-white-50 small d-md-none" style="bottom: 10px; z-index: 5; pointer-events: none;">
                        <i class="fas fa-arrows-alt-h me-1"></i> Swipe left or right to browse photos
                    </div>
                </div>

                <!-- Caption & Download Strip -->
                <div class="p-3 bg-dark d-flex flex-wrap justify-content-between align-items-center" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <div>
                        <h6 id="pubViewerPhotoTitle" class="fw-bold mb-1 text-white"></h6>
                        <p id="pubViewerPhotoCaption" class="small text-muted mb-0"></p>
                    </div>
                    <div class="mt-2 mt-md-0">
                        <a id="pubViewerDownloadBtn" href="" target="_blank" class="btn btn-sm btn-outline-info fw-bold">
                            <i class="fas fa-download me-1"></i> High-Res Original
                        </a>
                    </div>
                </div>

                <!-- Bottom Thumbnail Filmstrip -->
                <div id="pubViewerFilmstrip" class="p-2 bg-black d-flex gap-2" style="overflow-x: auto; white-space: nowrap; border-radius: 0 0 12px 12px; max-height: 90px;"></div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
        <p class="text-muted mb-0">&copy; 2026 Hindustan Petroleum Corporation Limited (HPCL). 20th All India Inter Unit Sports & Games Tournament. Balewadi, Pune.</p>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Public Album & Photo Gallery Carousel Scripts -->
    <script>
        // 1. Progressive Image Loader for initial page grid:
        document.addEventListener('DOMContentLoaded', function() {
            const progImages = document.querySelectorAll('.progressive-photo-img');
            progImages.forEach(function(img) {
                const originalSrc = img.getAttribute('data-original');
                if (originalSrc && originalSrc !== img.src) {
                    setTimeout(function() {
                        const fullImg = new Image();
                        fullImg.src = originalSrc;
                        fullImg.onload = function() {
                            img.src = originalSrc;
                            img.style.filter = 'none';
                        };
                    }, 1000); // 1-second delay as requested
                }
            });
        });

        // 2. View Mode Toggle (Albums vs Photos)
        function switchHomeGalleryMode(mode) {
            var btnAlbums = document.getElementById('btnToggleAlbumsView');
            var btnPhotos = document.getElementById('btnTogglePhotosView');
            var albumsCont = document.getElementById('homeAlbumsContainer');
            var photosCont = document.getElementById('homePhotosContainer');

            if (mode === 'albums') {
                btnAlbums.classList.add('active', 'btn-primary');
                btnAlbums.classList.remove('btn-outline-light');
                btnPhotos.classList.remove('active', 'btn-primary');
                btnPhotos.classList.add('btn-outline-light');
                albumsCont.style.display = 'block';
                photosCont.style.display = 'none';
            } else {
                btnPhotos.classList.add('active', 'btn-primary');
                btnPhotos.classList.remove('btn-outline-light');
                btnAlbums.classList.remove('active', 'btn-primary');
                btnAlbums.classList.add('btn-outline-light');
                albumsCont.style.display = 'none';
                photosCont.style.display = 'block';
            }
        }

        // 3. Category Filter Switcher
        function filterHomeGallery(category, btn) {
            document.querySelectorAll('.gallery-filter-btn').forEach(b => {
                b.classList.remove('active', 'btn-primary');
                b.classList.add('btn-outline-light');
            });
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-outline-light');

            document.querySelectorAll('.home-album-item').forEach(item => {
                item.style.display = (category === 'all' || item.getAttribute('data-category') === category) ? 'block' : 'none';
            });
            document.querySelectorAll('.home-photo-item').forEach(item => {
                item.style.display = (category === 'all' || item.getAttribute('data-category') === category) ? 'block' : 'none';
            });
        }

        // 4. Interactive Album Viewer Modal (Swapping left/right & buttons & touch swipe)
        var pubAlbumPhotos = [];
        var pubCurrentIndex = 0;
        var pubProgressiveTimer = null;
        var pubModalInstance = null;

        async function openHomeAlbumViewer(albumId, initialPhotoId) {
            if (!albumId) return;

            try {
                var res = await fetch('<?= BASE_URL ?>/api/albums?id=' + albumId);
                var data = await res.json();
                if (!res.ok || data.status !== 'success' || !data.photos || data.photos.length === 0) {
                    alert('No photos available in this album yet.');
                    return;
                }

                pubAlbumPhotos = data.photos;
                var album = data.album;

                document.getElementById('pubViewerAlbumTitle').innerText = album.title;
                document.getElementById('pubViewerBadge').innerText = album.event_type;
                document.getElementById('pubViewerDay').innerText = 'Day ' + album.day;

                var badge = document.getElementById('pubViewerBadge');
                badge.className = 'badge text-uppercase me-2 ';
                if (album.event_type === 'Game') badge.className += 'bg-primary';
                else if (album.event_type === 'Ceremony') badge.className += 'bg-success';
                else if (album.event_type === 'Meetings') badge.className += 'bg-warning text-dark';
                else badge.className += 'bg-info text-dark';

                pubCurrentIndex = 0;
                if (initialPhotoId) {
                    var foundIdx = pubAlbumPhotos.findIndex(p => parseInt(p.id) === parseInt(initialPhotoId));
                    if (foundIdx >= 0) pubCurrentIndex = foundIdx;
                }

                renderPubViewerFilmstrip();
                showPubViewerPhoto(pubCurrentIndex);

                if (!pubModalInstance) {
                    pubModalInstance = new bootstrap.Modal(document.getElementById('publicAlbumViewerModal'));
                }
                pubModalInstance.show();
            } catch(e) {
                alert('Error loading album: ' + e.message);
            }
        }

        function showPubViewerPhoto(index) {
            if (!pubAlbumPhotos || pubAlbumPhotos.length === 0) return;
            if (index < 0) index = pubAlbumPhotos.length - 1;
            if (index >= pubAlbumPhotos.length) index = 0;
            pubCurrentIndex = index;

            var ph = pubAlbumPhotos[index];
            var mainImg = document.getElementById('pubViewerMainImg');

            if (pubProgressiveTimer) {
                clearTimeout(pubProgressiveTimer);
            }

            var compressedSrc = '<?= BASE_URL ?>/' + (ph.compressed_path || ph.file_path);
            var originalSrc = '<?= BASE_URL ?>/' + ph.file_path;

            // Step 1: Immediately show compressed photo
            mainImg.src = compressedSrc;
            mainImg.style.filter = 'blur(1px)';

            // Step 2: Exactly after 1 second (1000ms), load original high-res and swap
            pubProgressiveTimer = setTimeout(function() {
                var highRes = new Image();
                highRes.src = originalSrc;
                highRes.onload = function() {
                    if (pubCurrentIndex === index) {
                        mainImg.src = originalSrc;
                        mainImg.style.filter = 'none';
                    }
                };
            }, 1000);

            document.getElementById('pubViewerCounter').innerText = 'Photo ' + (index + 1) + ' of ' + pubAlbumPhotos.length;
            document.getElementById('pubViewerPhotoTitle').innerText = ph.title || ph.file_name;
            document.getElementById('pubViewerPhotoCaption').innerText = ph.caption || 'Captured live at Balewadi Complex, Pune';
            document.getElementById('pubViewerDownloadBtn').href = originalSrc;

            // Highlight in filmstrip
            document.querySelectorAll('.pub-filmstrip-thumb').forEach(function(th, idx) {
                if (idx === index) {
                    th.style.borderColor = '#0d6efd';
                    th.style.opacity = '1';
                    th.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    th.style.borderColor = 'transparent';
                    th.style.opacity = '0.5';
                }
            });
        }

        function nextPubViewerPhoto() {
            showPubViewerPhoto(pubCurrentIndex + 1);
        }

        function prevPubViewerPhoto() {
            showPubViewerPhoto(pubCurrentIndex - 1);
        }

        function renderPubViewerFilmstrip() {
            var strip = document.getElementById('pubViewerFilmstrip');
            strip.innerHTML = '';

            pubAlbumPhotos.forEach(function(ph, idx) {
                var thumb = document.createElement('img');
                thumb.src = '<?= BASE_URL ?>/' + (ph.compressed_path || ph.file_path);
                thumb.className = 'pub-filmstrip-thumb rounded';
                thumb.style.width = '64px';
                thumb.style.height = '48px';
                thumb.style.objectFit = 'cover';
                thumb.style.cursor = 'pointer';
                thumb.style.border = '2px solid transparent';
                thumb.style.opacity = '0.5';
                thumb.style.transition = 'all 0.2s';
                thumb.onclick = function() { showPubViewerPhoto(idx); };
                strip.appendChild(thumb);
            });
        }

        // Keyboard arrow navigation
        document.addEventListener('keydown', function(e) {
            var modalElem = document.getElementById('publicAlbumViewerModal');
            if (modalElem && modalElem.classList.contains('show')) {
                if (e.key === 'ArrowLeft') {
                    prevPubViewerPhoto();
                } else if (e.key === 'ArrowRight') {
                    nextPubViewerPhoto();
                }
            }
        });

        // Touch Swipe Gestures
        var pubTouchStartX = 0;
        var pubTouchEndX = 0;
        var pubStageElem = document.getElementById('pubViewerStage');

        pubStageElem.addEventListener('touchstart', function(e) {
            pubTouchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        pubStageElem.addEventListener('touchend', function(e) {
            pubTouchEndX = e.changedTouches[0].screenX;
            var delta = pubTouchEndX - pubTouchStartX;
            if (Math.abs(delta) > 50) {
                if (delta < 0) {
                    nextPubViewerPhoto();
                } else {
                    prevPubViewerPhoto();
                }
            }
        }, { passive: true });
    </script>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js')
                    .then(reg => console.log('ServiceWorker registered with scope:', reg.scope))
                    .catch(err => console.error('ServiceWorker registration failed:', err));
            });
        }
    </script>

    <!-- Theme Toggle Logic -->
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
