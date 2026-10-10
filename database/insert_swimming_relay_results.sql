-- Swimming 2 X 50m Freestyle Relay Results (Day II - 10.10.2026)
-- Venue: Aquatic Complex - Shree Shiv Chhatrapati Sports Complex, Mahalunge, Balewadi

-- 1. Clean existing relay matches if any
DELETE FROM matches WHERE game_id = 781 AND round LIKE '%Relay%';

-- 2. Insert Event 5: 2 X 50m Freestyle Relay (Men) - Final
INSERT INTO matches (
    game_id, round, pool_name, team1_id, team2_id, winner_id, 
    facility_id, match_date, start_time, end_time, status, is_published, scores_json
) VALUES (
    781, 
    '2 X 50m Freestyle Relay (Men) - Final', 
    'Final', 
    797, 
    NULL, 
    797, 
    783, 
    '2026-10-10', 
    '10:00:00', 
    '10:30:00', 
    'completed', 
    1, 
    '{"type":"swimming","event_name":"2 X 50m Freestyle Relay (Men)","category":"Team Event","heat":"2 X 50m Freestyle Relay (Men) - Final","heat_no":"2 X 50m Freestyle Relay (Men) - Final","stage":"Final","participants":"11 Relay Teams","status":"completed","winner_name":"VR","summary":"2 X 50m Freestyle Relay (Men) Final: 1st VR (01.04.56), 2nd MR - A (01.06.36), 3rd EZ (01.22.71)","lanes":[{"lane":1,"swimmer":"VR","zone":"VR","time":"01.04.56","position":"1st","pos":"1st","place":"I","status":"NORMAL"},{"lane":2,"swimmer":"MR - A","zone":"MR","time":"01.06.36","position":"2nd","pos":"2nd","place":"II","status":"NORMAL"},{"lane":3,"swimmer":"EZ","zone":"EZ","time":"01.22.71","position":"3rd","pos":"3rd","place":"III","status":"NORMAL"},{"lane":4,"swimmer":"PH","zone":"PH","time":"01.29.00","position":"4th","pos":"4th","place":"IV","status":"NORMAL"},{"lane":5,"swimmer":"HB","zone":"HB","time":"01.39.02","position":"5th","pos":"5th","place":"V","status":"NORMAL"},{"lane":6,"swimmer":"SCZ - B","zone":"SCZ","time":"01.43.11","position":"6th","pos":"6th","place":"","status":"NORMAL"},{"lane":7,"swimmer":"NCZ","zone":"NCZ","time":"01.53.51","position":"7th","pos":"7th","place":"","status":"NORMAL"},{"lane":8,"swimmer":"SCZ - A","zone":"SCZ","time":"01.56.04","position":"8th","pos":"8th","place":"","status":"NORMAL"},{"lane":9,"swimmer":"MR - B","zone":"MR","time":"02.00.78","position":"9th","pos":"9th","place":"","status":"NORMAL"},{"lane":10,"swimmer":"SZ","zone":"SZ","time":"02.11.41","position":"10th","pos":"10th","place":"","status":"NORMAL"},{"lane":11,"swimmer":"MF","zone":"MF","time":"02.29.76","position":"11th","pos":"11th","place":"","status":"NORMAL"}]}'
);

-- 3. Insert Event 6: 2 X 50m Freestyle Relay (Women) - Final
INSERT INTO matches (
    game_id, round, pool_name, team1_id, team2_id, winner_id, 
    facility_id, match_date, start_time, end_time, status, is_published, scores_json
) VALUES (
    781, 
    '2 X 50m Freestyle Relay (Women) - Final', 
    'Final', 
    824, 
    NULL, 
    824, 
    783, 
    '2026-10-10', 
    '10:30:00', 
    '11:00:00', 
    'completed', 
    1, 
    '{"type":"swimming","event_name":"2 X 50m Freestyle Relay (Women)","category":"Team Event","heat":"2 X 50m Freestyle Relay (Women) - Final","heat_no":"2 X 50m Freestyle Relay (Women) - Final","stage":"Final","participants":"3 Relay Teams","status":"completed","winner_name":"PH","summary":"2 X 50m Freestyle Relay (Women) Final: 1st PH (01.54.28), 2nd MR (02.33.31), 3rd SCZ (03.29.46)","lanes":[{"lane":1,"swimmer":"PH","zone":"PH","time":"01.54.28","position":"1st","pos":"1st","place":"I","status":"NORMAL"},{"lane":2,"swimmer":"MR","zone":"MR","time":"02.33.31","position":"2nd","pos":"2nd","place":"II","status":"NORMAL"},{"lane":3,"swimmer":"SCZ","zone":"SCZ","time":"03.29.46","position":"3rd","pos":"3rd","place":"III","status":"NORMAL"}]}'
);

-- 4. Master events schedule
DELETE FROM master_events WHERE date = '2026-10-10' AND event_name LIKE '%Relay%';
INSERT INTO master_events (event_name, event_type, date, start_time, end_time, location, description)
VALUES 
('2 X 50m Freestyle Relay (Men) - Final', 'match', '2026-10-10', '10:00:00', '10:30:00', 'Aquatic Complex - Balewadi', 'Men Relay Final - 11 Teams'),
('2 X 50m Freestyle Relay (Women) - Final', 'match', '2026-10-10', '10:30:00', '11:00:00', 'Aquatic Complex - Balewadi', 'Women Relay Final - 3 Teams');
