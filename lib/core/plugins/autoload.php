<?php
// [LIB - SmartFramework / Plugins / AutoLoad]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.7 r.2017.02.22 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
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
		//-- db drivers
		case 'SmartRedisDb':
			require_once('lib/core/plugins/lib_db_redis.php');			// redis db connector
			break;
		case 'SmartSQliteDb':
		case 'SmartSQliteUtilDb':
			require_once('lib/core/plugins/lib_db_sqlite.php');			// sqlite3 db connector
			break;
		case 'SmartPgsqlDb':
		case 'SmartPgsqlExtDb':
			require_once('lib/core/plugins/lib_db_pgsql.php');			// postgresql db connector
			break;
		//-- session handler
		case 'SmartSession':
		case 'SmartAbstractCustomSession':
			require_once('lib/core/plugins/lib_session.php');			// session handler
			break;
		//-- yaml parser and composer
		case 'SmartYamlConverter':
			require_once('lib/core/plugins/lib_yaml.php');				// yaml converter
			break;
		//-- html parser
		case 'SmartHtmlParser':
			require_once('lib/core/plugins/lib_html.php');				// html parser
			break;
		//-- markdown
		case 'SmartMarkdownToHTML':
			require_once('lib/core/plugins/lib_markdown.php');			// markdown to html parser
			break;
		//-- xml parser and composer
		case 'SmartXmlParser':
		case 'SmartXmlComposer':
			require_once('lib/core/plugins/lib_xml.php');				// xml parser and composer
			break;
		//-- lzs archiver
		case 'SmartArchiverLZS':
			require_once('lib/core/plugins/lib_archlzs.php');			// lzs archiver
			break;
		//-- zip archive
		case 'SmartZipArchive':
			require_once('lib/core/plugins/lib_export_zip.php');		// zip archive
			break;
		//-- ooffice export
		case 'SmartExportToOpenOffice':
			require_once('lib/core/plugins/lib_export_ooffice.php'); 	// ooffice export
			break;
		//-- calendar
		case 'SmartCalendarComponent':
		case 'SmartHTMLCalendar':
			require_once('lib/core/plugins/lib_calendar.php');			// calendar component (html)
			break;
		//-- mediagallery
		case 'SmartMediaGalleryManager':
		case 'SmartMediaGalleryConverter':
		case 'SmartMediaGalleryPlayers':
		case 'SmartGdImageProcess':
		case 'SmartImagickImageProcess':
			require_once('lib/core/plugins/lib_mediagallery.php'); 		// mediagallery
			break;
		//-- captcha
		case 'SmartCaptchaFormCheck':
		case 'SmartCaptchaImageDraw':
			require_once('lib/core/plugins/lib_captcha.php'); 			// captcha image
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
		//-- charts
		case 'SmartImgBizCharts':
		case 'SmartImgGfxCharts':
			require_once('lib/core/plugins/lib_charts.php');			// gd charts
			break;
		//-- ftp client
		case 'SmartFtpClient':
			require_once('lib/core/plugins/lib_ftp_cli.php');			// ftp client
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