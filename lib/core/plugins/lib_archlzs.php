<?php
// [LIB - SmartFramework / Archive Utils]
// (c) 2006-2016 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - LZS Archiver / Unarchiver
// DEPENDS:
//	* Smart::
//	* SmartHashCrypto::
// DEPENDS-EXT: MBString
//======================================================

// [REGEX-SAFE-OK]

//--
if(!function_exists('mb_convert_encoding')) {
	die('ERROR: The PHP MBString Extension is required for SmartFramework / Lib Archive Utils');
} //end if
//--


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// fixes by unixman (see below)
// based on LZString v . 1 . 3 . 6 (a free LZ based compression algorithm)
// this is intended for on-the-fly archive/unarchive not for storing (where ZLib is a better option)
// it compatible with SmartFramework/JS/SmartJS_Archiver_LZS
// License: BSD
// (c) iradu@unix-world.org : optimizations, fixes, unicode safe
// Original work by Tobias Neeb <tobias.neeb@gmail.com>

/**
 * Class: SmartArchiverLZS - Compress or Decompress a LZS archive.
 *
 * <code>
 * // Usage example:
 * $myString = 'Some string to archive as LZS';
 * $archString = SmartArchiverLZS::compressToBase64($myString); // archive the string
 * $unarchString = SmartArchiverLZS::decompressFromBase64($archString); // unarchive it back
 * if((string)$unarchString !== (string)$myString) { // Test: check if unarchive is the same as archive
 *     throw new Exception('LZS Archive test Failed !');
 * } //end if
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	extensions: PHP MBString ; classes: Smart, SmartHashCrypto
 * @version 	v.160204
 * @package 	Archivers
 *
 */

final class SmartArchiverLZS {

