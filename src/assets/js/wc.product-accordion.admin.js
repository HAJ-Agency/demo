(function ($) {
   // Tiny, safe initializer (no double-init)
   var EditorBoot = (function () {
      var waiting = [];
      var queued = new Set();
      var ticking = false;

      function api() {
         if (!window.wp) return null;
         if (wp.editor && typeof wp.editor.initialize === "function") return wp.editor;
         if (wp.oldEditor && typeof wp.oldEditor.initialize === "function") return wp.oldEditor;
         return null;
      }
      function isReady() {
         return !!(api() && window.tinymce && window.QTags);
      }
      function hasEditor(id) {
         return (
            (window.tinymce && tinymce.get && tinymce.get(id)) ||
            (window.QTags && QTags.instances && QTags.instances[id]) ||
            document.getElementById("wp-" + id + "-wrap")
         );
      }
      function flush() {
         if (!isReady()) return;
         var edApi = api();
         var next = [];
         for (var i = 0; i < waiting.length; i++) {
            var it = waiting[i];
            if (!document.getElementById(it.id)) {
               queued.delete(it.id);
               continue;
            }
            if (hasEditor(it.id)) {
               queued.delete(it.id);
               continue;
            }
            try {
               edApi.initialize(
                  it.id,
                  it.settings || { tinymce: true, quicktags: true, mediaButtons: true }
               );
            } catch (e) {}
            queued.delete(it.id);
         }
         waiting = next;
      }
      function schedule() {
         if (ticking) return;
         ticking = true;
         (function retry(a) {
            flush();
            if (isReady() || a > 40) {
               ticking = false;
               return;
            }
            setTimeout(function () {
               retry(a + 1);
            }, 250);
         })(0);
      }
      function init(id, settings) {
         if (!id) return;
         if (hasEditor(id)) return;
         if (!queued.has(id)) {
            queued.add(id);
            waiting.push({ id: id, settings: settings });
            schedule();
         }
      }
      return { init: init, isReady: isReady, hasEditor: hasEditor };
   })();

   function setActiveMode(id) {
      var $w = $("#wp-" + id + "-wrap");
      if (!$w.length) return;
      $w.removeClass("html-active").addClass("tmce-active");
      if (window.switchEditors && switchEditors.go) {
         try {
            switchEditors.go(id, "tmce");
         } catch (e) {}
      }
   }

   function nextIndex($container, rowSel) {
      var attrVal = parseInt($container.attr("data-next-index"), 10);
      if (isNaN(attrVal)) attrVal = 0;
      var domMax = -1;
      $container.find(rowSel).each(function () {
         var n = parseInt($(this).attr("data-index"), 10);
         if (!isNaN(n) && n > domMax) domMax = n;
      });
      var cur = Math.max(attrVal, domMax + 1);
      $container.attr("data-next-index", cur + 1);
      return cur;
   }

   function t(key) {
      return (window.WcAccI18N && WcAccI18N[key]) || key;
   }

   /* ---------- Product-level add/remove ---------- */
   $(document).on("click", "#wc-accordion-add", function (e) {
      e.preventDefault();
      var $container = $("#wc-accordion-repeater");
      var i = nextIndex($container, ".wc-accordion-row");
      var textareaId = "wc_accordion_" + i + "_content";

      var html = [
         '<div class="wc-accordion-row" data-index="' + i + '">',
         '  <p class="form-field"><label>' + t("title") + "</label>",
         '    <input type="text" name="wc_accordion[' +
            i +
            '][title]" class="short wc-accordion-title" />',
         "  </p>",
         '  <div class="form-field"><label class="wc-accordion-label">' + t("content") + "</label>",
         '    <textarea id="' +
            textareaId +
            '" name="wc_accordion[' +
            i +
            '][content]" rows="6"></textarea>',
         "  </div>",
         '  <p><button type="button" class="button link-delete wc-accordion-remove">' +
            t("remove") +
            "</button></p>",
         "</div>",
      ].join("");
      $container.append($(html));

      EditorBoot.init(textareaId, { tinymce: true, quicktags: true, mediaButtons: true });
      setActiveMode(textareaId);
   });

   $(document).on("click", ".wc-accordion-remove", function (e) {
      e.preventDefault();
      var $wrap = $(this).closest(".wc-accordion-row");
      var $ta = $wrap.find("textarea");
      var id = $ta.attr("id");
      if (EditorBoot.isReady() && id && window.wp) {
         var api =
            wp.editor && wp.editor.remove
               ? wp.editor
               : wp.oldEditor && wp.oldEditor.remove
               ? wp.oldEditor
               : null;
         if (api) {
            try {
               api.remove(id);
            } catch (e) {}
         }
      }
      $wrap.remove();
   });

   /* ---------- Variation add/remove (in the same tab) ---------- */
   $(document).on("click", ".wc-var-add", function (e) {
      e.preventDefault();
      var variationId = $(this).data("variation");
      var $container = $("#wc-variation-repeater-" + variationId);
      if (!$container.length) return;

      var i = nextIndex($container, ".wc-var-row");
      var textareaId = "wc_var_" + variationId + "_" + i + "_content";

      var html = [
         '<div class="wc-var-row" data-index="' + i + '">',
         '  <p class="form-field"><label>' + t("title") + "</label>",
         '    <input type="text" name="wc_var_accordion[' +
            variationId +
            "][" +
            i +
            '][title]" class="wc-accordion-title" />',
         "  </p>",
         '  <div class="form-field"><label class="wc-accordion-label">' + t("content") + "</label>",
         '    <textarea id="' +
            textareaId +
            '" name="wc_var_accordion[' +
            variationId +
            "][" +
            i +
            '][content]" rows="5"></textarea>',
         "  </div>",
         '  <p><button type="button" class="button link-delete wc-var-remove">' +
            t("remove") +
            "</button></p>",
         "</div>",
      ].join("");
      $container.append($(html));

      EditorBoot.init(textareaId, { tinymce: true, quicktags: true, mediaButtons: true });
      setActiveMode(textareaId);
   });

   $(document).on("click", ".wc-var-remove", function (e) {
      e.preventDefault();
      var $wrap = $(this).closest(".wc-var-row");
      var $ta = $wrap.find("textarea");
      var id = $ta.attr("id");
      if (EditorBoot.isReady() && id && window.wp) {
         var api =
            wp.editor && wp.editor.remove
               ? wp.editor
               : wp.oldEditor && wp.oldEditor.remove
               ? wp.oldEditor
               : null;
         if (api) {
            try {
               api.remove(id);
            } catch (e) {}
         }
      }
      $wrap.remove();
   });

   /* Initialize any non-initialized editors when the tab becomes visible */
   $(document).on("click", ".product_data_tabs a", function () {
      if ($(this).attr("href") === "#wc_product_accordion_data") {
         setTimeout(function () {
            $("#wc_product_accordion_data textarea[id]").each(function () {
               var id = this.id;
               if (!EditorBoot.hasEditor(id)) {
                  EditorBoot.init(id, { tinymce: true, quicktags: true, mediaButtons: true });
                  setActiveMode(id);
               }
            });
         }, 0);
      }
   });
})(jQuery);
