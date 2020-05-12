
// Smart.Framework Syntax Highlight Helper using highlightjs
// v.20200511

/* minimal complete setup:
<link rel="stylesheet" href="lib/js/jshighlight/css/github.css">
<script type="text/javascript" src="lib/js/jshighlight/highlight.js"></script>
<script type="text/javascript" src="lib/js/jshighlight/syntax/plaintext.js"></script>
<script type="text/javascript" src="lib/js/jshighlight/syntax.pak.js"></script>
*/

if(typeof SmartJS_Custom_Syntax_Highlight != 'function') {
	var SmartJS_Custom_Syntax_Highlight = function(elem) {
		setTimeout(function(){
			jQuery(elem + ' pre code').each(function(i, block) {
				var theObj = jQuery(this);
				var theClass = theObj.attr('class');
				if((typeof theClass != 'undefined') && (theClass != 'undefined') && (theClass != '')) {
					theObj.attr('title', 'Syntax: ' + String(theClass));
					try {
						hljs.highlightBlock(block);
					} catch(err) {
						console.error('HighlightJs Failed to instantiate on selector #' + i + ' @ for class: ' + String(theClass));
					}
				} //end if
				theObj = null;
				theClass = null;
			});
		}, 50);
	}
} // END FUNCTION

// #END
