<?php

namespace Akka\Includes\Api;

use function Akka\Includes\Functions\get_post_type_content;
use function Akka\Includes\Functions\get_post_type_items;
use function Akka\Includes\Functions\get_product_data;

add_action('rest_api_init',  __NAMESPACE__ . '\hapi_search_page_endpoint');
function hapi_search_page_endpoint() {
   register_rest_route('hapi/v1', '/search-page/', array(
      'methods'  => 'GET',
      'callback' => __NAMESPACE__ . '\hapi_search_page',
      'permission_callback' => '__return_true',
   ));
}

function hapi_search_page($request) {
   $search_value = $request->get_param('search_value');
   $offset = intval($request->get_param('offset')) ?: 0;
   $posts_per_page = intval($request->get_param('posts_per_page')) ?: 8;
   // TODO Include products and other post types. Don't include resellers
   $args = array(
      's' => $search_value,
      'post_type' => array('page'),
      'posts_per_page' => $posts_per_page,
      'relevanssi' => true,
      'offset' =>  $offset,
   );
   $query = new \WP_Query($args);
   $posts = $query->posts;
   $found_posts = $query->found_posts;
   $response = array();
   foreach ($posts as $post) {
      $response[] = array(
         'id' => $post->ID,
         'title' => $post->post_title,
         'link' => get_permalink($post),
         'excerpt' => $post->post_excerpt,
         'image' => wp_get_attachment_image(get_post_thumbnail_id($post->ID), 'full') ? wp_get_attachment_image(get_post_thumbnail_id($post->ID), 'full') : '<svg width="400" height="300" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_346_2327)"><mask id="mask0_346_2327" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="400" height="300"><path d="M400 0H0V300H400V0Z" fill="white"/></mask><g mask="url(#mask0_346_2327)"><path d="M400 0H0V300H400V0Z" fill="#EFF1F3"/><path fill-rule="evenodd" clip-rule="evenodd" d="M134.583 97.2664C134.608 93.6806 137.506 90.7785 141.091 90.75H259.284C262.879 90.75 265.792 93.6701 265.792 97.2664V202.359C265.767 205.944 262.869 208.847 259.284 208.875H141.091C137.496 208.872 134.583 205.955 134.583 202.359V97.2664ZM252.672 103.875H147.705V195.75L208.664 134.758C211.226 132.197 215.379 132.197 217.941 134.758L252.672 169.566V103.875ZM160.824 130.125C160.824 137.373 166.698 143.25 173.945 143.25C181.191 143.25 187.066 137.373 187.066 130.125C187.066 122.876 181.191 117 173.945 117C166.698 117 160.824 122.876 160.824 130.125Z" fill="#687787"/></g></g><defs><clipPath id="clip0_346_2327"><rect width="400" height="300" fill="white"/></clipPath></defs></svg>',
      );
   }
   return rest_ensure_response(array(
      'posts' => $response,
      'found_posts' => $found_posts,
      'has_more' => $offset < $found_posts,
   ));
}

add_action('rest_api_init',  __NAMESPACE__ . '\hapi_get_post_type_content_endpoint');
function hapi_get_post_type_content_endpoint() {
   register_rest_route('hapi/v1', '/get-post-type-content/', array(
      'methods'  => 'GET',
      'callback' => __NAMESPACE__ . '\hapi_get_post_type_content',
      'permission_callback' => '__return_true',
   ));
}

// Register custom REST API endpoint
add_action('rest_api_init', __NAMESPACE__ . '\hapi_get_post_types_endpoint');
function hapi_get_post_types_endpoint() {
   register_rest_route('hapi/v1', '/post-types', array(
      'methods'  => 'GET',
      'callback' => __NAMESPACE__ . '\hapi_get_post_types',
      'permission_callback' => '__return_true',
   ));
}

// Callback function to retrieve post types
function hapi_get_post_types($request) {
   $post_types = get_post_types(array('public' => true), 'objects');

   $formatted_post_types = array();

   foreach ($post_types as $post_type) {
      $formatted_post_types[] = array(
         'label' => $post_type->label,
         'value' => $post_type->name,
      );
   }

   return rest_ensure_response($formatted_post_types);
}

