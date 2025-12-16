<?php

namespace Demo\Includes\Functions;

use WP_Query;

// Post archive block
function get_post_type_content($args) {
   $post_type = $args['post_type'] ?? 'post';
   $offset = intval($args['offset'] ?? 0);
   $posts_per_page = intval($args['posts_per_page'] ?? 8);
   $category_slug = $args['category_slug'] ?? '';
   $focus_slug = $args['focus_slug'] ?? '';
   $query_args = [
      'post_type' => $post_type,
      'posts_per_page' => $posts_per_page,
      'offset' => $offset,
      'orderby' => 'date title',
      'order' => 'DESC',
   ];
   if (!empty($category_slug)) {
      $query_args['tax_query'] = [
         [
            'taxonomy' => 'category',
            'field' => 'slug',
            'terms' => $category_slug,
         ],
      ];
   }
   if (!empty($focus_slug)) {
      $query_args['tax_query'][] = [
         'taxonomy' => 'foci',
         'field' => 'slug',
         'terms' => $focus_slug,
      ];
   }
   $query = new \WP_Query($query_args);
   $posts = $query->posts;
   $found_posts = $query->found_posts;
   $response = [];
   foreach ($posts as $post) {
      $response[] = [
         'id' => $post->ID,
         'title' => $post->post_title,
         'link' => get_permalink($post),
         'excerpt' => $post->post_excerpt,
         'image' => get_post_thumbnail_id($post->ID)
            ? wp_get_attachment_image(get_post_thumbnail_id($post->ID), 'full')
            : '<svg>...</svg>',
         'content' => apply_filters('the_content', get_post_field('post_content', $post->ID)),
      ];
   }
   return [
      'posts' => $response,
      'found_posts' => $found_posts,
      'has_more' => $offset < $found_posts,
      'query_args' => $query_args, // optionally include the WP_Query object
   ];
}


function get_product_data($args) {
   $total_posts = $args['numberOfProducts'] ?? 6;
   $product_category = $args['productCategory'] ?? '';
   $product_focus = $args['productFocus'] ?? '';
   $product_need = $args['productNeed'] ?? '';
   $offset = intval($args['offset'] ?? 0);
   $query_args = [
      'post_type' => 'product',
      'posts_per_page' => $total_posts,
   ];

   if (!empty($product_category)) {
      $query_args['tax_query'][] = [
         'taxonomy' => 'product_cat',
         'field' => 'slug',
         'terms' => $product_category,
      ];
   }
   if (!empty($product_focus)) {
      $query_args['tax_query'][] = [
         'taxonomy' => 'foci',
         'field' => 'slug',
         'terms' => $product_focus,
      ];
   }
   if (!empty($product_need)) {
      $query_args['tax_query'][] = [
         'taxonomy' => 'needs',
         'field' => 'slug',
         'terms' => $product_need,
      ];
   }
   if ($offset) {
      $query_args['offset'] = $offset;
   }
   return new \WP_Query($query_args);
}

// Accordion block
function get_post_type_items($post_type, $posts_per_page, $tax_query_data, $exclude = [], $include = [], $order = false) {
   $args = array(
      'post_type' => $post_type,
      'posts_per_page' => $posts_per_page,
      'post_status' => 'publish',
   );

   if ($order) {
      $args['orderby'] = 'title';
      $args['order'] = 'ASC';
   }

   if (is_array($exclude) && !empty($exclude)) {
      $args['post__not_in'] = $exclude;
   }

   if (is_array($include) && !empty($include)) {
      $args['post__in'] = $include;
   }

   // get post type based on taxonomies
   if ($tax_query_data) {
      $args['tax_query'] = array(
         'relation' => 'AND',
      );
      foreach ($tax_query_data as $tax_key => $tax_values) {
         $tax_query = array(
            'taxonomy' => $tax_key,
            'field' => 'slug',
            'terms' => [],
         );
         foreach ($tax_values as $tax_value) {
            if ($tax_value['selected'] == 1 || $tax_value['selected'] === 'true') {
               $tax_query['terms'][] = $tax_value['value'];
            }
         }
         if (count($tax_query['terms']) > 0) {
            $args['tax_query'][] = $tax_query;
         }
      }
   }

   $posts = new WP_Query($args);
   $post_data = [];
   while ($posts->have_posts()) {
      $posts->the_post();
      $attachment_id = attachment_url_to_postid(get_the_post_thumbnail_url());
      $post_data[] = array(
         'id' => get_the_ID(),
         'title' => get_the_title(),
         'excerpt' => wp_trim_words(get_the_excerpt(), 20, ' ...'),
         'content' => get_the_content(),
         'image' => wp_get_attachment_image($attachment_id, 'large', false),
         'image_url' => get_the_post_thumbnail_url(),
         'categories' => get_the_category(),
         'date' => get_the_date(),
      );
   }

   return $post_data;
}
