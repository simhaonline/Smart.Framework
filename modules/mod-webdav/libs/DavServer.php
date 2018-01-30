<?php
// Module Lib: \SmartModExtLib\Webdav\DavServer

namespace SmartModExtLib\Webdav;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


final class DavServer {

	// ::
	// v.180130

	const DAV_RESOURCE_TYPE_NONCOLLECTION = 'noncollection';
	const DAV_RESOURCE_TYPE_COLLECTION = 'collection';

	private static $httpRequestHeaders = null; // must init to null
	private static $httpRequestBody = null; // must init to null

	private static $tpl_path = 'modules/mod-webdav/libs/templates/'; // trailing slash req.


	public static function getTplPath() {
		//--
		return (string) self::$tpl_path;
		//--
	} //END FUNCTION


	// used to extract path from headers like MOVE ...
	public static function extractPathFromCurrentURL($url, $urldecode=false) { // sync with SmartFrameworkRuntime::Parse_Semantic_URL()
		//--
		$base_url = (string) \SmartUtils::get_server_current_url().\SmartUtils::get_server_current_script();
		//--
		if(strpos((string)$url, $base_url) !== 0) {
			return ''; // URL must start with the current server base URL ; this is important to avoid wrong path extract if /~ occurs before php script !!!
		} //end if
		$url_path = substr($url, strlen($base_url));
		//--
		$sem_path_pos = strpos((string)$url_path, '/~');
		if($sem_path_pos !== false) {
			$path_url = (string) substr((string)$url_path, ($sem_path_pos + 2));
		} else {
			$path_url = '';
		} //end if
		//--
		if($urldecode === true) {
			$path_url = (string) rawurldecode($path_url);
		} //end if
		$path_url = (string) ltrim($path_url, '/');
		//--
		return (string) $path_url;
		//--
	} //END FUNCTION


	public static function answerLocked($dav_req_path, $dav_author, $http_status, $lock_depth, $lock_time, $lock_uuid) {
		//--
		$xml = (string) \SmartMarkersTemplating::render_file_template(
			self::$tpl_path.'answer-locked.mtpl.xml',
			[
				'DAV-METHOD' 		=> (string) 'LOCK',
				'DAV-REQ-PATH' 		=> (string) $dav_req_path,
				'DAV-AUTHOR' 		=> (string) $dav_author,
				'LOCK-DEPTH' 		=> (string) $lock_depth,
				'LOCK-TIME-SEC' 	=> (int)    $lock_time,
				'LOCK-UUID' 		=> (string) $lock_uuid,
			],
			'yes' // cache
		);
		//--
		if(headers_sent()) {
			\Smart::raise_error(
				__METHOD__.'() :: Request FAILED # Headers Already Sent'
			);
		} else {
				http_response_code((int)$http_status);
				header('Content-type: text/xml; charset="utf-8"');
				header('Content-length: '.strlen($xml));
				echo((string)$xml);
		} //end if else
		//--
	} //END FUNCTION


