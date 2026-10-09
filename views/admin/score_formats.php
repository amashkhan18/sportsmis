<?php
/**
 * HPCL SportsMIS - Custom Score Formats Management
 * Admin screen allowing creation, configuration, and assignment of custom score formats
 * for games and matches (e.g. Best of 5 vs Best of 3, Quarters, Halves, Points).
 */
require_once 'config/helpers.php';
require_once 'config/scorecards.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

// -------------------------------------------------------------
// POST HANDLERS (Create, Edit, Delete, Assign)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_format' || $action === 'edit_format') {
        $format_id = (int)($_POST['format_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $game_id = !empty($_POST['game_id']) ? (int)$_POST['game_id'] : null;
        $type = trim($_POST['type'] ?? 'sets');
        $badge_text = trim($_POST['badge_text'] ?? '');
        $win_rule = trim($_POST['win_rule'] ?? 'target_wins');
        $target_wins = (int)($_POST['target_wins'] ?? 2);
        $points_to_win = (int)($_POST['points_to_win'] ?? 21);
        $has_serving = !empty($_POST['has_serving']) ? 1 : 0;
        $description = trim($_POST['description'] ?? '');

        // Parse dynamic columns from form
        $col_keys = $_POST['col_key'] ?? [];
        $col_labels = $_POST['col_label'] ?? [];
        $col_shorts = $_POST['col_short'] ?? [];
        $col_maxes = $_POST['col_max'] ?? [];

        $columns = [];
        for ($i = 0; $i < count($col_keys); $i++) {
            $k = trim($col_keys[$i] ?? '');
            if (!$k) continue;
            $columns[] = [
                'key' => strtolower($k),
                'label' => trim($col_labels[$i] ?? "Game " . ($i + 1)),
                'short' => trim($col_shorts[$i] ?? "G" . ($i + 1)),
                'max' => (int)($col_maxes[$i] ?? 40)
            ];
        }

        if (empty($columns)) {
            // Default 3 columns if none provided
            $columns = [
                ['key' => 'g1', 'label' => 'Game 1', 'short' => 'G1', 'max' => 40],
                ['key' => 'g2', 'label' => 'Game 2', 'short' => 'G2', 'max' => 40],
                ['key' => 'g3', 'label' => 'Game 3', 'short' => 'G3', 'max' => 40]
            ];
        }

        $total_columns = count($columns);
        $columns_json = json_encode($columns);

        if (!$name || !$code) {
            $err = "Format Name and Unique Code are mandatory.";
        } else {
            if ($action === 'create_format') {
                // Check code uniqueness
                $chk = $pdo->prepare("SELECT id FROM score_formats WHERE code = ?");
                $chk->execute([$code]);
                if ($chk->fetch()) {
                    $err = "A score format with code '$code' already exists. Please choose a unique code.";
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO score_formats (name, code, game_id, type, columns_json, total_columns, win_rule, target_wins, points_to_win, has_serving, badge_text, description, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([$name, $code, $game_id, $type, $columns_json, $total_columns, $win_rule, $target_wins, $points_to_win, $has_serving, $badge_text, $description]);
                    $newId = $pdo->lastInsertId();

                    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'SCORE_FORMAT_CREATE', "Created custom score format #$newId ('$name', code: '$code')");
                    $_SESSION['flash_msg'] = "Custom Score Format '{$name}' created successfully!";
                    header("Location: " . BASE_URL . "/admin/score-formats");
                    exit;
                }
            } else {
                // Edit format
                $chk = $pdo->prepare("SELECT id FROM score_formats WHERE code = ? AND id != ?");
                $chk->execute([$code, $format_id]);
                if ($chk->fetch()) {
                    $err = "Another score format with code '$code' already exists.";
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE score_formats
                        SET name = ?, code = ?, game_id = ?, type = ?, columns_json = ?, total_columns = ?, win_rule = ?, target_wins = ?, points_to_win = ?, has_serving = ?, badge_text = ?, description = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $code, $game_id, $type, $columns_json, $total_columns, $win_rule, $target_wins, $points_to_win, $has_serving, $badge_text, $description, $format_id]);

                    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'SCORE_FORMAT_UPDATE', "Updated score format #$format_id ('$name')");
                    $_SESSION['flash_msg'] = "Score Format '{$name}' updated successfully!";
                    header("Location: " . BASE_URL . "/admin/score-formats");
                    exit;
                }
            }
        }
    }

    if ($action === 'delete_format') {
        $format_id = (int)($_POST['format_id'] ?? 0);
        if ($format_id > 0) {
            // Check if matches or games use this format
            $mCount = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE score_format_id = ?");
            $mCount->execute([$format_id]);
            $usedInMatches = (int)$mCount->fetchColumn();

            $gCount = $pdo->prepare("SELECT COUNT(*) FROM games WHERE score_format_id = ?");
            $gCount->execute([$format_id]);
            $usedInGames = (int)$gCount->fetchColumn();

            if ($usedInMatches > 0 || $usedInGames > 0) {
                // Remove references or block
                $pdo->prepare("UPDATE matches SET score_format_id = NULL WHERE score_format_id = ?")->execute([$format_id]);
                $pdo->prepare("UPDATE games SET score_format_id = NULL WHERE score_format_id = ?")->execute([$format_id]);
            }

            $pdo->prepare("DELETE FROM score_formats WHERE id = ?")->execute([$format_id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'SCORE_FORMAT_DELETE', "Deleted score format #$format_id (was linked to $usedInMatches matches, $usedInGames games)");
            $_SESSION['flash_msg'] = "Score format deleted successfully. Match references reset.";
            header("Location: " . BASE_URL . "/admin/score-formats");
            exit;
        }
    }

    if ($action === 'assign_to_game') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        $format_id = !empty($_POST['format_id']) ? (int)$_POST['format_id'] : null;

        if ($game_id > 0) {
            $stmt = $pdo->prepare("UPDATE games SET score_format_id = ? WHERE id = ?");
            $stmt->execute([$format_id, $game_id]);

            // Optionally apply to all matches of this game that don't have a custom override
            if (!empty($_POST['apply_to_existing_matches'])) {
                $mStmt = $pdo->prepare("UPDATE matches SET score_format_id = ? WHERE game_id = ?");
                $mStmt->execute([$format_id, $game_id]);
            }

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'SCORE_FORMAT_ASSIGN_GAME', "Assigned format #$format_id to game #$game_id");
            $_SESSION['flash_msg'] = "Default score format updated for the selected discipline!";
            header("Location: " . BASE_URL . "/admin/score-formats");
            exit;
        }
    }

    if ($action === 'assign_to_matches') {
        $format_id = !empty($_POST['format_id']) ? (int)$_POST['format_id'] : null;
        $match_ids = $_POST['match_ids'] ?? [];

        if (!empty($match_ids) && is_array($match_ids)) {
            $inClause = implode(',', array_map('intval', $match_ids));
            $pdo->prepare("UPDATE matches SET score_format_id = ? WHERE id IN ($inClause)")->execute([$format_id]);

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'SCORE_FORMAT_ASSIGN_MATCHES', "Assigned format #$format_id to " . count($match_ids) . " matches");
            $_SESSION['flash_msg'] = "Score format assigned to " . count($match_ids) . " matches successfully!";
            header("Location: " . BASE_URL . "/admin/score-formats");
            exit;
        } else {
            $err = "Please select at least one match to assign the format.";
        }
    }
}

