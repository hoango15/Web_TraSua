<?php
session_start();
header('Content-Type: application/json');


if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}