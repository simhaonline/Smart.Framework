<?php
// [LIB - SmartFramework / Text Translations]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Regional Text
// DEPENDS:
//	* Smart::
//	* SmartPersistentCache::
//	* SmartAdapterTextTranslations:: *BOOTSTRAP*
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class Smart Text Translations.
 * It provides a Language Based Text Translations Layer for the Smart.Framework based Applications.
 *
 * <code>
 * // Usage example:
 * SmartTextTranslations::some_method_of_this_class(...);
 * </code>
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	classes: Smart, SmartPersistentCache, SmartAdapterTextTranslations
 * @version 	v.181218
 * @package 	Application
 *
 */
final class SmartTextTranslations {

	// ::

	private static $cache = array();
	private static $translators = array();


	//=====
	/**
	 * Regional Text :: Get Available Languages
	 *
	 * @return 	ARRAY						:: The array with available language IDs ; sample: ['en', 'ro']
	 */
	public static function getAvailableLanguages() {
		//--
		$all_languages = (array) self::getSafeLanguagesArr();
		//--
		return (array) array_keys((array)$all_languages);
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Get (available) Languages List
	 *
	 * @return 	ARRAY						:: The array with available languages List ['en' => 'English', 'ro' => 'Romanian']
	 */
	public static function getListOfLanguages() {
		//--
		$all_languages = (array) self::getSafeLanguagesArr();
		//--
		$list_languages = array();
		foreach($all_languages as $key => $val) {
			if(is_array($val)) {
				$list_languages[(string)$key] = (string) $val['name'];
			} else {
				$list_languages[(string)$key] = (string) $val;
			} //end if
		} //end for
		//--
		return (array) $list_languages;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Checks if the Current Language is the Default Language for the current session
	 *
	 * @return 	BOOLEAN						:: Returns TRUE if the Current Language is the Default Language for the current session otherwise returns FALSE
	 */
	public static function isDefaultLanguage() {
		//--
		if((string)self::getLanguage() == (string)self::getDefaultLanguage()) {
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Get the Default Language for the current session as Set by Init
	 *
	 * @return 	STRING						:: The language ID ; sample (for English) will return: 'en'
	 */
	public static function getDefaultLanguage() {
		//--
		$lang = 'en';
		//--
		if(defined('SMART_FRAMEWORK_DEFAULT_LANG')) {
			if(self::validateLanguage((string)SMART_FRAMEWORK_DEFAULT_LANG)) {
				$lang = (string) SMART_FRAMEWORK_DEFAULT_LANG;
			} else {
				Smart::raise_error(
					'Invalid Default Language set in SMART_FRAMEWORK_DEFAULT_LANG: '.SMART_FRAMEWORK_DEFAULT_LANG,
					'Invalid Default Language Set in Configs' // msg to display
				);
			} //end if
		} //end if
		//--
		return (string) $lang;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Get the Current Language for the current session as Set by Config / URL / Cookie / Method-Set
	 *
	 * @return 	STRING						:: The language ID ; sample (for English) will return: 'en'
	 */
	public static function getLanguage() {
		//--
		global $configs;
		//--
		if(strlen((string)self::$cache['#LANGUAGE#']) == 2) {
			if(SmartFrameworkRuntime::ifInternalDebug()) {
				if(SmartFrameworkRuntime::ifDebug()) {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Get Language from Internal Cache',
						'data' => 'Content: '.self::$cache['#LANGUAGE#']
					]);
				} //end if
			} //end if
			return (string) self::$cache['#LANGUAGE#'];
		} //end if
		//--
		$the_lang = 'en'; // default
		//--
		if(is_array($configs)) {
			if(is_array($configs['regional'])) {
				$tmp_lang = (string) strtolower((string)$configs['regional']['language-id']);
				if(self::validateLanguage($tmp_lang)) {
					$the_lang = (string) $tmp_lang;
					if(SmartFrameworkRuntime::ifInternalDebug()) {
						if(SmartFrameworkRuntime::ifDebug()) {
							SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
								'title' => 'Get Language from Configs',
								'data' => 'Content: '.$the_lang
							]);
						} //end if
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		self::$cache['#LANGUAGE#'] = (string) strtolower((string)$the_lang);
		//--
		return (string) self::$cache['#LANGUAGE#'];
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Set the Language for current session
	 *
	 * @param 	STRING 	$y_language 		:: The language ID ; sample (for English) will be: 'en'
	 *
	 * @return 	BOOLEAN						:: TRUE if successful, FALSE if not
	 */
	public static function setLanguage($y_language) {
		//--
		global $configs;
		//--
		$result = false;
		//--
		$all_languages = (array) self::getSafeLanguagesArr();
		//--
		$tmp_lang = (string) strtolower((string)SmartUnicode::utf8_to_iso((string)$y_language));
		//--
		if(is_array($configs)) {
			if(strlen((string)$tmp_lang) == 2) { // if language id have only 2 characters
				if(preg_match('/^[a-z]+$/', (string)$tmp_lang)) { // language id must contain only a..z characters (iso-8859-1)
					if(is_array($all_languages)) {
						if($all_languages[(string)$tmp_lang]) { // if that lang is set in languages array
							if((string)$tmp_lang != (string)$configs['regional']['language-id']) { // if it is the same, don't make sense to set it again !
								$configs['regional']['language-id'] = (string) $tmp_lang;
								if(Smart::array_size($all_languages[(string)$tmp_lang]) > 0) {
									// set also the rest of regional params if available and set custom for that language ...
									foreach($all_languages[(string)$tmp_lang] as $k => $v) {
										if(array_key_exists((string)$k, (array)$configs['regional'])) {
											//Smart::log_notice('Setting Regional Key for Language: '.$tmp_lang.' as @ '.$k.'='.$v);
											$configs['regional'][(string)$k] = (string) $v;
										} //end if
									} //end foreach
								} //end if
								self::$cache['#LANGUAGE#'] = (string) $tmp_lang;
								$result = true;
								if(SmartFrameworkRuntime::ifInternalDebug()) {
									if(SmartFrameworkRuntime::ifDebug()) {
										SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
											'title' => 'Set Language in Configs and Internal Cache',
											'data' => 'Content: '.$the_lang
										]);
									} //end if
								} //end if
							} //end if
						} //end if
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		return (bool) $result;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Validate Language
	 *
	 * @param 	STRING 	$y_language 		:: The language ID ; sample (for English) will be: 'en'
	 *
	 * @return 	BOOLEAN						:: TRUE if language defined in configs, FALSE if not
	 *
	 */
	public static function validateLanguage($y_language) {
		//--
		if((string)trim((string)$y_language) == '') {
			return false;
		} //end if
		//--
		$all_languages = (array) self::getSafeLanguagesArr();
		//--
		$ok = false;
		//--
		if(strlen((string)$y_language) == 2) { // if language id have only 2 characters
			if(preg_match('/^[a-z]+$/', (string)$y_language)) { // language id must contain only a..z characters (iso-8859-1)
				if(is_array($all_languages)) {
					if($all_languages[(string)$y_language]) { // if that lang is set in languages array
						$ok = true;
					} //end if
				} //end if
			} //end if
		} //end if
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Get Translator
	 *
	 * @param 	STRING 	$y_area 				:: The Translation Area
	 * @param 	STRING	$y_subarea 				:: The Translation Sub-Area
	 *
	 * @return 	OBJECT							:: An Instance of SmartTextTranslator->
	 */
	public static function getTranslator($y_area, $y_subarea, $y_custom_language='') {
		//--
		$y_area 	= (string) self::validateArea($y_area);
		$y_subarea 	= (string) self::validateSubArea($y_subarea);
		//--
		if(((string)$y_custom_language != '') AND (self::validateLanguage($y_custom_language) === true)) { // get for a custom language
			$the_lang = (string) $y_custom_language;
		} else {
			$the_lang = (string) self::getLanguage(); // use default language
		} //end if else
		//--
		$translator_key = (string) $the_lang.'.'.$y_area.'.'.$y_subarea; // must use . as separator as it is the only character that is not allowed in lang / area / sub-area but is allowed in persistent cache
		//--
		if(!is_object(self::$translators[(string)$translator_key])) {
			self::$translators[(string)$translator_key] = new SmartTextTranslator((string)$the_lang, (string)$y_area, (string)$y_subarea);
			if(SmartFrameworkRuntime::ifInternalDebug()) {
				if(SmartFrameworkRuntime::ifDebug()) {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Creating a New Translator: '.$translator_key,
						'data' => 'Content:'."\n".print_r(self::$translators[(string)$translator_key],1)
					]);
				} //end if
			} //end if
		} else {
			if(SmartFrameworkRuntime::ifInternalDebug()) {
				if(SmartFrameworkRuntime::ifDebug()) {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Re-Using an Existing Translator: '.$translator_key,
						'data' => 'Content:'."\n".print_r(self::$translators[(string)$translator_key],1)
					]);
				} //end if
			} //end if
		} //end if
		//--
		return (object) self::$translators[(string)$translator_key];
		//--
	} //END FUNCTION
	//=====


	//##### INTERNAL USE PUBLICS


	//=====
	/**
	 * Regional Text :: Get Text Translation By Key for the current language / area / sub-area.
	 * This does not implement any control against the case if the key is missing.
	 * It is mainly implemented to be re-used just with programatic cases.
	 * Thus, it is recommended to use the getTranslator() function instead ...
	 *
	 * @param 	STRING 	$y_area 				:: The Translation Area
	 * @param 	STRING	$y_subarea 				:: The Translation Sub-Area
	 * @param 	STRING 	$y_textkey 				:: The Translation Key
	 *
	 * @return 	STRING							:: The Translation by Key for the specific language / area / sub-area
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getTranslationByKey($y_area, $y_subarea, $y_textkey, $y_custom_language='') {
		//--
		$y_area 	= (string) self::validateArea($y_area);
		$y_subarea 	= (string) self::validateSubArea($y_subarea);
		//--
		if(((string)$y_custom_language != '') AND (self::validateLanguage($y_custom_language) === true)) { // get for a custom language
			$the_lang = (string) $y_custom_language;
		} else {
			$the_lang = (string) self::getLanguage(); // use default language
		} //end if else
		//--
		$translations = (array) self::getFromOptimalPlace($the_lang, $y_area, $y_subarea);
		//--
		return (string) $translations[(string)$y_textkey];
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Get All Translations for the current language / area / sub-area.
	 * This does not implement any control against cases where some keys are missing.
	 * It is mainly implemented to be re-used just with programatic cases.
	 * Thus, it is recommended to use the getTranslator() function instead ...
	 *
	 * @param 	STRING 	$y_area 				:: The Translation Area
	 * @param 	STRING	$y_subarea 				:: The Translation Sub-Area
	 *
	 * @return 	ARRAY							:: An Array with the full set of Translations for the specific language / area / sub-area
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getAllTranslations($y_area, $y_subarea, $y_custom_language='') {
		//--
		$y_area 	= (string) self::validateArea($y_area);
		$y_subarea 	= (string) self::validateSubArea($y_subarea);
		//--
		if(((string)$y_custom_language != '') AND (self::validateLanguage($y_custom_language) === true)) { // get for a custom language
			$the_lang = (string) $y_custom_language;
		} else {
			$the_lang = (string) self::getLanguage(); // use default language
		} //end if else
		//--
		return (array) self::getFromOptimalPlace($the_lang, $y_area, $y_subarea);
		//--
	} //END FUNCTION
	//=====


	//#####


	//=====
	// get safe fixed languages arr
	private static function getSafeLanguagesArr() {
		//--
		global $languages;
		//--
		if(!is_array($languages)) {
			$languages = array('en' => '[EN]');
		} else {
			$languages = (array) array_change_key_case((array)$languages, CASE_LOWER); // make all keys lower
		} //end if
		//--
		return (array) $languages;
		//--
	} //END FUNCTION
	//=====


	//=====
	// validates the area name
	private static function validateArea($y_area) {
		//--
		if(((string)$y_area != '') AND (preg_match('/^[a-z0-9_\-@]+$/', (string)$y_area))) {
			return (string) $y_area;
		} else {
			return 'invalid__area';
		} //end if else
		//--
	} //END FUNCTION
	//=====


	//=====
	// validates the sub-area name
	private static function validateSubArea($y_subarea) {
		//--
		if((string)$y_subarea != '') {
			return (string) $y_subarea;
		} else {
			return 'invalid__subarea';
		} //end if else
		//--
	} //END FUNCTION
	//=====


	//=====
	private static function checkSourceParser() {
		//--
		if(class_exists('SmartAdapterTextTranslations')) {
			if(!is_subclass_of('SmartAdapterTextTranslations', 'SmartInterfaceAdapterTextTranslations', true)) {
				Smart::log_warning('Invalid instance of SmartAdapterTextTranslations ; Must implement the SmartInterfaceAdapterTextTranslations ...');
			} else {
				return true;
			} //end if
		} //end if
		return false;
		//--
	} //END FUNCTION
	//=====


	//=====
	// This will handle the Text Translations Source Parsing and will return the parsed Array
	private static function getFromSource($the_lang, $y_area, $y_subarea) {
		//--
		if(self::checkSourceParser() === true) {
			return (array) SmartAdapterTextTranslations::getTranslationsFromSource($the_lang, $y_area, $y_subarea);
		} else {
			Smart::log_warning('SmartAdapterTextTranslations::getTranslationsFromSource() must be defined ...');
			return array();
		} //end if
		//--
	} //END FUNCTION
	//=====


	//=====
	// It returns the latest Version signature of the Text Translations.
	// If the Version cannot be provided is OK just returning the current date/time as YYYY-MM-DD (in this case it will invalidate the Translations once per day).
	// It will be used to invalidate the Persistent Cache every time when the translations version is changed.
	private static function getLatestVersion() {
		//--
		if((string)self::$cache['translations:persistent-cache-version'] != '') {
			return (string) self::$cache['translations:persistent-cache-version'];
		} //end if
		//--
		if(self::checkSourceParser() === true) {
			$version = (string) SmartAdapterTextTranslations::getTranslationsVersion();
			if((string)$version == '') {
				$version = date('Y-m-d');
				Smart::log_warning('SmartAdapterTextTranslations::getTranslationsVersion() must return a non-empty string ...');
			} //end if
		} else {
			Smart::log_warning('SmartAdapterTextTranslations::getTranslationsVersion() must be defined ...');
			$version = date('Y-m-d');
		} //end if
		//--
		self::$cache['translations:persistent-cache-version'] = (string) $version;
		//--
		return (string) self::$cache['translations:persistent-cache-version'];
		//--
	} //END FUNCTION
	//=====


	//=====
	// try to get from (in this order): Internal (in-memory) cache ; Persistent Cache ; Source
	private static function getFromOptimalPlace($y_language, $y_area, $y_subarea) {
		//-- normalize params
		$y_language = (string) $y_language;
		$y_area = (string) $y_area;
		$y_subarea = (string) $y_subarea;
		//-- built the cache key
		$the_cache_key = (string) $y_language.'.'.$y_area.'.'.$y_subarea; // must use . as separator as it is the only character that is not allowed in lang / area / sub-area but is allowed in persistent cache
		//-- try to get from internal (in-memory) cache
		$translations = (array) self::$cache['translations@'.$the_cache_key];
		if(Smart::array_size($translations) > 0) {
			if(SmartFrameworkRuntime::ifInternalDebug()) {
				if(SmartFrameworkRuntime::ifDebug()) {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Get Text from Internal Cache for Key: '.$the_cache_key,
						'data' => 'Content:'."\n".print_r($translations,1)
					]);
				} //end if
			} //end if
			return (array) $translations;
		} //end if
		//-- try to get from persistent cache
		$version_translations = (string) self::getLatestVersion(); // get translations version
		$translations = (array) self::getFromPersistentCache((string)$the_cache_key, (string)$version_translations);
		if(Smart::array_size($translations) > 0) {
			if(SmartFrameworkRuntime::ifInternalDebug()) {
				if(SmartFrameworkRuntime::ifDebug()) {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Get Text from Persistent Cache for Key: '.$the_cache_key,
						'data' => 'Version:'."\n".$version_translations."\n".'Content:'."\n".print_r($translations,1)
					]);
				} //end if
			} //end if
			self::$cache['translations@'.$the_cache_key] = (array) $translations;
			return (array) $translations;
		} //end if
		//-- try to get from source
		$translations = (array) self::getFromSource($y_language, $y_area, $y_subarea);
		if(Smart::array_size($translations) > 0) {
			if(SmartFrameworkRuntime::ifInternalDebug()) {
				if(SmartFrameworkRuntime::ifDebug()) {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Get Text from Sources for Key: '.$the_cache_key,
						'data' => 'Content:'."\n".print_r($translations,1)
					]);
				} //end if
			} //end if
			self::$cache['translations@'.$the_cache_key] = (array) $translations;
			self::setInPersistentCache((string)$the_cache_key, (array)$translations);
			return (array) $translations;
		} //end if
		//--
		if(SmartFrameworkRuntime::ifInternalDebug()) {
			if(SmartFrameworkRuntime::ifDebug()) {
				SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
					'title' => '*** NOT FOUND: the Text from Sources for Key: '.$the_cache_key,
					'data' => 'Content:'."\n".print_r($translations,1)
				]);
			} //end if
		} //end if
		if((string)self::getDefaultLanguage() == (string)$y_language) {
			Smart::log_warning('Cannot get from source Text Translations for Key: '.$the_cache_key); // show this if default language
		} elseif(SmartFrameworkRuntime::ifDebug()) {
			Smart::log_notice('The Text Translations Key is not available ; will fallback to default language ['.self::getDefaultLanguage().'] for: '.$the_cache_key); // show this if debug
		} //end if
		return array(); // this is invalid, means not found in any places
		//--
	} //END FUNCTION
	//=====


	//=====
	// try to get from persistent cache if active and cached
	private static function getFromPersistentCache($the_cache_key, $version_translations) {
		//--
		$arr = array();
		//--
		if(SmartPersistentCache::isActive() AND SmartPersistentCache::isMemoryBased()) {
			//-- if not set translations versions, set them to internal cache :: this will be executed just once per session and is necessary to keep sync between Persistent Cache Translations and Real Translation Sources
			if((string)$version_translations == '') {
				Smart::log_warning('Empty Version for Text Translations ... It is needed for store them in the Persistent Cache !');
			} //end if
			//-- check if persistent cache texts are outdated
			$check_version = true;
			if((string)self::$cache['#VERSION#'] != '') {
				$check_version = false;
			} elseif((string)$version_translations === (string)SmartPersistentCache::getKey('smart-regional-texts', 'version')) {
				$check_version = false;
			} //end if else
			if($check_version !== false) {
				//-- cleanup the outdated text keys from persistent cache
				SmartPersistentCache::unsetKey('smart-regional-texts', '*');
				//-- re-register in persistent cache the Date and Version (after cleanup)
				if(!SmartPersistentCache::keyExists('smart-regional-texts', 'version')) {
					if(!SmartPersistentCache::keyExists('smart-regional-texts', 'date')) {
						if(SmartPersistentCache::setKey('smart-regional-texts', 'date', 'Cached on: '.date('Y-m-d H:i:s O'))) {
							SmartPersistentCache::setKey('smart-regional-texts', 'version', (string)$version_translations);
						} //end if
					} //end if
				} //end if
				//--
			} else { // text keys in persistent cache appear to be latest version, try to get it
				//--
				self::$cache['#VERSION#'] = (string) $version_translations;
				//--
				$rdata = SmartPersistentCache::getKey('smart-regional-texts', (string)$the_cache_key);
				if($rdata) { // here evaluates if non-false
					$rdata = SmartPersistentCache::varDecode((string)$rdata);
				} //end if
				if(Smart::array_size($rdata) > 0) {
					$arr = (array) $rdata;
				} //end if
				$rdata = ''; // clear
				//--
			} //end if
			//--
		} //end if
		//--
		return (array) $arr;
		//--
	} //END FUNCTION
	//=====


	//=====
	// try to set to persistent cache if active and non-empty array
	private static function setInPersistentCache($the_cache_key, $y_data_arr) {
		//--
		if(SmartPersistentCache::isActive() AND SmartPersistentCache::isMemoryBased()) {
			if(Smart::array_size($y_data_arr) > 0) {
				SmartPersistentCache::setKey('smart-regional-texts', (string)$the_cache_key, (string)SmartPersistentCache::varEncode((array)$y_data_arr));
			} //end if
		} //end if
		//--
	} //END FUNCTION
	//=====


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class Smart Text Translator.
 * This is intended just for internal use.
 * This class may be changed or removed unattended, you should never rely on this class when coding !
 *
 * @version 	v.181218
 *
 * @access 		private
 * @internal
 *
 */
final class SmartTextTranslator {

	// ->

	private $language = '';
	private $area = '';
	private $subarea = '';


	public function __construct($y_language, $y_area, $y_subarea) {
		//--
		if((string)$y_language != '') {
			$this->language = (string) $y_language;
		} else {
			$this->language = '??';
			Smart::log_warning('Invalid Language for Text Context Translator Area ['.$y_language.']: '.$y_area.' ; SubArea: '.$y_subarea);
		} //end if else
		//--
		if((string)$y_area != '') {
			$this->area = (string) $y_area;
		} else {
			$this->area = 'undefined__area';
			Smart::log_warning('Invalid Area for Text Context Translator Area ['.$y_language.']: '.$y_area.' ; SubArea: '.$y_subarea);
		} //end if else
		//--
		if((string)$y_subarea != '') {
			$this->subarea = (string) $y_subarea;
		} else {
			Smart::log_warning('Invalid Sub-Area for Text Context Translator Area['.$y_language.']: '.$y_area.' ; SubArea: '.$y_subarea);
			$this->subarea = 'undefined__subarea';
		} //end if
		//--
	} //END FUNCTION


	public function getinfo() {
		//--
		return [
			'language' 	=> (string) $this->language,
			'area' 		=> (string) $this->area,
			'sub-area' 	=> (string) $this->subarea
		];
		//--
	} //END FUNCTION


	// texts are returned as raw, they must be escaped when used with HTML or JS
	public function text($y_textkey, $y_fallback_language='@default', $y_ignore_empty=false) {
		//--
		if((string)$y_textkey == '') {
			Smart::log_warning('Empty Key for Text Context Translator - Area: '.$this->area.' ; SubArea: '.$this->subarea);
			return '{Empty Translation Key}';
		} //end if
		//--
		if((string)$y_fallback_language == '@default') {
			$y_fallback_language = (string) SmartTextTranslations::getDefaultLanguage();
		} //end if else
		//--
		$text = (string) SmartTextTranslations::getTranslationByKey($this->area, $this->subarea, (string)$y_textkey, $this->language);
		if(((string)trim((string)$text) == '') AND ((string)$y_fallback_language != '') AND ((string)$y_fallback_language != (string)$this->language)) {
			$text = (string) SmartTextTranslations::getTranslationByKey($this->area, $this->subarea, (string)$y_textkey, (string)$y_fallback_language);
		} //end if
		if((string)trim((string)$text) == '') {
			if($y_ignore_empty !== true) {
				Smart::log_warning('Undefined Key: ['.$y_textkey.'] for Text Context Translator ['.$this->language.'] - Area: '.$this->area.' ; SubArea: '.$this->subarea);
				$text = '{Undefined Translation Key ['.$this->language.']: '.$y_textkey.'}';
			} else {
				$text = '';
			} //end if else
		} //end if
		//--
		return (string) $text;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== INTERFACE START
//=====================================================================================


/**
 * Abstract Inteface Smart Parse Regional Text
 * The extended object MUST NOT CONTAIN OTHER FUNCTIONS BECAUSE MAY NOT WORK as Expected !!!
 *
 * @access 		private
 * @internal
 *
 * @version 	v.181218
 *
 */
interface SmartInterfaceAdapterTextTranslations {

	// :: INTERFACE


	//=====
	/**
	 * Smart Parse Regional Text Parse Translation Source
	 * This function must implement a Text Translations parser.
	 * It can be implemented to read from one of the variety of sources: Arrays, INI, YAML, XML, JSON, SQLite, PostgreSQL, GetText, ...
	 * RETURN: an associative array as [key => value] for the specific translation
	 */
	public static function getTranslationsFromSource($the_lang, $y_area, $y_subarea);
	//=====


	//=====
	/**
	 * Smart Parse Regional Text Get Latest Version
	 * This function must implement a way to get last version string to validate Text Translations.
	 * If a version cannot be provided, must return (string) date('Y-m-d') and this way the texts persistent cache will be re-validated daily.
	 * If a real version can be provided it is the best, so persistent cache would be re-validated just upon changes !
	 * RETURN: a non-empty string the provides the latest version string of the current texts translations
	 */
	public static function getTranslationsVersion();
	//=====


} //END INTERFACE


//=====================================================================================
//===================================================================================== INTERFACE END
//=====================================================================================


//end of php code
?>