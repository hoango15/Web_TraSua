<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_after_login'] = '../pages/account.php';
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$redirect_tab = '../profile';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['name'])) {
    updateProfile($conn, $user_id);
    $redirect_tab = 'profile';
  } elseif (isset($_POST['address'])) {
    updateAddress($conn, $user_id);
    $redirect_tab = 'address';
  } elseif (isset($_POST['current_password'])) {
    changePassword($conn, $user_id);
    $redirect_tab = 'password';
  } else {
    $_SESSION['error_message'] = "Yêu cầu không hợp lệ";
  }
}
header("Location: account.php#$redirect_tab");
exit;