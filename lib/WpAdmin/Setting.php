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
 * of the WordPress settings.
 * 
 * $ wpadmin setting [update, add] --[key]="[value]"
 * $ wpadmin setting update --title="val"
 * 
 * @since 0.0.1
 */
class WpAdmin_Option
{
    private $_optionName = null;
    
    private $_data = array();
    
    /**
     * Class constructor.
     *
     * @access private
     * @param $optionName null|integer
     * @return WpAdmin_Option
     */
    private function __construct($optionName = null)
    {
        $this->setOptionID($optionName);
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
        
        $object->setData($object->queryData($optionName));
        
        exit();
        
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
            echo 'You must specify an option key. E.g. --my_key=My Value' . "\n";
            return;
        }
        
        foreach($bind as $key => $value){
            $res = add_option($key, $value);
            if (true === $res){
                echo "-- Option '" . $key . "' successfully added.\n";
            }else{
                echo "[!] Option '" . $key . "' not added. Does it already exist?\n";
            }
            return self::load($key);
            break;
        }
    }
    
    /**
     * Setter for {@link $_optionName}.
     *
     * @access public 
     * @param $optionName null|integer 
     * @return void
     */
    public function setOptionID($optionName = null)
    {
        $this->_optionName = $optionName;
    }
    
    /**
     * Getter for {@link $_optionName}.
     *
     * @access public 
     * @return integer|null
     */
    public function getOptionID()
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
    
    public function queryData($optionName)
    {
        global $wpdb;
        
        $stmt  = 'SELECT ';
        $stmt .= 'FROM `' . $wpdb->options . '` ';
        $stmt .= 'WHERE option_name = \'' . mysql_real_escape_string($optionName) . '\'';
        $stmt .= 'LIMIT 1';
        
        echo '<pre>';
        print_r($stmt);
        echo '</pre>';
        exit();
        
        $wpdb->query();
        echo '<pre>';
        print_r($wpdb);
        echo '</pre>';
    }
}