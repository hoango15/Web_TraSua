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
