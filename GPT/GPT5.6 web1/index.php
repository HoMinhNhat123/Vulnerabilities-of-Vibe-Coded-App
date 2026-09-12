<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$q = trim($_GET['q'] ?? '');

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, u.username,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
        FROM posts p
        JOIN users u ON u.id = p.user_id
        WHERE p.title LIKE ? OR p.body LIKE ?
        ORDER BY p.created_at DESC
    ");
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
} else {
    $stmt = $pdo->query("
        SELECT p.*, u.username,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
        FROM posts p
        JOIN users u ON u.id = p.user_id
        ORDER BY p.created_at DESC
        LIMIT 50
    ");
}
$posts = $stmt->fetchAll();
?>

<div class="page-head">
    <h1><?php echo $q !== '' ? 'Search results' : 'Recent posts'; ?></h1>
    <p class="lead">
        <?php if ($q !== ''): ?>
            Showing posts matching “<?php echo e($q); ?>”.
        <?php else: ?>
            Browse the latest discussions from the community.
        <?php endif; ?>
    </p>
</div>

<form class="search-bar" method="get" action="index.php" role="search">
    <input type="text" name="q" placeholder="Search posts by keyword…" value="<?php echo e($q); ?>" aria-label="Search posts">
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if ($q !== ''): ?>
        <a href="index.php" class="btn btn-ghost">Clear</a>
    <?php endif; ?>
</form>

<section class="posts">
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <p><?php echo $q !== '' ? 'No posts matched your search.' : 'No posts yet. Be the first to write one!'; ?></p>
            <?php if (isLoggedIn()): ?>
                <p><a class="btn btn-primary" href="create_post.php">Create a post</a></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <article class="post-item">
                <h2><a href="post.php?id=<?php echo (int)$post['id']; ?>"><?php echo e($post['title']); ?></a></h2>
                <div class="post-meta">
                    by <?php echo e($post['username']); ?>
                    · <?php echo e(date('M j, Y g:i A', strtotime($post['created_at']))); ?>
                    · <?php echo (int)$post['comment_count']; ?> comment<?php echo (int)$post['comment_count'] === 1 ? '' : 's'; ?>
                </div>
                <p class="post-excerpt"><?php echo e(mb_strimwidth($post['body'], 0, 180, '…')); ?></p>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
