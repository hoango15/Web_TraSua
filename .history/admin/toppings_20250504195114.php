<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle topping deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $topping_id = $_GET['delete'];
    
   
    $delete_sql = "DELETE FROM toppings WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $topping_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Topping deleted successfully.";
    } else {
        $error_message = "Error deleting topping.";
    }
}

// Get all toppings
$toppings_sql = "SELECT * FROM toppings ORDER BY name";
$toppings_result = $conn->query($toppings_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý Topping</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản lý Topping</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="topping-form.php" class="btn">Thêm mới topping </a>
      </div>

      <?php if (isset($success_message)): ?>
      <div class="success-message">
        <?php echo $success_message; ?>
      </div>
      <?php endif; ?>

      <?php if (isset($error_message)): ?>
      <div class="error-message">
        <?php echo $error_message; ?>
      </div>
      <?php endif; ?>

      <div class="content-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Tên </th>
              <th>Giá </th>
              <th>Ngày tạo </th>
              <th>Hoạt động </th>
            </tr>
          </thead>
          <tbody>
            <?php if ($toppings_result->num_rows > 0): ?>
            <?php while ($topping = $toppings_result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $topping['id']; ?></td>
              <td><?php echo $topping['name']; ?></td>
              <td><?php echo number_format($topping['price'], 0); ?>đ </td>
              <td><?php echo date('M d, Y', strtotime($topping['created_at'])); ?></td>
              <td class="actions">
                <a href="topping-form.php?id=<?php echo $topping['id']; ?>" class="edit-btn">Sửa </a>
                <a href="toppings.php?delete=<?php echo $topping['id']; ?>" class="delete-btn"
                  onclick="return confirm('Bạn có chắc muốn xóa topping này hay không ?')">Xóa </a>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
              <td colspan="5">Không có toppings nào </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>