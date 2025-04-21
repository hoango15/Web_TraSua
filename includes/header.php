<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://kit.fontawesome.com/c90e4cc50b.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="<?php echo isset($is_home) ? './assets/css/home.css' : '../assets/css/home.css'; ?>">
  <?php if (isset($extra_css)): ?>
    <?php foreach ($extra_css as $css): ?>
      <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
  <?php endif; ?>
  <title><?php echo isset($page_title) ? $page_title : SITE_NAME; ?></title>
</head>

<body>
  <div id="preloader">
    <div class="loader"></div>
  </div>
  <div id="main">
    <header id="header">
      <div class="logo">
        <a href="<?php echo isset($is_home) ? 'index.php' : '../index.php'; ?>">
          <img src="<?php echo isset($is_home) ? './assets/img/logo.png' : '../assets/img/logo.png'; ?>"
            alt="<?php echo SITE_NAME; ?> Logo">
        </a>
      </div>
      <div class="menu">
        <ul>
          <li><a href="<?php echo isset($is_home) ? 'index.php' : '../index.php'; ?>"
              <?php echo isset($is_home) ? 'class="active"' : ''; ?>>Trang Chủ</a></li>
          <li><a href="<?php echo isset($is_home) ? 'pages/products.php' : '../pages/products.php'; ?>"
              <?php echo isset($active_menu) && $active_menu == 'products' ? 'class="active"' : ''; ?>>Sản Phẩm</a></li>
          <li><a href="<?php echo isset($is_home) ? 'pages/news.php' : '../pages/news.php'; ?>"
              <?php echo isset($active_menu) && $active_menu == 'news' ? 'class="active"' : ''; ?>>Tin Tức</a></li>
          <li><a href="<?php echo isset($is_home) ? 'pages/reviews.php' : '../pages/reviews.php'; ?>"
              <?php echo isset($active_menu) && $active_menu == 'reviews' ? 'class="active"' : ''; ?>>Đánh Giá</a></li>

        </ul>
      </div>
      <div class="others">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="<?php echo isset($is_home) ? 'pages/account.php' : 'account.php'; ?>"
            class="login-btn <?php echo isset($active_menu) && $active_menu == 'account' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> Tài khoản
          </a>
        <?php else: ?>
          <a href="<?php echo isset($is_home) ? 'pages/login.php' : 'login.php'; ?>"
            class="login-btn <?php echo isset($active_menu) && $active_menu == 'login' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> Đăng nhập
          </a>
        <?php endif; ?>
        <a href="<?php echo isset($is_home) ? 'pages/cart.php' : 'cart.php'; ?>"
          class="cart-btn <?php echo isset($active_menu) && $active_menu == 'cart' ? 'active' : ''; ?>">
          <i class="fas fa-shopping-cart"></i> Giỏ hàng
          <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
            <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
          <?php endif; ?>
        </a>
      </div>
    </header>
  </div>
</body>

</html>