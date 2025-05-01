<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (empty($_SESSION['cart'])) {
  header('Location: cart.php');
  exit;
}
$user = null;
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $user_sql = "SELECT * FROM users WHERE id = ?";
  $user_stmt = $conn->prepare($user_sql);
  $user_stmt->bind_param("i", $user_id);
  $user_stmt->execute();
  $user_result = $user_stmt->get_result();
  $user = $user_result->fetch_assoc();
}
$cart_items = [];
$subtotal = 0;

foreach ($_SESSION['cart'] as $cart_id => $item) {
  $product_id = $item['product_id'];
  $sql = "SELECT * FROM products WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $product_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    $item_price = $product['price'];
    if ($product['discount_price'] > 0) {
      $item_price = $product['discount_price'];
    }

    $item_price += count($item['toppings']) * 5000;

    $item_total = $item_price * $item['quantity'];
    $subtotal += $item_total;
    $topping_names = [];
    $topping_data = []; 
    if (!empty($item['toppings'])) {
      $topping_ids = implode(',', $item['toppings']);
      $topping_sql = "SELECT id, name FROM toppings WHERE id IN ($topping_ids)";
      $topping_result = $conn->query($topping_sql);
      while ($topping = $topping_result->fetch_assoc()) {
        $topping_names[] = $topping['name'];
        $topping_data[] = [
          'id' => $topping['id'],
          'name' => $topping['name']
        ];
      }
    }
    $cart_items[] = [
      'cart_id' => $cart_id,
      'product' => $product,
      'quantity' => $item['quantity'],
      'sugar_level' => $item['sugar_level'],
      'ice_level' => $item['ice_level'],
      'toppings' => $topping_names,
      'topping_data' => $topping_data, // Store the complete topping data
      'item_price' => $item_price,
      'item_total' => $item_total
    ];
  }
}
$discount_amount = 0;
$coupon_code = '';
$coupon_error = '';
$coupon_success = '';


if (isset($_POST['apply_coupon'])) {
  $coupon_code = trim($_POST['coupon_code']);

  if (empty($coupon_code)) {
    $coupon_error = "Vui lòng nhập mã giảm giá.";
  } else {
    $coupon_sql = "SELECT * FROM coupons WHERE code = ? AND active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()";
    $coupon_stmt = $conn->prepare($coupon_sql);
    $coupon_stmt->bind_param("s", $coupon_code);
    $coupon_stmt->execute();
    $coupon_result = $coupon_stmt->get_result();

    if ($coupon_result->num_rows > 0) {
      $coupon = $coupon_result->fetch_assoc();

      if ($subtotal < $coupon['min_order_value']) {
        $coupon_error = "Đơn hàng tối thiểu phải từ " . number_format($coupon['min_order_value'], 0) . "đ để sử dụng mã này.";
      } else {
        if ($coupon['discount_type'] == 'percentage') {
          $discount_amount = ($subtotal * $coupon['discount_value']) / 100;
          if ($coupon['max_discount'] !== null && $discount_amount > $coupon['max_discount']) {
            $discount_amount = $coupon['max_discount'];
          }