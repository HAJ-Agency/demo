<?php

/**
 * File: includes/Cart/CartRenderer.php
 */

namespace Akka\Includes\WooCommerce;

if (!defined('ABSPATH')) exit;

class CartRenderer {

   /** Ensure Woo session/cart are available (works for both REST & normal requests). */
   public static function ensure_cart_loaded(): void {
      if (function_exists('wc_load_cart')) {
         wc_load_cart();
      } else {
         if (null === \WC()->session) {
            $session_class = apply_filters('woocommerce_session_handler', 'WC_Session_Handler');
            \WC()->session = new $session_class();
            \WC()->session->init();
         }
         if (null === \WC()->customer) {
            \WC()->customer = new \WC_Customer(get_current_user_id(), true);
         }
         if (null === \WC()->cart) {
            \WC()->cart = new \WC_Cart();
            \WC()->cart->get_cart();
         }
      }
   }

   /** Current user is reseller? */
   public static function is_reseller(): bool {
      $uid = get_current_user_id();
      return get_user_meta($uid, 'is_reseller', true) == "1";
   }

   /**
    * Render only the cart ITEMS list (inner of #cart-items).
    * $checkout=true: render with hidden inputs so checkout can update quantities via update_checkout.
    */
   public static function render_items_html($checkout = false): string {
      self::ensure_cart_loaded();

      $cart        = \WC()->cart ? \WC()->cart->get_cart() : [];
      $is_reseller = self::is_reseller();

      ob_start();

      if (empty($cart)) {
         echo '<p class="cart-empty">Din varukorg är tom</p>';
         return ob_get_clean();
      }

      foreach ($cart as $cart_key => $cart_item) {
         $is_variation = !empty($cart_item['variation_id']);
         $product_id   = $is_variation ? (int) $cart_item['variation_id'] : (int) $cart_item['product_id'];
         $quantity     = max(1, (int) ($cart_item['quantity'] ?? 1));
         $product      = wc_get_product($product_id);
         if (!$product) continue;

         // Title & link
         $title     = $is_variation ? get_the_title($cart_item['product_id']) : $product->get_name();
         $permalink = get_permalink($product_id);

         // Image
         $image_id = $product->get_image_id();
         $image    = $image_id
            ? wp_get_attachment_image($image_id, 'thumbnail', false, [
               'class' => 'cart-drawer__item-image',
               'alt'   => $title,
            ])
            : '<img class="cart-drawer__item-image" src="' . esc_url(wc_placeholder_img_src()) . '" alt="' . esc_attr($title) . '">';

         // Line price (respect reseller display inc/excl)
         $line_price_num = $is_reseller
            ? wc_get_price_excluding_tax($product, ['qty' => $quantity])
            : wc_get_price_including_tax($product, ['qty' => $quantity]);
         $line_price_html = wc_price($line_price_num, ['decimals' => 2]);

?>
         <div class="cart-drawer__item-wrap" data-context="<?php echo $checkout ? 'checkout' : 'drawer'; ?>">
            <div class="cart-drawer__item" data-product-id="<?php echo esc_attr($product_id); ?>">
               <?php echo $image; ?>
               <div class="cart-drawer__item-details">
                  <div class="cart-drawer__item-info">
                     <h5 class="cart-drawer__item-name">
                        <a href="<?php echo esc_url($permalink); ?>" class="cart-drawer__item-name">
                           <?php echo esc_html($title); ?>
                        </a>
                     </h5>
                     <?php
                     // Variation attributes (formatted, safer than raw get_attributes()).
                     if ($is_variation) {
                        // get variation attributes and format nicely
                        $attrs = [];
                        foreach ($product->get_attributes() as $attr_name => $term_slug) {
                           $label = wc_attribute_label($attr_name);
                           if (taxonomy_is_product_attribute($attr_name)) {
                              $taxonomy = wc_sanitize_taxonomy_name($attr_name);
                              $term     = get_term_by('slug', $term_slug, $taxonomy);
                              if ($term && !is_wp_error($term)) {
                                 $value = $term->name;
                              } else {
                                 $value = $term_slug;
                              }
                           } else {
                              // custom product attribute (not taxonomy-based)
                              $value = $term_slug;
                           }
                           $attrs[] = sprintf('%s: %s', $label, $value);
                        }
                        $formatted = implode(', ', $attrs);
                        if (!empty($formatted)) {
                           echo '<p class="cart-drawer__item-meta">' . wp_kses_post($formatted) . '</p>';
                        }
                     }
                     ?>
                  </div>

                  <?php
                  // Compute max purchase qty (respect sold individually / stock)
                  $max_purchase_qty = $product->is_sold_individually()
                     ? 1
                     : $product->get_max_purchase_quantity(); // returns '' if unlimited/backorders
                  ?>
                  <div class="cart-drawer__qty-wrap">
                     <button
                        type="button"
                        class="cart-drawer__item-qty-btn"
                        data-action="decrease"
                        data-cart-key="<?php echo esc_attr($cart_key); ?>"
                        aria-label="<?php esc_attr_e('Minska antal', 'tiburon'); ?>">-</button>

                     <input
                        type="number"
                        class="cart-drawer__item-qty-input"
                        data-cart-key="<?php echo esc_attr($cart_key); ?>"
                        value="<?php echo esc_attr($quantity); ?>"
                        min="0"
                        <?php if ($max_purchase_qty !== ''): ?>
                        max="<?php echo esc_attr($max_purchase_qty); ?>"
                        <?php endif; ?>
                        step="1"
                        inputmode="numeric"
                        aria-label="<?php esc_attr_e('Antal', 'tiburon'); ?>"
                        <?php if ($product->is_sold_individually()): ?>readonly<?php endif; ?> />

                     <button
                        type="button"
                        class="cart-drawer__item-qty-btn"
                        data-action="increase"
                        data-cart-key="<?php echo esc_attr($cart_key); ?>"
                        aria-label="<?php esc_attr_e('Öka antal', 'tiburon'); ?>"
                        <?php disabled($product->is_sold_individually()); ?>>+</button>
                  </div>

                  <?php if ($checkout): ?>
                     <!-- Keep hidden input so checkout's update_checkout picks up the value -->
                     <input
                        type="hidden"
                        class="cart-hidden-qty"
                        name="cart[<?php echo esc_attr($cart_key); ?>][qty]"
                        data-cart-key="<?php echo esc_attr($cart_key); ?>"
                        value="<?php echo esc_attr($quantity); ?>" />
                  <?php endif; ?>


                  <div class="cart-drawer__price">
                     <p class="cart-drawer__price-main"><?php echo $line_price_html; ?></p>
                     <?php if ($is_reseller): ?>
                        <p class="cart-drawer__price-sub"><?php echo esc_html__('Exkl moms', 'tiburon'); ?></p>
                     <?php endif; ?>
                  </div>
               </div>
            </div>

            <div class="cart-drawer__item-remove-wrap">
               <button type="button" class="cart-drawer__item-remove" data-cart-key="<?php echo esc_attr($cart_key); ?>">
                  <svg class="cart-drawer__trash-icon" xmlns="http://www.w3.org/2000/svg" stroke="#000" width="22" height="22" viewBox="0 0 22 22" fill="none">
                     <path d="M13 10V16M9 10V16M5 6V18C5 18.5304 5.21071 19.0391 5.58579 19.4142C5.96086 19.7893 6.46957 20 7 20H15C15.5304 20 16.0391 19.7893 16.4142 19.4142C16.7893 19.0391 17 18.5304 17 18V6M3 6H19M6 6L8 2H14L16 6" stroke="#303030" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
               </button>
            </div>
         </div>
      <?php
      }

      return ob_get_clean();
   }

