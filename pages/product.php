<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';


if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: products.php');
  exit;
}

$product_id = $_GET['id'];
$product = getProductById($conn, $product_id);

if (!$product) {
    header('Location: products.php');
    exit;
}
$toppings = getAllToppings($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_SESSION['cart'])) {
      $_SESSION['cart'] = [];
  }

  $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
  $sugar_level = isset($_POST['sugar_level']) ? (int)$_POST['sugar_level'] : 100;
  $ice_level = isset($_POST['ice_level']) ? (int)$_POST['ice_level'] : 100;
  $toppings_selected = isset($_POST['toppings']) ? $_POST['toppings'] : [];

  $cart_id = uniqid();
  $_SESSION['cart'][$cart_id] = [
      'product_id' => $product_id,
      'quantity' => $quantity,
      'sugar_level' => $sugar_level,
      'ice_level' => $ice_level,
      'toppings' => $toppings_selected,
      'added_at' => time()
  ];

  header('Location: cart.php');
  exit;
}
$related_products = getRelatedProducts($conn, $product['category_id'], $product_id);

$page_title = $product['name'] . " - " . SITE_NAME;
$active_menu = "product";
$extra_css = ['../assets/css/product-detail.css'];
include '../includes/header.php';
?>
<div class="product-detail-container">
  <div class="product-detail">
    <div class="product-image">
      <?php
        $imageSrc = $product['image'];
        if (!preg_match('/^https?:\/\//', $imageSrc)) {
            $imageSrc = '../' . ltrim($imageSrc, './');
        }
      ?>
      <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">

      <?php if ($product['is_new']): ?>
      <span class="tag">MỚI</span>
      <?php elseif ($product['discount_price'] > 0): ?>
      <span class="tag discount">GIẢM GIÁ</span>
      <?php endif; ?>
    </div>

    <div class="product-info">
      <h1><?php echo htmlspecialchars($product['name']); ?></h1>
      <p class="category">Danh mục: <?php echo htmlspecialchars($product['category_name']); ?></p>

      <div class="price-container">
        <?php if ($product['discount_price'] > 0): ?>
        <p class="price"><?php echo formatPrice($product['discount_price']); ?></p>
        <p class="old-price"><?php echo formatPrice($product['price']); ?></p>
        <?php $discount_percent = calculateDiscountPercent($product['price'], $product['discount_price']); ?>
        <span class="discount-badge">-<?php echo $discount_percent; ?>%</span>
        <?php else: ?>
        <p class="price"><?php echo formatPrice($product['price']); ?></p>
        <?php endif; ?>
      </div>

      <div class="description">
        <h3>Mô tả sản phẩm</h3>
        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
      </div>

      <form method="POST" action="" class="order-form">
        <div class="form-group">
          <label for="sugar_level">Mức đường:</label>
          <select name="sugar_level" id="sugar_level">
            <option value="100">100% (Bình thường)</option>
            <option value="70">70% (Vừa)</option>
            <option value="50">50% (Ít)</option>
            <option value="30">30% (Rất ít)</option>
            <option value="0">0% (Không đường)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="ice_level">Mức đá:</label>
          <select name="ice_level" id="ice_level">
            <option value="100">100% (Bình thường)</option>
            <option value="70">70% (Vừa)</option>
            <option value="50">50% (Ít)</option>
            <option value="30">30% (Rất ít)</option>
            <option value="0">0% (Không đá)</option>
          </select>
        </div>

        <?php if (count($toppings) > 0): ?>
        <div class="form-group">
          <label>Topping (+ 5.000đ mỗi loại):</label>
          <div class="toppings-list">
            <?php foreach($toppings as $topping): ?>
            <div class="topping-item">
              <input type="checkbox" name="toppings[]" id="topping-<?php echo $topping['id']; ?>"
                value="<?php echo $topping['id']; ?>">
              <label
                for="topping-<?php echo $topping['id']; ?>"><?php echo htmlspecialchars($topping['name']); ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label for="quantity">Số lượng:</label>
          <div class="quantity-control">
            <button type="button" id="decrease">-</button>
            <input type="number" name="quantity" id="quantity" value="1" min="1" max="10">
            <button type="button" id="increase">+</button>
          </div>
        </div>

        <button type="submit" class="add-to-cart-btn">
          <i class="fas fa-shopping-cart"></i> Thêm vào giỏ hàng
        </button>
      </form>
    </div>
  </div>

  <?php if (count($related_products) > 0): ?>
  <div class="related-products">
    <h2>Sản phẩm liên quan</h2>
    <div class="related-grid">
      <?php foreach($related_products as $product): ?>
      <?php include '../includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>