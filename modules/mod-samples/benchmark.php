<?php
// Controller: Samples/BenchMark
// Route: ?/page/samples.benchmark (?page=samples.benchmark)
// Author: unix-world.org
// r.2015-12-05

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//--
		$this->PageViewSetCfg('template-path', 'benchmark');
		$this->PageViewSetCfg('template-file', 'template-benchmark.htm');
		//--

		//--
		$this->PageViewSetVar(
			'title',
			'Benchmark'
		);
		//--
		$this->PageViewSetVars([
			'head-meta' => '<meta name="author" content="Smart.Framework by Unix-World, https://github.com/unix-world/Smart.Framework">',
			'main' => SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-path').'views/benchmark.htm',
				[
					'BENCHMARK-TITLE' => 'Benchmark Test'
				]
			)
		]);
		//--

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