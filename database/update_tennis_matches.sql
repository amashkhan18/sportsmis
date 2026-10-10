-- =============================================================================
-- HPCL SPORTS MIS - TENNIS TOURNAMENT RESULTS & MATCHES UPDATE
-- Generated: 2026-10-10 06:01:04
-- Discipline: Tennis (Game ID: 782)
-- =============================================================================

-- 1. Set Pool Assignments for Tennis Teams
UPDATE `teams` SET `pool` = 'A' WHERE `id` = 798;
UPDATE `teams` SET `pool` = 'A' WHERE `id` = 834;
UPDATE `teams` SET `pool` = 'A' WHERE `id` = 861;
UPDATE `teams` SET `pool` = 'B' WHERE `id` = 870;
UPDATE `teams` SET `pool` = 'B' WHERE `id` = 852;
UPDATE `teams` SET `pool` = 'B' WHERE `id` = 843;
UPDATE `teams` SET `pool` = 'C' WHERE `id` = 879;
UPDATE `teams` SET `pool` = 'C' WHERE `id` = 816;
UPDATE `teams` SET `pool` = 'C' WHERE `id` = 897;
UPDATE `teams` SET `pool` = 'D' WHERE `id` = 825;
UPDATE `teams` SET `pool` = 'D' WHERE `id` = 807;
UPDATE `teams` SET `pool` = 'D' WHERE `id` = 888;

-- 2. Remove all existing tennis matches
DELETE FROM `matches` WHERE `game_id` = 782;

