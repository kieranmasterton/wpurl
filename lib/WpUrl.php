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
 * @copyright   Copyright (c) 2012 88mph. (http://88mph.com)
 * @license     GNU
 * @author      88mph http://88mph.com
 */
 
/**
 * The WpUrl class provides methods for parsing user input, searching both serialized and unserialized data 
 * and replacing old site urls instances with new site url.
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
     * Array of cells to be updated.
     *
     * @static
     * @access private
     */
	private static $_updateCellArray = array();
	
	/**
     * Array of tables to be updated.
     *
     * @static
     * @access private
     */
	private static $_wpTables = array('commentmeta', 'comments', 'links', 'options', 'postmeta', 
		                        'posts', 'terms', 'term_relationships', 'term_taxonomy', 'usermeta',
		                        'users');
	
	/**
    * String to store current primary field.
    *
    * @static
    * @access private
    */	
	private static $_primaryField;
	
	/**
     * String to store current table name.
     *
     * @static
     * @access private
     */
	private static $_currentTableName = array();

    /**
     * Method invoked from prompt.
     *
     * @static
     * @access  public
     * @param   array $args
     * @return  void
     */
    public static function exec($args, $wpconfig)
    {
		// Parse wpconfig and set db config details.
		if(empty(self::$_dbConfig)){
		    self::_setDbConfig(self::_parseWpConfig($wpconfig));
        }
		
		// Connect to WP database
		self::_dbConnect();
		
        // Parse user input.
        self::_parseOptions($args);

        // Set URL values
        $oldUrl = self::$_oldUrl;
        $newUrl = self::$_newUrl;
       
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
			
			if($args[2]){
			    unset($args[0],$args[1]);
    		    foreach($args as $key => $value){
    		        $tmp = str_replace('-', '', $value);
    		        list($cmdName,$cmdValue) = explode('=', $tmp);
    		        $dbCreds[$cmdName] = $cmdValue;
    		    }
    		    if(!(isset($dbCreds['dbname'])) || !(isset($dbCreds['dbuser'])) || !(isset($dbCreds['dbpassword'])) || !(isset($dbCreds['tableprefix']))|| !(isset($dbCreds['dbhost']))){
    		        die("You have given partial database details, please specify --dbname, --dbuser, --dbpassword, --tableprefix and --dbhost. \n");
    		    }else{
    		        preg_match('/^.*\_$/',$dbCreds['tableprefix'], $matches);
    		        if(count($matches) == 0){
    		            $dbCreds['tableprefix'] = $dbCreds['tableprefix'] . '_';
    		        }
    		       
    		        $dbConfig['dbName'] =  $dbCreds['dbname'];    
                    $dbConfig['dbUser'] =  $dbCreds['dbuser']; 
            		$dbConfig['dbPassword'] =  $dbCreds['dbpassword']; 
            		$dbConfig['dbHost'] =  $dbCreds['dbhost'];
            		$dbConfig['tablePrefix'] = $dbCreds['tableprefix'];
            		self::_setDbConfig($dbConfig);
    		    }
    		}
		    
		    self::_promptForConf();
		}else{
			# No URL input from user, display help text.
			self::_displayHelp();
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
    private static function _setDbConfig($dbConfig)
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


    /**
     * Database connection.
     *
     * @static
     * @access  public
     * @return  void
     */
	public static function _dbConnect()
	{
		$mysql = mysql_connect(self::$_dbConfig['dbHost'], self::$_dbConfig['dbUser'], self::$_dbConfig['dbPassword']);
		if (!$mysql) {
		    die("[x] Sorry, we tried to guess your database config options from the contents of wp-config. It looks like we failed :(\nTry using our command line options to configure wpurl directly.\n");
		}else{
			mysql_select_db(self::$_dbConfig['dbName']);
			self::_setDbCon($mysql);
		}
		
	}
    
    /**
     * Displays end-user help.
     *
     * @static
     * @access  private
     * @return  string
     */
    public static function _displayHelp()
    {
		die('Your current WordPress site URL is: ' . 
			self::$_oldUrl . "\nTo change the URL type 'wpurl http://example.com' \n");
    }
    
    /**
     * Extracts string from between two deliminating strings.
     *
     * @static
     * @access  private
     * @return  string
     */
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
     * Method that updates site url across entire db.
     *
     * @static
     * @access  private
     * @return  void
     */
	private static function _updateSiteurl()
	{
		// Check user input
		self::_validateUserInput();

		// Loop through WP tables
		foreach(self::$_wpTables as $table){
			self::$_currentTableName = $table;
			// Retrieve table rows from DB.
			$tableRows = self::_getTableData($table);

			if($tableRows){
				// Loop through
				foreach($tableRows as $tableKey => $tableValue){
					foreach($tableValue as $cellKey => $cellValue){
						$primaryKey = self::$_primaryField;
						self::_parseForMatches($primaryKey, $tableValue[$primaryKey], $cellKey, $cellValue);
					}
				}
			}

		}

		// Update the database from collate changes.
		if(count(self::$_updateCellArray) > 0){
			$count = 0;
			foreach(self::$_updateCellArray as $table => $rows){
				foreach($rows as $rowKey => $rowData){
					$q = 'UPDATE ' . self::$_dbConfig['tablePrefix'] . $table . ' SET ' . $rowData['cell_name'] . " = '" . $rowData['cell_value'] . "' WHERE " . $rowData['pri_name'] . ' = ' . $rowData['pri_value'];
					$res = mysql_query($q);
				
					if (!$res) {
					   die('Database update error: ' . mysql_error());
					}else{
						$count++;
					}
				}
			}
		}
		
		die('Complete: ' . $count . ' instances updated across ' . count(self::$_updateCellArray) . " tables.\n");
	
	}

	/**
     * Parses values for old site url
     *
     * @static
     * @access  private
     * @return  void
     */
	private static function _parseForMatches($primaryKeyName, $primaryKeyValue,$cellKey, $data){
		// If an array has been passed in
		if(is_array($data)){
			// Loop through
			foreach($data as $key => $value){
				// And pass to same method
				self::_parseForMatches($value);
			}
		}else{
			// Attempt to unserialise
			$unSerializedData = unserialize($data);
			// If unsuccessful
			if(FALSE != $unSerializedData){
				// Then data is string for find / replace	
				self::_findAndStoreReplacement($primaryKeyName,$primaryKeyValue,$cellKey,$unSerializedData);
			}else{
				self::_findAndStoreReplacement($primaryKeyName,$primaryKeyValue,$cellKey,$data);
			}
		}
	}

	/**
     * Stores the dbs, rows and cells to be updated.
     *
     * @static
     * @access  private
     * @return  void
     */
	private static function _findAndStoreReplacement($primaryKeyName,$primaryKeyValue,$cellKey, $data){
		$result = self::_findReplace($data);
		if(TRUE === $result['updated']){
			self::$_updateCellArray[self::$_currentTableName][] = array('pri_name' => $primaryKeyName, 'pri_value' => $primaryKeyValue, 'cell_name' => $cellKey, 'cell_value' => $result['data'], 'old_value' => $data);
		}
	}

	/**
     * Replaces site url
     *
     * @static
     * @access  private
     * @return  array
     */
	private static function _findReplace($data)
	{
		$count = 0;
		if(is_string($data)){
			$data = str_replace(self::$_oldUrl, self::$_newUrl, $data, $count);
			
			if ($count > 0) { 
				$return['updated'] = TRUE;
			}else{
				$return['updated'] = FALSE;	
			}

			$return['data'] = $data;
		}else{
			$return['data'] = $data;
			$return['updated'] = FALSE;	
		}
		return $return;
	}

	/**
     * Returns table data from db.
     *
     * @static
     * @access  private
     * @return  array
     */
	private static function _getTableData($table)
	{		
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
	            self::$_primaryField = $tableCols['Field'];
	            break;
	        }
	    }

	    // Select all from each table.
		$res = mysql_query('SELECT * FROM ' . self::$_dbConfig['tablePrefix'] . $table);
		if (!$res) {
		   die('Database query error: ' . mysql_error());
		}

		if(FALSE != $res){
			while ($tmpTables = mysql_fetch_assoc($res)) {
				$tableRows[] = $tmpTables;
			}
		}

		return $tableRows;
	}

	/**
     * Validates that the user has entered a correct url.
     *
     * @static
     * @access  private
     * @return  void
     */
	public static function _validateUserInput(){
		// Ensure that we prompt the user to add a protocol prefix to their 
	    // site name.
	    if(!preg_match('/^http(s?):\/\//', self::$_newUrl)){
	        die("You must prefix your site url with http:// or https:// \n");
	    }

	    // Check that the two site url are not identical
	    if(self::$_oldUrl == self::$_newUrl){
	        die("Your site url is already set to: " . self::$_newUrl . "\n");
	    }
	} 
}