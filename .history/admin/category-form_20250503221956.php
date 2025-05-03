<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

$category = [
  'id' => '',
  'name' => '',
  'description' => ''
];

$is_edit = false;
$page_title = "Thêm danh mục ";

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $category_id = $_GET['id'];
  $is_edit = true;
  $page_title = "Sửa danh mục ";


  $category_sql = "SELECT * FROM categories WHERE id = ?";
  $category_stmt = $conn->prepare($category_sql);
  $category_stmt->bind_param("i", $category_id);
  $category_stmt->execute();
  $category_result = $category_stmt->get_result();

  if ($category_result->num_rows === 1) {
    $category = $category_result->fetch_assoc();
  } else {
    header('Location: categories.php');
    exit;
  }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $description = $_POST['description'];

  if (empty($name)) {
    $error = 'Tên loại sản phẩm này đã tồn tại.';
  } else {
    if ($is_edit) {
     
      $update_sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
      $update_stmt = $conn->prepare($update_sql);
      $update_stmt->bind_param("ssi", $name, $description, $category['id']);

      if ($update_stmt->execute()) {
        $success = ".";
      } else {
        $error = "Error updating category.";
      }
    } else {
      // Insert new category
      $insert_sql = "INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())";
      $insert_stmt = $conn->prepare($insert_sql);
      $insert_stmt->bind_param("ss", $name, $description);

      if ($insert_stmt->execute()) {
        $success = "Category added successfully.";
        // Clear form after successful add
        $category = [
          'id' => '',
          'name' => '',
          'description' => ''
        ];
      } else {
        $error = "Error adding category.";
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
        <a href="categories.php" class="btn secondary">← Quay lại </a>
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
          <div class="form-group">
            <label for="name">Tên danh mục *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>"
              required>
          </div>

          <div class="form-group">
            <label for="description">Miêu tả </label>
            <textarea id="description" name="description"
              rows="5"><?php echo htmlspecialchars($category['description']); ?></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn"><?php echo $is_edit ? 'Cập nhật ' : 'Thêm '; ?></button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>

</html>