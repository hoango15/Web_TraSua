<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $coupon_id = $_GET['delete'];
    $delete_sql = "DELETE FROM coupons WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $coupon_id);
    
    if ($delete_stmt->execute()) {
        $success_message = "Mã giảm giá đã được xóa thành công.";
    } else {
        $error_message = "Lỗi khi xóa mã giảm giá.";
    }
}
$coupons_sql = "SELECT * FROM coupons ORDER BY created_at DESC";
$coupons_result = $conn->query($coupons_sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý mã giảm giá</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
      <header class="content-header">
        <h1>Quản lý mã giảm giá</h1>
        <div class="user-info">
          <span>Welcome, <?php echo $_SESSION['admin_name']; ?></span>
          <a href="logout.php" class="logout-btn">Logout</a>
        </div>
      </header>

      <div class="content-actions">
        <a href="coupon-form.php" class="btn">Thêm mã giảm giá mới</a>
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
              <th>Mã</th>
              <th>Loại</th>
              <th>Giá trị</th>
              <th>Đơn hàng tối thiểu</th>
              <th>Giảm tối đa</th>
              <th>Ngày bắt đầu</th>
              <th>Ngày kết thúc</th>
              <th>Giới hạn sử dụng</th>
              <th>Đã sử dụng</th>
              <th>Trạng thái</th>
              <th>Hoạt động</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($coupons_result && $coupons_result->num_rows > 0): ?>
            <?php while ($coupon = $coupons_result->fetch_assoc()): ?>
            <tr>
              <td><?php echo $coupon['id']; ?></td>
              <td><?php echo $coupon['code']; ?></td>
              <td>
                <?php 
                  echo $coupon['discount_type'] == 'percentage' ? 'Phần trăm (%)' : 'Cố định';
                ?>
              </td>
              <td>
                <?php 
                  echo $coupon['discount_value']; 
                  echo $coupon['discount_type'] == 'percentage' ? '%' : ' đ';
                ?>
              </td>
              <td><?php echo number_format($coupon['min_order_value'], 0, ',', '.'); ?> đ</td>
              <td>
                <?php 
                  echo $coupon['max_discount'] ? number_format($coupon['max_discount'], 0, ',', '.') . ' đ' : 'Không giới hạn'; 
                ?>
              </td>
              <td><?php echo date('d/m/Y', strtotime($coupon['start_date'])); ?></td>
              <td><?php echo date('d/m/Y', strtotime($coupon['end_date'])); ?></td>
              <td>
                <?php 
                  echo $coupon['usage_limit'] ? $coupon['usage_limit'] : 'Không giới hạn'; 
                ?>
              </td>
              <td><?php echo $coupon['usage_count']; ?></td>
              <td>
                <?php if ($coupon['active'] == 1): ?>
                <span class="status-active">Hoạt động</span>
                <?php else: ?>
                <span class="status-inactive">Không hoạt động</span>
                <?php endif; ?>
              </td>
              <td class="actions">
                <a href="coupon-form.php?id=<?php echo $coupon['id']; ?>" class="edit-btn">Sửa</a>
                <a href="coupons.php?delete=<?php echo $coupon['id']; ?>" class="delete-btn"
                  onclick="return confirm('Bạn có chắc chắn muốn xóa mã giảm giá này?')">Xóa</a>
              </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr>
              <td colspan="12">Không có mã giảm giá nào</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>

</html>