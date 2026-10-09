-- =============================================================================
-- HPCL TOURNAMENT 2026 - BADMINTON DRAWS & MATCHES UPDATE
-- Generated: 2026-10-09 07:39:27
-- Discipline: Badminton (Game ID: 1)
-- Description: Updates courts, team pool seeds, and inserts 58 official matches
-- =============================================================================

-- 1. Facilities: Ensure Badminton Courts 1, 2, 3, 4 exist
UPDATE `facilities` SET `name` = 'Badminton Court 1', `court_number` = 'Court 1', `location` = 'Indoor Sports Complex' WHERE `id` = 1;
UPDATE `facilities` SET `name` = 'Badminton Court 2', `court_number` = 'Court 2', `location` = 'Indoor Sports Complex' WHERE `id` = 785;

INSERT INTO `facilities` (`id`, `name`, `type`, `location`, `court_number`) 
SELECT 789, 'Badminton Court 3', 'court', 'Indoor Sports Complex', 'Court 3'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `facilities` WHERE `name` = 'Badminton Court 3');

INSERT INTO `facilities` (`id`, `name`, `type`, `location`, `court_number`) 
SELECT 790, 'Badminton Court 4', 'court', 'Indoor Sports Complex', 'Court 4'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `facilities` WHERE `name` = 'Badminton Court 4');

-- 2. Teams: Update Badminton Team Pool & Seed Assignments (Game ID: 1)
UPDATE `teams` SET `pool` = 'A', `seed` = 5 WHERE `id` = 882; -- EZ Badminton
UPDATE `teams` SET `pool` = 'B', `seed` = 4 WHERE `id` = 891; -- HB Badminton
UPDATE `teams` SET `pool` = 'B', `seed` = 6 WHERE `id` = 810; -- MF Badminton
UPDATE `teams` SET `pool` = 'A', `seed` = 1 WHERE `id` = 855; -- MR Badminton
UPDATE `teams` SET `pool` = 'A', `seed` = 6 WHERE `id` = 801; -- NCZ Badminton
UPDATE `teams` SET `pool` = 'B', `seed` = 5 WHERE `id` = 864; -- NWZ Badminton
UPDATE `teams` SET `pool` = 'A', `seed` = 3 WHERE `id` = 846; -- NZ Badminton
UPDATE `teams` SET `pool` = 'A', `seed` = 4 WHERE `id` = 819; -- PH Badminton
UPDATE `teams` SET `pool` = 'B', `seed` = 2 WHERE `id` = 873; -- SCZ Badminton
UPDATE `teams` SET `pool` = 'B', `seed` = 3 WHERE `id` = 828; -- SZ Badminton
UPDATE `teams` SET `pool` = 'B', `seed` = 1 WHERE `id` = 792; -- VR Badminton
UPDATE `teams` SET `pool` = 'A', `seed` = 2 WHERE `id` = 837; -- WZ Badminton

-- 3. Matches: Clear existing Badminton matches (Game ID: 1)
DELETE FROM `matches` WHERE `game_id` = 1;

