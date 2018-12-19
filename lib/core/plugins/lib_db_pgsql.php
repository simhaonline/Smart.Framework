<?php
// [LIB - SmartFramework / PostgreSQL Database Client]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

ini_set('pgsql.ignore_notice', '0'); // this is REQUIRED to be set to 0 in order to work with advanced PostgreSQL Notifications (example: write ignores)

// NOTES ABOUT REUSING CONNECTIONS:
//		* BY DEFAULT the PHP PgSQL driver reuses connections if the same host:port@dbname#username are used
//		* this is not enough since Smart.Framework uses also the concept of settings like UTF8 and transaction mode
//		* thus the Smart.Framework implements a separate mechanism to control the connections re-use, to avoid break transactions while mixing (re)connections

//======================================================
// Smart-Framework - PostgreSQL Database Client
// DEPENDS:
//	* Smart::
//	* SmartUnicode::
//	* SmartUtils::
// DEPENDS-EXT: PHP PgSQL Extension
//======================================================
// Tested and Stable on PgSQL versions:
// 9.0.x / 9.1.x / 9.2.x / 9.3.x / 9.4.x / 9.5.x / 9.6.x
// Tested and Stable with PgPool-II versions:
// 3.0.x / 3.1.x / 3.2.x / 3.3.x / 3.4.x / 3.5.x
// Tested and Stable with PgBouncer:
// all versions
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartPgsqlDb - provides a Static PostgreSQL DB Server Client that can be used just with the DEFAULT connection from configs.
 *
 * This class provides an easy and convenient way to work with the PostgreSQL DEFAULT connection, as all methods are static.
 * It can be used just with the DEFAULT connection which must be set in etc/config.php: $configs['pgsql'].
 * It connects automatically, when needed (the connection is lazy, and is made just when is needed to avoid permanent connections to PgSQL which slower down the app and takes busy the slots).
 * NOTICE: You should never modify the (optional) connection parameter which should always have the value of (string) 'DEFAULT' for this static class to work.
 * Actually you should not use this parameter at all as it is optional ... This parameter is reserved for advanced usage to implement derived classes like SmartPgsqlExtDb !
 *
 * <code>
 *
 * // The connection to the DEFAULT PostgreSQL Server will be done automatically, when needed, using the config parameters ; but if you want to pre-connect, use SmartPgsqlDb::default_connect() ...
 * $count = (int) SmartPgsqlDb::count_data('SELECT COUNT("id") FROM "table" WHERE ("active" = \''.SmartPgsqlDb::escape_str('some-id').'\')');
 * $non_associative_read_multi_records = (array) SmartPgsqlDb::read_data('SELECT * FROM "table" WHERE "id" = '.SmartPgsqlDb::escape_literal(3));
 * $associative_read_multi_records = (array) SmartPgsqlDb::read_adata('SELECT * FROM "table" WHERE "id" = $1', array('other-id'));
 * $associative_read_for_just_one_record = (array) SmartPgsqlDb::read_asdata('SELECT * FROM "table" WHERE "id" = $1 LIMIT 1 OFFSET 0', array(99)); // NOTICE: this function will return just one record, so always use LIMIT 1 OFFSET 0 (or LIMIT 0,1) ; if the query will return more records will raise an error
 * $update = (array) SmartPgsqlDb::write_data('UPDATE "table" SET "active" = 1 WHERE "id" = $1', array(55)); // will return an array[ 0 => message, 1 => (integer) affected rows ]
 * $arr_insert = array(
 * 		'id' => 100,
 * 		'active' => 1,
 * 		'name' => 'Test Record'
 * );
 * $insert = (array) SmartPgsqlDb::write_data('INSERT INTO "table" '.SmartPgsqlDb::prepare_statement($arr_insert, 'insert'));
 * $prepared_sql = SmartPgsqlDb::prepare_param_query('SELECT * FROM "table" WHERE "id" = $1', [99]);
 *
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 * @hints		This class have no catcheable exception because the ONLY errors will raise are when the server returns an ERROR regarding a malformed SQL Statement, which is not acceptable to be just exception, so will raise a fatal error !
 *
 * @depends 	extensions: PHP PostgreSQL ; classes: Smart, SmartUnicode, SmartUtils
 * @version 	v.181219
 * @package 	Database:PostgreSQL
 *
 */
final class SmartPgsqlDb {

	// ::

	private static $slow_time = 0.0050;
	private static $server_version = [];


//======================================================
/**
 * Pre-connects manually to the Default PostgreSQL Server.
 * This function is OPTIONAL as the connection on the DEFAULT PostgreSQL Server will be done automatically when needed.
 * Anyway, if there is a need to create an explicit connection to the DEFAULT PostgreSQL server earlier, this function can be used by example in App Bootstrap.
 *
 */
public static function default_connect() {
	//--
	return self::check_connection('DEFAULT', 'DEFAULT-CONNECT');
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Create a PostgreSQL Server Custom Connection.
 * This MUST NOT be used with the default connection ... as that is handled automatically.
 *
 * @param STRING $yhost 						:: db host
 * @param STRING $yport 						:: db port
 * @param STRING $ydb 							:: db name
 * @param STRING $yuser							:: db user
 * @param STRING $ypass							:: db pass
 * @param INTEGER $ytimeout 					:: connection timeout
 * @param ENUM $y_transact_mode					:: transactional mode ('READ COMMITTED' | 'REPEATABLE READ' | 'SERIALIZABLE' | '' to leave it as default)
 * @param FLOAT $y_debug_sql_slowtime			:: debug query slow time
 * @param ENUM $y_type							:: server type: postgresql or pgpool2
 *
 * @return RESOURCE								:: the postgresql connection resource ID
 *
 * @access 		private
 * @internal
 *
 */
public static function server_connect($yhost, $yport, $ydb, $yuser, $ypass, $ytimeout, $y_transact_mode='', $y_debug_sql_slowtime=0, $y_type='postgresql') {

	//--
	if(defined('SMART_FRAMEWORK_DBSQL_CHARSET')) {
		if((string)SMART_FRAMEWORK_DBSQL_CHARSET != 'UTF8') {
			self::error('[PRE-CONNECT]', 'PHP-PgSQL', 'Check App Configuration', 'The SMART_FRAMEWORK_DBSQL_CHARSET must be set as: UTF8', 'Invalid INI Settings');
			return;
		} //end if
	} else {
		self::error('[PRE-CONNECT]', 'PHP-PgSQL', 'Check App Configuration', 'The SMART_FRAMEWORK_DBSQL_CHARSET must be set', 'Invalid INI Settings');
		return;
	} //end if else
	//--

	//--
	if(!function_exists('pg_connect')) {
		self::error('[PRE-CONNECT]', 'PHP-PgSQL', 'Check PgSQL PHP Extension', 'PHP Extension is required to run this software !', 'Cannot find PgSQL PHP Extension');
		return;
	} //end if
	//--
	if((string)ini_get('pgsql.ignore_notice') != '0') { // {{{SYNC-PGSQL-NOTIF-CHECK}}}
		self::error('[PRE-CONNECT]', 'PHP-Inits-PgSQL', 'Check PgSQL PHP.INI Settings', 'SETTINGS: PostgreSQL Notifications need to be ENABLED in PHP.INI !', 'SET in PHP.INI this: pgsql.ignore_notice = 0');
		return;
	} //end if
	//--

	//-- connection timeout
	$timeout = (int) $ytimeout;
	//--
	if($timeout < 1) {
		$timeout = 1;
	} //end if
	if($timeout > 60) {
		$timeout = 60;
	} //end if
	//--

	//-- debug settings
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		$y_debug_sql_slowtime = (float) $y_debug_sql_slowtime;
		if($y_debug_sql_slowtime <= 0) {
			$y_debug_sql_slowtime = (float) self::$slow_time;
		} //end if
		//--
		if($y_debug_sql_slowtime < 0.0000001) {
			$y_debug_sql_slowtime = 0.0000001;
		} elseif($y_debug_sql_slowtime > 0.9999999) {
			$y_debug_sql_slowtime = 0.9999999;
		} //end if
		//--
		self::$slow_time = (float) $y_debug_sql_slowtime; // update
		//--
	} //end if
	//--

	//-- debug inits
	if(SmartFrameworkRuntime::ifDebug()) {
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|slow-time', number_format(self::$slow_time, 7, '.', ''), '=');
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'metainfo',
			'data' => 'Database Server: PgSQL ('.$y_type.') / App Connector Version: '.SMART_FRAMEWORK_VERSION.' / Connection Charset: '.SMART_FRAMEWORK_DBSQL_CHARSET
		]);
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'metainfo',
			'data' => 'Connection Timeout: '.$timeout.' seconds / Fast Query Reference Time < '.self::$slow_time.' seconds'
		]);
	} //end if
	//--

	//--
	if((string)$ypass != '') {
		$password = (string) base64_decode((string)$ypass);
	} else {
		$password = '';
	} //end if else
	//--

	//-- {{{SYNC-CONNECTIONS-IDS}}}
	$the_conn_key = (string) $yhost.':'.$yport.'@'.$ydb.'#'.$yuser;
	//--
	$connection = @pg_connect('host='.$yhost.' port='.$yport.' dbname='.$ydb.' user='.$yuser.' password='.$password.' connect_timeout='.$timeout);
	// @pg_close($connection) (if is resource) ; but reusing connections policy dissalow disconnects
	//--
	if(!is_resource($connection)) {
		self::error($yhost.':'.$yport.'@'.$ydb.'#'.$yuser, 'Connection', 'Connect to PgSQL Server', 'NO CONNECTION !!!', 'Connection Failed to PgSQL Server !');
		return;
	} //end if
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'open-close',
			'data' => 'Connected to PgSQL Server: '.$the_conn_key,
			'connection' => (string) $connection
		]);
	} //end if
	//--

	//--
	@pg_set_error_verbosity($connection, PGSQL_ERRORS_DEFAULT); // this must be reset to PGSQL_ERRORS_DEFAULT and must NOT use PGSQL_ERRORS_VERBOSE because will affect write-igdata notice messages
	//--
	$tmp_pg_tracefile = 'tmp/logs/pgsql-trace.log';
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		if(defined('SMART_FRAMEWORK_DEBUG_SQL_TRACE')) {
			if(function_exists('pg_trace')) {
				@pg_trace($tmp_pg_tracefile, 'w', $connection); // pg_trace can cause some PHP versions to crash (Ex: Debian 6.0.6 with PHP 5.3 / Apache 2.0.x)
			} //end if
		} //end if
		//--
	} //end if else
	//--

	//--
	$result = @pg_query_params($connection, 'SELECT pg_encoding_to_char("encoding") FROM "pg_database" WHERE "datname" = $1', array($ydb));
	if(!$result) {
		self::error($connection, 'Encoding-Charset', 'Check Query Failed', 'Error='.@pg_last_error($connection), 'DB='.$ydb);
		return;
	} //end if
	$server_encoding = @pg_fetch_row($result);
	if((!is_array($server_encoding)) OR ((string)trim((string)$server_encoding[0]) != (string)trim((string)SMART_FRAMEWORK_DBSQL_CHARSET))) {
		self::error($connection, 'Encoding-Get-Charset', 'Wrong Server Encoding on PgSQL Server', 'Server='.$server_encoding[0], 'Client='.SMART_FRAMEWORK_DBSQL_CHARSET);
		return;
	} //end if
	@pg_free_result($result);
	//--

	//--
	$encoding = @pg_set_client_encoding($connection, SMART_FRAMEWORK_DBSQL_CHARSET);
	//--
	if(($encoding < 0) OR ((string)@pg_client_encoding() != (string)SMART_FRAMEWORK_DBSQL_CHARSET)) {
		self::error($connection, 'Encoding-Check-Charset', 'Failed to set Client Encoding on PgSQL Server', 'Server='.SMART_FRAMEWORK_DBSQL_CHARSET, 'Client='.@pg_client_encoding());
		return;
	} //end if
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'set',
			'data' => 'SET Client Encoding [+check] to: '.@pg_client_encoding(),
			'connection' => (string) $connection,
			'skip-count' => 'yes'
		]);
	} //end if
	//--

	//--
	$transact = strtoupper((string)$y_transact_mode);
	switch((string)$transact) {
		case 'SERIALIZABLE':
		case 'REPEATABLE READ':
		case 'READ COMMITTED':
			//--
			$result = @pg_query($connection, 'SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL '.$transact);
			if(!$result) {
				self::error($connection, 'Set-Session-Transaction-Level', 'Failed to Set Session Transaction Level as '.$transact, 'Error='.@pg_last_error($connection), 'DB='.$ydb);
				return;
			} //end if
			@pg_free_result($result);
			//--
			$result = @pg_query('SHOW transaction_isolation');
			$chk = @pg_fetch_row($result);
			if((!is_array($chk)) OR ((string)trim((string)$chk[0]) == '') OR ((string)$transact != (string)strtoupper((string)trim((string)$chk[0])))) {
				self::error($connection, 'Check-Session-Transaction-Level', 'Failed to Set Session Transaction Level as '.$transact, 'Error='.@pg_last_error($connection), 'DB='.$ydb);
				return;
			} //end if
			if(SmartFrameworkRuntime::ifDebug()) {
				SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
					'type' => 'set',
					'data' => 'SET Session Transaction Isolation Level [+check] to: '.strtoupper($chk[0]),
					'connection' => (string) $connection,
					'skip-count' => 'yes'
				]);
			} //end if
			@pg_free_result($result);
			//--
			break;
		default:
			// LEAVE THE SESSION TRANSACTION AS SET IN CFG
	} //end switch
	//--

	//-- export only at the end (after all settings)
	SmartFrameworkRegistry::$Connections['pgsql'][(string)$the_conn_key] = $connection; // export connection
	//--

	//-- OUTPUT
	return $connection;
	//-- OUTPUT

} //END FUNCTION
//======================================================


