<?php
// [LIB - SmartFramework / Marker Templating]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.7 r.2017.02.22 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Marker Templating
// DEPENDS:
//	* Smart::
//	* SmartUnicode::
//	* SmartParser::
//	* SmartFileSystem::
//	* SmartFileSysUtils::
//	* SmartPersistentCache::
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

//#####
// INFO: This template engine is 100% safe against un-predictable recursion patterns !
// It does support: MARKERS, IF/ELSE, LOOP, INCLUDES syntax.
// It does not currently support: nested identic IF/ELSE or nested identic LOOP syntax.
// Nested identic IF/ELSE or nested identic LOOP syntax must be separed with unique numbers such as: (1), (2), ...
//#####
// Because the recursion patterns are un-predictable, as a template can be rendered in other template in controllers or libs,
// the str_replace() is used internally instead of strtr()
// but with a fix: will replace all values before assign as:
// [#### => (####- ; ####] => -####) ; [%%%% => (%%%%+/- ; %%%%] => -/+%%%%) ; [@@@@ -> (@@@@+/- ; @@@@] -> -/+@@@@]
// in order to protect against unwanted or un-predictable recursions / replacements
//#####

/**
 * Class: SmartMarkersTemplating - provides a very fast and low footprint templating system.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartFileSystem, SmartFileSysUtils
 * @version 	v.170301r3
 * @package 	Templating:Engines
 *
 */
final class SmartMarkersTemplating {

	// ::

	private static $MkTplVars = array(); // registry of template variables
	private static $MkTplFCount = array(); // counter to register how many times a template / sub-template file is read from filesystem (can be used for optimizations)
	private static $MkTplCache = array(); // registry of cached template data


//================================================================
// returns the prepared marker template from a string
// the replacement of sub-templates is made before injecting variables to avoid security issues
/**
 * Parse Marker Template (String Template ; no sub-templates are allowed as there is no possibility to set a relative path from where to get them)
 *
 * @param 	STRING 		$mtemplate 						:: The markers template (partial text/html + markers) ; Ex: '<span>[####MARKER1####]<br>[####MARKER2####], ...</span>'
 * @param 	ARRAY 		$y_arr_vars 					:: The associative array with the template variables ; mapping the array keys to template markers is case insensitive ; Ex: [ 'MARKER1' => 'Value1', 'marker2' => 'Value2', ..., 'MarkerN' => 100 ]
 * @param 	ENUM 		$y_ignore_if_empty 				:: 'yes' will ignore if markers template is empty ; 'no' will add a warning (default)
 *
 * @return 	STRING										:: The parsed template
 *
 */
public static function render_template($mtemplate, $y_arr_vars, $y_ignore_if_empty='no') {
	//--
	$y_ignore_if_empty = (string) $y_ignore_if_empty;
	//--
	$mtemplate = (string) trim((string)$mtemplate);
	//--
	if(((string)$y_ignore_if_empty != 'yes') AND ((string)$mtemplate == '')) {
		//--
		Smart::log_warning('Empty Markers-Template Content: '.print_r($y_arr_vars,1));
		return '{#### Empty Markers-Template Content. See the ErrorLog for Details. ####}';
		//--
	} //end if
	//--
	if(!is_array($y_arr_vars)) {
		$y_arr_vars = array();
		Smart::log_warning('Invalid Markers-Template Data-Set for Template: '.$mtemplate);
	} //end if
	//-- make all keys upper
	$y_arr_vars = (array) array_change_key_case((array)$y_arr_vars, CASE_UPPER);
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
			'title' => '[TPL-Render.START] :: Markers-Templating / Render ; Ignore if Empty: '.$y_ignore_if_empty,
			'data' => 'Content: '."\n".SmartParser::text_endpoints($mtemplate, 255)
		]);
	} //end if
	//-- avoid use the sub-templates array later than this point ... not needed and safer to unset
	if(array_key_exists('@SUB-TEMPLATES@', (array)$y_arr_vars)) {
		unset($y_arr_vars['@SUB-TEMPLATES@']);
	} //end if
	//--
	return (string) self::template_renderer((string)$mtemplate, (array)$y_arr_vars);
	//--
} //END FUNCTION
//================================================================


//================================================================
// it can *optional* use caching to avoid read a file template (or it's sub-templates) more than once per execution
// if using the cache the template and also sub-templates (if any) are cached internally to avoid re-read them from filesystem
// the replacement of sub-templates is made before injecting variables to avoid security issues
/**
 * Render Marker File Template (incl. Sub-Templates from Files if any)
 *
 * @param 	STRING 		$y_file_path 					:: The relative path to the file markers template (partial text/html + markers + *sub-templates*) ; if sub-templates are used, they will use the base path from this (main template) file ; Ex: views/my-template.inc.htm ; (partial text/html + markers) ; Ex (file content): '<span>[####MARKER1####]<br>[####MARKER2####], ...</span>'
 * @param 	ARRAY 		$y_arr_vars 					:: The associative array with the template variables ; mapping the array keys to template markers is case insensitive ; Ex: [ 'MARKER1' => 'Value1', 'marker2' => 'Value2', ..., 'MarkerN' => 100 ]
 * @param 	ENUM 		$y_use_caching 					:: 'yes' will cache the template (incl. sub-templates if any) into memory to avoid re-read them from file system (to be used if a template is used more than once per execution) ; 'no' means no caching is used (default)
 *
 * @return 	STRING										:: The parsed and rendered template
 *
 */
