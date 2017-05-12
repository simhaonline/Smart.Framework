<?php
// SmartFramework / Abstract Middleware
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
//####################


// requires: SMART_FRAMEWORK_RELEASE_MIDDLEWARE


//==================================================================================
//================================================================================== CLASS START
//==================================================================================

// [REGEX-SAFE-OK]

/**
 * Class Smart.Framework Abstract Middleware
 *
 * It must contain ONLY public functions to avoid late state binding (self:: vs static::)
 *
 * @access 		private
 * @internal
 * @ignore		THIS CLASS IS FOR INTERNAL USE ONLY BY SMART-FRAMEWORK.RUNTIME !!!
 *
 * @version		170420
 *
 */
abstract class SmartAbstractAppMiddleware {

	// :: ABSTRACT
	// {{{SYNC-SMART-HTTP-STATUS-CODES}}}


//=====
public static function Run() {
	// THIS MUST IMPLEMENT THE MIDDLEWARE SERVICE HANDLER
} //END FUNCTION
//=====


//======================================================================
final public static function HeadersNoCache() {
	//--
	if(!headers_sent()) {
		header('Cache-Control: no-cache'); // HTTP 1.1
		header('Pragma: no-cache'); // HTTP 1.0
		header('Expires: '.gmdate('D, d M Y', @strtotime('-1 year')).' 09:05:00 GMT'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	} else {
		Smart::log_warning('WARNING: Smart App Runtime :: Could not set No-Cache Headers, Headers Already Sent ...');
	} //end if else
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function HeadersCacheExpire($expiration, $modified=0) {
	//--
	if(!headers_sent()) {
		//--
		$expiration = (int) $expiration; // expire time, in seconds, since now
		if($expiration < 60) {
			$expiration = 60;
		} //end if
		$expires = (int) time() + $expiration;
		//--
		$modified = (int) $modified; // last modification timestamp of the contents, in seconds, must be > 0 <= now
		if(($modified <= 0) OR ($modified > time())) {
			$modified = (int) time();
		} //end if
		//--
		header('Expires: '.gmdate('D, d M Y H:i:s', (int)$expires).' GMT'); // HTTP 1.0
		header('Pragma: cache'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', (int)$modified).' GMT');
		header('Cache-Control: private, max-age='.(int)$expiration); // HTTP 1.1 (private will dissalow proxies to cache the content)
		//--
	} else {
		//--
		Smart::log_warning('WARNING: Smart App Runtime :: Could not set Expire Cache Headers, Headers Already Sent ...');
		//--
	} //end if else
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise400Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(400);
	} else {
		Smart::log_warning('Headers Already Sent before 400 ...');
	} //end if else
	die(SmartComponents::http_message_400_badrequest((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise401Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(401);
	} else {
		Smart::log_warning('Headers Already Sent before 401 ...');
	} //end if else
	die(SmartComponents::http_message_401_unauthorized((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise403Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(403);
	} else {
		Smart::log_warning('Headers Already Sent before 403 ...');
	} //end if else
	die(SmartComponents::http_message_403_forbidden((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise404Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(404);
	} else {
		Smart::log_warning('Headers Already Sent before 404 ...');
	} //end if else
	die(SmartComponents::http_message_404_notfound((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise429Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(429);
	} else {
		Smart::log_warning('Headers Already Sent before 429 ...');
	} //end if else
	die(SmartComponents::http_message_429_toomanyrequests((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise500Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(500);
	} else {
		Smart::log_warning('Headers Already Sent before 500 ...');
	} //end if else
	die(SmartComponents::http_message_500_internalerror((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise502Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(502);
	} else {
		Smart::log_warning('Headers Already Sent before 502 ...');
	} //end if else
	die(SmartComponents::http_message_502_badgateway((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise503Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(503);
	} else {
		Smart::log_warning('Headers Already Sent before 503 ...');
	} //end if else
	die(SmartComponents::http_message_503_serviceunavailable((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
final public static function Raise504Error($y_msg) {
	//--
	if(!headers_sent()) {
		http_response_code(504);
	} else {
		Smart::log_warning('Headers Already Sent before 504 ...');
	} //end if else
	die(SmartComponents::http_message_504_gatewaytimeout((string)$y_msg));
	//--
} //END FUNCTION
//======================================================================


//======================================================================
// This will handle the file downloads. The file PACKET will be sent to this function.
// The PACKET (containing the File Download URL) is a data packet that have a structure like (see below: PACKET-STRUCTURE).
// All PACKETS are signed with an AccessKey based on a unique key SMART_FRAMEWORK_SECURITY_KEY, so they cant't be guessed or reversed.
// Event in the case that the AccessKey could be guessed, there is a two factor security layer that contains another key: UniqueKey (the unique client key, generated by the IP address and the unique browser signature).
// So the two factor security combination (secret server key: AccessKey based on SMART_FRAMEWORK_SECURITY_KEY / almost unique client key: UniqueKey) will assure enough protection.
// when used, the execution script must die('') after to avoid injections of extra content ...
// the nocache headers must be set before using this
// it returns the downloaded file path on success or empty string on error.
final public static function DownloadsHandler($encrypted_download_pack, $controller_key) {
	//--
	$encrypted_download_pack = (string) $encrypted_download_pack;
	$controller_key = (string) $controller_key;
	//--
	$client_signature = SmartUtils::get_visitor_signature();
	//--
	if((string)SMART_APP_VISITOR_COOKIE == '') {
		Smart::log_info('File Download', 'Failed: 400 / Invalid Visitor Cookie'.' on Client: '.$client_signature);
		self::Raise400Error('ERROR: Invalid Visitor UUID. Cookies must be enabled to enable this feature !');
		return '';
	} //end if
	//--
	$downloaded_file = ''; // init
	//--
	$decoded_download_packet = (string) trim((string)SmartUtils::crypto_decrypt(
		(string)$encrypted_download_pack,
		'SmartFramework//DownloadLink'.SMART_FRAMEWORK_SECURITY_KEY
	));
	//--
	if((string)$decoded_download_packet != '') { // if data is corrupted, decrypt checksum does not match, will return an empty string
		//--
		if(SMART_FRAMEWORK_ADMIN_AREA === true) { // {{{SYNC-DWN-CTRL-PREFIX}}}
			$controller_key = (string) 'AdminArea/'.$controller_key;
		} else {
			$controller_key = (string) 'IndexArea/'.$controller_key;
		} //end if
		//-- {{{SYNC-DOWNLOAD-ENCRYPT-ARR}}}
		$arr_metadata = explode("\n", (string)$decoded_download_packet, 6); // only need first 5 parts
		//print_r($arr_metadata);
		// #PACKET-STRUCTURE# [we will have an array like below, according with the: SmartUtils::create_download_link()]
		// [TimedAccess]\n
		// [FilePath]\n
		// [AccessKey]\n
		// [UniqueKey]\n
		// [SFR.UA]\n
		// #END#
		//--
		$crrtime = (string) trim((string)$arr_metadata[0]);
		$filepath = (string) trim((string)$arr_metadata[1]);
		$access_key = (string) trim((string)$arr_metadata[2]);
		$unique_key = (string) trim((string)$arr_metadata[3]);
		//--
		unset($arr_metadata);
		//--
		$timed_hours = 1; // default expire in 1 hour
		if(defined('SMART_FRAMEWORK_DOWNLOAD_EXPIRE')) {
			if((int)SMART_FRAMEWORK_DOWNLOAD_EXPIRE > 0) {
				if((int)SMART_FRAMEWORK_DOWNLOAD_EXPIRE <= 24) { // max is 24 hours (since download link is bind to unique browser signature + unique cookie ... make non-sense to keep more)
					$timed_hours = (int) SMART_FRAMEWORK_DOWNLOAD_EXPIRE;
				} //end if
			} //end if
		} //end if
		//--
		if((int)$timed_hours > 0) {
			if((int)$crrtime < (int)(time() - (60 * 60 * $timed_hours))) {
				Smart::log_info('File Download', 'Failed: 403 / Download expired at: '.date('Y-m-d H:i:s O', (int)$crrtime).' for: '.$filepath.' on Client: '.$client_signature);
				self::Raise403Error('ERROR: The Access Key for this Download is Expired !');
				return '';
			} //end if
		} //end if
		//--
		if((string)$access_key != (string)sha1('DownloadLink:'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.SMART_APP_VISITOR_COOKIE.':'.$filepath.'^'.$controller_key)) {
			Smart::log_info('File Download', 'Failed: 403 / Invalid Access Key for: '.$filepath.' on Client: '.$client_signature);
			self::Raise403Error('ERROR: Invalid Access Key for this Download !');
			return '';
		} //end if
		//--
		if((string)$unique_key != (string)SmartHashCrypto::sha1('Time='.$crrtime.'#'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.$access_key.'-'.SmartUtils::unique_auth_client_private_key().':'.$filepath.'+'.$controller_key)) {
			Smart::log_info('File Download', 'Failed: 403 / Invalid Client (Unique) Key for: '.$filepath.' on Client: '.$client_signature);
			self::Raise403Error('ERROR: Invalid Client Key to Access this Download !');
			return '';
		} //end if
		//--
		if(SmartFileSysUtils::check_file_or_dir_name($filepath)) {
			//--
			$skip_log = 'no'; // default log
			if(defined('SMART_FRAMEWORK_DOWNLOAD_SKIP_LOG')) {
				$skip_log = 'yes'; // do not log if accessed via admin area and user is authenticated
			} //end if
			//--
			$tmp_file_ext = (string) strtolower(SmartFileSysUtils::get_file_extension_from_path($filepath)); // [OK]
			$tmp_file_name = (string) strtolower(SmartFileSysUtils::get_file_name_from_path($filepath));
			//--
			$tmp_eval = SmartFileSysUtils::mime_eval($tmp_file_name);
			$mime_type = (string) $tmp_eval[0];
			$mime_disp = (string) $tmp_eval[1];
			//-- the path must not start with / but this is tested below
			$tmp_arr_paths = (array) explode('/', $filepath, 2); // only need 1st part for testing
			//-- allow file downloads just from specific folders like wpub/ or wsys/ (this is a very important security fix to dissalow any downloads that are not in the specific folders)
			if((substr((string)$filepath, 0, 1) != '/') AND (strpos((string)SMART_FRAMEWORK_DOWNLOAD_FOLDERS, '<'.trim((string)$tmp_arr_paths[0]).'>') !== false) AND (stripos((string)SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS, '<'.$tmp_file_ext.'>') === false)) {
				//--
				SmartFileSysUtils::raise_error_if_unsafe_path($filepath); // re-test finally
				//--
				@clearstatcache();
				//--
				if(is_file($filepath)) {
					//--
					if(!headers_sent()) {
						//--
						$fp = @fopen($filepath, 'rb');
						$fsize = @filesize($filepath);
						//--
						if((!$fp) || ($fsize <= 0)) {
							//--
							Smart::log_info('File Download', 'Failed: 404 / The requested File is Empty or Not Readable: '.$filepath.' on Client: '.$client_signature);
							self::Raise404Error('WARNING: The requested File is Empty or Not Readable !');
							return '';
							//--
						} //end if
						//-- set max execution time to zero
						ini_set('max_execution_time', 0); // we can expect a long time if file is big, but this will be anyway overriden by the WebServer Timeout Directive
						//--
						// cache headers are presumed to be sent by runtime before of this step
						//--
						header('Content-Type: '.$mime_type);
						header('Content-Disposition: '.$mime_disp);
						header('Content-Length: '.$fsize);
						//--
						@fpassthru($fp); // output without reading all in memory
						//--
						@fclose($fp);
						//--
					} else {
						//--
						Smart::log_info('File Download', 'Failed: 500 / Headers Already Sent: '.$filepath.' on Client: '.$client_signature);
						self::Raise500Error('ERROR: Download Failed, Headers Already Sent !');
						return '';
						//--
					} //end if else
					//--
					if((string)$skip_log != 'yes') {
						//--
						$downloaded_file = (string) $filepath; // return the file name to be logged
						//--
					} //end if
					//--
				} else {
					//--
					Smart::log_info('File Download', 'Failed: 404 / The requested File does not Exists: '.$filepath.' on Client: '.$client_signature);
					self::Raise404Error('WARNING: The requested File for Download does not Exists !');
					return '';
					//--
				} //end if else
			} else {
				//--
				Smart::log_info('File Download', 'Failed: 403 / Access to this File is Denied: '.$filepath.' on Client: '.$client_signature);
				self::Raise403Error('ERROR: Download Access to this File is Denied !');
				return '';
				//--
			} //end if else
			//--
		} else {
			//--
			Smart::log_info('File Download', 'Failed: 400 / Unsafe File Path: '.$filepath.' on Client: '.$client_signature);
			self::Raise400Error('ERROR: Unsafe Download File Path !');
			return '';
			//--
		} //end if else
		//--
	} else {
		//--
		Smart::log_info('File Download', 'Failed: 400 / Invalid Data Packet'.' on Client: '.$client_signature);
		self::Raise400Error('ERROR: Invalid Download Data Packet !');
		return '';
		//--
	} //end if else
	//--
	return (string) $downloaded_file;
	//--
} //END FUNCTION
//======================================================================


} //END CLASS


//==================================================================================
//================================================================================== CLASS END
//==================================================================================


// end of php code
?>