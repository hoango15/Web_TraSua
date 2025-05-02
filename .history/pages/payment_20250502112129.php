<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['order_id'])) {
  header('Location: index.php');
  exit;
}

$order_id = $_GET['order_id'];
$order_sql = "SELECT * FROM orders WHERE id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
  header('Location: index.php');
  exit;
}
$order = $order_result->fetch_assoc();

if ($order['payment_status'] === 'paid') {
  header('Location: order_confirmation.php?id=' . $order_id);
  exit;
}

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
$payment_method = $order['payment_method'];
$payment_status = '';
$transaction_id = '';
$response_data = '';
$error_message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'confirm_momo') {
    $transaction_id = 'MOMO_' . time() . rand(1000, 9999);
    $payment_status = 'paid';
    $response_data = json_encode([
      'status' => 'success',
      'message' => 'Payment successful',
      'transaction_id' => $transaction_id,
      'time' => date('Y-m-d H:i:s')
    ]);

    $update_order_sql = "UPDATE orders SET payment_status = ? WHERE id = ?";
    $update_order_stmt = $conn->prepare($update_order_sql);
    $update_order_stmt->bind_param("si", $payment_status, $order_id);
    $log_payment_sql = "INSERT INTO payment_logs (order_id, payment_method, transaction_id, amount, status, response_data, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, NOW())";

$log_payment_stmt = $conn->prepare($log_payment_sql);
$log_payment_stmt->bind_param("issdss", $order_id, $payment_method, $transaction_id, $order['total'], $payment_status, $response_data);

$conn->begin_transaction();

try {
$update_order_stmt->execute();
$log_payment_stmt->execute();
$conn->commit();
$success = true;


 header('Location: order_confirmation.php?id=' . $order_id);
      exit;
    } catch (Exception $e) {
      $conn->rollback();
      $error_message = "Lỗi xử lý thanh toán: " . $e->getMessage();
    }
  } elseif ($_POST['action'] === 'cancel_payment') {
    $payment_status = 'cancelled';
    $response_data = json_encode([
      'status' => 'cancelled',
      'message' => 'Payment cancelled by user',
      'time' => date('Y-m-d H:i:s')
    ]);
    $update_order_sql = "UPDATE orders SET status = 'cancelled', payment_status = ? WHERE id = ?";
    $update_order_stmt = $conn->prepare($update_order_sql);
    $update_order_stmt->bind_param("si", $payment_status, $order_id);

    $log_payment_sql = "INSERT INTO payment_logs (order_id, payment_method, transaction_id, amount, status, response_data, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $transaction_id = 'CANCEL_' . time();
    $log_payment_stmt = $conn->prepare($log_payment_sql);
    $log_payment_stmt->bind_param("issdss", $order_id, $payment_method, $transaction_id, $order['total'], $payment_status, $response_data);

    $conn->begin_transaction();

    try {
      $update_order_stmt->execute();
      $log_payment_stmt->execute();
      $conn->commit();
      header('Location: cart.php?cancelled=1');
      exit;
    } catch (Exception $e) {
      $conn->rollback();
      $error_message = "Lỗi hủy thanh toán: " . $e->getMessage();
    }
  }
} elseif ($payment_method === 'cod') {
  $transaction_id = 'COD_' . time();
  $payment_status = 'pending';
  $response_data = json_encode([
    'status' => 'pending',
    'message' => 'Payment will be collected on delivery',
    'time' => date('Y-m-d H:i:s')
  ]);
  $log_payment_sql = "INSERT INTO payment_logs (order_id, payment_method, transaction_id, amount, status, response_data, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
  $log_payment_stmt = $conn->prepare($log_payment_sql);
  $log_payment_stmt->bind_param("issdss", $order_id, $payment_method, $transaction_id, $order['total'], $payment_status, $response_data);

  try {
    $log_payment_stmt->execute();
    header('Location: order_confirmation.php?id=' . $order_id);
    exit;
  } catch (Exception $e) {
    $error_message = "Lỗi xử lý thanh toán: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanh toán đơn hàng #<?php echo $order_id; ?> - Bubble Tea Shop</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .payment-container {
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
    }

    .payment-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .payment-details {
      background-color: #f9f9f9;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 30px;
    }

    .payment-details h3 {
      margin-top: 0;
      border-bottom: 1px solid #ddd;
      padding-bottom: 10px;
    }

    .payment-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }

    .payment-actions {
      display: flex;
      justify-content: space-between;
      margin-top: 30px;
    }

    .qr-container {
      text-align: center;
      margin: 30px 0;
    }

    .qr-code {
      max-width: 200px;
      margin: 0 auto;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 8px;
      background-color: white;
    }

    .qr-code img {
      width: 200%;
      height: auto;
    }

    .payment-instructions {
      background-color: #f0f8ff;
      border-radius: 8px;
      padding: 15px;
      margin: 20px 0;
    }

    .payment-instructions ol {
      margin-left: 20px;
      padding-left: 0;
    }

    .payment-instructions li {
      margin-bottom: 10px;
    }

    .error-message {
      background-color: #ffebee;
      color: #c62828;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }

    .success-message {
      background-color: #e8f5e9;
      color: #2e7d32;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 20px;
    }

    .order-items-summary {
      margin-top: 20px;
    }

    .order-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .order-item:last-child {
      border-bottom: none;
    }

    .item-details {
      flex: 1;
    }

    .item-price {
      text-align: right;
      min-width: 100px;
    }

    .countdown {
      text-align: center;
      margin: 20px 0;
      font-size: 1.2em;
      font-weight: bold;
      color: #e53935;
    }
  </style>
</head>

<body>
  <section class="payment-section">
    <div class="container">
      <div class="payment-container">
        <div class="payment-header">
          <h2>Thanh toán đơn hàng #<?php echo $order_id; ?></h2>
        </div>

        <?php if (!empty($error_message)): ?>
          <div class="error-message">
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="success-message">
            Thanh toán thành công! Đơn hàng của bạn đang được xử lý.
          </div>
        <?php endif; ?>

        <div class="payment-details">
          <h3>Chi tiết đơn hàng</h3>
          <div class="payment-row">
            <span>Mã đơn hàng:</span>
            <span>#<?php echo $order_id; ?></span>
          </div>
          <div class="payment-row">
            <span>Người nhận:</span>
            <span><?php echo htmlspecialchars($order['name']); ?></span>
          </div>
          <div class="payment-row">
            <span>Địa chỉ:</span>
            <span><?php echo htmlspecialchars($order['address']); ?></span>
          </div>
          <?php if (!empty($order['city']) || !empty($order['district'])): ?>
            <div class="payment-row">
              <span>Khu vực:</span>
              <span>
                <?php
                $location = [];
                if (!empty($order['district'])) $location[] = htmlspecialchars($order['district']);
                if (!empty($order['city'])) $location[] = htmlspecialchars($order['city']);
                echo implode(', ', $location);
                ?>
              </span>
            </div>
          <?php endif; ?>
          <div class="payment-row">
            <span>Số điện thoại:</span>
            <span><?php echo htmlspecialchars($order['phone']); ?></span>
          </div>

          <div class="order-items-summary">
            <h4>Sản phẩm</h4>
            <?php foreach ($order_items as $item): ?>
              <div class="order-item">
                <div class="item-details">
                  <div><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></div>
                  <div class="item-options">
                    Đường: <?php echo $item['sugar_level']; ?>%,
                    Đá: <?php echo $item['ice_level']; ?>%
                    <?php if (!empty($item['toppings'])): ?>
                      <br>Toppings: <?php echo implode(', ', $item['toppings']); ?>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="item-price">
                  <?php echo number_format($item['price'] * $item['quantity'], 0); ?>đ
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="payment-row">
            <span>Tạm tính:</span>
            <span><?php echo number_format($order['subtotal'], 0); ?>đ</span>
          </div>
          <div class="payment-row">
            <span>Phí vận chuyển:</span>
            <span><?php echo number_format($order['shipping_fee'], 0); ?>đ</span>
          </div>
          <div class="payment-row" style="font-weight: bold; font-size: 1.1em;">
            <span>Tổng tiền:</span>
            <span><?php echo number_format($order['total'], 0); ?>đ</span>
          </div>
          <div class="payment-row">
            <span>Phương thức thanh toán:</span>
            <span>
              <?php
              if ($payment_method === 'cod') {
                echo 'Thanh toán khi nhận hàng';
              } elseif ($payment_method === 'momo') {
                echo 'Thanh toán qua Momo';
              }
              ?>
            </span>
          </div>
          <div class="payment-row">
            <span>Trạng thái:</span>
            <span>
              <?php
              if ($order['payment_status'] === 'pending') {
                echo 'Chờ thanh toán';
              } elseif ($order['payment_status'] === 'paid') {
                echo 'Đã thanh toán';
              } else {
                echo htmlspecialchars($order['payment_status']);
              }
              ?>
            </span>
          </div>
        </div>

        <?php if ($payment_method === 'momo' && $order['payment_status'] !== 'paid'): ?>
          <div class="payment-instructions">
            <h3>Hướng dẫn thanh toán qua Momo</h3>
            <ol>
              <li>Mở ứng dụng Momo trên điện thoại của bạn</li>
              <li>Chọn "Quét mã QR"</li>
              <li>Quét mã QR bên dưới</li>
              <li>Nhập số tiền: <?php echo number_format($order['total'], 0); ?>đ</li>
              <li>Xác nhận thanh toán trên ứng dụng Momo</li>
              <li>Sau khi thanh toán thành công, nhấn nút "Xác nhận đã thanh toán" bên dưới</li>
            </ol>
          </div>

          <div class="countdown" id="countdown">
            Thời gian thanh toán còn lại: 15:00
          </div>

          <div class="qr-container">
            <div class="qr-code">
              <img src="../assets/img/momo.jpg" alt="Mã QR Momo" onerror="this.src='../assets/img/momo.jpg'">
            </div>
            <p>Quét mã QR để thanh toán <?php echo number_format($order['total'], 0); ?>đ</p>
          </div>

          <form method="POST" class="payment-actions">
            <button type="submit" name="action" value="confirm_momo" class="btn">Xác nhận đã thanh toán</button>
            <button type="submit" name="action" value="cancel_payment" class="btn secondary">Hủy thanh toán</button>
          </form>
        <?php endif; ?>

        <?php if ($payment_method === 'cod'): ?>
          <div class="payment-instructions">
            <h3>Thông tin thanh toán khi nhận hàng</h3>
            <p>Bạn đã chọn phương thức thanh toán khi nhận hàng. Vui lòng chuẩn bị số tiền
              <?php echo number_format($order['total'], 0); ?>đ khi nhận hàng.</p>
            <p>Đơn hàng của bạn đang được xử lý và sẽ được giao trong thời gian sớm nhất.</p>
          </div>

          <div class="payment-actions">
            <a href="order_confirmation.php?id=<?php echo $order_id; ?>" class="btn">Xem chi tiết đơn hàng</a>
            <a href="index.php" class="btn secondary">Tiếp tục mua sắm</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php if ($payment_method === 'momo' && $order['payment_status'] !== 'paid'): ?>
    <script>

function startCountdown(duration, display) {
        var timer = duration,
          minutes, seconds;
        var countdownInterval = setInterval(function() {
          minutes = parseInt(timer / 60, 10);
          seconds = parseInt(timer % 60, 10);

          minutes = minutes < 10 ? "0" + minutes : minutes;
          seconds = seconds < 10 ? "0" + seconds : seconds;

          display.textContent = "Thời gian thanh toán còn lại: " + minutes + ":" + seconds;

          if (--timer < 0) {
            clearInterval(countdownInterval);
            display.textContent = "Hết thời gian thanh toán!";