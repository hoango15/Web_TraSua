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