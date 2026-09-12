<?php
/**
 * One-time setup: creates tables and default admin account.
 * Run once after configuring MySQL, then delete or protect this file.
 */
require_once __DIR__ . '/config/database.php';

$messages = [];
$error = null;

try {
    $pdo = getDB();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            profile_picture VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(200) NOT NULL,
            body TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            body TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $messages[] = 'Tables created successfully.';

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    if (!$stmt->fetch()) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $ins = $pdo->prepare('INSERT INTO users (username, password, is_admin) VALUES (?, ?, 1)');
        $ins->execute(['admin', $hash]);
        $messages[] = 'Admin account created (username: admin, password: admin123).';
    } else {
        $messages[] = 'Admin account already exists.';
    }

    $uploadDir = __DIR__ . '/uploads/profiles';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        $messages[] = 'Upload directory created.';
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Setup — Community Forum</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="page-bg"></div>
<main class="container main-content">
    <div class="page-head">
        <h1>Database Setup</h1>
    </div>
    <div class="form-panel">
        <?php if ($error): ?>
            <div class="flash flash-error"><?php echo htmlspecialchars($error); ?></div>
        <?php else: ?>
            <?php foreach ($messages as $m): ?>
                <div class="flash flash-success"><?php echo htmlspecialchars($m); ?></div>
            <?php endforeach; ?>
            <p><a class="btn btn-primary" href="index.php">Go to homepage</a></p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
