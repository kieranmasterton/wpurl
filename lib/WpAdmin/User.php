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
 * @author  Kieran Masterton http://twitter.com/kieranmasterton
 */
 
class WpAdmin_User extends WpAdmin
{
    /**
     * Username.
     *
     * A valid username value.(Options database table column)
     *
     * @access private
     * @see WpAdmin_User::setUsername()
     * @see WpAdmin_User::getUsername()
     * @param  string
     */
    private $_username;
    
    /**
     * User object of data returned from the database.
     *
     * @access private
     * @see WpAdmin_User::setData()
     * @see WpAdmin_User::getData()
     * @param  array
     */
    private $_data = false;
    
    /**
     * Class constructor.
     *
     * @access private
     * @param $username null|integer
     * @return WpAdmin_User
     */
    private function __construct($username = null)
    {
        $this->setUsername($username);
    }
    
    /**
     * Factory method for creating an instance of WpAdmin_User.
     *
     * @static
     * @access public
     * @param $username null|integer
     * @return My_Class
     */
    static public function load($username, stdClass $data = null)
    {
        if(NULL == $username){
            die("[!] You must specify a username.\n");
        }
        
        $object = new WpAdmin_User($username);
        
        // If we havent been passeed an array of option data then query the
        // database for it. Otherwise just set it from whats given.
        if (!is_a($data, 'stdClass')){
            $object->setData($object->queryData($username));
            
            if(false == $object->getData()){
                die("[!] Username \"" . $username ."\" is not valid. \n");
            }
            
        }else{
            $object->setData($data);
        }
        
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
                return self::load($options['username']);
            }else{
                foreach($result->errors as $key => $value){
                    $error = $value[0];
                }
                 die("[!] " . $error . "\n");
                 
            }
        }
    }
    
    /**
     * Fetchs all users from the WordPress database & then returns them 
     * as an array of WpAdmin_User objects.
     *
     * @example
     *  
     *      $users = WpAdmin_User::listAll();
     *      foreach($users as $user){
     *          echo $user->getUsername();
     *      }
     *
     * @static
     * @access  public
     * @return  array   Array of WpAdmin_User objects.
     */
    static public function listAll()
    {
        $users = self::queryAllData();
        
        $base = array();
        
        foreach ($users as $user){
            $base[] = self::load($user->user_login, $user);
        }
        
        return $base; 
    }
    
    /**
     * Setter for {@link $_username}.
     *
     * @access public 
     * @param $username null|integer 
     * @return void
     */
    public function setUsername($username = null)
    {
        $this->_username = $username;
    }
    
    /**
     * Getter for {@link $_username}.
     *
     * @access public 
     * @return integer|null
     */
    public function getUsername()
    {
        return $this->_username;
    }
    
    /**
     * Setter for {@link $_data}.
     *
     * @access public 
     * @param $data null|object 
     * @return void
     */
    public function setData($data = null)
    {
        $this->_data = $data;
    }
    
    /**
     * Getter for {@link $_username}.
     *
     * @access public 
     * @return integer|object
     */
    public function getData()
    {
        return $this->_data;
    }
    
    
    /**
     * Method for deleting a user
     *
     * @access  public
     * @return  void
     */
    public function delete(array $options = array())
    {
        $data = $this->getData();
        
        fwrite(STDOUT, "Are you sure you wish to delete the user with the following email address? (Y/N) " . $data->user_email . ": ");
        $response = trim(fgets(STDIN));
        
        if(TRUE == parent::_parseYesNo($response)){
            $result = wp_delete_user($data->ID);
            
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
        // Get user data.
        $data = $this->getData();
        
        // These aren't needed.
        unset($options['username'], $options['primary']);

        // Check to make sure some options have been submitted.
        if(!empty($options)){
            
            $updateFields = array('ID' => $data->ID);
            
            // Loop through options.
            foreach($options as $key => $value){
                // Switch gears based on key.
                switch($key){
                    case 'role':
                        $validRoles = array('subscriber', 'author', 'editor','administrator');
                        if(!in_array($options['role'], $validRoles)){
                            $missingArg = TRUE;
                            $error = "[!] You must specify a valid role: subscriber, author, editor, administrator. \n";
                        }else{
                            $missingArg = FALSE;
                            $updateFields[$key] = $value;
                        }
                    break;
                    case 'email':
                        if(NULL == $value){
                            $missingArg = TRUE;
                            $error = "[!] You must specify a value i.e. --" . $key ."={value}\n";
                        }else{
                            $missingArg = FALSE;
                            $updateFields['user_email'] = $value;
                        }
                    break;
                    case 'password':
                        if(NULL == $value){
                            $missingArg = TRUE;
                            $error = "[!] You must specify a value i.e. --" . $key ."={value}\n";
                        }else{
                            $missingArg = FALSE;
                            $updateFields['user_pass'] = $value;
                        }
                    break;
                    default:
                        if(NULL == $value){
                            $missingArg = TRUE;
                            $error = "[!] You must specify a value i.e. --" . $key ."={value}\n";
                        }else{
                            $missingArg = FALSE;
                            $updateFields[$key] = $value;
                        }
                    break;
                }
            }
            
            // Check for missing arguments and commit changes to the db.
            if(FALSE == $missingArg){    
                $result = wp_update_user($updateFields);
                if(TRUE == is_int($result)){
                    echo "-- User successfully updated.\n";
                }
            }else{
                echo $error;
            }
            
        }else{
            die("[!] You must specify a param to update. \n");
        }
    }
    
    public static function rm()
    {
       self::delete();
    }
    
    /**
     * Queries the WordPress user table for a user with a username the
     * same the $username.
     *
     * @access  public
     * @param   string  $username
     * @return  array
     */
    public function queryData($username)
    {
        return get_user_by('login', $username);
    }
    
    /**
     * Queries the WordPress user table for all users.
     *
     * @access  public
     * @return  array
     */
    public function queryAllData()
    {
        return get_users();
    }
    
    /**
     * Returns an array of column headings that match the order of 
     * .WpAdmin_User::queryAllData(). This is used for the tabular
     * output of $ wpadmin user list.
     *
     * @access  public
     * @return  array
     */
    public function getColumnHeaders()
    {
        $base = array('ID', 
                        'user_login (username)',
                        'user_pass',
                        'user_nicename',
                        'user_email',
                        'user_url',
                        'user_registered',
                        'user_activation_key',
                        'user_status', 
                        'display_name');
        
        return $base;
    }
    
}