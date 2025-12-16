<?php

namespace Demo\Includes\Functions;

add_action('init', __NAMESPACE__ . '\register_custom_product_taxonomies');
function register_custom_product_taxonomies() {
   // Register "Fokus" taxonomy
   register_taxonomy('foci', ['product', 'ingredient'], array(
      'labels' => array(
         'name'              => __('Fokus', 'tiburon'),
         'singular_name'     => __('Fokus', 'tiburon'),
         'search_items'      => __('Sök Fokus', 'tiburon'),
         'all_items'         => __('Alla Fokus', 'tiburon'),
         'edit_item'         => __('Redigera Fokus', 'tiburon'),
         'update_item'       => __('Uppdatera Fokus', 'tiburon'),
         'add_new_item'      => __('Lägg till nytt Fokus', 'tiburon'),
         'new_item_name'     => __('Nytt Fokussnamn', 'tiburon'),
         'menu_name'         => __('Fokus', 'tiburon'),
      ),
      'hierarchical'      => true,
      'public'            => true,
      'query_var'         => true,
      'show_ui'           => true,
      'show_admin_column' => false,
      'rewrite'           => array('slug' => 'fokus'),
      'show_in_rest'      => true,
   ));

   // Register "Behov" taxonomy
   register_taxonomy('needs', 'product', array(
      'labels' => array(
         'name'              => __('Behov', 'tiburon'),
         'singular_name'     => __('Behov', 'tiburon'),
         'search_items'      => __('Sök Behov', 'tiburon'),
         'all_items'         => __('Alla Behov', 'tiburon'),
         'edit_item'         => __('Redigera Behov', 'tiburon'),
         'update_item'       => __('Uppdatera Behov', 'tiburon'),
         'add_new_item'      => __('Lägg till nytt Behov', 'tiburon'),
         'new_item_name'     => __('Nytt Behovnamn', 'tiburon'),
         'menu_name'         => __('Behov', 'tiburon'),
      ),
      'hierarchical'      => true,
      'public'            => true,
      'query_var'         => true,
      'show_ui'           => true,
      'show_admin_column' => false,
      'rewrite'           => array('slug' => 'behov'),
      'show_in_rest'      => true,
   ));
}


if (!class_exists('ACP')) {
   // Add custom taxonomy columns to the product list table
   // add_filter('manage_edit-product_columns', __NAMESPACE__ . '\custom_product_taxonomy_columns');
   function custom_product_taxonomy_columns($columns) {
      $columns['foci'] = __('Fokus', 'textdomain');
      $columns['needs'] = __('Behov', 'textdomain');
      return $columns;
   }

   // Populate the taxonomy columns with data
   // add_action('manage_product_posts_custom_column', __NAMESPACE__ . '\custom_product_taxonomy_column_content', 10, 2);
   function custom_product_taxonomy_column_content($column, $post_id) {
      if ($column === 'foci') {
         $terms = get_the_terms($post_id, 'foci');
      } elseif ($column === 'needs') {
         $terms = get_the_terms($post_id, 'needs');
      } else {
         return;
      }

      if (!empty($terms) && !is_wp_error($terms)) {
         $term_names = wp_list_pluck($terms, 'name');
         echo implode(', ', $term_names);
      } else {
         echo '—';
      }
   }
}