   /** Render the totals block. */
   public static function render_totals_html($checkout = false): string {
      self::ensure_cart_loaded();
      self::prime_customer_tax_location();

      if (\WC()->cart) {
         \WC()->cart->calculate_totals();
      }

      $cart        = \WC()->cart;
      $is_reseller = self::is_reseller();
      $pct         = $is_reseller ? self::get_user_discount_pct() : 0.0;
      $stack       = false;

      // Woo numerics
      $t            = $cart ? (array) $cart->get_totals() : [];
      $subtotal_ex  = (float) ($t['subtotal']       ?? 0.0);
      $subtotal_tax = (float) ($t['subtotal_tax']   ?? 0.0);
      $shipping_ex  = (float) ($t['shipping_total'] ?? 0.0);
      $shipping_tax = (float) ($t['shipping_tax']   ?? 0.0);
      $tax_total    = (float) ($t['total_tax']      ?? 0.0);
      $grand_total  = (float) ($t['total']          ?? 0.0); // final incl. tax, after discounts & shipping

      // Display mode for rows
      $products_disp = $is_reseller ? $subtotal_ex : ($subtotal_ex + $subtotal_tax);
      $shipping_disp = $is_reseller ? $shipping_ex : ($shipping_ex + $shipping_tax);

      // Your ÅF-rabatt (display only)
      $reseller_discount = 0.0;
      if ($is_reseller && $pct > 0 && $cart) {
         foreach ($cart->get_cart() as $cart_item) {
            $pid     = !empty($cart_item['variation_id']) ? (int)$cart_item['variation_id'] : (int)$cart_item['product_id'];
            $qty     = max(1, (int)($cart_item['quantity'] ?? 1));
            $product = wc_get_product($pid);
            if (!$product) continue;

            $eff_pct = self::effective_pct_for($product);
            $base_raw = self::base_price_for($product, $stack);
            $disc_raw = $base_raw * (1 - $eff_pct / 100.0);

            // reseller display is ex VAT
            $before = wc_get_price_excluding_tax($product, ['qty' => $qty, 'price' => $base_raw]);
            $after  = wc_get_price_excluding_tax($product, ['qty' => $qty, 'price' => $disc_raw]);

            $reseller_discount += max(0.0, (float)($before - $after));
         }
      }

      // Coupon lines (in display mode: inc tax for non-reseller, ex tax for reseller)
      $coupon_lines = [];
      if ($cart) {
         foreach ($cart->get_applied_coupons() as $code) {
            $amount = (float) $cart->get_coupon_discount_amount($code, $inc_tax = !$is_reseller);
            if ($amount <= 0) continue;
            $label = __('Rabattkod', 'tiburon') . ' “' . wc_format_coupon_code($code) . '”';
            $c = new \WC_Coupon($code);
            if ($c->get_discount_type() === 'percent' && $c->get_amount() > 0) {
               $label .= ' ' . wc_format_decimal($c->get_amount(), 0) . '%';
            }
            $coupon_lines[] = ['label' => $label, 'amount' => $amount];
         }
      }

      // Legacy (drawer) grand from your previous logic if you ever render there
      $legacy_grand = $products_disp; // old block used subtotal-after-discount; keep for non-checkout if you want

      ob_start(); ?>
      <div class="cart-drawer__totals" data-context="<?php echo $checkout ? 'checkout' : 'drawer'; ?>">
         <h4 class="cart-drawer__totals-title"><?= __("Totalt", "tiburon"); ?></h4>

         <div class="cart-drawer__totals-list">
            <?php if ($checkout): ?>
               <!-- Produkter -->
               <p class="cart-drawer__totals-label"><?php esc_html_e('Produkter', 'tiburon'); ?></p>
               <p class="cart-drawer__totals-value"><?php echo wp_kses_post(wc_price($products_disp)); ?></p>

               <!-- Frakt -->
               <p class="cart-drawer__totals-label"><?php esc_html_e('Frakt', 'tiburon'); ?></p>
               <p class="cart-drawer__totals-value"><?php echo wp_kses_post(wc_price($shipping_disp)); ?></p>

               <!-- ÅF-rabatt (display only) -->
               <?php if ($is_reseller && $reseller_discount > 0): ?>
                  <p class="cart-drawer__totals-label"><?php esc_html_e('ÅF-Rabatt', 'tiburon'); ?></p>
                  <p class="cart-drawer__totals-value">-<?php echo wp_kses_post(wc_price($reseller_discount)); ?></p>
               <?php endif; ?>

               <!-- Coupon rows -->
               <?php foreach ($coupon_lines as $disc): ?>
                  <p class="cart-drawer__totals-label"><?php echo esc_html($disc['label']); ?></p>
                  <p class="cart-drawer__totals-value">-<?php echo wp_kses_post(wc_price($disc['amount'])); ?></p>
               <?php endforeach; ?>

               <!-- Optional Moms row for reseller style -->
               <?php if ($is_reseller): ?>
                  <p class="cart-drawer__totals-label"><?php esc_html_e('Moms', 'tiburon'); ?></p>
                  <p class="cart-drawer__totals-value"><?php echo wp_kses_post(wc_price($tax_total)); ?></p>
               <?php endif; ?>

               <!-- Grand (incl shipping & all discounts, Woo standard) -->
               <p class="cart-drawer__totals-grand"><?php echo wp_kses_post(wc_price($grand_total)); ?></p>

            <?php else: ?>
               <!-- Drawer (keep your old minimal look) -->
               <?php if ($is_reseller && $pct > 0): ?>
                  <p class="cart-drawer__totals-label">
                     <?php printf('%s%% %s', wc_format_decimal($pct, 2), esc_html__('ÅF-Rabatt', 'tiburon')); ?>
                  </p>
                  <p class="cart-drawer__totals-value">-<?php echo wp_kses_post(wc_price($reseller_discount)); ?></p>
                  <p class="cart-drawer__totals-label"><?php echo esc_html__('Moms', 'tiburon'); ?></p>
                  <p class="cart-drawer__totals-value"><?php echo wp_kses_post(wc_price($tax_total)); ?></p>
               <?php endif; ?>

               <p class="cart-drawer__totals-grand"><?php echo wp_kses_post(wc_price($legacy_grand)); ?></p>
            <?php endif; ?>
         </div>
      </div>

      <?php if (!$checkout):
         // link to woocommerce_terms_page
         $terms_page_id = get_option('woocommerce_terms_page_id');
         $terms_url = $terms_page_id ? get_permalink($terms_page_id) : '';
      ?>
         <a href="<?php echo esc_url(wc_get_checkout_url()); ?>">
            <button type="button" class="cart-drawer__checkout"><?php echo esc_html__('Fortsätt till kassan', 'tiburon'); ?></button>
         </a>
         <?php if (!$is_reseller) : ?>
            <div class="cart-drawer__info">
               <h4 class="cart-drawer__info-title"><?= __("Säker betalning", "tiburon"); ?></h4>
               <div class="cart-drawer__info-text cart-drawer__info-icons">
                  <div><svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.40067 1.91663V21.0833H0V1.91663H4.40067ZM15.3832 1.91663C15.3832 5.93588 13.8661 9.68392 11.1521 12.5235L10.8857 12.7947L16.9644 21.0833H11.5326L4.92679 12.075L6.63167 10.7985C9.37058 8.74763 10.9777 5.64454 11.0755 2.24629L11.0802 1.91663H15.3832ZM20.6042 16.2916C21.2396 16.2916 21.849 16.544 22.2983 16.9933C22.7476 17.4427 23 18.052 23 18.6875C23 19.3229 22.7476 19.9323 22.2983 20.3816C21.849 20.8309 21.2396 21.0833 20.6042 21.0833C19.9688 21.0833 19.3594 20.8309 18.9101 20.3816C18.4608 19.9323 18.2083 19.3229 18.2083 18.6875C18.2083 18.052 18.4608 17.4427 18.9101 16.9933C19.3594 16.544 19.9688 16.2916 20.6042 16.2916Z" fill="#303030" />
                     </svg>
                  </div>
                  <div><svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.6562 21.875H2.34375C1.04688 21.875 0 20.8281 0 19.5312V5.46875C0 4.17188 1.04688 3.125 2.34375 3.125H22.6562C23.9531 3.125 25 4.17188 25 5.46875V19.5312C25 20.8281 23.9531 21.875 22.6562 21.875ZM2.34375 4.6875C1.90625 4.6875 1.5625 5.03125 1.5625 5.46875V19.5312C1.5625 19.9688 1.90625 20.3125 2.34375 20.3125H22.6562C23.0938 20.3125 23.4375 19.9688 23.4375 19.5312V5.46875C23.4375 5.03125 23.0938 4.6875 22.6562 4.6875H2.34375Z" fill="#303030" />
                        <path d="M20.6094 10.0471H19.4375C19.0781 10.0471 18.8125 10.1565 18.6406 10.5159L16.3906 15.6409H17.9844C17.9844 15.6409 18.2656 14.9534 18.3125 14.7971H20.2656C20.3281 14.9846 20.4531 15.6252 20.4531 15.6252H21.875L20.6094 10.0627V10.0471ZM18.75 13.6409C18.8906 13.3127 19.3594 12.0784 19.3594 12.0784C19.3594 12.1096 19.5 11.7502 19.5469 11.5627L19.6562 12.0627L20.0156 13.6877H18.75V13.6565V13.6409ZM16.5 13.7971C16.5 14.9534 15.4531 15.719 13.8437 15.719C13.1562 15.719 12.5 15.5784 12.1406 15.4221L12.3594 14.1565L12.5469 14.2346C13.0469 14.4534 13.375 14.5315 13.9687 14.5315C14.4062 14.5315 14.875 14.3596 14.875 13.9846C14.875 13.7346 14.6875 13.5784 14.0781 13.2971C13.5 13.0159 12.7344 12.5784 12.7344 11.7971C12.7344 10.7034 13.7969 9.95337 15.3125 9.95337C15.8906 9.95337 16.375 10.0627 16.6875 10.2034L16.4688 11.4065L16.3594 11.2971C16.0781 11.1877 15.7344 11.0784 15.2031 11.0784C14.625 11.1096 14.3594 11.3596 14.3594 11.5784C14.3594 11.8284 14.6875 12.0159 15.2031 12.2659C16.0781 12.6721 16.4844 13.1409 16.4844 13.7971H16.5ZM3.125 10.094L3.15625 9.98462H5.51562C5.84375 9.98462 6.09375 10.094 6.17188 10.4534L6.6875 12.9221C6.17188 11.6096 4.95312 10.5315 3.125 10.094Z" fill="#303030" />
                        <path d="M10 10.0466L7.60937 15.6091H5.98437L4.60938 10.9529C5.59375 11.5779 6.42188 12.5779 6.71875 13.2498L6.89062 13.8279L8.375 10.0154H10V10.0466ZM10.6406 10.0154H12.1406L11.1875 15.6091H9.6875L10.6406 10.0154Z" fill="#303030" />
                     </svg>
                  </div>
               </div>
            </div>
         <?php endif; ?>
         <div class="cart-drawer__info">
            <h4 class="cart-drawer__info-title"><?= __("Frakt och retur", "tiburon"); ?></h4>
            <p class="cart-drawer__info-text"><?= __("Frakt & retur ska vara enkelt. Vi packar med omsorg och gör processen tydlig, även när du behöver ändra eller lämna tillbaka.", "tiburon"); ?> <a class="cart-drawer__link" href="<?= $terms_url; ?>"><?= __("Läs allt om frakt och retur här.", "tiburon"); ?></a></p>
         </div>
         <div class="cart-drawer__info">
            <h4 class="cart-drawer__info-title"><?= __("Behöver du hjälp", "tiburon"); ?></h4>
            <p class="cart-drawer__info-text"><?= __("Kontakta oss på", "tiburon"); ?> <a class="cart-drawer__link" href="mailto:info@akka.health">info@akka.health</a></p>
         </div>
      <?php endif;

      return ob_get_clean();
   }

