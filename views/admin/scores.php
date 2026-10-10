<?php
require_once 'config/helpers.php';
require_once 'config/scorecards.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

// Handle Admin Score Edit (FR-23, FR-28)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_score') {
        $match_id = (int)($_POST['match_id'] ?? 0);
        $winner_id = (int)($_POST['winner_id'] ?? 0) ?: null;
        $status = $_POST['status'] ?? 'completed';
        $edit_reason = trim($_POST['edit_reason'] ?? '');
        $sport_type = $_POST['sport_type'] ?? 'generic';

        if ($match_id > 0 && $edit_reason) {
            // Get original score for audit diff
            $stmt = $pdo->prepare("SELECT scores_json, winner_id, status FROM matches WHERE id = ?");
            $stmt->execute([$match_id]);
            $oldMatch = $stmt->fetch();

            $scoresData = json_decode($oldMatch['scores_json'] ?? '{}', true) ?: [];
            $scoresData['type'] = $sport_type;
            $scoresData['status'] = $status;
            $scoresData['edited_by'] = $_SESSION['username'] ?? 'Admin';
            $scoresData['edit_reason'] = $edit_reason;
            $scoresData['edited_at'] = date('Y-m-d H:i:s');

            // Build structured payload based on discipline
            if ($sport_type === 'badminton_table_tennis') {
                $scoresData['g1_a'] = $_POST['btt_g1_a'] ?? '';
                $scoresData['g1_b'] = $_POST['btt_g1_b'] ?? '';
                $scoresData['g2_a'] = $_POST['btt_g2_a'] ?? '';
                $scoresData['g2_b'] = $_POST['btt_g2_b'] ?? '';
                $scoresData['g3_a'] = $_POST['btt_g3_a'] ?? '';
                $scoresData['g3_b'] = $_POST['btt_g3_b'] ?? '';
                $scoresData['g4_a'] = $_POST['btt_g4_a'] ?? '';
                $scoresData['g4_b'] = $_POST['btt_g4_b'] ?? '';
                $scoresData['g5_a'] = $_POST['btt_g5_a'] ?? '';
                $scoresData['g5_b'] = $_POST['btt_g5_b'] ?? '';
                $scoresData['is_best_of_5'] = !empty($_POST['btt_is_best_of_5']) ? 1 : 0;
                $scoresData['server'] = $_POST['btt_server'] ?? 'team1';
                $scoresData['is_walkover'] = !empty($_POST['btt_walkover']) ? 1 : 0;
            } elseif ($sport_type === 'tennis') {
                $scoresData['s1_a'] = $_POST['ten_s1_a'] ?? '';
                $scoresData['s1_b'] = $_POST['ten_s1_b'] ?? '';
                $scoresData['s2_a'] = $_POST['ten_s2_a'] ?? '';
                $scoresData['s2_b'] = $_POST['ten_s2_b'] ?? '';
                $scoresData['s3_a'] = $_POST['ten_s3_a'] ?? '';
                $scoresData['s3_b'] = $_POST['ten_s3_b'] ?? '';
                $scoresData['pts_a'] = $_POST['ten_pts_a'] ?? '0';
                $scoresData['pts_b'] = $_POST['ten_pts_b'] ?? '0';
                $scoresData['server'] = $_POST['ten_server'] ?? 'team1';
            } elseif ($sport_type === 'chess') {
                $scoresData['board_no'] = $_POST['chess_board_no'] ?? '1';
                $scoresData['white_player'] = $_POST['chess_white_player'] ?? '';
                $scoresData['white_zone'] = $_POST['chess_white_zone'] ?? '';
                $scoresData['black_player'] = $_POST['chess_black_player'] ?? '';
                $scoresData['black_zone'] = $_POST['chess_black_zone'] ?? '';
                $scoresData['result'] = $_POST['chess_result'] ?? '';
                $scoresData['live_note'] = $_POST['chess_live_note'] ?? '';
                if ($scoresData['result'] === '1-0') {
                    $scoresData['points_a'] = 1.0; $scoresData['points_b'] = 0.0;
                } elseif ($scoresData['result'] === '0-1') {
                    $scoresData['points_a'] = 0.0; $scoresData['points_b'] = 1.0;
                } elseif ($scoresData['result'] === '1/2-1/2') {
                    $scoresData['points_a'] = 0.5; $scoresData['points_b'] = 0.5;
                }
            } elseif ($sport_type === 'carrom') {
                $scoresData['board_no'] = $_POST['carrom_board_no'] ?? '1';
                $scoresData['points_a'] = (int)($_POST['carrom_pts_a'] ?? 0);
                $scoresData['points_b'] = (int)($_POST['carrom_pts_b'] ?? 0);
            } elseif ($sport_type === 'bridge') {
                $scoresData['session_no'] = $_POST['bridge_session'] ?? '1';
                $scoresData['table_no'] = $_POST['bridge_table'] ?? '1';
                $scoresData['imps_a'] = (int)($_POST['bridge_imp_a'] ?? 0);
                $scoresData['imps_b'] = (int)($_POST['bridge_imp_b'] ?? 0);
                $scoresData['vps_a'] = (float)($_POST['bridge_vp_a'] ?? 0);
                $scoresData['vps_b'] = (float)($_POST['bridge_vp_b'] ?? 0);
            } elseif ($sport_type === 'swimming') {
                $scoresData['event_name'] = $_POST['swim_event_name'] ?? '50m Freestyle';
                $scoresData['heat_no'] = $_POST['swim_heat_no'] ?? 'Heat 1 / Final';
                $lanes = [];
                for ($ln = 1; $ln <= 8; $ln++) {
                    $swimmer = trim($_POST["swim_name_$ln"] ?? '');
                    $zone = trim($_POST["swim_zone_$ln"] ?? '');
                    $time = trim($_POST["swim_time_$ln"] ?? '');
                    $pos = trim($_POST["swim_pos_$ln"] ?? '');
                    $statusLn = trim($_POST["swim_status_$ln"] ?? 'NORMAL');
                    if ($swimmer || $time) {
                        $lanes[] = [
                            'lane' => $ln,
                            'swimmer' => $swimmer,
                            'zone' => $zone,
                            'time' => $time,
                            'position' => $pos,
                            'status' => $statusLn
                        ];
                    }
                }
                $scoresData['lanes'] = $lanes;
            }

            // Generate clean human-readable summary
            $customSummary = trim($_POST['score_summary'] ?? '');
            if ($customSummary) {
                $scoresData['summary'] = $customSummary;
            } else {
                $scoresData['summary'] = format_score_summary($scoresData, $sport_type);
            }

            // Update match record with custom score format
            $score_format_id = !empty($_POST['score_format_id']) ? (int)$_POST['score_format_id'] : null;
            $stmt = $pdo->prepare("UPDATE matches SET winner_id = ?, status = ?, scores_json = ?, score_format_id = ? WHERE id = ?");
            $stmt->execute([$winner_id, $status, json_encode($scoresData), $score_format_id, $match_id]);

            // Mandatory Audit Log (FR-23, FR-28)
            $oldJson = $oldMatch['scores_json'] ?? '{}';
            $newJson = json_encode($scoresData);
            $auditDetail = "ADMIN_SCORE_CORRECTION on Match #$match_id ($sport_type). Reason: '$edit_reason'. Winner: $winner_id. Status: $status. Format: $score_format_id. Summary: '{$scoresData['summary']}'";
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'SCORE_EDIT', $auditDetail);

            $_SESSION['flash_msg'] = "Score for Match #$match_id successfully updated and audit-logged! Downstream standings re-computed.";
            header("Location: " . BASE_URL . "/admin/scores");
            exit;
        } else {
            $err = "Match ID and Reason for Edit are mandatory for audit compliance.";
        }
    }
}

