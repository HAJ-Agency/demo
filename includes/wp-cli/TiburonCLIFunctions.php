<?php

namespace Demo\Includes\WPCLI;

class TiburonCLIFunctions {
   public function __construct() {
   }

   public function build_or_patch_products_json($full_rebuild = false) {
      if (!function_exists('wc_get_product')) {
         if (defined('WP_CLI') && WP_CLI) {
            \WP_CLI::error('WooCommerce is not active.');
         }
         return;
      }

      $start_time = microtime(true);
      $json_file_path = WP_CONTENT_DIR . '/tiburon-products.json.gz';
      $json_raw_file_path = WP_CONTENT_DIR . '/tiburon-products.json';
      $last_updated = '1970-01-01 00:00:00';

      $existing_data = [
         'products' => [],
         'filters' => [
            'categories' => [],
            'attributes' => [],
            'tags' => []
         ],
         'last_updated' => $last_updated
      ];

      if (file_exists($json_file_path) && !$full_rebuild) {
         $decoded = gzdecode(file_get_contents($json_file_path));
         if ($decoded !== false) {
            $existing_data = json_decode($decoded, true);
            $last_updated = $existing_data['last_updated'] ?? $last_updated;
         }
      }

      if (defined('WP_CLI') && WP_CLI) {
         \WP_CLI::log(($full_rebuild ? 'Full rebuild' : 'Patch update') . ' started from: ' . $last_updated);
      }

      $args = [
         'post_type' => 'product',
         'posts_per_page' => -1,
         'post_status' => ['publish'],
         'orderby' => 'modified',
         'order' => 'ASC',
      ];

      if (!$full_rebuild) {
         $args['date_query'] = [
            [
               'column' => 'post_modified',
               'after' => $last_updated,
            ],
         ];
      }

      $query = new \WP_Query($args);
      $count = 0;
      $filters = [
         'categories' => [],
         'attributes' => [],
         'tags' => []
      ];

      $default_taxonomies = ['product_cat', 'product_tag'];

      $product_taxonomies = get_object_taxonomies('product', 'objects');

      $custom_taxonomies = [];
      foreach ($product_taxonomies as $key => $tax_obj) {
         // Exclude core Woo taxonomies and attributes (which are included elsewhere)
         if (in_array($key, $default_taxonomies) || $this->taxonomy_is_product_attribute($key)) {
            continue;
         }
         $custom_taxonomies[$key] = $tax_obj;
         $filters[$key] = []; // initialize filter collector
      }

      while ($query->have_posts()) {
         $query->the_post();
         $product = wc_get_product(get_the_ID());
         if (!$product) continue;

         $id = $product->get_id();

         $visibility = $product->get_catalog_visibility();
         if ($visibility !== 'visible') {
            if (defined('WP_CLI') && WP_CLI) {
               \WP_CLI::log("Skipped product ID $id due to visibility: $visibility");
            }
            continue;
         }

         $status = get_post_status($id);

         if ($status === 'trash') {
            unset($existing_data['products'][$id]);
            if (defined('WP_CLI') && WP_CLI) {
               \WP_CLI::log("Removed trashed product ID: $id");
            }
            continue;
         }

         $cats = wp_get_post_terms($id, 'product_cat', ['fields' => 'names']);
         $tags = wp_get_post_terms($id, 'product_tag', ['fields' => 'names']);
         $attrs = [];

         foreach ($product->get_attributes() as $attr) {
            $label = wc_attribute_label($attr->get_name());
            $terms = $attr->get_terms();
            if ($terms) {
               $term_data = [];

               foreach ($terms as $term) {
                  $term_item = [
                     'id'   => $term->term_id,
                     'name' => $term->name,
                     'slug' => $term->slug,
                     'taxonomy' => $term->taxonomy,
                  ];
                  $term_data[] = $term_item;

                  // Deduplicated attribute filter data
                  $filters['attributes'][$label][$term->term_id] = $term_item;
               }

               $attrs[$label] = $term_data;
            }
         }
         foreach (['product_cat', 'product_tag'] as $tax_key) {
            $terms = get_the_terms($id, $tax_key);
            if (is_array($terms)) {
               foreach ($terms as $term) {
                  $filters[$tax_key][$term->term_id] = [
                     'id' => $term->term_id,
                     'name' => $term->name,
                     'slug' => $term->slug,
                     'parent' => $term->parent,
                  ];
               }
            }
         }

         $price_incl = (float) wc_get_price_including_tax($product);
         $price_excl = (float) wc_get_price_excluding_tax($product);
         $price_html = $product->get_price_html();

         $product_data = [
            'id' => $id,
            'title' => $product->get_name(),
            'price' => [
               'html' => $price_html,
               'incl_tax' => [
                  'raw' => $price_incl,
                  'formatted' => wc_price($price_incl)
               ],
               'excl_tax' => [
                  'raw' => $price_excl,
                  'formatted' => wc_price($price_excl)
               ]
            ],
            'categories' => $cats,
            'attributes' => $attrs,
            'tags' => $tags,
            'modified' => get_post_modified_time('Y-m-d H:i:s', false, $id),
            'stock_status' => $product->get_stock_status(),
         ];

         $custom_tax_term_data = [];

         foreach ($custom_taxonomies as $tax_key => $tax_obj) {
            $terms = wp_get_post_terms($id, $tax_key, ['fields' => 'names']);
            if (!empty($terms)) {
               $custom_tax_term_data[$tax_key] = $terms;

               // Add to global filters
               $term_objects = get_terms([
                  'taxonomy' => $tax_key,
                  'object_ids' => [$id],
                  'hide_empty' => false,
               ]);

               $term_data = [];

               foreach ($term_objects as $term) {
                  $term_item = [
                     'id'     => $term->term_id,
                     'name'   => $term->name,
                     'slug'   => $term->slug,
                     'parent' => $term->parent,
                  ];

                  $term_data[] = $term_item;

                  // Add to filters (by slug for uniqueness, rekey later)
                  $filters[$tax_key][$term->term_id] = $term_item;
               }

               if (!empty($term_data)) {
                  $custom_tax_term_data[$tax_key] = $term_data;
               }
            }
         }

         if (!empty($custom_tax_term_data)) {
            $product_data['custom_taxonomies'] = $custom_tax_term_data;
         }

         $thumbnail_id = $product->get_image_id();
         $image_data = [
            'id' => $thumbnail_id,
            'url' => wp_get_attachment_image_url($thumbnail_id, 'full'),
            'html' => wp_get_attachment_image($thumbnail_id, 'woocommerce_thumbnail'),
            'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
            'title' => get_the_title($thumbnail_id),
         ];

         $product_data['image'] = $image_data;

         $gallery_ids = $product->get_gallery_image_ids();
         $gallery_images = [];

         foreach ($gallery_ids as $gallery_id) {
            $gallery_images[] = [
               'id' => $gallery_id,
               'url' => wp_get_attachment_image_url($gallery_id, 'full'),
               'html' => wp_get_attachment_image($gallery_id, 'woocommerce_thumbnail'),
               'alt' => get_post_meta($gallery_id, '_wp_attachment_image_alt', true),
               'title' => get_the_title($gallery_id),
            ];
         }

         $product_data['gallery'] = $gallery_images;

         // Add variations if applicable
         if ($product->is_type('variable')) {
            $variations = [];

            foreach ($product->get_children() as $variation_id) {
               $variation = wc_get_product($variation_id);
               if (!$variation || !$variation->exists()) continue;

               $variation_attrs = [];
               foreach ($variation->get_attributes() as $key => $value) {
                  $tax_label = wc_attribute_label(str_replace('attribute_', '', $key));
                  $variation_attrs[$tax_label] = $value;
               }

               $variation_image_id = $variation->get_image_id();
               $variation_image = [
                  'id' => $variation_image_id,
                  'url' => wp_get_attachment_image_url($variation_image_id, 'full'),
                  'html' => wp_get_attachment_image($variation_image_id, 'woocommerce_thumbnail'),
                  'alt' => get_post_meta($variation_image_id, '_wp_attachment_image_alt', true),
                  'title' => get_the_title($variation_image_id),
               ];

               $variations[$variation_id] = [
                  'id' => $variation_id,
                  'attributes' => $variation_attrs,
                  'price' => [
                     'incl_tax' => [
                        'raw' => (float) wc_get_price_including_tax($variation),
                        'formatted' => wc_price(wc_get_price_including_tax($variation))
                     ],
                     'excl_tax' => [
                        'raw' => (float) wc_get_price_excluding_tax($variation),
                        'formatted' => wc_price(wc_get_price_excluding_tax($variation))
                     ]
                  ],
                  'stock_status' => $variation->get_stock_status(),
                  'image' => $variation_image
               ];
            }

            $product_data['variations'] = $variations;
         }

         $existing_data['products'][$id] = $product_data;

         $count++;
      }
      wp_reset_postdata();

      // Deduplicate filters
      $existing_data['filters']['categories'] = array_values(array_unique(array_merge($existing_data['filters']['categories'], $filters['categories'])));
      $existing_data['filters']['tags'] = array_values(array_unique(array_merge($existing_data['filters']['tags'], $filters['tags'])));

      foreach ($filters as $taxonomy => $terms) {
         // Ensure values are indexed numerically and sorted consistently
         $existing_data['filters'][$taxonomy] = array_values($terms);
      }

      foreach ($filters['attributes'] as $label => $terms) {
         $existing_data['filters']['attributes'][$label] = array_values($terms);
      }

      $existing_data['last_updated'] = current_time('mysql');

      // Encode & compress
      $json_raw = json_encode($existing_data, JSON_PRETTY_PRINT);
      $compressed = gzencode($json_raw, 9);
      file_put_contents($json_file_path, $compressed);
      file_put_contents($json_raw_file_path, $json_raw);

      $duration = round(microtime(true) - $start_time, 2);
      $size_kb = round(strlen($compressed) / 1024, 2);

      if (defined('WP_CLI') && WP_CLI) {
         \WP_CLI::success("Processed $count products.");
         \WP_CLI::success("Wrote compressed JSON to: $json_file_path");
         \WP_CLI::success("File size: {$size_kb} KB | Duration: {$duration}s");
      }
   }

   private function taxonomy_is_product_attribute($taxonomy) {
      return taxonomy_exists($taxonomy) && strpos($taxonomy, 'pa_') === 0;
   }
}
