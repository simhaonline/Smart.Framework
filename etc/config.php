<?php
// [@[#[!NO-STRIP!]#]@]
// [CFG - SETTINGS]
// v.3.7.7 r.2018.10.19 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//######################################### Mod.Samples (Tests ONLY Settings)
// !!! Remove these Test ONLY Settings when using in real production environments !!! They are required just for Samples ...
define('SMART_FRAMEWORK_TEST_MODE', true);
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_FS_TESTS', false);
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_DAVFS_TESTS', false);
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_PCACHE_TESTS', false); // redis
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_PGSQL_TESTS', false);
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_MONGO_TESTS', false);
//######################################### END TestOnly Settings


//--------------------------------------- Info URL
$configs['app']['info-url'] = 'smart-framework.demo';						// Info URL: this must be someting like `www . mydomain . net`
//---------------------------------------


//--------------------------------------- REGIONAL SETTINGS
$configs['regional']['language-id']					= 'en';					// The default Language ID: `en` | `ro` | ... (must exists and defined below under $languages)
$configs['regional']['decimal-separator']			= '.';					// decimal separator `.` | `,`
$configs['regional']['thousands-separator']			= ',';					// thousand separator `,` | `.` | ` `
$configs['regional']['calendar-week-start']			= '0';					// 0=start on sunday | 1=start on Monday ; used for both PHP and Javascript
$configs['regional']['calendar-date-format-client'] = 'dd.mm.yy';			// Client Date Format - Javascript (allow only these characters: yy mm dd . - [space])
$configs['regional']['calendar-date-format-server']	= 'd.m.Y';				// Server Date Format - PHP (allow only these characters: Y m d . - [space])
//--------------------------------------- LANGUAGE SETTINGS
$languages = [ 'en' => '[EN]' ];											// default associative array of available languages for this software
//$languages = [ 'en' => '[EN]', 'ro' => [ 'name' => '[RO]', 'decimal-separator' => ',', 'thousands-separator' => '.', 'calendar-week-start' => '1' ] ]; // extended associative array of available languages for this software
//---------------------------------------


//--------------------------------------- MAIL SEND (SMTP)
/*
$configs['sendmail']['server-mx-domain'] 	= 'yourdomain.tld';				// mx hello domain ; this is used for smtp send validations via HELO method, can be different from the server domain
$configs['sendmail']['server-host'] 		= 'yourdomain.tld';				// `` | SMTP Server Host (IP or Domain)
$configs['sendmail']['server-port']			= '465';						// `` | SMTP Server Port
$configs['sendmail']['server-ssl']			= 'tls';						// `` | SSL Mode: starttls | tls | sslv3
$configs['sendmail']['auth-user']			= 'user@yourdomain.tld';		// `` | smtp auth user (SMTP auth)
$configs['sendmail']['auth-password']		= '';							// `` | smtp auth password (SMTP auth)
$configs['sendmail']['from-address']		= 'user@yourdomain.tld';		// the email address From:
$configs['sendmail']['from-name'] 			= 'Your Name';					// the from name to be set in From:
$configs['sendmail']['log-messages']		= 'no';							// `no` | `yes` :: // Log Send Messages
*/
//---------------------------------------


//===== NOTICE on DB Connectors:
//
//		The standard DB connectors includded in Smart.Framework are available to config below, in this config file:
//			* Redis (Persistent Caching memory Server)
// 			* PostgreSQL (SQL Server w. many advanced features incl. jsonb ... ; requires the PHP PgSQL extension)
// 			* MongoDB (NoSQL, BigData Server ; requires the MongoDB PHP extensions available in PECL)
// 			* SQLite (embedded sql ; requires the PHP SQLite3 extension)
//
//		Other DB Connectors are (below): # they are only available via Smart.Framework.Modules, located in modules/smart-extra-libs ; to install them you must copy smart-extra-libs/ to the modules/ folder of Smart.Framework ; after load them to your project by uncomment this line into modules/app/app-custom-bootstrap.inc.php # require_once('modules/smart-extra-libs/autoload.php');
//			* MySQL 	(includded separately in extra modules ; requires the PHP MySQLi extension)
// 			* SoLR 		(includded separately in extra modules ; requires the PHP Solr extensions available in PECL)
//=====

//--------------------------------------- SQLite related configuration
$configs['sqlite']['timeout'] 		= 60;									// connection timeout
$configs['sqlite']['slowtime'] 		= 0.0025;								// slow query time (for debugging)
//---------------------------------------

//--------------------------------------- DB Redis related configuration (used for Persistent Memory Cache but also for Redis Based Sessions)
/*
$configs['redis']['server-host']	= '127.0.0.1';							// redis host
$configs['redis']['server-port']	= '6379';								// redis port
$configs['redis']['dbnum']			= 5;									// redis db number 0..15
$configs['redis']['password']		= '';									// redis Base64-Encoded password ; by default is empty
$configs['redis']['timeout']		= 3;									// redis connect timeout in seconds
$configs['redis']['slowtime']		= 0.0005;								// 0.0010 .. 0.0001 slow query time (for debugging)
*/
//---------------------------------------

//--------------------------------------- DB PostgreSQL related configuration of Default SQL Server
/*
$configs['pgsql']['type'] 			= 'postgresql'; 						// postgresql / pgpool2
$configs['pgsql']['server-id']		= '1';									// database server ID (default=1) :: this will use for getting true unique UUIDs in DB across many DB servers
$configs['pgsql']['server-host'] 	= '127.0.0.1';							// database host (default is 127.0.0.1)
$configs['pgsql']['server-port']	= '5432';								// database port (default is 5432)
$configs['pgsql']['dbname']			= 'smart_framework';					// database name
$configs['pgsql']['username']		= 'pgsql';								// sql server user name
$configs['pgsql']['password']		= base64_encode('pgsql');				// sql server Base64-Encoded password for that user name B64
$configs['pgsql']['timeout']		= 30;									// connection timeout (how many seconds to wait for a valid PgSQL Connection)
$configs['pgsql']['slowtime']		= 0.0050; 								// 0.0025 .. 0.0090 slow query time (for debugging)
$configs['pgsql']['transact']		= 'READ COMMITTED';						// Default Transaction Level: 'READ COMMITTED' | 'REPEATABLE READ' | 'SERIALIZABLE' | '' to leave it as default
*/
//---------------------------------------

//--------------------------------------- DB Mongo related configuration of Default MongoDB Server (standalone / cluster)
/*
$configs['mongodb']['type'] 		= 'mongo-standalone'; 					// mongodb server(s) type: 'mongo-standalone' | 'mongo-cluster' (sharding)
$configs['mongodb']['server-host']	= '127.0.0.1';							// mongodb host
$configs['mongodb']['server-port']	= '27017';								// mongodb port
$configs['mongodb']['dbname']		= 'smart_framework';					// mongodb database
$configs['mongodb']['username'] 	= '';									// mongodb username
$configs['mongodb']['password'] 	= '';									// mongodb Base64-Encoded password
$configs['mongodb']['timeout']		= 5;									// mongodb connect timeout in seconds
$configs['mongodb']['slowtime']		= 0.0035;								// 0.0025 .. 0.0090 slow query time (for debugging)
*/
//---------------------------------------


// end of php code
?>