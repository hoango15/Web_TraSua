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
  $delete_user_sql = "DELETE FROM users WHERE id = ?";
    $delete_user_stmt = $conn->prepare($delete_user_sql);
    $delete_user_stmt->bind_param("i", $user_id);
    if ($delete_user_stmt->execute()) {
      $_SESSION['success_message'] = "Tài khoản khách hàng đã được xóa thành công.";
        header('Location: users.php');
        exit;
    } else {
        $error_message = "Không thể xóa tài khoản. Vui lòng thử lại.";
    }
}
$_SESSION['success_message'] = "Tài khoản khách hàng đã được xóa thành công.";
        header('Location: users.php');
        exit;
    } else {
        $error_message = "Không thể xóa tài khoản. Vui lòng thử lại.";
    }
}
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    header('Location: users.php');
    exit;
}

$user = $user_result->fetch_assoc();

$orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
