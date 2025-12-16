<?php

use demo\Includes\Api;

$post_type = $attributes['postType'] ?? 'post';
$total_posts = $attributes['numberOfPosts'] ?? 6;

$data = Api\hapi_get_post_type_content([
   'post_type' => $post_type,
   'posts_per_page' => $total_posts,
   'offset' => 0,
   'category_slug' => '',
]);

$categories = get_categories([
   'taxonomy' => 'category',
   'hide_empty' => true,
]);

?>
<div id="<?= $attributes['blockId']; ?>" class="<?= $attributes['mainClassName']; ?>" data-posts-per-view-desktop="<?= $attributes['postsPerViewDesktop']; ?>" data-posts-per-view-tablet="<?= $attributes['postsPerViewTablet'] ?>" data-posts-per-view-mobile="<?= $attributes['postsPerViewMobile']; ?>" data-posts-per-page="<?= $total_posts; ?>" data-post-type="<?= $post_type; ?>" data-post-category="">
   <div class="post-card template">
      <div class="post-card__image"></div>
      <div class="post-card__content">
         <h3 class="post-card__title"></h3>
         <p class="post-card__excerpt"></p>
         <a class="post-card__read-more" href=""><?= __('Läs mer', 'demo') ?></a>
      </div>
   </div>
   <div class="post-archive-header">
      <h3 class="post-archive-title"><?= __('Filtrera', 'demo') ?></h3>
      <div class="post-categories">
         <button class="wp-block-button__link wp-element-button active" data-category-slug="">
            <?= __('Visa allt', 'demo') ?>
         </button>
         <?php foreach ($categories as $category) { ?>
            <button class="wp-block-button__link wp-element-button" data-category-slug="<?= esc_attr($category->slug); ?>">
               <?= $category->name ?>
            </button>
         <?php } ?>
      </div>
   </div>
   <div class="posts-wrapper">
      <?php foreach ($data['posts'] as $post) { ?>
         <div class="post-card">
            <div class="post-card__image">
               <?= $post['image']; ?>
            </div>
            <div class="post-card__content">
               <h3 class="post-card__title"><?= $post['title']; ?></h3>
               <p class="post-card__excerpt"><?= $post['excerpt']; ?></p>
               <a class="post-card__read-more" href="<?= $post['link'] ?>"><?= __('Läs mer', 'demo') ?></a>
            </div>
         </div>
      <?php } ?>
   </div>
   <div class="posts-load-more">
      <button class="wp-block-button__link wp-element-button">
         <span class="posts-load-more-text">
            <?= esc_html__('Visa fler', 'demo'); ?>
         </span>
         <span class="posts-load-more-loader">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
               <radialGradient id="a10" cx=".66" fx=".66" cy=".3125" fy=".3125" gradientTransform="scale(1.5)">
                  <stop offset="0" stop-color="#572F1E"></stop>
                  <stop offset=".3" stop-color="#572F1E" stop-opacity=".9"></stop>
                  <stop offset=".6" stop-color="#572F1E" stop-opacity=".6"></stop>
                  <stop offset=".8" stop-color="#572F1E" stop-opacity=".3"></stop>
                  <stop offset="1" stop-color="#572F1E" stop-opacity="0"></stop>
               </radialGradient>
               <circle transform-origin="center" fill="none" stroke="url(#a10)" stroke-width="20" stroke-linecap="round" stroke-dasharray="200 1000" stroke-dashoffset="0" cx="100" cy="100" r="70">
                  <animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="2" values="360;0" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform>
               </circle>
               <circle transform-origin="center" fill="none" opacity=".1" stroke="#572F1E" stroke-width="20" stroke-linecap="round" cx="100" cy="100" r="70"></circle>
            </svg>
         </span>
      </button>
   </div>
</div>
