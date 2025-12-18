<?php
if (! defined('ABSPATH')) exit;

global $product;
if (! $product || ! is_a($product, 'WC_Product')) {
   $product = wc_get_product(get_queried_object_id());
}
if (! $product) return;

$rest_url       = get_rest_url(null, 'hapi/v1/products/add_to_cart');
$product_id     = $product->get_id();
$product_type   = $product->get_type();
$is_variable    = ('variable' === $product_type) && $product->is_type('variable');

$in_stock = false;

if ($product) {
   if ($product->is_type('simple')) {
      $in_stock = $product->is_in_stock();
   } elseif ($product->is_type('variable')) {
      $variations = $product->get_available_variations();
      foreach ($variations as $variation) {
         $v = wc_get_product($variation['variation_id']);
         if ($v && $v->is_in_stock()) {
            $in_stock = true;
            break;
         }
      }
   }
}

$available_variations = [];
$variation_attributes = [];
$default_attributes   = [];
$variation_descs_map  = [];

if ($is_variable) {
   /** @var WC_Product_Variable $product */
   $available_variations = $product->get_available_variations();
   $variation_attributes = $product->get_variation_attributes(); // [ 'attribute_pa_color' => [ 'red', ... ] ] or [ 'pa_color' => ... ] depending on Woo version
   $default_attributes   = $product->get_default_attributes();   // [ 'pa_color' => 'red', ... ]

   // Collect per-variation descriptions
   foreach ($product->get_children() as $vid) {
      $v = wc_get_product($vid);
      if (!$v) continue;
      $vd = $v->get_description(); // “Variation description” field
      if ($vd) {
         // Format like Woo would on the front
         if (function_exists('wc_format_content')) {
            $vd = wc_format_content($vd);
         } else {
            $vd = wpautop($vd);
         }
         $variation_descs_map[$vid] = $vd;
      }
   }
}

// Parent description (prefer short)
$parent_desc = $product->get_short_description();
if (!$parent_desc) $parent_desc = $product->get_description();
if ($parent_desc) {
   if (function_exists('wc_format_content')) {
      $parent_desc = wc_format_content($parent_desc);
   } else {
      $parent_desc = wpautop($parent_desc);
   }
}

// Parent price HTML (range for variable, plain for simple)
$parent_price_html = $product->get_price_html();

// Small helper to present nice labels for attributes.
function hapi_attr_label($taxonomy_or_name) {
   return wc_attribute_label($taxonomy_or_name);
}

$currency         = get_woocommerce_currency();
$parent_price_num = wc_get_price_to_display($product); // numeric, tax-display aware
$product_name     = $product->get_name();
$product_sku      = $product->get_sku();

// categories (best-effort, take first category + its ancestors)
$cat_chain = [];
$cats = wc_get_product_terms($product_id, 'product_cat', ['orderby' => 'menu_order']);
if (!empty($cats)) {
   $primary = $cats[0];
   $anc = array_reverse(get_ancestors($primary->term_id, 'product_cat'));
   foreach ($anc as $tid) {
      $t = get_term($tid, 'product_cat');
      if ($t && !is_wp_error($t)) $cat_chain[] = $t->name;
   }
   $cat_chain[] = $primary->name;
}

// optional brand (common taxonomies)
$brand = '';
foreach (['product_brand', 'brand', 'pa_brand'] as $tax) {
   if (taxonomy_exists($tax)) {
      $names = wc_get_product_terms($product_id, $tax, ['fields' => 'names']);
      if (!empty($names)) {
         $brand = $names[0];
         break;
      }
   }
}

