[%%%%COMMENT%%%%]
// IMPORTANT: use only JavaScript code here, no HTML !
[%%%%/COMMENT%%%%]
//===== QUnit Tests.Templating: START :: r.20190103

(function() {

	QUnit.module('Templating', function(){

		QUnit.test('Markers-TPL Extended Test: PHP / Javascript :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=samples.templating-test-extended',
				500,
				assert,
				'Test OK: JS/PHP Render comparison for Markers-TPL.'
			);
		});

		QUnit.test('Markers-TPL Demo Test: PHP :: iFrame', function(assert) {
			SmartQUnit.runiFrameTest(
				baseScript + '?page=samples.templating-test',
				500,
				assert,
				'Markers-TPL w. Sub-Template Render (Table with #3 columns):'
			);
		});

	});

})();

//===== QUnit Tests.Templating: #END

// Smart.Framework.Modules Tests (optional)
[@@@@SUB-TEMPLATE:?!modules/mod-tpl-nette-latte/tests/module-test.mtpl.js.inc!@@@@]
[@@@@SUB-TEMPLATE:?!modules/mod-tpl-twig/tests/module-test.mtpl.js.inc!@@@@]
[@@@@SUB-TEMPLATE:?!modules/mod-tpl-typo3-fluid/tests/module-test.mtpl.js.inc!@@@@]
