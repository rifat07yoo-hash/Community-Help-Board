<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireAuth();
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Method not allowed');
}

$stmt = $pdo->prepare("
    SELECT id, category, priority, location, contact, description,
           target_qty, collected_qty, resolved, created_at
    FROM help_requests
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$user['id']]);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="my_requests_export.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'Category', 'Priority', 'Location', 'Contact', 'Description', 'Target Qty', 'Collected Qty', 'Resolved', 'Created At']);

foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'], $r['category'], $r['priority'], $r['location'], $r['contact'],
        $r['description'], $r['target_qty'], $r['collected_qty'],
        $r['resolved'] ? 'Yes' : 'No', $r['created_at'],
    ]);
}

fclose($out);
exit;
