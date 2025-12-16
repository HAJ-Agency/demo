<?php

namespace Akka\Includes\WPCLI;

// use WP_CLI
use Akka\Includes\WPCLI\TiburonCLI;

if (defined('WP_CLI')) {
   \WP_CLI::add_command('tiburon', new TiburonCLI());
}
