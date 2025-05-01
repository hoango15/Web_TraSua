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
$product_query = "SELECT id, name FROM products ORDER BY name";
$product_result = $conn->query($product_query);
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Đánh Giá Sản Phẩm</title>
  <link rel="stylesheet" href="css/user.css">
  <style>
  .form-container {
    max-width: 500px;
    margin: 30px auto;
    padding: 20px;
    border-radius: 10px;
    background-color: #f9f9f9;
    margin-top: 100px;
  }

  .form-container h2 {
    text-align: center;
  }

  .form-group {
    margin-bottom: 15px;
  }

  label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
  }

  select,
  textarea {
    width: 100%;
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
  }

  .btn-submit {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #28a745;
    color: white;
    border: none;
    font-weight: bold;
    cursor: pointer;
  }

  .success-message {
    color: green;
    text-align: center;
  }

  .error-message {
    color: red;
    text-align: center;
  }
  </style>
</head>

<body>
  <?php include '../includes/header.php'; ?>

  <div class="form-container">
    <h2>Gửi Đánh Giá Sản Phẩm</h2>

    <?php if (isset($success)) echo "<p class='success-message'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>

    <form method="POST" action="reviews.php">
      <div class="form-group">
        <label for="product_id">Chọn sản phẩm:</label>
        <select name="product_id" id="product_id" required>
          <option value="">-- Chọn sản phẩm --</option>
          <?php while ($product = $product_result->fetch_assoc()): ?>
          <option value="<?php echo $product['id']; ?>">
            <?php echo htmlspecialchars($product['name']); ?>
          </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="rating">Số sao (1-5):</label>
        <select name="rating" id="rating" required>
          <option value="">-- Chọn đánh giá --</option>
          <option value="5">5 sao - Tuyệt vời</option>
          <option value="4">4 sao - Tốt</option>
          <option value="3">3 sao - Bình thường</option>
          <option value="2">2 sao - Tệ</option>
          <option value="1">1 sao - Rất tệ</option>
        </select>
      </div>

      <div class="form-group">
        <label for="comment">Bình luận:</label>
        <textarea name="comment" id="comment" rows="4" placeholder="Viết bình luận của bạn..."></textarea>
      </div>

      <button type="submit" class="btn-submit">Gửi Đánh Giá</button>
    </form>
  </div>

  <?php include '../includes/footer.php'; ?>
</body>

</html>