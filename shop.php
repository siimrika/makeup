<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$cat     = sanitize($_GET['cat']    ?? '');
$sub     = sanitize($_GET['sub']    ?? '');
$brand   = (int)($_GET['brand']     ?? 0);
$sort    = sanitize($_GET['sort']   ?? 'newest');
$search  = sanitize($_GET['search'] ?? '');

// Build products query
$where = '1=1';
if ($cat)    $where .= " AND c.slug = '" . $conn->real_escape_string($cat) . "'";
if ($sub)    $where .= " AND s.slug = '" . $conn->real_escape_string($sub) . "'";
if ($brand)  $where .= " AND p.brand_id = $brand";
if ($search) $where .= " AND (p.name LIKE '%" . $conn->real_escape_string($search) . "%' OR b.name LIKE '%" . $conn->real_escape_string($search) . "%')";

$orderMap = ['price_asc'=>'p.price ASC','price_desc'=>'p.price DESC','name'=>'p.name ASC','newest'=>'p.created_at DESC','featured'=>'p.is_featured DESC, p.created_at DESC'];
$order = $orderMap[$sort] ?? 'p.created_at DESC';

$res = $conn->query("SELECT p.*, b.name AS brand_name, b.slug AS brand_slug,
    s.name AS subcat_name, s.slug AS subcat_slug,
    c.name AS cat_name, c.slug AS cat_slug
    FROM products p
    JOIN brands b ON p.brand_id = b.id
    JOIN subcategories s ON p.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE $where ORDER BY $order");
$products = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

$allBrands = getBrands($conn);
$allSubcats = getSubcategories($conn, $cat ?: null);

// Page title
$pageTitle = $cat ? ucfirst($cat) . ' Makeup' : ($search ? 'Search: '.$search : 'All Products');
if ($sub) $pageTitle = ucwords(str_replace('-',' ',$sub));

require_once 'includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p><?= count($products) ?> products found</p>
    </div>
</div>

<div class="container" style="padding-top:40px;padding-bottom:80px;">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="index.php">Home</a>
        <span class="breadcrumb-sep">›</span>
        <?php if ($cat): ?>
        <a href="shop.php?cat=<?= $cat ?>"><?= htmlspecialchars(ucfirst($cat)) ?></a>
        <?php if ($sub): ?><span class="breadcrumb-sep">›</span><span class="breadcrumb-current"><?= htmlspecialchars(ucwords(str_replace('-',' ',$sub))) ?></span><?php endif; ?>
        <?php else: ?>
        <span class="breadcrumb-current">All Products</span>
        <?php endif; ?>
    </nav>

    <!-- Search bar -->
    <form method="GET" style="margin-bottom:24px;display:flex;gap:10px;">
        <?php if ($cat): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>"><?php endif; ?>
        <input type="text" name="search" class="form-input" placeholder="Search products or brands..." value="<?= htmlspecialchars($search) ?>" style="max-width:400px;">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <?php if ($search || $cat || $sub || $brand): ?>
        <a href="shop.php" class="btn btn-outline-rose btn-sm">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Subcategory tabs -->
    <?php if ($cat && !empty($allSubcats)): ?>
    <div class="subcats-tabs">
        <a href="shop.php?cat=<?= $cat ?><?= $brand ? '&brand='.$brand : '' ?>" class="subcat-tab <?= !$sub ? 'active' : '' ?>">All <?= ucfirst($cat) ?></a>
        <?php foreach ($allSubcats as $s): ?>
        <a href="shop.php?cat=<?= $cat ?>&sub=<?= $s['slug'] ?><?= $brand ? '&brand='.$brand : '' ?>" class="subcat-tab <?= $sub === $s['slug'] ? 'active' : '' ?>">
            <?= htmlspecialchars($s['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="shop-layout">
        <!-- SIDEBAR -->
        <aside class="shop-sidebar">
            <h3 class="sidebar-title">Filter</h3>
            <form id="filter-form" method="GET">
                <?php if ($cat): ?><input type="hidden" name="cat" value="<?= $cat ?>"><?php endif; ?>
                <?php if ($sub): ?><input type="hidden" name="sub" value="<?= $sub ?>"><?php endif; ?>
                <?php if ($search): ?><input type="hidden" name="search" value="<?= $search ?>"><?php endif; ?>

                <div class="sidebar-section">
                    <h5>Brand</h5>
                    <?php foreach ($allBrands as $b): ?>
                    <label class="filter-check">
                        <input type="radio" name="brand" value="<?= $b['id'] ?>" <?= $brand == $b['id'] ? 'checked' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </label>
                    <?php endforeach; ?>
                    <?php if ($brand): ?>
                    <a href="shop.php?cat=<?= $cat ?><?= $sub ? '&sub='.$sub : '' ?>" style="font-size:.8rem;color:#c2185b;display:block;margin-top:6px;">✕ Clear brand</a>
                    <?php endif; ?>
                </div>

                <div class="sidebar-section">
                    <h5>Sort By</h5>
                    <select name="sort" class="sort-select" style="width:100%;" onchange="this.form.submit()">
                        <option value="newest"   <?= $sort==='newest'    ? 'selected' : '' ?>>Newest First</option>
                        <option value="featured" <?= $sort==='featured'  ? 'selected' : '' ?>>Featured</option>
                        <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected' : '' ?>>Price: Low → High</option>
                        <option value="price_desc" <?= $sort==='price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
                        <option value="name"     <?= $sort==='name'      ? 'selected' : '' ?>>Name A–Z</option>
                    </select>
                </div>
            </form>
        </aside>

        <!-- PRODUCTS -->
        <div>
            <div class="shop-header">
                <h2 class="shop-title"><?= htmlspecialchars($pageTitle) ?></h2>
                <span class="shop-count"><?= count($products) ?> products</span>
            </div>

            <div class="products-grid">
                <?php if (empty($products)): ?>
                <div class="no-products">
                    <h3>No Products Found</h3>
                    <p>Try a different filter or <a href="shop.php" style="color:#c2185b;">browse all</a>.</p>
                </div>
                <?php else: ?>
                <?php foreach ($products as $p): echo productCard($p); endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
