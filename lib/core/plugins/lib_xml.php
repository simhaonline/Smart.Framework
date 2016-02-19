<?php
// [LIB - SmartFramework / XML Parser and Composer]
// (c) 2006-2016 unix-world.org - all rights reserved

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
if(!function_exists('xml_parser_create')) {
	die('ERROR: The PHP XML Parser Extension is required for the SmartFramework XML Library');
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
 * @version     v.160213
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
public function transform($xml_str) {
	//--
	$xml_str = (string) trim((string)preg_replace('#<\?xml (.*?)>#si', '', (string)$xml_str)); // remove the xml markup tag
	//--
	return (array) $this->GetXMLTree((string)$xml_str);
	//--
} //END FUNCTION
//===============================

# PRIVATES

//===============================
// PRIVATE
private function GetXMLTree($xmldata) {
	//--
	$parser = xml_parser_create($this->encoding);
	//--
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	//--
	$handle = 0;
	//--
	if(!Smart::test_if_xml($xmldata)) { // fix parser bug if empty data passed
		//--
		Smart::log_warning('SmartXmlParser / GetXMLTree: Invalid XML Detected (555)'."\n".'Encoding: '.$this->encoding.' // Xml-String:'."\n".$xmldata."\n".'#END');
		return array('xml2array_error' => 'ERROR: (555) Invalid XML !');
		//--
	} else {
		//-- fix parser bug: $xmldata = '<xml>'."\n".'</xml>';
		$xmldata = '<'.'?xml version="1.0" encoding="'.$this->encoding.'"?'.'>'."\n".'<xml_data_parser_tag>'."\n".trim($xmldata)."\n".'</xml_data_parser_tag>';
		//--
		$handle = xml_parse_into_struct($parser, $xmldata, $vals, $index);
		//--
		if(!$handle) {
			//--
			$the_err = @xml_error_string(@xml_get_error_code($parser));
			Smart::log_warning('SmartXmlParser / GetXMLTree: XML Parser Error (444): '.$the_err."\n".'Encoding: '.$this->encoding.' // Xml-String:'."\n".$xmldata."\n".'#END');
			return array('xml2array_error' => 'ERROR: (444) XML Parsing Error: '.$the_err);
			//--
		} //end if
		//--
	} //end if
	//--
	xml_parser_free($parser);
	//--
	$result = array();
	//--
	if(is_array($vals)) {
		//--
		$i = 0;
		//--
		if(isset($vals[$i]['attributes'])) {
			foreach(array_keys($vals[$i]['attributes']) as $attkey) {
				$attributes[$attkey] = $vals[$i]['attributes'][$attkey];
			} //end foreach
		} //end if
		//--
		$result[$vals[$i]['tag']] = array_merge((array)$attributes, (array)$this->GetChildren($vals, $i, 'open'));
		//--
	} //end if
	//--
	return (array) $result['xml_data_parser_tag'];
	//--
} //END FUNCTION
//===============================


//===============================
// PRIVATE
private function GetChildren($vals, &$i, $type) {

	//--
	if((string)$type == 'complete') {
		//--
		if(isset($vals[$i]['value'])) {
			//--
			return($vals[$i]['value']);
			//--
		} else {
			//--
			return '';
			//--
		} //end if else
		//--
	} //end if
	//--

	//--
	$children = array(); // Contains node data
	//--

	//-- Loop through children
	while((string)$vals[++$i]['type'] != 'close') {
		//--
		$type = $vals[$i]['type'];
		//-- first check if we already have one and need to create an array
		if(isset($children[$vals[$i]['tag']])) {
			//--
			if(is_array($children[$vals[$i]['tag']])) {
				$temp = array_keys($children[$vals[$i]['tag']]);
				// there is one of these things already and it is itself an array
				if(is_string($temp[0])) {
					$a = $children[$vals[$i]['tag']];
					unset($children[$vals[$i]['tag']]);
					$children[$vals[$i]['tag']][0] = $a;
				} //end if
			} else {
				$a = $children[$vals[$i]['tag']];
				unset($children[$vals[$i]['tag']]);
				$children[$vals[$i]['tag']][0] = $a;
			} //end if else
			//--
			$children[$vals[$i]['tag']][] = $this->GetChildren($vals, $i, $type);
			//--
		} else {
			//--
			$children[$vals[$i]['tag']] = $this->GetChildren($vals, $i, $type);
			//--
		} //end if else
		//-- I don't think I need attributes but this is how I would do them:
		if(isset($vals[$i]['attributes'])) {
			//--
			$attributes = array();
			//--
			foreach(array_keys($vals[$i]['attributes']) as $attkey) {
				$attributes [$attkey] = $vals[$i]['attributes'][$attkey];
			} //end foreach
			//-- now check: do we already have an array or a value?
			if(isset($children[$vals[$i]['tag']])) {
				//--
				if((string)$children[$vals[$i]['tag']] == '') {
					//-- case where there is an attribute but no value, a complete with an attribute in other words
					unset($children[$vals[$i]['tag']]);
					$children[$vals[$i]['tag']] = $attributes;
					//--
				} elseif(is_array($children[$vals[$i]['tag']])) {
					//-- case where there is an array of identical items with attributes
					$index = count($children[$vals[$i]['tag']]) - 1;
					//-- probably also have to check here whether the individual item is also an array or not or what... all a bit messy
					if((string)$children[$vals[$i]['tag']][$index] == '') {
						unset($children[$vals[$i]['tag']][$index]);
						$children[$vals[$i]['tag']][$index] = $attributes;
					} //end if
					//--
					$children[$vals[$i]['tag']][$index] = array_merge ((array)$children[$vals[$i]['tag']][$index], (array)$attributes);
					//--
				} else {
					//--
					$value = $children[$vals[$i]['tag']];
					unset($children[$vals[$i]['tag']]);
					$children[$vals[$i]['tag']]['value'] = $value;
					$children[$vals[$i]['tag']] = array_merge((array)$children[$vals[$i]['tag']], (array)$attributes);
					//--
				} //end if else
				//--
			} else {
				//--
				$children[$vals[$i]['tag']] = $attributes;
				//--
			} //end if else
			//--
		} //end if
		//--
	} //end while
	//--

	//--
	return $children;
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
 * @version     v.160213
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
	foreach($y_array as $key => $val) {
		//--
		if((is_numeric($key)) OR ((string)$key == '')) {
			$key = (string) '_'.$key; // numeric or empty keys are not allowed
		} //end if
		//--
		if(is_array($val)) {
			$out .= '<'.Smart::escape_html($key).'>'."\n".$this->create_from_array($val).'</'.Smart::escape_html($key).'>'."\n";
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