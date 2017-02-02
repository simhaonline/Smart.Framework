<?php
// [CUSTOM 404 Status Code Page]
// v.2.3.7.6 r.2017.02.02 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Function: Custom 404 Answer (can be customized on your needs ...)
 *
 * @access 		private
 * @internal
 *
 */
function custom_http_message_404_notfound($y_message, $y_extra_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 404 Not Found', $y_message, $y_extra_message);
	//--
} //END FUNCTION

// end of php code
?>