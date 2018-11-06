<?php
// [LIB - SmartFramework / YAML Text Translations Parser]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// REQUIRED
//======================================================
// Smart-Framework - Parse Regional Text
// DEPENDS:
//	* Smart::
//	* SmartFileSystem::
//	* SmartFileSysUtils::
//	* SmartYamlConverter->
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

define('SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER', 'YAML: File based');

/**
 * Class App.Custom.TextTranslationsAdapter.Yaml - YAML files based text translations adapter (default).
 *
 * To use your own custom adapter for the text translations in Smart.Framework you have to build it by implementing the SmartInterfaceAdapterTextTranslations interface and define it in etc/init.php at the begining such as: define('SMART_FRAMEWORK_TRANSLATIONS_ADAPTER_CUSTOM', 'modules/app/translations-custom-adapter.php');
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.181105
 * @package 	Application
 *
 */
final class SmartAdapterTextTranslations implements SmartInterfaceAdapterTextTranslations {

	// ::


	//==================================================================
	// This reads and parse the YAML translation files
	public static function getTranslationsFromSource($the_lang, $y_area, $y_subarea) {
		//--
		$the_lang = (string) Smart::safe_varname((string)$the_lang);
		if(((string)$the_lang == '') OR (!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$the_lang))) {
			Smart::log_warning(__METHOD__.'() :: Invalid/Empty parameter for Translation Language: '.$the_lang);
			return array();
		} //end if
		//--
		$y_area = (string) Smart::safe_filename((string)$y_area);
		if(((string)$y_area == '') OR (!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$y_area))) {
			Smart::log_warning(__METHOD__.'() :: Invalid/Empty parameter for Translation Area: '.$y_area);
			return array();
		} //end if
		//--
		$y_subarea = (string) Smart::safe_filename((string)$y_subarea);
		if(((string)$y_subarea == '') OR (!SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$y_subarea))) {
			Smart::log_warning(__METHOD__.'() :: Invalid/Empty parameter for Translation SubArea: '.$y_subarea);
			return array();
		} //end if
		//--
		if(substr((string)$y_area, 0, 1) == '@') {
			if((string)$the_lang == 'en') {
				$fdb_dir = 'lib/app/translations/';
			} else { // default is: modules/app/translations/
				$fdb_dir = (string) SMART_FRAMEWORK_LANGUAGES_CACHE_DIR;
			} //end if else
			$fdb_template = (string) strtolower($y_area.'/'.$y_subarea.'-'.$the_lang);
		} else { // $y_area can be: apps, mod-something, ...
			$fdb_dir = (string) Smart::safe_pathname('modules/'.$y_area.'/translations/');
			$fdb_template = (string) strtolower($y_subarea.'-'.$the_lang);
		} //end if else
		//--
		$fdb_file = (string) $fdb_dir.$fdb_template.'.yaml';
		SmartFileSysUtils::raise_error_if_unsafe_path($fdb_file);
		//--
		if(!SmartFileSystem::is_type_dir($fdb_dir)) {
			//--
			// INFO: To be able to fallback to the default language, don't make this error FATAL ERROR except if this is the default language selected
			//--
			if((string)SmartTextTranslations::getDefaultLanguage() == (string)$the_lang) {
				Smart::raise_error(
					'Invalid Language Dir: '.$fdb_dir.' :: for: '.$y_area.'@'.$y_subarea,
					'Invalid Language Dir for: '.$y_area.'@'.$y_subarea // msg to display
				);
			} //end if
			return array();
		} //end if
		//--
		if(!SmartFileSystem::is_type_file($fdb_file)) {
			//--
			// INFO: To be able to fallback to the default language, don't make this error FATAL ERROR except if this is the default language selected
			//--
			if((string)SmartTextTranslations::getDefaultLanguage() == (string)$the_lang) {
				Smart::raise_error(
					'Invalid Language File: '.$fdb_file,
					'Invalid Language File: '.$fdb_template // msg to display
				);
			} //end if
			return array();
			//--
		} //end if
		//--
		$fcontent = (string) SmartFileSystem::read($fdb_file);
		$arr = (new SmartYamlConverter())->parse((string)$fcontent);
		$fcontent = '';
		//--
		if(!is_array($arr)) {
			Smart::raise_error(
				'Parse Error / TRANSLATIONS :: Language File: '.$fdb_file,
				'Parse Error / TRANSLATIONS :: Language File: '.$fdb_template // msg to display
			);
			return array();
		} //end if
		//--
		if(!is_array($arr['TRANSLATIONS'])) {
			Smart::raise_error(
				'Parse Error / TRANSLATIONS :: Language File: '.$fdb_file,
				'Parse Error / TRANSLATIONS :: Language File: '.$fdb_template // msg to display
			);
			return array();
		} //end if
		if(Smart::array_size($arr['TRANSLATIONS'][(string)$y_subarea]) <= 0) {
			Smart::log_warning('Parse Error / TRANSLATIONS.'.$y_subarea.' :: Language File: '.$fdb_template);
			return array();
		} //end if
		//--
		return (array) $arr['TRANSLATIONS'][(string)$y_subarea];
		//--
	} //END FUNCTION
	//==================================================================


	//==================================================================
	// This validates the translations last update version
	public static function getTranslationsVersion() {
		//--
		$version = 'Smart.Framework :: '.SMART_FRAMEWORK_RELEASE_VERSION.' '.SMART_FRAMEWORK_RELEASE_TAGVERSION;
		//--
		if(defined('SMART_APP_MODULES_RELEASE')) {
			$version .= "\n".'App.Modules :: '.SMART_APP_MODULES_RELEASE;
		} //end if
		//--
		return (string) trim('#TextTranslations::Version#'."\n".$version."\n".'#.#');
		//--
	} //END FUNCTION
	//==================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>