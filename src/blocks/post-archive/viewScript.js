(function ($) {
   $(function () {
      $('.post-archive').each(function () {
         $postArchive = $(this);
         let preloadBuffer = [];
         let postsPerPage = $postArchive.data('posts-per-page'); // Number of posts to load per request
         let postType = $postArchive.data('post-type') || 'post'; // Default to 'post' if not specified
         let offset = postsPerPage;
         let categorySlug = $postArchive.data('post-category') || ''; // Default to empty string if not specified
         const $postsWrapper = $postArchive.find('.posts-wrapper');
         const $postArchiveEmpty = $postArchive.find('.post-archive-empty');
         const $postsLoadMore = $postArchive.find('.posts-load-more button');
         const $postsLoadMoreText = $postsLoadMore.find(
            '.posts-load-more-text',
         );
         const $postsLoadMoreLoader = $postsLoadMore.find(
            '.posts-load-more-loader',
         );
         const $postCategories = $postArchive.find('.post-categories');
         const fetchSearchResults = (
            offset,
            postsPerPage,
            showResults = true,
            category_slug = $postArchive.data('post-category') || '',
         ) => {
            return $.ajax({
               url: `/wp-json/hapi/v1/get-post-type-content/`,
               method: 'GET',
               data: {
                  post_type: postType,
                  offset: offset,
                  posts_per_page: postsPerPage,
                  category_slug: category_slug,
               },
               success: function (data) {
                  const posts = data.posts || [];
                  const total = data.found_posts || 0;
                  if (showResults) {
                     offset = 0;
                     renderPosts(posts, total, true);
                  } else {
                     preloadBuffer.push(...posts);
                     if (preloadBuffer.length > 0) {
                        $postsLoadMoreLoader.removeClass('show');
                        $postsLoadMoreText.addClass('show');
                        $postsLoadMore.addClass('show');
                     } else {
                        $postsLoadMore.removeClass('show');
                     }
                  }
               },
            });
         };
         // Render posts into the DOM
         const renderPosts = (posts, foundPosts, clearList = false) => {
            if (clearList) {
               $postsWrapper.empty();
            }
            if (posts.length > 0) {
               posts.forEach((post) => {
                  const $template = $('.post-card.template')
                     .clone()
                     .removeClass('template');
                  $template.find('.post-card__title').text(post.title);
                  $template.find('.post-card__excerpt').text(post.excerpt);
                  $template.find('.post-card__image').html(post.image);
                  $template
                     .find('.post-card__read-more')
                     .attr('href', post.link);

                  $postsWrapper.append($template);
               });
            } else {
               $postArchiveEmpty.show();
            }
            $postsWrapper.show();
         };
         fetchSearchResults(offset, postsPerPage, false);
         $postsLoadMore.on('click', function (e) {
            e.preventDefault();
            const $this = $(this);
            $postsLoadMoreText.removeClass('show');
            $postsLoadMoreLoader.show();
            const postsToRender = preloadBuffer.splice(0, 8);
            renderPosts(postsToRender, preloadBuffer.length, false);
            offset += postsPerPage;
            fetchSearchResults(offset, postsPerPage, false, categorySlug).then(
               (data) => {
                  if (data.found_posts <= $postsWrapper.children().length) {
                     $postsLoadMore.removeClass('show');
                  }
                  $postsLoadMoreText.addClass('show');
                  $postsLoadMoreLoader.removeClass('show');
               },
            );
         });
         $postCategories.on('click', 'button', function (e) {
            e.preventDefault();
            const $this = $(this);
            categorySlug = $this.data('category-slug');
            $postArchive.data('post-category', categorySlug);
            $postCategories.find('button').removeClass('active');
            $this.addClass('active');
            preloadBuffer = []; // Clear preload buffer
            offset = 0; // Reset offset
            fetchSearchResults(offset, postsPerPage, true, categorySlug).then(
               () => {
                  offset += postsPerPage;
                  fetchSearchResults(offset, postsPerPage, false, categorySlug);
               },
            );
         });
      });
   });
})(jQuery);
