<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/Testunit
// Route: ?/page/samples.testunit (?page=samples.testunit)
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//-- this is just for testing purposes (lint) ; otherwise you should prefere to autoload classes by dependency injection only whey are needed !
require_once('lib/core/plugins/staticload.php');
if(SmartFileSystem::is_type_file('modules/smart-extra-libs/staticload.php')) {
	require_once('modules/smart-extra-libs/staticload.php');
} //end if
//--

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

define('SMART_FRAMEWORK_TESTUNIT_BASE_URL', '?/page/samples.testunit/op/');
if(SMART_FRAMEWORK_ADMIN_AREA === true) {
	define('SMART_FRAMEWORK_TESTUNIT_CAPTCHA_MODE', 'session');
} else {
	define('SMART_FRAMEWORK_TESTUNIT_CAPTCHA_MODE', 'cookie');
} //end if else


/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAbstractAppController {


	public function Initialize() {
		//--
		// this is pre-run
		//--
		$this->PageViewSetCfg('template-path', '@');
		$this->PageViewSetCfg('template-file', 'template.htm');
		//--
	} //END FUNCTION


	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//--
		if(SMART_FRAMEWORK_ADMIN_AREA === true) {
			SmartSession::start(); // start the session
		} //end if
		//--

		//--
		if(SmartPersistentCache::isActive()) {
			SmartPersistentCache::getKey('test-unit', 'version'); // just test if pcache re-uses the connection ...
		} //end if
		//--

		//--
		$release_hash = (string) $this->ControllerGetParam('release-hash');
		//--
		$op = $this->RequestVarGet('op', 'testunit.main', 'string');
		$test_cookie = $this->CookieVarGet((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME);
		//--
		switch((string)$op) {
		//#####
			case 'testunit.cookies':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = 'COOKIE-TEST: '.SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME.' = '.$test_cookie;
				//--
				break;
			case 'testunit.strings-test':
			case 'testunit.strings-test-json':
				//--
				sleep(1);
				$str_php = $this->RequestVarGet('str_php', '', 'string');
				$str_js = $this->RequestVarGet('str_js', '', 'string');
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitStrings::testUnicode($str_php, $str_js);
				if((string)$op == 'testunit.strings-test-json') {
					$this->PageViewSetCfg('rawmime', 'text/json');
					$this->PageViewSetCfg('rawdisp', 'inline');
					$main = Smart::json_encode([
						'div_content_html' => (string) $main
					]);
				} //end if
				//--
				break;
			case 'testunit.crypto-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitCrypto::testPhpAndJs();
				//--
				break;
			case 'testunit.filesys-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitFileSystem::testFs();
				//--
				break;
			case 'testunit.dbadb-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitDbaDB::testDbaDb();
				//--
				break;
			case 'testunit.pcache-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitPCache::testPersistentCache();
				//--
				break;
			case 'testunit.mongodb-server-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitMongoDB::testMongoServer();
				//--
				break;
			case 'testunit.pgsql-server-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitPgSQL::testPgServer();
				//--
				break;
			case 'testunit.mysql-server-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitMySQLi::testMyServer();
				//--
				break;
			case 'testunit.json-sqlite3-smartgrid':
				//--
				$this->PageViewSetCfg('rawpage', true);
				//--
				$ofs = $this->RequestVarGet('ofs', 0, 'integer+');
				$sortby = $this->RequestVarGet('sortby', 'id', 'string');
				$sortdir = $this->RequestVarGet('sortdir', 'ASC', 'string');
				$sorttype = $this->RequestVarGet('sorttype', 'text', 'string');
				$src = $this->RequestVarGet('src', '', 'string'); // filter var
				//--
				$main = \SmartModExtLib\Samples\TestUnitSQLite3::testJsonSmartgrid($ofs, $sortby, $sortdir, $sorttype, $src);
				//--
				break;
			case 'testunit.html-editor':
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				$main = '<script>'.SmartViewHtmlHelpers::js_code_init_away_page().'</script>';
				$main .= SmartViewHtmlHelpers::html_jsload_htmlarea();
				$main .= SmartViewHtmlHelpers::html_js_htmlarea('test_html_area', 'test_html_area', '', '920px', '500px');
				$fmcallback = SmartViewHtmlHelpers::html_js_htmlarea_fm_callback('#', false); // just for test
				$main .= '<button class="ux-button" onClick="alert($(\'#test_html_area\').val());">Get HTML Source</button>';
				//--
				break;
			case 'testunit.code-editor':
				//--
				$mode = $this->RequestVarGet('mode', 'markdown', 'string');
				if((string)$mode != 'markdown') {
					$mode = 'html';
				} //end if
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				$main = '<script>'.SmartViewHtmlHelpers::js_code_init_away_page('The changes will be lost !').'</script>';
				$main .= SmartViewHtmlHelpers::html_jsload_editarea();
				$main .= SmartViewHtmlHelpers::html_js_editarea('test_code_editor', 'test_code_editor', '', (string)$mode, true, '920px', '450px'); // html
				//--
				break;
			case 'testunit.barcodes-qrcode':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitBarcodes::test2dBarcodeQRCode();
				//--
				break;
			case 'testunit.barcodes-semcode':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitBarcodes::test2dBarcodeDataMatrix();
				//--
				break;
			case 'testunit.barcodes-pdf417':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitBarcodes::test2dBarcodePdf417();
				//--
				break;
			case 'testunit.barcodes-code128':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitBarcodes::test1dBarcode128B();
				//--
				break;
			case 'testunit.barcodes-code93':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitBarcodes::test1dBarcode93();
				//--
				break;
			case 'testunit.barcodes-code39':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitBarcodes::test1dBarcode39();
				//--
				break;
			case 'testunit.barcodes-rm4kix':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitBarcodes::test1dBarcodeKix();
				//--
				break;
			case 'testunit.interractions':
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				$main = \SmartModExtLib\Samples\TestUnitBrowserWinInterractions::winModalPopupContentHtml();
				//--
				break;
			case 'testunit.autocomplete':
				//--
				$src = $this->RequestVarGet('src', '', 'string');
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitSQLite3::testJsonAutocomplete($src);
				//--
				break;
			case 'testunit.captcha':
				//--
				$this->PageViewSetCfg('rawpage', 'yes'); // should work both: true or 'yes'
				$this->PageViewSetCfg('rawmime', 'image/png');
				$this->PageViewSetCfg('rawdisp', 'inline');
				$main = \SmartModExtLib\Samples\TestUnitMain::captchaImg('png');
				//--
				break;
			case 'testunit.post-form-by-ajax':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitMain::formReplyJson(
					$this->RequestVarGet('tab'),
					$this->RequestVarGet('frm')
				);
				//--
				break;
			case 'testunit.main':
				//--
				$is_modal = $this->RequestVarGet('winmod', '', 'string');
				$is_printable = $this->RequestVarGet('print', '', 'string');
				if(((string)$is_modal == 'yes') OR ((string)$is_printable == 'yes')) {
					$this->PageViewSetCfg('template-file', 'template-modal.htm');
				} //end if
				//--
				$main = \SmartModExtLib\Samples\TestUnitMain::mainScreen(
					$this->RequestVarGet('tab'),
					$this->RequestVarGet('frm'),
					$this->RequestVarGet('testformdata')
				);
				//--
				if(((string)$is_modal != 'yes') AND ((string)$is_printable != 'yes')) {
					if($this->IfDebug()) {
						$this->SetDebugData('TestUnit.Main.Request', $this->RequestVarsGet());
						$this->SetDebugData('TestUnit.Main', 'Loading all staticload libs at once for test purposes ...');
					} //end if
				} //end if
				//--
				break;
		//#####
			case 'test.phpinfo':
				//--
				if((defined('SMART_FRAMEWORK_TESTUNIT_ALLOW_FILESYSTEM_TESTS')) AND (SMART_FRAMEWORK_TESTUNIT_ALLOW_FILESYSTEM_TESTS === true)) { // if trusted environment
					$this->PageViewSetCfg('rawpage', true);
					ob_start();
					phpinfo();
					$main = ob_get_contents();
					ob_end_clean();
				} else {
					$main = SmartComponents::operation_notice('This test is currently DISABLED ...');
				} //end if else
				break;
			case 'test.markdown':
				//--
				$main = SmartViewHtmlHelpers::html_jsload_highlightsyntax('body'); // highlight js
				$main .= '<h1>Markdown Syntax Render Test</h1><hr>';
				$main .= SmartMarkersTemplating::render_template(
					(string) (new SmartMarkdownToHTML())->text((string)SmartFileSystem::read($this->ControllerGetParam('module-view-path').'markdown-test.md')),
					[
						'TITLE' => 'This is a <test> title that comes from TPL variables'
					]
				);
				$main .= '<hr>';
				$main .= '<h5 id="qunit-test-result">Test OK: PHP Markdown Render.</h5>';
				//--
				break;
			case 'test.json':
				//--
				$mixed_data = ['Unicode Text' => '"Unicode78źź:ăĂîÎâÂșȘțȚşŞţŢグッド\'#@<tag attribute="true">!$%^&*()-_=+'."\r\n\t".'</tag>', 'Numbers' => 1234567890.99, 'Boolean TRUE:' => true, 'Boolean FALSE:' => false];
				//--
				$main = '<h1>Json Test</h1>';
				$main .= '<pre style="background:#ECECEC; border:1px solid #CCCCCC; line-height:32px; padding:8px;">';
				$main .= '<b>(Default) Unicode Unescaped / HTML-Safe:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data))."\n";
				$main .= '<b>Unicode Unescaped:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data, false, true, false))."\n";
				$main .= '<hr>';
				$main .= '<b>Unicode Unescaped / HTML-Safe / Pretty Print:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data, true, true, true))."\n";
				$main .= '<b>Unicode Unescaped / Pretty Print:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data, true, true, false))."\n";
				$main .= '<hr>';
				$main .= '<b>Unicode Escaped / HTML-Safe:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data, false, false))."\n";
				$main .= '<b>Unicode Escaped:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data, false, false, false))."\n";
				$main .= '<hr>';
				$main .= '<b>Unicode Escaped / HTML-Safe / Pretty Print:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data, true, false))."\n";
				$main .= '<b>Unicode Escaped / Pretty Print:</b>'."\n".Smart::escape_html(Smart::json_encode($mixed_data, true, false, false))."\n";
				$main .= '</pre>';
				$main .= '<hr>';
				$main .= '<h5 id="qunit-test-result">Test OK: PHP JSON Encode/Decode.</h5>';
				//--
				break;
			case 'test.calendar':
				//--
				$main = SmartCalendarComponent::display_html_calendar(
					'',
					'100%',
					true,
					[
						[ 'date-start' => date('Y-m-d H:i:s'), 'event-html' => 'A test event for <b>Today</b> ...' ]
					]
				);
				$main .= '<br><hr><br>';
				$main .= SmartCalendarComponent::display_html_minicalendar();
				//--
				break;
			case 'test.http-post-preview':
				$this->PageViewSetCfg('rawpage', true);
				$nofiles = $this->RequestVarGet('nofiles', '', 'string');
				$main = 'Smart.Framework HTTP Post Test'."\n";
				$main .= 'COOKIES :: '.SmartUtils::pretty_print_var($_COOKIE)."\n";
				$main .= 'GET VARS :: '.SmartUtils::pretty_print_var($_GET)."\n";
				$main .= 'POST VARS :: '.SmartUtils::pretty_print_var($_POST)."\n";
				if((string)$nofiles != 'yes') {
					$main .= 'FILES :: '.SmartUtils::pretty_print_var($_FILES)."\n";
				} //end if
				break;
			case 'test.http-post':
				$nofiles = $this->RequestVarGet('nofiles', '', 'string');
				$browser = new SmartHttpClient('1.0');
				$browser->postvars = [
					'nofiles' 	=> (string) $nofiles,
					'var1' 		=> 'val1',
					'var2' 		=> [
						'val2.1' => 'a',
						'val"2.2' => 'b'
					],
					'var3' 		=> [
						1,
						2,
						3
					]
				];
				if((string)$nofiles != 'yes') {
					$browser->postfiles = [ // optional
						'my_file' => [
							'filename' => 'sample.txt',
							'content'  => 'this is the content of the file'
						],
						'my_other_file' => [
							'filename' => 'sample.xml',
							'content'  => '<xml>test</xml>'
						]
					];
				} //end if
				$browser->cookies = [ // optional
					'testCookie' => '12345'
				];
				$result = (array) $browser->browse_url($this->ControllerGetParam('url-addr').'?page=samples.testunit&op=test.http-post-preview', 'POST');
				$browser = null; // free mem
				if(($result['result'] != 1) OR ($result['code'] != 200)) {
					$this->PageViewSetErrorStatus(502, 'Browsing failed for the given URL :: Result: '.$result['result'].' ; Status-Code: '.(int)$result['code']);
					$result = null; // free mem
					return;
				} else {
					$main = (string) '<h1>Load URL: OK '.$result['code'].'</h1><pre style="background:#ECECEC">'.Smart::escape_html($result['content']).'</pre>';
					$result = null; // free mem
				} //end if else
				break;
			case 'test.load-url':
				//--
				$robot = (array) SmartRobot::load_url_content('http://www.unix-world.org', 20, 'GET');
				if(($robot['result'] != 1) OR ($robot['code'] != 200)) {
					$this->PageViewSetErrorStatus(502, 'Browsing failed for the given URL :: Result: '.$robot['result'].' ; Status-Code: '.(int)$robot['code']);
					$robot = null; // free mem
					return;
				} else {
					$main = (string) '<h1>Load URL: OK '.$robot['code'].'</h1><pre style="background:#ECECEC">'.Smart::escape_html($robot['content']).'</pre>';
					$robot = null; // free mem
				} //end if else
				//--
				break;
			case 'test.load-secure-url':
				//--
				$robot = (array) SmartRobot::load_url_content('https://www.unix-world.org', 20, 'GET', (string)SMART_FRAMEWORK_SSL_MODE);
				if(($robot['result'] != 1) OR ($robot['code'] != 200)) {
					$this->PageViewSetErrorStatus(502, 'Browsing failed for the given URL :: Result: '.$robot['result'].' ; Status-Code: '.(int)$robot['code']);
					$robot = null; // free mem
					return;
				} else {
					$main = (string) '<h1>Load Secure URL: OK '.$robot['code'].'</h1><pre style="background:#ECECEC">'.Smart::escape_html($robot['content']).'</pre>';
					$robot = null; // free mem
				} //end if else
				//--
				break;
			case 'test.spreadsheet-export':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$sse = new SmartSpreadSheetExport();
				$this->PageViewSetCfg('rawmime', $sse->getMimeType());
				$this->PageViewSetCfg('rawdisp', $sse->getDispositionHeader('myfile.excel2003.xml', 'attachment'));
				$main = $sse->getFileContents(
					'A Table',
					['<column 1>', 'column " 2', 'column & 3'], // header
					[ // data
						['data 1.1', 'data 1.2', 1.30],
						['data 2.1', 'data 2.2'."\n".'some extra text here ...', 2.31]
					]
				);
				//--
				break;
		//#####
			case 'testunit.redirect':
				//--
				$this->PageViewSetRedirectUrl('https://www.unix-world.org', 302);
				//--
				break;
		//#####
			default:
				//--
				$this->PageViewSetErrorStatus(400, [ 'Invalid TestUnit Operation ! ...', '<div title="Extra Message HTML"><b><i>You must select a valid TestUnit Operation</i></b></div>' ]);
				return;
				//--
		} //end switch
		//--

		//--
		$the_semaphore 	= ((SmartAppInfo::TestIfModuleExists('mod-ui-jqueryui')) && (SMART_FRAMEWORK_ADMIN_AREA === true)) ? 'skip-js-ui' : '';
		$custom_ui 		= $the_semaphore ? 'jqueryui' : '';
		//--
		$this->PageViewSetVars([
			'title' 		=> 'Smart.Framework Test and Demo Suite',
			'main' 			=> (string) $main,
			'semaphore' 	=> (string) $the_semaphore, // skip load the default JS-UI if jQueryUI is available
			'custom-js-ui' 	=> (string) $custom_ui // load custom JS-UI (jqueryUI if available)
		]);
		//--

		//$this->forceRawDebug(); // force debug profiler for raw pages (a raw page is not shown by default in Debug Profiler, must be explicit forced to be displayed in Debug Profiler)

	} //END FUNCTION

} //END CLASS


/**
 * Admin Controller (optional)
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