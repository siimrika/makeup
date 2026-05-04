<?php
$pageTitle = 'Manage Categories';
require_once __DIR__ . '/includes/admin_header.php';

$action  = $_GET['action'] ?? 'list';
$type    = $_GET['type'] ?? 'category'; // category or subcategory
$editId  = (int)($_GET['id'] ?? 0);
$msg     = $_SESSION['admin_msg'] ?? '';
$msgType = $_SESSION['admin_msg_type'] ?? 'success';
unset($_SESSION['admin_msg'], $_SESSION['admin_msg_type']);

function normalizeCategoryIcon(string $icon): string {
    $map = [
        'ðŸ‘ï¸' => '👁️',
        'âœ¨' => '✨',
        'ðŸ’‹' => '💋',
    ];
    return $map[$icon] ?? $icon;
}

// ── HANDLE FORM SUBMISSIONS ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['form_action'] ?? '';
    $postType   = $_POST['type'] ?? 'category';

    // DELETE
    if ($postAction === 'delete') {
        $delId = (int)$_POST['del_id'];
        $delType = $_POST['del_type'];
        if ($delType === 'category') {
            $conn->query("DELETE FROM categories WHERE id=$delId");
        } else {
            $conn->query("DELETE FROM subcategories WHERE id=$delId");
        }
        $_SESSION['admin_msg'] = 'Item deleted successfully.';
        $_SESSION['admin_msg_type'] = 'success';
        header('Location: categories.php'); exit;
    }

    // ADD or EDIT
    $name       = trim($_POST['name'] ?? '');
    $slug       = trim($_POST['slug'] ?? '');
    $icon       = trim($_POST['icon'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (!$name || !$slug) {
        $_SESSION['admin_msg']      = 'Please fill in all required fields.';
        $_SESSION['admin_msg_type'] = 'error';
        header("Location: categories.php?action=$postAction&type=$postType" . ($editId ? "&id=$editId" : '')); exit;
    }

    // Auto-generate slug from name if empty
    if (!$slug) $slug = strtolower(preg_replace('/[^a-z0-9]+/','-', $name));

    if ($postType === 'category') {
        if ($postAction === 'add') {
            $stmt = $conn->prepare("INSERT INTO categories (name,slug,icon,sort_order) VALUES (?,?,?,?)");
            $stmt->bind_param('sssi', $name,$slug,$icon,$sort_order);
            if ($stmt->execute()) {
                $_SESSION['admin_msg'] = '✅ Category added successfully!';
            } else {
                $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
                $_SESSION['admin_msg_type'] = 'error';
            }
        }
        if ($postAction === 'edit') {
            $stmt = $conn->prepare("UPDATE categories SET name=?,slug=?,icon=?,sort_order=? WHERE id=?");
            $stmt->bind_param('sssii', $name,$slug,$icon,$sort_order,$editId);
            if ($stmt->execute()) {
                $_SESSION['admin_msg'] = '✅ Category updated successfully!';
            } else {
                $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
                $_SESSION['admin_msg_type'] = 'error';
            }
        }
    } else {
        $category_id = (int)($_POST['category_id'] ?? 0);
        if ($postAction === 'add') {
            $stmt = $conn->prepare("INSERT INTO subcategories (category_id,name,slug,sort_order) VALUES (?,?,?,?)");
            $stmt->bind_param('issi', $category_id,$name,$slug,$sort_order);
            if ($stmt->execute()) {
                $_SESSION['admin_msg'] = '✅ Subcategory added successfully!';
            } else {
                $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
                $_SESSION['admin_msg_type'] = 'error';
            }
        }
        if ($postAction === 'edit') {
            $stmt = $conn->prepare("UPDATE subcategories SET category_id=?,name=?,slug=?,sort_order=? WHERE id=?");
            $stmt->bind_param('issii', $category_id,$name,$slug,$sort_order,$editId);
            if ($stmt->execute()) {
                $_SESSION['admin_msg'] = '✅ Subcategory updated successfully!';
            } else {
                $_SESSION['admin_msg'] = '❌ Error: ' . $conn->error;
                $_SESSION['admin_msg_type'] = 'error';
            }
        }
    }
    header('Location: categories.php'); exit;
}

// ── LOAD EDIT ITEM ─────────────────────────────────────────
$editItem = null;
$categories = getCategories($conn);

if ($action === 'edit' && $editId) {
    if ($type === 'category') {
        $editItem = $conn->query("SELECT * FROM categories WHERE id=$editId LIMIT 1")->fetch_assoc();
    } else {
        $editItem = $conn->query("SELECT s.*, c.name AS category_name FROM subcategories s JOIN categories c ON s.category_id=c.id WHERE s.id=$editId LIMIT 1")->fetch_assoc();
    }
    if (!$editItem) { header('Location: categories.php'); exit; }
    $pageTitle = 'Edit ' . ucfirst($type);
}
if ($action === 'add') $pageTitle = 'Add ' . ucfirst($type);

// ── LOAD ALL ITEMS ─────────────────────────────────────────
$allCategories = $conn->query("SELECT c.*, COUNT(s.id) AS subcat_count FROM categories c LEFT JOIN subcategories s ON c.id=s.category_id GROUP BY c.id ORDER BY c.sort_order, c.name")->fetch_all(MYSQLI_ASSOC);
$allSubcategories = $conn->query("SELECT s.*, c.name AS category_name FROM subcategories s JOIN categories c ON s.category_id=c.id ORDER BY c.sort_order, s.sort_order, s.name")->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?>
<div class="admin-alert admin-alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ══════════════════════════════════════════ ADD / EDIT FORM -->
<div class="admin-form-card">
    <h2 class="admin-form-title">
        <?= $action === 'add' ? '➕ Add New ' . ucfirst($type) : '✏️ Edit ' . ucfirst($type) ?>
    </h2>
    <form method="POST">
        <input type="hidden" name="form_action" value="<?= $action ?>">
        <input type="hidden" name="type" value="<?= $type ?>">

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Name *</label>
                <input type="text" name="name" class="admin-input" required
                       value="<?= htmlspecialchars($editItem['name'] ?? '') ?>"
                       placeholder="e.g. Eyes">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Slug * <span style="font-weight:400;color:#6b6b6b;">(URL key)</span></label>
                <input type="text" name="slug" id="slug-field" class="admin-input" required
                       value="<?= htmlspecialchars($editItem['slug'] ?? '') ?>"
                       placeholder="eyes">
                <span class="form-hint">Lowercase letters, numbers, hyphens only</span>
            </div>
        </div>

        <?php if ($type === 'category'): ?>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Icon <span style="font-weight:400;color:#6b6b6b;">(emoji)</span></label>
                <input type="text" name="icon" class="admin-input"
                       value="<?= htmlspecialchars($editItem['icon'] ?? '') ?>"
                       placeholder="e.g. 👁️">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Sort Order</label>
                <input type="number" name="sort_order" class="admin-input" min="0"
                       value="<?= $editItem['sort_order'] ?? 0 ?>" style="max-width:120px;">
                <span class="form-hint">Lower numbers appear first</span>
            </div>
        </div>
        <?php else: ?>
        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Parent Category *</label>
                <select name="category_id" class="admin-select" required>
                    <option value="">— Select Category —</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($editItem['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Sort Order</label>
                <input type="number" name="sort_order" class="admin-input" min="0"
                       value="<?= $editItem['sort_order'] ?? 0 ?>" style="max-width:120px;">
                <span class="form-hint">Lower numbers appear first</span>
            </div>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn-admin btn-admin-primary">
                <?= $action === 'add' ? '➕ Add ' . ucfirst($type) : '💾 Save Changes' ?>
            </button>
            <a href="categories.php" class="btn-admin btn-admin-outline">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════════ CATEGORIES LIST -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="categories.php?action=add&type=category" class="btn-admin btn-admin-primary">➕ Add Category</a>
        <a href="categories.php?action=add&type=subcategory" class="btn-admin btn-admin-outline">➕ Add Subcategory</a>
    </div>
</div>

<!-- Categories -->
<div class="admin-table-wrap" style="margin-bottom:32px;">
    <div class="table-header">
        <h3>Categories <span style="font-size:.85rem;color:#6b6b6b;font-weight:400;">(<?= count($allCategories) ?> total)</span></h3>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>Icon</th>
            <th>Name</th>
            <th>Slug</th>
            <th>Sort Order</th>
            <th>Subcategories</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($allCategories as $c): ?>
        <tr>
            <td style="font-size:1.2rem;text-align:center;"><?= htmlspecialchars(normalizeCategoryIcon($c['icon'] ?: '📂')) ?></td>
            <td><strong style="font-size:.9rem;"><?= htmlspecialchars($c['name']) ?></strong></td>
            <td style="font-size:.85rem;color:#6b6b6b;"><?= htmlspecialchars($c['slug']) ?></td>
            <td style="text-align:center;"><?= $c['sort_order'] ?></td>
            <td style="text-align:center;">
                <span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:12px;font-size:.8rem;">
                    <?= $c['subcat_count'] ?> subcats
                </span>
            </td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="categories.php?action=edit&type=category&id=<?= $c['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">✏️ Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($c['name'])) ?>? This will affect all subcategories and products.')">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="del_type" value="category">
                        <input type="hidden" name="del_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm">🗑️</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Subcategories -->
<div class="admin-table-wrap">
    <div class="table-header">
        <h3>Subcategories <span style="font-size:.85rem;color:#6b6b6b;font-weight:400;">(<?= count($allSubcategories) ?> total)</span></h3>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Parent Category</th>
            <th>Sort Order</th>
            <th>Products</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($allSubcategories as $s):
            $productCount = $conn->query("SELECT COUNT(*) FROM products WHERE subcategory_id={$s['id']}")->fetch_row()[0];
        ?>
        <tr>
            <td><strong style="font-size:.9rem;"><?= htmlspecialchars($s['name']) ?></strong></td>
            <td style="font-size:.85rem;color:#6b6b6b;"><?= htmlspecialchars($s['slug']) ?></td>
            <td style="font-size:.85rem;">
                <span style="background:#e3f2fd;color:#1565c0;padding:2px 8px;border-radius:12px;">
                    <?= htmlspecialchars($s['category_name']) ?>
                </span>
            </td>
            <td style="text-align:center;"><?= $s['sort_order'] ?></td>
            <td style="text-align:center;">
                <span style="background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:12px;font-size:.8rem;">
                    <?= $productCount ?> products
                </span>
            </td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="categories.php?action=edit&type=subcategory&id=<?= $s['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">✏️ Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars(addslashes($s['name'])) ?>? This will affect <?= $productCount ?> products.')">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="del_type" value="subcategory">
                        <input type="hidden" name="del_id" value="<?= $s['id'] ?>">
                        <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm">🗑️</button>
                    </form>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($allSubcategories)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:#6b6b6b;">No subcategories found. <a href="categories.php?action=add&type=subcategory" style="color:#c2185b;">Add one!</a></td></tr>
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