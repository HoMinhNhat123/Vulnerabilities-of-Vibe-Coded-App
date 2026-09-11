<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'You are logged in.'];
            header('Location: index.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Log in</h1>
    <p class="lead">Sign in to create posts and leave comments.</p>
</div>

<div class="form-panel">
    <?php if ($error): ?>
        <div class="flash flash-error"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required
               value="<?php echo e($_POST['username'] ?? ''); ?>">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-primary">Log in</button>
    </form>
    <p style="margin-top:1rem;color:var(--ink-muted)">No account? <a href="register.php">Register</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
