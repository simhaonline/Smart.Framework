// Smart Code Mirror JS
// (c) 2016 unix-world.org
// r. 160226


function SmartCodeMirror_Initialize(textarea, isReadOnly, codeType, originalWidth, originalHeight, visualTheme, lineNums, cursorRate, menu) {
	//--
	var the_menu_div;
	//--
	if((typeof menu == 'undefined') || (menu == null) || (menu == '')) {
		//--
		the_menu_div = null;
		//--
	} else {
		//--
		var innerMenu = '';
		innerMenu += '<span style="cursor:pointer;" title="Toggle Full Screen" onClick="SmartCodeMirror_ToggleFullScreen(SmartCodeMirror__' + textarea + '__Instance, $(\'#' + menu + '\'), SmartCodeMirror__' + textarea + '__Width);"><img src="lib/js/jseditcode/smartcodemirror/images/fullscreen.png"></span>';
		innerMenu += ' ';
		innerMenu += '<span style="cursor:pointer;" title="Search for Text ..."><img onClick="SmartCodeMirror__' + textarea + '__Instance.execCommand(\'find\');" src="lib/js/jseditcode/smartcodemirror/images/find.png"></span>';
		innerMenu += ' ';
		//--
		if(isReadOnly === false) {
			innerMenu += '<span style="cursor:pointer;" title="Replace Text ..."><img onClick="SmartCodeMirror__' + textarea + '__Instance.execCommand(\'replace\');" src="lib/js/jseditcode/smartcodemirror/images/findreplace.png"></span>';
		} else {
			innerMenu += '<span style="cursor:help; opacity:0.25;" title="Replace is not available ... This content is read-only !"><img src="lib/js/jseditcode/smartcodemirror/images/findreplace.png"></span>';
		} //end if else
		//--
		the_menu_div = $('#' + menu);
		the_menu_div.addClass('CodeMirror-SmartMenu-default').width(originalWidth).html(innerMenu);
		//--
	} //end if else
	//--
	var the_code_editor = CodeMirror.fromTextArea(document.getElementById(textarea), {
		'readOnly': isReadOnly,
		'lineNumbers': lineNums,
		'cursorBlinkRate': cursorRate,
		'mode': codeType,
		'theme': visualTheme,
		'matchBrackets': true,
		'matchTags': true,
		'highlightSelectionMatches': {showToken: /\w/},
		'styleActiveLine': true,
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
} //END FUNCTION


// END
