<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$pdo = getDB();
$stmt = $pdo->prepare('SELECT user_id FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post || (int)$post['user_id'] !== (int)$_SESSION['user_id']) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'You can only delete your own posts.'];
    header('Location: index.php');
    exit;
}

$del = $pdo->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
$del->execute([$id, $_SESSION['user_id']]);
$_SESSION['flash'] = ['type' => 'success', 'message' => 'Post deleted.'];
header('Location: index.php');
exit;
