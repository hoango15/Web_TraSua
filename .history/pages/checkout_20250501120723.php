<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (empty($_SESSION['cart'])) {
  header('Location: cart.php');
  exit;
}
$user = null;
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $user_sql = "SELECT * FROM users WHERE id = ?";
  $user_stmt = $conn->prepare($user_sql);
  $user_stmt->bind_param("i", $user_id);
  $user_stmt->execute();
  $user_result = $user_stmt->get_result();
  $user = $user_result->fetch_assoc();
}
$cart_items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $cart_id => $item) {
  $product_id = $item['product_id'];
  $sql = "SELECT * FROM products WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $product_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $item_price = $product['price'];
    if ($product['discount_price'] > 0) {
      $item_price = $product['discount_price'];
    }

    $item_price += count($item['toppings']) * 5000;

    $item_total = $item_price * $item['quantity'];
    $subtotal += $item_total;
