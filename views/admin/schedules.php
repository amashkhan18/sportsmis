<?php
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

// Handle Schedule actions: Add Match / Reschedule / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_match') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        $round = trim($_POST['round'] ?? '');
        $pool_name = trim($_POST['pool_name'] ?? '');
        $team1_id = (int)($_POST['team1_id'] ?? 0);
        $team2_id = (int)($_POST['team2_id'] ?? 0);
        $facility_id = (int)($_POST['facility_id'] ?? 0);
        $match_date = $_POST['match_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $volunteer_id = (int)($_POST['volunteer_id'] ?? 0);

        if ($game_id && $team1_id && $team2_id && $match_date && $start_time) {
            $stmt = $pdo->prepare("
                INSERT INTO matches (game_id, round, pool_name, team1_id, team2_id, facility_id, match_date, start_time, end_time, volunteer_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')
            ");
            $stmt->execute([$game_id, $round, $pool_name ?: null, $team1_id, $team2_id, $facility_id ?: null, $match_date, $start_time, $end_time, $volunteer_id ?: null]);
            $newMatchId = $pdo->lastInsertId();

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'MATCH_SCHEDULED', "Scheduled Match #$newMatchId ($round on $match_date $start_time)");
            $_SESSION['flash_msg'] = "Match successfully scheduled.";
            header("Location: " . BASE_URL . "/admin/schedules");
            exit;
        } else {
            $err = "Game, both teams, date, and start time are required.";
        }
    } elseif ($action === 'reschedule') {
        $id = (int)($_POST['id'] ?? 0);
        $match_date = $_POST['match_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $facility_id = (int)($_POST['facility_id'] ?? 0);

        if ($id > 0 && $match_date && $start_time) {
            $stmt = $pdo->prepare("UPDATE matches SET match_date = ?, start_time = ?, end_time = ?, facility_id = ? WHERE id = ?");
            $stmt->execute([$match_date, $start_time, $end_time, $facility_id ?: null, $id]);

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'MATCH_RESCHEDULED', "Rescheduled Match #$id to $match_date $start_time");
            $_SESSION['flash_msg'] = "Match #$id rescheduled successfully. Downstream dashboards updated.";
            header("Location: " . BASE_URL . "/admin/schedules");
            exit;
        }
    } elseif ($action === 'delete_match') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
            $stmt->execute([$id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'MATCH_DELETE', "Deleted Match #$id from schedule");
            $_SESSION['flash_msg'] = "Match #$id removed from schedule.";
            header("Location: " . BASE_URL . "/admin/schedules");
            exit;
        }
    }
}

// Handle GET delete for matches
if (isset($_GET['delete_match'])) {
    $id = (int)$_GET['delete_match'];
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM matches WHERE id = ?");
        $stmt->execute([$id]);
        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'MATCH_DELETE', "Deleted Match #$id from schedule");
        $_SESSION['flash_msg'] = "Match #$id removed from schedule.";
        header("Location: " . BASE_URL . "/admin/schedules");
        exit;
    }
}

// Clash Detection (FR-12)
$clashes = detect_player_clashes($pdo);

// Filters
$filterDay = $_GET['day'] ?? 'all';
$filterGame = (int)($_GET['game_id'] ?? 0);

$query = "
    SELECT m.*, 
           g.name as game_name, g.category,
           t1.name as team1_name, u1.short_code as u1_code, u1.color_code as u1_color,
           t2.name as team2_name, u2.short_code as u2_code, u2.color_code as u2_color,
           tw.name as winner_name,
           f.name as facility_name, f.court_number,
           u_vol.username as volunteer_name
    FROM matches m
    JOIN games g ON m.game_id = g.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    LEFT JOIN teams tw ON m.winner_id = tw.id
    LEFT JOIN facilities f ON m.facility_id = f.id
    LEFT JOIN users u_vol ON m.volunteer_id = u_vol.id
    WHERE 1=1
