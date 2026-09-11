<?php
require_once __DIR__ . '/auth.php';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' — ' : ''; ?>Community Forum</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
</head>
<body>
    <div class="page-bg" aria-hidden="true"></div>
    <header class="site-header">
        <div class="container header-inner">
            <a href="index.php" class="brand">Community Forum</a>
            <nav class="nav">
                <a href="index.php">Home</a>
                <?php if ($user): ?>
                    <a href="create_post.php">New Post</a>
                    <a href="profile.php">Profile</a>
                    <?php if ($user['is_admin']): ?>
                        <a href="admin.php">Admin</a>
                    <?php endif; ?>
                    <span class="nav-user">Hi, <?php echo e($user['username']); ?></span>
                    <a href="logout.php" class="btn btn-ghost">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="container main-content">
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="flash flash-<?php echo e($_SESSION['flash']['type']); ?>">
                <?php echo e($_SESSION['flash']['message']); unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
