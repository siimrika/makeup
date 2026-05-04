<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'makeup_studio');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;background:#fee;border:1px solid #c00;padding:20px;margin:20px;border-radius:8px;">
        <strong>Database Connection Failed:</strong> ' . htmlspecialchars($conn->connect_error) . '
        <p>Make sure XAMPP MySQL is running and the <code>makeup_studio</code> database is imported.</p>
    </div>');
}

$conn->set_charset('utf8mb4');
define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/makeup/');
