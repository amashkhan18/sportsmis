<?php
require_once 'config/helpers.php';

$msg = $_SESSION['flash_msg'] ?? '';
$err = $_SESSION['flash_err'] ?? '';
unset($_SESSION['flash_msg'], $_SESSION['flash_err']);

// ==========================================
// 1. AJAX & BACKEND HANDLERS
// ==========================================

// Action: Create Album (Used by parallel uploader & UI)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_album') {
    header('Content-Type: application/json');

    $eventType = trim($_POST['event_type'] ?? 'Game');
    if (!in_array($eventType, ['General', 'Meetings', 'Ceremony', 'Game'])) {
        $eventType = 'Game';
    }

    $day = (int)($_POST['day'] ?? 1);
    $title = trim($_POST['title'] ?? '');
    $game_id = null;
    $match_id = null;

    if ($eventType === 'Game') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        $match_id = (int)($_POST['match_id'] ?? 0);

        if (!$game_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Sports Discipline is required for Game album.']);
            exit;
        }
        if (!$match_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Link to Match is mandatory for Game album.']);
            exit;
        }

        if (empty($title)) {
            $mStmt = $pdo->prepare("SELECT g.name as game_name, m.round, t1.name as t1, t2.name as t2 FROM matches m JOIN games g ON m.game_id = g.id LEFT JOIN teams t1 ON m.team1_id = t1.id LEFT JOIN teams t2 ON m.team2_id = t2.id WHERE m.id = ?");
            $mStmt->execute([$match_id]);
            $mRow = $mStmt->fetch();
            $title = $mRow ? "{$mRow['game_name']} - {$mRow['round']} (" . ($mRow['t1'] ?: 'TBD') . " vs " . ($mRow['t2'] ?: 'TBD') . ")" : "Game Match #$match_id Album";
        }
    } else {
        if (empty($title)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "Album Title is required for {$eventType}."]);
            exit;
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO albums (title, event_type, day, game_id, match_id, created_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$title, $eventType, $day, $game_id, $match_id, $_SESSION['user_id'] ?? 1]);
    $albumId = (int)$pdo->lastInsertId();

    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'ALBUM_CREATE', "Created album #$albumId: '$title' ($eventType, Day $day)");

    echo json_encode([
        'status' => 'success',
        'album_id' => $albumId,
        'title' => $title,
        'event_type' => $eventType,
        'day' => $day
    ]);
    exit;
}

// Action: Handle AJAX Single File Upload (Used by Parallel Drag & Drop Uploader)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo_ajax') {
    header('Content-Type: application/json');

    $eventType = trim($_POST['event_type'] ?? 'Game');
    if (!in_array($eventType, ['General', 'Meetings', 'Ceremony', 'Game'])) {
        $eventType = 'Game';
    }

    $day = (int)($_POST['day'] ?? 1);
    $caption = trim($_POST['caption'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $game_id = null;
    $match_id = null;
    $album_id = (int)($_POST['album_id'] ?? 0);

    if ($eventType === 'Game') {
        $game_id = (int)($_POST['game_id'] ?? 0);
        $match_id = (int)($_POST['match_id'] ?? 0);

        if (!$game_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Sports Discipline is required for Game photos.']);
            exit;
        }
        if (!$match_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Link to Match is mandatory for Game photos.']);
            exit;
        }

        // Get match title
        $mStmt = $pdo->prepare("SELECT g.name as game_name, m.round, t1.name as t1, t2.name as t2 FROM matches m JOIN games g ON m.game_id = g.id LEFT JOIN teams t1 ON m.team1_id = t1.id LEFT JOIN teams t2 ON m.team2_id = t2.id WHERE m.id = ?");
        $mStmt->execute([$match_id]);
        $mRow = $mStmt->fetch();
        if (empty($title)) {
            $title = $mRow ? "{$mRow['game_name']} - {$mRow['round']} (" . ($mRow['t1'] ?: 'TBD') . " vs " . ($mRow['t2'] ?: 'TBD') . ")" : "Game Match #$match_id";
        }

        $originalFolder = "uploads/photos/originals/Game/Day{$day}/Game{$game_id}/Match{$match_id}";
        $compressedFolder = "uploads/photos/compressed/Game/Day{$day}/Game{$game_id}/Match{$match_id}";
    } else {
        if (!$title) {
            $title = "{$eventType} Day {$day} Highlights";
        }
        $cleanEvent = preg_replace('/[^a-zA-Z0-9_-]/', '_', $eventType);
        $originalFolder = "uploads/photos/originals/{$cleanEvent}/Day{$day}";
        $compressedFolder = "uploads/photos/compressed/{$cleanEvent}/Day{$day}";
    }

    // Ensure Album Exists or create default
    if ($album_id <= 0) {
        $aStmt = $pdo->prepare("INSERT INTO albums (title, event_type, day, game_id, match_id, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $aStmt->execute([$title, $eventType, $day, $game_id, $match_id, $_SESSION['user_id'] ?? 1]);
        $album_id = (int)$pdo->lastInsertId();
    }

    if (!isset($_FILES['photo_file']) || $_FILES['photo_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No valid image file received.']);
        exit;
    }

    $tmpPath = $_FILES['photo_file']['tmp_name'];
    $origName = basename($_FILES['photo_file']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Unsupported format. Please upload JPG, PNG, or WEBP.']);
        exit;
    }

    if (!is_dir($originalFolder)) {
        mkdir($originalFolder, 0777, true);
    }
    if (!is_dir($compressedFolder)) {
        mkdir($compressedFolder, 0777, true);
    }

    $uniqueBase = time() . '_' . uniqid();
    $origFileName = $uniqueBase . '.' . $ext;
    $origFilePath = $originalFolder . '/' . $origFileName;

    $compFileName = $uniqueBase . '_thumb.jpg';
    $compFilePath = $compressedFolder . '/' . $compFileName;

    if (move_uploaded_file($tmpPath, $origFilePath)) {
        // Generate compressed web version using GD
        compress_and_save_photo($origFilePath, $compFilePath, 1200, 1200, 75);

        // Insert into database with album_id
        $stmt = $pdo->prepare("
            INSERT INTO photos (album_id, event_type, title, day, game_id, match_id, file_name, file_path, compressed_path, caption, uploaded_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $album_id,
            $eventType, 
            $title, 
            $day, 
            $game_id, 
            $match_id, 
            $origName, 
            $origFilePath, 
            $compFilePath, 
            $caption, 
            $_SESSION['user_id'] ?? 1
        ]);

        $newId = (int)$pdo->lastInsertId();

        // Update album cover_photo_id if empty
        $albStmt = $pdo->prepare("SELECT cover_photo_id FROM albums WHERE id = ?");
        $albStmt->execute([$album_id]);
        $curCover = $albStmt->fetchColumn();
        if (!$curCover) {
            $pdo->prepare("UPDATE albums SET cover_photo_id = ? WHERE id = ?")->execute([$newId, $album_id]);
        }

        log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'PHOTO_UPLOAD', "Uploaded photo #$newId to album #$album_id ({$eventType}, Day $day, '$origName') with dual versions");

        echo json_encode([
            'status' => 'success',
            'id' => $newId,
            'album_id' => $album_id,
            'file_name' => $origName,
            'file_path' => $origFilePath,
            'compressed_path' => $compFilePath,
            'title' => $title,
            'event_type' => $eventType
        ]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded photo to storage.']);
        exit;
    }
}

// Action: Move Photos to Another Album (Supports multi-select)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'move_photos') {
    $photoIds = $_POST['photo_ids'] ?? [];
    if (is_string($photoIds)) {
        $photoIds = array_filter(array_map('intval', explode(',', $photoIds)));
    } elseif (is_array($photoIds)) {
        $photoIds = array_filter(array_map('intval', $photoIds));
    }

    $targetAlbumId = (int)($_POST['target_album_id'] ?? 0);
    $newAlbumTitle = trim($_POST['new_album_title'] ?? '');

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['format']) && $_POST['format'] === 'json');

    if (empty($photoIds)) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No photos selected to move.']);
            exit;
        }
        $_SESSION['flash_err'] = 'No photos selected to move.';
        header('Location: ' . BASE_URL . '/admin/photos');
        exit;
    }

    // If user specified a new album title, create it
    if ($targetAlbumId <= 0 && !empty($newAlbumTitle)) {
        // Fetch properties from the first photo
        $samplePhoto = $pdo->prepare("SELECT * FROM photos WHERE id = ?");
        $samplePhoto->execute([$photoIds[0]]);
        $sRow = $samplePhoto->fetch();

        $cStmt = $pdo->prepare("INSERT INTO albums (title, event_type, day, game_id, match_id, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $cStmt->execute([
            $newAlbumTitle,
            $sRow['event_type'] ?? 'Game',
            $sRow['day'] ?? 1,
            $sRow['game_id'] ?? null,
            $sRow['match_id'] ?? null,
            $_SESSION['user_id'] ?? 1
        ]);
        $targetAlbumId = (int)$pdo->lastInsertId();
    }

    if ($targetAlbumId <= 0) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid destination album.']);
            exit;
        }
        $_SESSION['flash_err'] = 'Invalid destination album selected.';
        header('Location: ' . BASE_URL . '/admin/photos');
        exit;
    }

    // Verify target album exists
    $tStmt = $pdo->prepare("SELECT id, title FROM albums WHERE id = ?");
    $tStmt->execute([$targetAlbumId]);
    $targetAlbum = $tStmt->fetch();
    if (!$targetAlbum) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Target album does not exist.']);
            exit;
        }
        $_SESSION['flash_err'] = 'Target album not found.';
        header('Location: ' . BASE_URL . '/admin/photos');
        exit;
    }

    $inPlaceholders = implode(',', array_fill(0, count($photoIds), '?'));
    $updateStmt = $pdo->prepare("UPDATE photos SET album_id = ? WHERE id IN ($inPlaceholders)");
    $updateStmt->execute(array_merge([$targetAlbumId], $photoIds));

    // Update target album cover if null
    $pdo->prepare("UPDATE albums SET cover_photo_id = ? WHERE id = ? AND cover_photo_id IS NULL")->execute([$photoIds[0], $targetAlbumId]);

    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'PHOTOS_MOVED', "Moved " . count($photoIds) . " photos to album #$targetAlbumId ('{$targetAlbum['title']}')");

    if ($isAjax) {
        echo json_encode([
            'status' => 'success',
            'message' => count($photoIds) . " photo(s) moved to '{$targetAlbum['title']}' successfully.",
            'target_album_id' => $targetAlbumId
        ]);
        exit;
    }

    $_SESSION['flash_msg'] = count($photoIds) . " photo(s) moved to '{$targetAlbum['title']}' successfully.";
    header('Location: ' . BASE_URL . '/admin/photos?album_id=' . $targetAlbumId);
    exit;
}

