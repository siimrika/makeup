<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
if (isLoggedIn()) { redirect(BASE_URL . 'account.php'); }
$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="index.php" class="logo" style="justify-content:center;">
                <span class="logo-ms">M</span><span class="logo-text">akeup <em>Studio</em></span>
            </a>
        </div>
        <h1 class="auth-title">Welcome Back 💄</h1>
        <p class="auth-subtitle">Sign in to your beauty account</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form action="process/auth.php" method="POST">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" required placeholder="your@email.com" autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;margin-top:8px;">
                Sign In ✨
            </button>
        </form>

        <div class="auth-divider"><span>or</span></div>
        <div class="auth-link">
            Don't have an account? <a href="register.php">Create one →</a>
        </div>
        <div class="auth-link" style="margin-top:8px;">
            <a href="index.php" style="color:#6b6b6b;">← Continue as Guest</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
