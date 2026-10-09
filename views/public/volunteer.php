<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Volunteer / Court-Side Scorer PWA Interface
// Responsive, Mobile-First Web Application (FR-14, FR-16, FR-17, FR-18)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HPCL Sports MIS - Volunteer Scoring Terminal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --hpcl-navy: #003366;
            --hpcl-red: #D9251D;
            --hpcl-dark: #001f3f;
            --hpcl-light: #f8fafc;
            --hpcl-accent: #0284c7;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f1f5f9;
            padding-bottom: 70px;
        }
        .app-header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #fff;
            padding: 1rem 0;
            border-bottom: 4px solid var(--hpcl-red);
        }
        .match-badge {
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .sync-indicator {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .table-score td, .table-score th {
            padding: 0.35rem;
        }
        .sport-score-form {
            animation: fadeIn 0.25s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .score-pill-input {
            width: 72px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            text-align: center;
        }
        .btn-inc {
            width: 32px;
            height: 32px;
            padding: 0;
            font-weight: bold;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .pulse-live {
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #ef4444;
            border-radius: 50%;
            animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
            margin-right: 4px;
            vertical-align: middle;
        }
        @keyframes pulse-ring {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .mode-toggle-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }
        .tennis-point-btn.active {
            background-color: #0284c7 !important;
            color: #fff !important;
            font-weight: 700;
            border-color: #0284c7 !important;
        }
        .card-live-glow {
            border: 2px solid #ef4444 !important;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.15) !important;
        }

        /* Bridge Matrix & Console Styles */
        .bridge-matrix-tbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            min-width: 800px;
        }
        .bridge-matrix-tbl th {
            background: #1e293b;
            color: #f8fafc;
            text-align: center;
            padding: 8px 6px;
            border: 1px solid #cbd5e1;
            font-size: 0.78rem;
            text-transform: uppercase;
        }
        .bridge-matrix-tbl td {
            padding: 6px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            text-align: center;
        }
        .bridge-matrix-tbl tr:nth-child(even) td {
            background: #f8fafc;
        }
        .bridge-cell-split {
            position: relative;
            width: 74px;
            height: 48px;
            background: #ffffff;
            border: 1px solid #94a3b8;
            border-radius: 4px;
            margin: 0 auto;
            overflow: hidden;
        }
        .bridge-cell-split::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, transparent calc(50% - 1px), #94a3b8 50%, transparent calc(50% + 1px));
            pointer-events: none;
        }
        .bridge-cell-cum-vp {
            position: absolute;
            top: 2px;
            left: 4px;
            font-weight: 800;
            font-size: 0.85rem;
            color: #0284c7;
            font-family: monospace;
            line-height: 1;
        }
        .bridge-cell-opp-no {
            position: absolute;
            bottom: 2px;
            right: 4px;
            font-weight: 800;
            font-size: 0.75rem;
            color: #b45309;
            background: #fef3c7;
            padding: 1px 4px;
            border-radius: 3px;
            line-height: 1;
            border: 1px solid #fde68a;
        }
        .bridge-cell-empty {
            position: relative;
            width: 74px;
            height: 48px;
            background: #f1f5f9;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            margin: 0 auto;
        }
        .bridge-cell-empty::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, transparent calc(50% - 1px), #cbd5e1 50%, transparent calc(50% + 1px));
        }
        .bridge-table-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #ffffff;
            transition: box-shadow 0.2s ease;
        }
        .bridge-table-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header class="app-header shadow-sm mb-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="me-2 bg-white text-danger px-2 py-1 rounded fw-bold">HPCL</div>
                <div>
                    <h5 class="mb-0 fw-bold text-white">Court-Side Scoring Terminal</h5>
                    <small class="text-white-50" style="font-size: 0.75rem;">Digital Scorecards &bull; Live Updates &bull; Offline Sync</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span id="networkBadge" class="badge bg-success p-2">
                    <i class="fas fa-wifi me-1"></i> <span id="networkText">Online</span>
                </span>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="badge bg-white bg-opacity-10 text-white p-2 d-none d-md-inline-block border border-white border-opacity-25" style="font-size: 0.8rem;">
                        <i class="fas fa-user-circle text-warning me-1"></i> <?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Scorer') ?>
                        <span class="badge bg-success ms-1" style="font-size: 0.7rem;">Volunteer</span>
                    </span>
                    <a href="<?= BASE_URL ?>/admin/logout" class="btn btn-danger btn-sm fw-bold px-3 py-1 shadow-sm" title="Log out from scoring session">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/admin/login" class="btn btn-outline-light btn-sm fw-bold px-3 py-1 shadow-sm">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main App Container -->
    <main class="container">

        <!-- Flash error notification if redirected from restricted pages -->
        <?php if (!empty($_SESSION['flash_err'])): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2 mb-3 shadow-sm d-flex align-items-center" role="alert">
                <i class="fas fa-shield-alt text-danger fs-5 me-2"></i>
                <div class="small fw-semibold"><?= htmlspecialchars($_SESSION['flash_err']) ?></div>
                <button type="button" class="btn-close ms-auto p-2" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_err']); ?>
        <?php endif; ?>

        <!-- Offline notice / Queue alert -->
        <div id="offlineAlert" class="alert alert-warning py-2 mb-3 d-none align-items-center shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
            <div>
                <strong>Offline Mode Active:</strong> You can continue scoring live and submitting results. All updates are securely cached and will automatically broadcast when internet restores.
            </div>
        </div>

        <!-- Sync button banner when pending items exist -->
        <div id="syncBanner" class="alert alert-info py-2 mb-3 d-none justify-content-between align-items-center shadow-sm">
            <div>
                <i class="fas fa-cloud-upload-alt me-2 fs-5 text-primary"></i>
                <span id="pendingCountText">0 matches pending sync</span>
            </div>
            <button class="btn btn-primary btn-sm fw-bold shadow-sm" onclick="syncToCloud()">
                <i class="fas fa-sync-alt me-1"></i> Sync Now
            </button>
        </div>

        <!-- Filter & View Controls -->
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
            <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active fw-bold" id="tabActive" onclick="setTab('active')">
                        <span class="pulse-live"></span> Live & Scheduled (<span id="countActive">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-secondary fw-bold" id="tabCompleted" onclick="setTab('completed')">
                        <i class="fas fa-check-circle me-1 text-success"></i> Concluded (<span id="countCompleted">0</span>)
                    </button>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <select id="sportFilter" class="form-select form-select-sm" style="width: auto; min-width: 140px;" onchange="renderMatches()">
                        <option value="all">All Sports</option>
                        <option value="badminton_table_tennis">Badminton & TT</option>
                        <option value="tennis">Lawn Tennis</option>
                        <option value="chess">Chess</option>
                        <option value="carrom">Carrom</option>
                        <option value="bridge">Bridge</option>
                        <option value="swimming">Swimming</option>
                    </select>

                    <button class="btn btn-light btn-sm border" onclick="fetchMatches()" title="Refresh Matches">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Match Cards Container -->
        <div class="row g-3" id="matchesContainer">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-2">Loading tournament match schedules...</p>
            </div>
        </div>

    </main>

    <!-- Bottom Sticky Status Bar -->
    <div id="stickyStatus" class="sync-indicator bg-dark text-white p-2 text-center shadow-lg">
        <span id="syncStatusMsg"><i class="fas fa-check-circle text-success me-1"></i> Terminal ready. Live score broadcast enabled.</span>
    </div>

    <!-- Official Dynamic Score Modal (Supports BOTH Live Updates & Final Submissions) -->
    <div class="modal fade" id="scoreModal" tabindex="-1" data-bs-backdrop="static">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
          
          <div class="modal-header bg-navy text-white" style="background-color: var(--hpcl-navy);">
            <div class="d-flex align-items-center">
                <div class="me-2 p-2 bg-white bg-opacity-10 rounded">
                    <i class="fas fa-tachometer-alt fa-lg text-warning"></i>
                </div>
                <div>
                    <h5 class="modal-title font-weight-bold mb-0">Court-Side Scoring Console</h5>
                    <small class="text-white-50" id="modalSportSubtitle">Select Mode: Live Point Update or Final Match Submission</small>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body text-dark p-3">
            <input type="hidden" id="modalMatchId">
            <input type="hidden" id="modalSportType">

            <!-- Match Meta Header -->
            <div class="p-2 mb-2 rounded bg-light border d-flex justify-content-between align-items-center">
                <div>
                    <span id="modalGameCategory" class="badge bg-primary mb-1"></span>
                    <h6 id="modalMatchTitle" class="fw-bold mb-0 text-navy"></h6>
                </div>
                <div class="text-end">
                    <span id="modalCurrentStatusBadge" class="badge bg-secondary"></span>
                    <div class="small text-muted" id="metaCourt"></div>
                </div>
            </div>

            <!-- MODE SELECTOR: LIVE vs FINAL -->
            <div class="mode-toggle-card p-2 mb-3">
                <div class="row g-2">
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="scoringMode" id="modeLive" value="live" autocomplete="off" onchange="toggleScoringMode('live')" checked>
                        <label class="btn btn-outline-danger w-100 py-2 fw-bold text-start d-flex align-items-center" for="modeLive">
                            <span class="pulse-live"></span>
                            <div>
                                <div class="lh-1">LIVE UPDATE</div>
                                <small class="text-muted fw-normal" style="font-size: 0.72rem;">Point/Game In Progress</small>
                            </div>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="scoringMode" id="modeFinal" value="final" autocomplete="off" onchange="toggleScoringMode('final')">
                        <label class="btn btn-outline-success w-100 py-2 fw-bold text-start d-flex align-items-center" for="modeFinal">
                            <i class="fas fa-trophy me-2 text-warning fs-5"></i>
                            <div>
                                <div class="lh-1">FINAL RESULT</div>
                                <small class="text-muted fw-normal" style="font-size: 0.72rem;">Conclude & Declare Winner</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div id="liveModeNotice" class="alert alert-danger bg-danger bg-opacity-10 border-danger py-1 px-2 small mb-0 mt-2 text-danger">
                    <i class="fas fa-broadcast-tower me-1"></i> <strong>Live Mode:</strong> Scores update instantly on live scoreboards. Winner is <strong>NOT required</strong> while the match is underway.
                </div>
                <div id="finalModeNotice" class="alert alert-success bg-success bg-opacity-10 border-success py-1 px-2 small mb-0 mt-2 text-success d-none">
                    <i class="fas fa-check-double me-1"></i> <strong>Final Mode:</strong> Enter the official concluding score and select the declared winner to close the match and update standings.
                </div>
            </div>

            <!-- 1. BADMINTON & TABLE TENNIS FORM (Set-Based) -->
            <div id="formBadmintonTT" class="sport-score-form d-none">
                <div id="bttFormatBanner" class="alert alert-primary py-1 px-2 small mb-2 d-flex justify-content-between align-items-center">
                    <span id="bttFormatText"><i class="fas fa-table-tennis me-1"></i> <strong>Men's Table Tennis:</strong> Best of 5 Games (First to 3)</span>
                    <span class="badge bg-primary" id="bttFormatBadge">Best of 5</span>
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm text-center align-middle mb-1">
                        <thead class="table-light small">
                            <tr>
                                <th class="text-start ps-2">Player / Team</th>
                                <th style="width: 100px;">Game 1</th>
                                <th style="width: 100px;">Game 2</th>
                                <th style="width: 100px;">Game 3</th>
                                <th style="width: 100px;" class="btt-extra-col">Game 4</th>
                                <th style="width: 100px;" class="btt-extra-col">Game 5</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-start ps-2 fw-bold" id="bttTeam1Label">Team 1</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g1_a" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g1_a', 1)">+1</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g2_a" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g2_a', 1)">+1</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g3_a" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g3_a', 1)">+1</button>
                                    </div>
                                </td>
                                <td class="btt-extra-col">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g4_a" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g4_a', 1)">+1</button>
                                    </div>
                                </td>
                                <td class="btt-extra-col">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g5_a" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g5_a', 1)">+1</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start ps-2 fw-bold" id="bttTeam2Label">Team 2</td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g1_b" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g1_b', 1)">+1</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g2_b" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g2_b', 1)">+1</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g3_b" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g3_b', 1)">+1</button>
                                    </div>
                                </td>
                                <td class="btt-extra-col">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g4_b" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g4_b', 1)">+1</button>
                                    </div>
                                </td>
                                <td class="btt-extra-col">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="btt_g5_b" class="form-control form-control-sm text-center fw-bold score-pill-input" min="0" max="99" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('btt_g5_b', 1)">+1</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row align-items-center mb-2 g-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">Serving Indicator (🏸)</label>
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <input type="radio" class="btn-check" name="btt_server" id="btt_srv_a" value="team1" checked>
                            <label class="btn btn-outline-dark" for="btt_srv_a" id="bttSrvTeam1Label">Team 1 Serving</label>
                            <input type="radio" class="btn-check" name="btt_server" id="btt_srv_b" value="team2">
                            <label class="btn btn-outline-dark" for="btt_srv_b" id="bttSrvTeam2Label">Team 2 Serving</label>
                        </div>
                    </div>
                    <div class="col-md-6" id="bttWinnerContainer">
                        <label class="form-label small fw-bold mb-1">Winner Output <span class="text-danger winner-req d-none">*</span></label>
                        <select id="bttWinner" class="form-select form-select-sm fw-bold"></select>
                    </div>
                </div>
            </div>

            <!-- 2. LAWN TENNIS FORM (Match & Live Points) -->
            <div id="formTennis" class="sport-score-form d-none">
                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm text-center align-middle mb-1">
                        <thead class="table-light small">
                            <tr>
                                <th class="text-start ps-2">Player / Team</th>
                                <th style="width: 50px;">Serve</th>
                                <th style="width: 80px;">Set 1</th>
                                <th style="width: 80px;">Set 2</th>
                                <th style="width: 80px;">Set 3</th>
                                <th style="width: 140px;">Live Game Pts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-start ps-2 fw-bold" id="tenTeam1Label">Player 1</td>
                                <td><input type="radio" name="ten_server" id="ten_srv_t1" value="team1" checked></td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="ten_s1_a" class="form-control form-control-sm text-center fw-bold" min="0" max="7" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('ten_s1_a', 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="ten_s2_a" class="form-control form-control-sm text-center fw-bold" min="0" max="7" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('ten_s2_a', 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="ten_s3_a" class="form-control form-control-sm text-center fw-bold" min="0" max="7" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('ten_s3_a', 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('a', '0')">0</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('a', '15')">15</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('a', '30')">30</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('a', '40')">40</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('a', 'Ad')">Ad</button>
                                    </div>
                                    <input type="hidden" id="ten_pts_a" value="0">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start ps-2 fw-bold" id="tenTeam2Label">Player 2</td>
                                <td><input type="radio" name="ten_server" id="ten_srv_t2" value="team2"></td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="ten_s1_b" class="form-control form-control-sm text-center fw-bold" min="0" max="7" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('ten_s1_b', 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="ten_s2_b" class="form-control form-control-sm text-center fw-bold" min="0" max="7" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('ten_s2_b', 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="number" id="ten_s3_b" class="form-control form-control-sm text-center fw-bold" min="0" max="7" placeholder="0">
                                        <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('ten_s3_b', 1)">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('b', '0')">0</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('b', '15')">15</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('b', '30')">30</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('b', '40')">40</button>
                                        <button type="button" class="btn btn-outline-secondary tennis-point-btn" onclick="setTennisPt('b', 'Ad')">Ad</button>
                                    </div>
                                    <input type="hidden" id="ten_pts_b" value="0">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row align-items-center mb-2 g-2">
                    <div class="col-md-6" id="tenWinnerContainer">
                        <label class="form-label small fw-bold mb-1">Declared Winner <span class="text-danger winner-req d-none">*</span></label>
                        <select id="tenWinner" class="form-select form-select-sm fw-bold"></select>
                    </div>
                </div>
            </div>

            <!-- 3. CHESS FORM (Points-Based) -->
            <div id="formChess" class="sport-score-form d-none">
                <div class="row g-2 mb-2">
                    <div class="col-md-6 border-end">
                        <div class="p-2 rounded bg-light border">
                            <span class="badge bg-light text-dark border mb-1">⚪ WHITE PIECES</span>
                            <div class="mb-2">
                                <label class="form-label small mb-0 fw-bold">Player Name</label>
                                <input type="text" id="chessWhitePlayer" class="form-control form-control-sm" placeholder="White Player Name">
                            </div>
                            <div>
                                <label class="form-label small mb-0 fw-bold">Zone / Unit</label>
                                <input type="text" id="chessWhiteZone" class="form-control form-control-sm" placeholder="e.g. WZ">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 rounded bg-dark text-white">
                            <span class="badge bg-secondary mb-1">⚫ BLACK PIECES</span>
                            <div class="mb-2">
                                <label class="form-label small mb-0 fw-bold text-white">Player Name</label>
                                <input type="text" id="chessBlackPlayer" class="form-control form-control-sm text-dark bg-white" placeholder="Black Player Name">
                            </div>
                            <div>
                                <label class="form-label small mb-0 fw-bold text-white">Zone / Unit</label>
                                <input type="text" id="chessBlackZone" class="form-control form-control-sm text-dark bg-white" placeholder="e.g. EZ">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-2 align-items-center mb-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Board No.</label>
                        <input type="number" id="chessBoardNo" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Current State / Result <span class="text-danger">*</span></label>
                        <select id="chessResult" class="form-select form-select-sm fw-bold">
                            <option value="in_progress">🔴 Game In Progress (Live)</option>
                            <option value="1-0">1 - 0 (White Wins)</option>
                            <option value="0-1">0 - 1 (Black Wins)</option>
                            <option value="1/2-1/2">½ - ½ (Draw)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Moves / Live Note</label>
                        <input type="text" id="chessLiveNote" class="form-control form-control-sm" placeholder="e.g. Move 24 - Middle game">
                    </div>
                </div>
            </div>

            <!-- 4. CARROM FORM (Cumulative Points) -->
            <div id="formCarrom" class="sport-score-form d-none">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1">Board Number</label>
                        <input type="number" id="carromBoardNo" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1" id="carromTeam1Label">Player / Team A Points</label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="number" id="carrom_pts_a" class="form-control form-control-sm text-center fw-bold" min="0" placeholder="0">
                            <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('carrom_pts_a', 1)">+1</button>
                            <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('carrom_pts_a', 3)">+3</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold mb-1" id="carromTeam2Label">Player / Team B Points</label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="number" id="carrom_pts_b" class="form-control form-control-sm text-center fw-bold" min="0" placeholder="0">
                            <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('carrom_pts_b', 1)">+1</button>
                            <button type="button" class="btn btn-outline-primary btn-inc btn-sm" onclick="incScore('carrom_pts_b', 3)">+3</button>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6" id="carromWinnerContainer">
                        <label class="form-label small fw-bold mb-1">Match Winner <span class="text-danger winner-req d-none">*</span></label>
                        <select id="carromWinner" class="form-select form-select-sm fw-bold"></select>
                    </div>
                </div>
            </div>

            <!-- 5. BRIDGE FORM (Team Match Points: IMPs & VPs) -->
            <div id="formBridge" class="sport-score-form d-none">
                <div class="row g-2 mb-2">
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold mb-1">Session No.</label>
                        <input type="number" id="bridgeSession" class="form-control form-control-sm" value="1">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold mb-1">Table No.</label>
                        <input type="number" id="bridgeTable" class="form-control form-control-sm" value="1">
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-6 p-2 rounded bg-light border">
                        <strong class="d-block mb-1 text-primary" id="bridgeTeam1Label">Team A</strong>
                        <div class="row g-1">
                            <div class="col-6">
                                <label class="form-label small mb-0">Total IMPs</label>
                                <input type="number" id="bridge_imp_a" class="form-control form-control-sm fw-bold" placeholder="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0">VPs (Victory Pts)</label>
                                <input type="number" step="0.01" id="bridge_vp_a" class="form-control form-control-sm" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 p-2 rounded bg-light border">
                        <strong class="d-block mb-1 text-primary" id="bridgeTeam2Label">Team B</strong>
                        <div class="row g-1">
                            <div class="col-6">
                                <label class="form-label small mb-0">Total IMPs</label>
                                <input type="number" id="bridge_imp_b" class="form-control form-control-sm fw-bold" placeholder="0">
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-0">VPs (Victory Pts)</label>
                                <input type="number" step="0.01" id="bridge_vp_b" class="form-control form-control-sm" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-md-6" id="bridgeWinnerContainer">
                        <label class="form-label small fw-bold mb-1">Winning Team <span class="text-danger winner-req d-none">*</span></label>
                        <select id="bridgeWinner" class="form-select form-select-sm fw-bold"></select>
                    </div>
                </div>
            </div>

            <!-- 6. SWIMMING FORM (Time & Rank Timesheet) -->
            <div id="formSwimming" class="sport-score-form d-none">
                <div class="row g-2 mb-2">
                    <div class="col-md-7">
                        <label class="form-label small fw-bold">Event Name <span class="text-danger">*</span></label>
                        <input type="text" id="swimEventName" class="form-control form-control-sm fw-bold" placeholder="e.g. 50m Freestyle Men">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">Heat No. / Final <span class="text-danger">*</span></label>
                        <input type="text" id="swimHeatNo" class="form-control form-control-sm" placeholder="e.g. Heat 1 or Final">
                    </div>
                </div>
                <label class="form-label small fw-bold mb-1">Lane Timesheet (Lanes 1 to 8):</label>
                <div class="table-responsive mb-2" style="max-height: 250px; overflow-y: auto;">
                    <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size: 0.8rem;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 45px;">Lane</th>
                                <th>Swimmer Name</th>
                                <th style="width: 80px;">Zone</th>
                                <th style="width: 100px;">Time (MM:SS.ms)</th>
                                <th style="width: 70px;">Rank</th>
                                <th style="width: 85px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="swimLanesBody">
                            <!-- 8 Lanes generated via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Generic Sport Fallback Form -->
            <div id="formGeneric" class="sport-score-form d-none">
                <div class="mb-3">
                    <label class="form-label fw-bold">Score Summary</label>
                    <input type="text" id="scoreInput" class="form-control" placeholder="e.g. 21-19, 21-17">
                </div>
                <div class="mb-3" id="genericWinnerContainer">
                    <label class="form-label fw-bold">Match Winner <span class="text-danger winner-req d-none">*</span></label>
                    <select id="genericWinner" class="form-select"></select>
                </div>
            </div>

          </div>

          <!-- Modal Footer: Contextual Live Update vs Final Submit -->
          <div class="modal-footer bg-light d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                <i class="fas fa-times me-1"></i> Close
            </button>
            <div class="d-flex gap-2">
                <!-- Button for LIVE UPDATE -->
                <button type="button" class="btn btn-danger font-weight-bold" id="btnLiveSubmit" onclick="processScoreSubmission('in_progress')">
                    <i class="fas fa-broadcast-tower me-1"></i> Update Live Score
                </button>
                <!-- Button for FINAL RESULT -->
                <button type="button" class="btn btn-success font-weight-bold d-none" id="btnFinalSubmit" onclick="processScoreSubmission('completed')">
                    <i class="fas fa-trophy me-1"></i> Submit Final Result
                </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = '<?= BASE_URL ?>/api/matches';
        const BRIDGE_API_URL = '<?= BASE_URL ?>/api/bridge_scores.php';
        let bridgeData = null;
        let activeBridgeRound = 1;
        const ZONES = ['VR', 'NC', 'MP', 'PH', 'SZ', 'WZ', 'NZ', 'MR', 'NW', 'SC', 'EZ', 'HB'];
        let localMatches = JSON.parse(localStorage.getItem('pwa_matches')) || [];
        let pendingSync = JSON.parse(localStorage.getItem('pwa_pending_sync')) || [];
        let activeTab = 'active'; // 'active' or 'completed'
        
        const scoreModal = new bootstrap.Modal(document.getElementById('scoreModal'));

        // Online/Offline detection
        function updateNetworkStatus() {
            const isOnline = navigator.onLine;
            const badge = document.getElementById('networkBadge');
            const text = document.getElementById('networkText');
            const alertBox = document.getElementById('offlineAlert');

            if (isOnline) {
                badge.className = 'badge bg-success p-2';
                text.innerText = 'Online';
                alertBox.classList.add('d-none');
                alertBox.classList.remove('d-flex');
                if (pendingSync.length > 0) syncToCloud();
            } else {
                badge.className = 'badge bg-danger p-2';
                text.innerText = 'Offline';
                alertBox.classList.remove('d-none');
                alertBox.classList.add('d-flex');
            }
        }
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);

        function setTab(tab) {
            activeTab = tab;
            document.getElementById('tabActive').classList.toggle('active', tab === 'active');
            document.getElementById('tabCompleted').classList.toggle('active', tab === 'completed');
            renderMatches();
        }

        // Fetch matches from server
        async function fetchMatches() {
            if (navigator.onLine) {
                try {
                    const res = await fetch(API_URL);
                    const json = await res.json();
                    if (json.status === 'success') {
                        let freshMatches = json.data;
                        freshMatches = freshMatches.map(m => {
                            const pending = pendingSync.find(p => p.id == m.id);
                            return pending ? { ...m, ...pending, isPending: true } : m;
                        });
                        localMatches = freshMatches;
                        localStorage.setItem('pwa_matches', JSON.stringify(localMatches));
                    }
                } catch (e) {
                    console.error('Fetch failed, using local storage cache', e);
                }
            }
            renderMatches();
        }

        function renderMatches() {
            const container = document.getElementById('matchesContainer');
            container.innerHTML = '';
            
            const sportFilter = document.getElementById('sportFilter').value;

            if (sportFilter === 'bridge') {
                renderBridgeRoundScoring(container);
                return;
            }

            // Count for tabs
            const activeMatches = localMatches.filter(m => m.status === 'in_progress' || m.status === 'scheduled');
            const completedMatches = localMatches.filter(m => m.status === 'completed');
            document.getElementById('countActive').innerText = activeMatches.length;
            document.getElementById('countCompleted').innerText = completedMatches.length;

            let filtered = (activeTab === 'active') ? activeMatches : completedMatches;

            if (sportFilter !== 'all') {
                filtered = filtered.filter(m => {
                    const type = getSportTypeFromSlug(m.game_slug);
                    return type === sportFilter;
                });
            }

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-light text-center py-5 border">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3 d-block"></i>
                            <h6 class="fw-bold text-dark">No ${activeTab === 'active' ? 'Active or Scheduled' : 'Completed'} Matches Found</h6>
                            <p class="text-muted small mb-0">Filter: ${sportFilter === 'all' ? 'All Disciplines' : sportFilter}. Check tournament schedule or change filters.</p>
                        </div>
                    </div>`;
                return;
            }

            filtered.forEach(m => {
                const isPending = pendingSync.some(p => p.id == m.id);
                const scores = (typeof m.scores_json === 'string' ? (JSON.parse(m.scores_json || '{}') || {}) : (m.scores_json || {}));
                const scoreSummary = scores.summary || (m.status === 'scheduled' ? 'Scheduled - Awaiting start' : 'Live scoring underway');
                
                const isLive = m.status === 'in_progress';
                const isCompleted = m.status === 'completed';

                const card = document.createElement('div');
                card.className = 'col-md-6 col-lg-4';
                card.innerHTML = `
                    <div class="card h-100 shadow-sm border-0 ${isLive ? 'card-live-glow' : ''} ${isPending ? 'border-warning border border-2' : ''}" style="border-radius: 10px; overflow: hidden;">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-primary match-badge text-uppercase">${m.game_name || 'Sport'}</span>
                                <span class="small text-muted">&bull; ${m.round || 'Round'}</span>
                            </div>
                            <div>
                                ${isLive ? 
                                    `<span class="badge bg-danger text-white"><span class="pulse-live"></span> LIVE NOW</span>` : 
                                  isCompleted ? 
                                    `<span class="badge bg-success"><i class="fas fa-check me-1"></i> FINAL</span>` :
                                    `<span class="badge bg-secondary">SCHEDULED</span>`
                                }
                            </div>
                        </div>
                        <div class="card-body text-center py-3">
                            ${m.game_slug === 'swimming' ? `
                                <div class="p-2 rounded bg-light border mb-2 text-center">
                                    <span class="badge bg-info text-dark mb-1"><i class="fas fa-swimmer me-1"></i> ${m.pool_name || 'Heats'}</span>
                                    <h6 class="fw-bold text-dark mb-0">${scores.participants || 'Multi-lane Heat'}</h6>
                                </div>
                            ` : `
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div style="width: 44%;">
                                        <span class="badge px-2 py-1 mb-1" style="background-color: ${m.u1_color || '#003366'};">${m.u1_code || 'T1'}</span>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" title="${m.team1_name}">${m.team1_name || 'Team 1'}</h6>
                                    </div>
                                    <div class="text-muted fw-bold small">VS</div>
                                    <div style="width: 44%;">
                                        <span class="badge px-2 py-1 mb-1" style="background-color: ${m.u2_color || '#003366'};">${m.u2_code || 'T2'}</span>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" title="${m.team2_name}">${m.team2_name || 'Team 2'}</h6>
                                    </div>
                                </div>
                            `}

                            <!-- Live Score Summary / Status Box -->
                            <div class="p-2 rounded mb-3 ${isLive ? 'bg-danger bg-opacity-10 text-danger border border-danger' : 'bg-light text-dark border'} small fw-bold text-truncate">
                                ${isLive ? '<i class="fas fa-broadcast-tower me-1"></i> Current: ' : ''}${scoreSummary}
                            </div>
                            
                            <p class="small text-muted mb-3" style="font-size: 0.78rem;">
                                <i class="far fa-clock me-1"></i> Slot: ${m.match_date || ''} ${m.start_time || ''} &bull; 
                                <i class="fas fa-map-marker-alt ms-1 me-1"></i> ${m.court_number || m.facility_name || 'Court'}
                            </p>
                            
                            ${isPending ? 
                                `<div class="bg-warning bg-opacity-10 p-2 rounded mb-3 border border-warning text-dark small">
                                    <i class="fas fa-cloud-upload-alt me-1 text-warning"></i>
                                    <strong>Saved Offline:</strong> Sync pending
                                 </div>` : ''
                            }
                            
                            <button class="btn ${isLive ? 'btn-danger' : isPending ? 'btn-warning' : isCompleted ? 'btn-outline-primary' : 'btn-primary'} btn-sm w-100 fw-bold" onclick="openScoreModal(${m.id})">
                                ${isLive ? '<i class="fas fa-bolt me-1"></i> Update Live Score' : 
                                  isCompleted ? '<i class="fas fa-edit me-1"></i> View / Amend Score' : 
                                  '<i class="fas fa-play me-1"></i> Start & Score Match'}
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function getSportTypeFromSlug(slug) {
            slug = (slug || '').toLowerCase();
            if (slug.includes('badminton') || slug.includes('table-tennis')) return 'badminton_table_tennis';
            if (slug.includes('tennis')) return 'tennis';
            if (slug.includes('chess')) return 'chess';
            if (slug.includes('carrom')) return 'carrom';
            if (slug.includes('bridge')) return 'bridge';
            if (slug.includes('swimming')) return 'swimming';
            return 'generic';
        }

        // Toggle Modal between Live Update and Final Result
        window.toggleScoringMode = function(mode) {
            const isLive = mode === 'live';
            document.getElementById('liveModeNotice').classList.toggle('d-none', !isLive);
            document.getElementById('finalModeNotice').classList.toggle('d-none', isLive);

            document.getElementById('btnLiveSubmit').classList.toggle('d-none', !isLive);
            document.getElementById('btnFinalSubmit').classList.toggle('d-none', isLive);

            // Toggle winner required asterisks
            document.querySelectorAll('.winner-req').forEach(el => {
                el.classList.toggle('d-none', isLive);
            });

            // For Chess result dropdown:
            const chessRes = document.getElementById('chessResult');
            if (chessRes) {
                if (isLive && chessRes.value !== 'in_progress') {
                    // keep current
                } else if (!isLive && chessRes.value === 'in_progress') {
                    chessRes.value = '1-0';
                }
            }
        };

        // Quick increment helper
        window.incScore = function(elemId, step) {
            const input = document.getElementById(elemId);
            if (input) {
                const val = parseInt(input.value || 0, 10);
                input.value = Math.max(0, val + step);
            }
        };

        // Quick tennis point button helper
        window.setTennisPt = function(side, pt) {
            document.getElementById('ten_pts_' + side).value = pt;
            const container = document.getElementById('ten_pts_' + side).parentElement;
            container.querySelectorAll('.tennis-point-btn').forEach(btn => {
                btn.classList.toggle('active', btn.innerText.trim() === pt);
            });
        };

        window.openScoreModal = function(id) {
            const m = localMatches.find(x => x.id == id);
            if (!m) return;

            document.getElementById('modalMatchId').value = id;
            document.getElementById('modalGameCategory').innerText = m.game_name + ' &bull; ' + (m.category || 'Open');
            const scoresObj = (typeof m.scores_json === 'string' ? (JSON.parse(m.scores_json || '{}') || {}) : (m.scores_json || {}));
            document.getElementById('modalMatchTitle').innerText = (m.game_slug === 'swimming') 
                ? `${m.round} (${scoresObj.participants || 'All Zones'})` 
                : `${m.team1_name || 'TBD'} vs ${m.team2_name || 'TBD'}`;
            document.getElementById('metaCourt').innerText = m.court_number || m.facility_name || 'Court 1';
            
            const isAlreadyLive = (m.status === 'in_progress');
            const isAlreadyCompleted = (m.status === 'completed');

            const statusBadge = document.getElementById('modalCurrentStatusBadge');
            statusBadge.innerText = isAlreadyLive ? 'LIVE' : isAlreadyCompleted ? 'FINAL' : 'SCHEDULED';
            statusBadge.className = isAlreadyLive ? 'badge bg-danger' : isAlreadyCompleted ? 'badge bg-success' : 'badge bg-secondary';

            // Set default mode: if already completed, default to final; otherwise default to live
            if (isAlreadyCompleted) {
                document.getElementById('modeFinal').checked = true;
                toggleScoringMode('final');
            } else {
                document.getElementById('modeLive').checked = true;
                toggleScoringMode('live');
            }

            const sportType = getSportTypeFromSlug(m.game_slug);
            document.getElementById('modalSportType').value = sportType;
            
            // Hide all sub-forms
            document.querySelectorAll('.sport-score-form').forEach(el => el.classList.add('d-none'));

            // Populate winner dropdowns
            const winnerSelects = ['bttWinner', 'tenWinner', 'carromWinner', 'bridgeWinner', 'genericWinner'];
            winnerSelects.forEach(selId => {
                const sel = document.getElementById(selId);
                if (sel) {
                    sel.innerHTML = `
                        <option value="">-- Ongoing (No Winner Declared Yet) --</option>
                        <option value="${m.team1_id}">${m.team1_name} (T1)</option>
                        <option value="${m.team2_id}">${m.team2_name} (T2)</option>
                    `;
                    if (m.winner_id) sel.value = m.winner_id;
                }
            });

            const pending = pendingSync.find(p => p.id == id);
            const saved = (pending && pending.scores_json) ? pending.scores_json : 
                          (typeof m.scores_json === 'object' && m.scores_json !== null ? m.scores_json : 
                          (typeof m.scores_json === 'string' && m.scores_json ? JSON.parse(m.scores_json || '{}') : {}));

            if (sportType === 'badminton_table_tennis') {
                document.getElementById('formBadmintonTT').classList.remove('d-none');
                document.getElementById('bttTeam1Label').innerHTML = `<span class="badge me-1" style="background-color: ${m.u1_color || '#003366'}">${m.u1_code || 'T1'}</span> ${m.team1_name}`;
                document.getElementById('bttTeam2Label').innerHTML = `<span class="badge me-1" style="background-color: ${m.u2_color || '#003366'}">${m.u2_code || 'T2'}</span> ${m.team2_name}`;
                document.getElementById('bttSrvTeam1Label').innerText = `${m.u1_code || 'T1'} Serving`;
                document.getElementById('bttSrvTeam2Label').innerText = `${m.u2_code || 'T2'} Serving`;

                const isTT = (m.game_slug && m.game_slug.includes('table-tennis')) || (m.game_name && m.game_name.toLowerCase().includes('table tennis'));
                const isWomen = (m.round && m.round.toLowerCase().includes('women'));
                const isBestOf5 = (isTT && !isWomen) || !!saved.is_best_of_5 || (saved.g4_a !== undefined && saved.g4_a !== null && saved.g4_a !== '') || (saved.g5_a !== undefined && saved.g5_a !== null && saved.g5_a !== '');

                const extraCols = document.querySelectorAll('.btt-extra-col');
                const bannerText = document.getElementById('bttFormatText');
                const bannerBadge = document.getElementById('bttFormatBadge');

                if (isBestOf5) {
                    extraCols.forEach(el => el.classList.remove('d-none'));
                    bannerText.innerHTML = `<i class="fas fa-table-tennis me-1"></i> <strong>Men's Table Tennis:</strong> Best of 5 Games (First to 3 Wins)`;
                    bannerBadge.className = 'badge bg-primary';
                    bannerBadge.innerText = 'Best of 5';
                } else {
                    extraCols.forEach(el => el.classList.add('d-none'));
                    if (isTT) {
                        bannerText.innerHTML = `<i class="fas fa-table-tennis me-1"></i> <strong>Women's Table Tennis:</strong> Best of 3 Games (First to 2 Wins)`;
                        bannerBadge.className = 'badge bg-info text-dark';
                        bannerBadge.innerText = 'Best of 3';
                    } else {
                        bannerText.innerHTML = `<i class="fas fa-feather-alt me-1"></i> <strong>Badminton:</strong> Best of 3 Games (First to 2 Wins)`;
                        bannerBadge.className = 'badge bg-secondary';
                        bannerBadge.innerText = 'Best of 3';
                    }
                }

                document.getElementById('btt_g1_a').value = saved.g1_a ?? '';
                document.getElementById('btt_g1_b').value = saved.g1_b ?? '';
                document.getElementById('btt_g2_a').value = saved.g2_a ?? '';
                document.getElementById('btt_g2_b').value = saved.g2_b ?? '';
                document.getElementById('btt_g3_a').value = saved.g3_a ?? '';
                document.getElementById('btt_g3_b').value = saved.g3_b ?? '';
                document.getElementById('btt_g4_a').value = saved.g4_a ?? '';
                document.getElementById('btt_g4_b').value = saved.g4_b ?? '';
                document.getElementById('btt_g5_a').value = saved.g5_a ?? '';
                document.getElementById('btt_g5_b').value = saved.g5_b ?? '';
                if (saved.server === 'team2') document.getElementById('btt_srv_b').checked = true;
                else document.getElementById('btt_srv_a').checked = true;
                document.getElementById('bttWinner').value = (pending ? pending.winner_id : m.winner_id) || '';

            } else if (sportType === 'tennis') {
                document.getElementById('formTennis').classList.remove('d-none');
                document.getElementById('tenTeam1Label').innerHTML = `<span class="badge me-1" style="background-color: ${m.u1_color || '#003366'}">${m.u1_code || 'T1'}</span> ${m.team1_name}`;
                document.getElementById('tenTeam2Label').innerHTML = `<span class="badge me-1" style="background-color: ${m.u2_color || '#003366'}">${m.u2_code || 'T2'}</span> ${m.team2_name}`;
                
                const sets = saved.sets || [];
                document.getElementById('ten_s1_a').value = saved.s1_a ?? (sets[0]?.t1 ?? '');
                document.getElementById('ten_s1_b').value = saved.s1_b ?? (sets[0]?.t2 ?? '');
                document.getElementById('ten_s2_a').value = saved.s2_a ?? (sets[1]?.t1 ?? '');
                document.getElementById('ten_s2_b').value = saved.s2_b ?? (sets[1]?.t2 ?? '');
                document.getElementById('ten_s3_a').value = saved.s3_a ?? (sets[2]?.t1 ?? '');
                document.getElementById('ten_s3_b').value = saved.s3_b ?? (sets[2]?.t2 ?? '');
                
                const ptA = saved.pts_a ?? (saved.live_points?.t1 ?? '0');
                const ptB = saved.pts_b ?? (saved.live_points?.t2 ?? '0');
                setTennisPt('a', ptA);
                setTennisPt('b', ptB);

                if (saved.server === 't2' || saved.server === 'team2') document.getElementById('ten_srv_t2').checked = true;
                else document.getElementById('ten_srv_t1').checked = true;

                document.getElementById('tenWinner').value = (pending ? pending.winner_id : m.winner_id) || '';

            } else if (sportType === 'chess') {
                document.getElementById('formChess').classList.remove('d-none');
                document.getElementById('chessBoardNo').value = saved.board_no ?? '1';
                document.getElementById('chessWhitePlayer').value = saved.white?.player ?? (saved.white_player ?? m.team1_name);
                document.getElementById('chessWhiteZone').value = saved.white?.zone ?? (saved.white_zone ?? (m.u1_code || 'WZ'));
                document.getElementById('chessBlackPlayer').value = saved.black?.player ?? (saved.black_player ?? m.team2_name);
                document.getElementById('chessBlackZone').value = saved.black?.zone ?? (saved.black_zone ?? (m.u2_code || 'EZ'));
                document.getElementById('chessResult').value = saved.result ?? (isAlreadyLive ? 'in_progress' : '1-0');
                document.getElementById('chessLiveNote').value = saved.live_note ?? '';

            } else if (sportType === 'carrom') {
                document.getElementById('formCarrom').classList.remove('d-none');
                document.getElementById('carromBoardNo').value = saved.board_no ?? '1';
                document.getElementById('carromTeam1Label').innerText = `${m.team1_name} Points`;
                document.getElementById('carromTeam2Label').innerText = `${m.team2_name} Points`;
                document.getElementById('carrom_pts_a').value = saved.points_a ?? '';
                document.getElementById('carrom_pts_b').value = saved.points_b ?? '';
                document.getElementById('carromWinner').value = (pending ? pending.winner_id : m.winner_id) || '';

            } else if (sportType === 'bridge') {
                document.getElementById('formBridge').classList.remove('d-none');
                document.getElementById('bridgeSession').value = saved.session_no ?? '1';
                document.getElementById('bridgeTable').value = saved.table_no ?? '1';
                document.getElementById('bridgeTeam1Label').innerText = m.team1_name;
                document.getElementById('bridgeTeam2Label').innerText = m.team2_name;
                document.getElementById('bridge_imp_a').value = saved.imps_a ?? '';
                document.getElementById('bridge_imp_b').value = saved.imps_b ?? '';
                document.getElementById('bridge_vp_a').value = saved.vps_a ?? '';
                document.getElementById('bridge_vp_b').value = saved.vps_b ?? '';
                document.getElementById('bridgeWinner').value = (pending ? pending.winner_id : m.winner_id) || '';

            } else if (sportType === 'swimming') {
                document.getElementById('formSwimming').classList.remove('d-none');
                document.getElementById('swimEventName').value = saved.event_name ?? '50m Freestyle Men';
                document.getElementById('swimHeatNo').value = saved.heat_no ?? (saved.heat ?? 'Final');

                const tbody = document.getElementById('swimLanesBody');
                tbody.innerHTML = '';
                const savedLanes = saved.lanes || {};
                
                for (let i = 1; i <= 8; i++) {
                    const lData = (Array.isArray(savedLanes) ? savedLanes.find(l => l.lane == i) : savedLanes[i]) || {};
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="fw-bold bg-light">${i}</td>
                        <td><input type="text" id="swim_name_${i}" class="form-control form-control-sm" placeholder="Swimmer Name" value="${lData.swimmer || ''}"></td>
                        <td>
                            <select id="swim_zone_${i}" class="form-select form-select-sm">
                                ${ZONES.map(z => `<option value="${z}" ${z === (lData.zone || 'WZ') ? 'selected' : ''}>${z}</option>`).join('')}
                            </select>
                        </td>
                        <td><input type="text" id="swim_time_${i}" class="form-control form-control-sm text-center font-monospace" placeholder="00:26.40" value="${lData.time || ''}"></td>
                        <td>
                            <select id="swim_pos_${i}" class="form-select form-select-sm">
                                <option value="">--</option>
                                ${['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'].map(p => `<option value="${p}" ${p === lData.position || p === lData.pos ? 'selected' : ''}>${p}</option>`).join('')}
                            </select>
                        </td>
                        <td>
                            <select id="swim_status_${i}" class="form-select form-select-sm">
                                <option value="normal" ${lData.status === 'normal' || !lData.status ? 'selected' : ''}>Normal</option>
                                <option value="dns" ${lData.status === 'dns' ? 'selected' : ''}>DNS</option>
                                <option value="dsq" ${lData.status === 'dsq' ? 'selected' : ''}>DSQ</option>
                            </select>
                        </td>
                    `;
                    tbody.appendChild(tr);
                }

            } else {
                document.getElementById('formGeneric').classList.remove('d-none');
                document.getElementById('scoreInput').value = saved.summary || '';
                document.getElementById('genericWinner').value = (pending ? pending.winner_id : m.winner_id) || '';
            }

            scoreModal.show();
        };

        // Comprehensive Score Processing (Live vs Final)
        window.processScoreSubmission = function(targetStatus) {
            const id = document.getElementById('modalMatchId').value;
            const sportType = document.getElementById('modalSportType').value;
            const m = localMatches.find(x => x.id == id);
            if (!m) return;

            const isFinal = (targetStatus === 'completed');
            let scoresPayload = {};
            let winnerId = null;

            if (sportType === 'badminton_table_tennis') {
                const isTT = (m.game_slug && m.game_slug.includes('table-tennis')) || (m.game_name && m.game_name.toLowerCase().includes('table tennis'));
                const isWomen = (m.round && m.round.toLowerCase().includes('women'));
                const isBestOf5 = (isTT && !isWomen);

                const g1a = document.getElementById('btt_g1_a').value.trim();
                const g1b = document.getElementById('btt_g1_b').value.trim();
                const g2a = document.getElementById('btt_g2_a').value.trim();
                const g2b = document.getElementById('btt_g2_b').value.trim();
                const g3a = document.getElementById('btt_g3_a').value.trim();
                const g3b = document.getElementById('btt_g3_b').value.trim();
                const g4a = document.getElementById('btt_g4_a').value.trim();
                const g4b = document.getElementById('btt_g4_b').value.trim();
                const g5a = document.getElementById('btt_g5_a').value.trim();
                const g5b = document.getElementById('btt_g5_b').value.trim();
                const server = document.querySelector('input[name="btt_server"]:checked')?.value || 'team1';
                winnerId = document.getElementById('bttWinner').value;

                if (isFinal && !winnerId) {
                    alert('Please select the declared match winner to submit the final result.');
                    return;
                }

                // Auto-generate human-readable summary
                const sets = [];
                if (g1a !== '' || g1b !== '') sets.push(`${g1a || 0}-${g1b || 0}`);
                if (g2a !== '' || g2b !== '') sets.push(`${g2a || 0}-${g2b || 0}`);
                if (g3a !== '' || g3b !== '') sets.push(`${g3a || 0}-${g3b || 0}`);
                if (isBestOf5 || g4a !== '' || g4b !== '') {
                    if (g4a !== '' || g4b !== '') sets.push(`${g4a || 0}-${g4b || 0}`);
                }
                if (isBestOf5 || g5a !== '' || g5b !== '') {
                    if (g5a !== '' || g5b !== '') sets.push(`${g5a || 0}-${g5b || 0}`);
                }
                
                let summaryStr = sets.length > 0 ? sets.join(', ') : 'In Progress';
                if (isFinal) {
                    const winName = winnerId == m.team1_id ? m.team1_name : m.team2_name;
                    summaryStr = `${winName} won (${summaryStr})`;
                } else {
                    summaryStr += ` (Live - Srv: ${server === 'team1' ? (m.u1_code || 'T1') : (m.u2_code || 'T2')})`;
                }

                scoresPayload = {
                    type: sportType,
                    is_best_of_5: isBestOf5 ? 1 : 0,
                    g1_a: g1a !== '' ? parseInt(g1a) : null,
                    g1_b: g1b !== '' ? parseInt(g1b) : null,
                    g2_a: g2a !== '' ? parseInt(g2a) : null,
                    g2_b: g2b !== '' ? parseInt(g2b) : null,
                    g3_a: g3a !== '' ? parseInt(g3a) : null,
                    g3_b: g3b !== '' ? parseInt(g3b) : null,
                    g4_a: g4a !== '' ? parseInt(g4a) : null,
                    g4_b: g4b !== '' ? parseInt(g4b) : null,
                    g5_a: g5a !== '' ? parseInt(g5a) : null,
                    g5_b: g5b !== '' ? parseInt(g5b) : null,
                    server: server,
                    status: isFinal ? 'completed' : 'live',
                    winner_name: isFinal && winnerId ? (winnerId == m.team1_id ? m.team1_name : m.team2_name) : null,
                    summary: summaryStr
                };

            } else if (sportType === 'tennis') {
                const s1a = document.getElementById('ten_s1_a').value.trim();
                const s1b = document.getElementById('ten_s1_b').value.trim();
                const s2a = document.getElementById('ten_s2_a').value.trim();
                const s2b = document.getElementById('ten_s2_b').value.trim();
                const s3a = document.getElementById('ten_s3_a').value.trim();
                const s3b = document.getElementById('ten_s3_b').value.trim();
                const ptsA = document.getElementById('ten_pts_a').value || '0';
                const ptsB = document.getElementById('ten_pts_b').value || '0';
                const server = document.querySelector('input[name="ten_server"]:checked')?.value || 'team1';
                winnerId = document.getElementById('tenWinner').value;

                if (isFinal && !winnerId) {
                    alert('Please select the declared match winner to submit the final result.');
                    return;
                }

                const setsArr = [];
                if (s1a !== '' || s1b !== '') setsArr.push({ t1: s1a || '0', t2: s1b || '0' });
                if (s2a !== '' || s2b !== '') setsArr.push({ t1: s2a || '0', t2: s2b || '0' });
                if (s3a !== '' || s3b !== '') setsArr.push({ t1: s3a || '0', t2: s3b || '0' });

                const setSummary = setsArr.map(s => `${s.t1}-${s.t2}`).join(', ');
                let summaryStr = setSummary || 'In Progress';

                if (isFinal) {
                    const winName = winnerId == m.team1_id ? m.team1_name : m.team2_name;
                    summaryStr = `${winName} won (${summaryStr})`;
                } else {
                    summaryStr += ` [Pts: ${ptsA}-${ptsB}*]`;
                }

                scoresPayload = {
                    type: sportType,
                    sets: setsArr,
                    s1_a: s1a, s1_b: s1b,
                    s2_a: s2a, s2_b: s2b,
                    s3_a: s3a, s3_b: s3b,
                    pts_a: ptsA, pts_b: ptsB,
                    live_points: { t1: ptsA, t2: ptsB },
                    server: server === 'team1' ? 't1' : 't2',
                    status: isFinal ? 'completed' : 'in_progress',
                    winner: isFinal && winnerId ? (winnerId == m.team1_id ? m.team1_name : m.team2_name) : null,
                    summary: summaryStr
                };

            } else if (sportType === 'chess') {
                const res = document.getElementById('chessResult').value;
                const whitePlayer = document.getElementById('chessWhitePlayer').value.trim();
                const whiteZone = document.getElementById('chessWhiteZone').value.trim();
                const blackPlayer = document.getElementById('chessBlackPlayer').value.trim();
                const blackZone = document.getElementById('chessBlackZone').value.trim();
                const boardNo = document.getElementById('chessBoardNo').value.trim() || '1';
                const liveNote = document.getElementById('chessLiveNote').value.trim();

                let summaryStr = '';
                if (res === '1-0') {
                    winnerId = m.team1_id;
                    summaryStr = `Board ${boardNo}: 1 - 0 (${whitePlayer || 'White'} Won)`;
                } else if (res === '0-1') {
                    winnerId = m.team2_id;
                    summaryStr = `Board ${boardNo}: 0 - 1 (${blackPlayer || 'Black'} Won)`;
                } else if (res === '1/2-1/2') {
                    winnerId = null;
                    summaryStr = `Board ${boardNo}: ½ - ½ (Draw)`;
                } else {
                    winnerId = null;
                    summaryStr = `Board ${boardNo}: In Progress ${liveNote ? '(' + liveNote + ')' : ''}`;
                }

                scoresPayload = {
                    type: sportType,
                    board_no: boardNo,
                    white: { player: whitePlayer, zone: whiteZone },
                    black: { player: blackPlayer, zone: blackZone },
                    result: res === 'in_progress' ? 'Live' : res,
                    points_a: res === '1-0' ? 1.0 : (res === '1/2-1/2' ? 0.5 : 0.0),
                    points_b: res === '0-1' ? 1.0 : (res === '1/2-1/2' ? 0.5 : 0.0),
                    live_note: liveNote,
                    status: isFinal ? 'completed' : 'in_progress',
                    winner: isFinal && winnerId ? (winnerId == m.team1_id ? m.team1_name : m.team2_name) : null,
                    summary: summaryStr
                };

            } else if (sportType === 'carrom') {
                const ptA = document.getElementById('carrom_pts_a').value.trim();
                const ptB = document.getElementById('carrom_pts_b').value.trim();
                const boardNo = document.getElementById('carromBoardNo').value.trim() || '1';
                winnerId = document.getElementById('carromWinner').value;

                if (isFinal && !winnerId) {
                    alert('Please select the winner to submit final result.');
                    return;
                }

                let summaryStr = `Board ${boardNo}: ${ptA || 0} - ${ptB || 0}`;
                if (isFinal) {
                    const winName = winnerId == m.team1_id ? m.team1_name : m.team2_name;
                    summaryStr = `${winName} won (${summaryStr})`;
                } else {
                    summaryStr += ' (Live)';
                }

                scoresPayload = {
                    type: sportType,
                    board_no: boardNo,
                    points_a: parseInt(ptA || 0, 10),
                    points_b: parseInt(ptB || 0, 10),
                    status: isFinal ? 'completed' : 'in_progress',
                    winner: isFinal && winnerId ? (winnerId == m.team1_id ? m.team1_name : m.team2_name) : null,
                    summary: summaryStr
                };

            } else if (sportType === 'bridge') {
                const impA = document.getElementById('bridge_imp_a').value.trim();
                const impB = document.getElementById('bridge_imp_b').value.trim();
                const vpA = document.getElementById('bridge_vp_a').value.trim();
                const vpB = document.getElementById('bridge_vp_b').value.trim();
                const sessionNo = document.getElementById('bridgeSession').value.trim() || '1';
                const tableNo = document.getElementById('bridgeTable').value.trim() || '1';
                winnerId = document.getElementById('bridgeWinner').value;

                if (isFinal && !winnerId) {
                    alert('Please declare the winning team to submit final result.');
                    return;
                }

                let summaryStr = `Session ${sessionNo} T${tableNo}: IMPs ${impA || 0}-${impB || 0} | VPs ${vpA || 0}-${vpB || 0}`;
                if (isFinal) {
                    const winName = winnerId == m.team1_id ? m.team1_name : m.team2_name;
                    summaryStr = `${winName} won (${summaryStr})`;
                } else {
                    summaryStr += ' (Live)';
                }

                scoresPayload = {
                    type: sportType,
                    session_no: sessionNo,
                    table_no: tableNo,
                    imps_a: parseInt(impA || 0, 10),
                    imps_b: parseInt(impB || 0, 10),
                    vps_a: parseFloat(vpA || 0),
                    vps_b: parseFloat(vpB || 0),
                    status: isFinal ? 'completed' : 'in_progress',
                    winner: isFinal && winnerId ? (winnerId == m.team1_id ? m.team1_name : m.team2_name) : null,
                    summary: summaryStr
                };

            } else if (sportType === 'swimming') {
                const eventName = document.getElementById('swimEventName').value.trim() || 'Swimming Event';
                const heatNo = document.getElementById('swimHeatNo').value.trim() || 'Final';
                const lanes = {};
                let topSwimmer = '';
                let topTime = '';

                for (let i = 1; i <= 8; i++) {
                    const swimmer = document.getElementById(`swim_name_${i}`).value.trim();
                    const zone = document.getElementById(`swim_zone_${i}`).value;
                    const time = document.getElementById(`swim_time_${i}`).value.trim();
                    const pos = document.getElementById(`swim_pos_${i}`).value;
                    const status = document.getElementById(`swim_status_${i}`).value;

                    if (swimmer || time) {
                        lanes[i] = { lane: i, swimmer, zone, time, pos, status };
                        if ((pos === '1st' || i === 1) && !topSwimmer && swimmer) {
                            topSwimmer = swimmer;
                            topTime = time;
                        }
                    }
                }

                winnerId = m.team1_id || null;
                let summaryStr = `${eventName} (${heatNo})`;
                if (topSwimmer) summaryStr += `: 1st ${topSwimmer} (${topTime})`;
                if (!isFinal) summaryStr += ' [Heat In Progress]';

                scoresPayload = {
                    type: sportType,
                    event_name: eventName,
                    category: m.category || "Open",
                    heat: heatNo,
                    heat_no: heatNo,
                    lanes: lanes,
                    status: isFinal ? 'completed' : 'in_progress',
                    winner_name: topSwimmer,
                    summary: summaryStr
                };

            } else {
                const score = document.getElementById('scoreInput').value.trim();
                winnerId = document.getElementById('genericWinner').value;
                if (isFinal && (!score || !winnerId)) {
                    alert('Please enter both the score summary and declare a winner for final submission.');
                    return;
                }
                scoresPayload = { type: 'generic', summary: score + (isFinal ? ' (Final)' : ' (Live)'), status: targetStatus };
            }

            // Save payload to queue
            const syncData = { 
                id: id, 
                scores_json: scoresPayload, 
                status: targetStatus,
                winner_id: winnerId ? parseInt(winnerId) : null 
            };
            
            const existingIdx = pendingSync.findIndex(p => p.id == id);
            if (existingIdx >= 0) pendingSync[existingIdx] = syncData;
            else pendingSync.push(syncData);
            
            localStorage.setItem('pwa_pending_sync', JSON.stringify(pendingSync));
            
            // Also update local copy immediately
            const localIdx = localMatches.findIndex(x => x.id == id);
            if (localIdx >= 0) {
                localMatches[localIdx].status = targetStatus;
                localMatches[localIdx].scores_json = scoresPayload;
                localMatches[localIdx].winner_id = syncData.winner_id;
                localMatches[localIdx].isPending = true;
                localStorage.setItem('pwa_matches', JSON.stringify(localMatches));
            }

            scoreModal.hide();
            renderMatches();
            
            // Show status notification
            const statusMsg = document.getElementById('syncStatusMsg');
            if (isFinal) {
                statusMsg.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> Match #${id} concluded and marked official.`;
            } else {
                statusMsg.innerHTML = `<i class="fas fa-broadcast-tower text-danger me-1"></i> Match #${id} live score updated. Broadcast underway.`;
            }

            // Auto sync if online
            if (navigator.onLine) {
                syncToCloud();
            } else {
                checkPendingSync();
            }
        };

        function checkPendingSync() {
            const banner = document.getElementById('syncBanner');
            const countText = document.getElementById('pendingCountText');
            if (pendingSync.length > 0) {
                banner.classList.remove('d-none');
                banner.classList.add('d-flex');
                countText.innerText = `${pendingSync.length} score update(s) pending cloud broadcast`;
            } else {
                banner.classList.add('d-none');
                banner.classList.remove('d-flex');
            }
        }

        async function syncToCloud() {
            if (!navigator.onLine || pendingSync.length === 0) {
                checkPendingSync();
                return;
            }

            const statusMsg = document.getElementById('syncStatusMsg');
            statusMsg.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Broadcasting score updates to master database...';

            try {
                const res = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(pendingSync)
                });
                
                const json = await res.json();
                if (json.status === 'success') {
                    statusMsg.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> Successfully synced ${json.synced} score update(s) live!`;
                    pendingSync = [];
                    localStorage.removeItem('pwa_pending_sync');
                    fetchMatches();
                } else {
                    statusMsg.innerHTML = `<i class="fas fa-exclamation-triangle text-warning me-1"></i> Sync warning: ${json.message || 'Check connection'}`;
                }
            } catch (e) {
                console.error('Sync failed', e);
                statusMsg.innerHTML = '<i class="fas fa-exclamation-circle text-danger me-1"></i> Sync failed. Will retry automatically.';
            }
            checkPendingSync();
        }

        // ==========================================
        // BRIDGE TOURNAMENT ROUND SCORING SYSTEM
        // ==========================================
        async function fetchBridgeData(force = false) {
            if (!force && bridgeData) return bridgeData;
            const res = await fetch(BRIDGE_API_URL);
            bridgeData = await res.json();
            return bridgeData;
        }

        async function refreshBridgeScores() {
            const statusMsg = document.getElementById('syncStatusMsg');
            statusMsg.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing Bridge tournament data...';
            await fetchBridgeData(true);
            statusMsg.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i> Bridge data up to date.';
            const container = document.getElementById('matchesContainer');
            if (document.getElementById('sportFilter').value === 'bridge') {
                renderBridgeRoundScoring(container);
            }
        }

        function setBridgeRound(roundNo) {
            activeBridgeRound = roundNo;
            const container = document.getElementById('matchesContainer');
            renderBridgeRoundScoring(container);
        }

        function getCanonicalTeamOptions(selectedId, selectedNum) {
            if (!bridgeData || !bridgeData.matrix) return '';
            const sorted = [...bridgeData.matrix].sort((a, b) => a.team_no - b.team_no);
            return sorted.map(t => {
                const isSel = (selectedId && t.team_id == selectedId) || (selectedNum && t.team_no == selectedNum);
                return `<option value="${t.team_id || ''}" data-team-no="${t.team_no}" ${isSel ? 'selected' : ''}>#${t.team_no} ${t.short_code} (${t.name})</option>`;
            }).join('');
        }

        function autoBalanceBridgeVp(tableNo, changedTeam) {
            const vp1Input = document.getElementById(`br_vp1_${tableNo}`);
            const vp2Input = document.getElementById(`br_vp2_${tableNo}`);
            const sumEl = document.getElementById(`br_sum_${tableNo}`);

            if (!vp1Input || !vp2Input) return;

            if (changedTeam === 1) {
                const val1 = parseFloat(vp1Input.value);
                if (!isNaN(val1) && val1 >= 0 && val1 <= 20) {
                    const val2 = Math.max(0, Math.min(20, (20 - val1))).toFixed(2);
                    vp2Input.value = val2;
                }
            } else if (changedTeam === 2) {
                const val2 = parseFloat(vp2Input.value);
                if (!isNaN(val2) && val2 >= 0 && val2 <= 20) {
                    const val1 = Math.max(0, Math.min(20, (20 - val2))).toFixed(2);
                    vp1Input.value = val1;
                }
            }

            const current1 = parseFloat(vp1Input.value) || 0;
            const current2 = parseFloat(vp2Input.value) || 0;
            const currentSum = (current1 + current2).toFixed(2);
            if (sumEl) {
                sumEl.innerHTML = `Sum: ${currentSum} ${currentSum == '20.00' ? '<span class="text-success"><i class="fas fa-check"></i></span>' : '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i></span>'}`;
            }
        }

        async function submitBridgeRound() {
            const btn = document.getElementById('btnSaveBridgeRound');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving Round Scores...';

            const tablesData = [];
            for (let t = 1; t <= 5; t++) {
                const t1Select = document.getElementById(`br_t1_${t}`);
                const t2Select = document.getElementById(`br_t2_${t}`);
                const vp1Input = document.getElementById(`br_vp1_${t}`);
                const vp2Input = document.getElementById(`br_vp2_${t}`);
                const imp1Input = document.getElementById(`br_imp1_${t}`);
                const imp2Input = document.getElementById(`br_imp2_${t}`);
                const statusSelect = document.getElementById(`br_status_${t}`);
                const matchIdInput = document.getElementById(`br_match_id_${t}`);

                const team1Id = t1Select ? t1Select.value : '';
                const team2Id = t2Select ? t2Select.value : '';
                const vp1 = vp1Input && vp1Input.value !== '' ? parseFloat(vp1Input.value) : null;
                const vp2 = vp2Input && vp2Input.value !== '' ? parseFloat(vp2Input.value) : null;
                const imp1 = imp1Input ? parseInt(imp1Input.value || 0, 10) : 0;
                const imp2 = imp2Input ? parseInt(imp2Input.value || 0, 10) : 0;
                const status = statusSelect ? statusSelect.value : 'scheduled';
                const matchId = matchIdInput ? matchIdInput.value : '';

                tablesData.push({
                    table_no: t,
                    match_id: matchId,
                    team1_id: team1Id,
                    team2_id: team2Id,
                    vps_a: vp1,
                    vps_b: vp2,
                    imps_a: imp1,
                    imps_b: imp2,
                    status: status
                });
            }

            try {
                const payload = {
                    round_no: activeBridgeRound,
                    tables: tablesData
                };

                const res = await fetch(BRIDGE_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.status === 'success') {
                    bridgeData = result; // update cached data
                    const statusMsg = document.getElementById('syncStatusMsg');
                    statusMsg.innerHTML = `<i class="fas fa-check-circle text-success me-1"></i> ${result.message}`;
                    alert(`✅ Round ${activeBridgeRound} scores saved successfully! Standings and broadcast updated.`);
                    renderBridgeRoundScoring(document.getElementById('matchesContainer'));
                } else {
                    alert('⚠️ Error saving round: ' + (result.message || 'Check inputs'));
                }
            } catch (err) {
                console.error('Save failed', err);
                alert('❌ Failed to save round scores. Please check internet connection.');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            }
        }

        async function renderBridgeRoundScoring(container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">Loading Bridge Tournament Console & Matrix...</p>
                </div>
            `;

            try {
                await fetchBridgeData();
            } catch (e) {
                console.error(e);
                container.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-circle fa-2x mb-2 d-block"></i>
                            Failed to connect to Bridge scoring service.
                        </div>
                    </div>`;
                return;
            }

            const rRomans = { 1: 'R-I', 2: 'R-II', 3: 'R-III', 4: 'R-IV', 5: 'R-V' };
            const fixtures = (bridgeData.fixtures && bridgeData.fixtures[activeBridgeRound]) ? bridgeData.fixtures[activeBridgeRound] : [];
            const matrix = bridgeData.matrix || [];

            let html = `
            <div class="col-12">
                <!-- Header Banner -->
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff;">
                    <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <span class="badge bg-warning text-dark px-2 py-1 mb-1 fw-bold">Bridge Scoring Console</span>
                            <h5 class="fw-bold mb-0 text-white"><i class="fas fa-clone text-info me-2"></i> HPCL Inter Unit Tournament 2026 - Round-Wise Sheet</h5>
                            <small class="text-white-50">Continuous 20-VP Scale &bull; Auto-computes match VP balance (VP A + VP B = 20.00)</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= BASE_URL ?>/bridge" target="_blank" class="btn btn-sm btn-outline-info fw-bold">
                                <i class="fas fa-external-link-alt me-1"></i> Public Scoreboard
                            </a>
                            <button class="btn btn-sm btn-light fw-bold" onclick="refreshBridgeScores()">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Round Tabs -->
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
                    <div class="card-body p-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="btn-group btn-group-sm flex-wrap" role="group">
            `;

            for (let r = 1; r <= 5; r++) {
                const fixes = (bridgeData.fixtures && bridgeData.fixtures[r]) ? bridgeData.fixtures[r] : [];
                const isDone = fixes.length > 0 && fixes.every(f => f.status === 'completed');
                const isActive = (r === activeBridgeRound);

                html += `
                    <button type="button" class="btn ${isActive ? 'btn-primary active fw-bold' : (isDone ? 'btn-outline-success' : 'btn-outline-secondary')} px-3 py-2" onclick="setBridgeRound(${r})">
                        ${rRomans[r]} ${isDone ? '<i class="fas fa-check ms-1"></i>' : (isActive ? '<span class="badge bg-white text-primary ms-1">Active</span>' : '')}
                    </button>
                `;
            }

            html += `
                        </div>
                        <span class="badge bg-light text-dark border p-2 small">
                            <i class="fas fa-info-circle text-primary me-1"></i> 5 Tables &bull; Round ${rRomans[activeBridgeRound]}
                        </span>
                    </div>
                </div>

                <!-- 5 Tables Entry Sheet Form -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header text-white d-flex justify-content-between align-items-center py-2" style="background-color: var(--hpcl-navy);">
                        <div class="fw-bold">
                            <i class="fas fa-edit me-1 text-warning"></i> Round ${rRomans[activeBridgeRound]} Scoresheet (Tables 1 to 5)
                        </div>
                        <span class="badge bg-white text-dark small fw-bold">Max 20.00 VPs per Match</span>
                    </div>
                    <div class="card-body p-3">
                        <form id="bridgeRoundForm" onsubmit="event.preventDefault(); submitBridgeRound();">
                            <div class="row g-3">
            `;

            // 5 Tables
            for (let t = 1; t <= 5; t++) {
                const fix = fixtures.find(f => f.table_no == t) || { table_no: t, team1_num: null, team2_num: null, team1_id: null, team2_id: null, vps_a: '', vps_b: '', imps_a: 0, imps_b: 0, status: 'scheduled', match_id: null };
                const vpA = fix.vps_a !== null && fix.vps_a !== undefined ? fix.vps_a : '';
                const vpB = fix.vps_b !== null && fix.vps_b !== undefined ? fix.vps_b : '';
                const isCompleted = fix.status === 'completed';

                html += `
                    <div class="col-12">
                        <div class="bridge-table-card p-3 ${isCompleted ? 'border-success border-opacity-50 bg-light' : ''}">
                            <input type="hidden" name="tbl_match_id_${t}" id="br_match_id_${t}" value="${fix.match_id || ''}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary px-2 py-1 fw-bold">Table ${t}</span>
                                <div class="d-flex align-items-center gap-2">
                                    <label class="small text-muted fw-bold mb-0">Status:</label>
                                    <select class="form-select form-select-sm" style="width: 130px;" id="br_status_${t}">
                                        <option value="completed" ${fix.status === 'completed' ? 'selected' : ''}>Concluded</option>
                                        <option value="in_progress" ${fix.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                        <option value="scheduled" ${fix.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center">
                                <!-- Team A Selection & VP -->
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold mb-1 text-primary">Team A</label>
                                    <select class="form-select form-select-sm fw-bold mb-2" id="br_t1_${t}">
                                        <option value="">-- Select Team A --</option>
                                        ${getCanonicalTeamOptions(fix.team1_id, fix.team1_num)}
                                    </select>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="small text-muted mb-0 fw-bold" style="white-space:nowrap;">VPs:</label>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm fw-bold text-center fs-6" id="br_vp1_${t}" placeholder="0.00" value="${vpA}" oninput="autoBalanceBridgeVp(${t}, 1)">
                                        <label class="small text-muted mb-0 fw-bold ms-1" style="white-space:nowrap;">IMPs:</label>
                                        <input type="number" class="form-control form-control-sm text-center" style="max-width: 70px;" id="br_imp1_${t}" placeholder="0" value="${fix.imps_a || 0}">
                                    </div>
                                </div>

                                <!-- VS Badge -->
                                <div class="col-md-2 text-center my-2 my-md-0">
                                    <span class="badge bg-secondary p-2 fw-bold font-monospace">VS</span>
                                    <div class="small text-muted mt-1" id="br_sum_${t}" style="font-size: 0.72rem;">
                                        ${vpA !== '' && vpB !== '' ? `Sum: ${(parseFloat(vpA) + parseFloat(vpB)).toFixed(2)}` : 'Sum: 20.00'}
                                    </div>
                                </div>

                                <!-- Team B Selection & VP -->
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold mb-1 text-danger">Team B</label>
                                    <select class="form-select form-select-sm fw-bold mb-2" id="br_t2_${t}">
                                        <option value="">-- Select Team B --</option>
                                        ${getCanonicalTeamOptions(fix.team2_id, fix.team2_num)}
                                    </select>
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="small text-muted mb-0 fw-bold" style="white-space:nowrap;">VPs:</label>
                                        <input type="number" step="0.01" min="0" max="20" class="form-control form-control-sm fw-bold text-center fs-6" id="br_vp2_${t}" placeholder="0.00" value="${vpB}" oninput="autoBalanceBridgeVp(${t}, 2)">
                                        <label class="small text-muted mb-0 fw-bold ms-1" style="white-space:nowrap;">IMPs:</label>
                                        <input type="number" class="form-control form-control-sm text-center" style="max-width: 70px;" id="br_imp2_${t}" placeholder="0" value="${fix.imps_b || 0}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            html += `
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                <span class="text-muted small">
                                    <i class="fas fa-check-double text-success me-1"></i> Saves all 5 tables for Round ${rRomans[activeBridgeRound]}
                                </span>
                                <button type="submit" class="btn btn-success fw-bold px-4 py-2 shadow-sm" id="btnSaveBridgeRound">
                                    <i class="fas fa-cloud-upload-alt me-1"></i> Save Round ${rRomans[activeBridgeRound]} Scores
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Live Consolidated Scoreboard Matrix -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                        <div class="fw-bold">
                            <i class="fas fa-table text-warning me-1"></i> Live Consolidated Scoreboard (Whiteboard Matrix)
                        </div>
                        <span class="badge bg-primary text-uppercase">10 Teams &bull; Continuous 20-VP Scale</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="bridge-matrix-tbl">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">No.</th>
                                        <th style="text-align: left; min-width: 130px;">Team Name</th>
                                        <th style="width: 90px;">R-I</th>
                                        <th style="width: 90px;">R-II</th>
                                        <th style="width: 90px;">R-III</th>
                                        <th style="width: 90px;">R-IV</th>
                                        <th style="width: 90px;">R-V</th>
                                        <th style="width: 80px;">TOTAL</th>
                                        <th style="width: 60px;">RANK</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;

            // Sort matrix by team_no 1 to 10 for the official scoreboard view
            const sortedMatrix = [...matrix].sort((a, b) => a.team_no - b.team_no);
            sortedMatrix.forEach(row => {
                html += `
                    <tr>
                        <td class="fw-bold text-primary font-monospace">${row.team_no}</td>
                        <td style="text-align: left;">
                            <span class="badge me-1" style="background-color: ${row.color_code || '#0284c7'}; font-size: 0.72rem;">${row.short_code}</span>
                            <strong class="text-dark">${row.name}</strong>
                        </td>
                `;

                for (let r = 1; r <= 5; r++) {
                    const rnd = row.rounds[r];
                    if (rnd && rnd.cum_vp !== null) {
                        html += `
                            <td>
                                <div class="bridge-cell-split" title="Round ${r}: ${rnd.round_vp !== null ? '+' + parseFloat(rnd.round_vp).toFixed(2) + ' VP' : ''} vs Team #${rnd.opp_no}">
                                    <div class="bridge-cell-cum-vp">${parseFloat(rnd.cum_vp).toFixed(2)}</div>
                                    <div class="bridge-cell-opp-no">${rnd.opp_no}</div>
                                </div>
                            </td>
                        `;
                    } else if (rnd && rnd.opp_no) {
                        html += `
                            <td>
                                <div class="bridge-cell-split" title="Scheduled vs Team #${rnd.opp_no}">
                                    <div class="bridge-cell-cum-vp" style="color: #94a3b8;">-</div>
                                    <div class="bridge-cell-opp-no">${rnd.opp_no}</div>
                                </div>
                            </td>
                        `;
                    } else {
                        html += `<td><div class="bridge-cell-empty"></div></td>`;
                    }
                }

                html += `
                        <td class="fw-bold font-monospace text-primary fs-6">${parseFloat(row.total_vp || 0).toFixed(2)}</td>
                        <td>
                            ${row.rank === 1 ? '<span class="badge bg-warning text-dark"><i class="fas fa-crown"></i> 1</span>' :
                              row.rank === 2 ? '<span class="badge bg-secondary">2</span>' :
                              row.rank === 3 ? '<span class="badge bg-danger">3</span>' :
                              `<span class="text-muted fw-bold">${row.rank}</span>`}
                        </td>
                    </tr>
                `;
            });

            html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-2 text-muted small d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-info-circle me-1 text-info"></i> Diagonal split: Top-Left = Cumulative VPs, Bottom-Right = Opponent Team #</span>
                        <span>Sum of both teams per round = 20.00 VPs</span>
                    </div>
                </div>
            </div>
            `;

            container.innerHTML = html;
        }

        // Initialize
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sport') === 'bridge' || urlParams.get('discipline') === 'bridge') {
            const filterEl = document.getElementById('sportFilter');
            if (filterEl) filterEl.value = 'bridge';
        }

        updateNetworkStatus();
        fetchMatches();
        checkPendingSync();
        setInterval(fetchMatches, 15000); // 15s auto-poll for schedule updates
    </script>
</body>
</html>