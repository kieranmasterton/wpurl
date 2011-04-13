<?php

class WpAdmin_User {
    
    public static function create ($username, $password, $email)
	{
	    $result = wp_create_user( $username, $password, $email );
	    return $result;
	}
    
}