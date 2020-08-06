<?php
// Class: \SmartModExtLib\PageBuilder\AbstractFrontendPageBuilder
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
 * Class: AbstractFrontendPageBuilder - Abstract Frontend PageBuilder, provides the Abstract Definitions to create PageBuilder (Frontend) Controllers and Plugins.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		needs to be extended as: UniqueClassPluginName
 *
 * @access 		PUBLIC
 *
 * @version 	v.20200805
 * @package 	development:modules:PageBuilder
 *
 */
abstract class AbstractFrontendPageBuilder extends \SmartAbstractAppController {


	//===== $y_ctrl can be: NULL / STRING / ARRAY
	final public function checkIfPageOrSegmentExist(string $y_id, $y_ctrl=null) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return false;
		} //end if
		//--
		return (bool) \SmartModDataModel\PageBuilder\PageBuilderFrontend::checkIfPageOrSegmentExist((string)$y_id, true, true, $y_ctrl);
		//--
	} //END FUNCTION
	//=====


	//===== $y_ctrl can be: NULL / STRING / ARRAY
	final public function checkIfPageExist(string $y_id, $y_ctrl=null) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return false;
		} //end if
		//--
		return (bool) \SmartModDataModel\PageBuilder\PageBuilderFrontend::checkIfPageOrSegmentExist((string)$y_id, true, false, $y_ctrl);
		//--
	} //END FUNCTION
	//=====


	//===== $y_ctrl can be: NULL / STRING / ARRAY
	final public function checkIfSegmentExist(string $y_id, $y_ctrl=null) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return false;
		} //end if
		//--
		return (bool) \SmartModDataModel\PageBuilder\PageBuilderFrontend::checkIfPageOrSegmentExist((string)$y_id, false, true, $y_ctrl);
		//--
	} //END FUNCTION
	//=====


	//=====
	final public function getPageById(string $y_id) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return array();
		} //end if
		//--
		return (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getListOfObjectsBy('pages', 'id', (string)$y_id);
		//--
	} //END FUNCTION
	//=====



	//=====
	final public function getSegmentById(string $y_id) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return array();
		} //end if
		//--
		return (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getListOfObjectsBy('segments', 'id', (string)$y_id);
		//--
	} //END FUNCTION
	//=====


	//=====
	final public function getListOfPagesByTag(string $y_tag, string $y_orderby='id', string $y_orderdir='ASC', int $y_limit=0, int $y_ofs=0) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return array();
		} //end if
		//--
		return (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getListOfObjectsBy('pages', 'tags', (string)$y_tag, (string)$y_orderby, (string)$y_orderdir, (int)$y_limit, (int)$y_ofs);
		//--
	} //END FUNCTION
	//=====


	//=====
	final public function getListOfSegmentsByTag(string $y_tag, string $y_orderby='id', string $y_orderdir='ASC', int $y_limit=0, int $y_ofs=0) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return array();
		} //end if
		//--
		return (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getListOfObjectsBy('segments', 'tags', (string)$y_tag, (string)$y_orderby, (string)$y_orderdir, (int)$y_limit, (int)$y_ofs);
		//--
	} //END FUNCTION
	//=====


	//=====
	final public function getListOfSegmentsByArea(string $y_area, string $y_orderby='id', string $y_orderdir='ASC', int $y_limit=0, int $y_ofs=0) {
		//--
		if((string)$this->ControllerGetParam('module-area') != 'index') {
			return array();
		} //end if
		//--
		return (array) \SmartModDataModel\PageBuilder\PageBuilderFrontend::getListOfObjectsBy('segments', 'area', (string)$y_area, (string)$y_orderby, (string)$y_orderdir, (int)$y_limit, (int)$y_ofs);
		//--
	} //END FUNCTION
	//=====


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
