<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser($refresh = false) {
    if (!isLoggedIn()) {
        return null;
    }
    static $user = null;
    if ($user === null || $refresh) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, username, is_admin, profile_picture, created_at FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
    return $user;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    $user = currentUser();
    if (!$user || !$user['is_admin']) {
        header('Location: index.php');
        exit;
    }
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