   public static function render_coupon_fragment(bool $checkout = false): string {
      self::ensure_cart_loaded();

      // Only non-resellers see this input
      if (self::is_reseller()) {
         return ''; // nothing for ÅF
      }

      $applied = \WC()->cart ? (array) \WC()->cart->get_applied_coupons() : [];
      $current = '';
      if (!empty($applied)) {
         // keep only the first visually (single-code UX)
         $current = wc_format_coupon_code($applied[0]);
      }

      // Give each context its own id so JS can ask for the right fragment
      $ctx = $checkout ? 'checkout' : 'drawer';

      ob_start(); ?>
      <div class="cart-discount" id="cart-discount--<?php echo esc_attr($ctx); ?>" data-context="<?php echo esc_attr($ctx); ?>">
         <label class="cart-discount__label" for="discount-code--<?php echo esc_attr($ctx); ?>">
            <?= esc_html__("Partnerkod", "tiburon"); ?>
         </label>
         <div class="cart-discount__row">
            <input
               class="cart-discount__input cart-drawer__discount-code-box"
               type="text"
               id="discount-code--<?php echo esc_attr($ctx); ?>"
               name="discount-code"
               value="<?php echo esc_attr($current); ?>"
               autocomplete="off"
               inputmode="text" />
            <button type="button" class="cart-discount__apply button" data-role="apply-coupon">
               <?= esc_html__("Lägg till", "tiburon"); ?>
            </button>
            <button type="button" class="cart-discount__remove button button--ghost" data-role="clear-code"
               <?php disabled($current === ''); ?>>
               <?= esc_html__("Ta bort", "tiburon"); ?>
            </button>
         </div>
         <small class="cart-discount__msg" aria-live="polite"></small>
      </div>
   <?php
      return ob_get_clean();
   }



