<?php
// Tournament Helper Functions
require_once __DIR__ . '/scorecards.php';

/**
 * Log an audit trail event (FR-28)
 */
function log_audit_event($pdo, $userId, $action, $details) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $action, $details, $ip]);
    } catch (Exception $e) {
        error_log("Audit log failed: " . $e->getMessage());
    }
}

/**
 * System Settings helper functions
 */
function get_system_setting($pdo, $key, $default = null) {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false && $val !== null) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function set_system_setting($pdo, $key, $value) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        return $stmt->execute([$key, $value]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get Overall Championship Leaderboard
 * Supports:
 * 1. 'auto': Auto-computed per 5-3-1 Points Scheme (FR-22, Section 7)
 * 2. 'manual': Admin-managed points, ranks, and medals with manual overrides
 *
 * @param PDO $pdo
 * @param string|null $forceMode Optional 'auto' or 'manual' override
 * @return array
 */
function get_overall_championship_leaderboard($pdo, $forceMode = null) {
    $mode = $forceMode ?? get_system_setting($pdo, 'leaderboard_mode', 'auto');

    // --- Manual Mode ---
    if ($mode === 'manual') {
        $units = $pdo->query("SELECT id, name, short_code, color_code, logo_path, manual_rank, manual_points, manual_gold, manual_silver, manual_bronze, manual_notes FROM units ORDER BY name ASC")->fetchAll();
        $leaderboard = [];
        $hasAnyManualRank = false;

        foreach ($units as $u) {
            $rankVal = ($u['manual_rank'] !== null && $u['manual_rank'] !== '' && (int)$u['manual_rank'] > 0) ? (int)$u['manual_rank'] : null;
            if ($rankVal !== null) {
                $hasAnyManualRank = true;
            }
            $leaderboard[] = [
                'id' => (int)$u['id'],
                'name' => $u['name'],
                'short_code' => $u['short_code'],
                'color_code' => $u['color_code'],
                'logo_path' => $u['logo_path'],
                'gold' => (int)($u['manual_gold'] ?? 0),
                'silver' => (int)($u['manual_silver'] ?? 0),
                'bronze' => (int)($u['manual_bronze'] ?? 0),
                'total_points' => (int)($u['manual_points'] ?? 0),
                'rank' => $rankVal,
                'manual_notes' => $u['manual_notes'] ?? '',
                'game_breakdown' => !empty($u['manual_notes']) ? [$u['manual_notes']] : ['Manually configured by Tournament Admin']
            ];
        }

        // Sort: Units with explicit manual_rank first (1, 2, 3...), then total_points DESC, medals DESC, name ASC
        usort($leaderboard, function($a, $b) {
            $rankA = $a['rank'];
            $rankB = $b['rank'];

            if ($rankA !== null && $rankB !== null) {
                if ($rankA !== $rankB) return $rankA <=> $rankB;
            } elseif ($rankA !== null && $rankB === null) {
                return -1;
            } elseif ($rankA === null && $rankB !== null) {
                return 1;
            }

            if ($b['total_points'] !== $a['total_points']) return $b['total_points'] <=> $a['total_points'];
            if ($b['gold'] !== $a['gold']) return $b['gold'] <=> $a['gold'];
            if ($b['silver'] !== $a['silver']) return $b['silver'] <=> $a['silver'];
            if ($b['bronze'] !== $a['bronze']) return $b['bronze'] <=> $a['bronze'];
            return strcmp($a['name'], $b['name']);
        });

        // If no explicit ranks were given anywhere, auto-assign ranks if points exist
        if (!$hasAnyManualRank) {
            $prevRow = null;
            $prevRank = 1;
            foreach ($leaderboard as $idx => &$row) {
                if ($row['total_points'] <= 0) {
                    $row['rank'] = null;
                    continue;
                }
                if ($prevRow !== null && 
                    $row['total_points'] === $prevRow['total_points'] &&
                    $row['gold'] === $prevRow['gold'] &&
                    $row['silver'] === $prevRow['silver'] &&
                    $row['bronze'] === $prevRow['bronze']
                ) {
                    $row['rank'] = $prevRank;
                } else {
                    $row['rank'] = $idx + 1;
                    $prevRank = $idx + 1;
                }
                $prevRow = $row;
            }
            unset($row);
        }

        return array_values($leaderboard);
    }

    // --- Automatic Mode (5-3-1 Points Scheme) ---
    // 1. Fetch points scheme
    $pointsMap = [1 => 5, 2 => 3, 3 => 1];
    $psRows = $pdo->query("SELECT position, points FROM points_scheme ORDER BY position ASC")->fetchAll();
    foreach ($psRows as $r) {
        $pointsMap[$r['position']] = (int)$r['points'];
    }

    // 2. Fetch all units
    $units = $pdo->query("SELECT id, name, short_code, color_code, logo_path FROM units ORDER BY name ASC")->fetchAll();
    $leaderboard = [];
    foreach ($units as $u) {
        $leaderboard[$u['id']] = [
            'id' => $u['id'],
            'name' => $u['name'],
            'short_code' => $u['short_code'],
            'color_code' => $u['color_code'],
            'logo_path' => $u['logo_path'],
            'gold' => 0,
            'silver' => 0,
            'bronze' => 0,
            'total_points' => 0,
            'game_breakdown' => []
        ];
    }

    // 3. Scan completed matches for tournament podiums / finals
    $matches = $pdo->query("
        SELECT m.id, m.game_id, m.round, m.winner_id, m.team1_id, m.team2_id, m.scores_json,
               g.name as game_name, g.category,
               t1.unit_id as t1_unit, t2.unit_id as t2_unit, tw.unit_id as win_unit
        FROM matches m
        JOIN games g ON m.game_id = g.id
        LEFT JOIN teams t1 ON m.team1_id = t1.id
        LEFT JOIN teams t2 ON m.team2_id = t2.id
        LEFT JOIN teams tw ON m.winner_id = tw.id
        WHERE m.status = 'completed'
    ")->fetchAll();

    foreach ($matches as $m) {
        $round = strtolower($m['round']);
        $scores = json_decode($m['scores_json'], true) ?? [];

        // Special handling for Swimming Final
        if (isset($scores['format']) && $scores['format'] === 'swimming' && !empty($scores['placements'])) {
            foreach ($scores['placements'] as $pl) {
                // Find unit by short code
                $pUnit = null;
                foreach ($units as $u) {
                    if (strtoupper($u['short_code']) === strtoupper($pl['team'])) {
                        $pUnit = $u['id'];
                        break;
                    }
                }
                if ($pUnit && isset($leaderboard[$pUnit])) {
                    $rank = (int)$pl['rank'];
                    $pts = $pointsMap[$rank] ?? 0;
                    if ($rank === 1) $leaderboard[$pUnit]['gold']++;
                    if ($rank === 2) $leaderboard[$pUnit]['silver']++;
                    if ($rank === 3) $leaderboard[$pUnit]['bronze']++;
                    $leaderboard[$pUnit]['total_points'] += $pts;
                    $leaderboard[$pUnit]['game_breakdown'][] = "Swimming (Rank $rank: +$pts pts)";
                }
            }
            continue;
        }

        // Finals across games (Gold = Winner, Silver = Runner-up)
        if (strpos($round, 'final') !== false && strpos($round, 'semi') === false && strpos($round, 'quarter') === false) {
            $winUnit = $m['win_unit'];
            $loseUnit = ($m['winner_id'] == $m['team1_id']) ? $m['t2_unit'] : $m['t1_unit'];

            if ($winUnit && isset($leaderboard[$winUnit])) {
                $goldPts = $pointsMap[1] ?? 5;
                $leaderboard[$winUnit]['gold']++;
                $leaderboard[$winUnit]['total_points'] += $goldPts;
                $leaderboard[$winUnit]['game_breakdown'][] = "{$m['game_name']} (1st: +$goldPts pts)";
            }
            if ($loseUnit && isset($leaderboard[$loseUnit])) {
                $silverPts = $pointsMap[2] ?? 3;
                $leaderboard[$loseUnit]['silver']++;
                $leaderboard[$loseUnit]['total_points'] += $silverPts;
                $leaderboard[$loseUnit]['game_breakdown'][] = "{$m['game_name']} (2nd: +$silverPts pts)";
            }
        }
        // 3rd Place Match
        else if (strpos($round, '3rd') !== false || strpos($round, 'bronze') !== false) {
            $bronzeUnit = $m['win_unit'];
            if ($bronzeUnit && isset($leaderboard[$bronzeUnit])) {
                $bronzePts = $pointsMap[3] ?? 1;
                $leaderboard[$bronzeUnit]['bronze']++;
                $leaderboard[$bronzeUnit]['total_points'] += $bronzePts;
                $leaderboard[$bronzeUnit]['game_breakdown'][] = "{$m['game_name']} (3rd: +$bronzePts pts)";
            }
        }
    }

    // Sort by Total Points DESC -> Gold DESC -> Silver DESC -> Bronze DESC -> Unit Name ASC
    usort($leaderboard, function($a, $b) {
        if ($b['total_points'] !== $a['total_points']) return $b['total_points'] <=> $a['total_points'];
        if ($b['gold'] !== $a['gold']) return $b['gold'] <=> $a['gold'];
        if ($b['silver'] !== $a['silver']) return $b['silver'] <=> $a['silver'];
        if ($b['bronze'] !== $a['bronze']) return $b['bronze'] <=> $a['bronze'];
        return strcmp($a['name'], $b['name']);
    });

    // Compute dynamic ranks (only assigned when points are scored; zero-point units remain unranked)
    $prevRow = null;
    $prevRank = 1;
    foreach ($leaderboard as $idx => &$row) {
        if ($row['total_points'] <= 0) {
            $row['rank'] = null; // Unranked if zero points
            continue;
        }

        if ($prevRow !== null && 
            $row['total_points'] === $prevRow['total_points'] &&
            $row['gold'] === $prevRow['gold'] &&
            $row['silver'] === $prevRow['silver'] &&
            $row['bronze'] === $prevRow['bronze']
        ) {
            $row['rank'] = $prevRank; // Tied rank
        } else {
            $row['rank'] = $idx + 1;
            $prevRank = $idx + 1;
        }
        $prevRow = $row;
    }
    unset($row);

    return array_values($leaderboard);
}

/**
 * Auto-compute Pool Standings for a specific game (FR-20)
 */
function get_pool_standings($pdo, $gameId) {
    // Get all teams for this game
    $stmt = $pdo->prepare("
        SELECT t.id, t.name, t.pool, u.short_code, u.color_code
        FROM teams t
        JOIN units u ON t.unit_id = u.id
        WHERE t.game_id = ?
        ORDER BY t.pool ASC, t.seed ASC
    ");
    $stmt->execute([$gameId]);
    $teams = $stmt->fetchAll();

    $standings = [];
    foreach ($teams as $t) {
        $pool = $t['pool'] ?: 'A';
        $standings[$pool][$t['id']] = [
            'team_id' => $t['id'],
            'name' => $t['name'],
            'short_code' => $t['short_code'],
            'color_code' => $t['color_code'],
            'played' => 0,
            'won' => 0,
            'lost' => 0,
            'points' => 0,
            'sets_for' => 0,
            'sets_against' => 0
        ];
    }

    // Fetch completed matches in this game
    $stmt = $pdo->prepare("
        SELECT id, team1_id, team2_id, winner_id, pool_name, scores_json
        FROM matches
        WHERE game_id = ? AND status = 'completed'
    ");
    $stmt->execute([$gameId]);
    $matches = $stmt->fetchAll();

    foreach ($matches as $m) {
        $t1 = $m['team1_id'];
        $t2 = $m['team2_id'];
        $winner = $m['winner_id'];
        $pool = $m['pool_name'] ?: 'A';

        if (isset($standings[$pool][$t1])) {
            $standings[$pool][$t1]['played']++;
            if ($winner == $t1) {
                $standings[$pool][$t1]['won']++;
                $standings[$pool][$t1]['points'] += 2;
            } else {
                $standings[$pool][$t1]['lost']++;
            }
        }

        if (isset($standings[$pool][$t2])) {
            $standings[$pool][$t2]['played']++;
            if ($winner == $t2) {
                $standings[$pool][$t2]['won']++;
                $standings[$pool][$t2]['points'] += 2;
            } else {
                $standings[$pool][$t2]['lost']++;
            }
        }

        // Set/game difference tally if sets or games are present
        $scores = json_decode($m['scores_json'], true);
        if (isset($scores['sets']) && is_array($scores['sets'])) {
            $t1Sets = 0; $t2Sets = 0;
            foreach ($scores['sets'] as $s) {
                if ($s['t1'] > $s['t2']) $t1Sets++;
                elseif ($s['t2'] > $s['t1']) $t2Sets++;
            }
            if (isset($standings[$pool][$t1])) {
                $standings[$pool][$t1]['sets_for'] += $t1Sets;
                $standings[$pool][$t1]['sets_against'] += $t2Sets;
            }
            if (isset($standings[$pool][$t2])) {
                $standings[$pool][$t2]['sets_for'] += $t2Sets;
                $standings[$pool][$t2]['sets_against'] += $t1Sets;
            }
        } elseif (is_array($scores) && (isset($scores['g1_a']) || isset($scores['g1_b']))) {
            $t1Sets = 0; $t2Sets = 0;
            for ($gi = 1; $gi <= 5; $gi++) {
                if (isset($scores["g{$gi}_a"]) && isset($scores["g{$gi}_b"]) && $scores["g{$gi}_a"] !== '' && $scores["g{$gi}_b"] !== '') {
                    $ga = (int)$scores["g{$gi}_a"];
                    $gb = (int)$scores["g{$gi}_b"];
                    if ($ga > $gb) $t1Sets++;
                    elseif ($gb > $ga) $t2Sets++;
                }
            }
            if (isset($standings[$pool][$t1])) {
                $standings[$pool][$t1]['sets_for'] += $t1Sets;
                $standings[$pool][$t1]['sets_against'] += $t2Sets;
            }
            if (isset($standings[$pool][$t2])) {
                $standings[$pool][$t2]['sets_for'] += $t2Sets;
                $standings[$pool][$t2]['sets_against'] += $t1Sets;
            }
        }
    }

    // Sort each pool by points DESC, then set diff DESC
    foreach ($standings as $pool => &$poolTeams) {
        usort($poolTeams, function($a, $b) {
            if ($b['points'] !== $a['points']) return $b['points'] <=> $a['points'];
            $diffA = $a['sets_for'] - $a['sets_against'];
            $diffB = $b['sets_for'] - $b['sets_against'];
            return $diffB <=> $diffA;
        });
    }

    return $standings;
}

/**
 * Player Clash Detection (FR-12)
/**
 * Real Automated Schedule Conflict Detector (FR-12)
 * Detects:
 * 1. Individual Athlete Clashes: Same athlete scheduled in two overlapping matches across disciplines
 * 2. Facility Overbooking Clashes: Two matches booked on the same court/table/board at the same time
 */
function detect_player_clashes($pdo) {
    // 1. Individual Athlete Double-Booking Clashes
    $sqlAthletes = "
        SELECT 
            p1.id as player_id, p1.name as player_name, u.short_code as unit_code,
            m1.id as m1_id, g1.name as game1_name, m1.round as m1_round, m1.match_date as m1_date, m1.start_time as m1_start, m1.end_time as m1_end, f1.name as f1_name,
            m2.id as m2_id, g2.name as game2_name, m2.round as m2_round, m2.match_date as m2_date, m2.start_time as m2_start, m2.end_time as m2_end, f2.name as f2_name
        FROM players p1
        JOIN players p2 ON (p1.name = p2.name AND p1.unit_id = p2.unit_id AND p1.id != p2.id)
        JOIN units u ON p1.unit_id = u.id
        JOIN teams t1 ON p1.team_id = t1.id
        JOIN matches m1 ON (m1.team1_id = t1.id OR m1.team2_id = t1.id)
        JOIN games g1 ON m1.game_id = g1.id
        LEFT JOIN facilities f1 ON m1.facility_id = f1.id
        JOIN teams t2 ON p2.team_id = t2.id
        JOIN matches m2 ON (m2.team1_id = t2.id OR m2.team2_id = t2.id)
        JOIN games g2 ON m2.game_id = g2.id
        LEFT JOIN facilities f2 ON m2.facility_id = f2.id
        WHERE m1.id < m2.id
          AND m1.match_date = m2.match_date
          AND (
              (m1.start_time <= m2.start_time AND m1.end_time > m2.start_time)
              OR (m2.start_time <= m1.start_time AND m2.end_time > m1.start_time)
          )
    ";
    $athleteClashes = $pdo->query($sqlAthletes)->fetchAll();

    // 2. Facility Overbooking Clashes
    $sqlFacilities = "
        SELECT 
            0 as player_id, CONCAT('Venue Conflict: ', f1.name) as player_name, 'FACILITY' as unit_code,
            m1.id as m1_id, g1.name as game1_name, m1.round as m1_round, m1.match_date as m1_date, m1.start_time as m1_start, m1.end_time as m1_end, f1.name as f1_name,
            m2.id as m2_id, g2.name as game2_name, m2.round as m2_round, m2.match_date as m2_date, m2.start_time as m2_start, m2.end_time as m2_end, f2.name as f2_name
        FROM matches m1
        JOIN matches m2 ON (m1.facility_id = m2.facility_id AND m1.id < m2.id AND m1.match_date = m2.match_date)
        JOIN games g1 ON m1.game_id = g1.id
        JOIN games g2 ON m2.game_id = g2.id
        JOIN facilities f1 ON m1.facility_id = f1.id
        JOIN facilities f2 ON m2.facility_id = f2.id
        WHERE (
            (m1.start_time <= m2.start_time AND m1.end_time > m2.start_time)
            OR (m2.start_time <= m1.start_time AND m2.end_time > m1.start_time)
        )
    ";
    $facilityClashes = $pdo->query($sqlFacilities)->fetchAll();

    return array_merge($athleteClashes, $facilityClashes);
}

/**
 * Image compression and thumbnail generation utility
 * Scales image to fit maxWidth x maxHeight and compresses via GD
 */
function compress_and_save_photo($sourcePath, $destPath, $maxWidth = 1200, $maxHeight = 1200, $quality = 75) {
    $dir = dirname($destPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $info = @getimagesize($sourcePath);
    if (!$info) {
        return copy($sourcePath, $destPath);
    }

    $mime = $info['mime'];
    $srcImg = null;
    switch ($mime) {
        case 'image/jpeg':
            $srcImg = @imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $srcImg = @imagecreatefrompng($sourcePath);
            break;
        case 'image/webp':
            $srcImg = @imagecreatefromwebp($sourcePath);
            break;
        case 'image/gif':
            $srcImg = @imagecreatefromgif($sourcePath);
            break;
    }

    if (!$srcImg) {
        return copy($sourcePath, $destPath);
    }

    $origWidth = imagesx($srcImg);
    $origHeight = imagesy($srcImg);

    // Calculate aspect ratio
    $ratio = min($maxWidth / max(1, $origWidth), $maxHeight / max(1, $origHeight));
    if ($ratio < 1) {
        $newWidth = (int)round($origWidth * $ratio);
        $newHeight = (int)round($origHeight * $ratio);
    } else {
        $newWidth = $origWidth;
        $newHeight = $origHeight;
    }

    $destImg = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG and WEBP
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($destImg, false);
        imagesavealpha($destImg, true);
        $transparent = imagecolorallocatealpha($destImg, 255, 255, 255, 127);
        imagefilledrectangle($destImg, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
    $success = false;
    if ($ext === 'webp') {
        $success = imagewebp($destImg, $destPath, $quality);
    } elseif ($ext === 'png') {
        $pngQuality = (int)round((100 - $quality) / 10);
        $success = imagepng($destImg, $destPath, min(9, max(0, $pngQuality)));
    } else {
        $success = imagejpeg($destImg, $destPath, $quality);
    }

    imagedestroy($srcImg);
    imagedestroy($destImg);

    return $success;
}
