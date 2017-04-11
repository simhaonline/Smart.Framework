<?php
// [CUSTOM 400 Status Code Page]
// v.3.1.2 r.2017.04.11 / smart.framework.v.3.1

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
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
function custom_http_message_400_badrequest($y_message, $y_extra_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 400 Bad Request', $y_message, $y_extra_message);
	//--
} //END FUNCTION

// end of php code
?>