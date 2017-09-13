<?php
// [LIB - SmartFramework / SQLite 3 Database Client]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - SQLite 3 Database Client
// DEPENDS:
//	* Smart::
//	* SmartUnicode::
//	* SmartUtils::
//	* SmartFileSystem::
// DEPENDS-EXT: PHP SQLite3 Extension
// Tested and Stable on SQLite versions:
// 3.x
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartSQliteDb - provides a Dynamic SQLite Database Client.
 *
 * <code>
 *
 * // IMPORTANT: https://sqlite.org/lang_keywords.html
 * // 	* SQLIte3 uses by default ' (single quotes) to quote strings and ` (backticks) or nothing to quote identifiers (table names / field names)
 * // 	* It is recommended to quote identifiers (table names / field names) using ` (backtick) instead of not quoting at all to avoid confusion with SQL reserved syntax
 * // 	* Using ` (backticks) is prefered as SELECT `field1` FROM `table1` is a strict syntax in SQLite3 and will throw error if either table1 or field1 do not exists
 * // 	* Using " (double quote) for quoting identifiers may result in unexpected results as SELECT "field1" FROM "table1" will not throw exception if field1 / table1 do not exists
 *
 * //Sample Usage
 * $db = new SmartSQliteDb('tmp/testunit.sqlite');
 * $db->open();
 * $sq_rd = (array) $db->read_asdata("SELECT `description` FROM `mytable` WHERE (`id` = '".$db->escape_str($my_id)."') LIMIT 1 OFFSET 0");
 * $sq_cnt = (int) $db->count_data("SELECT COUNT(1) FROM mytable WHERE (score > ?)", array(100));
 * $arr_insert = array(
 * 		'id' => 100,
 * 		'active' => 1,
 * 		'name' => 'Test Record'
 * );
 * $sq_ins = (array) $db->write_data('INSERT INTO other_table '.$db->prepare_statement($arr_insert, 'insert'));
 * $sq_upd = (array) $db->write_data('UPDATE other_table SET active = 0 WHERE (id = ?)', array(100));
 * $prepared_sql = $db->prepare_param_query('SELECT * FROM table WHERE id = ?', [99]);
 * $db->close(); // optional, but safe
 *
 * </code>
 *
 * @usage 		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	extensions: PHP SQLite (3) ; classes: Smart, SmartUnicode, SmartUtils, SmartFileSystem
 * @version 	v.170913
 * @package 	Database:SQLite
 *
 */
final class SmartSQliteDb {

// ->

//-- private vars
private $db;
private $file;
private $destroyed;
private $newinstance;
private $timeoutbusysec;
//--


//--
/**
 * Class constructor
 *
 * @param STRING $sqlite_db_file 		:: The path to the SQLite Database File :: Example: 'tmp/test.sqlite3' ; (if DB does not exist, will create it)
 *
 */
public function __construct($sqlite_db_file, $timeout_busy_sec=60) {

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
			'type' => 'metainfo',
			'data' => 'SQLite App Connector Version: '.SMART_FRAMEWORK_VERSION
		]);
		//--
	} //end if
	//--

	//--
	$this->destroyed = true;
	$this->file = (string) $sqlite_db_file; // add SQLite Version as suffix
	//--
	if($this->check_exists() !== true) {
		$this->newinstance = true;
	} else {
		$this->newinstance = false;
	} //end if
	//--
	$this->timeoutbusysec = (int) $timeout_busy_sec;
	if($this->timeoutbusysec < 0) {
		$this->timeoutbusysec = 0;
	} //end if
	//--

	//--
	register_shutdown_function(array($this, 'close')); // for extra safety when connection is not closed because of previous errors
	//--

} //END FUNCTION
//--


//--
/**
 * Class Destructor
 * This will automatically close the current DB of this class (if not closed explicit before).
 */
public function __destruct() {
	$this->close();
} //END FUNCTION
//--


//--
/**
 * Opens the current SQLite DB (similar to server connect).
 * This must be called prior any other DB operations: read / write / count / ...
 */
public function open() {
	$this->destroyed = false;
	$this->db = SmartSQliteUtilDb::open($this->file, $this->timeoutbusysec);
} //END FUNCTION
//--


//--
/**
 * Manually Closes the current SQLite DB (similar to server disconnect).
 * This is for safety and should be used when coding to explicit close the DB after ending operations to avoid DB corruption in high-load environments.
 * Otherwise, it will be closed automatically on object __destruct() ...
 */
public function close() {
	if($this->destroyed !== true) {
		SmartSQliteUtilDb::close($this->db, $this->file);
		$this->destroyed = true;
	} //end if
} //END FUNCTION
//--


//--
/**
 * Escape a string to be compliant and Safe (against SQL Injection) with SQLite standards.
 * This function will not add the (single) quotes arround the string, but just will just escape it to be safe.
 *
 * @param STRING $string 						:: A String or a Number to be Escaped
 * @return STRING 								:: The Escaped String / Number
 */
public function escape_str($string) {
	$this->check_opened();
	return SmartSQliteUtilDb::escape_str($this->db, $string);
} //END FUNCTION
//--


//--
/**
 * Registers a PHP function for use as an SQL scalar function with SQLite.
 *
 * @param STRING $func 							:: A PHP or custom Function Name
 * @param INTEGER $args 						:: The number of required args ; If this parameter is -1, then the SQL function may take any number of arguments
 */
public static function register_sql_function($func, $args) {
	$this->check_opened();
	SmartSQliteUtilDb::register_sql_function($this->db, $func, $args);
} //END FUNCTION
//--


//--
/**
 * Check if a Table exists in the current SQLite DataBase
 *
 * @param STRING $table_name					:: The Table Name
 * @return BOOLEAN 								:: TRUE if exists, FALSE if not
 *
 */
public function check_if_table_exists($table_name) {
	$this->check_opened();
	return SmartSQliteUtilDb::check_if_table_exists($this->db, (string)$table_name);
} //END FUNCTION
//--


//--
/**
 * SQLite Query -> Count
 * This function is intended to be used for count type queries: SELECT COUNT().
 *
 * @param STRING $query 						:: the SQLite Query
 * @param STRING $params_or_title 				:: *optional* array of parameters (?, ?, ... ?) or query title for easy debugging
 * @return INTEGER 								:: the result of COUNT()
 */
