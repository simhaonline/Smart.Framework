<?php
// [LIB - SmartFramework / Plugins / StaticLoad]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//--
// #PLUGINS# :: they can be loaded always (require) or as dependency injection (require_once)
//--
require_once('lib/core/plugins/lib_idn_punycode.php'); 		// idn punnycode converter
//--
require_once('lib/core/plugins/lib_mail_send.php');			// mail send client (sendmail, smtp)
require_once('lib/core/plugins/lib_mail_get.php'); 			// mail get client (pop3, imap4)
require_once('lib/core/plugins/lib_mail_decode.php'); 		// mail message decoder (mime)
require_once('lib/core/plugins/lib_mail_utils.php');		// mail utils (verify, parse)
//--
require_once('lib/core/plugins/lib_calendar.php');			// calendar component (html)
//--
require_once('lib/core/plugins/lib_yaml.php');				// yaml converter
require_once('lib/core/plugins/lib_xml.php');				// xml parser and composer
require_once('lib/core/plugins/lib_html.php');				// html parser
require_once('lib/core/plugins/lib_markdown.php'); 			// markdown syntax parser
require_once('lib/core/plugins/lib_archlzs.php');			// lzs archiver
//--
require_once('lib/core/plugins/lib_imgd.php');				// img (gd) process
require_once('lib/core/plugins/lib_barcodes_1d.php');		// barcodes 1D
require_once('lib/core/plugins/lib_barcodes_2d.php');		// barcodes 2D
require_once('lib/core/plugins/lib_captcha.php'); 			// captcha image
//--
require_once('lib/core/plugins/lib_ftp_cli.php');			// ftp client
//--
require_once('lib/core/plugins/lib_db_redis.php');			// redis db connector
require_once('lib/core/plugins/lib_db_sqlite.php');			// sqlite3 db connector
require_once('lib/core/plugins/lib_db_pgsql.php');			// postgresql db connector
//--
require_once('lib/core/plugins/lib_session.php');			// session storage
//--
require_once('lib/core/plugins/lib_export_zip.php');		// export zip archive
require_once('lib/core/plugins/lib_export_ooffice.php');	// export ooffice (opendocument)
require_once('lib/core/plugins/lib_export_pdf.php'); 		// pdf export
//--


// end of php code
?>