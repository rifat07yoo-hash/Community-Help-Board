<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/activity.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$data = readJsonBody();

$name     = trim($data['name'] ?? '');
$email    = strtolower(trim($data['email'] ?? ''));
$password = (string)($data['password'] ?? '');
$phone    = trim($data['phone'] ?? '');
$location = trim($data['location'] ?? '');
$blood    = $data['blood_group'] ?? 'Unknown';
$isVolunteer = !empty($data['is_volunteer']) ? 1 : 0;

$validBlood = ['A+','A-','B+','B-','O+','O-','AB+','AB-','Unknown'];
if (!in_array($blood, $validBlood, true)) $blood = 'Unknown';

if ($name === '' || $email === '' || $password === '' || $phone === '' || $location === '') {
    jsonError('Name, email, password, phone, and location are all required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('Please provide a valid email address.');
}
if (strlen($password) < 6) {
    jsonError('Password must be at least 6 characters.');
}
if (!preg_match('/^[0-9]{11}$/', $phone)) {
    jsonError('Phone number must be 11 digits.');
}

$pdo = getDB();

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonError('An account with this email already exists.');
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO users (name, email, password, phone, location, blood_group, is_volunteer) VALUES (?,?,?,?,?,?,?)');
$stmt->execute([$name, $email, $hash, $phone, $location, $blood, $isVolunteer]);

$newUserId = (int)$pdo->lastInsertId();
logActivity($newUserId, 'register', 'Joined the Community Help Board 🎉');

jsonResponse(['success' => true, 'message' => 'Registration successful. Please sign in.']);
