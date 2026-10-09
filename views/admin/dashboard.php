<?php
require_once 'config/helpers.php';

// Real counts from DB
$totalGames = $pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
$totalUnits = $pdo->query("SELECT COUNT(*) FROM units")->fetchColumn();
$totalPlayers = $pdo->query("SELECT COUNT(*) FROM players")->fetchColumn();
$totalMatches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$completedMatches = $pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'completed'")->fetchColumn();
$inProgressMatches = $pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'in_progress'")->fetchColumn();
$completionPct = $totalMatches > 0 ? round(($completedMatches / $totalMatches) * 100) : 0;

$leaderboard = get_overall_championship_leaderboard($pdo);
$topUnit = (!empty($leaderboard) && !empty($leaderboard[0]['rank'])) ? $leaderboard[0] : null;

$clashes = detect_player_clashes($pdo);
$clashCount = count($clashes);

// Game completion progress data for Chart.js
$gameProgress = $pdo->query("
    SELECT g.name, 
           COUNT(m.id) as total_m, 
           SUM(CASE WHEN m.status = 'completed' THEN 1 ELSE 0 END) as completed_m
    FROM games g
    LEFT JOIN matches m ON g.id = m.game_id
    GROUP BY g.id, g.name
    ORDER BY g.id ASC
")->fetchAll();

$chartGameLabels = [];
$chartGameCompleted = [];
$chartGamePending = [];
foreach ($gameProgress as $gp) {
    $chartGameLabels[] = $gp['name'];
    $chartGameCompleted[] = (int)$gp['completed_m'];
    $chartGamePending[] = (int)($gp['total_m'] - $gp['completed_m']);
}

// Points leaderboard data for Chart.js
$chartUnitLabels = [];
$chartUnitPoints = [];
$chartUnitColors = [];
foreach (array_slice($leaderboard, 0, 8) as $lb) {
    $chartUnitLabels[] = $lb['short_code'];
    $chartUnitPoints[] = $lb['total_points'];
    $chartUnitColors[] = $lb['color_code'];
}

// Recent Audit Logs
$recentAudits = $pdo->query("
    SELECT a.*, u.username 
    FROM audit_logs a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 6
")->fetchAll();
?>

<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold">
          <i class="fas fa-tachometer-alt text-primary mr-2"></i> Tournament Operations Dashboard
        </h1>
        <p class="text-muted mb-0">HPCL 20th All India Inter Unit Sports & Games | Balewadi, Pune</p>
      </div>
      <div class="col-sm-6 text-right">
        <span class="badge badge-success p-2 mr-2">
          <i class="fas fa-satellite-dish mr-1"></i> Live Scoring Online
        </span>
        <a href="<?= BASE_URL ?>/admin/schedules" class="btn btn-outline-danger btn-sm">
          <i class="fas fa-exclamation-triangle mr-1"></i> <?= $clashCount ?> Clash Alerts
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Main content -->
<div class="content">
  <div class="container-fluid">
    
    <!-- Stat Cards Row -->
    <div class="row">
      <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info elevation-2">
          <div class="inner">
            <h3><?= $totalGames ?></h3>
            <p>Games (9 Disciplines)</p>
          </div>
          <div class="icon"><i class="fas fa-medal"></i></div>
          <a href="<?= BASE_URL ?>/admin/games" class="small-box-footer">Manage Games <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success elevation-2">
          <div class="inner">
            <h3><?= $completionPct ?><sup style="font-size: 20px">%</sup></h3>
            <p><?= $completedMatches ?> of <?= $totalMatches ?> Matches Played</p>
          </div>
          <div class="icon"><i class="fas fa-check-double"></i></div>
          <a href="<?= BASE_URL ?>/admin/scores" class="small-box-footer">View Scores & Results <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning elevation-2">
          <div class="inner">
            <h3><?= $topUnit ? htmlspecialchars($topUnit['short_code']) : '—' ?> <small style="font-size: 16px;"><?= $topUnit ? '(' . $topUnit['total_points'] . ' Pts)' : '(Pending Results)' ?></small></h3>
            <p>Overall Trophy Leader</p>
          </div>
          <div class="icon"><i class="fas fa-trophy"></i></div>
          <a href="<?= BASE_URL ?>/admin/points" class="small-box-footer">Leaderboard & Points <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-6">
        <div class="small-box <?= $clashCount > 0 ? 'bg-gradient-danger' : 'bg-gradient-secondary' ?> elevation-2">
          <div class="inner">
            <h3><?= $clashCount ?></h3>
            <p>Schedule Clashes</p>
          </div>
          <div class="icon"><i class="fas fa-calendar-times"></i></div>
          <a href="<?= BASE_URL ?>/admin/schedules" class="small-box-footer">Resolve Clashes <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
      <!-- Chart 1: Game Progress -->
      <div class="col-lg-7">
        <div class="card elevation-2">
          <div class="card-header border-0 bg-light d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-chart-bar text-primary mr-2"></i> Match Completion by Discipline
            </h3>
            <span class="badge badge-info"><?= $completedMatches ?> Completed</span>
          </div>
          <div class="card-body">
            <canvas id="progressChart" height="150"></canvas>
          </div>
        </div>
      </div>

      <!-- Chart 2: Overall Points -->
      <div class="col-lg-5">
        <div class="card elevation-2">
          <div class="card-header border-0 bg-light d-flex justify-content-between align-items-center">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-trophy text-warning mr-2"></i> Championship Points (5-3-1)
            </h3>
            <span class="badge badge-warning text-dark">Section 7 Rule</span>
          </div>
          <div class="card-body">
            <canvas id="pointsChart" height="210"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Row: Recent Audit Trail + Quick Actions -->
    <div class="row">
      <div class="col-lg-8">
        <div class="card elevation-2">
          <div class="card-header border-0 bg-light">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-history text-secondary mr-2"></i> Live Audit Trail & Log Activity
            </h3>
            <div class="card-tools">
              <a href="<?= BASE_URL ?>/admin/audit" class="btn btn-tool text-primary">View All Logs</a>
            </div>
          </div>
          <div class="card-body p-0">
            <table class="table table-striped table-valign-middle mb-0">
              <thead>
                <tr>
                  <th>Timestamp</th>
                  <th>Action</th>
                  <th>Operator</th>
                  <th>Details</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentAudits)): ?>
                  <tr><td colspan="4" class="text-center text-muted">No recent audit logs.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentAudits as $log): ?>
                    <tr>
                      <td class="small text-muted"><?= date('d M H:i', strtotime($log['created_at'])) ?></td>
                      <td>
                        <span class="badge badge-<?= strpos($log['action'], 'EDIT') !== false ? 'danger' : 'info' ?>">
                          <?= htmlspecialchars($log['action']) ?>
                        </span>
                      </td>
                      <td class="font-weight-bold"><?= htmlspecialchars($log['username']) ?></td>
                      <td class="small"><?= htmlspecialchars($log['details']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card elevation-2">
          <div class="card-header border-0 bg-light">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-bolt text-warning mr-2"></i> Quick Operations
            </h3>
          </div>
          <div class="card-body">
            <?php if (($role = $_SESSION['role'] ?? '') === 'nodal'): ?>
              <a href="<?= BASE_URL ?>/admin/teams" class="btn btn-outline-primary btn-block text-left mb-2 font-weight-bold">
                <i class="fas fa-users mr-2 text-warning"></i> Manage Teams & Athlete Rosters
              </a>
              <a href="<?= BASE_URL ?>" class="btn btn-outline-info btn-block text-left mb-2 font-weight-bold" target="_blank">
                <i class="fas fa-external-link-alt mr-2"></i> View Live Public Dashboard
              </a>
              <a href="<?= BASE_URL ?>/social" class="btn btn-outline-success btn-block text-left mb-2 font-weight-bold" target="_blank">
                <i class="fas fa-magic mr-2"></i> Scorecard Studio
              </a>
            <?php else: ?>
              <a href="<?= BASE_URL ?>/admin/draws" class="btn btn-outline-primary btn-block text-left mb-2">
                <i class="fas fa-sitemap mr-2"></i> Execute TMM Seeded Draw
              </a>
              <a href="<?= BASE_URL ?>/admin/schedules" class="btn btn-outline-danger btn-block text-left mb-2">
                <i class="fas fa-calendar-alt mr-2"></i> Master Schedule & Clash Check
              </a>
              <a href="<?= BASE_URL ?>/admin/scores" class="btn btn-outline-success btn-block text-left mb-2">
                <i class="fas fa-edit mr-2"></i> Admin Score Override
              </a>
              <a href="<?= BASE_URL ?>/admin/photos" class="btn btn-outline-info btn-block text-left mb-2">
                <i class="fas fa-images mr-2"></i> Photographer Slot Folders
              </a>
              <a href="<?= BASE_URL ?>/admin/export" class="btn btn-outline-secondary btn-block text-left mb-2">
                <i class="fas fa-file-export mr-2"></i> Export Data (PDF & Excel)
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Chart 1: Game Progress
  var ctxProgress = document.getElementById('progressChart').getContext('2d');
  new Chart(ctxProgress, {
    type: 'bar',
    data: {
      labels: <?= json_encode($chartGameLabels) ?>,
      datasets: [
        {
          label: 'Completed Matches',
          backgroundColor: '#28a745',
          data: <?= json_encode($chartGameCompleted) ?>
        },
        {
          label: 'Pending / Scheduled',
          backgroundColor: '#e9ecef',
          data: <?= json_encode($chartGamePending) ?>
        }
      ]
    },
    options: {
      responsive: true,
      scales: {
        x: { stacked: true },
        y: { stacked: true, beginAtZero: true }
      }
    }
  });

  // Chart 2: Points Distribution
  var ctxPoints = document.getElementById('pointsChart').getContext('2d');
  new Chart(ctxPoints, {
    type: 'doughnut',
    data: {
      labels: <?= json_encode($chartUnitLabels) ?>,
      datasets: [{
        data: <?= json_encode($chartUnitPoints) ?>,
        backgroundColor: ['#0d6efd', '#dc3545', '#6f42c1', '#198754', '#fd7e14', '#20c997', '#0dcaf0', '#ffc107']
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });
});
</script>
