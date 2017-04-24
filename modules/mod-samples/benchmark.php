<?php
// Controller: Samples/BenchMark
// Route: ?/page/samples.benchmark (?page=samples.benchmark)
// Author: unix-world.org
// v.3.1.2 r.2017.04.11 / smart.framework.v.3.1

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'INDEX'); // INDEX, ADMIN, SHARED

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(SMART_FRAMEWORK_TEST_MODE !== true) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$this->PageViewSetCfg('template-path', '@'); // set template path to this module
		$this->PageViewSetCfg('template-file', 'template-benchmark.htm'); // the default template
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Benchmark Test URL',
			'head-meta' => '<meta name="author" content="Smart.Framework by Unix-World, https://github.com/unix-world/Smart.Framework">',
			'main' => SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'benchmark.inc.htm',
				[
					'BENCHMARK-TITLE' => '[ Benchmark Test URL '.date('Y-m-d H:i:s O').' ]<br>use this URL to run a benchmark of this PHP framework ...'
				],
				'no' // don't use caching (use of caching make sense only if file template is used more than once per execution)
			)
		]);
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>