<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

$admin_id = $_SESSION['admin_id'];
$permission_sql = "SELECT * FROM vw_admin_permissions WHERE admin_id = ? AND permission_code = 'admins_manage'";
$permission_stmt = $conn->prepare($permission_sql);
$permission_stmt->bind_param("i", $admin_id);
$permission_stmt->execute();
$permission_result = $permission_stmt->get_result();

if ($permission_result->num_rows === 0) {
  header('Location: index.php');
  exit;
}

$roles_sql = "SELECT * FROM admin_roles ORDER BY name";
$roles_result = $conn->query($roles_sql);
$roles = [];

if ($roles_result->num_rows > 0) {
  while ($row = $roles_result->fetch_assoc()) {
      $roles[] = $row;
  }
}

$admin_data = [
  'id' => '',
  'name' => '',
  'username' => '',
  'role_id' => '',
  'account_status' => 'active'
];

$is_edit = false;
$page_title = "Thêm Tài Khoản Quản Trị Mới";

// Check if editing existing admin
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $edit_id = $_GET['id'];
  $is_edit = true;
  $page_title = "Chỉnh Sửa Tài Khoản Quản Trị";
  
  // Get admin data
  $admin_sql = "SELECT * FROM admins WHERE id = ?";
  $admin_stmt = $conn->prepare($admin_sql);
  $admin_stmt->bind_param("i", $edit_id);
  $admin_stmt->execute();
  $admin_result = $admin_stmt->get_result();
  
  if ($admin_result->num_rows === 1) {
      $admin_data = $admin_result->fetch_assoc();
  } else {
      header('Location: admins.php');
      exit;
  }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $username = $_POST['username'];
  $role_id = $_POST['role_id'];
  $account_status = $_POST['account_status'];
  
  // Password is required for new accounts, optional for edits
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  
  // Validate input
  if (empty($name) || empty($username)) {
      $error = 'Tên và tên đăng nhập là bắt buộc.';
  } elseif (!$is_edit && empty($password)) {
      $error = 'Mật khẩu là bắt buộc cho tài khoản mới.';
  } elseif ((!empty($password) || !empty($confirm_password)) && $password !== $confirm_password) {
      $error = 'Mật khẩu xác nhận không khớp.';
  } else {
      // Check if username exists (for new accounts or when changing username)
      $check_sql = "SELECT * FROM admins WHERE username = ? AND id != ?";
      $check_stmt = $conn->prepare($check_sql);
      $check_id = $is_edit ? $admin_data['id'] : 0;
      $check_stmt->bind_param("si", $username, $check_id);
      $check_stmt->execute();
      $check_result = $check_stmt->get_result();
      
      if ($check_result->num_rows > 0) {
          $error = 'Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.';
      } else {
          if ($is_edit) {
              // Update existing admin
              if (!empty($password)) {
                  // Update with new password
                  $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                  $update_sql = "UPDATE admins SET name = ?, username = ?, password = ?, role_id = ?, account_status = ? WHERE id = ?";
                  $update_stmt = $conn->prepare($update_sql);
                  $update_stmt->bind_param("sssisi", $name, $username, $hashed_password, $role_id, $account_status, $admin_data['id']);
              } else {
                  // Update without changing password
                  $update_sql = "UPDATE admins SET name = ?, username = ?, role_id = ?, account_status = ? WHERE id = ?";
                  $update_stmt = $conn->prepare($update_sql);
                  $update_stmt->bind_param("ssisi", $name, $username, $role_id, $account_status, $admin_data['id']);
              }
              
              if ($update_stmt->execute()) {
                  $success = "Tài khoản quản trị đã được cập nhật thành công.";
              } else {
                  $error = "Lỗi khi cập nhật tài khoản quản trị.";
              }
          } else {
              // Insert new admin
              $hashed_password = password_hash($password, PASSWORD_DEFAULT);
              $insert_sql = "INSERT INTO admins (name, username, password, role_id, account_status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
              $insert_stmt = $conn->prepare($insert_sql);
              $insert_stmt->bind_param("sssis", $name, $username, $hashed_password, $role_id, $account_status);
              
              if ($insert_stmt->execute()) {
                  $success = "Tài khoản quản trị mới đã được tạo thành công.";
                  // Clear form after successful add
                  $admin_data = [
                      'id' => '',
                      'name' => '',
                      'username' => '',
                      'role_id' => '',
                      'account_status' => 'active'
                  ];
              } else {
                  $error = "Lỗi khi tạo tài khoản quản trị.";
              }
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
          <span>Xin chào, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="admins.php" class="btn secondary">← Quay Lại Danh Sách</a>
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
              <label for="name">Tên *</label>
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($admin_data['name']); ?>"
                required>
            </div>

            <div class="form-group">
              <label for="username">Tên đăng nhập *</label>
              <input type="text" id="username" name="username"
                value="<?php echo htmlspecialchars($admin_data['username']); ?>" required>
            </div>

            <div class="form-group">
              <label
                for="password"><?php echo $is_edit ? 'Mật khẩu (để trống nếu không thay đổi)' : 'Mật khẩu *'; ?></label>
              <input type="password" id="password" name="password" <?php echo $is_edit ? '' : 'required'; ?>>
            </div>

            <div class="form-group">
              <label for="confirm_password">Xác nhận mật khẩu</label>
              <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <div class="form-group">
              <label for="role_id">Vai trò</label>
              <select id="role_id" name="role_id">
                <option value="">-- Chọn vai trò --</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role['id']; ?>"
                  <?php echo ($admin_data['role_id'] == $role['id']) ? 'selected' : ''; ?>>
                  <?php echo $role['name']; ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="account_status">Trạng thái tài khoản</label>
              <select id="account_status" name="account_status">
                <option value="active" <?php echo ($admin_data['account_status'] == 'active') ? 'selected' : ''; ?>>Hoạt
                  động</option>
                <option value="disabled" <?php echo ($admin_data['account_status'] == 'disabled') ? 'selected' : ''; ?>>
                  Vô hiệu hóa</option>
              </select>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn"><?php echo $is_edit ? 'Cập Nhật Tài Khoản' : 'Thêm Tài Khoản'; ?></button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>

</html>