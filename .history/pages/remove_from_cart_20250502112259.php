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