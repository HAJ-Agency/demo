<?php

namespace Demo\Includes\WooCommerce;

if (!defined('ABSPATH')) {
   exit;
}

class ProductAccordionFields {
   const META_KEY = '_wc_product_accordions';

   public function __construct() {
      // Admin UI (single tab for product + all variations)
      add_filter('woocommerce_product_data_tabs', [$this, 'addTab']);
      add_action('woocommerce_product_data_panels', [$this, 'renderPanel']);
      add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
      add_action('woocommerce_process_product_meta', [$this, 'saveAllMeta'], 10, 1);

      // Frontend stays the same
      add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
      add_action('tiburon_after_add_to_cart_button', [$this, 'renderFrontend'], 16);
      // add_action('woocommerce_after_single_product_summary', [$this, 'renderFrontend'], 16);
   }

   /* ---------------- Assets ---------------- */
   private function asset_url(string $rel): string {
      return get_stylesheet_directory_uri() . '/build/assets/' . ltrim($rel, '/');
   }
   private function asset_path(string $rel): string {
      return get_stylesheet_directory() . '/build/assets/' . ltrim($rel, '/');
   }
   private function ver(string $rel): string {
      $p = $this->asset_path($rel);
      return file_exists($p) ? (string) filemtime($p) : '1.0.0';
   }

   /* ---------------- Admin UI ---------------- */
   public function addTab($tabs) {
      $tabs['wc_product_accordion'] = [
         'label'    => __('Accordions', 'tiburon'),
         'target'   => 'wc_product_accordion_data',
         'class'    => ['show_if_simple', 'show_if_variable', 'hide_if_grouped', 'hide_if_external'],
         'priority' => 80,
      ];
      return $tabs;
   }

   public function renderPanel() {
      global $post;
      $product = wc_get_product($post->ID);
      $is_variable = $product && $product->is_type('variable');

      echo '<div id="wc_product_accordion_data" class="panel woocommerce_options_panel">';
      wp_nonce_field('wc_save_all_accordions', 'wc_all_accordions_nonce');

      /* Product-level section */
      $p_items = $this->getAccordionItems((int) $post->ID);
      $p_next  = $this->nextIndexFromArray($p_items, 1); // we render index 0 initially

      echo '<h3 class="wc-accordion-heading">' . esc_html__('Produkt-accordions', 'tiburon') . '</h3>';
      echo '<div id="wc-accordion-repeater" class="wc-accordion-repeater" data-next-index="' . esc_attr($p_next) . '">';

      if ($p_items) {
         foreach ($p_items as $i => $row) {
            $this->renderProductRow((int) $i, $row);
         }
      } else {
         $this->renderProductRow(0, ['title' => '', 'content' => '']);
      }
      echo '</div>';
      echo '<p><button type="button" class="button" id="wc-accordion-add">' . esc_html__('Lägg till accordion', 'tiburon') . '</button></p>';

      /* Variation sections (inside the same tab) */
      if ($is_variable) {
         echo '<hr />';
         echo '<h3 class="wc-accordion-heading">' . esc_html__('Variation-accordions', 'tiburon') . '</h3>';

         foreach ($product->get_children() as $variation_id) {
            $variation = wc_get_product($variation_id);
            if (!$variation) {
               continue;
            }

            $v_items = $this->getAccordionItems($variation_id);
            $v_next  = $this->nextIndexFromArray($v_items, 1);

            $label = $this->variationLabel($variation);

            echo '<div class="wc-variation-accordion-block" data-variation="' . esc_attr($variation_id) . '">';
            echo '<h4 class="wc-variation-spec">' . esc_html($label) . '</h4>';

            echo '<div class="wc-variation-repeater" id="wc-variation-repeater-' . esc_attr($variation_id) . '" data-next-index="' . esc_attr($v_next) . '">';

            if ($v_items) {
               foreach ($v_items as $i => $row) {
                  $this->renderVariationRow($variation_id, (int) $i, $row);
               }
            } else {
               $this->renderVariationRow($variation_id, 0, ['title' => '', 'content' => '']);
            }

            echo '</div>';
            echo '<p><button type="button" class="button wc-var-add" data-variation="' . esc_attr($variation_id) . '">' . esc_html__('Add accordion', 'tiburon') . '</button></p>';
            echo '<hr /></div>';
         }
      }

      echo '</div>'; // panel
   }

   private function nextIndexFromArray($items, int $empty_next): int {
      if (is_array($items) && !empty($items)) {
         $keys = array_map('intval', array_keys($items));
         return (max($keys) + 1);
      }
      return $empty_next; // when we render index 0 initially, next must be 1
   }

