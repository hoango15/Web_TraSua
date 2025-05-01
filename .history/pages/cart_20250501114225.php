<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
  unset($_SESSION['cart'][$_GET['remove']]);
  header('Location: cart.php');
  exit;
}
if (isset($_GET['update']) && isset($_SESSION['cart'][$_GET['update']]) && isset($_GET['quantity'])) {
  $quantity = (int)$_GET['quantity'];
  if ($quantity > 0 && $quantity <= 10) {
    $_SESSION['cart'][$_GET['update']]['quantity'] = $quantity;
  }
  header('Location: cart.php');
  exit;
}
$cart_data = getCartItems($conn, $_SESSION['cart']);
$page_title = "Giỏ Hàng - " . SITE_NAME;
$active_menu = "cart";
$extra_css = ['../assets/css/cart.css'];
