<?php
// [LIB - SmartFramework / Debug]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.7 r.2017.02.22 / smart.framework.v.2.3

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
// Smart-Framework - Debug
// DEPENDS:
//	* Smart::
//	* SmartComponents::
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class Smart Debug Profiler
 *
 * @access 		private
 * @internal
 *
 * @version 	v.170315
 *
 */
final class SmartDebugProfiler {

	// ::


//==================================================================
public static function js_headers_debug($y_profiler_url) {

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
		return false;
	} //end if
	//--

	//--
	return SmartMarkersTemplating::render_file_template(
		'lib/core/templates/debug-profiler-head.inc.htm',
		array(
			'DEBUG-PROFILER-URL' => Smart::escape_js($y_profiler_url)
		),
		'no' // no cache
	);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function div_main_debug() {

	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
		return false;
	} //end if
	//--

	//--
	return '<div id="SmartFramework__Debug__Profiler"></div>';
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function save_debug_info($y_area, $y_debug_token, $is_main) {

	//-- {{{SYNC-DEBUG-DATA}}}
	if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
		return false;
	} //end if
	//--
	if(((string)$y_area != 'idx') AND ((string)$y_area != 'adm')) {
		return false;
	} //end if
	//--
	$y_debug_token = trim((string)$y_debug_token);
	if((string)$y_debug_token == '') {
		return false;
	} //end if
	//--
	$the_dir = 'tmp/logs/'.Smart::safe_filename($y_area).'/'.date('Y-m-d@H').'-debug-data/'.Smart::safe_filename($y_debug_token).'/';
	//-- #END# SYNC

	//--
	if($is_main) {
		$the_file = $the_dir.'debug-main.log';
	} else {
		$the_file = $the_dir.'debug-sub-req-'.time().'-'.SmartHashCrypto::sha1($_SERVER['REQUEST_URI']).'.log';
	} //end if else
	//--

	//--
	if(!is_dir($the_dir)) {
		SmartFileSystem::dir_recursive_create($the_dir);
	} //end if
	//--
	if(is_dir($the_dir)) {
		if(is_writable($the_dir)) {
			//--
			$arr = array();
			//-- generate debug info if set to show optimizations
			SmartMarkersTemplating::registerOptimizationHintsToDebugLog();
			//-- generate debug info if set to show internals
			if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
				Smart::registerInternalCacheToDebugLog();
				SmartFrameworkRegistry::registerInternalCacheToDebugLog();
				SmartAuth::registerInternalCacheToDebugLog();
				SmartHashCrypto::registerInternalCacheToDebugLog();
				SmartUtils::registerInternalCacheToDebugLog();
				SmartMarkersTemplating::registerInternalCacheToDebugLog();
			} //end if
			//--
			$dbg_stats = (array) SmartFrameworkRegistry::getDebugMsgs('stats');
			//--
			$arr['date-time'] = date('Y-m-d H:i:s O');
			$arr['debug-token'] = (string) $y_debug_token;
			$arr['is-request-main'] = $is_main;
			$arr['request-hash'] = SmartHashCrypto::sha1($_SERVER['REQUEST_URI']);
			$arr['request-uri'] = (string) $_SERVER['REQUEST_URI'];
			$arr['resources-time'] = $dbg_stats['time'];
			$arr['resources-memory'] = $dbg_stats['memory'];
			$arr['response-code'] = (int)http_response_code();
			$arr['response-headers'] = base64_encode(Smart::seryalize((array)headers_list()));
			if(function_exists('getallheaders')) {
				$arr['request-headers'] = base64_encode(Smart::seryalize((array)getallheaders()));
			} else {
				$arr['request-headers'] = base64_encode(Smart::seryalize(''));
			} //end if else
			$arr['env-req-filtered'] = base64_encode(Smart::seryalize((array)SmartFrameworkRegistry::getRequestVars()));
			$arr['env-get'] = base64_encode(Smart::seryalize((array)$_GET));
			$arr['env-post'] = base64_encode(Smart::seryalize((array)$_POST));
			$arr['env-cookies'] = base64_encode(Smart::seryalize((array)$_COOKIE));
			$arr['env-server'] = base64_encode(Smart::seryalize((array)$_SERVER));
			if(@session_status() === PHP_SESSION_ACTIVE) {
				$arr['php-session'] = base64_encode(Smart::seryalize((array)$_SESSION));
			} else {
				$arr['php-session'] = base64_encode(Smart::seryalize(''));
			} //end if else
			if(SmartAuth::check_login() === true) {
				$arr['auth-data'] = array('is_auth' => true, 'login_data' => (array)SmartAuth::get_login_data(), '#login-pass#', SmartAuth::get_login_password());
			} else {
				$arr['auth-data'] = array('is_auth' => false, 'login_data' => []);
			} //end if else
			foreach((array)SmartFrameworkRegistry::getDebugMsgs('optimizations') as $key => $val) {
				$arr['log-optimizations'][(string)$key] = base64_encode(Smart::seryalize((array)$val));
			} //end foreach
			foreach((array)SmartFrameworkRegistry::getDebugMsgs('extra') as $key => $val) {
				$arr['log-extra'][(string)$key] = base64_encode(Smart::seryalize((array)$val));
			} //end foreach
			foreach((array)SmartFrameworkRegistry::getDebugMsgs('db') as $key => $val) {
				$arr['log-db'][(string)$key] = base64_encode(Smart::seryalize((array)$val));
			} //end foreach
			if(Smart::array_size((array)SmartFrameworkRegistry::getDebugMsgs('mail')) > 0) {
				$arr['log-mail'] = base64_encode(Smart::seryalize((array)SmartFrameworkRegistry::getDebugMsgs('mail')));
			} else {
				$arr['log-mail'] = '';
			} //end if else
			foreach((array)SmartFrameworkRegistry::getDebugMsgs('modules') as $key => $val) {
				$arr['log-modules'][(string)$key] = base64_encode(Smart::seryalize((array)$val));
			} //end foreach
			//--
			SmartFileSystem::write($the_file, Smart::seryalize($arr));
			//--
		} //end if
	} //end if
	//--

	//--
	return true;
	//--

} //END FUNCTION
//==================================================================


