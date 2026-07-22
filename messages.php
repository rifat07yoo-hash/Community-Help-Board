<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user = requireAuth();

// ---------------------------------------------------------------------
// GET ?request_id=X       -> full thread for that request
// GET ?inbox=1            -> list of threads involving the current user
// ---------------------------------------------------------------------
if ($method === 'GET') {

    if (!empty($_GET['inbox'])) {
        $stmt = $pdo->prepare("
            SELECT r.id AS request_id, r.category, r.location, u.name AS owner_name,
                   (SELECT text FROM messages m2 WHERE m2.request_id = r.id
                        AND (m2.sender_id = ? OR m2.recipient_id = ?)
                        ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
                   (SELECT COUNT(*) FROM messages m3 WHERE m3.request_id = r.id
                        AND m3.recipient_id = ? AND m3.is_read = 0) AS unread_count
            FROM help_requests r
            JOIN users u ON u.id = r.user_id
            WHERE EXISTS (
                SELECT 1 FROM messages m WHERE m.request_id = r.id
                AND (m.sender_id = ? OR m.recipient_id = ?)
            )
            ORDER BY r.updated_at DESC
        ");
        $stmt->execute([$user['id'], $user['id'], $user['id'], $user['id'], $user['id']]);
        jsonResponse(['threads' => $stmt->fetchAll()]);
    }

    $requestId = (int)($_GET['request_id'] ?? 0);
    if (!$requestId) jsonError('Missing request_id.');

    $stmt = $pdo->prepare("SELECT id, user_id AS owner_id, location, category, contact FROM help_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request) jsonError('Request not found.', 404);

    $stmt = $pdo->prepare("
        SELECT m.id, m.text, m.is_read, m.created_at,
               s.id AS sender_id, s.name AS sender_name,
               rc.id AS recipient_id, rc.name AS recipient_name
        FROM messages m
        JOIN users s ON s.id = m.sender_id
        JOIN users rc ON rc.id = m.recipient_id
        WHERE m.request_id = ? AND (m.sender_id = ? OR m.recipient_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$requestId, $user['id'], $user['id']]);
    $messages = $stmt->fetchAll();

    // mark incoming messages as read
    $stmt = $pdo->prepare('UPDATE messages SET is_read = 1 WHERE request_id = ? AND recipient_id = ? AND is_read = 0');
    $stmt->execute([$requestId, $user['id']]);

    jsonResponse(['request' => $request, 'messages' => $messages]);
}

// ---------------------------------------------------------------------
// POST -> send a message. recipient_id is required unless the thread
// already has messages, in which case we infer "the other party".
// ---------------------------------------------------------------------
if ($method === 'POST') {
    $data = readJsonBody();
    $requestId = (int)($data['request_id'] ?? 0);
    $text = trim($data['text'] ?? '');
    $recipientId = (int)($data['recipient_id'] ?? 0);

    if (!$requestId || $text === '') jsonError('request_id and text are required.');

    $stmt = $pdo->prepare('SELECT user_id, location FROM help_requests WHERE id = ?');
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    if (!$request) jsonError('Request not found.', 404);

    if (!$recipientId) {
        if ((int)$request['user_id'] === (int)$user['id']) {
            // owner replying: find the other participant from existing thread
            $stmt = $pdo->prepare("SELECT sender_id FROM messages WHERE request_id = ? AND sender_id != ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$requestId, $user['id']]);
            $other = $stmt->fetchColumn();
            if (!$other) jsonError('No recipient specified and no existing conversation to reply to.');
            $recipientId = (int)$other;
        } else {
            $recipientId = (int)$request['user_id'];
        }
    }

    if ($recipientId === (int)$user['id']) jsonError('You cannot message yourself.');

    $stmt = $pdo->prepare('INSERT INTO messages (request_id, sender_id, recipient_id, text) VALUES (?,?,?,?)');
    $stmt->execute([$requestId, $user['id'], $recipientId, $text]);

    logActivity($user['id'], 'message', "Sent a coordination message about a request in {$request['location']} 📨", $requestId);

    jsonResponse(['success' => true, 'id' => (int)$pdo->lastInsertId()], 201);
}

// ---------------------------------------------------------------------
// DELETE ?id=X -> sender can delete their own message
// ---------------------------------------------------------------------
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonError('Missing id.');

    $stmt = $pdo->prepare('SELECT sender_id FROM messages WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) jsonError('Message not found.', 404);
    if ((int)$existing['sender_id'] !== (int)$user['id']) {
        jsonError('You can only delete your own messages.', 403);
    }

    $stmt = $pdo->prepare('DELETE FROM messages WHERE id = ?');
    $stmt->execute([$id]);

    jsonResponse(['success' => true]);
}

jsonError('Method not allowed', 405);
