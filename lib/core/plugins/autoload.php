<?php
// [LIB - Smart.Framework / Plugins / AutoLoad]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//--
// #PLUGINS# :: they are loaded via Dependency Injection
//--
/**
 * Function AutoLoad Plugins
 *
 * @access 		private
 * @internal
 *
 */
function autoload__SmartFrameworkPlugins($classname) {
	//--
	$classname = (string) $classname;
	//--
	if(substr($classname, 0, 5) !== 'Smart') { // must start with Smart
		return;
	} //end if
	//--
	switch((string)$classname) {
		//-- idn
		case 'SmartPunycode':
			require_once('lib/core/plugins/lib_idn_punycode.php'); 		// idn punnycode converter
			break;
		//-- detect
		case 'SmartDetectImages':
			require_once('lib/core/plugins/lib_detect_img.php');		// detect img
			break;
		//-- robot
		case 'SmartRobot':
			require_once('lib/core/plugins/lib_robot.php'); 			// smart robot
			break;
		//-- mail
		case 'SmartMailerSend':
		case 'SmartMailerSmtpClient':
			require_once('lib/core/plugins/lib_mail_send.php');			// mail send client (sendmail, smtp)
			break;
		case 'SmartMailerPop3Client':
		case 'SmartMailerImap4Client':
			require_once('lib/core/plugins/lib_mail_get.php'); 			// mail get client (pop3, imap4)
			break;
		case 'SmartMailerMimeDecode':
		case 'SmartMailerMimeExtract':
			require_once('lib/core/plugins/lib_mail_decode.php'); 		// mail message decoder (mime)
			break;
		case 'SmartMailerMimeParser':
		case 'SmartMailerUtils':
			require_once('lib/core/plugins/lib_mail_utils.php');		// mail utils (send, verify, parse)
			break;
		//-- yaml parser and composer
		case 'SmartYamlConverter':
			require_once('lib/core/plugins/lib_yaml.php');				// yaml converter
			break;
		//-- xml parser and composer
		case 'SmartXmlComposer':
		case 'SmartXmlParser':
			require_once('lib/core/plugins/lib_xml.php');				// xml parser and composer
			break;
		//-- html parser
		case 'SmartHtmlParser':
			require_once('lib/core/plugins/lib_html.php');				// html parser
			break;
		//-- markdown
		case 'SmartMarkdownToHTML':
			require_once('lib/core/plugins/lib_markdown.php');			// markdown to html parser
			break;
		//-- lzs archiver
		case 'SmartArchiverLZS':
			require_once('lib/core/plugins/lib_archlzs.php');			// lzs archiver
			break;
		//-- db drivers
		case 'SmartRedisDb':
			require_once('lib/core/plugins/lib_db_redis.php');			// redis db connector
			break;
		case 'SmartRedisPersistentCache':
			require_once('lib/core/plugins/lib_pcache_redis.php');		// redis persistent cache
			break;
		case 'SmartDbaUtilDb':
		case 'SmartDbaDb':
			require_once('lib/core/plugins/lib_db_dba.php');			// dba db connector
			break;
		case 'SmartDbaPersistentCache':
			require_once('lib/core/plugins/lib_pcache_dba.php'); 		// dba persistent cache
			break;
		case 'SmartSQliteFunctions':
		case 'SmartSQliteUtilDb':
		case 'SmartSQliteDb':
			require_once('lib/core/plugins/lib_db_sqlite.php');			// sqlite3 db connector
			break;
		case 'SmartPgsqlDb':
		case 'SmartPgsqlExtDb':
			require_once('lib/core/plugins/lib_db_pgsql.php');			// postgresql db connector
			break;
		case 'SmartMysqliDb':
		case 'SmartMysqliExtDb':
			require_once('lib/core/plugins/lib_db_mysqli.php'); 		// mysqli db connector
			break;
		case 'SmartMongoDb':
			require_once('lib/core/plugins/lib_db_mongodb.php');		// mongodb db connector
			break;
		//-- session handler
		case 'SmartAbstractCustomSession':
		case 'SmartSession':
			require_once('lib/core/plugins/lib_session.php');			// session handler
			break;
		//-- im (gd) process
		case 'SmartImageGdProcess':
			require_once('lib/core/plugins/lib_imgd.php');				// img (gd) process
			break;
		//-- barcodes 1D
		case 'SmartBarcode1D':
		case 'SmartBarcode1D_128':
		case 'SmartBarcode1D_93':
		case 'SmartBarcode1D_39':
		case 'SmartBarcode1D_RMS4CC':
			require_once('lib/core/plugins/lib_barcodes_1d.php');		// barcodes 1D
			break;
		//-- barcodes 2D
		case 'SmartBarcode2D':
		case 'SmartBarcode2D_QRcode':
		case 'SmartBarcode2D_DataMatrix':
		case 'SmartBarcode2D_Pdf417':
			require_once('lib/core/plugins/lib_barcodes_2d.php');		// barcodes 2D
			break;
		//-- captcha
		case 'SmartCaptchaImageDraw':
		case 'SmartCaptchaFormCheck':
			require_once('lib/core/plugins/lib_captcha.php'); 			// captcha image
			break;
		//-- viewhelpers
		case 'SmartViewHtmlHelpers':
			require_once('lib/core/plugins/lib_viewhelpers.php'); 		// viewhelpers components (html / js)
			break;
		//-- calendar
		case 'SmartCalendarComponent':
		case 'SmartHTMLCalendar':
			require_once('lib/core/plugins/lib_calendar.php');			// calendar component (html)
			break;
		//-- ftp client
		case 'SmartFtpClient':
			require_once('lib/core/plugins/lib_ftp_cli.php');			// ftp client
			break;
		//-- spreadsheet export / import
		case 'SmartSpreadSheetExport':
		case 'SmartSpreadSheetImport':
			require_once('lib/core/plugins/lib_spreadsheet.php');		// spreadsheet export / import
			break;
		case 'SmartPdfExport':
			require_once('lib/core/plugins/lib_export_pdf.php'); 		// pdf export
			break;
		//--
		default:
			return; // other classes are not managed here ...
		//--
	} //end switch
	//--
} //END FUNCTION
//--
spl_autoload_register('autoload__SmartFrameworkPlugins', true, true); 	// throw / prepend
//--


// end of php code
?>