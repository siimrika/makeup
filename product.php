<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) { header('Location: shop.php'); exit; }

$product = getSingleProduct($conn, $slug);
if (!$product) { header('Location: shop.php'); exit; }

// Related products (same subcategory)
$subId = (int)$product['subcategory_id'];
$relRes = $conn->query("SELECT p.*, b.name AS brand_name, s.name AS subcat_name, c.slug AS cat_slug
    FROM products p JOIN brands b ON p.brand_id=b.id
    JOIN subcategories s ON p.subcategory_id=s.id
    JOIN categories c ON s.category_id=c.id
    WHERE p.subcategory_id=$subId AND p.id != {$product['id']} LIMIT 4");
$related = $relRes ? $relRes->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = $product['name'];
require_once 'includes/header.php';

$discount = (!empty($product['original_price']) && $product['original_price'] > $product['price'])
    ? round((1 - $product['price'] / $product['original_price']) * 100) : 0;
?>

<div class="container">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-sep">›</span>
        <a href="shop.php?cat=<?= htmlspecialchars($product['cat_slug']) ?>"><?= htmlspecialchars($product['cat_name']) ?></a>
        <span class="breadcrumb-sep">›</span>
        <a href="shop.php?cat=<?= htmlspecialchars($product['cat_slug']) ?>&sub=<?= htmlspecialchars($product['subcat_slug']) ?>"><?= htmlspecialchars($product['subcat_name']) ?></a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <div class="product-detail">
        <!-- Image -->
        <div>
            <div class="product-img-main">
                <img src="<?= htmlspecialchars($product['image_path'] ?: 'assets/images/product-placeholder.svg') ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div style="display:flex;gap:8px;margin-top:12px;">
                <?php foreach(badgeHtml($product) ? [badgeHtml($product)] : [] as $b): echo $b; endforeach; ?>
            </div>
        </div>

        <!-- Info -->
        <div class="product-info">
            <a href="shop.php?brand=<?= (int)$product['brand_id'] ?>" class="product-brand-badge">
                💄 <?= htmlspecialchars($product['brand_name']) ?>
            </a>
            <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

            <div class="product-price-block">
                <span class="product-price"><?= formatPrice($product['price']) ?></span>
                <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                <span class="product-price-original"><?= formatPrice($product['original_price']) ?></span>
                <span class="product-price-save">Save <?= $discount ?>%</span>
                <?php endif; ?>
            </div>

            <?php if ($product['description']): ?>
            <p class="product-description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <form id="add-cart-form">
                <div class="product-actions">
                    <div class="qty-control">
                        <button type="button" class="qty-btn" data-action="minus">−</button>
                        <input type="number" class="qty-input" id="product-qty" value="1" min="1" max="99">
                        <button type="button" class="qty-btn" data-action="plus">+</button>
                    </div>
                    <button type="button" class="btn btn-primary btn-add-cart" onclick="addToCartDetail(<?= (int)$product['id'] ?>)">
                        🛒 Add to Beauty Bag
                    </button>
                </div>
            </form>

            <div class="product-meta">
                <div class="product-meta-row">
                    <span class="product-meta-label">Brand</span>
                    <span class="product-meta-val"><a href="shop.php?brand=<?= (int)$product['brand_id'] ?>"><?= htmlspecialchars($product['brand_name']) ?></a></span>
                </div>
                <div class="product-meta-row">
                    <span class="product-meta-label">Category</span>
                    <span class="product-meta-val"><a href="shop.php?cat=<?= htmlspecialchars($product['cat_slug']) ?>"><?= htmlspecialchars($product['cat_name']) ?></a></span>
                </div>
                <div class="product-meta-row">
                    <span class="product-meta-label">Sub-category</span>
                    <span class="product-meta-val"><?= htmlspecialchars($product['subcat_name']) ?></span>
                </div>
                <div class="product-meta-row">
                    <span class="product-meta-label">Availability</span>
                    <span class="product-meta-val" style="color:<?= $product['stock'] > 0 ? '#4caf50' : '#e53935' ?>">
                        <?= $product['stock'] > 0 ? '✓ In Stock' : '✗ Out of Stock' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <div style="padding:60px 0;">
        <div class="section-header" style="margin-bottom:32px;">
            <h2 class="section-title" style="font-size:1.8rem;">You May Also Like</h2>
        </div>
        <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
            <?php foreach ($related as $p): echo productCard($p); endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function addToCartDetail(productId) {
    const qty = document.getElementById('product-qty').value || 1;
    fetch('process/add_to_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `product_id=${productId}&qty=${qty}`
    }).then(r => r.json()).then(data => {
        if (data.success) {
            showToast('✨ Added to your beauty bag!');
            updateCartBadge(data.count);
        } else { showToast(data.message || 'Error', 'error'); }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
