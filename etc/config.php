<?php
// [@[#[!NO-STRIP!]#]@]
// [CFG - SETTINGS]
// v.2.3.7.8 r.2017.03.27 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//######################################### TestONLY Settings (the TestONLY Settings can be removed in real production environments)
define('SMART_FRAMEWORK_TESTUNIT_BASE_URL', '?/page/samples.testunit/op/');
define('SMART_FRAMEWORK_TESTUNIT_CAPTCHA_MODE', 'cookie'); // cookie | session
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_FS_TESTS', false);
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_PGSQL_TESTS', false);
define('SMART_FRAMEWORK_TESTUNIT_ALLOW_REDIS_TESTS', false);
//######################################### END TestOnly Settings

//--------------------------------------- Info URL
$configs['app']['info-url'] 		= 'smart-framework.demo';				// Info URL: this must be someting like `www . mydomain . net`
//---------------------------------------

//--------------------------------------- SQLite related configuration
$configs['sqlite']['timeout'] 		= 60;									// connection timeout
$configs['sqlite']['slowtime'] 		= 0.0025;								// slow query time (for debugging)
//---------------------------------------

// NOTICE on DB Connectors:
//		The standard DB connectors includded in Smart.Framework are below:
//			* Redis
// 			* PostgreSQL
// 			* SQLite
//		Other DB Connectors are (below); they are available via Smart.Framework.Modules (copy them to modules/ folder of Smart.Framework) ; to add them to your project uncomment this line into modules/app/app-custom-bootstrap.inc.php # require_once('modules/smart-extra-libs/autoload.php'); :
//			* MySQL 	(includded separately because MySQL (Oracle) Server License is not free if you build a commercial project with MySQL ...)
// 			* MongoDB 	(includded separately because needs a special PHP extensions available only in PECL)
// 			* SoLR 		(includded separately because needs a special PHP extensions available only in PECL)

//--------------------------------------- Redis related configuration (used for Persistent Memory Cache but also for Redis Based Sessions)
/*
$configs['redis']['server-host']	= 'localhost';							// redis host
$configs['redis']['server-port']	= '6379';								// redis port
$configs['redis']['dbnum']			= 5;									// redis db number 0..15
$configs['redis']['password']		= '';									// redis Base64-Encoded password ; by default is empty
$configs['redis']['timeout']		= 3;									// redis connect timeout in seconds
$configs['redis']['slowtime']		= 0.0005;								// 0.0010 .. 0.0001 slow query time (for debugging)
*/
//---------------------------------------

//--------------------------------------- PostgreSQL related configuration of Default SQL Server
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

//--------------------------------------- MAIL SEND
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

//--------------------------------------- REGIONAL SETTINGS
$configs['regional']['language-id']					= 'en';					// Language `en` | `ro` (must exists as defined)
$configs['regional']['decimal-separator']			= '.';					// decimal separator `.` | `,`
$configs['regional']['thousands-separator']			= ',';					// thousand separator `,` | `.` | ` `
$configs['regional']['calendar-week-start']			= '1';					// 0=start on sunday | 1=start on Monday ; used for both PHP and Javascript
$configs['regional']['calendar-date-format-client'] = 'dd.mm.yy';			// Client Date Format - Javascript (allow only these characters: yy mm dd . - [space])
$configs['regional']['calendar-date-format-server']	= 'd.m.Y';				// Server Date Format - PHP (allow only these characters: Y m d . - [space])
//---------------------------------------
$languages = array('en' => '[EN]', 'ro' => '[RO]');							// associative array of available languages for this software (do not change without installing new languages support files)
//---------------------------------------

//----------------------------------------
$configs['js']['notifications']					= 'growl'; 					// 'growl' = Sticky Notifications (Jquery) ;  'dialog' = JQueryUI Dialog
$configs['js']['popup-mode']					= 'modal';					// 'modal' | 'popup'
$configs['js']['popup-override-mobiles'] 		= '<ios>,<and>,<mlx>,<mgo>,<nsy>,<bby>,<wce>,<plm>'; // Override Modal with PopUp mode for Mobile Operating Systems: ios = iPhone ; ipd = ios iPad ; and = Android ; mlx = Mobile Linux ; mgo = Meego ; nsy = Nokia Symbian ; wce = Windows CE / Windows Mobile ; plm = Palm / WebOS
//----------------------------------------

//--------------------------------------- MEDIA GALLERY
define('SMART_FRAMEWORK_MEDIAGALLERY_IMG_CONVERTER', 	'@gd'); 				// `@gd` | path to ImagMagick Convert (change to match your system) ; can be `/usr/bin/convert` or `/usr/local/bin/convert` or `c:/open_runtime/image_magick/convert.exe`
define('SMART_FRAMEWORK_MEDIAGALLERY_IMG_COMPOSITE', 	'@gd'); 				// `@gd` | path to ImagMagick Composite/Watermark (change to match your system) ; can be `/usr/bin/composite` or `/usr/local/bin/composite` or `c:/open_runtime/image_magick/composite.exe`
define('SMART_FRAMEWORK_MEDIAGALLERY_MOV_THUMBNAILER', 	'/usr/bin/ffmpeg'); 	// path to FFMpeg (Video Thumbnailer to extract a preview Image from a movie) ; (change to match your system) ; can be `/usr/bin/ffmpeg` or `/usr/local/bin/ffmpeg` or `c:/open_runtime/ffmpeg/ffmpeg.exe`
define('SMART_FRAMEWORK_MEDIAGALLERY_PDF_EXTRACTOR', 	''); 					// path to PDF Extractor (Pdf2HtmlEx)
//--------------------------------------- BARCODE SYSTEM
define('SMART_FRAMEWORK_BARCODE_1D_MODE', '128');								// 1D barcode system :: `128` = Code128 B (Extended) (ISO-8859-1) ; `93` = Code93 Extended+Checksum ; `39` = Code39 Extended (ISO-8859-1)
define('SMART_FRAMEWORK_BARCODE_2D_MODE', 'qrcode');							// 2D matrix barcode system :: `qrcode` = QRCode (UTF-8) ; `semacode` = DataMatrix (UTF-8)
define('SMART_FRAMEWORK_BARCODE_2D_OPTS', 'L');									// 2D matrix barcode options :: QRCode[L,M,Q,H] ; DataMatrix[] ; PDF417[1,2,3]
//---------------------------------------

//--------------------------------------- OTHER SPECIAL SETTINGS :: DO NOT MODIFY IF YOU DON'T KNOW WHAT YOU ARE DOING, really ...
//define('SMART_FRAMEWORK_CUSTOM_ERR_PAGES', 'modules/app/error-pages/');		// `` or custom path to error pages: 400.php, 401.php, 403.php, 404.php, 500.php, 503.php
//---------------------------------------

// end of php code
?>