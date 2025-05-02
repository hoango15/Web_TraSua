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
$payment_method = $order['payment_method'];
$payment_status = '';
$transaction_id = '';
$response_data = '';
$error_message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'confirm_momo') {
    $transaction_id = 'MOMO_' . time() . rand(1000, 9999);
    $payment_status = 'paid';
    $response_data = json_encode([
      'status' => 'success',
      'message' => 'Payment successful',
      'transaction_id' => $transaction_id,
      'time' => date('Y-m-d H:i:s')
    ]);

    $update_order_sql = "UPDATE orders SET payment_status = ? WHERE id = ?";
    $update_order_stmt = $conn->prepare($update_order_sql);
    $update_order_stmt->bind_param("si", $payment_status, $order_id);
    $log_payment_sql = "INSERT INTO payment_logs (order_id, payment_method, transaction_id, amount, status, response_data, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, NOW())";

$log_payment_stmt = $conn->prepare($log_payment_sql);
$log_payment_stmt->bind_param("issdss", $order_id, $payment_method, $transaction_id, $order['total'], $payment_status, $response_data);

$conn->begin_transaction();

try {
$update_order_stmt->execute();
$log_payment_stmt->execute();
$conn->commit();
$success = true;
 header('Location: order_confirmation.php?id=' . $order_id);
      exit;
    } catch (Exception $e) {
      $conn->rollback();
      $error_message = "Lỗi xử lý thanh toán: " . $e->getMessage();
    }
  } elseif ($_POST['action'] === 'cancel_payment') {