<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Profile';
$error = '';
$pdo = getDB();
$me = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please choose an image to upload.';
    } elseif ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed. Please try again.';
    } else {
        $file = $_FILES['profile_picture'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            $error = 'Image must be 2MB or smaller.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            if (!isset($allowed[$mime])) {
                $error = 'Only JPEG, PNG, GIF, or WebP images are allowed.';
            } else {
                $dir = __DIR__ . '/uploads/profiles';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $filename = 'user_' . (int)$_SESSION['user_id'] . '_' . time() . '.' . $allowed[$mime];
                $dest = $dir . '/' . $filename;
                $relative = 'uploads/profiles/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    $error = 'Could not save the uploaded file.';
                } else {
                    // Remove old picture if present
                    if (!empty($me['profile_picture'])) {
                        $old = __DIR__ . '/' . $me['profile_picture'];
                        if (is_file($old)) {
                            @unlink($old);
                        }
                    }
                    $upd = $pdo->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
                    $upd->execute([$relative, $_SESSION['user_id']]);
                    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Profile picture updated.'];
                    header('Location: profile.php');
                    exit;
                }
            }
        }
    }
}

// Refresh user after potential updates
$me = currentUser();
// Force re-fetch since static cache may be stale after redirect only
$stmt = $pdo->prepare('SELECT id, username, is_admin, profile_picture, created_at FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$me = $stmt->fetch();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Your profile</h1>
    <p class="lead">Update your profile picture and view account details.</p>
</div>

<div class="profile-card">
    <?php if (!empty($me['profile_picture']) && file_exists(__DIR__ . '/' . $me['profile_picture'])): ?>
        <img class="avatar-lg" src="<?php echo e($me['profile_picture']); ?>" alt="Profile picture">
    <?php else: ?>
        <div class="avatar-lg avatar-placeholder"><?php echo e(strtoupper(substr($me['username'], 0, 1))); ?></div>
    <?php endif; ?>
    <div>
        <h2 style="margin-top:0"><?php echo e($me['username']); ?></h2>
        <p class="post-meta">
            Member since <?php echo e(date('M j, Y', strtotime($me['created_at']))); ?>
            <?php if ($me['is_admin']): ?> · <span class="badge">Admin</span><?php endif; ?>
        </p>

        <?php if ($error): ?>
            <div class="flash flash-error"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="profile.php" enctype="multipart/form-data">
            <label for="profile_picture">Upload profile picture</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif,image/webp" required>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
