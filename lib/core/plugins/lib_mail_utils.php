<?php
// [LIB - SmartFramework / Mail Utils]
// (c) 2006-2018 unix-world.org - all rights reserved
// v.3.7.5 r.2018.03.09 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.3.7')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework - Mail Utils
// DEPENDS:
//	* Smart::
//	* SmartUtils::
//	* SmartFileSysUtils::
//	* SmartFileSystem::
//	* SmartMailerSend::
//	* SmartMailerMimeDecode::
// REQUIRED CSS:
//	* email.css
//======================================================

// [REGEX-SAFE-OK]

//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartMailerUtils - provides various util functions for eMail like: Check/Validate, Send.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartUtils, SmartFileSysUtils, SmartFileSystem, SmartMailerSend
 * @version 	v.181018
 * @package 	Mailer:Utility
 *
 */
final class SmartMailerUtils {

	// ::

//==================================================================
// Validate e-mail address (SMTP)
/**
 * Check eMail Address
 * [PUBLIC]
 *
 * @param STRING $email					:: eMail Address
 * @param ENUM $ycheckdomain			:: 'no' = only validate if email address is in the form of text@texte.xt | 'yes' = check email with MX + SMTP validation
 * @param STRING $helo					:: SMTP HELO (if check MX + Domain will be used, cannot be empty)
 * @param NUMBER $y_smtp_port			:: SMTP Port (normal is '25')
 * @return STRING
 */
public static function check_email_address($email, $ycheckdomain='no', $helo='', $y_smtp_port='25') {

	//--
	$out = 'notok';
	$msg = '';
	//--

	//--
	$email = (string) trim((string)$email);
	//--

	//--
	$regex = SmartValidator::regex_stringvalidation_expression('email').'i'; // insensitive, without /u modifier as it does not decodes to punnycode and must contain only ISO-8859-1 charset
	//--
	if((string)$email != '') {
		if(!preg_match((string)$regex, (string)$email)) { // check if address is valid (match pattern 'email@domain.tld')
			$msg .= 'The e-mail address does NOT match the pattern \'email@domain.tld\''."\n";
		} else {
			$out = 'ok';
		} //end if else
	} else {
		$msg .= 'The e-mail address is empty !'."\n";
	} //end if else
	//--

	//--
	if((string)$out == 'ok') {
		//--
		if((string)$ycheckdomain == 'yes') {
			//--
			$out = 'notok'; // reset
			//--
			if(strlen($helo) <= 0) {
				$helo = '127.0.0.1';
			} //end if else
			//--
			$msg .= "\n".'Now we CHECK if this is a real email address ...'."\n\n";
			$chk = self::validate_mx_email_address($helo, $email, $y_smtp_port);
			//--
			if((string)$chk['status'] == 'ok') {
				$out = 'ok';
			} //end if
			//--
			$msg .= $chk['message']."\n";
			//--
			if(!SmartFrameworkRuntime::ifDebug()) {
				$msg = ''; // hide the message if no debug
			} //end if
			//--
		} //end if
		//--
	} //end if
	//--

	//--
	return array('status'=>(string)$out, 'message'=>(string)$msg);
	//--

} //END FUNCTION
//==================================================================
// $check = SmartMailerUtils::check_email_address('some@email.ext', 'yes', 'mymaildomain.ext');
// # $check['status'] = ok / notok
// # $check['message'] = message
//==================================================================


//================================================================== Do MX Check
/**
 * Does the MX Check of eMail / Domain
 * [PRIVATE]
 *
 * @param STRING $helo					:: SMTP HELO
 * @param STRING $email					:: eMail Address
 * @param NUMBER $y_smtp_port			:: SMTP Port (normal is '25')
 * @return STRING
 */
public static function validate_mx_email_address($helo, $email, $y_smtp_port) {

	// will check all available MX servers from DNS

	//------------
	$out = 'notok';
	$msg = '';
	//------------

	//------------
	$tmp_arr = array();
	$tmp_arr = (array) explode('@', (string)$email);
	$domain = (string) trim((string)$tmp_arr[1]);
	$safedom = (string) Smart::safe_validname($domain);
	$tmp_arr = array();
	//------------
	if(function_exists('getmxrr')) {
		if((string)$safedom != '') {
			@getmxrr($safedom, $tmp_arr); // getmxrr is available also on Windows platforms since PHP 5.3
		} else {
			$msg .= 'WARNING: Empty Safe Domain Name (after-conversion) for: '.$domain;
		} //end if
	} else {
		$msg .= 'WARNING: PHP getmxrr is not implemented on this platform ...';
	} //end if
	//------------
	if(Smart::array_size($tmp_arr) <= 0) {
		//-- ERR
		$msg .= 'WARNING: Invalid MX Records for Domain \''.$safedom.'\''."\n";
		//--
	} else {
		//--
		$msg .= 'List of available MX Servers for Domain \''.$safedom.'\':'."\n";
		//--
		for($m=0; $m<Smart::array_size($tmp_arr); $m++) {
			//--
			$msg .= ' -> '.$tmp_arr[$m]."\n";
			//--
		} //end for
		//--
		$msg .= "\n";
		//--
	} //end if else
	//------------
	$msg .= '[Checking mail address: \''.$email.'\']'."\n";
	//------------
	for($i=0; $i<Smart::array_size($tmp_arr); $i++) {
		//--
		$domain = trim($tmp_arr[$i]);
		$domain_ip = @gethostbyname($domain);
		//--
		$msg .= 'Start MX checking for domain: \''.$domain.'\' :: \''.$domain_ip.'\' ... '."\n";
		//--
		$smtp = new SmartMailerSmtpClient();
		$smtp->timeout = 10;
		$smtp->debug = false;
		$smtp->connect($helo, $domain_ip, $y_smtp_port);
		$vfy = $smtp->mail($email);
		if($vfy) {
			$vfy = $smtp->recipient($email);
		} //end if
		$smtp->quit();
		//--
		if((string)$vfy == '1') {
			//--
			$out = 'ok';
			$msg .= '[done]'."\n";
			//--
			break; //stop
			//--
		} else {
			//--
			$msg .= '[failed]'."\n".'LOG: '."\n".$smtp->log."\n";
			//--
		} //end if else
		//--
		$msg .= $chk['message']."\n";
		//--
		if($i >= 5) {
			break; // do not check more than 5 servers
		} //end if
		//--
	} //end for
	//------------

	//--
	return array('status'=>(string)$out, 'message'=>(string)$msg);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
/**
 * Send Email Mime Message from SmartFramework to a destination with optional log of sent messages to a specific directory
 * It will use the default server settings from configs: $configs['sendmail'][]
 *
 * @param STRING 		$logsend_dir 		A Directory relative path where to store send log messages OR Empty (no store): '' | 'tmp/my-email-send-log-dir'
 * @param STRING/ARRAY 	$to					To: to@addr | [ 'to1@addr', 'to2@addr', ... ]
 * @param STRING/ARRAY 	$cc					Cc: '' | cc@addr | [ 'cc1@addr', 'cc2@addr', ... ]
 * @param STRING 		$bcc				Bcc: '' | bcc@addr
 * @param STRING 		$subj				Subject: Your Subject
 * @param STRING 		$message			Message: The body of the message
 * @param TRUE/FALSE 	$is_html			Format: FALSE = Text/Plain ; TRUE = HTML
 * @param ARRAY 		$attachments		* Attachments array: [] | ['file1.txt'=>'This is the file 1 content', ...] :: default is []
 * @param STRING 		$replytoaddr 		* Reply To Addr: '' | reply-to@addr :: default is ''
 * @param STRING 		$inreplyto			* In Reply To: '' | the ID of message that is replying to :: default is ''
 * @param ENUM			$priority			* Priority: 1=High ; 3=Normal ; 5=Low :: default is 3
 * @param ENUM			$charset			* charset :: default is UTF-8
 * @return TRUE/FALSE	OPERATION RESULT [0 / 1]
 */
public static function send_email($logsend_dir, $to, $cc, $bcc, $subj, $message, $is_html, $attachments=[], $replytoaddr='', $inreplyto='', $priority='3', $charset='UTF-8') {

	//-- Get Default SMTP from configs
	$def_cfg_smtp = (array) Smart::get_from_config('sendmail');
	//-- SMTP connection vars
	$server_settings = [
		'server_name' 		=> (string) $def_cfg_smtp['server-host'],
		'server_port' 		=> (string) $def_cfg_smtp['server-port'],
		'server_sslmode' 	=> (string) $def_cfg_smtp['server-ssl'],
		'server_cafile' 	=> (string) $def_cfg_smtp['server-cafile'],
		'server_auth_user' 	=> (string) $def_cfg_smtp['auth-user'],
		'server_auth_pass' 	=> (string) $def_cfg_smtp['auth-password'],
		'send_from_addr' 	=> (string) $def_cfg_smtp['from-address'],
		'send_from_name' 	=> (string) $def_cfg_smtp['from-name'],
		'smtp_mxdomain' 	=> (string) $def_cfg_smtp['server-mx-domain']
	];
	//--

	//--
	$stmp_y = date('Y');
	$stmp_m = date('m');
	$stmp_d = date('d');
	$stmp_time = date('His');
	//--
	if((string)$def_cfg_smtp['log-messages'] != 'yes') { // no
		$logsend_dir = '';
	} else { // yes
		$logsend_dir = (string) trim((string)$logsend_dir);
	} //end if else
	//--
	if((string)$logsend_dir != '') {
		//--
		$logsend_dir = SmartFileSysUtils::add_dir_last_slash($logsend_dir); // if the last / if not present
		$logsend_dir .= $stmp_y.'/'.$stmp_y.'-'.$stmp_m.'/'.$stmp_y.'-'.$stmp_m.'-'.$stmp_d; // add the time stamps
		$logsend_dir = SmartFileSysUtils::add_dir_last_slash($logsend_dir); // add the last slash finally
		//--
		SmartFileSystem::dir_create($logsend_dir, true); // recursive
		//--
		$tmp_send_mode = 'send-return';
		//--
	} else {
		//--
		$tmp_send_mode = 'send';
		//--
	} //end if else
	//--
	$arr_send_result = (array) self::send_extended_email(
		(array) $server_settings, 	// arr server settings
		(string) $tmp_send_mode, 	// send mode
		$to, // to@addr : MIXED(STRING / ARRAY)
		$cc, // cc@addr : MIXED(STRING / ARRAY)
		(string) $bcc, // bcc@addr
		(string) $subj, // subject
		(string) $message, // message
		(bool) $is_html, // format: is-html ? TRUE : FALSE
		(array) $attachments, // array of attachments
		(string) $replytoaddr, // reply-to@addr
		(string) $inreplyto, // in reply to Msg-Id
		(int) $priority, // msg priority: 1 / 3 / 5
		(string) $charset // msg charset: UTF-8 | ISO-8859-1 | ...
	);
	//--
	if((string)$logsend_dir != '') {
		//--
		if(SmartFileSystem::is_type_dir($logsend_dir)) {
			//--
			if(is_array($to)) {
				$mark_to = '@multi@';
			} else {
				$mark_to = (string) $to;
			} //end if else
			//--
			SmartFileSystem::write($logsend_dir.$stmp_y.$stmp_m.$stmp_d.'_'.$stmp_time.'__'.Smart::safe_validname($mark_to).'__'.sha1($to.$cc.$subj.$message).'.eml', (string)$arr_send_result['message']);
			//--
		} //end if
		//--
	} //end if
	//--

	//--
	return (int) $arr_send_result['result']; // only return the result as 0 for error and 1 for success
	//--

} // END FUNCTION
//==================================================================


//==================================================================
/**
 * Send Email Mime Message from custom MailBox to a destination
 * It can use custom server settings
 *
 * @param ARRAY			$y_server_settings	config array: [ server_name, server_port, server_sslmode, server_auth_user, server_auth_pass, send_from_addr, send_from_name, smtp_mxdomain ]
 * @param ENUM			$y_mode				mode: 'send' = do send | 'send-return' = do send + return | 'return' = return mime formated mail
 * @param STRING/ARRAY 	$to					To: to@addr | [ 'to1@addr', 'to2@addr', ... ]
 * @param STRING/ARRAY 	$cc					Cc: '' | cc@addr | [ 'cc1@addr', 'cc2@addr', ... ]
 * @param STRING 		$bcc				Bcc: '' | bcc@addr
 * @param STRING 		$subj				Subject: Your Subject
 * @param STRING 		$message			Message: The body of the message
 * @param TRUE/FALSE 	$is_html			Format: FALSE = Text/Plain ; TRUE = HTML
 * @param ARRAY 		$attachments		* Attachments array: [] | ['file1.txt'=>'This is the file 1 content', ...] :: default is []
 * @param STRING 		$replytoaddr 		* Reply To Addr: '' | reply-to@addr :: default is ''
 * @param STRING 		$inreplyto			* In Reply To: '' | the ID of message that is replying to :: default is ''
 * @param ENUM			$priority			* Priority: 1=High ; 3=Normal ; 5=Low :: default is 3
 * @param ENUM			$charset			* charset :: default is UTF-8
 * @return ARRAY							OPERATION-RESULT, ERROR, LOG, MIME-MESSAGE
 */
public static function send_extended_email($y_server_settings, $y_mode, $to, $cc, $bcc, $subj, $message, $is_html, $attachments=[], $replytoaddr='', $inreplyto='', $priority=3, $charset='UTF-8') {

	//--
	$y_server_settings = (array) $y_server_settings;
	//--

	//-- SMTP Hello
	$server_helo 	= (string) trim((string)$y_server_settings['smtp_mxdomain']);
	//-- SMTP connection vars
	$server_name 	= (string) trim((string)$y_server_settings['server_name']);
	$server_port 	= (string) trim((string)$y_server_settings['server_port']);
	$server_sslmode = (string) trim((string)$y_server_settings['server_sslmode']);
	$server_cafile 	= (string) trim((string)$y_server_settings['server_cafile']);
	$server_user 	= (string) trim((string)$y_server_settings['server_auth_user']);
	$server_pass 	= (string) trim((string)$y_server_settings['server_auth_pass']);
	//-- SEND FROM
	$send_from_addr = (string) trim((string)$y_server_settings['send_from_addr']);
	$send_from_name = (string) trim((string)$y_server_settings['send_from_name']);
	//--

	//-- mail send class init
	$mail = new SmartMailerSend();
	$mail->usealways_b64 = true;
	//--
	if((string)$server_name == '@mail') {
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('mail', 'SEND', 'Send eMail Method Selected: [MAIL]');
		} //end if
		//-- mail method
		$mail->method = 'mail';
		//--
	} elseif(strlen($server_name) > 0) {
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('mail', 'SEND', 'Send eMail Method Selected: [SMTP]');
		} //end if
		//-- debug
		if(SmartFrameworkRuntime::ifDebug()) {
			$mail->debuglevel = 1; // default is 1
		} else {
			$mail->debuglevel = 0; // no debug
		} //end if else
		//-- smtp server method
		$mail->method = 'smtp';
		$mail->smtp_timeout = '30';
		$mail->smtp_helo = $server_helo;
		$mail->smtp_server = $server_name;
		$mail->smtp_port = $server_port;
		$mail->smtp_ssl = $server_sslmode;
		$mail->smtp_cafile = $server_cafile;
		//--
		if(((string)$server_user == '') OR ((string)$server_pass == '')) {
			$mail->smtp_login = false;
		} else {
			$mail->smtp_login = true;
			$mail->smtp_user = $server_user;
			$mail->smtp_password = $server_pass;
		} //end if
		//--
	} else {
		//--
		if(SmartFrameworkRuntime::ifDebug()) {
			SmartFrameworkRegistry::setDebugMsg('mail', 'SEND', 'Send eMail Method Selected: [NONE] !!!');
		} //end if
		//--
		$mail->method = 'skip';
		//--
	} //end if else
	//--

