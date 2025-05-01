<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}
if (isset($_GET['remove']) && isset($_SESSION['cart'][$_GET['remove']])) {
  unset($_SESSION['cart'][$_GET['remove']]);
  header('Location: cart.php');
  exit;
}
if (isset($_GET['update']) && isset($_SESSION['cart'][$_GET['update']]) && isset($_GET['quantity'])) {
  $quantity = (int)$_GET['quantity'];
  if ($quantity > 0 && $quantity <= 10) {
    $_SESSION['cart'][$_GET['update']]['quantity'] = $quantity;
  }
  header('Location: cart.php');
  exit;
}
$cart_data = getCartItems($conn, $_SESSION['cart']);
$page_title = "Giỏ Hàng - " . SITE_NAME;
$active_menu = "cart";
$extra_css = ['../assets/css/cart.css'];
include '../includes/header.php';
?>
<div class="cart-container">
  <h1>Giỏ Hàng Của Bạn</h1>

  <?php if (empty($cart_data['items'])): ?>
    <div class="empty-cart">
      <i class="fas fa-shopping-cart"></i>
      <p>Giỏ hàng của bạn đang trống</p>
      <a href="products.php" class="continue-shopping">Tiếp tục mua sắm</a>
    </div>
  <?php else: ?>
    <div class="cart-content">
      <div class="cart-items">
        <?php foreach ($cart_data['items'] as $item): ?>
          <div class="cart-item">
            <div class="item-image">
              <?php
              $imgSrc = $item['product']['image'];
              if (!preg_match('/^https?:\/\//', $imgSrc)) {
                $imgSrc = '../' . ltrim($imgSrc, './');
              }
              ?>
              <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                alt="<?php echo htmlspecialchars($item['product']['name']); ?>">
            </div>

            <div class="item-details">
              <h3><?php echo htmlspecialchars($item['product']['name']); ?></h3>
              <p class="item-options">
                <span>Đường: <?php echo (int)$item['sugar_level']; ?>%</span>
                <span>Đá: <?php echo (int)$item['ice_level']; ?>%</span>
                <?php if (!empty($item['toppings'])): ?>
                  <span>Topping: <?php echo htmlspecialchars(implode(', ', $item['toppings'])); ?></span>
                <?php endif; ?>
              </p>
              <p class="item-price"><?php echo formatPrice($item['item_price']); ?></p>
            </div>

            <div class="item-quantity">
              <form action="cart.php" method="GET">
                <input type="hidden" name="update" value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                <div class="quantity-control">
                  <button type="button" class="quantity-btn minus">-</button>
                  <input type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" max="10"
                    readonly>
                  <button type="button" class="quantity-btn plus">+</button>
                </div>
                <button type="submit" class="update-btn">Cập nhật</button>
              </form>
            </div>

            <div class="item-total">
              <p><?php echo formatPrice($item['item_total']); ?></p>
              <a href="cart.php?remove=<?php echo htmlspecialchars($item['cart_id']); ?>" class="remove-btn">
                <i class="fas fa-trash"></i>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="cart-summary">
        <h3>Tổng Giỏ Hàng</h3>
        <div class="summary-row">
          <span>Tạm tính:</span>
          <span><?php echo formatPrice($cart_data['subtotal']); ?></span>
        </div>
        <div class="summary-row">
          <span>Phí vận chuyển:</span>
          <span><?php echo formatPrice($cart_data['shipping']); ?></span>
        </div>
        <div class="summary-row total">
          <span>Tổng cộng:</span>
          <span><?php echo formatPrice($cart_data['total']); ?></span>
        </div>
        <div class="cart-actions">
          <a href="products.php" class="continue-shopping">Tiếp tục mua sắm</a>
          <a href="checkout.php" class="checkout-btn">Thanh toán</a>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>