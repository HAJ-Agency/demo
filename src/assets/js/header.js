(function ($) {
   $(function () {
      var headerHeight = $("header").outerHeight();
      $(":root").css("--header-height", headerHeight + "px");
      $("main").css("margin-top", headerHeight + "px");
      $("nav.header-navigation").css("top", headerHeight + "px");
      $(window).on("resize", function () {
         var headerHeight = $("header").outerHeight();
         $(":root").css("--header-height", headerHeight + "px");
         $("main").css("margin-top", headerHeight + "px");
         $("nav.header-navigation").css("top", headerHeight + "px");
      });

      // on scroll, set top headerheight on nav.header-navigation
      let scrollTimeout;
      $(window).on("scroll resize", function () {
         clearTimeout(scrollTimeout);
         scrollTimeout = setTimeout(function () {
            var headerHeight = $("header").outerHeight();
            $("nav.header-navigation").css("top", headerHeight + "px");
         }, 100);
      });

      $(".wp-block-button.is-style-menu-btn").each(function () {
         $(this).on("click", function () {
            $(this).toggleClass("open");
            $("nav.header-navigation").toggleClass("open");
            if (!$("nav.header-navigation").hasClass("open")) {
               setTimeout(function () {
                  $("nav.header-navigation").css("visibility", "hidden");
               }, 200);
            } else {
               $("nav.header-navigation").css("visibility", "visible");
            }
         });
      });
      /* on click outside of nav.header-navigation, close the menu */
      $(document).on("click", function (e) {
         if (
            !$(e.target).closest("nav.header-navigation .wp-block-navigation__container, .wp-block-button.is-style-menu-btn ").length &&
            $(e.target) !== $("nav.header-navigation .wp-block-navigation__container")
         ) {
            $(".wp-block-button.is-style-menu-btn").removeClass("open");
            $("nav.header-navigation").removeClass("open");
            setTimeout(function () {
               $("nav.header-navigation").css("visibility", "hidden");
            }, 200);
         }
      });

      // Marquee infitie loop
      var $marquee = $(".marquee");
      $marquee.append($marquee.html()); // duplicera innehållet för sömlös loop

      var $window = $(window);
      var $marquee = $(".marquee");

      // Kontrollera scroll vid sidladdning
      toggleMarquee($window.scrollTop());

      // Lyssna på scroll-event
      $window.on("scroll", function () {
         var scrollTop = $window.scrollTop();
         toggleMarquee(scrollTop);
      });

      function toggleMarquee(scrollTop) {
         if (scrollTop > 0) {
            // Döljer smidigt vid scroll
            $marquee.stop(true).slideUp(100);
         } else {
            // Visar igen när man är längst upp
            $marquee.stop(true).slideDown(100);
         }
      }
   });
})(jQuery);