	//-- charset
	if((string)$charset == '') {
		$charset = 'UTF-8'; // default
	} //end if
	//--
	$mail->charset = (string) $charset;
	//--

	//--
	if((string)$mail->charset != 'UTF-8') { // in this case (ISO-88591 / ISO-8859-2) we deaccent the things for maximum compatibility
		$send_from_name = SmartUnicode::deaccent_str($send_from_name);
		$subj = SmartUnicode::deaccent_str($subj);
		$message = SmartUnicode::deaccent_str($message);
	} //end if
	//--

	//--
	$tmp_explode_arr = (array) explode('@', (string)$send_from_addr);
	$tmp_name = trim($tmp_explode_arr[0]); // not used
	$tmp_domain = trim($tmp_explode_arr[1]); // used for message ID
	//--
	$tmp_my_uid = getmyuid();
	$tmp_my_gid = getmygid();
	//--

	//-- Extra Mail Headers
	$mail->headers = '';
	//-- Errors Reporting Header
	$mail->headers .= 'Errors-To: '.$send_from_addr."\r\n";
	//-- In-Reply-To Header
	if((string)$inreplyto != '') {
		$mail->headers .= 'In-Reply-To: '.$inreplyto."\r\n";
	} //end if else
	//-- Reply-To Header
	if((string)$replytoaddr != '') {
		$mail->headers .= 'Reply-To: '.$replytoaddr."\r\n";
	} //end if
	//-- antiSPAM Header
	$mail->headers .= 'X-AntiAbuse: This header was added to track abuse, please include it with any abuse report'."\r\n";
	$mail->headers .= 'X-AntiAbuse: Primary Hostname - '.$server_helo."\r\n";
	$mail->headers .= 'X-AntiAbuse: Original Domain - '.$server_helo."\r\n";
	$mail->headers .= 'X-AntiAbuse: Originator/Caller UID/GID - [48880 48885] / ['.$tmp_my_uid.' '.$tmp_my_gid.']'."\r\n";
	$mail->headers .= 'X-AntiAbuse: Sender Address Domain - '.$tmp_domain."\r\n";
	//--

