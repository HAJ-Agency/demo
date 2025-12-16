(function ($) {
   $(function () {
      $(".ingredients-archive").each(function () {
         const $postArchive = $(this);
         let preloadBuffer = [];
         let postsPerPage = "-1"; // Number of posts to load per request
         let offset = 0;
         let categorySlug = $postArchive.attr("data-post-category") || ""; // Default to empty string if not specified
         const $postsWrapper = $postArchive.siblings(".ingredients-posts-wrapper");
         const $postArchiveEmpty = $postArchive.find(".post-archive-empty");
         const $postsLoadMore = $postArchive.find(".posts-load-more button");
         const $postsLoadMoreText = $postsLoadMore.find(".posts-load-more-text");
         const $postsLoadMoreLoader = $postsLoadMore.find(".posts-load-more-loader");
         const $postCategories = $postArchive.find(".post-categories");
         const fetchSearchResults = (
            offset,
            postsPerPage,
            showResults = true,
            category_slug = categorySlug
         ) => {
            console.log("fetching posts", { offset, postsPerPage, category_slug });
            return $.ajax({
               url: `/wp-json/hapi/v1/get-post-type-content/`,
               method: "GET",
               data: {
                  post_type: "ingredient",
                  posts_per_page: "-1",
                  focus_slug: category_slug,
               },
               success: function (data) {
                  console.log("fetched posts", data);
                  const posts = data.posts || [];
                  const total = data.found_posts || 0;
                  if (showResults) {
                     offset = 0;
                     renderPosts(posts, total, true);
                  } else {
                     preloadBuffer.push(...posts);
                     if (preloadBuffer.length > 0) {
                        $postsLoadMoreLoader.removeClass("show");
                        $postsLoadMoreText.addClass("show");
                        $postsLoadMore.addClass("show");
                     } else {
                        $postsLoadMore.removeClass("show");
                     }
                  }
               },
            });
         };
         // Render posts into the DOM
         const renderPosts = (posts, foundPosts, clearList = true) => {
            if (clearList) {
               $postsWrapper.empty();
            }
            console.log("rendering posts", posts);
            if (posts.length > 0) {
               console.log($postsWrapper);
               posts.forEach((post) => {
                  const $template = $(".ingredient-card-wrapper.template")
                     .clone()
                     .removeClass("template");
                  $template.find(".ingredient-card__title").text(post.title);
                  $template.find(".ingredient-card__content").text(post.content);
                  $template.find(".ingredient-card__front").prepend(post.image);
                  $template.find(".ingredient-card__read-more").attr("href", post.link);

                  $postsWrapper.append($template);
               });
            } else {
               $postArchiveEmpty.show();
            }
            $postsWrapper.show();
         };
         // fetchSearchResults(offset, postsPerPage, false)
         $postsLoadMore.on("click", function (e) {
            e.preventDefault();
            const $this = $(this);
            $postsLoadMoreText.removeClass("show");
            $postsLoadMoreLoader.show();
            const postsToRender = preloadBuffer.splice(0, 8);
            renderPosts(postsToRender, preloadBuffer.length, false);
            offset = 0;
            fetchSearchResults(offset, postsPerPage, false, categorySlug).then((data) => {
               if (data.found_posts <= $postsWrapper.children().length) {
                  $postsLoadMore.removeClass("show");
               }
               $postsLoadMoreText.addClass("show");
               $postsLoadMoreLoader.removeClass("show");
            });
         });
         $postCategories.on("click", "button", function (e) {
            e.preventDefault();
            console.log("category click");
            const $this = $(this);
            categorySlug = $this.attr("data-category-slug");
            console.log(categorySlug);
            $postArchive.attr("data-post-category", categorySlug);
            $postCategories.find("button").removeClass("active");
            $this.addClass("active");
            preloadBuffer = []; // Clear preload buffer
            offset = 0; // Reset offset
            fetchSearchResults(offset, postsPerPage, true, categorySlug);
         });
      });
   });

   //DEMO
   document.addEventListener("click", function (e) {
      const card = e.target.closest(".post-card");
      if (card) {
         const id = card.dataset.postId;
         document.getElementById("post-details-" + id).classList.add("open");
      }

      if (e.target.classList.contains("post-details__close")) {
         e.target.closest(".post-details").classList.remove("open");
      }
   });

   document.querySelectorAll(".ingredient-card").forEach((card) => {
      card.addEventListener("click", () => {
         card.classList.toggle("show-back");
      });
   });
})(jQuery);
