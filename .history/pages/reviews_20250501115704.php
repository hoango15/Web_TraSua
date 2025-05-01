<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $product_id = $_POST['product_id'];
  $rating = $_POST['rating'];
  $comment = trim($_POST['comment']);
  if ($product_id && $rating) {
    $check_sql = "SELECT * FROM product_reviews WHERE product_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $product_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
      $error = "Bạn đã đánh giá sản phẩm này rồi.";
    } else {
      $insert_sql = "INSERT INTO product_reviews (product_id, user_id, rating, comment, created_at) 
                     VALUES (?, ?, ?, ?, NOW())";
      $stmt = $conn->prepare($insert_sql);
      $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
      if ($stmt->execute()) {
        $success = "Gửi đánh giá thành công!";
      } else {
        $error = "Lỗi khi gửi đánh giá.";
      }
    }
  } else {
    $error = "Vui lòng chọn sản phẩm và đánh giá.";
  }
}
