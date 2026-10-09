<?php
require_once 'config/helpers.php';

$title = "Official Match Scorecard Generator | HPCL Tournament 2026";

// Fetch tournament matches
// Fetch tournament matches with custom score formats
$matchesList = $pdo->query("
    SELECT m.id, m.match_date, m.round, m.status, m.scores_json, m.score_format_id,
           g.name as game_name, g.score_format_id as game_score_format_id,
           t1.name as team1_name, u1.short_code as u1_code, u1.name as u1_name,
           t2.name as team2_name, u2.short_code as u2_code, u2.name as u2_name,
           COALESCE(tw.name, '') as winner_name, COALESCE(uw.short_code, '') as winner_code,
           sf.name as format_name, sf.code as format_code, sf.badge_text as format_badge,
           sf.columns_json, sf.total_columns, sf.type as format_type, sf.win_rule, sf.target_wins
    FROM matches m
    JOIN games g ON m.game_id = g.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    LEFT JOIN teams tw ON m.winner_id = tw.id
    LEFT JOIN units uw ON tw.unit_id = uw.id
    LEFT JOIN score_formats sf ON COALESCE(m.score_format_id, g.score_format_id) = sf.id
    ORDER BY (m.status = 'completed') DESC, (m.status = 'in_progress') DESC, m.match_date DESC, m.id DESC
")->fetchAll();

function parseMatchForSocial($m) {
    $scores = json_decode($m['scores_json'] ?? '{}', true) ?: [];
    $isCompleted = ($m['status'] === 'completed');
    $isLive = ($m['status'] === 'in_progress');
    $game = strtolower($m['game_name']);
    
    $p1_name = $m['team1_name'] ?: ($m['round'] ?? 'Race / Heat');
    $p1_unit = !empty($m['u1_code']) ? '(' . $m['u1_code'] . ')' : '(' . ($scores['participants'] ?? 'Heats') . ')';
    $p2_name = $m['team2_name'] ?: ($scores['participants'] ?? 'Field');
    $p2_unit = !empty($m['u2_code']) ? '(' . $m['u2_code'] . ')' : '(All Zones)';

    $h1 = 'GAME 1'; $h2 = 'GAME 2'; $h3 = 'GAME 3'; $h4 = 'GAME 4'; $h5 = 'GAME 5'; $hTot = 'TOTAL';
    $s1_1 = '-'; $s1_2 = '-';
    $s2_1 = '-'; $s2_2 = '-';
    $s3_1 = '-'; $s3_2 = '-';
    $s4_1 = '-'; $s4_2 = '-';
    $s5_1 = '-'; $s5_2 = '-';
    $tot_1 = '-'; $tot_2 = '-';
    $winner = '';
    $tagline = 'Well Played  |  Keep the Momentum Going!';
    $colCount = 3;

    // 0. Custom Score Format Override (Match or Game level)
    if (!empty($m['columns_json'])) {
        $customCols = json_decode($m['columns_json'], true) ?: [];
        $colCount = count($customCols);
        $colHeaders = [];
        $scoresA = [];
        $scoresB = [];
        $wins1 = 0; $wins2 = 0;
        $totPts1 = 0; $totPts2 = 0;
        $hasNumeric = false;

        for ($i = 0; $i < min(5, $colCount); $i++) {
            $col = $customCols[$i];
            $k = $col['key'];
            $colHeaders[$i] = !empty($col['short']) ? $col['short'] : strtoupper($k);
            $vA = $scores[$k . '_a'] ?? ($scores[$k . '_1'] ?? ($scores["g" . ($i + 1) . "_a"] ?? ($scores["s" . ($i + 1) . "_a"] ?? '-')));
            $vB = $scores[$k . '_b'] ?? ($scores[$k . '_2'] ?? ($scores["g" . ($i + 1) . "_b"] ?? ($scores["s" . ($i + 1) . "_b"] ?? '-')));
            $scoresA[$i] = (string)$vA;
            $scoresB[$i] = (string)$vB;
            if (is_numeric($vA) && is_numeric($vB)) {
                $hasNumeric = true;
                $totPts1 += (int)$vA;
                $totPts2 += (int)$vB;
                if ((int)$vA > (int)$vB) $wins1++;
                elseif ((int)$vB > (int)$vA) $wins2++;
            }
        }

        $h1 = $colHeaders[0] ?? 'GAME 1';
        $h2 = $colHeaders[1] ?? 'GAME 2';
        $h3 = $colHeaders[2] ?? 'GAME 3';
        $h4 = $colHeaders[3] ?? 'GAME 4';
        $h5 = $colHeaders[4] ?? 'GAME 5';
        $hTot = ($m['win_rule'] ?? '') === 'total_score' ? 'TOTAL' : 'SETS';

        $s1_1 = $scoresA[0] ?? '-'; $s1_2 = $scoresB[0] ?? '-';
        $s2_1 = $scoresA[1] ?? '-'; $s2_2 = $scoresB[1] ?? '-';
        $s3_1 = $scoresA[2] ?? '-'; $s3_2 = $scoresB[2] ?? '-';
        $s4_1 = $scoresA[3] ?? '-'; $s4_2 = $scoresB[3] ?? '-';
        $s5_1 = $scoresA[4] ?? '-'; $s5_2 = $scoresB[4] ?? '-';

        if (($m['win_rule'] ?? '') === 'total_score') {
            $tot_1 = $hasNumeric ? (string)$totPts1 : '-';
            $tot_2 = $hasNumeric ? (string)$totPts2 : '-';
        } else {
            $tot_1 = $hasNumeric ? (string)$wins1 : '-';
            $tot_2 = $hasNumeric ? (string)$wins2 : '-';
        }

        if ($isCompleted) {
            $winner = $m['winner_name'] ? ('WINNER: ' . strtoupper($m['winner_name'])) : ($wins1 > $wins2 ? ('WINNER: ' . strtoupper($m['team1_name'])) : ($wins2 > $wins1 ? ('WINNER: ' . strtoupper($m['team2_name'])) : 'MATCH CONCLUDED'));
            $tagline = !empty($m['format_badge']) ? ($m['format_badge'] . '  |  Official Result Recorded') : 'Well Played  |  Keep the Momentum Going!';
        } elseif ($isLive) {
            $winner = 'MATCH IN PROGRESS';
            $tagline = !empty($m['format_badge']) ? ($m['format_badge'] . '  |  Action Underway') : 'Live Action  |  Court Underway';
        } else {
            $winner = 'UPCOMING FIXTURE';
            $tagline = 'Match Scheduled  |  Balewadi Sports Complex';
        }

    } elseif (strpos($game, 'swimming') !== false) {
        $h1 = 'TIME'; $h2 = 'LANE'; $h3 = 'STATUS'; $hTot = 'MEDAL';
        $lanes = $scores['lanes'] ?? [];
        $firstLane = null;
        $secondLane = null;

        foreach ($lanes as $laneKey => $ln) {
            $pos = strtolower(trim((string)($ln['pos'] ?? $ln['position'] ?? '')));
            if ($pos === '1st' || $pos === '1' || $pos === 'gold') {
                $firstLane = $ln;
            } elseif ($pos === '2nd' || $pos === '2' || $pos === 'silver') {
                $secondLane = $ln;
            }
        }
        if (!$firstLane && !empty($lanes)) {
            $laneVals = array_values($lanes);
            $firstLane = $laneVals[0] ?? null;
            $secondLane = $laneVals[1] ?? null;
        }

        if ($firstLane) {
            if (!empty($firstLane['swimmer'])) $p1_name = $firstLane['swimmer'];
            if (!empty($firstLane['zone'])) $p1_unit = '(' . $firstLane['zone'] . ')';
            $s1_1 = $firstLane['time'] ?? ($scores['winner_time'] ?? '-');
            $s2_1 = !empty($firstLane['lane']) ? 'Lane ' . $firstLane['lane'] : 'Lane 2';
            $s3_1 = strtoupper($firstLane['status'] ?? 'NORMAL');
            $tot_1 = 'GOLD';
        } else {
            $s1_1 = '--:--.--'; $s2_1 = 'Lane 1'; $s3_1 = 'NORMAL'; $tot_1 = '-';
        }

        if ($secondLane) {
            if (!empty($secondLane['swimmer'])) $p2_name = $secondLane['swimmer'];
            if (!empty($secondLane['zone'])) $p2_unit = '(' . $secondLane['zone'] . ')';
            $s1_2 = $secondLane['time'] ?? '-';
            $s2_2 = !empty($secondLane['lane']) ? 'Lane ' . $secondLane['lane'] : 'Lane 3';
            $s3_2 = strtoupper($secondLane['status'] ?? 'NORMAL');
            $tot_2 = 'SILVER';
        } else {
            $s1_2 = '--:--.--'; $s2_2 = 'Lane 2'; $s3_2 = 'NORMAL'; $tot_2 = '-';
        }

        $winName = $scores['winner_name'] ?? ($firstLane['swimmer'] ?? ($m['winner_name'] ?? ''));
        if ($isCompleted) {
            $winner = $winName ? ('GOLD: ' . strtoupper($winName)) : 'GOLD MEDALIST DECLARED';
            $tagline = 'Sensational Race  |  New Tournament Record!';
        } elseif ($isLive) {
            $winner = 'LIVE RACE IN PROGRESS';
            $tagline = 'Live from Poolside  |  Balewadi Aquatic Center';
        } else {
            $winner = 'UPCOMING RACE';
            $tagline = 'Scheduled Race  |  Balewadi Aquatic Center';
            $s1_1 = '--:--.--'; $s1_2 = '--:--.--';
            $s3_1 = 'SCHEDULED'; $s3_2 = 'SCHEDULED';
            $tot_1 = '-'; $tot_2 = '-';
        }

    } elseif (strpos($game, 'chess') !== false) {
        $h1 = 'BOARD'; $h2 = 'COLOR'; $h3 = 'POINTS'; $hTot = 'RESULT';
        $boardNo = $scores['board_no'] ?? '1';
        $whitePlayer = $scores['white']['player'] ?? ($scores['white_player'] ?? $m['team1_name']);
        $whiteZone = $scores['white']['zone'] ?? ($scores['white_zone'] ?? $m['u1_code']);
        $blackPlayer = $scores['black']['player'] ?? ($scores['black_player'] ?? $m['team2_name']);
        $blackZone = $scores['black']['zone'] ?? ($scores['black_zone'] ?? $m['u2_code']);

        $p1_name = $whitePlayer;
        $p1_unit = '(' . $whiteZone . ')';
        $p2_name = $blackPlayer;
        $p2_unit = '(' . $blackZone . ')';

        $s1_1 = 'B' . $boardNo; $s1_2 = 'B' . $boardNo;
        $s2_1 = 'WHITE'; $s2_2 = 'BLACK';

        $res = $scores['result'] ?? '';
        $ptA = isset($scores['points_a']) ? (string)$scores['points_a'] : ($res === '1-0' ? '1.0' : ($res === '1/2-1/2' ? '0.5' : ($res === '0-1' ? '0.0' : '-')));
        $ptB = isset($scores['points_b']) ? (string)$scores['points_b'] : ($res === '0-1' ? '1.0' : ($res === '1/2-1/2' ? '0.5' : ($res === '1-0' ? '0.0' : '-')));
        $s3_1 = $ptA; $s3_2 = $ptB;

        if ($isCompleted) {
            if ($res === '1-0') {
                $tot_1 = 'WIN'; $tot_2 = 'LOSS';
                $winner = 'WINNER: ' . strtoupper($whitePlayer) . ' (1.0)';
            } elseif ($res === '0-1') {
                $tot_1 = 'LOSS'; $tot_2 = 'WIN';
                $winner = 'WINNER: ' . strtoupper($blackPlayer) . ' (1.0)';
            } elseif ($res === '1/2-1/2') {
                $tot_1 = 'DRAW'; $tot_2 = 'DRAW';
                $winner = 'MATCH DRAW (0.5 - 0.5)';
            } else {
                $tot_1 = 'WIN'; $tot_2 = 'LOSS';
                $winner = $m['winner_name'] ? ('WINNER: ' . strtoupper($m['winner_name'])) : 'MATCH CONCLUDED';
            }
            $tagline = 'Grandmaster Clash  |  Tactical Masterclass!';
        } elseif ($isLive) {
            $tot_1 = 'LIVE'; $tot_2 = 'LIVE';
            $winner = 'GAME IN PROGRESS';
            $tagline = 'Live on Board ' . $boardNo . '  |  Every Move Counts';
        } else {
            $tot_1 = '-'; $tot_2 = '-';
            $winner = 'UPCOMING FIXTURE';
            $tagline = 'Match Scheduled  |  Balewadi Chess Arena';
        }

    } elseif (strpos($game, 'carrom') !== false) {
        $h1 = 'BOARD'; $h2 = 'POINTS'; $h3 = 'SLAMS'; $hTot = 'RESULT';
        $boardNo = $scores['board_no'] ?? '1';
        $ptA = isset($scores['points_a']) ? (int)$scores['points_a'] : '-';
        $ptB = isset($scores['points_b']) ? (int)$scores['points_b'] : '-';

        $s1_1 = 'B' . $boardNo; $s1_2 = 'B' . $boardNo;
        $s2_1 = (string)$ptA; $s2_2 = (string)$ptB;
        $s3_1 = (string)($scores['white_slam'] ?? '0');
        $s3_2 = (string)($scores['black_slam'] ?? '0');

        if ($isCompleted) {
            $tot_1 = ($ptA !== '-' && $ptB !== '-' && $ptA > $ptB) ? 'WIN' : (($ptA !== '-' && $ptB !== '-' && $ptA < $ptB) ? 'LOSS' : 'WIN');
            $tot_2 = ($ptA !== '-' && $ptB !== '-' && $ptB > $ptA) ? 'WIN' : (($ptA !== '-' && $ptB !== '-' && $ptB < $ptA) ? 'LOSS' : 'LOSS');
            $winner = $m['winner_name'] ? ('WINNER: ' . strtoupper($m['winner_name'])) : (($ptA > $ptB) ? ('WINNER: ' . strtoupper($m['team1_name'])) : ('WINNER: ' . strtoupper($m['team2_name'])));
            $tagline = 'Precision Strike  |  Carrom Board Action!';
        } elseif ($isLive) {
            $tot_1 = 'LIVE'; $tot_2 = 'LIVE';
            $winner = 'BOARD IN PROGRESS';
            $tagline = 'Live Striking  |  Carrom Arena';
        } else {
            $s2_1 = '-'; $s2_2 = '-';
            $s3_1 = '-'; $s3_2 = '-';
            $tot_1 = '-'; $tot_2 = '-';
            $winner = 'UPCOMING FIXTURE';
            $tagline = 'Match Scheduled  |  Balewadi Carrom Arena';
        }

    } elseif (strpos($game, 'bridge') !== false) {
        $h1 = 'IMPs'; $h2 = 'VPs'; $h3 = 'TABLE'; $hTot = 'RESULT';
        $impA = isset($scores['imps_a']) ? (int)$scores['imps_a'] : '-';
        $impB = isset($scores['imps_b']) ? (int)$scores['imps_b'] : '-';
        $vpA = isset($scores['vps_a']) ? (float)$scores['vps_a'] : '-';
        $vpB = isset($scores['vps_b']) ? (float)$scores['vps_b'] : '-';
        $tableNo = $scores['table_no'] ?? '1';

        $s1_1 = (string)$impA; $s1_2 = (string)$impB;
        $s2_1 = is_numeric($vpA) ? number_format($vpA, 2) : '-';
        $s2_2 = is_numeric($vpB) ? number_format($vpB, 2) : '-';
        $s3_1 = 'T' . $tableNo; $s3_2 = 'T' . $tableNo;

        if ($isCompleted) {
            $tot_1 = (is_numeric($vpA) && is_numeric($vpB) && $vpA >= $vpB) ? 'WIN' : 'LOSS';
            $tot_2 = (is_numeric($vpA) && is_numeric($vpB) && $vpB >= $vpA) ? 'WIN' : 'LOSS';
            if ($m['winner_name']) {
                $winner = 'WINNER: ' . strtoupper($m['winner_name']);
            } elseif (is_numeric($vpA) && is_numeric($vpB)) {
                $winner = ($vpA >= $vpB) ? ('WINNER: ' . strtoupper($m['team1_name']) . ' (' . number_format($vpA, 2) . ' VPs)') : ('WINNER: ' . strtoupper($m['team2_name']) . ' (' . number_format($vpB, 2) . ' VPs)');
            } else {
                $winner = 'MATCH CONCLUDED';
            }
            $tagline = 'Master Tacticians  |  Bridge Team Championship!';
        } elseif ($isLive) {
            $tot_1 = 'LIVE'; $tot_2 = 'LIVE';
            $winner = 'SESSION IN PROGRESS';
            $tagline = 'Live Duplicate Bridge  |  Balewadi Card Room';
        } else {
            $s1_1 = '-'; $s1_2 = '-';
            $s2_1 = '-'; $s2_2 = '-';
            $tot_1 = '-'; $tot_2 = '-';
            $winner = 'UPCOMING FIXTURE';
            $tagline = 'Session Scheduled  |  Balewadi Card Room';
        }

    } elseif (strpos($game, 'table tennis') !== false || strpos($game, 'badminton') !== false) {
        // Badminton (Best of 3), Table Tennis Men (Best of 5), Table Tennis Women (Best of 3)
        $h1 = 'GAME 1'; $h2 = 'GAME 2'; $h3 = 'GAME 3'; $hTot = 'TOTAL';
        $s1_1 = $scores['g1_a'] ?? '-'; $s1_2 = $scores['g1_b'] ?? '-';
        $s2_1 = $scores['g2_a'] ?? '-'; $s2_2 = $scores['g2_b'] ?? '-';
        $s3_1 = $scores['g3_a'] ?? '-'; $s3_2 = $scores['g3_b'] ?? '-';
        $s4_1 = $scores['g4_a'] ?? '-'; $s4_2 = $scores['g4_b'] ?? '-';
        $s5_1 = $scores['g5_a'] ?? '-'; $s5_2 = $scores['g5_b'] ?? '-';

        $allGames = [[$s1_1, $s1_2], [$s2_1, $s2_2], [$s3_1, $s3_2], [$s4_1, $s4_2], [$s5_1, $s5_2]];

        $w1 = 0; $w2 = 0;
        foreach ($allGames as $g) {
            if (is_numeric($g[0]) && is_numeric($g[1])) {
                if ((int)$g[0] > (int)$g[1]) $w1++;
                elseif ((int)$g[1] > (int)$g[0]) $w2++;
            }
        }
        $tot_1 = ($w1 > 0 || $w2 > 0) ? (string)$w1 : '-';
        $tot_2 = ($w1 > 0 || $w2 > 0) ? (string)$w2 : '-';

        if ($isCompleted) {
            $winner = $m['winner_name'] ? ('WINNER: ' . strtoupper($m['winner_name'])) : (($w1 > $w2) ? ('WINNER: ' . strtoupper($m['team1_name'])) : 'MATCH CONCLUDED');
            $tagline = strpos($game, 'table tennis') !== false ? 'Lightning Reflexes  |  Paddles & Spin!' : 'High Energy Rallies  |  Smash & Power!';
        } elseif ($isLive) {
            $winner = 'MATCH IN PROGRESS';
            $tagline = 'Live Point Update  |  Court Action Underway';
        } else {
            $winner = 'UPCOMING FIXTURE';
            $tagline = 'Fixture Scheduled  |  Stay Tuned for Live Action!';
        }

    } elseif (strpos($game, 'tennis') !== false) {
        // Lawn Tennis (Best of 3 Sets)
        $h1 = 'SET 1'; $h2 = 'SET 2'; $h3 = 'SET 3'; $hTot = 'TOTAL';
        $s1_1 = $scores['s1_a'] ?? ($scores['sets'][0]['t1'] ?? '-');
        $s1_2 = $scores['s1_b'] ?? ($scores['sets'][0]['t2'] ?? '-');
        $s2_1 = $scores['s2_a'] ?? ($scores['sets'][1]['t1'] ?? '-');
        $s2_2 = $scores['s2_b'] ?? ($scores['sets'][1]['t2'] ?? '-');
        $s3_1 = $scores['s3_a'] ?? ($scores['sets'][2]['t1'] ?? '-');
        $s3_2 = $scores['s3_b'] ?? ($scores['sets'][2]['t2'] ?? '-');

        $w1 = 0; $w2 = 0;
        foreach ([[$s1_1, $s1_2], [$s2_1, $s2_2], [$s3_1, $s3_2]] as $set) {
            if (is_numeric($set[0]) && is_numeric($set[1])) {
                if ((int)$set[0] > (int)$set[1]) $w1++;
                elseif ((int)$set[1] > (int)$set[0]) $w2++;
            }
        }
        $tot_1 = ($w1 > 0 || $w2 > 0) ? (string)$w1 : '-';
        $tot_2 = ($w1 > 0 || $w2 > 0) ? (string)$w2 : '-';

        if ($isCompleted) {
            $winner = $m['winner_name'] ? ('WINNER: ' . strtoupper($m['winner_name'])) : (($w1 > $w2) ? ('WINNER: ' . strtoupper($m['team1_name'])) : 'MATCH CONCLUDED');
            $tagline = 'Championship Point  |  Tennis Court Dominance!';
        } elseif ($isLive) {
            $winner = 'MATCH IN PROGRESS';
            $ptsA = $scores['pts_a'] ?? ($scores['live_points']['t1'] ?? '0');
            $ptsB = $scores['pts_b'] ?? ($scores['live_points']['t2'] ?? '0');
            $tagline = "Live Game: {$ptsA} - {$ptsB}  |  Centre Court";
        } else {
            $winner = 'UPCOMING FIXTURE';
            $tagline = 'Match Scheduled  |  Centre Court Balewadi';
        }

    } else {
        // Generic fallback
        $h1 = 'SET 1'; $h2 = 'SET 2'; $h3 = 'SET 3'; $hTot = 'TOTAL';
        $winner = $m['winner_name'] ? ('WINNER: ' . strtoupper($m['winner_name'])) : 'FIXTURE SCHEDULED';
    }

    return [
        'game' => $m['game_name'],
        'p1_name' => $p1_name,
        'p1_unit' => $p1_unit,
        'p2_name' => $p2_name,
        'p2_unit' => $p2_unit,
        'col_count' => isset($colCount) ? min(5, $colCount) : 3,
        'h1' => $h1,
        'h2' => $h2,
        'h3' => $h3,
        'h4' => $h4 ?? 'GAME 4',
        'h5' => $h5 ?? 'GAME 5',
        'h_tot' => $hTot,
        's1_1' => (string)$s1_1,
        's1_2' => (string)$s1_2,
        's2_1' => (string)$s2_1,
        's2_2' => (string)$s2_2,
        's3_1' => (string)$s3_1,
        's3_2' => (string)$s3_2,
        's4_1' => (string)($s4_1 ?? '-'),
        's4_2' => (string)($s4_2 ?? '-'),
        's5_1' => (string)($s5_1 ?? '-'),
        's5_2' => (string)($s5_2 ?? '-'),
        'tot_1' => (string)$tot_1,
        'tot_2' => (string)$tot_2,
        'badge' => $m['format_badge'] ?? '',
        'winner' => $winner,
        'tagline' => $tagline,
        'status' => $m['status']
    ];
}

// Fetch all albums
$albumsList = $pdo->query("
    SELECT a.*, g.name as game_name, COUNT(p.id) as photo_count
    FROM albums a
    LEFT JOIN games g ON a.game_id = g.id
    LEFT JOIN photos p ON a.id = p.album_id
    GROUP BY a.id
    ORDER BY a.created_at DESC, a.id DESC
")->fetchAll();

// Fetch all photos with their album info
$allPhotosList = $pdo->query("
    SELECT p.*, a.title as album_title, g.name as game_name
    FROM photos p
    JOIN albums a ON p.album_id = a.id
    LEFT JOIN games g ON p.game_id = g.id
    ORDER BY p.album_id DESC, p.uploaded_at DESC, p.id ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- Google Fonts for athletic typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,700;0,800;0,900;1,700;1,900&family=Oswald:wght@600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
    <style>
        .canvas-card-wrapper {
            position: relative;
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            border: 2px solid rgba(11, 58, 114, 0.6);
            border-radius: 12px;
            overflow: hidden;
            background: #06152b;
            box-shadow: 0 14px 40px rgba(0,0,0,0.7);
        }
        .canvas-container-landscape {
            aspect-ratio: 1024 / 685;
        }
        .canvas-container-square {
            aspect-ratio: 1 / 1;
        }
        canvas {
            width: 100%;
            height: 100%;
            display: block;
            cursor: grab;
        }
        canvas:active {
            cursor: grabbing !important;
        }
        .photo-card {
            position: relative;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            background: #111827;
            transition: all 0.2s ease;
        }
        .photo-card:hover {
            border-color: #3b82f6;
            transform: translateY(-2px);
        }
        .photo-card.selected {
            border-color: #ffc107;
            box-shadow: 0 0 12px rgba(255, 193, 7, 0.6);
        }
        .photo-card img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            display: block;
        }
        .photo-card .photo-caption {
            font-size: 0.72rem;
            padding: 4px 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            background: rgba(0,0,0,0.8);
            color: #e5e7eb;
        }
        .photo-card .check-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #ffc107;
            color: #000;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: bold;
        }
        .photo-card.selected .check-badge {
            display: flex;
        }
        .table-score-input {
            width: 58px;
            text-align: center;
            font-weight: bold;
            font-size: 1rem;
        }
        .tab-btn.active {
            background-color: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
        }
        .form-label-custom {
            font-weight: 600;
            color: #f1f5f9;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }
        .album-pill {
            cursor: pointer;
            font-size: 0.78rem;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #374151;
            background: #1f2937;
            color: #d1d5db;
            transition: all 0.2s;
        }
        .album-pill:hover, .album-pill.active {
            background: #2563eb;
            color: #fff;
            border-color: #3b82f6;
        }
        .zoom-drag-panel {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 215, 0, 0.25);
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-dark text-white">

    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm">
        <div class="container">
            <a class="navbar-brand text-white d-flex align-items-center fw-bold" href="<?= BASE_URL ?>">
                <i class="fas fa-arrow-left me-2"></i> HPCL Tournament Portal
            </a>
            <span class="navbar-text text-warning font-weight-bold ms-auto">
                <i class="fas fa-certificate me-1"></i> Official Scorecard Generator
            </span>
        </div>
    </nav>

    <div class="container-fluid px-lg-5 py-4">
        <div class="text-center mb-4">
            <span class="badge bg-warning text-dark px-3 py-2 text-uppercase fw-bold mb-2">
                <i class="fas fa-award me-1"></i> Official Media Asset Studio
            </span>
            <h2 class="display-6 fw-bold">Official Match Scorecard Generator</h2>
            <p class="text-muted">Inter Unit Sports Tournament &bull; Play &bull; Compete &bull; Connect &bull; Broadcast Ready</p>
        </div>

        <div class="row g-4">
            <!-- Left Column: Controls & Form Inputs -->
            <div class="col-xl-5 col-lg-6">
                <div class="glass-card p-4">
                    
                    <!-- Design Format Selector -->
                    <div class="mb-4">
                        <label class="form-label-custom text-warning text-uppercase d-block">
                            <i class="fas fa-layer-group me-1"></i> Scorecard Template Layout
                        </label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary tab-btn active py-2" id="btnModeLandscape" onclick="setTemplateMode('landscape')">
                                <i class="fas fa-image me-1"></i> Official Tournament Poster (3:2)
                            </button>
                            <button type="button" class="btn btn-outline-primary tab-btn py-2" id="btnModeSquare" onclick="setTemplateMode('square')">
                                <i class="fas fa-square me-1"></i> Square Feed (1:1)
                            </button>
                        </div>
                    </div>

                    <!-- 1. Match Selection Dropdown -->
                    <div class="mb-4">
                        <label class="form-label-custom text-info text-uppercase d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-list-check me-1"></i> 1. Select Match Data Source</span>
                            <span class="badge bg-info text-dark">Auto-Fill</span>
                        </label>
                        <select id="matchSelect" class="form-select bg-dark text-white border-secondary">
                            <option value="sample" selected>
                                [Sample Match] Badminton: Player Name (HPCL) vs Opponent (10-21, 11-21, 10-21)
                            </option>
                            <?php 
                                $completedMatches = [];
                                $liveMatches = [];
                                $scheduledMatches = [];
                                foreach ($matchesList as $m) {
                                    if ($m['status'] === 'completed') $completedMatches[] = $m;
                                    elseif ($m['status'] === 'in_progress') $liveMatches[] = $m;
                                    else $scheduledMatches[] = $m;
                                }
                            ?>
                            <?php if (!empty($completedMatches)): ?>
                                <optgroup label="✓ Completed Results (Official Scores)">
                                    <?php foreach ($completedMatches as $m): ?>
                                        <?php 
                                            $mObj = parseMatchForSocial($m); 
                                            $pairLabel = (!empty($m['u1_code']) && !empty($m['u2_code'])) ? "{$m['u1_code']} vs {$m['u2_code']}" : "{$m['round']}";
                                        ?>
                                        <option value='<?= htmlspecialchars(json_encode($mObj), ENT_QUOTES) ?>'>
                                            [Completed] <?= htmlspecialchars($m['game_name']) ?>: <?= htmlspecialchars($pairLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <?php if (!empty($liveMatches)): ?>
                                <optgroup label="🔴 Live Matches (In Progress)">
                                    <?php foreach ($liveMatches as $m): ?>
                                        <?php 
                                            $mObj = parseMatchForSocial($m); 
                                            $pairLabel = (!empty($m['u1_code']) && !empty($m['u2_code'])) ? "{$m['u1_code']} vs {$m['u2_code']}" : "{$m['round']}";
                                        ?>
                                        <option value='<?= htmlspecialchars(json_encode($mObj), ENT_QUOTES) ?>'>
                                            [Live] <?= htmlspecialchars($m['game_name']) ?>: <?= htmlspecialchars($pairLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                            <?php if (!empty($scheduledMatches)): ?>
                                <optgroup label="📅 Scheduled Fixtures (Upcoming)">
                                    <?php foreach ($scheduledMatches as $m): ?>
                                        <?php 
                                            $mObj = parseMatchForSocial($m); 
                                            $pairLabel = (!empty($m['u1_code']) && !empty($m['u2_code'])) ? "{$m['u1_code']} vs {$m['u2_code']}" : "{$m['round']}";
                                        ?>
                                        <option value='<?= htmlspecialchars(json_encode($mObj), ENT_QUOTES) ?>'>
                                            [Scheduled] <?= htmlspecialchars($m['game_name']) ?>: <?= htmlspecialchars($pairLabel) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- 2. Live Editable Scorecard Table -->
                    <div class="mb-4 p-3 rounded" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12);">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label-custom text-warning text-uppercase mb-0">
                                <i class="fas fa-table me-1"></i> 2. Scorecard Table Data
                            </label>
                            <span class="badge bg-secondary">Real-Time Sync</span>
                        </div>
                        
                        <!-- Table Column Headers Guide -->
                        <div class="d-flex align-items-center mb-1 text-muted small px-1" style="font-size: 0.75rem;">
                            <span class="flex-grow-1 fw-bold text-light">TEAMS / PLAYERS</span>
                            <div class="d-flex gap-1 text-center justify-content-end" id="scoreHeadersWrapper" style="width: 290px;">
                                <span id="colHeader1" style="width: 48px;" class="fw-bold text-light">GAME 1</span>
                                <span id="colHeader2" style="width: 48px;" class="fw-bold text-light">GAME 2</span>
                                <span id="colHeader3" style="width: 48px;" class="fw-bold text-light">GAME 3</span>
                                <span id="colHeader4" style="width: 48px; display: none;" class="fw-bold text-light">GAME 4</span>
                                <span id="colHeader5" style="width: 48px; display: none;" class="fw-bold text-light">GAME 5</span>
                                <span id="colHeaderTot" style="width: 48px;" class="fw-bold text-warning">TOTAL</span>
                            </div>
                        </div>

                        <!-- Row 1: Player / Team 1 -->
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="flex-grow-1">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="p1Name" class="form-control bg-dark text-white border-secondary fw-bold" value="Player Name" placeholder="Player 1 Name">
                                    <input type="text" id="p1Unit" class="form-control bg-dark text-white border-secondary" style="max-width: 90px;" value="(HPCL)" placeholder="(Unit)">
                                </div>
                            </div>
                            <div class="d-flex gap-1 justify-content-end" id="p1ScoresWrapper" style="width: 290px;">
                                <input type="text" id="s1_p1" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px;" value="10" title="Col 1">
                                <input type="text" id="s2_p1" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px;" value="11" title="Col 2">
                                <input type="text" id="s3_p1" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px;" value="10" title="Col 3">
                                <input type="text" id="s4_p1" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px; display: none;" value="-" title="Col 4">
                                <input type="text" id="s5_p1" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px; display: none;" value="-" title="Col 5">
                                <input type="text" id="tot_p1" class="form-control form-control-sm bg-dark text-warning border-secondary table-score-input fw-bold" style="width: 48px;" value="-" title="Total">
                            </div>
                        </div>

                        <!-- Row 2: Player / Team 2 -->
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="flex-grow-1">
                                <div class="input-group input-group-sm">
                                    <input type="text" id="p2Name" class="form-control bg-dark text-white border-secondary fw-bold" value="Opponent" placeholder="Opponent Name">
                                    <input type="text" id="p2Unit" class="form-control bg-dark text-white border-secondary" style="max-width: 90px;" value="" placeholder="(Unit)">
                                </div>
                            </div>
                            <div class="d-flex gap-1 justify-content-end" id="p2ScoresWrapper" style="width: 290px;">
                                <input type="text" id="s1_p2" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px;" value="21" title="Col 1">
                                <input type="text" id="s2_p2" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px;" value="21" title="Col 2">
                                <input type="text" id="s3_p2" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px;" value="21" title="Col 3">
                                <input type="text" id="s4_p2" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px; display: none;" value="-" title="Col 4">
                                <input type="text" id="s5_p2" class="form-control form-control-sm bg-dark text-white border-secondary table-score-input" style="width: 48px; display: none;" value="-" title="Col 5">
                                <input type="text" id="tot_p2" class="form-control form-control-sm bg-dark text-warning border-secondary table-score-input fw-bold" style="width: 48px;" value="-" title="Total">
                            </div>
                        </div>

                        <!-- Sport & Tagline -->
                        <div class="row g-2 pt-2 border-top border-secondary">
                            <div class="col-6">
                                <label class="form-label-custom d-flex justify-content-between align-items-center">
                                    <span>Sport Discipline:</span>
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">Auto From Match</span>
                                </label>
                                <div class="py-1 px-3 rounded bg-dark border border-secondary text-info fw-bold d-flex align-items-center" style="height: 31px; font-size: 0.85rem;">
                                    <i class="fas fa-medal text-warning me-2"></i>
                                    <span id="sportDisplay">Badminton</span>
                                </div>
                                <input type="hidden" id="sportName" value="Badminton">
                            </div>
                            <div class="col-6">
                                <label class="form-label-custom">Winner Banner Text:</label>
                                <input type="text" id="winnerText" class="form-control form-control-sm bg-dark text-white border-secondary" value="WINNER">
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label-custom">Bottom Tagline:</label>
                                <input type="text" id="taglineInput" class="form-control form-control-sm bg-dark text-white border-secondary" value="Well Played  |  Keep the Momentum Going!">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Photo Gallery by Album (Select from gallery) -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label-custom text-success text-uppercase mb-0">
                                <i class="fas fa-images me-1"></i> 3. Athlete Photo (Album / Upload)
                            </label>
                            <span class="small text-muted" id="photoCountBadge"><?= count($allPhotosList) ?> Photos</span>
                        </div>

                        <!-- Direct Photo Upload / Actions -->
                        <div class="d-flex gap-2 mb-2">
                            <input type="file" id="customPhotoUpload" accept="image/*" style="display:none;" onchange="handleCustomPhotoUpload(event)">
                            <button type="button" class="btn btn-sm btn-outline-warning w-100" onclick="document.getElementById('customPhotoUpload').click()">
                                <i class="fas fa-upload me-1"></i> Upload Athlete Photo
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearScorecardPhoto()" title="Remove selected photo">
                                <i class="fas fa-times me-1"></i> Clear
                            </button>
                        </div>

                        <!-- Album Selector Pills -->
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <button type="button" class="album-pill active" onclick="filterByAlbum('all', this)">
                                All Albums
                            </button>
                            <?php foreach ($albumsList as $a): ?>
                                <button type="button" class="album-pill" onclick="filterByAlbum(<?= $a['id'] ?>, this)">
                                    <?= htmlspecialchars($a['title']) ?> (<?= $a['photo_count'] ?>)
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Photo Grid -->
                        <div class="row g-2 overflow-auto p-1 mb-3" style="max-height: 200px;" id="photoGalleryGrid">
                            <?php if (empty($allPhotosList)): ?>
                                <div class="col-12 text-muted text-center py-4 small">
                                    <i class="fas fa-camera me-1"></i> No album photos available. You can upload an athlete photo above.
                                </div>
                            <?php endif; ?>

                            <!-- Database Photos from Albums -->
                            <?php foreach ($allPhotosList as $p): ?>
                                <?php 
                                    $photoUrl = BASE_URL . '/' . ($p['compressed_path'] ?: $p['file_path']); 
                                ?>
                                <div class="col-4 photo-item" data-album="<?= $p['album_id'] ?>">
                                    <div class="photo-card" onclick="selectScorecardPhoto('<?= $photoUrl ?>', this)">
                                        <div class="check-badge"><i class="fas fa-check"></i></div>
                                        <img src="<?= $photoUrl ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                                        <div class="photo-caption" title="<?= htmlspecialchars($p['title']) ?>">
                                            <?= htmlspecialchars($p['title']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>

                        <!-- Zoom & Pan Controls (Interactive for any selected photo) -->
                        <div class="zoom-drag-panel p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-warning">
                                    <i class="fas fa-search-plus me-1"></i> Zoom & Pan Photo
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-dark border border-secondary text-warning" id="zoomVal">1.00x</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary text-light py-0 px-2" onclick="resetZoomPan()" title="Reset to Center">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                            <input type="range" id="zoomSlider" min="0.4" max="3.0" step="0.05" value="1.0" class="form-range mb-1">
                            <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                                <span><i class="fas fa-arrows-alt me-1 text-info"></i> Click & drag photo on canvas to reposition</span>
                                <span>Scroll wheel zooms in/out</span>
                            </div>
                        </div>

                    </div>

                    <!-- Download & Actions -->
                    <div class="d-grid gap-2">
                        <button id="downloadBtn" class="btn btn-warning btn-lg fw-bold shadow py-3">
                            <i class="fas fa-download me-2"></i> Download High-Res Scorecard (PNG)
                        </button>
                        <button id="copyBtn" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-copy me-1"></i> Copy Image to Clipboard
                        </button>
                    </div>

                </div>
            </div>

            <!-- Right Column: Interactive Canvas Preview -->
            <div class="col-xl-7 col-lg-6 text-center">
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <span class="badge bg-primary px-3 py-2 text-uppercase fw-bold">
                        <i class="fas fa-eye me-1"></i> Live High-Res Broadcast Canvas
                    </span>
                    <span class="text-muted small" id="resBadge">1024 &times; 685 (Official Poster)</span>
                </div>

                <div class="canvas-card-wrapper canvas-container-landscape" id="canvasWrapper">
                    <canvas id="scorecardCanvas" width="1024" height="685"></canvas>
                </div>

                <div class="mt-3 text-muted small">
                    <span class="badge bg-secondary me-2">Interactive: Drag Photo to Pan • Wheel to Zoom</span>
                    <span>#HPCLInterUnit2026 &bull; Stronger Together &bull; Balewadi Pune</span>
                </div>
            </div>
        </div>
    </div>


    <script>
        const canvas = document.getElementById('scorecardCanvas');
        const ctx = canvas.getContext('2d');
        const canvasWrapper = document.getElementById('canvasWrapper');
        const resBadge = document.getElementById('resBadge');

        // State variables
        let templateMode = 'landscape'; // 'landscape' or 'square'
        let selectedPhotoType = null; // null or custom photo URL
        let activeImg = null; // Active Athlete Image object (starts null - no default stencil)
        
        // Helper to draw rounded rectangles reliably on canvas
        function drawRoundRect(c, x, y, w, h, radius, fillStyle = null, strokeStyle = null, lineWidth = 1) {
            c.save();
            c.beginPath();
            if (typeof c.roundRect === 'function') {
                c.roundRect(x, y, w, h, radius);
            } else {
                const r = typeof radius === 'number' ? radius : 8;
                c.moveTo(x + r, y);
                c.lineTo(x + w - r, y);
                c.quadraticCurveTo(x + w, y, x + w, y + r);
                c.lineTo(x + w, y + h - r);
                c.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                c.lineTo(x + r, y + h);
                c.quadraticCurveTo(x, y + h, x, y + h - r);
                c.lineTo(x, y + r);
                c.quadraticCurveTo(x, y, x + r, y);
            }
            c.closePath();
            if (fillStyle) {
                c.fillStyle = fillStyle;
                c.fill();
            }
            if (strokeStyle) {
                c.strokeStyle = strokeStyle;
                c.lineWidth = lineWidth;
                c.stroke();
            }
            c.restore();
        }
        


        // Zoom and Pan state
        let imgX = 0, imgY = 0, imgScale = 1.0;
        let isDragging = false;
        let startX, startY;

        // Form elements
        const matchSelect = document.getElementById('matchSelect');
        const p1Name = document.getElementById('p1Name');
        const p1Unit = document.getElementById('p1Unit');
        const p2Name = document.getElementById('p2Name');
        const p2Unit = document.getElementById('p2Unit');
        const s1_p1 = document.getElementById('s1_p1');
        const s2_p1 = document.getElementById('s2_p1');
        const s3_p1 = document.getElementById('s3_p1');
        const s4_p1 = document.getElementById('s4_p1');
        const s5_p1 = document.getElementById('s5_p1');
        const tot_p1 = document.getElementById('tot_p1');
        const s1_p2 = document.getElementById('s1_p2');
        const s2_p2 = document.getElementById('s2_p2');
        const s3_p2 = document.getElementById('s3_p2');
        const s4_p2 = document.getElementById('s4_p2');
        const s5_p2 = document.getElementById('s5_p2');
        const tot_p2 = document.getElementById('tot_p2');
        const sportName = document.getElementById('sportName');
        const winnerText = document.getElementById('winnerText');
        const taglineInput = document.getElementById('taglineInput');
        const zoomSlider = document.getElementById('zoomSlider');
        const zoomVal = document.getElementById('zoomVal');
        const btnModeLandscape = document.getElementById('btnModeLandscape');
        const btnModeSquare = document.getElementById('btnModeSquare');
        const downloadBtn = document.getElementById('downloadBtn');
        const copyBtn = document.getElementById('copyBtn');

        // Mode switch
        function setTemplateMode(mode) {
            templateMode = mode;
            if (mode === 'landscape') {
                canvas.width = 1024;
                canvas.height = 685;
                canvasWrapper.className = 'canvas-card-wrapper canvas-container-landscape';
                resBadge.innerText = '1024 × 685 (Official Poster)';
                btnModeLandscape.classList.add('active');
                btnModeSquare.classList.remove('active');
            } else {
                canvas.width = 1080;
                canvas.height = 1080;
                canvasWrapper.className = 'canvas-card-wrapper canvas-container-square';
                resBadge.innerText = '1080 × 1080 (Square Feed)';
                btnModeSquare.classList.add('active');
                btnModeLandscape.classList.remove('active');
            }
            renderScorecard();
        }

        // Reset Pan & Zoom
        function resetZoomPan() {
            imgX = 0;
            imgY = 0;
            imgScale = 1.0;
            zoomSlider.value = 1.0;
            zoomVal.innerText = '1.00x';
            renderScorecard();
        }

        // Zoom Slider Listener
        zoomSlider.addEventListener('input', (e) => {
            imgScale = parseFloat(e.target.value);
            zoomVal.innerText = imgScale.toFixed(2) + 'x';
            renderScorecard();
        });

        // Mouse Drag to Pan directly on Canvas
        canvas.addEventListener('mousedown', (e) => {
            isDragging = true;
            canvas.style.cursor = 'grabbing';
            const rect = canvas.getBoundingClientRect();
            startX = (e.clientX - rect.left) * (canvas.width / rect.width);
            startY = (e.clientY - rect.top) * (canvas.height / rect.height);
        });

        window.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                canvas.style.cursor = 'grab';
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.clientX - rect.left) * (canvas.width / rect.width);
            const mouseY = (e.clientY - rect.top) * (canvas.height / rect.height);
            imgX += (mouseX - startX);
            imgY += (mouseY - startY);
            startX = mouseX;
            startY = mouseY;
            renderScorecard();
        });

        // Mouse Wheel to Zoom directly on Canvas
        canvas.addEventListener('wheel', (e) => {
            e.preventDefault();
            const zoomDelta = e.deltaY < 0 ? 0.05 : -0.05;
            imgScale = Math.min(3.0, Math.max(0.4, imgScale + zoomDelta));
            zoomSlider.value = imgScale;
            zoomVal.innerText = imgScale.toFixed(2) + 'x';
            renderScorecard();
        }, { passive: false });

        // Touch Support for mobile/tablets
        canvas.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                isDragging = true;
                const rect = canvas.getBoundingClientRect();
                startX = (e.touches[0].clientX - rect.left) * (canvas.width / rect.width);
                startY = (e.touches[0].clientY - rect.top) * (canvas.height / rect.height);
            }
        }, { passive: true });

        canvas.addEventListener('touchend', () => { isDragging = false; });

        canvas.addEventListener('touchmove', (e) => {
            if (!isDragging || e.touches.length !== 1) return;
            const rect = canvas.getBoundingClientRect();
            const mouseX = (e.touches[0].clientX - rect.left) * (canvas.width / rect.width);
            const mouseY = (e.touches[0].clientY - rect.top) * (canvas.height / rect.height);
            imgX += (mouseX - startX);
            imgY += (mouseY - startY);
            startX = mouseX;
            startY = mouseY;
            renderScorecard();
        }, { passive: true });

        // Filter Photos by Album
        function filterByAlbum(albumId, el) {
            document.querySelectorAll('.album-pill').forEach(b => b.classList.remove('active'));
            el.classList.add('active');

            const items = document.querySelectorAll('.photo-item');
            let visibleCount = 0;
            items.forEach(item => {
                const itemAlbum = item.getAttribute('data-album');
                if (albumId === 'all') {
                    item.style.display = 'block';
                    visibleCount++;
                } else if (itemAlbum == albumId) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            document.getElementById('photoCountBadge').innerText = visibleCount + ' Photos';
        }

        // Select Photo from Gallery
        function selectScorecardPhoto(photoUrl, cardEl) {
            document.querySelectorAll('.photo-card').forEach(c => c.classList.remove('selected'));
            if (cardEl) cardEl.classList.add('selected');

            selectedPhotoType = photoUrl;
            if (!photoUrl || photoUrl === 'default') {
                activeImg = null;
                resetZoomPan();
                renderScorecard();
                return;
            }

            activeImg = new Image();
            activeImg.crossOrigin = "anonymous";
            activeImg.onload = () => {
                resetZoomPan();
                renderScorecard();
            };
            activeImg.src = photoUrl;
        }

        // Clear Athlete Photo
        function clearScorecardPhoto() {
            document.querySelectorAll('.photo-card').forEach(c => c.classList.remove('selected'));
            selectedPhotoType = null;
            activeImg = null;
            resetZoomPan();
            renderScorecard();
        }

        // Custom Photo Upload handler
        function handleCustomPhotoUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                selectScorecardPhoto(evt.target.result, null);
            };
            reader.readAsDataURL(file);
        }

        let currentHeaders = { h1: 'GAME 1', h2: 'GAME 2', h3: 'GAME 3', h4: 'GAME 4', h5: 'GAME 5', hTot: 'TOTAL', count: 3 };

        function updateHeadersUI(h1, h2, h3, h4, h5, hTot, count = 3) {
            currentHeaders = {
                h1: h1 || 'GAME 1',
                h2: h2 || 'GAME 2',
                h3: h3 || 'GAME 3',
                h4: h4 || 'GAME 4',
                h5: h5 || 'GAME 5',
                hTot: hTot || 'TOTAL',
                count: parseInt(count) || 3
            };
            const el1 = document.getElementById('colHeader1');
            const el2 = document.getElementById('colHeader2');
            const el3 = document.getElementById('colHeader3');
            const el4 = document.getElementById('colHeader4');
            const el5 = document.getElementById('colHeader5');
            const elTot = document.getElementById('colHeaderTot');

            if (el1) el1.innerText = currentHeaders.h1;
            if (el2) el2.innerText = currentHeaders.h2;
            if (el3) el3.innerText = currentHeaders.h3;
            if (el4) {
                el4.innerText = currentHeaders.h4;
                el4.style.display = currentHeaders.count >= 4 ? '' : 'none';
            }
            if (el5) {
                el5.innerText = currentHeaders.h5;
                el5.style.display = currentHeaders.count >= 5 ? '' : 'none';
            }
            if (elTot) elTot.innerText = currentHeaders.hTot;

            // Show / hide input columns 4 and 5
            if (s4_p1) s4_p1.style.display = currentHeaders.count >= 4 ? '' : 'none';
            if (s4_p2) s4_p2.style.display = currentHeaders.count >= 4 ? '' : 'none';
            if (s5_p1) s5_p1.style.display = currentHeaders.count >= 5 ? '' : 'none';
            if (s5_p2) s5_p2.style.display = currentHeaders.count >= 5 ? '' : 'none';

            // Tooltips
            [s1_p1, s1_p2].forEach(el => { if (el) { el.title = currentHeaders.h1; el.placeholder = currentHeaders.h1; } });
            [s2_p1, s2_p2].forEach(el => { if (el) { el.title = currentHeaders.h2; el.placeholder = currentHeaders.h2; } });
            [s3_p1, s3_p2].forEach(el => { if (el) { el.title = currentHeaders.h3; el.placeholder = currentHeaders.h3; } });
            [s4_p1, s4_p2].forEach(el => { if (el) { el.title = currentHeaders.h4; el.placeholder = currentHeaders.h4; } });
            [s5_p1, s5_p2].forEach(el => { if (el) { el.title = currentHeaders.h5; el.placeholder = currentHeaders.h5; } });
            [tot_p1, tot_p2].forEach(el => { if (el) { el.title = currentHeaders.hTot; el.placeholder = currentHeaders.hTot; } });
        }

        // Match select change
        matchSelect.addEventListener('change', () => {
            let matchSport = 'Badminton';
            if (matchSelect.value === 'sample') {
                p1Name.value = 'Player Name';
                p1Unit.value = '(HPCL)';
                p2Name.value = 'Opponent';
                p2Unit.value = '';
                s1_p1.value = '21'; s1_p2.value = '10';
                s2_p1.value = '21'; s2_p2.value = '11';
                s3_p1.value = '21'; s3_p2.value = '10';
                s4_p1.value = '-'; s4_p2.value = '-';
                s5_p1.value = '-'; s5_p2.value = '-';
                tot_p1.value = '3'; tot_p2.value = '0';
                matchSport = 'Badminton';
                winnerText.value = 'WINNER: PLAYER NAME';
                taglineInput.value = 'High Energy Rallies  |  Keep the Momentum Going!';
                updateHeadersUI('GAME 1', 'GAME 2', 'GAME 3', 'GAME 4', 'GAME 5', 'TOTAL', 3);
            } else {
                try {
                    const d = JSON.parse(matchSelect.value);
                    p1Name.value = d.p1_name || 'Player 1';
                    p1Unit.value = d.p1_unit || '';
                    p2Name.value = d.p2_name || 'Player 2';
                    p2Unit.value = d.p2_unit || '';
                    s1_p1.value = d.s1_1 || '-'; s1_p2.value = d.s1_2 || '-';
                    s2_p1.value = d.s2_1 || '-'; s2_p2.value = d.s2_2 || '-';
                    s3_p1.value = d.s3_1 || '-'; s3_p2.value = d.s3_2 || '-';
                    s4_p1.value = d.s4_1 || '-'; s4_p2.value = d.s4_2 || '-';
                    s5_p1.value = d.s5_1 || '-'; s5_p2.value = d.s5_2 || '-';
                    tot_p1.value = d.tot_1 || '-'; tot_p2.value = d.tot_2 || '-';
                    matchSport = d.game || 'Badminton';
                    winnerText.value = d.winner || (d.status === 'scheduled' ? 'UPCOMING FIXTURE' : 'WINNER');
                    taglineInput.value = d.tagline || 'Well Played  |  Keep the Momentum Going!';
                    updateHeadersUI(d.h1, d.h2, d.h3, d.h4, d.h5, d.h_tot, d.col_count || 3);
                } catch(e){}
            }

            // Sport Discipline is auto-derived from the match
            sportName.value = matchSport;
            const sportDisplay = document.getElementById('sportDisplay');
            if (sportDisplay) sportDisplay.innerText = matchSport;

            // Auto-filter photo gallery to matching album if available
            const sportKeyword = matchSport.toLowerCase().split(' ')[0];
            const albumPills = Array.from(document.querySelectorAll('.album-pill'));
            const matchingPill = albumPills.find(p => p.innerText.toLowerCase().includes(sportKeyword));
            if (matchingPill) {
                matchingPill.click();
            }

            renderScorecard();
        });

        // If sport name manually updated
        sportName.addEventListener('change', () => {
            const sp = sportName.value;
            const sportDisplay = document.getElementById('sportDisplay');
            if (sportDisplay) sportDisplay.innerText = sp;
            renderScorecard();
        });

        // Live input listeners
        [p1Name, p1Unit, p2Name, p2Unit, s1_p1, s2_p1, s3_p1, tot_p1, s1_p2, s2_p2, s3_p2, tot_p2, sportName, winnerText, taglineInput].forEach(inp => {
            inp.addEventListener('input', renderScorecard);
        });

        // Core Render Function
        function renderScorecard() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (templateMode === 'landscape') {
                renderLandscape();
            } else {
                renderSquare();
            }
        }

        // Render Landscape (1024x685 Official match scorecard design - Pure Canvas Vector Rendering)
        function renderLandscape() {
            // Background
            ctx.fillStyle = "#f1f5f9";
            ctx.fillRect(0, 0, 1024, 685);

            // 1. Top Header Bar (Full Width)
            ctx.fillStyle = "#071e3d";
            ctx.fillRect(0, 0, 1024, 64);
            // Top gold accent line
            ctx.fillStyle = "#ffc107";
            ctx.fillRect(0, 0, 1024, 4);

            // HPCL Crest Badge
            ctx.save();
            ctx.beginPath();
            ctx.arc(38, 34, 18, 0, Math.PI * 2);
            ctx.fillStyle = "#072b61";
            ctx.fill();
            ctx.strokeStyle = "#ffc107";
            ctx.lineWidth = 2.5;
            ctx.stroke();
            ctx.fillStyle = "#ffc107";
            ctx.font = "bold 11px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText("HPCL", 38, 34);
            ctx.restore();

            // Tournament Title
            ctx.save();
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 20px 'Montserrat', 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "left";
            ctx.textBaseline = "middle";
            ctx.fillText("HPCL INTER-UNIT SPORTS TOURNAMENT 2026", 70, 34);

            // Right header badge
            drawRoundRect(ctx, 840, 18, 160, 32, 16, "#0b3a72", "rgba(255,255,255,0.2)", 1);
            ctx.fillStyle = "#ffc107";
            ctx.font = "bold 11px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText("★ OFFICIAL SCORECARD", 920, 34);
            ctx.restore();

            // 2. Athlete Photo Area (Left Card, x: 20, y: 76, w: 430, h: 550)
            const photoCardX = 20, photoCardY = 76, photoCardW = 430, photoCardH = 550;
            drawRoundRect(ctx, photoCardX, photoCardY, photoCardW, photoCardH, 12, "#ffffff", "#cbd5e1", 1.5);

            // Draw Photo inside clipped container if active
            if (activeImg && activeImg.complete && activeImg.naturalWidth > 0) {
                ctx.save();
                ctx.beginPath();
                if (typeof ctx.roundRect === 'function') {
                    ctx.roundRect(photoCardX + 2, photoCardY + 2, photoCardW - 4, photoCardH - 4, 10);
                } else {
                    ctx.rect(photoCardX + 2, photoCardY + 2, photoCardW - 4, photoCardH - 4);
                }
                ctx.clip();

                // Subtle inner backdrop
                let bgGrad = ctx.createLinearGradient(photoCardX, photoCardY, photoCardX, photoCardY + photoCardH);
                bgGrad.addColorStop(0, "#e8f1fc");
                bgGrad.addColorStop(1, "#f8fafc");
                ctx.fillStyle = bgGrad;
                ctx.fillRect(photoCardX, photoCardY, photoCardW, photoCardH);

                const targetW = photoCardW;
                const targetH = photoCardH;
                const imgRatio = activeImg.naturalWidth / activeImg.naturalHeight;
                const targetRatio = targetW / targetH;
                let baseW, baseH;
                if (imgRatio > targetRatio) {
                    baseH = targetH;
                    baseW = targetH * imgRatio;
                } else {
                    baseW = targetW;
                    baseH = targetW / imgRatio;
                }

                const drawW = baseW * imgScale;
                const drawH = baseH * imgScale;
                const drawX = photoCardX + (targetW - drawW) / 2 + imgX;
                const drawY = photoCardY + (targetH - drawH) / 2 + imgY;

                ctx.drawImage(activeImg, drawX, drawY, drawW, drawH);

                // Subtle bottom gradient vignette over photo
                let vigGrad = ctx.createLinearGradient(photoCardX, photoCardY + photoCardH - 120, photoCardX, photoCardY + photoCardH);
                vigGrad.addColorStop(0, "rgba(7, 30, 61, 0)");
                vigGrad.addColorStop(1, "rgba(7, 30, 61, 0.75)");
                ctx.fillStyle = vigGrad;
                ctx.fillRect(photoCardX, photoCardY + photoCardH - 120, photoCardW, 120);

                ctx.restore();

                // Athlete card bottom badge
                drawRoundRect(ctx, photoCardX + 18, photoCardY + photoCardH - 38, 175, 26, 13, "rgba(7, 30, 61, 0.85)", "#ffc107", 1);
                ctx.fillStyle = "#ffc107";
                ctx.font = "bold 11px 'Segoe UI', Arial, sans-serif";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.fillText("⚡ ATHLETE SPOTLIGHT", photoCardX + 105, photoCardY + photoCardH - 25);
            } else {
                // High-Prestige HPCL Crest Placeholder when no photo is selected
                ctx.save();
                let cardGrad = ctx.createLinearGradient(photoCardX, photoCardY, photoCardX, photoCardY + photoCardH);
                cardGrad.addColorStop(0, "#0b2546");
                cardGrad.addColorStop(1, "#071e3d");
                drawRoundRect(ctx, photoCardX + 2, photoCardY + 2, photoCardW - 4, photoCardH - 4, 10, cardGrad);

                // Decorative sports rings
                ctx.strokeStyle = "rgba(255, 193, 7, 0.15)";
                ctx.lineWidth = 3;
                ctx.beginPath();
                ctx.arc(photoCardX + photoCardW / 2, photoCardY + 200, 75, 0, Math.PI * 2);
                ctx.stroke();

                ctx.beginPath();
                ctx.arc(photoCardX + photoCardW / 2, photoCardY + 200, 95, 0, Math.PI * 2);
                ctx.stroke();

                // Emblem in center
                ctx.beginPath();
                ctx.arc(photoCardX + photoCardW / 2, photoCardY + 200, 48, 0, Math.PI * 2);
                ctx.fillStyle = "#ffc107";
                ctx.fill();

                ctx.fillStyle = "#072b61";
                ctx.font = "bold 20px 'Segoe UI', Arial, sans-serif";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.fillText("HPCL", photoCardX + photoCardW / 2, photoCardY + 200);

                // Tournament text
                ctx.fillStyle = "#ffffff";
                ctx.font = "bold 18px 'Montserrat', 'Segoe UI', Arial, sans-serif";
                ctx.fillText("INTER-UNIT SPORTS MEET", photoCardX + photoCardW / 2, photoCardY + 310);

                ctx.fillStyle = "#ffc107";
                ctx.font = "bold 13px 'Segoe UI', Arial, sans-serif";
                ctx.fillText("2026 • ANNUAL CHAMPIONSHIP", photoCardX + photoCardW / 2, photoCardY + 338);

                ctx.fillStyle = "#94a3b8";
                ctx.font = "12px 'Segoe UI', Arial, sans-serif";
                ctx.fillText("Select a photo from albums below", photoCardX + photoCardW / 2, photoCardY + 375);
                ctx.fillText("or click 'Upload Athlete Photo'", photoCardX + photoCardW / 2, photoCardY + 395);

                ctx.restore();
            }

            // 3. Sport Discipline Banner (Right Side)
            const sport = (sportName.value || 'Badminton').trim();
            const cleanSport = sport.replace(/\s*-\s*Open\s*Category/i, '').trim();
            const sportLower = cleanSport.toLowerCase();

            let sportMotto = "FASTER • HIGHER • STRONGER";
            if (sportLower.includes('chess')) sportMotto = "STRATEGY • INTELLECT • FOCUS";
            else if (sportLower.includes('tennis') && !sportLower.includes('table')) sportMotto = "POWER • PRECISION • AGILITY";
            else if (sportLower.includes('table tennis') || sportLower.includes('table-tennis')) sportMotto = "SPEED • SPIN • REFLEXES";
            else if (sportLower.includes('carrom')) sportMotto = "ACCURACY • FOCUS • PRECISION";
            else if (sportLower.includes('bridge')) sportMotto = "TACTICS • TEAMWORK • MASTERY";
            else if (sportLower.includes('swimming')) sportMotto = "SWIFT • ENDURANCE • RECORD";

            const rightX = 468, rightW = 536;

            // Sport Card
            drawRoundRect(ctx, rightX, 76, rightW, 60, 10, "#0b3a72", "#1e4976", 1);
            // Left gold accent strip
            ctx.fillStyle = "#ffc107";
            ctx.fillRect(rightX, 86, 5, 40);

            ctx.save();
            ctx.fillStyle = "#ffffff";
            let sportFontSize = 23;
            if (cleanSport.length > 15) sportFontSize = 19;
            if (cleanSport.length > 22) sportFontSize = 16;
            ctx.font = "italic 900 " + sportFontSize + "px 'Oswald', 'Montserrat', Arial, sans-serif";
            ctx.textAlign = "left";
            ctx.textBaseline = "middle";
            ctx.fillText(cleanSport.toUpperCase() + " • OFFICIAL MATCH", rightX + 20, 96);

            ctx.fillStyle = "#38bdf8";
            ctx.font = "bold 11px 'Segoe UI', Arial, sans-serif";
            ctx.fillText(sportMotto, rightX + 20, 118);
            ctx.restore();

            // 4. Match Score Table Card
            const tableY = 148, tableH = 256;
            drawRoundRect(ctx, rightX, tableY, rightW, tableH, 10, "#ffffff", "#cbd5e1", 1.5);

            // Table Header Bar
            ctx.save();
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(rightX, tableY, rightW, 44, [10, 10, 0, 0]);
            } else {
                ctx.rect(rightX, tableY, rightW, 44);
            }
            ctx.fillStyle = "#072b61";
            ctx.fill();

            // Column Header Labels
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 14px 'Segoe UI', Arial, sans-serif";
            // Dynamic Active Columns Config
            let activeCols = [
                { header: currentHeaders.h1 || "GAME 1", v1: s1_p1.value || "-", v2: s1_p2.value || "-" },
                { header: currentHeaders.h2 || "GAME 2", v1: s2_p1.value || "-", v2: s2_p2.value || "-" },
                { header: currentHeaders.h3 || "GAME 3", v1: s3_p1.value || "-", v2: s3_p2.value || "-" }
            ];
            if (currentHeaders.count >= 4) {
                activeCols.push({ header: currentHeaders.h4 || "GAME 4", v1: s4_p1.value || "-", v2: s4_p2.value || "-" });
            }
            if (currentHeaders.count >= 5) {
                activeCols.push({ header: currentHeaders.h5 || "GAME 5", v1: s5_p1.value || "-", v2: s5_p2.value || "-" });
            }

            const numCols = activeCols.length;
            const startScoreX = (numCols >= 5) ? (rightX + 205) : (numCols === 4 ? (rightX + 235) : (rightX + 265));
            const endScoreX = rightX + 490;
            const scoreAreaW = endScoreX - startScoreX;
            const stepX = scoreAreaW / numCols;
            const boxW = (numCols >= 5) ? 42 : ((numCols === 4) ? 48 : 56);

            // Column Header Labels
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 13px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "left";
            ctx.textBaseline = "middle";
            ctx.fillText("PLAYER / UNIT", rightX + 20, tableY + 22);

            ctx.textAlign = "center";
            activeCols.forEach((ac, idx) => {
                const cx = startScoreX + idx * stepX + (stepX / 2);
                ctx.fillText(ac.header, cx, tableY + 22);
            });

            ctx.fillStyle = "#ffc107";
            ctx.fillText(currentHeaders.hTot || "TOTAL", endScoreX, tableY + 22);
            ctx.restore();

            // Helper to draw a beautiful score box
            function drawScoreBox(text, cx, cy, isTotal = false, customW = null) {
                const str = String(text || '-').trim();
                ctx.save();
                const bW = customW || (isTotal ? Math.max(boxW, 48) : boxW);
                const bH = 38;
                drawRoundRect(
                    ctx,
                    cx - bW / 2,
                    cy - bH / 2,
                    bW,
                    bH,
                    6,
                    isTotal ? "#fef3c7" : "#f1f5f9",
                    isTotal ? "#f59e0b" : "#e2e8f0",
                    1.5
                );

                let sz = 22;
                if (bW < 45) sz = 16;
                else if (bW < 50) sz = 18;
                if (str.length > 9) sz = 11;
                else if (str.length > 7) sz = 13;
                else if (str.length > 5) sz = 15;
                ctx.font = (isTotal ? "900 " : "bold ") + sz + "px 'Segoe UI', Arial, sans-serif";
                ctx.fillStyle = isTotal ? "#b45309" : "#072b61";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.fillText(str, cx, cy);
                ctx.restore();
            }

            // Row 1: Player 1 (y: 192 to 260, midpoint ~226)
            ctx.save();
            ctx.textAlign = "left";
            ctx.textBaseline = "middle";
            ctx.font = "bold " + (numCols >= 5 ? "16" : "18") + "px 'Segoe UI', Arial, sans-serif";
            ctx.fillStyle = "#072b61";
            ctx.fillText(p1Name.value || "Player 1", rightX + 20, 218);

            if (p1Unit.value) {
                drawRoundRect(ctx, rightX + 20, 234, 105, 20, 4, "#e0f2fe");
                ctx.font = "bold 11px 'Segoe UI', Arial, sans-serif";
                ctx.fillStyle = "#0369a1";
                ctx.textAlign = "center";
                ctx.fillText(p1Unit.value, rightX + 72, 244);
            }
            ctx.restore();

            activeCols.forEach((ac, idx) => {
                const cx = startScoreX + idx * stepX + (stepX / 2);
                drawScoreBox(ac.v1, cx, 226);
            });
            drawScoreBox(tot_p1.value || "-", endScoreX, 226, true);

            // Table Row Divider
            ctx.strokeStyle = "#e2e8f0";
            ctx.lineWidth = 1.5;
            ctx.beginPath();
            ctx.moveTo(rightX + 15, 266);
            ctx.lineTo(rightX + rightW - 15, 266);
            ctx.stroke();

            // Row 2: Player 2 / Opponent (y: 270 to 338, midpoint ~304)
            ctx.save();
            ctx.textAlign = "left";
            ctx.textBaseline = "middle";
            ctx.font = "bold " + (numCols >= 5 ? "16" : "18") + "px 'Segoe UI', Arial, sans-serif";
            ctx.fillStyle = "#072b61";
            ctx.fillText(p2Name.value || "Player 2", rightX + 20, 296);

            if (p2Unit.value) {
                drawRoundRect(ctx, rightX + 20, 312, 105, 20, 4, "#f1f5f9");
                ctx.font = "bold 11px 'Segoe UI', Arial, sans-serif";
                ctx.fillStyle = "#475569";
                ctx.textAlign = "center";
                ctx.fillText(p2Unit.value, rightX + 72, 322);
            }
            ctx.restore();

            activeCols.forEach((ac, idx) => {
                const cx = startScoreX + idx * stepX + (stepX / 2);
                drawScoreBox(ac.v2, cx, 304);
            });
            drawScoreBox(tot_p2.value || "-", endScoreX, 304, true);

            // Table Bottom Info Strip
            ctx.save();
            ctx.beginPath();
            if (typeof ctx.roundRect === 'function') {
                ctx.roundRect(rightX, tableY + tableH - 36, rightW, 36, [0, 0, 10, 10]);
            } else {
                ctx.rect(rightX, tableY + tableH - 36, rightW, 36);
            }
            ctx.fillStyle = "#f8fafc";
            ctx.fill();

            // Status indicator
            ctx.beginPath();
            ctx.arc(rightX + 24, tableY + tableH - 18, 4, 0, Math.PI * 2);
            ctx.fillStyle = "#10b981";
            ctx.fill();

            ctx.font = "bold 11px 'Segoe UI', Arial, sans-serif";
            ctx.fillStyle = "#0f766e";
            ctx.textAlign = "left";
            ctx.textBaseline = "middle";
            ctx.fillText("OFFICIAL RESULT RECORDED", rightX + 34, tableY + tableH - 18);

            ctx.fillStyle = "#94a3b8";
            ctx.textAlign = "right";
            ctx.fillText("Inter-Unit Championship 2026", rightX + rightW - 20, tableY + tableH - 18);
            ctx.restore();

            // 5. Winner Banner Card
            const winVal = (winnerText.value || "MATCH WINNER").trim();
            const winGrad = ctx.createLinearGradient(rightX, 416, rightX + rightW, 416);
            winGrad.addColorStop(0, "#f59e0b");
            winGrad.addColorStop(1, "#fbbf24");
            drawRoundRect(ctx, rightX, 416, rightW, 62, 10, winGrad, "#d97706", 1.5);

            ctx.save();
            ctx.fillStyle = "#072b61";
            let winFont = 24;
            if (winVal.length > 28) winFont = 16;
            else if (winVal.length > 22) winFont = 18;
            else if (winVal.length > 16) winFont = 21;
            ctx.font = "italic 900 " + winFont + "px 'Montserrat', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            const winTextToDraw = winVal.toUpperCase().startsWith("WINNER") || winVal.includes("🏆") ? winVal : "🏆 WINNER: " + winVal;
            ctx.fillText(winTextToDraw, rightX + rightW / 2, 447);
            ctx.restore();

            // 6. Tagline Banner
            const tagVal = (taglineInput.value || "Well Played | Keep the Momentum Going!").trim();
            drawRoundRect(ctx, rightX, 490, rightW, 46, 8, "#ffffff", "#cbd5e1", 1.5);

            ctx.save();
            ctx.fillStyle = "#072b61";
            ctx.font = "italic 700 16px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText(tagVal, rightX + rightW / 2, 513);
            ctx.restore();

            // 7. Sportsmanship Accent Strip
            ctx.save();
            ctx.fillStyle = "#64748b";
            ctx.font = "bold 12px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText("SPORTSMANSHIP  •  TEAM SPIRIT  •  EXCELLENCE", rightX + rightW / 2, 560);
            ctx.restore();

            // 8. Bottom Footer Bar (Full Width)
            ctx.fillStyle = "#071e3d";
            ctx.fillRect(0, 642, 1024, 43);
            ctx.fillStyle = "#ffc107";
            ctx.fillRect(0, 642, 1024, 2);

            ctx.save();
            ctx.fillStyle = "#94a3b8";
            ctx.font = "12px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.textBaseline = "middle";
            ctx.fillText("HINDUSTAN PETROLEUM CORPORATION LIMITED  •  ANNUAL INTER-UNIT SPORTS MEET 2026", 512, 664);
            ctx.restore();
        }

        // Render Square (1080x1080) for Instagram
        function renderSquare() {
            ctx.fillStyle = "#071e3d";
            ctx.fillRect(0, 0, 1080, 1080);

            // Draw photo on top half with Zoom & Pan if available
            ctx.save();
            if (activeImg && activeImg.complete && activeImg.naturalWidth > 0) {
                ctx.beginPath();
                ctx.rect(0, 0, 1080, 520);
                ctx.clip();
                const targetW = 1080;
                const targetH = 520;
                const imgRatio = activeImg.naturalWidth / activeImg.naturalHeight;
                const targetRatio = targetW / targetH;
                let baseW, baseH;
                if (imgRatio > targetRatio) {
                    baseH = targetH;
                    baseW = targetH * imgRatio;
                } else {
                    baseW = targetW;
                    baseH = targetW / imgRatio;
                }
                const drawW = baseW * imgScale;
                const drawH = baseH * imgScale;
                const drawX = (targetW - drawW) / 2 + imgX;
                const drawY = (targetH - drawH) / 2 + imgY;
                ctx.drawImage(activeImg, drawX, drawY, drawW, drawH);
            } else {
                let topGrad = ctx.createLinearGradient(0, 0, 0, 520);
                topGrad.addColorStop(0, "#071e3d");
                topGrad.addColorStop(1, "#0b3a72");
                ctx.fillStyle = topGrad;
                ctx.fillRect(0, 0, 1080, 520);

                // Crest in top center
                ctx.beginPath();
                ctx.arc(540, 240, 55, 0, Math.PI * 2);
                ctx.fillStyle = "#ffc107";
                ctx.fill();
                ctx.fillStyle = "#072b61";
                ctx.font = "bold 24px 'Segoe UI', Arial, sans-serif";
                ctx.textAlign = "center";
                ctx.textBaseline = "middle";
                ctx.fillText("HPCL", 540, 240);

                ctx.fillStyle = "#ffffff";
                ctx.font = "bold 20px 'Montserrat', 'Segoe UI', Arial, sans-serif";
                ctx.fillText("ANNUAL SPORTS TOURNAMENT 2026", 540, 330);
            }
            ctx.restore();

            // Dark gradient overlay
            let grad = ctx.createLinearGradient(0, 300, 0, 600);
            grad.addColorStop(0, "rgba(7, 30, 61, 0)");
            grad.addColorStop(1, "rgba(7, 30, 61, 1)");
            ctx.fillStyle = grad;
            ctx.fillRect(0, 300, 1080, 300);

            // Header Ribbon
            ctx.fillStyle = "#ffc107";
            ctx.fillRect(0, 0, 1080, 12);
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 26px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.fillText("INTER UNIT SPORTS TOURNAMENT 2026", 540, 50);

            // Match Scorecard Header Banner with dynamic sport from match
            const sqSport = (sportName.value || 'Badminton').replace(/\s*-\s*Open\s*Category/i, '').trim();
            ctx.fillStyle = "#0b3a72";
            ctx.fillRect(200, 510, 680, 65);
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 30px 'Oswald', Arial, sans-serif";
            ctx.fillText(sqSport.toUpperCase() + " • OFFICIAL SCORECARD", 540, 555);

            // Score Table on Dark Card
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(100, 600, 880, 240);

            // Build active columns array
            let activeColsSq = [
                { header: currentHeaders.h1 || "GAME 1", v1: s1_p1.value || "-", v2: s1_p2.value || "-" },
                { header: currentHeaders.h2 || "GAME 2", v1: s2_p1.value || "-", v2: s2_p2.value || "-" },
                { header: currentHeaders.h3 || "GAME 3", v1: s3_p1.value || "-", v2: s3_p2.value || "-" }
            ];
            if (currentHeaders.count >= 4) {
                activeColsSq.push({ header: currentHeaders.h4 || "GAME 4", v1: s4_p1.value || "-", v2: s4_p2.value || "-" });
            }
            if (currentHeaders.count >= 5) {
                activeColsSq.push({ header: currentHeaders.h5 || "GAME 5", v1: s5_p1.value || "-", v2: s5_p2.value || "-" });
            }

            const numColsSq = activeColsSq.length;
            const startScoreXSq = (numColsSq >= 5) ? 440 : ((numColsSq === 4) ? 480 : 520);
            const endScoreXSq = 920;
            const stepXSq = (endScoreXSq - startScoreXSq) / numColsSq;

            // Table Header
            ctx.fillStyle = "#072b61";
            ctx.fillRect(100, 600, 880, 55);
            ctx.fillStyle = "#ffffff";
            ctx.font = "bold 20px 'Segoe UI', Arial, sans-serif";
            ctx.textAlign = "left";
            ctx.fillText("PLAYER / UNIT", 130, 636);
            ctx.textAlign = "center";
            activeColsSq.forEach((ac, idx) => {
                const cx = startScoreXSq + idx * stepXSq + (stepXSq / 2);
                ctx.fillText(ac.header, cx, 636);
            });
            ctx.fillStyle = "#ffc107";
            ctx.fillText(currentHeaders.hTot || "TOTAL", endScoreXSq, 636);

            // Row 1
            ctx.fillStyle = "#072b61";
            ctx.textAlign = "left";
            ctx.font = "bold " + (numColsSq >= 5 ? "22" : "26") + "px 'Segoe UI', Arial, sans-serif";
            ctx.fillText(p1Name.value + " " + p1Unit.value, 130, 700);
            ctx.textAlign = "center";
            activeColsSq.forEach((ac, idx) => {
                const cx = startScoreXSq + idx * stepXSq + (stepXSq / 2);
                ctx.fillText(ac.v1, cx, 700);
            });
            ctx.fillText(tot_p1.value || "-", endScoreXSq, 700);

            // Divider
            ctx.strokeStyle = "#072b61";
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(100, 730);
            ctx.lineTo(980, 730);
            ctx.stroke();

            // Row 2
            ctx.textAlign = "left";
            ctx.fillText(p2Name.value + " " + p2Unit.value, 130, 790);
            ctx.textAlign = "center";
            activeColsSq.forEach((ac, idx) => {
                const cx = startScoreXSq + idx * stepXSq + (stepXSq / 2);
                ctx.fillText(ac.v2, cx, 790);
            });
            ctx.fillText(tot_p2.value || "-", endScoreXSq, 790);

            // Winner Box
            ctx.fillStyle = "#ffcc00";
            ctx.fillRect(200, 870, 680, 70);
            ctx.fillStyle = "#072b61";
            ctx.font = "italic 900 34px 'Montserrat', Arial, sans-serif";
            ctx.textAlign = "center";
            ctx.fillText("🏆 " + (winnerText.value || "WINNER"), 540, 918);

            // Tagline
            ctx.fillStyle = "#ffffff";
            ctx.font = "italic 600 24px 'Segoe UI', Arial, sans-serif";
            ctx.fillText(taglineInput.value, 540, 980);

            // Footer
            ctx.fillStyle = "#888888";
            ctx.font = "18px 'Segoe UI', Arial, sans-serif";
            ctx.fillText("SPORTSMANSHIP  •  TEAM SPIRIT  •  EXCELLENCE", 540, 1030);
        }

        // Download PNG
        downloadBtn.addEventListener('click', () => {
            const link = document.createElement('a');
            link.download = `HPCL_Scorecard_${p1Name.value.replace(/\s+/g, '_')}_vs_${p2Name.value.replace(/\s+/g, '_')}_${Date.now()}.png`;
            link.href = canvas.toDataURL('image/png', 1.0);
            link.click();
        });

        // Copy to Clipboard
        copyBtn.addEventListener('click', async () => {
            try {
                canvas.toBlob(async (blob) => {
                    const item = new ClipboardItem({ "image/png": blob });
                    await navigator.clipboard.write([item]);
                    const origText = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check text-success me-1"></i> Copied to Clipboard!';
                    setTimeout(() => { copyBtn.innerHTML = origText; }, 2500);
                });
            } catch(e) {
                alert("Clipboard access is not permitted in this browser context. Please use the Download button instead.");
            }
        });

        // Initial render
        renderScorecard();
    </script>
</body>
</html>
