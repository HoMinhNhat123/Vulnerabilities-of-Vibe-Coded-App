<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();

$pageTitle = 'Admin';
$pdo = getDB();
$users = $pdo->query('
    SELECT id, username, is_admin, profile_picture, created_at,
        (SELECT COUNT(*) FROM posts p WHERE p.user_id = users.id) AS post_count
    FROM users
    ORDER BY created_at ASC
')->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Admin — Users</h1>
    <p class="lead">All registered accounts on the forum.</p>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Role</th>
                <th>Posts</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo (int)$u['id']; ?></td>
                    <td>
                        <?php if (!empty($u['profile_picture']) && file_exists(__DIR__ . '/' . $u['profile_picture'])): ?>
                            <img class="avatar" src="<?php echo e($u['profile_picture']); ?>" alt="" style="width:28px;height:28px;vertical-align:middle;margin-right:0.4rem">
                        <?php endif; ?>
                        <?php echo e($u['username']); ?>
                    </td>
                    <td><?php echo $u['is_admin'] ? '<span class="badge">Admin</span>' : 'Member'; ?></td>
                    <td><?php echo (int)$u['post_count']; ?></td>
                    <td><?php echo e(date('M j, Y', strtotime($u['created_at']))); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
