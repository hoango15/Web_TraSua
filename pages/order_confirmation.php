<?php
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
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Confirmation - Bubble Tea Shop</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f8f8f8;
      margin: 0;
      padding: 0;
    }

    .confirmation-section {
      padding: 40px 20px;
      display: flex;
      justify-content: center;
    }

    .confirmation-container {
      background: white;
      max-width: 700px;
      width: 100%;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .confirmation-header h2 {
      color: #27ae60;
      margin-bottom: 10px;
    }

    .confirmation-header p {
      color: #555;
      margin-bottom: 30px;
    }

    .order-info .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      margin-bottom: 30px;
    }

    .info-item .label {
      font-weight: bold;
      color: #333;
    }

    .info-item .value {
      display: block;
      margin-top: 4px;
      color: #666;
    }

    .order-items-list {
      margin-bottom: 30px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 15px 0;
      border-bottom: 1px dashed #ccc;
    }

    .item-image img {
      width: 80px;
      height: 80px;
      border-radius: 6px;
      object-fit: cover;
    }

    .item-details {
      flex: 1;
      margin-left: 15px;
    }

    .item-details h4 {
      margin: 0 0 8px 0;
      color: #333;
    }

    .item-details p {
      margin: 0;
      font-size: 14px;
      color: #666;
    }

    .item-price {
      font-weight: bold;
      color: #000;
      min-width: 80px;
      text-align: right;
    }

    .order-summary {
      border-top: 2px solid #eee;
      padding-top: 20px;
    }

    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      color: #333;
    }

    .summary-row.total {
      font-size: 18px;
      font-weight: bold;
      border-top: 1px solid #ddd;
      padding-top: 10px;
      color: #000;
    }

    .confirmation-actions {
      margin-top: 30px;
      text-align: center;
    }

    .confirmation-actions .btn {
      padding: 10px 20px;
      border: none;
      background-color: #27ae60;
      color: white;
      font-weight: bold;
      border-radius: 5px;
      margin: 0 10px;
      text-decoration: none;
      display: inline-block;
      transition: background 0.2s ease-in-out;
    }

    .confirmation-actions .btn.secondary {
      background-color: #95a5a6;
    }

    .confirmation-actions .btn:hover {
      background-color: #2ecc71;
    }

    .confirmation-actions .btn.secondary:hover {
      background-color: #7f8c8d;
    }

    /* Status badge */
    .status-paid {
      color: green;
      font-weight: bold;
    }

    .status-pending {
      color: orange;
      font-weight: bold;
    }

    .status-cancelled {
      color: red;
      font-weight: bold;
    }
  </style>
</head>

<body>


  <section class="confirmation-section">
    <div class="container">
      <div class="confirmation-container">
        <div class="confirmation-header">
          <h2>Đơn hàng đã đặt thành công !</h2>
          <p>Cảm ơn bạn đã mua hàng của chúng tôi. Hân hạnh được phục vụ bạn </p>
        </div>

        <div class="order-details">
          <div class="order-info">
            <h3>Thông tin đơn hàng </h3>
            <div class="info-grid">
              <div class="info-item">
                <span class="label">Order Number:</span>
                <span class="value">#<?php echo $order['id']; ?></span>
              </div>
              <div class="info-item">
                <span class="label">Ngày :</span>
                <span class="value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
              </div>
              <div class="info-item">
                <span class="label">Phương thức thanh toán :</span>
                <span class="value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
              </div>
              <div class="info-item">
                <span class="label">Trạng thái :</span>
                <span
                  class="value status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
              </div>
            </div>
          </div>

          <div class="order-items-list">
            <h3>Chi tiết đơn hàng </h3>
            <?php foreach ($order_items as $item): ?>
              <div class="order-item">
                <div class="item-image">
                  <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                </div>
                <div class="item-details">
                  <h4><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></h4>
                  <p>
                    Đường : <?php echo $item['sugar_level']; ?>% |
                    Đá: <?php echo $item['ice_level']; ?>%
                    <?php if (!empty($item['toppings'])): ?>
                      <br>Toppings: <?php echo implode(', ', $item['toppings']); ?>
                    <?php endif; ?>
                  </p>
                </div>
                <div class="item-price">
                  <?php echo number_format($item['price'] * $item['quantity'], 0); ?>đ
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="order-summary">
            <div class="summary-row">
              <span>Giá vật phẩm :</span>
              <span><?php echo number_format($order['subtotal'], 0); ?>đ</span>
            </div>
            <div class="summary-row">
              <span>Phí vận chuyển :</span>
              <span><?php echo number_format($order['shipping_fee'], 0); ?>đ </span>
            </div>
            <div class="summary-row total">
              <span>Tổng Bill :</span>
              <span><?php echo number_format($order['total'], 0); ?>đ </span>
            </div>
          </div>
        </div>

        <div class="confirmation-actions">
          <a href="account.php" class="btn secondary">Xem đơn hàng </a>
          <a href="products.php" class="btn">Tiếp tục mua sắm </a>
        </div>
      </div>
    </div>
  </section>


  <script src="js/script.js"></script>
</body>

</html>