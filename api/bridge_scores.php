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

$roundLabels = [
    1 => 'R-I',
    2 => 'R-II',
    3 => 'R-III',
    4 => 'R-IV',
    5 => 'R-V'
];
$roundNames = [
    1 => 'Round 1 (R-I)',
    2 => 'Round 2 (R-II)',
    3 => 'Round 3 (R-III)',
    4 => 'Round 4 (R-IV)',
    5 => 'Round 5 (R-V)'
];

// GET: Return current Swiss matrix & round fixtures
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = get_bridge_swiss_matrix($pdo, $gameId);
    echo json_encode([
        'status' => 'success',
        'game_id' => $gameId,
        'matrix' => array_values($data['matrix']),
        'fixtures' => $data['fixtures'],
        'canonical_teams' => $data['canonical_teams']
    ]);
    exit;
}

// POST: Save / Update round-wise scores
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload.']);
        exit;
    }

    $roundNo = (int)($input['round_no'] ?? 0);
    if ($roundNo < 1 || $roundNo > 5) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid round number. Must be 1 to 5.']);
        exit;
    }

    $roundLabel = $roundLabels[$roundNo];
    $roundName = $roundNames[$roundNo];
    $tables = $input['tables'] ?? [];

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

        // Check if existing match exists
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
                SET team1_id = ?, team2_id = ?, winner_id = ?, status = ?, scores_json = ?, pool_name = ?
                WHERE id = ?
            ");
            $stmt->execute([$t1Id, $t2Id, $winnerId, $status, json_encode($scoresPayload), "Table $tableNo", $matchId]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO matches (game_id, round, pool_name, team1_id, team2_id, winner_id, status, scores_json, match_date, start_time, end_time)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, '2026-10-09', '10:00:00', '12:00:00')
            ");
            $stmt->execute([$gameId, $roundName, "Table $tableNo", $t1Id, $t2Id, $winnerId, $status, json_encode($scoresPayload)]);
        }
        $savedCount++;
    }

    log_audit_event(
        $pdo,
        3, // Volunteer / Admin user
        'BRIDGE_ROUND_SCORES_SAVED',
        "Saved round-wise scores for Bridge {$roundLabel} ({$savedCount} tables processed)."
    );

    // Return updated matrix
    $updated = get_bridge_swiss_matrix($pdo, $gameId);
    echo json_encode([
        'status' => 'success',
        'message' => "Round {$roundLabel} scores saved successfully!",
        'saved_count' => $savedCount,
        'matrix' => array_values($updated['matrix']),
        'fixtures' => $updated['fixtures']
    ]);
    exit;
}
