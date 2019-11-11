<?php
// [CUSTOM 400 Status Code Page]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Function: Custom 400 Answer (can be customized on your needs ...)
 *
 * @access 		private
 * @internal
 *
 */
function custom_http_message_400_badrequest($y_message, $y_html_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 400 Bad Request', $y_message, $y_html_message);
	//--
} //END FUNCTION

// end of php code
?>