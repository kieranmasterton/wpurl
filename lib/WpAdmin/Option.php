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
 *      To work with an existing option: 
 *      $option = WpAdmin_Option::load('siteurl');
 *      echo $option->getOptionData();
 * 
 *      To create a new option:
 *      $option = WpAdmin_Option::add(array('my_new_option' => 'My New Option Value'));
 *      echo $option->getOptionData();
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
     * Array of option data returned from the database.
     *
     * @access private
     * @see WpAdmin_Option::setOptionData()
     * @see WpAdmin_Option::getOptionData()
     * @param  array
     */
    private $_optionData = array();
    
    /**
     * Class constructor. Private for factory method.
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
     * Factory method for loading an existing option from the WordPress database 
     * and then creating & returning an instance of WpAdmin_Option.
     *
     * @example
     *  
     *      $option = WpAdmin_Option::load('blogname');
     *      echo $option->getOptionData();
     *
     * @static
     * @access  public
     * @param   string  $optionName     option_name to load.
     * @param   array   $optionData     array of option data to set against
     *                                  @link WpAdmin_Option::_optionData for
     *                                  use with WpAdmin_Option::getOptionData()
     *                                  If this parameter is omitted then the data
     *                                  is fetched from the database.
     * @return  WpAdmin_Option
     */
    static public function load($optionName, array $optionData = array())
    {
        $object = new WpAdmin_Option($optionName);
        
        if (empty($optionData)){
            $object->setOptionData($object->queryOptionData($optionName));
        }else{
            $object->setOptionData($optionData);
        }
        
        return $object;
    }
    
    /**
     * Factory method for inserting a new option into the WordPress database and 
     * then creating & returning an instance of WpAdmin_Option.
     *
     * @example
     *  
     *      $option = WpAdmin_Option::add(array('option_name' => 'option_value'))
     *
     * @static
     * @access  public
     * @param   array   $bind   In array('option_name' => 'option_value') format.
     * @return  WpAdmin_Option
     */
    static public function add($bind)
    {
        unset($bind['primary']);
        
        if(empty($bind)){
            echo '[!] To add an option you must specify an option\'s key & value in the following format:';
            echo "\n\n\t";
            echo 'wpadmin add option --{option_name}={option_value}';
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
     * Updates an option's value in the WordPress options table.
     *
     * @example
     *
     *      $option = WpAdmin_Option::load('blogname');
     *      $option->update('New Site Title');
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
            echo 'wpadmin update option --{option_name}={option_value}';
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
    
    /**
     * Deletes an option from the WordPress options table.
     *
     * @example
     *
     *      $option = WpAdmin_Option::load('blogname');
     *      $option->delete();
     *
     * @static
     * @access  public
     * @param   $bind array
     * @return  void
     */
    public function delete($bind)
    {
        unset($bind['primary']);
        
        if(empty($bind)){
            echo '[!] To delete an option you must specify the option\'s key in the following format:';
            echo "\n\n\t";
            echo 'wpadmin delete option --{option_name}';
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
    
    /**
     * Queries the WordPress options table for an option with an option_name the
     * same the $optionName.
     *
     * @access  public
     * @param   string  $optionName
     * @return  array
     */
    public function queryOptionData($optionName)
    {
        global $wpdb;
        
        $stmt  = 'SELECT `option_id`, `blog_id`, `option_name`, `option_value`, `autoload` ';
        $stmt .= 'FROM `' . $wpdb->options . '` ';
        $stmt .= 'WHERE `option_name` = \'' . mysql_real_escape_string($optionName) . '\' ';
        $stmt .= 'LIMIT 1';
        
        return $wpdb->get_row($stmt, ARRAY_A);
    }
    
    /**
     * Queries the WordPress options table for all options.
     *
     * @access  public
     * @return  array
     */
    public function queryOptionsData()
    {
        global $wpdb;
        
        $stmt  = 'SELECT `option_id`, `blog_id`, `option_name`, `option_value`, `autoload` ';
        $stmt .= 'FROM `' . $wpdb->options . '` ';
        $stmt .= 'ORDER BY `option_name` ';
        
        return $wpdb->get_results($stmt, ARRAY_A);
    }
}