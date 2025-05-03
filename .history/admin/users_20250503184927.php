<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}
$users_sql = "SELECT u.*, COUNT(o.id) as order_count, SUM(o.total) as total_spent 
             FROM users u 
             LEFT JOIN orders o ON u.id = o.user_id 
             GROUP BY u.id 
             ORDER BY u.created_at DESC";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lí khách hàng</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản lí khách hàng </h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Tên </th>
              <th>Email</th>
              <th>Ngày tạo </th>
              <th>Số đơn hàng </th>
              <th>Tổng bill </th>
              <th>Hoạt động </th>
            </tr>
          </thead>
          <tbody>
            <?php if ($users_result->num_rows > 0): ?>
              <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo $user['id']; ?></td>
                  <td><?php echo $user['name']; ?></td>
                  <td><?php echo $user['email']; ?></td>
                  <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                  <td><?php echo $user['order_count']; ?></td>
                  <td><?php echo number_format($user['total_spent'] ?? 0, 0); ?>đ</td>
                  <td class="actions">
                    <a href="user-details.php?id=<?php echo $user['id']; ?>" class="view-btn">View</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7">Không tìm thấy tài khoản nào </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>