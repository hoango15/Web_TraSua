<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
$news_items = getNewsItems($conn, 10);