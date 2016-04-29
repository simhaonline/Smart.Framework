<?php
// [LIB - SmartFramework / FileSystem Management]
// (c) 2006-2016 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - FileSystem Utils
// DEPENDS:
//	* Smart::
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartFileSysUtils - provides the File System Util functions.
 *
 * <code>
 *
 * // Usage example:
 * SmartFileSysUtils::some_method_of_this_class(...);
 *
 *  //-----------------------------------------------------------------------------------------------------
 *  //-----------------------------------------------------------------------------------------------------
 *  // SAFE REPLACEMENTS:
 *  // In order to supply a common framework for Unix / Linux but also on Windows,
 *  // because on Windows dir separator is \ instead of / the following functions must be used as replacements:
 *  //-----------------------------------------------------------------------------------------------------
 *  // Smart::real_path()        instead of:        realpath()
 *  // Smart::dir_name()         instead of:        dirname()
 *  // Smart::path_info()        instead of:        pathinfo()
 *  //-----------------------------------------------------------------------------------------------------
 *  // Also, when folders are get from external environments and are not certified if they have
 *  // been converted from \ to / on Windows, those paths have to be fixed using: Smart::fix_path_separator()
 *  //-----------------------------------------------------------------------------------------------------
 *
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 * @hints 		To use paths in a safe manner, never add manually a / at the end of a path variable, because if it is empty will result in accessing the root of the file system (/). To handle this in an easy and safe manner, use the function SmartFileSysUtils::add_dir_last_slash($my_dir) so it will add the trailing slash ONLY if misses but NOT if the $my_dir is empty to avoid root access !
 *
 * @depends 	classes: Smart
 * @version 	v.160429
 * @package 	Filesystem
 *
 */
final class SmartFileSysUtils {

