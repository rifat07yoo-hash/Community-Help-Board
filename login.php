<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = readJsonBody();
$email = strtolower(trim($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');

if ($email === '' || $password === '') {
    jsonError('Email and password are required.');
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    jsonError('Invalid email or password.', 401);
}

if ((int)$user['is_banned'] === 1) {
    jsonError('Your account has been suspended by an administrator.', 403);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];

unset($user['password']);
jsonResponse(['success' => true, 'user' => $user]);