//======================================================
/**
 * Fix a string to be compliant with PgSQL LIKE / ILIKE / SIMILAR syntax.
 * It will use special quotes for the LIKE / ILIKE / SIMILAR special characters: % _
 * This function IS NOT INTENDED TO ESCAPE AGAINST SQL INJECTIONS ; USE IT ONLY WITH PREPARED PARAMS OR USE escape_str() with mode 'likes' / escape_literal() with mode 'likes'
 *
 * @param STRING $y_string						:: A String or a Number to be Quoted for LIKES
 */
public static function quote_likes($y_string) {
	//--
	return (string) str_replace(['_', '%'], ['\\_', '\\%'], (string)$y_string); // escape for LIKE / ILIKE / SIMILAR: extra special escape: _ = \_ ; % = \%
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Fix a string to be compliant with PgSQL REGEX syntax.
 * It will use special quotes for the REGEX special characters: . \ + * ? [ ^ ] $ ( ) { } = ! < > | : -
 * This function IS NOT INTENDED TO ESCAPE AGAINST SQL INJECTIONS ; USE IT ONLY WITH PREPARED PARAMS OR USE escape_str() with mode 'regex' / escape_literal() with mode 'regex'
 *
 * @param STRING $y_string						:: A String or a Number to be Quoted for REGEX
 */
public static function quote_regex($y_string) {
	//--
	return (string) preg_quote((string)str_replace(['\\'], [''], (string)$y_string)); // escape for regex: ~ ~* !~ !~*
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Escape a string to be compliant and Safe (against SQL Injection) with PgSQL standards.
 * This function WILL NOT ADD the SINGLE QUOTES (') arround the string, but just will just escape it to be safe.
 *
 * @param STRING $y_string						:: A String or a Number to be Escaped
 * @param ENUM $y_mode							:: '' = default ; 'likes' = Escape LIKE / ILIKE / SIMILAR Syntax (% _) ; :: '' = default ; 'regex' = Escape ~ ~* !~ !~* Syntax
 * @param RESOURCE $y_connection				:: the connection
 * @return STRING 								:: The Escaped String / Number
 *
 */
public static function escape_str($y_string, $y_mode='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'ESCAPE-STR:['.$y_mode.']');
	//==

	//-- Fix
	$y_string = (string) SmartUnicode::fix_charset((string)$y_string);
	$y_mode = (string) trim((string)strtolower((string)$y_mode));
	//--

	//--
	if((string)$y_mode == 'likes') { // escape for LIKE / ILIKE / SIMILAR: extra special escape: _ = \_ ; % = \%
		$y_string = (string) self::quote_likes((string)$y_string);
	} elseif((string)$y_mode == 'regex') { // escape for regex: ~ ~* !~ !~*
		$y_string = (string) self::quote_regex((string)$y_string);
	} //end if else
	//--
	$y_string = (string) @pg_escape_string($y_connection, (string)$y_string); // [CONN]
	//--

	//--
	return (string) $y_string;
	//--

} // END FUNCTION
//======================================================


//======================================================
/**
 * Escape a variable in the literal way to be compliant and Safe (against SQL Injection) with PgSQL standards.
 * This function WILL ADD the SINGLE QUOTES (') arround the string as needed and will escape expressions containing backslashes \ in the postgresql way using E'' escapes.
 * This is the preferred way to escape variables inside PostgreSQL SQL Statements, and is better than escape_str().
 *
 * @param STRING $y_string						:: A String or a Number to be Escaped
 * @param ENUM $y_mode							:: '' = default ; 'likes' = Escape LIKE / ILIKE / SIMILAR Syntax (% _) ; :: '' = default ; 'regex' = Escape ~ ~* !~ !~* Syntax
 * @param RESOURCE $y_connection				:: the connection
 * @return STRING 								:: The Escaped String / Number
 *
 */
public static function escape_literal($y_string, $y_mode='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'ESCAPE-LITERAL:['.$y_mode.']');
	//==

	//-- Fix
	$y_string = (string) SmartUnicode::fix_charset((string)$y_string);
	$y_mode = (string) trim((string)strtolower((string)$y_mode));
	//--

	//--
	if((string)$y_mode == 'likes') { // escape for LIKE / ILIKE / SIMILAR: extra special escape: _ = \_ ; % = \%
		$y_string = (string) self::quote_likes((string)$y_string);
	} elseif((string)$y_mode == 'regex') { // escape for regex: ~ ~* !~ !~*
		$y_string = (string) self::quote_regex((string)$y_string);
	} //end if else
	//--
	$y_string = (string) @pg_escape_literal($y_connection, (string)$y_string); // [CONN]
	//--

	//--
	return (string) $y_string;
	//--

} // END FUNCTION
//======================================================


//======================================================
/**
 * Escape an identifier to be compliant and Safe (against SQL Injection) with PgSQL standards.
 * This function WILL ADD the DOUBLE QUOTES (") arround the identifiers (fields / table names) as needed.
 *
 * @param STRING $y_identifier					:: The Identifier to be Escaped: field / table
 * @param RESOURCE $y_connection				:: the connection
 * @return STRING 								:: The Escaped Identifier as: "field" / "table"
 *
 */
public static function escape_identifier($y_identifier, $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'ESCAPE-IDENTIFIER');
	//==

	//-- Fix
	$y_identifier = (string) SmartUnicode::utf8_to_iso((string)$y_identifier); // this is in sync with validate table and field names to make them all ISO
	$y_identifier = (string) SmartUnicode::fix_charset((string)$y_identifier); // fix in the case that something went wrong
	$y_identifier = (string) str_replace('?', '', (string)$y_identifier); // remove ? after conversion
	//--

	//--
	$y_identifier = (string) @pg_escape_identifier($y_connection, (string)$y_identifier); // [CONN]
	//--

	//--
	return (string) $y_identifier;
	//--

} // END FUNCTION
//======================================================


//======================================================
/**
 * Fix charset for param queries
 * Used for: pg_query_params()
 *
 * @param ARRAY $arr_params						:: A mixed variable
 * @return STRING 								:: JSON string
 *
 */
private static function escape_arr_params($arr_params) {

	//--
	if(is_array($arr_params)) {
		foreach($arr_params as $k => $v) {
			$arr_params[$k] = (string) SmartUnicode::fix_charset((string)$v); // fix
		} //end foreach
	} //end if
	//--

	//--
	return $arr_params; // mixed: this should not be enforced to a type ... must remain as it is
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * PostgreSQL compliant and Safe Json Encode.
 * This should be used with PostgreSQL json / jsonb fields.
 *
 * @param STRING $y_mixed_content				:: A mixed variable
 * @return STRING 								:: JSON string
 *
 */
public static function json_encode($y_mixed_content) {
	//--
	$json = (string) @json_encode($y_mixed_content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); // Fix: must return a string ; depth was added in PHP 5.5 only !
	if((string)$json == '') {
		Smart::log_warning('Invalid Encoded Json in '.__METHOD__.'() for input: '.print_r($y_mixed_content,1));
		$json = '{}'; // FIX: in PostgreSQL JSON/JSON-B fields cannot be empty !!!
	} //end if
	//--
	return (string) $json;
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Check if a Schema Exists in the current Database.
 *
 * @param STRING $y_schema 						:: The Schema Name
 * @param RESOURCE $y_connection				:: The connection to PgSQL server
 * @return 0/1									:: 1 if exists ; 0 if not
 *
 */
public static function check_if_schema_exists($y_schema, $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'SCHEMA-CHECK-IF-EXISTS');
	//==

	//--
	$arr_data = self::read_data('SELECT "nspname" FROM "pg_namespace" WHERE ("nspname" = \''.self::escape_str($y_schema, '', $y_connection).'\')', 'Check if Schema Exists', $y_connection);
	//--
	if((string)$arr_data[0] == (string)$y_schema) {
		$out = 1;
	} else {
		$out = 0;
	} //end if else
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Check if a Table Exists in the current Database.
 *
 * @param STRING $y_table 						:: The Table Name
 * @param STRING $y_schema						:: The Schema Name
 * @param RESOURCE $y_connection				:: The connection to PgSQL server
 * @return 0/1									:: 1 if exists ; 0 if not
 *
 */
public static function check_if_table_exists($y_table, $y_schema='public', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'TABLE-CHECK-IF-EXISTS');
	//==

	//--
	$y_table = (string) str_replace('"', '', (string)$y_table);
	//--

	//--
	$arr_data = self::read_data('SELECT "tablename", "schemaname" FROM "pg_tables" WHERE (("schemaname" = \''.self::escape_str($y_schema, '', $y_connection).'\') AND ("tablename" = \''.self::escape_str($y_table, '', $y_connection).'\'))', 'Check if Table Exists', $y_connection);
	//--
	if((string)$arr_data[0] == (string)$y_table) {
		$out = 1;
	} else {
		$out = 0;
	} //end if else
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * PgSQL Query :: Count
 * This function is intended to be used for count type queries: SELECT COUNT().
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @param RESOURCE $y_connection				:: the connection
 * @return INTEGER								:: the result of COUNT()
 */
public static function count_data($queryval, $params_or_title='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'COUNT-DATA');
	//==

	//-- samples
	// $queryval = 'SELECT COUNT(*) FROM "tablename" WHERE ("field" = \'x\')';
	//--

	//--
	$time_start = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_start = microtime(true);
	} //end if
	//--

	//--
	$use_param_query = false;
	if(Smart::array_size($params_or_title) > 0) {
		$use_param_query = true;
	} //end if
	//--
	if($use_param_query === true) {
		$the_query_title = '';
		if(array_key_exists('@title', $params_or_title) OR array_key_exists('@params', $params_or_title)) {
			$the_query_title = (string) $params_or_title['@title'];
			if(is_array($params_or_title['@params'])) {
				$params_or_title = (array) $params_or_title['@params'];
			} else {
				$params_or_title = array();
			} //end if else
		} //end if
		$params_or_title = self::escape_arr_params($params_or_title); // fix charset
		$result = @pg_query_params($y_connection, $queryval, $params_or_title);
	} else {
		$the_query_title = (string) $params_or_title;
		$result = @pg_query($y_connection, $queryval);
	} //end if else
	//--

	//--
	$error = '';
	if(!$result) {
		$error = 'Query FAILED: '.@pg_last_error($y_connection);
	} //end if else
	//--

	//--
	$time_end = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_end = (float) (microtime(true) - (float)$time_start);
	} //end if
	//--

	//--
	$pgsql_result_count = 0; // store COUNT data
	if((string)$error == '') {
		$record = @pg_fetch_row($result);
		if(is_array($record)) {
			$pgsql_result_count = Smart::format_number_int($record[0]);
		} //end if
	} //end if
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-queries', 1, '+');
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$dbg_query_params = (array) $params_or_title;
		} else {
			$dbg_query_params = '';
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'count',
			'data' => 'COUNT :: '.$the_query_title,
			'query' => $queryval,
			'params' => $dbg_query_params,
			'rows' => $pgsql_result_count,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) $y_connection
		]);
		//--
	} //end if
	//--

	//-- init vars
	if((string)$error != '') {
		//--
		self::error($y_connection, 'COUNT-DATA', $error, $queryval, $params_or_title);
		return 0;
		//--
	} //end else
	//--

	//--
	@pg_free_result($result);
	//--

	//--
	return Smart::format_number_int($pgsql_result_count, '+'); // be sure is 0 or greater
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * PgSQL Query :: Read (Non-Associative) one or multiple rows.
 * This function is intended to be used for read type queries: SELECT.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @param RESOURCE $y_connection				:: the connection
 * @return ARRAY (non-asociative) of results	:: array('column-0-0', 'column-0-1', ..., 'column-0-n', 'column-1-0', 'column-1-1', ... 'column-1-n', ..., 'column-m-0', 'column-m-1', ..., 'column-m-n')
 */
