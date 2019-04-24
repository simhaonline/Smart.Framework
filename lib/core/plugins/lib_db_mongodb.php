<?php
// [LIB - Smart.Framework / Plugins / MongoDB Database Client]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//======================================================
// Smart-Framework - MongoDB Client
// DEPENDS:
//	* Smart::
// DEPENDS-EXT: PHP MongoDB / PECL (v.1.1.0 or later)
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class Smart MongoDB Client (for PHP MongoDB extension v.1.1.0 or later)
 * Tested and Stable on MongoDB Server versions: 3.2 / 3.4 / 3.6 / 4.0 / 4.1
 *
 * <code>
 *
 * // sample mongo config
 * $cfg_mongo = array();
 * $cfg_mongo['type'] 		= 'mongo-standalone'; 					// mongodb server(s) type: 'mongo-standalone' | 'mongo-cluster' (sharding)
 * $cfg_mongo['server-host']	= '127.0.0.1';						// mongodb host
 * $cfg_mongo['server-port']	= '27017';						// mongodb port
 * $cfg_mongo['dbname']		= 'smart_framework';					// mongodb database
 * $cfg_mongo['username'] 		= '';							// mongodb username
 * $cfg_mongo['password'] 		= '';							// mongodb Base64-Encoded password
 * $cfg_mongo['timeout']		= 5;							// mongodb connect timeout in seconds
 * $cfg_mongo['slowtime']		= 0.0035;						// 0.0025 .. 0.0090 slow query time (for debugging)
 *
 * $mongo = new \SmartMongoDb($cfg_mongo);
 *
 * // sample insert
 * $doc = [];
 * $doc['_id']  = $mongo->assign_uuid();
 * $doc['name'] = 'My Name';
 * $doc['description'] = 'Some description goes here ...';
 * $insert = $mongo->insert('myTestCollection', $doc);
 * var_dump($insert);
 *
 * // sample find
 * $query = $mongo->find(
 * 	'myTestCollection',
 * 	[ 'name' => 'My Name' ], // filter (update all except these)
 * 	[ // projection
 * 		'name',
 * 		'description'
 * 	],
 * 	[
 * 		'limit' => 1, // limit
 * 		'skip' => 0 // offset
 * 	]
 * );
 * var_dump($query);
 *
 * </code>
 *
 * @usage 		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hint 		Important: MongoDB database specifies that max BSON document size is 16 megabytes and supports no more than 100 levels of nesting, thus this limit cannot be exceeded from PHP side when creating new mongodb documents: https://docs.mongodb.com/manual/reference/limits/ ; To store documents larger than the maximum size, MongoDB provides the GridFS API
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP MongoDB (v.1.1.0 or later) ; classes: Smart
 * @version 	v.20190423
 * @package 	Database:MongoDB
 *
 * @method MIXED		count($strCollection, $arrQuery)											# count documents in a collection
 * @method MIXED		find($strCollection, $arrQuery, $arrProjFields, $arrOptions)				# find single or multiple documents in a collection with optional filter criteria / limit
 * @method MIXED		findone($strCollection, $arrQuery, $arrProjFields, $arrOptions)				# find single document in a collection with optional filter criteria / limit
 * @method MIXED		bulkinsert($strCollection, $arrMultiDocs)									# add multiple documents to a collection
 * @method MIXED		insert($strCollection, $arrDoc)												# add single document to a collection
 * @method MIXED		upsert($strCollection, $arrFilter, $strUpdOp, $arrUpd)						# insert single or modify single or multi documents in a collection that are matching the filter criteria ; this is always non-fatal, will throw catcheable exception on error ...
 * @method MIXED		update($strCollection, $arrFilter, $strUpdOp, $arrUpd)						# modify single or many documents in a collection that are matching the filter criteria
 * @method MIXED		delete($strCollection, $arrFilter)											# delete single or many documents from a collection that are matching the filter criteria
 * @method MIXED		command($arrCmd)															# run a command over database like: aggregate, distinct, mapReduce, create Collection, drop Collection, ...
 * @method MIXED		igcommand($arrCmd)															# run a command over database and ignore if error ; in the case of throw error will ignore it and will not stop execution ; will return the errors instead of result like: create Collection which may throw errors if collection already exists, drop Collection, similar if does not exists
 *
 */
final class SmartMongoDb { // !!! Use no paranthesis after magic methods doc to avoid break the comments !!!

