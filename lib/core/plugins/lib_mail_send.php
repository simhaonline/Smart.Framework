<?php
// [LIB - Smart.Framework / Plugins / Mail Send (Mail, SMTP, Mime Compose)]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
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
 * This class is a low level message composer and send utility for advanced usage.
 * If defined the SMART_SOFTWARE_MAILSEND_SAFE_RULES and set to TRUE will use extra safe rules when composing email mime messages to be sent (Ex: adding alternate TEXT body to HTML email messages)
 * To easy send email messages use: SmartMailerUtils::send_email() / SmartMailerUtils::send_extended_email() functions.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20191213
 * @package 	Plugins:Mailer
 *
 */
final class SmartMailerSend {

	// ->


	//==================================================== PRIVATE vars
	//-- encoding
	private $parts;					// init array
	private $atts;					// init array
	private $composed;				// init flag
	//--
	//==================================================== PUBLIC vars

	//-- debug level, error and log

	/**
	 * Debug Level
	 * Valid values: 0 = no debug ; 1 = simple debug (partial) ; 2 = advanced debug (full)
	 * @var INTEGER+
	 * @default 0
	 */
	public $debuglevel;

	/**
	 * Errors Collector
	 * If any error occurs will be set here
	 * @var STRING
	 * default ''
	 */
	public $error;

	/**
	 * Debug Log Collector
	 * If Debug, this will return the partial or full Log as string, depend on how $debuglevel is set
	 * @var STRING
	 * default ''
	 */
	public $log;
	//--

	//-- how to encode

	/**
	 * If set to TRUE will encode using Base64Encode (the most safe)
	 * Otherwise will use QuotePrintableEncode (the most used)
	 * @var BOOLEAN
	 * @default TRUE
	 */
	public $usealways_b64 = true;

	//-- method (smtp or mail)

	/**
	 * The Mail Method to be used
	 * Valid values: 'mail' or 'smtp'
	 * If 'smtp' is used, additional SMTP settings must be added to this class (see below)
	 * @var ENUM
	 * @default 'mail'
	 */
	public $method;

	/**
	 * If set to TRUE will send HTML formated message, otherwise Text
	 * @var BOOLEAN
	 * @default false
	 */
	public $is_html;

	/**
	 * Message Character Set
	 * Ussualy should be set to 'UTF-8'
	 * @var STRING
	 * @default 'ISO-8859-1'
	 */
	public $charset;

	//-- message parts

	/**
	 * Message Return Recipient Email Address
	 * Ex: 'return@my-email.ext'
	 * @var STRING
	 * @default ''
	 */
	public $from_return;

	/**
	 * Message From Recipient Name
	 * *Optional*
	 * Ex: 'Me'
	 * @var STRING
	 * @default ''
	 */
	public $namefrom;

	/**
	 * Message From Recipient Email Address
	 * Ex: 'me@my-email.ext'
	 * @var STRING
	 * @default ''
	 */
	public $from;

	/**
	 * Message To Recipient Email Address
	 * Ex: 'to@email.ext'
	 * @var STRING
	 * @default ''
	 */
	public $to;

	/**
	 * Message Cc Recipient(s) Email Address(es) as STRING or ARRAY
	 * *Optional*
	 * Ex: 'cc@email.ext' or [ 'cc1@email.ext', 'cc2@email3.ext', ... ]
	 * @var MIXED
	 * @default ''
	 */
	public $cc;

	/**
	 * Message Bcc Recipient Email Address
	 * *Optional*
	 * Ex: 'bcc@email101.ext'
	 * @var STRING
	 * @default ''
	 */
	public $bcc;

	/**
	 * Message Priority ; Can be (as standards): 1 = High ; 3 = Normal ; 5 = Low
	 * *Optional*
	 * @var ENUM
	 * @default 3
	 */
	public $priority;

	/**
	 * Message Subject ; Ex: 'This is the subject of the email Message'
	 * @var STRING
	 * @default ''
	 */
	public $subject;

	/**
	 * Message Body
	 * Ex(text): 'This is the body of the email Message\nAnd a new Line ...' $is_html should be leave as FALSE as default in this case
	 * Ex(html): 'This is the body of the email Message<br>And a new Line ...' -> $is_html must be set to TRUE if HTML body is sent
	 * For the case of sending HTML bodies you must assure mprogramatically that all HTML required resources as images, css, ... are embedded (the will not be embedded automatically)
	 * As an alternative tho this class which is low level, the functions SmartMailerUtils::send_email() / SmartMailerUtils::send_extended_email() may be used and they will automatically resolve the HTML resources embedding ...
	 * @var STRING
	 * @default ''
	 */
	public $body;

