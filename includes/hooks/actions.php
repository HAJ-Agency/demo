<?php

namespace Demo\Includes\Hooks;

/**
 * Enqueue styles and scripts.
 *
 * @see https://developers.google.com/speed/libraries#jquery
 * @since 1.0.0
 */
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\demo_enqueue_assets', 1);

function demo_enqueue_assets() {

   wp_enqueue_style(
      'global',
      get_stylesheet_directory_uri() . '/build/assets/css/critical/global.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/critical/global.css')
   );
   wp_enqueue_style(
      'header',
      get_stylesheet_directory_uri() . '/build/assets/css/header.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/header.css')
   );
   wp_enqueue_style(
      'marquee',
      get_stylesheet_directory_uri() . '/build/assets/css/marquee.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/marquee.css')
   );
   wp_enqueue_style(
      'mini-shopping-cart',
      get_stylesheet_directory_uri() . '/build/assets/css/mini-shopping-cart.ext.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/mini-shopping-cart.ext.css')
   );
   wp_enqueue_style(
      'button',
      get_stylesheet_directory_uri() . '/build/assets/css/button.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/button.css')
   );
   wp_enqueue_style(
      'checkout',
      get_stylesheet_directory_uri() . '/build/assets/css/checkout.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/checkout.css')
   );
   wp_enqueue_style(
      'formidable-custom',
      get_stylesheet_directory_uri() . '/build/assets/css/formidable.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/formidable.css')
   );
   wp_enqueue_style(
      'pum-popup-custom',
      get_stylesheet_directory_uri() . '/build/assets/css/pum-popup.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/pum-popup.css')
   );
   wp_enqueue_style(
      'wc-product-image-gallery-ext',
      get_stylesheet_directory_uri() . '/build/assets/css/wc.product-image-gallery.ext.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/wc.product-image-gallery.ext.css')
   );
   wp_enqueue_style(
      'wc-product',
      get_stylesheet_directory_uri() . '/build/assets/css/wc.product.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/wc.product.css')
   );
   wp_enqueue_style(
      'progress-bar',
      get_stylesheet_directory_uri() . '/build/assets/css/progress-bar.ext.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/progress-bar.ext.css')
   );
   wp_enqueue_script(
      'progress-bar',
      get_stylesheet_directory_uri() . '/build/assets/js/progress-bar.ext.js',
      ['wp-blocks'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/progress-bar.ext.js'),
      true
   );

   wp_enqueue_script(
      'jquery',
      'https://code.jquery.com/jquery-3.7.1.min.js',
      [],
      '1.0',
      true
   );
   wp_enqueue_script(
      'formidable-ext',
      get_stylesheet_directory_uri() . '/build/assets/js/formidable.ext.js',
      ['jquery'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/formidable.ext.js'),
      true
   );

   wp_localize_script('formidable-ext', 'links', ['privacyPolicyUrl' => get_privacy_policy_url()]);

   wp_enqueue_script(
      'global',
      get_stylesheet_directory_uri() . '/build/assets/js/global.js',
      ['jquery'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/global.js'),
      array(
         'strategy'  => 'defer',
         'in_footer' => true,
      )
   );
   wp_enqueue_script(
      'header',
      get_stylesheet_directory_uri() . '/build/assets/js/header.js',
      ['jquery'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/header.js'),
      array(
         'strategy'  => 'defer',
         'in_footer' => true,
      )
   );
   wp_enqueue_script(
      'wc-product-image-gallery-ext',
      get_stylesheet_directory_uri() . '/build/assets/js/wc.product-image-gallery.ext.js',
      ['jquery'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/wc.product-image-gallery.ext.js'),
      array(
         'strategy'  => 'defer',
         'in_footer' => true,
      )
   );
   wp_enqueue_script(
      'wc-product',
      get_stylesheet_directory_uri() . '/build/assets/js/wc.product.js',
      ['jquery'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/wc.product.js'),
      array(
         'strategy'  => 'defer',
         'in_footer' => true,
      )
   );

   wp_enqueue_script(
      'wc-gtm-eec',
      get_stylesheet_directory_uri() . '/build/assets/js/wc.gtm-eec.js',
      [], // no deps; it just listens for our event
      filemtime(get_stylesheet_directory() . '/build/assets/js/wc.gtm-eec.js'),
      true
   );

   // wp_enqueue_script(
   //    'formidable-terms-link',
   //    get_stylesheet_directory_uri() . '/build/assets/js/formidable-terms-link.js',
   //    ['jquery'],
   //    filemtime(get_stylesheet_directory() . '/build/assets/js/formidable-terms-link.js'),
   //    true
   // );
}


/**
 * Enqueue admin styles and scripts.
 *
 * @since 1.0.0
 */
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\demo_enqueue_admin_assets', 10);

function demo_enqueue_admin_assets() {
   wp_enqueue_script(
      'demo-remove-default-btn',
      get_stylesheet_directory_uri() . '/build/assets/js/remove-default-btn.js',
      ['wp-blocks'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/remove-default-btn.js'),
      true
   );
   wp_enqueue_style(
      'tiburon-wp-admin',
      get_stylesheet_directory_uri() . '/build/assets/css/admin/wp-admin.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/admin/wp-admin.css')
   );
   wp_enqueue_script(
      'remove-default-btn',
      get_stylesheet_directory_uri() . '/build/assets/js/remove-default-btn.js',
      ['wp-blocks'],
      filemtime(get_stylesheet_directory() . '/build/assets/js/remove-default-btn.js'),
      true
   );
   wp_enqueue_style(
      'tiburon-editor-admin',
      get_stylesheet_directory_uri() . '/build/assets/css/admin/editor.admin.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/admin/editor.admin.css')
   );
   wp_enqueue_style(
      'button',
      get_stylesheet_directory_uri() . '/build/assets/css/button.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/button.css')
   );
}

add_action('enqueue_block_assets', __NAMESPACE__ . '\extend_block_assets');
function extend_block_assets() {
   $is_gutenberg_editor = is_admin() && get_current_screen()->is_block_editor();

   wp_enqueue_style(
      'image-block-ext-style',
      get_stylesheet_directory_uri() . '/build/assets/css/image-block.ext.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/image-block.ext.css')
   );
   if ($is_gutenberg_editor) {
      // EXTEND script
      wp_enqueue_script(
         'image-block-ext-js',
         get_stylesheet_directory_uri() . '/build/assets/js/image-block.ext.js',
         ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'],
         filemtime(get_stylesheet_directory() . '/build/assets/js/image-block.ext.js')
      );
      wp_enqueue_script(
         'demo-remove-default-btn',
         get_stylesheet_directory_uri() . '/build/assets/js/remove-default-btn.js',
         ['wp-blocks'],
         filemtime(get_stylesheet_directory() . '/build/assets/js/remove-default-btn.js'),
         true
      );
      wp_enqueue_script(
         'remove-default-btn',
         get_stylesheet_directory_uri() . '/build/assets/js/remove-default-btn.js',
         ['wp-blocks'],
         filemtime(get_stylesheet_directory() . '/build/assets/js/remove-default-btn.js'),
         true
      );
      wp_enqueue_style(
         'tiburon-editor-admin',
         get_stylesheet_directory_uri() . '/build/assets/css/admin/editor.admin.css',
         [],
         filemtime(get_stylesheet_directory() . '/build/assets/css/admin/editor.admin.css')
      );
      wp_enqueue_style(
         'header-css',
         get_stylesheet_directory_uri() . '/build/assets/css/header.css',
         [],
         filemtime(get_stylesheet_directory() . '/build/assets/css/header.css')
      );
      wp_enqueue_style(
         'button',
         get_stylesheet_directory_uri() . '/build/assets/css/button.css',
         [],
         filemtime(get_stylesheet_directory() . '/build/assets/css/button.css')
      );
   }

   wp_enqueue_style(
      'group-block-ext-style',
      get_stylesheet_directory_uri() . '/build/assets/css/group-block.ext.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/group-block.ext.css')
   );
   if ($is_gutenberg_editor) {
      // EXTEND script
      wp_enqueue_script(
         'group-block-ext-js',
         get_stylesheet_directory_uri() . '/build/assets/js/group-block.ext.js',
         ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'],
         filemtime(get_stylesheet_directory() . '/build/assets/js/group-block.ext.js')
      );
   }

   wp_enqueue_style(
      'column-block-ext-style',
      get_stylesheet_directory_uri() . '/build/assets/css/column-block.ext.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/column-block.ext.css')
   );
   if ($is_gutenberg_editor) {
      // EXTEND script
      wp_enqueue_script(
         'column-block-ext-js',
         get_stylesheet_directory_uri() . '/build/assets/js/column-block.ext.js',
         ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'],
         filemtime(get_stylesheet_directory() . '/build/assets/js/column-block.ext.js')
      );
   }

   wp_enqueue_style(
      'columns-block-ext-style',
      get_stylesheet_directory_uri() . '/build/assets/css/columns-block.ext.css',
      [],
      filemtime(get_stylesheet_directory() . '/build/assets/css/columns-block.ext.css')
   );
   if ($is_gutenberg_editor) {
      // EXTEND script
      wp_enqueue_script(
         'columns-block-ext-js',
         get_stylesheet_directory_uri() . '/build/assets/js/columns-block.ext.js',
         ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'],
         filemtime(get_stylesheet_directory() . '/build/assets/js/columns-block.ext.js')
      );
   }
}

