<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user = requireAuth();

if ($method === 'GET') {
    $stmt = $pdo->query('SELECT id, text, is_read, created_at FROM notifications ORDER BY created_at DESC LIMIT 15');
    jsonResponse(['notifications' => $stmt->fetchAll()]);
}

if ($method === 'PUT') {
    // mark all as read
    $pdo->exec('UPDATE notifications SET is_read = 1 WHERE is_read = 0');
    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    $pdo->exec('DELETE FROM notifications');
    jsonResponse(['success' => true]);
}

jsonError('Method not allowed', 405);