	// ::


//================================================================
/**
 * Return the MAXIMUM allowed Upload Size
 *
 * @return INTEGER								:: the Max Upload Size in Bytes
 */
public static function max_upload_size() {
	//--
	return Smart::format_number_int(((int)ini_get('upload_max_filesize') * 1000 * 1000), '+');
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Check a Filename or a Dirname if contain valid characters (to avoid security injections)
 *
 * @param 	STRING 	$y_path 					:: The path (dir or file) to validate
 * @param 	YES/NO 	$y_deny_absolute_path 		:: *Optional* If YES will dissalow absolute paths
 *
 * @return 	0/1									:: returns 1 if VALID ; 0 if INVALID
 */
public static function check_file_or_dir_name($y_path, $y_deny_absolute_path='yes') {
	//--
	if((string)$y_path == '') { // dissalow empty paths
		return 0;
	} //end if else
	//--
	if(self::test_valid_path($y_path) !== 1) {
		return 0;
	} //end if
	//--
	if(self::test_backward_path($y_path) !== 1) {
		return 0;
	} //end if
	//--
	if((string)$y_deny_absolute_path != 'no') {
		if(self::test_absolute_path($y_path) !== 1) {
			return 0;
		} //end if
	} //end if
	//--
	if(strlen($y_path) > 1024) {
		return 0; // path is longer than the allowed path max length by PHP_MAXPATHLEN between 512 to 4096 (safe is 1024)
	} //end if
	//--
	return 1; // valid
	//--
} //END FUNCTION
//================================================================


//================================================================ CHECK ABSOLUTE PATH ACCESS
/**
 * Function: Raise Error if Unsafe Path.
 *
 * Security: implements protection if unsafe paths are accessed.
 *
 * @access 		private
 * @internal
 *
 */
public static function raise_error_if_unsafe_path($y_path, $y_deny_absolute_path='yes') {
	//--
	if(self::test_valid_path($y_path) !== 1) {
		//--
		Smart::raise_error(
			'SmartFramework // FileSystemUtils // Check Valid Path // ACCESS DENIED to invalid path: '.$y_path,
			'FileSysUtils: INVALID CHARACTERS IN PATH ARE DISALLOWED !' // msg to display
		);
		die(''); // just in case
		//--
	} //end if
	//--
	if(self::test_backward_path($y_path) !== 1) {
		//--
		Smart::raise_error(
			'SmartFramework // FileSystemUtils // Check Backward Path // ACCESS DENIED to invalid path: '.$y_path,
			'FileSysUtils: BACKWARD PATH ACCESS IS DISALLOWED !' // msg to display
		);
		die(''); // just in case
		//--
	} //end if
	//--
	if((string)$y_deny_absolute_path != 'no') {
		if(self::test_absolute_path($y_path) !== 1) {
			//--
			Smart::raise_error(
				'SmartFramework // FileSystemUtils // Check Absolute Path // ACCESS DENIED to invalid path: '.$y_path,
				'FileSysUtils: ABSOLUTE PATH ACCESS IS DISALLOWED !' // msg to display
			);
			die(''); // just in case
			//--
		} //end if
	} //end if
	//--
} //END FUNCTION
//================================================================


//================================================================ TEST IF VALID PATH
// test if path is valid
// path should contain just these characters _ a-z A-Z 0-9 - . @ # /
// path should not contain \ or SPACE
// path should not be equalt with / . ..
// returns 1 if OK
private static function test_valid_path($y_path) {
	//--
	$y_path = (string) $y_path;
	//--
	if((strpos($y_path, ' ') !== false) OR (strpos($y_path, '\\') !== false) OR ((string)trim($y_path) == '/') OR ((string)trim($y_path) == '.') OR ((string)trim($y_path) == '..')) {
		return 0;
	} //end if else
	//-- {{{SYNC-SAFE-PATH-CHARS}}}
	if(!preg_match('/^[_a-zA-Z0-9\-\.@#\/]+$/', (string)$y_path)) { // only ISO-8859-1 characters are allowed in paths (unicode paths are unsafe for the network environments !!!)
		return 0;
	} //end if
	//--
	return 1; // valid
	//--
} //END FUNCTION
//================================================================


//================================================================ TEST IF BACKWARD PATH
// test backpath or combinations against crafted paths to access backward paths on filesystem
// returns 1 if OK
private static function test_backward_path($y_path) {
	//--
	$y_path = (string) $y_path;
	//--
	if((strpos($y_path, '/../') !== false) OR (strpos($y_path, '/./') !== false) OR (strpos($y_path, '/..') !== false) OR (strpos($y_path, '../') !== false)) {
		return 0;
	} //end if else
	//--
	return 1; // valid
	//--
} //END FUNCTION
//================================================================


//================================================================ TEST IF ABSOLUTE PATH
// test against absolute path access
// on UNIX, the first character should not be /
// on Windows, the second character should not be : as the first is the drive letter as c: or c:/
// on Macs & *All OSes, we do not allow | character
// returns 1 if OK
private static function test_absolute_path($y_path) {
	//--
	$y_path = (string) $y_path;
	//--
	if((substr($y_path, 0, 1) == '/') OR (substr($y_path, 0, 1) == '\\') OR (substr($y_path, 1, 1) == ':') OR (substr($y_path, 1, 2) == ':/') OR (strpos($y_path, '|') !== false)) {
		return 0;
	} //end if
	//--
	return 1; // valid
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Safe add a trailing slash to a path if not already have it, with safe detection and avoid root access.
 *
 * Adding a trailing slash to a path is not a simple task as if path is empty, adding the trailing slash will result in accessing the root file system as will be: /.
 * Otherwise it have to detect if the trailing slash exists already to avoid double slash.
 *
 * @param 	STRING 	$y_path 					:: The path to add the trailing slash to
 *
 * @return 	STRING								:: The fixed path with a trailing
 */
public static function add_dir_last_slash($y_path) {
	//--
	$y_path = (string) Smart::fix_path_separator(trim((string)$y_path));
	//--
	if((string)$y_path == '') {
		Smart::log_warning('SmartFramework // FileSystemUtils // Add Last Dir Slash: Empty Path, Returned TMP/INVALID/');
		return 'tmp/invalid/'; // Security Fix: avoid make the path as root: / (if the path is empty, adding a trailing slash is a huge security risk)
	} //end if
	if(strpos($y_path, '\\') !== false) {
		Smart::log_warning('SmartFramework // FileSystemUtils // Add Last Dir Slash: Invalid Path, containing: \\, Returned TMP/INVALID/');
		return 'tmp/invalid/'; // Security Fix: avoid make the path as root: / (if the path is empty, adding a trailing slash is a huge security risk)
	} //end if
	//--
	if(substr($y_path, -1, 1) != '/') {
		$y_path = $y_path.'/';
	} //end if
	//--
	return (string) $y_path;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Remove the version from a file name.
 *
 * @param 	STRING 	$file 						:: The file name to be processed
 *
 * @return 	STRING								:: The fixed file name without the version
 */
public static function version_remove($file) {
	//--
	$file = (string) $file;
	//--
	if((strpos($file, '.@') !== false) AND (strpos($file, '@.') !== false)) {
		$arr = @explode('.@', $file);
		$arr2 = @explode('@.', $arr[1]);
		$file = $arr[0].'.'.$arr2[1];
	} //end if
	//--
	return (string) $file;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Add the version to a file name.
 *
 * @param 	STRING 	$file 						:: The file name to be processed
 * @param 	STRING 	$version 					:: The version to be added
 *
 * @return 	STRING								:: The fixed file name with a version
 */
public static function version_add($file, $version) {
	//--
	$file = (string) self::version_remove(trim((string)$file));
	$version = (string) trim(strtolower(str_replace(array('.', '@'), array('', ''), Smart::safe_validname((string)$version))));
	//--
	$file_no_ext = strtolower((string)self::get_noext_file_name_from_path($file));
	$file_ext = strtolower((string)self::get_file_extension_from_path($file));
	//--
	return (string) $file_no_ext.'.@'.$version.'@.'.$file_ext;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Check the version from a file name.
 *
 * @param 	STRING 	$file 						:: The file name to be checked
 * @param 	STRING 	$version 					:: The version to be checked
 *
 * @return 	0/1									:: returns 1 if the version is detected ; otherwise returns 0 if version not detected
 */
public static function version_check($file, $version) {
	//--
	$file = (string) trim((string)$file);
	$version = (string) trim(strtolower(str_replace(array('.', '@'), array('', ''), Smart::safe_validname((string)$version))));
	//--
	if(stripos($file, '.@'.$version.'@.') !== false) {
		return 1;
	} else {
		return 0;
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the folder name from a path (except last trailing slash: /)
 *
 * @param STRING 	$ypath						:: the path (dir or file)
 * @return STRING 								:: a directory path [FOLDER NAME]
 */
public static function get_dir_from_path($y_path) {
	//--
	$y_path = Smart::safe_pathname($y_path);
	//--
	if((string)$y_path == '') {
		return '';
	} //end if
	//--
	$arr = (array) Smart::path_info((string)$y_path);
	//--
	return (string) trim(Smart::safe_pathname((string)$arr['dirname'])); // this may contain /
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the file name (includding extension) from path
 * WARNING: path_info('c:\\file.php') will not work correct on unix, but on windows will work correct both: path_info('c:\\file.php') and path_info('path/file.php'
 * @param STRING 		$ypath		path or file
 * @return STRING 				[FILE NAME]
 */
public static function get_file_name_from_path($y_path) {
	//--
	$y_path = Smart::safe_pathname($y_path);
	//--
	if((string)$y_path == '') {
		return '';
	} //end if
	//--
	$arr = (array) Smart::path_info((string)$y_path);
	//--
	return (string) trim(Smart::safe_filename((string)$arr['basename']));
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the file name (WITHOUT extension) from path
 *
 * @param STRING 		$ypath		path or file
 * @return STRING 				[FILE NAME]
 */
public static function get_noext_file_name_from_path($y_path) {
	//--
	$y_path = Smart::safe_pathname($y_path);
	//--
	if((string)$y_path == '') {
		return '';
	} //end if
	//--
	$arr = (array) Smart::path_info((string)$y_path);
	//--
	$tmp_ext = (string) $arr['extension'];
	$tmp_file = (string) $arr['basename'];
	//--
	$str_len = strlen($tmp_file) - strlen($tmp_ext) - 1;
	//--
	if(strlen($tmp_ext) > 0) {
		// with .extension
		$tmp_xfile = substr($tmp_file, 0, $str_len);
	} else {
		// no extension
		$tmp_xfile = $tmp_file;
	} //end if else
	//--
	return (string) trim(Smart::safe_filename((string)$tmp_xfile));
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the file extension (without .) from path
 *
 * @param STRING 		$ypath		path or file
 * @return STRING 				[FILE EXTENSION]
 */
public static function get_file_extension_from_path($y_path) {
	//--
	$y_path = Smart::safe_pathname($y_path);
	//--
	if((string)$y_path == '') {
		return '';
	} //end if
	//--
	$arr = (array) Smart::path_info((string)$y_path);
	//--
	return (string) trim(strtolower(Smart::safe_filename((string)$arr['extension'])));
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Generate a prefixed dir from a base36 ID: [0-9A-Z] length: 10 chars.
 * It does include also the ID as final folder.
 * Example: for ID ABCDEFGHIJ09 will return: 9T/5B/0B/9M/9T5B0B9M8M/ as the generated prefixed path.
 * This have to be used for large folder storage structure to avoid limitations on some filesystems (ext3 / ntfs) where max sub-dirs per dir is 32k.
 *
 * The prefixed path will be grouped by each 2 characters (max sub-folders per folder: 36 x 36 = 1296).
 * If a lower length than 10 chars is provided will pad with 0 on the left.
 * If a higher length or an invalid ID is provided will reset the ID to 000000..00 (10 chars) for the given length, but also drop a warning.
 *
 * @param STRING 		$y_id		10 chars id
 * @return STRING 					Prefixed Path
 */
public static function prefixed_uuid10_dir($y_id) { // check len is default 10 as set in lib core uuid 10s
	//--
	$y_id = (string) strtoupper(trim((string)$y_id));
	//--
	if((strlen($y_id) != 10) OR (!preg_match('/^[A-Z0-9]+$/', (string)$y_id))) {
		Smart::log_warning('ERROR: SmartFramework // FileSystemUtils // Prefixed Dir B36-UID // Invalid ID ['.$y_id.']');
		$y_id = '0000000000'; // the all zero
	} //end if
	//--
	$dir = self::add_dir_last_slash(self::add_dir_last_slash((string)implode('/', (array)str_split((string)substr((string)$y_id, 0, 8), 2))).$y_id); // split by 2 grouping except last 2 chars
	//--
	if(!self::check_file_or_dir_name($dir)) {
		Smart::log_warning('ERROR: SmartFramework // FileSystemUtils // Prefixed Dir B36-UID // Invalid Path: ['.$dir.'] :: From ID: ['.$y_id.']');
		return 'tmp/invalid/pfx-b36uid-path/'; // this error should not happen ...
	} //end if
	//--
	return (string) $dir;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Generate a prefixed parent dir from a base16 sha1: [0-9a-f] length: 40 chars.
 * It does NOT include the ID final folder.
 * Example: for ID df3a808b2bf20aaab4419c43d9f3a6143bd6b4bb will return: d/f3a/808/b2b/f20/aaa/b44/19c/43d/9f3/a61/43b/d6b/ as the generated prefixed path.
 * This have to be used for large folder storage structure to avoid limitations on some filesystems (ext3 / ntfs) where max sub-dirs per dir is 32k.
 *
 * The prefixed folder will be grouped by each 3 characters (max sub-folders per folder: 16 x 16 x 16 = 4096).
 * If a lower length than 40 chars is provided will pad with 0 on the left.
 * If a higher length than 40 chars or an invalid ID is provided will reset the ID to 000000..00 (40 chars) for the given length, but also drop a warning.
 *
 * @param STRING 		$y_id		10 chars id
 * @return STRING 					Prefixed Path
 */
public static function prefixed_sha1_path($y_id) { // here the number of levels does not matter too much as at the end will be a cache file
	//--
	$y_id = (string) strtolower(trim((string)$y_id));
	//--
	if((strlen($y_id) != 40) OR (!preg_match('/^[a-f0-9]+$/', (string)$y_id))) {
		Smart::log_warning('ERROR: SmartFramework // FileSystemUtils // Prefixed pDir B16SHA // Invalid ID ['.$y_id.']');
		$y_id = '0000000000000000000000000000000000000000'; // 40 hex like sha1
	} //end if
	//--
	$dir = self::add_dir_last_slash((string)substr((string)$y_id, 0, 1).'/'.implode('/', (array)str_split((string)substr((string)$y_id, 1, 36), 3))); // split by 3 grouping
	//--
	if(!self::check_file_or_dir_name($dir)) {
		Smart::log_warning('ERROR: SmartFramework // FileSystemUtils // Prefixed pDir B16SHA // Invalid Path: ['.$dir.'] :: From ID: ['.$y_id.']');
		return 'tmp/invalid/pfx-b16sha-path/'; // this error should not happen ...
	} //end if
	//--
	return (string) $dir;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Evaluate and return the File MimeType by File Extension.
 *
 * @param STRING 		$yfile		the file name (includding file extension) ; Ex: file.ext
 * @return ARRAY 					0 => mime type ; 1 => inline/attachment; filename="file.ext"
 */
public static function mime_eval($yfile, $ydisposition='') {
	//--
	$yfile = Smart::safe_pathname($yfile);
	//--
	$file = strtolower(self::get_file_name_from_path($yfile)); // bug fixed: if a full path is sent, try to get just the file name to return
	$extension = strtolower(self::get_file_extension_from_path($yfile)); // [OK]
	//--
	switch((string)$extension) {
		//--------------
		case 'txt':
		case 'htm':
		case 'html':
			$type = 'text/html';
			$disp = 'inline';
			//---
			break;
		//--------------
		case 'asc':
		case 'sig':
			$type = 'application/pgp-signature';
			$disp = 'attachment';
			//---
			break;
		case 'curl':
			$type = 'application/vnd.curl';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'js':
			$type = 'application/javascript';
			$disp = 'inline';
			//---
			break;
		case 'json':
			$type = 'application/json';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'xml':
		case 'xsl':
			$type = 'application/xml';
			$disp = 'attachment';
			//---
			break;
		case 'log':
		case 'sql':
			$type = 'text/plain';
			$disp = 'attachment';
			//---
			break;
		case 'csv':
			$type = 'text/csv';
			$disp = 'attachment';
			//---
			break;
		case 'rtf':
			$type = 'application/rtf';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'ai':
		case 'eps':
		case 'ps':
			$type = 'application/postscript';
			$disp = 'attachment';
			//---
			break;
		case 'xfdf':
			$type = 'application/vnd.adobe.xfdf';
			$disp = 'attachment';
			//---
			break;
		case 'pdf':
			$type = 'application/pdf';
			$disp = 'inline'; // 'attachment';
			//---
			break;
		//--------------
		case 'gif':
			$type = 'image/gif';
			$disp = 'inline';
			//---
			break;
		case 'jpg':
		case 'jpe':
		case 'jpeg':
			$type = 'image/jpeg';
			$disp = 'inline';
			//---
			break;
		case 'png':
			$type = 'image/png';
			$disp = 'inline';
			//---
			break;
		//--------------
		case 'tif':
		case 'tiff':
			$type = 'image/tiff';
			$disp = 'attachment';
			//---
			break;
		case 'wmf':
			$type = 'application/x-msmetafile';
			$disp = 'attachment';
			//---
			break;
		case 'bmp':
			$type = 'image/bmp';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'spl':
			$type = 'application/futuresplash';
			$disp = 'inline';
			//---
			break;
		case 'swf':
			$type = 'application/x-shockwave-flash';
			$disp = 'inline';
			//---
			break;
		//--------------
		case 'eml':
			$type = 'message/rfc822';
			$disp = 'attachment';
			//---
			break;
		case 'vcf':
			$type = 'text/x-vcard';
			$disp = 'attachment';
			//---
			break;
		case 'ics':
			$type = 'text/calendar';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'tar':
			$type = 'application/x-tar';
			$disp = 'attachment';
			//---
			break;
		case 'tgz':
		case 'tbz':
			$type = 'application/x-compressed';
			$disp = 'attachment';
			//---
			break;
		case 'z':
			$type = 'application/x-compress';
			$disp = 'attachment';
			//---
			break;
		case 'gz':
			$type = 'application/x-gzip';
			$disp = 'attachment';
			//---
			break;
		case 'bz2':
			$type = 'application/x-bzip2';
			$disp = 'attachment';
			//---
			break;
		case 'xz':
			$type = 'application/x-xz';
			$disp = 'attachment';
			//---
			break;
		case '7z':
		case 'zip':
			$type = 'application/zip';
			$disp = 'attachment';
			//---
			break;
		case 'rar':
			$type = 'application/x-rar-compressed';
			$disp = 'attachment';
			//---
			break;
		case 'sit':
			$type = 'application/x-stuffit';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'doc':
		case 'dot':
			$type = 'application/msword';
			$disp = 'attachment';
			//---
			break;
		case 'xla':
		case 'xlc':
		case 'xlm':
		case 'xls':
		case 'xlt':
		case 'xlw':
			$type = 'application/vnd.ms-excel';
			$disp = 'attachment';
			//---
			break;
		case 'pot':
		case 'pps':
		case 'ppt':
			$type = 'application/vnd.ms-powerpoint';
			$disp = 'attachment';
			//---
			break;
		case 'mdb':
			$type = 'application/x-msaccess';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'mny':
			$type = 'application/x-msmoney';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'wk1':
		case 'wcm':
		case 'wdb':
		case 'wks':
		case 'wps':
			$type = 'application/vnd.ms-works';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'odc':
			$type = 'application/vnd.oasis.opendocument.chart';
			$disp = 'attachment';
			//---
			break;
		case 'otc':
			$type = 'application/vnd.oasis.opendocument.chart-template';
			$disp = 'attachment';
			//---
			break;
		case 'odf':
			$type = 'application/vnd.oasis.opendocument.formula';
			$disp = 'attachment';
			//---
			break;
		case 'otf':
			$type = 'application/vnd.oasis.opendocument.formula-template';
			$disp = 'attachment';
			//---
			break;
		case 'odg':
			$type = 'application/vnd.oasis.opendocument.graphics';
			$disp = 'attachment';
			//---
			break;
		case 'otg':
			$type = 'application/vnd.oasis.opendocument.graphics-template';
			$disp = 'attachment';
			//---
			break;
		case 'odi':
			$type = 'application/vnd.oasis.opendocument.image';
			$disp = 'attachment';
			//---
			break;
		case 'oti':
			$type = 'application/vnd.oasis.opendocument.image-template';
			$disp = 'attachment';
			//---
			break;
		case 'odp':
			$type = 'application/vnd.oasis.opendocument.presentation';
			$disp = 'attachment';
			//---
			break;
		case 'otp':
			$type = 'application/vnd.oasis.opendocument.presentation-template';
			$disp = 'attachment';
			//---
			break;
		case 'ods':
			$type = 'application/vnd.oasis.opendocument.spreadsheet';
			$disp = 'attachment';
			//---
			break;
		case 'ots':
			$type = 'application/vnd.oasis.opendocument.spreadsheet-template';
			$disp = 'attachment';
			//---
			break;
		case 'odt':
			$type = 'application/vnd.oasis.opendocument.text';
			$disp = 'attachment';
			//---
			break;
		case 'otm':
			$type = 'application/vnd.oasis.opendocument.text-master';
			$disp = 'attachment';
			//---
			break;
		case 'ott':
			$type = 'application/vnd.oasis.opendocument.text-template';
			$disp = 'attachment';
			//---
			break;
		case 'oth':
			$type = 'application/vnd.oasis.opendocument.text-web';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'ogg': // theora audio
		case 'oga':
			$type = 'audio/ogg';
			$disp = 'inline';
			break;
		case 'ogv': // theora video
			$type = 'video/ogg';
			$disp = 'inline';
			break;
		case 'webm': // google vp8
			$type = 'video/webm';
			$disp = 'inline';
			break;
		//--------------
		case 'mpeg':
		case 'mpg':
		case 'mpe':
		case 'mpv':
		case 'mp4':
			$type = 'video/mpeg';
			$disp = 'attachment';
			//---
			break;
		case 'mpga':
		case 'mp2':
		case 'mp3':
			$type = 'audio/mpeg';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'qt':
		case 'mov':
			$type = 'video/quicktime';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'rm':
			$type = 'application/vnd.rn-realmedia';
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'avi':
			$type = 'video/x-msvideo';
			$disp = 'attachment';
			//---
			break;
		case 'wm':
		case 'wmv':
		case 'wmx':
		case 'wvx':
			$type = 'video/x-ms-'.$extension;
			$disp = 'attachment';
			//---
			break;
		//--------------
		case 'exe':
		case 'msi':
		case 'dll':
		case 'com':
		case 'bat':
		case 'cmd':
			$type = 'application/x-msdownload';
			$disp = 'attachment';
			//---
			break;
		//--------------
		default:
			$type = 'application/octet-stream';
			$disp = 'attachment';
		//--------------
	} //end switch
	//--
	switch((string)$ydisposition) {
		case 'inline':
			$disp = 'inline'; 	// rewrite display mode
			break;
		case 'attachment':
			$disp = 'attachment'; 	// rewrite display mode
			break;
		default:
			// nothing
	} //end switch
	//--
	return array($type, $disp.'; filename="'.Smart::safe_validname($file).'"');
	//--
} //END FUNCTION
//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartFileSystem - provides the File System Access functions.
 *
 * <code>
 * // Usage example:
 * SmartFileSystem::some_method_of_this_class(...);
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.160429
 * @package 	Filesystem
 *
 */
final class SmartFileSystem {

	// ::


//================================================================ SET LOCK FILE NAME
private static function lock_file_name($file_name) {
	//--
	$file_name = (string) $file_name;
	//--
	if(!SmartFileSysUtils::check_file_or_dir_name($file_name)) {
		//-- this is for absolute paths for example, to avoid create lock outside ...
		if(!is_dir('tmp/locks')) {
			self::dir_recursive_create('tmp/locks');
		} //end if
		$lock_file = 'tmp/locks/'.SmartHashCrypto::sha256(SMART_FRAMEWORK_SECURITY_KEY.'!'.$file_name).'__'.substr(Smart::safe_filename($file_name, '-'), 0, 99).'.__LOCK__'; // this is a max of 165 chars (file name is no more than 255 bytes on many systems)
		//--
	} else {
		//--
		$lock_file = $file_name.'.__LOCK__';
		//--
	} //end if else
	//--
	return (string) $lock_file;
	//--
} //END FUNCTION
//================================================================


//================================================================ CREATE THE LOCK FILE
private static function lock_file_set($lock_file) {
	//--
	$lock_file = (string) $lock_file;
	//--
	if((string)$lock_file == '') {
		Smart::log_warning('SmartFramework // FileSystem / Lock File Set: Empty File Name');
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($lock_file);
	//--
	$out = 1;
	//--
	@file_put_contents($lock_file, (string)time(), LOCK_EX); // do exclusive lock on write
	//--
	if(is_file($lock_file)) {
		@chmod($lock_file, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
	} else {
		$out = 0;
		Smart::log_warning('LibFileSys // LockFileSet // FAILED to set lockfile: '.$lock_file);
	} //end if
	//--
	if(!is_readable($lock_file)) {
		$out = 0;
		Smart::log_warning('LibFileSys // LockFileSet // The lockfile is not readable: '.$lock_file);
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ RELEASE (DELETE) THE LOCK FILE
private static function lock_file_unset($lock_file) {
	//--
	$lock_file = (string) $lock_file;
	//--
	if((string)$lock_file == '') {
		Smart::log_warning('SmartFramework // FileSystem / Lock File Unset: Empty File Name');
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($lock_file);
	//--
	$out = 0;
	//--
	if(is_file($lock_file)) {
		@chmod($lock_file, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
		$result = @unlink($lock_file); // release lock file
	} //end if
	//--
	if(is_file($lock_file)) {
		Smart::log_warning('LibFileSys // LockFileSet // FAILED to set lockfile: '.$lock_file);
	} //end if
	//--
	if($result) {
		$out = 1;
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ CHECK FILE LOCKING
// Returns (string) 1 if locked
private static function lock_file_check($lock_file, $lock_time=60) {
	//--
	$lock_file = (string) $lock_file;
	$lock_time = (int) (0 + $lock_time);
	//--
	if((string)$lock_file == '') {
		Smart::log_warning('SmartFramework // FileSystem / Lock File Check: Empty File Name');
		return 0; // not locked, but register the error above
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($lock_file);
	//--
	if(defined('SMART_FRAMEWORK_FILE_LOCKTIME')) {
		$lock_time = Smart::format_number_int(SMART_FRAMEWORK_FILE_LOCKTIME, '+');
	} else {
		$lock_time = Smart::format_number_int($lock_time, '+');
	} //end if
	//--
	if($lock_time < 5) {
		$lock_time = 5; // no less than 5 seconds
	} //end if
	//--
	if($lock_time > 600) {
		$lock_time = 600; // no more than 10 minutes
	} //end if
	//--
	$is_locked = 0; // init is zero
	//--
	if(SmartFileSysUtils::check_file_or_dir_name($lock_file)) {
		//--
		if(is_file($lock_file)) {
			//--
			$max_lock_time = $lock_time + 50; // time in seconds to wait until a possible garbage lock file is released (default: 90)
			$max_safe_lock_time = $lock_time + 20; // time in seconds to wait until Fail Message is Returned (default: 60)
			//--
			$start_time = time();
			//--
			while(1 == 1) { // loop until it is released
				//--
				if(!self::file_or_link_exists($lock_file)) { // if no more locked, is OK
					//--
					$is_locked = 0; // it is no more locked
					//--
					break;
					//--
				} else { // this is in the case for a remaining lock file (Ex: a previous script crashed and the lock file remains)
					//--
					$lock_fid = @fopen($lock_file, 'rb');
					$locked_time = @fread($lock_fid, 24); // read only 1st 24 bytes
					if($locked_time === false) {
						$locked_time = 0;
					} //end if
					$locked_time = (int) 0 + $locked_time;
					@fclose($lock_fid);
					//--
					if((time() > ($locked_time + $max_lock_time + 1)) OR ($locked_time > (time() + $max_lock_time + 30))) { // if locked time expired or wrong lock time
						//--
						$is_locked = 0; // it is still locked but the lock time expired (maybe a garbage lock file)
						//--
						break;
						//--
					} //end if
					//--
				} //end if
				//--
				if(time() > ($start_time + $max_safe_lock_time)) { // we take an extra delay of 1 seconds
					//--
					$is_locked = 1; // it is still locked
					//--
					break;
					//--
				} //end if
				//--
			} //end while
			//--
		} //end if
		//--
	} //end if
	//--
	return $is_locked;
	//--
} //END FUNCTION
//================================================================


//================================================================
// BUG FIX: PHP file_exists() will return false if the file is a broken link ...
public static function file_or_link_exists($y_file_or_link) {
	//--
	if((file_exists($y_file_or_link)) OR (is_link($y_file_or_link))) {
		return true;
	} else {
		return false;
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================ READ STATIC FILES
// read a file without locks, fast and may be used just on static files
// return the file content as string
// TO BE USED JUST FOR STATIC FILES (YOU ASSUME THE CONTENTS WILL NOT BE CHANGED DURING READS)
// CANNOT USED TO ACCESS TEMPORARY UPLOAD FILES WHICH ARE ALWAYS ABSOLUTE PATH
// NOTE: To access uploaded files use ::read_uploaded()
public static function staticread($file_name, $file_len=0, $markchmod='no') {
	//--
	$file_name = (string) $file_name;
	$file_len = (int) (0 + $file_len);
	if($file_len < 0) {
		$file_len = 0;
	} //end if
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem // StaticRead: Empty File Name');
		return '';
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($file_name);
	//--
	@clearstatcache();
	//--
	$f_cx = '';
	//--
	if(SmartFileSysUtils::check_file_or_dir_name($file_name)) {
		//--
		if(!is_dir($file_name)) {
			//--
			if(is_file($file_name)) {
				//--
				if((string)$markchmod == 'yes') {
					@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
				} //end if
				if(!is_readable($file_name)) {
					@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES); //try to make it readable by applying chmod
					if(!is_readable($file_name)) {
						Smart::log_warning('LibFileSys // StaticReadFile // A file is not readable: '.$file_name);
						return '';
					} //end if
				} //end if
				//--
				if($file_len > 0) {
					$f_cx = @file_get_contents($file_name, false, null, -1, $file_len);
				} else {
					$f_cx = @file_get_contents($file_name, false, null, -1);
				} //end if else
				if($f_cx === false) {
					$f_cx = '';
				} //end if
				//--
			} //end if
			//--
		} //end if
		//--
	} //end if
	//--
	return (string) $f_cx;
	//--
} //END FUNCTION
//================================================================


//================================================================ READ FILES
// read a file with checks until the defined length (if defined length is zero, read the entire file)
// return the file content as string
// CANNOT USED TO ACCESS TEMPORARY UPLOAD FILES WHICH ARE ALWAYS ABSOLUTE PATH
// NOTE: To access uploaded files use ::read_uploaded()
public static function read($file_name, $file_len=0, $markchmod='no') {
	//--
	$file_name = (string) $file_name;
	$file_len = (int) (0 + $file_len);
	if($file_len < 0) {
		$file_len = 0;
	} //end if
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem // Read: Empty File Name');
		return '';
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($file_name);
	//--
	@clearstatcache();
	//--
	if((string)self::lock_file_check(self::lock_file_name($file_name)) == '1') {
		Smart::raise_error(
			'LibFileSys // ReadFile // A file is still locked: '.$file_name,
			'ERROR: FS :: A File is still LOCKED while trying to Open it for READING ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return '';
	} //end if
	//--
	$f_cx = '';
	//--
	if(SmartFileSysUtils::check_file_or_dir_name($file_name)) {
		//--
		if(!is_dir($file_name)) {
			//--
			if(is_file($file_name)) {
				//--
				if((string)$markchmod == 'yes') {
					@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
				} //end if
				if(!is_readable($file_name)) {
					@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES); //try to make ir readable by applying chmod
					if(!is_readable($file_name)) {
						Smart::log_warning('LibFileSys // ReadFile // A file is not readable: '.$file_name);
						return '';
					} //end if
				} //end if
				//--
				$f_id = @fopen($file_name, 'rb');
				//--
				$tmp_file_len = Smart::format_number_int(@filesize($file_name), '+');
				//--
				if($file_len > 0) {
					if($file_len > $tmp_file_len) {
						$file_len = $tmp_file_len; // cannot be more than file length
					} //end if
					if($file_len > 0) {
						$f_cx = @fread($f_id, $file_len);
					} else {
						$f_cx = '';
					} //end if else
				} else {
					if($tmp_file_len > 0) {
						$f_cx = @fread($f_id, $tmp_file_len);
					} else {
						$f_cx = '';
					} //end if else
				} //end if else
				//--
				if($f_cx === false) {
					$f_cx = '';
				} //end if
				//--
				@fclose($f_id);
				//--
			} //end if
			//--
		} //end if else
		//--
	} //end if
	//--
	return (string) $f_cx;
	//--
} //END FUNCTION
//================================================================


//================================================================ CREATE AND WRITE FILES
// create or write a file
// return true or false
// $write_mode is: 'w' = write / 'a' = append
// returns: 1 for success and 0 for error/fail
public static function write($file_name, $file_content='', $write_mode='w') {
	//--
	$file_name = (string) $file_name;
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem // Write: Empty File Name');
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($file_name);
	//--
	@clearstatcache();
	//--
	$lock_file = self::lock_file_name($file_name);
	//--
	if((string)self::lock_file_check($lock_file) == '1') {
		Smart::raise_error(
			'LibFileSys // WriteFile // A file is still locked: '.$file_name,
			'ERROR: FS :: A File is still LOCKED while trying to Open it for WRITING ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return 0;
	} //end if
	//--
	$result = false;
	//--
	if(SmartFileSysUtils::check_file_or_dir_name($file_name)) {
		//--
		if(!is_dir($file_name)) {
			//-- remove it if is a link
			if(is_link($file_name)) {
				self::delete($file_name); // delete the link if is a link (before setting the lock file !)
			} //end if
			//-- create the lock file
			$the_lock_file = self::lock_file_set($lock_file);
			if($the_lock_file) {
				//--
				if(is_file($file_name)) {
					@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod first to be sure file is writable
				} //end if
				/* this method lacks the real locking which can be achieved just with flock which is not as safe as doing at once with: file_put_contents
				if((string)$write_mode == 'w') {
					$f_id = @fopen($file_name, 'wb');
				} else {
					$f_id = @fopen($file_name, 'ab');
				} //end if else
				$result = @fwrite($f_id, (string)$file_content); // return the number of bytes written or false on error
				@fclose($f_id);
				*/
				if((string)$write_mode == 'w') { // wb (write, binary safe)
					$result = @file_put_contents($file_name, (string)$file_content, LOCK_EX);
				} else { // ab (append, binary safe)
					$result = @file_put_contents($file_name, (string)$file_content, FILE_APPEND | LOCK_EX);
				} //end if else
				//--
				if(is_file($file_name)) {
					//--
					@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
					//--
					if(!is_writable($file_name)) {
						Smart::log_warning('LibFileSys // WriteFile // A file is not writable: '.$file_name);
					} //end if
					//--
				} //end if
				//-- check the write result (number of bytes written)
				if($result === false) {
					Smart::log_warning('LibFileSys // WriteFile // Failed to write a file: '.$file_name);
				} else {
					if($result !== @strlen((string)$file_content)) {
						Smart::log_warning('LibFileSys // WriteFile // A file was not completely written (removing it ...): '.$file_name);
						@unlink($file_name); // delete the file, was not completely written (do not use self::delete here, the file is still locked !)
					} //end if
				} //end if
				//--
			} else {
				//--
				Smart::log_warning('LibFileSys // WriteFile // Failed to set the lock file. File was not written: '.$file_name);
				//--
			} //end if
			//-- remove the lock file
			$the_unlock = self::lock_file_unset($lock_file);
			//--
		} //end if else
		//--
	} //end if else
	//--
	if($result === false) { // file was not written
		$out = 0;
	} else { // result can be zero or a positive number of bytes written
		$out = 1;
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ WRITE IF NOT EXISTS
// write a file just if not exists
// lock check is managed via ::write
// stat cache is cleared via :: write
// returns: 1 for success and 0 for error/fail
public static function write_if_not_exists($file_name, $file_content, $y_chkcompare='no') {
	//--
	$file_name = (string) $file_name;
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem // WriteIfNotExists: Empty File Name');
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($file_name);
	//--
	$x_ok = 0;
	//--
	if((string)$y_chkcompare == 'yes') {
		//--
		//if((string)self::read($file_name) != (string)$file_content) { // for performance reasons static read is used in this case to avoid double read/write locking witch is expensive
		if((string)self::staticread($file_name) != (string)$file_content) {
			$x_ok = self::write($file_name, (string)$file_content);
		} else {
			$x_ok = 1;
		} //end if
		//--
	} else {
		//--
		if(!is_file($file_name)) {
			$x_ok = self::write($file_name, (string)$file_content);
		} else {
			$x_ok = 1;
		} //end if else
		//--
	} //end if
	//--
	return $x_ok;
	//--
} //END FUNCTION
//================================================================


//================================================================ COPY FILE
// copy a file to a new location
// return true or false
// returns: 1 for success and 0 for error/fail
public static function copy($file_name, $newlocation) {
	//--
	$file_name = (string) $file_name;
	$newlocation = (string) $newlocation;
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem // Copy: Empty Source File Name');
		return 0;
	} //end if
	if((string)$newlocation == '') {
		Smart::log_warning('SmartFramework // FileSystem // Copy: Empty Destination File Name');
		return 0;
	} //end if
	if((string)$file_name == (string)$newlocation) {
		Smart::log_warning('SmartFramework // FileSystem // Copy: The Source and the Destination Files are the same: '.$file_name);
		return 0;
	} //end if
	//--
	if((!is_file($file_name)) OR ((is_link($file_name)) AND (!is_file(self::link_get_origin($file_name))))) {
		Smart::log_warning('LibFileSys // Rename/Move // Source is not a FILE: S='.$file_name.' ; D='.$newlocation);
		return 0;
	} //end if
	if(self::file_or_link_exists($newlocation)) {
		Smart::log_warning('LibFileSys // Rename/Move // The destination already exists: S='.$file_name.' ; D='.$newlocation);
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($file_name);
	SmartFileSysUtils::raise_error_if_unsafe_path($newlocation);
	//--
	@clearstatcache();
	//--
	if((string)self::lock_file_check(self::lock_file_name($file_name)) == '1') {
		Smart::raise_error(
			'LibFileSys // FileCopy // Source file is still locked: '.$file_name,
			'ERROR: FS :: A File is still LOCKED while trying to COPY [SOURCE] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return 0;
	} //end if
	//--
	if((string)self::lock_file_check(self::lock_file_name($newlocation)) == '1') {
		Smart::raise_error(
			'LibFileSys // FileCopy // Destination file is still locked: '.$newlocation,
			'ERROR: FS :: A File is still LOCKED while trying to COPY [DESTINATION] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return 0;
	} //end if
	//--
	$result = false;
	//--
	if(is_file($file_name)) {
		if(!self::file_or_link_exists($newlocation)) {
			$result = @copy($file_name, $newlocation);
			if(is_file($newlocation)) {
				@chmod($newlocation, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
			} else {
				Smart::log_warning('LibFileSys // CopyFile // Failed to copy a file: '.$file_name.' // to destination: '.$newlocation);
			} //end if
			if(!is_readable($newlocation)) {
				Smart::log_warning('LibFileSys // CopyFile // Destination file is not readable: '.$newlocation);
			} //end if
		} else {
			Smart::log_warning('LibFileSys // CopyFile // Destination file exists: '.$newlocation);
		} //end if
	} //end if
	//--
	if($result) {
		$out = 1;
	} else {
		$out = 0;
	} //end if else
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ RENAME / MOVE FILES
// move a file to a new location
// returns: 1 for success and 0 for error/fail
public static function rename($file_name, $newlocation) {
	//--
	$file_name = (string) $file_name;
	$newlocation = (string) $newlocation;
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem // Rename/Move: Empty Source File Name');
		return 0;
	} //end if
	if((string)$newlocation == '') {
		Smart::log_warning('SmartFramework // FileSystem // Rename/Move: Empty Destination File Name');
		return 0;
	} //end if
	if((string)$file_name == (string)$newlocation) {
		Smart::log_warning('SmartFramework // FileSystem // Rename/Move: The Source and the Destination Files are the same: '.$file_name);
		return 0;
	} //end if
	//--
	if((!is_file($file_name)) OR ((is_link($file_name)) AND (!is_file(self::link_get_origin($file_name))))) {
		Smart::log_warning('LibFileSys // Rename/Move // Source is not a FILE: S='.$file_name.' ; D='.$newlocation);
		return 0;
	} //end if
	if(self::file_or_link_exists($newlocation)) {
		Smart::log_warning('LibFileSys // Rename/Move // The destination already exists: S='.$file_name.' ; D='.$newlocation);
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($file_name);
	SmartFileSysUtils::raise_error_if_unsafe_path($newlocation);
	//--
	@clearstatcache();
	//--
	if((string)self::lock_file_check(self::lock_file_name($file_name)) == '1') {
		Smart::raise_error(
			'LibFileSys // FileRename // Source file is still locked: '.$file_name,
			'ERROR: FS :: A File is still LOCKED while trying to MOVE [SOURCE] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return 0;
	} //end if
	//--
	if((string)self::lock_file_check(self::lock_file_name($newlocation)) == '1') {
		Smart::raise_error(
			'LibFileSys // FileRename // Destination file is still locked: '.$newlocation,
			'ERROR: FS :: A File is still LOCKED while trying to MOVE [DESTINATION] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return 0;
	} //end if
	//--
	$f_cx = false;
	//--
	if(((string)$file_name != (string)$newlocation) AND (SmartFileSysUtils::check_file_or_dir_name($file_name)) AND (SmartFileSysUtils::check_file_or_dir_name($newlocation))) {
		//--
		if((is_file($file_name)) OR ((is_link($file_name)) AND (is_file(self::link_get_origin($file_name))))) {
			//--
			if(!is_dir($newlocation)) {
				//--
				self::delete($newlocation);
				//--
				$f_cx = @rename($file_name, $newlocation);
				//--
				if(is_file($newlocation)) {
					@chmod($newlocation, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
				} else {
					Smart::log_warning('LibFileSys // RenameFile // Failed to rename a file: '.$file_name.' // to destination: '.$newlocation);
				} //end if
				//--
				if(!is_readable($newlocation)) {
					Smart::log_warning('LibFileSys // RenameFile // Destination file is not readable: '.$newlocation);
				} //end if
				//--
			} //end if else
			//--
		} //end if else
		//--
	} //end if
	//--
	if($f_cx == true) {
		$x_ok = 1;
	} else {
		$x_ok = 0;
	} //end if
	//--
	return $x_ok;
	//--
} //END FUNCTION
//================================================================


//================================================================ READ UPLOADED FILES
// read an uploaded file
// return the file contents as string
// v.160215 with fix for Windows absolute paths
public static function read_uploaded($file_name) {
	//--
	$file_name = (string) $file_name;
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem / Read-Uploaded: Empty Uploaded File Name');
		return '';
	} //end if
	//-- {{{SYNC-FILESYS-UPLD-FILE-CHECKS}}}
	if((string)DIRECTORY_SEPARATOR != '\\') { // if not on Windows (this test will FAIL on Windows ...)
		if(!SmartFileSysUtils::check_file_or_dir_name($file_name, 'no')) { // here we do not test against absolute path access because uploaded files always return the absolute path
			Smart::log_warning('SmartFramework // FileSystem / Read-Uploaded: The Uploaded File Path is Not Safe: '.$file_name);
			return '';
		} //end if
		SmartFileSysUtils::raise_error_if_unsafe_path($file_name, 'no'); // here we do not test against absolute path access because uploaded files always return the absolute path
	} //end if
	//--
	@clearstatcache();
	//--
	$f_cx = '';
	//--
	if(is_uploaded_file($file_name)) {
		//--
		if(!is_dir($file_name)) {
			//--
			if((is_file($file_name)) AND (is_readable($file_name))) {
				//--
				$f_id = @fopen($file_name, 'rb');
				//--
				$tmp_file_len = @filesize($file_name);
				//--
				if($tmp_file_len > 0) {
					$f_cx = @fread($f_id, $tmp_file_len);
					if($f_cx === false) {
						$f_cx = '';
					} //end if
				} else {
					$f_cx = '';
				} //end if else
				//--
				@fclose($f_id);
				//--
			} else {
				//--
				Smart::log_warning('LibFileSys // ReadUploadedFile // The file is not readable: '.$file_name);
				//--
			} //end if
			//--
		} //end if else
		//--
	} else {
		//--
		Smart::log_warning('LibFileSys // ReadUploadedFile // Cannot Find the Uploaded File or it is NOT an Uploaded File: '.$file_name);
		//--
	} //end if
	//--
	return (string) $f_cx;
	//--
} //END FUNCTION
//================================================================


//================================================================ MOVE UPLOADED FILE
// move a UPLOADED file to a new location
// returns: 1 for success and 0 for error/fail
// v.160215 with fix for Windows absolute paths
public static function move_uploaded($file_name, $newlocation) {
	//--
	$file_name = (string) $file_name;
	$newlocation = (string) $newlocation;
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem // Move-Uploaded: Empty Uploaded File Name');
		return 0;
	} //end if
	if((string)$newlocation == '') {
		Smart::log_warning('SmartFramework // FileSystem // Move-Uploaded: Empty Destination File Name');
		return 0;
	} //end if
	if((string)$file_name == (string)$newlocation) {
		Smart::log_warning('SmartFramework // FileSystem // Move-Uploaded: The Source and the Destination Files are the same: '.$file_name);
		return 0;
	} //end if
	//--
	if(!is_uploaded_file($file_name)) {
		Smart::log_warning('SmartFramework // FileSystem // Move-Uploaded: Cannot Find the Uploaded File or it is NOT an Uploaded File: '.$file_name);
		return 0;
	} //end if
	//-- {{{SYNC-FILESYS-UPLD-FILE-CHECKS}}}
	if((string)DIRECTORY_SEPARATOR != '\\') { // if not on Windows (this test will FAIL on Windows ...)
		if(!SmartFileSysUtils::check_file_or_dir_name($file_name, 'no')) { // here we do not test against absolute path access because uploaded files always return the absolute path
			Smart::log_warning('SmartFramework // FileSystem / MoveUploadedFile: The Uploaded File Path is Not Safe: '.$file_name);
			return 0;
		} //end if
		SmartFileSysUtils::raise_error_if_unsafe_path($file_name, 'no'); // here we do not test against absolute path access because uploaded files always return the absolute path
	} //end if
	//--
	if(!SmartFileSysUtils::check_file_or_dir_name($newlocation)) {
		Smart::log_warning('SmartFramework // FileSystem / MoveUploadedFile: The Destination File Path is Not Safe: '.$file_name);
		return 0;
	} //end if
	SmartFileSysUtils::raise_error_if_unsafe_path($newlocation);
	//--
	@clearstatcache();
	//--
	if((string)self::lock_file_check(self::lock_file_name($newlocation)) == '1') {
		Smart::raise_error(
			'LibFileSys // MoveUploadedFile // Destination file is still locked: '.$newlocation,
			'ERROR: FS :: A File is still LOCKED while trying to MOVE-UPLOADED [DESTINATION] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return 0;
	} //end if
	//--
	$f_cx = false;
	//--
	if(SmartFileSysUtils::check_file_or_dir_name($newlocation)) {
		//--
		if(!is_dir($file_name)) {
			//--
			if(!is_dir($newlocation)) {
				//--
				self::delete($newlocation);
				//--
				$f_cx = @move_uploaded_file($file_name, $newlocation);
				//--
				if(is_file($newlocation)) {
					@touch($newlocation, time()); // touch modified time to avoid upload differences in time
					@chmod($newlocation, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
				} else {
					Smart::log_warning('LibFileSys // MoveUploadedFile // Failed to move uploaded file: '.$file_name.' // to destination: '.$newlocation);
				} //end if
				//--
				if(!is_readable($newlocation)) {
					Smart::log_warning('LibFileSys // MoveUploadedFile // Destination file is not readable: '.$newlocation);
				} //end if
				//--
				sleep(1); // stay one second to release a second difference between uploaded files
				//--
			} //end if else
			//--
		} //end if else
		//--
	} //end if
	//--
	if($f_cx == true) {
		$x_ok = 1;
	} else {
		$x_ok = 0;
	} //end if
	//--
	return $x_ok;
	//--
} //END FUNCTION
//================================================================


//================================================================ DELETE FILES
// delete a file
// returns: 1 for success and 0 for error/fail
public static function delete($file_name) {
	//--
	$file_name = (string) $file_name;
	//--
	if((string)$file_name == '') {
		Smart::log_warning('SmartFramework // FileSystem / File Delete: The File Name is Empty !');
		return 0; // empty file name
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($file_name);
	//--
	@clearstatcache();
	//--
	if(!self::file_or_link_exists($file_name)) {
		//--
		return 1;
		//--
	} //end if
	//--
	if(is_link($file_name)) { // {{{SYNC-BROKEN-LINK-DELETE}}}
		//--
		$f_cx = @unlink($file_name);
		//--
		if(($f_cx) AND (!is_link($file_name))) {
			return 1;
		} else {
			return 0;
		} //end if else
		//--
	} //end if
	//--
	if((string)self::lock_file_check(self::lock_file_name($file_name)) == '1') {
		Smart::raise_error(
			'LibFileSys // FileDelete // A file is still locked: '.$file_name,
			'ERROR: FS :: A File is still LOCKED while trying to DELETE ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
		);
		die(''); // just in case
		return 0;
	} //end if
	//--
	$f_cx = false;
	//--
	if(SmartFileSysUtils::check_file_or_dir_name($file_name)) {
		//--
		if((is_file($file_name)) OR (is_link($file_name))) {
			//--
			if(is_file($file_name)) {
				//--
				@chmod($file_name, SMART_FRAMEWORK_CHMOD_FILES); //apply chmod
				//--
				$f_cx = @unlink($file_name);
				//--
				if(self::file_or_link_exists($file_name)) {
					Smart::log_warning('LibFileSys // DeleteFile // FAILED to delete this file: '.$file_name);
				} //end if
				//--
			} //end if
			//--
		} elseif(is_dir($file_name)) {
			//--
			Smart::log_warning('LibFileSys // DeleteFile // A file was marked for deletion but that is a directory: '.$file_name);
			//--
		} //end if
		//--
	} //end if
	//--
	if($f_cx == true) {
		$x_ok = 1;
	} else {
		$x_ok = 0;
	} //end if
	//--
	return $x_ok;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function link_get_origin($y_link) {
	//--
	$y_link = (string) $y_link;
	//--
	if((string)$y_link == '') {
		Smart::log_warning('SmartFramework // FileSystem / Get Link: The Link Name is Empty !');
		return 0;
	} //end if
	//--
	if(!SmartFileSysUtils::check_file_or_dir_name($y_link)) {
		Smart::log_warning('SmartFramework // FileSystem / Get Link: Invalid Path Link : '.$y_link);
		return 0;
	} //end if
	//--
	if(substr($y_link, -1, 1) == '/') { // add trailing slash
		Smart::log_warning('SmartFramework // FileSystem / Get Link: Link Have a trailing Slash / : '.$y_link);
		$y_link = substr($y_link, 0, (strlen($y_link)-1));
	} //end if
	//--
	if(!SmartFileSysUtils::check_file_or_dir_name($y_link)) {
		Smart::log_warning('SmartFramework // FileSystem / Get Link: Invalid Link Path : '.$y_link);
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($y_link);
	//--
	if(!is_link($y_link)) {
		Smart::log_warning('SmartFramework // FileSystem / Get Link: Link does not exists : '.$y_link);
		return 0;
	} //end if
	//--
	return (string) @readlink($y_link);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function link_create($origin, $destination) {
	//--
	$origin = (string) $origin;
	$destination = (string) $destination;
	//--
	if((string)$origin == '') {
		Smart::log_warning('SmartFramework // FileSystem / Create Link: The Origin Name is Empty !');
		return 0;
	} //end if
	if((string)$destination == '') {
		Smart::log_warning('SmartFramework // FileSystem / Create Link: The Destination Name is Empty !');
		return 0;
	} //end if
	//--
	if(!SmartFileSysUtils::check_file_or_dir_name($origin, 'no')) { // here we do not test against absolute path access because readlink may return an absolute path
		Smart::log_warning('SmartFramework // FileSystem / Create Link: Invalid Path for Origin : '.$origin);
		return 0;
	} //end if
	if(!SmartFileSysUtils::check_file_or_dir_name($destination)) {
		Smart::log_warning('SmartFramework // FileSystem / Create Link: Invalid Path for Destination : '.$destination);
		return 0;
	} //end if
	//--
	if(!self::file_or_link_exists($origin)) {
		Smart::log_warning('SmartFramework // FileSystem / Create Link: Origin does not exists : '.$origin);
		return 0;
	} //end if
	if(self::file_or_link_exists($destination)) {
		Smart::log_warning('SmartFramework // FileSystem / Create Link: Destination exists : '.$destination);
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($origin, 'no'); // here we do not test against absolute path access because readlink may return an absolute path
	SmartFileSysUtils::raise_error_if_unsafe_path($destination);
	//--
	$result = @symlink($origin, $destination);
	//--
	if($result) {
		$out = 1;
	} else {
		$out = 0;
	} //end if else
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ CREATE DIRS
// create a dir
// returns: 1 for success and 0 for error/fail
public static function dir_create($dir_name) {
	//--
	$dir_name = (string) $dir_name;
	//--
	if((string)$dir_name == '') {
		Smart::log_warning('SmartFramework // FileSystem / Create Dir: The Dir Name is Empty !');
		return 0;
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($dir_name);
	//--
	@clearstatcache();
	//--
	$result = false;
	//--
	if(SmartFileSysUtils::check_file_or_dir_name($dir_name)) {
		//--
		if(!self::file_or_link_exists($dir_name)) {
			//--
			$result = @mkdir($dir_name, SMART_FRAMEWORK_CHMOD_DIRS);
			//--
			if(is_dir($dir_name)) {
				@chmod($dir_name, SMART_FRAMEWORK_CHMOD_DIRS); //apply chmod
			} //end if
			//--
		} elseif(is_dir($dir_name)) {
			//--
			$result = true; // dir exists
			//--
		} //end if else
		//--
		if(!is_dir($dir_name)) {
			Smart::log_warning('LibFileSys // CreateDir // FAILED to create a directory: '.$dir_name);
			$out = 0;
		} //end if
		//--
		if(!is_writable($dir_name)) {
			Smart::log_warning('LibFileSys // CreateDir // The directory is not writable: '.$dir_name);
			$out = 0;
		} //end if
		//--
	} //end if
	//--
	if($result == true) {
		$out = 1;
	} else {
		$out = 0;
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ CREATE RECURSIVE DIRS
// recursive create dir
// returns: 1 for success and 0 for error/fail
public static function dir_recursive_create($dir_name) {
	//--
	$dir_name = (string) $dir_name;
	//--
	if((string)$dir_name == '') {
		Smart::log_warning('SmartFramework // FileSystem / Create Dir Recursive: The Dir Name is Empty !');
		return 0;
	} //end if
	//--
	$dir_name = SmartFileSysUtils::add_dir_last_slash($dir_name); // fix invalid path (must end with /)
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($dir_name);
	//--
	if(is_dir($dir_name)) {
		return 1; // stop if dir exists
	} //end if
	//--
	$dir_elements = @explode('/', $dir_name);
	//--
	if(Smart::array_size($dir_elements) <= 1) { // will have 'dir/'
		return 0;
	} //end if
	//--
	@clearstatcache();
	//--
	for($i=0; $i<count($dir_elements); $i++) {
		//--
		$tmp_element = $dir_elements[$i];
		//--
		if(strlen($tmp_element) <= 0) {
			continue; // loop jump
		} //end if
		//--
		$tmp_crr_dir .= $tmp_element.'/';
		//--
		$result = 0;
		//--
		if(is_file($tmp_crr_dir)) {
			$result = 0; // will not rewrite a file with a dir !
		} elseif(is_dir($tmp_crr_dir)) {
			$result = 1; // dir already exists
		} else {
			$result = self::dir_create($tmp_crr_dir);
		} //end if else
		//--
		if(!$result) {
			return 0;
		} //end if
		//--
	} //end for
	//--
	if(!is_dir($dir_name)) {
		Smart::log_warning('LibFileSys // CreateDir Recursive // The directory does not exists: '.$dir_name);
		return 0;
	} //end if
	//--
	if(!is_writable($dir_name)) {
		Smart::log_warning('LibFileSys // CreateDir Recursive // The directory is not writable: '.$dir_name);
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//================================================================


//================================================================ RECURSIVE COPY A DIRECTORY (FULL CLONE)
//#####
// !!! IMPORTANT !!!
// Always use this with single-user mode enabled
// AFTER COPY A DIR USE compare_folders() to check (not use here because is recursive and would take too much ...)
//#####
// recursive function to copy a folder with all sub folders and files
// returns: 1 for success and 0, -1, -2, -3, -4, -5 for error/fail
// WARNING: Should Not Copy Destination inside Source to avoid Infinite Loop (anyway there is a loop protection but it is not safe as we don't know if all files were copied) !!!
// WARNING: Last two params SHOULD NOT be used (they are private to remember the initial dirs...)
// NOTICE: $dirsource = 'some/folder/one'; // Must not end with Slash !!!
// NOTICE: $dirdest = 'some/folder/two'; // Must not end with Slash !!!
public static function dir_copy($dirsource, $dirdest) {
	//--
	return self::dir_recursive_private_copy($dirsource, $dirdest);
	//--
} //END FUNCTION
//================================================================
private static function dir_recursive_private_copy($dirsource, $dirdest, $protected_dirsource='', $protected_dirdest='') {
	//--
	$dirsource = (string) $dirsource;
	$dirdest = (string) $dirdest;
	$protected_dirsource = (string) $protected_dirsource;
	$protected_dirdest = (string) $protected_dirdest;
	//--
	if(strlen($dirsource) <= 0) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Source Dir Name is Empty !');
		return 0; // empty source dir
	} //end if
	if(strlen($dirdest) <= 0) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Destination Dir Name is Empty !');
		return 0; // empty destination dir
	} //end if
	//--
	@clearstatcache();
	//--
	if(strlen($protected_dirsource) <= 0) {
		$protected_dirsource = (string) $dirsource; // 1st time
	} //end if
	if(strlen($protected_dirdest) <= 0) {
		if(self::file_or_link_exists($dirdest)) {
			Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Destination Dir exists: S='.$destination);
			return 0;
		} //end if else
		$protected_dirdest = (string) $dirdest; // 1st time
	} //end if
	//-- add trailing slash
	$dirsource = SmartFileSysUtils::add_dir_last_slash($dirsource);
	$dirdest = SmartFileSysUtils::add_dir_last_slash($dirdest);
	//-- checks (must be after adding trailing slashes)
	SmartFileSysUtils::raise_error_if_unsafe_path($dirsource);
	SmartFileSysUtils::raise_error_if_unsafe_path($dirdest);
	SmartFileSysUtils::raise_error_if_unsafe_path($protected_dirsource);
	SmartFileSysUtils::raise_error_if_unsafe_path($protected_dirdest);
	//-- protect against infinite loop if the source and destination are the same or destination contained in source
	if((string)$dirdest == (string)$dirsource) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Source Dir is the same as Destination Dir: S&D='.$dirdest);
		return 0;
	} //end if
	if((string)$dirdest == (string)SmartFileSysUtils::add_dir_last_slash(Smart::dir_name($dirsource))) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Destination Dir is the same as Source Parent Dir: S='.$dirsource.' ; D='.$dirdest);
		return 0;
	} //end if
	if((string)substr($dirdest, 0, strlen($dirsource)) == (string)$dirsource) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Destination Dir is inside the Source Dir: S='.$dirsource.' ; D='.$dirdest);
		return 0;
	} //end if
	if((string)substr($protected_dirdest, 0, strlen($protected_dirsource)) == (string)$protected_dirsource) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Original Destination Dir is inside the Original Source Dir: S*='.$protected_dirsource.' ; D*='.$protected_dirdest);
		return 0;
	} //end if
	//-- protect against infinite loop (this can happen with loop sym-links)
	if((string)$dirsource == (string)$protected_dirdest) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Source Dir is the same as Previous Step Source Dir (Loop Detected): S='.$dirsource.' ; S*='.$protected_dirdest);
		return 0;
	} //end if
	//--
	if(!SmartFileSysUtils::check_file_or_dir_name($dirsource)) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Source Dir Name is Invalid: S='.$dirsource);
		return 0;
	} //end if
	if(!SmartFileSysUtils::check_file_or_dir_name($dirdest)) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Destination Dir Name is Invalid: D='.$dirdest);
		return 0;
	} //end if
	//--
	if(!is_dir($dirsource)) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Source Dir Name is not a Directory or does not exists: S='.$dirsource);
		return 0;
	} //end if else
	//--
	if(self::file_or_link_exists($dirdest)) {
		if(!is_dir($dirdest)) {
			Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Destination Dir appear to be a file: D='.$dirdest);
			return 0;
		} //end if
	} else {
		if(self::dir_recursive_create($dirdest) !== 1) {
			Smart::log_warning('SmartFramework // FileSystem / Copy Dir: Could Not Recursively Create the Destination: D='.$dirdest);
			return 0;
		} //end if
	} //end if else
	//--
	$out = 1; // default is ok
	//--
	if($handle = opendir($dirsource)) {
		//--
		while(false !== ($file = readdir($handle))) {
			//--
			if(((string)$file != '.') AND ((string)$file != '..')) {
				//--
				$tmp_path = $dirsource.$file;
				$tmp_dest = $dirdest.$file;
				//--
				SmartFileSysUtils::raise_error_if_unsafe_path($tmp_path);
				SmartFileSysUtils::raise_error_if_unsafe_path($tmp_dest);
				//--
				if(self::file_or_link_exists($tmp_path)) {
					//--
					if(is_link($tmp_path)) { // link
						//--
						$tmp_readlink = self::link_get_origin($tmp_path);
						if(!is_dir($tmp_readlink)) {
							if((string)self::lock_file_check(self::lock_file_name($tmp_readlink)) == '1') {
								Smart::raise_error(
									'SmartFramework // FileSystem / Copy Dir: Link is Locked (Read-Link Source): '.$tmp_readlink,
									'ERROR: FS :: A Link is still LOCKED while trying to COPY [READLINK-SOURCE] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
								);
								die(''); // just in case
								return 0;
							} //end if
							if((string)self::lock_file_check(self::lock_file_name($tmp_path)) == '1') {
								Smart::raise_error(
									'SmartFramework // FileSystem / Copy Dir: Link is Locked (Source): '.$tmp_path,
									'ERROR: FS :: A Link is still LOCKED while trying to COPY [SOURCE] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
								);
								die(''); // just in case
								return 0;
							} //end if
						} //end if
						//--
						if((string)self::lock_file_check(self::lock_file_name($tmp_dest)) == '1') {
							Smart::raise_error(
								'SmartFramework // FileSystem / Copy Dir: Link is Locked (Destination): '.$tmp_dest,
								'ERROR: FS :: A Link is still LOCKED while trying to COPY [DESTINATION] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
							);
							die(''); // just in case
							return 0;
						} //end if
						//--
						self::delete($tmp_dest);
						if(self::file_or_link_exists($tmp_dest)) {
							Smart::log_warning('LibFileSys // RecursiveDirCopy // Destination link still exists: '.$tmp_dest);
						} //end if
						//--
						if(self::link_create($tmp_readlink, $tmp_dest) !== 1) {
							Smart::log_warning('LibFileSys // RecursiveDirCopy // Failed to copy a Link: '.$tmp_path);
							return 0;
						} //end if else
						//--
					} elseif(is_file($tmp_path)) { // file
						//--
						if((string)self::lock_file_check(self::lock_file_name($tmp_path)) == '1') {
							Smart::raise_error(
								'SmartFramework // FileSystem / Copy Dir: File is Locked (Source): '.$tmp_path,
								'ERROR: FS :: A File is still LOCKED while trying to COPY [SOURCE] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
							);
							die(''); // just in case
							return 0;
						} //end if
						//--
						if((string)self::lock_file_check(self::lock_file_name($tmp_dest)) == '1') {
							Smart::raise_error(
								'SmartFramework // FileSystem / Copy Dir: File is Locked (Destination): '.$tmp_dest,
								'ERROR: FS :: A File is still LOCKED while trying to COPY [DESTINATION] ... Please Try Again in few seconds as the Server is too Busy ...!' // msg to display
							);
							die(''); // just in case
							return 0;
						} //end if
						//--
						self::delete($tmp_dest);
						if(self::file_or_link_exists($tmp_dest)) {
							Smart::log_warning('LibFileSys // RecursiveDirCopy // Destination file still exists: '.$tmp_dest);
						} //end if
						//--
						if(self::copy($tmp_path, $tmp_dest) !== 1) {
							Smart::log_warning('LibFileSys // RecursiveDirCopy // Failed to copy a File: '.$tmp_path);
							return 0;
						} //end if else
						//--
					} elseif(is_dir($tmp_path)) { // dir
						//--
						if(self::dir_recursive_private_copy($tmp_path, $tmp_dest, $protected_dirsource, $protected_dirdest) !== 1) {
							Smart::log_warning('LibFileSys // RecursiveDirCopy // Failed on Dir: '.$tmp_path);
							return 0;
						} //end if
						//--
					} else {
						//--
						Smart::log_warning('LibFileSys // RecursiveDirCopy // Invalid Type: '.$tmp_path);
						return 0;
						//--
					} //end if else
					//--
				} elseif(is_link($tmp_path)) { // broken link (we still copy it)
					//--
					self::delete($tmp_dest);
					if(self::file_or_link_exists($tmp_dest)) {
						Smart::log_warning('LibFileSys // RecursiveDirCopy // Destination Link still exists: '.$tmp_dest);
					} //end if
					//--
					if(self::link_create(self::link_get_origin($tmp_path), $tmp_dest) !== 1) {
						Smart::log_warning('LibFileSys // RecursiveDirCopy // Failed to copy a Link: '.$tmp_path);
						return 0;
					} //end if else
					//--
				} else {
					//--
					Smart::log_warning('LibFileSys // RecursiveDirCopy // File does not exists or is not accessible: '.$tmp_path);
					return 0;
					//--
				} //end if
				//--
			} //end if
			//--
		} //end while
		//--
		@closedir($handle);
		//--
	} else {
		//--
		$out = 0;
		//--
	} //end if else
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ RENAME DIR / MOVE DIR
// rename or move a dir
// returns: 1 for success and 0 for error/fail
public static function dir_rename($dir_name, $new_dir_name) {
	//--
	$dir_name = (string) $dir_name;
	$new_dir_name = (string) $new_dir_name;
	//--
	if((string)$dir_name == '') {
		Smart::log_warning('SmartFramework // FileSystem / Rename/Move Dir: Source Dir Name is Empty !');
		return 0;
	} //end if
	if((string)$new_dir_name == '') {
		Smart::log_warning('SmartFramework // FileSystem / Rename/Move Dir: Destination Dir Name is Empty !');
		return 0;
	} //end if
	if((string)$dir_name == (string)$new_dir_name) {
		Smart::log_warning('SmartFramework // FileSystem // Rename/Move Dir: The Source and the Destination Files are the same: '.$dir_name);
		return 0;
	} //end if
	//--
	if((!is_dir($dir_name)) OR ((is_link($dir_name)) AND (!is_dir(self::link_get_origin($dir_name))))) {
		Smart::log_warning('LibFileSys // RenameDir // Source is not a DIR: S='.$dir_name.' ; D='.$new_dir_name);
		return 0;
	} //end if
	if(self::file_or_link_exists($new_dir_name)) {
		Smart::log_warning('LibFileSys // RenameDir // The destination already exists: S='.$dir_name.' ; D='.$new_dir_name);
		return 0;
	} //end if
	//--
	$dir_name = SmartFileSysUtils::add_dir_last_slash($dir_name); // trailing slash
	$new_dir_name = SmartFileSysUtils::add_dir_last_slash($new_dir_name); // trailing slash
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($dir_name);
	SmartFileSysUtils::raise_error_if_unsafe_path($new_dir_name);
	//--
	if((string)$dir_name == (string)$new_dir_name) {
		Smart::log_warning('SmartFramework // FileSystem / Rename/Move Dir: Source and Destination are the same: S&D='.$dir_name);
		return 0;
	} //end if
	if((string)$new_dir_name == (string)SmartFileSysUtils::add_dir_last_slash(Smart::dir_name($dir_name))) {
		Smart::log_warning('SmartFramework // FileSystem / Copy Dir: The Destination Dir is the same as Source Parent Dir: S='.$dir_name.' ; D='.$new_dir_name);
		return 0;
	} //end if
	if((string)substr($new_dir_name, 0, strlen($dir_name)) == (string)$dir_name) {
		Smart::log_warning('SmartFramework // FileSystem / Rename/Move Dir: The Destination Dir is inside the Source Dir: S='.$dir_name.' ; D='.$new_dir_name);
		return 0;
	} //end if
	if(!is_dir(SmartFileSysUtils::add_dir_last_slash(Smart::dir_name($new_dir_name)))) {
		Smart::log_warning('SmartFramework // FileSystem / Rename/Move Dir: The Destination Parent Dir is missing: P='.SmartFileSysUtils::add_dir_last_slash(Smart::dir_name($new_dir_name)).' of D='.$new_dir_name);
		return 0;
	} //end if
	//--
	@clearstatcache();
	//--
	$result = false;
	//--
	if(((string)$dir_name != (string)$new_dir_name) AND (SmartFileSysUtils::check_file_or_dir_name($dir_name)) AND (SmartFileSysUtils::check_file_or_dir_name($new_dir_name))) {
		if((is_dir($dir_name)) OR ((is_link($dir_name)) AND (is_dir(self::link_get_origin($dir_name))))) {
			if(!self::file_or_link_exists($new_dir_name)) {
				$result = @rename($dir_name, $new_dir_name);
			} //end if
		} //end if
	} //end if else
	//--
	if((!is_dir($new_dir_name)) OR ((is_link($new_dir_name)) AND (!is_dir(self::link_get_origin($new_dir_name))))) {
		Smart::log_warning('LibFileSys // RenameDir // FAILED to rename a directory: S='.$dir_name.' ; D='.$new_dir_name);
		return 0;
	} //end if
	if(self::file_or_link_exists($dir_name)) {
		Smart::log_warning('LibFileSys // RenameDir // Source DIR still exists: S='.$dir_name.' ; D='.$new_dir_name);
		return 0;
	} //end if
	//--
	if($result == true) {
		$out = 1;
	} else {
		$out = 0;
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================ DELETE DIRS
// delete a dir (simple or recursive)
// returns: 1 for success and 0 for error/fail
public static function dir_delete($dir_name, $recursive=true) {
	//--
	if((string)$dir_name == '') {
		Smart::log_warning('LibFileSys // DeleteDir // Dir Name is Empty !');
		return 0;
	} //end if
	//-- THIS MUST BE DONE BEFORE ADDING THE TRAILING SLASH
	if(is_link($dir_name)) {
		//--
		return self::delete($dir_name); // avoid deleting content from a linked dir, just remove the link
		//--
	} //end if
	//--
	$dir_name = SmartFileSysUtils::add_dir_last_slash($dir_name); // fix invalid path (must end with /)
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($dir_name);
	//--
	@clearstatcache();
	//--
	if(!self::file_or_link_exists($dir_name)) {
		//--
		return 1;
		//--
	} //end if
	//--
	if(is_link($dir_name)) { // {{{SYNC-BROKEN-LINK-DELETE}}}
		//--
		$f_cx = @unlink($dir_name);
		//--
		if(($f_cx) AND (!is_link($dir_name))) {
			return 1;
		} else {
			return 0;
		} //end if else
		//--
	} //end if
	//--
	$result = false;
	//-- remove all subdirs and files within
	if(SmartFileSysUtils::check_file_or_dir_name($dir_name)) {
		//--
		if((is_dir($dir_name)) AND (!is_link($dir_name))) {
			//--
			@chmod($dir_name, SMART_FRAMEWORK_CHMOD_DIRS); //apply chmod
			//--
			if($handle = opendir($dir_name)) {
				//--
				while(false !== ($file = readdir($handle))) {
					//--
					if(((string)$file != '.') AND ((string)$file != '..')) {
						//--
						if((is_dir($dir_name.$file)) AND (!is_link($dir_name.$file))) {
							//--
							if($recursive == true) {
								//--
								self::dir_delete($dir_name.$file, $recursive);
								//--
							} else {
								//--
								return 0; // not recursive and in this case sub-folders are not deleted
								//--
							} //end if else
							//--
						} else { // file or link
							//--
							self::delete($dir_name.$file);
							//--
						} //end if else
						//--
					} //end if
					//--
				} //end while
				//--
				@closedir($handle);
				//--
			} else {
				//--
				$result = false;
				Smart::log_warning('LibFileSys // DeleteDir // FAILED to open the directory: '.$dir_name);
				//--
			} //end if
			//-- finally, remove itself
			$result = @rmdir($dir_name);
			//--
		} else { // the rest of cases: is a file or a link
			//--
			$result = false;
			Smart::log_warning('LibFileSys // DeleteDir // This is not a directory: '.$dir_name);
			//--
		} //end if
		//--
	} //end if
	//--
	if(self::file_or_link_exists($dir_name)) { // last final check
		$result = false;
		Smart::log_warning('LibFileSys // DeleteDir // FAILED to delete a directory: '.$dir_name);
	} //end if
	//--
	if($result == true) {
		$out = 1;
	} else {
		$out = 0;
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
// compare two folders by number of dirs, files, links and the total size
// if second param is YES, will take in comparation also the .dot files
// if 3rd parameter is empty, will not compare folders or file names ; but if equal with * will also populate the pattern matches array with the file and folder names to compare
public static function compare_folders($dir1, $dir2, $include_dot_files=true, $recurring=true) {
	//-- get storage data for each folder
	$arr_dir1 = (array) (new SmartGetFileSystem(true))->get_storage($dir1, $recurring, $include_dot_files);
	$arr_dir2 = (array) (new SmartGetFileSystem(true))->get_storage($dir2, $recurring, $include_dot_files);
	//-- the above on error return empty array, so this error must be catched
	if(Smart::array_size($arr_dir1) <= 0) {
		return array('compare-error' => 'First Folder returned empty storage data: '.$dir1);
	} //end if
	if(Smart::array_size($arr_dir2) <= 0) {
		return array('compare-error' => 'Second Folder returned empty storage data: '.$dir2);
	} //end if
	//-- paths are not identical, so wipe out of compare
	unset($arr_dir1['path']);
	unset($arr_dir2['path']);
	//-- compute array diffs (must be on both directions)
	$arr_diff1 = array_diff_assoc($arr_dir1, $arr_dir2);
	$arr_diff2 = array_diff_assoc($arr_dir2, $arr_dir1);
	if((Smart::array_size($arr_diff1) > 0) OR (Smart::array_size($arr_diff2) > 0)) {
		return array('compare-error' => 'The two folders are not identical: '.$dir1.' [::] '.$dir2."\n".'@Diffs1: '.print_r($arr_diff1,1)."\n".'@Diffs2: '.print_r($arr_diff2,1)."\n".'@Dir1: '.print_r($arr_dir1,1)."\n".'@Dir2: '.print_r($arr_dir2,2));
	} //end if
	//--
	return array(); // this means no differences
	//--
} //END FUNCTION
//================================================================


//================================================================
// check a file by given filter
// returns 1/0 if valid or not ...
private static function test_filename_file_by_filter($file, $filter_fname, $filter_fext, $filter_special_extensions) {
	//--
	$out = 0;
	//--
	if(
		(
			(((string)$filter_fname == '') AND ((string)$filter_fext == '')) OR // both filters by name or extension are empty
			(((string)$filter_fname != '') AND (stripos($file, $filter_fname) !== false)) OR // filter by name
			(((string)$filter_fext != '') AND ((string)strtolower(SmartFileSysUtils::get_file_extension_from_path($file)) == (string)strtolower($filter_fext))) // filter by extension
		)
		AND (
			(Smart::array_size($filter_special_extensions) <= 0) OR
			((Smart::array_size($filter_special_extensions) > 0) AND (in_array(strtolower(SmartFileSysUtils::get_file_extension_from_path($file)), $filter_special_extensions)))
		)
	) {
		//--
		$out = 1;
		//--
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartGetFileSystem - provides the File System Get functions.
 *
 * <code>
 * // Usage example:
 * $filesys = new SmartGetFileSystem(true);
 * $data = $filesys->get_storage('my_dir/my_subdir');
 * print_r($data);
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.160128
 * @package 	Filesystem
 *
 */
final class SmartGetFileSystem {

	// ->


	//================================================================
	//--
	private $list_files_and_dirs	= false;
	//--
	private $num_size 				= 0;
	private $num_dirs_size			= 0;
	private $num_files_size			= 0;
	private $num_links 				= 0;
	private $num_dirs 				= 0;
	private $num_files 				= 0;
	private $pattern_file_matches 	= array();
	private $pattern_dir_matches 	= array();
	private $errors_arr 			= array();
	//--
	private $pattern_search_str		= '';
	private $search_prevent_file	= '';
	private $search_prevent_override = '';
	private $limit_search_files		= 0;
	//--
	//================================================================


	//================================================================
	public function __construct($list_files_and_dirs=false) { // CONSTRUCTOR
		//--
		if($list_files_and_dirs === true) {
			$this->list_files_and_dirs = true;
		} //end if
		//--
		$this->init_vars();
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function init_vars() {
		//--
		$this->num_size = 0;
		$this->num_dirs_size = 0;
		$this->num_files_size = 0;
		$this->num_links = 0;
		$this->num_dirs = 0;
		$this->num_files = 0;
		$this->pattern_file_matches = array();
		$this->pattern_dir_matches = array();
		$this->errors_arr = array();
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	public function get_storage($dir_name, $recurring=true, $include_dot_files=false, $search_pattern='') {
		//--
		$dir_name = (string) $dir_name;
		//--
		if((string)$dir_name == '') {
			Smart::log_warning('LibFileSys // GetStorage // Dir Name is Empty !');
			return array();
		} //end if
		//-- fix invalid path (must end with /)
		$dir_name = SmartFileSysUtils::add_dir_last_slash($dir_name);
		//-- protection
		SmartFileSysUtils::raise_error_if_unsafe_path($dir_name);
		//--
		$this->init_vars();
		//--
		$this->limit_search_files = 0;
		//-- get
		$this->folder_iterator($recurring, $dir_name, $include_dot_files, $search_pattern); // no search pattern (return only sizes)
		//-- sort dirs list descending by modified time, newest first
		$this->pattern_dir_matches = array_keys($this->pattern_dir_matches);
		natsort($this->pattern_dir_matches);
		$this->pattern_dir_matches = array_values($this->pattern_dir_matches);
		//-- sort files list descending by modified time, newest first
		$this->pattern_file_matches = array_keys($this->pattern_file_matches);
		natsort($this->pattern_file_matches);
		$this->pattern_file_matches = array_values($this->pattern_file_matches);
		//--
		$arr = array(); // {{{SYNC-SmartGetFileSystem-Output}}}
		//--
		$arr['quota'] 		= 0; //this will be set later
		$arr['path'] 		= $dir_name;
		$arr['reccuring'] 	= (string) $recurring;
		$arr['search@max-files'] = $this->limit_search_files;
		$arr['search@pattern'] = $this->pattern_search_str;
		$arr['restrict@dir-containing-file'] = $this->search_prevent_file;
		$arr['restrict@dir-override'] = $this->search_prevent_override;
		//--
		$arr['errors'] 		= (array) $this->errors_arr;
		//--
		$arr['size']		= $this->num_size;
		$arr['size-dirs']	= $this->num_dirs_size;
		$arr['size-files']	= $this->num_files_size;
		$arr['links'] 		= $this->num_links; // this is just for info, it is contained in the dirs or files num
		$arr['dirs'] 		= $this->num_dirs;
		$arr['files'] 		= $this->num_files;
		$arr['list#dirs'] 	= Smart::array_size($this->pattern_dir_matches);
		$arr['list#files']	= Smart::array_size($this->pattern_file_matches);
		$arr['list-dirs'] 	= (array) $this->pattern_dir_matches;
		$arr['list-files'] 	= $this->pattern_file_matches;
		//--
		return (array) $arr ;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	public function search_files($recurring, $dir_name, $include_dot_files, $search_pattern, $limit_search_files, $search_prevent_file='', $search_prevent_override='') {
		//--
		$dir_name = (string) $dir_name;
		//--
		if((string)$dir_name == '') {
			Smart::log_warning('LibFileSys // SearchFiles // Dir Name is Empty !');
			return array();
		} //end if
		//-- fix invalid path (must end with /)
		$dir_name = SmartFileSysUtils::add_dir_last_slash($dir_name);
		//-- protection
		SmartFileSysUtils::raise_error_if_unsafe_path($dir_name);
		//--
		$this->init_vars();
		//--
		$this->limit_search_files = Smart::format_number_int($limit_search_files, '+');
		//--
		$this->list_files_and_dirs = true;
		$this->folder_iterator($recurring, $dir_name, $include_dot_files, $search_pattern, $search_prevent_file, $search_prevent_override); // ! search pattern (return found files and dirs up to max matches)
		//-- sort dirs list descending by modified time, newest first
		arsort($this->pattern_dir_matches, SORT_NUMERIC);
		$this->pattern_dir_matches = array_keys($this->pattern_dir_matches);
		//-- sort files list descending by modified time, newest first
		arsort($this->pattern_file_matches, SORT_NUMERIC);
		$this->pattern_file_matches = array_keys($this->pattern_file_matches);
		//--
		$arr = array(); // {{{SYNC-SmartGetFileSystem-Output}}}
		//--
		$arr['quota'] 		= 0; //this will be set later
		$arr['path'] 		= $dir_name;
		$arr['reccuring'] 	= (string) $recurring;
		$arr['search@max-files'] = $this->limit_search_files;
		$arr['search@pattern'] = $this->pattern_search_str;
		$arr['restrict@dir-containing-file'] = $this->search_prevent_file;
		$arr['restrict@dir-override'] = $this->search_prevent_override;
		//--
		$arr['errors'] 		= $this->errors_arr;
		//--
		$arr['size']		= $this->num_size;
		$arr['size-dirs']	= $this->num_dirs_size;
		$arr['size-files']	= $this->num_files_size;
		$arr['links'] 		= $this->num_links; // this is just for info, it is contained in the dirs or files num
		$arr['dirs'] 		= $this->num_dirs;
		$arr['files'] 		= $this->num_files;
		$arr['list#dirs'] 	= Smart::array_size($this->pattern_dir_matches);
		$arr['list#files']	= Smart::array_size($this->pattern_file_matches);
		$arr['list-dirs'] 	= $this->pattern_dir_matches;
		$arr['list-files'] 	= $this->pattern_file_matches;
		//--
		return (array) $arr ;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private function folder_iterator($recurring, $dir_name, $include_dot_files, $search_pattern='', $search_prevent_file='', $search_prevent_override='') {
		//--
		$recurring = (bool) $recurring;
		$dir_name = (string) $dir_name;
		$include_dot_files = (bool) $include_dot_files;
		$search_pattern = (string) $search_pattern;
		$search_prevent_file = (string) $search_prevent_file;
		$search_prevent_override = (string) $search_prevent_override;
		//--
		if((string)$dir_name == '') {
			Smart::log_warning('LibFileSys // ReadsFolderRecurring // Dir Name is Empty !');
			return; // this function does not return anything, but just stop here in this case
		} //end if
		//-- fix invalid path (must end with /)
		$dir_name = SmartFileSysUtils::add_dir_last_slash($dir_name);
		//-- protection
		SmartFileSysUtils::raise_error_if_unsafe_path($dir_name);
		//--
		@clearstatcache();
		//--
		$this->pattern_search_str = $search_pattern;
		$this->search_prevent_file = $search_prevent_file;
		$this->search_prevent_override = $search_prevent_override;
		//--
		if((SmartFileSystem::file_or_link_exists($dir_name)) AND (!is_file($dir_name))) { // can be dir or link
			//list
			//--
			if($handle = opendir($dir_name)) {
				//---------------------------------------
				while(false !== ($file = readdir($handle))) {
					//--
					if(((string)$file != '.') AND ((string)$file != '..')) {
						//--
						if(($include_dot_files) OR ((!$include_dot_files) AND (substr($file, 0, 1) != '.'))) {
							//--
							SmartFileSysUtils::raise_error_if_unsafe_path($dir_name.$file);
							//-- params to see if counted or added to pattern matches
							$tmp_allow_addition = 1;
							$tmp_add_pattern = 0;
							//-- this is for #private folders, will prevent searching in folders containing for example this file: .private-folder but can be overriden by the $search_prevent_override option exluding a particular path like folder/private/user1
							if((strlen($search_prevent_file) > 0) AND (is_file($dir_name.$search_prevent_file))) {
								if((strlen($search_prevent_override) <= 0) OR ((strlen($search_prevent_override) > 0) AND (!is_file($dir_name.$search_prevent_override)))) {
									$tmp_allow_addition = 0;
								} //end if
							} //end if
							//-- this is a search pattern (search pattern does not apply to folders !!) ; if no empty will populate the pattern matches array with all files and folders matching ; to include all, use * or a particular search for the rest like myfile1
							if(((string)$search_pattern == '') OR (is_dir($dir_name.$file))) {
								if($tmp_allow_addition) {
									if($this->list_files_and_dirs) {
										$tmp_add_pattern = 1;
									} //end if
								} //end if
							} else {
								if(($this->limit_search_files <= 0) OR (Smart::array_size($this->pattern_file_matches) < $this->limit_search_files)) {
									if(((string)$search_pattern == '*') OR (((string)$search_pattern == '[image]') AND ((substr($file, -4, 4) == '.png') OR (substr($file, -4, 4) == '.gif') OR (substr($file, -4, 4) == '.jpg') OR (substr($file, -5, 5) == '.jpeg'))) OR (((string)$search_pattern != '*') AND ((string)$search_pattern != '[image]') AND (stripos($file, $search_pattern) !== false))) {
										if($tmp_allow_addition) {
											if($this->list_files_and_dirs) {
												$tmp_add_pattern = 1;
											} //end if
										} //end if
									} else {
										$tmp_allow_addition = 0;
									} //end if else
								} //end if
							} //end if
							//--
							if($this->limit_search_files > 0) { // the dir should not be taken in count here
								if(($this->num_files + $this->num_links) >= $this->limit_search_files) {
									break;
								} //end if
							} //end if
							//--
							if(!is_link($dir_name.$file)) {
								//--
								if(is_dir($dir_name.$file)) {
									//-- dir
									if($tmp_allow_addition) {
										//--
										$tmp_fsize = Smart::format_number_int(@filesize($dir_name.$file),'+');
										//--
										$this->num_dirs++;
										$this->num_size += $tmp_fsize;
										$this->num_dirs_size += $tmp_fsize;
										//--
										$tmp_fsize = 0;
										//--
										if($tmp_add_pattern) {
											if($recurring) { // if recurring, add the full path
												$this->pattern_dir_matches[$dir_name.$file] = @filemtime($dir_name.$file);
											} else { // if not recurring, add just base path, without dirname prefix
												$this->pattern_dir_matches[$file] = @filemtime($dir_name.$file);
											} //end if else
										} //end if
										//--
									} //end if
									//--
									if($recurring) {
										//-- we go search inside even if this folder name may not match the search pattern, it is a folder, except if dissalow addition from above
										$this->folder_iterator($recurring, SmartFileSysUtils::add_dir_last_slash($dir_name.$file), $include_dot_files, $search_pattern, $search_prevent_file, $search_prevent_override);
										//--
									} //end if
									//--
								} else {
									//-- file
									if($tmp_allow_addition) {
										//--
										$tmp_fsize = Smart::format_number_int(@filesize($dir_name.$file),'+');
										//--
										$this->num_files++;
										$this->num_size += $tmp_fsize;
										$this->num_files_size += $tmp_fsize;
										//--
										$tmp_fsize = 0;
										//--
										if($tmp_add_pattern) {
											if($recurring) { // if recurring, add the full path
												$this->pattern_file_matches[$dir_name.$file] = @filemtime($dir_name.$file);
											} else { // if not recurring, add just base path, without dirname prefix
												$this->pattern_file_matches[$file] = @filemtime($dir_name.$file);
											} //end if else
										} //end if
										//--
									} //end if
									//--
								} //end else
								//--
							} else {
								//-- link
								if($tmp_allow_addition) {
									//--
									$link_result = SmartFileSystem::link_get_origin($dir_name.$file);
									//--
									if(empty($link_result) OR ((string)$link_result == '') OR (!SmartFileSystem::file_or_link_exists($link_result))) {
										//--
										// case of readlink error ..., not includding broken links, they are useless
										//--
									} else {
										//--
										$tmp_size_arr = array();
										$tmp_fsize = 0;
										//$tmp_size_arr = (array) @lstat($dir_name.$file);
										//$tmp_fsize = Smart::format_number_int($tmp_size_arr[7],'+'); // $tmp_size_arr[7] -> size, but may break compare if on a different file system or in distributed storage on various OS
										//--
										$this->num_links++;
										//--
										if(file_exists($dir_name.$file)) { // here file_exists must be tested because if broken link not stat on it (filemtime) to avoid log un-necessary errors
											//-- bugfix: not if broken link
											$this->num_size += $tmp_fsize;
											if($tmp_add_pattern) {
												if(is_dir($dir_name.$file)) {
													$this->num_dirs++;
													$this->num_dirs_size += $tmp_fsize;
													if($recurring) { // if recurring, add the full path
														$this->pattern_dir_matches[$dir_name.$file] = @filemtime($dir_name.$file);
													} else { // if not recurring, add just base path, without dirname prefix
														$this->pattern_dir_matches[$file] = @filemtime($dir_name.$file);
													} //end if else
												} else {
													$this->num_files++;
													$this->num_files_size += $tmp_fsize;
													if($recurring) { // if recurring, add the full path
														$this->pattern_file_matches[$dir_name.$file] = @filemtime($dir_name.$file);
													} else { // if not recurring, add just base path, without dirname prefix
														$this->pattern_file_matches[$file] = @filemtime($dir_name.$file);
													} //end if else
												} //end if else
											} //end if
											//--
										} //end if
										//--
										$tmp_fsize = 0;
										$tmp_size_arr = array();
										//--
									} //end if else
									//--
								} //end if
								//--
							} //end if else
							//--
						} //end if
						//--
					} //end if(. ..)
					//--
				} //end while
				//---------------------------------------
				@closedir($handle);
				//---------------------------------------
			} else {
				//---------------------------------------
				$this->errors_arr[] = $dir_name;
				//---------------------------------------
			} //end else
			//--
		} else {
			//---------------------------------------
			// nothing ...
			//---------------------------------------
		} //end if else
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//----- USAGE
// $obj = new SmartGetFileSystem();
// $arr = $obj->get_storage('uploads/');
// $arr = $obj->search_files(true, 'uploads/', false, 'part of file', '100');
// print_r($arr);
//-----


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>