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