//==================================================================
// return HTML Formatted Debug Messages
// will no more echo because it can be used also with raw pages
public static function print_debug_info($y_area, $y_debug_token) {

	global $configs;

	//-- {{{SYNC-DEBUG-DATA}}}
	if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
		return '';
	} //end if
	//--
	if(((string)$y_area != 'idx') AND ((string)$y_area != 'adm')) {
		return '';
	} //end if
	//--
	$y_debug_token = trim((string)$y_debug_token);
	if((string)$y_debug_token == '') {
		return '';
	} //end if
	//--
	$the_dir = 'tmp/logs/'.Smart::safe_filename($y_area).'/'.date('Y-m-d@H').'-debug-data/'.Smart::safe_filename($y_debug_token).'/';
	//-- #END# SYNC

	//--
	$storage = (new SmartGetFileSystem(true))->get_storage($the_dir, true, false);
	$arr = array();
	if(is_array($storage['list-files'])) {
		$storage['list-files'] = Smart::array_sort($storage['list-files'], 'natsort');
		for($i=0; $i<Smart::array_size($storage['list-files']); $i++) {
			$arr[] = Smart::unseryalize(SmartFileSystem::read($storage['list-files'][$i]));
		} //end if
	} //end if
	$storage = array();
	//--

	//--
	$debug_response = '';
	$debug_resources = '';
	$debug_environment = '';
	$debug_session = '';
	$debug_auth = '';
	$debug_mail = '';
	$debug_dbqueries = '';
	$debug_optimizations = '';
	$debug_extra = '';
	$debug_modules = '';
	$tmp_decode_arr = array();
	//--
	$start_marker = '<div class="smartframework_debugbar_status smartframework_debugbar_status_title"><font size="5"><b># DEBUG Data :: ALL REQUESTS #</b></font></div>';
	$end_marker = '<div class="smartframework_debugbar_status smartframework_debugbar_status_title"><font size="3"><b># DEBUG # END #</b></font></div>';
	//--
	for($i=0; $i<Smart::array_size($arr); $i++) {
		//--
		if($arr[$i]['is-request-main'] === true) {
			$txt_main = '<div class="smartframework_debugbar_status smartframework_debugbar_status_title"><font size="5"><b># DEBUG Data :: MAIN REQUEST #</b></font></div>';
		} else {
			$txt_main = '<div class="smartframework_debugbar_status smartframework_debugbar_status_title"><font size="3"><b># DEBUG Data :: SUB-REQUEST #</b></font></div>';
		} //end if else
		$txt_token = '<div class="smartframework_debugbar_status smartframework_debugbar_status_token" style="width: 400px; text-align: center;"><font size="2"><b>Debug Token: '.Smart::escape_html($arr[$i]['debug-token']).'</b></font></div>';
		$txt_url = '<div class="smartframework_debugbar_status smartframework_debugbar_status_url"><font size="2">URL: '.Smart::escape_html($arr[$i]['request-uri']).'</font></div>';
		//--
		$debug_response .= $txt_main.$txt_url.$txt_token.self::print_log_headers($arr[$i]['response-code'], Smart::unseryalize(base64_decode($arr[$i]['response-headers'])), Smart::unseryalize(base64_decode($arr[$i]['request-headers']))).'<hr>';
		//--
		$debug_resources .= $txt_main.$txt_url.$txt_token.self::print_log_resources($arr[$i]['resources-time'], $arr[$i]['resources-memory']);
		//--
		$debug_environment .= $txt_main.$txt_url.$txt_token.self::print_log_environment(Smart::unseryalize(base64_decode($arr[$i]['env-req-filtered'])), Smart::unseryalize(base64_decode($arr[$i]['env-cookies'])), Smart::unseryalize(base64_decode($arr[$i]['env-get'])), Smart::unseryalize(base64_decode($arr[$i]['env-post'])), Smart::unseryalize(base64_decode($arr[$i]['env-server']))).'<hr>';
		//--
		$debug_session .= $txt_main.$txt_url.$txt_token.self::print_log_session(Smart::unseryalize(base64_decode($arr[$i]['php-session']))).'<hr>';
		//--
		$debug_auth .= $txt_main.$txt_url.$txt_token.self::print_log_auth($arr[$i]['auth-data']).'<hr>';
		//--
		if(is_array($arr[$i]['log-optimizations'])) {
			$debug_optimizations .= $txt_main.$txt_url.$txt_token;
			foreach($arr[$i]['log-optimizations'] as $key => $val) {
				$debug_optimizations .= self::print_log_optimizations(strtoupper((string)$key), Smart::unseryalize(base64_decode($val))).'<hr>';
			} //end foreach
		} //end if
		//--
		if((string)$arr[$i]['log-mail'] != '') {
			$debug_mail .= $txt_main.$txt_url.$txt_token.self::print_log_mail(Smart::unseryalize(base64_decode($arr[$i]['log-mail']))).'<hr>';
		} //end if
		//--
		if(is_array($arr[$i]['log-db'])) {
			$debug_dbqueries .= $txt_main.$txt_url.$txt_token;
			foreach($arr[$i]['log-db'] as $key => $val) {
				$debug_dbqueries .= self::print_log_database(strtoupper((string)$key), Smart::unseryalize(base64_decode($val))).'<hr>';
			} //end foreach
		} //end if
		//--
		if(is_array($arr[$i]['log-extra'])) {
			$debug_extra .= $txt_main.$txt_url.$txt_token;
			foreach($arr[$i]['log-extra'] as $key => $val) {
				$debug_extra .= self::print_log_extra(strtoupper((string)$key), Smart::unseryalize(base64_decode($val))).'<hr>';
			} //end foreach
		} //end if
		//--
		if(is_array($arr[$i]['log-modules'])) {
			$debug_modules .= $txt_main.$txt_url.$txt_token;
			foreach($arr[$i]['log-modules'] as $key => $val) {
				$debug_modules .= self::print_log_modules(strtoupper((string)$key), Smart::unseryalize(base64_decode($val))).'<hr>';
			} //end foreach
		} //end if
		//--
	} //end for
	//--
	if((string)$debug_optimizations == '') {
		$debug_optimizations = '<div class="smartframework_debugbar_status smartframework_debugbar_status_nodata"><font size="5"><b>Optimization Hints: N/A</b></font></div>';
	} else {
		$debug_optimizations .= $end_marker;
	} //end if else
	//--
	if((string)$debug_mail == '') {
		$debug_mail = '<div class="smartframework_debugbar_status smartframework_debugbar_status_nodata"><font size="5"><b>Mail Debug: No data</b></font></div>';
	} else {
		$debug_mail .= $end_marker;
	} //end if else
	//--
	if((string)$debug_dbqueries == '') {
		$debug_dbqueries = '<div class="smartframework_debugbar_status smartframework_debugbar_status_nodata"><font size="5"><b>Database Debug: No Queries found</b></font></div>';
	} else {
		$debug_dbqueries .= $end_marker;
	} //end if else
	//--
	if((string)$debug_extra == '') {
		$debug_extra = '<div class="smartframework_debugbar_status smartframework_debugbar_status_nodata"><font size="5"><b>Extra Debug: No data</b></font></div>';
	} else {
		$debug_extra .= $end_marker;
	} //end if else
	//--
	if((string)$debug_modules == '') {
		$debug_modules = '<div class="smartframework_debugbar_status smartframework_debugbar_status_nodata"><font size="5"><b>Modules Debug: No data</b></font></div>';
	} else {
		$debug_modules .= $end_marker;
	} //end if else
	//--

	//--
	return SmartMarkersTemplating::render_file_template(
		'lib/core/templates/debug-profiler-footer.inc.htm',
		array(
			'DEBUG-TIME' => date('Y-m-d H:i:s O'),
			'DEBUG-RUNTIME' => $start_marker.self::print_log_runtime().$end_marker, // ok
			'DEBUG-CONFIGS' => $start_marker.self::print_log_configs().$end_marker, // ok
			'DEBUG-RESOURCES' => $debug_resources.$end_marker, // ok
			'DEBUG-HEADERS' => $debug_response.$end_marker, // ok
			'DEBUG-ENVIRONMENT' => $debug_environment.$end_marker, // ok
			'DEBUG-SESSION' => $debug_session.$end_marker, // ok
			'DEBUG-AUTH' => $debug_auth.$end_marker,
			'DEBUG-OPTIMIZATIONS' => $debug_optimizations, // ok
			'DEBUG-MAIL' => $debug_mail,
			'DEBUG-DATABASE' => $debug_dbqueries, // ok
			'DEBUG-EXTRA' => $debug_extra, // ok
			'DEBUG-MODULES' => $debug_modules // ok
		),
		'no' // no cache
	);
	//--


} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_mail($log_mail_arr) {
	//--
	$log = '';
	//--
	$max = Smart::array_size($log_mail_arr);
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>MAIL Log</b></font></div>';
	//--
	if(is_array($log_mail_arr) AND ($max > 0)) {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Entries: <b>'.Smart::escape_html($max).'</b></div>';
		//--
		foreach($log_mail_arr as $key => $val) {
			//--
			$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Operation: <b>'.strtoupper((string)$key).'</b></div>';
			$log .= '<div class="smartframework_debugbar_inforow" style="font-size:11px; color:#000000;">';
			if(is_array($val)) {
				$log .= '<pre style="font-size:11px; color:#000000;">'.Smart::escape_html(str_replace(array("\r\n", "\r", "\t"), array("\n", "\n", ' '), trim((string)implode("\n\n##########\n\n", $val)))).'</pre>';
			} else {
				$log .= '<pre style="font-size:11px; color:#000000;">'.Smart::escape_html(str_replace(array("\r\n", "\r", "\t"), array("\n", "\n", ' '), trim((string)self::print_value_by_type($val)))).'</pre>';
			} //end if else
			$log .= '</div>';
			//--
		} //end foreach
		//--
	} else {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 100px; text-align: center;"><font size="2"><b>N/A</b></font></div>';
		//--
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_runtime() {
	//--
	$log = '';
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>Client / Server :: RUNTIME Log</b></font></div>';
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;"><b>App Runtime - Powered by</b></div>';
	$log .= '<div class="smartframework_debugbar_inforow">'.SmartComponents::draw_powered_info('yes').'</div>';
	//--
	if(SMART_FRAMEWORK_ADMIN_AREA === true) {
		$the_area = 'admin';
	} else {
		$the_area = 'index';
	} //end if else
	//--
	$arr = [
		'Server Runtime: Smart.Framework' => [
			'Smart.Framework Middleware Area' => $the_area,
			'Smart.Framework Release / Tag / Branch' => SMART_FRAMEWORK_RELEASE_VERSION.' / '.SMART_FRAMEWORK_RELEASE_TAGVERSION.' / '.SMART_FRAMEWORK_VERSION,
			'Smart.Framework Encoding: Internal / DB' => SMART_FRAMEWORK_CHARSET.' / '.SMART_FRAMEWORK_DBSQL_CHARSET
		],
		'Server Runtime: PHP' => [
			'PHP OS' => (string) PHP_OS,
			'PHP Version' => 'PHP '.PHP_VERSION.' / '.@php_sapi_name(),
			'PHP Locales: ' => (string) setlocale(LC_ALL, 0),
			'PHP Encoding: Internal / MBString' => ini_get('default_charset').' / '.@mb_internal_encoding(),
			'PHP Memory' => (string) ini_get('memory_limit'),
			'PHP Loaded Modules' => (string) strtolower(implode(', ', (array)@get_loaded_extensions()))
		],
		'Server Domain Info' => [
			'Server Full URL' => SmartUtils::get_server_current_url(),
			'Server Script' => SmartUtils::get_server_current_script(),
			'Server IP' => SmartUtils::get_server_current_ip(),
			'Server Port' => SmartUtils::get_server_current_port(),
			'Server Protocol' => SmartUtils::get_server_current_protocol(),
			'Server Path' => SmartUtils::get_server_current_path(),
			'Server Domain' => SmartUtils::get_server_current_domain_name(),
			'Server Base Domain' => SmartUtils::get_server_current_basedomain_name()
		],
		'Client Runtime' => (string) $_SERVER['HTTP_USER_AGENT'].' # '.SmartUtils::get_ip_client().' # '.SmartUtils::get_ip_proxyclient()
	];
	//--
	foreach($arr as $debug_key => $debug_val) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;"><b>'.Smart::escape_html($debug_key).'</b></div>';
		if(is_array($debug_val)) {
			$log .= '<table cellspacing="0" cellpadding="2" width="100%">';
			foreach($debug_val as $key => $val) {
				$log .= '<tr valign="top"><td width="295"><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($key).'</b></div></td><td><div class="smartframework_debugbar_inforow">'.Smart::escape_html(self::print_value_by_type($val)).'</div></td></tr>';
			} //end foreach
			$log .= '</table>';
		} else {
			$log .= '<div class="smartframework_debugbar_inforow">'.Smart::escape_html((string)$debug_val).'</div>';
		} //end if else
	} //end while
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_configs() {
	//--
	global $configs;
	//--
	$log = '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>Application :: CONFIGURATION Log</b></font></div>';
	//-- vars
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;"><b>App CONFIG VARIABLES</b></div>';
	$arr = (array) $configs;
	ksort($arr);
	$i=0;
	$j=0;
	foreach((array)$arr as $key => $val) {
		//--
		$i++;
		//--
		$log .= '<table cellspacing="0" cellpadding="2" width="100%">';
		$log .= '<tr valign="top" title="#'.$i.'"><td width="195"><div class="smartframework_debugbar_inforow">';
		$log .= '<b>'.Smart::escape_html((string)$key).'</b>';
		$log .= '</div></td><td><div class="smartframework_debugbar_inforow">';
		if(is_array($val)) {
			$log .= '<table width="100%" cellpadding="1" cellspacing="0" border="0">';
			$j=0;
			foreach($val as $k => $v) {
				$j++;
				if($j % 2) {
					$color = '#FFFFFF';
				} else {
					$color = '#FAFAFA';
				} //end if else
				$log .= '<tr bgcolor="'.$color.'" valign="top" title="#'.$i.'.'.$j.'"><td width="290"><b>'.Smart::escape_html((string)$k).'</b></td><td>'.Smart::nl_2_br(Smart::escape_html(self::print_value_by_type($v))).'</td></tr>';
			} //end foreach
			$log .= '</table>';
		} else {
			$log .= '<pre>'.Smart::escape_html((string)$val).'</pre>';
		} //end if else
		$log .= '</div></td></tr>';
		$log .= '</table>';
		//--
	} //end while
	//-- constants
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;"><b>App SETTING CONSTANTS</b></div>';
	$arr = (array) get_defined_constants(true);
	$arr = (array) $arr['user'];
	ksort($arr);
	$i=0;
	$j=0;
	foreach((array)$arr as $key => $val) {
		//--
		$i++;
		//--
		if(((string)$key == 'SMART_FRAMEWORK_CHMOD_DIRS') OR ((string)$key == 'SMART_FRAMEWORK_CHMOD_FILES')) {
			if(is_numeric($val)) {
				$val = (string) '0'.@decoct($val).' (octal)';
			} else {
				$val = (string) $val.' (!!! Warning, Invalid ... Must be OCTAL !!!)';
			} //end if
		} //end if
		//--
		$log .= '<table cellspacing="0" cellpadding="2" width="100%">';
		$log .= '<tr valign="top" title="#'.$i.'"><td width="375"><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html((string)$key).'</b></div></td><td><div class="smartframework_debugbar_inforow">'.Smart::nl_2_br(Smart::escape_html(self::print_value_by_type($val))).'</div></td></tr>';
		$log .= '</table>';
		//--
	} //end while
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_resources($time_res, $mem_res) {
	//--
	$log = '';
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>Script Execution :: RESOURCES Log</b></font></div>';
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;"><b>Script Execution Resources</b></div>';
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_inforow" style="width:450px;">Execution Time: <b>'.Smart::format_number_dec($time_res, 13, '.', '').' sec.'.'</b></div>';
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_inforow" style="width:450px;">Execution Memory: <b>'.SmartUtils::pretty_print_bytes($mem_res, 2).'</b></div>';
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_headers($response_code, $response_heads_arr, $request_heads_arr) {
	//--
	$log = '';
	//--
	if($response_code == 200) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>RESPONSE Headers: [ '.Smart::escape_html($response_code).' / OK ]</b></font></div>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn"><font size="4"><b>RESPONSE Headers: [ '.Smart::escape_html($response_code).' ]</b></font></div>';
	} //end if else
	$max = Smart::array_size($response_heads_arr);
	if($max > 0) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Entries: <b>'.Smart::escape_html($max).'</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		foreach($response_heads_arr as $debug_key => $debug_val) {
			$log .= '<tr valign="top"><td style="min-width: 25px;"><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html($debug_val).'</font></div></td></tr>';
		} //end while
		$log .= '</table>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 250px; text-align: center;"><font size="2"><b>RESPONSE Headers are Empty</b></font></div>';
	} //end if
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>REQUEST Headers</b></font></div>';
	$max = Smart::array_size($request_heads_arr);
	if($max > 0) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Entries: <b>'.Smart::escape_html($max).'</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		foreach($request_heads_arr as $debug_key => $debug_val) {
			$log .= '<tr valign="top"><td style="min-width: 150px;"><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html($debug_val).'</font></div></td></tr>';
		} //end while
		$log .= '</table>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 250px; text-align: center;"><font size="2"><b>REQUEST Headers are Empty</b></font></div>';
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_environment($req_filtered, $cookies_arr, $get_arr, $post_arr, $server_arr) {
	//--
	$log = '';
	//--
	$filter_strings = 'Non-Filtered';
	if(defined('SMART_FRAMEWORK_SECURITY_FILTER_INPUT')) {
		$filter_strings = 'Filtered: `'.Smart::escape_html(addslashes((string)SMART_FRAMEWORK_SECURITY_FILTER_INPUT)).'`';
	} //end if
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>REQUEST SemanticURL/GET/POST Vars :: '.$filter_strings.'</b></font></div>';
	$max = Smart::array_size($req_filtered);
	if($max > 0) {
		$tbl = '<table cellspacing="0" cellpadding="2">';
		$cnt = 0;
		foreach($req_filtered as $debug_key => $debug_val) {
			$cnt++;
			$tbl .= '<tr valign="top"><td><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html(self::print_value_by_type($debug_val)).'</font></div></td></tr>';
		} //end while
		$tbl .= '</table>';
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total (Non-Empty) Variables: <b>'.Smart::escape_html($cnt).'</b></div>';
		$log .= $tbl;
		$tbl = '';
		$cnt = 0;
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width: 250px; text-align: center;"><font size="2"><b>No Variables Found</b></font></div>';
	} //end if
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>COOKIE Vars</b></font></div>';
	$max = Smart::array_size($cookies_arr);
	if($max > 0) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Variables: <b>'.Smart::escape_html($max).'</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		foreach($cookies_arr as $debug_key => $debug_val) {
			$log .= '<tr valign="top"><td><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html($debug_val).'</font></div></td></tr>';
		} //end while
		$log .= '</table>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width: 250px; text-align: center;"><font size="2"><b>No Cookies Found</b></font></div>';
	} //end if
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>(Raw) GET Vars</b></font></div>';
	$max = Smart::array_size($get_arr);
	if($max > 0) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Variables: <b>'.Smart::escape_html($max).'</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		foreach($get_arr as $debug_key => $debug_val) {
			$log .= '<tr valign="top"><td><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html(self::print_value_by_type($debug_val)).'</font></div></td></tr>';
		} //end while
		$log .= '</table>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width: 250px; text-align: center;"><font size="2"><b>No GET Vars Found</b></font></div>';
	} //end if
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>(Raw) POST Vars</b></font></div>';
	$max = Smart::array_size($post_arr);
	if($max > 0) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Variables: <b>'.Smart::escape_html($max).'</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		foreach($post_arr as $debug_key => $debug_val) {
			$log .= '<tr valign="top"><td><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html(self::print_value_by_type($debug_val)).'</font></div></td></tr>';
		} //end while
		$log .= '</table>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width: 250px; text-align: center;"><font size="2"><b>No POST Vars Found</b></font></div>';
	} //end if
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>SERVER Vars</b></font></div>';
	$max = Smart::array_size($server_arr);
	if($max > 0) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Variables: <b>'.Smart::escape_html($max).'</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		foreach($server_arr as $debug_key => $debug_val) {
			$log .= '<tr valign="top"><td style="min-width: 200px;"><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html($debug_val).'</font></div></td></tr>';
		} //end while
		$log .= '</table>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 250px; text-align: center;"><font size="2"><b>Cannot find any SERVER Variable</b></font></div>';
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_session($session_arr) {
	//--
	$log = '';
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>SESSION Vars</b></font></div>';
	$max = Smart::array_size($session_arr);
	if($max > 0) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Variables: <b>'.Smart::escape_html($max).'</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		while(list($debug_key, $debug_val) = each($session_arr)) {
			if((is_array($debug_val)) OR (is_object($debug_val))) {
				$log .= '<tr valign="top"><td><div class="smartframework_debugbar_inforow"><font color="#333333"><b>'.Smart::escape_html($debug_key).'</b></font></div></td><td><div class="smartframework_debugbar_inforow"><pre color:#333333;">'.Smart::escape_html(self::print_value_by_type($debug_val)).'</pre></div></td></tr>';
			} else {
				$log .= '<tr valign="top"><td><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">'.Smart::escape_html($debug_val).'</font></div></td></tr>';
			} //end if else
		} //end while
		$log .= '</table>';
	} elseif(is_array($session_arr)) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 250px; text-align: center;"><font size="2"><b>Session contains NO Variables</b></font></div>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width: 250px; text-align: center;"><font size="2"><b>Session Not Started</b></font></div>';
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_auth($auth_arr) {
	//--
	$is_auth = (bool) $auth_arr['is_auth'];
	$login_data = (array) $auth_arr['login_data'];
	//--
	$log = '';
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>Authentication Info</b></font></div>';
	//--
	if($is_auth) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;"><b>Authenticated :: OK</b></div>';
		$log .= '<table cellspacing="0" cellpadding="2">';
		foreach($login_data as $debug_key => $debug_val) {
			$log .= '<tr valign="top"><td><div class="smartframework_debugbar_inforow"><b>'.Smart::escape_html($debug_key).'</b></div></td><td><div class="smartframework_debugbar_inforow"><font color="#000000">';
			if(is_array($debug_val)) {
				$log .= '<pre>'.Smart::escape_html(self::print_value_by_type($debug_val)).'</pre>';
			} else {
				$log .= Smart::escape_html($debug_val);
			} //end if else
			$log .= '</font></div></td></tr>';
		} //end for
		$log .= '</table>';
	} else {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 250px; text-align: center;"><font size="2"><b>Not Authenticated</b></font></div>';
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_database($title, $db_log) {
	//--
	$log = '';
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>'.Smart::escape_html($title).' :: DATABASE Queries</b></font></div>';
	//--
	$max = Smart::array_size($db_log['log']);
	if(is_array($db_log) AND ($max > 0)) {
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Queries Number: <b>'.Smart::escape_html(Smart::format_number_int($db_log['total-queries'], '+')).'</b></div>';
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Queries Time: <b>'.Smart::escape_html(Smart::format_number_dec($db_log['total-time'], 9, '.', '')).' sec.'.'</b></div>';
		$num = 0;
		for($i=0; $i<$max; $i++) {
			//--
			$tmp_arr = (array) $db_log['log'][$i];
			//--
			switch((string)$tmp_arr['type']) {
				case 'transaction':
					//--
					$num++;
					//--
					$tmp_color = '#003399';
					//--
					$log .= '<div class="smartframework_debugbar_inforow" style="font-size:12px; color:'.$tmp_color.';">';
					$log .= $num.'. '.'<b>'.Smart::escape_html($tmp_arr['data']).'</b>';
					if((string)$tmp_arr['connection'] != '') {
						$log .= ' @ '.Smart::escape_html($tmp_arr['connection']);
					} //end if
					if($tmp_arr['time'] > 0) {
						$log .= '<br><span style="padding:1px;"><b>@Time: '.Smart::format_number_dec($tmp_arr['time'], 9, '.', '').' sec.</b></span>';
					} //end if
					if((string)$tmp_arr['query'] != '') {
						$log .= '<br><span class="smartframework_debugbar_status_highlight" style="padding:1px;"><b>'.Smart::escape_html($tmp_arr['query']).'</b></span>';
					} //end if
					$log .= '</div>';
					//--
					break;
				case 'set':
				case 'count':
				case 'read':
				case 'write':
				case 'nosql':
					//--
					if((string)$tmp_arr['skip-count'] != 'yes') {
						$num++;
					} //end if
					//--
					if((string)$tmp_arr['type'] == 'write') {
						$tmp_color = '#666699';
					} elseif((string)$tmp_arr['type'] == 'read') {
						$tmp_color = '#333333';
					} elseif((string)$tmp_arr['type'] == 'count') {
						$tmp_color = '#339900';
					} elseif((string)$tmp_arr['type'] == 'set') {
						$tmp_color = '#778899';
					} else { // nosql
						$tmp_color = '#000000';
					} //end if else
					//--
					$log .= '<div class="smartframework_debugbar_inforow" style="font-size:11px; color:'.$tmp_color.';">';
					if((string)$tmp_arr['skip-count'] != 'yes') {
						$log .= $num.'. ';
					} //end if else
					$log .= '<b>'.Smart::escape_html($tmp_arr['data']).'</b>';
					if((string)$tmp_arr['connection'] != '') {
						$log .= ' @ '.Smart::escape_html($tmp_arr['connection']);
					} //end if
					if($tmp_arr['time'] > 0) {
						if($tmp_arr['time'] <= $db_log['slow-time']) {
							$log .= '<br><span style="padding:1px;"><b>@Time: '.Smart::format_number_dec($tmp_arr['time'], 9, '.', '').' sec.</b></span>';
						} else {
							$log .= '<br><span class="smartframework_debugbar_status_warn" style="padding:1px;" title="Slow-Time: '.Smart::escape_html($db_log['slow-time']).'"><b>@Time: '.Smart::format_number_dec($tmp_arr['time'], 9, '.', '').' sec.'.'</b></span>';
						} //end if else
					} //end if
					if((string)$tmp_arr['query'] != '') {
						$log .= '<br>'.Smart::escape_html($tmp_arr['query']);
						if((string)$tmp_arr['type'] == 'write') {
							$log .= '<br>'.'AFFECTED ROWS: #'.$tmp_arr['rows'];
						} //end if
						if(Smart::array_size($tmp_arr['params']) > 0) {
							$tmp_params = array();
							foreach($tmp_arr['params'] as $key => $val) {
								$tmp_params[] = Smart::escape_html('$'.($key+1).' => `'.$val.'`');
							} //end foreach
							$log .= '<br>'.'@PARAMS:&nbsp;{ '.implode(', ', $tmp_params).' }';
							$tmp_params = array();
						} //end if
					} //end if
					if(is_array($tmp_arr['command'])) {
						$tmp_params = array();
						foreach($tmp_arr['command'] as $key => $val) {
							$tmp_params[] = Smart::escape_html($key.' => `'.self::print_value_by_type($val).'`');
						} //end foreach
						$log .= '<br>'.'@COMMAND-PARAMS:&nbsp;{ '.implode(', ', $tmp_params).' }';
						$tmp_params = array();
					} elseif((string)$tmp_arr['command'] != '') {
						$log .= '<br>'.Smart::nl_2_br(Smart::escape_html(str_replace(array("\r\n", "\r", "\t"), array("\n", "\n", ' '), $tmp_arr['command'])));
					} //end if
					$log .= '</div>';
					//--
					break;
				case 'open-close':
					//--
					$tmp_color = '#4285F4';
					//--
					$log .= '<div class="smartframework_debugbar_inforow" style="font-size:12px; color:'.$tmp_color.';">';
					$log .= '<b>'.Smart::escape_html($tmp_arr['data']).'</b>';
					if((string)$tmp_arr['connection'] != '') {
						$log .= ' @ '.Smart::escape_html($tmp_arr['connection']);
					} //end if
					$log .= '</div>';
					//--
					break;
				case 'metainfo':
				default:
					//--
					$tmp_color = '#CCCCCC';
					//--
					$log .= '<div class="smartframework_debugbar_inforow" style="font-size:12px; color:'.$tmp_color.';">';
					$log .= '<b>'.Smart::escape_html($tmp_arr['data']).'</b>';
					if((string)$tmp_arr['connection'] != '') {
						$log .= ' @ '.Smart::escape_html($tmp_arr['connection']);
					} //end if
					$log .= '</div>';
					//--
			} //end switch
			//--
		} //end for
		//--
	} else {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 100px; text-align: center;"><font size="2"><b>N/A</b></font></div>';
		//--
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_optimizations($title, $optimizations_log) {
	//--
	$log = '';
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>'.Smart::escape_html($title).' :: OPTIMIZATIONS Log</b></font></div>';
	//--
	$max = Smart::array_size($optimizations_log);
	if(is_array($optimizations_log) AND ($max > 0)) {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Entries: <b>'.Smart::escape_html($max).'</b></div>';
		//--
		for($i=0; $i<$max; $i++) {
			//--
			$tmp_item = array(); // init
			$tmp_arr = (array) $optimizations_log[$i];
			//--
			$log .= '<div class="smartframework_debugbar_inforow" style="font-size:11px; color:#000000;">';
			$log .= '<b>'.Smart::escape_html((string)$tmp_arr['title']).'</b><br>';
			if(Smart::array_size($tmp_arr['data']) > 0) {
				for($j=0; $j<Smart::array_size($tmp_arr['data']); $j++) {
					$tmp_item = $tmp_arr['data'][$j];
					if(is_array($tmp_item)) {
						$tmp_line = '# '.$tmp_item['value'].' # '.$tmp_item['key'].' # '.$tmp_item['msg'];
						if($tmp_item['optimal'] === false) {
							$color = '#F5926C';
						} elseif($tmp_item['optimal'] === true) {
							$color = '#3FA325';
						} else {
							$color = '#555555';
						} //end if else
						$log .= '<span style="font-size:11px; color:'.$color.';">'.Smart::escape_html(str_replace(array("\r\n", "\r", "\t"), array("\n", "\n", ' '), $tmp_line)).'</span><br>';
					} //end if
				} //end for
			} else {
				$log .= '<span style="font-size:11px; color:#333333;">N/A</span>';
			} //end if else
			$log .= '</div>';
			//--
		} //end for
		//--
	} else {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 100px; text-align: center;"><font size="2"><b>N/A</b></font></div>';
		//--
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_extra($title, $extra_log) {
	//--
	$log = '';
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>'.Smart::escape_html($title).' :: EXTRA Log</b></font></div>';
	//--
	$max = Smart::array_size($extra_log);
	if(is_array($extra_log) AND ($max > 0)) {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Entries: <b>'.Smart::escape_html($max).'</b></div>';
		//--
		for($i=0; $i<$max; $i++) {
			//--
			$tmp_arr = (array) $extra_log[$i];
			//--
			$log .= '<div class="smartframework_debugbar_inforow" style="font-size:11px; color:#000000;">';
			$log .= '<b>'.Smart::escape_html((string)$tmp_arr['title']).'</b><br>';
			$log .= '<pre style="font-size:11px; color:#000000;">'.Smart::escape_html(str_replace(array("\r\n", "\r", "\t"), array("\n", "\n", ' '), trim((string)$tmp_arr['data']))).'</pre>';
			$log .= '</div>';
			//--
		} //end for
		//--
	} else {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 100px; text-align: center;"><font size="2"><b>N/A</b></font></div>';
		//--
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_log_modules($title, $modules_log) {
	//--
	$log = '';
	//--
	$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_head"><font size="4"><b>'.Smart::escape_html($title).' :: MODULE Log</b></font></div>';
	//--
	$max = Smart::array_size($modules_log);
	if(is_array($modules_log) AND ($max > 0)) {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_highlight" style="width:450px;">Total Entries: <b>'.Smart::escape_html($max).'</b></div>';
		//--
		for($i=0; $i<$max; $i++) {
			//--
			$tmp_arr = (array) $modules_log[$i];
			//--
			$log .= '<div class="smartframework_debugbar_inforow" style="font-size:11px; color:#000000;">';
			$log .= '<b>'.Smart::escape_html((string)$tmp_arr['title']).'</b><br>';
			$log .= '<pre style="font-size:11px; color:#000000;">'.Smart::escape_html(str_replace(array("\r\n", "\r", "\t"), array("\n", "\n", ' '), trim((string)$tmp_arr['data']))).'</pre>';
			$log .= '</div>';
			//--
		} //end for
		//--
	} else {
		//--
		$log .= '<div class="smartframework_debugbar_status smartframework_debugbar_status_warn" style="width: 100px; text-align: center;"><font size="2"><b>N/A</b></font></div>';
		//--
	} //end if
	//--
	return $log;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function print_value_by_type($value) {
	//--
	if($value === null) {
		$value = 'NULL (null)';
	} elseif($value === false) {
		$value = 'FALSE (bool)';
	} elseif($value === true) {
		$value = 'TRUE (bool)';
	} elseif($value === 0) {
		$value = '0 (zero)';
	} elseif($value === '') {
		$value = '`` (empty string)';
	} elseif(is_array($value)) {
		$value = (string) print_r($value,1);
	} elseif(is_object($value)) {
		$value = '[!OBJECT!]';
	} //end if else
	//--
	return (string) $value;
	//--
} //END FUNCTION
//==================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>