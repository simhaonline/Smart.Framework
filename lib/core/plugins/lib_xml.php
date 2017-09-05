<?php
// [LIB - SmartFramework / XML Parser and Composer]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - XMLToArray / ArrayToXML
// DEPENDS:
//	* Smart::
// DEPENDS-EXT: PHP XML Extension
//======================================================


//--
if(!function_exists('simplexml_load_string')) {
	die('ERROR: The PHP SimpleXML Parser Extension is required for the SmartFramework XML Library');
} //end if
//--


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartXmlParser - Create a PHP Array from simple XML structures.
 * XML tag attributes are supported but not parsed.
 *
 * <code>
 *   //-- Sample usage:
 *   $arr = (new SmartXmlParser())->transform('<xml><data>1</data></xml>'); // [OK]
 *   print_r($arr);
 *   //--
 * </code>
 *
 * @usage       dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints       The XML Parser may handle UTF-8 (default) and ISO-8859-1 encodings
 *
 * @access      PUBLIC
 * @depends     extensions: PHP XML ; classes: Smart
 * @version     v.170604
 * @package     DATA:XML
 *
 */
final class SmartXmlParser {

	// ->

//===============================
private $encoding = 'ISO-8859-1';
private $mode = 'simple'; // simple | extended
//===============================


//===============================
public function __construct($mode='simple', $encoding='') {
	//--
	if((string)$encoding == '') {
		if(defined('SMART_FRAMEWORK_CHARSET')) {
			if((string)SMART_FRAMEWORK_CHARSET != '') {
				$this->encoding = (string) SMART_FRAMEWORK_CHARSET;
			} //end if
		} //end if
	} else {
		$this->encoding = (string) $encoding;
	} //end if
	//--
	if((string)$mode === 'extended') {
		$this->mode = 'extended';
	} else { // simple
		$this->mode = 'simple';
	} //end if else
	//--
} //END FUNCTION
//===============================


//===============================
public function transform($xml_str, $log_parse_err_warns=false) {
	//--
	$xml_str = (string) trim((string)$xml_str);
	if((string)$xml_str == '') {
		return array();
	} //end if
	//--
	@libxml_use_internal_errors(true);
	@libxml_clear_errors();
	//-- FIX: json encode / decode forces to sanitize and convert any remaining simplexml objects into arrays !
	$arr = (array) Smart::json_decode(
		(string)Smart::json_encode(
			(array) $this->SimpleXML2Array(
				@simplexml_load_string( // object not array !!
					$this->FixXmlRoot((string)$xml_str),
					'SimpleXMLElement', // this element class is referenced and check in SimpleXML2Array
					LIBXML_ERR_WARNING | LIBXML_NONET | LIBXML_PARSEHUGE | LIBXML_BIGLINES | LIBXML_NOCDATA // {{{SYNC-LIBXML-OPTIONS}}} ; Fix: LIBXML_NOCDATA converts all CDATA to String
				)
			),
			false, // no pretty print
			true, // unescaped unicode
			false // unescaped slashes
		),
		true // return array
	);
	//-- log errors if any
	if(((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') OR ($log_parse_err_warns === true)) { // log errors if set
		$errors = (array) @libxml_get_errors();
		if(Smart::array_size($errors) > 0) {
			$notice_log = '';
			foreach($errors as $z => $error) {
				if(is_object($error)) {
					$notice_log .= 'PARSE-ERROR: ['.$the_ercode.'] / Level: '.$error->level.' / Line: '.$error->line.' / Column: '.$error->column.' / Code: '.$error->code.' / Message: '.$error->message."\n";
				} //end if
			} //end foreach
			if((string)$notice_log != '') {
				Smart::log_notice(__CLASS__.' # NOTICE [SimpleXML / Encoding: '.$this->encoding.']:'."\n".$notice_log."\n".'#END'."\n");
			} //end if
			if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
				Smart::log_notice(__CLASS__.' # Debug XML-String:'."\n".$xml_str."\n".'#END');
			} //end if
		} //end if
	} //end if
	//--
	@libxml_clear_errors();
	@libxml_use_internal_errors(false);
	//--
	if(Smart::array_size($arr) <= 0) {
		$arr = array('XML@PARSER:ERROR' => __CLASS__.' # No XML Data or Invalid Data'); // in case of error, return this
	} //end if
	//--
	return (array) $arr;
	//--
} //END FUNCTION
//===============================


##### PRIVATES


//=============================== fix parser bugs by adding the xml markup tag and a valid xml root
private function FixXmlRoot($xml_str) {
	//--
	$xml_str = (string) trim((string)preg_replace('#<\?xml (.*?)>#si', '', (string)$xml_str)); // remove the xml markup tag
	//$xml_str = str_replace(['<'.'?', '?'.'>'], ['<!-- ', ' -->'], $xml_str); // comment out any markup tags
	//--
	if(!SmartValidator::validate_html_or_xml_code($xml_str)) { // fix parser bug if empty data passed
		//--
		Smart::log_notice(__CLASS__.' # GetXMLTree: Invalid XML Detected (555)'."\n".'Encoding: '.$this->encoding.' // Xml-String:'."\n".$xml_str."\n".'#END');
		$xml_str = '<'.'?'.'xml version="1.0" encoding="'.$this->encoding.'"'.'?'.'>'."\n".'<smart_framework_xml_data_parser_fix_tag>'."\n".'</smart_framework_xml_data_parser_fix_tag>';
		//--
	} else {
		//--
		$xml_str = '<'.'?'.'xml version="1.0" encoding="'.$this->encoding.'"'.'?'.'>'."\n".'<smart_framework_xml_data_parser_fix_tag>'."\n".trim($xml_str)."\n".'</smart_framework_xml_data_parser_fix_tag>';
		//--
	} //end if
	//--
	return (string) $xml_str;
	//--
} //END FUNCTION
//===============================


//===============================
private function SimpleXML2Array($sxml) {
	//--
	if(!is_object($sxml)) {
		return array();
	} //end if
	if((string)get_class($sxml) !== 'SimpleXMLElement') {
		return array();
	} //end if
	//--
	$arr = array();
	//--
	foreach($sxml->children() as $r) {
		//--
		$t = (string) $r->getName();
		//--
		if($this->mode === 'extended') {
			//--
			$tmp_atts = (array) $r->attributes();
			$arr[$t.'|@attributes'][] = (array) $tmp_atts['@attributes'];
			$tmp_atts = null;
			if($r->count() <= 0) {
				$arr[$t][] = (string) $r; // array ; force add as toString
			} else {
				$arr[$t][] = (array) $this->SimpleXML2Array($r); // array ; force add as array
			} //end if else
			//--
		} else { // simple (no attributes
			//--
			if($r->count() <= 0) { // no childs, with Fix: empty arrays will be empty strings
				//--
				if(array_key_exists($t, $arr)) {
					$arr[$t] = (array) $this->addToArr($arr[$t], (string)$r); // array ; force add as toString
				} else {
					$arr[$t] = (string) $r; // string ; force add as toString
				} //end if else
				//--
			} else { // have childs
				//--
				$arr[$t] = (array) $this->addArrToArr((array)$arr[$t], (array)$this->SimpleXML2Array($r)); // array ; force add as array
				//--
			} //end if else
			//--
		} //end if else
		//--
	} //end foreach
	//--
	return (array) $arr; // return array
	//--
} //END FUNCTION
//===============================


//===============================
private function addToArr($arr, $val) {
	//--
	$tmp_arr = $arr; // mixed
	$arr = array();
	//--
	if(is_array($tmp_arr)) {
		foreach($tmp_arr as $k => $v) {
			$arr[] = $v; // force non-associative array, use no key $k
		} //end foreach
	} else {
		$arr[] = (string) $tmp_arr;
	} //end if else
	//--
	$arr[] = (string) $val;
	$tmp_arr = null;
	//--
	return (array) $arr; // return array
	//--
} //END FUNCTION
//===============================


//===============================
private function addArrToArr($arr, $val) {
	//--
	if(Smart::array_size($arr) <= 0) {
		//--
		$arr = (array) $val;
		//--
	} elseif(Smart::array_size($arr) == 1) {
		//--
		$tmp_arr = (array) $arr;
		//--
		$arr = array();
		$arr[] = (array) $tmp_arr;
		$arr[] = (array) $val;
		//--
		$tmp_arr = array();
		//--
	} else {
		//--
		$arr[] = (array) $val;
		//--
	} //end if else
	//--
	return (array) $arr; // return array
	//--
} //END FUNCTION
//===============================


} //END CLASS


/*** Test Extended XML String
<ab></ab>
<cd stt="2"></cd>
<ef>x</ef>
<gh att="3">y</gh>
<meal>
	<test></test>
	<type active="yes">Lunch</type>
	<time>12:30</time>
	<menu>
	 <name></name>
	 <xname att="7"></xname>
	  <entree>salad</entree>
	  <xentree att="t">cabbage</xentree>
	  <maincourse>
	  </maincourse>
	  <maincourse att="xxl">
	  </maincourse>
	  <maincourse>
		  <part></part>
	  </maincourse>
	  <maincourse>
		  <part att="one"></part>
	  </maincourse>
	  <maincourse>
		  <part>blu</part>
	  </maincourse>
	  <maincourse>
		  <part>ships</part>
		  <part>steak</part>
	  </maincourse>
	  <maincourse att="z">
		  <part att="f">fisch</part>
		  <part att="d">rice</part>
	  </maincourse>
	  <maincourse>
		  <part>wine</part>
		  <part>cheese</part>
		  <part>eggs</part>
	  </maincourse>
	</menu>
</meal>
***/


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartXmlComposer - Create simple XML structure from a PHP Array.
 * XML tag attributes are not supported.
 *
 * <code>
 *   //-- Sample use:
 *   $array = array(
 *   	'xml' => array(
 *   		'id' => '15',
 *   		'name' => 'Test',
 *   		'data' => array(
 *   			'key1' => '12345',
 *   			'key2' => '67890',
 *   			'key3' => 'ABCDEF'
 *   		),
 *   		'date' => '2016-02-05 09:30:05'
 *   	)
 *   );
 *   $xml = (new SmartXmlComposer())->transform($array);
 *   echo $xml;
 *   //--
 * </code>
 *
 * @usage       dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints       -
 *
 * @access      PUBLIC
 * @depends     classes: Smart
 * @version     v.170604
 * @package     DATA:XML
 *
 */
final class SmartXmlComposer {

