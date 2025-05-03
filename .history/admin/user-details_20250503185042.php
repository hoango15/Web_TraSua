<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: users.php');
  exit;
}

$user_id = $_GET['id'];
if (isset($_POST['delete_user']) && $_POST['user_id'] == $user_id) {
  $delete_orders_sql = "DELETE FROM orders WHERE user_id = ?";
  $delete_orders_stmt = $conn->prepare($delete_orders_sql);
  $delete_orders_stmt->bind_param("i", $user_id);
  $delete_orders_stmt->execute();
  