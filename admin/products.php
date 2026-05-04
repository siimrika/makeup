<?php
$pageTitle = 'Manage Products';
require_once __DIR__ . '/includes/admin_header.php';

$action  = $_GET['action'] ?? 'list';
$editId  = (int)($_POST['edit_id'] ?? $_GET['id'] ?? 0);
$msg     = $_SESSION['admin_msg'] ?? '';
$msgType = $_SESSION['admin_msg_type'] ?? 'success';
unset($_SESSION['admin_msg'], $_SESSION['admin_msg_type']);

$brands  = getBrands($conn);
$subcats = $conn->query("SELECT s.*, c.name AS cat_name FROM subcategories s JOIN categories c ON s.category_id=c.id ORDER BY c.sort_order, s.sort_order")->fetch_all(MYSQLI_ASSOC);

// ── HANDLE FORM SUBMISSIONS ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['form_action'] ?? '';

    // DELETE
    if ($postAction === 'delete') {
        $delId = (int)$_POST['del_id'];
        $conn->query("DELETE FROM products WHERE id=$delId");
        $_SESSION['admin_msg']      = 'Product deleted successfully.';
        $_SESSION['admin_msg_type'] = 'success';
        header('Location: products.php'); exit;
    }

    // ADD or EDIT
    $name           = trim($_POST['name'] ?? '');
    $subcat_id      = (int)($_POST['subcategory_id'] ?? 0);
    $brand_id       = (int)($_POST['brand_id'] ?? 0);
    $price          = (float)($_POST['price'] ?? 0);
    $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $stock          = (int)($_POST['stock'] ?? 50);
    $description    = trim($_POST['description'] ?? '');
    $is_featured    = isset($_POST['is_featured'])   ? 1 : 0;
    $is_new         = isset($_POST['is_new'])         ? 1 : 0;
    $is_bestseller  = isset($_POST['is_bestseller'])  ? 1 : 0;

    $currentProduct = null;
    if ($postAction === 'edit' && $editId) {
        $currentProduct = $conn->query("SELECT image_path FROM products WHERE id=$editId LIMIT 1")->fetch_assoc();
    }
    $existingImage = trim($currentProduct['image_path'] ?? $_POST['existing_image'] ?? '');

    if (!$name || !$subcat_id || !$brand_id || $price <= 0) {
        $_SESSION['admin_msg']      = 'Please fill in all required fields.';
        $_SESSION['admin_msg_type'] = 'error';
        header("Location: products.php?action=$postAction" . ($editId ? "&id=$editId" : '')); exit;
    }

    // Auto-generate slug from product name
    $baseSlug = strtolower(preg_replace('/[^a-z0-9]+/','-', $name));
    $slug = $baseSlug;
    $suffix = 1;
    while (true) {
        $query = "SELECT id FROM products WHERE slug='{$slug}'";
        if ($editId) {
            $query .= " AND id<>$editId";
        }
        $exists = $conn->query($query)->fetch_row();
        if (!$exists) {
            break;
        }
        $slug = $baseSlug . '-' . $suffix++;
    }

    // Handle image upload
    $image_path = $existingImage ?: 'assets/images/product-placeholder.svg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the server limit.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the form limit.',
                UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
            ];
            $message = $errorMessages[$_FILES['image']['error']] ?? 'Image upload error. Please try again.';
            $_SESSION['admin_msg']      = $message;
            $_SESSION['admin_msg_type'] = 'error';
            header("Location: products.php?action=$postAction" . ($editId ? "&id=$editId" : '')); exit;
        }

        if (!is_uploaded_file($_FILES['image']['tmp_name'])) {
            $_SESSION['admin_msg']      = 'Uploaded file is invalid. Please try again.';
            $_SESSION['admin_msg_type'] = 'error';
            header("Location: products.php?action=$postAction" . ($editId ? "&id=$editId" : '')); exit;
        }

        $uploadDir = __DIR__ . '/../assets/images/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext      = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','webp','gif','svg'];
        if (!in_array($ext, $allowed)) {
            $_SESSION['admin_msg']      = 'Please upload a valid image file (JPG, PNG, WebP, GIF, SVG).';
            $_SESSION['admin_msg_type'] = 'error';
            header("Location: products.php?action=$postAction" . ($editId ? "&id=$editId" : '')); exit;
        }
        if ($_FILES['image']['size'] > 5*1024*1024) {
            $_SESSION['admin_msg']      = 'Image file is too large. Max size is 5MB.';
            $_SESSION['admin_msg_type'] = 'error';
            header("Location: products.php?action=$postAction" . ($editId ? "&id=$editId" : '')); exit;
        }

        $filename = 'product_' . time() . '_' . uniqid() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $_SESSION['admin_msg']      = 'Failed to save uploaded image. Please check folder permissions.';
            $_SESSION['admin_msg_type'] = 'error';
            header("Location: products.php?action=$postAction" . ($editId ? "&id=$editId" : '')); exit;
        }

        if ($postAction === 'edit' && $existingImage && strpos($existingImage, 'assets/images/products/') === 0) {
            $oldFile = __DIR__ . '/../' . $existingImage;
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $image_path = 'assets/images/products/' . $filename;
    }

    if ($postAction === 'add') {
        $stmt = $conn->prepare("INSERT INTO products (subcategory_id,brand_id,name,slug,description,price,original_price,stock,image_path,is_featured,is_new,is_bestseller) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iisssddisiii', $subcat_id,$brand_id,$name,$slug,$description,$price,$original_price,$stock,$image_path,$is_featured,$is_new,$is_bestseller);
        if ($stmt->execute()) {
            $_SESSION['admin_msg'] = '✅ Product added successfully!';
        } else {
            $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
            $_SESSION['admin_msg_type'] = 'error';
        }
        header('Location: products.php'); exit;
    }

    if ($postAction === 'edit') {
        $stmt = $conn->prepare("UPDATE products SET subcategory_id=?,brand_id=?,name=?,slug=?,description=?,price=?,original_price=?,stock=?,image_path=?,is_featured=?,is_new=?,is_bestseller=? WHERE id=?");
        $stmt->bind_param('iisssddisiiii', $subcat_id,$brand_id,$name,$slug,$description,$price,$original_price,$stock,$image_path,$is_featured,$is_new,$is_bestseller,$editId);
        if ($stmt->execute()) {
            $_SESSION['admin_msg'] = '✅ Product updated successfully!';
        } else {
            $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
            $_SESSION['admin_msg_type'] = 'error';
        }
        header('Location: products.php'); exit;
    }
}

