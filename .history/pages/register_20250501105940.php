<?php
session_start();
require_once('../config/database.php');

if (isset($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = 'Vui lòng điền đầy đủ thông tin.';
  } elseif ($password !== $confirm_password) {
    $error = 'Mật khẩu xác nhận không khớp.';
  } elseif (strlen($password) < 6) {
    $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
  } else {
    $check_sql = "SELECT * FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = 'Email đã tồn tại. Vui lòng sử dụng email khác.';
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $sql = "INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("sss", $name, $email, $hashed_password);

      if ($stmt->execute()) {
        $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
      } else {
        $error = 'Đã xảy ra lỗi. Vui lòng thử lại.';
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://kit.fontawesome.com/c90e4cc50b.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="../assets/css/home.css">
  <link rel="stylesheet" href="../assets/css/auth.css">
  <title>Đăng Ký - K-Tea</title>
</head>

<body>
  <div id="preloader">
    <div class="loader"></div>
  </div>
  <div id="main">
    <Header id="header">
      <div class="logo">
        <a href="../index.php"><img src="../assets/img/logo.png" alt="K-Tea Logo"></a>
      </div>
      <div class="menu">
        <ul>
          <li><a href="../index.php">Trang Chủ</a></li>
          <li><a href="products.php">Sản Phẩm</a></li>
          <li><a href="news.php">Tin Tức</a></li>
          <li><a href="reviews.php">Đánh Giá</a></li>
          <li><a href="contact.php">Liên Hệ</a></li>
        </ul>
      </div>
      <div class="others">
        <a href="login.php" class="login-btn"><i class="fas fa-user"></i> Đăng nhập</a>
        <a href="cart.php" class="cart-btn">
          <i class="fas fa-shopping-cart"></i> Giỏ hàng
          <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
          <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
          <?php endif; ?>
        </a>
      </div>
    </Header>

    <div class="auth-container">
      <div class="auth-form">
        <h2>Đăng Ký</h2>

        <?php if (!empty($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
        <div class="success-message">
          <?php echo $success; ?>
          <p><a href="login.php">Nhấn vào đây để đăng nhập</a></p>
        </div>
        <?php else: ?>
        <form method="POST" action="">
          <div class="form-group">
            <label for="name">Họ và tên</label>
            <input type="text" id="name" name="name" value="<?php echo isset($name) ? $name : ''; ?>" required>
          </div>

          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
          </div>

          <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
            <small>Mật khẩu phải có ít nhất 6 ký tự</small>
          </div>

          <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
          </div>

          <button type="submit" class="auth-btn">Đăng Ký</button>
        </form>
        <?php endif; ?>

        <div class="auth-links">
          <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="footer-overlay"></div>
      <div class="footer-content">
        <h2 class="footer-title">K-Tea</h2>
        <p class="footer-address"><i class="fas fa-map-marker-alt"></i> 50 Tô Ký, Quận 12, HCM</p>
        <p class="footer-phone"><i class="fas fa-phone-alt"></i> 0896 547 435</p>
        <p class="footer-email"><i class="fas fa-envelope"></i> info@k-tea.com</p>
        <div class="social-icons">
          <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-icon"><i class="fab fa-tiktok"></i></a>
        </div>
        <p class="footer-copy">© <?php echo date('Y'); ?> K-Tea. All Rights Reserved.</p>
      </div>
    </footer>
  </div>

  <script>
     window.addEventListener("load", function() {
      const preloader = document.getElementById("preloader");
      preloader.classList.add("hidden");