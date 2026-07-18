<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// GET ?id=X -> public profile (no auth required to view)
if ($method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonError('Missing id.');

    $stmt = $pdo->prepare('SELECT ' . PUBLIC_USER_COLUMNS . ' FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $profile = $stmt->fetch();
    if (!$profile) jsonError('User not found.', 404);

    $stmt = $pdo->prepare('SELECT id, category, priority, location, description, created_at FROM help_requests WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$id]);
    $posts = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT score FROM ratings WHERE target_user_id = ?');
    $stmt->execute([$id]);
    $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $avg = count($scores) ? round(array_sum($scores) / count($scores), 1) : 0;

    jsonResponse(['profile' => $profile, 'posts' => $posts, 'rating_average' => $avg, 'rating_count' => count($scores)]);
}

// POST (multipart form) -> update own profile
if ($method === 'POST') {
    $user = requireAuth();

    $name = trim($_POST['name'] ?? $user['name']);
    $phone = trim($_POST['phone'] ?? $user['phone']);
    $location = trim($_POST['location'] ?? $user['location']);
    $blood = $_POST['blood_group'] ?? $user['blood_group'];

    $validBlood = ['A+','A-','B+','B-','O+','O-','AB+','AB-','Unknown'];
    if (!in_array($blood, $validBlood, true)) $blood = $user['blood_group'];

    if ($name === '') jsonError('Name cannot be empty.');
    if (!preg_match('/^[0-9]{11}$/', $phone)) jsonError('Phone number must be 11 digits.');

    $imagePath = handleImageUpload('profile_image');
    if ($imagePath === null) $imagePath = $user['profile_image'];

    $stmt = $pdo->prepare('UPDATE users SET name=?, phone=?, location=?, blood_group=?, profile_image=? WHERE id=?');
    $stmt->execute([$name, $phone, $location, $blood, $imagePath, $user['id']]);

    jsonResponse(['success' => true]);
}

jsonError('Method not allowed', 405);
