<?php

namespace Demo\Includes\WooCommerce;

if (!defined('ABSPATH')) {
   exit;
}

class ProductExtra {

   public function __construct() {
      add_action('woocommerce_product_options_general_product_data', [$this, 'add_custom_text_field']);
      add_action('woocommerce_process_product_meta', [$this, 'save_custom_text_field']);
   }

   // Lägg till textarea istället för text input
   public function add_custom_text_field() {
      woocommerce_wp_textarea_input([
         'id' => '_extra_product_text',
         'label' => __('Ingredients', 'your-text-domain'),
         'placeholder' => "Ex: Järn - För blodbildning och syretransport\nVitamin A - För syn, immunförsvar och hud",
         'desc_tip' => true,
         'description' => __('Skriv ingredienser, en per rad.', 'your-text-domain'),
      ]);
   }

   public function save_custom_text_field($post_id) {
      $extra_text = isset($_POST['_extra_product_text']) ? sanitize_textarea_field($_POST['_extra_product_text']) : '';
      update_post_meta($post_id, '_extra_product_text', $extra_text);
   }
}
