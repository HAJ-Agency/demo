<?php

namespace Akka\Includes\WPCLI;

use Akka\Includes\WPCLI\TiburonCLIFunctions;

class TiburonCLI {
   public function __construct() {
   }

   public function work($args = null, $assoc_args = null) {
      $assoc_args = wp_parse_args(
         $assoc_args,
         array(
            'debug' => 'undefined',
            'action' => 'undefined',
            'do' => 'undefined',
         )
      );
      // Do Work!'
      $tiburonWorker = new TiburonCLIFunctions();
      switch ($assoc_args['action']) {
         case 'buildProductJSON':
            if (defined('WP_CLI') && WP_CLI) {
               \WP_CLI::log('Building product JSON...');
               $tiburonWorker->build_or_patch_products_json(true);
            }
            break;
         case 'patchProductJSON':
            if (defined('WP_CLI') && WP_CLI) {
               \WP_CLI::log('Building product JSON...');
               $tiburonWorker->build_or_patch_products_json(false);
            }
            break;
         default:
            if (defined('WP_CLI') && WP_CLI) {
               \WP_CLI::error_multi_line(['INVALID ACTION', $assoc_args['action']]);
            }
            break;
      }
   }
}
