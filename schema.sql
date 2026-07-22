-- =====================================================================
-- Community Help Board & Emergency Network
-- MySQL Schema (CSE 2208 DBMS Lab Project)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS community_help_board
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE community_help_board;

-- ---------------------------------------------------------------------
-- USERS
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)  NOT NULL,
    email           VARCHAR(150)  NOT NULL UNIQUE,
    password        VARCHAR(255)  NOT NULL,
    phone           VARCHAR(20)   DEFAULT NULL,
    location        VARCHAR(255)  DEFAULT NULL,
    blood_group     ENUM('A+','A-','B+','B-','O+','O-','AB+','AB-','Unknown') DEFAULT 'Unknown',
    is_volunteer    TINYINT(1)    NOT NULL DEFAULT 0,
    is_admin        TINYINT(1)    NOT NULL DEFAULT 0,
    is_banned       TINYINT(1)    NOT NULL DEFAULT 0,
    profile_image   VARCHAR(255)  DEFAULT NULL,
    bio             VARCHAR(500)  DEFAULT NULL,
    social_link     VARCHAR(255)  DEFAULT NULL,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- HELP REQUESTS
-- ---------------------------------------------------------------------
CREATE TABLE help_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    category        ENUM('food','blood','medical','shelter','other') NOT NULL,
    priority        ENUM('urgent','high','medium') NOT NULL DEFAULT 'medium',
    location        VARCHAR(255) NOT NULL,
    contact         VARCHAR(50)  NOT NULL,
    description     TEXT NOT NULL,
    target_qty      INT NOT NULL DEFAULT 1,
    collected_qty   INT NOT NULL DEFAULT 0,
    image           VARCHAR(255) DEFAULT NULL,
    resolved        TINYINT(1)   NOT NULL DEFAULT 0,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_resolved (resolved)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- COMMENTS (public discussion on a request)
-- ---------------------------------------------------------------------
CREATE TABLE comments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    user_id         INT NOT NULL,
    text            TEXT NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- MESSAGES (private coordination chat, tied to a request)
-- ---------------------------------------------------------------------
CREATE TABLE messages (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    sender_id       INT NOT NULL,
    recipient_id    INT NOT NULL,
    text            TEXT NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_request (request_id),
    INDEX idx_recipient_read (recipient_id, is_read)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- NOTIFICATIONS (global activity feed)
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    text            VARCHAR(500) NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- RATINGS (one review per rater -> target)
-- ---------------------------------------------------------------------
CREATE TABLE ratings (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    target_user_id      INT NOT NULL,
    rated_by_user_id    INT NOT NULL,
    score               TINYINT NOT NULL,
    review              TEXT DEFAULT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rated_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_rating (target_user_id, rated_by_user_id),
    CONSTRAINT chk_score CHECK (score BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- REPORTS (flagging a post; auto-hidden at >= 3 reports)
-- ---------------------------------------------------------------------
CREATE TABLE reports (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    request_id            INT NOT NULL,
    reported_by_user_id   INT NOT NULL,
    created_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_report (request_id, reported_by_user_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- CONTRIBUTIONS (records who helped a request and how much, so posts can
-- show "X people helped" instead of just an aggregate collected_qty)
-- ---------------------------------------------------------------------
CREATE TABLE contributions (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    user_id         INT NOT NULL,
    quantity        INT NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES help_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_request (request_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- ACTIVITY LOG (per-user timeline of actions, shown on the profile page)
-- ---------------------------------------------------------------------
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
) ENGINE=InnoDB;

-- NOTE: No admin account is seeded here because a correct bcrypt hash
-- cannot be hand-written safely. Register a normal account through the
-- app, then run create_admin.php once (see README) to promote it to admin.
