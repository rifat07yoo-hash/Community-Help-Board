<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/response.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/activity.php';

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ---------------------------------------------------------------------
// GET — list contributions (who helped, how much) for one request
// ---------------------------------------------------------------------
if ($method === 'GET') {
    requireAuth();

    $requestId = (int)($_GET['request_id'] ?? 0);
    if (!$requestId) jsonError('Missing request_id.');

    $stmt = $pdo->prepare("
        SELECT c.id, c.request_id, c.user_id, c.quantity, c.created_at,
               u.name AS user_name, u.profile_image AS user_avatar
        FROM contributions c
        JOIN users u ON u.id = c.user_id
        WHERE c.request_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$requestId]);
    $contributions = $stmt->fetchAll();

    $helperCount = count(array_unique(array_column($contributions, 'user_id')));

    jsonResponse(['contributions' => $contributions, 'helper_count' => $helperCount]);
}

// ---------------------------------------------------------------------
// POST — log a contribution ("I helped") and bump collected_qty
// ---------------------------------------------------------------------
if ($method === 'POST') {
    $user = requireAuth();
    $body = readJsonBody();

    $requestId = (int)($body['request_id'] ?? 0);
    $quantity = (int)($body['quantity'] ?? 0);

    if (!$requestId) jsonError('Missing request_id.');
    if ($quantity < 1) jsonError('Quantity must be at least 1.');

    $stmt = $pdo->prepare('SELECT id, user_id, location, target_qty, collected_qty, resolved FROM help_requests WHERE id = ?');
    $stmt->execute([$requestId]);
    $reqRow = $stmt->fetch();
    if (!$reqRow) jsonError('Request not found.', 404);
    if ((int)$reqRow['user_id'] === (int)$user['id']) {
        jsonError('You cannot log a contribution on your own request — update the collected amount from Edit instead.', 403);
    }
    if ((int)$reqRow['resolved'] === 1) {
        jsonError('This request has already been resolved.', 400);
    }

    $stmt = $pdo->prepare('INSERT INTO contributions (request_id, user_id, quantity) VALUES (?,?,?)');
    $stmt->execute([$requestId, $user['id'], $quantity]);

    $newCollected = min((int)$reqRow['target_qty'], (int)$reqRow['collected_qty'] + $quantity);
    $stmt = $pdo->prepare('UPDATE help_requests SET collected_qty = ? WHERE id = ?');
    $stmt->execute([$newCollected, $requestId]);

    addNotification("{$user['name']} helped with the request in {$reqRow['location']}!");
    logActivity($user['id'], 'contribution', "Helped with a request in {$reqRow['location']} 🤝", $requestId);

    jsonResponse(['success' => true, 'collected_qty' => $newCollected], 201);
}

jsonError('Method not allowed', 405);
