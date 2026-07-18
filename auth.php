<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/response.php';

const PUBLIC_USER_COLUMNS = 'id,name,email,phone,location,blood_group,is_volunteer,is_admin,is_banned,profile_image,created_at';

function currentUser() {
    if (empty($_SESSION['user_id'])) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT ' . PUBLIC_USER_COLUMNS . ' FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) return null;
    if ((int)$user['is_banned'] === 1) return null;
    return $user;
}

function requireAuth() {
    $user = currentUser();
    if (!$user) {
        jsonError('Authentication required. Please sign in.', 401);
    }
    return $user;
}

function requireAdmin() {
    $user = requireAuth();
    if ((int)$user['is_admin'] !== 1) {
        jsonError('Admin access required.', 403);
    }
    return $user;
}

/**
 * Inserts a row into the notifications feed.
 */
function addNotification($text) {
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO notifications (text) VALUES (?)');
    $stmt->execute([$text]);
}