public static function read_data($queryval, $params_or_title='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'READ-DATA');
	//==

	//-- samples
	// $queryval = 'SELECT * FROM "tablename" WHERE ("field" = \'x\') ORDER BY "field" ASC LIMIT '.$limit.' OFFSET '.$offset; // [LIMIT-OFFSET]
	//--

	//--
	$time_start = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_start = microtime(true);
	} //end if
	//--

	//--
	$use_param_query = false;
	if(Smart::array_size($params_or_title) > 0) {
		$use_param_query = true;
	} //end if
	//--
	if($use_param_query === true) {
		$the_query_title = '';
		if(array_key_exists('@title', $params_or_title) OR array_key_exists('@params', $params_or_title)) {
			$the_query_title = (string) $params_or_title['@title'];
			if(is_array($params_or_title['@params'])) {
				$params_or_title = (array) $params_or_title['@params'];
			} else {
				$params_or_title = array();
			} //end if else
		} //end if
		$params_or_title = self::escape_arr_params($params_or_title); // fix charset
		$result = @pg_query_params($y_connection, $queryval, $params_or_title);
	} else {
		$the_query_title = (string) $params_or_title;
		$result = @pg_query($y_connection, $queryval);
	} //end if else
	//--

	//--
	$error = '';
	if(!$result) {
		$error = 'Query FAILED:'."\n".@pg_last_error($y_connection);
	} //end if else
	//--

	//--
	$time_end = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_end = (float) (microtime(true) - (float)$time_start);
	} //end if
	//--

	//--
	$number_of_rows = 0;
	$number_of_fields = 0;
	if((string)$error == '') {
		$number_of_rows = @pg_num_rows($result);
		$number_of_fields = @pg_num_fields($result);
	} //end if
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-queries', 1, '+');
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$dbg_query_params = (array) $params_or_title;
		} else {
			$dbg_query_params = '';
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'read',
			'data' => 'READ [NON-ASSOCIATIVE] :: '.$the_query_title,
			'query' => $queryval,
			'params' => $dbg_query_params,
			'rows' => $number_of_rows,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) $y_connection
		]);
		//--
	} //end if
	//--

	//-- init vars
	$pgsql_result_arr = array(); // store SELECT data
	//--
	if((string)$error != '') {
		//--
		self::error($y_connection, 'READ-DATA', $error, $queryval, $params_or_title);
		return array();
		//--
	} else {
		//--
		for($i=0; $i<$number_of_rows; $i++) {
			//--
			$record = @pg_fetch_row($result);
			//--
			if(is_array($record)) {
				for($ii=0; $ii<$number_of_fields; $ii++) {
					$pgsql_result_arr[] = (string) $record[$ii]; // force string
				} // end for
			} //end if
			//--
		} //end for
		//--
	} //end else
	//--

	//--
	@pg_free_result($result);
	//--

	//--
	return (array) $pgsql_result_arr;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * PgSQL Query :: Read (Associative) one or multiple rows.
 * This function is intended to be used for read type queries: SELECT.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @param RESOURCE $y_connection				:: the connection
 * @return ARRAY (asociative) of results		:: array(0 => array('column1', 'column2', ... 'column-n'), 1 => array('column1', 'column2', ... 'column-n'), ..., m => array('column1', 'column2', ... 'column-n'))
 */
public static function read_adata($queryval, $params_or_title='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'READ-aDATA');
	//==

	//-- samples
	// $queryval = 'SELECT * FROM "tablename" WHERE ("field" = \'x\') ORDER BY "field" ASC; // [LIMIT-OFFSET]
	//--

	//--
	$time_start = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_start = microtime(true);
	} //end if
	//--

	//--
	$use_param_query = false;
	if(Smart::array_size($params_or_title) > 0) {
		$use_param_query = true;
	} //end if
	//--
	if($use_param_query === true) {
		$the_query_title = '';
		if(array_key_exists('@title', $params_or_title) OR array_key_exists('@params', $params_or_title)) {
			$the_query_title = (string) $params_or_title['@title'];
			if(is_array($params_or_title['@params'])) {
				$params_or_title = (array) $params_or_title['@params'];
			} else {
				$params_or_title = array();
			} //end if else
		} //end if
		$params_or_title = self::escape_arr_params($params_or_title); // fix charset
		$result = @pg_query_params($y_connection, $queryval, $params_or_title);
	} else {
		$the_query_title = (string) $params_or_title;
		$result = @pg_query($y_connection, $queryval);
	} //end if else
	//--

	//--
	$error = '';
	if(!$result) {
		$error = 'Query FAILED:'."\n".@pg_last_error($y_connection);
	} //end if else
	//--

	//--
	$time_end = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_end = (float) (microtime(true) - (float)$time_start);
	} //end if
	//--

	//--
	$number_of_rows = 0;
	$number_of_fields = 0;
	if((string)$error == '') {
		$number_of_rows = @pg_num_rows($result);
		$number_of_fields = @pg_num_fields($result);
	} //end if
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-queries', 1, '+');
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$dbg_query_params = (array) $params_or_title;
		} else {
			$dbg_query_params = '';
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'read',
			'data' => 'aREAD [ASSOCIATIVE] :: '.$the_query_title,
			'query' => $queryval,
			'params' => $dbg_query_params,
			'rows' => $number_of_rows,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) $y_connection
		]);
		//--
	} //end if
	//--

	//-- init vars
	$pgsql_result_arr = array(); // store SELECT data
	//--
	if((string)$error != '') {
		//--
		self::error($y_connection, 'READ-aDATA', $error, $queryval, $params_or_title);
		return array();
		//--
	} else {
		//--
		if($number_of_rows > 0) {
			//--
			for($i=0; $i<$number_of_rows; $i++) {
				//--
				$record = @pg_fetch_array($result, $i, PGSQL_ASSOC);
				//--
				if(is_array($record)) {
					//--
					$tmp_datarow = array();
					//--
					foreach($record as $key => $val) {
						$tmp_datarow[$key] = (string) $val; // force string
					} //end foreach
					//--
					$pgsql_result_arr[] = (array) $tmp_datarow;
					//--
					$tmp_datarow = array();
					//--
				} //end if
				//--
			} //end for
			//--
		} //end if else
		//--
	} //end else
	//--

	//--
	@pg_free_result($result);
	//--

	//--
	return (array) $pgsql_result_arr;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * PgSQL Query :: Read (Associative) - Single Row (just for 1 row, to easy the use of data from queries).
 * !!! This will raise an error if more than one row(s) are returned !!!
 * This function does not support multiple rows because the associative data is structured without row iterator.
 * For queries that return more than one row use: read_adata() or read_data().
 * This function is intended to be used for read type queries: SELECT.
 *
 * @hints	ALWAYS use a LIMIT 1 OFFSET 0 with all queries using this function to avoid situations that will return more than 1 rows and will raise ERROR with this function.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @param RESOURCE $y_connection				:: the connection
 * @return ARRAY (asociative) of results		:: Returns just a SINGLE ROW as: array('column1', 'column2', ... 'column-n')
 */
