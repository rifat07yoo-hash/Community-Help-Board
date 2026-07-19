<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

// Top-rated helpers: average rating, requires at least 1 review
$topRated = $pdo->query("
    SELECT u.id, u.name, u.profile_image, u.location,
           ROUND(AVG(r.score), 1) AS avg_score,
           COUNT(r.id) AS review_count
    FROM users u
    JOIN ratings r ON r.target_user_id = u.id
    WHERE u.is_banned = 0
    GROUP BY u.id
    HAVING COUNT(r.id) >= 1
    ORDER BY avg_score DESC, review_count DESC
    LIMIT 10
")->fetchAll();

// Most active helpers: most requests they personally resolved
$mostActive = $pdo->query("
    SELECT u.id, u.name, u.profile_image, u.location,
           COUNT(hr.id) AS resolved_count
    FROM users u
    JOIN help_requests hr ON hr.user_id = u.id AND hr.resolved = 1
    WHERE u.is_banned = 0
    GROUP BY u.id
    ORDER BY resolved_count DESC
    LIMIT 10
")->fetchAll();

jsonResponse(['top_rated' => $topRated, 'most_active' => $mostActive]);
