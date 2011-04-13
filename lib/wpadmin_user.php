<?php

class WpAdmin_User {
    
    public static function create ($username, $password, $email)
	{
	    $result = wp_create_user($username, $password, $email);
	    return $result;
	}
	
	public static function delete($id){
	    $result = wp_delete_user($id);
	    return $result;
	}
	
	public static function updateRole($id, $role){
	   $result = wp_update_user(array ('ID' => $id, 'role' => $role));
	   return $result;
	}
	
	public static function all(){
	    $result = get_users();
	    return $result;
	}
    
}