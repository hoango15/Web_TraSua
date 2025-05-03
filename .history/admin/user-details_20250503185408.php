<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$user_id = $_GET['id'];

// Handle delete user request
if (isset($_POST['delete_user']) && $_POST['user_id'] == $user_id) {
    // First delete related records (orders)
    $delete_orders_sql = "DELETE FROM orders WHERE user_id = ?";
    $delete_orders_stmt = $conn->prepare($delete_orders_sql);
    $delete_orders_stmt->bind_param("i", $user_id);
    $delete_orders_stmt->execute();
    
    // Then delete the user
    $delete_user_sql = "DELETE FROM users WHERE id = ?";
    $delete_user_stmt = $conn->prepare($delete_user_sql);
    $delete_user_stmt->bind_param("i", $user_id);
    
    if ($delete_user_stmt->execute()) {
        // Redirect to users list with success message
        $_SESSION['success_message'] = "Tài khoản khách hàng đã được xóa thành công.";
        header('Location: users.php');
        exit;
    } else {
        $error_message = "Không thể xóa tài khoản. Vui lòng thử lại.";
    }
}


$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    header('Location: users.php');
    exit;
}

$user = $user_result->fetch_assoc();


$orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$orders_stmt = $conn->prepare($orders_sql);
$orders_stmt->bind_param("i", $user_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();


$total_orders = $orders_result->num_rows;
$total_spent = 0;

$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    $total_spent += $order['total'];
    $orders[] = $order;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chi tiết khách hàng </title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    /* Add styles for delete button */
    .btn.danger {
      background-color: #e74c3c;
      color: white;
      border: 1px solid #c0392b;
    }
    .btn.danger:hover {
      background-color: #c0392b;
    }
    .delete-form {
      display: inline;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
      background-color: #fefefe;
      margin: 15% auto;
      padding: 20px;
      border-radius: 5px;
      width: 400px;
      max-width: 80%;
    }
    .modal-actions {
      margin-top: 20px;
      text-align: right;
    }
    .modal-actions .btn {
      margin-left: 10px;
    }
  </style>
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Chi tiết khách hàng</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="users.php" class="btn secondary">← Quay lại </a>
        <button type="button" class="btn danger" onclick="showDeleteConfirmation()">Xóa tài khoản</button>
      </div>

      <?php if (isset($error_message)): ?>
      <div class="alert alert-danger">
        <?php echo $error_message; ?>
      </div>
      <?php endif; ?>

      <div class="user-details-container">
        <div class="user-profile">
          <h2><?php echo $user['name']; ?></h2>

          <div class="user-stats">
            <div class="stat-item">
              <span class="stat-label">Tổng đơn </span>
              <span class="stat-value"><?php echo $total_orders; ?></span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Tổng bill </span>
              <span class="stat-value"><?php echo number_format($total_spent, 0 ); ?>đ</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Ngày tạo </span>
              <span class="stat-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
            </div>
          </div>

          <div class="user-info-card">
            <h3>Thông tin liên hệ </h3>
            <div class="info-item">
              <span class="label">Email:</span>
              <span class="value"><?php echo $user['email']; ?></span>
            </div>
            <div class="info-item">
              <span class="label">Điện thoại :</span>
              <span class="value"><?php echo $user['phone'] ?? 'N/A'; ?></span>
            </div>
            <div class="info-item">
              <span class="label">Địa chỉ :</span>
              <span class="value"><?php echo nl2br($user['address'] ?? 'N/A'); ?></span>
            </div>
          </div>
        </div>

        <div class="user-orders">
          <h3>Lịch sử đơn hàng </h3>
          <?php if (count($orders) > 0): ?>
          <table>
            <thead>
              <tr>
                <th>ID đơn hàng </th>
                <th>Ngày </th>
                <th>Tổng bill </th>
                <th>Trạng thái </th>
                <th>Hoạt động </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
              <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo date('M d, Y, g:i a', strtotime($order['created_at'])); ?></td>
                <td><?php echo number_format($order['total'], 0); ?>đ </td>
                <td>
                  <span class="status-badge status-<?php echo $order['status']; ?>">
                    <?php echo ucfirst($order['status']); ?>
                  </span>
                </td>
                <td class="actions">
                  <a href="order-details.php?id=<?php echo $order['id']; ?>" class="view-btn">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <p class="no-data">Người dùng chưa có đơn hàng nào .</p>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

 
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <h3>Xác nhận xóa tài khoản</h3>
      <p>Bạn có chắc chắn muốn xóa tài khoản của <strong><?php echo $user['name']; ?></strong>?</p>
      <p>Hành động này không thể hoàn tác và sẽ xóa tất cả đơn hàng của khách hàng này.</p>
      
      <div class="modal-actions">
        <button type="button" class="btn secondary" onclick="hideDeleteConfirmation()">Hủy</button>
        <form method="POST" class="delete-form">
          <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
          <button type="submit" name="delete_user" class="btn danger">Xóa tài khoản</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function showDeleteConfirmation() {
      document.getElementById('deleteModal').style.display = 'block';
    }
    
    function hideDeleteConfirmation() {
      document.getElementById('deleteModal').style.display = 'none';
    }
    

    window.onclick = function(event) {
      var modal = document.getElementById('deleteModal');
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    }
  </script>
</body>

</html>