<?php
// [CUSTOM 503 Status Code Page]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Function: Custom 503 Answer (can be customized on your needs ...)
 *
 * @access 		private
 * @internal
 *
 */
function custom_http_message_503_serviceunavailable($y_message, $y_html_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 503 Service Unavailable', $y_message, $y_html_message);
	//--
} //END FUNCTION

// end of php code
