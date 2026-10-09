<?php
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

$isTeamManager = (($_SESSION['role'] ?? '') === 'nodal');
$userUnitId = (int)($_SESSION['unit_id'] ?? 0);
$managerUnit = null;
if ($isTeamManager && $userUnitId > 0) {
    $uStmt = $pdo->prepare("SELECT * FROM units WHERE id = ?");
    $uStmt->execute([$userUnitId]);
    $managerUnit = $uStmt->fetch();
}

// Handle POST actions: Create Team, Auto-Provision Unit Teams, Create Player, Delete Player, Delete Team
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Create a Team in a Game
    if ($action === 'create_team') {
        $unit_id = (int)($_POST['unit_id'] ?? 0);
        if ($isTeamManager && $userUnitId > 0) {
            $unit_id = $userUnitId;
        }
        $game_id = (int)($_POST['game_id'] ?? 0);
        $team_name = trim($_POST['team_name'] ?? '');
        $pool = trim($_POST['pool'] ?? 'A');
        $seed = (int)($_POST['seed'] ?? 0) ?: null;

        if ($unit_id && $game_id) {
            // Check if unit already has a team in this game
            $check = $pdo->prepare("SELECT id FROM teams WHERE unit_id = ? AND game_id = ?");
            $check->execute([$unit_id, $game_id]);
            if ($check->fetch()) {
                $err = "This unit already has a registered team in this game.";
            } else {
                if (!$team_name) {
                    $uCode = $pdo->query("SELECT short_code FROM units WHERE id = $unit_id")->fetchColumn() ?: 'Unit';
                    $gName = $pdo->query("SELECT name FROM games WHERE id = $game_id")->fetchColumn() ?: 'Game';
                    $team_name = "$uCode $gName";
                }

                $stmt = $pdo->prepare("INSERT INTO teams (unit_id, game_id, name, pool, seed) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$unit_id, $game_id, $team_name, $pool ?: 'A', $seed]);
                $newTeamId = $pdo->lastInsertId();

                log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'TEAM_CREATE', "Registered team '$team_name' (Unit #$unit_id, Game #$game_id, Pool $pool)");
                $msg = "Team '$team_name' registered successfully! You can now add athletes to this team.";
            }
        } else {
            $err = "Both Unit and Game selection are required to register a team.";
        }
    }

    // Action 2: Auto-Provision Teams for a Unit across all 9 games
    elseif ($action === 'auto_provision_unit') {
        $unit_id = (int)($_POST['unit_id'] ?? 0);
        if ($isTeamManager && $userUnitId > 0) {
            $unit_id = $userUnitId;
        }
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
                    $teamName = $u['short_code'] . ' ' . $g['name'];
                    $insTeam->execute([$unit_id, $g['id'], $teamName, $pool]);
                    $createdCount++;
                }
            }

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'TEAMS_AUTO_PROVISION', "Auto-provisioned $createdCount teams for Unit {$u['name']} ({$u['short_code']})");
            $msg = "Successfully entered {$u['name']} into $createdCount tournament disciplines! All teams are now available in the athlete registration dropdown.";
        }
    }

    // Action 3: Create Athlete / Player with Official Team Composition Validation
    elseif ($action === 'create_player') {
        $team_id = (int)($_POST['team_id'] ?? 0);
        $unit_id = (int)($_POST['unit_id'] ?? 0);
        if ($isTeamManager && $userUnitId > 0) {
            $unit_id = $userUnitId;
        }
        $game_id = (int)($_POST['game_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $gender = in_array($_POST['gender'] ?? '', ['Men', 'Women']) ? $_POST['gender'] : 'Men';
        $designation = trim($_POST['designation'] ?? 'Officer');
        $is_u30 = isset($_POST['is_u30']) ? 1 : 0;

        if ($name) {
            // If team_id is provided directly
            if ($team_id > 0) {
                $stmt = $pdo->prepare("SELECT unit_id, game_id FROM teams WHERE id = ?");
                $stmt->execute([$team_id]);
                $teamRow = $stmt->fetch();
                if ($teamRow) {
                    $foundUnitId = $teamRow['unit_id'];
                    $game_id = $teamRow['game_id'];
                    if ($isTeamManager && $userUnitId > 0 && $foundUnitId != $userUnitId) {
                        $err = "Unauthorized: Team does not belong to your assigned unit.";
                        $team_id = 0;
                    } else {
                        $unit_id = $foundUnitId;
                    }
                }
            } 
            // If unit_id and game_id are provided, auto-resolve or auto-create the team
            elseif ($unit_id > 0 && $game_id > 0) {
                $stmt = $pdo->prepare("SELECT id FROM teams WHERE unit_id = ? AND game_id = ?");
                $stmt->execute([$unit_id, $game_id]);
                $team_id = $stmt->fetchColumn();

                if (!$team_id) {
                    $uCode = $pdo->query("SELECT short_code FROM units WHERE id = $unit_id")->fetchColumn() ?: 'Unit';
                    $gName = $pdo->query("SELECT name FROM games WHERE id = $game_id")->fetchColumn() ?: 'Game';
                    $team_name = "$uCode $gName";
                    
                    $ins = $pdo->prepare("INSERT INTO teams (unit_id, game_id, name, pool) VALUES (?, ?, ?, 'A')");
                    $ins->execute([$unit_id, $game_id, $team_name]);
                    $team_id = $pdo->lastInsertId();
                    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'TEAM_AUTO_CREATE', "Auto-created team '$team_name' during athlete registration");
                }
            }

            if ($team_id && $unit_id) {
                // Official Team Composition Validation against games rules
                $gInfoStmt = $pdo->prepare("SELECT id, name, men_min, men_max, women_min, women_max, total_max FROM games WHERE id = ?");
                $gInfoStmt->execute([$game_id]);
                $gameInfo = $gInfoStmt->fetch();

                if ($gameInfo) {
                    // Check current roster counts for this team
                    $cntStmt = $pdo->prepare("SELECT gender, COUNT(*) as c FROM players WHERE team_id = ? GROUP BY gender");
                    $cntStmt->execute([$team_id]);
                    $rosterCounts = ['Men' => 0, 'Women' => 0];
                    foreach ($cntStmt->fetchAll() as $r) {
                        $rosterCounts[$r['gender']] = (int)$r['c'];
                    }
                    $curMen = $rosterCounts['Men'];
                    $curWomen = $rosterCounts['Women'];
                    $curTotal = $curMen + $curWomen;

                    // 1. Check Men Quota: Male athletes can NEVER exceed men_max (male cannot register in women's slots)
                    if ($gender === 'Men') {
                        if ($gameInfo['men_max'] > 0 && $curMen >= $gameInfo['men_max']) {
                            $err = "Quota Exceeded: {$gameInfo['name']} allows a maximum of {$gameInfo['men_max']} Men (Current registered: $curMen). Male athletes cannot register in Women's quota slots.";
                        } elseif ($gameInfo['total_max'] > 0 && $curTotal >= $gameInfo['total_max']) {
                            $err = "Discipline Cap Reached: {$gameInfo['name']} has reached its total maximum of {$gameInfo['total_max']} athletes.";
                        }
                    }
                    // 2. Check Women Quota: Female athletes CAN register against Men in any discipline
                    elseif ($gender === 'Women') {
                        if ($gameInfo['total_max'] > 0 && $curTotal >= $gameInfo['total_max']) {
                            $err = "Discipline Cap Reached: {$gameInfo['name']} has reached its total maximum of {$gameInfo['total_max']} athletes.";
                        }
                    }
                }

                // 3. Check Unit Overall Contingent Cap (47 Athletes + 1 Team Manager = 48 Max)
                if (!$err) {
                    $unitAthletesCount = (int)$pdo->query("SELECT COUNT(*) FROM players WHERE unit_id = $unit_id")->fetchColumn();
                    if ($unitAthletesCount >= 47) {
                        $err = "Maximum Team Size Limit: Unit already has 47 registered athletes. With 1 Team Manager, the maximum team size of 48 is reached!";
                    }
                }

                // Proceed with registration if validation passes
                if (!$err) {
                    $stmt = $pdo->prepare("INSERT INTO players (team_id, unit_id, name, designation, gender, is_u30) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$team_id, $unit_id, $name, $designation, $gender, $is_u30]);
                    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'PLAYER_ADD', "Added athlete $name ($gender) to team #$team_id");
                    $msg = "Athlete '$name' ($gender) registered successfully to roster!";
                }
            } else {
                if (!$err) $err = "Please select a valid Team or Unit & Sport.";
            }
        } else {
            $err = "Athlete full name is required.";
        }
    }

    // Action 4: Delete Athlete
    elseif ($action === 'delete_player') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($isTeamManager && $userUnitId > 0) {
                $chk = $pdo->prepare("SELECT id FROM players WHERE id = ? AND unit_id = ?");
                $chk->execute([$id, $userUnitId]);
                if (!$chk->fetch()) {
                    $_SESSION['flash_err'] = "Unauthorized: You can only remove athletes from your assigned unit.";
                    header("Location: " . BASE_URL . "/admin/teams?tab=athletes");
                    exit;
                }
            }
            $stmt = $pdo->prepare("DELETE FROM players WHERE id = ?");
            $stmt->execute([$id]);
            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'PLAYER_DELETE', "Removed athlete #$id");
            $_SESSION['flash_msg'] = "Athlete removed from roster.";
            header("Location: " . BASE_URL . "/admin/teams?tab=" . ($_GET['tab'] ?? 'athletes'));
            exit;
        }
    }
}

