<?php
// [LIB - SmartFramework / Samples / Test Unit Main]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

// Class: \SmartModExtLib\Samples\TestUnitMain
// Type: Module Library
// Info: this class integrates with the default Smart.Framework modules autoloader so it does not need anything else to be setup

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
 * Test Unit Main (Misc Tests)
 *
 * @access 		private
 * @internal
 *
 * @version 	v.180217
 *
 */
final class TestUnitMain {

	// ::


	//============================================================
	public static function mainScreen($tab, $frm, $frx) {

		//--
		if(!defined('SMART_FRAMEWORK_TESTUNIT_BASE_URL')) {
			//--
			http_response_code(500);
			die((string)\SmartComponents::http_message_500_internalerror('ERROR: TEST UNIT BASE URL has not been defined ...'));
			//--
		} //end if
		//--

		//--
		$tab = (int) $tab;
		$frm = (array) $frm;
		$frx = (array) $frx;
		//--

		//--
		if(\Smart::array_size($frx) > 0) { // test form data :: because is modal we have to close it in order to refresh the parent
			//--
			return '<table><tr><td><h1>Form Sent (Test) !</h1><hr><pre>'.\Smart::escape_html(print_r($frx,1)).'</pre></td></tr></table><script>SmartJS_BrowserUtils.RefreshParent();</script><br><br><input class="ux-button" id="myCloseButton" type="button" value="[Close Me]" onClick="SmartJS_BrowserUtils.CloseModalPopUp(); return false;"><br><br><b>This page will auto-close in 9 seconds [Counting: <span id="mycounter">9</span>]</b><script>SmartJS_BrowserUtils.CountDown(9, \'mycounter\', \'SmartJS_BrowserUtils.CloseDelayedModalPopUp(500);\');</script><br><br><b><i>After closing this window, parent will refresh ...</i></b>';
			//--
		} //end if
		//--

		//-- normal form with modal / popup
		$basic_form_start 	= '<form class="ux-form ux-inline-block" id="form_for_test" action="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.main&tab=1'.'&'.SMART_FRAMEWORK_URL_PARAM_MODALPOPUP.'='.SMART_FRAMEWORK_URL_VALUE_ENABLED.'" method="post" target="_blank"><input type="hidden" name="testformdata[test]" value="Testing ..."><input type="hidden" name="testformdata[another-test]" value="Testing more ...">';
		$basic_form_end 	= '</form>';
		//--
		$basic_form_send_modal = '<input class="ux-button ux-button-primary" style="min-width:320px;" type="submit" value="Submit Form (with Confirmation / Modal)" OnClick="'.\SmartComponents::js_code_confirm_form_submit('<div align="left"><h3><b>Are you sure you want to submit this form [MODAL] ?</b></h3></div>', 'my_form').'">';
		$basic_form_send_popup = '<input class="ux-button ux-button-secondary" style="min-width:320px;" type="submit" value="Submit Form (with Confirmation / PopUp)" OnClick="'.\SmartComponents::js_code_confirm_form_submit('<div align="left"><h3><b>Are you sure you want to submit this form [POPUP] ?</b></h3></div>', 'my_form', '780', '420', '1').'">';
		//--

		//-- ajax post form
		$btnop = '<button title="Submit this Test Form by AJAX (with Confirmation)" class="ux-button ux-button-large ux-button-special" onClick="'.\SmartComponents::js_ajax_submit_html_form('test_form_ajax', SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.post-form-by-ajax&tab=2', '<h2>Are you sure you want to submit this form by Ajax !?</h2>', 'jQuery(\'#smart__CaptchaFrm__img\').click();').' return false;">Submit this Test Form by AJAX &nbsp; <span class="fa fa-send"></span></button>';
		//-- end

		//-- lists with one element
		$one_single_select 				= \SmartComponents::html_select_list_single('test-unit-s-list-one', '', 'form', array('one' => 'One'), 'frm[one_single]', '150', '', 'no', 'no', '#JS-UI#'); // returns HTML Code
		$one_single_with_blank_select 	= \SmartComponents::html_select_list_multi('test-unit-lst-m-1', '', 'form', array('one' => 'One'), 'frm[one_multi][]', 'list', 'no', '200', '', '#JS-UI-FILTER#'); // returns HTML Code
		//--
		$test_normal_list_s 			= \SmartComponents::html_select_list_single('test_normal_s', '', 'form', [1 => 'Val 1', 2 => 'Val 2', 3 => 'Val 3', 4 => 'Val 4', 5 => 'Val 5']);
		$test_normal_list_m 			= \SmartComponents::html_select_list_multi('test_normal_m', '', 'form', [1 => 'Val 1', 2 => 'Val 2', 3 => 'Val 3', 4 => 'Val 4', 5 => 'Val 5'], '', 'list', 'no', '200/75', '', 'height:65px;');
		//--

		//-- misc purpose data array
		$array_of_values = array();
		$array_of_values[] = 'a&"/><i>Italic</i></body>';
		$array_of_values[] = 'a&"/><i>Italic</i></body>';
		$array_of_values[] = '#OPTGROUP#';
		$array_of_values[] = 'Labels';
		for($i=1; $i<=500; $i++) {
			$array_of_values[] = 'id'.$i;
			$array_of_values[] = 'Label '.$i;
		} //end for
		//--

		//-- single-select
		$selected_value 	= 'id2';
		$elem_single_select = \SmartComponents::html_select_list_single('test-unit-s-list-two', $selected_value, 'form', $array_of_values, 'frm[list_single]', '150', 'onChange="alert(\''.\Smart::escape_js('Getting value from the "SingleList": ').'\' + $(\'#test-unit-s-list-two\').val());"', 'no', 'yes', '#JS-UI-FILTER#'); // returns HTML Code
		//--

		//-- draw a multi-select (classic)
		$selected_values 	= '<id1>,<id3>';
		$elem_multi_select 	= \SmartComponents::html_select_list_multi('test-unit-m-list-2', $selected_values, 'form', $array_of_values, 'frm[list_multi_one][]', 'list', 'no', '250', 'onBlur="alert(\''.\Smart::escape_js('Getting value from the:'."\n".' "MultiList": ').'\' + $(\'#test-unit-m-list-2\').val());"', '#JS-UI-FILTER#'); // returns HTML Code
		//--

		//-- multi-select (checkboxes)
		$array_of_values 	= array('id1' => 'Label 1', 'id2' => 'Label 2', 'id3' => 'Label 3');
		$selected_values 	= array('id2', 'id3');
		$elem_multi_boxes 	= \SmartComponents::html_select_list_multi('test-unit-m-list-3', $selected_values, 'form', $array_of_values, 'frm[list_multi_two][]', 'checkboxes'); // returns HTML Code
		//--

		//--
		if(SMART_FRAMEWORK_ADMIN_AREA === true) {
			$info_adm = '[ Admin Area ]';
			$info_pfx = 'adm';
		} else {
			$info_adm = '[ Index ]';
			$info_pfx = 'idx';
		} //end if else
		//--

		//--
		$demo_mod_ext_toolkits = '';
		$demo_mod_ext_components = '';
		if(\SmartAppInfo::TestIfModuleExists('mod-ui-uikit')) {
			$demo_mod_ext_toolkits .= \SmartFileSystem::read('modules/mod-ui-uikit/testunit/templates/tab-ui-components.inc.htm');
		} //end if
		if(\SmartAppInfo::TestIfModuleExists('mod-ui-bootstrap')) {
			$demo_mod_ext_toolkits .= \SmartFileSystem::read('modules/mod-ui-bootstrap/testunit/templates/tab-ui-components.inc.htm');
		} //end if
		if(\SmartAppInfo::TestIfModuleExists('mod-wflow-components')) {
			$demo_mod_ext_components .= \SmartFileSystem::read('modules/mod-wflow-components/testunit/templates/tab-ui-components.inc.htm');
		} //end if
		if(\SmartAppInfo::TestIfModuleExists('mod-js-components')) {
			$demo_mod_ext_components .= \SmartFileSystem::read('modules/mod-js-components/testunit/templates/tab-ui-components.inc.htm');
		} //end if
		//--
		$demo_mod_ui_components = \SmartMarkersTemplating::render_file_template(
			'modules/mod-samples/libs/templates/testunit/test-unit-tab-components.inc.htm',
			[
				'TESTUNIT_BASE_URL' 	=> (string) SMART_FRAMEWORK_TESTUNIT_BASE_URL,
				'EXTERNAL-TOOLKITS' 	=> (string) $demo_mod_ext_toolkits,
				'EXTERNAL-COMPONENTS' 	=> (string) $demo_mod_ext_components
			]
		);
		//--

		//--
		$arr_bw = (array) \SmartComponents::get_imgdesc_by_bw_id(\SmartUtils::get_os_browser_ip('bw'));
		$tpl_path = 'modules/mod-samples/libs/templates/testunit';
		//--
		return \SmartMarkersTemplating::render_file_template( // rendering a complex template with hardcoded sub templates
			'modules/mod-samples/libs/templates/testunit/test-unit.inc.htm',
			[
				'@SUB-TEMPLATES@' => [
					'test-unit-tab-tests.inc.htm' 			=> (string) \SmartFileSysUtils::add_dir_last_slash((string)$tpl_path), 	// dir with trailing slash
					'test-unit-tab-interractions.inc.htm' 	=> (string) $tpl_path, 													// dir without trailing slash
					'test-unit-tab-forms.inc.htm' 			=> '@', 																// @ (self) path, assumes the same dir
					'%test-unit-tab-templating%'			=> '@/test-unit-tab-templating.inc.htm'									// variable, with full path, using self @/sub-dir/ instead of $tpl_path/test-unit-tab-misc.htm
				],
				'TEST-URL-UNICODE-STR' 						=> (string) \SmartModExtLib\Samples\TestUnitStrings::testStr(),
				'TEST-UNIT-AREA' 							=> (string) $info_pfx,
				'TESTUNIT-TPL-PATH' 						=> (string) \SmartFileSysUtils::add_dir_last_slash((string)$tpl_path), 	// this MUST be with trailing slash
				'TESTUNIT_BASE_URL' 						=> (string) SMART_FRAMEWORK_TESTUNIT_BASE_URL,
				'NO-CACHE-TIME' 							=> (string) time(),
				'CURRENT-DATE-TIME' 						=> (string) date('Y-m-d H:i:s O'),
				'TEST-JS_SCRIPTS_Init-Tabs' 				=> '<script type="text/javascript">'.\SmartComponents::js_code_uitabs_init('tabs_draw', \Smart::format_number_int($tab,'+')).'</script>', // '<script type="text/javascript">'.\SmartComponents::js_code_uitabs_activate('tabs_draw', false).'</script>',
				'Test-Buttons_AJAX-POST' 					=> (string) $btnop,
				'TEST-VAR'  								=> '<div style="background-color: #ECECEC; padding: 10px;"><b>Smart.Framework</b> :: PHP/Javascript web framework :: Test and Demo Suite @ '.$info_adm.'</div>',
				'TEST-ELEMENTS_DIALOG' 						=> '<a class="ux-button ux-button-dark" style="min-width:320px;" href="#" onClick="'.\SmartComponents::js_code_ui_confirm_dialog('<h1>Do you like this framework ?</h1><br>Option: <select id="test-dlg-select-el-sf"><option value="Yes">Yes</option><option value="No">No</option></select>', 'alert(\'Well ... then you selected the value: [\' + $(\'#test-dlg-select-el-sf\').val() + \'] ... \\\' " <tag> !\');').' return false;">Test JS-UI Dialog</a>',
				'TEST-ELEMENTS_ALERT' 						=> '<a class="ux-button" style="min-width:320px;" href="#" onClick="'.\SmartComponents::js_code_ui_alert_dialog('<h2>You can press now OK !</h2><br>Option: <select id="test-dlg-select-el-sf"><option value="One">One</option><option value="Two">Two</option></select>', 'alert(\'Good ... you selected the value: [\' + $(\'#test-dlg-select-el-sf\').val() + \'] ... \\\' " <tag> !\');').' return false;">Test JS-UI Alert</a>',
				'TEST-ELEMENTS_SEND-CONFIRM-MODAL' 			=> (string) $basic_form_start.$basic_form_send_modal.$basic_form_end,
				'TEST-ELEMENTS_SEND-CONFIRM-POPUP' 			=> (string) $basic_form_start.$basic_form_send_popup.$basic_form_end,
				'TEST-ELEMENTS-WND-INTERRACTIONS-MODAL' 	=> (string) \SmartModExtLib\Samples\TestUnitBrowserWinInterractions::bttnModalTestInit(),
				'TEST-ELEMENTS-WND-INTERRACTIONS-POPUP' 	=> (string) \SmartModExtLib\Samples\TestUnitBrowserWinInterractions::bttnPopupTestInit(),
				'TEST-ELEMENTS_SINGLE-SELECT' 				=> 'SingleSelect DropDown List without Blank: '.$one_single_select,
				'TEST-ELEMENTS_SINGLE-BLANK-SELECT' 		=> 'SingleSelect DropDown List (from Multi): '.$one_single_with_blank_select,
				'TEST-ELEMENTS_SINGLE-SEARCH-SELECT' 		=> 'SingleSelect DropDown List with Search: '.$elem_single_select,
				'TEST-ELEMENTS_MULTI-SELECT' 				=> 'MultiSelect DropDown List: '.$elem_multi_select,
				'TEST-ELEMENTS_MULTIBOX-SELECT' 			=> 'MultiSelect CheckBoxes:<br>'.$elem_multi_boxes,
				'TEST-ELEMENTS_NORMAL-LIST-S' 				=> (string) $test_normal_list_s,
				'TEST-ELEMENTS_NORMAL-LIST-M' 				=> (string) $test_normal_list_m,
				'TEST-ELEMENTS_CALENDAR' 					=> 'Calendar Selector: '.\SmartComponents::html_js_date_field('frm_calendar_id', 'frm[date]', \Smart::escape_html($frm['date']), 'Select Date', "'0d'", "'1y'", '', 'alert(\'You selected the date: \' + date);'),
				'TEST-ELEMENTS_TIMEPICKER' 					=> 'TimePicker Selector: '.\SmartComponents::html_js_time_field('frm_timepicker_id', 'frm[time]', \Smart::escape_html($frm['time']), 'Select Time', '9', '19', '0', '55', '5', '3', '', 'alert(\'You selected the time: \' + time);'),
				'TEST-ELEMENTS_AUTOCOMPLETE-SINGLE' 		=> 'AutoComplete Single: '.'<input id="auto-complete-fld" type="text" name="frm[autocomplete]" style="width:75px;"><script type="text/javascript">'.\SmartComponents::js_code_init_select_autocomplete_single('auto-complete-fld', SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.autocomplete', 'src', 1, 'alert(\'You selected: \' + value);').'</script>',
				'TEST-ELEMENTS_AUTOCOMPLETE-MULTI'			=> 'Autocomplete Multi: '.'<input id="auto-complete-mfld" type="text" name="frm[mautocomplete]" style="width:125px;"><script type="text/javascript">'.\SmartComponents::js_code_init_select_autocomplete_multi('auto-complete-mfld', SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.autocomplete', 'src', 1, 'alert(\'You selected: \' + value);').'</script>',
				'TEST-elements_Captcha' 					=> (string) \SmartCaptchaFormCheck::captcha_form(SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.captcha', self::captchaFormName()),
				'test-elements_limited-area' 				=> '<div>Limited TextArea:</div>'.\SmartComponents::html_js_limited_text_area('', 'frm[text_area_1]', '', 300, '400px', '90px'),
				'POWERED-INFO' 								=> (string) \SmartComponents::app_powered_info('no', [ [], [ 'type' => 'cside', 'name' => $arr_bw['desc'], 'logo' => \SmartUtils::get_server_current_url().$arr_bw['img'], 'url' => '' ] ]),
				'STR-NUM' 									=> '1abc', // this will be converted to num !!
				'NUM-NUM' 									=> '0.123456789',
				'IFTEST' 									=> \Smart::random_number(1,2),
				'IF2TEST' 									=> \Smart::random_number(0,9),
				'LOOPTEST-VAR1' => (array) [
						[
							'd1' => 'Column 1.x (HTML Escape)',
							'd2' => 'Column 2.x (JS Escape)',
							'd3' => 'Column 3.x (URL Escape)'
						]
				],
				'LOOPTEST-VAR2' => (array) [
						[
							'c1' => '<Column 1.1>',
							'c2' => 'Column 1.2'."\n",
							'c3' => 'Column 1.3'."\t"
						],
						[
							'c1' => '<Column 2.1>',
							'c2' => 'Column 2.2'."\n",
							'c3' => 'Column 2.3'."\t"
						],
						[
							'c1' => '<Column 3.1>',
							'c2' => 'Column 3.2'."\n",
							'c3' => 'Column 3.3'."\t"
						],
						[
							'c1' => \Smart::random_number(0,1),
							'c2' => 'a',
							'c3' => 'A'
						]
				],
				'TPL-SYNTAX-DESCR' 							=> (string) \SmartMarkersTemplating::prepare_nosyntax_html_template(\SmartFileSystem::read('modules/mod-samples/libs/templates/testunit/partials/test-tpl-syntax-desc.nosyntax.inc.htm'), true),
				'TEST-UI-COMPONENTS' 						=> (string) $demo_mod_ui_components,
				'TWIG-AVAILABLE' 							=> (string) (\SmartAppInfo::TestIfModuleExists('mod-tpl-twig') ? 'yes' : 'no')
			]
		);
		//--

	} //END FUNCTION
	//============================================================


	//============================================================
	public static function formReplyJson($tab, $frm) {

		//--
		$tab = (int) $tab;
		$frm = (array) $frm;
		//--

		//--
		$tmp_data = '<br><br><hr><pre>'.'GET:'.'<br>'.\Smart::escape_html(print_r(\SmartFrameworkSecurity::FilterGetPostCookieVars($_GET),1)).'<hr>'.'POST:'.'<br>'.\Smart::escape_html(print_r(\SmartFrameworkSecurity::FilterGetPostCookieVars($_POST),1)).'</pre>';
		//--

		//--
		if(\SmartCaptchaFormCheck::verify(self::captchaFormName(), self::captchaMode(), false) == 1) { // verify but do not clear yet
			$captcha_ok = true;
		} else {
			$captcha_ok = false;
		} //end if else
		//--

		//--
		$evcode = '';
		//--
		if(strlen($frm['date']) > 0) {
			//--
			if($captcha_ok !== true) {
				//--
				$code = 'ERROR';
				$title = 'CAPTCHA verification FAILED ...';
				$desc = 'Please enter a valid captcha value:'.$tmp_data;
				$redir = '';
				$div_id = '';
				$div_htm = '';
				//--
			} else {
				//--
				$code = 'OK';
				$title = 'Captcha validation OK ... The page or just the Captcha will be refreshed depending if TextArea is filled or not ...';
				$desc = 'Form sent successful:'.$tmp_data;
				//--
				if(strlen($frm['text_area_1']) <= 0) {
					$evcode = 'alert(\'The page will be redirected shortly (because the request answer set it - custom action) ...\');';
					$redir = SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.main&time='.time().'&tab='.\Smart::escape_url($tab);
					$div_id = '';
					$div_htm = '';
				} else {
					$redir = '';
					$div_id = 'answer_ajax';
					$div_htm = '<script>jQuery("#Smart-Captcha-Img img:first").attr("src", "'.\Smart::escape_js(SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.captcha&time='.time()).'");</script><table border="0" bgcolor="#DDEEFF" width="100%"><tr><td><h1>OK, form sent on: '.date('Y-m-d H:i:s').'</h1></td></tr><tr><td><div align="left"><img width="64" src="lib/framework/img/sign-ok.svg"></div><div><a data-smart="open.modal" href="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'test.markdown" target="testunit-json-test">Test Link 1 (modal link)</a><br><a href="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'test.json" target="_blank">Test Link 2 (default link)</a><br><a data-slimbox="slimbox" title="Image 3" href="?page=samples.test-image"><img src="?page=samples.test-image" alt="Click to Test Image Gallery" title="Click to Test Image Gallery"></a></div></td></tr><tr><td><hr><b>Here is the content of the text area:</b><br><pre>'.\Smart::escape_html($frm['text_area_1']).'</pre></td></tr></table>';
				} //end if else
				//--
				\SmartCaptchaFormCheck::clear(self::captchaFormName(), self::captchaMode()); // everything OK, so clear captcha
				//--
			} //end if else
			//--
		} else {
			//--
			$code = 'ERROR';
			$title = 'CAPTCHA NOT Checked yet ...';
			$desc = 'Please fill the Date field ...'.$tmp_data;
			//--
			if(strlen($frm['text_area_1']) > 0) {
				$redir = SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.main&time='.time().'&tab='.\Smart::escape_url($tab);
			} else {
				$redir = '';
			} //end if else
			//--
			$div_id = '';
			$div_htm = '';
			//--
		} //end if else
		//--

		//--
		return \SmartComponents::js_ajax_replyto_html_form($code, $title, $desc, $redir, $div_id, $div_htm, $evcode); // mixed output (json)
		//--

	} //END FUNCTION
	//============================================================


	//============================================================
	public static function captchaImg($type) {
		//--
		return (string) \SmartCaptchaFormCheck::captcha_image(
			(string) self::captchaFormName(),
			(string) self::captchaMode(),
			\Smart::random_number(0,1) ? 'dotted' : 'hashed',
			'0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			'300',
			'5',
			'175',
			'50',
			(string) $type // image type: gif / png / jpg
		);
		//--
	} //END FUNCTION
	//============================================================


	//##### PRIVATES


	//============================================================
	private static function captchaMode() {
		//--
		if((string)SMART_FRAMEWORK_TESTUNIT_CAPTCHA_MODE == 'session') {
			return 'session';
		} else {
			return 'cookie';
		} //end if else
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	private static function captchaFormName() {
		//--
		return ' Test_Unit-Ajax-Form-forCaptcha_'.date('Y').' '; // test value with all allowed characters and some spaces (that spaces are presumed to be trimmed ...)
		//--
	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>