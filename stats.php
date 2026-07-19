<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

$totalRequests = (int)$pdo->query('SELECT COUNT(*) FROM help_requests')->fetchColumn();
$resolvedCount = (int)$pdo->query('SELECT COUNT(*) FROM help_requests WHERE resolved = 1')->fetchColumn();
$openCount = $totalRequests - $resolvedCount;

$totalUsers = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_banned = 0')->fetchColumn();
$totalVolunteers = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE is_volunteer = 1 AND is_banned = 0')->fetchColumn();

$byCategory = $pdo->query('SELECT category, COUNT(*) AS count FROM help_requests GROUP BY category ORDER BY count DESC')->fetchAll();
$byPriority = $pdo->query('SELECT priority, COUNT(*) AS count FROM help_requests GROUP BY priority ORDER BY count DESC')->fetchAll();

$fulfillment = $pdo->query('SELECT ROUND(AVG(collected_qty / target_qty) * 100, 1) FROM help_requests WHERE target_qty > 0')->fetchColumn();
$fulfillment = $fulfillment !== null ? (float)$fulfillment : 0.0;

$last7Days = $pdo->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS count
    FROM help_requests
    WHERE created_at >= (NOW() - INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll();

$topLocations = $pdo->query("
    SELECT location, COUNT(*) AS count
    FROM help_requests
    GROUP BY location
    ORDER BY count DESC
    LIMIT 5
")->fetchAll();

jsonResponse([
    'total_requests' => $totalRequests,
    'resolved_count' => $resolvedCount,
    'open_count' => $openCount,
    'total_users' => $totalUsers,
    'total_volunteers' => $totalVolunteers,
    'avg_fulfillment_percent' => $fulfillment,
    'by_category' => $byCategory,
    'by_priority' => $byPriority,
    'last_7_days' => $last7Days,
    'top_locations' => $topLocations,
]);