	/**
	 * Message Headers
	 * *Optional*
	 * If non-empty, each header line must be end as CRLF (\r\n)
	 * Ex: 'X-AntiAbuse: This header was added to track abuse, please include it with any abuse report'."\r\n".'X-AntiAbuse: Sender Address Domain - mydomain.ext'."\r\n"
	 * @var STRING
	 * @default ''
	 */
	public $headers;

	//-- smtp

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP HELO (server name that is allowed to send mails for this domain)
	 * Must be set to a real domain host that is valid to send emails for that address
	 * @var STRING
	 * @default '127.0.0.1'
	 */
	public $smtp_helo;

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP server host or ip
	 * @var STRING
	 * @default null
	 */
	public $smtp_server;

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP server port ; usually 25
	 * @var INTEGER+
	 * @default null
	 */
	public $smtp_port;

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP SSL Mode: '', 'ssl', 'sslv3', 'tls', 'starttls'
	 * If empty string will be set it will be not using SSL Mode
	 * @var ENUM
	 * @default null
	 */
	public $smtp_ssl;

	/**
	 * Apply only if send the email message using SMTP Method
	 * Relative Path to a SSL Certificate Authority File
	 * If SSL Mode is set this is optional, otherwise is not used
	 * Ex: store within smart-framework/etc/certificates ; specify as 'etc/certificates/ca.pem')
	 * IMPORTANT: in this case the 'etc/certificates/' directory must be protected with a .htaccess to avoid being public readable - the directory and any files within this directory ...
	 * @var STRING
	 * @default null
	 */
	public $smtp_cafile;

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP Connection Timeout in seconds
	 * @var INTEGER+
	 * @default 30
	 */
	public $smtp_timeout;

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP Authentication ; If set to TRUE will try a SMTP Auth (login) using non-empty and valid $smtp_user and $smtp_password
	 * @var BOOLEAN
	 * @default false
	 */
	public $smtp_login;

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP Authentication username ($smtp_login must be set to TRUE and a valid, non-empty $smtp_password must be provided)
	 * @var STRING
	 * @default null
	 */
	public $smtp_user;

	/**
	 * Apply only if send the email message using SMTP Method
	 * SMTP Authentication password ($smtp_login must be set to TRUE and a valid, non-empty $smtp_user must be provided)
	 * @var STRING
	 * @default null
	 */
	public $smtp_password;

	//-- store the encoded message

	/**
	 * Store the composed message by this class
	 * If used with $class->send() and not using the 2nd parameter may return the mime message composed in this variable
	 * @default ''
	 * @var STRING
	 */
	public $mime_message;

	//--

	//====================================================


