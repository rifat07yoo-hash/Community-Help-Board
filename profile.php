<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_once __DIR__ . '/../includes/activity.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

/**
 * "Total Help Count" = number of *distinct other people's* help requests
 * this user has actively assisted with (by messaging the requester or
 * leaving a public comment). It is computed live from real activity
 * rather than stored, so it can never drift out of sync.
 */
function getTotalHelpCount($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(DISTINCT hr.id)
            FROM help_requests hr
            WHERE hr.user_id != ?
              AND (
                  EXISTS (SELECT 1 FROM messages m WHERE m.request_id = hr.id AND m.sender_id = ?)
                  OR EXISTS (SELECT 1 FROM comments c WHERE c.request_id = hr.id AND c.user_id = ?)
              )
        ");
        $stmt->execute([$userId, $userId, $userId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Recent activity timeline for a user (used on both the owner's settings
 * page and the public profile modal). Joins help_requests only to grab a
 * location label for context; request_id may be NULL if the post was
 * later deleted (FK is ON DELETE SET NULL), which is handled gracefully.
 */
function getActivityLog($pdo, $userId, $limit = 25) {
    $limit = max(1, min(100, (int)$limit));
    try {
        $stmt = $pdo->prepare("
            SELECT a.id, a.action_type, a.description, a.request_id, a.created_at,
                   hr.location AS request_location
            FROM activity_log a
            LEFT JOIN help_requests hr ON hr.id = a.request_id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
            LIMIT $limit
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// GET ?id=X -> public profile (no auth required to view)
if ($method === 'GET') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonError('Missing id.');

    $stmt = $pdo->prepare('SELECT ' . PUBLIC_USER_COLUMNS . ' FROM users WHERE id = ?');
    try {
        $stmt->execute([$id]);
        $profile = $stmt->fetch();
    } catch (PDOException $e) {
        $stmt = $pdo->prepare('SELECT ' . PUBLIC_USER_COLUMNS_FALLBACK . ' FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $profile = $stmt->fetch();
        if ($profile) {
            $profile['bio'] = null;
            $profile['social_link'] = null;
        }
    }
    if (!$profile) jsonError('User not found.', 404);

    $stmt = $pdo->prepare('SELECT id, category, priority, location, description, created_at FROM help_requests WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$id]);
    $posts = $stmt->fetchAll();

    $stmt = $pdo->prepare('SELECT score FROM ratings WHERE target_user_id = ?');
    $stmt->execute([$id]);
    $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $avg = count($scores) ? round(array_sum($scores) / count($scores), 1) : 0;

    $totalHelpCount = getTotalHelpCount($pdo, $id);
    $activity = getActivityLog($pdo, $id);

    jsonResponse([
        'profile' => $profile,
        'posts' => $posts,
        'rating_average' => $avg,
        'rating_count' => count($scores),
        'total_help_count' => $totalHelpCount,
        'activity' => $activity,
    ]);
}

// POST (multipart form) -> update own profile
if ($method === 'POST') {
    $user = requireAuth();

    $name = trim($_POST['name'] ?? $user['name']);
    $phone = trim($_POST['phone'] ?? $user['phone']);
    $location = trim($_POST['location'] ?? $user['location']);
    $blood = $_POST['blood_group'] ?? $user['blood_group'];
    $bio = trim($_POST['bio'] ?? ($user['bio'] ?? ''));
    $socialLink = trim($_POST['social_link'] ?? ($user['social_link'] ?? ''));

    $validBlood = ['A+','A-','B+','B-','O+','O-','AB+','AB-','Unknown'];
    if (!in_array($blood, $validBlood, true)) $blood = $user['blood_group'];

    if ($name === '') jsonError('Name cannot be empty.');
    if (!preg_match('/^[0-9]{11}$/', $phone)) jsonError('Phone number must be 11 digits.');

    if (mb_strlen($bio) > 500) jsonError('Bio must be 500 characters or fewer.');

    if ($socialLink !== '') {
        // Allow the user to omit the protocol (e.g. "facebook.com/name")
        if (!preg_match('#^https?://#i', $socialLink)) {
            $socialLink = 'https://' . $socialLink;
        }
        if (!filter_var($socialLink, FILTER_VALIDATE_URL) || mb_strlen($socialLink) > 255) {
            jsonError('Please provide a valid social/portfolio link (e.g. https://facebook.com/yourname).');
        }
    }

    $imagePath = handleImageUpload('profile_image');
    if ($imagePath === null) $imagePath = $user['profile_image'];

    try {
        $stmt = $pdo->prepare('UPDATE users SET name=?, phone=?, location=?, blood_group=?, bio=?, social_link=?, profile_image=? WHERE id=?');
        $stmt->execute([$name, $phone, $location, $blood, $bio !== '' ? $bio : null, $socialLink !== '' ? $socialLink : null, $imagePath, $user['id']]);
    } catch (PDOException $e) {
        // bio/social_link columns still missing (auto-migration couldn't run) —
        // save everything else so the core profile update still works.
        $stmt = $pdo->prepare('UPDATE users SET name=?, phone=?, location=?, blood_group=?, profile_image=? WHERE id=?');
        $stmt->execute([$name, $phone, $location, $blood, $imagePath, $user['id']]);
    }

    logActivity($user['id'], 'profile_update', 'Updated profile information 📝');

    jsonResponse(['success' => true]);
}

jsonError('Method not allowed', 405);
