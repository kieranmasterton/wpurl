<?php

class WpAdmin {
    
    public static function parseUserInput ($input)
	{
	    switch ($input[1]) {
        case 'adduser':
            self::_addWpUser();
            break;
        }

    }
    
    private static function _addWpUser()
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
    
}