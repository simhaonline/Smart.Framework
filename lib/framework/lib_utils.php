<?php
// [LIB - Smart.Framework / Utils]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
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
	die('ERROR: The PHP ZLIB Extension (gzdeflate/gzinflate) is required for Smart.Framework / Lib Utils');
} //end if
//--

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartUtils - provides various utility functions for Smart.Framework
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartValidator, SmartHashCrypto, SmartAuth, SmartFileSysUtils, SmartFileSystem, SmartHttpClient
 * @version 	v.20191103
 * @package 	@Core:Extra
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
			Smart::log_warning('Smart.Framework Utils / Data Archive :: ZLib Deflate ERROR ! ...');
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
			Smart::log_warning('Smart.Framework Utils / Data Archive :: ZLib Data Ratio is zero ! ...');
			return '';
		} //end if
		if($ratio > 32768) { // check for this bug in ZLib {{{SYNC-GZ-ARCHIVE-ERR-CHECK}}}
			Smart::log_warning('Smart.Framework Utils / Data Archive :: ZLib Data Ratio is higher than 32768 ! ...');
			return '';
		} //end if
		//--
		$y_str = ''; // free mem
		//-- add signature
		$out = (string) trim((string)base64_encode((string)$out))."\n".'PHP.SF.151129/B64.ZLibRaw.HEX';
		//-- test unarchive
		$unarch_checksum = SmartHashCrypto::sha1(self::data_unarchive($out));
		if((string)$chksum != (string)$unarch_checksum) { // check: if there is a serious bug with ZLib or PHP we can't tolerate, so test decompress here !!
			Smart::log_warning('Smart.Framework Utils / Data Archive :: Data Encode Check Failed ! ...');
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
			Smart::log_notice('Smart.Framework // Data Unarchive // Invalid Package Signature: '.$arr[1]);
		} //end if
		//-- decode it (at least try)
		$out = @base64_decode((string)$arr[0]); // NON-STRICT ! don't make it string, may return false
		if(($out === false) OR ((string)trim((string)$out) == '')) { // use trim, the deflated string can't contain only spaces
			Smart::log_warning('Smart.Framework // Data Unarchive // Invalid B64 Data for packet with signature: '.$arr[1]);
			return '';
		} //end if
		$out = @gzinflate($out);
		if(($out === false) OR ((string)trim((string)$out) == '')) {
			Smart::log_warning('Smart.Framework // Data Unarchive // Invalid Zlib GzInflate Data for packet with signature: '.$arr[1]);
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
				Smart::log_warning('Smart.Framework // Data Unarchive // Invalid HEX Data for packet with signature: '.$arr[1]);
				return '';
			} //end if
			$arr[1] = (string) trim((string)$arr[1]); // the checksum
			if(SmartHashCrypto::sha1($arr[0]) != (string)$arr[1]) {
				Smart::log_warning('Smart.Framework // Data Unarchive // Invalid Packet, Checksum FAILED :: A checksum was found but is invalid: '.$arr[1]);
				return '';
			} //end if
			//--
			$out = (string) $arr[0];
			$arr = array();
			//--
		} else {
			//--
			Smart::log_warning('Smart.Framework // Data Unarchive // Invalid Packet, no Checksum :: This can occur if decompression failed or an invalid packet has been assigned ...');
			return '';
			//--
		} //end if
		//--
		return (string) $out;
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
				//--
				$out .= $spaces;
				//--
				if(is_array($val)) {
					//--
					$out .= "\t".$key.' => '.self::pretty_print_var($val, $indent);
					//--
				} else {
					//--
					if(is_object($val)) { // {{{SYNC-UTILS-PRETTY-PRINT-VAR}}}
						$val = '!OBJECT!';
					} elseif($val === null) {
						$val = 'NULL';
					} elseif($val === false) {
						$val = 'FALSE';
					} elseif($val === true) {
						$val = 'TRUE';
					} elseif(!is_numeric($val)) {
						$val = '`'.$val.'`';
					} //end if else
					//--
					$out .= "\t".$key.' => '.$val;
					//--
				} //end if else
				//--
				$out .= "\n";
				//--
			} //end foreach
			//--
			$out .= $spaces.']';
			//--
		} else {
			//--
			$val = $y_var; // mixed
			//--
			if(is_object($val)) { // {{{SYNC-UTILS-PRETTY-PRINT-VAR}}}
				$val = '!OBJECT!';
			} elseif($val === null) {
				$val = 'NULL';
			} elseif($val === false) {
				$val = 'FALSE';
			} elseif($val === true) {
				$val = 'TRUE';
			} elseif(!is_numeric($val)) {
				$val = '`'.$val.'`';
			} //end if else
			//--
			$out = (string) $val;
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
	// extract HTML title (must not exceed 128 characters ; recommended is max 65) ; no changes
	public static function extract_title($ytxt, $y_limit=65, $clear_numbers=false) {
		//--
		$ytxt = (string) Smart::striptags((string)$ytxt, 'no'); // will do strip tags
		$ytxt = (string) Smart::normalize_spaces((string)$ytxt); // will do normalize spaces
		//--
		if($clear_numbers === true) {
			$ytxt = (string) self::cleanup_numbers_from_text((string)$ytxt); // do after strip tags to avoid break html
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
		if($clear_numbers === true) {
			$ytxt = (string) self::cleanup_numbers_from_text((string)$ytxt); // do after strip tags to avoid break html
		} //end if
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
			//--
			$tmp_word = (string) trim((string)str_replace(['`', '~', '!', '@', '#', '$', '%', '^', '*', '(', ')', '_', '+', '=', '[', ']', '{', '}', '|', '\\', '/', '?', '<', '>', ',', ':', ';', '"', "'"], ' ', (string)$arr[$i]));
			$tmp_word = (string) preg_replace("/(\.)\\1+/", '.', $tmp_word); // suppress multiple . dots and replace with single dot
			$tmp_word = (string) preg_replace("/(\-)\\1+/", '-', $tmp_word); // suppress multiple - minus signs and replace with single minus sign
			//--
			if($clear_numbers === true) {
				$tmp_word = (string) self::cleanup_numbers_from_text((string)$tmp_word); // do on each keyword after all processing
			} //end if
			//--
			$tmp_word = (string) trim((string)$tmp_word);
			//--
			if((string)$tmp_word != '') {
				$out .= $tmp_word.', ';
				$cnt++;
			} //end if
			//--
			if($cnt >= $y_count) {
				break;
			} //end if
			//--
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
	public static function decode_download_link($y_encrypted_link) {
		//--
		return (string) trim((string)SmartUtils::crypto_decrypt(
			(string) $y_encrypted_link,
			'Smart.Framework//DownloadLink'.SMART_FRAMEWORK_SECURITY_KEY // {{{SYNC-DOWNLOAD-LINK-CRYPT-KEY}}}
		));
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
			trim((string)$crrtime)."\n". 									// set the current time
			trim((string)$y_file)."\n". 									// the file path
			trim((string)$access_key)."\n". 								// access key based on UniqueID cookie
			trim((string)$unique_key)."\n".									// unique key based on: User-Agent and IP
			'-'."\n",														// self robot browser UserAgentName/ID key (does not apply here)
			'Smart.Framework//DownloadLink'.SMART_FRAMEWORK_SECURITY_KEY 	// {{{SYNC-DOWNLOAD-LINK-CRYPT-KEY}}}
		);
		//--
		return (string) Smart::escape_url((string)trim((string)$safe_download_link));
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * GET/SET a cache file on the file system ; Cached File will be saved in 'tmp/cache/{prefix}/.../'
	 * When SET will return the content back
	 *
	 * @param STRING 	$y_cache_file_extension		:: File Extension (example: '.ext')
	 * @param STRING 	$y_cache_prefix				:: prefix dir (at least 3 chars ; max 64 chars) ended by slash (Example: 'prefix')
	 * @param STRING 	$y_cache_unique_id			:: The Cache ID Unique Key (Ex: some.unique.key)
	 * @param STRING	$y_content					:: default is FALSE to GET the cached content if exists ; if this is a non-empty string will SET the content into the cache and will return it back
	 * @param INT 		$y_cache_expire				:: 0=never ; (>0)=seconds
	 * @param ENUM 		$y_encrypted				:: yes/no to encrypt the file content
	 * @return STRING								:: cached contents or empty string
	 */
	public static function load_cached_file_content($y_cache_file_extension, $y_cache_prefix, $y_cache_unique_id, $y_set_content=false, $y_cache_expire=0, $y_encrypted='no') {

		//--
		$y_cache_unique_id = (string) $y_cache_unique_id;
		//--
		if((string)$y_cache_unique_id == '') {
			Smart::log_warning('Utils // Load From Cache ... Empty URL ...');
			return '';
		} //end if
		//--
		$y_cache_file_extension = Smart::safe_validname($y_cache_file_extension);
		//--
		$y_cache_expire = Smart::format_number_int($y_cache_expire, '+');
		//--
		$y_cache_prefix = (string) Smart::safe_varname($y_cache_prefix);
		//--
		if((strlen($y_cache_prefix) >= 3) AND (strlen($y_cache_prefix) <= 64)) {
			//--
			$y_cache_prefix = SmartFileSysUtils::add_dir_last_slash($y_cache_prefix); // fix trailing slash
			//--
		} else {
			//--
			Smart::log_warning('Utils // Load From Cache ... Invalid Cache Prefix ...');
			$y_cache_prefix = 'default/';
			//--
		} //end if
		//--

		//--
		$unique_id = (string) SmartHashCrypto::sha1('@@::Smart.Framework::Content::Cache@@'.$y_cache_unique_id);
		//--
		$dir = 'tmp/cache/'.$y_cache_prefix.SmartFileSysUtils::prefixed_sha1_path($unique_id);
		SmartFileSysUtils::raise_error_if_unsafe_path($dir);
		//--
		$file = (string) $dir.$unique_id.$y_cache_file_extension;
		SmartFileSysUtils::raise_error_if_unsafe_path($file);
		//--
		if(!SmartFileSystem::is_type_dir($dir)) {
			SmartFileSystem::dir_create($dir, true); // recursive create
			if(!SmartFileSystem::is_type_dir($dir)) {
				Smart::log_warning('Utils // Load From Cache ... Cannot Create Directory Structure: '.$dir);
				return '';
			} //end if
		} // end if
		//--
		/* avoid this to avoid overload Inodes !! ('tmp/cache/' is already protected)
		$protect_file = $dir.'index.html';
		if(!SmartFileSystem::is_type_file($protect_file)) {
			SmartFileSystem::write($protect_file, '');
		} //end if
		*/
		//--

		//-- will go through this only if cache expired or no cache
		if((!SmartFileSystem::is_type_file($file)) OR ((SmartFileSystem::is_type_file($file)) AND ($y_cache_expire > 0) AND ((SmartFileSystem::get_file_mtime($file) + $y_cache_expire) < time()))) {
			//--
			//Smart::log_notice('MUST Resave to cache ... '.$y_cache_unique_id);
			//-- write to cache if not empty (set)
			if((string)$y_set_content != '') { // If Content Have been Set
				//--
				//Smart::log_notice('Resave to cache ... '.$y_cache_unique_id);
				//-- if required, apply encryption
				if((string)$y_encrypted == 'yes') {
					$y_set_content = (string) self::crypto_blowfish_encrypt($y_set_content);
				} //end if
				//--
				SmartFileSystem::write($file, $y_set_content); // save file to cache (safe write is controlled via locks)
				//--
			} else {
				//--
				$y_set_content = ''; // expired content
				//--
				// do not delete file in multi concurrency, simply rewrite ...
				//--
			} //end if
			//--
			$out = (string) $y_set_content;
			$y_set_content = ''; // free mem
			//--
		} else {
			//--
			$out = (string) SmartFileSystem::read($file); // ccahe valid, read from cache
			//Smart::log_notice(__METHOD__.'() read FS-Cached Content from: '.$file.' @ '.$out);
			//--
		} //end if
		//--
		if((string)$y_encrypted == 'yes') {
			if((string)$out != '') {
				$out = (string) self::crypto_blowfish_decrypt($out);
			} //end if
		} //end if
		//--

		//--
		return (string) $out;
		//--

	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Reads and return one Uploaded File
	 *
	 * @param STRING 	$var_name					:: The HTML Variable Name
	 * @param INTEGER 	$var_index					:: The HTML Variable Index: -1 for single file uploads ; 0..n for multi-file uploads ; DEFAULT is -1
	 * @param INTEGER 	$max_size					:: The max file size in bytes that would be accepted ; set to zero for allow maximum size supported by PHP via INI settings ; DEFAULT is zero
	 * @param STRING	$allowed_extensions			:: The list of allowed file extensions ; Default is '' ; Example to restrict to several extensions: '<ext1>,<ext2>,...<ext100>,...' ; set to empty string to allow all extenstions supported via Smart.Framework INI: SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS / SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS
	 * @return ARRAY								:: array [ status => 'OK' | 'WARN' | 'ERR', 'message' => '' | 'WARN Message' | 'ERR Message', 'msg-code' => 0..n, 'filename' => '' | 'filename.ext', 'filetype' => '' | 'ext', 'filesize' => Bytes, 'filecontent' => '' | 'the Contents of the file ...' ]
	 */
	public static function read_uploaded_file($var_name, $var_index=-1, $max_size=0, $allowed_extensions='') {
		//-- {{{SYNC-HANDLE-F-UPLOADS}}}
		$var_name 	= (string) trim((string)$var_name);
		$var_index 	= (int)    $var_index; // can be negative or 0..n
		$max_size 	= (int)    Smart::format_number_int($max_size,'+');
		if($max_size <= 0) {
			$max_size = (int) SmartFileSysUtils::max_upload_size();
		} //end if
		$allowed_extensions = (string) trim((string)$allowed_extensions);
		//--
		$out = [
			'status' 		=> 'ERR', 			// 'OK' | 'WARN' | 'ERR'
			'message' 		=> '???', 			// '' | 'WARN Message' | 'ERR Message'
			'msg-code' 		=> -999, 			// Message Code
			'filename' 		=> '', 				// '' | 'filename.ext'
			'filetype' 		=> '', 				// '' | 'ext'
			'filesize' 		=> 0, 				// Bytes
			'filecontent' 	=> '' 				// '' | 'the Contents of the file ...'
		];
		//--
		if(Smart::array_size($_FILES) <= 0) {
			$out['status'] = 'WARN';
			$out['message'] = 'No files uploads detected ...';
			$out['msg-code'] = 1;
			return (array) $out;
		} //end if
		//--
		if((string)$var_name == '') {
			$out['status'] = 'ERR';
			$out['message'] = 'Invalid File VarName for Upload';
			$out['msg-code'] = 2;
			return (array) $out;
		} //end if
		//--
		if($var_index >= 0) {
			$the_upld_file_name 	= (string) $_FILES[$var_name]['name'][$var_index];
			$the_upld_file_tmpname 	= (string) $_FILES[$var_name]['tmp_name'][$var_index];
			$the_upld_file_error 	= (int)    $_FILES[$var_name]['error'][$var_index];
		} else {
			$the_upld_file_name 	= (string) $_FILES[$var_name]['name'];
			$the_upld_file_tmpname 	= (string) $_FILES[$var_name]['tmp_name'];
			$the_upld_file_error 	= (int)    $_FILES[$var_name]['error'];
		} //end if else
		//-- check uploaded tmp name
		$the_upld_file_tmpname = (string) trim((string)$the_upld_file_tmpname);
		if((string)$the_upld_file_tmpname == '') {
			$out['status'] = 'WARN';
			$out['message'] = 'No File Uploaded (Empty TMP Name) ...';
			$out['msg-code'] = 3;
			return (array) $out;
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
			$out['status'] = 'WARN';
			$out['message'] = 'Uploaded File Name is Invalid (Empty)';
			$out['msg-code'] = 4;
			return (array) $out;
		} //end if
		if(strlen((string)$the_upld_file_name) > 100) {
			$out['status'] = 'WARN';
			$out['message'] = 'Uploaded File Name is too long (oversize 100 characters): '.$the_upld_file_name;
			$out['msg-code'] = 5;
			return (array) $out;
		} //end if
		if(!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$the_upld_file_name)) {
			$out['status'] = 'WARN';
			$out['message'] = 'Uploaded File Name is Invalid (not Safe): '.$the_upld_file_name;
			$out['msg-code'] = 6;
			return (array) $out;
		} //end if
		//-- protect against dot files .*
		if(substr((string)$the_upld_file_name, 0, 1) == '.') {
			$out['status'] = 'WARN';
			$out['message'] = 'Uploaded File Name is Invalid (Dot .Files are not allowed for safety): '.$the_upld_file_name;
			$out['msg-code'] = 7;
			return (array) $out;
		} //end if
		//--
		$tmp_fext = (string) strtolower((string)SmartFileSysUtils::get_file_extension_from_path((string)$the_upld_file_name)); // get the extension
		//-- {{{SYNC-CHK-ALLOWED-DENIED-EXT}}}
		if((string)$allowed_extensions != '') {
			if(stripos((string)$allowed_extensions, '<'.$tmp_fext.'>') === false) {
				$out['status'] = 'WARN';
				$out['message'] = 'Upload Failed: The uploaded file extension is not in the current custom allowed extensions list for file: '.$the_upld_file_name;
				$out['msg-code'] = 8;
				return (array) $out;
			} //end if
		} else {
			if(defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) {
				if(stripos((string)SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') === false) {
					$out['status'] = 'WARN';
					$out['message'] = 'Upload Failed: The uploaded file extension is not in the current allowed extensions list configuration for file: '.$the_upld_file_name;
					$out['msg-code'] = 9;
					return (array) $out;
				} //end if
			} //end if
			if((!defined('SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS')) OR (stripos((string)SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') !== false)) {
				$out['status'] = 'WARN';
				$out['message'] = 'Upload Failed: The uploaded file extension is denied by the current configuration for file: '.$the_upld_file_name;
				$out['msg-code'] = 10;
				return (array) $out;
			} //end if
		} //end if else
		//-- check for upload errors
		$up_err = '';
		$up_code = 0;
		switch((int)$the_upld_file_error) {
			case UPLOAD_ERR_OK:
				// OK, no error
				break;
			case UPLOAD_ERR_INI_SIZE:
				$up_code = 101;
				$up_err = 'UPLOAD ERROR: The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$up_code = 102;
				$up_err = 'UPLOAD ERROR: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case UPLOAD_ERR_PARTIAL:
				$up_code = 103;
				$up_err = 'UPLOAD ERROR: The uploaded file was only partially uploaded';
				break;
			case UPLOAD_ERR_NO_FILE:
				$up_code = 104;
				$up_err = 'UPLOAD ERROR: No file was uploaded';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$up_code = 105;
				$up_err = 'UPLOAD ERROR: Missing a temporary folder';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$up_code = 106;
				$up_err = 'UPLOAD ERROR: Failed to write file to disk';
				break;
			case UPLOAD_ERR_EXTENSION:
				$up_code = 107;
				$up_err = 'UPLOAD ERROR: File upload stopped by extension';
				break;
			default:
				$up_code = 108;
				$up_err =  'UPLOAD ERROR: Unknown error ...';
		} //end switch
		if((string)$up_err != '') {
			$out['status'] = 'ERR';
			$out['message'] = (string) $up_err.' for file: '.$the_upld_file_name;
			$out['msg-code'] = (int) $up_code;
			return (array) $out;
		} //end if
		//-- do upload
		if(!is_uploaded_file((string)$the_upld_file_tmpname)) {
			$out['status'] = 'ERR';
			$out['message'] = 'UPLOAD ERROR: Cannot find the uploaded data for file: '.$the_upld_file_name.' at: '.$the_upld_file_tmpname;
			$out['msg-code'] = 11;
			return (array) $out;
		} //end if
		$fsize_upld = (int) SmartFileSystem::get_file_size($the_upld_file_tmpname);
		if((int)$fsize_upld <= 0) { // dissalow upload empty files, does not make sense or there was an error !!!
			$out['status'] = 'WARN';
			$out['message'] = 'Upload Failed: File is empty: '.$the_upld_file_name;
			$out['msg-code'] = 12;
			return (array) $out;
		} elseif((int)$fsize_upld > (int)$max_size) {
			$out['status'] = 'WARN';
			$out['message'] = 'Upload Failed: File is oversized: '.$the_upld_file_name;
			$out['msg-code'] = 13;
			return (array) $out;
		} //end if
		//--
		$out['status'] = 'OK';
		$out['message'] = '';
		$out['msg-code'] = 0;
		$out['filename'] = (string) $the_upld_file_name;
		$out['filetype'] = (string) $tmp_fext;
		$out['filecontent'] = (string) SmartFileSystem::read_uploaded($the_upld_file_tmpname);
		$out['filesize'] = (int) strlen($out['filecontent']);
		return (array) $out;
		//--
	} //END FUNCTION
	//================================================================


	//================================================================
	/**
	 * Store one Uploaded File to a destination directory
	 *
	 * @param STRING 	$dest_dir 					:: The destination directory ; Example: 'wpub/my-test/'
	 * @param STRING 	$var_name					:: The HTML Variable Name
	 * @param INTEGER 	$var_index					:: The HTML Variable Index: -1 for single file uploads ; 0..n for multi-file uploads
	 * @param BOOLEAN 	$allow_rewrite 				:: Allow rewrite if already exists that file in the destination directory ; default is TRUE
	 * @param INTEGER 	$max_size					:: The max file size in bytes that would be accepted ; set to zero for allow maximum size supported by PHP via INI settings
	 * @param STRING	$allowed_extensions			:: The list of allowed file extensions ; Default is '' ; Example to restrict to several extensions: '<ext1>,<ext2>,...<ext100>,...' ; set to empty string to allow all extenstions supported via Smart.Framework INI: SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS / SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS
	 * @param STRING 	$new_name 					:: Use a new file name for the uploaded file instead of the original one ; Set to empty string to preserve the uploaded file name ; DEFAULT is ''
	 * @param BOOLEAN 	$enforce_lowercase 			:: Set to TRUE to enforce lowercase file name ; DEFAULT is FALSE
	 * @return MIXED								:: '' (empty string) if all OK ; FALSE (boolean) if upload failed ; otherwise will return a non-empty string with the ERROR / WARNING message if the file was not successfuly stored in the destination directory
	 */
	public static function store_uploaded_file($dest_dir, $var_name, $var_index=-1, $allow_rewrite=true, $max_size=0, $allowed_extensions='', $new_name='', $enforce_lowercase=false) {
		//-- {{{SYNC-HANDLE-F-UPLOADS}}}
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
			return 'Invalid File VarName for Upload';
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
			$the_upld_file_name 	= (string) $_FILES[$var_name]['name'][$var_index];
			$the_upld_file_tmpname 	= (string) $_FILES[$var_name]['tmp_name'][$var_index];
			$the_upld_file_error 	= (int)    $_FILES[$var_name]['error'][$var_index];
		} else {
			$the_upld_file_name 	= (string) $_FILES[$var_name]['name'];
			$the_upld_file_tmpname 	= (string) $_FILES[$var_name]['tmp_name'];
			$the_upld_file_error 	= (int)    $_FILES[$var_name]['error'];
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
		} else {
			if(defined('SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS')) {
				if(stripos((string)SMART_FRAMEWORK_ALLOW_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') === false) {
					return 'Upload Failed: The uploaded file extension is not in the current allowed extensions list configuration for file: '.$the_upld_file_name;
				} //end if
			} //end if
			if((!defined('SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS')) OR (stripos((string)SMART_FRAMEWORK_DENY_UPLOAD_EXTENSIONS, '<'.$tmp_fext.'>') !== false)) {
				return 'Upload Failed: The uploaded file extension is denied by the current configuration for file: '.$the_upld_file_name;
			} //end if
		} //end if else
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
			return 'UPLOAD ERROR: Cannot find the uploaded data for file: '.$the_upld_file_name.' at: '.$the_upld_file_tmpname;
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
		return 'Smart.Framework :: PHP/Robot :: SelfBrowser ('.php_uname().') @ '.SmartHashCrypto::sha1('SelfBrowser/PHP/'.php_uname().'/'.SMART_SOFTWARE_NAMESPACE.'/'.SMART_FRAMEWORK_SECURITY_KEY);
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
		if((isset($_SERVER['HTTPS'])) AND ((string)trim((string)strtolower((string)$_SERVER['HTTPS'])) == 'on')) {
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