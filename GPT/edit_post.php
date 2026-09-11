<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post || (int)$post['user_id'] !== (int)$_SESSION['user_id']) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'You can only edit your own posts.'];
    header('Location: index.php');
    exit;
}

$pageTitle = 'Edit Post';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title === '' || $body === '') {
        $error = 'Title and body are required.';
    } elseif (mb_strlen($title) > 200) {
        $error = 'Title must be 200 characters or fewer.';
    } else {
        $upd = $pdo->prepare('UPDATE posts SET title = ?, body = ? WHERE id = ? AND user_id = ?');
        $upd->execute([$title, $body, $id, $_SESSION['user_id']]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Post updated.'];
        header('Location: post.php?id=' . $id);
        exit;
    }
    $post['title'] = $title;
    $post['body'] = $body;
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Edit post</h1>
</div>

<div class="form-panel wide">
    <?php if ($error): ?>
        <div class="flash flash-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post" action="edit_post.php">
        <input type="hidden" name="id" value="<?php echo (int)$post['id']; ?>">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" required maxlength="200"
               value="<?php echo e($post['title']); ?>">

        <label for="body">Body</label>
        <textarea id="body" name="body" required><?php echo e($post['body']); ?></textarea>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="post.php?id=<?php echo (int)$post['id']; ?>" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
