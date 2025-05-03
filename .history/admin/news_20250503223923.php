<?php
session_start();
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
  $title = trim($_POST['title']);
  $content = trim($_POST['content']);
  $image = trim($_POST['image']);
  $status = $_POST['status'];

  if ($title && $content && $image && $status) {
    if ($id > 0) {
      $sql = "UPDATE news SET title=?, content=?, image=?, status=? WHERE id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssssi", $title, $content, $image, $status, $id);
      $stmt->execute();
      $message = "Đã cập nhật bản tin.";
    } else {
      $sql = "INSERT INTO news (title, content, image, status, created_at, updated_at) 
              VALUES (?, ?, ?, ?, NOW(), NOW())";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssss", $title, $content, $image, $status);
      $stmt->execute();
      $message = "Đã thêm bản tin mới.";
    }
  } else {
    $error = "Vui lòng điền đầy đủ thông tin.";
  }
}

// Xử lý xoá
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $conn->query("DELETE FROM news WHERE id=$id");
  $message = "Đã xoá bản tin.";
}

// Xử lý hiển thị bản tin để sửa
$editData = null;
if (isset($_GET['edit'])) {
  $id = intval($_GET['edit']);
  $result = $conn->query("SELECT * FROM news WHERE id=$id");
  if ($result->num_rows > 0) {
    $editData = $result->fetch_assoc();
  }
}

// Lấy danh sách tất cả bản tin
$news = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Quản lý bản tin</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
  body {
    font-family: Arial;

  }

  .main-container {
    margin-left: 250px;

    padding: 30px;
    background-color: #f9f9f9;
    min-height: 100vh;
  }

  .message {
    padding: 10px;
    background-color: #d4edda;
    color: #155724;
    border-radius: 6px;
    margin-bottom: 16px;
  }

  .error {
    padding: 10px;
    background-color: #f8d7da;
    color: #721c24;
    border-radius: 6px;
    margin-bottom: 16px;
  }

  table {
    border-collapse: collapse;
    width: 100%;
    margin-top: 20px;
  }

  th,
  td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: left;
  }

  form {
    background: #fff;
    padding: 24px;
    margin: 20px auto;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    max-width: 800px;
  }

  form label {
    font-weight: 600;
    margin-top: 12px;
    display: block;
    color: #333;
  }

  form input[type="text"],
  form textarea {
    width: 100%;
    padding: 10px 14px;
    margin-top: 6px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s;
  }

  form input[type="text"]:focus,
  form textarea:focus {
    border-color: #007bff;
    outline: none;
  }

  form button {
    margin-top: 20px;
    padding: 12px 24px;
    background-color: #007bff;
    border: none;
    color: white;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  form button:hover {
    background-color: #0056b3;
  }

  /* Nút Sửa */
  a.btn-edit {
    color: #fff;
    background-color: #28a745;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    transition: background-color 0.2s ease;
    display: inline-block;
  }

  a.btn-edit:hover {
    background-color: #218838;
  }

  /* Nút Xóa */
  a.btn-delete {
    color: #fff;
    background-color: #dc3545;
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 13px;
    transition: background-color 0.2s ease;
    display: inline-block;
    margin-left: 6px;
  }

  a.btn-delete:hover {
    background-color: #c82333;
  }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main-container">
    <header class="content-header">
      <h1>Quản lý bản tin</h1>
      <div class="user-info">
        <span>Xin chào, <?php echo $_SESSION['admin_name']; ?></span>
        <a href="logout.php" class="logout-btn">Đăng xuất</a>
      </div>
    </header>


    <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="post">
      <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
      <label>Tiêu đề:</label>
      <input type="text" name="title" required value="<?= $editData['title'] ?? '' ?>">

      <label>Nội dung:</label>
      <textarea name="content" rows="4" required><?= $editData['content'] ?? '' ?></textarea>

      <label>Ảnh (URL):</label>
      <input type="text" name="image" required value="<?= $editData['image'] ?? '' ?>">

      <label>Trạng thái:</label>
      <select name="status">
        <option value="published"
          <?= (isset($editData['status']) && $editData['status'] === 'published') ? 'selected' : '' ?>>Công khai
        </option>
        <option value="draft" <?= (isset($editData['status']) && $editData['status'] === 'draft') ? 'selected' : '' ?>>
          Nháp</option>
      </select>

      <button type="submit"><?= $editData ? 'Cập nhật' : 'Thêm mới' ?></button>
    </form>

    <table>
      <tr>
        <th>ID</th>
        <th>Tiêu đề</th>
        <th>Hình ảnh</th>
        <th>Nội dung</th>
        <th>Trạng thái</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
      </tr>
      <?php while ($row = $news->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['title']) ?></td>
        <td><img src="<?= $row['image'] ?>" height="40"></td>
        <td><?= $row['content'] ?></td>
        <td><?= $row['status'] ?></td>
        <td><?= $row['created_at'] ?></td>
        <td>
          <a href="?edit=<?= $row['id'] ?>">Sửa</a> |
          <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Xác nhận xoá?')">Xoá</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>

</html>