   public static function render_shipping_fragment(): string {
      self::ensure_cart_loaded();

      // Build full wrapper markup so fragments can replace the whole node.
      ob_start();
      echo '<div id="cart-shipping" class="cart-shipping">';
      if (WC()->cart && WC()->cart->needs_shipping() && WC()->cart->show_shipping()) {
         // Outputs the standard shipping method radios per package
         wc_cart_totals_shipping_html();
      } else {
         // Optional text when no shipping needed/available
         do_action('woocommerce_review_order_before_shipping');
         echo '<div class="woocommerce-shipping-notice" role="note" style="margin:.5rem 0;">';
         echo esc_html__('Ingen frakt krävs för den här beställningen.', 'tiburon');
         echo '</div>';
         do_action('woocommerce_review_order_after_shipping');
      }
      echo '</div>';
      return ob_get_clean();
   }

   /** Return numeric display amounts for subtotal/shipping/discounts/grand. */
   public static function get_display_totals(): array {
      self::ensure_cart_loaded();
      if (\WC()->cart) {
         \WC()->cart->calculate_totals(); // ensure fresh
      }

      $is_reseller = self::is_reseller();
      $cart        = \WC()->cart;
      $t           = $cart ? (array) $cart->get_totals() : [];

      // Raw numerics from WC (all ex tax except 'total')
      $subtotal_ex   = (float) ($t['subtotal']      ?? 0.0);
      $subtotal_tax  = (float) ($t['subtotal_tax']  ?? 0.0);
      $shipping_ex   = (float) ($t['shipping_total'] ?? 0.0);
      $shipping_tax  = (float) ($t['shipping_tax']  ?? 0.0);
      $grand_total   = (float) ($t['total']         ?? 0.0); // Woo: grand incl. tax, after discounts

      // Displayed amounts
      $subtotal_disp = $is_reseller ? $subtotal_ex                  : ($subtotal_ex + $subtotal_tax);
      $shipping_disp = $is_reseller ? $shipping_ex                  : ($shipping_ex + $shipping_tax);

      // Per-coupon discount amounts, in display mode (inc tax for non-reseller, ex tax for reseller)
      $discounts = [];
      foreach ($cart ? $cart->get_applied_coupons() : [] as $code) {
         $coupon  = new \WC_Coupon($code);
         $amount  = (float) $cart->get_coupon_discount_amount($code, $inc_tax = !$is_reseller);
         $label   = __('Rabattkod', 'tiburon') . ' “' . wc_format_coupon_code($code) . '”';
         // Optional percent label (e.g., "25%")
         if ($coupon->get_discount_type() === 'percent' && $coupon->get_amount() > 0) {
            $label .= ' ' . wc_format_decimal($coupon->get_amount(), 0) . '%';
         }
         if ($amount > 0) {
            $discounts[] = [
               'code'   => $code,
               'label'  => $label,
               'amount' => $amount, // positive numeric; we’ll print with a minus sign
            ];
         }
      }

      return [
         'is_reseller' => $is_reseller,
         'products'    => $subtotal_disp,
         'shipping'    => $shipping_disp,
         'discounts'   => $discounts,      // array of [label, amount]
         'grand'       => $grand_total,    // Woo grand total (incl. tax)
      ];
   }

