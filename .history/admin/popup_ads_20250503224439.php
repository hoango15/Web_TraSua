<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $ad_id = $_GET['delete'];
    $sql = "SELECT image FROM popup_ads WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $ad = $result->fetch_assoc();
        $image_path = '../' . $ad['image'];
        
       
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Delete from database
        $delete_sql = "DELETE FROM popup_ads WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $ad_id);
        
        if ($delete_stmt->execute()) {
            $success_message = "Quảng cáo đã được xóa thành công.";
        } else {
            $error_message = "Lỗi khi xóa quảng cáo.";
        }
    }
}

// Handle status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $ad_id = $_GET['toggle'];
    
    // Get current status
    $sql = "SELECT active FROM popup_ads WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $ad = $result->fetch_assoc();
        $new_status = $ad['active'] ? 0 : 1;
        
        // Update status
        $update_sql = "UPDATE popup_ads SET active = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $new_status, $ad_id);
        
        if ($update_stmt->execute()) {
            $success_message = "Trạng thái quảng cáo đã được cập nhật.";
        } else {
            $error_message = "Lỗi khi cập nhật trạng thái.";
        }
    }
}

// Get all popup ads
$sql = "SELECT * FROM popup_ads ORDER BY created_at DESC";
$result = $conn->query($sql);
$popup_ads = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $popup_ads[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản Lý Quảng Cáo Popup - Bubble Tea Shop Admin</title>
  <link rel="stylesheet" href="css/admin.css">
  <link rel="stylesheet" href="css/slider.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản Lý Quảng Cáo Pop</h1>
        <div class="user-info">
          <span>Xin chào, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Đăng xuất</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="popup_ad_form.php" class="btn">Thêm Quảng Cáo Mới</a>
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

      <div class="slider-container">
        <div class="slider-images">
          <?php if (count($popup_ads) > 0): ?>
          <?php foreach ($popup_ads as $ad): ?>
          <div class="slider-image-card">
            <div class="image-preview">
              <img src="<?php echo '../' . $ad['image']; ?>" alt="Popup Ad Image">
              <div class="image-status <?php echo $ad['active'] ? 'active' : 'inactive'; ?>">
                <?php echo $ad['active'] ? 'Hiển thị' : 'Ẩn'; ?>
              </div>
            </div>
            <div class="image-info">
              <div class="image-title"><?php echo $ad['title']; ?></div>
              <div class="image-dates">
                <span>Từ: <?php echo date('d/m/Y', strtotime($ad['start_date'])); ?></span>
                <span>Đến: <?php echo date('d/m/Y', strtotime($ad['end_date'])); ?></span>
              </div>
            </div>
            <div class="image-actions">
              <a href="popup_ad_form.php?id=<?php echo $ad['id']; ?>" class="edit-btn">Sửa</a>
              <a href="popup_ads.php?toggle=<?php echo $ad['id']; ?>" class="toggle-btn">
                <?php echo $ad['active'] ? 'Ẩn' : 'Hiển thị'; ?>
              </a>
              <a href="popup_ads.php?delete=<?php echo $ad['id']; ?>" class="delete-btn"
                onclick="return confirm('Bạn có chắc muốn xóa quảng cáo này?')">Xóa</a>
            </div>
          </div>
          <?php endforeach; ?>
          <?php else: ?>
          <div class="no-images">
            <p>Chưa có quảng cáo popup nào.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>
</body>

</html>