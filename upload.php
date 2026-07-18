<?php
require_once __DIR__ . '/../config.php';

/**
 * Validates and stores an uploaded image from $_FILES[$field].
 * Returns the relative URL path to store in the DB, or null if no file was sent.
 * Calls jsonError() (which exits) on invalid input.
 */
function handleImageUpload($field) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$field];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        jsonError('Image upload failed (error code ' . $file['error'] . ').');
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        jsonError('Image is too large. Max size is 3MB.');
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        jsonError('Unsupported image type. Use JPG, PNG, GIF, or WEBP.');
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        jsonError('Could not save uploaded image.');
    }

    return UPLOAD_URL_PREFIX . $filename;
}
