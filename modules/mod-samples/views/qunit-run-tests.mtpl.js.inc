[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests: START :: r.20190103

(function() {

	// Settings

	var charSet 				= '[####CHARSET|js####]';
	var phpVersion 				= '[####PHP-VERSION|js####]';
	var smartFrameworkVersion 	= '[####SF-VERSION|js####]';
	var appRealm 				= '[####APP-REALM|js####]';
	var debugMode 				= '[####DEBUG-MODE|js####]';
	var currentLanguage 		= '[####LANG|js####]';
	var modulePath 				= '[####MODULE-PATH|js####]';
	var baseScript  			= '[####SRV-SCRIPT|js####]';

	// @Self Tests

	QUnit.module('@App-Environment', function(){
		QUnit.test('Server Side Tests: PHP / Framework', function(assert) {
			var expected;
			expected = 'charSet: UTF-8';
			assert.equal('charSet: ' + charSet, expected, expected);
			expected = 'phpVersion: [####PHP-VERSION|js####]';
			assert.equal('phpVersion: ' + phpVersion, expected, expected);
			expected = 'smartFrameworkVersion: [####SF-VERSION|js####]';
			assert.equal('smartFrameworkVersion: ' + smartFrameworkVersion, expected, expected);
			expected = 'debugMode: no';
			assert.equal('debugMode: ' + debugMode, expected, expected);
			expected = 'appRealm: [####APP-REALM|js####] / [####SRV-SCRIPT|js####]';
			assert.equal('appRealm: ' + appRealm + ' / ' + baseScript, expected, expected);
			expected = 'currentLanguage: en';
			assert.equal('currentLanguage: ' + currentLanguage, expected, expected);
			expected = 'modulePath: [####MODULE-PATH|js####]';
			assert.equal('modulePath: ' + modulePath, expected, expected);
		});
	});

	// Smart.Framework Tests (incl Smart.Framework.Modules Tests by Area)
	[@@@@SUB-TEMPLATE:!modules/mod-samples/tests/core-tests.mtpl.js.inc!@@@@]
	[@@@@SUB-TEMPLATE:!modules/mod-samples/tests/templating-tests.mtpl.js.inc!@@@@]
	[@@@@SUB-TEMPLATE:!modules/mod-samples/tests/database-tests.mtpl.js.inc!@@@@]

	// Smart.Framework.Modules Tests (optional)
	[@@@@SUB-TEMPLATE:?!modules/mod-lang-detect/tests/module-test.mtpl.js.inc!@@@@]

})();

//===== QUnit Tests: #END
