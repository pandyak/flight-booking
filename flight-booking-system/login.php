<?php
$page_title = 'Login';
require_once __DIR__ . '/db.php';
require_once __DIR__ . './includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE . 'dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');

            if ($user['role'] === 'admin') {
                header('Location: ' . BASE . 'admin/index.php');
            } else {
                header('Location: ' . BASE . 'dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="card">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your account</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" data-validate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required
                           value="<?php echo htmlspecialchars($email ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="<?php echo BASE; ?>register.php">Register here</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