public static function read_asdata($queryval, $params_or_title='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'READ-asDATA');
	//==

	//-- samples
	// $queryval = 'SELECT * FROM "tablename" WHERE ("field" = \'x\') ORDER BY "field" ASC LIMIT '.$limit.' OFFSET '.$offset; // [LIMIT-OFFSET]
	//--

	//--
	$time_start = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_start = microtime(true);
	} //end if
	//--

	//--
	$use_param_query = false;
	if(Smart::array_size($params_or_title) > 0) {
		$use_param_query = true;
	} //end if
	//--
	if($use_param_query === true) {
		$the_query_title = '';
		if(array_key_exists('@title', $params_or_title) OR array_key_exists('@params', $params_or_title)) {
			$the_query_title = (string) $params_or_title['@title'];
			if(is_array($params_or_title['@params'])) {
				$params_or_title = (array) $params_or_title['@params'];
			} else {
				$params_or_title = array();
			} //end if else
		} //end if
		$params_or_title = self::escape_arr_params($params_or_title); // fix charset
		$result = @pg_query_params($y_connection, $queryval, $params_or_title);
	} else {
		$the_query_title = (string) $params_or_title;
		$result = @pg_query($y_connection, $queryval);
	} //end if else
	//--

	//--
	$error = '';
	if(!$result) {
		$error = 'Query FAILED:'."\n".@pg_last_error($y_connection);
	} //end if else
	//--

	//--
	$time_end = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_end = (float) (microtime(true) - (float)$time_start);
	} //end if
	//--

	//--
	$number_of_rows = 0;
	$number_of_fields = 0;
	if((string)$error == '') {
		$number_of_rows = @pg_num_rows($result);
		$number_of_fields = @pg_num_fields($result);
	} //end if
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-queries', 1, '+');
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$dbg_query_params = (array) $params_or_title;
		} else {
			$dbg_query_params = '';
		} //end if else
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'read',
			'data' => 'asREAD [SINGLE-ROW-ASSOCIATIVE] :: '.$the_query_title,
			'query' => $queryval,
			'params' => $dbg_query_params,
			'rows' => $number_of_rows,
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'connection' => (string) $y_connection
		]);
		//--
	} //end if
	//--

	//-- init vars
	$pgsql_result_arr = array(); // store SELECT data
	//--
	if((string)$error != '') {
		//--
		self::error($y_connection, 'READ-asDATA', $error, $queryval, $params_or_title);
		return array();
		//--
	} else {
		//--
		if($number_of_rows == 1) {
			//--
			$record = @pg_fetch_array($result, 0, PGSQL_ASSOC);
			//--
			if(is_array($record)) {
				foreach($record as $key => $val) {
					$pgsql_result_arr[$key] = (string) $val; // force string
				} //end foreach
			} //end if
			//--
		} elseif($number_of_rows > 1) {
			//--
			self::error($y_connection, 'READ-asDATA', 'The Result contains more than one row ...', $queryval, $params_or_title);
			return array();
			//--
		} //end if else
		//--
	} //end else
	//--

	//--
	@pg_free_result($result);
	//--

	//--
	return (array) $pgsql_result_arr;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * PgSQL Query :: Write.
 * This function is intended to be used for write type queries: BEGIN (TRANSACTION) ; COMMIT ; ROLLBACK ; INSERT ; UPDATE ; CREATE SCHEMAS ; CALLING STORED PROCEDURES ...
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @param RESOURCE $y_connection				:: the connection
 * @return ARRAY 								:: [0 => 'control-message', 1 => #affected-rows]
 */
public static function write_data($queryval, $params_or_title='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'WRITE-DATA');
	//==

	//-- samples
	// $queryval = 'BEGIN'; // start transaction
	// $queryval = 'UPDATE "tablename" SET "field" = \'value\' WHERE ("id_field" = \'val1\')';
	// $queryval = 'INSERT INTO "tablename" ("desiredfield1", "desiredfield2") VALUES (\'val1\', \'val2\')';
	// $queryval = 'DELETE FROM "tablename" WHERE ("id_field" = \'val1\')';
	// $queryval = 'COMMIT'; // commit transaction (on success)
	// $queryval = 'ROLLBACK'; // rollback transaction (on error)
	//--

	//--
	$time_start = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_start = microtime(true);
	} //end if
	//--

	//--
	$use_param_query = false;
	if(Smart::array_size($params_or_title) > 0) {
		$use_param_query = true;
	} //end if
	//--
	if($use_param_query === true) {
		$the_query_title = '';
		if(array_key_exists('@title', $params_or_title) OR array_key_exists('@params', $params_or_title)) {
			$the_query_title = (string) $params_or_title['@title'];
			if(is_array($params_or_title['@params'])) {
				$params_or_title = (array) $params_or_title['@params'];
			} else {
				$params_or_title = array();
			} //end if else
		} //end if
		$params_or_title = self::escape_arr_params($params_or_title); // fix charset
		$result = @pg_query_params($y_connection, $queryval, $params_or_title); // NOTICE: parameters are only allowed in ONE command not combined statements
	} else {
		$the_query_title = (string) $params_or_title;
		$result = @pg_query($y_connection, $queryval);
	} //end if else
	//--

	//--
	$error = '';
	$affected = 0;
	if(!$result) {
		$error = 'Query FAILED:'."\n".@pg_last_error($y_connection);
	} else {
		$affected = @pg_affected_rows($result);
	} //end if else
	//--

	//--
	$time_end = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_end = (float) (microtime(true) - (float)$time_start);
	} //end if
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-queries', 1, '+');
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-time', $time_end, '+');
		//--
		if(is_array($params_or_title)) {
			$dbg_query_params = (array) $params_or_title;
		} else {
			$dbg_query_params = '';
		} //end if else
		//--
		if((strtoupper(substr(trim($queryval), 0, 5)) == 'BEGIN') OR (strtoupper(substr(trim($queryval), 0, 17)) == 'START TRANSACTION') OR (strtoupper(substr(trim($queryval), 0, 6)) == 'COMMIT') OR (strtoupper(substr(trim($queryval), 0, 8)) == 'ROLLBACK')) {
			SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
				'type' => 'transaction',
				'data' => 'TRANSACTION :: '.$the_query_title,
				'query' => $queryval,
				'params' => '',
				'time' => Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $y_connection
			]);
		} elseif(strtoupper(substr(trim($queryval), 0, 4)) == 'SET ') {
			SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
				'type' => 'set',
				'data' => 'SET :: '.$the_query_title,
				'query' => $queryval,
				'params' => $dbg_query_params,
				'time' => Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $y_connection
			]);
		} else {
			SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
				'type' => 'write',
				'data' => 'WRITE :: '.$the_query_title,
				'query' => $queryval,
				'params' => $dbg_query_params,
				'rows' => $affected,
				'time' => Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $y_connection
			]);
		} //end if else
		//--
	} //end if
	//--

	//--
	if((string)$error != '') {
		//--
		$message = 'errorsqlwriteoperation: '.$error;
		//--
		self::error($y_connection, 'WRITE-DATA', $error, $queryval, $params_or_title);
		return array($message, 0);
		//--
	} else {
		//--
		$record = @pg_fetch_row($result);
		//--
		$message = 'oksqlwriteoperation'; // this can be extended to detect extra notices
		//--
	} //end else
	//--

	//--
	@pg_free_result($result);
	//--

	//--
	return array($message, Smart::format_number_int($affected, '+'));
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * PgSQL Query :: Write Ignore - Catch Duplicate Key Violation or Foreign Key Violation Errors (This is the equivalent of MySQL's INSERT IGNORE / UPDATE IGNORE / DELETE IGNORE, but it can catch UNIQUE violations on both: INSERT / UPDATE / DELETE statements and also can catch FOREIGN KEY violations).
 * This function is intended to be used only for write type queries like: INSERT / UPDATE / DELETE which can be ignored if unique violations or foreign key violations and will return the # of affected rows or zero if an exception raised.
 * The catch of PostgreSQL exceptions is handled completely by this function so there is no need for a catch errors outside.
 *
 * IMPORTANT:
 * This function needs the pgsql notice message tracking enabled in PHP (not ignored); This must be set in php.ini (pgsql.ignore_notice = 0).
 * The internal mechanism of this function to catch UNIQUE or FOREIGN KEYS violations is that the EXCEPTIONS are catch at the PostgreSQL level in a DO block.
 * This is the best approach to handle safe UPSERT or INSERT IGNORE / UPDATE IGNORE / DELETE IGNORE like queries in high load envionments or to avoid fatal errors when a INSERT / UPDATE / DELETE violates a unique key or a foreign key with PostgreSQL.
 * This function can be used inside transactions blocks but never use this function to execute statements as: BEGIN, START TRANSACTION, COMMIT, ROLLBACK or SET statements, as the context is incompatible.
 * HINTS:
 * On PostgreSQL 9.5/later there is an alternative which can be used directly with write_data() without the need of this function as the following statement: INSERT ... ON CONFLICT DO NOTHING/UPDATE ... (as the equivalent of INSERT IGNORE / UPSERT), but the following statements are still missing (not implemented): UPDATE ... ON CONFLICT DO NOTHING / DELETE ... ON CONFLICT DO NOTHING .
 * This function will remain in the future to offer backward compatibility with PostgreSQL 8.4 ... 9.5 even if PostgreSQL at some moment will have ON CONFLICT DO implemented for all 3 INSERT / UPDATE / DELETE.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @param RESOURCE $y_connection				:: the connection
 * @return ARRAY 								:: [0 => 'control-message', 1 => #affected-rows]
 */
public static function write_igdata($queryval, $params_or_title='', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'WRITE-IG-DATA');
	//==

	//-- samples
	// $queryval = 'UPDATE "tablename" SET "field" = \'value\' WHERE ("id_field" = \'val1\')';
	// $queryval = 'INSERT INTO "tablename" ("desiredfield1", "desiredfield2") VALUES (\'val1\', \'val2\')';
	//--

	// ##### 'pgsql.ignore_notice' must be set to 0 in PHP.INI (checked via connect) #####

	//--
	/* PRE-CHECK (DO NOT ALLOW IN TRANSACTION BLOCKS) - No More Necessary !!, now can be safe used also in transactions as the exceptions are catch in the DO block
	$transact_status = @pg_transaction_status($y_connection);
	if(($transact_status === PGSQL_TRANSACTION_INTRANS) OR ($transact_status === PGSQL_TRANSACTION_INERROR)) {
		self::error($y_connection, 'WRITE-IG-DATA', 'ERROR: Write Ignore cannot be used inside Transaction Blocks ...', $queryval, '');
		return array('errortransact: '.'Write Ignore cannot be used inside Transaction Blocks', 0);
	} //end if
	*/
	//--

	//--
	$time_start = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_start = microtime(true);
	} //end if
	//--

	//--
	$use_param_query = false;
	if((strpos((string)$queryval, '$') !== false) AND (Smart::array_size($params_or_title) > 0)) {
		$use_param_query = true;
	} //end if
	//--
	$the_query_title = '';
	if($use_param_query === true) {
		if(array_key_exists('@title', $params_or_title) OR array_key_exists('@params', $params_or_title)) {
			$the_query_title = (string) $params_or_title['@title'];
			if(is_array($params_or_title['@params'])) {
				$params_or_title = (array) $params_or_title['@params'];
			} else {
				$params_or_title = array();
			} //end if else
		} //end if
		$params_or_title = self::escape_arr_params($params_or_title); // fix charset
	} else {
		if(!is_array($params_or_title)) {
			$the_query_title = (string) $params_or_title;
		} //end if
	} //end if else
	//--

	//--
	/* At the moment, in PgSQL 9.5 only works ON CONFLICT DO NOTHING for INSERT (for UPDATE statements fails ...)
	if(version_compare(self::check_server_version($y_connection), '9.6') >= 0) {
		//--
		$xmode = 'affected';
		$vmode = '[ON CONFLICT DO NOTHING]';
		//--
		$prep_query = (string) $queryval.' ON CONFLICT DO NOTHING'; // fix for PostgreSQL >= 9.5 :: RETURNING *
		//--
		if($use_param_query === true) {
			$result = @pg_query_params($y_connection, $prep_query, $params_or_title); // NOTICE: parameters are only allowed in ONE command not combined statements
		} else {
			$result = @pg_query($y_connection, $prep_query);
		} //end if else
		//--
	} else {
	*/
	//--
	if((string)ini_get('pgsql.ignore_notice') != '0') { // {{{SYNC-PGSQL-NOTIF-CHECK}}}
		self::error($y_connection, 'WRITE-IG-DATA', 'Check PgSQL PHP.INI Settings', 'SETTINGS: PostgreSQL Notifications need to be ENABLED in PHP.INI !', 'SET in PHP.INI this: pgsql.ignore_notice = 0');
		return array('errorinits: PostgreSQL Notifications need to be ENABLED in PHP.INI', 0);
	} //end if
	//--
	$xmode = 'notice';
	$vmode = '[Catch EXCEPTION on Violations for: Unique / Foreign Key]';
	//--
	if($use_param_query === true) {
		$queryval = (string) self::prepare_param_query((string)$queryval, (array)$params_or_title, $y_connection);
	} //end if
	//--
	$unique_id = 'WrIgData_PgSQL_'.Smart::uuid_10_seq().'_'.Smart::uuid_10_str().'_'.Smart::uuid_10_num().'_'.sha1(SmartUtils::client_ident_private_key()).'_'.sha1(SmartUtils::get_visitor_tracking_uid().':'.Smart::uuid_36('pgsql-write-ig').':'.Smart::uuid_45('pgsql-write-ig')).'_Func'; // this must be a unique that cannot guess to avoid dollar escaping injections
	//--
	$prep_query = (string) '
	DO LANGUAGE plpgsql
	$'.$unique_id.'$
	DECLARE affected_rows BIGINT;
	BEGIN
		-- do the query an safe catch exceptions (unique key, foreign key)
			affected_rows := 0;
	'."\t\t".trim(rtrim($queryval, ';')).';'.'
			GET DIAGNOSTICS affected_rows = ROW_COUNT;
			RAISE NOTICE \'SMART-FRAMEWORK-PGSQL-NOTICE: AFFECTED ROWS #%\', affected_rows;
			RETURN;
		EXCEPTION
			WHEN unique_violation THEN RAISE NOTICE \'SMART-FRAMEWORK-PGSQL-NOTICE: AFFECTED ROWS #0\';
			WHEN foreign_key_violation THEN RAISE NOTICE \'SMART-FRAMEWORK-PGSQL-NOTICE: AFFECTED ROWS #0\'; -- this is a different behaviour than ON CONFLICT DO NOTHING in PgSQL 9.5 or later versions ...
	END
	$'.$unique_id.'$;
	';
	//--
	$result = @pg_query($y_connection, $prep_query);
	//--
	//} //end if else
	//--

	//--
	$error = '';
	$affected = 0;
	if(!$result) {
		$error = 'Query FAILED:'."\n".@pg_last_error($y_connection);
	} else {
		//if((string)$xmode == 'notice') {
		$affected = (int) self::get_notice_smart_affected_rows(@pg_last_notice($y_connection)); // in this case we can only monitor affected rows via a custom notice (the only possible way to return something from anonymous pgsql functions ...)
		//} else { // affected
		//	$affected = @pg_affected_rows($result); // for PostgreSQL >= 9.5
		//} //end if else
	} //end if else
	//--

	//--
	$time_end = 0;
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_end = (float) (microtime(true) - (float)$time_start);
	} //end if
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-queries', 1, '+');
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|total-time', $time_end, '+');
		//--
		$dbg_query_params = '';
		//--
		if((strtoupper(substr(trim($queryval), 0, 5)) == 'BEGIN') OR (strtoupper(substr(trim($queryval), 0, 17)) == 'START TRANSACTION') OR (strtoupper(substr(trim($queryval), 0, 6)) == 'COMMIT') OR (strtoupper(substr(trim($queryval), 0, 8)) == 'ROLLBACK')) {
			// ERROR
			self::error($y_connection, 'WRITE-IG-DATA '.$vmode, 'ERROR: This function cannot handle TRANSACTION Specific Statements ...', $queryval, $the_query_title);
			return array('errorsqlstatement: '.'This function cannot handle TRANSACTION Specific Statements', 0);
		} elseif(strtoupper(substr(trim($queryval), 0, 4)) == 'SET ') {
			// ERROR
			self::error($y_connection, 'WRITE-IG-DATA '.$vmode, 'ERROR: This function cannot handle SET Statements ...', $queryval, $the_query_title);
			return array('errorsqlstatement: '.'This function cannot handle SET Statements', 0);
		} else {
			SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
				'type' => 'write',
				'data' => 'WRITE / IGNORE '.$vmode.' :: '.$the_query_title,
				'query' => $queryval,
				'params' => $dbg_query_params,
				'rows' => $affected,
				'time' => Smart::format_number_dec($time_end, 9, '.', ''),
				'connection' => (string) $y_connection
			]);
		} //end if else
		//--
	} //end if
	//--

	//--
	if((string)$error != '') {
		//--
		$message = 'errorsqlwriteoperation: '.$error;
		//--
		self::error($y_connection, 'WRITE-IG-DATA '.$vmode, $error, $queryval, $the_query_title);
		return array($message, 0);
		//--
	} else {
		//--
		$record = @pg_fetch_row($result);
		//--
		$message = 'oksqlwriteoperation'; // this can be extended to detect extra notices
		//--
	} //end else
	//--

	//--
	if(is_resource($result)) { // check in case of error
		@pg_free_result($result);
	} //end if
	//--

	//--
	return array($message, Smart::format_number_int($affected, '+'));
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Create Escaped Write SQL Statements from Data - to be used with PgSQL for: INSERT ; INSERT-SUBSELECT ; UPDATE ; IN-SELECT ; DATA-ARRAY
 * Can be used with: write_data() or write_igdata() to build INSERT / INSERT (SELECT) / UPDATE queries from an associative array
 * or can be used with read_data(), read_adata(), read_asdata(), count_data() to build IN-SELECT / DATA-ARRAY queries from a non-associative array
 *
 * @param ARRAY-associative $arrdata			:: associative array: array of form data as $arr=array(); $arr['field1'] = 'a string'; $arr['field2'] = 100; | non-associative array $arr[] = 'some value'; $arr[] = 'other-value', ...
 * @param ENUM $mode							:: mode: 'insert' | 'insert-subselect' | 'update' | 'in-select', 'data-array'
 * @param RESOURCE $y_connection 				:: the connection to pgsql server
 * @return STRING								:: The SQL partial Statement
 *
 */
