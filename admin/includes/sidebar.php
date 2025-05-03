<aside class="sidebar">
  <div class="sidebar-header">
    <h2>K-Tea Shop </h2>
    <p>Trang Admin </p>
  </div>

  <nav class="sidebar-nav">
    <ul>
      <li>
        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
          <span class="icon">📊</span>
          <span>Trang chủ </span>
        </a>
      </li>
      <li>
        <a href="orders.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' || basename($_SERVER['PHP_SELF']) == 'order-details.php' ? 'active' : ''; ?>">
          <span class="icon">📋</span>
          <span>Đơn hàng </span>
        </a>
      </li>
      <li>
        <a href="products.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product-form.php' ? 'active' : ''; ?>">
          <span class="icon">🧋</span>
          <span>Sản phẩm </span>
        </a>
      </li>
      <li>
        <a href="categories.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' || basename($_SERVER['PHP_SELF']) == 'category-form.php' ? 'active' : ''; ?>">
          <span class="icon">🗂️</span>
          <span>Loại sản phẩm </span>
        </a>
      </li>
      <li>
        <a href="toppings.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'toppings.php' || basename($_SERVER['PHP_SELF']) == 'topping-form.php' ? 'active' : ''; ?>">
          <span class="icon">🧠</span>
          <span>Toppings</span>
        </a>
      </li>
      <li>
        <a href="users.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' || basename($_SERVER['PHP_SELF']) == 'user-details.php' ? 'active' : ''; ?>">
          <span class="icon">👥</span>
          <span>Khách hàng </span>
        </a>
      </li>
      <li>
        <a href="admins.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'admins.php' || basename($_SERVER['PHP_SELF']) == 'admin-form.php' ? 'active' : ''; ?>">
          <span class="icon">📂</span>
          <span>Nhân Viên </span>
        </a>
      </li>
      <li>
        <a href="admin_roles.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_roles.php' || basename($_SERVER['PHP_SELF']) == 'admin_roles.php' ? 'active' : ''; ?>">
          <span class="icon">🛠</span>
          <span> Vai trò </span>
        </a>
      </li>
      <li>
        <a href="Reviews.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' || basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>">
          <span class="icon">📩</span>
          <span>Reviews </span>
        </a>
      </li>
      <li>
        <a href="popup_ads.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'popup_ads.php' || basename($_SERVER['PHP_SELF']) == 'popup_ad_form.php' ? 'active' : ''; ?>">
          <span class="icon">🔝</span>
          <span>Quảng cáo </span>
        </a>
      </li>
      <li>
        <a href="news.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'news.php' || basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : ''; ?>">
          <span class="icon">📤</span>
          <span>Tin Tức </span>
        </a>
      </li>
      <li>
        <a href="coupons.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'coupons.php' || basename($_SERVER['PHP_SELF']) == 'coupon-form.php' ? 'active' : ''; ?>">
          <span class="icon">💰</span>
          <span>Mã giảm giá </span>
        </a>
      </li>
      <li>
        <a href="slider-management.php"
          class="<?php echo basename($_SERVER['PHP_SELF']) == 'slider-management' || basename($_SERVER['PHP_SELF']) == 'slider-form.php' ? 'active' : ''; ?>">
          <span class="icon">💰</span>
          <span>Slide</span>
        </a>
      </li>
    </ul>
  </nav>

  <div class="sidebar-footer">
    <a href="../index.php" target="_blank">
      <span class="icon">🏠</span>
      <span>Về trang Website </span>
    </a>
  </div>
</aside>