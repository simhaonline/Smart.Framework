<?php
// [LIB - SmartFramework / HTTP(S) Client]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.5')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - HTTP(S) Client w. (TLS/SSL)
// DEPENDS:
//	* Smart::
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartHttpClient - provides a HTTP / HTTPS Client (browser).
 *
 * To work with TLS / SSL (requires the PHP OpenSSL Module).
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	extensions: PHP OpenSSL (optional, just for HTTPS) ; classes: Smart
 * @version 	v.170613
 * @package 	Network:HTTP
 *
 */
final class SmartHttpClient {

	// ->

	//==============================================
	//--
	public $useragent = 'SmartFramework :: PHP/Robot'; 		// User agent (must have the robot in the name to avoid start un-necessary sessions)
	public $connect_timeout = 30;							// Connect timeout in seconds
	public $debug = 0;										// DEBUG
	//--
	public $rawheaders;										// Array of RawHeaders (to send)
	public $cookies;										// Array of Cookies (to send)
	public $postvars;										// Array of PostVars (to send)
	public $poststring;										// Pre-Built Post String (as alternative to PostVars) ; must not contain unencoded \r\n ; must use the RFC 3986 standard.
	public $jsonrequest;									// JSON Request (to send) ; must not contain unencoded \r\n but only \n ; hint: can be json-encoded without pretty-print.
	public $xmlrequest;										// XML Request (to send) ; must not contain \r\n but only \n
	//--
	//============================================== privates
	//-- set
	private $protocol = '1.0';								// HTTP Protocol :: 1.0 (default) or 1.1
	//-- returns
	private $header;										// Header (answer)
	private $body;											// Body (answer)
	private $status;										// STATUS (answer) :: 200, 401, 403 ...
	//-- log
	private $log;											// Operations Log (debug only)
	//-- internals
	private $socket = false;								// The Communication Socket
	private $raw_headers = array();							// Raw-Headers (internals)
	private $url_parts = array();							// URL Parts
	private $method = 'GET';								// method: GET / POST / HEAD / PUT / DELETE ...
	private $no_content_stop_if_unauth = true;				// Return no Content (response body) if Not Auth (401)
	//--
	private $cafile = '';									// Certificate Authority File (instead of using the global SMART_FRAMEWORK_SSL_CA_FILE can use a private cafile
	//--
	//==============================================


	//==============================================
	// [CONSTRUCTOR] :: init object
	public function __construct($y_protocol='', $y_no_content_stop_if_unauth=true) {

		//-- preset debugging
		$this->debug = 0;
		//--

		//-- set protocol: 1.0 or 1.1
		switch((string)$y_protocol) {
			case '1.1':
				$this->protocol = '1.1'; // for 1.1 the time can be significant LONGER than 1.0
				break;
			default:
			case '1.0':
				$this->protocol = '1.0'; // default is 1.0
		} //end switch
		//--

		//-- reset
		$this->reset();
		//--

		//-- inits
		$this->cookies = array();
		$this->postvars = array();
		$this->rawheaders = array();
		$this->jsonrequest = '';
		$this->xmlrequest = '';
		//--

		//-- signature
		$this->useragent = 'Mozilla/5.0 PHP.SmartFramework ('.php_uname().')';
		//--

		//-- option
		$this->no_content_stop_if_unauth = (bool) $y_no_content_stop_if_unauth;
		//--

	} //END FUNCTION
	//==============================================


