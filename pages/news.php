<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
$news_items = getNewsItems($conn, 10);
$page_title = "Tin Tức - " . SITE_NAME;
$active_menu = "news";
$extra_css = ['../assets/css/news.css'];
include '../includes/header.php';
?>
<div class="page-banner">
  <h1>Tin Tức & Khuyến Mãi</h1>
</div>

<div class="news-page-container">
  <?php if (count($news_items) > 0): ?>
  <div class="news-grid">
    <?php foreach($news_items as $news): ?>
    <div class="news-card">
      <div class="news-image">
        <img src="<?php echo '../' . substr($news['image'], 2); ?>" alt="<?php echo $news['title']; ?>">
      </div>
      <div class="news-content">
        <h2><?php echo $news['title']; ?></h2>
        <p class="news-date"><?php echo date('d/m/Y', strtotime($news['created_at'])); ?></p>
        <p class="news-excerpt"><?php echo substr($news['content'], 0, 150); ?>...</p>
        <a href="news-detail.php?id=<?php echo $news['id']; ?>" class="read-more-btn">Xem thêm</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="no-news">
    <p>Không có tin tức nào.</p>
  </div>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>