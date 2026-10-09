<?php
require_once 'config/helpers.php';

$msg = '';
$err = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Switch Leaderboard Mode
    if ($action === 'set_mode') {
        $mode = in_array($_POST['mode'] ?? '', ['auto', 'manual']) ? $_POST['mode'] : 'auto';
        set_system_setting($pdo, 'leaderboard_mode', $mode);
        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'LEADERBOARD_MODE_CHANGED', "Overall Championship Leaderboard mode switched to " . strtoupper($mode));
        $msg = "Overall Championship Leaderboard mode switched to " . ucfirst($mode) . " mode successfully!";
    }

    // Action 2: Save Manual Leaderboard
    elseif ($action === 'save_manual_leaderboard') {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                UPDATE units 
                SET manual_rank = ?, 
                    manual_points = ?, 
                    manual_gold = ?, 
                    manual_silver = ?, 
                    manual_bronze = ?, 
                    manual_notes = ? 
                WHERE id = ?
            ");

            if (isset($_POST['points']) && is_array($_POST['points'])) {
                foreach ($_POST['points'] as $uId => $ptsVal) {
                    $uId = (int)$uId;
                    $rVal = (isset($_POST['rank'][$uId]) && trim($_POST['rank'][$uId]) !== '') ? (int)$_POST['rank'][$uId] : null;
                    $pVal = (int)($ptsVal ?? 0);
                    $gVal = (int)($_POST['gold'][$uId] ?? 0);
                    $sVal = (int)($_POST['silver'][$uId] ?? 0);
                    $bVal = (int)($_POST['bronze'][$uId] ?? 0);
                    $nVal = trim($_POST['notes'][$uId] ?? '');

                    $stmt->execute([$rVal, $pVal, $gVal, $sVal, $bVal, $nVal ?: null, $uId]);
                }
            }

            // Auto-activate manual mode if checkbox is checked
            if (!empty($_POST['activate_manual'])) {
                set_system_setting($pdo, 'leaderboard_mode', 'manual');
            }

            $pdo->commit();
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'MANUAL_LEADERBOARD_UPDATE', "Updated manual leaderboard points and ranks for HPCL units.");
            $msg = "Manual Leaderboard updated successfully! Changes are live across Public Dashboard, Admin Standings, and Exports.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $err = "Failed to save manual leaderboard: " . $e->getMessage();
        }
    }

    // Action 3: Update 5-3-1 Scheme
    elseif ($action === 'update_scheme') {
        $p1 = (int)($_POST['pos_1'] ?? 5);
        $p2 = (int)($_POST['pos_2'] ?? 3);
        $p3 = (int)($_POST['pos_3'] ?? 1);

        $pdo->prepare("UPDATE points_scheme SET points = ? WHERE position = 1")->execute([$p1]);
        $pdo->prepare("UPDATE points_scheme SET points = ? WHERE position = 2")->execute([$p2]);
        $pdo->prepare("UPDATE points_scheme SET points = ? WHERE position = 3")->execute([$p3]);

        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'POINTS_SCHEME_UPDATE', "Updated points scheme to 1st: $p1, 2nd: $p2, 3rd: $p3 (FR-22 / Section 7)");
        $msg = "Overall Championship points scheme updated successfully.";
    }
}

// Fetch active mode
$currentMode = get_system_setting($pdo, 'leaderboard_mode', 'auto');

// Fetch current points scheme
$scheme = $pdo->query("SELECT position, points, label FROM points_scheme ORDER BY position ASC")->fetchAll();
$pts = [];
foreach ($scheme as $s) {
    $pts[$s['position']] = $s['points'];
}

