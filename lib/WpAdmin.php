<?php

/**
 * wpadmin
 *
 * Coded to Zend's coding standards:
 * http://framework.zend.com/manual/en/coding-standard.html
 *
 * File format: UNIX
 * File encoding: UTF8
 * File indentation: Spaces (4). No tabs
 *
 * @copyright   Copyright (c) 2011 88mph. (http://88mph.co)
 * @license     GNU
 * @author      88mph http://88mph.co
 */
 
/**
 * The WpAdmin class provides methods for parsing user input and instantiating
 * the required class.
 *
 * @since   0.0.1
 * @author  Kieran Masteron http://twitter.com/kieranmasterton
 */
class WpAdmin
{
    /**
     * Class name to instantiate.
     *
     * @static
     * @access private
     * @param  string
     */
    private static $_className;
    
    /**
     * Method name to instantiate.
     *
     * @static
     * @access private
     * @param  string
     */
    private static $_methodName;
    
    /**
     * Params to pass to class method.
     *
     * @static
     * @access private
     * @param  array
     */
    private static $_params = array();
    
    /**
     * Function invoked from prompt.
     *
     * @static
     * @access  public
     * @param   $args array
     * @return  void
     */
    public static function exec($args)
    {
        // Parse user input create set class properties.
        self::_parseOptions($args);

        // Check class exists and instantiate object & call method.
        $class      = self::$_className;
        $method     = self::$_methodName;
        
        if(method_exists($class, $method)){
            if('add' != $method){
                $object = $class::load(self::$_params['primary']);
                $object->$method(self::$_params);
            }else{
                $object = $class::add(self::$_params);
            }
        }else{
            // Else class / method not found, display help.
            self::displayHelp();
        }
    }
    
    /**
     * Parse the arguments send from the command line.
     *
     * @static
     * @access  private
     * @param   $args array
     * @return  void
     */
    private static function _parseOptions($args)
    {
        // Set class name and method name based on first & second user input.
        self::$_className = 'WpAdmin_' . ucfirst($args[1]);
        self::$_methodName = strtolower($args[2]);
        
        // Unset no longer required user input.
        unset($args[0], $args[1], $args[2]);
        
        // Special conditional checks for user commands.
        if('WpAdmin_User' == self::$_className && 'add' == self::$_methodName){
            if(preg_match('/^--.*$/', $args[3])){
                echo "Usage: wpadmin user add steve --password=password --email=steve@example.com\n";
                die("[!] You must specify a valid username\n");
            }else{
                self::$_params['username'] = $args[3];
                unset($args[3]);
            }

        }
        
        // Loop through user input create array of key value pairs.
        foreach($args as $k => $value){
            $pair = preg_split('/=/',$value);
            $pair[0] = substr($pair[0], 2);
            self::$_params[$pair[0]] = $pair[1];
        }
        
        // Special circumstances for primary key for load lookup.
        switch (self::$_className) {
            case 'WpAdmin_User':
                self::$_params['primary'] = self::$_params['username'];
                break;
            default:
                $firstKey = array_keys(self::$_params);
                self::$_params['primary'] = self::$_params[$firstKey[0]];
                break;
        }
        
        // Debug.
        //var_dump(self::$_params); exit;
    }
    
    /**
     * Determines whether the user has said "yes" or "no" to a question prompt on
     * the command line.
     *
     * @static
     * @access  public
     * @param   $args array
     * @return  void
     */
    public static function _parseYesNo($response)
    {
        if('yes' == strtolower($response) || 'y' == strtolower($response)){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Displays end-user help.
     *
     * @static
     * @access  public
     * @return  string
     */
    public static function displayHelp()
    {

$str = <<<EOF
Usage: wpadmin [options]

Options:
add user You will be prompted for username, email address and password.
delete user You will be prompted for a username.


EOF;
            
        echo $str;
    }
    
}