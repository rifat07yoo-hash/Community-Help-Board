<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $requestId = (int)($_GET['request_id'] ?? 0);
    if (!$requestId) jsonError('Missing request_id.');

    $stmt = $pdo->prepare("SELECT c.id, c.text, c.created_at, u.id AS user_id, u.name AS author, u.profile_image AS author_avatar
        FROM comments c JOIN users u ON u.id = c.user_id
        WHERE c.request_id = ? ORDER BY c.created_at ASC");
    $stmt->execute([$requestId]);
    jsonResponse(['comments' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    $user = requireAuth();
    $data = readJsonBody();
    $requestId = (int)($data['request_id'] ?? 0);
    $text = trim($data['text'] ?? '');

    if (!$requestId || $text === '') jsonError('request_id and text are required.');

    $stmt = $pdo->prepare('SELECT id, location FROM help_requests WHERE id = ?');
    $stmt->execute([$requestId]);
    $reqRow = $stmt->fetch();
    if (!$reqRow) jsonError('Request not found.', 404);

    $stmt = $pdo->prepare('INSERT INTO comments (request_id, user_id, text) VALUES (?,?,?)');
    $stmt->execute([$requestId, $user['id'], $text]);

    logActivity($user['id'], 'comment', "Commented on a help request in {$reqRow['location']} 💬", $requestId);

    jsonResponse(['success' => true, 'id' => (int)$pdo->lastInsertId()], 201);
}

if ($method === 'PUT') {
    $user = requireAuth();
    $data = readJsonBody();
    $id = (int)($data['id'] ?? 0);
    $text = trim($data['text'] ?? '');
    if (!$id || $text === '') jsonError('id and text are required.');

    $stmt = $pdo->prepare('SELECT user_id FROM comments WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) jsonError('Comment not found.', 404);
    if ((int)$existing['user_id'] !== (int)$user['id']) jsonError('You can only edit your own comments.', 403);

    $stmt = $pdo->prepare('UPDATE comments SET text = ? WHERE id = ?');
    $stmt->execute([$text, $id]);

    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    $user = requireAuth();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonError('Missing id.');

    $stmt = $pdo->prepare('SELECT user_id FROM comments WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) jsonError('Comment not found.', 404);
    if ((int)$existing['user_id'] !== (int)$user['id'] && !$user['is_admin']) {
        jsonError('You can only delete your own comments.', 403);
    }

    $stmt = $pdo->prepare('DELETE FROM comments WHERE id = ?');
    $stmt->execute([$id]);

    jsonResponse(['success' => true]);
}

jsonError('Method not allowed', 405);
