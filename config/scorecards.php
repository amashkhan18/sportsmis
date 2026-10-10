<?php
/**
 * HPCL SportsMIS - Digital Scorecard Formats & Rendering Engine
 * Implements Venue Requirements for Live Scorecards across 6 disciplines:
 * 1. Badminton & Table Tennis (Set-Based)
 * 2. Lawn Tennis (Match & Live Points)
 * 3. Chess (Points-Based)
 * 4. Carrom (Cumulative Points)
 * 5. Bridge (Team Match Points)
 * 6. Swimming (Time & Rank)
 */

/**
 * Determine the scorecard format from the game slug
 */
function get_scorecard_type($slug) {
    $slug = strtolower(trim((string)$slug));
    if (in_array($slug, ['badminton', 'badminton-open', 'table-tennis', 'table-tennis-open'])) {
        return 'badminton_table_tennis';
    }
    if (in_array($slug, ['tennis', 'lawn-tennis'])) {
        return 'tennis';
    }
    if ($slug === 'chess') {
        return 'chess';
    }
    if ($slug === 'carrom') {
        return 'carrom';
    }
    if ($slug === 'bridge') {
        return 'bridge';
    }
    if ($slug === 'swimming') {
        return 'swimming';
    }
    return 'generic';
}

/**
 * Cache and retrieve all registered score formats
 */
function get_all_score_formats() {
    global $pdo;
    static $formatsCache = null;
    if ($formatsCache === null) {
        $formatsCache = [];
        try {
            if ($pdo) {
                $rows = $pdo->query("SELECT * FROM score_formats ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $r) {
                    $r['columns'] = json_decode($r['columns_json'] ?? '[]', true) ?: [];
                    $formatsCache[$r['id']] = $r;
                }
            }
        } catch (\Exception $e) {}
    }
    return $formatsCache;
}

/**
 * Resolve the applicable score format for a match (match-level override > game-level default)
 */
function resolve_match_score_format($m) {
    $formats = get_all_score_formats();
    $fid = $m['score_format_id'] ?? null;
    if ($fid && isset($formats[$fid])) {
        return $formats[$fid];
    }
    $gid = $m['game_id'] ?? null;
    if ($gid) {
        foreach ($formats as $f) {
            if (!empty($f['game_id']) && $f['game_id'] == $gid) {
                return $f;
            }
        }
    }
    if (!empty($m['columns_json'])) {
        return [
            'id' => $m['score_format_id'] ?? 0,
            'name' => $m['format_name'] ?? 'Custom Format',
            'type' => $m['format_type'] ?? 'custom',
            'badge_text' => $m['format_badge'] ?? ($m['badge_text'] ?? ''),
            'total_columns' => $m['total_columns'] ?? 3,
            'columns' => json_decode($m['columns_json'], true) ?: [],
            'win_rule' => $m['win_rule'] ?? 'target_wins',
            'target_wins' => $m['target_wins'] ?? 2,
            'points_to_win' => $m['points_to_win'] ?? 21
        ];
    }
    return null;
}

/**
 * Generate a clean human-readable summary string from structured scores
 */
function format_score_summary($scores, $sportType = null, $customFormat = null) {
    if (!is_array($scores)) {
        return (string)$scores;
    }

    if ($customFormat && !empty($customFormat['columns'])) {
        $parts = [];
        foreach ($customFormat['columns'] as $col) {
            $k = $col['key'];
            $sh = $col['short'] ?? strtoupper($k);
            $a = $scores[$k . '_a'] ?? ($scores[$k . '_1'] ?? '');
            $b = $scores[$k . '_b'] ?? ($scores[$k . '_2'] ?? '');
            if ($a !== '' && $b !== '') {
                $parts[] = "{$sh}: {$a}-{$b}";
            }
        }
        if (!empty($parts)) {
            return implode(', ', $parts);
        }
    }

    $type = $sportType ?: ($scores['type'] ?? 'generic');

    switch ($type) {
        case 'badminton_table_tennis':
            if (!empty($scores['is_walkover'])) {
                $w = $scores['winner_name'] ?? 'Winner Declared';
                return "Walkover ($w)";
            }
            $games = [];
            for ($i = 1; $i <= 5; $i++) {
                $a = $scores["g{$i}_a"] ?? '';
                $b = $scores["g{$i}_b"] ?? '';
                if ($a !== '' && $b !== '') {
                    $games[] = "G{$i}: {$a}-{$b}";
                }
            }
            return !empty($games) ? implode(', ', $games) : ($scores['summary'] ?? 'Scores Pending');

        case 'tennis':
            $sets = [];
            for ($i = 1; $i <= 3; $i++) {
                $a = $scores["s{$i}_a"] ?? '';
                $b = $scores["s{$i}_b"] ?? '';
                if ($a !== '' && $b !== '') {
                    $sets[] = "S{$i}: {$a}-{$b}";
                }
            }
            $ptsA = $scores['pts_a'] ?? '0';
            $ptsB = $scores['pts_b'] ?? '0';
            $ptsStr = ($ptsA !== '' || $ptsB !== '') ? " [Pts: {$ptsA}-{$ptsB}]" : "";
            return !empty($sets) ? implode(', ', $sets) . $ptsStr : ($scores['summary'] ?? "Pts: {$ptsA}-{$ptsB}");

        case 'chess':
            $res = $scores['result'] ?? '';
            $white = $scores['white_player'] ?? 'White';
            $black = $scores['black_player'] ?? 'Black';
            if ($res === '1-0') return "1 - 0 ({$white} Wins)";
            if ($res === '0-1') return "0 - 1 ({$black} Wins)";
            if ($res === '1/2-1/2') return "½ - ½ (Draw)";
            return $res ?: ($scores['summary'] ?? 'Result Pending');

        case 'carrom':
            $ptA = $scores['points_a'] ?? 0;
            $ptB = $scores['points_b'] ?? 0;
            return "{$ptA} - {$ptB} Pts";

        case 'bridge':
            $impA = $scores['imps_a'] ?? 0;
            $impB = $scores['imps_b'] ?? 0;
            $vpA = $scores['vps_a'] ?? 0;
            $vpB = $scores['vps_b'] ?? 0;
            return "IMPs: {$impA}-{$impB} | VPs: {$vpA}-{$vpB}";

        case 'swimming':
            $event = $scores['event_name'] ?? 'Swimming Heat';
            $winner = $scores['winner_name'] ?? '';
            $time = $scores['winner_time'] ?? '';
            if ($winner && $time) {
                return "{$event}: {$winner} ({$time})";
            }
            return !empty($scores['summary']) ? $scores['summary'] : $event;

        default:
            return $scores['summary'] ?? 'Official Score Pending';
    }
}

/**
 * Render complete digital scorecard component matching the official BRD specifications
 */
