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

<section class="best-seller">
  <h2 class="section-title">🔥 BEST SELLER 🔥</h2>
  <div class="product-list">
    <?php if (count($featured_products) > 0): ?>
    <?php foreach ($featured_products as $product): ?>
    <?php include 'includes/product-card.php'; ?>
    <?php endforeach; ?>
    <?php else: ?>
    <p>Không có sản phẩm nào.</p>
    <?php endif; ?>
  </div>
  <div class="view-all-container">
    <a href="./pages/products.php" class="view-all-btn">XEM TẤT CẢ</a>
  </div>
</section>


<div class="news-section">
  <h2 class="news-title1">📰 BẢNG TIN</h2>
  <div class="news-container">
    <?php if (count($news_items) > 0): ?>
    <?php foreach ($news_items as $news): ?>
    <div class="news-item">
      <div class="news-img">
        <img src="<?php echo $news['image']; ?>" alt="<?php echo $news['title']; ?>">
      </div>
      <div class="news-content">
        <div class="news-title"><?php echo $news['title']; ?></div>
        <div class="news-desc"><?php echo substr($news['content'], 0, 100); ?>...</div>
        <a href="pages/news-detail.php?id=<?php echo $news['id']; ?>" class="read-more">Xem thêm</a>
      </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <p>Không có tin tức nào.</p>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php if ($popup_ad): ?>

  <style>
.popup-ad {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.7);
}

.popup-content {
  position: relative;
  background-color: #fff;
  margin: 10% auto;
  padding: 0;
  width: 80%;
  max-width: 800px;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: popupFadeIn 0.5s;
}



  </style>