	// ->


/** @var string */
private $server;
private $srvver;
private $extver;

/** @var string */
private $db;

/** @var timeout */
private $timeout;

/** @var resource */
private $mongodbclient;

/** @var $mongodb */
private $mongodb;

/** @var $collection */
private $collection;

/** @var slow_time */
private $slow_time = 0.0035;

/** @var fatal_err */
private $fatal_err = true;

/** @var connex_key */
private $connex_key = '';

/** @var connected */
private $connected = false;


//======================================================
/**
 * Class constructor
 *
 * @param 	STRING 	$col 				:: MongoDB Collection
 * @param 	ARRAY 	$y_configs_arr 		:: The Array of Configuration parameters - the ARRAY STRUCTURE should be identical with the default config.php: $configs['mongodb'].
 *
 */
public function __construct($y_configs_arr=array(), $y_fatal_err=true) {

	//--
	$this->extver = (string) phpversion('mongodb');
	//--
	if(version_compare((string)$this->extver, '1.1.0') < 0) { // to have all features req. 1.1.0
		$this->error('[INIT]', 'PHP MongoDB Extension', 'CHECK PHP MongoDB Version', 'This version of MongoDB Client Library needs MongoDB PHP Extension v.1.1.0 or later. The current version is: '.$this->extver);
		return;
	} //end if
	//--

	//--
	$this->fatal_err = (bool) $y_fatal_err;
	//--

	//--
	$y_configs_arr = (array) $y_configs_arr;
	//--
	if(Smart::array_size($y_configs_arr) <= 0) { // if not from constructor, try to use the default
		$y_configs_arr = Smart::get_from_config('mongodb');
	} //end if
	//--
	if(Smart::array_size($y_configs_arr) > 0) {
		$type 		= (string) $y_configs_arr['type'];
		$db 		= (string) $y_configs_arr['dbname'];
		$host 		= (string) $y_configs_arr['server-host'];
		$port 		= (string) $y_configs_arr['server-port'];
		$timeout 	= (string) $y_configs_arr['timeout'];
		$username 	= (string) $y_configs_arr['username'];
		$password 	= (string) $y_configs_arr['password'];
		$timeslow 	= (float)  $y_configs_arr['slowtime'];
	//	$transact 	= (string) $y_configs_arr['transact']; // reserved for future usage (only MongoDB v.4+ supports transactions ...)
	} else {
		$this->error('[CHECK-CONFIGS]', 'MongoDB Configuration Init', 'CHECK Connection Config', 'Empty Configuration');
		return;
	} //end if
	//--
	if((string)$type != 'mongo-cluster') {
		$type = 'mongo-standalone';
	} //end if else
	//--
	if((string)$password != '') {
		$password = (string) base64_decode((string)$password);
	} //end if
	//--
	if(((string)$host == '') OR ((string)$port == '') OR ((string)$db == '') OR ((string)$timeout == '')) {
		$this->error('[CHECK-CONFIGS]', 'MongoDB Configuration Init', 'CHECK Connection Params: '.$host.':'.$port.'@'.$db, 'Some Required Parameters are Empty');
		return;
	} //end if
	//--
	$this->srvver = '';
	$this->server = (string) $host.':'.$port;
	$this->db = (string) $db;
	//--
	$this->timeout = Smart::format_number_int($timeout, '+');
	if($this->timeout < 1) {
		$this->timeout = 1;
	} //end if
	if($this->timeout > 60) {
		$this->timeout = 60;
	} //end if
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
			'type' => 'metainfo',
			'data' => 'MongoDB App Connector Version: '.SMART_FRAMEWORK_VERSION
		]);
		//--
		if((float)$timeslow > 0) {
			$this->slow_time = (float) $timeslow;
		} else {
			$this->slow_time = 0.0035; // default slow time for mongodb
		} //end if
		if($this->slow_time < 0.0000001) {
			$this->slow_time = 0.0000001;
		} elseif($this->slow_time > 0.9999999) {
			$this->slow_time = 0.9999999;
		} //end if
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|slow-time', number_format($this->slow_time, 7, '.', ''), '=');
		SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
			'type' => 'metainfo',
			'data' => 'Fast Query Reference Time < '.number_format($this->slow_time, 7, '.', '').' seconds'
		]);
		//--
	} //end if
	//--

	//--
	$this->connex_key = (string) $this->server.'@'.$this->db.'#'.$username;
	//--

	//--
	if(!class_exists('\\MongoDB\\Driver\\Manager')) {
		$this->error((string)$this->connex_key, 'MongoDB Driver', 'PHP MongoDB Driver Manager is not available', '');
		return;
	} //end if
	//--
	if(!class_exists('\\MongoDB\\Driver\\Command')) {
		$this->error((string)$this->connex_key, 'MongoDB Driver', 'PHP MongoDB Driver Command is not available', '');
		return;
	} //end if
	//--
	if(!class_exists('\\MongoDB\\Driver\\Query')) {
		$this->error((string)$this->connex_key, 'MongoDB Driver', 'PHP MongoDB Driver Query is not available', '');
		return;
	} //end if
	//--
	if(!class_exists('\\MongoDB\\Driver\\BulkWrite')) {
		$this->error((string)$this->connex_key, 'MongoDB Driver', 'PHP MongoDB Driver BulkWrite is not available', '');
		return;
	} //end if
	//--
	if(!class_exists('\\MongoDB\\Driver\\WriteResult')) {
		$this->error((string)$this->connex_key, 'MongoDB Driver', 'PHP MongoDB Driver WriteResult is not available', '');
		return;
	} //end if
	//--

	//--
	$this->connect($type, $username, $password);
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * A replacement for the default MongoDB object ID, will generate a 32 characters very unique UUID
 *
 * @return 	STRING						:: UUID (base36)
 */
