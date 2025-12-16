(function ($) {
   /**
    * Functionality to open and close accordion items
    */
   $(".wp-block-accordion .wp-block-accordion__item .wp-block-accordion__item-title button").each(function () {
      $(this).on("click", function () {
         const $item = $(this).closest(".wp-block-accordion__item");
         const $accordion = $(this).closest(".wp-block-accordion");

         if ($item.hasClass("is-expanded")) {
            $item.removeClass("is-expanded");
         } else {
            $accordion.find(".wp-block-accordion__item.is-expanded").removeClass("is-expanded");
            $item.toggleClass("is-expanded");
         }
      });
   });
   /**
    * Data table initialization and functionality
    */
   let tables = {};
   $(function () {
      function waitForDataTable(callback) {
         if (typeof DataTable !== "undefined") {
            callback();
         } else {
            setTimeout(() => waitForDataTable(callback), 50);
         }
      }
      waitForDataTable(() => {
         $(".wp-block-accordion").each(function (index, element) {
            let blockId = $(this).attr("data-block-id");
            $(this)
               .find("table")
               .each(function (index, tableDom) {
                  let table = new DataTable(tableDom, {
                     ordering: false,
                     stateSave: false,
                     paging: false,
                     layout: {
                        topStart: null,
                        topEnd: null,
                        bottomStart: null,
                        bottomEnd: null,
                     },
                     language: {
                        paginate: {
                           previous: `<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.59056 0.410093C8.91599 0.73553 8.91599 1.26317 8.59056 1.5886L3.01315 7.16602H15.0013C15.4615 7.16602 15.8346 7.53911 15.8346 7.99935C15.8346 8.45959 15.4615 8.83268 15.0013 8.83268H3.01315L8.59056 14.4101C8.91599 14.7355 8.91599 15.2632 8.59056 15.5886C8.26512 15.914 7.73748 15.914 7.41205 15.5886L0.412046 8.5886C0.0866095 8.26317 0.0866095 7.73553 0.412046 7.41009L7.41205 0.410093C7.73748 0.0846564 8.26512 0.0846564 8.59056 0.410093Z" fill="#002A3A"/></svg>Tillbaka`,
                           next: `Nästa<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M7.41335 15.588C7.08791 15.2625 7.08791 14.7349 7.41335 14.4094L12.9908 8.83203L1.0026 8.83203C0.542366 8.83203 0.169271 8.45894 0.169271 7.9987C0.169271 7.53846 0.542366 7.16537 1.0026 7.16537L12.9908 7.16536L7.41335 1.58795C7.08791 1.26252 7.08791 0.73488 7.41335 0.409444C7.73878 0.0840061 8.26642 0.084006 8.59186 0.409444L15.5919 7.40944C15.9173 7.73488 15.9173 8.26252 15.5919 8.58795L8.59186 15.588C8.26642 15.9134 7.73879 15.9134 7.41335 15.588Z" fill="#002A3A"/></svg>`,
                        },
                        zeroRecords: "Inga artiklar hittades.",
                     },
                  });

                  let category = $(".wp-block-accordion").data("filter");
                  if (category) {
                     table.columns(1).search(category).draw();
                     // find .wp-element-button with attribute data-filter equal to category and add class is-active to it's parent and remove it from it's siblings
                     $(".filter-buttons [data-filter]").each(function () {
                        if ($(this).attr("data-filter") == category) {
                           $(this).addClass("is-active").siblings().removeClass("is-active");
                        }
                     });
                  }

                  tables[blockId + "-col-" + index] = table;
               });
         });
         // When the filter buttons are clicked.
         $(".accordion-filter-buttons [data-filter]").on("click", function (e) {
            e.preventDefault();
            let blockId = $(this).closest(".wp-block-accordion").attr("data-block-id");
            let value = $(this).attr("data-filter");
            $(this).addClass("is-active").siblings().removeClass("is-active");
            $(this).closest(".wp-block-accordion").find(".wp-block-accordion_container .wp-block-accordion__item.is-expanded").removeClass("is-expanded");
            // loop through all tables starting with blockId-col-
            if (!tables[blockId + "-col-1"]) {
               tables[blockId + "-col-0"].columns(1).search(value).draw();
            } else if (tables[blockId + "-col-1"]) {
               tables[blockId + "-col-0"].columns(1).search(value).draw();
               tables[blockId + "-col-1"].columns(1).search(value).draw();
               const rowsFirstCol = $(`#accordion-posts-${blockId}[data-column="0"] > tbody > tr`);
               rowsFirstCol.each((index, element) => {
                  const isLastVisible = index === Math.ceil(rowsFirstCol.length / 2) - 1;
                  $(element).toggle(index < rowsFirstCol.length / 2);
                  isLastVisible ? $(element).addClass("is-last-row") : $(element).removeClass("is-last-row");
                  $(element).toggle(index < rowsFirstCol.length / 2);
               });
               const rowsSecondCol = $(`#accordion-posts-${blockId}[data-column="1"] > tbody > tr`);
               rowsSecondCol.each((index, element) => {
                  const isLastVisible = index === rowsSecondCol.length - 1;
                  $(element).toggle(index >= rowsSecondCol.length / 2);
               });
            }
            const termLink = $(this).attr("data-filter");
            if (typeof termLink !== "undefined" && termLink !== "") {
               // Update the URL with the term link without history
               const url = new URL(window.location);
               url.searchParams.set("filter", termLink);
               window.history.replaceState(null, null, url.toString());
            }
         });
         const activeButton = $(".accordion-filter-buttons [data-filter].is-active");
         if (activeButton.length) {
            activeButton.trigger("click");
         }
      });
   });
})(jQuery);
