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
          rrecordLoginAttempt($conn, $email, 0);