add_action('enqueue_block_assets', function () {
   // Change to your actual block name.
   $block = \WP_Block_Type_Registry::get_instance()->get_registered('tiburon/add-to-cart');
   if (!$block) {
      return;
   }

   // Data you want in JS.
   $data = [
      // Use a **relative** path to avoid host/scheme mismatches.
      'restPath' => wp_parse_url(rest_url('hapi/v1/products/add_to_cart'), PHP_URL_PATH),
      'nonce'    => wp_create_nonce('wp_rest'),
   ];

   foreach ((array) $block->view_script_handles as $handle) {
      // If your block uses "viewScript": "file:./view.js"
      if (wp_script_is($handle, 'registered')) {
         // For classic scripts:
         wp_localize_script($handle, 'HAPI_ATC', $data);

         // For ESM `"viewScriptModule"` you can't localize; inline BEFORE instead:
         wp_add_inline_script(
            $handle,
            'window.HAPI_ATC = ' . wp_json_encode($data) . ';',
            'before'
         );
      }
   }
});
add_action('enqueue_block_assets', function () {
   // Change to your actual block name.
   $block = \WP_Block_Type_Registry::get_instance()->get_registered('tiburon/filtered-product-section');
   if (!$block) {
      return;
   }

   // Data you want in JS.
   $data = [
      // Use a **relative** path to avoid host/scheme mismatches.
      'restUrl' => rest_url('hapi/v1/products/add_to_cart'),
      'restPath' => wp_parse_url(rest_url('hapi/v1/products/add_to_cart'), PHP_URL_PATH),
      'nonce'    => wp_create_nonce('wp_rest'),
   ];

   foreach ((array) $block->view_script_handles as $handle) {
      // If your block uses "viewScript": "file:./view.js"
      if (wp_script_is($handle, 'registered')) {
         // For classic scripts:
         wp_localize_script($handle, 'HAPI_ATC_FPS', $data);

         // For ESM `"viewScriptModule"` you can't localize; inline BEFORE instead:
         wp_add_inline_script(
            $handle,
            'window.HAPI_ATC_FPS = ' . wp_json_encode($data) . ';',
            'before'
         );
      }
   }
});