	//--
	$mail->priority = Smart::format_number_int($priority, '+'); // high=1 | low=5 | normal=3
	//--

	//-- from
	$mail->from_return = $send_from_addr;
	$mail->from = $send_from_addr;
	$mail->namefrom = $send_from_name;
	//--

	//-- subject
	$mail->subject = $subj;
	//--

	//-- if message is html, include CID imgs as attachments
	if(((string)$y_mode != 'return') AND ($is_html)) {
		//-- init
		$arr_links = array();
		//-- embedd all images
		$htmlparser = new SmartHtmlParser($message);
		$htmlparser->get_clean_html(); // to be tested ...
		$arr_links = $htmlparser->get_tags('img');
		$htmlparser = '';
		unset($htmlparser);
		//--
		$chk_duplicates_arr = array();
		$uniq_id = 0;
		//--
		for($i=0; $i<Smart::array_size($arr_links); $i++) {
			//--
			$tmp_original_img_link = trim($arr_links[$i][src]); // trim any possible spaces
			//-- reverse the &amp; back to & (generated from JavaScript) ...
			$tmp_imglink = str_replace('&amp;', '&', (string)$tmp_original_img_link);
			//--
			$tmp_cid = 'img_'.sha1('SmartFramework eMail-Utils // CID Embedd // '.'@'.$tmp_imglink.'#'); // this should not vary by $i or others because if duplicate images are detected only the first is attached
			//--
			if(strlen($chk_duplicates_arr[$tmp_cid]) <= 0) { // avoid browse twice the same image
				//--
				$tmp_original_lnk = (string) $tmp_imglink;
				$tmp_eval_link = (string) $tmp_imglink;
				$tmp_allow_credentials = 'no';
				if(substr($tmp_original_lnk, 0, 10) == 'admin.php?') {
					$tmp_original_lnk = (string) SmartUtils::get_server_current_url().$tmp_imglink;
					$tmp_allow_credentials = 'yes'; // in the case we have embedded pictures generated by admin.php who always need authentication to work, we have to send credentials too
					$tmp_eval_link = ''; // we clear to re-eval
				} elseif((SmartUnicode::sub_str($tmp_original_lnk, 0, SmartUnicode::str_len(SmartUtils::get_server_current_url().'admin.php?')) == SmartUtils::get_server_current_url().'admin.php?') AND ((substr($tmp_original_lnk, 0, 7) == 'http://') OR (substr($tmp_original_lnk, 0, 8) == 'https://'))) {
					$tmp_allow_credentials = 'yes'; // in the case we have embedded pictures generated by admin.php who always need authentication to work, we have to send credentials too
					$tmp_eval_link = ''; // we clear to re-eval
				} elseif((substr($tmp_original_lnk, 0, 10) == 'index.php?') OR (substr($tmp_original_lnk, 0, 1) == '?')) {
					$tmp_original_lnk = (string) SmartUtils::get_server_current_url().$tmp_imglink;
					$tmp_eval_link = ''; // we clear to re-eval
				} elseif((SmartUnicode::sub_str($tmp_original_lnk, 0, SmartUnicode::str_len(SmartUtils::get_server_current_url().'index.php?')) == SmartUtils::get_server_current_url().'index.php?') AND ((substr($tmp_original_lnk, 0, 7) == 'http://') OR (substr($tmp_original_lnk, 0, 8) == 'https://'))) {
					$tmp_eval_link = ''; // we clear to re-eval
				} elseif((SmartUnicode::sub_str($tmp_original_lnk, 0, SmartUnicode::str_len(SmartUtils::get_server_current_url().'?')) == SmartUtils::get_server_current_url().'?') AND ((substr($tmp_original_lnk, 0, 7) == 'http://') OR (substr($tmp_original_lnk, 0, 8) == 'https://'))) {
					$tmp_eval_link = ''; // we clear to re-eval
				} //end if
				//--
				$tmp_browse_arr = array();
				$tmp_browse_arr = SmartUtils::load_url_or_file($tmp_original_lnk, SMART_FRAMEWORK_NETSOCKET_TIMEOUT, 'GET', '', '', '', $tmp_allow_credentials); // [OK]
				//Smart::log_notice(print_r($tmp_browse_arr,1));
				//--
				$guess_arr = array();
				$guess_arr = SmartUtils::guess_image_extension_by_url_head($tmp_browse_arr['headers']);
				$tmp_img_ext = (string) $guess_arr['extension'];
				$tmp_where_we_guess = (string) $guess_arr['where-was-detected'];
				//Smart::log_notice('Guess Ext by URL Head: '.$tmp_browse_arr['headers']."\n".'### '.print_r($guess_arr,1)."\n".'#');
				if((string)$tmp_img_ext == '') {
					$tmp_img_ext = SmartUtils::guess_image_extension_by_first_bytes(substr($tmp_browse_arr['content'], 0, 16)); // needs 1st 16 bytes
					if((string)$tmp_img_ext != '') {
						$tmp_where_we_guess = ' First Bytes ...';
					} //end if
				} //end if
				//Smart::log_notice('Guess Ext by First Bytes: '.$tmp_img_ext."\n".'#');
				if((string)$tmp_eval_link == '') {
					$tmp_eval_link = 'file'.$tmp_img_ext;
				} //end if
				//--
				$tmp_fcontent = '';
				if(((string)$tmp_browse_arr['result'] == '1') AND ((string)$tmp_browse_arr['code'] == '200')) {
					if(((string)$tmp_img_ext == '') OR ((string)$tmp_img_ext == '.png') OR ((string)$tmp_img_ext == '.gif') OR ((string)$tmp_img_ext == '.jpg')) {
						$tmp_fcontent = (string) $tmp_browse_arr['content'];
					} //end if
				} //end if else
				//--
				if(strlen($tmp_fcontent) > 0) {
					//--
					$tmp_arr_fmime = array();
					$tmp_arr_fmime = (array) SmartFileSysUtils::mime_eval($tmp_eval_link);
					//--
					$tmp_fmime = (string) $tmp_arr_fmime[0];
					if(((string)$tmp_fmime == '') OR ((string)$tmp_fmime == 'application/octet-stream')) {
						$tmp_fmime = 'image'; // in the case of CIDS we already pre-validated the images
					} //end if
					$tmp_fname = (string) 'cid_'.$uniq_id.'__'.$tmp_cid.$tmp_img_ext;
					//--
					$mail->add_attachment($tmp_fcontent, $tmp_fname, $tmp_fmime, 'inline', $tmp_cid.$tmp_img_ext); // attachment
					$message = str_replace('src="'.$tmp_original_img_link.'"', 'src="cid:'.$tmp_cid.$tmp_img_ext.'"', $message);
					//--
					$uniq_id += 1;
					//--
				} //end if
				//--
				$chk_duplicates_arr[$tmp_cid] = 'embedd';
				//--
			} //end if
			//--
		} //end for
		//-- clean
		$chk_duplicates_arr = array();
		$uniq_id = 0;
		$tmp_original_img_link = '';
		$tmp_imglink = '';
		$tmp_cid = '';
		$tmp_browse_arr = array();
		$tmp_fcontent = '';
		$tmp_arr_fmime = array();
		$tmp_fmime = '';
		$tmp_fname = '';
		//--
	} //end if
	//--

