<?php
// [SmartFramework / ERRORS MANAGEMENT]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// this should be loaded from app web root only

// ===== NOTICE =====
//	* NO VARIABLES SHOULD BE DEFINED IN THIS FILE BECAUSE IS LOADED BEFORE GET/POST AND CAN CAUSE SECURITY ISSUES
//	* ONLY CONSTANTS CAN BE DEFINED HERE
//	* FOR ERRORS WE TRY TO USE htmlspecialchars() with ISO-8859-1 encoding
//===================

// ALL ERRORS WILL BE LOGGED TO A LOG FILE: SMART_ERROR_LOGDIR/SMART_ERROR_LOGFILE defined below

//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
//####################

//--
if(defined('SMART_ERROR_LOG_MANAGEMENT')) {
	die('SmartFramework / Errors Management already loaded ...'); // avoid load more than once
} //end if
//--
define('SMART_ERROR_LOG_MANAGEMENT', 'SET');
//--
if(!defined('SMART_ERROR_HANDLER')) {
	die('A required INIT constant has not been defined: SMART_ERROR_HANDLER');
} //end if
//--
if(defined('SMART_ERROR_LOGDIR')) {
	die('SMART_ERROR_LOGDIR cannot be defined outside ERROR HANDLER');
} //end if
define('SMART_ERROR_LOGDIR', 'tmp/logs/'); // Error Handler log folder: the phperrors-Y-m-d.log file will be generated into this folder ; it must be .htaccess protected
//--
if(defined('SMART_ERROR_AREA')) { // display this error area
	die('SMART_ERROR_AREA cannot be defined outside ERROR HANDLER');
} //end if
if(defined('SMART_ERROR_LOGFILE')) { // for 'log' or 'off' the errors will be registered into this local error log file
	die('SMART_ERROR_LOGFILE cannot be defined outside ERROR HANDLER');
} //end if
if(SMART_FRAMEWORK_ADMIN_AREA === true) {
	define('SMART_ERROR_AREA', 'ADM');
	define('SMART_ERROR_LOGFILE', 'phperrors-adm-'.date('Y-m-d@H').'.log');
} else {
	define('SMART_ERROR_AREA', 'IDX');
	define('SMART_ERROR_LOGFILE', 'phperrors-idx-'.date('Y-m-d@H').'.log');
} //end if else
//--

