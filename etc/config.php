<?php
// [@[#[!NO-STRIP!]#]@]
// [Smart.Framework / CFG - SETTINGS]
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//========================================= Testing-ONLY Settings (Samples)
// !!! REMOVE these Testing-ONLY Settings when using this config in real production environments !!!
// ... They are required just for Samples / Testing / Development purposes ...
define('SMART_FRAMEWORK_TEST_MODE', true);
//define('SMART_FRAMEWORK_TESTUNIT_ALLOW_FILESYSTEM_TESTS', true);
//define('SMART_FRAMEWORK_TESTUNIT_ALLOW_PCACHE_TESTS', true); // redis
//define('SMART_FRAMEWORK_TESTUNIT_ALLOW_DATABASE_TESTS', true);
//define('SMART_FRAMEWORK_TESTUNIT_ALLOW_WEBDAV_TESTS', true);
//========================================= END Testing-ONLY Settings


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
//$languages = [ 'en' => '[EN]', 'ro' => [ 'name' => '[RO]', 'decimal-separator' => ',', 'thousands-separator' => '.', 'calendar-week-start' => '1' ] ]; // extended associative array of available languages for this software ; to enable languages be sure to set the SMART_FRAMEWORK_URL_PARAM_LANGUAGE in init.php
//---------------------------------------


//--------------------------------------- MAIL SEND (SMTP) related configuration
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
//$configs['sendmail']['use-qp-encoding'] 	= true; 						// if TRUE will use QuotedPrintable encoding instead of Base64 for email message text/html bodies
*/
//---------------------------------------


//===== NOTICE on DB Connectors:
//
//		The standard DB connectors includded in Smart.Framework are available to config below, in this config file:
//			* Redis (Persistent Caching memory Server / Redis based sessions / KeyStore)
// 			* MongoDB (NoSQL, BigData Server ; requires the MongoDB PHP extension available via PECL)
// 			* PostgreSQL (SQL Server w. many advanced features incl. jsonb ... ; requires the PHP PgSQL extension)
//			* MySQLi (popular SQL Server as MariaDB / MySQL ; requires the PHP MySQLi extension)
// 			* SQLite (embedded sql ; requires the PHP SQLite3 extension)
//
//		Other DB Connectors are available via Smart.Framework.Modules as:
// 			* SoLR (includded separately in Smart.Framework.Modules/smart-extra-libs ; uncomment this line into modules/app/app-custom-bootstrap.inc.php # require_once('modules/smart-extra-libs/autoload.php') ; requires the PHP Solr extensions available in PECL)
//			* Zend-DBAL (a PDO adapter for MySQL / PostgreSQL and SQLite ; includded separately in Smart.Framework.Modules/mod-dbal-zend)
//			* RedBean-ORM (an easy to use ORM for MySQL / PostgreSQL / SQLite / CUBRID / Firebird/Interbase ; includded separately in Smart.Framework.Modules/mod-dbal-zend)
//
//=====

//--------------------------------------- SQLite related configuration
$configs['sqlite']['timeout'] 		= 60;									// connection timeout
$configs['sqlite']['slowtime'] 		= 0.0025;								// slow query time (for debugging)
//---------------------------------------

//--------------------------------------- Redis (Default) In-Memory/Key:Value-Store Server configuration (this is primary used for Persistent Memory Cache but can be also for Redis Based Sessions and more ...)
/*
$configs['redis']['server-host']	= '127.0.0.1';							// redis host
$configs['redis']['server-port']	= 6379;									// redis port
$configs['redis']['dbnum']			= 8;									// redis db number 0..15
$configs['redis']['password']		= '';									// redis Base64-Encoded password ; by default is empty
$configs['redis']['timeout']		= 5;									// redis connect timeout in seconds
$configs['redis']['slowtime']		= 0.0005;								// redis slow query time (for debugging) 0.0010 .. 0.0001
*/
//---------------------------------------

//--------------------------------------- MongoDB (Default) BigData Server configuration (standalone or cluster)
/*
$configs['mongodb']['type'] 		= 'mongo-standalone'; 					// mongodb server(s) type: 'mongo-standalone' | 'mongo-cluster' (sharding)
$configs['mongodb']['server-host']	= '127.0.0.1';							// mongodb host
$configs['mongodb']['server-port']	= 27017;								// mongodb port
$configs['mongodb']['dbname']		= 'smart_framework';					// mongodb database name
$configs['mongodb']['username'] 	= '';									// mongodb username
$configs['mongodb']['password'] 	= '';									// mongodb Base64-Encoded password
$configs['mongodb']['timeout']		= 10;									// mongodb connect timeout in seconds
$configs['mongodb']['slowtime']		= 0.0035;								// mongodb slow query time (for debugging) 0.0025 .. 0.0090
*/
//---------------------------------------

//--------------------------------------- PostgreSQL (Default) DB Server configuration (standalone or cluster)
/*
$configs['pgsql']['type'] 			= 'postgresql'; 						// postgresql / pgpool2
$configs['pgsql']['server-host'] 	= '127.0.0.1';							// postgresql host (default is 127.0.0.1)
$configs['pgsql']['server-port']	= 5432;									// postgresql port (default is 5432)
$configs['pgsql']['dbname']			= 'smart_framework';					// postgresql database name
$configs['pgsql']['username']		= 'pgsql';								// postgresql server username
$configs['pgsql']['password']		= base64_encode('pgsql');				// postgresql server Base64-Encoded password for that user name B64
$configs['pgsql']['timeout']		= 15;									// postgresql connection timeout (how many seconds to wait for a valid PgSQL Connection)
$configs['pgsql']['slowtime']		= 0.0050; 								// postgresql slow query time (for debugging) 0.0025 .. 0.0090
$configs['pgsql']['transact']		= 'READ COMMITTED';						// postgresql session Default Transaction Level: 'READ COMMITTED' | 'REPEATABLE READ' | 'SERIALIZABLE' | '' to leave it as default
*/
//---------------------------------------

//--------------------------------------- MariaDB/MySQL (Default) DB Server configuration (standalone or cluster)
/*
$configs['mysqli']['type'] 			= 'mariadb'; 							// mysql / mariadb
$configs['mysqli']['server-host'] 	= '127.0.0.1';							// server host (default is 127.0.0.1)
$configs['mysqli']['server-port']	= 3306;									// server port (default is 3306)
$configs['mysqli']['dbname']		= 'smart_framework';					// database name
$configs['mysqli']['username']		= 'root';								// server username
$configs['mysqli']['password']		= base64_encode('root');				// server Base64-Encoded password for that user name B64
$configs['mysqli']['timeout']		= 15;									// server connection timeout (how many seconds to wait for a valid MySQL Connection)
$configs['mysqli']['slowtime']		= 0.0050; 								// server slow query time (for debugging) 0.0025 .. 0.0090
$configs['mysqli']['transact']		= 'REPEATABLE READ';					// session Default Transaction Level: 'REPEATABLE READ' | 'READ COMMITTED' | '' to leave it as default
*/
//---------------------------------------


// end of php code
?>