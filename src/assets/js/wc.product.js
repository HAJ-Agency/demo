(function ($) {
   $(function () {
      if (!$(".ajax-products").length) return;
      const isSwiper = $(".ajax-products").hasClass("swiper-products");
      const productsContainer = $(".ajax-products");
      // --- Tab switching logic (filter-taxonomy) ---
      $(document).on("click", ".filter-taxonomy", function (e) {
         e.preventDefault();

         const $clicked = $(this);
         const panelId = $clicked.attr("aria-controls");

         // Deactivate all tabs
         $(".filter-taxonomy").removeClass("is-active").attr("aria-selected", "false");
         // Activate clicked tab
         $clicked.addClass("is-active").attr("aria-selected", "true").attr("tabindex", "0").focus();

         // Hide all panels
         $(".filter-content[role='tabpanel']").attr("hidden", true);

         // Show corresponding panel
         $("#" + panelId).removeAttr("hidden");

         // Reset active filter options if anything is filtered
         const filtersAreActive = $(".filter-option.selected").length > 0;
         if (!filtersAreActive) return;
         $(".filter-option").removeClass("selected").attr("aria-pressed", "false");
         fetchProducts(null, null);
      });

      // --- Filter option selection logic ---
      $(document).on("click", ".filter-option", function (e) {
         e.preventDefault();

         const $btn = $(this);
         const taxonomy = $btn.data("taxonomy");

         // Single-select per taxonomy:
         $btn.siblings(".filter-option").removeClass("selected").attr("aria-pressed", "false");

         const isSelected = $btn.hasClass("selected");
         if (isSelected) {
            // Already selected → deselect
            $btn.removeClass("selected").attr("aria-pressed", "false");
         } else {
            // Select this one
            $btn.addClass("selected").attr("aria-pressed", "true");
         }

         // Emit custom event (optional hook for your filtering logic)
         $btn.trigger("filter:changed", {
            taxonomy: taxonomy,
            term: $btn.data("term"),
            pressed: !isSelected,
         });

         console.log("Filter changed:", {
            taxonomy: taxonomy,
            term: $btn.data("term"),
            pressed: !isSelected,
         });

         fetchProducts(taxonomy, $btn);
      });

      const setBusy = ($atcBtn, busy) => {
         $atcBtn.toggleClass("is-busy", !!busy);
         const disabled = !!busy || $atcBtn.attr("data-disabled") === "true";
         $atcBtn.prop("disabled", disabled);
      };

      $(document).on("click", ".product-card__add-to-cart", function (e) {
         const $atcBtn = $(this);
         const $statusBox = $atcBtn.siblings(".hapi-status");
         const $atcNotice = $atcBtn.find(".hapi-atc-notice");
         e.preventDefault();
         if ($atcBtn.attr("data-disabled") === "true") return;
         const productId = $atcBtn.attr("data-product-id");

         console.log("Lägger till i varukorg, produkt-ID:", productId);

         const qty = 1;
         const payload = { product_id: productId, quantity: qty };

         setBusy($atcBtn, true);
         $statusBox.text("");
         $atcNotice.text("");

         $.ajax({
            url: wc_add_to_cart_params.wc_ajax_url.replace("%%endpoint%%", "add_to_cart"),
            method: "POST",
            data: {
               product_id: productId,
               quantity: 1,
            },
            xhrFields: { withCredentials: true },
         })
            .done(function (data) {
               console.log("Add to cart response:", data);
               $atcBtn.addClass("was-added");
               /* WooCommerce event */
               jQuery(document.body).trigger("added_to_cart", [{}, data.cart_hash, $atcBtn]);

               /* Trigger GTM event */
               document.dispatchEvent(
                  new CustomEvent("hapi:add_to_cart", {
                     detail: {
                        payload: {
                           productId: productId,
                           quantity: qty,
                           currency: "SEK",
                           productName: $atcBtn.attr("data-product-name"),
                           productSku: $atcBtn.attr("data-product-sku"),
                           parentPrice: $atcBtn.attr("data-product-price"),
                           brand: $atcBtn.attr("data-product-brand"),
                           categories: window.hapiProductCategories ? window.hapiProductCategories[productId] : [],
                        },
                     },
                  })
               );

               setTimeout(function () {
                  jQuery(document.body).trigger("wc_fragment_refresh");
                  document.body.dispatchEvent(
                     new CustomEvent("wc-blocks_added_to_cart", {
                        bubbles: true,
                        detail: { preserveCartData: false },
                     })
                  );
               }, 150);
               setTimeout(() => {
                  try {
                     if (window.wc && window.wc.blocksCheckout && typeof window.wc.blocksCheckout.showMiniCartDrawer === "function") {
                        window.wc.blocksCheckout.showMiniCartDrawer();
                     } else {
                        jQuery(".wc-block-mini-cart__button, .wc-block-mini-cart__toggle").trigger("click");
                     }
                  } catch (e) {
                     console.warn("Kunde inte öppna mini-cart:", e);
                  }
               }, 300);

               setTimeout(function () {
                  $atcBtn.removeClass("was-added");
                  $atcNotice.text("");
               }, 1600);
            })
            .fail(function (jqXHR) {
               let msg = "Kunde inte lägga till i varukorg";
               try {
                  const json = jqXHR.responseJSON;
                  if (json?.message) msg = json.message;
               } catch (e) {}
               $statusBox.text(msg);
            })
            .always(function () {
               setBusy($atcBtn, false);
            });
      });

      // $(document).on("click", ".product-card__add-to-cart", function (e) {
      //     e.preventDefault();
      //     const productId = $(this).attr("data-product-id");
      //     const quantity = 1;
      //     const data = {
      //         product_id: productId,
      //         quantity: quantity,
      //     };
      //     console.log(data);
      //     const productCard = $(this).parents(".product-card");
      //     const productCardLink = productCard.find(".product-card__link");
      //     productCardLink.addClass("loading");
      //     const $notice = productCard.find(".product-card__add-to-cart-notice");
      //     $.ajax({
      //         url: "/wp-json/hapi/v1/products/add_to_cart",
      //         type: "POST",
      //         data: data,
      //         success: function (response) {
      //             document.body.dispatchEvent(
      //                 new CustomEvent("wc-blocks_added_to_cart", {
      //                     // leave as false to force Blocks to refetch cart data
      //                     detail: { preserveCartData: false },
      //                 })
      //             );

      //             if (!data || data.success !== true) throw new Error((data && data.message) || "Something went wrong");
      //             $(this).addClass("was-added");
      //             jQuery(document.body).trigger("added_to_cart", [{}, data.cart_hash, $(this)]);
      //             refreshFragments();

      //             setTimeout(() => {
      //                try {
      //                   if (window.wc && window.wc.blocksCheckout && typeof window.wc.blocksCheckout.showMiniCartDrawer === "function") {
      //                      window.wc.blocksCheckout.showMiniCartDrawer();
      //                   } else {
      //                      jQuery(".wc-block-mini-cart__button, .wc-block-mini-cart__toggle").trigger("click");
      //                   }
      //                } catch (e) {
      //                   console.warn("Kunde inte öppna mini-cart:", e);
      //                }
      //             }, 300);
      //         },
      //         error: function (jqXHR, textStatus, errorThrown) {
      //             console.log("AJAX ERROR");
      //             console.log(jqXHR);
      //             console.log(textStatus);
      //             console.log(errorThrown);
      //             productCardLink.removeClass("loading");
      //         },
      //     });
      // });

      function fetchProducts(taxonomy, $btn) {
         // Fetch products via AJAX via custom WP REST endpoint hapi/v1/ajax/get-product-data/
         const data = {
            posts_per_page: "-1",
         };
         if (taxonomy === "product_cat") {
            data["category_slug"] = $btn.data("term");
         }
         if (taxonomy === "foci") {
            data["focus_slug"] = $btn.data("term");
         }
         if (taxonomy === "needs") {
            data["need_slug"] = $btn.data("term");
         }

         $.ajax({
            url: "/wp-json/hapi/v1/ajax/get-product-data/",
            method: "POST",
            data: data,
            beforeSend: function () {
               productsContainer.html("<p>Loading...</p>");
            },
            success: function (response) {
               console.log(response.productData);
               productsContainer.empty();
               if (response.productData && response.productData.length > 0) {
                  renderProducts(response.productData);
                  // Reinitialize swiper
                  if (isSwiper) {
                     if (productsContainer[0].swiper) {
                        productsContainer[0].swiper.destroy(true, true);
                     }
                     const swiper = new Swiper(".swiper-products", {
                        direction: "horizontal",
                        slidesPerView:
                           $(".filtered-products-section").data("posts-per-view-mobile") || 1,
                        spaceBetween: 10,
                        scrollbar: {
                           el: ".swiper-scrollbar",
                           draggable: true,
                        },
                        breakpoints: {
                           720: {
                              slidesPerView:
                                 $(".filtered-products-section").data("posts-per-view-desktop") ||
                                 2,
                           },
                        },
                     });
                  }
               } else {
                  productsContainer.html("<p>No products found.</p>");
               }
            },
            error: function (err) {
               console.error("Error fetching products:", err);
               productsContainer.html("<p>Error loading products.</p>");
            },
         });
      }

      function renderProducts(products) {
         const productTemplate = $("[data-product-template]");
         const swiperWrapper = $('<div class="swiper-wrapper"></div>');
         console.log("Is swiper:", isSwiper);
         for (let i = 0; i < products.length; i++) {
            let product = productTemplate.clone();
            product.removeAttr("data-product-template");
            product.find(".product-card__title").html(products[i].title);
            product.find(".product-card__excerpt").html(products[i].excerpt);
            product.find(".product-card__image-inner").html(products[i].image);
            product.find(".product-card__price").html(products[i].price);

            product.find(".product-card__link").attr("href", products[i].link);
            product.find(".product-card__link").attr("aria-label", products[i].title);

            if (products[i].add_to_cart_button) {
               product.find(".product-card__add-to-cart").attr("data-product-id", products[i].id);
            } else {
               product.find(".product-card__add-to-cart").remove();
            }
            if (isSwiper) {
               // create a <div class="slide swiper-slide">
               const slide = $("<div></div>").addClass("slide swiper-slide");
               slide.append(product);
               swiperWrapper.append(slide);
            } else {
               productsContainer.append(product);
            }
         }
         if (isSwiper) {
            productsContainer.append(swiperWrapper);
            productsContainer.append('<div class="swiper-scrollbar"></div>');
         }
      }
   });
})(jQuery);
