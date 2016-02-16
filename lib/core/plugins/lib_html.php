<?php
// [LIB - SmartFramework / HTML Parser]
// (c) 2006-2016 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.2')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - HTML 5 Parser
// DEPENDS:
//	* Smart::
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartHtmlParser - provides a HTML Parser that will convert HTML to a PHP array.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.160213
 * @package 	DATA:HTML
 *
 */
final class SmartHtmlParser {

	// ->

//=========================================================================
private $html = '';
private $elements = array();
private $el_parsed = false;
private $comments = array();
private $cm_parsed = false;
private $is_std = false;
private $is_clean = false;
//=========================================================================


//=========================================================================
public function __construct($y_html='') {
	//--
	$this->html = (string) $y_html;
	//--
} //END FUNCTION
//=========================================================================


//=========================================================================
public function get_comments() {
	//--
	$this->standardize_html();
	$this->parse_comments();
	//--
	return (array) $this->comments;
	//--
} //END FUNCTION
//=========================================================================


//=========================================================================
public function get_all_tags() {
	//--
	$this->standardize_html();
	$this->parse_elements();
	//--
	return (array) $this->elements;
	//--
} //END FUNCTION
//=========================================================================


//=========================================================================
// use no SmartUnicode, tag names are always non-unicode
public function get_tags($tag) {
	//--
	$this->standardize_html();
	$this->parse_elements();
	//--
	$tag = strtolower('<'.$tag);
	$len = strlen((string)$tag) + 1; // will add ' ' or \t or or / '>' at the end for testing
	$attrib_arr = array();
	if(is_array($this->elements)) {
		while(list($key, $code) = @each($this->elements)) {
			if((strpos($code, '<') !== false) OR (strpos($code, '>') !== false)) { // if valid tag
				$code = trim(str_replace(array("\t", "\n", "\r"), array(' ', ' ', ' '), (string)$code)); // make tabs and new lines as simple space
				$tmp_test = strtolower(substr((string)$code, 0, $len));
				if(((string)$tmp_test == (string)$tag.' ') OR ((string)$tmp_test == (string)$tag.'/') OR ((string)$tmp_test == (string)$tag.'>')) {
					$attrib_arr[] = (array) $this->get_attributes($code);
				} //end if
			} //end if
		} //end while
	} //end if
	//--
	return $attrib_arr;
	//--
} //END FUNCTION
//=========================================================================


//=========================================================================
public function get_std_html() {

	//--
	$this->standardize_html();
	//--

	//--
	return (string) $this->html;
	//--

} //END FUNCTION
//=========================================================================


//=========================================================================
public function get_clean_html($y_comments=true) {

	//--
	$this->clean_html($y_comments);
	//--

	//--
	//return (string) (print_r($this->elements,1));
	return (string) $this->html;
	//--

} //END FUNCTION
//=========================================================================


## PRIVATES


//=========================================================================
private function standardize_html() {

	//--
	if($this->is_std != false) {
		return; // avoid to re-parse
	} //end if
	//--
	$this->is_std = true;
	//--

	//-- remove all non utf8 characters
	$this->html = (string) preg_replace((string)SmartValidator::regex_stringvalidation_expression('lower-unsafe-characters'), '', (string)$this->html);
	//-- standardize new lines, tabs and line ends
	$this->html = (string) str_replace(array("\0", "\r\n", "\r", ' />', '/>'), array('', "\n", "\n", '>', '>'), (string)$this->html);
	//-- protect against server-side tags
	$this->html = (string) str_replace(array('<'.'?', '?'.'>', '<'.'%', '%'.'>'), array('<tag-question:start', '<tag-question:end', '<tag-percent:start', '<tag-percent:end'), (string)$this->html);
	//--

	//-- standardize spaces and new lines
	$arr_spaces_cleanup = array(
		//shorten multiple tabs and spaces
		'/([\t ])+/si' => ' ',
		//remove leading and trailing spaces and tabs
		'/^([\t ])+/mi' => '',
		'/([\t ])+$/mi' => '',
		//remove empty lines (sequence of line-end and white-space characters)
		'/[\r\n]+([\t ]?[\r\n]+)+/si' => "\n"
	);
	//--
	$this->html = (string) preg_replace((array)array_keys((array)$arr_spaces_cleanup), (array)array_values((array)$arr_spaces_cleanup), (string)$this->html);
	//--

} //END FUNCTION
//=========================================================================


//=========================================================================
private function clean_html($y_comments) {

	//-- v.160204
	// INVALID TAGS :: HTML only :: protect against client-side scripting and html denied tags
	// NOTE: the <? tag(s) will be detected and if present, will make HIGHLIGHTCODE to prevent code injection
	// NOTE: the <% tag will be commented :-)
	// insensitive
	//--

	//--
	if($this->is_clean != false) {
		return; // avoid to re-parse
	} //end if
	//--
	$this->is_clean = true;
	//--

	//--
	$this->standardize_html();
	//--

	//--
	$arr_tags_2x_list_bad = array( // remove them and their content
		'head',
		'style',
		'script',
		'noscript',
		'frameset',
		'frame',
		'iframe',
		'canvas',
		'audio',
		'video',
		'applet',
		'param',
		'object',
		'form',
		'xml',
		'xmp',
		'o:p'
	);
	$arr_tags_2x_repl_bad = array(
		'#<\!\-\-(.*?)\-\->#si', // comments
		'#<\! (.*?)>#si' // invalid comments
	);
	$arr_tags_2x_repl_good = array(
		'<!-- SmartFramework HTML Cleaner // Comment Removed ! -->',
		'<!-- SmartFramework HTML Cleaner // Invalid Comment Removed ! -->'
	);
	$arr_tags_2x_repl_egood = array(
		''
	);
	for($i=0; $i<count($arr_tags_2x_list_bad); $i++) {
		$arr_tags_2x_repl_bad[] = '#<'.preg_quote((string)$arr_tags_2x_list_bad[$i]).'[^>]*?>.*?</'.preg_quote((string)$arr_tags_2x_list_bad[$i]).'[^>]*?>#si';
		$arr_tags_2x_repl_good[] = '<!-- SmartFramework HTML Cleaner // Removed (2): ['.Smart::escape_html((string)$arr_tags_2x_list_bad[$i]).'] ! -->';
		$arr_tags_2x_repl_egood[] = '';
	} //end if
	//--

	//--
	$arr_tags_1x_list_bad = (array) array_merge((array)$arr_tags_2x_list_bad, array( // remove them and their content
		'!doctype',
		'html',
		'body',
		'base',
		'meta',
		'link',
		'track',
		'source',
		'plaintext',
		'marquee'
	));
	$arr_tags_1x_repl_bad = array(
	);
	$arr_tags_1x_repl_good = array(
	);
	$arr_tags_1x_repl_egood = array(
	);
	for($i=0; $i<count($arr_tags_1x_list_bad); $i++) {
		$arr_tags_1x_repl_bad[] = '#<'.preg_quote((string)$arr_tags_1x_list_bad[$i]).'[^>]*?>#si';
		$arr_tags_1x_repl_bad[] = '#</'.preg_quote((string)$arr_tags_1x_list_bad[$i]).'[^>]*?>#si';
		$arr_tags_1x_repl_good[] = '<!-- SmartFramework HTML Cleaner // Removed (1): ['.Smart::escape_html((string)$arr_tags_1x_list_bad[$i]).'] ! -->';
		$arr_tags_1x_repl_good[] = '<!-- SmartFramework HTML Cleaner // Removed (1): [/'.Smart::escape_html((string)$arr_tags_1x_list_bad[$i]).'] ! -->';
		$arr_tags_1x_repl_egood[] = '';
		$arr_tags_1x_repl_egood[] = '';
	} //end if
	//--

	//--
	if($y_comments === false) {
		$arr_tags_2x_repl_good = $arr_tags_2x_repl_egood;
		$arr_tags_1x_repl_good = $arr_tags_1x_repl_egood;
	} //end if
	//--
	$arr_all_repl_bad  = (array) array_merge((array)$arr_tags_2x_repl_bad,  (array)$arr_tags_1x_repl_bad);
	$arr_all_repl_good = (array) array_merge((array)$arr_tags_2x_repl_good, (array)$arr_tags_1x_repl_good);
	//--
	//print_r($arr_tags_2x_repl_bad);
	//print_r($arr_tags_2x_repl_good);
	//print_r($arr_tags_1x_repl_bad);
	//print_r($arr_tags_1x_repl_good);
	//print_r($arr_all_repl_bad);
	//print_r($arr_all_repl_good);
	//die('');
	//--

	//--
	$this->html = (string) preg_replace((array)$arr_all_repl_bad, (array)$arr_all_repl_good, (string)$this->html);
	//--

	//--
	$this->parse_elements();
	//--

	//--
	for($i=0; $i<Smart::array_size($this->elements); $i++) {
		//--
		$code = (string) $this->elements[$i];
		if((substr($code, 0, 4) != '<!--') AND ((strpos($code, '<') !== false) OR (strpos($code, '>') !== false))) { // if valid tag and not a comment
			//--
			$tag_have_endline = false;
			if(substr($code, -1, 1) === "\n") {
				$tag_have_endline = true;
			} //end if
			//--
			$code = trim(str_replace(array("\t", "\n", "\r"), array(' ', ' ', ' '), (string)$code)); // make tabs and new lines as simple space
			$tmp_parse_attr = (array) $this->get_attributes($code);
			//--
			if((strpos($code, ' ') !== false) AND (Smart::array_size($tmp_parse_attr) > 0)) { // tag have attributes
				//--
				$tmp_arr = explode(' ', $code); // get tag parts
				$this->elements[$i] = strtolower((string)$tmp_arr[0]); // recompose the tags
				foreach($tmp_parse_attr as $key => $val) {
					$tmp_is_valid_attr = true;
					if(!preg_match('/^[a-z0-9\-\:]+$/si', (string)$key)) { // {{{SYNC-TAGS-ATTRIBUTES-CHARS}}}
						$tmp_is_valid_attr = false; // remove invalid attributes
					} elseif(substr((string)trim((string)$key), 0, 2) == 'on') {
						$tmp_is_valid_attr = false; // remove attributes starting with 'on' (all JS Events)
					} elseif(substr((string)trim((string)$key), 0, 10) == 'formaction') {
						$tmp_is_valid_attr = false; // remove attributes starting with 'formaction'
					} elseif(substr((string)trim((string)$val), 0, 2) == '&{') {
						$tmp_is_valid_attr = false; // remove attributes of which value are old Netscape JS ; Ex: border="&{getBorderWidth( )};"
					} elseif(substr((string)trim((string)$val), 0, 11) == 'javascript:') {
						$tmp_is_valid_attr = false; // remove attributes that contain javascript:
					} elseif((stripos((string)trim((string)$val), 'java') !== false) AND (stripos((string)trim((string)$val), 'script') !== false) AND (strpos((string)trim((string)$val), ':') !== false)) {
						$tmp_is_valid_attr = false; // remove attributes that contain java + script + :
					} //end for
					if($tmp_is_valid_attr) {
						$this->elements[$i] .= ' '.strtolower($key).'='.'"'.str_replace(array('"', '<', '>'), array('&quot;', '&lt;', '&gt;'), (string)$val).'"';
					} //end if
				} //end foreach
				$this->elements[$i] .= '>';
				if($tag_have_endline) {
					$this->elements[$i] .= "\n";
				} //end if
				$tmp_arr = array();
				//--
			} elseif(preg_match('/^[<a-z0-9\-\:\/ >]+$/si', (string)$code)) { // simple tags {{{SYNC-TAGS-ATTRIBUTES-CHARS}}}
				//--
				$this->elements[$i] = strtolower((string)$code);
				if($tag_have_endline) {
					$this->elements[$i] .= "\n";
				} //end if
				//--
			} else {
				//--
				$this->elements[$i] = ''; // invalid tags, clear
				//--
			} //end if
		} //end if
		//--
	} //end for
	//--

	//--
	$this->html = "\n".'<!-- Smart.HTML/Cleaner [Start] -->'."\n".(string) implode('', (array)$this->elements)."\n".'<!-- Smart.HTML/Cleaner [END] -->'."\n";
	//--

} //END FUNCTION
//=========================================================================


//=========================================================================
private function parse_comments() {
	//--
	if($this->cm_parsed != false) {
		return; // avoid to re-parse
	} //end if
	$this->cm_parsed = true;
	//--
	$this->comments = array(); // init
	//--
	if((string)$this->html == '') {
		return;
	} //end if
	//--
	$rcomments = array();
	if(preg_match_all('#<\!--(.*?)-->#si', (string)$this->html, $rcomments)) {
		if(is_array($rcomments)) {
			$this->comments['comment-keys'] = (array) $rcomments[1];
			$this->comments['comment-tags'] = (array) $rcomments[0];
		} //end if
	} //end if
	//--
} //END FUNCTION
//=========================================================================


//=========================================================================
// use no SmartUnicode, tag names are always non-unicode
private function parse_elements() {
	//--
	if($this->el_parsed != false) {
		return; // avoid to re-parse
	} //end if
	$this->el_parsed = true;
	//--
	$this->elements = array(); // init
	//--
	if((string)$this->html == '') {
		return;
	} //end if
	//--
	$ignorechar = false;
	$intag = false;
	$tagdepth = 0;
	$line = '';
	$text = '';
	$tag = '';
	//--
	$raw = @explode("\n", (string)$this->html);
	//--
	while(list($key, $line) = @each($raw)) {
		//--
		$line = trim($line);
		if((string)$line == '') {
			continue;
		} //end if
		$line .= "\n"; // fix: if tag is on multiple lines
		//--
		for($charsindex=0; $charsindex<strlen($line); $charsindex++) { // Fix: must be strlen() not SmartUnicode as it will break the parsing (Fix: 160203)
			if($ignorechar == true) {
				$ignorechar = false;
			} //end if
			if(((string)$line[$charsindex] == '<') AND (!$intag)) {
				if((string)$text != '') {
					// text found
					$this->elements[] = $text;
					$text = '';
				} //end if
				$intag = true;
			} else {
				if(((string)$line[$charsindex] == '>') AND ($intag)) {
					$tag .= '>';
					// tag found
					$this->elements[] = $tag;
					$ignorechar = true;
					$intag = false;
					$tag = '';
				} //end if
			} //end if else
			if((!$ignorechar) AND (!$intag)) {
				$text .= $line[$charsindex];
			} else {
				if((!$ignorechar) AND ($intag)) {
					$tag .= $line[$charsindex];
				} //end if
			} //end if else
		} //end for
	} //end while
	//--
} //END FUNCTION
//=========================================================================


//=========================================================================
private function get_attributes($html) {
	//-- {{{SYNC-TAGS-ATTRIBUTES-CHARS}}}
	$attr_with_dbl_quote = '(([a-z0-9\-\:]+)\s*=\s*"([^"]*)")*';
	$attr_with_quote = '(([a-z0-9\-\:]+)\s*=\s*\'([^\']*)\')*';
	$attr_without_quote = '(([a-z0-9\-\:]+)\s*=([^\s>\/]*))*';
	$attr = array();
	preg_match_all('/'.$attr_with_dbl_quote.'|'.$attr_with_quote.'|'.$attr_without_quote.'/si', (string)$html, $attr);
	//--
	$res = array();
	//--
	if(is_array($attr)) {
		foreach($attr as $count => $attr_arrx) {
			if(is_array($attr_arrx)) {
				foreach($attr_arrx as $i=>$a) {
					if(((string)$a != '') AND ($count == 2)) {
						$res[$a] = (string) $attr[3][$i];
					} //end if
					if(((string)$a != '') AND ($count == 5)) {
						$res[$a] = (string) $attr[6][$i];
					} //end if
					if(((string)$a != '') AND ($count == 8)) {
						$res[$a] = (string) $attr[9][$i];
					} //end if
				} //end foreach
			} //end if
		} //end foreach
	} //end if
	//--
	return (array) $res;
	//--
} //END FUNCTION
//=========================================================================


} //END CLASS


//=========================================================================
/*******************
// SAMPLE USAGE:
$html = <<<HTML_CODE
<a href="#anchor"><img src="some/image.jpg" width="32" height="64"></a>
<a href="#anchor"><img src="some/image2.jpg" width="33" height="65"></a>
HTML_CODE;
$obj = new SmartHtmlParser($html);
print_r($obj->get_tags("img"));
********************/
//=========================================================================


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>