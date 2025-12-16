<?php


$post_type = 'ingredient';
$total_posts = '-1';

$data = demo\Includes\Functions\get_post_type_content([
   'post_type' => $post_type,
   'posts_per_page' => $total_posts,
   'offset' => 0,
   'category_slug' => '',
]);

$categories = get_categories([
   'taxonomy' => 'foci',
   'hide_empty' => true,
]);

?>
<div id="<?= $attributes['blockId']; ?>" class="<?= $attributes['mainClassName']; ?>" data-posts-per-view-desktop="<?= $attributes['postsPerViewDesktop']; ?>" data-posts-per-view-tablet="<?= $attributes['postsPerViewTablet'] ?>" data-posts-per-view-mobile="<?= $attributes['postsPerViewMobile']; ?>" data-posts-per-page="<?= $total_posts; ?>" data-post-type="<?= $post_type; ?>" data-post-category="">
   <div class="ingredient-card-wrapper template">
      <div class="ingredient-card" data-post-id="">
         <div class="ingredient-card__front">

            <div class="ingredient-card__overlay">
               <p class="ingredient-card__text"><?= __('Click to read more', 'demo') ?></p>
            </div>
         </div>
         <div class="ingredient-card__back">
            <p class="ingredient-card__content"></p>
         </div>
      </div>
      <h2 class="ingredient-card__title wp-block-heading"></h2>
   </div>

   <div class="post-categories">
      <button class="wp-block-button__link wp-element-button active" data-category-slug="">
         <?= __('Show all', 'demo') ?>
      </button>
      <?php foreach ($categories as $category) { ?>
         <button class="wp-block-button__link wp-element-button" data-category-slug="<?= esc_attr($category->slug); ?>">
            <?= $category->name ?>
         </button>
      <?php } ?>
   </div>
</div>


<div class="ingredients-posts-wrapper">
   <?php foreach ($data['posts'] as $post) : ?>
      <div class="ingredient-card-wrapper">

         <div class="ingredient-card" data-post-id="<?= $post['id']; ?>">

            <div class="ingredient-card__front">
               <?= $post['image']; ?>
               <div class="ingredient-card__overlay">
                  <p class="ingredient-card__text"><?= __('Click to read more', 'demo') ?></p>
               </div>
            </div>

            <div class="ingredient-card__back">
               <p class="ingredient-card__content"><?= $post['content']; ?></p>
            </div>

         </div>

         <h2 class="ingredient-card__title wp-block-heading"><?= $post['title']; ?></h2>

      </div>
   <?php endforeach; ?>
</div>
