<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json');

// GET: Fetch albums or single album with its photos
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $albumId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($albumId > 0) {
        // Fetch specific album details
        $stmt = $pdo->prepare("
            SELECT a.*, g.name as game_name, u.username as creator_name,
                   m.round as match_round, t1.name as team1_name, t2.name as team2_name,
                   u1.short_code as u1_code, u2.short_code as u2_code,
                   cp.file_path as cover_file_path, cp.compressed_path as cover_compressed_path
            FROM albums a
            LEFT JOIN games g ON a.game_id = g.id
            LEFT JOIN users u ON a.created_by = u.id
            LEFT JOIN matches m ON a.match_id = m.id
            LEFT JOIN teams t1 ON m.team1_id = t1.id
            LEFT JOIN teams t2 ON m.team2_id = t2.id
            LEFT JOIN units u1 ON t1.unit_id = u1.id
            LEFT JOIN units u2 ON t2.unit_id = u2.id
            LEFT JOIN photos cp ON a.cover_photo_id = cp.id
            WHERE a.id = ?
        ");
        $stmt->execute([$albumId]);
        $album = $stmt->fetch();

        if (!$album) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Album not found']);
            exit;
        }

        // Fetch all photos in this album
        $pStmt = $pdo->prepare("
            SELECT p.id, p.album_id, p.event_type, p.title, p.day, p.game_id, p.match_id,
                   p.file_name, p.file_path, p.compressed_path, p.caption, p.uploaded_at
            FROM photos p
            WHERE p.album_id = ?
            ORDER BY p.uploaded_at ASC, p.id ASC
        ");
        $pStmt->execute([$albumId]);
        $photos = $pStmt->fetchAll();

        // If cover_file_path is missing, fall back to first photo
        if (empty($album['cover_file_path']) && !empty($photos)) {
            $album['cover_file_path'] = $photos[0]['file_path'];
            $album['cover_compressed_path'] = $photos[0]['compressed_path'];
        }

        echo json_encode([
            'status' => 'success',
            'album' => $album,
            'photos' => $photos,
            'total_photos' => count($photos)
        ]);
        exit;
    }

    // Fetch list of all albums
    $eventType = trim($_GET['event_type'] ?? '');
    $day = (int)($_GET['day'] ?? 0);

    $sql = "
        SELECT a.*, g.name as game_name,
               COUNT(p.id) as photo_count,
               COALESCE(cp.file_path, MIN(p.file_path)) as cover_file_path,
               COALESCE(cp.compressed_path, MIN(p.compressed_path)) as cover_compressed_path
        FROM albums a
        LEFT JOIN games g ON a.game_id = g.id
        LEFT JOIN photos cp ON a.cover_photo_id = cp.id
        LEFT JOIN photos p ON a.id = p.album_id
        WHERE 1=1
    ";
    $params = [];

    if ($eventType && in_array($eventType, ['General', 'Meetings', 'Ceremony', 'Game'])) {
        $sql .= " AND a.event_type = ?";
        $params[] = $eventType;
    }
    if ($day > 0) {
        $sql .= " AND a.day = ?";
        $params[] = $day;
    }

    $sql .= " GROUP BY a.id ORDER BY a.created_at DESC, a.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $albums = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'albums' => $albums,
        'count' => count($albums)
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
