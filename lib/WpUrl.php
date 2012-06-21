<?php

/**
 * wpurl
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
 * The WpUrl class provides methods for parsing user input and instantiating
 * the required class.
 *
 * @since   0.0.1
 * @author  Kieran Masterton (@kieranmasterton)
 */
class WpUrl
{
    
	/**
     * WP Site Database config options
     *
     * @static
     * @access private
     */
    private static $_dbConfig = array();

	/**
     * WP Site Database connection
     *
     * @static
     * @access private
     */
    private static $_dbCon;


    /**
     * Params to pass to class method.
     *
     * @static
     * @access private
     */
    private static $_params = array();

	/**
     * String to store old site URL.
     *
     * @static
     * @access private
     */
    private static $_oldUrl;

	/**
     * String to store new site URL.
     *
     * @static
     * @access private
     */
    private static $_newUrl;
    
    /**
     * Function invoked from prompt.
     *
     * @static
     * @access  public
     * @param   array $args
     * @return  void
     */
    public static function exec($args, $wpconfig)
    {
		// Parse wpconfig and set db config details.
		self::_setDbConfig(self::_parseWpConfig($wpconfig));
		
		// Connect to WP database
		self::_dbConnect();
		
        // Parse user input.
        self::_parseOptions($args);

        // Set URL values
        $oldUrl      = self::$_oldUrl;
        $newUrl     = self::$_newUrl;
       
    }
    
	/**
     * Parse the contents of the wp-config.php file.
     *
     * @static
     * @access  private
     * @param   string $wpconfig
     * @return  array
     */
    private static function _parseWpConfig($wpconfig)
    {
		$dbConfig['dbName'] =  self::_extractString($wpconfig, "'DB_NAME', '", "');");    
        $dbConfig['dbUser'] =  self::_extractString($wpconfig, "'DB_USER', '", "');"); 
		$dbConfig['dbPassword'] =  self::_extractString($wpconfig, "'DB_PASSWORD', '", "');"); 
		$dbConfig['dbHost'] =  self::_extractString($wpconfig, "'DB_HOST', '", "');");
		$dbConfig['tablePrefix'] = self::_extractString($wpconfig, "$table_prefix  = '", "';");
		
		return $dbConfig;
    }


    /**
     * Parse the arguments send from the command line.
     *
     * @static
     * @access  private
     * @param   array $args
     * @return  void
     */
    private static function _parseOptions($args)
    {
		# Set old URL value from db.
		self::_setOldUrl();
		
		if(isset($args[1])){
			# URL supplied by user, set URL value and prompt 
			# for confirmation to change site URL.
			self::_setNewUrl($args[1]);
			self::_promptForConf();
		
		}else{
			# No URL input from user, display help text.
			self::displayHelp();
		}
    }

 	/**
     * Set the db config array.
     *
     * @static
     * @param   array $dbConfig
     * @access  private
     * @return  void
     */
    private static function _setdbConfig($dbConfig)
    {
		if(is_array($dbConfig)){
			self::$_dbConfig = $dbConfig;
		}
    }   

 	/**
     * Set the db connection object.
     *
     * @static
     * @param   object $mysql
     * @access  private
     * @return  void
     */
    private static function _setdbCon($mysql)
    {
		self::$_dbCon = $mysql;
    }


 	/**
     * Query WP db and return old site URL.
     *
     * @static
     * @access  private
     * @return  void
     */
    private static function _setOldUrl()
    {
		$res = mysql_query('SELECT option_value FROM ' . self::$_dbConfig['tablePrefix'] . 'options WHERE option_name = "siteurl"');
		if (!$res) {
		    die('Database query error: ' . mysql_error());
		}
		$row = mysql_fetch_assoc($res);
		self::$_oldUrl = $row['option_value'];
    }


	
	/**
     * Set new site URL.
     *
     * @static
     * @access  private
     * @return  void
     */
    private static function _setNewUrl($newUrl)
    {
		self::$_newUrl = $newUrl;
    }

	/**
     * Prompt for confirmation.
     *
     * @static
     * @access  private
     * @return  void
     */
    private static function _promptForConf()
    {
		fwrite(STDOUT, "DB: " . self::$_dbConfig['dbName'] . " | DB User: " . self::$_dbConfig['dbUser'] . " | DB Host: " . self::$_dbConfig['dbHost'] ."\nAre you sure you wish change the current WordPress site URL from " . self::$_oldUrl . " to " . self::$_newUrl . "? (Y/N) : ");
		$response = trim(fgets(STDIN));

        if(TRUE == self::_parseYesNo($response)){
           	// Yes, update site URL.
			self::_updateSiteurl();
        }else{
			// Nope, do nothing and bail.
            die("Nothing has been changed, wpurl exited and left the database untouched. \n");
        }
    }
    
