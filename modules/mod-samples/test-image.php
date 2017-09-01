<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/TestImage
// Route: ?/page/samples.benchmark (?page=samples.test-image)
// Author: unix-world.org
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
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
		if(SMART_FRAMEWORK_TEST_MODE !== true) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetCfg('rawpage', true); // set raw page (the output must not load a template ; in this case the output will be a binary image string !!)
		//--

		//--
		$this->PageViewSetRawHeader(
			'Z-Test-Image', 'This is an image' // just a sample dummy header entry
		);
		//--

		//--
		$this->PageViewSetCfg('rawmime', 'image/png'); // set mime type: Image / PNG
		$this->PageViewSetCfg('rawdisp', 'inline; filename="sample-image-'.time().'.png"'); // display inline and set the file name for the image
		//--

		//-- 1st level output buffering to avoid inject warnings / errors into PNG ... if any !!
		ob_start();
		//--
		$im = imagecreatetruecolor(320, 90);
		if(!$im) {
			Smart::log_warning('Cannot create the image in: '.__METHOD__);
			$this->PageViewSetErrorStatus(500, 'ERROR: Cannot create the sample image ...'); // set an error message for 500 http status
			return;
		} //end if
		//--
		$bgcolor = imagecolorallocate($im, 0xEC, 0xEC, 0xEC); // color for background
		imagefill($im, 0, 0, $bgcolor);
		$text_color = imagecolorallocate($im, 33, 33, 33); // color for text
		imagestring($im, 25, 25, 30, 'This is a sample PNG image ...', $text_color);
		imagestring($im, 25, 25, 50, 'Generated from PHP GD Library', $text_color);
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
				http_response_code(500);
			} //end if
			die(SmartComponents::http_message_500_internalerror('ERROR: Test mode is disabled ...'));
			return;
		} //end if
		//--

		//-- because we do here direct output we need to set all the required headers
		header('Cache-Control: no-cache'); // HTTP 1.1
		header('Pragma: no-cache'); // HTTP 1.0
		header('Expires: '.gmdate('D, d M Y', @strtotime('-1 year')).' 09:05:00 GMT'); // HTTP 1.0
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		//--

		//--
		header('Z-Test-Image: This is an image'); // just a sample dummy header entry
		//--

		//--
		ob_start(); // avoid echo warnings or errors !
		$im = imagecreatetruecolor(320, 90);
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
		imagestring($im, 25, 25, 30, 'This is a sample JPEG image ...', $text_color);
		imagestring($im, 25, 25, 50, 'Generated from PHP GD Library', $text_color);
		ob_end_clean();
		//--

		//--
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