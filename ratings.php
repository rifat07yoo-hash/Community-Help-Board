<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $userId = (int)($_GET['user_id'] ?? 0);
    if (!$userId) jsonError('Missing user_id.');

    $stmt = $pdo->prepare("SELECT rt.score, rt.review, rt.created_at, u.name AS reviewer_name, u.id AS reviewer_id
        FROM ratings rt JOIN users u ON u.id = rt.rated_by_user_id
        WHERE rt.target_user_id = ? ORDER BY rt.created_at DESC");
    $stmt->execute([$userId]);
    $ratings = $stmt->fetchAll();

    $avg = 0;
    if (count($ratings) > 0) {
        $avg = round(array_sum(array_column($ratings, 'score')) / count($ratings), 1);
    }

    jsonResponse(['ratings' => $ratings, 'average' => $avg, 'count' => count($ratings)]);
}

if ($method === 'POST') {
    $user = requireAuth();
    $data = readJsonBody();
    $targetUserId = (int)($data['target_user_id'] ?? 0);
    $score = (int)($data['score'] ?? 0);
    $review = trim($data['review'] ?? '');

    if (!$targetUserId) jsonError('Missing target_user_id.');
    if ($score < 1 || $score > 5) jsonError('Score must be between 1 and 5.');
    if ($targetUserId === (int)$user['id']) jsonError('You cannot rate yourself.');

    $stmt = $pdo->prepare('SELECT id, name FROM users WHERE id = ?');
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch();
    if (!$targetUser) jsonError('Target user not found.', 404);

    $stmt = $pdo->prepare("INSERT INTO ratings (target_user_id, rated_by_user_id, score, review)
        VALUES (?,?,?,?)
        ON DUPLICATE KEY UPDATE score = VALUES(score), review = VALUES(review), created_at = CURRENT_TIMESTAMP");
    $stmt->execute([$targetUserId, $user['id'], $score, $review]);

    $stars = str_repeat('⭐', $score);
    logActivity($user['id'], 'rating_given', "Rated {$targetUser['name']} {$stars}");

    jsonResponse(['success' => true]);
}

jsonError('Method not allowed', 405);
