<?php

namespace Akka\Includes\WooCommerce;

if (!defined('ABSPATH')) {
   exit;
}

add_action('init', function () {
   // if woocommerce is activated
   if (class_exists('WooCommerce')) {
      // Init our classes
      new ProductAccordionFields();
      new ProductExtra();
      // new ResellerPriceDisplay();
   }
   delete_transient('wc_stripe_appearance');
});
add_filter('wc_stripe_upe_params', function ($params) {
   $params['appearance'] = [
      'variables' => [
         'fontFamily' => 'Helvetica Neue',
      ]
   ];
   return $params;
});

// if (is_admin()) {
//    add_action('admin_init', function () {
//       // Load only in wp-admin
//       new ResellerUserField();
//       new ResellerProductField();
//    });
// }
