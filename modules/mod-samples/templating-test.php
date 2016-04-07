<?php
// Controller: Samples/TemplatingTest
// Route: ?/page/samples.templating-test (?page=samples.templating-test)
// Author: unix-world.org
// r.2016-02-15

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
						'TESTURL' => 'a"b\'c',
						'DATA' => [
						   // id        slug         name                is_vowel
							['id'=>1,  'slug'=>'a', 'name'=>'Letter A', 'is_vowel'=>true],
							['id'=>2,  'slug'=>'b', 'name'=>'Letter B', 'is_vowel'=>false],
							['id'=>3,  'slug'=>'c', 'name'=>'Letter C', 'is_vowel'=>false],
							['id'=>4,  'slug'=>'d', 'name'=>'Letter D', 'is_vowel'=>false],
							['id'=>5,  'slug'=>'e', 'name'=>'Letter E', 'is_vowel'=>true],
							['id'=>6,  'slug'=>'f', 'name'=>'Letter F', 'is_vowel'=>false],
							['id'=>7,  'slug'=>'g', 'name'=>'Letter G', 'is_vowel'=>false],
							['id'=>8,  'slug'=>'h', 'name'=>'Letter H', 'is_vowel'=>false],
							['id'=>9,  'slug'=>'i', 'name'=>'Letter I', 'is_vowel'=>true],
							['id'=>10, 'slug'=>'j', 'name'=>'Letter J', 'is_vowel'=>false],
							['id'=>11, 'slug'=>'k', 'name'=>'Letter K', 'is_vowel'=>false],
							['id'=>12, 'slug'=>'l', 'name'=>'Letter L', 'is_vowel'=>false],
							['id'=>13, 'slug'=>'m', 'name'=>'Letter M', 'is_vowel'=>false],
							['id'=>14, 'slug'=>'n', 'name'=>'Letter N', 'is_vowel'=>false],
							['id'=>15, 'slug'=>'o', 'name'=>'Letter O', 'is_vowel'=>true]
						],
						'TEST1' => 3,
						'TEST2' => Smart::random_number(2,3),
						'TEST3' => 3,
						'STATUS' => (string) $test_switch_arr[Smart::random_number(0,3)]
					]
				)
		]);
		//--

	} //END FUNCTION

} //END CLASS

//end of php code
?>