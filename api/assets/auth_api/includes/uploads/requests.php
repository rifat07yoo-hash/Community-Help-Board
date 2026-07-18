<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

const REQUEST_SELECT = "
    r.id, r.category, r.priority, r.location, r.contact, r.description,
    r.target_qty, r.collected_qty, r.image, r.resolved, r.created_at, r.updated_at,
    u.id AS user_id, u.name AS user_name, u.email AS user_email,
    u.profile_image AS user_avatar, u.is_volunteer AS user_is_volunteer
";

function attachExtras($pdo, &$row, $currentUserId) {
    // comment count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM comments WHERE request_id = ?');
    $stmt->execute([$row['id']]);
    $row['comment_count'] = (int)$stmt->fetchColumn();

    // report count (only meaningful info, not who reported)
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM reports WHERE request_id = ?');
    $stmt->execute([$row['id']]);
    $row['report_count'] = (int)$stmt->fetchColumn();

    // whether current user already reported
    if ($currentUserId) {
        $stmt = $pdo->prepare('SELECT 1 FROM reports WHERE request_id = ? AND reported_by_user_id = ?');
        $stmt->execute([$row['id'], $currentUserId]);
        $row['reported_by_me'] = (bool)$stmt->fetchColumn();
    } else {
        $row['reported_by_me'] = false;
    }

    // message thread summary for this user
    if ($currentUserId) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE request_id = ? AND (sender_id = ? OR recipient_id = ?)');
        $stmt->execute([$row['id'], $currentUserId, $currentUserId]);
        $row['message_count'] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE request_id = ? AND recipient_id = ? AND is_read = 0');
        $stmt->execute([$row['id'], $currentUserId]);
        $row['unread_count'] = (int)$stmt->fetchColumn();
    } else {
        $row['message_count'] = 0;
        $row['unread_count'] = 0;
    }
}

// ---------------------------------------------------------------------
// GET — list requests (with filters) or a single request by id
// ---------------------------------------------------------------------
if ($method === 'GET') {
    $me = currentUser();
    $meId = $me ? (int)$me['id'] : null;

    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT " . REQUEST_SELECT . " FROM help_requests r JOIN users u ON u.id = r.user_id WHERE r.id = ?");
        $stmt->execute([(int)$_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) jsonError('Request not found.', 404);
        attachExtras($pdo, $row, $meId);
        jsonResponse(['request' => $row]);
    }

    $sql = "SELECT " . REQUEST_SELECT . " FROM help_requests r JOIN users u ON u.id = r.user_id WHERE 1=1";
    $params = [];

    if (!empty($_GET['category'])) {
        $sql .= " AND r.category = ?";
        $params[] = $_GET['category'];
    }
    if (!empty($_GET['priority'])) {
        $sql .= " AND r.priority = ?";
        $params[] = $_GET['priority'];
    }
    if (!empty($_GET['search'])) {
        $sql .= " AND (r.location LIKE ? OR r.description LIKE ?)";
        $like = '%' . $_GET['search'] . '%';
        $params[] = $like;
        $params[] = $like;
    }
    if (!empty($_GET['hide_resolved'])) {
        $sql .= " AND r.resolved = 0";
    }
    if (!empty($_GET['volunteer_only'])) {
        $sql .= " AND u.is_volunteer = 1";
    }
    if (!empty($_GET['mine']) && $meId) {
        $sql .= " AND r.user_id = ?";
        $params[] = $meId;
    }
    // Auto-hide posts with 3+ reports from the public feed (unless it's the admin viewing, or it's the owner's own post)
    if (empty($_GET['include_hidden'])) {
        $sql .= " AND (SELECT COUNT(*) FROM reports WHERE reports.request_id = r.id) < 3";
    }

    $sql .= " ORDER BY r.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        attachExtras($pdo, $row, $meId);
    }

    jsonResponse(['requests' => $rows, 'count' => count($rows)]);
}

