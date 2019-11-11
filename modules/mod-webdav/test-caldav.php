<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Webdav/TestCalDAV (CalDAV:FileSystem)
// Route: admin.php/page/webdav.test-caldav/~
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'ADMIN'); // admin area only
define('SMART_APP_MODULE_AUTH', true); // requires auth always
define('SMART_APP_MODULE_DIRECT_OUTPUT', true); // do direct output

/**
 * Admin Controller (direct output)
 * @ignore
 */
class SmartAppAdminController extends \SmartModExtLib\Webdav\ControllerAdmCalDavFs {

	// v.20191110

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			http_response_code(503);
			echo \SmartComponents::http_message_503_serviceunavailable('ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--
		if(!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_WEBDAV_TESTS') OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_WEBDAV_TESTS !== true)) {
			http_response_code(503);
			echo \SmartComponents::http_message_503_serviceunavailable('ERROR: CalDAV Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(!defined('SMART_SOFTWARE_URL_ALLOW_PATHINFO') OR ((int)SMART_SOFTWARE_URL_ALLOW_PATHINFO < 1)) {
			http_response_code(500);
			echo \SmartComponents::http_message_500_internalerror('ERROR: CalDAV requires PathInfo to be enabled into init.php for Admin Area ...');
			return;
		} //end if
		//--
		// !!! To SECURE the below folder for PRIVATE access, create a .htaccess in wpub/test-webdav to deny all access to this folder and sub-folders !!!
		$this->DavFsRunServer(
			'wpub/test-webdav/calendar',
			true // you may disable this on large webdav file systems to avoid huge calculations
		);
		//--

		//-- HINTS:
		// # CalDAV Folder Structure [calendar/]:
		// calendars/
		// calendars/{user}/
		// calendars/{user}/DefaultCalendar/
		// principals/
		// principals/{user}/
		// # ThunderBird Lightning Calendar URL: {http(s)://prefix-and-path}/admin.php/page/webdav.test-caldav/~/calendars/admin/DefaultCalendar/
		// # MacOS iCalendar / iOS Calendar URL: {http(s)://prefix-and-path}/admin.php/page/webdav.test-caldav/~/principals/admin/
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>