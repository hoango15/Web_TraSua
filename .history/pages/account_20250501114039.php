<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_after_login'] = 'pages/account.php';
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$user = getUserById($conn, $user_id);
$orders = getUserOrders($conn, $user_id);

$page_title = "Tài Khoản - " . SITE_NAME;
$active_menu = "account";
$extra_css = ['../assets/css/account.css'];
include '../includes/header.php';
?>
<div class="account-container">
  <div class="account-sidebar">
    <div class="user-info">
      <div class="avatar">
        <i class="fas fa-user"></i>
      </div>
      <h3><?php echo $user['name']; ?></h3>
      <p><?php echo $user['email']; ?></p>
    </div>

    <ul class="account-menu">
      <li class="active"><a href="#orders" data-tab="orders"><i class="fas fa-shopping-bag"></i> Đơn Hàng Của Tôi</a>
      </li>
      <li><a href="#profile" data-tab="profile"><i class="fas fa-user-edit"></i> Thông Tin Cá Nhân</a></li>
      <li><a href="#address" data-tab="address"><i class="fas fa-map-marker-alt"></i> Địa Chỉ Giao Hàng</a></li>
      <li><a href="#password" data-tab="password"><i class="fas fa-lock"></i> Đổi Mật Khẩu</a></li>
      <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a></li>
    </ul>
  </div>

  <div class="account-content">
    <div class="tab-content active" id="orders-content">
      <h2>Đơn Hàng Của Tôi</h2>

      <?php if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success_message'];
                                          unset($_SESSION['success_message']); ?>
      </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
      <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                        unset($_SESSION['error_message']); ?></div>
      <?php endif; ?>

      <?php if (empty($orders)): ?>
      <div class="empty-orders">
        <i class="fas fa-shopping-bag"></i>
        <p>Bạn chưa có đơn hàng nào</p>
        <a href="products.php" class="btn">Mua Sắm Ngay</a>
      </div>
      <?php else: ?>
      <div class="orders-list">
        <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="order-header">
            <div>
              <h3>Đơn Hàng #<?php echo $order['id']; ?></h3>
              <p class="order-date"><?php echo formatDate($order['created_at']); ?></p>
            </div>
            <div class="order-status <?php echo $order['status']; ?>">
              <?php echo getStatusText($order['status']); ?>
            </div>
          </div>

          <div class="order-summary">
            <div class="summary-item">
              <span>Tổng tiền:</span>
              <span class="price"><?php echo formatPrice($order['total']); ?></span>
            </div>
            <div class="summary-item">
              <span>Phương thức thanh toán:</span>
              <span><?php echo getPaymentMethodText($order['payment_method']); ?></span>
            </div>
            <div class="summary-item">
              <span>Trạng thái thanh toán:</span>
              <span class="<?php echo $order['payment_status']; ?>">
                <?php echo getPaymentStatusText($order['payment_status']); ?>
              </span>
            </div>
          </div>

          <div class="order-actions">
            <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn">Xem Chi Tiết</a>

            <?php if ($order['status'] == 'pending' && $order['payment_status'] == 'unpaid' && $order['payment_method'] == 'momo'): ?>
            <a href="payment.php?order_id=<?php echo $order['id']; ?>" class="btn payment">Thanh Toán</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <div class="tab-content" id="profile-content">
      <h2>Thông Tin Cá Nhân</h2>

      <?php if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success_message'];
                                          unset($_SESSION['success_message']); ?>
      </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
      <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                        unset($_SESSION['error_message']); ?></div>
      <?php endif; ?>

      <form action="update-profile.php" method="POST" class="profile-form">
        <div class="form-group">
          <label for="name">Họ và tên</label>
          <input type="text" id="name" name="name" value="<?php echo $user['name']; ?>" required>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" readonly>
          <small>Email không thể thay đổi</small>
        </div>

        <div class="form-group">
          <label for="phone">Số điện thoại</label>
          <input type="tel" id="phone" name="phone" value="<?php echo $user['phone'] ?? ''; ?>">
        </div>

        <button type="submit" class="btn">Cập Nhật Thông Tin</button>
      </form>
    </div>

    <div class="tab-content" id="address-content">
      <h2>Địa Chỉ Giao Hàng</h2>

      <?php if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success_message'];
                                          unset($_SESSION['success_message']); ?>
      </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
      <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                        unset($_SESSION['error_message']); ?></div>
      <?php endif; ?>

      <form action="update-profile.php" method="POST" class="address-form">
        <div class="form-group">
          <label for="address">Địa chỉ</label>
          <textarea id="address" name="address" rows="3" required><?php echo $user['address'] ?? ''; ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="city">Thành phố</label>
            <input type="text" id="city" name="city" value="<?php echo $user['city'] ?? ''; ?>">
          </div>

          <div class="form-group">
            <label for="district">Quận/Huyện</label>
            <input type="text" id="district" name="district" value="<?php echo $user['district'] ?? ''; ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="notes">Ghi chú giao hàng</label>
          <textarea id="notes" name="notes" rows="2"><?php echo $user['delivery_notes'] ?? ''; ?></textarea>
        </div>

        <button type="submit" class="btn">Cập Nhật Địa Chỉ</button>
      </form>
    </div>

    <div class="tab-content" id="password-content">
      <h2>Đổi Mật Khẩu</h2>

      <?php if (isset($_SESSION['success_message']) && !empty($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?php echo $_SESSION['success_message'];
                                          unset($_SESSION['success_message']); ?>
      </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
      <div class="alert alert-danger"><?php echo $_SESSION['error_message'];
                                        unset($_SESSION['error_message']); ?></div>
      <?php endif; ?>

      <form action="update-profile.php" method="POST" class="password-form">
        <div class="form-group">
          <label for="current_password">Mật khẩu hiện tại</label>
          <input type="password" id="current_password" name="current_password" required>
        </div>

        <div class="form-group">
          <label for="new_password">Mật khẩu mới</label>
          <input type="password" id="new_password" name="new_password" required>
          <small>Mật khẩu phải có ít nhất 6 ký tự</small>
        </div>

        <div class="form-group">
          <label for="confirm_password">Xác nhận mật khẩu mới</label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn">Đổi Mật Khẩu</button>
      </form>
    </div>
  </div>
</div>

<script>
const tabLinks = document.querySelectorAll('.account-menu a[data-tab]');
const tabContents = document.querySelectorAll('.tab-content');

tabLinks.forEach(link => {
  link.addEventListener('click', function(e) {
    const tabId = this.getAttribute('data-tab');
    if (!tabId) return;

    e.preventDefault();
