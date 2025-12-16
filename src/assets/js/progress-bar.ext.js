/* STATISTICAL GRAPHS: COUNTS UP & DOWN */
(function ($) {
   $(document).ready(function () {
      var $section = $(".section-progress-bar");
      var $counters = $section.find(".progress-bar-percent");
      var animationRunning = false;

      function easeOutCubic(x) {
         return 1 - Math.pow(1 - x, 3);
      }

      // Räkna upp från 0 till target
      function countUp($counter, target, duration) {
         var startTime = performance.now();

         function update(time) {
            var rawProgress = Math.min((time - startTime) / duration, 1);
            var progress = easeOutCubic(rawProgress);
            var value = Math.floor(progress * target);
            $counter.text("+" + value + "%");
            if (rawProgress < 1) {
               requestAnimationFrame(update);
            } else {
               $counter.text("+" + target + "%");
            }
         }

         requestAnimationFrame(update);
      }

      // Räkna ned från target till 0
      function countDown($counter, target, duration) {
         var startTime = performance.now();

         function update(time) {
            var rawProgress = Math.min((time - startTime) / duration, 1);
            var progress = easeOutCubic(rawProgress);
            var value = Math.floor(target * (1 - progress));
            $counter.text("+" + value + "%");
            if (rawProgress < 1) {
               requestAnimationFrame(update);
            } else {
               $counter.text("+0%");
            }
         }

         requestAnimationFrame(update);
      }

      function animateCountersUp() {
         $counters.each(function () {
            var $counter = $(this);
            var target = parseFloat($counter.attr("data-target"));
            if (!target) {
               var match = $counter
                  .text()
                  .trim()
                  .match(/([\d.]+)/);
               if (!match) return;
               target = parseFloat(match[1]);
               $counter.attr("data-target", target);
            }
            countUp($counter, target, 1600);
         });
      }

      function animateCountersDown() {
         $counters.each(function () {
            var $counter = $(this);
            var target = parseFloat($counter.attr("data-target")) || 0;
            countDown($counter, target, 1000);
         });
      }

      function startAnimation() {
         if (animationRunning) return;
         animationRunning = true;
         $section.removeClass("reset-bars").addClass("animate-bars");
         animateCountersUp();
      }

      function resetAnimation() {
         if (!animationRunning) return;
         animationRunning = false;
         $section.removeClass("animate-bars").addClass("reset-bars");
         animateCountersDown();
      }

      var observer = new IntersectionObserver(
         function (entries) {
            entries.forEach(function (entry) {
               if (entry.isIntersecting) {
                  startAnimation();
               } else {
                  resetAnimation();
               }
            });
         },
         { threshold: 0.3 }
      );

      if ($section.length) {
         observer.observe($section[0]);
      }
   });
})(jQuery);