// ── LOAD EDIT PRODUCT ─────────────────────────────────────────
$editProduct = null;
if ($action === 'edit' && $editId) {
    $editProduct = $conn->query("SELECT * FROM products WHERE id=$editId LIMIT 1")->fetch_assoc();
    if (!$editProduct) { header('Location: products.php'); exit; }
    $pageTitle = 'Edit Product';
}
if ($action === 'add') $pageTitle = 'Add Product';

// ── SEARCH / FILTER ───────────────────────────────────────────
$search  = $conn->real_escape_string($_GET['search'] ?? '');
$fBrand  = (int)($_GET['brand'] ?? 0);
$where   = '1=1';
if ($search) $where .= " AND (p.name LIKE '%$search%' OR b.name LIKE '%$search%')";
if ($fBrand) $where .= " AND p.brand_id=$fBrand";
$products = $conn->query("SELECT p.*, b.name AS brand_name, s.name AS subcat_name FROM products p JOIN brands b ON p.brand_id=b.id JOIN subcategories s ON p.subcategory_id=s.id WHERE $where ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?>
<div class="admin-alert admin-alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ══════════════════════════════════════════ ADD / EDIT FORM -->
<div class="admin-form-card">
    <h2 class="admin-form-title">
        <?= $action === 'add' ? '➕ Add New Product' : '✏️ Edit Product' ?>
    </h2>
    <form method="POST" enctype="multipart/form-data" action="products.php?action=<?= $action ?><?= $editId ? '&id=' . $editId : '' ?>">
        <input type="hidden" name="form_action" value="<?= $action ?>">
        <?php if ($action === 'edit'): ?>
        <input type="hidden" name="edit_id" value="<?= $editId ?>">
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProduct['image_path'] ?? '') ?>">
        <?php endif; ?>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Product Name *</label>
                <input type="text" name="name" class="admin-input" required
                       value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>"
                       oninput="autoSlug(this.value)"
                       placeholder="e.g. Maybelline Sky High Mascara">
            </div>
        </div>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Brand *</label>
                <select name="brand_id" class="admin-select" required>
                    <option value="">— Select Brand —</option>
                    <?php foreach ($brands as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($editProduct['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Subcategory *</label>
                <select name="subcategory_id" class="admin-select" required>
                    <option value="">— Select Subcategory —</option>
                    <?php
                    $lastCat = '';
                    foreach ($subcats as $s):
                        if ($s['cat_name'] !== $lastCat) {
                            if ($lastCat) echo '</optgroup>';
                            echo '<optgroup label="' . htmlspecialchars($s['cat_name']) . '">';
                            $lastCat = $s['cat_name'];
                        }
                    ?>
                    <option value="<?= $s['id'] ?>" <?= ($editProduct['subcategory_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['name']) ?>
                    </option>
                    <?php endforeach; if ($lastCat) echo '</optgroup>'; ?>
                </select>
            </div>
        </div>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Price (Rs.) *</label>
                <input type="number" name="price" class="admin-input" required step="0.01" min="1"
                       value="<?= $editProduct['price'] ?? '' ?>" placeholder="999.00">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Original Price (Rs.) <span style="font-weight:400;color:#6b6b6b;">— for discount badge</span></label>
                <input type="number" name="original_price" class="admin-input" step="0.01" min="0"
                       value="<?= $editProduct['original_price'] ?? '' ?>" placeholder="Leave blank if no discount">
            </div>
        </div>

        <div class="admin-form-group">
            <label class="admin-label">Stock Quantity</label>
            <input type="number" name="stock" class="admin-input" min="0"
                   value="<?= $editProduct['stock'] ?? 50 ?>" style="max-width:200px;">
        </div>

        <div class="admin-form-group">
            <label class="admin-label">Description</label>
            <textarea name="description" class="admin-textarea" rows="4" placeholder="Product description..."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
        </div>

        <div class="admin-form-group">
            <label class="admin-label">Product Image</label>
            <?php if (!empty($editProduct['image_path'])): ?>
            <div style="margin-bottom:10px;">
                <img src="<?= BASE_URL . htmlspecialchars($editProduct['image_path']) ?>" class="current-img" alt="Current">
                <span class="form-hint" style="display:block;margin-top:4px;">Current image. Upload new to replace.</span>
            </div>
            <?php endif; ?>
            <input type="file" name="image" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml">
            <span class="form-hint">JPG, PNG, WebP, SVG — max 5MB</span>
        </div>

        <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:24px;">
            <label class="admin-check-label">
                <input type="checkbox" name="is_featured" value="1" <?= !empty($editProduct['is_featured']) ? 'checked' : '' ?>>
                ⭐ Featured Product
            </label>
            <label class="admin-check-label">
                <input type="checkbox" name="is_new" value="1" <?= !empty($editProduct['is_new']) ? 'checked' : '' ?>>
                🆕 New Arrival
            </label>
            <label class="admin-check-label">
                <input type="checkbox" name="is_bestseller" value="1" <?= !empty($editProduct['is_bestseller']) ? 'checked' : '' ?>>
                🏆 Bestseller
            </label>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <?= $action === 'add' ? '➕ Add Product' : '💾 Save Changes' ?>
            </button>
            <a href="products.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════════ PRODUCT LIST -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <a href="products.php?action=add" class="btn-admin btn-admin-primary">➕ Add New Product</a>
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" class="admin-search" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <select name="brand" class="admin-select" style="width:160px;" onchange="this.form.submit()">
            <option value="">All Brands</option>
            <?php foreach ($brands as $b): ?>
            <option value="<?= $b['id'] ?>" <?= $fBrand == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-admin btn-admin-outline">Filter</button>
        <?php if ($search || $fBrand): ?>
        <a href="products.php" class="btn-admin btn-admin-danger btn-admin-sm">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-table-wrap">
    <div class="table-header">
        <h3>Products <span style="font-size:.85rem;color:#6b6b6b;font-weight:400;">(<?= count($products) ?> total)</span></h3>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>Image</th>
            <th>Product Name</th>
            <th>Brand</th>
            <th>Subcategory</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Tags</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
            <td>
                <img class="product-thumb"
                     src="<?= BASE_URL . htmlspecialchars($p['image_path'] ?: 'assets/images/product-placeholder.svg') ?>"
                     alt="">
            </td>
            <td>
                <strong style="font-size:.9rem;"><?= htmlspecialchars($p['name']) ?></strong>

            </td>
            <td style="font-size:.85rem;"><?= htmlspecialchars($p['brand_name']) ?></td>
            <td style="font-size:.85rem;"><?= htmlspecialchars($p['subcat_name']) ?></td>
            <td>
                <strong style="color:#c2185b;">Rs. <?= number_format($p['price'],2) ?></strong>
                <?php if ($p['original_price']): ?>
                <div style="font-size:.75rem;color:#6b6b6b;text-decoration:line-through;">Rs. <?= number_format($p['original_price'],2) ?></div>
                <?php endif; ?>
            </td>
            <td class="<?= $p['stock'] < 10 ? 'stock-low' : 'stock-ok' ?>"><?= $p['stock'] ?></td>
            <td style="font-size:.75rem;">
                <?= $p['is_featured']   ? '<span style="background:#fff3e0;color:#e65100;padding:2px 6px;border-radius:4px;margin:2px;display:inline-block;">⭐ Featured</span>' : '' ?>
                <?= $p['is_new']        ? '<span style="background:#e3f2fd;color:#1565c0;padding:2px 6px;border-radius:4px;margin:2px;display:inline-block;">🆕 New</span>' : '' ?>
                <?= $p['is_bestseller'] ? '<span style="background:#e8f5e9;color:#2e7d32;padding:2px 6px;border-radius:4px;margin:2px;display:inline-block;">🏆 Best</span>' : '' ?>
            </td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="products.php?action=edit&id=<?= $p['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">✏️ Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($p['name'])) ?>?')">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="del_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm">🗑️</button>
                    </form>
                    <a href="<?= BASE_URL ?>product.php?slug=<?= htmlspecialchars($p['slug']) ?>" target="_blank" class="btn-admin btn-admin-success btn-admin-sm">👁️</a>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:#6b6b6b;">No products found. <a href="products.php?action=add" style="color:#c2185b;">Add one!</a></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

</div></div></body></html>
