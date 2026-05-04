<?php
$pageTitle = 'My Cart';
require_once 'includes/header.php';
$items = getCartItems($conn);
$subtotal = array_sum(array_column($items, 'line_total'));
$shipping = $subtotal >= 2000 ? 0 : 150;
$total = $subtotal + $shipping;
?>

<div class="container">
    <nav class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">My Cart</span>
    </nav>

    <?php if (empty($items)): ?>
    <div class="cart-empty">
        <div class="cart-empty-icon">🛍️</div>
        <h2>Your Beauty Bag is Empty</h2>
        <p>Looks like you haven't added anything yet. Explore our collection!</p>
        <a href="shop.php" class="btn btn-primary">Start Shopping ✨</a>
    </div>
    <?php else: ?>

    <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:32px;">My Beauty Bag</h1>

    <div class="cart-layout">
        <!-- Cart Items -->
        <div>
            <div class="cart-table">
                <div class="cart-table-head">
                    <span>Product</span>
                    <span>Price</span>
                    <span>Qty</span>
                    <span>Total</span>
                </div>
                <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-info">
                        <img class="cart-item-img"
                             src="<?= htmlspecialchars($item['image_path'] ?: 'assets/images/product-placeholder.svg') ?>"
                             alt="<?= htmlspecialchars($item['name']) ?>">
                        <div>
                            <div class="cart-item-brand"><?= htmlspecialchars($item['brand_name']) ?></div>
                            <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <button class="cart-remove" data-id="<?= $item['id'] ?>" title="Remove">✕ Remove</button>
                        </div>
                    </div>
                    <div class="cart-item-price"><?= formatPrice($item['price']) ?></div>
                    <div>
                        <div class="qty-control" style="width:fit-content;">
                            <button class="qty-btn" onclick="changeCartQty(<?= $item['id'] ?>, -1)">−</button>
                            <input class="qty-input cart-qty-input" type="number" value="<?= $item['qty'] ?>" min="1" max="99" data-id="<?= $item['id'] ?>">
                            <button class="qty-btn" onclick="changeCartQty(<?= $item['id'] ?>, 1)">+</button>
                        </div>
                    </div>
                    <div class="cart-item-total"><?= formatPrice($item['line_total']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top:16px;">
                <a href="shop.php" class="btn btn-outline-rose btn-sm">← Continue Shopping</a>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="cart-summary">
            <h3 class="cart-summary-title">Order Summary</h3>
            <div class="cart-summary-row">
                <span>Subtotal</span><span><?= formatPrice($subtotal) ?></span>
            </div>
            <div class="cart-summary-row">
                <span>Shipping</span>
                <span><?= $shipping == 0 ? '<span style="color:#4caf50;font-weight:600;">FREE</span>' : formatPrice($shipping) ?></span>
            </div>
            <?php if ($shipping > 0): ?>
            <p style="font-size:.78rem;color:#6b6b6b;margin-bottom:12px;">Add <?= formatPrice(2000 - $subtotal) ?> more for free shipping!</p>
            <?php endif; ?>
            <div class="cart-summary-total">
                <span>Total</span><span><?= formatPrice($total) ?></span>
            </div>
            <a href="checkout.php" class="btn btn-primary w-full" style="margin-top:20px;justify-content:center;">
                Checkout Securely →
            </a>
            <div style="margin-top:16px;text-align:center;">
                <span style="font-size:.75rem;color:#6b6b6b;">🔒 Secure & Encrypted Checkout</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function changeCartQty(id, delta) {
    const input = document.querySelector(`.cart-qty-input[data-id="${id}"]`);
    let qty = parseInt(input.value) + delta;
    if (qty < 1) qty = 1;
    input.value = qty;
    fetch('process/update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `product_id=${id}&qty=${qty}`
    }).then(r => r.json()).then(data => {
        if (data.success) { updateCartBadge(data.count); location.reload(); }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
