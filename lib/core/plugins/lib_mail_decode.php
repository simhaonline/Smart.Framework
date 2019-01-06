<?php
// [LIB - Smart.Framework / Plugins / Mail Mime Decode]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Mail Mime Decoder and Parser
// DEPENDS:
//	* Smart::
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartMailerMimeDecode - provides an eMail MIME decoder.
 * This class is for very advanced use.
 *
 * It just implements the mime decoding to a PHP array by decoding Mime Email Messages.
 * To easy parse mime messages and display them on-the-fly use the class: SmartMailerMimeParser.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20190105
 * @package 	Mailer
 *
 */
final class SmartMailerMimeDecode {

	// ->

//================================================================
	//-- export
	public $arr_heads;
	public $arr_parts;
	public $arr_atts;
	//-- temporary
	private $last_charset;
	private $last_fname;
	private $last_cid;
	private $cycle;
	//-- set
	private $local_charset = 'ISO-8859-1';
	//--
//================================================================


//================================================================
public function __construct($encoding='') {
	//--
	if((string)$encoding == '') {
		if(defined('SMART_FRAMEWORK_CHARSET')) {
			if((string)SMART_FRAMEWORK_CHARSET != '') {
				$this->local_charset = (string) SMART_FRAMEWORK_CHARSET;
			} //end if
		} //end if
	} else {
		$this->local_charset = (string) $encoding;
	} //end if
	//--
	$this->reset();
	//--
} //END FUNCTION
//================================================================


//================================================================
public function get_working_charset() {
	//--
	return (string) $this->local_charset;
	//--
} //END FUNCTION
//================================================================


//================================================================
public function reset() {
	//--
	$this->arr_heads = array();
	$this->arr_parts = array();
	$this->arr_atts = array();
	//--
	$this->last_charset = '';
	$this->last_fname = 'attachment.file';
	$this->last_cid = '';
	$this->cycle = 0;
	//--
} //END FUNCTION
//================================================================


//================================================================
// PUBLIC
public function get_bodies($message, $part_id) {
	//-- decode params
	$params 					= array();
	$params['decode_headers'] 	= true;		// Whether to decode headers
	$params['include_bodies'] 	= true;		// Whether to include the body in the returned object.
	$params['decode_bodies'] 	= true; 	// Whether to decode the bodies of the parts. (Transfer encoding)
	//-- call private decode
	$obj = new SmartMailerMimeExtract((string)$message, $this->local_charset); // [OK]
	$message = ''; // free memory
	$structure = $obj->decode($params);
	//-- get decode arrays
	$this->reset();
	$this->printarray($structure, $part_id);
	//-- free memory
	unset($structure);
	unset($obj);
	unset($params);
	//-- what to return
	return array('texts'=>$this->arr_parts, 'attachments'=>$this->arr_atts);
	//--
} //END FUNCTION
//================================================================


//================================================================
// separe email from name as: 'Name <email@address>'
public function separe_email_from_name($y_address) {
	//--
	if(SmartUnicode::str_contains($y_address, '<')) {
		//--
		$tmp_expl = array();
		$tmp_expl = (array) explode('<', (string)$y_address);
		$tmp_name = trim($tmp_expl[0]);
		$tmp_name = trim(str_replace(array("'", '"', '`'), array('', '', ''), $tmp_name));
		$tmp_expl = (array) explode('>', (string)$tmp_expl[1]);
		$tmp_email = trim($tmp_expl[0]);
		$tmp_expl = array();
		//--
	} else {
		//--
		$tmp_name = '';
		$tmp_email = trim($y_address);
		//--
	} //end if
	//--
	return array($tmp_email, $tmp_name);
	//--
} //END FUNCTION
//================================================================


//================================================================
public function get_header($message) {

	//== [INITS]
	//--
	$export_from_addr = '';
	$export_from_name = '';
	$export_to_addr = '';
	$export_to_name = '';
	$export_cc_addr = '';
	$export_cc_name = '';
	$export_subject = '';
	$export_date = '';
	$export_msguid = '';
	$export_msgid = '';
	$export_inreplyto = '';
	$export_priority = '';
	$export_attachments = '';
	//--
	$headers = array();
	//--
	//==

	//== [ATTACHMENTS]
	//--
	$export_attachments = 0; // attachments not detected
	//--
	if(preg_match('/^content-disposition:(\s)attachment(.*);/mi', (string)$message)) { // insensitive
		$export_attachments = 1; // attachments were detected
	} //end if
	//--
	//==

	//== DECODING
	//--
	$params = array();
	$params['decode_headers'] = true;	// Whether to decode headers
	$params['include_bodies'] = false;	// Whether to include the body in the returned object.
	$params['decode_bodies'] = false; 	// Whether to decode the bodies of the parts. (Transfer encoding)
	//--
	$obj = new SmartMailerMimeExtract((string)$message, $this->local_charset); // [OK]
	unset($message);
	$structure = $obj->decode($params);
	//--
	$this->reset();
	$this->printarray($structure, ''); // this will be free after trying to guess atatchments
	//-- free memory
	unset($structure);
	unset($obj);
	unset($params);
	//-- some process of data
	$headers = (array) $this->arr_heads[0]; // get first header
	//--
	$this->reset();
	//--
	//==

	//== [FROM]
	$from = '';
	//--
	if(is_array($headers['from'])) {
		$from = trim($headers['from'][0]);
	} else {
		$from = trim($headers['from']);
	} //end if else
	//--
	if((string)$from == '') { // if from is not specified we use return path
		if(is_array($headers['return-path'])) {
			$from = trim($headers['return-path'][0]);
		} else {
			$from = trim($headers['return-path']);
		} //end if else
	} //end if
	//--
	$tmp_arr = array();
	$tmp_arr = $this->separe_email_from_name($from);
	//--
	$export_from_addr = trim($tmp_arr[0]);
	$export_from_name = trim($tmp_arr[1]);
	//--
	$tmp_arr = array();
	$from = '';
	//--
	$tmp_arr = array();
	$from = '';
	//--
	//==

	//== [TO]
	$to = '';
	//--
	if(is_array($headers['to'])) {
		$to = trim($headers['to'][0]);
	} else {
		$to = trim($headers['to']);
	} //end if else
	//--
	$tmp_arr = array();
	$tmp_arr = $this->separe_email_from_name($to);
	//--
	$export_to_addr = trim($tmp_arr[0]);
	$export_to_name = trim($tmp_arr[1]);
	//--
	if(SmartUnicode::str_contains($to, '[::@::]')) { // fix for netoffice :: Multi-Message
		$export_to_addr = '[@]';
		$export_to_name = '';
	} elseif(SmartUnicode::str_contains($to, '[::#::]')) { // fix for netoffice :: Fax
		$export_to_addr = '[#]';
		$export_to_name = '';
	} elseif(SmartUnicode::str_contains($to, '[::!::]')) { // Not Sent
		$export_to_addr = '[!]';
		$export_to_name = '';
	} //end if
	//--
	$tmp_arr = array();
	$to = '';
	//--
	//==

	//== [CC]
	$cc = '';
	//--
	$export_cc_addr = array();
	$export_cc_name = array();
	//--
	if(is_array($headers['cc'])) {
		$cc = trim($headers['cc'][0]);
	} else {
		$cc = trim($headers['cc']);
	} //end if else
	//--
	$arr_cc = array();
	//--
	if(SmartUnicode::str_contains($cc, ',')) {
		$arr_cc = (array) explode(',', (string)$cc);
	} else {
		$arr_cc[] = (string) $cc;
	} //end if else
	//--
	for($z=0; $z<Smart::array_size($arr_cc); $z++) {
		//--
		$tmp_arr = array();
		$tmp_arr = $this->separe_email_from_name($arr_cc[$z]);
		//--
		$export_cc_addr[] = trim($tmp_arr[0]);
		$export_cc_name[] = trim($tmp_arr[1]);
		//--
		$tmp_arr = array();
		//--
	} //end for
	//--
	$export_cc_addr = implode(', ', $export_cc_addr);
	$export_cc_name = implode(', ', $export_cc_name);
	//--
	$cc = '';
	//--
	//==

	//== [BCC]
	$bcc = '';
	//--
	if(is_array($headers['bcc'])) {
		$bcc = trim($headers['bcc'][0]);
	} else {
		$bcc = trim($headers['bcc']);
	} //end if else
	//--
	$tmp_arr = array();
	$tmp_arr = $this->separe_email_from_name($bcc);
	//--
	$export_bcc_addr = trim($tmp_arr[0]);
	$export_bcc_name = trim($tmp_arr[1]);
	//--
	$tmp_arr = array();
	//--
	$bcc = '';
	//--
	//==

	//== [SUBJECT]
	$subj = '';
	//--
	if(is_array($headers['subject'])) {
		$subj = trim($headers['subject'][0]);
	} else {
		$subj = trim($headers['subject']);
	} //end if else
	//--
	$export_subject = trim($subj);
	//--
	if((string)$export_subject == '') {
		$export_subject = '[?]';
	} //end if
	//--
	$subj = '';
	//--
	//==

	//== [DATE]
	$date = '';
	//--
	if(is_array($headers['date'])) {
		$date = (string) trim((string)$headers['date'][0]);
	} else {
		$date = (string) trim((string)$headers['date']);
	} //end if else
	//--
	$export_date = (string) trim((string)preg_replace('/[^0-9a-zA-Z,\+\:\-]/', ' ', (string)$date)); // fix: remove invalid characters in date
	//--
	if((string)$export_date == '') {
		$export_date = '(?)';
	} //end if
	//--
	$date = '';
	//--
	//==

	//== [MESSAGE-UID]
	$msguid = '';
	//--
	if(is_array($headers['message-uid'])) {
		$msguid = trim($headers['message-uid'][0]);
	} else {
		$msguid = trim($headers['message-uid']);
	} //end if else
	//--
	$msguid = trim(str_replace(array('<', '>'), array('', ''), (string)$msguid));
	//--
	$export_msguid = trim($msguid);
	//--
	$msguid = '';
	//--
	//==

	//== [MESSAGE-ID]
	$msgid = '';
	//--
	if(is_array($headers['message-id'])) {
		$msgid = trim($headers['message-id'][0]);
	} else {
		$msgid = trim($headers['message-id']);
	} //end if else
	//--
	$msgid = trim(str_replace(array('<', '>'), array('', ''), (string)$msgid));
	//--
	$export_msgid = trim($msgid);
	//--
	$msgid = '';
	//--
	//==

	//== [IN-REPLY-TO]
	$inreplyto = '';
	//--
	if(is_array($headers['in-reply-to'])) {
		$inreplyto = trim($headers['in-reply-to'][0]);
	} else {
		$inreplyto = trim($headers['in-reply-to']);
	} //end if else
	//--
	$inreplyto = trim(str_replace(array('<', '>'), array('', ''), (string)$inreplyto));
	//--
	$export_inreplyto = trim($inreplyto);
	//--
	$inreplyto = '';
	//--
	//==

	//== [PRIORITY] :: ( 1=high, 3=normal, 5=low )
	$priority = '';
	//--
	if(is_array($headers['x-priority'])) {
		$priority = trim($headers['x-priority'][0]);
	} else {
		$priority = trim($headers['x-priority']);
	} //end if else
	//--
	switch(strtolower($priority)) {
		case 'high':
		case '0':
		case '1':
		case '2':
			$export_priority = '1'; //high
			break;
		case 'low':
		case '5':
		case '6':
		case '7':
		case '8':
		case '9':
		case '10':
			$export_priority = '5'; //low
			break;
		case 'normal':
		case 'medium':
		case '3':
		case '4':
		default:
			$export_priority = '3'; //medium (normal)
	} //end switch
	//--
	$priority = '';
	//--
	//==

	//== [CLEANUP]
	//--
	unset($headers);
	//--
	//==

	//== [EXPORT DATA AS ARRAY]
	return array(
		'from_addr' 	=> (string) $export_from_addr,
		'from_name' 	=> (string) $export_from_name,
		'to_addr' 		=> (string) $export_to_addr,
		'to_name' 		=> (string) $export_to_name,
		'cc_addr' 		=> (string) $export_cc_addr,
		'cc_name' 		=> (string) $export_cc_name,
		'bcc_addr'		=> (string) $export_bcc_addr,
		'bcc_name'		=> (string) $export_bcc_name,
		'subject' 		=> (string) $export_subject,
		'date' 			=> (string) $export_date,
		'message-uid' 	=> (string) $export_msguid,
		'message-id' 	=> (string) $export_msgid,
		'in-reply-to' 	=> (string) $export_inreplyto,
		'priority' 		=> (string) $export_priority,
		'attachments' 	=> (string) $export_attachments
	);
	//==

} //END FUNCTION
//================================================================


//================================================================
// PRIVATE
private function printarray($array, $part_id) {

	//--
	$this->cycle += 1;
	//--

	//--
	$vxf_mail_part_type = '';
	$vxf_mail_part_stype = '';
	//--

	//--
	//while(list($key,$value) = @each($array)) {
	foreach($array as $key => $value) { // Fix: the above is deprecated as of PHP 7.2
		//--
		if(is_object($value)) {
			//--
			$this->printarray(get_object_vars($value), $part_id);
			//--
		} elseif(is_array($value)) {
			//-- get params from pre-body-arrays
			if ($key === 'ctype_parameters') {
				//--
				if(trim($value['charset']) != '') {
					//--
					$tmp_charset = SmartUnicode::str_tolower($value['charset']);
					//--
					if(((string)$tmp_charset == '') OR ((string)$tmp_charset == 'us-ascii')) {
						$tmp_charset = 'iso-8859-1'; // correction :: {{{SYNC-CHARSET-FIX}}}
					} //end if
					//--
					$this->last_charset = $tmp_charset;
					//--
				} //end if
				//--
			} elseif($key === 'headers') {
				//--
				$this->arr_heads[] = $value;
				//--
				if(trim($value['content-id']) != '') {
					$this->last_cid = str_replace(array(' ', '<', '>'), array('', '', ''), (string)$value['content-id']);
				} //end if
				//--
			} //end if
			//--
			$this->printarray($value, $part_id); //recursive array
			//--
		} else {
			//--
			if($key === 'ctype_primary') {
				$vxf_mail_part_type = SmartUnicode::str_tolower((string)$value);
			} elseif($key === 'ctype_secondary') {
				$vxf_mail_part_stype = SmartUnicode::str_tolower((string)$value);
			} elseif(($key === 'name') OR ($key === 'filename')){
				$this->last_fname = (string) str_replace(' ', '_', (string)$value); // fix invalid spaces in file names
			} elseif($key === 'disposition') {
				if(SmartUnicode::str_tolower((string)$value) === 'attachment') {
					$vxf_mail_part_type = 'attachment';
				} //end if
			} elseif($key === 'body') {
				//-- calculate part id
				if ((string)$vxf_mail_part_type == 'text') {
					//--
					$tmp_part_id = 'txt_'.md5((string)trim((string)$value)); // text parts are not very long
					//--
				} else {
					//--
					$tmp_part_len = (int) strlen((string)$value); // this is the file size in bytes
					//--
					if((string)$this->last_cid == '') {
						$tmp_part_id = 'att_'.sha1((string)$this->last_fname.$tmp_part_len.SmartUnicode::sub_str((string)$value, 0, 8192).SmartUnicode::sub_str((string)$value, -8192, 8192)); // try to be unique
						$tmp_att_mod = 'normal';
					} else {
						$tmp_part_id = 'cid_'.$this->last_cid; // we have an ID from cid ...
						$tmp_att_mod = 'cid';
					} //end if else
					//--
				} //end if else
				//--
				$tmp_part_id = (string) strtolower((string)$tmp_part_id);
				//--
				if(((string)$tmp_part_id != '') AND (((string)$part_id == '') OR ((trim(strtolower($part_id)) == (string)$tmp_part_id) OR (trim(strtolower(str_replace(' ', '', $part_id))) == (string)$tmp_part_id)))) {
					// DEFAULT
					if((string)$vxf_mail_part_type == 'text') {
						//--
						// TEXT / HTML PART
						//--
						$value = (string) SmartUnicode::convert_charset((string)$value, $this->last_charset, $this->local_charset); // {{{SYNC-CHARSET-CONVERT}}}
						//--
						if((string)trim((string)$value) != '') { // avoid empty text parts
							$this->arr_parts[(string)$tmp_part_id] = array(
								'type'			=> (string) 'text',
								'mode'			=> (string) $vxf_mail_part_type.'/'.$vxf_mail_part_stype,
								'charset'		=> (string) $this->last_charset,
								'description'	=> (string) 'Text Part: '.$vxf_mail_part_type.'/'.$vxf_mail_part_stype,
								'content'		=> (string) trim((string)$value)
							);
						} //end if
						//--
					} else {
						//--
						// ATTACHMENT / CID PART
						//--
						$this->arr_atts[(string)$tmp_part_id] = array(
							'type'		=> (string) 'attachment',
							'mode'		=> (string) $tmp_att_mod,
							'filename'	=> (string) $this->last_fname,
							'filesize'	=> (int)    $tmp_part_len
						);
						if((string)$part_id == '') { // avoid include bodies for attachments except when they are express required
							$this->arr_atts[(string)$tmp_part_id]['description'] = 'Attachment: not includded (by default...)';
							$this->arr_atts[(string)$tmp_part_id]['content'] = '';
						} else {
							$this->arr_atts[(string)$tmp_part_id]['description'] = 'Attachment: includded';
							$this->arr_atts[(string)$tmp_part_id]['content'] = $value;
						} //end if else
						//--
					} //end else
					//--
				} //end if else
				//--
				//-- the body is always last in one cycle ; at the end of one cycle we reset types
				//--
				$value = ''; // free memory
				//--
				$this->last_charset = '';
				$this->last_fname = 'attachment.file';
				$this->last_cid = '';
				//--
				$vxf_mail_part_type = '';
				$vxf_mail_part_stype = '';
				//--
			} else {
				// don't know how to handle this ...
			} //end if else
			//--
		} //end else
	} //end while
	//--
} //END FUNCTION
//================================================================

} //END CLASS


