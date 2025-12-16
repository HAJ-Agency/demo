(function ($, window, document) {
   // --- internals -------------------------------------------------------------
   function toggleVariationAccordions(variationId) {
      var $all = $(".wc-variation-accordion");
      if (!$all.length) return;
      $all.addClass("wc-hidden");
      if (variationId) {
         $all.filter('[data-variation="' + variationId + '"]').removeClass("wc-hidden");
      }
   }

   // local normalization (lowercase values to mirror Woo JSON)
   function normVal(v) {
      return String(v == null ? "" : v)
         .trim()
         .toLowerCase();
   }
   function normKey(k) {
      return String(k || "").trim();
   }
   function normalizeAttrs(obj) {
      var out = {};
      Object.keys(obj || {}).forEach(function (k) {
         out[normKey(k)] = normVal(obj[k]);
      });
      return out;
   }
   function findMatch(variations, selection) {
      var sel = normalizeAttrs(selection);
      for (var i = 0; i < (variations || []).length; i++) {
         var v = variations[i];
         if (v.is_in_stock === false) continue;
         if (v.variation_is_active === false) continue;
         var attrs = normalizeAttrs(v.attributes || {});
         var allOk = true;
         for (var key in attrs) {
            var want = sel[key] || "";
            if (!want || normVal(attrs[key]) !== want) {
               allOk = false;
               break;
            }
         }
         if (allOk) return v;
      }
      return null;
   }

   // --- public API ------------------------------------------------------------
   var API = {
      toggle: function (variationId) {
         toggleVariationAccordions(variationId || null);
      },
      updateBySelection: function (payload) {
         if (!payload || payload.productType !== "variable") {
            toggleVariationAccordions(null);
            return;
         }
         var variations = payload.variations || [];
         var selection = payload.selection || {};
         var allSelected = Object.values(selection).every(Boolean);
         if (!allSelected) {
            toggleVariationAccordions(null);
            return;
         }
         var match = findMatch(variations, selection);
         toggleVariationAccordions(match ? match.variation_id : null);
      },
   };

   // Expose + signal ready
   window.WCAccordions = API;
   document.dispatchEvent(new CustomEvent("wc:accordions-ready"));

   // --- native Woo hooks (keep working with classic variation forms) ----------
   $(document).on("found_variation", "form.variations_form", function (event, variation) {
      API.toggle(variation && variation.variation_id ? variation.variation_id : null);
   });
   $(document).on("reset_data", "form.variations_form", function () {
      API.toggle(null);
   });

   // --- custom event bus from other scripts -----------------------------------
   document.addEventListener("wc:variation-change", function (e) {
      var id = e && e.detail ? e.detail.variationId : null;
      API.toggle(id || null);
   });

   // Accordion open/close with ARIA + inert
   function setOpen($btn, open) {
      var controls = $btn.attr("aria-controls");
      var $panel = controls ? $("#" + controls) : $();
      $btn.attr("aria-expanded", open ? "true" : "false");
      if ($panel.length) {
         $panel.attr("aria-hidden", open ? "false" : "true");
         if (open) {
            $panel.removeAttr("inert");
         } else {
            $panel.attr("inert", "");
         }
      }
   }

   function toggle($btn) {
      var isOpen = $btn.attr("aria-expanded") === "true";
      setOpen($btn, !isOpen);
   }

   // Click to toggle
   $(document).on("click", ".wc-accordion .wc-acc__trigger", function (e) {
      e.preventDefault();
      toggle($(this));
   });

   // Optional keyboard nav between triggers (↑/↓ or ←/→)
   $(document).on("keydown", ".wc-accordion .wc-acc__trigger", function (e) {
      var $triggers = $(this).closest(".wc-accordion").find(".wc-acc__trigger");
      var idx = $triggers.index(this);
      if (e.key === "ArrowDown" || e.key === "ArrowRight") {
         e.preventDefault();
         $triggers.eq((idx + 1) % $triggers.length).focus();
      } else if (e.key === "ArrowUp" || e.key === "ArrowLeft") {
         e.preventDefault();
         $triggers.eq((idx - 1 + $triggers.length) % $triggers.length).focus();
      }
   });
})(jQuery, window, document);
