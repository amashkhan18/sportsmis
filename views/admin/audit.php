<?php
require_once 'config/helpers.php';

$filterAction = $_GET['action_filter'] ?? '';

$sql = "
    SELECT a.*, u.username, u.role, u.full_name
    FROM audit_logs a
    JOIN users u ON a.user_id = u.id
    WHERE 1=1
";
$params = [];
if ($filterAction) {
    $sql .= " AND a.action = ?";
    $params[] = $filterAction;
}
$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

$actionTypes = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-clipboard-list text-primary mr-2"></i> Audit Trail & Activity Logs</h1>
        <p class="text-muted mb-0">Immutable records of score entries, admin edits, draw generations, and publish events</p>
      </div>
      <div class="col-sm-6 text-right">
        <span class="badge badge-success p-2">
          <i class="fas fa-fingerprint mr-1"></i> <?= count($logs) ?> Recorded Audit Events
        </span>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <!-- Filter Card -->
    <div class="card card-outline card-primary mb-3">
      <div class="card-body p-3">
        <form method="GET" class="form-inline">
          <label class="mr-2">Filter by Action:</label>
          <select name="action_filter" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
            <option value="">-- All Actions --</option>
            <?php foreach ($actionTypes as $at): ?>
              <option value="<?= htmlspecialchars($at) ?>" <?= $filterAction === $at ? 'selected' : '' ?>><?= htmlspecialchars($at) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if ($filterAction): ?>
            <a href="<?= BASE_URL ?>/admin/audit" class="btn btn-sm btn-outline-secondary">Reset</a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <!-- Audit Table -->
    <div class="card elevation-2">
      <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
          <thead class="thead-dark">
            <tr>
              <th>Timestamp</th>
              <th>Action Key</th>
              <th>User / Operator</th>
              <th>Role</th>
              <th>IP Address</th>
              <th>Event Details & Diff</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr><td colspan="6" class="text-center py-4 text-muted">No audit logs found.</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $l): ?>
                <tr>
                  <td class="small font-weight-bold text-muted" style="white-space: nowrap;">
                    <i class="far fa-clock mr-1"></i> <?= date('d M Y, H:i:s', strtotime($l['created_at'])) ?>
                  </td>
                  <td>
                    <?php 
                      $badgeClass = 'secondary';
                      if (strpos($l['action'], 'EDIT') !== false) $badgeClass = 'danger';
                      elseif (strpos($l['action'], 'CREATE') !== false || strpos($l['action'], 'ADD') !== false) $badgeClass = 'success';
                      elseif (strpos($l['action'], 'SYNC') !== false || strpos($l['action'], 'SCORE') !== false) $badgeClass = 'info';
                      elseif (strpos($l['action'], 'DRAW') !== false) $badgeClass = 'warning text-dark';
                    ?>
                    <span class="badge badge-<?= $badgeClass ?>"><?= htmlspecialchars($l['action']) ?></span>
                  </td>
                  <td>
                    <strong><?= htmlspecialchars($l['full_name']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($l['username']) ?></small>
                  </td>
                  <td><span class="badge badge-light text-uppercase"><?= htmlspecialchars($l['role']) ?></span></td>
                  <td><code><?= htmlspecialchars($l['ip_address']) ?></code></td>
                  <td>
                    <div class="small" style="max-width: 500px; word-break: break-word;">
                      <?= htmlspecialchars($l['details']) ?>
                    </div>
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
