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