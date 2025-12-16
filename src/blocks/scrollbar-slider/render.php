<?php
if (!wp_script_is('swiper-js', 'enqueued')) {
   wp_enqueue_script(
      'swiper-js',
      'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
      [],
      null,
      true
   );
}

if (!wp_style_is('swiper-css', 'enqueued')) {
   wp_enqueue_style(
      'swiper-css',
      'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
      [],
      null
   );
   error_log('Slider block render started.');
}
$post_type = $attributes['postType'] ?? 'post';
$total_posts = $attributes['numberOfPosts'] ?? 6;

$query = new WP_Query([
   'post_type' => $post_type,
   'posts_per_page' => $total_posts
]);


?>
<div id="<?= $attributes['blockId']; ?>" class="<?= $attributes['mainClassName']; ?>" data-posts-per-view-desktop="<?= $attributes['postsPerViewDesktop']; ?>" data-posts-per-view-mobile="<?= $attributes['postsPerViewMobile']; ?>">
   <div class="swiper-wrapper">
      <?php while ($query->have_posts()) : $query->the_post(); ?>
         <div class="slide swiper-slide">
            <div class="post-card">
               <div class="post-card__image">
                  <?php
                  $thumbnail_id = get_post_thumbnail_id();
                  if ($thumbnail_id) {
                     echo wp_get_attachment_image($thumbnail_id, 'full', false, []);
                  } else {
                  ?>
                     <svg width="400" height="300" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_346_2327)">
                           <mask id="mask0_346_2327" style="mask-type:luminance" maskUnits="userSpaceOnUse" x="0" y="0" width="400" height="300">
                              <path d="M400 0H0V300H400V0Z" fill="white"></path>
                           </mask>
                           <g mask="url(#mask0_346_2327)">
                              <path d="M400 0H0V300H400V0Z" fill="#EFF1F3"></path>
                              <path fill-rule="evenodd" clip-rule="evenodd" d="M134.583 97.2664C134.608 93.6806 137.506 90.7785 141.091 90.75H259.284C262.879 90.75 265.792 93.6701 265.792 97.2664V202.359C265.767 205.944 262.869 208.847 259.284 208.875H141.091C137.496 208.872 134.583 205.955 134.583 202.359V97.2664ZM252.672 103.875H147.705V195.75L208.664 134.758C211.226 132.197 215.379 132.197 217.941 134.758L252.672 169.566V103.875ZM160.824 130.125C160.824 137.373 166.698 143.25 173.945 143.25C181.191 143.25 187.066 137.373 187.066 130.125C187.066 122.876 181.191 117 173.945 117C166.698 117 160.824 122.876 160.824 130.125Z" fill="#687787"></path>
                           </g>
                        </g>
                        <defs>
                           <clipPath id="clip0_346_2327">
                              <rect width="400" height="300" fill="white"></rect>
                           </clipPath>
                        </defs>
                     </svg>
                  <?php
                  }
                  ?>
               </div>
               <div class="post-card__content">
                  <h3 class="post-card__title"><?php the_title(); ?></h3>
                  <p class="post-card__excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                  <a class="post-card__read-more" href="<?php the_permalink(); ?>">Läs mer</a>
               </div>
            </div>
         </div>
      <?php endwhile; ?>
   </div>
   <div class="swiper-scrollbar"></div>
</div>