// Fetch all score formats for modal dropdown
$allScoreFormats = $pdo->query("SELECT * FROM score_formats ORDER BY name ASC")->fetchAll();

// Fetch all matches with details and custom formats
$matches = $pdo->query("
    SELECT m.*, 
           g.name as game_name, g.slug as game_slug, g.category,
           t1.id as t1_id, COALESCE(NULLIF(m.athlete1_name, ''), t1.name) as team1_name, u1.short_code as u1_code, u1.color_code as u1_color,
           t2.id as t2_id, COALESCE(NULLIF(m.athlete2_name, ''), t2.name) as team2_name, u2.short_code as u2_code, u2.color_code as u2_color,
           COALESCE(
               CASE 
                   WHEN m.winner_id = m.team1_id AND m.athlete1_name IS NOT NULL AND m.athlete1_name != '' THEN m.athlete1_name
                   WHEN m.winner_id = m.team2_id AND m.athlete2_name IS NOT NULL AND m.athlete2_name != '' THEN m.athlete2_name
                   ELSE tw.name
               END
           ) as winner_name, tw.id as win_id,
           f.name as facility_name,
           sf.name as format_name, sf.badge_text as format_badge, sf.columns_json, sf.total_columns
    FROM matches m
    JOIN games g ON m.game_id = g.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    LEFT JOIN teams tw ON m.winner_id = tw.id
    LEFT JOIN facilities f ON m.facility_id = f.id
    LEFT JOIN score_formats sf ON COALESCE(m.score_format_id, g.score_format_id) = sf.id
    ORDER BY m.id DESC
")->fetchAll();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-edit text-warning mr-2"></i> Live Scores & Scorecard Audit</h1>
        <p class="text-muted mb-0">Sport-Specific Official Scorecard Corrections & Immediate Standings Sync</p>
      </div>
      <div class="col-sm-6 text-right">
        <a href="<?= BASE_URL ?>/admin/audit" class="btn btn-outline-secondary font-weight-bold">
          <i class="fas fa-history mr-1"></i> View Audit Trail
        </a>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <?php if ($msg): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($err) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <div class="card card-outline card-warning elevation-2">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">
          <i class="fas fa-table mr-2"></i> All Tournament Matches & Digital Scorecards
        </h3>
        <span class="badge badge-warning font-weight-bold px-2 py-1"><?= count($matches) ?> Matches Total</span>
      </div>
      <div class="card-body p-0 table-responsive" style="max-height: 750px;">
        <table class="table table-hover table-striped mb-0 text-nowrap">
          <thead class="thead-light">
            <tr>
              <th>Match</th>
              <th>Discipline & Round</th>
              <th>Teams / Units</th>
              <th>Status</th>
              <th>Digital Scorecard (Unified Format)</th>
              <th>Winner</th>
              <th class="text-right">Admin Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($matches as $m): ?>
              <?php 
                $scores = json_decode($m['scores_json'] ?? '{}', true);
                $summaryText = $scores['summary'] ?? ($m['status'] === 'scheduled' ? 'Awaiting start' : 'Scores pending');
                $sportType = get_scorecard_type($m['game_slug']);
              ?>
              <tr>
                <td class="align-middle"><strong>#<?= $m['id'] ?></strong></td>
                <td class="align-middle">
                  <strong><?= htmlspecialchars($m['game_name']) ?></strong><br>
                  <small class="text-muted"><?= htmlspecialchars($m['round']) ?></small>
                </td>
                <td class="align-middle">
                  <?php if ($sportType === 'swimming'): 
                      $partText = $scores['participants'] ?? 'Multi-lane Heat';
                  ?>
                    <div class="d-flex align-items-center">
                      <span class="badge badge-info mr-1"><i class="fas fa-swimmer mr-1"></i> <?= htmlspecialchars($partText) ?></span>
                      <span class="badge badge-secondary"><?= htmlspecialchars($m['pool_name'] ?: 'Heats') ?></span>
                    </div>
                  <?php else: ?>
                    <div class="d-flex align-items-center">
                      <span class="badge mr-1" style="background-color: <?= $m['u1_color'] ?>; color: #fff; min-width: 30px;"><?= htmlspecialchars($m['u1_code']) ?></span>
                      <span class="font-weight-bold"><?= htmlspecialchars($m['team1_name'] ?: 'TBD') ?></span>
                      <strong class="mx-2 text-muted">vs</strong>
                      <span class="badge mr-1" style="background-color: <?= $m['u2_color'] ?>; color: #fff; min-width: 30px;"><?= htmlspecialchars($m['u2_code']) ?></span>
                      <span class="font-weight-bold"><?= htmlspecialchars($m['team2_name'] ?: 'TBD') ?></span>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="align-middle">
                  <?php if ($m['status'] === 'completed'): ?>
                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Final</span>
                  <?php elseif ($m['status'] === 'in_progress'): ?>
                    <span class="badge badge-danger"><i class="fas fa-broadcast-tower mr-1"></i> Live</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Scheduled</span>
                  <?php endif; ?>
                </td>
                <td class="align-middle">
                  <div style="min-width: 280px; max-width: 400px;">
                    <?= render_digital_scorecard($m, $scores) ?>
                  </div>
                </td>
                <td class="align-middle">
                  <?php if ($m['winner_name']): ?>
                    <strong class="text-success"><i class="fas fa-trophy mr-1 text-warning"></i> <?= htmlspecialchars($m['winner_name']) ?></strong>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td class="align-middle text-right">
                  <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold shadow-sm" 
                          onclick='openEditScoreModal(<?= json_encode([
                              "id" => $m["id"],
                              "game_name" => $m["game_name"],
                              "game_slug" => $m["game_slug"],
                              "sport_type" => $sportType,
                              "round" => $m["round"],
                              "t1_id" => $m["t1_id"],
                              "t1_name" => $m["team1_name"],
                              "u1_code" => $m["u1_code"],
                              "t2_id" => $m["t2_id"],
                              "t2_name" => $m["team2_name"],
                              "u2_code" => $m["u2_code"],
                              "win_id" => $m["win_id"],
                              "status" => $m["status"],
                              "score_format_id" => $m["score_format_id"] ?? 0,
                              "format_name" => $m["format_name"] ?? "",
                              "format_badge" => $m["format_badge"] ?? "",
                              "total_columns" => $m["total_columns"] ?? null,
                              "scores" => $scores
                          ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                    <i class="fas fa-edit mr-1"></i> Edit Score
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: SPORT-SPECIFIC ADMIN SCORE EDIT & AUDIT OVERRIDE  -->
<!-- ======================================================== -->
<div class="modal fade" id="editScoreModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="edit_score">
        <input type="hidden" name="match_id" id="edit_match_id">
        <input type="hidden" name="sport_type" id="edit_sport_type">

        <div class="modal-header bg-warning">
          <h5 class="modal-title font-weight-bold text-dark">
            <i class="fas fa-edit mr-2"></i> Official Scorecard Correction (<span id="edit_match_title"></span>)
          </h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        
        <div class="modal-body">
          <div class="alert alert-light border small mb-3">
            <i class="fas fa-shield-alt text-primary mr-1"></i> <strong>Official Verification:</strong> Changes made here immediately synchronize across all spectator dashboards, volunteer mobile terminals, and downstream pool standings.
          </div>

          <div class="row mb-3 bg-light p-2 rounded border mx-0">
            <div class="col-md-4">
              <label class="small text-muted font-weight-bold text-uppercase mb-1">Match Status</label>
              <select name="status" id="edit_status" class="form-control font-weight-bold">
                <option value="completed">Completed / Final Result</option>
                <option value="in_progress">In Progress / Live Match</option>
                <option value="scheduled">Scheduled / Reset Match</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="small text-muted font-weight-bold text-uppercase mb-1">Official Winner</label>
              <select name="winner_id" id="edit_winner" class="form-control font-weight-bold">
                <option value="0">-- None / Draw / In Progress --</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="small text-muted font-weight-bold text-uppercase mb-1">
                <i class="fas fa-sliders-h text-warning mr-1"></i> Custom Format
              </label>
              <select name="score_format_id" id="edit_score_format_id" class="form-control font-weight-bold" onchange="onScoreFormatChanged(this)">
                <option value="0">-- Inherit Default --</option>
                <?php foreach ($allScoreFormats as $sf): ?>
                  <option value="<?= $sf['id'] ?>" data-cols="<?= $sf['total_columns'] ?>" data-badge="<?= htmlspecialchars($sf['badge_text'] ?: '') ?>">
                    <?= htmlspecialchars($sf['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- 1. BADMINTON & TABLE TENNIS SUB-FORM -->
          <div id="subFormBTT" class="sport-subform d-none border p-3 rounded mb-3 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="font-weight-bold text-primary mb-0" id="btt_subform_title">
                <i class="fas fa-table-tennis mr-1"></i> Racket Game Scores
              </h6>
              <span class="badge badge-primary px-2 py-1 font-weight-bold" id="btt_format_badge">Best of 5</span>
              <input type="hidden" name="btt_is_best_of_5" id="btt_is_best_of_5" value="0">
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-bordered text-center mb-2">
                <thead class="thead-light">
                  <tr>
                    <th class="text-left">Team / Player</th>
                    <th style="width: 75px;">Game 1</th>
                    <th style="width: 75px;">Game 2</th>
                    <th style="width: 75px;">Game 3</th>
                    <th style="width: 75px;" class="btt-extra-game">Game 4</th>
                    <th style="width: 75px;" class="btt-extra-game">Game 5</th>
                    <th style="width: 80px;">Serving</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-left font-weight-bold" id="btt_t1_lbl">Team 1</td>
                    <td><input type="number" name="btt_g1_a" id="btt_g1_a" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td><input type="number" name="btt_g2_a" id="btt_g2_a" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td><input type="number" name="btt_g3_a" id="btt_g3_a" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td class="btt-extra-game"><input type="number" name="btt_g4_a" id="btt_g4_a" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td class="btt-extra-game"><input type="number" name="btt_g5_a" id="btt_g5_a" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td><input type="radio" name="btt_server" id="btt_srv_a" value="team1" checked></td>
                  </tr>
                  <tr>
                    <td class="text-left font-weight-bold" id="btt_t2_lbl">Team 2</td>
                    <td><input type="number" name="btt_g1_b" id="btt_g1_b" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td><input type="number" name="btt_g2_b" id="btt_g2_b" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td><input type="number" name="btt_g3_b" id="btt_g3_b" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td class="btt-extra-game"><input type="number" name="btt_g4_b" id="btt_g4_b" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td class="btt-extra-game"><input type="number" name="btt_g5_b" id="btt_g5_b" class="form-control form-control-sm text-center" min="0" max="40"></td>
                    <td><input type="radio" name="btt_server" id="btt_srv_b" value="team2"></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="custom-control custom-checkbox mt-2">
              <input type="checkbox" name="btt_walkover" class="custom-control-input" id="btt_walkover" value="1">
              <label class="custom-control-label text-danger font-weight-bold" for="btt_walkover">Declare Walkover (W/O)</label>
            </div>
          </div>

          <!-- 2. TENNIS SUB-FORM -->
          <div id="subFormTennis" class="sport-subform d-none border p-3 rounded mb-3 bg-white">
            <h6 class="font-weight-bold text-success mb-3"><i class="fas fa-baseball-ball mr-1"></i> Lawn Tennis Sets & Points</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered text-center mb-2">
                <thead class="thead-light">
                  <tr>
                    <th class="text-left">Team / Player</th>
                    <th style="width: 80px;">Set 1</th>
                    <th style="width: 80px;">Set 2</th>
                    <th style="width: 80px;">Set 3</th>
                    <th style="width: 85px;">Points</th>
                    <th style="width: 85px;">Serving</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-left font-weight-bold" id="ten_t1_lbl">Team 1</td>
                    <td><input type="number" name="ten_s1_a" id="ten_s1_a" class="form-control form-control-sm text-center" min="0" max="7"></td>
                    <td><input type="number" name="ten_s2_a" id="ten_s2_a" class="form-control form-control-sm text-center" min="0" max="7"></td>
                    <td><input type="number" name="ten_s3_a" id="ten_s3_a" class="form-control form-control-sm text-center" min="0" max="7"></td>
                    <td>
                      <select name="ten_pts_a" id="ten_pts_a" class="form-control form-control-sm">
                        <option value="0">0</option><option value="15">15</option><option value="30">30</option><option value="40">40</option><option value="Ad">Ad</option>
                      </select>
                    </td>
                    <td><input type="radio" name="ten_server" id="ten_srv_a" value="team1" checked></td>
                  </tr>
                  <tr>
                    <td class="text-left font-weight-bold" id="ten_t2_lbl">Team 2</td>
                    <td><input type="number" name="ten_s1_b" id="ten_s1_b" class="form-control form-control-sm text-center" min="0" max="7"></td>
                    <td><input type="number" name="ten_s2_b" id="ten_s2_b" class="form-control form-control-sm text-center" min="0" max="7"></td>
                    <td><input type="number" name="ten_s3_b" id="ten_s3_b" class="form-control form-control-sm text-center" min="0" max="7"></td>
                    <td>
                      <select name="ten_pts_b" id="ten_pts_b" class="form-control form-control-sm">
                        <option value="0">0</option><option value="15">15</option><option value="30">30</option><option value="40">40</option><option value="Ad">Ad</option>
                      </select>
                    </td>
                    <td><input type="radio" name="ten_server" id="ten_srv_b" value="team2"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- 3. CHESS SUB-FORM -->
          <div id="subFormChess" class="sport-subform d-none border p-3 rounded mb-3 bg-white">
            <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-chess mr-1"></i> Chess Board Result</h6>
            <div class="row">
              <div class="col-md-2 form-group">
                <label>Board No</label>
                <input type="text" name="chess_board_no" id="chess_board_no" class="form-control" value="1">
              </div>
              <div class="col-md-4 form-group">
                <label>White Player</label>
                <input type="text" name="chess_white_player" id="chess_white_player" class="form-control">
                <input type="hidden" name="chess_white_zone" id="chess_white_zone">
              </div>
              <div class="col-md-3 form-group">
                <label>Result</label>
                <select name="chess_result" id="chess_result" class="form-control font-weight-bold">
                  <option value="1-0">1 - 0 (White Wins)</option>
                  <option value="0-1">0 - 1 (Black Wins)</option>
                  <option value="1/2-1/2">½ - ½ (Draw)</option>
                  <option value="in_progress">In Progress (Live)</option>
                </select>
              </div>
              <div class="col-md-3 form-group">
                <label>Black Player</label>
                <input type="text" name="chess_black_player" id="chess_black_player" class="form-control">
                <input type="hidden" name="chess_black_zone" id="chess_black_zone">
              </div>
            </div>
            <div class="form-group mb-0">
              <label>Live Move / Position Note</label>
              <input type="text" name="chess_live_note" id="chess_live_note" class="form-control" placeholder="e.g. Move 28 - Endgame Rook & Pawn">
            </div>
          </div>

          <!-- 4. CARROM SUB-FORM -->
          <div id="subFormCarrom" class="sport-subform d-none border p-3 rounded mb-3 bg-white">
            <h6 class="font-weight-bold text-danger mb-3"><i class="fas fa-bullseye mr-1"></i> Carrom Cumulative Board Points</h6>
            <div class="row">
              <div class="col-md-2 form-group">
                <label>Board No</label>
                <input type="text" name="carrom_board_no" id="carrom_board_no" class="form-control" value="1">
              </div>
              <div class="col-md-5 form-group">
                <label id="carrom_t1_lbl">Team 1 Points</label>
                <input type="number" name="carrom_pts_a" id="carrom_pts_a" class="form-control text-center font-weight-bold" min="0" max="29">
              </div>
              <div class="col-md-5 form-group">
                <label id="carrom_t2_lbl">Team 2 Points</label>
                <input type="number" name="carrom_pts_b" id="carrom_pts_b" class="form-control text-center font-weight-bold" min="0" max="29">
              </div>
            </div>
          </div>

          <!-- 5. BRIDGE SUB-FORM -->
          <div id="subFormBridge" class="sport-subform d-none border p-3 rounded mb-3 bg-white">
            <h6 class="font-weight-bold text-info mb-3"><i class="fas fa-clone mr-1"></i> Bridge IMPs & Victory Points (WBF Scale)</h6>
            <div class="row">
              <div class="col-md-2 form-group">
                <label>Session</label>
                <input type="text" name="bridge_session" id="bridge_session" class="form-control" value="1">
              </div>
              <div class="col-md-2 form-group">
                <label>Table</label>
                <input type="text" name="bridge_table" id="bridge_table" class="form-control" value="1">
              </div>
              <div class="col-md-4 form-group">
                <label id="bridge_t1_lbl">Team 1 (IMPs / VPs)</label>
                <div class="input-group">
                  <input type="number" name="bridge_imp_a" id="bridge_imp_a" class="form-control" placeholder="IMPs">
                  <input type="number" step="0.01" name="bridge_vp_a" id="bridge_vp_a" class="form-control" placeholder="VPs">
                </div>
              </div>
              <div class="col-md-4 form-group">
                <label id="bridge_t2_lbl">Team 2 (IMPs / VPs)</label>
                <div class="input-group">
                  <input type="number" name="bridge_imp_b" id="bridge_imp_b" class="form-control" placeholder="IMPs">
                  <input type="number" step="0.01" name="bridge_vp_b" id="bridge_vp_b" class="form-control" placeholder="VPs">
                </div>
              </div>
            </div>
          </div>

          <!-- 6. SWIMMING SUB-FORM -->
          <div id="subFormSwimming" class="sport-subform d-none border p-3 rounded mb-3 bg-white">
            <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-swimmer mr-1"></i> Swimming 8-Lane Timesheet</h6>
            <div class="row mb-2">
              <div class="col-md-7 form-group">
                <label>Event Name</label>
                <input type="text" name="swim_event_name" id="swim_event_name" class="form-control" value="50m Freestyle">
              </div>
              <div class="col-md-5 form-group">
                <label>Heat / Stage</label>
                <input type="text" name="swim_heat_no" id="swim_heat_no" class="form-control" value="Heat 1 / Final">
              </div>
            </div>
            <div class="table-responsive" style="max-height: 250px;">
              <table class="table table-sm table-bordered text-center mb-0">
                <thead class="thead-light">
                  <tr>
                    <th style="width: 40px;">Ln</th>
                    <th>Swimmer Name</th>
                    <th style="width: 70px;">Zone</th>
                    <th style="width: 100px;">Time (MM:SS.ms)</th>
                    <th style="width: 90px;">Rank</th>
                    <th style="width: 90px;">Status</th>
                  </tr>
                </thead>
                <tbody id="swim_lanes_tbody">
                  <?php for ($ln = 1; $ln <= 8; $ln++): ?>
                    <tr>
                      <td class="font-weight-bold text-muted"><?= $ln ?></td>
                      <td><input type="text" name="swim_name_<?= $ln ?>" id="swim_name_<?= $ln ?>" class="form-control form-control-sm text-left" placeholder="Swimmer"></td>
                      <td><input type="text" name="swim_zone_<?= $ln ?>" id="swim_zone_<?= $ln ?>" class="form-control form-control-sm" placeholder="Zone"></td>
                      <td><input type="text" name="swim_time_<?= $ln ?>" id="swim_time_<?= $ln ?>" class="form-control form-control-sm" placeholder="00:25.50"></td>
                      <td><input type="text" name="swim_pos_<?= $ln ?>" id="swim_pos_<?= $ln ?>" class="form-control form-control-sm" placeholder="1st"></td>
                      <td>
                        <select name="swim_status_<?= $ln ?>" id="swim_status_<?= $ln ?>" class="form-control form-control-sm">
                          <option value="NORMAL">Normal</option>
                          <option value="DNS">DNS</option>
                          <option value="DSQ">DSQ</option>
                        </select>
                      </td>
                    </tr>
                  <?php endfor; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- SUMMARY OVERRIDE & REASON -->
          <div class="form-group">
            <label>Official Scorecard Summary Text</label>
            <input type="text" name="score_summary" id="edit_summary" class="form-control font-weight-bold" placeholder="Auto-generated if left blank">
          </div>

          <div class="form-group mb-0">
            <label class="text-danger font-weight-bold">
              <i class="fas fa-exclamation-circle mr-1"></i> Mandatory Reason for Administrative Correction (Audit Trail FR-28) *
            </label>
            <textarea name="edit_reason" class="form-control border-danger" rows="2" placeholder="e.g. Corrected referee timesheet for Lane 2 / Inverted score entered by volunteer." required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning font-weight-bold">
            <i class="fas fa-save mr-1"></i> Save Correction & Synchronize
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditScoreModal(data) {
  document.getElementById('edit_match_id').value = data.id;
  document.getElementById('edit_match_title').innerText = '#' + data.id + ' ' + data.game_name;
  document.getElementById('edit_sport_type').value = data.sport_type;
  document.getElementById('edit_status').value = data.status || 'completed';
  if (document.getElementById('edit_score_format_id')) {
    document.getElementById('edit_score_format_id').value = data.score_format_id || 0;
  }

  var scores = data.scores || {};
  document.getElementById('edit_summary').value = scores.summary || '';

  // Setup Winner Select
  var winSelect = document.getElementById('edit_winner');
  winSelect.innerHTML = '<option value="0">-- None / Draw / In Progress --</option>';
  if (data.t1_id) {
    var opt1 = document.createElement('option');
    opt1.value = data.t1_id;
    opt1.text = '[' + (data.u1_code || 'T1') + '] ' + data.t1_name;
    if (data.win_id == data.t1_id) opt1.selected = true;
    winSelect.appendChild(opt1);
  }
  if (data.t2_id) {
    var opt2 = document.createElement('option');
    opt2.value = data.t2_id;
    opt2.text = '[' + (data.u2_code || 'T2') + '] ' + data.t2_name;
    if (data.win_id == data.t2_id) opt2.selected = true;
    winSelect.appendChild(opt2);
  }

  // Hide all subforms
  document.querySelectorAll('.sport-subform').forEach(function(el) {
    el.classList.add('d-none');
  });

  var st = data.sport_type;

  if (st === 'badminton_table_tennis') {
    document.getElementById('subFormBTT').classList.remove('d-none');
    document.getElementById('btt_t1_lbl').innerText = '[' + (data.u1_code || 'T1') + '] ' + data.t1_name;
    document.getElementById('btt_t2_lbl').innerText = '[' + (data.u2_code || 'T2') + '] ' + data.t2_name;
    document.getElementById('btt_g1_a').value = scores.g1_a ?? '';
    document.getElementById('btt_g1_b').value = scores.g1_b ?? '';
    document.getElementById('btt_g2_a').value = scores.g2_a ?? '';
    document.getElementById('btt_g2_b').value = scores.g2_b ?? '';
    document.getElementById('btt_g3_a').value = scores.g3_a ?? '';
    document.getElementById('btt_g3_b').value = scores.g3_b ?? '';
    document.getElementById('btt_g4_a').value = scores.g4_a ?? '';
    document.getElementById('btt_g4_b').value = scores.g4_b ?? '';
    document.getElementById('btt_g5_a').value = scores.g5_a ?? '';
    document.getElementById('btt_g5_b').value = scores.g5_b ?? '';

    var isTT = (data.game_slug && data.game_slug.indexOf('table-tennis') !== -1) || (data.game_name && data.game_name.toLowerCase().indexOf('table tennis') !== -1);
    var isOpenCategory = (data.category === 'Open Category' || (data.game_slug && data.game_slug.indexOf('open') !== -1) || (data.game_name && data.game_name.toLowerCase().indexOf('open') !== -1));
    var isWomen = (data.round && data.round.toLowerCase().indexOf('women') !== -1);
    var isBestOf5 = isOpenCategory || (data.total_columns == 5) || (data.score_format_id == 1) || ((isTT && !isWomen) && data.score_format_id != 2) || (scores.g4_a !== undefined && scores.g4_a !== '' && scores.g4_a !== null) || (scores.g5_a !== undefined && scores.g5_a !== '' && scores.g5_a !== null) || !!scores.is_best_of_5;

    document.getElementById('btt_is_best_of_5').value = isBestOf5 ? '1' : '0';

    var badge = document.getElementById('btt_format_badge');
    var title = document.getElementById('btt_subform_title');
    var extraCols = document.querySelectorAll('.btt-extra-game');

    if (isOpenCategory) {
      badge.className = 'badge badge-warning px-2 py-1 font-weight-bold text-dark';
      badge.innerText = 'Best of 5 Games (First to 3)';
      title.innerHTML = '<i class="fas fa-medal mr-1 text-warning"></i> Open Category (' + data.game_name + ' - Best of 5 Games)';
      extraCols.forEach(function(el) { el.style.display = ''; });
    } else if (isBestOf5) {
      badge.className = 'badge badge-primary px-2 py-1 font-weight-bold';
      badge.innerText = data.format_badge || 'Best of 5 Games (First to 3)';
      title.innerHTML = '<i class="fas fa-table-tennis mr-1"></i> Table Tennis Men (Best of 5 Games to 11 Pts)';
      extraCols.forEach(function(el) { el.style.display = ''; });
    } else {
      badge.className = 'badge badge-info px-2 py-1 font-weight-bold';
      badge.innerText = data.format_badge || (isTT ? 'Women\'s TT - Best of 3 Games (First to 2)' : 'Best of 3 Games (First to 2)');
      title.innerHTML = '<i class="fas fa-table-tennis mr-1"></i> ' + (isTT ? 'Table Tennis Women (Best of 3 to 11 Pts)' : 'Badminton (Best of 3 to 21 Pts)');
      extraCols.forEach(function(el) { el.style.display = 'none'; });
    }

    if (scores.server === 'team2') document.getElementById('btt_srv_b').checked = true;
    else document.getElementById('btt_srv_a').checked = true;
    document.getElementById('btt_walkover').checked = !!scores.is_walkover;

  } else if (st === 'tennis') {
    document.getElementById('subFormTennis').classList.remove('d-none');
    document.getElementById('ten_t1_lbl').innerText = '[' + (data.u1_code || 'T1') + '] ' + data.t1_name;
    document.getElementById('ten_t2_lbl').innerText = '[' + (data.u2_code || 'T2') + '] ' + data.t2_name;
    document.getElementById('ten_s1_a').value = scores.s1_a ?? '';
    document.getElementById('ten_s1_b').value = scores.s1_b ?? '';
    document.getElementById('ten_s2_a').value = scores.s2_a ?? '';
    document.getElementById('ten_s2_b').value = scores.s2_b ?? '';
    document.getElementById('ten_s3_a').value = scores.s3_a ?? '';
    document.getElementById('ten_s3_b').value = scores.s3_b ?? '';
    document.getElementById('ten_pts_a').value = scores.pts_a ?? '0';
    document.getElementById('ten_pts_b').value = scores.pts_b ?? '0';
    if (scores.server === 'team2') document.getElementById('ten_srv_b').checked = true;
    else document.getElementById('ten_srv_a').checked = true;

  } else if (st === 'chess') {
    document.getElementById('subFormChess').classList.remove('d-none');
    document.getElementById('chess_board_no').value = scores.board_no ?? '1';
    document.getElementById('chess_white_player').value = scores.white_player ?? data.t1_name;
    document.getElementById('chess_white_zone').value = scores.white_zone ?? data.u1_code;
    document.getElementById('chess_black_player').value = scores.black_player ?? data.t2_name;
    document.getElementById('chess_black_zone').value = scores.black_zone ?? data.u2_code;
    document.getElementById('chess_result').value = scores.result ?? '1-0';
    document.getElementById('chess_live_note').value = scores.live_note ?? '';

  } else if (st === 'carrom') {
    document.getElementById('subFormCarrom').classList.remove('d-none');
    document.getElementById('carrom_board_no').value = scores.board_no ?? '1';
    document.getElementById('carrom_t1_lbl').innerText = '[' + (data.u1_code || 'T1') + '] ' + data.t1_name + ' Pts';
    document.getElementById('carrom_t2_lbl').innerText = '[' + (data.u2_code || 'T2') + '] ' + data.t2_name + ' Pts';
    document.getElementById('carrom_pts_a').value = scores.points_a ?? '';
    document.getElementById('carrom_pts_b').value = scores.points_b ?? '';

  } else if (st === 'bridge') {
    document.getElementById('subFormBridge').classList.remove('d-none');
    document.getElementById('bridge_session').value = scores.session_no ?? '1';
    document.getElementById('bridge_table').value = scores.table_no ?? '1';
    document.getElementById('bridge_t1_lbl').innerText = '[' + (data.u1_code || 'T1') + '] ' + data.t1_name;
    document.getElementById('bridge_t2_lbl').innerText = '[' + (data.u2_code || 'T2') + '] ' + data.t2_name;
    document.getElementById('bridge_imp_a').value = scores.imps_a ?? '';
    document.getElementById('bridge_imp_b').value = scores.imps_b ?? '';
    document.getElementById('bridge_vp_a').value = scores.vps_a ?? '';
    document.getElementById('bridge_vp_b').value = scores.vps_b ?? '';

  } else if (st === 'swimming') {
    document.getElementById('subFormSwimming').classList.remove('d-none');
    document.getElementById('swim_event_name').value = scores.event_name ?? '50m Freestyle';
    document.getElementById('swim_heat_no').value = scores.heat_no ?? 'Heat 1 / Final';
    var lanes = scores.lanes || [];
    for (var ln = 1; ln <= 8; ln++) {
      var laneData = lanes.find(function(l) { return l.lane == ln; }) || {};
      document.getElementById('swim_name_' + ln).value = laneData.swimmer || '';
      document.getElementById('swim_zone_' + ln).value = laneData.zone || '';
      document.getElementById('swim_time_' + ln).value = laneData.time || '';
      document.getElementById('swim_pos_' + ln).value = laneData.position || '';
      document.getElementById('swim_status_' + ln).value = laneData.status || 'NORMAL';
    }
  }

  $('#editScoreModal').modal('show');
}

function onScoreFormatChanged(sel) {
  var opt = sel.options[sel.selectedIndex];
  var cols = parseInt(opt.getAttribute('data-cols') || '3');
  var badgeText = opt.getAttribute('data-badge') || '';

  if (document.getElementById('subFormBTT') && !document.getElementById('subFormBTT').classList.contains('d-none')) {
    var isBestOf5 = (cols === 5);
    document.getElementById('btt_is_best_of_5').value = isBestOf5 ? '1' : '0';
    var badge = document.getElementById('btt_format_badge');
    var extraCols = document.querySelectorAll('.btt-extra-game');
    if (isBestOf5) {
      badge.className = 'badge badge-primary px-2 py-1 font-weight-bold';
      badge.innerText = badgeText || 'Best of 5 Games (First to 3)';
      extraCols.forEach(function(el) { el.style.display = ''; });
    } else {
      badge.className = 'badge badge-info px-2 py-1 font-weight-bold';
      badge.innerText = badgeText || 'Best of 3 Games (First to 2)';
      extraCols.forEach(function(el) { el.style.display = 'none'; });
    }
  }
}
</script>
