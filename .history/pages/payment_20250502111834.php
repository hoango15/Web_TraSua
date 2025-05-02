<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['order_id'])) {
  header('Location: index.php');
  exit;
}

$order_id = $_GET['order_id'];