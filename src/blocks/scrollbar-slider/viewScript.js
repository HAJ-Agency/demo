(function ($) {
   $(function () {
      const swiper = new Swiper('.scrollbar-slider', {
         direction: 'horizontal',
         slidesPerView:
            $('.scrollbar-slider').data('posts-per-view-mobile') || 1,
         spaceBetween: 10,
         scrollbar: {
            el: '.swiper-scrollbar',
            draggable: true,
         },
         breakpoints: {
            640: {
               slidesPerView:
                  $('.scrollbar-slider').data('posts-per-view-desktop') || 2,
            },
         },
      });
   });
})(jQuery);