// Handle GET delete team
if (isset($_GET['delete_team'])) {
    $id = (int)$_GET['delete_team'];
    if ($id > 0) {
        if ($isTeamManager && $userUnitId > 0) {
            $chk = $pdo->prepare("SELECT id FROM teams WHERE id = ? AND unit_id = ?");
            $chk->execute([$id, $userUnitId]);
            if (!$chk->fetch()) {
                $_SESSION['flash_err'] = "Unauthorized: You can only remove teams from your assigned unit.";
                header("Location: " . BASE_URL . "/admin/teams?tab=teams");
                exit;
            }
        }
        $pdo->prepare("DELETE FROM players WHERE team_id = ?")->execute([$id]);
        $pdo->prepare("UPDATE matches SET team1_id = NULL WHERE team1_id = ?")->execute([$id]);
        $pdo->prepare("UPDATE matches SET team2_id = NULL WHERE team2_id = ?")->execute([$id]);
        $pdo->prepare("UPDATE matches SET winner_id = NULL WHERE winner_id = ?")->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
        $stmt->execute([$id]);
        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'TEAM_DELETE', "Removed team entry #$id");
        $_SESSION['flash_msg'] = "Team entry removed.";
        header("Location: " . BASE_URL . "/admin/teams?tab=teams");
        exit;
    }
}

// Active tab
$activeTab = $_GET['tab'] ?? ($isTeamManager ? 'composition' : 'athletes');

// Filters
$filterGame = (int)($_GET['game_id'] ?? 0);
$filterUnit = (int)($_GET['unit_id'] ?? 0);
if ($isTeamManager && $userUnitId > 0) {
    $filterUnit = $userUnitId;
}