   private function variationLabel(\WC_Product_Variation $v): string {
      $attrs = wc_get_formatted_variation($v, true, false, true);
      $sku   = $v->get_sku();
      $id    = $v->get_id();
      $bits  = array_filter([$attrs, $sku ? "SKU: $sku" : null, "ID: $id"]);
      return implode(' | ', $bits);
   }

   private function renderProductRow(int $index, array $row) {
      $title     = $row['title'] ?? '';
      $content   = $row['content'] ?? '';
      $editor_id = 'wc_accordion_' . $index . '_content';

      echo '<div class="wc-accordion-row" data-index="' . esc_attr($index) . '">';
      echo '<p class="form-field"><label class="wc-accordion-title-label">' . esc_html__('Rubrik', 'tiburon') . '</label>';
      echo '<input type="text" name="wc_accordion[' . esc_attr($index) . '][title]" value="' . esc_attr($title) . '" class="short wc-accordion-title"></p>';

      echo '<div class="form-field"><label class="wc-accordion-label">' . esc_html__('Innehåll', 'tiburon') . '</label>';
      $this->editor($content, $editor_id, 'wc_accordion[' . $index . '][content]', 6);
      echo '</div>';

      echo '<p><button type="button" class="button link-delete wc-accordion-remove">' . esc_html__('Ta bort', 'tiburon') . '</button></p>';
      echo '</div>';
   }

   private function renderVariationRow(int $variation_id, int $index, array $row) {
      $title     = $row['title'] ?? '';
      $content   = $row['content'] ?? '';
      $editor_id = 'wc_var_' . $variation_id . '_' . $index . '_content';

      echo '<div class="wc-var-row" data-index="' . esc_attr($index) . '">';
      echo '<p class="form-field"><label class="wc-accordion-title-label">' . esc_html__('Rubrik', 'tiburon') . '</label>';
      echo '<input type="text" name="wc_var_accordion[' . esc_attr($variation_id) . '][' . esc_attr($index) . '][title]" value="' . esc_attr($title) . '" class="wc-accordion-title" /></p>';

      echo '<div class="form-field"><label class="wc-accordion-label">' . esc_html__('Innehåll', 'tiburon') . '</label>';
      $this->editor($content, $editor_id, 'wc_var_accordion[' . $variation_id . '][' . $index . '][content]', 5);
      echo '</div>';

      echo '<p><button type="button" class="button link-delete wc-var-remove">' . esc_html__('Ta bort', 'tiburon') . '</button></p>';
      echo '</div>';
   }

   private function editor(string $content, string $editor_id, string $textarea_name, int $rows) {
      // Full TinyMCE with headings + readable content
      wp_editor(
         $content,
         $editor_id,
         [
            'textarea_name' => $textarea_name,
            'textarea_rows' => 5,
            'media_buttons' => true,
            'teeny'         => false,
            'quicktags'     => true,
         ]
      );
   }

   public function enqueueAdminAssets($hook) {
      if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
      $screen = get_current_screen();
      if (!$screen || $screen->post_type !== 'product') return;

      if (function_exists('wp_enqueue_editor')) wp_enqueue_editor();
      if (function_exists('wp_enqueue_media')) wp_enqueue_media();

      wp_enqueue_style(
         'product-accordion-admin',
         $this->asset_url('css/admin/wc.product-accordion.admin.css'),
         [],
         $this->ver('css/admin/wc.product-accordion.admin.css')
      );

      // one script handles product + per-variation repeaters inside this tab
      wp_enqueue_script(
         'product-accordion-admin',
         $this->asset_url('js/wc.product-accordion.admin.js'),
         ['jquery', 'editor'],
         $this->ver('js/wc.product-accordion.admin.js'),
         true
      );

      wp_localize_script('product-accordion-admin', 'WcAccI18N', [
         'title'   => __('Rubrik', 'tiburon'),
         'content' => __('Innehåll', 'tiburon'),
         'remove'  => __('Ta bort', 'tiburon'),
      ]);
   }

