<?php
session_start();
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

$slider = [
  'id' => '',
  'title' => '',
  'description' => '',
  'button_text' => '',
  'button_link' => '',
  'image_path' => '',
  'active' => 1,
  'sort_order' => 0
];

$is_edit = false;

// Check if editing existing slider
if (isset($_GET['id'])) {
  $id = $_GET['id'];
  $is_edit = true;
  
  $slider_sql = "SELECT * FROM slider_images WHERE id = ?";
  $stmt = $conn->prepare($slider_sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    $slider = $result->fetch_assoc();
  } else {
    $_SESSION['error'] = "Slider không tồn tại!";
    header('Location: slider-management.php');
    exit;
  }
} else {
  // If adding new slider, get the next available sort order
  $next_order_sql = "SELECT MAX(sort_order) as max_order FROM slider_images";
  $result = $conn->query($next_order_sql);
  if ($result && $row = $result->fetch_assoc()) {
    $slider['sort_order'] = ($row['max_order'] !== null) ? $row['max_order'] + 1 : 1;
  } else {
    $slider['sort_order'] = 1; // Start with 1 if no sliders exist
  }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $description = $_POST['description'];
  $button_text = $_POST['button_text'];
  $button_link = $_POST['button_link'];
  $active = isset($_POST['active']) ? 1 : 0;
  $sort_order = $_POST['sort_order'];
  
  // Create uploads directory if it doesn't exist
  $upload_dir = "../uploads/sliders/";
  if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }
  
  // Handle image upload
  $image_path = $slider['image_path']; // Keep existing image by default
  
  if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
    $file_name = $_FILES['image']['name'];
    $file_tmp = $_FILES['image']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Check file extension
    $extensions = ["jpeg", "jpg", "png", "gif", "webp"];
    if (in_array($file_ext, $extensions)) {
      $new_file_name = uniqid() . '.' . $file_ext;
      $relative_path = "uploads/sliders/" . $new_file_name;
      
      // Delete old image if exists
      if ($is_edit && !empty($slider['image_path'])) {
        $old_file = "../" . $slider['image_path'];
        if (file_exists($old_file)) {
          unlink($old_file);
        }
      }
      
      // Upload new image
      if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
        $image_path = $relative_path;
      } else {
        $_SESSION['error'] = "Không thể tải lên hình ảnh!";
      }
    } else {
      $_SESSION['error'] = "Chỉ cho phép các file JPG, JPEG, PNG, GIF và WEBP!";
    }
  }
  
  if (!isset($_SESSION['error'])) {
    if ($is_edit) {
      // Update existing slider
      $update_sql = "UPDATE slider_images SET 
                    title = ?, 
                    description = ?, 
                    button_text = ?, 
                    button_link = ?, 
                    image_path = ?, 
                    active = ?, 
                    sort_order = ?
                    WHERE id = ?";
      
      $stmt = $conn->prepare($update_sql);
      $stmt->bind_param("sssssiii", $title, $description, $button_text, $button_link, $image_path, $active, $sort_order, $slider['id']);
      
      if ($stmt->execute()) {
        $_SESSION['success'] = "Slider đã được cập nhật thành công!";
        header('Location: slider-management.php');
        exit;
      } else {
        $_SESSION['error'] = "Có lỗi xảy ra: " . $conn->error;
      }
    } else {
     
      $insert_sql = "INSERT INTO slider_images 
                    (title, description, button_text, button_link, image_path, active, sort_order) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
      
      $stmt = $conn->prepare($insert_sql);
      $stmt->bind_param("sssssii", $title, $description, $button_text, $button_link, $image_path, $active, $sort_order);
      
      if ($stmt->execute()) {
        $_SESSION['success'] = "Slider đã được thêm thành công!";
        header('Location: slider-management.php');
        exit;
      } else {
        $_SESSION['error'] = "Có lỗi xảy ra: " . $conn->error;
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
  <title><?php echo $is_edit ? 'Sửa' : 'Thêm'; ?> Slider</title>
  <link rel="stylesheet" href="css/admin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4a6cf7;
      --primary-hover: #3a5bd9;
      --secondary-color: #6c757d;
      --secondary-hover: #5a6268;
      --success-color: #28a745;
      --danger-color: #dc3545;
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

    .form-container {
      background-color: #fff;
      padding: 30px;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      margin-bottom: 30px;
    }

    .form-header {
      margin-bottom: 25px;
      border-bottom: 1px solid var(--border-color);
      padding-bottom: 15px;
    }

    .form-header h2 {
      margin: 0;
      color: #333;
      font-size: 1.5rem;
    }

    .form-row {
      display: flex;
      flex-wrap: wrap;
      margin-right: -15px;
      margin-left: -15px;
    }

    .form-col {
      flex: 0 0 100%;
      max-width: 100%;
      padding-right: 15px;
      padding-left: 15px;
      margin-bottom: 20px;
    }

    @media (min-width: 768px) {
      .form-col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
      }
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #444;
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      box-sizing: border-box;
      font-size: 1rem;
      transition: var(--transition);
    }

    .form-control:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.25);
    }

    textarea.form-control {
      min-height: 120px;
      resize: vertical;
    }

    .file-upload {
      position: relative;
      display: flex;
      flex-direction: column;
    }

    .file-upload-input {
      opacity: 0;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      cursor: pointer;
      z-index: 2;
    }

    .file-upload-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 12px 15px;
      background-color: #f8f9fa;
      border: 1px dashed var(--border-color);
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: var(--transition);
      margin-bottom: 10px;
    }

    .file-upload-btn:hover {
      background-color: #e9ecef;
    }

    .file-upload-btn i {
      margin-right: 8px;
      font-size: 1.2rem;
    }

    .file-name {
      margin-top: 5px;
      font-size: 0.9rem;
      color: #666;
    }

    .image-preview-container {
      margin-top: 15px;
      text-align: center;
    }

    .preview-image {
      max-width: 100%;
      max-height: 250px;
      border-radius: var(--border-radius);
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      object-fit: contain;
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 34px;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }

    input:checked + .slider {
      background-color: var(--primary-color);
    }

    input:focus + .slider {
      box-shadow: 0 0 1px var(--primary-color);
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }

    .switch-container {
      display: flex;
      align-items: center;
    }

    .switch-label {
      margin-left: 10px;
      font-weight: 600;
    }

    .btn-container {
      margin-top: 30px;
      display: flex;
      gap: 15px;
      justify-content: flex-end;
    }

    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      transition: var(--transition);
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background-color: var(--primary-hover);
    }

    .btn-secondary {
      background-color: var(--secondary-color);
      color: white;
    }

    .btn-secondary:hover {
      background-color: var(--secondary-hover);
    }

    .alert {
      padding: 15px 20px;
      margin-bottom: 20px;
      border-radius: var(--border-radius);
      font-weight: 500;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .text-muted {
      color: #6c757d;
      font-weight: normal;
      font-size: 0.9rem;
      margin-left: 5px;
    }

    .help-text {
      font-size: 0.85rem;
      color: #6c757d;
      margin-top: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .form-container {
        padding: 20px;
      }
      
      .btn-container {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1><?php echo $is_edit ? 'Sửa' : 'Thêm'; ?> Slider</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-circle"></i>
          <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
          ?>
        </div>
      <?php endif; ?>

      <div class="form-container">
        <div class="form-header">
          <h2><i class="fas fa-image"></i> <?php echo $is_edit ? 'Chỉnh sửa thông tin slider' : 'Thêm slider mới'; ?></h2>
        </div>
        
        <form method="post" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-col form-col-md-6">
              <div class="form-group">
                <label for="title">Tiêu đề</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($slider['title']); ?>" placeholder="Nhập tiêu đề slider">
                <div class="help-text">Tiêu đề sẽ hiển thị trên slider</div>
              </div>

              <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" class="form-control" placeholder="Nhập mô tả ngắn gọn"><?php echo htmlspecialchars($slider['description']); ?></textarea>
                <div class="help-text">Mô tả ngắn gọn về nội dung của slider</div>
              </div>

              <div class="form-row">
                <div class="form-col form-col-md-6">
                  <div class="form-group">
                    <label for="button_text">Nút (Text)</label>
                    <input type="text" id="button_text" name="button_text" class="form-control" value="<?php echo htmlspecialchars($slider['button_text']); ?>" placeholder="Ví dụ: Xem thêm">
                  </div>
                </div>
                <div class="form-col form-col-md-6">
                  <div class="form-group">
                    <label for="button_link">Nút (Link)</label>
                    <input type="text" id="button_link" name="button_link" class="form-control" value="<?php echo htmlspecialchars($slider['button_link']); ?>" placeholder="Ví dụ: /products">
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-col form-col-md-6">
                  <div class="form-group">
                    <label for="sort_order">Thứ tự hiển thị</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" value="<?php echo $slider['sort_order']; ?>" min="1" <?php echo !$is_edit ? 'readonly' : ''; ?>>
                    <div class="help-text">
                      <?php if (!$is_edit): ?>
                        Tự động tạo thứ tự tiếp theo
                      <?php else: ?>
                        Số nhỏ hơn sẽ hiển thị trước
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="form-col form-col-md-6">
                  <div class="form-group">
                    <label>Trạng thái</label>
                    <div class="switch-container">
                      <label class="switch">
                        <input type="checkbox" id="active" name="active" <?php echo $slider['active'] ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                      </label>
                      <span class="switch-label" id="status-text">
                        <?php echo $slider['active'] ? 'Hiển thị' : 'Ẩn'; ?>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-col form-col-md-6">
              <div class="form-group">
                <label for="image">Hình ảnh <span class="text-muted">(Kích thước đề xuất: 1920x1080px)</span></label>
                
                <div class="file-upload">
                  <div class="file-upload-btn" id="file-upload-btn">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <span>Chọn hình ảnh</span>
                  </div>
                  <input type="file" id="image" name="image" class="file-upload-input form-control" accept="image/*">
                  <div class="file-name" id="file-name">Chưa có file nào được chọn</div>
                </div>
                
                <div class="help-text">Hỗ trợ các định dạng: JPG, JPEG, PNG, GIF, WEBP</div>
                
                <div class="image-preview-container" id="image-preview-container">
                  <?php if (!empty($slider['image_path'])): ?>
                    <img src="../<?php echo $slider['image_path']; ?>" alt="Current Slider Image" class="preview-image" id="preview-image">
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

          <div class="btn-container">
            <a href="slider-management.php" class="btn btn-secondary">
              <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> <?php echo $is_edit ? 'Cập nhật' : 'Thêm mới'; ?>
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>

  <script>
    
    document.getElementById('image').addEventListener('change', function(e) {
      const file = e.target.files[0]; 
      const fileNameElement = document.getElementById('file-name');
      const previewContainer = document.getElementById('image-preview-container');
      
      if (file) {
     
        fileNameElement.textContent = file.name;
        
        const reader = new FileReader();
        reader.onload = function(event) {
        
          let previewImage = document.getElementById('preview-image');
          
          if (!previewImage) {
            previewImage = document.createElement('img');
            previewImage.id = 'preview-image';
            previewImage.className = 'preview-image';
            previewImage.alt = 'Image Preview';
            previewContainer.appendChild(previewImage);
          }
          
          previewImage.src = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
    
    document.getElementById('active').addEventListener('change', function(e) {
      const statusText = document.getElementById('status-text');
      statusText.textContent = this.checked ? 'Hiển thị' : 'Ẩn';
    });
    
   
    document.getElementById('file-upload-btn').addEventListener('click', function() {
      document.getElementById('image').click();
    });
  </script>
</body>

</html>