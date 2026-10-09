<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

header('Content-Type: application/json');

// POST: Sync offline/live scores from the volunteer PWA (FR-16, FR-18)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !is_array($data)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
        exit;
    }

    $synced = 0;
    foreach ($data as $match) {
        if (!isset($match['id']) || !isset($match['scores_json'])) continue;
        
        $scoresPayload = $match['scores_json'];
        if (is_string($scoresPayload)) {
            $decoded = json_decode($scoresPayload, true);
            if (is_array($decoded)) {
                $scoresData = $decoded;
            } else {
                $scoresData = [
                    'summary' => $scoresPayload,
                    'entered_by' => 'Volunteer PWA Scorer'
                ];
            }
        } else {
            $scoresData = (array)$scoresPayload;
        }

        // Determine match status (live/in_progress vs completed)
        $matchStatus = 'completed';
        if (isset($match['status']) && in_array($match['status'], ['in_progress', 'completed', 'scheduled'])) {
            $matchStatus = $match['status'];
        } elseif (isset($scoresData['status'])) {
            if ($scoresData['status'] === 'live' || $scoresData['status'] === 'in_progress') {
                $matchStatus = 'in_progress';
            } elseif ($scoresData['status'] === 'walkover' || $scoresData['status'] === 'completed') {
                $matchStatus = 'completed';
            }
        }

        $winnerId = !empty($match['winner_id']) ? (int)$match['winner_id'] : null;
        if ($matchStatus === 'in_progress' && empty($winnerId)) {
            $winnerId = null; // Live matches typically do not have a declared winner yet
        }

        // Update match status and scores
        $stmt = $pdo->prepare("
            UPDATE matches 
            SET scores_json = ?, status = ?, winner_id = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            json_encode($scoresData),
            $matchStatus,
            $winnerId,
            $match['id']
        ]);
        
        // Log the audit event (FR-28)
        $logType = ($matchStatus === 'in_progress') ? 'LIVE_SCORE_UPDATE' : 'FINAL_SCORE_SYNC';
        log_audit_event(
            $pdo, 
            3, // Volunteer Scorer User ID
            $logType, 
            "Match #{$match['id']} updated via Volunteer Scoring PWA. Status: {$matchStatus}. Winner ID: " . ($winnerId ?: 'Pending') . ". Score Summary: " . ($scoresData['summary'] ?? '')
        );
        
        $synced++;
    }
    
    echo json_encode(['status' => 'success', 'synced' => $synced]);
    exit;
}

// GET: Fetch matches for a volunteer / PWA (FR-14)
$whereClause = "m.status IN ('scheduled', 'in_progress', 'completed')";

$stmt = $pdo->query("
    SELECT m.*, 
           g.name as game_name, g.slug as game_slug, g.category, g.format as game_format,
           t1.name as team1_name, u1.short_code as u1_code, u1.color_code as u1_color,
           t2.name as team2_name, u2.short_code as u2_code, u2.color_code as u2_color,
           tw.name as winner_name,
           f.name as facility_name, f.court_number
    FROM matches m 
    JOIN games g ON m.game_id = g.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    LEFT JOIN teams tw ON m.winner_id = tw.id
    LEFT JOIN facilities f ON m.facility_id = f.id
    WHERE $whereClause
    ORDER BY FIELD(m.status, 'in_progress', 'scheduled', 'completed'), m.match_date ASC, m.start_time ASC
");

$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['status' => 'success', 'data' => $matches]);