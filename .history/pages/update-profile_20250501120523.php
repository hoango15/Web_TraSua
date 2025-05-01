<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['user_id'])) {
  $_SESSION['redirect_after_login'] = '../pages/account.php';
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];
$redirect_tab = '../profile';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['name'])) {
    updateProfile($conn, $user_id);
    $redirect_tab = 'profile';
  } elseif (isset($_POST['address'])) {
    updateAddress($conn, $user_id);
    $redirect_tab = 'address';
  } elseif (isset($_POST['current_password'])) {
    changePassword($conn, $user_id);
    $redirect_tab = 'password';
  } else {
    $_SESSION['error_message'] = "Yêu cầu không hợp lệ";
  }
}
header("Location: account.php#$redirect_tab");
exit;


function updateProfile($conn, $user_id)
{
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  if (empty($name)) {
    $_SESSION['error_message'] = "Vui lòng nhập họ tên";
    return;
  }
  try {
    $sql = "UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $phone, $user_id);

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Cập nhật thông tin cá nhân thành công!";
    } else {
      $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật thông tin. Vui lòng thử lại.";
    }
  } catch (Exception $e) {
    $_SESSION['error_message'] = "Lỗi hệ thống: " . $e->getMessage();
    error_log("Error updating user profile: " . $e->getMessage());
  }
}


function updateAddress($conn, $user_id)
{
  $address = trim($_POST['address'] ?? '');
  $city = trim($_POST['city'] ?? '');
  $district = trim($_POST['district'] ?? '');
  $delivery_notes = trim($_POST['notes'] ?? '');
  if (empty($address)) {
    $_SESSION['error_message'] = "Vui lòng nhập địa chỉ";
    return;
  }
  try {

    $sql = "UPDATE users SET address = ?, city = ?, district = ?, delivery_notes = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $address, $city, $district, $delivery_notes, $user_id);

    if ($stmt->execute()) {
      $_SESSION['success_message'] = "Cập nhật địa chỉ thành công!";
    } else {
      $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật địa chỉ. Vui lòng thử lại.";
    }
  } catch (Exception $e) {
    $_SESSION['error_message'] = "Lỗi hệ thống: " . $e->getMessage();
    error_log("Error updating user address: " . $e->getMessage());
  }
}

function changePassword($conn, $user_id)
{
  $current_password = $_POST['current_password'] ?? '';
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    $_SESSION['error_message'] = "Vui lòng điền đầy đủ thông tin mật khẩu";
    return;
  }

  if (strlen($new_password) < 6) {
    $_SESSION['error_message'] = "Mật khẩu mới phải có ít nhất 6 ký tự";
    return;
  }

  if ($new_password !== $confirm_password) {
    $_SESSION['error_message'] = "Mật khẩu xác nhận không khớp";
    return;
  }

  try {

    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      $_SESSION['error_message'] = "Không tìm thấy thông tin người dùng";
      return;
    }

    $user = $result->fetch_assoc();
    if (!password_verify($current_password, $user['password'])) {
      $_SESSION['error_message'] = "Mật khẩu hiện tại không đúng";
      return;
    }
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $update_sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $hashed_password, $user_id);

    if ($update_stmt->execute()) {
      $_SESSION['success_message'] = "Đổi mật khẩu thành công!";
    } else {
      $_SESSION['error_message'] = "Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng thử lại.";
    }
  } catch (Exception $e) {
    $_SESSION['error_message'] = "Lỗi hệ thống: " . $e->getMessage();
    error_log("Error changing password: " . $e->getMessage());
  }
}