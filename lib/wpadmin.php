<?php

class WpAdmin {
    
    public static function exec ($input)
	{
	    $method = '_' . $input[1] . '_' . $input[2];
        if(method_exists(__CLASS__, $method)){
            self::$method();
        }else{
            echo self::displayHelp();
        }
    }
    
    private static function _add()
    {
        echo "What would you like to add? \n";
    }
    
    private static function _add_user()
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
    
    private static function _delete_user()
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
    
    public static function displayHelp()
    {

$str = <<<EOF
Usage: wpadmin [options]

Options:
    add user        You will be prompted for username, email address and password.
    delete user     You will be prompted for a username.


EOF;
            
        echo $str;
    }
    
}