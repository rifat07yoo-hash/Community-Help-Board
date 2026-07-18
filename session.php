<?php
require_once __DIR__ . '/../includes/auth.php';

$user = currentUser();
if (!$user) {
    jsonResponse(['user' => null]);
}
jsonResponse(['user' => $user]);
