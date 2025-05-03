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


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $role_id = $_GET['delete'];
  $check_sql = "SELECT COUNT(*) as count FROM admins WHERE role_id = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("i", $role_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  $admin_count = $check_result->fetch_assoc()['count'];
  
  if ($admin_count > 0) {
      $error_message = "Không thể xóa vai trò này vì đã được gán cho $admin_count tài khoản quản trị.";
  } else {
      $delete_perms_sql = "DELETE FROM role_permissions WHERE role_id = ?";
      $delete_perms_stmt = $conn->prepare($delete_perms_sql);
      $delete_perms_stmt->bind_param("i", $role_id);
      $delete_perms_stmt->execute();
      $delete_sql = "DELETE FROM admin_roles WHERE id = ?";
      $delete_stmt = $conn->prepare($delete_sql);
      $delete_stmt->bind_param("i", $role_id);
      
      if ($delete_stmt->execute()) {
          $success_message = "Vai trò đã được xóa thành công.";
      } else {
          $error_message = "Lỗi khi xóa vai trò.";
      }
  }
}

// Get all roles with permission counts
$roles_sql = "SELECT r.*, COUNT(rp.permission_id) as permission_count 
             FROM admin_roles r 
             LEFT JOIN role_permissions rp ON r.id = rp.role_id 
             GROUP BY r.id 
             ORDER BY r.name";
$roles_result = $conn->query($roles_sql);
$roles = [];

if ($roles_result->num_rows > 0) {
  while ($row = $roles_result->fetch_assoc()) {
      $roles[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Vai Trò - Bubble Tea Shop Admin</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản Lý Vai Trò</h1>
        <div class="user-info">
          <span>Xin chào, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="role-form.php" class="btn">Thêm Vai Trò Mới</a>
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
              <th>Tên Vai Trò</th>
              <th>Mô Tả</th>
              <th>Số Quyền</th>
              <th>Ngày Tạo</th>
              <th>Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($roles) > 0): ?>
            <?php foreach ($roles as $role): ?>
            <tr>
              <td><?php echo $role['id']; ?></td>
              <td><?php echo $role['name']; ?></td>
              <td><?php echo $role['description']; ?></td>
              <td><?php echo $role['permission_count']; ?></td>
              <td><?php echo date('d/m/Y', strtotime($role['created_at'])); ?></td>
              <td class="actions">
                <a href="role-form.php?id=<?php echo $role['id']; ?>" class="edit-btn">Sửa</a>
                <a href="admin_roles.php?delete=<?php echo $role['id']; ?>" class="delete-btn"
                  onclick="return confirm('Bạn có chắc muốn xóa vai trò này?')">Xóa</a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="6">Không có vai trò nào</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>