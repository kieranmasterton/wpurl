#!/usr/bin/env php
<?php

/**
 * @file
 * wpadmin is a PHP script implementing a command line shell for WordPress.
 *
 * @requires PHP CLI 5.2.0, or newer.
 */

define('WPADMIN_BASE_PATH', dirname(__FILE__));
define('WPADMIN_LIBS_PATH', dirname(__FILE__) . '/lib');

# Load wordpress libs
require_once( 'wp-load.php' );
require_once( ABSPATH . WPINC . '/template-loader.php' );
require_once( ABSPATH . 'wp-admin/includes/admin.php');

# Load wpdmin libs
require_once WPADMIN_LIBS_PATH . '/wpadmin_user.php';

require_once WPADMIN_LIBS_PATH . '/wpadmin.php';

WpAdmin::exec($GLOBALS['argv']);