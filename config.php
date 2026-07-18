<?php
// =====================================================================
// Community Help Board — Configuration
// Edit DB_USER / DB_PASS to match your local MySQL setup.
// =====================================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'community_help_board');
define('DB_USER', 'root');
define('DB_PASS', '');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL_PREFIX', 'uploads/');
define('MAX_UPLOAD_BYTES', 3 * 1024 * 1024); // 3MB

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
