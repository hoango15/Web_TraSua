<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

// Get all categories for the dropdown
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = $conn->query($categories_sql);

$product = [
  'id' => '',
  'name' => '',
  'description' => '',
  'price' => '',
  'image' => '../assets/img/',
  'category_id' => ''
];

$is_edit = false;
$page_title = "Thêm sản phẩm ";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $product_id = $_GET['id'];
  $is_edit = true;
  $page_title = "Sửa sản phẩm";

  $product_sql = "SELECT * FROM products WHERE id = ?";
  $product_stmt = $conn->prepare($product_sql);
  $product_stmt->bind_param("i", $product_id);
  $product_stmt->execute();
  $product_result = $product_stmt->get_result();

  if ($product_result->num_rows === 1) {
    $product = $product_result->fetch_assoc();
  } else {
    header('Location: products.php');
    exit;
  }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $category_id = $_POST['category_id'];
  $image = $_POST['image'];

  if (empty($name) || empty($price) || empty($category_id)) {
    $error = 'Please fill in all required fields.';
  } elseif (!is_numeric($price) || $price <= 0) {
    $error = 'Price must be a positive number.';
  } else {
    if ($is_edit) {
     
      $update_sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, category_id = ? WHERE id = ?";
      $update_stmt = $conn->prepare($update_sql);
      $update_stmt->bind_param("ssdsii", $name, $description, $price, $image, $category_id, $product['id']);

      if ($update_stmt->execute()) {
        $success = "Sản phẩm cập nhật thành công .";
      } else {
        $error = "Sản phẩm cập nhật bị lỗi .";
      }
    } else {
  
      $insert_sql = "INSERT INTO products (name, description, price, image, category_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
      $insert_stmt = $conn->prepare($insert_sql);
      $insert_stmt->bind_param("ssdsi", $name, $description, $price, $image, $category_id);

      if ($insert_stmt->execute()) {
        $success = "Sản phảm thêm thành công .";
        // Clear form after successful add
        $product = [
          'id' => '',
          'name' => '',
          'description' => '',
          'price' => '',
          'image' => '../assets/img/',
          'category_id' => ''
        ];
      } else {
        $error = "Lỗi thêm sản phẩm .";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?> - Bubble Tea Shop Admin</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1><?php echo $page_title; ?></h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="products.php" class="btn secondary">← Quay lại </a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="success-message">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <div class="form-container">
        <form method="POST" action="">
          <div class="form-grid">
            <div class="form-group">
              <label for="name">Tên sản phẩm *</label>
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>"
                required>
            </div>

            <div class="form-group">
              <label for="category_id">Loại sản phẩm *</label>
              <select id="category_id" name="category_id" required>
                <option value="">Lựa chọn loại sản phẩm </option>
                <?php while ($category = $categories_result->fetch_assoc()): ?>
                  <option value="<?php echo $category['id']; ?>"
                    <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                    <?php echo $category['name']; ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="price">Giá (đ) *</label>
              <input type="number" id="price" name="price" step="1000" min="0"
                value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>

            <div class="form-group">
              <label for="image">URL hình ảnh </label>
              <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($product['image']); ?>">

            </div>

            <div class="form-group full-width">
              <label for="description">Miêu tả </label>
              <textarea id="description" name="description"
                rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn"><?php echo $is_edit ? 'Cập Nhật ' : 'Thêm sản phẩm '; ?></button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>

</html>