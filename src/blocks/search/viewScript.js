;(function ($) {
    $(function () {
        const postsPerPage = 8
        let preloadBuffer = []
        let currentOffset = 0

        $('.search-header').each(function () {
            const $searchHeader = $(this)
            const $searchContainer = $searchHeader.find('.search-content-container')
            const $searchInput = $searchHeader.find('.search-input')
            const $resultsList = $searchHeader.find('.search-results-list')
            const $resultsContainer = $searchHeader.find('.search-results-container')
            const $resultsContainerOverlay = $searchHeader.find('.search-results-container-overlay')
            const $showMoreButton = $searchHeader.find('.search-results-show-more .wp-block-button')
            const $loader = $searchHeader.find('.search-results-loading')
            const $searchResultsEmpty = $searchHeader.find('.search-results-empty')
            const $showingCount = $searchHeader.find('.search-results-showing')
            const $resultsCount = $searchHeader.find('.search-results-count')
            const $showMoreLoader = $('.search-results-show-more-loader')
            const $showMoreText = $('.search-results-show-more-text')

            let searchTimeout

            // Toggle search container visibility
            $searchHeader.find('.search-open-button').on('click', function () {
                if ($searchContainer.hasClass('open')) {
                    $searchContainer.removeClass('open')
                    $resultsContainer.removeClass('open').css('top', '100%')
                    setTimeout(() => {
                        $searchContainer.css('visibility', 'hidden')
                        console.log('Search container hidden')
                    }, 200)
                } else {
                    $searchContainer.css('visibility', 'visible')
                    $searchContainer.addClass('open')
                    $searchInput.trigger('focus')
                    setTimeout(() => {
                        $resultsContainer.css('top', `calc(var(--header-height) + ${$searchContainer.outerHeight()}px)`)
                    }, 200)
                }
                $searchInput.val('')
            })

            $searchHeader.find('.search-close-button').on('click', function () {
                $searchContainer.removeClass('open')
                $resultsContainer.removeClass('open')
                $resultsContainer.removeClass('open').css('top', '100%')
                $searchInput.val('')

                setTimeout(() => $searchContainer.css('visibility', 'hidden'), 200)
            })

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.search-header').length) {
                    $searchContainer.removeClass('open')
                    $resultsContainer.removeClass('open')
                    $resultsContainer.css('top', '100%')
                    $searchInput.val('')
                    setTimeout(() => $searchContainer.css('visibility', 'hidden'), 200)
                }
            })
            // Fetch results from API
            const fetchSearchResults = (searchValue, offset, showResults = true) => {
                return $.ajax({
                    url: `/wp-json/hapi/v1/search-page/`,
                    method: 'GET',
                    data: {
                        search_value: searchValue,
                        offset: offset,
                        posts_per_page: postsPerPage
                    },
                    success: function (data) {
                        const posts = data.posts || []
                        const total = data.found_posts || 0

                        $showMoreButton.toggle(!!data.has_more)

                        if (showResults) {
                            renderPosts(posts, total, true)
                        } else {
                            preloadBuffer.push(...posts)
                        }

                        return data
                    }
                })
            }

            // Render posts into the DOM
            const renderPosts = (posts, foundPosts, clearList = false) => {
                if (clearList) {
                    $resultsList.empty()
                    $resultsCount.text(foundPosts)
                    $showingCount.text(posts.length)
                } else {
                    $showingCount.text($resultsList.find('.search-result-item').length + posts.length)
                }
                if (posts.length > 0) {
                    $searchResultsEmpty.hide()
                    posts.forEach((post) => {
                        const $template = $('.search-result-template')
                            .clone()
                            .removeClass('search-result-template')
                            .addClass('search-result-item')

                        $template.find('.search-result-title').text(post.title)
                        $template.find('.search-result-excerpt').html(post.excerpt)
                        $template.find('.search-result-image').html(post.image)
                        $template.find('.search-result-link').attr('href', post.link)

                        $resultsList.append($template)
                    })
                } else {
                    $searchResultsEmpty.show()
                }
                $resultsList.show()
                $loader.hide()
                $searchHeader.find('.search-results-show-more').show()
            }

            // Search input handler
            $searchInput.on('input', function () {
                $resultsContainer.css('top', `calc(var(--header-height) + ${$searchContainer.outerHeight()}px)`)
                $resultsContainerOverlay.css(
                    'max-height',
                    `calc(100vh - var(--header-height) - ${$searchContainer.outerHeight()}px)`
                )
                $resultsList.hide()
                $loader.show()
                $searchHeader.find('.search-results-show-more').hide()

                clearTimeout(searchTimeout)
                searchTimeout = setTimeout(() => {
                    const searchQuery = $searchInput.val().trim()

                    if (searchQuery.length > 0) {
                        currentOffset = 0
                        preloadBuffer = []
                        $resultsContainer.addClass('open')

                        fetchSearchResults(searchQuery, 0).then(() => {
                            currentOffset += postsPerPage
                            $showMoreLoader.show()
                            $showMoreText.hide()

                            fetchSearchResults(searchQuery, currentOffset, false).then(() => {
                                $showMoreLoader.hide()
                                $showMoreText.show()
                            })
                        })
                    } else {
                        $resultsList.empty()
                        $resultsContainer.removeClass('open')
                    }
                }, 200)
            })

            // Show more button handler
            $showMoreButton.on('click', function () {
                const searchQuery = $searchInput.val().trim()

                if (preloadBuffer.length > 0) {
                    $showMoreLoader.show()
                    $showMoreText.hide()

                    const nextBatch = preloadBuffer.splice(0, postsPerPage)
                    renderPosts(nextBatch, true)
                    currentOffset += postsPerPage

                    fetchSearchResults(searchQuery, currentOffset, false).then(() => {
                        $showMoreLoader.hide()
                        $showMoreText.show()
                    })
                }
            })
        })
    })
})(jQuery)