public function count_data($query, $params_or_title='') {
	$this->check_opened();
	return SmartSQliteUtilDb::count_data($this->db, $query, $params_or_title);
} //END FUNCTION
//--


//--
/**
 * SQLite Query -> Read (Non-Associative) one or multiple rows.
 * This function is intended to be used for read type queries: SELECT.
 *
 * @param STRING $query 						:: the SQLite Query
 * @param STRING $params_or_title 				:: *optional* array of parameters (?, ?, ... ?) or query title for easy debugging
 * @return ARRAY (non-asociative) of results	:: array('column-0-0', 'column-0-1', ..., 'column-0-n', 'column-1-0', 'column-1-1', ... 'column-1-n', ..., 'column-m-0', 'column-m-1', ..., 'column-m-n')
 */
public function read_data($query, $params_or_title='') {
	$this->check_opened();
	return SmartSQliteUtilDb::read_data($this->db, $query, $params_or_title);
} //END FUNCTION
//--


//--
/**
 * SQLite Query -> Read (Associative) one or multiple rows.
 * This function is intended to be used for read type queries: SELECT.
 *
 * @param STRING $query 						:: the SQLite Query
 * @param STRING $params_or_title 				:: *optional* array of parameters (?, ?, ... ?) or query title for easy debugging
 * @return ARRAY (asociative) of results		:: array(0 => array('column1', 'column2', ... 'column-n'), 1 => array('column1', 'column2', ... 'column-n'), ..., m => array('column1', 'column2', ... 'column-n'))
 */
public function read_adata($query, $params_or_title='') {
	$this->check_opened();
	return SmartSQliteUtilDb::read_adata($this->db, $query, $params_or_title);
} //END FUNCTION
//--


//--
/**
 * SQLite Query -> Read (Associative) - Single Row (just for 1 row, to easy the use of data from queries).
 * !!! This will raise an error if more than one row(s) are returned !!!
 * This function does not support multiple rows because the associative data is structured without row iterator.
 * For queries that return more than one row use: read_adata() or read_data().
 * This function is intended to be used for read type queries: SELECT.
 *
 * @hints	ALWAYS use a LIMIT 1 OFFSET 0 with all queries using this function to avoid situations that will return more than 1 rows and will raise ERROR with this function.
 *
 * @param STRING $query 						:: the SQLite Query
 * @param STRING $params_or_title 				:: *optional* array of parameters (?, ?, ... ?) or query title for easy debugging
 * @return ARRAY (asociative) of results		:: Returns just a SINGLE ROW as: array('column1', 'column2', ... 'column-n')
 */
public function read_asdata($query, $params_or_title='') {
	$this->check_opened();
	return SmartSQliteUtilDb::read_asdata($this->db, $query, $params_or_title);
} //END FUNCTION
//--


//--
/**
 * SQLite Query -> Write.
 * This function is intended to be used for write type queries: BEGIN (TRANSACTION) ; COMMIT ; ROLLBACK ; INSERT ; UPDATE ; CREATE SCHEMAS ; CALLING STORED PROCEDURES ...
 *
 * @param STRING $query 						:: the SQLite Query
 * @param STRING $params_or_title 				:: *optional* array of parameters (?, ?, ... ?) or query title for easy debugging
 * @return ARRAY 								:: [0 => 'control-message', 1 => #affected-rows]
 */
public function write_data($query, $params_or_title='') {
	$this->check_opened();
	return SmartSQliteUtilDb::write_data($this->db, $query, $params_or_title);
} //END FUNCTION
//--


//--
/**
 * Create Escaped Write SQL Statements from Data - to be used with SQLite for: INSERT ; UPDATE ; IN-SELECT
 * Can be used with: write_data() to build INSERT / UPDATE queries from an associative array
 * or can be used with read_data(), read_adata(), read_asdata(), count_data() to build IN-SELECT queries from a non-associative array
 *
 * @param ARRAY $arrdata 						:: associative array: array of form data as $arr=array(); $arr['field1'] = 'a string'; $arr['field2'] = 100; | non-associative array $arr[] = 'some value'; $arr[] = 'other-value', ...
 * @param ENUM $mode							:: mode: 'insert' | 'update' | 'in-select'
 * @return STRING								:: The SQL partial Statement
 */
public function prepare_statement($arrdata, $mode) {
	$this->check_opened();
	return SmartSQliteUtilDb::prepare_statement($this->db, $arrdata, $mode);
} //END FUNCTION
//--


//--
/**
 * Create Escaped SQL Statements from Parameters and Array of Data by replacing ? (question marks)
 * This can be used for a full SQL statement or just for a part.
 * The statement must not contain any Single Quotes to prevent SQL injections which are unpredictable if mixing several statements at once !
 *
 * @param STRING $query							:: SQL Statement to process like '   WHERE (id = ?)'
 * @param ARRAY $arrdata 						:: The non-associative array as of: $arr=array('a');
 * @return STRING								:: The SQL processed (partial/full) Statement
 */
public function prepare_param_query($query, $arrdata) {
	$this->check_opened();
	return SmartSQliteUtilDb::prepare_param_query($this->db, $query, $arrdata);
} //END FUNCTION
//--


//--
/**
 * Get A UNIQUE (SAFE) ID for DB Tables / Schema
 *
 * @param ENUM $mode 							:: mode: uid10str | uid10num
 * @param STRING $id_field 						:: the field name
 * @param STRING $table_name 					:: the table name
 * @return STRING 								:: the generated Unique ID
 *
 */
public function new_safe_id($mode, $id_field, $table_name) {
	$this->check_opened();
	return SmartSQliteUtilDb::new_safe_id($this->db, $mode, $id_field, $table_name);
} //END FUNCTION
//--


//--
/**
 * Create a new Table in the current SQLite DataBase
 *
 * @param STRING $table_name		:: The Table Name
 * @param STRING $table_schema		:: The Table SQL Schema for create the table ; Example: 'id varchar(100), name text'
 * @param ARRAY $table_arr_indexes 	:: The Table indexes (Array) ; Example: array('id' => 'ASC', 'name' => 'DESC')
 * @return BOOLEAN 					:: TRUE if exists, FALSE if not
 *
 * @access 		private
 * @internal
 *
 */
