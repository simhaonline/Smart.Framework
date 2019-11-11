<?php
// [LIB - Smart.Framework / Plugins / Mail Get (IMAP4 and POP3 Client)]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Mail Get (SSL/TLS): IMAP4 / POP3
// DEPENDS:
//	* Smart::
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== START CLASS
//=====================================================================================


/**
 * Class: SmartMailerImap4Client - provides an IMAP4 Mail Client with SSL/TLS support.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20191111
 * @package 	Plugins:Mailer
 *
 */
final class SmartMailerImap4Client {

	// ->


	/**
	 * @var INT
	 * socket read buffer (default is 1024)
	 */
	public $buffer = 1024;

	/**
	 * @var INT
	 * socket timeout in seconds (default is 30)
	 */
	public $timeout = 30;

	/**
	 * @var BOOLEAN
	 * debug on/off (default is FALSE)
	 */
	public $debug = false;

	/**
	 * @var STRING
	 * the error message(s) will be collected here
	 * do not SET a value here, but just GET the result
	 */
	public $error = '';

	/**
	 * @var STRING
	 * the operations log (only if debug is enabled)
	 * do not SET a value here, but just GET the result
	 */
	public $log = '';

	//--
	private $socket = false; 	// socket resource ID
	private $tag = '';			// unique ID Tag
	private $username = '';		// the username
	private $authmec = ''; 		// the auth mechanism
	//--
	private $crr_mbox = '';		// current selected mailbox
	private $crr_uiv = 0;		// current UIVALIDITY for the selected mailbox folder
	private $inf_count = 0;		// store mailbox count
	private $inf_recent = 0;	// store mailbox recent number
	private $inf_size = 0;		// store mailbox total size
	//--
	private $cafile = '';		// Certificate Authority File (instead of using the global SMART_FRAMEWORK_SSL_CA_FILE can use a private cafile
	//--