	//==============================================
	// [PUBLIC] :: set the SSL/TLS Certificate Authority File
	public function set_ssl_tls_ca_file($cafile) {
		//--
		$this->cafile = (string) $cafile;
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	// [PUBLIC] :: browse the url as a robot (auth works only with Basic authentication)
	public function browse_url($url, $method='GET', $ssl_version='', $user='', $pwd='') {
		//--
		$result = $this->get_answer($url, $user, $pwd, $method, $ssl_version);
		//--
		return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
			'client' 		=> (string) __CLASS__,
			'date-time' 	=> (string) date('Y-m-d H:i:s O'),
			'protocol' 		=> (string) $this->protocol,
			'method' 		=> (string) $this->method,
			'url' 			=> (string) $url,
			'ssl'			=> (string) $ssl_version,
			'auth-user' 	=> (string) $user,
			'cookies-len' 	=> (int) Smart::array_size($this->cookies),
			'post-vars-len' => (int) Smart::array_size($this->postvars),
			'post-str-len' 	=> (int) strlen($this->poststring),
			'json-req-len' 	=> (int) strlen($this->jsonrequest),
			'xml-req-len' 	=> (int) strlen($this->jsonrequest),
			'mode' 			=> (string) trim((string)$this->url_parts['protocol']),
			'result' 		=> (int) $result,
			'code' 			=> (string) $this->status,
			'headers' 		=> (string) $this->header,
			'content' 		=> (string) $this->body,
			'log' 			=> (string) 'User-Agent: '.$this->useragent."\n", // this is reserved for calltime functions
			'debuglog' 		=> (string) $this->log // this is for internal use
		);
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	private function get_answer($url, $user, $pwd, $method, $ssl_version) {

		//-- reset
		$this->reset();
		//--

		//--
		if($this->debug) {
			$run_time = microtime(true);
		} //end if
		//--

		//-- set raw headers
		if(is_array($this->rawheaders)) {
			foreach($this->rawheaders as $key => $val) {
				$this->raw_headers[(string)$key] = (string) $val;
			} //end foreach
		} //end if
		//--

		//-- user agent will not be rewritten above
		if((string)$this->protocol == '1.1') {
			$this->raw_headers['Connection'] = 'close'; // fix for HTTP 1.1: by default on HTTP 1.1 connection is: keep-alive and must be set to explicit: close
		} //end if
		$this->raw_headers['User-Agent'] = (string) $this->useragent;
		//--

		//-- log action
		if($this->debug) {
			$this->log .= '[INF] HTTP(S) Robot Browser :: Get Answer :: url \''.$url.'\' @ Auth-User: '.$user.' // Auth-Pass-Length: ('.strlen($pwd).') // Method: '.$method.' // SSLVersion: '.$ssl_version."\n";
		} //end if
		//--

		//-- check if url supplied
		if((string)$url == '') {
			if($this->debug) {
				$this->log .= '[ERR] URL to browse is missing !'."\n";
			} //end if
			Smart::log_warning('LibHTTP // GetFromURL // URL to browse is empty ...');
			return 0;
		} //end if
		//--

		//-- get from url
		$success = $this->send_request($url, $user, $pwd, $method, $ssl_version);
		//--
		if(!$success) {
			if($this->debug) {
				$this->log .= '[ERR] Robot Browser Failed !'."\n";
				Smart::log_notice('LibHTTP // GetFromURL // Robot Browser Failed ... '.$url);
			} //end if
			$this->close_connection();
			return 0;
		} //end if
		//--

		//-- check
		if(!$this->socket) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Premature connection end (2.1)'."\n";
				Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (2.1) ...'.$url);
			} //end if
			$this->close_connection();
			return 0;
			//--
		} //end if
		//--

