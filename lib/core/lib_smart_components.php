<?php
// [LIB - Smart.Framework / Smart Components]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_APP_BOOTSTRAP')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime App Bootstrap Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart.Framework - Smart Components
// DEPENDS:
//	* Smart::
//	* SmartUtils::
//	* SmartFileSystem::
//	* SmartTextTranslations::
//	* SmartMarkersTemplating::
// REQUIRED CSS:
//	* notifications.css
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartComponents - provides various components for Smart.Framework
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartUtils, SmartFileSystem, SmartTextTranslations
 * @version 	v.20191117
 * @package 	Application:ViewComponents
 *
 */
final class SmartComponents {

	// ::
	// {{{SYNC-SMART-HTTP-STATUS-CODES}}}


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
		if($y_width < 250) {
			$y_width = 250;
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
				'CRR-URL' 	=> (string) SmartUtils::get_server_current_url()
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'400.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'401.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'403.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'404.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'429.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'500.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'502.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'503.php')) {
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
			if(SmartFileSystem::is_type_file(SMART_FRAMEWORK_CUSTOM_ERR_PAGES.'504.php')) {
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
	 */
	private static function notifications_template($y_html, $y_idcss, $y_width) {
		//--
		$y_width = (string) self::fix_css_elem_dim($y_width);
		//--
		return '<!-- require: notifications.css --><div id="'.Smart::escape_html($y_idcss).'" style="width:'.Smart::escape_html($y_width).';">'.$y_html.'</div>';
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Function: Format CSS Dimension for Elements
	 * If no unit is specified then assume px (pixels)
	 * If number is < 0, will assume 1 to avoid hide element
	 * Allowed Units: %, vw, vh, pt, pc, px
	 * Returns the CSS safe formated dimension
	 */
	private static function fix_css_elem_dim($css_w_or_h) {
		//--
		$css_w_or_h = Smart::normalize_spaces((string)$css_w_or_h); // $css_w_or_h = str_replace([' ', "\t", "\n", "\r"], '', (string)$css_w_or_h);
		$css_w_or_h = (string) trim((string)$css_w_or_h);
		//--
		$css_w_or_h = (array) explode(';', (string)$css_w_or_h);
		$css_w_or_h = (string) trim((string)$css_w_or_h[0]);
		$matches = array();
		preg_match('/^([0-9]+)(%|[a-z]{1,2})?$/', (string)$css_w_or_h, $matches);
		$css_unit = 'px';
		$css_num = (int) $matches[1];
		if($css_num <= 0) {
			$css_num = 1;
		} //end if
		$css_w_or_h = '';
		switch((string)$matches[2]) {
			case '%':
			case 'vw':
			case 'vh':
				$css_unit = (string) $matches[2];
				if($css_num > 100) {
					$css_num = 100;
				} //end if
				break;
			case 'pt':
			case 'pc':
			case 'px':
				$css_unit = (string) $matches[2];
				break;
			default:
				$css_unit = 'px';
		} //end switch
		if($css_num > 3200) {
			$css_num = 3200; // avoid too large values
		} //end if
		$css_w_or_h = (string) $css_num.$css_unit;
		//--
		return (string) $css_w_or_h;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Return the Highlight type for a file type Highlight.Js
	 *
	 * @access 		private
	 * @internal
	 *
	 * @param STRING 	$path				The file path or file name (includding file extension)
	 *
	 * @return ARRAY						[ type, pack ]
	 */
	public static function get_filetype_highlightsyntax($path) {
		//--
		$path = (string) $path;
		//--
		$fname = (string) SmartFileSysUtils::get_file_name_from_path((string)$path);
		$fext = (string) SmartFileSysUtils::get_file_extension_from_path((string)$fname);
		$fext = (string) strtolower((string)trim((string)$fext));
		//--
		$fpack = '';
		$ftype = '';
		switch((string)$fext) { // {{{SYNC-HIGHLIGHT-FTYPE-PACK}}}
			//-- web
			case 'css':
				$fpack = 'web';
				$ftype = 'css';
				break;
			case 'diff':
			case 'patch':
				$fpack = 'web';
				$ftype = 'diff';
				break;
			case 'ini':
			case 'toml': // rust cargo def
				$fpack = 'web';
				$ftype = 'ini';
				break;
			case 'js':
			case 'gjs':
				$fpack = 'web';
				$ftype = 'javascript';
				break;
			case 'json':
				$fpack = 'web';
				$ftype = 'json';
				break;
			case 'less':
				$fpack = 'web';
				$ftype = 'less';
				break;
			case 'md':
			case 'markdown':
				$fpack = 'web';
				$ftype = 'markdown';
				break;
			case 'pgsql':
				$fpack = 'web';
				$ftype = 'pgsql';
				break;
			case 'php':
			case 'php3':
			case 'php4':
			case 'php5':
			case 'php6': // n/a
			case 'php7':
			case 'hh': // hip hop, a kind of static PHP
				$fpack = 'web';
				$ftype = 'php';
				break;
			case 'scss':
				$fpack = 'web';
				$ftype = 'scss';
				break;
			case 'sql':
				$fpack = 'web';
				$ftype = 'sql';
				break;
			case 'yaml':
			case 'yml':
				$fpack = 'web';
				$ftype = 'yaml';
				break;
				break;
			case 'xml':
			case 'svg':
			case 'html':
				$fpack = 'web';
				$ftype = 'xml';
				break;
			//-- tpl (depends on web)
			case 'htm':
				$fpack = 'web,tpl';
				$ftype = 'markertpl';
				break;
			//-- lnx
			case 'awk':
				$fpack = 'lnx';
				$ftype = 'awk';
				break;
			case 'pl':
			case 'pm':
				$fpack = 'lnx';
				$ftype = 'perl';
				break;
			case 'bash':
				$fpack = 'lnx';
				$ftype = 'bash';
				break;
			case 'sh':
				$fpack = 'lnx';
				$ftype = 'shell';
				break;
			//-- srv
			case 'dns':
				$fpack = 'srv';
				$ftype = 'dns';
				break;
			//-- net
			case 'csp':
				$fpack = 'net';
				$ftype = 'csp';
				break;
			case 'httph':
				$fpack = 'net';
				$ftype = 'http';
				break;
			//-- lang
			case 'coffee':
			case 'cson':
				$fpack = 'lang';
				$ftype = 'coffeescript';
				break;
			case 'c':
			case 'h':
			case 'cpp':
			case 'hpp':
			case 'cxx':
			case 'hxx':
				$fpack = 'lang';
				$ftype = 'cpp';
				break;
			case 'go':
				$fpack = 'lang';
				$ftype = 'go';
				break;
			case 'lua':
				$fpack = 'lang';
				$ftype = 'lua';
				break;
			case 'py':
				$fpack = 'lang';
				$ftype = 'python';
				break;
			case 'rb':
				$fpack = 'lang';
				$ftype = 'ruby';
				break;
			case 'rs':
				$fpack = 'lang';
				$ftype = 'rust';
				break;
			case 'tcl':
			case 'tk':
				$fpack = 'lang';
				$ftype = 'tcl';
				break;
			case 'vala':
			case 'vapi':
				$fpack = 'lang';
				$ftype = 'vala';
				break;
			//--
			default:
				// no handler
		} //end switch
		//--
		if((string)strtolower((string)$fname) == 'cmake') {
			$fpack = 'lang';
			$ftype = 'cmake';
		} elseif((string)strtolower((string)$fname) == 'makefile') {
			$fpack = 'lang';
			$ftype = 'makefile';
		} elseif((string)$fname == 'pf.conf') {
			$fpack = 'srv';
			$ftype = 'pf';
		} elseif((in_array((string)$ftype, ['xml', 'html', 'md', 'json'])) OR ((string)$fext == 'txt')) {
			if((stripos((string)$fname, '.mtpl.') !== false) OR (stripos((string)$fname, '.inc.') !== false)) {
				$fpack = 'web,tpl';
				$ftype = 'markertpl';
			} //end if
		} //end if
		//--
		return array(
			'type' => (string) $ftype,
			'pack' => (array)  explode(',', (string)$fpack)
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
			case 'atom-one-light':
			case 'github-gist':
			case 'github':
			case 'googlecode':
			case 'grayscale':
			case 'ocean':
			case 'tomorrow-night-blue':
			case 'xcode':
			case 'zenburn':
				$theme = (string) $theme;
				break;
			case 'default':
			default:
				$theme = 'default';
		} //end switch
		//--
		$arr_packs = [ // {{{SYNC-HIGHLIGHT-FTYPE-PACK}}}
			'web'  => 'css, diff, ini, javascript, json, less, markdown, php, scss, sql, pgsql, xml, yaml',
			'tpl'  => 'markertpl',
			'lnx'  => 'awk, bash, perl, shell',
			'srv'  => 'accesslog, apache, dns, nginx, pf',
			'net'  => 'csp, http',
			'lang' => 'cmake, coffeescript, cpp, go, lua, makefile, python, ruby, rust, tcl, vala'
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
	 * Function: Draw App Powered Info
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function app_powered_info($y_show_versions='no', $y_plugins=array()) {
		//--
		global $configs;
		//--
		$base_url = (string) SmartUtils::get_server_current_url();
		//-- framework
		$software_name = 'Smart.Framework, a PHP / Javascript Web Framework';
		if((string)$y_show_versions == 'yes') { // expose versions (not recommended in web area, except for auth admins)
			$software_name .= ' :: '.SMART_FRAMEWORK_RELEASE_TAGVERSION.'-'.SMART_FRAMEWORK_RELEASE_VERSION.' @ '.SMART_SOFTWARE_APP_NAME;
		} //end if
		$software_logo = $base_url.'lib/framework/img/sf-logo.svg';
		$software_url = (string) SMART_FRAMEWORK_RELEASE_URL;
		//--
		$arr_powered_sside = [];
		//-- os
		$arr_os = (array) self::get_imgdesc_by_os_id((string)SmartUtils::get_server_os());
		$os_pict = (string) $arr_os['img'];
		$os_desc = (string) $arr_os['desc'];
		$arr_powered_sside[] = [
			'name' 	=> (string) $os_desc,
			'logo' 	=> (string) $base_url.$os_pict,
			'url' 	=> (string) ''
		];
		//-- web server
		$tmp_arr_web_server = SmartUtils::get_webserver_version();
		$name_webserver = (string) $tmp_arr_web_server['name'].' Web Server';
		if((string)$y_show_versions == 'yes') { // expose versions (not recommended in web area, except for auth admins)
			$name_webserver .= ' :: '.$tmp_arr_web_server['version'];
		} //end if
		if(stripos((string)$name_webserver, 'apache') !== false) {
			$logo_webserver = 'lib/framework/img/apache-logo.svg';
			$url_webserver = 'https://httpd.apache.org';
		} elseif(stripos((string)$name_webserver, 'nginx') !== false) {
			$logo_webserver = 'lib/framework/img/nginx-logo.svg';
			$url_webserver = 'https://www.nginx.com';
		} else {
			$logo_webserver = 'lib/framework/img/sign-info.svg';
			$url_webserver = '';
		} //end if else
		$arr_powered_sside[] = [
			'name' 	=> (string) $name_webserver,
			'logo' 	=> (string) $base_url.$logo_webserver,
			'url' 	=> (string) $url_webserver
		];
		//-- php
		$php_name = 'PHP Server-Side Scripting Language';
		if((string)$y_show_versions == 'yes') { // expose versions (not recommended in web area, except for auth admins)
			$php_name .= ' :: '.PHP_VERSION;
		} //end if
		$arr_powered_sside[] = [
			'name' 	=> (string) $php_name,
			'logo' 	=> (string) $base_url.'lib/framework/img/php-logo.svg',
			'url' 	=> (string) 'http://www.php.net'
		];
		//-- sqlite
		if(is_array($configs['sqlite'])) {
			$arr_powered_sside[] = [
				'name' 	=> (string) 'SQLite Embedded Database',
				'logo' 	=> (string) $base_url.'lib/core/img/db/sqlite-logo.svg',
				'url' 	=> (string) 'https://www.sqlite.org'
			];
		} //end if
		//-- redis
		if(is_array($configs['redis'])) {
			$arr_powered_sside[] = [
				'name' 	=> (string) 'Redis In-Memory Distributed Key-Value Store (Caching Data Store)',
				'logo' 	=> (string) $base_url.'lib/core/img/db/redis-logo.svg',
				'url' 	=> (string) 'https://redis.io'
			];
		} //end if
		//-- mongodb
		if(is_array($configs['mongodb'])) {
			$arr_powered_sside[] = [
				'name' 	=> (string) 'MongoDB BigData Server',
				'logo' 	=> (string) $base_url.'lib/core/img/db/mongodb-logo.svg',
				'url' 	=> (string) 'https://docs.mongodb.com'
			];
		} //end if
		//-- pgsql
		if(is_array($configs['pgsql'])) {
			$arr_powered_sside[] = [
				'name' 	=> (string) 'PostgreSQL Database Server',
				'logo' 	=> (string) $base_url.'lib/core/img/db/postgresql-logo.svg',
				'url' 	=> (string) 'https://www.postgresql.org'
			];
		} //end if
		//-- mysqli
		if(is_array($configs['mysqli'])) {
			$tmp_name = (string) trim((string)$configs['mysqli']['type']);
			$arr_powered_sside[] = [
				'name' 	=> (string) ucfirst((string)$tmp_name).' Database Server',
				'logo' 	=> (string) $base_url.'lib/core/img/db/mysql-logo.svg',
				'url' 	=> (string) 'https://mariadb.org'
			];
		} //end if
		//--
		$arr_powered_cside = [];
		//-- html
		$arr_powered_cside[] = [
			'name' 	=> (string) 'HTML Markup Language for World Wide Web',
			'logo' 	=> (string) $base_url.'lib/framework/img/html-logo.svg',
			'url' 	=> (string) 'https://www.w3.org/TR/html/'
		];
		//-- css
		$arr_powered_cside[] = [
			'name' 	=> (string) 'CSS Style Sheet Language for World Wide Web',
			'logo' 	=> (string) $base_url.'lib/framework/img/css-logo.svg',
			'url' 	=> (string) 'https://www.w3.org/TR/CSS/'
		];
		//-- javascript
		$arr_powered_cside[] = [
			'name' 	=> (string) 'Javascript Client-Side Scripting Language for World Wide Web',
			'logo' 	=> (string) $base_url.'lib/framework/img/javascript-logo.svg',
			'url' 	=> (string) 'https://developer.mozilla.org/en-US/docs/Web/JavaScript'
		];
		//-- jquery
		$arr_powered_cside[] = [
			'name' 	=> (string) 'jQuery Javascript Library',
			'logo' 	=> (string) $base_url.'lib/framework/img/jquery-logo.svg',
			'url' 	=> (string) 'https://jquery.com'
		];
		//--
		if(Smart::array_size($y_plugins) > 0) {
			for($i=0; $i<Smart::array_size($y_plugins); $i++) {
				$tmp_arr = [];
				if(is_array($y_plugins[$i])) {
					if(((string)$y_plugins[$i]['name'] != '') AND ((string)$y_plugins[$i]['logo'] != '')) {
						$tmp_arr = [
							'name' 	=> (string) $y_plugins[$i]['name'],
							'logo' 	=> (string) $y_plugins[$i]['logo'],
							'url' 	=> (string) $y_plugins[$i]['url']
						];
						if((string)$y_plugins[$i]['type'] == 'sside') {
							$arr_powered_sside[] = (array) $tmp_arr;
						} elseif((string)$y_plugins[$i]['type'] == 'cside') {
							$arr_powered_cside[] = (array) $tmp_arr;
						} //end if else
					} else {
						$arr_powered_cside[] = [
							'name' 	=> '',
							'logo' 	=> '',
							'url' 	=> ''
						];
					} //end if
				} //end if
			} //end for
		} //end if
		//--
		return (string) SmartMarkersTemplating::render_file_template(
			'lib/core/templates/app-powered-info.inc.htm',
			[
				'APP-NAME' 	=> (string) $software_name,
				'APP-LOGO' 	=> (string) $software_logo,
				'APP-URL' 	=> (string) $software_url,
				'ARR-SSIDE' => (array) $arr_powered_sside,
				'ARR-CSIDE' => (array) $arr_powered_cside
			]
		);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	// This conform the var names to lowercase and set the meta vars into a template array context (by default this is used by ::render_app_template() but can be used outside if needed ...
	public static function set_app_template_conform_metavars($arr_data=[]) {
		//--
		if(!is_array($arr_data)) {
			return array();
		} //end if
		//--
		if(SMART_FRAMEWORK_ADMIN_AREA === true) {
			$the_area = 'admin';
			$the_realm = 'ADM';
		} else {
			$the_area = 'index';
			$the_realm = 'IDX';
		} //end if else
		$os_bw = (array) SmartUtils::get_os_browser_ip();
		//--
		$arr_data = (array) array_change_key_case((array)$arr_data, CASE_LOWER); // make all keys lower (only 1st level, not nested), to comply with SmartAbstractAppController handling mode
		//--
		$netport = (string) SmartUtils::get_server_current_port();
		$srvport = (string) ((($netport == 80) || ($netport == 443)) ? '' : ':'.$netport);
		$srvproto = (string) SmartUtils::get_server_current_protocol();
		//--
		$arr_data['release-hash'] 				= (string) SmartFrameworkRuntime::getAppReleaseHash(); // the release hash based on app framework version, framework release and modules version
		$arr_data['lang'] 						= (string) SmartTextTranslations::getLanguage(); 					// current language (ex: en)
		$arr_data['charset'] 					= (string) SMART_FRAMEWORK_CHARSET;									// current charset (ex: UTF-8)
		$arr_data['timezone'] 					= (string) SMART_FRAMEWORK_TIMEZONE; 								// current timezone (ex: UTC)
		$arr_data['client-ip'] 					= (string) $os_bw['ip']; 											// client browser IP (ex: 127.0.0.1)
		$arr_data['client-os'] 					= (string) $os_bw['os']; 											// client browser OS (ex: bsd)
		$arr_data['client-is-mobile'] 			= (string) $os_bw['mobile']; 										// client browser is Mobile (yes/no)
		$arr_data['client-class'] 				= (string) $os_bw['bc']; 											// client browser Class (ex: gk)
		$arr_data['client-browser'] 			= (string) $os_bw['bw']; 											// client browser (ex: fox)
		$arr_data['client-uid-cookie-name'] 	= (string) SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME;					// client browser UID Cookie Name (as defined in etc/init.php) ; it may be required to pass this cookie name to the Javascript ...)
		$arr_data['client-uid-cookie-domain'] 	= (string) SmartUtils::cookie_default_domain(); 					// client browser UID Cookie Domain (as defined in etc/init.php) ; it may be required to pass this cookie domain to the Javascript ...)
		$arr_data['app-env'] 					= (string) (SMART_ERROR_HANDLER === 'log') ? 'prod' : 'dev'; 		// App Environment: dev | prod :: {{{SYNC-APP-ENV-SETT}}}
		$arr_data['app-namespace'] 				= (string) SMART_SOFTWARE_NAMESPACE;								// NameSpace from configs (as defined in etc/init.php)
		$arr_data['app-realm'] 					= (string) $the_realm; 												// IDX (for index.php area) ; ADM (for admin.php area)
		$arr_data['app-domain'] 				= (string) Smart::get_from_config('app.'.$the_area.'-domain'); 		// the domain set in configs, that may differ by area: $configs['app']['index-domain'] | $configs['app']['admin-domain']
		$arr_data['base-url'] 					= (string) SmartUtils::get_server_current_url(); 					// http(s)://crr-subdomain.crr-domain.ext/ | http(s)://crr-domain.ext/ | http(s)://127.0.0.1/sites/frameworks/smart-framework/
		$arr_data['base-path'] 					= (string) SmartUtils::get_server_current_path(); 					// / | /sites/frameworks/smart-framework/
		$arr_data['base-domain'] 				= (string) SmartUtils::get_server_current_basedomain_name(); 		// crr-domain.ext | IP (ex: 127.0.0.1)
		$arr_data['srv-domain'] 				= (string) SmartUtils::get_server_current_domain_name(); 			// crr-subdomain.crr-domain.ext | crr-domain.ext | IP
		$arr_data['srv-ip-addr'] 				= (string) SmartUtils::get_server_current_ip(); 					// current server IP (ex: 127.0.0.1)
		$arr_data['srv-proto'] 					= (string) $srvproto; 												// http:// | https://
		$arr_data['net-proto'] 					= (string) ((string)$srvproto == 'https://') ? 'https' : 'http'; 	// http | https
		$arr_data['srv-port'] 					= (string) $srvport; 												// '' | ''  | ':8080' ... (the current server port address ; empty for port 80 and 443 ; for the rest of ports will be :portnumber)
		$arr_data['net-port'] 					= (string) $netport; 												// 80 | 443 | 8080 ... (the current server port)
		$arr_data['srv-script'] 				= (string) SmartUtils::get_server_current_script(); 				// index.php | admin.php
		$arr_data['srv-urlquery'] 				= (string) SmartUtils::get_server_current_queryurl(); 				// ?page=some.page&ofs=...
		$arr_data['srv-requri'] 				= (string) SmartUtils::get_server_current_request_uri(); 			// page.html
		$arr_data['timeout-execution'] 			= (int)    SMART_FRAMEWORK_EXECUTION_TIMEOUT; 						// execution timeout
		$arr_data['timeout-netsocket'] 			= (int)    SMART_FRAMEWORK_NETSOCKET_TIMEOUT; 						// netsocket timeout
		$arr_data['time-date-start'] 			= (string) date('Y-m-d H:i:s O'); 									// date time start
		$arr_data['auth-login-ok'] 				= (string) (SmartAuth::check_login() === true ? 'yes' : 'no'); 		// Auth Login OK: yes/no
		$arr_data['auth-login-id'] 				= (string) SmartAuth::get_login_id(); 								// Auth Login ID
		$arr_data['auth-login-alias'] 			= (string) SmartAuth::get_login_alias(); 							// Auth Login Alias (UserName)
		$arr_data['auth-login-fullname'] 		= (string) SmartAuth::get_login_fullname(); 						// Auth Login FullName
		$arr_data['auth-login-privileges'] 		= (string) SmartAuth::get_login_privileges(); 						// Auth Login Privileges
		$arr_data['debug-mode'] 				= (string) (SmartFrameworkRuntime::ifDebug() ? 'yes' : 'no'); 		// yes | no
		//--
		return (array) $arr_data;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	// This renders the App Main Template (should be used only on custom developments ...)
	public static function render_app_template($template_path, $template_file, $arr_data) { // {{{SYNC-ARRAY-MAKE-KEYS-LOWER}}}

		//--
		$template_path = (string) Smart::safe_pathname((string)SmartFileSysUtils::add_dir_last_slash((string)trim((string)$template_path)));
		if(!SmartFileSysUtils::check_if_safe_path($template_path)) {
			Smart::raise_error(
				'#SMART-FRAMEWORK-RENDER-APP-TEMPLATE#'."\n".'The Template Dir Path is Invalid: '.$template_path,
				'App Template Render ERROR :: (See Error Log for More Details)'
			);
			die();
			return;
		} //end if
		//--
		$template_file = (string) Smart::safe_filename((string)trim((string)$template_file));
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name($template_file)) {
			Smart::raise_error(
				'#SMART-FRAMEWORK-RENDER-APP-TEMPLATE#'."\n".'The Template File Name is Invalid: '.$template_file,
				'App Template Render ERROR :: (See Error Log for More Details)'
			);
			die();
			return;
		} //end if
		//--
		if(!SmartFileSysUtils::check_if_safe_path($template_path.$template_file)) {
			Smart::raise_error(
				'#SMART-FRAMEWORK-RENDER-APP-TEMPLATE#'."\n".'The Template File Path is Invalid: '.$template_path.$template_file,
				'App Template Render ERROR :: (See Error Log for More Details)'
			);
			die();
			return;
		} //end if
		//--

		//-- add meta vars and conform all keys to lowercase
		$arr_data = (array) self::set_app_template_conform_metavars($arr_data);
		//-- special TPL vars
		$arr_data['template-path'] 				= (string) $template_path; // current template path (ex: etc/templates/default/)
		$arr_data['template-file'] 				= (string) $template_file; // current template file (ex: template.htm | template-modal.htm | ...)
		//-- external TPL vars
		$arr_data['semaphore'] 					= (string) $arr_data['semaphore']; // a general purpose conditional var
		$arr_data['title'] 						= (string) $arr_data['title'];
		$arr_data['head-meta'] 					= (string) $arr_data['head-meta'];
		$arr_data['head-css'] 					= (string) $arr_data['head-css'];
		$arr_data['head-js'] 					= (string) $arr_data['head-js'];
		$arr_data['header'] 					= (string) $arr_data['header'];
		$arr_data['main'] 						= (string) $arr_data['main'];
		$arr_data['aside'] 						= (string) $arr_data['aside'];
		$arr_data['footer'] 					= (string) $arr_data['footer'];
		//--

		//-- read TPL
		$tpl = (string) trim((string)SmartMarkersTemplating::read_template_file((string)$template_path.$template_file));
		if((string)$tpl == '') {
			Smart::raise_error(
				'#SMART-FRAMEWORK-RENDER-APP-TEMPLATE#'."\n".'The Template File is either: Empty / Does not Exists / Cannot be Read: '.$template_path.$template_file,
				'App Template Render ERROR :: (See Error Log for More Details)'
			);
			die();
			return;
		} //end if
		//-- add debug support in TPL
		if(SmartFrameworkRuntime::ifDebug()) {
			if(class_exists('SmartDebugProfiler')) {
				if((stripos((string)$tpl, '</head>') !== false) AND (stripos((string)$tpl, '</body>') !== false)) {
					$tpl = (string) str_ireplace('</head>', "\n".SmartDebugProfiler::js_headers_debug(Smart::escape_url((SMART_FRAMEWORK_ADMIN_AREA === true ? 'admin' : 'index')).'.php?smartframeworkservice=debug')."\n".'</head>', (string)$tpl);
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
			case 'fox':
				$desc = 'Mozilla Firefox';
				$pict = 'browser/fox';
				break;
			case 'smk':
				$desc = 'Mozilla Seamonkey';
				$pict = 'browser/smk';
				break;
			case 'moz':
				$desc = 'Mozilla (Derivate)';
				$pict = 'browser/moz';
				break;
			case 'crm':
				$desc = 'Google Chromium / Chrome';
				$pict = 'browser/crm';
				break;
			case 'sfr':
				$desc = 'Apple Safari / Webkit';
				$pict = 'browser/sfr';
				break;
			case 'wkt':
				$desc = 'Webkit (Derivate)';
				$pict = 'browser/wkt';
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
			case 'eph':
				$desc = 'Epiphany';
				$pict = 'browser/eph';
				break;
			case 'knq':
				$desc = 'Konqueror';
				$pict = 'browser/knq';
				break;
			case 'nsf':
				$desc = 'NetSurf';
				$pict = 'browser/nsf';
				break;
			default:
				$desc = '[Other]: ('.(string)$y_bw.')';
				$pict = 'browser/xxx';
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
			case 'macos':
			case 'mac': // cli
				$desc = 'Apple MacOS';
				$pict = 'os/mac-os';
				break;
			//-
			case 'windows':
			case 'winnt':
			case 'win': // cli
				$desc = 'Microsoft Windows';
				$pict = 'os/windows-os';
				break;
			//-
			case 'openbsd':
				$desc = 'OpenBSD';
				$pict = 'os/bsd-openbsd';
				break;
			case 'netbsd':
				$desc = 'NetBSD';
				$pict = 'os/bsd-netbsd';
				break;
			case 'freebsd':
				$desc = 'FreeBSD';
				$pict = 'os/bsd-freebsd';
				break;
			case 'dragonfly':
				$desc = 'DragonFly-BSD';
				$pict = 'os/bsd-dragonfly';
				break;
			case 'bsd-os':
			case 'bsd': // cli
				$desc = 'BSD';
				$pict = 'os/bsd-generic';
				break;
			//-
			case 'linux':
			case 'lnx': // cli
				$desc = 'Linux';
				$pict = 'os/linux-generic';
				break;
			case 'debian':
				$desc = 'Debian Linux';
				$pict = 'os/linux-debian';
				break;
			case 'ubuntu':
				$desc = 'Ubuntu Linux';
				$pict = 'os/linux-ubuntu';
				break;
			case 'mint':
				$desc = 'Mint Linux';
				$pict = 'os/linux-mint';
				break;
			case 'redhat':
				$desc = 'RedHat Linux';
				$pict = 'os/linux-redhat';
				break;
			case 'centos':
				$desc = 'CentOS Linux';
				$pict = 'os/linux-centos';
				break;
			case 'fedora':
				$desc = 'Fedora Linux';
				$pict = 'os/linux-fedora';
				break;
			case 'suse':
				$desc = 'SuSE Linux';
				$pict = 'os/linux-suse';
				break;
			//-
			case 'solaris':
			case 'sun': // cli
				$desc = '(Open) Solaris';
				$pict = 'os/unix-solaris';
				break;
			//- cli only
			case 'ios':
				$desc = 'Apple iOS Mobile';
				$pict = 'os/mobile/ios';
				break;
			case 'android':
			case 'and':
				$desc = 'Google Android Mobile';
				$pict = 'os/mobile/android';
				break;
			case 'wmo':
				$desc = 'Microsoft Windows Mobile';
				$pict = 'os/mobile/windows-mobile';
				break;
			case 'lxm':
				$desc = 'Linux Mobile';
				$pict = 'os/mobile/linux-mobile';
				break;
			//-
			case '[?]':
			default:
				$desc = '[UNKNOWN]: ('.$y_os_id.')';
				$pict = 'os/other-os';
			//-
		} //end switch
		//--
		return (array) [
			'img'  => (string) 'lib/core/img/'.$pict.'.svg',
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