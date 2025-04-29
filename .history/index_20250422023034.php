<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';

$featured_products = getTopRatedProducts($conn,8);

$news_items = getNewsItems($conn, 4);

$slider_images = getSliderImages($conn);

$popup_ad = getActivePopupAd($conn);