   /** Utility: format a number as Woo price. */
   private static function fmt_price(float $n): string {
      return wc_price($n, ['decimals' => wc_get_price_decimals()]);
   }

   /** Render the summary block exactly like your mock (products/shipping/coupons/total). */
   public static function render_totals_summary_html(): string {
      self::ensure_cart_loaded();
      $d = self::get_display_totals();

      ob_start(); ?>
      <div class="cart-summary" id="cart-summary">
         <div class="cart-summary__row">
            <span class="cart-summary__label"><?php esc_html_e('Produkter', 'tiburon'); ?></span>
            <span class="cart-summary__value"><?php echo wp_kses_post(self::fmt_price($d['products'])); ?></span>
         </div>

         <div class="cart-summary__row">
            <span class="cart-summary__label"><?php esc_html_e('Frakt', 'tiburon'); ?></span>
            <span class="cart-summary__value"><?php echo wp_kses_post(self::fmt_price($d['shipping'])); ?></span>
         </div>

         <?php if (!empty($d['discounts'])): ?>
            <?php foreach ($d['discounts'] as $disc): ?>
               <div class="cart-summary__row cart-summary__row--discount">
                  <span class="cart-summary__label"><?php echo esc_html($disc['label']); ?></span>
                  <span class="cart-summary__value">-<?php echo wp_kses_post(self::fmt_price($disc['amount'])); ?></span>
               </div>
            <?php endforeach; ?>
         <?php endif; ?>

         <div class="cart-summary__total">
            <span class="cart-summary__total-label"><?php esc_html_e('Totalt', 'tiburon'); ?></span>
            <span class="cart-summary__total-value"><?php echo wp_kses_post(self::fmt_price($d['grand'])); ?></span>
         </div>
      </div>
<?php
      return ob_get_clean();
   }

