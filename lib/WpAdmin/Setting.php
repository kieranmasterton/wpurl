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
 * @category   Setting
 * @copyright  Copyright (c) 2011 88mph. (http://88mph.co)
 * @license    GNU
 */
 
/**
 * The WpAdmin_Setting class provides methods for dealing with the administering
 * of the WordPress settings.
 * 
 * $ wpadmin setting --title="val"
 * 
 * @since 0.0.1
 */
class WpAdmin_Setting
{
    private $_settingID = 0;
    
    /**
     * Class constructor.
     *
     * @access private
     * @param $settingID null|integer
     * @return WpAdmin_Setting
     */
    private function __construct($settingID = null)
    {
        $this->setSettingID($settingID);
    }
    
    /**
     * Factory method for creating an instance of WpAdmin_Setting.
     *
     * @static
     * @access public
     * @param $settingID null|integer
     * @return My_Class
     */
    static public function load($settingID)
    {
        $object = new WpAdmin_Setting($settingID);
        
        return $object;
    }
    
    /**
     * Factory method for inserting a new "option" into the database and then
     * creating an instance of WpAdmin_Setting.
     *
     * @static
     * @access  public
     * @param   $key    string  Option key
     * @param   $value  string  Option value
     * @return WpAdmin_Setting
     */
    static public function create($key, $value)
    {
        add_option($key, $value);
        return self::load(Zend_Registry::get('db')->lastInsertId());
    }
    
    /**
     * Setter for {@link $_settingID}.
     *
     * @access public 
     * @param $settingID null|integer 
     * @return void
     */
    public function setSettingID($settingID = null)
    {
        $this->_settingID = $settingID;
    }
    
    /**
     * Getter for {@link $_settingID}.
     *
     * @access public 
     * @return integer|null
     */
    public function getSettingID()
    {
        return $this->_settingID;
    }
}