// Fetch units for manual editing (ordered by rank or name)
$editableUnits = $pdo->query("
    SELECT id, name, short_code, color_code, logo_path,
           manual_rank, manual_points, manual_gold, manual_silver, manual_bronze, manual_notes
    FROM units 
    ORDER BY (manual_rank IS NULL OR manual_rank = 0), manual_rank ASC, manual_points DESC, name ASC
")->fetchAll();

// Compute auto leaderboard for sync comparison
$autoLeaderboard = get_overall_championship_leaderboard($pdo, 'auto');
$autoMap = [];
foreach ($autoLeaderboard as $alb) {
    $autoMap[$alb['id']] = [
        'rank' => $alb['rank'] ?? '',
        'points' => (int)($alb['total_points'] ?? 0),
        'gold' => (int)($alb['gold'] ?? 0),
        'silver' => (int)($alb['silver'] ?? 0),
        'bronze' => (int)($alb['bronze'] ?? 0)
    ];
}

// Fetch live active leaderboard for preview
$activeLeaderboard = get_overall_championship_leaderboard($pdo);
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-7">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-trophy text-warning mr-2"></i> Championship Leaderboard & Points</h1>
        <p class="text-muted mb-0">Manage Overall Championship rankings via automated match calculations or direct manual admin override</p>
      </div>
      <div class="col-sm-5 text-right">
        <a href="<?= BASE_URL ?>/#leaderboard" target="_blank" class="btn btn-outline-primary btn-sm mr-1">
          <i class="fas fa-external-link-alt mr-1"></i> View Public Leaderboard
        </a>
        <a href="<?= BASE_URL ?>/admin/export?download=overall_pdf" target="_blank" class="btn btn-outline-danger btn-sm">
          <i class="fas fa-file-pdf mr-1"></i> Export PDF
        </a>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <?php if ($msg): ?>
      <div class="alert alert-success alert-dismissible fade show elevation-1">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger alert-dismissible fade show elevation-1">
        <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($err) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <!-- MODE SWITCHER CALLOUT CARD -->
    <div class="card elevation-2 <?= $currentMode === 'manual' ? 'border-warning' : 'border-success' ?>" style="border-left: 6px solid <?= $currentMode === 'manual' ? '#ffc107' : '#28a745' ?>;">
      <div class="card-body py-3">
        <div class="row align-items-center">
          <div class="col-md-8">
            <div class="d-flex align-items-center">
              <?php if ($currentMode === 'manual'): ?>
                <span class="badge badge-warning text-dark px-3 py-2 mr-3 font-weight-bold" style="font-size: 0.95rem;">
                  <i class="fas fa-user-edit mr-1"></i> MANUAL MODE ACTIVE
                </span>
                <div>
                  <h5 class="mb-0 font-weight-bold text-dark">Leaderboard is governed by Manual Admin Override</h5>
                  <small class="text-muted">The Public Dashboard, Admin Overview, and Exports are displaying the official manual points & ranks configured below.</small>
                </div>
              <?php else: ?>
                <span class="badge badge-success px-3 py-2 mr-3 font-weight-bold" style="font-size: 0.95rem;">
                  <i class="fas fa-robot mr-1"></i> AUTOMATIC MODE ACTIVE
                </span>
                <div>
                  <h5 class="mb-0 font-weight-bold text-dark">Leaderboard is auto-computed from completed match finals</h5>
                  <small class="text-muted">Rankings are calculated automatically using the 5-3-1 points scheme (1st: 5 pts, 2nd: 3 pts, 3rd: 1 pt).</small>
                </div>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <form method="POST" class="d-inline">
              <input type="hidden" name="action" value="set_mode">
              <?php if ($currentMode === 'manual'): ?>
                <input type="hidden" name="mode" value="auto">
                <button type="submit" class="btn btn-outline-success font-weight-bold" onclick="return confirm('Switch to Automatic Calculation mode? Standings will be computed live from match finals.');">
                  <i class="fas fa-robot mr-1"></i> Switch to Automatic Mode
                </button>
              <?php else: ?>
                <input type="hidden" name="mode" value="manual">
                <button type="submit" class="btn btn-warning font-weight-bold text-dark" onclick="return confirm('Switch to Manual Override mode? The points and ranks specified in the manual table will take effect immediately.');">
                  <i class="fas fa-user-edit mr-1"></i> Switch to Manual Mode
                </button>
              <?php endif; ?>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- MAIN SECTION: MANUAL LEADERBOARD EDITOR -->
    <div class="row">
      <div class="col-12">
        <div class="card elevation-2">
          <div class="card-header bg-gradient-navy text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
              <i class="fas fa-edit mr-2 text-warning"></i> Manual Points & Rank Management (All HPCL Units)
            </h3>
            <div class="card-tools">
              <button type="button" class="btn btn-info btn-sm mr-2" onclick="syncFromAutoResults()">
                <i class="fas fa-sync-alt mr-1"></i> Autofill from Match Results
              </button>
              <button type="button" class="btn btn-outline-light btn-sm" onclick="clearAllManual()">
                <i class="fas fa-eraser mr-1"></i> Reset All Inputs
              </button>
            </div>
          </div>

          <form method="POST" id="manualLeaderboardForm">
            <input type="hidden" name="action" value="save_manual_leaderboard">
            <div class="card-body p-0 table-responsive">
              <table class="table table-bordered table-hover mb-0 align-middle">
                <thead class="bg-light">
                  <tr class="text-center text-uppercase small font-weight-bold text-secondary">
                    <th style="width: 70px;">Rank</th>
                    <th class="text-left" style="min-width: 220px;">HPCL Unit</th>
                    <th style="width: 140px;"><i class="fas fa-star text-primary mr-1"></i> Total Points</th>
                    <th style="width: 110px;"><i class="fas fa-medal text-warning mr-1"></i> Gold (1st)</th>
                    <th style="width: 110px;"><i class="fas fa-medal text-secondary mr-1"></i> Silver (2nd)</th>
                    <th style="width: 110px;"><i class="fas fa-medal mr-1" style="color: #cd7f32;"></i> Bronze (3rd)</th>
                    <th>Official Remarks / Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($editableUnits as $u): ?>
                    <?php 
                      $uid = (int)$u['id'];
                      $hasPoints = !empty($u['manual_points']) && $u['manual_points'] > 0;
                      $rVal = $u['manual_rank'];
                      $isTop = ($rVal == 1);
                    ?>
                    <tr class="<?= $isTop ? 'table-warning' : '' ?>" id="row_<?= $uid ?>">
                      <!-- Rank Input -->
                      <td class="text-center align-middle">
                        <div class="input-group input-group-sm">
                          <input type="number" 
                                 name="rank[<?= $uid ?>]" 
                                 id="rank_<?= $uid ?>" 
                                 class="form-control text-center font-weight-bold manual-rank-input <?= $isTop ? 'border-warning bg-white' : '' ?>" 
                                 min="1" 
                                 max="50" 
                                 placeholder="Auto"
                                 value="<?= htmlspecialchars($rVal ?? '') ?>"
                                 title="Leave empty to calculate automatically from points">
                        </div>
                      </td>

                      <!-- Unit Info -->
                      <td class="align-middle">
                        <div class="d-flex align-items-center">
                          <span class="badge mr-2 px-2 py-1" style="background-color: <?= $u['color_code'] ?: '#0d6efd' ?>; color: #fff; font-size: 0.9rem; min-width: 48px; text-align: center;">
                            <?= htmlspecialchars($u['short_code']) ?>
                          </span>
                          <div>
                            <strong class="text-dark font-weight-bold"><?= htmlspecialchars($u['name']) ?></strong>
                            <?php if ($isTop): ?>
                              <span class="badge badge-warning text-dark ml-2"><i class="fas fa-crown"></i> Rank 1</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>

                      <!-- Points Input -->
                      <td class="align-middle text-center">
                        <div class="input-group input-group-sm">
                          <input type="number" 
                                 name="points[<?= $uid ?>]" 
                                 id="points_<?= $uid ?>" 
                                 class="form-control text-center font-weight-bold text-primary manual-pts-input" 
                                 min="0" 
                                 max="999" 
                                 value="<?= (int)($u['manual_points'] ?? 0) ?>"
                                 style="font-size: 1.05rem;"
                                 required>
                          <div class="input-group-append">
                            <span class="input-group-text font-weight-bold small text-muted">pts</span>
                          </div>
                        </div>
                      </td>

                      <!-- Gold Input -->
                      <td class="align-middle text-center">
                        <input type="number" 
                               name="gold[<?= $uid ?>]" 
                               id="gold_<?= $uid ?>" 
                               class="form-control form-control-sm text-center font-weight-bold text-warning manual-gold-input" 
                               min="0" 
                               max="99" 
                               value="<?= (int)($u['manual_gold'] ?? 0) ?>">
                      </td>

                      <!-- Silver Input -->
                      <td class="align-middle text-center">
                        <input type="number" 
                               name="silver[<?= $uid ?>]" 
                               id="silver_<?= $uid ?>" 
                               class="form-control form-control-sm text-center font-weight-bold text-secondary manual-silver-input" 
                               min="0" 
                               max="99" 
                               value="<?= (int)($u['manual_silver'] ?? 0) ?>">
                      </td>

                      <!-- Bronze Input -->
                      <td class="align-middle text-center">
                        <input type="number" 
                               name="bronze[<?= $uid ?>]" 
                               id="bronze_<?= $uid ?>" 
                               class="form-control form-control-sm text-center font-weight-bold manual-bronze-input" 
                               style="color: #cd7f32;" 
                               min="0" 
                               max="99" 
                               value="<?= (int)($u['manual_bronze'] ?? 0) ?>">
                      </td>

                      <!-- Remarks / Notes Input -->
                      <td class="align-middle">
                        <input type="text" 
                               name="notes[<?= $uid ?>]" 
                               id="notes_<?= $uid ?>" 
                               class="form-control form-control-sm manual-notes-input" 
                               placeholder="e.g. Winner Tennis & Badminton" 
                               value="<?= htmlspecialchars($u['manual_notes'] ?? '') ?>">
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="card-footer bg-light d-flex justify-content-between align-items-center py-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="activateManualCheck" name="activate_manual" value="1" <?= $currentMode === 'manual' ? 'checked' : 'checked' ?>>
                <label class="custom-control-label font-weight-bold text-dark" for="activateManualCheck">
                  Apply & activate Manual Override Mode upon saving
                </label>
                <div class="small text-muted">Ensures Public Dashboard and Reports immediately show these manual standings.</div>
              </div>

              <div>
                <button type="submit" class="btn btn-primary btn-lg font-weight-bold px-4 elevation-1">
                  <i class="fas fa-save mr-2"></i> Save Manual Leaderboard
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- SECOND ROW: 5-3-1 SCHEME CONFIGURATION & LIVE STANDINGS PREVIEW -->
    <div class="row">
      <!-- Left Column: Configure 5-3-1 Scheme -->
      <div class="col-lg-5">
        <div class="card elevation-2">
          <div class="card-header bg-gradient-primary text-white">
            <h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-2"></i> Configure 5-3-1 Points Scheme</h3>
          </div>
          <form method="POST">
            <input type="hidden" name="action" value="update_scheme">
            <div class="card-body">
              <div class="callout callout-info">
                <h6 class="font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Section 7 Rules:</h6>
                <ul class="pl-3 mb-0 small">
                  <li>Points apply equally to Men's, Women's, and Individual disciplines (credited to player's unit).</li>
                  <li>Swimming is counted as one team placement per category.</li>
                  <li><strong>Tie-breakers:</strong> Most 1st places &rarr; Most 2nd places &rarr; Most 3rd places.</li>
                </ul>
              </div>

              <div class="form-group">
                <label><i class="fas fa-medal text-warning mr-1"></i> 1st Place (Gold / Winner) Points</label>
                <input type="number" name="pos_1" class="form-control form-control-lg font-weight-bold" value="<?= $pts[1] ?? 5 ?>" min="1" max="50" required>
              </div>

              <div class="form-group">
                <label><i class="fas fa-medal text-secondary mr-1"></i> 2nd Place (Silver / Runner-up) Points</label>
                <input type="number" name="pos_2" class="form-control form-control-lg font-weight-bold" value="<?= $pts[2] ?? 3 ?>" min="0" max="50" required>
              </div>

              <div class="form-group">
                <label><i class="fas fa-medal mr-1" style="color: #cd7f32;"></i> 3rd Place (Bronze / 3rd Place) Points</label>
                <input type="number" name="pos_3" class="form-control form-control-lg font-weight-bold" value="<?= $pts[3] ?? 1 ?>" min="0" max="50" required>
              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" class="btn btn-primary font-weight-bold">
                <i class="fas fa-save mr-1"></i> Update Points Scheme
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Right Column: Live Standings Preview -->
      <div class="col-lg-7">
        <div class="card elevation-2">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
              <i class="fas fa-list-ol text-warning mr-2"></i> Current Live Standings (Public & Report View)
            </h3>
            <span class="badge <?= $currentMode === 'manual' ? 'badge-warning text-dark' : 'badge-success' ?> font-weight-bold">
              <?= $currentMode === 'manual' ? '<i class="fas fa-user-edit mr-1"></i> Mode: Manual Override' : '<i class="fas fa-robot mr-1"></i> Mode: Auto-computed' ?>
            </span>
          </div>
          <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover mb-0">
              <thead class="thead-dark">
                <tr>
                  <th style="width: 70px;">Rank</th>
                  <th>HPCL Unit</th>
                  <th class="text-center"><i class="fas fa-medal text-warning"></i> Gold</th>
                  <th class="text-center"><i class="fas fa-medal text-secondary"></i> Silver</th>
                  <th class="text-center"><i class="fas fa-medal" style="color: #cd7f32;"></i> Bronze</th>
                  <th class="text-right">Total Points</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($activeLeaderboard as $idx => $row): ?>
                  <?php 
                    $hasRank = !empty($row['rank']);
                    $rankVal = $row['rank'] ?? null;
                    $isTop = ($hasRank && $rankVal === 1);
                  ?>
                  <tr class="<?= $isTop ? 'table-warning font-weight-bold' : '' ?>">
                    <td>
                      <?php if (!$hasRank): ?>
                        <span class="text-muted font-weight-bold">—</span>
                      <?php elseif ($rankVal === 1): ?>
                        <span class="badge badge-warning text-dark"><i class="fas fa-crown"></i> 1</span>
                      <?php else: ?>
                        <strong>#<?= $rankVal ?></strong>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge mr-1" style="background-color: <?= $row['color_code'] ?: '#0d6efd' ?>; color: #fff;"><?= htmlspecialchars($row['short_code']) ?></span>
                      <?= htmlspecialchars($row['name']) ?>
                    </td>
                    <td class="text-center font-weight-bold"><?= $row['gold'] ?></td>
                    <td class="text-center font-weight-bold"><?= $row['silver'] ?></td>
                    <td class="text-center font-weight-bold"><?= $row['bronze'] ?></td>
                    <td class="text-right">
                      <span class="badge badge-primary px-3 py-2" style="font-size: 0.95rem;">
                        <?= $row['total_points'] ?> pts
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
// Auto-results map computed from live match finals
const autoResults = <?= json_encode($autoMap) ?>;

function syncFromAutoResults() {
    if (!confirm("Populate the manual leaderboard inputs with current live match results? You will be able to review and modify any points or ranks before saving.")) {
        return;
    }

    for (const [unitId, data] of Object.entries(autoResults)) {
        const rankInput = document.getElementById('rank_' + unitId);
        const ptsInput = document.getElementById('points_' + unitId);
        const goldInput = document.getElementById('gold_' + unitId);
        const silverInput = document.getElementById('silver_' + unitId);
        const bronzeInput = document.getElementById('bronze_' + unitId);

        if (rankInput) rankInput.value = data.rank || '';
        if (ptsInput) ptsInput.value = data.points || 0;
        if (goldInput) goldInput.value = data.gold || 0;
        if (silverInput) silverInput.value = data.silver || 0;
        if (bronzeInput) bronzeInput.value = data.bronze || 0;
    }

    alert("Leaderboard inputs populated with match results. Please review and click 'Save Manual Leaderboard' to commit.");
}

function clearAllManual() {
    if (!confirm("Reset all manual points, ranks, and medals to 0/empty?")) {
        return;
    }

    document.querySelectorAll('.manual-rank-input').forEach(el => el.value = '');
    document.querySelectorAll('.manual-pts-input').forEach(el => el.value = 0);
    document.querySelectorAll('.manual-gold-input').forEach(el => el.value = 0);
    document.querySelectorAll('.manual-silver-input').forEach(el => el.value = 0);
    document.querySelectorAll('.manual-bronze-input').forEach(el => el.value = 0);
    document.querySelectorAll('.manual-notes-input').forEach(el => el.value = '');
}
</script>