    /**
     * Determines whether the user has said "yes" or "no" to a question prompt on
     * the command line.
     *
     * @static
     * @access  public
     * @param   array $args
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


	public static function _dbConnect()
	{
		$mysql = mysql_connect(self::$_dbConfig['dbHost'], self::$_dbConfig['dbUser'], self::$_dbConfig['dbPassword']);
		if (!$mysql) {
		    die("[x] Sorry, we tried to guess your database config options from the contents of wp-config. It looks like we failed :(\nTry using our command line options to configure wpurl directly, type 'wpurl --help' for more info.\n");
		}else{
			mysql_select_db(self::$_dbConfig['dbName']);
			self::_setDbCon($mysql);
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
		die('Your current WordPress site URL is: ' . self::$_oldUrl . "\nTo change the URL type 'wpurl http://example.com'\nFor more options type 'wpurl --help'\n");
    }
    
	private static function _extractString($string, $start, $end){
		$pos = stripos($string, $start);
		$str = substr($string, $pos);
		$strTwo = substr($str, strlen($start));
		$secondPos = stripos($strTwo, $end);
		$strThree = substr($strTwo, 0, $secondPos);
		$string = trim($strThree); // remove whitespaces
		return $string;
	}
	
	/**
	     * Globally updates the site url across the entire database including 
	     * all serialised data.
	     *
	     * @access  private
	     * @return  void
	     * @author  Kieran Masterton (@kieranmasterton)
	     * @author  David Coveney of Interconnect IT Ltd (UK) for original idea.
	     */
	    private static function _updateSiteurl()
	    {
	        // Ensure that we prompt the user to add a protocol prefix to their 
	        // site name.
	        if(!preg_match('/^http(s?):\/\//', self::$_newUrl)){
	            die("You must prefix your site url with http:// or https:// \n");
	        }

	        // Check that the two site url are not identical
	        if(self::$_oldUrl == self::$_newUrl){
	            die("Your site url is already set to: " . self::$_newUrl . "\n");
	        }

	        // Array of all relervant WordPress tables.
	        $tables = array('commentmeta', 'comments', 'links', 'options', 'postmeta', 
	                        'posts', 'terms', 'term_relationships', 'term_taxonomy', 'usermeta',
	                        'users');
	
			// Set cell counter to zero.
			$cellValuesCount = 0;

	        // Loop through tables.
	        foreach($tables as $table){
	            // Get all columns in each table.
				$res = mysql_query('DESCRIBE ' . self::$_dbConfig['tablePrefix'] . $table);
				if (!$res) {
				   die('Database query error: ' . mysql_error());
				}
				$tableCols = mysql_fetch_assoc($res);

	            // Loop through columns.
	            foreach($tableCols as $key => $value){
	                // No need to find / replace on IDs.
	                if('PRI' == $value){
	                    $primaryField = $tableCols['Field'];
	                    break;
	                }
	            }

	            // Select all from each table.
				$res = mysql_query('SELECT * FROM ' . self::$_dbConfig['tablePrefix'] . $table);
				if (!$res) {
				   die('Database query error: ' . mysql_error());
				}
				$tableRows = mysql_fetch_assoc($res);
	
	            #$tableRows = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . $table, ARRAY_A);

	            // If rows are retured.
	            if($tableRows){
	                // Set the update flag to false.
	                $updated = FALSE;
	                // Loop through resulting rows.
	                    foreach($tableRows as $cellKey => $cellValue){
	                        // Unserialise the cell value.
	                        $unserialisedValue = unserialize($cellValue);
	                        // If value is not successfully unserialised.
	                        if(FALSE == $unserialisedValue){ 
	                            // Set count to zero.
	                            $count = 0;
	                            // Find and replace old site url.
	                            if($cellValue = str_replace(self::$_oldUrl, self::$_newUrl, $cellValue, $count)){
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
	                            $cellValue = self::_recursiveArrayReplace(self::$_oldUrl, $cellSiteurl, $cellValue);
	                            // Add returned data as a value of our replacement array.
	                            $cellValues[$cellKey] = serialize($cellValue); 
	                            // And, set update flat to true.
	                            $updated = TRUE;
	                        }
	                    }

	                    // If we have cells to update.
	                    if(TRUE == $updated){
	                        // Pass array of cells to $wpdb->update along with the primary key and value.				
							foreach($cellValues as $key => $value){
							// Select all from each table.
							$q = 'UPDATE ' . self::$_dbConfig['tablePrefix'] . $table . ' SET ' . $key . " = '" . $value . "' WHERE " . $primaryField . ' = ' . $tableRows[$primaryField];
	
							$res = mysql_query($q);
							if (!$res) {
							   die('Database update error: ' . mysql_error());
							}
							}

	                        // Set error flag to true if update failed.
	                        if(FALSE == $res){
	                            $error = TRUE;
	                        }

	                        // Reset update flag.
	                        $updated = FALSE;
							
							// Update cell counter.
							$cellValuesCount = count($cellValues) + $cellValuesCount;
							
	                        // Reset cell values array
	                        $cellValues = array();
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
	           echo "-- Site url successfully updated. " . $cellValuesCount . " instances were found and updated.\n";  
	        }

	    }


	    /**
	     * Globally updates the site url across the entire database including 
	     * all serialised data.
	     *
	     * @access  private
	     * @return  void
	     * @author  moz667 AT gmail DOT com - originally posted at uk.php.net.
	     * @author  Kieran Masterton - updated to work with WpUrl::_updateSiteurlGlobal
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