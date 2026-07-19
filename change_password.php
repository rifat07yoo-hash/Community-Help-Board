<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = readJsonBody();
$currentPassword = (string)($data['current_password'] ?? '');
$newPassword = (string)($data['new_password'] ?? '');
$confirmPassword = (string)($data['confirm_password'] ?? '');

if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
    jsonError('All password fields are required.');
}
if ($newPassword !== $confirmPassword) {
    jsonError('New password and confirmation do not match.');
}
if (strlen($newPassword) < 6) {
    jsonError('New password must be at least 6 characters.');
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$row = $stmt->fetch();

if (!$row || !password_verify($currentPassword, $row['password'])) {
    jsonError('Current password is incorrect.', 401);
}

$newHash = password_hash($newPassword, PASSWORD_BCRYPT);
$stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
$stmt->execute([$newHash, $user['id']]);

jsonResponse(['success' => true, 'message' => 'Password updated successfully.']);
