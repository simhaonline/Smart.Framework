
// [LIB - Smart.Framework / JS / Browser Check]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

// DEPENDS: -

//==================================================================
//==================================================================

//================== [NO:evcode]

/**
 * CLASS :: Browser Test Compliance
 *
 * @package Sf.Javascript:Browser
 *
 * @desc This class provide a Browser Compliance Check for JavaScript
 * @author unix-world.org
 * @license BSD
 * @file browser_check.js
 * @version 20191123
 * @class Test_Browser_Compliance
 * @static
 *
 */
var Test_Browser_Compliance = new function() { // START CLASS

	// :: static


	/**
	 * Detect if the Browser is on a mobile device.
	 * @hint This is a very basic but effective and quick detection
	 *
	 * @memberof Test_Browser_Compliance
	 * @method checkIsMobileDevice
	 * @static
	 *
	 * @returns {Boolean} will return TRUE if Browser seems to be a Mobile Devices, FALSE if not
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
	 * Detect if a Browser support Cookies or have the Cookies enabled.
	 * @hint If a browser show that does not supports Cookies may be a situation like user disabled cookies in the browser
	 *
	 * @example
	 * if(!Test_Browser_Compliance.checkCookies()) {
	 * 		alert('NOTICE: The COOKIES are required to access this URL ...\nMake sure your browser allow this URL to set cookies.\nFollow this steps:\n\n1. ENABLE cookies in your browser from PREFERENCES (OPTIONS) / PRIVACY  Panel. \n2. RELOAD / REFRESH this URL. \n3. IF YOU STILL SEE THIS MESSAGE after following the above steps it means that your browser may have a problem or is outdated. In this case try to use another browser like:\n* FireFox 10 or later\n* Chrome 12 or later\n* Safari 5 or later\n* Internet Explorer 9 or later (or other browser that support cookies).');
	 * }
	 *
	 * @memberof Test_Browser_Compliance
	 * @method checkCookies
	 * @static
	 *
	 * @returns {Boolean} will return TRUE if Browser supports Cookies, FALSE if not
	 */
	this.checkCookies = function() {
		//--
		if(navigator.cookieEnabled === true) {
			return true;
		} else {
			return false;
		} //end if
		//--
	} //END FUNCTION


} //END CLASS

//==================================================================
//==================================================================

// END
