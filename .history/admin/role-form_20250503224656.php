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

// Get all permissions
$permissions_sql = "SELECT * FROM admin_permissions ORDER BY name";
$permissions_result = $conn->query($permissions_sql);
$permissions = [];

if ($permissions_result->num_rows > 0) {
  while ($row = $permissions_result->fetch_assoc()) {
      $permissions[] = $row;
  }
}

$role = [
  'id' => '',
  'name' => '',
  'description' => ''
];

$role_permissions = [];

$is_edit = false;
$page_title = "Thêm Vai Trò Mới";

// Check if editing existing role
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $role_id = $_GET['id'];
  $is_edit = true;
  $page_title = "Chỉnh Sửa Vai Trò";
  
  // Get role data
  $role_sql = "SELECT * FROM admin_roles WHERE id = ?";
  $role_stmt = $conn->prepare($role_sql);
  $role_stmt->bind_param("i", $role_id);
  $role_stmt->execute();
  $role_result = $role_stmt->get_result();
  
  if ($role_result->num_rows === 1) {
      $role = $role_result->fetch_assoc();
      
      // Get role permissions
      $role_perms_sql = "SELECT permission_id FROM role_permissions WHERE role_id = ?";
      $role_perms_stmt = $conn->prepare($role_perms_sql);
      $role_perms_stmt->bind_param("i", $role_id);
      $role_perms_stmt->execute();
      $role_perms_result = $role_perms_stmt->get_result();
      
      while ($perm = $role_perms_result->fetch_assoc()) {
          $role_permissions[] = $perm['permission_id'];
      }
  } else {
      header('Location: admin_roles.php');
      exit;
  }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $selected_permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
  
  // Validate input
  if (empty($name)) {
      $error = 'Tên vai trò là bắt buộc.';
  } else {
      $conn->begin_transaction();
      
      try {
          if ($is_edit) {
              // Update existing role
              $update_sql = "UPDATE admin_roles SET name = ?, description = ? WHERE id = ?";
              $update_stmt = $conn->prepare($update_sql);
              $update_stmt->bind_param("ssi", $name, $description, $role['id']);
              $update_stmt->execute();
              
             
              $delete_perms_sql = "DELETE FROM role_permissions WHERE role_id = ?";
              $delete_perms_stmt = $conn->prepare($delete_perms_sql);
              $delete_perms_stmt->bind_param("i", $role['id']);
              $delete_perms_stmt->execute();
              
             
              if (!empty($selected_permissions)) {
                  $insert_perms_sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                  $insert_perms_stmt = $conn->prepare($insert_perms_sql);
                  
                  foreach ($selected_permissions as $perm_id) {
                      $insert_perms_stmt->bind_param("ii", $role['id'], $perm_id);
                      $insert_perms_stmt->execute();
                  }
              }
              
              $conn->commit();
              $success = "Vai trò đã được cập nhật thành công.";
          } else {
            
              $insert_sql = "INSERT INTO admin_roles (name, description, created_at) VALUES (?, ?, NOW())";
              $insert_stmt = $conn->prepare($insert_sql);
              $insert_stmt->bind_param("ss", $name, $description);
              $insert_stmt->execute();
              
              $new_role_id = $conn->insert_id;
              
             
              if (!empty($selected_permissions)) {
                  $insert_perms_sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
                  $insert_perms_stmt = $conn->prepare($insert_perms_sql);
                  
                  foreach ($selected_permissions as $perm_id) {
                      $insert_perms_stmt->bind_param("ii", $new_role_id, $perm_id);
                      $insert_perms_stmt->execute();
                  }
              }
              
              $conn->commit();
              $success = "Vai trò mới đã được tạo thành công.";
              
             
              $role = [
                  'id' => '',
                  'name' => '',
                  'description' => ''
              ];
              $role_permissions = [];
          }
      } catch (Exception $e) {
          $conn->rollback();
          $error = "Đã xảy ra lỗi: " . $e->getMessage();
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
  <style>
  .permissions-container {
    margin-top: 20px;
  }

  .permissions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }

  .permission-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }

  .permission-item input {
    margin-right: 10px;
  }

  @media (max-width: 768px) {
    .permissions-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 576px) {
    .permissions-grid {
      grid-template-columns: 1fr;
    }
  }
  </style>
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
        <a href="admin_roles.php" class="btn secondary">← Quay Lại Danh Sách</a>
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
            <label for="name">Tên Vai Trò *</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($role['name']); ?>" required>
          </div>

          <div class="form-group">
            <label for="description">Mô Tả</label>
            <textarea id="description" name="description"
              rows="3"><?php echo htmlspecialchars($role['description']); ?></textarea>
          </div>

          <div class="permissions-container">
            <h3>Quyền Hạn</h3>
            <p>Chọn các quyền hạn cho vai trò này:</p>

            <div class="permissions-grid">
              <?php foreach ($permissions as $permission): ?>
              <div class="permission-item">
                <input type="checkbox" id="perm_<?php echo $permission['id']; ?>" name="permissions[]"
                  value="<?php echo $permission['id']; ?>"
                  <?php echo in_array($permission['id'], $role_permissions) ? 'checked' : ''; ?>>
                <label for="perm_<?php echo $permission['id']; ?>">
                  <?php echo $permission['name']; ?>
                </label>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn"><?php echo $is_edit ? 'Cập Nhật Vai Trò' : 'Thêm Vai Trò'; ?></button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>

</html>