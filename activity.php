<?php
require_once __DIR__ . '/db.php';

/**
 * Records one entry in the user's activity timeline (shown on the
 * profile page, both to the owner and to public viewers).
 *
 * This is intentionally best-effort: a logging failure must never break
 * the primary action (posting a request, sending a message, etc.), so
 * every call is wrapped in try/catch and swallows its own errors.
 */
function logActivity($userId, $actionType, $description, $requestId = null) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO activity_log (user_id, action_type, description, request_id) VALUES (?,?,?,?)'
        );
        $stmt->execute([(int)$userId, (string)$actionType, (string)$description, $requestId !== null ? (int)$requestId : null]);
    } catch (Throwable $e) {
        // Silently ignore — activity logging must never break the main request.
    }
}
