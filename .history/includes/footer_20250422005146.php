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
  </div>
</footer>