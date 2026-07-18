<?php
// Run this once from the command line to promote an existing registered
// user to admin, e.g.:
//   php create_admin.php someone@example.com
// (Register the account through the normal signup form first.)

require_once __DIR__ . '/includes/db.php';

if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

$email = strtolower(trim($argv[1] ?? ''));
if ($email === '') {
    die("Usage: php create_admin.php <email>\n");
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    die("No user found with email {$email}. Register the account first.\n");
}

$stmt = $pdo->prepare('UPDATE users SET is_admin = 1 WHERE id = ?');
$stmt->execute([$user['id']]);

echo "{$user['name']} ({$email}) is now an admin.\n";
