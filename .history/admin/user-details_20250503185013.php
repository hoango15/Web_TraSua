<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}