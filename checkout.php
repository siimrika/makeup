<?php
$pageTitle = 'Checkout';
require_once 'includes/header.php';
$items = getCartItems($conn);
if (empty($items)) { header('Location: cart.php'); exit; }
$subtotal = array_sum(array_column($items, 'line_total'));
$shipping = $subtotal >= 2000 ? 0 : 150;
$total = $subtotal + $shipping;
$error = $_SESSION['checkout_error'] ?? '';
$success = $_SESSION['checkout_success'] ?? '';
unset($_SESSION['checkout_error'], $_SESSION['checkout_success']);
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="index.php">Home</a><span class="breadcrumb-sep">›</span>
        <a href="cart.php">Cart</a><span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Checkout</span>
    </nav>
    <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:32px;">Checkout</h1>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="checkout-layout">
        <!-- Form -->
        <form action="process/checkout_process.php" method="POST">
            <div class="checkout-form-card" style="margin-bottom:24px;">
                <h3 class="checkout-section-title">👤 Delivery Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-input" required placeholder="Your full name"
                               value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_name'] ?? '') : '' ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input type="tel" name="phone" class="form-input" required placeholder="10-digit mobile">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="Optional — for order confirmation"
                           value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_email'] ?? '') : '' ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Address *</label>
                    <textarea name="address" class="form-input" required rows="3" placeholder="Street, area, landmark..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">City *</label>
                    <input type="text" name="city" class="form-input" required placeholder="City">
                </div>
                <div class="form-group">
                    <label class="form-label">Order Notes (optional)</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Any special instructions..."></textarea>
                </div>
            </div>

            <div class="checkout-form-card">
                <h3 class="checkout-section-title">💳 Payment Method</h3>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <span>💵</span>
                        <span class="payment-option-label">Cash on Delivery</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="khalti">
                        <span>💳</span>
                        <span class="payment-option-label">Khalti</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="esewa">
                        <span>📱</span>
                        <span class="payment-option-label">eSewa</span>
                    </label>
                </div>
                <div style="margin-top:24px;">
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;font-size:1rem;padding:16px;">
                        Place Order — <?= formatPrice($total) ?> ✨
                    </button>
                    <p style="text-align:center;font-size:.78rem;color:#6b6b6b;margin-top:12px;">🔒 Your information is safe and encrypted</p>
                </div>
            </div>
        </form>

        <!-- Order Summary -->
        <div class="cart-summary">
            <h3 class="cart-summary-title">Order Summary</h3>
            <?php foreach ($items as $item): ?>
            <div style="display:flex;gap:12px;margin-bottom:12px;padding-bottom:12px;border-bottom:1px solid #f5f5f5;">
                <img src="<?= htmlspecialchars($item['image_path'] ?: 'assets/images/product-placeholder.svg') ?>"
                     alt="" style="width:52px;height:52px;object-fit:cover;border-radius:8px;background:#fff8f8;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.82rem;font-weight:500;line-height:1.3;"><?= htmlspecialchars($item['name']) ?></div>
                    <div style="font-size:.75rem;color:#6b6b6b;">Qty: <?= $item['qty'] ?></div>
                </div>
                <div style="font-size:.88rem;font-weight:600;"><?= formatPrice($item['line_total']) ?></div>
            </div>
            <?php endforeach; ?>
            <div class="cart-summary-row"><span>Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
            <div class="cart-summary-row"><span>Shipping</span><span><?= $shipping == 0 ? '<span style="color:#4caf50;font-weight:600;">FREE</span>' : formatPrice($shipping) ?></span></div>
            <div class="cart-summary-total"><span>Total</span><span><?= formatPrice($total) ?></span></div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
