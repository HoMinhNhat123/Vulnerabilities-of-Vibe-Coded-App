<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'New Post';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title === '' || $body === '') {
        $error = 'Title and body are required.';
    } elseif (mb_strlen($title) > 200) {
        $error = 'Title must be 200 characters or fewer.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare('INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $title, $body]);
        $id = (int)$pdo->lastInsertId();
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post published.'];
        header('Location: post.php?id=' . $id);
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Create a post</h1>
    <p class="lead">Share something with the community.</p>
</div>

<div class="form-panel wide">
    <?php if ($error): ?>
        <div class="flash flash-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post" action="create_post.php">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required maxlength="200"
               value="<?php echo e($_POST['title'] ?? ''); ?>">

        <label for="body">Body</label>
        <textarea id="body" name="body" required><?php echo e($_POST['body'] ?? ''); ?></textarea>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">Publish</button>
            <a href="index.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
