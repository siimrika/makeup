<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_REQUEST['action'] ?? '';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if (!$email || !$pass) {
        $_SESSION['auth_error'] = 'Please fill in all fields.';
        redirect(BASE_URL . 'login.php');
    }
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];
        if ($user['role'] === 'admin') redirect(BASE_URL . 'admin/index.php');
        redirect(BASE_URL . 'account.php');
    } else {
        $_SESSION['auth_error'] = 'Invalid email or password.';
        redirect(BASE_URL . 'login.php');
    }
}

if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $conf  = $_POST['confirm_password'] ?? '';
    if (!$name || !$email || !$pass) {
        $_SESSION['auth_error'] = 'Please fill in all fields.';
        redirect(BASE_URL . 'register.php');
    }
    if (strlen($pass) < 6) {
        $_SESSION['auth_error'] = 'Password must be at least 6 characters.';
        redirect(BASE_URL . 'register.php');
    }
    if ($pass !== $conf) {
        $_SESSION['auth_error'] = 'Passwords do not match.';
        redirect(BASE_URL . 'register.php');
    }
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['auth_error'] = 'Email already registered. Please login.';
        redirect(BASE_URL . 'register.php');
    }
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?,?,?)");
    $stmt->bind_param('sss', $name, $email, $hash);
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $_SESSION['user_id']    = $id;
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role']  = 'customer';
        redirect(BASE_URL . 'account.php');
    } else {
        $_SESSION['auth_error'] = 'Registration failed. Try again.';
        redirect(BASE_URL . 'register.php');
    }
}

if ($action === 'logout') {
    session_destroy();
    redirect(BASE_URL . 'index.php');
}

redirect(BASE_URL . 'index.php');
