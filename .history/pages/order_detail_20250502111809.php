<?php
define('SITE_NAME', 'KTea shop');
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: orders.php');
  exit;
}
$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$order_sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
  header('Location: orders.php');
  exit;
}
$order = $order_result->fetch_assoc();

$items_sql = "SELECT oi.*, p.name, p.image FROM order_items oi 
JOIN products p ON oi.product_id = p.id 
WHERE oi.order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$order_items = [];
while ($item = $items_result->fetch_assoc()) {
  $toppings_sql = "SELECT topping_name FROM order_item_toppings WHERE order_item_id = ?";
  $toppings_stmt = $conn->prepare($toppings_sql);
  $toppings_stmt->bind_param("i", $item['id']);
  $toppings_stmt->execute();
  $toppings_result = $toppings_stmt->get_result();

  $toppings = [];
  while ($topping = $toppings_result->fetch_assoc()) {
    $toppings[] = $topping['topping_name'];
  }

  $item['toppings'] = $toppings;
  $order_items[] = $item;
}



?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Chi tiết đơn hàng</title>
  <link rel="stylesheet" href="../css/style.css">

  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 800px;
      margin: 40px auto;
      padding: 30px;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    h2,
    h3 {
      color: #333;
      margin-bottom: 20px;
    }

    p {
      font-size: 16px;
      line-height: 1.6;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    table thead {
      background-color: #ffc107;
      color: #000;
    }

    table th,
    table td {
      padding: 12px 15px;
      text-align: center;
      border: 1px solid #ddd;
    }

    table tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }

    .status-pending {
      color: #ffc107;
      font-weight: bold;
    }

    .status-completed {
      color: #28a745;
      font-weight: bold;
    }

    .status-cancelled {
      color: #dc3545;
      font-weight: bold;
    }

    .btn.secondary {
      display: inline-block;
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #6c757d;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background-color 0.3s ease;
    }

    .btn.secondary:hover {
      background-color: #5a6268;
    }
  </style>
</head>

<body>


  <div class="container">
    <h2>Chi tiết đơn hàng #<?php echo $order['id']; ?>
    </h2>

    <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
    <p><strong>Thanh toán:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
    <p><strong>Trạng thái:</strong> <span
        class="status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>

    <h3>Sản phẩm</h3>
    <table>
      <thead>
        <tr>
          <th>Sản phẩm</th>
          <th>SL</th>
          <th>Đường</th>
          <th>Đá</th>
          <th>Topping</th>
          <th>Giá</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($order_items as $item): ?>
          <tr>
            <td><?php echo $item['name']; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td><?php echo $item['sugar_level']; ?>%</td>
            <td><?php echo $item['ice_level']; ?>%</td>
            <td><?php echo implode(', ', $item['toppings']); ?></td>
            <td><?php echo number_format($item['price'] * $item['quantity'], 0); ?>đ</td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h3>Tổng cộng</h3>
    <p><strong>Tạm tính:</strong> <?php echo number_format($order['subtotal'], 0); ?>đ</p>
    <p><strong>Phí vận chuyển:</strong> <?php echo number_format($order['shipping_fee'], 0); ?>đ</p>
    <p><strong>Tổng thanh toán:</strong> <strong><?php echo number_format($order['total'], 0); ?>đ</strong></p>

    <a href="account.php" class="btn secondary">Quay lại danh sách đơn hàng</a>
  </div>
</body>

</html>