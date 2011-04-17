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
 * @category   Option
 * @copyright  Copyright (c) 2011 88mph. (http://88mph.co)
 * @license    GNU
 */
 
/**
 * The WpAdmin_Option class provides methods for dealing with the administering
 * of the WordPress options.
 * 
 * @example
 * 
 *      $ wpadmin option [command] --[option_name]="[value]"
 *
 *      $ wpadmin option add --blogname="My Cool Blog"
 *      $ wpadmin option update --siteurl="http://my-cool-blog.com"
 * 
 *      $ wpadmin option delete --option=[option_name]
 * 
 * @since 0.0.1
 * @author  Jon Reeks http://twitter.com/jonreeks
 */
class WpAdmin_Option extends WpAdmin
{
    /**
     * Option name.
     *
     * A valid option_name value.(Options database table column)
     *
     * @access private
     * @see WpAdmin_Option::setOptionName()
     * @see WpAdmin_Option::getOptionName()
     * @param  string
     */
    private $_optionName = null;
    
    /**
     * Array of option data.
     *
     * @access private
     * @see WpAdmin_Option::setOptionData()
     * @see WpAdmin_Option::getOptionData()
     * @param  array
     */
    private $_optionData = array();
    
    /**
     * Class constructor.
     *
     * @access private
     * @param $optionName null|integer
     * @return WpAdmin_Option
     */
    private function __construct($optionName = null)
    {
        $this->setOptionName($optionName);
    }
    
    /**
     * Factory method for creating an instance of WpAdmin_Option.
     *
     * @static
     * @access public
     * @param $optionName null|integer
     * @return My_Class
     */
    static public function load($optionName)
    {
        $object = new WpAdmin_Option($optionName);
        
        $object->setOptionData($object->queryData($optionName));
        
        return $object;
    }
    
    /**
     * Factory method for inserting a new "option" into the database and then
     * creating an instance of WpAdmin_Option.
     *
     * @static
     * @access  public
     * @param   $key    string  Option key
     * @param   $value  string  Option value
     * @return  WpAdmin_Option
     */
    static public function add($bind)
    {
        unset($bind['primary']);
        
        if(empty($bind)){
            echo '[!] To add an option you must specify an option\'s key & value in the following format:';
            echo "\n\n\t";
            echo 'wpadmin add option --{option_key}={option_value}';
            echo "\n\n";
            return;
        }
        
        // Add the option.
        foreach($bind as $key => $value){
            $res = add_option($key, $value);
            if (true === $res){
                echo "-- Option '" . $key . "' successfully added.\n";
            }else{
                echo "[!] Option '" . $key . "' not added. Does it already exist?\n";
            }
            return self::load($key);
        }
    }
    
    /**
     * Factory method for inserting a new "option" into the database and then
     * creating an instance of WpAdmin_Option.
     *
     * @static
     * @access  public
     * @param   $bind array
     * @return  void
     */
    public function update($bind)
    {
        unset($bind['primary']);
        
        if(empty($bind)){
            echo '[!] To update an option you must specify an option\'s key & value in the following format:';
            echo "\n\n\t";
            echo 'wpadmin update option --{option_key}={option_value}';
            echo "\n\n";
            return;
            return;
        }
        
        // Add the option.
        foreach($bind as $key => $value){
            $res = update_option($key, $value);
            if (true === $res){
                echo "-- Option '" . $key . "' successfully updated.\n";
            }else{
                echo "[!] Option '" . $key . "' not updated. Does it exist? Is the value already '" . $value . "'? \n";
            }
        }
    }
    
    public function delete($bind)
    {
        unset($bind['primary']);
        
        if(empty($bind)){
            echo '[!] To delete an option you must specify the option\'s key in the following format:';
            echo "\n\n\t";
            echo 'wpadmin delete option --{option_key}';
            echo "\n\n";
            return;
            return;
        }
        
        // Add the option.
        foreach($bind as $key => $value){
            $res = delete_option($value);
            if (true === $res){
                echo "-- Option '" . $value . "' successfully deleted.\n";
            }else{
                echo "[!] Option '" . $value . "' not deleted. Does it exist?\n";
            }
        }
    }
    
    /**
     * Setter for {@link $_optionName}.
     *
     * @access public 
     * @param $optionName null|integer 
     * @return void
     */
    public function setOptionName($optionName = null)
    {
        $this->_optionName = $optionName;
    }
    
    /**
     * Getter for {@link $_optionName}.
     *
     * @access public 
     * @return integer|null
     */
    public function getOptionName()
    {
        return $this->_optionName;
    }
    
    /**
     * Setter for {@link $_optionData}.
     *
     * @access public
     * @param $data array
     * @return void
     */
    public function setOptionData($optionData = array())
    {
        $this->_optionData = $optionData;
    }
    
    /**
     * Getter for {@link $_optionData}.
     *
     * @access public
     * @return void
     */
    public function getOptionData()
    {
        return $this->_optionData;
    }
    
    public function queryData($optionName)
    {
        global $wpdb;
        
        $stmt  = 'SELECT * ';
        $stmt .= 'FROM `' . $wpdb->options . '` ';
        $stmt .= 'WHERE `option_name` = \'' . mysql_real_escape_string($optionName) . '\' ';
        $stmt .= 'LIMIT 1';
        
        return $wpdb->get_row($stmt, ARRAY_A);
    }
}