//--------------------------------------------------------------
// Returns an array as :: Array(arr_texts, arr_atts)
//-- Usage:
//	$eml = new SmartMailerMimeDecode();
//	$head = $eml->get_header(SmartUnicode::sub_str($message, 0, 8192));
//	$msg = $eml->get_bodies($message, $part_id); // if $part_id is empty, all message will be displayed
//	unset($eml);
//--
//--------------------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


// This class will parse a raw mime email and return the structure.
// Returned structure is similar to that returned by imap_fetchstructure().

// Based on PHP Pear Mime Decode Class with Modifications, Fixes and Enhancements
// Copyright (c) unix-world.org
// LICENSE: This LICENSE is in the BSD license style.
// Copyright (c) 2002-2003, Richard Heyes <richard@phpguru.org>
// Copyright (c) 2003-2006, PEAR <pear-group@php.net>
// Other Authors: George Schlossnagle <george@omniti.com>, Cipriano Groenendal <cipri@php.net>, Sean Coates <sean@php.net>

/**
 * Class Smart Mailer Mime Extract
 *
 * @access 		private
 * @internal
 *
 */
final class SmartMailerMimeExtract {

	// ->
	// v.20190105

//================================================================
	//--
	private $charset = 'ISO-8859-1';	// The charset
	//--
	private $_header;					// The header part of the input 				:: @var string
	private $_body;						// The body part of the input 					:: @var string
	private $_error; 					// Store last error								:: @var string
	private $_include_bodies;			// whether to include bodies in returned object :: @var boolean
	private $_decode_bodies;			// Flag to determine whether to decode bodies 	:: @var boolean
	private $_decode_headers;			// Flag to determine whether to decode headers 	:: @var boolean
	//--
	private $errors;					// errors log
	//--
//================================================================


//================================================================
// Constructor.
// Sets up the object, initialise the variables, and splits and stores the header and body of the input.
// @param string (The input to decode)
// @access public
public function __construct($input, $encoding='') {
	//--
	if((string)$encoding == '') {
		if(defined('SMART_FRAMEWORK_CHARSET')) {
			if((string)SMART_FRAMEWORK_CHARSET != '') {
				$this->charset = (string) SMART_FRAMEWORK_CHARSET;
			} //end if
		} //end if
	} else {
		$this->charset = (string) $encoding;
	} //end if
	//--
	list($header, $body) = $this->_splitBodyHeader($input);
	//--
	$this->_header 			= $header;
	$this->_body 			= $body;
	$this->_include_bodies 	= true;
	$this->_decode_bodies  	= false;
	$this->_decode_headers 	= false;
	//--
	$this->errors 			= '';
	//--
} //END FUNCTION
//================================================================


//================================================================
public function get_working_charset() {
	//--
	return (string) $this->charset;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Begins the decoding process. (no more accepts to be called statically)
// @param array An array of various parameters that determine various things:
// :: include_bodies - Whether to include the body in the returned object.
// :: decode_bodies  - Whether to decode the bodies of the parts. (Transfer encoding)
// :: decode_headers - Whether to decode headers input (If called statically, this will be treated as the input)
// @return object Decoded results
// @access public
public function decode($params = null) {

	//-- Called via an object
	$this->_include_bodies = isset($params['include_bodies']) ? $params['include_bodies']  : false;
	$this->_decode_bodies  = isset($params['decode_bodies'])  ? $params['decode_bodies']   : false;
	$this->_decode_headers = isset($params['decode_headers']) ? $params['decode_headers']  : false;
	//--
	$structure = $this->_decode($this->_header, $this->_body);
	//--
	if($structure === false) {
		$structure = $this->raiseError($this->_error);
	} //end if
	//--

	//--
	return $structure;
	//--

} //END FUNCTION
//================================================================


//================================================================
// Performs the decoding. Decodes the body string passed to it.
// If it finds certain content-types it will call itself in a recursive fashion.
// @param string Header section
// @param string Body section
// @return object Results of decoding process
// @access private
private function _decode($headers, $body, $default_ctype = 'text/plain') {

	//--
	$return = new stdClass;
	//--
	$headers = $this->_parseHeaders($headers);
	//--

	//--
	foreach($headers as $u => $value) {
		//--
		if(isset($return->headers[SmartUnicode::str_tolower($value['name'])]) AND !is_array($return->headers[SmartUnicode::str_tolower($value['name'])])) {
			$return->headers[SmartUnicode::str_tolower($value['name'])]   = array($return->headers[SmartUnicode::str_tolower($value['name'])]);
			$return->headers[SmartUnicode::str_tolower($value['name'])][] = $value['value'];
		} elseif(isset($return->headers[SmartUnicode::str_tolower($value['name'])])) {
			$return->headers[SmartUnicode::str_tolower($value['name'])][] = $value['value'];
		} else {
			$return->headers[SmartUnicode::str_tolower($value['name'])] = $value['value'];
		} //end if else
		//--
	} //end foreach
	//--

	//--
	reset($headers);
	//--
	//while(list($key, $value) = @each($headers)) {
	foreach($headers as $key => $value) { // Fix: the above is deprecated as of PHP 7.2
		//--
		$headers[(string)$key]['name'] = (string) strtolower($headers[(string)$key]['name']);
		//--
		switch((string)$headers[(string)$key]['name']) {
			case 'content-type':
				$content_type = $this->_parseHeaderValue($headers[(string)$key]['value']);
				$regs = array();
				if(preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', (string)$content_type['value'], $regs)) {
					$return->ctype_primary   = $regs[1];
					$return->ctype_secondary = $regs[2];
				} //end if
				//if(isset($content_type['other'])) {
				if(is_array($content_type['other'])) {
					//while(list($p_name, $p_value) = @each($content_type['other'])) {
					foreach($content_type['other'] as $p_name => $p_value) { // Fix: the above is deprecated as of PHP 7.2
						//--
						if((string)$p_name == 'charset') {
							$content_charset = $p_value ; // charset
						} //end if
						//--
						$return->ctype_parameters[$p_name] = $p_value;
						//--
					} //end while
				} //end if
				break;
			case 'content-disposition';
				$content_disposition = $this->_parseHeaderValue($headers[(string)$key]['value']);
				$return->disposition = $content_disposition['value'];
				//if(isset($content_disposition['other'])) {
				if(is_array($content_disposition['other'])) {
					//while(list($p_name, $p_value) = @each($content_disposition['other'])) {
					foreach($content_disposition['other'] as $p_name => $p_value) { // Fix: the above is deprecated as of PHP 7.2
						$return->d_parameters[$p_name] = $p_value;
					} //end while
				} //end if
				break;
			case 'content-transfer-encoding':
				$content_transfer_encoding = $this->_parseHeaderValue($headers[(string)$key]['value']);
				break;
		} //end switch
		//--
	} //end while
	//--

	//--
	if(isset($content_type)) {
		//--
		switch(strtolower($content_type['value'])) {
			case 'text/plain':
				//--
				$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
				$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
				//--
				break;
			case 'text/html':
				//--
				$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
				$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
				//--
				break;
			case 'multipart/parallel':
			case 'multipart/report': // RFC1892
			case 'multipart/signed': // PGP
			case 'multipart/encrypted': // GPG
			case 'multipart/digest':
			case 'multipart/alternative':
			case 'multipart/related':
			case 'multipart/mixed':
				//--
				if(!isset($content_type['other']['boundary'])){
					$this->_error = 'No boundary found for ' . $content_type['value'] . ' part';
					return false;
				} //end if
				//-- the default part is text/plain, except for message/digest where is message/rfc822
				$default_ctype = (SmartUnicode::str_tolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';
				//--
				$parts = $this->_boundarySplit($body, $content_type['other']['boundary']);
				//--
				for($i=0; $i<Smart::array_size($parts); $i++) {
					//--
					list($part_header, $part_body) = $this->_splitBodyHeader($parts[$i]);
					$part = $this->_decode($part_header, $part_body, $default_ctype);
					//--
					if($part === false) {
						$part = $this->raiseError($this->_error);
					} //end if
					//--
					$return->parts[] = $part;
					//--
				} //end for
				//--
				break;
			case 'message/rfc822':
			case 'message/partial':
			case 'partial/message': // fake type to avoid Google and Yahoo to show the Un-Encoded part
				//--
				$obj = new SmartMailerMimeExtract($this->_decodeBody($body, $content_transfer_encoding['value']), $this->charset); // [OK]
				$return->parts[] = $obj->decode(array('include_bodies' => $this->_include_bodies, 'decode_bodies' => $this->_decode_bodies));
				//--
				unset($obj);
				//--
				break;
			default:
				//--
				if(!isset($content_transfer_encoding['value'])) {
					$content_transfer_encoding['value'] = '7bit';
				} //end if
				//--
				$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $content_transfer_encoding['value']) : $body) : null;
				//--
				break;
		} //end switch
		//--
	} else {
		//--
		$ctype = (array) explode('/', (string)$default_ctype);
		$return->ctype_primary   = $ctype[0];
		$return->ctype_secondary = $ctype[1];
		$this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body) : $body) : null;
		//--
	} //end if else
	//--

	//--
	return $return;
	//--

} //END FUNCTION
//================================================================