-- 3. Insert Tennis Matches with Scores
INSERT INTO `matches` (
    `game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`,
    `facility_id`, `score_format_id`, `match_date`, `start_time`, `end_time`,
    `status`, `scores_json`, `is_published`, `created_at`
) VALUES
(782, 'Group A - Match 1', 'A', 798, 834, 798, 782, 4, '2026-10-09', '10:00:00', '11:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":3,\"s2_a\":6,\"s2_b\":4,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":3},{\"t1\":6,\"t2\":4}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"VR Tennis\",\"summary\":\"6-3, 6-4\"}', 1, NOW()),
(782, 'Group A - Match 2', 'A', 861, 834, 861, 782, 4, '2026-10-09', '11:00:00', '12:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":2,\"s2_a\":6,\"s2_b\":4,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":2},{\"t1\":6,\"t2\":4}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Tennis\",\"summary\":\"6-2, 6-4\"}', 1, NOW()),
(782, 'Group A - Match 3', 'A', 861, 798, 861, 782, 4, '2026-10-09', '12:00:00', '13:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":0,\"s2_a\":6,\"s2_b\":0,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":0},{\"t1\":6,\"t2\":0}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"MR Tennis\",\"summary\":\"6-0, 6-0\"}', 1, NOW()),
(782, 'Group B - Match 1', 'B', 870, 852, 870, 782, 4, '2026-10-09', '10:00:00', '11:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":1,\"s2_a\":6,\"s2_b\":0,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":1},{\"t1\":6,\"t2\":0}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"NWZ Tennis\",\"summary\":\"6-1, 6-0\"}', 1, NOW()),
(782, 'Group B - Match 2', 'B', 843, 852, 843, 782, 4, '2026-10-09', '11:00:00', '12:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":1,\"s2_a\":6,\"s2_b\":1,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":1},{\"t1\":6,\"t2\":1}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Tennis\",\"summary\":\"6-1, 6-1\"}', 1, NOW()),
(782, 'Group B - Match 3', 'B', 843, 870, 843, 782, 4, '2026-10-09', '12:00:00', '13:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":4,\"s2_a\":7,\"s2_b\":6,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":4},{\"t1\":7,\"t2\":6}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Tennis\",\"summary\":\"6-4, 7-6(2)\"}', 1, NOW()),
(782, 'Group C - Match 1', 'C', 879, 897, 879, 782, 4, '2026-10-09', '10:00:00', '11:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":0,\"s2_a\":6,\"s2_b\":0,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":0},{\"t1\":6,\"t2\":0}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"SCZ Tennis\",\"summary\":\"6-0, 6-0\"}', 1, NOW()),
(782, 'Group C - Match 2', 'C', 879, 816, 879, 782, 4, '2026-10-09', '11:00:00', '12:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":\"\",\"s1_b\":\"\",\"s2_a\":\"\",\"s2_b\":\"\",\"s3_a\":\"\",\"s3_b\":\"\",\"is_walkover\":1,\"status\":\"completed\",\"winner_name\":\"SCZ Tennis\",\"summary\":\"Bye \\/ Walkover\"}', 1, NOW()),
(782, 'Group C - Match 3', 'C', 897, 816, 897, 782, 4, '2026-10-09', '12:00:00', '13:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":\"\",\"s1_b\":\"\",\"s2_a\":\"\",\"s2_b\":\"\",\"s3_a\":\"\",\"s3_b\":\"\",\"is_walkover\":1,\"status\":\"completed\",\"winner_name\":\"HB Tennis\",\"summary\":\"Bye \\/ Walkover\"}', 1, NOW()),
(782, 'Group D - Match 1', 'D', 825, 888, 825, 782, 4, '2026-10-09', '10:00:00', '11:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":3,\"s2_a\":6,\"s2_b\":0,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":3},{\"t1\":6,\"t2\":0}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"PH Tennis\",\"summary\":\"6-3, 6-0\"}', 1, NOW()),
(782, 'Group D - Match 2', 'D', 825, 807, 825, 782, 4, '2026-10-09', '11:00:00', '12:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":\"\",\"s1_b\":\"\",\"s2_a\":\"\",\"s2_b\":\"\",\"s3_a\":\"\",\"s3_b\":\"\",\"is_walkover\":1,\"status\":\"completed\",\"winner_name\":\"PH Tennis\",\"summary\":\"Bye \\/ Walkover\"}', 1, NOW()),
(782, 'Group D - Match 3', 'D', 888, 807, 888, 782, 4, '2026-10-09', '12:00:00', '13:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":\"\",\"s1_b\":\"\",\"s2_a\":\"\",\"s2_b\":\"\",\"s3_a\":\"\",\"s3_b\":\"\",\"is_walkover\":1,\"status\":\"completed\",\"winner_name\":\"EZ Tennis\",\"summary\":\"Bye \\/ Walkover\"}', 1, NOW()),
(782, 'Quarter-Final 1 (Q1)', 'Knockout', 861, 888, 888, 782, 4, '2026-10-09', '14:00:00', '15:30:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":0,\"s2_a\":6,\"s2_b\":7,\"s3_a\":4,\"s3_b\":6,\"sets\":[{\"t1\":6,\"t2\":0},{\"t1\":6,\"t2\":7},{\"t1\":4,\"t2\":6}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team2\",\"status\":\"completed\",\"winner_name\":\"EZ Tennis\",\"summary\":\"6-0, 6-7(4), 4-6\"}', 1, NOW()),
(782, 'Quarter-Final 2 (Q2)', 'Knockout', 798, 825, 825, 782, 4, '2026-10-09', '14:00:00', '15:30:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":1,\"s1_b\":6,\"s2_a\":3,\"s2_b\":6,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":1,\"t2\":6},{\"t1\":3,\"t2\":6}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team2\",\"status\":\"completed\",\"winner_name\":\"PH Tennis\",\"summary\":\"1-6, 3-6\"}', 1, NOW()),
(782, 'Quarter-Final 3 (Q3)', 'Knockout', 870, 879, 879, 782, 4, '2026-10-09', '15:30:00', '17:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":4,\"s2_a\":2,\"s2_b\":6,\"s3_a\":3,\"s3_b\":6,\"sets\":[{\"t1\":6,\"t2\":4},{\"t1\":2,\"t2\":6},{\"t1\":3,\"t2\":6}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team2\",\"status\":\"completed\",\"winner_name\":\"SCZ Tennis\",\"summary\":\"6-4, 2-6, 3-6\"}', 1, NOW()),
(782, 'Quarter-Final 4 (Q4)', 'Knockout', 843, 897, 843, 782, 4, '2026-10-09', '15:30:00', '17:00:00', 'completed', '{\"type\":\"tennis\",\"s1_a\":6,\"s1_b\":3,\"s2_a\":6,\"s2_b\":0,\"s3_a\":\"\",\"s3_b\":\"\",\"sets\":[{\"t1\":6,\"t2\":3},{\"t1\":6,\"t2\":0}],\"pts_a\":\"0\",\"pts_b\":\"0\",\"server\":\"team1\",\"status\":\"completed\",\"winner_name\":\"WZ Tennis\",\"summary\":\"6-3, 6-0\"}', 1, NOW()),
(782, 'Semi-Final 1 (SF1)', 'Knockout', 888, 825, NULL, 782, 4, '2026-10-10', '10:00:00', '11:30:00', 'scheduled', NULL, 1, NOW()),
(782, 'Semi-Final 2 (SF2)', 'Knockout', 879, 843, NULL, 782, 4, '2026-10-10', '10:00:00', '11:30:00', 'scheduled', NULL, 1, NOW()),
(782, 'Championship Final', 'Knockout', 888, 843, NULL, 782, 4, '2026-10-10', '11:30:00', '13:00:00', 'scheduled', NULL, 1, NOW()),
(782, '3rd Place Playoff', 'Knockout', 825, 879, NULL, 782, 4, '2026-10-10', '11:30:00', '13:00:00', 'scheduled', NULL, 1, NOW());