// -------------------------------------------------------------
// DATA QUERIES
// -------------------------------------------------------------
// All formats with usage counts
$formats = $pdo->query("
    SELECT sf.*, g.name as game_name,
           (SELECT COUNT(*) FROM matches m WHERE m.score_format_id = sf.id) as match_count,
           (SELECT COUNT(*) FROM games gm WHERE gm.score_format_id = sf.id) as game_default_count
    FROM score_formats sf
    LEFT JOIN games g ON sf.game_id = g.id
    ORDER BY sf.id ASC
")->fetchAll();

// All games
$games = $pdo->query("SELECT id, name, slug, category, score_format_id FROM games ORDER BY name ASC")->fetchAll();

// Matches with current formats for quick assignment
$matches = $pdo->query("
    SELECT m.id, m.match_date, m.round, m.status, m.score_format_id,
           g.name as game_name, g.id as game_id,
           t1.name as team1_name, u1.short_code as u1_code,
           t2.name as team2_name, u2.short_code as u2_code,
           sf.name as format_name, sf.badge_text as format_badge
    FROM matches m
    JOIN games g ON m.game_id = g.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    LEFT JOIN score_formats sf ON m.score_format_id = sf.id
    ORDER BY g.name ASC, m.match_date ASC, m.id ASC
")->fetchAll();

$totalFormats = count($formats);
$totalMatchesAssigned = $pdo->query("SELECT COUNT(*) FROM matches WHERE score_format_id IS NOT NULL")->fetchColumn();
?>

<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold text-dark">
          <i class="fas fa-sliders-h text-warning mr-2"></i> Custom Score Formats
        </h1>
        <p class="text-muted small mb-0">Configure bespoke score templates (Best of 5, Best of 3, Quarters, Halves) reflected across Public Dashboard and Social Studio.</p>
      </div>
      <div class="col-sm-6 text-right">
        <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#formatModal" onclick="openCreateModal()">
          <i class="fas fa-plus mr-1"></i> Create Custom Format
        </button>
        <button type="button" class="btn btn-outline-info font-weight-bold ml-2 shadow-sm" data-toggle="modal" data-target="#assignMatchesModal">
          <i class="fas fa-tasks mr-1"></i> Bulk Assign to Matches
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid">

    <?php if ($msg): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($err) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div class="row">
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border">
          <span class="info-box-icon bg-primary"><i class="fas fa-list-ol"></i></span>
          <div class="info-box-content">
            <span class="info-box-text text-muted text-uppercase small font-weight-bold">Score Formats</span>
            <span class="info-box-number text-dark h4 mb-0"><?= $totalFormats ?></span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border">
          <span class="info-box-icon bg-success"><i class="fas fa-check-double"></i></span>
          <div class="info-box-content">
            <span class="info-box-text text-muted text-uppercase small font-weight-bold">Assigned Matches</span>
            <span class="info-box-number text-dark h4 mb-0"><?= $totalMatchesAssigned ?></span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border">
          <span class="info-box-icon bg-warning"><i class="fas fa-table-tennis text-white"></i></span>
          <div class="info-box-content">
            <span class="info-box-text text-muted text-uppercase small font-weight-bold">TT Men / Women Rule</span>
            <span class="info-box-number text-dark font-weight-bold small mb-0">Men: Bo5 • Women: Bo3</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm border">
          <span class="info-box-icon bg-info"><i class="fas fa-magic"></i></span>
          <div class="info-box-content">
            <span class="info-box-text text-muted text-uppercase small font-weight-bold">Sync Channels</span>
            <span class="info-box-number text-dark font-weight-bold small mb-0">Public & Social Studio</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Score Formats Master List Card -->
    <div class="card card-outline card-primary shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h3 class="card-title font-weight-bold text-dark mb-0">
          <i class="fas fa-th-list text-primary mr-2"></i> Configured Score Formats & Discipline Rules
        </h3>
        <div class="card-tools">
          <span class="badge badge-light border px-2 py-1"><?= count($formats) ?> Formats Active</span>
        </div>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-striped mb-0 align-middle">
          <thead class="thead-light">
            <tr>
              <th style="width: 50px;" class="text-center">#</th>
              <th>Format Name & Badge</th>
              <th>Format Code</th>
              <th>Linked Discipline</th>
              <th>Format Type</th>
              <th>Columns Layout</th>
              <th>Win Rule</th>
              <th class="text-center">Usage</th>
              <th class="text-right pr-3" style="width: 170px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($formats)): ?>
              <tr>
                <td colspan="9" class="text-center py-4 text-muted">
                  <i class="fas fa-info-circle mr-1"></i> No custom score formats configured yet. Click "Create Custom Format" above to add one.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($formats as $f): ?>
                <?php 
                  $cols = json_decode($f['columns_json'] ?? '[]', true) ?: [];
                ?>
                <tr>
                  <td class="text-center font-weight-bold text-muted"><?= $f['id'] ?></td>
                  <td>
                    <div class="font-weight-bold text-dark"><?= htmlspecialchars($f['name']) ?></div>
                    <?php if ($f['badge_text']): ?>
                      <span class="badge badge-pill badge-primary font-weight-normal px-2 py-1" style="font-size: 0.75rem;">
                        <i class="fas fa-award mr-1"></i> <?= htmlspecialchars($f['badge_text']) ?>
                      </span>
                    <?php endif; ?>
                    <?php if ($f['description']): ?>
                      <div class="text-muted small mt-1"><?= htmlspecialchars($f['description']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <code class="bg-light px-2 py-1 rounded text-dark font-weight-bold"><?= htmlspecialchars($f['code']) ?></code>
                  </td>
                  <td>
                    <?php if ($f['game_name']): ?>
                      <span class="badge badge-info"><i class="fas fa-running mr-1"></i> <?= htmlspecialchars($f['game_name']) ?></span>
                    <?php else: ?>
                      <span class="badge badge-secondary"><i class="fas fa-globe mr-1"></i> Universal (Any Game)</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="text-uppercase font-weight-bold text-secondary small"><?= htmlspecialchars($f['type']) ?></span>
                  </td>
                  <td>
                    <div class="d-flex flex-wrap gap-1">
                      <?php foreach ($cols as $c): ?>
                        <span class="badge badge-dark mr-1" title="<?= htmlspecialchars($c['label'] ?? '') ?>">
                          <?= htmlspecialchars($c['short'] ?? $c['key']) ?>
                        </span>
                      <?php endforeach; ?>
                      <span class="badge badge-light border text-muted">(<?= count($cols) ?> cols)</span>
                    </div>
                  </td>
                  <td>
                    <small class="font-weight-bold text-dark">
                      <?php if ($f['win_rule'] === 'target_wins'): ?>
                        First to <?= $f['target_wins'] ?> Wins (<?= $f['points_to_win'] ?> pts)
                      <?php elseif ($f['win_rule'] === 'total_score'): ?>
                        Total Score Summation
                      <?php else: ?>
                        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $f['win_rule']))) ?>
                      <?php endif; ?>
                    </small>
                  </td>
                  <td class="text-center">
                    <?php if ($f['match_count'] > 0): ?>
                      <span class="badge badge-success px-2 py-1 font-weight-bold" title="Directly assigned to matches">
                        <i class="fas fa-futbol mr-1"></i> <?= $f['match_count'] ?> Matches
                      </span>
                    <?php else: ?>
                      <span class="badge badge-light border text-muted">0 Matches</span>
                    <?php endif; ?>

                    <?php if ($f['game_default_count'] > 0): ?>
                      <div class="mt-1">
                        <span class="badge badge-warning text-dark px-2 font-weight-bold" title="Default format for discipline">
                          <i class="fas fa-star mr-1"></i> Game Default
                        </span>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td class="text-right pr-3">
                    <button type="button" class="btn btn-sm btn-outline-primary mr-1" title="Edit Format"
                            onclick='openEditModal(<?= json_encode($f, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                      <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info mr-1" title="Assign to Matches"
                            onclick="openAssignSingleModal(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>')">
                      <i class="fas fa-link"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete Format"
                            onclick="confirmDelete(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['name'])) ?>', <?= $f['match_count'] ?>)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Quick Discipline Default Assignment Card -->
    <div class="card card-outline card-secondary shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h3 class="card-title font-weight-bold text-dark mb-0">
          <i class="fas fa-cog text-secondary mr-2"></i> Discipline Default Score Format Binding
        </h3>
        <p class="card-subtitle text-muted small mt-1 mb-0">
          When a discipline has a default format, all its matches automatically inherit it unless overridden individually.
        </p>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= BASE_URL ?>/admin/score-formats">
          <input type="hidden" name="action" value="assign_to_game">
          <div class="row align-items-end">
            <div class="col-md-4 form-group mb-md-0">
              <label class="font-weight-bold text-dark">Select Discipline / Game:</label>
              <select name="game_id" class="form-control font-weight-bold" required>
                <option value="">-- Choose Discipline --</option>
                <?php foreach ($games as $g): ?>
                  <option value="<?= $g['id'] ?>">
                    <?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['category']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 form-group mb-md-0">
              <label class="font-weight-bold text-dark">Default Score Format:</label>
              <select name="format_id" class="form-control font-weight-bold">
                <option value="">-- Standard Sport Default (No Custom Override) --</option>
                <?php foreach ($formats as $f): ?>
                  <option value="<?= $f['id'] ?>">
                    <?= htmlspecialchars($f['name']) ?> [<?= $f['badge_text'] ?: $f['code'] ?>]
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" name="apply_to_existing_matches" value="1" class="custom-control-input" id="chkApplyExisting" checked>
                <label class="custom-control-label text-dark small font-weight-bold" for="chkApplyExisting">
                  Apply immediately to all existing matches of this discipline
                </label>
              </div>
              <button type="submit" class="btn btn-secondary font-weight-bold btn-block">
                <i class="fas fa-save mr-1"></i> Update Discipline Default
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

  </div>
