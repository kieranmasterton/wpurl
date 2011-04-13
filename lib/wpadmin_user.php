<?php

class WpAdmin_User {
    
    public static function create ($username, $password, $email)
	{
	    $result = wp_create_user( $username, $password, $email );
	    return $result;
	}
	
	public static function delete($id){
	    $result = wp_delete_user($id);
	    return $result;
	}
    
}