-- 4. Matches: Insert 58 Official Badminton Matches (50 Pool Matches + 8 Knockouts)
INSERT INTO `matches` (`id`, `game_id`, `round`, `pool_name`, `team1_id`, `team2_id`, `facility_id`, `score_format_id`, `match_date`, `start_time`, `end_time`, `status`, `is_published`) VALUES
(714, 1, 'Men\'s Pool A - Match 1 (A1 vs A6)', 'A', 855, 801, 1, 3, '2026-10-09', '09:00:00', '09:40:00', 'scheduled', 1),
(715, 1, 'Men\'s Pool A - Match 2 (A2 vs A5)', 'A', 837, 882, 785, 3, '2026-10-09', '09:00:00', '09:40:00', 'scheduled', 1),
(716, 1, 'Men\'s Pool A - Match 3 (A3 vs A4)', 'A', 846, 819, 1, 3, '2026-10-09', '10:20:00', '11:00:00', 'scheduled', 1),
(717, 1, 'Men\'s Pool A - Match 4 (A1 vs A5)', 'A', 855, 882, 785, 3, '2026-10-09', '10:20:00', '11:00:00', 'scheduled', 1),
(718, 1, 'Men\'s Pool A - Match 5 (A6 vs A4)', 'A', 801, 819, 1, 3, '2026-10-09', '11:40:00', '12:20:00', 'scheduled', 1),
(719, 1, 'Men\'s Pool A - Match 6 (A2 vs A3)', 'A', 837, 846, 785, 3, '2026-10-09', '11:40:00', '12:20:00', 'scheduled', 1),
(720, 1, 'Men\'s Pool A - Match 7 (A1 vs A4)', 'A', 855, 819, 1, 3, '2026-10-09', '14:00:00', '14:40:00', 'scheduled', 1),
(721, 1, 'Men\'s Pool A - Match 8 (A5 vs A3)', 'A', 882, 846, 785, 3, '2026-10-09', '14:00:00', '14:40:00', 'scheduled', 1),
(722, 1, 'Men\'s Pool A - Match 9 (A6 vs A2)', 'A', 801, 837, 1, 3, '2026-10-09', '15:20:00', '16:00:00', 'scheduled', 1),
(723, 1, 'Men\'s Pool A - Match 10 (A1 vs A3)', 'A', 855, 846, 785, 3, '2026-10-09', '15:20:00', '16:00:00', 'scheduled', 1),
(724, 1, 'Men\'s Pool A - Match 11 (A4 vs A2)', 'A', 819, 837, 1, 3, '2026-10-09', '16:40:00', '17:20:00', 'scheduled', 1),
(725, 1, 'Men\'s Pool A - Match 12 (A5 vs A6)', 'A', 882, 801, 785, 3, '2026-10-09', '16:40:00', '17:20:00', 'scheduled', 1),
(726, 1, 'Men\'s Pool A - Match 13 (A1 vs A2)', 'A', 855, 837, 1, 3, '2026-10-09', '17:20:00', '18:00:00', 'scheduled', 1),
(727, 1, 'Men\'s Pool A - Match 14 (A3 vs A6)', 'A', 846, 801, 785, 3, '2026-10-09', '17:20:00', '18:00:00', 'scheduled', 1),
(728, 1, 'Men\'s Pool A - Match 15 (A4 vs A5)', 'A', 819, 882, 1, 3, '2026-10-09', '18:00:00', '18:40:00', 'scheduled', 1),
(729, 1, 'Men\'s Pool B - Match 1 (B1 vs B6)', 'B', 792, 810, 789, 3, '2026-10-09', '09:00:00', '09:40:00', 'scheduled', 1),
(730, 1, 'Men\'s Pool B - Match 2 (B2 vs B5)', 'B', 873, 864, 790, 3, '2026-10-09', '09:00:00', '09:40:00', 'scheduled', 1),
(731, 1, 'Men\'s Pool B - Match 3 (B3 vs B4)', 'B', 828, 891, 789, 3, '2026-10-09', '10:20:00', '11:00:00', 'scheduled', 1),
(732, 1, 'Men\'s Pool B - Match 4 (B1 vs B5)', 'B', 792, 864, 790, 3, '2026-10-09', '10:20:00', '11:00:00', 'scheduled', 1),
(733, 1, 'Men\'s Pool B - Match 5 (B6 vs B4)', 'B', 810, 891, 789, 3, '2026-10-09', '11:40:00', '12:20:00', 'scheduled', 1),
(734, 1, 'Men\'s Pool B - Match 6 (B2 vs B3)', 'B', 873, 828, 790, 3, '2026-10-09', '11:40:00', '12:20:00', 'scheduled', 1),
(735, 1, 'Men\'s Pool B - Match 7 (B1 vs B4)', 'B', 792, 891, 789, 3, '2026-10-09', '14:00:00', '14:40:00', 'scheduled', 1),
(736, 1, 'Men\'s Pool B - Match 8 (B5 vs B3)', 'B', 864, 828, 790, 3, '2026-10-09', '14:00:00', '14:40:00', 'scheduled', 1),
(737, 1, 'Men\'s Pool B - Match 9 (B6 vs B2)', 'B', 810, 873, 789, 3, '2026-10-09', '15:20:00', '16:00:00', 'scheduled', 1),
(738, 1, 'Men\'s Pool B - Match 10 (B1 vs B3)', 'B', 792, 828, 790, 3, '2026-10-09', '15:20:00', '16:00:00', 'scheduled', 1),
(739, 1, 'Men\'s Pool B - Match 11 (B4 vs B2)', 'B', 891, 873, 789, 3, '2026-10-09', '16:40:00', '17:20:00', 'scheduled', 1),
(740, 1, 'Men\'s Pool B - Match 12 (B5 vs B6)', 'B', 864, 810, 790, 3, '2026-10-09', '16:40:00', '17:20:00', 'scheduled', 1),
(741, 1, 'Men\'s Pool B - Match 13 (B1 vs B2)', 'B', 792, 873, 789, 3, '2026-10-09', '17:20:00', '18:00:00', 'scheduled', 1),
(742, 1, 'Men\'s Pool B - Match 14 (B3 vs B6)', 'B', 828, 810, 790, 3, '2026-10-09', '17:20:00', '18:00:00', 'scheduled', 1),
(743, 1, 'Men\'s Pool B - Match 15 (B4 vs B5)', 'B', 891, 864, 789, 3, '2026-10-09', '18:00:00', '18:40:00', 'scheduled', 1),
(744, 1, 'Women\'s Pool A - Match 1 (A1 vs A2)', 'A', 819, 864, 1, 3, '2026-10-09', '09:40:00', '10:20:00', 'scheduled', 1),
(745, 1, 'Women\'s Pool A - Match 2 (A3 vs A4)', 'A', 810, 846, 785, 3, '2026-10-09', '09:40:00', '10:20:00', 'scheduled', 1),
(746, 1, 'Women\'s Pool A - Match 3 (A1 vs A5)', 'A', 819, 882, 1, 3, '2026-10-09', '11:00:00', '11:40:00', 'scheduled', 1),
(747, 1, 'Women\'s Pool A - Match 4 (A2 vs A3)', 'A', 864, 810, 785, 3, '2026-10-09', '11:00:00', '11:40:00', 'scheduled', 1),
(748, 1, 'Women\'s Pool A - Match 5 (A4 vs A5)', 'A', 846, 882, 1, 3, '2026-10-09', '12:20:00', '13:00:00', 'scheduled', 1),
(749, 1, 'Women\'s Pool A - Match 6 (A1 vs A3)', 'A', 819, 810, 785, 3, '2026-10-09', '12:20:00', '13:00:00', 'scheduled', 1),
(750, 1, 'Women\'s Pool A - Match 7 (A2 vs A4)', 'A', 864, 846, 1, 3, '2026-10-09', '14:40:00', '15:20:00', 'scheduled', 1),
(751, 1, 'Women\'s Pool A - Match 8 (A3 vs A5)', 'A', 810, 882, 785, 3, '2026-10-09', '14:40:00', '15:20:00', 'scheduled', 1),
(752, 1, 'Women\'s Pool A - Match 9 (A1 vs A4)', 'A', 819, 846, 1, 3, '2026-10-09', '16:00:00', '16:40:00', 'scheduled', 1),
(753, 1, 'Women\'s Pool A - Match 10 (A2 vs A5)', 'A', 864, 882, 785, 3, '2026-10-09', '16:00:00', '16:40:00', 'scheduled', 1),
(754, 1, 'Women\'s Pool B - Match 1 (B1 vs B2)', 'B', 792, 855, 789, 3, '2026-10-09', '09:40:00', '10:20:00', 'scheduled', 1),
(755, 1, 'Women\'s Pool B - Match 2 (B3 vs B4)', 'B', 828, 873, 790, 3, '2026-10-09', '09:40:00', '10:20:00', 'scheduled', 1),
(756, 1, 'Women\'s Pool B - Match 3 (B1 vs B5)', 'B', 792, 801, 789, 3, '2026-10-09', '11:00:00', '11:40:00', 'scheduled', 1),
(757, 1, 'Women\'s Pool B - Match 4 (B2 vs B3)', 'B', 855, 828, 790, 3, '2026-10-09', '11:00:00', '11:40:00', 'scheduled', 1),
(758, 1, 'Women\'s Pool B - Match 5 (B4 vs B5)', 'B', 873, 801, 789, 3, '2026-10-09', '12:20:00', '13:00:00', 'scheduled', 1),
(759, 1, 'Women\'s Pool B - Match 6 (B1 vs B3)', 'B', 792, 828, 790, 3, '2026-10-09', '12:20:00', '13:00:00', 'scheduled', 1),
(760, 1, 'Women\'s Pool B - Match 7 (B2 vs B4)', 'B', 855, 873, 789, 3, '2026-10-09', '14:40:00', '15:20:00', 'scheduled', 1),
(761, 1, 'Women\'s Pool B - Match 8 (B3 vs B5)', 'B', 828, 801, 790, 3, '2026-10-09', '14:40:00', '15:20:00', 'scheduled', 1),
(762, 1, 'Women\'s Pool B - Match 9 (B1 vs B4)', 'B', 792, 873, 789, 3, '2026-10-09', '16:00:00', '16:40:00', 'scheduled', 1),
(763, 1, 'Women\'s Pool B - Match 10 (B2 vs B5)', 'B', 855, 801, 790, 3, '2026-10-09', '16:00:00', '16:40:00', 'scheduled', 1),
(764, 1, 'Women\'s Semifinal 1 (Winner A vs Runner-up B)', 'Knockout', 819, 855, 1, 3, '2026-10-10', '10:00:00', '10:50:00', 'scheduled', 1),
(765, 1, 'Women\'s Semifinal 2 (Winner B vs Runner-up A)', 'Knockout', 792, 864, 785, 3, '2026-10-10', '10:00:00', '10:50:00', 'scheduled', 1),
(766, 1, 'Women\'s 3rd Place Playoff (Bronze Medal)', 'Knockout', 855, 864, 1, 3, '2026-10-10', '14:00:00', '14:50:00', 'scheduled', 1),
(767, 1, 'Women\'s Championship Final (Gold & Silver)', 'Knockout', 819, 792, 785, 3, '2026-10-10', '14:00:00', '15:00:00', 'scheduled', 1),
(768, 1, 'Men\'s Semifinal 1 (Winner A vs Runner-up B)', 'Knockout', 855, 873, 1, 3, '2026-10-10', '11:00:00', '12:00:00', 'scheduled', 1),
(769, 1, 'Men\'s Semifinal 2 (Winner B vs Runner-up A)', 'Knockout', 792, 837, 785, 3, '2026-10-10', '11:00:00', '12:00:00', 'scheduled', 1),
(770, 1, 'Men\'s 3rd Place Playoff (Bronze Medal)', 'Knockout', 873, 837, 1, 3, '2026-10-10', '15:30:00', '16:45:00', 'scheduled', 1),
(771, 1, 'Men\'s Championship Final (Gold & Silver)', 'Knockout', 855, 792, 785, 3, '2026-10-10', '15:30:00', '17:00:00', 'scheduled', 1);

-- =============================================================================
-- END OF SCRIPT
-- =============================================================================
