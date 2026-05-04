<?php
// Development mode: Auto-login as admin
if (session_status() === PHP_SESSION_NONE) session_start();
// Use absolute path from document root
$rootPath = dirname(__DIR__); // Go up one level from admin to root
require_once $rootPath . '/includes/db.php';
require_once $rootPath . '/includes/functions.php';

// Check if admin user exists, if not create one
$adminCheck = $conn->query("SELECT * FROM users WHERE role='admin' LIMIT 1");
if ($adminCheck && $adminCheck->num_rows > 0) {
    $admin = $adminCheck->fetch_assoc();
} else {
    // Create default admin user
    $adminPass = password_hash('Admin@123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (name, email, password_hash, role) VALUES ('Admin', 'admin@makeupstudio.com', '$adminPass', 'admin')");
    $admin = [
        'id' => $conn->insert_id,
        'name' => 'Admin',
        'email' => 'admin@makeupstudio.com',
        'role' => 'admin'
    ];
}

// Auto-login as admin
$_SESSION['user_id'] = $admin['id'];
$_SESSION['user_name'] = $admin['name'];
$_SESSION['user_email'] = $admin['email'];
$_SESSION['user_role'] = $admin['role'];

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';

// Stats
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$totalOrders   = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalRevenue  = $conn->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetch_row()[0];
$totalUsers    = $conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetch_row()[0];

// Recent orders
$recentOrders = $conn->query("SELECT o.*, COALESCE(u.name, o.guest_name) AS customer FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Low stock
$lowStock = $conn->query("SELECT p.*, b.name AS brand_name FROM products p JOIN brands b ON p.brand_id=b.id WHERE p.stock < 10 ORDER BY p.stock ASC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-value"><?= $totalProducts ?></div>
        <div class="stat-label">💄 Total Products</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalOrders ?></div>
        <div class="stat-label">📦 Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">Rs.<?= number_format($totalRevenue) ?></div>
        <div class="stat-label">💰 Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-label">👥 Customers</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

<!-- Recent Orders -->
<div class="admin-table-wrap">
    <div class="table-header">
        <h3>Recent Orders</h3>
        <a href="orders.php" class="btn-admin btn-admin-outline btn-admin-sm">View All</a>
    </div>
    <table class="admin-table">
        <thead><tr>
            <th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th>
        </tr></thead>
        <tbody>
        <?php foreach ($recentOrders as $o): ?>
        <tr>
            <td><strong>#<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></strong></td>
            <td><?= htmlspecialchars($o['customer'] ?? 'Guest') ?></td>
            <td><strong style="color:#c2185b;">Rs. <?= number_format($o['total'],2) ?></strong></td>
            <td><?= htmlspecialchars(paymentMethodLabel($o['payment_method'])) ?></td>
            <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
            <td style="color:#6b6b6b;font-size:.8rem;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentOrders)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#6b6b6b;">No orders yet</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Low Stock -->
<div class="admin-table-wrap">
    <div class="table-header"><h3>⚠️ Low Stock</h3></div>
    <table class="admin-table">
        <thead><tr><th>Product</th><th>Brand</th><th>Stock</th></tr></thead>
        <tbody>
        <?php foreach ($lowStock as $p): ?>
        <tr>
            <td style="font-size:.82rem;"><?= htmlspecialchars($p['name']) ?></td>
            <td style="font-size:.8rem;color:#6b6b6b;"><?= htmlspecialchars($p['brand_name']) ?></td>
            <td class="stock-low"><?= $p['stock'] ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($lowStock)): ?>
        <tr><td colspan="3" style="text-align:center;padding:20px;color:#4caf50;">✓ All stock levels OK</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</div>

</div></div></body></html>
