<?php

class WpAdmin {
    
    public static function exec ($input)
	{
	    $function = '_' . $input;
        self::$function();
    }
    
    private static function _add()
    {
        echo "What would you like to add? \n";
    }
    
    private static function _adduser()
    {
        fwrite(STDOUT, "Username: ");
        $username = trim(fgets(STDIN));    
        fwrite(STDOUT, "Password: ");
        $password = trim(fgets(STDIN));
        fwrite(STDOUT, "Email: ");
        $email = trim(fgets(STDIN));

        $wpadmin_user = new WpAdmin_User();
        $result = $wpadmin_user->create($username, $password, $email);
        
        if(TRUE == is_int($result)){
            echo "-- User successfully added.\n";
        }
    }
    
    private static function _deleteuser()
    {
        fwrite(STDOUT, "Username: ");
        $username = trim(fgets(STDIN));
        $user = get_user_by('login', $username);
        fwrite(STDOUT, "Are you sure you wish to delete the user with the following email address? (Y/N) " . $user->user_email . ": ");
        $response = trim(fgets(STDIN));
        
        if(TRUE == self::_parseYesNo($response)){
            $wpadmin_user = new WpAdmin_User();
            $result = $wpadmin_user->delete($user->ID);
            
            if(TRUE == $result){
                echo "-- User successfully deleted. \n";
            }
        }else{
            echo "-- User deletion cancelled. \n";
        }
    }
    
    private static function _parseYesNo($response)
    {
        if('yes' == strtolower($response) || 'y' == strtolower($response)){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
}