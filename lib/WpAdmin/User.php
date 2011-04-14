<?php

class WpAdmin_User {
    
    public static function add()
    {
        fwrite(STDOUT, "Username: ");
        $username = trim(fgets(STDIN));    
        fwrite(STDOUT, "Password: ");
        $password = trim(fgets(STDIN));
        fwrite(STDOUT, "Email: ");
        $email = trim(fgets(STDIN));

        $result = wp_create_user($username, $password, $email);
        
        if(TRUE == is_int($result)){
            echo "-- User successfully added.\n";
        }
    }
    
    public static function delete()
    {
        fwrite(STDOUT, "Username: ");
        $username = trim(fgets(STDIN));
        $user = get_user_by('login', $username);
        fwrite(STDOUT, "Are you sure you wish to delete the user with the following email address? (Y/N) " . $user->user_email . ": ");
        $response = trim(fgets(STDIN));
        
        if(TRUE == self::_parseYesNo($response)){
            $result = wp_delete_user($user->ID);
            
            if(TRUE == $result){
                echo "-- User successfully deleted. \n";
            }
        }else{
            echo "-- User deletion cancelled. \n";
        }
    }
    
    public static function role()
    {
        fwrite(STDOUT, "Username: ");
        $username = trim(fgets(STDIN));
        $user = get_user_by('login', $username);
        fwrite(STDOUT, "Role (subscriber, author, editor, administrator): ");
        $role = trim(fgets(STDIN));
            
        $result = wp_update_user(array ('ID' => $user->ID, 'role' => strtolower($role)));
            
        if(TRUE == is_int($result)){
            echo "-- User successfully updated.\n";
        }
    }
    
    public static function rm()
    {
       self::delete();
    }
    
    public static function all()
    {
        $users = get_users();
        foreach($users as $k => $user){
            echo $user->user_login . " - " . $user->user_email . "\n";
        }
    }
    
}