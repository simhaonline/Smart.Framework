<?php
// Controller: Samples/TemplatingTest
// Route: ?/page/samples.templating-test (?page=samples.templating-test)
// Author: unix-world.org
// v.2.3.7.7 r.2017.02.22 / smart.framework.v.2.3

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
		$title = 'Extended Markers Template Test';
		//--
		$test_switch_arr = ['a', 'b', 'c', 'd'];
		$this->PageViewSetVars([
			'title' => $title,
			'main' => SmartMarkersTemplating::render_file_template(
					$this->ControllerGetParam('module-path').'views/templating-test.htm', // the view
					[
						'TITLE' => $title,
						'TEST-COMPARE' => 'a',
						'DATA' => [
							// id        slug         name                is_vowel         arr of numbers
							'This is a sample table',
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
						'DAT2' => [
							'key1' => [ 'id' => 'val'.rand(0,1), 'name' => 'Value 1' ],
							'key2' => 'val2',
							'key3' => 'val3',
							'key4' => 'val4',
						],
						'TEST1' => 3,
						'TEST2' => Smart::random_number(2,3),
						'TEST3' => 3,
						'STATUS' => (string) $test_switch_arr[Smart::random_number(0,3)],
						'TEST-NUM' => rand(0,2),
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

//end of php code
?>