public function create_table($table_name, $table_schema, $table_arr_indexes=array()) {
	$this->check_opened();
	return SmartSQliteUtilDb::create_table($this->db, (string)$table_name, (string)$table_schema, (array)$table_arr_indexes);
} //END FUNCTION
//--


//--
/**
 * Returns the SQLite DB (full Path, includding the Filename)
 *
 * @access 		private
 * @internal
 *
 */
public function get_filename() {
	return (string) $this->file;
} //END FUNCTION
//--


//--
/**
 * return the status of new instance
 *
 * @access 		private
 * @internal
 *
 */
public function check_newinstance() {
	return $this->newinstance;
} //END FUNCTION
//--


//--
/**
 * Returns true if the SQLite DB exists
 *
 * @access 		private
 * @internal
 */
private function check_exists() {
	$exists = false;
	if(((string)$this->get_filename() != '') AND (SmartFileSystem::is_type_file($this->get_filename()))) {
		$exists = true;
	} //end if
	return $exists;
} //END FUNCTION
//--


//--
/**
 * check if DB is opened
 *
 * @access 		private
 * @internal
 *
 */
private function check_opened() {
	if($this->destroyed !== false) {
		Smart::log_notice('The DataBase: '.$this->file.' was not opened or has been already closed !');
	} //end if
} //END FUNCTION
//--


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartSQliteDb - provides a Static SQLite Database Client.
 *
 * THIS CLASS IS FOR PRIVATE USE. USE INSTEAD THE: SmartSQliteDb
 * @access 		private
 * @internal
 *
 * @usage 		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	extensions: PHP SQLite (3) ; classes: Smart, SmartUnicode, SmartUtils, SmartFileSystem
 * @version 	v.170913
 * @package 	Database:SQLite
 *
 */
final class SmartSQliteUtilDb {

	// ::

	private static $slow_time = 0.0025;

