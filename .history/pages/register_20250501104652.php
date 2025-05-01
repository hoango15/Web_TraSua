<?php
session_start();
require_once('../config/database.php');

if (isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = 'Vui lòng điền đầy đủ thông tin.';
  } elseif ($password !== $confirm_password) {
    $error = 'Mật khẩu xác nhận không khớp.';
  } elseif (strlen($password) < 6) {
    $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
  } else {