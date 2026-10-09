<?php
require_once 'config/helpers.php';

$msg = '';
$err = '';
$selectedGameId = (int)($_GET['game_id'] ?? 0);

// Handle Actions (FR-08, FR-09, FR-10, Manual Match Management)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate_draw') {
        $game_id = (int)($_POST['generate_game_id'] ?? 0);
        if ($game_id > 0) {
            // Fetch teams for this game
            $stmt = $pdo->prepare("SELECT t.id, t.name, t.unit_id, u.short_code FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = ?");
            $stmt->execute([$game_id]);
            $teams = $stmt->fetchAll();

            if (count($teams) >= 2) {
                // Delete existing matches for this game before fresh draw
                $pdo->prepare("DELETE FROM matches WHERE game_id = ?")->execute([$game_id]);

                // Shuffle teams with seeded randomizer (TMM Seeded Random Draw FR-08)
                mt_srand(time());
                shuffle($teams);

                // Fetch game info to map to dedicated facility
                $gameStmt = $pdo->prepare("SELECT name, slug, format FROM games WHERE id = ?");
                $gameStmt->execute([$game_id]);
                $gameInfo = $gameStmt->fetch() ?: ['name' => '', 'slug' => '', 'format' => 'pools_knockout'];
                $gName = $gameInfo['name'];

                $defFacId = 1;
                if (stripos($gName, 'Open') !== false && stripos($gName, 'Badminton') !== false) {
                    $fStmt = $pdo->prepare("SELECT id FROM facilities WHERE name LIKE '%Badminton Court 2%' LIMIT 1");
                    $fStmt->execute();
                    $defFacId = $fStmt->fetchColumn() ?: 1;
                } elseif (stripos($gName, 'Open') !== false && stripos($gName, 'Table Tennis') !== false) {
                    $fStmt = $pdo->prepare("SELECT id FROM facilities WHERE name LIKE '%Table Tennis Table 2%' LIMIT 1");
                    $fStmt->execute();
                    $defFacId = $fStmt->fetchColumn() ?: 778;
                } else {
                    $firstWord = explode(' ', trim($gName))[0];
                    $fStmt = $pdo->prepare("SELECT id FROM facilities WHERE name LIKE ? ORDER BY id ASC LIMIT 1");
                    $fStmt->execute(['%' . $firstWord . '%']);
                    $matched = $fStmt->fetchColumn();
                    if ($matched) {
                        $defFacId = $matched;
                    } else {
                        $facStmt = $pdo->prepare("SELECT id FROM facilities WHERE type IN ('court', 'table', 'board', 'pool') LIMIT 1");
                        $facStmt->execute();
                        $defFacId = $facStmt->fetchColumn() ?: 1;
                    }
                }

                $insStmt = $pdo->prepare("
                    INSERT INTO matches (game_id, round, pool_name, team1_id, team2_id, facility_id, match_date, start_time, end_time, status, is_published)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', 0)
                ");

                // Special handling for Swimming: Generate Event Heats & Finals
                if (stripos($gName, 'Swimming') !== false || ($gameInfo['slug'] ?? '') === 'swimming') {
                    $swimEvents = [
                        ['round' => '50m Freestyle - Heat 1', 'date' => '2026-10-08', 'start' => '09:00:00', 'end' => '09:45:00'],
                        ['round' => '50m Freestyle - Heat 2', 'date' => '2026-10-08', 'start' => '10:00:00', 'end' => '10:45:00'],
                        ['round' => '50m Freestyle - Final',  'date' => '2026-10-08', 'start' => '11:15:00', 'end' => '12:00:00'],
                        ['round' => '100m Breaststroke - Heat 1', 'date' => '2026-10-09', 'start' => '09:00:00', 'end' => '09:45:00'],
                        ['round' => '100m Breaststroke - Final',  'date' => '2026-10-09', 'start' => '10:30:00', 'end' => '11:15:00'],
                        ['round' => '4x50m Freestyle Relay - Final', 'date' => '2026-10-10', 'start' => '14:00:00', 'end' => '15:00:00']
                    ];
                    foreach ($swimEvents as $ev) {
                        $insStmt->execute([
                            $game_id, $ev['round'], 'All Zones', $teams[0]['id'], $teams[1]['id'] ?? $teams[0]['id'],
                            $defFacId, $ev['date'], $ev['start'], $ev['end']
                        ]);
                    }
                } elseif (($gameInfo['format'] ?? '') === 'seeded_knockout' || ($gameInfo['format'] ?? '') === 'knockout') {
                    // Seeded Knockout Bracket (FR-08)
                    $teamCount = count($teams);
                    if ($teamCount >= 12) {
                        // 4 Byes, 4 Round 1 matches
                        $r1Teams = array_slice($teams, 4);
                        $qfSeeds = array_slice($teams, 0, 4);

                        $times = ['09:00:00', '10:30:00', '12:00:00', '13:30:00'];
                        for ($k = 0; $k < 4; $k++) {
                            $tA = $r1Teams[$k * 2];
                            $tB = $r1Teams[$k * 2 + 1];
                            $insStmt->execute([
                                $game_id, 'Round 1 (Pre-QF ' . ($k + 1) . ')', 'Knockout', $tA['id'], $tB['id'],
                                $defFacId, '2026-10-08', $times[$k], date('H:i:s', strtotime($times[$k]) + 4500)
                            ]);
                        }

                        // Quarterfinals
                        for ($k = 0; $k < 4; $k++) {
                            $insStmt->execute([
                                $game_id, 'Quarterfinal ' . ($k + 1), 'Knockout', $qfSeeds[$k]['id'], $r1Teams[$k * 2]['id'],
                                $defFacId, '2026-10-09', $times[$k], date('H:i:s', strtotime($times[$k]) + 4500)
                            ]);
                        }
                    } else {
                        for ($k = 0; $k < $teamCount; $k += 2) {
                            if (isset($teams[$k + 1])) {
                                $insStmt->execute([
                                    $game_id, 'Round 1 - Match ' . (($k / 2) + 1), 'Knockout', $teams[$k]['id'], $teams[$k + 1]['id'],
                                    $defFacId, '2026-10-08', '10:00:00', '11:30:00'
                                ]);
                            }
                        }
                    }

                    // Semifinals and Final
                    $insStmt->execute([
                        $game_id, 'Semifinal 1', 'Knockout', $teams[0]['id'], $teams[1]['id'],
                        $defFacId, '2026-10-10', '10:00:00', '11:30:00'
                    ]);
                    $insStmt->execute([
                        $game_id, 'Semifinal 2', 'Knockout', $teams[2]['id'] ?? $teams[0]['id'], $teams[3]['id'] ?? $teams[1]['id'],
                        $defFacId, '2026-10-10', '11:45:00', '13:15:00'
                    ]);
                    $insStmt->execute([
                        $game_id, 'Grand Final', 'Knockout', $teams[0]['id'], $teams[2]['id'] ?? $teams[1]['id'],
                        $defFacId, '2026-10-10', '15:00:00', '17:00:00'
                    ]);

                } else {
                    // Full Round-Robin Pools (Pool A & Pool B) -> Semifinals -> Final (FR-08)
                    $mid = ceil(count($teams) / 2);
                    $poolA = array_slice($teams, 0, $mid);
                    $poolB = array_slice($teams, $mid);

                    // Update teams table with pool assignment
                    $updPoolStmt = $pdo->prepare("UPDATE teams SET pool = ? WHERE id = ?");
                    foreach ($poolA as $t) { $updPoolStmt->execute(['A', $t['id']]); }
                    foreach ($poolB as $t) { $updPoolStmt->execute(['B', $t['id']]); }

                    // Determine second facility for Pool B if available
                    $facBId = $defFacId;
                    if (stripos($gName, 'Badminton') !== false) {
                        $f2 = $pdo->query("SELECT id FROM facilities WHERE name LIKE '%Badminton Court 2%' LIMIT 1")->fetchColumn();
                        if ($f2) $facBId = $f2;
                    } elseif (stripos($gName, 'Table Tennis') !== false) {
                        $f2 = $pdo->query("SELECT id FROM facilities WHERE name LIKE '%Table Tennis Table 2%' LIMIT 1")->fetchColumn();
                        if ($f2) $facBId = $f2;
                    }

                    // Helper to generate Berger round-robin pairings
                    $genRR = function($pTeams) {
                        $n = count($pTeams);
                        if ($n % 2 != 0) { $pTeams[] = null; $n++; }
                        $rounds = [];
                        for ($round = 0; $round < $n - 1; $round++) {
                            $matches = [];
                            for ($match = 0; $match < $n / 2; $match++) {
                                $home = ($round + $match) % ($n - 1);
                                $away = ($n - 1 - $match + $round) % ($n - 1);
                                if ($match == 0) { $away = $n - 1; }
                                if ($pTeams[$home] !== null && $pTeams[$away] !== null) {
                                    $matches[] = [$pTeams[$home], $pTeams[$away]];
                                }
                            }
                            $rounds[$round + 1] = $matches;
                        }
                        return $rounds;
                    };

                    $rrA = $genRR($poolA);
                    $rrB = $genRR($poolB);

                    // Staggered round times across tournament days
                    $roundScheduleMeta = [
                        1 => ['date' => '2026-10-08', 'slots' => [['09:00:00', '10:15:00'], ['10:30:00', '11:45:00'], ['12:00:00', '13:15:00']]],
                        2 => ['date' => '2026-10-08', 'slots' => [['14:30:00', '15:45:00'], ['16:00:00', '17:15:00'], ['17:30:00', '18:45:00']]],
                        3 => ['date' => '2026-10-09', 'slots' => [['09:00:00', '10:15:00'], ['10:30:00', '11:45:00'], ['12:00:00', '13:15:00']]],
                        4 => ['date' => '2026-10-09', 'slots' => [['14:00:00', '15:15:00'], ['15:30:00', '16:45:00'], ['17:00:00', '18:15:00']]],
                        5 => ['date' => '2026-10-09', 'slots' => [['18:30:00', '19:45:00'], ['20:00:00', '21:15:00'], ['21:30:00', '22:45:00']]],
                    ];

                    // Insert Pool A rounds
                    foreach ($rrA as $rNum => $mList) {
                        $meta = $roundScheduleMeta[$rNum] ?? ['date' => '2026-10-08', 'slots' => [['09:00:00', '10:15:00']]];
                        foreach ($mList as $idx => $pair) {
                            $slot = $meta['slots'][$idx % count($meta['slots'])];
                            $insStmt->execute([
                                $game_id, "Pool A - Round $rNum", 'A', $pair[0]['id'], $pair[1]['id'],
                                $defFacId, $meta['date'], $slot[0], $slot[1]
                            ]);
                        }
                    }

                    // Insert Pool B rounds
                    foreach ($rrB as $rNum => $mList) {
                        $meta = $roundScheduleMeta[$rNum] ?? ['date' => '2026-10-08', 'slots' => [['09:00:00', '10:15:00']]];
                        foreach ($mList as $idx => $pair) {
                            $slot = $meta['slots'][$idx % count($meta['slots'])];
                            $insStmt->execute([
                                $game_id, "Pool B - Round $rNum", 'B', $pair[0]['id'], $pair[1]['id'],
                                $facBId, $meta['date'], $slot[0], $slot[1]
                            ]);
                        }
                    }

                    // Knockout Finals on Day 3
                    $insStmt->execute([
                        $game_id, 'Semifinal 1 (Winner A vs Runner-up B)', 'Knockout', $poolA[0]['id'], $poolB[1]['id'] ?? $poolB[0]['id'],
                        $defFacId, '2026-10-10', '09:30:00', '11:00:00'
                    ]);
                    $insStmt->execute([
                        $game_id, 'Semifinal 2 (Winner B vs Runner-up A)', 'Knockout', $poolB[0]['id'], $poolA[1]['id'] ?? $poolA[0]['id'],
                        $defFacId, '2026-10-10', '11:15:00', '12:45:00'
                    ]);
                    $insStmt->execute([
                        $game_id, '3rd Place Playoff (Bronze Medal)', 'Knockout', $poolA[1]['id'] ?? $poolA[0]['id'], $poolB[1]['id'] ?? $poolB[0]['id'],
                        $defFacId, '2026-10-10', '14:00:00', '15:30:00'
                    ]);
                    $insStmt->execute([
                        $game_id, 'Grand Final (Championship)', 'Knockout', $poolA[0]['id'], $poolB[0]['id'],
                        $defFacId, '2026-10-10', '16:00:00', '18:00:00'
                    ]);
                }

                log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'GENERATE_DRAW', "Executed seeded random draw for Game #$game_id at Team Managers' Meeting (FR-08)");
                $msg = "Seeded random draw generated successfully! You can now adjust pairings, edit schedules, or add matches below before publishing.";
                $selectedGameId = $game_id;
            } else {
                $err = "Need at least 2 registered teams in this discipline to generate draws.";
            }
        }
    } elseif ($action === 'publish_draws') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        if ($game_id > 0) {
            $pdo->prepare("UPDATE matches SET is_published = 1 WHERE game_id = ?")->execute([$game_id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'DRAWS_PUBLISHED', "Published draws for Game #$game_id to public dashboard (FR-10)");
            $msg = "Draws published! Now instantly visible across all public spectator and per-game dashboards.";
            $selectedGameId = $game_id;
        }
    } elseif ($action === 'swap_teams') {
        $match_id = (int)($_POST['match_id'] ?? 0);
        $t1 = (int)($_POST['team1_id'] ?? 0);
        $t2 = (int)($_POST['team2_id'] ?? 0);
        if ($match_id > 0 && $t1 && $t2) {
            $pdo->prepare("UPDATE matches SET team1_id = ?, team2_id = ? WHERE id = ?")->execute([$t1, $t2, $match_id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'DRAW_ADJUSTMENT', "Admin adjusted pairings for Match #$match_id (FR-09)");
            $msg = "Match #$match_id pairing adjusted successfully.";
            $selectedGameId = (int)$_POST['game_id'];
        }
    } elseif ($action === 'create_match') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        $round = trim($_POST['round'] ?? 'Round 1');
        $pool_name = trim($_POST['pool_name'] ?? '');
        $team1_id = (int)($_POST['team1_id'] ?? 0);
        $team2_id = (int)($_POST['team2_id'] ?? 0);
        $facility_id = (int)($_POST['facility_id'] ?? 0);
        $match_date = $_POST['match_date'] ?? '2026-10-08';
        $start_time = $_POST['start_time'] ?? '09:00:00';
        $end_time = $_POST['end_time'] ?? '10:15:00';
        $is_pub = isset($_POST['is_published']) ? 1 : 0;

        if ($game_id > 0 && $team1_id > 0 && $team2_id > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO matches (game_id, round, pool_name, team1_id, team2_id, facility_id, match_date, start_time, end_time, status, is_published)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', ?)
            ");
            $stmt->execute([$game_id, $round, $pool_name ?: null, $team1_id, $team2_id, $facility_id ?: null, $match_date, $start_time, $end_time, $is_pub]);
            $newId = $pdo->lastInsertId();
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'CREATE_MATCH', "Manually created Match #$newId ($round) for Game #$game_id");
            $msg = "Match #$newId created successfully!";
            $selectedGameId = $game_id;
        } else {
            $err = "Please select both teams and ensure valid match details.";
        }
    } elseif ($action === 'update_match') {
        $match_id = (int)($_POST['match_id'] ?? 0);
        $game_id = (int)($_POST['game_id'] ?? 0);
        $round = trim($_POST['round'] ?? '');
        $pool_name = trim($_POST['pool_name'] ?? '');
        $team1_id = (int)($_POST['team1_id'] ?? 0);
        $team2_id = (int)($_POST['team2_id'] ?? 0);
        $facility_id = (int)($_POST['facility_id'] ?? 0);
        $match_date = $_POST['match_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $status = $_POST['status'] ?? 'scheduled';
        $is_pub = isset($_POST['is_published']) ? 1 : 0;

        if ($match_id > 0 && $team1_id > 0 && $team2_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE matches 
                SET round = ?, pool_name = ?, team1_id = ?, team2_id = ?, facility_id = ?, 
                    match_date = ?, start_time = ?, end_time = ?, status = ?, is_published = ?
                WHERE id = ?
            ");
            $stmt->execute([$round, $pool_name ?: null, $team1_id, $team2_id, $facility_id ?: null, $match_date, $start_time, $end_time, $status, $is_pub, $match_id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'UPDATE_MATCH', "Admin updated details for Match #$match_id");
            $msg = "Match #$match_id details updated successfully.";
            $selectedGameId = $game_id;
        } else {
            $err = "Invalid match update parameters. Please select both teams.";
        }
    } elseif ($action === 'delete_match') {
        $match_id = (int)($_POST['match_id'] ?? 0);
        $game_id = (int)($_POST['game_id'] ?? 0);
        if ($match_id > 0) {
            $pdo->prepare("DELETE FROM matches WHERE id = ?")->execute([$match_id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'DELETE_MATCH', "Admin deleted Match #$match_id");
            $msg = "Match #$match_id deleted successfully.";
            $selectedGameId = $game_id;
        }
    } elseif ($action === 'clear_all_matches') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        if ($game_id > 0) {
            $pdo->prepare("DELETE FROM matches WHERE game_id = ?")->execute([$game_id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'CLEAR_MATCHES', "Admin cleared all drafted matches for Game #$game_id");
            $msg = "All matches for this discipline have been cleared.";
            $selectedGameId = $game_id;
        }
    }
}

// Fetch all games
$games = $pdo->query("SELECT * FROM games ORDER BY name ASC")->fetchAll();
if (!$selectedGameId && !empty($games)) {
    $selectedGameId = $games[0]['id'];
}

// Fetch matches for selected game
$gameMatches = [];
$isPublished = false;
if ($selectedGameId > 0) {
    $stmt = $pdo->prepare("
        SELECT m.*, 
               t1.name as team1_name, u1.short_code as u1_code,
               t2.name as team2_name, u2.short_code as u2_code,
               f.name as facility_name
        FROM matches m
        LEFT JOIN teams t1 ON m.team1_id = t1.id
        LEFT JOIN units u1 ON t1.unit_id = u1.id
        LEFT JOIN teams t2 ON m.team2_id = t2.id
        LEFT JOIN units u2 ON t2.unit_id = u2.id
        LEFT JOIN facilities f ON m.facility_id = f.id
        WHERE m.game_id = ?
        ORDER BY m.id ASC
    ");
    $stmt->execute([$selectedGameId]);
    $gameMatches = $stmt->fetchAll();

    foreach ($gameMatches as $gm) {
        if ($gm['is_published']) {
            $isPublished = true;
            break;
        }
    }
}

// Fetch teams for selected game for pairing adjustment dropdowns
$gameTeams = [];
if ($selectedGameId > 0) {
    $stmt = $pdo->prepare("SELECT t.id, t.name, u.short_code FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = ? ORDER BY u.short_code ASC");
    $stmt->execute([$selectedGameId]);
    $gameTeams = $stmt->fetchAll();
}

// Fetch facilities for dropdowns
$allFacilities = $pdo->query("SELECT id, name, type FROM facilities ORDER BY name ASC")->fetchAll();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-sitemap text-primary mr-2"></i> Draws & Pairings</h1>
        <p class="text-muted mb-0">TMM Seeded Random Draw &rarr; Manual Custom Matches &rarr; Edit/Delete Pairings &rarr; Instant Public Publish</p>
      </div>
      <div class="col-sm-6 text-right d-flex justify-content-end align-items-center">
        <button type="button" class="btn btn-primary font-weight-bold mr-2 shadow-sm" data-toggle="modal" data-target="#createMatchModal">
          <i class="fas fa-plus-circle mr-1"></i> Add Match Manually
        </button>
        <?php if ($selectedGameId && !empty($gameMatches)): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="action" value="publish_draws">
            <input type="hidden" name="game_id" value="<?= $selectedGameId ?>">
            <button type="submit" class="btn btn-success font-weight-bold shadow-sm">
              <i class="fas fa-bullhorn mr-1"></i> Publish Draws to Public
            </button>
          </form>
        <?php endif; ?>
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

    <div class="row">
      <!-- Left Column: Generator Form -->
      <div class="col-lg-4">
        <div class="card card-warning elevation-2">
          <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-dice mr-2"></i> TMM Seeded Random Draw</h3>
          </div>
          <div class="card-body">
            <div class="alert alert-light small border">
              <strong><i class="fas fa-clock mr-1"></i> Day 1 Morning TMM:</strong> Executes seeded random draw across 8 HPCL units per Annexure A format and records audit timestamp.
            </div>

            <form method="POST" onsubmit="return confirm('Execute seeded random draw? This will generate full pool round-robins and knockout fixtures.');">
              <input type="hidden" name="action" value="generate_draw">
              <div class="form-group">
                <label>Select Discipline</label>
                <select name="generate_game_id" class="form-control" onchange="window.location.href='<?= BASE_URL ?>/admin/draws?game_id=' + this.value">
                  <?php foreach ($games as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= $selectedGameId == $g['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['category']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="p-3 bg-light rounded mb-3 small">
                <p class="mb-1"><strong>Selected Format:</strong> <?= htmlspecialchars($games[array_search($selectedGameId, array_column($games, 'id'))]['format'] ?? 'pools_knockout') ?></p>
                <p class="mb-0 text-muted">Pool A & B 5-round robins &rarr; Semifinals &rarr; Bronze Medal &rarr; Grand Final.</p>
              </div>

              <button type="submit" class="btn btn-warning btn-block font-weight-bold">
                <i class="fas fa-cogs mr-1"></i> Execute Seeded Draw & Fixtures
              </button>
            </form>

            <div class="mt-3 pt-3 border-top">
              <button type="button" class="btn btn-outline-primary btn-block font-weight-bold" data-toggle="modal" data-target="#createMatchModal">
                <i class="fas fa-plus-circle mr-1"></i> Add Custom Match to This Sport
              </button>
            </div>
          </div>
        </div>

        <div class="card card-info elevation-2">
          <div class="card-header">
            <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-2"></i> Publication Status</h3>
          </div>
          <div class="card-body">
            <?php if ($isPublished): ?>
              <div class="alert alert-success mb-2">
                <i class="fas fa-check-circle mr-1"></i> <strong>Draws are LIVE on Public Dashboard</strong>
              </div>
              <p class="small text-muted mb-0">Spectators, team managers, and volunteer scorer apps can see all published fixtures in real-time.</p>
            <?php else: ?>
              <div class="alert alert-secondary mb-2">
                <i class="fas fa-eye-slash mr-1"></i> <strong>Draft Mode (Unpublished)</strong>
              </div>
              <p class="small text-muted mb-0">Use the green "Publish Draws to Public" button on the top right to push fixtures live to spectators and volunteer mobile apps.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Right Column: Match List & Actions -->
      <div class="col-lg-8">
        <div class="card card-primary card-outline elevation-2">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold mb-0">
              <i class="fas fa-network-wired text-primary mr-2"></i> Bracket & Pairing Adjustments
            </h3>
            <div class="card-tools d-flex align-items-center">
              <span class="badge badge-primary px-2 py-1 mr-2"><?= count($gameMatches) ?> Matches</span>
              <button type="button" class="btn btn-xs btn-primary mr-2" data-toggle="modal" data-target="#createMatchModal">
                <i class="fas fa-plus"></i> Add Match
              </button>
              <?php if (!empty($gameMatches)): ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Clear ALL matches for this sport? This cannot be undone.');">
                  <input type="hidden" name="action" value="clear_all_matches">
                  <input type="hidden" name="game_id" value="<?= $selectedGameId ?>">
                  <button type="submit" class="btn btn-xs btn-outline-danger">
                    <i class="fas fa-trash-alt"></i> Clear All
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
          <div class="card-body p-0 table-responsive" style="max-height: 700px;">
            <?php if (empty($gameMatches)): ?>
              <div class="p-5 text-center text-muted">
                <i class="fas fa-calendar-times fa-3x mb-3 text-secondary"></i>
                <h5>No draw or matches generated for this discipline yet.</h5>
                <p class="small">Click <strong>"Execute Seeded Draw & Fixtures"</strong> on the left, or click <strong>"Add Match Manually"</strong> above to craft custom fixtures.</p>
                <button type="button" class="btn btn-sm btn-primary font-weight-bold" data-toggle="modal" data-target="#createMatchModal">
                  <i class="fas fa-plus-circle mr-1"></i> Create First Match
                </button>
              </div>
            <?php else: ?>
              <table class="table table-hover table-striped mb-0 text-nowrap">
                <thead class="thead-light">
                  <tr>
                    <th>Match</th>
                    <th>Round / Stage</th>
                    <th>Team 1 vs Team 2</th>
                    <th>Schedule & Venue</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($gameMatches as $gm): ?>
                    <?php
                    $rBadgeClass = 'badge-secondary';
                    if (stripos($gm['round'], 'Pool A') !== false) {
                        $rBadgeClass = 'badge-info';
                    } elseif (stripos($gm['round'], 'Pool B') !== false) {
                        $rBadgeClass = 'badge-primary';
                    } elseif (stripos($gm['round'], 'Semifinal') !== false) {
                        $rBadgeClass = 'badge-warning text-dark font-weight-bold';
                    } elseif (stripos($gm['round'], 'Final') !== false) {
                        $rBadgeClass = 'badge-danger font-weight-bold';
                    } elseif (stripos($gm['round'], 'Playoff') !== false || stripos($gm['round'], 'Bronze') !== false) {
                        $rBadgeClass = 'badge-success';
                    }
                    ?>
                    <tr>
                      <td class="align-middle"><strong>#<?= $gm['id'] ?></strong></td>
                      <td class="align-middle">
                        <span class="badge <?= $rBadgeClass ?> px-2 py-1"><?= htmlspecialchars($gm['round']) ?></span>
                        <?php if ($gm['pool_name']): ?>
                          <span class="badge badge-light border ml-1">Pool <?= htmlspecialchars($gm['pool_name']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="align-middle">
                        <div class="d-flex align-items-center">
                          <span class="badge badge-dark mr-1" style="min-width: 32px;"><?= htmlspecialchars($gm['u1_code'] ?: 'T1') ?></span>
                          <span class="font-weight-bold mr-2 text-truncate" style="max-width: 130px;"><?= htmlspecialchars($gm['team1_name'] ?: 'Team 1') ?></span>
                          <span class="text-muted font-weight-bold mx-1">vs</span>
                          <span class="badge badge-dark mr-1" style="min-width: 32px;"><?= htmlspecialchars($gm['u2_code'] ?: 'T2') ?></span>
                          <span class="font-weight-bold text-truncate" style="max-width: 130px;"><?= htmlspecialchars($gm['team2_name'] ?: 'Team 2') ?></span>
                        </div>
                      </td>
                      <td class="align-middle small">
                        <div><i class="far fa-calendar-alt text-primary mr-1"></i> <?= date('d M Y', strtotime($gm['match_date'])) ?> &bull; <?= date('h:i A', strtotime($gm['start_time'])) ?> - <?= date('h:i A', strtotime($gm['end_time'])) ?></div>
                        <div class="text-muted"><i class="fas fa-map-marker-alt text-danger mr-1"></i> <?= htmlspecialchars($gm['facility_name'] ?: 'Court Not Assigned') ?></div>
                      </td>
                      <td class="align-middle">
                        <?php if ($gm['is_published']): ?>
                          <span class="badge badge-success"><i class="fas fa-globe"></i> Live</span>
                        <?php else: ?>
                          <span class="badge badge-secondary">Draft</span>
                        <?php endif; ?>
                        <?php if ($gm['status'] === 'completed'): ?>
                          <span class="badge badge-dark ml-1">Completed</span>
                        <?php elseif ($gm['status'] === 'in_progress'): ?>
                          <span class="badge badge-danger ml-1">Live</span>
                        <?php endif; ?>
                      </td>
                      <td class="align-middle text-right">
                        <!-- Edit Button -->
                        <button type="button" class="btn btn-xs btn-primary mr-1" 
                                onclick='openEditMatchModal(<?= json_encode([
                                    "id" => $gm["id"],
                                    "game_id" => $gm["game_id"],
                                    "round" => $gm["round"],
                                    "pool_name" => $gm["pool_name"] ?? "",
                                    "team1_id" => $gm["team1_id"],
                                    "team2_id" => $gm["team2_id"],
                                    "facility_id" => $gm["facility_id"] ?? 0,
                                    "match_date" => $gm["match_date"],
                                    "start_time" => substr($gm["start_time"], 0, 5),
                                    "end_time" => substr($gm["end_time"], 0, 5),
                                    "status" => $gm["status"],
                                    "is_published" => (int)$gm["is_published"]
                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                title="Edit Match, Round, Teams, Venue & Schedule">
                          <i class="fas fa-edit"></i> Edit
                        </button>

                        <!-- Delete Button -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete Match #<?= $gm['id'] ?> (<?= htmlspecialchars($gm['round']) ?>)? This cannot be undone.');">
                          <input type="hidden" name="action" value="delete_match">
                          <input type="hidden" name="match_id" value="<?= $gm['id'] ?>">
                          <input type="hidden" name="game_id" value="<?= $selectedGameId ?>">
                          <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete Match">
                            <i class="fas fa-trash-alt"></i>
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: CREATE MATCH MANUALLY             -->
<!-- ========================================== -->
<div class="modal fade" id="createMatchModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="create_match">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-plus-circle mr-2"></i> Manually Create Match
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Discipline / Sport <span class="text-danger">*</span></label>
              <select name="game_id" class="form-control" required onchange="window.location.href='<?= BASE_URL ?>/admin/draws?game_id=' + this.value">
                <?php foreach ($games as $g): ?>
                  <option value="<?= $g['id'] ?>" <?= $selectedGameId == $g['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['category']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Round / Stage <span class="text-danger">*</span></label>
              <input type="text" name="round" list="roundSuggestions" class="form-control" placeholder="e.g. Pool A - Round 1, or 50m Freestyle" required>
              <datalist id="roundSuggestions">
                <option value="Pool A - Round 1">
                <option value="Pool A - Round 2">
                <option value="Pool A - Round 3">
                <option value="Pool A - Round 4">
                <option value="Pool A - Round 5">
                <option value="Pool B - Round 1">
                <option value="Pool B - Round 2">
                <option value="Pool B - Round 3">
                <option value="Pool B - Round 4">
                <option value="Pool B - Round 5">
                <option value="Swiss Round 1">
                <option value="Swiss Round 2">
                <option value="Swiss Round 3">
                <option value="Swiss Round 4">
                <option value="Swiss Round 5">
                <option value="Session 1">
                <option value="Session 2">
                <option value="Session 3">
                <option value="Round 1 (Pre-QF 1)">
                <option value="Round 1 (Pre-QF 2)">
                <option value="Quarterfinal 1">
                <option value="Quarterfinal 2">
                <option value="Quarterfinal 3">
                <option value="Quarterfinal 4">
                <option value="Semifinal 1">
                <option value="Semifinal 2">
                <option value="3rd Place Playoff (Bronze Medal)">
                <option value="Grand Final (Gold Medal)">
                <option value="50m Freestyle - Heat 1">
                <option value="50m Freestyle - Heat 2">
                <option value="50m Freestyle - Final">
                <option value="100m Breaststroke - Heat 1">
                <option value="100m Breaststroke - Final">
                <option value="50m Backstroke - Final">
                <option value="50m Butterfly - Final">
                <option value="4x50m Freestyle Relay - Final">
                <option value="Exhibition Match">
              </datalist>
            </div>
            <div class="col-md-3 form-group">
              <label>Pool / Group</label>
              <select name="pool_name" class="form-control">
                <option value="">None / Knockout</option>
                <option value="A">Pool A</option>
                <option value="B">Pool B</option>
                <option value="C">Pool C</option>
                <option value="D">Pool D</option>
                <option value="Knockout">Knockout</option>
                <option value="Swiss League">Swiss League</option>
                <option value="All Zones">All Zones (Swimming)</option>
              </select>
            </div>
          </div>

          <div class="row bg-light p-2 rounded mb-3 border">
            <div class="col-md-6 form-group mb-0">
              <label><i class="fas fa-shield-alt text-primary mr-1"></i> Team 1 <span class="text-danger">*</span></label>
              <select name="team1_id" class="form-control font-weight-bold" required>
                <option value="">-- Select Team 1 --</option>
                <?php foreach ($gameTeams as $gt): ?>
                  <option value="<?= $gt['id'] ?>">
                    [<?= htmlspecialchars($gt['short_code']) ?>] <?= htmlspecialchars($gt['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 form-group mb-0">
              <label><i class="fas fa-shield-alt text-danger mr-1"></i> Team 2 <span class="text-danger">*</span></label>
              <select name="team2_id" class="form-control font-weight-bold" required>
                <option value="">-- Select Team 2 --</option>
                <?php foreach ($gameTeams as $gt): ?>
                  <option value="<?= $gt['id'] ?>">
                    [<?= htmlspecialchars($gt['short_code']) ?>] <?= htmlspecialchars($gt['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 form-group">
              <label><i class="far fa-calendar-alt mr-1"></i> Match Date <span class="text-danger">*</span></label>
              <input type="date" name="match_date" class="form-control" value="2026-10-08" required>
            </div>
            <div class="col-md-4 form-group">
              <label><i class="far fa-clock mr-1"></i> Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" class="form-control" value="09:00" required>
            </div>
            <div class="col-md-4 form-group">
              <label><i class="far fa-clock mr-1"></i> End Time <span class="text-danger">*</span></label>
              <input type="time" name="end_time" class="form-control" value="10:15" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8 form-group">
              <label><i class="fas fa-map-marker-alt text-danger mr-1"></i> Venue / Facility Court</label>
              <select name="facility_id" class="form-control">
                <option value="">-- Auto Assign / Default Court --</option>
                <?php foreach ($allFacilities as $fac): ?>
                  <option value="<?= $fac['id'] ?>">
                    <?= htmlspecialchars($fac['name']) ?> (<?= htmlspecialchars($fac['type']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 form-group d-flex align-items-center pt-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="is_published" class="custom-control-input" id="createPublish" value="1" checked>
                <label class="custom-control-label font-weight-bold" for="createPublish">Publish Immediately (Live)</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">
            <i class="fas fa-plus-circle mr-1"></i> Create Match
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: EDIT MATCH DETAILS                -->
<!-- ========================================== -->
<div class="modal fade" id="editMatchModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="update_match">
        <input type="hidden" name="match_id" id="edit_match_id">
        <input type="hidden" name="game_id" value="<?= $selectedGameId ?>">

        <div class="modal-header bg-info text-white">
          <h5 class="modal-title font-weight-bold">
            <i class="fas fa-edit mr-2"></i> Edit Match Details (<span id="edit_match_id_display"></span>)
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-8 form-group">
              <label>Round / Stage Name <span class="text-danger">*</span></label>
              <input type="text" name="round" id="edit_round" class="form-control" required>
            </div>
            <div class="col-md-4 form-group">
              <label>Pool / Group</label>
              <select name="pool_name" id="edit_pool_name" class="form-control">
                <option value="">None / Knockout</option>
                <option value="A">Pool A</option>
                <option value="B">Pool B</option>
                <option value="C">Pool C</option>
                <option value="D">Pool D</option>
                <option value="Knockout">Knockout</option>
                <option value="Swiss League">Swiss League</option>
                <option value="All Zones">All Zones (Swimming)</option>
              </select>
            </div>
          </div>

          <div class="row bg-light p-2 rounded mb-3 border">
            <div class="col-md-6 form-group mb-0">
              <label><i class="fas fa-shield-alt text-primary mr-1"></i> Team 1 <span class="text-danger">*</span></label>
              <select name="team1_id" id="edit_team1_id" class="form-control font-weight-bold" required>
                <?php foreach ($gameTeams as $gt): ?>
                  <option value="<?= $gt['id'] ?>">
                    [<?= htmlspecialchars($gt['short_code']) ?>] <?= htmlspecialchars($gt['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 form-group mb-0">
              <label><i class="fas fa-shield-alt text-danger mr-1"></i> Team 2 <span class="text-danger">*</span></label>
              <select name="team2_id" id="edit_team2_id" class="form-control font-weight-bold" required>
                <?php foreach ($gameTeams as $gt): ?>
                  <option value="<?= $gt['id'] ?>">
                    [<?= htmlspecialchars($gt['short_code']) ?>] <?= htmlspecialchars($gt['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 form-group">
              <label><i class="far fa-calendar-alt mr-1"></i> Match Date <span class="text-danger">*</span></label>
              <input type="date" name="match_date" id="edit_match_date" class="form-control" required>
            </div>
            <div class="col-md-4 form-group">
              <label><i class="far fa-clock mr-1"></i> Start Time <span class="text-danger">*</span></label>
              <input type="time" name="start_time" id="edit_start_time" class="form-control" required>
            </div>
            <div class="col-md-4 form-group">
              <label><i class="far fa-clock mr-1"></i> End Time <span class="text-danger">*</span></label>
              <input type="time" name="end_time" id="edit_end_time" class="form-control" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 form-group">
              <label><i class="fas fa-map-marker-alt text-danger mr-1"></i> Venue / Facility Court</label>
              <select name="facility_id" id="edit_facility_id" class="form-control">
                <option value="">-- Not Assigned --</option>
                <?php foreach ($allFacilities as $fac): ?>
                  <option value="<?= $fac['id'] ?>">
                    <?= htmlspecialchars($fac['name']) ?> (<?= htmlspecialchars($fac['type']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Match Status</label>
              <select name="status" id="edit_status" class="form-control">
                <option value="scheduled">Scheduled</option>
                <option value="in_progress">In Progress (Live)</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div class="col-md-3 form-group d-flex align-items-center pt-3">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" name="is_published" class="custom-control-input" id="edit_is_published" value="1">
                <label class="custom-control-label font-weight-bold" for="edit_is_published">Published (Live)</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info font-weight-bold">
            <i class="fas fa-save mr-1"></i> Save Match Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditMatchModal(data) {
  document.getElementById('edit_match_id').value = data.id;
  document.getElementById('edit_match_id_display').innerText = '#' + data.id;
  document.getElementById('edit_round').value = data.round;
  document.getElementById('edit_pool_name').value = data.pool_name || '';
  document.getElementById('edit_team1_id').value = data.team1_id;
  document.getElementById('edit_team2_id').value = data.team2_id;
  document.getElementById('edit_facility_id').value = data.facility_id || '';
  document.getElementById('edit_match_date').value = data.match_date;
  document.getElementById('edit_start_time').value = data.start_time;
  document.getElementById('edit_end_time').value = data.end_time;
  document.getElementById('edit_status').value = data.status || 'scheduled';
  document.getElementById('edit_is_published').checked = !!data.is_published;

  $('#editMatchModal').modal('show');
}
</script>
