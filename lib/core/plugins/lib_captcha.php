<?php
// [LIB - SmartFramework / Plugins / Captcha]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.8 r.2017.03.27 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Captcha
// DEPENDS:
//	* Smart::
//	* SmartUtils::
//	* SmartFileSysUtils::
//	* SmartTextTranslations::
// DEPENDS-EXT: PHP GD w. *optional TTF support
// REQUIRED CSS:
//	* captcha.css
// REQUIRED TEMPLATES:
//	* captcha-form.inc.htm
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// [REGEX-SAFE-OK]

/**
 * Class: SmartCaptchaFormCheck - Render and Check the Captcha Image/Form.
 *
 * <code>
 * //==
 * //-- captcha image (generate)
 * header("Content-type: image/png"); // by default outputs PNG ; this can be changed to GIF or JPG
 * echo SmartCaptchaFormCheck::captcha_image('form_name', 'session', 'hashed', '0123456789ABCDEF', '200'); // this controller should output a raw image
 * //-- captcha form (draw)
 * echo SmartCaptchaFormCheck::captcha_form('index.php?page=mymodule.mycaptcha-image', 'form_name', '300'); // this controller should output HTML code to render the form
 * //-- captcha check (verify)
 * echo $check = SmartCaptchaFormCheck::verify('form_name', 'session', true); // and this is the way you verify the captcha (1 = ok ; 0 = not ok)
 * //-- some more info on verify()
 * // captcha will reset (clear) by default upon each SmartCaptchaFormCheck::verify()
 * // to avoid this (default) behaviour, you can set the 3rd parameters of verify() to FALSE
 * // but if you do so, don't forget to manually clear by calling SmartCaptchaFormCheck::clear() at the end !!!
 * //--
 * //==
 * </code>
 *
 * @usage 		static object: Class::method() - This class provides only STATIC methods
 * @hints 		To render captcha, SmartCaptchaFormCheck::captcha_form() and SmartCaptchaFormCheck::captcha_image() are required. To verify, use SmartCaptchaFormCheck::verify() ; SmartCaptchaFormCheck::clear() is optional, depending how SmartCaptchaFormCheck::verify() is called
 *
 * @access 		PUBLIC
 * @depends 	extensions: PHP XML ; classes: Smart
 * @version 	v.170316
 * @package 	Components:Captcha
 *
 */

final class SmartCaptchaFormCheck {

