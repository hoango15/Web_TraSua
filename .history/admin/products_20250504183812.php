<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Delete product
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $product_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Sản phẩm xóa thành công .";
    } else {
        $error_message = "Xóa sản phẩm đã bị lỗi .";
    }
}

// Get all products with category names
$products_sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.id DESC";
$products_result = $conn->query($products_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý sản phẩm</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản lý sản phẩm</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="product-form.php" class="btn">Thêm sản phẩm </a>
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
              <th>Ảnh </th>
              <th>Tên </th>
              <th>Loại sản phẩm </th>
              <th>Giá </th>
              <th>Hoạt động </th>
            </tr>
          </thead>
          <tbody>
            <?php if ($products_result->num_rows > 0): ?>
            <?php while ($product = $products_result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $product['id']; ?></td>
              <td>
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>"
                  class="product-thumbnail">
              </td>
              <td><?php echo $product['name']; ?></td>
              <td><?php echo $product['category_name']; ?></td>
              <td><?php echo number_format($product['price'], 0); ?>đ </td>
              <td class="actions">
                <a href="product-form.php?id=<?php echo $product['id']; ?>" class="edit-btn">Sửa</a>
                <a href="products.php?delete=<?php echo $product['id']; ?>" class="delete-btn"
                  onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này ?')">Xóa</a>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
              <td colspan="6">Không có sản phẩm nào </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>