// optional: variation SKUs map (helps GA4 item_id = SKU preference)
$variation_skus = [];
if ($is_variable) {
   foreach ($product->get_children() as $vid) {
      $v = wc_get_product($vid);
      if (!$v) continue;
      $sku = $v->get_sku();
      if ($sku) $variation_skus[$vid] = $sku;
   }
}
?>
<div
   class="hapi-ajax-atc"
   data-rest-url="<?php echo esc_url($rest_url); ?>"
   data-product-id="<?php echo esc_attr($product_id); ?>"
   data-product-type="<?php echo esc_attr($product_type); ?>"
   data-in-stock="<?php echo esc_attr($product->is_in_stock() ? 'true' : 'false'); ?>"
   data-parent-price-html="<?php echo esc_attr($parent_price_html); ?>"
   data-parent-desc="<?php echo esc_attr($parent_desc); ?>"
   <?php if ($is_variable): ?>
   data-available-variations="<?php echo esc_attr(wp_json_encode($available_variations, JSON_UNESCAPED_UNICODE)); ?>"
   data-variation-attributes="<?php echo esc_attr(wp_json_encode($variation_attributes, JSON_UNESCAPED_UNICODE)); ?>"
   data-default-attributes="<?php echo esc_attr(wp_json_encode($default_attributes, JSON_UNESCAPED_UNICODE)); ?>"
   data-variation-descriptions="<?php echo esc_attr(wp_json_encode($variation_descs_map, JSON_UNESCAPED_UNICODE)); ?>"
   <?php endif; ?>
   data-i18n="<?php echo esc_attr(wp_json_encode([
                  'add'        => __('Lägg i varukorg', 'demo'),
                  'added'      => __('Tillagd i varukorg', 'demo'),
                  'selectAll'  => __('Välj alternativ', 'demo'),
                  'outOfStock' => __('Slut i lager', 'demo'),
                  'unavailable' => __('Ej tillgänglig kombination', 'demo'),
                  'qty'        => __('Antal', 'demo'),
                  'readMore'   => __('Läs mer', 'demo'),
                  'readLess'   => __('Visa mindre', 'demo'),
               ], JSON_UNESCAPED_UNICODE)); ?>"
   data-currency="<?php echo esc_attr($currency); ?>"
   data-parent-price="<?php echo esc_attr(wc_format_decimal($parent_price_num)); ?>"
   data-product-name="<?php echo esc_attr($product_name); ?>"
   data-product-sku="<?php echo esc_attr($product_sku); ?>"
   data-categories="<?php echo esc_attr(wp_json_encode($cat_chain, JSON_UNESCAPED_UNICODE)); ?>"
   data-brand="<?php echo esc_attr($brand); ?>"
   <?php if ($is_variable): ?>
   data-variation-skus="<?php echo esc_attr(wp_json_encode($variation_skus, JSON_UNESCAPED_UNICODE)); ?>"
   <?php endif; ?>>

   <!-- Dynamic price goes here -->
   <div class="hapi-price" aria-live="polite" aria-atomic="true"></div>

   <div class="hapi-qty-and-btn__wrapper">
      <?php if ($in_stock) : ?>
         <div class="hapi-qty" aria-label="<?php echo esc_attr__('Antal', 'demo'); ?>">
            <button type="button" class="hapi-qty__btn hapi-qty__btn--minus" aria-label="<?php echo esc_attr__('Minska antal', 'demo'); ?>">−</button>
            <input class="hapi-qty__input" type="number" inputmode="numeric" min="1" step="1" value="1" aria-label="<?php echo esc_attr__('Antal', 'demo'); ?>" />
            <button type="button" class="hapi-qty__btn hapi-qty__btn--plus" aria-label="<?php echo esc_attr__('Öka antal', 'demo'); ?>">+</button>
         </div>
      <?php endif; ?>
      <div class="hapi-atc-wrap">
         <?php if ($in_stock) : ?>
            <button
               type="button"
               class="hapi-atc-button"
               aria-label="<?php echo esc_attr__('Add to cart', 'demo'); ?>"
               data-disabled="false">
               <?php echo esc_html__('Add to cart', 'demo'); ?>
               <svg class="cart-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.881 122.88" width="16" height="16">
                  <path d="M56.573,4.868c0-0.655,0.132-1.283,0.37-1.859c0.249-0.6,0.61-1.137,1.056-1.583C58.879,0.545,60.097,0,61.44,0 c0.658,0,1.287,0.132,1.863,0.371c0.012,0.005,0.023,0.011,0.037,0.017c0.584,0.248,1.107,0.603,1.543,1.039 c0.881,0.88,1.426,2.098,1.426,3.442c0,0.03-0.002,0.06-0.006,0.089v51.62l51.619,0c0.029-0.003,0.061-0.006,0.09-0.006 c0.656,0,1.285,0.132,1.861,0.371c0.014,0.005,0.025,0.011,0.037,0.017c0.584,0.248,1.107,0.603,1.543,1.039 c0.881,0.88,1.428,2.098,1.428,3.441c0,0.654-0.133,1.283-0.371,1.859c-0.248,0.6-0.609,1.137-1.057,1.583 c-0.445,0.445-0.98,0.806-1.58,1.055v0.001c-0.576,0.238-1.205,0.37-1.861,0.37c-0.029,0-0.061-0.002-0.09-0.006l-51.619,0.001 v51.619c0.004,0.029,0.006,0.06,0.006,0.09c0,0.656-0.133,1.286-0.371,1.861c-0.006,0.014-0.012,0.025-0.018,0.037 c-0.248,0.584-0.602,1.107-1.037,1.543c-0.883,0.882-2.1,1.427-3.443,1.427c-0.654,0-1.283-0.132-1.859-0.371 c-0.6-0.248-1.137-0.609-1.583-1.056c-0.445-0.444-0.806-0.98-1.055-1.58h-0.001c-0.239-0.575-0.371-1.205-0.371-1.861 c0-0.03,0.002-0.061,0.006-0.09V66.303H4.958c-0.029,0.004-0.059,0.006-0.09,0.006c-0.654,0-1.283-0.132-1.859-0.371 c-0.6-0.248-1.137-0.609-1.583-1.056c-0.445-0.445-0.806-0.98-1.055-1.58H0.371C0.132,62.726,0,62.097,0,61.44 c0-0.655,0.132-1.283,0.371-1.859c0.249-0.6,0.61-1.137,1.056-1.583c0.881-0.881,2.098-1.426,3.442-1.426 c0.031,0,0.061,0.002,0.09,0.006l51.62,0l0-51.62C56.575,4.928,56.573,4.898,56.573,4.868L56.573,4.868z" />
               </svg>
               <!-- <div class="hapi-atc-notice" aria-live="polite" aria-atomic="true"></div> -->
            </button>
         <?php else : ?>
            <button
               type="button"
               class="hapi-atc-button is-disabled hapi-atc-button--out-of-stock"
               disabled
               aria-label="<?php echo esc_attr__('Out of stock', 'demo'); ?>">
               <?php echo esc_html__('Out of stock', 'demo'); ?>
            </button>
         <?php endif; ?>
         <div class="hapi-status" aria-live="polite" aria-atomic="true"></div>
         <div class="added_to_cart"></div>
      </div>
   </div>

   <!-- Dynamic description (variation → fallback to parent), clamped with “Read more” -->
   <div class="hapi-desc">
      <div class="hapi-desc__content"><!-- filled by JS --></div>
      <!-- <button type="button" class="hapi-desc__toggle" aria-expanded="false" hidden>
         <?php echo esc_html__('Läs mer', 'demo'); ?>
      </button> -->
   </div>

   <?php if ($is_variable && ! empty($variation_attributes)): ?>
      <div class="hapi-attrs" role="group" aria-label="<?php echo esc_attr__('Produktattribut', 'demo'); ?>">
         <?php foreach ($variation_attributes as $attr_name => $options) :
            $label = hapi_attr_label($attr_name); ?>
            <div class="hapi-attr" data-attribute-name="<?php echo esc_attr($attr_name); ?>">
               <div class="hapi-attr__label"><?php echo esc_html($label); ?></div>
               <div class="hapi-attr__options" role="radiogroup" aria-label="<?php echo esc_attr($label); ?>">
                  <?php foreach ($options as $term_slug):
                     $term_name = $term_slug;
                     if (taxonomy_is_product_attribute($attr_name)) {
                        $taxonomy = wc_sanitize_taxonomy_name($attr_name);
                        $term     = get_term_by('slug', $term_slug, $taxonomy);
                        if ($term && ! is_wp_error($term)) $term_name = $term->name;
                     } ?>
                     <button type="button" class="hapi-attr__option" data-term="<?php echo esc_attr($term_slug); ?>" aria-pressed="false">
                        <?php echo esc_html($term_name); ?>
                     </button>
                  <?php endforeach; ?>
               </div>
            </div>
         <?php endforeach; ?>
      </div>
   <?php endif; ?>

</div>

<?php do_action("tiburon_after_add_to_cart_button", $product);
