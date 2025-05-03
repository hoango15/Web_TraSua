<?php
session_start();
require_once '../config/database.php';


if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = $_GET['delete'];
    
    $check_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $category_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $product_count = $check_result->fetch_assoc()['count'];
    
    if ($product_count > 0) {
        $error_message = "Kh. It has $product_count products assigned to it.";
    } else {
        // Delete category
        $delete_sql = "DELETE FROM categories WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $category_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Category deleted successfully.";
        } else {
            $error_message = "Error deleting category.";
        }
    }
}

// Get all categories
$categories_sql = "SELECT c.*, COUNT(p.id) as product_count 
                  FROM categories c 
                  LEFT JOIN products p ON c.id = p.category_id 
                  GROUP BY c.id 
                  ORDER BY c.name";
$categories_result = $conn->query($categories_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý danh mục
  </title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản lý danh mục
        </h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="category-form.php" class="btn">Thêm danh mục mới </a>
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
              <th>Số lượng sản phẩm </th>
              <th>Ngày tạo </th>
              <th>Hoạt động </th>
            </tr>
          </thead>
          <tbody>
            <?php if ($categories_result->num_rows > 0): ?>
            <?php while ($category = $categories_result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $category['id']; ?></td>
              <td><?php echo $category['name']; ?></td>
              <td><?php echo $category['product_count']; ?></td>
              <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
              <td class="actions">
                <a href="category-form.php?id=<?php echo $category['id']; ?>" class="edit-btn">Sửa </a>
                <a href="categories.php?delete=<?php echo $category['id']; ?>" class="delete-btn"
                  onclick="return confirm('Are you sure you want to delete this category?')">Xóa </a>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
              <td colspan="5">Không có danh mục nào</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>