(function ($) {
   $(function () {
      var $section = $(".filtered-products-section");
      if ($section.length === 0) return;

      var isGrid = String($section.data("is-grid")) === "1";
      if (!isGrid) {
         const swiper = new Swiper(".swiper-products", {
            direction: "horizontal",
            slidesPerView: $section.data("posts-per-view-mobile") || 1,
            spaceBetween: 10,
            navigation: {
               nextEl: ".swiper-button-next",
               prevEl: ".swiper-button-prev",
            },
            scrollbar: { el: ".swiper-scrollbar", draggable: true },
            breakpoints: {
               720: {
                  slidesPerView: $section.data("posts-per-view-desktop") || 2,
               },
            },
         });
      }

      // Save .filter-footer height to CSS variable --filter-footer-height
      function updateHeaderHeight() {
         var $filterFooter = $(".filter-footer");
         if ($filterFooter.length) {
            var filterFooterHeight = $filterFooter.outerHeight();
            document.documentElement.style.setProperty("--filter-footer-height", filterFooterHeight + "px");
         }
      }
      updateHeaderHeight();
      // Debounce resize events to avoid excessive calls
      var resizeTimeout;
      $(window).on("resize", function () {
         clearTimeout(resizeTimeout);
         resizeTimeout = setTimeout(updateHeaderHeight, 200);
      });

      // Off-canvas elements
      const $drawer = $section.find("#product-filter-drawer");
      const $backdrop = $section.find(".product-filter-backdrop");
      const $openBtn = $section.find(".product-filter-open");
      const $closeBtn = $section.find(".product-filter-close");

      if ($drawer.length) {
         let lastFocused = null;

         const focusableSelector = [
            "a[href]",
            "area[href]",
            "button:not([disabled])",
            'input:not([disabled]):not([type="hidden"])',
            "select:not([disabled])",
            "textarea:not([disabled])",
            '[tabindex]:not([tabindex="-1"])',
         ].join(",");

         function trapFocus(e) {
            if (!$drawer.hasClass("is-open")) return;
            if (e.key !== "Tab") return;

            const $focusables = $drawer.find(focusableSelector).filter(":visible");
            if ($focusables.length === 0) return;

            const first = $focusables[0];
            const last = $focusables[$focusables.length - 1];

            if (e.shiftKey && document.activeElement === first) {
               e.preventDefault();
               last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
               e.preventDefault();
               first.focus();
            }
         }

         function openDrawer() {
            if ($drawer.hasClass("is-open")) return;
            lastFocused = document.activeElement;
            $drawer.addClass("is-open").attr("aria-hidden", "false");
            $backdrop.addClass("is-visible").removeAttr("hidden");
            $("body").addClass("fw-no-scroll");

            // Focus first focusable in drawer, or the close button
            const $focusables = $drawer.find(focusableSelector).filter(":visible");
            ($focusables[0] || $closeBtn).focus();

            $(document).on("keydown.fwtrap", trapFocus);
         }

         function closeDrawer() {
            if (!$drawer.hasClass("is-open")) return;
            $drawer.removeClass("is-open").attr("aria-hidden", "true");
            $backdrop.removeClass("is-visible").attr("hidden", "hidden");
            $("body").removeClass("fw-no-scroll");

            $(document).off("keydown.fwtrap", trapFocus);

            // Return focus to opener
            if (lastFocused && typeof lastFocused.focus === "function") {
               lastFocused.focus();
            }
         }

         // Open/close actions
         $openBtn.on("click", function (e) {
            e.preventDefault();
            openDrawer();
         });
         $closeBtn.on("click", function (e) {
            e.preventDefault();
            closeDrawer();
         });
         $backdrop.on("click", closeDrawer);

         // ESC to close
         $(document).on("keydown.fwesc", function (e) {
            if (e.key === "Escape" && $drawer.hasClass("is-open")) {
               e.preventDefault();
               closeDrawer();
            }
         });

         // If resizing to desktop, make sure the drawer is closed & states reset
         const mq = window.matchMedia("(min-width: 721px)");
         mq.addEventListener ? mq.addEventListener("change", onMQChange) : mq.addListener(onMQChange); // Safari fallback

         function onMQChange(e) {
            if (e.matches) {
               closeDrawer();
            }
         }
      }

      if (isGrid) {
         $section.on("click", ".filter-option", function (e) {
            e.preventDefault();
            const webshopUrl = $section.data("webshop-url");
            const tax = $(this).data("rewrite"); // 'behov' | 'fokus' | 'sortiment'
            const term = $(this).data("term"); // any slug

            const $btn = $(this);

            const isSelected = $btn.hasClass("selected");
            if (isSelected) {
               // change url structure to webshop baseurl
               var baseurl = webshopUrl || window.location.origin;
               window.history.pushState({ path: baseurl }, "", baseurl);
            } else {
               // change url structure {baseurl}/{tax}/{term}
               var baseurl = window.location.origin;
               if (tax && term) {
                  baseurl += "/" + tax + "/" + term;
               }

               // Change URL without reloading the page
               window.history.pushState({ path: baseurl }, "", baseurl);
            }

            // Close drawer on mobile for better UX
            if ($drawer.hasClass("is-open")) {
               $closeBtn.trigger("click");
            }
         });
      }
   });
})(jQuery);
