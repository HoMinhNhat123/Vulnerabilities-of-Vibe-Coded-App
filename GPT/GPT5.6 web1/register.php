<?php
$pageTitle = 'Register';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = 'Username must be 3–50 characters (letters, numbers, underscore).';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = getDB();
        $check = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = 'That username is already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $ins->execute([$username, $hash]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Welcome! Your account was created.'];
            header('Location: index.php');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Create an account</h1>
    <p class="lead">Join the forum to post and comment.</p>
</div>

<div class="form-panel">
    <?php if ($error): ?>
        <div class="flash flash-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required maxlength="50"
               value="<?php echo e($_POST['username'] ?? ''); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">

        <label for="confirm">Confirm password</label>
        <input type="password" id="confirm" name="confirm" required minlength="6">

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
    <p style="margin-top:1rem;color:var(--ink-muted)">Already have an account? <a href="login.php">Log in</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