</section>

<!-- ============================================================== -->
<!-- CREATE / EDIT FORMAT MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="formatModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0">
      <form method="POST" action="<?= BASE_URL ?>/admin/score-formats" id="formatForm">
        <input type="hidden" name="action" id="modalAction" value="create_format">
        <input type="hidden" name="format_id" id="modalFormatId" value="0">

        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title font-weight-bold" id="formatModalTitle">
            <i class="fas fa-sliders-h text-warning mr-2"></i> Create Custom Score Format
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body p-4 bg-light">
          <!-- Preset Fast Buttons -->
          <div class="mb-3 p-3 bg-white border rounded">
            <label class="small text-muted font-weight-bold text-uppercase d-block mb-2">
              <i class="fas fa-magic text-warning mr-1"></i> Quick Presets (Click to autofill):
            </label>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-xs btn-outline-primary font-weight-bold mr-1" onclick="applyPreset('tt_bo5')">
                Table Tennis Men (Best of 5)
              </button>
              <button type="button" class="btn btn-xs btn-outline-info font-weight-bold mr-1" onclick="applyPreset('tt_bo3')">
                Table Tennis Women (Best of 3)
              </button>
              <button type="button" class="btn btn-xs btn-outline-success font-weight-bold mr-1" onclick="applyPreset('badminton')">
                Badminton (Best of 3 to 21)
              </button>
              <button type="button" class="btn btn-xs btn-outline-warning text-dark font-weight-bold mr-1" onclick="applyPreset('tennis_bo3')">
                Lawn Tennis (3 Sets)
              </button>
              <button type="button" class="btn btn-xs btn-outline-danger font-weight-bold mr-1" onclick="applyPreset('periods_4q')">
                Basketball (4 Quarters)
              </button>
              <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" onclick="applyPreset('halves_2')">
                Football / Kabaddi (2 Halves)
              </button>
            </div>
          </div>

          <div class="row">
            <div class="col-md-7 form-group">
              <label class="font-weight-bold text-dark">Format Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="fmtName" class="form-control font-weight-bold" placeholder="e.g. Table Tennis Men (Best of 5)" required>
            </div>
            <div class="col-md-5 form-group">
              <label class="font-weight-bold text-dark">Unique Code <span class="text-danger">*</span></label>
              <input type="text" name="code" id="fmtCode" class="form-control font-weight-bold" placeholder="e.g. tt_mens_bo5" required>
              <small class="text-muted">Slug identifier (lowercase, underscores)</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 form-group">
              <label class="font-weight-bold text-dark">Linked Discipline (Optional)</label>
              <select name="game_id" id="fmtGameId" class="form-control">
                <option value="">-- Universal (Any Sport) --</option>
                <?php foreach ($games as $g): ?>
                  <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold text-dark">Format Category</label>
              <select name="type" id="fmtType" class="form-control font-weight-bold">
                <option value="sets">Sets / Games (Best of N)</option>
                <option value="periods">Quarters / Periods</option>
                <option value="halves">Halves (1st & 2nd Half)</option>
                <option value="points">Points / Cumulative</option>
                <option value="custom">Custom Columns</option>
              </select>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold text-dark">Scorecard Badge Text</label>
              <input type="text" name="badge_text" id="fmtBadge" class="form-control" placeholder="e.g. Best of 5 Games">
            </div>
          </div>

          <!-- Dynamic Columns Configuration -->
          <div class="card border mb-3">
            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
              <span class="font-weight-bold text-primary">
                <i class="fas fa-columns mr-1"></i> Scorecard Columns Configuration
              </span>
              <div>
                <button type="button" class="btn btn-xs btn-success font-weight-bold" onclick="addColumnRow()">
                  <i class="fas fa-plus mr-1"></i> Add Column
                </button>
              </div>
            </div>
            <div class="card-body p-2 bg-white">
              <div class="table-responsive">
                <table class="table table-sm table-bordered text-center mb-0">
                  <thead class="thead-light">
                    <tr>
                      <th style="width: 40px;">#</th>
                      <th style="width: 100px;">Key</th>
                      <th style="width: 100px;">Short Header</th>
                      <th>Full Column Label</th>
                      <th style="width: 90px;">Max Pts</th>
                      <th style="width: 50px;">Del</th>
                    </tr>
                  </thead>
                  <tbody id="columnsTableBody">
                    <!-- Dynamic column rows will be injected here via JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Rules & Win Conditions -->
          <div class="row">
            <div class="col-md-4 form-group">
              <label class="font-weight-bold text-dark">Win Condition Rule</label>
              <select name="win_rule" id="fmtWinRule" class="form-control">
                <option value="target_wins">Target Games Won (e.g. First to 3)</option>
                <option value="most_sets">Highest Games at End</option>
                <option value="total_score">Total Points Summation</option>
                <option value="manual">Manual Official Decision</option>
              </select>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold text-dark">Target Wins to Seal Match</label>
              <input type="number" name="target_wins" id="fmtTargetWins" class="form-control text-center font-weight-bold" value="2" min="1" max="10">
              <small class="text-muted">e.g. 3 for Best of 5, 2 for Best of 3</small>
            </div>
            <div class="col-md-4 form-group">
              <label class="font-weight-bold text-dark">Points per Game / Set</label>
              <input type="number" name="points_to_win" id="fmtPointsToWin" class="form-control text-center font-weight-bold" value="21" min="1" max="100">
              <small class="text-muted">e.g. 11 for TT, 21 for Badminton</small>
            </div>
          </div>

          <div class="form-group mb-0">
            <label class="font-weight-bold text-dark">Description / Administrative Notes</label>
            <input type="text" name="description" id="fmtDesc" class="form-control" placeholder="Optional notes about when to apply this format...">
          </div>

        </div>

        <div class="modal-footer bg-white">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold px-4" id="saveFormatBtn">
            <i class="fas fa-save mr-1"></i> Save Score Format
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- BULK ASSIGN TO MATCHES MODAL -->
<!-- ============================================================== -->
<div class="modal fade" id="assignMatchesModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content shadow-lg border-0">
      <form method="POST" action="<?= BASE_URL ?>/admin/score-formats">
        <input type="hidden" name="action" value="assign_to_matches">

        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-tasks text-info mr-2"></i> Bulk Assign Score Format to Fixtures
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body p-4 bg-light">
          <div class="row mb-3 bg-white p-3 border rounded">
            <div class="col-md-6 form-group mb-0">
              <label class="font-weight-bold text-dark">1. Select Target Score Format to Assign:</label>
              <select name="format_id" id="assignTargetFormatId" class="form-control font-weight-bold" required>
                <option value="">-- Choose Format --</option>
                <?php foreach ($formats as $f): ?>
                  <option value="<?= $f['id'] ?>">
                    <?= htmlspecialchars($f['name']) ?> (<?= $f['badge_text'] ?: $f['code'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 form-group mb-0">
              <label class="font-weight-bold text-dark">Filter Matches by Discipline:</label>
              <select id="filterMatchDiscipline" class="form-control" onchange="filterAssignMatches()">
                <option value="all">-- All Disciplines (Show All) --</option>
                <?php foreach ($games as $g): ?>
                  <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <span class="font-weight-bold text-dark">2. Select Fixtures to Apply Format:</span>
            <div>
              <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold mr-1" onclick="toggleSelectAllMatches(true)">
                Select All Visible
              </button>
              <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" onclick="toggleSelectAllMatches(false)">
                Deselect All
              </button>
            </div>
          </div>

          <div class="table-responsive bg-white border rounded" style="max-height: 420px; overflow-y: auto;">
            <table class="table table-sm table-hover mb-0 align-middle">
              <thead class="thead-light sticky-top">
                <tr>
                  <th style="width: 40px;" class="text-center">
                    <input type="checkbox" id="chkMasterAssign" onclick="toggleSelectAllMatches(this.checked)">
                  </th>
                  <th style="width: 60px;">ID</th>
                  <th>Discipline</th>
                  <th>Stage / Round</th>
                  <th>Team 1</th>
                  <th>Team 2</th>
                  <th>Current Score Format</th>
                </tr>
              </thead>
              <tbody id="assignMatchesTbody">
                <?php foreach ($matches as $m): ?>
                  <tr class="assign-match-row" data-game-id="<?= $m['game_id'] ?>">
                    <td class="text-center">
                      <input type="checkbox" name="match_ids[]" value="<?= $m['id'] ?>" class="chk-match-item">
                    </td>
                    <td class="font-weight-bold text-muted"><?= $m['id'] ?></td>
                    <td>
                      <span class="badge badge-info"><?= htmlspecialchars($m['game_name']) ?></span>
                    </td>
                    <td class="font-weight-bold text-dark"><?= htmlspecialchars($m['round']) ?></td>
                    <td>
                      <span class="badge badge-light border"><?= htmlspecialchars($m['u1_code'] ?: 'T1') ?></span>
                      <?= htmlspecialchars($m['team1_name'] ?: 'Team A') ?>
                    </td>
                    <td>
                      <span class="badge badge-light border"><?= htmlspecialchars($m['u2_code'] ?: 'T2') ?></span>
                      <?= htmlspecialchars($m['team2_name'] ?: 'Team B') ?>
                    </td>
                    <td>
                      <?php if ($m['format_name']): ?>
                        <span class="badge badge-success px-2 py-1">
                          <i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars($m['format_name']) ?>
                        </span>
                      <?php else: ?>
                        <span class="badge badge-light border text-muted">Inherits Game Default</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>

        <div class="modal-footer bg-white">
          <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-info font-weight-bold px-4">
            <i class="fas fa-check mr-1"></i> Apply Selected Format to Matches
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteFormatForm" method="POST" action="<?= BASE_URL ?>/admin/score-formats" style="display: none;">
  <input type="hidden" name="action" value="delete_format">
  <input type="hidden" name="format_id" id="deleteFormatId" value="0">
</form>

<script>
// Column Configuration state
let currentColumns = [];

function renderColumnsTable() {
  const tbody = document.getElementById('columnsTableBody');
  tbody.innerHTML = '';

  if (currentColumns.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-muted text-center py-2">No columns added yet. Click "+ Add Column".</td></tr>`;
    return;
  }

  currentColumns.forEach((c, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="font-weight-bold text-muted">${idx + 1}</td>
      <td>
        <input type="text" name="col_key[]" class="form-control form-control-sm text-center font-weight-bold" value="${c.key}" placeholder="e.g. g${idx + 1}" required>
      </td>
      <td>
        <input type="text" name="col_short[]" class="form-control form-control-sm text-center font-weight-bold text-primary" value="${c.short}" placeholder="e.g. G${idx + 1}" required>
      </td>
      <td>
        <input type="text" name="col_label[]" class="form-control form-control-sm" value="${c.label}" placeholder="e.g. Game ${idx + 1}" required>
      </td>
      <td>
        <input type="number" name="col_max[]" class="form-control form-control-sm text-center" value="${c.max || 40}" min="1" max="999">
      </td>
      <td>
        <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeColumnRow(${idx})" title="Remove column">
          <i class="fas fa-times"></i>
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });
}

function addColumnRow(key = null, short = null, label = null, max = 40) {
  const count = currentColumns.length + 1;
  currentColumns.push({
    key: key || `g${count}`,
    short: short || `G${count}`,
    label: label || `Game ${count}`,
    max: max || 40
  });
  renderColumnsTable();
}

function removeColumnRow(idx) {
  currentColumns.splice(idx, 1);
  renderColumnsTable();
}

function openCreateModal() {
  document.getElementById('modalAction').value = 'create_format';
  document.getElementById('modalFormatId').value = '0';
  document.getElementById('formatModalTitle').innerHTML = '<i class="fas fa-plus text-warning mr-2"></i> Create Custom Score Format';
  document.getElementById('formatForm').reset();
  
  // Default 3 columns
  currentColumns = [
    { key: 'g1', short: 'G1', label: 'Game 1', max: 40 },
    { key: 'g2', short: 'G2', label: 'Game 2', max: 40 },
    { key: 'g3', short: 'G3', label: 'Game 3', max: 40 }
  ];
  renderColumnsTable();
}

function openEditModal(f) {
  document.getElementById('modalAction').value = 'edit_format';
  document.getElementById('modalFormatId').value = f.id;
  document.getElementById('formatModalTitle').innerHTML = '<i class="fas fa-edit text-warning mr-2"></i> Edit Score Format: ' + f.name;

  document.getElementById('fmtName').value = f.name;
  document.getElementById('fmtCode').value = f.code;
  document.getElementById('fmtGameId').value = f.game_id || '';
  document.getElementById('fmtType').value = f.type || 'sets';
  document.getElementById('fmtBadge').value = f.badge_text || '';
  document.getElementById('fmtWinRule').value = f.win_rule || 'target_wins';
  document.getElementById('fmtTargetWins').value = f.target_wins || 2;
  document.getElementById('fmtPointsToWin').value = f.points_to_win || 21;
  document.getElementById('fmtDesc').value = f.description || '';

  try {
    currentColumns = typeof f.columns_json === 'string' ? JSON.parse(f.columns_json) : (f.columns_json || []);
  } catch(e) {
    currentColumns = [];
  }
  renderColumnsTable();

  $('#formatModal').modal('show');
}

function openAssignSingleModal(formatId, formatName) {
  document.getElementById('assignTargetFormatId').value = formatId;
  $('#assignMatchesModal').modal('show');
}

function confirmDelete(id, name, matchCount) {
  let warning = `Are you sure you want to delete score format "${name}"?`;
  if (matchCount > 0) {
    warning += `\n\nWarning: It is currently assigned to ${matchCount} match(es). Matches will revert to discipline defaults.`;
  }
  if (confirm(warning)) {
    document.getElementById('deleteFormatId').value = id;
    document.getElementById('deleteFormatForm').submit();
  }
}

// Preset Handlers
function applyPreset(preset) {
  if (preset === 'tt_bo5') {
    document.getElementById('fmtName').value = 'Table Tennis Men (Best of 5)';
    document.getElementById('fmtCode').value = 'tt_mens_bo5';
    document.getElementById('fmtType').value = 'sets';
    document.getElementById('fmtBadge').value = 'Best of 5';
    document.getElementById('fmtWinRule').value = 'target_wins';
    document.getElementById('fmtTargetWins').value = 3;
    document.getElementById('fmtPointsToWin').value = 11;
    currentColumns = [
      { key: 'g1', short: 'G1', label: 'Game 1', max: 40 },
      { key: 'g2', short: 'G2', label: 'Game 2', max: 40 },
      { key: 'g3', short: 'G3', label: 'Game 3', max: 40 },
      { key: 'g4', short: 'G4', label: 'Game 4', max: 40 },
      { key: 'g5', short: 'G5', label: 'Game 5', max: 40 }
    ];
  } else if (preset === 'tt_bo3') {
    document.getElementById('fmtName').value = 'Table Tennis Women (Best of 3)';
    document.getElementById('fmtCode').value = 'tt_womens_bo3';
    document.getElementById('fmtType').value = 'sets';
    document.getElementById('fmtBadge').value = 'Best of 3';
    document.getElementById('fmtWinRule').value = 'target_wins';
    document.getElementById('fmtTargetWins').value = 2;
    document.getElementById('fmtPointsToWin').value = 11;
    currentColumns = [
      { key: 'g1', short: 'G1', label: 'Game 1', max: 40 },
      { key: 'g2', short: 'G2', label: 'Game 2', max: 40 },
      { key: 'g3', short: 'G3', label: 'Game 3', max: 40 }
    ];
  } else if (preset === 'badminton') {
    document.getElementById('fmtName').value = 'Badminton (Best of 3 to 21 Pts)';
    document.getElementById('fmtCode').value = 'badminton_bo3';
    document.getElementById('fmtType').value = 'sets';
    document.getElementById('fmtBadge').value = 'Best of 3';
    document.getElementById('fmtWinRule').value = 'target_wins';
    document.getElementById('fmtTargetWins').value = 2;
    document.getElementById('fmtPointsToWin').value = 21;
    currentColumns = [
      { key: 'g1', short: 'G1', label: 'Game 1', max: 40 },
      { key: 'g2', short: 'G2', label: 'Game 2', max: 40 },
      { key: 'g3', short: 'G3', label: 'Game 3', max: 40 }
    ];
  } else if (preset === 'tennis_bo3') {
    document.getElementById('fmtName').value = 'Lawn Tennis (Best of 3 Sets)';
    document.getElementById('fmtCode').value = 'tennis_bo3';
    document.getElementById('fmtType').value = 'sets';
    document.getElementById('fmtBadge').value = 'Best of 3 Sets';
    document.getElementById('fmtWinRule').value = 'target_wins';
    document.getElementById('fmtTargetWins').value = 2;
    document.getElementById('fmtPointsToWin').value = 6;
    currentColumns = [
      { key: 's1', short: 'S1', label: 'Set 1', max: 7 },
      { key: 's2', short: 'S2', label: 'Set 2', max: 7 },
      { key: 's3', short: 'S3', label: 'Set 3', max: 7 }
    ];
  } else if (preset === 'periods_4q') {
    document.getElementById('fmtName').value = 'Basketball (4 Quarters)';
    document.getElementById('fmtCode').value = 'bball_4q';
    document.getElementById('fmtType').value = 'periods';
    document.getElementById('fmtBadge').value = '4 Quarters';
    document.getElementById('fmtWinRule').value = 'total_score';
    document.getElementById('fmtTargetWins').value = 1;
    document.getElementById('fmtPointsToWin').value = 100;
    currentColumns = [
      { key: 'q1', short: 'Q1', label: 'Quarter 1', max: 99 },
      { key: 'q2', short: 'Q2', label: 'Quarter 2', max: 99 },
      { key: 'q3', short: 'Q3', label: 'Quarter 3', max: 99 },
      { key: 'q4', short: 'Q4', label: 'Quarter 4', max: 99 }
    ];
  } else if (preset === 'halves_2') {
    document.getElementById('fmtName').value = 'Standard Match (2 Halves)';
    document.getElementById('fmtCode').value = 'halves_2h';
    document.getElementById('fmtType').value = 'halves';
    document.getElementById('fmtBadge').value = '2 Halves';
    document.getElementById('fmtWinRule').value = 'total_score';
    document.getElementById('fmtTargetWins').value = 1;
    document.getElementById('fmtPointsToWin').value = 99;
    currentColumns = [
      { key: 'h1', short: '1H', label: '1st Half', max: 99 },
      { key: 'h2', short: '2H', label: '2nd Half', max: 99 }
    ];
  }
  renderColumnsTable();
}

// Bulk Assign Filtering
function filterAssignMatches() {
  const selGame = document.getElementById('filterMatchDiscipline').value;
  const rows = document.querySelectorAll('.assign-match-row');
  rows.forEach(r => {
    if (selGame === 'all' || r.getAttribute('data-game-id') === selGame) {
      r.style.display = '';
    } else {
      r.style.display = 'none';
    }
  });
}

function toggleSelectAllMatches(chk) {
  const items = document.querySelectorAll('.assign-match-row:not([style*="display: none"]) .chk-match-item');
  items.forEach(i => i.checked = chk);
}
</script>
