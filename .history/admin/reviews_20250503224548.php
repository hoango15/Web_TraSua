<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $review_id = $_GET['delete'];

    $delete_sql = "DELETE FROM product_reviews WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $review_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Đánh giá đã được xóa thành công.";
    } else {
        $error_message = "Lỗi khi xóa đánh giá.";
    }
}

$product_filter = isset($_GET['product']) ? $_GET['product'] : '';
$rating_filter = isset($_GET['rating']) ? $_GET['rating'] : '';

$reviews_sql = "SELECT r.*, p.name as product_name, u.name as user_name 
               FROM product_reviews r 
               JOIN products p ON r.product_id = p.id 
               JOIN users u ON r.user_id = u.id 
               WHERE 1=1";

$params = [];
$param_types = "";

if (!empty($product_filter)) {
    $reviews_sql .= " AND r.product_id = ?";
    $params[] = $product_filter;
    $param_types .= "i";
}

if (!empty($rating_filter)) {
    $reviews_sql .= " AND r.rating = ?";
    $params[] = $rating_filter;
    $param_types .= "i";
}

$reviews_sql .= " ORDER BY r.created_at DESC";

$reviews_stmt = $conn->prepare($reviews_sql);

if (!empty($params)) {
    $reviews_stmt->bind_param($param_types, ...$params);
}

$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();
$reviews = [];

if ($reviews_result->num_rows > 0) {
    while ($row = $reviews_result->fetch_assoc()) {
        $reviews[] = $row;
    }
}

// Get all products for filter dropdown
$products_sql = "SELECT id, name FROM products ORDER BY name";
$products_result = $conn->query($products_sql);
$products = [];

if ($products_result->num_rows > 0) {
    while ($row = $products_result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Đánh Giá - Bubble Tea Shop Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
  .star-rating {
    color: #FFD700;
    font-size: 18px;
  }

  .review-comment {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  </style>
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản Lý Đánh Giá</h1>
        <div class="user-info">
          <span>Xin chào, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
      </header>

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

      <div class="filter-container">
        <form method="GET" action="" class="filter-form">
          <div class="filter-group">
            <label for="product">Sản phẩm:</label>
            <select id="product" name="product">
              <option value="">Tất cả sản phẩm</option>
              <?php foreach ($products as $product): ?>
              <option value="<?php echo $product['id']; ?>"
                <?php echo $product_filter == $product['id'] ? 'selected' : ''; ?>>
                <?php echo $product['name']; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="filter-group">
            <label for="rating">Đánh giá:</label>
            <select id="rating" name="rating">
              <option value="">Tất cả đánh giá</option>
              <option value="5" <?php echo $rating_filter == '5' ? 'selected' : ''; ?>>5 sao</option>
              <option value="4" <?php echo $rating_filter == '4' ? 'selected' : ''; ?>>4 sao</option>
              <option value="3" <?php echo $rating_filter == '3' ? 'selected' : ''; ?>>3 sao</option>
              <option value="2" <?php echo $rating_filter == '2' ? 'selected' : ''; ?>>2 sao</option>
              <option value="1" <?php echo $rating_filter == '1' ? 'selected' : ''; ?>>1 sao</option>
            </select>
          </div>

          <div class="filter-actions">
            <button type="submit" class="btn">Lọc</button>
            <a href="reviews.php" class="btn secondary">Đặt lại</a>
          </div>
        </form>
      </div>

      <div class="content-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Sản phẩm</th>
              <th>Người dùng</th>
              <th>Đánh giá</th>
              <th>Bình luận</th>
              <th>Ngày tạo</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
            <tr>
              <td><?php echo $review['id']; ?></td>
              <td><?php echo $review['product_name']; ?></td>
              <td><?php echo $review['user_name']; ?></td>
              <td>
                <div class="star-rating">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <?php if ($i <= $review['rating']): ?>
                  ★
                  <?php else: ?>
                  ☆
                  <?php endif; ?>
                  <?php endfor; ?>
                </div>
              </td>
              <td class="review-comment"><?php echo $review['comment']; ?></td>
              <td><?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?></td>
              <td class="actions">
                <a href="reviews.php?delete=<?php echo $review['id']; ?>" class="delete-btn"
                  onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">Xóa</a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="7">Không có đánh giá nào</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>