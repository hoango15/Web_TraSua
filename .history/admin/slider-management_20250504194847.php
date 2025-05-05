<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

// Handle delete action
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  // Get image path to delete file
  $img_sql = "SELECT image_path FROM slider_images WHERE id = ?";
  $stmt = $conn->prepare($img_sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    if (!empty($row['image_path']) && file_exists("../" . $row['image_path'])) {
      unlink("../" . $row['image_path']);
    }
  }
 
  $delete_sql = "DELETE FROM slider_images WHERE id = ?";
  $stmt = $conn->prepare($delete_sql);
  $stmt->bind_param("i", $id);
  if ($stmt->execute()) {
    $_SESSION['success'] = "Slider đã được xóa thành công!";
  } else {
    $_SESSION['error'] = "Có lỗi xảy ra: " . $conn->error;
  }
  header('Location: slider-management.php');
  exit;
}

if (isset($_GET['toggle'])) {
  $id = $_GET['toggle'];
  $status_sql = "SELECT active FROM slider_images WHERE id = ?";
  $stmt = $conn->prepare($status_sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $new_status = $row['active'] ? 0 : 1;
    $update_sql = "UPDATE slider_images SET active = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $new_status, $id);
    if ($stmt->execute()) {
      $_SESSION['success'] = "Trạng thái slider đã được cập nhật!";
    } else {
      $_SESSION['error'] = "Có lỗi xảy ra: " . $conn->error;
    }
  }
  header('Location: slider-management.php');
  exit;
}

if (isset($_POST['update_order'])) {
  foreach ($_POST['sort_order'] as $id => $order) {
    $update_sql = "UPDATE slider_images SET sort_order = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $order, $id);
    $stmt->execute();
  }
  $_SESSION['success'] = "Thứ tự slider đã được cập nhật!";
  header('Location: slider-management.php');
  exit;
}

