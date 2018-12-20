
/*
QUnit.test('hello test ok', function(assert) {
	assert.ok(
		1 == '1',
		'Passed!'
	);
});
QUnit.test('hello test with error', function(assert) {
	var value = '[####HALO|js####]';
	assert.equal(
		value, 'hello',
		'We expect value to be hello'
	);
});

QUnit.test('async test', function(assert) {
	var done = assert.async();
	jQuery.get('?json', function(data) {
		var value = data.status;
		assert.equal(
			value, 'OK',
			'OK!'
		);
		done();
	}, 'json');
});

*/

QUnit.test('async test', function(assert) {
	var QAsyncTestDone = assert.async();
	// create a temporary `iframe`, make it hidden, and attach to the DOM
	var frame = $('<iframe id="q-sandbox" src="?page=samples.templating-test-extended" style="display:none;"></iframe>').appendTo('body');
	// check that the frame has loaded content
	$(frame).on('load', function(){
		// grab the HTML from the body, using the raw DOM node (frame[0]) and more specifically, it's `contentDocument` property
		var html = $(this).contents();
		// check the HTML
		//console.log(html);
		// remove the temporary iframe
		setTimeout(function() {
			var value = html.find('#qunit-test-result').text();
			html = null;
			assert.equal(
				value, 'JS/PHP Render comparison for reference Markers-TPL: OK',
				'OK!'
			);
			$('#q-sandbox').attr('src', '').remove();
			QAsyncTestDone();
		}, 500);
	});
});

