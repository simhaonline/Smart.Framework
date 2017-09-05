<?php
// [CUSTOM 429 Status Code Page]
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Function: Custom 429 Answer (can be customized on your needs ...)
 *
 * @access 		private
 * @internal
 *
 */
function custom_http_message_429_toomanyrequests($y_message, $y_html_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 429 Too Many Requests', $y_message, $y_html_message);
	//--
} //END FUNCTION

// end of php code
?>