	public static function answerMultiStatus($dav_method, $dav_req_path, $is_root_path, $http_status, $dav_req_uri, $arr_items=[], $arr_quota=[]) {
		//--
		$http_status = (int) $http_status;
		if((int)$http_status != 207) {
			$http_status = 404;
			$arr_items = array();
		} //end if
		//--
		$sett_is_root = (bool) $is_root_path; // import first time
		$arr_items = (array) $arr_items;
		$item_arr = [];
		if(\Smart::array_size($arr_items) > 0) {
			foreach($arr_items as $key => $val) {
				if(\Smart::array_size($val) > 0) { // must check if array is non empty
					if((string)$val['dav-resource-type'] == (string)self::DAV_RESOURCE_TYPE_COLLECTION) {
						$val['dav-resource-type'] = (string) self::DAV_RESOURCE_TYPE_COLLECTION;
					} else {
						$val['dav-resource-type'] = (string) self::DAV_RESOURCE_TYPE_NONCOLLECTION;
					} //end if else
					$item_arr[] = (array) [
						'IS-ROOT' 			=> (string) ($sett_is_root ? 'yes' : 'no'),
						'DAV-RESOURCE-TYPE' => (string) $val['dav-resource-type'],
						'DAV-REQUEST-PATH' 	=> (string) $val['dav-request-path'],
						'DATE-CREATION' 	=> (string) gmdate('D, d M Y H:i:s O', (int)$val['date-creation-timestamp']),
						'DATE-MODIFIED' 	=> (string) gmdate('D, d M Y H:i:s O', (int)$val['date-modified-timestamp']),
						'SIZE-BYTES' 		=> (int)    $val['size-bytes'],
						'MIME-TYPE' 		=> (string) $val['mime-type'],
						'E-TAG' 			=> (string) $val['etag-hash']
					];
					$sett_is_root = false; // set to false after first usage
				} //end if
			} //end foreach
		} //end if
		//--
		if(\Smart::array_size($item_arr) <= 0) {
			$http_status = 404;
			$arr_items = array();
		} //end if
		//--
		$xml = (string) \SmartMarkersTemplating::render_file_template(
			self::$tpl_path.'answer-multistatus.mtpl.xml',
			[
				'DAV-METHOD' 		=> (string) $dav_method,
				'DAV-REQ-PATH' 		=> (string) $dav_req_path,
				'DAV-REQUEST-URI' 	=> (string) $dav_req_uri,
				'HTTP-STATUS' 		=> (int)    $http_status,
				'ITEM' 				=> (array) 	$item_arr,
				'QUOTA-USED' 		=> (int)    $arr_quota['used'],
				'QUOTA-FREE' 		=> (int)    $arr_quota['free']
			],
			'yes' // cache
		);
		//--
		if(headers_sent()) {
			\Smart::raise_error(
				__METHOD__.'() :: Request FAILED # Headers Already Sent'
			);
		} else {
				http_response_code((int)$http_status);
				header('Content-type: text/xml; charset="utf-8"');
				header('Content-length: '.strlen($xml));
				echo((string)$xml);
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Returns all (known) HTTP headers as Array or a specific Header as String if Name is non-empty.
	 *
	 * All headers are converted to lower-case, and additionally all underscores are automatically converted to dashes
	 *
	 * @return array / string
	 */
	public static function getRequestHeaders($name='') {
		//--
		$name = (string) trim((string)$name);
		//--
		if(self::$httpRequestHeaders === null) {
			//--
			self::$httpRequestHeaders = [];
			//--
			foreach((array)$_SERVER as $key => $value) {
				//--
				switch((string)strtoupper((string)$key)) {
					case 'CONTENT_LENGTH':
					case 'CONTENT_TYPE':
						self::$httpRequestHeaders[(string)strtolower((string)str_replace('_', '-', (string)$key))] = (string) $value;
						break;
					default :
						if(strpos((string)$key, 'HTTP_') === 0) {
							self::$httpRequestHeaders[(string)substr((string)strtolower((string)str_replace('_', '-', (string)$key)), 5)] = (string) $value;
						} //end if else
				} //end switch
			} //end foreach
			//--
		} //end if
		//--
		if((string)$name != '') {
			return (string) self::$httpRequestHeaders[(string)strtolower((string)str_replace('_', '-', (string)$name))];
		} else {
			return (array) self::$httpRequestHeaders;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * Returns the HTTP request body as string or stream
	 *
	 * @return string / resource
	 */
	public static function getRequestBody($get_as_stream=false) {
		//--
		if(self::$httpRequestBody === null) {
			if($get_as_stream === true) {
				self::$httpRequestBody = fopen('php://input', 'r'); // for large file puts this is essential to avoid memory overflow
			} else {
				self::$httpRequestBody = (string) file_get_contents('php://input');
			} //end if else
		} //end if
		//--
		return self::$httpRequestBody; // mixed: string / resource
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>