	private static $invalid_conn = '[?+SQLITE-FILE+?]'; // DO NOT CHANGE THIS !!!


//======================================================
public static function check_is_available() {
	//--
	if(!class_exists('SQLite3')) {
		self::error('', 'CHECK-IF-AVAILABLE', 'SQLite3 PHP Extenstion is not available !', '', '');
		return;
	} //end if
	//--
} //END FUNCTION
//======================================================


//======================================================
// SQLite will automatically lock file on write access (does not allow multiple write acess at same time)
public static function open($file_name, $timeout_busy_sec=60) {
	//--
	global $configs;
	//-- check if available
	self::check_is_available();
	//--
	if((string)$file_name == '') {
		self::error((string)$file_name, 'OPEN', 'ERROR: DB path is empty !', '', '');
		return;
	} //end if
	//--
	if(SmartFileSysUtils::check_file_or_dir_name((string)$file_name, 'yes', 'yes') != 1) { 				// deny absolute path access ; allow protected path access (starting with #)
		self::error((string)$file_name, 'OPEN', 'ERROR: DB path is invalid !', '', '');
		return;
	} //end if
	//--
	$dir_of_db = (string) Smart::dir_name((string)$file_name);
	if((string)$dir_of_db == '') {
		self::error((string)$file_name, 'OPEN', 'ERROR: DB folder not defined !', '', '');
		return;
	} //end if
	if(substr((string)$dir_of_db, -1, 1) != '/') {
		$dir_of_db .= '/';
	} //end if
	SmartFileSysUtils::raise_error_if_unsafe_path((string)$dir_of_db, 'yes', 'yes'); 					// deny absolute path access ; allow protected path access (starting with #)
	//--
	if(!SmartFileSystem::is_type_dir($dir_of_db)) {
		@mkdir($dir_of_db, SMART_FRAMEWORK_CHMOD_DIRS, true);
	} //end if
	if(SmartFileSystem::is_type_dir($dir_of_db)) {
		@chmod($dir_of_db, SMART_FRAMEWORK_CHMOD_DIRS); //apply chmod
	} else {
		self::error((string)$file_name, 'OPEN', 'ERROR: DB folder does not exists !', '', '');
		return;
	} //end if
	if(!SmartFileSystem::have_access_write($dir_of_db)) {
		self::error((string)$file_name, 'OPEN', 'ERROR: DB folder is not writable !', '', '');
		return;
	} //end if
	if(!SmartFileSystem::is_type_file((string)$dir_of_db.'.htaccess')) {
		SmartFileSysUtils::raise_error_if_unsafe_path((string)$dir_of_db.'.htaccess', 'yes', 'yes'); 	// deny absolute path access ; allow protected path access (starting with #)
		if(!@file_put_contents((string)$dir_of_db.'.htaccess', (string)'### Smart.Framework // '.__METHOD__.' @ HtAccess Data Protection ###'."\n".SMART_FRAMEWORK_HTACCESS_NOINDEXING.SMART_FRAMEWORK_HTACCESS_FORBIDDEN."\n".'### END ###', LOCK_EX)) {
			self::error((string)$file_name, 'OPEN', 'ERROR: DB folder access-protection not initialized !', '', '');
			return;
		} //end if
		@chmod((string)$dir_of_db.'.htaccess', SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
		if(!SmartFileSystem::is_type_file((string)$dir_of_db.'.htaccess')) {
			self::error((string)$file_name, 'OPEN', 'ERROR: DB folder access-protection not found !', '', '');
			return;
		} //end if
	} //end if
	if(!SmartFileSystem::is_type_file((string)$dir_of_db.'index.html')) {
		SmartFileSysUtils::raise_error_if_unsafe_path((string)$dir_of_db.'index.html', 'yes', 'yes'); 	// deny absolute path access ; allow protected path access (starting with #)
		@file_put_contents((string)$dir_of_db.'index.html', '', LOCK_EX);
		if(!SmartFileSystem::is_type_file((string)$dir_of_db.'index.html')) {
			self::error((string)$file_name, 'OPEN', 'ERROR: DB folder index-protection not found !', '', '');
			return;
		} //end if
	} //end if
	//-- open DB connection
	try {
		//--
		$db = @new SQLite3((string)$file_name, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
		//--
		$db->busyTimeout((int)$timeout_busy_sec * 1000); // $timeout_busy_sec is in seconds ; we set a busy timeout in miliseconds
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			//--
			$arr_version = @$db->version(); // mixed
			//--
			if(!is_array($arr_version)) {
				$arr_version = array();
			} //end if
			//--
			SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
				'type' => 'metainfo',
				'data' => 'SQLite Library Version: '.$arr_version['versionString'].' / '.$arr_version['versionNumber']
			]);
			//--
			if((float)$configs['sqlite']['slowtime'] > 0) {
				self::$slow_time = (float) $configs['sqlite']['slowtime'];
			} //end if
			if(self::$slow_time < 0.0000001) {
				self::$slow_time = 0.0000001;
			} elseif(self::$slow_time > 0.9999999) {
				self::$slow_time = 0.9999999;
			} //end if
			//--
			SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|slow-time', number_format(self::$slow_time, 7, '.', ''), '=');
			SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
				'type' => 'metainfo',
				'data' => 'Fast Query Reference Time < '.self::$slow_time.' seconds'
			]);
			//--
		} //end if
		//--
	} catch (Exception $e) {
		//--
		self::error((string)$file_name, 'OPEN', $e->getMessage(), 'Catch Exception ...', '');
		return;
		//--
	} //end try catch
	//--
	if(SmartFileSystem::is_type_file($file_name)) {
		@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES);
	} //end if
	//--
	self::check_connection($db);
	//--
	SmartFrameworkRegistry::$Connections['sqlite'][(string)self::get_connection_id($db)] = (string) $file_name;
	//--
	if(@$db->lastErrorCode() !== 0) {
		$sqlite_error = 'SQLite3-ERR:: '.@$db->lastErrorMsg();
		self::error((string)$file_name, 'OPEN', 'Failed to Open DB File', $file_name."\n".'ERR: '.$sqlite_error, $file_name);
		return;
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
			'type' => 'open-close',
			'data' => 'Open SQLite Database: '.$file_name
		]);
	} //end if
	//-- register basic user functions
	$ext_functions = [
		'md5' => 1, 'sha1' => 1, 'time' => 0, 'strtotime' => 1,
		'strip_tags' => -1 // can have 1 or 2 args
	];
	foreach($ext_functions as $func => $args) {
		self::register_sql_function($db, $func, $args);
	} //end foreach
	//-- create the first time table to record the sqlite version
	if(!self::check_if_table_exists($db, '_smartframework_metadata')) {
		self::create_table($db, '_smartframework_metadata', '`id` VARCHAR(255) PRIMARY KEY UNIQUE, `description` TEXT');
		self::write_data($db, 'INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (\'sqlite-version\', \''.self::escape_str($db, '3').'\')');
		self::write_data($db, 'INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (\'smartframework-version\', \''.self::escape_str($db, (string)SMART_FRAMEWORK_VERSION).'\')');
		self::write_data($db, 'INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (\'creation-date-and-time\', \''.self::escape_str($db, (string)date('Y-m-d H:i:s O')).'\')');
		self::write_data($db, 'INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (\'database-name\', \''.self::escape_str($db, (string)$file_name).'\')');
		self::write_data($db, 'INSERT INTO `_smartframework_metadata` (`id`, `description`) VALUES (\'domain-realm-id\', \''.self::escape_str($db, (string)SMART_SOFTWARE_NAMESPACE).'\')');
	} //end if
	//--
	return $db;
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function close($db, $infofile='') {
	//--
	//self::check_connection($db);
	//-- close DB connection
	try {
		//--
		if(($db instanceof SQLite3)) {
			//--
			$conn = (string) self::get_connection_id($db);
			if((string)$conn != (string)self::$invalid_conn) { // does not make sense to unset default INVALID connection !
				if((string)$conn != '') {
					//Smart::log_notice('Unsetting SQLite connection from Registry: '.$conn.' @ '.__METHOD__);
					unset(SmartFrameworkRegistry::$Connections['sqlite'][(string)$conn]);
				} else {
					Smart::log_warning('Cannot Unset EMPTY SQLite connection from Registry @ '.__METHOD__);
				} //end if else
			} //end if
			//--
			@$db->close();
			//--
			if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
					'type' => 'open-close',
					'data' => 'Close SQLite Database: '.$infofile
				]);
				//--
			} //end if
			//--
		} else {
			//--
			Smart::log_warning('WARNING: '.__METHOD__.' # The connection is not an instance of SQLite DB');
			//--
		} //end if
		//--
	} catch(Exception $e) {
		//--
		Smart::log_warning('WARNING: '.__METHOD__.' # Something get wrong when trying to close an SQLite DB: '.$e->getMessage());
		//--
	} //end try catch
	//--
	return true;
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function check_connection($db) {
	//--
	if(!($db instanceof SQLite3)) {
		self::error($db, 'CHECK-CONNECTION', 'DB-Object is not an instance of SQLite3 !', '', '');
		return;
	} //end if else
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function register_sql_function($db, $func, $args) {
	//--
	self::check_connection($db);
	//--
	$db->createFunction((string)$func, (string)$func, (int)$args);
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function check_if_table_exists($db, $table_name) {
	//--
	self::check_connection($db);
	//--
	$tquery = 'SELECT `name` FROM `sqlite_master` WHERE `type`=\'table\' AND `name`=\''.self::escape_str($db, $table_name).'\'';
	$test = self::read_data($db, $tquery);
	//--
	$sqlite_error = '';
	//if(!$test) {
	if((Smart::array_size($test) <= 0) OR (((string)$test[0]) !== ((string)$table_name))) {
		//--
		$sqlite_error = 'SQLite3-ERR:: '.@$db->lastErrorMsg();
		//--
	} //end if else
	//--
	if(strlen($sqlite_error) > 0) { // if test failed means table is not available
		$out = 0;
	} else {
		$out = 1;
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function count_data($db, $query, $params_or_title='') {
	//--
	self::check_connection($db);
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		$time_start = microtime(true);
		//--
	} //end if
	//--
	if(is_array($params_or_title)) {
		$query = self::prepare_param_query($db, $query, $params_or_title);
	} //end if
	//--
	$result = @$db->query($query);
	//--
	if($result) {
		//--
		$sqlite_error = '';
		//--
		$num_count = 0;
		//--
		$res = @$result->fetchArray(SQLITE3_NUM);
		//--
		if(is_array($res)) {
			//--
			$num_count = $res[0];
			//--
		} else {
			//--
			//$sqlite_error = 'SQLite3-ERR:: Result is not an array (Count) !'; // this must not be used because it raise error with no results on count
			//--
			$num_count = 0;
			//--
		} //end if
		//--
		@$result->finalize(); // free result
		//--
	} else {
		//--
		$sqlite_error = 'SQLite3-ERR:: '.@$db->lastErrorMsg();
		//--
		$num_count = 0;
		//--
	} //end if else
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-queries', 1, '+');
		//--
		$time_end = (float) (microtime(true) - (float)$time_start);
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$the_query_title = '';
		} else {
			$the_query_title = (string) $params_or_title;
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
			'type' => 'count',
			'data' => 'COUNT :: '.$the_query_title,
			'query' => $query,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) self::get_connection_id($db)
		]);
		//--
	} //end if
	//--
	if(strlen($sqlite_error) > 0) {
		self::error($db, 'COUNT', $sqlite_error, $query, $params_or_title);
		return 0;
	} //end if
	//--
	return Smart::format_number_int($num_count, '+'); // be sure is 0 or greater
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function read_data($db, $query, $params_or_title='') {
	//--
	self::check_connection($db);
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		$time_start = microtime(true);
		//--
	} //end if
	//--
	if(is_array($params_or_title)) {
		$query = self::prepare_param_query($db, $query, $params_or_title);
	} //end if
	//--
	$result = @$db->query($query);
	//--
	if($result) {
		//--
		$sqlite_error = '';
		//--
		$arr_data = array();
		//--
		$number_of_rows = 0;
		$number_of_fields = 0;
		//--
		while($res = @$result->fetchArray(SQLITE3_NUM)) {
			//--
			if(is_array($res)) {
				//--
				$number_of_rows++;
				$number_of_fields = 0;
				//--
				$arrsize = Smart::array_size($res);
				//--
				for($i=0; $i<$arrsize; $i++) {
					//--
					$number_of_fields++;
					//--
					$arr_data[] = (string) $res[$i]; // force string
					//--
				} //end for
				//--
			} else {
				//--
				$sqlite_error = 'SQLite3-ERR:: Result is not an array (Read) !';
				//--
				break;
				//--
			} //end if else
			//--
		} //end while
		//--
		@$result->finalize(); // free result
		//--
	} else {
		//--
		$sqlite_error = 'SQLite3-ERR:: '.@$db->lastErrorMsg();
		//--
		$arr_data = array();
		//--
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-queries', 1, '+');
		//--
		$time_end = (float) (microtime(true) - (float)$time_start);
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$the_query_title = '';
		} else {
			$the_query_title = (string) $params_or_title;
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
			'type' => 'read',
			'data' => 'READ [NON-ASSOCIATIVE] :: '.$the_query_title,
			'query' => $query,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) self::get_connection_id($db)
		]);
		//--
	} //end if
	//--
	if(strlen($sqlite_error) > 0) {
		self::error($db, 'READ-DATA', $sqlite_error, $query, $params_or_title);
		return array();
	} //end if
	//--
	return (array) $arr_data;
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function read_adata($db, $query, $params_or_title='') {
	//--
	self::check_connection($db);
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		$time_start = microtime(true);
		//--
	} //end if
	//--
	if(is_array($params_or_title)) {
		$query = self::prepare_param_query($db, $query, $params_or_title);
	} //end if
	//--
	$result = @$db->query($query);
	//--
	if($result) {
		//--
		$sqlite_error = '';
		//--
		$arr_data = array();
		//--
		$number_of_rows = 0;
		$number_of_fields = 0;
		//--
		while($res = @$result->fetchArray(SQLITE3_ASSOC)) {
			//--
			if(is_array($res)) {
				//--
				$number_of_rows++;
				$number_of_fields = 0;
				//--
				$tmp_datarow = array();
				//--
				foreach($res as $key => $val) {
					//--
					$number_of_fields++;
					//--
					$tmp_datarow[$key] = (string) $val; // force string
					//--
				} //end foreach
				//--
				$arr_data[] = (array) $tmp_datarow;
				//--
				$tmp_datarow = array();
				//--
			} else {
				//--
				$sqlite_error = 'SQLite3-ERR:: Result is not an array (asRead) !';
				//--
				break;
				//--
			} //end if else
			//--
		} //end while
		//--
		@$result->finalize(); // free result
		//--
	} else {
		//--
		$sqlite_error = 'SQLite3-ERR:: '.@$db->lastErrorMsg();
		//--
		$arr_data = array();
		//--
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-queries', 1, '+');
		//--
		$time_end = (float) (microtime(true) - (float)$time_start);
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$the_query_title = '';
		} else {
			$the_query_title = (string) $params_or_title;
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
			'type' => 'read',
			'data' => 'aREAD [ASSOCIATIVE] :: '.$the_query_title,
			'query' => $query,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) self::get_connection_id($db)
		]);
		//--
	} //end if
	//--
	if(strlen($sqlite_error) > 0) {
		self::error($db, 'READ-aDATA', $sqlite_error, $query, $params_or_title);
		return array();
	} //end if
	//--
	return (array) $arr_data;
	//--
} //END FUNCTION
//======================================================


