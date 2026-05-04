<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
$_subcatsGrouped = getAllSubcatsGrouped($conn);
$_cartCount = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Makeup Studio — Premium cosmetics from Maybelline, MARS, NARS & Huda Beauty. Shop Eyes, Face & Lips.">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Makeup Studio' : 'Makeup Studio | Premium Beauty Destination' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>

<!-- Announcement Bar -->
<div class="announcement-bar">
    <p>✨ Free Shipping on orders above Rs. 2000 &nbsp;|&nbsp; 💄 New arrivals from Huda Beauty &amp; NARS &nbsp;|&nbsp; 🎀 Shop the latest trends</p>
</div>

<!-- Header -->
<header class="site-header" id="site-header">
    <div class="header-inner container">
        <!-- Logo -->
        <a href="<?= BASE_URL ?>index.php" class="logo">
            <span class="logo-ms">M</span><span class="logo-text">akeup <em>Studio</em></span>
        </a>

        <!-- Nav -->
        <nav class="main-nav" id="main-nav">
            <ul class="nav-list">
                <li><a href="<?= BASE_URL ?>index.php" class="nav-link">Home</a></li>

                <!-- Eyes -->
                <li class="has-dropdown">
                    <a href="<?= BASE_URL ?>shop.php?cat=eyes" class="nav-link">Eyes <span class="nav-caret">▾</span></a>
                    <div class="mega-dropdown">
                        <div class="mega-inner container">
                            <div class="mega-col">
                                <h4 class="mega-heading">👁️ Eyes</h4>
                                <ul>
                                    <?php foreach (($_subcatsGrouped['eyes'] ?? []) as $s): ?>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=eyes&sub=<?= $s['slug'] ?>"><?= htmlspecialchars($s['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="mega-col mega-brands">
                                <h4 class="mega-heading">Shop by Brand</h4>
                                <ul>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=eyes&brand=1">Maybelline</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=eyes&brand=2">MARS</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=eyes&brand=3">NARS</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=eyes&brand=4">Huda Beauty</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Face -->
                <li class="has-dropdown">
                    <a href="<?= BASE_URL ?>shop.php?cat=face" class="nav-link">Face <span class="nav-caret">▾</span></a>
                    <div class="mega-dropdown">
                        <div class="mega-inner container">
                            <div class="mega-col">
                                <h4 class="mega-heading">✨ Face</h4>
                                <ul>
                                    <?php foreach (($_subcatsGrouped['face'] ?? []) as $s): ?>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=face&sub=<?= $s['slug'] ?>"><?= htmlspecialchars($s['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="mega-col mega-brands">
                                <h4 class="mega-heading">Shop by Brand</h4>
                                <ul>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=face&brand=1">Maybelline</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=face&brand=2">MARS</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=face&brand=3">NARS</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=face&brand=4">Huda Beauty</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Lips -->
                <li class="has-dropdown">
                    <a href="<?= BASE_URL ?>shop.php?cat=lips" class="nav-link">Lips <span class="nav-caret">▾</span></a>
                    <div class="mega-dropdown">
                        <div class="mega-inner container">
                            <div class="mega-col">
                                <h4 class="mega-heading">💋 Lips</h4>
                                <ul>
                                    <?php foreach (($_subcatsGrouped['lips'] ?? []) as $s): ?>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=lips&sub=<?= $s['slug'] ?>"><?= htmlspecialchars($s['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="mega-col mega-brands">
                                <h4 class="mega-heading">Shop by Brand</h4>
                                <ul>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=lips&brand=1">Maybelline</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=lips&brand=2">MARS</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=lips&brand=3">NARS</a></li>
                                    <li><a href="<?= BASE_URL ?>shop.php?cat=lips&brand=4">Huda Beauty</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>

                <li><a href="<?= BASE_URL ?>brands.php" class="nav-link">Brands</a></li>
            </ul>
        </nav>

        <!-- Header Actions -->
        <div class="header-actions">
            <?php if (isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>account.php" class="header-icon" title="Account">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>login.php" class="header-icon" title="Login">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>cart.php" class="header-icon cart-icon" title="Cart">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span class="cart-badge" id="cart-count"><?= $_cartCount ?></span>
            </a>
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</header>

<!-- Toast Notification -->
<div id="toast" class="toast"></div>

<main>
