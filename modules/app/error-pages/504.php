<?php
// [CUSTOM 504 Status Code Page]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Function: Custom 504 Answer (can be customized on your needs ...)
 *
 * @access 		private
 * @internal
 *
 */
function custom_http_message_504_gatewaytimeout($y_message, $y_html_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 504 Gateway Timeout', $y_message, $y_html_message);
	//--
} //END FUNCTION

// end of php code
?>