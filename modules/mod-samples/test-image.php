<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/TestImage
// Route: ?/page/samples.test-image (?page=samples.test-image)
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// This sample controller contains 2 different methods for the same thing: generate an image
// for INDEX area it works with the framework output buffering (more simple and can control the output in details ...) ; see below sample in SmartAppIndexController
// for ADMIN area it does direct output (more complicated, needs to implement all the events, status codes, output headers) ; see below sample in SmartAppAdminController

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

if(SMART_FRAMEWORK_ADMIN_AREA === true) {
	define('SMART_APP_MODULE_DIRECT_OUTPUT', true); // for admin area do direct output
} //end if

/**
 * Index Controller (output buffering, using framework controlled environment)
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		// this is for INDEX area ; it will use framework buffered output

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetCfg('rawpage', true); // set raw page (the output must not load a template ; in this case the output will be a binary image string !!)
		//--

		//--
		if((!function_exists('imagecreate')) AND (!function_exists('imagecreatetruecolor'))) {
			$this->PageViewSetErrorStatus(500, 'ERROR: PHP GD Extension is missing ...');
			return;
		} //end if
		//--

		//--
		//$this->PageViewResetRawHeaders();
		$this->PageViewSetRawHeader(
			'Z-Test-Image', 'This is an image' // just a sample dummy header entry
		);
		//--
	//	$this->PageViewSetCfg('c-control', 'public'); // cache control: private | public
	//	$this->PageViewSetCfg('expires', 3600); // set expiration in one hour in the future
	//	$this->PageViewSetCfg('modified', time()); // set modified now
		$this->PageViewSetCfg('rawmime', 'image/png'); // set mime type: Image / PNG
		$this->PageViewSetCfg('rawdisp', 'inline; filename="sample-image-'.time().'.png"'); // display inline and set the file name for the image
		//--

		//-- 1st level output buffering to avoid inject warnings / errors into PNG ... if any !!
		ob_start();
		//--
		if(function_exists('imagecreatetruecolor')) {
			$im = imagecreatetruecolor(280, 90);
		} else {
			$im = imagecreate(280, 90);
		} //end if else
		if(!$im) {
			Smart::log_warning('Cannot create the image in: '.__METHOD__);
			$this->PageViewSetErrorStatus(500, 'ERROR: Cannot create the sample image ...'); // set an error message for 500 http status
			return;
		} //end if
		//--
		$bgcolor = imagecolorallocate($im, 0xEC, 0xEC, 0xEC); // color for background
		imagefill($im, 0, 0, $bgcolor);
		$text_color = imagecolorallocate($im, 33, 33, 33); // color for text
		imagestring($im, 20, 5, 20, 'This is a sample PNG image ...', $text_color);
		imagestring($im, 20, 5, 45, 'Generated from PHP GD Library', $text_color);
		//--
		ob_end_clean(); // #end 1st level buffering
		//--

		//-- 2nd level buffering to get the image content
		ob_start();
		imagepng($im); // this function will echo, but in this controller type it is not allowed direct echo, so we need to capture the output of this echo !
		$png = ob_get_contents();
		imagedestroy($im);
		ob_end_clean();
		//--
		if((string)$png == '') {
			Smart::log_warning('Image is empty in: '.__METHOD__);
			$this->PageViewSetErrorStatus(500, 'ERROR: Image is Empty ...'); // set an error message for 500 http status
			return;
		} //end if
		//--

		//-- output the image via framework interface controller
		$this->PageViewSetVar(
			'main',
			(string) $png
		);
		//--

	} //END FUNCTION

} //END CLASS

/**
 * Admin Controller (direct output)
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAbstractAppController {

	public function Run() {

		// this is for ADMIN area ; it will use direct (unbuffered) output (aka echo)
		// this way is much more complicated ... comparing with the above (buffered example) as we need to control every portion of output
		// as you can see above we can control even the output of the image: if is empty (which is not possible here ...)

		//-- dissalow run this sample if not test mode enabled
		if(SMART_FRAMEWORK_TEST_MODE !== true) {
			if(!headers_sent()) {
				http_response_code(503);
			} //end if
			die(SmartComponents::http_message_503_serviceunavailable('ERROR: Test mode is disabled ...'));
			return;
		} //end if
		//--

		//-- because we do here direct output we need to set all the required headers
		SmartFrameworkRuntime::outputHttpHeadersNoCache();
		//--

		//--
		if((!function_exists('imagecreate')) AND (!function_exists('imagecreatetruecolor'))) {
			if(!headers_sent()) {
				http_response_code(500);
			} //end if
			die(SmartComponents::http_message_500_internalerror('ERROR: PHP GD Extension is missing ...'));
			return;
		} //end if
		//--

		//--
		header('Z-Test-Image: This is an image'); // just a sample dummy header entry
		//--
		ob_start(); // avoid echo warnings or errors !
		if(function_exists('imagecreatetruecolor')) {
			$im = imagecreatetruecolor(280, 90);
		} else {
			$im = imagecreate(280, 90);
		} //end if else
		ob_end_clean();
		if(!$im) {
			if(!headers_sent()) {
				http_response_code(500);
			} else {
				Smart::log_warning('Headers Already Sent before 500 in: '.__METHOD__);
			} //end if
			Smart::log_warning('Cannot create the image in: '.__METHOD__);
			die(SmartComponents::http_message_500_internalerror('ERROR: Cannot create the sample image ...'));
			return;
		} //end if
		//--
		ob_start(); // avoid echo warnings or errors !
		$bgcolor = imagecolorallocate($im, 0xEC, 0xEC, 0xEC); // color for background
		imagefill($im, 0, 0, $bgcolor);
		$text_color = imagecolorallocate($im, 33, 33, 33); // color for text
		imagestring($im, 20, 5, 20, 'This is a sample JPEG image ...', $text_color);
		imagestring($im, 20, 5, 45, 'Generated from PHP GD Library', $text_color);
		ob_end_clean();
		//--

		//--
	//	header('Expires: '.gmdate('D, d M Y H:i:s', (int)((int)time() + 3600)) .' GMT');
	//	header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
	//	header('Cache-Control: public, max-age=3600');
		header('Content-Type: image/jpeg');
		header('Content-Disposition: inline; filename="sample-image-'.time().'.jpg"');
		imagejpeg($im, null, 100); // direct echo
		//--
		ob_start(); // avoid echo warnings or errors !
		imagedestroy($im);
		ob_end_clean();
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>