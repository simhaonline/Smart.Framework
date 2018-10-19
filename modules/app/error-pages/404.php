<?php
// [CUSTOM 404 Status Code Page]
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

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
/*
//This is a basic implementation
function custom_http_message_404_notfound($y_message, $y_html_message='') {
	//--
	return SmartComponents::http_error_message('*Custom* 404 Not Found', $y_message, $y_html_message);
	//--
} //END FUNCTION
*/
//This is a more advanced implementation
function custom_http_message_404_notfound($y_message, $y_html_message='') {
	//--
	$controller = new \CustomErr404(
		'index',
		'modules/mod-samples/',
		'index.php',
		(string) SmartUtils::get_server_current_path(),
		(string) SmartUtils::get_server_current_url(),
		'samples.404',
		'samples.404'
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