public function assign_uuid() {

	//--
	return (string) Smart::uuid_10_seq().'-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str();
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Get the MongoDB Extension version
 *
 * @return 	STRING						:: MongoDB extension version
 */
public function get_ext_version() {

	//--
	return (string) $this->extver;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Get the MongoDB server version
 *
 * @return 	STRING						:: MongoDB version
 */
public function get_server_version() {

	//--
	if((string)$this->srvver == '') {
		//--
		$arr_build_info = $this->command(['buildinfo' => true]);
		//--
		if(is_array($arr_build_info)) {
			if(is_array($arr_build_info[0])) {
				$this->srvver = (string) trim((string)$arr_build_info[0]['version']);
			} //end if
		} //end if
		//--
		$arr_build_info = null;
		//--
	} //end if
	//--
	if((string)$this->srvver == '') {
		$this->srvver = '0.0'; // avoid requery
	} //end if
	//--

	//--
	return (string) $this->srvver;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * Get the MongoDB FTS Dictionary by Two Letter language code (ISO 639-1)
 *
 * @return 	STRING						:: dictionary name (ex: 'english' - if available or 'none' - if n/a)
 */
public function getFtsDictionaryByLang($lang) {
	//--
	$dictionary = '';
	//--
	$lang = (string) strtolower((string)$lang);
	//--
	switch((string)$lang) { // https://docs.mongodb.com/manual/reference/text-search-languages/
		case 'en':
			$dictionary = 'english';
			break;
		case 'de':
			$dictionary = 'german';
			break;
		case 'fr':
			$dictionary = 'french';
			break;
		case 'es':
			$dictionary = 'spanish';
			break;
		case 'pt':
			$dictionary = 'portuguese';
			break;
		case 'ro':
			$dictionary = 'romanian';
			break;
		case 'it':
			$dictionary = 'italian';
			break;
		case 'nl':
			$dictionary = 'dutch';
			break;
		case 'da':
			$dictionary = 'danish';
			break;
		case 'nb':
			$dictionary = 'norwegian';
			break;
		case 'fi':
			$dictionary = 'finnish';
			break;
		case 'sv':
			$dictionary = 'swedish';
			break;
		case 'ru':
			$dictionary = 'russian';
			break;
		case 'hu':
			$dictionary = 'hungarian';
			break;
		case 'tr':
			$dictionary = 'turkish';
			break;
		default:
			$dictionary = 'none'; // text search uses simple tokenization with no list of stop words and no stemming
	} //end switch
	//--
	return (string) $dictionary;
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Test if a command output is OK compliant (will check if result[0][ok] == 1)
 * Notice: not all MongoDB commands will return this standard answer, but for most will work
 *
 * @param MIXED result							:: result output from a mongodb command
 *
 * @return BOOLEAN								:: TRUE / FALSE
 */
public function is_command_ok($result) {

	//--
	$is_ok = false;
	//--
	if(is_array($result)) {
		if(is_array($result[0])) {
			if((int)$result[0]['ok'] == 1) {
				$is_ok = true;
			} //end if
		} //end if
	} //end if
	//--

	//--
	return (bool) $is_ok;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * this is the Magic Call Method
 *
 * @access 		private
 * @internal
 *
 */
public function __call($method, array $args) {

	//--
	$method = (string) $method;
	$args = (array) $args;
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		$time_start = microtime(true);
	} //end if
	//--

	//--
	if(!is_object($this->mongodbclient)) {
		$this->error((string)$this->connex_key, 'MongoDB Initialize', 'MongoDB->INIT-MANAGER() :: '.$this->connex_key.'/'.$this->collection, 'ERROR: MongoDB Manager Object is null ...');
		return null;
	} //end if
	//--

	//--
	$obj = null;
	$qry = array();
	$opts = array();
	$drows = 0;
	$dcmd = 'nosql';
	$dmethod = (string) $method;
	$skipdbg = false;
	//--
	switch((string)$method) {
		//-- collection methods
		case 'count': // ARGS [ strCollection, arrQuery ]
			//--
			$dcmd = 'count';
			//--
			$this->collection = (string) trim((string)$args[0]); // strCollection
			if((string)trim((string)$this->collection) == '') {
				$this->error((string)$this->connex_key, 'MongoDB Count', 'MongoDB->'.$method.'()', 'ERROR: Empty Collection name ...', $args);
				return 0;
			} //end if
			//--
			$qry = (array) $args[1]; // arrQuery
			//--
			$command = new \MongoDB\Driver\Command([
				'count' => (string) $this->collection,
				'query' => (array) $qry
			]);
			if(!is_object($command)) {
				$this->error((string)$this->connex_key, 'MongoDB Count', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Command Object is null ...', $args);
				return 0;
			} //end if
			//--
			try {
				$cursor = $this->mongodbclient->executeCommand($this->db, $command);
			} catch(Exception $err) {
				$this->error((string)$this->connex_key, 'MongoDB Count Execute', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: '.$err->getMessage(), $args);
				return 0;
			} //end try
			if(!is_object($cursor)) {
				$this->error((string)$this->connex_key, 'MongoDB Count Cursor', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Cursor Object is null ...', $args);
				return 0;
			} //end if
			$cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
			//print_r($cursor->toArray()); die();
			$obj = 0;
			if(is_object($cursor)) {
				$tmp_obj = (array) $cursor->toArray();
				if(is_array($tmp_obj[0])) {
					$tmp_obj = (array) $tmp_obj[0];
					if(array_key_exists('n', (array)$tmp_obj)) {
						if((int)$tmp_obj['ok'] == 1) {
							$obj = (int) $tmp_obj['n'];
							$drows = (int) $obj;
						} //end if
					} //end if
				} //end if
				$tmp_obj = array(); // free mem
			} //end if object
			//--
			unset($cursor);
			unset($command);
			//--
			break;
		//--
		case 'find': 	// ARGS [ strCollection, arrQuery, arrProjFields, arrOptions ]
		case 'findone': // ARGS [ strCollection, arrQuery, arrProjFields, arrOptions ]
			//--
			$dcmd = 'read';
			//--
			$this->collection = (string) trim((string)$args[0]); // strCollection
			if((string)trim((string)$this->collection) == '') {
				$this->error((string)$this->connex_key, 'MongoDB Read', 'MongoDB->'.$method.'()', 'ERROR: Empty Collection name ...', $args);
				return array();
			} //end if
			//--
			$qry = (array) $args[1]; // arrQuery
			//--
			if(is_array($args[3])) {
				$opts = (array) $args[3]; // arrOptions
			} //end if
			//-- fix: find one must have limit 1, offset 0
			if((string)$method == 'findone') {
				$opts['limit'] = 1; // limit
				$opts['skip'] = 0; // offset
			} //end if
			//-- fix: select just particular fields
			$opts['projection'] = array(); // arrProjFields
			if(Smart::array_size($args[2]) > 0) {
				if(\Smart::array_type_test($args[2]) === 2) { // associative
					foreach((array)$args[2] as $key => $val) {
						$key = (string) trim((string)$key);
						if((string)$key != '') {
							$opts['projection'][(string)$key] = $val; // mixed: number or array
						} //end if
					} //end foreach
				} elseif(\Smart::array_type_test($args[2]) === 1) { // non-associative
					for($i=0; $i<\Smart::array_size($args[2]); $i++) {
						$key = (string) trim((string)$args[2][$i]);
						if((string)$key != '') {
							$opts['projection'][(string)$key] = 1; // must be 1 here
						} //end if
					} //end for
				} //end if else
			} //end if
			//print_r($opts); die();
			//--
			$query = new \MongoDB\Driver\Query( // max 2 parameters
				(array) $qry, // query (empty: select all)
				(array) $opts // options
			);
			//print_r($query); die();
			if(!is_object($query)) {
				$this->error((string)$this->connex_key, 'MongoDB Read', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Query Object is null ...', $args);
				return array();
			} //end if
			//--
			try {
				$cursor = $this->mongodbclient->executeQuery($this->db.'.'.$this->collection, $query);
			} catch(Exception $err) {
				$this->error((string)$this->connex_key, 'MongoDB Read Execute', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: '.$err->getMessage(), $args);
				return array();
			} //end try
			if(!is_object($cursor)) {
				$this->error((string)$this->connex_key, 'MongoDB Read Cursor', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Cursor Object is null ...', $args);
				return array();
			} //end if
			$cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
			//print_r($cursor->toArray()); die();
			$obj = array();
			if(is_object($cursor)) {
				$obj = \Smart::json_decode(
					(string) \Smart::json_encode(
						(array)$cursor->toArray(),
						false, // no pretty print
						true, // unescaped unicode
						false // html safe
					),
					true // return array
				); // mixed, normalize via json:encode/decode
				if(!is_array($obj)) {
					$obj = array();
				} //end if
				$drows = (int) Smart::array_size($obj);
				if((string)$method == 'findone') {
					if(is_array($obj[0])) {
						$obj = (array) $obj[0];
						$drows = 1;
					} else {
						$obj = array();
						$drows = 0;
					} //end if
				} //end if
			} //end if object
			//--
			unset($cursor);
			unset($query);
			//--
			//print_r($obj); die();
			//--
			break;
		//--
		case 'bulkinsert': 	// ARGS [ strCollection, arrMultiDocs ] ; can do multiple inserts
		case 'insert': 		// ARGS [ strCollection, arrDoc ] ; can do just single insert
		case 'upsert': 		// ARGS [ strCollection, arrDoc ] ; can do just single insert or single/multi update
		case 'update': 		// ARGS [ strCollection, arrFilter, strUpdOp, arrUpd ] ; can do single or multi update
			//--
			$dcmd = 'write';
			//--
			$this->collection = (string) trim((string)$args[0]); // strCollection
			if((string)trim((string)$this->collection) == '') {
				$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'()', 'ERROR: Empty Collection name ...', $args);
				return array();
			} //end if
			//--
			$write = new \MongoDB\Driver\BulkWrite();
			if(!is_object($write)) {
				$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Write Object is null ...', $args);
				return array();
			} //end if
			//--
			$num_docs = 0;
			//--
			if((string)$method == 'bulkinsert') {
				if(Smart::array_type_test($args[1]) === 1) { // 1: non-associative array of multi docs
					$qry = 'bulkinsert['.Smart::array_size($args[1]).']';
					$opts = [];
					for($i=0; $i<Smart::array_size($args[1]); $i++) {
						if(Smart::array_size($args[1][$i]) > 0) {
							$write->insert(
								(array) $args[1][$i] // doc
							);
							$num_docs++;
						} else {
							$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Multi-Document #'.$i.' is empty or not array ...', $args);
							return array();
							break;
						} //end if
					} //end for
				} else {
					$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Invalid Multi-Document structure ...', $args);
					return array();
				} //end if else
			} elseif((string)$method == 'insert') {
				$qry = 'insert';
				$opts = [];
				if(Smart::array_size($args[1]) > 0) {
					$write->insert(
						(array) $args[1] // doc
					);
					$num_docs++;
				} else {
					$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Document is empty or not array ...', $args);
					return array();
					break;
				} //end if
			} elseif(((string)$method == 'update') OR ((string)$method == 'upsert')) {
				if(!is_array($args[1])) {
					$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Invalid Filter provided ...', $args);
					return array();
				} //end if
				$qry = (string) 'update:'.$args[2];
				if((string)$method == 'upsert') {
					$opts = [ // update options
						'multi' 	=> true, // update all the matching documents
						'upsert' 	=> true // if filter does not match an existing document, do insert a single document
					];
				} else { // update
					$opts = [ // update options
						'multi' 	=> true, // update all the matching documents
						'upsert' 	=> false // if filter does not match an existing document, do not insert a single document
					];
				} //end if else
				if(Smart::array_size($args[3]) > 0) {
					$write->update(
						(array) $args[1], 									// filter
						(array) [ (string)$args[2] => (array)$args[3] ], 	// must be in format: [ '$set|$inc|$mul|...' => (array)$doc ]
						(array) $opts										// options
					);
					$num_docs++;
				} else {
					$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Document is empty or not array or invalid format ...', $args);
					return array();
					break;
				} //end if
			} //end if else
			//--
			if($num_docs <= 0) {
				$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: No valid document(s) found ...', $args);
				return array();
			} //end if
			//--
			try {
				$result = $this->mongodbclient->executeBulkWrite($this->db.'.'.$this->collection, $write);
			} catch(Exception $err) {
				if((string)$method == 'upsert') {
					$is_fatal = false; // for upsert, will throw catcheable exception because: 'Multiple clients can simultaneously update the collection. Wiredtiger will lock the document to be updated, rather than the collection), thus may result in multi-concurrency error !!
				} else {
					$is_fatal = null; // leave as default (depends on how $this->fatal_err is set)
				} //end if else
				$this->error(
					(string) $this->connex_key,
					'MongoDB Write Execute',
					'MongoDB->'.$method.'() :: '.$this->collection,
					'ERROR: '.$err->getMessage(),
					$args,
					'', // warning
					$is_fatal // mixed: false OR null
				);
				return array();
			} //end try
			if(!is_object($result)) {
				$this->error((string)$this->connex_key, 'MongoDB Write Result', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Result Object is null ...', $args);
				return array();
			} //end if
			$obj = array();
			if($result instanceof \MongoDB\Driver\WriteResult) {
				$msg = (string) implode("\n", (array)$result->getWriteErrors());
				$msg = (string) trim((string)$msg);
				if((string)$msg == '') {
					$msg = 'oknosqlwriteoperation';
				} //end if
				$obj[0] = (string) $msg; // ok / error message
				$obj[1] = 0; // affected
				if(((string)$method == 'insert') OR ((string)$method == 'bulkinsert')) {
					$obj[1] = (int) $result->getInsertedCount();
				} elseif(((string)$method == 'upsert') OR ((string)$method == 'update')) {
					$obj[1] = (int) ((int)$result->getUpsertedCount() + (int)$result->getModifiedCount());
				} //end if else
				$obj[2] = (string) $qry; // query
				$obj[3] = []; // return extra messages
				if((string)$method == 'upsert') {
					$obj[3] = [
						'upserted-ids' => (array) $result->getUpsertedIds()
					];
				} //end if
				$msg = '';
				$drows = (int) $obj[1];
			} else {
				$this->error((string)$this->connex_key, 'MongoDB Write Result Type', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Result Object is not instance of WriteResult ...', $args);
				return array();
			} //end if
			//--
			//print_r($result); die();
			//--
			unset($result);
			unset($write);
			//--
			//print_r($obj); die();
			//--
			break;
		//--
		case 'delete': // ARGS [ strCollection, arrFilter ]
			//--
			$dcmd = 'write';
			//--
			$this->collection = (string) trim((string)$args[0]); // strCollection
			if((string)trim((string)$this->collection) == '') {
				$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'()', 'ERROR: Empty Collection name ...', $args);
				return array();
			} //end if
			//--
			$write = new \MongoDB\Driver\BulkWrite();
			if(!is_object($write)) {
				$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Write Object is null ...', $args);
				return array();
			} //end if
			//--
			if(!is_array($args[1])) {
				$this->error((string)$this->connex_key, 'MongoDB Write', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Invalid Filter provided ...', $args);
				return array();
			} //end if
			$qry = 'delete';
			$opts = [ // delete options
				'limit' => false // delete all matching documents
			];
			$write->delete(
				(array) $args[1], 									// filter
				(array) $opts										// options
			);
			try {
				$result = $this->mongodbclient->executeBulkWrite($this->db.'.'.$this->collection, $write);
			} catch(Exception $err) {
				$this->error((string)$this->connex_key, 'MongoDB Write Execute', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: '.$err->getMessage(), $args);
				return array();
			} //end try
			if(!is_object($result)) {
				$this->error((string)$this->connex_key, 'MongoDB Write Result', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Result Object is null ...', $args);
				return array();
			} //end if
			$obj = array();
			if($result instanceof \MongoDB\Driver\WriteResult) {
				$msg = (string) implode("\n", (array)$result->getWriteErrors());
				$msg = (string) trim((string)$msg);
				if((string)$msg == '') {
					$msg = 'oknosqlwriteoperation';
				} //end if
				$obj[0] = (string) $msg;
				$obj[1] = (int) $result->getDeletedCount();
				$obj[2] = (string) $qry;
				$obj[3] = []; // return extra messages
				$msg = '';
				$drows = (int) $obj[1];
			} else {
				$this->error((string)$this->connex_key, 'MongoDB Write Result Type', 'MongoDB->'.$method.'() :: '.$this->collection, 'ERROR: Result Object is not instance of WriteResult ...', $args);
				return array();
			} //end if
			//--
			//print_r($result); die();
			//--
			unset($result);
			unset($write);
			//--
			//print_r($obj); die();
			//--
			break;
		//--
		case 'command': 	// ARGS [ arrCmd ]
		case 'igcommand': 	// ARGS [ arrCmd ]
			//-- dbg types: 'count', 'read', 'write', 'transaction', 'set', 'metainfo'
			$qry = (array) $args[0]; // arrQuery
			foreach($qry as $kk => $vv) {
				if((string)strtolower((string)$kk) == 'buildinfo') {
					$dcmd = (string) 'metainfo';
				} elseif((string)strtolower((string)$kk) == 'count') {
					$dcmd = (string) 'count';
				} elseif(in_array((string)strtolower((string)$kk), ['find', 'aggregate', 'distinct', 'mapreduce', 'geosearch'])) {
					$dcmd = (string) 'read';
				} elseif(in_array((string)strtolower((string)$kk), ['delete', 'insert', 'update'])) {
					$dcmd = (string) 'write';
				} //end if
				$dmethod = (string) str_replace(':', '-', (string)$kk).'::'.$method; // subname
				break;
			} //end if
			//--
			$command = new \MongoDB\Driver\Command((array)$qry);
			if(!is_object($command)) {
				$this->error((string)$this->connex_key, 'MongoDB Command', 'MongoDB->'.$dmethod.'()', 'ERROR: Command Object is null ...', $args);
				return array();
			} //end if
			//--
			$igerr = false;
			try {
				$cursor = $this->mongodbclient->executeCommand($this->db, $command);
			} catch(Exception $err) {
				if((string)$method == 'igcommand') {
					$igerr = (string) $err->getMessage(); // must be type string
				} else {
					$this->error((string)$this->connex_key, 'MongoDB Command Execute', 'MongoDB->'.$dmethod.'()', 'ERROR: '.$err->getMessage(), $args);
					return array();
				} //end if else
			} //end try
			$obj = array();
			if(((string)$method == 'command') OR ($igerr === false)) {
				if(!is_object($cursor)) {
					$this->error((string)$this->connex_key, 'MongoDB Command Cursor', 'MongoDB->'.$dmethod.'()', 'ERROR: Cursor Object is null ...', $args);
					return array();
				} //end if
				$cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
				//print_r($cursor->toArray()); die();
				if(is_object($cursor)) {
					$obj = \Smart::json_decode(
						(string) \Smart::json_encode(
							(array)$cursor->toArray(),
							false, // no pretty print
							true, // unescaped unicode
							false // html safe
						),
						true // return array
					); // mixed, normalize via json:encode/decode
					if(!is_array($obj)) {
						$obj = array();
					} //end if
					$drows = (int) Smart::array_size($obj);
				} //end if object
			} else {
				$obj = array(
					'ERRORS' => [
						'err-msg' 	=> (string) $igerr,
						'type' 		=> 'catcheable PHP Exception / MongoDB Manager: executeCommand',
						'class' 	=> (string) __CLASS__,
						'function' 	=> (string) __FUNCTION__,
						'method' 	=> (string) $dmethod
					]
				);
			} //end if else
			//--
			unset($cursor);
			unset($command);
			//print_r($obj); die();
			//--
			break;

		//--
		default:
			//--
			$this->error((string)$this->connex_key, 'MongoDB Method', 'MongoDB->'.$method.'()', 'ERROR: The selected method ['.$method.'] is NOT implemented ...', $args);
			return null;
			//--
	} //end switch
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		if($skipdbg !== true) {
			if($this->connected === true) { // avoid register pre-connect commands like version)
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|total-queries', 1, '+');
				//--
				$time_end = (float) (microtime(true) - (float)$time_start);
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|total-time', $time_end, '+');
				//--
				$dbg_arr_cmd = [];
				if($this->collection) {
					$dbg_arr_cmd['Collection'] = (string) $this->collection;
				} //end if
				$dbg_arr_cmd['Query'] = (array) $qry;
				if($opts) {
					$dbg_arr_cmd['Options'] = (array) $opts;
				} //end if
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
					'type' 			=> (string) $dcmd,
					'data' 			=> (string) strtoupper((string)$dmethod),
					'command' 		=> (array)  $dbg_arr_cmd,
					'time' 			=> (string) Smart::format_number_dec($time_end, 9, '.', ''),
					'rows' 			=> (int)    $drows,
					'connection' 	=> (string) $this->connex_key
				]);
				//--
				$dbg_arr_cmd = null; // free mem
				//--
			} //end if
		} //end if
	} //end if
	//--

	//--
	return $obj; // mixed
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * this is the internal connector (will connect just when needed)
 *
 * @access 		private
 * @internal
 *
 */
private function connect($type, $username, $password) {

	//--
	if((string)$type == 'mongo-cluster') { // cluster (sharding)
		$concern_rd = 'majority'; // requires the servers to be started with --enableMajorityReadConcern
		$concern_wr = 'majority'; // make sense if with a sharding cluster
	} else { // mongo-standalone
		$concern_rd = 'local';
		$concern_wr = 1;
	} //end if else
	//--

	//--
	$options = array(
		'connect' 			=> false,
		'connectTimeoutMS' 	=> (int) ($this->timeout * 1000),
		'socketTimeoutMS' 	=> (int) (SMART_FRAMEWORK_NETSOCKET_TIMEOUT * 1000),
		'readConcernLevel' 	=> $concern_rd, // rd concern
		'w' 				=> $concern_wr, // wr concern
		'wTimeoutMS' 		=> (int) (SMART_FRAMEWORK_NETSOCKET_TIMEOUT * 1000) // if this is 0 (no timeout) the write operation will block indefinitely
	);
	//--
	if((string)$username != '') {
		$options['username'] = (string) $username;
		if((string)$password != '') {
			$options['password'] = (string) $password;
			$options['authMechanism'] = 'MONGODB-CR';
		} //end if
	} //end if
	//--

	//--
	if(is_object(SmartFrameworkRegistry::$Connections['mongodb'][(string)$this->connex_key])) {
		//--
		$this->mongodbclient = &SmartFrameworkRegistry::$Connections['mongodb'][(string)$this->connex_key];
		$this->connected = true;
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
				'type' => 'open-close',
				'data' => 'Re-Using MongoDB Manager Instance :: ServerType ['.$type.']: '.$this->connex_key
			]);
		} //end if
		//--
	} else {
		//--
		try {
			$this->mongodbclient = new \MongoDB\Driver\Manager(
				(string) 'mongodb://'.$this->server.'/'.$this->db,
				(array) $options
			);
		} catch(Exception $err) {
			$this->mongodbclient = null;
			$this->error((string)$this->connex_key, 'MongoDB Manager', 'Failed to Initialize Object: '.$this->db.' on '.$this->server, 'ERROR: '.$err->getMessage());
			return false;
		} //end try catch
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
				'type' => 'open-close',
				'data' => 'Creating MongoDB Manager Instance :: ServerType ['.$type.']: '.$this->connex_key
			]);
		} //end if
		//--
		$this->get_server_version(); // this will register the $this->srvver if req.
		//--
		$min_ver_srv = '2.6.0';
		if(((string)$this->srvver == '') OR (version_compare((string)$min_ver_srv, (string)$this->srvver) > 0)) {
			$this->mongodbclient = null;
			$this->error((string)$this->connex_key, 'MongoDB Manager', 'Invalid MongoDB Server Version on '.$this->server, 'ERROR: Minimum MongoDB supported Server version is: '.$min_ver_srv.' but this Server version is: '.$this->srvver);
			return false;
		} //end if
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			//--
			SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
				'type' => 'metainfo',
				'data' => 'MongoDB Extension Version: '.$this->extver
			]);
			//--
			SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
				'type' => 'metainfo',
				'data' => 'MongoDB Server Version: '.$this->srvver
			]);
			//--
		} //end if
		//--
		SmartFrameworkRegistry::$Connections['mongodb'][(string)$this->connex_key] = &$this->mongodbclient; // export connection
		$this->connected = true;
		//--
	} //end if else
	//--

	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
			'type' => 'set',
			'data' => 'Using Database: '.$this->db,
			'connection' => (string) $this->server,
			'skip-count' => 'yes'
		]);
		//--
	} //end if
	//--

	//--
	return true;
	//--

} //END FUNCTION
//======================================================


