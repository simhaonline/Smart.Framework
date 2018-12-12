<?php
// [LIB - SmartFramework / Utils]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Utils
// DEPENDS:
//	* Smart::
//	* SmartUnicode::
//	* SmartValidator::
//	* SmartHashCrypto::
//	* SmartAuth::
//	* SmartFileSysUtils::
//	* SmartFileSystem::
//	* SmartHttpClient->
// REQUIRED CSS:
//	* responsive-fluid.css
//======================================================


//--
// gzdeflate / gzinflate (rfc1951) have no checksum for data integrity by default ; if sha1 checksums are integrated separately, it can be better than other zlib algorithms
//--
if((!function_exists('gzdeflate')) OR (!function_exists('gzinflate'))) {
	@http_response_code(500);
	die('ERROR: The PHP ZLIB Extension (gzdeflate/gzinflate) is required for SmartFramework / Lib Utils');
} //end if
//--

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartUtils - provides various utility functions for SmartFramework.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartValidator, SmartHashCrypto, SmartAuth, SmartFileSysUtils, SmartFileSystem, SmartHttpClient
 * @version 	v.181212
 * @package 	Base
 *
 */
final class SmartUtils {

	// ::

	private static $cache = array();


//================================================================
// use this function to get the cookie domain
public static function cookie_default_domain() {
	//--
	$cookie_domain = '';
	if((defined('SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN')) AND ((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN != '')) {
		if((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN == '*') {
			$cookie_domain = (string) self::get_server_current_basedomain_name();
		} else {
			$cookie_domain = (string) SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN;
		} //end if
	} //end if
	//--
	return (string) $cookie_domain;
	//--
} //END FUNCTION
//================================================================


//================================================================
// use this function to get cookies as it takes care of safe filtering of cookie values
public static function get_cookie($cookie_name) {
	//--
	return SmartFrameworkRegistry::getCookieVar((string)$cookie_name); // mixed: null / string
	//--
} //END FUNCTION
//================================================================


//================================================================
// use this function to set cookies as it takes care to set them according with the cookie domain if set or not per app ; use empty data to unset ; use zero expire time for cookies that will expire with browser session
public static function set_cookie($cookie_name, $cookie_data, $expire_time=0, $cookie_path='/', $cookie_domain='@') {
	//--
	if(headers_sent()) {
		return false;
	} //end if
	//--
	$expire_time = (int) $expire_time;
	if($expire_time < 0) {
		$expire_time = 0;
	} //end if
	//--
	if((string)$cookie_domain == '@') { // if empty or non @ leave as it is
		$cookie_domain = (string) self::cookie_default_domain();
	} //end if
	//--
	if((string)$cookie_domain != '') {
		$cookie_set = @setcookie((string)$cookie_name, (string)$cookie_data, (int)$expire_time, (string)$cookie_path, (string)$cookie_domain); // set it using domain (if running on IP will be set on current IP)
	} else {
		$cookie_set = @setcookie((string)$cookie_name, (string)$cookie_data, (int)$expire_time, (string)$cookie_path); // set it without specific domain (will using current IP or subdomain)
	} //end if else
	//--
	return (bool) $cookie_set;
	//--
} //END FUNCTION
//================================================================


//================================================================
// simple encode a URL parameter using bin2hex()
public static function url_hex_encode($y_val) {
	//--
	return (string) @bin2hex((string)$y_val);
	//--
} //END FUNCTION
//================================================================


//================================================================
// simple encode a URL parameter using hex2bin()
public static function url_hex_decode($y_enc_val) {
	//--
	return (string) SmartFrameworkSecurity::FilterUnsafeString((string)@hex2bin((string)$y_enc_val));
	//--
} //END FUNCTION
//================================================================


//================================================================ Display a localized number
// WARNING: Do not use when you do calculations, because will break calculations
// Use it just for display (a local formated number)
// The number to format must come in US format as 1234.56 or -1234.56 or 1,234.56 or 1 234.56 or -1,234.56 or -1 234.56
// [-1] will place auto decimals
// [-2] will place auto decimals but force as min as .0
public static function local_display_number($y_number, $y_decimals=-1, $y_usethousandsep=true) {
	//--
	$y_number = (string) trim((string)$y_number);
	//--
	if(!preg_match('/^[0-9\-\.\, ]+$/', (string)$y_number)) {
		Smart::log_warning('Invalid Number to convert to local (invalid characters): '.$y_number);
		return (string) '!'.$y_number.'!';
	} //end if
	//--
	$y_decimals = Smart::format_number_int($y_decimals);
	if($y_decimals > 4) {
		$y_decimals = 4;
	} elseif($y_decimals < -2) {
		$y_decimals = -2;
	} //end if else
	//--
	$separator_dec = (string) Smart::get_from_config('regional.decimal-separator');
	$separator_thd = (string) Smart::get_from_config('regional.thousands-separator');
	//--
	$sign = '';
	if((string)substr((string)$y_number, 0, 1) == '-') {
		$sign = '-';
	} //end if
	//--
	$localnum = '0';
	//--
	$tmp_arr = explode('.', (string)$y_number);
	//--
	$int_part = (string) trim(str_replace(['.', ',', ' ', '-', $separator_dec, $separator_thd], ['', '', '', '', '', ''], (string)$tmp_arr[0]));
	$dec_part = (string) trim(str_replace(['.', ',', ' ', '-', $separator_dec, $separator_thd], ['', '', '', '', '', ''], (string)$tmp_arr[1]));
	//--
	$intx_part = strrev(chunk_split(strrev($int_part), 3, $separator_thd));
	$intx_part = trim(substr($intx_part, 1));
	//--
	if($y_usethousandsep === false) {
		$intx_part = (string) $int_part;
	} //end if
	//--
	if(count($tmp_arr) > 2) { // invalid
		//--
		Smart::log_warning('Invalid Number to convert to local (have too many decimal parts): '.$y_number);
		return (string) '!'.$y_number.'!';
		//--
	} else {
		//--
		switch((string)$y_decimals) {
			case '0': // no decimals
				$localnum = (string) $intx_part;
				break;
			case '1': // fixed number of decimal: 1
			case '2': // fixed number of decimal: 2
			case '3': // fixed number of decimal: 3
			case '4': // fixed number of decimal: 4
				$localnum = (string) $intx_part.$separator_dec.str_pad(substr((string)(int)$dec_part, 0, (int)$y_decimals), (int)$y_decimals, '0');
				break;
			case '-2': // auto decimals but force at least one
				$autodec = (int) 0 + $dec_part;
				$localnum = (string) $intx_part.$separator_dec.$autodec;
				break;
			case '-1': // auto decimals (zero or more)
			default:
				$autodec = (int) 0 + $dec_part;
				if((int)$autodec > 0) {
					$localnum = (string) $intx_part.$separator_dec.$autodec;
				} else {
					$localnum = (string) $intx_part;
				} //end if else
		} //end switch
		//--
	} //end if else
	//--
	return (string) $sign.$localnum;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Reverse (Inverse) the SIGN for a localized number (like localnum * -1)
// The Number will be treated as STRING, as it may be huge to avoid break it
public static function local_number_sign_reverse($y_number) {
	//--
	$separator_dec = (string) Smart::get_from_config('regional.decimal-separator');
	$separator_thd = (string) Smart::get_from_config('regional.thousands-separator');
	//-- test if zero
	$tmp_number = str_replace(['.', ',', ' ', $separator_dec, $separator_thd], ['', '', '', '', ''], (string)$y_number); // remove garbage characters
	if((0+$tmp_number) == 0) {
		return (string) $y_number; // it is zero, so no sign should be used
	} //end if
	//-- inverse the sign
	$y_number = (string) trim((string)$y_number);
	if(substr($y_number, 0, 1) == '-') {
		$y_number = trim(substr($y_number, 1)); // remove the minus sign -
	} else {
		$y_number = '-'.$y_number; // add the minus sign -
	} //end if
	//--
	return (string) $y_number;
	//--
} //END FUNCTION
//================================================================


//================================================================
// will return the time interval in days between 2 dates (negative = past ; positive = future)
public static function date_interval_days($y_date_now, $y_date_past) {
	//--
	$y_date_now = date('Y-m-d', @strtotime($y_date_now));
	$y_date_past = date('Y-m-d', @strtotime($y_date_past));
	//--
	$tmp_ux_start = date('U', @strtotime($y_date_now)); // get date now in seconds
	$tmp_ux_end = date('U', @strtotime($y_date_past)); // get date past in seconds
	//--
	$tmp_ux_diff = Smart::format_number_int($tmp_ux_start - $tmp_ux_end); // calc interval in seconds
	$tmp_ux_diff = Smart::format_number_int(ceil($tmp_ux_diff / (60 * 60 * 24))); // calc interval in days
	//--
	return $tmp_ux_diff;
	//--
} //END FUNCTION
//================================================================


//================================================================ Calculate DateTime with FIXED TimeZoneOffset
// this will NOT count the DayLight Savings when calculating date and time from GMT with offset
public static function datetime_fixed_offset($y_timezone_offset, $ydate) {
	//--
	// y_timezone_offset 	:: +0300 :: date('O')
	// ydate 				:: yyyy-mm-dd H:i:s
	//--
	$tmp_tz_offset_sign = substr($y_timezone_offset, 0, 1);
	$tmp_tz_offset_hour = substr($y_timezone_offset, 1, 2);
	$tmp_tz_offset_mins = substr($y_timezone_offset, 3, 2);
	//--
	$out = date('Y-m-d H:i:s', @strtotime($ydate.' '.$tmp_tz_offset_sign.''.$tmp_tz_offset_hour.' hours '.$tmp_tz_offset_sign.''.$tmp_tz_offset_mins.' minutes'));
	//--
	return $out ;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Archive data (string) to B64/Zlib-Raw/Hex
public static function data_archive($y_str) {
	//-- if empty data, return empty string
	if((string)$y_str == '') {
		return '';
	} //end if
	//-- checksum of original data
	$chksum = SmartHashCrypto::sha1((string)$y_str);
	//-- prepare data and add checksum
	$y_str = trim(strtoupper(bin2hex((string)$y_str))).'#CHECKSUM-SHA1#'.$chksum;
	$out = @gzdeflate($y_str, -1, ZLIB_ENCODING_RAW); // don't make it string, may return false ; -1 = default compression of the zlib library is used which is 6
	//-- check for possible deflate errors
	if(($out === false) OR ((string)$out == '')) {
		Smart::log_warning('SmartFramework Utils / Data Archive :: ZLib Deflate ERROR ! ...');
		return '';
	} //end if
	$len_data = strlen((string)$y_str);
	$len_arch = strlen((string)$out);
	if(($len_data > 0) AND ($len_arch > 0)) {
		$ratio = $len_data / $len_arch;
	} else {
		$ratio = 0;
	} //end if
	if($ratio <= 0) { // check for empty input / output !
		Smart::log_warning('SmartFramework Utils / Data Archive :: ZLib Data Ratio is zero ! ...');
		return '';
	} //end if
	if($ratio > 32768) { // check for this bug in ZLib {{{SYNC-GZ-ARCHIVE-ERR-CHECK}}}
		Smart::log_warning('SmartFramework Utils / Data Archive :: ZLib Data Ratio is higher than 32768 ! ...');
		return '';
	} //end if
	//--
	$y_str = ''; // free mem
	//-- add signature
	$out = (string) trim((string)base64_encode((string)$out))."\n".'PHP.SF.151129/B64.ZLibRaw.HEX';
	//-- test unarchive
	$unarch_checksum = SmartHashCrypto::sha1(self::data_unarchive($out));
	if((string)$chksum != (string)$unarch_checksum) { // check: if there is a serious bug with ZLib or PHP we can't tolerate, so test decompress here !!
		Smart::log_warning('SmartFramework Utils / Data Archive :: Data Encode Check Failed ! ...');
		return '';
	} //end if
	//-- if all test pass, return archived data
	return (string) $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Unarchive data (string) from B64/Zlib-Raw/Hex
public static function data_unarchive($y_enc_data) {
	//--
	$y_enc_data = trim((string)$y_enc_data);
	//--
	if((string)$y_enc_data == '') {
		return '';
	} //end if
	//--
	$out = ''; // initialize output
	//-- pre-process
	$arr = array();
	$arr = explode("\n", (string)$y_enc_data);
	$y_enc_data = ''; // free mem
	$arr[0] = trim((string)$arr[0]); // is the data packet
	$arr[1] = trim((string)$arr[1]); // signature
	//-- check signature
	if((string)$arr[1] != 'PHP.SF.151129/B64.ZLibRaw.HEX') { // signature is different, try to decode but log the error
		Smart::log_notice('SmartFramework // Data Unarchive // Invalid Package Signature: '.$arr[1]);
	} //end if
	//-- decode it (at least try)
	$out = @base64_decode((string)$arr[0]); // NON-STRICT ! don't make it string, may return false
	if(($out === false) OR ((string)trim((string)$out) == '')) { // use trim, the deflated string can't contain only spaces
		Smart::log_warning('SmartFramework // Data Unarchive // Invalid B64 Data for packet with signature: '.$arr[1]);
		return '';
	} //end if
	$out = @gzinflate($out);
	if(($out === false) OR ((string)trim((string)$out) == '')) {
		Smart::log_warning('SmartFramework // Data Unarchive // Invalid Zlib GzInflate Data for packet with signature: '.$arr[1]);
		return '';
	} //end if
	//-- post-process
	if(strpos((string)$out, '#CHECKSUM-SHA1#') !== false) {
		//--
		$arr = array();
		$arr = (array) explode('#CHECKSUM-SHA1#', (string)$out);
		$out = '';
		$arr[0] = @hex2bin(strtolower(trim((string)$arr[0]))); // don't make it string, may return false ; it is the data packet
		if(($arr[0] === false) OR ((string)$arr[0] == '')) { // no trim here ... (the real string may contain only some spaces)
			Smart::log_warning('SmartFramework // Data Unarchive // Invalid HEX Data for packet with signature: '.$arr[1]);
			return '';
		} //end if
		$arr[1] = (string) trim((string)$arr[1]); // the checksum
		if(SmartHashCrypto::sha1($arr[0]) != (string)$arr[1]) {
			Smart::log_warning('SmartFramework // Data Unarchive // Invalid Packet, Checksum FAILED :: A checksum was found but is invalid: '.$arr[1]);
			return '';
		} //end if
		//--
		$out = (string) $arr[0];
		$arr = array();
		//--
	} else {
		//--
		Smart::log_warning('SmartFramework // Data Unarchive // Invalid Packet, no Checksum :: This can occur if decompression failed or an invalid packet has been assigned ...');
		return '';
		//--
	} //end if
	//--
	return (string) $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
// pre-pend a message to a log, keep max 65535 characters long
public static function prepend_to_log($y_message, $y_log) {
	//--
	$y_message = trim(str_replace(array("\n", "\r"), array(' ', ' '), (string)$y_message));
	$y_log = trim(str_replace(array("\r\n", "\r"), array("\n", "\n"), (string)$y_log));
	//--
	if((string)$y_message != '') {
		//--
		if((string)$y_log != '') {
			$arr = (array) explode("\n", (string)$y_log);
		} else {
			$arr = array();
		} //end if else
		$y_log = ''; // reset
		$y_log .= $y_message."\n"; // prepend message
		//--
		for($i=0; $i<Smart::array_size($arr); $i++) {
			//--
			$tmp_line = trim($arr[$i]);
			if((string)$tmp_line != '') {
				$tmp_line .= "\n";
				if((strlen($y_log) + strlen($tmp_line)) <= 65535) { // size of text
					$y_log .= $tmp_line;
				} else {
					break; // log reached max length
				} //end if
			} //end if else
			//--
		} //end for
		//--
		$y_log = trim($y_log);
		//--
	} //end if
	//--
	return (string) $y_log;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function comment_php_code($y_code, $y_repl=['tag-start' => '<!--? ', 'tag-end' => ' ?-->']) {
	//--
	$y_code = (string) $y_code;
	$y_repl = (array) $y_repl;
	//--
	$tag_start 	= (string) $y_repl['tag-start'];
	$tag_end 	= (string) $y_repl['tag-end'];
	//--
	$tmp_regex_php = array(
	//	'<'.'%',
	//	'%'.'>',
		'<'.'?php',
		'<'.'?',
		'?'.'>'
	);
	$tmp_regex_htm = array(
		$tag_start,
		$tag_start,
		$tag_start,
		$tag_end,
		$tag_end
	);
	//--
	return str_ireplace($tmp_regex_php, $tmp_regex_htm, $y_code);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function pretty_print_var($y_var, $indent=0) {
	//--
	$out = '';
	//--
	if(is_array($y_var)) {
		//--
		$spaces = '';
		for($i=0; $i<(int)$indent; $i++) {
			$spaces .= "\t";
		} //end for
		$indent += 1;
		//--
		$out .= '['."\n";
		//--
		foreach($y_var as $key => $val) {
			$out .= $spaces;
			if(is_array($val)) {
				$out .= "\t".$key.' => '.self::pretty_print_var($val, $indent);
			} else {
				$out .= "\t".$key.' => '.$val;
			} //end if else
			$out .= "\n";
		} //end foreach
		//--
		$out .= $spaces.']';
		//--
	} else {
		//--
		$out = (string) $y_var;
		//--
	} //end if
	//--
	return (string) $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function pretty_print_bytes($y_bytes, $y_decimals=1, $y_separator=' ') {
	//--
	$y_decimals = (int) $y_decimals;
	if($y_decimals < 0) {
		$y_decimals = 0;
	} //end if
	if($y_decimals > 4) {
		$y_decimals = 4;
	} //end if
	//--
	if(!is_int($y_bytes)) {
		return (string) $y_bytes;
	} //end if
	//--
	if($y_bytes < 1000) {
		return (string) Smart::format_number_int($y_bytes).$y_separator.'bytes';
	} //end if
	//--
	$y_bytes = $y_bytes / 1000;
	if($y_bytes < 1000) {
		return (string) Smart::format_number_dec($y_bytes, $y_decimals, '.', '').$y_separator.'KB';
	} //end if
	//--
	$y_bytes = $y_bytes / 1000;
	if($y_bytes < 1000) {
		return (string) Smart::format_number_dec($y_bytes, $y_decimals, '.', '').$y_separator.'MB';
	} //end if
	//--
	$y_bytes = $y_bytes / 1000;
	if($y_bytes < 1000) {
		return (string) Smart::format_number_dec($y_bytes, $y_decimals, '.', '').$y_separator.'GB';
	} //end if
	//--
	$y_bytes = $y_bytes / 1000;
	//--
	return (string) Smart::format_number_dec($y_bytes, $y_decimals, '.', '').$y_separator.'TB';
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function pretty_print_numbers($y_number, $y_decimals=1) {
	//--
	$y_decimals = (int) $y_decimals;
	if($y_decimals < 0) {
		$y_decimals = 0;
	} //end if
	if($y_decimals > 4) {
		$y_decimals = 4;
	} //end if
	//--
	if(!is_int($y_number)) {
		return (string) $y_number;
	} //end if
	//--
	if($y_number < 1000) {
		return (string) Smart::format_number_int($y_number);
	} //end if
	//--
	$y_number = $y_number / 1000;
	if($y_number < 1000) {
		return (string) Smart::format_number_dec($y_number, $y_decimals, '.', '').'k';
	} //end if
	//--
	$y_number = $y_number / 1000;
	//--
	return (string) Smart::format_number_dec($y_number, $y_decimals, '.', '').'m';
	//--
} //END FUNCTION
//================================================================


//================================================================
// min: 0 ; max: MMMMCMXCIX
public static function number_to_roman($num) {
	//-- Make sure that we only use the integer portion of the value
	$n = intval($num);
	//--
	if($n == 0) {
		return 0;
	} //end if
	if($n < 0) {
		return 'ERR:MIN:0';
	} //end if
	if($n > 4999) {
		return 'ERR:MAX:4999';
	} //end if
	//--
	$result = '';
	//-- Declare a lookup array that we will use to traverse the number:
	$lookup = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
	//--
	foreach ($lookup as $roman => $value) {
		//-- Determine the number of matches
		$matches = intval($n / $value);
		//-- Store that many characters
		$result .= str_repeat($roman, $matches);
		//-- Substract that from the number
		$n = $n % $value;
		//--
	} //end foreach
	//-- The Roman numeral should be built, return it
	return $result;
	//--
} //END FUNCTION
//================================================================


//================================================================
// based on PHP roman to number, author: Sterling Hughes <sterling@php.net>
// min: 0 ; max: MMMMCMXCIX
public static function roman_to_number($roman) {
	//--
	$roman = (string) $roman;
	//--
	if(!preg_match('/^(?i:(?=[MDCLXVI])((M{0,4})((C[DM])|(D?C{0,3}))?((X[LC])|(L?XX{0,2})|L)?((I[VX])|(V?(II{0,2}))|V)?))$/i', $roman)) {
		return 0;
	} //end if
	//--
	$conv = array(
		array('letter' => 'I', 'number' => 1),
		array('letter' => 'V', 'number' => 5),
		array('letter' => 'X', 'number' => 10),
		array('letter' => 'L', 'number' => 50),
		array('letter' => 'C', 'number' => 100),
		array('letter' => 'D', 'number' => 500),
		array('letter' => 'M', 'number' => 1000),
		array('letter' => 0, 'number' => 0)
	);
	//--
	$arabic = 0;
	$state = 0;
	$sidx = 0;
	$len = strlen($roman) - 1;
	//--
	while($len >= 0) {
		//--
		$i = 0;
		$sidx = $len;
		//--
		while($conv[$i]['number'] > 0) {
			//--
			if(strtoupper($roman[$sidx]) == $conv[$i]['letter']) {
				if($state > $conv[$i]['number']) {
					$arabic -= $conv[$i]['number'];
				} else {
					$arabic += $conv[$i]['number'];
					$state = $conv[$i]['number'];
				} //end if else
			} //end if
			//--
			$i++;
			//--
		} //end while
		//--
		$len--;
		//--
	} //end while
	//--
	return($arabic);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function calc_percent($number, $maxnumber) {
	//--
	$maxnumber = 0 + $maxnumber;
	//--
	if($maxnumber <= 0) {
		$out = 0 ;
	} else {
		$out = (0+$number) / $maxnumber * 100 ;
	} //end if else
	//--
	return Smart::format_number_dec($out, 2, '.', '') ;
	//--
} //END FUNCTION
//================================================================


//================================================================
// extract HTML title (must not exceed 128 characters ; recommended is max 65) ; no changes
public static function extract_title($ytxt, $y_limit=65, $clear_numbers=false) {
	//--
	if($clear_numbers === true) {
		$ytxt = (string) self::cleanup_numbers_from_text((string)$ytxt);
	} //end if
	//--
	$ytxt = (string) Smart::striptags((string)$ytxt, 'no'); // will do strip tags
	$ytxt = (string) Smart::normalize_spaces((string)$ytxt); // will do normalize spaces
	//--
	$ytxt = (string) trim((string)$ytxt);
	if((string)$ytxt == '') {
		return '';
	} //end if
	//--
	$y_limit = Smart::format_number_int($y_limit, '+');
	if($y_limit < 10) {
		$y_limit = 10;
	} elseif($y_limit > 128) {
		$y_limit = 128;
	} //end if
	//--
	return (string) trim((string)Smart::text_cut_by_limit((string)$ytxt, (int)$y_limit, false, ''));
	//--
} //END FUNCTION
//================================================================


//================================================================
// extract HTML meta description (must not exceed 256 characters ; recommended is max 155 characters)
public static function extract_description($ytxt, $y_limit=155, $clear_numbers=false) {
	//--
	if($clear_numbers === true) {
		$ytxt = (string) self::cleanup_numbers_from_text((string)$ytxt);
	} //end if
	//--
	$ytxt = (string) trim((string)$ytxt);
	if((string)$ytxt == '') {
		return '';
	} //end if
	//--
	$y_limit = Smart::format_number_int($y_limit, '+');
	if($y_limit < 10) {
		$y_limit = 10;
	} //end if
	if($y_limit > 256) {
		$y_limit = 256;
	} //end if
	//--
	$arr = (array) self::extract_words_from_text_html($ytxt); // will do strip tags + normalize spaces
	$ytxt = (string) implode(' ', (array)$arr);
	$arr = null; // free mem
	//--
	return (string) trim((string)Smart::text_cut_by_limit((string)$ytxt, (int)$y_limit, false, ''));
	//--
} //END FUNCTION
//================================================================


//================================================================
// prepare HTML compliant keywords from a string
// max is 128 words, recommended is 97 words
// will find the keywords listed descending by the occurence number
// keywords with higher frequency will be listed first
// We add Strategy: Max 2% up to 7% of keywords from existing text (SEO req.)
public static function extract_keywords($ytxt, $y_count=97, $clear_numbers=true) {
	//--
	if($clear_numbers === true) {
		$ytxt = (string) self::cleanup_numbers_from_text((string)$ytxt);
	} //end if
	//--
	$ytxt = (string) trim((string)$ytxt);
	if((string)$ytxt == '') {
		return '';
	} //end if
	//--
	$y_count = Smart::format_number_int($y_count, '+');
	if($y_count < 10) {
		$y_count = 10;
	} //end if
	if($y_count > 128) {
		$y_count = 128;
	} //end if
	//--
	$ytxt = str_replace(',', ' ', SmartUnicode::str_tolower($ytxt));
	$arr = self::extract_words_from_text_html($ytxt); // will do strip tags + normalize spaces
	if(is_array($arr)) {
		$arr = (array) array_unique($arr);
	} //end if
	//--
	$cnt = 0;
	$out = '';
	for($i=0; $i<Smart::array_size($arr); $i++) { // allow: '&', '-', '.'
		$tmp_word = (string) trim((string)str_replace(['`', '~', '!', '@', '#', '$', '%', '^', '*', '(', ')', '_', '+', '=', '[', ']', '{', '}', '|', '\\', '/', '?', '<', '>', ',', ':', ';', '"', "'"], ' ', (string)$arr[$i]));
		$tmp_word = (string) preg_replace("/(\.)\\1+/", '.', $tmp_word); // suppress multiple . dots and replace with single dot
		$tmp_word = (string) preg_replace("/(\-)\\1+/", '-', $tmp_word); // suppress multiple - minus signs and replace with single minus sign
		if((string)$tmp_word != '') {
			$out .= $tmp_word.', ';
			$cnt++;
		} //end if
		if($cnt >= $y_count) {
			break;
		} //end if
	} //end for
	//--
	return (string) trim((string)$out, ' ,');
	//--
} //END FUNCTION
//================================================================


//================================================================
// prepare HTML compliant keywords from a string
public static function extract_words_from_text_html($ytxt) {
	//--
	$ytxt = Smart::striptags((string)$ytxt, 'no');
	$ytxt = Smart::normalize_spaces((string)$ytxt);
	//--
	return (array) explode(' ', (string)$ytxt);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function cleanup_numbers_from_text($ytxt) {
	//--
	$num_prefix_sufix = '(\(?\)?\-?\:?\+?#?.?)';
	return (string) preg_replace('~'.$num_prefix_sufix.'([0-9]+)'.$num_prefix_sufix.'~i', ' ', (string)$ytxt); // remove numbers from a text
	//--
} //END FUNCTION
//================================================================


//================================================================
// Used for log arrays
public static function arr_log_last_entries($y_arr, $y_count) {
	//--
	if(!is_array($y_arr)) {
		return array(); // return an empty array
	} //end if
	//--
	$y_count = Smart::format_number_int($y_count);
	//--
	$y_arr = (array) $y_arr;
	//--
	$y_count = Smart::format_number_int($y_count);
	//--
	if($y_count < 2) {
		$y_count = 2; // do not allow values lower than 2
	} //end if
	//--
	$new_arr = array();
	//--
	$counter = 0;
	//--
	@arsort($y_arr);
	//--
	foreach($y_arr as $key => $val) {
		if($counter < $y_count) {
			$new_arr[$key] = $val;
			$counter++;
		} else {
			break;
		} //end if else
	} //end foreach
	//--
	return (array) $new_arr;
	//--
} //END FUNCTION
//================================================================


//================================================================ Add leading zeros to a string
public static function left_pad_str($y_string, $y_padnum, $y_padchar) {
	//--
	return str_pad($y_string, $y_padnum, $y_padchar, STR_PAD_LEFT);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function crypto_blowfish_algo() {
	//--
	$cipher = 'blowfish.cbc'; // default: internal
	//--
	if(defined('SMART_FRAMEWORK_SECURITY_OPENSSLBFCRYPTO')) {
		if(SMART_FRAMEWORK_SECURITY_OPENSSLBFCRYPTO === true) {
			$cipher = 'openssl/blowfish/CBC';
		} //end if
	} //end if
	//--
	return (string) $cipher;
	//--
} //END FUNCTION
//================================================================


//================================================================
// This always provides a compatible layer with the JS Blowfish CBC
// It must be used for safe exchanging data between PHP and Javascript
public static function crypto_blowfish_encrypt($y_data, $y_key='') {
	//--
	if((string)$y_key == '') {
		$key = (string) SMART_FRAMEWORK_SECURITY_KEY;
	} else {
		$key = (string) $y_key;
	} //end if
	//--
	$cipher = (string) self::crypto_blowfish_algo();
	//--
	return (string) SmartCipherCrypto::encrypt((string)$cipher, (string)$key, (string)$y_data);
	//--
} //END FUNCTION
//================================================================


//================================================================
// This always provides a compatible layer with the JS Blowfish CBC
// It must be used for safe exchanging data between PHP and Javascript
public static function crypto_blowfish_decrypt($y_data, $y_key='') {
	//--
	if((string)$y_key == '') {
		$key = (string) SMART_FRAMEWORK_SECURITY_KEY;
	} else {
		$key = (string) $y_key;
	} //end if
	//--
	$cipher = (string) self::crypto_blowfish_algo();
	//--
	return (string) SmartCipherCrypto::decrypt((string)$cipher, (string)$key, (string)$y_data);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function crypto_algo() {
	//--
	$cipher = 'hash/sha256'; // default: internal
	//--
	if(defined('SMART_FRAMEWORK_SECURITY_CRYPTO')) {
		if((string)trim((string)SMART_FRAMEWORK_SECURITY_CRYPTO) != '') {
			$cipher = (string) trim((string)SMART_FRAMEWORK_SECURITY_CRYPTO);
		} //end if
	} //end if
	//--
	return (string) $cipher;
	//--
} //END FUNCTION
//================================================================


//================================================================
// This is intended for general use of symetric crypto api in Smart.Framework
// It can use any of the: hash or openssl algos: blowfish, twofish, serpent, ghost
public static function crypto_encrypt($y_data, $y_key='') {
	//--
	if((string)$y_key == '') {
		$key = (string) SMART_FRAMEWORK_SECURITY_KEY;
	} else {
		$key = (string) $y_key;
	} //end if
	//--
	$cipher = (string) self::crypto_algo();
	//--
	return (string) SmartCipherCrypto::encrypt((string)$cipher, (string)$key, (string)$y_data);
	//--
} //END FUNCTION
//================================================================


//================================================================
// This is intended for general use of symetric crypto api in Smart.Framework
// It can use any of the: hash or openssl algos: blowfish, twofish, serpent, ghost
public static function crypto_decrypt($y_data, $y_key='') {
	//--
	if((string)$y_key == '') {
		$key = (string) SMART_FRAMEWORK_SECURITY_KEY;
	} else {
		$key = (string) $y_key;
	} //end if
	//--
	$cipher = (string) self::crypto_algo();
	//--
	return (string) SmartCipherCrypto::decrypt((string)$cipher, (string)$key, (string)$y_data);
	//--
} //END FUNCTION
//================================================================


//================================================================
// Create a Download Link for the Download Handler
public static function create_download_link($y_file, $y_ctrl_key) {
	//--
	$y_file = (string) trim((string)$y_file);
	if((string)$y_file == '') {
		Smart::log_warning('Utils / Create Download Link: Empty File Path has been provided. This means the download link will be unavaliable (empty) to assure security protection.');
		return '';
	} //end if
	if(!SmartFileSysUtils::check_if_safe_path($y_file)) {
		Smart::log_warning('Utils / Create Download Link: Invalid File Path has been provided. This means the download link will be unavaliable (empty) to assure security protection. File: '.$y_file);
		return '';
	} //end if
	//--
	$y_ctrl_key = (string) trim((string)$y_ctrl_key);
	if((string)$y_ctrl_key == '') {
		Smart::log_warning('Utils / Create Download Link: Empty Controller Key has been provided. This means the download link will be unavaliable (empty) to assure security protection.');
		return '';
	} //end if
	if(SMART_FRAMEWORK_ADMIN_AREA === true) { // {{{SYNC-DWN-CTRL-PREFIX}}}
		$y_ctrl_key = (string) 'AdminArea/'.$y_ctrl_key;
	} else {
		$y_ctrl_key = (string) 'IndexArea/'.$y_ctrl_key;
	} //end if
	//--
	$crrtime = (int) time();
	$access_key = SmartHashCrypto::sha1('DownloadLink:'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.SMART_APP_VISITOR_COOKIE.':'.$y_file.'^'.$y_ctrl_key);
	$unique_key = SmartHashCrypto::sha1('Time='.$crrtime.'#'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.$access_key.'-'.self::unique_auth_client_private_key().':'.$y_file.'+'.$y_ctrl_key);
	//-- {{{SYNC-DOWNLOAD-ENCRYPT-ARR}}}
	$safe_download_link = self::crypto_encrypt(
		trim((string)$crrtime)."\n". 							// set the current time
		trim((string)$y_file)."\n". 							// the file path
		trim((string)$access_key)."\n". 						// access key based on UniqueID cookie
		trim((string)$unique_key)."\n".							// unique key based on: User-Agent and IP
		'-'."\n",												// self robot browser UserAgentName/ID key (does not apply here)
		'SmartFramework//DownloadLink'.SMART_FRAMEWORK_SECURITY_KEY
	);
	//--
	return (string) Smart::escape_url(trim((string)$safe_download_link));
	//--
} //END FUNCTION
//================================================================


//================================================================
// require the all bytes (full size string) of an image to detect if GIF / PNG or JPG
public static function guess_image_extension_by_all_bytes($pict) {
	//--
	if(!function_exists('getimagesizefromstring')) {
		//--
		//Smart::log_notice('Cannot use PHP function: getimagesizefromstring(), so will use a fallback: '.__CLASS__.'::'.guess_image_extension_by_first_bytes.'()');
		return (string) self::guess_image_extension_by_first_bytes(substr((string)$pict, 0, 16));
		//--
	} //end if
	//--
	$arr_info = (array) @getimagesizefromstring((string)$pict);
	//$width 	= (int) $arr_info[0]; // not used here
	//$height 	= (int) $arr_info[1]; // not used here
	$imgtyp 	= (int) $arr_info[2]; // image type constant
	if($imgtyp <= 0) {
		return ''; // invalid type detected
	} //end if
	//--
	$ext = (string) strtolower((string)@image_type_to_extension((int)$imgtyp, true)); // return the image extension with . (dot) prepend
	//--
	$type = '';
	switch((string)$ext) {
		case '.gif':
			$type = '.gif';
			break;
		case '.png':
			$type = '.png';
			break;
		case '.jpg':
		case '.jpeg':
			$type = '.jpg';
			break;
		default:
			$type = '';
	} //end switch
	//--
	return (string) $type;
	//--
} //END FUNCTION
//================================================================


//================================================================
// require the first 16 bytes (first 16 characters - string) of an image to detect if GIF / PNG or JPG
public static function guess_image_extension_by_first_bytes($pict) {
	//--
	// .jpg:  FF D8 FF
	// .png:  89 50 4E 47 0D 0A 1A 0A
	// .gif:  GIF89a | GIF87a
	// .tiff: 49 49 2A 00 | 4D 4D 00 2A
	//--
	$pict = (string) $pict;
	if(strlen($pict) < 16) {
		Smart::log_warning(__METHOD__.'(): expects the first 16 bytes for detection ...');
		return '';
	} //end if
	//--
	$type = '';
	if((substr($pict, 0, 6) == 'GIF89a') OR (substr($pict, 0, 6) == 'GIF87a')) {
		$type = '.gif';
	} elseif((bin2hex(substr($pict, 0, 1)) == '89') AND (substr($pict, 1, 3) == 'PNG')) {
		$type = '.png';
	} elseif((strtolower(bin2hex(substr($pict, 0, 1))) == 'ff') AND (strtolower(bin2hex(substr($pict, 1, 1))) == 'd8')) {
		$type = '.jpg';
	} //end if else
	//--
	return (string) $type;
	//--
} //END FUNCTION
//================================================================


//================================================================
// guess extension from URL get headers
public static function guess_image_extension_by_url_head($y_headers) {
	//--
	$y_headers = (string) $y_headers;
	//--
	$temp_image_extension = '';
	$temp_where_was_detected = '???';
	//--
	if(strlen($y_headers) > 0) {
		//-- {{{SYNC-DATA-IMAGE}}}
		if((strtolower(substr($y_headers, 0, 11)) == 'data:image/') AND (stripos($y_headers, ';base64,') !== false)) {
			//--
			$temp_where_was_detected = '??? Try to guess by data:image/ ...';
			//--
			$y_headers = substr($y_headers, 11);
			$eimg = (array) explode(';base64,', $y_headers);
			$eimg[0] = (string) strtolower(trim((string)$eimg[0]));
			if((string)$eimg[0] == 'jpeg') {
				$eimg[0] = 'jpg'; // correction
			} //end if
			if(((string)$eimg[0] == 'png') OR ((string)$eimg[0] == 'gif') OR ((string)$eimg[0] == 'jpg')) {
				$temp_image_extension = '.'.$eimg[0]; // add the point
				$temp_where_was_detected = ' * Embedded in HTML as # data:image/ + ;base64, = '.$eimg[0];
			} //end if
			//--
		} else {
			//--
			$temp_where_was_detected = '??? Try to guess by headers ...';
			//-- try to get file extension by the content (strategy 1)
			$temp_guess_ext_tmp = array();
			preg_match("/^content\-disposition:(.*)$/mi", (string)$y_headers, $temp_guess_ext_tmp);
			$temp_guess_extension = (string) trim((string)$temp_guess_ext_tmp[1]);
			$temp_guess_extension = (array) explode(' filename=', (string)$temp_guess_extension);
			$temp_guess_extension = (string) trim((string)$temp_guess_extension[1]);
			$temp_guess_extension = (array) explode('"', (string)$temp_guess_extension);
			$temp_guess_extension = (string) trim((string)$temp_guess_extension[1]);
			$temp_guess_extension = (string) trim(strtolower(SmartFileSysUtils::get_file_extension_from_path((string)$temp_guess_extension))); // [OK]
			$temp_guess_ext_tmp = array();
			//-- test
			if((string)$temp_guess_extension == 'jpeg') {
				$temp_guess_extension = 'jpg'; // correction
			} //end if
			if(((string)$temp_guess_extension == 'png') OR ((string)$temp_guess_extension == 'gif') OR ((string)$temp_guess_extension == 'jpg')) {
				// OK, we guess it
				$temp_where_was_detected = '[content-disposition]: \''.$temp_guess_extension.'\'';
				$temp_image_extension = Smart::safe_validname($temp_guess_extension); // make it safe
				if((string)$temp_image_extension != '') {
					$temp_image_extension = '.'.strtolower($temp_image_extension); // add the point only if non-empty to avoid issues
				} //end if
			} else {
				//-- try to guess by the content type (strategy 2)
				$temp_guess_ext_tmp = array();
				preg_match("/^content\-type:(.*)$/mi", (string)$y_headers, $temp_guess_ext_tmp);
				$temp_guess_extension = (string) trim((string)$temp_guess_ext_tmp[1]);
				$temp_guess_extension = (array) explode('/', (string)$temp_guess_extension);
				$temp_guess_extension = (string) trim((string)$temp_guess_extension[1]);
				$temp_guess_extension = (array) explode(';', (string)$temp_guess_extension);
				$temp_guess_extension = (string) trim((string)$temp_guess_extension[0]);
				//--
				switch((string)$temp_guess_extension) {
					case 'gif':
						$temp_image_extension = '.gif';
						$temp_where_was_detected = '[content-type]: \''.$temp_image_extension.'\'';
						break;
					case 'png':
						$temp_image_extension = '.png';
						$temp_where_was_detected = '[content-type]: \''.$temp_image_extension.'\'';
						break;
					case 'jpg':
					case 'jpeg':
						$temp_image_extension = '.jpg';
						$temp_where_was_detected = '[content-type]: \''.$temp_image_extension.'\'';
						break;
					case 'html':
						$temp_image_extension = '.htm'; // we want to avoid a wrong answer from server to be get as image
						$temp_where_was_detected = '[content-type]: \''.$temp_image_extension.'\'';
						break;
					default:
						// nothing
						$temp_where_was_detected = '[content-type]: COULD NOT GUESS EXTENSION ! :: \''.$temp_guess_extension.'\'';
				} //end switch
				//--
			} //end if else
			//--
		} //end if
		//--
	} //end if else
	//--
	return array('extension' => (string) $temp_image_extension, 'where-was-detected' => (string) $temp_where_was_detected);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Load a File or an URL
 * it may use 3 methods: FileRead, CURL or HTTP-Browser class
 *
 * @param STRING 	$y_url_or_path			:: /path/to/file | http(s)://some.url:port/path (port is optional)
 * @param NUMBER 	$y_timeout				:: timeout in seconds
 * @param ENUM 		$y_method 				:: used only for URLs, the browsing method: GET | POST
 * @param ENUM		$y_ssl_method			:: SSL Mode: tls | sslv3 | sslv2 | ssl
 * @param STRING 	$y_auth_name			:: used only for URLs, the auth user name
 * @param STRING 	$y_auth_pass			:: used only for URLs, the auth password
 * @param YES/NO	y_allow_set_credentials	:: DEFAULT MUST BE set to NO ; if YES must be set just for internal URLs ; if the $y_url_or_path to get is detected to be under current URL will send also the Unique / session IDs ; more if detected that is from admin.php and if this is set to YES will send the HTTP-BASIC Auth credentials if detected (using YES with other URLs than SmartFramework's current URL can be a serious SECURITY ISSUE, so don't !)
 */
public static function load_url_or_file($y_url_or_path, $y_timeout=30, $y_method='GET', $y_ssl_method='', $y_auth_name='', $y_auth_pass='', $y_allow_set_credentials='no') {
	//-- v.2016-01-15
	// fixed sessionID with new Dynamic generated
	// TODO: use the CURL to browse also FTP and SSH ...
	//--
	$y_url_or_path = (string) $y_url_or_path;
	//--
	if((string)$y_url_or_path == '') {
		//--
		return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
			'log' => 'ERROR: FILE Name is Empty ...',
			'mode' => 'file',
			'result' => '0',
			'code' => '400', // HTTP 400 BAD REQUEST
			'headers' => '',
			'content' => '',
			'debuglog' => ''
		);
		//--
	} //end if
	//-- detect if file or url
	if((substr($y_url_or_path, 0, 7) == 'http://') OR (substr($y_url_or_path, 0, 8) == 'https://')) {
		$test_file = 0; // it is a url
	} else {
		$test_file = 1; // it is a file
	} //end if
	//--
	if($test_file == 1) {
		//--
		$y_url_or_path = trim($y_url_or_path);
		//-- try to detect if data:image/ :: {{{SYNC-DATA-IMAGE}}}
		if((strtolower(substr($y_url_or_path, 0, 11)) == 'data:image/') AND (stripos($y_url_or_path, ';base64,') !== false)) {
			//--
			$eimg = (array) explode(';base64,', $y_url_or_path);
			//--
			return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
				'log' => 'OK ? Not sure, decoded from embedded b64 image: ',
				'mode' => 'embedded',
				'result' => '1',
				'code' => '200', // HTTP 200 OK
				'headers' => (string) SmartUnicode::sub_str($y_url_or_path, 0, 50).'...', // try to get the 1st 50 chars for trying to guess the extension
				'content' => (string) @base64_decode((string)trim((string)$eimg[1])),
				'debuglog' => ''
			);
			//--
		} elseif(SmartFileSystem::is_type_file($y_url_or_path)) {
			//--
			return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
				'log' => 'OK: FILE Exists',
				'mode' => 'file',
				'result' => '1',
				'code' => '200', // HTTP 200 OK
				'headers' => 'Content-Disposition: inline; filename="'.basename($y_url_or_path).'"'."\n",
				'content' => (string) SmartFileSystem::read($y_url_or_path),
				'debuglog' => ''
			);
			//--
		} else {
			//--
			return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
				'log' => 'ERROR: FILE Not Found or Invalid Data ...',
				'mode' => 'file',
				'result' => '0',
				'code' => '404', // HTTP 404 NOT FOUND
				'headers' => '',
				'content' => '',
				'debuglog' => ''
			);
			//--
		} //end if else
		//--
	} else {
		//--
		if((string)$y_ssl_method == '') {
			if(defined('SMART_FRAMEWORK_SSL_MODE')) {
				$y_ssl_method = (string) SMART_FRAMEWORK_SSL_MODE;
			} else {
				Smart::log_notice('NOTICE: LibUtils/Load-URL-or-File // The SSL Method not defined and SMART_FRAMEWORK_SSL_MODE was not defined. Using the `tls` as default ...');
				$y_ssl_method = 'tls';
			} //end if else
		} //end if
		//--
		$browser = new SmartHttpClient();
		//--
		$y_timeout = Smart::format_number_int($y_timeout,'+');
		if($y_timeout <= 0) {
			$y_timeout = 30; // default value
		} //end if
		$browser->connect_timeout = (int) $y_timeout;
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			$browser->debug = 1;
		} //end if
		//--
		if((string)self::get_server_current_protocol() == 'https://') {
			$tmp_current_protocol = 'https://';
		} else {
			$tmp_current_protocol = 'http://';
		} //end if else
		//--
		$tmp_current_server = (string) self::get_server_current_domain_name();
		$tmp_current_port 	= (string) self::get_server_current_port();
		$tmp_current_path 	= (string) self::get_server_current_request_uri();
		$tmp_current_script = (string) self::get_server_current_full_script();
		//--
		$tmp_test_url_arr 		= (array) Smart::url_parse($y_url_or_path);
		$tmp_test_browser_id 	= (array) self::get_os_browser_ip();
		//--
		$tmp_extra_log = '';
		if(SmartFrameworkRuntime::ifDebug()) {
			$tmp_extra_log .= "\n".'===== # ====='."\n";
		} //end if
		//--
		$cookies = array();
		$auth_name = (string) $y_auth_name;
		$auth_pass = (string) $y_auth_pass;
		//--
		if((string)$y_allow_set_credentials == 'yes') {
			//--
			if(SmartFrameworkRuntime::ifDebug()) {
				$tmp_extra_log .= '[EXTRA]: I will try to detect if this is my current Domain and I will check if it is safe to send my sessionID COOKIE and my Auth CREDENTIALS ...'."\n";
			} //end if
			//--
			if(((string)$tmp_current_protocol == (string)$tmp_test_url_arr['protocol']) AND ((string)$tmp_current_server == (string)$tmp_test_url_arr['host']) AND ((string)$tmp_current_port == (string)$tmp_test_url_arr['port'])) {
				//--
				if(SmartFrameworkRuntime::ifDebug()) {
					$tmp_extra_log .= '[EXTRA]: OK, Seems that the browsed Domain is identical with my current Domain which is: '.$tmp_current_protocol.$tmp_current_server.':'.$tmp_current_port.' and the browsed one is: '.$tmp_test_url_arr['protocol'].$tmp_test_url_arr['host'].':'.$tmp_test_url_arr['port']."\n";
					$tmp_extra_log .= '[EXTRA]: I will also check if my current script and path are identical with the browsed ones ...'."\n";
				} //end if
				//--
				if(((string)$tmp_current_script == (string)$tmp_test_url_arr['path']) AND (substr($tmp_current_path, 0, strlen($tmp_current_script)) == (string)$tmp_test_url_arr['path'])) {
					//--
					if(SmartFrameworkRuntime::ifDebug()) {
						$tmp_extra_log .= '[EXTRA]: OK, Seems that the current script is identical with the browsed one :: '.'Current Path is: \''.$tmp_current_script.'\' / Browsed Path is: \''.$tmp_test_url_arr['path'].'\' !'."\n";
						$tmp_extra_log .= '[EXTRA]: I will check if I have to send my SessionID so I will check the browserID ...'."\n";
					} //end if
					//--
					$browser->useragent = (string) self::get_selfrobot_useragent_name(); // this must be set just when detected the same path and script ; it is a requirement to detect it as the self-robot [ @s# ] in order to send the credentials or the current
					//-- {{{SYNC-SMART-UNIQUE-COOKIE}}}
					if((defined('SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME')) AND (!defined('SMART_FRAMEWORK_UNIQUE_ID_COOKIE_SKIP'))) {
						if((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME != '') {
							if(SmartFrameworkSecurity::ValidateVariableName(strtolower((string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME))) {
								//--
								if((string)SMART_APP_VISITOR_COOKIE != '') { // if set, then forward
									if(SmartFrameworkRuntime::ifDebug()) {
										$tmp_extra_log .= '[EXTRA]: OK, I will send my current Visitor Unique Cookie ID as it is set and not empty ...'."\n";
									} //end if
									$cookies[(string)SMART_FRAMEWORK_UNIQUE_ID_COOKIE_NAME] = (string) SMART_APP_VISITOR_COOKIE; // this is a requirement
								} //end if
								//--
							} //end if
						} //end if
					} //end if
					//-- #end# sync
					if(((string)SmartAuth::get_login_method() == 'HTTP-BASIC') AND ((string)$auth_name == '') AND ((string)$auth_pass == '') AND (strpos($tmp_current_script, '/admin.php') !== false) AND (strpos($tmp_test_url_arr['path'], '/admin.php') !== false)) {
						//--
						if(SmartFrameworkRuntime::ifDebug()) {
							$tmp_extra_log .= '[EXTRA]: HTTP-BASIC Auth method detected / Allowed to pass the Credentials - as the browsed URL belongs to this ADMIN Server as I run, the Auth credentials are set but passed as empty - everything seems to be safe I will send my credentials: USERNAME = \''.SmartAuth::get_login_id().'\' ; PASS = *****'."\n";
						} //end if
						//--
						$auth_name = (string) SmartAuth::get_login_id();
						$auth_pass = (string) SmartAuth::get_login_password();
						//--
					} //end if
					//--
				} else {
					//--
					if(SmartFrameworkRuntime::ifDebug()) {
						$tmp_extra_log .= '[EXTRA]: Seems that the scripts are NOT identical :: '.'Current Script is: \''.$tmp_current_script.'\' / Browsed Script is: \''.$tmp_test_url_arr['path'].'\' !'."\n";
						$tmp_extra_log .= '[EXTRA]: This is the diff for having a comparation: '.substr($tmp_current_path, 0, strlen($tmp_current_script))."\n";
					} //end if
					//--
				} //end if
				//--
			} //end if
			//--
		} //end if
		//--
		$browser->cookies = (array) $cookies;
		//--
		$data = (array) $browser->browse_url($y_url_or_path, $y_method, $y_ssl_method, $auth_name, $auth_pass); // do browse
		//--
		return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
			'log' 		=> (string) $data['log'].$tmp_extra_log,
			'mode' 		=> (string) $data['mode'],
			'result' 	=> (string) $data['result'],
			'code' 		=> (string) $data['code'],
			'headers' 	=> (string) $data['headers'],
			'content' 	=> (string) $data['content'],
			'debuglog' 	=> (string) $data['debuglog']
		);
		//--
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Load/Save a cache file from Memory or from a URL
 *
 * @param STRING 	$y_cache_file_extension		:: File Extension (example: '.ext')
 * @param STRING 	$y_cache_prefix				:: prefix dir (at least 3 chars) ended by slash (Example: 'prefix/')
 * @param STRING 	$y_load_url					:: URL to Load (Ex: http(s)://some/test.txt ; memory://some.unique.key)
 * @param STRING	$y_content					:: just for memory:// ; contents of the file to be saved into cache - [set] mode ; if this is empty will just get
 * @param INT 		$y_cache_expire				:: 0=never ; (>0)=seconds
 * @param ENUM 		$y_encrypted				:: yes/no to encrypt the file content
 * @return MIXED								:: cached contents
 */
public static function load_cached_content($y_cache_file_extension, $y_cache_prefix, $y_load_url, $y_content='', $y_cache_expire=0, $y_encrypted='no') {

	//--
	$y_load_url = (string) $y_load_url;
	//--
	if((string)$y_load_url == '') {
		Smart::log_warning('Utils // Load From Cache ... Empty URL ...');
		return '';
	} //end if
	//--

	//--
	$y_cache_file_extension = Smart::safe_validname($y_cache_file_extension);
	//--
	$y_cache_expire = Smart::format_number_int($y_cache_expire, '+');
	//--
	$y_cache_prefix = (string) $y_cache_prefix;
	//--
	if((strlen($y_cache_prefix) >= 3) AND (strlen($y_cache_prefix) <= 64)) {
		//--
		$y_cache_prefix = SmartFileSysUtils::add_dir_last_slash($y_cache_prefix); // fix trailing slash
		//--
	} else {
		//--
		$y_cache_prefix = 'default/';
		//--
	} //end if
	//--

	//--
	$unique_id = (string) SmartHashCrypto::sha1('@@::SmartFramework::Content::Cache@@'.$y_load_url);
	//--
	$dir = 'tmp/cache/'.$y_cache_prefix.SmartFileSysUtils::prefixed_sha1_path($unique_id);
	SmartFileSysUtils::raise_error_if_unsafe_path($dir);
	//--
	$file = (string) $dir.$unique_id.$y_cache_file_extension;
	SmartFileSysUtils::raise_error_if_unsafe_path($file);
	//--

	//--
	if(!SmartFileSystem::is_type_dir($dir)) {
		SmartFileSystem::dir_create($dir, true); // recursive
	} // end if
	//--
	$protect_file = $dir.'index.html';
	if(!SmartFileSystem::is_type_file($protect_file)) {
		SmartFileSystem::write($protect_file, '');
	} //end if
	//--

	//-- will go through this only if cache expired or no cache
	if((!SmartFileSystem::is_type_file($file)) OR ((SmartFileSystem::is_type_file($file)) AND ($y_cache_expire > 0) AND ((SmartFileSystem::get_file_mtime($file) + $y_cache_expire) < time()))) {
		//-- read
		if((substr($y_load_url, 0, 9) == 'memory://') AND ((string)$y_content != '')) {
			//-- set the content from memory
			$tmp_content = (string) $y_content;
			$tmp_result = '1';
			$tmp_code = '200';
			//--
		} elseif(substr($y_load_url, 0, 9) != 'memory://') {
			//--
			$arr = self::load_url_or_file($y_load_url); // [OK]
			$tmp_result = $arr['result'];
			$tmp_code = $arr['code'];
			$tmp_content = $arr['content'];
			$arr = array();
			//--
		} //end if else
		//-- if required, apply encryption
		if((string)$y_encrypted == 'yes') {
			//--
			$tmp_content = self::crypto_blowfish_encrypt($tmp_content);
			//--
		} //end if
		//-- write to cache
		if(((string)$tmp_result == '1') AND ((string)$tmp_code == '200')) {
			//--
			SmartFileSystem::write($file, $tmp_content); // save file to cache (safe write is controlled via locks)
			//--
		} //end if
		//--
		$tmp_content = '';
		//--
	  } //end if
	  //--

	//-- get from cache
	$out = SmartFileSystem::read($file);
	//--
	if((string)$y_encrypted == 'yes') {
		$out = self::crypto_blowfish_decrypt($out);
	} //end if
	//--

	//--
	return $out;
	//--

} //END FUNCTION
//================================================================


//================================================================
// Handle Upload One File ; To handle many files upload at once must iterate using this function by var_index=0..n
// returns FALSE if no file uploaded ; Empty String if OK ; Error String otherwise
public static function store_uploaded_file($dest_dir, $var_name, $var_index=-1, $allow_rewrite=true, $max_size=0, $allowed_extensions='', $new_name='', $enforce_lowercase=false) {
	//--
	$dest_dir = (string) $dest_dir;
	$var_name = (string) trim((string)$var_name);
	$var_index = (int) $var_index;
	if((string)$allow_rewrite === 'versioning') {
		$allow_rewrite = (string) $allow_rewrite;
	} else {
		$allow_rewrite = (bool) $allow_rewrite;
	} //end if else
	$max_size = (int) Smart::format_number_int($max_size,'+');
	if($max_size <= 0) {
		$max_size = (int) SmartFileSysUtils::max_upload_size();
	} //end if
	$allowed_extensions = (string) trim((string)$allowed_extensions);
	$new_name = (string) $new_name; // an optional override file name (NO extension !!! The extension will be preserved from the uploaded file)
	//--
	if(Smart::array_size($_FILES) <= 0) {
		return false; // no files uploads detected ; should return no error ...
	} //end if
	//--
	if((string)$var_name == '') {
		return 'Invalid File Var Name';
	} //end if
	//--
	if(SmartFileSysUtils::check_if_safe_path((string)$dest_dir) != '1') {
		return 'Invalid Destination Dir: Unsafe DirName';
	} //end if
	$dest_dir = (string) SmartFileSysUtils::add_dir_last_slash((string)$dest_dir);
	if(SmartFileSysUtils::check_if_safe_path((string)$dest_dir) != '1') {
		return 'Invalid Destination Dir: Unsafe Path';
	} //end if
	if(SmartFileSystem::is_type_dir((string)$dest_dir) !== true) {
		return 'Invalid Destination Dir: Path must exist and it must be a directory';
	} //end if
	//--
	if($var_index >= 0) {
		$the_upld_file_name = (string) $_FILES[$var_name]['name'][$var_index];
		$the_upld_file_tmpname = (string) $_FILES[$var_name]['tmp_name'][$var_index];
		$the_upld_file_error = (int) $_FILES[$var_name]['error'][$var_index];
	} else {
		$the_upld_file_name = (string) $_FILES[$var_name]['name'];
		$the_upld_file_tmpname = (string) $_FILES[$var_name]['tmp_name'];
		$the_upld_file_error = (int) $_FILES[$var_name]['error'];
	} //end if else
	//-- check uploaded tmp name
	$the_upld_file_tmpname = (string) trim((string)$the_upld_file_tmpname);
	if((string)$the_upld_file_tmpname == '') {
		return false; // should return no error because the file may not be uploaded
	} //end if
	//-- fix file name
	$the_upld_file_name = (string) SmartUnicode::deaccent_str($the_upld_file_name);
	$the_upld_file_name = (string) str_replace('#', '-', $the_upld_file_name); // {{{SYNC-WEBDAV-#-ISSUE}}}
	$the_upld_file_name = (string) Smart::safe_filename($the_upld_file_name, '-'); // {{{SYNC-SAFE-FNAME-REPLACEMENT}}}
	//-- remove versioning if any
	$the_upld_file_name = (string) SmartFileSysUtils::version_remove($the_upld_file_name);
	//-- remove dangerous characters
	$the_upld_file_name = (string) trim((string)str_replace(['\\', ' ', '?'], ['-', '-', '-'], (string)$the_upld_file_name));
	$the_upld_file_name = (string) trim((string)$the_upld_file_name);
	//-- hard limit for file name length for max 100 characters
	if((string)$the_upld_file_name == '') {
		return 'Uploaded File Name is Invalid (Empty)';
	} //end if
	if(strlen((string)$the_upld_file_name) > 100) {
		return 'Uploaded File Name is too long (oversize 100 characters): '.$the_upld_file_name;
	} //end if
	if(!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$the_upld_file_name)) {
		return 'Uploaded File Name is Invalid (not Safe): '.$the_upld_file_name;
	} //end if
	//-- protect against dot files .*
	if(substr((string)$the_upld_file_name, 0, 1) == '.') {
		return 'Uploaded File Name is Invalid (Dot .Files are not allowed for safety): '.$the_upld_file_name;
	} //end if
	//--
	$tmp_fext = (string) strtolower((string)SmartFileSysUtils::get_file_extension_from_path((string)$the_upld_file_name)); // get the extension
	if((string)$new_name != '') {
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$new_name)) {
			return 'Uploaded File New Name: `'.$new_name.'` is Invalid for file name: '.$the_upld_file_name;
		} //end if
		if(substr((string)$new_name, 0, 1) == '.') {
			return 'Uploaded File New Name: `'.$new_name.'` is Invalid (Dot .Files are not allowed for safety): '.$the_upld_file_name;
		} //end if
		$the_upld_file_name = (string) SmartFileSysUtils::version_remove((string)trim((string)$new_name)); // since the new name is provided programatically we do not check if > 100 chars ...
		if($var_index >= 0) {
			$the_upld_file_name .= ''.(int)$var_index;
		} //end if
		$the_upld_file_name .= '.'.$tmp_fext;
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$the_upld_file_name)) {
			return 'Uploaded New File Name `'.$the_upld_file_name.'` is Invalid (not Safe): '.$the_upld_file_name;
		} //end if
	} //end if
	if($enforce_lowercase === true) {
		$the_upld_file_name = (string) strtolower((string)$the_upld_file_name);
	} //end if
	//-- {{{SYNC-CHK-ALLOWED-DENIED-EXT}}}
	if((string)$allowed_extensions != '') {
		if(stripos((string)$allowed_extensions, '<'.$tmp_fext.'>') === false) {
			return 'Upload Failed: The uploaded file extension is not in the current custom allowed extensions list for file: '.$the_upld_file_name;
		} //end if
	} //end if
	if((defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) AND ((string)trim((string)SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS) != '')) {
		if(stripos((string)SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') === false) {
			return 'Upload Failed: The uploaded file extension is not in the current allowed extensions list configuration for file: '.$the_upld_file_name;
		} //end if
	} //end if
	if((!defined('SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS')) OR (stripos((string)SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') !== false)) {
		return 'Upload Failed: The uploaded file extension is denied by the current configuration for file: '.$the_upld_file_name;
	} //end if
	//-- check for upload errors
	$up_err = '';
	switch((int)$the_upld_file_error) {
		case UPLOAD_ERR_OK:
			// OK, no error
			break;
		case UPLOAD_ERR_INI_SIZE:
			$up_err = 'UPLOAD ERROR: The uploaded file exceeds the upload_max_filesize directive in php.ini';
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$up_err = 'UPLOAD ERROR: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			break;
		case UPLOAD_ERR_PARTIAL:
			$up_err = 'UPLOAD ERROR: The uploaded file was only partially uploaded';
			break;
		case UPLOAD_ERR_NO_FILE:
			$up_err = 'UPLOAD ERROR: No file was uploaded';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$up_err = 'UPLOAD ERROR: Missing a temporary folder';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$up_err = 'UPLOAD ERROR: Failed to write file to disk';
			break;
		case UPLOAD_ERR_EXTENSION:
			$up_err = 'UPLOAD ERROR: File upload stopped by extension';
			break;
		default:
			$up_err =  'UPLOAD ERROR: Unknown error ...';
	} //end switch
	if((string)$up_err != '') {
		return (string) $up_err.' for file: '.$the_upld_file_name;
	} //end if
	//-- if there is an existing file already with the same name
	if(SmartFileSystem::is_type_file($dest_dir.$the_upld_file_name)) {
		if((string)$allow_rewrite === 'versioning') {
			if(!SmartFileSystem::rename($dest_dir.$the_upld_file_name, $dest_dir.SmartFileSysUtils::version_add($the_upld_file_name, SmartFileSysUtils::version_stdmtime()))) {
				return 'Upload Failed: Destination File Versioning Failed for file: '.$the_upld_file_name;
			} //end if
		} elseif($allow_rewrite === false) {
			return 'Upload Failed: Destination File Exists and Allow Rewrite is turned off for file: '.$the_upld_file_name;
		} else { // true
			if(!SmartFileSystem::delete($dest_dir.$the_upld_file_name)) { // try to remove the destination file (will be replaced with new uploaded version)
				return 'Upload Failed: Destination File Exists and could not be removed for file: '.$the_upld_file_name;
			} //end if
		} //end if else
	} //end if
	//-- do upload
	if(!is_uploaded_file((string)$the_upld_file_tmpname)) {
		return 'FATAL ERROR: Cannot find the uploaded data for file: '.$the_upld_file_name;
	} //end if
	$fsize_upld = (int) SmartFileSystem::get_file_size($the_upld_file_tmpname);
	if((int)$fsize_upld <= 0) { // dissalow upload empty files, does not make sense or there was an error !!!
		return 'Upload Failed: File is empty: '.$the_upld_file_name;
	} elseif((int)$fsize_upld > (int)$max_size) {
		return 'Upload Failed: File is oversized: '.$the_upld_file_name;
	} //end if
	if(!SmartFileSystem::move_uploaded($the_upld_file_tmpname, $dest_dir.$the_upld_file_name, true)) { // also check sha1-file
		return 'Failed to Move the Uploaded File: '.$the_upld_file_name.' to the Destination Directory';
	} //end if
	//--
	return ''; // OK
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function client_ident_private_key() {
	//--
	return (string) self::get_visitor_signature().' [#] '.SMART_SOFTWARE_NAMESPACE.'*'.SMART_FRAMEWORK_SECURITY_KEY.'.';
	//--
} //END FUNCTION
//================================================================


//================================================================
// generate a PRIVATE unique, very secure hash of the current user by IP and Browser Signature
// This key should never be exposed to the public, it is used to check signed data (which may be paired with visitor unique track id)
public static function unique_client_private_key() {
	//--
	return SmartHashCrypto::sha512('*'.self::client_ident_private_key());
	//--
} //END FUNCTION
//================================================================


//================================================================
// generate a PRIVATE unique, very secure hash of the current user by loginID, IP and Browser Signature
// This key should never be exposed to the public, it is used to check signed data (which may be paired with visitor unique track id)
public static function unique_auth_client_private_key() {
	//--
	return SmartHashCrypto::sha512('*'.SmartAuth::get_login_id().self::client_ident_private_key());
	//--
} //END FUNCTION
//================================================================


//================================================================
// this provide a stable but unique, non variable signature for the self browser robot
// this should be used just for identification purposes
// this should be never be trusted, the signature is public
// it must contain also the Robot keyword as it fails to identify as self-browser, at least to be identified as robot
// this signature should be used just for the internal browsing operations
public static function get_selfrobot_useragent_name() {
	//--
	return 'SmartFramework :: PHP/Robot :: SelfBrowser ('.php_uname().') @ '.SmartHashCrypto::sha1('SelfBrowser/PHP/'.php_uname().'/'.SMART_SOFTWARE_NAMESPACE.'/'.SMART_FRAMEWORK_SECURITY_KEY);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_visitor_signature() {
	//--
	return (string) 'Visitor // '.trim((string)$_SERVER['REMOTE_ADDR']).' ; '.trim((string)$_SERVER['HTTP_CLIENT_IP']).' ; '.trim((string)$_SERVER['HTTP_X_FORWARDED_FOR']).' :: '.trim((string)$_SERVER['HTTP_USER_AGENT']);
	//--
} //END FUNCTION
//================================================================


//================================================================
// This is the visitor UID calculated using the visitor private key and visitor public key
// This should be used just for tracking purposes and can be trusted as ~ 99% ONLY if the SMART_APP_VISITOR_COOKIE is defined ; if SMART_APP_VISITOR_COOKIE is not used then it may be trusted ~ 90%
public static function get_visitor_tracking_uid() {
	//--
	return (string) SmartHashCrypto::sha1('>'.SMART_SOFTWARE_NAMESPACE.'['.SMART_FRAMEWORK_SECURITY_KEY.']'.self::client_ident_private_key().'>'.SMART_APP_VISITOR_COOKIE);
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_encoding_charset() {
	//--
	return (string) SMART_FRAMEWORK_CHARSET;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_server_current_request_method() {
	//--
	return (string) strtoupper((string)trim((string)$_SERVER['REQUEST_METHOD'])); // string
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_server_current_protocol() {
	//--
	if(trim(strtolower((string)$_SERVER['HTTPS'])) == 'on') {
		$current_protocol = 'https://';
	} else {
		$current_protocol = 'http://';
	} //end if else
	//--
	return (string) $current_protocol;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: 443
public static function get_server_current_port() {
	//--
	return (string) trim((string)$_SERVER['SERVER_PORT']);
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: 127.0.0.1
public static function get_server_current_ip() {
	//--
	return (string) trim((string)$_SERVER['SERVER_ADDR']);
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: localhost or IP
public static function get_server_current_domain_name() {
	//--
	return (string) trim((string)$_SERVER['SERVER_NAME']);
	//--
} //END FUNCTION
//================================================================


//================================================================
// get main domain without sub-domain
public static function get_server_current_basedomain_name() {
	//--
	$xout = (string) self::$cache['get_server_current_basedomain_name'];
	//--
	if((string)$xout == '') {
		//--
		$domain = (string) self::get_server_current_domain_name();
		//--
		if(preg_match('/^[0-9\.]+$/', $domain) OR (strpos($domain, ':') !== false)) { // if IPv4 or IPv6
			$xout = (string) $domain;
		} else { // assume is domain
			if(strpos($domain, '.') !== false) { // ex: subdomain.domain.ext or subdomain.domain
				$domain = (array) explode('.', (string)$domain);
				$domain = (array) array_reverse($domain);
				$xout = (string) $domain[1].'.'.$domain[0];
			} else { // ex: localhost
				$xout = (string) $domain;
			} //end if else
		} //end if
		//--
		self::$cache['get_server_current_basedomain_name'] = (string) $xout;
		//--
	} //end if
	//--
	return (string) $xout;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: /sites/test/script.php/page.html|path/to/seomething-else ; the path is decoded
public static function get_server_current_request_path() {
	//--
	return (string) trim((string)$_SERVER['PATH_INFO']);
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: /sites/test/script.php?param= | /page.html (rewrited to some-script.php?var=val&ofs=...) ; it includes the current path. but RAW (not decoded)
public static function get_server_current_request_uri() {
	//--
	return (string) trim((string)$_SERVER['REQUEST_URI']);
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: /sites/test/script.php
public static function get_server_current_full_script() {
	//--
	return (string) Smart::fix_path_separator(trim((string)$_SERVER['SCRIPT_NAME'])); // Fix: on Windows it can contain \ instead of /
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: /sites/test/
public static function get_server_current_path() {
	//--
	$xout = (string) self::$cache['get_server_current_path'];
	//--
	if((string)$xout == '') {
		//--
		$current_path = '/'; // this is default
		if((string)self::get_server_current_full_script() != '') {
			$current_path = Smart::dir_name(self::get_server_current_full_script()); // may return '' or .
			if(((string)$current_path == '') OR ((string)$current_path == '.') OR ((string)$current_path == '//')) {
				$current_path = '/';
			} //end if
			if(substr($current_path, 0, 1) != '/') {
				$current_path = '/'.$current_path;
			} //end if
			if(substr($current_path, -1, 1) != '/') {
				$current_path .= '/';
			} //end if
		} //end if
		if((string)$current_path == '') {
			Smart::raise_error('Cannot Determine Current WebServer URL / Path', 'Invalid WebServer URL / Path');
			return '';
		} //end if
		//--
		$xout = (string) $current_path;
		//--
		self::$cache['get_server_current_path'] = (string) $xout;
		//--
	} //end if
	//--
	return (string) $xout;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: http(s)://domain(:port)/sites/test/
public static function get_server_current_url() {
	//--
	$xout = (string) self::$cache['get_server_current_url'];
	//--
	if((string)$xout == '') {
		//--
		$current_port = self::get_server_current_port();
		if((string)$current_port == '') {
			Smart::raise_error('Cannot Determine Current WebServer URL / Port', 'Invalid WebServer URL / Port');
			return '';
		} //end if
		$used_port = ':'.$current_port;
		//--
		$current_domain = self::get_server_current_domain_name();
		if((string)$current_domain == '') {
			Smart::raise_error('Cannot Determine Current WebServer URL / Domain', 'Invalid WebServer URL / Domain');
			return '';
		} //end if
		//--
		$current_prefix = 'http://';
		if((string)$current_port == '80') {
			$used_port = ''; // avoid specify port if default, 80 on http://
		} //end if
		if((string)self::get_server_current_protocol() == 'https://') {
			$current_prefix = 'https://';
			if((string)$current_port == '443') {
				$used_port = ''; // avoid specify port if default, 443 on https://
			} //end if
		} //end if
		//--
		$xout = (string) $current_prefix.$current_domain.$used_port.self::get_server_current_path();
		//--
		self::$cache['get_server_current_url'] = (string) $xout;
		//--
	} //end if
	//--
	return (string) $xout;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: script.php
public static function get_server_current_script() {
	//--
	$xout = (string) self::$cache['get_server_current_script'];
	//--
	if((string)$xout == '') {
		//--
		$current_script = '';
		if((string)self::get_server_current_full_script() != '') {
			$current_script = basename(self::get_server_current_full_script());
		} //end if
		if((string)$current_script == '') {
			Smart::raise_error('Cannot Determine Current WebServer Script', 'Invalid Current WebServer Script');
			return '';
		} //end if
		//--
		$xout = (string) $current_script;
		//--
		self::$cache['get_server_current_script'] = (string) $xout;
		//--
	} //end if
	//--
	return (string) $xout;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Ex: ?param1=one&param2=two
public static function get_server_current_queryurl() {
	//--
	$url_query = (string) trim((string)$_SERVER['QUERY_STRING']); // will get without the prefix '?' as: page=one&subpage=two
	//--
	if((string)$url_query == '') {
		$url_query = '?'; // at least '?' is expected even if the url query is empty
	} elseif((string)substr($url_query, 0, 1) != '?') { // add '?' prefix if missing, this is required for building url with suffixes, all current url builders rely on assuming there will be a '?' as prefix
		$url_query = '?'.$url_query;
	} //end if else
	//--
	return $url_query;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_webserver_version() {
	//--
	$xout = (array) self::$cache['get_webserver_version'];
	//--
	if(Smart::array_size($xout) <= 0) {
		//--
		$tmp_version_arr = (array) explode('/', (string)$_SERVER['SERVER_SOFTWARE']);
		$tmp_name_str = trim($tmp_version_arr[0]);
		$tmp_out = trim($tmp_version_arr[1]);
		$tmp_version_arr = (array) explode(' ', (string)$tmp_out);
		$tmp_version_str = trim($tmp_version_arr[0]);
		//--
		$xout = array(
			'name' => (string) $tmp_name_str,
			'version' => (string) $tmp_version_str
		);
		//--
		self::$cache['get_webserver_version'] = (array) $xout;
		//--
	} //end if
	//--
	return (array) $xout;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_server_os() {
	//--
	$out = (string) self::$cache['get_server_os'];
	//--
	if((string)$out == '') {
		//--
		$the_lower_os = (string) strtolower((string)$_SERVER['SERVER_SOFTWARE']);
		//--
		switch(strtolower((string)PHP_OS)) { // {{{SYNC-SRV-OS-ID}}}
			case 'mac':
			case 'macos':
			case 'darwin':
			case 'macosx':
				$out = 'macosx'; // MacOSX
				break;
			case 'windows':
			case 'winnt':
			case 'win32':
			case 'win64':
				$out = 'winnt'; // Windows NT
				break;
			case 'bsdos':
			case 'bsd': // Generic BSD OS
				$out = 'bsd-os';
				break;
			case 'netbsd': //NetBSD
				$out = 'netbsd';
				break;
			case 'openbsd': // OpenBSD
				$out = 'openbsd';
				break;
			case 'freebsd': // FreeBSD
				$out = 'freebsd';
				break;
			case 'dragonfly':
			case 'dragonflybsd':
				$out = 'dragonfly'; // DragonFlyBSD
				break;
			case 'linux':
				$out = 'linux'; // Generic Linux
				//-
				if(strpos($the_lower_os, '(debian') !== false) {
					$out = 'debian';
				} elseif(strpos($the_lower_os, '(ubuntu') !== false) {
					$out = 'ubuntu';
				} elseif(strpos($the_lower_os, '(mint') !== false) {
					$out = 'mint';
				} elseif(strpos($the_lower_os, '(redhat') !== false) {
					$out = 'redhat';
				} elseif(strpos($the_lower_os, '(centos') !== false) {
					$out = 'centos';
				} elseif(strpos($the_lower_os, '(fedora') !== false) {
					$out = 'fedora';
				} elseif(strpos($the_lower_os, '(suse') !== false) {
					$out = 'suse';
				} //end if else
				//-
				break;
			case 'opensolaris':
			case 'openindiana':
			case 'nexenta':
			case 'solaris':
			case 'sunos':
			case 'sun':
				$out = 'solaris'; // SOLARIS
				break;
			default:
				// UNKNOWN
				$out = strtoupper('[?] '.PHP_OS);
		} //end switch
		//--
		self::$cache['get_server_os'] = (string) $out;
		//--
	} //end if
	//--
	return (string) $out;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function check_ip_in_range($lower_range_ip_address, $upper_range_ip_address, $needle_ip_address) {
		//-- Get the numeric reprisentation of the IP Address with IP2long
		$min 	= ip2long($lower_range_ip_address);
		$max 	= ip2long($upper_range_ip_address);
		$needle = ip2long($needle_ip_address);
		//-- Then it's as simple as checking whether the needle falls between the lower and upper ranges
		return (($needle >= $min) AND ($needle <= $max));
		//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_ip_client() {
	//--
	$xout = (string) self::$cache['get_ip_client'];
	//--
	if((string)$xout == '') {
		//--
		$ip = '';
		//--
		$ip = SmartValidator::validate_filter_ip_address((string)$_SERVER['REMOTE_ADDR']); // no forward or client IP should be considered here as they can be a security risk as they are untrusted !!
		//--
		if((string)$ip == '') {
			Smart::raise_error('Cannot Determine Current Client IP Address', 'Invalid Client IP Address');
			return '';
		} //end if
		//--
		$xout = (string) $ip;
		//--
		self::$cache['get_ip_client'] = (string) $xout;
		//--
	} //end if
	//--
	return (string) $xout;
	//--
} //END FUNCTION
//================================================================


//================================================================
public static function get_ip_proxyclient() {
	//--
	$xout = (string) self::$cache['get_ip_proxyclient'];
	//--
	if((string)$xout == '') {
		//--
		$proxy = '';
		//--
		if((string)$proxy == '') {
			if((string)$_SERVER['HTTP_CLIENT_IP'] != '') {
				$proxy = (string) self::_iplist_get_last_address((string)$_SERVER['HTTP_CLIENT_IP']);
			} //end if
		} //end if
		//--
		if((string)$proxy == '') {
			if((string)$_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
				$proxy = (string) self::_iplist_get_last_address((string)$_SERVER['HTTP_X_FORWARDED_FOR']);
			} //end if
		} //end if
		//--
		if((string)$proxy == '') {
			$proxy = ' '; // use a space for cache to avoid re-running it
		} //end if
		//--
		$xout = (string) $proxy;
		//--
		self::$cache['get_ip_proxyclient'] = (string) $xout;
		//--
	} //end if
	//--
	return (string) $xout;
	//--
} //END FUNCTION
//================================================================


//================================================================
// GET OS, BROWSER, IP :: ACCESS LOG
// This will be used only once
public static function get_os_browser_ip($y_mode='') {
	//--
	$xout = (array) self::$cache['get_os_browser_ip'];
	//--
	if(Smart::array_size($xout) <= 0) {
		//--
		$wp_browser = '[?]';
		$wp_class = '[?]';
		$wp_os = '[?]';
		$wp_ip = '[?]';
		$wp_px = '[?]';
		$wp_mb = 'no'; // by default is not mobile
		//--
		$the_lower_signature = (string) strtolower((string)$_SERVER['HTTP_USER_AGENT']);
		//--
		// {{{SYNC-CLI-BW-ID}}}
		//-- identify browser ; real supported browser classes: gk, ie, wk ; other classes as: xy are not trusted ... ;  tx / rb are text/robots browsers
		if((strpos($the_lower_signature, 'firefox') !== false) OR (strpos($the_lower_signature, 'iceweasel') !== false) OR (strpos($the_lower_signature, ' fxios/') !== false)) {
			$wp_browser = 'fox'; // firefox
			$wp_class = 'gk'; // gecko class
		} elseif(strpos($the_lower_signature, 'seamonkey') !== false) {
			$wp_browser = 'smk'; // mozilla seamonkey
			$wp_class = 'gk'; // gecko class
		} elseif(strpos($the_lower_signature, ' edge/') !== false) {
			$wp_browser = 'iee'; //microsoft edge
			$wp_class = 'ie'; // trident class
		} elseif((strpos($the_lower_signature, ' msie ') !== false) OR (strpos($the_lower_signature, ' trident/') !== false)) {
			$wp_browser = 'iex'; // internet explorer (must be before any stealth browsers as ex.: opera)
			$wp_class = 'ie'; // trident class
		} elseif((strpos($the_lower_signature, 'opera') !== false) OR (strpos($the_lower_signature, ' opr/') !== false) OR (strpos($the_lower_signature, ' oupeng/') !== false) OR (strpos($the_lower_signature, ' opios/') !== false)) {
			$wp_browser = 'opr'; // opera
			$wp_class = 'wk'; // webkit class
		} elseif((strpos($the_lower_signature, 'chrome') !== false) OR (strpos($the_lower_signature, 'chromium') !== false) OR (strpos($the_lower_signature, 'iridium') !== false) OR (strpos($the_lower_signature, ' crios/') !== false)) {
			$wp_browser = 'crm'; // chrome
			$wp_class = 'wk'; // webkit class
		} elseif(strpos($the_lower_signature, 'epiphany') !== false) { // must be detected before safari because includes safari signature
			$wp_browser = 'eph'; // epiphany (gnome)
			$wp_class = 'xy'; // various class
		} elseif(strpos($the_lower_signature, 'konqueror') !== false) { // must be detected before safari because includes safari signature
			$wp_browser = 'knq'; // konqueror (kde)
			$wp_class = 'xy'; // various class
		} elseif((strpos($the_lower_signature, 'safari') !== false) OR (strpos($the_lower_signature, 'applewebkit') !== false)) {
			$wp_browser = 'sfr'; // safari
			$wp_class = 'wk'; // webkit class
		} elseif(strpos($the_lower_signature, 'webkit') !== false) { // general webkit signature, untrusted
			$wp_browser = 'wkt'; // webkit
			$wp_class = 'xy'; // various class
		} elseif((strpos($the_lower_signature, 'mozilla') !== false) OR (strpos($the_lower_signature, 'gecko') !== false)) { // general mozilla signature, untrusted
			$wp_browser = 'moz'; // mozilla derivates
			$wp_class = 'xy'; // various class
		} elseif(strpos($the_lower_signature, 'netsurf/') !== false) { // it have just a simple signature
			$wp_browser = 'nsf'; // netsurf
			$wp_class = 'xy'; // various class
		} elseif((strpos($the_lower_signature, 'lynx') !== false) OR (strpos($the_lower_signature, 'links') !== false)) {
			$wp_browser = 'lyx'; // lynx / links (text browser)
			$wp_class = 'tx'; // text class
		} elseif(defined('SMART_FRAMEWORK_IDENT_ROBOTS')) {
			$robots = (array) Smart::list_to_array((string)SMART_FRAMEWORK_IDENT_ROBOTS, false);
			$imax = Smart::array_size($robots);
			for($i=0; $i<$imax; $i++) {
				if(strpos($the_lower_signature, (string)$robots[$i]) !== false) {
					$wp_browser = 'bot'; // Robot
					$wp_class = 'rb'; // bot class
					break;
				} //end if
			} //end for
		} //end if else
		//-- this is just for self-robot which name is always unique and impossible to guess ; this must override the rest of detections just in the case that someone adds it to the ident robots in init ...
		if((string)trim($the_lower_signature) == (string)strtolower(self::get_selfrobot_useragent_name())) {
			$wp_browser = '@s#';
			$wp_class = 'rb'; // bot class
		} //end if
		//--
		// {{{SYNC-CLI-OS-ID}}}
		//-- identify os
		if((strpos($the_lower_signature, 'windows') !== false) OR (strpos($the_lower_signature, 'winnt') !== false)) {
			$wp_os = 'win'; // ms windows
		} elseif((strpos($the_lower_signature, ' mac ') !== false) OR (strpos($the_lower_signature, 'macos') !== false) OR (strpos($the_lower_signature, 'os x') !== false) OR (strpos($the_lower_signature, 'osx') !== false) OR (strpos($the_lower_signature, 'darwin') !== false)) {
			$wp_os = 'mac'; // apple mac / osx / darwin
		} elseif(strpos($the_lower_signature, 'linux') !== false) {
			$wp_os = 'lnx'; // *linux
		} elseif((strpos($the_lower_signature, 'netbsd') !== false) OR (strpos($the_lower_signature, 'openbsd') !== false) OR (strpos($the_lower_signature, 'freebsd') !== false) OR (strpos($the_lower_signature, 'dragonfly') !== false) OR (strpos($the_lower_signature, ' bsd ') !== false)) {
			$wp_os = 'bsd'; // *bsd
		} elseif((strpos($the_lower_signature, 'solaris') !== false) OR (strpos($the_lower_signature, 'sunos') !== false) OR (strpos($the_lower_signature, 'nexenta') !== false) OR (strpos($the_lower_signature, 'openindiana') !== false)) {
			$wp_os = 'sun'; // sun solaris incl clones
		} //end if
		//-- identify mobile os
		if((strpos($the_lower_signature, 'iphone') !== false) OR (strpos($the_lower_signature, 'ipad') !== false) OR (strpos($the_lower_signature, ' opios/') !== false)) {
			$wp_os = 'ios'; // apple mobile ios: iphone / ipad / ipod
			$wp_mb = 'yes';
		} elseif((strpos($the_lower_signature, 'android') !== false) OR (strpos($the_lower_signature, ' opr/') !== false)) {
			$wp_os = 'and'; // google android
			$wp_mb = 'yes';
		} elseif((strpos($the_lower_signature, 'windows ce') !== false) OR (strpos($the_lower_signature, 'windows phone') !== false) OR (strpos($the_lower_signature, 'windows mobile') !== false) OR (strpos($the_lower_signature, 'windows rt') !== false)) {
			$wp_os = 'wmo'; // ms windows mobile
			$wp_mb = 'yes';
		} elseif((strpos($the_lower_signature, 'linux mobile') !== false) OR (strpos($the_lower_signature, 'ubuntu; mobile') !== false) OR (strpos($the_lower_signature, 'tizen') !== false) OR (strpos($the_lower_signature, 'blackberry') !== false)) {
			$wp_os = 'lxm'; // linux mobile
			$wp_mb = 'yes';
		} //end if
		//-- identify ip addr
		$wp_ip = self::get_ip_client();
		//-- identify proxy ip if any
		$wp_px = self::get_ip_proxyclient();
		//-- out data arr
		$xout = array(
			'signature'	=> (string) $_SERVER['HTTP_USER_AGENT'],
			'mobile' 	=> (string) $wp_mb,
			'os' 		=> (string) $wp_os,
			'bw' 		=> (string) $wp_browser,
			'bc' 		=> (string) $wp_class,
			'ip' 		=> (string) $wp_ip,
			'px' 		=> (string) $wp_px
		);
		//--
		self::$cache['get_os_browser_ip'] = (array) $xout;
		//--
	} //end if
	//--
	if((string)$y_mode != '') {
		return (string) $xout[(string)$y_mode];
	} else {
		return (array) $xout;
	} //end if else
	//--
} //END FUNCTION
//================================================================


//##### NON-PUBLICS


//================================================================
/**
 * Function: Run Proc Cmd
 * This method is using the proc_open() which provides a much greater degree of control over the program execution
 *
 * @access 		private
 * @internal
 *
 * @param $cmd 		STRING 			:: the command to run ; must be escaped using escapeshellcmd() and arguments using escapeshellarg()
 * @param $inargs 	ARRAY / NULL 	:: *Optional*, Default NULL ; the array containing the input for the STDIN
 * @param $cwd 		STRING / NULL 	:: *Optional*, Default 'tmp/cache/run-proc-cmd' ; Use NULL to use the the working dir of the current PHP process (not recommended) ; A path for a directory to run the process in ; If not null, if path does not exists will be created
 * @param $env 		ARRAY / NULL 	:: *Optional*, default $env ; the array with environment variables ; If NULL will use the same environment as the current PHP process
 *
 * @return ARRAY					:: [ stdout, stderr, exitcode ]
 *
 */
public static function run_proc_cmd($cmd, $inargs=null, $cwd='tmp/cache/run-proc-cmd', $env=null) {

	//-- initialize
	$descriptorspec = [
		0 => [ 'pipe', 'r' ], // stdin
		1 => [ 'pipe', 'w' ], // stdout
		2 => [ 'pipe', 'w' ]  // stderr
	];
	//--
	$output = array();
	$rderr = false;
	$pipes = array();
	//--

	//--
	$outarr = [
		'stdout' 	=> '',
		'stderr' 	=> '',
		'exitcode' 	=> -999
	];
	//--

	//-- exec
	if((string)$cwd != '') {
		if(!SmartFileSystem::path_exists((string)$cwd)) {
			SmartFileSystem::dir_create((string)$cwd, true); // recursive
		} //end if
		if(!SmartFileSystem::is_type_dir((string)$cwd)) {
			//--
			Smart::raise_error(__METHOD__.'(): The Proc Open CWD Path: ['.$cwd.'] cannot be created and is not available !', 'See Error Log for more details ...');
			//--
			$outarr['stdout'] 	= '';
			$outarr['stderr'] 	= '';
			$outarr['exitcode'] = -998;
			//--
			return (array) $outarr;
			//--
		} //end if
	} else {
		$cwd = null;
	} //end if
	$resource = proc_open((string)$cmd, (array)$descriptorspec, $pipes, $cwd, $env);
	//--
	if(!is_resource($resource)) {
		//--
		$outarr['stdout'] 	= '';
		$outarr['stderr'] 	= 'Could not open Process / Not Resource';
		$outarr['exitcode'] = -997;
		//--
		return (array) $outarr;
		//--
	} //end if
	//--

	//-- write to stdin
	if(is_array($inargs)) {
		if(count($inargs) > 0) {
			foreach($inargs as $key => $val) {
				fwrite($pipes[0], (string)$val);
			} //end foreach
		} //end if
	} //end if
	//--

	//-- read stdout
	$output = (string) stream_get_contents($pipes[1]); // don't convert charset as it may break binary files
	//--

	//-- read stderr (here may be errors or warnings)
	$errors = (string) stream_get_contents($pipes[2]); // don't convert charset as it may break binary files
	//--

	//--
	fclose($pipes[0]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	//--
	$exitcode = proc_close($resource);
	//--

	//--
	$outarr['stdout'] 	= (string) $output;
	$outarr['stderr'] 	= (string) $errors;
	$outarr['exitcode'] = $exitcode; // don't make it INT !!!
	//--
	return (array) $outarr;
	//--

} //END FUNCTION
//================================================================


//##### PRIVATES


//================================================================
// gets the IP from composed headers
// Note on Proxy and IPs:
// Format: 'X-Forwarded-For: client, proxy1, proxy2'
// 15 characters for IPv4 (xxx.xxx.xxx.xxx format, 12+3 separators)
// 39 characters (32 + 7 separators) for IPv6
private static function _iplist_get_first_address($ip) {
	//--
	if((string)$ip == '') {
		return '';
	} //end if
	//--
	if(strpos((string)$ip, ',') !== false) { // if we detect many IPs in a header
		//--
		$arr = explode(',', (string)$ip);
		$ip = ''; // we clear it
		//--
		$imax = Smart::array_size($arr);
		for($i=0; $i<$imax; $i++) { // loop forward
			//--
			$tmp_ip = (string) SmartValidator::validate_filter_ip_address(trim((string)$arr[$i])); // this returns empty if no valid IP
			//--
			if((strlen($tmp_ip) >= 7) AND (strlen($tmp_ip) <= 39)) { // valid IP must be in this range
				$ip = (string) $tmp_ip;
				break;
			} //end if
			//--
		} //end for
		//--
	} else {
		//--
		$ip = (string) SmartValidator::validate_filter_ip_address((string)$ip);
		//--
	} //end if
	//--
	return (string) $ip;
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function _iplist_get_last_address($ip) {
	//--
	if((string)$ip == '') {
		return '';
	} //end if
	//--
	if(strpos((string)$ip, ',') !== false) { // if we detect many IPs in a header
		//--
		$arr = explode(',', (string)$ip);
		$ip = ''; // we clear it
		//--
		$imax = Smart::array_size($arr);
		if($imax > 1) {
			//--
			for($i=$imax; $i>0; $i--) { // loop backward
				//--
				$tmp_ip = (string) SmartValidator::validate_filter_ip_address(trim((string)$arr[$i])); // this returns empty if no valid IP
				//--
				if((strlen($tmp_ip) >= 7) AND (strlen($tmp_ip) <= 39)) { // valid IP must be in this range
					$ip = (string) $tmp_ip;
					break;
				} //end if
				//--
			} //end for
			//--
		} //end if
		//--
	} else {
		//--
		$ip = (string) SmartValidator::validate_filter_ip_address((string)$ip);
		//--
	} //end if
	//--
	return (string) $ip;
	//--
} //END FUNCTION
//================================================================


//##### DEBUG ONLY


//================================================================
/**
 *
 * @access 		private
 * @internal
 *
 */
public static function registerInternalCacheToDebugLog() {
	//--
	if(SmartFrameworkRuntime::ifInternalDebug()) {
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('extra', '***SMART-CLASSES:INTERNAL-CACHE***', [
				'title' => 'SmartUtils // Internal Cache',
				'data' => 'Dump:'."\n".print_r(self::$cache,1)
			]);
		} //end if
	} //end if
	//--
} //END FUNCTION
//================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>