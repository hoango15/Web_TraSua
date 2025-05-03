<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}
$users_sql = "SELECT u.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent 
             FROM users u 
             LEFT JOIN orders o ON u.id = o.user_id 
             GROUP BY u.id 
             ORDER BY u.created_at DESC";
$users_result = $conn->query($users_sql);
?>

