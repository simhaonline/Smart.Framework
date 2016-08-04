<?php
// Controller: Samples/Toolkit
// Route: ?/page/samples.toolkit (?page=samples.toolkit)
// Author: unix-world.org
// r.2015-12-05

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
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

		//-- sample page variable from Request (GET/POST)
		$some_var_from_request = $this->RequestVarGet('extra_text', 'default', 'string');
		//--

		//--
		$module_area = $this->ControllerGetParam('module-area');
		$the_lang = (string) $this->ConfigParamGet('regional.language-id');
		$the_xlang = (string) $this->ConfigParamGet('regional.language-id'); // repeat this to check if caching works
		//--
		if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
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
			$the_page_cache_key = 'samples-toolkit-'.$module_area.'__'.SmartHashCrypto::sha384((string)$some_var_from_request);
			//--
		} //end if
		//--

		//--
		if($this->PageCacheisActive()) {
			//--
			$test_cache = $this->PageGetFromCache(
				'cached-samples', // the cache sample namespace
				$the_page_cache_key  // the unique key (if there are GET/POST variables that will change the content
			);
			//--
			if(Smart::array_size($test_cache) > 0) {
				if((is_array($test_cache['configs'])) && (is_array($test_cache['vars']))) { // if valid cache (test as we exported both arrays ... so they must be the 2 arrays again)
					$this->PageViewSetCfgs((array)$test_cache['configs']);
					$this->PageViewSetVars((array)$test_cache['vars']);
					$this->PageViewAppendVar('main', "\n".'<!-- Redis Cached Content Key: '.Smart::escape_html($the_page_cache_key).' -->'."\n"); // add a markup to the HTML to know was served from cache ...
					if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
						$this->SetDebugData('Page Cache Info', 'Serving page from Persistent Cache: Redis (override PHP Execution). Page key is: '.$the_page_cache_key);
					} // end if
					return; // the page was served from Cache (stop here)
				} //end if
			} //end if
			//--
		} //end if
		//--

		//=== if no cached, execute the code below ...

		//--
		$this->PageViewSetCfg('template-path', 'default');
		$this->PageViewSetCfg('template-file', 'template.htm');
		$this->PageViewSetCfg('template-modal-popup-file', 'template-modal.htm');
		//--

		//--
		$fcontent = SmartFileSystem::staticread('lib/framework/css/ux-toolkit-samples.html');
		$arr_data = explode('<body>', $fcontent);
		$fcontent = (string) $arr_data[1];
		$arr_data = explode('</body>', $fcontent);
		$fcontent = (string) $arr_data[0];
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
		$url_benchmark = Smart::url_add_params(
			$this->ControllerGetParam('url-script'),
			array(
				'page' => 'samples.benchmark.html'
			)
		);
		$url_benchmark = Smart::url_make_semantic($url_benchmark);
		//--

		//--
		$translator_core 			= SmartTextTranslations::getTranslator('@core', 'messages');
		//--
		$translator_mod_samples 	= SmartTextTranslations::getTranslator('mod-samples', 'samples');
		$txt_hello_world = $translator_mod_samples->text('hello-world');
		unset($translator_mod_samples); // this is just an internal test, normally the translator should not be unset ...
		$translator_mod_samples 	= SmartTextTranslations::getTranslator('mod-samples', 'samples');
		$txt_this_is_sf = $translator_mod_samples->text('this-is-smart-framework');
		//--

		//--
		$this->PageViewSetVars([
			'title' => 'Toolkit Samples',
			'main'	=> '<h1>This text should not be displayed, it was RESET !!!</h1>'
		]);
		$this->PageViewResetVar('main'); // test reset
		$this->PageViewSetVar(
			'main',
			SmartMarkersTemplating::render_template(
				'<h1>'.'[####TXT-HELLO-WORLD####]</h1><div align="right"><b>[####DATE-TIME|html####] [[####TXT-OK####]]'."\n".'</b></div><br><a class="ux-button ux-button-special" href="http://sourceforge.net/projects/warp-cms/files/smart-framework/" target="_blank"><i class="fa fa-cloud-download"></i> &nbsp; Download Smart.Framework (latest stable releases)</a> &nbsp;&nbsp;&nbsp; <a class="ux-button ux-button-highlight" href="http://demo.unix-world.org/smart-framework.docs/" target="_blank"><i class="fa fa-book"></i> &nbsp; Documentation for the Smart.Framework</a><br>'."\n".'<br><a class="ux-button ux-button-primary" href="[####URL-TESTUNIT|html####]"><i class="fa fa-object-group"></i> &nbsp; Go to the Smart.Framework PHP/Javascript Test &amp; Demo Suite</a> &nbsp;&nbsp;&nbsp; <a class="ux-button ux-button-secondary" href="[####URL-BENCHMARK|html####]"><i class="fa fa-line-chart"></i> &nbsp; Benchmark URL for Smart.Framework</a><br><br>',
				[
					'DATE-TIME' 		=> date('Y-m-d H:i:s O'),
					'TXT-OK' 			=> $translator_core->text('ok'),
					'TXT-HELLO-WORLD' 	=> '<span title="LanguageID: '.Smart::escape_html($the_xlang).'" style="cursor:help;">'.'['.Smart::escape_html($the_lang).']'.'</span>'.' '.$txt_hello_world.', '.$txt_this_is_sf.' - a modern PHP / Javascript framework featuring MVC + Middlewares',
					'URL-TESTUNIT'		=> $url_test_unit,
					'URL-BENCHMARK'		=> $url_benchmark
				]
			)
		);
		$this->PageViewAppendVar('main', '<hr><div style="color:#DDDDDD">'.Smart::escape_html('Unicode@String :: Smart スマート // Cloud Application Platform クラウドアプリケーションプラットフォーム :: áâãäåāăąÁÂÃÄÅĀĂĄ ćĉčçĆĈČÇ ďĎ èéêëēĕėěęÈÉÊËĒĔĖĚĘ ĝģĜĢ ĥħĤĦ ìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮ ĳĵĲĴ ķĶ ĺļľłĹĻĽŁ ñńņňÑŃŅŇ óôõöōŏőøœÒÓÔÕÖŌŎŐØŒ ŕŗřŔŖŘ șşšśŝßȘŞŠŚŜ țţťȚŢŤ ùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲ ŵŴ ẏỳŷÿýẎỲŶŸÝ źżžŹŻŽ').'</div><hr><div align="right">['.Smart::escape_html($some_var_from_request).']</div>');
		$this->PageViewAppendVar('main', trim($fcontent));
		//--

		//== cache page (if redis - persistent cache is set in config)

		//-- if Redis is active this will cache the page for 1 hour ...
		if($this->PageCacheisActive()) {
			//--
			$this->PageSetInCache(
				'cached-samples', // the cache sample namespace
				$the_page_cache_key, // the unique key (if there are GET/POST variables that will change the content
				array(
					'configs' => $this->PageViewGetCfgs(),
					'vars' => $this->PageViewGetVars()
				), // this will het the full array with all page vars and configs
				3600 // 60 mins
			);
			//--
			if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
				$this->SetDebugData('Page Cache Info', 'Setting page in Persistent Cache: Redis (after PHP Execution). Page key is: '.$the_page_cache_key);
			} //end if
			//--
		} else {
			//--
			if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
				$this->SetDebugData('Page Cache Info', 'Persistent Cache (Redis) is not active. Serving Page from PHP Execution.');
			} //end if
			//--
		} //end if else
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