//======================================================
/**
 * this is for disconnect from MongoDB
 *
 * @access 		private
 * @internal
 *
 */
public function disconnect() {
	//--
	SmartFrameworkRegistry::$Connections['mongodb'][(string)$this->connex_key] = null; // close connection
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		SmartFrameworkRegistry::setDebugMsg('db', 'mongodb|log', [
			'type' => 'open-close',
			'data' => 'Destroying MongoDB Manager Instance: '.$this->connex_key
		]);
	} //end if
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Displays the MongoDB Errors and HALT EXECUTION (This have to be a FATAL ERROR as it occur when a FATAL MongoDB ERROR happens or when a Data Query fails)
 * PRIVATE
 *
 * @param STRING $y_area :: The Area
 * @param STRING $y_error_message :: The Error Message to Display
 * @param STRING $y_query :: The query
 * @param STRING $y_warning :: The Warning Title
 *
 * @return :: HALT EXECUTION WITH ERROR MESSAGE
 *
 */
private function error($y_conhash, $y_area, $y_info, $y_error_message, $y_query='', $y_warning='', $y_is_fatal=null) {
//--
if(($y_is_fatal === true) OR ($y_is_fatal === false)) { // depends on how is set, conform
	$y_is_fatal = (bool) $y_is_fatal;
} else { // NULL :: default, depend on how $this->fatal_err is
	if($this->fatal_err === false) {
		$y_is_fatal = false;
	} else {
		$y_is_fatal = true;
	} //end if else
} //end if else
//--
if($y_is_fatal === false) {
	throw new Exception('#MONGO-DB@'.$y_conhash.'# :: Q# // MongoDB Client :: EXCEPTION :: '.$y_area."\n".$y_info.': '.$y_error_message);
	return;
} //end if
//--
$def_warn = 'Execution Halted !';
$y_warning = (string) trim((string)$y_warning);
if(Smart::array_size($y_query) > 0) {
	$y_query = (string) print_r($y_query,1);
} //end if
$the_params = '- '.'MongoDB Manager v.'.$this->extver.' -';
if(SmartFrameworkRuntime::ifDebug()) {
	$width = 750;
	$the_area = (string) $y_area;
	if((string)$y_warning == '') {
		$y_warning = (string) $def_warn;
	} //end if
	$the_error_message = 'Operation FAILED: '.$def_warn."\n".$y_error_message;
	$the_query_info = (string) trim((string)$y_query);
	$y_query = ' '.trim((string)$the_params."\n".$y_info."\n".$y_query);
} else {
	$width = 550;
	$the_area = '';
	$the_error_message = 'Operation FAILED: '.$def_warn;
	$y_query = ' '.trim((string)$the_params."\n".$y_info."\n".$y_query);
	$the_query_info = ''; // do not display query if not in debug mode ... this a security issue if displayed to public ;)
	$the_params = '';
} //end if else
//--
$out = SmartComponents::app_error_message(
	'MongoDB Manager',
	'MongoDB',
	'NoSQL/DB',
	'Server',
	'lib/core/img/db/mongodb-logo.svg',
	$width, // width
	$the_area, // area
	$the_error_message, // err msg
	$the_params, // title or params
	$the_query_info // command
);
//--
Smart::raise_error(
	'#MONGO-DB@'.$y_conhash.' :: Q# // MongoDB Client :: ERROR :: '.$y_area."\n".'*** Error-Message: '.$y_error_message."\n".'*** Statement:'.$y_query,
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

// end of php code
?>