<?php

namespace Demo\Includes\Functions;

// Register Custom Post Type
add_action('init', __NAMESPACE__ . '\register_reseller_post_type');
function register_reseller_post_type() {
   register_post_type('reseller', [
      'labels' => [
         'name' => __('Reseller', 'tiburon'),
         'singular_name' => __('Reseller', 'tiburon'),
      ],
      'public' => true,
      'has_archive' => false,
      'publicly_queryable' => false,
      'show_in_rest' => false, // Disable Gutenberg
      'supports' => ['title'],
      'rewrite' => false,
      'menu_icon' => 'dashicons-store',
   ]);
}

// Register Taxonomy 'City'
add_action('init', __NAMESPACE__ . '\register_city_taxonomy');
function register_city_taxonomy() {
   register_taxonomy('city', 'reseller', [
      'labels' => [
         'name' => 'Cities',
         'singular_name' => 'City',
      ],
      'hierarchical' => false,
      'public' => true,
      'show_ui' => true,
      'show_in_rest' => false,
   ]);
}

// Add Meta Box
add_action('add_meta_boxes', __NAMESPACE__ . '\add_reseller_meta_box');
function add_reseller_meta_box() {
   add_meta_box(
      'reseller_stores_repeater',
      'Butiker',
      'render_reseller_meta_box',
      'reseller',
      'normal',
      'default'
   );
}

function render_reseller_meta_box($post) {
   wp_nonce_field('save_reseller_meta', 'reseller_meta_nonce');
   $stores = get_post_meta($post->ID, 'stores', true) ?: [];
   $cities = get_terms(['taxonomy' => 'city', 'hide_empty' => false]);

   echo '<div id="repeater-container">';

   foreach ($stores as $index => $store) {
?>
      <div class="repeater-item">
         <input type="text" name="stores[<?php echo $index; ?>][store_name]" value="<?php echo esc_attr($store['store_name'] ?? ''); ?>" placeholder="Butiksnamn" />
         <select name="stores[<?php echo $index; ?>][city]">
            <option value="">Välj stad</option>
            <?php foreach ($cities as $city): ?>
               <option value="<?php echo $city->term_id; ?>" <?php selected($store['city'], $city->term_id); ?>>
                  <?php echo esc_html($city->name); ?>
               </option>
            <?php endforeach; ?>
         </select>
         <input type="text" name="stores[<?php echo $index; ?>][address]" value="<?php echo esc_attr($store['address'] ?? ''); ?>" placeholder="Adress" />
         <input type="text" name="stores[<?php echo $index; ?>][phone]" value="<?php echo esc_attr($store['phone'] ?? ''); ?>" placeholder="Telefonnummer" />
         <input type="email" name="stores[<?php echo $index; ?>][email]" value="<?php echo esc_attr($store['email'] ?? ''); ?>" placeholder="Email" />
         <input type="text" name="stores[<?php echo $index; ?>][contact_person]" value="<?php echo esc_attr($store['contact_person'] ?? ''); ?>" placeholder="Kontaktperson" />
         <input type="url" name="stores[<?php echo $index; ?>][website]" value="<?php echo esc_attr($store['website'] ?? ''); ?>" placeholder="Hemsida" />
         <button class="remove-repeater-item">Ta bort</button>
      </div>
   <?php
   }

   echo '</div>';
   echo '<button id="add-repeater-item">Lägg till butik</button>';
   ?>
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const container = document.getElementById('repeater-container');
         const addBtn = document.getElementById('add-repeater-item');

         addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const index = container.children.length;
            const newItem = document.createElement('div');
            newItem.classList.add('repeater-item');

            newItem.innerHTML = `
                    <input type="text" name="stores[${index}][store_name]" placeholder="Butiksnamn" />
                    <select name="stores[${index}][city]">
                        <option value="">Välj stad</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city->term_id; ?>"><?php echo esc_html($city->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="stores[${index}][address]" placeholder="Adress" />
                    <input type="text" name="stores[${index}][phone]" placeholder="Telefonnummer" />
                    <input type="email" name="stores[${index}][email]" placeholder="Email" />
                    <input type="text" name="stores[${index}][contact_person]" placeholder="Kontaktperson" />
                    <input type="url" name="stores[${index}][website]" placeholder="Hemsida" />
                    <button class="remove-repeater-item">Ta bort</button>
                `;

            container.appendChild(newItem);
         });

         container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-repeater-item')) {
               e.preventDefault();
               e.target.parentElement.remove();
            }
         });
      });
   </script>
   <style>
      .repeater-item {
         margin-bottom: 15px;
         padding: 10px;
         border: 1px solid #ccc;
      }

      .repeater-item input,
      .repeater-item select {
         display: block;
         margin-bottom: 5px;
         width: 100%;
      }
   </style>
<?php
}

// Save Meta Data
add_action('save_post', __NAMESPACE__ . '\save_reseller_meta');
function save_reseller_meta($post_id) {
   if (!isset($_POST['reseller_meta_nonce']) || !wp_verify_nonce($_POST['reseller_meta_nonce'], 'save_reseller_meta')) {
      return;
   }

   if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
   if (!current_user_can('edit_post', $post_id)) return;

   if (isset($_POST['stores']) && is_array($_POST['stores'])) {
      $stores = array_values(array_filter($_POST['stores'], function ($store) {
         return !empty($store['store_name']) || !empty($store['city']) || !empty($store['address']);
      }));

      // Add city name to each entry
      foreach ($stores as &$store) {
         if (!empty($store['city'])) {
            $term = get_term($store['city'], 'city');
            if ($term && !is_wp_error($term)) {
               $store['city_name'] = $term->name;
            }
         }
      }

      update_post_meta($post_id, 'stores', $stores);
   } else {
      delete_post_meta($post_id, 'stores');
   }
}