";
$params = [];
if ($filterDay !== 'all') {
    $dateMap = ['day1' => '2026-10-08', 'day2' => '2026-10-09', 'day3' => '2026-10-10'];
    if (isset($dateMap[$filterDay])) {
        $query .= " AND m.match_date = ?";
        $params[] = $dateMap[$filterDay];
    }
}
if ($filterGame > 0) {
    $query .= " AND m.game_id = ?";
    $params[] = $filterGame;
}
$query .= " ORDER BY m.match_date ASC, m.start_time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$matches = $stmt->fetchAll();

$allGames = $pdo->query("SELECT id, name FROM games ORDER BY name ASC")->fetchAll();
$allFacilities = $pdo->query("SELECT id, name FROM facilities ORDER BY name ASC")->fetchAll();
$allTeams = $pdo->query("SELECT t.id, t.name, g.name as game_name, u.short_code FROM teams t JOIN games g ON t.game_id = g.id JOIN units u ON t.unit_id = u.id ORDER BY g.name ASC, u.short_code ASC")->fetchAll();
$allVolunteers = $pdo->query("SELECT id, username, full_name FROM users WHERE role = 'volunteer' ORDER BY full_name ASC")->fetchAll();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-calendar-alt text-primary mr-2"></i> Master Schedules & Clash Detection</h1>
        <p class="text-muted mb-0">Multi-facility 3-day tournament scheduling with automated conflict detection</p>
      </div>
      <div class="col-sm-6 text-right">
        <button class="btn btn-primary" data-toggle="modal" data-target="#scheduleMatchModal">
          <i class="fas fa-plus mr-1"></i> Schedule Fixture
        </button>
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

    <!-- PLAYER CLASH DETECTION BANNER -->
    <?php if (!empty($clashes)): ?>
      <div class="card card-danger card-outline mb-4">
        <div class="card-header bg-danger text-white">
          <h3 class="card-title font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i> Attention: <?= count($clashes) ?> Schedule Conflicts Detected!
          </h3>
        </div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-sm table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th>Athlete / Unit</th>
                <th>Conflict 1 (Game & Slot)</th>
                <th>Conflict 2 (Game & Slot)</th>
                <th>Facility Overlap</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($clashes as $c): ?>
                <tr>
                  <td>
                    <strong class="text-danger"><?= htmlspecialchars($c['player_name']) ?></strong>
                    <span class="badge badge-secondary ml-1"><?= htmlspecialchars($c['unit_code']) ?></span>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($c['game1_name']) ?></strong> (<?= htmlspecialchars($c['m1_round']) ?>)<br>
                    <small class="text-muted"><?= $c['m1_date'] ?> <?= $c['m1_start'] ?> - <?= $c['m1_end'] ?></small>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($c['game2_name']) ?></strong> (<?= htmlspecialchars($c['m2_round']) ?>)<br>
                    <small class="text-muted"><?= $c['m2_date'] ?> <?= $c['m2_start'] ?> - <?= $c['m2_end'] ?></small>
                  </td>
                  <td><?= htmlspecialchars($c['f1_name']) ?> vs <?= htmlspecialchars($c['f2_name']) ?></td>
                  <td>
                    <button class="btn btn-xs btn-outline-danger" onclick="openRescheduleModal(<?= $c['m2_id'] ?>, '<?= $c['m2_date'] ?>', '<?= $c['m2_start'] ?>', '<?= $c['m2_end'] ?>')">
                      <i class="fas fa-clock mr-1"></i> Reschedule Match #<?= $c['m2_id'] ?>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-success bg-gradient-success text-white mb-4">
        <i class="fas fa-shield-alt mr-2"></i> <strong>Zero Player Clashes:</strong> All athlete rosters across 7 disciplines are free of overlapping match slots.
      </div>
    <?php endif; ?>

    <!-- Filter Buttons -->
    <div class="card card-outline card-primary mb-3">
      <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center">
        <div class="btn-group mb-2 mb-md-0">
          <a href="?day=all&game_id=<?= $filterGame ?>" class="btn btn-sm <?= $filterDay === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All 3 Days</a>
          <a href="?day=day1&game_id=<?= $filterGame ?>" class="btn btn-sm <?= $filterDay === 'day1' ? 'btn-primary' : 'btn-outline-primary' ?>">Day 1 (8 Oct)</a>
          <a href="?day=day2&game_id=<?= $filterGame ?>" class="btn btn-sm <?= $filterDay === 'day2' ? 'btn-primary' : 'btn-outline-primary' ?>">Day 2 (9 Oct)</a>
          <a href="?day=day3&game_id=<?= $filterGame ?>" class="btn btn-sm <?= $filterDay === 'day3' ? 'btn-primary' : 'btn-outline-primary' ?>">Day 3 (10 Oct)</a>
        </div>

        <form method="GET" class="form-inline">
          <input type="hidden" name="day" value="<?= htmlspecialchars($filterDay) ?>">
          <select name="game_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="0">-- All Games --</option>
            <?php foreach ($allGames as $g): ?>
              <option value="<?= $g['id'] ?>" <?= $filterGame == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>
    </div>

    <!-- Match Schedule Table -->
    <div class="card elevation-2">
      <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="thead-dark">
            <tr>
              <th>Match ID</th>
              <th>Date & Time Slot</th>
              <th>Sport & Round</th>
              <th>Teams / Units Pairing</th>
              <th>Facility / Court</th>
              <th>Status</th>
              <th>Volunteer</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($matches)): ?>
              <tr><td colspan="8" class="text-center py-4 text-muted">No scheduled matches found for this filter.</td></tr>
            <?php else: ?>
              <?php foreach ($matches as $m): ?>
                <tr>
                  <td><strong>#<?= $m['id'] ?></strong></td>
                  <td>
                    <strong><?= date('D, d M', strtotime($m['match_date'])) ?></strong><br>
                    <span class="badge badge-info"><?= date('H:i', strtotime($m['start_time'])) ?> – <?= date('H:i', strtotime($m['end_time'])) ?></span>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($m['game_name']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($m['round']) ?> <?= $m['pool_name'] ? "({$m['pool_name']})" : '' ?></small>
                  </td>
                  <td>
                    <?php if (stripos($m['game_name'], 'Swimming') !== false): 
                        $scoresMeta = json_decode($m['scores_json'] ?? '{}', true);
                        $participantsText = $scoresMeta['participants'] ?? 'Multi-lane Heat';
                    ?>
                      <div class="d-flex align-items-center">
                        <span class="badge badge-info mr-1"><i class="fas fa-users mr-1"></i> <?= htmlspecialchars($participantsText) ?></span>
                        <span class="badge badge-secondary"><?= htmlspecialchars($m['pool_name'] ?: 'Heats') ?></span>
                      </div>
                    <?php else: ?>
                      <div class="d-flex align-items-center">
                        <span class="badge mr-1" style="background-color: <?= $m['u1_color'] ?>; color: #fff;"><?= htmlspecialchars($m['u1_code']) ?></span>
                        <span><?= htmlspecialchars($m['team1_name'] ?: 'TBD') ?></span>
                        <strong class="mx-2 text-muted">vs</strong>
                        <span class="badge mr-1" style="background-color: <?= $m['u2_color'] ?>; color: #fff;"><?= htmlspecialchars($m['u2_code']) ?></span>
                        <span><?= htmlspecialchars($m['team2_name'] ?: 'TBD') ?></span>
                      </div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <i class="fas fa-map-marker-alt text-muted mr-1"></i>
                    <?= htmlspecialchars($m['facility_name'] ?: 'Unassigned Court') ?>
                  </td>
                  <td>
                    <?php if ($m['status'] === 'completed'): ?>
                      <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Completed</span>
                    <?php elseif ($m['status'] === 'in_progress'): ?>
                      <span class="badge badge-danger"><i class="fas fa-broadcast-tower mr-1"></i> Live</span>
                    <?php else: ?>
                      <span class="badge badge-secondary">Scheduled</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <small class="text-muted"><?= htmlspecialchars($m['volunteer_name'] ?: 'Auto Assign') ?></small>
                  </td>
                  <td class="text-right">
                    <button class="btn btn-sm btn-outline-primary" onclick="openRescheduleModal(<?= $m['id'] ?>, '<?= $m['match_date'] ?>', '<?= $m['start_time'] ?>', '<?= $m['end_time'] ?>', <?= $m['facility_id'] ?: 0 ?>)">
                      <i class="fas fa-calendar-edit"></i> Reschedule
                    </button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Remove match from schedule?');">
                      <input type="hidden" name="action" value="delete_match">
                      <input type="hidden" name="id" value="<?= $m['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- Schedule Modal -->
<div class="modal fade" id="scheduleMatchModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="create_match">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-plus text-primary mr-2"></i> Schedule Tournament Match</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Game / Discipline <span class="text-danger">*</span></label>
              <select name="game_id" class="form-control" required>
                <?php foreach ($allGames as $g): ?>
                  <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>Round Name <span class="text-danger">*</span></label>
              <input type="text" name="round" class="form-control" placeholder="e.g. Pool A - Round 2, Semifinal 1" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Pool (Optional)</label>
              <select name="pool_name" class="form-control">
                <option value="">None / Knockout</option>
                <option value="A">Pool A</option>
                <option value="B">Pool B</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Team 1 <span class="text-danger">*</span></label>
              <select name="team1_id" class="form-control" required>
                <?php foreach ($allTeams as $tm): ?>
                  <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['short_code']) ?>: <?= htmlspecialchars($tm['name']) ?> (<?= htmlspecialchars($tm['game_name']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 form-group">
              <label>Team 2 <span class="text-danger">*</span></label>
              <select name="team2_id" class="form-control" required>
                <?php foreach ($allTeams as $tm): ?>
                  <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['short_code']) ?>: <?= htmlspecialchars($tm['name']) ?> (<?= htmlspecialchars($tm['game_name']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 form-group">
              <label>Facility / Court <span class="text-danger">*</span></label>
              <select name="facility_id" class="form-control" required>
                <?php foreach ($allFacilities as $f): ?>
                  <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4 form-group">
              <label>Date <span class="text-danger">*</span></label>
              <input type="date" name="match_date" class="form-control" value="2026-10-08" required>
            </div>
            <div class="col-md-2 form-group">
              <label>Start Time</label>
              <input type="time" name="start_time" class="form-control" value="11:30" required>
            </div>
            <div class="col-md-2 form-group">
              <label>End Time</label>
              <input type="time" name="end_time" class="form-control" value="12:30" required>
            </div>
          </div>
          <div class="form-group">
            <label>Assigned Volunteer (Scorer)</label>
            <select name="volunteer_id" class="form-control">
              <option value="">-- Auto Assign Volunteer --</option>
              <?php foreach ($allVolunteers as $v): ?>
                <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['full_name']) ?> (<?= htmlspecialchars($v['username']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Schedule Fixture</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="reschedule">
        <input type="hidden" name="id" id="reschedule_id">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-clock text-warning mr-2"></i> Reschedule Match</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>New Date</label>
            <input type="date" name="match_date" id="reschedule_date" class="form-control" required>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Start Time</label>
              <input type="time" name="start_time" id="reschedule_start" class="form-control" required>
            </div>
            <div class="col-md-6 form-group">
              <label>End Time</label>
              <input type="time" name="end_time" id="reschedule_end" class="form-control" required>
            </div>
          </div>
          <div class="form-group">
            <label>Facility / Court</label>
            <select name="facility_id" id="reschedule_facility" class="form-control">
              <?php foreach ($allFacilities as $f): ?>
                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning font-weight-bold">Confirm Reschedule</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openRescheduleModal(id, date, start, end, facId) {
  document.getElementById('reschedule_id').value = id;
  document.getElementById('reschedule_date').value = date;
  document.getElementById('reschedule_start').value = start;
  document.getElementById('reschedule_end').value = end;
  if (facId) document.getElementById('reschedule_facility').value = facId;
  $('#rescheduleModal').modal('show');
}
</script>
