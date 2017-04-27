<?php
// [LIB - SmartFramework / Samples / Test Crypto]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.1.2 r.2017.04.11 / smart.framework.v.3.1

// Class: \SmartModExtLib\Samples\TestUnitCrypto
// Type: Module Library
// Info: this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

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
 * Test Crypto
 *
 * @access 		private
 * @internal
 *
 * @version 	v.170419
 *
 */
final class TestUnitCrypto {

	// ::

	//============================================================
	public static function testPhpAndJs() {

		//--
		$time = microtime(true);
		//--

		//--
		$unicode_text = "Unicode String [ ".time()." ]: @ Smart スマート // Cloud Application Platform クラウドアプリケーションプラットフォーム '".implode('', array_keys(\SmartUnicode::accented_chars()))." \" <p></p>
		? & * ^ $ @ ! ` ~ % () [] {} | \\ / + - _ : ; , . #'".microtime().'#';
		//--

		//--
		$b64enc = (string) base64_encode((string)$unicode_text);
		$b64dec = (string) base64_decode((string)$b64enc);
		//--

		//--
		$bin2hex = (string) strtoupper(bin2hex((string)$unicode_text));
		$hex2bin = (string) hex2bin(strtolower(trim((string)$bin2hex)));
		//--

		//--
		$hkey = 'TestUnit // This is a test key for Crypto Cipher ...'.time().$unicode_text;
		//--
		$he_enc = \SmartUtils::crypto_encrypt($unicode_text, $hkey);
		$he_dec = \SmartUtils::crypto_decrypt($he_enc, $hkey);
		//--
		if(((string)$he_dec != (string)$unicode_text) OR (sha1($he_dec) != \SmartHashCrypto::sha1($unicode_text))) {
			\Smart::raise_error('TestUnit FAILED in '.__METHOD__.'() :: Crypto Cipher test', 'TestUnit: Crypto Cipher test failed ...');
			return;
		} //end if
		//--

		//--
		$bf_key = \SmartHashCrypto::sha512('TestUnit // This is a test key for Blowfish ...'.time().$unicode_text);
		$bf_enc = \SmartUtils::crypto_blowfish_encrypt($unicode_text, $bf_key);
		$bf_dec = \SmartUtils::crypto_blowfish_decrypt($bf_enc, $bf_key);
		if(((string)$bf_dec != (string)$unicode_text) OR (sha1($bf_dec) != sha1($unicode_text))) {
			\Smart::raise_error('TestUnit FAILED in '.__METHOD__.'() :: Crypto Blowfish test', 'TestUnit: Blowfish test failed ...');
			return;
		} //end if
		//--

		//--
		$arch_lzs = \SmartArchiverLZS::compressToBase64($unicode_text);
		$unarch_lzs = \SmartArchiverLZS::decompressFromBase64($arch_lzs);
		if(((string)$unarch_lzs != (string)$unicode_text) OR (sha1($unarch_lzs) != sha1($unicode_text))) {
			\Smart::raise_error('TestUnit FAILED in '.__METHOD__.'() :: Crypto Arch-LZS test', 'TestUnit: Arch-LZS test failed ...');
			return;
		} //end if
		//--

		//--
		$arch_bf_lzs = \SmartArchiverLZS::compressToBase64($bf_enc);
		$unarch_bf_lzs = \SmartArchiverLZS::decompressFromBase64($arch_bf_lzs);
		if(((string)$unarch_bf_lzs != (string)$bf_enc) OR (sha1($unarch_bf_lzs) != sha1($bf_enc))) {
			\Smart::raise_error('TestUnit FAILED in '.__METHOD__.'() :: Crypto Blowfish-Arch-LZS test', 'TestUnit: Blowfish-Arch-LZS test failed ...');
			return;
		} //end if
		//--

		//--
		$time = 'TOTAL TIME was: '.(microtime(true) - $time);
		//--

		//--
		return (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-samples/libs/templates/testunit/partials/crypto-test.inc.htm',
			[
				//--
				'EXE-TIME' 					=> (string) $time,
				'UNICODE-TEXT' 				=> (string) $unicode_text,
				'JS-ESCAPED' 				=> (string) \Smart::escape_js($unicode_text),
				'HASH-SHA512' 				=> (string) \SmartHashCrypto::sha512($unicode_text),
				'HASH-SHA1' 				=> (string) sha1($unicode_text),
				'HASH-MD5' 					=> (string) md5($unicode_text),
				'BASE64-ENCODED' 			=> (string) $b64enc,
				'BASE64-DECODED' 			=> (string) $b64dec,
				'BIN2HEX-ENCODED' 			=> (string) $bin2hex,
				'HEX2BIN-DECODED' 			=> (string) $hex2bin,
				'LZS-ARCHIVED' 				=> (string) $arch_lzs,
				'LZS-UNARCHIVED' 			=> (string) $unarch_lzs,
				'BLOWFISH-ENCRYPTED' 		=> (string) $bf_enc,
				'BLOWFISH-LZS-ENCRYPTED' 	=> (string) $arch_bf_lzs,
				'BLOWFISH-DECRYPTED' 		=> (string) $bf_dec,
				'BLOWFISH-KEY' 				=> (string) $bf_key,
				'BLOWFISH-OPTIONS' 			=> (string) \SmartCipherCrypto::crypto_options('blowfish'),
				'HASHCRYPT-ENC' 			=> (string) $he_enc,
				'HASHCRYPT-DEC' 			=> (string) $he_dec,
				'HASHCRYPT-OPTIONS' 		=> (string) \SmartCipherCrypto::crypto_options('custom'),
				//--
				'DIALOG-WIDTH' 				=> '725',
				'DIALOG-HEIGHT' 			=> '400',
				'IMG-SIGN' 					=> 'lib/core/img/sign_info.png',
				'IMG-CHECK' 				=> 'lib/core/img/q_completed.png',
				'TXT-MAIN-HTML' 			=> '<span style="color:#83B953;">Good ... Perfect &nbsp;&nbsp;&nbsp; :: &nbsp;&nbsp;&nbsp; グッド ... パーフェクト</span>',
				'TXT-INFO-HTML' 			=> '<h2><span style="color:#333333;"><span style="color:#83B953;">All</span> the SmartFramework Unicode <span style="color:#83B953;">Tests PASSED on both PHP&nbsp;&amp;&nbsp;Javascript</span>:</span></h2>'.'<span style="font-size:14px;">'.\Smart::nl_2_br(\Smart::escape_html("##### Unicode CRYPTO / TESTS: ##### \n * Unicode support / UTF-8 \n * JS-Escape \n * SHA512 \n * SHA1 \n * MD5 \n * Base64: Encode / Decode \n * Bin2Hex / Hex2Bin \n * LZS-Arch: Archive / Unarchive \n * Blowfish (CBC): Encrypt / Decrypt \n * Custom: Encrypt / Decrypt (** Only for PHP) \n ##### END TESTS ... #####")).'</span>',
				'TEST-INFO' 				=> (string) 'Crypto Test Suite for SmartFramework: PHP + Javascript'
				//--
			]
		);
		//--

	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>