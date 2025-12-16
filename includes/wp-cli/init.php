<?php

namespace Demo\Includes\WPCLI;

// use WP_CLI
use Demo\Includes\WPCLI\TiburonCLI;

if (defined('WP_CLI')) {
   \WP_CLI::add_command('tiburon', new TiburonCLI());
}
