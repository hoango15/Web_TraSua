<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$categories_count = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

$recent_orders_sql = "SELECT o.*, u.name as user_name FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($recent_orders_sql);