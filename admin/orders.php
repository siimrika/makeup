<?php
$pageTitle = 'Manage Orders';
require_once __DIR__ . '/includes/admin_header.php';

$msg     = $_SESSION['admin_msg'] ?? '';
$msgType = $_SESSION['admin_msg_type'] ?? 'success';
unset($_SESSION['admin_msg'], $_SESSION['admin_msg_type']);

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $oid    = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $conn->query("UPDATE orders SET status='$status' WHERE id=$oid");
        $_SESSION['admin_msg'] = '✅ Order status updated.';
    }
    header('Location: orders.php'); exit;
}

$viewId = (int)($_GET['id'] ?? 0);
$filter = $conn->real_escape_string($_GET['status'] ?? '');
$where  = $filter ? "WHERE o.status='$filter'" : '';

$orders = $conn->query("SELECT o.*, COALESCE(u.name, o.guest_name) AS customer_name, u.email AS user_email
    FROM orders o LEFT JOIN users u ON o.user_id=u.id
    $where ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$viewOrder = null;
$viewItems = [];
if ($viewId) {
    $viewOrder = $conn->query("SELECT o.*, COALESCE(u.name, o.guest_name) AS customer_name FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=$viewId")->fetch_assoc();
    $viewItems = $conn->query("SELECT oi.*, p.name AS product_name, p.image_path, b.name AS brand_name FROM order_items oi JOIN products p ON oi.product_id=p.id JOIN brands b ON p.brand_id=b.id WHERE oi.order_id=$viewId")->fetch_all(MYSQLI_ASSOC);
}
?>

<?php if ($msg): ?><div class="admin-alert admin-alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

<?php if ($viewOrder): ?>
<!-- ORDER DETAIL VIEW -->
<div style="display:flex;gap:12px;margin-bottom:20px;align-items:center;">
    <a href="orders.php" class="btn-admin btn-admin-outline">← Back to Orders</a>
    <h2 style="font-family:'Playfair Display',serif;">Order #<?= str_pad($viewOrder['id'],6,'0',STR_PAD_LEFT) ?></h2>
    <span class="status-badge status-<?= $viewOrder['status'] ?>"><?= ucfirst($viewOrder['status']) ?></span>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
    <div>
        <div class="admin-form-card" style="margin-bottom:20px;">
            <h3 class="admin-form-title">Order Items</h3>
            <table class="admin-table">
                <thead><tr><th>Image</th><th>Product</th><th>Brand</th><th>Price</th><th>Qty</th><th>Total</th></tr></thead>
                <tbody>
                <?php foreach ($viewItems as $item): ?>
                <tr>
                    <td><img class="product-thumb" src="<?= BASE_URL . htmlspecialchars($item['image_path'] ?: 'assets/images/product-placeholder.svg') ?>" alt=""></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td style="color:#6b6b6b;font-size:.83rem;"><?= htmlspecialchars($item['brand_name']) ?></td>
                    <td>Rs. <?= number_format($item['price'],2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><strong style="color:#c2185b;">Rs. <?= number_format($item['price']*$item['quantity'],2) ?></strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f5f5f5;display:flex;flex-direction:column;gap:8px;max-width:280px;margin-left:auto;">
                <div style="display:flex;justify-content:space-between;font-size:.9rem;"><span>Subtotal</span><span>Rs. <?= number_format($viewOrder['subtotal'],2) ?></span></div>
                <div style="display:flex;justify-content:space-between;font-size:.9rem;"><span>Shipping</span><span><?= $viewOrder['shipping'] == 0 ? 'FREE' : 'Rs. '.number_format($viewOrder['shipping'],2) ?></span></div>
                <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;color:#c2185b;border-top:1px solid #f5f5f5;padding-top:8px;"><span>Total</span><span>Rs. <?= number_format($viewOrder['total'],2) ?></span></div>
            </div>
        </div>
    </div>

    <div>
        <div class="admin-form-card" style="margin-bottom:16px;">
            <h3 class="admin-form-title">Customer Info</h3>
            <div style="font-size:.88rem;line-height:2;">
                <div><strong>Name:</strong> <?= htmlspecialchars($viewOrder['customer_name']) ?></div>
                <div><strong>Email:</strong> <?= htmlspecialchars($viewOrder['guest_email'] ?? $viewOrder['user_email'] ?? '—') ?></div>
                <div><strong>Phone:</strong> <?= htmlspecialchars($viewOrder['phone']) ?></div>
                <div><strong>Address:</strong> <?= htmlspecialchars($viewOrder['address']) ?>, <?= htmlspecialchars($viewOrder['city']) ?></div>
                <div><strong>Payment:</strong> <?= htmlspecialchars(paymentMethodLabel($viewOrder['payment_method'])) ?></div>
                <div><strong>Date:</strong> <?= date('d M Y H:i', strtotime($viewOrder['created_at'])) ?></div>
                <?php if ($viewOrder['notes']): ?><div><strong>Notes:</strong> <?= htmlspecialchars($viewOrder['notes']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="admin-form-card">
            <h3 class="admin-form-title">Update Status</h3>
            <form method="POST">
                <input type="hidden" name="order_id" value="<?= $viewOrder['id'] ?>">
                <select name="status" class="admin-select" style="margin-bottom:12px;">
                    <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $viewOrder['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;justify-content:center;">Update Status</button>
            </form>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ORDER LIST -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach ([''=>'All','pending'=>'Pending','processing'=>'Processing','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $val=>$label): ?>
        <a href="orders.php<?= $val?'?status='.$val:'' ?>" class="btn-admin <?= $filter===$val?'btn-admin-primary':'btn-admin-outline' ?> btn-admin-sm"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
    <span style="color:#6b6b6b;font-size:.85rem;"><?= count($orders) ?> orders</span>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr>
            <th>Order #</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php foreach ($orders as $o):
            $itemCount = $conn->query("SELECT SUM(quantity) FROM order_items WHERE order_id={$o['id']}")->fetch_row()[0] ?? 0;
        ?>
        <tr>
            <td><strong>#<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></strong></td>
            <td>
                <div style="font-size:.88rem;"><?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?></div>
                <div style="font-size:.75rem;color:#6b6b6b;"><?= htmlspecialchars($o['phone'] ?? '') ?></div>
            </td>
            <td style="text-align:center;"><?= $itemCount ?></td>
            <td><strong style="color:#c2185b;">Rs. <?= number_format($o['total'],2) ?></strong></td>
            <td style="font-size:.82rem;"><?= htmlspecialchars(paymentMethodLabel($o['payment_method'])) ?></td>
            <td><span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
            <td style="font-size:.8rem;color:#6b6b6b;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
            <td><a href="orders.php?id=<?= $o['id'] ?>" class="btn-admin btn-admin-outline btn-admin-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($orders)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:#6b6b6b;">No orders found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

</div></div></body></html>
