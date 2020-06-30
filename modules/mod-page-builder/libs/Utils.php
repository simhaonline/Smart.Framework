<?php
// Class: \SmartModExtLib\PageBuilder\Utils
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

namespace SmartModExtLib\PageBuilder;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


/**
 * Class: PageBuilder Utils
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20200630
 * @package 	PageBuilder
 *
 */
final class Utils {

	// ::

	const REGEX_PLACEHOLDERS 	= '/\{\{\:[A-Z0-9_\-\.]+\:\}\}/';
	const REGEX_MARKERS 		= '/\{\{\=\#[A-Z0-9_\-\.]+(\|[a-z0-9]+)*\#\=\}\}/';


	public static function getDbType() {
		//--
		$type = '';
		//--
		if(\defined('\\SMART_PAGEBUILDER_DB_TYPE')) {
			if((string)\SMART_PAGEBUILDER_DB_TYPE == 'sqlite') {
				$type = 'sqlite';
			} elseif(((string)\SMART_PAGEBUILDER_DB_TYPE == 'pgsql') AND (\Smart::array_size(\Smart::get_from_config('pgsql')) > 0)) {
				$type = 'pgsql';
			} //end if
		} //end if
		//--
		return (string) $type;
		//--
	} //END FUNCTION


	public static function allowPages() {
		//--
		$allow = true;
		//--
		if(\defined('\\SMART_PAGEBUILDER_DISABLE_PAGES')) {
			if(\SMART_PAGEBUILDER_DISABLE_PAGES === true) {
				$allow = false;
			} //end if
		} //end if
		//--
		return (bool) $allow;
		//--
	} //END FUNCTION


	public static function getAvailableLayouts() {
		//--
		$layouts = [];
		//--
		$layouts[''] = 'DEFAULT';
		//--
		$available_layouts = \Smart::get_from_config('pagebuilder.layouts');
		$cnt_available_layouts = (int) \Smart::array_size($available_layouts);
		if($cnt_available_layouts > 0) {
			if(\Smart::array_type_test($available_layouts) == 1) { // non-associative
				for($i=0; $i<$cnt_available_layouts; $i++) {
					$available_layouts[$i] = (string) \trim((string)$available_layouts[$i]);
					if((string)$available_layouts[$i] != '') {
						if(\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$available_layouts[$i])) {
							$layouts[(string)$available_layouts[$i]] = (string) $available_layouts[$i];
						} //end if
					} //end if
				} //end for
			} //end if
		} //end if
		//--
		return (array) $layouts;
		//--
	} //END FUNCTION


	public static function getMediaFolderRoot() {
		//--
		return (string) \Smart::safe_pathname('wpub/media-pbld/');
		//--
	} //END FUNCTION


	public static function getMediaFolderByObjectId($y_id) {
		//--
		return (string) \Smart::safe_pathname('wpub/media-pbld/'.\Smart::safe_filename(\str_replace('#', '@', (string)$y_id)).'/');
		//--
	} //END FUNCTION


	public static function getMediaFolderContent($y_media_dir) {
		//--
		$arr_imgs = array();
		//--
		if(\SmartFileSysUtils::check_if_safe_path($y_media_dir)) {
			if(\SmartFileSystem::is_type_dir($y_media_dir)) {
				$files_n_dirs = (array) (new \SmartGetFileSystem(true))->get_storage($y_media_dir, false, false);
				if(\Smart::array_size($files_n_dirs['list-files']) > 0) {
					for($i=0; $i<\Smart::array_size($files_n_dirs['list-files']); $i++) {
						$tmp_ext = (string) \substr((string)$files_n_dirs['list-files'][$i], -4, 4);
						switch((string)$tmp_ext) {
							case '.svg':
							case '.gif':
							case '.png':
							case '.jpg':
							// TODO: add support for webp
								$arr_imgs[] = [
									'img' 	=> (string) $y_media_dir.$files_n_dirs['list-files'][$i],
									'file' 	=> (string) $files_n_dirs['list-files'][$i],
									'type' 	=> (string) \substr((string)$tmp_ext, 1),
									'size' 	=> (string) \SmartUtils::pretty_print_bytes(\SmartFileSystem::get_file_size($y_media_dir.$files_n_dirs['list-files'][$i]), 1, '')
								];
								break;
							default:
								// skip
						} //end switch
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		return (array) $arr_imgs;
		//--
	} //END FUNCTION


	public static function fixSafeCode($y_html) {
		//--
		$y_html = (string) $y_html;
		//--
		$y_html = \SmartUtils::comment_php_code($y_html); // avoid PHP code
		$y_html = \str_replace([' />', '/>'], ['>', '>'], $y_html); // cleanup XHTML tag style
		//--
		return (string) $y_html;
		//--
	} //END FUNCTION


	public static function renderMarkdown($markdown_code) {
		//--
		return (string) \SmartMarkersTemplating::prepare_nosyntax_html_template(self::fixSafeCode((new \SmartMarkdownToHTML(true, true, true, false))->text((string)$markdown_code))); // Breaks=1,Markup=0,Links=1,Entities=1
		//--
	} //END FUNCTION


	public static function composePluginClassName($str) {
		//--
		$arr = (array) \explode('-', (string)$str);
		//--
		$class = '';
		//--
		for($i=0; $i<\Smart::array_size($arr); $i++) {
			//--
			$arr[$i] = (string) \trim((string)$arr[$i]);
			//--
			if((string)$arr[$i] != '') {
				//--
				$arr[$i] = (string) \strtolower((string)\Smart::safe_varname((string)$arr[$i])); // from camelcase to lower
				//--
				if((string)$arr[$i] != '') {
					$class .= (string) \ucfirst((string)$arr[$i]);
				} //end if
				//--
			} //end if
			//--
		} //end for
		//--
		return (string) $class;
		//--
	} //END FUNCTION


	public static function comparePlaceholdersAndMarkers($original_str, $transl_str) {
		//--
		$arr_placeholder_diffs 	= (array) self::comparePlaceholders($original_str, $transl_str);
		$arr_marker_diffs 		= (array) self::compareMarkers($original_str, $transl_str);
		//--
		return (array) \array_merge((array)$arr_placeholder_diffs, (array)$arr_marker_diffs);
		//--
	} //END FUNCTION


	public static function comparePlaceholders($original_str, $transl_str) {
		//--
		$original_arr 	= (array) self::extractPlaceholders((string)$original_str);
		$transl_arr 	= (array) self::extractPlaceholders((string)$transl_str);
		//--
		return (array) \array_diff($original_arr, $transl_arr);
		//--
	} //END FUNCTION


	public static function compareMarkers($original_str, $transl_str) {
		//--
		$original_arr 	= (array) self::extractMarkers((string)$original_str);
		$transl_arr 	= (array) self::extractMarkers((string)$transl_str);
		//--
		return (array) \array_diff($original_arr, $transl_arr);
		//--
	} //END FUNCTION


	//===== PRIVATES


	private static function extractPlaceholders($str) {
		//--
		$re = (string) self::REGEX_PLACEHOLDERS;
		//--
		\preg_match_all((string)$re, (string)$str, $matches);
		$arr = (array) \Smart::array_sort((array)$matches[0], 'natcasesort');
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


	private static function extractMarkers($str) {
		//--
		$re = (string) self::REGEX_MARKERS;
		//--
		\preg_match_all((string)$re, (string)$str, $matches);
		$arr = (array) \Smart::array_sort((array)$matches[0], 'natcasesort');
		//--
		return (array) $arr;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