	//-- message body
	$mail->is_html = $is_html; // false | true
	$mail->body = $message;
	//--
	$message = '';
	unset($message);
	//--

	//-- attachments
	if(is_array($attachments)) {
		if(Smart::array_size($attachments) > 0) {
			//while(list($key, $val) = @each($attachments)) { // Fix: this is deprecated as of PHP 7.2
			foreach($attachments as $key => $val) {
				//--
				$tmp_arr_fmime = array();
				$tmp_arr_fmime = (array) SmartFileSysUtils::mime_eval($key);
				//--
				$mail->add_attachment($val, $key, (string)$tmp_arr_fmime[0], 'attachment', '', 'yes'); // force as real attachments
				//--
			} //end while
		} //end if
	} //end if
	//--

	//--
	switch((string)$y_mode) {
		case 'return':
			//--
			$mail->to = '[::!::]';
			$mail->cc = '';
			//-- only return mime formated message
			$mail->send('no');
			return array('result' => 1, 'error' => '', 'log' => '', 'message' => $mail->mime_message);
			//--
			break;
		case 'send-return':
		case 'send':
		default:
			//--
			$out = 0;
			//--
			$arr_to = array();
			if(!is_array($to)) {
				$arr_to[] = (string) $to;
				$tmp_send_to = (string) $to;
			} else {
				$arr_to = (array) $to;
				if(Smart::array_size($arr_to) > 1) {
					$tmp_send_to = '[::@::]'; // multi message
				} else {
					$tmp_send_to = (string) $arr_to[0];
				} //end if else
			} //end if else
			//--
			$tmp_send_log = '';
			$tmp_send_log .= '-----------------------------------------------------------------------'."\n";
			$tmp_send_log .= 'Smart / eMail Send Log :: '.$send_from_addr.' ['.$send_from_name.']'."\n";
			$tmp_send_log .= $server_sslmode.'://'.$server_name.':'.$server_port.' # '.$server_user.' :: '.$server_helo."\n";
			$tmp_send_log .= '-----------------------------------------------------------------------'."\n";
			//--
			$counter_sent = 0;
			for($i=0; $i<Smart::array_size($arr_to); $i++) {
				//--
				$arr_to[$i] = trim($arr_to[$i]);
				//--
				if(strlen($arr_to[$i]) > 0) {
					//--
					$mail->to = (string) $arr_to[$i];
					//--
					$mail->cc = $cc; // can be string or array
					//--
					$mail->bcc = (string) $bcc;
					//--
					$tmp_send_log .= '#'.($i+1).'. To: \''.$arr_to[$i].'\' :: '.date('Y-m-d H:i:s O');
					//-- real send
					if(((string)$mail->method == 'mail') OR ((string)$mail->method == 'smtp')) {
						$err = $mail->send('yes');
						if(SmartFrameworkRuntime::ifDebug()) {
							SmartFrameworkRegistry::setDebugMsg('mail', 'SEND', '[##### Send eMail Log #'.($i+1).': '.date('Y-m-d H:i:s').' #####]');
						} //end if
					} else {
						$err = 'WARNING: SMTP Server or Mail Method IS NOT SET in CONFIG. Send eMail - Operation ABORTED !';
					} //end if else
					//--
					if(SmartFrameworkRuntime::ifDebug()) {
						SmartFrameworkRegistry::setDebugMsg('mail', 'SEND', '========== SEND TO: '.$arr_to[$i].' =========='."\n".'ERRORS: '.$err."\n".'=========='."\n".$mail->log."\n".'========== # ==========');
					} //end if
					//--
					if(strlen($err) > 0) {
						$tmp_send_log .= ' :: ERROR:'."\n".$arr_to[$i]."\n".$err."\n";
					} else {
						$counter_sent += 1;
						$tmp_send_log .= ' :: OK'."\n";
					} //end if else
					//--
					if($i > 10000) {
						break; // hard limit
					} //end if
					//--
				} //end if
				//--
			} //end for
			//--
			if($counter_sent > 0) {
				$out = 1;
			} //end if
			//--
			$tmp_send_log .= '-----------------------------------------------------------------------'."\n\n";
			if(SmartFrameworkRuntime::ifDebug()) {
				SmartFrameworkRegistry::setDebugMsg('mail', 'SEND', 'Send eMail Operations Log: '.$tmp_send_log);
			} //end if
			//--
			if((string)$y_mode == 'send-return') {
				//--
				$mail->to = $tmp_send_to;
				if(is_array($cc)) {
					$mail->cc = (string) implode(', ', $cc);
				} elseif((string)$cc != '') {
					$mail->cc = (string) $cc;
				} //end if else
				$mail->add_attachment($tmp_send_log, 'smart-email-send.log', 'text/plain', 'inline');
				$mail->send('no');
				return array('result' => (int)$out, 'error' => (string)$err, 'log' => (string)$tmp_send_log, 'message' => (string)$mail->mime_message);
				//--
			} else {
				//--
				return array('result' => (int)$out, 'error' => (string)$err, 'log' => (string)$tmp_send_log, 'message' => ''); // skip returning the message
				//--
			} //end if else
			//--
	} //end switch
	//--

} // END FUNCTION
//==================================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartMailerMimeParser - provides an easy to use eMail MIME Parser.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	classes: Smart, SmartUtils, SmartFileSysUtils, SmartFileSystem, SmartMailerMimeDecode
 * @version 	v.181018
 * @package 	Mailer:Utility
 *
 */
final class SmartMailerMimeParser {

