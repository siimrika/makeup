<?php
$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/admin_header.php';

$msg     = $_SESSION['admin_msg'] ?? '';
$msgType = $_SESSION['admin_msg_type'] ?? 'success';
unset($_SESSION['admin_msg'], $_SESSION['admin_msg_type']);

// Update user role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
    $role   = $conn->real_escape_string($_POST['role']);
    $allowed = ['customer','admin'];
    if (in_array($role, $allowed)) {
        $conn->query("UPDATE users SET role='$role' WHERE id=$userId");
        $_SESSION['admin_msg'] = '✅ User role updated.';
    }
    header('Location: users.php'); exit;
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userId = (int)$_POST['delete_user_id'];
    // Don't allow deleting the current admin user
    if ($userId !== $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE id=$userId");
        $_SESSION['admin_msg'] = '✅ User deleted successfully.';
    } else {
        $_SESSION['admin_msg'] = '❌ Cannot delete your own account.';
        $_SESSION['admin_msg_type'] = 'error';
    }
    header('Location: users.php'); exit;
}

$filter = $conn->real_escape_string($_GET['role'] ?? '');
$search = $conn->real_escape_string($_GET['search'] ?? '');
$where  = '1=1';
if ($filter) $where .= " AND role='$filter'";
if ($search) $where .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";

$users = $conn->query("SELECT u.*,
    (SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS order_count,
    (SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id=u.id AND status != 'cancelled') AS total_spent
    FROM users u WHERE $where ORDER BY u.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<?php if ($msg): ?>
<div class="admin-alert admin-alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- ══════════════════════════════════════════ USERS LIST -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach ([''=>'All','customer'=>'Customers','admin'=>'Admins'] as $val=>$label): ?>
        <a href="users.php<?= $val?'?role='.$val:'' ?>" class="btn-admin <?= $filter===$val?'btn-admin-primary':'btn-admin-outline' ?> btn-admin-sm"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" class="admin-search" placeholder="Search users..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
        <button type="submit" class="btn-admin btn-admin-outline">Search</button>
        <?php if ($search || $filter): ?>
        <a href="users.php" class="btn-admin btn-admin-danger btn-admin-sm">✕ Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="admin-table-wrap">
    <div class="table-header">
        <h3>Users <span style="font-size:.85rem;color:#6b6b6b;font-weight:400;">(<?= count($users) ?> total)</span></h3>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Orders</th>
            <th>Total Spent</th>
            <th>Joined</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u):
            $isCurrentUser = $u['id'] == $_SESSION['user_id'];
        ?>
        <tr>
            <td>
                <strong style="font-size:.9rem;"><?= htmlspecialchars($u['name']) ?></strong>
                <?php if ($isCurrentUser): ?>
                <span style="background:#e8f5e9;color:#2e7d32;padding:1px 6px;border-radius:8px;font-size:.7rem;margin-left:6px;">YOU</span>
                <?php endif; ?>
            </td>
            <td style="font-size:.85rem;color:#6b6b6b;"><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <?php if ($isCurrentUser): ?>
                    <span class="status-badge status-processing"><?= ucfirst($u['role']) ?></span>
                <?php else: ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <select name="role" class="admin-select" style="width:120px;font-size:.8rem;padding:4px 8px;" onchange="this.form.submit()">
                            <option value="customer" <?= $u['role']==='customer'?'selected':'' ?>>Customer</option>
                            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
                        </select>
                    </form>
                <?php endif; ?>
            </td>
            <td style="text-align:center;">
                <span style="background:#e3f2fd;color:#1565c0;padding:2px 8px;border-radius:12px;font-size:.8rem;">
                    <?= $u['order_count'] ?> orders
                </span>
            </td>
            <td>
                <strong style="color:#c2185b;">Rs. <?= number_format($u['total_spent'],2) ?></strong>
            </td>
            <td style="font-size:.8rem;color:#6b6b6b;">
                <?= date('d M Y', strtotime($u['created_at'])) ?>
            </td>
            <td>
                <?php if (!$isCurrentUser): ?>
                <form method="POST" onsubmit="return confirm('Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>? This cannot be undone.')">
                    <input type="hidden" name="delete_user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn-admin btn-admin-danger btn-admin-sm">🗑️ Delete</button>
                </form>
                <?php else: ?>
                <span style="color:#6b6b6b;font-size:.8rem;">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#6b6b6b;">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- User Statistics -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:32px;">
    <?php
    $totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
    $totalCustomers = $conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetch_row()[0];
    $totalAdmins = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetch_row()[0];
    $recentUsers = $conn->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_row()[0];
    ?>
    <div class="stat-card">
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-label">👥 Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalCustomers ?></div>
        <div class="stat-label">🛒 Customers</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalAdmins ?></div>
        <div class="stat-label">👑 Admins</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $recentUsers ?></div>
        <div class="stat-label">🆕 New (30 days)</div>
    </div>
</div>

</div></div></body></html>