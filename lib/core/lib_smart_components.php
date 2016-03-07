<?php
// [LIB - SmartFramework / Smart Components]
// (c) 2006-2016 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_APP_BOOTSTRAP')) { // this must be defined in the first line of the application
	die('Invalid Runtime App Bootstrap Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Smart Components
// DEPENDS:
//	* Smart::
//	* SmartUtils::
//	* SmartFileSystem::
//	* SmartHTMLCalendar::
//	* SmartTextTranslations::
// REQUIRED JS LIBS:
//	* js/framework [arch-utils, browser-check, browser-utils, core-utils, crypt-utils, ifmodalbox, validate-input, page-away-control.inc]
//	* js/jquery [carousel, cleditor, dialog, growl, imgfx, listselect, pager, ratingstars, slickgrid, slimbox, timepicker, tree, ui, cookie, easing, event-drag, event-drop, idle, metadata, number, placeholder, simulate, smart.compat, smartframework.ui, sparkline, tinyscrollbar, qunit]
//	* js/jscharts [core, bar, doughnut, line, polararea, radar, stackedbar, stem]
//	* js/jseditcode [codemirror]
//	* js/jskeyboard
//	* js/jssuggest
// REQUIRED CSS:
//	* notifications.css
//	* activetable.css
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartComponents - provides various components for SmartFramework.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartUtils, SmartFileSystem, SmartHTMLCalendar, SmartTextTranslations
 * @version 	v.160307
 * @package 	Components:Framework
 *
 */
final class SmartComponents {

	// ::

//================================================================
/**
 * Function: Parse Language Based Settings
 * This is intended to pre-parse the Settings to select the proper language ...
 *
 * @access 		private
 * @internal
 *
 */
public static function parse_settings($arr_base_settings, $arr_local_settings) {
	//--
	$the_lang = SmartTextTranslations::getLanguage();
	//--
	if(is_array($arr_base_settings)) {
		//--
		if(is_array($arr_local_settings)) {
			//--
			foreach($arr_local_settings as $key => $val) {
				//--
				if(strlen($key) > 0) {
					//--
					if(array_key_exists($key, $arr_base_settings)) { // only if previous defined
						//--
						if((is_array($val)) AND ((string)$the_lang != '')) {
							//--
							if((string)$val['@language@'] == 'select') {
								//--
								$arr_base_settings[$key] = $val[(string)$the_lang];
								//--
								if((string)$arr_base_settings[$key] == '') { // if the current language have no value set
									//--
									foreach($val as $test_key => $test_val) { // try to get the first one
										//--
										if(((string)$test_key != '@language@') AND ((string)$test_val != '')) { // skip language select key ...
											//--
											$arr_base_settings[$key] = $test_val;
											//--
											break; // stop after first was found
											//--
										} //end if
										//--
									} //end foreach
									//--
								} //end if
								//--
							} else {
								//--
								$arr_base_settings[$key] = $val;
								//--
							} //end if else
							//--
						} else {
							//--
							$arr_base_settings[$key] = $val;
							//--
						} //end if else
						//--
					} //end if
					//--
				} //end if
				//--
			} //end foreach
			//--
		} //end if
		//--
	} //end if
	//--
	return $arr_base_settings;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Lock File Name
 *
 * @access 		private
 * @internal
 *
 */
public static function lock_file() {
	return '____SMART-FRAMEWORK_SingleUser_Mode__Enabled';
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: HTTP Status Message
 *
 * @access 		private
 * @internal
 *
 */
public static function http_status_message($y_title, $y_message) {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/http-message-status.inc.htm',
		[
			'CHARSET' => SmartUtils::get_encoding_charset(),
			'TITLE' => Smart::escape_html($y_title),
			'SIGNATURE' => '<b>SmartFramework // Web :: '.Smart::escape_html(SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION.' # '.SMART_SOFTWARE_APP_NAME.' / '.SMART_SOFTWARE_NAMESPACE).'</b>'.'<br>'.Smart::escape_html(SmartUtils::get_server_current_url().SmartUtils::get_server_current_script()),
			'MESSAGE' => $y_message
		],
		'no'
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: HTTP Error Message
 *
 * @access 		private
 * @internal
 *
 */
public static function http_error_message($y_title, $y_message, $y_extra_message='') {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/http-message-error.inc.htm',
		[
			'CHARSET' => SmartUtils::get_encoding_charset(),
			'TITLE' => Smart::escape_html($y_title),
			'SIGNATURE' => '<b>SmartFramework // Web :: '.Smart::escape_html(SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION.' # '.SMART_SOFTWARE_APP_NAME.' / '.SMART_SOFTWARE_NAMESPACE).'</b>'.'<br>'.Smart::escape_html(SmartUtils::get_server_current_url().SmartUtils::get_server_current_script()),
			'MESSAGE' => self::operation_error($y_message, '100%'),
			'EXTMSG' => $y_extra_message
		],
		'no'
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 400 Bad Request (The server cannot or will not process the request due to something that is perceived to be a client error (e.g., malformed request syntax, invalid request message framing, or deceptive request routing))
public static function http_message_400_badrequest($y_message, $y_extra_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'400.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'400.php');
			if(function_exists('custom_http_message_400_badrequest')) {
				return custom_http_message_400_badrequest($y_message, $y_extra_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('400 Bad Request', $y_message, $y_extra_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 401 Unauthorized (Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to the requested resource. See Basic access authentication and Digest access authentication)
public static function http_message_401_unauthorized($y_message, $y_extra_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'401.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'401.php');
			if(function_exists('custom_http_message_401_unauthorized')) {
				return custom_http_message_401_unauthorized($y_message, $y_extra_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('401 Unauthorized', $y_message, $y_extra_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 403 Forbidden (The request was a valid request, but the server is refusing to respond to it. Unlike a 401 Unauthorized response, authenticating will make no difference)
public static function http_message_403_forbidden($y_message, $y_extra_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'403.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'403.php');
			if(function_exists('custom_http_message_403_forbidden')) {
				return custom_http_message_403_forbidden($y_message, $y_extra_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('403 Forbidden', $y_message, $y_extra_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 404 Not Found (The requested resource could not be found but may be available again in the future. Subsequent requests by the client are permissible)
public static function http_message_404_notfound($y_message, $y_extra_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'404.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'404.php');
			if(function_exists('custom_http_message_404_notfound')) {
				return custom_http_message_404_notfound($y_message, $y_extra_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('404 Not Found', $y_message, $y_extra_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 500 Internal Server Error (A generic error message, given when an unexpected condition was encountered and no more specific message is suitable)
public static function http_message_500_internalerror($y_message, $y_extra_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'500.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'500.php');
			if(function_exists('custom_http_message_500_internalerror')) {
				return custom_http_message_500_internalerror($y_message, $y_extra_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('500 Internal Server Error', $y_message, $y_extra_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 503 Service Unavailable (The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a temporary state)
public static function http_message_503_serviceunavailable($y_message, $y_extra_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'503.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'503.php');
			if(function_exists('custom_http_message_503_serviceunavailable')) {
				return custom_http_message_503_serviceunavailable($y_message, $y_extra_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('503 Service Unavailable', $y_message, $y_extra_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Check if Single-User Mode is Enabled
 *
 * @access 		private
 * @internal
 *
 * @return 0/1
 */
public static function check_single_user() {
	//--
	$lock_file = self::lock_file();
	//--
	$out = 0;
	//--
	if(SmartFileSystem::file_or_link_exists($lock_file)) {
		//--
		$lock_content = SmartFileSystem::read($lock_file);
		$chk_arr = @explode("\n", trim($lock_content));
		$tmp_time = Smart::format_number_dec((($chk_arr[1] - time()) / 60), 0, '.', '');
		//--
		if($tmp_time <= 0) {
			$out = 1; // TOTAL LOCKED (if greater or equal than ZERO it only warns) !
		} //end if
		//--
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Manage a SINGLE Selection HTML List Element for Edit or Display data :: v.20141212
 *
 * @param STRING			$y_id					the HTML element ID
 * @param STRING 			$y_selected_value		selected value of the list
 * @param ENUM				$y_mode					'form' = display form | 'list' = display list
 * @param ARRAY				$yarr_data				DATASET ROWS AS: ['id' => 'name', 'id2' => 'name2'] OR ['id', 'name', 'id2', 'name2']
 * @param STRING 			$y_varname				as 'frm[test]'
 * @param INTEGER			$y_width				width in pixels
 * @param CODE				$y_custom_js			custom js code (Ex: submit on change)
 * @param YES/NO			$y_raw					If Yes, the description values will not apply html special chars
 * @param YES/NO			$y_allowblank			If Yes, a blank value is allowed in list
 * @param CSS/#JQUERY#		$y_extrastyle			Extra Style CSS | '#JQUERY#'
 * @param YES/NO			$y_jquery_filter  		Use Jquery Filter (only works with style '#JQUERY#' ; NOT WORK with Blank Value)
 *
 * @return HTMLCode
 */
public static function html_single_select_list($y_id, $y_selected_value, $y_mode, $yarr_data, $y_varname='', $y_dimensions='150/0', $y_custom_js='', $y_raw='no', $y_allowblank='yes', $y_extrastyle='') {

	//-- fix associative array
	$arr_type = Smart::array_type_test($yarr_data);
	if($arr_type === 2) { // associative array detected
		$arr_save = (array) $yarr_data;
		$yarr_data = array();
		foreach((array)$arr_save as $key => $val) {
			$yarr_data[] = (string) $key;
			$yarr_data[] = (string) $val;
		} //end foreach
		$arr_save = array();
	} //end if
	//--

	//--
	$tmp_dimens = @explode('/', trim($y_dimensions));
	//--
	$the_width = (int) $tmp_dimens[0];
	$the_height = (int) $tmp_dimens[1];
	//--
	if($the_width <= 0) {
		$the_width = 150;
	} //end if
	if($the_height < 0) {
		$the_height = 0;
	} //end if
	//--

	//--
	$element_id = Smart::escape_html(trim($y_id));
	//--

	//--
	$js = '';
	//--
	if(((string)$element_id != '') && (((string)$y_extrastyle == '#JQUERY#') || ((string)$y_extrastyle == '#JQUERY-FILTER#'))) {
		//--
		$tmp_extra_style = (string) $y_extrastyle;
		$y_extrastyle = ''; // reset
		//--
		if((string)$y_mode == 'form') {
			//--
			$the_width = $the_width + 20;
			if($the_height > 0) {
				if($the_height < 50) {
					$the_height = 50;
				} //end if
				if($the_height > 200) {
					$the_height = 200;
				} //end if
			} else {
				$the_height = (int) ((Smart::array_size($yarr_data) + 1) * 20);
				if($the_height > 200) {
					$the_height = 200;
				} //end if
			} //end if else
			//--
			if((string)$tmp_extra_style == '#JQUERY-FILTER#') {
				$have_filter = true;
				$the_width += 25;
			} else {
				$have_filter = false;
			} //end if else
			//--
			$js = (string) SmartMarkersTemplating::render_file_template(
				'lib/core/templates/ui-list-single.inc.htm',
				[
					'LANG' => (string) SmartTextTranslations::getLanguage(),
					'ID' => (string) $element_id,
					'WIDTH' => (int) $the_width,
					'HEIGHT' => (int) $the_height,
					'HAVE-FILTER' => (bool) $have_filter
				],
				'yes' // export to cache
			);
			//--
		} //end if else
		//--
	} //end if else
	//--

	//--
	$out = '';
	//--
	if((string)$y_mode == 'form') {
		//--
		$out .= '<select name="'.$y_varname.'" id="'.$element_id.'" size="1" style="width: '.$the_width.'px; '.$y_extrastyle.'" '.$y_custom_js.'>'."\n";
		//--
		if((string)$y_allowblank == 'yes') {
			$out .= '<option value="">&nbsp;</option>'."\n"; // we need a blank value to avoid wrong display of selected value
		} //end if
		//--
	} //end if
	//--
	$found = 0;
	for($i=0; $i<Smart::array_size($yarr_data); $i++) {
		//--
		$i_key = $i;
		$i_val = $i+1;
		$i=$i+1;
   		//--
   		if((string)$y_mode == 'form') {
   			//--
   			$tmp_sel = '';
   			//--
   			if((strlen($y_selected_value) > 0) AND ((string)$y_selected_value == (string)$yarr_data[$i_key])) {
				$tmp_sel = ' selected'; // single ID
   			} //end if
   			//--
   			if((string)$y_raw == 'yes') {
				$tmp_desc_val = $yarr_data[$i_val];
   			} else {
	   			$tmp_desc_val = Smart::escape_html($yarr_data[$i_val]);
   			} //end if else
   			//--
   			if((string)$yarr_data[$i_key] == '#OPTGROUP#') {
				$out .= '<optgroup label="'.$tmp_desc_val.'">'."\n"; // the optgroup
			} else {
				$out .= '<option value="'.Smart::escape_html($yarr_data[$i_key]).'"'.$tmp_sel.'>'.$tmp_desc_val.'</option>'."\n";
			} //end if else
			//--
   		} else {
   			//--
  			if(((string)$yarr_data[$i_val] != '') AND ((string)$y_selected_value == (string)$yarr_data[$i_key])) {
  				//-- single ID
	  			if((string)$y_raw == 'yes') {
  					$out .= $yarr_data[$i_val].'<br>'."\n";
	  			} else {
  					$out .= Smart::escape_html($yarr_data[$i_val]).'<br>'."\n";
	  			} //end if else
	  			//--
  				$found += 1;
  				//--
  			} //end if
  			//--
 		} //end if else
 		//--
   	} //end for
   	//--
	if((string)$y_mode == 'form') {
		//--
  		$out .= '</select>'."\n";
  		//--
  		$out .= $js."\n";
  		//--
	} else {
		//--
		if($found == 0) {
			if($y_allowblank != 'yes') {
				$out .= Smart::escape_html($y_selected_value).'<sup>?</sup>'.'<br>'."\n";
			} //end if
		} //end if
		//--
	} //end if
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//================================================================


//================================================================
/**
 * Generate a MULTIPLE (many selections) View/Edit List to manage ID Selections
 *
 * @param STRING			$y_id					the HTML element ID
 * @param STRING 			$y_selected_value		selected value(s) data as ARRAY or STRING list as: '<id1>,<id2>'
 * @param ENUM				$y_mode					'form' = display form | checkboxes | 'list' = display list
 * @param ARRAY				$yarr_data				DATASET ROWS AS: ['id' => 'name', 'id2' => 'name2'] OR ['id', 'name', 'id2', 'name2']
 * @param STRING 			$y_varname				as 'frm[test][]'
 * @param ENUM				$y_draw 				list | checkboxes
 * @param YES/NO 			$y_sync_values			If Yes, sync select similar values used (curently works only for checkboxes)
 * @param INTEGER			$y_width				width in pixels (Only for jQuery Style)
 * @param CODE				$y_custom_js			custom js code (Ex: submit on change)
 * @param SPECIAL			$y_extrastyle			Extra Style CSS | '#JQUERY#' | '#JQUERY-FILTER#'
 *
 * @return HTMLCode
 */
public static function html_multi_select_list($y_id, $y_selected_value, $y_mode, $yarr_data, $y_varname='', $y_draw='list', $y_sync_values='no', $y_dimensions='300/0', $y_custom_js='', $y_extrastyle='#JQUERY-FILTER#') {

	//-- fix associative array
	$arr_type = Smart::array_type_test($yarr_data);
	if($arr_type === 2) { // associative array detected
		$arr_save = (array) $yarr_data;
		$yarr_data = array();
		foreach((array)$arr_save as $key => $val) {
			$yarr_data[] = (string) $key;
			$yarr_data[] = (string) $val;
		} //end foreach
		$arr_save = array();
	} //end if
	//--

	//-- bug fix
	if(Smart::array_size($yarr_data) > 2) {
		$use_multi_list_jq = true;
		$use_multi_list_htm = 'multiple size="8"';
	} else {
		$use_multi_list_jq = false;
		$use_multi_list_htm = 'size="1"';
	} //end if else
	//--

	//--
	$tmp_dimens = @explode('/', trim($y_dimensions));
	$the_width = (int) $tmp_dimens[0];
	$the_height = (int) $tmp_dimens[1];
	//--
	if($the_width <= 0) {
		$the_width = 150;
	} //end if
	if($the_height < 0) {
		$the_height = 0;
	} //end if
	//--

	//--
	$element_id = Smart::escape_html($y_id);
	//--

	//--
	if(((string)$element_id != '') && (((string)$y_extrastyle == '#JQUERY#') || ((string)$y_extrastyle == '#JQUERY-FILTER#'))) {
		//--
		$use_blank_value = 'no';
		//--
		$tmp_extra_style = (string) $y_extrastyle;
		$y_extrastyle = ''; // reset
		//--
		if((string)$y_mode == 'form') {
			//--
			if($the_height > 0) {
				if($the_height < 50) {
					$the_height = 50;
				} //end if
				if($the_height > 200) {
					$the_height = 200;
				} //end if
			} else {
				$the_height = (int) ((Smart::array_size($yarr_data) + 1) * 20);
				if($the_height > 200) {
					$the_height = 200;
				} //end if
			} //end if else
			//--
			if((string)$tmp_extra_style == '#JQUERY-FILTER#') {
				$have_filter = true;
				$the_width += 25;
			} else {
				$have_filter = false;
			} //end if else
			//--
			if($use_multi_list_jq === false) {
				$have_filter = false; // if multi will be enforced to single because of just 2 rows or less, disable filter !
			} //end if
			//--
			$js = (string) SmartMarkersTemplating::render_file_template(
				'lib/core/templates/ui-list-multi.inc.htm',
				[
					'LANG' => (string) SmartTextTranslations::getLanguage(),
					'ID' => (string) $element_id,
					'WIDTH' => (int) $the_width,
					'HEIGHT' => (int) $the_height,
					'USE-JQ' => (bool) $use_multi_list_jq,
					'HAVE-FILTER' => (bool) $have_filter
				],
				'yes' // export to cache
			);
			//--
		} //end if
		//--
	} else {
		//--
		$use_blank_value = 'yes';
		//--
		$js = '';
		//--
	} //end if else
	//--

	//--
	if($use_multi_list_jq === false) {
		$use_blank_value = 'yes';
	} //emd if
	//--

	//--
	$out = '';
	//--
	if((string)$y_mode == 'form') {
		//--
		if((string)$y_draw == 'checkboxes') { // checkboxes
			$out .= '<input type="hidden" name="'.$y_varname.'" value="">'."\n"; // we need a hidden value
		} else { // list
			$out .= '<select name="'.$y_varname.'" id="'.$element_id.'" '.$use_multi_list_htm.' '.$y_custom_js.'>'."\n";
			if((string)$use_blank_value == 'yes') {
				$out .= '<option value="">&nbsp;</option>'."\n"; // we need a blank value to unselect
			} //end if
		} //end if else
		//--
	} //end if
	//--
	for($i=0; $i<Smart::array_size($yarr_data); $i++) {
		//--
		$i_key = $i;
		$i_val = $i+1;
		$i=$i+1;
		//--
		if((string)$y_mode == 'form') {
			//--
			$tmp_el_id = 'SmartFrameworkComponents_MultiSelect_ID__'.sha1($y_varname.$yarr_data[$i_key]);
			//--
			$tmp_sel = '';
			$tmp_checked = '';
			//--
			if(is_array($y_selected_value)) {
				//--
				if(in_array($yarr_data[$i_key], $y_selected_value)) {
					//--
					$tmp_sel = ' selected';
					$tmp_checked = ' checked';
					//--
				} //end if
				//--
			} else {
				//--
				if(SmartUnicode::str_icontains($y_selected_value, '<'.$yarr_data[$i_key].'>')) { // multiple categs as <id1>,<id2>
					//--
					$tmp_sel = ' selected';
					$tmp_checked = ' checked';
					//--
				} //end if
				//--
			} //end if
			//--
			if((string)$y_draw == 'checkboxes') { // checkboxes
				//--
				if((string)$y_sync_values == 'yes') {
					$tmp_onclick = ' onClick="SmartJS_BrowserUtils.checkAll_CkBoxes(this.form.name, \''.Smart::escape_html($tmp_el_id).'\', this.checked);"';
				} else {
					$tmp_onclick = '';
				} //end if else
				//--
				$out .= '<input type="checkbox" name="'.$y_varname.'" id="'.Smart::escape_html($tmp_el_id).'" value="'.Smart::escape_html($yarr_data[$i_key]).'"'.$tmp_checked.$tmp_onclick.'>';
				$out .= ' &nbsp; '.Smart::escape_html($yarr_data[$i_val]).'<br>';
				//--
			} else { // list
				//--
				if((string)$yarr_data[$i_key] == '#OPTGROUP#') {
					$out .= '<optgroup label="'.Smart::escape_html($yarr_data[$i_val]).'">'."\n"; // the optgroup
				} else {
					$out .= '<option value="'.Smart::escape_html($yarr_data[$i_key]).'"'.$tmp_sel.'>&nbsp;'.Smart::escape_html($yarr_data[$i_val]).'</option>'."\n";
				} //end if else
				//--
			} //end if else
			//--
		} else {
			//--
			if(is_array($y_selected_value)) {
				//--
				if(in_array($yarr_data[$i_key], $y_selected_value)) {
					//--
					$out .= '&middot;&nbsp;'.Smart::escape_html($yarr_data[$i_val]).'<br>'."\n";
					//--
				} //end if
				//--
			} else {
				//--
				if(SmartUnicode::str_icontains($y_selected_value, '<'.$yarr_data[$i_key].'>')) {
					//-- multiple categs as <id1>,<id2>
					$out .= '&middot;&nbsp;'.Smart::escape_html($yarr_data[$i_val]).'<br>'."\n";
					//--
				} // end if
				//--
			} //end if else
			//--
		} //end if else
		//--
	} //end for
	//--
	if((string)$y_mode == 'form') {
		//--
		if((string)$y_draw == 'checkboxes') { // checkboxes
			$out .= '<br>'."\n";
		} else { // list
			$out .= '</select>'."\n";
			$out .= $js."\n";
		} //end if else
		//--
	} //end if
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//================================================================


//================================================================
/**
 * Draw a rounded table in CSS3
 *
 * @param STRING $htmlcode		HTML Code to display within table
 * @param STRING $class			CSS Class for rounded table
 * @param STRING $y_width		Table width (px / %)
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[HTML Code]
 */
public static function rounded_table($htmlcode, $class, $y_width='100%', $y_bgcolor='', $y_valign='middle', $y_height='') {
	//--
	if((string)$y_width == '100%') {
		$y_width = '99%'; // fix
	} //end if
	//--
	if(substr($y_bgcolor, 0, 1) == '#') {
		$color = ' class="rounded" bgcolor="'.$y_bgcolor.'"';
	} else {
		$color = ' class="rounded '.$class.'"';
	} //end if
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/rounded-table.inc.htm',
		[
			'WIDTH' => $y_width,
			'HEIGHT' => $y_height,
			'STYLE-BGCOLOR' => $color,
			'V-ALIGN' => $y_valign,
			'HTML-CONTENT' => $htmlcode
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Format CSS Width
 * Format the CSS Width: Passed number (550) or percent (100%) and return the correct CSS3 format as 550px or 100%
 *
 * @access 		private
 * @internal
 *
 */
public static function format_css_width($y_width) {
	//--
	if(strpos($y_width, '%') !== false) {
		//--
		$css_width = (string) $y_width; // Ex: 100%
		//--
	} elseif(strlen($y_width) > 0) {
		//--
		$y_width = (int) $y_width;
		//--
		if($y_width < 1) {
			$y_width = 1;
		} //end if
		if($y_width > 3200) {
			$y_width = 3200;
		} //end if
		//--
		$css_width = (string) $y_width.'px'; // Ex: 750px
		//--
	} else {
		//--
		$css_width = '100px';
		//--
	} //end if else
	//--
	return $css_width;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Notifications Message Template
 *
 * @access 		private
 * @internal
 *
 */
private static function notifications_template($y_html, $y_sign, $y_sgnstyle, $y_style, $y_width) {
	//--
	$y_width = self::format_css_width($y_width);
	//--
	if(((string)$y_width == '100%') OR ((string)$y_width == '99%') OR ((string)$y_width == '98%')) {
		$y_width = '97%'; // correction because of the margin
	} //end if
	//--
	return '<!-- require: notifications.css --><div align="center"><div style="width:'.$y_width.';"><div id="'.$y_style.'"><div style="'.$y_sgnstyle.'"><img src="'.$y_sign.'" style="vertical-align:middle; padding-left:10px;"></div>'.$y_html.'</div></div></div>';
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_question($y_html, $y_width='550') {
	//--
	return self::notifications_template($y_html, 'lib/core/img/sign_quest.png', 'float:right; line-height:40px;', 'operation_question', $y_width); // question
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_notice($y_html, $y_width='550') {
	//--
	return self::notifications_template($y_html, 'lib/core/img/sign_notice.png', 'float:right; line-height:40px;', 'operation_notice', $y_width); // notice
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_ok($y_html, $y_width='550') {
	//--
	return self::notifications_template($y_html, 'lib/core/img/sign_info.png', 'float:right; line-height:40px;', 'operation_info', $y_width); // info (ok)
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_warn($y_html, $y_width='550') {
	//--
	return self::notifications_template($y_html, 'lib/core/img/sign_warn.png', 'float:right; line-height:40px;', 'operation_warn', $y_width); // warn
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_error($y_html, $y_width='550') {
	//--
	return self::notifications_template($y_html, 'lib/core/img/sign_error.png', 'float:right; line-height:40px;', 'operation_error', $y_width); // error
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return JavaScripting for <tr> table ROW as: title="" id="" onClick="" onMouseOver="" onMouseOut=""
 * The above <tr> params must not be used in conjunction with this function to avoid unexpected results
 *
 * @param #css_style $y_bg_color		:: ID css name BG Color
 * @param #css_style $y_hover_color		:: ID css name Hover Color
 * @param #css_style $y_click_color		:: ID css name Click Color
 * @param ENUM $y_selected				:: '' = not selected | '*' = selected
 *
 * @access 		private
 * @internal
 *
 * @return STRING		HTML code as JavaScript for Active Table Row
 *
 */
public static function table_active_row($y_bg_color, $y_hover_color, $y_click_color, $y_selected='') {
	//-- [v.150105]
	if(strlen($y_selected) > 0) {
		$y_selected = '*';
	} //end if
	//--
	return 'title="" id="'.Smart::escape_html($y_bg_color).'" onClick="if(this.title==\'\') { this.title=\'*\'; this.id=\''.Smart::escape_js($y_click_color).'\'; } else { this.title=\'\'; }" onMouseOver="this.id=\''.Smart::escape_js($y_hover_color).'\';" onMouseOut="if(this.title==\'\') { this.id=\''.Smart::escape_js($y_bg_color).'\'; } else { this.id=\''.Smart::escape_js($y_click_color).'\'; }"';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Create an active table
 *
 * @access 		private
 * @internal
 *
 * @param STRING $y_size		[the table size px / %]
 * @param STRING $y_title		[the table title]
 * @param ARRAY $y_arr_addlink	[associative, 'link', 'title'] :: REPLACEMENTS {{{id}}}
 * @param ARRAY $y_arr_edtlink	[associative, 'link', 'title'] :: REPLACEMENTS {{{id}}}
 * @param ARRAY $y_arr_dellink	[associative, 'link', 'title'] :: REPLACEMENTS {{{id}}}
 * @param ARRAY $y_arr_fields	[non-associative, fields]
 * @param ARRAY $y_arr_process	[non-associative array(0, 1, 2, ...), process ($tmp_id / $value) | associative array( 'objects' => array(0, 1, 2, ...), 'handlers' => array('obj1' => $obj1, obj2=>$obj2, ...) )]
 * @param ARRAY $y_arr_data		[non-associative, data]
 *
 * @return STRING				[html code]
 */
public static function table($y_size, $y_title, $y_arr_addlink, $y_arr_edtlink, $y_arr_dellink, $y_arr_fields, $y_arr_process, $y_arr_data, $y_arr_align=array(), $y_arr_width=array(), $y_form_action=array(), $y_form_actoptions=array(), $y_wnd_js='', $y_form_name='smart_table_form', $y_arr_styles=array('heading'=>'smart_active_table_heading', 'subheading'=>'smart_active_table_subheading', 'hilite'=>'smart_active_table_row_hilite', 'alt1'=>'smart_active_table_row_alt1', 'alt2'=>'smart_active_table_row_alt2', 'click'=>'smart_active_table_row_click')) {

	// [v.150105]
	// now can handle passed objects

	//$y_form_actoptions = array(array('button_img'=>'lib/core/img/op_insert.png', 'button_act'=>'action2', 'button_title'=>'Some Action'), array('button_img'=>'lib/core/img/op_edit.png', 'button_act'=>'eeee', 'button_title'=>'Some Other Action'));

	//-- protection
	if(Smart::array_size($y_arr_fields) <= 0) {
		return self::operation_error('WARNING: Build Table has been invoked with a Zero Fields Array !');
	} //end if
	//--

	//-- transform the $y_arr_process for handle objects
	// ths $y_arr_process can be either:
	// DEFAULT: array(0, 1, 2, ...) // non associative
	// ADVANCED: array(
	//				'objects' => array(0, 1, 2, ...), // non associative
	//				'handlers' => array('obj1' => $obj1, obj2=>$obj2, ...)
	//			)
	//--
	if(is_array($y_arr_process)) {
		//--
		if((is_array($y_arr_process['handlers'])) OR (is_array($y_arr_process['objects']))) {
			//--
			if(is_array($y_arr_process['handlers'])) {
				$the_arr_process = (array) $y_arr_process['handlers'];
			} else {
				$the_arr_process = array();
			} //end if else
			//--
			if(is_array($y_arr_process['objects'])) {
				//--
				foreach($y_arr_process['objects'] as $key => $val) {
					eval("\n".'$obj__'.$key.' = &$val;'."\n"); // register local objects
				} //end foreach
				//--
			} //end if else
			//-- restore back
			$y_arr_process = array();
			$y_arr_process = (array) $the_arr_process;
			//--
		} //end if
		//--
	} else {
		//--
		$y_arr_process = array();
		//--
	} //end if
	//--

	//--
	if(((string)$y_wnd_js == '') OR ((string)$y_wnd_js == 'smart_table_detail')) {
		$y_wnd_js = 'smart_table_detail_'.sha1($y_title);
	} else {
		$y_wnd_js = (string) $y_wnd_js;
	} //end if else
	//--

	//-------------------------------------------------------- INITS
	//--
	$color_title 		= $y_arr_styles['heading'];		// #ECECEC
	$color_subtitle		= $y_arr_styles['subheading'];	// #FFFFFF
	$bgcolor_highlight 	= $y_arr_styles['hilite'];		// #DDEEFF
	$alt_color_one 		= $y_arr_styles['alt1']; 		// #FAFAFA
	$alt_color_two 		= $y_arr_styles['alt2'];		// #F3F3F3
	$click_color 		= $y_arr_styles['click'];		// #FFCC00
	//--
	//--------------------------------------------------------
	$out = '';
	//-------------------------------------------------------- START
	$tbl_plus_cols = 2;
	//--
	$translator_core_window = SmartTextTranslations::getTranslator('@core', 'window');
	//--
	if(Smart::array_size($y_form_action) > 0) {
		//--
		if((string)$y_form_action['method'] == '') {
			$y_form_action['method'] = 'post';
			$y_form_action['enctype'] = 'multipart/form-data';
		} //end if
		//-
		if((string)$y_form_action['target'] == '') {
			$y_form_action['target'] = '_self';
		} //end if
		//--
		if((string)$y_form_action['jscript'] == '') {
			$y_form_action['jscript'] = 'onClick=" '.self::js_draw_html_confirm_form_submit($translator_core_window->text('confirm_action')).' "';
		} //end if
		//--
		if((string)$y_form_action['button_img'] == '') {
			$y_form_action['button_img'] = 'lib/core/img/op_delete.png';
		} //end if
		//--
		$out .= '<form class="ux-form" name="'.Smart::escape_html($y_form_name).'" action="'.Smart::escape_html($y_form_action['action']).'" method="'.Smart::escape_html($y_form_action['method']).'" enctype="'.Smart::escape_html($y_form_action['enctype']).'" target="'.Smart::escape_html($y_form_action['target']).'">'."\n";
		//--
	} //end if else
	//--
	if(Smart::array_size($y_arr_dellink) <= 0) {
		$tbl_plus_cols = $tbl_plus_cols - 1;
	} //end if
	if(Smart::array_size($y_arr_edtlink) <= 0) {
		$tbl_plus_cols = $tbl_plus_cols - 1;
	} //end if
	if(Smart::array_size($y_arr_addlink) > 0) {
		if((Smart::array_size($y_arr_dellink) > 0) AND (Smart::array_size($y_arr_edtlink) > 0)) {
			$tbl_plus_cols = 2; // fix again
		} else {
			$tbl_plus_cols = 1; // fix again
		} //end if else
	} //end if
	//--
	$out .= '<!-- require: activetable.css -->'."\n";
	$out .= '<div style="width:100%;">'."\n"; // style += overflow:auto;
	$out .= '<script type="text/javascript">var Smart_Table_Notification_ID;</script>'."\n";
	$out .= '<table id="'.Smart::escape_html($color_subtitle).'" align="center" width="'.Smart::escape_html($y_size).'" border="0" cellspacing="1" cellpadding="2" title="'.Smart::escape_html(Smart::striptags($y_title, 'no')).'">'."\n";
	//-------------------------------------------------------- TITLE
	$out .= '<tr id="'.Smart::escape_html($color_title).'">'."\n";
	//--
	if(Smart::array_size($y_arr_addlink) > 0) {
		//--
		if((Smart::array_size($y_arr_dellink) > 0) AND (Smart::array_size($y_arr_edtlink) > 0)) {
			$out .= '<td valign="middle" align="center" colspan="2">'."\n";
		} else {
			$out .= '<td valign="middle" align="center">'."\n";
		} //end if else
		//--
		if((string)$y_arr_addlink['target'] != '') {
			$new_tgt = $y_arr_addlink['target'];
		} else {
			$new_tgt = $y_wnd_js;
		} //end if else
		//--
		if(strlen($y_arr_addlink['custom_picture']) > 0) {
			eval("\n".$y_arr_addlink['custom_picture']."\n");
			$the_add_pict = $add_custom_picture;
		} else {
			$the_add_pict = 'lib/core/img/op_insert.png';
		} //end if else
		//--
		if((Smart::format_number_int($y_arr_addlink['width']) > 0) AND (Smart::format_number_int($y_arr_addlink['height']) > 0)) {
			$the_onclick = 'SmartJS_BrowserUtils.PopUpLink(this.href, this.target, '.Smart::format_number_int($y_arr_addlink['width']).', '.Smart::format_number_int($y_arr_addlink['height']).', 0, 1);';
		} else {
			$the_onclick = 'SmartJS_BrowserUtils.PopUpLink(this.href, this.target);';
		} //end if else
		//--
		$out .= '<a href="'.Smart::escape_html(str_replace('{{{id}}}', '', $y_arr_addlink['link'])).'" target="'.Smart::escape_html($new_tgt).'" onClick="'.$the_onclick.' return false;"><img src="'.Smart::escape_html($the_add_pict).'" border="0" title="'.$y_arr_addlink['title'].'" alt="'.$y_arr_addlink['title'].'"></a>';
		//--
		$out .= '</td>'."\n";
		//--
	} else {
		//--
		if((int)$tbl_plus_cols > 0) {
			//--
			if((int)$tbl_plus_cols > 1) {
				$out .= '<td valign="middle" align="center" colspan="'.$tbl_plus_cols.'">'."\n";
			} else {
				$out .= '<td valign="middle" align="center">'."\n";
			} //end if else
			$out .= '&nbsp;';
			$out .= '</td>'."\n";
		} //end if
		//--
	} //end if else
	//--
	$out .= '<td width="99%" align="left" valign="middle" colspan="'.(Smart::array_size($y_arr_fields)).'">'."\n";
	//--
	if(strpos($y_title, '<') !== false) {
		$out .= $y_title; // is html
	} elseif((string)$y_title != '') {
		$out .= '<h1>&nbsp; '.$y_title.' &nbsp;</h1>';
	} //end if else
	//--
	$out .= '</td>'."\n";
	$out .= '</tr>'."\n";
	//-------------------------------------------------------- EXTRA BUTTONS
	$extra_bttns = '';
	if(Smart::array_size($y_form_action) > 0) {
		$extra_bttns .= '<td><table width="50%" cellpadding="0" cellspacing="0">';
		if(Smart::array_size($y_form_actoptions) > 0) {
			$extra_bttns .= '<tr>'."\n";
			$extra_bttns .= '<td align="left" valign="middle" colspan="'.(Smart::array_size($y_arr_fields)).'">'."\n";
			for($i=0; $i<Smart::array_size($y_form_actoptions); $i++) {
				$the_actopts_arr = $y_form_actoptions[$i];
				if((string)$the_actopts_arr['jscript'] == '') {
					$the_actopts_arr['jscript'] = 'onClick=" '.self::js_draw_html_confirm_form_submit($translator_core_window->text('confirm_action')).' "';
				} //end if
				$extra_bttns .= '<input type="image" id="bttn_f_action'.$i.'" name="bttn_f_action" value="'.Smart::escape_html($the_actopts_arr['button_act']).'" src="'.Smart::escape_html($the_actopts_arr['button_img']).'" '.$the_actopts_arr['jscript'].' style="border:none" title="'.$the_actopts_arr['button_title'].'" alt="'.$the_actopts_arr['button_title'].'">&nbsp;';
			} //end for
			$extra_bttns .= '</td>'."\n";
			$extra_bttns .= '</tr>'."\n";
		} //end if
		$extra_bttns .= '</table></td>';
		//--
		$the_hd_size_del = '32';
		$the_hd_size_edt = '32';
		//--
	} else {
		//--
		if(Smart::array_size($y_arr_dellink) > 0) {
			$the_hd_size_del = '32';
		} else {
			$the_hd_size_del = '1';
		} //end if else
		//--
		if(Smart::array_size($y_arr_edtlink) > 0) {
			$the_hd_size_edt = '32';
		} else {
			$the_hd_size_edt = '1';
		} //end if else
		//--
	} //end if else
	//-------------------------------------------------------- HEADING
	$out .= '<tr>'."\n";
	if(Smart::array_size($y_form_action) > 0) {
		$out .= '<td width="'.Smart::format_number_int($the_hd_size_del+$the_hd_size_edt).'" align="center" colspan="2">'."\n";
		$out .= '<table border="0" cellspacing="0" cellpadding="2"><tr>';
		$out .= '<td width="'.Smart::format_number_int($the_hd_size_del).'"><input type="checkbox" name="'.Smart::escape_html($y_form_name).'_toggle" value="" onClick="this.checked=!this.checked;'.self::js_draw_checkbox_checkall($y_form_name).'">'.'</td>'."\n";
		$out .= '<td width="'.Smart::format_number_int($the_hd_size_edt).'"><input type="image" id="bttn_f_action" name="bttn_f_action" value="'.Smart::escape_html($y_form_action['button_act']).'" src="'.Smart::escape_html($y_form_action['button_img']).'" '.$y_form_action['jscript'].' style="border:none" title="'.$y_form_action['button_title'].'" alt="'.$y_form_action['button_title'].'"></td>'."\n";
		$out .= '</tr></table>';
		$out .= '</td>'."\n";
	} else {
		if((Smart::array_size($y_arr_dellink) > 0) AND (Smart::array_size($y_arr_edtlink) > 0)) {
			$out .= '<td width="'.Smart::format_number_int($the_hd_size_del+$the_hd_size_edt).'" align="center" colspan="2">'."\n";
			$out .= '';
			$out .= '</td>'."\n";
		} elseif(Smart::array_size($y_arr_dellink) > 0) {
			$out .= '<td width="'.Smart::format_number_int($the_hd_size_del).'" align="center">'."\n";
			$out .= '';
			$out .= '</td>'."\n";
		} elseif(Smart::array_size($y_arr_edtlink) > 0) {
			$out .= '<td width="'.Smart::format_number_int($the_hd_size_edt).'" align="center">'."\n";
			$out .= '';
			$out .= '</td>'."\n";
		} else {
			// nothing
		} //end if else
	} //end if else
	//--
	for($i=0; $i<Smart::array_size($y_arr_fields); $i++) {
		$out .= '<td align="center" valign="middle" width="'.Smart::escape_html($y_arr_width[$i]).'">'."\n";
		$out .= '<table width="100%" border="0"><tr>';
		if($i==0) {
			if(Smart::array_size($y_form_action) > 0) {
				$out .= $extra_bttns;
			} //end if
		} //end if
		$out .= '<td align="center"><b>'.$y_arr_fields[$i].'</b></td>';
		$out .= '</tr></table>';
		$out .= '</td>'."\n";
	} //end for
	//--
	$out .= '</tr>'."\n";
	//-------------------------------------------------------- DATA
	$the_alt_cnt = 0;
	for($n=0; $n<Smart::array_size($y_arr_data); $n++) {
		//--
		$the_alt_cnt += 1;
		if($the_alt_cnt % 2) {
			$the_alt_color = $alt_color_two;
		} else {
			$the_alt_color = $alt_color_one;
		} //end else
		//--
		$tmp_id = $y_arr_data[$n]; // the id must be get first
		//--
		$out .= '<tr valign="top" '.self::table_active_row($the_alt_color, $bgcolor_highlight, $click_color).'>'."\n";
		//--
		if(Smart::array_size($y_form_action) > 0) {
			//--
			$out .= '<td align="center" width="'.Smart::format_number_int($the_hd_size_del).'">'."\n";
			$out .= '<input type="checkbox" name="id[]" value="'.Smart::escape_html($tmp_id).'">';
			$out .= '</td>'."\n";
			//--
		} else {
			//--
			if(Smart::array_size($y_arr_dellink) > 0) {
				//--
				$out .= '<td align="center" width="'.Smart::format_number_int($the_hd_size_del).'" onClick="this.parentNode.title=\'**\';">'."\n";
				//--
				if(strlen($y_arr_dellink['custom_picture']) > 0) {
					eval("\n".$y_arr_dellink['custom_picture']."\n");
					$the_del_pict = $delete_custom_picture;
				} else {
					$the_del_pict = 'lib/core/img/op_delete.png';
				} //end if else
				//--
				if((Smart::format_number_int($y_arr_dellink['width']) > 0) AND (Smart::format_number_int($y_arr_dellink['height']) > 0)) {
					$the_onclick = 'SmartJS_BrowserUtils.PopUpLink(this.href, this.target, '.Smart::format_number_int($y_arr_dellink['width']).', '.Smart::format_number_int($y_arr_dellink['height']).', 0, 1);';
				} else {
					$the_onclick = 'SmartJS_BrowserUtils.PopUpLink(this.href, this.target);';
				} //end if else
				//--
				$out .= '<a href="'.Smart::escape_html(str_replace('{{{id}}}', rawurlencode($tmp_id), $y_arr_dellink['link'])).'" target="'.'del__'.Smart::escape_html($y_wnd_js).'" onClick="'.$the_onclick.' return false;"><img src="'.Smart::escape_html($the_del_pict).'" border="0" title="'.$y_arr_dellink['title'].'" alt="'.$y_arr_dellink['title'].'"></a>';
				//--
				$out .= '</td>'."\n";
				//--
			} //end if
			//--
		} //end if else
		//--
		if(Smart::array_size($y_arr_edtlink) > 0) {
			//--
			$out .= '<td align="center" width="'.Smart::format_number_int($the_hd_size_edt).'" onClick="this.parentNode.title=\'**\';">'."\n"; // disable onclick for parent row (fix)
			//--
			if(strlen($y_arr_edtlink['custom_picture']) > 0) {
				eval("\n".$y_arr_edtlink['custom_picture']."\n");
				$the_edt_pict = $edit_custom_picture;
			} else {
				$the_edt_pict = 'lib/core/img/op_edit.png';
			} //end if else
			//--
			if((Smart::format_number_int($y_arr_edtlink['width']) > 0) AND (Smart::format_number_int($y_arr_edtlink['height']) > 0)) {
				$the_onclick = 'SmartJS_BrowserUtils.PopUpLink(this.href, this.target, '.Smart::format_number_int($y_arr_edtlink['width']).', '.Smart::format_number_int($y_arr_edtlink['height']).', 0, 1);';
			} else {
				$the_onclick = 'SmartJS_BrowserUtils.PopUpLink(this.href, this.target);';
			} //end if else
			//--
			$out .= '<a href="'.Smart::escape_html(str_replace('{{{id}}}', rawurlencode($tmp_id), $y_arr_edtlink['link'])).'" target="'.'edt__'.Smart::escape_html($y_wnd_js).'_'.sha1(date('Y-m-d H:i:s').$tmp_id).'" onClick="'.$the_onclick.' return false;"><img src="'.Smart::escape_html($the_edt_pict).'" border="0" title="'.$y_arr_edtlink['title'].'" alt="'.$y_arr_edtlink['title'].'"></a>';
			//--
			$out .= '</td>'."\n";
			//--
		} //end if
		//--
		$a = 0;
		for($i=0; $i<Smart::array_size($y_arr_fields); $i++) {
			//--
			$the_align = 'center';
			if((string)$y_arr_align[$a] != '') {
				$the_align = $y_arr_align[$a];
			} // end if
			//--
			$a += 1;
			//--
			$kk = $i + $n;
			$value = $y_arr_data[$kk];
			//--
			$out .= '<td align="'.$the_align.'">'."\n";
			if((string)$y_arr_process[$i] == '') {
				$out .= Smart::escape_html($value);
			} else {
				eval("\n".$y_arr_process[$i]."\n");
				$out .= $value;
			} //end if else
			//--
			$out .= '</td>'."\n";
			//--
		} //end for
		//--
		$out .= '</tr>'."\n";
		//--
		$n += (Smart::array_size($y_arr_fields)-1); // salt
		//--
	} //end for
	//-------------------------------------------------------- END LINE
	$out .= '<tr id="'.$color_title.'">'."\n";
	//--
	if((int)$tbl_plus_cols > 0) {
		//--
		if((int)$tbl_plus_cols > 1) {
			$out .= '<td valign="middle" align="center" colspan="'.$tbl_plus_cols.'">'."\n";
		} else {
			$out .= '<td valign="middle" align="center">'."\n";
		} //end if else
		$out .= '<font size="1">&nbsp;</font>';
		$out .= '</td>'."\n";
		//--
	} else {
		// nothing
	} //end if else
	//--
	$out .= '<td width="99%" align="center" valign="middle" colspan="'.(Smart::array_size($y_arr_fields)).'">'."\n";
	$out .= '<font size="1">&nbsp;</font>';
	$out .= '</td>'."\n";
	$out .= '</tr>'."\n";
	//-------------------------------------------------------- END
	$out .= '</table>'."\n";
	$out .= '</div>'."\n";
	//--
	if(Smart::array_size($y_form_action) > 0) {
		$out .= '</form>'."\n";
	} //end if
	//--------------------------------------------------------

	//--
	return $out;
	//--

} //END FUNCTION
//================================================================


//================================================================
/**
 * Create a static table
 *
 * @access 		private
 * @internal
 *
 * @return STRING		HTML code
 *
 */
public static function table_ncolumns($arr_data, $ycols, $ytitle='', $y_counter='no', $y_width='', $y_cellspacing='8', $y_align='center', $y_firstisheader='no', $y_bgcolors='') {

	// v.141009.0945

	// $y_counter :: yes / no / full

	//-- $y_bgcolors ::
	//		''
	//		array(
	//			'bg' => '#FCFCFC',
	//			'text-head-color' => '#FFFFFF',
	//			'header' => '#778899',
	//			'text-color' => '#000000',
	//			'color1' => '#ECECEC',
	//			'color2' => '#CCCCCC'
	//		)
	//--

	//--
	if(!is_array($arr_data)) {
		return '';
	} else {
		$arr_data = array_values($arr_data);
	} //end if else
	//--

	//--
	$bg_color = '';
	//--
	if(is_array($y_bgcolors)) {
		if((string)$y_bgcolors['bg'] != '') {
			$bg_color = 'bgcolor="'.$y_bgcolors['bg'].'" ';
		} //end if
	} //end if
	//--

	//--
	$ycols = (int) 0 + $ycols;
	//--
	if($ycols < 1) {
		$ycols = 1;
	} //end if
	//--
	$tmp_cell_w_percent = floor((1 / $ycols) * 100).'%';
	//--
	$max_loops = count($arr_data);
	//--

	//--
	$out = "\n".'<!-- START nColumns TABLE -->'."\n";
	$out .= '<table '.$bg_color.'border="0" align="'.$y_align.'" width="'.$y_width.'" cellpadding="4" cellspacing="0">';
	//--
	if(strlen($ytitle) > 0) {
		$out .= '<tr><th><div align="left">'.$ytitle.'</div></th></tr>';
	} //end if
	//--
	$out .= '<tr><td>'."\n";
	//--

	//--
	$alt_cols = 0;
	//--
	if($max_loops > 0) {
		//--
		$out .= '<table width="100%" border="0" cellpadding="2" cellspacing="'.$y_cellspacing.'" align="'.$y_align.'">'."\n";
		//--
		for($i=0;$i<$max_loops; $i++) {
			//-- start row
			if(($i % $ycols) == 0) {
				//-- alternate color
				if(is_array($y_bgcolors)) {
					//--
					$tmp_text_color = '#000000'; // in the case it is not defined
					//--
					if((string)$y_bgcolors['text-color'] != '') {
						$tmp_text_color = (string) $y_bgcolors['text-color'];
					} //end if
					//--
					if((string)$y_firstisheader == 'yes') {
						//--
						if($alt_cols <= 0) { // header
							if((string)$y_bgcolors['text-head-color'] != '') {
								$tmp_text_color = (string) $y_bgcolors['text-head-color'];
							} //end if
							$tmp_alt_color = (string) $y_bgcolors['header'];
						} else {
							if($alt_cols % 2) {
								$tmp_alt_color = (string) $y_bgcolors['color1'];
							} else {
								$tmp_alt_color = (string) $y_bgcolors['color2'];
							} //end if else
						} //end if else
						//--
					} else {
						//--
						if($alt_cols % 2) {
							$tmp_alt_color = (string) $y_bgcolors['color2'];
						} else {
							$tmp_alt_color = (string) $y_bgcolors['color1'];
						} //end if else
						//--
					} //end if else
					//--
				} else {
					//--
					$tmp_alt_color = '';
					//--
				} //end if
				//--
				$out .= '<!-- row -->';
				//--
				if(((string)$tmp_alt_color == '') OR ((string)$tmp_text_color == '')) {
					$out .= "\n".'<tr title="#'.($alt_cols+1).'" valign="top">'."\n";
				} else {
					$out .= "\n".'<tr title="#'.($alt_cols+1).'" valign="top" style="color:'.$tmp_text_color.'; background-color:'.$tmp_alt_color.';">'."\n";
				} //end if else
				//--
				$alt_cols++;
				//--
			} //end if
			//--
			$out .= '<td width="'.$tmp_cell_w_percent.'">';
			$out .= $arr_data[$i]."\n";
			$out .= '</td>'."\n";
			//-- end row
			if((($i+1) % $ycols) == 0) {
				$out .= '</tr>'."\n"; // end of row
			} //end if
			//--
		} //end for
		//--
		$out .= '</table>'."\n";
		//--
	} //end if
	//--

	//--
	$out .= '</td></tr>'."\n";
	//--
	if((string)$y_counter == 'yes') {
		$out .= '<tr><td colspan="'.$ycols.'" title="#'.Smart::escape_html($max_loops).'">&nbsp;</td></tr>'."\n";
	} elseif((string)$y_counter == 'full') {
		$out .= '<tr><td colspan="'.$ycols.'" align="center">'.'<b>#'.Smart::escape_html($max_loops).'</b></td></tr>'."\n";
	} //end if
	//--
	$out .= '</table>'."\n";
	$out .= '<!-- END nColumns TABLE -->'."\n";
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//================================================================


//================================================================
/**
 * Creates a navigation pager
 * The style of the pager can be set overall in: $configs['nav']['pager'], and can be: arrows or numeric
 *
 * @hints			$link = 'some-script.php?ofs={{{offset}}}';
 *
 * @return HTML Code
 *
 */
public static function navpager($link, $total, $limit, $current, $display_if_empty=false, $adjacents=3) {
	//--
	global $configs;
	//--
	$styles = '<!-- require: navbox.css -->'."\n";
	//--
	if((string)$configs['nav']['pager'] == 'arrows') {
		return $styles.self::arrows_navpager($link, $total, $limit, $current, $display_if_empty, $adjacents);
	} else {
		return $styles.self::numeric_navpager($link, $total, $limit, $current, $display_if_empty, $adjacents);
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
// $link = 'some-script.php?ofs={{{offset}}}';
private static function arrows_navpager($link, $total, $limit, $current, $display_if_empty=false, $adjacents=3) {
	//--
	$link = (string) $link;
	//--
	$translator_core_nav_texts = SmartTextTranslations::getTranslator('@core', 'nav_texts');
	//--
	$limit = Smart::format_number_int($limit, '+');
	$current = Smart::format_number_int($current, '+');
	$total = Smart::format_number_int($total, '+');
	//--
	$txt_start 	= '<div class="nav_box_start" title="'.$translator_core_nav_texts->text('start').'"></div>';
	$txt_prev 	= '<div class="nav_box_prev" title="'.$translator_core_nav_texts->text('prev').'"></div>';
	$txt_next 	= '<div class="nav_box_next" title="'.$translator_core_nav_texts->text('next').'"></div>';
	$txt_end 	= '<div class="nav_box_end" title="'.$translator_core_nav_texts->text('end').'"></div>';
	//--
	$txt_listed = (string) $translator_core_nav_texts->text('listed'); // Page
	$txt_empty = (string) $translator_core_nav_texts->text('empty'); // No Results
	$txt_of = (string) $translator_core_nav_texts->text('of'); // of
	//--
	if($limit > 0) {
		//--
		if($total > 0) {
			//--
			$tmp_lst_min = $current + 1;
			$tmp_lst_max = $current + $limit;
			//--
			$dys_next = $current + $limit;
			$dys_prev = $current - $limit;
			//--
			if($dys_prev < 0) {
				$dys_prev = 0;
			} //end if
			if($dys_prev > $total) {
				$dys_prev = $total;
			} //end if
			//--
			if($dys_next < 0) {
				$dys_next = 0;
			} //end if
			if($dys_next > $total) {
				$dys_next = $total;
			} //end if
			if($dys_next == 0) {
				$dys_prev = 0;
				$tmp_lst_min = 0;
				$tmp_lst_max = 0;
			} //end if
			//-- Fix max nav
			if($tmp_lst_max > $total) {
				$tmp_lst_max = $total;
			} //end if
			//-- info
			$tmp_nfo = '<div title="'.Smart::escape_html($tmp_lst_min.'-'.$tmp_lst_max.' / '.$total).'">&nbsp;&nbsp;'.$txt_listed.'&nbsp;'.ceil($tmp_lst_max / $limit).'&nbsp;'.$txt_of.'&nbsp;'.ceil($total / $limit).'&nbsp;&nbsp;</div>';
			//-- FFW
			$tmp_last_calc_pages = @floor((($total - 1) / $limit));
			$tmp_lastpage = $tmp_last_calc_pages * $limit;
			//-- REW
			$tmp_firstpage = 0;
			//--
			$tmp_link_nav_start = (string) str_replace('{{{offset}}}', $tmp_firstpage, $link);
			$tmp_link_nav_prev = (string) str_replace('{{{offset}}}', $dys_prev, $link);
			$tmp_link_nav_next = (string) str_replace('{{{offset}}}', $dys_next, $link);
			$tmp_link_nav_end = (string) str_replace('{{{offset}}}', $tmp_lastpage, $link);
			//--
			$tmp_box_nav_start = '<a class="nav_box_link" href="'.Smart::escape_html($tmp_link_nav_start).'">'.$txt_start.'</a>';
			$tmp_box_nav_prev = '<a class="nav_box_link" href="'.Smart::escape_html($tmp_link_nav_prev).'">'.$txt_prev.'</a>';
			$tmp_box_nav_next = '<a class="nav_box_link" href="'.Smart::escape_html($tmp_link_nav_next).'">'.$txt_next.'</a>';
			$tmp_box_nav_end = '<a class="nav_box_link" href="'.Smart::escape_html($tmp_link_nav_end).'">'.$txt_end.'</a>';
			//--
			if($current <= 0) { // is at start
				$tmp_box_nav_start = $txt_start;
				$tmp_box_nav_prev = $txt_prev;
			} //end if
			if($tmp_lst_max >= $total) { // is at end
				$tmp_box_nav_next = $txt_next;
				$tmp_box_nav_end = $txt_end;
			} //end if
			//--
			$html = (string) SmartMarkersTemplating::render_file_template(
				'lib/core/templates/nav-box.inc.htm',
				[
					'NAV-START' => $tmp_box_nav_start,
					'NAV-PREV' => $tmp_box_nav_prev,
					'NAV-NEXT' => $tmp_box_nav_next,
					'NAV-END' => $tmp_box_nav_end,
					'NAV-INFO' => $tmp_nfo
				],
				'yes' // export to cache
			);
			//--
		} else {
			//--
			if($display_if_empty !== false) {
				//--
				$html = (string) SmartMarkersTemplating::render_file_template(
					'lib/core/templates/nav-box.inc.htm',
					[
						'NAV-START' => $txt_start,
						'NAV-PREV' => $txt_prev,
						'NAV-NEXT' => $txt_next,
						'NAV-END' => $txt_end,
						'NAV-INFO' => '<div title="'.Smart::escape_html('0-0 / 0').'">&nbsp;&nbsp;'.$txt_empty.'&nbsp;&nbsp;</div>'
					],
					'yes' // export to cache
				);
				//--
			} else {
				//--
				$html = '<!-- Navigation Pager (1) '.'T='.Smart::escape_html($total).' ; '.'L='.Smart::escape_html($limit).' ; '.'C='.Smart::escape_html($current).' --><div>&nbsp;</div><!-- hidden, all results are shown (just one page) --><!-- #END# Navigation Pager -->'; // total is zero or lower than limit ; no pagination in this case
				//--
			} //end if
			//--
		} //end if else
		//--
	} else {
		//--
		$html = '[ ERROR: Invalid Navigation Pager (1): Limit is ZERO ]';
		//--
	} //end if else
	//--
	return (string) $html;
	//--
} //END FUNCTION
//================================================================


//================================================================
// $link = 'some-script.php?ofs={{{offset}}}';
private static function numeric_navpager($link, $total, $limit, $current, $display_if_empty=false, $adjacents=3) {
	//--
	$link = (string) $link;
	$total = Smart::format_number_int($total, '+');
	$limit = Smart::format_number_int($limit, '+');
	$current = Smart::format_number_int($current, '+');
	$adjacents = Smart::format_number_int($adjacents, '+');
	//--
	if($limit <= 0) {
		return (string) '<!-- Navigation Pager (2) -->[ ERROR: Invalid Navigation Pager (2): Limit is ZERO ]<!-- #END# Navigation Pager -->';
	} //end if
	if($adjacents <= 0) {
		$adjacents = 2;
	} //end if
	//--
	if(($total <= 0) OR ($total <= $limit)) {
		return (string) '<!-- Navigation Pager (2) '.'T='.Smart::escape_html($total).' ; '.'L='.Smart::escape_html($limit).' ; '.'C='.Smart::escape_html($current).' --><div>&nbsp;</div><!-- hidden, all results are shown (just one page) --><!-- #END# Navigation Pager -->'; // total is zero or lower than limit ; no pagination in this case
	} //end if
	//--
	$min = 1;
	//--
	$max = ceil($total / $limit);
	if($max < 1) {
		$max = 1;
	} //end if
	//--
	$info_current = $current;
	$info_max = ($current + $limit);
	if($info_max > $total) {
		$info_max = $total;
	} //end if
	//--
	$crr = ceil($current / $limit) + 1;
	if($crr < $min) {
		$crr = $min;
	} //end if
	if($crr > $max) {
		$crr = $max;
	} //end if
	//--
	$backmin = $crr - $adjacents;
	if($backmin < $min) {
		$backmin = $min;
	} //end if
	$backmax = $crr + $adjacents;
	if($backmax > $max) {
		$backmax = $max;
	} //end if
	//--
	$arr = array();
	for($i=($backmin+1); $i<$backmax; $i++) {
		$arr[(string)$i] = $i;
	} //end for
	//--
	$html = '';
	foreach($arr as $key => $val) {
		if((int)$val === (int)$crr) {
			$html .= '<span id="nav-pager-current" title="'.Smart::escape_html($val.' # '.$info_current.' - '.$info_max).'">'.Smart::escape_html($key).'</span> ';
		} else {
			$html .= '<a id="nav-pager-item" href="'.Smart::escape_html(str_replace('{{{offset}}}', (int)(($val-1)*$limit), (string)$link)).'" title="'.Smart::escape_html($val).'">'.Smart::escape_html($key).'</a> ';
		} //end if else
	} //end foreach
	//--
	if((string)$arr[(string)$min] == '') {
		if((int)$min === (int)$crr) {
			$htext = '<span id="nav-pager-current" title="'.Smart::escape_html($min.' # '.$info_current.' - '.$info_max).'">'.Smart::escape_html($min).'</span> ';
		} else {
			$htext = '<a id="nav-pager-item" href="'.Smart::escape_html(str_replace('{{{offset}}}', (int)(($min-1)*$limit), (string)$link)).'" title="'.Smart::escape_html($min).'">'.Smart::escape_html($min).'</a> ';
		} //end if else
		if(($max > ($adjacents + 1)) AND ((string)$arr[(string)($min+1)] == '')) {
			$html = $htext.'<span id="nav-pager-dots">...</span> '.$html;
		} else {
			$html = $htext.$html;
		} //end if else
	} //end if
	//--
	if((string)$arr[(string)$max] == '') {
		if((int)$max === (int)$crr) {
			$htext = '<span id="nav-pager-current" title="'.Smart::escape_html($max.' # '.$info_current.' - '.$info_max).'">'.Smart::escape_html($max).'</span> ';
		} else {
			$htext = '<a id="nav-pager-item" href="'.Smart::escape_html(str_replace('{{{offset}}}', (int)(($max-1)*$limit), (string)$link)).'" title="'.Smart::escape_html($max).'">'.Smart::escape_html($max).'</a> ';
		} //end if else
		if(($max > ($adjacents + 1)) AND ((string)$arr[(string)($max-1)] == '')) {
			$html = (string) $html.'<span id="nav-pager-dots">...</span> '.$htext;
		} else {
			$html = (string) $html.$htext;
		} //end if else
	} //end if
	//--
	return (string) '<!-- Navigation Pager (2) '.'T='.Smart::escape_html($total).' ; '.'L='.Smart::escape_html($limit).' ; '.'C='.Smart::escape_html($current).' --><div id="nav-pager-box">'.$html.'</div><!-- #END# Navigation Pager -->';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Window PopUp Init
 * NOTICE: If mixing http:// with https:// to avoid errors will force the PopUp instead of modal !!
 *
 * @access 		private
 * @internal
 *
 */
private static function window_mode_init_smartpopup() {
	return '<script type="text/javascript">SmartJS_BrowserUtils_Use_iFModalBox_Active = 0;</script>';
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Window Modal Init
 * NOTICE: If mixing http:// with https:// to avoid errors will force the PopUp instead of modal !!
 *
 * @access 		private
 * @internal
 *
 */
private static function window_mode_init_smartmodal() {
	return '<script type="text/javascript">SmartJS_BrowserUtils_Use_iFModalBox_Active = 1; SmartJS_BrowserUtils.Control_ModalCascading();</script>';
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Window Modal Protection Init
 *
 * @access 		private
 * @internal
 *
 */
private static function window_mode_init_protection_to_smartmodal() {
	return '<script type="text/javascript">SmartJS_BrowserUtils_Use_iFModalBox_Protection = 1;</script>';
} //END FUNCTION
//================================================================


//================================================================
/**
 * Post Form by Ajax / JQuery
 *
 * @param $y_form_id 			HTML form ID (Example: myForm)
 * @param $y_script_url 		the php script to post to (Example: admin.php)
 * @param $y_confirm_question 	if not empty will ask a confirmation question
 *
 * @return STRING				[javascript code]
 */
public static function post_form_by_ajax($y_form_id, $y_script_url, $y_confirm_question='') {
	//--
	global $configs;
	//--
	if((string)$configs['js']['notifications'] == 'growl') {
		$tmp_use_growl = 'yes';
	} else {
		$tmp_use_growl = 'no';
	} //end if else
	//--
	$js_post = 'SmartJS_BrowserUtils.Submit_Form_By_Ajax(\''.Smart::escape_js($y_form_id).'\', \''.Smart::escape_js($y_script_url).'\', \''.Smart::escape_js($tmp_use_growl).'\');';
	//--
	if(strlen($y_confirm_question) > 0) {
		$js_post = self::js_draw_html_confirm_dialog($y_confirm_question, $js_post);
	} else {
		$js_post = $js_post;
	} //end if else
	//--
	return $js_post;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Answer Post Form by Ajax / JQuery
 *
 * NOTICE:
 * - if OK: and redirect URL have been provided, the replace div is not handled
 * - if ERROR: no replace div or redirect is handled
 *
 * @param 	$y_status 			OK / ERROR
 * @param 	$y_title 			Dialog Title
 * @param 	$y_message 			Dialog Message (Optional in the case of OK)
 * @param 	$y_redirect_url 	**OPTIONAL** URL to redirect in the case of OK
 * @param 	$y_replace_div 		**OPTIONAL** The ID of the DIV to Replace
 * @param 	$y_replace_html 	**OPTIONAL** the HTML Code to replace in DIV
 *
 * @return STRING				[javascript code]
 *
 */
public static function post_answer_by_ajax($y_status, $y_title, $y_message, $y_redirect_url='', $y_replace_div='', $y_replace_html='') {
	//--
	$translator_core_messages = SmartTextTranslations::getTranslator('@core', 'messages');
	//--
	if((string)$y_status == 'OK') {
		$y_status = 'OK';
		$button_text = $translator_core_messages->text('ok');
	} else {
		$y_status = 'ERROR';
		$button_text = $translator_core_messages->text('cancel');
	} //end if else
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		$y_redirect_url = ''; // avoid redirect if DEBUG IS ON to catch the debug messages ...
	} //end if
	//--
	return Smart::json_encode(array('completed'=>'DONE', 'status'=>$y_status, 'action'=>$button_text, 'title'=>$y_title, 'message'=>base64_encode($y_message), 'redirect'=>$y_redirect_url, 'replace_div'=>$y_replace_div, 'replace_html'=>base64_encode($y_replace_html)));
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draw Close Window Button with Confirmation or Not by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[javascript code]
 */
public static function draw_btn_close_confirm_window($y_bttn_id, $y_confirm_close='no') {
	//--
	$translator_core_window = SmartTextTranslations::getTranslator('@core', 'window');
	//--
	if((string)$y_confirm_close == 'yes') {
		$action = self::js_draw_html_confirm_dialog($translator_core_window->text('confirm_action'), 'SmartJS_BrowserUtils_PageAway=true; SmartJS_BrowserUtils.CloseModalPopUp();');
	} else {
		$action = 'SmartJS_BrowserUtils.CloseModalPopUp();';
	} //end if else
	//--
	$out = '<input type="button" id="'.Smart::escape_html($y_bttn_id).'" value="'.$translator_core_window->text('button_modal_close').'" onClick="'.$action.' return false;">';
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Scroll Down Window by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[html code]
 */
public static function scroll_down_window($y_offset) {
	//-- if offset is -1 will gotoEnd
	return '<script type="text/javascript">SmartJS_BrowserUtils.windwScrollDown(window, '.intval($y_offset).');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Refresh Parent Window by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[html code]
 */
public static function close_window($y_timeout=750) {
	//--
	$y_timeout = (int) $y_timeout;
	if($y_timeout < 1) {
		$y_timeout = 1;
	} //end if
	if($y_timeout > 10000) {
		$y_timeout = 10000;
	} //end if
	//--
	$out = '';
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
		$out = '<script type="text/javascript">SmartJS_BrowserUtils.CloseDelayedModalPopUp('.intval($y_timeout).');</script>';
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * AutoRefresh Window by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[html code]
 */
public static function autorefresh($y_link, $y_miliseconds='10000') {
	//--
	$y_miliseconds = (int) $y_miliseconds;
	if($y_miliseconds < 1) {
		$y_miliseconds = 1;
	} //end if
	if($y_miliseconds > 3600000) {
		$y_miliseconds = 3600000;
	} //end if
	//-- we wish to disable modal in this case to avoid losing modal window by refresh
	return '<script type="text/javascript">SmartJS_BrowserUtils_Use_iFModalBox_Active = 0; SmartJS_BrowserUtils.RedirectDelayedToURL(\''.Smart::escape_js($y_link).'\', '.intval($y_miliseconds).');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Refresh Parent Window by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[html code]
 */
public static function refresh_parent($y_custom_url='') {
	//--
	if((string)$y_custom_url != '') {
		$out = '<script type="text/javascript">SmartJS_BrowserUtils.RefreshParent(\''.Smart::escape_js($y_custom_url).'\');</script>';
	} else {
		$out = '<script type="text/javascript">SmartJS_BrowserUtils.RefreshParent();</script>';
	} //end if else
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Redirect Parent Window by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[html code]
 */
public static function redirect_parent($y_custom_url) {
	//--
	return '<script type="text/javascript">SmartJS_BrowserUtils.RedirectParent(\''.Smart::escape_js($y_custom_url).'\');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Redirect Parent Window by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[html code]
 */
public static function redirect_btn_parent($y_custom_url) {
	//--
	return 'SmartJS_BrowserUtils.RedirectParent(\''.Smart::escape_js($y_custom_url).'\');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Redirect a Page by JavaScript
 *
 * @access 		private
 * @internal
 *
 * @param STRING $y_location (URL to redirect :: ABSOLUTE or RELATIVE)
 * @param INTEGER $y_timeout (time in miliseconds :: 1000 ms = 1 s)
 * @return HTML code
 */
public static function redirect_page($y_location, $y_timeout='1000') {
	//--
	$y_timeout = (int) $y_timeout;
	if($y_timeout < 1) {
		$y_timeout = 1;
	} //end if
	if($y_timeout > 10000) {
		$y_timeout = 10000;
	} //end if
	//--
	return '<script type="text/javascript">SmartJS_BrowserUtils.RedirectDelayedToURL(\''.Smart::escape_js($y_location).'\', '.intval($y_timeout).');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Javascript to Redirect a Window by Button
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[html code]
 */
public static function redirect_btn_page($y_location) {
	//--
	return 'SmartJS_BrowserUtils.RedirectToURL(\''.Smart::escape_js($y_location).'\');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Javascript to Check All Checkboxes
 *
 * @access 		private
 * @internal
 *
 * @return STRING				[js code]
 */
public static function js_draw_checkbox_checkall($y_form_name) {
	//--
	return 'SmartJS_BrowserUtils.checkAll_CkBoxes(\''.Smart::escape_js($y_form_name).'\');';
	//--
} //END FORM
//================================================================


//================================================================
/**
 * Function: JS Include Settings
 *
 * @access 		private
 * @internal
 *
 */
public static function js_inc_settings($y_popup_mode, $y_use_protection=false, $y_use_test_browser=false) {
	//--
	switch((string)$y_popup_mode == '') {
		case 'popup':
			$the_popup_mode = 'popup';
			break;
		case 'modal':
		default:
			$the_popup_mode = 'modal';
	} //end if else
	//--
	$js_settings = '';
	//--
	if($y_use_test_browser === true) {
		$js_settings .= '<script type="text/javascript">Test_Browser_Compliance.checkCookies();</script>';
	} //end if
	//--
	if((string)$the_popup_mode == 'popup') { // popup
		$js_settings .= self::window_mode_init_smartpopup();
	} else { // modal (default)
		$js_settings .= self::window_mode_init_smartmodal();
		if($y_use_protection === true) {
			$js_settings .= self::window_mode_init_protection_to_smartmodal();
		} //end if
	} //end if else
	//--
	return (string) '<!-- JS Settings -->'.$js_settings.'<!-- JS Settings :: END -->';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Draw a Growl Notification
 *
 * @access 		private
 * @internal
 *
 */
public static function js_draw_growl($y_title, $y_text, $y_image, $y_time=6000, $y_sticky='false', $y_class='') {
	//--
	if(substr($y_title, 0, 11) == 'javascript:') {
		$y_title = substr($y_title, 11);
	} else {
		$y_title = "'".Smart::escape_js($y_title)."'";
	} //end if
	//--
	if(substr($y_text, 0, 11) == 'javascript:') {
		$y_text = substr($y_text, 11);
	} else {
		$y_text = "'".Smart::escape_js($y_text)."'";
	} //end if
	//--
	if((string)$y_sticky != 'true') {
		$y_sticky = 'false';
	} //end if
	//--
	$y_time = (int) $y_time;
	if($y_time < 1) {
		$y_time = 1; //miliseconds
	} //end if
	//--
	return 'SmartJS_BrowserUtils.GrowlNotificationAdd('.$y_title.', '.$y_text.', \''.Smart::escape_js($y_image).'\', '.$y_time.', '.$y_sticky.', \''.Smart::escape_js($y_class).'\');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Clear a Growl Notification
 *
 * @access 		private
 * @internal
 *
 */
public static function js_cleanup_growl($y_id='') {
	//-- here we take it as raw as this is the name of a JS variable ...
	$y_id = trim((string)$y_id); // (no prepare js string)
	if(!preg_match('/^[a-zA-Z0-9_]+$/', (string)$y_id)) {
		$y_id = '';
	} //end if
	//--
	return 'SmartJS_BrowserUtils.GrowlNotificationRemove('.$y_id.');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Draw a Notification Dialog
 *
 * @access 		private
 * @internal
 *
 */
public static function js_draw_html_confirm_dialog($y_question_html, $y_ok_jscript_function='', $y_width='550', $y_height='225', $y_title='?') {
	//--
	return 'SmartJS_BrowserUtils.confirm_Dialog(\''.Smart::escape_js($y_question_html).'\', \''.Smart::escape_js($y_ok_jscript_function).'\', \''.Smart::escape_js($y_title).'\', '.intval($y_width).', '.intval($y_height).');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Draw a Notification Alert
 *
 * @access 		private
 * @internal
 *
 */
public static function js_draw_html_alert($y_message, $y_ok_jscript_function='', $y_width='', $y_height='', $y_title='') {
	//--
	return 'SmartJS_BrowserUtils.alert_Dialog(\''.Smart::escape_js($y_message).'\', \''.Smart::escape_js($y_ok_jscript_function).'\', \''.Smart::escape_js($y_title).'\', '.intval($y_width).', '.intval($y_height).');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Confirm Form Submit
 *
 * @access 		private
 * @internal
 *
 */
public static function js_draw_html_confirm_form_submit($y_question, $y_popuptarget='', $y_width='', $y_height='', $y_force_popup='', $y_force_dims='') {
	//--
	if((string)$y_width != '') {
		$y_width = Smart::format_number_int((0+$y_width), '+');
	} //end if
	if((string)$y_height != '') {
		$y_height = Smart::format_number_int((0+$y_height), '+');
	} //end if
	if((string)$y_force_popup != '') {
		$y_force_popup = Smart::format_number_int((0+$y_force_popup)); // this can be -1, 0, 1
	} //end if
	if((string)$y_force_dims != '') {
		$y_force_dims = Smart::format_number_int((0+$y_force_dims), '+'); // 0 or 1
	} //end if
	//--
	return 'SmartJS_BrowserUtils.confirmSubmitForm(\''.Smart::escape_js($y_question).'\', this.form, \''.Smart::escape_js($y_popuptarget).'\', \''.$y_width.'\', \''.$y_height.'\', \''.$y_force_popup.'\', \''.$y_force_dims.'\'); return false;';
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function js_init_away_page($y_question='') {
	//--
	$translator_core_js_messages = SmartTextTranslations::getTranslator('@core', 'js_messages');
	//--
	if((string)$y_question == '') {
		$y_question = $translator_core_js_messages->text('page_away');
	} //end if else
	if((string)$y_question == '') {
		$y_question = 'Do you want to leave this page ?';
	} //end if else
	//--
	return '<script type="text/javascript">SmartJS_BrowserUtils.PageAwayControl(\''.Smart::escape_js($y_question).'\');</script>';
	//--
} //END FUNCTION
//================================================================



//================================================================
// $y_arr_values = array( array(id=>Smart::escape_html(''), c1=>Smart::escape_html(''), ... , c#=>Smart::escape_html(''), ...), array(-||-) );
// $y_src = Smart::escape_html($y_src) already
/**
 * Function: JS Answer Ajax Suggest Selector
 *
 * @access 		private
 * @internal
 *
 */
public static function js_answer_suggest_ajx_selector($y_arr_values, $y_cols, $y_src) {
	//--
	if($y_cols < 0) {
		$y_cols = 0;
	} //end if
	//--
	$arr = array();
	$arr['search_value'] = $y_src;
	$arr['search_cols'] = $y_cols;
	$arr['search_data'] = (array) $y_arr_values;
	//--
	return Smart::json_encode($arr);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Outputs the HTML Code to init the HTML (wysiwyg) Editor
 *
 * @param $y_filebrowser_link STRING 		URL to Image Browser (Example: script.php?op=image-gallery&type=images)
 *
 * @return STRING							[HTML Code]
 */
public static function js_init_html_area($y_filebrowser_link='') {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/html-editor-init.inc.htm',
		[
			'LANG' => (string) SmartTextTranslations::getLanguage(),
			'FILE-BROWSER-CALLBACK-URL' => (string) $y_filebrowser_link
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draw a TextArea with a built-in javascript HTML (wysiwyg) Editor
 *
 * @param STRING $yid					[Unique HTML Page Element ID]
 * @param STRING $yvarname				[HTML Form Variable Name]
 * @param STRING $yvalue				[HTML Data]
 * @param INTEGER+ $ywidth				[Area Width: (Example) 720px or 75%]
 * @param INTEGER+ $yheight				[Area Height (Example) 480px or 50%]
 * @param BOOLEAN $y_allow_scripts		[Allow JavaScripts]
 * @param BOOLEAN $y_allow_script_src	[Allow JavaScript SRC attribute]
 * @param MIXED $y_cleaner_deftags 		['' or array of HTML Tags to be allowed / dissalowed by the cleaner ... see HTML Cleaner Documentation]
 * @param ENUM $y_cleaner_mode 			[HTML Cleaner mode for defined tags: ALLOW / DISALLOW]
 * @param STRING $y_toolbar_ctrls		[Toolbar Controls: ... see CLEditor Documentation]
 *
 * @return STRING						[HTML Code]
 *
 */
public static function js_draw_html_area($yid, $yvarname, $yvalue='', $ywidth='720px', $yheight='480px', $y_allow_scripts=false, $y_allow_script_src=false, $y_cleaner_deftags='', $y_cleaner_mode='', $y_toolbar_ctrls='') {
	//--
	if((string)$y_cleaner_mode != '') {
		if((string)$y_cleaner_mode !== 'DISALLOW') {
			$y_cleaner_mode = 'ALLOW';
		} //end if
	} //end if
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/html-editor-draw.inc.htm',
		[
			'TXT-AREA-ID' 					=> (string) $yid, // HTML or JS ID
			'TXT-AREA-VAR-NAME' 			=> (string) $yvarname, // HTML variable name
			'TXT-AREA-WIDTH' 				=> (string) $ywidth, // 100px or 100%
			'TXT-AREA-HEIGHT' 				=> (string) $yheight, // 100px or 100%
			'TXT-AREA-CONTENT' 				=> (string) $yvalue,
			'TXT-AREA-ALLOW-SCRIPTS' 		=> (bool) $y_allow_scripts, // boolean
			'TXT-AREA-ALLOW-SCRIPT-SRC' 	=> (bool) $y_allow_script_src, // boolean
			'CLEANER-REMOVE-TAGS' 			=> $y_cleaner_deftags, // mixed
			'CLEANER-MODE-TAGS' 			=> (string) $y_cleaner_mode,
			'TXT-AREA-TOOLBAR' 				=> (string) $y_toolbar_ctrls
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * CallBack Mapping for HTML (wysiwyg) Editor - FileBrowser Integration
 *
 * @param STRING $yurl					The Callback URL
 * @param BOOLEAN $is_popup 			Set to True if Popup (incl. Modal)
 *
 * @return STRING						[JS Code]
 */
public static function js_callback_html_area($yurl, $is_popup=false) {
	//--
	return (string) str_replace(array("\r\n", "\r", "\n", "\t"), array(' ', ' ', ' ', ' '), (string)SmartMarkersTemplating::render_file_template(
		'lib/core/templates/html-editor-callback.inc.htm',
		[
			'IS_POPUP' 	=> (int) $is_popup,
			'URL' 		=> (string) $yurl
		],
		'yes' // export to cache
	));
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Init the Code Editor (Edit Area)
 *
 * @return STRING						[HTML Code]
 */
public static function js_init_editarea() {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/code-editor-init.inc.htm',
		[
			'LANG' => (string) SmartTextTranslations::getLanguage()
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draw a TextArea with a built-in javascript Code Editor (Edit Area).
 * Supported syntax parsers: CSS, Javascript, Json, HTML, XML, YAML, Markdown, SQL, PHP, Text (default).
 *
 * @param STRING $yid					[Unique HTML Page Element ID]
 * @param STRING $yvarname				[HTML Form Variable Name]
 * @param STRING $yvalue				[HTML Data]
 * @param ENUM $y_mode 					[Parser mode: css, javascript, json, html, xml, yaml, markdown, sql, php, text]
 * @param BOOLEAN $y_editable 			[Editable: true / Not Editable: false]
 * @param INTEGER+ $ywidth				[Area Width: (Example) 720px or 75%]
 * @param INTEGER+ $yheight				[Area Height (Example) 480px or 50%]
 * @param BOOLEAN $y_line_numbers		[Display line numbers: true ; Hide line numbersL false]
 *
 * @return STRING						[HTML Code]
 *
 */
public static function js_draw_editarea($yid, $yvarname, $yvalue='', $y_mode='text', $y_editable=true, $y_width='720px', $y_height='300px', $y_line_numbers=true) {
	//--
	$the_lang = SmartTextTranslations::getLanguage();
	//--
	switch((string)$y_mode) {
		case 'json':
			$the_mode = 'application/json';
			break;
		case 'javascript':
			$the_mode = 'text/javascript';
			break;
		case 'css':
			$the_mode = 'text/css';
			break;
		case 'html':
			$the_mode = 'text/html';
			break;
		case 'xml':
			$the_mode = 'text/xml';
			break;
		case 'markdown':
			$the_mode = 'text/x-markdown';
			break;
		case 'yaml':
			$the_mode = 'text/x-yaml';
			break;
		case 'php':
			$the_mode = 'application/x-httpd-php';
			break;
		case 'sql':
			$the_mode = 'text/x-sql';
			break;
		case 'spreadsheet':
			$the_mode = 'text/x-spreadsheet';
			break;
		case 'gpg':
		case 'pgp':
			$the_mode = 'application/pgp';
			break;
		case 'text':
		default:
			$the_mode = 'text/plain';
	} //end switch
	if(!$y_editable) {
		$is_readonly = true;
		$attrib_readonly = ' readonly';
		$cursor_blinking = '0';
		$theme = 'uxm';
	} else {
		$is_readonly = false;
		$attrib_readonly = '';
		$cursor_blinking = '530';
		$theme = 'uxw';
	} //end switch
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/code-editor-draw.inc.htm',
		[
			'TXT-AREA-ID' 		=> (string) $yid,
			'WIDTH' 			=> (string) $y_width,
			'HEIGHT' 			=> (string) $y_height,
			'SHOW-LINE-NUM' 	=> (bool) $y_line_numbers,
			'READ-ONLY' 		=> (bool) $is_readonly,
			'BLINK-CURSOR' 		=> Smart::format_number_int($cursor_blinking,'+'),
			'CODE-TYPE' 		=> (string) $the_mode,
			'THEME' 			=> (string) $theme,
			'TXT-AREA-VAR-NAME' => (string) $yvarname,
			'TXT-AREA-CONTENT' 	=> (string) $yvalue,
			'TXT-AREA-READONLY'	=> (string) $attrib_readonly
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function js_draw_preview_iframe($yid, $y_contents, $y_width='720px', $y_height='300px', $y_maximized=false, $y_sandbox='allow-popups') {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/preview-iframe-draw.inc.htm',
		[
			'IFRM-ID' 		=> (string) $yid,
			'WIDTH' 		=> (string) $y_width,
			'HEIGHT' 		=> (string) $y_height,
			'SANDBOX' 		=> (string) $y_sandbox,
			'MAXIMIZED' 	=> (bool) $y_maximized,
			'CONTENT' 		=> (string) $y_contents
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draws a JS-Date-Box Field with Calendar
 *
 * @param STRING 	$y_id					[HTML page ID for field (unique) ; used foor JavaScript]
 * @param STRING 	$y_var					[HTML Variable Name or empty if no necessary]
 * @param DATE 		$yvalue					[DATE, empty or formated as YYYY-MM-DD]
 * @param STRING 	$y_text_select			[The text as title: 'Select Date']
 * @param JS-Date 	$yjs_mindate			[JS Expression, Min Date] :: new Date(1937, 1 - 1, 1)   or '-1y -1m -1d'
 * @param JS-Date 	$yjs_maxdate			[JS Expression, Max Date] :: new Date(2037, 12 - 1, 31) or '1y 1m 1d'
 * @param ARRAY 	$y_extra_options		[Options Array[width, ...] for for datePicker]
 * @param JS-Code 	$yjs_custom				[JS Code to execute on Select(date)]
 *
 * @return STRING 							[HTML Code]
 */
public static function js_draw_date_field($y_id, $y_var, $yvalue, $y_text_select='', $yjs_mindate='', $yjs_maxdate='', $y_extra_options=array(), $yjs_custom='') {
	//-- v.160306
	global $configs;
	//--
	if((string)$yvalue != '') {
		$yvalue = date('Y-m-d', @strtotime($yvalue)); // enforce this date format for internals and be sure is valid
	} //end if
	//--
	if($configs['regional']['calendar-week-start'] == 1) {
		$the_first_day = 1; // Calendar Start on Monday
	} else {
		$the_first_day = 0; // Calendar Start on Sunday
	} //end if else
	//--
	$the_altdate_format = self::get_date_format_for_js((string)$configs['regional']['calendar-date-format-client']);
	//--
	if(!is_array($y_extra_options)) {
		$y_extra_options = array();
	} //end if
	if((string)$y_extra_options['width'] == '') {
		$the_option_size = '85';
	} else {
		$the_option_size = (string) $y_extra_options['width'];
	} //end if
	$the_option_size = 0 + $the_option_size;
	if($the_option_size >= 1) {
		$the_option_size = ' width:'.((int)$the_option_size).'px;';
	} elseif($the_option_size > 0) {
		$the_option_size = ' width:'.($the_option_size * 100).'%;';
	} else {
		$the_option_size = '';
	} //end if else
	//--
	if((string)$yjs_mindate == '') {
		$yjs_mindate = 'null';
	} //end if
	if((string)$yjs_maxdate == '') {
		$yjs_maxdate = 'null';
	} //end if
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/ui-picker-date.inc.htm',
		[
			'LANG' 				=> (string) SmartTextTranslations::getLanguage(),
			'THE-ID' 			=> (string) $y_id,
			'THE-VAR' 			=> (string) $y_var,
			'THE-VALUE' 		=> (string) $yvalue,
			'TEXT-SELECT' 		=> (string) $y_text_select,
			'ALT-DATE-FORMAT' 	=> (string) $the_altdate_format,
			'STYLE-SIZE' 		=> (string) $the_option_size,
			'FDOW' 				=> (int) $the_first_day, // of week
			'DATE-MIN' 			=> (string) $yjs_mindate,
			'DATE-MAX' 			=> (string) $yjs_maxdate,
			'EVAL-JS' 			=> (string) $yjs_custom
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draws a JS-Time-Box Field with Times
 *
 * @param STRING 	$y_id					[HTML page ID for field (unique) ; used foor JavaScript]
 * @param STRING 	$y_var					[HTML Variable Name]
 * @param HH:ii 	$yvalue					[TIME, pre-definned value, formated as 24h HH:ii]
 * @param STRING 	$y_text_select			[The text for 'Select Time']
 * @param 0..22 	$y_h_st					[Starting Time]
 * @param 1..23 	$y_h_end				[Ending Time]
 * @param 0..58 	$y_i_st					[Starting Minute]
 * @param 1..59 	$y_i_end				[Ending Minute]
 * @param 1..30 	$y_i_step				[Step of Minutes]
 * @param INTEGER 	$y_rows 				[Default is 2]
 * @param JS-Code 	$y_extra_options		[Options Array[width, ...] for timePicker]
 * @param JS-Code 	$yjs_custom				[JS Code to execute on Select(time)]
 *
 * @return STRING 							[HTML Code]
 */
public static function js_draw_time_field($y_id, $y_var, $yvalue, $y_text_select='', $y_h_st='0', $y_h_end='23', $y_i_st='0', $y_i_end='55', $y_i_step='5', $y_rows='2', $y_extra_options=array(), $yjs_custom='') {
	//-- v.160306
	if((string)$yvalue != '') {
		$yvalue = date('H:i', @strtotime(date('Y-m-d').' '.$yvalue)); // enforce this time format for internals and be sure is valid
	} //end if
	//--
	$prep_hstart = Smart::format_number_int($y_h_st, '+');
	$prep_hend = Smart::format_number_int($y_h_end, '+');
	$prep_istart = Smart::format_number_int($y_i_st, '+');
	$prep_iend = Smart::format_number_int($y_i_end, '+');
	$prep_iinterv = Smart::format_number_int($y_i_step, '+');
	$prep_rows = Smart::format_number_int($y_rows, '+');
	//--
	if(!is_array($y_extra_options)) {
		$y_extra_options = array();
	} //end if
	if((string)$y_extra_options['width'] == '') {
		$the_option_size = '50';
	} else {
		$the_option_size = (string) $y_extra_options['width'];
	} //end if
	$the_option_size = 0 + $the_option_size;
	if($the_option_size >= 1) {
		$the_option_size = ' width:'.((int)$the_option_size).'px;';
	} elseif($the_option_size > 0) {
		$the_option_size = ' width:'.($the_option_size * 100).'%;';
	} else {
		$the_option_size = '';
	} //end if else
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/ui-picker-time.inc.htm',
		[
			'LANG' 			=> (string) SmartTextTranslations::getLanguage(),
			'THE-ID' 		=> (string) $y_id,
			'THE-VAR' 		=> (string) $y_var,
			'THE-VALUE' 	=> (string) $yvalue,
			'TEXT-SELECT' 	=> (string) $y_text_select,
			'STYLE-SIZE' 	=> (string) $the_option_size,
			'H-START' 		=> (int) $prep_hstart,
			'H-END' 		=> (int) $prep_hend,
			'MIN-START'		=> (int) $prep_istart,
			'MIN-END' 		=> (int) $prep_iend,
			'MIN-INTERVAL' 	=> (int) $prep_iinterv,
			'DISPLAY-ROWS' 	=> (int) $prep_rows,
			'EVAL-JS' 		=> (string) $yjs_custom
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
// $y_limit :: max characters :: between 100 and 99999
/**
 * Function: JS Draw Limited Text Area
 *
 */
public static function js_draw_limited_text_area($y_var_name, $y_var_value, $y_limit, $y_cols='40', $y_rows='7', $y_field_id='', $y_wrap='physical', $y_rawval='no', $y_placeholder='', $y_percent_w='') {
//--
if((int)$y_limit < 50) {
	$y_limit = 50;
} //end if
//--
if((int)$y_limit > 99999) {
	$y_limit = 99999;
} //end if
//--
if($y_rawval != 'yes') {
	$y_var_value = Smart::escape_html($y_var_value);
} //end if
//--
if(strlen($y_field_id) > 0) {
	$field = (string) $y_field_id;
} else {
	$field = '__Fld_TEXTAREA__'.sha1('Limited Text Area :: '.$y_var_name.' @@ '.$y_limit.' #').'__NO_Var__';
} //end if else
//--
$counter = '__Fld_COUNTER__'.sha1('Limited Text Area :: '.$y_var_name.' @@ '.$y_limit.' #').'__NO_Var__';
//--
$style_override_w = '';
if(($y_percent_w > 0) AND ($y_percent_w <= 100)) {
	$css_w = ((int) $y_percent_w).'%';
	$style_override_w = 'width:'.$css_w;
} else {
	$css_w = ((int) $y_cols * 8).'px';
} //end if
$css_h = ((int) $y_rows * 18).'px';
//--
$placeholder = '';
if((string)$y_placeholder != '') {
	$placeholder = ' placeholder="'.$y_placeholder.'"';
} //end if
//--
$html = <<<HTML_CODE
<!-- Limited Text Area -->
<div style="position:relative;{$style_override_w}">
	<textarea id="{$field}" name="{$y_var_name}" wrap="{$y_wrap}" maxlength="{$y_limit}" onClick="SmartJS_BrowserUtils.textArea_Limit('{$field}', '{$counter}', {$y_limit});" onBlur="SmartJS_BrowserUtils.textArea_Limit('{$field}', '{$counter}', {$y_limit});" onKeyDown="SmartJS_BrowserUtils.textArea_Limit('{$field}', '{$counter}', {$y_limit});" onKeyUp="SmartJS_BrowserUtils.textArea_Limit('{$field}', '{$counter}', {$y_limit});" style="width:{$css_w}; height:{$css_h};"{$placeholder}>{$y_var_value}</textarea>
	<input title="Max Chars Limit: {$y_limit}" type="text" readonly disabled id="{$counter}" size="5" maxlength="4" value="{$y_limit}" style="padding:1px!important; font-size:10px!important; text-align:center!important; position:absolute; left:5px; bottom:5px; opacity:0.5;">
</div>
HTML_CODE;
//--
return $html;
//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Init Ajax Suggest Selector
 *
 */
public static function js_init_suggest_ajx_selector() {
//--
$js = <<<'JS'
<!-- AjaxSuggest -->
<link rel="stylesheet" type="text/css" href="lib/js/jsjssuggest/ajax_suggest.css">
<script type="text/javascript" src="lib/js/jsjssuggest/ajax_suggest.js"></script>
<!-- END AjaxSuggest -->
JS;
//--
return (string) $js;
//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Draw Ajax Suggest Selector
 *
 */
public static function js_draw_suggest_ajx_selector($y_width, $y_prefix, $y_suffix, $y_ajx_method, $y_ajx_url, $y_id_prefix, $y_form_hint, $y_form_var, $y_form_value='') {
	//--
	$ajx_div = $y_id_prefix.'_AJXSelector_DIV';
	$ajx_txt = $y_id_prefix.'_AJXSelector_TXT';
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/js/jsjssuggest/ajax_suggest.inc.htm',
		[
			//-- passed as html
			'WIDTH' 		=> Smart::escape_html((string)$y_width),
			'DIV-HTML-ID' 	=> Smart::escape_html((string)$ajx_div),
			'TXT-HTML-ID' 	=> Smart::escape_html((string)$ajx_txt),
			'TXT-TITLE' 	=> Smart::escape_html((string)$y_form_hint),
			'TXT-FORM-VAR' 	=> Smart::escape_html((string)$y_form_var),
			'TXT-VALUE' 	=> Smart::escape_html((string)$y_form_value),
			//-- passed to js
			'DIV-JS-ID' 	=> Smart::escape_js((string)$ajx_div),
			'TXT-JS-ID' 	=> Smart::escape_js((string)$ajx_txt),
			'AJAX-METHOD' 	=> Smart::escape_js((string)$y_ajx_method),
			'AJAX-URL' 		=> Smart::escape_js((string)$y_ajx_url),
			//-- passed raw
			'PREFIX' 		=> (string) $y_prefix, // this is preformatted HTML
			'SUFFIX' 		=> (string) $y_suffix // this is preformatted HTML
			//--
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Draw AutoComplete Single
 *
 * @access 		private
 * @internal
 *
 */
public static function js_draw_jquery_autocomplete_single($y_element_id, $y_script, $y_term_var, $y_min_len=1, $y_eval_selector_js='') {
	//--
	$y_min_len = Smart::format_number_int($y_min_len, '+');
	if($y_min_len < 1) {
		$y_min_len = 1;
	} //end if
	if($y_min_len > 255) {
		$y_min_len = 255;
	} //end if
	//--
	return '<script type="text/javascript">SmartJS_BrowserUIUtils.AutoComplete_Single(\''.Smart::escape_js($y_element_id).'\', \''.Smart::escape_js($y_script).'\', \''.Smart::escape_js($y_term_var).'\', '.$y_min_len.', \''.Smart::escape_js($y_eval_selector_js).'\');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Draw AutoComplete Multi
 *
 * @access 		private
 * @internal
 *
 */
public static function js_draw_jquery_autocomplete_multi($y_element_id, $y_script, $y_term_var, $y_min_len=1, $y_eval_selector_js='') {
	//--
	$y_min_len = Smart::format_number_int($y_min_len, '+');
	if($y_min_len < 1) {
		$y_min_len = 1;
	} //end if
	if($y_min_len > 255) {
		$y_min_len = 255;
	} //end if
	//--
	return '<script type="text/javascript">SmartJS_BrowserUIUtils.AutoComplete_Multi(\''.Smart::escape_js($y_element_id).'\', \''.Smart::escape_js($y_script).'\', \''.Smart::escape_js($y_term_var).'\', '.$y_min_len.', \''.Smart::escape_js($y_eval_selector_js).'\');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Init jQueryUI Tabs
 *
 * @access 		private
 * @internal
 *
 */
public static function js_ajx_tabs_init($y_id_of_tabs, $y_selected=0, $y_prevent_reload=false) {
	//--
	$y_selected = Smart::format_number_int($y_selected, '+');
	//--
	if($y_prevent_reload === true) {
		$prevreload = 'true';
	} else {
		$prevreload = 'false';
	} //end if else
	//--
	return '<script type="text/javascript">SmartJS_BrowserUIUtils.Tabs_Init(\''.Smart::escape_js($y_id_of_tabs).'\', '.$y_selected.', '.$prevreload.');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Ajax Wait for Complete
 * used just for reports (only once per page to avoid conflicts
 *
 * @access 		private
 * @internal
 *
 */
public static function js_ajx_wait_complete($y_div_id, $y_html_code, $y_url, $y_method='GET') {
	//--
	$div_id = 'AJX_requester_DIV_'.$y_div_id;
	//--
	return '<div id="'.$div_id.'">'.$y_html_code.'</div><script type="text/javascript">SmartJS_BrowserUtils.Load_Div_Content_By_Ajax(\''.$div_id.'\', \'lib/core/img/busy_timer.gif\', \''.Smart::escape_js($y_url).'\', \'{$y_method}\', \'html\');</script>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Redirect on Success, Button if Error
 * used just for reports (only once per page to avoid conflicts
 *
 * @access 		private
 * @internal
 *
 */
public static function js_redirect_on_success_button_on_error($y_redirect_url, $y_error, $y_timeout='1000') {
	//--
	$translator_core_window = SmartTextTranslations::getTranslator('@core', 'window');
	//--
	if(strlen($y_error) > 0) {
		$out = '<br><input type="button" value="'.$translator_core_window->text('action_back').'" class="bttnz" onClick="SmartJS_BrowserUtils.RedirectToURL(\''.Smart::escape_js($y_redirect_url).'\'); return false;"><br>';
	} else {
		$out = '<br><img src="lib/core/img/busy_circle.gif"><br>'.self::redirect_page($y_redirect_url, $y_timeout);
	} //end if else
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Get Browser Image
 *
 * @access 		private
 * @internal
 *
 */
public static function get_browser_pict($y_bw) {
	//--
	switch(strtolower($y_bw)) {
		case '@s#':
			$pict = '<span title="[SmartFramework/Robot]"><img src="lib/core/img/browser/@smart-robot.png"></span>';
			break;
		case 'bot':
			$pict = '<span title="Robots / Crawlers"><img src="lib/core/img/browser/bot.png"></span>';
			break;
		case 'lyx':
			$pict = '<span title="Lynx Text Browser"><img src="lib/core/img/browser/lyx.png"></span>';
			break;
		case 'moz':
			$pict = '<span title="Mozilla / Seamonkey"><img src="lib/core/img/browser/moz.png"></span>';
			break;
		case 'fox':
			$pict = '<span title="Firefox"><img src="lib/core/img/browser/fox.png"></span>';
			break;
		case 'cam':
			$pict = '<span title="Camino"><img src="lib/core/img/browser/cam.png"></span>';
			break;
		case 'crm':
			$pict = '<span title="Google Chrome / Chromium"><img src="lib/core/img/browser/crm.png"></span>';
			break;
		case 'sfr':
			$pict = '<span title="Apple Safari / Webkit"><img src="lib/core/img/browser/sfr.png"></span>';
			break;
		case 'iex':
			$pict = '<span title="MS Internet Explorer"><img src="lib/core/img/browser/iex.png"></span>';
			break;
		case 'opr':
			$pict = '<span title="Opera"><img src="lib/core/img/browser/opr.png"></span>';
			break;
		case 'mid':
			$pict = '<span title="Midori / Webkit"><img src="lib/core/img/browser/mid.png"></span>';
			break;
		case 'knq':
			$pict = '<span title="Konqueror"><img src="lib/core/img/browser/knq.png"></span>';
			break;
		case 'eph':
			$pict = '<span title="Epiphany"><img src="lib/core/img/browser/eph.png"></span>';
			break;
		case 'gal':
			$pict = '<span title="Galeon"><img src="lib/core/img/browser/gal.png"></span>';
			break;
		case 'omw':
			$pict = '<span title="Omniweb Browser"><img src="lib/core/img/browser/omw.png"></span>';
			break;
		default:
			$pict = '<span title="[UNKNOWN] Browser :: '.Smart::escape_html($y_bw).'"><img src="lib/core/img/sign_notice.png"></span>';
	} //end switch
	//--
	return $pict;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Get Browser Image
 *
 * @access 		private
 * @internal
 *
 */
public static function get_os_pict($y_os, $y_prefix='') {
	//--
	switch(strtolower($y_os)) {
		//-
		case 'netbsd':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'NetBSD Operating System"><img src="lib/core/img/os/bsd_netbsd.png"></span>';
			break;
		case 'openbsd':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'OpenBSD Operating System"><img src="lib/core/img/os/bsd_openbsd.png"></span>';
			break;
		case 'freebsd':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'FreeBSD Operating System"><img src="lib/core/img/os/bsd_freebsd.png"></span>';
			break;
		case 'dragonfly':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'DragonFlyBSD Operating System"><img src="lib/core/img/os/bsd_dragonfly.png"></span>';
			break;
		case 'bsd':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'* BSD Operating System"><img src="lib/core/img/os/bsd_generic.png"></span>';
			break;
		//-
		case 'win':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Windows Operating System"><img src="lib/core/img/os/windows.png"></span>';
			break;
		case 'macos':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Apple Mac Operating System"><img src="lib/core/img/os/mac_os.png"></span>';
			break;
		case 'darwin':
		case 'macosx':
		case 'mac':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Apple MacOSX Operating System"><img src="lib/core/img/os/mac_osx.png"></span>';
			break;
		//-
		case 'lnx':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'* Linux Operating System"><img src="lib/core/img/os/linux_generic.png"></span>';
			break;
		case 'lnx_debian':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Debian Linux Operating System"><img src="lib/core/img/os/linux_debian.png"></span>';
			break;
		case 'lnx_ubuntu':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Ubuntu Linux Operating System"><img src="lib/core/img/os/linux_ubuntu.png"></span>';
			break;
		case 'lnx_suse':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'SuSE Linux Operating System"><img src="lib/core/img/os/linux_suse.png"></span>';
			break;
		case 'lnx_novell':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Novell Linux Operating System"><img src="lib/core/img/os/linux_novell.png"></span>';
			break;
		case 'lnx_redhat':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'RedHat Linux Operating System"><img src="lib/core/img/os/linux_redhat.png"></span>';
			break;
		case 'lnx_fedora':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Fedora Linux Operating System"><img src="lib/core/img/os/linux_fedora.png"></span>';
			break;
		case 'lnx_centos':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'CentOS Linux Operating System"><img src="lib/core/img/os/linux_centos.png"></span>';
			break;
		case 'lnx_gentoo':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Gentoo Linux Operating System"><img src="lib/core/img/os/linux_gentoo.png"></span>';
			break;
		case 'lnx_mandrake':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Mandriva / Mandrake Linux Operating System"><img src="lib/core/img/os/linux_mandriva.png"></span>';
			break;
		case 'lnx_knoppix':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Knoppix Linux Operating System"><img src="lib/core/img/os/linux_knoppix.png"></span>';
			break;
		case 'lnx_arch':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Arch Linux Operating System"><img src="lib/core/img/os/linux_arch.png"></span>';
			break;
		//-
		case 'aix':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'IBM/AIX Operating System"><img src="lib/core/img/os/unix_ibmaix.png"></span>';
			break;
		case 'hpx':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'HP-UX Operating System"><img src="lib/core/img/os/unix_hpux.png"></span>';
			break;
		case 'opensolaris':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Open Solaris Operating System"><img src="lib/core/img/os/unix_opensolaris.png"></span>';
			break;
		case 'nexenta':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Nexenta Operating System"><img src="lib/core/img/os/unix_nexentasolaris.png"></span>';
			break;
		case 'sun':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Sun/Solaris Operating System"><img src="lib/core/img/os/unix_solaris.png"></span>';
			break;
		case 'sco':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Sco/Unixware Operating System"><img src="lib/core/img/os/unix_sco.png"></span>';
			break;
		case 'irx':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'SGI/IRIX Operating System"><img src="lib/core/img/os/unix_sgiirix.png"></span>';
			break;
		//-
		case 'ios':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Apple/iOS Mobile Operating System (iPhone)"><img src="lib/core/img/os/mobile/ios.png"></span>';
			break;
		case 'ipd':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Apple/iOS Mobile Operating System (iPad)"><img src="lib/core/img/os/mobile/ios_tablet.png"></span>';
			break;
		case 'mlx':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Linux Mobile Operating System"><img src="lib/core/img/os/mobile/linux_mobile.png"></span>';
			break;
		case 'and':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Google/Android Mobile Operating System"><img src="lib/core/img/os/mobile/android.png"></span>';
			break;
		case 'mgo':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Nokia/MeeGO Mobile Operating System"><img src="lib/core/img/os/mobile/meego.png"></span>';
			break;
		case 'nsy':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Nokia/Symbian Mobile Operating System"><img src="lib/core/img/os/mobile/symbian.png"></span>';
			break;
		case 'bby':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'BlackBerry Mobile Operating System"><img src="lib/core/img/os/mobile/blackberry.png"></span>';
			break;
		case 'wce':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'WindowsCE Mobile Operating System"><img src="lib/core/img/os/mobile/wince.png"></span>';
			break;
		case 'plm':
			$pict = '<span title="'.Smart::escape_html($y_prefix).'Palm Mobile Operating System"><img src="lib/core/img/os/mobile/palm.png"></span>';
			break;
		//-
		case '[?]':
		default:
			$pict = '<span title="'.Smart::escape_html($y_prefix).'[UNKNOWN] Operating System :: '.Smart::escape_html($y_os).'"><img src="lib/core/img/sign_notice.png"></span>';
		//-
	} //end switch
	//--
	return $pict;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Display Yes=y/No=n Selector
 *
 * @access 		private
 * @internal
 *
 * @param STRING $y_var			:: HTML Var Name
 * @param STRING $y_val			:: '' | 'y' | 'n'
 * @param STRING $ytxt_yes		:: Text for 'Yes'
 * @param STRING $ytxt_no		:: Text for 'No'
 * @return STRING				:: HTML Code
 */
public static function yes_no_selector($y_var, $y_val) {
//--
$translator_core_messages = SmartTextTranslations::getTranslator('@core', 'messages');
//--
$txt_yes = $translator_core_messages->text('yes');
$txt_no = $translator_core_messages->text('no');
//--
if((string)$y_val == 'y') {
	$tmp_m = 'checked';
	$tmpx_code = $txt_yes;
} //end if
if((string)$y_val == 'n') {
	$tmp_f = 'checked';
	$tmpx_code = $txt_no;
} //end if
//--
$code = <<<HTML_CODE
  {$txt_yes}<input name="{$y_var}" type="radio" value="y" {$tmp_m}>
  &nbsp; &nbsp;
  {$txt_no}<input name="{$y_var}" type="radio" value="n" {$tmp_f}>
HTML_CODE;
//--
if((string)$y_var == '') {
	$code = (string) $tmpx_code;
} //end if
//--
return (string) $code;
//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Display True=1/False=0 Selector
 *
 * @access 		private
 * @internal
 *
 * @param STRING $y_var			:: HTML Var Name
 * @param STRING $y_val			:: '' | '0' | '1'
 * @param STRING $ytxt_true		:: Text for 'Yes' (True=1)
 * @param STRING $ytxt_false	:: Text for 'No' (False=0)
 * @return STRING				:: HTML Code
 */
public static function true_false_selector($y_var, $y_val) {
//--
$translator_core_messages = SmartTextTranslations::getTranslator('@core', 'messages');
//--
$txt_true = $translator_core_messages->text('yes');
$txt_false = $translator_core_messages->text('no');
//--
if((string)$y_val == '1') {
	$tmp_m = 'checked';
	$tmpx_code = $txt_true;
} else {
	$tmp_f = 'checked';
	$tmpx_code = $txt_false;
} //end if
//--
$code = <<<HTML_CODE
  {$txt_true}<input name="{$y_var}" type="radio" value="1" {$tmp_m}>
  &nbsp; &nbsp;
  {$txt_false}<input name="{$y_var}" type="radio" value="0" {$tmp_f}>
HTML_CODE;
//--
if((string)$y_var == '') {
	$code = (string) $tmpx_code;
} //end if
//--
return (string) $code;
//--
} //END FUNCTION
//================================================================


//================================================================
public static function html_form_vars($y_var, $y_html_var) {
	//--
	$out = '';
	//--
	$regex_var = '/^([_a-zA-Z0-9])+$/';
	//--
	if(((string)$y_html_var != '') AND (preg_match((string)$regex_var, (string)$y_html_var))) {
		if(is_array($y_var)) { // SYNC VARS
			foreach($y_var as $key => $val) {
				if(((string)$key != '') AND (preg_match((string)$regex_var, (string)$key))) {
					$out .= '<input type="hidden" name="'.$y_html_var.'['.$key.']" value="'.Smart::escape_html((string)$val).'">'."\n";
				} //end if
			} //end for
		} elseif((string)$y_var != '') {
			$out .= '<input type="hidden" name="'.$y_html_var.'" value="'.Smart::escape_html((string)$y_var).'">'."\n";
		} //end if else
	} //end if
	//--
	return (string) $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
// the allowed date formats for Javascript (just for display reasons)
public static function get_date_format_for_js($y_format) {
	//-- yy = year with 4 digits, mm = month 01..12, dd = day 01..31
	$format = 'yy-mm-dd'; // the default format
	//--
	switch((string)$y_format) {
		//--
		case 'yy.mm.dd':
		case 'yy-mm-dd':
		case 'yy mm dd':
		//--
		case 'dd.mm.yy':
		case 'dd-mm-yy':
		case 'dd mm yy':
		//--
		case 'mm.dd.yy':
		case 'mm-dd-yy':
		case 'mm dd yy':
		//--
			$format = $y_format;
			break;
		default:
			// nothing
	} //end switch
	//--
	return (string) $format;
	//--
} //END FUNCTION
//================================================================


//================================================================
// the allowed date formats for PHP (just for display reasons)
public static function get_date_format_for_php($y_format) {
	//-- Y = year with 4 digits, m = month 01..12, d = day 01..31
	$format = 'Y-m-d'; // the default format
	//--
	switch((string)$y_format) {
		//--
		case 'Y.m.d':
		case 'Y-m-d':
		case 'Y m d':
		//--
		case 'd.m.Y':
		case 'd-m-Y':
		case 'd m Y':
		//--
		case 'm.d.Y':
		case 'm-d-Y':
		case 'm d Y':
		//--
			$format = $y_format;
			break;
		default:
			// nothing
	} //end switch
	//--
	return (string) $format;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Draw Powered Info
 *
 * @access 		private
 * @internal
 *
 */
public static function draw_powered_info($y_show_versions, $y_software_name='', $y_software_powered_logo='') {
	//--
	global $configs;
	//--
	$os_pict = self::get_os_pict(SmartUtils::get_server_os(), 'Server Powered by ');
	//--
	if(((string)$y_software_name == '') OR ((string)$y_software_powered_logo == '')) {
		$y_software_name = 'Smart.Framework';
		$y_software_powered_logo = 'lib/framework/img/powered_by_smart_framework.png';
	} //end if
	//--
	$tmp_arr_web_server = SmartUtils::get_webserver_version();
	$name_webserver = Smart::escape_html($tmp_arr_web_server['name']);
	//--
	if((string)$y_show_versions == 'yes') { // expose versions (not recommended in web area, except for auth admins)
		//--
		$y_software_name .= ' :: '.SMART_SOFTWARE_APP_NAME;
		//--
		$version_webserver = ' :: '.Smart::escape_html($tmp_arr_web_server['version']);
		$version_php = ' :: '.Smart::escape_html(PHP_VERSION);
		//--
	} else { // avoid expose versions
		//--
		$version_webserver = '';
		$version_php = '';
		//--
	} //end if else
	//--
	if(trim(strtolower($name_webserver)) == 'apache') {
		$name_webserver = 'Apache';
		$icon_webserver_powered = 'lib/framework/img/powered_by_apache.png';
		$icon_webserver_logo = 'lib/framework/img/apache_logo_small_trans.png';
	} else {
		$icon_webserver_powered = 'lib/framework/img/powered_by_nginx.png';
		$icon_webserver_logo = 'lib/framework/img/nginx_logo_small_trans.png';
	} //end if else
	//--
	$version_dbserver = '';
	if(is_array($configs['pgsql'])) {
		if((defined('SMART_FRAMEWORK_DB_VERSION_PostgreSQL')) AND ((string)$y_show_versions == 'yes')) {
			$version_dbserver = ' :: '.Smart::escape_html(SMART_FRAMEWORK_DB_VERSION_PostgreSQL);
		} //end if
		$name_dbserver = 'PostgreSQL';
		$icon_dbserver_powered = '<img src="lib/core/img/db/powered_by_postgresql.png">';
		$icon_dbserver_logo = '<img src="lib/core/img/db/postgresql_logo_small_trans.png">';
	} else {
		$name_dbserver = '';
		$icon_dbserver_powered = '';
		$icon_dbserver_logo = '';
	} //end if else
	//--
	if(is_array($configs['redis'])) {
		$name_cacheserver = 'Redis';
		$icon_cacheserver_powered = '<img src="lib/core/img/db/powered_by_redis.png">';
		$icon_cacheserver_logo = '<img src="lib/core/img/db/redis_logo_small_trans.png">';
	} else {
		$name_cacheserver = '';
		$icon_cacheserver_powered = '';
		$icon_cacheserver_logo = '';
	} //end if
	//--
	$name_dblite = 'SQLite';
	$icon_dblite_powered = 'lib/core/img/db/powered_by_sqlite.png';
	$icon_dblite_logo = 'lib/core/img/db/sqlite_logo_small.png';
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/powered-info.inc.htm',
		[
			'OS-LOGO' => $os_pict,
			'WEB-SERVER-POWERED-VERSION' => $name_webserver.$version_webserver,
			'WEB-SERVER-POWERED-ICON' => $icon_webserver_powered,
			'WEB-SERVER-VERSION' => $name_webserver.' Web Server',
			'WEB-SERVER-ICON' => $icon_webserver_logo,
			'PHP-VERSION' => $version_php,
			'DBSERVER-NAME' => $name_dbserver,
			'DBSERVER-VERSION' => $version_dbserver,
			'DBSERVER-POWERED-ICON' => $icon_dbserver_powered,
			'DBSERVER-POWERED-LOGO' => $icon_dbserver_logo,
			'CACHESERVER-NAME' => $name_cacheserver,
			'CACHESERVER-POWERED-ICON' => $icon_cacheserver_powered,
			'CACHESERVER-POWERED-LOGO' => $icon_cacheserver_logo,
			'DBLITE-NAME' => $name_dblite,
			'DBLITE-POWERED-ICON' => $icon_dblite_powered,
			'DBLITE-POWERED-LOGO' => $icon_dblite_logo,

			'SOFTWARE-NAME' => Smart::escape_html($y_software_name),
			'SOFTWARE-POWERED-LOGO' => Smart::escape_html($y_software_powered_logo)
		]
	);
	//--
} //END FUNCTION
//================================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>