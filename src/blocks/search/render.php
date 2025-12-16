<div id="<?= $attributes['blockId']; ?>" class="<?= $attributes['mainClassName']; ?>">
   <button class="search-open-button" aria-label="<?= esc_attr__('Öppna sök', 'demo'); ?>">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
         <path d="M17.7227 12.1113C17.7227 11.2431 17.551 10.3832 17.2188 9.58105C16.8865 8.77911 16.3999 8.05033 15.7861 7.43652C15.1723 6.82267 14.4436 6.33517 13.6416 6.00293C12.8395 5.6707 11.9795 5.50002 11.1113 5.5C10.2431 5.5 9.38316 5.67069 8.58105 6.00293C7.77895 6.33517 7.05043 6.82262 6.43652 7.43652C5.19668 8.67637 4.5 10.3579 4.5 12.1113C4.50003 13.8647 5.19671 15.5463 6.43652 16.7861C7.67636 18.0259 9.35798 18.7227 11.1113 18.7227C12.8647 18.7226 14.5463 18.0259 15.7861 16.7861C16.3999 16.1723 16.8865 15.4435 17.2188 14.6416C17.551 13.8395 17.7226 12.9795 17.7227 12.1113ZM18.7227 12.1113C18.7226 13.1107 18.525 14.1001 18.1426 15.0234C17.824 15.7925 17.3802 16.5016 16.833 17.126L20.707 21L20 21.707L16.126 17.833C14.7423 19.0458 12.9625 19.7226 11.1113 19.7227C9.09276 19.7227 7.15686 18.9205 5.72949 17.4932C4.30214 16.0658 3.50003 14.1299 3.5 12.1113C3.5 10.0927 4.30211 8.15687 5.72949 6.72949C6.43622 6.02276 7.27487 5.4616 8.19824 5.0791C9.12168 4.6966 10.1118 4.5 11.1113 4.5C12.1106 4.50002 13.1002 4.69675 14.0234 5.0791C14.9469 5.4616 15.7864 6.02273 16.4932 6.72949C17.1998 7.43613 17.7601 8.27502 18.1426 9.19824C18.5251 10.1217 18.7227 11.1118 18.7227 12.1113Z" fill="currentColor" />
      </svg>
   </button>
   <div class="search-content-container">
      <div class="search-content-container-overlay">
         <button class="search-button" aria-label="<?= esc_attr__('Sök', 'demo'); ?>">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
               <path d="M20 20L16.1396 16.1396M16.1396 16.1396C16.7999 15.4793 17.3237 14.6953 17.6811 13.8326C18.0385 12.9698 18.2224 12.0451 18.2224 11.1112C18.2224 10.1774 18.0385 9.25264 17.6811 8.38987C17.3237 7.5271 16.7999 6.74316 16.1396 6.08283C15.4793 5.42249 14.6953 4.89868 13.8326 4.54131C12.9698 4.18394 12.0451 4 11.1112 4C10.1774 4 9.25264 4.18394 8.38987 4.54131C7.5271 4.89868 6.74316 5.42249 6.08283 6.08283C4.74921 7.41644 4 9.2252 4 11.1112C4 12.9972 4.74921 14.806 6.08283 16.1396C7.41644 17.4732 9.2252 18.2224 11.1112 18.2224C12.9972 18.2224 14.806 17.4732 16.1396 16.1396Z" stroke="currentColor" stroke-linecap="square" />
            </svg>
         </button>
         <input type="text" class="search-input" placeholder="<?= esc_attr__('Sök på produkt, ingrediens, behov eller något annat du vill hitta...', 'demo'); ?>" aria-label="<?= esc_attr__('Sök', 'demo'); ?>" />
         <button class="search-close-button" aria-label="<?= esc_attr__('Stäng sök', 'demo'); ?>">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
               <path d="M4.75827 4.75736L13.2435 13.2426M4.75827 13.2426L13.2435 4.75736" stroke="#572F1E" stroke-width="1.125" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
         </button>
      </div>
   </div>
   <div class="search-results-container">
      <div class="search-result-template">
         <div class="search-result-image"></div>
         <div class="search-result-meta">
            <h3 class="search-result-title"></h3>
            <p class="search-result-excerpt"></p>
            <a class="search-result-link" href="#" target="_blank" rel="noopener noreferrer"><?= esc_html__('Läs mer', 'demo'); ?></a>
         </div>
      </div>
      <div class="search-results-container-overlay">
         <div class="search-results-list"></div>
         <div class="search-results-loading">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
               <radialGradient id="a8" cx=".66" fx=".66" cy=".3125" fy=".3125" gradientTransform="scale(1.5)">
                  <stop offset="0" stop-color="#572F1E"></stop>
                  <stop offset=".3" stop-color="#572F1E" stop-opacity=".9"></stop>
                  <stop offset=".6" stop-color="#572F1E" stop-opacity=".6"></stop>
                  <stop offset=".8" stop-color="#572F1E" stop-opacity=".3"></stop>
                  <stop offset="1" stop-color="#572F1E" stop-opacity="0"></stop>
               </radialGradient>
               <circle transform-origin="center" fill="none" stroke="url(#a8)" stroke-width="20" stroke-linecap="round" stroke-dasharray="200 1000" stroke-dashoffset="0" cx="100" cy="100" r="70">
                  <animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="2" values="360;0" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform>
               </circle>
               <circle transform-origin="center" fill="none" opacity=".1" stroke="#572F1E" stroke-width="20" stroke-linecap="round" cx="100" cy="100" r="70"></circle>
            </svg>
         </div>
         <div class="search-results-show-more">
            <p class="search-results-empty"><?= __('Inga sidor hittades', 'demo') ?></p>
            <p class="search-results-number-of-pages"><?= esc_html__('Visar', 'demo') ?> <span class="search-results-showing"></span> <?= esc_html__('av', 'demo') ?> <span class="search-results-count"></span> <?= esc_html__('resultat', 'demo') ?></p>
            <div class="wp-block-button">
               <button class="wp-block-button__link wp-element-button">
                  <span class="search-results-show-more-text">
                     <?= esc_html__('Visa fler', 'demo'); ?>
                  </span>
                  <span class="search-results-show-more-loader">
                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
                        <radialGradient id="a9" cx=".66" fx=".66" cy=".3125" fy=".3125" gradientTransform="scale(1.5)">
                           <stop offset="0" stop-color="#572F1E"></stop>
                           <stop offset=".3" stop-color="#572F1E" stop-opacity=".9"></stop>
                           <stop offset=".6" stop-color="#572F1E" stop-opacity=".6"></stop>
                           <stop offset=".8" stop-color="#572F1E" stop-opacity=".3"></stop>
                           <stop offset="1" stop-color="#572F1E" stop-opacity="0"></stop>
                        </radialGradient>
                        <circle transform-origin="center" fill="none" stroke="url(#a9)" stroke-width="20" stroke-linecap="round" stroke-dasharray="200 1000" stroke-dashoffset="0" cx="100" cy="100" r="70">
                           <animateTransform type="rotate" attributeName="transform" calcMode="spline" dur="2" values="360;0" keyTimes="0;1" keySplines="0 0 1 1" repeatCount="indefinite"></animateTransform>
                        </circle>
                        <circle transform-origin="center" fill="none" opacity=".1" stroke="#572F1E" stroke-width="20" stroke-linecap="round" cx="100" cy="100" r="70"></circle>
                     </svg>
                  </span>
               </button>
            </div>
         </div>
      </div>

   </div>
</div>