function hapi_get_post_type_content($request) {
   $args = [
      'post_type' => $request->get_param('post_type'),
      'posts_per_page' => intval($request->get_param('posts_per_page')) ?: 8,
      'offset' => intval($request->get_param('offset')) ?: 0,
      'category_slug' => $request->get_param('category_slug') ?: '',
      'focus_slug' => $request->get_param('focus_slug') ?: '',
   ];
   return rest_ensure_response(get_post_type_content($args));
}


add_action('rest_api_init',  __NAMESPACE__ . '\hapi_get_post_type_items_endpoint');
function hapi_get_post_type_items_endpoint() {
   register_rest_route('hapi/v1', '/(?P<post_type>[a-zA-Z0-9-]+)', array(
      'methods'  => 'POST',
      'callback' => __NAMESPACE__ . '\hapi_get_post_type_items',
      'permission_callback' => '__return_true',
   ));
}

function hapi_get_post_type_items($request) {
   $post_type = $request->get_param('post_type');
   $posts_per_page = $request->get_param('limit');
   $tax_query_data = $request->get_param('taxData');

   $post_data = get_post_type_items($post_type, $posts_per_page, $tax_query_data);

   return rest_ensure_response($post_data);
}

add_action('rest_api_init',  __NAMESPACE__ . '\hapi_get_post_type_taxonomies_endpoint');
function hapi_get_post_type_taxonomies_endpoint() {

   register_rest_route('hapi/v1', '/tax/(?P<post_type>[a-zA-Z0-9-]+)', array(
      'methods'  => 'GET',
      'callback' => __NAMESPACE__ . '\hapi_get_post_type_taxonomies',
      'permission_callback' => '__return_true',
   ));
}

function hapi_get_post_type_taxonomies($request) {
   $post_type = $request->get_param('post_type');

   // get taxonomies for post type
   $taxonomies = get_object_taxonomies($post_type, 'objects');

   // get all terms for each taxonomy
   $return_taxonomies = [];
   foreach ($taxonomies as $taxonomy) {
      $terms = get_terms(array(
         'taxonomy' => $taxonomy->name,
         'hide_empty' => false,
      ));
      foreach ($terms as $term) {
         $return_taxonomies[$taxonomy->name][] = array(
            'label' => $term->name,
            'value' => $term->slug,
            'selected' => false,
            'is_child' => $term->parent !== 0,
            'has_children' => (bool) get_term_children($term->term_id, $taxonomy->name),
         );
      }
   }

   return rest_ensure_response($return_taxonomies);
}

add_action('rest_api_init', function () {
   register_rest_route('hapi/v1', '/resellers', [
      'methods' => 'GET',
      'callback' => __NAMESPACE__ . '\hapi_get_resellers',
      'permission_callback' => '__return_true',
      'args' => [
         's' => [
            'description' => 'Search query',
            'type' => 'string',
            'required' => false,
         ],
      ],
   ]);
});

function hapi_get_resellers($request) {
   $query_args = [
      'post_type' => 'reseller',
      'posts_per_page' => -1,
   ];

   $search_term = '';
   if (!empty($request['s'])) {
      $search_term = sanitize_text_field($request['s']);
      $query_args['s'] = $search_term;
   }

   $query = new \WP_Query($query_args);

   if (function_exists('relevanssi_do_query')) {
      relevanssi_do_query($query);
   }

   $results = [];

   while ($query->have_posts()) {
      $query->the_post();
      $post_id = get_the_ID();
      $stores = get_post_meta($post_id, 'stores', true);

      if (!empty($stores) && is_array($stores)) {
         foreach ($stores as $store) {
            // If searching, filter individual store rows
            if ($search_term) {
               $haystack = implode(' ', [
                  $store['store_name'] ?? '',
                  $store['address'] ?? '',
                  $store['phone'] ?? '',
                  $store['email'] ?? '',
                  $store['contact_person'] ?? '',
                  $store['website'] ?? '',
                  $store['city_name'] ?? '',
               ]);

               if (stripos($haystack, $search_term) === false) {
                  continue; // Skip this store
               }
            }

            $city_name = $store['city_name'] ?? __('Okänd stad', 'akka');

            $results[$city_name][] = [
               'store_name' => $store['store_name'] ?? '',
               'address' => $store['address'] ?? '',
               'phone' => $store['phone'] ?? '',
               'email' => $store['email'] ?? '',
               'contact_person' => $store['contact_person'] ?? '',
               'website' => $store['website'] ?? '',
            ];
         }
      }
   }

   wp_reset_postdata();
   ksort($results);

   // New: Cache version (based on latest reseller post modified time)
   $latest_post = get_posts([
      'post_type' => 'reseller',
      'posts_per_page' => 1,
      'orderby' => 'modified',
      'order' => 'DESC',
      'fields' => 'ids',
   ]);

   $cache_version = $latest_post ? get_post_modified_time('U', true, $latest_post[0]) : time();

   return rest_ensure_response([
      'cache_version' => $cache_version,
      'data' => $results
   ]);
}

