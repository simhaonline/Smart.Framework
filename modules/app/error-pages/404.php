<?php
// [CUSTOM 404 Status Code Page]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
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
function custom_http_message_404_notfound($y_message, $y_html_message='') {
	/*
	//-- This is a basic implementation
	return SmartComponents::http_error_message('*Custom* 404 Not Found', $y_message, $y_html_message);
	//--
	*/
	//-- This is a more advanced implementation
	$controller = new \CustomErr404(
		'modules/mod-samples/',
		'samples.404',
		'samples.404',
		(SMART_FRAMEWORK_ADMIN_AREA === true) ? 'admin' : 'index' // if not admin, hardcoded to index
	);
	//--
	return $controller->outputErrorPage($y_message, $y_html_message);
	//--
} //END FUNCTION

/**
 * Class: Custom 404 Answer (used for the advanced implementation)
 *
 * @access 		private
 * @internal
 *
 */
class CustomErr404 extends \SmartModExtLib\Samples\ErrorXxx {
	protected $errcode = 404;
	protected $errtext = 'Not Found';
} //END CLASS

// end of php code
?>