public static function render_file_template($y_file_path, $y_arr_vars, $y_use_caching='no') {
	//--
	$y_file_path = (string) $y_file_path;
	if(!is_file($y_file_path)) {
		Smart::log_warning('Invalid Markers-Template File: '.$y_file_path);
		return '{#### Invalid Markers-Template File. See the ErrorLog for Details. ####}';
	} //end if
	//--
	if(!is_array($y_arr_vars)) {
		$y_arr_vars = array();
		Smart::log_warning('Invalid Markers-File-Template Data-Set for Template file: '.$y_file_path);
	} //end if
	//--
	$y_use_caching = (string) $y_use_caching;
	//--
	$y_arr_vars = (array) array_change_key_case((array)$y_arr_vars, CASE_UPPER); // make all keys upper
	//--
	$mtemplate = (string) self::read_template_or_subtemplate_file((string)$y_file_path, (string)$y_use_caching);
	if((string)$mtemplate == '') {
		Smart::log_warning('Empty or Un-Readable Markers-Template File: '.$y_file_path);
		return '{#### Empty Markers-Template File. See the ErrorLog for Details. ####}';
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
			'title' => '[TPL-Render.START] :: Markers-Templating / File-Render: '.$y_file_path,
			'data' => 'Caching: '.$y_use_caching
		]);
	} //end if
	//--
	$arr_sub_templates = array();
	if(is_array($y_arr_vars['@SUB-TEMPLATES@'])) { // if supplied then use it (preffered), never mix supplied with detection else results would be unpredictable ...
		$arr_sub_templates = (array) $y_arr_vars['@SUB-TEMPLATES@'];
	} else { // if not supplied, try to detect
		$arr_sub_templates = (array) self::detect_subtemplates($mtemplate);
	} //end if else
	if(Smart::array_size($arr_sub_templates) > 0) {
		$tpl_basepath = (string) SmartFileSysUtils::add_dir_last_slash(SmartFileSysUtils::get_dir_from_path($y_file_path));
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
				'title' => '[TPL-Render.LOAD-SUBTEMPLATES] :: Markers-Templating / File-Render: '.$y_file_path.' ; Sub-Templates Load Base Path: '.$tpl_basepath,
				'data' => 'Sub-Templates: '."\n".print_r($arr_sub_templates,1)
			]);
		} //end if
		$mtemplate = (string) self::load_subtemplates((string)$y_use_caching, $tpl_basepath, $mtemplate, $arr_sub_templates); // load sub-templates before template processing and use caching also for sub-templates if set
	} //end if
	$arr_sub_templates = array();
	//-- avoid send the sub-templates array to the render_template() as the all sub-templates were processed here if any ; that function will try to detect only if used from separate context, this context will not allow re-detection as there would be no more
	if(array_key_exists('@SUB-TEMPLATES@', (array)$y_arr_vars)) {
		unset($y_arr_vars['@SUB-TEMPLATES@']);
	} //end if
	//--
	return (string) self::template_renderer((string)$mtemplate, (array)$y_arr_vars);
	//--
} //END FUNCTION
//================================================================


//================================================================
// it can *optional* use caching to avoid read the sub-templates (if any) more than once per execution
// if using the cache the sub-templates (if any) are cached internally to avoid re-read them from filesystem
// the replacement of sub-templates is made before injecting variables to avoid security issues
/**
 * Parse Mixed Marker Template (String Template + Sub-Templates from Files if any)
 * If no-subtemplates are available is better to use render_template() instead of this one.
 * !!! This is intended for very special usage (ex: main app template) since it does not support caching (and is not optimal to reload sub-templates several times) ... !!!
 *
 * @access 		private
 * @internal
 *
 * @param 	STRING 		$mtemplate 						:: The markers template (partial text/html + markers) ; Ex: '<span>[####MARKER1####]<br>[####MARKER2####], ...</span>'
 * @param 	ARRAY 		$y_arr_vars 					:: The associative array with the template variables ; mapping the array keys to template markers is case insensitive ; Ex: [ 'MARKER1' => 'Value1', 'marker2' => 'Value2', ..., 'MarkerN' => 100 ]
 * @param 	STRING 		$y_sub_templates_base_path 		:: The (relative) base path of sub-templates files if they are used (required to be non-empty)
 * @param 	ENUM 		$y_ignore_if_empty 				:: 'yes' will ignore if markers template is empty ; 'no' will add a warning (default)
 * @param 	ENUM 		$y_use_caching 					:: 'yes' will cache the sub-templates files (if any) into memory to avoid re-read them from file system (to be used if a sub-template is used more than once per execution) ; 'no' means no caching is used (default)
 *
 * @return 	STRING										:: The parsed template
 *
 */
public static function render_mixed_template($mtemplate, $y_arr_vars, $y_sub_templates_base_path, $y_ignore_if_empty='no', $y_use_caching='no') {
	//--
	$mtemplate = (string) trim((string)$mtemplate);
	//--
	if(((string)$y_ignore_if_empty != 'yes') AND ((string)$mtemplate == '')) {
		//--
		Smart::log_warning('Empty Mixed Markers-Template Content: '.print_r($y_arr_vars,1));
		return '{#### Empty Mixed Markers-Template Content. See the ErrorLog for Details. ####}';
		//--
	} //end if
	//--
	if(!is_array($y_arr_vars)) {
		$y_arr_vars = array();
		Smart::log_warning('Invalid Mixed Markers-Template Data-Set for Template: '.$mtemplate);
	} //end if
	//--
	if((string)$y_sub_templates_base_path == '') {
		Smart::log_warning('Empty Base Path for Mixed Markers-Template Content: '.$mtemplate);
		return '{#### Empty Base Path for Mixed Markers-Template Content. See the ErrorLog for Details. ####}';
	} //end if
	//-- make all keys upper
	$y_arr_vars = (array) array_change_key_case((array)$y_arr_vars, CASE_UPPER);
	//-- process sub-templates if any
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
			'title' => '[TPL-Render.START] :: Markers-Templating / Mixed Render ; Ignore if Empty: '.$y_ignore_if_empty.' ; Sub-Templates Load Base Path: '.$y_sub_templates_base_path,
			'data' => 'Content: '."\n".SmartParser::text_endpoints($mtemplate, 255)
		]);
	} //end if
	//--
	$arr_sub_templates = array();
	if(is_array($y_arr_vars['@SUB-TEMPLATES@'])) { // if supplied use it (preffered), never mix supplied with detection else results would be unpredictable ...
		$arr_sub_templates = (array) $y_arr_vars['@SUB-TEMPLATES@'];
	} else { // if not supplied, try to detect
		$arr_sub_templates = (array) self::detect_subtemplates($mtemplate);
	} //end if else
	if(Smart::array_size($arr_sub_templates) > 0) {
		$mtemplate = (string) self::load_subtemplates((string)$y_use_caching, (string)$y_sub_templates_base_path, (string)$mtemplate, (array)$arr_sub_templates); // load sub-templates before template processing
	} //end if
	$arr_sub_templates = array();
	//-- avoid use the sub-templates array later than this point ... not needed and safer to unset
	if(array_key_exists('@SUB-TEMPLATES@', (array)$y_arr_vars)) {
		unset($y_arr_vars['@SUB-TEMPLATES@']);
	} //end if
	//--
	return (string) self::template_renderer((string)$mtemplate, (array)$y_arr_vars);
	//--
} //END FUNCTION
//================================================================



//================================================================
// This function uses static read from filesystem and if (memory) persistent cache is available will cache it and all future reads until key expire will be done from memory instead of overloading the file system
/**
 * Read a Marker File Template from FileSystem or from Persistent (Memory) Cache if exists
 * !!! This is intended for very special usage ... !!! This is used automatically by the render_file_template() and used in combination with render_mixed_template() may produce the same results ... it make non-sense using it with render_template() as this should be used for internal (php) templates as all external templates should be loaded with render_file_template()
 *
 * @access 		private
 * @internal
 *
 * @param 	STRING 		$y_file_path 					:: The relative path to the file markers template
 *
 * @return 	STRING										:: The template string
 *
 */
