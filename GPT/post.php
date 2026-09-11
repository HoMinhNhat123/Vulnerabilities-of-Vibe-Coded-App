<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: index.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('
    SELECT p.*, u.username, u.profile_picture
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.id = ?
');
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Post not found.'];
    header('Location: index.php');
    exit;
}

$commentError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_body'])) {
    requireLogin();
    $body = trim($_POST['comment_body'] ?? '');
    if ($body === '') {
        $commentError = 'Comment cannot be empty.';
    } else {
        $ins = $pdo->prepare('INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)');
        $ins->execute([$id, $_SESSION['user_id'], $body]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Comment added.'];
        header('Location: post.php?id=' . $id);
        exit;
    }
}

$cstmt = $pdo->prepare('
    SELECT c.*, u.username, u.profile_picture
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at ASC
');
$cstmt->execute([$id]);
$comments = $cstmt->fetchAll();

$pageTitle = $post['title'];
$me = currentUser();
$isOwner = $me && (int)$me['id'] === (int)$post['user_id'];

require_once __DIR__ . '/includes/header.php';
?>

<article class="post-detail">
    <div class="page-head" style="margin-bottom:0.75rem">
        <h1><?php echo e($post['title']); ?></h1>
        <div class="post-meta">
            by <?php echo e($post['username']); ?>
            · <?php echo e(date('M j, Y g:i A', strtotime($post['created_at']))); ?>
            <?php if ($post['updated_at'] !== $post['created_at']): ?>
                · edited <?php echo e(date('M j, Y g:i A', strtotime($post['updated_at']))); ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="post-body"><?php echo e($post['body']); ?></div>
    <?php if ($isOwner): ?>
        <div class="btn-row" style="margin-top:1.25rem">
            <a class="btn btn-ghost" href="edit_post.php?id=<?php echo (int)$post['id']; ?>">Edit</a>
            <form method="post" action="delete_post.php" data-confirm="Delete this post permanently?">
                <input type="hidden" name="id" value="<?php echo (int)$post['id']; ?>">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    <?php endif; ?>
</article>

<section class="comments">
    <h2>Comments (<?php echo count($comments); ?>)</h2>

    <?php if (empty($comments)): ?>
        <p class="lead">No comments yet.</p>
    <?php else: ?>
        <?php foreach ($comments as $c): ?>
            <div class="comment">
                <div class="comment-meta">
                    <?php if (!empty($c['profile_picture']) && file_exists(__DIR__ . '/' . $c['profile_picture'])): ?>
                        <img class="avatar" src="<?php echo e($c['profile_picture']); ?>" alt="" style="width:28px;height:28px;vertical-align:middle;margin-right:0.35rem">
                    <?php endif; ?>
                    <strong><?php echo e($c['username']); ?></strong>
                    · <?php echo e(date('M j, Y g:i A', strtotime($c['created_at']))); ?>
                </div>
                <div><?php echo e($c['body']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isLoggedIn()): ?>
        <div class="form-panel wide" style="margin-top:1.5rem">
            <h3 style="margin-top:0">Add a comment</h3>
            <?php if ($commentError): ?>
                <div class="flash flash-error"><?php echo e($commentError); ?></div>
            <?php endif; ?>
            <form method="post" action="post.php?id=<?php echo $id; ?>">
                <label for="comment_body">Your comment</label>
                <textarea id="comment_body" name="comment_body" required style="min-height:100px"></textarea>
                <button type="submit" class="btn btn-primary">Post comment</button>
            </form>
        </div>
    <?php else: ?>
        <p class="lead"><a href="login.php">Log in</a> to leave a comment.</p>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
