# Community Help Board — PHP + MySQL Edition

Rebuilt from the original Firebase/Firestore version to use **PHP + MySQL**,
per the CSE 2208 DBMS Lab requirement (CRUD operations implemented with MySQL).

## Stack
- Backend: PHP 8+ (PDO, prepared statements everywhere — no raw string SQL)
- Database: MySQL / MariaDB (`schema.sql`)
- Frontend: plain HTML + Tailwind (CDN) + vanilla JS (`assets/app.js`), talking
  to the PHP backend over `fetch()` — no Firebase, no client-side database calls.

## 1. Requirements
- PHP 8.0+ with `pdo_mysql`, `fileinfo` extensions enabled
- MySQL 5.7+ / MariaDB 10.3+
- Any local server: XAMPP, Laragon, WAMP, or `php -S` + a MySQL server

## 2. Setup

1. **Create the database and tables**
   ```
   mysql -u root -p < schema.sql
   ```
   (Or open `schema.sql` in phpMyAdmin and run it.)

2. **Configure credentials** — edit `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'community_help_board');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. **Make the uploads folder writable**
   ```
   chmod 755 uploads
   ```

4. **Put the project in your web root** (e.g. `htdocs/community-help-board`)
   and start Apache/MySQL (XAMPP control panel), or run PHP's built-in
   server from the project folder for quick local testing:
   ```
   php -S localhost:8000
   ```

5. **Open the app** at `http://localhost/community-help-board/` (or
   `http://localhost:8000/`), register an account through the UI.

6. **Promote yourself to admin** (one-time, from the command line —
   run this AFTER registering that account through the signup form):
   ```
   php create_admin.php your-email@example.com
   ```

### Upgrading an existing database

If you already ran `schema.sql` before this update, run these two
statements once instead of recreating the database (they're additive and
won't touch existing data):

```sql
ALTER TABLE users
    ADD COLUMN bio VARCHAR(500) DEFAULT NULL AFTER profile_image,
    ADD COLUMN social_link VARCHAR(255) DEFAULT NULL AFTER bio;

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
```

## 3. Project structure

```
community-help-board/
├── schema.sql              -- MySQL schema (8 tables, FKs, cascading deletes)
├── config.php               -- DB credentials
├── create_admin.php         -- CLI: promote a user to admin
├── index.php                 -- Frontend shell (login/dashboard/profile/admin)
├── includes/
│   ├── db.php                -- PDO connection
│   ├── response.php          -- JSON response helpers
│   ├── auth.php               -- session/auth guards
│   ├── upload.php             -- image upload validation & storage
│   └── activity.php           -- ★ activity-timeline logging helper
├── auth_api/
│   ├── register.php, login.php, logout.php, session.php
├── api/
│   ├── requests.php   -- ★ core CRUD: Create/Read/Update/Delete help requests
│   ├── comments.php   -- CRUD for public comments on a request
│   ├── messages.php   -- private coordination chat per request
│   ├── notifications.php
│   ├── ratings.php    -- star ratings/reviews between users
│   ├── reports.php    -- flag/report a post
│   ├── admin.php      -- ban/unban/delete users, delete posts, clear flags
│   ├── profile.php    -- update own profile / view public profile (bio, social link, help count, activity)
│   ├── donors.php     -- registered blood donor directory
│   ├── stats.php          -- ★ platform statistics (COUNT/GROUP BY/AVG)
│   ├── leaderboard.php    -- ★ top-rated & most-active helpers (JOIN/GROUP BY/HAVING)
│   ├── change_password.php-- ★ dedicated password-change endpoint
│   ├── export.php         -- ★ CSV export of the current user's own requests
│   └── search.php         -- ★ global search across users + requests
├── uploads/            -- uploaded images (post photos, avatars)
└── assets/app.js       -- all frontend logic (fetch calls, rendering)
```

## 4. Database design (maps to the "Project Features & Functionality" +
   "GitHub Commit History" evaluation criteria)

8 normalized tables with foreign keys and `ON DELETE CASCADE` (or
`ON DELETE SET NULL` where the row should survive its parent's deletion):
`users` (now includes `bio` and `social_link`), `help_requests`, `comments`,
`messages`, `notifications`, `ratings` (unique per rater→target pair),
`reports` (unique per reporter→post pair, auto-hides a post at 3+ reports),
and `activity_log` (per-user action timeline).

## 5. Security notes
- Every query uses PDO **prepared statements** — no string-concatenated SQL.
- Passwords are hashed with `password_hash()` / verified with `password_verify()`.
- Sessions are server-side (`$_SESSION`); ownership is checked server-side
  before every update/delete (not just hidden in the UI).
- Uploaded files are re-validated by MIME type (via `fileinfo`), renamed
  to random names, and the `uploads/` folder blocks PHP execution via
  `.htaccess`.

## 6. Newer additions

- **📊 Insights modal** (header button) — calls `api/stats.php` (aggregate counts,
  category/priority breakdown, average fulfillment %, 7-day trend, top locations)
  and `api/leaderboard.php` (top-rated helpers via `AVG`/`GROUP BY`/`HAVING`, and
  most-active helpers by resolved-request count).
- **🔎 Live global search** — typing in the main search box now also queries
  `api/search.php`, showing matching people and posts in a strip above the feed.
- **🔒 Change Password** — a dedicated form on the profile page, backed by
  `api/change_password.php` (re-verifies the current password before updating).
- **⬇️ Export CSV** — button on "My Personal Posts" downloads all of your own
  requests as a CSV via `api/export.php`.

## 7. Latest profile & security additions

- **📝 Bio** — a short (≤500 char) public bio field on every profile, editable
  from the Settings page, shown on both your own profile and the public
  profile modal.
- **🔗 Social / Portfolio Link** — one link (Facebook, LinkedIn, personal site,
  etc.). The backend auto-prefixes `https://` if omitted and validates it's a
  real URL before saving (`api/profile.php`).
- **🤝 Total Help Count** — a live-computed stat (not a stored counter, so it
  can never drift) showing how many *other people's* requests you've actively
  assisted with, based on distinct requests where you sent a message or left
  a comment as a non-owner.
- **🕐 Activity Timeline** — a new `activity_log` table + `includes/activity.php`
  helper records every meaningful action (registering, posting/editing/
  deleting/resolving a request, commenting, messaging, rating someone,
  updating your profile, changing your password) and renders it as a
  scrollable, animated timeline on both your own Settings page and the public
  profile modal.
- **🔑 Password strength meter** — the registration page now shows a live
  Weak / Medium / Strong meter as you type, with a hard client- and
  server-side minimum of 6 characters (weak/too-short attempts are rejected
  with a shake animation before any request is sent).
- **✨ Extra animations** — new stat-card pop-in, activity-item slide-in, and
  strength-bar transition animations layered on top of the existing
  fade/entrance system.

## 8. What changed vs. the original
The original used Firebase Auth + Firestore (a NoSQL, client-side database),
which doesn't satisfy a "CRUD with MySQL" requirement and has no real schema.
This version keeps the same UI/UX and feature set (post requests, comments,
private messaging, notifications, ratings, blood donor directory, admin
moderation) but every read/write now goes through PHP endpoints backed by
a real relational MySQL schema. Two things were simplified since they don't
affect the DBMS requirement: the live "user is typing…" indicator was
dropped, and updates refresh via a 10-second poll instead of a push
subscription (MySQL has no built-in realtime listener like Firestore).
