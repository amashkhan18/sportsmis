<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/scorecards.php';

header('Content-Type: application/json');

// Find Bridge Game ID
$stmt = $pdo->query("SELECT id FROM games WHERE slug = 'bridge' LIMIT 1");
$game = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$game) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Bridge game discipline not found.']);
    exit;
}
$gameId = (int)$game['id'];

$swissRoundLabels = [
    1 => 'R-I',
    2 => 'R-II',
    3 => 'R-III',
    4 => 'R-IV',
    5 => 'R-V'
];
$swissRoundNames = [
    1 => 'Round 1 (R-I)',
    2 => 'Round 2 (R-II)',
    3 => 'Round 3 (R-III)',
    4 => 'Round 4 (R-IV)',
    5 => 'Round 5 (R-V)'
];

$slRoundLabels = [
    1 => 'SL-I',
    2 => 'SL-II',
    3 => 'SL-III'
];
$slRoundNames = [
    1 => 'Super League R-1 (SL-I)',
    2 => 'Super League R-2 (SL-II)',
    3 => 'Super League R-3 (SL-III)'
];

// GET: Return current Swiss matrix & Super League Finals data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $swissData = get_bridge_swiss_matrix($pdo, $gameId);
    $slData = get_bridge_super_league_data($pdo, $gameId);

    echo json_encode([
        'status' => 'success',
        'game_id' => $gameId,
        'matrix' => array_values($swissData['matrix']),
        'fixtures' => $swissData['fixtures'],
        'canonical_teams' => $swissData['canonical_teams'],
        'super_league' => [
            'matrix' => array_values($slData['matrix']),
            'fixtures' => $slData['fixtures'],
            'qualified_teams' => $slData['qualified_teams']
        ]
    ]);
    exit;
}

// POST: Save / Update round-wise scores (Swiss or Super League)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload.']);
        exit;
    }

    $stage = trim($input['stage'] ?? 'swiss');
    if ($stage === 'super_league' || $stage === 'finals') {
        $stage = 'super_league';
    } else {
        $stage = 'swiss';
    }

    $roundNo = (int)($input['round_no'] ?? 0);
    $tables = $input['tables'] ?? [];

    if ($stage === 'super_league') {
        if ($roundNo < 1 || $roundNo > 3) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Super League round number. Must be 1 to 3.']);
            exit;
        }
        $roundLabel = $slRoundLabels[$roundNo];
        $roundName = $slRoundNames[$roundNo];
        $matchDate = '2026-10-10';
    } else {
        if ($roundNo < 1 || $roundNo > 5) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Swiss round number. Must be 1 to 5.']);
            exit;
        }
        $roundLabel = $swissRoundLabels[$roundNo];
        $roundName = $swissRoundNames[$roundNo];
        $matchDate = '2026-10-09';
    }

    if (!is_array($tables) || empty($tables)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No table data provided for round ' . $roundLabel]);
        exit;
    }

    $savedCount = 0;
    foreach ($tables as $tbl) {
        $tableNo = (int)($tbl['table_no'] ?? 1);
        $t1Id = !empty($tbl['team1_id']) ? (int)$tbl['team1_id'] : null;
        $t2Id = !empty($tbl['team2_id']) ? (int)$tbl['team2_id'] : null;
        $matchId = !empty($tbl['match_id']) ? (int)$tbl['match_id'] : null;
        
        $vpA = isset($tbl['vps_a']) && $tbl['vps_a'] !== '' ? (float)$tbl['vps_a'] : null;
        $vpB = isset($tbl['vps_b']) && $tbl['vps_b'] !== '' ? (float)$tbl['vps_b'] : null;
        
        // Auto-complete VP if one is entered and the other is blank
        if ($vpA !== null && $vpB === null) {
            $vpB = round(20.00 - $vpA, 2);
        } elseif ($vpB !== null && $vpA === null) {
            $vpA = round(20.00 - $vpB, 2);
        }

        $impA = isset($tbl['imps_a']) ? (int)$tbl['imps_a'] : 0;
        $impB = isset($tbl['imps_b']) ? (int)$tbl['imps_b'] : 0;
        $status = in_array($tbl['status'] ?? '', ['completed', 'in_progress', 'scheduled']) ? $tbl['status'] : 'scheduled';
        
        // Auto-detect status if scores are entered
        if ($status === 'scheduled' && ($vpA !== null || $vpB !== null)) {
            $status = 'completed';
        }

        $winnerId = null;
        if ($status === 'completed' && $t1Id && $t2Id && $vpA !== null && $vpB !== null) {
            if ($vpA > $vpB) $winnerId = $t1Id;
            elseif ($vpB > $vpA) $winnerId = $t2Id;
        }

        $scoresPayload = [
            'type' => 'bridge',
            'stage' => $stage,
            'round_no' => $roundNo,
            'round_label' => $roundLabel,
            'table_no' => $tableNo,
            'vps_a' => $vpA,
            'vps_b' => $vpB,
            'imps_a' => $impA,
            'imps_b' => $impB,
            'status' => $status,
            'summary' => "{$roundLabel} Table {$tableNo}: " . ($vpA !== null ? "{$vpA} - {$vpB} VPs" : "Scheduled")
        ];

        // Check if match exists
        if (!$matchId) {
            $stmt = $pdo->prepare("
                SELECT id FROM matches 
                WHERE game_id = ? AND round = ? AND pool_name = ? 
                LIMIT 1
            ");
            $stmt->execute([$gameId, $roundName, "Table $tableNo"]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $matchId = (int)$existing['id'];
            }
        }

        if ($matchId) {
            $stmt = $pdo->prepare("
                UPDATE matches 
                SET team1_id = ?, team2_id = ?, winner_id = ?, status = ?, scores_json = ?, pool_name = ?, match_date = ?
                WHERE id = ?
            ");
            $stmt->execute([$t1Id, $t2Id, $winnerId, $status, json_encode($scoresPayload), "Table $tableNo", $matchDate, $matchId]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO matches (game_id, round, pool_name, team1_id, team2_id, winner_id, status, scores_json, match_date, start_time, end_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, '10:00:00', '12:00:00')
            ");
            $stmt->execute([$gameId, $roundName, "Table $tableNo", $t1Id, $t2Id, $winnerId, $status, json_encode($scoresPayload), $matchDate]);
        }
        $savedCount++;
    }

    log_audit_event(
        $pdo,
        3, // Volunteer / Admin user
        'BRIDGE_ROUND_SCORES_SAVED',
        "Saved scores for Bridge [Stage: {$stage}] {$roundLabel} ({$savedCount} tables processed)."
    );

    // Return updated matrix and super league
    $updatedSwiss = get_bridge_swiss_matrix($pdo, $gameId);
    $updatedSL = get_bridge_super_league_data($pdo, $gameId);

    echo json_encode([
        'status' => 'success',
        'message' => ($stage === 'super_league' ? "Super League {$roundLabel}" : "Round {$roundLabel}") . " scores saved successfully!",
        'saved_count' => $savedCount,
        'matrix' => array_values($updatedSwiss['matrix']),
        'fixtures' => $updatedSwiss['fixtures'],
        'super_league' => [
            'matrix' => array_values($updatedSL['matrix']),
            'fixtures' => $updatedSL['fixtures'],
            'qualified_teams' => $updatedSL['qualified_teams']
        ]
    ]);
    exit;
}
