<?php

namespace Akka\Includes\BlockStyles;

// is possible to register in separate json file with the styles as well 
// Add link button with arrow to the left
register_block_style(
   'core/button',
   array(
      'name'  => 'outline-black',
      'label' => __('Outline Black', "akka"),
      'is_default' => true,
   )
);
register_block_style(
   'core/button',
   array(
      'name'  => 'outline-white',
      'label' => __('Outline White', "akka"),
   )
);
register_block_style(
   'core/button',
   array(
      'name'    => 'fill-black',
      'label'   => __('Black', "akka"),
      
   )
);
register_block_style(
   'core/button',
   array(
      'name'  => 'fill-white',
      'label' => __('White', "akka"),
   )
);
register_block_style(
   'core/button',
   array(
      'name'  => 'menu-btn',
      'label' => __('Menu Button', "akka"),
   )
);
