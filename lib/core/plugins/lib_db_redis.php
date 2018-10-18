<?php
// [LIB - SmartFramework / Plugins / Redis Database Client]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Redis Database Client
// DEPENDS:
//	* Smart::
//	* SmartParser::
// DEPENDS-EXT: PHP Sockets
//======================================================
// Tested and Stable on Redis versions:
// 2.6.x / 2.8.x / 3.0.x / 3.2.x
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// based on TinyRedisClient - the most lightweight Redis client written in PHP, by Petr Trofimov https://github.com/ptrofimov
// with portions of code from: PRedis StreamConnection, by Daniele Alessandri https://github.com/nrk/predis

/**
 * Class: SmartRedisDb - provides a Client for Redis (Data Structure / Caching) Server.
 * By default this class will just log the errors.
 *
 * <code>
 *
 * // Redis Client usage example:
 * $redis = new SmartRedisDb('localhost', '6379', 3); // connects at the database no. 3
 * $redis->set('key1', 'value1');
 * $redis->set('list1:key1', 'value2');
 * $value1 = $redis->get('key1');
 * if($redis->exists('key1')) {
 *     $value2 = $redis->get('list5:key7');
 * } //end if
 *
 * </code>
 *
 * @usage 		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hint 		for the rest of supported methods take a look at the SmartRedisDb class magic __call method ; Visit: http://redis.io/commands ; Most of the base methods are implemented.
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP Sockets ; classes: Smart
 * @version 	v.181018
 * @package 	Database:Redis
 *
 * @method	STRING		ping()										# Ping the Redis server ; returns: the test answer which is always PONG
 * @method	MIXED		get(STRING $key) 							# Get a Redis Key ; returns: the value of key as STRING or NULL if not exists
 * @method	MIXED	 	set(STRING $key, STRING $value)				# Set a Redis Key ; returns: OK on success or NULL on failure
 * @method	MIXED		append(STRING $key, STRING $value)			# Append a Redis Key ; returns: OK on success or NULL on failure
 * @method	INT			del(STRING $key) 							# Delete a Redis Key ; returns: 0 if not successful or 1 if successful
 * @method	INT			expire(STRING $key, INT $expireinseconds)	# Set the Expiration time for a Redis Key in seconds ; returns: 0 if not successful or 1 if successful
 * @method	INT			expireat(STRING $key, INT $expirationtime)	# Set the Expiration time for a Redis Key at unixtimestamp ; returns: 0 if not successful or 1 if successful
 * @method	INT			persist(STRING $key)						# Remove the existing expiration timeout on a key ; returns: 0 if not successful or 1 if successful
 * @method	INT			exists(STRING $key)							# Determine if a key exists ; returns 1 if the key exists or 0 if does not exists
 * @method	MIXED		keys(STRING $pattern)						# Get all keys matching a pattern ; return array of all keys matching a pattern or null if no key
 *
 */
