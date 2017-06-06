<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/TemplatingTest
// Route: ?/page/samples.templating-test (?page=samples.templating-test)
// Author: unix-world.org
// v.3.5.1 r.2017.05.12 / smart.framework.v.3.5

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

		//-- dissalow run this sample if not test mode enabled
		if(SMART_FRAMEWORK_TEST_MODE !== true) {
			$this->PageViewSetErrorStatus(500, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		$op = $this->RequestVarGet('op', '', 'string');
		//--

		//--
		$tpl = (string) $this->ControllerGetParam('module-path').'views/templating-test.inc.htm';
		//--

		//-- Uncomment the following line to see a Marker Template Analysis (DEBUG ONLY !!! Never use this in real production environments, it is intended for Development Only)
		//if($this->IfDebug()) { echo SmartDebugProfiler::print_tpl_debug($tpl,[]); } else { echo '<h1> Torn ON Debugging to see the Template Debug Analyze Info ...'; } die();
		//--

		//--
		if((string)$op == 'viewsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('div', ['web','tpl']).'<h1>Marker-TPL Template Source</h1><hr><pre style="background:#FAFAFA;"><code class="markertpl" style="width:96vw; height:75vh; overflow:auto;">'.SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::escape_html((string)SmartFileSystem::staticread((string)$tpl))).'</code></pre><hr><br>');
			return;
			//--
		} //end if
		//--

		//--
		$title = 'Marker-TPL Templating Render Demo - Extended Syntax';
		//--
		$test_switch_arr = ['a', 'b', 'c', 'd'];
		$this->PageViewSetVars([
			'title' => $title,
			'main' => SmartMarkersTemplating::render_file_template(
					(string) $tpl, // the TPL view
					[
						'TITLE' => $title,
						'VIEWS-PATH' => (string) $this->ControllerGetParam('module-view-path'),
						'MARKER' => Smart::json_encode('<a>&amp;1234567890.コアテスト·スイート.abcdefghijklmniopqrstuvwxyz:'.date('Y-m-d H:i:s').':~`!@#$%^&*()_-+={}[]|,.?</a>'),
						'MARK-AREA' => 'php',
						'TEST-COMPARE' => 'a',
						'DATA' => [
							// id        slug         name                is_vowel         arr of numbers
							'This is a sample table with Header + 15 Rows = 16 Rows in Total',
							['id'=>1,  'slug'=>'a', 'name'=>'Letter A', 'is_vowel'=>true,  'arr' => [1,2,3]],
							['id'=>2,  'slug'=>'b', 'name'=>'Letter B', 'is_vowel'=>false, 'arr' => [1,5,3]],
							['id'=>3,  'slug'=>'c', 'name'=>'Letter C', 'is_vowel'=>false, 'arr' => [5,2,3]],
							['id'=>4,  'slug'=>'d', 'name'=>'Letter D', 'is_vowel'=>false, 'arr' => [4,2,7]],
							['id'=>5,  'slug'=>'e', 'name'=>'Letter E', 'is_vowel'=>true,  'arr' => [3,1,2]],
							['id'=>6,  'slug'=>'f', 'name'=>'Letter F', 'is_vowel'=>false, 'arr' => [7,2,1]],
							['id'=>7,  'slug'=>'g', 'name'=>'Letter G', 'is_vowel'=>false, 'arr' => [6,1,8]],
							['id'=>8,  'slug'=>'h', 'name'=>'Letter H', 'is_vowel'=>false, 'arr' => [8,9,0]],
							['id'=>9,  'slug'=>'i', 'name'=>'Letter I', 'is_vowel'=>true,  'arr' => [4,2,0]],
							['id'=>10, 'slug'=>'j', 'name'=>'Letter J', 'is_vowel'=>false, 'arr' => [5,6,7]],
							['id'=>11, 'slug'=>'k', 'name'=>'Letter K', 'is_vowel'=>false, 'arr' => [7,8,9]],
							['id'=>12, 'slug'=>'l', 'name'=>'Letter L', 'is_vowel'=>false, 'arr' => [9,0,1]],
							['id'=>13, 'slug'=>'m', 'name'=>'Letter M', 'is_vowel'=>false, 'arr' => [9,1,2]],
							['id'=>14, 'slug'=>'n', 'name'=>'Letter N', 'is_vowel'=>false, 'arr' => [8,7,6]],
							['id'=>15, 'slug'=>'o', 'name'=>'Letter O', 'is_vowel'=>true,  'arr' => [6,3,8]]
						],
						'DaT2' => [
							'key1' => [ 'id' => 'val'.rand(0,1), 'name' => 'Value 1' ],
							'key2' => 'val2',
							'key3' => 'val3',
							'key4' => 'val4',
						],
						'TEST1' => 3,
						'TEST2' => Smart::random_number(2,3),
						'TEST3' => 3,
						'STATUS' => (string) $test_switch_arr[Smart::random_number(0,3)],
						'TEST-NUM' => (float) rand(0,4)/4,
						'TEST-STR' => 'a-\'b\'_"c" <d>',
						'TEST-URL' => 'http://some-url/',
						'TEST-TXT' => 'this is line one'."\n".'this is line <two>',
						'TEST-UNISTR' => 'ăĂîÎâÂșȘțȚ ȚțȘșÂâÎîĂă'
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