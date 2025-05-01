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
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if ($contentType === 'application/json') {
  $input = json_decode(file_get_contents('php://input'), true);

  if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
  }
  $product_id = $input['product_id'] ?? null;
  $quantity = $input['quantity'] ?? 1;
  $sugar_level = $input['sugar_level'] ?? 100;
  $ice_level = $input['ice_level'] ?? 100;
  $toppings = $input['toppings'] ?? [];
} else {
  $product_id = $_POST['product_id'] ?? null;
  $quantity = $_POST['quantity'] ?? 1;
  $sugar_level = $_POST['sugar_level'] ?? 100;
  $ice_level = $_POST['ice_level'] ?? 100;
  $toppings = $_POST['toppings'] ?? [];
}