	//=====================================================================================
	/**
	 * Class constructor
	 */
	public function __construct() {
		//--
		$this->cleanup();
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	/**
	 * Compose and/or Send an Email Message using MAIL or SMTP method, depend how this class properties are set
	 * If the 2nd parameter is empty as '', the composed message can be retrieved by reading the (string) $class->mime_message property after calling this function
	 * Can be a Text or HTML Message
	 * @param ENUM $do_send If set to 'yes' will do send the message using MAIL or SMTP method depend how this class is set ; If set to 'no' will just compose a message from provided parameters ; does not apply if $raw_message is supplied ...
	 * @param STRING $raw_message If this is provided will use the message from this string and will not compose the message using this class properties: $subject, $body, $headers, $from, $to, $cc, $bcc, ...
	 * @return STRING Empty string if SUCCESS or Error Message on FAILURE ; Failure may come as missing some required message compose properties and/or if send = 'yes' will also check the email send success ; NOTICE: if using MAIL method the mail send result may report or not an error ... ; With SMTP sending the message assure it is delivered to the destination SMTP server with or without an error
	 */
	public function send($do_send, $raw_message='') {

		//--
		if((string)$this->smtp_helo == '') { // fix
			$this->smtp_helo = '127.0.0.1';
		} //end if
		//--
		$tmp_explode_arr = (array) explode('@', (string)$this->from);
		$tmp_name = (string) trim($tmp_explode_arr[0]); // used for from name in the case it is empty
		$tmp_domain = (string) trim($tmp_explode_arr[1]); // used for message ID
		//--
		if((string)$this->namefrom != '') {
			$tmp_name = (string) SmartUnicode::deaccent_str((string)$this->namefrom);
		} else {
			$tmp_name = (string) ucwords((string)str_replace(array('.', '-', '_'), array(' ', ' ', ' '), (string)$tmp_name));
		} //end if
		//--
		$this->mime_message = ''; // init
		//--
		$this->mime_message .= 'Return-Path: '.'<'.$this->safe_header_str((string)$this->from_return).'>'."\r\n";
		$this->mime_message .= 'From: '.$this->safe_header_str((string)$tmp_name.' <'.$this->from.'>')."\r\n"; // [ucwords] is safe here as the name is ISO-8859-1 (1st part of email address)
		$this->mime_message .= 'Date: '.date('D, d M Y H:i:s O')."\r\n";
		$this->mime_message .= 'To: '.$this->safe_header_str((string)$this->to)."\r\n";
		//--
		if(is_array($this->cc)) {
			for($z=0; $z<Smart::array_size($this->cc); $z++) {
				if((string)$this->cc[$z] != '') {
					$this->mime_message .= 'Cc: '.$this->safe_header_str((string)$this->cc[$z])."\r\n";
				} //end if
			} //end for
		} elseif((string)$this->cc != '') {
			$this->mime_message .= 'Cc: '.$this->safe_header_str((string)$this->cc)."\r\n";
		} //end if
		if((string)$do_send != 'yes') {
			if((string)$this->bcc != '') {
				$this->mime_message .= 'BCc: '.$this->safe_header_str((string)$this->bcc)."\r\n";
			} //end if
		} //end if
		//--
		$this->mime_message .= 'Subject: '.$this->prepare_subject((string)$this->subject)."\r\n"; // this applies secure header
		//--
		switch((string)$this->priority) {
			case '1':
				$this->mime_message .= 'X-Priority: 1'."\r\n"; // high
				break;
			case '5':
				$this->mime_message .= 'X-Priority: 5'."\r\n"; // low
				break;
			case '3':
			default:
				$this->mime_message .= 'X-Priority: 3'."\r\n"; // normal
		} //end switch
		//--
		$this->mime_message .= 'X-Mailer: '.'Smart.Framework Mailer ('.$this->safe_header_str((string)SMART_FRAMEWORK_VERSION).')'."\r\n";
		$this->mime_message .= 'MIME-Version: 1.0 '.'(Smart.Framework Mime-Message v.2018.11.23)'."\r\n";
		$this->mime_message .= 'Message-Id: '.'<ID-'.$this->safe_header_str((string)Smart::uuid_10_seq().'-'.Smart::uuid_10_str().'-'.Smart::uuid_10_num().'@'.Smart::safe_validname($tmp_domain)).'>'."\r\n";
		//--
		if((string)trim((string)$this->headers) != '') {
			$this->mime_message .= (string) trim((string)$this->headers)."\r\n"; // all lines must end with: \r\n !!! IF THIS IS MALFORMED THERE IS A RISK TO BREAK THE MIME MESSAGE !!
		} //end if
		//--
		if((string)$raw_message == '') {
			//--
			if((string)$this->body != '') {
				//--
				if($this->composed !== true) { // prevent reattach body on re-send
					//--
					if($this->is_html == false) {
						$this->add_attachment($this->body, '', 'text/plain', 'inline');
					} else {
						if(defined('SMART_SOFTWARE_MAILSEND_SAFE_RULES')) {
							if(SMART_SOFTWARE_MAILSEND_SAFE_RULES === true) {
								$this->add_attachment('This is a MIME Message in HTML Format.', 'alternative-part.txt', 'text/plain', 'inline'); // antiSPAM needs an alternate body
							} //end if
						} //end if
						$this->add_attachment($this->body, '', 'text/html', 'inline');
					} //end else
					//--
					$this->composed = true;
					//--
				} //end if
				//--
			} //end if
			//--
			$this->mime_message .= (string) $this->build_multipart()."\r\n";
			//--
		} else {
			//-- RAW (get as is)
			$this->mime_message .= (string) $raw_message."\r\n";
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
				if(SmartUnicode::mailsend((string)$this->to, (string)$this->prepare_subject($this->subject), '', (string)$this->mime_message) != true) {
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
									if((string)$this->cc[$z] != '') {
										if($rcpt_cc == 1) {
											$rcpt_cc = $smtp->recipient($this->cc[$z]);
										} else {
											break;
										} //end if
									} //end if
								} //end for
							} elseif((string)$this->cc != '') {
								$rcpt_cc = $smtp->recipient((string)$this->cc);
							} //end if
							//--
							$rcpt_bcc = 1;
							if((string)$this->bcc != '') {
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
				if((string)$err != '') {
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
		return (string) $err;
		//--

	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	/**
	 * Add an attachment to the message
	 * @param STRING $att_body The body of the attachment
	 * @param STRING $name *Optional* A File Name for this attachment
	 * @param ENUM $ctype *Optional* The content type of the attachment: 'text/plain', 'text/html', 'message/rfc822', 'application/octet-stream'
	 * @param ENUM $disp *Optional* The content disposition of the attachment: 'inline' or 'attachment' ; if $ctype is 'application/octet-stream' can be only 'attachment'
	 * @param STRING $cid *Optional* The content ID if using with linked HTML reference parts of the message HTML body
	 * @param ENUM $embed *Optional* If set to 'yes' will embed this part as real attachment ; else will embedd it as message sub-part ; Chossing this depends pretty much on how the message is built, if there are linked sub-parts or not ... too much philosophy to explain all here, but all the documentation for MIME E-mail Encapsulation of Aggregate Parts is here: https://tools.ietf.org/html/rfc2110
	 * @return BOOLEAN On Success will return TRUE, on Failure will return FALSE
	 */
	public function add_attachment($att_body, $name='', $ctype='', $disp='attachment', $cid='', $embed='no') {
		//--
		if((string)$att_body == '') {
			return false;
		} //end if
		//--
		$cid = (string) $this->safe_header_str((string)$cid); // content ID
		//--
		switch((string)strtolower((string)$ctype)) {
			//-- text parts
			case 'text/plain':
			case 'text/html':
				//--
				if((string)$disp != 'attachment') {
					$disp = 'inline'; // default
				} //end if
				//--
				if($this->usealways_b64 === false) {
					$encode = 'quoted-printable'; // notice: this is a risk when mixing character sets that some email servers / antiSPAM filters would reject the messages
				} else {
					$encode = 'base64'; // notice: this is the preferred encoding so is better handling all as base64
				} //end if else
				//--
				if((string)$disp == 'inline') {
					$charset = (string) $this->safe_header_str((string)strtoupper((string)trim((string)$this->charset)));
				} //end if
				//--
				$name = (string) $this->safe_header_str((string)$name);
				$filename = '';
				//--
				$att_body = (string) SmartUnicode::fix_charset((string)$att_body);
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
				$filename = (string) $this->safe_header_str((string)$name);
				//--
				$att_body = (string) SmartUnicode::fix_charset((string)$att_body);
				//--
				break;
			//-- the rest ...
			default:
				//--
				$ctype = (string) $this->safe_header_str((string)trim((string)$ctype));
				if((string)$ctype == '') {
					$ctype = 'application/octet-stream';
				} //end if
				if(((string)$ctype == 'image') OR ((string)$ctype == 'image/svg+xml') OR ((string)$ctype == 'image/jpeg') OR ((string)$ctype == 'image/jpg') OR ((string)$ctype == 'image/png') OR ((string)$ctype == 'image/gif')) {
					if((string)$disp != 'inline') {
						$disp = 'attachment'; // default
					} //end if
				} else {
					$disp = 'attachment'; // force attachment
				} //end if else
				//--
				$encode = 'base64'; // force base64 in this case, can be binary data
				//--
				$filename = (string) $this->safe_header_str((string)$name);
				//--
				// DO NOT DO UNICODE FIX ON BINARY DATA
				//--
		} //end switch
		//--
		$arr_part = array(
			'ctype' 	=> (string) $ctype,
			'message' 	=> (string) $att_body,
			'charset' 	=> (string) $charset,
			'encode' 	=> (string) $encode,
			'disp' 		=> (string) $disp,
			'name' 		=> (string) $name,
			'filename'	=> (string) $filename,
			'cid'		=> (string) $cid
		);
		//--
		if((string)$embed == 'yes') { // if embed is 'yes' will pack this part as real attachment
			$this->atts[] = (array) $arr_part;
		} else {
			$this->parts[] = (array) $arr_part;
		} //end if else
		//--
		return true;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	/**
	 * Provide a helper to safe escape Mime (Email) Message Header Lines
	 * @param STRING $str The mime header line (MUST NOT Contain the line ending CRLF as \r\n) ; Line ending must be added after escaping the header line using this function
	 * @return STRING The safe escaped Mime Message Header Line
	 */
	public function safe_header_str($str) {
		//--
		return (string) Smart::normalize_spaces((string)SmartUnicode::fix_charset((string)$str));
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	/**
	 * Provide a helper to safe escape Mime (Email) Message Values
	 * @param STRING $str The mime value
	 * @return STRING The safe escaped Mime Message Value
	 */
	public function safe_value_str($str) {
		//--
		return (string) str_replace([' ', '"', '<', '>'], ['_', "'", '(', ')'], (string)$this->safe_header_str((string)$str));
		//--
	} //END FUNCTION
	//=====================================================================================


	//##### PRIVATES


	//=====================================================================================
	private function cleanup() {
		//--
		$this->parts = array();
		$this->atts = array();
		$this->composed = false;
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
		$subject = (string) $this->safe_header_str((string)$subject);
		//--
		$charset = (string) $this->safe_header_str((string)strtoupper((string)trim((string)$this->charset)));
		//--
		if((string)$charset != 'ISO-8859-1') {
			if($this->usealways_b64 === false) { // quote printable encoded subject
				$subject = (string) str_replace(["\r\n", "\r"], "\n", (string)quoted_printable_encode((string)$subject)); // encode QP
				$subject = (string) str_replace(' ', '_', (string)$subject); // {{{SYNC-QUOTED-PRINTABLE-FIX}}} Reverse Fix: as google mail subjects ; normally on QP the _ must be encoded as =5F ; because google mail use the _ instead of space in all emails subject, it is considered a major enforcement to support this replacement
				$subject = (string) '=?'.$charset.'?Q?'.$subject.'?=';
			} else { // prefer base64 encoded subjects
				$subject = (string) '=?'.$charset.'?B?'.base64_encode((string)$subject).'?=';
			} //end if else
		} //end if
		//--
		return (string) $subject;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// v.20191213, added content length and safe values {{{SYNC-MULTIPART-BUILD}}}
	private function build_message($part) {
		//--
		$part = (array) $part;
		//--
		$checksum = (string) sha1((string)$part['message']);
		//--
		if((string)$part['encode'] == '7bit') {
			// leave as is
		} elseif((string)$part['encode'] == 'quoted-printable') { // quoted printable encode specific for email body
			$part['message'] = (string) str_replace(["\r\n", "\r"], "\n", (string)quoted_printable_encode((string)$part['message'])); // see PHP Mailer ; no need for chunk split
	//		$part['message'] = (string) str_replace(' ', '-', (string)$part['message']); // {{{SYNC-QUOTED-PRINTABLE-FIX}}} ; this appear to be a fix just for email subject that must be not applied to email bodies
	//	} elseif((string)$part['encode'] == 'uuencode') { // currently this is not tested an may not work with modern antispam filters !!
	//		$part['message'] = (string) convert_uuencode((string)$part['message']); // uuencode
	//		$part['message'] = (string) trim((string)chunk_split((string)$part['message'], 76, "\r\n"));
		} else { // base64 encode
			$part['encode'] = 'base64'; // rewrite this for all other cases
			$part['message'] = (string) base64_encode((string)$part['message']); // encode b64
			$part['message'] = (string) trim((string)chunk_split((string)$part['message'], 76, "\r\n"));
		} //end if
		//--
		return 	'Content-Type: '.$this->safe_value_str($part['ctype']).($part['charset'] ? '; charset='.$this->safe_value_str($part['charset']) : '').($part['name'] ? '; name="'.$this->safe_value_str($part['name']).'"' : '')."\r\n".
				'Content-Transfer-Encoding: '.$this->safe_value_str(strtoupper((string)$part['encode']))."\r\n".
				($part['cid'] ? 'Content-ID: <'.$this->safe_value_str($part['cid']).'>'."\r\n" : '').
				'Content-Disposition: '.$this->safe_value_str($part['disp']).';'.($part['filename'] ? ' filename="'.$this->safe_value_str($part['filename']).'"' : '')."\r\n".
				'Content-Length: '.(int)strlen((string)$part['message'])."\r\n".
				'Content-Decoded-Checksum-SHA1: '.$this->safe_value_str($checksum)."\r\n".
				"\r\n".$part['message']; //."\r\n";
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// v.20191213 (multipart/mixed + multipart/related) {{{SYNC-MULTIPART-BUILD}}}
	private function build_multipart() {
		//--
		$timeduid = (string) Smart::uuid_10_seq(); // 10 chars, timed based, can repeat only once in 1000 years for the same millisecond
		$timedrid = (string) strrev((string)$timeduid);
		$entropy = (string) Smart::unique_entropy('mail/send'); // this generate a very random value
		$boundary 			= '_===-Mime.Part____.'.$timeduid.'_'.md5('@MimePart---#Boundary@'.$entropy).'_P_.-=_'; // 69 chars of 70 max
		$relatedboundary 	= '_-==-Mime.Related_.'.$timedrid.'_'.md5('@MimeRelated#Boundary@'.$entropy).'_R_.=-_'; // 69 chars of 70 max
		//--
		$multipart = '';
		//--
		$multipart .= 'Content-Type: multipart/mixed; boundary="'.$boundary.'"'."\r\n"."\r\n";
		$multipart .= 'This is a multi-part message in MIME format.'."\r\n"."\r\n";
		$multipart .= '--'.$boundary."\r\n";
		$multipart .= 'Content-Type: multipart/related; boundary="'.$relatedboundary.'"'."\r\n";
		//-- cid parts
		$multipart .= "\r\n";
		for($i=Smart::array_size($this->parts)-1; $i>=0; $i--) {
			$multipart .= '--'.$relatedboundary."\r\n";
			$multipart .= (string) $this->build_message($this->parts[$i]);
		} //end for
		$multipart .= "\r\n";
		$multipart .= '--'.$relatedboundary.'--'."\r\n";
		//-- attachments
		$multipart .= "\r\n";
		for($i=Smart::array_size($this->atts)-1; $i>=0; $i--) {
			$multipart .= '--'.$boundary."\r\n";
			$multipart .= (string) $this->build_message($this->atts[$i]);
		} //end for
		//--
		$multipart .= "\r\n";
		$multipart .= '--'.$boundary.'--'."\r\n";
		//--
		return (string) $multipart;
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
//$mail->add_attachment((string)$attachment, 'test.jpg', 'image/jpeg', 'attachment', '', 'yes'); // to embedd
//$mail->add_attachment((string)$attachment, 'test2.jpg', 'image/jpeg', 'inline', 'cid:12345'); // to display as inline contents ID
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
 * This class is for advanced usage.
 *
 * It just implements the communication protocol between PHP and a SMTP or ESMTP server.
 * It does and NOT implement the Mail Send Client or Message Composing.
 * For a bit more easy use on send emails use the SmartMailerSend class ...
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20191125
 * @package 	Plugins:Mailer
 *
 */
final class SmartMailerSmtpClient {

	// ->


	//===============================================
	//--
	/**
	 * @var INTEGER+
	 * @default 30
	 * socket timeout in seconds
	 */
	public $timeout = 30;
	//--
	/**
	 * @var BOOLEAN
	 * @default FALSE
	 * to debug or not
	 */
	public $debug = false;
	/**
	 * @var ENUM
	 * @default 1
	 * debug level (1 or 2)
	 */
	public $dbglevel = 1;
	//--
	/**
	 * @var STRING
	 * @default ''
	 * collects the error message(s)
	 */
	public $error = '';
	/**
	 * @var STRING
	 * @default ''
	 * if debug is enabled will collect the send log(s)
	 */
	public $log = '';
	//--
	//===============================================
	/**
	 * @var STRING
	 * @default ''
	 * Certificate Authority File Path (instead of using the global SMART_FRAMEWORK_SSL_CA_FILE it can use another CaFile)
	 */
	private $cafile = '';
	/**
	 * @var RESOURCE
	 * @default FALSE
	 * socket resource ID or FALSE if not connected
	 */
	private $socket = false;
	//--
	//===============================================


	//=====================================================================================
	/**
	 * SMTP Client Class constructor
	 */
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
	/**
	 * Set a SSL/TLS Certificate Authority File
	 * If not set but SMART_FRAMEWORK_SSL_CA_FILE is defined will use the SMART_FRAMEWORK_SSL_CA_FILE
	 * @param STRING $cafile Relative Path to a SSL Certificate Authority File (Ex: store within smart-framework/etc/certificates ; specify as 'etc/certificates/ca.pem') ; IMPORTANT: in this case the 'etc/certificates/' directory must be protected with a .htaccess to avoid being public readable - the directory and any files within this directory ...)
	 * @return VOID
	 */
	public function set_ssl_tls_ca_file($cafile) {
		//--
		$this->cafile = '';
		if(SmartFileSysUtils::check_if_safe_path((string)$cafile) == '1') {
			if(SmartFileSystem::is_type_file((string)$cafile)) {
				$this->cafile = (string) $cafile;
			} //end if
		} //end if
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	/**
	 * Will try to open a socket to the specified SMTP Server using the host/ip and port ; If a SSL option is selected will try to establish a SSL socket or fail
	 * @hints SMTP SUCCESS CODE: 220 ; SMTP FAILURE CODE: 421
	 * @param STRING $helo The SMTP HELO (server name that is allowed to send mails for this domain) ; Must be set to a real domain host that is valid to send emails for that address ; Ex: 'mail.mydomain.ext'
	 * @param STRING $server The SMTP server Hostname or IP address
	 * @param INTEGER+ $port *Optional* The SMTP Server Port ; Default is: 25
	 * @param ENUM $sslversion To connect using SSL mode this must be set to any of these accepted values: '', 'ssl', 'sslv3', 'tls', 'starttls' ; If empty string will be set will be not using SSL Mode
	 * @return INTEGER+ 1 on success, 0 on fail
	 */
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
		if((string)$sslversion != '') {
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
		if((string)$this->error != '') {
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
			if(stripos((string)$chk_crypto['stream_type'], '/ssl') === false) { // expects to have something like: tcp_socket/ssl
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
	/**
	 * Try a SMTP Authentication with a username and password
	 * Generally this must be run after running the SMTP hello() method
	 * Sends both user and pass to the SMTP server
	 * @hints SMTP SUCCESS CODES are: 334 OR 235 (final)
	 * @param STRING $username The SMTP authentication username
	 * @param STRING $pass The SMTP authentication password
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function login($username, $pass) {
		//--
		if($this->debug) {
			$this->log .= '[INF] Login to Mail Server (USER = '.$username.')'."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('AUTH LOGIN');
		if((string)$this->error != '') {
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
		if((string)$this->error != '') {
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
		if((string)$this->error != '') {
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
	/**
	 * Sends the QUIT command to the SMTP server
	 * Closes the communication socket after sending QUIT command
	 * Implemented as RFC 821: QUIT <CRLF>
	 * @hints SMTP SUCCESS CODE: 221 ; SMTP ERROR CODE: 500
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
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
	/**
	 * Ping the SMTP Server
	 * Sends the command NOOP to the SMTP server
	 * Implemented as RFC 821: NOOP <CRLF>
	 * @hints SMTP SUCCESS CODE: 250 ; SMTP ERROR CODE: 500, 421
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function noop() {
		//--
		if($this->debug) {
			$this->log .= '[INF] Ping the Mail Server // NOOP'."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('NOOP');
		if((string)$this->error != '') {
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
	/**
	 * Help for supported commands on the SMTP Server
	 * Sends the command HELP to the SMTP server.
	 * Implemented as RFC 821: HELP [ <SP> <string> ] <CRLF>
	 * @hints SMTP SUCCESS CODE: 211, 214 ; SMTP ERROR CODE: 500, 501, 502, 504, 421
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function help() {
		//--
		if($this->debug) {
			$this->log .= '[INF] Ask Help from Mail Server'."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('HELP');
		if((string)$this->error != '') {
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
	/**
	 * Sends the RSET command to the SMTP Server to abort any transaction that is currently in progress
	 * Implemented as RFC 821: RSET <CRLF>
	 * @hints SMTP SUCCESS CODE: 250 ; SMTP ERROR CODE: 500, 501, 504, 421
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function reset() {
		//--
		if($this->debug) {
			$this->log .= '[INF] Reset the Connection to Mail Server'."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('RSET');
		if((string)$this->error != '') {
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
	/**
	 * Sends the EHLO and/or HELO command to the SMTP Server
	 * First will try the extended SMTP feature by sending EHLO. If EHLO is not successful will try HELO
	 * This makes sure that the client and the server are in the same known state.
	 * Implemented as RFC 821: EHLO <SP> <domain> <CRLF> / HELO <SP> <domain> <CRLF>
	 * @hints SMTP SUCCESS CODE: 250 ; SMTP ERROR CODE: 500, 501, 504, 421
	 * @return INTEGER+ 1 on Success (if any of EHLO/HELO is successful) ; 0 on Error (if both EHLO and HELO fail)
	 */
	public function hello($hostname) {
		//--
		if($this->debug) {
			$this->log .= '[INF] Sending EHLO / HELO to Mail Server !'."\n";
		} //end if
		//--
		if((string)$this->error != '') {
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
	/**
	 * Verifies if the given recipient name is recognized by the SMTP server
	 * Implemented as RFC 821: VRFY <SP> <string> <CRLF>
	 * @hints SMTP SUCCESS CODE: 250, 251 ; SMTP FAIL CODE: 550, 551, 553 ; SMTP ERROR CODE: 500, 501, 502, 421
	 * @param STRING $name The recipient name to be verified ; Ex: name@domain.ext
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function verify($name) {
		//--
		if($this->debug) {
			$this->log .= '[INF] Verify is sent on Mail Server for: '.$name."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('VRFY '.$name);
		if((string)$this->error != '') {
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
	/**
	 * Expand takes the recipient name and asks the server to list all the recipients who are members of the _list_
	 * SMTP Expand will return back an empty string for error and the reply with reply lines ended by [CRLF]
	 * Each value in the array returned has the format of: [ <full-name> <sp> ] <path>
	 * The definition of <path> is defined in RFC 821
	 * Implemented as RFC 821: EXPN <SP> <string> <CRLF>
	 * @hints SMTP SUCCESS CODE: 250 ; SMTP FAIL CODE: 550 ; SMTP ERROR CODE: 500, 501, 502, 504, 421
	 * @param STRING $name The recipient name to be expanded ; Ex: mail-list@domain.ext
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function expand($name) {
		//--
		if($this->debug) {
			$this->log .= '[INF] Expand is sent on Mail Server for: '.$name."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return '';
		} //end if
		//--
		$reply = $this->send_cmd('EXPN '.$name);
		if((string)$this->error != '') {
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
	/**
	 * Starts a send mail (message) transaction originating from the email address specified in $from recipient name on the SMTP Server
	 * If this command is successful then the mail transaction is started and then one or more Recipient commands may be called followed by a Data command
	 * Implemented as RFC 821: MAIL <SP> FROM:<reverse-path> <CRLF>
	 * @hints SMTP SUCCESS CODE: 250 ; SMTP FAIL CODE: 552, 451, 452 ; SMTP ERROR CODE: 500, 501, 421
	 * @param STRING $from The originating email recipient ; Ex: me@my-email.ext
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function mail($from) {
		//--
		if($this->debug) {
			$this->log .= '[INF] Mail command is sent on Mail Server for: '.$from."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('MAIL FROM:<'.$from.'>');
		if((string)$this->error != '') {
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
	/**
	 * Sends the command RCPT to the SMTP server with the TO: argument of $to.
	 * Implemented as RFC 821: RCPT <SP> TO:<forward-path> <CRLF>
	 * @hints SMTP SUCCESS CODE: 250, 251 ; SMTP FAIL CODE: 550, 551, 552, 553, 450, 451, 452 ; SMTP ERROR CODE: 500, 501, 503, 421
	 * @param STRING $to The destination email recipient (or email list) ; Ex: destination@your-email.ext
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function recipient($to) {
		//--
		if($this->debug) {
			$this->log .= '[INF] Recipient command is sent on Mail Server for: '.$to."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('RCPT TO:<'.$to.'>');
		if((string)$this->error != '') {
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
	/**
	 * Initiates a data command on the SMTP Server and sends the $msg_data to the server finalizing the mail transaction started with mail($from) and followed by recipient($to)
	 * The $msg_data as data is the message that is to be send together with the message headers
	 * Each header line (if any) needs to be on a single line followed by a <CRLF>
	 * After headers the mail message body have to be appended and being separated by and additional <CRLF>
	 * Implemented as RFC 821: DATA <CRLF>
	 * @hints [ Intermediate codes for {data} <CRLF>.<CRLF> are: SMTP INTERMEDIATE CODE: 354 ; SMTP CODE SUCCESS: 250 ; SMTP CODE FAILURE: 552,554,451,452 ] ; [ Final Transaction codes are: SMTP SUCCESS CODE: 250 ; SMTP FAIL CODE: 451, 554 ; SMTP ERROR CODE: 500, 501, 503, 421 ]
	 * @param STRING $msg_data The message data (headers + body) to be sent to the SMTP Server
	 * @return INTEGER+ 1 on Success or 0 on Error
	 */
	public function data_send($msg_data) {
		//--
		if($this->debug) {
			$this->log .= '[INF] Data-Send command is sent on Mail Server'."\n";
		} //end if
		//--
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('DATA');
		if((string)$this->error != '') {
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
		$field = (string) substr((string)$lines[0], 0, strpos((string)$lines[0], ':'));
		$in_headers = false;
		//--
		if(((string)$field != '') AND (strpos((string)$field, ' ') === false)) {
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
			while((int)strlen((string)$line) > (int)$max_line_length) {
				//--
				$pos = strrpos((string)substr((string)$line, 0, (int)$max_line_length), ' '); // here we need reverse strpos
				$lines_out[] = (string) substr((string)$line, 0, $pos);
				$line = (string) substr((string)$line, ($pos + 1));
				//-- if we are processing headers we need to add a LWSP-char to the front of the new line rfc 822 on long msg headers
				if($in_headers) {
					$line = "\t".$line;
				} //end if
				//--
			} //end while
			//--
			$lines_out[] = $line;
			//-- now send the lines to the server
			//while(list($key,$line_out) = @each($lines_out)) { // FIX to be compatible with the upcoming PHP 7
			foreach($lines_out as $key => $line_out) { // Fix: the above is deprecated as of PHP 7.2
				//--
				if((string)$line_out != '') {
					if((string)substr((string)$line_out, 0, 1) == '.') {
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
		if((string)$this->error != '') {
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
		if((string)$this->error != '') {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('STARTTLS');
		if((string)$this->error != '') {
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
	// [PRIVATE]
	// read the server code (1st 3 chars ; Ex: 220 = OK)
	private function answer_code($reply) {
		//--
		return (string) trim((string)substr((string)trim((string)$reply), 0, 3));
		//--
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
			if((string)substr((string)$str, 3, 1) == ' ') {
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
		if((string)$cmd == '') {
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