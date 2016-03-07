
// JQuery Compatibility: Browser
// v.2.3 r.2016.03.07

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
	var match = /(chromium)[ \/]([\w.]+)/.exec(ua) || /(chrome)[ \/]([\w.]+)/.exec(ua) || /(webkit)[ \/]([\w.]+)/.exec(ua) || /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) || /(msie) ([\w.]+)/.exec(ua) || ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) || [];
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
	//-- Chrome/Chromium is Webkit, but Webkit can be also Safari.
	if(browser.chromium) {
		browser.webkit = true;
	} else if(browser.chrome) {
		browser.webkit = true;
	} else if(browser.webkit) {
		browser.safari = true;
	} //end if else
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
