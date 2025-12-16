(function ($) {
   const $container = $("#reseller-results");
   const $cityTemplate = $(".city-group-template");
   const $storeTemplate = $(".store-entry-template");
   const $search = $("#reseller-search");

   const cacheKey = "tiburon_reseller_cache_v1"; // update this if schema changes

   function renderResellers(data) {
      $container.fadeOut(150, () => {
         $container.empty();

         if (!data || Object.keys(data).length === 0) {
            $container.append("<p>Inga återförsäljare hittades.</p>");
            $container.fadeIn(200);
            return;
         }

         $.each(data, function (city, stores) {
            const $city = $cityTemplate.clone().removeClass("city-group-template").show();
            $city.find(".city-name").text(city);
            const $ul = $city.find(".store-list");

            $.each(stores, function (i, store) {
               const $entry = $storeTemplate.clone().removeClass("store-entry-template").show();

               $entry.find(".store-name").text(store.store_name);
               store.address
                  ? $entry.find(".store-address").text(store.address).show()
                  : $entry.find(".store-address").remove();
               store.phone
                  ? $entry
                       .find(".store-phone")
                       .html('<a href="tel:' + store.phone + '">' + store.phone + "</a>")
                       .show()
                  : $entry.find(".store-phone").remove();
               store.email
                  ? $entry
                       .find(".store-email")
                       .html('<a href="mailto:' + store.email + '">' + store.email + "</a>")
                       .show()
                  : $entry.find(".store-email").remove();
               store.website
                  ? $entry
                       .find(".store-website")
                       .html('<a id="' + store.store_name + '" class="store-website-link" href="' + store.website + '" target="_blank">Hemsida</a>')
                       .show()
                  : $entry.find(".store-website").remove();
               store.contact_person
                  ? $entry.find(".store-contact").text(store.contact_person).show()
                  : $entry.find(".store-contact").remove();

               $ul.append($entry);
            });

            $container.append($city);
         });

         $container.fadeIn(250);
      });
   }

   function fetchResellers(query = "") {
      const cached = JSON.parse(localStorage.getItem(cacheKey) || "{}");
      const cachedEntry = cached[query];

      $container.html(`
         <div class="reseller-loading">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
               <radialGradient id="a8" cx=".66" fx=".66" cy=".3125" fy=".3125" gradientTransform="scale(1.5)">
                  <stop offset="0" stop-color="#572F1E"></stop>
                  <stop offset=".3" stop-color="#572F1E" stop-opacity=".9"></stop>
                  <stop offset=".6" stop-color="#572F1E" stop-opacity=".6"></stop>
                  <stop offset=".8" stop-color="#572F1E" stop-opacity=".3"></stop>
                  <stop offset="1" stop-color="#572F1E" stop-opacity="0"></stop>
               </radialGradient>
               <circle transform-origin="center" fill="none" stroke="url(#a8)" stroke-width="20" stroke-linecap="round" stroke-dasharray="200 1000" stroke-dashoffset="0" cx="100" cy="100" r="70">
                  <animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="2" values="360;0" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform>
               </circle>
               <circle transform-origin="center" fill="none" opacity=".1" stroke="#572F1E" stroke-width="20" stroke-linecap="round" cx="100" cy="100" r="70"></circle>
            </svg>
         </div>`);

      $.ajax({
         url: "/wp-json/hapi/v1/resellers",
         method: "GET",
         data: { s: query },
         success: function (response) {
            const version = response.cache_version;
            const data = response.data || {};

            // Use cache if version matches
            if (cachedEntry && cachedEntry.version === version) {
               renderResellers(cachedEntry.data);
            } else {
               renderResellers(data);

               // Save new versioned cache
               cached[query] = {
                  version: version,
                  data: data,
               };
               localStorage.setItem(cacheKey, JSON.stringify(cached));
            }
         },
         error: function () {
            $container.html("<p>Något gick fel. Försök igen.</p>");
         },
      });
   }

   function debounce(func, wait) {
      let timeout;
      return function () {
         const context = this,
            args = arguments;
         clearTimeout(timeout);
         timeout = setTimeout(() => func.apply(context, args), wait);
      };
   }

   $(function () {
      const lastQuery = sessionStorage.getItem("reseller_last_query") || "";
      $search.val(lastQuery);
      fetchResellers(lastQuery);

      $search.on(
         "input",
         debounce(function () {
            const query = $(this).val();
            sessionStorage.setItem("reseller_last_query", query);
            fetchResellers(query);
         }, 300)
      );
   });
})(jQuery);