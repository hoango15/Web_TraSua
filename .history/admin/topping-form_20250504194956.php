<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$topping = [
    'id' => '',
    'name' => '',
    'price' => '0.50'
];

$is_edit = false;
$page_title = "Thêm mới Topping";

// Check if editing existing topping
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $topping_id = $_GET['id'];
    $is_edit = true;
    $page_title = "Sửa loại Topping";
    
    $topping_sql = "SELECT * FROM toppings WHERE id = ?";
    $topping_stmt = $conn->prepare($topping_sql);
    $topping_stmt->bind_param("i", $topping_id);
    $topping_stmt->execute();
    $topping_result = $topping_stmt->get_result();
    
    if ($topping_result->num_rows === 1) {
        $topping = $topping_result->fetch_assoc();
    } else {
        header('Location: toppings.php');
        exit;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    
    if (empty($name)) {
        $error = 'Topping name is required.';
    } elseif (!is_numeric($price) || $price < 0) {
        $error = 'Price must be a positive number.';
    } else {
        if ($is_edit) {
            $update_sql = "UPDATE toppings SET name = ?, price = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sdi", $name, $price, $topping['id']);
            
            if ($update_stmt->execute()) {
                $success = "Topping updated successfully.";
            } else {
                $error = "Error updating topping.";
            }
        } else {
            $insert_sql = "INSERT INTO toppings (name, price, created_at) VALUES (?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sd", $name, $price);
            
            if ($insert_stmt->execute()) {
                $success = "Topping added successfully.";
                // Clear form after successful add
                $topping = [
                    'id' => '',
                    'name' => '',
                    'price' => '0.50'
                ];
            } else {
                $error = "Error adding topping.";
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
        <a href="toppings.php" class="btn secondary">← Quay lại </a>
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
            <label for="name">Tên Topping *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($topping['name']); ?>" required>
          </div>

          <div class="form-group">
            <label for="price">Giá (đ) *</label>
            <input type="number" id="price" name="price" step="1000 " min="0"
              value="<?php echo htmlspecialchars($topping['price']); ?>" required>
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