<?php
// [LIB - Smart.Framework / Plugins / Captcha Form]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Captcha Form
// DEPENDS:
//	* Smart::
//	* SmartUtils::
//	* SmartTextTranslations::
// REQUIRED CSS:
//	* captcha.css
// REQUIRED JS:
//	* jquery.js
//	* smart-framework.pak.js
// REQUIRED TEMPLATES:
//	* captcha-form.inc.htm
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// [REGEX-SAFE-OK]

/**
 * Class: SmartCaptcha - Manages, Render and Check the Captcha Form.
 *
 * <code>
 * //==
 * //-- captch form needs a captcha plugin to work with (ex: image)
 * // See: \SmartModExtLib\Samples\TestUnitMain::captchaImg()
 * // Also See: modules/mod-samples/testunit.php # case 'testunit.captcha'
 * // The method SmartCaptcha::initCaptchaPlugin() must be used to init a captcha plugin (ex: image)
 * //-- captcha form (draw)
 * echo SmartCaptcha::drawCaptchaForm('form_name', '?page=samples.testunit&op=testunit.captcha'); // this controller should output HTML code to render the form
 * //-- captcha check (verify)
 * echo $check = SmartCaptcha::verifyCaptcha('form_name', true, 'cookie'); // and this is the way you verify the captcha (1 = ok ; 0 = not ok)
 * //-- some more info on verify()
 * // captcha will reset (clear) by default upon each SmartCaptcha::verifyCaptcha()
 * // to avoid this (default) behaviour, you can set the 3rd parameters of verify() to FALSE
 * // but if you do so, don't forget to manually clear captcha by calling SmartCaptcha::clearCaptcha() at the end !!!
 * //--
 * //==
 * </code>
 *
 * @usage 		static object: Class::method() - This class provides only STATIC methods
 * @hints 		To render captcha, SmartCaptcha::drawCaptchaForm() and a captcha plugin is required. To verify, use SmartCaptcha::verifyCaptcha() ; SmartCaptcha::clearCaptcha() is optional to be call after verify, depending how SmartCaptcha::verifyCaptcha() is called
 *
 * @access 		PUBLIC
 * @depends 	classes: Smart, SmartUtils, SmartTextTranslations ; javascript: jquery.js, smart-framework.pak.js ; css: captcha.css
 * @version 	v.20191217
 * @package 	development:Captcha
 *
 */

final class SmartCaptcha {

	// ::