   /** Compact, display-ready totals for your summary. */
   private static function get_display_totals_array(): array {
      self::ensure_cart_loaded();
      if (\WC()->cart) {
         \WC()->cart->calculate_totals();
      }

      $is_reseller = self::is_reseller();
      $cart        = \WC()->cart;
      $t           = $cart ? (array) $cart->get_totals() : [];

      // WC raw numbers (subtotal/shipping are ex tax; total is incl tax)
      $subtotal_ex  = (float) ($t['subtotal']       ?? 0.0);
      $subtotal_tax = (float) ($t['subtotal_tax']   ?? 0.0);
      $shipping_ex  = (float) ($t['shipping_total'] ?? 0.0);
      $shipping_tax = (float) ($t['shipping_tax']   ?? 0.0);
      $grand_total  = (float) ($t['total']          ?? 0.0);

      // Display mode
      $products_disp = $is_reseller ? $subtotal_ex            : ($subtotal_ex + $subtotal_tax);
      $shipping_disp = $is_reseller ? $shipping_ex            : ($shipping_ex + $shipping_tax);

      // Coupon discounts (positive numbers we’ll render as negatives)
      $discounts = [];
      if ($cart) {
         foreach ($cart->get_applied_coupons() as $code) {
            $amount = (float) $cart->get_coupon_discount_amount($code, $inc_tax = !$is_reseller);
            if ($amount <= 0) continue;
            $label = __('Rabattkod', 'tiburon') . ' “' . wc_format_coupon_code($code) . '”';
            $c = new \WC_Coupon($code);
            if ($c->get_discount_type() === 'percent' && $c->get_amount() > 0) {
               $label .= ' ' . wc_format_decimal($c->get_amount(), 0) . '%';
            }
            $discounts[] = ['label' => $label, 'amount' => $amount];
         }
      }

      // Optional: compute ÅF-rabatt display (based on your old logic, pure display)
      $reseller_discount = 0.0;
      if ($is_reseller) {
         $pct   = self::get_user_discount_pct();
         $stack = false;
         if ($cart && $pct > 0) {
            foreach ($cart->get_cart() as $cart_item) {
               $pid     = !empty($cart_item['variation_id']) ? (int)$cart_item['variation_id'] : (int)$cart_item['product_id'];
               $qty     = max(1, (int)($cart_item['quantity'] ?? 1));
               $product = wc_get_product($pid);
               if (!$product) continue;

               $eff_pct = self::effective_pct_for($product);
               $base_raw = self::base_price_for($product, $stack);
               $disc_raw = $base_raw * (1 - $eff_pct / 100.0);

               // reseller display is ex VAT
               $before = wc_get_price_excluding_tax($product, ['qty' => $qty, 'price' => $base_raw]);
               $after  = wc_get_price_excluding_tax($product, ['qty' => $qty, 'price' => $disc_raw]);

               $reseller_discount += max(0.0, (float)($before - $after));
            }
         }
      }

      return [
         'is_reseller'       => $is_reseller,
         'products'          => $products_disp,
         'shipping'          => $shipping_disp,
         'discounts'         => $discounts,        // array of [label, amount]
         'reseller_discount' => $reseller_discount, // numeric (ex VAT), 0 if none
         'grand'             => $grand_total,      // Woo final total (incl VAT)
      ];
   }

