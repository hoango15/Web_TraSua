<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

$orders_sql = "SELECT o.*, u.name as user_name, u.email as user_email 
              FROM orders o 
              JOIN users u ON o.user_id = u.id 
              WHERE 1=1";

$params = [];
$param_types = "";

if (!empty($status_filter)) {
    $orders_sql .= " AND o.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if (!empty($date_filter)) {
    $orders_sql .= " AND DATE(o.created_at) = ?";
    $params[] = $date_filter;
    $param_types .= "s";
}

$orders_sql .= " ORDER BY o.created_at DESC";

$orders_stmt = $conn->prepare($orders_sql);

if (!empty($params)) {
    $orders_stmt->bind_param($param_types, ...$params);
}

$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý đơn hàng</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản lý đơn hàng</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="filter-container">
        <form method="GET" action="" class="filter-form">
          <div class="filter-group">
            <label for="status">Trạng thái:</label>
            <select id="status" name="status">
              <option value="">Trạng thái:</option>
              <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Chưa giải quyết
              </option>
              <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Xử lý
              </option>
              <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Hoàn thành
              </option>
              <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Hủy
              </option>
            </select>
          </div>

          <div class="filter-group">
            <label for="date">Ngày :</label>
            <input type="date" id="date" name="date" value="<?php echo $date_filter; ?>">
          </div>

          <div class="filter-actions">
            <button type="submit" class="btn">Lọc </button>
            <a href="orders.php" class="btn secondary">Reset</a>
          </div>
        </form>
      </div>

      <div class="content-table">
        <table>
          <thead>
            <tr>
              <th>Mã đơn hàng</th>
              <th>Khách hàng</th>
              <th>Tổng cộng</th>
              <th>Trạng thái</th>
              <th>Phương thức chi trả </th>
              <th>Ngày </th>
              <th>Hoạt động </th>
            </tr>
          </thead>
          <tbody>
            <?php if ($orders_result->num_rows > 0): ?>
            <?php while ($order = $orders_result->fetch_assoc()): ?>
            <tr>
              <td>#<?php echo $order['id']; ?></td>
              <td>
                <div><?php echo $order['user_name']; ?></div>
                <div class="small-text"><?php echo $order['user_email']; ?></div>
              </td>
              <td><?php echo number_format($order['total'], 0); ?>đ</td>
              <td>
                <span class="status-badge status-<?php echo $order['status']; ?>">
                  <?php echo ucfirst($order['status']); ?>
                </span>
              </td>
              <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
              <td><?php echo date('M d, Y, g:i a', strtotime($order['created_at'])); ?></td>
              <td class="actions">
                <a href="order-details.php?id=<?php echo $order['id']; ?>" class="view-btn">View</a>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
              <td colspan="7">Không có đơn hàng nào </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>