function render_digital_scorecard($m, $scores = null, $sportType = null) {
    if ($scores === null) {
        $scores = json_decode($m['scores_json'] ?? '{}', true) ?: [];
    }

    $gameSlug = $m['game_slug'] ?? ($m['slug'] ?? '');
    $type = $sportType ?: ($scores['type'] ?? get_scorecard_type($gameSlug));
    $summary = format_score_summary($scores, $type);

    $t1Name = htmlspecialchars($m['team1_name'] ?? 'Team A');
    $t2Name = htmlspecialchars($m['team2_name'] ?? 'Team B');
    $u1Code = htmlspecialchars($m['u1_code'] ?? 'T1');
    $u2Code = htmlspecialchars($m['u2_code'] ?? 'T2');
    $u1Color = htmlspecialchars($m['u1_color'] ?? '#0d6efd');
    $u2Color = htmlspecialchars($m['u2_color'] ?? '#dc3545');

    ob_start();
    ?>
    <div class="digital-scorecard-container my-2 p-2 rounded" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.1);">
        
        <?php 
            $customFormat = resolve_match_score_format($m);
            if ($customFormat && !empty($customFormat['columns'])):
                $cols = $customFormat['columns'];
                $colCount = count($cols);
                $isWalkover = !empty($scores['is_walkover']);
                $badgeText = $customFormat['badge_text'] ?: ($customFormat['name'] ?: 'Custom Format');
                $colWidth = $colCount >= 5 ? '38px' : ($colCount >= 4 ? '48px' : '55px');

                // Compute totals or sets won
                $winsA = 0; $winsB = 0; $totA = 0; $totB = 0; $hasScore = false;
                foreach ($cols as $c) {
                    $k = $c['key'];
                    $vA = $scores[$k . '_a'] ?? ($scores[$k . '_1'] ?? '');
                    $vB = $scores[$k . '_b'] ?? ($scores[$k . '_2'] ?? '');
                    if ($vA !== '' && $vB !== '' && is_numeric($vA) && is_numeric($vB)) {
                        $hasScore = true;
                        $totA += (int)$vA;
                        $totB += (int)$vB;
                        if ((int)$vA > (int)$vB) $winsA++;
                        elseif ((int)$vB > (int)$vA) $winsB++;
                    }
                }
                $isTotalRule = ($customFormat['win_rule'] ?? '') === 'total_score';
        ?>
            <!-- Dynamic Custom Scorecard Format -->
            <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                <span class="small text-white-50"><?= htmlspecialchars($m['game_name'] ?? 'Tournament Match') ?></span>
                <span class="badge bg-primary px-2 py-1" style="font-size: 0.65rem;">
                    <i class="fas fa-award me-1"></i> <?= htmlspecialchars($badgeText) ?>
                </span>
            </div>
            <div class="table-responsive mb-1">
                <table class="table table-sm table-bordered text-center text-white mb-0" style="font-size: 0.85rem; border-color: rgba(255,255,255,0.15);">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <th class="text-start ps-2">Player / Team</th>
                            <?php foreach ($cols as $c): ?>
                                <th style="width: <?= $colWidth ?>;" title="<?= htmlspecialchars($c['label'] ?? '') ?>">
                                    <?= htmlspecialchars($c['short'] ?? strtoupper($c['key'])) ?>
                                </th>
                            <?php endforeach; ?>
                            <th style="width: 45px;" class="text-warning">
                                <?= $isTotalRule ? 'TOT' : 'SETS' ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <span class="badge me-1" style="background-color: <?= $u1Color ?>; font-size: 0.7rem;"><?= $u1Code ?></span>
                                <strong><?= $t1Name ?></strong>
                            </td>
                            <?php foreach ($cols as $c): ?>
                                <?php 
                                    $k = $c['key'];
                                    $vA = $scores[$k . '_a'] ?? ($scores[$k . '_1'] ?? '');
                                    $vB = $scores[$k . '_b'] ?? ($scores[$k . '_2'] ?? '');
                                    $isWin = ($vA !== '' && $vB !== '' && is_numeric($vA) && is_numeric($vB) && (int)$vA > (int)$vB);
                                ?>
                                <td class="fw-bold <?= $isWin ? 'text-warning' : '' ?>">
                                    <?= htmlspecialchars($vA !== '' ? $vA : '-') ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="fw-bold text-warning">
                                <?= $hasScore ? ($isTotalRule ? $totA : $winsA) : '-' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <span class="badge me-1" style="background-color: <?= $u2Color ?>; font-size: 0.7rem;"><?= $u2Code ?></span>
                                <strong><?= $t2Name ?></strong>
                            </td>
                            <?php foreach ($cols as $c): ?>
                                <?php 
                                    $k = $c['key'];
                                    $vA = $scores[$k . '_a'] ?? ($scores[$k . '_1'] ?? '');
                                    $vB = $scores[$k . '_b'] ?? ($scores[$k . '_2'] ?? '');
                                    $isWin = ($vA !== '' && $vB !== '' && is_numeric($vA) && is_numeric($vB) && (int)$vB > (int)$vA);
                                ?>
                                <td class="fw-bold <?= $isWin ? 'text-warning' : '' ?>">
                                    <?= htmlspecialchars($vB !== '' ? $vB : '-') ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="fw-bold text-warning">
                                <?= $hasScore ? ($isTotalRule ? $totB : $winsB) : '-' ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php if ($isWalkover): ?>
                <div class="text-center mt-1">
                    <span class="badge bg-danger px-2 py-1"><i class="fas fa-flag me-1"></i> WALKOVER DECLARED</span>
                </div>
            <?php endif; ?>

        <?php elseif ($type === 'badminton_table_tennis'): ?>
            <!-- 1. Badminton & Table Tennis (Set-Based) -->
            <?php
                $g1a = $scores['g1_a'] ?? ''; $g1b = $scores['g1_b'] ?? '';
                $g2a = $scores['g2_a'] ?? ''; $g2b = $scores['g2_b'] ?? '';
                $g3a = $scores['g3_a'] ?? ''; $g3b = $scores['g3_b'] ?? '';
                $g4a = $scores['g4_a'] ?? ''; $g4b = $scores['g4_b'] ?? '';
                $g5a = $scores['g5_a'] ?? ''; $g5b = $scores['g5_b'] ?? '';
                $isWalkover = !empty($scores['is_walkover']);

                // Best of 5 rule: Table Tennis Men (or explicit best-of-5 flag / score data)
                $isTT = (strpos($gameSlug, 'table-tennis') !== false || stripos($m['game_name'] ?? '', 'table tennis') !== false);
                $roundText = $m['round'] ?? '';
                $isWomen = (stripos($roundText, 'women') !== false);
                $isBestOf5 = ($isTT && !$isWomen) || (!empty($scores['is_best_of_5'])) || ($g4a !== '' || $g4b !== '' || $g5a !== '' || $g5b !== '');
            ?>
            <div class="d-flex justify-content-between align-items-center mb-1 px-1">
                <span class="small text-white-50"><?= htmlspecialchars($m['game_name'] ?? 'Racket Event') ?></span>
                <span class="badge <?= $isBestOf5 ? 'bg-primary' : 'bg-info text-dark' ?>" style="font-size: 0.65rem;">
                    <?= $isBestOf5 ? 'Best of 5 Games' : 'Best of 3 Games' ?>
                </span>
            </div>
            <div class="table-responsive mb-1">
                <table class="table table-sm table-bordered text-center text-white mb-0" style="font-size: 0.85rem; border-color: rgba(255,255,255,0.15);">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <th class="text-start ps-2">Player / Team</th>
                            <th style="width: <?= $isBestOf5 ? '44px' : '55px' ?>;">G1</th>
                            <th style="width: <?= $isBestOf5 ? '44px' : '55px' ?>;">G2</th>
                            <th style="width: <?= $isBestOf5 ? '44px' : '55px' ?>;">G3</th>
                            <?php if ($isBestOf5): ?>
                                <th style="width: 44px;">G4</th>
                                <th style="width: 44px;">G5</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <span class="badge me-1" style="background-color: <?= $u1Color ?>; font-size: 0.7rem;"><?= $u1Code ?></span>
                                <strong><?= $t1Name ?></strong>
                            </td>
                            <td class="fw-bold <?= ($g1a !== '' && $g1b !== '' && intval($g1a) > intval($g1b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g1a !== '' ? $g1a : '-') ?></td>
                            <td class="fw-bold <?= ($g2a !== '' && $g2b !== '' && intval($g2a) > intval($g2b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g2a !== '' ? $g2a : '-') ?></td>
                            <td class="fw-bold <?= ($g3a !== '' && $g3b !== '' && intval($g3a) > intval($g3b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g3a !== '' ? $g3a : '-') ?></td>
                            <?php if ($isBestOf5): ?>
                                <td class="fw-bold <?= ($g4a !== '' && $g4b !== '' && intval($g4a) > intval($g4b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g4a !== '' ? $g4a : '-') ?></td>
                                <td class="fw-bold <?= ($g5a !== '' && $g5b !== '' && intval($g5a) > intval($g5b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g5a !== '' ? $g5a : '-') ?></td>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <span class="badge me-1" style="background-color: <?= $u2Color ?>; font-size: 0.7rem;"><?= $u2Code ?></span>
                                <strong><?= $t2Name ?></strong>
                            </td>
                            <td class="fw-bold <?= ($g1a !== '' && $g1b !== '' && intval($g1b) > intval($g1a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g1b !== '' ? $g1b : '-') ?></td>
                            <td class="fw-bold <?= ($g2a !== '' && $g2b !== '' && intval($g2b) > intval($g2a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g2b !== '' ? $g2b : '-') ?></td>
                            <td class="fw-bold <?= ($g3a !== '' && $g3b !== '' && intval($g3b) > intval($g3a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g3b !== '' ? $g3b : '-') ?></td>
                            <?php if ($isBestOf5): ?>
                                <td class="fw-bold <?= ($g4a !== '' && $g4b !== '' && intval($g4b) > intval($g4a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g4b !== '' ? $g4b : '-') ?></td>
                                <td class="fw-bold <?= ($g5a !== '' && $g5b !== '' && intval($g5b) > intval($g5a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($g5b !== '' ? $g5b : '-') ?></td>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php if ($isWalkover): ?>
                <div class="text-center mt-1">
                    <span class="badge bg-danger px-2 py-1"><i class="fas fa-flag me-1"></i> WALKOVER DECLARED</span>
                </div>
            <?php endif; ?>

        <?php elseif ($type === 'tennis'): ?>
            <!-- 2. Lawn Tennis (Match & Live Points) -->
            <?php
                $s1a = $scores['s1_a'] ?? ''; $s1b = $scores['s1_b'] ?? '';
                $s2a = $scores['s2_a'] ?? ''; $s2b = $scores['s2_b'] ?? '';
                $s3a = $scores['s3_a'] ?? ''; $s3b = $scores['s3_b'] ?? '';
                $ptsA = $scores['pts_a'] ?? '0';
                $ptsB = $scores['pts_b'] ?? '0';
                $server = $scores['server'] ?? 'team1';
            ?>
            <div class="table-responsive mb-1">
                <table class="table table-sm table-bordered text-center text-white mb-0" style="font-size: 0.85rem; border-color: rgba(255,255,255,0.15);">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <th class="text-start ps-2">Player / Team</th>
                            <th style="width: 45px;">S1</th>
                            <th style="width: 45px;">S2</th>
                            <th style="width: 45px;">S3</th>
                            <th style="width: 60px; background: rgba(56, 189, 248, 0.15); color: #38bdf8;">PTS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <?php if ($server === 'team1'): ?>
                                    <span title="Serving" class="me-1">🎾</span>
                                <?php else: ?>
                                    <span class="me-1 invisible">🎾</span>
                                <?php endif; ?>
                                <span class="badge me-1" style="background-color: <?= $u1Color ?>; font-size: 0.7rem;"><?= $u1Code ?></span>
                                <strong><?= $t1Name ?></strong>
                            </td>
                            <td class="fw-bold <?= ($s1a !== '' && $s1b !== '' && intval($s1a) > intval($s1b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($s1a !== '' ? $s1a : '-') ?></td>
                            <td class="fw-bold <?= ($s2a !== '' && $s2b !== '' && intval($s2a) > intval($s2b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($s2a !== '' ? $s2a : '-') ?></td>
                            <td class="fw-bold <?= ($s3a !== '' && $s3b !== '' && intval($s3a) > intval($s3b)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($s3a !== '' ? $s3a : '-') ?></td>
                            <td class="fw-bold text-info" style="background: rgba(56, 189, 248, 0.1);"><?= htmlspecialchars($ptsA) ?></td>
                        </tr>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <?php if ($server === 'team2'): ?>
                                    <span title="Serving" class="me-1">🎾</span>
                                <?php else: ?>
                                    <span class="me-1 invisible">🎾</span>
                                <?php endif; ?>
                                <span class="badge me-1" style="background-color: <?= $u2Color ?>; font-size: 0.7rem;"><?= $u2Code ?></span>
                                <strong><?= $t2Name ?></strong>
                            </td>
                            <td class="fw-bold <?= ($s1a !== '' && $s1b !== '' && intval($s1b) > intval($s1a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($s1b !== '' ? $s1b : '-') ?></td>
                            <td class="fw-bold <?= ($s2a !== '' && $s2b !== '' && intval($s2b) > intval($s2a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($s2b !== '' ? $s2b : '-') ?></td>
                            <td class="fw-bold <?= ($s3a !== '' && $s3b !== '' && intval($s3b) > intval($s3a)) ? 'text-warning' : '' ?>"><?= htmlspecialchars($s3b !== '' ? $s3b : '-') ?></td>
                            <td class="fw-bold text-info" style="background: rgba(56, 189, 248, 0.1);"><?= htmlspecialchars($ptsB) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php elseif ($type === 'chess'): ?>
            <!-- 3. Chess (Points-Based) -->
            <?php
                $whitePlayer = $scores['white_player'] ?? $t1Name;
                $whiteZone = $scores['white_zone'] ?? $u1Code;
                $blackPlayer = $scores['black_player'] ?? $t2Name;
                $blackZone = $scores['black_zone'] ?? $u2Code;
                $boardNo = $scores['board_no'] ?? ($m['court_number'] ?? '1');
                $res = $scores['result'] ?? '';
            ?>
            <div class="p-2 rounded" style="background: rgba(255,255,255,0.03);">
                <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                    <span><i class="fas fa-chess-board me-1 text-warning"></i> Board <?= htmlspecialchars($boardNo) ?></span>
                    <span class="badge bg-secondary">Chess Result</span>
                </div>
                <div class="row align-items-center text-center">
                    <div class="col-5">
                        <div class="text-white small fw-bold">
                            <span class="badge bg-light text-dark me-1" style="font-size: 0.65rem;">WHITE</span>
                            <?= htmlspecialchars($whitePlayer) ?>
                        </div>
                        <span class="badge bg-secondary mt-1" style="font-size: 0.65rem;"><?= htmlspecialchars($whiteZone) ?></span>
                    </div>
                    <div class="col-2">
                        <span class="badge bg-primary px-2 py-1 fw-bold fs-6">
                            <?= htmlspecialchars($res ?: 'vs') ?>
                        </span>
                    </div>
                    <div class="col-5">
                        <div class="text-white small fw-bold">
                            <span class="badge bg-dark border border-secondary text-light me-1" style="font-size: 0.65rem;">BLACK</span>
                            <?= htmlspecialchars($blackPlayer) ?>
                        </div>
                        <span class="badge bg-secondary mt-1" style="font-size: 0.65rem;"><?= htmlspecialchars($blackZone) ?></span>
                    </div>
                </div>
                <?php if ($res): ?>
                    <div class="text-center mt-2 small" style="color: #4ade80;">
                        <?php if ($res === '1-0'): ?>
                            <i class="fas fa-trophy text-warning me-1"></i> White Awarded <strong>+1.0 Pt</strong> &bull; Black <strong>0 Pt</strong>
                        <?php elseif ($res === '0-1'): ?>
                            <i class="fas fa-trophy text-warning me-1"></i> Black Awarded <strong>+1.0 Pt</strong> &bull; White <strong>0 Pt</strong>
                        <?php elseif ($res === '1/2-1/2'): ?>
                            <i class="fas fa-handshake text-info me-1"></i> Draw: Each Team Awarded <strong>+0.5 Pts</strong>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($type === 'carrom'): ?>
            <!-- 4. Carrom (Cumulative Points) -->
            <?php
                $ptsA = $scores['points_a'] ?? 0;
                $ptsB = $scores['points_b'] ?? 0;
                $boardNo = $scores['board_no'] ?? ($m['court_number'] ?? '1');
            ?>
            <div class="p-2 rounded text-center" style="background: rgba(255,255,255,0.03);">
                <div class="d-flex justify-content-between align-items-center mb-2 small text-muted">
                    <span><i class="fas fa-bullseye me-1 text-warning"></i> Board <?= htmlspecialchars($boardNo) ?></span>
                    <span class="badge bg-secondary">Cumulative Board Points</span>
                </div>
                <div class="row align-items-center">
                    <div class="col-5 text-center">
                        <span class="badge me-1" style="background-color: <?= $u1Color ?>;"><?= $u1Code ?></span>
                        <div class="text-white fw-bold small text-truncate"><?= $t1Name ?></div>
                        <div class="display-6 fw-bold mt-1 text-warning" style="font-size: 1.8rem;"><?= intval($ptsA) ?></div>
                        <span class="small text-muted">Points</span>
                    </div>
                    <div class="col-2 text-center text-muted fw-bold">VS</div>
                    <div class="col-5 text-center">
                        <span class="badge me-1" style="background-color: <?= $u2Color ?>;"><?= $u2Code ?></span>
                        <div class="text-white fw-bold small text-truncate"><?= $t2Name ?></div>
                        <div class="display-6 fw-bold mt-1 text-warning" style="font-size: 1.8rem;"><?= intval($ptsB) ?></div>
                        <span class="small text-muted">Points</span>
                    </div>
                </div>
            </div>

        <?php elseif ($type === 'bridge'): ?>
            <!-- 5. Bridge (Team Match Points) -->
            <?php
                $impA = $scores['imps_a'] ?? 0;
                $impB = $scores['imps_b'] ?? 0;
                $vpA = $scores['vps_a'] ?? 0;
                $vpB = $scores['vps_b'] ?? 0;
                $session = $scores['session_no'] ?? '1';
                $table = $scores['table_no'] ?? '1';
            ?>
            <div class="table-responsive mb-1">
                <div class="d-flex justify-content-between align-items-center mb-1 small text-muted px-1">
                    <span><i class="fas fa-clone me-1 text-warning"></i> Session <?= htmlspecialchars($session) ?> &bull; Table <?= htmlspecialchars($table) ?></span>
                    <span class="badge bg-secondary">Bridge IMPs / VPs</span>
                </div>
                <table class="table table-sm table-bordered text-center text-white mb-0" style="font-size: 0.85rem; border-color: rgba(255,255,255,0.15);">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <th class="text-start ps-2">Team Name</th>
                            <th style="width: 80px;">Total IMPs</th>
                            <th style="width: 80px; background: rgba(56, 189, 248, 0.15); color: #38bdf8;">VPs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <span class="badge me-1" style="background-color: <?= $u1Color ?>; font-size: 0.7rem;"><?= $u1Code ?></span>
                                <strong><?= $t1Name ?></strong>
                            </td>
                            <td class="fw-bold <?= intval($impA) > intval($impB) ? 'text-warning' : '' ?>"><?= htmlspecialchars($impA) ?></td>
                            <td class="fw-bold text-info" style="background: rgba(56, 189, 248, 0.1);"><?= htmlspecialchars($vpA) ?></td>
                        </tr>
                        <tr>
                            <td class="text-start ps-2 text-truncate" style="max-width: 140px;">
                                <span class="badge me-1" style="background-color: <?= $u2Color ?>; font-size: 0.7rem;"><?= $u2Code ?></span>
                                <strong><?= $t2Name ?></strong>
                            </td>
                            <td class="fw-bold <?= intval($impB) > intval($impA) ? 'text-warning' : '' ?>"><?= htmlspecialchars($impB) ?></td>
                            <td class="fw-bold text-info" style="background: rgba(56, 189, 248, 0.1);"><?= htmlspecialchars($vpB) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        <?php elseif ($type === 'swimming'): ?>
            <!-- 6. Swimming (Time & Rank) -->
            <?php
                $eventName = $scores['event_name'] ?? '50m Freestyle';
                $heatNo = $scores['heat_no'] ?? 'Heat 1 / Final';
                $lanes = $scores['lanes'] ?? [];
            ?>
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <strong class="text-info small"><i class="fas fa-swimmer me-1"></i> <?= htmlspecialchars($eventName) ?></strong>
                    <span class="badge bg-secondary"><?= htmlspecialchars($heatNo) ?></span>
                </div>
                <?php if (!empty($lanes)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered text-center text-white mb-0" style="font-size: 0.8rem; border-color: rgba(255,255,255,0.15);">
                            <thead>
                                <tr style="background: rgba(255,255,255,0.05);">
                                    <th style="width: 35px;">Ln</th>
                                    <th class="text-start ps-2">Swimmer Name</th>
                                    <th style="width: 60px;">Zone</th>
                                    <th style="width: 80px;">Time</th>
                                    <th style="width: 65px;">Pos / Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lanes as $lane): ?>
                                    <?php 
                                        if (empty($lane['swimmer']) && empty($lane['time'])) continue;
                                        $status = $lane['status'] ?? 'NORMAL';
                                        $posStr = strtolower(trim($lane['position'] ?? ($lane['pos'] ?? '-')));
                                        preg_match('/^(\d+)/', $posStr, $pMatches);
                                        $rankNum = isset($pMatches[1]) ? (int)$pMatches[1] : ($posStr === 'i' ? 1 : ($posStr === 'ii' ? 2 : ($posStr === 'iii' ? 3 : 0)));
                                    ?>
                                    <tr>
                                        <td class="text-muted fw-bold"><?= htmlspecialchars($lane['lane'] ?? '') ?></td>
                                        <td class="text-start ps-2 fw-bold text-truncate" style="max-width: 130px;">
                                            <?= htmlspecialchars($lane['swimmer'] ?? 'Vacant') ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary" style="font-size: 0.65rem;"><?= htmlspecialchars($lane['zone'] ?? '-') ?></span>
                                        </td>
                                        <td class="font-monospace text-info"><?= htmlspecialchars($lane['time'] ?? '--:--.--') ?></td>
                                        <td>
                                            <?php if ($status === 'DNS'): ?>
                                                <span class="badge bg-secondary" style="font-size: 0.65rem;">DNS</span>
                                            <?php elseif ($status === 'DSQ'): ?>
                                                <span class="badge bg-danger" style="font-size: 0.65rem;">DSQ</span>
                                            <?php elseif ($rankNum === 1): ?>
                                                <span class="badge bg-warning text-dark fw-bold" style="font-size: 0.65rem;"><i class="fas fa-medal me-1"></i>1st</span>
                                            <?php elseif ($rankNum === 2): ?>
                                                <span class="badge bg-light text-dark fw-bold" style="font-size: 0.65rem;">2nd</span>
                                            <?php elseif ($rankNum === 3): ?>
                                                <span class="badge bg-warning-subtle text-dark fw-bold" style="font-size: 0.65rem;">3rd</span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= htmlspecialchars($lane['position'] ?? ($lane['pos'] ?? $posStr)) ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-2 text-muted small">
                        <?= htmlspecialchars($summary) ?>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Fallback Generic Scorecard -->
            <div class="text-center p-2">
                <span class="fw-bold text-info" style="font-size: 0.95rem; letter-spacing: 0.5px;">
                    <?= htmlspecialchars($summary) ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if (!empty($m['winner_name'])): ?>
            <div class="text-center mt-2 pt-2 border-top border-secondary small" style="color: #4ade80; font-weight: 600;">
                <i class="fas fa-trophy me-1 text-warning"></i> Winner: <strong><?= htmlspecialchars($m['winner_name']) ?></strong>
            </div>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}

