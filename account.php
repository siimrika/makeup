<?php
$pageTitle = 'My Account';
require_once 'includes/header.php';
if (!isLoggedIn()) { redirect(BASE_URL . 'login.php'); }

$userId = (int)$_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$userId")->fetch_assoc();
$orders = $conn->query("SELECT * FROM orders WHERE user_id=$userId ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$success = $_SESSION['account_success'] ?? '';
unset($_SESSION['account_success']);
?>

<div class="container">
    <nav class="breadcrumb"><a href="index.php">Home</a><span class="breadcrumb-sep">›</span><span class="breadcrumb-current">My Account</span></nav>

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="account-layout">
        <!-- Sidebar -->
        <div class="account-sidebar">
            <div class="account-avatar">
                <div class="account-avatar-initial"><?= mb_substr($user['name'],0,1) ?></div>
                <div style="font-family:'Playfair Display',serif;font-size:1.1rem;"><?= htmlspecialchars($user['name']) ?></div>
                <div style="font-size:.78rem;opacity:.75;margin-top:4px;"><?= htmlspecialchars($user['email']) ?></div>
            </div>
            <nav class="account-nav">
                <a href="account.php" class="active">📦 My Orders</a>
                <a href="cart.php">🛍️ My Cart</a>
                <a href="shop.php">💄 Shop</a>
                <a href="process/auth.php?action=logout" style="color:#e53935;">🚪 Logout</a>
            </nav>
        </div>

        <!-- Main -->
        <div>
            <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:24px;">My Orders</h2>

            <?php if (empty($orders)): ?>
            <div style="text-align:center;padding:60px;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(194,24,91,.08);">
                <div style="font-size:3rem;margin-bottom:16px;">📦</div>
                <h3 style="font-family:'Playfair Display',serif;margin-bottom:8px;">No Orders Yet</h3>
                <p style="color:#6b6b6b;margin-bottom:20px;">Start shopping to see your orders here!</p>
                <a href="shop.php" class="btn btn-primary">Shop Now ✨</a>
            </div>
            <?php else: ?>
            <?php foreach ($orders as $o):
                $oItems = $conn->query("SELECT oi.*, p.name AS product_name, p.image_path FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id={$o['id']}")->fetch_all(MYSQLI_ASSOC);
            ?>
            <div class="order-card">
                <div class="order-card-header">
                    <div>
                        <span style="font-size:.78rem;color:#6b6b6b;">Order #</span>
                        <strong><?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></strong>
                        <span style="font-size:.78rem;color:#6b6b6b;margin-left:12px;"><?= date('d M Y', strtotime($o['created_at'])) ?></span>
                    </div>
                    <span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span>
                </div>
                <div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
                    <?php foreach ($oItems as $oi): ?>
                    <div style="display:flex;align-items:center;gap:8px;background:#fff8f8;border-radius:8px;padding:8px 12px;">
                        <img src="<?= htmlspecialchars($oi['image_path'] ?: 'assets/images/product-placeholder.svg') ?>" style="width:36px;height:36px;object-fit:cover;border-radius:4px;" alt="">
                        <span style="font-size:.82rem;"><?= htmlspecialchars($oi['product_name']) ?> ×<?= $oi['quantity'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:.88rem;color:#6b6b6b;">
                    <span>Payment: <strong><?= htmlspecialchars(paymentMethodLabel($o['payment_method'])) ?></strong></span>
                    <span style="font-size:1rem;font-weight:700;color:#c2185b;"><?= formatPrice($o['total']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
