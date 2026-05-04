<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }
$product_id = (int)($_POST['product_id'] ?? 0);
$qty        = (int)($_POST['qty'] ?? 0);
if (!$product_id) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }
updateCartQty($product_id, $qty);
echo json_encode(['success'=>true,'count'=>getCartCount()]);
