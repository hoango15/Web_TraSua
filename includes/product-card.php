<div class="product">
  <div class="product-img">
    <?php if ($product['is_new']): ?>
    <span class="tag">MỚI</span>
    <?php elseif ($product['discount_price'] > 0): ?>
    <span class="tag discount">GIẢM GIÁ</span>
    <?php endif; ?>


    <?php
      $imageSrc = $product['image'];
      if (!isset($is_home) && !preg_match('/^https?:\/\//', $imageSrc)) {
        $imageSrc = '../' . ltrim($imageSrc, './');
      }
      ?>
        <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>

        <h3><?php echo htmlspecialchars($product['name']); ?></h3>

  <?php if ($product['rating_avg'] > 0): ?>
  <div class="product-rating">
    <div class="stars">
      <?php for ($i = 1; $i <= 5; $i++): ?>
      <?php if ($i <= round($product['rating_avg'])): ?>
      <i class="fas fa-star"></i>
      <?php else: ?>
      <i class="far fa-star"></i>
      <?php endif; ?>
      <?php endfor; ?>
    </div>
    <span class="review-count">(<?php echo $product['review_count']; ?>)</span>
  </div>
  <?php endif; ?>

  <p class="price">
    <?php if ($product['discount_price'] > 0): ?>
    <?php echo formatPrice($product['discount_price']); ?>
    <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
    <?php else: ?>
    <?php echo formatPrice($product['price']); ?>
    <?php endif; ?>
  </p>

  <a href="<?php echo isset($is_home) ? 'pages/product.php?id=' . $product['id'] : '../pages/product.php?id=' . $product['id']; ?>"
    class="order-btn">🛒 Đặt hàng</a>
</div>