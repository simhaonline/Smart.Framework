<?php
// [LIB - Smart.Framework / Symmetric Crypto and Hashing]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.7.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Crypto Support: BlowFish (CBC) built-in / BlowFish/AES256/Camellia256 (CBC/CFB/OFB via OpenSSL)
// DEPENDS:
//	* Smart::
// DEPENDS-EXT: PHP OpenSSL *optional*
//======================================================
// NOTICE: This is now unicode safe ...
//	* Recommended type is CBC
//	* Unicode issues were fixed as this: because Blowfish is not unicode safe we do B64Encode before BlowFish encode
//	* Returned string is Upper Bin2Hex
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

if(!function_exists('hash_algos')) {
	@http_response_code(500);
	die('PHP Extension Hash is not available');
} //end if


/**
 * Class: SmartHashCrypto - provide various hashes for a string: salted password, sha512, sha384, sha256, sha1, md5.
 *
 * <code>
 * // Usage example:
 * SmartHashCrypto::some_method_of_this_class(...);
 * </code>
 *
 * @usage       static object: Class::method() - This class provides only STATIC methods
 *
 * @access      PUBLIC
 * @depends     PHP hash_algos() / hash()
 * @version     v.20200424
 * @package     @Core:Crypto
 *
 */
final class SmartHashCrypto {

	// ::

	private static $cache = array();


