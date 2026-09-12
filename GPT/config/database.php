<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'community_forum');
define('DB_USER', 'forum_user');
define('DB_PASS', 'forum_pass123');
define('DB_SOCKET', '/run/mysqld/mysqld.sock');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $errors = [];
        // Prefer TCP via DB_HOST; fall back to Unix socket when available.
        $candidates = [
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        ];
        if (defined('DB_SOCKET') && DB_SOCKET !== '' && is_readable(DB_SOCKET)) {
            $candidates[] = 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        }
        foreach ($candidates as $dsn) {
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                break;
            } catch (PDOException $e) {
                $errors[] = $e->getMessage();
            }
        }
        if ($pdo === null) {
            die('Database connection failed: ' . htmlspecialchars(implode(' | ', $errors)));
        }
    }
    return $pdo;
}
