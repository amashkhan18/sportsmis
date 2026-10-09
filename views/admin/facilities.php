<?php
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

// Handle Add / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $court_number = trim($_POST['court_number'] ?? '');

        if ($name && $type) {
            $stmt = $pdo->prepare("INSERT INTO facilities (name, type, location, court_number) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $type, $location, $court_number]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'FACILITY_CREATE', "Created facility: $name ($type)");
            $_SESSION['flash_msg'] = "Facility '$name' successfully added.";
            header("Location: " . BASE_URL . "/admin/facilities");
            exit;
        } else {
            $err = "Facility name and type are required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE matches SET facility_id = NULL WHERE facility_id = ?")->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM facilities WHERE id = ?");
            $stmt->execute([$id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'FACILITY_DELETE', "Deleted facility ID #$id");
            $_SESSION['flash_msg'] = "Facility deleted.";
            header("Location: " . BASE_URL . "/admin/facilities");
            exit;
        }
    }
}

// Handle GET delete for facilities
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $pdo->prepare("UPDATE matches SET facility_id = NULL WHERE facility_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM facilities WHERE id = ?");
        $stmt->execute([$id]);
        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'FACILITY_DELETE', "Deleted facility ID #$id");
        $_SESSION['flash_msg'] = "Facility deleted.";
        header("Location: " . BASE_URL . "/admin/facilities");
        exit;
    }
}

// Fetch all facilities with scheduled slot counts
$facilities = $pdo->query("
    SELECT f.*, COUNT(m.id) as scheduled_matches
    FROM facilities f
    LEFT JOIN matches m ON f.id = m.facility_id
    GROUP BY f.id
    ORDER BY f.type ASC, f.name ASC
")->fetchAll();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Facilities & Schedulable Slots</h1>
        <p class="text-muted mb-0">Manage Balewadi Sports Complex venues: courts, tables, boards, and lanes</p>
      </div>
      <div class="col-sm-6 text-right">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addFacilityModal">
          <i class="fas fa-plus mr-1"></i> Add New Facility Slot
        </button>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <?php if ($msg): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($err) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <div class="card elevation-2">
      <div class="card-header bg-light">
        <h3 class="card-title font-weight-bold">Configured Venues & Courts</h3>
        <div class="card-tools">
          <span class="badge badge-primary"><?= count($facilities) ?> Facilities Active</span>
        </div>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="thead-dark">
            <tr>
              <th>ID</th>
              <th>Facility Name</th>
              <th>Type</th>
              <th>Complex Location</th>
              <th>Court / Table / Lane #</th>
              <th>Assigned Slots</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($facilities as $f): ?>
              <tr>
                <td><strong>#<?= $f['id'] ?></strong></td>
                <td>
                  <i class="fas fa-<?= $f['type'] === 'pool' ? 'swimmer' : ($f['type'] === 'court' ? 'baseball-ball' : ($f['type'] === 'table' ? 'table-tennis' : 'chess-board')) ?> mr-2 text-muted"></i>
                  <strong><?= htmlspecialchars($f['name']) ?></strong>
                </td>
                <td>
                  <span class="badge badge-info text-uppercase"><?= htmlspecialchars($f['type']) ?></span>
                </td>
                <td><?= htmlspecialchars($f['location']) ?></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($f['court_number'] ?: 'Standard') ?></span></td>
                <td>
                  <span class="badge badge-<?= $f['scheduled_matches'] > 0 ? 'success' : 'light' ?>">
                    <?= $f['scheduled_matches'] ?> matches scheduled
                  </span>
                </td>
                <td class="text-right">
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this facility? Any assigned matches will lose their slot.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
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

<!-- Add Facility Modal -->
<div class="modal fade" id="addFacilityModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-plus mr-2 text-primary"></i> Add Facility / Schedulable Slot</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Facility Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Badminton Hall - Court 4" required>
          </div>
          <div class="form-group">
            <label>Venue Type <span class="text-danger">*</span></label>
            <select name="type" class="form-control" required>
              <option value="court">Court (Badminton, Tennis)</option>
              <option value="table">Table (Table Tennis, Bridge)</option>
              <option value="board">Board (Chess, Carrom)</option>
              <option value="lane">Lane (Swimming)</option>
              <option value="pool">Pool (Swimming Complex)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Location / Hall in Complex</label>
            <input type="text" name="location" class="form-control" placeholder="e.g. Balewadi Indoor Stadium Hall A" required>
          </div>
          <div class="form-group">
            <label>Court / Table / Lane Number</label>
            <input type="text" name="court_number" class="form-control" placeholder="e.g. 4, Lane 2, Table 3">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Save Facility</button>
        </div>
      </form>
    </div>
  </div>
</div>
