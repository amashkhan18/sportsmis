-- =============================================================================
-- HPCL TOURNAMENT 2026 - BRIDGE CONSOLIDATED SWISS LEAGUE & MATCHES UPDATE
-- Discipline: Bridge (slug: 'bridge')
-- Description: Sets up 10-team Swiss league structure (R-I to R-V, 25 matches)
--              Populates official tournament scores (R-I to R-V) from official whiteboard
-- =============================================================================

SET NAMES utf8mb4;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;

-- 1. Identify Bridge Game ID
SET @game_id = (SELECT `id` FROM `games` WHERE `slug` = 'bridge' LIMIT 1);

-- 2. Map Canonical Teams (1 to 10) by Unit Code
SET @t1  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'MF' LIMIT 1);
SET @t2  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'MR' LIMIT 1);
SET @t3  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'NCZ' LIMIT 1);
SET @t4  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'HB' LIMIT 1);
SET @t5  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'VR' LIMIT 1);
SET @t6  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'WZ' LIMIT 1);
SET @t7  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'NZ' LIMIT 1);
SET @t8  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'SCZ' LIMIT 1);
SET @t9  = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'PH' LIMIT 1);
SET @t10 = (SELECT t.id FROM teams t JOIN units u ON t.unit_id = u.id WHERE t.game_id = @game_id AND u.short_code = 'NWZ' LIMIT 1);

-- 3. Clear existing Bridge matches
DELETE FROM `matches` WHERE `game_id` = @game_id;

-- 4. Insert Round 1 (R-I) - 5 Concluded Tables
INSERT INTO `matches` (`game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`, `status`, `scores_json`, `match_date`, `start_time`, `end_time`) VALUES
(@game_id, 'Round 1 (R-I)', 'Table 1', @t1, @t2, @t2, 'completed', '{"type":"bridge","round_no":1,"round_label":"R-I","table_no":1,"team1_num":1,"team2_num":2,"vps_a":0.26,"vps_b":19.74,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-I Table 1: 0.26 - 19.74 VPs"}', '2026-10-09', '10:00:00', '11:30:00'),
(@game_id, 'Round 1 (R-I)', 'Table 2', @t3, @t4, @t3, 'completed', '{"type":"bridge","round_no":1,"round_label":"R-I","table_no":2,"team1_num":3,"team2_num":4,"vps_a":12.77,"vps_b":7.23,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-I Table 2: 12.77 - 7.23 VPs"}', '2026-10-09', '10:00:00', '11:30:00'),
(@game_id, 'Round 1 (R-I)', 'Table 3', @t5, @t6, NULL, 'completed', '{"type":"bridge","round_no":1,"round_label":"R-I","table_no":3,"team1_num":5,"team2_num":6,"vps_a":10.00,"vps_b":10.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-I Table 3: 10 - 10 VPs"}', '2026-10-09', '10:00:00', '11:30:00'),
(@game_id, 'Round 1 (R-I)', 'Table 4', @t7, @t8, @t8, 'completed', '{"type":"bridge","round_no":1,"round_label":"R-I","table_no":4,"team1_num":7,"team2_num":8,"vps_a":7.58,"vps_b":12.42,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-I Table 4: 7.58 - 12.42 VPs"}', '2026-10-09', '10:00:00', '11:30:00'),
(@game_id, 'Round 1 (R-I)', 'Table 5', @t9, @t10, @t9, 'completed', '{"type":"bridge","round_no":1,"round_label":"R-I","table_no":5,"team1_num":9,"team2_num":10,"vps_a":19.74,"vps_b":0.26,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-I Table 5: 19.74 - 0.26 VPs"}', '2026-10-09', '10:00:00', '11:30:00');

