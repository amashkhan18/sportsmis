<?php
require_once 'config/helpers.php';

// Check if download parameter is set for CSV export
$download = $_GET['download'] ?? '';

if ($download === 'results_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=hpcl_tournament_results_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Match ID', 'Date', 'Sport / Discipline', 'Round', 'Team 1', 'Team 2', 'Winner', 'Score Summary', 'Facility']);

    $stmt = $pdo->query("
        SELECT m.id, m.match_date, g.name as game_name, m.round,
               t1.name as team1, t2.name as team2, tw.name as winner,
               m.scores_json, f.name as facility_name
        FROM matches m
        JOIN games g ON m.game_id = g.id
        LEFT JOIN teams t1 ON m.team1_id = t1.id
        LEFT JOIN teams t2 ON m.team2_id = t2.id
        LEFT JOIN teams tw ON m.winner_id = tw.id
        LEFT JOIN facilities f ON m.facility_id = f.id
        WHERE m.status = 'completed'
        ORDER BY m.match_date ASC, m.id ASC
    ");
    while ($row = $stmt->fetch()) {
        $scores = json_decode($row['scores_json'] ?? '{}', true);
        $summary = $scores['summary'] ?? '';
        fputcsv($output, [
            $row['id'], $row['match_date'], $row['game_name'], $row['round'],
            $row['team1'], $row['team2'], $row['winner'], $summary, $row['facility_name']
        ]);
    }
    fclose($output);
    exit;
} elseif ($download === 'standings_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=hpcl_overall_standings_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Rank', 'Unit Code', 'Unit Name', 'Gold (1st)', 'Silver (2nd)', 'Bronze (3rd)', 'Total Points']);

    $leaderboard = get_overall_championship_leaderboard($pdo);
    foreach ($leaderboard as $idx => $r) {
        fputcsv($output, [
            !empty($r['rank']) ? $r['rank'] : '-', $r['short_code'], $r['name'], $r['gold'], $r['silver'], $r['bronze'], $r['total_points']
        ]);
    }
    fclose($output);
    exit;
} elseif ($download === 'fixtures_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=hpcl_master_fixtures_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Match ID', 'Date', 'Start Time', 'End Time', 'Discipline', 'Round', 'Team 1', 'Team 2', 'Court / Venue', 'Status']);

    $stmt = $pdo->query("
        SELECT m.id, m.match_date, m.start_time, m.end_time, g.name as game_name, m.round,
               t1.name as team1, t2.name as team2, f.name as facility_name, m.status
        FROM matches m
        JOIN games g ON m.game_id = g.id
        LEFT JOIN teams t1 ON m.team1_id = t1.id
        LEFT JOIN teams t2 ON m.team2_id = t2.id
        LEFT JOIN facilities f ON m.facility_id = f.id
        ORDER BY m.match_date ASC, m.start_time ASC
    ");
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'], $row['match_date'], $row['start_time'], $row['end_time'],
            $row['game_name'], $row['round'], $row['team1'], $row['team2'], $row['facility_name'], $row['status']
        ]);
    }
    fclose($output);
    exit;
}

