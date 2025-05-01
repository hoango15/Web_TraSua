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
