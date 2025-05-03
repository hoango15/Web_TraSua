<?php
session_start();
require_once '../config/database.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$ad = [
    'id' => '',
    'title' => '',
    'description' => '',
    'image' => '',
    'button_text' => '',
    'button_link' => '',
    'start_date' => date('Y-m-d'),
    'end_date' => date('Y-m-d', strtotime('+30 days')),
    'active' => 1
];

$is_edit = false;
$page_title = "Thêm Quảng Cáo Popup Mới";

// Check if editing existing ad
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ad_id = $_GET['id'];
    $is_edit = true;
    $page_title = "Chỉnh Sửa Quảng Cáo Popup";
    
    // Get ad data
    $sql = "SELECT * FROM popup_ads WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $ad = $result->fetch_assoc();
    } else {
        header('Location: popup_ads.php');
        exit;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $button_text = $_POST['button_text'];
    $button_link = $_POST['button_link'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $active = isset($_POST['active']) ? 1 : 0;
    
    // Validate input
    if (empty($title) || empty($start_date) || empty($end_date)) {
        $error = 'Vui lòng điền đầy đủ các trường bắt buộc.';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = 'Ngày kết thúc phải sau ngày bắt đầu.';
    } else {
        // Handle image upload
        $image_path = $ad['image'];
        $upload_new_image = false;
        
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $upload_dir = '../assets/img/popup/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES['image']['tmp_name']);
            if ($check === false) {
                $error = 'File không phải là hình ảnh.';
            }
            // Check file size (max 5MB)
            elseif ($_FILES['image']['size'] > 5000000) {
                $error = 'Kích thước file quá lớn (tối đa 5MB).';
            }
            // Allow certain file formats
            elseif (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = 'Chỉ chấp nhận file JPG, JPEG, PNG & GIF.';
            }
            // If everything is ok, try to upload file
            else {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = 'assets/img/popup/' . $file_name;
                    $upload_new_image = true;
                    
                    // Delete old image if exists and we're updating
                    if ($is_edit && !empty($ad['image']) && file_exists('../' . $ad['image'])) {
                        unlink('../' . $ad['image']);
                    }
                } else {
                    $error = 'Có lỗi khi tải lên file.';
                }
            }
        } elseif (!$is_edit && empty($image_path)) {
            $error = 'Vui lòng chọn hình ảnh.';
        }
        
        if (empty($error)) {
            if ($is_edit) {
                // Update existing ad
                $sql = "UPDATE popup_ads SET title = ?, description = ?, image = ?, button_text = ?, button_link = ?, start_date = ?, end_date = ?, active = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssii", $title, $description, $image_path, $button_text, $button_link, $start_date, $end_date, $active, $ad['id']);
                
                if ($stmt->execute()) {
                    $success = "Cập nhật quảng cáo popup thành công.";
                } else {
                    $error = "Lỗi khi cập nhật quảng cáo popup.";
                }
            } else {
                // Insert new ad
                $sql = "INSERT INTO popup_ads (title, description, image, button_text, button_link, start_date, end_date, active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $title, $description, $image_path, $button_text, $button_link, $start_date, $end_date, $active);
                
                if ($stmt->execute()) {
                    $success = "Thêm quảng cáo popup thành công.";
                    // Clear form after successful add
                    $ad = [
                        'id' => '',
                        'title' => '',
                        'description' => '',
                        'image' => '',
                        'button_text' => '',
                        'button_link' => '',
                        'start_date' => date('Y-m-d'),
                        'end_date' => date('Y-m-d', strtotime('+30 days')),
                        'active' => 1
                    ];
                } else {
                    $error = "Lỗi khi thêm quảng cáo popup.";
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
  <link rel="stylesheet" href="css/slider.css">
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
        <a href="popup_ads.php" class="btn secondary">← Quay Lại Danh Sách</a>
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
        <form method="POST" action="" enctype="multipart/form-data">
          <div class="form-grid">
            <div class="form-group">
              <label for="title">Tiêu đề *</label>
              <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($ad['title']); ?>" required>
            </div>

            <div class="form-group">
              <label for="button_text">Nút nhấn</label>
              <input type="text" id="button_text" name="button_text"
                value="<?php echo htmlspecialchars($ad['button_text']); ?>" placeholder="Ví dụ: Đặt Ngay">
            </div>

            <div class="form-group">
              <label for="button_link">Liên kết nút nhấn</label>
              <input type="text" id="button_link" name="button_link"
                value="<?php echo htmlspecialchars($ad['button_link']); ?>" placeholder="Ví dụ: products.php">
            </div>

            <div class="form-group">
              <label for="start_date">Ngày bắt đầu *</label>
              <input type="date" id="start_date" name="start_date" value="<?php echo $ad['start_date']; ?>" required>
            </div>

            <div class="form-group">
              <label for="end_date">Ngày kết thúc *</label>
              <input type="date" id="end_date" name="end_date" value="<?php echo $ad['end_date']; ?>" required>
            </div>

            <div class="form-group full-width">
              <label for="description">Mô tả</label>
              <textarea id="description" name="description"
                rows="3"><?php echo htmlspecialchars($ad['description']); ?></textarea>
            </div>

            <div class="form-group full-width">
              <label for="image">Hình ảnh <?php echo $is_edit ? '(để trống nếu không thay đổi)' : '*'; ?></label>
              <input type="file" id="image" name="image" accept="image/*">
              <?php if ($is_edit && !empty($ad['image'])): ?>
              <div class="current-image">
                <p>Ảnh hiện tại:</p>
                <img src="<?php echo '../' . $ad['image']; ?>" alt="Current Popup Ad Image">
              </div>
              <?php endif; ?>
            </div>

            <div class="form-group checkbox-group">
              <label>
                <input type="checkbox" name="active" <?php echo $ad['active'] ? 'checked' : ''; ?>>
                Hiển thị quảng cáo này
              </label>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn"><?php echo $is_edit ? 'Cập Nhật' : 'Thêm Mới'; ?></button>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>

</html>