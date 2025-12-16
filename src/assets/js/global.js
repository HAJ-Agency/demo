(function ($) {
   $(function () {
      // Convert inline min-height: Xvh to min-height: Xpx for iOS compatibility
      $('[style*="min-height"]').each(function () {
         var styleAttr = this.getAttribute("style");
         var headerHeight = $("header").outerHeight();
         // Replace all min-height: Xvh with min-height: Xpx (handles multiple cases)
         var newStyle = styleAttr.replace(/min-height\s*:\s*(\d+(\.\d+)?)vh/gi, function (match, num) {
            // Only subtract header height if it's 100vh
            if (parseFloat(num) === 100) {
               return "min-height:calc(" + window.innerHeight * 0.01 * num + "px - " + headerHeight + "px)";
            } else {
               return "min-height:" + window.innerHeight * 0.01 * num + "px";
            }
         });
         if (styleAttr !== newStyle) {
            this.setAttribute("style", newStyle);
         }
      });

      // Smooth scrolling when clicking anchor links, target's offset by 80px.
      $('a[href^="#"]').on("click", function (e) {
         var target = this.hash;
         var $target = $(target);
         if ($target.length) {
            e.preventDefault();
            $("html, body").animate(
               {
                  scrollTop: $target.offset().top - 80,
               },
               650
            );
         }
      });
   });
})(jQuery);