add_action('rest_api_init',  __NAMESPACE__ . '\hapi_get_product_data_endpoint');
function hapi_get_product_data_endpoint() {
   register_rest_route('hapi/v1', '/get-product-data/', array(
      'methods'  => 'POST',
      'callback' => __NAMESPACE__ . '\hapi_get_product_data',
      'permission_callback' => '__return_true',
   ));
}

function hapi_get_product_data($request) {
   $args = [
      'numberOfProducts' => intval($request->get_param('posts_per_page')) ?: 6,
      'offset' => intval($request->get_param('offset')) ?: 0,
      'category_slug' => $request->get_param('category_slug') ?: '',
   ];
   return rest_ensure_response(get_product_data($args));
}

add_action('rest_api_init',  __NAMESPACE__ . '\hapi_ajax_get_product_data_endpoint');
function hapi_ajax_get_product_data_endpoint() {
   register_rest_route('hapi/v1', '/ajax/get-product-data/', array(
      'methods'  => 'POST',
      'callback' => __NAMESPACE__ . '\hapi_ajax_get_product_data',
      'permission_callback' => '__return_true',
   ));
}

function hapi_ajax_get_product_data($request) {
   $args = [
      'posts_per_page' => intval($request->get_param('posts_per_page')) ?: 6,
      'offset' => intval($request->get_param('offset')) ?: 0,
      'productCategory' => $request->get_param('category_slug') ?: '',
      'productFocus' => $request->get_param('focus_slug') ?: '',
      'productNeed' => $request->get_param('need_slug') ?: '',
   ];

   $product_data_query = get_product_data($args);

   while ($product_data_query->have_posts()) {
      $product_data_query->the_post();
      $product_id = get_the_ID();
      $product = wc_get_product($product_id);
      $product_title = $product->get_name();
      $excerpt = $product->get_short_description();
      $description = $product->get_description();
      if (empty($excerpt)) {
         $excerpt = $description;
      }
      $permalink = $product->get_permalink();

      $thumbnail_id = get_post_thumbnail_id($product_id);
      $image = wp_get_attachment_image($thumbnail_id, 'full', false, []);
      $stock_available_quantity = $product->get_stock_quantity();
      $stock_status = $product->get_stock_status();
      $product_type = $product->get_type();
      if ($product && $product->is_type('variable')) {
         // Lowest active price across all variations (sale price if applicable)
         $min_price = $product->get_variation_price('min', true); // true => for display (tax-aware)
      } else {
         // Simple product (or anything else)
         $min_price = wc_get_price_to_display($product);
      }
      $product_price_html = wc_price($min_price);
      $price = $product->get_price();
      $sale_price = $product->get_sale_price();
      $add_to_cart_button = $product->is_purchasable() && $product->is_in_stock() && $product->get_type() === 'simple';

      $product_data[] = array(
         'id' => $product_id,
         'title' => $product_title,
         'excerpt' => wp_trim_words($excerpt, 20),
         'description' => $description,
         'link' => $permalink,
         'image' => $image,
         'stock_available_quantity' => $stock_available_quantity,
         'stock_status' => $stock_status,
         'product_type' => $product_type,
         'price_html' => $product_price_html,
         'price' => wc_price($price, ['thousand_separator' => '', 'decimals' => 0]),
         'sale_price' => wc_price($sale_price, ['thousand_separator' => '', 'decimals' => 0]),
         'add_to_cart_button' => $add_to_cart_button,
         // 'show_vat' => $show_vat,
      );
   }
   $response_data = ["productData" => $product_data];
   return rest_ensure_response($response_data);
}

add_action('rest_api_init', __NAMESPACE__ . '\hapi_add_to_cart_endpoint');
function hapi_add_to_cart_endpoint() {
   register_rest_route('hapi/v1', '/products/add_to_cart', array(
      'methods'  => 'POST',
      'callback' => __NAMESPACE__ . '\hapi_add_to_cart',
      'permission_callback' => '__return_true',
   ));
}

