<?php
$pageTitle = 'Manage Brands';
require_once __DIR__ . '/includes/admin_header.php';

$action  = $_GET['action'] ?? 'list';
$editId  = (int)($_GET['id'] ?? 0);
$msg     = $_SESSION['admin_msg'] ?? '';
$msgType = $_SESSION['admin_msg_type'] ?? 'success';
unset($_SESSION['admin_msg'], $_SESSION['admin_msg_type']);

// ── HANDLE FORM SUBMISSIONS ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['form_action'] ?? '';

    // DELETE
    if ($postAction === 'delete') {
        $delId = (int)$_POST['del_id'];
        $conn->query("DELETE FROM brands WHERE id=$delId");
        $_SESSION['admin_msg']      = 'Brand deleted successfully.';
        $_SESSION['admin_msg_type'] = 'success';
        header('Location: brands.php'); exit;
    }

    // ADD or EDIT
    $name        = trim($_POST['name'] ?? '');
    $slug        = trim($_POST['slug'] ?? '');
    $tagline     = trim($_POST['tagline'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $color       = trim($_POST['color'] ?? '#c2185b');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);

    if (!$name || !$slug) {
        $_SESSION['admin_msg']      = 'Please fill in all required fields.';
        $_SESSION['admin_msg_type'] = 'error';
        header("Location: brands.php?action=$postAction" . ($editId ? "&id=$editId" : '')); exit;
    }

    // Auto-generate slug from name if empty
    if (!$slug) $slug = strtolower(preg_replace('/[^a-z0-9]+/','-', $name));

    if ($postAction === 'add') {
        $stmt = $conn->prepare("INSERT INTO brands (name,slug,tagline,description,color,sort_order) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param('sssssi', $name,$slug,$tagline,$description,$color,$sort_order);
        if ($stmt->execute()) {
            $_SESSION['admin_msg'] = '✅ Brand added successfully!';
        } else {
            $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
            $_SESSION['admin_msg_type'] = 'error';
        }
        header('Location: brands.php'); exit;
    }

    if ($postAction === 'edit') {
        $stmt = $conn->prepare("UPDATE brands SET name=?,slug=?,tagline=?,description=?,color=?,sort_order=? WHERE id=?");
        $stmt->bind_param('sssssii', $name,$slug,$tagline,$description,$color,$sort_order,$editId);
        if ($stmt->execute()) {
            $_SESSION['admin_msg'] = '✅ Brand updated successfully!';
        } else {
            $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
            $_SESSION['admin_msg_type'] = 'error';
        }
        header('Location: brands.php'); exit;
    }
}

// ── LOAD EDIT BRAND ─────────────────────────────────────────
$editBrand = null;
if ($action === 'edit' && $editId) {
    $editBrand = $conn->query("SELECT * FROM brands WHERE id=$editId LIMIT 1")->fetch_assoc();
    if (!$editBrand) { header('Location: brands.php'); exit; }
    $pageTitle = 'Edit Brand';
}
if ($action === 'add') $pageTitle = 'Add Brand';

// ── SEARCH / FILTER ───────────────────────────────────────────
$search = $conn->real_escape_string($_GET['search'] ?? '');
$where  = '1=1';
if ($search) $where .= " AND (name LIKE '%$search%' OR tagline LIKE '%$search%')";
$brands = $conn->query("SELECT * FROM brands WHERE $where ORDER BY sort_order, name")->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?>
<div class="admin-alert admin-alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ══════════════════════════════════════════ ADD / EDIT FORM -->
<div class="admin-form-card">
    <h2 class="admin-form-title">
        <?= $action === 'add' ? '➕ Add New Brand' : '✏️ Edit Brand' ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="form_action" value="<?= $action ?>">

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Brand Name *</label>
                <input type="text" name="name" class="admin-input" required
                       value="<?= htmlspecialchars($editBrand['name'] ?? '') ?>"
                       placeholder="e.g. Maybelline">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Slug * <span style="font-weight:400;color:#6b6b6b;">(URL key)</span></label>
                <input type="text" name="slug" id="slug-field" class="admin-input" required
                       value="<?= htmlspecialchars($editBrand['slug'] ?? '') ?>"
                       placeholder="maybelline">
                <span class="form-hint">Lowercase letters, numbers, hyphens only</span>
            </div>
        </div>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Tagline</label>
                <input type="text" name="tagline" class="admin-input"
                       value="<?= htmlspecialchars($editBrand['tagline'] ?? '') ?>"
                       placeholder="e.g. Maybe She's Born With It">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Brand Color</label>
                <input type="color" name="color" class="admin-input"
                       value="<?= htmlspecialchars($editBrand['color'] ?? '#c2185b') ?>"
                       style="width:80px;">
            </div>
        </div>

        <div class="admin-form-group">
            <label class="admin-label">Description</label>
            <textarea name="description" class="admin-textarea" rows="3" placeholder="Brand description..."><?= htmlspecialchars($editBrand['description'] ?? '') ?></textarea>
        </div>

        <div class="admin-form-group">
            <label class="admin-label">Sort Order</label>
            <input type="number" name="sort_order" class="admin-input" min="0"
                   value="<?= $editBrand['sort_order'] ?? 0 ?>" style="max-width:120px;">
            <span class="form-hint">Lower numbers appear first</span>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <?= $action === 'add' ? '➕ Add Brand' : '💾 Save Changes' ?>
            </button>
            <a href="brands.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════════ BRANDS LIST -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <a href="brands.php?action=add" class="btn-admin btn-admin-primary">➕ Add New Brand</a>
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" class="admin-search" placeholder="Search brands..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit" class="btn-admin btn-admin-outline">Search</button>
        <?php if ($search): ?>
        <a href="brands.php" class="btn-admin btn-admin-danger btn-admin-sm">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-table-wrap">
    <div class="table-header">
        <h3>Brands <span style="font-size:.85rem;color:#6b6b6b;font-weight:400;">(<?= count($brands) ?> total)</span></h3>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>Name</th>
            <th>Tagline</th>
            <th>Color</th>
            <th>Sort Order</th>
            <th>Products</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($brands as $b):
            $productCount = $conn->query("SELECT COUNT(*) FROM products WHERE brand_id={$b['id']}")->fetch_row()[0];
        ?>
        <tr>
            <td>
                <strong style="font-size:.9rem;"><?= htmlspecialchars($b['name']) ?></strong>
                <div style="font-size:.75rem;color:#6b6b6b;"><?= htmlspecialchars($b['slug']) ?></div>
            </td>
            <td style="font-size:.85rem;"><?= htmlspecialchars($b['tagline'] ?: '—') ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:20px;height:20px;border-radius:50%;background:<?= htmlspecialchars($b['color']) ?>;border:1px solid #ddd;"></div>
                    <span style="font-family:monospace;font-size:.8rem;"><?= htmlspecialchars($b['color']) ?></span>
                </div>
            </td>
            <td style="text-align:center;"><?= $b['sort_order'] ?></td>
            <td style="text-align:center;">
                <span style="background:#e3f2fd;color:#1565c0;padding:2px 8px;border-radius:12px;font-size:.8rem;">
                    <?= $productCount ?> products
                </span>
            </td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="brands.php?action=edit&id=<?= $b['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">✏️ Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($b['name'])) ?>? This will affect <?= $productCount ?> products.')">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="del_id" value="<?= $b['id'] ?>">
                        <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm">🗑️</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($brands)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:#6b6b6b;">No brands found. <a href="brands.php?action=add" style="color:#c2185b;">Add one!</a></td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<script>
function autoSlug(name) {
    const slugField = document.getElementById('slug-field');
    if (!slugField || slugField.dataset.manual) return;
    slugField.value = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}
document.getElementById('slug-field')?.addEventListener('input', function() {
    this.dataset.manual = true;
});
document.querySelector('input[name="name"]')?.addEventListener('input', function() {
    autoSlug(this.value);
});
</script>

</div></div></body></html>