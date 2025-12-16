<?php

use function demo\Includes\Functions\get_product_data;

$is_grid = !empty($attributes['isGrid']);

// Only enqueue Swiper when NOT grid
if (!$is_grid) {
   if (!wp_script_is('swiper-js', 'enqueued')) {
      wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);
   }
   if (!wp_style_is('swiper-css', 'enqueued')) {
      wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], null);
   }
}

// Show ALL in grid, else respect control (0 = all)
$total_posts = $is_grid ? -1 : ($attributes['numberOfProducts'] ?? 6);

$categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
$foci       = get_terms(['taxonomy' => 'foci', 'hide_empty' => true]);
$needs      = get_terms(['taxonomy' => 'needs', 'hide_empty' => true]);

$selected_category = get_query_var('product_cat');
$selected_focus = get_query_var('foci');
$selected_need = get_query_var('needs');

$active_taxonomy = !empty($selected_need) ? 'needs'
   : (!empty($selected_focus) ? 'foci'
      : (!empty($selected_category) ? 'product_cat' : 'needs'));

// If is on single product page exclude the product from results
$exclude_products = [];
if (is_product()) {
   $current_product_id = get_the_ID();
   $exclude_products[] = $current_product_id;
}

$product_data = get_product_data([
   'numberOfProducts' => $total_posts,
   'productCategory'  => $selected_category,
   'productFocus'     => $selected_focus,
   'productNeed'      => $selected_need,
   'excludeProducts'  => $exclude_products,
]);

if (!function_exists('tabAttrs')) {
   function tabAttrs($tax, $active_taxonomy, $label) {
      $isActive = $tax === $active_taxonomy;
      $id = "tab-$tax";
      $controls = "panel-$tax";
      return sprintf(
         'id="%s" class="filter-taxonomy%s" role="tab" aria-selected="%s" aria-controls="%s" tabindex="0" data-taxonomy="%s" aria-label="%s"',
         esc_attr($id),
         $isActive ? ' is-active' : '',
         $isActive ? 'true' : 'false',
         esc_attr($controls),
         esc_attr($tax),
         esc_attr($label)
      );
   }
}
if (!function_exists('panelAttrs')) {
   function panelAttrs($tax, $active_taxonomy) {
      $isActive = $tax === $active_taxonomy;
      $id = "panel-$tax";
      $labelledby = "tab-$tax";
      return sprintf(
         'id="%s" class="filter-content filter-%s"%s role="tabpanel" aria-labelledby="%s" data-taxonomy="%s"',
         esc_attr($id),
         esc_attr($tax),
         $isActive ? '' : ' hidden',
         esc_attr($labelledby),
         esc_attr($tax)
      );
   }
}

