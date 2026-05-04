<?php
$pageTitle = 'Our Brands';
require_once 'includes/header.php';
$brands = getBrands($conn);
?>

<div class="page-hero">
    <div class="container">
        <h1>Our Brands</h1>
        <p>Curated from the world's most iconic cosmetics houses</p>
    </div>
</div>

<section class="section-pad">
    <div class="container">
        <div class="brand-cards">
            <?php foreach ($brands as $b): ?>
            <div class="brand-card brand-<?= htmlspecialchars($b['slug']) ?>">
                <div class="brand-card__initial"><?= mb_substr($b['name'],0,1) ?></div>
                <div class="brand-card__name"><?= htmlspecialchars($b['name']) ?></div>
                <div class="brand-card__tagline">"<?= htmlspecialchars($b['tagline']) ?>"</div>
                <?php if ($b['description']): ?>
                <p style="font-size:.82rem;color:rgba(255,255,255,.7);line-height:1.6;margin-top:4px;"><?= htmlspecialchars($b['description']) ?></p>
                <?php endif; ?>
                <a href="shop.php?brand=<?= $b['id'] ?>" class="brand-card__cta">Shop <?= htmlspecialchars($b['name']) ?> →</a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Brand Category Grid -->
<?php foreach ($brands as $b): ?>
<?php
$res = $conn->query("SELECT p.*, b.name AS brand_name, s.name AS subcat_name, c.slug AS cat_slug
    FROM products p JOIN brands b ON p.brand_id=b.id
    JOIN subcategories s ON p.subcategory_id=s.id
    JOIN categories c ON s.category_id=c.id
    WHERE p.brand_id={$b['id']} ORDER BY p.is_featured DESC, p.created_at DESC LIMIT 4");
$bProducts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
if (empty($bProducts)) continue;
?>
<section class="section-pad <?= $b['id'] % 2 == 0 ? 'bg-ivory' : '' ?>">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:36px;">
            <div>
                <span style="font-size:.78rem;color:#c2185b;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Featured Brand</span>
                <h2 style="font-family:'Playfair Display',serif;font-size:2rem;margin-top:4px;"><?= htmlspecialchars($b['name']) ?></h2>
                <p style="color:#6b6b6b;font-style:italic;font-size:.9rem;"><?= htmlspecialchars($b['tagline']) ?></p>
            </div>
            <a href="shop.php?brand=<?= $b['id'] ?>" class="btn btn-outline-rose">View All →</a>
        </div>
        <div class="products-grid">
            <?php foreach ($bProducts as $p): echo productCard($p); endforeach; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>

<?php require_once 'includes/footer.php'; ?>
