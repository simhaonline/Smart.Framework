<?php
// [LIB - SmartFramework / YAML Text Translations Parser]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.7.1 r.2016.09.21 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
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

if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
	define('SMART_FRAMEWORK__INFO__TEXT_TRANSLATIONS_ADAPTER', 'YAML: File based');
} //end if

/**
 * Class SmartAdapterTextTranslations - YAML files based text translations adapter
 *
 * @access 		private
 * @internal
 *
 * @version		160215
 *
 */
final class SmartAdapterTextTranslations implements SmartInterfaceAdapterTextTranslations {

	// ::


	//==================================================================
	// This reads and parse from YAML sources
	public static function getTranslationsFromSource($the_lang, $y_area, $y_subarea) {
		//--
		if(substr((string)$y_area, 0, 1) == '@') {
			if((string)$the_lang == 'en') {
				$fdb_dir = 'lib/app/translations/';
			} else { // default is: modules/app/translations/
				$fdb_dir = (string) SMART_FRAMEWORK_LANGUAGES_CACHE_DIR;
			} //end if else
			$fdb_template = strtolower($y_area.'/'.$y_subarea.'-'.$the_lang);
		} else { // $y_area can be: apps, mod-something, ...
			$fdb_dir = (string) Smart::safe_pathname('modules/'.$y_area.'/translations/');
			$fdb_template = strtolower($y_subarea.'-'.$the_lang);
		} //end if else
		//--
		$fdb_file = (string) $fdb_dir.$fdb_template.'.yaml';
		SmartFileSysUtils::raise_error_if_unsafe_path($fdb_file);
		//--
		if(!is_dir($fdb_dir)) {
			Smart::raise_error(
				'Invalid Language Dir: '.$fdb_dir.' :: for: '.$y_area.'@'.$y_subarea,
				'Invalid Language Dir for: '.$y_area.'@'.$y_subarea // msg to display
			);
			return array();
		} //end if
		//--
		if(!is_file($fdb_file)) {
			//--
			Smart::raise_error(
				'Invalid Language File: '.$fdb_file,
				'Invalid Language File: '.$fdb_template // msg to display
			);
			return array();
			//--
		} //end if
		//--
		$fcontent = SmartFileSystem::staticread($fdb_file);
		$arr = (new SmartYamlConverter())->parse((string)$fcontent);
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
	// This reads and parse from YAML sources
	public static function getTranslationsVersion() {
		//--
		$version = 'Smart.Framework :: '.SMART_FRAMEWORK_RELEASE_VERSION.' '.SMART_FRAMEWORK_RELEASE_TAGVERSION;
		//--
		//if(defined('SMART_FRAMEWORK_MODULES_VERSION')) {
		//	if(defined('SMART_FRAMEWORK_MODULES_HEAD_VERSION')) {
		//		$version .= "\n".'Smart.Framework.Modules :: '.SMART_FRAMEWORK_MODULES_VERSION.' '.SMART_FRAMEWORK_MODULES_HEAD_VERSION;
		//	} //end if
		//} //end if
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