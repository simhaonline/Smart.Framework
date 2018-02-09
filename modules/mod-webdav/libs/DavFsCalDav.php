<?php
// Module Lib: \SmartModExtLib\Webdav\DavFsCalDav

namespace SmartModExtLib\Webdav;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


final class DavFsCalDav {

	// ::
	// v.180209

	private static $caldav_ns = 'xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/"';
	private static $caldav_urn = 'urn:ietf:params:xml:ns:caldav';
	private static $caldav_rep_data = ':calendar-data';
	private static $caldav_max_res_size = 1500000; // 1.5MB

	public static function methodOptions() { // 200 @ https://tools.ietf.org/html/rfc4791
		//--
		http_response_code(200);
		//--
		header('Date: '.date('D, d M Y H:i:s O'));
		header('Content-length: 0');
		header('MS-Author-Via: DAV'); // Microsoft clients are set default to the Frontpage protocol unless we tell them to use DAV
		header('DAV: 1, 2, calendar-access'); // don't support (LOCK / UNLOCK) as seen in sabreDAV 1.5.x
		header('Allow: OPTIONS, HEAD, GET, PROPFIND, REPORT, PUT, DELETE');
		header('Accept-Ranges: none');
		header('Z-Cloud-Service: CalDAV Server (iCalendar ics / events, tasks)');
		//--
		return 200;
		//--
	} //END FUNCTION


	public static function methodHead($dav_vfs_path) { // 200 | 404 | 415
		//--
		$dav_vfs_path = (string) $dav_vfs_path;
		//--
		header('Content-length: 0');
		//--
		if(!\SmartFileSystem::path_exists($dav_vfs_path)) {
			http_response_code(404);
			return 404;
		} //end if
		//--
		if(\SmartFileSystem::is_type_dir($dav_vfs_path)) {
			http_response_code(200);
			header('Content-Type: '.self::mimeTypeDir($dav_vfs_path)); // directory
		} elseif(\SmartFileSystem::is_type_file($dav_vfs_path)) {
			http_response_code(200);
			header('Content-Type: '.self::mimeTypeFile($dav_vfs_path));
			header('Content-Length: '.(int)\SmartFileSystem::get_file_size($dav_vfs_path));
		} else { // unknown media type
			http_response_code(415);
			return 415;
		} //end if else
		//--
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', (int)\SmartFileSystem::get_file_mtime($dav_vfs_path)).' GMT');
		return 200;
		//--
	} //END FUNCTION


