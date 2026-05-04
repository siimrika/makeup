<?php
$pageTitle = 'Home';
require_once 'includes/header.php';
$featured  = getFeaturedProducts($conn, 8);
$brands    = getBrands($conn);
$cats      = getCategories($conn);
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg-shapes">
        <div class="hero-shape hero-shape-1"></div>
        <div class="hero-shape hero-shape-2"></div>
        <div class="hero-shape hero-shape-3"></div>
    </div>
    <div class="container hero-inner">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <span>💄</span> Premium Beauty Destination
            </div>
            <h1 class="hero-title">
                Unleash Your<br>
                <em>Inner Glow</em>
            </h1>
            <p class="hero-subtitle">
                Discover curated cosmetics from the world's most iconic brands. From bold eyes to the perfect pout — your beauty story begins here.
            </p>
            <div class="hero-ctas">
                <a href="shop.php" class="btn btn-primary">Shop Now ✨</a>
                <a href="brands.php" class="btn btn-outline">Explore Brands</a>
            </div>
            <div class="hero-brands">
                <span class="hero-brand-label">As seen in:</span>
                <?php foreach ($brands as $b): ?>
                <span class="hero-brand-pill"><?= htmlspecialchars($b['name']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- BRAND STRIP -->
<div class="brands-strip">
    <div class="brands-strip-inner">
        <?php for ($i = 0; $i < 3; $i++): foreach ($brands as $b): ?>
        <div class="brand-strip-item">
            <span class="brand-strip-dot"></span>
            <span class="brand-strip-name"><?= htmlspecialchars($b['name']) ?></span>
        </div>
        <?php endforeach; endfor; ?>
    </div>
</div>

<!-- CATEGORIES -->
<section class="section-pad">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Shop by Category</h2>
            <p class="section-subtitle">Curated collections for every aspect of your beauty routine</p>
        </div>
        <div class="category-cards">
            <a href="shop.php?cat=eyes" class="category-card">
                <div class="category-card__bg cat-eyes"></div>
                <div class="category-card__overlay"></div>
                <div class="category-card__content">
                    <span class="category-card__icon">👁️</span>
                    <div class="category-card__title">Eyes</div>
                    <div class="category-card__count">Eyeliner · Eyeshadow · Mascara · Lashes</div>
                    <div class="category-card__link">Shop Eyes →</div>
                </div>
            </a>
            <a href="shop.php?cat=face" class="category-card">
                <div class="category-card__bg cat-face"></div>
                <div class="category-card__overlay"></div>
                <div class="category-card__content">
                    <span class="category-card__icon">✨</span>
                    <div class="category-card__title">Face</div>
                    <div class="category-card__count">Foundation · Blush · Contour · Powder</div>
                    <div class="category-card__link">Shop Face →</div>
                </div>
            </a>
            <a href="shop.php?cat=lips" class="category-card">
                <div class="category-card__bg cat-lips"></div>
                <div class="category-card__overlay"></div>
                <div class="category-card__content">
                    <span class="category-card__icon">💋</span>
                    <div class="category-card__title">Lips</div>
                    <div class="category-card__count">Lipstick · Gloss · Liner · Balm</div>
                    <div class="category-card__link">Shop Lips →</div>
                </div>
            </a>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<section class="section-pad bg-ivory">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Featured Picks</h2>
            <p class="section-subtitle">Editor-curated bestsellers and new arrivals</p>
        </div>
        <div class="products-grid">
            <?php foreach ($featured as $p): echo productCard($p); endforeach; ?>
            <?php if (empty($featured)): ?>
            <p class="text-center" style="grid-column:1/-1;color:#6b6b6b;padding:40px;">No featured products yet. Add products via the admin panel.</p>
            <?php endif; ?>
        </div>
        <div style="text-align:center;margin-top:48px;">
            <a href="shop.php" class="btn btn-outline-rose">View All Products →</a>
        </div>
    </div>
</section>

<!-- BRANDS SHOWCASE -->
<section class="section-pad">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Our Brands</h2>
            <p class="section-subtitle">Trusted by millions of beauty lovers worldwide</p>
        </div>
        <div class="brand-cards">
            <?php foreach ($brands as $b): ?>
            <a href="shop.php?brand=<?= $b['id'] ?>" class="brand-card brand-<?= $b['slug'] ?>">
                <div class="brand-card__initial"><?= mb_substr($b['name'],0,1) ?></div>
                <div class="brand-card__name"><?= htmlspecialchars($b['name']) ?></div>
                <div class="brand-card__tagline"><?= htmlspecialchars($b['tagline']) ?></div>
                <div class="brand-card__cta">Shop <?= htmlspecialchars($b['name']) ?> →</div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- WHY US -->
<section class="section-pad" style="background:linear-gradient(135deg,#0d0010,#3d0026); padding:80px 0;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title" style="color:#fff;">Why Makeup Studio?</h2>
            <p class="section-subtitle" style="color:rgba(255,255,255,.6);">We obsess over quality so you don't have to</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px;margin-top:48px;">
            <?php foreach ([
                ['💄','100% Authentic','Every product is sourced directly from brands.'],
                ['🚚','Fast Delivery','Orders dispatched within 24 hours.'],
                ['💳','Secure Payment','COD, UPI, Cards — all accepted safely.'],
                ['💬','Beauty Experts','Get advice from our in-house makeup artists.'],
            ] as [$icon,$title,$desc]): ?>
            <div style="text-align:center;color:#fff;">
                <div style="font-size:2.5rem;margin-bottom:16px;"><?= $icon ?></div>
                <h4 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:8px;"><?= $title ?></h4>
                <p style="font-size:.85rem;color:rgba(255,255,255,.6);line-height:1.6;"><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>