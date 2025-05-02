<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['order_id'])) {
  header('Location: index.php');
  exit;
}

$order_id = $_GET['order_id'];
$order_sql = "SELECT * FROM orders WHERE id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
  header('Location: index.php');
  exit;
}
$order = $order_result->fetch_assoc();

if ($order['payment_status'] === 'paid') {
  header('Location: order_confirmation.php?id=' . $order_id);
  exit;
}
$items_sql = "SELECT oi.*, p.name, p.image FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = [];

while ($item = $items_result->fetch_assoc()) {