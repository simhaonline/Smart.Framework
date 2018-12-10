// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: https://codemirror.net/LICENSE

// addon/mode/multiplex_test.js
// codemirror: v.5.42.0

(function() {
	CodeMirror.defineMode("markdown_with_stex", function(){
		var inner = CodeMirror.getMode({}, "stex");
		var outer = CodeMirror.getMode({}, "markdown");

		var innerOptions = {
			open: '$',
			close: '$',
			mode: inner,
			delimStyle: 'delim',
			innerStyle: 'inner'
		};

		return CodeMirror.multiplexingMode(outer, innerOptions);
	});

	var mode = CodeMirror.getMode({}, "markdown_with_stex");

	function MT(name) {
		test.mode(
			name,
			mode,
			Array.prototype.slice.call(arguments, 1),
			'multiplexing');
	}

	MT(
		"stexInsideMarkdown",
		"[strong **Equation:**] [delim&delim-open $][inner&tag \\pi][delim&delim-close $]");
})();

// #END