//======================================================
// CAN BE USED JUST WITH ONE ROW !!!
public static function read_asdata($db, $query, $params_or_title='') {
	//--
	self::check_connection($db);
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		$time_start = microtime(true);
		//--
	} //end if
	//--
	if(is_array($params_or_title)) {
		$query = self::prepare_param_query($db, $query, $params_or_title);
	} //end if
	//--
	$result = @$db->query($query);
	//--
	if($result) {
		//--
		$sqlite_error = '';
		//--
		$arr_data = array();
		//--
		$number_of_rows = 0;
		$number_of_fields = 0;
		//--
		while($res = @$result->fetchArray(SQLITE3_ASSOC)) {
			//--
			if(is_array($res)) {
				//--
				$number_of_rows++;
				$number_of_fields = 0;
				//--
				foreach($res as $key => $val) {
					//--
					if(!isset($arr_data[$key])) {
						//--
						$number_of_fields++;
						//--
						$arr_data[$key] = (string) $val; // force string
						//--
					} else {
						//--
						$sqlite_error = 'SQLite3-ERR:: Result contains more than one row !';
						//--
						break;
						//--
					} //end if else
					//--
				} //end foreach
				//--
			} else {
				//--
				$sqlite_error = 'SQLite3-ERR:: Result is not an array (aRead) !';
				//--
				break;
				//--
			} //end if else
			//--
		} //end while
		//--
		@$result->finalize(); // free result
		//--
	} else {
		//--
		$sqlite_error = 'SQLite3-ERR:: '.@$db->lastErrorMsg();
		//--
		$arr_data = array();
		//--
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-queries', 1, '+');
		//--
		$time_end = (float) (microtime(true) - (float)$time_start);
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$the_query_title = '';
		} else {
			$the_query_title = (string) $params_or_title;
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
			'type' => 'read',
			'data' => 'asREAD [SINGLE-ROW-ASSOCIATIVE] :: '.$the_query_title,
			'query' => $query,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) self::get_connection_id($db)
		]);
		//--
	} //end if
	//--
	if(strlen($sqlite_error) > 0) {
		self::error($db, 'READ-asDATA', $sqlite_error, $query, $params_or_title);
		return array();
	} //end if
	//--
	return (array) $arr_data;
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function write_data($db, $query, $params_or_title='') {
	//--
	self::check_connection($db);
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		$time_start = microtime(true);
		//--
	} //end if
	//--
	if(is_array($params_or_title)) {
		$query = self::prepare_param_query($db, $query, $params_or_title);
	} //end if
	//--
	$result = @$db->exec($query);
	//--
	if($result) {
		$affected_rows = @$db->changes();
		// free result is not available for exec, but just for query
		$sqlite_error = '';
	} else {
		$affected_rows = 0;
		$sqlite_error = 'SQLite3-ERR:: '.@$db->lastErrorMsg();
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-queries', 1, '+');
		//--
		$time_end = (float) (microtime(true) - (float)$time_start);
		SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$the_query_title = '';
		} else {
			$the_query_title = (string) $params_or_title;
		} //end if else
		//--
		if((strtoupper(substr(trim($query), 0, 5)) == 'BEGIN') OR (strtoupper(substr(trim($query), 0, 6)) == 'COMMIT') OR (strtoupper(substr(trim($query), 0, 8)) == 'ROLLBACK')) {
			SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
				'type' => 'transaction',
				'data' => 'TRANSACTION :: '.$the_query_title,
				'query' => $query,
				'time' => Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) self::get_connection_id($db)
			]);
		} else {
			SmartFrameworkRegistry::setDebugMsg('db', 'sqlite|log', [
				'type' => 'write',
				'data' => 'WRITE :: '.$the_query_title,
				'query' => $query,
				'rows' => $affected_rows,
				'time' => Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) self::get_connection_id($db)
			]);
		} //end if else
		//--
	} //end if
	//--
	if(strlen($sqlite_error) > 0) {
		$message = 'errorsqlwriteoperation: '.$sqlite_error;
		self::error($db, 'WRITE-DATA', $sqlite_error, $query, $params_or_title);
		return array($message, 0);
	} else {
		$message = 'oksqlwriteoperation';
	} //end if
	//--
	return array($message, Smart::format_number_int($affected_rows, '+'));
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function escape_str($db, $y_string) {
	//--
	self::check_connection($db);
	//--
	$y_string = (string) SmartUnicode::fix_charset((string)$y_string); // Fix
	//--
	$y_string = (string) @$db->escapeString((string)$y_string);
	//--
	return (string) $y_string;
	//--
} // END FUNCTION
//======================================================


