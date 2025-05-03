<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$coupon = [
    'id' => '',
    'code' => '',
    'discount_type' => 'percentage',
    'discount_value' => '',
    'min_order_value' => '0.00',
    'max_discount' => '',
    'start_date' => date('Y-m-d'),
    'end_date' => date('Y-m-d', strtotime('+30 days')),
    'usage_limit' => '',
    'usage_count' => 0,
    'active' => 1
];

$is_edit = false;


if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $coupon_id = $_GET['id'];
    $is_edit = true;
    
  
    $coupon_sql = "SELECT * FROM coupons WHERE id = ?";
    $coupon_stmt = $conn->prepare($coupon_sql);
    $coupon_stmt->bind_param("i", $coupon_id);
    $coupon_stmt->execute();
    $result = $coupon_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $coupon = $result->fetch_assoc();
    } else {
        header('Location: coupons.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $code = trim($_POST['code']);
    $discount_type = $_POST['discount_type'];
    $discount_value = floatval($_POST['discount_value']);
    $min_order_value = floatval($_POST['min_order_value']);
    $max_discount = $_POST['max_discount'] !== '' ? floatval($_POST['max_discount']) : null;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $usage_limit = $_POST['usage_limit'] !== '' ? intval($_POST['usage_limit']) : null;
    $active = isset($_POST['active']) ? 1 : 0;
    
    
    if (empty($code)) {
        $error_message = "Mã giảm giá không được để trống.";
    } else {
      
        $check_sql = "SELECT id FROM coupons WHERE code = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_id = $is_edit ? $coupon['id'] : 0;
        $check_stmt->bind_param("si", $code, $check_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "Mã giảm giá '$code' đã tồn tại. Vui lòng chọn mã khác.";
        } else {
           
            if ($discount_value <= 0) {
                $error_message = "Giá trị giảm giá phải lớn hơn 0.";
            } elseif ($discount_type === 'percentage' && $discount_value > 100) {
                $error_message = "Giá trị phần trăm không được vượt quá 100%.";
            } else {
               
                if ($is_edit) {
                    // Update existing coupon
                    $sql = "UPDATE coupons SET 
                            code = ?, 
                            discount_type = ?, 
                            discount_value = ?, 
                            min_order_value = ?, 
                            max_discount = ?, 
                            start_date = ?, 
                            end_date = ?, 
                            usage_limit = ?, 
                            active = ? 
                            WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdddssiis", $code, $discount_type, $discount_value, $min_order_value, 
                                      $max_discount, $start_date, $end_date, $usage_limit, $active, $coupon['id']);
                } else {
                    // Insert new coupon
                    $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_order_value, max_discount, 
                            start_date, end_date, usage_limit, usage_count, active, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdddssis", $code, $discount_type, $discount_value, $min_order_value, 
                                      $max_discount, $start_date, $end_date, $usage_limit, $active);
                }
                
                if ($stmt->execute()) {
                    $success_message = $is_edit ? "Mã giảm giá đã được cập nhật thành công." : "Mã giảm giá mới đã được tạo thành công.";
                    
                    if (!$is_edit) {
                        // Redirect to edit page for new coupons
                        $new_id = $conn->insert_id;
                        header("Location: coupon-form.php?id=$new_id&success=1");
                        exit;
                    }
                } else {
                    $error_message = "Lỗi: " . $conn->error;
                }
            }
        }
    }
    
    // Update coupon array with posted values for form redisplay in case of error
    if (isset($error_message)) {
        $coupon = [
            'id' => $is_edit ? $coupon['id'] : '',
            'code' => $code,
            'discount_type' => $discount_type,
            'discount_value' => $discount_value,
            'min_order_value' => $min_order_value,
            'max_discount' => $max_discount,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'usage_limit' => $usage_limit,
            'usage_count' => $is_edit ? $coupon['usage_count'] : 0,
            'active' => $active
        ];
    }
}

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Mã giảm giá mới đã được tạo thành công.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $is_edit ? 'Chỉnh sửa' : 'Thêm mới'; ?> mã giảm giá</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
  .form-group {
    margin-bottom: 15px;
  }

  .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
  }

  .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
  }

  .form-col {
    flex: 1;
  }

  .checkbox-group {
    margin-top: 20px;
  }

  .checkbox-group label {
    display: inline;
    margin-left: 5px;
  }

  .hint {
    font-size: 0.85em;
    color: #666;
    margin-top: 3px;
  }
  </style>
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1><?php echo $is_edit ? 'Chỉnh sửa' : 'Thêm mới'; ?> mã giảm giá</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="coupons.php" class="btn">Quay lại danh sách</a>
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

      <div class="content-form">
        <form method="POST" action="">
          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="code">Mã giảm giá *</label>
                <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($coupon['code']); ?>"
                  required maxlength="20">
                <div class="hint">Mã giảm giá phải là duy nhất, tối đa 20 ký tự.</div>
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="discount_type">Loại giảm giá *</label>
                <select id="discount_type" name="discount_type" required>
                  <option value="percentage" <?php echo $coupon['discount_type'] == 'percentage' ? 'selected' : ''; ?>>
                    Phần trăm (%)</option>
                  <option value="fixed" <?php echo $coupon['discount_type'] == 'fixed' ? 'selected' : ''; ?>>Số tiền cố
                    định</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="discount_value">Giá trị giảm giá *</label>
                <input type="number" id="discount_value" name="discount_value"
                  value="<?php echo htmlspecialchars($coupon['discount_value']); ?>" step="0.01" min="0" required>
                <div class="hint">Nếu là phần trăm, giá trị từ 0-100. Nếu là số tiền cố định, nhập số tiền.</div>
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="min_order_value">Giá trị đơn hàng tối thiểu</label>
                <input type="number" id="min_order_value" name="min_order_value"
                  value="<?php echo htmlspecialchars($coupon['min_order_value']); ?>" step="0.01" min="0">
                <div class="hint">Đơn hàng phải đạt giá trị này mới áp dụng được mã giảm giá. Để 0 nếu không có giới
                  hạn.</div>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="max_discount">Giảm giá tối đa</label>
                <input type="number" id="max_discount" name="max_discount"
                  value="<?php echo htmlspecialchars($coupon['max_discount'] ?? ''); ?>" step="0.01" min="0">
                <div class="hint">Chỉ áp dụng cho giảm giá phần trăm. Để trống nếu không giới hạn.</div>
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="usage_limit">Giới hạn sử dụng</label>
                <input type="number" id="usage_limit" name="usage_limit"
                  value="<?php echo htmlspecialchars($coupon['usage_limit'] ?? ''); ?>" min="0">
                <div class="hint">Số lần mã giảm giá có thể được sử dụng. Để trống nếu không giới hạn.</div>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-col">
              <div class="form-group">
                <label for="start_date">Ngày bắt đầu *</label>
                <input type="date" id="start_date" name="start_date"
                  value="<?php echo htmlspecialchars($coupon['start_date']); ?>" required>
              </div>
            </div>
            <div class="form-col">
              <div class="form-group">
                <label for="end_date">Ngày kết thúc *</label>
                <input type="date" id="end_date" name="end_date"
                  value="<?php echo htmlspecialchars($coupon['end_date']); ?>" required>
              </div>
            </div>
          </div>

          <?php if ($is_edit): ?>
          <div class="form-group">
            <label>Đã sử dụng:</label>
            <span><?php echo $coupon['usage_count']; ?> lần</span>
          </div>
          <?php endif; ?>

          <div class="checkbox-group">
            <input type="checkbox" id="active" name="active" value="1"
              <?php echo $coupon['active'] ? 'checked' : ''; ?>>
            <label for="active">Kích hoạt</label>
            <div class="hint">Mã giảm giá chỉ có thể sử dụng khi được kích hoạt.</div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn"><?php echo $is_edit ? 'Cập nhật' : 'Tạo mới'; ?></button>
            <a href="coupons.php" class="btn btn-secondary">Hủy</a>
          </div>
        </form>
      </div>
    </main>
  </div>
</body>

</html>