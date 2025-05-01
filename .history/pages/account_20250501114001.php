<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_after_login'] = 'pages/account.php';
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id);
$orders = getUserOrders($conn, $user_id);

$page_title = "Tài Khoản - " . SITE_NAME;
$active_menu = "account";
$extra_css = ['../assets/css/account.css'];