		//-- Get response header
		$this->header = (string) @fgets($this->socket, 4096);
		$this->status = (string) trim(substr(trim($this->header), 9, 3));
		//--
		$is_unauth = false;
		if(((string)$this->status == '401') AND (stripos($this->header, ' 401 Unauthorized') !== false)) {
			//--
			$is_unauth = true;
			//--
			if($this->debug) {
				if((string)$user != '') {
					$this->log .= '[ERR] HTTP Authentication Failed for URL: [User='.$user.']: '.$url."\n";
					Smart::log_notice('LibHTTP // GetFromURL // HTTP Authentication Failed for URL: [User='.$user.']: '.$url);
				} else {
					$this->log .= '[ERR] HTTP Authentication is Required for URL: '.$url."\n";
					Smart::log_notice('LibHTTP // GetFromURL // HTTP Authentication is Required for URL: '.$url);
				} //end if
			} //end if
			//--
		} //end if
		//--
		while(($this->socket) && (trim($line = @fgets($this->socket, 4096)) != '') && (!feof($this->socket))) {
			//--
			$this->header .= (string) $line;
			//--
			if(!$this->socket) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Premature connection end (2.2)'."\n";
					Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (2.2) ... '.$url);
				} //end if
				$this->close_connection();
				return 0;
				//--
			} //end if
			//--
		} //end while
		//--
		if(($is_unauth) AND ($this->no_content_stop_if_unauth)) { // in this case (by settings) no content (response body) should be returned
			$this->close_connection();
			return 0;
		} //end if
		//--

		//-- check
		if(!$this->socket) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Premature connection end (2.3)'."\n";
				Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (2.3) ... '.$url);
			} //end if
			$this->close_connection();
			return 0;
			//--
		} //end if
		//--

		//-- Get response body
		while(($this->socket) && (!feof($this->socket))) {
			//--
			$this->body .= (string) @fgets($this->socket, 4096);
			//--
			if(!$this->socket) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Premature connection end (2.4)'."\n";
					Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (2.4) ... '.$url);
				} //end if
				$this->close_connection();
				return 0;
				//--
			} //end if
			//--
		} //end while
		//--

		//--
		$this->close_connection();
		//--

		//--
		if($this->debug) {
			//--
			$run_time = microtime(true) - $run_time;
			//--
			$this->log .= '[INF] Total Time: '.$run_time.' sec.'."\n";
			//--
		} //end if
		//--

		//--
		return 1;
		//--

	} //END FUNCTION
	//==============================================


	## PRIVATES


	//==============================================
	private function reset() {
		//-- the log
		$this->log = '';
		//-- outputs
		$this->status = '';
		$this->header = '';
		$this->body = '';
		//-- internals
		$this->method = 'GET';
		$this->raw_headers = array();
		$this->url_parts = array();
		$this->socket = false;
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	// [PRIVATE] :: close connection
	private function close_connection() {
		//--
		if($this->socket) {
			//--
			@fclose($this->socket);
			//--
			if($this->debug) {
				$this->log .= '[INF] Connection Closed: OK.'."\n";
			} //end if
			//--
		} else {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Connection is already closed ...'."\n";
				Smart::log_notice('LibHTTP // GetFromURL // Connection is already closed ...');
			} //end if
			//--
		} //end if
		//--
		$this->socket = false;
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	// [PRIVATE] :: request from url and return content, headers, ...
	private function send_request($url, $user='', $pwd='', $method='GET', $ssl_version='') {

		//--
		$this->method = (string) strtoupper(trim((string)$method));
		//--

		//--
		$this->connect_timeout = (int) $this->connect_timeout;
		if($this->connect_timeout < 1) {
			$this->connect_timeout = 1;
		} //end if
		if($this->connect_timeout > 120) {
			$this->connect_timeout = 120;
		} //end if
		//--

		//-- log action
		if($this->debug) {
			$this->log .= '[INF] Get From URL :: is starting ...'."\n";
		} //end if
		//--

		//-- separations
		$this->url_parts = (array) Smart::separe_url_parts($url);
		$protocol = (string) $this->url_parts['protocol'];
		$server = (string) $this->url_parts['server'];
		$port = (string) $this->url_parts['port'];
		$path = (string) $this->url_parts['path'];
		//--
		if($this->debug) {
			$this->log .= '[INF] Analize of the URL: '.@print_r($this->url_parts,1)."\n";
		} //end if
		//--

		//--
		if((string)$server == '') {
			if($this->debug) {
				$this->log .= '[ERR] Invalid Server to Browse'."\n";
			} //end if
			Smart::log_warning('LibHTTP // GetFromURL () // Invalid (empty) Server to Browse ...');
			return 0;
		} //end if
		//--

		//--
		$browser_protocol = '';
		//--
		if((string)$protocol == 'https://') {
			//--
			switch(strtolower((string)$ssl_version)) {
				case 'ssl':
					$browser_protocol = 'ssl://';
					break;
				case 'sslv3':
					$browser_protocol = 'sslv3://';
					break;
				case 'tls':
				default:
					$browser_protocol = 'tls://';
			} //end switch
			//--
			if(!function_exists('openssl_open')) {
				if($this->debug) {
					$this->log .= '[ERR] PHP OpenSSL Extension is required to perform SSL requests'."\n";
				} //end if
				Smart::log_warning('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // PHP OpenSSL Extension not installed ...');
				return 0;
			} //end if
			//--
		} //end if else
		//--

		//--
		$have_cookies = false;
		if(is_array($this->cookies)) {
			if(count($this->cookies) > 0) {
				$have_cookies = true;
			} //end if
		} //end if
		//--
		$have_post_vars = false;
		if((string)$this->poststring != '') {
			$have_post_vars = true;
		} elseif(is_array($this->postvars)) {
			if(count($this->postvars) > 0) {
				$have_post_vars = true;
			} //end if
		} //end if
		//--

		//-- navigate
		if($this->debug) {
			$this->log .= 'Opening HTTP(S) Browser Connection to: '.$protocol.$server.':'.$port.$path.' using socket protocol: ['.$browser_protocol.']'."\n";
			$this->log .= '[INF] HTTP Protocol: '.$this->protocol."\n";
			$this->log .= '[INF] Connection TimeOut: '.$this->connect_timeout."\n";
		} //end if
		//--
		$stream_context = @stream_context_create();
		if((string)$browser_protocol != '') {
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
		$this->socket = @stream_socket_client($browser_protocol.$server.':'.$port, $errno, $errstr, $this->connect_timeout, STREAM_CLIENT_CONNECT, $stream_context);
		//--
		if(!is_resource($this->socket)) {
			if($this->debug) {
				$this->log .= '[ERR] Could not open connection. Error : '.$errno.': '.$errstr."\n";
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Could not open connection. Error : '.$errno.': '.$errstr.' #');
			} //end if
			return 0;
		} //end if
		//--
		if($this->debug) {
			$this->log .= '[INF] Socket Resource ID: '.$this->socket."\n";
		} //end if
		//--
		@stream_set_timeout($this->socket, (int)SMART_FRAMEWORK_NETSOCKET_TIMEOUT);
		if($this->debug) {
			$this->log .= '[INF] Set Socket Stream TimeOut to: '.SMART_FRAMEWORK_NETSOCKET_TIMEOUT."\n";
		} //end if
		//--

		//-- avoid connect normally if SSL/TLS was explicit required
		$chk_crypto = (array) @stream_get_meta_data($this->socket);
		if((string)$browser_protocol != '') {
			if(stripos($chk_crypto['stream_type'], '/ssl') === false) { // will return something like: tcp_socket/ssl
				if($this->debug) {
					$this->log .= '[ERR] Connection CRYPTO CHECK Failed ...'."\n";
					Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Connection CRYPTO CHECK Failed ...');
				} //end if
				return 0;
			} //end if
		} //end if
		//--

		//--
		$this->raw_headers['Host'] = $server.':'.$port;
		//--

		//-- auth
		if(((string)$user != '') AND ((string)$pwd != '')) {
			//--
			if($this->debug) {
				$this->log .= '[INF] Authentication will be attempted for USERNAME = \''.$user.'\' ; PASSWORD('.strlen($pwd).') *****'."\n";
			} //end if
			//--
			$this->raw_headers['Authorization'] = 'Basic '.base64_encode($user.':'.$pwd);
			//--
		} //end if
		//--

		//-- cookies
		$send_cookies = '';
		//--
		if($have_cookies) {
			//--
			foreach($this->cookies as $key => $value) {
				if((string)$key != '') {
					if((string)$value != '') {
						$send_cookies .= (string) SmartHttpUtils::encode_var_cookie($key, $value);
					} //end if
				} //end if
			} //end foreach
			//--
			if((string)$send_cookies != '') {
				$this->raw_headers['Cookie'] = $send_cookies;
				if($this->debug) {
					$this->log .= '[INF] Cookies will be SET: '.$send_cookies."\n";
				} //end if
			} //end if
			//--
		} //end if
		//--

		//-- request
		if((string)$this->jsonrequest != '') { // json request
			//--
			if($this->debug) {
				$this->log .= '[INF] JSON Request will be sent to server via: '.$this->method."\n";
			} //end if
			//--
			$request = $this->method.' '.$path.' HTTP/'.$this->protocol."\r\n";
			$this->raw_headers['Content-Type'] = 'application/json';
			$this->raw_headers['Content-Length'] = strlen($this->jsonrequest);
			//--
		} elseif((string)$this->xmlrequest != '') { // xml request
			//--
			if($this->debug) {
				$this->log .= '[INF] XML Request will be sent to server via: '.$this->method."\n";
			} //end if
			//--
			$request = $this->method.' '.$path.' HTTP/'.$this->protocol."\r\n";
			$this->raw_headers['Content-Type'] = 'application/xml'; // may be also: text/xml
			$this->raw_headers['Content-Length'] = strlen($this->xmlrequest);
			//--
		} elseif($have_post_vars) { // post vars
			//--
			if((string)$this->method == 'GET') {
				$this->method = 'POST'; // FIX: if GET Method is using PostVars, then set method to POST ; this should not be fixed for other methods like: HEAD, PUT, DELETE ...
			} //end if
			//--
			if($this->debug) {
				$this->log .= '[INF] Variables will be sent to server using POST method'."\n";
			} //end if
			//--
			$post_string = '';
			if((string)$this->poststring != '') {
				$post_string = (string) $this->poststring;
			} elseif(is_array($this->postvars)) {
				foreach($this->postvars as $key => $value) {
					$post_string .= (string) SmartHttpUtils::encode_var_post($key, $value);
				} //end foreach
			} //end if else
			//--
			$request = $this->method.' '.$path.' HTTP/'.$this->protocol."\r\n";
			$this->raw_headers['Content-Type'] = 'application/x-www-form-urlencoded';
			$this->raw_headers['Content-Length'] = strlen($post_string);
			//--
		} else { // simple request
			//--
			if($this->debug) {
				$this->log .= '[INF] Simple Request via: '.$this->method."\n";
			} //end if
			//--
			$request = $this->method.' '.$path.' HTTP/'.$this->protocol."\r\n";
			//--
		} //end if else
		//--

		//-- check
		if(!$this->socket) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Premature connection end (1.1)'."\n";
				Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (1.1) ... '.$url);
			} //end if
			return 0;
			//--
		} //end if
		//--

		//--
		if(@fwrite($this->socket, $request) === false) {
			if($this->debug) {
				$this->log .= '[ERR] Error writing Request type to socket'."\n";
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing Request type to socket ...');
			} //end if
			return 0;
		} //end if
		//--

		//-- raw headers
		if(!$this->socket) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Premature connection end (1.2)'."\n";
				Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (1.2) ... '.$url);
			} //end if
			return 0;
			//--
		} //end if
		//--
		foreach($this->raw_headers as $key => $value) {
			if(@fwrite($this->socket, $key.": ".$value."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing Raw-Headers to socket'."\n";
					Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing Raw-Headers to socket ...');
				} //end if
				return 0;
			} //end if
		} //end foreach
		//--

		//-- end-line or blank line before post / cookies
		if(!$this->socket) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Premature connection end (1.3)'."\n";
				Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (1.3) ... '.$url);
			} //end if
			return 0;
			//--
		} //end if
		//--
		if(@fwrite($this->socket, "\r\n") === false) {
			if($this->debug) {
				$this->log .= '[ERR] Error writing End-Of-Line to socket'."\n";
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing End-Of-Line to socket ...');
			} //end if
			return 0;
		} //end if
		//--

		//--
		if((string)$this->jsonrequest != '') { // json request
			//--
			if(!$this->socket) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Premature connection end (1.4)'."\n";
					Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (1.4) ... '.$url);
				} //end if
				return 0;
				//--
			} //end if
			//--
			if(@fwrite($this->socket, $this->jsonrequest."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing JSON Request data to socket'."\n";
					Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing JSON Request data to socket ...');
				} //end if
				return 0;
			} //end if
			//--
		} elseif((string)$this->xmlrequest != '') { // xml request
			//--
			if(!$this->socket) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Premature connection end (1.5)'."\n";
					Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (1.5) ... '.$url);
				} //end if
				return 0;
				//--
			} //end if
			//--
			if(@fwrite($this->socket, $this->xmlrequest."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing XML Request data to socket'."\n";
					Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing XML Request data to socket ...');
				} //end if
				return 0;
			} //end if
			//--
		} elseif($have_post_vars) {
			//--
			if(!$this->socket) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Premature connection end (1.6)'."\n";
					Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (1.6) ... '.$url);
				} //end if
				return 0;
				//--
			} //end if
			//--
			if(@fwrite($this->socket, $post_string."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing POST data to socket'."\n";
					Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing POST data to socket ...');
				} //end if
				return 0;
			} //end if
			//--
		} //end if else
		//--

		//-- NOTICE: is this necessary ??? appears that not (it was tested a long time without and appear to be faster) ...
		//if(@fwrite($this->socket, "\r\n") === false) {
		//	if($this->debug) {
		//		$this->log .= '[ERR] Error writing EOL to socket'."\n";
		//		Smart::log_notice('LibHTTP // GetFromURL ('.$conex_info.') // Error writing EOL ...'); // FIX: the final \r\n
		//	} //end if
		//} //end if
		//--

		//--
		return 1;
		//--

	} //END FUNCTION
	//==============================================


} //END CLASS


