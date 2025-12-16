<?php

namespace Demo\Includes\Functions;

// Register Custom Post Type
add_action('init', __NAMESPACE__ . '\register_ingredient_post_type');
function register_ingredient_post_type() {
   register_post_type('ingredient', [
      'labels' => [
         'name' => __('Ingredients', 'tiburon'),
         'singular_name' => __('Ingredient', 'tiburon'),
         'menu_name' => __('Ingredients', 'tiburon'),
         'name_admin_bar' => __('Ingredients', 'tiburon'),
      ],
      'public' => true,
      'has_archive' => false,
      'publicly_queryable' => false,
      'show_in_rest' => true,
      'supports' => ['title', 'editor', 'thumbnail'],
      'rewrite' => false,
      'menu_icon' => 'dashicons-image-filter',
      'taxonomies' => ['foci', 'category']
   ]);
}
