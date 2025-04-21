<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://kit.fontawesome.com/c90e4cc50b.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="<?php echo isset($is_home) ? './assets/css/home.css' : '../assets/css/home.css'; ?>">
  <?php if(isset($extra_css)):?>
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
    </header>
  </div>
</body>

</html>