	// ::


//================================================================
public static function captcha_image($y_form_name, $y_store, $y_mode='hashed', $y_pool='0123456789', $y_noise='200', $y_chars='5', $y_width='170', $y_height='50', $y_format='png', $y_font_file='lib/core/plugins/fonts/liberation-mono-italic.ttf', $y_font_size='24', $y_char_space='20', $y_char_xvary='20', $y_char_yvary='10', $y_char_colors=array(0x111111, 0x333333, 0x778899, 0x666699, 0x003366, 0x669966, 0x006600, 0xFF3300), $y_noise_colors=array(0x888888, 0x999999, 0xAAAAAA, 0xBBBBBB, 0xCCCCCC, 0xDDDDDD, 0xEEEEEE, 0x8080C0)) {
	//--
	$captcha = new SmartCaptchaImageDraw();
	//--
	$captcha->store = (string) $y_store;
	$captcha->format = (string) $y_format;
	$captcha->mode = (string) $y_mode;
	$captcha->pool = (string) $y_pool;
	$captcha->width = Smart::format_number_int($y_width,'+');
	$captcha->height = Smart::format_number_int($y_height,'+');
	$captcha->chars = Smart::format_number_int($y_chars,'+');
	$captcha->charfont = (string) $y_font_file;
	$captcha->charttfsize = Smart::format_number_int($y_font_size, '+');
	$captcha->charspace = Smart::format_number_int($y_char_space,'+');
	$captcha->charxvar = Smart::format_number_int($y_char_xvary,'+');
	$captcha->charyvar = Smart::format_number_int($y_char_yvary,'+');
	$captcha->noise = Smart::format_number_int($y_noise,'+');
	if(Smart::array_size($y_char_colors) > 0) {
		$captcha->colors_chars = (array)$y_char_colors;
	} //end if
	if(Smart::array_size($y_noise_colors) > 0) {
		$captcha->colors_noise = (array)$y_noise_colors;
	} //end if
	//--
	return (string) $captcha->draw_image((string)$y_form_name);
	//--
} //END FUNCTION
//================================================================


//================================================================
// Draw the Captcha Form (needs the captcha image link and the form name)
public static function captcha_form($y_captcha_image_url, $y_form_name) {
	//--
	$y_form_name = (string) trim((string)$y_form_name);
	$js_cookie_name = self::jscookiename($y_form_name);
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
			'CAPTCHA-IMG-SRC' 			=> (string) Smart::escape_html($captcha_url.Smart::escape_url((string)time().Smart::random_number(10,99))),
			'CAPTCHA-JS-IMG-CLICK' 		=> (string) 'var captcha_date = new Date(); this.src=\''.Smart::escape_js($captcha_url).'\'+captcha_date.getTime()+\'\'+captcha_date.getMilliseconds();',
			'CAPTCHA-JS-FIELD-BLUR' 	=> (string) 'try { eval( \'\' + SmartJS_Base64.decode(\''.base64_encode("try { var SmartCaptchaChecksum = SmartJS_BrowserUtils.getCookie('".Smart::escape_js(self::chkcookiename($y_form_name))."'); if(SmartCaptchaChecksum == '') { SmartCaptchaChecksum = 'invalid-captcha'; alert('".Smart::escape_js($translator_core_captcha->text('error'))."'); } var smartCaptchaTimerCookie = new Date(); var smartCaptchaCookie = SmartJS_Archiver_LZS.compressToBase64(SmartJS_CryptoBlowfish.encrypt(SmartJS_Base64.encode(smartCaptchaTimerCookie.getTime() + '!' + this.value.toUpperCase() + '!SmartFramework'), SmartJS_CoreUtils.stringTrim(SmartCaptchaChecksum))); SmartJS_BrowserUtils.setCookie('".Smart::escape_js($js_cookie_name)."', smartCaptchaCookie, false, '/'); } catch(err) { alert('Captcha ERROR: (2) ' + err); } this.value = '';").'\')); } catch(error){ alert(\'Captcha ERROR: (2) :: Invalid Captcha Script \'); }'
		],
		'yes' // export to cache
	);
	//--
} //END FUNCTION
//================================================================


//================================================================
// Verify Captcha and *OPTIONAL* Clear It
public static function verify($y_form_name, $y_mode, $y_clear=true) {
	//--
	$y_form_name = trim((string)$y_form_name);
	//--
	$ok = 1; // default, if not active
	//--
	if(self::validate_form_name($y_form_name) !== 1) {
		return 0; // invalid form name
	} //end if
	//--
	$cookie_name = self::cookiename($y_form_name);
	//--
	if((string)$y_mode == 'session') {
		//--
		$cookie_value = (string) SmartSession::get((string)$cookie_name);
		$run_mode = 'session';
		//--
	} else {
		//--
		$cookie_value = (string) $_COOKIE[(string)$cookie_name];
		$run_mode = 'cookie';
		//--
	} //end if else
	//--
	$var_name = self::jscookiename($y_form_name);
	$var_value = trim((string)$_COOKIE[(string)$var_name]);
	//--
	if((string)$var_value != '') {
		$arr_value = explode('!', base64_decode(SmartUtils::crypto_blowfish_decrypt(SmartArchiverLZS::decompressFromBase64((string)$var_value), sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY)))); // explode by '!'
	} //end if
	//--
	$ok = 0; // error check by default
	//--
	if((@strlen($var_value) > 0) AND ((string)$cookie_value == (string)self::checksum(trim($arr_value[1])))) {
		//--
		$ok = 1;
		//--
		if($y_clear == true) { // clear is optional (there are situations when after veryfying captcha, even if OK, other code must be run and if that code returns error, still captcha must be active, not cleared (so clearing it manually is a solution ...)
			self::clear($y_form_name, $y_mode);
		} //end if
		//--
	} //end if
	//--
	return $ok;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Manually Clear Captcha, if was not set to be done automatically by verify()