-- 5. Insert Round 2 (R-II) - 5 Concluded Tables
INSERT INTO `matches` (`game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`, `status`, `scores_json`, `match_date`, `start_time`, `end_time`) VALUES
(@game_id, 'Round 2 (R-II)', 'Table 1', @t1, @t10, @t1, 'completed', '{"type":"bridge","round_no":2,"round_label":"R-II","table_no":1,"team1_num":1,"team2_num":10,"vps_a":20.00,"vps_b":0.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-II Table 1: 20 - 0 VPs"}', '2026-10-09', '11:45:00', '13:15:00'),
(@game_id, 'Round 2 (R-II)', 'Table 2', @t2, @t9, @t2, 'completed', '{"type":"bridge","round_no":2,"round_label":"R-II","table_no":2,"team1_num":2,"team2_num":9,"vps_a":11.27,"vps_b":8.73,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-II Table 2: 11.27 - 8.73 VPs"}', '2026-10-09', '11:45:00', '13:15:00'),
(@game_id, 'Round 2 (R-II)', 'Table 3', @t3, @t8, @t8, 'completed', '{"type":"bridge","round_no":2,"round_label":"R-II","table_no":3,"team1_num":3,"team2_num":8,"vps_a":7.58,"vps_b":12.42,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-II Table 3: 7.58 - 12.42 VPs"}', '2026-10-09', '11:45:00', '13:15:00'),
(@game_id, 'Round 2 (R-II)', 'Table 4', @t4, @t5, @t5, 'completed', '{"type":"bridge","round_no":2,"round_label":"R-II","table_no":4,"team1_num":4,"team2_num":5,"vps_a":4.00,"vps_b":16.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-II Table 4: 4 - 16 VPs"}', '2026-10-09', '11:45:00', '13:15:00'),
(@game_id, 'Round 2 (R-II)', 'Table 5', @t6, @t7, @t6, 'completed', '{"type":"bridge","round_no":2,"round_label":"R-II","table_no":5,"team1_num":6,"team2_num":7,"vps_a":20.00,"vps_b":0.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-II Table 5: 20 - 0 VPs"}', '2026-10-09', '11:45:00', '13:15:00');

-- 6. Insert Round 3 (R-III) - 5 Concluded Tables
INSERT INTO `matches` (`game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`, `status`, `scores_json`, `match_date`, `start_time`, `end_time`) VALUES
(@game_id, 'Round 3 (R-III)', 'Table 1', @t2, @t6, @t2, 'completed', '{"type":"bridge","round_no":3,"round_label":"R-III","table_no":1,"team1_num":2,"team2_num":6,"vps_a":20.00,"vps_b":0.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-III Table 1: 20 - 0 VPs"}', '2026-10-09', '14:30:00', '16:00:00'),
(@game_id, 'Round 3 (R-III)', 'Table 2', @t5, @t9, @t5, 'completed', '{"type":"bridge","round_no":3,"round_label":"R-III","table_no":2,"team1_num":5,"team2_num":9,"vps_a":20.00,"vps_b":0.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-III Table 2: 20 - 0 VPs"}', '2026-10-09', '14:30:00', '16:00:00'),
(@game_id, 'Round 3 (R-III)', 'Table 3', @t1, @t8, @t1, 'completed', '{"type":"bridge","round_no":3,"round_label":"R-III","table_no":3,"team1_num":1,"team2_num":8,"vps_a":16.90,"vps_b":3.10,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-III Table 3: 16.90 - 3.10 VPs"}', '2026-10-09', '14:30:00', '16:00:00'),
(@game_id, 'Round 3 (R-III)', 'Table 4', @t7, @t3, @t7, 'completed', '{"type":"bridge","round_no":3,"round_label":"R-III","table_no":4,"team1_num":7,"team2_num":3,"vps_a":20.00,"vps_b":0.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-III Table 4: 20 - 0 VPs"}', '2026-10-09', '14:30:00', '16:00:00'),
(@game_id, 'Round 3 (R-III)', 'Table 5', @t4, @t10, @t4, 'completed', '{"type":"bridge","round_no":3,"round_label":"R-III","table_no":5,"team1_num":4,"team2_num":10,"vps_a":18.37,"vps_b":1.63,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-III Table 5: 18.37 - 1.63 VPs"}', '2026-10-09', '14:30:00', '16:00:00');

