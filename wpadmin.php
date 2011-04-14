#!/usr/bin/env php
<?php

/**
 * @file
 * wpadmin is a PHP script implementing a command line shell for WordPress.
 *
 * @requires PHP CLI 5.2.0, or newer.
 */
 
ob_start("strip_tags");

define('WPADMIN_BASE_PATH', dirname(__FILE__));
define('WPADMIN_LIBS_PATH', dirname(__FILE__) . '/lib');

if(TRUE == is_readable('wp-load.php')){
    # Load wordpress libs
    require_once('wp-load.php');
    require_once(ABSPATH . WPINC . '/template-loader.php');
    require_once(ABSPATH . 'wp-admin/includes/admin.php');

    # Load wpdmin libs
    require_once WPADMIN_LIBS_PATH . '/WpAdmin.php';
    require_once WPADMIN_LIBS_PATH . '/WpAdmin/User.php';
    require_once WPADMIN_LIBS_PATH . '/WpAdmin/Option.php';

    WpAdmin::exec($argv);
}else{
    die("Either this is not a WordPress document root or you do not have permission to administer this site. \n");
}

ob_end_flush();