   /** Small header snippets – counts for the bubble and (N) beside title. */
   public static function counts(): array {
      self::ensure_cart_loaded();
      $count = (int) (\WC()->cart ? \WC()->cart->get_cart_contents_count() : 0);
      return [
         'count'      => $count,
         'count_html' => (string) $count,
         'paren_html' => '(' . $count . ')',
      ];
   }

   /** Resolve current user’s Fortnox pricelist discount percent (0–100). */
   private static function get_user_discount_pct(): float {
      $uid = get_current_user_id();
      if (!$uid) return 0.0;

      $pl  = strtoupper((string) get_user_meta($uid, 'fortnox_price_list', true));
      if ($pl === '') return 0.0;

      $opts = get_option(\Akka\Includes\Fortnox\AdminPage::OPTION_KEY, []);
      $pct  = isset($opts['pricelists'][$pl]['discount_pct']) ? (float) $opts['pricelists'][$pl]['discount_pct'] : 0.0;
      if ($pct < 0)   $pct = 0.0;
      if ($pct > 100) $pct = 100.0;
      return $pct;
   }

   /**
    * Base raw price to discount against.
    * If you want reseller discount to always base on REGULAR price, change the second return to $reg.
    */
   private static function base_price_for(\WC_Product $product, bool $stack_over_sale = false): float {
      $reg  = (float) $product->get_regular_price();
      $sale = $product->get_sale_price() !== '' ? (float) $product->get_sale_price() : null;

      if (!$stack_over_sale && $sale !== null) return $sale;
      if ($stack_over_sale && $sale !== null)  return $sale; // change to $reg to base discount on regular
      return $reg;
   }

