(function ($) {
   /**
    * Scroll animation to change from color black to green:
    * svg.circle should switch from fill:none; to fill:green;
    * svg.circle circle should switch from stroke:black; to stroke:green;
    * .line line should switch from stroke:black; to stroke:green;
    *
    * there are several blocks on the same page, so we need to loop through each of them
    * and check if they are in the viewport
    */

   // Initialize dynamic transition delays based on block order
   function initializeTimelineDelays() {
      $(".wp-block-timeline").each(function (timelineIndex) {
         var $timeline = $(this);

         // Find all blocks within this timeline (assuming they have class containing 'block-')
         var $blocks = $timeline.find('[class*="block-"]');

         // Set CSS custom property for each block based on its index
         $blocks.each(function (blockIndex) {
            $(this).css("--block-index", blockIndex);
         });
      });
   }

   $(window).on("scroll", function () {
      var scrollTop = $(this).scrollTop();
      var windowHeight = $(window).height();

      $(".wp-block-timeline").each(function () {
         var $block = $(this);
         if ($block.hasClass("in-view")) return;

         var offsetTop = $block.offset().top;
         var triggerPoint = scrollTop + windowHeight * 0.7;

         if (triggerPoint > offsetTop) {
            $block.find(".circle circle, .line line").addClass("in-view");
            $block.addClass("in-view");
         }
      });
   });

   // Initialize delays on page load
   $(document).ready(function () {
      initializeTimelineDelays();
   });

   $(window).trigger("scroll");
})(jQuery);