	//=====================================================================================
	// [INIT]
	public function __construct($buffer=0) { // IMAP4
		//--
		$this->socket = false;
		$this->tag = '';
		$this->crr_mbox = '';
		//--
		$this->username = '';
		$this->authmec = '';
		//--
		if($buffer > 0) {
			$this->buffer = (int) $buffer;
		} //end if
		if($this->buffer < 512) {
			$this->buffer = 512;
		} elseif($this->buffer > 8192) {
			$this->buffer = 8192;
		} //end if else
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
	public function connect($server, $port=143, $sslversion='') { // IMAP4

		//-- inits
		$this->socket = false;
		$this->tag = '';
		$this->crr_mbox = '';
		//--

		//-- checks
		$server = trim($server);
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
		//--
		if((string)$sslversion != '') {
			//--
			if(!function_exists('openssl_open')) {
				$this->error = '[ERR] PHP OpenSSL Extension is required to perform SSL requests !';
				return 0;
			} //end if
			//--
			switch(strtolower($sslversion)) {
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
		if((string)$protocol != '') {
			//--
			$cafile = '';
			if((string)$this->cafile != '') {
				$cafile = (string) $this->cafile;
			} elseif(defined('SMART_FRAMEWORK_SSL_CA_FILE')) {
				if((string)SMART_FRAMEWORK_SSL_CA_FILE != '') {
					$cafile = (string) SMART_FRAMEWORK_SSL_CA_FILE;
				} //end if
			} //end if
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

		//-- avoid connect normally if SSL/TLS was explicit required
		$chk_crypto = (array) @stream_get_meta_data($this->socket);
		if((string)$protocol != '') {
			if(!SmartUnicode::str_icontains($chk_crypto['stream_type'], '/ssl')) { // will return something like: tcp_socket/ssl
				//--
				$this->error = '[ERR] Connection CRYPTO CHECK Failed ...'."\n";
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
		$reply = $this->get_answer_line();
		//--
		$reply = $this->strip_clf($reply);
		//--
		if(substr($reply, 0, 5) != '* OK ') {
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
		if($this->debug) {
			$this->log .= '[REPLY] \''.$reply.'\''."\n";
		} //end if
		//--

		//--
		return 1;
		//--

	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// ping server
	public function noop() { // IMAP4
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
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
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
	// reset server connection (includding all messages marked to be deleted)
	public function reset() { // IMAP4
		//--
		if($this->debug) {
			$this->log .= '[INF] Reset the Connection to Mail Server'."\n";
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// close connection
	public function quit() { // IMAP4
		//--
		if($this->debug) {
			$this->log .= '[INF] Sending QUIT to Mail Server !'."\n";
		} //end if
		//--
		if(!$this->socket) {
			$this->error = '[ERR] IMAP4 Connection cannot QUIT, it appears is not opened !';
			return 0;
		} //end if
		//--
		//$this->send_cmd('EXPUNGE'); // delete messages marked as Deleted (overall)
		$this->send_cmd('CLOSE'); // delete messages marked as Deleted (from selected mailbox only)
		$this->send_cmd('UNSELECT'); // some servers req. this (dovecot to avoid throw CLIENTBUG warnings)
		//--
		$reply = $this->send_cmd('LOGOUT'); // imap4
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 Logout Failed ['.$reply.']';
		} //end if else
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
	// Sends both user and pass. Returns 1 on Success and 0 on Error
	public function login($username, $pass, $mode='') { // IMAP4
		//--
		$this->tag = 'smart77'.strtolower(Smart::uuid_10_seq()).'7framework';
		$this->username = (string) $username;
		$this->authmec = 'PLAIN';
		//--
		if($this->debug) {
			$this->log .= '[INF] Login to Mail Server (TAG='.$this->tag.' ; MODE='.$mode.' ; USER='.$username.')'."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//-- normal login
		if($this->debug) {
			$this->log .= '[INF] Login Method: Normal'."\n";
		} //end if
		$this->send_cmd('CAPABILITY');
		//--
		if((string)$mode == 'login') {
			$reply = $this->send_cmd('LOGIN '.$username.' '.$pass);
		} else {
			$reply = $this->send_cmd('AUTHENTICATE '.$this->authmec.' '.(string)base64_encode("\0".$username."\0".$pass));
		} //end if else
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 User or Password Failed ['.$reply.']';
			return 0;
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	public function select_mailbox($mbox_name, $allow_create=false) { // IMAP4
		//--
		if($this->debug) {
			$this->log .= '[INF] Select MailBox // '.$mbox_name."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		if($allow_create) {
			$this->send_cmd('CREATE "'.$this->mailbox_escape($mbox_name).'"'); // we do not check error now but on the next command
		} //end if
		//--
		$reply = $this->send_cmd('SELECT "'.$this->mailbox_escape($mbox_name).'"');
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] Select MailBox ('.$mbox_name.') Failed :: '.$test.' // '.$reply;
			return 0;
		} //end if
		//--
		$this->crr_mbox = (string) $mbox_name;
		//--
		$tmp_arr = (array) explode('* OK [UIDVALIDITY ', (string)$reply);
		$tmp_uiv = $tmp_arr['1'];
		$tmp_arr = array();
		$tmp_arr = (array) explode('] UIDs', (string)$tmp_uiv);
		$this->crr_uiv = trim($tmp_arr[0]);
		//--
		$size = 0; // we can't determine in IMAP except situation below
		//--
		$count = 0;
		$recent = 0;
		$tmp_arr = (array) explode("\r\n", (string)$reply);
		for($i=0; $i<count($tmp_arr); $i++) {
			$tmp_arr[$i] = trim($tmp_arr[$i]);
			if(strlen($tmp_arr[$i]) > 0) {
				if(($count == 0) AND (strpos($tmp_arr[$i], ' EXISTS') !== false) AND (substr($tmp_arr[$i], 0, 2) == '* ')) {
					$tmp_x_arr = array();
					$tmp_txt = $tmp_arr[$i];
					$tmp_x_arr = (array) explode('* ', (string)$tmp_txt);
					$tmp_txt = trim($tmp_x_arr[1]);
					$tmp_x_arr = array();
					$tmp_x_arr = (array) explode(' EXISTS', (string)$tmp_txt);
					$tmp_txt = trim($tmp_x_arr[0]);
					$count = (int) $tmp_txt;
				} elseif(($recent == 0) AND (strpos($tmp_arr[$i], ' RECENT') !== false) AND (substr($tmp_arr[$i], 0, 2) == '* ')) {
					$tmp_x_arr = array();
					$tmp_txt = $tmp_arr[$i];
					$tmp_x_arr = (array) explode('* ', (string)$tmp_txt);
					$tmp_txt = trim($tmp_x_arr[1]);
					$tmp_x_arr = array();
					$tmp_x_arr = (array) explode(' RECENT', (string)$tmp_txt);
					$tmp_txt = trim($tmp_x_arr[0]);
					$recent = (int) $tmp_txt;
				} //end if
			} //end if
		} //end for
		$tmp_arr = array();
		//--
		$reply = $this->send_cmd('STATUS "'.$this->mailbox_escape($mbox_name).'" (MESSAGES UIDNEXT SIZE)'); // example: '* STATUS Inbox (MESSAGES 8 UIDNEXT 12345 SIZE 45678)';
		$test = $this->is_ok($reply);
		if((string)$test == 'ok') {
			$tmp_arr = (array) explode(' SIZE ', (string)$reply);
			$count = (int) rtrim((string)$tmp_arr[1], ') ');
			if($count < 0) {
				$count = 0;
			} //end if
			$tmp_arr = array();
		} //end if
		//--
		$this->inf_count = $count;
		$this->inf_recent = $recent;
		$this->inf_size = 0; // imap size is not reported except if server have STATUS=SIZE Extension, so try below
		//--
		$this->send_cmd('CHECK'); // maintenance over mailbox
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	public function parse_uidls($y_list) { // this is just for IMAP4, n/a for POP3

		// ID[SPACE]UID\n

		//--
		$y_list = (string) trim((string)str_replace(array("\r\n", "\r", "\t"), array("\n", "\n", ' '), (string)$y_list));
		//--
		$uidls = (array) explode("\n", (string)$y_list);
		//--

		//--
		$new_uidls = array();
		//--
		if(Smart::array_size($uidls) > 0) {
			//--
			foreach($uidls as $key => $val) {
				//--
				$val = (string) trim((string)$val);
				//--
				$tmp_arr = array();
				//--
				if((string)$val != '') {
					//--
					$tmp_arr = (array) explode(' ', (string)$val);
					$tmp_arr[0] = (string) trim((string)$tmp_arr[0]);
					$tmp_arr[1] = (string) trim((string)$tmp_arr[1]);
					//--
					if(preg_match('/^([0-9])+$/', (string)$tmp_arr[0])) {
						//--
						if((string)$tmp_arr[1] != '') {
							$new_uidls[(string)$tmp_arr[0]] = (string) $tmp_arr[1];
						} //end if
						//--
					} //end if
					//--
				} //end if
				//--
			} //end foreach
			//--
		} //end if
		//--
		$uidls = array();
		//--

		//--
		return (array) $new_uidls;
		//--

	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	public function get_metadata() { // IMAP4
		//--
		$uivalidity = $this->crr_uiv;
		//--
		return array('uivalidity' => $uivalidity, 'count' => $this->inf_count, 'recent' => $this->inf_recent, 'size' => $this->inf_size);
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// returns array() on Success and '' on Error
	public function count() { // IMAP4
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 Count // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			$this->log .= '[INF] Reading the Messages Count and Size for MailBox ('.$this->crr_mbox.') ...'."\n";
		} //end if
		//--
		$size = $this->inf_size;
		$count = $this->inf_count;
		$recent = $this->inf_recent;
		//--
		if($this->debug) {
			$this->log .= '[INF] Messages Count [Size='.$size.' ; Count='.$count.' ; Recent='.$recent.']'."\n";
		} //end if
		//--
		return array('size' => $size, 'count' => $count, 'recent' => $recent);
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return the UID for the selected message or empty on error
	public function uid($msg_num='') { // IMAP4
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 UID // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			if(strlen($msg_num) > 0) {
				$this->log .= '[INF] IMAP4 UID Message Number: '.$msg_num."\n";
			} else {
				$this->log .= '[INF] IMAP4 UID for all Messages'."\n";
			} //end if else
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		if(strlen($msg_num) > 0) {
			$reply = $this->send_cmd('UID SEARCH '.$msg_num);
		} else {
			$reply = $this->send_cmd('UID SEARCH ALL');
		} //end if else
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 UID Message(s) Failed ['.$reply.']';
			return '';
		} //end if
		//--
		if(strlen($msg_num) > 0) {
			//--
			$uid = '';
			$tmp_arr = (array) explode("\r\n", (string)$reply);
			for($i=0; $i<count($tmp_arr); $i++) {
				$tmp_line = trim($tmp_arr[$i]);
				if(substr($tmp_line, 0, 9) == '* SEARCH ') {
					$uid = 'IMAP4-UIV-'.$this->crr_uiv.'-UID-'.trim(substr($tmp_line, 9));
					break;
				} //end if
			} //end for
			//--
		} else {
			//--
			$uid = '';
			$tmp_arr = (array) explode("\r\n", (string)$reply);
			for($i=0; $i<count($tmp_arr); $i++) {
				$tmp_line = trim($tmp_arr[$i]);
				if(substr($tmp_line, 0, 9) == '* SEARCH ') {
					$uid = trim(substr($tmp_line, 9));
				} //end if
			} //end for
			//--
			if(strlen($uid) > 0) {
				$tmp_arr = (array) explode(' ', (string)$uid);
				$uid = '';
				for($i=0; $i<count($tmp_arr); $i++) {
					$tmp_line = trim($tmp_arr[$i]);
					if(strlen($tmp_line) > 0) { // keep sense with POP3 format which is: ID[SPACE]UID\n
						$uid .= (string) $i.' '.'IMAP4-UIV-'.$this->crr_uiv.'-UID-'.$tmp_line."\n";
					} //end if
				} //end for
			} else {
				$tmp_arr = array();
			} //end if
			//--
		} //end if
		//--
		if($this->debug) {
			if(strlen($msg_num) > 0) {
				$this->log .= '[INF] UID For Message #'.$msg_num.' is: ['.$uid.']'."\n";
			} else {
				$this->log .= '[INF] UID For Messages are: [(LIST)]'."\n";
			} //end if else
		} //end if
		//--
		return $uid;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return the size for the selected message or empty on error
	public function size($msg_num) { // IMAP4
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 Size // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			$this->log .= '[INF] IMAP4 Size Message Number: '.$msg_num."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$reply = $this->send_cmd('FETCH '.$msg_num.' RFC822.SIZE');
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 Size Message Failed ['.$reply.']';
			return '';
		} //end if
		//--
		$tmp_arr = (array) explode('RFC822.SIZE ', (string)$reply); // The answer is like * {MSGNUM} FETCH (RFC822.SIZE 3663)
		$tmp_size = trim($tmp_arr[1]);
		$tmp_arr = array();
		$tmp_arr = (array) explode(')', (string)$tmp_size);
		$size = trim($tmp_arr[0]);
		//--
		if($this->debug) {
			$this->log .= '[INF] Size For Message #'.$msg_num.' is: ['.$size.']'."\n";
		} //end if
		//--
		return $size;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// delete a message from server
	public function delete($msg_num, $by_uid=false) { // IMAP4
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 Delete // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			if($by_uid) {
				$this->log .= '[INF] IMAP4 Delete Message UID: '.$msg_num."\n";
			} else {
				$this->log .= '[INF] IMAP4 Delete Message Number: '.$msg_num."\n";
			} //end if else
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		if($by_uid) {
			$reply = $this->send_cmd('UID STORE '.$msg_num.' +FLAGS.SILENT (\Deleted)');
		} else {
			$reply = $this->send_cmd('STORE '.$msg_num.' +FLAGS.SILENT (\Deleted)');
		} //end if else
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 Delete Message Failed ['.$reply.']';
			return 0;
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return STRING: the message header or empty on error
	public function header($msg_num, $read_lines=5) { // IMAP4
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 Header // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			$this->log .= '[INF] IMAP4 Header Message Number: '.$msg_num.' // Lines: n/a'."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$reply = $this->send_cmd('FETCH '.$msg_num.' BODY.PEEK[HEADER]');
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 Header Message Failed ['.$reply.']';
			return '';
		} //end if
		//--
		$header_out = '';
		//--
		$mark_ok = ')'."\r\n".$this->tag.' OK ';
		if(strpos($reply, $mark_ok) !== false) {
			//--
			$tmp_repl_arr = (array) explode((string)$mark_ok, (string)$reply);
			$tmp_repl_txt = $tmp_repl_arr[0];
			$tmp_repl_arr = (array) explode("\r\n", trim((string)$tmp_repl_txt));
			$tmp_repl_arr[0] = ''; // the 1st line is the IMAP Answer
			//--
			if($read_lines <= 0) {
				//--
				$header_out = trim((string)implode("\r\n", (array)$tmp_repl_arr));
				//--
			} else {
				//--
				$tmp_max = count($tmp_repl_arr);
				//--
				if($tmp_max > ($read_lines + 1)) {
					$tmp_max = $read_lines + 1;
				} //end if
				//--
				for($i=1; $i<$tmp_max; $i++) { // we start at 1 because the 1st line is the IMAP Answer
					$header_out .= $tmp_repl_arr[$i]."\r\n";
				} //end for
				//--
			} //end if else
			//--
		} //end if
		//--
		return $header_out;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return STRING: the full message or empty on error
	public function read($msg_num, $by_uid=false) { // IMAP4
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 Read // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			if($by_uid) {
				$this->log .= '[INF] IMAP4 Read Message by UID: '.$msg_num."\n";
			} else {
				$this->log .= '[INF] IMAP4 Read Message Number: '.$msg_num."\n";
			} //end if
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		if($by_uid) {
			$reply = $this->send_cmd('UID FETCH '.$msg_num.' BODY[]');
		} else {
			$reply = $this->send_cmd('FETCH '.$msg_num.' BODY[]');
		} //end if else
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 Read Message Failed ['.$reply.']';
			return '';
		} //end if
		//--
		$msg_out = '';
		//--
		$mark_ok = ')'."\r\n".$this->tag.' OK ';
		if(strpos($reply, $mark_ok) !== false) {
			//--
			$tmp_repl_arr = (array) explode((string)$mark_ok, (string)$reply);
			$tmp_repl_txt = $tmp_repl_arr[0];
			$tmp_repl_arr = (array) explode("\r\n", trim((string)$tmp_repl_txt));
			$tmp_repl_arr[0] = ''; // the 1st line is the IMAP Answer
			//--
			for($i=1; $i<count($tmp_repl_arr); $i++) { // we start at 1 because the 1st line is the IMAP Answer
				$msg_out .= $tmp_repl_arr[$i]."\r\n";
			} //end for
			//--
		} //end if
		//--
		return $msg_out;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return 1/0: COPY Message to another MBox by NUM/UID
	public function copy($msg_uid, $dest_mbox, $by_uid=false) { // IMAP4
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 Append // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			if($by_uid) {
				$this->log .= '[INF] Copy Message by UID ('.$msg_uid.') to: ('.$dest_mbox.')'."\n";
			} else {
				$this->log .= '[INF] Copy Message by NUM ('.$msg_uid.') to: ('.$dest_mbox.')'."\n";
			} //end if else
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//-- UID COPY {uid} {mbox} is to copy by UID ; COPY {num} {mbox} is to copy by number
		if($by_uid) {
			$reply = $this->send_cmd('UID COPY '.$msg_uid.' "'.$this->mailbox_escape($dest_mbox).'"');
		} else {
			$reply = $this->send_cmd('COPY '.$msg_uid.' "'.$this->mailbox_escape($dest_mbox).'"');
		} //end if else
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] UID Copy Failed :: '.$test.' // '.$reply;
			return 0;
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return STRING: UID of uploaded (appended) message or empty on error
	public function append($message) {
		//--
		if(strlen($this->crr_mbox) <= 0) {
			$this->error = '[ERR] IMAP4 Append // No MailBox Selected ...';
		} //end if
		//--
		if($this->debug) {
			$this->log .= '[INF] Appending a Message to MailBox ('.$this->crr_mbox.') ...'."\n";
		} //end if
		//--
		$checksum = sha1($message);
		$message = str_replace("\r", '', $message);
		$message = str_replace("\n", "\r\n", $message);
		$len = strlen($message);
		//--
		if($len <= 0) {
			$this->error = '[ERR] IMAP4 Append // Message is Empty ...';
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		if(!fputs($this->socket, $this->tag.' APPEND "'.$this->mailbox_escape($this->crr_mbox).'" (\\Seen) {'.$len.'}'."\r\n")) {
			$this->error = '[ERR] IMAP4 Append CMD // Failed ...';
			return '';
		} //end if
		//--
		if(!@fputs($this->socket, $message."\r\n")) {
			$this->error = '[ERR] IMAP4 Append MSG // Failed ...';
			return '';
		} //end if
		//--
		$data = '';
		//--
		while(1) {
			//--
			$line = $this->get_answer_line();
			//--
			$data .= $line;
			//--
			if(substr(trim($line), 0, (strlen($this->tag) + 1)) == $this->tag.' ') {
				break;
			} //end if
			//--
		} //end while
		//--
		$this->log .= trim($data)."\n";
		//--
		$reply = $data;
		$reply = $this->strip_clf($reply);
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] IMAP4 Count Messages Failed ['.$reply.']';
			return '';
		} //end if
		//--
		$tmp_arr_uid = (array) explode('[APPENDUID ', (string)$data);
		$tmp_uid = trim($tmp_arr_uid[1]);
		$tmp_arr_uid = array();
		$tmp_arr_uid = (array) explode('] Append', (string)$tmp_uid);
		$tmp_uid = trim($tmp_arr_uid[0]);
		$tmp_arr_uid = array();
		$tmp_arr_uid = (array) explode(' ', (string)$tmp_uid);
		if(trim($tmp_arr_uid[0]) == $this->crr_uiv) {
			$tmp_uid = 'IMAP4-UIV-'.trim($this->crr_uiv).'-UID-'.trim($tmp_arr_uid[1]);
		} else {
			$tmp_uid = 'IMAP4-UIV-'.trim($this->crr_uiv).'-UIDSHA1-'.trim($checksum);
		} //end if else
		//--
		$this->log .= '[INF] Appended Completed and the UID is: '.$tmp_uid."\n";
		//--
		return $tmp_uid;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// :: PRIVATES ::
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// escapes a mailbox name (get from roundcube class)
	private function mailbox_escape($string) { // escape the name of a mailbox
		return strtr($string, array('"'=>'\\"', '\\' => '\\\\'));
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// Strips \r\n from server responses
	private function strip_clf($text) { // IMAP4
		//--
		return trim($text);
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// Return 'ok' on +OK or 'error#' on -ERR
	private function is_ok($reply) { // IMAP4
		//--
		$ok = 'ERROR: Reply is not OK !';
		//--
		if(strlen($reply) <= 0)	{
			$ok = 'ERROR: Reply is Empty !';
		} //end if
		//--
		$reply = trim($reply);
		//--
		$arr_lines = (array) explode("\r\n", (string)$reply);
		//--
		for($i=0; $i<count($arr_lines); $i++) {
			//--
			$tmp_line = trim($arr_lines[$i]);
			//--
			if(strlen($tmp_line) > 0) {
				if(substr($tmp_line, 0, 1) != '*') {
					if(substr($tmp_line, 0, strlen($this->tag)+4) == $this->tag.' OK ') {
						$ok = 'ok';
					} //end if
				} //end if
			} //end if
			//--
		} //end for
		//--
		return $ok;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// Sends a user defined command string to the IMAP server and returns the results.
	// Useful for non-compliant or custom IMAP servers.
	// Do NOT include the \r\n as part of your command string - it will be appended automatically.
	// The return value is a standard fgets() call, which will read up to buffer bytes of data,
	// until it encounters a new line, or EOF, whichever happens first.
	// This method works best if $cmd responds with only one line of data.
	private function send_cmd($cmd) { // IMAP4
		//--
		if(!$this->socket) {
			$this->error = '[ERR] IMAP4 Send Command: No connection to server // '.$cmd;
			return '';
		} //end if
		//--
		if(strlen($cmd) <= 0) {
			$this->error = '[ERR] IMAP4 Send Command: Empty command to send !';
			return '';
		} //end if
		//--
		$original_cmd = (string) $cmd;
		$cmd = $this->tag.' '.$cmd;
		//--
		if(!@fputs($this->socket, $cmd."\r\n")) {
			$this->error = '[ERR] IMAP4 Send Command: FAILED !';
			return '';
		} //end if
		//--
		$reply = $this->get_answer_data();
		$reply = $this->strip_clf($reply);
		//--
		if($this->debug) {
			//--
			if(substr(trim($original_cmd), 0, 6) == 'LOGIN ') {
				$tmp_cmd = $this->tag.' LOGIN '.$this->username.' *****'; // hide the password protection
			} elseif(substr(trim($original_cmd), 0, 13) == 'AUTHENTICATE ') {
				$tmp_cmd = $this->tag.' AUTHENTICATE '.$this->authmec.' ['.$this->username.':*****]'; // hide the password protection
			} else {
				$tmp_cmd = $cmd;
			} //end if else
			//--
			$this->log .= '[INF] IMAP4 Send Command ['.$tmp_cmd.']'."\n".'[REPLY]: \''.$reply.'\''."\n";
			//--
		} //end if
		//--
		return $reply;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// retrieve one response line from server
	private function get_answer_line() { // IMAP4
		//--
		if(!$this->socket) {
			$this->error = '[ERR] IMAP4 Get Answer: No connection to server // '.$cmd;
			return '';
		} //end if
		//--
		$line = '';
		//--
		while(!feof($this->socket)) {
			//--
			$line .= fgets($this->socket, $this->buffer);
			//--
			if((strlen($line) >= 2) && (substr($line, -2) == "\r\n")) {
				//--
				break;
				//--
			} //end if
			//--
		} //end while
		//--
		return $line;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// retrive the full response message from server
	private function get_answer_data() { // IMAP4
		//--
		$data = '';
		//--
		while(1) {
			//--
			$line = $this->get_answer_line();
			//--
			$data .= $line;
			//--
			if(substr(trim($line), 0, (strlen($this->tag) + 1)) == $this->tag.' ') {
				break;
			} //end if
			//--
		} //end while
		//--
		return $data;
		//--
	} //END FUNCTION
	//=====================================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== END CLASS
//=====================================================================================



//=====================================================================================
//===================================================================================== START CLASS
//=====================================================================================


/**
 * Class: SmartMailerPop3Client - provides a POP3 Mail Client with SSL/TLS support.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.20191111
 * @package 	Plugins:Mailer
 *
 */
final class SmartMailerPop3Client {

	// ->


	/**
	 * @var INT
	 * socket read buffer (default is 512)
	 */
	public $buffer = 512;

	/**
	 * @var INT
	 * socket timeout in seconds (default is 30)
	 */
	public $timeout = 30;

	/**
	 * @var BOOLEAN
	 * debug on/off (default is FALSE)
	 */
	public $debug = false;

	/**
	 * @var STRING
	 * the error message(s) will be collected here
	 * do not SET a value here, but just GET the result
	 */
	public $error = '';

	/**
	 * @var STRING
	 * the operations log (only if debug is enabled)
	 * do not SET a value here, but just GET the result
	 */
	public $log = '';

	//--
	private $socket = false; 	// socket resource ID
	private $apop_banner = ''; 	// store the banner used for APOP Auth Method
	//--
	private $cafile = '';		// Certificate Authority File (instead of using the global SMART_FRAMEWORK_SSL_CA_FILE can use a private cafile
	//--


	//=====================================================================================
	// [INIT]
	public function __construct($buffer=0) {
		//--
		$this->socket = false;
		$this->apop_banner = '';
		//--
		if($buffer > 0) {
			$this->buffer = (int) $buffer;
		} //end if
		if($this->buffer < 512) {
			$this->buffer = 512;
		} elseif($this->buffer > 8192) {
			$this->buffer = 8192;
		} //end if else
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
	public function connect($server, $port=110, $sslversion='') {

		//-- inits
		$this->socket = false;
		$this->apop_banner = '';
		//--

		//-- checks
		$server = trim($server);
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
		//--
		if((string)$sslversion != '') {
			//--
			if(!function_exists('openssl_open')) {
				$this->error = '[ERR] PHP OpenSSL Extension is required to perform SSL requests !';
				return 0;
			} //end if
			//--
			switch(strtolower($sslversion)) {
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
		if((string)$protocol != '') {
			//--
			$cafile = '';
			if((string)$this->cafile != '') {
				$cafile = (string) $this->cafile;
			} elseif(defined('SMART_FRAMEWORK_SSL_CA_FILE')) {
				if((string)SMART_FRAMEWORK_SSL_CA_FILE != '') {
					$cafile = (string) SMART_FRAMEWORK_SSL_CA_FILE;
				} //end if
			} //end if
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

		//-- If mode is 0, the given stream will be switched to non-blocking mode, and if 1, it will be switched to blocking mode. This affects calls like fgets() and fread()  that read from the stream. In non-blocking mode an fgets() call will always return right away while in blocking mode it will wait for data to become available on the stream.
		@socket_set_blocking($this->socket, 1); // set to blocking mode
		//--

		//-- avoid connect normally if SSL/TLS was explicit required
		$chk_crypto = (array) @stream_get_meta_data($this->socket);
		if((string)$protocol != '') {
			if(!SmartUnicode::str_icontains($chk_crypto['stream_type'], '/ssl')) { // will return something like: tcp_socket/ssl
				//--
				$this->error = '[ERR] Connection CRYPTO CHECK Failed ...'."\n";
				//--
				@socket_set_blocking($this->socket, 0);
				@fclose($this->socket);
				$this->socket = false;
				//--
				return 0;
				//--
			} //end if
		} //end if
		//--

		//--
		$reply = @fgets($this->socket, $this->buffer);
		$reply = $this->strip_clf($reply);
		$test = $this->is_ok($reply);
		//--
		if((string)$test != 'ok') {
			//--
			$this->error = '[ERR] Server Reply is NOT OK // '.$test.' // '.$reply;
			//--
			@socket_set_blocking($this->socket, 0);
			@fclose($this->socket);
			$this->socket = false;
			//--
			return 0;
			//--
		} //end if
		//--
		if($this->debug) {
			$this->log .= '[REPLY] \''.$reply.'\''."\n";
		} //end if
		//--

		//-- apop banner
		$this->apop_banner = $this->parse_banner($reply);
		//--

		//--
		return 1;
		//--

	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// ping server
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
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
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
	// reset server connection (includding all messages marked to be deleted)
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
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] Reset the Connection FAILED !';
			return 0;
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// close connection
	public function quit() {
		//--
		if($this->debug) {
			$this->log .= '[INF] Sending QUIT to Mail Server !'."\n";
		} //end if
		//--
		if(!$this->socket) {
			$this->error = '[ERR] POP3 Connection cannot QUIT, it appears is not opened !';
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('QUIT'); // pop3
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$reply = $this->send_cmd('? LOGOUT'); // imap
		} //end if else
		//--
		@socket_set_blocking($this->socket, 0);
		@fclose($this->socket);
		$this->socket = false;
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// Sends both user and pass. Returns 1 on Success and 0 on Error
	public function login($username, $pass, $mode='') {
		//--
		$apop = false;
		if((string)$mode == 'apop') {
			$apop = true;
		} //end if
		//--
		if($this->debug) {
			$this->log .= '[INF] Login to Mail Server (MODE='.$mode.' ; USER='.$username.')'."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		if(($apop == true) AND (strlen($this->apop_banner) > 0)) {
			//-- apop login
			if($this->debug) {
				$this->log .= '[INF] Login Method: APOP // Banner = ['.$this->apop_banner.']'."\n";
			} //end if
			//--
			$reply = $this->send_cmd('APOP '.$username.' '.md5($this->apop_banner.$pass));
			if(strlen($this->error) > 0) {
				return 0;
			} //end if
			//--
			$test = $this->is_ok($reply);
			if((string)$test != 'ok') {
				$this->error = '[ERR] POP3 APOP Failed ['.$reply.']';
				return 0;
			} //end if
			//--
		} else {
			//-- normal login
			if($this->debug) {
				$this->log .= '[INF] Login Method: Normal'."\n";
			} //end if
			//--
			$reply = $this->send_cmd('USER '.$username);
			if(strlen($this->error) > 0) {
				return 0;
			} //end if
			//--
			$test = $this->is_ok($reply);
			if((string)$test != 'ok') {
				$this->error = '[ERR] POP3 User Failed ['.$reply.']';
				return 0;
			} //end if
			//--
			$reply = $this->send_cmd('PASS '.$pass);
			if(strlen($this->error) > 0) {
				return 0;
			} //end if
			//--
			$test = $this->is_ok($reply);
			if((string)$test != 'ok') {
				$this->error = '[ERR] POP3 Pass Failed ['.$reply.']';
				return 0;
			} //end if
			//--
		} //end if else
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// returns array() on Success and '' on Error
	public function count() {
		//--
		if($this->debug) {
			$this->log .= '[INF] Reading the Messages Count and Size for MailBox ...'."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$reply = $this->send_cmd('STAT');
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] POP3 Count Messages Failed ['.$reply.']';
			return '';
		} //end if
		//--
		$vars = (array) explode(' ', (string)$reply);
		$count = trim($vars[1]);
		$size = trim($vars[2]);
		//--
		$count = (int) $count;
		$size = (int) $size;
		//--
		if($this->debug) {
			$this->log .= '[INF] Messages Count [Count='.$count.' ; Size='.$size.']'."\n";
		} //end if
		//--
		return array('count' => $count, 'size' => $size);
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return the UID for the selected message or empty on error
	public function uid($msg_num='') {
		//--
		if($this->debug) {
			if(strlen($msg_num) > 0) {
				$this->log .= '[INF] POP3 UID Message Number: '.$msg_num."\n";
			} else {
				$this->log .= '[INF] POP3 UID for all Messages'."\n";
			} //end if else
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		if(strlen($msg_num) > 0) {
			$reply = $this->send_cmd('UIDL '.$msg_num);
		} else {
			$reply = $this->send_cmd('UIDL'); // returns: ID[SPACE]UID\n
		} //end if else
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] POP3 UID Message(s) Failed ['.$reply.']';
			return '';
		} //end if
		//--
		if(strlen($msg_num) > 0) {
			$tmp_arr = (array) explode(' ', (string)$reply); // The answer is like [OK] [MSGNUM] [UIDL]
			$uid = trim($tmp_arr[2]);
		} else {
			$uid = $this->retry_data();
			if(strlen($this->error) > 0) {
				return '';
			} //end if
		} //end if
		//--
		if($this->debug) {
			if(strlen($msg_num) > 0) {
				$this->log .= '[INF] UID For Message #'.$msg_num.' is: ['.$uid.']'."\n";
			} else {
				$this->log .= '[INF] UID For Messages are: [(LIST)]'."\n";
			} //end if else
		} //end if
		//--
		return $uid;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return the size for the selected message or empty on error
	public function size($msg_num) {
		//--
		if($this->debug) {
			$this->log .= '[INF] POP3 Size Message Number: '.$msg_num."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$reply = $this->send_cmd('LIST '.$msg_num);
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] POP3 Size Message Failed ['.$reply.']';
			return '';
		} //end if
		//--
		$tmp_arr = (array) explode(' ', (string)$reply); // The answer is like [JUNK] [MSGNUM] [MSGSIZE]
		$size = trim($tmp_arr[2]);
		//--
		if($this->debug) {
			$this->log .= '[INF] Size For Message #'.$msg_num.' is: ['.$size.']'."\n";
		} //end if
		//--
		return $size;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// delete a message from server
	public function delete($msg_num) {
		//--
		if($this->debug) {
			$this->log .= '[INF] POP3 Delete Message Number: '.$msg_num."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		$reply = $this->send_cmd('DELE '.$msg_num);
		if(strlen($this->error) > 0) {
			return 0;
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] POP3 Delete Message Failed ['.$reply.']';
			return 0;
		} //end if
		//--
		return 1;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return STRING: the message header or empty on error
	public function header($msg_num, $read_lines=5) {
		//--
		if($this->debug) {
			$this->log .= '[INF] POP3 Header Message Number: '.$msg_num.' // Lines: '.$read_lines."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$reply = $this->send_cmd('TOP '.$msg_num.' '.$read_lines);
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] POP3 Header Message Failed ['.$reply.']';
			return '';
		} //end if
		//--
		if(!$this->socket) {
			$this->error = '[ERR] POP3 Header Message: No connection to server';
			return '';
		} //end if
		//--
		$header_out = $this->retry_data();
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		return $header_out;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PUBLIC]
	// return STRING: the full message or empty on error
	public function read($msg_num) {
		//--
		if($this->debug) {
			$this->log .= '[INF] POP3 Read Message Number: '.$msg_num."\n";
		} //end if
		//--
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$reply = $this->send_cmd('RETR '.$msg_num);
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		$test = $this->is_ok($reply);
		if((string)$test != 'ok') {
			$this->error = '[ERR] POP3 Read Message Failed ['.$reply.']';
			return '';
		} //end if
		//--
		if(!$this->socket) {
			$this->error = '[ERR] POP3 Read Message: No connection to server';
			return '';
		} //end if
		//--
		$msg_out = $this->retry_data();
		if(strlen($this->error) > 0) {
			return '';
		} //end if
		//--
		return $msg_out;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// :: PRIVATES ::
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// Strips \r\n from server responses
	private function strip_clf($text='') {
		//--
		if(strlen($text) > 0) {
			$text = str_replace(array("\r", "\n"), array('', ''), $text);
		} //end if
		//--
		return $text;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// Return 'ok' on +OK or 'error#' on -ERR
	private function is_ok($reply) {
		//--
		$ok = 'ok';
		//--
		if(strlen((string)$reply) <= 0)	{
			$ok = 'ERROR: Reply is Empty !';
		} //end if
		//--
		if(!preg_match("/^\+OK/", (string)$reply)) {
			$ok = 'ERROR: Reply is not OK !';
		} //end if
		//--
		return $ok;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// parse the APOP banner
	private function parse_banner($reply) {
		//--
		$outside = true;
		$banner = '';
		//--
		$reply = trim($reply);
		//--
		for($i=0; $i<SmartUnicode::str_len($reply); $i++) {
			//--
			$digit = SmartUnicode::sub_str($reply, $i, 1);
			//--
			if(strlen($digit) > 0) {
				//--
				if((!$outside) AND ((string)$digit != '<') AND ((string)$digit != '>')) {
					$banner .= $digit;
				} //end if
				//--
				if((string)$digit == '<') {
					$outside = false;
				} //end if
				//--
				if((string)$digit == '>') {
					$outside = true;
				} //end if
				//--
			} //end if
			//--
		} //end for
		//--
		$banner = trim($this->strip_clf($banner)); // just in case
		if(strlen($banner) > 0) {
			$banner = '<'.$banner.'>';
		} //end if
		//--
		return $banner;
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
			$this->error = '[ERR] POP3 Send Command: No connection to server // '.$cmd;
			return '';
		} //end if
		//--
		if(strlen($cmd) <= 0) {
			$this->error = '[ERR] POP3 Send Command: Empty command to send !';
			return '';
		} //end if
		//--
		if(!@fputs($this->socket, $cmd."\r\n")) {
			$this->error = '[ERR] POP3 Send Command: FAILED !';
			return '';
		} //end if
		//--
		$reply = @fgets($this->socket, $this->buffer);
		$reply = $this->strip_clf($reply);
		//--
		if($this->debug) {
			//--
			if(substr(trim($cmd), 0, 5) == 'PASS ') {
				$tmp_cmd = 'PASS *****'; // hide the password protection
			} else {
				$tmp_cmd = $cmd;
			} //end if else
			//--
			$this->log .= '[INF] POP3 Send Command ['.$tmp_cmd.']'."\n".'[REPLY]: \''.$reply.'\''."\n";
			//--
		} //end if
		//--
		return $reply;
		//--
	} //END FUNCTION
	//=====================================================================================


	//=====================================================================================
	// [PRIVATE]
	// Retry Data from Server
	private function retry_data() {
		//--
		if(!$this->socket) {
			$this->error = '[ERR] POP3 Retry Data: No connection to server';
			return '';
		} //end if
		//--
		$data = '';
		$line = @fgets($this->socket, $this->buffer);
		if(strlen($line) > 0) {
			while(!preg_match("/^\.\r\n/", (string)$line)) {
				//--
				$data .= $line;
				//--
				$line = @fgets($this->socket, $this->buffer);
				if(strlen($line) <= 0) {
					break;
				} //end if
				//--
			} //end while
		} //end if
		//--
		return $data;
		//--
	} //END FUNCTION
	//=====================================================================================


} //END CLASS


//=====================================================================================
//===================================================================================== END CLASS
//=====================================================================================


//end of php code
?>