<?php
session_start();
require_once('../config/database.php');
if (isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}

$error = '';
$locked_message = '';
function isAccountLocked($conn, $email)
{
  $sql = "SELECT account_status, account_locked_until FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if ($user['account_status'] === 'locked' && $user['account_locked_until'] > date('Y-m-d H:i:s')) {
      // Calculate remaining lock time
      $lock_until = new DateTime($user['account_locked_until']);
      $now = new DateTime();
      $interval = $now->diff($lock_until);
      if ($interval->i > 0) {
        return $interval->format('%i phút %s giây');
      } else {
        return $interval->format('%s giây');
      }
    }
  }

  return false;
}
function recordLoginAttempt($conn, $email, $success)
{
  $ip = $_SERVER['REMOTE_ADDR'];
  $sql = "INSERT INTO login_attempts (email, ip_address, attempt_time, success) VALUES (?, ?, NOW(), ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssi", $email, $ip, $success);
  $stmt->execute();
}
function updateFailedAttempts($conn, $email)
{
  $sql = "UPDATE users SET failed_login_attempts = failed_login_attempts + 1, last_failed_login = NOW() WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $check_sql = "SELECT failed_login_attempts FROM users WHERE email = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("s", $email);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if ($check_result->num_rows === 1) {
    $user = $check_result->fetch_assoc();
    if ($user['failed_login_attempts'] >= 5) {
      $lock_sql = "UPDATE users SET account_status = 'locked', account_locked_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE email = ?";
      $lock_stmt = $conn->prepare($lock_sql);
      $lock_stmt->bind_param("s", $email);
      $lock_stmt->execute();
      return true;
    }
  }
  return false;
}
function resetFailedAttempts($conn, $email)
{
  $sql = "UPDATE users SET failed_login_attempts = 0, last_failed_login = NULL, account_status = 'active', account_locked_until = NULL WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];
  if (empty($email) || empty($password)) {
    $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
  } else {
    $locked_time = isAccountLocked($conn, $email);
    if ($locked_time !== false) {
      $locked_message = "Tài khoản của bạn đã bị khóa tạm thời. Vui lòng thử lại sau $locked_time.";
    } else {
      $sql = "SELECT * FROM users WHERE email = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
          recordLoginAttempt($conn, $email, 1);
          resetFailedAttempts($conn, $email);
          resetFailedAttempts($conn, $email);
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_name'] = $user['name'];
          if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
          } else {
            header('Location: ../index.php');
          }
          exit;
        } else {
          recordLoginAttempt($conn, $email, 0);
          $account_locked = updateFailedAttempts($conn, $email);

          if ($account_locked) {
            $locked_message = "Tài khoản của bạn đã bị khóa tạm thời do nhập sai mật khẩu nhiều lần. Vui lòng thử lại sau 5 phút.";
          } else {
            $error = 'Email hoặc mật khẩu không đúng.';
          }
        }
      } else {
        recordLoginAttempt($conn, $email, 0);
        $error = 'Email hoặc mật khẩu không đúng.';
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
  <title>Đăng Nhập - K-Tea</title>
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
          <li><a href="../index.php">Trang Chủ</a> </li>
          <li><a href="products.php">Sản Phẩm</a></li>
          <li><a href="news.php">Tin Tức</a></li>
          <li><a href="reviews.php">Đánh Giá</a></li>
         
        </ul>
      </div>
      <div class="others">
        <a href="login.php" class="login-btn active"><i class="fas fa-user"></i> Đăng nhập</a>
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
        <h2>Đăng Nhập</h2>

        <?php if (!empty($error)): ?>
        <div class="error-message">
          <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($locked_message)): ?>
        <div class="locked-message">
          <?php echo $locked_message; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
          </div>

          <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
          </div>

          <div class="form-group remember-me">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ghi nhớ đăng nhập</label>
          </div>

          <button type="submit" class="auth-btn">Đăng Nhập</button>
        </form>

        <div class="auth-links">
          <a href="forgot-password.php">Quên mật khẩu?</a>
          <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
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
    setTimeout(() => {
      document.querySelector(".menu").classList.add("show");
      document.querySelector(".auth-form").classList.add("show");
    }, 500);
  });
  window.addEventListener('scroll', function() {
    const header = document.getElementById('header');
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });
  </script>
</body>

</html>