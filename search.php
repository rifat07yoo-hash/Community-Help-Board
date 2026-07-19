<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$q = trim($_GET['q'] ?? '');
if ($q === '' || strlen($q) < 2) {
    jsonResponse(['users' => [], 'requests' => []]);
}
$like = '%' . $q . '%';

$stmt = $pdo->prepare("
    SELECT id, name, location, blood_group, profile_image
    FROM users
    WHERE is_banned = 0 AND (name LIKE ? OR location LIKE ?)
    LIMIT 5
");
$stmt->execute([$like, $like]);
$users = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT r.id, r.category, r.priority, r.location, r.description, u.name AS user_name
    FROM help_requests r JOIN users u ON u.id = r.user_id
    WHERE r.location LIKE ? OR r.description LIKE ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$stmt->execute([$like, $like]);
$requests = $stmt->fetchAll();

jsonResponse(['users' => $users, 'requests' => $requests]);