/**
 * Wordpress Init action
 */
add_action('init', __NAMESPACE__ . '\demo_theme_init');
function demo_theme_init() {
   // Register custom blocks (A - Z)
   register_block_type(get_stylesheet_directory() . '/build/blocks/accordion');
   register_block_type(get_stylesheet_directory() . '/build/blocks/add-to-cart');
   register_block_type(get_stylesheet_directory() . '/build/blocks/filtered-products-section');
   register_block_type(get_stylesheet_directory() . '/build/blocks/ingredients-archive');
   register_block_type(get_stylesheet_directory() . '/build/blocks/off-canvas-shopping-cart');
   register_block_type(get_stylesheet_directory() . '/build/blocks/post-archive');
   register_block_type(get_stylesheet_directory() . '/build/blocks/reseller');
   register_block_type(get_stylesheet_directory() . '/build/blocks/reseller-search-bar');
   register_block_type(get_stylesheet_directory() . '/build/blocks/scrollbar-slider');
   register_block_type(get_stylesheet_directory() . '/build/blocks/search');
   register_block_type(get_stylesheet_directory() . '/build/blocks/slider');
   register_block_type(get_stylesheet_directory() . '/build/blocks/slider-item');
   register_block_type(get_stylesheet_directory() . '/build/blocks/timeline');
}


