<?php
// SmartFramework / Middleware / Admin
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.5.3 r.2016.08.23 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
//####################


define('SMART_FRAMEWORK_RELEASE_MIDDLEWARE', '[A]@v.2.3.5.3');


//==================================================================================
//================================================================================== CLASS START
//==================================================================================

// [REGEX-SAFE-OK]

/**
 * Class: Middleware Admin Service Handler
 *
 * @access 		private
 * @internal
 *
 * @version		160920
 *
 */
final class SmartAppAdminMiddleware extends SmartAbstractAppMiddleware {

	// ::

	private static $MiddlewareCompleted = false;


//====================================================================
public static function Run() {
	//--
	global $configs;
	//--
	//==
	//--
	if(self::$MiddlewareCompleted !== false) { // avoid to execute more than 1 this middleware !
		self::Raise500Error('Middleware App Execution already completed ...');
		return;
	} //end if
	self::$MiddlewareCompleted = true;
	//--
	$the_midmark = '[A]';
	//--
	if(SMART_FRAMEWORK_ADMIN_AREA !== true) {
		Smart::raise_error(
			'Admin Middleware ERROR: SMART_FRAMEWORK_ADMIN_AREA is not set to TRUE',
			'Invalid Area / This middleware is designed for Admin area and requires to turn ON the Administration flag ...' // msg to display
		);
		return;
	} //end if
	//--
	if(!defined('SMART_APP_TEMPLATES_DIR')) {
		self::Raise500Error('The SMART_APP_TEMPLATES_DIR not defined ...');
		return;
	} //end if
	//--
	if(defined('SMART_APP_MODULE_AREA')) {
		self::Raise500Error('Smart App Area must NOT be Defined outside controllers ...');
		return;
	} //end if
	if(defined('SMART_APP_MODULE_AUTH')) {
		self::Raise500Error('Smart App Module Auth must NOT be Defined outside controllers ...');
		return;
	} //end if
	if(defined('SMART_APP_MODULE_REALM_AUTH')) {
		self::Raise500Error('Smart App Module Realm Auth must NOT be Defined outside controllers ...');
		return;
	} //end if
	if(defined('SMART_APP_MODULE_DIRECT_OUTPUT')) {
		self::Raise500Error('Smart App Module Direct Output must NOT be Defined outside controllers ...');
		return;
	} //end if
	//--
	//==
	//--
	$smartframeworkservice = ''; // special operation
	if(SmartFrameworkRegistry::issetRequestVar('smartframeworkservice') === true) {
		$smartframeworkservice = (string) strtolower((string)SmartUnicode::utf8_to_iso((string)SmartFrameworkRegistry::getRequestVar('smartframeworkservice')));
		switch((string)$smartframeworkservice) {
			case 'status':
			case 'debug':
				break;
			default: // invalid value
				$smartframeworkservice = '';
		} //end switch
	} //end if
	//--
	//==
	//-- switch language by cookie (this needs to be before loading the app core)
	if(strlen(trim((string)$_COOKIE['SmartApp_ADM_LANGUAGE_SET'])) > 0) {
		SmartTextTranslations::setLanguage(trim((string)$_COOKIE['SmartApp_ADM_LANGUAGE_SET']));
	} //end if
	//-- switch language by print cookie (this needs to be before loading the app core and after language by cookie)
	if(SmartFrameworkRegistry::issetRequestVar((string)SMART_FRAMEWORK_URL_PARAM_PRINTABLE) === true) {
		if(strtolower((string)SmartFrameworkRegistry::getRequestVar((string)SMART_FRAMEWORK_URL_PARAM_PRINTABLE)) == strtolower((string)SMART_FRAMEWORK_URL_VALUE_ENABLED)) {
			if(strlen(trim((string)$_COOKIE['SmartApp_ADM_PRINT_LANGUAGE_SET'])) > 0) {
				SmartTextTranslations::setLanguage(trim((string)$_COOKIE['SmartApp_ADM_PRINT_LANGUAGE_SET']));
			} //end if
		} //end if
	} //end if
	//--
	//== RAW OUTPUT FOR STATUS
	//--
	if((string)$smartframeworkservice == 'status') {
		//--
		if(SMART_SOFTWARE_DISABLE_STATUS_POWERED === true) {
			$status_powered_info = '';
		} else {
			$status_powered_info = (string) SmartComponents::draw_powered_info('no');
		} //end if else
		//--
		self::HeadersNoCache(); // headers: cache control, force no-cache
		echo SmartComponents::http_status_message('Smart.Framework :: Status :: [OK]', '<script type="text/javascript">setTimeout(function(){ self.location = self.location; }, 60000);</script><img src="lib/core/img/busy_bar.gif"><div><h1>'.date('Y-m-d H:i:s O').' // Service Ready :: '.$the_midmark.'</h1></div>'.$status_powered_info.'<br>');
		//--
		return; // break stop
		//--
	} //end if
	//--
	//== OVERALL AUTHENTICATION BREAKPOINT
	//--
	SmartAppBootstrap::Authenticate('admin'); // if the auth uses session it may start now
	//--
	//== RAW OUTPUT FOR DEBUG
	//--
	if((string)$smartframeworkservice == 'debug') {
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			self::HeadersNoCache(); // headers: cache control, force no-cache
			$the_debug_cookie = trim((string)$_COOKIE['SmartFramework__DebugAdmID']);
			echo SmartDebugProfiler::print_debug_info('adm', $the_debug_cookie);
		} else {
			http_response_code(404);
			echo SmartComponents::http_message_404_notfound('No Debug service has been activated on this server ...');
		} //end if
		//--
		return; // break stop
		//--
	} //end if else
	//--
	//== LOAD THE MODULE (OR DEFAULT MODULE)
	//--
	$err404 = '';
	$arr = array();
	//--
	$page = (string) SmartUnicode::utf8_to_iso((string)SmartFrameworkRegistry::getRequestVar('page'));
	$page = trim(str_replace(array('/', '\\', ':', '?', '&', '=', '%'), array('', '', '', '', '', '', ''), $page)); // fix for get as it automatically replaces . with _ (so, reverse), but also fix some invalid characters ...
	if((string)$page == '') {
		$page = (string) $configs['app']['admin-home'];
	} //end if
	//--
	if(strpos($page, '.') !== false) { // page can be as module.controller / module.controller.(html|stml|json) / module.controller.some-indexing-words-for-spiders.(html|stml|json)
		//--
		$arr = (array) explode('.', (string)$page, 3); // separe 1st and 2nd from the rest
		$arr[0] = trim(strtolower((string)$arr[0])); // module
		$arr[1] = trim(strtolower((string)$arr[1])); // controller
		//--
	} elseif((string)$configs['app']['admin-default-module'] != '') {
		//--
		$arr[0] = trim(strtolower((string)$configs['app']['admin-default-module'])); // get default module
		$arr[1] = trim(strtolower((string)$page)); // controller
		//--
	} else {
		//--
		if((string)$err404 == '') {
			$err404 = 'Invalid Page (Invalid URL Page Segments Syntax): '.$page;
		} //end if
		//--
	} //end if else
	//--
	if(((string)$arr[0] == '') OR ((string)$arr[1] == '')) {
		if((string)$err404 == '') {
			$err404 = 'Invalid Page (Empty or Missing URL Page Segments): '.$page;
		} //end if
	} //end if
	if((!preg_match('/^[a-z0-9_\-]+$/', (string)$arr[0])) OR (!preg_match('/^[a-z0-9_\-]+$/', (string)$arr[1]))) {
		if((string)$err404 == '') {
			$err404 = 'Invalid Page (Invalid Characters in the URL Page Segments): '.$page;
		} //end if
	} //end if
	//--
	$the_controller_name = (string) $arr[0].'.'.$arr[1];
	$the_path_to_module = Smart::safe_pathname(SmartFileSysUtils::add_dir_last_slash('modules/mod-'.Smart::safe_filename($arr[0])));
	$the_module = Smart::safe_pathname($the_path_to_module.Smart::safe_filename($arr[1]).'.php');
	if(!is_file($the_module)) {
		if((string)$err404 == '') {
			$err404 = 'Page does not exist: '.$page;
		} //end if
	} //end if
	//--
	if((string)$err404 != '') {
		self::Raise404Error((string)$err404);
		return;
	} //end if
	//--
	if((!SmartFileSysUtils::check_file_or_dir_name($the_path_to_module)) OR (!SmartFileSysUtils::check_file_or_dir_name($the_module))) {
		self::Raise400Error('Insecure Module Access for Page: '.$page);
		return;
	} //end if
	//--
	if((class_exists('SmartAppIndexController')) OR (class_exists('SmartAppAdminController'))) {
		self::Raise500Error('Module Class Runtimes must be defined only in modules ...');
		return;
	} //end if
	//--
	require((string)$the_module);
	//--
	if(((string)SMART_APP_MODULE_AREA !== 'ADMIN') AND ((string)SMART_APP_MODULE_AREA !== 'SHARED')) {
		self::Raise403Error('Page Access Denied for Admin Area: '.$page);
		return;
	} //end if
	if(defined('SMART_APP_MODULE_AUTH')) {
		if(SmartAuth::check_login() !== true) {
			self::Raise403Error('Page Access Denied ! No Authentication: '.$page);
			return;
		} //end if
		if(defined('SMART_APP_MODULE_REALM_AUTH')) {
			if((string)SmartAuth::get_login_realm() !== (string)SMART_APP_MODULE_REALM_AUTH) {
				self::Raise403Error('Page Access Denied ! Invalid Login Realm: '.$page);
				return;
			} //end if
		} //end if
	} //end if
	//--
	if(!class_exists('SmartAppAdminController')) {
		self::Raise500Error('Invalid Module Class Runtime for Page: '.$page);
		return;
	} //end if
	if(!is_subclass_of('SmartAppAdminController', 'SmartAbstractAppController')) {
		self::Raise500Error('Invalid Module Class Inheritance for Controller Page: '.$page);
		return;
	} //end if
	//--
	//== PATHS
	//--
	$base_script = SmartUtils::get_server_current_script();
	$base_full_path = SmartUtils::get_server_current_path();
	$base_full_url = SmartUtils::get_server_current_url();
	//--
	//== RUN THE MODULE
	//--
	$appModule = new SmartAppAdminController($the_path_to_module, $base_script, $base_full_path, $base_full_url, $page, $the_controller_name);
	//--
	if(SMART_APP_MODULE_DIRECT_OUTPUT !== true) {
		ob_start();
	} //end if
	$appStatusCode = (int) $appModule->Run();
	$appModule->ShutDown();
	if(SMART_APP_MODULE_DIRECT_OUTPUT !== true) {
		$ctrl_output = ob_get_contents();
		ob_end_clean();
		if((string)$ctrl_output != '') {
			Smart::log_warning('The middleware service '.$the_midmark.' detected an illegal output in the controller: '.$page."\n".'The result of this output is: '.$ctrl_output);
		} //end if
		$ctrl_output = '';
	} else {
		return; // break stop after the controller has terminated the direct output
	} //end if else
	//--
	$appSettings = (array) $appModule->PageViewGetCfgs();
	//--
	//== CACHE CONTROL
	//--
	if(((int)$appSettings['expires'] > 0) AND ((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes')) {
		self::HeadersCacheExpire((int)$appSettings['expires'], (int)$appSettings['modified']); // headers: cache expiration control
	} else {
		self::HeadersNoCache(); // headers: cache control, force no-cache
	} //end if else
	//--
	//== STATUS CODE
	//--
	switch((int)$appStatusCode) {
		//-- client errors
		case 400:
			self::Raise400Error((string)$appSettings['error']);
			return;
			break;
		case 401:
			self::Raise401Error((string)$appSettings['error']);
			return;
			break;
		case 403:
			self::Raise403Error((string)$appSettings['error']);
			return;
			break;
		case 404:
			self::Raise404Error((string)$appSettings['error']);
			return;
			break;
		case 429:
			self::Raise429Error((string)$appSettings['error']);
			return;
			break;
		//-- server errors
		case 500:
			self::Raise500Error((string)$appSettings['error']);
			return;
			break;
		case 502:
			self::Raise502Error((string)$appSettings['error']);
			return;
			break;
		case 503:
			self::Raise503Error((string)$appSettings['error']);
			return;
			break;
		case 504:
			self::Raise504Error((string)$appSettings['error']);
			return;
			break;
		//-- extended 2xx statuses: NOTICE / WARNING / ERROR that can be used for REST / API
		case 202: // NOTICE
			if(!headers_sent()) {
				http_response_code(202); // Accepted (this should be used only as an alternate SUCCESS code instead of 200 for NOTICES)
			} else {
				Smart::log_warning('Headers Already Sent before 202 ...');
			} //end if else
			break;
		case 203: // WARNING
			if(!headers_sent()) {
				http_response_code(203); // Non-Authoritative Information (this should be used only as an alternate SUCCESS code instead of 200 for WARNINGS)
			} else {
				Smart::log_warning('Headers Already Sent before 203 ...');
			} //end if else
			break;
		case 208: // ERROR
			if(!headers_sent()) {
				http_response_code(208); // Already Reported (this should be used only as an alternate SUCCESS code instead of 200 for ERRORS)
			} else {
				Smart::log_warning('Headers Already Sent before 208 ...');
			} //end if else
			break;
		//-- DEFAULT: OK
		case 200:
		default: // any other codes not listed above are not supported and will be interpreted as 200
			// nothing to do here ...
	} //end switch
	//--
	//== PREPARE THE OUTPUT
	//--
	if(stripos((string)$configs['js']['popup-override-mobiles'], '<'.SmartUtils::get_os_browser_ip('os').'>') !== false) {
		$configs['js']['popup-mode'] = 'popup'; // particular os settings for mobiles
	} //end if
	//--
	$rawpage = '';
	if(isset($appSettings['rawpage'])) {
		$rawpage = strtolower((string)$appSettings['rawpage']);
		if((string)$rawpage == 'yes') {
			$rawpage = 'yes'; // standardize the value
		} //end if
	} //end if
	if((string)$rawpage != 'yes') {
		$rawpage = '';
	} //end if
	//--
	$rawmime = '';
	if(isset($appSettings['rawmime'])) {
		$rawmime = (string) $appSettings['rawmime'];
		if((string)$rawmime != '') {
			$rawmime = SmartValidator::validate_mime_type($rawmime);
		} //end if
	} //end if else
	//--
	$rawdisp = '';
	if(isset($appSettings['rawdisp'])) {
		$rawdisp = (string) $appSettings['rawdisp'];
		if((string)$rawdisp != '') {
			$rawdisp = SmartValidator::validate_mime_disposition($rawdisp);
		} //end if
	} //end if else
	//--
	$appData = (array) $appModule->PageViewGetVars();
	//--
	$appData['base-path'] = (string) $base_full_path;
	$appData['base-url'] = (string) $base_full_url;
	//--
	//== REDIRECTION HANDLER (this can be set only explicit from Controllers)
	//--
	if((string)$appSettings['redirect-url'] != '') { // expects a valid URL
		//--
		$the_redirect_link = '<a href="'.Smart::escape_html((string)$appSettings['redirect-url']).'">'.Smart::escape_html((string)$appSettings['redirect-url']).'</a>';
		//--
		if(headers_sent()) {
			Smart::log_warning('Headers Already Sent before Redirection: ['.$appStatusCode.'] ; URL: '.$appSettings['redirect-url']);
			self::Raise500Error('The app failed to Redirect to: '.$the_redirect_link);
			return;
		} //end if
		switch((int)$appStatusCode) {
			case 301:
				http_response_code(301);
				$the_redirect_text = 'Moved Permanently'; // permanent redirect for HTTP 1.0 / HTTP 1.1
				break;
			case 302:
			default: // any other code will be interpreted as 302 (the default redirection in PHP)
				http_response_code(302);
				$the_redirect_text = 'Found'; // temporary redirect for HTTP 1.0 / HTTP 1.1
				break;
		} //end switch
		header('Location: '.SmartFrameworkSecurity::FilterUnsafeString((string)$appSettings['redirect-url']));
		echo '<h1>'.Smart::escape_html($the_redirect_text).'</h1>'.'<br>'.'If the page redirection fails, click on the below link:'.'<br>'.$the_redirect_link;
		return; // break stop
	} //end if
	//--
	//== DOWNLOADS HANDLER (downloads can be set only explicit from Controllers)
	//--
	if(((string)$appSettings['download-packet'] != '') AND ((string)$appSettings['download-key'] != '')) { // expects an encrypted data packet and a key
		$dwl_result = self::DownloadsHandler((string)$appSettings['download-packet'], (string)$appSettings['download-key']);
		if((string)$dwl_result != '') {
			Smart::log_info('File Download - Client: '.SmartUtils::get_visitor_signature(), (string)$dwl_result); // log result and mark it as finalized
		} //end if
		return; // break stop
	} //end if
	//--
	//== RAW OUTPUT FOR PAGES
	//--
	if((string)$rawpage == 'yes') {
		//-- {{{SYNC-RESOURCES}}}
		if(function_exists('memory_get_peak_usage')) {
			$res_memory = @memory_get_peak_usage(false);
		} else {
			$res_memory = 'unknown';
		} //end if else
		$res_time = (float) (microtime(true) - (float)SMART_FRAMEWORK_RUNTIME_READY);
		//-- #END-SYNC
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			//-- {{{SYNC-DEBUG-META-INFO}}}
			SmartFrameworkRegistry::setDebugMsg('stats', 'memory', $res_memory); 	// bytes
			SmartFrameworkRegistry::setDebugMsg('stats', 'time',   $res_time); 		// seconds
			//-- #END-SYNC
			$the_debug_cookie = trim((string)$_COOKIE['SmartFramework__DebugAdmID']);
			SmartDebugProfiler::save_debug_info('adm', $the_debug_cookie, false);
		} else {
			$the_debug_cookie = '';
		} //end if
		//--
		if(headers_sent()) {
			Smart::raise_error(
				'Middleware ERROR: Headers already sent',
				'ERROR: Headers already sent !' // msg to display
			);
			return; // avoid serve raw pages with errors injections before headers
		} //end if
		//--
		if((string)$rawmime != '') {
			header('Content-Type: '.$rawmime);
		} //end if
		if((string)$rawdisp != '') {
			header('Content-Disposition: '.$rawdisp);
		} //end if
		header('Content-Length: '.(0+strlen((string)$appData['main']))); // must be strlen NOT SmartUnicode::str_len as it must get number of bytes not characters
		echo (string) $appData['main'];
		return; // break stop
		//--
	} //end if else
	//--
	//== DEFAULT OUTPUT
	//--
	if(isset($appSettings['template-path'])) {
		if((string)$appSettings['template-path'] == '@') { // if template path is set to self (module)
			$the_template_path = '@'; // this is a special setting
		} else {
			$the_template_path = Smart::safe_pathname(SmartFileSysUtils::add_dir_last_slash(trim((string)$appSettings['template-path'])));
		} //end if else
	} else {
		$the_template_path = Smart::safe_pathname(SmartFileSysUtils::add_dir_last_slash(trim((string)$configs['app']['admin-template-path']))); // use default template path
	} //end if else
	//--
	if(isset($appSettings['template-file'])) {
		$the_template_file = Smart::safe_filename(trim((string)$appSettings['template-file']));
	} else {
		$the_template_file = Smart::safe_filename(trim((string)$configs['app']['admin-template-file'])); // use default template
	} //end if else
	//--
	if((string)$the_template_path == '@') {
		$the_template_path = (string) $the_path_to_module.'templates/'; // must have the dir last slash as above
	} else {
		$the_template_path = (string) SMART_APP_TEMPLATES_DIR.$the_template_path; // finally normalize and set the complete template path
	} //end if else
	$the_template_file = (string) $the_template_file; // finally normalize
	//--
	if(!SmartFileSysUtils::check_file_or_dir_name($the_template_path)) {
		Smart::log_warning('Invalid Page Template Path: '.$the_template_path);
		self::Raise500Error('Invalid Page Template Path. See the error log !');
		return;
	} //end if
	if(!is_dir($the_template_path)) {
		Smart::log_warning('Page Template Path does not Exists: '.$the_template_path);
		self::Raise500Error('Page Template Path does not Exists. See the error log !');
		return;
	} //end if
	if(!SmartFileSysUtils::check_file_or_dir_name($the_template_path.$the_template_file)) {
		Smart::log_warning('Invalid Page Template File: '.$the_template_path.$the_template_file);
		self::Raise500Error('Invalid Page Template File. See the error log !');
		return;
	} //end if
	if(!is_file($the_template_path.$the_template_file)) {
		Smart::log_warning('Page Template File does not Exists: '.$the_template_path.$the_template_file);
		self::Raise500Error('Page Template File does not Exists. See the error log !');
		return;
	} //end if
	//--
	$the_template_content = trim(SmartFileSystem::staticread($the_template_path.$the_template_file));
	if((string)$the_template_content == '') {
		Smart::log_warning('Page Template File is Empty or cannot be read: '.$the_template_path.$the_template_file);
		self::Raise500Error('Page Template File is Empty or cannot be read. See the error log !');
		return;
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		$the_template_content = str_ireplace('</head>', "\n".SmartDebugProfiler::js_headers_debug('admin.php?smartframeworkservice=debug')."\n".'</head>', $the_template_content);
		$the_template_content = str_ireplace('</body>', "\n".SmartDebugProfiler::div_main_debug()."\n".'</body>', $the_template_content);
	} //end if
	//--
	$appData['app-domain'] = (string) $configs['app']['admin-domain'];
	$appData['template-file'] = $the_template_path.$the_template_file;
	$appData['template-path'] = $the_template_path;
	$appData['js.settings'] = SmartComponents::js_inc_settings((string)$configs['js']['popup-mode'], true, (bool)SMART_APP_VISITOR_COOKIE);
	$appData['head-meta'] = (string) $appData['head-meta'];
	if((string)$appData['head-meta'] == '') {
		$appData['head-meta'] = '<!-- Head Meta -->';
	} //end if
	$appData['title'] = (string) $appData['title'];
	$appData['main'] = (string) $appData['main'];
	$appData['lang'] = SmartTextTranslations::getLanguage();
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		$the_debug_cookie = 'adm-'.Smart::uuid_10_seq().'-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str();
		@setcookie('SmartFramework__DebugAdmID', (string)$the_debug_cookie, 0, '/'); // debug token cookie is set just on main request
		//--
	} //end if
	//--
	echo SmartMarkersTemplating::render_mixed_template((string)$the_template_content, (array)$appData, (string)$appData['template-path'], 'no', 'no');
	//-- {{{SYNC-RESOURCES}}}
	if(function_exists('memory_get_peak_usage')) {
		$res_memory = @memory_get_peak_usage(false);
	} else {
		$res_memory = 'unknown';
	} //end if else
	$res_time = (float) (microtime(true) - (float)SMART_FRAMEWORK_RUNTIME_READY);
	//-- #END-SYNC
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//-- {{{SYNC-DEBUG-META-INFO}}}
		SmartFrameworkRegistry::setDebugMsg('stats', 'memory', $res_memory); 	// bytes
		SmartFrameworkRegistry::setDebugMsg('stats', 'time',   $res_time); 		// seconds
		//-- #END-SYNC
		SmartDebugProfiler::save_debug_info('adm', $the_debug_cookie, true);
		//--
	} //end if else
	//--
	echo "\n".'<!-- Smart.Framework スマート.フレームワーク :: '.SMART_FRAMEWORK_RELEASE_TAGVERSION.' / '.SMART_FRAMEWORK_RELEASE_VERSION.' @ '.$the_midmark.' :: '.SMART_FRAMEWORK_RELEASE_URL.' -->'."\n".'<!-- Resources リソース: ['.Smart::format_number_dec($res_time, 13, '.', '').' sec.] / ['.Smart::format_number_dec($res_memory, 0, '.', ' ').' by.]'.' -->'."\n";
	//--
} //END FUNCTION
//====================================================================


} //END CLASS


//==================================================================================
//================================================================================== CLASS END
//==================================================================================


// end of php code
?>