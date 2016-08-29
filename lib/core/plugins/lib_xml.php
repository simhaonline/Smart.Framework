<?php
// [LIB - SmartFramework / XML Parser and Composer]
// (c) 2006-2016 unix-world.org - all rights reserved
// v.2.3.5.3 r.2016.08.23 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
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
 * Class: SmartXmlParser - Create a PHP Array from XML.
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
 * @version     v.160827
 * @package     DATA:XML
 *
 */
final class SmartXmlParser {

	// ->

//===============================
private $encoding = 'ISO-8859-1';
//===============================


//===============================
// INIT
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
// PUBLIC
public function transform($xml_str, $log_parse_err_warns=false) {
	//--
	$xml_str = (string) trim((string)$xml_str);
	if((string)$xml_str == '') {
		return array();
	} //end if
	//--
	@libxml_use_internal_errors(true);
	@libxml_clear_errors();
	//--
	$arr = $this->SimpleXML2Array(
		@simplexml_load_string(
			$this->FixXmlRoot((string)$xml_str),
			'SimpleXMLElement',
			LIBXML_ERR_WARNING | LIBXML_NONET | LIBXML_PARSEHUGE | LIBXML_BIGLINES | LIBXML_NOCDATA // {{{SYNC-LIBXML-OPTIONS}}} ; Fix: LIBXML_NOCDATA converts all CDATA to String
		)
	);
	//-- log errors if any
	if(((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') OR ($log_parse_err_warns === true) OR (Smart::array_size($arr) <= 0)) {
		$errors = (array) @libxml_get_errors();
		if(Smart::array_size($errors) > 0) {
			foreach($errors as $z => $error) {
				if(is_object($error)) {
					Smart::log_notice('SmartXmlParser NOTICE: ('.$the_ercode.'): '.'Level: '.$error->level.' / Line: '.$error->line.' / Column: '.$error->column.' / Code: '.$error->code.' / Message: '.$error->message."\n".'Encoding: '.$this->encoding."\n");
				} //end if
			} //end foreach
			if((string)SMART_FRAMEWORK_DEBUG_MODE == 'yes') {
				Smart::log_notice('SmartXmlParser / Debug XML-String:'."\n".$xml_str."\n".'#END');
			} //end if
		} //end if
	} //end if
	//--
	@libxml_clear_errors();
	@libxml_use_internal_errors(false);
	//--
	if(Smart::array_size($arr) <= 0) {
		$arr = array('xml2array_error' => 'SmartXmlParser / Parsing ERROR');
	} //end if
	//--
	return (array) $arr;
	//--
} //END FUNCTION
//===============================

# PRIVATES


//=============================== fix parser bugs by adding the xml markup tag and a valid xml root
private function FixXmlRoot($xml_str) {
	//--
	$xml_str = (string) trim((string)preg_replace('#<\?xml (.*?)>#si', '', (string)$xml_str)); // remove the xml markup tag
	//$xml_str = str_replace(['<'.'?', '?'.'>'], ['<!-- ', ' -->'], $xml_str); // comment out any markup tags
	//--
	if(!SmartValidator::validate_html_or_xml_code($xml_str)) { // fix parser bug if empty data passed
		//--
		Smart::log_warning('SmartXmlParser / GetXMLTree: Invalid XML Detected (555)'."\n".'Encoding: '.$this->encoding.' // Xml-String:'."\n".$xml_str."\n".'#END');
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
	//--
	$array = (array) $sxml;
	$sxml = array();
	//-- recursive Parser
	foreach($array as $key => $value) {
		if(is_object($value)) {
			if(strpos(get_class($value), 'SimpleXML') !== false) {
				$tmp_val = $this->SimpleXML2Array($value);
				if(is_array($tmp_val)) {
					if(Smart::array_size($tmp_val) <= 0) {
						$array[(string)$key] = ''; // FIX: avoid return empty XML key as array() in SimpleXML (empty XML key should be returned as empty string)
					} else {
						$array[(string)$key] = (array) $tmp_val;
					} //end if
				} else {
					$array[(string)$key] = (string) $tmp_val;
				} //end if else
			} //end if
		} //end if
	} //end foreach
	//--
	return (array) $array;
	//--
} //END FUNCTION
//===============================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartXmlComposer - Create XML from a PHP Array.
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
 * @version     v.160827
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

# PRIVATES

//===============================
private function create_from_array($y_array) {

	//--
	if(!is_array($y_array)) {
		Smart::log_warning('SmartXmlComposer / create_from_array expects an Array as parameter ...');
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