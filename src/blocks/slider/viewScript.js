(function ($) {
   $(function () {
      $(".tiburon-slider").each(function (index, element) {
         const $slider = $(element);
         const $wrapper = $slider.find(".swiper-wrapper");

         // Create a unique Swiper instance per slider
         new Swiper($slider[0], {
            effect: "fade",
            fadeEffect: { crossFade: true },
            loop: true,
            navigation: {
               nextEl: $slider.find(".swiper-button-next-custom")[0],
               prevEl: $slider.find(".swiper-button-prev-custom")[0],
            },
         });
      });
   });
})(jQuery);
