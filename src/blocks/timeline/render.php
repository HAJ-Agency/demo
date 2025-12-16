<?php

// Extract attributes
$block_id = isset($attributes['blockId']) ? $attributes['blockId'] : '';
$main_class_name = isset($attributes['mainClassName']) ? $attributes['mainClassName'] : '';

?>
<div id="<?php echo esc_attr($block_id); ?>" class="<?php echo esc_attr($main_class_name); ?>">
   <!-- <svg class="circle" xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="transparent">
      <circle cx="12" cy="12.7246" r="11.5" stroke="black" />
   </svg> -->
   <svg class="circle" width="20" height="20" viewBox="0 0 20 20" fill="transparent" xmlns="http://www.w3.org/2000/svg">
      <circle cx="10" cy="10" r="9.5" stroke="black" />
   </svg>
   <svg class="line" width="1" height="70" viewBox="0 0 1 70" fill="transparent" xmlns="http://www.w3.org/2000/svg">
      <line x1="0.5" y1="0.724609" x2="0.499996" y2="69.725" stroke="black" />
   </svg>
</div>