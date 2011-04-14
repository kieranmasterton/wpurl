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
class WpAdmin_User extends WpAdmin
{
    private $_userID = 0;
    private $_userData;
    
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
        $userData = get_user_by('login', $userID);

        if(FALSE == $userData){
            die("[!] Username \"" . $userID ." \" is not valid. \n");
        }else{
            $object->setUserData($userData);
            return $object;
        }
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
        $missingArg = FALSE;
        $required = array('username', 'password', 'email');
        foreach($required as $requiredKey){
           if(!array_key_exists($requiredKey, $options)){
                echo "[!] You must specify --" . $requiredKey . "\n";
                $missingArg = TRUE;
           } 
        }
        
        if (FALSE == $missingArg){ 
            $result = wp_create_user($options['username'], $options['password'], $options['email']);

            if(TRUE == is_int($result)){
                echo "-- User successfully added.\n";
            }else{
                foreach($result->errors as $key => $value){
                    $error = $value[0];
                }
                 echo "[!] " . $error . "\n";
            }
    
            return self::load($result);
        }
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
     * Setter for {@link $_userData}.
     *
     * @access public 
     * @param $userData null|object 
     * @return void
     */
    public function setUserData($userData = null)
    {
        $this->_userData = $userData;
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
    
    /**
     * Getter for {@link $_userID}.
     *
     * @access public 
     * @return integer|object
     */
    public function getUserData()
    {
        return $this->_userData;
    }
    
    
    /**
     * Method for deleting a user
     *
     * @access  public
     * @return  void
     */
    public function delete(array $options = array())
    {
        $userData = $this->getUserData();
        
        fwrite(STDOUT, "Are you sure you wish to delete the user with the following email address? (Y/N) " . $userData->user_email . ": ");
        $response = trim(fgets(STDIN));
        
        if(TRUE == parent::_parseYesNo($response)){
            $result = wp_delete_user($userData->ID);
            
            if(TRUE == $result){
                echo "-- User successfully deleted. \n";
            }
        }else{
            echo "[x] User deletion cancelled. \n";
        }
    }
    
    /**
     * Method for updating a user
     *
     * @access  public
     * @return  void
     */
    public function update(array $options = array())
    {
        $userData = $this->getUserData();
        
        $roles = array('subscriber', 'author', 'editor','administrator');
        foreach($roles as $requiredValue){
            if($requiredValue != $options['role']){        
                if(FALSE == $missingArg){
                    continue;
                }
                $missingArg = TRUE;
            }else{
                $missingArg = FALSE;
            }
        }
        
        if(FALSE == $missingArg){    
            $result = wp_update_user(array ('ID' => $userData->ID, 'role' => strtolower($options['role'])));
            if(TRUE == is_int($result)){
                echo "-- User successfully updated.\n";
            }
        }else{
            echo "[!] You must specify a valid role: subscriber, author, editor, administrator.\n";
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