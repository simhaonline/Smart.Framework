<?php
// [LIB - SmartFramework / Samples / Test MongoDB Server]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

// Class: \SmartModExtLib\Samples\TestUnitMongoDB
// Type: Module Library
// Info: this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Samples;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Test TestUnitMongoDB Server
 *
 * @access 		private
 * @internal
 *
 * @version 	v.180310
 *
 */
final class TestUnitMongoDB {

	// ::

	//============================================================
	public static function testMongoServer() {

		//--
		if(SMART_FRAMEWORK_TESTUNIT_ALLOW_MONGO_TESTS !== true) {
			//--
			return (string) \SmartComponents::operation_notice('Test Unit for MongoDB Server is DISABLED ...');
			//--
		} //end if
		//--

		//--
		$cfg_mongo = (array) \Smart::get_from_config('mongodb');
		//--

		//--
		if(((string)$cfg_mongo['server-host'] == '') OR ((string)$cfg_mongo['server-port'] == '') OR ((string)$cfg_mongo['dbname'] == '')) {
			//--
			return (string) \SmartComponents::operation_warn('Test Unit for Mongo Server: INVALID MongoDB server configuration available in configs ...');
			//--
		} //end if
		//--

		//--
		$mongo = new \SmartMongoDb((array)$cfg_mongo);
		//--

		//--
		$time = microtime(true);
		//--

		//--
		$dtime = date('Y-m-d H:i:s');
		$comments = '"Unicode78źź:ăĂîÎâÂșȘțȚşŞţŢグッド'.'-'.\Smart::random_number(1000,9999)."'";
		//--

		//--
		$tests = array();
		$tests[] = '##### MongoDB / TESTS: #####';
		//--
		$err = '';
		//--

		//--
		$tests[] = 'MongoDB Server Version: '.$mongo->get_server_version();
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Drop Test Collection if Exists (if not exists just ignore the error)';
			$result = $mongo->igcommand([
				'drop' => (string) 'myTestCollection'
			]);
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Create Test Collection';
			$tests[] = (string) $tst;
			$result = $mongo->igcommand([
				'create' => (string) 'myTestCollection'
			]);
			if(!$mongo->command_is_ok($result)) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[0/ok] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Bulk Insert 10 Documents';
			$tests[] = (string) $tst;
			$docs = array();
			for($i=0; $i<10; $i++) {
				$docs[] = [
					'_id'  => $mongo->assign_uuid(),
					'name' => 'Test #'.$i,
					'cost' => ($i+1),
					'data' => [
						'unicodeStr' => (string) $comments,
						'dTime' => (string) $dtime,
						'isBulk' => true,
						'rating' => rand(1,9) / 100
					]
				];
			} //end for
			$result = $mongo->bulkinsert('myTestCollection', (array)$docs);
			$docs = array();
			if($result[1] != 10) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 10 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Insert Single Document';
			$tests[] = (string) $tst;
			$doc = array();
			$doc['_id'] = $mongo->assign_uuid();
			$doc['name'] = 'Test:'.$comments;
			$doc['cost'] = 0;
			$result = $mongo->insert('myTestCollection', (array)$doc);
			$doc = array();
			if($result[1] != 1) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Insert Another Single Document';
			$tests[] = (string) $tst;
			$doc = array();
			$doc['_id'] = $mongo->assign_uuid();
			$doc['name'] = 'Test:'.$comments;
			$doc['cost'] = 2;
			$result = $mongo->insert('myTestCollection', (array)$doc);
			$doc = array();
			if($result[1] != 1) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Insert Another Single Document';
			$tests[] = (string) $tst;
			$doc = array();
			$doc['_id'] = $mongo->assign_uuid();
			$doc['name'] = 'Test:'.$comments;
			$doc['cost'] = 2;
			$result = $mongo->insert('myTestCollection', (array)$doc);
			$doc = array();
			if($result[1] != 1) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Update No Documents by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->update(
				'myTestCollection',
				[ 'name' => $comments ], 	// filter (update only this)
				'$set', 					// increment operation
				[ 							// update array
					'wrongupdate' => true
				]
			);
			if($result[1] != 0) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 0 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Update Single Document by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->update(
				'myTestCollection',
				[ 'name' => 'Test:'.$comments, 'cost' => 0 ], 	// filter (update only this)
				'$inc', 										// increment operation
				[ 												// update array
					'cost' => (float) 1
				]
			);
			if($result[1] != 1) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Update Many Documents by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->update(
				'myTestCollection',
				[ 'name' => [ '$ne' => 'Test:'.$comments ] ], 	// filter (update all except these)
				'$set', 										// upd. operation
				[ 												// update array
					'updated' => true
				]
			);
			if($result[1] != 10) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 10 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Delete Single Document by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->delete(
				'myTestCollection',
				[ 'name' => 'Test:'.$comments, 'cost' => 1 ] // filter
			);
			if($result[1] != 1) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Delete Many Documents by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->delete(
				'myTestCollection',
				[ 'name' => 'Test:'.$comments ] // filter
			);
			if($result[1] != 2) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 2 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Delete No Documents by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->delete(
				'myTestCollection',
				[ 'name' => 'Test:'.$comments ] // filter
			);
			if($result[1] != 0) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 0 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Insert Another Document';
			$tests[] = (string) $tst;
			$doc = array();
			$doc['_id'] = $mongo->assign_uuid();
			$doc['name'] = 'Test:'.$comments;
			$doc['cost'] = 7;
			$result = $mongo->insert('myTestCollection', (array)$doc);
			$doc = array();
			if($result[1] != 1) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[1] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Count Many Documents by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->count(
				'myTestCollection',
				[ 'name' => [ '$ne' => 'Test:'.$comments ] ] // filter (update all except these)
			);
			if($result != 10) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of integer should be 10 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Count Single Document by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->count(
				'myTestCollection',
				[ 'name' => [ '$eq' => 'Test:'.$comments ] ] // filter (update all except these)
			);
			if($result != 1) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of integer should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Count No Documents by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->count(
				'myTestCollection',
				[ 'name' => [ '$eq' => 'Test:!' ] ] // filter (update all except these)
			);
			if($result != 0) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of integer should be 0 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Find One Document by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->findone(
				'myTestCollection',
				[ 'cost' => 7 ], // filter (update all except these)
				[ '_id', 'name', 'cost' ],
				[
					'limit' => 2 // trying to fake the limit
				]
			);
			if((int)$result['cost'] != 7) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of one specific document is different: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Find Single Document by Filter and Limit';
			$tests[] = (string) $tst;
			$result = $mongo->find(
				'myTestCollection',
				[ 'cost' => 7 ], // filter (update all except these)
				[],
				[
					'limit' => 1
				]
			);
			if((\Smart::array_size($result) != 1) OR (!is_array($result[0])) OR ($result[0]['cost'] != 7)) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of one specific document is different: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Find Many Documents by Filter';
			$tests[] = (string) $tst;
			$result = $mongo->find(
				'myTestCollection',
				[ 'cost' => 7 ], // filter (update all except these)
				[ '_id', 'name', 'cost' ],
				[
					'limit' => 2, // trying to fake the limit
					'sort' => [ 'cost' => -1 ], // sort by cost descending
				]
			);
			if((\Smart::array_size($result) != 2) OR (!is_array($result[0])) OR ($result[0]['cost'] != 7) OR (!is_array($result[1])) OR ($result[1]['cost'] != 7)) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of one specific document is different: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		$mongo = null;
		$mongo = new \SmartMongoDb((array)$cfg_mongo);
		//--

		//--
		if((string)$err == '') {
			$tst = 'Search Aggregate Group By with Filter and Sort';
			$tests[] = (string) $tst;
			$result = $mongo->command([
				'aggregate' => (string) 'myTestCollection',
				'pipeline' => [
					[
						'$match' => [ 'cost' => ['$gte' => 6] ]
					],
					[
						'$group' => [ '_id' => '$cost', 'total' => ['$sum' => '$cost'] ]
					],
					[
						'$sort' => [ '_id' => -1 ]
					]
				]
			]);
			if((!$mongo->command_is_ok($result)) OR (!is_array($result[0])) OR (\Smart::array_size($result[0]['result']) != 5) OR (!is_array($result[0]['result'][0])) OR (!is_array($result[0]['result'][1])) OR (!is_array($result[0]['result'][2])) OR (!is_array($result[0]['result'][3])) OR (!is_array($result[0]['result'][4]))) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of one specific document is different: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Search Distinct with Filter';
			$tests[] = (string) $tst;
			$result = $mongo->command([
				'distinct' => (string) 'myTestCollection',
				'key' => (string) 'cost',
				'query' => (array) ['cost' => ['$gte' => 6]]
			]);
			if((!$mongo->command_is_ok($result)) OR (!is_array($result[0])) OR (\Smart::array_size($result[0]['values']) != 5)) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of one specific document is different: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tst = 'Drop Test Collection';
			$tests[] = (string) $tst;
			$result = $mongo->command([
				'drop' => (string) 'myTestCollection'
			]);
			if(!$mongo->command_is_ok($result)) {
				$err = 'The Test: '.$tst.' FAILED ! Expected result of array[0/ok] should be 1 but is: '.print_r($result,1);
			} //end if
		} //end if
		//--

		//--
		$time = 'TOTAL TIME was: '.(microtime(true) - $time);
		//--
		$end_tests = '##### END TESTS ... '.$time.' sec. #####';
		//--
		$img_check = 'lib/core/img/db/mongodb-logo.svg';
		if((string)$err == '') {
			$img_sign = 'lib/framework/img/sign-info.svg';
			$text_main = '<span style="color:#83B953;">Good ... Perfect &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; グッド ... パーフェクト</span>';
			$text_info = '<h2><span style="color:#83B953;">All</span> the SmartFramework MongoDB Server Operations <span style="color:#83B953;">Tests PASSED on PHP</span><hr></h2><span style="font-size:14px;">'.\Smart::nl_2_br(\Smart::escape_html(implode("\n".'* ', $tests)."\n".$end_tests)).'</span>';
		} else {
			$img_sign = 'lib/framework/img/sign-error.svg';
			$text_main = '<span style="color:#FF5500;">An ERROR occured ... &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; エラーが発生しました ...</span>';
			$text_info = '<h2><span style="color:#FF5500;">A test FAILED</span> when testing MongoDB Server Operations.<span style="color:#FF5500;"><hr>FAILED Test Details</span>:</h2><br><h5 class="inline">'.\Smart::escape_html($tests[\Smart::array_size($tests)-1]).'</h5><br><span style="font-size:14px;"><pre>'.\Smart::escape_html($err).'</pre></span>';
		} //end if else
		//--
		$test_info = 'MongoDB Server Test Suite for SmartFramework: PHP';
		//--
		$test_heading = 'SmartFramework MongoDB Server Tests: DONE ...';
		//--

		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-samples/libs/templates/testunit/partials/test-dialog.inc.htm',
			[
				//--
				'TEST-HEADING' 		=> (string) $test_heading,
				//--
				'DIALOG-WIDTH' 		=> '725',
				'DIALOG-HEIGHT' 	=> '480',
				'IMG-SIGN' 			=> (string) $img_sign,
				'IMG-CHECK' 		=> (string) $img_check,
				'TXT-MAIN-HTML' 	=> (string) $text_main,
				'TXT-INFO-HTML' 	=> (string) $text_info,
				'TEST-INFO' 		=> (string) $test_info
				//--
			]
		);
		//--

	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>