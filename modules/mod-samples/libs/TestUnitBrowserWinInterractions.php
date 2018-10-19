<?php
// [LIB - SmartFramework / Samples / Test Browser (Window) Interractions]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

// Class: \SmartModExtLib\Samples\TestUnitBrowserWinInterractions
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
 * Test Browser (Window) Interractions
 *
 * @access 		private
 * @internal
 *
 * @version 	v.170519
 *
 */
final class TestUnitBrowserWinInterractions {

	// ::

	//============================================================
	public static function winModalPopupContentHtml() {
		//--
		return (string) '<div><h1>Interractions Test for Browser Window '.\Smart::escape_html(date('Y-m-d H:i:s')).'</h1></div>'.self::bttn_open_modal(true).' &nbsp;&nbsp;&nbsp; '.self::bttn_open_modal(false).'<br><br>'.self::bttn_open_popup(true).' &nbsp;&nbsp;&nbsp; '.self::bttn_open_popup(false).'<br><br>'.self::bttn_set_confirm_unload().' &nbsp;&nbsp;&nbsp; '.self::bttn_set_parent_refresh().'<br><br>'.self::bttn_close_modal_or_popup();
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function bttnModalTestInit() {
		//--
		return (string) self::bttn_open_modal(true, 'test_bw_win_interractions_modal_start');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function bttnPopupTestInit() {
		//--
		return (string) self::bttn_open_popup(true, 'test_bw_win_interractions_popup_start');
		//--
	} //END FUNCTION
	//============================================================


	//##### PRIVATES


	//============================================================
	private static function bttn_open_modal($forced, $winname='') {
		//--
		if((string)$winname == '') {
			$wname = 'test_bw_win_interractions_mod_'.\Smart::uuid_10_seq().'_'.\Smart::uuid_10_num().'_'.\Smart::uuid_10_str();
		} else {
			$wname = (string) $winname;
		} //end if else
		if($forced) {
			$set = '-1';
			$btn = 'Open Modal (strict)';
		} else {
			$set = '0';
			$btn = 'Open Modal or PopUp (auto)';
		} //end if else
		//--
		return (string) '<a class="ux-button ux-button-regular" style="min-width:320px;" target="'.\Smart::escape_html($wname).'" href="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.interractions'.'" onClick="SmartJS_BrowserUtils.PopUpLink(this.href, this.target, null, null, '.(int)$set.'); return false;">'.\Smart::escape_html($btn).'</a>';
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	private static function bttn_open_popup($forced, $winname='') {
		//--
		if((string)$winname == '') {
			$wname = 'test_bw_win_interractions_pop_'.\Smart::uuid_10_seq().'_'.\Smart::uuid_10_num().'_'.\Smart::uuid_10_str();
		} else {
			$wname = (string) $winname;
		} //end if else
		if($forced) {
			$set = '1';
			$btn = 'Open PopUp (strict)';
		} else {
			$set = '0';
			$btn = 'Open PopUp or Modal (auto)';
		} //end if else
		//--
		return (string) '<a class="ux-button ux-button-highlight" style="min-width:320px;" target="'.\Smart::escape_html($wname).'" href="'.SMART_FRAMEWORK_TESTUNIT_BASE_URL.'testunit.interractions'.'" onClick="SmartJS_BrowserUtils.PopUpLink(this.href, this.target, null, null, '.(int)$set.'); return false;">'.\Smart::escape_html($btn).'</a>';
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	private static function bttn_close_modal_or_popup() {
		//--
		return (string) '<button class="ux-button ux-button-special" style="min-width:320px;" onClick="SmartJS_BrowserUtils.CloseModalPopUp(); return false;">[ Close: Modal / PopUp ]</button>';
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	private static function bttn_set_parent_refresh() {
		//--
		return (string) '<button class="ux-button ux-button-dark" style="min-width:320px;" onClick="SmartJS_BrowserUtils.RefreshParent(); return false;">[ Set: Parent Refresh / Reload ]</button>';
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	private static function bttn_set_confirm_unload($question='') {
		//--
		if((string)$question == '') {
			$question = 'This is a test for Confirm Unload. Are you sure you want to close this page ?';
		} //end if
		//--
		return '<button class="ux-button ux-button-dark" style="min-width:320px;" onClick="SmartJS_BrowserUtils.PageAwayControl(\''.\Smart::escape_js($question).'\'); return false;">[ Set: Confirm Unload ]</button>';
		//--
	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>