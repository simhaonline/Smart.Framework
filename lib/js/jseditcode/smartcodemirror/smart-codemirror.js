// Smart Code Mirror JS
// (c) 2016 unix-world.org
// r.181207


function SmartCodeMirror_Initialize(textarea, isReadOnly, codeType, originalWidth, originalHeight, visualTheme, lineNums, cursorRate, menu, showFold) {
	//--
	textarea = String(textarea);
	//--
	var the_menu_div;
	//--
	if((typeof menu == 'undefined') || (menu == null) || (menu == '') || (!menu)) {
		//--
		the_menu_div = null;
		//--
	} else {
		//--
		menu = String(menu);
		//--
		var innerMenu = '';
		innerMenu += '<span style="cursor:pointer;" title="Toggle Full Screen" onClick="SmartCodeMirror_ToggleFullScreen(SmartCodeMirror__' + textarea + '__Instance, $(\'#' + menu + '\'), SmartCodeMirror__' + textarea + '__Width);"><img src="lib/js/jseditcode/smartcodemirror/images/fullscreen.png"></span>';
		innerMenu += ' ';
		innerMenu += '<span style="cursor:pointer;" title="Search for Text ..."><img onClick="SmartCodeMirror__' + textarea + '__Instance.execCommand(\'find\');" src="lib/js/jseditcode/smartcodemirror/images/find.png"></span>';
		innerMenu += ' ';
		//--
		if(isReadOnly === false) {
			innerMenu += '<span style="cursor:pointer;" title="Replace Text ..."><img onClick="SmartCodeMirror__' + textarea + '__Instance.execCommand(\'replace\');" src="lib/js/jseditcode/smartcodemirror/images/findreplace.png"></span>';
			innerMenu += '<span style="cursor:pointer;" title="Undo"><img onClick="SmartCodeMirror__' + textarea + '__Instance.execCommand(\'undo\');" src="lib/js/jseditcode/smartcodemirror/images/undo.png"></span>';
			innerMenu += '<span style="cursor:pointer;" title="Redo"><img onClick="SmartCodeMirror__' + textarea + '__Instance.execCommand(\'redo\');" src="lib/js/jseditcode/smartcodemirror/images/redo.png"></span>';
		} //end if else
		//--
		if((isReadOnly === false) && (codeType === 'text/x-markdown')) {
			//--
			innerMenu += '<div style="display:inline-block; width:24px; height:24px;"></div>';
			//--
			var mkdw_hint = '';
			var mkdw_code = '';
			//--  h1 (h2..h6)
			mkdw_hint = '\n# H1\n## H2\n### H3\n#### H4\n##### H5\n###### H6';
			mkdw_code = '# ';
			innerMenu += '<span style="cursor:pointer;" title="Heading Styles: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'\');" src="lib/js/jseditcode/smartcodemirror/images/style.png"></span>';
			//-- bold
			mkdw_hint = '\n**Bold text**\n__Bold text__';
			mkdw_code = '**';
			innerMenu += '<span style="cursor:pointer;" title="Bold: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'cursor\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/bold.png"></span>';
			//-- italic
			mkdw_hint = '\n*Italic text*\n_Italic text_';
			mkdw_code = '*';
			innerMenu += '<span style="cursor:pointer;" title="Italic: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'cursor\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/italic.png"></span>';
			//-- strike
			mkdw_hint = '\n~~Striketrough text~~';
			mkdw_code = '~~';
			innerMenu += '<span style="cursor:pointer;" title="Striketrough: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'cursor\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/strikethrough.png"></span>';
			//-- sub
			mkdw_hint = '\n~Subscript text~';
			mkdw_code = '~';
			innerMenu += '<span style="cursor:pointer;" title="Subscript: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'cursor\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/subscript.png"></span>';
			//-- sub
			mkdw_hint = '\n^Superscript text^';
			mkdw_code = '^';
			innerMenu += '<span style="cursor:pointer;" title="Superscript: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'cursor\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/superscript.png"></span>';
			//-- unordered list
			mkdw_hint = '\n- List Item\n- List Item\n- List Item\n...\n- List Item';
			mkdw_code = '- ';
			innerMenu += '<span style="cursor:pointer;" title="Unordered List: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'\');" src="lib/js/jseditcode/smartcodemirror/images/bullets.png"></span>';
			//-- ordered list
			mkdw_hint = '\n1. List Item\n1. List Item\n1. List Item\n...\n1. List Item';
			mkdw_code = '1. ';
			innerMenu += '<span style="cursor:pointer;" title="Ordered List: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'\');" src="lib/js/jseditcode/smartcodemirror/images/numbering.png"></span>';
			//-- quote
			mkdw_hint = '\n> Quoted Text\n> Quoted Text...\n> Quoted Text'
			mkdw_code = '> ';
			innerMenu += '<span style="cursor:pointer;" title="Quote: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\', \'\');" src="lib/js/jseditcode/smartcodemirror/images/blockquote.png"></span>';
			//-- hyperlink
			mkdw_hint = '\n[Linked Text](http://url.link){@target=_blank}\nSimple Link: <http://www.google.com>';
			mkdw_code = '(http://url.link)';
			innerMenu += '<span style="cursor:pointer;" title="Hyperlink: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'cursor\', \'[\', \']' + SmartJS_CoreUtils.escape_js(mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/link.png"></span>';
			//-- image
			mkdw_hint = '\nsimple ![Alt](http://image.gif.jpg.png)\nwith title ![Alt](http://image.gif.jpg.png "Title")';
			mkdw_code = '![Alternate Text](path/or/url/to/image.gif.jpg.png "Image Title")';
			innerMenu += '<span style="cursor:pointer;" title="Image: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'\', \'' + SmartJS_CoreUtils.escape_js('\n' + mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/image.png"></span>';
			//-- table
			mkdw_code = '| TH 1 | TH 2 |\n| --- | --- |\n| TD 1.1 | TD 1.2 |\n| TD 2.1 | TD 2.2 |\n';
			mkdw_hint = '| One      | Two      | Three    | Four     |\n| :------- |:--------:| --------:| -------- |\n| Cell 1.1 | Cell 1.2 | Cell 1.3 | Cell 1.4 |\n| Cell 2.1 ||| Cell 2.1-2.4 (spans over 3 cells) {@colspan=3}|';
			innerMenu += '<span style="cursor:pointer;" title="' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'\', \'' + SmartJS_CoreUtils.escape_js('\n\n' + mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/table.png"></span>';
			//-- hr
			mkdw_hint = '\n- - -\n* * *';
			mkdw_code = '- - -';
			innerMenu += '<span style="cursor:pointer;" title="Horizontal Rule: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'\', \'' + SmartJS_CoreUtils.escape_js('\n' + mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/rule.png"></span>';
			//-- code
			mkdw_code = '```\nprint(123);\n```';
			mkdw_hint = mkdw_code;
			innerMenu += '<span style="cursor:pointer;" title="Code Sequence: ' + SmartJS_CoreUtils.escape_html(mkdw_hint) + '"><img onClick="SmartCodeMirror_AddCode(SmartCodeMirror__' + textarea + '__Instance, \'line\', \'\', \'' + SmartJS_CoreUtils.escape_js('\n\n' + mkdw_code) + '\');" src="lib/js/jseditcode/smartcodemirror/images/code.png"></span>';
			//--
		} //end if
		//--
		the_menu_div = $('#' + menu);
		the_menu_div.addClass('CodeMirror-SmartMenu-default').width(originalWidth).html(innerMenu);
		//--
	} //end if else
	//--
	var showMatchBrackets = true;
	var showMatchTags = false;
	if(codeType === 'text/x-markdown') {
		showMatchBrackets = false; // this make the editing go slower for markdown
	} else if((codeType === 'text/xml') || (codeType === 'text/html') || (codeType === 'text/x-markdown')) {
		showMatchTags = true;
	} //end if
	//--
	cursorRate = parseInt(cursorRate);
	if(cursorRate < 0) {
		cursorRate = 0;
	} else if(cursorRate > 1000) {
		cursorRate = 1000;
	} //end if else
	//--
	if((typeof showFold == 'undefined') || (showFold === null)) {
		showFold = true; // default
	} else {
		showFold = !!showFold; // force bool
	} //end if else
	//--
	if(!!isReadOnly) {
		showFold = false;
	} //end if
	//--
	var arrGutters = [];
	if(!!lineNums) {
		arrGutters.push('CodeMirror-linenumbers');
	} //end if
	if(!!showFold) {
		arrGutters.push('CodeMirror-foldgutter');
	} //end if
	//--
	var the_code_editor = CodeMirror.fromTextArea(document.getElementById(textarea), {
		'readOnly': !!isReadOnly,
		'lineNumbers': !!lineNums,
		'foldGutter': !!showFold,
		'gutters': arrGutters,
		'smartIndent': true,
		'cursorBlinkRate': cursorRate,
		'mode': String(codeType),
		'theme': String(visualTheme),
		'matchBrackets': !!showMatchBrackets,
		'matchTags': !!showMatchTags,
		'styleActiveLine': true,
		'dragDrop': false,
		'undoDepth': 250,
		'extraKeys': {
			'F11': function(cm) {
				SmartCodeMirror_ToggleFullScreen(cm, the_menu_div, originalWidth);
			},
			'Esc': function(cm) {
				if(cm.getOption('fullScreen')) {
					SmartCodeMirror_ToggleFullScreen(cm, the_menu_div, originalWidth);
				} //end if
			}
		},
	});
	//--
	the_code_editor.setSize(originalWidth, originalHeight);
	//--
	if(isReadOnly === false) {
		the_code_editor.on('blur', function(){
			the_code_editor.save();
		});
	} //end if
	//--
	return the_code_editor;
	//--
} //END FUNCTION


function SmartCodeMirror_ToggleFullScreen(cm, menu, originalWidth) {
	//--
	var is_fullscreen = cm.getOption('fullScreen');
	//--
	if(!is_fullscreen) {
		cm.setOption('fullScreen', true);
		if(menu !== null) {
			menu.addClass('CodeMirror-SmartMenu-fullscreen').width('100%');
		} //end if
	} else {
		cm.setOption('fullScreen', false);
		if(menu !== null) {
			menu.removeClass('CodeMirror-SmartMenu-fullscreen').width(originalWidth);
		} //end if
	} //end if else
	//--
} //END FUNCTION


function SmartCodeMirror_AddCode(cm, mode, code_prefix, code_sufix) {
	//--
	if((typeof code_prefix == 'undefined') || (code_prefix == null)) {
		code_prefix = '';
	} //end if
	if((typeof code_sufix == 'undefined') || (code_sufix == null)) {
		code_sufix = '';
	} //end if
	//--
	var doc = cm.getDoc(); // gets the document
	//--
	switch(mode) {
		case 'line':
			var cursor, line, pos;
			if(code_prefix != '') {
				cursor = doc.getCursor(); // gets the line number in the cursor position
				line = doc.getLine(cursor.line); // get the line contents
				pos = { // create a new object to avoid mutation of the original selection
					line: cursor.line,
					ch: 0 // set the character position to the end of the line
				};
				doc.replaceRange(code_prefix, pos); // adds prefix to the start of the line
			} //end if
			if(code_sufix != '') {
				cursor = doc.getCursor(); // gets the line number in the cursor position
				line = doc.getLine(cursor.line); // get the line contents
				pos = { // create a new object to avoid mutation of the original selection
					line: cursor.line,
					ch: line.length // set the character position to the end of the line
				};
				doc.replaceRange(code_sufix, pos); // adds suffix to the end of line
			} //end if
			break;
		case 'cursor':
			var cursor = doc.getCursor(); // gets the line number in the cursor position
			var line = doc.getLine(cursor.line); // get the line contents
			var selected = doc.getSelection();
			if(selected == '') {
				selected = ' ';
			} //end if
			doc.replaceSelection(code_prefix + selected + code_sufix); // adds prefix and suffix at the selection
			break;
		default:
			alert('Invalid Mode for SmartCodeMirror_AddCode(): ' + mode);
	} //end switch
	//--
	cm.save();
	//--
} //END FUNCTION


// END
