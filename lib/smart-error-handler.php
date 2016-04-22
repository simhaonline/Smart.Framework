<?php
// [SmartFramework / ERRORS MANAGEMENT]
// this should be loaded from app web root only
// v.2.3.1.8 r.2016.04.22 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

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
if(defined('SMART_ERROR_LOGFILE')) { // for 'log' or 'off' the errors will be registered into this local error log file
	die('SMART_ERROR_LOGFILE cannot be defined outside ERROR HANDLER');
} //end if
if(SMART_FRAMEWORK_ADMIN_AREA === true) {
	define('SMART_ERROR_LOGFILE', 'phperrors-adm-'.date('Y-m-d@H').'.log');
} else {
	define('SMART_ERROR_LOGFILE', 'phperrors-idx-'.date('Y-m-d@H').'.log');
} //end if else
//--

//==
if((string)SMART_ERROR_HANDLER == 'off') {
	ini_set('display_errors', '0');	// hide runtime errors
} else {
	ini_set('display_errors', '1');	// display runtime errors
} //end if else
//==
// PHP 5.4 or later: E_ALL & ~E_NOTICE & ~E_STRICT :: E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
//==
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED); // error reporting
//==
set_error_handler(function($errno, $errstr, $errfile, $errline) {
	//--
	global $smart_____framework_____last__error;
	//-- The following error types cannot be handled with a user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, and most of E_STRICT raised in the file where set_error_handler() is called : http://php.net/manual/en/function.set-error-handler.php
	$app_halted = '';
	switch($errno) { // friendly err names
		case E_NOTICE:
			$ferr = 'NOTICE';
			break;
		case E_USER_NOTICE:
			$ferr = 'APP-NOTICE';
			break;
		case E_WARNING:
			$ferr = 'WARNING';
			break;
		case E_USER_WARNING:
			$ferr = 'APP-WARNING';
			break;
		case E_USER_ERROR:
			$app_halted = ' :: Execution Halted !';
			$ferr = 'APP-ERROR';
			break;
		default:
			$ferr = 'OTHER';
	} //end switch
	//--
	$msg = "\n".'===== '.date('Y-m-d H:i:s O')."\n".'PHP [SMART-ERR-HANDLER] #'.$errno.' ['.$ferr.']'.$app_halted."\n".'URI: '.$_SERVER['REQUEST_URI']."\n".'Script: '.$errfile."\n".'Line number: '.$errline."\n".$errstr."\n".'==================================='."\n\n";
	//--
	if((is_dir(SMART_ERROR_LOGDIR)) && (is_writable(SMART_ERROR_LOGDIR))) {
		@file_put_contents(SMART_ERROR_LOGDIR.SMART_ERROR_LOGFILE, $msg, FILE_APPEND | LOCK_EX);
	} //end if
	//--
	if($errno === E_USER_ERROR) { // this is necessary just for E_USER_ERROR is used just for Exceptions and all other PHP errors are FATAL and will stop the execution ; For WARNING / NOTICE type errors we just want to log them, not to stop the execution !
		//--
		if((string)SMART_ERROR_HANDLER == 'php') {
			$message = trim($msg);
		} else { // log
			$message = 'Server Script Execution Halted.'."\n".'See the App Error Log for details.';
		} //end if
		//--
		if(!headers_sent()) {
			@http_response_code(500); // try, if not headers send
		} //end if
		die('<!-- Smart Error Reporting / Smart Error Handler --><div align="center"><div style="width:548px; border: 1px solid #CCCCCC; margin-top:10px; margin-bottom:10px;"><table align="center" cellpadding="4" style="max-width:540px;"><tr valign="top"><td width="32"><img src="lib/framework/img/sign_error.png" alt="[!]" title="[!]"></td><td>&nbsp;</td><td><b>'.'Application Runtime Error [#'.$errno.']:<br>'.'</b><i>'.nl2br(htmlspecialchars($message, ENT_HTML401 | ENT_COMPAT | ENT_SUBSTITUTE, 'ISO-8859-1'),false).'</i></td></tr></table></div><br><div style="width:550px; color:#778899; text-align:justify;">'.$smart_____framework_____last__error.'</div></div>');
		//--
	} else {
		//--
		if((string)SMART_ERROR_HANDLER == 'php') {
			echo '<pre style="background: #ECECEC; border: 1px solid #CCCCCC; margin: 5px; padding: 3px; font-size: 11px;" title="ERROR-HANDLER: PHP">'.htmlspecialchars(trim($msg), ENT_HTML401 | ENT_COMPAT | ENT_SUBSTITUTE, 'ISO-8859-1').'</pre>';
		} //end if else
		//--
	} //end if else
	//--
}, E_ALL & ~E_NOTICE);
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
	@trigger_error('***** EXCEPTION ***** [#'.$exid.']:'."\n".'Error-Message: '.$message."\n".$details, E_USER_ERROR); // log the exception as ERROR
	//-- below code would be executed only if E_USER_ERROR fails to stop the execution
	if(!headers_sent()) {
		@http_response_code(500); // try, if not headers send
	} //end if
	die('Execution Halted. Application Level Exception. See the App Error Log for more details.');
	//--
});
//==
if(((string)SMART_ERROR_HANDLER == 'log') OR ((string)SMART_ERROR_HANDLER == 'off')) {
	ini_set('html_errors', '0');
	ini_set('log_errors', '1');
	ini_set('log_errors_max_len', 2048);
	ini_set('error_log', SMART_ERROR_LOGDIR.SMART_ERROR_LOGFILE); // record them to a log
} //end if
//==

// end of php code
?>