	public static function methodPropfind($dav_uri, $dav_request_path, $dav_vfs_path, $dav_is_root_path, $dav_vfs_root) {
		//--
		$dav_method = 'PROPFIND';
		$dav_vfs_path = (string) $dav_vfs_path;
		//--
		header('Expires: '.gmdate('D, d M Y', @strtotime('-1 day')).' '.date('H:i:s').' GMT'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		//--
		if(\SmartFileSystem::is_type_file($dav_vfs_path)) { // file
			$statuscode = 207;
			\SmartModExtLib\Webdav\DavServer::answerMultiStatus(
				(string) self::$caldav_ns,
				(string) $dav_method,
				(string) $dav_request_path,
				(bool)   $dav_is_root_path,
				(int)    $statuscode,
				(string) $dav_uri,
				(array)  self::getItem($dav_uri, $dav_vfs_path)
			);
		} elseif(\SmartFileSystem::is_type_dir($dav_vfs_path)) { // dir
			$statuscode = 207;
			\SmartModExtLib\Webdav\DavServer::answerMultiStatus(
				(string) self::$caldav_ns,
				(string) $dav_method,
				(string) $dav_request_path,
				(bool)   $dav_is_root_path,
				(int)    $statuscode,
				(string) $dav_uri,
				(array)  self::getItem($dav_uri, $dav_vfs_path),
				(array)  self::getQuotaAndUsageInfo($dav_vfs_root)
			);
		} else { // not found
			$statuscode = 404;
			\SmartModExtLib\Webdav\DavServer::answerMultiStatus(
				(string) self::$caldav_ns,
				(string) $dav_method,
				(string) $dav_request_path,
				(bool)   $dav_is_root_path,
				(int)    $statuscode,
				(string) $dav_uri
			);
		} //end if else
		// \Smart::log_notice('iCal/Propfind: '.$dav_uri.' # '.$statuscode);
		//--
		return (int) $statuscode;
		//--
	} //END FUNCTION


	public static function methodPut($dav_vfs_path) { // 201 | 405 | 415 | 423 | 500
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_path)) {
			http_response_code(415); // unsupported media type
			return 415;
		} //end if
		//--
		$the_fname = (string) trim((string)\SmartFileSysUtils::get_file_name_from_path((string)$dav_vfs_path));
		if(((string)$the_fname == '') OR (substr($the_fname, 0, 1) == '.')) {
			http_response_code(415); // unsupported media type (empty or dot files not allowed)
			return 415;
		} //end if
		//--
		$the_ext = (string) strtolower(trim((string)\SmartFileSysUtils::get_file_extension_from_path((string)$dav_vfs_path)));
		if(!defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) {
			http_response_code(415); // unsupported media type
			return 415;
		} //end if
		if(stripos((string)SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS, '<'.$the_ext.'>') !== false) {
			http_response_code(415); // unsupported media type
			return 415;
		} //end if
		if(defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) {
			if(stripos((string)SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS, '<'.$the_ext.'>') === false) {
				http_response_code(415); // unsupported media type
				return 415;
			} //end if
		} //end if
		if((string)$the_ext != 'ics') {
			http_response_code(415); // unsupported media type ; allow just .ics !!!
			return 415;
		} //end if
		//--
		// NOTICE: enforcing lowercase file name fails with Thunderbird/Calendar
		//--
		$fp = \SmartModExtLib\Webdav\DavServer::getRequestBody(true); // get as resource stream
		if(!is_resource($fp)) {
			http_response_code(500); // internal server error
			return 500;
		} //end if
		//--
		if(\SmartFileSystem::is_type_dir($dav_vfs_path)) {
			http_response_code(405); // the destination exists and is a directory
			return 405;
		} //end if
		//--
		$oversized = false;
		$max_res_size = \Smart::format_number_int(self::$caldav_max_res_size,'+');
		$ics_data = '';
		while($data = fread($fp, 1024*8)) {
			$ics_data .= $data;
			if((int)strlen((string)$ics_data) > (int)$max_res_size) {
				$oversized = true;
				break;
			} //end if
		} //end while
		//--
		fclose($fp);
		//--
		if((string)trim((string)$ics_data) == '') {
			http_response_code(423); // locked: could not achieve fopen advisory lock
			return 423;
		} //end if
		if($oversized === true) {
			http_response_code(507); // not enough space (for oversized)
			return 507;
		} //end if
		//--
		if(!\SmartFileSystem::write((string)$dav_vfs_path, (string)$ics_data)) {
			http_response_code(423); // locked: could not achieve fopen advisory lock
			return 423;
		} //end if
		//--
		$ics_data = ''; // free mem
		//--
		http_response_code(201); // HTTP/1.1 201 Created
		header('Content-length: 0');
		header('ETag: "'.(string)md5_file((string)$dav_vfs_path).'"');
		return 201;
		//--
	} //END FUNCTION


	public static function methodDelete($dav_vfs_path) { // 204 | 405 | 415 | 423
		//--
		$dav_vfs_path = (string) $dav_vfs_path;
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_path)) {
			http_response_code(415); // unsupported media type
			return 415;
		} //end if
		//--
		if(\SmartFileSystem::path_exists($dav_vfs_path)) {
			if(\SmartFileSystem::is_type_dir($dav_vfs_path)) {
				\SmartFileSystem::dir_delete($dav_vfs_path, true);
			} elseif(\SmartFileSystem::is_type_file($dav_vfs_path)) {
				\SmartFileSystem::delete($dav_vfs_path);
			} else {
				http_response_code(405); // method not allowed: unknown resource type
				return 405;
			} //end if
		} //end if
		//--
		if(\SmartFileSystem::path_exists($dav_vfs_path)) {
			http_response_code(423); // locked: could not remove the resource, perhaps locked
			return 423;
		} //end if
		//--
		http_response_code(204); // HTTP/1.1 204 No Content
		header('Content-length: 0');
		return 204;
		//--
	} //END FUNCTION


	public static function methodGet($dav_method, $dav_author, $dav_url, $dav_request_path, $dav_vfs_path, $dav_is_root_path, $dav_vfs_root, $dav_request_back_path, $nfo_title, $nfo_signature, $nfo_prefix_crrpath, $nfo_lnk_welcome, $nfo_txt_welcome, $nfo_svg_logo) { // 200 | 404 | 405 | 415 | 423
		//--
		$dav_vfs_path = (string) $dav_vfs_path;
		$dav_vfs_root = (string) $dav_vfs_root;
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_path)) {
			http_response_code(415); // unsupported media type
			return 415;
		} //end if
		//--
		if(!\SmartFileSystem::path_exists($dav_vfs_path)) {
			http_response_code(404); // directories can't be get !
			return 404;
		} //end if
		//--
		if(!\SmartFileSystem::is_type_file($dav_vfs_path)) {
			//--
			$nfo_crrpath = (string) $nfo_prefix_crrpath.$dav_request_path;
			//--
			$bw = (array) \SmartUtils::get_os_browser_ip();
			//--
			if(!in_array((string)$bw['bw'], ['fox', 'crm', 'opr', 'sfr', 'iee', 'iex', 'eph', 'nsf'])) {
				http_response_code(405); // method not allowed: only files can be GET !
				return 405;
			} //end if
			//--
			http_response_code(200);
			$arr_quota = (array) self::getQuotaAndUsageInfo($dav_vfs_root);
			$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage($dav_vfs_path, false, false, '.ics'); // non-recuring
			$fixed_vfs_dir = (string) \SmartFileSysUtils::add_dir_last_slash($dav_vfs_path);
			$fixed_dav_url = (string) rtrim((string)$dav_url, '/').'/';
			$base_url = (string) \SmartUtils::get_server_current_url();
			$arr_f_dirs = array();
			for($i=0; $i<\Smart::array_size($files_n_dirs['list-dirs']); $i++) {
				$arr_f_dirs[] = [
					'name'  => (string) $files_n_dirs['list-dirs'][$i],
					'type'  => (string) self::mimeTypeDir((string)$fixed_vfs_dir.$files_n_dirs['list-dirs'][$i]),
					'size'  => '-',
					'modif' => (string) date('Y-m-d H:i:s O', (int)\SmartFileSystem::get_file_mtime($fixed_vfs_dir.$files_n_dirs['list-dirs'][$i])),
					'link'  => (string) $fixed_dav_url.$files_n_dirs['list-dirs'][$i]
				];
			} //end for
			$arr_f_files = array();
			for($i=0; $i<\Smart::array_size($files_n_dirs['list-files']); $i++) {
				$arr_f_files[] = [
					'name'  => (string) $files_n_dirs['list-files'][$i],
					'type'  => (string) self::mimeTypeFile((string)$files_n_dirs['list-files'][$i]),
					'size'  => (string) \SmartUtils::pretty_print_bytes((int)\SmartFileSystem::get_file_size($fixed_vfs_dir.$files_n_dirs['list-files'][$i]), 2, ' '),
					'modif' => (string) date('Y-m-d H:i:s O', (int)\SmartFileSystem::get_file_mtime($fixed_vfs_dir.$files_n_dirs['list-files'][$i])),
					'link'  => (string) $fixed_dav_url.$files_n_dirs['list-files'][$i]
				];
			} //end for
			$detect_dav_url_root = (array) explode('~', (string)$dav_url);
			if((string)trim((string)$detect_dav_url_root[0]) != '') {
				$detect_dav_url_back = (string) trim((string)$detect_dav_url_root[0]).'~/'.$dav_request_back_path;
			} else {
				$detect_dav_url_back = '';
			} //end if else
			$info_extensions_list = '';
			if((defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) AND ((string)trim((string)SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS) != '')) {
				$info_extensions_list = 'Allowed Extensions List: '.SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS;
			} else {
				$info_extensions_list = 'Disallowed Extensions List: '.SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS;
			} //end if else
			$info_restr_charset = 'restricted charset as [ _ a-z A-Z 0-9 - . @ ]';
			$html = (string) \SmartMarkersTemplating::render_file_template(
				\SmartModExtLib\Webdav\DavServer::getTplPath().'answer-get-path.mtpl.html',
				[
					'IMG-SVG-LOGO' 		=> (string) $nfo_svg_logo,
					'TEXT-WELCOME' 		=> (string) $nfo_txt_welcome,
					'LINK-WELCOME' 		=> (string) $nfo_lnk_welcome,
					'INFO-HEADING' 		=> (string) $nfo_title,
					'INFO-SIGNATURE' 	=> (string) $nfo_signature,
					'INFO-ROOT' 		=> (string) '{DAV:'.$dav_vfs_root.'}',
					'INFO-TITLE' 		=> (string) $nfo_signature.' - '.$nfo_title.' / '.$nfo_crrpath.' @ '.date('Y-m-d H:i:s O'),
					'INFO-AUTHNAME' 	=> (string) $dav_author,
					'INFO-VERSION' 		=> (string) SMART_FRAMEWORK_RELEASE_TAGVERSION.'-'.SMART_FRAMEWORK_RELEASE_VERSION,
					'CRR-PATH' 			=> (string) $nfo_crrpath,
					'NUM-CRR-DIRS' 		=> (int)    $files_n_dirs['dirs'],
					'NUM-CRR-FILES' 	=> (int)    $files_n_dirs['files'],
					'QUOTA-USED' 		=> (string) \SmartUtils::pretty_print_bytes((int)$arr_quota['used'], 0, ''),
					'QUOTA-FREE' 		=> (string) \SmartUtils::pretty_print_bytes((int)$arr_quota['free'], 0, ''),
					'QUOTA-SPACE' 		=> (string) ((int)$arr_quota['quota'] ? \SmartUtils::pretty_print_bytes((int)$arr_quota['quota'], 0, '') : 'NOLIMIT'),
					'NUM-DIRS' 			=> (int)    $arr_quota['num-dirs'],
					'NUM-FILES' 		=> (int)    $arr_quota['num-files'],
					'LIST-DIRS' 		=> (array)  $arr_f_dirs,
					'LIST-FILES' 		=> (array)  $arr_f_files,
					'BASE-URL' 			=> (string) $base_url,
					'IS-ROOT' 			=> (string) ($dav_is_root_path ? 'yes' : 'no'),
					'BACK-PATH' 		=> (string) $detect_dav_url_back,
					'DISPLAY-QUOTA' 	=> (string) (defined('SMART_WEBDAV_SHOW_USAGE_QUOTA') AND (SMART_WEBDAV_SHOW_USAGE_QUOTA === true)) ? 'yes' : 'no',
					'DIR-NEW-INFO' 		=> (string) 'INFO: Directory Creation is dissalowed ...', // TODO: add support to create new calendars ...
					'MAX-UPLOAD-INFO' 	=> (string) 'INFO: Direct Files Uploads is dissalowed ...', // TODO: add support to upload validated ICSs only
					'SHOW-POST-FORM' 	=> 'no'
				],
				'yes' // cache
			);
			echo (string) $html;
			return 200;
		} elseif((string)$dav_method == 'POST') { // POST to a file is not allowed
			http_response_code(405); // method not allowed: only dirs can be POST !
			return 405;
		} //end if
		//--
		if(!\SmartFileSystem::have_access_read($dav_vfs_path)) {
			http_response_code(423); // locked: file is not accessible
			return 423;
		} //end if
		//--
		http_response_code(200); // HTTP/1.1 200 OK
		header('Expires: '.gmdate('D, d M Y', @strtotime('-1 day')).' '.date('H:i:s').' GMT'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-length: '.(int)\SmartFileSystem::get_file_size($dav_vfs_path));
		header('Content-Type: '.(string)self::mimeTypeFile($dav_vfs_path));
		readfile($dav_vfs_path);
		return 200;
		//--
	} //END FUNCTION


	public static function methodReport($dav_uri, $dav_request_path, $dav_vfs_path, $dav_is_root_path, $dav_vfs_root) {
		//--
		$dav_method = 'REPORT';
		//--
		// Method REPORT is for serving multiple files in one request
		// It should only be done over a directory that contains some files to serve
		// For CalDAV will report just for: Calendar/*.ics
		// The REPORT method is very complex, and cover many situations, but the main purpose is just to serve many files at once ...
		// For a simple implementation of REPORT we validate the request to be as:
		//	1. Have a Valid XML Body
		// 	2. The XML Body should contain CalDAV signature (self::$caldav_urn) and a request for CalDAV Files (self::$caldav_rep_data)
		// 	3. Idea: we don't take care if is calendar-multiget or calendar-query because is too complex to handle and we have not the intention to implement real query ...
		// 	4. Because of the above exposed Idea, to make it simple, if the request contain an xml body from which we can parse and extract some links to the requested calendar ics files, that OK and we serve back just those files (is important to serve back all requested files to inform if some files are 404 as deleted meanwhile by another client instance !!) ; if the parsed array is empty (no content or parsing errors, we serve back all files from calendar !!)
		//	5. If the Request path is not a calendar folder or a file, serve back an error 400
		//--
		if(\SmartFileSystem::is_type_file((string)$dav_vfs_path)) {
			// \Smart::log_notice('CalDAV REPORT Method called for a file type, which is not supported ...');
			http_response_code(400); // bad request
			return 400;
		} //end if
		if(!\SmartFileSystem::is_type_dir((string)$dav_vfs_path)) {
			// \Smart::log_notice('CalDAV REPORT Method called for a non-existing folder ...');
			http_response_code(400); // bad request
			return 400;
		} //end if
		if((int)self::testIsCalendarCollection((string)$dav_vfs_path) !== 1) {
			// \Smart::log_notice('CalDAV REPORT Method called for a non-calendar folder, which is not supported ...');
			http_response_code(400); // bad request
			return 400;
		} //end if
		//--
		$heads = (array) \SmartModExtLib\Webdav\DavServer::getRequestHeaders();
		// \Smart::log_notice(print_r($heads,1));
		$body = (string) \SmartModExtLib\Webdav\DavServer::getRequestBody();
		// \Smart::log_notice(print_r($body,1));
		//-- OR (stripos((string)$body, ':calendar-multiget ') === false)
		$arr = array();
		if((string)trim((string)$body) != '') { // test only if non-empty body, otherwise suppose it requested for all files
			if((stripos((string)$body, (string)self::$caldav_urn) === false) OR (strpos((string)$body, (string)self::$caldav_rep_data) === false)) {
				// \Smart::log_notice('CalDAV REPORT is invalid: '.$body);
				http_response_code(400); // bad request
				return 400;
			} //end if
			$arr = (array) \SmartModExtLib\Webdav\DavServer::parseXMLBody((string)$body, '', 'href');
		} //end if
		//--
		$files = [];
		if(\Smart::array_size($arr) > 0) { // if successfuly extracted some links from the body
			for($i=0; $i<\Smart::array_size($arr); $i++) {
				$link = (string) trim((string)$arr[$i]);
				if(((string)$link != '') AND ((string)strtolower((string)substr((string)$link, -4, 4)) == '.ics')) { // don't test for safe path as this is the server full url path and may not be compliant with this check
					$link = (string) \SmartFileSysUtils::get_file_name_from_path((string)$link);
					$link = (string) trim((string)$link);
					if(((string)$link != '') AND ((string)strtolower((string)substr((string)$link, -4, 4)) == '.ics') AND (\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$link) == '1')) { // but the file must be safe compliant
						$files[] = (string) $link;
					} //end if
				} //end if
			} //end for
		} //end if
		$arr = array();
		//--
		//$dbg_data = 'Parsed-XML for URI: ';
		if(\Smart::array_size($files) <= 0) { // if no ics files found in request, serve them all
			// \Smart::log_notice('CalDAV REPORT contain no HREFs or could not parse the body: '."\n".$body);
			//$dbg_data = 'Parsed-XML Empty for URI: ';
			$arr_list_ics = (array) (new \SmartGetFileSystem(true))->get_storage((string)$dav_vfs_path, false, false, '.ics');
			$files = array();
			$files = (array) $arr_list_ics['list-files'];
			$arr_list_ics = array();
		} //end if
		// \Smart::log_notice($dbg_data.' <'.$dav_uri.'> ('.$dav_request_path.') ['.$dav_vfs_path.']:'."\n".print_r($files,1));
		//--
		$arr = array();
	//	$arr[] = (array) self::getItemTypeCollection($dav_uri, $dav_vfs_path); // add the folder tho this request # THIS MUST NOT BE SET IN REPORT !!!
		$arr = self::addSubItem($dav_uri, $dav_vfs_path, $arr, $files, 'files', true);
		//--
		$statuscode = 207;
		//ob_start();
		\SmartModExtLib\Webdav\DavServer::answerMultiStatus(
			(string) self::$caldav_ns,
			(string) $dav_method,
			(string) $dav_request_path,
			(bool)   $dav_is_root_path,
			(int)    $statuscode,
			(string) $dav_uri,
			(array)  $arr,
			(array)  self::getQuotaAndUsageInfo($dav_vfs_root)
		);
		//$tst = ob_get_contents();
		//ob_end_clean();
		//echo $tst;
		// \Smart::log_notice('HEADERS:['.http_response_code().']'."\n".print_r(headers_list(),1));
		// \Smart::log_notice('REPORT:'."\n".$tst);
		//--
		return (int) $statuscode;
		//--
	} //END FUNCTION


	//#####


	private static function getQuotaAndUsageInfo($dav_vfs_root) {
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_root)) {
			return array();
		} //end if
		//--
		if((!defined('SMART_WEBDAV_SHOW_USAGE_QUOTA')) OR (SMART_WEBDAV_SHOW_USAGE_QUOTA !== true)) {
			return array(); // skip quota info if not express specified
		} //end if
		//--
		$arr_storage = (new \SmartGetFileSystem())->get_storage((string)$dav_vfs_root); // recuring
		// \Smart::log_notice(print_r($arr_storage,1));
		$used_space = (int) $arr_storage['size-files']; // 'size'
		$free_space = (int) floor(disk_free_space((string)$dav_vfs_root));
		//--
		return array(
			'root-dir' 		=> (string) $dav_vfs_root, 		// vfs root dir
			'quota' 		=> (int) $arr_storage['quota'], // total quota (0 is unlimited)
			'used' 			=> (int) $used_space, 			// used space (total - free) in bytes,
			'free' 			=> (int) $free_space, 			// free space (free) in bytes,
			'num-dirs' 		=> (int) $arr_storage['dirs'], 	// # dirs
			'num-files' 	=> (int) $arr_storage['files'] 	// # files
		);
		//--
	} //END FUNCTION


	private static function getItem($dav_request_path, $dav_vfs_path) {
		//--
		$dav_request_path = (string) trim((string)$dav_request_path);
		$dav_vfs_path = (string) trim((string)$dav_vfs_path);
		//--
		if(((string)$dav_request_path == '') OR ((string)$dav_vfs_path == '')) {
			return array();
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_path)) {
			return array();
		} //end if
		//--
		$arr = array();
		//--
		if(\SmartFileSystem::is_type_file($dav_vfs_path)) {
			$arr[] = (array) self::getItemTypeNonCollection($dav_request_path, $dav_vfs_path);
		} elseif(\SmartFileSystem::is_type_dir($dav_vfs_path)) {
			$arr[] = (array) self::getItemTypeCollection($dav_request_path, $dav_vfs_path);
			$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage($dav_vfs_path, false, false, '.ics'); // non-recuring
			//print_r($files_n_dirs); die();
			//print_r($arr); die();
			$arr = self::addSubItem($dav_request_path, $dav_vfs_path, $arr, $files_n_dirs['list-dirs'], 'dirs');
			$arr = self::addSubItem($dav_request_path, $dav_vfs_path, $arr, $files_n_dirs['list-files'], 'files');
			//print_r($arr); die();
		} //end if else
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	private static function testIsCalendarCollection($dav_vfs_path) {
		//--
		if(defined('SMART_WEBDAV_CALDAV_ICAL_PATH')) {
			if(strpos((string)$dav_vfs_path, (string)SMART_WEBDAV_CALDAV_ICAL_PATH) === 0) {
				return 1; // calendar
			} //end if
		} //end if
		//--
		if(defined('SMART_WEBDAV_CALDAV_ACC_PATH')) {
			if(strpos((string)$dav_vfs_path, (string)SMART_WEBDAV_CALDAV_ACC_PATH) === 0) {
				return 2; // account
			} //end if
		} //end if
		//--
		return 0;
		//--
	} //end if


	private static function mimeTypeDir($dav_vfs_path) {
		//--
		switch((int)self::testIsCalendarCollection($dav_vfs_path)) {
			case 1:
				$type = 'Collection, Calendar';
				break;
			case 2:
				$type = 'Collection, Account';
				break;
			default:
				$type = 'Collection';
		} //end if
		//--
		return (string) $type;
		//--
	} //END FUNCTION


	private static function mimeTypeFile($dav_vfs_path) {
		//--
		$dav_vfs_path = (string) $dav_vfs_path;
		//--
		return (string) \SmartFileSysUtils::mime_eval($dav_vfs_path, false);
		//--
	} //END FUNCTION


	private static function addSubItem($dav_request_path, $dav_vfs_path, $arr, $subitems, $type, $add_data_fcontent=false) {
		//--
		$arr = (array) $arr;
		$subitems = (array) $subitems;
		//--
		if(\Smart::array_size($subitems) > 0) {
			for($i=0; $i<\Smart::array_size($subitems); $i++) {
				if(\SmartFileSysUtils::check_if_safe_file_or_dir_name($subitems[$i])) {
					if(\SmartFileSysUtils::check_if_safe_path($subitems[$i])) { // must check this to dissalow # and . protected paths
						$tmp_new_req_path = (string) rtrim((string)$dav_request_path, '/').'/'.$subitems[$i];
						$tmp_new_vfs_path = (string) \SmartFileSysUtils::add_dir_last_slash((string)$dav_vfs_path).$subitems[$i];
						if(\SmartFileSysUtils::check_if_safe_path($tmp_new_vfs_path)) {
							if(((string)$type == 'dirs') AND (\SmartFileSystem::is_type_dir($tmp_new_vfs_path))) {
								$tmp_new_arr = (array) self::getItemTypeCollection(
									(string) $tmp_new_req_path,
									(string) $tmp_new_vfs_path
								);
							} elseif(((string)$type == 'files') AND (\SmartFileSystem::is_type_file($tmp_new_vfs_path))) {
								$tmp_new_arr = (array) self::getItemTypeNonCollection(
									(string) $tmp_new_req_path,
									(string) $tmp_new_vfs_path
								);
								if($add_data_fcontent === true) {
									if(\Smart::array_size($tmp_new_arr) > 0) {
										$tmp_new_arr['c-xml-data'] = '<cal:calendar-data>'.\Smart::escape_html(\SmartFileSystem::read($tmp_new_vfs_path)).'</cal:calendar-data>';
									} //end if
								} //end if
							} //end if else
							if(\Smart::array_size($tmp_new_arr) > 0) {
								$arr[] = (array) $tmp_new_arr;
							} //end if
						} //end if
					} //end if
				} //end if
			} //end for
		} //end if
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	private static function getItemTypeNonCollection($dav_request_path, $dav_vfs_path) {
		//--
		$dav_request_path = (string) trim((string)$dav_request_path);
		$dav_vfs_path = (string) trim((string)$dav_vfs_path);
		//--
		if(((string)$dav_request_path == '') OR ((string)$dav_vfs_path == '')) {
			return array();
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_path)) {
			return array();
		} //end if
		if(!\SmartFileSystem::is_type_file($dav_vfs_path)) {
			return array();
		} //end if
		//--
		return (array) [
			'dav-resource-type' 		=> (string) \SmartModExtLib\Webdav\DavServer::DAV_RESOURCE_TYPE_NONCOLLECTION,
			'dav-request-path' 			=> (string) $dav_request_path,
			'dav-vfs-path' 				=> (string) $dav_vfs_path, // private
			'date-creation-timestamp' 	=> (int) 	0, // \SmartFileSystem::get_file_ctime($dav_vfs_path), // currently is unused
			'date-modified-timestamp' 	=> (int) 	\SmartFileSystem::get_file_mtime($dav_vfs_path),
			'size-bytes' 				=> (int)    \SmartFileSystem::get_file_size($dav_vfs_path),
			'etag-hash' 				=> (string) md5_file($dav_vfs_path),
			'mime-type' 				=> (string) self::mimeTypeFile($dav_vfs_path)
		];
		//--
	} //END FUNCTION


	private static function getItemTypeCollection($dav_request_path, $dav_vfs_path) {
		//--
		$dav_request_path = (string) trim((string)$dav_request_path);
		$dav_vfs_path = (string) trim((string)$dav_vfs_path);
		//--
		if(((string)$dav_request_path == '') OR ((string)$dav_vfs_path == '')) {
			return array();
		} //end if
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_path)) {
			return array();
		} //end if
		if(!\SmartFileSystem::is_type_dir($dav_vfs_path)) {
			return array();
		} //end if
		//--
		$restype = '';
		$ext_prop = '';
		//--
		$restype .= '<d:collection/>';
		//--
		$ext_prop .= '<d:displayname>'.\Smart::escape_html(\SmartFileSysUtils::get_file_name_from_path((string)$dav_request_path)).'</d:displayname>'; // iOS Fix
		$ext_prop .= '<d:current-user-principal><d:href>'.\Smart::escape_html((string)SMART_WEBDAV_CALDAV_ICAL_ACC).'</d:href></d:current-user-principal>'; // iOS Fix
		$ext_prop .= '<d:principal-collection-set>'.\Smart::escape_html((string)SMART_WEBDAV_CALDAV_ICAL_PPS).'</d:principal-collection-set>'; // iOS Fix
		$ext_prop .= '<d:principal-URL><d:href>'.\Smart::escape_html((string)SMART_WEBDAV_CALDAV_ICAL_ACC).'</d:href></d:principal-URL>'; // iOS Fix
		//--
		switch((int)self::testIsCalendarCollection($dav_vfs_path)) {
			case 1: // calendar
				$restype  .= '<cal:calendar/>';
				$ext_prop .= '<cal:supported-calendar-data><cal:calendar-data content-type="text/calendar"/></cal:supported-calendar-data>'; // version="2.0"
				$ext_prop .= '<cal:max-resource-size>'.\Smart::format_number_int(self::$caldav_max_res_size,'+').'</cal:max-resource-size>';
				$ext_prop .= '<cal:supported-calendar-component-set><cal:comp name="VEVENT"/><cal:comp name="VTODO"/></cal:supported-calendar-component-set>';
				break;
			case 2: // principal
				$restype  .= '<d:principal/>';
				$ext_prop .= '<cal:calendar-home-set><d:href>'.\Smart::escape_html((string)SMART_WEBDAV_CALDAV_ICAL_HOME).'</d:href></cal:calendar-home-set>';
				break;
			default:
				// nothing to add
		} //end if
		//--
		return (array) [
			'dav-resource-type' 		=> (string) \SmartModExtLib\Webdav\DavServer::DAV_RESOURCE_TYPE_COLLECTION,
			'dav-request-path' 			=> (string) rtrim($dav_request_path, '/').'/',
			'dav-vfs-path' 				=> (string) $dav_vfs_path, // private
			'date-creation-timestamp' 	=> (int) 	0, // \SmartFileSystem::get_file_ctime($dav_vfs_path), // currently is unused
			'date-modified-timestamp' 	=> (int) 	\SmartFileSystem::get_file_mtime($dav_vfs_path),
			'size-bytes' 				=> (int)    0,
		//	'etag-hash' 				=> '', // if etag is empty will not show
			'mime-type' 				=> (string) self::mimeTypeDir($dav_vfs_path),
			'c-xml-restype' 			=> (string) $restype,
			'c-xml-data' 				=> (string) $ext_prop
		];
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>