<?php
// [LIB - SmartFramework / HTTP(S) Client]
// (c) 2006-2016 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
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
 * @version 	v.160419
 * @package 	Network:HTTP
 *
 */
final class SmartHttpClient {

	// ->

	//==============================================
	//--
	public $useragent = 'SmartFramework :: PHP/Robot'; 		// user agent (must have the robot in the name to avoid start un-necessary sessions)
	public $connect_timeout = 30;							// timeout in seconds
	public $debug = 0;										// DEBUG
	//--
	public $cookies;										// Cookies (to send)
	public $postvars;										// PostVars (to send)
	public $poststring;										// Pre-Built Post String (for working with Lucene / Solr)
	public $rawheaders;										// Raw Headers (to send)
	public $xmlrequest;										// XML Request (to send)
	public $jsonrequest;									// JSON Request (to send)
	//--
	//============================================== privates
	//-- set
	private $protocol = '1.0';								// HTTP Protocol :: 1.0 or 1.1
	//-- returns
	private $header;										// Header (answer)
	private $body;											// Body (answer)
	private $status;										// STATUS
	//-- log
	private $log;											// Log (debug)
	//-- internals
	private $socket = false;								// Communication Socket
	private $raw_headers = array();							// Raw-Headers (internals)
	private $url_parts = array();							// URL Parts
	//--
	//==============================================


	//==============================================
	// [CONSTRUCTOR] :: init object
	public function __construct($y_protocol='') {

		//-- preset debugging
		$this->debug = 0;
		//--

		//-- set protocol
		if((string)$y_protocol == '1.1') {
			$this->protocol = '1.1'; // the time can be significant LONGER ... otherwise default is 1.0
		} //end if else
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

		//--
		return 1;
		//--

	} //END FUNCTION
	//==============================================