   /* Save product + all variation accordions here */
   public function saveAllMeta($post_id) {
      if (!isset($_POST['wc_all_accordions_nonce']) || !wp_verify_nonce($_POST['wc_all_accordions_nonce'], 'wc_save_all_accordions')) {
         return;
      }
      if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
      if (!current_user_can('edit_product', $post_id)) return;

      // Product-level
      $p_data = isset($_POST['wc_accordion']) && is_array($_POST['wc_accordion']) ? $_POST['wc_accordion'] : [];
      $p_clean = [];
      foreach ($p_data as $row) {
         if (empty($row['title']) && empty($row['content'])) continue;
         $p_clean[] = [
            'title'   => isset($row['title']) ? sanitize_text_field($row['title']) : '',
            'content' => isset($row['content']) ? wp_kses_post($row['content']) : '',
         ];
      }
      if (!empty($p_clean)) update_post_meta($post_id, self::META_KEY, $p_clean);
      else delete_post_meta($post_id, self::META_KEY);

      // Variations (keyed by variation_id)
      $v_root = isset($_POST['wc_var_accordion']) && is_array($_POST['wc_var_accordion']) ? $_POST['wc_var_accordion'] : [];
      foreach ($v_root as $variation_id => $rows) {
         $variation_id = (int) $variation_id;
         if ($variation_id <= 0) continue;

         $v_clean = [];
         foreach ((array) $rows as $row) {
            if (empty($row['title']) && empty($row['content'])) continue;
            $v_clean[] = [
               'title'   => isset($row['title']) ? sanitize_text_field($row['title']) : '',
               'content' => isset($row['content']) ? wp_kses_post($row['content']) : '',
            ];
         }
         if (!empty($v_clean)) update_post_meta($variation_id, self::META_KEY, $v_clean);
         else delete_post_meta($variation_id, self::META_KEY);
      }
   }

   /* ---------------- Frontend ---------------- */
   public function enqueueFrontendAssets() {
      if (!is_product()) return;
      wp_enqueue_style(
         'product-accordion-frontend',
         $this->asset_url('css/wc.product-accordion.css'),
         [],
         $this->ver('css/wc.product-accordion.css')
      );
      wp_enqueue_script(
         'product-accordion-frontend',
         $this->asset_url('js/wc.product-accordion.js'),
         ['jquery'],
         $this->ver('js/wc.product-accordion.js'),
         true
      );
   }

   public function renderFrontend() {
      global $product;
      if (! $product) return;

      $pid = $product->get_id();
      $product_items = get_post_meta($pid, self::META_KEY, true);
      $product_items = is_array($product_items) ? $product_items : [];

      // Helper to print one accordion list (product or variation)
?>
      <!-- <div class="accordion-top-border"></div> -->
      <?php
      $print_items = function (array $items, string $uid_prefix) {
         if (empty($items)) return; ?>
         <div class="wc-accordion" data-accordion>
            <?php foreach ($items as $i => $row):
               $t = isset($row['title']) ? trim($row['title']) : '';
               $c = isset($row['content']) ? $row['content'] : '';
               if ($t === '' && trim(wp_strip_all_tags($c)) === '') continue;

               // Unique IDs for a11y connections
               $trigger_id = "{$uid_prefix}-trigger-{$i}";
               $panel_id   = "{$uid_prefix}-panel-{$i}";
            ?>
               <div class="wc-acc__item">
                  <h3 class="wc-acc__heading">
                     <button
                        type="button"
                        class="wc-acc__trigger"
                        id="<?php echo esc_attr($trigger_id); ?>"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr($panel_id); ?>">
                        <span class="wc-acc__title"><?php echo esc_html($t ?: __('Details', 'tiburon')); ?></span>
                        <span class="wc-acc__icon" aria-hidden="true"></span>
                     </button>
                  </h3>

                  <div
                     id="<?php echo esc_attr($panel_id); ?>"
                     class="wc-acc__panel"
                     role="region"
                     aria-labelledby="<?php echo esc_attr($trigger_id); ?>"
                     aria-hidden="true"
                     inert>
                     <div class="wc-acc__panel-inner">
                        <?php
                        // Allow Woo content (shortcodes, embeds) while keeping it safe
                        echo wp_kses_post(wpautop($c));
                        ?>
                     </div>
                  </div>
               </div>
            <?php endforeach; ?>
         </div>
         <?php };

      // Render product-level accordions
      if (!empty($product_items)) {
         $print_items($product_items, "acc-{$pid}-p");
      }

      // Render per-variation accordions (hidden until that variation is selected)
      if ($product->is_type('variable')) {
         foreach ($product->get_children() as $vid) {
            $v_items = get_post_meta($vid, self::META_KEY, true);
            $v_items = is_array($v_items) ? $v_items : [];
            if (empty($v_items)) continue; ?>
            <div class="wc-variation-accordion wc-hidden" data-variation="<?php echo esc_attr($vid); ?>">
               <?php $print_items($v_items, "acc-{$pid}-v{$vid}"); ?>
            </div>
<?php }
      }
   }

   private function getAccordionItems(int $post_id): array {
      $items = get_post_meta($post_id, self::META_KEY, true);
      return is_array($items) ? $items : [];
   }
}
