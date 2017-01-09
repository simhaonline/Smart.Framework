<?php
// [LIB - SmartFramework / FTP Client]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.5 r.2017.01.09 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - FTP Client
// DEPENDS:
//	* Smart::
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartFtpClient - provides a FTP Client with support for common FTP protocol. In addition it supports the extended Hylafax FTP protocol.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	classes: Smart
 * @version 	v.160205
 * @package 	Network:FTP
 *
 */
final class SmartFtpClient {

	// ->


	//=================================================== Public variables
	public $error_msg;		// error messages
	public $debug;			// debug true/false
	public $debug_level;	// if 'full' get all messages else then get only answers
	public $debug_msg;		// store debug messages
	public $timeout;		// socket time-out
	public $umask;			// local umask
	//=================================================== Private variables
	private $_sock;			// connection socket
	private $_buf;			// socket buffer
	private $_resp;			// server response
	//===================================================


	//=================================================== Constructor
	public function __construct($debug=false, $timeout=30, $umask=0022) {
		//--
		$this->error_msg 	= '';
		//--
		if($debug) {
			$this->debug   	= true;
		} else {
			$this->debug   	= false;
		} //end if else
		$this->debug_level 	= '';
		$this->debug_msg = '';
		//--
		$this->timeout 		= $timeout;
		$this->umask   		= $umask;
		//--
		$this->_sock 		= false;
		$this->_buf  		= 4096;
		$this->_resp 		= '';
		//--
	} //END FUNCTION
	//===================================================


	//===================================================
	//=================================================== Public functions
	//===================================================


