<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/TestDownload
// Route: ?/page/samples.test-download (?page=samples.test-download)
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// This sample will run a sample download.
// It is recommended to use the framework security mechanisms to serve files for download whenever possible.
// See the code below and enjoy ;)

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	// by default in etc/init.php the allowed download folder(s) is set in a constant: SMART_FRAMEWORK_DOWNLOAD_FOLDERS = '<wpub>'
	// so only files under the 'wpub/' folder are allowed by default !
	private $download_file = 'wpub/sample-download.svg';

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		if((string)$this->download_file == '') {
			$this->PageViewSetErrorStatus(500, 'Empty file name to download !');
			return;
		} //end if

		if(!SmartFileSysUtils::check_if_safe_path((string)$this->download_file)) {
			$this->PageViewSetErrorStatus(403, 'Invalid file name to download (unsafe path) !');
			return;
		} //end if

		$test_file = 'modules/mod-samples/views/img/osi.svg';
		if(SmartFileSystem::is_type_file((string)$this->download_file)) { // avoid re-copy each time this script runs, compare using sha1 file ...
			if((string)sha1_file((string)$this->download_file) != (string)sha1_file((string)$test_file)) {
				SmartFileSystem::delete((string)$this->download_file);
			} //end if
		} //end if
		if(!SmartFileSystem::is_type_file((string)$this->download_file)) {
			SmartFileSystem::copy((string)$test_file, (string)$this->download_file, true); // copy a file to wpub/ to allow download it (the internal security mechanisms dissalow download files except what is defined in SMART_FRAMEWORK_DOWNLOAD_FOLDERS ...)
		} //end if

		if(!SmartFileSystem::is_type_file((string)$this->download_file)) {
			$this->PageViewSetErrorStatus(404, 'Cannot find the required file for download !');
			return;
		} //end if

		if(!SmartFileSystem::have_access_read((string)$this->download_file)) {
			$this->PageViewSetErrorStatus(500, 'The required file for download is not readable !');
			return;
		} //end if

		$download_key 	= (string) sha1((string)time().SMART_FRAMEWORK_SECURITY_KEY); // generate a unique download key that will expire shortly
		$download_link 	= (string) SmartUtils::create_download_link((string)$this->download_file, (string)$download_key); // generate an encrypted internal download link to serve that file once

		$this->PageViewSetRawHeaders([
			'Z-Test-Header-FileMTime:' 	=> (int)    filemtime($this->download_file),
			'Z-Test-Header-SHA1File:' 	=> (string) sha1_file($this->download_file)
		]);

		$this->PageViewSetCfgs([
			'download-key' 		=> (string) $download_key,
			'download-packet' 	=> (string) $download_link
		]);

		// for the rest the framework will take care as:
		// 		* detect the mime type and set the required headers (includding atatchment/inline type and file name)
		// 		* serve the file: output it using readfile() so the file can be up to 4GB (on some 64-bit file systems can be even larger)

		// for your information, as long as you ensure a strong $download_key
		// the key is automatically composed with several parts additions: the http user agent signature + visitor IP
		// any download link will expire in several hours (1..24), as defined in SMART_FRAMEWORK_DOWNLOAD_EXPIRE
		// so thereafter you can create secure download links that you can set in controllers but more,
		// you can even send this encrypted (secured) download links via URL GET/POST between requests as long as the $download_key is not exposed to the visitor !!!

	} //END FUNCTION

} //END CLASS

/**
 * Admin Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php
	// or this can implement a completely different controller if it is accessed via admin.php

} //END CLASS

//end of php code
?>