final class SmartRedisDb {

// ->

/**
 * @var int
 * @ignore
 **/
public $recvbuf = 4096;

/** @var string */
private $server;

/** @var integer */
private $db;

/** @var timeout */
private $timeout;

/** @var $password */
private $password;

/** @var resource */
private $socket;

/** @var description */
private $description;

/** @var fatal_err */
private $fatal_err;

/** @var err */
private $err;

/** @var slow_time */
private $slow_time = 0.0005;

//======================================================
/**
 * Object Constructor
 *
 * @access 		private
 * @internal
 *
 */
public function __construct($host, $port, $db, $password='', $timeout=5, $y_debug_exch_slowtime=0.0005, $y_description='DEFAULT', $y_fatal_err=false) {
	//--
	if(((string)$host == '') OR ((string)$port == '') OR ((string)$db == '') OR ((string)$timeout == '')) {
		$this->error('Redis Configuration Init', 'Some Required Parameters are Empty', 'CFG:host:port@db#timeout'); // fatal error
		return;
	} //end if
	//--
	$this->server = $host.':'.$port;
	//--
	$this->db = Smart::format_number_int($db, '+');
	if($this->db < 0) {
		$this->db = 0;
	} //end if
	if($this->db > 15) {
		$this->db = 15;
	} //end if
	//--
	$this->timeout = Smart::format_number_int($timeout, '+');
	if($this->timeout < 1) {
		$this->timeout = 1;
	} //end if
	if($this->timeout > 30) {
		$this->timeout = 30;
	} //end if
	//--
	if((string)$password != '') {
		$this->password = (string) base64_decode((string)$password);
	} else {
		$this->password = '';
	} //end if else
	//--
	$this->description = (string) $y_description;
	//--
	$y_fatal_err = (bool) $y_fatal_err;
	if($y_fatal_err === true) {
		$this->fatal_err = false;
		$txt_conn = 'IGNORED BUT LOGGED AS WARNINGS';
	} else {
		$this->fatal_err = true;
		$txt_conn = 'FATAL ERRORS';
	} //end if else
	//--
	$this->err = false;
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		if((float)$y_debug_exch_slowtime > 0) {
			$this->slow_time = (float) $y_debug_exch_slowtime;
		} //end if
		if($this->slow_time < 0.0000001) {
			$this->slow_time = 0.0000001;
		} elseif($this->slow_time > 0.9999999) {
			$this->slow_time = 0.9999999;
		} //end if
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'redis|slow-time', number_format($this->slow_time, 7, '.', ''), '=');
		SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
			'type' => 'metainfo',
			'data' => 'Redis App Connector Version: '.SMART_FRAMEWORK_VERSION.' // Connection Errors are: '.$txt_conn
		]);
		//--
	} //end if
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * set the RecvBuff
 *
 * @access 		private
 * @internal
 *
 */
