<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: orders.php');
  exit;
}

$order_id = $_GET['id'];

$order_sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE o.id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $order_id);
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

// Handle status update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
  $new_status = $_POST['status'];

  // Update order status
  $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
  $update_stmt = $conn->prepare($update_sql);
  $update_stmt->bind_param("si", $new_status, $order_id);

  if ($update_stmt->execute()) {
    $success_message = "Trạng thái đơn hàng đã cập nhật thành công .";
    $order['status'] = $new_status;
  } else {
    $error_message = "Lỗi khi đổi trạng thái .";
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết đơn hàng #<?php echo $order_id; ?> - KTea Shop Admin</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Đơn hàng #<?php echo $order_id; ?> Chi tiết </h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="orders.php" class="btn secondary">← Quay lại </a>

        <form method="POST" action="" class="status-form">
          <select name="status" id="status">
            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing
            </option>
            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed
            </option>
            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled
            </option>
          </select>
          <button type="submit" class="btn">Update Status</button>
        </form>
      </div>

      <?php if (!empty($success_message)): ?>
      <div class="success-message">
        <?php echo $success_message; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
      <div class="error-message">
        <?php echo $error_message; ?>
      </div>
      <?php endif; ?>

      <div class="order-details-container">
        <div class="order-info-grid">
          <div class="order-info-card">
            <h3>Thông tin đơn hàng </h3>
            <div class="info-item">
              <span class="label">ID đơn hàng :</span>
              <span class="value">#<?php echo $order['id']; ?></span>
            </div>
            <div class="info-item">
              <span class="label">Ngày :</span>
              <span class="value"><?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="info-item">
              <span class="label">Trạng thái :</span>
              <span
                class="value status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
            </div>
            <div class="info-item">
              <span class="label">Phương thức thanh toán :</span>
              <span class="value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
            </div>
          </div>

          <div class="order-info-card">
            <h3>Thông tin khách hàng </h3>
            <div class="info-item">
              <span class="label">Tên :</span>
              <span class="value"><?php echo $order['user_name']; ?></span>
            </div>
            <div class="info-item">
              <span class="label">Email:</span>
              <span class="value"><?php echo $order['user_email']; ?></span>
            </div>
            <div class="info-item">
              <span class="label">Số điện thoại :</span>
              <span class="value"><?php echo $order['user_phone'] ?? 'N/A'; ?></span>
            </div>
            <div class="info-item">
              <span class="label">Địa chỉ nhận hàng :</span>
              <span class="value"><?php echo nl2br($order['address']); ?></span>
            </div>
          </div>
        </div>

        <div class="order-items-container">
          <h3>Vật phẩm đơn hàng </h3>
          <table>
            <thead>
              <tr>
                <th>Vật phẩm </th>
                <th>Tỉ lệ </th>
                <th>Giá </th>
                <th>Số lượng </th>
                <th>Tổng </th>
              </tr>
            </thead>
            <tbody>
              <?php while ($item = $items_result->fetch_assoc()): ?>
              <?php
                // Get toppings for this item
                $toppings_sql = "SELECT topping_name FROM order_item_toppings WHERE order_item_id = ?";
                $toppings_stmt = $conn->prepare($toppings_sql);
                $toppings_stmt->bind_param("i", $item['id']);
                $toppings_stmt->execute();
                $toppings_result = $toppings_stmt->get_result();

                $toppings = [];
                while ($topping = $toppings_result->fetch_assoc()) {
                  $toppings[] = $topping['topping_name'];
                }
                ?>
              <tr>
                <td class="product-cell">
                  <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="product-thumbnail">
                  <span><?php echo $item['name']; ?></span>
                </td>
                <td>
                  <div>Đường : <?php echo $item['sugar_level']; ?>%</div>
                  <div>Đá : <?php echo $item['ice_level']; ?>%</div>
                  <?php if (!empty($toppings)): ?>
                  <div>Toppings: <?php echo implode(', ', $toppings); ?></div>
                  <?php endif; ?>
                </td>
                <td><?php echo number_format($item['price'], 0); ?>đ </td>
                <td><?php echo $item['quantity']; ?></td>
                <td><?php echo number_format($item['price'] * $item['quantity'], 0); ?>đ </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
            <tfoot>

              <tr>
                <td colspan="4" class="text-right">Phí vận chuyển :</td>
                <td><?php echo number_format($order['shipping_fee'], 0); ?>đ </td>
              </tr>
              <tr class="total-row">
                <td colspan="4" class="text-right">Tổng Bill :</td>
                <td><?php echo number_format($order['total'], 0); ?>đ </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </main>
  </div>
</body>

</html>