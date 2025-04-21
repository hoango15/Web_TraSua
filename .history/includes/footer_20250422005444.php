<?php
$footer_content = getFooterContent($conn);
?>

<footer class="footer">
  <div class="footer-overlay"></div>
  <div class="footer-content">
    <div class="footer-about">
      <h2 class="footer-tittle"><?php echo SITE_NAME; ?></h2>
      <p><?php echo $footer_content['about']; ?></p>
    </div>

    <div class="footer-contact">
      <h3>Thông Tin Liên Hệ</h3>
      <div class="contact-info">
        <?php echo $footer_content['contact']; ?>
      </div>
    </div>

    <div class="social-icons">
      <?php if (!empty($footer_content['social']['facebook'])): ?>
      <a href="<?php echo $footer_content['social']['facebook']; ?>" class="social-icon" target="_blank"><i
          class="fab fa-facebook-f"></i></a>
      <?php endif; ?>

      <?php if (!empty($footer_content['social']['instagram'])): ?>
      <a href="<?php echo $footer_content['social']['instagram']; ?>" class="social-icon" target="_blank"><i
          class="fab fa-instagram"></i></a>
      <?php endif; ?>

      <?php if (!empty($footer_content['social']['twitter'])): ?>
      <a href="<?php echo $footer_content['social']['twitter']; ?>" class="social-icon" target="_blank"><i
          class="fab fa-twitter"></i></a>
      <?php endif; ?>

      <?php if (!empty($footer_content['social']['tiktok'])): ?>
      <a href="<?php echo $footer_content['social']['tiktok']; ?>" class="social-icon" target="_blank"><i
          class="fab fa-tiktok"></i></a>
      <?php endif; ?>
    </div>

    
  </div>
</footer>

<script>
window.addEventListener("load")


</script>