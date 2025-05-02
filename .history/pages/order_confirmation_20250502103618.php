<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: orders.php');
  exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
  header('Location: orders.php');
  exit;
}
$order = $order_result->fetch_assoc();

$items_sql = "SELECT oi.*, p.name, p.image FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($item = $items_result->fetch_assoc()) {
  $toppings_sql = "SELECT topping_name FROM order_item_toppings WHERE order_item_id = ?";
  $toppings_stmt = $conn->prepare($toppings_sql);
  $toppings_stmt->bind_param("i", $item['id']);
  $toppings_stmt->execute();
  $toppings_result = $toppings_stmt->get_result();

  $toppings = [];
  while ($topping = $toppings_result->fetch_assoc()) {
    $toppings[] = $topping['topping_name'];
  }

  $item['toppings'] = $toppings;
  $order_items[] = $item;
}
?>