public static function read_template_file($y_file_path) {
	//--
	$use_pcache = false;
	if((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes') {
		if(defined('SMART_SOFTWARE_MKTPL_PCACHETIME')) {
			if(is_int(SMART_SOFTWARE_MKTPL_PCACHETIME)) {
				if(((int)SMART_SOFTWARE_MKTPL_PCACHETIME >= 0) AND ((int)SMART_SOFTWARE_MKTPL_PCACHETIME <= 31622400)) { // 0 unlimited ; 1 sec .. 366 days
					$use_pcache = true;
				} //end if
			} //end if
		} //end if
	} //end if
	if(($use_pcache === true) AND SmartPersistentCache::isActive() AND SmartPersistentCache::isMemoryBased()) {
		$the_cache_key = 'tpl__'.Smart::safe_pathname((string)Smart::base_name((string)$y_file_path)).'__'.sha1((string)$y_file_path);
	} else {
		$the_cache_key = '';
	} //end if else
	//--
	$tpl = '';
	//--
	if((string)$the_cache_key != '') {
		if(SmartPersistentCache::keyExists('smart-markers-templating', (string)$the_cache_key)) {
			$tpl = (string) SmartPersistentCache::getKey('smart-markers-templating', (string)$the_cache_key);
			if((string)$tpl != '') {
				//Smart::log_notice('TPL found in cache: '.$y_file_path);
				return (string) $tpl; // return from persistent cache
			} //end if
		} //end if
	} //end if
	//--
	$tpl = (string) SmartFileSystem::staticread((string)$y_file_path);
	if((string)$the_cache_key != '') {
		if((string)$tpl != '') {
			SmartPersistentCache::setKey('smart-markers-templating', (string)$the_cache_key.'__path', (string)$y_file_path, (int)SMART_SOFTWARE_MKTPL_PCACHETIME); // set to persistent cache
			SmartPersistentCache::setKey('smart-markers-templating', (string)$the_cache_key, (string)$tpl, (int)SMART_SOFTWARE_MKTPL_PCACHETIME); // set to persistent cache
		} //end if
	} //end if
	//--
	return (string) $tpl; // return from fs read
	//--
} //END FUNCTION
//================================================================


//##### PRIVATES


//================================================================
// INFO: this renders the template except sub-templates loading which is managed separately
// $mtemplate must be STRING, non-empty
// $y_arr_vars must be a prepared ARRAY with all keys UPPERCASE
private static function template_renderer($mtemplate, $y_arr_vars) {
	//-- debug start
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		$bench = microtime(true);
	} //end if
	//-- if have syntax, process it
	if(self::have_syntax((string)$mtemplate) === true) {
		$mtemplate = (string) self::process_syntax((string)$mtemplate, (array)$y_arr_vars);
	} //end if
	//-- process markers until the last one detected
	foreach((array)$y_arr_vars as $key => $val) {
		if(self::have_marker((string)$mtemplate) === true) {
			if(!is_array($val)) { // fix
				$mtemplate = (string) self::replace_marker((string)$mtemplate, (string)$key, (string)$val);
			} //end if # else do not log, it may occur many times with the loop variables !!!
		} else {
			break;
		} //end if else
	} //end foreach
	//-- if any garbage markers are still detected log warning
	if(self::have_marker((string)$mtemplate) === true) {
		Smart::log_warning('Undefined Markers detected in Template:'."\n".self::log_template($mtemplate));
		$mtemplate = (string) str_replace(array('[####', '####]'), array('(####-', '-####)'), (string)$mtemplate); // finally protect against undefined variables
	} //end if
	//-- debug end
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		$bench = Smart::format_number_dec((float)(microtime(true) - (float)$bench), 9, '.', '');
		SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
			'title' => '[TPL-Parsing:Render.DONE] :: Markers-Templating / Processing ; Time = '.$bench.' sec.',
			'data' => 'Content: '."\n".SmartParser::text_endpoints($mtemplate, 255)
		]);
	} //end if
	//--
	/* Don't use this, it may affect <pre></pre> code ...
	if(SmartValidator::validate_html_or_xml_code((string)$mtemplate) === true) {
		$mtemplate = (string) preg_replace( // fix: remove multiple empty lines from HTML / XML
			'/^\s*[\r\n]/m',
			'',
			(string) $mtemplate
		);
	} //end if
	*/
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
// test if the template have at least one marker
private static function have_marker($mtemplate) {
	//--
	if(strpos((string)$mtemplate, '[####') !== false) {
		return true;
	} else {
		return false;
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
// test if the template have at least one syntax
private static function have_syntax($mtemplate) {
	//--
	if(strpos((string)$mtemplate, '[%%%%') !== false) {
		return true;
	} else {
		return false;
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
// test if the template have at least one sub-template
private static function have_subtemplate($mtemplate) {
	//--
	if(strpos((string)$mtemplate, '[@@@@') !== false) {
		return true;
	} else {
		return false;
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
// do replacements (and escapings) for one marker
/* {{{SYNC-MARKER-ALL-TEST-SEQUENCES}}}
<!-- INFO: The VALID Escaping Sequences for a Marker are all below ; If other escaping sequences are used or the escaping order is invalid, the Marker will not be detected and replaced ... -->
[####MARKER####]
[####MARKER|bool####]
[####MARKER|num####]
[####MARKER|htmid####]
[####MARKER|jsvar####]
[####MARKER|json####]
	[####MARKER|json|url####]
	[####MARKER|json|js####] 			** not necessary unless special purpose **
	[####MARKER|json|html####]
	[####MARKER|json|url|js####]
	[####MARKER|json|url|html####]
	[####MARKER|json|js|html####] 		** not necessary unless special purpose **
	[####MARKER|json|url|js|html####] 	** not necessary unless special purpose **
[####MARKER|url####]
[####MARKER|url|js####]
[####MARKER|url|html####]
[####MARKER|url|js|html####] 			** not necessary unless special purpose **
[####MARKER|js####]
[####MARKER|js|html####] 				** not necessary unless special purpose **
[####MARKER|html####]
*/
private static function replace_marker($mtemplate, $key, $val) {
	//--
	if(((string)$key != '') AND (preg_match('/^[A-Z0-9_\-\.]+$/', (string)$key)) AND (strpos((string)$mtemplate, '[####'.$key) !== false)) {
		//--
		$regex = '/\[####'.preg_quote((string)$key, '/').'(\|bool|\|num|\|htmid|\|jsvar|\|json)?(\|url)?(\|js)?(\|html)?(\|nl2br)?'.'####\]/';
		//--
		if((string)$val != '') {
			$val = (string) str_replace(
				array('[####',   '####]', '[%%%%',   '%%%%]', '[@@@@',   '@@@@]'),
				array('(####+', '+####)', '(%%%%+', '+%%%%)', '(@@@@+', '+@@@@)'), // the content is marked with +
				(string) $val
			); // protect against cascade / recursion / undefined variables - for content injections of: variables / syntax / sub-templates
		} //end if
		//--
		$mtemplate = (string) preg_replace_callback(
			(string) $regex,
			function($matches) use ($val) {
				//-- Format
				if((string)$matches[1] == '|num') { // Number
					$val = (string) (float) $val;
				} elseif((string)$matches[1] == '|bool') { // Boolean
					if($val) {
						$val = 'true';
					} else {
						$val = 'false';
					} //end if else
				} elseif((string)$matches[1] == '|htmid') { // HTML ID
					$val = (string) trim((string)preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$val));
				} elseif((string)$matches[1] == '|jsvar') { // JS Variable
					$val = (string) trim((string)preg_replace('/[^a-zA-Z0-9_]/', '', (string)$val));
				} elseif((string)$matches[1] == '|json') { // Json Data (!!! DO NOT ENCLOSE IN ' or " as it can contain them as well as it can be [] or {} ... this is pure JSON !!!)
					$val = (string) Smart::json_encode($val, false, false); // no pretty print, escape unicode as it is served inline !
				} //end if
				//-- Escape
				if((string)$matches[2] == '|url') {
					$val = (string) Smart::escape_url((string)$val);
				} //end if
				if((string)$matches[3] == '|js') {
					$val = (string) Smart::escape_js((string)$val);
				} //end if
				if((string)$matches[4] == '|html') {
					$val = (string) Smart::escape_html((string)$val);
				} //end if
				//--
				if((string)$matches[5] == '|nl2br') {
					$val = (string) Smart::nl_2_br((string)$val);
				} //end if
				//--
				return (string) $val;
				//--
			}, //end anonymous function
			(string) $mtemplate
		);
		//--
	} //end if
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
// process the template syntax: for now just LOOP and IF ...
private static function process_syntax($mtemplate, $y_arr_vars) {
	//-- zero priority: remove comments
	$mtemplate = (string) self::process_comments_syntax((string)$mtemplate);
	//-- 1st process IF and remove parts that will not be rendered
	$mtemplate = (string) self::process_if_syntax((string)$mtemplate, (array)$y_arr_vars); // this will auto-check if the template have any IF Syntax
	//-- 2nd process loop syntax
	$mtemplate = (string) self::process_loop_syntax((string)$mtemplate, (array)$y_arr_vars); // this will auto-check if the template have any LOOP Syntax
	//-- 3rd, process special characters: \r \n \t SPACE syntax
	$mtemplate = (string) self::process_rntspace_syntax($mtemplate);
	//-- 4th, finally if any garbage syntax is detected log warning
	if(self::have_syntax((string)$mtemplate) === true) {
		Smart::log_warning('Undefined Marker Syntax detected in Template:'."\n".self::log_template($mtemplate));
		$mtemplate = (string) str_replace(array('[%%%%', '%%%%]'), array('(%%%%-', '-%%%%)'), (string)$mtemplate); // finally protect against invalid loops (may have not bind to an existing var or invalid syntax)
	} //end if
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
// process the template COMMENT syntax
private static function process_comments_syntax($mtemplate) {
	//--
	if(strpos((string)$mtemplate, '[%%%%COMMENT') !== false) {
		//--
		//$pattern = '{\[%%%%COMMENT%%%%\](.*)?\[%%%%\/COMMENT%%%%\]}sU';
		$pattern = '{\s?\[%%%%COMMENT%%%%\](.*)?\[%%%%\/COMMENT%%%%\]\s?}sU'; // Fix: trim parts (#170301) [OK]
		$mtemplate = (string) preg_replace($pattern, '', (string)$mtemplate);
		//--
	} //end if
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
// process the template \r \n \t SPACE syntax to preserve special characters if required
private static function process_rntspace_syntax($mtemplate) {
	//--
	if(strpos((string)$mtemplate, '[%%%%|') !== false) {
		//--
		$mtemplate = (string) str_replace(
			[
				'[%%%%|R%%%%]',
				'[%%%%|N%%%%]',
				'[%%%%|T%%%%]',
				'[%%%%|SPACE%%%%]'
			],
			[
				"\r",
				"\n",
				"\t",
				' '
			],
			(string) $mtemplate
		);
		//--
	} //end if
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
// process the template IF syntax, nested ... on n+ levels
private static function process_if_syntax($mtemplate, $y_arr_vars, $y_context='') {
	//--
	if(strpos((string)$mtemplate, '[%%%%IF:') !== false) {
		//--
		$pattern = '{\[%%%%IF\:([a-zA-Z0-9_\-\.]*)\:(\=\=|\!\=|\<\=|\<|\>|\>\=|%|%\!|@\=|@\!)([#a-zA-Z0-9_\-\.\|]*)((\([0-9]*\))?%%)%%\](.*)?(\[%%%%ELSE\:\1\4%%\](.*)?)?\[%%%%\/IF\:\1\4%%\]}sU';
		$matches = array();
		preg_match_all((string)$pattern, (string)$mtemplate, $matches);
		//echo '<pre>'.htmlspecialchars(print_r($matches,1)).'</pre>'; die();
		//--
		list($orig_part, $var_part, $sign_not, $compare_val, $opt_uniqid, $opt_uniqix, $if_part, $else_all, $else_part) = (array) $matches;
		//--
		for($i=0; $i<Smart::array_size($orig_part); $i++) {
			//--
			$bind_var_key = (string) $var_part[$i];
			if(((string)$y_context != '') AND (substr($bind_var_key, strlen((string)$y_context), 1) == '.')) {
				$bind_var_key = (string) '$$$$'.$bind_var_key; // if context var appears as '$$$$CONTEXT.VAR123' instead of 'CONTEXT.VAR123'
			} //end if
			//--
			if(((string)$bind_var_key != '') AND (array_key_exists((string)$bind_var_key, (array)$y_arr_vars))) { // if the IF is binded to a non-empty KEY and an existing (which is mandatory to avoid mixing levels which will break this syntax in complex blocks !!!)
				//--
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
					if((string)$y_context != '') {
						self::$MkTplVars['%IF:'.$var_part[$i]][] = 'Processing IF Syntax in Context: '.$y_context;
					} else {
						self::$MkTplVars['%IF:'.$var_part[$i]][] = 'Processing IF Syntax';
					} //end if else
				} //end if
				//--
				$line = '';
				//-- Fix: trim parts (#170301) [OK]
				$if_part[$i] 	= (string) trim((string)$if_part[$i],   "\t\n\r\0\x0B");
				$else_part[$i] 	= (string) trim((string)$else_part[$i], "\t\n\r\0\x0B");
				//-- recursive process if in pieces of if or else
				if(strpos((string)$if_part[$i], '[%%%%IF:') !== false) {
					$if_part[$i] = (string) self::process_if_syntax((string)$if_part[$i], (array)$y_arr_vars, (string)$y_context);
				} //end if
				if(strpos((string)$else_part[$i], '[%%%%IF:') !== false) {
					$else_part[$i] = (string) self::process_if_syntax((string)$else_part[$i], (array)$y_arr_vars, (string)$y_context);
				} //end if
				//--
				if((substr((string)$compare_val[$i], 0, 4) == '####') AND (substr((string)$compare_val[$i], -4, 4) == '####')) { // compare with variable instead of static value
					$compare_val[$i] = (string) $y_arr_vars[str_replace('#', '', (string)$compare_val[$i])];
				} //end if
				//echo 'Context: '.$y_context."\n";
				//print_r($y_arr_vars);
				//-- do last if / else processing
				switch((string)$sign_not[$i]) {
					case '@=': // in array
						$tmp_compare_arr = (array) explode('|', (string)$compare_val[$i]);
						if(in_array((string)$y_arr_vars[(string)$bind_var_key], (array)$tmp_compare_arr)) { // if in array
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						$tmp_compare_arr = array();
						break;
					case '@!': // not in array
						$tmp_compare_arr = (array) explode('|', (string)$compare_val[$i]);
						if(!in_array((string)$y_arr_vars[(string)$bind_var_key], (array)$tmp_compare_arr)) { // if in array
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						$tmp_compare_arr = array();
						break;
					case '==':
						if((string)$y_arr_vars[(string)$bind_var_key] == (string)$compare_val[$i]) { // if variable evaluates to true keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					case '!=':
						if((string)$y_arr_vars[(string)$bind_var_key] != (string)$compare_val[$i]) { // if variable evaluates to true keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					case '<=':
						if((float)$y_arr_vars[(string)$bind_var_key] <= (float)$compare_val[$i]) { // if variable evaluates to true keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					case '<':
						if((float)$y_arr_vars[(string)$bind_var_key] < (float)$compare_val[$i]) { // if variable evaluates to true keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					case '>=':
						if((float)$y_arr_vars[(string)$bind_var_key] >= (float)$compare_val[$i]) { // if variable evaluates to true keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					case '>':
						if((float)$y_arr_vars[(string)$bind_var_key] > (float)$compare_val[$i]) { // if variable evaluates to true keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					case '%': // modulo (true/false)
						if((float)$y_arr_vars[(string)$bind_var_key] % (float)$compare_val[$i]) { // if variable evaluates to true keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					case '%!': // not modulo (false/true)
						if(!((float)$y_arr_vars[(string)$bind_var_key] % (float)$compare_val[$i])) { // if variable evaluates to false keep the inner content
							$line .= (string) $if_part[$i]; // if part
						} else {
							$line .= (string) $else_part[$i]; // else part ; if else not present will don't add = remove it !
						} //end if else
						break;
					default:
						// invalid syntax
						Smart::log_warning('Invalid Marker Template IF Syntax: ['.$sign_not[$i].'] / Template: '.$mtemplate);
				} //end switch
				//--
				$mtemplate = (string) str_replace((string)$orig_part[$i], (string)$line, (string)$mtemplate);
				//--
			} //end if else
			//--
		} //end for
		//--
	} //end if
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
// process the template LOOP syntax (added nested Level Loop (2nd++) ; will process IF syntax inside it also)
private static function process_loop_syntax($mtemplate, $y_arr_vars) {
	//--
	if(strpos((string)$mtemplate, '[%%%%LOOP:') !== false) {
		//--
		$pattern = '{\[%%%%LOOP\:([a-zA-Z0-9_\-\.]*)((\([0-9]*\))?%%)%%\](.*)?\[%%%%\/LOOP\:\1\2%%\]}sU';
		$matches = array();
		preg_match_all((string)$pattern, (string)$mtemplate, $matches);
		//echo '<pre>'.htmlspecialchars(print_r($matches,1)).'</pre>'; die();
		//--
		list($orig_part, $var_part, $opt_uniqid, $opt_uniqix, $loop_part) = (array) $matches;
		//--
		for($i=0; $i<Smart::array_size($orig_part); $i++) {
			//--
			$bind_var_key = (string) $var_part[$i];
			//--
			if(((string)$bind_var_key != '') AND (is_array($y_arr_vars[(string)$bind_var_key]))) { // if the LOOP is binded to an existing Array Variable and a non-empty KEY
				//--
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
					self::$MkTplVars['%LOOP:'.$bind_var_key][] = 'Processing LOOP Syntax: '.Smart::array_size($y_arr_vars[(string)$bind_var_key]);
				} //end if
				//--
				//$loop_orig = (string) rtrim((string)$loop_part[$i]);
				$loop_orig = (string) trim((string)$loop_part[$i], "\t\n\r\0\x0B"); // Fix: trim parts (#170301) [OK]
				//--
				$line = '';
				//--
				$arrtype = Smart::array_type_test($y_arr_vars[(string)$bind_var_key]); // 0: not an array ; 1: non-associative ; 2:associative
				//--
				if($arrtype === 1) { // 1: non-associative
					//--
					$the_max = Smart::array_size($y_arr_vars[(string)$bind_var_key]);
					//--
					for($j=0; $j<$the_max; $j++) {
						//-- operate on a copy of original
						$mks_line = (string) $loop_orig;
						//-- process IF inside LOOP for this context (the global context is evaluated prior as this function is called after process_if_syntax() in process_syntax() via render_template()
						$tmp_arr_context = array();
						if(strpos((string)$mks_line, '[%%%%IF:') !== false) {
							$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'._-MAXCOUNT-_')] = (string) $the_max;
							$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'._-ITERATOR-_')] = (string) $j;
							if(is_array($y_arr_vars[(string)$bind_var_key][$j])) {
								foreach($y_arr_vars[(string)$bind_var_key][$j] as $key => $val) {
									$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'.'.$key)] = $val;
								} //end foreach
							} else {
								$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'._-VAL-_')] = (string) $y_arr_vars[(string)$bind_var_key][$j];
							} //end if else
							$mks_line = (string) self::process_if_syntax(
								(string) $mks_line,
								(array) array_merge((array)$y_arr_vars, (array)$tmp_arr_context),
								(string) $bind_var_key // context
							);
						} //end if
						//-- process 2nd Level LOOP inside LOOP for non-Associative Array
						if((strpos((string)$mks_line, '[%%%%LOOP:') !== false) AND (is_array($y_arr_vars[(string)$bind_var_key][$j]))) {
							foreach($y_arr_vars[(string)$bind_var_key][$j] as $qk => $qv) {
								if(((strpos((string)$mks_line, '[%%%%LOOP:'.$bind_var_key.'.'.strtoupper((string)$qk).'%') !== false) OR (strpos((string)$mks_line, '[%%%%LOOP:'.$bind_var_key.'.'.strtoupper((string)$qk).'(') !== false)) AND (is_array($qv))) {
									//echo '***** ['.$bind_var_key.'.'.strtoupper((string)$qk).'] = '.print_r($qv,1)."\n\n";
									$mks_line = (string) self::process_loop_syntax(
										(string) $mks_line,
										[ $bind_var_key.'.'.strtoupper((string)$qk) => (array)$qv ]
									);
								} //end if
							} //end foreach
						} //end if
						//-- process the loop replacements
						$mks_line = (string) self::replace_marker(
							(string) $mks_line,
							(string) strtoupper($bind_var_key.'._-MAXCOUNT-_'),
							(string) $the_max
						);
						$mks_line = (string) self::replace_marker(
							(string) $mks_line,
							(string) strtoupper($bind_var_key.'._-ITERATOR-_'),
							(string) $j
						);
						if(is_array($y_arr_vars[(string)$bind_var_key][$j])) {
							foreach($y_arr_vars[(string)$bind_var_key][$j] as $key => $val) {
								$mks_line = (string) self::replace_marker(
									(string) $mks_line,
									(string) strtoupper($bind_var_key.'.'.$key),
									(string) $val
								);
							} //end for
						} else {
							$mks_line = (string) self::replace_marker(
								(string) $mks_line,
								(string) strtoupper($bind_var_key.'._-VAL-_'),
								(string) $y_arr_vars[(string)$bind_var_key][$j]
							);
						} //end if else
						//-- render
						$line .= (string) $mks_line;
						//--
					} //end for
					//--
				} elseif($arrtype === 2) { // 2: associative
					//--
					$j=0;
					$the_max = Smart::array_size($y_arr_vars[(string)$bind_var_key]);
					//--
					foreach($y_arr_vars[(string)$bind_var_key] as $zkey => $zval) {
						//-- operate on a copy of original
						$mks_line = (string) $loop_orig;
						//--
						$ziterator = $j;
						$j++;
						//-- process IF inside LOOP for this context (the global context is evaluated prior as this function is called after process_if_syntax() in process_syntax() via render_template()
						$tmp_arr_context = array();
						if(strpos((string)$mks_line, '[%%%%IF:') !== false) {
							$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'._-MAXCOUNT-_')] = (string) $the_max;
							$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'._-ITERATOR-_')] = (string) $ziterator;
							$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'._-KEY-_')] = (string) $zkey;
							if(is_array($zval)) {
								foreach($zval as $key => $val) {
									$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'.'.$key)] = $val;
								} //end foreach
							} else {
								$tmp_arr_context[strtoupper('$$$$'.$bind_var_key.'._-VAL-_')] = (string) $zval;
							} //end if else
							$mks_line = (string) self::process_if_syntax(
								(string) $mks_line,
								(array) array_merge((array)$y_arr_vars, (array)$tmp_arr_context),
								(string) $bind_var_key // context
							);
						} //end if
						//-- process 2nd Level LOOP inside LOOP for Associative Array
						if((strpos((string)$mks_line, '[%%%%LOOP:') !== false) AND (is_array($zval))) {
							if(((strpos((string)$mks_line, '[%%%%LOOP:'.$bind_var_key.'.'.strtoupper((string)$zkey).'%') !== false) OR (strpos((string)$mks_line, '[%%%%LOOP:'.$bind_var_key.'.'.strtoupper((string)$zkey).'(') !== false)) AND (is_array($zval))) {
								//echo '***** ['.$bind_var_key.'.'.strtoupper((string)$zkey).'] = '.print_r($zval,1)."\n\n";
								$mks_line = (string) self::process_loop_syntax(
									(string) $mks_line,
									[ $bind_var_key.'.'.strtoupper((string)$zkey) => (array)$zval ]
								);
							} //end if
						} //end if
						//-- process the loop replacements
						$mks_line = (string) self::replace_marker(
							(string) $mks_line,
							(string) strtoupper($bind_var_key.'._-MAXCOUNT-_'),
							(string) $the_max
						);
						$mks_line = (string) self::replace_marker(
							(string) $mks_line,
							(string) strtoupper($bind_var_key.'._-ITERATOR-_'),
							(string) $ziterator
						);
						$mks_line = (string) self::replace_marker(
							(string) $mks_line,
							(string) strtoupper($bind_var_key.'._-KEY-_'),
							(string) $zkey
						);
						if(is_array($zval)) {
							foreach($zval as $key => $val) {
								$mks_line = (string) self::replace_marker(
									(string) $mks_line,
									(string) strtoupper($bind_var_key.'.'.$key),
									(string) $val
								);
							} //end for
						} else {
							$mks_line = (string) self::replace_marker(
								(string) $mks_line,
								(string) strtoupper($bind_var_key.'._-VAL-_'),
								(string) $zval
							);
						} //end if else
						//-- render
						$line .= (string) $mks_line;
						//--
					} //end foreach
					//--
				} //end if else
				//--
				$mtemplate = (string) str_replace((string)$orig_part[$i], (string)$line, (string)$mtemplate);
				//--
			} //end if else
			//--
		} //end for
		//--
	} //end if
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
// detect marker sub-templates and returns an array with them
private static function detect_subtemplates($mtemplate) {
	//--
	$arr_detected_sub_templates = array();
	//--
	if(self::have_subtemplate((string)$mtemplate) === true) {
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			$bench = microtime(true);
		} //end if
		//--
		$arr_matched_sub_templates = array();
		preg_match_all('{\[@@@@SUB\-TEMPLATE:([a-zA-Z0-9_\-\.\/\!]*)@@@@\]}', (string)$mtemplate, $arr_matched_sub_templates);
		//print_r($arr_matched_sub_templates);
		//--
		if(Smart::array_size($arr_matched_sub_templates) > 0) {
			for($i=0; $i<Smart::array_size($arr_matched_sub_templates[1]); $i++) {
				if((string)$arr_matched_sub_templates[1][$i] != '') {
					if(self::have_subtemplate((string)$arr_matched_sub_templates[1][$i]) !== true) {
						$arr_detected_sub_templates[(string)$arr_matched_sub_templates[1][$i]] = '@'; // add detected sub-template only if it does not contain the sub-templates syntax to avoid unpredictable behaviours
					} //end if
				} //end if
			} //end for
		} //end if
		//--
		$arr_matched_sub_templates = array();
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			$bench = Smart::format_number_dec((float)(microtime(true) - (float)$bench), 9, '.', '');
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
				'title' => '[TPL-Parsing:Evaluate] :: Markers-Templating / Detecting Sub-Templates ; Time = '.$bench.' sec.',
				'data' => 'Sub-Templates Detected: '.print_r($arr_detected_sub_templates,1)
			]);
		} //end if
		//--
	} //end if
	//--
	return (array) $arr_detected_sub_templates;
	//--
} //END FUNCTION
//================================================================


