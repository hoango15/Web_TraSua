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