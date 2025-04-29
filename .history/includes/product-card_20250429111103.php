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
      