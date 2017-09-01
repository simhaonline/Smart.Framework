<?php
// [LIB - SmartFramework / Smart Components]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_APP_BOOTSTRAP')) { // this must be defined in the first line of the application
	die('Invalid Runtime App Bootstrap Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
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
//	* js-base.inc.htm
//	* js-ui.inc.htm [SmartJS_BrowserUIUtils] or an extension
//	* js/jsedithtml [cleditor]
//	* js/jseditcode [codemirror]
// REQUIRED CSS:
//	* notifications.css
//	* navpager.css
//	* date-time.css
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
 * @version 	v.170901
 * @package 	Components:Framework
 *
 */
final class SmartComponents {

	// ::
	// {{{SYNC-SMART-HTTP-STATUS-CODES}}}

//================================================================
/**
 * Function: Parse Language Based Settings
 * This is intended to pre-parse the Settings to select the proper language ...
 *
 * @access 		private
 * @internal
 *
 */
public static function get_by_language_parsed_settings($arr_base_settings, $arr_local_settings) {
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
	return $arr_base_settings; // mixed
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
 * Compose an App Error Message
 *
 * @access 		private
 * @internal
 *
 */
public static function app_error_message($y_title, $y_name, $y_mode, $y_type, $y_logo, $y_width, $y_area, $y_errmsg, $y_area_one, $y_area_two) {
	//--
	$y_width = (int) $y_width;
	if($y_width < 550) {
		$y_width = 550;
	} elseif($y_width > 750) {
		$y_width = 750;
	} //end if
	//--
	$y_area = (string) trim((string)$y_area); // if this is empty will simply not be displayed
	$y_area_one = (string) trim((string)$y_area_one); // if this is empty will display: DEBUG OFF
	$y_area_two = (string) trim((string)$y_area_two); // if this is empty will display: View App Log for more details ...
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/app-error-message.inc.htm',
		[
			'WIDTH' 	=> (int) $y_width,
			'TITLE' 	=> (string) $y_title,
			'AREA' 		=> (string) $y_area,
			'LOGO' 		=> (string) $y_logo,
			'NAME' 		=> (string) $y_name,
			'MODE' 		=> (string) $y_mode,
			'TYPE' 		=> (string) $y_type,
			'ERR-MSG' 	=> (string) $y_errmsg,
			'AREA-ONE' 	=> (string) $y_area_one,
			'AREA-TWO' 	=> (string) $y_area_two,
		],
		'no'
	);
	//--
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
public static function http_status_message($y_title, $y_html_message) {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/http-message-status.htm',
		[
			'CHARSET' 			=> SmartUtils::get_encoding_charset(),
			'BASE-URL' 			=> SmartUtils::get_server_current_url(),
			'TITLE' 			=> (string) $y_title,
			'SIGNATURE-HTML' 	=> '<b>Smart.Framework // Web :: '.Smart::escape_html(SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION.' # '.SMART_SOFTWARE_APP_NAME.' / '.SMART_SOFTWARE_NAMESPACE).'</b>'.'<br>'.Smart::escape_html(SmartUtils::get_server_current_url().SmartUtils::get_server_current_script()),
			'MESSAGE-HTML' 		=> (string) $y_html_message
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
public static function http_error_message($y_title, $y_message, $y_html_message='') {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/http-message-error.htm',
		[
			'CHARSET' 			=> SmartUtils::get_encoding_charset(),
			'BASE-URL' 			=> SmartUtils::get_server_current_url(),
			'TITLE' 			=>(string) $y_title,
			'SIGNATURE-HTML' 	=> '<b>Smart.Framework // Web :: '.Smart::escape_html(SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION.' # '.SMART_SOFTWARE_APP_NAME.' / '.SMART_SOFTWARE_NAMESPACE).'</b>'.'<br>'.Smart::escape_html(SmartUtils::get_server_current_url().SmartUtils::get_server_current_script()),
			'MESSAGE-HTML' 		=> self::operation_error(Smart::escape_html((string)$y_message), '100%'),
			'EXTMSG-HTML' 		=> (string) $y_html_message
		],
		'no'
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 400 Bad Request :: The server cannot or will not process the request due to something that is perceived to be a client error (e.g., malformed request syntax, invalid request message framing, or deceptive request routing).
public static function http_message_400_badrequest($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'400.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'400.php');
			if(function_exists('custom_http_message_400_badrequest')) {
				return custom_http_message_400_badrequest($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('400 Bad Request', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 401 Unauthorized :: Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to the requested resource. See Basic access authentication and Digest access authentication.
public static function http_message_401_unauthorized($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'401.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'401.php');
			if(function_exists('custom_http_message_401_unauthorized')) {
				return custom_http_message_401_unauthorized($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('401 Unauthorized', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 403 Forbidden :: The request was a valid request, but the server is refusing to respond to it. Unlike a 401 Unauthorized response, authenticating will make no difference.
public static function http_message_403_forbidden($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'403.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'403.php');
			if(function_exists('custom_http_message_403_forbidden')) {
				return custom_http_message_403_forbidden($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('403 Forbidden', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 404 Not Found :: The requested resource could not be found but may be available again in the future. Subsequent requests by the client are permissible.
public static function http_message_404_notfound($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'404.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'404.php');
			if(function_exists('custom_http_message_404_notfound')) {
				return custom_http_message_404_notfound($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('404 Not Found', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 429 Too Many Requests :: The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.
public static function http_message_429_toomanyrequests($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'429.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'429.php');
			if(function_exists('custom_http_message_429_toomanyrequests')) {
				return custom_http_message_429_toomanyrequests($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('429 Too Many Requests', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 500 Internal Server Error :: A generic error message, given when an unexpected condition was encountered and no more specific message is suitable.
public static function http_message_500_internalerror($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'500.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'500.php');
			if(function_exists('custom_http_message_500_internalerror')) {
				return custom_http_message_500_internalerror($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('500 Internal Server Error', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 502 Bad Gateway :: The server was acting as a gateway or proxy and received an invalid response from the upstream server.
public static function http_message_502_badgateway($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'502.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'502.php');
			if(function_exists('custom_http_message_502_badgateway')) {
				return custom_http_message_502_badgateway($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('502 Bad Gateway', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 503 Service Unavailable :: The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a temporary state.
public static function http_message_503_serviceunavailable($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'503.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'503.php');
			if(function_exists('custom_http_message_503_serviceunavailable')) {
				return custom_http_message_503_serviceunavailable($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('503 Service Unavailable', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
// 504 Gateway Timeout :: The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
public static function http_message_504_gatewaytimeout($y_message, $y_html_message='') {
	//--
	global $configs;
	//--
	if(defined('SMART_FRAMEWORK_CUSTOM_ERR_PAGES')) {
		//--
		if(is_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'504.php')) {
			require_once(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'504.php');
			if(function_exists('custom_http_message_504_gatewaytimeout')) {
				return custom_http_message_504_gatewaytimeout($y_message, $y_html_message);
			} //end if
		} //end if
		//--
	} //end if
	//--
	return self::http_error_message('504 Gateway Timeout', $y_message, $y_html_message);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_question($y_html, $y_width='500') {
	//--
	return self::notifications_template($y_html, 'operation_question', $y_width); // question
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_notice($y_html, $y_width='500') {
	//--
	return self::notifications_template($y_html, 'operation_notice', $y_width); // notice
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_ok($y_html, $y_width='500') {
	//--
	return self::notifications_template($y_html, 'operation_info', $y_width); // info (ok)
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_warn($y_html, $y_width='500') {
	//--
	return self::notifications_template($y_html, 'operation_warn', $y_width); // warn
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function operation_error($y_html, $y_width='500') {
	//--
	return self::notifications_template($y_html, 'operation_error', $y_width); // error
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
private static function notifications_template($y_html, $y_idcss, $y_width) {
	//--
	$y_width = (string) self::format_css_dimension($y_width);
	//--
	if(in_array((string)$y_width, ['100%', '99%', '98%'])) {
		$y_width = '97%'; // correction because of the margin
	} //end if
	//--
	return '<!-- require: notifications.css --><div id="'.Smart::escape_html($y_idcss).'" style="width:'.Smart::escape_html($y_width).'!important;">'.$y_html.'</div>';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Format CSS Width or Height
 * Format the CSS Width: Passed number (550) or percent (100%) and return the correct CSS3 format as 550px or 100%
 *
 * @access 		private
 * @internal
 *
 */
private static function format_css_dimension($y_w_or_h) {
	//--
	if(strpos($y_w_or_h, '%') !== false) {
		//--
		$css_w_or_h = (int) str_replace([':', ';', '-', '%'], ['', '', '', ''], (string)$y_w_or_h); // Ex: 100% and dissalow styles separators : ;
		if($y_w_or_h < 1) {
			$y_w_or_h = 1;
		} //end if
		if($y_w_or_h > 100) {
			$y_w_or_h = 100;
		} //end if
		$css_w_or_h = (string) $css_w_or_h.'%';
		//--
	} elseif(strlen($y_w_or_h) > 0) {
		//--
		$y_w_or_h = (int) $y_w_or_h;
		//--
		if($y_w_or_h < 1) {
			$y_w_or_h = 1;
		} //end if
		if($y_w_or_h > 3200) {
			$y_w_or_h = 3200;
		} //end if
		//--
		$css_w_or_h = (string) $y_w_or_h.'px'; // Ex: 750px
		//--
	} else {
		//--
		$css_w_or_h = ''; // default / empty
		//--
	} //end if else
	//--
	return (string) $css_w_or_h;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: HTML Form Vars
 *
 * @access 		private
 * @internal
 *
 */
public static function html_hidden_formvars($y_var, $y_html_var) {
	//--
	$out = '';
	//--
	$regex_var = '/^([_a-zA-Z0-9])+$/';
	//--
	if(((string)$y_html_var != '') AND (preg_match((string)$regex_var, (string)$y_html_var))) {
		if(is_array($y_var)) { // SYNC VARS
			foreach($y_var as $key => $val) {
				if(((string)$key != '') AND (preg_match((string)$regex_var, (string)$key))) {
					$out .= '<input type="hidden" name="'.Smart::escape_html((string)$y_html_var).'['.Smart::escape_html((string)$key).']" value="'.Smart::escape_html((string)$val).'">'."\n";
				} //end if
			} //end for
		} elseif((string)$y_var != '') {
			$out .= '<input type="hidden" name="'.Smart::escape_html((string)$y_html_var).'" value="'.Smart::escape_html((string)$y_var).'">'."\n";
		} //end if else
	} //end if
	//--
	return (string) $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * HTML Selector: YES / NO
 * Display Yes=y/No=n Selector
 *
 * @access 		private
 * @internal
 *
 * @param STRING $y_var			:: HTML Var Name
 * @param STRING $y_val			:: '' | 'y' | 'n'
 * @return STRING				:: HTML Code
 */
public static function html_selector_yes_no($y_var, $y_val) {
	//--
	$y_var = (string) trim((string)$y_var);
	$y_val = (string) strtolower(trim((string)$y_val));
	//--
	$translator_core_messages = SmartTextTranslations::getTranslator('@core', 'messages');
	//--
	$txt_y = (string) $translator_core_messages->text('yes');
	$txt_n = (string) $translator_core_messages->text('no');
	//--
	$code = '?';
	$sel_y = '';
	$sel_n = '';
	if((string)$y_val == 'y') {
		$code = (string) $txt_y;
		$sel_y = ' checked';
	} else{ // 'n' | ''
		$code = (string) $txt_n;
		$sel_n = ' checked';
	} //end if
	//--
	if((string)$y_var != '') { // if var is non empty, show radio buttons else show just Yes or No
		$code = SmartMarkersTemplating::render_template(
			'[####TXT-YES####]<input type="radio" name="[####THE-VAR####]" value="y"[####SEL-YES####]>  &nbsp;&nbsp; [####TXT-NO####]<input type="radio" name="[####THE-VAR####]" value="n"[####SEL-NO####]>',
			[
				'TXT-YES' 	=> (string) $txt_y,
				'TXT-NO' 	=> (string) $txt_n,
				'THE-VAR' 	=> (string) Smart::escape_html((string)$y_var),
				'SEL-YES' 	=> (string) $sel_y,
				'SEL-NO' 	=> (string) $sel_n
			]
		);
	} //end if else
	//--
	return (string) $code;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * HTML Selector: TRUE / FALSE
 * Display True=1/False=0 Selector
 *
 * @access 		private
 * @internal
 *
 * @param STRING $y_var			:: HTML Var Name
 * @param STRING $y_val			:: '' | '0' | '1'
 * @return STRING				:: HTML Code
 */
public static function html_selector_true_false($y_var, $y_val) {
	//--
	$y_var = (string) trim((string)$y_var);
	$y_val = (string) strtolower(trim((string)$y_val));
	//--
	$translator_core_messages = SmartTextTranslations::getTranslator('@core', 'messages');
	//--
	$txt_y = (string) $translator_core_messages->text('yes');
	$txt_n = (string) $translator_core_messages->text('no');
	//--
	$code = '?';
	$sel_y = '';
	$sel_n = '';
	if((string)$y_val == '1') {
		$code = (string) $txt_y;
		$sel_y = ' checked';
	} else{ // '0' | ''
		$code = (string) $txt_n;
		$sel_n = ' checked';
	} //end if
	//--
	if((string)$y_var != '') { // if var is non empty, show radio buttons else show just Yes or No
		$code = SmartMarkersTemplating::render_template(
			'[####TXT-YES####]<input type="radio" name="[####THE-VAR####]" value="1"[####SEL-YES####]>  &nbsp;&nbsp; [####TXT-NO####]<input type="radio" name="[####THE-VAR####]" value="0"[####SEL-NO####]>',
			[
				'TXT-YES' 	=> (string) $txt_y,
				'TXT-NO' 	=> (string) $txt_n,
				'THE-VAR' 	=> (string) Smart::escape_html((string)$y_var),
				'SEL-YES' 	=> (string) $sel_y,
				'SEL-NO' 	=> (string) $sel_n
			]
		);
	} //end if else
	//--
	return (string) $code;
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
 * @param INTEGER			$y_dimensions			dimensions in pixels (width or width / (list) height for '#JS-UI#' or '#JS-UI-FILTER#')
 * @param CODE				$y_custom_js			custom js code (Ex: submit on change)
 * @param YES/NO			$y_raw					If Yes, the description values will not apply html special chars
 * @param YES/NO			$y_allowblank			If Yes, a blank value is allowed in list
 * @param CSS/#JS-UI#		$y_extrastyle			Extra Style CSS | '#JS-UI#' or '#JS-UI-FILTER#'
 *
 * @return HTMLCode
 */
public static function html_select_list_single($y_id, $y_selected_value, $y_mode, $yarr_data, $y_varname='', $y_dimensions='150/0', $y_custom_js='', $y_raw='no', $y_allowblank='yes', $y_extrastyle='') {

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
	$tmp_dimens = explode('/', trim((string)$y_dimensions));
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
	$css_class = '';
	//--
	if(((string)$element_id != '') && (((string)$y_extrastyle == '#JS-UI#') || ((string)$y_extrastyle == '#JS-UI-FILTER#'))) {
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
			if((string)$tmp_extra_style == '#JS-UI-FILTER#') {
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
	} else {
		//--
		$css_class = 'class="ux-field"';
		//--
	} //end if else
	//--

	//--
	$out = '';
	//--
	if((string)$y_mode == 'form') {
		//--
		$out .= '<select name="'.$y_varname.'" id="'.$element_id.'" size="1" '.$css_class.' style="width:'.$the_width.'px; '.$y_extrastyle.'" '.$y_custom_js.'>'."\n";
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
 * @param INTEGER			$y_dimensions			dimensions in pixels (width or width / (list) height for '#JS-UI#' or '#JS-UI-FILTER#')
 * @param CODE				$y_custom_js			custom js code (Ex: submit on change)
 * @param SPECIAL			$y_extrastyle			Extra Style CSS | '#JS-UI#' or '#JS-UI-FILTER#'
 *
 * @return HTMLCode
 */
public static function html_select_list_multi($y_id, $y_selected_value, $y_mode, $yarr_data, $y_varname='', $y_draw='list', $y_sync_values='no', $y_dimensions='300/0', $y_custom_js='', $y_extrastyle='#JS-UI-FILTER#') {

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
	$tmp_dimens = explode('/', trim((string)$y_dimensions));
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
	$css_class = '';
	//--
	if(((string)$element_id != '') && (((string)$y_extrastyle == '#JS-UI#') || ((string)$y_extrastyle == '#JS-UI-FILTER#'))) {
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
			if((string)$tmp_extra_style == '#JS-UI-FILTER#') {
				$have_filter = true;
				$the_width += 25;
			} else {
				$have_filter = false;
			} //end if else
			//--
			if($use_multi_list_jq === false) {
				$use_blank_value = 'yes';
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
		$use_blank_value = 'no';
		//--
		$js = '';
		$css_class = 'class="ux-field"';
		//--
	} //end if else
	//--

	//--
	$out = '';
	//--
	if((string)$y_mode == 'form') {
		//--
		if((string)$y_draw == 'checkboxes') { // checkboxes
			$out .= '<input type="hidden" name="'.$y_varname.'" value="">'."\n"; // we need a hidden value
		} else { // list
			$out .= '<select name="'.$y_varname.'" id="'.$element_id.'" '.$css_class.' style="width:'.$the_width.'px; '.$y_extrastyle.'" '.$use_multi_list_htm.' '.$y_custom_js.'>'."\n";
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
 * Creates a navigation pager
 * The style of the pager can be set overall in: $configs['nav']['pager'], and can be: arrows or numeric
 *
 * @hints			$link = 'some-script.php?ofs={{{offset}}}';
 *
 * @return HTML Code
 *
 */
public static function html_navpager($link, $total, $limit, $current, $display_if_empty=false, $adjacents=3, $options=[]) {
	//--
	$styles = '';
	//--
	$navpager_mode = (string) Smart::get_from_config('nav.pager');
	//--
	if(((string)$navpager_mode == 'arrows') OR (strpos((string)$navpager_mode, 'arrows:') === 0)) {
		//--
		if((string)$navpager_mode != 'arrows') { // arrows:path/to/navpager-arrows.inc.htm
			$tpl = trim((string)substr((string)$navpager_mode, 7));
		} else { // arrows
			$styles = '<!-- require: navpager.css -->'."\n";
			$tpl = 'lib/core/templates/navpager-arrows.inc.htm';
		} //end if else
		//--
		return (string) $styles.self::html_navpager_type_arrows($tpl, $link, $total, $limit, $current, $display_if_empty, $adjacents, $options);
		//--
	} else {
		//--
		if(strpos((string)$navpager_mode, 'numeric:') === 0) { // numeric:path/to/navpager-numeric.inc.htm
			$tpl = trim((string)substr((string)$navpager_mode, 8));
		} else { // pager
			$styles = '<!-- require: navpager.css -->'."\n";
			$tpl = 'lib/core/templates/navpager-numeric.inc.htm';
		} //end if else
		//--
		return (string) $styles.self::html_navpager_type_numeric($tpl, $link, $total, $limit, $current, $display_if_empty, $adjacents, $options);
		//--
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
// $link = 'some-script.php?ofs={{{offset}}}';
private static function html_navpager_type_arrows($tpl, $link, $total, $limit, $current, $display_if_empty=false, $adjacents=3, $options=[]) {
	//--
	$tpl = (string) $tpl;
	$link = (string) $link;
	$total = Smart::format_number_int($total, '+');
	$limit = Smart::format_number_int($limit, '+');
	$current = Smart::format_number_int($current, '+');
	$display_if_empty = (bool) $display_if_empty;
	$adjacents = Smart::format_number_int($adjacents, '+');
	$options = (array) $options;
	//--
	if($limit <= 0) {
		Smart::log_warning('NavBox ERROR: Limit is ZERO in: '.__CLASS__.'::'.__FUNCTION__.'()');
		return (string) '<!-- Navigation Pager (1) -->[ ERROR: Invalid Navigation Pager: Limit is ZERO ]<!-- #END# Navigation Pager -->';
	} //end if
	//--
	$is_paging = false;
	$orig_total = $total;
	$orig_limit = $limit;
	if((string)$options['nav-mode'] == 'pages') { // navigate by page number instead of offset
		$is_paging = true;
		$total = Smart::format_number_int(ceil($total / $limit), '+');
		$current = Smart::format_number_int(ceil($current / $limit), '+');
		$limit = (int) 1;
	} //end if
	$opt_zerolink = '';
	if((string)$options['zero-link'] != '') {
		if((string)$options['zero-link'] == '@') {
			$options['zero-link'] = (string) str_replace('{{{offset}}}', '', (string)$link);
		} //end if
		$opt_zerolink = (string) $options['zero-link'];
	} //end if
	$opt_emptydiv = '<div>&nbsp;</div>';
	if(array_key_exists('empty-div', $options)) {
		$opt_emptydiv = (string) $options['empty-div'];
	} //end if
	$showfirst = true; // show go first
	if($options['show-first'] === false) {
		$showfirst = false;
	} //end if
	$showlast = true; // show go last
	if($options['show-last'] === false) {
		$showlast = false;
	} //end if
	//--
	if($display_if_empty !== true) {
		if(($total <= 0) OR ($total <= $limit)) {
			return (string) '<!-- Navigation Pager (1) '.'T='.Smart::escape_html($total).' ; '.'L='.Smart::escape_html($limit).' ; '.'C='.Smart::escape_html($current).' -->'.$opt_emptydiv.'<!-- hidden, all results are shown (just one page) --><!-- #END# Navigation Pager -->'; // total is zero or lower than limit ; no pagination in this case
		} //end if
	} //end if
	//--
	$translator_core_nav_texts = SmartTextTranslations::getTranslator('@core', 'nav_texts');
	//--
	$txt_start 	= (string) $translator_core_nav_texts->text('start');
	$txt_prev 	= (string) $translator_core_nav_texts->text('prev');
	$txt_next 	= (string) $translator_core_nav_texts->text('next');
	$txt_end 	= (string) $translator_core_nav_texts->text('end');
	$txt_listed = (string) $translator_core_nav_texts->text('listed'); // Page
	$txt_res 	= (string) $translator_core_nav_texts->text('res'); // Results
	$txt_empty 	= (string) $translator_core_nav_texts->text('empty'); // No Results
	$txt_of 	= (string) $translator_core_nav_texts->text('of'); // of
	//--
	if($total > 0) {
		//--
		$tmp_lst_min = (int) $current + 1;
		$tmp_lst_max = (int) $current + $limit;
		//--
		$dys_next = (int) $current + $limit;
		$dys_prev = (int) $current - $limit;
		//--
		if($dys_prev < 0) {
			$dys_prev = 0;
		} //end if
		if($dys_prev > $total) {
			$dys_prev = (int) $total;
		} //end if
		//--
		if($dys_next < 0) {
			$dys_next = 0;
		} //end if
		if($dys_next > $total) {
			$dys_next = (int) $total;
		} //end if
		if($dys_next == 0) {
			$dys_prev = 0;
			$tmp_lst_min = 0;
			$tmp_lst_max = 0;
		} //end if
		//-- Fix max nav
		if($tmp_lst_max > $total) {
			$tmp_lst_max = (int) $total;
		} //end if
		//-- FFW
		$tmp_last_calc_pages = (int) floor((($total - 1) / $limit));
		$tmp_lastpage = (int) $tmp_last_calc_pages * $limit;
		//-- REW
		$tmp_firstpage = 0;
		//--
		if((string)$opt_zerolink != '') {
			$tmp_link_nav_start = (string) $opt_zerolink;
		} else {
			$tmp_link_nav_start = (string) str_replace('{{{offset}}}', $tmp_firstpage, $link);
		} //end if else
		if(((string)$opt_zerolink != '') AND ($dys_prev <= 0)) {
			$tmp_link_nav_prev = (string) $opt_zerolink;
		} else {
			$tmp_link_nav_prev = (string) str_replace('{{{offset}}}', $dys_prev, $link);
		} //end if else
		$tmp_link_nav_next = (string) str_replace('{{{offset}}}', $dys_next, $link);
		$tmp_link_nav_end = (string) str_replace('{{{offset}}}', $tmp_lastpage, $link);
		//--
		$tmp_box_nav_start = (string) $tmp_link_nav_start;
		$tmp_box_nav_prev = (string) $tmp_link_nav_prev;
		$tmp_box_nav_next = (string) $tmp_link_nav_next;
		$tmp_box_nav_end = (string) $tmp_link_nav_end;
		//--
		if($current <= 0) { // is at start
			$tmp_box_nav_start = '';
			$tmp_box_nav_prev = '';
		} //end if
		if($tmp_lst_max >= $total) { // is at end
			$tmp_box_nav_next = '';
			$tmp_box_nav_end = '';
		} //end if
		//--
		$tmp_pg_min = ceil($tmp_lst_max / $limit);
		$tmp_pg_max = ceil($total / $limit);
		//--
		if($is_paging) {
			$tmp_res_total 	= (int) $orig_total;
			$tmp_res_min 	= (int) (($tmp_lst_min - 1) * $orig_limit) + 1;
			$tmp_res_max 	= (int) $tmp_lst_min * $orig_limit;
			if($tmp_res_max > $tmp_res_total) {
				$tmp_res_max = $tmp_res_total;
			} //end if
		} else {
			$tmp_res_total 	= (int) $total;
			$tmp_res_min 	= (int) $tmp_lst_min;
			$tmp_res_max 	= (int) $tmp_lst_max;
		} //end if else
		//--
		$html = (string) SmartMarkersTemplating::render_file_template(
			(string) $tpl,
			[
				'NAV-LNK-START' 	=> (string) $tmp_box_nav_start,
				'NAV-LNK-PREV' 		=> (string) $tmp_box_nav_prev,
				'NAV-LNK-NEXT' 		=> (string) $tmp_box_nav_next,
				'NAV-LNK-END' 		=> (string) $tmp_box_nav_end,
				'NAV-TXT-START' 	=> (string) $txt_start,
				'NAV-TXT-PREV' 		=> (string) $txt_prev,
				'NAV-TXT-NEXT' 		=> (string) $txt_next,
				'NAV-TXT-END' 		=> (string) $txt_end,
				'NAV-TXT-LISTED' 	=> (string) $txt_listed,
				'NAV-TXT-EMPTY' 	=> (string) $txt_empty,
				'NAV-TXT-OF' 		=> (string) $txt_of,
				'NAV-TXT-RES' 		=> (string) $txt_res,
				'NAV-RES-MIN' 		=> (string) $tmp_res_min,
				'NAV-RES-MAX' 		=> (string) $tmp_res_max,
				'NAV-RES-TOTAL' 	=> (string) $tmp_res_total,
				'NAV-PG-MIN' 		=> (string) $tmp_pg_min,
				'NAV-PG-MAX' 		=> (string) $tmp_pg_max,
				'NAV-SHOW-FIRST' 	=> (string) ($showfirst ? 'yes' : 'no'),
				'NAV-SHOW-LAST' 	=> (string) ($showlast ? 'yes' : 'no'),
			],
			'yes' // export to cache
		);
		//--
	} else {
		//--
		if($showfirst === false) {
			$txt_start = '&nbsp;';
		} //end if
		if($showlast === false) {
			$txt_end = '&nbsp;';
		} //end if
		//--
		$html = (string) SmartMarkersTemplating::render_file_template(
			(string) $tpl,
			[
				'NAV-LNK-START' 	=> '',
				'NAV-LNK-PREV' 		=> '',
				'NAV-LNK-NEXT' 		=> '',
				'NAV-LNK-END' 		=> '',
				'NAV-TXT-START' 	=> (string) $txt_start,
				'NAV-TXT-PREV' 		=> (string) $txt_prev,
				'NAV-TXT-NEXT' 		=> (string) $txt_next,
				'NAV-TXT-END' 		=> (string) $txt_end,
				'NAV-TXT-LISTED' 	=> (string) $txt_listed,
				'NAV-TXT-EMPTY' 	=> (string) $txt_empty,
				'NAV-TXT-OF' 		=> (string) $txt_of,
				'NAV-TXT-RES' 		=> (string) $txt_res,
				'NAV-RES-MIN' 		=> (string) 0,
				'NAV-RES-MAX' 		=> (string) 0,
				'NAV-RES-TOTAL' 	=> (string) 0,
				'NAV-PG-MIN' 		=> (string) 0,
				'NAV-PG-MAX' 		=> (string) 0,
				'NAV-SHOW-FIRST' 	=> (string) ($showfirst ? 'yes' : 'no'),
				'NAV-SHOW-LAST' 	=> (string) ($showlast ? 'yes' : 'no'),
			],
			'yes' // export to cache
		);
		//--
	} //end if else
	//--
	return (string) $html;
	//--
} //END FUNCTION
//================================================================


//================================================================
// $link = 'some-script.php?ofs={{{offset}}}';
private static function html_navpager_type_numeric($tpl, $link, $total, $limit, $current, $display_if_empty=false, $adjacents=3, $options=[]) {
	//--
	$tpl = (string) $tpl;
	$link = (string) $link;
	$total = Smart::format_number_int($total, '+');
	$limit = Smart::format_number_int($limit, '+');
	$current = Smart::format_number_int($current, '+');
	$display_if_empty = (bool) $display_if_empty;
	$adjacents = Smart::format_number_int($adjacents, '+');
	$options = (array) $options;
	//--
	if($limit <= 0) {
		Smart::log_warning('NavBox ERROR: Limit is ZERO in: '.__CLASS__.'::'.__FUNCTION__.'()');
		return (string) '<!-- Navigation Pager (2) -->[ ERROR: Invalid Navigation Pager: Limit is ZERO ]<!-- #END# Navigation Pager -->';
	} //end if
	//--
	if((string)$options['nav-mode'] == 'pages') { // navigate by page number instead of offset
		$total = Smart::format_number_int(ceil($total / $limit), '+');
		$current = Smart::format_number_int(ceil($current / $limit), '+');
		$limit = (int) 1;
	} //end if
	$opt_zerolink = '';
	if((string)$options['zero-link'] != '') {
		if((string)$options['zero-link'] == '@') {
			$options['zero-link'] = (string) str_replace('{{{offset}}}', '', (string)$link);
		} //end if
		$opt_zerolink = (string) $options['zero-link'];
	} //end if
	$opt_emptydiv = '<div>&nbsp;</div>';
	if(array_key_exists('empty-div', $options)) {
		$opt_emptydiv = (string) $options['empty-div'];
	} //end if
	$showfirst = true; // show go prev-next
	if($options['show-first'] === false) {
		$showfirst = false;
	} //end if
	$showlast = true; // show go last
	if($options['show-last'] === false) {
		$showlast = false;
	} //end if
	//--
	if($display_if_empty !== true) {
		if(($total <= 0) OR ($total <= $limit)) {
			return (string) '<!-- Navigation Pager (2) '.'T='.Smart::escape_html($total).' ; '.'L='.Smart::escape_html($limit).' ; '.'C='.Smart::escape_html($current).' -->'.$opt_emptydiv.'<!-- hidden, all results are shown (just one page) --><!-- #END# Navigation Pager -->'; // total is zero or lower than limit ; no pagination in this case
		} //end if
	} //end if
	//--
	$translator_core_nav_texts = SmartTextTranslations::getTranslator('@core', 'nav_texts');
	//--
	if($total > 0) {
		//--
		if($adjacents <= 0) {
			$adjacents = 2; // fix
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
		$prev = $crr - 1;
		if($prev <= 0) {
			$txt_prev = '';
			$lnk_prev = '';
		} else {
			$txt_prev = (string) $translator_core_nav_texts->text('prev');
			if(((string)$opt_zerolink != '') AND (((int)(($prev-1)*$limit)) <= 0)) {
				$lnk_prev = (string) $opt_zerolink;
			} else {
				$lnk_prev = (string) str_replace('{{{offset}}}', (int)(($prev-1)*$limit), (string)$link);
			} //end if else
		} //end if
		$next = $crr + 1;
		if($next > $max) {
			$txt_next = '';
			$lnk_next = '';
		} else {
			$txt_next = (string) $translator_core_nav_texts->text('next');
			$lnk_next = (string) str_replace('{{{offset}}}', (int)(($next-1)*$limit), (string)$link);
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
		$data = array();
		//--
		if((string)$arr[(string)$min] == '') {
			if((int)$min === (int)$crr) {
				$data[(string)$min] = 'SELECTED';
			} else {
				if((string)$opt_zerolink != '') {
					$data[(string)$min] = (string) $opt_zerolink;
				} else {
					$data[(string)$min] = (string) str_replace('{{{offset}}}', (int)(($min-1)*$limit), (string)$link);
				} //end if else
			} //end if else
			if(($max > ($adjacents + 1)) AND ((string)$arr[(string)($min+1)] == '')) {
				$data['.'] = 'DOTS';
			} //end if else
		} //end if
		//--
		foreach($arr as $key => $val) {
			if((int)$val === (int)$crr) {
				$data[(string)$key] = 'SELECTED';
			} else {
				$data[(string)$key] = (string) str_replace('{{{offset}}}', (int)(($val-1)*$limit), (string)$link);
			} //end if else
		} //end foreach
		//--
		if((string)$arr[(string)$max] == '') {
			if(($max > ($adjacents + 1)) AND ((string)$arr[(string)($max-1)] == '')) {
				$data['..'] = 'DOTS';
			} else {
				$showlast = true; // fix if on last pages !!
			} //end if else
			if((int)$max === (int)$crr) {
				$data[(string)$max] = 'SELECTED';
			} else {
				$data[(string)$max] = (string) str_replace('{{{offset}}}', (int)(($max-1)*$limit), (string)$link);
			} //end if else
		} //end if
		//--
		$html = (string) SmartMarkersTemplating::render_file_template(
			(string) $tpl,
			[
				'DATA-ARR' 		=> (array) $data,
				'PREV-PAGE' 	=> (string) $txt_prev,
				'PREV-LINK' 	=> (string) $lnk_prev,
				'NEXT-PAGE' 	=> (string) $txt_next,
				'NEXT-LINK' 	=> (string) $lnk_next,
				'TOTAL'			=> (int) $total,
				'LIMIT' 		=> (int) $limit,
				'CURRENT' 		=> (int) $current,
				'SHOW-FIRST' 	=> (string) ($showfirst ? 'yes' : 'no'),
				'SHOW-LAST' 	=> (string) (($showlast || ($current >= ($total - $adjacents - 1))) ? 'yes' : 'no'),
				'NO-RESULTS' 	=> '' // must be empty in this case
			],
			'yes' // export to cache
		);
		//--
	} else {
		//--
		$html = (string) SmartMarkersTemplating::render_file_template(
			(string) $tpl,
			[
				'DATA-ARR' 		=> [],
				'PREV-PAGE' 	=> '',
				'PREV-LINK' 	=> '',
				'NEXT-PAGE' 	=> '',
				'NEXT-LINK' 	=> '',
				'TOTAL'			=> 0,
				'LIMIT' 		=> 0,
				'CURRENT' 		=> 0,
				'SHOW-FIRST' 	=> 'no',
				'SHOW-LAST' 	=> 'no',
				'NO-RESULTS' 	=> (string) $translator_core_nav_texts->text('empty') // must be non-empty in this case
			],
			'yes' // export to cache
		);
		//--
	} //end if else
	//--
	return (string) '<!-- Navigation Pager (2) '.'T='.Smart::escape_html($total).' ; '.'L='.Smart::escape_html($limit).' ; '.'C='.Smart::escape_html($current).' -->'.$html.'<!-- #END# Navigation Pager -->';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draws a HTML JS-UI Date Selector Field
 *
 * @param STRING 	$y_id					[HTML page ID for field (unique) ; used foor JavaScript]
 * @param STRING 	$y_var					[HTML Variable Name or empty if no necessary]
 * @param DATE 		$yvalue					[DATE, empty or formated as YYYY-MM-DD]
 * @param STRING 	$y_text_select			[The text as title: 'Select Date']
 * @param JS-Date 	$yjs_mindate			[JS Expression, Min Date] :: new Date(1937, 1 - 1, 1) or '-1y -1m -1d'
 * @param JS-Date 	$yjs_maxdate			[JS Expression, Max Date] :: new Date(2037, 12 - 1, 31) or '1y 1m 1d'
 * @param ARRAY 	$y_extra_options		[Options Array[width, ...] for for datePicker]
 * @param JS-Code 	$y_js_evcode			[JS Code to execute on Select(date)]
 *
 * @return STRING 							[HTML Code]
 */
public static function html_js_date_field($y_id, $y_var, $yvalue, $y_text_select='', $yjs_mindate='', $yjs_maxdate='', $y_extra_options=array(), $y_js_evcode='') {
	//-- v.160306
	if((string)$yvalue != '') {
		$yvalue = date('Y-m-d', @strtotime($yvalue)); // enforce this date format for internals and be sure is valid
	} //end if
	//--
	$y_js_evcode = (string) trim((string)$y_js_evcode);
	//--
	if((int)Smart::get_from_config('regional.calendar-week-start') == 1) {
		$the_first_day = 1; // Calendar Start on Monday
	} else {
		$the_first_day = 0; // Calendar Start on Sunday
	} //end if else
	//--
	$the_altdate_format = self::get_date_format_for_js((string)Smart::get_from_config('regional.calendar-date-format-client'));
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
			'FDOW' 				=> (int)    $the_first_day, // of week
			'DATE-MIN' 			=> (string) $yjs_mindate,
			'DATE-MAX' 			=> (string) $yjs_maxdate,
			'EVAL-JS' 			=> (string) $y_js_evcode
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Draws a HTML JS-UI Time Selector Field
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
 * @param JS-Code 	$y_js_evcode			[JS Code to execute on Select(time)]
 *
 * @return STRING 							[HTML Code]
 */
public static function html_js_time_field($y_id, $y_var, $yvalue, $y_text_select='', $y_h_st='0', $y_h_end='23', $y_i_st='0', $y_i_end='55', $y_i_step='5', $y_rows='2', $y_extra_options=array(), $y_js_evcode='') {
	//-- v.160306
	if((string)$yvalue != '') {
		$yvalue = date('H:i', @strtotime(date('Y-m-d').' '.$yvalue)); // enforce this time format for internals and be sure is valid
	} //end if
	//--
	$y_js_evcode = (string) trim((string)$y_js_evcode);
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
			'H-START' 		=> (int)    $prep_hstart,
			'H-END' 		=> (int)    $prep_hend,
			'MIN-START'		=> (int)    $prep_istart,
			'MIN-END' 		=> (int)    $prep_iend,
			'MIN-INTERVAL' 	=> (int)    $prep_iinterv,
			'DISPLAY-ROWS' 	=> (int)    $prep_rows,
			'EVAL-JS' 		=> (string) $y_js_evcode
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Draw Limited Text Area
 *
 * @access 		private
 * @internal
 *
 */
public static function html_js_limited_text_area($y_field_id, $y_var_name, $y_var_value, $y_limit, $y_css_w='125px', $y_css_h='50px', $y_placeholder='', $y_wrap='physical', $y_rawval='no') {
	//--
	$y_limit = (int) $y_limit; // max characters :: between 100 and 99999
	//--
	if($y_limit < 50) {
		$y_limit = 50;
	} elseif($y_limit > 99999) {
		$y_limit = 99999;
	} //end if
	//--
	if($y_rawval != 'yes') {
		$y_var_value = Smart::escape_html($y_var_value);
	} //end if
	//--
	if((string)$y_field_id != '') {
		$field = (string) $y_field_id;
	} else { //  no ID, generate a hash
		$fldhash = sha1('Limited Text Area :: '.$y_var_name.' @@ '.$y_limit.' #').'_'.Smart::uuid_10_str();
		$field = '__Fld_TEXTAREA__'.$fldhash.'__NO_Id__';
	} //end if else
	//--
	$placeholder = '';
	if((string)$y_placeholder != '') {
		$placeholder = ' placeholder="'.Smart::escape_html($y_placeholder).'"';
	} //end if
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/limited-text-area.inc.htm',
		[
			'LIMIT-CHARS' 		=> (int) $y_limit,
			'ID-AREA' 			=> (string) $field,
			'VAR-AREA' 			=> (string) $y_var_name,
			'VAL-AREA-HTML' 	=> (string) $y_var_value, // this is pre-escaped if not raw
			'WRAP-MODE' 		=> (string) $y_wrap,
			'WIDTH' 			=> (string) $y_css_w,
			'HEIGHT' 			=> (string) $y_css_h,
			'PLACEHOLDER-HTML' 	=> (string) $placeholder
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the HTML / CSS / Javascript code to Load the required Javascripts for the Highlight.Js
 * Should be called just once per a HTML page
 *
 * @access 		private
 * @internal
 *
 * @param STRING 	$dom_selector		The HTML-DOM Selector as container(s) for Pre>Code (see jQuery ...)
 * @param ARRAY 	$plugins 			The Array with enum of packages to load
 * @param ENUM 		$theme 				The Visual CSS Theme to Load
 *
 * @return STRING						[HTML Code]
 */
public static function js_code_highlightsyntax($dom_selector, $plugins=['web'], $theme='github') {
	//--
	$theme = (string) strtolower((string)$theme);
	switch((string)$theme) {
		case 'arta':
		case 'atom-one-light':
		case 'codepen-embed':
		case 'dracula':
		case 'github-gist':
		case 'github':
		case 'googlecode':
		case 'grayscale':
		case 'mono-blue':
		case 'monokai-sublime':
		case 'ocean':
		case 'rainbow':
		case 'solarized-dark':
		case 'sunburst':
		case 'tomorrow-night-blue':
		case 'tomorrow':
		case 'xcode':
		case 'zenburn':
			$theme = (string) $theme;
			break;
		case 'default':
		default:
			$theme = 'default';
	} //end switch
	//--
	$arr_packs = [
		'web'  => 'css, diff, ini, javascript, json, less, markdown, php, scss, sql, xml, yaml',
		'tpl'  => 'markertpl, tex, twig',
		'lnx'  => 'awk, bash, perl, shell',
		'srv'  => 'accesslog, apache, dns, nginx, pf',
		'net'  => 'csp, http, ldif, protobuf',
		'lang' => 'basic, cmake, coffeescript, cpp, cs, delphi, erlang, fortran, fsharp, go, haskell, java, lua, makefile, objectivec, ocaml, openscad, python, r, ruby, scala, swift, tcl, vala',
		'ms'   => 'dos, powershell, typescript, vbnet, vbscript',
		'hw'   => 'armasm, llvm, mipsasm, vhdl, x86asm'
	];
	//--
	$arr_stx_plugs = [];
	foreach($arr_packs as $key => $val) {
		$key = (string) strtolower((string)trim((string)$key));
		if((Smart::array_size($plugins) <= 0) OR (in_array((string)$key, (array)$plugins))) {
			if((string)$key != '') {
				$tmp_arr = (array) explode(',', (string)$val);
				for($i=0; $i<Smart::array_size($tmp_arr); $i++) {
					$tmp_arr[$i] = (string) trim((string)$tmp_arr[$i]);
					if((string)$tmp_arr[$i] != '') {
						$arr_stx_plugs[] = (string) $key.'/'.strtolower((string)$tmp_arr[$i]);
					} //end if
				} //end if
				$tmp_arr = [];
			} //end if
		} //end if
	} //end foreach
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/syntax-highlight.inc.htm',
		[
			'CSS-THEME' 		=> (string) $theme,
			'AREAS-SELECTOR' 	=> (string) $dom_selector,
			'SYNTAX-PLUGINS' 	=> (array)  $arr_stx_plugs
		]
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Refresh Parent
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_wnd_refresh_parent($y_redir_url='') {
	//--
	$y_redir_url = (string) $y_redir_url;
	if((string)$y_redir_url != '') {
		return 'SmartJS_BrowserUtils.RefreshParent(\''.Smart::escape_js((string)$y_redir_url).'\');';
	} else {
		return 'SmartJS_BrowserUtils.RefreshParent();';
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * (Delayed) close Pop-Up / Modal
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_wnd_close_modal_popup($y_delay=-1) {
	//--
	$y_delay = (int) $y_delay; // microseconds
	if($y_delay > 0) {
		return 'SmartJS_BrowserUtils.CloseDelayedModalPopUp('.(int)$y_delay.');';
	} else {
		return 'SmartJS_BrowserUtils.CloseDelayedModalPopUp();';
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the JS Code to Submit a HTML Form by Ajax
 * Expects a standardized (json) reply created with SmartComponents::js_ajax_replyto_html_form()
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @param $y_form_id 			HTML form ID (Example: myForm)
 * @param $y_script_url 		the php script to post to (Example: admin.php)
 * @param $y_confirm_question 	if not empty will ask a confirmation question
 * @param $y_js_evcode			if not empty, JS to execute on Success (before anything else)
 *
 * @return STRING				[javascript code]
 */
public static function js_ajax_submit_html_form($y_form_id, $y_script_url, $y_confirm_question='', $y_js_evcode='') {
	//--
	$y_js_evcode = (string) trim((string)$y_js_evcode);
	//--
	$tmp_use_growl = 'auto';
	//--
	$js_post = 'SmartJS_BrowserUtils.Submit_Form_By_Ajax(\''.Smart::escape_js($y_form_id).'\', \''.Smart::escape_js($y_script_url).'\', \''.Smart::escape_js($tmp_use_growl).'\', \''.Smart::escape_js($y_js_evcode).'\');';
	//--
	if(strlen($y_confirm_question) > 0) {
		$js_post = (string) self::js_code_ui_confirm_dialog($y_confirm_question, (string)$js_post);
	} else {
		$js_post = (string) $js_post;
	} //end if else
	//--
	return (string) $js_post;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Reply back to the HTML Form submited by Ajax by returning a Json answer
 * Creates a standardized (json) reply for SmartComponents::js_ajax_submit_html_form()
 *
 * NOTICE:
 * - if OK: and redirect URL have been provided, the replace div is not handled
 * - if ERROR: no replace div or redirect is handled
 *
 * @param 	$y_status 			OK / ERROR
 * @param 	$y_title 			Dialog Title
 * @param 	$y_message 			Dialog Message (Optional in the case of Success)
 * @param 	$y_redirect_url 	**OPTIONAL** URL to redirect on either Success or Error
 * @param 	$y_replace_div 		**OPTIONAL** The ID of the DIV to Replace on Success
 * @param 	$y_replace_html 	**OPTIONAL** the HTML Code to replace in DIV on Success
 * @param 	$y_js_evcode 		**OPTIONAL** the JS EvCode to be executed on either Success or Error (before redirect or Div Replace)
 *
 * @return MIXED				[JSON data]
 *
 */
public static function js_ajax_replyto_html_form($y_status, $y_title, $y_message, $y_redirect_url='', $y_replace_div='', $y_replace_html='', $y_js_evcode='') {
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
	return (string) Smart::json_encode([
		'completed'			=> 'DONE',
		'status'			=> (string) $y_status,
		'action'			=> (string) $button_text,
		'title'				=> (string) $y_title,
		'message'			=> (string) $y_message,
		'js_evcode' 		=> (string) $y_js_evcode,
		'redirect'			=> (string) $y_redirect_url,
		'replace_div'		=> (string) $y_replace_div,
		'replace_html'		=> (string) $y_replace_html
	]);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: JS Escape Mixed JS Code
 *
 * @access 		private
 * @internal
 *
 */
public static function escape_js_mixed_type_code($y_jscode) {
	//--
	$y_jscode = (string) trim((string)$y_jscode);
	//--
	$iscode = false;
	if(substr($y_jscode, 0, 11) == 'javascript:') {
		$iscode = true;
		$y_jscode = (string) trim((string)substr((string)$y_jscode, 11)); // javascript explicit prefixed executable code (ex: javascript: some code) ; need to remove out the javascript: part
	} elseif(preg_match('/^\s?function\s?\(/i', (string)$y_jscode)) {
		$iscode = true;
		$y_jscode = (string) $y_jscode; // javascript variable function (ex: function(){ ...})
	} //end if else
	if(($iscode === false) OR ((string)$y_jscode == '')) {
		$y_jscode = (string) "'".Smart::escape_js($y_jscode)."'"; // text or eval code
	} //end if
	//--
	return (string) $y_jscode;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Returns the JS Code to add (raise) a Growl Notification (sticky or not)
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_notification_add($y_title, $y_text, $y_image, $y_time=6000, $y_sticky='false', $y_class='') {
	//--
	$y_title 	= (string) self::escape_js_mixed_type_code($y_title);
	$y_text 	= (string) self::escape_js_mixed_type_code($y_text);
	//--
	if((string)$y_sticky != 'true') {
		$y_sticky = 'false';
	} //end if
	//--
	$y_time = (int) $y_time;
	if($y_time < 1) {
		$y_time = 1; // miliseconds
	} //end if
	//--
	return 'SmartJS_BrowserUtils.GrowlNotificationAdd('.$y_title.', '.$y_text.', \''.Smart::escape_js($y_image).'\', '.(int)$y_time.', '.(string)$y_sticky.', \''.Smart::escape_js((string)$y_class).'\');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Returns the JS Code to remove a Growl Notification (sticky or not)
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_notification_remove($y_id='') {
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
 * Return the JS Code to init a JS-UI Confirm Dialog
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_ui_confirm_dialog($y_question_html, $y_ok_jscript_function='', $y_width='550', $y_height='250', $y_title='?') {
	//--
	return 'SmartJS_BrowserUtils.confirm_Dialog(\''.Smart::escape_js($y_question_html).'\', \''.Smart::escape_js($y_ok_jscript_function).'\', \''.Smart::escape_js($y_title).'\', '.(int)$y_width.', '.(int)$y_height.');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the JS Code to init a JS-UI Alert Dialog
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_ui_alert_dialog($y_message, $y_ok_jscript_function='', $y_width='', $y_height='', $y_title='!') {
	//--
	return 'SmartJS_BrowserUtils.alert_Dialog(\''.Smart::escape_js($y_message).'\', \''.Smart::escape_js($y_ok_jscript_function).'\', \''.Smart::escape_js($y_title).'\', '.(int)$y_width.', '.(int)$y_height.');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the JS Code to Confirm Form Submit by raising a Dialog / Notification (depend on global settings)
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_confirm_form_submit($y_question, $y_popuptarget='', $y_width='', $y_height='', $y_force_popup='', $y_force_dims='') {
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
/**
 * Return the JS Code to Init Page-Away Confirmation when trying to leave a page
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_init_away_page($y_question='') {
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
	return 'SmartJS_BrowserUtils.PageAwayControl(\''.Smart::escape_js($y_question).'\');';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Returns the JS Code to Init an Input Field with AutoComplete Single
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_init_select_autocomplete_single($y_element_id, $y_script, $y_term_var, $y_min_len=1, $y_js_evcode='') {
	//--
	$y_min_len = Smart::format_number_int($y_min_len, '+');
	if($y_min_len < 1) {
		$y_min_len = 1;
	} elseif($y_min_len > 255) {
		$y_min_len = 255;
	} //end if
	//--
	$y_js_evcode = (string) trim((string)$y_js_evcode);
	//--
	return 'try { SmartJS_BrowserUIUtils.AutoCompleteField(\'single\', \''.Smart::escape_js((string)$y_element_id).'\', \''.Smart::escape_js((string)$y_script).'\', \''.Smart::escape_js((string)$y_term_var).'\', '.(int)$y_min_len.', \''.Smart::escape_js((string)$y_js_evcode).'\'); } catch(e) { console.log(\'Failed to initialize JS-UI AutoComplete-Single: \' + e); }';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Returns the JS Code to Init an Input Field with AutoComplete Multi
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_init_select_autocomplete_multi($y_element_id, $y_script, $y_term_var, $y_min_len=1, $y_js_evcode='') {
	//--
	$y_min_len = Smart::format_number_int($y_min_len, '+');
	if($y_min_len < 1) {
		$y_min_len = 1;
	} elseif($y_min_len > 255) {
		$y_min_len = 255;
	} //end if
	//--
	$y_js_evcode = (string) trim((string)$y_js_evcode);
	//--
	return 'try { SmartJS_BrowserUIUtils.AutoCompleteField(\'multilist\', \''.Smart::escape_js((string)$y_element_id).'\', \''.Smart::escape_js((string)$y_script).'\', \''.Smart::escape_js((string)$y_term_var).'\', '.(int)$y_min_len.', \''.Smart::escape_js((string)$y_js_evcode).'\'); } catch(e) { console.log(\'Failed to initialize JS-UI AutoComplete-Multi: \' + e); }';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Returns the JS Code to Init a JS-UI Tabs Element
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_uitabs_init($y_id_of_tabs, $y_selected=0, $y_prevent_reload=false) {
	//--
	$y_selected = Smart::format_number_int($y_selected, '+');
	//--
	if($y_prevent_reload === true) {
		$prevreload = 'true';
	} else {
		$prevreload = 'false';
	} //end if else
	//--
	return 'try { SmartJS_BrowserUIUtils.Tabs_Init(\''.Smart::escape_js($y_id_of_tabs).'\', '.$y_selected.', '.$prevreload.'); } catch(e) { console.log(\'Failed to initialize JS-UI Tabs: \' + e); }';
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Returns the JS Code to Activate/Deactivate JS-UI Tabs Element
 * Must be enclosed in a <script type="text/javascript">...</script> html tag or can be used for a JS action (ex: onClick="...")
 *
 * @access 		private
 * @internal
 *
 */
public static function js_code_uitabs_activate($y_id_of_tabs, $y_activate) {
	//--
	if($y_activate === false) {
		$activate = 'false';
	} else {
		$activate = 'true';
	} //end if else
	//--
	return 'try { SmartJS_BrowserUIUtils.Tabs_Activate(\''.Smart::escape_js($y_id_of_tabs).'\', '.$activate.'); } catch(e) { console.log(\'Failed to activate JS-UI Tabs: \' + e); }';
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function html_js_preview_iframe($yid, $y_contents, $y_width='720px', $y_height='300px', $y_maximized=false, $y_sandbox='allow-popups') {
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/preview-iframe-draw.inc.htm',
		[
			'IFRM-ID' 		=> (string) $yid,
			'WIDTH' 		=> (string) $y_width,
			'HEIGHT' 		=> (string) $y_height,
			'SANDBOX' 		=> (string) $y_sandbox,
			'MAXIMIZED' 	=> (bool)   $y_maximized,
			'CONTENT' 		=> (string) $y_contents
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Return the HTML / Javascript code to Load the required Javascripts for the Code Editor (Edit Area).
 * Should be called just once, before calling one or many ::html_js_editarea()
 *
 * @return STRING						[HTML Code]
 */
public static function html_jsload_editarea() {
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
 * Return the HTML / Javascript code with a special TextArea with a built-in javascript Code Editor (Edit Area).
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
public static function html_js_editarea($yid, $yvarname, $yvalue='', $y_mode='text', $y_editable=true, $y_width='720px', $y_height='300px', $y_line_numbers=true) {
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
			$the_mode = 'application/x-php';
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
			'SHOW-LINE-NUM' 	=> (bool)   $y_line_numbers,
			'READ-ONLY' 		=> (bool)   $is_readonly,
			'BLINK-CURSOR' 		=> (int)    Smart::format_number_int($cursor_blinking,'+'),
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
/**
 * Outputs the HTML Code to init the HTML (wysiwyg) Editor
 *
 * @param $y_filebrowser_link STRING 		URL to Image Browser (Example: script.php?op=image-gallery&type=images)
 *
 * @return STRING							[HTML Code]
 */
public static function html_jsload_htmlarea($y_filebrowser_link='') {
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
public static function html_js_htmlarea($yid, $yvarname, $yvalue='', $ywidth='720px', $yheight='480px', $y_allow_scripts=false, $y_allow_script_src=false, $y_cleaner_deftags='', $y_cleaner_mode='', $y_toolbar_ctrls='') {
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
			'TXT-AREA-ALLOW-SCRIPTS' 		=> (bool)   $y_allow_scripts, // boolean
			'TXT-AREA-ALLOW-SCRIPT-SRC' 	=> (bool)   $y_allow_script_src, // boolean
			'CLEANER-REMOVE-TAGS' 			=> (string) Smart::json_encode($y_cleaner_deftags), // mixed, will be json encoded in tpl
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
 * Returns HTML / JS code for CallBack Mapping for HTML (wysiwyg) Editor - FileBrowser Integration
 *
 * @param STRING $yurl					The Callback URL
 * @param BOOLEAN $is_popup 			Set to True if Popup (incl. Modal)
 *
 * @return STRING						[JS Code]
 */
public static function html_js_htmlarea_fm_callback($yurl, $is_popup=false) {
	//--
	return (string) str_replace(array("\r\n", "\r", "\n", "\t"), array(' ', ' ', ' ', ' '), (string)SmartMarkersTemplating::render_file_template(
		'lib/core/templates/html-editor-fm-callback.inc.js',
		[
			'IS_POPUP' 	=> (bool)   $is_popup,
			'URL' 		=> (string) $yurl
		],
		'yes' // export to cache
	));
	//--
} //END FUNCTION
//================================================================


//### END CHECK


//================================================================
/**
 * Function: Draw App Powered Info
 *
 * @access 		private
 * @internal
 *
 */
public static function app_powered_info($y_show_versions, $y_software_name='', $y_software_powered_logo='', $y_software_powered_url='') {
	//--
	global $configs;
	//--
	$arr_os = (array) self::get_imgdesc_by_os_id((string)SmartUtils::get_server_os());
	$os_pict = (string) $arr_os['img'];
	$os_desc = (string) $arr_os['desc'];
	$os_desc = 'Server Powered by '.$os_desc;
	//--
	if(((string)$y_software_name == '') OR ((string)$y_software_powered_logo == '')) {
		$y_software_name = 'Smart.Framework, a PHP / Javascript Web Framework';
		$y_software_powered_logo = 'lib/framework/img/powered_by_smart_framework.png';
		$y_software_powered_url = (string) SMART_FRAMEWORK_RELEASE_URL;
	} //end if
	//--
	$tmp_arr_web_server = SmartUtils::get_webserver_version();
	$name_webserver = (string) $tmp_arr_web_server['name'];
	//--
	if((string)$y_show_versions == 'yes') { // expose versions (not recommended in web area, except for auth admins)
		$y_software_name .= ' :: '.SMART_SOFTWARE_APP_NAME;
		$version_webserver = ' :: '.$tmp_arr_web_server['version'];
		$version_php = ' :: '.PHP_VERSION;
	} else { // avoid expose versions
		$version_webserver = '';
		$version_php = '';
	} //end if else
	//--
	if(trim(strtolower($name_webserver)) == 'apache') {
		$name_webserver = 'Apache';
		$icon_webserver_powered = 'lib/framework/img/powered_by_apache.png';
		$icon_webserver_logo = 'lib/framework/img/apache-logo.svg';
	} else {
		$name_webserver = 'Nginx / '.$name_webserver;
		$icon_webserver_powered = 'lib/framework/img/powered_by_nginx.png';
		$icon_webserver_logo = 'lib/framework/img/nginx-logo.svg';
	} //end if else
	//--
	$version_dbserver = '';
	if(is_array($configs['pgsql'])) {
		if((defined('SMART_FRAMEWORK_DB_VERSION_PostgreSQL')) AND ((string)$y_show_versions == 'yes')) {
			$version_dbserver = ' :: '.SMART_FRAMEWORK_DB_VERSION_PostgreSQL;
		} //end if
		$name_dbserver = 'PostgreSQL';
		$icon_dbserver_powered = 'lib/core/img/db/powered_by_postgresql.png';
		$icon_dbserver_logo = 'lib/core/img/db/postgresql-logo.svg';
	} else {
		$name_dbserver = '';
		$icon_dbserver_powered = '';
		$icon_dbserver_logo = '';
	} //end if else
	//--
	if(is_array($configs['redis'])) {
		$name_cacheserver = 'Redis';
		$icon_cacheserver_powered = 'lib/core/img/db/powered_by_redis.png';
		$icon_cacheserver_logo = 'lib/core/img/db/redis-logo.svg';
	} else {
		$name_cacheserver = '';
		$icon_cacheserver_powered = '';
		$icon_cacheserver_logo = '';
	} //end if
	//--
	if(is_array($configs['sqlite'])) {
		$show_last_entry = 'sqlite';
		$name_db_embedded = 'SQLite Embedded Database';
		$icon_db_embedded_powered = 'lib/core/img/db/powered_by_sqlite.png';
		$icon_db_embedded_logo = 'lib/core/img/db/sqlite-logo.svg';
	} else {
		$show_last_entry = 'firefox';
		$name_db_embedded = 'Firefox - The Open-Source Web Browser';
		$icon_db_embedded_powered = 'lib/framework/img/powered_optimized_firefox.png';
		$icon_db_embedded_logo = 'lib/core/img/browser/fox.svg';
	} //end if else
	//--
	return (string) SmartMarkersTemplating::render_file_template(
		'lib/core/templates/app-powered-info.inc.htm',
		[
			'OS-LOGO-IMG' 					=> (string) $os_pict,
			'OS-LOGO-DESC' 					=> (string) $os_desc,
			'WEB-SERVER-NAME' 				=> (string) $name_webserver,
			'WEB-SERVER-POWERED-VERSION' 	=> (string) $name_webserver.$version_webserver,
			'WEB-SERVER-POWERED-ICON' 		=> (string) $icon_webserver_powered,
			'WEB-SERVER-VERSION' 			=> (string) $name_webserver.' Web Server',
			'WEB-SERVER-ICON' 				=> (string) $icon_webserver_logo,
			'PHP-VERSION' 					=> (string) $version_php,
			'DBSERVER-NAME' 				=> (string) $name_dbserver,
			'DBSERVER-VERSION' 				=> (string) $version_dbserver,
			'DBSERVER-POWERED-ICON' 		=> (string) $icon_dbserver_powered,
			'DBSERVER-POWERED-LOGO' 		=> (string) $icon_dbserver_logo,
			'CACHESERVER-NAME' 				=> (string) $name_cacheserver,
			'CACHESERVER-POWERED-ICON' 		=> (string) $icon_cacheserver_powered,
			'CACHESERVER-POWERED-LOGO' 		=> (string) $icon_cacheserver_logo,
			'LAST-ENTRY-TYPE' 				=> (string) $show_last_entry,
			'DBEMBEDDED-NAME' 				=> (string) $name_db_embedded,
			'DBEMBEDDED-POWERED-ICON' 		=> (string) $icon_db_embedded_powered,
			'DBEMBEDDED-POWERED-LOGO' 		=> (string) $icon_db_embedded_logo,
			'SOFTWARE-NAME' 				=> (string) $y_software_name,
			'SOFTWARE-POWERED-LOGO' 		=> (string) $y_software_powered_logo,
			'SOFTWARE-POWERED-URL' 			=> (string) $y_software_powered_url
		]
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function render_app_template($template_path, $template_file, $arr_data) { // {{{SYNC-ARRAY-MAKE-KEYS-LOWER}}}

	//--
	$template_path = (string) Smart::safe_pathname((string)SmartFileSysUtils::add_dir_last_slash((string)trim((string)$template_path)));
	$template_file = (string) Smart::safe_filename((string)trim((string)$template_file));
	//--
	$arr_data = (array) array_change_key_case((array)$arr_data, CASE_LOWER); // make all keys lower (only 1st level, not nested), to comply with SmartAbstractAppController handling mode
	//--

	//--
	if(SMART_FRAMEWORK_ADMIN_AREA === true) {
		$the_area = 'admin';
		$the_realm = 'ADM';
	} else {
		$the_area = 'index';
		$the_realm = 'IDX';
	} //end if else
	//--
	$os_bw = (array) SmartUtils::get_os_browser_ip();
	//--

	//-- external TPL vars
	$arr_data['release-hash'] 			= (string) SmartFrameworkRuntime::getAppReleaseHash(); // the release hash based on app framework version, framework release and modules version
	$arr_data['semaphore'] 				= (string) $arr_data['semaphore']; // a general purpose conditional var
	$arr_data['title'] 					= (string) $arr_data['title'];
	$arr_data['head-meta'] 				= (string) $arr_data['head-meta'];
	$arr_data['head-css'] 				= (string) $arr_data['head-css'];
	$arr_data['head-js'] 				= (string) $arr_data['head-js'];
	$arr_data['header'] 				= (string) $arr_data['header'];
	$arr_data['main'] 					= (string) $arr_data['main'];
	$arr_data['aside'] 					= (string) $arr_data['aside'];
	$arr_data['footer'] 				= (string) $arr_data['footer'];
	//-- internal TPL vars
	$arr_data['template-path'] 			= (string) $template_path; 											// current template path (ex: etc/templates/default/)
	$arr_data['template-file'] 			= (string) $template_file; 											// current template file (ex: template.htm | template-modal.htm | ...)
	$arr_data['lang'] 					= (string) SmartTextTranslations::getLanguage(); 					// current set language (ex: en)
	$arr_data['client-browser'] 		= (string) $os_bw['bw']; 											// client browser OS (ex: bsd)
	$arr_data['client-os'] 				= (string) $os_bw['os']; 											// client browser ID (ex: fox)
	$arr_data['client-uid-cookie-name'] = (string) SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME;					// client browser UID Cookie Name (as defined in init.php) ; it may be required to pass this cookie name to the Javascript ...)
	$arr_data['app-realm'] 				= (string) $the_realm; 												// IDX (for index.php area) ; ADM (for admin.php area)
	$arr_data['app-domain'] 			= (string) Smart::get_from_config('app.'.$the_area.'-domain'); 		// the domain set in configs, that may differ by realm: $configs['app']['index-domain'] | $configs['app']['admin-domain']
	$arr_data['base-url'] 				= (string) SmartUtils::get_server_current_url(); 					// http(s)://crr-subdomain.crr-domain.ext/ | http(s)://crr-domain.ext/ | http(s)://127.0.0.1/sites/frameworks/smart-framework/
	$arr_data['base-path'] 				= (string) SmartUtils::get_server_current_path(); 					// / | /sites/frameworks/smart-framework/
	$arr_data['base-domain'] 			= (string) SmartUtils::get_server_current_basedomain_name(); 		// crr-domain.ext | IP
	$arr_data['srv-domain'] 			= (string) SmartUtils::get_server_current_domain_name(); 			// crr-subdomain.crr-domain.ext | crr-domain.ext | IP
	$arr_data['srv-proto'] 				= (string) SmartUtils::get_server_current_protocol(); 				// http:// | https://
	$arr_data['srv-port'] 				= (string) SmartUtils::get_server_current_port(); 					// 80 | 443 | ...
	$arr_data['srv-script'] 			= (string) SmartUtils::get_server_current_script(); 				// index.php | admin.php
	$arr_data['srv-urlquery'] 			= (string) SmartUtils::get_server_current_queryurl(); 				// ?page=some.page&ofs=...
	$arr_data['srv-requri'] 			= (string) SmartUtils::get_server_current_request_uri(); 			// page.html
	$arr_data['debug-mode'] 			= (string) SMART_FRAMEWORK_DEBUG_MODE; 								// yes | no
	//--

	//-- read TPL
	$tpl = (string) trim((string)SmartMarkersTemplating::read_template_file((string)$template_path.$template_file));
	if((string)$tpl == '') {
		Smart::raise_error(
			'#SMART-FRAMEWORK-RENDER-MAIN-TEMPLATE#'."\n".'The Template File is either: Empty / Does not Exists / Cannot be Read: '.$template_path.$template_file,
			'Main Template Render ERROR :: (See Error Log for More Details)'
		);
		die();
		return;
	} //end if
	//-- add debug support in TPL
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		if(class_exists('SmartDebugProfiler')) {
			if((stripos((string)$tpl, '</head>') !== false) AND (stripos((string)$tpl, '</body>') !== false)) {
				$tpl = (string) str_ireplace('</head>', "\n".SmartDebugProfiler::js_headers_debug(Smart::escape_url($the_area).'.php?smartframeworkservice=debug')."\n".'</head>', (string)$tpl);
				$tpl = (string) str_ireplace('</body>', "\n".SmartDebugProfiler::div_main_debug()."\n".'</body>', (string)$tpl);
			} //end if
		} //end if
	} //end if
	//--

	//-- render TPL
	return (string) SmartMarkersTemplating::render_mixed_template(
		(string) $tpl,				// tpl string
		(array)  $arr_data, 		// tpl vars
		(string) $template_path, 	// tpl base path (for sub-templates, if any)
		'no'						// ignore if empty
	);
	//--

} //END FUNCTION
//================================================================


//================================================================
/**
 * Get Browser Image and Description by BW-ID
 * This is compatible with BW-ID supplied by:
 * 		cli: SmartUtils::get_os_browser_ip()
 *
 * @access 		private
 * @internal
 *
 */
public static function get_imgdesc_by_bw_id($y_bw) {
	//--
	switch(strtolower((string)$y_bw)) { // {{{SYNC-CLI-BW-ID}}}
		case '@s#':
			$desc = 'Smart.Framework @Robot';
			$pict = 'browser/@smart-robot';
			break;
		case 'bot':
			$desc = 'Robot / Crawler';
			$pict = 'browser/bot';
			break;
		case 'lyx':
			$desc = 'Lynx Text Browser';
			$pict = 'browser/lyx';
			break;
		case 'moz':
			$desc = 'Mozilla / Seamonkey';
			$pict = 'browser/moz';
			break;
		case 'fox':
			$desc = 'Mozilla Firefox';
			$pict = 'browser/fox';
			break;
		case 'crm':
			$desc = 'Google Chrome / Chromium';
			$pict = 'browser/crm';
			break;
		case 'sfr':
			$desc = 'Apple Safari / Webkit';
			$pict = 'browser/sfr';
			break;
		case 'iee':
			$desc = 'Microsoft Edge';
			$pict = 'browser/iee';
			break;
		case 'iex':
			$desc = 'Microsoft Internet Explorer';
			$pict = 'browser/iex';
			break;
		case 'opr':
			$desc = 'Opera';
			$pict = 'browser/opr';
			break;
		case 'mid':
			$desc = 'Midori / Webkit';
			$pict = 'browser/mid';
			break;
		case 'knq':
			$desc = 'Konqueror';
			$pict = 'browser/knq';
			break;
		case 'eph':
			$desc = 'Epiphany';
			$pict = 'browser/eph';
			break;
		case 'gal':
			$desc = 'Galeon';
			$pict = 'browser/gal';
			break;
		case 'omw':
			$desc = 'OmniWeb';
			$pict = 'browser/omw';
			break;
		case 'mxt':
			$desc = 'Maxthon';
			$pict = 'browser/mxt';
			break;
		case 'nsf':
			$desc = 'NetSurf';
			$pict = 'browser/nsf';
			break;
		default:
			$desc = '[UNKNOWN]: ('.(string)$y_bw.')';
			$pict = 'browser/wkt';
	} //end switch
	//--
	return (array) [
		'img'  => (string) 'lib/core/img/'.$pict.'.svg',
		'desc' => (string) $desc.' :: Web Browser'
	];
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Get OS Image and Description by OS-ID
 * This is compatible with OS-ID supplied by:
 * 		srv: SmartUtils::get_server_os()
 * 		cli: SmartUtils::get_os_browser_ip()
 *
 * @access 		private
 * @internal
 *
 */
public static function get_imgdesc_by_os_id($y_os_id) {
	//--
	switch(strtolower((string)$y_os_id)) { // {{{SYNC-SRV-OS-ID}}} ; {{{SYNC-CLI-OS-ID}}}
		//-
		case 'macosx':
		case 'mac': // cli
			$desc = 'Apple MacOSX';
			$pict = 'os/mac_osx';
			break;
		//-
		case 'winnt':
		case 'win': // cli
			$desc = 'Microsoft Windows';
			$pict = 'os/windows';
			break;
		//-
		case 'bsd-os':
		case 'bsd': // cli
			$desc = 'BSD';
			$pict = 'os/bsd_generic';
			break;
		case 'netbsd':
			$desc = 'NetBSD';
			$pict = 'os/bsd_netbsd';
			break;
		case 'openbsd':
			$desc = 'OpenBSD';
			$pict = 'os/bsd_openbsd';
			break;
		case 'freebsd':
			$desc = 'FreeBSD';
			$pict = 'os/bsd_freebsd';
			break;
		case 'dragonfly':
			$desc = 'DragonFly-BSD';
			$pict = 'os/bsd_dragonfly';
			break;
		//-
		case 'linux':
		case 'lnx': // cli
			$desc = 'Linux';
			$pict = 'os/linux_generic';
			break;
		case 'debian':
			$desc = 'Debian Linux';
			$pict = 'os/linux_debian';
			break;
		case 'ubuntu':
			$desc = 'Ubuntu Linux';
			$pict = 'os/linux_ubuntu';
			break;
		case 'redhat':
			$desc = 'RedHat Linux';
			$pict = 'os/linux_redhat';
			break;
		case 'centos':
			$desc = 'CentOS Linux';
			$pict = 'os/linux_centos';
			break;
		case 'fedora':
			$desc = 'Fedora Linux';
			$pict = 'os/linux_fedora';
			break;
		case 'suse':
			$desc = 'SuSE Linux';
			$pict = 'os/linux_suse';
			break;
		case 'novell':
			$desc = 'Novell Linux';
			$pict = 'os/linux_novell';
			break;
		case 'slack':
			$desc = 'Slackware Linux';
			$pict = 'os/linux_slackware';
			break;
		case 'gentoo':
			$desc = 'Gentoo Linux';
			$pict = 'os/linux_gentoo';
			break;
		case 'knoppix':
			$desc = 'Knoppix Linux';
			$pict = 'os/linux_knoppix';
			break;
		case 'archlnx':
			$desc = 'Arch Linux';
			$pict = 'os/linux_arch';
			break;
		//-
		case 'ibm-aix':
		case 'aix': // cli
			$desc = 'IBM / AIX';
			$pict = 'os/unix_ibmaix';
			break;
		case 'hp-ux':
		case 'hpx': // cli
			$desc = 'HP-UX';
			$pict = 'os/unix_hpux';
			break;
		case 'opensolaris':
			$desc = 'Open Solaris';
			$pict = 'os/unix_opensolaris';
			break;
		case 'nexenta':
			$desc = 'Nexenta OS';
			$pict = 'os/unix_nexentasolaris';
			break;
		case 'solaris':
		case 'sun': // cli
			$desc = 'Oracle (Sun) Solaris';
			$pict = 'os/unix_solaris';
			break;
		case 'sgi-irix':
		case 'irx': // cli
			$desc = 'SGI Irix';
			$pict = 'os/unix_sgiirix';
			break;
		case 'sco-uxw':
		case 'sco': // cli
			$desc = 'SCO Unixware';
			$pict = 'os/unix_sco';
			break;
		//- cli only
		case 'ios':
			$desc = 'Apple iOS Mobile';
			$pict = 'os/mobile/ios';
			break;
		case 'and':
			$desc = 'Google Android Mobile';
			$pict = 'os/mobile/android';
			break;
		case 'wmo':
			$desc = 'Microsoft Windows Mobile';
			$pict = 'os/mobile/win_mobile';
			break;
		case 'lxm':
			$desc = 'Linux Mobile';
			$pict = 'os/mobile/linux_mobile';
			break;
		case 'bby':
			$desc = 'BlackBerry Mobile';
			$pict = 'os/mobile/blackberry';
			break;
		case 'pwo':
			$desc = 'Palm / WebOs Mobile';
			$pict = 'os/mobile/palm_webos';
			break;
		//-
		case '[?]':
		default:
			$desc = '[UNKNOWN]: ('.$y_os_id.')';
			$pict = 'os/linux_other';
		//-
	} //end switch
	//--
	return (array) [
		'img'  => (string) 'lib/core/img/'.$pict.'.png',
		'desc' => (string) $desc.' Operating System'
	];
	//--
} //END FUNCTION
//================================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>