// ---------------------------------------------------------------------
// POST — create / update / delete / resolve (all via POST so file
// uploads work; action is distinguished by the "action" field)
// ---------------------------------------------------------------------
if ($method === 'POST') {
    $user = requireAuth();
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $category = $_POST['category'] ?? '';
        $priority = $_POST['priority'] ?? 'medium';
        $location = trim($_POST['location'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $targetQty = max(1, (int)($_POST['target_qty'] ?? 1));
        $collectedQty = max(0, (int)($_POST['collected_qty'] ?? 0));

        $validCategories = ['food','blood','medical','shelter','other'];
        $validPriorities = ['urgent','high','medium'];
        if (!in_array($category, $validCategories, true)) jsonError('Invalid category.');
        if (!in_array($priority, $validPriorities, true)) jsonError('Invalid priority.');
        if ($location === '' || $contact === '' || $description === '') {
            jsonError('Location, contact, and description are required.');
        }

        $imagePath = handleImageUpload('image');

        $stmt = $pdo->prepare("INSERT INTO help_requests
            (user_id, category, priority, location, contact, description, target_qty, collected_qty, image)
            VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$user['id'], $category, $priority, $location, $contact, $description, $targetQty, $collectedQty, $imagePath]);

        $newId = $pdo->lastInsertId();
        addNotification("New {$priority} emergency request posted in {$location}!");

        jsonResponse(['success' => true, 'id' => (int)$newId], 201);
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Missing request id.');

        $stmt = $pdo->prepare('SELECT * FROM help_requests WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) jsonError('Request not found.', 404);
        if ((int)$existing['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
            jsonError('You can only edit your own posts.', 403);
        }

        $category = $_POST['category'] ?? $existing['category'];
        $priority = $_POST['priority'] ?? $existing['priority'];
        $location = trim($_POST['location'] ?? $existing['location']);
        $contact = trim($_POST['contact'] ?? $existing['contact']);
        $description = trim($_POST['description'] ?? $existing['description']);
        $targetQty = max(1, (int)($_POST['target_qty'] ?? $existing['target_qty']));
        $collectedQty = max(0, (int)($_POST['collected_qty'] ?? $existing['collected_qty']));

        $imagePath = handleImageUpload('image');
        if ($imagePath === null) $imagePath = $existing['image'];

        $stmt = $pdo->prepare("UPDATE help_requests SET
            category=?, priority=?, location=?, contact=?, description=?,
            target_qty=?, collected_qty=?, image=? WHERE id = ?");
        $stmt->execute([$category, $priority, $location, $contact, $description, $targetQty, $collectedQty, $imagePath, $id]);

        addNotification("Update issued for the request in {$location}.");

        jsonResponse(['success' => true]);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Missing request id.');

        $stmt = $pdo->prepare('SELECT user_id, image FROM help_requests WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) jsonError('Request not found.', 404);
        if ((int)$existing['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
            jsonError('You can only delete your own posts.', 403);
        }

        $stmt = $pdo->prepare('DELETE FROM help_requests WHERE id = ?');
        $stmt->execute([$id]);

        if ($existing['image']) {
            $path = __DIR__ . '/../' . $existing['image'];
            if (is_file($path)) @unlink($path);
        }

        jsonResponse(['success' => true]);
    }

    if ($action === 'resolve') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) jsonError('Missing request id.');

        $stmt = $pdo->prepare('SELECT user_id, resolved, location FROM help_requests WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) jsonError('Request not found.', 404);
        if ((int)$existing['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
            jsonError('You can only update your own posts.', 403);
        }

        $newState = $existing['resolved'] ? 0 : 1;
        $stmt = $pdo->prepare('UPDATE help_requests SET resolved = ? WHERE id = ?');
        $stmt->execute([$newState, $id]);

        addNotification("Case status in {$existing['location']} marked as " . ($newState ? 'resolved' : 'reopened') . " by {$user['name']}.");

        jsonResponse(['success' => true, 'resolved' => (bool)$newState]);
    }

    jsonError('Unknown action.');
}

jsonError('Method not allowed', 405);
