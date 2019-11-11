<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

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
final class PageBuilderFrontendPluginPageBuilderTest1 extends \SmartModExtLib\PageBuilder\AbstractFrontendPlugin {

	// r.20191002

	public function Run() {

		//--
		//$this->PageViewSetCfg('rawpage', true);
		//--
		$this->PageViewSetVars([
			'meta-description' 	=> 'Meta desc. comes from Plugin1',
			'meta-keywords' 	=> 'meta, keywords, come, from, plugin1',
			'content' 			=> '<div>this is Plugin1 Test</div>',
		]);
		//--
		if(rand(0,10) >= 5) {
			$this->PageViewSetVar('meta-title', 'Title (override) comes from Plugin1');
		} //end if
		//--

		//$this->PageViewSetErrorStatus(503, 'Test Err');
		//return 503;

	}//END FUNCTION


	public function ShutDown() {
		// *** optional*** can be redefined in a plugin
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>