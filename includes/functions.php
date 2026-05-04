<?php
// -------------------------------------------------------
// Helper Functions
// -------------------------------------------------------

function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

function paymentMethodLabel(string $method): string {
    $map = [
        'cod'    => 'Cash on Delivery',
        'khalti' => 'Khalti',
        'esewa'  => 'eSewa',
        'card'   => 'Credit / Debit Card',
        'upi'    => 'UPI / Digital Wallet',
    ];
    return $map[$method] ?? ucfirst($method);
}

function getCartCount() {
    if (!isset($_SESSION['cart'])) return 0;
    return array_sum(array_column($_SESSION['cart'], 'qty'));
}

function addToCart($product_id, $qty = 1) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = ['qty' => $qty];
    }
}

function removeFromCart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function updateCartQty($product_id, $qty) {
    if ($qty <= 0) { removeFromCart($product_id); return; }
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['qty'] = (int)$qty;
    }
}

function getCartItems($conn) {
    if (empty($_SESSION['cart'])) return [];
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $res = $conn->query("SELECT p.*, b.name AS brand_name FROM products p JOIN brands b ON p.brand_id=b.id WHERE p.id IN ($ids)");
    $items = [];
    while ($row = $res->fetch_assoc()) {
        $row['qty'] = $_SESSION['cart'][$row['id']]['qty'];
        $row['line_total'] = $row['price'] * $row['qty'];
        $items[] = $row;
    }
    return $items;
}

function getCartTotal($conn) {
    $items = getCartItems($conn);
    return array_sum(array_column($items, 'line_total'));
}

function getFeaturedProducts($conn, $limit = 8) {
    $res = $conn->query("SELECT p.*, b.name AS brand_name, s.name AS subcat_name, c.slug AS cat_slug
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN subcategories s ON p.subcategory_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE p.is_featured = 1
        ORDER BY p.created_at DESC LIMIT $limit");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getProductsBySubcat($conn, $slug, $brand_id = null, $sort = 'newest') {
    $where = "s.slug = '" . $conn->real_escape_string($slug) . "'";
    if ($brand_id) $where .= " AND p.brand_id = " . (int)$brand_id;
    $order = match($sort) {
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name'       => 'p.name ASC',
        default      => 'p.created_at DESC',
    };
    $res = $conn->query("SELECT p.*, b.name AS brand_name, s.name AS subcat_name, c.slug AS cat_slug
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN subcategories s ON p.subcategory_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE $where ORDER BY $order");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getProductsByCategory($conn, $cat_slug, $brand_id = null, $sort = 'newest') {
    $where = "c.slug = '" . $conn->real_escape_string($cat_slug) . "'";
    if ($brand_id) $where .= " AND p.brand_id = " . (int)$brand_id;
    $order = match($sort) {
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name'       => 'p.name ASC',
        default      => 'p.created_at DESC',
    };
    $res = $conn->query("SELECT p.*, b.name AS brand_name, s.name AS subcat_name, c.slug AS cat_slug, c.name AS cat_name
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN subcategories s ON p.subcategory_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE $where ORDER BY $order");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getCategories($conn) {
    $res = $conn->query("SELECT * FROM categories ORDER BY sort_order");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getSubcategories($conn, $cat_slug = null) {
    $where = $cat_slug ? "WHERE c.slug = '" . $conn->real_escape_string($cat_slug) . "'" : '';
    $res = $conn->query("SELECT s.*, c.slug AS cat_slug, c.name AS cat_name FROM subcategories s JOIN categories c ON s.category_id = c.id $where ORDER BY s.sort_order");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllSubcatsGrouped($conn) {
    $res = $conn->query("SELECT s.*, c.slug AS cat_slug, c.name AS cat_name, c.icon AS cat_icon FROM subcategories s JOIN categories c ON s.category_id = c.id ORDER BY c.sort_order, s.sort_order");
    $grouped = [];
    while ($row = $res->fetch_assoc()) {
        $grouped[$row['cat_slug']][] = $row;
    }
    return $grouped;
}

function getBrands($conn) {
    $res = $conn->query("SELECT * FROM brands ORDER BY sort_order");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getSingleProduct($conn, $slug) {
    $slug = $conn->real_escape_string($slug);
    $res = $conn->query("SELECT p.*, b.name AS brand_name, b.slug AS brand_slug, b.color AS brand_color,
        s.name AS subcat_name, s.slug AS subcat_slug, c.name AS cat_name, c.slug AS cat_slug
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN subcategories s ON p.subcategory_id = s.id
        JOIN categories c ON s.category_id = c.id
        WHERE p.slug = '$slug' LIMIT 1");
    return $res ? $res->fetch_assoc() : null;
}

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin()    { return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'; }

function redirect($url) { header("Location: $url"); exit; }

function sanitize($str) { return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8'); }

function badgeHtml($product) {
    $badges = [];
    if (!empty($product['is_new']))        $badges[] = '<span class="badge badge-new">New</span>';
    if (!empty($product['is_bestseller'])) $badges[] = '<span class="badge badge-best">Bestseller</span>';
    if (!empty($product['original_price']) && $product['original_price'] > $product['price']) {
        $pct = round((1 - $product['price'] / $product['original_price']) * 100);
        $badges[] = "<span class=\"badge badge-sale\">-{$pct}%</span>";
    }
    return implode('', $badges);
}

function productCard($p) {
    $badge = badgeHtml($p);
    $img   = htmlspecialchars($p['image_path'] ?? 'assets/images/product-placeholder.jpg');
    $name  = htmlspecialchars($p['name']);
    $brand = htmlspecialchars($p['brand_name']);
    $price = formatPrice($p['price']);
    $orig  = !empty($p['original_price']) ? '<span class="price-orig">' . formatPrice($p['original_price']) . '</span>' : '';
    $slug  = htmlspecialchars($p['slug']);
    return <<<HTML
    <div class="product-card" data-id="{$p['id']}">
        <a href="product.php?slug={$slug}" class="product-card__img-wrap">
            <img src="{$img}" alt="{$name}" loading="lazy">
            <div class="product-card__badges">{$badge}</div>
            <div class="product-card__actions">
                <button class="btn-cart-quick" onclick="quickAddToCart(event,{$p['id']})">🛒 Add to Bag</button>
            </div>
        </a>
        <div class="product-card__info">
            <span class="product-card__brand">{$brand}</span>
            <h3 class="product-card__name"><a href="product.php?slug={$slug}">{$name}</a></h3>
            <div class="product-card__price">{$price} {$orig}</div>
        </div>
    </div>
HTML;
}
