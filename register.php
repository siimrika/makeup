<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
if (isLoggedIn()) { redirect(BASE_URL . 'account.php'); }
$error = $_SESSION['auth_error'] ?? '';
unset($_SESSION['auth_error']);
$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="index.php" class="logo" style="justify-content:center;">
                <span class="logo-ms">M</span><span class="logo-text">akeup <em>Studio</em></span>
            </a>
        </div>
        <h1 class="auth-title">Join the Studio ✨</h1>
        <p class="auth-subtitle">Create your beauty account today</p>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form action="process/auth.php" method="POST">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" required placeholder="Your name" autofocus>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" required placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-input" required placeholder="Min. 6 characters">
                <span class="form-hint">At least 6 characters</span>
            </div>
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required placeholder="Repeat password">
            </div>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;margin-top:8px;">
                Create Account 💄
            </button>
        </form>

        <div class="auth-divider"><span>or</span></div>
        <div class="auth-link">
            Already have an account? <a href="login.php">Sign in →</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