function hapi_add_to_cart(\WP_REST_Request $request) {
   // Make sure Woo's cart/session are loaded correctly for REST.
   if (function_exists('wc_load_cart')) {
      wc_load_cart();
   } else {
      // Manual fallback for older WC versions
      // if (null === WC()->session) {
      //    $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
      //    WC()->session = new $session_class();
      //    WC()->session->init();
      // }
      // if (null === WC()->cart) {
      //    WC()->cart = new \WC_Cart();
      //    WC()->cart->get_cart();
      // }
      return new \WP_REST_Response(
         ['success' => false, 'message' => 'WooCommerce version too old for this endpoint.'],
         500
      );
   }

   $product_id   = (int) $request->get_param('product_id');
   $variation_id = (int) $request->get_param('variation_id') ?: 0;
   $variation    = (array) ($request->get_param('variation') ?: []);
   $qty          = max(1, (int) $request->get_param('quantity')); // force >= 1

   if (!$product_id) {
      return new \WP_REST_Response(['success' => false, 'message' => 'No product ID'], 400);
   }

   $product = wc_get_product($variation_id ?: $product_id);
   if (!$product) {
      return new \WP_REST_Response(['success' => false, 'message' => 'Invalid product'], 400);
   }

   // Check if product exists in cart
   $product_in_cart = false;
   foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
      if ($cart_item['product_id'] === $product_id && $cart_item['variation_id'] === $variation_id) {
         $product_in_cart = true;
         break;
      }
   }


   if ($product_in_cart) {
      if ($product->is_sold_individually()) {
         // Respect "sold individually" — keep it at 1.
         return new \WP_REST_Response([
            'success'    => true,
            'message'    => 'Product already in cart (sold individually).',
            'cart_hash'  => WC()->cart->get_cart_hash(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'link'       => WC()->cart->get_cart_contents_count() > 0 ? wc_get_page_permalink('cart') : '/'
         ]);
      }

      $added_key = WC()->cart->add_to_cart($product_id, $qty, $variation_id, $variation);
      wc_clear_notices();

      if (!$added_key) {
         error_log('Failed to add product to cart: ' . $product_id);
         return new \WP_REST_Response(['success' => false, 'message' => 'Could not add to cart'], 500);
      }
   } else {
      // Fresh add; WC will also merge quantities on its own when not sold individually,
      // but we’ve handled the existing case above explicitly.
      $added_key = WC()->cart->add_to_cart($product_id, $qty, $variation_id, $variation);
      wc_clear_notices();

      if (!$added_key) {
         error_log('Failed to add product to cart: ' . $product_id);
         return new \WP_REST_Response(['success' => false, 'message' => 'Could not add to cart'], 500);
      }
   }

   // Persist session cookie for REST clients that aren’t the browser (if applicable).
   if (WC()->session) {
      WC()->session->save_data();
   }

   // // Make sure Woo sets cart cookies and browser accepts them
   // if (WC()->cart) {
   //    // WC 3.6+ helper sets both cookies when needed
   //    if (method_exists(WC()->cart, 'set_cart_cookies')) {
   //       WC()->cart->set_cart_cookies(true);
   //    }

   //    // Belt & suspenders: explicitly set cookies like Woo core does
   //    if (!headers_sent()) {
   //       wc_setcookie('woocommerce_items_in_cart', 1);
   //       wc_setcookie('woocommerce_cart_hash', WC()->cart->get_cart_hash());
   //       /**
   //        * Let extensions hook same moment as core.
   //        * @param bool $set
   //        */
   //       do_action('woocommerce_set_cart_cookies', true);
   //    }
   // }


   return new \WP_REST_Response([
      'success'    => true,
      'message'    => 'Added to cart',
      'cart_hash'  => WC()->cart->get_cart_hash(),
      'cart_count' => WC()->cart->get_cart_contents_count(),
      'link'       => WC()->cart->get_cart_contents_count() > 0 ? wc_get_page_permalink('cart') : '/',
      'product_id' => $product_id,
      'variation_id' => $variation_id,
      'quantity' => $qty,
      'cart_contents' => WC()->cart->get_cart(),
      'session' => WC()->session->get_session_data(),
      'product_in_cart' => $product_in_cart,
   ]);
}
