<?php
$pageTitle = 'Site Settings';
require_once __DIR__ . '/includes/admin_header.php';

$msg     = $_SESSION['admin_msg'] ?? '';
$msgType = $_SESSION['admin_msg_type'] ?? 'success';
unset($_SESSION['admin_msg'], $_SESSION['admin_msg_type']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName     = trim($_POST['site_name'] ?? '');
    $siteEmail    = trim($_POST['site_email'] ?? '');
    $sitePhone    = trim($_POST['site_phone'] ?? '');
    $freeShipping = (float)($_POST['free_shipping_threshold'] ?? 0);
    $shippingRate = (float)($_POST['default_shipping_rate'] ?? 0);

    // For now, we'll store settings in a simple JSON file
    // In a production app, you'd want a proper settings table
    $settings = [
        'site_name' => $siteName,
        'site_email' => $siteEmail,
        'site_phone' => $sitePhone,
        'free_shipping_threshold' => $freeShipping,
        'default_shipping_rate' => $shippingRate,
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['user_name']
    ];

    $settingsFile = __DIR__ . '/../config/settings.json';
    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
        $_SESSION['admin_msg'] = '✅ Settings updated successfully!';
    } else {
        $_SESSION['admin_msg'] = '❌ Error saving settings.';
        $_SESSION['admin_msg_type'] = 'error';
    }
    header('Location: settings.php'); exit;
}

// Load current settings
$settingsFile = __DIR__ . '/../config/settings.json';
$currentSettings = [];
if (file_exists($settingsFile)) {
    $currentSettings = json_decode(file_get_contents($settingsFile), true) ?: [];
}

// Set defaults
$siteName     = $currentSettings['site_name'] ?? 'Makeup Studio';
$siteEmail    = $currentSettings['site_email'] ?? 'info@makeupstudio.com';
$sitePhone    = $currentSettings['site_phone'] ?? '+92 300 1234567';
$freeShipping = $currentSettings['free_shipping_threshold'] ?? 2000;
$shippingRate = $currentSettings['default_shipping_rate'] ?? 150;
?>

<?php if ($msg): ?>
<div class="admin-alert admin-alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="admin-form-card">
    <h2 class="admin-form-title">⚙️ Site Settings</h2>
    <form method="POST">

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Site Name</label>
                <input type="text" name="site_name" class="admin-input" required
                       value="<?= htmlspecialchars($siteName) ?>"
                       placeholder="Makeup Studio">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Contact Email</label>
                <input type="email" name="site_email" class="admin-input" required
                       value="<?= htmlspecialchars($siteEmail) ?>"
                       placeholder="info@makeupstudio.com">
            </div>
        </div>

        <div class="admin-form-row">
            <div class="admin-form-group">
                <label class="admin-label">Contact Phone</label>
                <input type="text" name="site_phone" class="admin-input" required
                       value="<?= htmlspecialchars($sitePhone) ?>"
                       placeholder="+92 300 1234567">
            </div>
            <div class="admin-form-group">
                <label class="admin-label">Free Shipping Threshold (Rs.)</label>
                <input type="number" name="free_shipping_threshold" class="admin-input" step="0.01" min="0"
                       value="<?= $freeShipping ?>"
                       placeholder="2000">
                <span class="form-hint">Orders above this amount get free shipping</span>
            </div>
        </div>

        <div class="admin-form-group">
            <label class="admin-label">Default Shipping Rate (Rs.)</label>
            <input type="number" name="default_shipping_rate" class="admin-input" step="0.01" min="0"
                   value="<?= $shippingRate ?>" style="max-width:200px;"
                   placeholder="150">
            <span class="form-hint">Standard shipping cost for orders below free shipping threshold</span>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px;">
            <button type="submit" class="btn-admin btn-admin-primary">💾 Save Settings</button>
        </div>
    </form>
</div>

<!-- System Information -->
<div class="admin-form-card" style="margin-top:32px;">
    <h3 class="admin-form-title">ℹ️ System Information</h3>
    <div style="font-size:.88rem;line-height:2;">
        <div><strong>PHP Version:</strong> <?= PHP_VERSION ?></div>
        <div><strong>MySQL Version:</strong>
            <?php
            $mysqlVersion = $conn->query("SELECT VERSION()")->fetch_row()[0] ?? 'Unknown';
            echo htmlspecialchars($mysqlVersion);
            ?>
        </div>
        <div><strong>Server:</strong> <?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></div>
        <div><strong>Last Updated:</strong> <?= htmlspecialchars($currentSettings['updated_at'] ?? 'Never') ?></div>
        <div><strong>Updated By:</strong> <?= htmlspecialchars($currentSettings['updated_by'] ?? '—') ?></div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-form-card" style="margin-top:32px;">
    <h3 class="admin-form-title">🚀 Quick Actions</h3>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>database/makeup_studio.sql" target="_blank" class="btn-admin btn-admin-outline">
            📥 Download Database Backup
        </a>
        <a href="products.php" class="btn-admin btn-admin-outline">
            💄 Manage Products
        </a>
        <a href="orders.php" class="btn-admin btn-admin-outline">
            📦 View Orders
        </a>
        <a href="<?= BASE_URL ?>index.php" target="_blank" class="btn-admin btn-admin-success">
            🌐 View Live Site
        </a>
    </div>
</div>

</div></div></body></html>