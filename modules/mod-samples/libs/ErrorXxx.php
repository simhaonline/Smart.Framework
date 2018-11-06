<?php
// [LIB - SmartFramework / Samples / ErrorXxx - a sample helper for custom 4xx and 5xx status pages]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

// Class: \SmartModExtLib\Samples\ErrorXxx
// Type: Module Library
// Info: this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Samples;

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

/**
 * Sample Helper to implement custom Error Handlers for HTTP Status Errors (4xx, 5xx)
 *
 * @access 		private
 * @internal
 *
 * @version 	v.181105
 *
 */
abstract class ErrorXxx extends \SmartAbstractAppController {

	protected $errcode = 000;
	protected $errtext = '???';


	final public function Run() {

		//--
		$uri = (string) \SmartUtils::get_server_current_request_uri();
		$uri = (string) ltrim($uri, '/');
		$uri = (array)  explode('?', (string)$uri);
		$uri = (string) $uri[0];
		$ext = (string) \SmartFileSysUtils::get_file_extension_from_path($uri);
		$lext = (string) strtolower((string)$ext);
		//-- special handler for several well known non-HTML extension types
		switch((string)$lext) {
			case 'jpg':
			case 'gif':
			case 'png':
				$this->PageViewResetVars();
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'image/'.$lext);
				$this->PageViewSetCfg('rawdisp', 'inline; filename="'.(int)$this->errcode.'.'.$lext.'"');
				$this->PageViewSetVar('main', (string)\SmartFileSystem::read('modules/mod-samples/libs/views/img/error-xxx.'.$lext));
				return;
				break;
			case 'json':
				$this->PageViewResetVars();
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'text/json');
				$this->PageViewSetCfg('rawdisp', 'inline');
				$this->PageViewSetVar('main', \Smart::json_encode([ 'Error'.(int)$this->errcode => 'Json '.$this->errtext ]));
				return;
				break;
			case 'js':
				$this->PageViewResetVars();
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'application/javascript');
				$this->PageViewSetCfg('rawdisp', 'inline');
				$this->PageViewSetVar('main', '/* # Error'.(int)$this->errcode.': JS '.$this->errtext.' # */');
				return;
				break;
			case 'css':
				$this->PageViewResetVars();
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'text/css');
				$this->PageViewSetCfg('rawdisp', 'inline');
				$this->PageViewSetVar('main', '/* # Error'.(int)$this->errcode.': CSS '.$this->errtext.' # */');
				return;
				break;
			case 'xml':
				$this->PageViewResetVars();
				$this->PageViewSetCfg('rawpage', true);
				$this->PageViewSetCfg('rawmime', 'application/xml');
				$this->PageViewSetCfg('rawdisp', 'inline');
				$this->PageViewSetVar('main', '<error'.(int)$this->errcode.'>XML '.\Smart::escape_html($this->errtext).'</error'.(int)$this->errcode.'>');
				return;
				break;
			default:
				// nothing
		} //end if
		//--

		//--
		$this->PageViewSetVars([
			'title'				=> (string) (int)$this->errcode.' '.(string)$this->errtext,
			'main' 				=> (string) \SmartMarkersTemplating::render_file_template(
				'modules/mod-samples/libs/views/error-xxx.mtpl.htm',
				[
					'STATUS-CODE' 	=> (int) $this->errcode,
					'STATUS-MSG' 	=> (string) $this->errtext
				]
			)
		]);
		//--

	} //END FUNCTION


	final public function outputErrorPage($y_message, $y_html_message) {
		//--
		$this->Initialize();
		$this->Run();
		$this->ShutDown();
		$cfgs = $this->getRenderCfgs();
		$vars = $this->getRenderVars();
		if((!headers_sent()) && ($this->isRawPage())) {
			\SmartFrameworkRuntime::outputHttpHeadersNoCache();
			header('Content-Type: '.$cfgs['rawmime']);
			header('Content-Disposition: '.$cfgs['rawdisp']);
			return (string) $vars['main'];
		} //end if
		//--
		$template_path = (string) \SmartFileSysUtils::add_dir_last_slash(SMART_APP_TEMPLATES_DIR.\Smart::get_from_config('app.index-template-path'));
		$template_file = (string) \Smart::get_from_config('app.index-template-file');
		//--
		$vars['FOOTER'] = (string) \SmartMarkersTemplating::render_file_template(
			'modules/mod-samples/libs/views/error-xxx-footer.mtpl.htm',
			[
				'ERR-MESSAGE' 	=> (string) $y_message
			]
		);
		//--
		return (string) \SmartComponents::render_app_template(
			(string) $template_path,
			(string) $template_file,
			(array)  $vars
		);
		//--
	} //END FUNCTION


	private function getRenderVars() {
		//--
		return (array) $this->PageViewGetVars();
		//--
	} //END FUNCTION


	private function getRenderCfgs() {
		//--
		return (array) $this->PageViewGetCfgs();
		//--
	} //END FUNCTION


	private function isRawPage() {
		//--
		$cfgs = (array) $this->PageViewGetCfgs();
		if(((string)$cfgs['rawpage'] == 'yes') OR ($cfgs['rawpage'] === true)) {
			return true;
		} else {
			return false;
		} //end if else
		//--
	} //END FUNCTION


} //END CLASS


//end of php code
?>