	// ::


//==================================================================
public static function encode_mime_fileurl($y_msg_file, $y_ctrl_key) {
	//--
	$y_msg_file = (string) trim((string)$y_msg_file);
	if((string)$y_msg_file == '') {
		Smart::log_warning('Mail-Utils / Encode Mime File URL: Empty Message File Path has been provided. This means the URL link will be unavaliable (empty) to assure security protection.');
		return '';
	} //end if
	if(!SmartFileSysUtils::check_if_safe_path($y_msg_file)) {
		Smart::log_warning('Mail-Utils / Encode Mime File URL: Invalid Message File Path has been provided. This means the URL link will be unavaliable (empty) to assure security protection. Message File: '.$y_msg_file);
		return '';
	} //end if
	//--
	$y_ctrl_key = (string) trim((string)$y_ctrl_key);
	if((string)$y_ctrl_key == '') {
		Smart::log_warning('Mail-Utils / Encode Mime File URL: Empty Controller Key has been provided. This means the URL link will be unavaliable (empty) to assure security protection.');
		return '';
	} //end if
	if(SMART_FRAMEWORK_ADMIN_AREA === true) { // {{{SYNC-ENCMIMEURL-CTRL-PREFIX}}}
		$y_ctrl_key = (string) 'AdminMailUtilArea/'.$y_ctrl_key;
	} else {
		$y_ctrl_key = (string) 'IndexMailUtilArea/'.$y_ctrl_key;
	} //end if
	//--
	$crrtime = (int) time();
	$access_key = sha1('MimeLink:'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.SMART_APP_VISITOR_COOKIE.':'.$y_msg_file.'>'.$y_ctrl_key);
	$unique_key = sha1('Time='.$crrtime.'#'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.$access_key.'-'.SmartUtils::unique_auth_client_private_key().':'.$y_msg_file.'>'.$y_ctrl_key);
	$self_robot_key = sha1('Time='.$crrtime.'#'.SmartAuth::get_login_id().'*'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.SmartUtils::get_selfrobot_useragent_name().'$'.$access_key.':'.$y_msg_file.'>'.$y_ctrl_key);
	//-- {{{SYNC-MIME-ENCRYPT-ARR}}}
	$safe_link = SmartUtils::crypto_encrypt(
		trim((string)$crrtime)."\n". 			// current time stamp
		trim((string)$y_msg_file)."\n". 		// file
		trim((string)$access_key)."\n". 		// access key based on UniqueID cookie
		trim((string)$unique_key)."\n". 		// unique key based on: AuthUserID, User-Agent and IP
		trim((string)$self_robot_key)."\n", 		// self robot browser UserAgentName/ID key
		'SmartFramework//MimeLink'.SMART_FRAMEWORK_SECURITY_KEY
	);
	//--
	return (string) $safe_link;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
// It takes in account if the User is Authenticated or not
public static function decode_mime_fileurl($y_enc_msg_file, $y_ctrl_key) {
	//--
	$y_enc_msg_file = (string) trim((string)$y_enc_msg_file);
	if((string)$y_enc_msg_file == '') {
		Smart::log_warning('Mail-Utils / Decode Mime File URL: Empty Message File Path has been provided. This means the URL link will be unavaliable (empty) to assure security protection.');
		return '';
	} //end if
	if(!SmartFileSysUtils::check_if_safe_path($y_enc_msg_file)) {
		Smart::log_warning('Mail-Utils / Decode Mime File URL: Invalid Message File Path has been provided. This means the URL link will be unavaliable (empty) to assure security protection. Message File: '.$y_enc_msg_file);
		return '';
	} //end if
	//--
	$y_ctrl_key = (string) trim((string)$y_ctrl_key);
	if((string)$y_ctrl_key == '') {
		Smart::log_warning('Mail-Utils / Decode Mime File URL: Empty Controller Key has been provided. This means the URL link will be unavaliable (empty) to assure security protection.');
		return '';
	} //end if
	if(SMART_FRAMEWORK_ADMIN_AREA === true) { // {{{SYNC-ENCMIMEURL-CTRL-PREFIX}}}
		$y_ctrl_key = (string) 'AdminMailUtilArea/'.$y_ctrl_key;
	} else {
		$y_ctrl_key = (string) 'IndexMailUtilArea/'.$y_ctrl_key;
	} //end if
	//--
	$the_sep_arr = (array) self::mime_separe_part_link($y_enc_msg_file);
	$y_enc_msg_file = (string) $the_sep_arr['msg'];
	$the_msg_part = (string) $the_sep_arr['part'];
	unset($the_sep_arr);
	//--
	$arr = array(); // {{{SYNC-MIME-ENCRYPT-ARR}}}
	$arr['error'] = ''; // by default, no error
	//--
	if((string)SMART_APP_VISITOR_COOKIE == '') {
		$arr['error'] = 'WARNING: Access Forbidden ... No Visitor ID set ...!';
		return (array) $arr;
	} //end if
	//--
	if((string)$the_msg_part != '') {
		$the_msg_part = strtolower(trim((string)SmartUtils::url_hex_decode((string)$the_msg_part)));
	} //end if
	//--
	$decoded_link =  trim((string)SmartUtils::crypto_decrypt(
		(string)$y_enc_msg_file,
		'SmartFramework//MimeLink'.SMART_FRAMEWORK_SECURITY_KEY
	));
	$dec_arr = (array) explode("\n", trim((string)$decoded_link));
	//print_r($dec_arr);
	//--
	$arr['creation-time'] 	= trim((string)$dec_arr[0]);
	$arr['message-file'] 	= trim((string)$dec_arr[1]);
	$arr['message-part'] 	= trim((string)$the_msg_part);
	$arr['access-key'] 		= trim((string)$dec_arr[2]);
	$arr['bw-unique-key'] 	= trim((string)$dec_arr[3]);
	$arr['sf-robot-key']	= trim((string)$dec_arr[4]);
	//-- check if file path is valid
	if((string)$arr['message-file'] == '') {
		$arr = array();
		$arr['error'] = 'ERROR: Empty Message Path ...';
		return (array) $arr;
	} //end if
	if(!SmartFileSysUtils::check_if_safe_path($arr['message-file'])) {
		$arr = array();
		$arr['error'] = 'ERROR: Unsafe Message Path Access ...';
		return (array) $arr;
	} //end if
	//--
	$browser_os_ip_identification = SmartUtils::get_os_browser_ip(); // get browser and os identification
	//-- re-compose the access key
	$crrtime = (int) $arr['creation-time'];
	$access_key = sha1('MimeLink:'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.SMART_APP_VISITOR_COOKIE.':'.$arr['message-file'].'>'.$y_ctrl_key);
	$uniq_key = sha1('Time='.$crrtime.'#'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.$access_key.'-'.SmartUtils::unique_auth_client_private_key().':'.$arr['message-file'].'>'.$y_ctrl_key);
	$self_robot_key = sha1('Time='.$crrtime.'#'.SmartAuth::get_login_id().'*'.SMART_SOFTWARE_NAMESPACE.'-'.SMART_FRAMEWORK_SECURITY_KEY.'-'.trim($browser_os_ip_identification['signature']).'$'.$access_key.':'.$arr['message-file'].'>'.$y_ctrl_key);
	//-- check access key
	if((string)$arr['error'] == '') {
		if((string)$access_key != (string)$arr['access-key']) {
			$arr = array();
			$arr['error'] = 'ERROR: Access Forbidden ... Invalid ACCESS KEY ...';
		} //end if
	} //end if
	//-- check the client key
	if((string)$arr['error'] == '') {
		//--
		$ok_client_key = false;
		//--
		if(((string)$the_msg_part == '') AND ((string)$arr['bw-unique-key'] == (string)$uniq_key)) { // no message part, allow only client browser
			$ok_client_key = true;
		} elseif(((string)$the_msg_part != '') AND (((string)$arr['bw-unique-key'] == (string)$uniq_key) OR (((string)$browser_os_ip_identification['bw'] == '@s#') AND ((string)$arr['sf-robot-key'] == (string)$self_robot_key)))) {
			$ok_client_key = true;
		} else {
			$ok_client_key = false;
		} //end if else
		//--
		if($ok_client_key != true) {
			$arr = array();
			$arr['error'] = 'ERROR: Access Forbidden ... Invalid CLIENT KEY ...';
		} //end if
		//--
	} //end if
	//--
	return (array) $arr;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
public static function display_message($y_enc_msg_file, $y_ctrl_key, $y_link, $y_target='', $y_title='', $y_process_mode='', $y_show_headers='') {

	//--
	if((string)$y_process_mode != 'print') {
		$y_process_mode = 'default';
	} //end if
	if((string)$y_show_headers != 'subject') {
		$y_show_headers = 'default';
	} //end if
	if((string)$y_target == '') {
		$y_target = '_blank';
	} //end if
	//--

	//--
	return (string) self::read_mime_message($y_enc_msg_file, $y_ctrl_key, $y_process_mode, $y_show_headers, $y_title, $y_link, $y_target);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
public static function get_message_data_structure($y_enc_msg_file, $y_ctrl_key, $y_process_mode, $y_link='', $y_target='') {

	//--
	if((string)$y_process_mode != 'data-reply') {
		$y_process_mode = 'data-full';
	} //end if
	//--

	//--
	return (array) self::read_mime_message($y_enc_msg_file, $y_ctrl_key, $y_process_mode, '', '', $y_link, $y_target);
	//--

} //END FUNCTION
//==================================================================


//==================================================================
// the link can be empty as '' just for 'reply' process mode when forwards
// for the rest of cases the link is something like: yourscript?page=your.action&your_url_param_message={{{MESSAGE}}}&your_url_param_rawmode={{{RAWMODE}}}&your_url_param_mime={{{MIME}}}&your_url_param_disp={{{DISP}}}
// [PRIVATE]
private static function read_mime_message($y_enc_msg_file, $y_ctrl_key, $y_process_mode, $y_show_headers, $y_title, $y_link, $y_target) {

	// $y_process_mode : 'default' | 'print' | 'data-full' | 'data-reply'
	// $y_show_headers : 'default' | 'subject' (just for mode: 'default' | 'print')

	//--
	$msg_decode_arr = (array) self::decode_mime_fileurl((string)$y_enc_msg_file, (string)$y_ctrl_key);
	//--
	if((string)$msg_decode_arr['error'] != '') {
		Smart::raise_error(
			'ERROR: MIME Parser // Mesage File Decode: '.$msg_decode_arr['error'],
			'ERROR: MIME Parser // Mesage File Decode // See error log for details ...'
		);
		return '';
	} //end if
	//--

	//--
	$the_message_eml = (string) trim((string)$msg_decode_arr['message-file']);
	$the_part_id = (string) trim((string)$msg_decode_arr['message-part']);
	//--

	//--
	if(((string)$the_message_eml == '') OR (!SmartFileSystem::is_type_file((string)$the_message_eml))) {
		Smart::raise_error(
			'ERROR: MIME Parser // Message File EMPTY or NOT FOUND !: '.$the_message_eml,
			'ERROR: MIME Parser // Mesage File Decode // See error log for details ...'
		);
		return '';
	} //end if
	//--
	if(substr((string)$the_message_eml, -4, 4) != '.eml') {
		Smart::raise_error(
			'ERROR: MIME Parser // Message File Extension is not .eml !: '.$the_message_eml,
			'ERROR: MIME Parser // Mesage File Decode // See error log for details ...'
		);
		return '';
	} //end if
	//--

	//--
	$out = ''; // init
	$reply_text = array(); // init
	//--

	//==
	//--
	$content = SmartFileSystem::read((string)$the_message_eml);
	$eml = new SmartMailerMimeDecode();
	$head = $eml->get_header(SmartUnicode::sub_str((string)$content, 0, 65535)); // some messages fail with 8192 to decode ; a faster compromise would be 16384, but here we can use a higher value since is done once (text 65535)
	$msg = $eml->get_bodies((string)$content, (string)$the_part_id);
	unset($eml);
	unset($content);
	//--
	//==

	//--
	$reg_atts_num = 0;
	$reg_atts_list = ''; // list separed by \n
	//--
	if(strlen($the_part_id) <= 0) {
		//-- display whole message
		$reg_is_part = 'no';
		$skip_part_processing = 'no';
		$skip_part_linking = 'no';
		//--
	} else {
		//-- display only a part of the message
		$reg_is_part = 'yes';
		$skip_part_processing = 'no';
		$skip_part_linking = 'yes';
		//--
		if(substr($the_part_id, 0, 4) == 'txt_') {
			//-- text part
			$tmp_part = $msg['texts'][$the_part_id];
			$msg = array();
			$msg['texts'][$the_part_id] = (array) $tmp_part;
			unset($tmp_part);
			//--
		} else {
			//-- att / cid part
			$skip_part_processing = 'yes';
			//--
			if(!is_array($msg['attachments'][$the_part_id])) { // try to normalize name
				$the_part_id = trim(str_replace(' ', '', $the_part_id));
			} //end if
			//--
			$out = (string) $msg['attachments'][$the_part_id]['content']; // DO NO MORE ADD ANYTHING TO $out ... downloading, there are no risk of code injection
			//--
		} //end if else
		//--
	} //end if else
	//--

	//--
	if((string)$y_process_mode == 'print') {
		$skip_part_linking = 'yes'; // skip links to other sub-parts like texts / attachments but not cids !
	} elseif((string)$y_process_mode == 'data-reply') {
		$skip_part_linking = 'yes';
	} //end if
	//--

	//--
	if((string)$skip_part_processing != 'yes') {
		//--
		if((string)$y_title != '') {
			$out .= (string) $y_title; // expects '' or valid HTML
		} //end if
		//--
		$out .= '<!-- Smart.Framework // MIME MESSAGE HTML --><div align="left"><div id="mime_msg_box">';
		//--
		if(strlen($the_part_id) <= 0) {
			//--
			$priority_img = '';
			switch((string)$head['priority']) {
				case '1': // high
					$priority_img = '<img src="lib/core/plugins/img/email/priority-high.svg" align="left" alt="High Priority" title="High Priority">';
					break;
				case '5': // low
					$priority_img = '<img src="lib/core/plugins/img/email/priority-low.svg" align="left" alt="Low Priority" title="Low Priority">';
					break;
				case '3': // medium
				default:
					//$priority_img = '';
					$priority_img = '<img src="lib/core/plugins/img/email/priority-normal.svg" align="left" alt="Normal Priority" title="Normal Priority">';
			} //end switch
			//--
			if((string)$skip_part_linking != 'yes') { // avoid display the print link when only a part is displayed
				$out .= '<a href="'.self::mime_link($y_ctrl_key, $the_message_eml, $the_part_id, $y_link, $eval_arr[0], $eval_arr[1], 'print').'" target="'.$y_target.'__mimepart" data-smart="open.modal">'.'<img align="right" src="lib/core/plugins/img/email/bttn-print.svg" title="Print" alt="Print">'.'</a>';
			} //end if
			//--
			switch((string)$y_show_headers) {
				case 'subject':
					//--
					if((string)$head['subject'] != '[?]') {
						$out .= '<h1><font size="4">'.Smart::escape_html($head['subject']).'</font></h1><br>';
					} //end if
					//--
					break;
				case 'default':
				default:
					//--
					if((string)$head['subject'] != '[?]') {
						$out .= '<h1><font size="4">&nbsp;'.Smart::escape_html($head['subject']).'</font>'.$priority_img.'</h1><hr>';
					} //end if
					//--
					if((string)$head['date'] != '(?)') {
						$out .= '<font size="3"><b>Date:</b> '.Smart::escape_html(date('Y-m-d H:i:s O', @strtotime($head['date']))).'</font><br>';
					} //end if
					//--
					$out .= '<font size="2"><b>From:</b> '.Smart::escape_html($head['from_addr']).' &nbsp; <i>'.Smart::escape_html($head['from_name']).'</i>'.'</font><br>';
					$out .= '<font size="2"><b>To:</b> '.Smart::escape_html($head['to_addr']).' &nbsp; <i>'.Smart::escape_html($head['to_name']).'</i>'.'</font><br>';
					//--
					if(strlen($head['cc_addr']) > 0) {
						$out .= '<font size="2"><b>Cc:</b> ';
						if(SmartUnicode::str_contains($head['cc_addr'], ',')) {
							$arr_cc_addr = (array) explode(',', (string)$head['cc_addr']);
							$arr_cc_name = (array) explode(',', (string)$head['cc_name']);
							$out .= '[@]';
							for($z=0; $z<Smart::array_size($arr_cc_addr); $z++) {
								$out .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.Smart::escape_html(trim($arr_cc_addr[$z])).' &nbsp; <i>'.Smart::escape_html(trim($arr_cc_name[$z])).'</i>';
							} //end for
						} else {
							$out .= Smart::escape_html($head['cc_addr']).' &nbsp; <i>'.Smart::escape_html($head['cc_name']).'</i>';
						} //end if else
						$out .= '</font><br>';
					} //end if
					//--
					if(strlen($head['bcc_addr']) > 0) {
						$out .= '<font size="2"><b>Bcc:</b> ';
						$out .= Smart::escape_html($head['bcc_addr']).' &nbsp; <i>'.Smart::escape_html($head['bcc_name']).'</i>';
						$out .= '</font><br>';
					} //end if
					//--
			} //end switch
			//-- print attachments
			if(is_array($msg['attachments'])) {
				//--
				$cnt=0;
				//--
				$atts = ''; // atts with link
				$xatts = ''; // atts without link
				//--
				$tmp_att_img = '<img src="lib/core/plugins/img/email/attachment.svg">';
				//--
				foreach ($msg['attachments'] as $key => $val) {
					//--
					$tmp_arr = array();
					$tmp_arr = (array) $val;
					//--
					if((string)$tmp_arr['mode'] == 'normal') {
						//--
						$cnt += 1;
						//--
						$eval_arr = SmartFileSysUtils::mime_eval((string)$tmp_arr['filename']);
						$tmp_att_name = Smart::escape_html((string)$tmp_arr['filename']);
						$tmp_att_size = Smart::escape_html((string)SmartUtils::pretty_print_bytes((int)$tmp_arr['filesize'], 1));
						//--
						$reg_atts_num += 1;
						$reg_atts_list .= str_replace(array("\r", "\n", "\t"), array('', '', ''), (string)$tmp_arr['filename'])."\n";
						//--
						$atts .= '<div align="left"><table border="0" cellpadding="2" cellspacing="0" title="Attachment #'.$cnt.'"><tr><td>'.$tmp_att_img.'</td><td>&nbsp;</td><td><a href="'.self::mime_link($y_ctrl_key, $the_message_eml, $key, $y_link, $eval_arr[0], $eval_arr[1]).'" target="'.$y_target.'__mimepart" data-smart="open.modal"><font size="1"><b>'.$tmp_att_name.'</b></font></a></td><td><font size="1"> &nbsp;<b><i>'.$tmp_att_size.'</i></b></font></td></tr></table></div>';
						$xatts .= '<div align="left">'.$tmp_att_img.'&nbsp;&nbsp;<font size="1">'.$tmp_att_name.'&nbsp;&nbsp;<i>'.$tmp_att_size.'</i></font></div>';
						//--
					} //end if
					//--
				} //end foreach
				//--
				if($cnt > 0) {
					if((string)$skip_part_linking == 'yes') { // avoid displaying attachments links when only a part is displayed
						$out .= '<hr><div align="left">'.$xatts.'</div>';
					} else {
						$out .= '<hr><div align="left">'.$atts.'</div>';
					} //end if
				} //end if
				//--
				$tmp_att_name = '';
				$tmp_att_size = '';
				//--
				$atts = '';
				$xatts = '';
				//--
			} //end if
			//--
		} else {
			//--
			$out .= '<div align="right"><font size="1">'.Smart::escape_html($head['subject']).' // '.'MIME Part ID : <i>'.Smart::escape_html($the_part_id).'</i></font></div>';
			//--
		} //end if
		//-- print text bodies
		$markup_multipart = 'This is a multi-part message in MIME format.';
		if(is_array($msg['texts'])) {
			//-- check similarity and prepare the HTML parts
			$buff = '';
			$buff_id = '';
			$xbuff = '';
			$xbuff_id = '';
			$skips = array();
			$numparts = 0;
			foreach($msg['texts'] as $key => $val) {
				//--
				$numparts += 1;
				//--
				if((string)$val['type'] == 'text') { // assure we don't print other things
					//--
					if((string)$val['mode'] == 'text/plain') { // Plain TEXT
						//-- sanitize text
						$val['content'] = '<!-- MIMEREAD:PART:TEXT -->'.Smart::escape_html($val['content']);
						$val['content'] = str_replace(array("\r\n", "\r", "\n"), array("\n", "\n", '<br>'), $val['content']);
						$val['content'] = SmartParser::text_urls($val['content']);
						//--
						$msg['texts'][$key]['content'] = $val['content']; // rewrite back
						//-- assign buffer
						$buff = SmartUnicode::sub_str($val['content'], 0, 16384);
						$buff_id = $key;
						//--
						$percent_similar = 0;
						if(strlen($the_part_id) <= 0) {
							@similar_text($buff, $markup_multipart, $percent_similar);
							if($percent_similar >= 25) { // 25% at least similarity
								$skips[$buff_id] = $percent_similar; // skip this alternate html part ...
							} //end if
						} //end if
						//--
						// clean buffer
						$xbuff = '';
						$xbuff_id = '';
						//--
					} else { // HTML Parts :: check similarity
						//--
						$val['content'] = '<!-- MIMEREAD:PART:HTML -->'.preg_replace("'".'<\?xml'.".*?".'>'."'si", " ", (string)$val['content']); // remove always fake "< ?" as "< ?xml" (fixed with /u modifier for unicode strings)
						//--
						if((SmartUnicode::str_contains($val['content'], '<'.'?')) OR (SmartUnicode::str_contains($val['content'], '?'.'>')) OR (SmartUnicode::str_contains($val['content'], '<'.'%')) OR (SmartUnicode::str_contains($val['content'], '%'.'>'))) {
							//--
							$val['content'] = @highlight_string($val['content'], 1); // highlight the PHP* code & sanitize the parts
							//--
						} else {
							//-- sanitize this html part
							$val['content'] = (new SmartHtmlParser($val['content']))->get_clean_html();
							//-- replace cid images
							$tmp_matches = array();
							preg_match_all('/<img[^>]+src=[\'"]?(cid:)([^\'"]*)[\'"]?[^>]*>/si', (string)$val['content'], $tmp_matches); // fix: previous was just i (not si) ; modified on 160205
							// $tmp_matches[0][i] : the full link
							// $tmp_matches[1][i] : 'cid:'
							// $tmp_matches[2][i] : cid part id
							for($cids=0; $cids<Smart::array_size($tmp_matches[0]); $cids++) {
								$tmp_replace_cid_link = '';
								$tmp_replace_cid_link = (string)$tmp_matches[0][$cids];
								$tmp_replace_cid_link = str_replace("\n", ' ', $tmp_replace_cid_link);
								$tmp_replace_cid_link = str_replace($tmp_matches[1][$cids].$tmp_matches[2][$cids], self::mime_link($y_ctrl_key, $the_message_eml, 'cid_'.$tmp_matches[2][$cids], $y_link, 'image', 'inline'), $tmp_replace_cid_link);
								//echo '<pre>'.Smart::escape_html($tmp_replace_cid_link).'</pre>';
								$val['content'] = str_replace($tmp_matches[0][$cids], $tmp_replace_cid_link, $val['content']);
							} //end for
							$tmp_matches = array();
							//--
						} //end if else
						//--
						$msg['texts'][$key]['content'] = $val['content']; // rewrite back
						//--
						$xbuff = SmartUnicode::sub_str(Smart::striptags($val['content']), 0, 16384);
						$xbuff_id = $key;
						//--
						$percent_similar = 0;
						if(strlen($the_part_id) <= 0) {
							@similar_text($buff, $xbuff, $percent_similar);
							if($percent_similar >= 15) { // 15% at least similarity
								$skips[$buff_id] = $percent_similar; // skip this alternate text part ...
							} //end if
						} //end if
						//--
						// clean buffer
						$buff = '';
						$buff_id = '';
						//--
					} //end if
					//--
				} //end if
				//--
			} //end foreach
			//--
			if($numparts <= 1) {
				$skips = array(); // disallow skips if only one part
			} //end if
			//-- print bodies except the skipped by similarity
			$out .= '<hr>';
			//--
			$cnt=0;
			foreach($msg['texts'] as $key => $val) {
				//--
				if((string)$val['type'] == 'text') { // assure we don't print other things
					//--
					$cnt += 1;
					//--
					$eval_arr = array();
					$eval_arr = SmartFileSysUtils::mime_eval('part_'.$cnt.'.html', 'inline');
					//--
					$tmp_link_pre = '<span title="Mime Part #'.$cnt.' ( '.Smart::escape_html(strtolower($val['mode']).' : '.strtoupper($val['charset'])).' )"><a href="'.self::mime_link($y_ctrl_key, $the_message_eml, $key, $y_link, $eval_arr[0], $eval_arr[1], 'minimal').'" target="'.$y_target.'__mimepart" data-smart="open.modal">';
					$tmp_link_pst = '</a></span>';
					//--
					if(strlen($skips[$key]) <= 0) { // print part if not skipped by similarity ...
						//--
						if((string)$skip_part_linking == 'yes') { // avoid display sub-text part links when only a part is displayed
							$tmp_pict_img = '';
						} else {
							$tmp_pict_img = '<div align="right">'.$tmp_link_pre.'<img src="lib/core/plugins/img/email/mime-part.svg">'.$tmp_link_pst.'</div>';
						} //end if
						//--
						if((string)$y_process_mode == 'data-reply') {
							if(strlen($reply_text['message']) <= 0) {
								$reply_text['message'] = (string) $val['content'];
							} //end if
						} else {
							$out .= $tmp_pict_img;
							$out .= $val['content'];
							$out .= '<br><hr><br>';
						} //end if
						//--
					} else {
						//--
						if((string)$skip_part_linking != 'yes') { // for replies, avoid display sub-text part links when only a part is displayed
							if((string)$y_process_mode == 'data-reply') {
								// nothing
							} else {
								$out .= '<div align="right">'.'<span title="'.'~'.Smart::escape_html(Smart::format_number_dec($skips[$key], 0, '.', ',').'%').'">&nbsp;</span>'.$tmp_link_pre.'<img src="lib/core/plugins/img/email/mime-alt-part.svg">'.$tmp_link_pst.'</div>';
							} //end if else
						} //end if
						//--
					} //end if else
					//--
				} //end if
				//--
			} //end foreach
			//--
		} //end if
		//--
		$out .= '</div></div><!-- END MIME MESSAGE HTML -->';
		//--
	} //end if else
	//--

	//--
	if((string)$y_process_mode == 'data-full') { // output an array with message and all header info as data structure
		//--
		return array(
			'message' 		=> (string) $out,
			'message-id' 	=> (string) $head['message-id'],
			'in-reply-to' 	=> (string) $head['in-reply-to'],
			'from' 			=> (string) $head['from_addr'],
			'to' 			=> (string) $head['to_addr'],
			'cc' 			=> (string) $head['cc_addr'],
			'date' 			=> (string) $head['date'],
			'atts_num' 		=> (int)    $reg_atts_num,
			'atts_lst' 		=> (string) $reg_atts_list,
			'filepath' 		=> (string) $the_message_eml,
			'is_part' 		=> (string) $reg_is_part // yes/no
		);
		//--
	} elseif((string)$y_process_mode == 'data-reply') { // output a special array for replies only
		//--
		$reply_text['message'] 		= (string) $reply_text['message']; // this comes from above
		$reply_text['from'] 		= (string) $head['from_addr'];
		$reply_text['to'] 			= (string) $head['to_addr'];
		$reply_text['cc'] 			= (string) $head['cc_addr'];
		$reply_text['date'] 		= (string) $head['date'];
		$reply_text['subject'] 		= (string) $head['subject'];
		$reply_text['message-id'] 	= (string) $head['message-id'];
		$reply_text['in-reply-to'] 	= (string) $head['in-reply-to'];
		//--
		return (array) $reply_text;
		//--
	} else { // 'default' or 'print' :: message as html view
		//--
		return (string) $out;
		//--
	} //end if
	//--

} //END FUNCTION
//==================================================================


//==================================================================
// [PRIVATE]
private static function mime_separe_part_link($y_msg_file) {
	//--
	$out = array('msg' => '', 'part' => '');
	//--
	if(strpos((string)$y_msg_file, '@') !== false) {
		$tmp_arr = (array) explode('@', (string)$y_msg_file);
		$out['msg'] = (string) trim((string)$tmp_arr[0]);
		$out['part'] = (string) trim((string)$tmp_arr[1]);
	} else {
		$out['msg'] = (string) trim((string)$y_msg_file);
	} //end if else
	//--
	return (array) $out;
	//--
} //END FUNCTION
//==================================================================


//==================================================================
// [PRIVATE]
private static function mime_link($y_ctrl_key, $y_msg_file, $y_part, $y_link, $y_rawmime, $y_rawdisp, $y_printable='') {
	//--
	$y_msg_file = (string) $y_msg_file;
	$y_part = (string) $y_part;
	$y_link = (string) $y_link;
	$y_rawmime = (string) $y_rawmime;
	$y_rawdisp = (string) $y_rawdisp;
	$y_printable = (string) $y_printable;
	//--
	$the_url_param_msg = '';
	$the_url_param_raw = '';
	$the_url_param_mime = '';
	$the_url_param_disp = '';
	//--
	if(((string)$y_link != '') AND ((string)$y_msg_file != '')) {
		//--
		$the_url_param_msg = (string) self::encode_mime_fileurl((string)$y_msg_file, (string)$y_ctrl_key); // {{{SYNC-MIME-ENCRYPT-ARR}}}
		if((string)$y_part != '') {
			$the_url_param_msg .= '@'.SmartUtils::url_hex_encode((string)$y_part); // have part
		} //end if
		//--
		if((string)$y_rawmime != '') {
			$the_url_param_raw = 'raw';
			$the_url_param_mime = (string) Smart::escape_url(SmartUtils::url_hex_encode((string)$y_rawmime));
		} //end if
		if((string)$y_rawdisp != '') {
			$the_url_param_raw = 'raw';
			$the_url_param_disp = (string) Smart::escape_url(SmartUtils::url_hex_encode((string)$y_rawdisp));
		} //end if
		//--
		if((string)$y_printable != '') { // printable display
			$y_link .= '&'.SMART_FRAMEWORK_URL_PARAM_PRINTABLE.'='. Smart::escape_url((string)SMART_FRAMEWORK_URL_VALUE_ENABLED); // .'&'.SMART_FRAMEWORK_URL_PARAM_MODALPOPUP.'='. Smart::escape_url((string)SMART_FRAMEWORK_URL_VALUE_ENABLED).'&';
		} //end if else
		//--
		$y_link = str_replace(
			array(
				'{{{MESSAGE}}}',
				'{{{RAWMODE}}}',
				'{{{MIME}}}',
				'{{{DISP}}}'
			),
			array(
				(string) $the_url_param_msg,
				(string) $the_url_param_raw,
				(string) $the_url_param_mime,
				(string) $the_url_param_disp
			),
			(string) $y_link
		);
		//--
	} //end if
	//--
	return (string) $y_link;
	//--
} //END FUNCTION
//==================================================================


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>