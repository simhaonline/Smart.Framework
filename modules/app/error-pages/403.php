<?php
// [CUSTOM 403 Status Code Page]
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Function: Custom 403 Answer (can be customized on your needs ...)
 *
 * @access 		private
 * @internal
 *
 */
function custom_http_message_403_forbidden($y_message, $y_html_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 403 Forbidden', $y_message, $y_html_message);
	//--
} //END FUNCTION

// end of php code
?>