// Action: Delete Single or Multiple Photos (Supports multi-select)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['delete_photo', 'delete_photos'])) {
    $photoIds = [];
    if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
        $photoIds[] = (int)$_POST['id'];
    } elseif (isset($_POST['photo_ids'])) {
        if (is_string($_POST['photo_ids'])) {
            $photoIds = array_filter(array_map('intval', explode(',', $_POST['photo_ids'])));
        } elseif (is_array($_POST['photo_ids'])) {
            $photoIds = array_filter(array_map('intval', $_POST['photo_ids']));
        }
    }

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['format']) && $_POST['format'] === 'json');

    if (empty($photoIds)) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No photos selected to delete.']);
            exit;
        }
        $_SESSION['flash_err'] = 'No photos selected for deletion.';
        header('Location: ' . BASE_URL . '/admin/photos');
        exit;
    }

    $deletedCount = 0;
    foreach ($photoIds as $pId) {
        $stmt = $pdo->prepare("SELECT file_path, compressed_path, album_id FROM photos WHERE id = ?");
        $stmt->execute([$pId]);
        $row = $stmt->fetch();
        if ($row) {
            if (!empty($row['file_path']) && file_exists($row['file_path'])) {
                @unlink($row['file_path']);
            }
            if (!empty($row['compressed_path']) && file_exists($row['compressed_path'])) {
                @unlink($row['compressed_path']);
            }

            $albumId = $row['album_id'];
            $pdo->prepare("DELETE FROM photos WHERE id = ?")->execute([$pId]);
            $deletedCount++;

            // If deleted photo was album cover, assign another photo as cover
            if ($albumId) {
                $nxtStmt = $pdo->prepare("SELECT id FROM photos WHERE album_id = ? ORDER BY id ASC LIMIT 1");
                $nxtStmt->execute([$albumId]);
                $nextCover = $nxtStmt->fetchColumn();
                $pdo->prepare("UPDATE albums SET cover_photo_id = ? WHERE id = ?")->execute([$nextCover ?: null, $albumId]);
            }
        }
    }

    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'PHOTOS_DELETED', "Deleted $deletedCount photo(s) (originals and compressed versions removed)");

    if ($isAjax) {
        echo json_encode(['status' => 'success', 'message' => "$deletedCount photo(s) deleted successfully.", 'deleted_count' => $deletedCount]);
        exit;
    }

    $_SESSION['flash_msg'] = "$deletedCount photo(s) removed successfully.";
    header('Location: ' . BASE_URL . '/admin/photos' . (isset($_GET['album_id']) ? '?album_id=' . (int)$_GET['album_id'] : ''));
    exit;
}

// Action: Delete Single or Multiple Albums (Supports multi-select)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['delete_album', 'delete_albums'])) {
    $albumIds = [];
    if (isset($_POST['album_id']) && (int)$_POST['album_id'] > 0) {
        $albumIds[] = (int)$_POST['album_id'];
    } elseif (isset($_POST['album_ids'])) {
        if (is_string($_POST['album_ids'])) {
            $albumIds = array_filter(array_map('intval', explode(',', $_POST['album_ids'])));
        } elseif (is_array($_POST['album_ids'])) {
            $albumIds = array_filter(array_map('intval', $_POST['album_ids']));
        }
    }

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['format']) && $_POST['format'] === 'json');

    if (empty($albumIds)) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No albums selected to delete.']);
            exit;
        }
        $_SESSION['flash_err'] = 'No albums selected for deletion.';
        header('Location: ' . BASE_URL . '/admin/photos');
        exit;
    }

    $deletedAlbumCount = 0;
    $deletedPhotosTotal = 0;

    foreach ($albumIds as $aId) {
        // Fetch all photos in this album to delete files
        $pStmt = $pdo->prepare("SELECT file_path, compressed_path FROM photos WHERE album_id = ?");
        $pStmt->execute([$aId]);
        $photos = $pStmt->fetchAll();

        foreach ($photos as $ph) {
            if (!empty($ph['file_path']) && file_exists($ph['file_path'])) {
                @unlink($ph['file_path']);
            }
            if (!empty($ph['compressed_path']) && file_exists($ph['compressed_path'])) {
                @unlink($ph['compressed_path']);
            }
            $deletedPhotosTotal++;
        }

        // Delete photos from DB
        $pdo->prepare("DELETE FROM photos WHERE album_id = ?")->execute([$aId]);
        // Delete album record
        $pdo->prepare("DELETE FROM albums WHERE id = ?")->execute([$aId]);
        $deletedAlbumCount++;
    }

    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'ALBUMS_DELETED', "Deleted $deletedAlbumCount album(s) and $deletedPhotosTotal photo file(s)");

    if ($isAjax) {
        echo json_encode([
            'status' => 'success',
            'message' => "$deletedAlbumCount album(s) and $deletedPhotosTotal photo(s) deleted successfully."
        ]);
        exit;
    }

    $_SESSION['flash_msg'] = "$deletedAlbumCount album(s) deleted successfully.";
    header('Location: ' . BASE_URL . '/admin/photos');
    exit;
}

// Action: Merge Two or More Albums
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'merge_albums') {
    $albumIds = [];
    if (isset($_POST['album_ids'])) {
        if (is_string($_POST['album_ids'])) {
            $albumIds = array_filter(array_map('intval', explode(',', $_POST['album_ids'])));
        } elseif (is_array($_POST['album_ids'])) {
            $albumIds = array_filter(array_map('intval', $_POST['album_ids']));
        }
    }

    $targetAlbumId = (int)($_POST['target_album_id'] ?? 0);
    $mergedTitle = trim($_POST['merged_title'] ?? '');

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['format']) && $_POST['format'] === 'json');

    if (count($albumIds) < 2) {
        if ($isAjax) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please select at least two albums to merge.']);
            exit;
        }
        $_SESSION['flash_err'] = 'Please select at least two albums to merge.';
        header('Location: ' . BASE_URL . '/admin/photos');
        exit;
    }

    if ($targetAlbumId <= 0 || !in_array($targetAlbumId, $albumIds)) {
        $targetAlbumId = $albumIds[0];
    }

    // Donor albums are all selected albums except target
    $donorIds = array_values(array_diff($albumIds, [$targetAlbumId]));

    if (!empty($donorIds)) {
        $donorPlaceholders = implode(',', array_fill(0, count($donorIds), '?'));
        
        // Count photos being moved
        $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM photos WHERE album_id IN ($donorPlaceholders)");
        $cntStmt->execute($donorIds);
        $movedPhotosCount = (int)$cntStmt->fetchColumn();

        // Move all photos to target album
        $moveStmt = $pdo->prepare("UPDATE photos SET album_id = ? WHERE album_id IN ($donorPlaceholders)");
        $moveStmt->execute(array_merge([$targetAlbumId], $donorIds));

        // Delete donor albums
        $delStmt = $pdo->prepare("DELETE FROM albums WHERE id IN ($donorPlaceholders)");
        $delStmt->execute($donorIds);
    } else {
        $movedPhotosCount = 0;
    }

    // If custom title provided, update target album title
    if (!empty($mergedTitle)) {
        $pdo->prepare("UPDATE albums SET title = ? WHERE id = ?")->execute([$mergedTitle, $targetAlbumId]);
    }

    // Ensure target album has a valid cover photo
    $covStmt = $pdo->prepare("SELECT id FROM photos WHERE album_id = ? ORDER BY id ASC LIMIT 1");
    $covStmt->execute([$targetAlbumId]);
    $covPhotoId = $covStmt->fetchColumn();
    if ($covPhotoId) {
        $pdo->prepare("UPDATE albums SET cover_photo_id = ? WHERE id = ?")->execute([$covPhotoId, $targetAlbumId]);
    }

    log_audit_event($pdo, $_SESSION['user_id'] ?? 1, 'ALBUMS_MERGED', "Merged " . count($donorIds) . " album(s) into album #$targetAlbumId. $movedPhotosCount photos consolidated.");

    if ($isAjax) {
        echo json_encode([
            'status' => 'success',
            'message' => "Successfully merged " . count($albumIds) . " albums into one!",
            'target_album_id' => $targetAlbumId
        ]);
        exit;
    }

    $_SESSION['flash_msg'] = "Successfully merged " . count($albumIds) . " albums into one.";
    header('Location: ' . BASE_URL . '/admin/photos?album_id=' . $targetAlbumId);
    exit;
}

// Action: Get Album Photos JSON (API for carousel viewer)
if ((isset($_GET['action']) && $_GET['action'] === 'get_album_photos') || 
    (isset($_POST['action']) && $_POST['action'] === 'get_album_photos')) {
    header('Content-Type: application/json');
    $albumId = (int)($_REQUEST['album_id'] ?? 0);

    $stmt = $pdo->prepare("
        SELECT a.*, g.name as game_name, u.username as creator_name
        FROM albums a
        LEFT JOIN games g ON a.game_id = g.id
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.id = ?
    ");
    $stmt->execute([$albumId]);
    $album = $stmt->fetch();

    if (!$album) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Album not found']);
        exit;
    }

    $pStmt = $pdo->prepare("
        SELECT id, album_id, event_type, title, day, game_id, match_id, file_name, file_path, compressed_path, caption, uploaded_at
        FROM photos
        WHERE album_id = ?
        ORDER BY uploaded_at ASC, id ASC
    ");
    $pStmt->execute([$albumId]);
    $photos = $pStmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'album' => $album,
        'photos' => $photos,
        'total' => count($photos)
    ]);
    exit;
}

// ==========================================
// 2. VIEW DATA QUERIES
// ==========================================

$currentView = trim($_GET['view'] ?? 'albums'); // 'albums' or 'photos'
$activeAlbumId = (int)($_GET['album_id'] ?? 0);
$filterEvent = trim($_GET['event_type'] ?? '');
$filterDay = (int)($_GET['day'] ?? 0);

