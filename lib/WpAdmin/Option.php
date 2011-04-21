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
        }
        
        if(array_key_exists('siteurl', $bind) && array_key_exists('global', $bind)){
            self::_updateSiteurlGlobal($bind['siteurl']);
        }else{
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
    
    
    /**
     * Globally updates the site url across the entire database including 
     * all serialised data.
     *
     * @access  private
     * @return  void
     * @author  Kieran Masterton http://twitter.com/kieranmasterton
     * @author  David Coveney of Interconnect IT Ltd (UK) for original idea.
     */
    private static function _updateSiteurlGlobal($newSiteurl)
    {
        // Ensure that we prompt the user to add a protocol prefix to their 
        // site name.
        if(!preg_match('/^http(s?):\/\//', $newSiteurl)){
            die("[!] You must prefix your --siteurl option with http:// or https:// \n");
        }
        
        global $wpdb;
        
        // Get the old site url from the database.
        $oldSiteurlRow = $wpdb->get_row('SELECT option_value from ' . $wpdb->prefix . 'options WHERE option_name = "siteurl"', ARRAY_A);
        $oldSiteurl = $oldSiteurlRow['option_value'];
        
        // Check that the two site url are not identical
        if($oldSiteurl == $newSiteurl){
            die("[!] Your site url is already set to: " . $newSiteurl . "\n");
        }
        
        // Array of all relervant WordPress tables.
        $tables = array('commentmeta', 'comments', 'links', 'options', 'postmeta', 
                        'posts', 'terms', 'term_relationships', 'taxonomy', 'usermeta',
                        'users');
        
        // Loop through tables.
        foreach($tables as $table){
            // Get all columns in each table.
            $tableCols = $wpdb->get_results('DESCRIBE ' . $wpdb->prefix . $table);

            // Loop through columns.
            foreach($tableCols as $col){
                // No need to find / replace on IDs.
                if('PRI' == $col->Key){
                    $primaryField = $col->Field;
                    break;
                }
            }
            
            // Select all from each table.
            $tableRows = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . $table, ARRAY_A);

            // If rows are retured.
            if(!empty($tableRows)){
                // Set the update flag to false.
                $updated = FALSE;
                // Loop through resulting rows.
                foreach($tableRows as $row){
                    // Loop through each cell.
                    foreach($row as $cellKey => $cellValue){
                        // Unserialise the cell value.
                        $unserialisedValue = unserialize($cellValue);
                        // If value is not successfully unserialised.
                        if(FALSE == $unserialisedValue){ 
                            // Set count to zero.
                            $count = 0;
                            // Find and replace old site url.
                            if($cellValue = str_replace($oldSiteurl, $newSiteurl, $cellValue, $count)){
                                // If instances of old site url being replaced
                                // is greater than 1.
                                if(1 <= $count){
                                    // Add the cell to an array to be updated.
                                    $cellValues[$cellKey] = $cellValue;
                                    // And, set update flat to true.
                                    $updated = TRUE;
                                }
                            }
                        // If value does successfully unserialise.
                        }else{
                            // Pass values to recursive array replace method.
                            $cellValue = self::_recursiveArrayReplace($oldSiteurl, $cellSiteurl, $cellValue);
                            // Add returned data as a value of our replacement array.
                            $cellValues[$cellKey] = serialize($cellValue); 
                            // And, set update flat to true.
                            $updated = TRUE;
                        }
                    }
                    
                    // If we have cells to update.
                    if(TRUE == $updated){
                        // Pass array of cells to $wpdb->update along with the primary key and value.
                        $result = $wpdb->update($wpdb->prefix . $table, $cellValues, array( $primaryField => $row[$primaryField]), null, null);
                        
                        // Set error flag to true if update failed.
                        if(FALSE == $result){
                            $error = TRUE;
                        }
                        
                        // Reset update flag.
                        $updated = FALSE;
                        // Reset cell values array
                        $cellValues = array();
                    }  
    
                }
            // If the table is empty, skip it.
            }else{
                continue;
            }
        }
        
        // Check for errors and deliver message to user.
        if(TRUE == $error){
            echo "[?] Site url was updated, but there may have some instances missed. Please proceed with caution! \n";
        }else{
           echo "-- Site url successfully updated. \n";  
        }
        
    }
    
    
    /**
     * Globally updates the site url across the entire database including 
     * all serialised data.
     *
     * @access  private
     * @return  void
     * @author  moz667 AT gmail DOT com - originally posted at uk.php.net.
     * @author  Kieran Masterton - updated to work with WpAdmin_Option::_updateSiteurlGlobal
     */
    
    private static function _recursiveArrayReplace($find, $replace, $data)
    {
    
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    self::_recursiveArrayReplace($find, $replace, $data[$key]);
                } else {
                    if (is_string($value)) $data[$key] = str_replace($find, $replace, $value);
                }
            }
        } else {
            if (is_string($data)) $data = str_replace($find, $replace, $data);
        } 
        
        return $data;
    } 
}