$sliders_sql = "SELECT * FROM slider_images ORDER BY sort_order ASC, created_at DESC";
$sliders_result = $conn->query($sliders_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lí Slider</title>
  <link rel="stylesheet" href="css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4a6cf7;
      --primary-hover: #3a5bd9;
      --secondary-color: #6c757d;
      --secondary-hover: #5a6268;
      --success-color: #28a745;
      --success-hover: #218838;
      --danger-color: #dc3545;
      --danger-hover: #c82333;
      --light-color: #f8f9fa;
      --dark-color: #343a40;
      --border-color: #dee2e6;
      --border-radius: 8px;
      --box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      --transition: all 0.3s ease;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fb;
      color: #333;
    }

    .content-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border-color);
    }

    .content-header h1 {
      margin: 0;
      font-size: 1.8rem;
      color: #333;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-info span {
      font-weight: 500;
    }

    .logout-btn {
      padding: 8px 16px;
      background-color: var(--danger-color);
      color: white;
      text-decoration: none;
      border-radius: var(--border-radius);
      transition: var(--transition);
    }

    .logout-btn:hover {
      background-color: var(--danger-hover);
    }

    .alert {
      padding: 15px 20px;
      margin-bottom: 20px;
      border-radius: var(--border-radius);
      display: flex;
      align-items: center;
      font-weight: 500;
    }

    .alert i {
      margin-right: 10px;
      font-size: 1.2rem;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .action-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }

    .btn i {
      margin-right: 8px;
    }

    .btn-add {
      background-color: var(--success-color);
      color: white;
    }

    .btn-add:hover {
      background-color: var(--success-hover);
    }

    .btn-order {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-order:hover {
      background-color: var(--primary-hover);
    }

    .content-card {
      background-color: #fff;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      margin-bottom: 30px;
    }

    .table-responsive {
      overflow-x: auto;
      min-height: 300px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table th {
      background-color: #f8f9fa;
      padding: 15px;
      text-align: left;
      font-weight: 600;
      color: #495057;
      border-bottom: 2px solid var(--border-color);
    }

    table td {
      padding: 15px;
      border-bottom: 1px solid var(--border-color);
      vertical-align: middle;
    }

    table tr:hover {
      background-color: #f8f9fa;
    }

    .slider-img {
      width: 120px;
      height: 70px;
      object-fit: cover;
      border-radius: var(--border-radius);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .status-badge {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      text-align: center;
      transition: var(--transition);
    }

    .status-active {
      background-color: #d4edda;
      color: #155724;
    }

    .status-active:hover {
      background-color: #c3e6cb;
    }

    .status-inactive {
      background-color: #f8d7da;
      color: #721c24;
    }

    .status-inactive:hover {
      background-color: #f5c6cb;
    }

    .order-input {
      width: 70px;
      padding: 8px;
      text-align: center;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
    }

    .actions {
      display: flex;
      gap: 10px;
    }

    .edit-btn, .delete-btn {
      padding: 8px 16px;
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 500;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      transition: var(--transition);
    }

    .edit-btn {
      background-color: #e9ecef;
      color: #495057;
    }

    .edit-btn:hover {
      background-color: #dee2e6;
    }

    .delete-btn {
      background-color: #f8d7da;
      color: #721c24;
    }

    .delete-btn:hover {
      background-color: #f5c6cb;
    }

    .edit-btn i, .delete-btn i {
      margin-right: 5px;
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #6c757d;
    }

    .empty-state i {
      font-size: 3rem;
      margin-bottom: 15px;
      opacity: 0.5;
    }

    .empty-state p {
      font-size: 1.1rem;
      margin-bottom: 20px;
    }

    .form-footer {
      padding: 15px 20px;
      background-color: #f8f9fa;
      border-top: 1px solid var(--border-color);
      display: flex;
      justify-content: flex-end;
    }

    /* Responsive adjustments */
    @media (max-width: 992px) {
      .content-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .user-info {
        width: 100%;
        justify-content: space-between;
      }
      
      .action-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .btn-add {
        width: 100%;
      }
    }

    @media (max-width: 768px) {
      table {
        min-width: 800px;
      }
    }
  </style>
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1><i class="fas fa-images"></i> Quản lí Slider</h1>
        <div class="user-info">
          <span><i class="fas fa-user-circle"></i> Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </header>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']);
          ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
          ?>
        </div>
      <?php endif; ?>

      <div class="action-bar">
        <a href="slider-form.php" class="btn btn-add">
          <i class="fas fa-plus"></i> Thêm Slider Mới
        </a>
      </div>

      <form method="post" action="">
        <div class="content-card">
          <div class="table-responsive">
            <table>
              <thead>
                <tr>
                  <th width="5%">ID</th>
                  <th width="15%">Hình ảnh</th>
                  <th width="20%">Tiêu đề</th>
                  <th width="20%">Mô tả</th>
                  <th width="10%">Nút</th>
                  <th width="10%">Trạng thái</th>
                  <th width="10%">Thứ tự</th>
                  <th width="10%">Hoạt động</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($sliders_result->num_rows > 0): ?>
                  <?php while ($slider = $sliders_result->fetch_assoc()): ?>
                    <tr>
                      <td><?php echo $slider['id']; ?></td>
                      <td>
                        <?php if (!empty($slider['image_path'])): ?>
                          <img src="../<?php echo $slider['image_path']; ?>" alt="Slider Image" class="slider-img">
                        <?php else: ?>
                          <div class="no-image">
                            <i class="fas fa-image"></i> No image
                          </div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <strong><?php echo !empty($slider['title']) ? $slider['title'] : '<em>No title</em>'; ?></strong>
                      </td>
                      <td>
                        <?php 
                          if (!empty($slider['description'])) {
                            echo substr($slider['description'], 0, 50) . (strlen($slider['description']) > 50 ? '...' : '');
                          } else {
                            echo '<em>No description</em>';
                          }
                        ?>
                      </td>
                      <td>
                        <?php 
                          if (!empty($slider['button_text'])) {
                            echo '<span class="status-badge" style="background-color: #e2e3e5; color: #383d41;">' . 
                                  $slider['button_text'] . 
                                  '</span>';
                          } else {
                            echo '<em>No button</em>';
                          }
                        ?>
                      </td>
                      <td>
                        <a href="slider-management.php?toggle=<?php echo $slider['id']; ?>" title="Click to toggle status">
                          <span class="status-badge <?php echo $slider['active'] ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $slider['active'] ? '<i class="fas fa-eye"></i> Hiển thị' : '<i class="fas fa-eye-slash"></i> Ẩn'; ?>
                          </span>
                        </a>
                      </td>
                      <td>
                        <input type="number" name="sort_order[<?php echo $slider['id']; ?>]" value="<?php echo $slider['sort_order']; ?>" class="order-input" min="0">
                      </td>
                      <td class="actions">
                        <a href="slider-form.php?id=<?php echo $slider['id']; ?>" class="edit-btn" title="Edit this slider">
                          <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="slider-management.php?delete=<?php echo $slider['id']; ?>" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa slider này?')" title="Delete this slider">
                          <i class="fas fa-trash-alt"></i> Xóa
                        </a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8">
                      <div class="empty-state">
                        <i class="fas fa-images"></i>
                        <p>Không có slider nào</p>
                        <a href="slider-form.php" class="btn btn-add">
                          <i class="fas fa-plus"></i> Thêm Slider Mới
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          
          <?php if ($sliders_result->num_rows > 0): ?>
            <div class="form-footer">
              <button type="submit" name="update_order" class="btn btn-order">
                <i class="fas fa-sort-numeric-down"></i> Cập nhật thứ tự
              </button>
            </div>
          <?php endif; ?>
        </div>
      </form>
    </main>
  </div>
</body>

</html>