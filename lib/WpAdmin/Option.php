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
 *      echo $option->getOptionName();
 * 
 *      To create a new option:
 *      $option = WpAdmin_Option::add(array('my_new_option' => 'My New Option Value'));
 *      echo $option->getOptionName();
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
     * @see WpAdmin_Option::setData()
     * @see WpAdmin_Option::getData()
     * @param  array
     */
    private $_data = array();
    
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
     *      echo $option->getOptionName();
     *
     * @static
     * @access  public
     * @param   string  $optionName     option_name to load.
     * @param   array   $data     array of option data to set against
     *                                  @link WpAdmin_Option::_data for
     *                                  use with WpAdmin_Option::getData()
     *                                  If this parameter is omitted then the data
     *                                  is fetched from the database.
     * @return  WpAdmin_Option
     */
    static public function load($optionName, array $data = array())
    {
        // Instantiate a new WpAdmin_Option object.
        $object = new WpAdmin_Option($optionName);
        
        // If we havent been passeed an array of option data then query the
        // database for it. Otherwise just set it from what'given.
        if (empty($data)){
            $object->setData($object->queryData($optionName));
        }else{
            $object->setData($data);
        }
        
        // Return the object.
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
     * Fetchs all options from the WordPress database & then returns them 
     * as an array of WpAdmin_Option objects.
     *
     * @example
     *  
     *      $options = WpAdmin_Option::listAll();
     *      foreach($options as $option){
     *          echo $option->getOptionName();
     *      }
     *
     * @static
     * @access  public
     * @return  array   Array of WpAdmin_Option objects.
     */
    static public function listAll($params)
    {
        $options = self::queryAllData($params);
        
        $base = array();
        
        foreach ($options as $option){
            $base[] = self::load($option['option_name'], $option);
        }
        
        return $base; 
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
     * @param   array $bind
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
     * @param   array $bind
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
     * Setter for {@link $_data}.
     *
     * @access public
     * @param $data array
     * @return void
     */
    public function setData($data = array())
    {
        $this->_data = $data;
    }
    
    /**
     * Getter for {@link $_data}.
     *
     * @access public
     * @return void
     */
    public function getData()
    {
        return $this->_data;
    }
    
    /**
     * Queries the WordPress options table for an option with an option_name the
     * same the $optionName.
     *
     * @access  public
     * @param   string  $optionName
     * @return  array
     */
    public function queryData($optionName)
    {
        global $wpdb;
        
        $stmt  = 'SELECT `option_id`, 
                            `blog_id`, 
                            `option_name`, 
                            `option_value`, 
                            `autoload` ';
        $stmt .= 'FROM `' . $wpdb->options . '` ';
        $stmt .= 'WHERE `option_name` = \'' . mysql_real_escape_string($optionName) . '\' ';
        $stmt .= 'LIMIT 1';
        
        return $wpdb->get_row($stmt, ARRAY_A);
    }
    
    /**
     * Queries the WordPress options table for all options.
     *
     * @access  public
     * @param   array   $whereBind  Additional key => value pairs that will be
     *                              converted into MySQL WHERE clauses.
     * @return  array
     */
    public function queryAllData(array $whereBind = array())
    {
        global $wpdb;
        
        $allowedWhereFields = array('option_id',
                                        'blog_id',
                                        'option_name',
                                        'option_value',
                                        'autoload');
        
        $stmt  = 'SELECT `option_id`, 
                            `blog_id`, 
                            `option_name`, 
                            `option_value`, 
                            `autoload` ';
        $stmt .= 'FROM `' . $wpdb->options . '` ';
        $stmt .= 'WHERE 1 ';
        foreach($whereBind as $key => $value){
            if (in_array($key, $allowedWhereFields)){
                $stmt .= 'AND `' . $key . '` = \'' . mysql_real_escape_string($value) . '\' ';
            }
        }
        $stmt .= 'ORDER BY `option_name` ';
        
        return $wpdb->get_results($stmt, ARRAY_A);
    }
    
    /**
     * Returns an array of column headings that match the order of 
     * .WpAdmin_Option::queryAllData(). This is used for the tabular
     * output of $ wpadmin option list.
     *
     * @access  public
     * @return  array
     */
    public function getColumnHeaders()
    {
        $base = array('option_id', 
                        'blog_id', 
                        'option_name', 
                        'option_value', 
                        'autoload');
        
        return $base;
    }
}