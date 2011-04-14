<?php

class WpAdmin {
    
    public static function exec($input)
	{
	    $class = 'WpAdmin_' . ucfirst($input[1]);
	    $method = $input[2];
	    
        if(method_exists($class, $method)){
            $class::$method();
        }else{
            self::displayHelp();
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