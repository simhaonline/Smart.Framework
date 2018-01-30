<?php
// Module Lib: \SmartModExtLib\Webdav\DavFileSystem

namespace SmartModExtLib\Webdav;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


final class DavFileSystem {

	// ::
	// v.180130


	public static function methodOptions() { // 200
		//--
		http_response_code(200);
		//--
		header('Content-length: 0');
		header('MS-Author-Via: DAV'); // Microsoft clients are set default to the Frontpage protocol unless we tell them to use WebDAV
		header('DAV: 2'); // [DAV 1, 2] must allow LOCK / UNLOCK ; may here list CalDAV / CardDAV ; DAV version 2 is req. for MacOS
		header('Allow: OPTIONS, HEAD, PROPFIND, MKCOL, PUT, COPY, MOVE, DELETE, GET, LOCK, UNLOCK'); // not yet supported: PROPPATCH ; LOCK / UNLOCK are req. for MacOS
		header('Accept-Ranges: none');
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


	public static function methodLock($dav_request_path, $dav_author) {
		//--
		// Just FAKE, Emulate, to handle compatibility with MacOS
		//--
		header('Expires: '.gmdate('D, d M Y', @strtotime('-1 day')).' '.date('H:i:s').' GMT'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		//--
		$statuscode = 200;
		\SmartModExtLib\Webdav\DavServer::answerLocked(
			(string) $dav_request_path,
			(string) $dav_author,
			(int) $statuscode,
			(string) 'infinity',
			(int) 3600, // fixed 1h
			(string) '00000000-0000-0000-0000-000000000000' // fake UUID
		);
		//--
		return (int) $statuscode;
		//--
	} //END FUNCTION


	public static function methodUnlock($dav_request_path, $dav_author) {
		//--
		// Just FAKE, Emulate, to handle compatibility with MacOS
		// The $dav_request_path, $dav_author must be used when a real unlock will be implemented ...
		//--
		header('Expires: '.gmdate('D, d M Y', @strtotime('-1 day')).' '.date('H:i:s').' GMT'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		//--
		header('Content-length: 0');
		http_response_code(204); // no content
		//--
	} //END FUNCTION


	public static function methodPostMkd($dav_url, $dav_vfs_path, $dir_name) { // browser support to make new dir(s)
		//--
		$dav_vfs_path = (string) trim((string)$dav_vfs_path);
		$dir_name = (string) trim((string)$dir_name);
		//--
		if(((string)$dav_vfs_path == '') OR ((string)$dir_name == '') OR ((string)substr((string)$dir_name, 0, 1) == '.')) {
			echo self::answerPostErr400('Create Directory ERROR: Invalid Directory Name (1)', (string)$dav_url);
			return 400;
		} //end if
		//--
		$dir_name = (string) \SmartUnicode::deaccent_str($dir_name);
		$dir_name = \Smart::safe_filename($dir_name, '-'); // {{{SYNC-SAFE-FNAME-REPLACEMENT}}}
		//--
		if(\SmartFileSysUtils::check_if_safe_path($dav_vfs_path) != '1') {
			echo self::answerPostErr400('Create Directory ERROR: Invalid Directory Name (2)', (string)$dav_url);
			return 400;
		} //end if
		//--
		if(\SmartFileSysUtils::check_if_safe_file_or_dir_name($dir_name) != '1') {
			echo self::answerPostErr400('Create Directory ERROR: Invalid Directory Name (3)', (string)$dav_url);
			return 400;
		} //end if
		//--
		$mkdir_path = (string) \SmartFileSysUtils::add_dir_last_slash($dav_vfs_path);
		if(!\SmartFileSystem::path_exists($mkdir_path)) {
			echo self::answerPostErr400('Create Directory ERROR: Invalid Directory Name (4)', (string)$dav_url);
			return 400;
		} //end if
		$mkdir_path .= (string) $dir_name;
		$mkdir_path = (string) \SmartFileSysUtils::add_dir_last_slash($mkdir_path);
		if(\SmartFileSysUtils::check_if_safe_path($mkdir_path) != '1') {
			echo self::answerPostErr400('Create Directory ERROR: Invalid Directory Name (5)', (string)$dav_url);
			return 400;
		} //end if
		//--
		if(!\SmartFileSystem::path_exists($mkdir_path)) {
			\SmartFileSystem::dir_create($mkdir_path, false);
		} //end if
		//--
		return 200;
		//--
	} //END FUNCTION


	public static function methodMkcol($dav_vfs_path) { // 201 | 207 | 405 | 409 | 415
		//--
		$dav_vfs_path = (string) $dav_vfs_path;
		//--
		$heads = (array) \SmartModExtLib\Webdav\DavServer::getRequestHeaders();
		//Smart::log_notice(print_r($heads,1));
		$body = (string) \SmartModExtLib\Webdav\DavServer::getRequestBody();
		//--
		if((string)trim((string)$body != '')) {
			if(strpos((string)$heads['content-type'], '/xml') !== false) {
				http_response_code(415); // unsupported media type (we must throw 415 for unsupport mkcol bodies which are non-standard)
				header('Content-length: 0');
				return 415;
			} //end if
		} //end if
		//--
		$the_fname = (string) (string) trim((string)\SmartFileSysUtils::get_file_name_from_path((string)$dav_vfs_path));
		if(((string)$the_fname == '') OR (substr($the_fname, 0, 1) == '.')) {
			http_response_code(415); // unsupported media type (empty or dot dirs not allowed)
			return 415;
		} //end if
		//--
		if(!\SmartFileSystem::path_exists($dav_vfs_path)) {
			\SmartFileSystem::dir_create($dav_vfs_path, false);
			if(!\SmartFileSystem::is_type_dir($dav_vfs_path)) {
				http_response_code(409); // in case of FAIL use 409: a collection cannot be created until intermediate collections have been created
				header('Content-length: 0');
				return 409;
			} //end if
		} elseif(!\SmartFileSystem::is_type_dir($dav_vfs_path)) {
	//	} else {
			http_response_code(405); // the destination exists and is a file but the request required to create a directory :: Method Not Allowed (File or Dir Already Exists)
			header('Content-length: 0');
			return 405;
		} //end if
		//--
		http_response_code(201); // HTTP/1.1 201 Created
		header('Content-length: 0');
		return 201;
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
				(string) $dav_method,
				(string) $dav_request_path,
				(bool)   $dav_is_root_path,
				(int)    $statuscode,
				(string) $dav_uri,
				(array) \SmartModExtLib\Webdav\DavFileSystem::getItem($dav_uri, $dav_vfs_path)
			);
		} elseif(is_dir($dav_vfs_path)) { // dir
			$statuscode = 207;
			\SmartModExtLib\Webdav\DavServer::answerMultiStatus(
				(string) $dav_method,
				(string) $dav_request_path,
				(bool)   $dav_is_root_path,
				(int)    $statuscode,
				(string) $dav_uri,
				(array) \SmartModExtLib\Webdav\DavFileSystem::getItem($dav_uri, $dav_vfs_path),
				(array) \SmartModExtLib\Webdav\DavFileSystem::getQuotaAndUsageInfo($dav_vfs_root)
			);
		} else { // not found
			$statuscode = 404;
			\SmartModExtLib\Webdav\DavServer::answerMultiStatus(
				(string) $dav_method,
				(string) $dav_request_path,
				(bool)   $dav_is_root_path,
				(int)    $statuscode,
				(string) $dav_uri
			);
		} //end if else
		//--
		return (int) $statuscode;
		//--
	} //END FUNCTION


	public static function methodPostUpf($dav_url, $dav_vfs_path) { // browser support to make new dir(s)
		//--
		// dot files and valid names are checked in the function below
		//--
		//print_r($_FILES);
		$result = \SmartUtils::store_uploaded_file(
			(string) $dav_vfs_path,
			'file',
			0
		);
		if($result) {
			http_response_code(400); // upload failed for some reasons
			echo self::answerPostErr400('File Upload: '.$result, (string)$dav_url);
			return 400;
		} //end if
		//--
		return 200;
		//--
	} //END FUNCTION


	public static function methodPut($dav_vfs_path) { // 201 | 405 | 415 | 423 | 500
		//--
		if(!\SmartFileSysUtils::check_if_safe_path($dav_vfs_path)) {
			http_response_code(415); // unsupported media type
			return 415;
		} //end if
		//--
		$the_fname = (string) (string) trim((string)\SmartFileSysUtils::get_file_name_from_path((string)$dav_vfs_path));
		if(((string)$the_fname == '') OR (substr($the_fname, 0, 1) == '.')) {
			http_response_code(415); // unsupported media type (empty or dot files not allowed)
			return 415;
		} //end if
		//--
		$the_ext = (string) trim((string)\SmartFileSysUtils::get_file_extension_from_path((string)$dav_vfs_path));
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
		//--
		$fp = \SmartModExtLib\Webdav\DavServer::getRequestBody(true); // get as resource stream
		if(!is_resource($fp)) {
			http_response_code(500); // internal server error
			return 500;
		} //end if
		//--
		if(\SmartFileSystem::is_type_dir($dav_vfs_path)) {
			http_response_code(405); // // the destination exists and is a directory
			return 405;
		} //end if
		//--
		$fd = fopen($dav_vfs_path, 'wb');
		if(!$fd) {
			http_response_code(423); // locked: could not achieve fopen advisory lock
			return 423;
		} //end if
		while($data = fread($fp, 1024*8)) {
			fwrite($fd, $data);
		} //end while
		fclose($fd);
		fclose($fp);
		//--
		http_response_code(201); // HTTP/1.1 201 Created
		header('Content-length: 0');
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


	public static function methodGet($dav_method, $dav_author, $dav_url, $dav_request_path, $dav_vfs_path, $dav_is_root_path, $dav_vfs_root, $dav_request_back_path) { // 200 | 404 | 405 | 415 | 423
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
$nfo_title = 'Files';
$nfo_signature = 'Smart.Framework::WebDAV';
$nfo_crrpath = 'DAV:'.$dav_request_path;
$nfo_lnk_welcome = '#';
			//--
			$bw = (array) \SmartUtils::get_os_browser_ip();
			//--
			if(!in_array((string)$bw['bw'], ['fox', 'crm', 'opr', 'sfr', 'iee', 'iex', 'eph', 'nsf'])) {
				http_response_code(405); // method not allowed: only files can be GET !
				return 405;
			} //end if
			//--
			http_response_code(200);
			$arr_quota = (array) \SmartModExtLib\Webdav\DavFileSystem::getQuotaAndUsageInfo($dav_vfs_root);
			$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage($dav_vfs_path, false, false, ''); // non-recuring
			$fixed_vfs_dir = (string) \SmartFileSysUtils::add_dir_last_slash($dav_vfs_path);
			$fixed_dav_url = (string) rtrim((string)$dav_url, '/').'/';
			$base_url = (string) \SmartUtils::get_server_current_url();
			$arr_f_dirs = array();
			for($i=0; $i<\Smart::array_size($files_n_dirs['list-dirs']); $i++) {
				$arr_f_dirs[] = [
					'name'  => (string) $files_n_dirs['list-dirs'][$i],
					'type'  => (string) self::mimeTypeDir((string)$files_n_dirs['list-dirs'][$i]),
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
			$html = (string) \SmartMarkersTemplating::render_file_template(
				\SmartModExtLib\Webdav\DavServer::getTplPath().'answer-get-path.mtpl.html',
				[
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
					'DISPLAY-QUOTA' 	=> (string) (defined('SMART_WEBDAV_SHOW_USAGE_QUOTA') AND (SMART_WEBDAV_SHOW_USAGE_QUOTA === true)) ? 'yes' : 'no'
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
		header('Content-length: '.(int)\SmartFileSystem::get_file_size($dav_vfs_path));
		header('Content-Type: '.(string)self::mimeTypeFile($dav_vfs_path));
		readfile($dav_vfs_path);
		return 200;
		//--
	} //END FUNCTION


	public static function methodCopy($dav_request_path, $dav_vfs_path, $dav_vfs_root) { // 201 | 204 | 400 | 403 | 412 | 502 | 507
		//--
		return self::methodCopyOrMove('COPY', $dav_request_path, $dav_vfs_path, $dav_vfs_root);
		//--
	} //END FUNCTION


	public static function methodMove($dav_request_path, $dav_vfs_path, $dav_vfs_root) { // 201 | 204 | 400 | 403 | 412 | 502
		//--
		return self::methodCopyOrMove('MOVE', $dav_request_path, $dav_vfs_path, $dav_vfs_root);
		//--
	} //END FUNCTION


	//#####


	private static function answerPostErr400($message, $dav_url) {
		//--
		return (string) \SmartComponents::http_message_400_badrequest('ERROR: '.$message, '<a href="'.\Smart::escape_html((string)$dav_url).'">Click here to return to current Directory</a>');
		//--
	} //END FUNCTION


	private static function methodCopyOrMove($dav_method, $dav_request_path, $dav_vfs_path, $dav_vfs_root) {
		//--
		$heads = (array) \SmartModExtLib\Webdav\DavServer::getRequestHeaders();
		//--
		if((string)trim((string)$heads['destination']) == '') {
			http_response_code(400); // bad request ; destination must be non-empty
			return 400;
		} //end if
		$path_raw_dest = \SmartModExtLib\Webdav\DavServer::extractPathFromCurrentURL((string)$heads['destination'], true);
		if(((string)trim((string)$path_raw_dest) == '') OR ((string)substr(trim((string)$path_raw_dest), 0, 1) == '.')) {
			http_response_code(400); // bad request ; destination must be non-empty, and not start with dot
			return 400;
		} //end if
		$path_raw_dest = (string) trim((string)$path_raw_dest);
		$path_raw_dest = (string) \SmartUnicode::deaccent_str((string)$path_raw_dest);
		$path_raw_dest = (string) \Smart::safe_pathname($path_raw_dest);
		//--
		if(((string)$dav_request_path == (string)$path_raw_dest) OR ((string)$path_raw_dest == '')) {
		//	http_response_code(403); // forbidden ; destination and source are the same
			http_response_code(405); // not allowed ; destination and source are the same
			return 405;
		} //end if
		//--
		$path_dest = (string) \Smart::safe_pathname(rtrim($dav_vfs_root.$path_raw_dest, '/'));
		//\Smart::log_notice($dav_method.' # Src=`'.$dav_vfs_path.'` ; Dest=`'.$path_dest.'` ; OverWr='.(string)$heads['overwrite']);
		//\Smart::log_notice(print_r($heads,1));
		//--
		if((string)strtoupper(trim((string)$heads['overwrite'])) == 'T') {
			$overwrite = true; // this should create a version to avoid rewrite, just on files ; on dirs will not allow
	//	} elseif((string)strtoupper(trim((string)$heads['overwrite'])) == 'F') {
	//		$overwrite = false;
		} else { // MacOS does not provide it if false
			$overwrite = false;
	//		http_response_code(400); // bad request ; the overwrite http header should be either T or F
	//		return 400;
		} //end if else
		//--
		if($overwrite !== true) {
			if(\SmartFileSystem::path_exists($path_dest)) {
				http_response_code(412); // precondition failed (Either the Overwrite header is "F" and the state of the destination resource is not null, or the method was used in a Depth: 0 transaction)
				return 412;
			} //end if
		} //end if
		//--
		if(\SmartFileSystem::path_exists($path_dest)) {
			if(\SmartFileSystem::is_type_dir($path_dest)) {
				\SmartFileSystem::dir_delete($path_dest, true);
			} elseif(\SmartFileSystem::is_type_file($path_dest)) {
				\SmartFileSystem::delete($path_dest);
			} else {
				http_response_code(502); // bad gateway (unknown type: dest, perhaps located on a remote location which refuses to accept the resource)
				return 502;
			} //end if else
			$ok_answer = 204; // no content (The resource was moved successfully to a pre-existing destination URI)
		} else {
			$ok_answer = 201; // created (The resource was moved successfully and a new resource was created at the specified destination URI)
		} //end if
		//--
		$ok = -1;
		if(\SmartFileSystem::is_type_dir($dav_vfs_path)) {
			if((string)$dav_method == 'COPY') {
				$ok = \SmartFileSystem::dir_copy($dav_vfs_path, $path_dest, true);
			} elseif((string)$dav_method == 'MOVE') {
				$ok = \SmartFileSystem::dir_rename($dav_vfs_path, $path_dest);
			} else {
				http_response_code(500); // internal server error
				return 500;
			} //end if else
		} elseif(\SmartFileSystem::is_type_file($dav_vfs_path)) {
			if((string)$dav_method == 'COPY') {
				$ok = \SmartFileSystem::copy($dav_vfs_path, $path_dest, false, true);
			} elseif((string)$dav_method == 'MOVE') {
				$tmp_fext = (string) strtolower((string)\SmartFileSysUtils::get_file_extension_from_path($path_dest)); // get the extension
				if(!defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) { // {{{SYNC-CHK-ALLOWED-DENIED-EXT}}}
					http_response_code(500); // internal server error
					return 500;
				} //end if
				if(stripos((string)SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') !== false) {
					http_response_code(412); // precondition failed
					return 412;
				} //end if
				if(defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) {
					if(stripos((string)SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') === false) {
						http_response_code(412); // precondition failed
						return 412;
					} //end if
				} //end if
				$ok = \SmartFileSystem::rename($dav_vfs_path, $path_dest);
			} else {
				http_response_code(500); // internal server error
				return 500;
			} //end if else
		} else {
			http_response_code(502); // bad gateway (unknown type: source, perhaps located on a remote location which refuses to accept the resource)
			return 502;
		} //end if else
		//--
		//$body = (string) \SmartModExtLib\Webdav\DavServer::getRequestBody();
		//\Smart::log_notice($body);
		//--
		if($ok != 1) {
			if((string)$dav_method == 'COPY') {
				http_response_code(507); // not enough space
				return 507;
			} elseif((string)$dav_method == 'MOVE') {
				http_response_code(502); // bad gateway (something was wrong ... on moving)
				return 502;
			} else {
				http_response_code(500); // method should be COPY or MOVE (something was wrong ... on moving)
				return 500;
			} //end if else
		} //end if
		//--
		http_response_code((int)$ok_answer); // HTTP/1.1 201 Created or HTTP/1.1 204 No Content
		header('Content-length: 0');
		return (int) $ok_answer;
		//--
	} //END FUNCTION


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
			$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage($dav_vfs_path, false, false, ''); // non-recuring
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


	private static function mimeTypeDir($dav_vfs_path) {
		//--
	//	return (string) 'httpd/unix-directory';
		return (string) 'directory';
		//--
	} //END FUNCTION


	private static function mimeTypeFile($dav_vfs_path) {
		//--
		$dav_vfs_path = (string) $dav_vfs_path;
		//--
		return (string) \SmartFileSysUtils::mime_eval($dav_vfs_path, false);
		//--
	} //END FUNCTION


	private static function addSubItem($dav_request_path, $dav_vfs_path, $arr, $subitems, $type) {
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
		//	'etag-hash' 				=> (string) md5_file($dav_vfs_path),
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
		return (array) [
			'dav-resource-type' 		=> (string) \SmartModExtLib\Webdav\DavServer::DAV_RESOURCE_TYPE_COLLECTION,
			'dav-request-path' 			=> (string) rtrim($dav_request_path, '/').'/',
			'dav-vfs-path' 				=> (string) $dav_vfs_path, // private
			'date-creation-timestamp' 	=> (int) 	0, // \SmartFileSystem::get_file_ctime($dav_vfs_path), // currently is unused
			'date-modified-timestamp' 	=> (int) 	\SmartFileSystem::get_file_mtime($dav_vfs_path),
			'size-bytes' 				=> (int)    0,
		//	'etag-hash' 				=> '', // if etag is empty will not show
			'mime-type' 				=> (string) self::mimeTypeDir($dav_vfs_path)
		];
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>