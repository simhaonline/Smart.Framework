<?php
// [LIB - Smart.Framework / Samples / Test (1D & 2D) Barcodes]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

// Class: \SmartModExtLib\Samples\TestUnitBarcodes
// Type: Module Library
// Info: this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Samples;

//----------------------------------------------------- PREVENT DIRECT EXECUTION (Namespace)
if(!\defined('\\SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@\http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@\basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START [OK: NAMESPACE]
//=====================================================================================


/**
 * Test (1D & 2D) Barcodes
 *
 * @access 		private
 * @internal
 *
 * @version 	v.20191006
 *
 */
final class TestUnitBarcodes {

	// ::

	//============================================================
	public static function test1dBarcode128B() {
		//--
		$str = 'BAR Code # 128B';
		//--
		if(\Smart::random_number(1, 100) > 50) {
			$use_cache = 60; // seconds
		} else {
			$use_cache = -1; // no
		} //end if else
		//--
		return \SmartBarcode1D::getBarcode($str, '128', 'html-svg', 1, 20, '#3B5897', true, (int)$use_cache);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test1dBarcode93() {
		//--
		$str = 'BAR Code # 93E+c';
		//--
		return \SmartBarcode1D::getBarcode($str, '93', 'html-png', 1, 20, '#3B5897', true);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test1dBarcode39() {
		//--
		$str = 'BAR Code # 39E';
		//--
		return \SmartBarcode1D::getBarcode($str, '39', 'html-svg', 1, 20, '#3B5897', true);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test1dBarcodeKix() {
		//--
		$str = '1231FZ13XHS';
		//--
		return \SmartBarcode1D::getBarcode($str, 'KIX', 'html-png', 2, 20, '#3B5897', true);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test2dBarcodeQRCode() {
		//--
		$str = 'Smart スマート // Cloud Application Platform クラウドアプリケーションプラットフォーム áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇòóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝßȘŞŠŚŜțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ " <p></p> ? & * ^ $ @ ! ` ~ % () [] {} | \ / + - _ : ; , . #0.97900300';
		//--
		if(\Smart::random_number(1, 100) > 50) {
			$use_cache = 60; // seconds
		} else {
			$use_cache = -1; // no
		} //end if else
		//--
		return \SmartBarcode2D::getBarcode($str, 'qrcode', 'html-svg', 2, '#3B5897', 'M', (int)$use_cache);
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test2dBarcodeDataMatrix() {
		//--
		$str = 'áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇòóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝßȘŞŠŚŜțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ " <p></p> ? & * ^ $ @ ! ` ~ % () [] {} | \ / + - _ : ; , . #0.97900300';
		//--
		return \SmartBarcode2D::getBarcode($str, 'semacode', 'html-png', 2, '#3B5897', '');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test2dBarcodePdf417() {
		//--
		$str = '1234567890 abcdefghij klmnopqrst uvwxzy 234DSKJFH23YDFKJHaS 1234567890 abcdefghij klmnopqrst uvwxzy 234DSKJFH23YDFKJHaS';
		//--
		return \SmartBarcode2D::getBarcode($str, 'pdf417', 'html-svg', 1, '#3B5897', '1');
		//--
	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>