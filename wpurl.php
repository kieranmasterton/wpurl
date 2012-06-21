#!/usr/bin/env php
<?php

/**
 * wpurl
 *
 * Coded to Zend's coding standards:
 * http://framework.zend.com/manual/en/coding-standard.html
 *
 * File format: UNIX
 * File encoding: UTF8
 * File indentation: Spaces (4). No tabs
 *
 * @copyright  Copyright (c) 2012 88mph. (http://88mph.com)
 * @license    GNU
 */

/**
 * @file
 * wpurl is a PHP script implementing a command line shell for WordPress.
 *
 * @requires PHP CLI 5.2.0, or newer.
 * @since 0.0.1
 */
 
// Start output buffering to stop WordPress from spitting out its usual output.
ob_start("strip_tags");

define('WPURL_BASE_PATH', dirname(__FILE__));
define('WPURL_LIB_PATH', dirname(__FILE__) . '/lib');

// Does the user have access to read the directory? If so, allow them to use the
// command line tool.
if(true == is_readable('wp-config.php')){
    
    // Load WordPress libs.
    $wpconfig = file_get_contents('wp-config.php');

    // Load wpdmin libs.
    require_once WPURL_LIB_PATH . '/WpUrl.php';

    // Run WpUrl::exec() method, pass in user input and wpconfig content.
    WpUrl::exec($argv, $wpconfig);
    
}else{
    die("Either this is not a WordPress document root or you do not have permission to read these site files. \n");
}

ob_end_flush();