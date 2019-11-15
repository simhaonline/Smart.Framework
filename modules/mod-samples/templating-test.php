<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/TemplatingTest
// Route: ?/page/samples.templating-test (?page=samples.templating-test)
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
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

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$op = $this->RequestVarGet('op', '', 'string');
		//--

		//--
		$stpl = (string) $this->ControllerGetParam('module-path').'views/templating-test-highlight-syntax.mtpl.htm';
		//--
		$tpl = (string) $this->ControllerGetParam('module-view-path').'templating-test.mtpl.htm';
		$ptpl = (string) $this->ControllerGetParam('module-view-path').'templating-test.inc.htm';
		//--

		//-- Uncomment the following line to see a sample of Marker Template Analysis (DEBUG ONLY !!! Never use this in real production environments, it is intended for Development Only)
		//if($this->IfDebug()) { echo SmartDebugProfiler::display_marker_tpl_debug($tpl,[],false); } else { echo '<h1> Turn ON Debugging to see the Template Debug Analyze Info ...'; } die();
		//--

		//--
		if((string)$op == 'viewsource') {
			//--
			$this->PageViewSetVar(
				'main',
				(string) SmartMarkersTemplating::render_file_template(
					(string) $stpl,
					[
						'@SUB-TEMPLATES@' => [
							'%the-tpl%|html' => '@/'.SmartFileSysUtils::get_file_name_from_path($tpl)
						],
						'HTML-HIGHLIGHT' 	=> (string) SmartComponents::js_code_highlightsyntax('body', ['web','tpl']),
						'TPL-PATH' 			=> (string) $tpl,
						'TPL-TYPE' 			=> 'Template'
					]
				)
			);
			return;
			//--
		} elseif((string)$op == 'viewpartialsource') {
			//--
			$this->PageViewSetVar(
				'main',
				(string) SmartMarkersTemplating::render_file_template(
					(string) $stpl,
					[
						'@SUB-TEMPLATES@' => [
							'%the-tpl%|html' => '@/'.SmartFileSysUtils::get_file_name_from_path($ptpl)
						],
						'HTML-HIGHLIGHT' 	=> (string) SmartComponents::js_code_highlightsyntax('body', ['web','tpl']),
						'TPL-PATH' 			=> (string) $ptpl,
						'TPL-TYPE' 			=> 'Sub-Template'
					]
				)
			);
			return;
			//--
		} //end if
		//--

		//--
		$title = 'Markers-TPL Templating Render Demo - Syntax';
		//--
		$data = [ // v.20191115
			//-- ### ALL VARIABLE KEYS ARE CASE INSENSITIVE IN CONTROLLERS ; IN TEMPLATES ALL VARIABLE NAME / KEYS ARE UPPERCASE ; variable names will allow also - (. is reserved for separator as arr[key] is ARR.KEY)
			'Version' => (string) SMART_FRAMEWORK_RELEASE_TAGVERSION.' '.SMART_FRAMEWORK_RELEASE_VERSION,
			'heLLo__World' => '<h1>Demo: Markers-TPL Templating built-into Smart.Framework</h1>',
			'NaViGaTiOn' => [
				array('href' => '#link1', 'caption' => 'Sample Link <1>'),
				array('href' => '#link2', 'caption' => 'Sample Link <2>'),
				array('href' => '#link3', 'caption' => 'Sample Link <3>')
			],
			'DATE-TIME' => (string) date('Y-m-d H:i:s O')."\t"."'".date('T')."'",
			'tbL' => [
				['a1' => '1.1', 'a2' => '1.2', 'a3' => '1.3'],
				['a1' => '2.1', 'a2' => '2.2', 'a3' => '2.3'],
				['a1' => '3.1', 'a2' => '3.2', 'a3' => '3.3']
			],
			'tCount' => 3,
			'A' 		=> 'Test-1',
			'b' 		=> 'Test-2'
			//--
		];
		//--
		$res_time = (float) microtime(true);
		//--
		if(class_exists('SmartTemplating') AND (Smart::random_number(0,1))) { // must enable require_once('modules/smart-extra-libs/autoload.php'); in modules/app/app-custom-bootstrap.inc.php
			$this->PageViewSetVars([
				'title' => $title.' (autodetect file extension)',
				'main' => SmartMarkersTemplating::render_file_template(
					(string) $tpl, // the TPL view (syntax: Markers-TPL ; ; must contain '.mtpl.' in the file name)
					(array)  $data // the Variables array
				)
			]);
		} else {
			$this->PageViewSetVars([
				'title' => $title,
				'main' => SmartMarkersTemplating::render_file_template(
					(string) $tpl, // the TPL view (syntax: Markers-TPL)
					(array)  $data // the Variables array
				)
			]);
		} //end if else
		//--
		$this->PageViewSetVar('aside', '<div style="background:#333333; color:#ffffff; position:fixed; right:5px; top:10px; padding:3px;">RenderTime:&nbsp;'.Smart::format_number_dec((float)(microtime(true) - (float)$res_time), 7).'&nbsp;s</div>');
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