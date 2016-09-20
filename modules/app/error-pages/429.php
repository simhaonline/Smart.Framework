<?php
// [CUSTOM 429 Status Code Page]
// v.150619

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
function custom_http_message_429_toomanyrequests($y_message, $y_extra_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 429 Too Many Requests', $y_message, $y_extra_message);
	//--
} //END FUNCTION

// end of php code
?>