-- 7. Insert Round 4 (R-IV) - 5 Concluded Tables
INSERT INTO `matches` (`game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`, `status`, `scores_json`, `match_date`, `start_time`, `end_time`) VALUES
(@game_id, 'Round 4 (R-IV)', 'Table 1', @t2, @t5, @t2, 'completed', '{"type":"bridge","round_no":4,"round_label":"R-IV","table_no":1,"team1_num":2,"team2_num":5,"vps_a":18.97,"vps_b":1.03,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-IV Table 1: 18.97 - 1.03 VPs"}', '2026-10-09', '16:15:00', '17:45:00'),
(@game_id, 'Round 4 (R-IV)', 'Table 2', @t1, @t6, @t1, 'completed', '{"type":"bridge","round_no":4,"round_label":"R-IV","table_no":2,"team1_num":1,"team2_num":6,"vps_a":10.44,"vps_b":9.56,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-IV Table 2: 10.44 - 9.56 VPs"}', '2026-10-09', '16:15:00', '17:45:00'),
(@game_id, 'Round 4 (R-IV)', 'Table 3', @t4, @t7, @t4, 'completed', '{"type":"bridge","round_no":4,"round_label":"R-IV","table_no":3,"team1_num":4,"team2_num":7,"vps_a":10.44,"vps_b":9.56,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-IV Table 3: 10.44 - 9.56 VPs"}', '2026-10-09', '16:15:00', '17:45:00'),
(@game_id, 'Round 4 (R-IV)', 'Table 4', @t9, @t3, @t9, 'completed', '{"type":"bridge","round_no":4,"round_label":"R-IV","table_no":4,"team1_num":9,"team2_num":3,"vps_a":12.05,"vps_b":7.95,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-IV Table 4: 12.05 - 7.95 VPs"}', '2026-10-09', '16:15:00', '17:45:00'),
(@game_id, 'Round 4 (R-IV)', 'Table 5', @t10, @t8, @t10, 'completed', '{"type":"bridge","round_no":4,"round_label":"R-IV","table_no":5,"team1_num":10,"team2_num":8,"vps_a":10.86,"vps_b":9.14,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-IV Table 5: 10.86 - 9.14 VPs"}', '2026-10-09', '16:15:00', '17:45:00');

-- 8. Insert Round 5 (R-V) - 5 Concluded Tables
INSERT INTO `matches` (`game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `winner_id`, `status`, `scores_json`, `match_date`, `start_time`, `end_time`) VALUES
(@game_id, 'Round 5 (R-V)', 'Table 1', @t2, @t7, @t2, 'completed', '{"type":"bridge","round_no":5,"round_label":"R-V","table_no":1,"team1_num":2,"team2_num":7,"vps_a":20.00,"vps_b":0.00,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-V Table 1: 20 - 0 VPs"}', '2026-10-09', '18:00:00', '19:30:00'),
(@game_id, 'Round 5 (R-V)', 'Table 2', @t5, @t1, @t5, 'completed', '{"type":"bridge","round_no":5,"round_label":"R-V","table_no":2,"team1_num":5,"team2_num":1,"vps_a":15.23,"vps_b":4.77,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-V Table 2: 15.23 - 4.77 VPs"}', '2026-10-09', '18:00:00', '19:30:00'),
(@game_id, 'Round 5 (R-V)', 'Table 3', @t8, @t6, @t8, 'completed', '{"type":"bridge","round_no":5,"round_label":"R-V","table_no":3,"team1_num":8,"team2_num":6,"vps_a":16.90,"vps_b":3.10,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-V Table 3: 16.90 - 3.10 VPs"}', '2026-10-09', '18:00:00', '19:30:00'),
(@game_id, 'Round 5 (R-V)', 'Table 4', @t9, @t4, @t9, 'completed', '{"type":"bridge","round_no":5,"round_label":"R-V","table_no":4,"team1_num":9,"team2_num":4,"vps_a":11.27,"vps_b":8.73,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-V Table 4: 11.27 - 8.73 VPs"}', '2026-10-09', '18:00:00', '19:30:00'),
(@game_id, 'Round 5 (R-V)', 'Table 5', @t3, @t10, @t3, 'completed', '{"type":"bridge","round_no":5,"round_label":"R-V","table_no":5,"team1_num":3,"team2_num":10,"vps_a":18.83,"vps_b":1.17,"imps_a":0,"imps_b":0,"status":"completed","summary":"R-V Table 5: 18.83 - 1.17 VPs"}', '2026-10-09', '18:00:00', '19:30:00');

SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
