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
          $coupon_success = "Áp dụng mã giảm " . $coupon['discount_value'] . "% thành công.";
        } else {
          $discount_amount = $coupon['discount_value'];
          if ($discount_amount > $subtotal) {
            $discount_amount = $subtotal;
          }
          $coupon_success = "Áp dụng mã giảm " . number_format($coupon['discount_value'], 0) . "đ thành công.";
        }
      }
    } else {
      $coupon_error = "Mã giảm giá không hợp lệ hoặc đã hết hạn.";
    }
  }
}
if (isset($_POST['remove_coupon'])) {
  $coupon_code = '';
  $discount_amount = 0;
  $coupon_success = '';
}
$shipping_fee = 20000;
$total = $subtotal + $shipping_fee - $discount_amount;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
  $conn->begin_transaction();

  try {
    $payment_method = $_POST['payment_method'];
    $address = $_POST['address'];
    $city = $_POST['city'] ?? '';
    $district = $_POST['district'] ?? '';
    $delivery_notes = $_POST['notes'] ?? '';
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $payment_status = 'pending';
    $status = 'pending';
    $order_sql = "INSERT INTO orders (user_id, name, email, phone, address, city, district, delivery_notes, 
                  subtotal, shipping_fee, discount, coupon_code, total, status, payment_method, payment_status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param(
      "isssssssdddsssss",
      $user_id,
      $name,
      $email,
      $phone,
      $address,
      $city,
      $district,
      $delivery_notes,
      $subtotal,
      $shipping_fee,
      $discount_amount,
      $coupon_code,
      $total,
      $status,
      $payment_method,
      $payment_status
    );
    $order_stmt->execute();

    $order_id = $conn->insert_id;
    foreach ($cart_items as $item) {
      $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price, sugar_level, ice_level) 
                   VALUES (?, ?, ?, ?, ?, ?)";
      $item_stmt = $conn->prepare($item_sql);
      $item_stmt->bind_param("iiidii", $order_id, $item['product']['id'], $item['quantity'], $item['item_price'], $item['sugar_level'], $item['ice_level']);
      $item_stmt->execute();

      $order_item_id = $conn->insert_id;
      if (!empty($item['topping_data'])) {
        foreach ($item['topping_data'] as $topping) {
          $topping_sql = "INSERT INTO order_item_toppings (order_item_id, topping_id, topping_name) VALUES (?, ?, ?)";
          $topping_stmt = $conn->prepare($topping_sql);
          $topping_stmt->bind_param("iis", $order_item_id, $topping['id'], $topping['name']);
          $topping_stmt->execute();
        }
      }
    }

    $conn->commit();
    $_SESSION['cart'] = [];
    header('Location: payment.php?order_id=' . $order_id);
    exit;
  } catch (Exception $e) {
    $conn->rollback();
    $error_message = "Lỗi khi xử lý đơn hàng. Vui lòng thử lại. Chi tiết: " . $e->getMessage();
  }
}
