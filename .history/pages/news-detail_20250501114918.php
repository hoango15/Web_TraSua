<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: news.php');
  exit;
}

$news_id = $_GET['id'];
$news = getNewsById($conn, $news_id);