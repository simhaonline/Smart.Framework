<?php
// [LIB - Smart.Framework / Samples / Test Persistent Cache]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

// Class: \SmartModExtLib\Samples\TestUnitPCache
// Type: Module Library
// Info: this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Samples;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Test Persistent Cache
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20190401
 *
 */
final class TestUnitPCache {

	// ::

	//============================================================
	public static function testPersistentCache() {

		//--
		if((!defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_PCACHE_TESTS')) OR (SMART_FRAMEWORK_TESTUNIT_ALLOW_PCACHE_TESTS !== true)) {
			//--
			return \SmartComponents::operation_notice('Test Unit for Persistent Cache is DISABLED ...');
			//--
		} //end if
		//--

		//--
		if(!\SmartPersistentCache::isActive()) {
			//--
			return (string) \SmartComponents::operation_warn('Test Unit for Persistent Cache: NO Active Persistent Cache configuration available in configs ...');
			//--
		} //end if
		//--

		//--
		$the_test_realm = 'persistent-cache-test';
		//--
		$pcache_big_content = self::packTestArchive(); // CREATE THE Test Archive (time not counted)
		//--
		$pcache_test_key = 'pcache-test-key_'.\SmartPersistentCache::safeKey(\Smart::uuid_10_num().'-'.\Smart::uuid_36().'-'.\Smart::uuid_45());
		$pcache_test_value = array(
			'unicode-test' => '"Unicode78źź:ăĂîÎâÂșȘțȚşŞţŢグッド', // unicode value
			'big-key-test' => (string) $pcache_big_content, // a big key
			'random-key' => \Smart::uuid_10_str().'.'. \Smart::uuid_10_seq().'.'.\Smart::random_number(1000,9999) // a very random key
		);
		$pcache_test_checkum = \SmartHashCrypto::sha1(implode("\n", (array)$pcache_test_value));
		$pcache_test_arch_content = \SmartPersistentCache::varCompress($pcache_test_value);
		$pcache_test_arch_checksum = \SmartHashCrypto::sha1($pcache_test_arch_content);
		//--

		//--
		$tests = array();
		$tests[] = '***** Persistent Cache Backend: ['.SMART_FRAMEWORK__INFO__PERSISTENT_CACHE_BACKEND.'] *****';
		$tests[] = '##### Persistent Cache / TESTS with a huge size Variable (String/Json) Key-Size of 2x'.\SmartUtils::pretty_print_bytes(strlen($pcache_test_arch_content), 2).' : #####';
		//--
		$err = '';
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Building a Test Archive file for Persistent Cache Tests (time not counted)'; // archive was previous created, only test here
			if((string)$pcache_big_content == '') {
				$err = 'Failed to build the Test Archive file for the Persistent Cache Test (see the error log for more details) ...';
			} //end if
		} //end if
		//--

		//--
		$time = microtime(true);
		//--
		$tests[] = '++ START Counter ...';
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Clearing All Data (FLUSHDB)';
			if(\SmartPersistentCache::clearData() !== true) {
				$err = 'Persistent Cache FAILED to Clear All Data (FLUSHDB)';
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Building the Cache Archive';
			if((string)$pcache_test_arch_content == '') {
				$err = 'Failed to build the Cache Variable(s) Archive file for the Persistent Cache Test (see the error log for more details) ...';
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Set a short Persistent Cache Key (auto-expire in 3 seconds)';
			$pcache_set_key = \SmartPersistentCache::setKey(
				$the_test_realm,
				$pcache_test_key,
				(string) $pcache_test_value['unicode-test'],
				3 // expire it after 3 seconds
			);
			if($pcache_set_key !== true) {
				$err = 'Persistent Cache SetKey (short) returned a non-true result: '."\n".$pcache_test_key;
			} //end if
			if((string)$err == '') {
				$tests[] = 'Wait 5 seconds for Persistent Cache Key to expire, then check again if exists (time not counted)';
				sleep(5); // wait the Persistent Cache Key to Expire
				$time = (float) $time + 5; // ignore those 5 seconds (waiting time) to fix counter
				$tests[] = '-- FIX Counter (substract the 5 seconds, waiting time) ...';
				if(\SmartPersistentCache::keyExists($the_test_realm, $pcache_test_key)) {
					$err = 'Persistent Cache (short) Key does still exists (but should be expired after 5 seconds) and is not: '."\n".$pcache_test_key;
				} //end if
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Set a long Persistent Cache Key (will not expire)';
			$pcache_set_key = \SmartPersistentCache::setKey(
				$the_test_realm,
				$pcache_test_key,
				$pcache_test_arch_content
			);
			if($pcache_set_key !== true) {
				$err = 'Persistent Cache SetKey (long) returned a non-true result: '."\n".$pcache_test_key;
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Check if Persistent Cache Key exists (after set)';
			if(!\SmartPersistentCache::keyExists($the_test_realm, $pcache_test_key)) {
				$err = 'Persistent Cache Key does not exists: '."\n".$pcache_test_key;
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Set a Persistent Cache Key with Empty Realm (will expire after 30 seconds)';
			$pcache_set_rxkey = \SmartPersistentCache::setKey(
				'',
				'No-Realm-'.$pcache_test_key,
				date('Y-m-d H:i:s'),
				30
			);
			if($pcache_set_rxkey !== true) {
				$err = 'Persistent Cache SetKey with Empty Realm returned a non-true result: '."\n".'No-Realm-'.$pcache_test_key;
			} //end if
		} //end if
		//--

		//--
		if((string)$err == '') {
			$tests[] = 'Get Persistent Cache Key';
			$pcache_cached_value = \SmartPersistentCache::varUncompress(\SmartPersistentCache::getKey($the_test_realm, $pcache_test_key));
			if(\Smart::array_size($pcache_cached_value) > 0) {
				$tests[] = 'Check if Persistent Cache Key is valid (array-keys)';
				if(((string)$pcache_cached_value['unicode-test'] != '') AND ((string)$pcache_cached_value['big-key-test'] != '')) {
					$tests[] = 'Check if Persistent Cache Key is valid (checksum)';
					if((string)\SmartHashCrypto::sha1(implode("\n", (array)$pcache_cached_value)) == (string)$pcache_test_checkum) {
						if($pcache_test_value === $pcache_cached_value) {
							$tests[] = 'Unset Persistent Cache Key';
							$pcache_unset_key = \SmartPersistentCache::unsetKey($the_test_realm, $pcache_test_key);
							if($pcache_unset_key === true) {
								$tests[] = 'Check if Persistent Cache Key exists (after unset)';
								if(\SmartPersistentCache::keyExists($the_test_realm, $pcache_test_key)) {
									$err = 'Persistent Cache Key does exists (after unset) and should not: '."\n".$pcache_test_key;
								} else {
									// OK
								} //end if
							} else {
								$err = 'Persistent Cache UnSetKey returned a non-true result: '."\n".$pcache_test_key;
							} //end if else
						} else {
							$err = 'Persistent Cache Cached Value is broken: comparing stored value with original value failed on key: '."\n".$pcache_test_key;
						} //end if else
					} else {
						$err = 'Persistent Cache Cached Value is broken: checksum failed on key: '."\n".$pcache_test_key;
					} //end if else
				} else {
					$err = 'Persistent Cache Cached Value is broken: array-key is missing after Cache-Variable-Unarchive on key: '."\n".$pcache_test_key;
				} //end if
			} else {
				$err = 'Persistent Cache Cached Value is broken: non-array value was returned after Cache-Variable-Unarchive on key: '."\n".$pcache_test_key;
			} //end if
		} //end if
		//--

		//--
		$time = 'TOTAL TIME (Except building the test archive) was: '.(microtime(true) - $time); // substract the 3 seconds waiting time for Persistent Cache Key to expire
		//--
		$end_tests = '##### END TESTS ... '.$time.' sec. #####';
		//--
		if(stripos((string)SMART_FRAMEWORK__INFO__PERSISTENT_CACHE_BACKEND, 'redis:') === 0) {
			$img_check = 'lib/core/img/db/redis-logo.svg';
		} else {
			$img_check = 'lib/framework/img/sf-logo.svg';
		} //end if else
		if((string)$err == '') {
			$img_sign = 'lib/framework/img/sign-info.svg';
			$text_main = '<span style="color:#83B953;">Test OK: PHP PersistentCache.</span>';
			$text_info = '<h2><span style="color:#83B953;">All</span> the SmartFramework PersistentCache Server Operations <span style="color:#83B953;">Tests PASSED on PHP</span><hr></h2><span style="font-size:14px;">'.\Smart::nl_2_br(\Smart::escape_html(implode("\n".'* ', $tests)."\n".$end_tests)).'</span>';
		} else {
			$img_sign = 'lib/framework/img/sign-error.svg';
			$text_main = '<span style="color:#FF5500;">An ERROR occured ... PHP PersistentCache Test FAILED !</span>';
			$text_info = '<h2><span style="color:#FF5500;">A test FAILED</span> when testing PersistentCache Server Operations.<span style="color:#FF5500;"><hr>FAILED Test Details</span>:</h2><br><h5 class="inline">'.\Smart::escape_html($tests[\Smart::array_size($tests)-1]).'</h5><br><span style="font-size:14px;"><pre>'.\Smart::escape_html($err).'</pre></span>';
		} //end if else
		//--
		$test_info = 'Persistent Cache Server Test Suite for SmartFramework: PHP';
		//--
		$test_heading = 'SmartFramework Persistent Cache Server Tests: DONE ...';
		//--

		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-samples/libs/templates/testunit/partials/test-dialog.inc.htm',
			[
				//--
				'TEST-HEADING' 		=> (string) $test_heading,
				//--
				'DIALOG-WIDTH' 		=> '725',
				'DIALOG-HEIGHT' 	=> '425',
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


	//============================================================
	private static function packTestArchive($y_exclusions_arr='') {
		//--
		$the_test_file = 'modules/mod-samples/libs/TestUnitPCache.php';
		//--
		$testsrcfile = (string) \SmartFileSystem::read((string)$the_test_file);
		$out = '';
		if((string)$testsrcfile != '') {
			//--
			$testsrcfile = (string) base64_encode((string)$testsrcfile);
			$vlen = \Smart::random_number(100000,900000);
			//--
			while(strlen((string)$out) < (8388608 + $vlen)) {
				$randomizer = (string) '#'.\Smart::random_number().'#'."\n";
				$testfile = \SmartUtils::data_archive((string)$randomizer.$testsrcfile);
				if(\SmartHashCrypto::sha1((string)\SmartUtils::data_unarchive((string)$testfile)) !== \SmartHashCrypto::sha1((string)$randomizer.$testsrcfile)) {
					\Smart::log_warning('Data Unarchive Failed for Pack Test Archive ...');
					return 'Data Unarchive Failed for Pack Test Archive !';
				} //end if
				$out .= (string) $testfile;
			} //end if
			//--
		} else {
			//--
			\Smart::log_warning('Failed to read the test file: '.$the_test_file);
			return 'ERROR: Cannot Get File Read for this test !';
			//--
		} //end if
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>