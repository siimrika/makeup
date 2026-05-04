<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Use absolute path from document root
$rootPath = dirname(__DIR__, 2); // Go up two levels from admin/includes to root
require_once $rootPath . '/includes/db.php';
require_once $rootPath . '/includes/functions.php';
if (!isLoggedIn() || !isAdmin()) {
    redirect(BASE_URL . 'login.php');
}
$adminUser = $_SESSION['user_name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Admin' : 'Makeup Studio Admin' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">

<!-- Sidebar -->
<aside class="admin-sidebar">
    <div class="admin-sidebar-logo">
        <a href="<?= BASE_URL ?>index.php" class="logo" style="font-size:1.1rem;">
            <span class="logo-ms" style="width:32px;height:32px;font-size:1rem;">M</span>
            <span class="logo-text">Studio <em>Admin</em></span>
        </a>
    </div>
    <nav class="admin-nav">
        <a href="<?= BASE_URL ?>admin/index.php" class="<?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
            📊 Dashboard
        </a>
        <a href="<?= BASE_URL ?>admin/products.php" class="<?= basename($_SERVER['PHP_SELF'])=='products.php'?'active':'' ?>">
            💄 Products
        </a>
        <a href="<?= BASE_URL ?>admin/products.php?action=add" class="<?= (basename($_SERVER['PHP_SELF'])=='products.php'&&($_GET['action']??'')=='add')?'active':'' ?>" style="padding-left:36px;font-size:.82rem;">
            ＋ Add Product
        </a>
        <a href="<?= BASE_URL ?>admin/brands.php" class="<?= basename($_SERVER['PHP_SELF'])=='brands.php'?'active':'' ?>">
            🏷️ Brands
        </a>
        <a href="<?= BASE_URL ?>admin/categories.php" class="<?= basename($_SERVER['PHP_SELF'])=='categories.php'?'active':'' ?>">
            📂 Categories
        </a>
        <a href="<?= BASE_URL ?>admin/users.php" class="<?= basename($_SERVER['PHP_SELF'])=='users.php'?'active':'' ?>">
            👥 Users
        </a>
        <a href="<?= BASE_URL ?>admin/settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php'?'active':'' ?>">
            ⚙️ Settings
        </a>
        <a href="<?= BASE_URL ?>process/auth.php?action=logout" style="margin-top:auto;color:rgba(255,255,255,.5);">
            🚪 Logout
        </a>
    </nav>
</aside>

<!-- Main -->
<div class="admin-main">
<div class="admin-topbar">
    <div style="font-family:'Playfair Display',serif;font-size:1.1rem;">
        <?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard' ?>
    </div>
    <div style="font-size:.85rem;color:#6b6b6b;">
        Welcome, <strong><?= htmlspecialchars($adminUser) ?></strong>
    </div>
</div>
<div class="admin-content">