	//================================================================
	/**
	 * Inits a captha plugin by setting the required values in cookie or session depend how mode is set
	 * This should be used for internal development only of new captcha Plugins (ex: image)
	 *
	 * @param $y_form_name 		STRING the name of the HTML form to bind to ; This must be unique on a page with multiple forms
	 * @param $y_mode 			ENUM the storage mode ; Can be set to 'cookie' or 'session' ; default is 'cookie'
	 * @param $y_captcha_word 	The Captcha Word to be initialized (this must be supplied by the Captcha Plugin)
	 * @return BOOLEAN 			TRUE on success or FALSE on failure
	 */
	public static function initCaptchaPlugin($y_form_name, $y_captcha_word, $y_mode='cookie') {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		if(self::validate_form_name($y_form_name) !== true) {
			return false;
		} //end if
		//--
		if((string)trim((string)$y_captcha_word) == '') {
			return false;
		} //end if
		//--
		$ok = (bool) SmartUtils::set_cookie(self::cookie_name_chk($y_form_name), (string)sha1((string)$y_form_name.SMART_FRAMEWORK_SECURITY_KEY), 0);
		if(!$ok) {
			return false;
		} //end if
		//--
		if((string)$y_mode == 'session') {
			$ok = (bool) SmartSession::set(self::cookie_name_frm($y_form_name), self::cksum_hash($y_captcha_word));
		} else {
			$ok = (bool) SmartUtils::set_cookie(self::cookie_name_frm($y_form_name), self::cksum_hash($y_captcha_word), 0);
		} //end if else
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Draw the Captcha Form partial HTML
	 * Requires a captcha plugin (ex: image)
	 *
	 * @param $y_captcha_image_url 	The URL to a Captcha Plugin ; Example: 'index.php?page=mymodule.mycaptcha-image'
	 * @param $y_form_name 			STRING the name of the HTML form to bind to ; This must be unique on a page with multiple forms
	 * @return STRING 				The partial captcha HTML to include in a form
	 */
	public static function drawCaptchaForm($y_form_name, $y_captcha_image_url) {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		if(self::validate_form_name($y_form_name) !== true) {
			return 'ERROR: Invalid Captcha Form Name';
		} //end if
		//--
		$js_cookie_name = self::cookie_name_jsc($y_form_name);
		//--
		$captcha_url = (string) $y_captcha_image_url;
		$captcha_url = (string) Smart::url_add_suffix($captcha_url, 'captcha_form='.rawurlencode($y_form_name));
		$captcha_url = (string) Smart::url_add_suffix($captcha_url, 'captcha_mode=image');
		$captcha_url = (string) Smart::url_add_suffix($captcha_url, 'new=');
		//--
		$translator_core_captcha = SmartTextTranslations::getTranslator('@core', 'captcha');
		//--
		return (string) SmartMarkersTemplating::render_file_template(
			'lib/core/plugins/templates/captcha-form.inc.htm',
			[
				'CAPTCHA-TXT-CONFIRM' 		=> (string) $translator_core_captcha->text('confirm'),
				'CAPTCHA-IMG-TITLE' 		=> (string) $translator_core_captcha->text('click'),
				'CAPTCHA-TXT-VERIFY' 		=> (string) $translator_core_captcha->text('verify'),
				'CAPTCHA-IMG-SRC' 			=> (string) $captcha_url.Smart::escape_url((string)time().Smart::random_number(10,99)),
				'CAPTCHA-JS-FIELD-BLUR' 	=> (string) base64_encode("try { var SmartCaptchaChecksum = SmartJS_BrowserUtils.getCookie('".Smart::escape_js(self::cookie_name_chk($y_form_name))."'); if(SmartCaptchaChecksum == '') { SmartCaptchaChecksum = 'invalid-captcha'; alert('".Smart::escape_js($translator_core_captcha->text('error'))."'); } var smartCaptchaTimerCookie = new Date(); var smartCaptchaCookie = SmartJS_Archiver_LZS.compressToBase64(SmartJS_CryptoBlowfish.encrypt(SmartJS_Base64.encode(smartCaptchaTimerCookie.getTime() + '!' + fld.value.toUpperCase() + '!Smart.Framework'), SmartJS_CoreUtils.stringTrim(SmartCaptchaChecksum))); SmartJS_BrowserUtils.setCookie('".Smart::escape_js($js_cookie_name)."', smartCaptchaCookie, false, '/', '@'); } catch(err) { console.error('Captcha ERROR: ' + err); } fld.value = '*******';")
			],
			'yes' // export to cache
		);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Verify Captcha and *OPTIONAL* Clear It
	 *
	 * @param $y_form_name 		STRING the name of the HTML form to bind to ; This must be unique on a page with multiple forms
	 * @param $y_mode 			ENUM the storage mode ; Can be set to 'cookie' or 'session' ; default is 'cookie'
	 * @param $y_clear 			BOOLEAN if clear Captcha on verify success ; Default is TRUE ; If TRUE if the captcha verification pass will clear all value from the storage (cookie or session)
	 * @return BOOLEAN 			TRUE on success or FALSE on failure
	 */
	public static function verifyCaptcha($y_form_name, $y_clear=true, $y_mode='cookie') {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		//--
		if(self::validate_form_name($y_form_name) !== true) {
			return false; // invalid form name
		} //end if
		//--
		$cookie_name = self::cookie_name_frm($y_form_name);
		//--
		if((string)$y_mode == 'session') {
			//--
			$cookie_value = (string) SmartSession::get((string)$cookie_name);
			$run_mode = 'session';
			//--
		} else {
			//--
			$cookie_value = (string) SmartUtils::get_cookie((string)$cookie_name);
			$run_mode = 'cookie';
			//--
		} //end if else
		//--
		$var_name = self::cookie_name_jsc($y_form_name);
		$var_value = (string) trim((string)SmartUtils::get_cookie((string)$var_name));
		//--
		$arr_value = array();
		if((string)$var_value != '') {
			$arr_value = (array) explode('!', (string)base64_decode(SmartUtils::crypto_blowfish_decrypt(SmartArchiverLZS::decompressFromBase64((string)$var_value), sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY)))); // explode by '!'
		} //end if
		//--
		$ok = false; // error check by default
		//--
		if((@strlen($var_value) > 0) AND ((string)$cookie_value == (string)self::cksum_hash(trim((string)$arr_value[1])))) {
			//--
			$ok = true;
			//--
			if($y_clear === true) { // clear is optional (there are situations when after veryfying captcha, even if OK, other code must be run and if that code returns error, still captcha must be active, not cleared (so clearing it manually is a solution ...)
				self::clearCaptcha($y_form_name, $y_mode);
			} //end if
			//--
		} //end if
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Programatically clear the Captcha (from cookie or session)
	 * On Verify Success the Captcha clears automatically all stored values
	 *
	 * @param $y_form_name 		STRING the name of the HTML form to bind to ; This must be unique on a page with multiple forms
	 * @param $y_mode 			ENUM the storage mode ; Can be set to 'cookie' or 'session' ; default is 'cookie'
	 * @return BOOLEAN 			TRUE on success or FALSE on failure
	 */
	public static function clearCaptcha($y_form_name, $y_mode='cookie') {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		//--
		if(self::validate_form_name($y_form_name) !== true) {
			return false; // invalid form name
		} //end if
		//--
		$cookie_name = self::cookie_name_frm($y_form_name);
		//--
		if((string)$y_mode == 'session') {
			//--
			$ok = (bool) SmartSession::unsets((string)$cookie_name); // unset from session
			//--
		} else {
			//--
			$ok = (bool) SmartUtils::unset_cookie((string)$cookie_name); // unset cookie
			//--
		} //end if else
		//--
		return (bool) $ok; // OK
		//--
	} //END FUNCTION
	//================================================================


	//===== PRIVATES


	//================================================================
	private static function validate_form_name($y_form_name) {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		//--
		$out = true;
		//--
		if((string)$y_form_name == '') {
			$out = false; // empty form name
		} //end if
		//--
		if(!preg_match('/^[A-Za-z0-9_\-]+$/', (string)$y_form_name)) {
			$out = false; // invalid characters in form name
		} //end if
		//--
		return (bool) $out;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function cksum_hash($y_code) {
		//--
		return (string) sha1('Captcha#Code'.$y_code.SMART_FRAMEWORK_SECURITY_KEY);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function cookie_name_jsc($y_form_name) {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		//--
		return (string) 'SmartCaptcha_DATA_'.sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function cookie_name_chk($y_form_name) {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		//--
		return (string) 'SmartCaptcha_CHK_'.sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY);
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	private static function cookie_name_frm($y_form_name) {
		//--
		$y_form_name = (string) trim((string)$y_form_name);
		//--
		return (string) 'SmartCaptcha_CODE_'.sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY);
		//--
	} //END FUNCTION
	//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>