<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// (c) 2006-2020 unix-world.org - all rights reserved
// r.5.7.2 / smart.framework.v.5.7

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * PageBuilder Plugin
 *
 * @ignore
 *
 */
final class PageBuilderFrontendPluginPageBuilderTest4 extends \SmartModExtLib\PageBuilder\AbstractFrontendPlugin {

	// r.20200512

	public function Run() {
		//--
		//$this->PageViewSetErrorStatus(500, 'WARNING: Test from Plugin 4');
		//--
		$this->PageViewSetVars([
			'content' 	=> '<div>this is Plugin4 Test</div>'
		]);
		//--
	}//END FUNCTION


	public function ShutDown() {
		// *** optional*** can be redefined in a plugin
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