   /** Ensure the customer has a taxable location; fall back to shop base for preview. */
   private static function prime_customer_tax_location(): void {
      if (!function_exists('wc_get_base_location')) return;
      if (!\WC()->customer) return;

      $customer = \WC()->customer;

      $has_billing  = (string) $customer->get_billing_country()  !== '';
      $has_shipping = (string) $customer->get_shipping_country() !== '';

      if (!$has_billing && !$has_shipping) {
         $base    = wc_get_base_location();
         $country = $base['country'] ?? '';
         $state   = $base['state'] ?? '';

         if ($country) {
            $customer->set_billing_country($country);
            $customer->set_billing_state($state);
            $customer->set_shipping_country($country);
            $customer->set_shipping_state($state);
            $customer->save();
         }
      }
   }

   private static function get_product_max_discount_pct(\WC_Product $product): ?float {
      $raw = '';
      if ($product->is_type('variation')) {
         $raw = (string) get_post_meta($product->get_id(), '_reseller_max_discount_pct', true);
         if ($raw === '') {
            $parent_id = $product->get_parent_id();
            if ($parent_id) $raw = (string) get_post_meta($parent_id, '_reseller_max_discount_pct', true);
         }
      } else {
         $raw = (string) get_post_meta($product->get_id(), '_reseller_max_discount_pct', true);
      }
      if ($raw === '') return null;
      $v = (float)$raw;
      if ($v < 0) $v = 0.0;
      if ($v > 100) $v = 100.0;
      return $v;
   }

   /** Effective pct for this product (min of user pct and product cap if any). */
   private static function effective_pct_for(\WC_Product $product): float {
      $user_pct = self::get_user_discount_pct();
      if ($user_pct <= 0) return 0.0;
      $cap = self::get_product_max_discount_pct($product);
      return $cap === null ? (float)$user_pct : (float)min($user_pct, $cap);
   }
}
