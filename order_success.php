<?php
$pageTitle = 'Order Confirmed!';
require_once 'includes/header.php';
$orderId = $_SESSION['order_success'] ?? null;
unset($_SESSION['order_success']);
?>
<div class="container" style="text-align:center;padding:100px 20px;">
    <div style="font-size:5rem;margin-bottom:24px;">🎉</div>
    <h1 style="font-family:'Playfair Display',serif;font-size:3rem;color:#c2185b;margin-bottom:12px;">Order Placed!</h1>
    <p style="color:#6b6b6b;font-size:1.1rem;margin-bottom:8px;">Thank you for shopping with Makeup Studio ✨</p>
    <?php if ($orderId): ?>
    <p style="color:#6b6b6b;margin-bottom:32px;">Your order <strong>#<?= str_pad($orderId,6,'0',STR_PAD_LEFT) ?></strong> has been received and is being processed.</p>
    <?php endif; ?>
    <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
        <a href="account.php" class="btn btn-primary">Track My Order</a>
        <a href="shop.php" class="btn btn-outline-rose">Continue Shopping</a>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
