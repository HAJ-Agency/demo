<?php

namespace Demo\Includes\BlockStyles;

// is possible to register in separate json file with the styles as well
// Add link button with arrow to the left
register_block_style(
   'core/button',
   array(
      'name'  => 'outline-black',
      'label' => __('Outline Black', "demo"),
      'is_default' => true,
   )
);
register_block_style(
   'core/button',
   array(
      'name'  => 'outline-white',
      'label' => __('Outline White', "demo"),
   )
);
register_block_style(
   'core/button',
   array(
      'name'    => 'fill-black',
      'label'   => __('Black', "demo"),

   )
);
register_block_style(
   'core/button',
   array(
      'name'  => 'fill-white',
      'label' => __('White', "demo"),
   )
);
register_block_style(
   'core/button',
   array(
      'name'  => 'menu-btn',
      'label' => __('Menu Button', "demo"),
   )
);
