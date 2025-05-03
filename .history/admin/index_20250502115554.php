<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

$products_count = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$categories_count = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

$recent_orders_sql = "SELECT o.*, u.name as user_name FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.created_at DESC LIMIT 5";
$recent_orders = $conn->query($recent_orders_sql);

$sales_data_sql = "SELECT DATE(created_at) as date, SUM(total) as total 
                   FROM orders 
                   WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                   GROUP BY DATE(created_at) 
                   ORDER BY date";
$sales_result = $conn->query($sales_data_sql);

$dates = [];
$sales = [];

while ($row = $sales_result->fetch_assoc()) {
    $dates[] = date('M d', strtotime($row['date']));
    $sales[] = $row['total'];
}
$popular_products_sql = "SELECT p.name, COUNT(oi.id) as order_count 
                         FROM products p 
                         JOIN order_items oi ON p.id = oi.product_id 
                         GROUP BY p.id 
                         ORDER BY order_count DESC 
                         LIMIT 5";
$popular_products = $conn->query($popular_products_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin- KTea Shop</title>
  <link rel="stylesheet" href="css/admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Dashboard</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="dashboard">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon products-icon">
              <i class="icon">🧋</i>
            </div>
            <div class="stat-info">
              <h3>Sản phẩm </h3>
              <p><?php echo $products_count; ?></p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon orders-icon">
              <i class="icon">📋</i>
            </div>
            <div class="stat-info">
              <h3>Đơn hàng </h3>
              <p><?php echo $orders_count; ?></p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon users-icon">
              <i class="icon">👥</i>
            </div>
            <div class="stat-info">
              <h3>Khách hàng </h3>
              <p><?php echo $users_count; ?></p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon categories-icon">
              <i class="icon">🗂️</i>
            </div>
            <div class="stat-info">
              <h3>Loại sản phẩm </h3>
              <p><?php echo $categories_count; ?></p>
            </div>
          </div>
        </div>

        <div class="dashboard-grid">
          <div class="chart-container">
            <h2>Doanh thu trong 7 ngày </h2>
            <canvas id="salesChart"></canvas>
          </div>

          <div class="recent-orders">
            <h2>Lịch sử đơn hàng </h2>
            <table>
              <thead>
                <tr>
                  <th>ID đơn hàng</th>
                  <th>Khách hàng </th>
                  <th>Tổng Bill </th>
                  <th>Trạng thái </th>
                  <th>Ngày </th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recent_orders->num_rows > 0): ?>
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                <tr>
                  <td>#<?php echo $order['id']; ?></td>
                  <td><?php echo $order['user_name']; ?></td>
                  <td><?php echo number_format($order['total'], 0 ); ?>đ</td>
                  <td>
                    <span class="status-badge status-<?php echo $order['status']; ?>">
                      <?php echo ucfirst($order['status']); ?>
                    </span>
                  </td>
                  <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                  <td colspan="5">Không có đơn nào </td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
            <a href="orders.php" class="view-all">Xem tất cả đơn hàng </a>
          </div>

          <div class="popular-products">
            <h2>Sản phẩm được ưu thích </h2>
            <ul>
              <?php if ($popular_products->num_rows > 0): ?>
              <?php while ($product = $popular_products->fetch_assoc()): ?>
              <li>
                <span class="product-name"><?php echo $product['name']; ?></span>
                <span class="product-count"><?php echo $product['order_count']; ?> đơn hàng </span>
              </li>
              <?php endwhile; ?>
              <?php else: ?>
              <li>Chưa có dữ liệu </li>
              <?php endif; ?>
            </ul>
          </div>
        </div>
      </div>
    </main>
  </div>
