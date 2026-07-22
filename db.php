<?php
require_once __DIR__ . '/../config.php';

/**
 * Self-healing schema check. If this codebase was dropped onto a database
 * created by an older schema.sql (before bio/social_link/activity_log
 * existed), every authenticated request would otherwise fail with an
 * "Unknown column" SQL error — because currentUser() explicitly selects
 * those columns. This runs once per request, is idempotent, and silently
 * no-ops once the schema is already current.
 */
function ensureSchemaUpgrades(PDO $pdo) {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    try {
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'
              AND COLUMN_NAME IN ('bio','social_link')
        ");
        $stmt->execute();
        $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!in_array('bio', $existingCols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN bio VARCHAR(500) DEFAULT NULL AFTER profile_image");
        }
        if (!in_array('social_link', $existingCols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN social_link VARCHAR(255) DEFAULT NULL AFTER bio");
        }

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_log'
        ");
        $stmt->execute();
        if ((int)$stmt->fetchColumn() === 0) {
            $pdo->exec("
                CREATE TABLE activity_log (
                    id              INT AUTO_INCREMENT PRIMARY KEY,
                    user_id         INT NOT NULL,
                    action_type     VARCHAR(30)  NOT NULL,
                    description     VARCHAR(255) NOT NULL,
                    request_id      INT DEFAULT NULL,
                    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (request_id) REFERENCES help_requests(id) ON DELETE SET NULL,
                    INDEX idx_user_created (user_id, created_at)
                ) ENGINE=InnoDB
            ");
        }
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'contributions'
        ");
        $stmt->execute();
        if ((int)$stmt->fetchColumn() === 0) {
            $pdo->exec("
                CREATE TABLE contributions (
                    id              INT AUTO_INCREMENT PRIMARY KEY,
                    request_id      INT NOT NULL,
                    user_id         INT NOT NULL,
                    quantity        INT NOT NULL DEFAULT 1,
                    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    INDEX idx_request (request_id)
                ) ENGINE=InnoDB
            ");
        }
    } catch (Throwable $e) {
        // If the DB user lacks ALTER/CREATE privileges, we deliberately do
        // NOT crash the request here — downstream code already treats
        // bio/social_link/activity as optional-safe (see profile.php).
        // The person should run the SQL from README.md § "Upgrading an
        // existing database" manually in that case.
    }
}

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            ensureSchemaUpgrades($pdo);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed. Check config.php credentials.']);
            exit;
        }
    }
    return $pdo;
}
