<?php
session_start();

require_once 'includes/config.php';
require_once 'includes/functions.php';

$featured_products = getTopRatedProducts($conn,8);

$news_items = getNewsItems($conn, 4);

$slider_images = getSliderImages($conn);

$popup_ad = getActivePopupAd($conn);

$page_title = "K-Tea - Trà Sữa Ngon Nhất Thành Phố";
$is_home = true;
include 'includes/header.php';
?>

<?php if ($popup_ad): ?>
<div id="popup-ad" class="popup-ad">
  <div class="popup-content">
    <span class="close-popup">&times;</span>
    <div class="popup-image">
      <img src="<?php echo $popup_ad['image']; ?>" alt="<?php echo $popup_ad['title']; ?>">
    </div>
    <div class="popup-text">
      <h3><?php echo $popup_ad['title']; ?></h3>
      <?php if (!empty($popup_ad['description'])): ?>
      <p><?php echo nl2br($popup_ad['description']); ?></p>
      <?php endif; ?>
      <?php if (!empty($popup_ad['button_text']) && !empty($popup_ad['button_link'])): ?>
      <a href="<?php echo $popup_ad['button_link']; ?>" class="popup-btn"><?php echo $popup_ad['button_text']; ?></a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>
<div class="slider">
  <div class="slides">
    <?php if (count($slider_images) > 0): ?>
    <?php foreach ($slider_images as $index => $image): ?>
    <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>"
      style="background-image: url('<?php echo $image['image_path']; ?>');">
      <div class="slide-content">
        <?php if (!empty($image['title'])): ?>
        <h2><?php echo $image['title']; ?></h2>
        <?php endif; ?>

        <?php if (!empty($image['description'])): ?>
        <p><?php echo $image['description']; ?></p>
        <?php endif; ?>

        <?php if (!empty($image['button_text']) && !empty($image['button_link'])): ?>
        <a href="<?php echo $image['button_link']; ?>" class="slide-btn"><?php echo $image['button_text']; ?></a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="slide active" style="background-image: url('./assets/img/1.jpg');"></div>
    <div class="slide" style="background-image: url('./assets/img/2.jpg');"></div>
    <div class="slide" style="background-image: url('./assets/img/3.jpg');"></div>
    <?php endif; ?>
  </div>
  <div class="dots">
    <?php for ($i = 0; $i < (count($slider_images) > 0 ? count($slider_images) : 3); $i++): ?>
    <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
    <?php endfor; ?>
  </div>
  <div class="scroll-down">
    Kéo xuống
    <i class="fas fa-chevron-down"></i>
  </div>
</div>