//======================================================
public static function prepare_statement($db, $arrdata, $mode) {

	// version: 170411

	//--
	$mode = strtolower((string)$mode);
	//--
	switch((string)$mode) {
		//-- associative array
		case 'insert':
			$mode = 'insert';
			break;
		case 'update':
			$mode = 'update';
			break;
		//-- non-associative array
		case 'in-select':
			$mode = 'in-select';
			break;
		//-- invalid
		default:
			self::error($db, 'PREPARE-STATEMENT', 'Invalid Mode', '', $mode);
			return '';
	} //end switch
	//--

	//--
	$tmp_query = '';
	//--
	$tmp_query_x = '';
	$tmp_query_y = '';
	$tmp_query_z = '';
	$tmp_query_w = '';
	//--

	//--
	if(is_array($arrdata)) {
		//--
		foreach($arrdata as $key => $val) {
			//-- check for SQL INJECTION
			$key = (string) trim(str_replace(array('`', "'", '"'), array('', '', ''), (string)$key));
			//-- except in-select, do not allow invalid keys as they represent the field names ; valid fields must contain only the following chars [A..Z][a..z][0..9][_]
			if((string)$mode == 'in-select') { // in-select
				$key = (int) $key; // force int keys
			} elseif(!self::validate_table_and_fields_names($key)) { // no unicode modifier
				self::error($db, 'PREPARE-STATEMENT', 'Invalid KEY', '', $key);
				return '';
			} //end if
			//--
			$val_x = ''; // reset
			//--
			if(is_array($val)) { // array (this is a special case, and always escape data)
				//--
				$val_x = (string) "'".self::escape_str($db, Smart::array_to_list($val))."'"; // array values will be converted to: <val1>, <val2>, ...
				//--
			} elseif($val === null) { // emulate the SQL: NULL
				//--
				$val_x = 'NULL';
				//--
			} elseif($val === false) { // emulate the SQL: FALSE
				//--
				$val_x = 'FALSE';
				//--
			} elseif($val === true) { // emulate the SQL: TRUE
				//--
				$val_x = 'TRUE';
				//--
			} elseif(SmartValidator::validate_numeric_integer_or_decimal_values($val) === true) { // number ; {{{SYNC-DETECT-PURE-NUMERIC-INT-OR-DECIMAL-VALUES}}}
				//--
				$val_x = (string) trim((string)$val); // not escaped, it is safe: numeric and can contain just 0-9 - .
				//--
			} else { // string or other cases
				//--
				$val_x = (string) "'".self::escape_str($db, $val)."'";
				//--
			} //end if else
			//--
			if((string)$mode == 'in-select') { // in-select
				$tmp_query_w .= $val_x.',';
			} elseif((string)$mode == 'update') { // update
				$tmp_query_x .= '`'.$key.'`'.'='.$val_x.','; // no field escaping
			} else { // insert
				$tmp_query_y .= '`'.$key.'`'.','; // no field escaping
				$tmp_query_z .= $val_x.',';
			} //end if else
			//--
		} //end while
		//--
	} else {
		//--
		self::error($db, 'PREPARE-STATEMENT', 'The second argument must be array !', '', '');
		return '';
		//--
	} //end if else
	//--

	//-- eliminate last comma
	if((string)$mode == 'in-select') { // in-select
		$tmp_query_w = rtrim($tmp_query_w, ' ,');
	} elseif((string)$mode == 'update') { // update
		$tmp_query_x = rtrim($tmp_query_x, ' ,');
	} else { // insert
		$tmp_query_y = rtrim($tmp_query_y, ' ,');
		$tmp_query_z = rtrim($tmp_query_z, ' ,');
	} //end if else
	//--

	//--
	if((string)$mode == 'in-select') { // in-select
		$tmp_query = ' IN ('.$tmp_query_w.') ';
	} elseif((string)$mode == 'update') { // update
		$tmp_query = ' SET '.$tmp_query_x.' ';
	} else { // (new) insert
		$tmp_query = ' ('.$tmp_query_y.') VALUES ('.$tmp_query_z.') ';
	} //end if else
	//--

	//--
	return (string) $tmp_query;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Create Escaped SQL Statements from Parameters and Array of Data by replacing ? (question marks)
 * This can be used for a full SQL statement or just for a part.
 * The statement must not contain any Single Quotes to prevent SQL injections which are unpredictable if mixing several statements at once !
 *
 * @param STRING $query							:: SQL Statement to process like '   WHERE (id = ?)'
 * @param ARRAY $arrdata 						:: The non-associative array as of: $arr=array('a');
 * @return STRING								:: The SQL processed (partial/full) Statement
 */
public static function prepare_param_query($db, $query, $replacements_arr) { // {{{SYNC-SQL-PARAM-QUERY}}}
	//--
	if(!is_string($query)) {
		self::error($db, 'PREPARE-PARAM-QUERY', 'Query is not a string !', print_r($query,1), $replacements_arr);
		return ''; // query must be a string
	} //end if
	//--
	if((string)trim((string)$query) == '') {
		self::error($db, 'PREPARE-PARAM-QUERY', 'Query is empty !', (string)$query, $replacements_arr);
		return ''; // empty query not allowed
	} //end if
	//--
	if(strpos($query, "'") !== false) { // this must be avoided as below will be exploded by ? thus if a ? is inside '' this is a problem ...
		self::error($db, 'PREPARE-PARAM-QUERY', 'Query used for prepare with params in '.__FUNCTION__.'() cannot contain single quotes to prevent possible SQL injections which can produce unpredictable results !', (string)$query, $replacements_arr);
		return ''; // single quote is not allowed
	} //end if
	//--
	if(!is_array($replacements_arr)) {
		self::error($db, 'PREPARE-PARAM-QUERY', 'Query Replacements is NOT Array !', (string)$query, $replacements_arr);
		return ''; // replacements must be an array
	} //end if
	//--
	$out_query = '';
	//--
	if(strpos((string)$query, '?') !== false) {
		//--
		$expr_arr = (array) explode('?', (string)$query);
		$expr_count = count($expr_arr);
		//--
		for($i=0; $i<$expr_count; $i++) {
			//--
			$out_query .= (string) $expr_arr[$i];
			//--
			if($i < ($expr_count - 1)) {
				//--
				if(!array_key_exists((string)$i, $replacements_arr)) {
					self::error($db, 'PREPARE-PARAM-QUERY', 'Invalid Replacements Array Size ; Key='.$i, (string)$query, $replacements_arr);
					return ''; // array key does not exists in replacements
					break;
				} //end if
				//--
				if(SmartValidator::validate_numeric_integer_or_decimal_values($replacements_arr[$i]) === true) { // {{{SYNC-DETECT-PURE-NUMERIC-INT-OR-DECIMAL-VALUES}}}
					$out_query .= (string) trim((string)$replacements_arr[$i]); // not escaped, it is safe: numeric and can contain just 0-9 - .
				} else {
					$out_query .= "'".self::escape_str($db, (string)$replacements_arr[$i])."'";
				} //end if else
				//--
			} //end if
			//--
		} //end for
		//--
	} else {
		//--
		$out_query = (string) $query;
		//--
	} //end if else
	//--
	return (string) $out_query;
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function new_safe_id($db, $y_mode, $y_id_field, $y_table_name) {
	//--
	$y_table_name = (string) $y_table_name;
	if(!self::validate_table_and_fields_names($y_table_name)) {
		self::error($db, 'NEW-SAFE-ID', 'Get New Safe ID', 'Invalid Table Name', $y_table_name);
		return '';
	} //end if
	//--
	$y_id_field = (string) $y_id_field;
	if(!self::validate_table_and_fields_names($y_id_field)) {
		self::error($db, 'NEW-SAFE-ID', 'Get New Safe ID', 'Invalid Field Name', $y_id_field.' / [Table='.$y_table_name.']');
		return '';
	} //end if
	//--
	$tmp_result = 'NO-ID-INIT'; //init (must be not empty)
	$counter = 0; // default is zero
	//--
	while((string)$tmp_result != '') { // while we cannot find an unused ID
		//--
		$counter += 1;
		//--
		if($counter > 5500) { // loop to max 5500
			self::error($db, 'NEW-SAFE-ID', 'Get New Safe ID', 'Could Not Assign a Unique ID', '(timeout / 5500) ... try again !');
			return '';
		} //end if
		//--
		if(($counter % 500) == 0) {
			sleep(1);
		} //end if
		//--
		$new_id = 'NO-ID-ALGO';
		switch((string)$y_mode) {
			case 'uid10seq': // ! sequences are not safe without a second registry allocation table as the chance to generate the same ID in the same time moment is just 1 in 999
				$new_id = (string) Smart::uuid_10_seq();
				break;
			case 'uid10num':
				$new_id = (string) Smart::uuid_10_num();
				break;
			case 'uid10str':
			default:
				$new_id = (string) Smart::uuid_10_str();
		} //end switch
		//--
		$result_arr = array();
		//--
		$result_arr = self::read_data($db, 'SELECT `'.$y_id_field.'` FROM `'.$y_table_name.'` WHERE (`'.$y_id_field.'` = \''.self::escape_str($db, (string)$new_id).'\') LIMIT 1 OFFSET 0');
		//--
		$tmp_result = (string) trim((string)$result_arr[0]);
		$result_arr = array();
		//--
	} //end while
	//--
	return (string) $new_id;
	//--
} //END FUNCTION
//======================================================


//======================================================
public static function create_table($db, $table_name, $table_schema, $table_indexes=array()) {
	//-- samples
	// $table_indexes = '';
	// $table_indexes = 'date_time ASC, status_delete, status_read';
	// $table_indexes = array('idx_uidls' => 'date_time ASC, status_delete, status_read');
	//--
	self::check_connection($db);
	//-- check names
	$table_name = (string) $table_name;
	if(!self::validate_table_and_fields_names($table_name)) {
		self::error($db, 'CREATE TABLE', 'Create Table: '.$table_name, 'Invalid Table Name', $table_name);
		return '';
	} //end if
	//-- the create table query
	$tbl_query = "CREATE TABLE {$table_name} ({$table_schema});";
	//--
	$idx_query = '';
	//--
	if((is_array($table_indexes)) AND (Smart::array_size($table_indexes) > 0)) {
		//--
		foreach($table_indexes as $key => $val) {
			if(!self::validate_table_and_fields_names((string)$key)) {
				self::error($db, 'CREATE TABLE', 'Create Table: '.$table_name, 'Invalid Index Name', (string)$key);
				return '';
			} //end if
			$idx_query .= ' CREATE INDEX '.(string)$key.' ON '.(string)$table_name.' ('.$val.');';
		} //end for
		//--
	} //end if
	//--
	$query = (string) $tbl_query.$idx_query;
	//--
	$sqlite_table_exists = self::check_if_table_exists($db, $table_name);
	//--
	if($sqlite_table_exists != 1) { // if test failed means table is not available
		self::write_data($db, $query); // this will die with message if query have errors
	} //end if
	//--
} //END FUNCTION
//======================================================


//======================================================
private static function validate_table_and_fields_names($y_table_or_field) {
	//--
	if(preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string)$y_table_or_field)) {
		$is_ok = true;
	} else {
		$is_ok = false;
	} //end if else
	//--
	return $is_ok;
	//--
} //END FUNCTION
//======================================================