if (!function_exists('render_product_cards')) {
   function render_product_cards($product_data, $is_grid = false) {
      while ($product_data->have_posts()): $product_data->the_post();
         $product_id   = get_the_ID();
         $product      = wc_get_product($product_id);
         $is_variation = $product instanceof \WC_Product_Variation;
         $parent       = $is_variation ? wc_get_product($product->get_parent_id()) : null;

         $product_title      = $product->get_name();
         $product_price_html = wc_get_price_to_display($product);
         $permalink          = $product->get_permalink();

         // --- Excerpt with parent fallbacks ---
         $excerpt = trim(wp_strip_all_tags($product->get_short_description()));

         if ($excerpt === '' && $is_variation && $parent) {
            $excerpt = trim(wp_strip_all_tags($parent->get_short_description()));
         }
         if ($excerpt === '') {
            $excerpt = trim(wp_strip_all_tags($product->get_description()));
         }
         if ($excerpt === '' && $is_variation && $parent) {
            $excerpt = trim(wp_strip_all_tags($parent->get_description()));
         }

         if (!$is_grid) {
?>
            <div class="slide swiper-slide">
            <?php } ?>
            <div class="product-card">
               <a href="<?= esc_url($permalink); ?>" class="product-card__link" aria-label="<?= esc_attr($product_title); ?>"></a>

               <div class="product-card__image">
                  <div class="product-card__image-inner">
                     <?php
                     // --- Image with parent fallback (useful for variations) ---
                     $thumbnail_id = get_post_thumbnail_id($product_id);
                     if (!$thumbnail_id && $is_variation && $parent) {
                        $thumbnail_id = get_post_thumbnail_id($parent->get_id());
                     }

                     if ($thumbnail_id) {
                        echo wp_get_attachment_image($thumbnail_id, 'full', false, []);
                     } else {
                        // your SVG placeholder unchanged...
                     ?>
                        <svg width="300" height="300" viewBox="0 0 300 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                           <g clip-path="url(#clip0_2748_2)">
                              <mask id="mask0_2748_2" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="300" height="400">
                                 <path d="M300 0H0V400H300V0Z" fill="white" />
                              </mask>
                              <g mask="url(#mask0_2748_2)">
                                 <mask id="mask1_2748_2" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="300" height="400">
                                    <path d="M300 0H0V400H300V0Z" fill="white" />
                                 </mask>
                                 <g mask="url(#mask1_2748_2)">
                                    <path d="M300 0H0V400H300V0Z" fill="transparent" />
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M83 123.171C83.0255 119.225 85.9852 116.031 89.6464 116H210.354C214.025 116 217 119.214 217 123.171V238.829C216.974 242.774 214.015 245.969 210.354 246H89.6464C85.975 245.997 83 242.786 83 238.829V123.171ZM203.601 130.444H96.4011V231.556L158.657 164.432C161.273 161.614 165.515 161.614 168.131 164.432L203.601 202.739V130.444ZM109.799 159.333C109.799 167.31 115.798 173.778 123.199 173.778C130.599 173.778 136.599 167.31 136.599 159.333C136.599 151.356 130.599 144.889 123.199 144.889C115.798 144.889 109.799 151.356 109.799 159.333Z" fill="black" fill-opacity="0.6" />
                                 </g>
                              </g>
                           </g>
                           <defs>
                              <clipPath id="clip0_2748_2">
                                 <rect width="300" height="400" fill="white" />
                              </clipPath>
                           </defs>
                        </svg>

                     <?php } ?>
                  </div>

                  <div class="product-card__content">
                     <div class="product-card__info">
                        <span class="product-card__title"><?= $product_title; ?></span>
                        <span class="product-card__price"><?= wc_price($product_price_html); ?></span>
                     </div>
                     <?php if ($product->is_purchasable() && $product->is_in_stock()) { ?>
                        <div class="hapi-atc-wrap">
                           <button
                              type="button"
                              class="product-card__add-to-cart"
                              aria-label="<?php echo esc_attr__('Add to cart', 'demo'); ?>"
                              data-product-id="<?= esc_attr($product_id); ?>"
                              data-disabled="false">
                              <?php echo esc_html__('Add to cart', 'demo'); ?>
                              <svg class="cart-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 122.881 122.88" width="16" height="16">
                                 <path d="M56.573,4.868c0-0.655,0.132-1.283,0.37-1.859c0.249-0.6,0.61-1.137,1.056-1.583C58.879,0.545,60.097,0,61.44,0 c0.658,0,1.287,0.132,1.863,0.371c0.012,0.005,0.023,0.011,0.037,0.017c0.584,0.248,1.107,0.603,1.543,1.039 c0.881,0.88,1.426,2.098,1.426,3.442c0,0.03-0.002,0.06-0.006,0.089v51.62l51.619,0c0.029-0.003,0.061-0.006,0.09-0.006 c0.656,0,1.285,0.132,1.861,0.371c0.014,0.005,0.025,0.011,0.037,0.017c0.584,0.248,1.107,0.603,1.543,1.039 c0.881,0.88,1.428,2.098,1.428,3.441c0,0.654-0.133,1.283-0.371,1.859c-0.248,0.6-0.609,1.137-1.057,1.583 c-0.445,0.445-0.98,0.806-1.58,1.055v0.001c-0.576,0.238-1.205,0.37-1.861,0.37c-0.029,0-0.061-0.002-0.09-0.006l-51.619,0.001 v51.619c0.004,0.029,0.006,0.06,0.006,0.09c0,0.656-0.133,1.286-0.371,1.861c-0.006,0.014-0.012,0.025-0.018,0.037 c-0.248,0.584-0.602,1.107-1.037,1.543c-0.883,0.882-2.1,1.427-3.443,1.427c-0.654,0-1.283-0.132-1.859-0.371 c-0.6-0.248-1.137-0.609-1.583-1.056c-0.445-0.444-0.806-0.98-1.055-1.58h-0.001c-0.239-0.575-0.371-1.205-0.371-1.861 c0-0.03,0.002-0.061,0.006-0.09V66.303H4.958c-0.029,0.004-0.059,0.006-0.09,0.006c-0.654,0-1.283-0.132-1.859-0.371 c-0.6-0.248-1.137-0.609-1.583-1.056c-0.445-0.445-0.806-0.98-1.055-1.58H0.371C0.132,62.726,0,62.097,0,61.44 c0-0.655,0.132-1.283,0.371-1.859c0.249-0.6,0.61-1.137,1.056-1.583c0.881-0.881,2.098-1.426,3.442-1.426 c0.031,0,0.061,0.002,0.09,0.006l51.62,0l0-51.62C56.575,4.928,56.573,4.898,56.573,4.868L56.573,4.868z" />
                              </svg>
                              <!-- <div class="hapi-atc-notice" aria-live="polite" aria-atomic="true"></div> -->
                           </button>
                           <div class="hapi-status" aria-live="polite" aria-atomic="true"></div>
                           <div class="added_to_cart"></div>
                        </div>
                     <?php } else { ?>
                        <button
                           type="button"
                           class="product-card__add-to-cart wp-block-button__link wp-element-button is-disabled out-of-stock"
                           disabled
                           aria-label="<?php echo esc_attr__('Out of stock', 'demo'); ?>">
                           <?php echo esc_html__('Out of stock', 'demo'); ?>
                        </button>
                     <?php } ?>
                  </div>
               </div>
            </div>
            <?php if (!$is_grid) { ?>
            </div>
         <?php } ?>
<?php
      endwhile;
   }
}
$available_taxonomies = ['needs', 'foci', 'product_cat'];
$taxonomy_data = [];
// Get the rewrite slug for each taxonomy
foreach ($available_taxonomies as $taxonomy) {
   $taxonomy_obj = get_taxonomy($taxonomy);
   if ($taxonomy_obj) {
      $taxonomy_data[$taxonomy] = $taxonomy_obj;
   }
}

// Helper for taxonomy intro in grid mode
if (!function_exists('fw_get_selected_term')) {
   function fw_get_selected_term($selected_category, $selected_focus, $selected_need) {
      if ($selected_need)        return get_term_by('slug', $selected_need, 'needs');
      if ($selected_focus)       return get_term_by('slug', $selected_focus, 'foci');
      if ($selected_category)    return get_term_by('slug', $selected_category, 'product_cat');
      return null;
   }
}
$selected_term = $is_grid ? fw_get_selected_term($selected_category, $selected_focus, $selected_need) : null;

$root_classes = trim(($attributes['mainClassName'] ?? 'filtered-products-section') . ' ' . ($is_grid ? 'is-grid' : 'is-slider'));
?>
<div id="<?= esc_attr($attributes['blockId']); ?>"
   class="<?= esc_attr($root_classes); ?>"
   data-posts-per-view-desktop="<?= esc_attr($attributes['postsPerViewDesktop']); ?>"
   data-posts-per-view-mobile="<?= esc_attr($attributes['postsPerViewMobile']); ?>"
   data-is-grid="<?= $is_grid ? '1' : '0'; ?>"
   data-webshop-url="<?= esc_url(wc_get_page_permalink('shop')); ?>">

   <?php if (0) { ?>
      <!-- Backdrop for off-canvas (hidden on desktop by CSS) -->
      <div class="product-filter-backdrop" hidden></div>
      <aside id="product-filter-drawer" class="product-filter-container" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="product-filter-title">
         <div class="filter-panel">
            <h2 id="product-filter-title" class="product-filter-title"><?= esc_html__('filter after', 'demo'); ?></h2>
            <div class="filter-header" role="tablist" aria-label="<?= esc_attr__('Filter after', 'demo'); ?>">
               <button <?= tabAttrs('needs', $active_taxonomy, __("Needs", "demo")); ?>><?= __("Needs", "demo"); ?></button>
               <div class="filter-header__button-separator" aria-hidden="true">/</div>
               <button <?= tabAttrs('foci', $active_taxonomy, __("Focus", "demo")); ?>><?= __("Focus", "demo"); ?></button>
               <div class="filter-header__button-separator" aria-hidden="true">/</div>
               <button <?= tabAttrs('product_cat', $active_taxonomy, __("Assortment", "demo")); ?>><?= __("Assortment", "demo"); ?></button>
            </div>

            <div class="filter-body">
               <div <?= panelAttrs('needs', $active_taxonomy); ?>>
                  <?php foreach ($needs as $need): $is_selected = $selected_need === $need->slug ? 'selected' : '';

                     $pressed = $is_selected ? 'true' : 'false'; ?>
                     <button class="filter-option <?= $is_selected; ?>" data-taxonomy="needs" data-term="<?= esc_attr($need->slug); ?>" data-rewrite="<?= $taxonomy_data['needs']->rewrite['slug']; ?>" aria-pressed="<?= $pressed; ?>">
                        <div class="filter-bullet" aria-hidden="true"></div><?= esc_html($need->name); ?>
                     </button>
                  <?php endforeach; ?>
               </div>

               <div <?= panelAttrs('foci', $active_taxonomy); ?>>
                  <?php foreach ($foci as $focus): $is_selected = $selected_focus === $focus->slug ? 'selected' : '';
                     $pressed = $is_selected ? 'true' : 'false'; ?>
                     <button class="filter-option <?= $is_selected; ?>" data-taxonomy="foci" data-term="<?= esc_attr($focus->slug); ?>" data-rewrite="<?= $taxonomy_data['foci']->rewrite['slug']; ?>" aria-pressed="<?= $pressed; ?>">
                        <div class="filter-bullet" aria-hidden="true"></div><?= esc_html($focus->name); ?>
                     </button>
                  <?php endforeach; ?>
               </div>

               <div <?= panelAttrs('product_cat', $active_taxonomy); ?>>
                  <?php foreach ($categories as $category): $is_selected = $selected_category === $category->slug ? 'selected' : '';
                     $pressed = $is_selected ? 'true' : 'false'; ?>
                     <button class="filter-option <?= $is_selected; ?>" data-taxonomy="product_cat" data-term="<?= esc_attr($category->slug); ?>" data-rewrite="<?= $taxonomy_data['product_cat']->rewrite['slug']; ?>" aria-pressed="<?= $pressed; ?>">
                        <div class="filter-bullet" aria-hidden="true"></div><?= esc_html($category->name); ?>
                     </button>
                  <?php endforeach; ?>
               </div>
            </div>
         </div>
         <div class="filter-footer">
            <button class="product-filter-close wp-block-button__link wp-element-button" aria-label="<?= esc_attr__('Close filter', 'demo'); ?>">Filter</button>
         </div>
      </aside>
      <div class="swiper-products-divider"></div>
   <?php } ?>

   <div class="swiper-products-container">
      <?php if (0) { ?>
         <button class="product-filter-open wp-block-button__link wp-element-button"><?= __("Filter", "demo"); ?></button>
      <?php } ?>

      <?php if ($is_grid) {
         get_template_part('partials/ajax', 'taxonomy-intro');
      } ?>
      <?php get_template_part('partials/ajax', 'product-card'); ?>

      <?php if ($is_grid) { ?>
         <div class="products-grid ajax-products" role="list">
            <?php if ($selected_term && $selected_term->description) { ?>
               <section class="taxonomy-intro" aria-live="polite">
                  <h3 class="taxonomy-intro__title"><?= $selected_term->name; ?></h3>
                  <div class="taxonomy-intro__desc"><?= $selected_term->description; ?></div>
               </section>
            <?php } ?>
            <?php render_product_cards($product_data, $is_grid); ?>
         </div>
      <?php } else { ?>
         <div class="swiper-products ajax-products">
            <div class="swiper-wrapper">
               <?php render_product_cards($product_data, $is_grid); ?>
            </div>
            <div class="swiper-controls">
               <div class="swiper-scrollbar"></div>
               <div class="swiper-buttons">
                  <div class="swiper-button-prev">
                     <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="64" height="64" fill="#000" />
                        <path d="M29.2239 22L39.1289 31.905L29.2239 41.81L27.9989 40.5675L36.6614 31.905L27.9989 23.2425L29.2239 22Z" fill="white" />
                     </svg>
                  </div>
                  <div class="swiper-button-next">
                     <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="64" height="64" fill="#000" />
                        <path d="M29.2239 22L39.1289 31.905L29.2239 41.81L27.9989 40.5675L36.6614 31.905L27.9989 23.2425L29.2239 22Z" fill="white" />
                     </svg>
                  </div>
               </div>
            </div>
         </div>
      <?php } ?>
   </div>
</div>