//===================================================== USAGE
/*
$browser = new SmartHttpClient();
$browser->connect_timeout = 20;
print_r(
	$browser->browse_url('https://some-website.ext:443/some-path/', 'GET', 'tls')
);
*/
//===================================================== SAMPLE PARSE PUT METHOD IN PHP
/*
if((string)$_SERVER['REQUEST_METHOD'] === 'PUT') {
	$putdata = file_get_contents('php://input', false , null, -1 , (int)$_SERVER['CONTENT_LENGTH']);
	file_put_contents("test-http-stdin.txt", $putdata);
	parse_str($putdata, $put_vars); // parse PUT data to array
	print_r($put_vars);
} //end if
*/
//=====================================================


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: Smart HTTP Utils) - provides utils function to work with HTTP protocol.
 *
 * <code>
 * // Usage example:
 * Smart::some_method_of_this_class(...);
 * </code>
 *
 * @usage       static object: Class::method() - This class provides only STATIC methods
 * @hints       It is recommended to use the methods in this class instead of PHP native methods whenever is possible because this class will offer Long Term Support and the methods will be supported even if the behind PHP methods can change over time, so the code would be easier to maintain.
 *
 * @access      PUBLIC
 * @depends     classes: Smart
 * @version     v.170613
 * @package 	Network:HTTP
 *
 */