//======================================================
private static function get_connection_id($db) {
	//--
	$out = (string) self::$invalid_conn;
	//--
	self::check_connection($db);
	//--
	try { // if DB is very busy this may fail, thus needs a try/catch
		//--
		$arr = (array) @$db->query('PRAGMA database_list')->fetchArray(SQLITE3_ASSOC);
		//--
	} catch (Exception $e) {
		//--
		Smart::log_warning('WARNING: '.__METHOD__.' # Failed to Get Connection ID: '.$e->getMessage());
		//--
	} //end try catch
	//--
	if(Smart::array_size($arr) > 0) {
		if(((string)$arr['seq'] == '0') AND ((string)$arr['name'] == 'main') AND ((string)$arr['file'] != '')) {
			$out = (string) $arr['file'];
		} //end if
	} //end if
	//--
	return (string) $out;
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Displays the SQLite Errors and HALT EXECUTION (This have to be a FATAL ERROR as it occur when a FATAL SQLite ERROR happens or when a Query Syntax is malformed)
 * PRIVATE
 *
 * @return :: HALT EXECUTION WITH ERROR MESSAGE
 *
 */
private static function error($db, $y_area, $y_error_message, $y_query, $y_params_or_title, $y_warning='') {
//--
$def_warn = 'Execution Halted !';
$y_warning = (string) trim((string)$y_warning);
if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
	$width = 750;
	$the_area = (string) $y_area;
	if((string)$y_warning == '') {
		$y_warning = (string) $def_warn;
	} //end if
	$the_error_message = 'Operation FAILED: '.$def_warn."\n".$y_error_message;
	if(is_array($y_params_or_title)) {
		$the_params = '*** Params ***'."\n".print_r($y_params_or_title, 1);
	} elseif((string)$y_params_or_title != '') {
		$the_params = '[ Reference Title ]: '.$y_params_or_title;
	} else {
		$the_params = '- No Params or Reference Title -';
	} //end if
	$the_query_info = (string) trim((string)$y_query);
	if((string)$the_query_info == '') {
		$the_query_info = '-'; // query cannot e empty in this case (templating enforcement)
	} //end if
} else {
	$width = 550;
	$the_area = '';
	$the_error_message = 'Operation FAILED: '.$def_warn;
	$the_params = '';
	$the_query_info = ''; // do not display query if not in debug mode ... this a security issue if displayed to public ;)
} //end if else
//--
$out = SmartComponents::app_error_message(
	'SQLite Client',
	'SQLite',
	'Embedded',
	'SQL/DB',
	'lib/core/img/db/sqlite-logo.svg',
	$width, // width
	$the_area, // area
	$the_error_message, // err msg
	$the_params, // title or params
	$the_query_info // sql statement
);
//--
if(!($db instanceof SQLite3)) {
	$the_conn = (string) $db;
} else {
	$the_conn = (string) self::get_connection_id($db);
} //end if else
//--
Smart::raise_error(
	'#SQLITE-DB@'.$the_conn.'# :: Q# // SQLite Client :: ERROR :: '.$y_area."\n".'*** Error-Message: '.$y_error_message."\n".'*** Params / Title:'."\n".print_r($y_params_or_title,1)."\n".'*** Query:'."\n".$y_query,
	$out // msg to display
);
die(''); // just in case
//--
} //END FUNCTION
//======================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================

//end of php code
?>