public function setRecvBuf($buff) {
	//--
	$this->recvbuf = (int) $buff;
	//--
	if($this->recvbuf < 512) {
		$this->recvbuf = 512;
	} elseif($this->recvbuf > 16384) {
		$this->recvbuf = 16384;
	} //end if
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
	if($this->err !== false) {
		if(SmartFrameworkRuntime::ifDebug()) {
			Smart::log_notice('#REDIS-DB# :: Method Call Aborted. Detected Previous Redis Error before calling the method: '.$method.'()');
		} //end if
		return null;
	} //end if
	//--
	$method = strtoupper((string)$method);
	$args = (array) $args;
	//--
	switch((string)$method) {
		//--
		case 'MULTI': // start transaction
		case 'EXEC': // commit transaction
		case 'DISCARD': // discard transaction
		//--
		case 'EXISTS': // determine if a key exists ; returns 1 if the key exists or 0 if does not exists
		case 'TYPE': // determine the type of the given key
		case 'STRLEN': // gets the strlen of a key
		//--
		case 'TTL': // get the TTL in seconds for a key ; -1 if the key does not expire ; -2 if the key does not exists
		case 'EXPIRE': // set the expire time for a key in seconds ; returns 1 on success or 0 on failure
		case 'EXPIREAT': // like EXPIRE but instead of set how many seconds to persist it sets the unix timestamp when will expire
		case 'PERSIST': // remove the existing expiration timeout on key
		//--
		case 'GET': // get a key value ; returns the key value or null
		case 'SET': // set a key with a value ; returns OK if successful
		case 'APPEND': // append a value to an existing key value
		case 'DEL': // delete a key ; a key is ignored if does not exists ; return (integer) the number of keys that have been deleted
		//--
		case 'RENAME': // renames key to newkey ; returns an error when the source and destination names are the same, or when key does not exist
		case 'MOVE': // move a key to the given DB ; returns 1 on success or 0 on failure
		//--
		case 'INCR': // increments a key that have an integer value with 1 ; max is 64-bit int ; if the value is non integer returns error ; returns the value after increment
		case 'INCRBY': // increments a key that have an integer value with the given int value ; max is 64-bit int ; if the value is non integer returns error ; returns the value after increment
		case 'DECR': // decrements a key that have an integer value with 1 ; max is 64-bit int ; if the value is non integer returns error ; returns the value after decrement
		case 'DECRBY': // decrements a key that have an integer value with the given int value ; max is 64-bit int ; if the value is non integer returns error ; returns the value after decrement
		//--
		case 'KEYS': // return all keys matching a pattern
		case 'SCAN': // available since Redis 2.8 ; incrementally iterate over a collection of keys
		case 'SORT': // sort key by pattern ; SORT mylist DESC ; SORT mylist ALPHA ; for UTF-8 the !LC_COLLATE environment must be set
		case 'RANDOMKEY': // return a random key from the currently selected database
		//--
		case 'HSET': // sets field in the hash stored at key to value
		case 'HDEL': // removes the specified fields from the hash stored at key
		case 'HEXISTS': // returns if field is an existing field in the hash stored at key
		case 'HGET': // returns the value associated with field in the hash stored at key
		case 'HGETALL': // returns all fields and values of the hash stored at key
		case 'HINCRBY': // increments the number stored at field in the hash stored at key by increment ; if key does not exist, a new key holding a hash is created
		case 'HINCRBYFLOAT': // increment the specified field of an hash stored at key, and representing a floating point number, by the specified increment
		case 'HKEYS': // returns all field names in the hash stored at key
		case 'HLEN': // returns the number of fields contained in the hash stored at key
		case 'HMGET': // returns the values associated with the specified fields in the hash stored at key
		case 'HMSET': // sets the specified fields to their respective values in the hash stored at key
		case 'HSCAN': // available since 2.8.0 ; iterates fields of Hash types and their associated values
		case 'HSET': // sets field in the hash stored at key to value ; if key does not exist, a new key holding a hash is created. If field already exists in the hash, it is overwritten
		case 'HSETNX': // sets field in the hash stored at key to value, only if field does not yet exist
		case 'HSTRLEN': // returns the string length of the value associated with field in the hash stored at key
		case 'HVALS': // returns all values in the hash stored at key
		//--
		case 'LINSERT': // inserts value in the list stored at key either before or after the reference value pivot
		case 'LINDEX': // returns the element at index 0..n in the list stored at key
		case 'LLEN': // returns the length of the list stored at key ; if key does not exist, it is interpreted as an empty list and 0 is returned ; an error is returned when the value stored at key is not a list
		case 'LPOP': // removes and returns the first element of the list stored at key
		case 'RPOP': // removes and returns the last element of the list stored at key
		case 'LPUSH': // insert all the specified values at the begining of the list stored at key ; key value
		case 'LPUSHX': // inserts value at the head of the list stored at key, only if key already exists and holds a list. In contrary to LPUSH, no operation will be performed when key does not yet exist
		case 'RPUSH': // insert all the specified values at the end of the list stored at key ; key value
		case 'RPUSHX': // inserts value at the tail of the list stored at key, only if key already exists and holds a list. In contrary to RPUSH, no operation will be performed when key does not yet exist
		case 'RPOPLPUSH': // atomically returns and removes the last element (tail) of the list stored at source, and pushes the element at the first element (head) of the list stored at destination
		case 'LRANGE': // get a list key value(s) ; key start stop
		case 'LREM': // remove list key(s) ; key count value ; count > 0: Remove elements equal to value moving from head to tail ; count < 0: Remove elements equal to value moving from tail to head ; count = 0: Remove all elements equal to value
		case 'LSET': // set a list key value ; key index value
		case 'LTRIM': // trim an existing list so that it will contain only the specified range of elements specified ; key start stop
		//--
		case 'SADD': // add a key to a set
		case 'SREM': // remove a key from a set
		case 'SMOVE': // atomically move member from the set at source to the set at destination
		case 'SCARD': // returns the cardinality of a key
		case 'SDIFF': // returns the difference between the first set and all the successive sets
		case 'SDIFFSTORE': // identical with SDIFF but instead of return will store the result
		case 'SINTER': // returns the intersection of all the given sets
		case 'SINTERSTORE': // identical with SINTER but instead of return will store the result
		case 'SUNION': // returns the members of the set resulting from the union of all the given sets
		case 'SUNIONSTORE': // identical with SUNION but instead of return will store the result
		case 'SISMEMBER': // returns if member is a member of the set stored at key
		case 'SMEMBERS': // returns all the members of the set value stored at key
		case 'SPOP': // removes and returns one or more random elements from the set value store at key
		case 'SRANDMEMBER': // when called with just the key argument, return a random element from the set value stored at key ; since Redis 2.6 a second count parameter has been added
		case 'SSCAN': // available since Redis 2.8 ; incrementally iterate over a collection of elements in a set
		//--
		case 'ZADD': // adds all the specified members with the specified scores to the sorted set stored at key
		case 'ZREM': // removes the specified members from the sorted set stored at key ; non existing members are ignored
		case 'ZCARD': // returns the sorted set cardinality (number of elements) of the sorted set stored at key
		case 'ZCOUNT': // returns the number of elements in the sorted set at key with a score between min and max
		case 'ZINCRBY': // increments the score of member in the sorted set stored at key by increment
		case 'ZINTERSTORE': // computes the intersection of numkeys sorted sets given by the specified keys, and stores the result in destination
		case 'ZRANGE': // returns the specified range of elements in the sorted set stored at key
		case 'ZRANGEBYSCORE': // returns all the elements in the sorted set at key with a score between min and max (including elements with score equal to min or max)
		case 'ZRANK': // returns the rank of member in the sorted set stored at key, with the scores ordered from low to high
		case 'ZREMRANGEBYSCORE': // removes all elements in the sorted set stored at key with a score between min and max (inclusive)
		case 'ZREVRANGE': // returns the specified range of elements in the sorted set stored at key
		case 'ZREVRANGEBYSCORE': // returns all the elements in the sorted set at key with a score between max and min (including elements with score equal to max or min)
		case 'ZREVRANK': // returns the rank of member in the sorted set stored at key, with the scores ordered from high to low
		case 'ZSCORE': // returns the score of member in the sorted set at key
		case 'ZUNIONSTORE': // computes the union of numkeys sorted sets given by the specified keys, and stores the result in destination
		case 'ZSCAN': // available since 2.8.0 ; iterates elements of Sorted Set types and their associated scores
		//--
		case 'SAVE': // synchronous save the DB to disk ; returns OK on success
		case 'FLUSHDB': // remove all keys from the selected database
		case 'FLUSHALL': // remove all keys from all databases
		case 'DBSIZE': // return the number of keys in the currently-selected database
		//--
		case 'INFO': // returns information and statistics about the server
		case 'TIME': // returns the current server time as a two items lists: a Unix timestamp and the amount of microseconds already elapsed in the current second
		//--
		case 'ECHO': // echo a message
		case 'PING': // ping the server ; returns PONG
		case 'QUIT': // always return OK ; ask the server to close the connection ; the connection is closed as soon as all pending replies have been written to the client
			//--
			if(!is_resource($this->connect())) { // this have it's own error raise mechanism
				if(SmartFrameworkRuntime::ifDebug()) {
					Smart::log_notice('#REDIS-DB# :: Redis connection FAILED just before calling the method: '.$method.'()');
				} //end if
				return null;
			} //end if
			//--
			return $this->run_command($method, $args);
			//--
			break;
		case 'AUTH': // password ; returns OK
		case 'SELECT': // select the DB by the given index (default is 0 .. 15)
			//--
			$this->error('Redis Dissalowed Command', 'Method is Forbidden', 'Method: '.$method); // fatal error
			return null;
			//--
		default:
			//--
			$this->error('Redis Unavailable Command', 'Method is Unavailable', 'Method: '.$method); // fatal error
			return null;
			//--
	} //end switch
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * this is the Run Command
 *
 * @access 		private
 * @internal
 *
 */
private function run_command($method, array $args) {
	//--
	if(!$this->socket) {
		$this->error('Redis Connection / Run', 'Connection Failed', 'Method: '.$method); // fatal error
		return null;
	} //end if
	//--
	$method = (string) $method;
	$args = (array) $args;
	//--
	array_unshift($args, $method);
	$cmd = '*'.count($args)."\r\n"; // no. of arguments
	foreach($args as $z => $item) {
		$cmd .= '$'.strlen($item)."\r\n"; // str length
		$cmd .= $item."\r\n"; // key contents
	} //end foreach
	//--
	if((string)$cmd == '') {
		//--
		$this->error('Redis Run Command', 'Empty commands are not allowed ...', ''); // fatal error
		return null;
		//--
	} //end if
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		$time_start = microtime(true);
		//--
	} //end if
	//--
	@fwrite($this->socket, $cmd);
	//--
	$response = $this->parse_response($method);
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'redis|total-queries', 1, '+');
		//--
		$time_end = (float) (microtime(true) - (float)$time_start);
		SmartFrameworkRegistry::setDebugMsg('db', 'redis|total-time', $time_end, '+');
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
			'type' => 'nosql',
			'data' => strtoupper($method).' :: '.$this->description,
			'command' => Smart::text_cut_by_limit((string)implode(' ', (array)$args), 1024, true, '[...data-longer-than-1024-bytes-is-not-logged-all-here...]'),
			'time' => Smart::format_number_dec($time_end, 9, '.', ''),
			'rows' => is_array($response) ? count($response) : strlen((string)$response),
			'connection' => (string) $this->socket
		]);
		//--
	} //end if
	//--
	return $response;
	//--
} //END FUNCTION
//======================================================


