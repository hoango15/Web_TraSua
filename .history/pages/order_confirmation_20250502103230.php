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
