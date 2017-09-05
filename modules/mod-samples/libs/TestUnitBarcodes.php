<?php
// [LIB - SmartFramework / Samples / Test (1D & 2D) Barcodes]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.3.5.7 r.2017.09.05 / smart.framework.v.3.5

// Class: \SmartModExtLib\Samples\TestUnitBarcodes
// Type: Module Library
// Info: this class integrates with the default Smart.Framework modules autoloader so does not need anything else to be setup

namespace SmartModExtLib\Samples;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Test (1D & 2D) Barcodes
 *
 * @access 		private
 * @internal
 *
 * @version 	v.170519
 *
 */
final class TestUnitBarcodes {

	// ::

	//============================================================
	public static function test1dBarcode128B() {
		//--
		$str = 'BAR Code # 128B';
		//--
		return \SmartBarcode1D::getBarcode($str, '128', 'html-svg', 1, 20, '#3B5897', true, 'no');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test1dBarcode93() {
		//--
		$str = 'BAR Code # 93E+c';
		//--
		return \SmartBarcode1D::getBarcode($str, '93', 'html-png', 1, 20, '#3B5897', true, 'no');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test1dBarcode39() {
		//--
		$str = 'BAR Code # 39E';
		//--
		return \SmartBarcode1D::getBarcode($str, '39', 'html-svg', 1, 20, '#3B5897', true, 'no');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test1dBarcodeKix() {
		//--
		$str = '1231FZ13XHS';
		//--
		return \SmartBarcode1D::getBarcode($str, 'KIX', 'html-png', 2, 20, '#3B5897', true, 'no');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test2dBarcodeQRCode() {
		//--
		$str = 'Smart スマート // Cloud Application Platform クラウドアプリケーションプラットフォーム áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇòóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝßȘŞŠŚŜțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ " <p></p> ? & * ^ $ @ ! ` ~ % () [] {} | \ / + - _ : ; , . #0.97900300';
		//--
		return \SmartBarcode2D::getBarcode($str, 'qrcode', 'html-svg', 2, '#3B5897', 'M', 'no');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test2dBarcodeDataMatrix() {
		//--
		$str = 'áâãäåāăąÁÂÃÄÅĀĂĄćĉčçĆĈČÇďĎèéêëēĕėěęÈÉÊËĒĔĖĚĘĝģĜĢĥħĤĦìíîïĩīĭȉȋįÌÍÎÏĨĪĬȈȊĮĳĵĲĴķĶĺļľłĹĻĽŁñńņňÑŃŅŇòóôõöōŏőøœÒÓÔÕÖŌŎŐØŒŕŗřŔŖŘșşšśŝßȘŞŠŚŜțţťȚŢŤùúûüũūŭůűųÙÚÛÜŨŪŬŮŰŲŵŴẏỳŷÿýẎỲŶŸÝźżžŹŻŽ " <p></p> ? & * ^ $ @ ! ` ~ % () [] {} | \ / + - _ : ; , . #0.97900300';
		//--
		return \SmartBarcode2D::getBarcode($str, 'semacode', 'html-png', 2, '#3B5897', '', 'no');
		//--
	} //END FUNCTION
	//============================================================


	//============================================================
	public static function test2dBarcodePdf417() {
		//--
		$str = '1234567890 abcdefghij klmnopqrst uvwxzy 234DSKJFH23YDFKJHaS 1234567890 abcdefghij klmnopqrst uvwxzy 234DSKJFH23YDFKJHaS';
		//--
		return \SmartBarcode2D::getBarcode($str, 'pdf417', 'html-svg', 1, '#3B5897', '1', 'no');
		//--
	} //END FUNCTION
	//============================================================


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>