public static function clear($y_form_name, $y_mode) {
	//--
	$y_form_name = trim((string)$y_form_name);
	//--
	if(self::validate_form_name($y_form_name) !== 1) {
		return 0; // invalid form name
	} //end if
	//--
	$cookie_name = self::cookiename($y_form_name);
	//--
	if((string)$y_mode == 'session') {
		//--
		SmartSession::set((string)$cookie_name, ''); // unset from session
		//--
	} else {
		//--
		@setcookie((string)$cookie_name, '', 1, '/'); // unset cookie
		//--
	} //end if else
	//--
	return 1; // OK
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Captcha Form Validate Form Name
 *
 * @access 		private
 * @internal
 *
 */
public static function validate_form_name($y_form_name) {
	//--
	$y_form_name = (string) trim((string)$y_form_name);
	//--
	$out = 1;
	//--
	if((string)$y_form_name == '') {
		$out = 0; // empty form name
	} //end if
	//--
	if(!preg_match('/^[A-Za-z0-9_\-]+$/', (string)$y_form_name)) {
		$out = 0; // invalid characters in form name
	} //end if
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Captcha Form Checksum
 *
 * @access 		private
 * @internal
 *
 */
public static function checksum($y_num) {
	//--
	return sha1('Captcha#Code'.$y_num.'@'.$y_num.'# ^^777^ %% #@#.'.$y_num.' *** #ENDCaptcha'.'->SECURITY-KEY:'.SMART_FRAMEWORK_SECURITY_KEY);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Captcha Form JS Cookie Name
 *
 * @access 		private
 * @internal
 *
 */
public static function jscookiename($y_form_name) {
	//--
	$y_form_name = trim((string)$y_form_name);
	//--
	return 'SmartCaptcha_DATA_'.sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Captcha Form CHK Cookie Name
 *
 * @access 		private
 * @internal
 *
 */
public static function chkcookiename($y_form_name) {
	//--
	$y_form_name = trim((string)$y_form_name);
	//--
	return 'SmartCaptcha_CHK_'.sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Function: Captcha Form Cookie Name
 *
 * @access 		private
 * @internal
 *
 */
public static function cookiename($y_form_name) {
	//--
	$y_form_name = trim((string)$y_form_name);
	//--
	return 'SmartCaptcha_CODE_'.sha1($y_form_name.SMART_FRAMEWORK_SECURITY_KEY);
	//--
} //END FUNCTION
//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartCaptchaImageDraw
 *
 * Create a Form Captcha Validation (for internal use only)
 *
 * @access 		private
 * @internal
 *
 */
final class SmartCaptchaImageDraw {

	// ->
	// v.170316


//================================================================
//--
public $store = 'cookie';		// captcha mode: 'cookie' | 'session'
public $format = 'jpg';			// default format: png | gif | jpg
public $mode = 'dotted'; 		// captcha noise style: dotted | hashed
public $noise = 88; 			// captcha noise level 10..1000
//--
public $width = 80;				// image default width:  80..320
public $height = 40;			// image default height: 40..160
public $quality = 90; 			// image quality 1..100 (just for jpeg)
//--
public $pool = '01234567890'; 	// captcha charset: 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
public $chars = 5;				// captcha number of characters: 3..5
//--
public $charspace = 8; 			// char space
public $charxvar = 7; 			// the X start limit > 2
public $charyvar = 8; 			// char Y start limit > 2
//--
public $colors_chars = [0x111111, 0x333333, 0x778899, 0x666699, 0x003366, 0x669966, 0x006600, 0xFF3300]; // color palette characters
public $colors_noise = [0x888888, 0x999999, 0xAAAAAA, 0xBBBBBB, 0xCCCCCC, 0xDDDDDD, 0xEEEEEE, 0x8080C0]; // color palette noise
//--
public $charfont = 5; 			// character's font (1..5 for built-in gd font ; path/to/font.gdf ; path/to/font.ttf)
public $charttfsize = 24; 		// ttf font size (just for ttf fonts)
//--
//================================================================


//================================================================
public function __construct() {
	//--
	if(!function_exists('gd_info')) {
		echo('"[ERROR] :: Captcha Verification LIB :: PHP-GD extension with JPEG Lib is  required to run Captcha Library');
	} //end if
	//--
} //END FUNCTION
//================================================================


//================================================================
public function draw_image($y_form) {

	//--
	$y_form = trim((string)$y_form);
	//--

	//--
	$this->noise = (int) $this->noise;
	if($this->noise < 10) {
		$this->noise = 10;
	} elseif($this->noise > 1000) {
		$this->noise = 1000;
	} //end if
	//--
	$this->width = (int) $this->width;
	if($this->width < 80) {
		$this->width = 80;
	} elseif($this->width > 320) {
		$this->width = 320;
	} //end if
	//--
	$this->height = (int) $this->height;
	if($this->height < 40) {
		$this->height = 40;
	} elseif($this->height > 160) {
		$this->height = 160;
	} //end if
	//--
	$this->quality = (int) $this->quality;
	if($this->quality < 50) {
		$this->quality = 50;
	} elseif($this->quality > 100) {
		$this->quality = 100;
	} //end if else
	//--
	$this->pool = (string) trim((string)$this->pool);
	if((string)$this->pool == '') {
		$this->pool = '01234567890';
	} //end if
	//--
	$this->chars = (int) $this->chars;
	if($this->chars < 3) {
		$this->chars = 3;
	} elseif($this->chars > 10) {
		$this->chars = 10;
	} //end if else
	//--
	$this->charspace = (int) $this->charspace;
	if($this->charspace < 1) {
		$this->charspace = 1;
	} elseif($this->charspace > 100) {
		$this->charspace = 100;
	} //end if
	//--
	$this->charxvar = (int) $this->charxvar;
	if($this->charxvar < 0) {
		$this->charxvar = 0;
	} elseif($this->charxvar > 100) {
		$this->charxvar = 100;
	} //end if else
	//--
	$this->charyvar = (int) $this->charyvar;
	if($this->charyvar < 0) {
		$this->charyvar = 0;
	} elseif($this->charyvar > 100) {
		$this->charyvar = 100;
	} //end if else
	//--
	$this->colors_chars = (array) $this->colors_chars;
	//--
	$this->colors_noise = (array) $this->colors_noise;
	//--

	//--
	$out = '';
	//--
	ob_start();
	//--
	if((string)$this->mode == 'hashed') {
		$captcha_arr = (array) $this->generate_captcha_hashed();
	} else { // 'dotted'
		$captcha_arr = (array) $this->generate_captcha_dotted();
	} //end if else
	$captcha_image = $captcha_arr['rawimage'];
	$captcha_word = $captcha_arr['word'];
	unset($captcha_arr);
	//--
	$err = ob_get_contents();
	ob_end_clean();
	//--
	if((string)$err != '') { // trigger errors
		Smart::log_warning('#Captcha / Draw Image ['.$intext.'] Errors/Output: '.$err);
	} //end if
	//--
	if(!is_resource($captcha_image)) {
		Smart::log_warning('#Captcha / Draw Image :: Invalid Resource');
		return '';
	} //end if
	//--
	ob_start();
	//-
	switch(@strtolower($this->format)) {
		case 'png':
			//header: "Content-type: image/png"
			@imagepng($captcha_image);
			break;
		case 'gif':
			//header: "Content-type: image/gif"
			@imagegif($captcha_image);
			break;
		case 'jpg':
		case 'jpeg':
		default:
			//header: "Content-type: image/jpeg"
			@imagejpeg($captcha_image, '', $this->quality);
	} //end switch
	//-
	$out = ob_get_contents();
	//-
	ob_end_clean();
	//-
	@imagedestroy($captcha_image); // free resources
	//--

	//--
	@setcookie(SmartCaptchaFormCheck::chkcookiename($y_form), sha1($y_form.SMART_FRAMEWORK_SECURITY_KEY), 0, '/');
	//--
	if(SmartCaptchaFormCheck::validate_form_name($y_form) === 1) {
		if((string)$this->store == 'session') {
			SmartSession::set(SmartCaptchaFormCheck::cookiename($y_form), SmartCaptchaFormCheck::checksum($captcha_word));
		} else {
			@setcookie(SmartCaptchaFormCheck::cookiename($y_form), SmartCaptchaFormCheck::checksum($captcha_word), 0, '/');
		} //end if else
	} //end if
	//--

	//--
	return (string) $out;
	//--

} //END FUNCTION
//================================================================


//================================================================
private function generate_color() {
	//-- init
	$min = 0;
	$max = 0;
	$arr = $this->colors_chars;
	//--
	$monochrome = true;
	if(is_array($arr)) {
		$max = count($arr) - 1;
		if($max >= 0) {
			$monochrome = false;
		} //end if
	} //end if
	//--
	if($monochrome) {
		$out = 0x999999;
	} else {
		$out = $arr[Smart::random_number($min,$max)];
	} //end if else
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
private function generate_noise_color() {
	//-- init
	$min = 0;
	$max = 0;
	$arr = $this->colors_noise;
	//--
	$monochrome = true;
	if(is_array($arr)) {
		$max = count($arr) - 1;
		if($max >= 0) {
			$monochrome = false;
		} //end if
	} //end if
	//--
	if($monochrome) {
		$out = 0xCCCCCC;
	} else {
		$out = $arr[Smart::random_number($min,$max)];
	} //end if else
	//--
	return $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
private function generate_word() {
	//--
	$pool = (string) $this->pool;
	$len = (int) strlen($pool) - 1;
	if($len <= 0) {
		$len = 1;
	} //end if
	//--
	$str = '';
	//--
	for($i = 0; $i<$this->chars; $i++) {
		$str .= substr($pool, Smart::random_number(0, (int)$len), 1);
	} //end for
	//--
	return (string) $str;
	//--
} //END FUNCTION
//================================================================


//================================================================
private function img_draw_text($im, $word) {
	//--
	$use_ttf_font = false;
	if(is_int($this->charfont) AND ($this->charfont > 0)) {
		$font = (int) $this->charfont;
	} elseif(((string)$this->charfont != '') AND (SmartFileSysUtils::check_file_or_dir_name($this->charfont)) AND (is_file($this->charfont))) {
		if(function_exists('imagettftext') AND (substr($this->charfont, -4, 4) == '.ttf')) {
			$font = (string) $this->charfont;
			$use_ttf_font = true;
		} else { // gdf font
			$font = @imageloadfont($this->charfont);
			if($font === false) {
				$font = 5; // on error
			} //end if
		} //end if else
	} else {
		$font = 5 ; // on error
	} //end if else
	//--
	$first_x = Smart::random_number(5, $this->charxvar);
	//--
	for($i=0; $i < strlen($word); $i++) {
		//--
		$w = substr($word, $i, 1);
		$c = $this->generate_color();
		//--
		$sign = 1;
		if(Smart::random_number(0, 1) != 0) {
			$sign = -1;
		} //end if
		//--
		if($use_ttf_font != true) { // GDF font
			$y = ($this->height / 2) + ($sign * Smart::random_number(0, $this->charyvar));
			@imagestring($im, (int)$font, (int)$first_x, (int)$y, (string)$w, $c);
		} else { // TTF font
			$y = ($this->height / 2) + ($this->charttfsize / 2) + ($sign * Smart::random_number(0, $this->charyvar));
			$angle = Smart::random_number(0, 20);
			@imagettftext($im, $this->charttfsize, (int)$angle, (int)$first_x, (int)$y, $c, (string)$font, (string)$w);
		} //end if else
		//--
		$first_x += (int) $this->charspace + Smart::random_number(1, 15);
		//--
	} //end for
	//--
} // END FUNCTION
//================================================================


//================================================================
private function generate_captcha_dotted() {

	//-- inits
	$word = (string) $this->generate_word();
	//--

	//-- create image
	if(function_exists('imagecreatetruecolor')) {
		$im = @imagecreatetruecolor($this->width, $this->height);
	} else {
		$im = @imagecreate($this->width, $this->height);
	} //end if else
	//-
	@imagefill($im, 0, 0, 0xDDDDDD);
	//--

	//-- add horiz lines
	$margin = 1;
	$first_x = $margin;
	$factor = 7;
	$max_lines = ceil($this->width / $factor);
	for($i=0; $i<$max_lines; $i++) {
		if($first_x > ($this->width - $margin)) {
			break;
		} //end if
		@imageline ($im, $first_x, 2, $first_x, ($this->height-2), 0xFFFFFF);
		$first_x += ceil($factor);
	} //end for
	//--

	//-- add vert lines
	$margin = 1;
	$first_y = $margin;
	$factor = 7;
	$max_lines = ceil($this->height / $factor);
	for($i=0; $i<$max_lines; $i++) {
		if($first_y > ($this->height - $margin)) {
			break;
		} //end if
		@imageline ($im, 2, $first_y, ($this->width-2), $first_y, 0xFFFFFF);
		$first_y += ceil($factor);
	} //end for
	//--

	//-- add text
	$this->img_draw_text($im, $word);
	//--

	//-- add noise
	for($i=0; $i<$this->noise; $i++){
		$noise_color = $this->generate_noise_color();
		@imagesetpixel($im, Smart::random_number(2,$this->width-2), Smart::random_number(2,$this->height-2), $noise_color);
		@imagesetpixel($im, Smart::random_number(2,$this->width-2), Smart::random_number(2,$this->height-2), $noise_color);
		@imagesetpixel($im, Smart::random_number(2,$this->width-2), Smart::random_number(2,$this->height-2), $noise_color);
		@imagesetpixel($im, Smart::random_number(2,$this->width-2), Smart::random_number(2,$this->height-2), $noise_color);
	} //end for
	//--

	//--
	return array('word' => (string)$word, 'rawimage' => $im);
	//--

} //END FUNCTION
//================================================================


//================================================================
private function generate_captcha_hashed() {

	// v.170316
	// portions of this code is based on CodeIgniter

	//--
	$word = (string) $this->generate_word();
	//--

	//--
	$length	= strlen($word);
	$angle	= ($length >= 6) ? Smart::random_number(-($length-6), ($length-6)) : 0;
	$x_axis	= Smart::random_number(6, (360 / $length)-16);
	$y_axis = ($angle >= 0 ) ? Smart::random_number($this->height, $this->width) : Smart::random_number(6, $this->height);
	//--

	//--
	if(function_exists('imagecreatetruecolor')) {
		$im = @imagecreatetruecolor($this->width, $this->height);
	} else {
		$im = @imagecreate($this->width, $this->height);
	} //end if else
	//--

	//--
	@imagefilledrectangle($im, 0, 0, $this->width, $this->height, 0xFFFFFF);
	//--

	//--
	$theta = 1;
	$thetac = 7;
	$radius = 16;
	//--
	$circles = (int) ($this->noise / 1.5);
	if($circles < 1) {
		$circles = 1;
	} //end if
	//--
	$points	= (int) ($this->noise - $circles);
	if($points < 1) {
		$points = 1;
	} //end if
	//--
	for($i=0; $i<($circles*$points)-1; $i++) {
		//--
		$theta = $theta + $thetac;
		$rad = $radius * ($i / $points);
		$x = ($rad * cos($theta)) + $x_axis;
		$y = ($rad * sin($theta)) + $y_axis;
		$theta = $theta + $thetac;
		$rad1 = $radius * (($i + 1) / $points);
		$x1 = ($rad1 * cos($theta)) + $x_axis;
		$y1 = ($rad1 * sin($theta )) + $y_axis;
		//--
		@imageline($im, $x, $y, $x1, $y1, $this->generate_noise_color());
		//--
		$theta = $theta - $thetac;
		//--
	} //end for
	//--

	//--
	$this->img_draw_text($im, $word);
	//--

	//--
	return array('word' => (string)$word, 'rawimage' => $im);
	//--

} //END FUNCTION
//================================================================


// Sample
/*
$captcha = new SmartCaptchaImageDraw();
$captcha->store = 'cookie';
$captcha->format='png';
$captcha->width = 100;
$captcha->height = 50;
$captcha->noise = 300;
$captcha->chars = 5;
$captcha->charfont = 'lib/core/plugins/fonts/adventure.gdf';
$captcha->charspace = 18;
$captcha->charxvar = 11;
$captcha->charyvar = 22;
echo $captcha->draw_image('form_name'); // raw output the captcha image
*/

} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>