	// ::


//================================================================
private static $keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
//================================================================


//================================================================
/**
 * Compress a string to LZS + Base64
 *
 * @param		STRING		$input		The uncompressed string
 * @return		STRING		The Base64 LZS Compressed String
 *
 */
public static function compressToBase64($input) {
	//--
	$input = (string) $input;
	//--
	$input = self::compressRawLZS($input);
	//--
	$output = '';
	//--
	$chr1 = 'NaN';
	$chr2 = 'NaN';
	$chr3 = 'NaN';
	$enc1 = 'NaN';
	$enc2 = 'NaN';
	$enc3 = 'NaN';
	$enc4 = 'NaN';
	//--
	$strlen = SmartUnicode::str_len($input);
	//--
	$i = 0;
	//--
	while($i < ($strlen * 2)) {
		//--var_dump('-------'.$i.'<'.($strlen*2).'-------');
		if(($i % 2) === 0) {
			//--
			$chr1 = self::charCodeAt($input, (int)($i/2)) >> 8;
			$chr2 = self::charCodeAt($input, (int)($i/2)) & 255;
			//--
			if((($i/2)+1) < $strlen) {
				$chr3 = self::charCodeAt($input, (int)(($i/2)+1)) >> 8;
			} else {
				$chr3 = 'NaN';
			} //end if else
			//--
		} else {
			//--
			$chr1 = self::charCodeAt($input, (int)(($i-1)/2)) & 255;
			//--
			if((($i+1)/2) < $strlen) {
				$chr2 = self::charCodeAt($input, (int)(($i+1)/2)) >> 8;
				$chr3 = self::charCodeAt($input, (int)(($i+1)/2)) & 255;
			} else  {
				$chr2 = 'NaN';
				$chr3 = 'NaN';
			} //end if else
			//--
		} //end if else
		//--
		$i += 3;
		//--
		$enc1 = $chr1 >> 2;
		$enc2 = (($chr1 & 3) << 4) | ($chr2 >> 4);
		$enc3 = (($chr2 & 15) << 2) | ($chr3 >> 6);
		$enc4 = $chr3 & 63;
		//--
		if($chr2 === 'NaN') {
			$enc3 = 64;
			$enc4 = 64;
		} elseif($chr3 === 'NaN') {
			$enc4 = 64;
		} //end if else
		//--
		//var_dump(array(
		//	$chr1,
		//	$chr2,
		//	$chr3,
		//	'-',
		//	$enc1.' = '.self::$keyStr{$enc1},
		//	$enc2.' = '.self::$keyStr{$enc2},
		//	$enc3.' = '.self::$keyStr{$enc3},
		//	$enc4.' = '.self::$keyStr{$enc4}
		//));
		//--
		$output = $output.self::$keyStr{$enc1}.self::$keyStr{$enc2}.self::$keyStr{$enc3}.self::$keyStr{$enc4};
		//--
	} //end while
	//--
	return (string) $output;
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Decompress a Base64 + LZS compressed string
 *
 * @param		STRING		$input		The Base64 LZS Compressed String
 * @return		STRING		The uncompressed string
 *
 */
public static function decompressFromBase64($input) {
	//--
	$input = (string) $input;
	//--
	$output = '';
	//--
	$ol = 0;
	$output_ = NULL;
	$chr1 = NULL;
	$chr2 = NULL;
	$chr3 = NULL;
	$enc1 = NULL;
	$enc2 = NULL;
	$enc3 = NULL;
	$enc4 = NULL;
	//--
	$input = preg_replace('/[^A-Za-z0-9\+\/\=]/', '', (string)$input);
	//--
	$i=0;
	//--
	while($i < SmartUnicode::str_len($input)) {
		//--
		$enc1 = strpos(self::$keyStr, (string)$input{$i++});
		$enc2 = strpos(self::$keyStr, (string)$input{$i++});
		$enc3 = strpos(self::$keyStr, (string)$input{$i++});
		$enc4 = strpos(self::$keyStr, (string)$input{$i++});
		//--
		$chr1 = ($enc1 << 2) | ($enc2 >> 4);
		$chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
		$chr3 = (($enc3 & 3) << 6) | $enc4;
		//--
		if(($ol % 2) == 0) {
			//--
			$output_ = $chr1 << 8;
			//--
			if($enc3 != 64) {
				//--
				$output .= self::fromCharCode($output_ | $chr2);
				//--
			} //end if
			//--
			if($enc4 != 64) {
				//--
				$output_ = $chr3 << 8;
				//--
			} //end if
			//--
		} else {
			//--
			$output = $output.self::fromCharCode($output_ | $chr1);
			//--
			if($enc3 != 64) {
				$output_ = $chr2 << 8;
			} //end if
			//--
			if($enc4 != 64) {
				//--
				$output .= self::fromCharCode($output_ | $chr3);
				//--
			} //end if
			//--
		} //end if else
		//--
		$ol += 3;
		//--
	} //end while
	//--
	return self::decompressRawLZS($output);
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Compress RAW LZS
 *
 * @access 		private
 * @internal
 *
 */
public static function compressRawLZS($uncompressed) {
	//--
	$arch = strtoupper(bin2hex((string)$uncompressed));
	//--
	return self::RawDeflate($arch.'#CHECKSUM-SHA1#'.SmartHashCrypto::sha1($arch)); // add sha1 checksum
	//--
} //END FUNCTION
//================================================================


//================================================================
/**
 * Decompress RAW LZS
 *
 * @access 		private
 * @internal
 *
 */
public static function decompressRawLZS($compressed) {
	//--
	$unarch = trim(self::RawInflate((string)$compressed));
	//-- checksum verification
	$arr = explode('#CHECKSUM-SHA1#', $unarch);
	$unarch = trim($arr[0]);
	$checksum = trim($arr[1]);
	//--
	if((string)SmartHashCrypto::sha1($unarch) != (string)$checksum) {
		Smart::log_warning('SmartArchiverLZS/decompressRawLZS: Checksum Failed');
		return ''; // string is corrupted, avoid to return
	} //end if
	//--
	return @hex2bin(strtolower($unarch));
	//--
} //END FUNCTION
//================================================================


//############ PRIVATES


//================================================================
private static function fromCharCode() {
	//--
	$args = func_get_args();
	//-- var_dump($args[0].': '.array_reduce(func_get_args(),function($a,$b){$a.=self::utf8_chr($b);return $a;}));
	return array_reduce(func_get_args(), function($a, $b){ $a .= self::utf8_chr($b); return $a; });
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function utf8_chr($u) {
	//--
	return mb_convert_encoding('&#'.intval($u).';', SMART_FRAMEWORK_CHARSET, 'HTML-ENTITIES');
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function charCodeAt($str, $num) {
	//--
	return self::utf8_ord(self::utf8_charAt($str, $num));
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function utf8_ord($ch) {
	//--
	$len = strlen($ch);
	//--
	if($len <= 0) {
		return false;
	} //end if
	//--
	$h = ord($ch{0});
	//--
	if($h <= 0x7F) {
		return $h;
	} //end if
	if($h < 0xC2) {
		return false;
	} //end if
	if($h <= 0xDF && $len>1) {
		return ($h & 0x1F) <<  6 | (ord($ch{1}) & 0x3F);
	} //end if
	if($h <= 0xEF && $len>2) {
		return ($h & 0x0F) << 12 | (ord($ch{1}) & 0x3F) << 6 | (ord($ch{2}) & 0x3F);
	} //end if
	if($h <= 0xF4 && $len>3) {
		return ($h & 0x0F) << 18 | (ord($ch{1}) & 0x3F) << 12 | (ord($ch{2}) & 0x3F) << 6 | (ord($ch{3}) & 0x3F);
	} //end if
	//--
	return false;
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function utf8_charAt($str, $num) {
	//--
	return SmartUnicode::sub_str($str, $num, 1);
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function writeBit($value, SmartArchiverObjDataLZS $data) {
	//--
	$data->val = ($data->val << 1) | $value;
	//--
	if($data->position == 15) {
		//--
		$data->position = 0;
		$data->str .= self::fromCharCode($data->val);
		$data->val = 0;
		//--
	} else {
		//--
		$data->position++;
		//--
	} //end if else
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function writeBits($numbits, $value, SmartArchiverObjDataLZS $data) {
	//--
	if(is_string($value)) {
		//--
		$value = self::charCodeAt($value, 0);
		//--
	} //end if
	//--
	for($i = 0; $i < $numbits; $i++) {
		//--
		self::writeBit($value & 1, $data);
		//--
		$value = $value >> 1;
		//--
	} //end for
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function decrementEnlargeIn(SmartArchiverObjContextLZS $context) {
	//--
	$context->enlargeIn--;
	//--
	if($context->enlargeIn === 0) {
		$context->enlargeIn = pow(2, $context->numBits);
		$context->numBits++;
	} //end if
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function produceW(SmartArchiverObjContextLZS $context) {
	//--
	if(array_key_exists($context->w, $context->dictionaryToCreate)) {
		//--
		if(self::charCodeAt($context->w, 0) < 256) {
			self::writeBits($context->numBits, 0, $context->data);
			self::writeBits(8, self::utf8_charAt($context->w, 0), $context->data);
		} else {
			self::writeBits($context->numBits, 1, $context->data);
			self::writeBits(16, self::utf8_charAt($context->w, 0), $context->data);
		} //end if
		//--
		self::decrementEnlargeIn($context);
		//--
		unset($context->dictionaryToCreate[$context->w]);
		//--
	} else {
		//--
		self::writeBits($context->numBits, $context->dictionary[$context->w], $context->data);
		//--
	} //end if else
	//--
	self::decrementEnlargeIn($context);
	//--
	return $context;
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function RawDeflate($uncompressed) {
	//--
	$uncompressed = (string) $uncompressed;
	//--
	$context = new SmartArchiverObjContextLZS();
	//--
	for($i = 0; $i < strlen($uncompressed); $i++) {
		//--
		$context->c = self::utf8_charAt($uncompressed, $i);
		//--
		if(!array_key_exists($context->c, $context->dictionary)) {
			$context->dictionary[$context->c] = $context->dictSize++;
			$context->dictionaryToCreate[$context->c] = TRUE;
		} //end if
		//--
		$context->wc = $context->w.$context->c;
		//--
		if(array_key_exists($context->wc, $context->dictionary)) {
			$context->w = $context->wc;
		} else {
			self::produceW($context);
			$context->dictionary[$context->wc] = $context->dictSize++;
			$context->w = $context->c;
		} //end if else
		//--
	} //end for
	//--
	if($context->w !== '') {
	   self::produceW($context);
	} //end if
	//--
	self::writeBits($context->numBits, 2, $context->data);
	//--
	$safe = 0;
	//--
	while(true) {
		//--
		$context->data->val = $context->data->val << 1;
		//--
		if($context->data->position == 15) {
			//--
			$context->data->str .= self::fromCharCode($context->data->val);
			//--
			break;
			//--
		} //end if
		//--
		$context->data->position++;
		//--
	} //end while
	//--
	return (string) $context->data->str;
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function readBit(SmartArchiverObjDataLZS $data) {
	//--
	$res = $data->val & $data->position;
	//--
	$data->position >>= 1;
	//--
	if($data->position == 0) {
		//--
		$data->position = 32768;
		$data->val = self::charCodeAt($data->str, $data->index++);
		//--
	} //end if
	//-- data.val = (data.val << 1); // this was not enabled in original
	return $res > 0 ? 1 : 0;
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function readBits($numBits, SmartArchiverObjDataLZS $data) {
	//--
	$res = 0;
	//--
	$maxpower = pow(2, $numBits);
	//--
	$power = 1;
	//--
	while($power != $maxpower) {
		//--
		$res |= self::readBit($data) * $power;
		//--
		$power <<= 1;
		//--
	} //end while
	//--
	return $res;
	//--
} //END FUNCTION
//================================================================


//================================================================
private static function RawInflate($compressed) {
	//--
	$compressed = (string) $compressed;
	//--
	$dictionary = array(
		0 => 0,
		1 => 1,
		2 => 2
	);
	//--
	$next = NULL;
	$enlargeIn = 4;
	$dictSize = 4;
	$numBits = 3;
	$entry = '';
	$result = '';
	$w = NULL;
	$c = NULL;
	$errorCount = 0;
	$literal = NULL;
	$data = new SmartArchiverObjDataLZS();
	//--
	$data->str = $compressed;
	$data->val = self::charCodeAt($compressed, 0);
	$data->position = 32768;
	$data->index = 1;
	//--
	switch(self::readBits(2, $data)) {
	   case 0:
		   $c = self::fromCharCode(self::readBits(8, $data));
		   break;
	   case 1:
		   $c = self::fromCharCode(self::readBits(16, $data));
		   break;
	   case 2:
		   return '';
	} //end switch
	//--
	$dictionary[3] = $c;
	$w = $result = $c;
	//--
	while(true) {
		//-- # fix by unixman (this portion was added) based on JS version to avoid Loop Hard Limit
		if($data->index > strlen($data->str)) {
			return '';
		} //end if
		//-- #end fix
		$c = self::readBits($numBits, $data);
		//--
		switch($c) {
			case 0:
				//--
				/* fixed above: unixman, and no more necessary because actually this limits the archive size to 10k
				if($errorCount++ > 10000) { // this is perhaps no more necessary because is catched above with the fix (unixman)
					Smart::log_warning('ERROR: Archiver/LZS/Decompress: Decode Loop Hard Limit (10.000) ...');
					return ''; // null (not null to sync with fixes from javascript)
				} //end if
				*/
				//--
				$c = self::fromCharCode(self::readBits(8, $data));
				//--
				$dictionary[$dictSize++] = $c;
				$c = $dictSize-1;
				$enlargeIn--;
				//--
				break;
			case 1:
				//--
				$c = self::fromCharCode(self::readBits(16, $data));
				//--
				$dictionary[$dictSize++] = $c;
				$c = $dictSize-1;
				$enlargeIn--;
				//--
				break;
			case 2:
				//--
				return $result;
				//--
		} //end switch
		//--
		if($enlargeIn === 0) {
			$enlargeIn = pow(2, $numBits);
			$numBits++;
		} //end if
		//--
		if(array_key_exists($c, $dictionary) && $dictionary[$c] !== false) {
			//--
			$entry = $dictionary[$c];
			//--
		} else {
			//--
			if($c === $dictSize) {
				//--
				$entry = $w.self::utf8_charAt($w, 0);
				//--
			} else {
				//--
				//Smart::log_notice('ERROR: Archiver/LZS/Decompress: $c != $dictSize ('.$c.','.$dictSize.')');
				return null;
				//--
			} //end if else
			//--
		} //end if else
		//--
		$result .= $entry;
		//-- Add w+entry[0] to the dictionary.
		$dictionary[$dictSize++] = (string) $w.''.self::utf8_charAt($entry, 0);
		//--
		$enlargeIn--;
		//--
		$w = $entry;
		//--
		if($enlargeIn == 0) {
			$enlargeIn = pow(2, $numBits);
			$numBits++;
		} //end if
		//--
	} //end while
	//--
	return $result;
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
 * Class Smart Archiver Obj Context LZS
 *
 * @access 		private
 * @internal
 *
 */
final class SmartArchiverObjContextLZS {
	//--
	// ->
	// v.150122
	//--
	public $c = '';
	public $w = '';
	public $wc = '';
	public $enlargeIn = 2;
	public $dictSize = 3;
	public $numBits = 2;
	public $data;
	public $dictionary = array();
	public $dictionaryToCreate = array();
	//--
	public function __construct() {
		//--
		$this->data = new SmartArchiverObjDataLZS();
		//--
	} //END FUNCTION
	//--
} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class Smart Archiver Obj Data LZS
 *
 * @access 		private
 * @internal
 *
 */
final class SmartArchiverObjDataLZS {
	//--
	// ->
	// v.150122
	//--
	public $str;
	public $val;
	public $position = 0;
	public $index = 1;
	//--
	public function __construct() {
		// nothing here ...
	} //END FUNCTION
	//--
} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>