jQuery(function ($) {
   // For each product gallery on the page
   $('.wp-block-woocommerce-product-image-gallery .woocommerce-product-gallery').each(function () {
      const $gallery = $(this)
      const $wrapper = $gallery.find('.woocommerce-product-gallery__wrapper')
      const $slides = $wrapper.children('.woocommerce-product-gallery__image')
      const $thumbs = $('ol.flex-control-thumbs > li > img')
      const total = $slides.length

      if (!total) return

      // Build pagination UI
      const $pager = $(`
            <div class="wcg-pager" aria-label="Product image pagination">
               <button type="button" class="wcg-prev" aria-label="Previous image">
                  <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                     <path d="M7.07031 14L0.0703123 7L7.07031 8.34742e-08L7.93604 0.878091L1.81413 7L7.93604 13.1219L7.07031 14Z" fill="#303030"/>
                  </svg>
               </button>

               <button type="button" class="wcg-next" aria-label="Next image">
                  <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                     <path d="M0.929688 -3.0598e-07L7.92969 7L0.929688 14L0.0639629 13.1219L6.18587 7L0.0639629 0.878092L0.929688 -3.0598e-07Z" fill="#303030"/>
                  </svg>
               </button>
            </div>
         `)

      // Insert after the viewport (matches your screenshot placement)
      const $viewport = $gallery.find('.flex-viewport')
      ;($viewport.length ? $viewport : $gallery).after($pager)

      // Helpers
      const getCurrentIndex = () => $slides.index($slides.filter('.flex-active-slide'))

      const goTo = (i) => {
         // Wrap index and delegate to Woo's existing thumb click (safe with FlexSlider)
         const idx = (i + total) % total
         const $thumb = $thumbs.eq(idx)
         if ($thumb.length) $thumb.trigger('click')
      }

      const render = () => {
         const i = getCurrentIndex()
         $pager.find('.wcg-current').text(i + 1)
         // Optional: disable edges (remove these two lines if you prefer infinite feel)
         $pager.find('.wcg-prev').prop('disabled', i === 0)
         $pager.find('.wcg-next').prop('disabled', i === total - 1)
      }

      // Wire buttons
      $pager.on('click', '.wcg-prev', () => goTo(getCurrentIndex() - 1))
      $pager.on('click', '.wcg-next', () => goTo(getCurrentIndex() + 1))

      $pager.on('keydown', (e) => {
         if (e.key === 'ArrowLeft') {
            e.preventDefault()
            $pager.find('.wcg-prev').click()
         }
         if (e.key === 'ArrowRight') {
            e.preventDefault()
            $pager.find('.wcg-next').click()
         }
      })

      // Keep in sync when the slide changes:
      // - when a user clicks a (hidden) thumb via keyboard nav / other scripts
      // - when FlexSlider updates classes
      const observer = new MutationObserver(() => render())
      observer.observe($wrapper[0], {
         subtree: true,
         attributes: true,
         attributeFilter: ['class']
      })

      // Initial UI state
      render()
   })
})