//======================================================
private function parse_response($method) {
	//--
	$result = null;
	//--
	if(!$this->socket) {
		$this->error('Redis Connection / Response', 'Connection Failed (1)', 'Method: '.$method); // fatal error
		return null;
	} //end if
	//--
	$line = @fgets($this->socket, $this->recvbuf);
	//--
	list($type, $result) = array($line[0], substr($line, 1, (strlen($line) - 3)));
	//--
	if((string)$type == '-') { // error message
		//--
		$this->error('Redis Response', 'Invalid Response', 'Method: '.$method); // fatal error
		return null;
		//--
	} elseif((string)$type == '$') { // bulk reply
		//--
		if($result == -1) {
			//--
			$result = null;
			//--
		} else {
			//--
			if(!$this->socket) {
				$this->error('Redis Connection / Response', 'Connection Failed (2)', 'Method: '.$method); // fatal error
				return null;
			} //end if
			//--
			/* Old Buggy Method
			$line = @fread($this->socket, ($result + 2));
			$result = substr($line, 0, (strlen($line) - 2));
			*/
			//### Fix from: Predis\Connection\StreamConnection->read() # case: case '$':
			$size = (int) $result;
			$bytes_left = ($size += 2);
			$result = '';
			do {
				$chunk = @fread($this->socket, min((int)$bytes_left, $this->recvbuf)); // 4096 was instead of $this->recvbuf
				if($chunk === false || $chunk === '') {
					$this->error('Redis Response', 'Error while reading bulk reply from the server', 'Method: '.$method); // fatal error
					return null;
				} //end if
				$result .= (string) $chunk;
				$bytes_left = (int) $size - strlen($result);
			} while($bytes_left > 0);
			$result = substr($result, 0, -2);
			//###
			//--
		} //end if else
		//--
	} elseif((string)$type == '*') { // multi-bulk reply
		//--
		$count = (int) $result;
		//--
		for($i=0, $result=array(); $i<$count; $i++) {
			//--
			$result[] = $this->parse_response($method);
			//--
		} //end for
		//--
	} //end if else
	//--
	return $result;
	//--
} //END FUNCTION
//======================================================


