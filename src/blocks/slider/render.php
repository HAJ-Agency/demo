<?php
if (!wp_script_is('swiper-js', 'enqueued')) {
   wp_enqueue_script(
      'swiper-js',
      'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
      [],
      null,
      true
   );
}
if (!wp_style_is('swiper-css', 'enqueued')) {
   wp_enqueue_style(
      'swiper-css',
      'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
      [],
      null
   );
}
?>
<div id="<?= $attributes['blockId']; ?>" class="<?= $attributes['mainClassName']; ?> swiper">
   <div class="swiper-wrapper">
      <?= $content; ?>
   </div>
   <div class="swiper-navigation">
      <button class="swiper-button-prev-custom" aria-label="Previous slide">
         <svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M23.7722 31L13.8672 21.095L23.7722 11.19L24.9972 12.4325L16.3347 21.095L24.9972 29.7575L23.7722 31Z" fill="currentColor" />
         </svg>
      </button>
      <button class="swiper-button-next-custom" aria-label="Next slide">
         <svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18.2278 11L28.1328 20.905L18.2278 30.81L17.0028 29.5675L25.6653 20.905L17.0028 12.2425L18.2278 11Z" fill="currentColor" />
         </svg>
      </button>
   </div>
</div>