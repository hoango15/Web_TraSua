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
  $delete_id = $_GET['delete'];
  
  if ($delete_id == $_SESSION['admin_id']) {
      $error_message = "Bạn không thể xóa tài khoản của chính mình.";
  } else {
      $delete_sql = "DELETE FROM admins WHERE id = ?";
      $delete_stmt = $conn->prepare($delete_sql);
      $delete_stmt->bind_param("i", $delete_id);
      
      if ($delete_stmt->execute()) {
          $success_message = "Tài khoản quản trị đã được xóa thành công.";
      } else {
          $error_message = "Lỗi khi xóa tài khoản quản trị.";
      }
  }
}

if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
  $toggle_id = $_GET['toggle'];
  
  if ($toggle_id == $_SESSION['admin_id']) {
      $error_message = "Bạn không thể thay đổi trạng thái tài khoản của chính mình.";
  } else {
     
      $status_sql = "SELECT account_status FROM admins WHERE id = ?";
      $status_stmt = $conn->prepare($status_sql);
      $status_stmt->bind_param("i", $toggle_id);
      $status_stmt->execute();
      $status_result = $status_stmt->get_result();
      
      if ($status_result->num_rows > 0) {
          $admin = $status_result->fetch_assoc();
          $new_status = ($admin['account_status'] == 'active') ? 'disabled' : 'active';
          
        
          $update_sql = "UPDATE admins SET account_status = ? WHERE id = ?";
          $update_stmt = $conn->prepare($update_sql);
          $update_stmt->bind_param("si", $new_status, $toggle_id);
          
          if ($update_stmt->execute()) {
              $success_message = "Trạng thái tài khoản đã được cập nhật thành công.";
          } else {
              $error_message = "Lỗi khi cập nhật trạng thái tài khoản.";
          }
      }
  }
}


$admins_sql = "SELECT a.*, r.name as role_name 
              FROM admins a 
              LEFT JOIN admin_roles r ON a.role_id = r.id 
              ORDER BY a.id";
$admins_result = $conn->query($admins_sql);
$admins = [];

if ($admins_result->num_rows > 0) {
  while ($row = $admins_result->fetch_assoc()) {
      $admins[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Tài Khoản Quản Trị - KTea Shop Admin</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản Lý Tài Khoản Quản Trị</h1>
        <div class="user-info">
          <span>Xin chào, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="admin-form.php" class="btn">Thêm Tài Khoản Mới</a>
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
              <th>Tên</th>
              <th>Tên đăng nhập</th>
              <th>Vai trò</th>
              <th>Trạng thái</th>
              <th>Ngày tạo</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($admins) > 0): ?>
            <?php foreach ($admins as $admin): ?>
            <tr>
              <td><?php echo $admin['id']; ?></td>
              <td><?php echo $admin['name']; ?></td>
              <td><?php echo $admin['username']; ?></td>
              <td><?php echo $admin['role_name'] ?? 'Không có'; ?></td>
              <td>
                <span class="status-badge status-<?php echo $admin['account_status']; ?>">
                  <?php 
                                              switch($admin['account_status']) {
                                                  case 'active':
                                                      echo 'Hoạt động';
                                                      break;
                                                  case 'locked':
                                                      echo 'Bị khóa';
                                                      break;
                                                  case 'disabled':
                                                      echo 'Vô hiệu hóa';
                                                      break;
                                                  default:
                                                      echo 'Không xác định';
                                              }
                                          ?>
                </span>
              </td>
              <td><?php echo date('d/m/Y', strtotime($admin['created_at'])); ?></td>
              <td class="actions">
                <a href="admin-form.php?id=<?php echo $admin['id']; ?>" class="edit-btn">Sửa</a>
                <a href="admins.php?toggle=<?php echo $admin['id']; ?>" class="toggle-btn">
                  <?php echo ($admin['account_status'] == 'active') ? 'Vô hiệu' : 'Kích hoạt'; ?>
                </a>
                <a href="admins.php?delete=<?php echo $admin['id']; ?>" class="delete-btn"
                  onclick="return confirm('Bạn có chắc muốn xóa tài khoản quản trị này?')">Xóa</a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
              <td colspan="7">Không có tài khoản quản trị nào</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>