final class SmartHttpUtils {


	// ::


	//==============================================
	// encode a cookie var ; returns the HTTP Cookie string
	public static function encode_var_cookie($name, $value) {
		//--
		$name = (string) trim((string)$name);
		//--
		$out = '';
		//--
		if((string)$name != '') {
			$out = (string) urlencode($name).'='.rawurlencode($value).';';
		} //end if else
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	// encode a post var ; returns the HTTP Post string
	public static function encode_var_post($varname, $value) {
		//--
		$varname = (string) trim((string)$varname);
		//--
		if((string)$varname == '') {
			return '';
		} //end if
		//--
		$out = '';
		//--
		if(is_array($value)) {
			$arrtype = Smart::array_type_test($value); // 0: not an array ; 1: non-associative ; 2:associative
			if($arrtype === 1) { // 1: non-associative
				for($i=0; $i<Smart::array_size($value); $i++) {
					$out .= urlencode($varname).'[]='.rawurlencode($value[$i]).'&';
				} //end foreach
			} else { // 2: associative
				foreach($value as $key => $val) {
					$out .= urlencode($varname).'['.rawurlencode($key).']='.rawurlencode($val).'&';
				} //end foreach
			} //end if else
		} else {
			$out = urlencode($varname).'='.rawurlencode($value).'&';
		} //end if else
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//==============================================

} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>