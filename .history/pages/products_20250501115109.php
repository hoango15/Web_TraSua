<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';


if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$categories = getAllCategories($conn);
$products = getProductsByCategory($conn, $category_id);

$page_title = "Sản Phẩm - " . SITE_NAME;
$active_menu = "products";
$extra_css = ['../assets/css/products.css'];