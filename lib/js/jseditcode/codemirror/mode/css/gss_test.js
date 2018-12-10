// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: https://codemirror.net/LICENSE

// mode/css/gss_test.js
// codemirror: v.5.42.0

(function() {
	"use strict";

	var mode = CodeMirror.getMode({indentUnit: 2}, "text/x-gss");
	function MT(name) { test.mode(name, mode, Array.prototype.slice.call(arguments, 1), "gss"); }

	MT("atComponent",
		 "[def @component] {",
		 "[tag foo] {",
		 "  [property color]: [keyword black];",
		 "}",
		 "}");

})();

// #END
