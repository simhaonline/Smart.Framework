<?php
// Controller: Samples/TestUnit
// Route: ?/page/samples.testunit (?page=samples.testunit)
// Author: unix-world.org
// v.3.1.1 r.2017.04.10 / smart.framework.v.3.1

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
class SmartAppAdminController extends SmartAbstractAppController {

	public function Run() {

		//--
		require_once('lib/core/lib_smart_test_suite.php');			// test suite
		//--

		//--
		SmartSession::start(); // start the session
		//--

		//--
		if(SmartPersistentCache::isActive()) {
			SmartPersistentCache::getKey('test-unit', 'version'); // just test if redis re-uses the connection ...
		} //end if
		//--

		//--
		$op = $this->RequestVarGet('op', 'testunit.main', 'string');
		//--
		switch((string)$op) {
			case 'testunit.phpinfo':
				//--
				$this->PageViewSetCfg('rawpage', true);
				ob_start();
				phpinfo();
				$main = ob_get_contents();
				ob_end_clean();
				break;
			case 'testunit.captcha':
				//--
				$this->PageViewSetCfg('rawpage', 'yes'); // should work both: true or 'yes'
				$this->PageViewSetCfg('rawmime', 'image/png');
				$this->PageViewSetCfg('rawdisp', 'inline');
				$main = SmartTestSuite::test_captcha('png');
				//--
				break;
			case 'testunit.post-form-by-ajax':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::post__answer__by__ajax(
					$this->RequestVarGet('tab'),
					$this->RequestVarGet('frm')
				);
				//--
				break;
			case 'testunit.strings-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_strings();
				//--
				break;
			case 'testunit.crypto-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_crypto();
				//--
				break;
			case 'testunit.filesys-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_fs();
				//--
				break;
			case 'testunit.pgsql-server-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_pgsqlserver();
				//--
				break;
			case 'testunit.redis-server-test':
				//--
				sleep(1);
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_redisserver();
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
				$main = SmartTestSuite::test_sqlite3_json_smartgrid($ofs, $sortby, $sortdir, $sorttype, $src);
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
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				$main = '<script>'.SmartComponents::js_code_init_away_page('The changes will be lost !').'</script>';
				$main .= SmartComponents::html_jsload_editarea();
				$main .= SmartComponents::html_js_editarea('test_code_editor', 'test_code_editor', '', 'html', true, '920px', '450px');
				//--
				break;
			case 'testunit.load-url-or-file':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::load__url__or__file('http://www.unix-world.org');
				//--
				break;
			case 'testunit.barcodes-qrcode':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_barcode2d_qrcode();
				//--
				break;
			case 'testunit.barcodes-semcode':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_barcode2d_datamatrix();
				//--
				break;
			case 'testunit.barcodes-pdf417':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_barcode2d_pdf417();
				//--
				break;
			case 'testunit.barcodes-code128':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_barcode1d_128B();
				//--
				break;
			case 'testunit.barcodes-code93':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_barcode1d_93();
				//--
				break;
			case 'testunit.barcodes-code39':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_barcode1d_39();
				//--
				break;
			case 'testunit.barcodes-rm4kix':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_barcode1d_kix();
				//--
				break;
			case 'testunit.charts-biz':
				//--
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('expires', 120); // cache expire test
				//--
				$chart = new SmartImgBizCharts(
					'matrix',
					'Marketing Chart',
					array(
						'Chart 1' => array(
							'red label' => array('x'=>Smart::random_number(5,7), 'y'=>Smart::random_number(100,120), 'z'=>Smart::random_number(45,75), 'color'=>'#FF3300'),
							'blue' => array('x'=>Smart::random_number(100,115), 'y'=>Smart::random_number(200,210), 'z'=>Smart::random_number(20,50), 'color'=>'#003399'),
							'green' => array('x'=>Smart::random_number(150,175), 'y'=>Smart::random_number(250,270), 'z'=>Smart::random_number(2,8), 'color'=>'#33CC33', 'labelcolor'=>'#11AA11'),
							'yellow' => array('x'=>Smart::random_number(400,420), 'y'=>Smart::random_number(70,90), 'z'=>Smart::random_number(50,90), 'color'=>'#FFCC00'),
							'default' => array('x'=>Smart::random_number(300,325), 'y'=>Smart::random_number(300,320))
						)
					),
					'png'
				);
				$chart->width = 500;
				$chart->height = 500;
				$chart->axis_x_label = 'Relative Market Share';
				$chart->axis_y_label = 'Market Growth Rate';
				//--
				$this->PageViewSetCfg('rawmime', $chart->mime_header());
				$this->PageViewSetCfg('rawdisp', $chart->disposition_header());
				$main = $chart->generate();
				//--
				break;
			case 'testunit.charts-gfx':
				//--
				$this->PageViewSetCfg('rawpage', true);
				//--
				$showgraph2 = Smart::random_number(0,1);
				$showgraphdepths = Smart::random_number(0,1);
				$showtype = Smart::random_number(1,6);
				switch((string)$showtype) {
					case 1:
						$mode = 'vbars';
						break;
					case 2:
						$mode = 'hbars';
						break;
					case 3:
						$mode = 'dots';
						break;
					case 4:
						$mode = 'lines';
						break;
					case 5:
						$mode = 'pie';
						break;
					case 6:
					default:
						$mode = 'donut';
				} //end if
				//--
				$chart = new SmartImgGfxCharts(
					$mode,
					"Type [".$mode."]",
					array(
						array(
							'x' => "white",
							'y' => Smart::random_number(10,90),
							'z' => Smart::random_number(10,90),
							'w' => 10,
							'v' => '#ECECEC'
						),
						array(
							'x' => "red",
							'y' => 22.45,
							'z' => Smart::random_number(10,90),
							'w' => 25,
							'v' => '#FF3300'
						),
						array(
							'x' => "blue",
							'y' => Smart::random_number(10,90),
							'z' => Smart::random_number(10,90),
							'w' => 7,
							'v' => '#003399'
						),
						array(
							'x' => "yellow",
							'y' => Smart::random_number(10,90),
							'z' => Smart::random_number(10,90),
							'w' => 17,
							'v' => '#FFCC00'
						),
						array(
							'x' => "green",
							'y' => Smart::random_number(10,90),
							'z' => Smart::random_number(10,90),
							'w' => 31,
							'v' => '#33CC33'
						),
						array(
							'x' => "black",
							'y' => Smart::random_number(10,90),
							'z' => Smart::random_number(10,90),
							'w' => 17,
							'v' => '#333333'
						)
					),
					'png',
					$showgraph2,
					$showgraphdepths
				);
				$chart->axis_x = 'X-Axis';
				$chart->axis_y = 'Y-Axis';
				//--
				$this->PageViewSetCfg('rawmime', $chart->mime_header());
				$this->PageViewSetCfg('rawdisp', $chart->disposition_header());
				$main = $chart->generate();
				//--
				break;
			case 'testunit.ods':
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
			case 'testunit.json-test':
				//--
				$mixed_data = ['Unicode Text' => '"Unicode78źź:ăĂîÎâÂșȘțȚşŞţŢグッド\'#@<tag>!$%^&*()-_=+'."\r\n\t".'</tag>', 'Numbers' => 1234567890.99, 'Boolean TRUE:' => true, 'Boolean FALSE:' => false];
				//--
				$main = '<h1> Json Test</h1>';
				$main .= '<pre style="background:#ECECEC; border:1px solid #CCCCCC; line-height:32px; padding:8px;">';
				$main .= '<b>Default (Unicode Unescaped) Json:</b>'."\n".Smart::json_encode($mixed_data)."\n";
				$main .= '<hr>';
				$main .= '<b>Default (Unicode Unescaped) Json / Pretty Print:</b>'."\n".Smart::json_encode($mixed_data, true)."\n";
				$main .= '<hr>';
				$main .= '<b>Unicode Escaped Json:</b>'."\n".Smart::json_encode($mixed_data, false, false)."\n";
				$main .= '<hr>';
				$main .= '<b>Unicode Escaped Json / Pretty Print:</b>'."\n".Smart::json_encode($mixed_data, true, false)."\n";
				$main .= '</pre>';
				//--
				break;
			case 'testunit.interractions':
				//--
				$this->PageViewSetCfg('template-file', 'template-modal.htm');
				$main = SmartTestSuite::test_interractions(
					$this->RequestVarGet('mode')
				);
				//--
				break;
			case 'testunit.autocomplete':
				//--
				$src = $this->RequestVarGet('src', '', 'string');
				//--
				$this->PageViewSetCfg('rawpage', true);
				$main = SmartTestSuite::test_sqlite3_json_autocomplete($src);
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
				$main = SmartTestSuite::main_screen(
					$this->RequestVarGet('tab'),
					$this->RequestVarGet('frm'),
					$this->RequestVarGet('testformdata')
				);
				//--
				if(!$is_modal) {
					SmartTestSuite::test_load_libs(); // just for testing all libs
					if($this->IfDebug()) {
						$this->SetDebugData('TestUnit.Main', 'Loading all staticload libs at once for test purposes ...');
					} //end if
				} //end if
				//--
				break;
			default:
				//--
				$this->PageViewSetCfg('error', 'Invalid TestUnit Operation ! ...');
				return 400;
				//--
		} //end switch
		//--

		//--
		$this->PageViewSetVars(array(
			'title' => 'Test Suite',
			'main' => $main
		));
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