add_action('init', __NAMESPACE__ . '\rename_default_post_type', 100);
function rename_default_post_type() {
   global $wp_post_types;

   if (isset($wp_post_types['post'])) {
      $wp_post_types['post']->labels->name = 'Articles';
      $wp_post_types['post']->labels->singular_name = 'Article';
      $wp_post_types['post']->labels->add_new = 'Lägg till ny Article';
      $wp_post_types['post']->labels->add_new_item = 'Lägg till ny Article';
      $wp_post_types['post']->labels->edit_item = 'Redigera Article';
      $wp_post_types['post']->labels->new_item = 'Ny Article';
      $wp_post_types['post']->labels->view_item = 'Visa Article';
      $wp_post_types['post']->labels->search_items = 'Sök Article';
      $wp_post_types['post']->labels->not_found = 'Ingen Article hittades';
      $wp_post_types['post']->labels->not_found_in_trash = 'Ingen Article i papperskorgen';
      $wp_post_types['post']->labels->all_items = 'All Article';
      $wp_post_types['post']->labels->menu_name = 'Article';
      $wp_post_types['post']->labels->name_admin_bar = 'Article';
   }
}

add_action('init', __NAMESPACE__ . '\remove_comment_support', 100);
function remove_comment_support() {
   remove_post_type_support('post', 'comments');
   remove_post_type_support('page', 'comments');
}
add_filter('comments_open',  '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);
add_action('admin_menu', __NAMESPACE__ . '\remove_comments_admin_menu');
function remove_comments_admin_menu() {
   remove_menu_page('edit-comments.php');
}
add_action('admin_bar_menu', __NAMESPACE__ . '\remove_comments_admin_bar', 999);
function remove_comments_admin_bar($wp_admin_bar) {
   $wp_admin_bar->remove_node('comments');
}

// Disable all core block patterns
remove_action('init', 'register_core_block_patterns');
remove_action('init', 'register_core_block_pattern_categories');

// Allow SVG
add_action('admin_head', __NAMESPACE__ . '\fix_svg');
function fix_svg() {
   echo '<style type="text/css">
          .attachment-266x266, .thumbnail img {
               width: 100% !important;
               height: auto !important;
          }
          </style>';
}

/* Push purchase event to dataLayer for Meta tracking */
add_action('woocommerce_thankyou', function ($order_id) {
   if (! $order_id) return;

   $order = wc_get_order($order_id);
   if (! $order) return;

   // Build ecommerce items array
   $items = [];
   foreach ($order->get_items() as $item) {
      $product = $item->get_product();
      if (! $product) continue;

      $items[] = [
         'item_id'      => $product->get_sku() ?: $product->get_id(),
         'item_name'    => $product->get_name(),
         'price'        => (float) $product->get_price(),
         'quantity'     => (int) $item->get_quantity(),
      ];
   }

   // Build ecommerce payload
   $payload = [
      'event' => 'purchase',
      'ecommerce' => [
         'transaction_id' => $order->get_order_number(),
         'value'          => (float) $order->get_total(),
         'currency'       => $order->get_currency(),
         'items'          => $items,
      ]
   ];

   // Output script on thank you page
   echo "<script>window.dataLayer = window.dataLayer || []; window.dataLayer.push(" . wp_json_encode($payload) . ");</script>";
});
