<?php
session_start();
header('Content-Type: application/json');


if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}
$cart_id = $_POST['cart_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$cart_id || !isset($_SESSION['cart'][$cart_id])) {
  echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
  exit;
}

if (!in_array($action, ['increase', 'decrease'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid action']);
  exit;
}

if ($action === 'increase') {
  $_SESSION['cart'][$cart_id]['quantity']++;
} else {
  $_SESSION['cart'][$cart_id]['quantity']--;
  if ($_SESSION['cart'][$cart_id]['quantity'] <= 0) {
      unset($_SESSION['cart'][$cart_id]);
  }
}
echo json_encode([
  'success' => true,
  'message' => 'Cart updated',
  'cart_count' => count($_SESSION['cart'])
]);
?>