public static function prepare_statement($arrdata, $mode, $y_connection='DEFAULT') {

	// version: 170411

	//==
	$y_connection = self::check_connection($y_connection, 'PREPARE-STATEMENT');
	//==

	//--
	$mode = strtolower((string)$mode);
	//--
	switch((string)$mode) {
		//-- associative array
		case 'insert':
			$mode = 'insert';
			break;
		case 'insert-subselect':
			$mode = 'insert-subselect';
			break;
		case 'update':
			$mode = 'update';
			break;
		//-- non-associative array
		case 'in-select':
			$mode = 'in-select';
			break;
		case 'data-array':
			$mode = 'data-array';
			break;
		//-- invalid
		default:
			self::error($y_connection, 'PREPARE-STATEMENT', 'Invalid Mode', '', $mode);
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
			//-- except [ in-select | 'data-array' ], do not allow invalid keys as they represent the field names ; valid fields must contain only the following chars [A..Z][a..z][0..9][_]
			if(((string)$mode == 'in-select') OR ((string)$mode == 'data-array')) { // in-select, data-array
				$key = (int) $key; // force int keys
			} elseif(!self::validate_table_and_fields_names($key)) { // no unicode modifier
				self::error($y_connection, 'PREPARE-STATEMENT', 'Invalid KEY', '', $key);
				return '';
			} //end if
			//--
			$val_x = ''; // reset
			//--
			if(is_array($val)) { // array (this is a special case, and always escape data)
				//--
				$val_x = (string) self::escape_literal((string)Smart::array_to_list($val), '', $y_connection); // array values will be always escaped and converted to: <val1>, <val2>, ...
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
			} else { // string, number or other cases
				//--
				$val_x = (string) self::escape_literal($val, '', $y_connection);
				//--
			} //end if else
			//--
			if(((string)$mode == 'in-select') OR ((string)$mode == 'data-array')) { // in-select, data-array
				$tmp_query_w .= $val_x.',';
			} elseif((string)$mode == 'update') { // update
				$tmp_query_x .= self::escape_identifier($key, $y_connection).'='.$val_x.',';
			} else { // insert, insert-subselect
				$tmp_query_y .= self::escape_identifier($key, $y_connection).',';
				$tmp_query_z .= $val_x.',';
			} //end if else
			//--
		} //end while
		//--
	} else {
		//--
		self::error($y_connection, 'PREPARE-STATEMENT', 'The first argument must be array !', '', '');
		return '';
		//--
	} //end if else
	//--

	//-- eliminate last comma
	if(((string)$mode == 'in-select') OR ((string)$mode == 'data-array')) { // in-select, data-array
		$tmp_query_w = rtrim($tmp_query_w, ' ,');
	} elseif((string)$mode == 'update') { // update
		$tmp_query_x = rtrim($tmp_query_x, ' ,');
	} else { // insert, insert-subselect
		$tmp_query_y = rtrim($tmp_query_y, ' ,');
		$tmp_query_z = rtrim($tmp_query_z, ' ,');
	} //end if else
	//--

	//--
	if((string)$mode == 'in-select') { // in-select
		$tmp_query = ' IN ('.$tmp_query_w.') ';
	} elseif((string)$mode == 'data-array') { // data-array
		$tmp_query = ' ARRAY ['.$tmp_query_w.'] ';
	} elseif((string)$mode == 'update') { // update
		$tmp_query = ' SET '.$tmp_query_x.' ';
	} elseif((string)$mode == 'insert-subselect') { // (upsert) insert-subselect
		$tmp_query = ' ('.$tmp_query_y.') SELECT '.$tmp_query_z.' ';
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
 * Create Escaped SQL Statements from Parameters and Array of Data by replacing $# params
 * This can be used for a full SQL statement or just for a part.
 * The statement must not contain any Single Quotes to prevent SQL injections which are unpredictable if mixing several statements at once !
 *
 * @param STRING $query							:: SQL Statement to process like '   WHERE ("id" = $1)'
 * @param ARRAY $arrdata 						:: The non-associative array as of: $arr=array('a');
 * @param RESOURCE $y_connection 				:: the connection to pgsql server
 * @return STRING								:: The SQL processed (partial/full) Statement
 */
public static function prepare_param_query($query, $replacements_arr, $y_connection='DEFAULT') { // {{{SYNC-SQL-PARAM-QUERY}}}

	// version: 181219

	//==
	$y_connection = self::check_connection($y_connection, 'PREPARE-PARAM-QUERY');
	//==

	//--
	if(!is_string($query)) {
		self::error($y_connection, 'PREPARE-PARAM-QUERY', 'Query is not a string !', print_r($query,1), $replacements_arr);
		return ''; // query must be a string
	} //end if
	//--
	if((string)trim((string)$query) == '') {
		self::error($y_connection, 'PREPARE-PARAM-QUERY', 'Query is empty !', (string)$query, $replacements_arr);
		return ''; // empty query not allowed
	} //end if
	//--
	if(strpos($query, "'") !== false) { // this must be avoided as below will be exploded by ? thus if a ? is inside '' this is a problem ...
		self::error($y_connection, 'PREPARE-PARAM-QUERY', 'Query used for prepare with params in '.__FUNCTION__.'() cannot contain single quotes to prevent possible SQL injections which can produce unpredictable results !', (string)$query, $replacements_arr);
		return ''; // single quote is not allowed
	} //end if
	//--
	if(!is_array($replacements_arr)) {
		self::error($y_connection, 'PREPARE-PARAM-QUERY', 'Query Replacements is NOT Array !', (string)$query, $replacements_arr);
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
			if($i < ($expr_count - 1)) { // this is req. as it comes from explode
				//--
				if(!array_key_exists((string)$i, $replacements_arr)) {
					self::error($y_connection, 'PREPARE-PARAM-QUERY', 'Invalid Replacements Array.Size ; Key: #'.$i, (string)$query, $replacements_arr);
					return ''; // array key does not exists in replacements
					break;
				} //end if
				//-- {{{SYNC-DETECT-PURE-NUMERIC-INT-OR-DECIMAL-VALUES}}} :: for PostgreSQL IS IMPOSSIBLE TO KNOW OUT OF CONTEXT TO PASS A PURE NUMERIC OR A STRING VALUE BECAUSE OF ERRORS ; THUS IS SAFE TO USE ESCAPE LITERAL WHICH ALWAYS ADDS QUOTES ARROUND VALUES (INCL. NUMERIC) ; ERROR EXAMPLE: DO A QUERY WHERE A VALUE = NUMERIC WHERE COLUMN IS TEXT
				$out_query .= (string) self::escape_literal((string)$replacements_arr[$i], '', $y_connection);
				//--
			} //end if
			//--
		} //end for
		//--
	} elseif(strpos((string)$query, '$') !== false) {
		//--
		$expr_arr = array();
		preg_match_all('{'.'([^\$]*)?(\$[0-9]+)?'.'}s', (string)$query, $expr_arr, PREG_SET_ORDER, 0);
		//print_r($expr_arr); die();
		$expr_count = Smart::array_size($expr_arr);
		//--
		for($i=0; $i<$expr_count; $i++) {
			//--
			$out_query .= (string) $expr_arr[$i][1];
			//--
			$crr_key = (int) substr((string)trim((string)$expr_arr[$i][2]), 1);
			$crr_key -= 1; // fix: $1 is for $arr[0]
			//--
			if((int)$crr_key >= 0) {
				//--
				if(!array_key_exists((string)$crr_key, $replacements_arr)) {
					self::error($y_connection, 'PREPARE-PARAM-QUERY', 'Invalid Replacements Array.Size ; Key: #'.$i.' / $'.($crr_key+1), (string)$query, $replacements_arr);
					return ''; // array key does not exists in replacements
					break;
				} //end if
				//-- {{{SYNC-DETECT-PURE-NUMERIC-INT-OR-DECIMAL-VALUES}}} :: for PostgreSQL IS IMPOSSIBLE TO KNOW OUT OF CONTEXT TO PASS A PURE NUMERIC OR A STRING VALUE BECAUSE OF ERRORS ; THUS IS SAFE TO USE ESCAPE LITERAL WHICH ALWAYS ADDS QUOTES ARROUND VALUES (INCL. NUMERIC) ; ERROR EXAMPLE: DO A QUERY WHERE A VALUE = NUMERIC WHERE COLUMN IS TEXT
				$out_query .= (string) self::escape_literal((string)$replacements_arr[$crr_key], '', $y_connection);
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
	//echo $out_query; die();
	//--

	//--
	return (string) $out_query;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Get A UNIQUE (SAFE) ID for DB Tables / Schema
 *
 * @param ENUM 		$y_mode 					:: mode: uid10str | uid10num | uid10seq | uid36 | uid45
 * @param STRING 	$y_field_name 				:: the field name
 * @param STRING 	$y_table_name 				:: the table name
 * @param STRING 	$y_schema 					:: the schema
 * @param RESOURCE 	$y_connection 				:: pgsql connection
 * @return STRING 								:: the generated Unique ID
 *
 */
public static function new_safe_id($y_mode, $y_id_field, $y_table_name, $y_schema='public', $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'NEW-SAFE-ID');
	//==

	//--
	if(!self::validate_table_and_fields_names($y_id_field)) {
		self::error($y_connection, 'NEW-SAFE-ID', 'Get New Safe ID', 'Invalid Field Name', $y_id_field.' / [Schema='.$y_schema.';Table='.$y_table_name.']');
		return '';
	} //end if
	if(!self::validate_table_and_fields_names($y_table_name)) {
		self::error($y_connection, 'NEW-SAFE-ID', 'Get New Safe ID', 'Invalid Table Name', $y_table_name);
		return '';
	} //end if
	if(!self::validate_table_and_fields_names($y_schema)) {
		self::error($y_connection, 'NEW-SAFE-ID', 'Get New Safe ID', 'Invalid Schema Name', $y_schema);
		return '';
	} //end if
	//--

	//--
	$use_safe_id_record = true;
	if(defined('SMART_SOFTWARE_DB_DISABLE_SAFE_IDS')) {
		if(SMART_SOFTWARE_DB_DISABLE_SAFE_IDS === true) {
			$use_safe_id_record = false;
		} //end if
	} //end if
	//--
	if($use_safe_id_record === true) {
		//--
		if(self::check_if_table_exists('_safe_id_records', 'smart_runtime', $y_connection) !== 1) {
			if(self::check_if_schema_exists('smart_runtime', $y_connection) !== 1) {
				self::write_data('CREATE SCHEMA "smart_runtime"', 'Initialize SafeID Schema', $y_connection);
			} //end if
			self::write_data((string)self::schema_safe_id_records_table(), 'Initialize SafeID Table', $y_connection);
		} //end if
		//--
		if((int)Smart::random_number(0,99) == 1) { // 1% chance to run it for cleanup records older than 24 hours
			self::write_data('DELETE FROM "smart_runtime"."_safe_id_records" WHERE ("date_time" < \''.self::escape_str(date('Y-m-d H:i:s', @strtotime('-1 day')), '', $y_connection).'\')', 'Safe ID Records Cleanup (OLDs)', $y_connection); // cleanup olds
		} //end if
		//--
	} //end if
	//--
	$tmp_result = 'NO-ID-INIT'; //init (must be not empty)
	$counter = 0;
	$id_is_ok = false;
	//--
	while($id_is_ok !== true) { // while we cannot find an unused ID
		//--
		$counter += 1;
		//--
		if($counter > 7500) { // loop to max 7500
			self::error($y_connection, 'NEW-SAFE-ID', 'Get New Safe ID', 'Could Not Assign a Unique ID', '(timeout / 7500) ... try again !');
			return '';
		} //end if
		//--
		if(($counter % 500) == 0) {
			sleep(1);
		} //end if
		//--
		$new_id = 'NO-ID-ALGO';
		switch((string)$y_mode) {
			case 'uid45':
				$new_id = (string) Smart::uuid_45(SMART_FRAMEWORK_NETSERVER_ID.SmartUtils::get_server_current_url()); // will use the server ID.Host as Prefix to ensure it is true unique in a cluster
				break;
			case 'uid36':
				$new_id = (string) Smart::uuid_36(SMART_FRAMEWORK_NETSERVER_ID.SmartUtils::get_server_current_url()); // will use the server ID.Host as Prefix to ensure it is true unique in a cluster
				break;
			case 'uid10seq':
				if($use_safe_id_record === true) { // sequences are not safe without a second registry allocation table as the chance to generate the same ID in the same time moment is just 1 in 999
					$new_id = (string) Smart::uuid_10_seq();
				} else {
					$new_id = (string) Smart::uuid_10_str();
				} //end if else
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
		$chk_uniqueness = 'SELECT '.self::escape_identifier($y_id_field, $y_connection).' FROM '.self::escape_identifier($y_schema, $y_connection).'.'.self::escape_identifier($y_table_name, $y_connection).' WHERE ('.self::escape_identifier($y_id_field, $y_connection).' = '.self::escape_literal($new_id, '', $y_connection).') LIMIT 1 OFFSET 0';
		$result_arr = self::read_data($chk_uniqueness, 'Safe Check if NEW ID Exists into Table', $y_connection);
		$tmp_result = (string) trim((string)$result_arr[0]);
		$result_arr = array();
		//--
		if((string)$tmp_result == '') {
			//--
			if($use_safe_id_record === true) { // with safety check against safe ID records table
				//-- reserve this ID to bse sure will not be assigned to another instance
				$uniqueness_mark = (string) $y_schema.'.'.$y_table_name.':'.$y_id_field;
				$write_res = self::write_igdata(
					'INSERT INTO "smart_runtime"."_safe_id_records" ("id", "table_space", "date_time") ( SELECT \''.self::escape_str($new_id, '', $y_connection).'\', \''.self::escape_str($uniqueness_mark, '', $y_connection).'\', \''.self::escape_str(date('Y-m-d H:i:s'), '', $y_connection).'\' WHERE (NOT EXISTS ( SELECT 1 FROM "smart_runtime"."_safe_id_records" WHERE (("id" = \''.self::escape_str($new_id, '', $y_connection).'\') AND ("table_space" = \''.self::escape_str($uniqueness_mark, '', $y_connection).'\')) LIMIT 1 OFFSET 0 ) AND NOT EXISTS ('.$chk_uniqueness.') ) )',
					'Safe Record of NEW ID of Table into Zone Control',
					$y_connection
				);
				//--
				if($write_res[1] === 1) {
					$id_is_ok = true;
				} //end if
				//--
			} else { // default (not safe in very high load environments ...
				//--
				$id_is_ok = true;
				//--
			} //end if else
			//--
		} //end if
		//--
	} //end while
	//--

	//--
	return (string) $new_id;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * List All Tables in the current Database.
 *
 * @param STRING $y_schema 						:: PgSQL Schema Name (public | *other)
 * @param RESOURCE $y_connection				:: The connection to PgSQL server
 * @return ARRAY
 *
 * @access 		private
 * @internal
 *
 */
public static function list_db_tables($y_schema, $y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'LIST-DB-TABLES');
	//==

	//--
	$arr_data = self::read_data('SELECT "schemaname", "tablename" FROM "pg_tables" WHERE ("schemaname" = \''.self::escape_str($y_schema, '', $y_connection).'\') ORDER BY "tablename" ASC', 'List DB Tables', $y_connection);
	//--

	//--
	return $arr_data;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Check and Return the PostgreSQL Server Version
 *
 * @access 		private
 * @internal
 *
 */
public static function check_server_version($y_connection='DEFAULT', $y_revalidate=false) {

	//==
	$y_connection = self::check_connection($y_connection, 'CHECK-SERVER-VERSION');
	//==

	//--
	if($y_revalidate !== true) {
		if((string)self::$server_version[(string)$y_connection] != '') {
			return (string) self::$server_version[(string)$y_connection];
		} //end if
	} //end if
	//--

	//--
	$minimum_pgsql_version_for_smartframework = '9.0.x'; // PostgreSQL minimum version required [9.0.x] or later (DO NOT RUN THIS SOFTWARE ON OLDER PostgreSQL Versions !!!
	//--

	//--
	$queryval = 'SHOW SERVER_VERSION';
	$result = @pg_query($y_connection, $queryval);
	//--
	$error = '';
	if(!$result) {
		$error = 'CHECK PgSQL Version FAILED:'."\n".@pg_last_error($y_connection);
	} //end if else
	//--
	if((string)$error != '') {
		//--
		self::error($y_connection, 'CHECK-SERVER-VERSION', $error, $queryval, '');
		return '';
		//--
	} else {
		//--
		$record = @pg_fetch_row($result);
		//--
	} //end if else
	//--
	@pg_free_result($result);
	//--

	//--
	$pgsql_version = '0.0';
	if(is_array($record)) {
		$pgsql_version = (string) trim((string)$record[0]);
	} //end if
	$pgsql_txt_version = (string) strtoupper('PostgreSQL');
	$pgsql_num_version = (string) strtolower((string)$pgsql_version);
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
			'type' => 'metainfo',
			'data' => 'PostgreSQL Server Version: '.$pgsql_version,
			'connection' => (string) $y_connection,
			'skip-count' => 'yes'
		]);
	} //end if
	//--

	//--
	if(((string)$pgsql_txt_version != 'POSTGRESQL') OR (version_compare(self::major_version($minimum_pgsql_version_for_smartframework), self::major_version($pgsql_num_version)) > 0)) {
		self::error($y_connection, 'Server-Version', 'PgSQL Server Version not supported', $pgsql_txt_version.' '.$pgsql_num_version, 'PgSQL.version='.self::major_version($minimum_pgsql_version_for_smartframework).' or later is required to run this software !');
		return '';
	} //end if
	//--

	//--
	self::$server_version[(string)$y_connection] = (string) $pgsql_num_version;
	//--

	//--
	return (string) $pgsql_num_version;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * List All Runtime Info from the current PostgreSQL server.
 *
 * @param RESOURCE $y_connection				:: The connection to PgSQL server
 * @return ARRAY
 *
 * @access 		private
 * @internal
 *
 */
public static function runtime_info($y_connection='DEFAULT') {

	//==
	$y_connection = self::check_connection($y_connection, 'RUNTIME-INFO');
	//==

	//--
	$arr_data = self::read_data('SHOW ALL', 'Show Runtime Info', $y_connection);
	//--

	//--
	return $arr_data;
	//--

} //END FUNCTION
//======================================================


//======================================================================
// # PRIVATES
//======================================================================


//======================================================
private static function get_notice_smart_affected_rows($y_pgsql_notice) {
	//--
	$y_pgsql_notice = (string) trim((string)$y_pgsql_notice);
	$arr = explode('SMART-FRAMEWORK-PGSQL-NOTICE: AFFECTED ROWS #', (string)$y_pgsql_notice);
	$msg = trim((string)$arr[1]);
	//--
	return (int) $msg;
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Check the connection to PgSQL if Active and Not Busy
 * If not connected will connect
 * PRIVATE
 *
 * @param RESOURCE 	$y_connection 				:: The Connection to PgSQL Server
 * @param STRING 	$y_description				:: The Description of Where it is Checked (for having a clue where it fails)
 * @return Connection Resource
 *
 */
private static function check_connection($y_connection, $y_description) {
	//--
	global $configs;
	//--
	if($y_connection === 'DEFAULT') { // just for the default connection !!!
		//--
		if(!defined('SMART_FRAMEWORK_DB_LINK_PostgreSQL')) { // PostgreSQL default connection is exported as constant to avoid re-connection which can break transactions
			//--
			if(!is_array($configs['pgsql'])) {
				self::error('', 'CHECK-DEFAULT-PGSQL-CONFIGS', 'The Default PostgreSQL Configs not detected !', 'The configs[pgsql] is not an array !', $y_description);
				return null;
			} //end if
			if(((string)$configs['pgsql']['server-host'] == '') OR ((string)$configs['pgsql']['server-port'] == '') OR ((string)$configs['pgsql']['dbname'] == '') OR ((string)$configs['pgsql']['username'] == '')) {
				self::error('', 'CHECK-DEFAULT-PGSQL-CONFIGS', 'The Default PostgreSQL Configs are not complete !', 'Some of the configs[pgsql] parameters are missing !', $y_description);
				return null;
			} //end if
			//-- {{{SYNC-CONNECTIONS-IDS}}}
			$the_conn_key = (string) $configs['pgsql']['server-host'].':'.$configs['pgsql']['server-port'].'@'.$configs['pgsql']['dbname'].'#'.$configs['pgsql']['username'];
			if(array_key_exists((string)$the_conn_key, (array)SmartFrameworkRegistry::$Connections['pgsql'])) { // if the connection was made before using the SmartPgsqlExtDb
				//--
				$y_connection = SmartFrameworkRegistry::$Connections['pgsql'][(string)$the_conn_key];
				//--
				define('SMART_FRAMEWORK_DB_LINK_PostgreSQL', $y_connection);
				//--
				if(SmartFrameworkRuntime::ifDebug()) {
					SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
						'type' => 'open-close',
						'data' => 'Re-Using Connection to PgSQL Server as DEFAULT: '.$the_conn_key,
						'connection' => (string) $y_connection
					]);
				} //end if
				//--
			} else {
				//--
				$y_connection = self::server_connect( // create a DEFAULT connection using default postgresql connection params from config
					(string)$configs['pgsql']['server-host'],
					(int)$configs['pgsql']['server-port'],
					(string)$configs['pgsql']['dbname'],
					(string)$configs['pgsql']['username'],
					(string)$configs['pgsql']['password'],
					(int)$configs['pgsql']['timeout'],
					(string)$configs['pgsql']['transact'],
					(float)$configs['pgsql']['slowtime'],
					(string)$configs['pgsql']['type']
				);
				//--
				define('SMART_FRAMEWORK_DB_LINK_PostgreSQL', $y_connection);
				//--
				if(is_resource($y_connection)) {
					//--
					define('SMART_FRAMEWORK_DB_VERSION_PostgreSQL', self::check_server_version($y_connection, true)); // re-validate
					//--
				} //end if
				//--
			} //end if else
			//--
		} else {
			//-- re-use the default connection
			$y_connection = SMART_FRAMEWORK_DB_LINK_PostgreSQL;
			//--
		} //end if
		//--
	} //end if
	//--
	if(!is_resource($y_connection)) { // if no connection
		//--
		self::error($y_connection, 'CHECK-CONNECTION', 'Connection is BROKEN !', 'Connection-ID: '.$y_connection, $y_description);
		return null;
		//--
	} //end if
	//--
	if(@pg_connection_status($y_connection) != PGSQL_CONNECTION_OK) {
		//--
		$re_connect = @pg_ping($y_connection);
		//--
		if(!$re_connect) {
			self::error($y_connection, 'CHECK-CONNECTION', 'Connection LOST !', 'Connection-ID: '.$y_connection, $y_description);
			return null;
		} //end if
		//--
	} //end if else
	//--
	return $y_connection;
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
// returns major version for pgsql versions
private static function major_version($y_version) {
	//--
	$y_version = (string) $y_version;
	//--
	$arr = (array) explode('.', trim($y_version));
	//--
	return trim($arr[0]).'.'.trim($arr[1]).'.x';
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Displays the PgSQL Errors and HALT EXECUTION (This have to be a FATAL ERROR as it occur when a FATAL PgSQL ERROR happens or when a Query Syntax is malformed)
 * PRIVATE
 *
 * @return 										:: HALT EXECUTION WITH ERROR MESSAGE
 *
 */
private static function error($y_connection, $y_area, $y_error_message, $y_query, $y_params_or_title, $y_warning='') {
//--
if(defined('SMART_SOFTWARE_SQLDB_FATAL_ERR') AND (SMART_SOFTWARE_SQLDB_FATAL_ERR === false)) {
	throw new Exception('#POSTGRESQL-DB@'.$y_connection.'# :: Q# // PgSQL Client :: EXCEPTION :: '.$y_area."\n".$y_error_message);
	return;
} //end if
//--
$def_warn = 'Execution Halted !';
$y_warning = (string) trim((string)$y_warning);
if(SmartFrameworkRuntime::ifDebug()) {
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
	'PgSQL Client',
	'PostgreSQL',
	'SQL/DB',
	'Server',
	'lib/core/img/db/postgresql-logo.svg',
	$width, // width
	$the_area, // area
	$the_error_message, // err msg
	$the_params, // title or params
	$the_query_info // sql statement
);
//--
Smart::raise_error(
	'#POSTGRESQL-DB@'.$y_connection.' :: Q# // PgSQL Client :: ERROR :: '.$y_area."\n".'*** Error-Message: '.$y_error_message."\n".'*** Params / Title:'."\n".print_r($y_params_or_title,1)."\n".'*** Query:'."\n".$y_query,
	$out // msg to display
);
die(''); // just in case
//--
} //END FUNCTION
//======================================================


//======================================================
private static function schema_safe_id_records_table() {
//--
$sql = <<<'SQL'
-- Table smart_runtime._safe_id_records #####
CREATE TABLE smart_runtime._safe_id_records (
    id character varying(45) NOT NULL,
    table_space character varying(512) NOT NULL,
    date_time character varying(22) NOT NULL,
    CONSTRAINT _safe_id_records__check__id CHECK ((char_length((id)::text) >= 10)),
    CONSTRAINT _safe_id_records__check__table_space CHECK ((char_length((table_space)::text) >= 1)),
    CONSTRAINT _safe_id_records__check__date_time CHECK ((char_length((date_time)::text) >= 19))
);
ALTER TABLE ONLY smart_runtime._safe_id_records ADD CONSTRAINT _safe_id_records__id PRIMARY KEY (id);
CREATE INDEX _safe_id_records__table_space 	ON smart_runtime._safe_id_records USING btree (table_space);
CREATE INDEX _safe_id_records__date_time 	ON smart_runtime._safe_id_records USING btree (date_time);
COMMENT ON TABLE smart_runtime._safe_id_records IS 'Smart.Framework Safe-ID Records v.2015.03.25';
COMMENT ON COLUMN smart_runtime._safe_id_records.id IS 'ID';
COMMENT ON COLUMN smart_runtime._safe_id_records.table_space IS 'Table Space as: schema.table:field';
COMMENT ON COLUMN smart_runtime._safe_id_records.date_time IS 'Date and Time ( yyyy-mm-dd hh:ii:ss)';
SQL;
//--
return (string) $sql;
//--
} //END FUNCTION
//======================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartPgsqlExtDb - provides a Dynamic (Extended) PostgreSQL DB Server Client that can be used with custom made connections.
 *
 * This class is made to be used with custom made PostgreSQL connections (other servers than default).
 *
 * <code>
 * // Sample config array for this class constructor:
 * $custom_pgsql = array();
 * $custom_pgsql['type']         = 'postgresql';            // postgresql / pgpool2
 * $custom_pgsql['server-host']  = '127.0.0.1';             // database host (default is 127.0.0.1)
 * $custom_pgsql['server-port']  = '5432';                  // database port (default is 5432)
 * $custom_pgsql['dbname']       = 'smart_framework';       // database name
 * $custom_pgsql['username']     = 'pgsql';                 // sql server user name
 * $custom_pgsql['password']     = base64_encode('pgsql');  // sql server Base64-Encoded password for that user name B64
 * $custom_pgsql['timeout']      = 30;                      // connection timeout (how many seconds to wait for a valid PgSQL Connection)
 * $custom_pgsql['slowtime']     = 0.0050;                  // 0.0025 .. 0.0090 slow query time (for debugging)
 * $custom_pgsql['transact']     = 'READ COMMITTED';        // Default Transaction Level: 'READ COMMITTED' | 'REPEATABLE READ' | 'SERIALIZABLE' | '' to leave it as default
 * // sample usage:
 * $pgsql = new SmartPgsqlExtDb($custom_pgsql);
 * $pgsql->read_adata('SELECT * FROM "my_table" LIMIT 100 OFFSET 0');
 * //... for other hints look to the samples of the class: SmartPgsqlDb::*
 * </code>
 *
 * @usage 		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		This class have no catcheable exception because the ONLY errors will raise are when the server returns an ERROR regarding a malformed SQL Statement, which is not acceptable to be just exception, so will raise a fatal error !
 *
 * @depends 	extensions: PHP PostgreSQL ; classes: Smart, SmartUnicode, SmartUtils
 * @version 	v.181219
 * @package 	Database:PostgreSQL
 *
 */
final class SmartPgsqlExtDb {

	// ->


private $connection;


//==================================================


/**
 * Class Constructor
 * It will initiate also the custom connection
 * or will re-use an existing connection (if the same connection parameters are provided)
 * for a PostgreSQL Server.
 *
 * @param ARRAY $y_configs_arr 					:: The Array of Configuration parameters for the connection - the ARRAY STRUCTURE should be identical with the default config.php: $configs['pgsql'].
 *
 */
public function __construct($y_configs_arr) {
	//--
	$y_configs_arr = (array) $y_configs_arr;
	//-- {{{SYNC-CONNECTIONS-IDS}}}
	$the_conn_key = (string) $y_configs_arr['server-host'].':'.$y_configs_arr['server-port'].'@'.$y_configs_arr['dbname'].'#'.$y_configs_arr['username'];
	if(array_key_exists((string)$the_conn_key, (array)SmartFrameworkRegistry::$Connections['pgsql'])) {
		//-- try to reuse the connection :: only check if array key exists, not if it is a valid resource ; this should be as so to avoid mismatching connection mixings (if by example will re-use the connection of another server, and connection is broken in the middle of a transaction, it will fail ugly ;) and out of any control !
		$this->connection = SmartFrameworkRegistry::$Connections['pgsql'][(string)$the_conn_key];
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('db', 'pgsql|log', [
				'type' => 'open-close',
				'data' => 'Re-Using Connection to PgSQL Server: '.$the_conn_key,
				'connection' => (string)$this->connection
			]);
		} //end if
		//--
	} else {
		//-- connect
		$this->connection = SmartPgsqlDb::server_connect(
			(string)$y_configs_arr['server-host'],
			(int)$y_configs_arr['server-port'],
			(string)$y_configs_arr['dbname'],
			(string)$y_configs_arr['username'],
			(string)$y_configs_arr['password'],
			(int)$y_configs_arr['timeout'],
			(string)$y_configs_arr['transact'],
			(float)$y_configs_arr['slowtime'],
			(string)$y_configs_arr['type']
		);
		//--
		$this->check_server_version(true); // re-validate
		//--
	} //end if else
	//--
} //END FUNCTION


//==================================================


/**
 * Returns the connection resource of the current PostgreSQL Server.
 */
public function getConnection() {
	//--
	return $this->connection;
	//--
} //END FUNCTION


//==================================================


/**
 * Fix a string to be compliant with PgSQL LIKE / ILIKE / SIMILAR syntax.
 * It will use special quotes for the LIKE / ILIKE / SIMILAR special characters: % _
 * This function IS NOT INTENDED TO ESCAPE AGAINST SQL INJECTIONS ; USE IT ONLY WITH PREPARED PARAMS OR USE escape_str() with mode 'likes' / escape_literal() with mode 'likes'
 *
 * @param STRING $y_string						:: A String or a Number to be Quoted for LIKES
 */
public function quote_likes($y_string) {
	//--
	return (string) SmartPgsqlDb::quote_likes($y_string);
	//--
} //END FUNCTION


/**
 * Fix a string to be compliant with PgSQL REGEX syntax.
 * It will use special quotes for the REGEX special characters: . \ + * ? [ ^ ] $ ( ) { } = ! < > | : -
 * This function IS NOT INTENDED TO ESCAPE AGAINST SQL INJECTIONS ; USE IT ONLY WITH PREPARED PARAMS OR USE escape_str() with mode 'regex' / escape_literal() with mode 'regex'
 *
 * @param STRING $y_string						:: A String or a Number to be Quoted for REGEX
 */
public function quote_regex($y_string) {
	//--
	return (string) SmartPgsqlDb::quote_regex($y_string);
	//--
} //END FUNCTION


/**
 * Escape a string to be compliant and Safe (against SQL Injection) with PgSQL standards.
 * This function will not add the (single) quotes arround the string, but just will just escape it to be safe.
 *
 * @param STRING $y_string						:: A String or a Number to be Escaped
 * @param ENUM $y_mode							:: '' = default ; 'likes' = Escape LIKE / ILIKE / SIMILAR Syntax (% _) ; :: '' = default ; 'regex' = Escape ~ ~* !~ !~* Syntax
 * @return STRING 								:: The Escaped String / Number
 *
 */
public function escape_str($y_string, $y_mode='') {
	//--
	return SmartPgsqlDb::escape_str($y_string, $y_mode, $this->connection);
	//--
} //END FUNCTION


/**
 * Escape a variable in the literal way to be compliant and Safe (against SQL Injection) with PgSQL standards.
 * This function will add the (single) quotes arround the string as needed and will escape expressions containing backslashes \ in the postgresql way using E'' escapes.
 * This is the preferred way to escape variables inside PostgreSQL SQL Statements, and is better than escape_str().
 *
 * @param STRING $y_string						:: A String or a Number to be Escaped
 * @param ENUM $y_mode							:: '' = default ; 'likes' = Escape LIKE / ILIKE / SIMILAR Syntax (% _) ; :: '' = default ; 'regex' = Escape ~ ~* !~ !~* Syntax
 * @return STRING 								:: The Escaped String / Number
 *
 */
public function escape_literal($y_string, $y_mode='') {
	//--
	return SmartPgsqlDb::escape_literal($y_string, $y_mode, $this->connection);
	//--
} //END FUNCTION


/**
 * Escape an identifier to be compliant and Safe (against SQL Injection) with PgSQL standards.
 * This function will add the (double) quotes arround the identifiers (fields / table names) as needed.
 *
 * @param STRING $y_identifier					:: The Identifier to be Escaped: field / table
 * @return STRING 								:: The Escaped Identifier as: "field" / "table"
 *
 */
public function escape_identifier($y_identifier) {
	//--
	return SmartPgsqlDb::escape_identifier($y_identifier, $this->connection);
	//--
} //END FUNCTION


/**
 * PostgreSQL compliant and Safe Json Encode.
 * This should be used with PostgreSQL json / jsonb fields.
 *
 * @param STRING $y_mixed_content				:: A mixed variable
 * @return STRING 								:: JSON string
 *
 */
public function json_encode($y_mixed_content) {
	//--
	return SmartPgsqlDb::json_encode($y_mixed_content);
	//--
} //END FUNCTION


/**
 * Check if a Schema Exists in the current Database.
 *
 * @param STRING $y_schema 						:: The Schema Name
 * @return 0/1									:: 1 if exists ; 0 if not
 *
 */
public function check_if_schema_exists($y_schema) {
	//--
	return SmartPgsqlDb::check_if_schema_exists($y_schema, $this->connection);
	//--
} //END FUNCTION


/**
 * Check if a Table Exists in the current Database.
 *
 * @param STRING $y_table 						:: The Table Name
 * @param STRING $y_schema						:: The Schema Name
 * @return 0/1									:: 1 if exists ; 0 if not
 *
 */
public function check_if_table_exists($y_table, $y_schema='public') {
	//--
	return SmartPgsqlDb::check_if_table_exists($y_table, $y_schema, $this->connection);
	//--
} //END FUNCTION


/**
 * PgSQL Query -> Count
 * This function is intended to be used for count type queries: SELECT COUNT().
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @return INTEGER								:: the result of COUNT()
 */
public function count_data($queryval, $params_or_title='') {
	//--
	return SmartPgsqlDb::count_data($queryval, $params_or_title, $this->connection);
	//--
} //END FUNCTION


/**
 * PgSQL Query -> Read (Non-Associative) one or multiple rows.
 * This function is intended to be used for read type queries: SELECT.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @return ARRAY (non-asociative) of results	:: array('column-0-0', 'column-0-1', ..., 'column-0-n', 'column-1-0', 'column-1-1', ... 'column-1-n', ..., 'column-m-0', 'column-m-1', ..., 'column-m-n')
 */
public function read_data($queryval, $params_or_title='') {
	//--
	return SmartPgsqlDb::read_data($queryval, $params_or_title, $this->connection);
	//--
} //END FUNCTION


/**
 * PgSQL Query -> Read (Associative) one or multiple rows.
 * This function is intended to be used for read type queries: SELECT.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @return ARRAY (asociative) of results		:: array(0 => array('column1', 'column2', ... 'column-n'), 1 => array('column1', 'column2', ... 'column-n'), ..., m => array('column1', 'column2', ... 'column-n'))
 */
public function read_adata($queryval, $params_or_title='') {
	//--
	return SmartPgsqlDb::read_adata($queryval, $params_or_title, $this->connection);
	//--
} //END FUNCTION


/**
 * PgSQL Query -> Read (Associative) - Single Row (just for 1 row, to easy the use of data from queries).
 * !!! This will raise an error if more than one row(s) are returned !!!
 * This function does not support multiple rows because the associative data is structured without row iterator.
 * For queries that return more than one row use: read_adata() or read_data().
 * This function is intended to be used for read type queries: SELECT.
 *
 * @hints	ALWAYS use a LIMIT 1 OFFSET 0 with all queries using this function to avoid situations that will return more than 1 rows and will raise ERROR with this function.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @return ARRAY (asociative) of results		:: Returns just a SINGLE ROW as: array('column1', 'column2', ... 'column-n')
 */
public function read_asdata($queryval, $params_or_title='') {
	//--
	return SmartPgsqlDb::read_asdata($queryval, $params_or_title, $this->connection);
	//--
} //END FUNCTION


/**
 * PgSQL Query -> Write.
 * This function is intended to be used for write type queries: BEGIN (TRANSACTION) ; COMMIT ; ROLLBACK ; INSERT ; UPDATE ; CREATE SCHEMAS ; CALLING STORED PROCEDURES ...
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @return ARRAY 								:: [0 => 'control-message', 1 => #affected-rows]
 */
public function write_data($queryval, $params_or_title='') {
	//--
	return SmartPgsqlDb::write_data($queryval, $params_or_title, $this->connection);
	//--
} //END FUNCTION


/**
 * PgSQL Query :: Write Ignore - Catch Duplicate Key Violation or Foreign Key Violation Errors (This is the equivalent of MySQL's INSERT IGNORE / UPDATE IGNORE / DELETE IGNORE, but it can catch UNIQUE violations on both: INSERT / UPDATE / DELETE statements and also can catch FOREIGN KEY violations).
 * This function is intended to be used only for write type queries like: INSERT / UPDATE / DELETE which can be ignored if unique violations or foreign key violations and will return the # of affected rows or zero if an exception raised.
 * The catch of PostgreSQL exceptions is handled completely by this function so there is no need for a catch errors outside.
 *
 * IMPORTANT:
 * This function needs the pgsql notice message tracking enabled in PHP (not ignored); This must be set in php.ini (pgsql.ignore_notice = 0).
 * The internal mechanism of this function to catch UNIQUE or FOREIGN KEYS violations is that the EXCEPTIONS are catch at the PostgreSQL level in a DO block.
 * This is the best approach to handle safe UPSERT or INSERT IGNORE / UPDATE IGNORE / DELETE IGNORE like queries in high load envionments or to avoid fatal errors when a INSERT / UPDATE / DELETE violates a unique key or a foreign key with PostgreSQL.
 * This function can be used inside transactions blocks but never use this function to execute statements as: BEGIN, START TRANSACTION, COMMIT, ROLLBACK or SET statements, as the context is incompatible.
 * HINTS:
 * On PostgreSQL 9.5/later there is an alternative which can be used directly with write_data() without the need of this function as the following statement: INSERT ... ON CONFLICT DO NOTHING/UPDATE ... (as the equivalent of INSERT IGNORE / UPSERT), but the following statements are still missing (not implemented): UPDATE ... ON CONFLICT DO NOTHING / DELETE ... ON CONFLICT DO NOTHING .
 * This function will remain in the future to offer backward compatibility with PostgreSQL 8.4 ... 9.5 even if PostgreSQL at some moment will have ON CONFLICT DO implemented for all 3 INSERT / UPDATE / DELETE.
 *
 * @param STRING $queryval						:: the query
 * @param STRING $params_or_title 				:: *optional* array of parameters ($1, $2, ... $n) or query title for easy debugging
 * @return ARRAY 								:: [0 => 'control-message', 1 => #affected-rows]
 */
public function write_igdata($queryval, $params_or_title='') {
	//--
	return SmartPgsqlDb::write_igdata($queryval, $params_or_title, $this->connection);
	//--
} //END FUNCTION


/**
 * Create Escaped Write SQL Statements from Data - to be used with PgSQL for: INSERT ; INSERT-SUBSELECT ; UPDATE ; IN-SELECT ; DATA-ARRAY
 * Can be used with: write_data() or write_igdata() to build INSERT / INSERT (SELECT) / UPDATE queries from an associative array
 * or can be used with read_data(), read_adata(), read_asdata(), count_data() to build IN-SELECT / DATA-ARRAY queries from a non-associative array
 *
 * @param ARRAY-associative $arrdata			:: associative array: array of form data as $arr=array(); $arr['field1'] = 'a string'; $arr['field2'] = 100; | non-associative array $arr[] = 'some value'; $arr[] = 'other-value', ...
 * @param ENUM $mode							:: mode: 'insert' | 'insert-subselect' | 'update' | 'in-select', 'data-array'
 * @return STRING								:: The SQL partial Statement
 *
 */
public function prepare_statement($arrdata, $mode) {
	//--
	return SmartPgsqlDb::prepare_statement($arrdata, $mode, $this->connection);
	//--
} //END FUNCTION


/**
 * Create Escaped SQL Statements from Parameters and Array of Data by replacing $# params
 * This can be used for a full SQL statement or just for a part.
 * The statement must not contain any Single Quotes to prevent SQL injections which are unpredictable if mixing several statements at once !
 *
 * @param STRING $query							:: SQL Statement to process like '   WHERE ("id" = $1)'
 * @param ARRAY $arrdata 						:: The non-associative array as of: $arr=array('a');
 * @return STRING								:: The SQL processed (partial/full) Statement
 */
public function prepare_param_query($query, $arrdata) {
	//--
	return SmartPgsqlDb::prepare_param_query($query, $arrdata, $this->connection);
	//--
} //END FUNCTION


/**
 * Get A UNIQUE (SAFE) ID for DB Tables / Schema
 *
 * @param ENUM 		$y_mode 					:: mode: uid10str | uid10num | uid10seq | uid36 | uid45
 * @param STRING 	$y_field_name 				:: the field name
 * @param STRING 	$y_table_name 				:: the table name
 * @param STRING 	$y_schema 					:: the schema (default is: public)
 * @return STRING 								:: the generated Unique ID
 *
 */
public function new_safe_id($y_mode, $y_id_field, $y_table_name, $y_schema='public') {
	//--
	return SmartPgsqlDb::new_safe_id($y_mode, $y_id_field, $y_table_name, $y_schema, $this->connection);
	//--
} //END FUNCTION


/**
 * List All Tables in the current Database.
 *
 * @param STRING $y_schema 						:: PgSQL Schema Name (public | *other)
 * @return ARRAY
 *
 * @access 		private
 * @internal
 *
 */
public function list_db_tables($y_schema) {
	//--
	return SmartPgsqlDb::list_db_tables($y_schema, $this->connection);
	//--
} //END FUNCTION


/**
 * Check and Return the PostgreSQL Server Version
 *
 * @access 		private
 * @internal
 *
 */
public function check_server_version($y_revalidate=false) {
	//--
	return SmartPgsqlDb::check_server_version($this->connection, $y_revalidate);
	//--
} //END FUNCTION


/**
 * List All Runtime Info from the current PostgreSQL server.
 *
 * @return ARRAY
 *
 * @access 		private
 * @internal
 *
 */
public function runtime_info() {
	//--
	return SmartPgsqlDb::runtime_info($this->connection);
	//--
} //END FUNCTION


//==================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>