//==
if(((string)SMART_ERROR_HANDLER == 'log') AND ((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes')) { // log :: hide errors and just log them
	ini_set('display_startup_errors', '0');
	ini_set('display_errors', '0');
} else { // dev :: display errors + log them
	ini_set('display_startup_errors', '1');
	ini_set('display_errors', '1');
} //end if else
ini_set('track_errors', '0');
//==
// PHP 5.4 or later: E_ALL & ~E_NOTICE & ~E_STRICT :: E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED); // error reporting for display only
set_error_handler(function($errno, $errstr, $errfile, $errline) {
	//--
	global $smart_____framework_____last__error;
	//--
	if(((string)SMART_ERROR_HANDLER == 'log') AND ((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes')) {
		$smart_____framework_____last__error = ''; // hide errors if explicit set so (make sense in production environments)
	} //end if
	//-- The following error types cannot be handled with a user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called : http://php.net/manual/en/function.set-error-handler.php
	$app_halted = '';
	$is_supressed = false;
	switch($errno) { // friendly err names
		case E_NOTICE:
			$ferr = 'NOTICE';
			if(0 == error_reporting()) { // fix: don't log E_NOTICE from @functions
				$is_supressed = true;
			} //end if
			break;
		case E_USER_NOTICE:
			$ferr = 'APP-NOTICE';
			break;
		case E_WARNING:
			$ferr = 'WARNING';
			if(0 == error_reporting()) { // fix: don't log E_WARNING from @functions
				$is_supressed = true;
			} //end if
			break;
		case E_USER_WARNING:
			$ferr = 'APP-WARNING';
			break;
		case E_ERROR:
			$app_halted = ' :: Execution FAILED !';
			$ferr = 'ERROR';
			break;
		case E_USER_ERROR:
			$app_halted = ' :: Execution Halted !';
			$ferr = 'APP-ERROR';
			break;
		default:
			$ferr = 'OTHER';
	} //end switch
	//--
	if(((string)SMART_ERROR_HANDLER != 'log') OR ((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes')) {
		$is_supressed = false;
	} //end if
	if(!defined('SMART_ERROR_SILENCE_WARNS_NOTICE')) { // to silence warnings and notices from logs this must be set explicit in init.php as: define('SMART_ERROR_SILENCE_WARNS_NOTICE', true); // Error Handler silence warnings and notices log (available just for SMART_ERROR_HANDLER=log mode)
		$is_supressed = false;
	} //end if
	//--
	if($is_supressed !== true) {
		if((is_dir(SMART_ERROR_LOGDIR)) && (is_writable(SMART_ERROR_LOGDIR))) {
			@file_put_contents(
				SMART_ERROR_LOGDIR.SMART_ERROR_LOGFILE,
				"\n".'===== '.date('Y-m-d H:i:s O')."\n".'PHP '.PHP_VERSION.' [SMART-ERR-HANDLER] #'.$errno.' ['.$ferr.']'.$app_halted."\n".'URI: ['.SMART_ERROR_AREA.'] @ '.$_SERVER['REQUEST_URI']."\n".'Script: '.$errfile."\n".'Line number: '.$errline."\n".$errstr."\n".'==================================='."\n\n",
				FILE_APPEND | LOCK_EX
			);
		} //end if
	} //end if
	//--
	if($errno === E_USER_ERROR) { // this is necessary just for E_USER_ERROR is used just for Exceptions and all other PHP errors are FATAL and will stop the execution ; For WARNING / NOTICE type errors we just want to log them, not to stop the execution !
		//--
		$message = 'Server Script Execution Halted.'."\n".'See the App Error Log for details';
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			$message .= ':'."\n".SMART_ERROR_LOGDIR.SMART_ERROR_LOGFILE;
		} else {
			$message .= '.';
		} //end if
		//--
		if(!headers_sent()) {
			@http_response_code(500); // try, if not headers send
		} //end if

		die('<!-- Smart Error Reporting / Smart Error Handler --><div align="center"><div style="width:548px; border: 1px solid #CCCCCC; margin-top:10px; margin-bottom:10px;"><table align="center" cellpadding="4" style="max-width:540px;"><tr valign="top"><td width="32"><img src="'.smart__framework__err__handler__get__basepath().'lib/framework/img/sign-crit-error.svg" alt="[!]" title="[!]"></td><td>&nbsp;</td><td><b>'.'Application Runtime Error @ '.SMART_ERROR_AREA.' [#'.$errno.']:<br>'.'</b><i>'.nl2br(htmlspecialchars((string)$message, ENT_HTML401 | ENT_COMPAT | ENT_SUBSTITUTE, 'ISO-8859-1'),false).'</i></td></tr></table></div><br><div style="width:550px; color:#778899; text-align:justify;"></div>'.$smart_____framework_____last__error.'</div>');
		//--
	} //end if else
	//--
}, E_ALL & ~E_NOTICE); // error reporting for logging
//==
set_exception_handler(function($exception) { // no type for EXCEPTION to be PHP 7 compatible
	//--
	//print_r($exception);
	//print_r($exception->getTrace());
	//--
	$message = $exception->getMessage();
	$details = '#'.$exception->getLine().' @ '.$exception->getFile();
	$exid = sha1('ExceptionID:'.$message.'/'.$details);
	//--
	if(is_array($exception->getTrace())) {
		$arr = (array) $exception->getTrace();
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
			//--
			$details .= "\n".print_r($arr,1);
			//--
		} else {
			//--
			for($i=0; $i<2; $i++) { // trace just 2 levels
				$details .= "\n".'  ----- Line #'.$arr[$i]['line'].' @ Class:['.$arr[$i]['class'].'] '.$arr[$i]['type'].' Function:['.$arr[$i]['function'].'] | File: '.$arr[$i]['file'];
				$details .= "\n".'  ----- Args * '.print_r($arr[$i]['args'],1);
			} //end for
			//--
		} //end if else
	} //end if
	//--
	@trigger_error('***** EXCEPTION ***** [#'.$exid.']:'."\n"."\n".'Error-Message: '.$message."\n".$details, E_USER_ERROR); // log the exception as ERROR
	//-- below code would be executed only if E_USER_ERROR fails to stop the execution
	if(!headers_sent()) {
		@http_response_code(500); // try, if not headers send
	} //end if
	die('Execution Halted. Application Level Exception. See the App Error Log for more details.');
	//--
});
//==
ini_set('ignore_repeated_source', '0'); // do not ignore repeated errors if in different files
if(((string)SMART_ERROR_HANDLER == 'log') AND ((string)SMART_FRAMEWORK_DEBUG_MODE != 'yes')) {
	ini_set('ignore_repeated_errors', '1'); // ignore repeated errors in the same file on the same line
	ini_set('log_errors_max_len', 2048); // max size of one error to log 2k (in production environments this is costly)
} else { // dev
	ini_set('ignore_repeated_errors', '0'); // do not ignore repeated errors
	ini_set('error_prepend_string', '<style type="text/css">* { font-family: arial,sans-serif; font-smooth: always; }</style> &nbsp; <font size="7" color="#4E5A92"><b>Code Execution ERROR <img src="'.smart__framework__err__handler__get__basepath().'lib/framework/img/sign-crit-error.svg"> PHP '.PHP_VERSION.' <img width="48" align="right" src="'.smart__framework__err__handler__get__basepath().'lib/framework/img/php-logo.svg"></b></font><div><hr size="1"><pre>');
	ini_set('error_append_string', '</pre></div><br><div>'.'<small>'.date('Y-m-d H:i:s O').'</small>'.'<hr size="1"></div><span title="Powered by Smart.Framework"><img src="'.smart__framework__err__handler__get__basepath().'lib/framework/img/powered_by_smart_framework.png"></span><span title="PHP Version: '.PHP_VERSION.'"><img src="'.smart__framework__err__handler__get__basepath().'lib/framework/img/powered_by_php.png" align="right"></span>');
	ini_set('log_errors_max_len', 16384); // max size of one error to log 16k
} //end if else
ini_set('html_errors', '0'); // display errors in TEXT format
ini_set('log_errors', '1'); // log always the errors
ini_set('error_log', (string)SMART_ERROR_LOGDIR.SMART_ERROR_LOGFILE); // error log file
//==
function smart__framework__err__handler__get__basepath() {
	$imgprefix = (string) dirname((string)$_SERVER['SCRIPT_NAME']);
	if(((string)$imgprefix == '') || ((string)$imgprefix == '/') || ((string)$imgprefix == '\\') || ((string)$imgprefix == '.')) {
		$imgprefix = '';
	} else {
		$imgprefix .= '/';
	} //end if
	return (string) $imgprefix;
} //END FUNCTION
//==

// end of php code
?>