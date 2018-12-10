<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/TemplatingTestExtended
// Route: ?/page/samples.templating-test-extended (?page=samples.templating-test-extended)
// Author: unix-world.org
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

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
		$tpl = (string) $this->ControllerGetParam('module-path').'views/templating-test-extended.mtpl.htm';
		$ptpl = (string) $this->ControllerGetParam('module-path').'views/partials/templating-test-extended.inc.htm';
		//--

		//-- Uncomment the following line to see a sample of Marker Template Analysis (DEBUG ONLY !!! Never use this in real production environments, it is intended for Development Only)
		//if($this->IfDebug()) { echo SmartDebugProfiler::display_marker_tpl_debug($tpl,[],false); } else { echo '<h1> Turn ON Debugging to see the Template Debug Analyze Info ...'; } die();
		//--

		//--
		if((string)$op == 'viewsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<h1>Markers-TPL Template Source:<br><i>'.Smart::escape_html($tpl).'</i></h1><hr><pre style="background:#FAFAFA;"><code class="markerstpl" style="width:96vw; height:75vh; overflow:auto;">'.SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::escape_html((string)SmartFileSystem::read((string)$tpl))).'</code></pre><hr><br>');
			return;
			//--
		} elseif((string)$op == 'viewpartialsource') {
			//--
			$this->PageViewSetVar('main', SmartComponents::js_code_highlightsyntax('body', ['web','tpl']).'<h1>Markers-TPL Sub-Template Source:<br><i>'.Smart::escape_html($ptpl).'</i></h1><hr><pre style="background:#FAFAFA;"><code class="markerstpl" style="width:96vw; height:75vh; overflow:auto;">'.SmartMarkersTemplating::prepare_nosyntax_html_template(Smart::escape_html((string)SmartFileSystem::read((string)$ptpl))).'</code></pre><hr><br>');
			return;
			//--
		} //end if
		//--

		//--
		$title = 'Markers-TPL Templating Render Demo - Extended Syntax';
		//--
		$test_switch_arr = ['a', 'b', 'c', 'd'];
		$this->PageViewSetVars([
			'title' => $title,
			'main' => SmartMarkersTemplating::render_file_template(
					(string) $tpl, // the TPL view
					[
						//-- ##### ALL VARIABLE KEYS ARE CASE INSENSITIVE IN CONTROLLERS ; IN TEMPLATES ALL VARIABLE NAME / KEYS ARE UPPERCASE #####
						'TITLE' => (string) $title,
						'VIEWS-PATH' => (string) $this->ControllerGetParam('module-view-path'),
						'NUMBER' => (rand(0,1)) ? '1' : '-1',
						'MARKER' => (string) Smart::json_encode('<a>&amp;1234567890.コアテスト·スイート.abcdefghijklmniopqrstuvwxyz:'.date('Y-m-d H:i:s').':~`!@#$%^&*()_-+={}[]|,.?</a>'),
						'TEXTSTR' => '1234567890 . コアテスト·スイート . abcdefghijklmniopqrstuvwxyz',
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
						'STATUS' => (string) $test_switch_arr[(int)Smart::random_number(0,3)],
						'TEST-NUM' => (float) rand(0,4)/4,
						'TEST-STR' => 'a-\'b\'_"c" <d>',
						'TEST-URL' => 'http://some-url/',
						'TEST-TXT' => 'this is line one'."\n".'this is line <two>',
						'TEST-UNISTR' => 'ăĂîÎâÂșȘțȚ țȚȘșÂâÎîĂă " ABc ;',
						'TEST-STRTOTRIM' => ' abc '
						//--
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