//======================================================
private function connect() {
	//--
	if(!is_resource($this->socket)) { // try to connect or re-use the connection
		//--
		if(array_key_exists((string)$this->server.'@'.$this->db, (array)SmartFrameworkRegistry::$Connections['redis']) AND is_resource(SmartFrameworkRegistry::$Connections['redis'][(string)$this->server.'@'.$this->db])) {
			//--
			$this->socket = SmartFrameworkRegistry::$Connections['redis'][(string)$this->server.'@'.$this->db]; // re-use conection (import)
			//--
			if(SmartFrameworkRuntime::ifDebug()) {
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
					'type' => 'open-close',
					'data' => 'Redis DB :: Re-Using Connection to: '.$this->server.'@'.$this->db.' :: '.$this->description.' @ Connection-Socket: '.$this->socket
				]);
				//--
			} //end if
			//--
			$errno = 0;
			$errstr = 'Trying to reuse the connection: '.$this->socket;
			//--
		} else {
			//--
			$this->socket = @stream_socket_client($this->server, $errno, $errstr, $this->timeout);
			//--
			if(SmartFrameworkRuntime::ifDebug()) {
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
					'type' => 'metainfo',
					'data' => 'Connection Timeout: '.$this->timeout.' seconds'
				]);
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
					'type' => 'metainfo',
					'data' => 'Fast Query Reference Time < '.$this->slow_time.' seconds'
				]);
				//--
				SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
					'type' => 'open-close',
					'data' => 'Redis DB :: Open Connection to: '.$this->server.'@'.$this->db.' :: '.$this->description.' @ Connection-Socket: '.$this->socket
				]);
				//--
			} //end if
			//--
			if(is_resource($this->socket)) {
				//--
				@stream_set_blocking($this->socket, true);
				@stream_set_timeout($this->socket, (int)SMART_FRAMEWORK_NETSOCKET_TIMEOUT);
				//--
				SmartFrameworkRegistry::$Connections['redis'][(string)$this->server.'@'.$this->db] = $this->socket; // export connection
				//--
				if((string)$this->password != '') {
					$this->run_command('AUTH', array($this->password)); // authenticate
				} //end if
				//--
				$this->run_command('SELECT', array($this->db)); // select database
				//--
				if(SmartFrameworkRuntime::ifDebug()) {
					//--
					SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
						'type' => 'set',
						'data' => 'Selected Database #'.$this->db,
						'skip-count' => 'yes'
					]);
					//--
				} //end if
				//--
			} //end if else
			//--
		} //end if else
		//--
		if(!is_resource($this->socket)) {
			$this->error('Redis Connect', 'ERROR: #'.$errno.' :: '.$errstr, 'Connection to Redis server: '.$this->server.'@'.$this->db); // non-fatal error, depends on how Redis class is setup
			return null;
		} //end if
		//--
	} //end if
	//--
	return $this->socket;
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * this is for disconnect from Redis
 *
 * @access 		private
 * @internal
 *
 */
