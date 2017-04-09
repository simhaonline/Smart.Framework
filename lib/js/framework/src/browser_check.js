
// [LIB - SmartFramework / JS / Browser Check]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.8 r.2017.03.27 / smart.framework.v.2.3

// DEPENDS: -

//==================================================================
//==================================================================

//================== [NO:evcode]

/**
* CLASS :: Browser Test Compliance
*
* @class Test_Browser_Compliance
* @static
*
* @module Smart.Framework/JS/Browser
*/
var Test_Browser_Compliance = new function() { // START CLASS

// :: static


/**
 * Detect if a Browser support Cookies or have the Cookies enabled.
 * If fail, an alert will be shown.
 *
 * @method checkCookies
 * @static
 */
this.checkCookies = function() {
	//--
	/*
	var the_datetime = new Date();
	var the_value = 'Time-' + the_datetime.getTime();
	//--
	var the_name = 'BrowserTestCompliance__Javascript__CookieTest';
	//--
	var the_cookie = '';
	try {
		document.cookie = the_name + '=' + encodeURIComponent('' + the_value) + '; path=/';
		the_cookie = document.cookie.match(new RegExp('(^|;)\\s*' + the_name + '=([^;\\s]*)'));
	} catch(err){}
	//--
	if((the_cookie) && (the_cookie !== null) && (the_cookie.length >= 3) && (decodeURIComponent(the_cookie[2]) == ('' + the_value))) {
	*/
	if(navigator.cookieEnabled === true) {
		// ok
	} else {
		alert('NOTICE: The COOKIES are required to access this URL ...\nMake sure your browser allow this URL to set cookies.\nFollow this steps:\n\n1. ENABLE cookies in your browser from PREFERENCES (OPTIONS) / PRIVACY  Panel. \n2. RELOAD / REFRESH this URL. \n3. IF YOU STILL SEE THIS MESSAGE after following the above steps it means that your browser may have a problem or is outdated. In this case try to use another browser like:\n* FireFox 10 or later\n* Chrome 12 or later\n* Safari 5 or later\n* Internet Explorer 9 or later (or other browser that support cookies).');
	} //end if
	//--
} //END FUNCTION


/**
 * Detect if the Browser is on a mobile device.
 * This is a very basic but effective and quick detection
 *
 * @method checkCookies
 * @static
 */
this.checkIsMobileDevice = function() {
	//--
	var isMobile = false;
	//-- https://coderwall.com/p/i817wa/one-line-function-to-detect-mobile-devices-with-javascript
	if((typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1)) {
		isMobile = true;
	} //end if
	//--
	if(((typeof window.screen.width !== "undefined") && (window.screen.width > 0)) && ((typeof window.screen.height !== "undefined") && (window.screen.height > 0))) {
		if((window.screen.width <= 320) || (window.screen.height <= 320)) {
			isMobile = true;
		} //end if
	} //end if
	//--
	return isMobile;
	//--
} //END FUNCTION


/**
 * Detect if the Internet Explorer matches minimum required version: at least IE9.
 * If fail, an alert will be shown.
 *
 * @method checkIEVersion
 * @static
 */
this.checkIEVersion = function(min_ver) { // check if version of internet explorer is at least 8 ; max is 10
	//--
	if(typeof min_ver == 'undefined') {
		min_ver = 9; // minimum required version of Internet Explorer
	} //end if
	//--
	var ver = -1; // Return value assumes failure.
	//--
	if((navigator.appName == 'Microsoft Internet Explorer') || (navigator.appName == 'Netscape')) { // IE11 or later identifies as Netscape
		//--
		var ua = navigator.userAgent;
		var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if(re.exec(ua) != null) {
			ver = parseFloat(RegExp.$1);
		} //end if
		//--
		if((ver > -1) && (ver < min_ver)) {
			alert('You are using an OLD VERSION of Internet Explorer (' + ver + ') OR your Internet Explorer have the (Old) Compatibility Mode Enabled which drops some of the HTML5 features.\n\nDISABLE the COMPATIBILITY MODE or UPDATE your Internet Explorer to version ' + min_ver + ' or later.\nYou can also use ALTERNATE BROWSERS:\n* Firefox 10 or later\n* Chrome 12 or later\n* Safari 5 or later\n\nRESET / UPGRADE / SWITCH your browser and try again.');
		} //end if
		//--
	} //end if
	//--
} //END FUNCTION
//--

} //END CLASS

//==================================================================
//==================================================================

// END
