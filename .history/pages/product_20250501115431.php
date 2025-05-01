<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';


if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: products.php');
  exit;
}

$product_id = $_GET['id'];
$product = getProductById($conn, $product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}
$toppings = getAllToppings($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_SESSION['cart'])) {
      $_SESSION['cart'] = [];
  }

  $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
  $sugar_level = isset($_POST['sugar_level']) ? (int)$_POST['sugar_level'] : 100;
  $ice_level = isset($_POST['ice_level']) ? (int)$_POST['ice_level'] : 100;
  $toppings_selected = isset($_POST['toppings']) ? $_POST['toppings'] : [];

  $cart_id = uniqid();
  $_SESSION['cart'][$cart_id] = [
      'product_id' => $product_id,
      'quantity' => $quantity,
      'sugar_level' => $sugar_level,
      'ice_level' => $ice_level,
      'toppings' => $toppings_selected,
      'added_at' => time()
  ];

  header('Location: cart.php');
  exit;
}