private function disconnect() {
	//--
	if($this->socket) {
		//--
		@fclose($this->socket); // closing the local connection (the global might remain opened ...)
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			//--
			SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
				'type' => 'open-close',
				'data' => 'Redis DB :: Close Connection for: '.$this->server.'@'.$this->db.' on: '.$this->socket
			]);
			//--
		} //end if
		//--
	} //end if
	//--
} //END FUNCTION
//======================================================


//======================================================
/**
 * Displays the Redis Errors and HALT EXECUTION (This have to be a FATAL ERROR as it occur when a FATAL Redis ERROR happens or when Data Exchange fails)
 * PRIVATE
 *
 * @param BOOL $is_fatal :: TRUE / FALSE if the Error is Fatal or Not
 * @param STRING $y_area :: The Area
 * @param STRING $y_error_message :: The Error Message to Display
 * @param STRING $y_query :: The query
 * @param STRING $y_warning :: The Warning Title
 *
 * @return :: HALT EXECUTION WITH ERROR MESSAGE
 *
 */
private function error($is_fatal, $y_area, $y_error_message, $y_query='', $y_warning='') {
//--
$this->err = true; // required, to halt driver
//--
$is_fatal = (bool) $this->fatal_err;
//--
if($is_fatal === false) { // NON-FATAL ERROR
	//--
	if(SmartFrameworkRuntime::ifDebug()) {
		//--
		SmartFrameworkRegistry::setDebugMsg('db', 'redis|log', [
			'type' => 'metainfo',
			'data' => 'Redis SILENT WARNING: '.$y_area."\n".$y_query."\n".'Error-Message: '.$y_error_message."\n".'The settings for this Redis instance allow just silent warnings on connection fail.'."\n".'All next method calls to this Redis instance will be discarded silently ...'
		]);
		//--
	} //end if
	//--
	Smart::log_warning('#REDIS@'.$this->socket.' :: Q# // Redis :: WARNING :: '.$y_area."\n".'*** Error-Message: '.$y_error_message."\n".'*** Command:'."\n".$y_query);
	//--
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
	$the_params = '- '.$this->description.' -';
	$the_query_info = (string) $y_query;
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
	'Redis Client',
	'Redis',
	'Caching',
	'Server',
	'lib/core/img/db/redis-logo.svg',
	$width, // width
	$the_area, // area
	$the_error_message, // err msg
	$the_params, // title or params
	$the_query_info // command
);
//--
Smart::raise_error(
	'#REDIS@'.$this->socket.'# :: Q# // Redis Client :: ERROR :: '.$y_area."\n".'*** Error-Message: '.$y_error_message."\n".'*** Command:'."\n".$y_query,
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