	//===================================
	public function ftp_connect($server, $port=21) {
		//--
		if((string)$this->debug_level == 'full') {
			$this->_debug_print('Connecting to '.$server.':'.$port.' (TimeOut='.$this->timeout.')...'."\n");
		} //end if
		//--
		$this->_sock = @fsockopen($server, $port, $errno, $errstr, $this->timeout);
		//--
		if((!$this->_sock) || (!$this->_ok())) {
			$this->error_msg = 'ERROR: Cannot connect to remote host @ '.$server.':'.$port.' // '.$errstr.' ('.$errno.')';
			$this->_close_data_connection($this->_sock);
			return false;
		} //end if
		//--
		$this->_debug_print('OK: Connected to remote host '.$server.':'.$port."\n");
		//--
		return true;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_login($user, $pass) {
		//--
		$this->_putcmd("USER", $user);
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: USER command failed';
			return false;
		} //end if
		//--
		if((string)$pass != '') {
		  $this->_putcmd("PASS", $pass);
		  if(!$this->_ok()) {
			$this->error_msg = 'ERROR: PASS command failed';
			return false;
		  } //end if
		} //end if
		//--
		$this->_debug_print('OK: Authentication succeeded'."\n");
		//--
		return true;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_pwd() {
		//--
		$this->_putcmd("PWD");
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: PWD command failed';
			return false;
		} //end if
		//--
		return preg_replace("/^[0-9]{3} \"(.+)\" .+\r\n/", "\\1", (string)$this->_resp);
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_size($pathname) {
		// if file does not exists returns -1
		//--
		$this->_putcmd("SIZE", $pathname);
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: SIZE command failed';
			return -1;
		} //end if
		//--
		return preg_replace("/^[0-9]{3} ([0-9]+)\r\n/", "\\1", (string)$this->_resp);
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_mdtm($pathname) {
		//--
		$this->_putcmd("MDTM", $pathname);
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: MDTM command failed';
			return -1;
		} //end if
		//--
		$mdtm = preg_replace("/^[0-9]{3} ([0-9]+)\r\n/", "\\1", (string)$this->_resp);
		//--
		$date = sscanf($mdtm, "%4d%2d%2d%2d%2d%2d");
		$timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);
		//--
		return $timestamp;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_systype() {
		//--
		$this->_putcmd("SYST");
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: SYST command failed';
			return false;
		} //end if
		//--
		$res_data = (array) explode(" ", (string)$this->_resp);
		//--
		return $res_data[1];
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_cdup() {
		//--
		$this->_putcmd("CDUP");
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: CDUP command failed';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_chdir($pathname) {
		//--
		$this->_putcmd("CWD", $pathname);
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: CWD command failed';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_delete($pathname) {
		//--
		$this->_putcmd("DELE", $pathname);
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: DELE command failed';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_rmdir($pathname) {
		//--
		$this->_putcmd("RMD", $pathname);
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: RMD command failed';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_mkdir($pathname) {
		//--
		$this->_putcmd("MKD", $pathname);
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: MKD command failed';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_file_exists($pathname) {
		//--
		if(!($remote_list = $this->ftp_nlist("-a"))) {
			$this->error_msg = 'ERROR: Cannot get remote file list';
			return -1;
		} //end if
		//--
		reset($remote_list);
		//--
		//while(list(,$value) = @each($remote_list)) {
		while(list($key,$value) = @each($remote_list)) { // FIX to be compatible with the upcoming PHP 7
			//--
			if((string)$value == (string)$pathname) {
				$this->error_msg = 'ERROR: Remote file exists: '.$pathname;
				return 1;
			} //end if
			//--
		} //end while
		//--
		if((string)$this->debug_level == 'full') {
			$this->_debug_print('OK: Remote file does not exists: '.$pathname."\n");
		} //end if
		//--
		return 0;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_rename($from, $to) {
		//--
		$this->_putcmd("RNFR", $from);
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: RNFR command failed';
			return false;
		} //end if
		//--
		$this->_putcmd("RNTO", $to);
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: RNTO command failed';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_nlist($arg='', $pathname='') {
		//--
		if(!($string = $this->_pasv())) {
			$this->error_msg = 'ERROR: NLST command failed - PASSIVE';
			return false;
		} //end if
		//--
		if((string)$arg == "") {
			$nlst = "NLST";
		} else {
			$nlst = "NLST ".$arg;
		} //end if else
		//--
		$this->_putcmd($nlst, $pathname);
		//--
		$sock_data = $this->_open_data_connection($string);
		if(!$sock_data) {
			$this->error_msg = 'ERROR: NLST // Cannot connect to remote host';
			return false;
		} //end if
		//--
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: NLST command failed (1)';
			$this->_close_data_connection($sock_data);
			return false;
		} //end if
		//--
		$this->_debug_print('OK: NLST // Connected to remote host'."\n");
		//--
		$list = array();
		while(!feof($sock_data)) {
			$list[] = preg_replace("/[\r\n]/", "", (string)@fgets($sock_data, 512));
		} //end while
		//--
		$this->_close_data_connection($sock_data);
		//--
		if((string)$this->debug_level == 'full') {
			$this->_debug_print((string)implode("\n", (array)$list));
		} //end if
		//--
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: NLST command failed (2)';
			return array();
		} //end if
		//--
		return $list;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_rawlist($pathname='') {
		//--
		if(!($string = $this->_pasv())) {
			$this->error_msg = 'ERROR: LIST command failed - PASSIVE';
			return false;
		} //end if
		//--
		$this->_putcmd("LIST", $pathname);
		$sock_data = $this->_open_data_connection($string);
		//--
		if(!$sock_data) {
			$this->error_msg = 'ERROR: LIST // Cannot connect to remote host';
			return false;
		} //end if
		//--
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: LIST command failed (1)';
			$this->_close_data_connection($sock_data);
			return false;
		} //end if
		//--
		$this->_debug_print('OK: LIST // Connected to remote host'."\n");
		//--
		$list = array();
		while(!feof($sock_data)) {
			$list[] = preg_replace("/[\r\n]/", "", (string)@fgets($sock_data, 512));
		} //end while
		//--
		if((string)$this->debug_level == 'full') {
			$this->_debug_print((string)implode("\n", (array)$list));
		} //end if
		//--
		$this->_close_data_connection($sock_data);
		//--
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: LIST command failed (2)';
			return array();
		} //end if
		//--
		return $list;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_get($localfile, $remotefile, $mode=1) {
		//--
		umask($this->umask);
		//--
		if(SmartFileSystem::file_or_link_exists($localfile)) {
			SmartFileSystem::delete($localfile);
			if((string)$this->debug_level == 'full') {
				$this->_debug_print('WARNING: local file will be overwritten'."\n");
			} //end if
		} //end if
		//--
		$fp = @fopen($localfile, 'wb');
		if(!$fp) {
			$this->error_msg = 'ERROR: GET command failed // Cannot create local file: '.$localfile;
			return false;
		} //end if
		//--
		if(!$this->_type($mode)) {
			$this->error_msg = 'ERROR: GET command failed - TYPE: '.$mode;
			@fclose($fp);
			return false;
		} //end if
		//--
		if(!($string = $this->_pasv())) {
			$this->error_msg = 'ERROR: GET command failed - PASSIVE';
			@fclose($fp);
			return false;
		} //end if
		//--
		$this->_putcmd("RETR", $remotefile);
		//--
		$sock_data = $this->_open_data_connection($string);
		//--
		if(!$sock_data) {
			$this->error_msg = 'ERROR: GET // Cannot connect to remote host';
			return false;
		} //end if
		//--
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: GET command failed (1)';
			$this->_close_data_connection($sock_data);
			return false;
		} //end if
		//--
		$this->_debug_print('OK: GET // Connected to remote host'."\n");
		if((string)$this->debug_level == 'full') {
			$this->_debug_print('Retrieving remote file: '.$remotefile.' to local file: '.$localfile."\n");
		} //end if
		//--
		while(!feof($sock_data)) {
			@fwrite($fp, @fread($sock_data, $this->_buf));
		} //end if
		//--
		@fclose($fp);
		//--
		$this->_close_data_connection($sock_data);
		//--
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: GET command failed (2)';
			return '';
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_put($remotefile, $localfile, $mode=1) {
		//--
		if(!SmartFileSystem::file_or_link_exists($localfile)) {
			$this->error_msg = 'ERROR: PUT command failed // No such file or directory (or broken link): '.$localfile;
			return false;
		} //end if
		//--
		$fp = @fopen($localfile, "rb");
		if(!$fp) {
			$this->error_msg = 'ERROR: PUT command failed // Cannot read file: '.$localfile;
			return false;
		} //end if
		//--
		if(!$this->_type($mode)) {
			$this->error_msg = 'ERROR: PUT command failed - TYPE: '.$mode;
			@fclose($fp);
			return false;
		} //end if
		//--
		if(!($string = $this->_pasv())) {
			$this->error_msg = 'ERROR: PUT command failed - PASSIVE';
			@fclose($fp);
			return false;
		} //end if
		//--
		$this->_putcmd("STOR", $remotefile);
		//--
		$sock_data = $this->_open_data_connection($string);
		//--
		if(!$sock_data) {
			$this->error_msg = 'ERROR: PUT // Cannot connect to remote host';
			return false;
		} //end if
		//--
		if(!$this->_ok()) {
			$this->error_msg = 'ERROR: PUT command failed (1)';
			$this->_close_data_connection($sock_data);
			return false;
		} //end if
		//--
		$this->_debug_print('OK: PUT // Connected to remote host'."\n");
		if((string)$this->debug_level == 'full') {
			$this->_debug_print('Storing local file: '.$localfile.' to remote file: '.$remotefile."\n");
		} //end if
		//--
		while(!feof($fp)) {
			@fwrite($sock_data, @fread($fp, $this->_buf));
		} //end while
		//--
		@fclose($fp);
		//--
		$this->_close_data_connection($sock_data);
		//--
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: PUT command failed (2)';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_site($command) {
		//--
		$this->_putcmd("SITE", $command);
		//--
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: SITE command failed';
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_extcmd($command) {
		//--
		$this->_putcmd($command, '');
		$response = $this->_ok();
		if(!$response) {
			$this->error_msg = 'ERROR: EXTENDED command failed: '.$command;
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_extcmdx($command) {
		//--
		$this->_putcmd($command, '');
		$response = $this->_answer();
		if(!$response) {
			$this->error_msg = 'ERROR: EXTENDED-XTRA command failed: '.$command;
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	public function ftp_quit() {
		//--
		$this->_putcmd("QUIT");
		//--
		if(!$this->_ok()) {
			$this->_debug_print('ERROR: QUIT command failed'."\n");
		} //end if
		//--
		$out = $this->_close_data_connection($this->_sock);
		//--
		if($out) {
			$this->_debug_print('Disconnected from remote host'."\n");
		} else {
			$this->_debug_print('WARNING: Disconnecting from remote host Failed ...'."\n");
		} //end if
		//--
		return $out;
		//--
	} //END FUNCTION
	//===================================


	//===================================================
	//=================================================== Private Functions
	//===================================================


	//===================================
	private function _type($mode) {
		//--
		if($mode) {
			$type = "I"; //Binary mode
		} else {
			$type = "A"; //ASCII mode
		} //end if else
		//--
		$this->_putcmd("TYPE", $type);
		$response = $this->_ok();
		if(!$response) {
			$this->_debug_print('ERROR: TYPE command failed'."\n");
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _port($ip_port) {
		//--
		$this->_putcmd("PORT", $ip_port);
		$response = $this->_ok();
		if(!$response) {
			$this->_debug_print('ERROR: PORT command failed'."\n");
			return false;
		} //end if
		//--
		return $response;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _pasv() {
		//--
		$this->_putcmd("PASV");
		if(!$this->_ok()) {
			$this->_debug_print('ERROR: PASV command failed'."\n");
			return false;
		} //end if
		//--
		$ip_port = preg_replace("/^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*\r\n$/", "\\1", (string)$this->_resp);
		//--
		return $ip_port;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _putcmd($cmd, $arg='') {
		//--
		if((string)$arg != '') {
			$cmd = $cmd.' '.$arg;
		} //end if
		//--
		if(!$this->_sock) {
			$this->_debug_print('ERROR: CMD command failed: '.$cmd."\n");
			return false;
		} //end if
		//--
		@fwrite($this->_sock, $cmd."\r\n");
		//--
		if($this->debug_level == 'full') {
			//--
			if(SmartUnicode::str_toupper(substr($cmd, 0, 5)) == 'PASS ') {
				$this->_debug_print('# '.'PASS ********'."\n");
			} elseif(SmartUnicode::str_toupper(substr($cmd, 0, 6)) == 'ADMIN ') {
				$this->_debug_print('# '.'ADMIN ********'."\n");
			} else {
				$this->_debug_print('# '.$cmd."\n");
			} //end if else
			//--
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _ok() {
		//--
		$this->_resp = '';
		//--
		for($i=0; $i<3; $i++) {
			//--
			$res = @fgets($this->_sock, 512);
			$this->_resp .= $res;
			//--
			$rstop = SmartUnicode::sub_str($res, 3, 1);
			$rstop_plus = SmartUnicode::sub_str($res, 0, 3);
			//--
			if(is_numeric($rstop_plus)) {
				if((string)$rstop == ' ') {
					$i = 3; // stop
				} else {
					$i = 1; // continue
				} //end if else
			} else {
				$i = 1; // continue
			} //end if else
			//--
		} //end for
		//--
		$this->_debug_print(str_replace("\r\n", "\n", $this->_resp));
		//--
		if(!preg_match("/^[123]/", (string)$this->_resp)) {
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _answer() {
		//--
		$this->_resp = '';
		//--
		for($i=0; $i<3; $i++) {
			//--
			$res = @fgets($this->_sock, 512);
			$this->_resp .= $res;
			//--
			$rstop = SmartUnicode::sub_str($res, 3, 1);
			$rstop_plus = SmartUnicode::sub_str($res, 0, 3);
			//--
			if(is_numeric($rstop_plus)) {
				if((string)$rstop == ' ') {
					$i = 3; // stop
				} else {
					$i = 1; // continue
				} //end else
			} else {
				$i = 1; // continue
			} //end if else
			//--
		} //end for
		//--
		$this->_debug_print(str_replace("\r\n", "\n", $this->_resp));
		//--
		return $this->_resp;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _close_data_connection($sock) {
		//--
		if((string)$this->debug_level == 'full') {
			$this->_debug_print('Closing Data Connection for Channel: '.$sock."\n");
		} //end if
		//--
		if($sock) {
			$out = @fclose($sock);
		} else {
			$out = false;
		} //end if else
		//--
		return $out;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _open_data_connection($ip_port) {
		//--
		if(!preg_match("/[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+/", (string)$ip_port)) {
			$this->_debug_print("Error : Illegal ip-port format(".$ip_port.")\n");
			return false;
		} //end if
		//--
		$res_data = (array) explode(",", (string)$ip_port);
		$ipaddr = $res_data[0].".".$res_data[1].".".$res_data[2].".".$res_data[3];
		$port = $res_data[4]*256 + $res_data[5];
		//--
		$this->_debug_print("Opening Data Connection to ".$ipaddr.":".$port." ...\n");
		//--
		$data_connection = @fsockopen($ipaddr, $port, $errno, $errstr);
		if(!$data_connection) {
			$this->_debug_print('Error : Cannot open data connection to @ '.$ipaddr.':'.$port.' // '.$errstr.' ('.$errno.')'."\n");
			return false;
		} //end if
		//--
		return $data_connection;
		//--
	} //END FUNCTION
	//===================================


	//===================================
	private function _debug_print($message='') {
		//--
		if($this->debug) {
			$this->debug_msg .= $message;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION
	//===================================


} //END CLASS


//============================================================
//============================================================


//======================================================= USAGE
//ftp_connect($server, $port = 21);
//ftp_login($user, $pass);
//ftp_pwd();
//ftp_size($pathname);
//ftp_mdtm($pathname);
//ftp_systype();
//ftp_cdup();
//ftp_chdir($pathname);
//ftp_delete($pathname);
//ftp_rmdir($pathname);
//ftp_mkdir($pathname);
//ftp_file_exists($pathname);
//ftp_rename($from, $to);
//ftp_nlist($arg = "", $pathname = "");
//ftp_rawlist($pathname = "");
//ftp_get($localfile, $remotefile, $mode = 1);
//ftp_put($remotefile, $localfile, $mode = 1);
//ftp_site($command);
//ftp_extcmd($command); // sends ftp raw commands [XNT]
//ftp_quit();
//======================================================= EXAMPLE
/**
	$ftp = new SmartFtpClient();
	$ftp->debug = true;
	$ftp->debug_level = 'full';
	$next = $ftp->ftp_connect('IP.ADDRESS.SERVER', '21');
	if($next) {
		$next = $ftp->ftp_login('username', 'password');
	} //end if
	if($next) {
		//$ftp->ftp_size('/image.jpg');
		//$ftp->ftp_file_exists('/some_folder');
		//$ftp->ftp_mkdir('/some_folder');
		$next = $ftp->ftp_put('/image.jpg', 'local/dir/file122.jpg', 1); // for binary files always use binary !!!
	} //end if
	$ftp->ftp_quit();
	//$ftp->debug_msg;
**/
//=======================================================

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>