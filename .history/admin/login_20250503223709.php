<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['admin_id'])) {
   header('Location: index.php');
   exit;
}

$error = '';
$locked_message = '';

function isAccountLocked($conn, $username) {
    $sql = "SELECT account_status, account_locked_until FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if ($admin['account_status'] === 'locked' && $admin['account_locked_until'] > date('Y-m-d H:i:s')) {
            $lock_until = new DateTime($admin['account_locked_until']);
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

function recordLoginAttempt($conn, $username, $success) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $sql = "INSERT INTO login_attempts (email, ip_address, attempt_time, success) VALUES (?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $username, $ip, $success);
    $stmt->execute();
}

function updateFailedAttempts($conn, $username) {
    $sql = "UPDATE admins SET failed_login_attempts = failed_login_attempts + 1, last_failed_login = NOW() WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $check_sql = "SELECT failed_login_attempts FROM admins WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 1) {
        $admin = $check_result->fetch_assoc();
        if ($admin['failed_login_attempts'] >= 5) {
            
            $lock_sql = "UPDATE admins SET account_status = 'locked', account_locked_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE username = ?";
            $lock_stmt = $conn->prepare($lock_sql);
            $lock_stmt->bind_param("s", $username);
            $lock_stmt->execute();
            return true;
        }
    }
    
    return false;
}

function resetFailedAttempts($conn, $username) {
    $sql = "UPDATE admins SET failed_login_attempts = 0, last_failed_login = NULL, account_status = 'active', account_locked_until = NULL WHERE username = ? AND account_status != 'disabled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   $username = $_POST['username'];
   $password = $_POST['password'];

   if (empty($username) || empty($password)) {
       $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.';
   } else {
       // Check if account is locked
       $locked_time = isAccountLocked($conn, $username);
       if ($locked_time !== false) {
           $locked_message = "Tài khoản của bạn đã bị khóa tạm thời. Vui lòng thử lại sau $locked_time.";
       } else {
           // Check if admin exists
           $sql = "SELECT * FROM admins WHERE username = ?";
           $stmt = $conn->prepare($sql);
           $stmt->bind_param("s", $username);
           $stmt->execute();
           $result = $stmt->get_result();
           
           if ($result->num_rows === 1) {
               $admin = $result->fetch_assoc();
               
               // Check if account is disabled
               if ($admin['account_status'] === 'disabled') {
                   $error = 'Tài khoản này đã bị vô hiệu hóa. Vui lòng liên hệ quản trị viên.';
               } else {
                   // Verify password
                   if (password_verify($password, $admin['password'])) {
                       // Record successful login
                       recordLoginAttempt($conn, $username, 1);
                       
                       // Reset failed attempts
                       resetFailedAttempts($conn, $username);
                       
                       // Set session variables
                       $_SESSION['admin_id'] = $admin['id'];
                       $_SESSION['admin_name'] = $admin['name'];
                       
                       header('Location: index.php');
                       exit;
                   } else {
                       // Record failed login
                       recordLoginAttempt($conn, $username, 0);
                       
                       // Update failed attempts counter
                       $account_locked = updateFailedAttempts($conn, $username);
                       
                       if ($account_locked) {
                           $locked_message = "Tài khoản của bạn đã bị khóa tạm thời do nhập sai mật khẩu nhiều lần. Vui lòng thử lại sau 5 phút.";
                       } else {
                           $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
                       }
                   }
               }
           } else {
               // Record failed login attempt for non-existent account
               recordLoginAttempt($conn, $username, 0);
               $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
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
  <title>Admin Login - Bubble Tea Shop</title>
  <link rel="stylesheet" href="css/admin.css">
</head>

<body class="login-page">
  <div class="login-container">
    <div class="login-header">
      <h1>K-Tea Shop </h1>
      <p>Trang đăng nhập Admin </p>
    </div>

    <div class="login-form">
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
          <label for="username">Tên đăng nhập</label>
          <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
          <label for="password">Mật khẩu</label>
          <input type="password" id="password" name="password" required>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn">Đăng nhập</button>
        </div>
      </form>

      <div class="back-to-site">
        <a href="../index.php">← Quay lại Website</a>
      </div>
    </div>
  </div>
</body>

</html>