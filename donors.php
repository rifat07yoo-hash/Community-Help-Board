<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$stmt = $pdo->query("
    SELECT u.id, u.name, u.location, u.blood_group, u.phone, u.profile_image,
           (SELECT ROUND(AVG(score),1) FROM ratings WHERE ratings.target_user_id = u.id) AS rating_average,
           (SELECT COUNT(*) FROM ratings WHERE ratings.target_user_id = u.id) AS rating_count
    FROM users u
    WHERE u.blood_group != 'Unknown' AND u.is_banned = 0
    ORDER BY u.name ASC
");

jsonResponse(['donors' => $stmt->fetchAll()]);
