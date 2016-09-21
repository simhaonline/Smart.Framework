<?php
// [LIB - SmartFramework / Smart Test Suite]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.5.3 r.2016.08.23 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_APP_BOOTSTRAP')) { // this must be defined in the first line of the application
	die('Invalid Runtime App Bootstrap Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Smart Test Suite
// DEPENDS: SmartFramework + SmartComponents
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class Smart Test Suite Main
 *
 * @access 		private
 * @internal
 *
 */
final class SmartTestSuite {

	// ::
	// v.160921


//==================================================================
public static function test_load_libs() {
	//--
	require_once('lib/core/plugins/staticload.php'); // this is for test, otherwise it does autoload dependency injection
	if(is_file('modules/smart-extra-libs/staticload.php')) {
		require_once('modules/smart-extra-libs/staticload.php');
	} //end if
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function main_screen($tab, $frm, $testformdata) {

	//--
	global $configs;
	//--

	//--
	if(!defined('SMART_FRAMEWORK_TESTUNIT_BASE_URL')) {
		http_response_code(500);
		die(SmartComponents::http_message_500_internalerror('ERROR: TEST UNIT BASE URL has not been defined ...'));
	} //end if
	//--

	//--
	if(Smart::array_size($testformdata) > 0) { // because is modal we have to close it in order to refresh the parent
		return '<table><tr><td><h1>Form Sent (Test) !</h1><hr><pre>'.Smart::escape_html(print_r($testformdata,1)).'</pre></td></tr></table><script>SmartJS_BrowserUtils.RefreshParent();</script><br><br><input id="myCloseButton" type="button" value="[Close Me]" onClick="SmartJS_BrowserUtils.CloseModalPopUp(); return false;"><br><br><b>This page will auto-close in 9 seconds [Counting: <span id="mycounter">9</span>]</b><script>jQuery("#myCloseButton").button(); SmartJS_BrowserUtils.CountDown(9, \'mycounter\', \'SmartJS_BrowserUtils.CloseDelayedModalPopUp(500);\');</script><br><br><b><i>After closing this window, parent will refresh ...</i></b>';
	} //end if
	//--

	//-- normal form with modal/popup
	$basic_form_start = '<form id="form_for_test" action="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.main&tab=1'.'&'.SMART_FRAMEWORK_URL_PARAM_MODALPOPUP.'='.SMART_FRAMEWORK_URL_VALUE_ENABLED.'" method="post" target="_blank"><input type="hidden" name="testformdata[test]" value="Testing ..."><input type="hidden" name="testformdata[another-test]" value="Testing more ...">';
	$basic_form_end = '</form>';
	//--
	$basic_form_send_modal = '<input class="ux-button ux-button-primary" style="min-width:325px;" type="submit" value="Submit Form (with Confirmation / Modal)" OnClick="'.SmartComponents::js_draw_html_confirm_form_submit('<div align="left"><h3><b>Are you sure you want to submit this form [MODAL] ?</b></h3></div>', 'my_form').'">';
	$basic_form_send_popup = '<input class="ux-button ux-button-secondary" style="min-width:325px;" type="submit" value="Submit Form (with Confirmation / PopUp)" OnClick="'.SmartComponents::js_draw_html_confirm_form_submit('<div align="left"><h3><b>Are you sure you want to submit this form [POPUP] ?</b></h3></div>', 'my_form', '780', '420', '1').'">';
	//--

	//-- AJAX POST FORM
	$btnop = '<button class="ux-button ux-button-large ux-button-highlight" onClick="'.SmartComponents::post_form_by_ajax('test_form_ajax', SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.post-form-by-ajax&tab=2', '<h2>Are you sure you want to submit this form by Ajax !?</h2>').' return false;">Submit this Test Form by AJAX &nbsp; <span class="fa fa-send"></span></button>';
	//-- END

	//-- lists with one element
	$one_single_select = SmartComponents::html_single_select_list('test-unit-s-list-one', '', 'form', array('one' => 'One'), 'frm[one_single]', '150', '', 'no', 'no', '#JS-UI#'); // returns HTML Code
	$one_single_with_blank_select = SmartComponents::html_multi_select_list('test-unit-lst-m-1', '', 'form', array('one' => 'One'), 'frm[one_multi][]', 'list', 'no', '200', '', '#JS-UI-FILTER#'); // returns HTML Code
	//--
	$test_normal_list_s = SmartComponents::html_single_select_list('test_normal_s', '', 'form', [1 => 'Val 1', 2 => 'Val 2', 3 => 'Val 3']);
	$test_normal_list_m = SmartComponents::html_multi_select_list('test_normal_m', '', 'form', [1 => 'Val 1', 2 => 'Val 2', 3 => 'Val 3'], '', 'list', 'no', '200/75', '', 'height:65px;');
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
	//-- single-select
	$selected_value = 'id2';
	$elem_single_select = SmartComponents::html_single_select_list('test-unit-s-list-two', $selected_value, 'form', $array_of_values, 'frm[list_single]', '150', 'onChange="alert(\''.Smart::escape_js('Getting value from the "SingleList": ').'\' + $(\'#test-unit-s-list-two\').val());"', 'no', 'yes', '#JS-UI-FILTER#'); // returns HTML Code
	//--
	// draw a multi-select (classic)
	$selected_values = '<id1>,<id3>';
	$elem_multi_select = SmartComponents::html_multi_select_list('test-unit-m-list-2', $selected_values, 'form', $array_of_values, 'frm[list_multi_one][]', 'list', 'no', '250', 'onBlur="alert(\''.Smart::escape_js('Getting value from the:'."\n".' "MultiList": ').'\' + $(\'#test-unit-m-list-2\').val());"', '#JS-UI-FILTER#'); // returns HTML Code
	//--
	// multi-select (checkboxes)
	$array_of_values = array('id1' => 'Label 1', 'id2' => 'Label 2', 'id3' => 'Label 3');
	$selected_values = array('id2', 'id3');
	$elem_multi_boxes = SmartComponents::html_multi_select_list('test-unit-m-list-3', $selected_values, 'form', $array_of_values, 'frm[list_multi_two][]', 'checkboxes'); // returns HTML Code
	//--

	//--
	if(SMART_FRAMEWORK_ADMIN_AREA === true) {
		$info_adm = '[ Admin Area ]';
	} else {
		$info_adm = '[ Index ]';
	} //end if else
	//--

	//--
	$demo_mod_js_components = '<h1>JS Components are not Installed ...</h1>';
	if(SmartAppInfo::TestIfModuleExists('mod-js-components')) {
		$demo_mod_js_components = SmartFileSystem::staticread('modules/mod-js-components/views/testunit/tab-js-components.inc.htm');
	} //end if
	//--

	//--
	return SmartMarkersTemplating::render_file_template(
		'lib/core/templates/testunit/test-unit.htm',
		array(
			'@SUB-TEMPLATES@' => [
				'test-unit-tab-tests.htm' 			=> 'lib/core/templates/testunit/', 						// dir with trailing slash
				'test-unit-tab-interractions.htm' 	=> 'lib/core/templates/testunit', 						// empty, expects the same dir as parent
				'test-unit-tab-ui.htm' 				=> '@', 												// @ (self) path, assumes the same dir
				'%test-unit-tab-misc%'				=> '@/test-unit-tab-misc.htm'							// variable, with full path, using self @/sub-dir/ instead of lib/core/templates/testunit/test-unit-tab-misc.htm
			],
			'TESTUNIT_BASE_URL' => SMART_FRAMEWORK_TESTUNIT_BASE_URL,
			'NO-CACHE-TIME' => time(),
			'CURRENT-DATE-TIME' => date('Y-m-d H:i:s O'),
			'TEST-JS_SCRIPTS.Init-Tabs' => SmartComponents::js_ajx_tabs_init('tabs_draw', Smart::format_number_int($tab,'+')), // .SmartComponents::js_ajx_tabs_activate('tabs_draw', false),
			'Test-Buttons.AJAX-POST' => $btnop,
			'TEST-VAR'  => '<div style="background-color: #ECECEC; padding: 10px;"><b>Smart.Framework</b> :: PHP/Javascript web framework :: '.$info_adm.' // Test Suite</div>',
			'TEST-ELEMENTS.DIALOG' => '<a class="ux-button" style="min-width:325px;" href="#" onClick="'.SmartComponents::js_draw_html_confirm_dialog('<h1>Do you like this framework ?</h1>', 'alert(\'Well ... then \\\' " <tag> !\');').' return false;">Test JS-UI Dialog</a>',
			'TEST-ELEMENTS.ALERT' => '<a class="ux-button" style="min-width:325px;" href="#" onClick="'.SmartComponents::js_draw_html_alert('<h2>You can press now OK !</h2>', 'alert(\'Good ... \\\' " <tag> !\');').' return false;">Test JS-UI Alert</a>',
			'TEST-ELEMENTS.SEND-CONFIRM-MODAL' => $basic_form_start.$basic_form_send_modal.$basic_form_end,
			'TEST-ELEMENTS.SEND-CONFIRM-POPUP' => $basic_form_start.$basic_form_send_popup.$basic_form_end,
			'TEST-ELEMENTS-WND-INTERRACTIONS-MODAL' => self::bttn_open_modal(true, 'test_interractions_modal_start'),
			'TEST-ELEMENTS-WND-INTERRACTIONS-POPUP' => self::bttn_open_popup(true, 'test_interractions_popup_start'),
			'TEST-ELEMENTS.SINGLE-SELECT' => 'SingleSelect DropDown List without Blank: '.$one_single_select,
			'TEST-ELEMENTS.SINGLE-BLANK-SELECT' => 'SingleSelect DropDown List (from Multi): '.$one_single_with_blank_select,
			'TEST-ELEMENTS.SINGLE-SEARCH-SELECT' => 'SingleSelect DropDown List with Search: '.$elem_single_select,
			'TEST-ELEMENTS.MULTI-SELECT' => 'MultiSelect DropDown List: '.$elem_multi_select,
			'TEST-ELEMENTS.MULTIBOX-SELECT' => 'MultiSelect CheckBoxes:<br>'.$elem_multi_boxes,
			'TEST-ELEMENTS.NORMAL-LIST-S' => $test_normal_list_s,
			'TEST-ELEMENTS.NORMAL-LIST-M' => $test_normal_list_m,
			'TEST-ELEMENTS.CALENDAR' => 'Calendar Selector: '.SmartComponents::js_draw_date_field('frm_calendar_id', 'frm[date]', Smart::escape_html($frm['date']), 'Select Date', "'0d'", "'1y'", '', 'alert(\'You selected the date: \' + date);'),
			'TEST-ELEMENTS.TIMEPICKER' => 'TimePicker Selector: '.SmartComponents::js_draw_time_field('frm_timepicker_id', 'frm[time]', Smart::escape_html($frm['time']), 'Select Time', '9', '19', '0', '55', '5', '3', '', 'alert(\'You selected the time: \' + time);'),
			'TEST-ELEMENTS.AUTOCOMPLETE-SINGLE' => 'AutoComplete: '.'<input id="auto-complete-fld" type="text" name="frm[autocomplete]" style="width:75px;">'.SmartComponents::js_draw_ui_autocomplete_single('auto-complete-fld', SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.autocomplete', 'src', 1, 'alert(\'You selected: \' + value);').' &nbsp; '.'<input id="auto-complete-mfld" type="text" name="frm[mautocomplete]" style="width:125px;">'.SmartComponents::js_draw_ui_autocomplete_multi('auto-complete-mfld', SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.autocomplete', 'src', 1, 'alert(\'You selected: \' + value);'),
			'TEST-elements.Captcha' => SmartCaptchaFormCheck::captcha_form(SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.captcha', self::captcha_form_name()),
			'test-elements.limited-area' => 'Limited TextArea: '.SmartComponents::js_draw_limited_text_area('', 'frm[text_area_1]', '', 300, '400px', '100px'),
			'POWERED-INFO' => SmartComponents::draw_powered_info('no'),
			'STR-NUM' => '1abc', // this will be converted to num !!
			'NUM-NUM' => '0.123456789',
			'MARKER' => '1234567890.abcdefghijklmniopqrstuvwxyz"',
			'IFTEST' => Smart::random_number(1,2),
			'IF2TEST' => Smart::random_number(0,9),
			'LOOPTEST-VAR1' => [
					[
						'd1' => 'Column 1.x (HTML Escape)',
						'd2' => 'Column 2.x (JS Escape)',
						'd3' => 'Column 3.x (URL Escape)'
					]
			],
			'LOOPTEST-VAR2' => [
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
						'c1' => Smart::random_number(0,1),
						'c2' => 'a',
						'c3' => 'A'
					]
			],
			'MOD-JS-COMPONENTS' => $demo_mod_js_components
		),
		'yes'
	);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function test_interractions($mode) {
	//--
	return '<div><h1>Interractions Test for Windows</h1></div>'.self::bttn_open_modal(true).' &nbsp;&nbsp;&nbsp; '.self::bttn_open_modal(false).'<br><br>'.self::bttn_open_popup(true).' &nbsp;&nbsp;&nbsp; '.self::bttn_open_popup(false).'<br><br>'.self::bttn_set_confirm_unload().' &nbsp;&nbsp;&nbsp; '.self::bttn_set_parent_refresh().'<br><br>'.self::bttn_close_modal_or_popup();
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function bttn_open_modal($forced, $winname='') {
	if((string)$winname == '') {
		$wname = 'test_interractions_mod_'.Smart::uuid_10_seq().'_'.Smart::uuid_10_num().'_'.Smart::uuid_10_str();
	} else {
		$wname = (string) $winname;
	} //end if else
	if($forced) {
		$mode = 'mod';
		$set = '-1';
		$btn = 'Open Modal (strict)';
	} else {
		$mode = 'auto';
		$set = '0';
		$btn = 'Open Modal or PopUp (auto)';
	} //end if else
	return '<a class="ux-button ux-button-regular" style="min-width:325px;" target="'.Smart::escape_html($wname).'" href="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.interractions&mode='.Smart::escape_url($mode).'" onClick="SmartJS_BrowserUtils.PopUpLink(this.href, this.target, null, null, '.Smart::escape_js($set).'); return false;">'.Smart::escape_html($btn).'</a>';
} //END FUNCTION
//==================================================================


//==================================================================
private static function bttn_open_popup($forced, $winname='') {
	if((string)$winname == '') {
		$wname = 'test_interractions_pop_'.Smart::uuid_10_seq().'_'.Smart::uuid_10_num().'_'.Smart::uuid_10_str();
	} else {
		$wname = (string) $winname;
	} //end if else
	if($forced) {
		$mode = 'pop';
		$set = '1';
		$btn = 'Open PopUp (strict)';
	} else {
		$mode = 'auto';
		$set = '0';
		$btn = 'Open PopUp or Modal (auto)';
	} //end if else
	return '<a class="ux-button ux-button-highlight" style="min-width:325px;" target="'.Smart::escape_html($wname).'" href="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.interractions&mode='.Smart::escape_url($mode).'" onClick="SmartJS_BrowserUtils.PopUpLink(this.href, this.target, null, null, '.Smart::escape_js($set).'); return false;">'.Smart::escape_html($btn).'</a>';
} //END FUNCTION
//==================================================================


//==================================================================
private static function bttn_close_modal_or_popup() {
	//--
	return '<button class="ux-button ux-button-special" style="min-width:325px;" onClick="SmartJS_BrowserUtils.CloseModalPopUp(); return false;">[ Close: Modal / PopUp ]</button>';
	//--
} //END FUNCTION
//==================================================================


//==================================================================
private static function bttn_set_parent_refresh() {
	//--
	return '<button class="ux-button ux-button-dark" style="min-width:325px;" onClick="SmartJS_BrowserUtils.RefreshParent(); return false;">[ Set: Parent Refresh / Reload ]</button>';
	//--
} //END FUNCTION
//==================================================================


private static function bttn_set_confirm_unload() {
	//--
	$question = 'This is a test for Confirm Unload. Are you sure you want to close this page ?';
	//--
	return '<button class="ux-button ux-button-dark" style="min-width:325px;" onClick="SmartJS_BrowserUtils.PageAwayControl(\''.Smart::escape_js($question).'\'); return false;">[ Set: Confirm Unload ]</button>';
	//--
} //END FUNCTION

//==================================================================
private static function captcha_form_name() {
	return ' Test_Unit-Ajax-Form-forCaptcha_2015 '; // test value with all allowed characters and spaces to be trimmed
} //END FUNCTION
//==================================================================


//==================================================================
private static function captcha_mode() {
	if((string)SMART_FRAMEWORK_TESTUNIT_CAPTCHA_MODE == 'session') {
		return 'session';
	} else {
		return 'cookie';
	} //end if else
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_captcha($y_type='png') {
	//--
	return SmartCaptchaFormCheck::captcha_image(
		self::captcha_form_name(),
		self::captcha_mode(),
		'hashed',
		'0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ#@$%&*!',
		'300',
		'5',
		'170',
		'50',
		(string)$y_type
	);
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function post__answer__by__ajax($tab, $frm) {

	//--
	global $configs;
	//--

	//--
	$tmp_data = '<br><br><hr><pre>'.'GET:'.'<br>'.Smart::escape_html(print_r(SmartFrameworkSecurity::FilterGetPostCookieVars($_GET),1)).'<hr>'.'POST:'.'<br>'.Smart::escape_html(print_r(SmartFrameworkSecurity::FilterGetPostCookieVars($_POST),1)).'</pre>';
	//--

	//--
	if(SmartCaptchaFormCheck::verify(self::captcha_form_name(), self::captcha_mode(), false) == 1) { // verify but do not clear yet
		$captcha_ok = true;
	} else {
		$captcha_ok = false;
	} //end if else
	//--

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
				$redir = SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.main&time='.time().'&tab='.rawurlencode($tab);
				$div_id = '';
				$div_htm = '';
			} else {
				$redir = '';
				$div_id = 'answer_ajax';
				$div_htm = '<script>$("#smart__CaptchaFrm__img").attr("src", "'.Smart::escape_js(SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.captcha&time='.time()).'");</script><table border="0" bgcolor="#DDEEFF" width="100%"><tr><td><h1>OK, form sent on: '.date('Y-m-d H:i:s').'</h1></td></tr><tr><td><div align="center"><img src="lib/core/img/q_completed.png"></div></td></tr><tr><td><hr><b>Here is the content of the text area:</b><br><pre>'.Smart::escape_html($frm['text_area_1']).'</pre></td></tr></table>';
			} //end if else
			//--
			SmartCaptchaFormCheck::clear(self::captcha_form_name(), self::captcha_mode()); // everything OK, so clear captcha
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
			$redir = SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.main&time='.time().'&tab='.rawurlencode($tab);
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
	$out = SmartComponents::post_answer_by_ajax($code, $title, $desc, $redir, $div_id, $div_htm);
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function load__url__or__file($y_url) {

	//--
	$page = SmartUtils::load_url_or_file($y_url, 15, 'GET', 'tls');
	//--

	//--
	return $page['content'];
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function test_crypto() {

	//--
	$time = microtime(true);
	//--

	//--
	$unicode_text = "Unicode String [ ".time()." ]: @ Smart スマート // Cloud Application Platform クラウドアプリケーションプラットフォーム '".implode('', array_keys(SmartUnicode::accented_chars()))." \" <p></p>
	? & * ^ $ @ ! ` ~ % () [] {} | \\ / + - _ : ; , . #'".microtime().'#';
	//--

	//--
	$b64enc = base64_encode($unicode_text);
	$b64dec = base64_decode($b64enc);
	//--

	//--
	$bin2hex = strtoupper(bin2hex((string)$unicode_text));
	$hex2bin = hex2bin(strtolower(trim((string)$bin2hex)));
	//--

	//--
	$hkey = 'TestUnit // This is a test key for Crypto Cipher ...'.time().$unicode_text;
	//--
	$he_enc = SmartUtils::crypto_encrypt($unicode_text, $hkey);
	$he_dec = SmartUtils::crypto_decrypt($he_enc, $hkey);
	//--
	if(((string)$he_dec != (string)$unicode_text) OR (sha1($he_dec) != sha1($unicode_text))) {
		Smart::raise_error('TestUnit FAILED in '.__FUNCTION__.'() :: Crypto Cipher test', 'TestUnit: Crypto Cipher test failed ...');
		return;
	} //end if
	//--

	//--
	$bf_key = SmartHashCrypto::sha512('TestUnit // This is a test key for Blowfish ...'.time().$unicode_text);
	$bf_enc = SmartUtils::crypto_blowfish_encrypt($unicode_text, $bf_key);
	$bf_dec = SmartUtils::crypto_blowfish_decrypt($bf_enc, $bf_key);
	if(((string)$bf_dec != (string)$unicode_text) OR (sha1($bf_dec) != sha1($unicode_text))) {
		Smart::raise_error('TestUnit FAILED in '.__FUNCTION__.'() :: Crypto Blowfish test', 'TestUnit: Blowfish test failed ...');
		return;
	} //end if
	//--

	//--
	$arch_lzs = SmartArchiverLZS::compressToBase64($unicode_text);
	$unarch_lzs = SmartArchiverLZS::decompressFromBase64($arch_lzs);
	if(((string)$unarch_lzs != (string)$unicode_text) OR (sha1($unarch_lzs) != sha1($unicode_text))) {
		Smart::raise_error('TestUnit FAILED in '.__FUNCTION__.'() :: Crypto Arch-LZS test', 'TestUnit: Arch-LZS test failed ...');
		return;
	} //end if
	//--

	//--
	$arch_bf_lzs = SmartArchiverLZS::compressToBase64($bf_enc);
	$unarch_bf_lzs = SmartArchiverLZS::decompressFromBase64($arch_bf_lzs);
	if(((string)$unarch_bf_lzs != (string)$bf_enc) OR (sha1($unarch_bf_lzs) != sha1($bf_enc))) {
		Smart::raise_error('TestUnit FAILED in '.__FUNCTION__.'() :: Crypto Blowfish-Arch-LZS test', 'TestUnit: Blowfish-Arch-LZS test failed ...');
		return;
	} //end if
	//--

	//--
	$time = 'TOTAL TIME was: '.(microtime(true) - $time);
	//--

	//--
	return SmartMarkersTemplating::render_file_template(
		'lib/core/templates/testunit/crypto-test.inc.htm',
		array(
			//--
			'EXE-TIME' => Smart::escape_html($time),
			'UNICODE-TEXT' => Smart::escape_html($unicode_text),
			'JS-ESCAPED' => $unicode_text, // Smart::escape_html(Smart::escape_js($unicode_text)) // is escaped in the template as JS-ESCAPED|js|html
			'HASH-SHA512' => Smart::escape_html(SmartHashCrypto::sha512($unicode_text)),
			'HASH-SHA1' => Smart::escape_html(sha1($unicode_text)),
			'HASH-MD5' => Smart::escape_html(md5($unicode_text)),
			'BASE64-ENCODED' => Smart::escape_html($b64enc),
			'BASE64-DECODED' => Smart::escape_html($b64dec),
			'BIN2HEX-ENCODED' => Smart::escape_html($bin2hex),
			'HEX2BIN-DECODED' => Smart::escape_html($hex2bin),
			'LZS-ARCHIVED' => Smart::escape_html($arch_lzs),
			'LZS-UNARCHIVED' => Smart::escape_html($unarch_lzs),
			'BLOWFISH-ENCRYPTED' => Smart::escape_html($bf_enc),
			'BLOWFISH-LZS-ENCRYPTED' => Smart::escape_html($arch_bf_lzs),
			'BLOWFISH-DECRYPTED' => Smart::escape_html($bf_dec),
			'BLOWFISH-KEY' => Smart::escape_html($bf_key),
			'BLOWFISH-OPTIONS' => Smart::escape_html(SmartCipherCrypto::crypto_options('blowfish')),
			'HASHCRYPT-ENC' => Smart::escape_html($he_enc),
			'HASHCRYPT-DEC' => Smart::escape_html($he_dec),
			'HASHCRYPT-OPTIONS' => Smart::escape_html(SmartCipherCrypto::crypto_options('custom')),
			//--
		)
	);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function test_strings() {

	//--
	$unicode_text = '"Unicode78źź:ăĂîÎâÂșȘțȚşŞţŢグッド';
	//--

	//--
	$err = '';
	//--

	//--
	$tests[] = '##### Unicode STRING / TESTS: #####';
	//--

	//--
	$regex_positive = '/^[\w"\:\?]+$/';
	$regex_negative = '/[^\w"\:\?]/';
	//--

	//--
	if(defined('SMART_FRAMEWORK_SECURITY_FILTER_INPUT')) {
		if((string)SMART_FRAMEWORK_SECURITY_FILTER_INPUT != '') {
			if((string)$err == '') {
				$the_test = 'Smart.Framework Security Input Filter Regex - test over a full Unicode String';
				$tests[] = $the_test;
				if(preg_match((string)SMART_FRAMEWORK_SECURITY_FILTER_INPUT, 'Platform クラウドアプリケーションプラットフォーム \'áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇòóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝßȘŞŠŚŜțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ " <p></p> ? & * ^ $ @ ! ` ~ % () [] {} | \ / + - _ : ; , . #\'0.51085600 1454529112#'."\r\n\t".'`~@#$%^&*()-_=+[{]}|;:"<>,.?/\\')) {
					$err = 'ERROR: '.$the_test.' FAILED ...';
				} //end if
			} //end if
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Unicode Regex Test Positive';
		$tests[] = $the_test;
		if(!preg_match((string)$regex_positive.'u', (string)$unicode_text)) {
			$err = 'ERROR: '.$the_test.' FAILED (1) ...';
		} elseif(preg_match((string)$regex_positive, (string)$unicode_text)) {
			$err = 'ERROR: '.$the_test.' FAILED (2) ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Unicode Regex Test Negative';
		$tests[] = $the_test;
		if(preg_match((string)$regex_negative.'u', (string)$unicode_text)) {
			$err = 'ERROR: '.$the_test.' FAILED (1) ...';
		} elseif(!preg_match((string)$regex_negative, (string)$unicode_text)) {
			$err = 'ERROR: '.$the_test.' FAILED (2) ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Deaccented ISO Regex Test Positive';
		$tests[] = $the_test;
		if(!preg_match((string)$regex_positive, (string)SmartUnicode::deaccent_str($unicode_text))) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Deaccented ISO Regex Test Negative';
		$tests[] = $the_test;
		if(preg_match((string)$regex_negative, (string)SmartUnicode::deaccent_str($unicode_text))) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Unicode Strlen Test';
		$tests[] = $the_test;
		if(SmartUnicode::str_len($unicode_text) !== 30) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') { // this tests also SmartUnicode::str_ipos
		$the_test = 'Unicode Find Substring (Case Insensitive), Positive';
		$tests[] = $the_test;
		if(SmartUnicode::str_icontains($unicode_text, 'șș') !== true) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') { // this tests also SmartUnicode::str_ipos
		$the_test = 'Unicode Find Substring (Case Insensitive), Negative';
		$tests[] = $the_test;
		if(SmartUnicode::str_icontains($unicode_text, 'șş') !== false) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') { // this tests also SmartUnicode::str_pos
		$the_test = 'Unicode Find Substring (Case Sensitive), Positive';
		$tests[] = $the_test;
		if(SmartUnicode::str_contains($unicode_text, 'țȚ') !== true) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') { // this tests also SmartUnicode::str_pos
		$the_test = 'Unicode Find Substring (Case Sensitive), Negative';
		$tests[] = $the_test;
		if(SmartUnicode::str_contains($unicode_text, 'țŢ') !== false) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Unicode Find Substring (Case Insensitive), Reverse';
		$tests[] = $the_test;
		if(SmartUnicode::str_ripos($unicode_text, 'ţţグ') === false) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Unicode Find Substring (Case Sensitive), Reverse';
		$tests[] = $the_test;
		if(SmartUnicode::str_rpos($unicode_text, 'ţŢグ') === false) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Unicode Return Substring (Case Insensitive)';
		$tests[] = $the_test;
		if(SmartUnicode::stri_str($unicode_text, 'âȘșȚ') !== 'ÂșȘțȚşŞţŢグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Unicode Return Substring (Case Sensitive)';
		$tests[] = $the_test;
		if(SmartUnicode::str_str($unicode_text, 'ÂșȘț') !== 'ÂșȘțȚşŞţŢグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Unicode String to LowerCase';
		$tests[] = $the_test;
		if(SmartUnicode::str_tolower($unicode_text) !== '"unicode78źź:ăăîîââșșțțşşţţグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Unicode String to UpperCase';
		$tests[] = $the_test;
		if(SmartUnicode::str_toupper($unicode_text) !== '"UNICODE78ŹŹ:ĂĂÎÎÂÂȘȘȚȚŞŞŢŢグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Unicode SubString function (without last param)';
		$tests[] = $the_test;
		if(SmartUnicode::sub_str($unicode_text, 25) !== 'ţŢグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'Unicode SubString function (with last param)';
		$tests[] = $the_test;
		if(SmartUnicode::sub_str($unicode_text, 25, 3) !== 'ţŢグ') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Unicode SubString Count function';
		$tests[] = $the_test;
		if(SmartUnicode::substr_count($unicode_text, 'ţ') !== 1) {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Unicode String Replace with Limit (Case Sensitive)';
		$tests[] = $the_test;
		if(SmartUnicode::str_limit_replace('ź', '@', $unicode_text, 1) !== '"Unicode78@ź:ăĂîÎâÂșȘțȚşŞţŢグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'String Replace without Limit (Case Sensitive)';
		$tests[] = $the_test;
		if(str_replace('ź', '@', $unicode_text) !== '"Unicode78@@:ăĂîÎâÂșȘțȚşŞţŢグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	if((string)$err == '') { /* This test fails if the replacements accented characters are different case than one find in string (upper/lower) ... */
		$the_test = 'String Replace without Limit (Case Insensitive) *** Only with unaccented replacements !!';
		$tests[] = $the_test;
		if(str_ireplace('E7', '@', $unicode_text) !== '"Unicod@8źź:ăĂîÎâÂșȘțȚşŞţŢグッド') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Deaccent String';
		$tests[] = $the_test;
		if(SmartUnicode::deaccent_str($unicode_text) !== '"Unicode78zz:aAiIaAsStTsStT???') {
			$err = 'ERROR: '.$the_test.' FAILED ...';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'YAML Unicode Test: Compose from Array / Parse from YAML';
		$tests[] = $the_test;
		$test_arr = array(
			'@test' => 'Testing weird key characters',
			'line1' => 'Some ISO-8859-1 String: @ # $ % ^ & * (\') _ - + = { [ ] } ; < ,. > / ? \\ |', 'line2' => 'Unicode (long) String: '.$unicode_text.' '.SmartUnicode::str_toupper($unicode_text).' '.$unicode_text.' '.SmartUnicode::str_tolower($unicode_text).' '.$unicode_text.' '.SmartUnicode::deaccent_str($unicode_text).' '.$unicode_text,
			$unicode_text => 'Unicode as Key',
			'line3' => ['A' => 'b', 100, 'Thousand'],
			'line4' => [1, 0.2, 3.0001],
			'line5' => date('Y-m-d H:i:s')
		);
		$test_yaml = (string) '# start YAML (to test also comments)'."\n".(new SmartYamlConverter())->compose($test_arr)."\n".'# end YAML';
		$test_parr = (new SmartYamlConverter())->parse($test_yaml);
		if($test_arr !== $test_parr) {
			$err = 'ERROR: '.$the_test.' FAILED ...'.' #ORIGINAL Array ['.print_r($test_arr,1).']'."\n\n".'#YAML Array (from YAML String): '.print_r($test_parr,1)."\n\n".'#YAML String (from ORIGINAL Array): '."\n".$test_yaml;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'XML Unicode Test: Compose from Array / Parse from XML';
		$tests[] = $the_test;
		$test_arr = array(
			'TEST' => 'Testing weird key characters',
			'line1' => 'Some ISO-8859-1 String: @ # $ % ^ & * (\') _ - + = { [ ] } ; < ,. > / ? \\ |', 'line2' => 'Unicode (long) String: '.$unicode_text.' '.SmartUnicode::str_toupper($unicode_text).' '.$unicode_text.' '.SmartUnicode::str_tolower($unicode_text).' '.$unicode_text.' '.SmartUnicode::deaccent_str($unicode_text).' '.$unicode_text,
			'line3' => ['A' => 'b', 'c' => 'D'],
			'line4' => '',
			'line5' => date('Y-m-d H:i:s')
		);
		$test_xml = (string) (new SmartXmlComposer())->transform($test_arr);
		$test_parr = (new SmartXmlParser())->transform($test_xml);
		if($test_arr !== $test_parr) {
			$err = 'ERROR: '.$the_test.' FAILED ...'.' #ORIGINAL Array ['.print_r($test_arr,1).']'."\n\n".'#XML Array (from XML String): '.print_r($test_parr,1)."\n\n".'#XML String (from ORIGINAL Array): '."\n".$test_xml;
		} //end if
	} //end if
	//--
	$the_random_unicode_text = sha1($unicode_text.Smart::random_number(1000,9999)).'-'.$unicode_text." \r\n\t".'-'.Smart::uuid_10_num().'-'.Smart::uuid_10_str().'-'.Smart::uuid_10_seq();
	//--
	if((string)$err == '') {
		$the_test = 'Data: Archive / Unarchive';
		$tests[] = $the_test;
		if(SmartUtils::data_unarchive(SmartUtils::data_archive($the_random_unicode_text)) !== (string)$the_random_unicode_text) {
			$err = 'ERROR: '.$the_test.' FAILED ...'.' ['.$the_random_unicode_text.']';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'Cache: Archive / Unarchive';
		$tests[] = $the_test;
		if(SmartUtils::cache_variable_unarchive(SmartUtils::cache_variable_archive($the_random_unicode_text)) !== (string)$the_random_unicode_text) {
			$err = 'ERROR: '.$the_test.' FAILED ...'.' ['.$the_random_unicode_text.']';
		} //end if
	} //end if
	//--

	//-- regex positive tests
	$arr_regex = [
		'number-integer' 		=> [ 0, '75', '-101' ],
		'number-decimal' 		=> [ 0, '0.0', '0.1', '75', '75.0', '75.1', '-555', '-555.0', '-555.1' ],
		'number-list-integer' 	=> '1;2;30',
		'number-list-decimal' 	=> '1.0;2;30.44',
		'url' 					=> [ 'https://192.168.1.0', 'http://localhost', 'https://www.dom.ext', 'http://dom.ext/path?a=b&c=d%20#s' ],
		'domain' 				=> [ 'domain.com', 'sdom.domain.org' ],
		'email' 				=> [ 'root@localhost', 'root@localhost.loc', 'sometest-name.extra@dom.ext' ],
		'fax' 					=> [ '~+99-(0)999-123.456.78~' ],
		'macaddr' 				=> [ '00:0A:95:9d:68:16', '00-0a-95-9D-68-16' ],
		'ipv4' 					=> [ '192.168.0.1', '169.254.1.0', '1.0.0.1' ],
		'ipv6' 					=> [ '::1', '0000:0000:0000:0000:0000:0000:0000:0001', '2001:0db8:0000:0000:0000:ff00:0042:8329', '2001:dB8::2:1', '2001:db8::1', '3731:54:65fe:2::a7' ]
	];
	//--
	foreach((array)$arr_regex as $key => $val) {
		//--
		if(is_array($val)) {
			for($i=0; $i<Smart::array_size($val); $i++) {
				$the_test = 'Regex Validate Positive (#'.$i.'): '.$key.' ['.$val[$i].']';
				$tests[] = $the_test;
				if(SmartValidator::validate_string($val[$i], $key) !== true) {
					$err = 'ERROR: '.$the_test.' FAILED ...';
					break;
				} //end if
				if((stripos((string)$key, 'number-') === 0) AND (stripos((string)$key, 'number-list-') === false)) {
					$the_test = 'Regex Validate Numeric Positive (#'.$i.'): '.$key.' ['.$val[$i].']';
					$tests[] = $the_test;
					if(SmartValidator::validate_numeric_integer_or_decimal_values($val[$i], $key) !== true) {
						$err = 'ERROR: '.$the_test.' FAILED ...';
						break;
					} //end if
				} //end if
			} //end for
		} else {
			$the_test = 'Regex Validate Positive: '.$key.' ['.$val.']';
			$tests[] = $the_test;
			if(SmartValidator::validate_string($val, $key) !== true) {
				$err = 'ERROR: '.$the_test.' FAILED ...';
			} //end if
			if((stripos((string)$key, 'number-') === 0) AND (stripos((string)$key, 'number-list-') === false)) {
				$the_test = 'Regex Validate Numeric Positive: '.$key.' ['.$val.']';
				$tests[] = $the_test;
				if(SmartValidator::validate_numeric_integer_or_decimal_values($val, $key) !== true) {
					$err = 'ERROR: '.$the_test.' FAILED ...';
				} //end if
			} //end if
		} //end if else
		//--
		if((string)$err != '') {
			break;
		} //end if
		//--
	} //end foreach
	//--

	//-- regex negative tests
	$arr_regex = [
		'number-integer' 		=> [ '', '.', 'a9', '7B', '-9 ', ' -7' ],
		'number-decimal' 		=> [ '', '.0', '.1', '-.10', ' -7', '-9.0 ' ],
		'number-list-integer' 	=> '1;2.3;30',
		'number-list-decimal' 	=> '1.0;2;30.44a',
		'url' 					=> [ 'http:://192.168.1.0', 'https://local host', 'http:/www.dom.ext', 'https:dom.ext/path?a=b&c=d%20#s' ],
		'domain' 				=> [ 'doMain.com', 's dom.domain.org', '.dom.ext', 'dom..ext', 'localhost', 'loc', 'dom.ext.' ],
		'email' 				=> [ 'rooT@localhost', 'root@local host.loc', 'sometest-name.extra@do_m.ext' ],
		'fax' 					=> [ '~ +99-(0)999-123.456.78 ~' ],
		'macaddr' 				=> [ '00:0A:95:9z:68:16', '00-0Z-95-9D-68-16' ],
		'ipv4' 					=> [ '192.168.0.', '169..1.0', '1.0.1' ],
		'ipv6' 					=> [ '::x', '00z0:0000:0000:0000:0000:0000:0000:0001', '2001:0dx8:0000:0000:0000:ff00:0042:8329', '2001:WB8::2:1', '2001:@db8::1', '3731:54:65Qe:2::a7' ]
	];
	//--
	foreach((array)$arr_regex as $key => $val) {
		//--
		if(is_array($val)) {
			for($i=0; $i<Smart::array_size($val); $i++) {
				$the_test = 'Regex Validate Negative (#'.$i.'): '.$key.' ['.$val[$i].']';
				$tests[] = $the_test;
				if(SmartValidator::validate_string($val[$i], $key) === true) {
					$err = 'ERROR: '.$the_test.' FAILED ...';
					break;
				} //end if
			} //end for
		} else {
			$the_test = 'Regex Validate Negative: '.$key.' ['.$val.']';
			$tests[] = $the_test;
			if(SmartValidator::validate_string($val, $key) === true) {
				$err = 'ERROR: '.$the_test.' FAILED ...';
			} //end if
		} //end if else
		//--
		if((string)$err != '') {
			break;
		} //end if
		//--
	} //end foreach
	//--

	//--
	$endtest = '##### END TESTS ... #####';
	//--

	//--
	if((string)$err == '') {
		$img_sign = 'lib/core/img/sign_info.png';
		$img_check = 'lib/core/img/q_completed.png';
		$text_main = Smart::escape_js('<span style="color:#83B953;">Good ... Perfect &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; グッド ... パーフェクト</span>');
		$text_info = Smart::escape_js('<h2><span style="color:#83B953;">All</span> the SmartFramework Unicode String <span style="color:#83B953;">Tests PASSED on PHP</span><hr></h2><span style="font-size:14px;">'.Smart::nl_2_br(Smart::escape_html(implode("\n".'* ', $tests)."\n".$endtest)).'</span>');
	} else {
		$img_sign = 'lib/core/img/sign_error.png';
		$img_check = 'lib/core/img/q_warning.png';
		$text_main = Smart::escape_js('<span style="color:#FF5500;">An ERROR occured ... &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; エラーが発生しました ...</span>');
		$text_info = Smart::escape_js('<h2><span style="color:#FF5500;">A test FAILED</span> when testing Unicode String Tests.<span style="color:#FF5500;"><hr>FAILED Test Details</span>:</h2><br><h3>'.Smart::escape_html($tests[Smart::array_size($tests)-1]).'</h3><br><span style="font-size:14px;"><pre>'.Smart::escape_html($err).'</pre></span>');
	} //end if else
	//--

//--
$html = <<<HTML
<h1>SmartFramework Unicode Strings Tests: DONE ...</h1>
<script type="text/javascript">
	SmartJS_BrowserUtils.alert_Dialog(
		'<img src="{$img_sign}" align="right"><h1>{$text_main}</h1><hr><span style="color:#333333;"><img src="{$img_check}" align="right">{$text_info}<br>',
		'',
		'Unicode String Test Suite for SmartFramework: PHP',
		'725',
		'425'
	);
</script>
HTML;
//--

	//--
	return $html;
	//--

} //END FUNCTION
//==================================================================





//==================================================================
private static function pack_test_archive($y_exclusions_arr='') {
	//--
	$testsrcfile = (string) SmartFileSystem::read('lib/core/lib_smart_test_suite.php');
	$out = '';
	if((string)$testsrcfile != '') {
		//--
		$testsrcfile = (string) base64_encode((string)$testsrcfile);
		$vlen = Smart::random_number(100000,900000);
		//--
		while(strlen((string)$out) < (8388608 + $vlen)) {
			$randomizer = (string) '#'.Smart::random_number().'#'."\n";
			$testfile = SmartUtils::data_archive((string)$randomizer.$testsrcfile);
			if(sha1((string)SmartUtils::data_unarchive((string)$testfile)) !== sha1((string)$randomizer.$testsrcfile)) {
				Smart::log_warning('Data Unarchive Failed for Pack Test Archive ...');
				return 'Data Unarchive Failed for Pack Test Archive !';
			} //end if
			$out .= (string) $testfile;
		} //end if
		//--
	} else {
		//--
		Smart::log_warning('Failed to read the test file: lib/core/lib_smart_test_suite.php');
		return 'ERROR: Cannot Get File Read for this test !';
		//--
	} //end if
	//--
	return (string) $out;
	//--
} //END FUNCTION
//==================================================================



//==================================================================
public static function test_redisserver() {

	global $configs;

	//--
	if(SMART_FRAMEWORK_TESTUNIT_ALLOW_REDIS_TESTS !== true) {
		return SmartComponents::operation_notice('Test Unit for Redis Server is DISABLED ...');
	} //end if
	//--

	//--
	if(SmartPersistentCache::isActive()) {
		//--
		$redis_big_content = self::pack_test_archive(); // CREATE THE Test Archive (time not counted)
		//--
		$redis_test_key = 'redis-test-key_'.Smart::uuid_10_num().'-'.Smart::uuid_36().'-'.Smart::uuid_45();
		$redis_test_value = array(
			'unicode-test' => '"Unicode78źź:ăĂîÎâÂșȘțȚşŞţŢグッド', // unicode value
			'big-key-test' => (string) $redis_big_content, // a big key
			'random-key' => Smart::uuid_10_str().'.'.Smart::random_number(1000,9999)
		);
		$redis_test_checkum = sha1(implode("\n", (array)$redis_test_value));
		$redis_test_arch_content = SmartUtils::cache_variable_archive($redis_test_value);
		$redis_test_arch_checksum = sha1($redis_test_arch_content);
		//--
		$tests = array();
		$tests[] = '##### Redis / TESTS (Persistent Cache) with a Variable Key-Size of '.SmartUtils::pretty_print_bytes(strlen($redis_test_arch_content), 2).' : #####';
		//--
		$err = '';
		//--
		if((string)$err == '') {
			$tests[] = 'Building a Test Archive file for Redis Tests (time not counted)'; // archive was previous created, only test here
			if((string)$redis_big_content == '') {
				$err = 'Failed to build the Test Archive file for the Redis Test (see the error log for more details) ...';
			} //end if
		} //end if
		//--
		$time = microtime(true);
		$tests[] = '++ START Counter ...';
		//--
		if((string)$err == '') {
			$tests[] = 'Building the Cache Archive';
			if((string)$redis_test_arch_content == '') {
				$err = 'Failed to build the Cache Variable(s) Archive file for the Redis Test (see the error log for more details) ...';
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Set a short Redis Key (auto-expire in 3 seconds)';
			$redis_set_key = SmartPersistentCache::setKey(
				'redis-server-tests',
				$redis_test_key,
				(string)$redis_test_value['unicode-test'],
				3 // expire it after 3 seconds
			);
			if($redis_set_key !== true) {
				$err = 'Redis SetKey (short) returned a non-true result: '."\n".$redis_test_key;
			} //end if
			if((string)$err == '') {
				$tests[] = 'Wait 5 seconds for Redis Key to expire, then check again if exists (time not counted)';
				sleep(5); // wait the Redis Key to Expire
				$time = (float) $time + 5; // ignore those 5 seconds (waiting time) to fix counter
				$tests[] = '-- FIX Counter (substract the 5 seconds, waiting time) ...';
				if(SmartPersistentCache::keyExists('redis-server-tests', $redis_test_key)) {
					$err = 'Redis (short) Key does still exists (but should be expired after 5 seconds) and is not: '."\n".$redis_test_key;
				} //end if
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Set a long Redis Key (will not expire)';
			$redis_set_key = SmartPersistentCache::setKey(
				'redis-server-tests',
				$redis_test_key,
				$redis_test_arch_content
			);
			if($redis_set_key !== true) {
				$err = 'Redis SetKey (long) returned a non-true result: '."\n".$redis_test_key;
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Check if Redis Key exists (after set)';
			if(!SmartPersistentCache::keyExists('redis-server-tests', $redis_test_key)) {
				$err = 'Redis Key does not exists: '."\n".$redis_test_key;
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Get Redis Key';
			$redis_cached_value = SmartUtils::cache_variable_unarchive(SmartPersistentCache::getKey('redis-server-tests', $redis_test_key));
			if(Smart::array_size($redis_cached_value) > 0) {
				$tests[] = 'Check if Redis Key is valid (array-keys)';
				if(((string)$redis_cached_value['unicode-test'] != '') AND ((string)$redis_cached_value['big-key-test'] != '')) {
					$tests[] = 'Check if Redis Key is valid (checksum)';
					if((string)sha1(implode("\n", (array)$redis_cached_value)) == (string)$redis_test_checkum) {
						if($redis_test_value === $redis_cached_value) {
							$tests[] = 'Unset Redis Key';
							$redis_unset_key = SmartPersistentCache::unsetKey('redis-server-tests', $redis_test_key);
							if($redis_unset_key === true) {
								$tests[] = 'Check if Redis Key exists (after unset)';
								if(SmartPersistentCache::keyExists('redis-server-tests', $redis_test_key)) {
									$err = 'Redis Key does exists (after unset) and should not: '."\n".$redis_test_key;
								} else {
									// OK
								} //end if
							} else {
								$err = 'Redis UnSetKey returned a non-true result: '."\n".$redis_test_key;
							} //end if else
						} else {
							$err = 'Redis Cached Value is broken: comparing stored value with original value failed on key: '."\n".$redis_test_key;
						} //end if else
					} else {
						$err = 'Redis Cached Value is broken: checksum failed on key: '."\n".$redis_test_key;
					} //end if else
				} else {
					$err = 'Redis Cached Value is broken: array-key is missing after Cache-Variable-Unarchive on key: '."\n".$redis_test_key;
				} //end if
			} else {
				$err = 'Redis Cached Value is broken: non-array value was returned after Cache-Variable-Unarchive on key: '."\n".$redis_test_key;
			} //end if
		} //end if
		//--
		$title = 'SmartFramework Redis Server Tests: DONE ...';
		//--
		$time = 'TOTAL TIME (Except building the test archive) was: '.(microtime(true) - $time); // substract the 3 seconds waiting time for Redis Key to expire
		//--
		$end_tests = '##### END TESTS ... '.$time.' sec. #####';
		//--
		if((string)$err == '') {
			$img_sign = 'lib/core/img/sign_info.png';
			$img_check = 'lib/core/img/q_completed.png';
			$text_main = Smart::escape_js('<span style="color:#83B953;">Good ... Perfect &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; グッド ... パーフェクト</span>');
			$text_info = Smart::escape_js('<h2><span style="color:#83B953;">All</span> the SmartFramework Redis Server Operations <span style="color:#83B953;">Tests PASSED on PHP</span><hr></h2><span style="font-size:14px;">'.Smart::nl_2_br(Smart::escape_html(implode("\n".'* ', $tests)."\n".$end_tests)).'</span>');
		} else {
			$img_sign = 'lib/core/img/sign_error.png';
			$img_check = 'lib/core/img/q_warning.png';
			$text_main = Smart::escape_js('<span style="color:#FF5500;">An ERROR occured ... &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; エラーが発生しました ...</span>');
			$text_info = Smart::escape_js('<h2><span style="color:#FF5500;">A test FAILED</span> when testing Redis Server Operations.<span style="color:#FF5500;"><hr>FAILED Test Details</span>:</h2><br><span style="font-size:14px;"><pre>'.Smart::escape_html($err).'</pre></span>');
		} //end if else
		//--
	} else {
		//--
		$title = 'SmartFramework Redis Server Tests - Redis Server was NOT SET ...';
		//--
		$img_sign = 'lib/core/img/sign_info.png';
		$img_check = 'lib/core/img/q_warning.png';
		$text_main = Smart::escape_js('<span style="color:#778899;">No Redis Server Tests performed ...</span>');
		$text_info = '<h2>The current configuration have not set the Redis Server ...</h2>';
		//--
	} //end if
	//--

//--
$html = <<<HTML
<h1>{$title}</h1>
<script type="text/javascript">
	SmartJS_BrowserUtils.alert_Dialog(
		'<img src="{$img_sign}" align="right"><h1>{$text_main}</h1><hr><span style="color:#333333;"><img src="{$img_check}" align="right">{$text_info}<br>',
		'',
		'Redis Server Test Suite for SmartFramework: PHP',
		'725',
		'480'
	);
</script>
HTML;
//--

	//--
	return $html;
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function test_pgsqlserver() {

	global $configs;

	//--
	if(SMART_FRAMEWORK_TESTUNIT_ALLOW_PGSQL_TESTS !== true) {
		return SmartComponents::operation_notice('Test Unit for PgSQL Server is DISABLED ...');
	} //end if
	//--

	//--
	$value = date('Y-m-d H:i:s');
	$comments = '"Unicode78źź:ăĂîÎâÂșȘțȚşŞţŢグッド'.'-'.Smart::random_number(1000,9999)."'";
	//--

	//-- PgSQL Tests
	if(((string)$configs['pgsql']['server-host'] != '') AND ((string)$configs['pgsql']['server-port'] != '') AND ((string)$configs['pgsql']['dbname'] != '') AND ((string)$configs['pgsql']['username'] != '')) {
		//--
		$time = microtime(true);
		//--
		$tests = array();
		$tests[] = '##### PostgreSQL / TESTS: #####';
		//--
		$err = '';
		//--
		if(Smart::random_number(1,9) >= 5) {
			$tests[] = 'Random Test Dynamic Connection: Open / Drop Test Table (If Exists)';
			$pgsql = new SmartPgsqlExtDb((array)$configs['pgsql']);
			$pgsql->write_data('DROP TABLE IF EXISTS "public"."_test_unit_db_server_tests"');
			unset($pgsql);
		} //end if
		//--
		$tests[] = 'PostgreSQL Server Version: '.SmartPgsqlDb::check_server_version();
		//--
		$tests[] = 'Start Transaction';
		SmartPgsqlDb::write_data('BEGIN');
		//--
		$tests[] = 'Create a Temporary Table for this Test, after transaction to test DDL';
		SmartPgsqlDb::write_data('CREATE TABLE "public"."_test_unit_db_server_tests" ( "variable" character varying(100) NOT NULL, "value" character varying(16384) DEFAULT \'\'::character varying, "comments" text DEFAULT \'\'::text NOT NULL, CONSTRAINT _test_unit_db_server_tests__check__variable CHECK ((char_length((variable)::text) >= 1)), CONSTRAINT _test_unit_db_server_tests__uniq__variable UNIQUE(variable) )');
		//--
		$variable = '"'.'Ș'."'".substr(SmartPgsqlDb::new_safe_id('uid10seq', 'variable', '_test_unit_db_server_tests', 'public'), 3, 7);
		//--
		if((string)$err == '') {
			$tests[] = 'Check if the Test Table exists [ Positive ; Variable is: '.$variable.' ]';
			$data = SmartPgsqlDb::check_if_table_exists('_test_unit_db_server_tests', 'public');
			if($data !== 1) {
				$err = 'Table Creation FAILED ... Table does not exists in the `public` schema ...';
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Write [ Insert ]';
			$quer_str = 'INSERT INTO "public"."_test_unit_db_server_tests" '.SmartPgsqlDb::prepare_write_statement(array('variable'=>$variable, 'value'=>$value, 'comments'=>$comments), 'insert');
			$data = SmartPgsqlDb::write_data($quer_str);
			if($data[1] !== 1) {
				$err = 'Write / Insert Test Failed, should return 1 but returned: '.$data[1];
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Write [ Insert if not exists, with Insert-SubSelect ]';
			$quer_str = 'INSERT INTO "public"."_test_unit_db_server_tests" '.SmartPgsqlDb::prepare_write_statement(array('variable'=>$variable, 'value'=>$value, 'comments'=>$comments), 'insert-subselect');
			$data = SmartPgsqlDb::write_igdata($quer_str);
			if($data[1] !== 0) {
				$err = 'Write / Insert if not exists with insert-subselect Test Failed, should return 0 but returned: '.$data[1];
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Write Ignore [ Update ]';
			$quer_str = 'UPDATE "public"."_test_unit_db_server_tests" SET "comments" = $2 WHERE ("variable" = $1)';
			$data = SmartPgsqlDb::write_igdata($quer_str, array($variable, $comments));
			if($data[1] !== 1) {
				$err = 'Write Ignore / Update Test Failed, should return 1 but returned: '.$data[1];
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Count [ One Row ]';
			$quer_str = 'SELECT COUNT(1) FROM "public"."_test_unit_db_server_tests" WHERE (("variable" = $1) AND ("comments" = '.SmartPgsqlDb::escape_literal($comments).'))';
			$data = SmartPgsqlDb::count_data($quer_str, array($variable));
			if($data !== 1) {
				$err = 'Count Test Failed, should return 1 but returned: '.$data;
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Read [ LIKE / Literal Escape +% ]';
			$data = SmartPgsqlDb::read_adata('SELECT * FROM "public"."_test_unit_db_server_tests" WHERE ("variable" LIKE '.SmartPgsqlDb::escape_literal('%'.$variable.'_%', 'yes').')');
			if(Smart::array_size($data) !== 0) {
				$err = 'Read Like / Literal-Escape +% Test Failed, should return 0 rows but returned: '.Smart::array_size($data);
			} //end if
		} //end if
		if((string)$err == '') {
			$tests[] = 'Read [ LIKE / Literal Escape ]';
			$data = SmartPgsqlDb::read_adata('SELECT * FROM "public"."_test_unit_db_server_tests" WHERE ("variable" LIKE '.SmartPgsqlDb::escape_literal('%'.$variable.'%').')');
			if(Smart::array_size($data) !== 1) {
				$err = 'Read Like / Literal-Escape Test Failed, should return 1 row but returned: '.Smart::array_size($data);
			} //end if
		} //end if
		if((string)$err == '') {
			$tests[] = 'Read [ LIKE / Str Escape +% ]';
			$data = SmartPgsqlDb::read_adata('SELECT * FROM "public"."_test_unit_db_server_tests" WHERE ("variable" ILIKE \''.SmartPgsqlDb::escape_str('%'.$variable.'_%', 'yes').'\')');
			if(Smart::array_size($data) !== 0) {
				$err = 'Read Like / Str-Escape +% Test Failed, should return 0 rows but returned: '.Smart::array_size($data);
			} //end if
		} //end if
		if((string)$err == '') {
			$tests[] = 'Read [ LIKE / Str Escape ]';
			$data = SmartPgsqlDb::read_adata('SELECT * FROM "public"."_test_unit_db_server_tests" WHERE ("variable" ILIKE \''.SmartPgsqlDb::escape_str('%'.$variable.'%').'\')');
			if(Smart::array_size($data) !== 1) {
				$err = 'Read Like / Str-Escape Test Failed, should return 1 row but returned: '.Smart::array_size($data);
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Read [ IN (ARRAY) ]';
			$quer_str = 'SELECT * FROM "public"."_test_unit_db_server_tests" WHERE ("variable" '.SmartPgsqlDb::prepare_write_statement(array('a', 'b', '3', $variable, '@?%'), 'in-select').') LIMIT 100 OFFSET 0';
			$data = SmartPgsqlDb::read_adata($quer_str);
			if(Smart::array_size($data) !== 1) {
				$err = 'Read IN Test Failed, should return 1 row but returned: '.Smart::array_size($data).' rows ...';
			} //end if
		} //end if
		//--
		$quer_str = 'SELECT "comments" FROM "public"."_test_unit_db_server_tests" WHERE ("variable" = $1) LIMIT 1 OFFSET 0';
		//--
		if((string)$err == '') {
			$tests[] = 'Read [ Non-Associative + Param Query $ ]';
			//$data = SmartPgsqlDb::read_data($quer_str, array($variable));
			//$param_query = str_replace('$1', '?', $quer_str); // convert $1 to ?
			$param_query = (string) $quer_str; // no more necessary to convert $1 to ? as prepare_param_query() has been extended to support ? or $#
			$param_query = SmartPgsqlDb::prepare_param_query($param_query, array($variable));
			$data = SmartPgsqlDb::read_data($param_query, 'Test Param Query');
			if(trim($data[0]) !== (string)$comments) {
				$err = 'Read / Non-Associative Test Failed, should return `'.$comments.'` but returned `'.$data[0].'`';
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Read [ Associative: One-Row ]';
			$data = SmartPgsqlDb::read_asdata($quer_str, array($variable));
			if(trim($data['comments']) !== (string)$comments) {
				$err = 'Read / Associative / One-Row Test Failed, should return `'.$comments.'` but returned `'.$data['comments'].'`';
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Read [ Associative: Multi-Rows ]';
			$data = SmartPgsqlDb::read_adata($quer_str, array($variable));
			if(trim($data[0]['comments']) !== (string)$comments) {
				$err = 'Read / Associative / Multi-Rows Test Failed, should return `'.$comments.'` but returned `'.$data[0]['comments'].'`';
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Write [ Delete ]';
			$quer_str = 'DELETE FROM "public"."_test_unit_db_server_tests" WHERE ("variable" = \''.SmartPgsqlDb::escape_str($variable).'\')';
			$data = SmartPgsqlDb::write_data($quer_str);
			if($data[1] !== 1) {
				$err = 'Write / Delete Test Failed, should return 1 but returned: '.$data[1];
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Write Ignore Duplicates [ Insert Ignore Positive ]';
			$quer_str = 'INSERT INTO "public"."_test_unit_db_server_tests" '.SmartPgsqlDb::prepare_write_statement(array('variable'=>$variable, 'value'=>null, 'comments'=>$comments), 'insert');
			$data = SmartPgsqlDb::write_igdata($quer_str);
			if($data[1] !== 1) {
				$err = 'Write / Insert Ignore Positive Test Failed, should return 1 but returned: '.$data[1];
			} //end if
		} //end if
		//--
		if((string)$err == '') {
			$tests[] = 'Write Ignore Duplicates [ Insert Ignore Negative ]';
			$quer_str = 'INSERT INTO "public"."_test_unit_db_server_tests" '.SmartPgsqlDb::prepare_write_statement(array('variable'=>$variable, 'value'=>$value, 'comments'=>$comments), 'insert');
			$data = SmartPgsqlDb::write_igdata($quer_str);
			if($data[1] !== 0) {
				$err = 'Write / Insert Ignore Negative Test Failed, should return 0 but returned: '.$data[1];
			} //end if
		} //end if
		//--
		$tests[] = 'Commit Transation';
		SmartPgsqlDb::write_data('COMMIT');
		//--
		if((string)$err == '') {
			$tests[] = 'Check if the Test Table Exists (Param Query ?) after Drop [ Negative ], using a new Constructor (should be able to re-use connection)';
			$pgsql2 = new SmartPgsqlExtDb((array)$configs['pgsql']);
			$pgsql2->write_data('DROP TABLE IF EXISTS "public"."_test_unit_db_server_tests"');
			$data = $pgsql2->check_if_table_exists('_test_unit_db_server_tests', 'public');
			$pgsql2->prepare_param_query('SELECT ?', array('\'1"'));
			unset($pgsql2);
			if($data === 1) {
				$err = 'Table Drop FAILED ... Table still exists ...';
			} //end if
		} //end if
		//--
		$title = 'SmartFramework PostgreSQL Server Tests: DONE ...';
		//--
		$time = 'TOTAL TIME was: '.(microtime(true) - $time);
		//--
		$end_tests = '##### END TESTS ... '.$time.' sec. #####';
		//--
		if((string)$err == '') {
			$img_sign = 'lib/core/img/sign_info.png';
			$img_check = 'lib/core/img/q_completed.png';
			$text_main = Smart::escape_js('<span style="color:#83B953;">Good ... Perfect &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; グッド ... パーフェクト</span>');
			$text_info = Smart::escape_js('<h2><span style="color:#83B953;">All</span> the SmartFramework PostgreSQL Server Operations <span style="color:#83B953;">Tests PASSED on PHP</span><hr></h2><span style="font-size:14px;">'.Smart::nl_2_br(Smart::escape_html(implode("\n".'* ', $tests)."\n".$end_tests)).'</span>');
		} else {
			$img_sign = 'lib/core/img/sign_error.png';
			$img_check = 'lib/core/img/q_warning.png';
			$text_main = Smart::escape_js('<span style="color:#FF5500;">An ERROR occured ... &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; エラーが発生しました ...</span>');
			$text_info = Smart::escape_js('<h2><span style="color:#FF5500;">A test FAILED</span> when testing PostgreSQL Server Operations.<span style="color:#FF5500;"><hr>FAILED Test Details</span>:</h2><br><span style="font-size:14px;"><pre>'.Smart::escape_html($err).'</pre></span>');
		} //end if else
		//--
	} else {
		//--
		$title = 'SmartFramework PostgreSQL Server Tests - PostgreSQL Server was NOT SET ...';
		//--
		$img_sign = 'lib/core/img/sign_info.png';
		$img_check = 'lib/core/img/q_warning.png';
		$text_main = Smart::escape_js('<span style="color:#778899;">No PostgreSQL Server Tests performed ...</span>');
		$text_info = '<h2>The current configuration have not set the PostgreSQL Server ...</h2>';
		//--
	} //end if
	//--

//--
$html = <<<HTML
<h1>{$title}</h1>
<script type="text/javascript">
	SmartJS_BrowserUtils.alert_Dialog(
		'<img src="{$img_sign}" align="right"><h1>{$text_main}</h1><hr><span style="color:#333333;"><img src="{$img_check}" align="right">{$text_info}<br>',
		'',
		'PostgreSQL Server Test Suite for SmartFramework: PHP',
		'725',
		'480'
	);
</script>
HTML;
//--

	//--
	return $html;
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function test_sqlite3_json_autocomplete($src) {
	//--
	$db = new SmartTestSQLite3Model();
	$model = $db->getConnection();
	//--
	$where = '';
	if((string)$src != '') {
		$where = ' WHERE name LIKE \''.$model->escape_str((string)$src).'%\'';
	} //end if else
	//--
	$rd = (array) $model->read_adata('SELECT iso, name FROM sample_countries'.$where.' ORDER BY name ASC LIMIT 25 OFFSET 0');
	$arr = array();
	for($i=0; $i<Smart::array_size($rd); $i++) { // id is optional for display only
		$arr[] = [ 'id' => '', 'value' => (string)$rd[$i]['iso'], 'label' => $rd[$i]['name'] ];
	} //end for
	//--
	unset($db); // close
	//--
	return Smart::json_encode((array)$arr);
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_sqlite3_json_smartgrid($ofs, $sortby, $sortdir, $sorttype, $src='') {

	//--
	$db = new SmartTestSQLite3Model();
	$model = $db->getConnection();
	//--

	//--
	$data = array();
	$data['status'] = 'OK';
	$data['crrOffset'] = (int) $ofs;
	$data['itemsPerPage'] = 25;
	$data['sortBy'] = (string) $sortby;
	$data['sortDir'] = (string) $sortdir;
	$data['sortType'] = (string) $sorttype;
	$data['filter'] = array(
		'src' => (string) $src
	);
	//--
	if((string)strtoupper((string)$sortdir) == 'DESC') {
		$syntax_sort_dir = 'DESC';
	} else {
		$syntax_sort_dir = 'ASC';
	} //end if else
	$syntax_sort_mode = '';
	switch((string)$sortby) {
		case 'iso':
			$syntax_sort_mode = ' ORDER BY iso '.$syntax_sort_dir;
			break;
		case 'name':
			$syntax_sort_mode = ' ORDER BY name '.$syntax_sort_dir;
			break;
		case 'iso3':
			$syntax_sort_mode = ' ORDER BY iso3 '.$syntax_sort_dir;
			break;
		case 'numcode':
			$syntax_sort_mode = ' ORDER BY numcode '.$syntax_sort_dir;
			break;
		default:
			$syntax_sort_mode = '';
	} //end switch
	//--
	$where = '';
	if((string)$src != '') {
		if(is_numeric($src)) {
			$where = $model->prepare_param_query(' WHERE numcode = ?', array((int)$src));
		} elseif(strlen((string)$src) == 2) {
			$where = $model->prepare_param_query(' WHERE iso = ?', array(SmartUnicode::str_toupper($src)));
		} elseif(strlen((string)$src) == 3) {
			$where = $model->prepare_param_query(' WHERE iso3 = ?', array(SmartUnicode::str_toupper($src)));
		} else {
			$where = $model->prepare_param_query(' WHERE name LIKE ?', array($src.'%'));
		} //end if else
	} //end if
	$data['totalRows'] = $model->count_data('SELECT COUNT(1) FROM sample_countries'.$where);
	$data['rowsList'] = $model->read_adata('SELECT iso, name, iso3, numcode FROM sample_countries'.$where.$syntax_sort_mode.' LIMIT '.(int)$data['itemsPerPage'].' OFFSET '.(int)$data['crrOffset']);
	//--
	unset($db); // close
	//--

	//--
	return Smart::json_encode((array)$data);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function test_fs() {

	//--
	if(SMART_FRAMEWORK_TESTUNIT_ALLOW_FS_TESTS !== true) {
		return SmartComponents::operation_notice('Test Unit File System Tests are DISABLED ...');
	} //end if
	//--

	//--
	$time = microtime(true);
	//--

	//--
	$err = '';
	$tests = array();
	//--

	//--
	if((string)DIRECTORY_SEPARATOR != '\\') { // broken links do not work on Windows !
		$tests[] = '##### FS OPERATIONS / TESTS - ALL: #####';
	} else {
		$tests[] = '##### FS OPERATIONS / TESTS *** PARTIAL SUPPORT ONLY ***: #####';
	} //end if else
	//--

	//--
	$test_string = '#START#'."\n".'グッド'."\n".'SmartFramework/Test/FileSystem'."\n".time()."\n".SMART_FRAMEWORK_HTACCESS_NOINDEXING.SMART_FRAMEWORK_HTACCESS_FORBIDDEN.SMART_FRAMEWORK_HTACCESS_NOEXECUTION."\n".'#END#';
	$test_str_cksum = SmartHashCrypto::sha512($test_string);
	$long_prefixed = SmartFileSysUtils::prefixed_sha1_path(sha1(time()));
	$short_prefixed = SmartFileSysUtils::prefixed_uuid10_dir(Smart::uuid_10_seq());
	//--
	$the_base_folder = 'tmp/tests/';
	$the_sufx_folder = 'Folder1';
	$the_base_file = 'NORMAL-Write_123_@#.txt';
	//--
	$the_folder = $the_base_folder.$the_sufx_folder.'/';
	$the_copy_folder = $the_base_folder.'folder2';
	$the_move_folder = $the_base_folder.'FOLDER3';
	$the_extra_folder = $the_folder.'extra/';
	$the_file = $the_folder.$the_base_file;
	//--
	$get_folder = SmartFileSysUtils::add_dir_last_slash(SmartFileSysUtils::get_dir_from_path($the_folder));
	$get_file = SmartFileSysUtils::get_file_name_from_path($the_file);
	$get_xfile = SmartFileSysUtils::get_noext_file_name_from_path($the_file);
	$get_ext = SmartFileSysUtils::get_file_extension_from_path($the_file);
	//--
	$the_copy_file = $the_file.'.copy.txt';
	$the_move_file = $the_extra_folder.$the_base_file.'.copy.moved.txt';
	$the_broken_link = $the_extra_folder.'a-broken-link';
	$the_broken_dir_link = $the_extra_folder.'a-broken-dir-link';
	$the_good_link = $the_extra_folder.'a-good-link';
	$the_good_dir_link = $the_extra_folder.'a-good-dir-link';
	//--

	//--
	$tests[] = 'PREV-FOLDER: '.$get_folder;
	$tests[] = 'FOLDER: '.$the_folder;
	$tests[] = 'FILE: '.$the_file;
	//--

	//--
	if((string)$err == '') {
		$the_test = 'CHECK TEST SAFE PATH NAME: DIR / FILE ...';
		$tests[] = $the_test;
		if(((string)Smart::safe_pathname((string)$get_folder) !== (string)$get_folder) OR ((string)Smart::safe_pathname((string)$the_copy_file) !== (string)$the_copy_file)) {
			$err = 'ERROR: SAFE PATH NAME TEST ... FAILED !!!';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'CHECK TEST ABSOLUTE / BACKWARD PATHS ...';
		$tests[] = $the_test;
		if((!SmartFileSysUtils::check_file_or_dir_name('/this/is/absolute', 'no')) OR (SmartFileSysUtils::check_file_or_dir_name('/this/is/absolute')) OR (SmartFileSysUtils::check_file_or_dir_name('/this/is/../backward/path'))) {
			$err = 'ERROR: CHECK TEST ABSOLUTE / BACKWARD PATHS ... FAILED !!!';
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'CHECK EXTRACT FOLDER FROM PATH ...';
		$tests[] = $the_test;
		if((string)$get_folder != SmartFileSysUtils::add_dir_last_slash(Smart::dir_name($the_folder))) {
			$err = 'ERROR: Path Extraction FAILED: Dir='.$get_folder.' ; DirName='.SmartFileSysUtils::add_dir_last_slash(Smart::dir_name($the_folder));
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'CHECK EXTRACT FILE AND EXTENSION FROM PATH (1) ...';
		$tests[] = $the_test;
		if((string)$get_folder.SmartFileSysUtils::add_dir_last_slash($the_sufx_folder).$get_file != $the_file) {
			$err = 'ERROR :: Path Extraction FAILED: Re-Composed-File='.$get_folder.SmartFileSysUtils::add_dir_last_slash($the_sufx_folder).$get_file.' ; File='.$the_file;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'CHECK EXTRACT FILE AND EXTENSION FROM PATH (2) ...';
		$tests[] = $the_test;
		if((string)$get_file != $get_xfile.'.'.$get_ext) {
			$err = 'ERROR :: Path Extraction FAILED: File='.$get_file.' ; XFile='.$get_xfile.' ; Ext='.$get_ext;
		} //end if
	} //end if
	//--
	SmartFileSysUtils::raise_error_if_unsafe_path($the_folder);
	if((string)$err == '') {
		$the_test = 'CHECK PATH NAME DIR: check_file_or_dir_name() : '.$the_folder;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSysUtils::check_file_or_dir_name($the_folder);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	SmartFileSysUtils::raise_error_if_unsafe_path($the_file);
	if((string)$err == '') {
		$the_test = 'CHECK PATH NAME FILE: check_file_or_dir_name() : '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSysUtils::check_file_or_dir_name($the_file);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		if(is_dir($get_folder)) {
			$the_test = 'DIR DELETE - INIT CLEANUP: dir_delete() + recursive: '.$get_folder;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::dir_delete($the_base_folder, true);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} else {
			$tests[] = 'DIR DELETE - INIT CLEANUP: Test Not Run (folder does not exists): '.$get_folder;
		} //end if else
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'DIR CREATE RECURSIVE: dir_recursive_create() : '.$the_folder.$long_prefixed.$short_prefixed;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::dir_recursive_create($the_folder.$long_prefixed.$short_prefixed);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'DIR CREATE NON-RECURSIVE: dir_create() : extra/ in : '.$the_extra_folder;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::dir_recursive_create($the_extra_folder);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)DIRECTORY_SEPARATOR != '\\') { // broken links do not work on Windows !
		if((string)$err == '') {
			$the_test = 'CREATE BROKEN FILE LINK FOR DELETION (1): link_create() : as : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create('tmp/cache', $the_broken_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'DELETE BROKEN FILE LINK (1): delete() : as : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::delete($the_broken_link);
			if(($result !== 1) || is_link($the_broken_link)) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'CREATE BROKEN FILE LINK FOR DELETION (2): link_create() : as : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create('tmp/index.html', $the_broken_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'DELETE BROKEN FILE LINK (2): dir_delete() : as : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::dir_delete($the_broken_link);
			if(($result !== 1) || is_link($the_broken_link)) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'CREATE BROKEN FILE LINK: link_create() : as : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create('tmp/index.html', $the_broken_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'CREATE BROKEN DIR LINK: link_create() : as : '.$the_broken_dir_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create('tmp/', $the_broken_dir_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'CREATE A FILE LINK: link_create() : as : '.$the_good_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create(Smart::real_path('tmp/index.html'), $the_good_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'CREATE A DIR LINK: link_create() : as : '.$the_good_dir_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create(Smart::real_path('tmp/'), $the_good_dir_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'FILE WRITE with empty content: write() : '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::write($the_file, '');
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE WRITE: write() / before append : '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::write($the_file, $test_string);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE WRITE: write() +append : '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::write($the_file, $test_string, 'a');
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE READ / Append: read() Full Size: '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::read($the_file);
		if((string)SmartHashCrypto::sha512($result) != (string)SmartHashCrypto::sha512($test_string.$test_string)) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE WRITE: re-write() : '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::write($the_file, $test_string);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)DIRECTORY_SEPARATOR != '\\') { // broken links do not work on Windows !
		if((string)$err == '') {
			$the_test = 'FILE WRITE TO A BROKEN LINK: write() : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::write($the_broken_link, $test_string);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'DELETE THE BROKEN LINK AFTER write() and RE-CREATE IT : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::delete($the_broken_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'RE-CREATE BROKEN FILE LINK [AFTER WRITE]: link_create() : as : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create('tmp/index.html', $the_broken_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'FILE WRITE: write_if_not_exists() with Content Compare to a broken link : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::write_if_not_exists($the_broken_link, $test_string, 'yes');
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'DELETE THE BROKEN LINK AFTER write_if_not_exists() and RE-CREATE IT : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::delete($the_broken_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
		if((string)$err == '') {
			$the_test = 'RE-CREATE BROKEN FILE LINK [AFTER WRITE-IF-NOT-EXISTS]: link_create() : as : '.$the_broken_link;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::link_create('tmp/index.html', $the_broken_link);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'FILE WRITE: write_if_not_exists() without Content Compare : '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::write_if_not_exists($the_file, $test_string, 'no');
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'FILE READ: read() Full Size: '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::read($the_file);
		if((string)SmartHashCrypto::sha512($result) != (string)$test_str_cksum) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE READ: read() Partial Size, First 10 bytes: '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::read($the_file, 10);
		if((string)sha1($result) != (string)sha1(substr($test_string, 0, 10))) { // here we read bytes so substr() not SmartUnicode::sub_str() should be used
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'FILE STATIC-READ: staticread() Full Size: '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::staticread($the_file);
		if((string)SmartHashCrypto::sha512($result) != (string)$test_str_cksum) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE STATIC-READ: staticread() Partial Size, First 10 bytes: '.$the_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::staticread($the_file, 10);
		if((string)sha1($result) != (string)sha1(substr($test_string, 0, 10))) { // here we read bytes so substr() not SmartUnicode::sub_str() should be used
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'FILE COPY: copy() : '.$the_file.' to: '.$the_copy_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::copy($the_file, $the_copy_file);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE COPY with OVERWRITE: copy() : '.$the_file.' to: '.$the_copy_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::copy($the_file, $the_copy_file, true); // overwrite destination file(s)
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE RE-COPY (test should re-write the destination): copy() : '.$the_file.' to: '.$the_move_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::copy($the_file, $the_move_file);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} else {
			$the_test = 'FILE DELETE: delete() : '.$the_move_file;
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::delete($the_move_file);
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'FILE RENAME/MOVE: rename() : '.$the_copy_file.' to: '.$the_move_file;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::rename($the_copy_file, $the_move_file);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		if(is_dir('__development/')) {
			//--
			$the_test = 'RECURSIVE COPY (CLONE) DIR [DEVELOPMENT]: dir_copy() : '.'__development/'.' to: '.$the_folder.'__development';
			$tests[] = $the_test;
			$result = 0;
			$result = SmartFileSystem::dir_copy('__development/', $the_folder.'__development');
			if($result !== 1) {
				$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
			} //end if
			//--
			if((string)$err == '') {
				$the_test = 'DIR COMPARE THE [DEVELOPMENT] SOURCE WITH [DEVELOPMENT] DESTINATION AFTER DIR COPY AND DIR MOVE:'."\n".'compare_folders() : '.'__development/'.' with: '.$the_folder.'__development/';
				$tests[] = $the_test;
				$arr_diff = array();
				$arr_diff = SmartFileSystem::compare_folders('__development', $the_folder.'__development', true, true);
				if(Smart::array_size($arr_diff) > 0) {
					$err = 'ERROR :: '.$the_test.' #DIFFERENCES='.print_r($arr_diff,1);
				} //end if
			} //end if
			//--
		} else {
			$tests[] = 'RECURSIVE COPY (CLONE) DIR [DEVELOPMENT]: Test Not Run (Development environment not detected) ...';
		} //end if else
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'RECURSIVE COPY (CLONE) DIR: dir_copy() : '.$the_folder.' to: '.$the_copy_folder;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::dir_copy($the_folder, $the_copy_folder);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'MOVE/RENAME DIR: dir_rename() : '.$the_copy_folder.' to: '.$the_move_folder;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::dir_rename($the_copy_folder, $the_move_folder);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'DIR COMPARE THE SOURCE WITH DESTINATION AFTER DIR COPY AND DIR MOVE: '.$the_folder.' with: '.$the_move_folder;
		$tests[] = $the_test;
		$arr_diff = array();
		$arr_diff = SmartFileSystem::compare_folders($the_folder, $the_move_folder, true, true);
		if(Smart::array_size($arr_diff) > 0) {
			$err = 'ERROR :: '.$the_test.' #DIFFERENCES='.print_r($arr_diff,1);
		} //end if
	} //end if
	//--
	if((string)$err == '') {
		$the_test = 'DIR DELETE - SIMPLE: dir_delete() non-recursive: '.$the_extra_folder;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::dir_delete($the_extra_folder, false);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	if((string)$err == '') {
		$the_test = 'DIR DELETE - LAST CLEANUP: dir_delete() + recursive: '.$get_folder;
		$tests[] = $the_test;
		$result = 0;
		$result = SmartFileSystem::dir_delete($the_base_folder, true);
		if($result !== 1) {
			$err = 'ERROR :: '.$the_test.' #RESULT='.$result;
		} //end if
	} //end if
	//--

	//--
	$time = 'TOTAL TIME was: '.(microtime(true) - $time);
	//--

	//--
	$end_tests = '##### END TESTS ... '.$time.' sec. #####';
	//--

	//--
	if((string)$err == '') {
		$img_sign = 'lib/core/img/sign_info.png';
		$img_check = 'lib/core/img/q_completed.png';
		$text_main = Smart::escape_js('<span style="color:#83B953;">Good ... Perfect &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; グッド ... パーフェクト</span>');
		$text_info = Smart::escape_js('<h2><span style="color:#83B953;">All</span> the SmartFramework FS Operations <span style="color:#83B953;">Tests PASSED on PHP</span><hr></h2><span style="font-size:14px;">'.Smart::nl_2_br(Smart::escape_html(implode("\n".'* ', $tests)."\n".$end_tests)).'</span>');
	} else {
		$img_sign = 'lib/core/img/sign_error.png';
		$img_check = 'lib/core/img/q_warning.png';
		$text_main = Smart::escape_js('<span style="color:#FF5500;">An ERROR occured ... &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; エラーが発生しました ...</span>');
		$text_info = Smart::escape_js('<h2><span style="color:#FF5500;">A test FAILED</span> when testing FS Operations.<span style="color:#FF5500;"><hr>FAILED Test Details</span>:</h2><br><h3>'.Smart::escape_html($tests[Smart::array_size($tests)-1]).'</h3><br><span style="font-size:14px;"><pre>'.Smart::escape_html($err).'</pre></span>');
	} //end if else
	//--

//--
$html = <<<HTML
<h1>SmartFramework LibFileSystem Tests: DONE ... [ Time: {$time} sec. ]</h1>
<script type="text/javascript">
	SmartJS_BrowserUtils.alert_Dialog(
		'<img src="{$img_sign}" align="right"><h1>{$text_main}</h1><hr><span style="color:#333333;"><img src="{$img_check}" align="right">{$text_info}<br>',
		'',
		'FileSystem Operations Test Suite for SmartFramework: PHP',
		'920',
		'480'
	);
</script>
HTML;
//--

	//--
	return $html;
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function test_barcode2d_qrcode() {
	//--
	$str = 'Smart スマート // Cloud Application Platform クラウドアプリケーションプラットフォーム áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇòóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝßȘŞŠŚŜțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ " <p></p> ? & * ^ $ @ ! ` ~ % () [] {} | \ / + - _ : ; , . #0.97900300';
	//--
	return SmartBarcode2D::getBarcode($str, 'qrcode', 'html-svg', 2, '#3B5897', 'M', 'no');
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_barcode2d_datamatrix() {
	//--
	$str = 'áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇòóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝßȘŞŠŚŜțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ " <p></p> ? & * ^ $ @ ! ` ~ % () [] {} | \ / + - _ : ; , . #0.97900300';
	//--
	return SmartBarcode2D::getBarcode($str, 'semacode', 'html-png', 2, '#3B5897', '', 'no');
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_barcode2d_pdf417() {
	//--
	$str = '1234567890 abcdefghij klmnopqrst uvwxzy 234DSKJFH23YDFKJHaS 1234567890 abcdefghij klmnopqrst uvwxzy 234DSKJFH23YDFKJHaS';
	//--
	return SmartBarcode2D::getBarcode($str, 'pdf417', 'html-svg', 1, '#3B5897', '1', 'no');
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_barcode1d_128B() {
	//--
	$str = 'BAR Code # 128B';
	//--
	return SmartBarcode1D::getBarcode($str, '128', 'html-svg', 1, 15, '#3B5897', true, 'no');
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_barcode1d_93() {
	//--
	$str = 'BAR Code # 93E+c';
	//--
	return SmartBarcode1D::getBarcode($str, '93', 'html-png', 1, 15, '#3B5897', true, 'no');
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_barcode1d_39() {
	//--
	$str = 'BAR Code # 39E';
	//--
	return SmartBarcode1D::getBarcode($str, '39', 'html-svg', 1, 15, '#3B5897', true, 'no');
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function test_barcode1d_kix() {
	//--
	$str = '1231FZ13XHS';
	//--
	return SmartBarcode1D::getBarcode($str, 'KIX', 'html-png', 2, 15, '#3B5897', true, 'no');
	//--
} //END FUNCTION
//==================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class Smart Test Suite SQLite3
 *
 * @access 		private
 * @internal
 *
 */
class SmartTestSQLite3Model {

	// ->
	// v.160921

private $db;

//============================================================
public function __construct() {

	//--
	$this->db = new SmartSQliteDb('tmp/testunit.sqlite3');
	//--
	$this->db->open();
	//--

	//-- init (create) the sample tables if they do not exist
	$this->init_table_main_samples();
	$this->init_table_samples_countries();
	//--

} //END FUNCTION
//============================================================


//============================================================
public function __destruct() {

	//--
	if(is_object($this->db)) {
		$this->db->close(); // clean shutdown
	} //end if
	//--

} //END FUNCTION
//============================================================


//============================================================
public function getConnection() {
	//--
	return $this->db;
	//--
} //END FUNCTION
//============================================================


//============================================================
private function init_table_main_samples() {

	//-- create table has a built-in check to avoid run if table is already created
	$this->db->create_table('table_main_sample', "id character varying(10) NOT NULL, name character varying(100) NOT NULL, description text NOT NULL");
	//--

} //END FUNCTION
//============================================================


//============================================================
private function init_table_samples_countries() {

	//--
	if($this->db->check_if_table_exists('sample_countries') == 1) {
		return; // prevent execution if the table has been already created
	} //end if
	//--

	//--
	$rows = array(
		array('iso'=>'AF',
		'name'=>'Afghanistan',
		'iso3'=>'AFG',
		'numcode'=>'4',
		),
		array('iso'=>'AL',
		'name'=>'Albania',
		'iso3'=>'ALB',
		'numcode'=>'8',
		),
		array('iso'=>'DZ',
		'name'=>'Algeria',
		'iso3'=>'DZA',
		'numcode'=>'12',
		),
		array('iso'=>'AS',
		'name'=>'American Samoa',
		'iso3'=>'ASM',
		'numcode'=>'16',
		),
		array('iso'=>'AD',
		'name'=>'Andorra',
		'iso3'=>'AND',
		'numcode'=>'20',
		),
		array('iso'=>'AO',
		'name'=>'Angola',
		'iso3'=>'AGO',
		'numcode'=>'24',
		),
		array('iso'=>'AI',
		'name'=>'Anguilla',
		'iso3'=>'AIA',
		'numcode'=>'660',
		),
		array('iso'=>'AQ',
		'name'=>'Antarctica',
		'iso3'=>'ART',
		'numcode'=>'0',
		),
		array('iso'=>'AG',
		'name'=>'Antigua and Barbuda',
		'iso3'=>'ATG',
		'numcode'=>'28',
		),
		array('iso'=>'AR',
		'name'=>'Argentina',
		'iso3'=>'ARG',
		'numcode'=>'32',
		),
		array('iso'=>'AM',
		'name'=>'Armenia',
		'iso3'=>'ARM',
		'numcode'=>'51',
		),
		array('iso'=>'AW',
		'name'=>'Aruba',
		'iso3'=>'ABW',
		'numcode'=>'533',
		),
		array('iso'=>'AU',
		'name'=>'Australia',
		'iso3'=>'AUS',
		'numcode'=>'36',
		),
		array('iso'=>'AT',
		'name'=>'Austria',
		'iso3'=>'AUT',
		'numcode'=>'40',
		),
		array('iso'=>'AZ',
		'name'=>'Azerbaijan',
		'iso3'=>'AZE',
		'numcode'=>'31',
		),
		array('iso'=>'BS',
		'name'=>'Bahamas',
		'iso3'=>'BHS',
		'numcode'=>'44',
		),
		array('iso'=>'BH',
		'name'=>'Bahrain',
		'iso3'=>'BHR',
		'numcode'=>'48',
		),
		array('iso'=>'BD',
		'name'=>'Bangladesh',
		'iso3'=>'BGD',
		'numcode'=>'50',
		),
		array('iso'=>'BB',
		'name'=>'Barbados',
		'iso3'=>'BRB',
		'numcode'=>'52',
		),
		array('iso'=>'BY',
		'name'=>'Belarus',
		'iso3'=>'BLR',
		'numcode'=>'112',
		),
		array('iso'=>'BE',
		'name'=>'Belgium',
		'iso3'=>'BEL',
		'numcode'=>'56',
		),
		array('iso'=>'BZ',
		'name'=>'Belize',
		'iso3'=>'BLZ',
		'numcode'=>'84',
		),
		array('iso'=>'BJ',
		'name'=>'Benin',
		'iso3'=>'BEN',
		'numcode'=>'204',
		),
		array('iso'=>'BM',
		'name'=>'Bermuda',
		'iso3'=>'BMU',
		'numcode'=>'60',
		),
		array('iso'=>'BT',
		'name'=>'Bhutan',
		'iso3'=>'BTN',
		'numcode'=>'64',
		),
		array('iso'=>'BO',
		'name'=>'Bolivia',
		'iso3'=>'BOL',
		'numcode'=>'68',
		),
		array('iso'=>'BA',
		'name'=>'Bosnia and Herzegovina',
		'iso3'=>'BIH',
		'numcode'=>'70',
		),
		array('iso'=>'BW',
		'name'=>'Botswana',
		'iso3'=>'BWA',
		'numcode'=>'72',
		),
		array('iso'=>'BV',
		'name'=>'Bouvet Island',
		'iso3'=>'BVT',
		'numcode'=>'0',
		),
		array('iso'=>'BR',
		'name'=>'Brazil',
		'iso3'=>'BRA',
		'numcode'=>'76',
		),
		array('iso'=>'IO',
		'name'=>'British Indian Ocean Territory',
		'iso3'=>'BIO',
		'numcode'=>'0',
		),
		array('iso'=>'BN',
		'name'=>'Brunei Darussalam',
		'iso3'=>'BRN',
		'numcode'=>'96',
		),
		array('iso'=>'BG',
		'name'=>'Bulgaria',
		'iso3'=>'BGR',
		'numcode'=>'100',
		),
		array('iso'=>'BF',
		'name'=>'Burkina Faso',
		'iso3'=>'BFA',
		'numcode'=>'854',
		),
		array('iso'=>'BI',
		'name'=>'Burundi',
		'iso3'=>'BDI',
		'numcode'=>'108',
		),
		array('iso'=>'KH',
		'name'=>'Cambodia',
		'iso3'=>'KHM',
		'numcode'=>'116',
		),
		array('iso'=>'CM',
		'name'=>'Cameroon',
		'iso3'=>'CMR',
		'numcode'=>'120',
		),
		array('iso'=>'CA',
		'name'=>'Canada',
		'iso3'=>'CAN',
		'numcode'=>'124',
		),
		array('iso'=>'CV',
		'name'=>'Cape Verde',
		'iso3'=>'CPV',
		'numcode'=>'132',
		),
		array('iso'=>'KY',
		'name'=>'Cayman Islands',
		'iso3'=>'CYM',
		'numcode'=>'136',
		),
		array('iso'=>'CF',
		'name'=>'Central African Republic',
		'iso3'=>'CAF',
		'numcode'=>'140',
		),
		array('iso'=>'TD',
		'name'=>'Chad',
		'iso3'=>'TCD',
		'numcode'=>'148',
		),
		array('iso'=>'CL',
		'name'=>'Chile',
		'iso3'=>'CHL',
		'numcode'=>'152',
		),
		array('iso'=>'CN',
		'name'=>'China',
		'iso3'=>'CHN',
		'numcode'=>'156',
		),
		array('iso'=>'CX',
		'name'=>'Christmas Island',
		'iso3'=>'CMI',
		'numcode'=>'0',
		),
		array('iso'=>'CC',
		'name'=>'Cocos (Keeling) Islands',
		'iso3'=>'CKI',
		'numcode'=>'0',
		),
		array('iso'=>'CO',
		'name'=>'Colombia',
		'iso3'=>'COL',
		'numcode'=>'170',
		),
		array('iso'=>'KM',
		'name'=>'Comoros',
		'iso3'=>'COM',
		'numcode'=>'174',
		),
		array('iso'=>'CG',
		'name'=>'Congo',
		'iso3'=>'COG',
		'numcode'=>'178',
		),
		array('iso'=>'CD',
		'name'=>'Congo, the Democratic Republic of the',
		'iso3'=>'COD',
		'numcode'=>'180',
		),
		array('iso'=>'CK',
		'name'=>'Cook Islands',
		'iso3'=>'COK',
		'numcode'=>'184',
		),
		array('iso'=>'CR',
		'name'=>'Costa Rica',
		'iso3'=>'CRI',
		'numcode'=>'188',
		),
		array('iso'=>'CI',
		'name'=>'Cote D\'Ivoire',
		'iso3'=>'CIV',
		'numcode'=>'384',
		),
		array('iso'=>'HR',
		'name'=>'Croatia',
		'iso3'=>'HRV',
		'numcode'=>'191',
		),
		array('iso'=>'CU',
		'name'=>'Cuba',
		'iso3'=>'CUB',
		'numcode'=>'192',
		),
		array('iso'=>'CY',
		'name'=>'Cyprus',
		'iso3'=>'CYP',
		'numcode'=>'196',
		),
		array('iso'=>'CZ',
		'name'=>'Czech Republic',
		'iso3'=>'CZE',
		'numcode'=>'203',
		),
		array('iso'=>'DK',
		'name'=>'Denmark',
		'iso3'=>'DNK',
		'numcode'=>'208',
		),
		array('iso'=>'DJ',
		'name'=>'Djibouti',
		'iso3'=>'DJI',
		'numcode'=>'262',
		),
		array('iso'=>'DM',
		'name'=>'Dominica',
		'iso3'=>'DMA',
		'numcode'=>'212',
		),
		array('iso'=>'DO',
		'name'=>'Dominican Republic',
		'iso3'=>'DOM',
		'numcode'=>'214',
		),
		array('iso'=>'EC',
		'name'=>'Ecuador',
		'iso3'=>'ECU',
		'numcode'=>'218',
		),
		array('iso'=>'EG',
		'name'=>'Egypt',
		'iso3'=>'EGY',
		'numcode'=>'818',
		),
		array('iso'=>'SV',
		'name'=>'El Salvador',
		'iso3'=>'SLV',
		'numcode'=>'222',
		),
		array('iso'=>'GQ',
		'name'=>'Equatorial Guinea',
		'iso3'=>'GNQ',
		'numcode'=>'226',
		),
		array('iso'=>'ER',
		'name'=>'Eritrea',
		'iso3'=>'ERI',
		'numcode'=>'232',
		),
		array('iso'=>'EE',
		'name'=>'Estonia',
		'iso3'=>'EST',
		'numcode'=>'233',
		),
		array('iso'=>'ET',
		'name'=>'Ethiopia',
		'iso3'=>'ETH',
		'numcode'=>'231',
		),
		array('iso'=>'FK',
		'name'=>'Falkland Islands (Malvinas)',
		'iso3'=>'FLK',
		'numcode'=>'238',
		),
		array('iso'=>'FO',
		'name'=>'Faroe Islands',
		'iso3'=>'FRO',
		'numcode'=>'234',
		),
		array('iso'=>'FJ',
		'name'=>'Fiji',
		'iso3'=>'FJI',
		'numcode'=>'242',
		),
		array('iso'=>'FI',
		'name'=>'Finland',
		'iso3'=>'FIN',
		'numcode'=>'246',
		),
		array('iso'=>'FR',
		'name'=>'France',
		'iso3'=>'FRA',
		'numcode'=>'250',
		),
		array('iso'=>'GF',
		'name'=>'French Guiana',
		'iso3'=>'GUF',
		'numcode'=>'254',
		),
		array('iso'=>'PF',
		'name'=>'French Polynesia',
		'iso3'=>'PYF',
		'numcode'=>'258',
		),
		array('iso'=>'TF',
		'name'=>'French Southern Territories',
		'iso3'=>'FST',
		'numcode'=>'0',
		),
		array('iso'=>'GA',
		'name'=>'Gabon',
		'iso3'=>'GAB',
		'numcode'=>'266',
		),
		array('iso'=>'GM',
		'name'=>'Gambia',
		'iso3'=>'GMB',
		'numcode'=>'270',
		),
		array('iso'=>'GE',
		'name'=>'Georgia',
		'iso3'=>'GEO',
		'numcode'=>'268',
		),
		array('iso'=>'DE',
		'name'=>'Germany',
		'iso3'=>'DEU',
		'numcode'=>'276',
		),
		array('iso'=>'GH',
		'name'=>'Ghana',
		'iso3'=>'GHA',
		'numcode'=>'288',
		),
		array('iso'=>'GI',
		'name'=>'Gibraltar',
		'iso3'=>'GIB',
		'numcode'=>'292',
		),
		array('iso'=>'GR',
		'name'=>'Greece',
		'iso3'=>'GRC',
		'numcode'=>'300',
		),
		array('iso'=>'GL',
		'name'=>'Greenland',
		'iso3'=>'GRL',
		'numcode'=>'304',
		),
		array('iso'=>'GD',
		'name'=>'Grenada',
		'iso3'=>'GRD',
		'numcode'=>'308',
		),
		array('iso'=>'GP',
		'name'=>'Guadeloupe',
		'iso3'=>'GLP',
		'numcode'=>'312',
		),
		array('iso'=>'GU',
		'name'=>'Guam',
		'iso3'=>'GUM',
		'numcode'=>'316',
		),
		array('iso'=>'GT',
		'name'=>'Guatemala',
		'iso3'=>'GTM',
		'numcode'=>'320',
		),
		array('iso'=>'GN',
		'name'=>'Guinea',
		'iso3'=>'GIN',
		'numcode'=>'324',
		),
		array('iso'=>'GW',
		'name'=>'Guinea-Bissau',
		'iso3'=>'GNB',
		'numcode'=>'624',
		),
		array('iso'=>'GY',
		'name'=>'Guyana',
		'iso3'=>'GUY',
		'numcode'=>'328',
		),
		array('iso'=>'HT',
		'name'=>'Haiti',
		'iso3'=>'HTI',
		'numcode'=>'332',
		),
		array('iso'=>'HM',
		'name'=>'Heard Island and Mcdonald Islands',
		'iso3'=>'HMI',
		'numcode'=>'0',
		),
		array('iso'=>'VA',
		'name'=>'Holy See (Vatican City State)',
		'iso3'=>'VAT',
		'numcode'=>'336',
		),
		array('iso'=>'HN',
		'name'=>'Honduras',
		'iso3'=>'HND',
		'numcode'=>'340',
		),
		array('iso'=>'HK',
		'name'=>'Hong Kong',
		'iso3'=>'HKG',
		'numcode'=>'344',
		),
		array('iso'=>'HU',
		'name'=>'Hungary',
		'iso3'=>'HUN',
		'numcode'=>'348',
		),
		array('iso'=>'IS',
		'name'=>'Iceland',
		'iso3'=>'ISL',
		'numcode'=>'352',
		),
		array('iso'=>'IN',
		'name'=>'India',
		'iso3'=>'IND',
		'numcode'=>'356',
		),
		array('iso'=>'ID',
		'name'=>'Indonesia',
		'iso3'=>'IDN',
		'numcode'=>'360',
		),
		array('iso'=>'IR',
		'name'=>'Iran, Islamic Republic of',
		'iso3'=>'IRN',
		'numcode'=>'364',
		),
		array('iso'=>'IQ',
		'name'=>'Iraq',
		'iso3'=>'IRQ',
		'numcode'=>'368',
		),
		array('iso'=>'IE',
		'name'=>'Ireland',
		'iso3'=>'IRL',
		'numcode'=>'372',
		),
		array('iso'=>'IL',
		'name'=>'Israel',
		'iso3'=>'ISR',
		'numcode'=>'376',
		),
		array('iso'=>'IT',
		'name'=>'Italy',
		'iso3'=>'ITA',
		'numcode'=>'380',
		),
		array('iso'=>'JM',
		'name'=>'Jamaica',
		'iso3'=>'JAM',
		'numcode'=>'388',
		),
		array('iso'=>'JP',
		'name'=>'Japan',
		'iso3'=>'JPN',
		'numcode'=>'392',
		),
		array('iso'=>'JO',
		'name'=>'Jordan',
		'iso3'=>'JOR',
		'numcode'=>'400',
		),
		array('iso'=>'KZ',
		'name'=>'Kazakhstan',
		'iso3'=>'KAZ',
		'numcode'=>'398',
		),
		array('iso'=>'KE',
		'name'=>'Kenya',
		'iso3'=>'KEN',
		'numcode'=>'404',
		),
		array('iso'=>'KI',
		'name'=>'Kiribati',
		'iso3'=>'KIR',
		'numcode'=>'296',
		),
		array('iso'=>'KP',
		'name'=>'Korea, Democratic People\'s Republic of',
		'iso3'=>'PRK',
		'numcode'=>'408',
		),
		array('iso'=>'KR',
		'name'=>'Korea, Republic of',
		'iso3'=>'KOR',
		'numcode'=>'410',
		),
		array('iso'=>'KW',
		'name'=>'Kuwait',
		'iso3'=>'KWT',
		'numcode'=>'414',
		),
		array('iso'=>'KG',
		'name'=>'Kyrgyzstan',
		'iso3'=>'KGZ',
		'numcode'=>'417',
		),
		array('iso'=>'LA',
		'name'=>'Lao People\'s Democratic Republic',
		'iso3'=>'LAO',
		'numcode'=>'418',
		),
		array('iso'=>'LV',
		'name'=>'Latvia',
		'iso3'=>'LVA',
		'numcode'=>'428',
		),
		array('iso'=>'LB',
		'name'=>'Lebanon',
		'iso3'=>'LBN',
		'numcode'=>'422',
		),
		array('iso'=>'LS',
		'name'=>'Lesotho',
		'iso3'=>'LSO',
		'numcode'=>'426',
		),
		array('iso'=>'LR',
		'name'=>'Liberia',
		'iso3'=>'LBR',
		'numcode'=>'430',
		),
		array('iso'=>'LY',
		'name'=>'Libyan Arab Jamahiriya',
		'iso3'=>'LBY',
		'numcode'=>'434',
		),
		array('iso'=>'LI',
		'name'=>'Liechtenstein',
		'iso3'=>'LIE',
		'numcode'=>'438',
		),
		array('iso'=>'LT',
		'name'=>'Lithuania',
		'iso3'=>'LTU',
		'numcode'=>'440',
		),
		array('iso'=>'LU',
		'name'=>'Luxembourg',
		'iso3'=>'LUX',
		'numcode'=>'442',
		),
		array('iso'=>'MO',
		'name'=>'Macao',
		'iso3'=>'MAC',
		'numcode'=>'446',
		),
		array('iso'=>'MK',
		'name'=>'Macedonia, the Former Yugoslav Republic of',
		'iso3'=>'MKD',
		'numcode'=>'807',
		),
		array('iso'=>'MG',
		'name'=>'Madagascar',
		'iso3'=>'MDG',
		'numcode'=>'450',
		),
		array('iso'=>'MW',
		'name'=>'Malawi',
		'iso3'=>'MWI',
		'numcode'=>'454',
		),
		array('iso'=>'MY',
		'name'=>'Malaysia',
		'iso3'=>'MYS',
		'numcode'=>'458',
		),
		array('iso'=>'MV',
		'name'=>'Maldives',
		'iso3'=>'MDV',
		'numcode'=>'462',
		),
		array('iso'=>'ML',
		'name'=>'Mali',
		'iso3'=>'MLI',
		'numcode'=>'466',
		),
		array('iso'=>'MT',
		'name'=>'Malta',
		'iso3'=>'MLT',
		'numcode'=>'470',
		),
		array('iso'=>'MH',
		'name'=>'Marshall Islands',
		'iso3'=>'MHL',
		'numcode'=>'584',
		),
		array('iso'=>'MQ',
		'name'=>'Martinique',
		'iso3'=>'MTQ',
		'numcode'=>'474',
		),
		array('iso'=>'MR',
		'name'=>'Mauritania',
		'iso3'=>'MRT',
		'numcode'=>'478',
		),
		array('iso'=>'MU',
		'name'=>'Mauritius',
		'iso3'=>'MUS',
		'numcode'=>'480',
		),
		array('iso'=>'YT',
		'name'=>'Mayotte',
		'iso3'=>'MAY',
		'numcode'=>'0',
		),
		array('iso'=>'MX',
		'name'=>'Mexico',
		'iso3'=>'MEX',
		'numcode'=>'484',
		),
		array('iso'=>'FM',
		'name'=>'Micronesia, Federated States of',
		'iso3'=>'FSM',
		'numcode'=>'583',
		),
		array('iso'=>'MD',
		'name'=>'Moldova, Republic of',
		'iso3'=>'MDA',
		'numcode'=>'498',
		),
		array('iso'=>'MC',
		'name'=>'Monaco',
		'iso3'=>'MCO',
		'numcode'=>'492',
		),
		array('iso'=>'MN',
		'name'=>'Mongolia',
		'iso3'=>'MNG',
		'numcode'=>'496',
		),
		array('iso'=>'MS',
		'name'=>'Montserrat',
		'iso3'=>'MSR',
		'numcode'=>'500',
		),
		array('iso'=>'MA',
		'name'=>'Morocco',
		'iso3'=>'MAR',
		'numcode'=>'504',
		),
		array('iso'=>'MZ',
		'name'=>'Mozambique',
		'iso3'=>'MOZ',
		'numcode'=>'508',
		),
		array('iso'=>'MM',
		'name'=>'Myanmar',
		'iso3'=>'MMR',
		'numcode'=>'104',
		),
		array('iso'=>'NA',
		'name'=>'Namibia',
		'iso3'=>'NAM',
		'numcode'=>'516',
		),
		array('iso'=>'NR',
		'name'=>'Nauru',
		'iso3'=>'NRU',
		'numcode'=>'520',
		),
		array('iso'=>'NP',
		'name'=>'Nepal',
		'iso3'=>'NPL',
		'numcode'=>'524',
		),
		array('iso'=>'NL',
		'name'=>'Netherlands',
		'iso3'=>'NLD',
		'numcode'=>'528',
		),
		array('iso'=>'AN',
		'name'=>'Netherlands Antilles',
		'iso3'=>'ANT',
		'numcode'=>'530',
		),
		array('iso'=>'NC',
		'name'=>'New Caledonia',
		'iso3'=>'NCL',
		'numcode'=>'540',
		),
		array('iso'=>'NZ',
		'name'=>'New Zealand',
		'iso3'=>'NZL',
		'numcode'=>'554',
		),
		array('iso'=>'NI',
		'name'=>'Nicaragua',
		'iso3'=>'NIC',
		'numcode'=>'558',
		),
		array('iso'=>'NE',
		'name'=>'Niger',
		'iso3'=>'NER',
		'numcode'=>'562',
		),
		array('iso'=>'NG',
		'name'=>'Nigeria',
		'iso3'=>'NGA',
		'numcode'=>'566',
		),
		array('iso'=>'NU',
		'name'=>'Niue',
		'iso3'=>'NIU',
		'numcode'=>'570',
		),
		array('iso'=>'NF',
		'name'=>'Norfolk Island',
		'iso3'=>'NFK',
		'numcode'=>'574',
		),
		array('iso'=>'MP',
		'name'=>'Northern Mariana Islands',
		'iso3'=>'MNP',
		'numcode'=>'580',
		),
		array('iso'=>'NO',
		'name'=>'Norway',
		'iso3'=>'NOR',
		'numcode'=>'578',
		),
		array('iso'=>'OM',
		'name'=>'Oman',
		'iso3'=>'OMN',
		'numcode'=>'512',
		),
		array('iso'=>'PK',
		'name'=>'Pakistan',
		'iso3'=>'PAK',
		'numcode'=>'586',
		),
		array('iso'=>'PW',
		'name'=>'Palau',
		'iso3'=>'PLW',
		'numcode'=>'585',
		),
		array('iso'=>'PS',
		'name'=>'Palestinian Territory, Occupied',
		'iso3'=>'PTO',
		'numcode'=>'0',
		),
		array('iso'=>'PA',
		'name'=>'Panama',
		'iso3'=>'PAN',
		'numcode'=>'591',
		),
		array('iso'=>'PG',
		'name'=>'Papua New Guinea',
		'iso3'=>'PNG',
		'numcode'=>'598',
		),
		array('iso'=>'PY',
		'name'=>'Paraguay',
		'iso3'=>'PRY',
		'numcode'=>'600',
		),
		array('iso'=>'PE',
		'name'=>'Peru',
		'iso3'=>'PER',
		'numcode'=>'604',
		),
		array('iso'=>'PH',
		'name'=>'Philippines',
		'iso3'=>'PHL',
		'numcode'=>'608',
		),
		array('iso'=>'PN',
		'name'=>'Pitcairn',
		'iso3'=>'PCN',
		'numcode'=>'612',
		),
		array('iso'=>'PL',
		'name'=>'Poland',
		'iso3'=>'POL',
		'numcode'=>'616',
		),
		array('iso'=>'PT',
		'name'=>'Portugal',
		'iso3'=>'PRT',
		'numcode'=>'620',
		),
		array('iso'=>'PR',
		'name'=>'Puerto Rico',
		'iso3'=>'PRI',
		'numcode'=>'630',
		),
		array('iso'=>'QA',
		'name'=>'Qatar',
		'iso3'=>'QAT',
		'numcode'=>'634',
		),
		array('iso'=>'RE',
		'name'=>'Reunion',
		'iso3'=>'REU',
		'numcode'=>'638',
		),
		array('iso'=>'RO',
		'name'=>'Romania',
		'iso3'=>'ROM',
		'numcode'=>'642',
		),
		array('iso'=>'RU',
		'name'=>'Russian Federation',
		'iso3'=>'RUS',
		'numcode'=>'643',
		),
		array('iso'=>'RW',
		'name'=>'Rwanda',
		'iso3'=>'RWA',
		'numcode'=>'646',
		),
		array('iso'=>'SH',
		'name'=>'Saint Helena',
		'iso3'=>'SHN',
		'numcode'=>'654',
		),
		array('iso'=>'KN',
		'name'=>'Saint Kitts and Nevis',
		'iso3'=>'KNA',
		'numcode'=>'659',
		),
		array('iso'=>'LC',
		'name'=>'Saint Lucia',
		'iso3'=>'LCA',
		'numcode'=>'662',
		),
		array('iso'=>'PM',
		'name'=>'Saint Pierre and Miquelon',
		'iso3'=>'SPM',
		'numcode'=>'666',
		),
		array('iso'=>'VC',
		'name'=>'Saint Vincent and the Grenadines',
		'iso3'=>'VCT',
		'numcode'=>'670',
		),
		array('iso'=>'WS',
		'name'=>'Samoa',
		'iso3'=>'WSM',
		'numcode'=>'882',
		),
		array('iso'=>'SM',
		'name'=>'San Marino',
		'iso3'=>'SMR',
		'numcode'=>'674',
		),
		array('iso'=>'ST',
		'name'=>'Sao Tome and Principe',
		'iso3'=>'STP',
		'numcode'=>'678',
		),
		array('iso'=>'SA',
		'name'=>'Saudi Arabia',
		'iso3'=>'SAU',
		'numcode'=>'682',
		),
		array('iso'=>'SN',
		'name'=>'Senegal',
		'iso3'=>'SEN',
		'numcode'=>'686',
		),
		array('iso'=>'CS',
		'name'=>'Serbia and Montenegro',
		'iso3'=>'SNM',
		'numcode'=>'0',
		),
		array('iso'=>'SC',
		'name'=>'Seychelles',
		'iso3'=>'SYC',
		'numcode'=>'690',
		),
		array('iso'=>'SL',
		'name'=>'Sierra Leone',
		'iso3'=>'SLE',
		'numcode'=>'694',
		),
		array('iso'=>'SG',
		'name'=>'Singapore',
		'iso3'=>'SGP',
		'numcode'=>'702',
		),
		array('iso'=>'SK',
		'name'=>'Slovakia',
		'iso3'=>'SVK',
		'numcode'=>'703',
		),
		array('iso'=>'SI',
		'name'=>'Slovenia',
		'iso3'=>'SVN',
		'numcode'=>'705',
		),
		array('iso'=>'SB',
		'name'=>'Solomon Islands',
		'iso3'=>'SLB',
		'numcode'=>'90',
		),
		array('iso'=>'SO',
		'name'=>'Somalia',
		'iso3'=>'SOM',
		'numcode'=>'706',
		),
		array('iso'=>'ZA',
		'name'=>'South Africa',
		'iso3'=>'ZAF',
		'numcode'=>'710',
		),
		array('iso'=>'GS',
		'name'=>'South Georgia and the South Sandwich Islands',
		'iso3'=>'SGS',
		'numcode'=>'0',
		),
		array('iso'=>'ES',
		'name'=>'Spain',
		'iso3'=>'ESP',
		'numcode'=>'724',
		),
		array('iso'=>'LK',
		'name'=>'Sri Lanka',
		'iso3'=>'LKA',
		'numcode'=>'144',
		),
		array('iso'=>'SD',
		'name'=>'Sudan',
		'iso3'=>'SDN',
		'numcode'=>'736',
		),
		array('iso'=>'SR',
		'name'=>'Suriname',
		'iso3'=>'SUR',
		'numcode'=>'740',
		),
		array('iso'=>'SJ',
		'name'=>'Svalbard and Jan Mayen',
		'iso3'=>'SJM',
		'numcode'=>'744',
		),
		array('iso'=>'SZ',
		'name'=>'Swaziland',
		'iso3'=>'SWZ',
		'numcode'=>'748',
		),
		array('iso'=>'SE',
		'name'=>'Sweden',
		'iso3'=>'SWE',
		'numcode'=>'752',
		),
		array('iso'=>'CH',
		'name'=>'Switzerland',
		'iso3'=>'CHE',
		'numcode'=>'756',
		),
		array('iso'=>'SY',
		'name'=>'Syrian Arab Republic',
		'iso3'=>'SYR',
		'numcode'=>'760',
		),
		array('iso'=>'TW',
		'name'=>'Taiwan, Province of China',
		'iso3'=>'TWN',
		'numcode'=>'158',
		),
		array('iso'=>'TJ',
		'name'=>'Tajikistan',
		'iso3'=>'TJK',
		'numcode'=>'762',
		),
		array('iso'=>'TZ',
		'name'=>'Tanzania, United Republic of',
		'iso3'=>'TZA',
		'numcode'=>'834',
		),
		array('iso'=>'TH',
		'name'=>'Thailand',
		'iso3'=>'THA',
		'numcode'=>'764',
		),
		array('iso'=>'TL',
		'name'=>'Timor-Leste',
		'iso3'=>'TIM',
		'numcode'=>'0',
		),
		array('iso'=>'TG',
		'name'=>'Togo',
		'iso3'=>'TGO',
		'numcode'=>'768',
		),
		array('iso'=>'TK',
		'name'=>'Tokelau',
		'iso3'=>'TKL',
		'numcode'=>'772',
		),
		array('iso'=>'TO',
		'name'=>'Tonga',
		'iso3'=>'TON',
		'numcode'=>'776',
		),
		array('iso'=>'TT',
		'name'=>'Trinidad and Tobago',
		'iso3'=>'TTO',
		'numcode'=>'780',
		),
		array('iso'=>'TN',
		'name'=>'Tunisia',
		'iso3'=>'TUN',
		'numcode'=>'788',
		),
		array('iso'=>'TR',
		'name'=>'Turkey',
		'iso3'=>'TUR',
		'numcode'=>'792',
		),
		array('iso'=>'TM',
		'name'=>'Turkmenistan',
		'iso3'=>'TKM',
		'numcode'=>'795',
		),
		array('iso'=>'TC',
		'name'=>'Turks and Caicos Islands',
		'iso3'=>'TCA',
		'numcode'=>'796',
		),
		array('iso'=>'TV',
		'name'=>'Tuvalu',
		'iso3'=>'TUV',
		'numcode'=>'798',
		),
		array('iso'=>'UG',
		'name'=>'Uganda',
		'iso3'=>'UGA',
		'numcode'=>'800',
		),
		array('iso'=>'UA',
		'name'=>'Ukraine',
		'iso3'=>'UKR',
		'numcode'=>'804',
		),
		array('iso'=>'AE',
		'name'=>'United Arab Emirates',
		'iso3'=>'ARE',
		'numcode'=>'784',
		),
		array('iso'=>'GB',
		'name'=>'United Kingdom',
		'iso3'=>'GBR',
		'numcode'=>'826',
		),
		array('iso'=>'US',
		'name'=>'United States',
		'iso3'=>'USA',
		'numcode'=>'840',
		),
		array('iso'=>'UM',
		'name'=>'United States Minor Outlying Islands',
		'iso3'=>'USI',
		'numcode'=>'0',
		),
		array('iso'=>'UY',
		'name'=>'Uruguay',
		'iso3'=>'URY',
		'numcode'=>'858',
		),
		array('iso'=>'UZ',
		'name'=>'Uzbekistan',
		'iso3'=>'UZB',
		'numcode'=>'860',
		),
		array('iso'=>'VU',
		'name'=>'Vanuatu',
		'iso3'=>'VUT',
		'numcode'=>'548',
		),
		array('iso'=>'VE',
		'name'=>'Venezuela',
		'iso3'=>'VEN',
		'numcode'=>'862',
		),
		array('iso'=>'VN',
		'name'=>'Viet Nam',
		'iso3'=>'VNM',
		'numcode'=>'704',
		),
		array('iso'=>'VG',
		'name'=>'Virgin Islands, British',
		'iso3'=>'VGB',
		'numcode'=>'92',
		),
		array('iso'=>'VI',
		'name'=>'Virgin Islands, U.s.',
		'iso3'=>'VIR',
		'numcode'=>'850',
		),
		array('iso'=>'WF',
		'name'=>'Wallis and Futuna',
		'iso3'=>'WLF',
		'numcode'=>'876',
		),
		array('iso'=>'EH',
		'name'=>'Western Sahara',
		'iso3'=>'ESH',
		'numcode'=>'732',
		),
		array('iso'=>'YE',
		'name'=>'Yemen',
		'iso3'=>'YEM',
		'numcode'=>'887',
		),
		array('iso'=>'ZM',
		'name'=>'Zambia',
		'iso3'=>'ZMB',
		'numcode'=>'894',
		),
		array('iso'=>'ZW',
		'name'=>'Zimbabwe',
		'iso3'=>'ZWE',
		'numcode'=>'716',
		)
	);
	//--

	//--
	$this->db->write_data('BEGIN'); // start transaction
	$this->db->create_table('sample_countries', "iso character varying(2) PRIMARY KEY NOT NULL, name character varying(100) NOT NULL, iso3 character varying(3) NOT NULL, numcode integer NOT NULL");
	foreach($rows as $key => $row) {
		$this->db->write_data('INSERT INTO sample_countries '.$this->db->prepare_write_statement($row, 'insert'));
	} //end foreach
	$this->db->write_data('UPDATE sample_countries '.$this->db->prepare_write_statement($rows[0], 'update').' WHERE (iso IS NULL)'); // test
	$this->db->read_data('SELECT * FROM sample_countries WHERE (iso '.$this->db->prepare_write_statement(array('US', 7, null), 'in-select').')');
	$this->db->write_data('COMMIT');
	//--

} //END FUNCTION
//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>