/* global jQuery, wc_cart_fragments_params, HAPI_CART */
(function ($) {
   "use strict";

   const $roots = $(".hapi-ajax-atc");
   if (!$roots.length) return;

   const normKey = (k) => String(k || "").trim();
   const normVal = (v) =>
      String(v == null ? "" : v)
         .trim()
         .toLowerCase();

   const normalizeAttrs = (obj) => {
      const out = {};
      Object.keys(obj || {}).forEach((k) => (out[normKey(k)] = normVal(obj[k])));
      return out;
   };

   const comboPossible = (variations, partialSelection) => {
      const sel = normalizeAttrs(partialSelection);
      return (variations || []).some((v) => {
         if (v.is_in_stock === false) return false;
         if (v.variation_is_active === false) return false;
         const attrs = normalizeAttrs(v.attributes || {});
         for (const [k, val] of Object.entries(sel)) {
            if (!val) continue;
            if (normVal(attrs[k]) !== val) return false;
         }
         return true;
      });
   };

   const findMatch = (variations, selection) => {
      const sel = normalizeAttrs(selection);
      return (
         (variations || []).find((v) => {
            if (v.is_in_stock === false) return false;
            if (v.variation_is_active === false) return false;
            const attrs = normalizeAttrs(v.attributes || {});
            for (const key of Object.keys(attrs)) {
               const want = sel[key] || "";
               if (!want || normVal(attrs[key]) !== want) return false;
            }
            return true;
         }) || null
      );
   };

   const clampQty = ($input) => {
      const n = Math.max(1, parseInt($input.val() || "1", 10));
      $input.val(String(n));
      return n;
   };

   const readJSON = ($el, name, fallback) => {
      const raw = $el.attr("data-" + name);
      if (!raw) return fallback;
      const decoded = $("<textarea/>").html(raw).text();
      try {
         return JSON.parse(decoded);
      } catch (e) {
         return fallback;
      }
   };
   const readHTML = ($el, name, fallback) => {
      const raw = $el.attr("data-" + name);
      return raw ? $("<textarea/>").html(raw).text() : fallback;
   };
   const toVarKey = (name) => {
      const n = String(name || "").trim();
      return n.startsWith("attribute_") ? n : `attribute_${n}`;
   };
   const hasAnySelection = (sel) => Object.values(sel || {}).some(Boolean);

   // -------- gallery helpers (unchanged) ----------
   const findGallery = ($root) => {
      const $scoped = $root
         .closest(".product, main, .wp-site-blocks, body")
         .find(".woocommerce-product-gallery")
         .first();
      return $scoped.length ? $scoped : $(".woocommerce-product-gallery").first();
   };
   const setAttr = ($el, attr, val) => {
      if (val != null && String(val).length) $el.attr(attr, val);
   };
   const resetAttr = ($el, attr) => {
      const original = $el.data("original_" + attr);
      if (typeof original !== "undefined") $el.attr(attr, original);
   };
   const swapGallery = ($root, variation) => {
      const $gallery = findGallery($root);
      if (!$gallery.length) return;
      const $img = $gallery.find(".woocommerce-product-gallery__image img").first();
      const $link = $gallery.find(".woocommerce-product-gallery__image a").first();
      const $frame = $gallery.find(".woocommerce-product-gallery__image").first();

      if (variation && variation.image && variation.image.src) {
         const img = variation.image;
         $gallery.trigger("woocommerce_gallery_reset_slide_position");
         setAttr($img, "src", img.src);
         setAttr($img, "width", img.src_w);
         setAttr($img, "height", img.src_h);
         setAttr($img, "srcset", img.srcset);
         setAttr($img, "sizes", img.sizes);
         setAttr($img, "data-src", img.full_src);
         setAttr($img, "data-large_image", img.full_src);
         setAttr($img, "data-large_image_width", img.full_src_w);
         setAttr($img, "data-large_image_height", img.full_src_h);
         setAttr($link, "href", img.full_src);
         setAttr($frame, "data-thumb", img.src);
         $gallery.trigger("woocommerce_gallery_init_zoom");
         return;
      }
      ["src", "width", "height", "srcset", "sizes"].forEach((a) => resetAttr($img, a));
      const large = $img.attr("data-large_image");
      if (large) setAttr($link, "href", large);
      $gallery.trigger("woocommerce_gallery_reset_slide_position");
   };
   // ----------------------------------------------

   $roots.each(function () {
      const $root = $(this);

      const productId = parseInt($root.data("productId"), 10);
      const productType = String($root.data("productType") || "");
      const i18n = readJSON($root, "i18n", {});
      const parentPriceHTML = readHTML($root, "parent-price-html", "");
      const parentDescHTML = readHTML($root, "parent-desc", "");
      const varDescs = readJSON($root, "variation-descriptions", {});

      const $qtyInput = $root.find(".hapi-qty__input");
      const $btnMinus = $root.find(".hapi-qty__btn--minus");
      const $btnPlus = $root.find(".hapi-qty__btn--plus");
      const $atcBtn = $root.find(".hapi-atc-button");
      const $atcNotice = $root.find(".hapi-atc-notice");
      const $statusBox = $root.find(".hapi-status");
      const $priceNode = $root.find(".hapi-price");

      const $desc = $root.find(".hapi-desc");
      const $descCont = $root.find(".hapi-desc__content");
      const $descTgl = $root.find(".hapi-desc__toggle");

      let variations = [];
      let attrOptions = {};
      let selection = {};
      let defaults = {};
      let didAutoSelect = false;

      if (productType === "variable") {
         variations = readJSON($root, "available-variations", []);
         attrOptions = readJSON($root, "variation-attributes", {});
         defaults = readJSON($root, "default-attributes", {});
         Object.keys(attrOptions).forEach((k) => (selection[toVarKey(k)] = ""));
      }

      // qty controls
      $btnMinus.on("click", function () {
         $qtyInput
            .val(String(Math.max(1, parseInt($qtyInput.val() || "1", 10) - 1)))
            .trigger("change");
      });
      $btnPlus.on("click", function () {
         $qtyInput
            .val(String(Math.max(1, parseInt($qtyInput.val() || "1", 10) + 1)))
            .trigger("change");
      });
      $qtyInput.on("input change", function () {
         clampQty($qtyInput);
      });

      // attributes
      const $groups = $root.find(".hapi-attr");
      const toVar = (name) => toVarKey(name);
      const urlSelection = () => {
         const out = {};
         const usp = new URLSearchParams(window.location.search);
         usp.forEach((val, key) => {
            if (/^attribute_/.test(key)) out[toVar(key)] = String(val || "").trim();
         });
         return out;
      };

      const clickOption = (attrKey, term) => {
         const groupKey = String(attrKey).replace(/^attribute_/, "");
         const $group = $groups.filter('[data-attribute-name="' + groupKey + '"]');
         const $btn = $group.find('.hapi-attr__option[data-term="' + term + '"]');
         if ($btn.length) {
            $btn.trigger("click");
            return true;
         }
         return false;
      };

      const clickOptionLoose = (attrKey, termRaw) => {
         if (!termRaw) return false;
         const groupKey = String(attrKey).replace(/^attribute_/, "");
         const $group = $groups.filter('[data-attribute-name="' + groupKey + '"]');
         if (!$group.length) return false;

         let $btn = $group.find('.hapi-attr__option[data-term="' + termRaw + '"]');
         if (!$btn.length) {
            const want = termRaw.toLowerCase();
            $btn = $group
               .find(".hapi-attr__option")
               .filter(function () {
                  const got = String($(this).data("term") || "").toLowerCase();
                  return got === want;
               })
               .first();
         }
         if ($btn.length) {
            $group.find(".hapi-attr__option").attr("aria-pressed", "false");
            $btn.attr("aria-pressed", "true").trigger("click");
            return true;
         }
         return false;
      };

      const updateAvailability = () => {
         if (productType !== "variable") return;
         const ok = Array.isArray(variations) && variations.length > 0;
         if (!ok || !hasAnySelection(selection)) {
            $groups
               .find(".hapi-attr__option")
               .prop("disabled", false)
               .removeClass("is-unavailable");
            return;
         }
         $groups.each(function () {
            const $g = $(this);
            const attrName = $g.data("attributeName");
            const attrKey = toVar(attrName);
            const $opts = $g.find(".hapi-attr__option");
            const baseSel = { ...selection };
            delete baseSel[attrKey];
            $opts.each(function () {
               const $opt = $(this);
               const term = String($opt.data("term") || "");
               const candidate = { ...baseSel, [attrKey]: term };
               const possible = comboPossible(variations, candidate);
               $opt.prop("disabled", !possible).toggleClass("is-unavailable", !possible);
            });
         });
      };

      const reflectATCState = () => {
         if (productType !== "variable") return;
         const allSelected = Object.values(selection).every(Boolean);
         const match = allSelected ? findMatch(variations, selection) : null;
         const shouldDisable = !match;
         $atcBtn
            .attr("data-disabled", shouldDisable ? "true" : "false")
            .prop("disabled", shouldDisable);
         if (shouldDisable && !allSelected) $statusBox.text(i18n.selectAll || "Välj alternativ");
         else if (shouldDisable && allSelected)
            $statusBox.text(i18n.unavailable || "Ej tillgänglig kombination");
         else $statusBox.text("");
      };

      // price/desc
      const setPriceHTML = (html) => {
         $priceNode.html(html || "");
      };
      const applyClampUI = () => {
         const expanded = $desc.hasClass("is-expanded");
         $descTgl.text(expanded ? i18n.readLess || "Read less" : i18n.readMore || "Read more");
         $descTgl.attr("aria-expanded", expanded ? "true" : "false");
         if (!expanded)
            requestAnimationFrame(() => {
               const needsToggle =
                  $descCont[0] && $descCont[0].scrollHeight - 1 > $descCont[0].clientHeight;
               $descTgl.prop("hidden", !needsToggle);
            });
         else $descTgl.prop("hidden", false);
      };
      const setDescriptionHTML = (html) => {
         $descCont.html(html || "");
         applyClampUI();
      };
      const updatePriceAndDesc = () => {
         if (productType !== "variable") {
            setPriceHTML(parentPriceHTML);
            setDescriptionHTML(parentDescHTML);
            return;
         }
         const allSelected = Object.values(selection).every(Boolean);
         const match = allSelected ? findMatch(variations, selection) : null;
         setPriceHTML(match ? match.price_html || parentPriceHTML : parentPriceHTML);
         const vid = match ? String(match.variation_id) : null;
         setDescriptionHTML(vid && varDescs[vid] ? varDescs[vid] : parentDescHTML);
      };
      $descTgl.on("click", function () {
         $desc.toggleClass("is-expanded");
         applyClampUI();
      });

      const notifyAccordions = () => {
         if (productType !== "variable") return;
         const allSelected = Object.values(selection).every(Boolean);
         const match = allSelected ? findMatch(variations, selection) : null;
         if (window.WCAccordions?.updateBySelection)
            window.WCAccordions.updateBySelection({ productType, variations, selection });
         else
            document.dispatchEvent(
               new CustomEvent("wc:variation-change", {
                  detail: { variationId: match ? match.variation_id : null },
               })
            );
      };

      // click wiring
      $groups.each(function () {
         const $g = $(this);
         const attrName = $g.data("attributeName");
         const attrKey = toVar(attrName);
         const $opts = $g.find(".hapi-attr__option");
         $opts.on("click", function () {
            const $btn = $(this);
            $opts.attr("aria-pressed", "false");
            $btn.attr("aria-pressed", "true");
            selection[attrKey] = String($btn.data("term") || "");
            updateAvailability();
            reflectATCState();
            updatePriceAndDesc();
            notifyAccordions();
            const allSelectedNow = Object.values(selection).every(Boolean);
            const matchNow = allSelectedNow ? findMatch(variations, selection) : null;
            swapGallery($root, matchNow);
         });
      });

      // auto-select
      const selectByVariation = (v) => {
         if (!v) return false;
         const attrs = v.attributes || {};
         for (let k of Object.keys(attrs)) {
            const term = attrs[k];
            if (!term) return false;
            if (!clickOption(k, term)) return false;
         }
         return true;
      };
      const ensureInitialSelection = () => {
         if (productType !== "variable" || didAutoSelect) return;
         didAutoSelect = true;

         // defaults first
         if (defaults && Object.keys(defaults).length) {
            Object.keys(defaults).forEach((k) => {
               const term = defaults[k];
               if (term) clickOption(toVar(k), term);
            });
         }
         // first valid, if still not all selected
         if (!Object.values(selection).every(Boolean)) {
            const firstValid = (variations || []).find(
               (v) => v && v.is_in_stock !== false && v.variation_is_active !== false
            );
            if (firstValid) selectByVariation(firstValid);
         }
         updateAvailability();
         reflectATCState();
         updatePriceAndDesc();
         notifyAccordions();

         const allSelectedNow = Object.values(selection).every(Boolean);
         const matchNow = allSelectedNow ? findMatch(variations, selection) : null;
         swapGallery($root, matchNow);
      };

      // initial render
      if (productType === "variable") {
         setPriceHTML(parentPriceHTML);
         setDescriptionHTML(parentDescHTML);
         ensureInitialSelection();
         const pre = urlSelection();
         Object.keys(pre).forEach((attrKey) => {
            const term = pre[attrKey];
            if (term) {
               clickOptionLoose(attrKey, term);
               selection[toVar(attrKey)] = term;
            }
         });
         const allSelectedInit = Object.values(selection).every(Boolean);
         swapGallery($root, allSelectedInit ? findMatch(variations, selection) : null);
      } else {
         setPriceHTML(parentPriceHTML);
         setDescriptionHTML(parentDescHTML);
      }

      // ATC
      const setBusy = (busy) => {
         $atcBtn.toggleClass("is-busy", !!busy);
         const disabled = !!busy || $atcBtn.attr("data-disabled") === "true";
         $atcBtn.prop("disabled", disabled);
      };

      $atcBtn.on("click", function () {
         if ($atcBtn.attr("data-disabled") === "true") return;

         const qty = clampQty($qtyInput);
         const payload = { product_id: productId, quantity: qty };

         if (productType === "variable") {
            const match = findMatch(variations, selection);
            if (!match) {
               reflectATCState();
               return;
            }
            payload.variation_id = match.variation_id;
            payload.variation = selection;
         }

         setBusy(true);
         $statusBox.text("");
         $atcNotice.text("");

         $.ajax({
            url: window.HAPI_ATC?.restPath || "/wp-json/hapi/v1/products/add_to_cart",
            method: "POST",
            data: JSON.stringify(payload),
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            xhrFields: { withCredentials: true },
            beforeSend: function (xhr) {
               if (window.HAPI_ATC?.nonce)
                  xhr.setRequestHeader("X-WP-Nonce", window.HAPI_ATC.nonce);
            },
         })
            .done(function (data) {
               console.log("Add to cart response:", data);
               if (!data || data.success !== true) throw new Error((data && data.message) || "Something went wrong");
               $atcBtn.addClass("was-added");
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
                  console.log("Uppdaterar cart fragments...");
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
               setBusy(false);
            });
      });

      // variations_form signal (optional, mirrors Woo)
      const $form = $("form.variations_form").first();
      const allSelectedNow = Object.values(selection).every(Boolean);
      const matchNow = allSelectedNow ? findMatch(variations, selection) : null;
      if ($form.length) {
         if (matchNow) $form.trigger("found_variation", [matchNow]);
         else $form.trigger("reset_data");
      }

      document.addEventListener("wc:accordions-ready", function once() {
         document.removeEventListener("wc:accordions-ready", once);
         updatePriceAndDesc();
         notifyAccordions();
         const allNow = Object.values(selection).every(Boolean);
         swapGallery($root, allNow ? findMatch(variations, selection) : null);
      });
   });
})(jQuery);
