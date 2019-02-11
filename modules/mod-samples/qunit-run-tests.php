<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: RunTests/TestingQUnit
// Route: ?page=testing-qunit.run-tests
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED


/**
 * Admin Controller
 * Notice: some tests need single user mode ...
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAbstractAppController {

	public function Run() { // r.181221

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if($this->IfDebug()) {
			$this->PageViewSetErrorStatus(500, 'ERROR: QUnit Testing mode cannot be used when Debug is ON ...');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetCfg('template-path', 'modules/mod-testing-qunit/templates/'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-qunit.htm'); // the default template
		//--

		//--
		$tests_db_enabled = (bool) (defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS') AND (SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS === true));
		//--

		//--
		$this->PageViewSetVars([
			'title' 	=> 'QUnit TestEngine :: Smart.Framework '.SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION,
			'semaphore' => (string) 'Javascript@jQuery/PHP@v.'.phpversion(),
			'main' 		=> SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'qunit-run-tests.mtpl.js.inc',
				[
					//--
					'CHARSET' 				=> (string) $this->ControllerGetParam('charset'),
					'PHP-VERSION' 			=> (string) phpversion(),
					'SF-VERSION' 			=> (string) SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION,
					'APP-REALM' 			=> (string) $this->ControllerGetParam('app-realm'),
					'DEBUG-MODE' 			=> (string) ($this->IfDebug() ? 'yes' : 'no'),
					'LANG' 					=> (string) $this->ControllerGetParam('lang'),
					'MODULE-PATH' 			=> (string) $this->ControllerGetParam('module-path'),
					//--
					'TEST-FILESYSTEM' 		=> (string) ((defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_FILESYSTEM_TESTS') AND (SMART_FRAMEWORK_TESTUNIT_ALLOW_FILESYSTEM_TESTS === true)) ? true : false),
					//--
					'TEST-DB-SQLITE' 		=> (string) true,
					'NAME-DB-PCACHE' 		=> (string) SMART_FRAMEWORK__INFO__PERSISTENT_CACHE_BACKEND,
					'TEST-DB-PCACHE' 		=> (string) ((defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_PCACHE_TESTS') AND (SMART_FRAMEWORK_TESTUNIT_ALLOW_PCACHE_TESTS === true) AND (SmartPersistentCache::isActive() === true)) ? true : false),
					'TEST-DB-MONGO' 		=> (string) (($tests_db_enabled === true) AND (Smart::array_size(Smart::get_from_config('mongodb')) > 0) ? true : false),
					'TEST-DB-PGSQL' 		=> (string) (($tests_db_enabled === true) AND (Smart::array_size(Smart::get_from_config('pgsql')) > 0) ? true : false),
					'TEST-DB-MYSQL' 		=> (string) (($tests_db_enabled === true) AND (Smart::array_size(Smart::get_from_config('mysqli')) > 0) ? true : false),
					//--
					'TEST-MOD-DB' 			=> (string) (($tests_db_enabled === true) ? true : false),
					//--
					'SRV-SCRIPT' 			=> (string) $this->ControllerGetParam('url-script')
					//--
				]
			)
		]);
		//--

	} //END FUNCTION

} //END CLASS


/**
 * Index Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAppAdminController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php
	// or this can implement a completely different controller if it is accessed via admin.php

} //END CLASS


//end of php code
?>