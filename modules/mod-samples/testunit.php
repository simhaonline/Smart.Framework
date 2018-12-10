<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/TestUnit
// Route: ?/page/samples.testunit (?page=samples.testunit)
// Author: unix-world.org
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

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
			case 'testunit.pcache-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitPCache::testPersistentCache();
				//--
				break;
			case 'testunit.pgsql-server-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitPgSQL::testPgServer();
				//--
				break;
			case 'testunit.mongodb-server-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = \SmartModExtLib\Samples\TestUnitMongoDB::testMongoServer();
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
				$main = '<script>'.SmartComponents::js_code_init_away_page().'</script>';
				$main .= SmartComponents::html_jsload_htmlarea();
				$main .= SmartComponents::html_js_htmlarea('test_html_area', 'test_html_area', '', '920px', '500px');
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
				$main = '<script>'.SmartComponents::js_code_init_away_page('The changes will be lost !').'</script>';
				$main .= SmartComponents::html_jsload_editarea();
				$main .= SmartComponents::html_js_editarea('test_code_editor', 'test_code_editor', '', (string)$mode, true, '920px', '450px'); // html
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
				$is_modal = false;
				if($this->IfRequestModalPopup() OR $this->IfRequestPrintable()) {
					$is_modal = true;
					$this->PageViewSetCfg('template-file', 'template-modal.htm');
				} //end if
				//--
				$main = \SmartModExtLib\Samples\TestUnitMain::mainScreen(
					$this->RequestVarGet('tab'),
					$this->RequestVarGet('frm'),
					$this->RequestVarGet('testformdata')
				);
				//--
				if(!$is_modal) {
					if($this->IfDebug()) {
						$this->SetDebugData('TestUnit.Main', 'Loading all staticload libs at once for test purposes ...');
					} //end if
				} //end if
				//--
				break;
		//#####
			case 'test.phpinfo':
				//--
				if(SMART_FRAMEWORK_TESTUNIT_ALLOW_FS_TESTS === true) { // if trusted environment
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
				$main = SmartComponents::js_code_highlightsyntax('body'); // highlight js
				$main .= '<h1>Markdown Syntax Render Test</h1><hr>';
				$main .= SmartMarkersTemplating::render_template(
					(string) (new SmartMarkdownToHTML())->text((string)SmartFileSystem::read($this->ControllerGetParam('module-view-path').'markdown-test.md')),
					[
						'TITLE' => 'This is a <test> title that comes from TPL variables'
					]
				);
				$main .= '<hr>';
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
			case 'test.load-url':
				//--
				$browser = (array) SmartUtils::load_url_or_file('http://www.unix-world.org', 20, 'GET');
				if(($browser['result'] != 1) OR ($browser['code'] != 200)) {
					$this->PageViewSetErrorStatus(502, 'Browsing failed for the given URL :: Result: '.$browser['result'].' ; Status-Code: '.(int)$browser['code']);
					unset($browser);
					return;
				} else {
					$main = (string) '<h1>Load URL: OK '.$browser['code'].'</h1><pre style="background:#ECECEC">'.Smart::escape_html($browser['content']).'</pre>';
					unset($browser);
				} //end if else
				//--
				break;
			case 'test.load-secure-url':
				//--
				$browser = (array) SmartUtils::load_url_or_file('https://www.unix-world.org', 20, 'GET', 'tls');
				if(($browser['result'] != 1) OR ($browser['code'] != 200)) {
					$this->PageViewSetErrorStatus(502, 'Browsing failed for the given URL :: Result: '.$browser['result'].' ; Status-Code: '.(int)$browser['code']);
					unset($browser);
					return;
				} else {
					$main = (string) '<h1>Load Secure URL: OK '.$browser['code'].'</h1><pre style="background:#ECECEC">'.Smart::escape_html($browser['content']).'</pre>';
					unset($browser);
				} //end if else
				//--
				break;
			case 'test.ods':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$oo = new SmartExportToOpenOffice();
				$this->PageViewSetCfg('rawmime', $oo->ODS_Mime_Header());
				$this->PageViewSetCfg('rawdisp', $oo->ODS_Disposition_Header('myfile.ods', 'attachment'));
				$main = $oo->ODS_SpreadSheet(
					'A Table',
					array('<column 1>', 'column " 2', 'column & 3'),
					array('data 1.1', 'data 1.2', 1.30, 'data 2.1', 'data 2.2', 2.31),
					array('', '', 'decimal4')
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
		$this->PageViewSetVars([
			'title' => 'Smart.Framework Test and Demo Suite',
			'main' => $main,
			'semaphore' => ((SMART_FRAMEWORK_ADMIN_AREA === true) && (SmartAppInfo::TestIfModuleExists('mod-ui-jqueryui'))) ? 'jqueryui' : ''
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
class SmartAppIndexController extends SmartAppAdminController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php
	// or this can implement a completely different controller if it is accessed via admin.php

} //END CLASS


//end of php code
?>