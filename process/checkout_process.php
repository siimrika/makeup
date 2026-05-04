<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(BASE_URL . 'checkout.php'); }
$items = getCartItems($conn);
if (empty($items)) { redirect(BASE_URL . 'cart.php'); }
$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$email   = trim($_POST['email'] ?? '');
$address = trim($_POST['address'] ?? '');
$city    = trim($_POST['city'] ?? '');
$notes   = trim($_POST['notes'] ?? '');
$payment = trim($_POST['payment_method'] ?? 'cod');
if (!$name || !$phone || !$address || !$city) {
    $_SESSION['checkout_error'] = 'Please fill in all required fields.';
    redirect(BASE_URL . 'checkout.php');
}
$subtotal = array_sum(array_column($items, 'line_total'));
$shipping = $subtotal >= 2000 ? 0 : 150;
$total    = $subtotal + $shipping;
$userId   = isLoggedIn() ? (int)$_SESSION['user_id'] : null;
$stmt = $conn->prepare("INSERT INTO orders (user_id,guest_name,guest_email,phone,address,city,payment_method,subtotal,shipping,total,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
$stmt->bind_param('issssssddds', $userId,$name,$email,$phone,$address,$city,$payment,$subtotal,$shipping,$total,$notes);
if (!$stmt->execute()) {
    $_SESSION['checkout_error'] = 'Order failed. Please try again.';
    redirect(BASE_URL . 'checkout.php');
}
$orderId = $conn->insert_id;
$itemStmt = $conn->prepare("INSERT INTO order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)");
foreach ($items as $item) {
    $pid = (int)$item['id']; $qty = (int)$item['qty']; $price = (float)$item['price'];
    $itemStmt->bind_param('iiid', $orderId, $pid, $qty, $price);
    $itemStmt->execute();
}
unset($_SESSION['cart']);
$_SESSION['order_success'] = $orderId;
redirect(BASE_URL . 'order_success.php');