	//==============================================
	// [PUBLIC] :: browse the url as a robot (auth works only with Basic authentication)
	public function browse_url($url, $method='GET', $ssl_version='', $user='', $pwd='') {
		//--
		$result = $this->fetch_url($url, $user, $pwd, $method, $ssl_version);
		//--
		return array( // {{{SYNC-GET-URL-OR-FILE-RETURN}}}
			'log' 		=> 'User-Agent: '.$this->useragent."\n", // this is reserved for calltime functions
			'mode' 		=> trim((string)$this->url_parts['protocol']),
			'result' 	=> $result,
			'code' 		=> $this->status,
			'headers' 	=> $this->header,
			'content' 	=> $this->body,
			'debuglog' 	=> $this->log // this is for internal use
		);
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	private function fetch_url($url, $user, $pwd, $method, $ssl_version) {

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
		$this->raw_headers['User-Agent'] = (string) $this->useragent;
		//--

		//-- log action
		if($this->debug) {
			$this->log .= '[INF] HTTP(S) Robot Browser :: Fetch :: url \''.$url.'\' @ Auth-User: '.$user.' // Auth-Pass-Length: ('.strlen($pwd).') // Method: '.$method.' // SSLVersion: '.$ssl_version."\n";
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
		$success = $this->get_from_url($url, $user, $pwd, $method, $ssl_version);
		//--
		if(!$success) {
			if($this->debug) {
				$this->log .= '[ERR] Robot Browser Failed !'."\n";
			} //end if
			$this->close_connection();
			Smart::log_notice('LibHTTP // GetFromURL // Robot Browser Failed ... '.$url);
			return 0;
		} //end if
		//--

		//-- Get response header
		if(!$this->socket) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Premature connection end (1)'."\n";
			} //end if
			$this->close_connection();
			Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (1) ...'.$url);
			return 0;
			//--
		} //end if
		//--

		//--
		$this->header = @fgets($this->socket, 4096);
		//--
		$this->status = trim(substr(trim($this->header), 9, 3));
		//--
		while(($this->socket) && (trim($line = @fgets($this->socket, 4096)) != '') && (!feof($this->socket))) {
			//--
			$this->header .= $line;
			//--
			if(((string)$this->status == '401') AND (stripos($line, 'WWW-Authenticate: Basic realm="') === false)) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Could not authenticate'."\n";
				} //end if
				$this->close_connection();
				Smart::log_notice('LibHTTP // GetFromURL // Could not authenticate ... : '.$url);
				return 0;
				//--
			} //end if
			//--
			if(!$this->socket) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Premature connection end (2)'."\n";
				} //end if
				$this->close_connection();
				Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (2) ... '.$url);
				return 0;
				//--
			} //end if
			//--
		} //end while
		//--

		//-- Get response header
		if(!$this->socket) {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Premature connection end (3)'."\n";
			} //end if
			$this->close_connection();
			Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (3) ... '.$url);
			return 0;
			//--
		} //end if
		//--

		//-- Get response body
		while(($this->socket) && (!feof($this->socket))) {
			//--
			$this->body .= @fgets($this->socket, 4096);
			//--
			if(!$this->socket) {
				//--
				if($this->debug) {
					$this->log .= '[ERR] Premature connection end (4)'."\n";
				} //end if
				$this->close_connection();
				Smart::log_notice('LibHTTP // GetFromURL // Premature connection end (4) ... '.$url);
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
			$run_time = microtime(true) - $run_time;
			$this->log .= '[INF] Total Time: '.$run_time.' sec.'."\n";
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
				$this->log .= '[INF] Connection Closed / OK.'."\n";
			} //end if
			//--
		} else {
			//--
			if($this->debug) {
				$this->log .= '[ERR] Connection is already closed ...'."\n";
			} //end if
			//--
			Smart::log_notice('LibHTTP // GetFromURL // Connection is already closed ...');
			//--
		} //end if
		//--
	} //END FUNCTION
	//==============================================


	//==============================================
	// [PRIVATE] :: get content from url
	private function get_from_url($url, $user='', $pwd='', $method='GET', $ssl_version='') {

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
		$parts = (array) Smart::separe_url_parts($url);
		$this->url_parts = $parts;
		$protocol = $parts['protocol'];
		$server = $parts['server'];
		$port = $parts['port'];
		$path = $parts['path'];
		//--
		if($this->debug) {
			$this->log .= '[INF] Analize of the URL: '.@print_r($parts,1)."\n";
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
			switch(strtolower($ssl_version)) {
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
			$this->log .= 'Opening HTTP(S) Browser: '.$protocol.$server.':'.$port.$path.' using socket protocol: ['.$browser_protocol.']'."\n";
			$this->log .= '[INF] HTTP Protocol: '.$this->protocol."\n";
			$this->log .= '[INF] Connection TimeOut: '.$this->connect_timeout."\n";
		} //end if
		//--
		$stream_context = @stream_context_create();
		if((string)$browser_protocol != '') {
			if(defined('SMART_FRAMEWORK_SSL_CA_PATH')) {
				if((string)SMART_FRAMEWORK_SSL_CA_PATH != '') {
					@stream_context_set_option($stream_context, 'ssl', 'capath', Smart::real_path((string)SMART_FRAMEWORK_SSL_CA_PATH));
				} //end if
			} //end if
			@stream_context_set_option($stream_context, 'ssl', 'ciphers', 				(string)SMART_FRAMEWORK_SSL_CIPHERS); // allow only high ciphers
			@stream_context_set_option($stream_context, 'ssl', 'verify_host', 			(bool)SMART_FRAMEWORK_SSL_VFY_HOST); // allways must be set to true !
			@stream_context_set_option($stream_context, 'ssl', 'verify_peer', 			(bool)SMART_FRAMEWORK_SSL_VFY_PEER); // this may fail with some CAs
			@stream_context_set_option($stream_context, 'ssl', 'verify_peer_name', 		(bool)SMART_FRAMEWORK_SSL_VFY_PEER_NAME); // allow also wildcard names *
			@stream_context_set_option($stream_context, 'ssl', 'allow_self_signed', 	(bool)SMART_FRAMEWORK_SSL_ALLOW_SELF_SIGNED); // must allow self-signed certificates but verified above
			@stream_context_set_option($stream_context, 'ssl', 'disable_compression', 	(bool)SMART_FRAMEWORK_SSL_DISABLE_COMPRESS); // help mitigate the CRIME attack vector
		} //end if else
		$this->socket = @stream_socket_client($browser_protocol.$server.':'.$port, $errno, $errstr, $this->connect_timeout, STREAM_CLIENT_CONNECT, $stream_context);
		//--
		if(!is_resource($this->socket)) {
			if($this->debug) {
				$this->log .= '[ERR] Could not open connection. Error : '.$errno.': '.$errstr."\n";
			} //end if
			Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Could not open connection. Error : '.$errno.': '.$errstr.' #');
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
				} //end if
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Connection CRYPTO CHECK Failed ...');
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
						$send_cookies .= (string) $this->encode_cookie($key, $value);
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
				$this->log .= '[INF] JSON Request will be sent to server via: '.$method."\n";
			} //end if
			//--
			$request = $method.' '.$path.' HTTP/'.$this->protocol."\r\n";
			$this->raw_headers['Content-Type'] = 'application/json';
			$this->raw_headers['Content-Length'] = strlen($this->jsonrequest);
			//--
		} elseif((string)$this->xmlrequest != '') { // xml request
			//--
			if($this->debug) {
				$this->log .= '[INF] XML Request will be sent to server via: '.$method."\n";
			} //end if
			//--
			$request = $method.' '.$path.' HTTP/'.$this->protocol."\r\n";
			$this->raw_headers['Content-Type'] = 'application/xml'; // may be also: text/xml
			$this->raw_headers['Content-Length'] = strlen($this->xmlrequest);
			//--
		} elseif($have_post_vars) { // post vars
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
					$post_string .= (string) $this->encode_postvar($key, $value);
				} //end foreach
			} //end if else
			//--
			$request = 'POST '.$path.' HTTP/'.$this->protocol."\r\n";
			$this->raw_headers['Content-Type'] = 'application/x-www-form-urlencoded';
			$this->raw_headers['Content-Length'] = strlen($post_string);
			//--
		} else { // simple request
			//--
			if($this->debug) {
				$this->log .= '[INF] Simple Request via: '.$method."\n";
			} //end if
			//--
			$request = $method.' '.$path.' HTTP/'.$this->protocol."\r\n";
			//--
		} //end if else
		//--
		if(@fwrite($this->socket, $request) === false) {
			if($this->debug) {
				$this->log .= '[ERR] Error writing Request type to socket'."\n";
			} //end if
			Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing Request type to socket ...');
			return 0;
		} //end if
		//--

		//-- raw headers
		foreach($this->raw_headers as $key => $value) {
			if(@fwrite($this->socket, $key.": ".$value."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing Raw-Headers to socket'."\n";
				} //end if
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing Raw-Headers to socket ...');
				return 0;
			} //end if
		} //end foreach
		//--

		//-- end-line or blank line before post / cookies
		if(@fwrite($this->socket, "\r\n") === false) {
			if($this->debug) {
				$this->log .= '[ERR] Error writing End-Of-Line to socket'."\n";
			} //end if
			Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing End-Of-Line to socket ...');
			return 0;
		} //end if
		//--

		//--
		if((string)$this->jsonrequest != '') { // json request
			if(@fwrite($this->socket, $this->jsonrequest."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing JSON Request data to socket'."\n";
				} //end if
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing JSON Request data to socket ...');
				return 0;
			} //end if
		} elseif((string)$this->xmlrequest != '') { // xml request
			if(@fwrite($this->socket, $this->xmlrequest."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing XML Request data to socket'."\n";
				} //end if
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing XML Request data to socket ...');
				return 0;
			} //end if
		} elseif($have_post_vars) {
			if(@fwrite($this->socket, $post_string."\r\n") === false) {
				if($this->debug) {
					$this->log .= '[ERR] Error writing POST data to socket'."\n";
				} //end if
				Smart::log_notice('LibHTTP // GetFromURL ('.$browser_protocol.$server.':'.$port.$path.') // Error writing POST data to socket ...');
				return 0;
			} //end if
		} //end if else
		//--

		//--
		return 1;
		//--

	} //END FUNCTION
	//==============================================


	//==============================================
	// [PRIVATE] :: encode a cookie
	private function encode_cookie($name, $value) {
		$out = '';
		if((string)$name != '') {
			$out = (string) urlencode($name).'='.rawurlencode($value).';';
		} //end if else
		return (string) $out;
	} //END FUNCTION
	//==============================================


	//==============================================
	// [PRIVATE] :: encode a post var
	private function encode_postvar($varname, $value) {
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
				for($i=0; $i<count($value); $i++) {
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
		return (string) $out;
	} //END FUNCTION
	//==============================================


} //END CLASS


//===================================================== USAGE
/*
$browser = new SmartHttpClient();
$browser->connect_timeout=20;
print_r($browser->browse_url('https://some-website.ext:443/some-path/'));
*/
//=====================================================


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//end of php code
?>