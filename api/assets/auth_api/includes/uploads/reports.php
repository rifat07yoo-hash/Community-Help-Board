<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$user = requireAuth();

if ($method !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = readJsonBody();
$requestId = (int)($data['request_id'] ?? 0);
if (!$requestId) jsonError('Missing request_id.');

$stmt = $pdo->prepare('SELECT id FROM help_requests WHERE id = ?');
$stmt->execute([$requestId]);
if (!$stmt->fetch()) jsonError('Request not found.', 404);

$stmt = $pdo->prepare('SELECT id FROM reports WHERE request_id = ? AND reported_by_user_id = ?');
$stmt->execute([$requestId, $user['id']]);
if ($stmt->fetch()) {
    jsonError('You have already reported this post.');
}

$stmt = $pdo->prepare('INSERT INTO reports (request_id, reported_by_user_id) VALUES (?,?)');
$stmt->execute([$requestId, $user['id']]);

jsonResponse(['success' => true]);
