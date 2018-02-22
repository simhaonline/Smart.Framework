<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Webdav/Test (WebDAV:FileSystem)
// Route: admin.php/page/webdav.test/~
// Author: unix-world.org
// v.180222.1945

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // admin area only
define('SMART_APP_MODULE_AUTH', true); // requires auth always
define('SMART_APP_MODULE_DIRECT_OUTPUT', true); // do direct output

/**
 * Admin Controller (direct output)
 */
class SmartAppAdminController extends \SmartModExtLib\Webdav\ControllerAdmDavFs {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			http_response_code(503);
			echo \SmartComponents::http_message_503_serviceunavailable('ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--
		if(!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DAVFS_TESTS') OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_DAVFS_TESTS !== true)) {
			http_response_code(503);
			echo \SmartComponents::http_message_503_serviceunavailable('ERROR: WebDAV Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!defined('SMART_SOFTWARE_URL_ALLOW_PATHINFO') OR ((int)SMART_SOFTWARE_URL_ALLOW_PATHINFO < 1)) {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: WebDAV requires PathInfo to be enabled into init.php for Admin Area ...');
			return;
		} //end if
		//--
		// !!! To SECURE the below folder for PRIVATE access, create a .htaccess in wpub/webapps-content to deny all access to this folder and sub-folders !!!
		//define('SMART_WEBDAV_PROPFIND_ETAG_MAX_FSIZE', -1); // etags on PROPFIND :: set = -2 to disable etags ; set to -1 to show etags for all files ; if >= 0, if the file size is >= with this limit will only calculate the etag (etag on PROPFIND is not mandatory for WebDAV and may impact performance if there are a large number of files in a directory or big size files ...) ; etags will always show on HEAD method
		$this->DavFsRunServer(
			'wpub/webapps-content/test-webdav',
			true // you may disable this on large webdav file systems to avoid huge calculations
		);
		//--

		//-- HINTS:
		// # WebDAV:
		// # Linux File Managers: {dav(s)://prefix-and-path}/admin.php/page/webdav.test/~
		// # MacOS Finder / iOS FileBrowser (App) / Windows (Cyberduck): {http(s)://prefix-and-path}/admin.php/page/webdav.test/~
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>