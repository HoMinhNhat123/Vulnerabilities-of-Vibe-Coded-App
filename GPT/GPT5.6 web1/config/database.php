<?php
/**
 * Database configuration.
 *
 * Prefers MySQL when the pdo_mysql extension is installed.
 * Falls back to a local SQLite file when MySQL is unavailable
 * (common cause of "could not find driver").
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'community_forum');
define('DB_USER', 'forum_user');
define('DB_PASS', 'forum_pass123');
define('DB_SOCKET', '/run/mysqld/mysqld.sock');
define('DB_SQLITE_PATH', __DIR__ . '/../data/forum.sqlite');

function getDB() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $errors = [];

    // 1) MySQL via TCP / Unix socket when pdo_mysql is available
    if (in_array('mysql', PDO::getAvailableDrivers(), true)) {
        $candidates = [
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        ];
        if (DB_SOCKET !== '' && is_readable(DB_SOCKET)) {
            $candidates[] = 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        }
        foreach ($candidates as $dsn) {
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return $pdo;
            } catch (PDOException $e) {
                $errors[] = $e->getMessage();
            }
        }
    } else {
        $errors[] = 'PDO MySQL driver not installed (pdo_mysql).';
    }

    // 2) SQLite fallback — works with stock PHP on many machines
    if (in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $dir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        try {
            $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $options);
            $pdo->exec('PRAGMA foreign_keys = ON');
            return $pdo;
        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
        }
    } else {
        $errors[] = 'PDO SQLite driver not installed (pdo_sqlite).';
    }

    $hint = 'Install a driver, e.g. on Ubuntu/Debian: sudo apt install php-mysql   '
        . 'Or: sudo apt install php-sqlite3';
    die('Database connection failed: ' . htmlspecialchars(implode(' | ', $errors))
        . ' — ' . htmlspecialchars($hint));
}
