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

    
  </div>
</footer>