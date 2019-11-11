<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: PageBuilder/TestFrontendSegmentWithMarkers
// Route: ?page=page-builder.test-frontend-segment-with-markers
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT S EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'INDEX');

/**
 * Index Sample Controller
 *
 * @ignore
 *
 */
final class SmartAppIndexController extends \SmartModExtLib\PageBuilder\AbstractFrontendController {

	// r.20191002

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--
		if((!$this->checkIfPageOrSegmentExist('#website-menu')) OR (!$this->checkIfPageOrSegmentExist('#segment-with-markers')) OR (!$this->checkIfPageOrSegmentExist('#website-footer'))) {
			$this->PageViewSetErrorStatus(404, 'PageBuilder SampleData Not Found ...');
			return;
		} //end if
		//--

		$this->PageViewSetCfg('template-path', '@');
		$this->PageViewSetCfg('template-file', 'template-test-frontend.htm');

		$top = $this->getRenderedBuilderSegmentCode('#website-menu');
		$main = $this->renderSegmentMarkers(
			$this->getRenderedBuilderSegmentCode('#segment-with-markers'),
			[
				'THE-MARKER' => '<b>This is a marker that should be HTML escaped</b>'
			]
		);
		$foot = $this->getRenderedBuilderSegmentCode('#website-footer');

		$this->PageViewSetVars([
			'AREA.TOP' => (string) $top,
			'MAIN' => (string) $main,
			'AREA.FOOTER' => (string) $foot,
			'META-DESCRIPTION' => '',
			'META-KEYWORDS' => ''
		]);

	} //END FUNCTION

} //END CLASS

//end of php code
?>