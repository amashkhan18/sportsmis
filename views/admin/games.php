<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

// Handle Add Game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $category = $_POST['category'] ?? 'Team Event';
    $format = $_POST['format'] ?? 'pools_knockout';
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
    if ($category === 'Under-30 Individual') $slug .= '-u30';

    $men_min = (int)($_POST['men_min'] ?? 0);
    $men_max = (int)($_POST['men_max'] ?? 0);
    $women_min = (int)($_POST['women_min'] ?? 0);
    $women_max = (int)($_POST['women_max'] ?? 0);
    $total_max = (int)($_POST['total_max'] ?? ($men_max + $women_max));

    if ($name) {
        $check = $pdo->prepare("SELECT id FROM games WHERE slug = ?");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $err = "A game with this name or slug already exists.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO games (name, slug, category, format, men_min, men_max, women_min, women_max, total_max) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $category, $format, $men_min, $men_max, $women_min, $women_max, $total_max]);
            $newGId = $pdo->lastInsertId();

            // Auto-create teams for all existing units in this new game
            $units = $pdo->query("SELECT id, short_code FROM units ORDER BY id ASC")->fetchAll();
            $insTeam = $pdo->prepare("INSERT INTO teams (unit_id, game_id, name, pool) VALUES (?, ?, ?, ?)");
            foreach ($units as $idx => $u) {
                $pool = ($idx % 2 == 0) ? 'A' : 'B';
                $teamName = "{$u['short_code']} $name";
                $insTeam->execute([$u['id'], $newGId, $teamName, $pool]);
            }

            log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'GAME_CREATE', "Added game '$name' (Men: $men_min-$men_max, Women: $women_min-$women_max, Tot: $total_max)");
            $_SESSION['flash_msg'] = "Game '$name' registered successfully with default unit teams created.";
            header("Location: " . BASE_URL . "/admin/games");
            exit;
        }
    } else {
        $err = "Game name is required.";
    }
}

// Handle Delete (supports both POST form and GET ?delete=ID)
$deleteId = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $deleteId = (int)($_POST['id'] ?? 0);
} elseif (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
}

if ($deleteId > 0) {
    try {
        $pdo->beginTransaction();
        // 1. Delete matches associated with this game
        $pdo->prepare("DELETE FROM matches WHERE game_id = ?")->execute([$deleteId]);
        // 2. Delete players in teams of this game
        $pdo->prepare("DELETE FROM players WHERE team_id IN (SELECT id FROM teams WHERE game_id = ?)")->execute([$deleteId]);
        // 3. Delete teams of this game
        $pdo->prepare("DELETE FROM teams WHERE game_id = ?")->execute([$deleteId]);
        // 4. Delete photos of this game
        $pdo->prepare("DELETE FROM photos WHERE game_id = ?")->execute([$deleteId]);
        // 5. Delete the game itself
        $pdo->prepare("DELETE FROM games WHERE id = ?")->execute([$deleteId]);
        $pdo->commit();

        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'GAME_DELETE', "Deleted game ID #$deleteId and all associated fixtures/teams");
        $_SESSION['flash_msg'] = "Game ID #$deleteId and its associated teams/fixtures were successfully deleted.";
        header("Location: " . BASE_URL . "/admin/games");
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $err = "Could not delete game: " . $e->getMessage();
    }
}

// Fetch Games
$stmt = $pdo->query("SELECT * FROM games ORDER BY id DESC");
$games = $stmt->fetchAll();
?>

    <!-- Content Header -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Tournament Games Setup</h1>
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

        <!-- Add Game Form -->
        <div class="card card-primary collapsed-card">
            <div class="card-header">
                <h3 class="card-title">Add New Game</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i></button>
                </div>
            </div>
            <div class="card-body" style="display: none;">
                <form action="<?= BASE_URL ?>/admin/games" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Game Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Badminton" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Category</label>
                                <select name="category" class="form-control" required>
                                    <option value="Team Event">Team Event</option>
                                    <option value="Open Category">Open Category</option>
                                    <option value="Under-30 Individual">Under-30 Individual</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Format</label>
                                <select name="format" class="form-control" required>
                                    <option value="pools_knockout">Short Pools → Knockout</option>
                                    <option value="seeded_knockout">Seeded Knockout with Byes</option>
                                    <option value="swiss">Swiss System</option>
                                    <option value="heats_finals">Heats → Finals</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 col-6">
                            <div class="form-group">
                                <label>Men Min</label>
                                <input type="number" name="men_min" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="form-group">
                                <label>Men Max</label>
                                <input type="number" name="men_max" class="form-control" value="4">
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="form-group">
                                <label>Women Min</label>
                                <input type="number" name="women_min" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="form-group">
                                <label>Women Max</label>
                                <input type="number" name="women_max" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label>Total Max Athletes</label>
                                <input type="number" name="total_max" class="form-control" value="4">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary font-weight-bold"><i class="fas fa-save mr-1"></i> Save Game & Provision Teams</button>
                </form>
            </div>
        </div>

        <!-- Games List -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold mb-0">Official Sports Disciplines & Squad Limits</h3>
                <span class="badge badge-primary"><?= count($games) ?> Disciplines Configured</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Game Name</th>
                            <th>Category</th>
                            <th>Men (Min-Max)</th>
                            <th>Women (Min-Max)</th>
                            <th>Total Max</th>
                            <th>Format</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($games) > 0): ?>
                            <?php foreach ($games as $g): ?>
                            <tr>
                                <td><strong>#<?= $g['id'] ?></strong></td>
                                <td><strong><i class="<?= htmlspecialchars($g['icon'] ?: 'fas fa-trophy') ?> mr-2 text-primary"></i> <?= htmlspecialchars($g['name']) ?></strong></td>
                                <td><span class="badge badge-info"><?= $g['category'] ?></span></td>
                                <td><?= $g['men_min'] ?> - <?= $g['men_max'] ?></td>
                                <td><?= $g['women_min'] ?> - <?= $g['women_max'] ?></td>
                                <td><span class="badge badge-primary px-2 py-1"><?= $g['total_max'] ?></span></td>
                                <td><?= str_replace('_', ' ', ucwords($g['format'])) ?></td>
                                <td class="text-right">
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this game? All associated teams, fixtures, and scores will be removed.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                        <button type="submit" class="btn btn-xs btn-danger" title="Delete Game">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">No games configured yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
    </div>