	// ->

//===============================
private $encoding = 'ISO-8859-1';
//===============================


//===============================
public function __construct($encoding='') {
	//--
	if((string)$encoding == '') {
		if(defined('SMART_FRAMEWORK_CHARSET')) {
			if((string)SMART_FRAMEWORK_CHARSET != '') {
				$this->encoding = (string) SMART_FRAMEWORK_CHARSET;
			} //end if
		} //end if
	} else {
		$this->encoding = (string) $encoding;
	} //end if
	//--
} //END FUNCTION
//===============================


//===============================
public function transform($y_array) {
	//--
	return '<'.'?xml version="1.0" encoding="'.Smart::escape_html($this->encoding).'"?'.'>'."\n".$this->create_from_array($y_array);
	//--
} //END FUNCTION
//===============================


##### PRIVATES


//===============================
private function create_from_array($y_array) {

	//--
	if(!is_array($y_array)) {
		Smart::log_warning(__CLASS__.' # create_from_array expects an Array as parameter ...');
		return '<error>XML Writer requires an Array as parameter</error>';
	} //end if
	//--

	//--
	$out = '';
	//--
	$arrtype = Smart::array_type_test($y_array); // 0: not an array ; 1: non-associative ; 2: associative
	//--
	foreach($y_array as $key => $val) {
		//--
		if($arrtype === 2) { // fix keys for associative array
			if((is_numeric($key)) OR ((string)$key == '')) {
				$key = (string) '_'.$key; // numeric or empty keys are not allowed: _#
			} //end if
		} //end if
		//--
		if(is_array($val)) {
			if(is_numeric($key)) { // this can happen only if non-associative array as for associative arrays the numeric key is fixed above as _#
				$out .= $this->create_from_array($val);
			} else {
				$out .= '<'.Smart::escape_html($key).'>'."\n".$this->create_from_array($val).'</'.Smart::escape_html($key).'>'."\n";
			} //end if else
		} elseif((string)$val != '') {
			$out .= '<'.Smart::escape_html($key).'>'.Smart::escape_html($val).'</'.Smart::escape_html($key).'>'."\n";
		} else {
			$out .= '<'.Smart::escape_html($key).' />'."\n";
		} //end if else
		//--
	} //end foreach
	//--

	//--
	return (string) $out;
	//--

} //END FUNCTION
//===============================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>