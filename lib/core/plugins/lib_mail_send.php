<?php
// [LIB - SmartFramework / Mail or SMTP Send, Mime Compose]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Mail Send: SendMail / SMTP (SSL/TLS/STARTTLS)
// DEPENDS:
//	* Smart::
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


//===========================================================================
// php class for sending mail messages (sendmail or SMTP w. SSL/TLS/STARTTLS)
// send multipart e-mail base64 encoded
// added CID parts extractor and re-embedd
//===========================================================================


/**
 * Class: SmartMailerSend - provides a Mail Send Client that supports both: MAIL or SMTP with SSL/TLS support.
 * It automatically includes the SmartMailerSmtpClient class when sending via SMTP method.
 *
 * This class is for very advanced use.
 * To easy send email messages use: SmartMailerUtils::send_email() / SmartMailerUtils::send_extended_email() functions.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.180727
 * @package 	Mailer:Send
 *
 */
final class SmartMailerSend {

	// ->

//==================================================== PRIVATE vars
//-- encoding
private $parts;					// init array
private $atts;					// init array
//-- error and log
public $error;					// error collect
public $log;					// debug log
//--
//==================================================== PUBLIC vars
//-- store the encoded message
public $mime_message;			// store the message
//-- debug level
public $debuglevel;				// debug level :: 0=no debug ; 1,2 debug
//-- how to encode
public $usealways_b64 = true;	// use always b64 encode (as google), instead of quote printable
//-- method (smtp or mail)
public $method;					// mail | smtp
//-- message parts
public $from_return;
public $namefrom;
public $from;
public $to;
public $cc;
public $bcc;
public $priority;
public $subject;
public $body;
public $headers;
public $is_html;
public $is_related;
public $charset;
//-- smtp
public $smtp_helo;				// smtp helo (server name that is allowed to send mails for this domain)
public $smtp_server;			// smtp server host or ip
public $smtp_port;				// smtp port
public $smtp_ssl;				// smtp ssl mode
public $smtp_cafile; 			// smtp ssl ca file
public $smtp_timeout;			// seconds to timeout
public $smtp_login;				// true | false :: use smtp auth
public $smtp_user;				// SMTP auth username
public $smtp_password;			// SMTP auth password
//--
//====================================================


//=====================================================================================
public function __construct() {
	//--
	$this->cleanup();
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
public function send($do_send, $raw_message='') {

	//--
	if(strlen($this->smtp_helo) <= 0) { // fix
		$this->smtp_helo = '127.0.0.1';
	} //end if
	//--
	$tmp_explode_arr = (array) explode('@', (string)$this->from);
	$tmp_name = trim($tmp_explode_arr[0]); // used for from name in the case it is empty
	$tmp_domain = trim($tmp_explode_arr[1]); // used for message ID
	//--
	if(strlen($this->namefrom) > 0) {
		$tmp_name = SmartUnicode::deaccent_str($this->namefrom);
	} else {
		$tmp_name = ucwords(str_replace(array('.', '-', '_'), array(' ', ' ', ' '), (string)$tmp_name));
	} //end if
	//--
	$this->mime_message = ''; // init
	//--
	$this->mime_message .= 'Return-Path: '.'<'.$this->from_return.'>'."\r\n";
	$this->mime_message .= 'From: '.$tmp_name.' <'.$this->from.'>'."\r\n"; // [ucwords] is safe here as the name is ISO-8859-1 (1st part of email address)
	$this->mime_message .= 'Date: '.date('D, d M Y H:i:s O')."\r\n";
	$this->mime_message .= 'To: '.$this->to."\r\n";
	//--
	if(is_array($this->cc)) {
		for($z=0; $z<Smart::array_size($this->cc); $z++) {
			if(strlen($this->cc[$z]) > 0) {
				$this->mime_message .= "Cc: ".$this->cc[$z]."\r\n";
			} //end if
		} //end for
	} elseif(strlen($this->cc) > 0) {
		$this->mime_message .= "Cc: ".$this->cc."\r\n";
	} //end if
	if((string)$do_send != 'yes') {
		if(strlen($this->bcc) > 0) {
			$this->mime_message .= "BCc: ".$this->bcc."\r\n";
		} //end if
	} //end if
	//--
	$this->mime_message .= "Subject: ".$this->prepare_subject($this->subject)."\r\n";
	//--
	switch((string)$this->priority) {
		case '1':
			$this->mime_message .=  "X-Priority: ".'1'."\r\n"; //high
			break;
		case '5':
			$this->mime_message .=  "X-Priority: ".'5'."\r\n"; //low
			break;
		case '3':
		default:
			$this->mime_message .=  "X-Priority: ".'3'."\r\n"; //normal
	} //end switch
	//--
	$this->mime_message .= "X-Mailer: ".'SmartFramework Mailer ('.SMART_FRAMEWORK_VERSION.')'."\r\n";
	$this->mime_message .= "MIME-Version: 1.0 ".'(SmartFramework Mime-Message v.2016.02.01)'."\r\n";
	$this->mime_message .= "Message-Id: ".'<ID-'.Smart::uuid_10_seq().'-'.Smart::uuid_10_str().'-'.Smart::uuid_10_num().'@'.Smart::safe_validname($tmp_domain).'>'."\r\n";
	//--
	if(strlen($this->headers) > 0) {
		$this->mime_message .= $this->headers; // must be end by \r\n
	} //end if
	//--
	if(strlen($raw_message) <= 0) {
		//--
		if(strlen($this->body) > 0) {
			//--
			if($this->is_html == false) {
				$this->add_attachment($this->body,  '',  'text/plain', 'inline');
			} else {
				$this->add_attachment('This is a MIME Message in HTML Format.',  'alternative-part.txt',  'text/plain', 'inline'); // antiSPAM needs an alternate body
				$this->add_attachment($this->body,  '',  'text/html', 'inline');
			} //end else
			//--
		} //end if
		//--
		$this->mime_message .= $this->build_multipart()."\r\n";
		//--
	} else {
		//-- RAW (get as is)
		$this->mime_message .= $raw_message."\r\n";
		//--
	} //end if else
	//--

	//--
	$err = '';
	//--
	if((string)$do_send == 'yes') {
		//--
	    if((string)$this->method == 'mail') {
			//-- MAIL METHOD
			if($this->debuglevel > 0) {
				$this->log = 'SendMail :: DEBUG :: MAIL';
			} //end if
			//--
			if(SmartUnicode::mailsend($this->to, $this->prepare_subject($this->subject),  '', $this->mime_message) != true) {
				$err = 'Mail Method Failed !';
				if($this->debuglevel > 0) {
					$this->log .= ' :: '.$err;
				} //end if
			} //end if else
			//--
	    } elseif((string)$this->method == 'smtp') {
			//-- SMTP METHOD
			if($this->debuglevel > 0) {
				$this->log = 'SendMail :: DEBUG :: SMTP';
			} //end if
			//--
			$smtp = new SmartMailerSmtpClient();
			//--
			if($this->debuglevel > 0) {
				$smtp->debug = true;
				$smtp->dbglevel = $this->debuglevel;
			} else {
				$smtp->debug = false;
			} //end if
			//--
			if((string)$this->smtp_cafile != '') {
				$smtp->set_ssl_tls_ca_file((string)$this->smtp_cafile);
			} //end if
			//--
			$connect = $smtp->connect($this->smtp_helo, $this->smtp_server, $this->smtp_port, $this->smtp_ssl);
			//--
			if($connect) {
				//--
				$login = 1; // default
				if($this->smtp_login) {
					$login = $smtp->login($this->smtp_user, $this->smtp_password);
				} //end if
				//--
				if($login) {
					//--
					$vfy = $smtp->mail($this->from);
					//--
					if($vfy) {
						//--
						$rcpt_to = $smtp->recipient($this->to);
						//--
						$rcpt_cc = 1;
						if(is_array($this->cc)) {
							for($z=0; $z<Smart::array_size($this->cc); $z++) {
								if(strlen($this->cc[$z]) > 0) {
									if($rcpt_cc == 1) {
										$rcpt_cc = $smtp->recipient($this->cc[$z]);
									} else {
										break;
									} //end if
								} //end if
							} //end for
						} elseif(strlen($this->cc) > 0) {
							$rcpt_cc = $smtp->recipient((string)$this->cc);
						} //end if
						//--
						$rcpt_bcc = 1;
						if(strlen($this->bcc) > 0) {
							$rcpt_bcc = $smtp->recipient((string)$this->bcc);
						} //end if
						//--
						if(((string)$rcpt_to == '1') AND ((string)$rcpt_cc == '1') AND ((string)$rcpt_bcc == '1')) {
							//--
							$sendresult = $smtp->data_send($this->mime_message);
							//--
							if((string)$sendresult != '1') {
								$err = 'SMTP SEND-DATA :: '.$smtp->error;
							} //end if
							//--
						} else {
							//--
							$err = 'SMTP RECIPIENT :: '.$smtp->error;
							//--
						} //end if
						//--
					} else {
						//--
						$err = 'SMTP MAIL :: '.$smtp->error;
						//--
					} //end if
					//--
				} else {
					//--
					$err = 'SMTP LOGIN :: '.$smtp->error;
					//--
				} //end if
				//--
				$smtp->noop();
				//--
			} else {
				//--
				$err = 'SMTP CONNECT :: '.$smtp->error;
				//--
			} //end if
			//--
			if(strlen($err) > 0) {
				$err = 'ERROR :: '.$err;
			} //end if
			//--
			$smtp->quit();
			//--
			if($this->debuglevel > 0) {
				$this->log .= 'SMTP Log :: '.$smtp->log;
			} //end if
			//--
	    } //end else
	    //--
	} //end if (send real)
	//--

	//--
	return $err;
	//--

} //END FUNCTION
//=====================================================================================


//=====================================================================================
public function add_attachment($message, $name='', $ctype='', $disp='attachment', $cid='', $realattachment='no') {
	//--
	switch(strtolower($ctype)) {
		//-- text parts
		case 'text/plain':
		case 'text/html':
			//--
			if((string)$disp != 'attachment') {
				$disp = 'inline'; // default
			} //end if
			//--
			$encode = 'base64'; // quoted-printable
			//--
			if((string)$disp == 'inline') {
				$charset = SmartUnicode::str_toupper(trim($this->charset));
			} //end if
			//--
			break;
		//-- email messages
		case 'message/rfc822':
		case 'message/partial':
		case 'partial/message': // fake type to avoid Google and Yahoo to show the Un-Encoded part
			//-- OLD method :: rewrite type to avoid conflicts (gmail, yahoo, thunderbird)
			//$ctype = 'partial/message';
			//$encode = 'base64';
			//$disp = 'attachment';
			//-- NEW Method (tested with Thunderbird and GMail)
			$ctype = 'message/rfc822';
			$encode = '7bit'; // this is known to work with Thunderbird and GMail
			//$encode = 'base64'; this does not work with Thunderbird ...
			$disp = 'inline';
			//--
			$name = 'forwarded_message_'.date('YmdHis').'_'.Smart::random_number(10000,99999).'.eml';
			$filename = $name ;
			//--
			break;
		//-- the rest ...
		default:
			//--
			if((string)$ctype == '') {
				$ctype = 'application/octet-stream';
			} //end if
			//--
			$encode = 'base64';
			//--
			if(((string)$ctype == 'image') OR ((string)$ctype == 'image/jpeg') OR ((string)$ctype == 'image/jpg') OR ((string)$ctype == 'image/png') OR ((string)$ctype == 'image/gif')) {
				if((string)$disp != 'inline') {
					$disp = 'attachment'; // default
				} //end if
			} else {
				$disp = 'attachment';
			} //end if else
			//--
			$filename = $name ;
			//--
	} //end switch
	//--
	if((string)$realattachment == 'yes') { // real attachments
		$this->atts[] = array(
			'ctype' 	=> $ctype,
			'message' 	=> $message,
			'charset' 	=> $charset,
			'encode' 	=> $encode,
			'disp' 		=> $disp,
			'name' 		=> $name,
			'filename'	=> $filename,
			'cid'		=> $cid
		);
	} else {
		$this->parts[] = array(
			'ctype' 	=> $ctype,
			'message' 	=> $message,
			'charset' 	=> $charset,
			'encode' 	=> $encode,
			'disp' 		=> $disp,
			'name' 		=> $name,
			'filename'	=> $filename,
			'cid'		=> $cid
		);
	} //end if else
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
private function cleanup() {
	//--
	$this->parts = array();
	$this->atts = array();
	$this->mime_message = '';
	//--
	$this->usealways_b64 = true;
	//--
	$this->method = 'mail';
	//--
	$this->from_return = '';
	$this->namefrom = '';
	$this->from = '';
	$this->to = '';
	$this->cc = ''; // '' or array()
	$this->bcc = '';
	$this->priority = '';
	$this->subject =  '';
	$this->body =  '';
	$this->headers = '';
	$this->is_html = false;
	$this->is_related = '';
	$this->charset = 'ISO-8859-1';
	//--
	$this->error = '';
	$this->debuglevel = 0;
	$this->log = '';
	//--
	$this->smtp_helo = '';
	$this->smtp = '';
	$this->smtp_server = '';
	$this->smtp_port = '25';
	$this->smtp_ssl = '';
	$this->smtp_timeout = '30';
	$this->smtp_login = false;
	$this->smtp_user = '';
	$this->smtp_password = '';
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
private function prepare_subject($subject) {
	//--
	$charset = strtoupper(trim($this->charset));
	//--
	if((string)$charset != 'ISO-8859-1') {
		$subject = '=?'.$charset.'?B?'.base64_encode($subject).'?=';
	} //end if
	//--
	return $subject;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// v.150119, added sha512 checksum
// v.180209, added quoted printable fix
private function build_message($part) {
	//--
	$part = (array) $part;
	//--
	$checksum = sha1($part['message']);
	//--
	if((string)$part['encode'] == '7bit') {
		// leave as is
//	} elseif((string)$part['encode'] == 'quoted-printable') {
//		$part['message'] = str_replace(' ', '-', quoted_printable_encode((string)$part['message'])); // {{{SYNC-QUOTED-PRINTABLE-FIX}}}
//	} elseif((string)$part['encode'] == 'uuencode') {
//		$part['message'] = chunk_split(convert_uuencode((string)$part['message']), 76, "\r\n");
	} else { // base64 encode
		$part['encode'] = 'base64'; // rewrite this for all other cases
		$part['message'] = chunk_split(base64_encode((string)$part['message']), 76, "\r\n"); // encode b64
	} //end if
	//--
	return 	'Content-Type: '.$part['ctype'].($part['charset'] ? '; charset='.$part['charset'] : '').($part['name'] ? '; name="'.$part['name'].'"' : '')."\r\n".
			'Content-Transfer-Encoding: '.strtoupper((string)$part['encode'])."\r\n".
			($part['cid'] ? 'Content-ID: <'.$part['cid'].'>'."\r\n" : '').
			'Content-Disposition: '.$part['disp'].';'.($part['filename'] ? ' filename="'.$part['filename'].'"' : '')."\r\n".
			'Content-Decoded-Checksum-SHA1: '.$checksum."\r\n".
			"\r\n".$part['message']."\r\n";
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// v.2016-02-01 (multipart/mixed + multipart/related)
private function build_multipart() {
	//--
	$timeduid = Smart::uuid_10_seq(); // 10 chars, timed based, can repeat only once in 1000 years for the same millisecond
	$timedrid = strrev($timeduid);
	$entropy = Smart::unique_entropy('mail/send'); // this generate a very random value
	$boundary 			= '_===-Mime.Part____.'.$timeduid.'_'.md5('@MimePart---#Boundary@'.$entropy).'_P_.-=_'; // 69 chars of 70 max
	$relatedboundary 	= '_-==-Mime.Related_.'.$timedrid.'_'.md5('@MimeRelated#Boundary@'.$entropy).'_R_.=-_'; // 69 chars of 70 max
	//--
	$multipart = '';
	$multipart .= 'Content-Type: multipart/mixed; boundary="'.$boundary.'"'."\r\n"."\r\n";
	$multipart .= 'This is a multi-part message in MIME format.'."\r\n"."\r\n";
	$multipart .= '--'.$boundary."\r\n";
	$multipart .= 'Content-Type: multipart/related; boundary="'.$relatedboundary.'"'."\r\n";
	//-- cid parts
	$multipart .= "\r\n";
	for($i=Smart::array_size($this->parts)-1; $i>=0; $i--) {
		$multipart .= '--'.$relatedboundary."\r\n";
		$multipart .= $this->build_message($this->parts[$i]);
	} //end for
	$multipart .= "\r\n";
	$multipart .= '--'.$relatedboundary.'--'."\r\n";
	//-- attachments
	$multipart .= "\r\n";
	for($i=Smart::array_size($this->atts)-1; $i>=0; $i--) {
		$multipart .= '--'.$boundary."\r\n";
		$multipart .= $this->build_message($this->atts[$i]);
	} //end for
	//--
	$multipart .= "\r\n";
	$multipart .= '--'.$boundary.'--'."\r\n";
	//--
	return $multipart;
	//--
} //END FUNCTION
//=====================================================================================


} // END CLASS


//=====================================================
// #Example usage (smtp)
//$mail = new SmartMailerSend();
//$mail->charset = 'UTF-8';
//$mail->method = 'smtp';
//$mail->SMTP_HOST = 'localhost';
//$mail->SMTP_Port = '25';
//$mail->Helo = 'mail_server';
//$mail->Timeout = '10';
//$mail->SMTPDebug = 0;
//$mail->SMTPAuth = true;
//$mail->Username = 'user';
//$mail->Password = 'pass';

// #Example usage (mail)
//$mail = new SmartMailerSend();
//$mail->method = 'mail';

// #Example usage BOTH : Mail & SMTP
//$mail->priority = '1'; // high=1 | low=5 | normal=3
//$mail->from = "address@yourdomain.ext";
//$mail->from_return="address@yourdomain.ext";
//$mail->headers = "Errors-To: postmaster@yourdomain.ext";
//$mail->to = "another-address@yourdomain.ext";
//$mail->cc = "address@yourdomain.ext";
//$mail->subject = "Testing...";
//$mail->is_html = false; // false | true
//$mail->body = "This is just a test.";
//$attachment = file_get_contents(test.jpg);
//$mail->add_attachment("$attachment", "test.jpg", "image/jpeg");
//$mail->send();
//=====================================================


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================



//======================================================
// SMTP class (WIN:LINUX:UNIX)
// DEPENDS ON 'Lib Smart'
//======================================================
// Requires PHP 4.4.1 or later
//======================================================
// Define an SMTP class that can be used to connect
// and communicate with any SMTP server. It implements
// all the SMTP functions defined in RFC821 except TURN.
// SMTP is rfc 821 compliant and implements all the rfc 821 SMTP
// commands except TURN which will always return a not implemented
// error. SMTP also provides some utility methods for sending mail
// to an SMTP server.
//======================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: SmartMailerSmtpClient - provides the raw communication protocol between PHP and a SMTP server with support for SSL/TLS.
 * This class is for very advanced use.
 *
 * It just implements the communication protocol between PHP and a SMTP server.
 * It does and NOT implement the Mail Send Client.
 * To easy send email messages use: SmartMailerUtils::send_email() / SmartMailerUtils::send_extended_email() functions.
 * For more advanced needs on send emails use the SmartMailerSend class ...
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.170913
 * @package 	Mailer:Send
 *
 */
final class SmartMailerSmtpClient {

	// ->

//===============================================
//--
public $socket = false; 	// socket resource ID
//--
public $timeout = 30;		// socket timeout in seconds
//--
public $debug = false;		// to debug or not
public $dbglevel = 1;		// debug level (1 or 2)
//--
public $error = '';			// the error message
public $log = '';			// if debug is enabled, this is the log
//--
private $cafile = '';		// Certificate Authority File (instead of using the global SMART_FRAMEWORK_SSL_CA_FILE can use a private cafile
//--
//===============================================


//=====================================================================================
// [INIT]
public function __construct() {
	//--
	$this->socket = false;
	//--
	$this->log = '';
	$this->error = '';
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC] :: set the SSL/TLS Certificate Authority File
public function set_ssl_tls_ca_file($cafile) {
	//--
	$this->cafile = (string) $cafile;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Opens a socket to the specified server. Returns 1 on success, 0 on fail
// SMTP CODE SUCCESS: 220
// SMTP CODE FAILURE: 421
public function connect($helo, $server, $port=25, $sslversion='') {

	//-- inits
	$this->socket = false;
	//--

	//-- checks
	$helo = trim((string)$helo);
	$server = trim((string)$server);
	if((strlen($server) <= 0) OR (strlen($server) > 255)) {
		$this->error = '[ERR] Invalid Server to Connect ! ['.$server.']';
		return 0;
	} //end if
	//--
	$port = (int) $port;
	if(($port <= 0) OR ($port > 65535)) {
		$this->error = '[ERR] Invalid Port to Connect ! ['.$port.']';
		return 0;
	} //end if
	//--

	//--
	$protocol = '';
	$start_tls = false;
	//--
	if(strlen($sslversion) > 0) {
		//--
		if(!function_exists('openssl_open')) {
			$this->error = '[ERR] PHP OpenSSL Extension is required to perform SSL requests !';
			return 0;
		} //end if
		//--
		switch(strtolower($sslversion)) {
			case 'starttls':
				$start_tls = true;
				$protocol = ''; // reset because will connect in a different way
				break;
			case 'ssl':
				$protocol = 'ssl://';
				break;
			case 'sslv3':
				$protocol = 'sslv3://';
				break;
			case 'tls':
			default:
				$protocol = 'tls://';
		} //end switch
		//--
	} //end if else
	//--

	//--
	if($this->debug) {
		$this->log .= '[INF] Connecting to Mail Server: '.$protocol.$server.':'.$port."\n";
	} //end if
	//--

	//--
	//$sock = @fsockopen($protocol.$server, $port, $errno, $errstr, $this->timeout);
	$stream_context = @stream_context_create();
	if(((string)$protocol != '') OR ($start_tls === true)) {
		//--
		$cafile = '';
		if((string)$this->cafile != '') {
			$cafile = (string) $this->cafile;
		} elseif(defined('SMART_FRAMEWORK_SSL_CA_FILE')) {
			if((string)SMART_FRAMEWORK_SSL_CA_FILE != '') {
				$cafile = (string) SMART_FRAMEWORK_SSL_CA_FILE;
			} //end if
		} //end if
		//--
		if((string)$cafile != '') {
			@stream_context_set_option($stream_context, 'ssl', 'cafile', Smart::real_path((string)$cafile));
		} //end if
		//--
		@stream_context_set_option($stream_context, 'ssl', 'ciphers', 				(string)SMART_FRAMEWORK_SSL_CIPHERS); // allow only high ciphers
		@stream_context_set_option($stream_context, 'ssl', 'verify_host', 			(bool)SMART_FRAMEWORK_SSL_VFY_HOST); // allways must be set to true !
		@stream_context_set_option($stream_context, 'ssl', 'verify_peer', 			(bool)SMART_FRAMEWORK_SSL_VFY_PEER); // this may fail with some CAs
		@stream_context_set_option($stream_context, 'ssl', 'verify_peer_name', 		(bool)SMART_FRAMEWORK_SSL_VFY_PEER_NAME); // allow also wildcard names *
		@stream_context_set_option($stream_context, 'ssl', 'allow_self_signed', 	(bool)SMART_FRAMEWORK_SSL_ALLOW_SELF_SIGNED); // must allow self-signed certificates but verified above
		@stream_context_set_option($stream_context, 'ssl', 'disable_compression', 	(bool)SMART_FRAMEWORK_SSL_DISABLE_COMPRESS); // help mitigate the CRIME attack vector
		//--
	} //end if else
	$sock = @stream_socket_client($protocol.$server.':'.$port, $errno, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $stream_context);
	//--
	if(!is_resource($sock)) {
		$this->error = '[ERR] Could not open connection. Error: '.$errno.' :: '.$errstr;
		return 0;
	} //end if
	//--
	$this->socket = $sock;
	unset($sock);
	//--
	@stream_set_timeout($this->socket, (int)SMART_FRAMEWORK_NETSOCKET_TIMEOUT);
	if($this->debug) {
		$this->log .= '[INF] Set Socket Stream TimeOut to: '.SMART_FRAMEWORK_NETSOCKET_TIMEOUT."\n";
	} //end if
	//--

	//--
	$reply = $this->retry_data();
	if(strlen($this->error) > 0) {
		//--
		@fclose($this->socket);
		$this->socket = false;
		//--
		return 0;
		//--
	} //end if
	if($this->debug) {
		$this->log .= '[REPLY] \''.$reply.'\''."\n";
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '220') {
		//--
		$this->error = '[ERR] Server Reply is NOT OK // '.$test.' // '.$reply;
		//--
		@fclose($this->socket);
		$this->socket = false;
		//--
		return 0;
		//--
	} //end if
	//--

	//--
	if(!$this->hello($helo)) {
		//--
		$this->error = '[ERR] HELLO Command Failed // '.$helo;
		//--
		@fclose($this->socket);
		$this->socket = false;
		//--
		return 0;
		//--
	} //end if
	//--

	//--
	if($start_tls === true) {
		//--
		if($this->starttls($stream_context) != '1') {
			//--
			if((string)$this->error == '') {
				$this->error = '[ERR] Connection CRYPTO ENABLE Failed ...';
			} //end if
			//--
			@fclose($this->socket);
			$this->socket = false;
			//--
			return 0; // error message comes from above
			//--
		} //end if
		//-- BugFix: Xmail fails after STARTTLS without sending again the HELO as 503 BAD Sequence of commands
		if(!$this->hello($helo)) {
			//--
			$this->error = '[ERR] HELLO Command Failed // '.$helo;
			//--
			@fclose($this->socket);
			$this->socket = false;
			//--
			return 0;
			//--
		} //end if
		//--
	} //end if
	//--

	//--
	$chk_crypto = (array) @stream_get_meta_data($this->socket);
	if(((string)$protocol != '') OR ($start_tls === true)) { // avoid connect normally if SSL/TLS was explicit required
		if(!SmartUnicode::str_icontains($chk_crypto['stream_type'], '/ssl')) { // will return something like: tcp_socket/ssl
			//--
			$this->error = '[ERR] Connection CRYPTO CHECK Failed ...';
			//--
			@fclose($this->socket);
			$this->socket = false;
			//--
			return 0;
			//--
		} //end if
	} //end if
	//--

	//--
	return 1;
	//--

} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// enable crypto on server
// Sends the command STARTTLS to the SMTP server.
// Implements from rfc 821: STARTTLS <CRLF>
// SMTP CODE SUCCESS: 220
// SMTP CODE ERROR  : 501, 454
private function starttls($stream_context) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Starting TLS on Mail Server // STARTTLS'."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('STARTTLS');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '220') {
		$this->error = '[ERR] Server StartTLS Failed :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	if(!$this->socket) {
		$this->error = '[ERR] Server StartTLS Failed :: Invalid Socket';
		return 0;
	} //end if
	//--
	$test_starttls = @stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
	if(!$test_starttls) {
		$this->error = '[ERR] Server StartTLS Failed to be Enabled on Socket ...';
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Sends both user and pass. Returns 1 on Success and 0 on Error
// Performs SMTP authentication.  Must be run after running the
// Hello() method.  Returns true if successfully authenticated.
// Success Codes are: 334 and final is 235
public function login($username, $pass) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Login to Mail Server (USER = '.$username.')'."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('AUTH LOGIN');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '334') {
		$this->error = '[ERR] SMTP Server did not accept Auth Login :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd(base64_encode($username)); // send encoded username
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '334') {
		$this->error = '[ERR] SMTP Server did not accept the UserName: '.$username.' :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd(base64_encode($pass)); // send encoded password
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '235') {
		$this->error = '[ERR] SMTP Server did not accept the Password: '.'*****'.' :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Sends both user and pass. Returns 1 on Success and 0 on Error
// Sends the quit command to the server and then closes the socket
// if there is no error or the $close_on_error argument is true.
// Implements from rfc 821: QUIT <CRLF>
// SMTP CODE SUCCESS: 221
// SMTP CODE ERROR  : 500
public function quit() {
	//--
	if($this->debug) {
		$this->log .= '[INF] Sending QUIT to Mail Server !'."\n";
	} //end if
	//--
	if(!$this->socket) {
		$this->error = '[ERR] SMTP Connection cannot QUIT, it appears is not opened !';
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('QUIT');
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '221') {
		if($this->debug) {
			$this->log .= '[WARN] SMTP Server rejected Quit Command !'."\n";
		} //end if
	} //end if
	//--
	@fclose($this->socket);
	$this->socket = false;
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// ping server
// Sends the command NOOP to the SMTP server.
// Implements from rfc 821: NOOP <CRLF>
// SMTP CODE SUCCESS: 250
// SMTP CODE ERROR  : 500, 421
public function noop() {
	//--
	if($this->debug) {
		$this->log .= '[INF] Ping the Mail Server // NOOP'."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('NOOP');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '250') {
		$this->error = '[ERR] Server Noop Failed :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Help for supported commands
// Sends the command HELP to the SMTP server.
// Implements rfc 821: HELP [ <SP> <string> ] <CRLF>
// SMTP CODE SUCCESS: 211,214
// SMTP CODE ERROR  : 500,501,502,504,421
public function help() {
	//--
	if($this->debug) {
		$this->log .= '[INF] Ask Help from Mail Server'."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('HELP');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if(((string)$test != '211') AND ((string)$test != '214')) {
		$this->error = '[ERR] Server Help Failed :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Sends the RSET command to abort and transaction that is currently in progress.
// Returns true if successful false otherwise.
// Implements rfc 821: RSET <CRLF>
// SMTP CODE SUCCESS: 250
// SMTP CODE ERROR  : 500,501,504,421
public function reset() {
	//--
	if($this->debug) {
		$this->log .= '[INF] Reset the Connection to Mail Server'."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('RSET');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '250') {
		$this->error = '[ERR] Server Reset Failed :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// Sends the EHLO/HELO command to the smtp server.
// This makes sure that we and the server are in the same known state.
// Implements from rfc 821: HELO <SP> <domain> <CRLF>
// SMTP CODE SUCCESS: 250
// SMTP CODE ERROR  : 500, 501, 504, 421
public function hello($hostname) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Sending EHLO / HELO to Mail Server !'."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('EHLO '.$hostname); // first send EHLO (Extended SMTP)
	$test = $this->answer_code($reply);
	if((string)$test != '250') {
		$reply = $this->send_cmd('HELO '.$hostname); // if EHLO fails, try the classic HELO
		$test = $this->answer_code($reply);
		if((string)$test != '250') {
			if($this->debug) {
				$this->log .= '[WARN] Failed to Send EHLO/HELO to the Mail Server ! (Server answer is: '.$test.' // '.$reply.')'."\n"; // only set warning as this is not a fatal error
			} //end if
			return 0; // if both fail, then stop !
		} //end if
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Verifies that the name is recognized by the server.
// Returns false if the name could not be verified otherwise the response from the server is returned.
// Implements rfc 821: VRFY <SP> <string> <CRLF>
// SMTP CODE SUCCESS: 250,251
// SMTP CODE FAILURE: 550,551,553
// SMTP CODE ERROR  : 500,501,502,421
public function verify($name) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Verify is sent on Mail Server for: '.$name."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('VRFY '.$name);
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if(((string)$test != '250') AND ((string)$test != '251')) {
		$this->error = '[ERR] Server Verify Failed :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Expand takes the name and asks the server to list all the people who are members of the _list_.
// Expand will return back an empty string for error and the reply with reply lines ended by [CRLF].
// Each value in the array returned has the format of: [ <full-name> <sp> ] <path>
// The definition of <path> is defined in rfc 821
// Implements rfc 821: EXPN <SP> <string> <CRLF>
// SMTP CODE SUCCESS: 250
// SMTP CODE FAILURE: 550
// SMTP CODE ERROR  : 500,501,502,504,421
public function expand($name) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Expand is sent on Mail Server for: '.$name."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return '';
	} //end if
	//--
	$reply = $this->send_cmd('EXPN '.$name);
	if(strlen($this->error) > 0) {
		return '';
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '250') {
		$this->error = '[ERR] Server Expand Failed :: '.$test.' // '.$reply;
		return '';
	} //end if
	//--
	return $reply;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Starts a mail transaction from the email address specified in
// $from. Returns true if successful or false otherwise. If True
// the mail transaction is started and then one or more Recipient
// commands may be called followed by a Data command.
// Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
// SMTP CODE SUCCESS: 250
// SMTP CODE SUCCESS: 552,451,452
// SMTP CODE SUCCESS: 500,501,421
public function mail($from) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Mail command is sent on Mail Server for: '.$from."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('MAIL FROM:<'.$from.'>');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '250') {
		$this->error = '[ERR] MAIL Not Accepted From Server :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Sends the command RCPT to the SMTP server with the TO: argument of $to.
// Returns true if the recipient was accepted false if it was rejected.
// Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
// SMTP CODE SUCCESS: 250,251
// SMTP CODE FAILURE: 550,551,552,553,450,451,452
// SMTP CODE ERROR  : 500,501,503,421
public function recipient($to) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Recipient command is sent on Mail Server for: '.$to."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('RCPT TO:<'.$to.'>');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if(((string)$test != '250') AND ((string)$test != '251')) {
		$this->error = '[ERR] RCPT Not Accepted From Server :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PUBLIC]
// Issues a data command and sends the msg_data to the server finializing the mail transaction.
// The data $msg_data is the message that is to be send with the headers.
// Each header needs to be on a single line followed by a <CRLF> with the message headers
// and the message body being seperated by and additional <CRLF>.
// Implements rfc 821: DATA <CRLF>
// SMTP CODE INTERMEDIATE: 354
//     [data]
//     <CRLF>.<CRLF>
//     SMTP CODE SUCCESS: 250
//     SMTP CODE FAILURE: 552,554,451,452
// SMTP CODE FAILURE: 451,554
// SMTP CODE ERROR  : 500,501,503,421
public function data_send($msg_data) {
	//--
	if($this->debug) {
		$this->log .= '[INF] Data-Send command is sent on Mail Server'."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$reply = $this->send_cmd('DATA');
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	$test = $this->answer_code($reply);
	if((string)$test != '354') {
		$this->error = '[ERR] Data-Send command Failed on Server :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	// The server is ready to accept data. According to rfc 821 we should not send more than 1000 characters including the CRLF on a single line
	// so we will break the data up into lines by \r and/or \n then if needed we will break each of those into smaller lines to fit within the limit.
	// In addition we will be looking for lines that start with a period '.' and append and additional period '.' to that line.
	// NOTE: this does not count towards are limit.
	//-- normalize the line breaks so we know the explode works
	$msg_data = str_replace(array("\r\n", "\r"), array("\n", "\n"), $msg_data); // replacing the CRLF to LF
	$lines = (array) explode("\n", (string)$msg_data);
	$msg_data = ''; // cleanup
	//--
	// We need to find a good way to determine if headers are in the msg_data or if it is a straight msg body.
	// Currently assuming rfc 822 definitions of msg headers and if the first field of the first line (':' sperated) does not contain a space
	// then it _should_ be a header and we can process all lines before a blank "" line as headers.
	//--
	$field = SmartUnicode::sub_str($lines[0], 0, SmartUnicode::str_pos($lines[0], ':'));
	$in_headers = false;
	//--
	if((strlen($field) > 0) AND (!SmartUnicode::str_contains($field, ' '))) {
		$in_headers = true;
	} //end if
	//--
	$max_line_length = 800; // used below ; set here for ease in change (we use a lower value than 1000 as we use UTF-8 text)
	//--
	//while(list(,$line) = @each($lines)) {
	//while(list($key,$line) = @each($lines)) { // FIX to be compatible with the upcoming PHP 7
	foreach($lines as $key => $line) { // Fix: the above is deprecated as of PHP 7.2
		//--
		//$lines_out = null;
		$lines_out = array(); // Fix !!
		//--
		if(((string)$line == '') AND ($in_headers)) {
			$in_headers = false;
		} //end if
		//-- ok we need to break this line up into several smaller lines
		while(SmartUnicode::str_len($line) > $max_line_length) {
			//--
			$pos = SmartUnicode::str_rpos(SmartUnicode::sub_str($line, 0, $max_line_length), ' '); // here we need reverse strpos
			$lines_out[] = SmartUnicode::sub_str($line, 0, $pos);
			$line = SmartUnicode::sub_str($line, ($pos + 1));
			//-- if we are processing headers we need to add a LWSP-char to the front of the new line rfc 822 on long msg headers
			if($in_headers) {
				$line = "\t".$line;
			} //end if
			//--
		} //end while
		//--
		$lines_out[] = $line;
		//-- now send the lines to the server
		//while(list(,$line_out) = @each($lines_out)) {
		//while(list($key,$line_out) = @each($lines_out)) { // FIX to be compatible with the upcoming PHP 7
		foreach($lines_out as $key => $line_out) { // Fix: the above is deprecated as of PHP 7.2
			//--
			if(strlen($line_out) > 0) {
				if(SmartUnicode::sub_str($line_out, 0, 1) == '.') {
					$line_out = '.'.$line_out;
				} //end if
			} //end if
			//--
			@fputs($this->socket, $line_out."\r\n");
			//--
		} //end while
		//--
	} //end while
	//-- ok all the message data has been sent so lets get this over with aleady
	@fputs($this->socket, "\r\n".'.'."\r\n");
	//--
	$reply = $this->retry_data();
	$test = $this->answer_code($reply);
	//--
	if($this->debug) {
		$this->log .= '[INF] Data-Send Mail Server Reply is :: '.$test.' // '.$reply."\n";
	} //end if
	//--
	if(strlen($this->error) > 0) {
		return 0;
	} //end if
	//--
	if((string)$test != '250') {
		$this->error = '[ERR] Data-Send Finalize Failed on Server :: '.$test.' // '.$reply;
		return 0;
	} //end if
	//--
	return 1;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// :: PRIVATES ::
//=====================================================================================


//=====================================================================================
// [PRIVATE]
// read the server code (1st 3 chars ; Ex: 220 = OK)
private function answer_code($reply) {
	return (string) trim((string)SmartUnicode::sub_str(trim($reply), 0, 3));
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PRIVATE]
// Read in as many lines as possible either before eof or socket timeout occurs on the operation.
// With SMTP we can tell if we have more lines to read if the 4th character is '-' symbol.
// If the 4th character is a space then we don't need to read anything else.
// @return string
private function retry_data() {
	//--
	if(!$this->socket) {
		$this->error = '[ERR] SMTP Retry Data: No connection to server';
		return '';
	} //end if
	//--
	$data = '';
	//--
	while($str = @fgets($this->socket, 515)) { // do not change the read buffer (default is set to 515 = 512 + 3)
		//--
		$data .= $str;
		//--
		if($this->debug) {
			if($this->dbglevel >= 2) { // advanced debug
				$this->log .= 'SMTP [3] // RETRY DATA // partial data is: '.$str."\n";
			} //end if
		} //end if
		//-- if the 4th character is a space then we are done reading so just break the loop (else the 4th char. is '-')
		if(SmartUnicode::sub_str($str, 3, 1) == ' ') {
			break;
		} //end if
		//--
	} //end while
	//--
	return $data;
	//--
} //END FUNCTION
//=====================================================================================


//=====================================================================================
// [PRIVATE]
// Sends a user defined command string to the POP server and returns the results.
// Useful for non-compliant or custom POP servers.
// Do NOT include the \r\n as part of your command string - it will be appended automatically.
// The return value is a standard fgets() call, which will read up to buffer bytes of data,
// until it encounters a new line, or EOF, whichever happens first.
// This method works best if $cmd responds with only one line of data.
private function send_cmd($cmd) {
	//--
	if(!$this->socket) {
		$this->error = '[ERR] SMTP Send Command: No connection to server // '.$cmd;
		return '';
	} //end if
	//--
	if(strlen($cmd) <= 0) {
		$this->error = '[ERR] SMTP Send Command: Empty command to send !';
		return '';
	} //end if
	//--
	@fputs($this->socket, $cmd."\r\n");
	//--
	$reply = $this->retry_data();
	//--
	if($this->debug) {
		$this->log .= '[INF] SMTP Send Command ['.$cmd.']'."\n".'[REPLY]: \''.$reply.'\''."\n";
	} //end if
	//--
	return $reply;
	//--
} //END FUNCTION
//=====================================================================================

} //END CLASS


/**** HOW TO
connect
helo
login
noop
recipient
data_send
quit
****/

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>