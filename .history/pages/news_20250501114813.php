<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
$news_items = getNewsItems($conn, 10);
$page_title = "Tin Tức - " . SITE_NAME;
$active_menu = "news";
$extra_css = ['../assets/css/news.css'];
include '../includes/header.php';
?>