// If an activeAlbumId is specified, fetch that album details
$activeAlbum = null;
if ($activeAlbumId > 0) {
    $aStmt = $pdo->prepare("
        SELECT a.*, g.name as game_name, u.username as creator_name,
               m.round as match_round, t1.name as t1, t2.name as t2
        FROM albums a
        LEFT JOIN games g ON a.game_id = g.id
        LEFT JOIN users u ON a.created_by = u.id
        LEFT JOIN matches m ON a.match_id = m.id
        LEFT JOIN teams t1 ON m.team1_id = t1.id
        LEFT JOIN teams t2 ON m.team2_id = t2.id
        WHERE a.id = ?
    ");
    $aStmt->execute([$activeAlbumId]);
    $activeAlbum = $aStmt->fetch();
    if ($activeAlbum) {
        $currentView = 'album_drilldown';
    }
}

// Query Albums List
$albumsSql = "
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
$albumsParams = [];
if ($filterEvent && in_array($filterEvent, ['General', 'Meetings', 'Ceremony', 'Game'])) {
    $albumsSql .= " AND a.event_type = ?";
    $albumsParams[] = $filterEvent;
}
if ($filterDay > 0) {
    $albumsSql .= " AND a.day = ?";
    $albumsParams[] = $filterDay;
}
$albumsSql .= " GROUP BY a.id ORDER BY a.created_at DESC, a.id DESC";
$albStmt = $pdo->prepare($albumsSql);
$albStmt->execute($albumsParams);
$allAlbums = $albStmt->fetchAll();

// Query Photos List (All photos or photos inside active album)
$photosSql = "
    SELECT p.*, a.title as album_title, g.name as game_name, u.username as uploader_name,
           m.round as match_round, t1.name as team1_name, t2.name as team2_name
    FROM photos p
    LEFT JOIN albums a ON p.album_id = a.id
    LEFT JOIN games g ON p.game_id = g.id
    LEFT JOIN users u ON p.uploaded_by = u.id
    LEFT JOIN matches m ON p.match_id = m.id
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    WHERE 1=1
";
$photosParams = [];
if ($currentView === 'album_drilldown' && $activeAlbumId > 0) {
    $photosSql .= " AND p.album_id = ?";
    $photosParams[] = $activeAlbumId;
} else {
    if ($filterEvent && in_array($filterEvent, ['General', 'Meetings', 'Ceremony', 'Game'])) {
        $photosSql .= " AND p.event_type = ?";
        $photosParams[] = $filterEvent;
    }
    if ($filterDay > 0) {
        $photosSql .= " AND p.day = ?";
        $photosParams[] = $filterDay;
    }
}
$photosSql .= " ORDER BY p.uploaded_at DESC, p.id DESC";
$pStmt = $pdo->prepare($photosSql);
$pStmt->execute($photosParams);
$allPhotos = $pStmt->fetchAll();

// Counts by Event Type
$eventCounts = [
    'All' => (int)$pdo->query("SELECT COUNT(*) FROM albums")->fetchColumn(),
    'Game' => (int)$pdo->query("SELECT COUNT(*) FROM albums WHERE event_type = 'Game'")->fetchColumn(),
    'Ceremony' => (int)$pdo->query("SELECT COUNT(*) FROM albums WHERE event_type = 'Ceremony'")->fetchColumn(),
    'Meetings' => (int)$pdo->query("SELECT COUNT(*) FROM albums WHERE event_type = 'Meetings'")->fetchColumn(),
    'General' => (int)$pdo->query("SELECT COUNT(*) FROM albums WHERE event_type = 'General'")->fetchColumn()
];

// All Games & Matches for Dropdowns
$allGames = $pdo->query("SELECT id, name, slug FROM games ORDER BY name ASC")->fetchAll();
$allMatches = $pdo->query("
    SELECT m.id, m.game_id, g.name as game_name, m.round, m.match_date, m.start_time,
           t1.name as team1_name, t2.name as team2_name,
           u1.short_code as u1_code, u2.short_code as u2_code
    FROM matches m 
    JOIN games g ON m.game_id = g.id 
    LEFT JOIN teams t1 ON m.team1_id = t1.id
    LEFT JOIN teams t2 ON m.team2_id = t2.id
    LEFT JOIN units u1 ON t1.unit_id = u1.id
    LEFT JOIN units u2 ON t2.unit_id = u2.id
    ORDER BY g.name ASC, m.match_date ASC, m.start_time ASC
")->fetchAll();

// Simple list of all albums for dropdowns (Move modal, etc.)
$albumDropdownList = $pdo->query("SELECT id, title, event_type, day FROM albums ORDER BY event_type ASC, title ASC")->fetchAll();
?>

<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2 align-items-center">
      <div class="col-sm-6">
        <h1 class="m-0 font-weight-bold">
          <i class="fas fa-camera text-primary mr-2"></i> Photographer Portal & Media Albums
        </h1>
        <p class="text-muted mb-0">Multi-file parallel drag & drop upload &bull; Auto-grouped batch albums &bull; Merging & batch management</p>
      </div>
      <div class="col-sm-6 text-right">
        <button class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#uploadPhotoModal">
          <i class="fas fa-cloud-upload-alt mr-1"></i> Multi-File Photo Upload
        </button>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">

    <?php if ($msg): ?>
      <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($msg) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <?php if ($err): ?>
      <div class="alert alert-danger alert-dismissible fade show shadow-sm">
        <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($err) ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
    <?php endif; ?>

    <!-- Navigation Breadcrumbs & View Toggle -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <div class="btn-group shadow-sm">
        <a href="?view=albums" class="btn <?= $currentView === 'albums' ? 'btn-primary font-weight-bold' : 'btn-outline-primary' ?>">
          <i class="fas fa-folder-open mr-1"></i> Albums Repository (<?= count($allAlbums) ?>)
        </a>
        <a href="?view=photos" class="btn <?= $currentView === 'photos' ? 'btn-primary font-weight-bold' : 'btn-outline-primary' ?>">
          <i class="fas fa-images mr-1"></i> All Photos (<?= count($allPhotos) ?>)
        </a>
      </div>

      <?php if ($currentView === 'album_drilldown' && $activeAlbum): ?>
        <div class="d-flex align-items-center">
          <a href="?view=albums" class="btn btn-sm btn-outline-secondary font-weight-bold mr-2">
            <i class="fas fa-arrow-left mr-1"></i> Back to Albums
          </a>
          <span class="badge badge-primary px-3 py-2 text-uppercase font-weight-bold">
            <i class="fas fa-folder mr-1"></i> <?= htmlspecialchars($activeAlbum['title']) ?> (<?= count($allPhotos) ?> Photos)
          </span>
        </div>
      <?php else: ?>
        <!-- Category Filter Pills -->
        <ul class="nav nav-pills mt-2 mt-md-0">
          <li class="nav-item">
            <a class="nav-link <?= empty($filterEvent) ? 'active' : '' ?>" href="?view=<?= $currentView ?>">
              All <span class="badge badge-light ml-1"><?= $eventCounts['All'] ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $filterEvent === 'Game' ? 'active' : '' ?>" href="?view=<?= $currentView ?>&event_type=Game">
              <i class="fas fa-trophy mr-1"></i> Game <span class="badge badge-light ml-1"><?= $eventCounts['Game'] ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $filterEvent === 'Ceremony' ? 'active' : '' ?>" href="?view=<?= $currentView ?>&event_type=Ceremony">
              <i class="fas fa-flag-checkered mr-1"></i> Ceremony <span class="badge badge-light ml-1"><?= $eventCounts['Ceremony'] ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $filterEvent === 'Meetings' ? 'active' : '' ?>" href="?view=<?= $currentView ?>&event_type=Meetings">
              <i class="fas fa-users-cog mr-1"></i> Meetings <span class="badge badge-light ml-1"><?= $eventCounts['Meetings'] ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $filterEvent === 'General' ? 'active' : '' ?>" href="?view=<?= $currentView ?>&event_type=General">
              <i class="fas fa-camera-retro mr-1"></i> General <span class="badge badge-light ml-1"><?= $eventCounts['General'] ?></span>
            </a>
          </li>
        </ul>
      <?php endif; ?>
    </div>

    <!-- ========================================== -->
    <!-- SECTION A: ALBUMS VIEW                     -->
    <!-- ========================================== -->
    <?php if ($currentView === 'albums'): ?>
      
      <!-- Multi-Select Album Action Toolbar (Appears when >= 1 album selected) -->
      <div id="albumsActionToolbar" class="card bg-gradient-navy text-white p-3 mb-3 shadow" style="display: none;">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <i class="fas fa-check-double fa-2x text-warning mr-3"></i>
            <div>
              <h5 class="mb-0 font-weight-bold"><span id="selectedAlbumsCount">0</span> Album(s) Selected</h5>
              <small class="text-white-50">Perform batch operations across chosen album collections</small>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            <button type="button" class="btn btn-warning font-weight-bold mr-2" id="btnMergeAlbums" onclick="openMergeAlbumsModal()" disabled>
              <i class="fas fa-object-group mr-1"></i> Merge Selected Albums
            </button>
            <button type="button" class="btn btn-danger font-weight-bold mr-2" onclick="confirmDeleteSelectedAlbums()">
              <i class="fas fa-trash-alt mr-1"></i> Delete Selected Albums
            </button>
            <button type="button" class="btn btn-outline-light" onclick="deselectAllAlbums()">
              Cancel Selection
            </button>
          </div>
        </div>
      </div>

      <!-- Albums Cards Grid -->
      <div class="card elevation-2 border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h3 class="card-title font-weight-bold mb-0">
            <i class="fas fa-layer-group text-primary mr-2"></i>
            <?= $filterEvent ? htmlspecialchars($filterEvent) . ' Albums' : 'Tournament Media Albums' ?>
          </h3>
          <div>
            <button type="button" class="btn btn-xs btn-outline-secondary mr-2" onclick="toggleSelectAllAlbums()">
              <i class="fas fa-check-square mr-1"></i> Select All
            </button>
            <span class="badge badge-primary px-3 py-1 font-weight-bold"><?= count($allAlbums) ?> Albums Found</span>
          </div>
        </div>

        <div class="card-body">
          <?php if (empty($allAlbums)): ?>
            <div class="text-center py-5 text-muted">
              <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
              <h5>No albums found.</h5>
              <p>Uploaded batches automatically create album collections. Click <strong>"Multi-File Photo Upload"</strong> to create one.</p>
            </div>
          <?php else: ?>
            <div class="row">
              <?php foreach ($allAlbums as $alb): ?>
                <?php
                  $badgeClass = 'badge-secondary';
                  if ($alb['event_type'] === 'Game') $badgeClass = 'badge-primary';
                  elseif ($alb['event_type'] === 'Ceremony') $badgeClass = 'badge-success';
                  elseif ($alb['event_type'] === 'Meetings') $badgeClass = 'badge-warning text-dark';
                  elseif ($alb['event_type'] === 'General') $badgeClass = 'badge-info';

                  $coverImg = !empty($alb['cover_compressed_path']) && file_exists($alb['cover_compressed_path']) 
                              ? $alb['cover_compressed_path'] 
                              : (!empty($alb['cover_file_path']) && file_exists($alb['cover_file_path']) ? $alb['cover_file_path'] : 'public/images/default-album.jpg');
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 album-card-col" id="album_card_<?= $alb['id'] ?>">
                  <div class="card h-100 shadow-sm border position-relative" style="border-radius: 10px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
                    
                    <!-- Top Checkbox Overlay -->
                    <div class="position-absolute" style="top: 10px; left: 10px; z-index: 5;">
                      <div class="custom-control custom-checkbox bg-dark px-2 py-1 rounded" style="background: rgba(0,0,0,0.65) !important;">
                        <input type="checkbox" class="custom-control-input album-checkbox" id="chk_album_<?= $alb['id'] ?>" value="<?= $alb['id'] ?>" data-title="<?= htmlspecialchars($alb['title']) ?>" onchange="updateAlbumSelectionState()">
                        <label class="custom-control-label text-white small" for="chk_album_<?= $alb['id'] ?>"></label>
                      </div>
                    </div>

                    <!-- Album Cover Image (Clicking opens Carousel Viewer) -->
                    <div style="height: 190px; overflow: hidden; background: #001f3f; position: relative; cursor: pointer;" onclick="openAlbumCarousel(<?= $alb['id'] ?>)">
                      <img src="<?= BASE_URL ?>/<?= htmlspecialchars($coverImg) ?>" 
                           class="w-100 h-100" 
                           style="object-fit: cover; transition: transform 0.3s ease;"
                           onmouseover="this.style.transform='scale(1.05)'"
                           onmouseout="this.style.transform='scale(1)'"
                           alt="<?= htmlspecialchars($alb['title']) ?>">
                      
                      <!-- Event Badge -->
                      <span class="badge <?= $badgeClass ?> position-absolute font-weight-bold text-uppercase" style="top: 10px; right: 10px;">
                        <?= htmlspecialchars($alb['event_type']) ?>
                      </span>

                      <!-- Photo Count Badge -->
                      <span class="badge badge-dark position-absolute font-weight-bold" style="bottom: 10px; right: 10px; background: rgba(0,0,0,0.75);">
                        <i class="fas fa-images mr-1 text-warning"></i> <?= $alb['photo_count'] ?> Photos
                      </span>

                      <!-- Day Badge -->
                      <span class="badge badge-dark position-absolute font-weight-bold" style="bottom: 10px; left: 10px; background: rgba(0,0,0,0.75);">
                        Day <?= $alb['day'] ?>
                      </span>
                    </div>

                    <!-- Album Body -->
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                      <div>
                        <h6 class="font-weight-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($alb['title']) ?>">
                          <?= htmlspecialchars($alb['title']) ?>
                        </h6>
                        <p class="small text-muted mb-2 text-truncate">
                          <?= $alb['game_name'] ? '<i class="fas fa-trophy mr-1 text-primary"></i>' . htmlspecialchars($alb['game_name']) : '<i class="fas fa-calendar-day mr-1"></i> Tournament Event' ?>
                        </p>
                      </div>

                      <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-xs btn-primary font-weight-bold" onclick="openAlbumCarousel(<?= $alb['id'] ?>)">
                          <i class="fas fa-play mr-1"></i> View Album
                        </button>
                        <a href="?view=album_drilldown&album_id=<?= $alb['id'] ?>" class="btn btn-xs btn-outline-info font-weight-bold">
                          <i class="fas fa-cog mr-1"></i> Manage Photos
                        </a>
                        <button type="button" class="btn btn-xs btn-outline-danger" onclick="confirmDeleteSingleAlbum(<?= $alb['id'] ?>, '<?= htmlspecialchars(addslashes($alb['title'])) ?>')">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </div>

                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <!-- ========================================== -->
    <!-- SECTION B: PHOTOS VIEW / ALBUM DRILLDOWN   -->
    <!-- ========================================== -->
    <?php else: ?>

      <!-- Multi-Select Photos Action Toolbar (Appears when >= 1 photo selected) -->
      <div id="photosActionToolbar" class="card bg-gradient-primary text-white p-3 mb-3 shadow" style="display: none;">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <i class="fas fa-images fa-2x text-warning mr-3"></i>
            <div>
              <h5 class="mb-0 font-weight-bold"><span id="selectedPhotosCount">0</span> Photo(s) Selected</h5>
              <small class="text-white-50">Move selected photos into another album or delete them in batch</small>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            <button type="button" class="btn btn-light font-weight-bold text-primary mr-2" onclick="openMovePhotosModal()">
              <i class="fas fa-folder-open mr-1"></i> Move to Another Album
            </button>
            <button type="button" class="btn btn-danger font-weight-bold mr-2" onclick="confirmDeleteSelectedPhotos()">
              <i class="fas fa-trash-alt mr-1"></i> Delete Selected Photos
            </button>
            <button type="button" class="btn btn-outline-light" onclick="deselectAllPhotos()">
              Cancel Selection
            </button>
          </div>
        </div>
      </div>

      <!-- Photos Grid -->
      <div class="card elevation-2 border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h3 class="card-title font-weight-bold mb-0">
            <i class="fas fa-photo-video text-primary mr-2"></i> 
            <?php if ($currentView === 'album_drilldown' && $activeAlbum): ?>
              Album: <?= htmlspecialchars($activeAlbum['title']) ?>
            <?php else: ?>
              <?= $filterEvent ? htmlspecialchars($filterEvent) . ' Photos' : 'All Tournament Photos' ?>
            <?php endif; ?>
          </h3>
          <div>
            <button type="button" class="btn btn-xs btn-outline-secondary mr-2" onclick="toggleSelectAllPhotos()">
              <i class="fas fa-check-square mr-1"></i> Select All
            </button>
            <span class="badge badge-primary px-3 py-1 font-weight-bold"><?= count($allPhotos) ?> Photos Found</span>
          </div>
        </div>

        <div class="card-body">
          <?php if (empty($allPhotos)): ?>
            <div class="text-center py-5 text-muted">
              <i class="fas fa-camera fa-3x mb-3 text-secondary"></i>
              <h5>No photos found in this view.</h5>
              <p>Click <strong>"Multi-File Photo Upload"</strong> to add action photos.</p>
            </div>
          <?php else: ?>
            <div class="row">
              <?php foreach ($allPhotos as $ph): ?>
                <?php
                  $badgeClass = 'badge-secondary';
                  if ($ph['event_type'] === 'Game') $badgeClass = 'badge-primary';
                  elseif ($ph['event_type'] === 'Ceremony') $badgeClass = 'badge-success';
                  elseif ($ph['event_type'] === 'Meetings') $badgeClass = 'badge-warning text-dark';
                  elseif ($ph['event_type'] === 'General') $badgeClass = 'badge-info';

                  $displayThumb = !empty($ph['compressed_path']) && file_exists($ph['compressed_path']) ? $ph['compressed_path'] : $ph['file_path'];
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4 photo-card-col" id="photo_card_<?= $ph['id'] ?>">
                  <div class="card h-100 shadow-sm border position-relative" style="border-radius: 8px; overflow: hidden;">
                    
                    <!-- Checkbox Overlay -->
                    <div class="position-absolute" style="top: 8px; left: 8px; z-index: 5;">
                      <div class="custom-control custom-checkbox bg-dark px-2 py-1 rounded" style="background: rgba(0,0,0,0.65) !important;">
                        <input type="checkbox" class="custom-control-input photo-checkbox" id="chk_photo_<?= $ph['id'] ?>" value="<?= $ph['id'] ?>" onchange="updatePhotoSelectionState()">
                        <label class="custom-control-label text-white small" for="chk_photo_<?= $ph['id'] ?>"></label>
                      </div>
                    </div>

                    <div style="height: 190px; overflow: hidden; background: #0b1f44; position: relative; cursor: pointer;" 
                         onclick="openAlbumCarousel(<?= (int)$ph['album_id'] ?>, <?= (int)$ph['id'] ?>)">
                      <img src="<?= BASE_URL ?>/<?= htmlspecialchars($displayThumb) ?>" 
                           class="card-img-top w-100 h-100" 
                           style="object-fit: cover;" 
                           alt="<?= htmlspecialchars($ph['file_name']) ?>">
                      
                      <span class="badge <?= $badgeClass ?> position-absolute font-weight-bold text-uppercase" style="top: 8px; right: 8px; font-size: 0.75rem;">
                        <?= htmlspecialchars($ph['event_type']) ?>
                      </span>
                      <span class="badge badge-dark position-absolute font-weight-bold" style="bottom: 8px; right: 8px; background: rgba(0,0,0,0.65);">
                        Day <?= $ph['day'] ?>
                      </span>
                    </div>

                    <div class="card-body p-2 d-flex flex-column justify-content-between">
                      <div>
                        <h6 class="font-weight-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($ph['title'] ?: $ph['file_name']) ?>" style="font-size: 0.9rem;">
                          <?= htmlspecialchars($ph['title'] ?: ($ph['game_name'] ? $ph['game_name'] . ' Match' : 'Tournament Photo')) ?>
                        </h6>
                        <small class="text-muted d-block text-truncate mb-1">
                          <i class="fas fa-folder text-warning mr-1"></i> Album: <?= htmlspecialchars($ph['album_title'] ?: 'Default Album') ?>
                        </small>
                        <?php if ($ph['caption']): ?>
                          <p class="small text-muted mb-2 text-truncate" style="font-size: 0.8rem; line-height: 1.3;" title="<?= htmlspecialchars($ph['caption']) ?>">
                            <i class="fas fa-quote-left text-light mr-1"></i><?= htmlspecialchars($ph['caption']) ?>
                          </p>
                        <?php endif; ?>
                      </div>

                      <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                        <span class="badge badge-light border text-muted" style="font-size: 0.7rem;">
                          <i class="fas fa-compress-arrows-alt text-success mr-1"></i> Dual-Version
                        </span>
                        <div class="btn-group">
                          <a href="<?= BASE_URL ?>/<?= htmlspecialchars($ph['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary" title="Download High-Res Original">
                            <i class="fas fa-download"></i>
                          </a>
                          <button type="button" class="btn btn-xs btn-outline-danger" onclick="confirmDeleteSinglePhoto(<?= $ph['id'] ?>)">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <?php endif; ?>

  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 1: MULTI-FILE PARALLEL BATCH UPLOADER (ALBUM CREATOR)   -->
<!-- ============================================================== -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-camera mr-2"></i> Photographer Multi-File Upload & Album Portal
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" onclick="resetUploadModal()">&times;</button>
      </div>

      <div class="modal-body">
        <!-- 1. EVENT TYPE SELECTION -->
        <div class="form-group mb-3">
          <label class="font-weight-bold text-dark d-block">
            Select Event Type <span class="text-danger">*</span>
          </label>
          <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
            <label class="btn btn-outline-primary active font-weight-bold py-2" onclick="setEventType('Game')">
              <input type="radio" name="event_type_choice" id="optGame" checked>
              <i class="fas fa-trophy mr-1"></i> Game Action
            </label>
            <label class="btn btn-outline-success font-weight-bold py-2" onclick="setEventType('Ceremony')">
              <input type="radio" name="event_type_choice" id="optCeremony">
              <i class="fas fa-flag-checkered mr-1"></i> Ceremony
            </label>
            <label class="btn btn-outline-warning font-weight-bold py-2 text-dark" onclick="setEventType('Meetings')">
              <input type="radio" name="event_type_choice" id="optMeetings">
              <i class="fas fa-users-cog mr-1"></i> Meetings
            </label>
            <label class="btn btn-outline-secondary font-weight-bold py-2" onclick="setEventType('General')">
              <input type="radio" name="event_type_choice" id="optGeneral">
              <i class="fas fa-camera-retro mr-1"></i> General
            </label>
          </div>
        </div>

        <!-- 2. METADATA & ALBUM DESTINATION -->
        <div class="card bg-light p-3 border mb-3">
          <div class="row">
            <div class="col-md-4 form-group mb-2">
              <label class="small font-weight-bold">Tournament Day <span class="text-danger">*</span></label>
              <select id="upload_day" class="form-control font-weight-bold" onchange="filterMatchesByGame()">
                <option value="1">Day 1 (8 Oct 2026)</option>
                <option value="2">Day 2 (9 Oct 2026)</option>
                <option value="3">Day 3 (10 Oct 2026)</option>
              </select>
            </div>

            <!-- NON-GAME SECTION: General / Meetings / Ceremony (Asks for Title) -->
            <div id="non_game_fields" class="col-md-8 form-group mb-2" style="display: none;">
              <label class="small font-weight-bold">
                Album / Event Title <span class="text-danger">*</span>
              </label>
              <input type="text" id="upload_title" class="form-control font-weight-bold" placeholder="e.g. Opening Ceremony Parade, TMM Technical Briefing">
              <small class="form-text text-muted">A dedicated album will be created for this batch of photos.</small>
            </div>

            <!-- GAME SECTION: Sports Discipline & Link to Match (Mandatory, No Time Slot) -->
            <div id="game_fields" class="col-md-8 mb-2">
              <div class="row">
                <div class="col-md-6 form-group mb-2">
                  <label class="small font-weight-bold">Sports Discipline <span class="text-danger">*</span></label>
                  <select id="upload_game_id" class="form-control font-weight-bold" onchange="filterMatchesByGame()">
                    <option value="">-- Select Sport (1 of 9) --</option>
                    <?php foreach ($allGames as $gm): ?>
                      <option value="<?= $gm['id'] ?>"><?= htmlspecialchars($gm['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-6 form-group mb-2">
                  <label class="small font-weight-bold text-danger">
                    <i class="fas fa-link mr-1"></i> Link to Match (Mandatory) <span class="text-danger">*</span>
                  </label>
                  <select id="upload_match_id" class="form-control font-weight-bold" required>
                    <option value="">-- Choose Sport First --</option>
                    <?php foreach ($allMatches as $m): ?>
                      <?php 
                        $t1 = $m['u1_code'] ?: ($m['team1_name'] ?: 'Team A');
                        $t2 = $m['u2_code'] ?: ($m['team2_name'] ?: 'Team B');
                      ?>
                      <option value="<?= $m['id'] ?>" data-game="<?= $m['game_id'] ?>">
                        #<?= $m['id'] ?> <?= htmlspecialchars($m['game_name']) ?>: <?= htmlspecialchars($t1) ?> vs <?= htmlspecialchars($t2) ?> (<?= htmlspecialchars($m['round']) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Album Destination Selector (Batch creates an album or attaches to existing) -->
          <div class="form-group mb-2 mt-1">
            <label class="small font-weight-bold text-dark d-flex justify-content-between">
              <span><i class="fas fa-layer-group text-primary mr-1"></i> Batch Album Assignment</span>
              <span class="text-muted">This batch will be bundled as an album</span>
            </label>
            <div class="d-flex align-items-center gap-3">
              <div class="custom-control custom-radio mr-3">
                <input type="radio" id="radNewAlbum" name="album_mode" class="custom-control-input" value="new" checked onchange="toggleAlbumMode()">
                <label class="custom-control-label small font-weight-bold" for="radNewAlbum">Create New Album for this Batch</label>
              </div>
              <div class="custom-control custom-radio">
                <input type="radio" id="radExistingAlbum" name="album_mode" class="custom-control-input" value="existing" onchange="toggleAlbumMode()">
                <label class="custom-control-label small font-weight-bold" for="radExistingAlbum">Add to Existing Album</label>
              </div>
            </div>
            
            <div id="existing_album_wrap" class="mt-2" style="display: none;">
              <select id="upload_existing_album_id" class="form-control font-weight-bold">
                <option value="">-- Choose Destination Album --</option>
                <?php foreach ($albumDropdownList as $adb): ?>
                  <option value="<?= $adb['id'] ?>">[<?= $adb['event_type'] ?> - Day <?= $adb['day'] ?>] <?= htmlspecialchars($adb['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Common Caption Field -->
          <div class="form-group mb-0 mt-2">
            <label class="small font-weight-bold text-muted">Caption / Athlete Names (Optional)</label>
            <input type="text" id="upload_caption" class="form-control" placeholder="e.g. Spectacular rally at match point, Gold medal podium ceremony">
          </div>
        </div>

        <!-- 3. DRAG & DROP MULTI-FILE ZONE -->
        <div class="form-group mb-3">
          <label class="font-weight-bold text-dark d-flex justify-content-between align-items-center">
            <span>Select / Drag High-Resolution Photos <span class="text-danger">*</span></span>
            <small class="text-muted"><i class="fas fa-images mr-1"></i> Multi-file parallel upload with dual-version storage</small>
          </label>

          <div id="dropZone" class="border border-primary rounded p-4 text-center" 
               style="border-style: dashed !important; border-width: 2px !important; background: #f8fafc; cursor: pointer; transition: all 0.2s ease;">
            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-2"></i>
            <h6 class="font-weight-bold text-dark mb-1">Drag and Drop Action Photos Here</h6>
            <p class="text-muted small mb-2">or click anywhere to browse from your device (Select multiple files at once)</p>
            <span class="btn btn-outline-primary btn-sm font-weight-bold">
              <i class="fas fa-folder-open mr-1"></i> Choose Photos
            </span>
            <input type="file" id="multiFilesInput" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
          </div>
        </div>

        <!-- 4. SELECTED FILES QUEUE & PARALLEL PROGRESS -->
        <div id="fileQueueContainer" style="display: none;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="font-weight-bold mb-0 text-dark">
              Upload Queue: <span id="queueCountBadge" class="badge badge-info">0 Files</span>
            </h6>
            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="clearFileQueue()">Clear Queue</button>
          </div>

          <div id="filesList" class="border rounded p-2 bg-white" style="max-height: 200px; overflow-y: auto;"></div>

          <!-- Overall Progress Bar -->
          <div id="overallProgressWrap" class="mt-3" style="display: none;">
            <div class="d-flex justify-content-between small font-weight-bold mb-1">
              <span id="overallStatusText" class="text-primary">Uploading & generating compressed versions...</span>
              <span id="overallPercentText">0%</span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 6px;">
              <div id="overallProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%;"></div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer bg-light d-flex justify-content-between">
        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal" onclick="resetUploadModal()">Cancel</button>
        <button type="button" id="startUploadBtn" class="btn btn-primary font-weight-bold shadow-sm" onclick="startParallelUpload()" disabled>
          <i class="fas fa-cloud-upload-alt mr-1"></i> Start Parallel Upload
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: MOVE PHOTOS TO ANOTHER ALBUM                          -->
<!-- ============================================================== -->
<div class="modal fade" id="movePhotosModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-folder-open mr-2"></i> Move Selected Photos to Album
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted">
          You are moving <strong id="moveModalCountText" class="text-primary font-weight-bold">0</strong> photo(s). Select a destination album or create a new one:
        </p>

        <div class="form-group mb-3">
          <label class="font-weight-bold small">Choose Destination Album</label>
          <select id="move_target_album_id" class="form-control font-weight-bold" onchange="toggleMoveNewAlbumInput()">
            <option value="">-- Select an Existing Album --</option>
            <?php foreach ($albumDropdownList as $adb): ?>
              <option value="<?= $adb['id'] ?>">[<?= $adb['event_type'] ?> - Day <?= $adb['day'] ?>] <?= htmlspecialchars($adb['title']) ?></option>
            <?php endforeach; ?>
            <option value="new_album">+ Create New Album...</option>
          </select>
        </div>

        <div id="move_new_album_field" class="form-group mb-3" style="display: none;">
          <label class="font-weight-bold small text-primary">New Album Title <span class="text-danger">*</span></label>
          <input type="text" id="move_new_album_title" class="form-control font-weight-bold" placeholder="e.g. Highlights of Finals, Medal Ceremony">
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary font-weight-bold" onclick="submitMovePhotos()">
          <i class="fas fa-check mr-1"></i> Confirm & Move Photos
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 3: MERGE ALBUMS MODAL                                    -->
<!-- ============================================================== -->
<div class="modal fade" id="mergeAlbumsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-object-group mr-2"></i> Merge Selected Albums
        </h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <p class="text-muted">
          Consolidate photos from <strong id="mergeModalCountText" class="text-dark font-weight-bold">0</strong> selected albums into a single merged album:
        </p>

        <div class="card bg-light p-2 mb-3 border">
          <h6 class="small font-weight-bold text-dark mb-1">Albums being merged:</h6>
          <ul id="mergeAlbumsList" class="small mb-0 pl-3"></ul>
        </div>

        <div class="form-group mb-3">
          <label class="font-weight-bold small">Primary Destination Album</label>
          <select id="merge_target_album_id" class="form-control font-weight-bold" onchange="onMergeTargetChange()"></select>
          <small class="form-text text-muted">All photos will be moved into this album, and donor albums will be merged.</small>
        </div>

        <div class="form-group mb-2">
          <label class="font-weight-bold small">Merged Album Title</label>
          <input type="text" id="merge_album_title" class="form-control font-weight-bold">
          <small class="form-text text-muted">Customize the title for the consolidated album.</small>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning font-weight-bold text-dark" onclick="submitMergeAlbums()">
          <i class="fas fa-object-group mr-1"></i> Merge Albums
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 4: FULLSCREEN ALBUM CAROUSEL VIEWER (SWIPE + BUTTONS)    -->
<!-- ============================================================== -->
<div class="modal fade" id="albumViewerModal" tabindex="-1" style="background: rgba(0,0,0,0.92);">
  <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 95vw;">
    <div class="modal-content bg-transparent border-0 text-white">
      
      <!-- Top Bar -->
      <div class="d-flex justify-content-between align-items-center p-3" style="background: rgba(0,0,0,0.6); border-radius: 8px 8px 0 0;">
        <div class="d-flex align-items-center">
          <span id="viewerBadge" class="badge badge-primary text-uppercase mr-2"></span>
          <span id="viewerDay" class="badge badge-secondary mr-2"></span>
          <h5 id="viewerAlbumTitle" class="modal-title font-weight-bold mb-0 text-truncate" style="max-width: 60vw;"></h5>
        </div>
        <div class="d-flex align-items-center">
          <span id="viewerCounter" class="badge badge-light px-3 py-1 font-weight-bold mr-3" style="font-size: 0.9rem;">Photo 0 of 0</span>
          <button type="button" class="close text-white" data-dismiss="modal" style="font-size: 2rem; opacity: 0.9;">&times;</button>
        </div>
      </div>

      <!-- Main Stage with Left/Right Buttons & Touch Swipe Image Area -->
      <div class="position-relative text-center p-0" id="viewerStage" style="background: #000; min-height: 520px; display: flex; align-items: center; justify-content: center; overflow: hidden; user-select: none;">
        
        <!-- Left Navigation Button -->
        <button type="button" class="btn position-absolute text-white" id="btnPrevPhoto" onclick="prevViewerPhoto()" 
                style="left: 15px; top: 50%; transform: translateY(-50%); z-index: 10; width: 55px; height: 55px; border-radius: 50%; background: rgba(0,0,0,0.55); border: 2px solid rgba(255,255,255,0.3); font-size: 1.5rem; transition: all 0.2s;"
                onmouseover="this.style.background='rgba(0,123,255,0.85)'; this.style.borderColor='#fff';"
                onmouseout="this.style.background='rgba(0,0,0,0.55)'; this.style.borderColor='rgba(255,255,255,0.3)';"
                title="Previous Photo (Left Arrow or Swipe Right)">
          <i class="fas fa-chevron-left"></i>
        </button>

        <!-- Image Display (Progressive Swap: Compressed loads first, 1 second later swapped with High-Res) -->
        <div id="viewerImgContainer" style="width: 100%; height: 65vh; display: flex; align-items: center; justify-content: center; position: relative;">
          <img id="viewerMainImg" src="" 
               style="max-width: 100%; max-height: 100%; object-fit: contain; transition: filter 0.4s ease, opacity 0.3s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.8);" 
               alt="Tournament Photo">
          
          <div id="viewerLoadingSpinner" class="spinner-border text-primary position-absolute" style="display: none; width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading high resolution...</span>
          </div>
        </div>

        <!-- Right Navigation Button -->
        <button type="button" class="btn position-absolute text-white" id="btnNextPhoto" onclick="nextViewerPhoto()" 
                style="right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; width: 55px; height: 55px; border-radius: 50%; background: rgba(0,0,0,0.55); border: 2px solid rgba(255,255,255,0.3); font-size: 1.5rem; transition: all 0.2s;"
                onmouseover="this.style.background='rgba(0,123,255,0.85)'; this.style.borderColor='#fff';"
                onmouseout="this.style.background='rgba(0,0,0,0.55)'; this.style.borderColor='rgba(255,255,255,0.3)';"
                title="Next Photo (Right Arrow or Swipe Left)">
          <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Subtle Swipe Left / Right Hint for touch devices -->
        <div class="position-absolute text-white-50 small d-md-none" style="bottom: 10px; z-index: 5; pointer-events: none;">
          <i class="fas fa-arrows-alt-h mr-1"></i> Swipe left or right to browse
        </div>
      </div>

      <!-- Caption & Metadata Strip -->
      <div class="p-3 bg-dark d-flex flex-wrap justify-content-between align-items-center" style="border-top: 1px solid rgba(255,255,255,0.1);">
        <div>
          <h6 id="viewerPhotoTitle" class="font-weight-bold mb-1 text-white"></h6>
          <p id="viewerPhotoCaption" class="small text-muted mb-0"></p>
        </div>
        <div class="mt-2 mt-md-0">
          <a id="viewerDownloadBtn" href="" target="_blank" class="btn btn-sm btn-outline-info font-weight-bold">
            <i class="fas fa-download mr-1"></i> High-Res Original
          </a>
        </div>
      </div>

      <!-- Bottom Thumbnail Filmstrip -->
      <div id="viewerFilmstrip" class="p-2 bg-black d-flex gap-2" style="overflow-x: auto; white-space: nowrap; border-radius: 0 0 8px 8px; max-height: 90px;"></div>

    </div>
  </div>
</div>

<!-- ============================================================== -->
<!-- JAVASCRIPT LOGIC: UPLOADER, CAROUSEL, MERGING, MOVING          -->
<!-- ============================================================== -->
<script>
var currentEventType = 'Game';
var selectedFilesQueue = [];
var uploadInProgress = false;

// 1. Event Type Mode Switching
function setEventType(type) {
  currentEventType = type;
  var nonGame = document.getElementById('non_game_fields');
  var game = document.getElementById('game_fields');

  if (type === 'Game') {
    nonGame.style.display = 'none';
    game.style.display = 'block';
    document.getElementById('upload_match_id').setAttribute('required', 'required');
  } else {
    nonGame.style.display = 'block';
    game.style.display = 'none';
    document.getElementById('upload_match_id').removeAttribute('required');
  }
}

// 2. Filter match options by selected game
function filterMatchesByGame() {
  var gameId = document.getElementById('upload_game_id').value;
  var matchSelect = document.getElementById('upload_match_id');
  var options = matchSelect.querySelectorAll('option');

  var visibleCount = 0;
  options.forEach(function(opt) {
    if (!opt.value) {
      opt.style.display = '';
      return;
    }
    if (!gameId || opt.getAttribute('data-game') === gameId) {
      opt.style.display = '';
      visibleCount++;
    } else {
      opt.style.display = 'none';
    }
  });

  if (gameId && visibleCount > 0) {
    matchSelect.options[0].text = '-- Choose Match (' + visibleCount + ' Available) --';
  } else if (gameId && visibleCount === 0) {
    matchSelect.options[0].text = '-- No Scheduled Matches Found for Sport --';
  } else {
    matchSelect.options[0].text = '-- Choose Sport First --';
  }
  matchSelect.value = '';
}

// 3. Album mode in uploader
function toggleAlbumMode() {
  var isExisting = document.getElementById('radExistingAlbum').checked;
  document.getElementById('existing_album_wrap').style.display = isExisting ? 'block' : 'none';
}

// 4. Drag and Drop Setup
var dropZone = document.getElementById('dropZone');
var multiFilesInput = document.getElementById('multiFilesInput');

dropZone.addEventListener('click', function() {
  multiFilesInput.click();
});

dropZone.addEventListener('dragover', function(e) {
  e.preventDefault();
  dropZone.style.background = '#e8f4fd';
  dropZone.style.borderColor = '#0056b3';
});

dropZone.addEventListener('dragleave', function() {
  dropZone.style.background = '#f8fafc';
  dropZone.style.borderColor = '#007bff';
});

dropZone.addEventListener('drop', function(e) {
  e.preventDefault();
  dropZone.style.background = '#f8fafc';
  dropZone.style.borderColor = '#007bff';
  if (!uploadInProgress && e.dataTransfer.files.length > 0) {
    handleFilesSelected(e.dataTransfer.files);
  }
});

multiFilesInput.addEventListener('change', function() {
  if (this.files.length > 0) {
    handleFilesSelected(this.files);
  }
});

function handleFilesSelected(filesList) {
  for (var i = 0; i < filesList.length; i++) {
    var f = filesList[i];
    if (f.type.startsWith('image/')) {
      selectedFilesQueue.push({
        file: f,
        id: 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
        status: 'pending',
        progress: 0
      });
    }
  }
  renderFilesQueue();
}

function renderFilesQueue() {
  var container = document.getElementById('fileQueueContainer');
  var list = document.getElementById('filesList');
  var countBadge = document.getElementById('queueCountBadge');
  var uploadBtn = document.getElementById('startUploadBtn');

  if (selectedFilesQueue.length === 0) {
    container.style.display = 'none';
    uploadBtn.disabled = true;
    return;
  }

  container.style.display = 'block';
  countBadge.innerText = selectedFilesQueue.length + ' Files';
  uploadBtn.disabled = false;
  uploadBtn.innerText = 'Upload ' + selectedFilesQueue.length + ' Photos (Parallel)';

  var html = '';
  selectedFilesQueue.forEach(function(item, idx) {
    var sizeMB = (item.file.size / (1024 * 1024)).toFixed(2);
    var statusBadge = '<span class="badge badge-secondary">Ready</span>';
    if (item.status === 'uploading') statusBadge = '<span class="badge badge-primary">Uploading (' + item.progress + '%)</span>';
    else if (item.status === 'success') statusBadge = '<span class="badge badge-success"><i class="fas fa-check mr-1"></i> Original + Compressed</span>';
    else if (item.status === 'error') statusBadge = '<span class="badge badge-danger">Failed</span>';

    html += '<div class="d-flex align-items-center justify-content-between p-2 mb-1 border-bottom" id="' + item.id + '">';
    html += '  <div class="d-flex align-items-center text-truncate mr-2">';
    html += '    <i class="fas fa-file-image text-primary mr-2" style="font-size: 1.25rem;"></i>';
    html += '    <div>';
    html += '      <strong class="small d-block text-truncate" style="max-width: 320px;">' + item.file.name + '</strong>';
    html += '      <span class="text-muted small">' + sizeMB + ' MB</span>';
    html += '    </div>';
    html += '  </div>';
    html += '  <div class="d-flex align-items-center">';
    html += '    <div class="mr-3" style="width: 140px;">' + statusBadge + '</div>';
    if (item.status === 'pending' && !uploadInProgress) {
      html += '    <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeQueueItem(' + idx + ')">&times;</button>';
    }
    html += '  </div>';
    html += '</div>';
  });
  list.innerHTML = html;
}

function removeQueueItem(index) {
  selectedFilesQueue.splice(index, 1);
  renderFilesQueue();
}

function clearFileQueue() {
  if (!uploadInProgress) {
    selectedFilesQueue = [];
    renderFilesQueue();
  }
}

// 5. Parallel Batch Uploader (Creates Album & Uploads Concurrent Photos)
async function startParallelUpload() {
  var day = document.getElementById('upload_day').value;
  var caption = document.getElementById('upload_caption').value;
  var title = '';
  var gameId = '';
  var matchId = '';

  if (currentEventType === 'Game') {
    gameId = document.getElementById('upload_game_id').value;
    matchId = document.getElementById('upload_match_id').value;
    if (!gameId) {
      alert('Please select a Sports Discipline for Game action photos.');
      return;
    }
    if (!matchId) {
      alert('Link to Match is mandatory for Game action photos! Please select the match.');
      return;
    }
  } else {
    title = document.getElementById('upload_title').value.trim();
    if (!title) {
      alert('Please enter an Album / Event Title for this ' + currentEventType + ' upload.');
      return;
    }
  }

  if (selectedFilesQueue.length === 0) {
    alert('Please select or drag at least one photo.');
    return;
  }

  uploadInProgress = true;
  document.getElementById('startUploadBtn').disabled = true;
  document.getElementById('overallProgressWrap').style.display = 'block';

  // Determine Album ID (Create new album or use existing)
  var isExisting = document.getElementById('radExistingAlbum').checked;
  var targetAlbumId = 0;

  if (isExisting) {
    targetAlbumId = parseInt(document.getElementById('upload_existing_album_id').value) || 0;
    if (targetAlbumId <= 0) {
      alert('Please select a destination album.');
      uploadInProgress = false;
      document.getElementById('startUploadBtn').disabled = false;
      return;
    }
  } else {
    // Call create_album to bundle this batch into a new album
    try {
      var albFormData = new FormData();
      albFormData.append('action', 'create_album');
      albFormData.append('event_type', currentEventType);
      albFormData.append('day', day);
      albFormData.append('title', title);
      albFormData.append('game_id', gameId);
      albFormData.append('match_id', matchId);

      var aRes = await fetch('<?= BASE_URL ?>/admin/photos', { method: 'POST', body: albFormData });
      var aData = await aRes.json();
      if (aRes.ok && aData.status === 'success') {
        targetAlbumId = aData.album_id;
      } else {
        alert(aData.message || 'Failed to create album for batch.');
        uploadInProgress = false;
        document.getElementById('startUploadBtn').disabled = false;
        return;
      }
    } catch(err) {
      alert('Error creating album collection: ' + err.message);
      uploadInProgress = false;
      document.getElementById('startUploadBtn').disabled = false;
      return;
    }
  }

  var total = selectedFilesQueue.length;
  var completed = 0;
  var concurrencyLimit = 3;
  var pendingItems = selectedFilesQueue.filter(it => it.status === 'pending');

  async function uploadItem(item) {
    item.status = 'uploading';
    renderFilesQueue();

    var formData = new FormData();
    formData.append('action', 'upload_photo_ajax');
    formData.append('album_id', targetAlbumId);
    formData.append('event_type', currentEventType);
    formData.append('day', day);
    formData.append('caption', caption);
    formData.append('title', title);
    formData.append('game_id', gameId);
    formData.append('match_id', matchId);
    formData.append('photo_file', item.file);

    try {
      var res = await fetch('<?= BASE_URL ?>/admin/photos', {
        method: 'POST',
        body: formData
      });
      var result = await res.json();
      if (res.ok && result.status === 'success') {
        item.status = 'success';
        item.progress = 100;
      } else {
        item.status = 'error';
      }
    } catch (e) {
      item.status = 'error';
    }

    completed++;
    var percent = Math.round((completed / total) * 100);
    document.getElementById('overallProgressBar').style.width = percent + '%';
    document.getElementById('overallPercentText').innerText = percent + '%';
    document.getElementById('overallStatusText').innerText = 'Uploaded ' + completed + ' of ' + total + ' photos...';
    renderFilesQueue();
  }

  // Parallel concurrency worker pool
  var queue = [...pendingItems];
  var workers = Array.from({ length: Math.min(concurrencyLimit, queue.length) }, async function() {
    while (queue.length > 0) {
      var nextItem = queue.shift();
      if (nextItem) {
        await uploadItem(nextItem);
      }
    }
  });
  await Promise.all(workers);

  uploadInProgress = false;
  document.getElementById('overallStatusText').innerHTML = '<i class="fas fa-check-circle text-success mr-1"></i> All ' + total + ' photos uploaded and album ready!';
  
  setTimeout(function() {
    window.location.href = '<?= BASE_URL ?>/admin/photos?view=albums';
  }, 1200);
}

function resetUploadModal() {
  if (!uploadInProgress) {
    selectedFilesQueue = [];
    renderFilesQueue();
    document.getElementById('overallProgressWrap').style.display = 'none';
  }
}

// ==========================================
// 6. MULTI-SELECT ALBUM ACTIONS & MERGING
// ==========================================
function updateAlbumSelectionState() {
  var checked = document.querySelectorAll('.album-checkbox:checked');
  var count = checked.length;
  var toolbar = document.getElementById('albumsActionToolbar');
  var countBadge = document.getElementById('selectedAlbumsCount');
  var mergeBtn = document.getElementById('btnMergeAlbums');

  if (count > 0) {
    toolbar.style.display = 'block';
    countBadge.innerText = count;
    // Merging requires 2 or more albums
    mergeBtn.disabled = (count < 2);
  } else {
    toolbar.style.display = 'none';
  }
}

function toggleSelectAllAlbums() {
  var checkboxes = document.querySelectorAll('.album-checkbox');
  var allChecked = Array.from(checkboxes).every(cb => cb.checked);
  checkboxes.forEach(cb => cb.checked = !allChecked);
  updateAlbumSelectionState();
}

function deselectAllAlbums() {
  document.querySelectorAll('.album-checkbox').forEach(cb => cb.checked = false);
  updateAlbumSelectionState();
}

function openMergeAlbumsModal() {
  var checked = document.querySelectorAll('.album-checkbox:checked');
  if (checked.length < 2) {
    alert('Please select at least 2 albums to merge.');
    return;
  }

  document.getElementById('mergeModalCountText').innerText = checked.length;
  var list = document.getElementById('mergeAlbumsList');
  var targetSelect = document.getElementById('merge_target_album_id');
  list.innerHTML = '';
  targetSelect.innerHTML = '';

  checked.forEach(function(cb, index) {
    var title = cb.getAttribute('data-title');
    var id = cb.value;

    var li = document.createElement('li');
    li.innerText = title + ' (ID: #' + id + ')';
    list.appendChild(li);

    var opt = document.createElement('option');
    opt.value = id;
    opt.text = title + ' (Keep as Primary Album)';
    if (index === 0) opt.selected = true;
    targetSelect.appendChild(opt);
  });

  onMergeTargetChange();
  $('#mergeAlbumsModal').modal('show');
}

function onMergeTargetChange() {
  var targetSelect = document.getElementById('merge_target_album_id');
  var selectedText = targetSelect.options[targetSelect.selectedIndex].text.replace(' (Keep as Primary Album)', '');
  document.getElementById('merge_album_title').value = selectedText;
}

async function submitMergeAlbums() {
  var checked = document.querySelectorAll('.album-checkbox:checked');
  var albumIds = Array.from(checked).map(cb => cb.value);
  var targetId = document.getElementById('merge_target_album_id').value;
  var mergedTitle = document.getElementById('merge_album_title').value.trim();

  var formData = new FormData();
  formData.append('action', 'merge_albums');
  formData.append('album_ids', albumIds.join(','));
  formData.append('target_album_id', targetId);
  formData.append('merged_title', mergedTitle);
  formData.append('format', 'json');

  try {
    var res = await fetch('<?= BASE_URL ?>/admin/photos', { method: 'POST', body: formData });
    var data = await res.json();
    if (res.ok && data.status === 'success') {
      window.location.href = '<?= BASE_URL ?>/admin/photos?view=albums';
    } else {
      alert(data.message || 'Error merging albums');
    }
  } catch(e) {
    alert('Network error: ' + e.message);
  }
}

async function confirmDeleteSelectedAlbums() {
  var checked = document.querySelectorAll('.album-checkbox:checked');
  var count = checked.length;
  if (count === 0) return;

  if (!confirm('Are you sure you want to permanently delete ' + count + ' album(s) and ALL of their photos? This action cannot be undone.')) {
    return;
  }

  var albumIds = Array.from(checked).map(cb => cb.value);
  var formData = new FormData();
  formData.append('action', 'delete_albums');
  formData.append('album_ids', albumIds.join(','));
  formData.append('format', 'json');

  try {
    var res = await fetch('<?= BASE_URL ?>/admin/photos', { method: 'POST', body: formData });
    var data = await res.json();
    if (res.ok && data.status === 'success') {
      window.location.reload();
    } else {
      alert(data.message || 'Error deleting albums');
    }
  } catch(e) {
    alert('Error: ' + e.message);
  }
}

function confirmDeleteSingleAlbum(id, title) {
  if (confirm('Delete album "' + title + '" and all its photos?')) {
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = '<?= BASE_URL ?>/admin/photos';
    f.innerHTML = '<input type="hidden" name="action" value="delete_albums"><input type="hidden" name="album_ids" value="' + id + '">';
    document.body.appendChild(f);
    f.submit();
  }
}

// ==========================================
// 7. MULTI-SELECT PHOTO ACTIONS & MOVING
// ==========================================
function updatePhotoSelectionState() {
  var checked = document.querySelectorAll('.photo-checkbox:checked');
  var count = checked.length;
  var toolbar = document.getElementById('photosActionToolbar');
  var countBadge = document.getElementById('selectedPhotosCount');

  if (count > 0) {
    toolbar.style.display = 'block';
    countBadge.innerText = count;
  } else {
    toolbar.style.display = 'none';
  }
}

function toggleSelectAllPhotos() {
  var checkboxes = document.querySelectorAll('.photo-checkbox');
  var allChecked = Array.from(checkboxes).every(cb => cb.checked);
  checkboxes.forEach(cb => cb.checked = !allChecked);
  updatePhotoSelectionState();
}

function deselectAllPhotos() {
  document.querySelectorAll('.photo-checkbox').forEach(cb => cb.checked = false);
  updatePhotoSelectionState();
}

function openMovePhotosModal() {
  var checked = document.querySelectorAll('.photo-checkbox:checked');
  if (checked.length === 0) return;
  document.getElementById('moveModalCountText').innerText = checked.length;
  document.getElementById('move_target_album_id').value = '';
  document.getElementById('move_new_album_field').style.display = 'none';
  document.getElementById('move_new_album_title').value = '';
  $('#movePhotosModal').modal('show');
}

function toggleMoveNewAlbumInput() {
  var val = document.getElementById('move_target_album_id').value;
  document.getElementById('move_new_album_field').style.display = (val === 'new_album') ? 'block' : 'none';
}

async function submitMovePhotos() {
  var checked = document.querySelectorAll('.photo-checkbox:checked');
  var photoIds = Array.from(checked).map(cb => cb.value);
  var targetVal = document.getElementById('move_target_album_id').value;
  var newTitle = document.getElementById('move_new_album_title').value.trim();

  if (!targetVal) {
    alert('Please choose an album destination.');
    return;
  }
  if (targetVal === 'new_album' && !newTitle) {
    alert('Please enter a title for the new album.');
    return;
  }

  var formData = new FormData();
  formData.append('action', 'move_photos');
  formData.append('photo_ids', photoIds.join(','));
  if (targetVal === 'new_album') {
    formData.append('new_album_title', newTitle);
  } else {
    formData.append('target_album_id', targetVal);
  }
  formData.append('format', 'json');

  try {
    var res = await fetch('<?= BASE_URL ?>/admin/photos', { method: 'POST', body: formData });
    var data = await res.json();
    if (res.ok && data.status === 'success') {
      window.location.reload();
    } else {
      alert(data.message || 'Error moving photos');
    }
  } catch(e) {
    alert('Error: ' + e.message);
  }
}

async function confirmDeleteSelectedPhotos() {
  var checked = document.querySelectorAll('.photo-checkbox:checked');
  var count = checked.length;
  if (count === 0) return;

  if (!confirm('Are you sure you want to permanently delete ' + count + ' selected photo(s)?')) {
    return;
  }

  var photoIds = Array.from(checked).map(cb => cb.value);
  var formData = new FormData();
  formData.append('action', 'delete_photos');
  formData.append('photo_ids', photoIds.join(','));
  formData.append('format', 'json');

  try {
    var res = await fetch('<?= BASE_URL ?>/admin/photos', { method: 'POST', body: formData });
    var data = await res.json();
    if (res.ok && data.status === 'success') {
      window.location.reload();
    } else {
      alert(data.message || 'Error deleting photos');
    }
  } catch(e) {
    alert('Error: ' + e.message);
  }
}

function confirmDeleteSinglePhoto(id) {
  if (confirm('Delete this photo permanently?')) {
    var f = document.createElement('form');
    f.method = 'POST';
    f.action = '<?= BASE_URL ?>/admin/photos';
    f.innerHTML = '<input type="hidden" name="action" value="delete_photo"><input type="hidden" name="id" value="' + id + '">';
    document.body.appendChild(f);
    f.submit();
  }
}

// ==============================================================
// 8. INTERACTIVE ALBUM VIEWER (SWIPE, BUTTONS, PROGRESSIVE LOAD)
// ==============================================================
var currentAlbumPhotos = [];
var currentPhotoIndex = 0;
var progressiveSwapTimer = null;

async function openAlbumCarousel(albumId, initialPhotoId) {
  if (!albumId) return;

  try {
    var res = await fetch('<?= BASE_URL ?>/api/albums?id=' + albumId);
    var data = await res.json();
    if (!res.ok || data.status !== 'success' || !data.photos || data.photos.length === 0) {
      alert('This album has no photos yet.');
      return;
    }

    currentAlbumPhotos = data.photos;
    var album = data.album;

    document.getElementById('viewerAlbumTitle').innerText = album.title;
    document.getElementById('viewerBadge').innerText = album.event_type;
    document.getElementById('viewerDay').innerText = 'Day ' + album.day;

    // Badge styling
    var badge = document.getElementById('viewerBadge');
    badge.className = 'badge text-uppercase mr-2 ';
    if (album.event_type === 'Game') badge.className += 'badge-primary';
    else if (album.event_type === 'Ceremony') badge.className += 'badge-success';
    else if (album.event_type === 'Meetings') badge.className += 'badge-warning text-dark';
    else badge.className += 'badge-info';

    // Find starting photo index
    currentPhotoIndex = 0;
    if (initialPhotoId) {
      var foundIdx = currentAlbumPhotos.findIndex(p => parseInt(p.id) === parseInt(initialPhotoId));
      if (foundIdx >= 0) currentPhotoIndex = foundIdx;
    }

    renderViewerFilmstrip();
    showViewerPhoto(currentPhotoIndex);

    $('#albumViewerModal').modal('show');
  } catch(e) {
    alert('Error loading album: ' + e.message);
  }
}

function showViewerPhoto(index) {
  if (!currentAlbumPhotos || currentAlbumPhotos.length === 0) return;
  if (index < 0) index = currentAlbumPhotos.length - 1;
  if (index >= currentAlbumPhotos.length) index = 0;
  currentPhotoIndex = index;

  var ph = currentAlbumPhotos[index];
  var mainImg = document.getElementById('viewerMainImg');
  var spinner = document.getElementById('viewerLoadingSpinner');

  // Cancel any previous timer
  if (progressiveSwapTimer) {
    clearTimeout(progressiveSwapTimer);
  }

  // Dual-version progressive loading:
  // Step 1: Immediately render the compressed thumbnail
  var compressedSrc = '<?= BASE_URL ?>/' + (ph.compressed_path || ph.file_path);
  var originalSrc = '<?= BASE_URL ?>/' + ph.file_path;

  mainImg.src = compressedSrc;
  mainImg.style.filter = 'blur(1px)';
  spinner.style.display = 'none';

  // Step 2: Exactly after 1 second (1000ms), fetch original high-res and swap smoothly
  progressiveSwapTimer = setTimeout(function() {
    var highRes = new Image();
    highRes.src = originalSrc;
    highRes.onload = function() {
      // Only swap if user hasn't switched to another photo
      if (currentPhotoIndex === index) {
        mainImg.src = originalSrc;
        mainImg.style.filter = 'none';
      }
    };
  }, 1000);

  // Update text & counter
  document.getElementById('viewerCounter').innerText = 'Photo ' + (index + 1) + ' of ' + currentAlbumPhotos.length;
  document.getElementById('viewerPhotoTitle').innerText = ph.title || ph.file_name;
  document.getElementById('viewerPhotoCaption').innerText = ph.caption || 'Captured during HPCL 20th All India Tournament';
  document.getElementById('viewerDownloadBtn').href = originalSrc;

  // Highlight active thumbnail in filmstrip
  document.querySelectorAll('.filmstrip-thumb').forEach(function(th, idx) {
    if (idx === index) {
      th.style.borderColor = '#007bff';
      th.style.opacity = '1';
      th.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    } else {
      th.style.borderColor = 'transparent';
      th.style.opacity = '0.55';
    }
  });
}

function nextViewerPhoto() {
  showViewerPhoto(currentPhotoIndex + 1);
}

function prevViewerPhoto() {
  showViewerPhoto(currentPhotoIndex - 1);
}

function renderViewerFilmstrip() {
  var strip = document.getElementById('viewerFilmstrip');
  strip.innerHTML = '';

  currentAlbumPhotos.forEach(function(ph, idx) {
    var thumb = document.createElement('img');
    thumb.src = '<?= BASE_URL ?>/' + (ph.compressed_path || ph.file_path);
    thumb.className = 'filmstrip-thumb rounded';
    thumb.style.width = '64px';
    thumb.style.height = '48px';
    thumb.style.objectFit = 'cover';
    thumb.style.cursor = 'pointer';
    thumb.style.border = '2px solid transparent';
    thumb.style.opacity = '0.55';
    thumb.style.transition = 'all 0.2s';
    thumb.onclick = function() { showViewerPhoto(idx); };
    strip.appendChild(thumb);
  });
}

// Keyboard arrow listeners (Left / Right / Escape)
document.addEventListener('keydown', function(e) {
  var modal = document.getElementById('albumViewerModal');
  if (modal && $(modal).hasClass('show')) {
    if (e.key === 'ArrowLeft') {
      prevViewerPhoto();
    } else if (e.key === 'ArrowRight') {
      nextViewerPhoto();
    }
  }
});

// Touch / Swipe Gesture Support (Swipe left = next, Swipe right = prev)
var touchStartX = 0;
var touchEndX = 0;
var stageElem = document.getElementById('viewerStage');

stageElem.addEventListener('touchstart', function(e) {
  touchStartX = e.changedTouches[0].screenX;
}, { passive: true });

stageElem.addEventListener('touchend', function(e) {
  touchEndX = e.changedTouches[0].screenX;
  handleGesture();
}, { passive: true });

function handleGesture() {
  var delta = touchEndX - touchStartX;
  if (Math.abs(delta) > 50) { // 50px threshold
    if (delta < 0) {
      // Swiped Left -> Next Photo
      nextViewerPhoto();
    } else {
      // Swiped Right -> Previous Photo
      prevViewerPhoto();
    }
  }
}
</script>
