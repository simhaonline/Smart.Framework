
// JQuery Compatibility: Browser
// v.2.3 r.2016.03.11

//===== INFO: $.browser has been deprecated and removed since jQuery 1.9
// provide: $.browser
// source: https://github.com/jquery/jquery-migrate/blob/master/src/core.js
// copyright: MIT License / Based on jQuery migration plugin
// modified by: unixman
//=====

//--
jQuery.uaMatch = function(ua) {
	//--
	ua = ua.toLowerCase();
	//--
	var match = / (firefox)\//.exec(ua) || / (fxios)\//.exec(ua) || /(msie) ([\w.]+)/.exec(ua) || / (trident)\//.exec(ua) || / (edge)\//.exec(ua) || / (opr)\//.exec(ua) || / (opios)\//.exec(ua) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) || / (crios)\//.exec(ua) || /(chromium)[ \/]([\w.]+)/.exec(ua) || /(chrome)[ \/]([\w.]+)/.exec(ua) || /(webkit)[ \/]([\w.]+)/.exec(ua) || ua.indexOf('compatible') < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) || [];
	//--
	return {
		browser: match[1] || '',
		version: match[2] || '0'
	};
	//--
} //END FUNCTION
//--

//--
jQuery.SmartBrowserGetVersion = function() {
	//--
	browser = {};
	//--
	matched = jQuery.uaMatch(navigator.userAgent);
	//--
	if(matched.browser) {
		browser[matched.browser] = true;
		browser.version = matched.version;
	} //end if
	//-- fixes
	if((browser.trident) || (browser.edge)) {
		browser.msie = true;
	} //end if
	if((browser.opr) || (browser.opios)) {
		browser.opera = true;
	} //end if
	if((browser.crios) || (browser.chromium)) {
		browser.chrome = true;
	} //end if
	if(browser.fxios) {
		browser.firefox = true;
	} //end if
	if(browser.webkit) {
		browser.safari = true;
	} //end if else
	if((browser.chrome) || (browser.opera)) {
		browser.webkit = true;
	} //end if
	if(browser.firefox) {
		browser.mozilla = true;
	} //end if
	//--
	jQuery.browser = browser;
	//--
} //END FUNCTION
//--

//--
//if(!jQuery.browser) {
jQuery.SmartBrowserGetVersion();
//} //end if
//--

// #END FILE
