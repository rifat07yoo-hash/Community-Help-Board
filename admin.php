<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$admin = requireAdmin();

if ($method === 'GET') {
    $stmt = $pdo->query("
        SELECT r.id, r.category, r.description, r.location, u.name AS user_name,
               (SELECT COUNT(*) FROM reports WHERE reports.request_id = r.id) AS report_count
        FROM help_requests r JOIN users u ON u.id = r.user_id
        ORDER BY report_count DESC, r.created_at DESC
    ");
    $posts = $stmt->fetchAll();

    $stmt = $pdo->query("SELECT id, name, email, phone, is_admin, is_volunteer, is_banned FROM users ORDER BY name ASC");
    $users = $stmt->fetchAll();

    $stats = [
        'total_users'      => count($users),
        'banned_users'     => count(array_filter($users, fn($u) => (int)$u['is_banned'] === 1)),
        'volunteers'       => count(array_filter($users, fn($u) => (int)$u['is_volunteer'] === 1)),
        'total_posts'      => count($posts),
        'flagged_posts'    => count(array_filter($posts, fn($p) => (int)$p['report_count'] > 0)),
        'resolved_posts'   => (int)$pdo->query('SELECT COUNT(*) FROM help_requests WHERE resolved = 1')->fetchColumn(),
    ];

    jsonResponse(['posts' => $posts, 'users' => $users, 'stats' => $stats]);
}

if ($method === 'POST') {
    $data = readJsonBody();
    $action = $data['action'] ?? '';

    if ($action === 'ban_user' || $action === 'unban_user') {
        $userId = (int)($data['user_id'] ?? 0);
        if (!$userId) jsonError('Missing user_id.');

        $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $target = $stmt->fetch();
        if (!$target) jsonError('User not found.', 404);
        if ($target['is_admin']) jsonError('Cannot ban an admin account.');

        $newState = $action === 'ban_user' ? 1 : 0;
        $stmt = $pdo->prepare('UPDATE users SET is_banned = ? WHERE id = ?');
        $stmt->execute([$newState, $userId]);

        jsonResponse(['success' => true]);
    }

    if ($action === 'toggle_volunteer') {
        $userId = (int)($data['user_id'] ?? 0);
        if (!$userId) jsonError('Missing user_id.');

        $stmt = $pdo->prepare('SELECT is_volunteer FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $target = $stmt->fetch();
        if (!$target) jsonError('User not found.', 404);

        $newState = $target['is_volunteer'] ? 0 : 1;
        $stmt = $pdo->prepare('UPDATE users SET is_volunteer = ? WHERE id = ?');
        $stmt->execute([$newState, $userId]);

        jsonResponse(['success' => true]);
    }

    if ($action === 'delete_user') {
        $userId = (int)($data['user_id'] ?? 0);
        if (!$userId) jsonError('Missing user_id.');

        $stmt = $pdo->prepare('SELECT is_admin FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $target = $stmt->fetch();
        if (!$target) jsonError('User not found.', 404);
        if ($target['is_admin']) jsonError('Cannot delete an admin account.');

        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$userId]);

        jsonResponse(['success' => true]);
    }

    if ($action === 'delete_post') {
        $requestId = (int)($data['request_id'] ?? 0);
        if (!$requestId) jsonError('Missing request_id.');

        $stmt = $pdo->prepare('DELETE FROM help_requests WHERE id = ?');
        $stmt->execute([$requestId]);

        jsonResponse(['success' => true]);
    }

    if ($action === 'clear_reports') {
        $requestId = (int)($data['request_id'] ?? 0);
        if (!$requestId) jsonError('Missing request_id.');

        $stmt = $pdo->prepare('DELETE FROM reports WHERE request_id = ?');
        $stmt->execute([$requestId]);

        jsonResponse(['success' => true]);
    }

    jsonError('Unknown action.');
}

jsonError('Method not allowed', 405);
