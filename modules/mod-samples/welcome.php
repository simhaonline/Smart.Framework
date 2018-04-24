<?php
// [@[#[!SF.DEV-ONLY!]#]@]
// Controller: Samples/Welcome
// Route: ?/page/samples.welcome (?page=samples.welcome)
// Author: unix-world.org
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

define('SMART_APP_MODULE_AREA', 'SHARED'); // INDEX, ADMIN, SHARED

// This is a Sample Controller of Smart.Framework / Samples Module
// The controller classes: SmartAppIndexController and SmartAppAdminController can be complete separated in different files, they can be extended from SmartAbstractAppController or one from each other.
// The SMART_APP_MODULE_AREA constant must be adjusted as necessary: INDEX (allow just SmartAppIndexController) ; ADMIN (allow just SmartAppAdminController) ; SHARED (allow both: SmartAppIndexController and SmartAppAdminController) - in the same controller

/**
 * Index Controller
 *
 * @ignore
 *
 */
class SmartAppIndexController extends SmartAbstractAppController {

	public function Run() {

		//-- dissalow run this sample if not test mode enabled
		if(!defined('SMART_FRAMEWORK_TEST_MODE') OR (SMART_FRAMEWORK_TEST_MODE !== true)) {
			$this->PageViewSetErrorStatus(503, 'ERROR: Test mode is disabled ...');
			return;
		} //end if
		//--

		//-- sample page variable from Request (GET/POST)
		$some_var_from_request = $this->RequestVarGet('extra_text', 'default', 'string');
		//--

		//--
		$module_area = $this->ControllerGetParam('module-area');
		$the_lang = (string) $this->ConfigParamGet('regional.language-id');
		$the_xlang = (string) $this->ConfigParamGet('regional.language-id'); // repeat this to check if caching works
		//--
		if($this->IfDebug()) {
			$this->SetDebugData('Module Area', $module_area);
			$this->SetDebugData('Module Path', $this->ControllerGetParam('module-path'));
			$this->SetDebugData('Module Name', $this->ControllerGetParam('module-name'));
			$this->SetDebugData('URL Script', $this->ControllerGetParam('url-script'));
			$this->SetDebugData('URL Path', $this->ControllerGetParam('url-path'));
			$this->SetDebugData('URL Address', $this->ControllerGetParam('url-addr'));
			$this->SetDebugData('URL Page', $this->ControllerGetParam('url-page'));
			$this->SetDebugData('Config / Language ID', $the_lang);
		} //end if
		//--

		//--
		if($this->PageCacheisActive()) {
			//-- because the Request can modify the content, also the unique key must take in account variables that will vary the page config or page content vars
			$the_page_cache_key = (string) $this->PageCacheSafeKey('samples-welcome-'.$module_area.'@'.SmartTextTranslations::getLanguage().'__'.SmartHashCrypto::sha384((string)$some_var_from_request));
			//--
		} //end if
		//--

		//--
		if($this->PageCacheisActive()) {
			//--
			$test_pcache_data_arr = $this->PageGetFromCache(
				'cached-samples', // the cache sample namespace
				$the_page_cache_key  // the unique key (if there are GET/POST variables that will change the content
			);
			//--
			if(Smart::array_size($test_pcache_data_arr) > 0) {
				if($this->PageViewSetData($test_pcache_data_arr) === true) {
					//-- *optional* code
					if($this->IfDebug()) {
						$this->SetDebugData('Page Cache Info', 'Serving page from Persistent Cache (override PHP full code Execution). Page namespace/key is: cached-samples / '.$the_page_cache_key);
					} // end if
					$this->PageViewPrependVar('main', "\n".'<!-- [R]: Cached Content ; Key: '.Smart::escape_html($the_page_cache_key).' -->'."\n"); // add a markup to the HTML to know was served from cache ...
					$this->PageViewAppendVar('main',  "\n".'<!-- [R]: Cached Content ; Key: '.Smart::escape_html($the_page_cache_key).' -->'."\n"); // add a markup to the HTML to know was served from cache ...
					//-- #end: *optional code
					return; // this is mandatory, the page was served from Cache (stop here ...)
				} //end if
			} //end if
			//--
		} //end if
		//--

		//=== if not cached, execute the code below ...

		//--
		$this->PageViewResetRawHeaders();
		$this->PageViewSetRawHeaders([
			'Z-Test-Header-1:' 	=> 'This is a test (1)',
			'Z-Test-Header-2' 	=> 'This is a test (2)'
		]);
		$this->PageViewSetRawHeader(
			'Z-Test-Header-3', 'This is a test (3)'
		);
		//--

		//--
		$this->PageViewSetCfg('template-path', 'default'); 		// set the template path (must be inside etc/templates/)
		$this->PageViewSetCfg('template-file', 'template.htm');	// set the template file
		//--

		//-- building a semantic URL
		$url_test_unit = Smart::url_add_params(
			$this->ControllerGetParam('url-script'),
			array(
				'page' => 'samples.testunit',
				'tab' => 0
			)
		); // will generate: index.php?page=samples.testunit OR admin.php?page=samples.testunit
		$url_test_unit = Smart::url_make_semantic($url_test_unit); // convert the above to a pretty URL as: ?/page/samples.testunit (in this case index.php is ignored) OR admin.php?/page/samples.testunit
		//--

		//-- building a regular URL
		if((string)$module_area == 'admin') {
			$sign_benchmark = '[A]';
			$page_benchmark = 'samples.benchmark-with-session.html';
		} else { // index (default)
			$sign_benchmark = '[I]';
			$page_benchmark = 'samples.benchmark.html';
		} //end if else
		$url_benchmark = Smart::url_add_params(
			$this->ControllerGetParam('url-script'),
			array(
				'page' => (string) $page_benchmark
			)
		);
		$url_benchmark = Smart::url_make_semantic($url_benchmark);
		//--

		//--
		$translator_core 			= SmartTextTranslations::getTranslator('@core', 'messages');
		//--
		$translator_mod_samples 	= SmartTextTranslations::getTranslator('mod-samples', 'samples');
		$txt_hello_world = $translator_mod_samples->text('hello-world'); // get key with defaults (escape HTML) + fallback on english if not found
		unset($translator_mod_samples); // this is just an internal test, normally the translator should not be unset ...
		$translator_mod_samples 	= SmartTextTranslations::getTranslator('mod-samples', 'samples');
		$txt_this_is = $translator_mod_samples->text('this-is');
		//--

		//--
		$this->PageViewSetVars([
			'title' => SmartUtils::extract_title('Smart Framework - A   PHP / Javascript Framework for 123 Web !!!!!', 57, true),
			'main'	=> '<h1>This text should not be displayed, it was RESET !!!</h1>'
		]);
		$this->PageViewResetVar('main'); // test reset
		$this->PageViewSetVar(
			'main',
			SmartMarkersTemplating::render_file_template(
				$this->ControllerGetParam('module-view-path').'welcome.inc.htm',
				[
					'DATE-TIME' 		=> (string) date('Y-m-d H:i:s O'),
					'TXT-OK' 			=> (string) $translator_core->text('ok'),
					'TXT-HELLO-WORLD' 	=> (string) $txt_hello_world,
					'TXT-THIS-IS' 		=> (string) $txt_this_is,
					'URL-TESTUNIT'		=> (string) $url_test_unit,
					'URL-BENCHMARK'		=> (string) $url_benchmark,
					'AREA-BENCHMARK' 	=> (string) $sign_benchmark,
					'THE-LANGUAGE' 		=> (string) $the_lang,
					'THE-LANGUAGE-ID' 	=> (string) $the_xlang
				]
			)
		);
		//--
		$this->PageViewAppendVar('main', '<div style="text-align:right; color:#CCCCCC;">['.Smart::escape_html($some_var_from_request).']</div>'.'<hr>'.'<div style="color:#DDDDDD">Smart.Framework have Full Unicode (UTF-8) Support: '.Smart::escape_html('Unicode@String :: Smart スマート // Cloud Application Platform クラウドアプリケーションプラットフォーム :: áâãäåāăąÁÂÃÄÅĀĂĄ ćĉčçĆĈČÇ ďĎ èéêëēĕėěęÈÉÊËĒĔĖĚĘ ĝģĜĢ ĥħĤĦ ìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮ ĳĵĲĴ ķĶ ĺļľłĹĻĽŁ ñńņňÑŃŅŇ óôõöōŏőøœÒÓÔÕÖŌŎŐØŒ ŕŗřŔŖŘ șşšśŝßȘŞŠŚŜ țţťȚŢŤ ùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲ ŵŴ ẏỳŷÿýẎỲŶŸÝ źżžŹŻŽ').'</div><hr><br>');
		$this->PageViewAppendVar('main', (string) (new SmartMarkdownToHTML(true, false))->text((string)SmartFileSystem::read('README.md')));
		$this->PageViewAppendVar('main', '<br><hr><br><br>');
		$txt_meta = 'Smart.Framework, a modern, high-performance   PHP / Javascript Framework (for Web) featuring MVC + Middlewares #123-456:789+10 11.12';
		$this->PageViewSetVars([
			'head-meta' => '<meta name="description" content="'.Smart::escape_html(SmartUtils::extract_description($txt_meta, 150, true)).'">'."\n".'<meta name="keywords" content="'.Smart::escape_html(SmartUtils::extract_keywords($txt_meta, 90, true)).'">'."\n"
		]);
		//--

		//-- the purpose of setting here 202 instead of 200 is just for testing the export of cfgs ...
		$this->PageViewSetOkStatus(202); // HTTP OK Status Code ; this is optional ; by default if no status code is set the 200 status code is served
		//$this->PageViewSetErrorStatus(500, 'Testing 500 Status Code ...'); // HTTP ERROR Status Code + Message ; this should be used for: 400, 403, 404, 500, 503
		//$this->PageViewSetRedirectUrl('https://demo.unix-world.org/smart-framework/', 302); // sample redirection with 302 (temporary) or can be 301 (permanent)
		//--

		//== cache page (if persistent cache is set in config)

		//-- if pCache is active this will cache the page for 1 hour ...
		if($this->PageCacheisActive()) {
			//--
			$this->PageSetInCache(
				'cached-samples', 					// the cache sample namespace
				(string) $the_page_cache_key, 		// the cache unique key (if there are GET/POST variables that will change the content
				(array)  $this->PageViewGetData(), 	// this will get the full array with all page vars, configs and heads
				(int)    3600 						// cache time: 60 mins
			);
			//--
			if($this->IfDebug()) {
				$this->SetDebugData('Page Cache Info', 'Setting page in Persistent Cache (after PHP Execution). Page key is: '.$the_page_cache_key);
			} //end if
			//--
		} else {
			//--
			if($this->IfDebug()) {
				$this->SetDebugData('Page Cache Info', 'Persistent Cache is not active. Serving Page from PHP Execution.');
			} //end if
			//--
		} //end if else
		//--

		//==

		//-- after cache content, to avoid save it into cache
		$this->PageViewPrependVar('main', "\n".'<!-- [L]: Live Content -->'."\n"); // add a markup to the HTML to know was served live (not cached) ...
		$this->PageViewAppendVar('main',  "\n".'<!-- [L]: Live Content -->'."\n"); // add a markup to the HTML to know was served live (not cached) ...
		//--

	} //END FUNCTION

} //END CLASS

/**
 * Admin Controller (optional)
 *
 * @ignore
 *
 */
class SmartAppAdminController extends SmartAppIndexController {

	// this will clone the SmartAppIndexController to run exactly the same action in admin.php
	// or this can implement a completely different controller if it is accessed via admin.php

} //END CLASS

//end of php code
?>