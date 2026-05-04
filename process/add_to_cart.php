<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Return cart count
if (isset($_GET['action']) && $_GET['action'] === 'count') {
    echo json_encode(['count' => getCartCount()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$qty        = max(1, (int)($_POST['qty'] ?? 1));

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Verify product exists and is in stock
$res = $conn->query("SELECT id, stock FROM products WHERE id=$product_id LIMIT 1");
$product = $res ? $res->fetch_assoc() : null;

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

addToCart($product_id, $qty);
echo json_encode(['success' => true, 'count' => getCartCount(), 'message' => 'Added to cart']);