//================================================================
// Given a string containing a header and body
// section, this function will split them (at the first
// blank line) and return them.
// @param string Input to split apart
// @return array Contains header and body section
// @access private
private function _splitBodyHeader($input) {
	//--
	$match = array();
	if(preg_match("/^(.*?)\r?\n\r?\n(.*)/s", (string)$input, $match)) {
		return array($match[1], $match[2]);
	} //end if
	//--
	$this->_error = 'Could not split header and body';
	//--
	//return false;
	return array($input, ''); // bug fix: in the case the header is not separed by body we consider the message is only a header with empty body
	//--
} //END FUNCTION
//================================================================


//================================================================
// Parse headers given in $input and return
// as assoc array.
// @param string Headers to parse
// @return array Contains parsed headers
// @access private
private function _parseHeaders($input) {
	//--
	if((string)$input !== '') {
		//-- Unfold the input
		$input   = preg_replace("/\r?\n/", "\r\n", (string)$input);
		$input   = preg_replace("/\r\n(\t| )+/", ' ', (string)$input);
		$headers = explode("\r\n", trim($input));
		//--
		foreach($headers as $u => $value) {
			//--
			$hdr_name = SmartUnicode::sub_str($value, 0, $pos = SmartUnicode::str_pos($value, ':'));
			$hdr_value = SmartUnicode::sub_str($value, $pos+1);
			//--
			if((string)$hdr_value[0] == ' ') {
				$hdr_value = SmartUnicode::sub_str($hdr_value, 1);
			} //end if
			//--
			$return[] = array('name'=>$hdr_name, 'value'=>$this->_decode_headers ? $this->_decodeHeader($hdr_value) : $hdr_value);
			//--
		} //end foreach
		//--
	} else {
		//--
		$return = array();
		//--
	} //end if else
	//--
	return $return;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Function to parse a header value,
// extract first part, and any secondary
// parts (after ;) This function is not as
// robust as it could be. Eg. header comments
// in the wrong place will probably break it.
// @param string Header value to parse
// @return array Contains parsed result
// @access private
private function _parseHeaderValue($input) {
	//--
	$return = [];
	//--
	if(($pos = SmartUnicode::str_pos($input, ';')) !== false) {
		//--
		$return['value'] = (string) trim(SmartUnicode::sub_str($input, 0, $pos));
		$input = trim(SmartUnicode::sub_str($input, $pos+1));
		//--
		if((string)$input != '') {
			//-- This splits on a semi-colon, if there's no preceeding backslash. Can't handle if it's in double quotes however. (Of course anyone sending that needs a good slap).
			$parameters = preg_split('/\s*(?<!\\\\);\s*/i', (string)$input);
			//--
			for($i=0; $i<Smart::array_size($parameters); $i++) {
				//--
				$param_name  = (string) trim(SmartUnicode::sub_str($parameters[$i], 0, $pos = SmartUnicode::str_pos($parameters[$i], '='))); // added TRIM to fix invalid ' = ' case
				$param_value = (string) trim(SmartUnicode::sub_str($parameters[$i], $pos + 1)); // added TRIM to fix invalid ' = ' case
				//--
				if((string)$param_value[0] == '"') {
					$param_value = SmartUnicode::sub_str($param_value, 1, -1);
				} //end if
				//--
				if(!is_array($return['other'])) {
					$return['other'] = [];
				} //end if
				$return['other'][(string)$param_name] = $param_value;
				$return['other'][(string)SmartUnicode::str_tolower($param_name)] = $param_value;
				//--
			} //end for
			//--
		} //end if
		//--
	} else {
		//--
		$return['value'] = (string) trim((string)$input);
		//--
	} //end if else

	//--
	return (array) $return;
	//--

} //END FUNCTION
//================================================================


//================================================================
// UNIXW :: (FIXED)
// This function splits the input based on the given boundary
// @param string Input to parse
// @return array Contains array of resulting mime parts
// @access private
private function _boundarySplit($input, $boundary) {
	//--
	$tmp = (array) explode('--'.$boundary, (string)$input);
	//--
	for($i=1; $i<Smart::array_size($tmp); $i++) {
		$parts[] = $tmp[$i];
	} //end for
	//--
	return $parts;
	//--
} //END FUNCTION
//================================================================


//================================================================
// UNIXW :: (FIXED)
// Given a header, this function will decode it
// according to RFC2047. Probably not *exactly*
// conformant, but it does pass all the given
// examples (in RFC2047).
// @param string Input header value to decode
// @return string Decoded header value
// @access private
private function _decodeHeader($input) {
	//-- Remove white space between encoded-words
	$input = (string) preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', (string)$input); // insensitive
	//-- For each encoded-word...
	$matches = array();
	while(preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', (string)$input, $matches)) { // insensitive
		//--
		$encoded  = (string) $matches[1];
		$charset  = (string) $matches[2];
		$encoding = (string) $matches[3];
		$text     = (string) $matches[4];
		//--
		if(((string)$charset == '') OR ((string)$charset == 'us-ascii')) {
			$charset = 'iso-8859-1'; // correction :: {{{SYNC-CHARSET-FIX}}}
		} //end if
		//--
		switch(strtoupper($encoding)) {
			case 'B':
				$text = (string) base64_decode($text);
				$text = (string) SmartUnicode::convert_charset($text, $charset, $this->charset); // {{{SYNC-CHARSET-CONVERT}}}
				break;
			case 'Q':
				$text = (string) str_replace('_', ' ', $text); // // {{{SYNC-QUOTED-PRINTABLE-FIX}}} Fix: for google mail subjects ; normally on QP the _ must be encoded as =5F ; because google mail use the _ instead of space in all emails subject, it is considered a major enforcement to support this replacement
				$text = (string) quoted_printable_decode($text);
				$text = (string) SmartUnicode::convert_charset($text, $charset, $this->charset); // {{{SYNC-CHARSET-CONVERT}}}
				break;
			default:
				// as is
		} //end switch
		//--
		$input = (string) str_replace($encoded, $text, $input);
		//--
	} //end while
	//--
	return (string) $input;
	//--
} //END FUNCTION
//================================================================


//================================================================
// Given a body string and an encoding type,
// this function will decode and return it.
// @param  string Input body to decode
// @param  string Encoding type to use.
// @return string Decoded body
// @access private
private function _decodeBody($input, $encoding='') {
	//--
	switch(strtolower((string)$encoding)) {
		case 'base64':
			$input = (string) base64_decode((string)$input);
			break;
		case 'quoted-printable':
			$input = (string) quoted_printable_decode((string)$input);
			break;
		case 'x-uuencode':
			$input = (string) convert_uudecode((string)$input);
			break;
		case '7bit':
		case '8bit':
		default:
			// as is
	} //end switch
	//--
	// {{{SYNC-CHARSET-CONVERT}}} :: only text bodies will be converted using SmartUnicode::convert_charset(), but later as we do not know yet what they are really are
	//--
	return $input;
	//--
} //END FUNCTION
//================================================================


//================================================================
// error handler
// return the error
// @access private
private function raiseError($error) {
	$this->errors .= $error."\n";
} //END FUNCTION
//================================================================


} //END CLASS


//======================================================
// USAGE: (assume $input is your raw email)
// $decode = new SmartMailerMimeExtract($input, $charset); // [OK]
// $structure = $decode->decode(...see params[arr]...);
// print_r($structure);
//======================================================


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>