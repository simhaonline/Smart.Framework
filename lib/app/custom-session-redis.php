<?php
// [LIB - SmartFramework / Redis Custom Session]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// OPTIONAL ; [REGEX-SAFE-OK]

//--
if(!is_array($configs['redis'])) {
	Smart::raise_error(
		'ERROR: Redis Custom Session requires the Redis server Configuration to be set in SmartFramework ...',
		'ERROR: Invalid Settings for App Session Handler. See the Error Log for more details ...'
	);
	die('');
} //end if
//--

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
	define('SMART_FRAMEWORK__INFO__CUSTOM_SESSION_ADAPTER', 'Redis: Memory based');
} //end if

/**
 * Class Smart.Framework App.Custom.Session.Redis
 * NOTICE: If Session is set to expire 0 (when browser is closed, in redis the session will expire at session.gc_maxlifetime seconds ...)
 *
 * @access 		private
 * @internal
 *
 */
final class SmartCustomSession extends SmartAbstractCustomSession {

	// ->
	// v.180411
	// Redis Custom Session [OPTIONAL]
	// NOTICE: This object MUST NOT CONTAIN OTHER FUNCTIONS BECAUSE WILL NOT WORK !!!


	//-- PUBLIC VARS
	public $sess_area;
	public $sess_ns;
	public $sess_expire;
	//--
	private $redis;
	//--


	//==================================================
	public function open() {
		//--
		if((defined('SMART_SOFTWARE_MEMDB_FATAL_ERR')) AND (SMART_SOFTWARE_MEMDB_FATAL_ERR === true)) {
			$is_fatal_err = false;
		} else {
			$is_fatal_err = true; // default
		} //end if
		//--
		$redis_cfg = (array) Smart::get_from_config('redis');
		//--
		$this->redis = new SmartRedisDb(
			(string) $redis_cfg['server-host'],
			(string) $redis_cfg['server-port'],
			(string) $redis_cfg['dbnum'],
			(string) $redis_cfg['password'],
			(string) $redis_cfg['timeout'],
			(string) $redis_cfg['slowtime'],
			'SmartCustomSession',
			(bool)   $is_fatal_err
		);
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	public function close() {
		//--
		$this->redis = null;
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	public function write($id, $data) {
		//--
		$key = (string) SmartPersistentCache::safeKey((string)$this->sess_area).':'.SmartPersistentCache::safeKey($id.'_'.$this->sess_ns);
		//--
		$result = $this->redis->set((string)$key, (string)$data);
		//--
		if(strtoupper(trim($result)) != 'OK') {
			Smart::log_warning('Redis Custom Session: Failed to write ...');
			return false;
		} //end if
		//--
		if((int)$this->sess_expire > 0) {
			$expire = (int) $this->sess_expire;
		} else {
			$expire = (int) ini_get('session.gc_maxlifetime');
			if($expire <= 0) {
				$expire = (int) 60 * 60; // default to 1 hour (in redis expire zero means no expire ...)
			} //end if
		} //end if
		//--
		$result = $this->redis->expire((string)$key, (int)$expire);
		//--
		return true; // don't throw if redis error !
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	public function read($id) {
		//--
		$key = (string) SmartPersistentCache::safeKey((string)$this->sess_area).':'.SmartPersistentCache::safeKey($id.'_'.$this->sess_ns);
		//--
		$data = $this->redis->get((string)$key);
		//--
		if(!is_string($data)) {
			$data = ''; // if key does not exists it returns null
		} //end if
		//--
		return (string) $data;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	public function destroy($id) {
		//--
		$key = (string) SmartPersistentCache::safeKey((string)$this->sess_area).':'.SmartPersistentCache::safeKey($id.'_'.$this->sess_ns);
		//--
		$ok = $this->redis->del((string)$key);
		//--
		if($ok <= 0) {
			Smart::log_warning('Redis Custom Session: Failed to destroy ...');
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION
	//==================================================


	//==================================================
	// TO BE EXTENDED
	public function gc($lifetime) {
		//--
		return true; // for Redis the Keys are Expiring with set in Write, so GC will not make use here ...
		//--
	} //END FUNCTION
	//==================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>