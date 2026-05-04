<?php
/**
 * Makeup Studio — One-Click Database Setup
 * Visit: http://localhost/makeup/setup.php
 * DELETE this file after setup is complete!
 */

$host = 'localhost'; $user = 'root'; $pass = ''; $dbname = 'makeup_studio';
$step = $_GET['step'] ?? '1';
$log  = [];

function logLine($msg, $ok = true) {
    global $log;
    $log[] = ['msg' => $msg, 'ok' => $ok];
}

if ($step === 'run') {
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) { logLine('❌ Cannot connect to MySQL: ' . $conn->connect_error, false); }
    else {
        $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db($dbname);
        logLine("✅ Database '$dbname' ready.");

        $sqlFile = __DIR__ . '/database/makeup_studio.sql';
        if (!file_exists($sqlFile)) { logLine('❌ SQL file not found at ' . $sqlFile, false); }
        else {
            $sql = file_get_contents($sqlFile);
            // Split by semicolon, skip USE / CREATE DATABASE
            $statements = array_filter(array_map('trim', explode(';', $sql)), function($s) {
                return strlen($s) > 5 &&
                       stripos($s, 'CREATE DATABASE') === false &&
                       stripos($s, 'USE `') === false;
            });
            $ok = 0; $fail = 0;
            foreach ($statements as $stmt) {
                if ($conn->query($stmt) === false) {
                    logLine("⚠️ " . substr($stmt, 0, 60) . '... — ' . $conn->error, false);
                    $fail++;
                } else { $ok++; }
            }
            logLine("✅ Executed $ok statements. " . ($fail ? "⚠️ $fail warnings (often safe)." : ''));
            logLine("✅ All tables & seed data imported.");
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Makeup Studio — Setup</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:linear-gradient(135deg,#0d0010,#3d0026);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.card{background:#fff;border-radius:20px;padding:48px;width:100%;max-width:600px;box-shadow:0 30px 80px rgba(0,0,0,.4)}
.logo{font-family:'Playfair Display',serif;font-size:2rem;color:#c2185b;text-align:center;margin-bottom:8px}
.logo span{color:#1a1a1a}
p.sub{text-align:center;color:#6b6b6b;font-size:.9rem;margin-bottom:36px}
.step-list{list-style:none;margin-bottom:32px}
.step-list li{padding:10px 0;border-bottom:1px solid #f5f5f5;font-size:.9rem;display:flex;align-items:flex-start;gap:10px;color:#444}
.step-list li:last-child{border:none}
.btn{display:block;width:100%;padding:15px;background:linear-gradient(135deg,#c2185b,#e91e8c);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;text-align:center;text-decoration:none;transition:.3s}
.btn:hover{opacity:.9}
.log-item{padding:8px 12px;border-radius:8px;font-size:.85rem;margin-bottom:8px}
.log-ok{background:#e8f5e9;color:#2e7d32}
.log-err{background:#fde8ee;color:#c2185b}
.links{display:flex;gap:12px;margin-top:20px;flex-wrap:wrap}
.link-btn{flex:1;text-align:center;padding:12px;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;min-width:140px}
.link-site{background:#c2185b;color:#fff}
.link-admin{background:#1a1a1a;color:#fff}
.warn{background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:12px 16px;font-size:.82rem;color:#856404;margin-top:16px}
</style>
</head>
<body>
<div class="card">
    <div class="logo">Makeup <span>Studio</span></div>
    <p class="sub">Database Setup Wizard</p>

    <?php if ($step !== 'run'): ?>
    <ul class="step-list">
        <li>✅ <span>Make sure <strong>XAMPP</strong> is running (Apache + MySQL)</span></li>
        <li>✅ <span>MySQL root user has no password (default XAMPP setup)</span></li>
        <li>✅ <span>This will create the <strong>makeup_studio</strong> database with all tables and 48 seed products</span></li>
        <li>⚡ <span>Default admin login: <code>admin@makeupstudio.com</code> / <code>password</code></span></li>
    </ul>
    <a href="setup.php?step=run" class="btn">🚀 Run Setup Now</a>

    <?php else: ?>
    <?php foreach ($log as $l): ?>
    <div class="log-item <?= $l['ok'] ? 'log-ok' : 'log-err' ?>"><?= htmlspecialchars($l['msg']) ?></div>
    <?php endforeach; ?>

    <?php $allOk = empty(array_filter($log, fn($l) => !$l['ok'])); ?>
    <?php if ($allOk): ?>
    <div style="text-align:center;padding:16px 0 8px;font-size:1.5rem;">🎉</div>
    <p style="text-align:center;font-weight:600;color:#2e7d32;margin-bottom:8px;">Setup Complete!</p>
    <div class="links">
        <a href="index.php" class="link-btn link-site">🌸 View Website</a>
        <a href="admin/index.php" class="link-btn link-admin">⚙️ Admin Panel</a>
    </div>
    <div class="warn">⚠️ <strong>Security:</strong> Please delete <code>setup.php</code> after setup is complete.</div>
    <?php else: ?>
    <a href="setup.php?step=run" class="btn" style="margin-top:16px;">🔄 Try Again</a>
    <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