//================================================================
// inject marker sub-templates
// max 3 levels: template -> sub-template -> sub-sub-template
// max 255 cycles overall: template + sub-templates + sub-sub-templates)
// returns the prepared marker template contents
private static function load_subtemplates($y_use_caching, $y_base_path, $mtemplate, $y_arr_vars_sub_templates, $cycles=0, $process_sub_sub_templates=true) {
	//--
	$y_use_caching = (string) $y_use_caching;
	$y_base_path = (string) $y_base_path;
	$mtemplate = (string) $mtemplate;
	$y_arr_vars_sub_templates = (array) $y_arr_vars_sub_templates;
	$cycles = (int) $cycles;
	//--
	if((string)$y_base_path == '') {
		Smart::log_warning('Marker Template Load Sub-Templates: INVALID Base Path (Empty) ... / Template: '.$mtemplate);
		return 'Marker Template Load Sub-Templates: INVALID Base Path (Empty). See the ErrorLog for Details.';
	} //end if
	//--
	if(Smart::array_size($y_arr_vars_sub_templates) > 0) {
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			$bench = microtime(true);
		} //end if
		//--
		foreach($y_arr_vars_sub_templates as $key => $val) {
			//--
			$key = (string) $key;
			$val = (string) $val;
			//--
			if(((string)$key != '') AND (strpos($key, '..') === false) AND (strpos($val, '..') === false) AND (preg_match('/^[a-zA-Z0-9_\-\.\/\!%]+$/', $key))) {
				//--
				if((string)$val == '') {
					//--
					$mtemplate = str_replace(
						'[@@@@SUB-TEMPLATE:'.$key.'@@@@]',
						'', // clear (this is required for the cases the sub-templates must not includded in some cases: a kind of IF syntax)
						(string) $mtemplate
					);
					//--
					if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
						SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
							'title' => '[TPL-Parsing:Load] :: Markers-Templating / Skipping Sub-Template File: Key='.$key.' ; *Path='.$val.' ; Cycle='.$cycles,
							'data' => 'Unset based on empty Path value ...'
						]);
					} //end if
					//--
				} else {
					//--
					if((substr($key, 0, 1) == '%') AND (substr($key, -1, 1) == '%')) { // variable, only can be set programatically, full path to the template file is specified
						if(substr($val, 0, 2) == '@/') { // use a path suffix relative path to parent template, starting with @/ ; otherwise the full relative path is expected
							$val = (string) SmartFileSysUtils::add_dir_last_slash((string)$y_base_path).substr($val, 2);
						} //end if
						$stpl_path = (string) $val;
					} elseif(strpos($key, '%') !== false) { // % is not valid in other circumstances
						Smart::log_warning('Invalid Markers-Sub-Template Syntax [%] as: '.$key);
						return 'Invalid Markers-Sub-Template Syntax. See the ErrorLog for Details.';
					} elseif((substr($key, 0, 1) == '!') AND (substr($key, -1, 1) == '!')) { // path override: use this relative path instead of parent relative referenced path ; Ex: [@@@@SUB-TEMPLATE:!etc/templates/default/js-base.inc.htm!@@@@]
						$stpl_path = (string) substr($key, 1, -1);
					} elseif(strpos($key, '!') !== false) { // ! is not valid in other circumstances
						Smart::log_warning('Invalid Markers-Sub-Template Syntax [!] as: '.$key);
						return 'Invalid Markers-Sub-Template Syntax. See the ErrorLog for Details.';
					} else {
						if((string)$val == '@') { // use the same dir as parent
							$val = (string) $y_base_path;
						} elseif(substr($val, 0, 2) == '@/') { // use a path suffix relative to parent template, starting with @/
							$val = (string) SmartFileSysUtils::add_dir_last_slash((string)$y_base_path).substr($val, 2);
						} //end if
						$stpl_path = (string) SmartFileSysUtils::add_dir_last_slash($val).$key;
					} //end if else
					//--
					if(!is_file((string)$stpl_path)) {
						Smart::log_warning('Invalid Markers-Sub-Template File: '.$stpl_path);
						return 'Invalid Markers-Sub-Template File. See the ErrorLog for Details.';
					} //end if
					//--
					$stemplate = (string) self::read_template_or_subtemplate_file((string)$stpl_path, (string)$y_use_caching); // read
					if($process_sub_sub_templates === true) {
						$arr_sub_sub_templates = (array) self::detect_subtemplates((string)$stemplate); // detect sub-sub templates
						$num_sub_sub_templates = Smart::array_size($arr_sub_sub_templates);
						if($num_sub_sub_templates > 0) {
							$stemplate = (string) self::load_subtemplates((string)$y_use_caching, $y_base_path, $stemplate, $arr_sub_sub_templates, $cycles, false); // this is level 3 !!
							$cycles += $num_sub_sub_templates;
						} //end if
					} //end if
					$stemplate = str_replace(array('[@@@@', '@@@@]'), array('(@@@@-', '-@@@@)'), (string)$stemplate); // protect against cascade recursion or undefined sub-templates
					$mtemplate = str_replace('[@@@@SUB-TEMPLATE:'.$key.'@@@@]', (string)$stemplate, (string)$mtemplate); // do replacements
					$arr_sub_sub_templates = array();
					$num_sub_sub_templates = 0;
					//--
					if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
						SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
							'title' => '[TPL-Parsing:Load] :: Markers-Templating / Loading Sub-Template File: Key='.$key.' ; Path='.$stpl_path.' ; Cycle='.$cycles,
							'data' => 'Content: '."\n".SmartParser::text_endpoints($stemplate, 255)
						]);
					} //end if
					//--
					$stemplate = '';
					//--
				} //end if else
				//--
			} else { // invalid key
				//--
				Smart::log_warning('Invalid Markers-Sub-Template Key: '.$key.' or Value: '.$val);
				//--
			} //end if else
			//--
			$cycles++;
			if($cycles > 255) { // protect against infinite loop, max 255 loops (incl. sub-sub templates) :: hard limit
				Smart::log_warning('Inclusion of the Sub-Template: '.$stpl_path.' failed as it overflows the maximum hard limit: only 255 loops (sub-templates) are allowed. Current Cycle is: #'.$cycles);
				break;
			} //end if
			//--
		} //end foreach
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			$bench = Smart::format_number_dec((float)(microtime(true) - (float)$bench), 9, '.', '');
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
				'title' => '[TPL-Parsing:Load.DONE] :: Markers-Templating / Loading Sub-Templates Completed ; Time = '.$bench.' sec.',
				'data' => 'Total Cycles: '.$cycles
			]);
		} //end if
		//--
	} //end if
	//--
	if(self::have_subtemplate((string)$mtemplate) === true) {
		Smart::log_warning('Undefined Marker Sub-Templates detected in Template:'."\n".self::log_template($mtemplate));
		$mtemplate = str_replace(array('[@@@@', '@@@@]'), array('(@@@@-', '-@@@@)'), (string)$mtemplate); // finally protect against undefined sub-templates
	} //end if
	//--
	return (string) $mtemplate;
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function read_template_or_subtemplate_file($y_file_path, $y_use_caching) {
	//--
	$y_file_path = (string) $y_file_path;
	//--
	$cached_key = 'read_template_or_subtemplate_file:'.$y_file_path;
	//--
	if(array_key_exists((string)$cached_key, (array)self::$MkTplCache)) {
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			self::$MkTplVars['@SUB-TEMPLATE:'.$y_file_path][] = 'Includding a Sub-Template from VCache';
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
				'title' => '[TPL-ReadFileTemplate-From-VCache] :: Markers-Templating / File-Read ; Serving from VCache the File Template: '.$y_file_path.' ; VCacheFlag: '.$y_use_caching,
				'data' => 'Content: '."\n".SmartParser::text_endpoints(self::$MkTplCache[(string)$cached_key], 255)
			]);
		} //end if
		//--
		return (string) self::$MkTplCache[(string)$cached_key];
		//--
	} //end if
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		self::$MkTplFCount[(string)$cached_key]++; // register to counter anytime is read from FileSystem
	} //end if
	//--
	if((string)$y_use_caching == 'yes') {
		//--
		self::$MkTplCache[(string)$cached_key] = (string) self::read_template_file($y_file_path);
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			self::$MkTplVars['@SUB-TEMPLATE:'.$y_file_path][] = 'Reading a Sub-Template from FS and REGISTER in VCache';
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
				'title' => '[TPL-ReadFileTemplate-From-FS-Register-In-VCache] :: Markers-Templating / Registering to VCache the File Template: '.$y_file_path.' ;',
				'data' => 'Content: '."\n".SmartParser::text_endpoints(self::$MkTplCache[(string)$cached_key], 255)
			]);
		} //end if
		//--
		return (string) self::$MkTplCache[(string)$cached_key];
		//--
	} else {
		//--
		$mtemplate = (string) self::read_template_file($y_file_path);
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			self::$MkTplVars['@SUB-TEMPLATE:'.$y_file_path][] = 'Reading a Sub-Template from FS  ; VCacheFlag: '.$y_use_caching;
			SmartFrameworkRegistry::setDebugMsg('extra', 'SMART-TEMPLATING', [
				'title' => '[TPL-ReadFileTemplate-From-FS] :: Markers-Templating / File-Read ; Serving from FS the File Template: '.$y_file_path.' ;',
				'data' => 'Content: '."\n".SmartParser::text_endpoints($mtemplate, 255)
			]);
		} //end if
		//--
		return (string) $mtemplate;
		//--
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function log_template($mtemplate) {
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		return (string) $mtemplate;
	} else {
		return (string) SmartUnicode::sub_str($mtemplate, 0, 255)."\n".'***** turn on Debugging to see more ... *****';
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 *
 * @access 		private
 * @internal
 *
 */
public static function registerOptimizationHintsToDebugLog() {
	//--
	if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
		//--
		$optim_msg = [];
		foreach(self::$MkTplFCount as $key => $val) {
			$key = (string) $key;
			if(strpos($key, 'debug') === false) { // avoid hints for debug templates / sub-templates
				$key = (array) explode(':', $key);
				$key = (string) $key[1];
				$val = (int) $val;
				if($val > 1) {
					$optim_msg[] = [
						'optimal' => false,
						'value' => (int) $val,
						'key' => (string) $key,
						'msg' => 'Optimization Hint: Set Caching Parameter for Rendering this Template to avoid multiple reads on FileSystem'
					];
				} else {
					$optim_msg[] = [
						'optimal' => true,
						'value' => (int) $val,
						'key' => (string) $key,
						'msg' => 'OK'
					];
				} //end if else
			} //end if
		} //end foreach
		SmartFrameworkRegistry::setDebugMsg('optimizations', '*SMART-CLASSES:OPTIMIZATION-HINTS*', [
			'title' => 'SmartMarkersTemplating // Optimization Hints @ Number of FileSystem Reads for current Template / Sub-Templates',
			'data' => (array) $optim_msg
		]);
		//--
		$optim_msg = [];
		foreach(self::$MkTplVars as $key => $val) {
			$counter = Smart::array_size($val);
			if($counter > 0) {

				$optim_msg[] = [
					'optimal' => null,
					'value' => (int) $counter,
					'key' => (string) $key,
					'msg' => (string) implode(' ; ', array_unique($val))
				];
			} //end if
		} //end foreach
		SmartFrameworkRegistry::setDebugMsg('optimizations', '*SMART-CLASSES:OPTIMIZATION-HINTS*', [
			'title' => 'SmartMarkersTemplating // Optimization Notices @ Rendering Details of current Template / Sub-Templates',
			'data' => (array) $optim_msg
		]);
		//--
		$optim_msg = [];
		//--
	} //end if
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 *
 * @access 		private
 * @internal
 *
 */
public static function registerInternalCacheToDebugLog() {
	//--
	if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			SmartFrameworkRegistry::setDebugMsg('extra', '***SMART-CLASSES:INTERNAL-CACHE***', [
				'title' => 'SmartMarkersTemplating // Internal Cache',
				'data' => 'Dump of Cached Templates / Sub-Templates:'."\n".print_r(self::$MkTplCache,1)
			]);
		} //end if
	} //end if
	//--
} //END FUNCTION
//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>