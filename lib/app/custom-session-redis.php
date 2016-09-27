<?php
// [LIB - SmartFramework / Redis Custom Session]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.7.2 r.2016.09.27 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// OPTIONAL

//--
if(Smart::array_size($configs['redis']) <= 0) {
	die('ERROR: Redis Custom Session requires the Redis server Configuration to be set in SmartFramework');
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
 *
 * @access 		private
 * @internal
 *
 */
final class SmartCustomSession extends SmartAbstractCustomSession {

	// ->
	// v.160827
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
	global $configs;
	//--
	if((defined('SMART_SOFTWARE_MEMDB_FATAL_ERR')) AND (SMART_SOFTWARE_MEMDB_FATAL_ERR === true)) {
		$ignore_conn_errs = false;
	} else {
		$ignore_conn_errs = true; // default
	} //end if
	//--
	$this->redis = new SmartRedisDb(
		$configs['redis']['server-host'],
		$configs['redis']['server-port'],
		$configs['redis']['dbnum'],
		$configs['redis']['password'],
		$configs['redis']['timeout'],
		$configs['redis']['slowtime'],
		'SmartCustomSession',
		$ignore_conn_errs
	);
	//--
} //END FUNCTION
//==================================================


//==================================================
public function close() {
	//--
	$this->redis = null;
	//--
} //END FUNCTION
//==================================================


//==================================================
public function write($id, $data) {
	//--
	$key = (string) 'smart-'.$this->sess_area.'-'.str_replace(':', '', $this->sess_ns).':'.str_replace(':', '-', $id);
	//--
	$result = $this->redis->set((string)$key, (string)$data);
	//--
	if(strtoupper(trim($result)) != 'OK') {
		Smart::log_warning('Redis Custom Session: Failed to write ...');
		return false;
	} //end if
	//--
	if((int)$this->sess_expire > 0) {
		$result = $this->redis->expire((string)$key, (int)$this->sess_expire);
	} //end if
	//--
	return true;
	//--
} //END FUNCTION
//==================================================


//==================================================
public function read($id) {
	//--
	$key = (string) $this->sess_area.':'.str_replace(':', '-', $id.'-'.$this->sess_ns);
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
	$key = (string) $this->sess_area.':'.str_replace(':', '-', $id.'-'.$this->sess_ns);
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