$leaderboard = get_overall_championship_leaderboard($pdo);
$completedMatches = $pdo->query("SELECT COUNT(*) FROM matches WHERE status = 'completed'")->fetchColumn();
$totalMatches = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
?>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold"><i class="fas fa-file-export text-primary mr-2"></i> Official Data Export & Reports</h1>
        <p class="text-muted mb-0">Generate certified match reports, Excel/CSV exports, and print-ready PDF certificates for prize distribution</p>
      </div>
      <div class="col-sm-6 text-right">
        <button class="btn btn-outline-dark" onclick="window.print();">
          <i class="fas fa-print mr-1"></i> Print / Save as PDF
        </button>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <!-- Export Action Cards -->
    <div class="row">
      <!-- Card 1: Results Export -->
      <div class="col-md-4">
        <div class="card card-outline card-success shadow-sm">
          <div class="card-body">
            <h5 class="font-weight-bold"><i class="fas fa-file-excel text-success mr-2"></i> Match Results & Scores</h5>
            <p class="small text-muted">Complete audit log of all completed matches, set scores, winners, and dates.</p>
            <a href="?download=results_csv" class="btn btn-success btn-block font-weight-bold">
              <i class="fas fa-download mr-1"></i> Export Results (CSV)
            </a>
          </div>
        </div>
      </div>

      <!-- Card 2: Standings Export -->
      <div class="col-md-4">
        <div class="card card-outline card-warning shadow-sm">
          <div class="card-body">
            <h5 class="font-weight-bold"><i class="fas fa-trophy text-warning mr-2"></i> Overall Championship</h5>
            <p class="small text-muted">Official medal tally and cumulative points table per 5-3-1 points scheme.</p>
            <a href="?download=standings_csv" class="btn btn-warning btn-block font-weight-bold text-dark">
              <i class="fas fa-download mr-1"></i> Export Standings (CSV)
            </a>
          </div>
        </div>
      </div>

      <!-- Card 3: Fixtures Export -->
      <div class="col-md-4">
        <div class="card card-outline card-primary shadow-sm">
          <div class="card-body">
            <h5 class="font-weight-bold"><i class="fas fa-calendar-check text-primary mr-2"></i> Master Fixtures</h5>
            <p class="small text-muted">Complete 3-day schedule across Balewadi sports facilities for all 7 disciplines.</p>
            <a href="?download=fixtures_csv" class="btn btn-primary btn-block font-weight-bold">
              <i class="fas fa-download mr-1"></i> Export Fixtures (CSV)
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Official Printable Report Preview -->
    <div class="card elevation-2 mt-4 printable-section">
      <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-0 font-weight-bold">HPCL 20th All India Inter Unit Sports & Games Tournament</h4>
          <small class="text-warning">Official Championship Standings & Prize Distribution Summary</small>
        </div>
        <div class="text-right small">
          Venue: Balewadi, Pune<br>
          Date: <?= date('d M Y') ?>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-bordered table-striped">
          <thead class="thead-light">
            <tr>
              <th style="width: 80px;">Rank</th>
              <th>Participating Unit</th>
              <th>Unit Code</th>
              <th class="text-center">Gold (5 pts)</th>
              <th class="text-center">Silver (3 pts)</th>
              <th class="text-center">Bronze (1 pt)</th>
              <th class="text-right">Total Points</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($leaderboard as $idx => $r): ?>
              <?php 
                $hasRank = !empty($r['rank']);
                $rankVal = $r['rank'] ?? null;
                $isTop = ($hasRank && $rankVal === 1);
              ?>
              <tr class="<?= $isTop ? 'table-warning font-weight-bold' : '' ?>">
                <td><strong><?= $hasRank ? '#' . $rankVal : '—' ?></strong></td>
                <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                <td><span class="badge badge-secondary"><?= htmlspecialchars($r['short_code']) ?></span></td>
                <td class="text-center font-weight-bold"><?= $r['gold'] ?></td>
                <td class="text-center font-weight-bold"><?= $r['silver'] ?></td>
                <td class="text-center font-weight-bold"><?= $r['bronze'] ?></td>
                <td class="text-right font-weight-bold" style="font-size: 1.1em; color: #003366;">
                  <?= $r['total_points'] ?> Pts
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="row mt-5 pt-4">
          <div class="col-4 text-center">
            <hr style="border-top: 1px solid #333; width: 80%;">
            <strong class="small">Tournament Director</strong><br>
            <span class="text-muted small">HPCL Organizing Committee</span>
          </div>
          <div class="col-4 text-center">
            <hr style="border-top: 1px solid #333; width: 80%;">
            <strong class="small">Chief Referee</strong><br>
            <span class="text-muted small">Balewadi Sports Complex</span>
          </div>
          <div class="col-4 text-center">
            <hr style="border-top: 1px solid #333; width: 80%;">
            <strong class="small">Project Sponsor</strong><br>
            <span class="text-muted small">Hindustan Petroleum Corp. Ltd.</span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