// Query Units and Games
$allUnits = $pdo->query("SELECT id, name, short_code, color_code FROM units ORDER BY short_code ASC")->fetchAll();
$allGames = $pdo->query("
    SELECT * FROM games 
    ORDER BY FIELD(slug, 'badminton', 'bridge', 'carrom', 'chess', 'swimming', 'table-tennis', 'tennis', 'badminton-open', 'table-tennis-open'), id ASC
")->fetchAll();

// Target Unit for Team Composition Matrix
$targetUnitId = $filterUnit > 0 ? $filterUnit : ($userUnitId > 0 ? $userUnitId : ($allUnits[0]['id'] ?? 1));
$targetUnitStmt = $pdo->prepare("SELECT * FROM units WHERE id = ?");
$targetUnitStmt->execute([$targetUnitId]);
$targetUnit = $targetUnitStmt->fetch() ?: ($allUnits[0] ?? null);

// Fetch Team Composition Matrix for $targetUnitId
$compMatrix = [];
$totalUnitMen = 0;
$totalUnitWomen = 0;
$totalUnitAthletes = 0;

if ($targetUnit) {
    foreach ($allGames as $gm) {
        $tmStmt = $pdo->prepare("SELECT id, name, pool, seed FROM teams WHERE unit_id = ? AND game_id = ?");
        $tmStmt->execute([$targetUnit['id'], $gm['id']]);
        $team = $tmStmt->fetch();

        $menCount = 0;
        $womenCount = 0;
        if ($team) {
            $pStmt = $pdo->prepare("SELECT gender, COUNT(*) as c FROM players WHERE team_id = ? GROUP BY gender");
            $pStmt->execute([$team['id']]);
            foreach ($pStmt->fetchAll() as $row) {
                if ($row['gender'] === 'Men') $menCount = (int)$row['c'];
                if ($row['gender'] === 'Women') $womenCount = (int)$row['c'];
            }
        }
        $totCount = $menCount + $womenCount;

        $totalUnitMen += $menCount;
        $totalUnitWomen += $womenCount;
        $totalUnitAthletes += $totCount;

        $compMatrix[] = [
            'game' => $gm,
            'team' => $team,
            'men' => $menCount,
            'women' => $womenCount,
            'total' => $totCount,
            'men_compliant' => ($gm['men_min'] == 0 || $menCount >= $gm['men_min']) && ($gm['men_max'] == 0 || $menCount <= $gm['men_max']),
            'women_compliant' => ($gm['women_min'] == 0 || $womenCount >= $gm['women_min']) && ($totCount <= $gm['total_max']),
            'total_compliant' => ($gm['total_max'] == 0 || $totCount <= $gm['total_max'])
        ];
    }
}

// Fetch Athletes
$queryPlayers = "
    SELECT p.*, t.name as team_name, g.name as game_name, u.name as unit_name, u.short_code as unit_code, u.color_code
    FROM players p
    JOIN teams t ON p.team_id = t.id
    JOIN games g ON t.game_id = g.id
    JOIN units u ON p.unit_id = u.id
    WHERE 1=1
";
$paramsPlayers = [];
if ($filterGame > 0) {
    $queryPlayers .= " AND g.id = ?";
    $paramsPlayers[] = $filterGame;
}
if ($filterUnit > 0) {
    $queryPlayers .= " AND u.id = ?";
    $paramsPlayers[] = $filterUnit;
}
$queryPlayers .= " ORDER BY u.short_code ASC, g.id ASC, p.name ASC";

$stmtPlayers = $pdo->prepare($queryPlayers);
$stmtPlayers->execute($paramsPlayers);
$players = $stmtPlayers->fetchAll();

// Fetch Teams
$queryTeams = "
    SELECT t.*, g.name as game_name, g.category, u.name as unit_name, u.short_code as unit_code, u.color_code,
           COUNT(p.id) as athlete_count
    FROM teams t
    JOIN games g ON t.game_id = g.id
    JOIN units u ON t.unit_id = u.id
    LEFT JOIN players p ON t.id = p.team_id
    WHERE 1=1
";
$paramsTeams = [];
if ($filterGame > 0) {
    $queryTeams .= " AND g.id = ?";
    $paramsTeams[] = $filterGame;
}
if ($filterUnit > 0) {
    $queryTeams .= " AND u.id = ?";
    $paramsTeams[] = $filterUnit;
}
$queryTeams .= " GROUP BY t.id ORDER BY u.short_code ASC, g.id ASC";

$stmtTeams = $pdo->prepare($queryTeams);
$stmtTeams->execute($paramsTeams);
$teamsList = $stmtTeams->fetchAll();

$allTeams = $pdo->query("
    SELECT t.id, t.name, t.unit_id, t.game_id, g.name as game_name, u.short_code, u.name as unit_name 
    FROM teams t 
    JOIN games g ON t.game_id = g.id 
    JOIN units u ON t.unit_id = u.id 
    ORDER BY u.short_code ASC, g.name ASC
")->fetchAll();
?>

<?php if ($isTeamManager && $managerUnit): ?>
  <!-- TEAM MANAGER HERO BANNER -->
  <div class="content-header pb-0">
    <div class="container-fluid">
      <div class="card elevation-3 border-0" style="background: linear-gradient(135deg, #0b1f44 0%, #173b75 100%); color: #fff; border-radius: 12px; overflow: hidden;">
        <div class="card-body p-4">
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div>
              <div class="d-flex align-items-center mb-2">
                <span class="badge px-3 py-1 font-weight-bold text-uppercase mr-2" style="background-color: <?= $managerUnit['color_code'] ?>; color: #fff; font-size: 0.95rem;">
                  <i class="fas fa-building mr-1"></i> <?= htmlspecialchars($managerUnit['short_code']) ?>
                </span>
                <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold text-uppercase">
                  <i class="fas fa-user-tie mr-1"></i> Team Manager Portal
                </span>
              </div>
              <h1 class="font-weight-bold mb-1 text-white" style="font-size: 1.85rem;"><?= htmlspecialchars($managerUnit['name']) ?></h1>
              <p class="text-light mb-0 small">
                Logged in as <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></strong> &bull; Official Team Manager for team entries, athlete rosters, and squad quotas.
              </p>
            </div>
            <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
              <button class="btn btn-warning font-weight-bold text-dark shadow-sm mr-2 mb-1" data-toggle="modal" data-target="#addPlayerModal">
                <i class="fas fa-user-plus mr-1"></i> Register Athlete
              </button>
              <button class="btn btn-outline-light font-weight-bold shadow-sm mb-1" data-toggle="modal" data-target="#addTeamModal">
                <i class="fas fa-shield-alt mr-1"></i> Add / Edit Team Entry
              </button>
            </div>
          </div>

          <!-- Unit Metrics Strip matching official tournament composition -->
          <div class="row mt-4 pt-3 border-top" style="border-color: rgba(255,255,255,0.15) !important;">
            <div class="col-lg-2 col-md-4 col-6 mb-2">
              <small class="text-muted d-block text-uppercase" style="color: #cbd5e1 !important;">Disciplines Entered</small>
              <h4 class="font-weight-bold text-warning mb-0"><?= count($teamsList) ?> / <?= count($allGames) ?> Sports</h4>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-2">
              <small class="text-muted d-block text-uppercase" style="color: #cbd5e1 !important;">Men Athletes</small>
              <h4 class="font-weight-bold text-info mb-0"><?= $totalUnitMen ?> / 30 Max</h4>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-2">
              <small class="text-muted d-block text-uppercase" style="color: #cbd5e1 !important;">Women Athletes</small>
              <h4 class="font-weight-bold text-danger mb-0" style="color: #fca5a5 !important;"><?= $totalUnitWomen ?> / 17 Max</h4>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-2">
              <small class="text-muted d-block text-uppercase" style="color: #cbd5e1 !important;">Total Athletes</small>
              <h4 class="font-weight-bold text-white mb-0"><?= $totalUnitAthletes ?> / 47 Max</h4>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-2">
              <small class="text-muted d-block text-uppercase" style="color: #cbd5e1 !important;">Team Manager</small>
              <h4 class="font-weight-bold text-success mb-0">1 / 1 (Active)</h4>
            </div>
            <div class="col-lg-2 col-md-4 col-6 mb-2">
              <small class="text-muted d-block text-uppercase" style="color: #cbd5e1 !important;">Total Contingent</small>
              <h4 class="font-weight-bold text-warning mb-0"><?= $totalUnitAthletes + 1 ?> / 48 Max</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php else: ?>
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0 font-weight-bold"><i class="fas fa-users text-primary mr-2"></i> Sports Team Composition & Rosters</h1>
          <p class="text-muted mb-0">Manage Unit Squads, Athlete Rosters, and Official HPCL Tournament Quotas</p>
        </div>
        <div class="col-sm-6 text-right">
          <button class="btn btn-warning font-weight-bold text-dark mr-2" data-toggle="modal" data-target="#addPlayerModal">
            <i class="fas fa-user-plus mr-1"></i> Register Athlete
          </button>
          <button class="btn btn-success" data-toggle="modal" data-target="#addTeamModal">
            <i class="fas fa-shield-alt mr-1"></i> Register Team Entry
          </button>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="content">
  <div class="container-fluid">

    <?php if ($isTeamManager && empty($managerUnit)): ?>
      <div class="alert alert-warning elevation-2 mb-3">
        <h5 class="font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i> Notice: No Unit Assigned to this Team Manager Account</h5>
        <p class="mb-1">
          Your account is registered as a <strong>Team Manager</strong>, but does not have a specific HPCL participating unit (e.g. <strong>Mumbai Refinery</strong>) linked to it in the system.
        </p>
        <p class="mb-0">
          Because no unit is assigned, you are currently viewing teams across <strong>all 12 units (including East Zone - EZ)</strong>. The administrator can link your specific unit in <a href="<?= BASE_URL ?>/admin/users" class="font-weight-bold text-dark text-underline">User Roles & Credential Management &rarr; Edit</a> to restrict your view and roster entries exclusively to your unit.
        </p>
      </div>
    <?php endif; ?>

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

    <!-- 1-CLICK PROVISION ALERT IF NOT ALL 9 SPORTS ENTERED -->
    <?php if ($isTeamManager && $managerUnit && count($teamsList) < count($allGames)): ?>
      <div class="alert alert-warning elevation-1 d-flex justify-content-between align-items-center mb-3">
        <div>
          <i class="fas fa-magic fa-lg mr-2 text-dark"></i>
          <strong>Quick Setup:</strong> Enter <?= htmlspecialchars($managerUnit['name']) ?> into all <?= count($allGames) ?> tournament disciplines with 1-click so you can immediately register athletes across all sports!
        </div>
        <form method="POST" style="margin: 0;">
          <input type="hidden" name="action" value="auto_provision_unit">
          <input type="hidden" name="unit_id" value="<?= $managerUnit['id'] ?>">
          <button type="submit" class="btn btn-dark btn-sm font-weight-bold shadow-sm">
            <i class="fas fa-bolt mr-1 text-warning"></i> 1-Click: Enter All 9 Disciplines
          </button>
        </form>
      </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-3">
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'composition' ? 'active' : '' ?>" href="?tab=composition&game_id=<?= $filterGame ?>&unit_id=<?= $filterUnit ?>">
          <i class="fas fa-clipboard-list mr-1"></i> Sports Team Composition
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'athletes' ? 'active' : '' ?>" href="?tab=athletes&game_id=<?= $filterGame ?>&unit_id=<?= $filterUnit ?>">
          <i class="fas fa-running mr-1"></i> Athlete Rosters (<?= count($players) ?>)
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'teams' ? 'active' : '' ?>" href="?tab=teams&game_id=<?= $filterGame ?>&unit_id=<?= $filterUnit ?>">
          <i class="fas fa-shield-alt mr-1"></i> Registered Teams (<?= count($teamsList) ?>)
        </a>
      </li>
    </ul>

    <!-- Filter Card -->
    <div class="card card-outline card-primary mb-3">
      <div class="card-body p-3">
        <form method="GET" class="form-row align-items-center">
          <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">
          <div class="col-md-4">
            <select name="game_id" class="form-control form-control-sm" onchange="this.form.submit()">
              <option value="0">-- Filter by Sport (All Disciplines) --</option>
              <?php foreach ($allGames as $g): ?>
                <option value="<?= $g['id'] ?>" <?= $filterGame == $g['id'] ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <?php if ($isTeamManager && $managerUnit): ?>
              <div class="input-group input-group-sm">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light font-weight-bold"><i class="fas fa-lock mr-1 text-muted"></i> Unit:</span>
                </div>
                <input type="text" class="form-control form-control-sm font-weight-bold bg-light" value="<?= htmlspecialchars($managerUnit['short_code']) ?> - <?= htmlspecialchars($managerUnit['name']) ?>" readonly>
              </div>
            <?php else: ?>
              <select name="unit_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="0">-- Select Unit for Composition --</option>
                <?php foreach ($allUnits as $u): ?>
                  <option value="<?= $u['id'] ?>" <?= $targetUnitId == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['short_code']) ?> - <?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>
          <div class="col-md-2">
            <a href="<?= BASE_URL ?>/admin/teams?tab=<?= $activeTab ?>" class="btn btn-outline-secondary btn-sm">Reset Filters</a>
          </div>
        </form>
      </div>
    </div>

    <?php if ($activeTab === 'composition'): ?>
      <!-- TAB 1: OFFICIAL SPORTS TEAM COMPOSITION TABLE -->
      <div class="card elevation-2 border-0 mb-4" style="border-radius: 8px; overflow: hidden;">
        <div class="card-header" style="background-color: #0b1f44; color: #fff;">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <h3 class="card-title font-weight-bold text-white mb-0" style="font-size: 1.25rem;">
                <i class="fas fa-table mr-2 text-warning"></i> Sports Team Composition
              </h3>
              <span class="text-light small ml-2 d-none d-md-inline">
                HPCL Tournament Quotas & Squad Limits &bull; Participating Unit: <strong><?= htmlspecialchars($targetUnit['name'] ?? 'Unit') ?> (<?= htmlspecialchars($targetUnit['short_code'] ?? '') ?>)</strong>
              </span>
            </div>
            <button class="btn btn-warning btn-sm font-weight-bold text-dark shadow-sm" data-toggle="modal" data-target="#addPlayerModal">
              <i class="fas fa-user-plus mr-1"></i> Register Athlete
            </button>
          </div>
        </div>

        <div class="card-body p-0 table-responsive">
          <table class="table table-bordered table-hover mb-0" style="font-size: 0.95rem;">
            <thead style="background-color: #1a365d; color: #ffffff; text-align: center;">
              <tr>
                <th style="width: 70px;">Sr. No.</th>
                <th style="text-align: left;">Sports Discipline</th>
                <th style="width: 110px;">Men Max</th>
                <th style="width: 110px;">Men Min</th>
                <th style="width: 130px; background-color: #1e4273;">Registered Men</th>
                <th style="width: 110px;">Women Max</th>
                <th style="width: 110px;">Women Min</th>
                <th style="width: 130px; background-color: #1e4273;">Registered Women</th>
                <th style="width: 110px;">Total Max</th>
                <th style="width: 140px; background-color: #1e4273;">Current Total</th>
                <th style="width: 110px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $sr = 1; foreach ($compMatrix as $row): ?>
                <?php 
                  $gm = $row['game'];
                  $men = $row['men'];
                  $women = $row['women'];
                  $tot = $row['total'];
                ?>
                <tr>
                  <td class="text-center font-weight-bold text-muted"><?= $sr++ ?></td>
                  <td class="font-weight-bold text-dark">
                    <i class="<?= htmlspecialchars($gm['icon']) ?> text-primary mr-2" style="width: 18px;"></i>
                    <?= htmlspecialchars($gm['name']) ?>
                  </td>
                  <td class="text-center font-weight-bold"><?= $gm['men_max'] ?></td>
                  <td class="text-center"><?= $gm['men_min'] ?></td>
                  <td class="text-center" style="background-color: #f8fafc;">
                    <span class="badge badge-<?= ($men >= $gm['men_min'] && $men <= $gm['men_max'] && $men > 0) ? 'success' : ($men < $gm['men_min'] ? 'warning text-dark' : ($men > $gm['men_max'] ? 'danger' : 'secondary')) ?> px-2 py-1">
                      <?= $men ?> / <?= $gm['men_max'] ?>
                    </span>
                  </td>
                  <td class="text-center font-weight-bold"><?= $gm['women_max'] ?></td>
                  <td class="text-center"><?= $gm['women_min'] ?></td>
                  <td class="text-center" style="background-color: #f8fafc;">
                    <?php if ($gm['women_max'] == 0): ?>
                      <?php if ($women > 0): ?>
                        <span class="badge badge-info px-2 py-1" title="Female athlete(s) registered against Men's quota">
                          <i class="fas fa-venus mr-1"></i> <?= $women ?> (vs Men)
                        </span>
                      <?php else: ?>
                        <span class="badge badge-light border text-muted" title="Women can register against Men">0 (Open vs Men)</span>
                      <?php endif; ?>
                    <?php else: ?>
                      <?php if ($women > $gm['women_max']): ?>
                        <span class="badge badge-info px-2 py-1" title="Female athlete(s) taking Men's quota slots">
                          <?= $women ?> / <?= $gm['women_max'] ?> (+<?= ($women - $gm['women_max']) ?> vs Men)
                        </span>
                      <?php else: ?>
                        <span class="badge badge-<?= ($women >= $gm['women_min'] && $women > 0) ? 'success' : ($women < $gm['women_min'] ? 'warning text-dark' : 'secondary') ?> px-2 py-1">
                          <?= $women ?> / <?= $gm['women_max'] ?>
                        </span>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <td class="text-center font-weight-bold text-primary"><?= $gm['total_max'] ?></td>
                  <td class="text-center font-weight-bold" style="background-color: #f8fafc;">
                    <span class="badge badge-<?= ($tot <= $gm['total_max'] && $tot > 0) ? 'info' : ($tot > $gm['total_max'] ? 'danger' : 'secondary') ?> px-2 py-1">
                      <?= $tot ?> / <?= $gm['total_max'] ?>
                    </span>
                  </td>
                  <td class="text-center">
                    <?php if ($row['team']): ?>
                      <button class="btn btn-xs btn-outline-primary font-weight-bold" onclick="openAthleteModalForTeam(<?= $row['team']['id'] ?>, <?= $gm['id'] ?>)">
                        <i class="fas fa-plus mr-1"></i> Add
                      </button>
                    <?php else: ?>
                      <button class="btn btn-xs btn-outline-success font-weight-bold" onclick="openAthleteModalForGame(<?= $gm['id'] ?>)">
                        <i class="fas fa-plus mr-1"></i> Enroll
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot style="background-color: #dbeafe; font-weight: bold; border-top: 2px solid #93c5fd;">
              <tr>
                <td colspan="2" class="text-uppercase text-dark pl-3">
                  <strong>TOTAL ATHLETES</strong>
                </td>
                <td class="text-center font-weight-bold text-dark" style="font-size: 1.1rem;">30</td>
                <td></td>
                <td class="text-center font-weight-bold text-info" style="font-size: 1.05rem;">
                  <?= $totalUnitMen ?> / 30
                </td>
                <td class="text-center font-weight-bold text-dark" style="font-size: 1.1rem;">17</td>
                <td></td>
                <td class="text-center font-weight-bold text-danger" style="font-size: 1.05rem;">
                  <?= $totalUnitWomen ?> / 17
                </td>
                <td class="text-center font-weight-bold text-primary" style="font-size: 1.1rem;">47</td>
                <td class="text-center font-weight-bold text-primary" style="font-size: 1.05rem;">
                  <?= $totalUnitAthletes ?> / 47
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="card-footer bg-light p-3 border-top">
          <div class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
              <h5 class="mb-1 text-dark font-weight-bold">
                <i class="fas fa-user-tie text-primary mr-2"></i> Team Manager: <span class="badge badge-primary px-3 py-1 font-weight-bold">1</span>
              </h5>
              <h4 class="mb-0 text-success font-weight-bold">
                <i class="fas fa-users text-success mr-2"></i> Maximum Team Size: <span class="badge badge-success px-3 py-1" style="font-size: 1.25rem;">48</span>
              </h4>
            </div>
            <div class="col-md-6 text-md-right text-muted small">
              <span class="d-block"><strong>Quota Breakdown:</strong> 47 Maximum Registered Athletes + 1 Official Team Manager = 48 Total Contingent Size.</span>
              <span class="d-block text-info mt-1"><i class="fas fa-check-circle mr-1"></i> Roster limits adhere to HPCL 20th All India Inter Unit Sports & Games rules.</span>
            </div>
          </div>
        </div>
      </div>

    <?php elseif ($activeTab === 'athletes'): ?>
      <!-- TAB 2: ATHLETES TABLE -->
      <div class="card elevation-2">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h3 class="card-title font-weight-bold mb-0">Enrolled Athlete Rosters</h3>
          <span class="badge badge-info"><?= count($players) ?> Athletes</span>
        </div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
              <tr>
                <th>Athlete Name</th>
                <th>Gender</th>
                <th>Unit</th>
                <th>Sport / Discipline</th>
                <th>Team Entry</th>
                <th>Designation</th>
                <th>Category Tag</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($players)): ?>
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">
                    No athletes found. Click "Register Athlete" above to enroll an athlete!
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($players as $p): ?>
                  <tr>
                    <td>
                      <i class="fas fa-user-circle mr-2 text-secondary"></i>
                      <strong><?= htmlspecialchars($p['name']) ?></strong>
                    </td>
                    <td>
                      <?php if (($p['gender'] ?? 'Men') === 'Women'): ?>
                        <span class="badge badge-light border text-danger font-weight-bold"><i class="fas fa-venus mr-1"></i> Women</span>
                      <?php else: ?>
                        <span class="badge badge-light border text-primary font-weight-bold"><i class="fas fa-mars mr-1"></i> Men</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="badge" style="background-color: <?= $p['color_code'] ?>; color: #fff;">
                        <?= htmlspecialchars($p['unit_code']) ?>
                      </span>
                      <small class="text-muted ml-1"><?= htmlspecialchars($p['unit_name']) ?></small>
                    </td>
                    <td><strong><?= htmlspecialchars($p['game_name']) ?></strong></td>
                    <td><span class="badge badge-light border"><?= htmlspecialchars($p['team_name']) ?></span></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
                    <td>
                      <?php if ($p['is_u30']): ?>
                        <span class="badge badge-warning text-dark"><i class="fas fa-bolt mr-1"></i> Under-30</span>
                      <?php else: ?>
                        <span class="badge badge-secondary">Senior Team</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-right">
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Remove athlete from roster?');">
                        <input type="hidden" name="action" value="delete_player">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove Athlete">
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

    <?php else: ?>
      <!-- TAB 3: REGISTERED TEAMS TABLE -->
      <div class="card elevation-2">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h3 class="card-title font-weight-bold mb-0">Registered Discipline Teams</h3>
          <span class="badge badge-primary"><?= count($teamsList) ?> Teams</span>
        </div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="thead-dark">
              <tr>
                <th>ID</th>
                <th>Team Name</th>
                <th>Unit</th>
                <th>Sport / Discipline</th>
                <th>Pool</th>
                <th>Seed</th>
                <th>Athletes Enrolled</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($teamsList)): ?>
                <tr>
                  <td colspan="8" class="text-center py-4 text-muted">
                    No teams registered matching this filter. Click "Register Team Entry" to enter a unit into a sport.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($teamsList as $tm): ?>
                  <tr>
                    <td><strong>#<?= $tm['id'] ?></strong></td>
                    <td><strong><?= htmlspecialchars($tm['name']) ?></strong></td>
                    <td>
                      <span class="badge" style="background-color: <?= $tm['color_code'] ?>; color: #fff;">
                        <?= htmlspecialchars($tm['unit_code']) ?>
                      </span>
                      <small class="text-muted ml-1"><?= htmlspecialchars($tm['unit_name']) ?></small>
                    </td>
                    <td><strong><?= htmlspecialchars($tm['game_name']) ?></strong></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($tm['pool'] ?: 'A') ?></span></td>
                    <td><?= $tm['seed'] ? '#' . $tm['seed'] : '<span class="text-muted">—</span>' ?></td>
                    <td>
                      <span class="badge badge-<?= $tm['athlete_count'] > 0 ? 'success' : 'warning text-dark' ?>">
                        <?= $tm['athlete_count'] ?> athletes
                      </span>
                    </td>
                    <td class="text-right">
                      <button class="btn btn-sm btn-outline-primary mr-1" onclick="openAthleteModalForTeam(<?= $tm['id'] ?>, <?= $tm['game_id'] ?>)" title="Add Athlete">
                        <i class="fas fa-user-plus"></i>
                      </button>
                      <a href="?delete_team=<?= $tm['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this team entry? All associated athlete records will also be removed.')" title="Delete Team">
                        <i class="fas fa-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- MODAL 1: REGISTER ATHLETE TO ROSTER -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="create_player">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i> Register Athlete to Roster</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Athlete Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control font-weight-bold" placeholder="e.g. Arun Sharma, Priya Verma" required>
          </div>

          <div class="card bg-light p-3 border mb-3">
            <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-sitemap mr-1"></i> Select Unit & Sport</h6>
            
            <div class="form-group mb-2">
              <label class="small font-weight-bold mb-1">HPCL Unit <span class="text-danger">*</span></label>
              <?php if ($isTeamManager && $managerUnit): ?>
                <input type="hidden" name="unit_id" id="modal_player_unit" value="<?= $managerUnit['id'] ?>">
                <div class="form-control bg-light font-weight-bold d-flex align-items-center">
                  <span class="badge mr-2" style="background-color: <?= $managerUnit['color_code'] ?>; color: #fff;"><?= htmlspecialchars($managerUnit['short_code']) ?></span>
                  <?= htmlspecialchars($managerUnit['name']) ?>
                  <span class="badge badge-info ml-auto">Assigned Unit</span>
                </div>
              <?php else: ?>
                <select name="unit_id" id="modal_player_unit" class="form-control" onchange="updateTeamDropdown()">
                  <option value="">-- Select Unit (e.g. Bhopal, Mumbai Refinery) --</option>
                  <?php foreach ($allUnits as $un): ?>
                    <option value="<?= $un['id'] ?>" <?= $targetUnitId == $un['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($un['short_code']) ?> - <?= htmlspecialchars($un['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>
            </div>

            <div class="form-group mb-2">
              <label class="small font-weight-bold mb-1">Sport / Discipline <span class="text-danger">*</span></label>
              <select name="game_id" id="modal_player_game" class="form-control font-weight-bold" onchange="updateTeamDropdown()" required>
                <option value="">-- Select Sport (1 of 9 Disciplines) --</option>
                <?php foreach ($allGames as $gm): ?>
                  <option value="<?= $gm['id'] ?>" 
                          data-men-min="<?= $gm['men_min'] ?>" 
                          data-men-max="<?= $gm['men_max'] ?>" 
                          data-women-min="<?= $gm['women_min'] ?>" 
                          data-women-max="<?= $gm['women_max'] ?>" 
                          data-tot-max="<?= $gm['total_max'] ?>"
                          <?= $filterGame == $gm['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($gm['name']) ?> (Max: <?= $gm['total_max'] ?> &bull; Men: <?= $gm['men_min'] ?>-<?= $gm['men_max'] ?>, Women: <?= $gm['women_min'] ?>-<?= $gm['women_max'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <small id="discipline_quota_notice" class="form-text text-muted mt-1"></small>
            </div>

            <div class="form-group mb-0">
              <label class="small text-muted mb-1">Assigned Team Entry (Auto-Resolved)</label>
              <select name="team_id" id="modal_player_team" class="form-control font-weight-bold">
                <option value="">-- Auto-create / Select Team --</option>
                <?php foreach ($allTeams as $tm): ?>
                  <option value="<?= $tm['id'] ?>" data-unit="<?= $tm['unit_id'] ?>" data-game="<?= $tm['game_id'] ?>">
                    <?= htmlspecialchars($tm['short_code']) ?>: <?= htmlspecialchars($tm['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small id="team_help_text" class="text-info d-block mt-1">
                <i class="fas fa-info-circle mr-1"></i> If the team entry doesn't exist yet, it will be automatically created upon saving!
              </small>
            </div>
          </div>

          <!-- GENDER SELECTION FOR COMPOSITION COMPLIANCE -->
          <div class="form-group mb-3">
            <label class="font-weight-bold mb-1">Athlete Gender Category <span class="text-danger">*</span></label>
            <div class="d-flex align-items-center gap-3">
              <div class="custom-control custom-radio mr-3">
                <input type="radio" id="genderMen" name="gender" value="Men" class="custom-control-input" checked onchange="checkGenderRules()">
                <label class="custom-control-label font-weight-bold text-primary" for="genderMen">
                  <i class="fas fa-mars mr-1"></i> Men
                </label>
              </div>
              <div class="custom-control custom-radio">
                <input type="radio" id="genderWomen" name="gender" value="Women" class="custom-control-input" onchange="checkGenderRules()">
                <label class="custom-control-label font-weight-bold text-danger" for="genderWomen">
                  <i class="fas fa-venus mr-1"></i> Women
                </label>
              </div>
            </div>
            <small id="gender_rule_warning" class="font-weight-bold d-block mt-1"></small>
            <small class="form-text text-muted mt-1">
              <i class="fas fa-info-circle text-primary mr-1"></i> <strong>Tournament Rule:</strong> Female athletes can register in Women's slots or against Men's quota in any discipline. Male athletes cannot register in Women's quota slots.
            </small>
          </div>

          <div class="form-group">
            <label>Designation / Role</label>
            <input type="text" name="designation" class="form-control" placeholder="e.g. Sr. Operations Officer, Engineer">
          </div>

          <div class="form-check mt-3">
            <input type="checkbox" name="is_u30" value="1" class="form-check-input" id="u30Check">
            <label class="form-check-label font-weight-bold text-dark" for="u30Check">
              Eligible for Under-30 Corporate Championship Quota
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="saveAthleteBtn" class="btn btn-primary font-weight-bold">Register Athlete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL 2: REGISTER TEAM IN GAME -->
<div class="modal fade" id="addTeamModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="create_team">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title font-weight-bold"><i class="fas fa-shield-alt mr-2"></i> Register Team Entry in Discipline</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>HPCL Participating Unit <span class="text-danger">*</span></label>
            <?php if ($isTeamManager && $managerUnit): ?>
              <input type="hidden" name="unit_id" id="new_team_unit" value="<?= $managerUnit['id'] ?>" data-code="<?= htmlspecialchars($managerUnit['short_code']) ?>">
              <div class="form-control bg-light font-weight-bold d-flex align-items-center">
                <span class="badge mr-2" style="background-color: <?= $managerUnit['color_code'] ?>; color: #fff;"><?= htmlspecialchars($managerUnit['short_code']) ?></span>
                <?= htmlspecialchars($managerUnit['name']) ?>
                <span class="badge badge-info ml-auto">Assigned Unit</span>
              </div>
            <?php else: ?>
              <select name="unit_id" id="new_team_unit" class="form-control" required onchange="autoSuggestTeamName()">
                <option value="">-- Select Unit (e.g. Bhopal, Visakh Refinery) --</option>
                <?php foreach ($allUnits as $un): ?>
                  <option value="<?= $un['id'] ?>" data-code="<?= htmlspecialchars($un['short_code']) ?>" <?= $targetUnitId == $un['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($un['short_code']) ?> - <?= htmlspecialchars($un['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <div class="form-group">
            <label>Sport / Discipline <span class="text-danger">*</span></label>
            <select name="game_id" id="new_team_game" class="form-control font-weight-bold" required onchange="autoSuggestTeamName()">
              <option value="">-- Select Sport (1 of 9 Disciplines) --</option>
              <?php foreach ($allGames as $gm): ?>
                <option value="<?= $gm['id'] ?>" data-name="<?= htmlspecialchars($gm['name']) ?>">
                  <?= htmlspecialchars($gm['name']) ?> (Max: <?= $gm['total_max'] ?> athletes)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Team Name (Auto-Generated or Custom)</label>
            <input type="text" name="team_name" id="new_team_name" class="form-control font-weight-bold" placeholder="e.g. MR Badminton, VR Chess">
          </div>

          <div class="row">
            <div class="col-6 form-group">
              <label>Tournament Pool</label>
              <select name="pool" class="form-control">
                <option value="A">Pool A</option>
                <option value="B">Pool B</option>
              </select>
            </div>
            <div class="col-6 form-group">
              <label>Tournament Seed (Optional)</label>
              <input type="number" name="seed" class="form-control" placeholder="e.g. 1, 2">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success font-weight-bold">Save Team Entry</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function autoSuggestTeamName() {
  var unitElem = document.getElementById('new_team_unit');
  var gameSelect = document.getElementById('new_team_game');
  var nameInput = document.getElementById('new_team_name');

  var unitCode = '';
  if (unitElem.tagName.toLowerCase() === 'select') {
    var unitOption = unitElem.options[unitElem.selectedIndex];
    unitCode = unitOption ? unitOption.getAttribute('data-code') : '';
  } else {
    unitCode = unitElem.getAttribute('data-code') || '';
  }

  var gameOption = gameSelect.options[gameSelect.selectedIndex];
  var gameName = gameOption ? gameOption.getAttribute('data-name') : '';

  if (unitCode && gameName) {
    nameInput.value = unitCode + ' ' + gameName;
  }
}

function updateTeamDropdown() {
  var uVal = document.getElementById('modal_player_unit').value;
  var gSelect = document.getElementById('modal_player_game');
  var gVal = gSelect.value;
  var teamSelect = document.getElementById('modal_player_team');
  var helpText = document.getElementById('team_help_text');

  var options = teamSelect.options;
  var found = false;

  for (var i = 1; i < options.length; i++) {
    var opt = options[i];
    var optUnit = opt.getAttribute('data-unit');
    var optGame = opt.getAttribute('data-game');

    if (uVal && gVal) {
      if (optUnit == uVal && optGame == gVal) {
        opt.style.display = 'block';
        opt.selected = true;
        found = true;
      } else {
        opt.style.display = 'none';
      }
    } else if (uVal) {
      if (optUnit == uVal) {
        opt.style.display = 'block';
      } else {
        opt.style.display = 'none';
      }
    } else {
      opt.style.display = 'block';
    }
  }

  if (uVal && gVal && !found) {
    teamSelect.value = "";
    helpText.innerHTML = '<i class="fas fa-magic text-warning mr-1"></i> No team entry exists yet for this Unit in this sport. It will be <strong>automatically created</strong> upon saving!';
  } else if (found) {
    helpText.innerHTML = '<i class="fas fa-check-circle text-success mr-1"></i> Existing discipline team matched and selected!';
  } else {
    helpText.innerHTML = '<i class="fas fa-info-circle mr-1"></i> Choose Unit and Sport above.';
  }

  checkGenderRules();
}

function checkGenderRules() {
  var gSelect = document.getElementById('modal_player_game');
  var opt = gSelect.options[gSelect.selectedIndex];
  var warningBox = document.getElementById('gender_rule_warning');
  var saveBtn = document.getElementById('saveAthleteBtn');

  if (!opt || !opt.value) {
    warningBox.innerHTML = '';
    saveBtn.disabled = false;
    return;
  }

  var isWomen = document.getElementById('genderWomen').checked;
  var womenMax = parseInt(opt.getAttribute('data-women-max') || '0', 10);
  var menMax = parseInt(opt.getAttribute('data-men-max') || '0', 10);
  var gameTitle = opt.text.split('(')[0].trim();

  if (isWomen) {
    saveBtn.disabled = false;
    if (womenMax === 0) {
      warningBox.className = 'text-info font-weight-bold d-block mt-1';
      warningBox.innerHTML = '<i class="fas fa-check-circle text-success mr-1"></i> Permitted: Female athlete registering against Men\'s quota in ' + gameTitle + '.';
    } else {
      warningBox.className = 'text-success font-weight-bold d-block mt-1';
      warningBox.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Female athlete permitted to register in Women\'s quota or against Men\'s slots.';
    }
  } else {
    // Men selected: Male cannot register in women's slots
    if (menMax === 0) {
      warningBox.className = 'text-danger font-weight-bold d-block mt-1';
      warningBox.innerHTML = '<i class="fas fa-ban mr-1"></i> ' + gameTitle + ' has 0 Men quota. Male athletes cannot register in Women\'s slots.';
      saveBtn.disabled = true;
    } else {
      warningBox.className = 'text-muted d-block mt-1';
      warningBox.innerHTML = '<i class="fas fa-info-circle text-secondary mr-1"></i> Maximum ' + menMax + ' Men allowed in ' + gameTitle + ' (male cannot register in Women\'s slots).';
      saveBtn.disabled = false;
    }
  }
}

function openAthleteModalForTeam(teamId, gameId) {
  var teamSelect = document.getElementById('modal_player_team');
  teamSelect.value = teamId;
  if (gameId) {
    document.getElementById('modal_player_game').value = gameId;
  }
  var opt = teamSelect.options[teamSelect.selectedIndex];
  if (opt) {
    document.getElementById('modal_player_unit').value = opt.getAttribute('data-unit') || '';
  }
  updateTeamDropdown();
  $('#addPlayerModal').modal('show');
}

function openAthleteModalForGame(gameId) {
  document.getElementById('modal_player_game').value = gameId;
  updateTeamDropdown();
  $('#addPlayerModal').modal('show');
}
</script>
