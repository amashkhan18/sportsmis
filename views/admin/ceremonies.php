<?php
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $event_name = trim($_POST['event_name'] ?? '');
        $event_type = trim($_POST['event_type'] ?? 'ceremony');
        $date = $_POST['date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($event_name && $date && $start_time) {
            $stmt = $pdo->prepare("INSERT INTO master_events (event_name, event_type, date, start_time, end_time, location, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$event_name, $event_type, $date, $start_time, $end_time, $location, $description]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'EVENT_CREATE', "Added master ceremony: $event_name on $date");
            $_SESSION['flash_msg'] = "Master event '$event_name' scheduled.";
            header("Location: " . BASE_URL . "/admin/ceremonies");
            exit;
        } else {
            $err = "Event title, date, and start time are required.";
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM master_events WHERE id = ?");
            $stmt->execute([$id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'EVENT_DELETE', "Deleted master event #$id");
            $_SESSION['flash_msg'] = "Event removed from master schedule.";
            header("Location: " . BASE_URL . "/admin/ceremonies");
            exit;
        }
    }
}

// Handle GET delete for ceremonies
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM master_events WHERE id = ?");
        $stmt->execute([$id]);
        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'EVENT_DELETE', "Deleted master event #$id");
        $_SESSION['flash_msg'] = "Event removed from master schedule.";
        header("Location: " . BASE_URL . "/admin/ceremonies");
        exit;
    }
}

// Fetch all master events
$events = $pdo->query("SELECT * FROM master_events ORDER BY date ASC, start_time ASC")->fetchAll();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-flag-checkered text-primary mr-2"></i> TMM & Ceremonies</h1>
        <p class="text-muted mb-0">Team Managers' Meeting, Opening/Closing Ceremonies on Master Schedule</p>
      </div>
      <div class="col-sm-6 text-right">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addEventModal">
          <i class="fas fa-plus mr-1"></i> Add Ceremony / Meeting
        </button>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <?php if ($msg): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check mr-2"></i> <?= htmlspecialchars($msg) ?>
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
      <div class="card-header bg-light">
        <h3 class="card-title font-weight-bold">Scheduled Master Events (8–10 October 2026)</h3>
      </div>
      <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="thead-dark">
            <tr>
              <th>Date</th>
              <th>Time</th>
              <th>Event Title</th>
              <th>Type</th>
              <th>Venue / Hall</th>
              <th>Description / Protocol</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($events as $ev): ?>
              <tr>
                <td><strong><?= date('D, d M Y', strtotime($ev['date'])) ?></strong></td>
                <td>
                  <span class="badge badge-primary">
                    <?= date('h:i A', strtotime($ev['start_time'])) ?> – <?= date('h:i A', strtotime($ev['end_time'])) ?>
                  </span>
                </td>
                <td><strong class="text-dark"><?= htmlspecialchars($ev['event_name']) ?></strong></td>
                <td>
                  <span class="badge badge-<?= $ev['event_type'] === 'meeting' ? 'warning text-dark' : ($ev['event_type'] === 'dinner' ? 'success' : 'info') ?> text-uppercase">
                    <?= htmlspecialchars($ev['event_type']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($ev['location']) ?></td>
                <td class="small text-muted"><?= htmlspecialchars($ev['description']) ?></td>
                <td class="text-right">
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this ceremony from the master schedule?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $ev['id'] ?>">
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
<div class="modal fade" id="addEventModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="create">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-plus text-primary mr-2"></i> Schedule Master Event</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Event Title <span class="text-danger">*</span></label>
            <input type="text" name="event_name" class="form-control" placeholder="e.g. Team Managers Meeting (TMM)" required>
          </div>
          <div class="form-group">
            <label>Event Type</label>
            <select name="event_type" class="form-control">
              <option value="meeting">Meeting (TMM, Committee)</option>
              <option value="ceremony">Ceremony (Opening, Closing)</option>
              <option value="dinner">Gala Dinner / Fellowship</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 form-group">
              <label>Date <span class="text-danger">*</span></label>
              <input type="date" name="date" class="form-control" value="2026-10-08" required>
            </div>
            <div class="col-md-3 form-group">
              <label>Start</label>
              <input type="time" name="start_time" class="form-control" value="08:30" required>
            </div>
            <div class="col-md-3 form-group">
              <label>End</label>
              <input type="time" name="end_time" class="form-control" value="09:30" required>
            </div>
          </div>
          <div class="form-group">
            <label>Location / Hall</label>
            <input type="text" name="location" class="form-control" placeholder="e.g. Conference Hall 1, Balewadi" required>
          </div>
          <div class="form-group">
            <label>Description / Notes</label>
            <textarea name="description" class="form-control" rows="2" placeholder="Rules briefing, agenda, protocol..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary font-weight-bold">Schedule Event</button>
        </div>
      </form>
    </div>
  </div>
</div>
