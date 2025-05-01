<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';


if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$categories = getAllCategories($conn);
$products = getProductsByCategory($conn, $category_id);

$page_title = "Sản Phẩm - " . SITE_NAME;
$active_menu = "products";
$extra_css = ['../assets/css/products.css'];
include '../includes/header.php';
?>
<div class="page-banner">
  <h1>Sản Phẩm Của Chúng Tôi</h1>
</div>

<div class="products-container">
  <div class="category-filter">
    <h3>Danh Mục</h3>
    <ul>
      <li><a href="products.php" class="<?php echo !$category_id ? 'active' : ''; ?>">Tất Cả</a></li>
      <?php foreach ($categories as $category): ?>
        <li>
          <a href="products.php?category=<?php echo $category['id']; ?>"
            class="<?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
            <?php echo $category['name']; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="products-grid">
    <?php if (count($products) > 0): ?>
      <?php foreach ($products as $product): ?>
        <?php include '../includes/product-card.php'; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-products">
        <p>Không có sản phẩm nào trong danh mục này.</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include '../includes/footer.php'; ?>