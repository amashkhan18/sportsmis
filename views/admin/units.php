<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

// Handle POST actions: Add Unit, Enter Unit into All Games, Delete Unit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $short_code = strtoupper(trim($_POST['short_code'] ?? ''));
        $color_code = $_POST['color_code'] ?? '#0d6efd';
        $auto_enter_games = isset($_POST['auto_enter_games']) ? 1 : 0;
        
        if ($name && $short_code) {
            $check = $pdo->prepare("SELECT id FROM units WHERE short_code = ?");
            $check->execute([$short_code]);
            if ($check->fetch()) {
                $err = "A unit with short code '$short_code' already exists.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO units (name, short_code, color_code) VALUES (?, ?, ?)");
                $stmt->execute([$name, $short_code, $color_code]);
                $newUId = $pdo->lastInsertId();

                $teamsCreated = 0;
                if ($auto_enter_games) {
                    $games = $pdo->query("SELECT id, name FROM games ORDER BY id ASC")->fetchAll();
                    $insTeam = $pdo->prepare("INSERT INTO teams (unit_id, game_id, name, pool) VALUES (?, ?, ?, ?)");
                    foreach ($games as $idx => $g) {
                        $pool = ($idx % 2 == 0) ? 'A' : 'B';
                        $teamName = "$short_code {$g['name']}";
                        $insTeam->execute([$newUId, $g['id'], $teamName, $pool]);
                        $teamsCreated++;
                    }
                }

                log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'UNIT_CREATE', "Added HPCL Unit: $name ($short_code) with $teamsCreated team entries");
                $_SESSION['flash_msg'] = "Unit '$name' successfully registered" . ($teamsCreated ? " and entered into $teamsCreated tournament disciplines!" : ".");
                header("Location: " . BASE_URL . "/admin/units");
                exit;
            }
        } else {
            $err = "Unit name and short code are required.";
        }
    } elseif ($action === 'enter_all_games') {
        $unit_id = (int)($_POST['unit_id'] ?? 0);
        if ($unit_id > 0) {
            $unit = $pdo->prepare("SELECT name, short_code FROM units WHERE id = ?");
            $unit->execute([$unit_id]);
            $u = $unit->fetch();

            $games = $pdo->query("SELECT id, name FROM games ORDER BY id ASC")->fetchAll();
            $insTeam = $pdo->prepare("INSERT INTO teams (unit_id, game_id, name, pool) VALUES (?, ?, ?, ?)");
            $chkTeam = $pdo->prepare("SELECT id FROM teams WHERE unit_id = ? AND game_id = ?");

            $createdCount = 0;
            foreach ($games as $idx => $g) {
                $chkTeam->execute([$unit_id, $g['id']]);
                if (!$chkTeam->fetch()) {
                    $pool = ($idx % 2 == 0) ? 'A' : 'B';
                    $teamName = "{$u['short_code']} {$g['name']}";
                    $insTeam->execute([$unit_id, $g['id'], $teamName, $pool]);
                    $createdCount++;
                }
            }

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'TEAMS_AUTO_PROVISION', "Entered {$u['name']} into $createdCount games");
            $_SESSION['flash_msg'] = "Successfully entered {$u['name']} into $createdCount tournament disciplines! You can now enroll athletes under this unit.";
            header("Location: " . BASE_URL . "/admin/units");
            exit;
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $short_code = strtoupper(trim($_POST['short_code'] ?? ''));
        $color_code = $_POST['color_code'] ?? '#0d6efd';

        if ($id > 0 && $name && $short_code) {
            $check = $pdo->prepare("SELECT id FROM units WHERE short_code = ? AND id != ?");
            $check->execute([$short_code, $id]);
            if ($check->fetch()) {
                $err = "A unit with short code '$short_code' already exists.";
            } else {
                $oldUnit = $pdo->prepare("SELECT * FROM units WHERE id = ?");
                $oldUnit->execute([$id]);
                $oldU = $oldUnit->fetch();

                $stmt = $pdo->prepare("UPDATE units SET name = ?, short_code = ?, color_code = ? WHERE id = ?");
                $stmt->execute([$name, $short_code, $color_code, $id]);

                // Auto-sync team names if short code changed
                $teamsUpdated = 0;
                if ($oldU && $oldU['short_code'] !== $short_code) {
                    $teams = $pdo->prepare("SELECT t.id, g.name as game_name FROM teams t JOIN games g ON t.game_id = g.id WHERE t.unit_id = ?");
                    $teams->execute([$id]);
                    $updTeam = $pdo->prepare("UPDATE teams SET name = ? WHERE id = ?");
                    while ($tm = $teams->fetch()) {
                        $updTeam->execute(["$short_code {$tm['game_name']}", $tm['id']]);
                        $teamsUpdated++;
                    }
                }

                log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'UNIT_UPDATE', "Updated Unit #$id: $name ($short_code)" . ($teamsUpdated ? " and updated $teamsUpdated team names" : ""));
                $_SESSION['flash_msg'] = "Unit '$name' ($short_code) updated successfully!" . ($teamsUpdated ? " ($teamsUpdated team names synchronized)" : "");
                header("Location: " . BASE_URL . "/admin/units");
                exit;
            }
        } else {
            $err = "Unit name and short code are required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
            $stmt->execute([$id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'UNIT_DELETE', "Deleted Unit ID #$id");
            $_SESSION['flash_msg'] = "Unit ID #$id was deleted.";
            header("Location: " . BASE_URL . "/admin/units");
            exit;
        }
    }
}

// Handle GET delete for units as fallback
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
        $stmt->execute([$id]);
        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'UNIT_DELETE', "Deleted Unit ID #$id");
        $_SESSION['flash_msg'] = "Unit ID #$id was deleted.";
        header("Location: " . BASE_URL . "/admin/units");
        exit;
    }
}

