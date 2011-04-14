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
 * @category   User
 * @copyright  Copyright (c) 2011 88mph. (http://88mph.co)
 * @license    GNU
 */
 
/**
 * The WpAdmin_User class provides methods for dealing with the administration
 * of WordPress users.
 * 
 * $ wpadmin user add --username=steve --password=s0m3t1ngSecUr3 --email=bob@example.com
 * 
 * @since 0.0.1
 */
class WpAdmin_User
{
    private $_userID = 0;
    
/**
     * Class constructor.
     *
     * @access private
     * @param $userID null|integer
     * @return WpAdmin_User
     */
    private function __construct($userID = null)
    {
        $this->setUserID($userID);
    }
    
    /**
     * Factory method for creating an instance of WpAdmin_User.
     *
     * @static
     * @access public
     * @param $userID null|integer
     * @return My_Class
     */
    static public function load($userID)
    {
        $object = new WpAdmin_User($userID);
        
        return $object;
    }
    
    /**
     * Factory method for inserting a new user into the database and then
     * creating an instance of WpAdmin_User.
     *
     * @static
     * @access  public
     * @param   $options    array  Options array of key value pairs
     * @return  WpAdmin_User
     */
    static public function add($options)
    {
        $required = array('username', 'password', 'email');
        foreach($required as $requiredKey){
           if(!in_array($requiredKey, $options)){
                echo "You must specify --" . $requiredKey . "\n";
           } 
        }
        
        $result = wp_create_user($options['username'], $options['password'], $options['email']);
        
        if(TRUE == is_int($result)){
            echo "-- User successfully added.\n";
        }
    
        return self::load($result);
    }
    
    /**
     * Setter for {@link $_userID}.
     *
     * @access public 
     * @param $userID null|integer 
     * @return void
     */
    public function setUserID($userID = null)
    {
        $this->_userID = $userID;
    }
    
    /**
     * Getter for {@link $_userID}.
     *
     * @access public 
     * @return integer|null
     */
    public function getUserID()
    {
        return $this->_userID;
    }
    
    public function delete()
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