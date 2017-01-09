<?php
// [LIB - SmartFramework / Text Translations]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.5 r.2017.01.09 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
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
 * @version 	v.160224
 * @package 	Application
 *
 */
final class SmartTextTranslations {

	// ::

	private static $cache = array();
	private static $translators = array();


	//=====
	/**
	 * Regional Text :: Get Language
	 *
	 * @return 	STRING						:: The language ID ; sample (for English) will return: 'en'
	 */
	public static function getLanguage() {
		//--
		global $configs;
		global $languages;
		//--
		if(strlen((string)self::$cache['#LANGUAGE#']) == 2) {
			if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Get Language from Internal Cache',
						'data' => 'Content: '.self::$cache['#LANGUAGE#']
					]);
				} //end if
			} //end if
			return (string) self::$cache['#LANGUAGE#'];
		} //end if
		//--
		if(!is_array($languages)) {
			$languages = array('en' => '[EN]');
		} //end if
		//--
		$the_lang = 'en'; // default
		//--
		if(is_array($configs)) {
			if(is_array($configs['regional'])) {
				$tmp_lang = (string) $configs['regional']['language-id'];
				if(strlen((string)$tmp_lang) == 2) { // if language id have only 2 characters
					if(preg_match('/^[a-z]+$/', (string)$tmp_lang)) { // language id must contain only a..z characters (iso-8859-1)
						if(is_array($languages)) {
							if((string)$languages[(string)$tmp_lang] != '') { // if that lang is set in languages array
								$the_lang = (string) $tmp_lang;
								if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
									if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
										SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
											'title' => 'Get Language from Configs',
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
		$the_lang = (string) strtolower((string)$the_lang);
		self::$cache['#LANGUAGE#'] = (string) $the_lang;
		//--
		return (string) $the_lang;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Regional Text :: Set Language
	 *
	 * @param 	STRING 	$y_language 		:: The language ID ; sample (for English) will be: 'en'
	 *
	 * @return 	BOOLEAN						:: TRUE if successful, FALSE if not
	 */
	public static function setLanguage($y_language) {
		//--
		global $configs;
		global $languages;
		//--
		$result = false;
		//--
		$tmp_lang = (string) strtolower((string)SmartUnicode::utf8_to_iso((string)$y_language));
		//--
		if(is_array($configs)) {
			if(strlen((string)$tmp_lang) == 2) { // if language id have only 2 characters
				if(preg_match('/^[a-z]+$/', (string)$tmp_lang)) { // language id must contain only a..z characters (iso-8859-1)
					if(is_array($languages)) {
						if((string)$languages[(string)$tmp_lang] != '') { // if that lang is set in languages array
							if((string)$tmp_lang != (string)$configs['regional']['language-id']) { // if it is the same, don't make sense to set it again !
								$configs['regional']['language-id'] = (string) $tmp_lang;
								self::$cache['#LANGUAGE#'] = (string) $tmp_lang;
								$result = true;
								if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
									if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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
	 * Regional Text :: Get Translator
	 *
	 * @param 	STRING 	$y_area 				:: The Translation Area
	 * @param 	STRING	$y_subarea 				:: The Translation Sub-Area
	 *
	 * @return 	OBJECT							:: An Instance of SmartTextTranslator->
	 */
	public static function getTranslator($y_area, $y_subarea) {
		//--
		$y_area 	= (string) self::validateArea($y_area);
		$y_subarea 	= (string) self::validateSubArea($y_subarea);
		//--
		$the_lang = self::getLanguage();
		$translator_key = (string) $the_lang.'.'.$y_area.'.'.$y_subarea; // must use . as separator as it is the only character that is not allowed in lang / area / sub-area but is allowed in persistent cache
		//--
		if(!is_object(self::$translators[(string)$translator_key])) {
			self::$translators[(string)$translator_key] = new SmartTextTranslator((string)$y_area, (string)$y_subarea);
			if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
					SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
						'title' => 'Creating a New Translator: '.$translator_key,
						'data' => 'Content:'."\n".print_r(self::$translators[(string)$translator_key],1)
					]);
				} //end if
			} //end if
		} else {
			if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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


	//#####


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
	public static function getTranslationByKey($y_area, $y_subarea, $y_textkey) {
		//--
		$y_area 	= (string) self::validateArea($y_area);
		$y_subarea 	= (string) self::validateSubArea($y_subarea);
		//--
		$translations = (array) self::getFromOptimalPlace($y_area, $y_subarea);
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
	public static function getAllTranslations($y_area, $y_subarea) {
		//--
		$y_area 	= (string) self::validateArea($y_area);
		$y_subarea 	= (string) self::validateSubArea($y_subarea);
		//--
		return (array) self::getFromOptimalPlace($y_area, $y_subarea);
		//--
	} //END FUNCTION
	//=====


	//#####


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
	private static function getFromOptimalPlace($y_area, $y_subarea) {
		//-- normalize params
		$y_area = (string) $y_area;
		$y_subarea = (string) $y_subarea;
		//-- get the current language
		$the_lang = self::getLanguage();
		//-- built the cache key
		$the_cache_key = (string) $the_lang.'.'.$y_area.'.'.$y_subarea; // must use . as separator as it is the only character that is not allowed in lang / area / sub-area but is allowed in persistent cache
		//-- try to get from internal (in-memory) cache
		$translations = (array) self::$cache['translations@'.$the_cache_key];
		if(Smart::array_size($translations) > 0) {
			if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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
			if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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
		$translations = (array) self::getFromSource($the_lang, $y_area, $y_subarea);
		if(Smart::array_size($translations) > 0) {
			if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
				if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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
		if(defined('SMART_FRAMEWORK_INTERNAL_DEBUG')) {
			if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
				SmartFrameworkRegistry::setDebugMsg('extra', '***REGIONAL-TEXTS***', [
					'title' => '!!! FAILED !!! to Get Text from Sources for Key: '.$the_cache_key,
					'data' => 'Content:'."\n".print_r($translations,1)
				]);
			} //end if
		} //end if
		Smart::log_warning('Cannot get from source Text Translations for Key: '.$the_cache_key);
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
					$rdata = Smart::unseryalize((string)$rdata);
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
				SmartPersistentCache::setKey('smart-regional-texts', (string)$the_cache_key, (string)Smart::seryalize((array)$y_data_arr));
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
 * @version 	v.160215
 *
 * @access 		private
 * @internal
 *
 */
final class SmartTextTranslator {

	// ->

	private $area = '';
	private $subarea = '';


	public function __construct($y_area, $y_subarea) {
		//--
		if((string)$y_area != '') {
			$this->area = (string) $y_area;
		} else {
			$this->area = 'undefined__area';
			Smart::log_warning('Invalid Area for Text Context Translator Area: '.$y_area.' ; SubArea: '.$y_subarea);
		} //end if else
		//--
		if((string)$y_subarea != '') {
			$this->subarea = (string) $y_subarea;
		} else {
			Smart::log_warning('Invalid Sub-Area for Text Context Translator Area: '.$y_area.' ; SubArea: '.$y_subarea);
			$this->subarea = 'undefined__subarea';
		} //end if
		//--
	} //END FUNCTION


	// Texts are presumed to be HTML Safe, Already HTML Escaped ...
	// This way texts will can contain also portions of HTML code !
	// NOTICE: Never escape the text that come fro translations when using it
	// NOTICE: When creating a new translation take care to save text as HTML escaped
	public function text($y_textkey) {
		//--
		if((string)$y_textkey == '') {
			Smart::log_warning('Empty Key for Text Context Translator - Area: '.$this->area.' ; SubArea: '.$this->subarea);
			return '{Empty Translation Key}';
		} //end if
		//--
		$text = (string) SmartTextTranslations::getTranslationByKey($this->area, $this->subarea, $y_textkey);
		//--
		if((string)$text == '') {
			Smart::log_warning('Undefined Key: ['.$y_textkey.'] for Text Context Translator - Area: '.$this->area.' ; SubArea: '.$this->subarea);
			return '{Undefined Translation Key: '.Smart::escape_html($y_textkey).'}';
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
 * @version 	v.160215
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