// Fetch Units with team count and athlete count
$units = $pdo->query("
    SELECT u.*, 
           COUNT(DISTINCT t.id) as team_count,
           COUNT(DISTINCT p.id) as player_count
    FROM units u
    LEFT JOIN teams t ON u.id = t.unit_id
    LEFT JOIN players p ON u.id = p.unit_id
    GROUP BY u.id
    ORDER BY u.short_code ASC
")->fetchAll();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-building text-primary mr-2"></i> Participating Units</h1>
        <p class="text-muted mb-0">Pre-load HPCL Refineries, Marketing Zones, Regional Offices, and Plants</p>
      </div>
      <div class="col-sm-6 text-right">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addUnitModal">
          <i class="fas fa-plus mr-1"></i> Register HPCL Unit
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

    <div class="card elevation-2">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">Participating Tournament Units</h3>
        <span class="badge badge-info"><?= count($units) ?> Units Active</span>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="thead-dark">
            <tr>
              <th>ID</th>
              <th>Unit Name</th>
              <th>Short Code</th>
              <th>Identity Color</th>
              <th>Teams Entered</th>
              <th>Athletes Registered</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($units as $u): ?>
              <tr>
                <td><strong>#<?= $u['id'] ?></strong></td>
                <td>
                  <strong class="text-dark"><?= htmlspecialchars($u['name']) ?></strong>
                </td>
                <td>
                  <span class="badge" style="background-color: <?= $u['color_code'] ?>; color: #fff; font-size: 0.9rem;">
                    <?= htmlspecialchars($u['short_code']) ?>
                  </span>
                </td>
                <td>
                  <span class="badge" style="background: <?= $u['color_code'] ?>; color: transparent; width: 30px; height: 18px; display: inline-block;">.</span>
                  <code class="ml-1"><?= htmlspecialchars($u['color_code']) ?></code>
                </td>
                <td>
                  <?php if ($u['team_count'] > 0): ?>
                    <span class="badge badge-primary"><?= $u['team_count'] ?> teams</span>
                  <?php else: ?>
                    <span class="badge badge-warning text-dark mr-1">0 teams entered</span>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="action" value="enter_all_games">
                      <input type="hidden" name="unit_id" value="<?= $u['id'] ?>">
                      <button type="submit" class="btn btn-xs btn-outline-warning font-weight-bold" title="Enter unit into all sports">
                        <i class="fas fa-magic mr-1"></i> Enter All Games
                      </button>
                    </form>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge badge-success"><?= $u['player_count'] ?> athletes</span>
                </td>
                <td class="text-right">
                  <button type="button" class="btn btn-sm btn-outline-secondary edit-unit-btn mr-1" 
                          data-id="<?= $u['id'] ?>" 
                          data-name="<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>" 
                          data-short_code="<?= htmlspecialchars($u['short_code'], ENT_QUOTES) ?>" 
                          data-color_code="<?= htmlspecialchars($u['color_code'], ENT_QUOTES) ?>" 
                          title="Edit Unit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <a href="<?= BASE_URL ?>/admin/teams?tab=teams&unit_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Teams">
                    <i class="fas fa-shield-alt mr-1"></i> Teams
                  </a>
                  <a href="<?= BASE_URL ?>/admin/teams?tab=athletes&unit_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-info" title="View Athletes">
                    <i class="fas fa-users mr-1"></i> Athletes
                  </a>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this unit? All associated teams, rosters, and matches will be affected.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/admin/units" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-building mr-2"></i> Register New HPCL Unit</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Unit Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control font-weight-bold" placeholder="e.g. Bhopal, Visakh Refinery, Central Zone" required>
          </div>
          <div class="form-group">
            <label>Short Code <span class="text-danger">*</span></label>
            <input type="text" name="short_code" class="form-control font-weight-bold" placeholder="e.g. BP, VR, CZ, HQ" required maxlength="10">
          </div>
          <div class="form-group">
            <label>Badge Identity Color</label>
            <input type="color" name="color_code" class="form-control" value="#0d6efd">
          </div>
          <div class="form-check mt-3 p-3 bg-light rounded border">
            <input type="checkbox" name="auto_enter_games" value="1" class="form-check-input" id="autoEnterCheck" checked>
            <label class="form-check-label font-weight-bold text-dark" for="autoEnterCheck">
              <i class="fas fa-magic text-warning mr-1"></i> Automatically enter this unit into all tournament games
            </label>
            <small class="form-text text-muted">
              Creates teams (e.g. BP Badminton, BP Chess, etc.) so you can immediately register athletes without manual setup.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Register Unit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= BASE_URL ?>/admin/units" method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit_unit_id">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i> Edit HPCL Unit</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Unit Name <span class="text-danger">*</span></label>
            <input type="text" name="name" id="edit_unit_name" class="form-control font-weight-bold" required>
          </div>
          <div class="form-group">
            <label>Short Code <span class="text-danger">*</span></label>
            <input type="text" name="short_code" id="edit_unit_short_code" class="form-control font-weight-bold" required maxlength="10">
            <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Changing this will automatically update associated team names across all games.</small>
          </div>
          <div class="form-group">
            <label>Badge Identity Color</label>
            <input type="color" name="color_code" id="edit_unit_color_code" class="form-control" value="#0d6efd">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('.edit-unit-btn').on('click', function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    var code = $(this).data('short_code');
    var color = $(this).data('color_code');

    $('#edit_unit_id').val(id);
    $('#edit_unit_name').val(name);
    $('#edit_unit_short_code').val(code);
    $('#edit_unit_color_code').val(color || '#0d6efd');

    $('#editUnitModal').modal('show');
  });
});
</script>
