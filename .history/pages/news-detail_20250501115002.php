<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$user_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: news.php');
  exit;
}

$news_id = $_GET['id'];
$news = getNewsById($conn, $news_id);
if (!$news) {
  header('Location: news.php');
  exit;
}
$other_news = getNewsItems($conn, 3);
$page_title = $news['title'] . " - " . SITE_NAME;
$active_menu = "news";
$extra_css = ['../assets/css/news.css'];
include '../includes/header.php';
?>
<div class="news-detail-container">
  <div class="news-detail">
    <h1><?php echo $news['title']; ?></h1>
    <p class="news-date"><?php echo date('d/m/Y', strtotime($news['created_at'])); ?></p>

    <div class="news-image">
      <img src="<?php echo '../' . substr($news['image'], 2); ?>" alt="<?php echo $news['title']; ?>">
    </div>

    <div class="news-content">
      <?php echo nl2br($news['content']); ?>
    </div>

    <div class="news-actions">
      <a href="news.php" class="back-btn"><i class="fas fa-arrow-left"></i> Quay lại tin tức</a>
      <div class="share-buttons">
        <span>Chia sẻ:</span>
        <a href="#" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
        <a href="#" class="share-btn email"><i class="fas fa-envelope"></i></a>
      </div>
    </div>
  </div>

  <?php if (count($other_news) > 0): ?>
  <div class="other-news">
    <h2>Tin Tức Khác</h2>
    <div class="other-news-grid">
      <?php foreach($other_news as $item): ?>
      <?php if ($item['id'] != $news_id): ?>
      <div class="news-card">
        <div class="news-image">
          <img src="<?php echo '../' . substr($item['image'], 2); ?>" alt="<?php echo $item['title']; ?>">
        </div>
        <div class="news-content">
          <h3><?php echo $item['title']; ?></h3>
          <p class="news-date"><?php echo date('d/m/Y', strtotime($item['created_at'])); ?></p>
          <p class="news-excerpt"><?php echo substr($item['content'], 0, 100); ?>...</p>
          <a href="news-detail.php?id=<?php echo $item['id']; ?>" class="read-more-btn">Xem thêm</a>
        </div>
      </div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

