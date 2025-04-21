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
window.addEventListener("load",function(){
  const preloader = document.getElementById("preloader");
  preloader.classList.add("hidden");
//Hiệu ứng xuất hiện khi preloader ẩn đi
  setTimeout(() => {
    document.querySelector(".menu").classList.add("show");
    <?php if (isset($is_home)): ?>
    document.querySelector(".slider").classList.add("show");
    document.querySelector(".product-list").classList.add("show");
    <?php elseif (isset($active_menu) && $active_menu == 'products'): ?>
    document.querySelector(".products-grid").classList.add("show");
    <?php elseif (isset($active_menu) && $active_menu == 'product'): ?>
    document.querySelector(".product-detail").classList.add("show");
    <?php elseif (isset($active_menu) && $active_menu == 'cart'): ?>
    document.querySelector(".cart-container").classList.add("show");
    <?php elseif (isset($active_menu) && ($active_menu == 'login' || $active_menu == 'register')): ?>
    document.querySelector(".auth-form").classList.add("show");
    <?php elseif (isset($active_menu) && $active_menu == 'account'): ?>
    document.querySelector(".account-content").classList.add("show");
    <?php elseif (isset($active_menu) && $active_menu == 'checkout'): ?>
    document.querySelector(".checkout-content").classList.add("show");
    <?php endif; ?>
  }, 500);

});
window.addEventListener('scroll', function(){
  const header = document.getElementById('header');
  if (window.scrollY>)


})


</script>