/* global jQuery, wc_add_to_cart_params, HAPI_CART */
jQuery(function ($) {
   "use strict";

   const $drawer = window.HAPI_CART && HAPI_CART.getDrawer ? HAPI_CART.getDrawer() : $();
   const inCheckout = $("form.checkout").length > 0;

   // Drawer open/close (unchanged)
   if ($drawer.length) {
      const drawerEl = $drawer.get(0);
      const openBtn = document.querySelector(".cart-drawer__header-icon");
      const closeBtn = document.querySelector(".cart-drawer__close");
      const overlay = document.querySelector(".cart-overlay");

      const open = () => {
         drawerEl.classList.add("is-open");
         overlay && overlay.classList.add("is-visible");
         document.body.classList.add("no-scroll");
         if (window.HAPI_CART && HAPI_CART.refreshCartUI) HAPI_CART.refreshCartUI();
      };
      const close = () => {
         drawerEl.classList.remove("is-open");
         overlay && overlay.classList.remove("is-visible");
         document.body.classList.remove("no-scroll");
      };

      if (openBtn) openBtn.addEventListener("click", open);
      if (closeBtn) closeBtn.addEventListener("click", close);
      if (overlay) overlay.addEventListener("click", close);
   }

   // Debounced checkout refresh
   const scheduleCheckoutRefresh = (function () {
      let t;
      return function () {
         clearTimeout(t);
         t = setTimeout(function () {
            $("body").trigger("update_checkout");
         }, 150);
      };
   })();

   function isCheckoutContext($el) {
      if (!inCheckout) return false;
      const ctx = $el.closest("[data-context]").attr("data-context");
      if (ctx === "checkout") return true;
      if (ctx === "drawer") return false;
      return $el.closest("form.checkout").length > 0;
   }

   function setItemLoading($el, on) {
      const $wrap = $el.closest(".cart-drawer__item-wrap");
      if (!$wrap.length) return;
      $wrap.toggleClass("is-loading", !!on);
      $wrap.find(".cart-drawer__item-qty-btn, .cart-drawer__item-remove").prop("disabled", !!on);
      // a11y
      $wrap.attr("aria-busy", on ? "true" : "false");
   }

   // When Woo finishes replacing fragments, clear loading states
   $("body").on("updated_checkout", function () {
      $(".cart-drawer__item-wrap.is-loading")
         .removeClass("is-loading")
         .removeAttr("aria-busy")
         .find(".cart-drawer__item-qty-btn, .cart-drawer__item-remove")
         .prop("disabled", false);
   });

   // Block bad keys and enforce digits-only (min 1)
   $(document).on("keydown", ".cart-drawer__item-qty-input", function (e) {
      const allowedCtrl = [
         "Backspace",
         "Delete",
         "Tab",
         "Escape",
         "Enter",
         "ArrowLeft",
         "ArrowRight",
         "Home",
         "End",
      ];
      if (allowedCtrl.includes(e.key) || e.ctrlKey || e.metaKey) return;

      // Disallow scientific notation, signs, decimals
      if (["e", "E", "+", "-", "."].includes(e.key)) {
         e.preventDefault();
         return;
      }
      // Allow digits only
      if (!/^\d$/.test(e.key)) {
         e.preventDefault();
      }
   });

   // Prevent invalid paste (non-digits or resulting < 1)
   $(document).on("paste", ".cart-drawer__item-qty-input", function (e) {
      const text = (e.originalEvent.clipboardData || window.clipboardData).getData("text");
      if (!/^\d+$/.test(text) || parseInt(text, 10) < 1) {
         e.preventDefault();
      }
   });

   // Normalize & persist: clamp to >= 1
   $(document).on("input change blur", ".cart-drawer__item-qty-input", function () {
      const $inp = $(this);
      const cartKey = $inp.data("cart-key");
      if (!cartKey) return;

      let qty = parseInt($inp.val(), 10);
      if (isNaN(qty) || qty < 1) qty = 1;
      if ($inp.val() !== String(qty)) $inp.val(qty);

      if (isCheckoutContext($inp)) {
         const $form = $("form.checkout");
         const $hid = $form.find('input.cart-hidden-qty[data-cart-key="' + cartKey + '"]');
         if ($hid.length) {
            setItemLoading($inp, true);
            $hid.val(qty).trigger("change");
            scheduleCheckoutRefresh();
         }
         return;
      }

      // Drawer: use same endpoint as +/- with `set`
      setItemLoading($inp, true);
      $.ajax({
         url: wc_add_to_cart_params.ajax_url,
         type: "POST",
         dataType: "json",
         data: {
            action: "update_cart_item_quantity",
            cart_key: cartKey,
            update_action: "set",
            quantity: qty,
         },
      })
         .done(function (res) {
            if (!res || res.success !== true) {
               console.error("AJAX error:", res && res.data);
               return;
            }
            if (window.HAPI_CART && HAPI_CART.refreshAfterChange) HAPI_CART.refreshAfterChange();
         })
         .fail(function (xhr, status, err) {
            console.error("AJAX error:", status, err);
         })
         .always(function () {
            setItemLoading($inp, false);
         });
   });

   // Keep Enter = commit but *never* go below 1
   $(document).on("keydown", ".cart-drawer__item-qty-input", function (e) {
      if (e.key === "Enter") {
         e.preventDefault();
         $(this).trigger("blur");
      }
   });

   // QTY +/-
   $(document).on("click", ".cart-drawer__item-qty-btn", function (e) {
      e.preventDefault();
      const $btn = $(this);
      const cartKey = $btn.data("cart-key");
      const action = $btn.data("action");
      if (!cartKey || !action) return;

      if (isCheckoutContext($btn)) {
         const $form = $("form.checkout");
         const $hid = $form.find('input.cart-hidden-qty[data-cart-key="' + cartKey + '"]');
         if (!$hid.length) return;

         let qty = parseInt($hid.val(), 10) || 1;
         if (action === "increase") qty += 1;
         if (action === "decrease") qty = Math.max(1, qty - 1); // <-- never below 1

         setItemLoading($btn, true);
         $hid.val(qty).trigger("change");
         // optionally reflect in any inline text display you still have
         $btn.closest(".cart-drawer__item-wrap").find(".cart-drawer__item-qty").text(qty);

         scheduleCheckoutRefresh();
         return;
      }

      // Drawer (admin-ajax)
      // Short-circuit: if trying to decrease at 1, do nothing client-side
      if (action === "decrease") {
         const $input = $btn
            .closest(".cart-drawer__item-wrap")
            .find(".cart-drawer__item-qty-input");
         const current = parseInt($input.val(), 10) || 1;
         if (current <= 1) return; // <-- stop here
      }

      setItemLoading($btn, true);
      $.ajax({
         url: wc_add_to_cart_params.ajax_url,
         type: "POST",
         dataType: "json",
         data: { action: "update_cart_item_quantity", cart_key: cartKey, update_action: action },
      })
         .done(function (res) {
            if (!res || res.success !== true) {
               console.error("AJAX error:", res && res.data);
               return;
            }
            if (window.HAPI_CART && HAPI_CART.refreshAfterChange) HAPI_CART.refreshAfterChange();
         })
         .fail(function (xhr, status, err) {
            console.error("AJAX error:", status, err);
         })
         .always(function () {
            setItemLoading($btn, false);
         });
   });

   // QTY +/-
   // $(document).on("click", ".cart-drawer__item-qty-btn", function (e) {
   //    e.preventDefault();
   //    const $btn = $(this);
   //    const cartKey = $btn.data("cart-key");
   //    const action = $btn.data("action");
   //    if (!cartKey || !action) return;

   //    if (isCheckoutContext($btn)) {
   //       const $form = $("form.checkout");
   //       const $hid = $form.find('input.cart-hidden-qty[data-cart-key="' + cartKey + '"]');
   //       if (!$hid.length) return;

   //       let qty = parseInt($hid.val(), 10) || 0;
   //       qty = action === "increase" ? qty + 1 : Math.max(0, qty - 1);

   //       setItemLoading($btn, true);
   //       $hid.val(qty).trigger("change");
   //       $btn.closest(".cart-drawer__item-wrap").find(".cart-drawer__item-qty").text(qty);

   //       scheduleCheckoutRefresh();
   //       return;
   //    }

   //    // Drawer (admin-ajax)
   //    setItemLoading($btn, true);
   //    $.ajax({
   //       url: wc_add_to_cart_params.ajax_url,
   //       type: "POST",
   //       dataType: "json",
   //       data: { action: "update_cart_item_quantity", cart_key: cartKey, update_action: action },
   //    })
   //       .done(function (res) {
   //          if (!res || res.success !== true) {
   //             console.error("AJAX error:", res && res.data);
   //             return;
   //          }
   //          if (window.HAPI_CART && HAPI_CART.refreshAfterChange) HAPI_CART.refreshAfterChange();
   //       })
   //       .fail(function (xhr, status, err) {
   //          console.error("AJAX error:", status, err);
   //       })
   //       .always(function () {
   //          setItemLoading($btn, false);
   //       });
   // });

   // REMOVE
   $(document).on("click", ".cart-drawer__item-remove", function (e) {
      e.preventDefault();
      const $btn = $(this);
      const cartKey = $btn.data("cart-key");
      if (!cartKey) return;

      if (isCheckoutContext($btn)) {
         const $form = $("form.checkout");
         const $hid = $form.find('input.cart-hidden-qty[data-cart-key="' + cartKey + '"]');
         setItemLoading($btn, true);
         if ($hid.length) {
            $hid.val(0).trigger("change");
            scheduleCheckoutRefresh();
         }
         return;
      }

      // Drawer
      setItemLoading($btn, true);
      $.ajax({
         url: wc_add_to_cart_params.ajax_url,
         type: "POST",
         dataType: "json",
         data: { action: "remove_cart_item", cart_key: cartKey },
      })
         .done(function (res) {
            if (!res || res.success !== true) {
               console.error("AJAX error:", res && res.data);
               return;
            }
            if (window.HAPI_CART && HAPI_CART.refreshAfterChange) HAPI_CART.refreshAfterChange();
         })
         .fail(function (xhr, status, err) {
            console.error("AJAX error:", status, err);
         })
         .always(function () {
            setItemLoading($btn, false);
         });
   });

   // Close (fallback older drawer)
   $(document).on("click", ".cart-drawer__close", function (e) {
      e.preventDefault();
      $("body").removeClass("drawer-open");
      $(".wc-block-components-drawer__screen-overlay").remove();
   });

   const getEndpoints = () => {
      if (window.HAPI_CART && HAPI_CART.getEndpoints) {
         return HAPI_CART.getEndpoints();
      }
      return {
         coupon: (HAPI_CART_CFG && HAPI_CART_CFG.couponPath) || "",
         couponFrag: (HAPI_CART_CFG && HAPI_CART_CFG.couponFragPath) || "",
      };
   };

   function setBusy($scope, on) {
      $scope.toggleClass("is-applying", !!on);
      $scope.find(".cart-discount__apply, .cart-discount__remove").prop("disabled", !!on);
      $scope.find(".cart-discount__input").prop("readonly", !!on);
   }
   function setMsg($scope, txt) {
      $scope.find(".cart-discount__msg").text(txt || "");
   }

   function getContext($scope) {
      return (
         $scope.closest(".cart-discount").data("context") || (inCheckout ? "checkout" : "drawer")
      );
   }

   async function refreshCouponFragment(context) {
      const { couponFrag } = getEndpoints();
      if (!couponFrag) return;

      try {
         const res = await $.ajax({
            url: couponFrag,
            method: "GET",
            dataType: "json",
            data: { context },
            xhrFields: { withCredentials: true },
            beforeSend: (xhr) => {
               if (HAPI_CART_CFG && HAPI_CART_CFG.nonce)
                  xhr.setRequestHeader("X-WP-Nonce", HAPI_CART_CFG.nonce);
            },
         });

         if (res && res.success && typeof res.html === "string") {
            const targetId =
               context === "checkout" ? "#cart-discount--checkout" : "#cart-discount--drawer";
            // Replace the entire container (by parent)
            const $old = $(targetId);
            if ($old.length) {
               $old.replaceWith(res.html);
            } else {
               // If not found, append to a sensible location (optional)
            }
         }
      } catch (e) {}
   }

   async function callCouponAPI(action, code, $scope) {
      const { coupon } = getEndpoints();
      if (!coupon) return;

      setBusy($scope, true);
      setMsg($scope, "");

      try {
         const res = await $.ajax({
            url: coupon,
            method: "POST",
            dataType: "json",
            data: { action, code },
            xhrFields: { withCredentials: true },
            beforeSend: (xhr) => {
               if (HAPI_CART_CFG && HAPI_CART_CFG.nonce)
                  xhr.setRequestHeader("X-WP-Nonce", HAPI_CART_CFG.nonce);
            },
         });

         // setMsg($scope, res && res.message ? res.message : "");
         if (res && Array.isArray(res.errors) && res.errors.length) {
            setMsg($scope, res.errors.join(" "));
         } else {
            setMsg($scope, res && res.message ? res.message : "");
         }

         // Refresh coupon fragment to reflect new single-code value
         const ctx = getContext($scope);
         await refreshCouponFragment(ctx);

         // Refresh totals
         if (inCheckout) {
            $(document.body).trigger("update_checkout");
         } else if (window.HAPI_CART && HAPI_CART.refreshAfterChange) {
            HAPI_CART.refreshAfterChange();
         }
      } catch (e) {
         // setMsg($scope, "Tekniskt fel.");
         // Try to surface API-provided errors even on transport failure
         let msg = "Tekniskt fel.";
         if (e && e.responseJSON) {
            const r = e.responseJSON;
            if (Array.isArray(r.errors) && r.errors.length) {
               msg = r.errors.join(" ");
            } else if (r.message) {
               msg = r.message;
            }
         }
         setMsg($scope, msg);
      } finally {
         setBusy($scope, false);
      }
   }

   // Apply
   $(document).on("click", "[data-role='apply-coupon']", function (e) {
      e.preventDefault();
      const $scope = $(this).closest(".cart-discount");
      const code = ($scope.find(".cart-discount__input").val() || "").trim();
      if (!code) {
         setMsg($scope, "Fyll i en kod.");
         return;
      }
      callCouponAPI("apply", code, $scope);
   });

   // Clear
   $(document).on("click", "[data-role='clear-code']", function (e) {
      e.preventDefault();
      const $scope = $(this).closest(".cart-discount");
      callCouponAPI("clear", "", $scope);
   });

   // INPUT: type/blur a quantity → use the SAME path as +/-
   $(document).on("input change blur", ".cart-drawer__item-qty-input", function () {
      const $inp = $(this);
      const cartKey = $inp.data("cart-key");
      if (!cartKey) return;

      let qty = parseInt($inp.val(), 10);
      if (isNaN(qty) || qty < 0) qty = 0;
      if ($inp.val() !== String(qty)) $inp.val(qty);

      if (isCheckoutContext($inp)) {
         // Checkout: mirror hidden field, refresh checkout (same as your +/-)
         const $form = $("form.checkout");
         const $hid = $form.find('input.cart-hidden-qty[data-cart-key="' + cartKey + '"]');
         if ($hid.length) {
            setItemLoading($inp, true);
            $hid.val(qty).trigger("change");
            scheduleCheckoutRefresh();
         }
         return;
      }

      // Drawer: same admin-ajax endpoint your +/- uses, now with "set"
      setItemLoading($inp, true);
      $.ajax({
         url: wc_add_to_cart_params.ajax_url,
         type: "POST",
         dataType: "json",
         data: {
            action: "update_cart_item_quantity",
            cart_key: cartKey,
            update_action: "set",
            quantity: qty,
         },
      })
         .done(function (res) {
            if (!res || res.success !== true) {
               console.error("AJAX error:", res && res.data);
               return;
            }
            if (window.HAPI_CART && HAPI_CART.refreshAfterChange) HAPI_CART.refreshAfterChange();
         })
         .fail(function (xhr, status, err) {
            console.error("AJAX error:", status, err);
         })
         .always(function () {
            setItemLoading($inp, false);
         });
   });

   // Optional: Enter key commits immediately (prevents accidental form submits)
   $(document).on("keydown", ".cart-drawer__item-qty-input", function (e) {
      if (e.key === "Enter") {
         e.preventDefault();
         $(this).trigger("blur"); // will run the handler above
      }
   });
});
