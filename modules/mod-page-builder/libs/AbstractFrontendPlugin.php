<?php
// Class: \SmartModExtLib\PageBuilder\AbstractFrontendPlugin
// (c) 2006-2020 unix-world.org - all rights reserved
// r.7.2.1 / smart.framework.v.7.2

namespace SmartModExtLib\PageBuilder;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================

/**
 * Class: AbstractFrontendPlugin - Abstract Frontend Plugin, provides the Abstract Definitions to create PageBuilder (Frontend) Plugins.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		needs to be extended as: UniqueClassPluginName
 *
 * @access 		PUBLIC
 *
 * @version 	v.20200806
 * @package 	development:modules:PageBuilder
 *
 */
abstract class AbstractFrontendPlugin extends \SmartModExtLib\PageBuilder\AbstractFrontendPageBuilder {


	private $plugin_initialized 		= false;
	private $plugin_name 				= 'ERROR-NO-PLUGIN-NAME';
	private $plugin_config 				= array();
	private $plugin_caller_module_path 	= 'modules/app/';
	private $plugin_caller_data 		= [];
	private $plugin_data 				= [];


	//=====
	/**
	 * Initialize Plugin (internal use only)
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	final public function initPlugin(string $plugin_name, array $plugin_config, string $plugin_caller_module_path, array $plugin_caller_data, array $plugin_data) {
		//--
		if($this->plugin_initialized === true) {
			return true;
		} //end if
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return false;
		} //end if
		//--
		if(\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$plugin_name)) {
			$this->plugin_name = (string) $plugin_name;
		} //end if
		//--
		if(\is_array($plugin_config)) {
			$this->plugin_config = (array) \array_change_key_case((array)$plugin_config, \CASE_LOWER); // plugin config ; force all keys lower case
		} //end if
		//--
		if(\SmartFileSysUtils::check_if_safe_path((string)$plugin_caller_module_path)) {
			$this->plugin_caller_module_path = (string) $plugin_caller_module_path;
		} //end if
		//--
		$this->plugin_caller_data = [ // {{{SYNC-PAGEBUILDER-OBJ-EXPORT-LEVEL0-FIELDS}}} ; these are the fields from level zero object
			'ID' 			=> (string) $plugin_caller_data['id'],
			'NAME' 			=> (string) $plugin_caller_data['name'],
			'AUTH' 			=> (int)    $plugin_caller_data['auth'],
			'TYPE' 			=> (string) $plugin_caller_data['type'],
			'MODE' 			=> (string) $plugin_caller_data['mode'],
			'CTRL-AREA' 	=> (string) $plugin_caller_data['ctrl-area'],
			'LAYOUT' 		=> (string) $plugin_caller_data['layout'],
			'DATE-CREATED' 	=> (string) $plugin_caller_data['publisher-date-created'],
			'DATE-MODIFIED' => (string) $plugin_caller_data['publisher-date-modified'],
			'AUTHOR-ID' 	=> (string) $plugin_caller_data['publisher-id'],
		];
		//--
		$this->plugin_data = [ // {{{SYNC-PAGEBUILDER-OBJ-EXPORT-LEVEL0-FIELDS}}} ; these are the fields from level zero object
			'ID' 			=> (string) $plugin_data['id'],
			'NAME' 			=> (string) $plugin_data['name'],
			'AUTH' 			=> (int)    $plugin_data['auth'],
			'TYPE' 			=> (string) $plugin_data['type'],
			'MODE' 			=> (string) $plugin_data['mode'],
			'CTRL-AREA' 	=> (string) $plugin_data['ctrl-area'],
			'LAYOUT' 		=> (string) $plugin_data['layout'],
			'DATE-CREATED' 	=> (string) $plugin_data['publisher-date-created'],
			'DATE-MODIFIED' => (string) $plugin_data['publisher-date-modified'],
			'AUTHOR-ID' 	=> (string) $plugin_data['publisher-id'],
		];
		//--
		$this->plugin_initialized = true;
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Name
	 */
	final public function getPluginName() {
		//--
		return (string) $this->plugin_name;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Config as Array
	 */
	final public function getPluginConfig() {
		//--
		return (array) $this->plugin_config;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Caller Module Path, for Level Zero Object
	 */
	final public function getPluginCallerModulePath() {
		//--
		return (string) $this->plugin_caller_module_path;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Caller Data as Array, for Level Zero Object
	 */
	final public function getPluginCallerData() {
		//--
		return (array) $this->plugin_caller_data;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Data as Array, for the current Object Level
	 */
	final public function getPluginData() {
		//--
		return (array) $this->plugin_data;
		//--
	} //END FUNCTION
	//=====


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