/**
 * Compute Swimming Medal Tally & Points Leaderboard across all 12 units
 */
function get_swimming_discipline_standings($pdo, $gameId) {
    $units = $pdo->query("SELECT id, name, short_code, color_code FROM units ORDER BY short_code ASC")->fetchAll(PDO::FETCH_ASSOC);
    $standings = [];
    foreach ($units as $u) {
        $standings[$u['short_code']] = [
            'id' => $u['id'],
            'name' => $u['name'],
            'short_code' => $u['short_code'],
            'color_code' => $u['color_code'],
            'gold' => 0,
            'silver' => 0,
            'bronze' => 0,
            'points' => 0
        ];
    }

    $stmt = $pdo->prepare("SELECT id, scores_json, winner_id FROM matches WHERE game_id = ? AND status = 'completed'");
    $stmt->execute([$gameId]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($matches as $m) {
        $scores = json_decode($m['scores_json'] ?? '{}', true) ?: [];
        $lanes = $scores['lanes'] ?? [];
        if (!empty($lanes)) {
            foreach ($lanes as $l) {
                $zone = strtoupper(trim($l['zone'] ?? ''));
                $baseZone = trim(preg_replace('/[\s\-_]+[AB]$/i', '', $zone));
                $matchedZone = isset($standings[$zone]) ? $zone : (isset($standings[$baseZone]) ? $baseZone : null);
                $pos = strtolower(trim($l['position'] ?? ($l['pos'] ?? '')));
                preg_match('/^(\d+)/', $pos, $pMatches);
                $rankNum = isset($pMatches[1]) ? (int)$pMatches[1] : ($pos === 'i' ? 1 : ($pos === 'ii' ? 2 : ($pos === 'iii' ? 3 : 0)));
                if ($matchedZone) {
                    if ($rankNum === 1) {
                        $standings[$matchedZone]['gold']++;
                        $standings[$matchedZone]['points'] += 5;
                    } elseif ($rankNum === 2) {
                        $standings[$matchedZone]['silver']++;
                        $standings[$matchedZone]['points'] += 3;
                    } elseif ($rankNum === 3) {
                        $standings[$matchedZone]['bronze']++;
                        $standings[$matchedZone]['points'] += 1;
                    }
                }
            }
        } elseif (!empty($m['winner_id'])) {
            $wUnit = $pdo->query("SELECT u.short_code FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.id = " . (int)$m['winner_id'])->fetchColumn();
            if ($wUnit && isset($standings[$wUnit])) {
                $standings[$wUnit]['gold']++;
                $standings[$wUnit]['points'] += 5;
            }
        }
    }

    uasort($standings, function($a, $b) {
        if ($b['gold'] !== $a['gold']) return $b['gold'] <=> $a['gold'];
        if ($b['silver'] !== $a['silver']) return $b['silver'] <=> $a['silver'];
        if ($b['bronze'] !== $a['bronze']) return $b['bronze'] <=> $a['bronze'];
        return $b['points'] <=> $a['points'];
    });

    return $standings;
}

/**
 * Compute Chess Standings (P, W, D, L, Pts: 1.0 for Win, 0.5 for Draw)
 */
function get_chess_discipline_standings($pdo, $gameId) {
    $teams = $pdo->query("
        SELECT t.id, t.name, u.short_code, u.color_code
        FROM teams t
        JOIN units u ON t.unit_id = u.id
        WHERE t.game_id = $gameId
        ORDER BY u.short_code ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $standings = [];
    foreach ($teams as $t) {
        $standings[$t['id']] = [
            'team_id' => $t['id'],
            'name' => $t['name'],
            'short_code' => $t['short_code'],
            'color_code' => $t['color_code'],
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'points' => 0.0
        ];
    }

    $stmt = $pdo->prepare("SELECT id, team1_id, team2_id, winner_id, scores_json FROM matches WHERE game_id = ? AND status = 'completed'");
    $stmt->execute([$gameId]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($matches as $m) {
        $t1 = $m['team1_id'];
        $t2 = $m['team2_id'];
        $scores = json_decode($m['scores_json'] ?? '{}', true) ?: [];
        $res = $scores['result'] ?? '';

        if (isset($standings[$t1])) $standings[$t1]['played']++;
        if (isset($standings[$t2])) $standings[$t2]['played']++;

        if ($res === '1-0' || $res === '1 - 0' || $m['winner_id'] == $t1) {
            if (isset($standings[$t1])) { $standings[$t1]['won']++; $standings[$t1]['points'] += 1.0; }
            if (isset($standings[$t2])) { $standings[$t2]['lost']++; }
        } elseif ($res === '0-1' || $res === '0 - 1' || $m['winner_id'] == $t2) {
            if (isset($standings[$t2])) { $standings[$t2]['won']++; $standings[$t2]['points'] += 1.0; }
            if (isset($standings[$t1])) { $standings[$t1]['lost']++; }
        } elseif ($res === '1/2-1/2' || $res === '1/2 - 1/2' || $res === '½ - ½' || $res === '½-½') {
            if (isset($standings[$t1])) { $standings[$t1]['drawn']++; $standings[$t1]['points'] += 0.5; }
            if (isset($standings[$t2])) { $standings[$t2]['drawn']++; $standings[$t2]['points'] += 0.5; }
        }
    }

    uasort($standings, function($a, $b) {
        if ($b['points'] != $a['points']) return ($b['points'] > $a['points']) ? 1 : -1;
        return $b['won'] <=> $a['won'];
    });

    return $standings;
}

/**
 * Compute Bridge Standings (Sessions, IMPs Diff, Victory Points)
 */
function get_bridge_discipline_standings($pdo, $gameId) {
    $teams = $pdo->query("
        SELECT t.id, t.name, u.short_code, u.color_code
        FROM teams t
        JOIN units u ON t.unit_id = u.id
        WHERE t.game_id = $gameId
        ORDER BY u.short_code ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $standings = [];
    foreach ($teams as $t) {
        $standings[$t['id']] = [
            'team_id' => $t['id'],
            'name' => $t['name'],
            'short_code' => $t['short_code'],
            'color_code' => $t['color_code'],
            'sessions' => 0,
            'imps_for' => 0,
            'imps_against' => 0,
            'vps' => 0.0
        ];
    }

    $stmt = $pdo->prepare("SELECT id, team1_id, team2_id, winner_id, scores_json FROM matches WHERE game_id = ? AND status = 'completed'");
    $stmt->execute([$gameId]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($matches as $m) {
        $t1 = $m['team1_id'];
        $t2 = $m['team2_id'];
        $scores = json_decode($m['scores_json'] ?? '{}', true) ?: [];
        $impA = (int)($scores['imps_a'] ?? 0);
        $impB = (int)($scores['imps_b'] ?? 0);
        $vpA = (float)($scores['vps_a'] ?? 0);
        $vpB = (float)($scores['vps_b'] ?? 0);

        if (isset($standings[$t1])) {
            $standings[$t1]['sessions']++;
            $standings[$t1]['imps_for'] += $impA;
            $standings[$t1]['imps_against'] += $impB;
            $standings[$t1]['vps'] += $vpA;
        }
        if (isset($standings[$t2])) {
            $standings[$t2]['sessions']++;
            $standings[$t2]['imps_for'] += $impB;
            $standings[$t2]['imps_against'] += $impA;
            $standings[$t2]['vps'] += $vpB;
        }
    }

    uasort($standings, function($a, $b) {
        if ($b['vps'] != $a['vps']) return ($b['vps'] > $a['vps']) ? 1 : -1;
        $diffA = $a['imps_for'] - $a['imps_against'];
        $diffB = $b['imps_for'] - $b['imps_against'];
        return $diffB <=> $diffA;
    });

    return $standings;
}

/**
 * Compute Bridge Swiss Matrix (R-I to R-V, Cumulative VPs, Opponent Team No., Total, Rank)
 */
function get_bridge_swiss_matrix($pdo, $gameId) {
    // Official tournament numbering from Bridge whiteboard:
    $canonical = [
        1 => ['code' => 'MF', 'name' => 'MARATHON', 'full' => 'Marathon'],
        2 => ['code' => 'MR', 'name' => 'MR', 'full' => 'Mumbai Refinery'],
        3 => ['code' => 'NCZ', 'name' => 'NCZ', 'full' => 'North Central Zone'],
        4 => ['code' => 'HB', 'name' => 'HB', 'full' => 'Hindustan Bhawan'],
        5 => ['code' => 'VR', 'name' => 'VR', 'full' => 'Vizag refinery'],
        6 => ['code' => 'WZ', 'name' => 'WZ', 'full' => 'West Zone'],
        7 => ['code' => 'NZ', 'name' => 'NZ', 'full' => 'North Zone'],
        8 => ['code' => 'SCZ', 'name' => 'SCZ', 'full' => 'South Central Zone'],
        9 => ['code' => 'PH', 'name' => 'PH', 'full' => 'Petroleum House'],
        10 => ['code' => 'NWZ', 'name' => 'NWZ', 'full' => 'North West Zone']
    ];

    $dbTeams = $pdo->query("
        SELECT t.id, t.name, u.short_code, u.color_code, u.name as unit_name
        FROM teams t
        JOIN units u ON t.unit_id = u.id
        WHERE t.game_id = $gameId
    ")->fetchAll(PDO::FETCH_ASSOC);

    $teamNumById = [];
    $matrix = [];

    foreach ($canonical as $num => $info) {
        $found = null;
        foreach ($dbTeams as $t) {
            if (strcasecmp($t['short_code'], $info['code']) === 0) {
                $found = $t;
                break;
            }
        }
        $tId = $found ? (int)$found['id'] : null;
        if ($tId) {
            $teamNumById[$tId] = $num;
        }

        $matrix[$num] = [
            'team_no' => $num,
            'name' => $info['name'],
            'full_name' => $found['unit_name'] ?? $info['full'],
            'short_code' => $info['code'],
            'team_id' => $tId,
            'color_code' => $found['color_code'] ?? '#003366',
            'rounds' => [
                1 => ['opp_no' => null, 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
                2 => ['opp_no' => null, 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
                3 => ['opp_no' => null, 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
                4 => ['opp_no' => null, 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
                5 => ['opp_no' => null, 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
            ],
            'total_vp' => 0.0,
            'rank' => 10
        ];
    }

    // Fetch matches for game
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE game_id = ? ORDER BY id ASC");
    $stmt->execute([$gameId]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $roundFixtures = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];

    foreach ($matches as $m) {
        $scores = json_decode($m['scores_json'] ?? '{}', true) ?: [];
        if (!empty($scores['stage']) && $scores['stage'] === 'super_league') continue;
        if (stripos($m['round'], 'Super League') !== false) continue;

        $rNo = (int)($scores['round_no'] ?? 0);
        if ($rNo < 1 || $rNo > 5) {
            if (preg_match('/(?:Round|R)[ -]*([1-5]|I{1,3}|IV|V)/i', $m['round'], $matchesRound)) {
                $rStr = strtoupper($matchesRound[1]);
                $mapRoman = ['1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, 'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5];
                $rNo = $mapRoman[$rStr] ?? 0;
            }
        }
        if ($rNo < 1 || $rNo > 5) continue;

        $t1Id = $m['team1_id'];
        $t2Id = $m['team2_id'];
        $t1Num = $teamNumById[$t1Id] ?? null;
        $t2Num = $teamNumById[$t2Id] ?? null;

        $tableNo = (int)($scores['table_no'] ?? 0);
        if (!$tableNo && preg_match('/Table\s*([1-5])/i', $m['pool_name'] ?? '', $tblMatch)) {
            $tableNo = (int)$tblMatch[1];
        }

        $vpA = isset($scores['vps_a']) ? (float)$scores['vps_a'] : null;
        $vpB = isset($scores['vps_b']) ? (float)$scores['vps_b'] : null;
        $status = $m['status'];

        if ($t1Num && $t2Num) {
            $matrix[$t1Num]['rounds'][$rNo]['opp_no'] = $t2Num;
            $matrix[$t2Num]['rounds'][$rNo]['opp_no'] = $t1Num;
            $matrix[$t1Num]['rounds'][$rNo]['status'] = $status;
            $matrix[$t2Num]['rounds'][$rNo]['status'] = $status;
            $matrix[$t1Num]['rounds'][$rNo]['match_id'] = $m['id'];
            $matrix[$t2Num]['rounds'][$rNo]['match_id'] = $m['id'];
            $matrix[$t1Num]['rounds'][$rNo]['table_no'] = $tableNo;
            $matrix[$t2Num]['rounds'][$rNo]['table_no'] = $tableNo;

            if ($status === 'completed' && $vpA !== null && $vpB !== null) {
                $matrix[$t1Num]['rounds'][$rNo]['round_vp'] = $vpA;
                $matrix[$t2Num]['rounds'][$rNo]['round_vp'] = $vpB;
            } elseif ($status === 'in_progress' && ($vpA !== null || $vpB !== null)) {
                $matrix[$t1Num]['rounds'][$rNo]['round_vp'] = $vpA;
                $matrix[$t2Num]['rounds'][$rNo]['round_vp'] = $vpB;
            }
        }

        $roundFixtures[$rNo][] = [
            'match_id' => $m['id'],
            'table_no' => $tableNo ?: (count($roundFixtures[$rNo]) + 1),
            'team1_id' => $t1Id,
            'team2_id' => $t2Id,
            'team1_num' => $t1Num,
            'team2_num' => $t2Num,
            'team1_name' => $t1Num ? $matrix[$t1Num]['name'] : 'TBD',
            'team2_name' => $t2Num ? $matrix[$t2Num]['name'] : 'TBD',
            'team1_code' => $t1Num ? $matrix[$t1Num]['short_code'] : '',
            'team2_code' => $t2Num ? $matrix[$t2Num]['short_code'] : '',
            'team1_color' => $t1Num ? $matrix[$t1Num]['color_code'] : '#94a3b8',
            'team2_color' => $t2Num ? $matrix[$t2Num]['color_code'] : '#94a3b8',
            'vps_a' => $vpA,
            'vps_b' => $vpB,
            'imps_a' => $scores['imps_a'] ?? 0,
            'imps_b' => $scores['imps_b'] ?? 0,
            'status' => $status,
            'winner_id' => $m['winner_id']
        ];
    }

    // Sort round fixtures by table_no
    for ($r = 1; $r <= 5; $r++) {
        usort($roundFixtures[$r], function($a, $b) {
            return ($a['table_no'] ?? 0) <=> ($b['table_no'] ?? 0);
        });
    }

    // Compute cumulative VPs across rounds
    foreach ($matrix as $num => &$row) {
        $cum = 0.0;
        for ($r = 1; $r <= 5; $r++) {
            if ($row['rounds'][$r]['round_vp'] !== null) {
                $cum += $row['rounds'][$r]['round_vp'];
                $row['rounds'][$r]['cum_vp'] = round($cum, 2);
            }
        }
        $row['total_vp'] = round($cum, 2);
    }
    unset($row);

    // Calculate Ranks (highest Total VP gets Rank 1)
    $sorted = $matrix;
    uasort($sorted, function($a, $b) {
        if ($b['total_vp'] != $a['total_vp']) {
            return ($b['total_vp'] > $a['total_vp']) ? 1 : -1;
        }
        return $a['team_no'] <=> $b['team_no'];
    });

    $rank = 1;
    foreach ($sorted as $num => $data) {
        $matrix[$num]['rank'] = $rank++;
    }

    return [
        'matrix' => $matrix,
        'fixtures' => $roundFixtures,
        'canonical_teams' => $canonical
    ];
}

/**
 * Compute Bridge Super League Finals Matrix (Top 4 Qualifiers, R-1 to R-3, Cumulative VPs, Medals)
 * Date: 10th October 2026
 */
function get_bridge_super_league_data($pdo, $gameId) {
    // 1. Identify Top 4 teams from Swiss Prelims
    $swissData = get_bridge_swiss_matrix($pdo, $gameId);
    $qualified = [];
    foreach ($swissData['matrix'] as $row) {
        if ($row['rank'] <= 4) {
            $qualified[$row['rank']] = $row;
        }
    }
    ksort($qualified);

    // Fallback if Swiss not complete
    if (count($qualified) < 4) {
        $canonicalMap = [
            1 => ['code' => 'MR', 'name' => 'MR'],
            2 => ['code' => 'VR', 'name' => 'VR'],
            3 => ['code' => 'SCZ', 'name' => 'SCZ'],
            4 => ['code' => 'MF', 'name' => 'MARATHON']
        ];
        foreach ($canonicalMap as $seed => $c) {
            if (!isset($qualified[$seed])) {
                foreach ($swissData['matrix'] as $row) {
                    if (strcasecmp($row['short_code'], $c['code']) === 0) {
                        $row['rank'] = $seed;
                        $qualified[$seed] = $row;
                        break;
                    }
                }
            }
        }
        ksort($qualified);
    }

    $seedMapByTeamId = [];
    $slMatrix = [];
    $roundFixtures = [1 => [], 2 => [], 3 => []];

    foreach ($qualified as $seed => $t) {
        if (!empty($t['team_id'])) {
            $seedMapByTeamId[$t['team_id']] = $seed;
        }
        $slMatrix[$seed] = [
            'seed' => $seed,
            'team_id' => $t['team_id'] ?? null,
            'name' => $t['name'],
            'full_name' => $t['full_name'],
            'short_code' => $t['short_code'],
            'color_code' => $t['color_code'],
            'rounds' => [
                1 => ['opp_seed' => null, 'opp_code' => '', 'opp_name' => '', 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
                2 => ['opp_seed' => null, 'opp_code' => '', 'opp_name' => '', 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
                3 => ['opp_seed' => null, 'opp_code' => '', 'opp_name' => '', 'round_vp' => null, 'cum_vp' => null, 'status' => 'scheduled', 'match_id' => null, 'table_no' => null],
            ],
            'total_vp' => 0.0,
            'rank' => $seed,
            'medal' => ($seed === 1 ? 'gold' : ($seed === 2 ? 'silver' : ($seed === 3 ? 'bronze' : 'none')))
        ];
    }

    // Fetch Super League matches
    $stmt = $pdo->prepare("
        SELECT * FROM matches 
        WHERE game_id = ? AND (round LIKE 'Super League%' OR scores_json LIKE '%super_league%')
        ORDER BY id ASC
    ");
    $stmt->execute([$gameId]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($matches as $m) {
        $scores = json_decode($m['scores_json'] ?? '{}', true) ?: [];
        $rNo = (int)($scores['round_no'] ?? 0);
        if ($rNo < 1 || $rNo > 3) {
            if (preg_match('/(?:SL|Super League)[ -]*(?:R-?|Round )?([1-3]|I{1,3})/i', $m['round'], $rMatch)) {
                $rStr = strtoupper($rMatch[1]);
                $mapRoman = ['1' => 1, '2' => 2, '3' => 3, 'I' => 1, 'II' => 2, 'III' => 3];
                $rNo = $mapRoman[$rStr] ?? 0;
            }
        }
        if ($rNo < 1 || $rNo > 3) continue;

        $t1Id = $m['team1_id'];
        $t2Id = $m['team2_id'];
        $s1 = $seedMapByTeamId[$t1Id] ?? null;
        $s2 = $seedMapByTeamId[$t2Id] ?? null;

        $tableNo = (int)($scores['table_no'] ?? 0);
        if (!$tableNo && preg_match('/Table\s*([1-2])/i', $m['pool_name'] ?? '', $tblMatch)) {
            $tableNo = (int)$tblMatch[1];
        }

        $vpA = isset($scores['vps_a']) && $scores['vps_a'] !== null ? (float)$scores['vps_a'] : null;
        $vpB = isset($scores['vps_b']) && $scores['vps_b'] !== null ? (float)$scores['vps_b'] : null;
        $status = $m['status'];

        if ($s1 && $s2 && isset($slMatrix[$s1]) && isset($slMatrix[$s2])) {
            $slMatrix[$s1]['rounds'][$rNo]['opp_seed'] = $s2;
            $slMatrix[$s2]['rounds'][$rNo]['opp_seed'] = $s1;
            $slMatrix[$s1]['rounds'][$rNo]['opp_code'] = $slMatrix[$s2]['short_code'];
            $slMatrix[$s2]['rounds'][$rNo]['opp_code'] = $slMatrix[$s1]['short_code'];
            $slMatrix[$s1]['rounds'][$rNo]['opp_name'] = $slMatrix[$s2]['name'];
            $slMatrix[$s2]['rounds'][$rNo]['opp_name'] = $slMatrix[$s1]['name'];
            $slMatrix[$s1]['rounds'][$rNo]['status'] = $status;
            $slMatrix[$s2]['rounds'][$rNo]['status'] = $status;
            $slMatrix[$s1]['rounds'][$rNo]['match_id'] = $m['id'];
            $slMatrix[$s2]['rounds'][$rNo]['match_id'] = $m['id'];
            $slMatrix[$s1]['rounds'][$rNo]['table_no'] = $tableNo;
            $slMatrix[$s2]['rounds'][$rNo]['table_no'] = $tableNo;

            if (($status === 'completed' || $status === 'in_progress') && ($vpA !== null || $vpB !== null)) {
                $slMatrix[$s1]['rounds'][$rNo]['round_vp'] = $vpA;
                $slMatrix[$s2]['rounds'][$rNo]['round_vp'] = $vpB;
            }
        }

        $roundFixtures[$rNo][] = [
            'match_id' => $m['id'],
            'table_no' => $tableNo ?: (count($roundFixtures[$rNo]) + 1),
            'team1_id' => $t1Id,
            'team2_id' => $t2Id,
            'team1_seed' => $s1,
            'team2_seed' => $s2,
            'team1_name' => $s1 && isset($slMatrix[$s1]) ? $slMatrix[$s1]['name'] : 'TBD',
            'team2_name' => $s2 && isset($slMatrix[$s2]) ? $slMatrix[$s2]['name'] : 'TBD',
            'team1_code' => $s1 && isset($slMatrix[$s1]) ? $slMatrix[$s1]['short_code'] : '',
            'team2_code' => $s2 && isset($slMatrix[$s2]) ? $slMatrix[$s2]['short_code'] : '',
            'team1_color' => $s1 && isset($slMatrix[$s1]) ? $slMatrix[$s1]['color_code'] : '#94a3b8',
            'team2_color' => $s2 && isset($slMatrix[$s2]) ? $slMatrix[$s2]['color_code'] : '#94a3b8',
            'vps_a' => $vpA,
            'vps_b' => $vpB,
            'imps_a' => $scores['imps_a'] ?? 0,
            'imps_b' => $scores['imps_b'] ?? 0,
            'status' => $status,
            'winner_id' => $m['winner_id'],
            'match_date' => $m['match_date'],
            'start_time' => $m['start_time'],
            'end_time' => $m['end_time']
        ];
    }

    // Sort round fixtures by table_no
    for ($r = 1; $r <= 3; $r++) {
        usort($roundFixtures[$r], function($a, $b) {
            return ($a['table_no'] ?? 0) <=> ($b['table_no'] ?? 0);
        });
    }

    // Compute cumulative VPs across Super League rounds
    foreach ($slMatrix as $seed => &$row) {
        $cum = 0.0;
        for ($r = 1; $r <= 3; $r++) {
            if ($row['rounds'][$r]['round_vp'] !== null) {
                $cum += $row['rounds'][$r]['round_vp'];
                $row['rounds'][$r]['cum_vp'] = round($cum, 2);
            }
        }
        $row['total_vp'] = round($cum, 2);
    }
    unset($row);

    // Calculate Super League Ranks
    $sorted = $slMatrix;
    uasort($sorted, function($a, $b) {
        if ($b['total_vp'] != $a['total_vp']) {
            return ($b['total_vp'] > $a['total_vp']) ? 1 : -1;
        }
        return $a['seed'] <=> $b['seed'];
    });

    $rank = 1;
    foreach ($sorted as $seed => $data) {
        $slMatrix[$seed]['rank'] = $rank;
        $slMatrix[$seed]['medal'] = ($rank === 1 ? 'gold' : ($rank === 2 ? 'silver' : ($rank === 3 ? 'bronze' : 'none')));
        $rank++;
    }

    return [
        'matrix' => $slMatrix,
        'fixtures' => $roundFixtures,
        'qualified_teams' => array_values($qualified)
    ];
}