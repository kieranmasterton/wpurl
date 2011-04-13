<?php

require_once WPADMIN_LIBS_PATH . '/wpadmin_user.php';

class Wpadmin {
    
    public static function loadEnv ()
	{
		require_once( 'wp-load.php' );
        require_once( ABSPATH . WPINC . '/template-loader.php' );
	}
    
}