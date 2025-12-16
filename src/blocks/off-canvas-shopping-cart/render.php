<?php

use Akka\Includes\WooCommerce\CartRenderer;

if (!defined('ABSPATH')) exit;

// Don't render on checkout page (Woo already shows cart there).
if (function_exists('is_checkout') && is_checkout()) {
   return;
}

CartRenderer::ensure_cart_loaded();
$counts = CartRenderer::counts();
?>

<button class="cart-drawer__header-icon">
   <svg class="shopping-cart" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M4 9H20L19.165 18.181C19.1198 18.6779 18.8906 19.14 18.5222 19.4766C18.1538 19.8131 17.673 19.9998 17.174 20H6.826C6.32704 19.9998 5.84617 19.8131 5.4778 19.4766C5.10942 19.14 4.88016 18.6779 4.835 18.181L4 9Z" stroke="#303030" stroke-linejoin="round" />
      <path d="M8 11V8C8 6.93913 8.42143 5.92172 9.17157 5.17157C9.92172 4.42143 10.9391 4 12 4C13.0609 4 14.0783 4.42143 14.8284 5.17157C15.5786 5.92172 16 6.93913 16 8V11" stroke="#303030" stroke-linecap="round" />
   </svg>
   <div class="cart-drawer__header-icon-count" id="cart-bubble-count">
      <?php echo (int)$counts['count']; ?>
   </div>
</button>

<div class="cart-overlay"></div>

<div class="cart-drawer"
   data-endpoint-items="<?php echo esc_attr(wp_parse_url(rest_url('hapi/v1/cart/items'),  PHP_URL_PATH)); ?>"
   data-endpoint-totals="<?php echo esc_attr(wp_parse_url(rest_url('hapi/v1/cart/totals'), PHP_URL_PATH)); ?>"
   data-endpoint-counts="<?php echo esc_attr(wp_parse_url(rest_url('hapi/v1/cart/counts'), PHP_URL_PATH)); ?>"
   data-endpoint-coupon="<?php echo esc_attr(wp_parse_url(rest_url('hapi/v1/cart/coupon'), PHP_URL_PATH)); ?>">
   <div class="cart-drawer__header">
      <div class="cart-drawer__title-wrap">
         <span class="cart-drawer__icon">
            <svg class="cart-drawer__icon-cart" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
               <path d="M4 9H20L19.165 18.181C19.1198 18.6779 18.8906 19.14 18.5222 19.4766C18.1538 19.8131 17.673 19.9998 17.174 20H6.826C6.32704 19.9998 5.84617 19.8131 5.4778 19.4766C5.10942 19.14 4.88016 18.6779 4.835 18.181L4 9Z" stroke="#303030" stroke-linejoin="round" />
               <path d="M8 11V8C8 6.93913 8.42143 5.92172 9.17157 5.17157C9.92172 4.42143 10.9391 4 12 4C13.0609 4 14.0783 4.42143 14.8284 5.17157C15.5786 5.92172 16 6.93913 16 8V11" stroke="#303030" stroke-linecap="round" />
            </svg>
         </span>
         <h4 class="cart-drawer__title">Varukorg</h4>
         <p class="cart-drawer__count" id="cart-title-count"><?php echo esc_html($counts['paren_html']); ?></p>
      </div>
      <div>
         <button href="#" class="cart-drawer__close">
            <svg class="cart-drawer__close-icon" xmlns="http://www.w3.org/2000/svg" width="23" height="24" viewBox="0 0 23 24" fill="none">
               <path d="M20.1692 3.00004L21.3138 4.14465L4.14461 21.3138L3 20.1692L20.1692 3.00004Z" fill="currentColor" />
               <path d="M21.3137 20.1692L20.1691 21.3138L2.99992 4.14461L4.14453 3L21.3137 20.1692Z" fill="currentColor" />
            </svg>
         </button>
      </div>
   </div>

   <!-- ITEMS -->
   <div class="cart-drawer__item-wrap" id="cart-items">
      <?php echo CartRenderer::render_items_html(); ?>
   </div>
   <?php echo CartRenderer::render_coupon_fragment(false); ?>
   <!-- TOTALS / RIGHT SIDE -->
   <div id="cart-totals">
      <?php echo CartRenderer::render_totals_html(); ?>
   </div>
</div>