	//==============================================================
	/**
	 * Encrypt (one way) a password :: this may depend on *OPTIONAL* extra salt $y_custom_salt
	 *
	 * @param STRING $y_pass
	 * @return STRING, 128 chars length
	 */
	public static function password($y_pass, $y_custom_salt='') { // {{{SYNC-HASH-PASSWORD}}}
		//-- v.151216
		// Password Salt must not be very complex :: http://stackoverflow.com/questions/5482437/md5-hashing-using-password-as-salt
		// extraordinary good salt + weak password = breakable in seconds
		// just sensible salt + strong password = unbreakable
		// the best is to pre-pend the salt: http://stackoverflow.com/questions/4171859/password-salts-prepending-vs-appending
		// ex: azA-Z09 pass, prepend needs 26^6 permutations while append 26^10, so append adds more complexity
		// SHA512 is high complexity: O(2^n/2) # http://stackoverflow.com/questions/6776050/how-long-to-brute-force-a-salted-sha-512-hash-salt-provided
		//--
		if((string)$y_custom_salt != '') {
			$y_custom_salt = (string) md5((string)'$1'.$y_custom_salt.'$2'.$y_pass);
		} //end if
		//--
		return self::sha512((string)$y_custom_salt.'@ Smart Framework :'.$y_pass.': スマート フレームワーク # 170115%!Password.512/($Auth)*'.strtoupper((string)sha1((string)$y_pass.'&$'.$y_custom_salt)).'^#[?]');
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Returns the SHA512 hash of a string
	 *
	 * @param STRING $y_str
	 * @return STRING, 128 chars length
	 */
	public static function sha512($y_str) {
		//--
		if(!self::algo_check('sha512')) {
			Smart::raise_error('ERROR: Smart.Framework Crypto Hash requires SHA512 Hash/Algo', 'SHA512 Hash/Algo is missing');
			return '';
		} //end if
		//--
		return (string) hash('sha512', (string)$y_str, false); // execution cost: 0.35
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Returns the SHA384 hash of a string
	 *
	 * @param STRING $y_str
	 * @return STRING, 96 chars length
	 */
	public static function sha384($y_str) {
		//--
		if(!self::algo_check('sha384')) {
			Smart::raise_error('ERROR: Smart.Framework Crypto Hash requires SHA384 Hash/Algo', 'SHA384 Hash/Algo is missing');
			return '';
		} //end if
		//--
		return (string) hash('sha384', (string)$y_str, false); // execution cost: 0.34
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Returns the SHA256 hash of a string
	 *
	 * @param STRING $y_str
	 * @return STRING, 64 chars length
	 */
	public static function sha256($y_str) {
		//--
		if(!self::algo_check('sha256')) {
			Smart::raise_error('ERROR: Smart.Framework Crypto Hash requires SHA256 Hash/Algo', 'SHA256 Hash/Algo is missing');
			return '';
		} //end if
		//--
		return (string) hash('sha256', (string)$y_str, false); // execution cost: 0.21
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Returns the SHA1 hash of a string
	 *
	 * @param STRING $y_str
	 * @return STRING, 40 chars length
	 */
	public static function sha1($y_str) {
		//--
		if(!function_exists('sha1')) {
			Smart::raise_error('ERROR: Smart.Framework Crypto Hash requires SHA1 support', 'SHA1 support is missing');
			return '';
		} //end if
		//--
		return (string) sha1((string)$y_str); // execution cost: 0.14
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Returns the MD5 hash of a string
	 *
	 * @param STRING $y_str
	 * @return STRING, 32 chars length
	 */
	public static function md5($y_str) {
		//--
		if(!function_exists('md5')) {
			Smart::raise_error('ERROR: Smart.Framework Crypto Hash requires MD5 support', 'MD5 support is missing');
			return '';
		} //end if
		//--
		return (string) md5((string)$y_str); // execution cost: 0.13
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Returns the CRC32B hash of a string in base16 by default or base36 optional (better than CRC32, portable between 32-bit and 64-bit platforms, unsigned)
	 *
	 * @param STRING $y_str
	 * @param BOOLEAN $y_base36
	 * @return STRING, 8 chars length
	 */
	public static function crc32b($y_str, $y_base36=false) {
		//--
		if(!self::algo_check('sha512')) {
			Smart::raise_error('ERROR: Smart.Framework Crypto Hash requires CRC32B Hash/Algo', 'CRC32B Hash/Algo is missing');
			return '';
		} //end if
		//--
		$hash = (string) hash('crc32b', (string)$y_str, false); // execution cost: 0.21
		if($y_base36 === true) {
			$hash = (string) base_convert((string)$hash, 16, 36);
		} //end if
		//--
		return (string) $hash;
		//--
	} //END FUNCTION
	//==============================================================


	//##### PRIVATES


	//==============================================================
	private static function algo_check($y_algo) {
		//--
		if(in_array($y_algo, (array)self::algos())) {
			$out = 1;
		} else {
			$out = 0;
		} //end if else
		//--
		return $out;
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	private static function algos() {
		//--
		if(!is_array(self::$cache['algos'])) {
			self::$cache['algos'] = (array) hash_algos();
		} //end if else
		//--
		return (array) self::$cache['algos'];
		//--
	} //END FUNCTION
	//==============================================================


	//##### DEBUG ONLY


	//==============================================================
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
					'title' => 'SmartHashCrypto // Internal Cache',
					'data' => 'Dump:'."\n".print_r(self::$cache,1)
				]);
			} //end if
		} //end if
		//--
	} //END FUNCTION
	//==============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartCipherCrypto
 * Provides a built-in based feature to handle the Blowfish (CBC) encryption / decryption.
 * This provides an advanced crypto handler for Blowfish CBC algorithm.
 *
 * <code>
 * // Usage example:
 * SmartCipherCrypto::some_method_of_this_class(...);
 * </code>
 *
 * @usage       static object: Class::method() - This class provides only STATIC methods
 * @hints       Blowfish is a 64-bit (8 bytes) block cipher. Max Key is up to 56 chars length (56 bytes = 448 bits). The CBC mode requires a initialization vector (iv).
 *
 * @depends     classes: Smart
 * @version     v.20200424
 * @package     @Core:Crypto
 *
 */
final class SmartCipherCrypto {

	// ::


	//==============================================================
	/**
	 * Encrypts a string using the selected Cipher Algo.
	 *
	 * @param ENUM $cipher 		Selected cipher: hash/{mode}, blowfish.cbc, openssl/cipher/mode
	 * @param STRING $key 		The encryption key
	 * @param STRING $data 		The plain data to be encrypted
	 * @return STRING 			The encrypted data
	 */
	public static function encrypt($cipher, $key, $data) {
		//--
		if((string)trim((string)$data) == '') {
			return '';
		} //end if
		//--
		if(((string)$cipher == 'blowfish') OR ((string)$cipher == 'blowfish.cbc')) { // use the built-in blowfish CBC
			$crypt = new SmartCryptoCipherBlowfishCBC((string)$key);
		} elseif(substr((string)$cipher, 0, 8) == 'openssl/') {
			$crypt = new SmartCryptoOpenSSLCipher((string)$key, (string)$cipher);
		} elseif(substr((string)$cipher, 0, 5) == 'hash/') {
			$crypt = new SmartCryptoCipherHash((string)$key, (string)$cipher);
		} else {
			Smart::raise_error('SmartCipherCrypto/Encrypt: INVALID Cipher/Algo');
			return '';
		} //end if else
		//--
		return (string) $crypt->encrypt((string)$data);
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Decrypts a string using the selected Cipher Algo.
	 *
	 * @param ENUM $cipher 		Selected cipher: hash/{mode}, blowfish.cbc, openssl/cipher/mode
	 * @param STRING $key 		The encryption key
	 * @param STRING $data 		The encrypted data
	 * @return STRING 			The plain / decrypted data
	 */
	public static function decrypt($cipher, $key, $data) {
		//--
		if((string)trim((string)$data) == '') {
			return '';
		} //end if
		//--
		if(((string)$cipher == 'blowfish') OR ((string)$cipher == 'blowfish.cbc')) { // use the built-in blowfish CBC
			$crypt = new SmartCryptoCipherBlowfishCBC((string)$key);
		} elseif(substr((string)$cipher, 0, 8) == 'openssl/') {
			$crypt = new SmartCryptoOpenSSLCipher((string)$key, (string)$cipher);
		} elseif(substr((string)$cipher, 0, 5) == 'hash/') {
			$crypt = new SmartCryptoCipherHash((string)$key, (string)$cipher);
		} else {
			Smart::raise_error('SmartCipherCrypto/Decrypt: INVALID Cipher/Algo');
			return '';
		} //end if else
		//--
		return (string) $crypt->decrypt((string)$data);
		//--
	} //END FUNCTION
	//==============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartCryptoOpenSSLCipher
 * Provides an OpenSSL based encryption / decryption.
 *
 * @usage       dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @depends     extensions: PHP OpenSSL ; classes: Smart
 * @version     v.20200424
 *
 */
final class SmartCryptoOpenSSLCipher {

	// ->


	//==============================================================
	//-- @ PRIVATE
	private $crypto_cipher;		// Crypto Cipher (ex: BF-CBC)
	private $crypto_key;		// Crypto Key
	private $crypto_iv;			// Crypto IV (initialization vector)
	private $crypto_opts; 		// Crypto Options (OpenSSL options: OPENSSL_RAW_DATA OPENSSL_ZERO_PADDING)
	//--
	//==============================================================


	//==============================================================
	/**
	 * Constructor
	 * Initializes the blowfish cipher object, and gives a sets
	 * the secret key
	 *
	 * @param string $key
	 * @param enum $runmode		ex: openssl/blowfish/CBC
	 * @access public
	 */
	public function __construct($key, $runmode) {
		//--
		if((!function_exists('openssl_encrypt')) OR (!function_exists('openssl_decrypt'))) {
			Smart::raise_error('SmartCryptoOpenSSLCipher requires the PHP OpenSSL Extension with Encrypt/Decrypt support ! If is not available use the alternative Encryption Mode in Configuration INITS !', 'PHP OpenSSL Extension Encrypt/Decrypt support is missing');
			return '';
		} //end if
		//-- Blowfish uses a variable size key, ranging from 32 to 448 bits (4 to 56 characters)
		$key = (string) $key;
		if((string)trim((string)$key) == '') {
			$key = (string) SMART_FRAMEWORK_SECURITY_KEY;
		} //end if
		//--
		$tmp_mode_crypto 	= (array)  explode('/', (string)$runmode); // Example: 'openssl/blowfish/CBC'
		$tmp_expl_check 	= (string) trim((string)strtolower((string)$tmp_mode_crypto[0]));
		$tmp_expl_algo 		= (string) trim((string)strtolower((string)$tmp_mode_crypto[1]));
		$tmp_expl_method 	= (string) trim((string)strtoupper((string)$tmp_mode_crypto[2]));
		//--
		if((string)$tmp_expl_check != 'openssl') {
			Smart::raise_error('SmartCryptoOpenSSLCipher // Invalid Run Mode: '.$tmp_mode_crypto);
			return '';
		} //end if
		//--
		$this->crypto_opts = 0;
		//--
		switch((string)$tmp_expl_algo) { // cipher
			//--
			case 'blowfish': // currently this is the only-one compatible with the Symmetric Crypto JS Api ; chipher (Blowfish is a 64-bit (8 bytes) block cipher)
				//--
				$this->crypto_opts = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING; // preserve compatibility with mcrypt
				//--
				switch((string)$tmp_expl_method) { // for blowfish accept only: CBC, CFB or OFB
					case 'OFB':
						$this->crypto_cipher = 'BF-OFB'; // Blowfish OFB
						break;
					case 'CFB':
						$this->crypto_cipher = 'BF-CFB'; // Blowfish CFB
						break;
					case 'CBC':
					default:
						$this->crypto_cipher = 'BF-CBC'; // Blowfish CBC (default mode)
				} //end switch
				//-- Blowfish key {{{SYNC-BLOWFISH-KEY}}} ; key size: 32 .. 448 bits (4 .. 56 chars) ; default used is 384 bits = 48 chars.
				$this->crypto_key = (string) substr((string)SmartHashCrypto::sha512((string)$key), 13, 29).strtoupper((string)substr((string)sha1((string)$key), 13, 10)).substr((string)md5((string)$key), 13, 9);
				//-- Blowfish iv {{{SYNC-BLOWFISH-IV}}} ; block size: 64 bits = 8 chars
				$this->crypto_iv = (string) substr((string)base64_encode((string)sha1('@Smart.Framework-Crypto/BlowFish:'.$key.'#'.sha1('BlowFish-iv-SHA1'.$key).'-'.strtoupper((string)md5('BlowFish-iv-MD5'.$key)).'#')), 1, 8);
				//--
				break;
			case 'aes256':
				//--
				$this->crypto_opts = OPENSSL_RAW_DATA; // don't use OPENSSL_ZERO_PADDING
				//--
				switch((string)$tmp_expl_method) { // for AES256 accept only: CBC, CFB or OFB
					case 'OFB':
						$this->crypto_cipher = 'AES-256-OFB'; // AES-256 OFB
						break;
					case 'CFB':
						$this->crypto_cipher = 'AES-256-CFB'; // AES-256 CFB
						break;
					case 'CBC':
					default:
						$this->crypto_cipher = 'AES-256-CBC'; // AES-256 CBC (default mode)
				} //end switch
				//-- key sizes: 128, 192 or 256 bits = max 32 chars
				$this->crypto_key = (string) substr((string)SmartHashCrypto::sha512((string)$key), 13, 20).strtoupper((string)substr((string)sha1((string)$key), 13, 10)).substr((string)md5((string)$key), 13, 2);
				//-- block sizes: 128 bits = 16 chars
				$this->crypto_iv = (string) substr((string)base64_encode((string)sha1('@Smart.Framework-Crypto/AES256:'.$key.'#'.sha1('AES256-iv-SHA1'.$key).'-'.strtoupper((string)md5('AES256-iv-MD5'.$key)).'#')), 1, 16);
				//--
				break;
			case 'camellia256':
				//--
				$this->crypto_opts = OPENSSL_RAW_DATA; // don't use OPENSSL_ZERO_PADDING
				//--
				switch((string)$tmp_expl_method) { // for Camellia256 accept only: CBC, CFB or OFB
					case 'OFB':
						$this->crypto_cipher = 'CAMELLIA-256-OFB'; // CAMELLIA-256 OFB
						break;
					case 'CFB':
						$this->crypto_cipher = 'CAMELLIA-256-CFB'; // CAMELLIA-256 CFB
						break;
					case 'CBC':
					default:
						$this->crypto_cipher = 'CAMELLIA-256-CBC'; // CAMELLIA-256 CBC (default mode)
				} //end switch
				//-- key sizes: 128, 192 or 256 bits = max 32 chars
				$this->crypto_key = (string) substr((string)SmartHashCrypto::sha512((string)$key), 13, 20).strtoupper((string)substr((string)sha1((string)$key), 13, 10)).substr((string)md5((string)$key), 13, 2);
				//-- block sizes: 128 bits = 16 chars
				$this->crypto_iv = (string) substr((string)base64_encode((string)sha1('@Smart.Framework-Crypto/Camellia256:'.$key.'#'.sha1('Camellia256-iv-SHA1'.$key).'-'.strtoupper((string)md5('Camellia256-iv-MD5'.$key)).'#')), 19, 16);
				//--
				break;
			default:
				Smart::raise_error('SmartCryptoOpenSSLCipher // Invalid Cipher: '.$tmp_expl_algo);
				return '';
		} //end if
		//--
	//	Smart::log_notice(__CLASS__.' @ Cipher: '.$this->crypto_cipher);
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Get the Internal Key (the real key is a modified version of the original key)
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function getKey() {
		//--
		return (string) $this->crypto_key;
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Get the Initialization Vector (the real IV is a modified version of the original IV)
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function getIv() {
		//--
		return (string) $this->crypto_iv;
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Encrypts a string in CBC Mode or ECB Mode
	 *
	 * @param string $plainText
	 * @return string Returns cipher text
	 * @access public
	 */
	public function encrypt($plainText) {
		//--
		$plainText = (string) $plainText;
		//-- base64 :: because is not UTF-8 safe and may corrupt unicode characters
		$plainText = base64_encode((string)$plainText);
		//-- {{{SYNC-BLOWFISH-CHECKSUM}}}
		$plainText .= '#CHECKSUM-SHA1#'.sha1($plainText);
		//--
		//== {{{SYNC-BLOWFISH-PADDING}}}
		//-- Blowfish is a 64-bit block cipher. This means that the data must be provided in units that are a multiple of 8 bytes
		$padding = 8 - (strlen($plainText) & 7);
		//-- unixman: fix: add spaces as padding as we have it as b64 encoded and will not modify the original
		for($i=0; $i<$padding; $i++) {
			$plainText .= ' '; // unixman (pad with spaces)
		} //end for
		//==
		//--
		return (string) strtoupper((string)bin2hex((string)openssl_encrypt((string)$plainText, (string)$this->crypto_cipher, (string)$this->crypto_key, $this->crypto_opts, (string)$this->crypto_iv)));
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Decrypts an encrypted string in CBC Mode or ECB Mode
	 *
	 * @param string $cipherText
	 * @return string Returns plain text
	 * @access public
	 */
	public function decrypt($cipherText) {
		//--
		if((!is_string($cipherText)) OR ((string)$cipherText == '')) {
			return '';
		} //end if
		//--
		$cipherText = @hex2bin(strtolower(trim((string)$cipherText)));
		//-- {{{SYNC-BLOWFISH-PADDING-TRIM}}} :: trim padding spaces
		$plainText = (string) trim((string)openssl_decrypt((string)$cipherText, (string)$this->crypto_cipher, (string)$this->crypto_key, $this->crypto_opts, (string)$this->crypto_iv));
		//-- {{{SYNC-BLOWFISH-CHECKSUM}}}
		$arr = explode('#CHECKSUM-SHA1#', $plainText);
		$plainText = (string) trim((string)$arr[0]);
		$checksum = (string) trim((string)$arr[1]);
		if((string)sha1($plainText) != (string)$checksum) {
			Smart::log_notice('SmartCryptoOpenSSLCipher/Decrypt: Checksum Failed');
			return ''; // string is corrupted, avoid to return
		} //end if
		//-- base64 :: because is not UTF-8 safe and may corrupt unicode characters
		return (string) base64_decode((string)$plainText);
		//--
	} //END FUNCTION
	//==============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================





//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

//--
// PHP implementation of the Blowfish algorithm in CBC mode
// It does not require any PHP extension, it uses only the core PHP.
// Class support encryption/decryption with or without a secret key.
//
// LICENSE: BSD, authors: Matthew Fonda <mfonda@php.net>, Philippe Jausions <jausions@php.net>
// (c) 2005-2008 Matthew Fonda
//
// Modified from the v.1.1.0 by unixman (iradu@unix-world.org), contains many fixes and make it unicode safe
// (c) 2015 unix-world.org
//--

/**
 * Class: SmartCryptoCipherBlowfishCBC
 * Provides a built-in based feature to handle the Blowfish (CBC) encryption / decryption.
 *
 * This provides an advanced crypto handler for Blowfish CBC algorithm.
 * It is recommended to use instead the SmartUtils::crypto_blowfish_encrypt() and SmartUtils::crypto_blowfish_decrypt() which are detecting from inits if to use the OpenSSL Blowfish (faster) or the built-in Blowfish (compatible with all platforms) classes of Blowfish CBC.
 *
 * @usage       dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints       Blowfish is a 64-bit (8 bytes) block cipher. Max Key is up to 56 chars length (56 bytes = 448 bits). The CBC mode requires a initialization vector (iv).
 *
 * @access 		private
 * @internal
 *
 * @depends     classes: Smart
 * @version     v.20200424
 *
 */
final class SmartCryptoCipherBlowfishCBC {

	// ->


	//==============================================================
	//--
	private $_P = array(); 	// P-Array contains 18 32-bit subkeys
	private $_S = array(); 	// Array of four S-Blocks each containing 256 32-bit entries
	//--
	private $_iv = null;	// Initialization vector
	private $_key = '';		// the key
	//--
	//==============================================================


	//==============================================================
	/**
	 * Constructor
	 * Initializes the blowfish cipher object, and gives a sets
	 * the secret key
	 *
	 * @param string $key
	 * @access public
	 */
	public function __construct($key) {
		//-- Blowfish uses a variable size key, ranging from 32 to 448 bits (4 to 56 characters)
		if((string)trim((string)$key) == '') {
			$key = (string) SMART_FRAMEWORK_SECURITY_KEY;
		} //end if
		//-- Blowfish key {{{SYNC-BLOWFISH-KEY}}}
		$this->_key = (string) substr((string)SmartHashCrypto::sha512((string)$key), 13, 29).strtoupper((string)substr((string)sha1((string)$key), 13, 10)).substr((string)md5((string)$key), 13, 9);
		//-- Blowfish iv {{{SYNC-BLOWFISH-IV}}}
		$this->_iv = (string) substr((string)base64_encode((string)sha1('@Smart.Framework-Crypto/BlowFish:'.$key.'#'.sha1('BlowFish-iv-SHA1'.$key).'-'.strtoupper((string)md5('BlowFish-iv-MD5'.$key)).'#')), 1, 8);
		//--
		$this->init();
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Get the Internal Key
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function getKey() {
		//--
		return (string) $this->_key;
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Get the Initialization Vector
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public function getIv() {
		//--
		return (string) $this->_iv;
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Encrypts a string in CBC Mode
	 *
	 * @param string $plainText
	 * @return string Returns cipher text
	 * @access public
	 */
	public function encrypt($plainText) {
		//--
		$plainText = (string) $plainText;
		//--
		$plainText = (string) base64_encode((string)$plainText); // b64 because is not UTF-8 safe and may corrupt unicode characters
		//-- {{{SYNC-BLOWFISH-CHECKSUM}}}
		$plainText .= '#CHECKSUM-SHA1#'.sha1($plainText);
		//--
		//== {{{SYNC-BLOWFISH-PADDING}}}
		//-- Blowfish is a 64-bit block cipher. This means that the data must be provided in units that are a multiple of 8 bytes
		$padding = 8 - (strlen($plainText) & 7);
		//-- unixman: fix: add spaces as padding as we have it as b64 encoded and will not modify the original
		for($i=0; $i<$padding; $i++) {
			$plainText .= ' '; // unixman (pad with spaces)
		} //end for
		//==
		//--
		$cipherText = '';
		$len = (int) strlen((string)$plainText);
		$plainText .= str_repeat(chr(0), (8 - ($len % 8)) % 8);
		//list(, $Xl, $Xr) = unpack('N2', substr($plainText, 0, 8) ^ $this->_iv);
		list($kk, $Xl, $Xr) = unpack('N2', substr($plainText, 0, 8) ^ $this->_iv); // FIX to be compatible with the upcoming PHP 7
		$this->_encipher($Xl, $Xr);
		$cipherText .= pack('N2', $Xl, $Xr);
		for($i = 8; $i < $len; $i += 8) {
			//list(, $Xl, $Xr) = unpack('N2', substr($plainText, $i, 8) ^ substr($cipherText, $i - 8, 8));
			list($kk, $Xl, $Xr) = unpack('N2', substr($plainText, $i, 8) ^ substr($cipherText, $i - 8, 8)); // FIX to be compatible with the upcoming PHP 7
			$this->_encipher($Xl, $Xr);
			$cipherText .= pack('N2', $Xl, $Xr);
		} //end for
		//--
		return (string) strtoupper((string)bin2hex((string)$cipherText));
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Decrypts an encrypted string in CBC Mode
	 *
	 * @param string $cipherText
	 * @return string Returns plain text
	 * @access public
	 */
	public function decrypt($cipherText) {
		//--
		if((!is_string($cipherText)) OR ((string)$cipherText == '')) {
			return '';
		} //end if
		//--
		$plainText = '';
		//--
		$cipherText = (string) @hex2bin(strtolower(trim((string)$cipherText)));
		//--
		if(strlen($cipherText) <= 0) {
			return '';
		} //end if
		//--
		$len = (int) strlen((string)$cipherText);
		$cipherText .= str_repeat(chr(0), (8 - ($len % 8)) % 8);
		//list(, $Xl, $Xr) = unpack('N2', substr($cipherText, 0, 8));
		list($kk, $Xl, $Xr) = unpack('N2', substr($cipherText, 0, 8)); // FIX to be compatible with the upcoming PHP 7
		$this->_decipher($Xl, $Xr);
		$plainText .= (pack('N2', $Xl, $Xr) ^ $this->_iv);
		for($i = 8; $i < $len; $i += 8) {
			//list(, $Xl, $Xr) = unpack('N2', substr($cipherText, $i, 8));
			list($kk, $Xl, $Xr) = unpack('N2', substr($cipherText, $i, 8)); // FIX to be compatible with the upcoming PHP 7
			$this->_decipher($Xl, $Xr);
			$plainText .= (pack('N2', $Xl, $Xr) ^ substr($cipherText, $i - 8, 8));
		} //end for
		//-- {{{SYNC-BLOWFISH-PADDING-TRIM}}} :: trim padding spaces
		$plainText = trim($plainText);
		//-- {{{SYNC-BLOWFISH-CHECKSUM}}}
		$arr = (array) explode('#CHECKSUM-SHA1#', $plainText);
		$plainText = (string) trim((string)$arr[0]);
		$checksum = (string) trim((string)$arr[1]);
		if((string)sha1($plainText) != (string)$checksum) {
			Smart::log_notice('SmartCryptoCipherBlowfishCBC/Decrypt: Checksum Failed');
			return ''; // string is corrupted, avoid to return
		} //end if
		//-- b64 because is not UTF-8 safe and may corrupt unicode characters
		return (string) base64_decode((string)$plainText);
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Sets the secret key
	 * The key must be non-zero, and less than or equal to
	 * 56 characters in length.
	 *
	 * @param string $key
	 * @return bool  Returns true on success
	 * @access public
	 */
	private function init() {
		//--
		$key = $this->_key;
		$len = strlen($key);
		//--
		$this->_init();
		//--
		$k = 0;
		$data = 0;
		$datal = 0;
		$datar = 0;
		//--
		for($i=0; $i<18; $i++) {
			$data = 0;
			for($j=4; $j>0; $j--) {
				$data = $data << 8 | ord($key[$k]); // fix for PHP 7.4
				$k = ($k+1) % $len;
			} //end for
			$this->_P[$i] ^= $data;
		} //end for
		//--
		for($i=0; $i<=16; $i+=2) {
			$this->_encipher($datal, $datar);
			$this->_P[$i] = $datal;
			$this->_P[$i+1] = $datar;
		} //end for
		//--
		for($i=0; $i<256; $i+=2) {
			$this->_encipher($datal, $datar);
			$this->_S[0][$i] = $datal;
			$this->_S[0][$i+1] = $datar;
		} //end for
		//--
		for($i=0; $i<256; $i+=2) {
			$this->_encipher($datal, $datar);
			$this->_S[1][$i] = $datal;
			$this->_S[1][$i+1] = $datar;
		} //end for
		//--
		for($i=0; $i<256; $i+=2) {
			$this->_encipher($datal, $datar);
			$this->_S[2][$i] = $datal;
			$this->_S[2][$i+1] = $datar;
		} //end for
		//--
		for($i=0; $i<256; $i+=2) {
			$this->_encipher($datal, $datar);
			$this->_S[3][$i] = $datal;
			$this->_S[3][$i+1] = $datar;
		} //end for
		//--
		return true;
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Enciphers a single 64 bit block
	 *
	 * @param int &$Xl
	 * @param int &$Xr
	 * @access private
	 */
	private function _encipher(&$Xl, &$Xr) {
		//--
		for($i = 0; $i < 16; $i++) {
			$temp = $Xl ^ $this->_P[$i];
			$Xl = ((($this->_S[0][($temp>>24) & 255] + $this->_S[1][($temp>>16) & 255]) ^ $this->_S[2][($temp>>8) & 255]) + $this->_S[3][$temp & 255]) ^ $Xr;
			$Xr = $temp;
		} //end for
		//--
		$Xr = $Xl ^ $this->_P[16];
		$Xl = $temp ^ $this->_P[17];
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Deciphers a single 64 bit block
	 *
	 * @param int &$Xl
	 * @param int &$Xr
	 * @access private
	 */
	private function _decipher(&$Xl, &$Xr) {
		//--
		for($i = 17; $i > 1; $i--) {
			$temp = $Xl ^ $this->_P[$i];
			$Xl = ((($this->_S[0][($temp>>24) & 255] + $this->_S[1][($temp>>16) & 255]) ^ $this->_S[2][($temp>>8) & 255]) + $this->_S[3][$temp & 255]) ^ $Xr;
			$Xr = $temp;
		} //end for
		//--
		$Xr = $Xl ^ $this->_P[1];
		$Xl = $temp ^ $this->_P[0];
		//--
	} //END FUNCTION
	//==============================================================


	//==============================================================
	/**
	 * Initializes the blowfish cipher object
	 *
	 * @access private
	 */
	private function _init() {
		//--
		$this->_P = array(
			0x243F6A88, 0x85A308D3, 0x13198A2E, 0x03707344,
			0xA4093822, 0x299F31D0, 0x082EFA98, 0xEC4E6C89,
			0x452821E6, 0x38D01377, 0xBE5466CF, 0x34E90C6C,
			0xC0AC29B7, 0xC97C50DD, 0x3F84D5B5, 0xB5470917,
			0x9216D5D9, 0x8979FB1B
		);
		//--
		$this->_S = array(
			array(
				0xD1310BA6, 0x98DFB5AC, 0x2FFD72DB, 0xD01ADFB7,
				0xB8E1AFED, 0x6A267E96, 0xBA7C9045, 0xF12C7F99,
				0x24A19947, 0xB3916CF7, 0x0801F2E2, 0x858EFC16,
				0x636920D8, 0x71574E69, 0xA458FEA3, 0xF4933D7E,
				0x0D95748F, 0x728EB658, 0x718BCD58, 0x82154AEE,
				0x7B54A41D, 0xC25A59B5, 0x9C30D539, 0x2AF26013,
				0xC5D1B023, 0x286085F0, 0xCA417918, 0xB8DB38EF,
				0x8E79DCB0, 0x603A180E, 0x6C9E0E8B, 0xB01E8A3E,
				0xD71577C1, 0xBD314B27, 0x78AF2FDA, 0x55605C60,
				0xE65525F3, 0xAA55AB94, 0x57489862, 0x63E81440,
				0x55CA396A, 0x2AAB10B6, 0xB4CC5C34, 0x1141E8CE,
				0xA15486AF, 0x7C72E993, 0xB3EE1411, 0x636FBC2A,
				0x2BA9C55D, 0x741831F6, 0xCE5C3E16, 0x9B87931E,
				0xAFD6BA33, 0x6C24CF5C, 0x7A325381, 0x28958677,
				0x3B8F4898, 0x6B4BB9AF, 0xC4BFE81B, 0x66282193,
				0x61D809CC, 0xFB21A991, 0x487CAC60, 0x5DEC8032,
				0xEF845D5D, 0xE98575B1, 0xDC262302, 0xEB651B88,
				0x23893E81, 0xD396ACC5, 0x0F6D6FF3, 0x83F44239,
				0x2E0B4482, 0xA4842004, 0x69C8F04A, 0x9E1F9B5E,
				0x21C66842, 0xF6E96C9A, 0x670C9C61, 0xABD388F0,
				0x6A51A0D2, 0xD8542F68, 0x960FA728, 0xAB5133A3,
				0x6EEF0B6C, 0x137A3BE4, 0xBA3BF050, 0x7EFB2A98,
				0xA1F1651D, 0x39AF0176, 0x66CA593E, 0x82430E88,
				0x8CEE8619, 0x456F9FB4, 0x7D84A5C3, 0x3B8B5EBE,
				0xE06F75D8, 0x85C12073, 0x401A449F, 0x56C16AA6,
				0x4ED3AA62, 0x363F7706, 0x1BFEDF72, 0x429B023D,
				0x37D0D724, 0xD00A1248, 0xDB0FEAD3, 0x49F1C09B,
				0x075372C9, 0x80991B7B, 0x25D479D8, 0xF6E8DEF7,
				0xE3FE501A, 0xB6794C3B, 0x976CE0BD, 0x04C006BA,
				0xC1A94FB6, 0x409F60C4, 0x5E5C9EC2, 0x196A2463,
				0x68FB6FAF, 0x3E6C53B5, 0x1339B2EB, 0x3B52EC6F,
				0x6DFC511F, 0x9B30952C, 0xCC814544, 0xAF5EBD09,
				0xBEE3D004, 0xDE334AFD, 0x660F2807, 0x192E4BB3,
				0xC0CBA857, 0x45C8740F, 0xD20B5F39, 0xB9D3FBDB,
				0x5579C0BD, 0x1A60320A, 0xD6A100C6, 0x402C7279,
				0x679F25FE, 0xFB1FA3CC, 0x8EA5E9F8, 0xDB3222F8,
				0x3C7516DF, 0xFD616B15, 0x2F501EC8, 0xAD0552AB,
				0x323DB5FA, 0xFD238760, 0x53317B48, 0x3E00DF82,
				0x9E5C57BB, 0xCA6F8CA0, 0x1A87562E, 0xDF1769DB,
				0xD542A8F6, 0x287EFFC3, 0xAC6732C6, 0x8C4F5573,
				0x695B27B0, 0xBBCA58C8, 0xE1FFA35D, 0xB8F011A0,
				0x10FA3D98, 0xFD2183B8, 0x4AFCB56C, 0x2DD1D35B,
				0x9A53E479, 0xB6F84565, 0xD28E49BC, 0x4BFB9790,
				0xE1DDF2DA, 0xA4CB7E33, 0x62FB1341, 0xCEE4C6E8,
				0xEF20CADA, 0x36774C01, 0xD07E9EFE, 0x2BF11FB4,
				0x95DBDA4D, 0xAE909198, 0xEAAD8E71, 0x6B93D5A0,
				0xD08ED1D0, 0xAFC725E0, 0x8E3C5B2F, 0x8E7594B7,
				0x8FF6E2FB, 0xF2122B64, 0x8888B812, 0x900DF01C,
				0x4FAD5EA0, 0x688FC31C, 0xD1CFF191, 0xB3A8C1AD,
				0x2F2F2218, 0xBE0E1777, 0xEA752DFE, 0x8B021FA1,
				0xE5A0CC0F, 0xB56F74E8, 0x18ACF3D6, 0xCE89E299,
				0xB4A84FE0, 0xFD13E0B7, 0x7CC43B81, 0xD2ADA8D9,
				0x165FA266, 0x80957705, 0x93CC7314, 0x211A1477,
				0xE6AD2065, 0x77B5FA86, 0xC75442F5, 0xFB9D35CF,
				0xEBCDAF0C, 0x7B3E89A0, 0xD6411BD3, 0xAE1E7E49,
				0x00250E2D, 0x2071B35E, 0x226800BB, 0x57B8E0AF,
				0x2464369B, 0xF009B91E, 0x5563911D, 0x59DFA6AA,
				0x78C14389, 0xD95A537F, 0x207D5BA2, 0x02E5B9C5,
				0x83260376, 0x6295CFA9, 0x11C81968, 0x4E734A41,
				0xB3472DCA, 0x7B14A94A, 0x1B510052, 0x9A532915,
				0xD60F573F, 0xBC9BC6E4, 0x2B60A476, 0x81E67400,
				0x08BA6FB5, 0x571BE91F, 0xF296EC6B, 0x2A0DD915,
				0xB6636521, 0xE7B9F9B6, 0xFF34052E, 0xC5855664,
				0x53B02D5D, 0xA99F8FA1, 0x08BA4799, 0x6E85076A
			),
			array(
				0x4B7A70E9, 0xB5B32944, 0xDB75092E, 0xC4192623,
				0xAD6EA6B0, 0x49A7DF7D, 0x9CEE60B8, 0x8FEDB266,
				0xECAA8C71, 0x699A17FF, 0x5664526C, 0xC2B19EE1,
				0x193602A5, 0x75094C29, 0xA0591340, 0xE4183A3E,
				0x3F54989A, 0x5B429D65, 0x6B8FE4D6, 0x99F73FD6,
				0xA1D29C07, 0xEFE830F5, 0x4D2D38E6, 0xF0255DC1,
				0x4CDD2086, 0x8470EB26, 0x6382E9C6, 0x021ECC5E,
				0x09686B3F, 0x3EBAEFC9, 0x3C971814, 0x6B6A70A1,
				0x687F3584, 0x52A0E286, 0xB79C5305, 0xAA500737,
				0x3E07841C, 0x7FDEAE5C, 0x8E7D44EC, 0x5716F2B8,
				0xB03ADA37, 0xF0500C0D, 0xF01C1F04, 0x0200B3FF,
				0xAE0CF51A, 0x3CB574B2, 0x25837A58, 0xDC0921BD,
				0xD19113F9, 0x7CA92FF6, 0x94324773, 0x22F54701,
				0x3AE5E581, 0x37C2DADC, 0xC8B57634, 0x9AF3DDA7,
				0xA9446146, 0x0FD0030E, 0xECC8C73E, 0xA4751E41,
				0xE238CD99, 0x3BEA0E2F, 0x3280BBA1, 0x183EB331,
				0x4E548B38, 0x4F6DB908, 0x6F420D03, 0xF60A04BF,
				0x2CB81290, 0x24977C79, 0x5679B072, 0xBCAF89AF,
				0xDE9A771F, 0xD9930810, 0xB38BAE12, 0xDCCF3F2E,
				0x5512721F, 0x2E6B7124, 0x501ADDE6, 0x9F84CD87,
				0x7A584718, 0x7408DA17, 0xBC9F9ABC, 0xE94B7D8C,
				0xEC7AEC3A, 0xDB851DFA, 0x63094366, 0xC464C3D2,
				0xEF1C1847, 0x3215D908, 0xDD433B37, 0x24C2BA16,
				0x12A14D43, 0x2A65C451, 0x50940002, 0x133AE4DD,
				0x71DFF89E, 0x10314E55, 0x81AC77D6, 0x5F11199B,
				0x043556F1, 0xD7A3C76B, 0x3C11183B, 0x5924A509,
				0xF28FE6ED, 0x97F1FBFA, 0x9EBABF2C, 0x1E153C6E,
				0x86E34570, 0xEAE96FB1, 0x860E5E0A, 0x5A3E2AB3,
				0x771FE71C, 0x4E3D06FA, 0x2965DCB9, 0x99E71D0F,
				0x803E89D6, 0x5266C825, 0x2E4CC978, 0x9C10B36A,
				0xC6150EBA, 0x94E2EA78, 0xA5FC3C53, 0x1E0A2DF4,
				0xF2F74EA7, 0x361D2B3D, 0x1939260F, 0x19C27960,
				0x5223A708, 0xF71312B6, 0xEBADFE6E, 0xEAC31F66,
				0xE3BC4595, 0xA67BC883, 0xB17F37D1, 0x018CFF28,
				0xC332DDEF, 0xBE6C5AA5, 0x65582185, 0x68AB9802,
				0xEECEA50F, 0xDB2F953B, 0x2AEF7DAD, 0x5B6E2F84,
				0x1521B628, 0x29076170, 0xECDD4775, 0x619F1510,
				0x13CCA830, 0xEB61BD96, 0x0334FE1E, 0xAA0363CF,
				0xB5735C90, 0x4C70A239, 0xD59E9E0B, 0xCBAADE14,
				0xEECC86BC, 0x60622CA7, 0x9CAB5CAB, 0xB2F3846E,
				0x648B1EAF, 0x19BDF0CA, 0xA02369B9, 0x655ABB50,
				0x40685A32, 0x3C2AB4B3, 0x319EE9D5, 0xC021B8F7,
				0x9B540B19, 0x875FA099, 0x95F7997E, 0x623D7DA8,
				0xF837889A, 0x97E32D77, 0x11ED935F, 0x16681281,
				0x0E358829, 0xC7E61FD6, 0x96DEDFA1, 0x7858BA99,
				0x57F584A5, 0x1B227263, 0x9B83C3FF, 0x1AC24696,
				0xCDB30AEB, 0x532E3054, 0x8FD948E4, 0x6DBC3128,
				0x58EBF2EF, 0x34C6FFEA, 0xFE28ED61, 0xEE7C3C73,
				0x5D4A14D9, 0xE864B7E3, 0x42105D14, 0x203E13E0,
				0x45EEE2B6, 0xA3AAABEA, 0xDB6C4F15, 0xFACB4FD0,
				0xC742F442, 0xEF6ABBB5, 0x654F3B1D, 0x41CD2105,
				0xD81E799E, 0x86854DC7, 0xE44B476A, 0x3D816250,
				0xCF62A1F2, 0x5B8D2646, 0xFC8883A0, 0xC1C7B6A3,
				0x7F1524C3, 0x69CB7492, 0x47848A0B, 0x5692B285,
				0x095BBF00, 0xAD19489D, 0x1462B174, 0x23820E00,
				0x58428D2A, 0x0C55F5EA, 0x1DADF43E, 0x233F7061,
				0x3372F092, 0x8D937E41, 0xD65FECF1, 0x6C223BDB,
				0x7CDE3759, 0xCBEE7460, 0x4085F2A7, 0xCE77326E,
				0xA6078084, 0x19F8509E, 0xE8EFD855, 0x61D99735,
				0xA969A7AA, 0xC50C06C2, 0x5A04ABFC, 0x800BCADC,
				0x9E447A2E, 0xC3453484, 0xFDD56705, 0x0E1E9EC9,
				0xDB73DBD3, 0x105588CD, 0x675FDA79, 0xE3674340,
				0xC5C43465, 0x713E38D8, 0x3D28F89E, 0xF16DFF20,
				0x153E21E7, 0x8FB03D4A, 0xE6E39F2B, 0xDB83ADF7
			),
			array(
				0xE93D5A68, 0x948140F7, 0xF64C261C, 0x94692934,
				0x411520F7, 0x7602D4F7, 0xBCF46B2E, 0xD4A20068,
				0xD4082471, 0x3320F46A, 0x43B7D4B7, 0x500061AF,
				0x1E39F62E, 0x97244546, 0x14214F74, 0xBF8B8840,
				0x4D95FC1D, 0x96B591AF, 0x70F4DDD3, 0x66A02F45,
				0xBFBC09EC, 0x03BD9785, 0x7FAC6DD0, 0x31CB8504,
				0x96EB27B3, 0x55FD3941, 0xDA2547E6, 0xABCA0A9A,
				0x28507825, 0x530429F4, 0x0A2C86DA, 0xE9B66DFB,
				0x68DC1462, 0xD7486900, 0x680EC0A4, 0x27A18DEE,
				0x4F3FFEA2, 0xE887AD8C, 0xB58CE006, 0x7AF4D6B6,
				0xAACE1E7C, 0xD3375FEC, 0xCE78A399, 0x406B2A42,
				0x20FE9E35, 0xD9F385B9, 0xEE39D7AB, 0x3B124E8B,
				0x1DC9FAF7, 0x4B6D1856, 0x26A36631, 0xEAE397B2,
				0x3A6EFA74, 0xDD5B4332, 0x6841E7F7, 0xCA7820FB,
				0xFB0AF54E, 0xD8FEB397, 0x454056AC, 0xBA489527,
				0x55533A3A, 0x20838D87, 0xFE6BA9B7, 0xD096954B,
				0x55A867BC, 0xA1159A58, 0xCCA92963, 0x99E1DB33,
				0xA62A4A56, 0x3F3125F9, 0x5EF47E1C, 0x9029317C,
				0xFDF8E802, 0x04272F70, 0x80BB155C, 0x05282CE3,
				0x95C11548, 0xE4C66D22, 0x48C1133F, 0xC70F86DC,
				0x07F9C9EE, 0x41041F0F, 0x404779A4, 0x5D886E17,
				0x325F51EB, 0xD59BC0D1, 0xF2BCC18F, 0x41113564,
				0x257B7834, 0x602A9C60, 0xDFF8E8A3, 0x1F636C1B,
				0x0E12B4C2, 0x02E1329E, 0xAF664FD1, 0xCAD18115,
				0x6B2395E0, 0x333E92E1, 0x3B240B62, 0xEEBEB922,
				0x85B2A20E, 0xE6BA0D99, 0xDE720C8C, 0x2DA2F728,
				0xD0127845, 0x95B794FD, 0x647D0862, 0xE7CCF5F0,
				0x5449A36F, 0x877D48FA, 0xC39DFD27, 0xF33E8D1E,
				0x0A476341, 0x992EFF74, 0x3A6F6EAB, 0xF4F8FD37,
				0xA812DC60, 0xA1EBDDF8, 0x991BE14C, 0xDB6E6B0D,
				0xC67B5510, 0x6D672C37, 0x2765D43B, 0xDCD0E804,
				0xF1290DC7, 0xCC00FFA3, 0xB5390F92, 0x690FED0B,
				0x667B9FFB, 0xCEDB7D9C, 0xA091CF0B, 0xD9155EA3,
				0xBB132F88, 0x515BAD24, 0x7B9479BF, 0x763BD6EB,
				0x37392EB3, 0xCC115979, 0x8026E297, 0xF42E312D,
				0x6842ADA7, 0xC66A2B3B, 0x12754CCC, 0x782EF11C,
				0x6A124237, 0xB79251E7, 0x06A1BBE6, 0x4BFB6350,
				0x1A6B1018, 0x11CAEDFA, 0x3D25BDD8, 0xE2E1C3C9,
				0x44421659, 0x0A121386, 0xD90CEC6E, 0xD5ABEA2A,
				0x64AF674E, 0xDA86A85F, 0xBEBFE988, 0x64E4C3FE,
				0x9DBC8057, 0xF0F7C086, 0x60787BF8, 0x6003604D,
				0xD1FD8346, 0xF6381FB0, 0x7745AE04, 0xD736FCCC,
				0x83426B33, 0xF01EAB71, 0xB0804187, 0x3C005E5F,
				0x77A057BE, 0xBDE8AE24, 0x55464299, 0xBF582E61,
				0x4E58F48F, 0xF2DDFDA2, 0xF474EF38, 0x8789BDC2,
				0x5366F9C3, 0xC8B38E74, 0xB475F255, 0x46FCD9B9,
				0x7AEB2661, 0x8B1DDF84, 0x846A0E79, 0x915F95E2,
				0x466E598E, 0x20B45770, 0x8CD55591, 0xC902DE4C,
				0xB90BACE1, 0xBB8205D0, 0x11A86248, 0x7574A99E,
				0xB77F19B6, 0xE0A9DC09, 0x662D09A1, 0xC4324633,
				0xE85A1F02, 0x09F0BE8C, 0x4A99A025, 0x1D6EFE10,
				0x1AB93D1D, 0x0BA5A4DF, 0xA186F20F, 0x2868F169,
				0xDCB7DA83, 0x573906FE, 0xA1E2CE9B, 0x4FCD7F52,
				0x50115E01, 0xA70683FA, 0xA002B5C4, 0x0DE6D027,
				0x9AF88C27, 0x773F8641, 0xC3604C06, 0x61A806B5,
				0xF0177A28, 0xC0F586E0, 0x006058AA, 0x30DC7D62,
				0x11E69ED7, 0x2338EA63, 0x53C2DD94, 0xC2C21634,
				0xBBCBEE56, 0x90BCB6DE, 0xEBFC7DA1, 0xCE591D76,
				0x6F05E409, 0x4B7C0188, 0x39720A3D, 0x7C927C24,
				0x86E3725F, 0x724D9DB9, 0x1AC15BB4, 0xD39EB8FC,
				0xED545578, 0x08FCA5B5, 0xD83D7CD3, 0x4DAD0FC4,
				0x1E50EF5E, 0xB161E6F8, 0xA28514D9, 0x6C51133C,
				0x6FD5C7E7, 0x56E14EC4, 0x362ABFCE, 0xDDC6C837,
				0xD79A3234, 0x92638212, 0x670EFA8E, 0x406000E0
			),
			array(
				0x3A39CE37, 0xD3FAF5CF, 0xABC27737, 0x5AC52D1B,
				0x5CB0679E, 0x4FA33742, 0xD3822740, 0x99BC9BBE,
				0xD5118E9D, 0xBF0F7315, 0xD62D1C7E, 0xC700C47B,
				0xB78C1B6B, 0x21A19045, 0xB26EB1BE, 0x6A366EB4,
				0x5748AB2F, 0xBC946E79, 0xC6A376D2, 0x6549C2C8,
				0x530FF8EE, 0x468DDE7D, 0xD5730A1D, 0x4CD04DC6,
				0x2939BBDB, 0xA9BA4650, 0xAC9526E8, 0xBE5EE304,
				0xA1FAD5F0, 0x6A2D519A, 0x63EF8CE2, 0x9A86EE22,
				0xC089C2B8, 0x43242EF6, 0xA51E03AA, 0x9CF2D0A4,
				0x83C061BA, 0x9BE96A4D, 0x8FE51550, 0xBA645BD6,
				0x2826A2F9, 0xA73A3AE1, 0x4BA99586, 0xEF5562E9,
				0xC72FEFD3, 0xF752F7DA, 0x3F046F69, 0x77FA0A59,
				0x80E4A915, 0x87B08601, 0x9B09E6AD, 0x3B3EE593,
				0xE990FD5A, 0x9E34D797, 0x2CF0B7D9, 0x022B8B51,
				0x96D5AC3A, 0x017DA67D, 0xD1CF3ED6, 0x7C7D2D28,
				0x1F9F25CF, 0xADF2B89B, 0x5AD6B472, 0x5A88F54C,
				0xE029AC71, 0xE019A5E6, 0x47B0ACFD, 0xED93FA9B,
				0xE8D3C48D, 0x283B57CC, 0xF8D56629, 0x79132E28,
				0x785F0191, 0xED756055, 0xF7960E44, 0xE3D35E8C,
				0x15056DD4, 0x88F46DBA, 0x03A16125, 0x0564F0BD,
				0xC3EB9E15, 0x3C9057A2, 0x97271AEC, 0xA93A072A,
				0x1B3F6D9B, 0x1E6321F5, 0xF59C66FB, 0x26DCF319,
				0x7533D928, 0xB155FDF5, 0x03563482, 0x8ABA3CBB,
				0x28517711, 0xC20AD9F8, 0xABCC5167, 0xCCAD925F,
				0x4DE81751, 0x3830DC8E, 0x379D5862, 0x9320F991,
				0xEA7A90C2, 0xFB3E7BCE, 0x5121CE64, 0x774FBE32,
				0xA8B6E37E, 0xC3293D46, 0x48DE5369, 0x6413E680,
				0xA2AE0810, 0xDD6DB224, 0x69852DFD, 0x09072166,
				0xB39A460A, 0x6445C0DD, 0x586CDECF, 0x1C20C8AE,
				0x5BBEF7DD, 0x1B588D40, 0xCCD2017F, 0x6BB4E3BB,
				0xDDA26A7E, 0x3A59FF45, 0x3E350A44, 0xBCB4CDD5,
				0x72EACEA8, 0xFA6484BB, 0x8D6612AE, 0xBF3C6F47,
				0xD29BE463, 0x542F5D9E, 0xAEC2771B, 0xF64E6370,
				0x740E0D8D, 0xE75B1357, 0xF8721671, 0xAF537D5D,
				0x4040CB08, 0x4EB4E2CC, 0x34D2466A, 0x0115AF84,
				0xE1B00428, 0x95983A1D, 0x06B89FB4, 0xCE6EA048,
				0x6F3F3B82, 0x3520AB82, 0x011A1D4B, 0x277227F8,
				0x611560B1, 0xE7933FDC, 0xBB3A792B, 0x344525BD,
				0xA08839E1, 0x51CE794B, 0x2F32C9B7, 0xA01FBAC9,
				0xE01CC87E, 0xBCC7D1F6, 0xCF0111C3, 0xA1E8AAC7,
				0x1A908749, 0xD44FBD9A, 0xD0DADECB, 0xD50ADA38,
				0x0339C32A, 0xC6913667, 0x8DF9317C, 0xE0B12B4F,
				0xF79E59B7, 0x43F5BB3A, 0xF2D519FF, 0x27D9459C,
				0xBF97222C, 0x15E6FC2A, 0x0F91FC71, 0x9B941525,
				0xFAE59361, 0xCEB69CEB, 0xC2A86459, 0x12BAA8D1,
				0xB6C1075E, 0xE3056A0C, 0x10D25065, 0xCB03A442,
				0xE0EC6E0E, 0x1698DB3B, 0x4C98A0BE, 0x3278E964,
				0x9F1F9532, 0xE0D392DF, 0xD3A0342B, 0x8971F21E,
				0x1B0A7441, 0x4BA3348C, 0xC5BE7120, 0xC37632D8,
				0xDF359F8D, 0x9B992F2E, 0xE60B6F47, 0x0FE3F11D,
				0xE54CDA54, 0x1EDAD891, 0xCE6279CF, 0xCD3E7E6F,
				0x1618B166, 0xFD2C1D05, 0x848FD2C5, 0xF6FB2299,
				0xF523F357, 0xA6327623, 0x93A83531, 0x56CCCD02,
				0xACF08162, 0x5A75EBB5, 0x6E163697, 0x88D273CC,
				0xDE966292, 0x81B949D0, 0x4C50901B, 0x71C65614,
				0xE6C6C7BD, 0x327A140A, 0x45E1D006, 0xC3F27B9A,
				0xC9AA53FD, 0x62A80F00, 0xBB25BFE2, 0x35BDD2F6,
				0x71126905, 0xB2040222, 0xB6CBCF7C, 0xCD769C2B,
				0x53113EC0, 0x1640E3D3, 0x38ABBD60, 0x2547ADF0,
				0xBA38209C, 0xF746CE76, 0x77AFA1C5, 0x20756060,
				0x85CBFE4E, 0x8AE88DD8, 0x7AAAF9B0, 0x4CF9AA7E,
				0x1948C25C, 0x02FB8A8C, 0x01C36AE4, 0xD6EBE1F9,
				0x90D4F869, 0xA65CDEA0, 0x3F09252D, 0xC208E69F,
				0xB74E6132, 0xCE77E25B, 0x578FDFE3, 0x3AC372E6
			)
		);
		//--
	} //END FUNCTION
	//==============================================================


} //END CLASS


/*** Sample Usage
$bf = new SmartCryptoCipherBlowfishCBC('some secret key!');
$encrypted = $bf->encrypt('this is some example plain text');
$plaintext = $bf->decrypt($encrypted);
echo "plain text: $plaintext";
*/


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

// provide the (WEAK but FAST :: symetrical :: HASH) cryptography support // (ENCRYPT + DECRYPT)
// v.1.2.1 (unixworld)
// Simple but secure encryption based on hash functions
// Basically this algorithm provides a block cipher in OFB mode (output feedback mode)
// requires sha1 function in PHP
// based on :: Quadracom's class v.1.0

/**
 * Class Smart Crypto Hash Encryption
 * This class uses a dynamic generated initialization vector based on Visitor Data thus will cannot be used between different visits ...
 * The purpose of this class is to encrypt / decrypt just per visit data
 *
 * @usage       dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @access 		private
 * @internal
 *
 * @depends     classes: Smart
 * @version     v.20200424
 *
 */
final class SmartCryptoCipherHash {

	// ->


	//========================================
	// @ PRIVATE
	private $hash_key;			// @var	string :: Hashed value of the user provided encryption key
	// @ PRIVATE
	private $hash_length;		// @var	int :: String length of hashed values using the current algorithm
	// @PRIVATE
	private $mode;				// @var enum :: md5, sha1, sha256, sha384, sha512
	//========================================


	//==============================================================
	 // Constructor method
	 // Used to set key for encryption and decryption.
	 // @param	string	$key	Your secret key used for encryption and decryption
	 // @return mixed
	public function __construct($key, $mode='sha256') {

		// fix for empty key
		$key = (string) $key;
		if((string)trim((string)$key) == '') {
			$key = (string) SMART_FRAMEWORK_SECURITY_KEY;
		} //end if

		// for the case: hash/sha256
		if((string)substr((string)$mode, 0, 5) == 'hash/') {
			$cfgcrypto = '';
			if(defined('SMART_FRAMEWORK_SECURITY_CRYPTO')) {
				$cfgcrypto = (string) SMART_FRAMEWORK_SECURITY_CRYPTO;
			} //end if
			$arr = (array) explode('/', (string)$cfgcrypto);
			$mode = (string) trim((string)$arr[1]);
		} //end if

		// mode
		switch((string)$mode) {
			case 'md5':
				$this->mode = 'md5';
				break;
			case 'sha1':
				$this->mode = 'sha1'; // default
				break;
			case 'sha384':
				$this->mode = 'sha384';
				break;
			case 'sha512':
				$this->mode = 'sha512';
				break;
			case 'sha256':
			default:
				$this->mode = 'sha256';
		} //end switch

		// Instead of using the key directly we compress it using a hash function
		$this->hash_key = (string) $this->_hash($key);

		// Remember length of hashvalues for later use
		$this->hash_length = (int) strlen((string)$this->hash_key);

	} //END FUNCTION
	//==============================================================


	//==============================================================
	// [PUBLIC]
	 // Method used for encryption
	 // @param	string	$string	Message to be encrypted
	 // @return string	Encrypted message
	public function encrypt($string) {

		$string = (string) base64_encode((string)$string); // this is required because it cannot handle unicode characters
		$string = (string) $string.'#CHECKSUM-MD5#'.md5((string)$string);

		// gen IV
		$iv = $this->_generate_iv();

		// Clear output
		$out = '';

		// First block of output is ($this->hash_hey XOR IV)
		for($c=0;$c < $this->hash_length;$c++) {
			$out .= chr(ord($iv[$c]) ^ ord($this->hash_key[$c]));
		} //end for

		// Use IV as first key
		$key = $iv;
		$c = 0;

		// Go through input string
		while($c < strlen($string)) {
			// If we have used all characters of the current key we switch to a new one
			if(($c != 0) and ($c % $this->hash_length == 0)) {
				// New key is (Last block of plaintext XOR current Key)
				$key = $this->_hash($key.substr($string,$c - $this->hash_length,$this->hash_length));
			} //end if
			// Generate output by xor-ing input and key character for character
			$out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
			$c++;
		} //end while

		return (string) strtoupper((string)bin2hex((string)$out));

	} //END FUNCTION
	//==============================================================


	//==============================================================
	// [PUBLIC]
	 // Method used for decryption
	 // @param	string	$string	Message to be decrypted
	 // @return string	Decrypted message
	public function decrypt($string) {

		$string = (string) @hex2bin(strtolower(trim((string)$string)));

		//-- Extract encrypted IV from input
		$tmp_iv = substr($string, 0, $this->hash_length);
		//-- Extract encrypted message from input
		$string = substr($string, $this->hash_length, (strlen($string) - $this->hash_length));
		//--
		$iv = '';
		$out = '';
		//--

		//-- Regenerate IV by xor-ing encrypted IV from block 1 and $this->hashed_key :: Mathematics: (IV XOR KeY) XOR Key = IV
		for($c=0;$c < $this->hash_length;$c++) {
			$iv .= chr(ord($tmp_iv[$c]) ^ ord($this->hash_key[$c]));
		} //end for
		//-- Use IV as key for decrypting the first block cyphertext
		$key = $iv;
		$c = 0;
		//--

		//-- Loop through the whole input string
		while($c < strlen($string)) {
			//-- If we have used all characters of the current key we switch to a new one
			if(($c != 0) and ($c % $this->hash_length == 0)) {
				// New key is (Last block of recovered plaintext XOR current Key)
				$key = $this->_hash($key.substr($out,$c - $this->hash_length,$this->hash_length));
			} //end if
			//-- Generate output by xor-ing input and key character for character
			$out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
			//--
			$c++;
			//--
		} //end while
		//--

		//--
		$arr = (array) explode('#CHECKSUM-MD5#', $out);
		$out = (string) trim((string)$arr[0]);
		$chk = (string) trim((string)$arr[1]);
		//--
		if((string)md5($out) == (string)$chk) {
			$out = (string) base64_decode((string)$out);
		} else {
			$out = ''; // invalid checksum
		} //end if
		//--

		//--
		return (string) $out;
		//--

	} //END FUNCTION
	//==============================================================


	//==============================================================
	//============================================================== PRIVATES
	//==============================================================


	//==============================================================
	 // Hashfunction used for encryption
	 // This class hashes any given string using the best available hash algorithm.
	 // Default is using sha1, but it is not the best recommended ...
	 // @access	private
	 // @param	string	$string	Message to hashed
	 // @return string	Hash value of input message
	private function _hash($string) {

		// force use sha1() encryption (unixman)
		//$result = sha1($string);
		//$out ='';
		// Convert hexadecimal hash value to binary string
		//for($c=0;$c<strlen($result);$c+=2) {
		//	$out .= chr(hexdec($result[$c].$result[$c+1]));
		//} //end for
		//return $out;

		switch((string)$this->mode) { // enhancement by unixman
			case 'md5':
				$result = SmartHashCrypto::md5($string);
				break;
			case 'sha1':
				$result = SmartHashCrypto::sha1($string);
				break;
			case 'sha256':
				$result = SmartHashCrypto::sha256($string);
				break;
			case 'sha384':
				$result = SmartHashCrypto::sha384($string);
				break;
			case 'sha512':
				$result = SmartHashCrypto::sha512($string);
				break;
			default:
				Smart::log_warning('ERROR: Invalid mode for: SmartCryptoCipherHash / _hash: '.$this->mode.' ; Using sha1()');
				$result = (string) sha1((string)$string);
		} //end switch

		return (string) @hex2bin((string)$result); // convert hexadecimal hash value to binary string

	} //END FUNCTION
	//==============================================================


	//==============================================================
	 // Generate a random string to initialize encryption
	 // This method will return a random binary string IV ( = initialization vector).
	 // The randomness of this string is one of the crucial points of this algorithm as it
	 // is the basis of encryption. The encrypted IV will be added to the encrypted message
	 // to make decryption possible. The transmitted IV will be encoded using the user provided key.
	 // @todo	Add more random sources.
	 // @access	private
	 // @see function	hash encryption
	 // @return string	Binary pseudo random string
	private function _generate_iv() {

		// Initialize pseudo random generator
		// seed rand: (double)microtime()*1000000 // no more needed

		// Collect very random data.
		// Add as many "pseudo" random sources as you can find.
		// Possible sources: Memory usage, diskusage, file and directory content...
		$iv =  (string) Smart::random_number();
		$iv .= (string) Smart::unique_entropy();
		$iv .= (string) SmartUtils::get_visitor_tracking_uid();
		$iv .= (string) implode("\r", (array)$_SERVER);
		$iv .= (string) implode("\r", (array)$_COOKIE);

		return $this->_hash($iv);

	} //END FUNCTION
	//==============================================================


	//------------------------------------
	//-- # EXAMPLE USAGE:
	// $crypt = new SmartCryptoCipherHash('the secret ...');
	// $enc_text = $crypt->encrypt('text to be encrypted');
	// $dec_text = $crypt->decrypt($enc_text);
	//-- # WARNING: !!! The $encrypted WILL BE ALWAYS (ALMOST) DIFFERENT !!!
	//------------------------------------


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
