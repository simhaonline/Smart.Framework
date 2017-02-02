<?php
// [LIB - SmartFramework / Plugins / Smart BarCodes 2D]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.6 r.2017.02.02 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// BarCodes 2D - QRCode, DataMatrix and PDF417
// DEPENDS: SmartFramework
//======================================================

//--
if(!defined('SMART_FRAMEWORK_BARCODE_2D_MODE')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_BARCODE_2D_MODE');
} //end if
if(!defined('SMART_FRAMEWORK_BARCODE_2D_OPTS')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_BARCODE_2D_OPTS');
} //end if
//--

// [REGEX-SAFE-OK]

//======================================================
// BarCodes 2D:
//		* QRCode
//		* DataMatrix (SemaCode)
//		* PDF417
// License: BSD
// (c) 2015 unix-world.org
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartBarcode2D - Generates 2D BarCodes: QRCode, DataMatrix (SemaCode), PDF417.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	SmartFramework
 * @version 	v.160827
 * @package 	Components:BarCodes
 *
 */
final class SmartBarcode2D {

	// ::


	/**
	 * Function: Generate a 2D Barcode: QRCode, DataMatrix (SemaCode), PDF417
	 *
	 * @param STRING 	$y_code 			The code for the BarCode Generator
	 * @param ENUM 		$y_type				The BarCode Type: qrcode / semacode / pdf417
	 * @param ENUM 		$y_format			The Barcode format: html, html-png, png, html-svg, svg
	 * @param INTEGER+ 	$y_size				The Scale-Size for Barcode (1..4)
	 * @param HEXCOLOR	$y_color			The Hexadecimal Color for the Barcode Pixels ; default is Black = #000000
	 * @param MIXED		$y_extraoptions		Extra Options: for QRCode = Quality [L, M, Q, H] L as default ; for PDF417 a Ratio Integer between 1 and 17
	 * @param YES/NO	$y_cache			If YES will cache the Barcode to avoid on-the-fly generation ; default is set to NO
	 *
	 * @return MIXED	By Type Selection: 	HTML Code / PNG Image / SVG Code
	 *
	 */
	public static function getBarcode($y_code, $y_type, $y_format, $y_size, $y_color='#000000', $y_extraoptions='', $y_cache='no') {
		//--
		switch((string)$y_type) {
			case 'qrcode':
				switch((string)$y_extraoptions) {
					case 'H':
						$y_extraoptions = 'H';
						break;
					case 'Q':
						$y_extraoptions = 'Q';
						break;
					case 'M':
						$y_extraoptions = 'M';
						break;
					case 'L':
					default:
						$y_extraoptions = 'L';
				} //end switch
				$barcode_type = 'qrcode';
				break;
			case 'semacode':
				$y_extraoptions = '';
				$barcode_type = 'semacode';
				break;
			case 'pdf417':
				$y_extraoptions = (int) (0 + $y_extraoptions);
				if($y_extraoptions <= 0) {
					$y_extraoptions = 1;
				} //end if
				if($y_extraoptions > 17) {
					$y_extraoptions = 17;
				} //end if
				$barcode_type = 'pdf417';
				break;
			default:
				$barcode_type = '???';
				Smart::log_warning('ERROR: BarCodes2D - Invalid Type Selected for getBarcode');
				return '';
		} //end switch
		//--
		switch((string)$y_format) {
			case 'html':
				$barcode_format = '.htm';
				break;
			case 'html-png':
				$barcode_format = '.png.htm';
				break;
			case 'png':
				$barcode_format = '.png';
				break;
			case 'html-svg':
				$barcode_format = '.svg.htm';
				break;
			case 'svg':
				$barcode_format = '.svg';
				break;
			default:
				$barcode_format = '.unknown';
				Smart::log_warning('ERROR: BarCodes2D - Invalid Mode Selected for getBarcode');
				return '';
		} //end switch
		//--

		//--
		$memory_cache_url = 'memory://barcode-2d/'.$barcode_type.'/'.$barcode_format.'/'.$y_size.'/'.$y_color.'/'.$y_extraoptions.'/'.$y_code;
		$realm = 'barcode-2d/';
		//--

		//--
		if((string)$y_cache == 'yes') {
			//--
			$out = SmartUtils::load_cached_content($barcode_format, $realm, $memory_cache_url, ''); // (try to) get from cache
			//--
			if((string)$out != '') {
				return $out; // if found in cache return it
			} //end if
			//--
		} //end if
		//--

		//--
		switch((string)$barcode_type) {
			case 'qrcode':
				$arr_barcode = (new SmartBarcode2D_QRcode($y_code, $y_extraoptions))->getBarcodeArray();
				break;
			case 'semacode':
				$arr_barcode = (new SmartBarcode2D_DataMatrix($y_code))->getBarcodeArray();
				break;
			case 'pdf417':
				$arr_barcode = (new SmartBarcode2D_Pdf417($y_code, $y_extraoptions, -1))->getBarcodeArray();
				break;
			default:
				$arr_barcode = ''; // not to be an array for error detection
		} //end switch
		//--
		switch((string)$y_format) {
			case 'html':
				$out = '<!-- '.Smart::escape_html(strtoupper($barcode_type).' ('.$y_size.'/'.$y_color.') ['.$y_extraoptions.']'.' :: '.date('YmdHis')).' -->'.'<div title="'.Smart::escape_html($y_code).'">'.self::getBarcodeHTML($arr_barcode, $y_size, $y_color).'</div>'.'<!-- #END :: '.Smart::escape_html(strtoupper($barcode_type)).' -->';
				break;
			case 'html-png': // html img embedded png
				$out = '<!-- '.Smart::escape_html(strtoupper($barcode_type).' ('.$y_size.'/'.$y_color.') ['.$y_extraoptions.']'.' :: '.date('YmdHis')).' -->'.'<div title="'.Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLPNG($arr_barcode, $y_size, $y_color).'</div>'.'<!-- #END :: '.Smart::escape_html(strtoupper($barcode_type)).' -->';
				break;
			case 'png': // raw png
				$out = self::getBarcodePNG($arr_barcode, $y_size, $y_color); // needs header image/png on output
				break;
			case 'html-svg':
				$out = '<!-- '.Smart::escape_html(strtoupper($barcode_type).' ('.$y_size.'/'.$y_color.') ['.$y_extraoptions.']'.' :: '.date('YmdHis')).' -->'.'<div title="'.Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLSVG($arr_barcode, $y_size, $y_color).'</div>'.'<!-- #END :: '.Smart::escape_html(strtoupper($barcode_type)).' -->';
				break;
			case 'svg':
				$out = self::getBarcodeSVG($arr_barcode, $y_size, $y_color); // needs header image/svg on output
				break;
			default:
				$out = '';
		} //end switch
		//--

		//--
		if((string)$y_cache == 'yes') {
			//--
			$out = SmartUtils::load_cached_content($barcode_format, $realm, $memory_cache_url, $out); // set + get from cache
			//--
		} //end if
		//--

		//--
		return $out;
		//--

	} //END FUNCTION


	/**
	 * Function: Get BarCode as HTML
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeHTML($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$z = self::conformZ($z);
		//--
		$microtime = microtime(true);
		//--
		$html = '';
		//--
		$html .= "\n".'<!-- Barcode2D / HTML --><table border="0" cellspacing="0" cellpadding="0" style="border-style:hidden; border-collapse:collapse;">';
		//-- print barcode elements
		for($r=0; $r<$barcode_arr['num_rows']; $r++) {
			//--
			$html .= "\n".'<tr height="'.$z.'" style="height:'.$z.'px;">';
			//-- for each column
			for($c=0; $c<$barcode_arr['num_cols']; $c++) {
				//--
				if($barcode_arr['bcode'][$r][$c] == 1) {
					$html .= '<td bgcolor="'.$color.'" width="'.$z.'" height="'.$z.'" style="font-size:1px;width:'.$z.'px;height:'.$z.'px;"></td>';
				} else {
					$html .= '<td bgcolor="#FFFFFF" width="'.$z.'" height="'.$z.'" style="font-size:1px;width:'.$z.'px;height:'.$z.'px;"></td>';
				} //end if
				//--
			} //end for
			//--
			$html .= '</tr>';
			//--
		} //end for
		//--
		$html .= "\n".'</table><!-- END :: Barcode2D ['.(microtime(true) - $microtime).'] -->'."\n";
		//--
		return $html;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as SVG embedded in HTML
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeEmbeddedHTMLSVG($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$z = self::conformZ($z);
		//--
		$microtime = microtime(true);
		//--
		return "\n".'<!-- Barcode2D / SVG -->'.'<img src="data:image/svg+xml;base64,'.Smart::escape_html(base64_encode(self::getBarcodeSVG($barcode_arr, $z, $color))).'" alt="BarCode2D-SVG">'.'<!-- END :: Barcode2D ['.(microtime(true) - $microtime).'] -->'."\n";
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as SVG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeSVG($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!is_array($barcode_arr)) {
			return '<svg width="100" height="10"><text x="0" y="10" fill="#FF0000" font-size="9" font-family="monospace">[ INVALID BARCODE ]</text></svg>';
		} //end if
		$z = self::conformZ($z);
		//--
		$svg = '';
		//--
		$svg .= '<'.'?'.'xml version="1.0" encoding="UTF-8" standalone="no"'.' ?'.'>'."\n";
		$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
		$svg .= '<svg width="'.round(($barcode_arr['num_cols'] * $z), 3).'" height="'.round(($barcode_arr['num_rows'] * $z), 3).'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";
		$svg .= "\t".'<desc>'.Smart::escape_html($barcode_arr['code']).'</desc>'."\n";
		$svg .= "\t".'<rect fill="#FFFFFF" x="0" y="0" width="'.round(($barcode_arr['num_cols'] * $z), 3).'" height="'.round(($barcode_arr['num_rows'] * $z), 3).'" />'."\n";
		$svg .= "\t".'<g id="elements" fill="'.$color.'" stroke="none">'."\n";
		//-- print barcode elements
		$y = 0;
		//-- for each row
		for($r=0; $r<$barcode_arr['num_rows']; ++$r) {
			//--
			$x = 0;
			//-- for each column
			for($c=0; $c<$barcode_arr['num_cols']; ++$c) {
				//--
				if($barcode_arr['bcode'][$r][$c] == 1) {
					//-- draw a single barcode cell
					$svg .= "\t\t".'<rect x="'.$x.'" y="'.$y.'" width="'.$z.'" height="'.$z.'" />'."\n";
					//--
				} //end if
				//--
				$x += $z;
				//--
			} //end for
			//--
			$y += $z;
			//--
		} //end for
		//--
		$svg .= "\t".'</g>'."\n";
		$svg .= '</svg>'."\n";
		//--
		return $svg;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as PNG embedded in HTML
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeEmbeddedHTMLPNG($barcode_arr, $z=3, $color='#000000') {
		//--
		if(!is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$z = self::conformZ($z);
		//--
		$microtime = microtime(true);
		//--
		return "\n".'<!-- Barcode2D / PNG -->'.'<img src="data:image/png;base64,'.Smart::escape_html(base64_encode(self::getBarcodePNG($barcode_arr, $z, $color))).'" alt="BarCode2D-PNG">'.'<!-- END :: Barcode2D ['.(microtime(true) - $microtime).'] -->'."\n";
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as PNG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodePNG($barcode_arr, $z=3, $color=array(0,0,0)) {
		//--
		if(!is_array($color)) {
			$color = (string) $color;
			$color = trim(str_replace('#', '', $color));
			$color = array(hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
		} //end if
		//--
		if(!is_array($barcode_arr)) {
			//--
			Smart::log_notice('Invalid Barcode2D PNG Data: Not Array !');
			//--
			$width = 125;
			$height = 10;
			//--
			$png = @imagecreate($width, $height);
			$bgcol = @imagecolorallocate($png, 250, 250, 250);
			$fgcol = @imagecolorallocate($png, 255, 0, 0);
			@imagestring($png, 1, 1, 1, "[ INVALID BARCODE (1) ]", $fgcol);
			//--
		} else {
			//--
			$z = self::conformZ($z);
			//-- calculate image size
			$the_width = ($barcode_arr['num_cols'] * $z);
			$the_height = ($barcode_arr['num_rows'] * $z);
			//--
			$png = null;
			if(($the_width > 0) AND ($the_height > 0)) {
				$png = @imagecreate($the_width, $the_height);
			} //end if
			//--
			if(!$png) {
				//--
				Smart::log_notice('Invalid Barcode2D PNG Dimensions: '."\n".'Code='.$barcode_arr['code']."\n".'Cols='.$barcode_arr['num_cols'].' ; Rows='.$barcode_arr['num_rows']);
				//--
				$width = 125;
				$height = 10;
				//--
				$png = @imagecreate($width, $height);
				$bgcol = @imagecolorallocate($png, 250, 250, 250);
				$fgcol = @imagecolorallocate($png, 255, 0, 0);
				@imagestring($png, 1, 1, 1, "[ INVALID BARCODE (2) ]", $fgcol);
				//--
			} else {
				//--
				$bgcol = @imagecolorallocate($png, 255, 255, 255);
				$fgcol = @imagecolorallocate($png, $color[0], $color[1], $color[2]);
				//-- print barcode elements
				$y = 0;
				//-- for each row
				for($r = 0; $r < $barcode_arr['num_rows']; ++$r) {
					//--
					$x = 0;
					//-- for each column
					for($c = 0; $c < $barcode_arr['num_cols']; ++$c) {
						//--
						if($barcode_arr['bcode'][$r][$c] == 1) {
							//-- draw a single barcode cell
							@imagefilledrectangle($png, $x, $y, ($x + $z - 1), ($y + $z - 1), $fgcol);
							//--
						} //end if
						//--
						$x += $z;
						//--
					} //end for
					//--
					$y += $z;
					//--
				} //end for
				//--
			} //end if else
			//--
		} //end if else
		//--
		ob_start();
		@imagepng($png);
		$imagedata = ob_get_clean();
		@imagedestroy($png);
		//--
		return $imagedata;
		//--
	} //END FUNCTION


	private static function conformZ($z) {
		//-- z must be between 1 and 16
		$z = (int) $z;
		if($z < 1) {
			$z = 1;
		} //end if
		if($z > 16) {
			$z = 16;
		} //end if
		//--
		return $z;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

//============================================================
// BarCode 2D: QRCode
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create QR-Code barcode arrays.
// QR Code symbol is a 2D barcode that can be scanned by
// handy terminals such as a mobile phone with CCD.
// The capacity of QR Code is up to 7000 digits or 4000
// characters, and has high robustness.
// This class supports QR Code model 2, described in
// JIS (Japanese Industrial Standards) X0510:2004
// or ISO/IEC 18004.
// Currently the following features are not supported:
// ECI and FNC1 mode, Micro QR Code, QR Code model 1,
// Structured mode.
// TECHNICAL DATA / FEATURES OF QRCODE:
// * Encodable Character Set: 	UTF-8 + Kanji
// * Code Type: 				Matrix
// * Error Correction Levels: 	L: 7% ; M: 15% ; Q: 25% ; H: 30%
// * Maximum Data Characters: 	7089 numeric, 4296 alphanumeric (ISO-8859-1), 2953 Binary (UTF-8), 1817 Kanji (Shift JIS)
//============================================================
//
// This class is derived from the following projects:
//
// "TcPDF" / Barcodes 2D / 1.0.010 / 20120725
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
// "PHP QR Code encoder"
// License: GNU-LGPLv3
// Copyright (C) 2010 by Dominik Dzienia
//
// The "PHP QR Code encoder" is based on
// "C libqrencode library" (ver. 3.1.1)
// License: GNU-LGPL 2.1
// Copyright (C) 2006-2010 by Kentaro Fukuchi
//
// Reed-Solomon code encoder is written by Phil Karn, KA9Q.
// Copyright (C) 2002-2006 Phil Karn, KA9Q
//
// QR Code is registered trademark of DENSO WAVE INCORPORATED
//
//============================================================


/**
 * Class Smart BarCode 2D QRCode
 *
 * @access 		private
 * @internal
 *
 */
final class SmartBarcode2D_QRcode {

	// ->
	// v.160827


	// Encoding modes (characters which can be encoded in QRcode)
	private $const_QR_BARCODE_MODE_NL = -1; 			// Encoding modes (characters which can be encoded in QRcode)
	private $const_QR_BARCODE_MODE_NM = 0; 				// Encoding mode numeric (0-9). 3 characters are encoded to 10bit length. In theory, 7089 characters or less can be stored in a QRcode.
	private $const_QR_BARCODE_MODE_AN = 1; 				// Encoding mode alphanumeric (0-9A-Z $%*+-./:) 45characters. 2 characters are encoded to 11bit length. In theory, 4296 characters or less can be stored in a QRcode.
	private $const_QR_BARCODE_MODE_8B = 2; 				// Encoding mode 8bit byte data. In theory, 2953 characters or less can be stored in a QRcode.
	private $const_QR_BARCODE_MODE_KJ = 3; 				// Encoding mode KANJI. A KANJI character (multibyte character) is encoded to 13bit length. In theory, 1817 characters or less can be stored in a QRcode.
	private $const_QR_BARCODE_MODE_ST = 4; 				// Encoding mode STRUCTURED (currently unsupported)

	// Levels of error correction. QRcode has a function of an error correcting for miss reading that white is black. Error correcting is defined in 4 level as below.
	private $const_QR_BARCODE_ECC_LEVEL_L = 0; 			// Error correction level L : About 7% or less errors can be corrected.
	private $const_QR_BARCODE_ECC_LEVEL_M = 1; 			// Error correction level M : About 15% or less errors can be corrected.
	private $const_QR_BARCODE_ECC_LEVEL_Q = 2; 			// Error correction level Q : About 25% or less errors can be corrected.
	private $const_QR_BARCODE_ECC_LEVEL_H = 3; 			// Error correction level H : About 30% or less errors can be corrected.

	// Version. Size of QRcode is defined as version from 1 to 40. Version 1 is 21*21 matrix. And 4 modules increases whenever 1 version increases. So version 40 is 177*177 matrix.
	private $const_QR_BARCODE_SPEC_VERSION_MAX = 40; 	// Maximum QR Code version.
	private $const_QR_BARCODE_SPEC_WIDTH_MAX = 177; 	// Maximum matrix size for maximum version (version 40 is 177*177 matrix).

	// Capacity
	private $const_QR_BARCODE_CAP_WIDTH = 0; 			// Matrix index to get width from $capacity array.
	private $const_QR_BARCODE_CAP_WORDS = 1; 			// Matrix index to get number of words from $capacity array.
	private $const_QR_BARCODE_CAP_REMINDER = 2; 		// Matrix index to get remainder from $capacity array.
	private $const_QR_BARCODE_CAP_ECC = 3; 				// Matrix index to get error correction level from $capacity array.

	// Structure (currently usupported)
	private $const_QR_BARCODE_STRUCT_HEAD_BITS =  20; 	// Number of header bits for structured mode
	private $const_QR_BARCODE_STRUCT_MAX_SYMBOLS = 16; 	// Max number of symbols for structured mode

	// Masks
	private $const_QR_BARCODE_MASK_N1 = 3; 				// Down point base value for case 1 mask pattern (concatenation of same color in a line or a column)
	private $const_QR_BARCODE_MASK_N2 = 3; 				// Down point base value for case 2 mask pattern (module block of same color)
	private $const_QR_BARCODE_MASK_N3 = 40; 			// Down point base value for case 3 mask pattern (1:1:3:1:1(dark:bright:dark:bright:dark)pattern in a line or a column)
	private $const_QR_BARCODE_MASK_N4 = 10; 			// Down point base value for case 4 mask pattern (ration of dark modules in whole)

	// Optimization settings
	private $const_QR_BARCODE_FIND_BEST_MASK = true; 	// if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code
	private $const_QR_BARCODE_FIND_FROM_RANDOM = false; // (default is 2) ; if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly
	private $const_QR_BARCODE_FIND_DEFAULT_MASK = 2; 	// when $this->const_QR_BARCODE_FIND_BEST_MASK === false

	/**
	 * Barcode array to be returned which is readable by TCPDF.
	 * @private
	 */
	private $barcode_array = array();

	/**
	 * QR code version. Size of QRcode is defined as version. Version is from 1 to 40. Version 1 is 21*21 matrix. And 4 modules increases whenever 1 version increases. So version 40 is 177*177 matrix.
	 * @private
	 */
	private $version = 0;

	/**
	 * Levels of error correction. See definitions for possible values.
	 * @private
	 */
	private $level = 0; // must be set with the same value as: const_QR_BARCODE_ECC_LEVEL_L

	/**
	 * Encoding mode.
	 * @private
	 */
	private $hint = 2; // must be initialized with the same value as: const_QR_BARCODE_MODE_8B

	/**
	 * Boolean flag, if true the input string will be converted to uppercase.
	 * @private
	 */
	private $casesensitive = true;

	/**
	 * Structured QR code (not supported yet).
	 * @private
	 */
	private $structured = 0;

	/**
	 * Mask data.
	 * @private
	 */
	private $data;

	// FrameFiller

	/**
	 * Width.
	 * @private
	 */
	private $width;

	/**
	 * Frame.
	 * @private
	 */
	private $frame;

	/**
	 * X position of bit.
	 * @private
	 */
	private $x;

	/**
	 * Y position of bit.
	 * @private
	 */
	private $y;

	/**
	 * Direction.
	 * @private
	 */
	private $dir;

	/**
	 * Single bit value.
	 * @private
	 */
	private $bit;

	// ---- QRrawcode ----

	/**
	 * Data code.
	 * @private
	 */
	private $datacode = array();

	/**
	 * Error correction code.
	 * @private
	 */
	private $ecccode = array();

	/**
	 * Blocks.
	 * @private
	 */
	private $blocks;

	/**
	 * Reed-Solomon blocks.
	 * @private
	 */
	private $rsblocks = array(); //of RSblock

	/**
	 * Counter.
	 * @private
	 */
	private $count;

	/**
	 * Data length.
	 * @private
	 */
	private $dataLength;

	/**
	 * Error correction length.
	 * @private
	 */
	private $eccLength;

	/**
	 * Value b1.
	 * @private
	 */
	private $b1;

	// ---- QRmask ----

	/**
	 * Run length.
	 * @private
	 */
	private $runLength = array();

	// ---- QRsplit ----

	/**
	 * Input data string.
	 * @private
	 */
	private $dataStr = '';

	/**
	 * Input items.
	 * @private
	 */
	private $items;

	// Reed-Solomon items

	/**
	 * Reed-Solomon items.
	 * @private
	 */
	private $rsitems = array();

	/**
	 * Array of frames.
	 * @private
	 */
	private $frames = array();

	/**
	 * Alphabet-numeric convesion table.
	 * @private
	 */
	private $anTable = array(
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, //
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, //
		36, -1, -1, -1, 37, 38, -1, -1, -1, -1, 39, 40, -1, 41, 42, 43, //
		 0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 44, -1, -1, -1, -1, -1, //
		-1, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, //
		25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, -1, -1, -1, -1, -1, //
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, //
		-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1  //
		);

	/**
	 * Array Table of the capacity of symbols.
	 * See Table 1 (pp.13) and Table 12-16 (pp.30-36), JIS X0510:2004.
	 * @private
	 */
	private $capacity = array(
		array(  0,    0, 0, array(   0,    0,    0,    0)), //
		array( 21,   26, 0, array(   7,   10,   13,   17)), //  1
		array( 25,   44, 7, array(  10,   16,   22,   28)), //
		array( 29,   70, 7, array(  15,   26,   36,   44)), //
		array( 33,  100, 7, array(  20,   36,   52,   64)), //
		array( 37,  134, 7, array(  26,   48,   72,   88)), //  5
		array( 41,  172, 7, array(  36,   64,   96,  112)), //
		array( 45,  196, 0, array(  40,   72,  108,  130)), //
		array( 49,  242, 0, array(  48,   88,  132,  156)), //
		array( 53,  292, 0, array(  60,  110,  160,  192)), //
		array( 57,  346, 0, array(  72,  130,  192,  224)), // 10
		array( 61,  404, 0, array(  80,  150,  224,  264)), //
		array( 65,  466, 0, array(  96,  176,  260,  308)), //
		array( 69,  532, 0, array( 104,  198,  288,  352)), //
		array( 73,  581, 3, array( 120,  216,  320,  384)), //
		array( 77,  655, 3, array( 132,  240,  360,  432)), // 15
		array( 81,  733, 3, array( 144,  280,  408,  480)), //
		array( 85,  815, 3, array( 168,  308,  448,  532)), //
		array( 89,  901, 3, array( 180,  338,  504,  588)), //
		array( 93,  991, 3, array( 196,  364,  546,  650)), //
		array( 97, 1085, 3, array( 224,  416,  600,  700)), // 20
		array(101, 1156, 4, array( 224,  442,  644,  750)), //
		array(105, 1258, 4, array( 252,  476,  690,  816)), //
		array(109, 1364, 4, array( 270,  504,  750,  900)), //
		array(113, 1474, 4, array( 300,  560,  810,  960)), //
		array(117, 1588, 4, array( 312,  588,  870, 1050)), // 25
		array(121, 1706, 4, array( 336,  644,  952, 1110)), //
		array(125, 1828, 4, array( 360,  700, 1020, 1200)), //
		array(129, 1921, 3, array( 390,  728, 1050, 1260)), //
		array(133, 2051, 3, array( 420,  784, 1140, 1350)), //
		array(137, 2185, 3, array( 450,  812, 1200, 1440)), // 30
		array(141, 2323, 3, array( 480,  868, 1290, 1530)), //
		array(145, 2465, 3, array( 510,  924, 1350, 1620)), //
		array(149, 2611, 3, array( 540,  980, 1440, 1710)), //
		array(153, 2761, 3, array( 570, 1036, 1530, 1800)), //
		array(157, 2876, 0, array( 570, 1064, 1590, 1890)), // 35
		array(161, 3034, 0, array( 600, 1120, 1680, 1980)), //
		array(165, 3196, 0, array( 630, 1204, 1770, 2100)), //
		array(169, 3362, 0, array( 660, 1260, 1860, 2220)), //
		array(173, 3532, 0, array( 720, 1316, 1950, 2310)), //
		array(177, 3706, 0, array( 750, 1372, 2040, 2430))  // 40
	);

	/**
	 * Array Length indicator.
	 * @private
	 */
	private $lengthTableBits = array(
		array(10, 12, 14),
		array( 9, 11, 13),
		array( 8, 16, 16),
		array( 8, 10, 12)
	);

	/**
	 * Array Table of the error correction code (Reed-Solomon block).
	 * See Table 12-16 (pp.30-36), JIS X0510:2004.
	 * @private
	 */
	private $eccTable = array(
		array(array( 0,  0), array( 0,  0), array( 0,  0), array( 0,  0)), //
		array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), //  1
		array(array( 1,  0), array( 1,  0), array( 1,  0), array( 1,  0)), //
		array(array( 1,  0), array( 1,  0), array( 2,  0), array( 2,  0)), //
		array(array( 1,  0), array( 2,  0), array( 2,  0), array( 4,  0)), //
		array(array( 1,  0), array( 2,  0), array( 2,  2), array( 2,  2)), //  5
		array(array( 2,  0), array( 4,  0), array( 4,  0), array( 4,  0)), //
		array(array( 2,  0), array( 4,  0), array( 2,  4), array( 4,  1)), //
		array(array( 2,  0), array( 2,  2), array( 4,  2), array( 4,  2)), //
		array(array( 2,  0), array( 3,  2), array( 4,  4), array( 4,  4)), //
		array(array( 2,  2), array( 4,  1), array( 6,  2), array( 6,  2)), // 10
		array(array( 4,  0), array( 1,  4), array( 4,  4), array( 3,  8)), //
		array(array( 2,  2), array( 6,  2), array( 4,  6), array( 7,  4)), //
		array(array( 4,  0), array( 8,  1), array( 8,  4), array(12,  4)), //
		array(array( 3,  1), array( 4,  5), array(11,  5), array(11,  5)), //
		array(array( 5,  1), array( 5,  5), array( 5,  7), array(11,  7)), // 15
		array(array( 5,  1), array( 7,  3), array(15,  2), array( 3, 13)), //
		array(array( 1,  5), array(10,  1), array( 1, 15), array( 2, 17)), //
		array(array( 5,  1), array( 9,  4), array(17,  1), array( 2, 19)), //
		array(array( 3,  4), array( 3, 11), array(17,  4), array( 9, 16)), //
		array(array( 3,  5), array( 3, 13), array(15,  5), array(15, 10)), // 20
		array(array( 4,  4), array(17,  0), array(17,  6), array(19,  6)), //
		array(array( 2,  7), array(17,  0), array( 7, 16), array(34,  0)), //
		array(array( 4,  5), array( 4, 14), array(11, 14), array(16, 14)), //
		array(array( 6,  4), array( 6, 14), array(11, 16), array(30,  2)), //
		array(array( 8,  4), array( 8, 13), array( 7, 22), array(22, 13)), // 25
		array(array(10,  2), array(19,  4), array(28,  6), array(33,  4)), //
		array(array( 8,  4), array(22,  3), array( 8, 26), array(12, 28)), //
		array(array( 3, 10), array( 3, 23), array( 4, 31), array(11, 31)), //
		array(array( 7,  7), array(21,  7), array( 1, 37), array(19, 26)), //
		array(array( 5, 10), array(19, 10), array(15, 25), array(23, 25)), // 30
		array(array(13,  3), array( 2, 29), array(42,  1), array(23, 28)), //
		array(array(17,  0), array(10, 23), array(10, 35), array(19, 35)), //
		array(array(17,  1), array(14, 21), array(29, 19), array(11, 46)), //
		array(array(13,  6), array(14, 23), array(44,  7), array(59,  1)), //
		array(array(12,  7), array(12, 26), array(39, 14), array(22, 41)), // 35
		array(array( 6, 14), array( 6, 34), array(46, 10), array( 2, 64)), //
		array(array(17,  4), array(29, 14), array(49, 10), array(24, 46)), //
		array(array( 4, 18), array(13, 32), array(48, 14), array(42, 32)), //
		array(array(20,  4), array(40,  7), array(43, 22), array(10, 67)), //
		array(array(19,  6), array(18, 31), array(34, 34), array(20, 61))  // 40
	);

	/**
	 * Array Positions of alignment patterns.
	 * This array includes only the second and the third position of the alignment patterns. Rest of them can be calculated from the distance between them.
	 * See Table 1 in Appendix E (pp.71) of JIS X0510:2004.
	 * @private
	 */
	private $alignmentPattern = array(
		array( 0,  0),
		array( 0,  0), array(18,  0), array(22,  0), array(26,  0), array(30,  0), //  1- 5
		array(34,  0), array(22, 38), array(24, 42), array(26, 46), array(28, 50), //  6-10
		array(30, 54), array(32, 58), array(34, 62), array(26, 46), array(26, 48), // 11-15
		array(26, 50), array(30, 54), array(30, 56), array(30, 58), array(34, 62), // 16-20
		array(28, 50), array(26, 50), array(30, 54), array(28, 54), array(32, 58), // 21-25
		array(30, 58), array(34, 62), array(26, 50), array(30, 54), array(26, 52), // 26-30
		array(30, 56), array(34, 60), array(30, 58), array(34, 62), array(30, 54), // 31-35
		array(24, 50), array(28, 54), array(32, 58), array(26, 54), array(30, 58)  // 35-40
	);

	/**
	 * Array Version information pattern (BCH coded).
	 * See Table 1 in Appendix D (pp.68) of JIS X0510:2004.
	 * size: [$this->const_QR_BARCODE_SPEC_VERSION_MAX - 6]
	 * @private
	 */
	private $versionPattern = array(
		0x07c94, 0x085bc, 0x09a99, 0x0a4d3, 0x0bbf6, 0x0c762, 0x0d847, 0x0e60d, //
		0x0f928, 0x10b78, 0x1145d, 0x12a17, 0x13532, 0x149a6, 0x15683, 0x168c9, //
		0x177ec, 0x18ec4, 0x191e1, 0x1afab, 0x1b08e, 0x1cc1a, 0x1d33f, 0x1ed75, //
		0x1f250, 0x209d5, 0x216f0, 0x228ba, 0x2379f, 0x24b0b, 0x2542e, 0x26a64, //
		0x27541, 0x28c69
	);

	/**
	 * Array Format information
	 * @private
	 */
	private $formatInfo = array(
		array(0x77c4, 0x72f3, 0x7daa, 0x789d, 0x662f, 0x6318, 0x6c41, 0x6976), //
		array(0x5412, 0x5125, 0x5e7c, 0x5b4b, 0x45f9, 0x40ce, 0x4f97, 0x4aa0), //
		array(0x355f, 0x3068, 0x3f31, 0x3a06, 0x24b4, 0x2183, 0x2eda, 0x2bed), //
		array(0x1689, 0x13be, 0x1ce7, 0x19d0, 0x0762, 0x0255, 0x0d0c, 0x083b)  //
	);


	// -------------------------------------------------
	// -------------------------------------------------


	/**
	 * This is the class constructor.
	 * Creates a QRcode object
	 * @param $code (string) code to represent using QRcode
	 * @param $eclevel (string) error level: <ul><li>L : About 7% or less errors can be corrected.</li><li>M : About 15% or less errors can be corrected.</li><li>Q : About 25% or less errors can be corrected.</li><li>H : About 30% or less errors can be corrected.</li></ul>
	 * @public
	 * @since 1.0.000
	 */
	public function __construct($code, $eclevel='L') {
		//--
		$this->level = $this->const_QR_BARCODE_ECC_LEVEL_L; // fix after converting class const to vars
		$this->hint = $this->const_QR_BARCODE_MODE_8B; // fix after converting class const to vars
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$code = (string) $code; // force string
		//--
		$this->barcode_array = array();
		$barcode_array = array();
		$barcode_array['code'] = $code;
		//--
		$this->level = array_search($eclevel, array('L', 'M', 'Q', 'H')); // set error correction level
		if($this->level === false) {
			$this->level = $this->const_QR_BARCODE_ECC_LEVEL_L; // default is L
		} //end if
		if(($this->hint != $this->const_QR_BARCODE_MODE_8B) AND ($this->hint != $this->const_QR_BARCODE_MODE_KJ)) {
			return false;
		} //end if
		if(($this->version < 0) OR ($this->version > $this->const_QR_BARCODE_SPEC_VERSION_MAX)) {
			return false;
		} //end if
		//--
		$this->items = array();
		$this->encodeString($code);
		if(is_null($this->data)) {
			return false;
		} //end if
		$qrTab = $this->binarize($this->data);
		$size = count($qrTab);
		$barcode_array['num_rows'] = $size;
		$barcode_array['num_cols'] = $size;
		$barcode_array['bcode'] = array();
		foreach($qrTab as $u => $line) {
			$arrAdd = array();
			foreach(str_split($line) as $u => $char) {
				$arrAdd[] = ($char=='1') ? 1 : 0;
			} //end foreach
			$barcode_array['bcode'][] = $arrAdd;
		} //end foreach
		//--
		$this->barcode_array = (array) $barcode_array;
		//--
	} //END FUNCTION


	/**
	 * Returns a barcode array which is readable by TCPDF
	 * @return array barcode array readable by TCPDF;
	 * @public
	 */
	public function getBarcodeArray() {
		//--
		return (array) $this->barcode_array;
		//--
	} //END FUNCTION


	/**
	 * Convert the frame in binary form
	 * @param $frame (array) array to binarize
	 * @return array frame in binary form
	 */
	private function binarize($frame) {
		$len = count($frame);
		// the frame is square (width = height)
		foreach($frame as &$frameLine) { // PHP7-CHECK:FOREACH-BY-VAL
			for($i=0; $i<$len; $i++) {
				$frameLine[$i] = (ord($frameLine[$i])&1)?'1':'0';
			} //end for
		} //end foreach
		return $frame;
	} //END FUNCTION


	/**
	 * Encode the input string to QR code
	 * @param $string (string) input string to encode
	 */
	private function encodeString($string) {
		$this->dataStr = $string;
		if(!$this->casesensitive) {
			$this->toUpper();
		} //end if
		$ret = $this->splitString();
		if($ret < 0) {
			return NULL;
		} //end if
		$this->encodeMask(-1);
	} //END FUNCTION


	/**
	 * Encode mask
	 * @param $mask (int) masking mode
	 */
	private function encodeMask($mask) {
		$spec = array(0, 0, 0, 0, 0);
		$this->datacode = $this->getByteStream($this->items);
		if(is_null($this->datacode)) {
			return NULL;
		} //end if
		$spec = $this->getEccSpec($this->version, $this->level, $spec);
		$this->b1 = $this->rsBlockNum1($spec);
		$this->dataLength = $this->rsDataLength($spec);
		$this->eccLength = $this->rsEccLength($spec);
		$this->ecccode = array_fill(0, $this->eccLength, 0);
		$this->blocks = $this->rsBlockNum($spec);
		$ret = $this->init($spec);
		if($ret < 0) {
			return NULL;
		} //end if
		$this->count = 0;
		$this->width = $this->getWidth($this->version);
		$this->frame = $this->newFrame($this->version);
		$this->x = $this->width - 1;
		$this->y = $this->width - 1;
		$this->dir = -1;
		$this->bit = -1;
		//-- inteleaved data and ecc codes
		for($i=0; $i < ($this->dataLength + $this->eccLength); $i++) {
			$code = $this->getCode();
			$bit = 0x80;
			for($j=0; $j<8; $j++) {
				$addr = $this->getNextPosition();
				$this->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
				$bit = $bit >> 1;
			} //end for
		} //end for
		//-- remainder bits
		$j = $this->getRemainder($this->version);
		for($i=0; $i<$j; $i++) {
			$addr = $this->getNextPosition();
			$this->setFrameAt($addr, 0x02);
		} //end for
		//-- masking
		$this->runLength = array_fill(0, $this->const_QR_BARCODE_SPEC_WIDTH_MAX + 1, 0);
		if($mask < 0) {
			if($this->const_QR_BARCODE_FIND_BEST_MASK) {
				$masked = $this->mask($this->width, $this->frame, $this->level);
			} else {
				$masked = $this->makeMask($this->width, $this->frame, (intval($this->const_QR_BARCODE_FIND_DEFAULT_MASK) % 8), $this->level);
			} //end if else
		} else {
			$masked = $this->makeMask($this->width, $this->frame, $mask, $this->level);
		} //end if else
		if($masked == NULL) {
			return NULL;
		} //end if
		$this->data = $masked;
	} //END FUNCTION


	//-- FrameFiller


	/**
	 * Set frame value at specified position
	 * @param $at (array) x,y position
	 * @param $val (int) value of the character to set
	 */
	private function setFrameAt($at, $val) {
		$this->frame[$at['y']][$at['x']] = chr($val);
	} //END FUNCTION


	/**
	 * Get frame value at specified position
	 * @param $at (array) x,y position
	 * @return value at specified position
	 */
	private function getFrameAt($at) {
		return ord($this->frame[$at['y']][$at['x']]);
	} //END FUNCTION


	/**
	 * Return the next frame position
	 * @return array of x,y coordinates
	 */
	private function getNextPosition() {
		//--
		do {
			if($this->bit == -1) {
				$this->bit = 0;
				return array('x'=>$this->x, 'y'=>$this->y);
			} //end if
			$x = $this->x;
			$y = $this->y;
			$w = $this->width;
			if($this->bit == 0) {
				$x--;
				$this->bit++;
			} else {
				$x++;
				$y += $this->dir;
				$this->bit--;
			} //end if else
			if($this->dir < 0) {
				if($y < 0) {
					$y = 0;
					$x -= 2;
					$this->dir = 1;
					if($x == 6) {
						$x--;
						$y = 9;
					} //end if
				} //end if
			} else {
				if($y == $w) {
					$y = $w - 1;
					$x -= 2;
					$this->dir = -1;
					if($x == 6) {
						$x--;
						$y -= 8;
					} //end if
				} //end if
			} //end if else
			if(($x < 0) OR ($y < 0)) {
				return NULL;
			} //end if
			$this->x = $x;
			$this->y = $y;
		} while(ord($this->frame[$y][$x]) & 0x80);
		//--
		return array('x'=>$x, 'y'=>$y);
		//--
	} //END FUNCTION


	//-- QRrawcode


	/**
	 * Initialize code.
	 * @param $spec (array) array of ECC specification
	 * @return 0 in case of success, -1 in case of error
	 */
	private function init($spec) {
		//--
		$dl = $this->rsDataCodes1($spec);
		$el = $this->rsEccCodes1($spec);
		$rs = $this->init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
		$blockNo = 0;
		$dataPos = 0;
		$eccPos = 0;
		$endfor = $this->rsBlockNum1($spec);
		for($i=0; $i < $endfor; ++$i) {
			$ecc = array_slice($this->ecccode, $eccPos);
			$this->rsblocks[$blockNo] = array();
			$this->rsblocks[$blockNo]['dataLength'] = $dl;
			$this->rsblocks[$blockNo]['data'] = array_slice($this->datacode, $dataPos);
			$this->rsblocks[$blockNo]['eccLength'] = $el;
			$ecc = $this->encode_rs_char($rs, $this->rsblocks[$blockNo]['data'], $ecc);
			$this->rsblocks[$blockNo]['ecc'] = $ecc;
			$this->ecccode = array_merge(array_slice($this->ecccode,0, $eccPos), $ecc);
			$dataPos += $dl;
			$eccPos += $el;
			$blockNo++;
		} //end for
		if($this->rsBlockNum2($spec) == 0) {
			return 0;
		} //end if
		$dl = $this->rsDataCodes2($spec);
		$el = $this->rsEccCodes2($spec);
		$rs = $this->init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
		if($rs == NULL) {
			return -1;
		} //end if
		$endfor = $this->rsBlockNum2($spec);
		for($i=0; $i < $endfor; ++$i) {
			$ecc = array_slice($this->ecccode, $eccPos);
			$this->rsblocks[$blockNo] = array();
			$this->rsblocks[$blockNo]['dataLength'] = $dl;
			$this->rsblocks[$blockNo]['data'] = array_slice($this->datacode, $dataPos);
			$this->rsblocks[$blockNo]['eccLength'] = $el;
			$ecc = $this->encode_rs_char($rs, $this->rsblocks[$blockNo]['data'], $ecc);
			$this->rsblocks[$blockNo]['ecc'] = $ecc;
			$this->ecccode = array_merge(array_slice($this->ecccode, 0, $eccPos), $ecc);
			$dataPos += $dl;
			$eccPos += $el;
			$blockNo++;
		} //end for
		//--
		return 0;
		//--
	} //END FUNCTION


	/**
	 * Return Reed-Solomon block code.
	 * @return array rsblocks
	 */
	private function getCode() {
		//--
		if($this->count < $this->dataLength) {
			$row = $this->count % $this->blocks;
			$col = $this->count / $this->blocks;
			if($col >= $this->rsblocks[0]['dataLength']) {
				$row += $this->b1;
			} //end if
			$ret = $this->rsblocks[$row]['data'][$col];
		} elseif($this->count < $this->dataLength + $this->eccLength) {
			$row = ($this->count - $this->dataLength) % $this->blocks;
			$col = ($this->count - $this->dataLength) / $this->blocks;
			$ret = $this->rsblocks[$row]['ecc'][$col];
		} else {
			return 0;
		} //end if else
		//--
		$this->count++;
		//--
		return $ret;
	} //END FUNCTION


	//-- QRmask


	/**
	 * Write Format Information on frame and returns the number of black bits
	 * @param $width (int) frame width
	 * @param $frame (array) frame
	 * @param $mask (array) masking mode
	 * @param $level (int) error correction level
	 * @return int blacks
	 */
	 private function writeFormatInformation($width, &$frame, $mask, $level) {
		//--
		$blacks = 0;
		$format =  $this->getFormatInfo($mask, $level);
		//--
		for($i=0; $i<8; ++$i) {
			if($format & 1) {
				$blacks += 2;
				$v = 0x85;
			} else {
				$v = 0x84;
			} //end if else
			$frame[8][$width - 1 - $i] = chr($v);
			if($i < 6) {
				$frame[$i][8] = chr($v);
			} else {
				$frame[$i + 1][8] = chr($v);
			} //end if else
			$format = $format >> 1;
		} //end for
		//--
		for($i=0; $i<7; ++$i) {
			if($format & 1) {
				$blacks += 2;
				$v = 0x85;
			} else {
				$v = 0x84;
			} //end if else
			$frame[$width - 7 + $i][8] = chr($v);
			if($i == 0) {
				$frame[8][7] = chr($v);
			} else {
				$frame[8][6 - $i] = chr($v);
			} //end if else
			$format = $format >> 1;
		} //end for
		//--
		return $blacks;
		//--
	} //END FUNCTION


	/**
	 * mask0
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask0($x, $y) {
		return ($x + $y) & 1;
	} //END FUNCTION


	/**
	 * mask1
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask1($x, $y) {
		return ($y & 1);
	} //END FUNCTION


	/**
	 * mask2
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask2($x, $y) {
		return ($x % 3);
	} //END FUNCTION


	/**
	 * mask3
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask3($x, $y) {
		return ($x + $y) % 3;
	} //END FUNCTION


	/**
	 * mask4
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask4($x, $y) {
		return (((int)($y / 2)) + ((int)($x / 3))) & 1;
	} //END FUNCTION


	/**
	 * mask5
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask5($x, $y) {
		return (($x * $y) & 1) + ($x * $y) % 3;
	} //END FUNCTION


	/**
	 * mask6
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask6($x, $y) {
		return ((($x * $y) & 1) + ($x * $y) % 3) & 1;
	} //END FUNCTION


	/**
	 * mask7
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @return int mask
	 */
	 private function mask7($x, $y) {
		return ((($x * $y) % 3) + (($x + $y) & 1)) & 1;
	} //END FUNCTION


	/**
	 * Return bitmask
	 * @param $maskNo (int) mask number
	 * @param $width (int) width
	 * @param $frame (array) frame
	 * @return array bitmask
	 */
	private function generateMaskNo($maskNo, $width, $frame) {
		$bitMask = array_fill(0, $width, array_fill(0, $width, 0));
		for($y=0; $y<$width; ++$y) {
			for($x=0; $x<$width; ++$x) {
				if(ord($frame[$y][$x]) & 0x80) {
					$bitMask[$y][$x] = 0;
				} else {
					$maskFunc = call_user_func(array($this, 'mask'.$maskNo), $x, $y);
					$bitMask[$y][$x] = ($maskFunc == 0)?1:0;
				} //end if else
			} //end for
		} //end for
		return $bitMask;
	} //END FUNCTION


	/**
	 * makeMaskNo
	 * @param $maskNo (int)
	 * @param $width (int)
	 * @param $s (int)
	 * @param $d (int)
	 * @param $maskGenOnly (boolean)
	 * @return int b
	 */
	 private function makeMaskNo($maskNo, $width, $s, &$d, $maskGenOnly=false) {
		$b = 0;
		$bitMask = array();
		$bitMask = $this->generateMaskNo($maskNo, $width, $s, $d);
		if($maskGenOnly) {
			return;
		} //end if
		$d = $s;
		for($y=0; $y<$width; ++$y) {
			for($x=0; $x<$width; ++$x) {
				if($bitMask[$y][$x] == 1) {
					$d[$y][$x] = chr(ord($s[$y][$x]) ^ ((int)($bitMask[$y][$x])));
				} //end if
				$b += (int)(ord($d[$y][$x]) & 1);
			} //end for
		} //end for
		return $b;
	} //END FUNCTION


	/**
	 * makeMask
	 * @param $width (int)
	 * @param $frame (array)
	 * @param $maskNo (int)
	 * @param $level (int)
	 * @return array mask
	 */
	 private function makeMask($width, $frame, $maskNo, $level) {
		$masked = array_fill(0, $width, str_repeat("\0", $width));
		$this->makeMaskNo($maskNo, $width, $frame, $masked);
		$this->writeFormatInformation($width, $masked, $maskNo, $level);
		return $masked;
	} //END FUNCTION


	/**
	 * calcN1N3
	 * @param $length (int)
	 * @return int demerit
	 */
	 private function calcN1N3($length) {
		$demerit = 0;
		for($i=0; $i<$length; ++$i) {
			if($this->runLength[$i] >= 5) {
				$demerit += ($this->const_QR_BARCODE_MASK_N1 + ($this->runLength[$i] - 5));
			} //end if
			if($i & 1) {
				if(($i >= 3) AND ($i < ($length-2)) AND ($this->runLength[$i] % 3 == 0)) {
					$fact = (int)($this->runLength[$i] / 3);
					if(($this->runLength[$i-2] == $fact) AND ($this->runLength[$i-1] == $fact) AND ($this->runLength[$i+1] == $fact) AND ($this->runLength[$i+2] == $fact)) {
						if(($this->runLength[$i-3] < 0) OR ($this->runLength[$i-3] >= (4 * $fact))) {
							$demerit += $this->const_QR_BARCODE_MASK_N3;
						} elseif ((($i+3) >= $length) OR ($this->runLength[$i+3] >= (4 * $fact))) {
							$demerit += $this->const_QR_BARCODE_MASK_N3;
						} //end if else
					} //end if
				} //end if
			} //end if
		} //end for
		return $demerit;
	} //END FUNCTION


	/**
	 * evaluateSymbol
	 * @param $width (int)
	 * @param $frame (array)
	 * @return int demerit
	 */
	 private function evaluateSymbol($width, $frame) {
		$head = 0;
		$demerit = 0;
		for($y=0; $y<$width; ++$y) {
			$head = 0;
			$this->runLength[0] = 1;
			$frameY = $frame[$y];
			if($y > 0) {
				$frameYM = $frame[$y-1];
			} //end if
			for($x=0; $x<$width; ++$x) {
				if(($x > 0) AND ($y > 0)) {
					$b22 = ord($frameY[$x]) & ord($frameY[$x-1]) & ord($frameYM[$x]) & ord($frameYM[$x-1]);
					$w22 = ord($frameY[$x]) | ord($frameY[$x-1]) | ord($frameYM[$x]) | ord($frameYM[$x-1]);
					if(($b22 | ($w22 ^ 1)) & 1) {
						$demerit += $this->const_QR_BARCODE_MASK_N2;
					} //end if
				} //end if
				if(($x == 0) AND (ord($frameY[$x]) & 1)) {
					$this->runLength[0] = -1;
					$head = 1;
					$this->runLength[$head] = 1;
				} elseif($x > 0) {
					if((ord($frameY[$x]) ^ ord($frameY[$x-1])) & 1) {
						$head++;
						$this->runLength[$head] = 1;
					} else {
						$this->runLength[$head]++;
					} //end if else
				} //end if else
			} //end for
			$demerit += $this->calcN1N3($head+1);
		} //end for
		for($x=0; $x<$width; ++$x) {
			$head = 0;
			$this->runLength[0] = 1;
			for($y=0; $y<$width; ++$y) {
				if(($y == 0) AND (ord($frame[$y][$x]) & 1)) {
					$this->runLength[0] = -1;
					$head = 1;
					$this->runLength[$head] = 1;
				} elseif($y > 0) {
					if((ord($frame[$y][$x]) ^ ord($frame[$y-1][$x])) & 1) {
						$head++;
						$this->runLength[$head] = 1;
					} else {
						$this->runLength[$head]++;
					} //end if else
				} //end if else
			} //end for
			$demerit += $this->calcN1N3($head+1);
		} //end for
		return $demerit;
	} //END FUNCTION


	/**
	 * mask
	 * @param $width (int)
	 * @param $frame (array)
	 * @param $level (int)
	 * @return array best mask
	 */
	 private function mask($width, $frame, $level) {
		$minDemerit = PHP_INT_MAX;
		$bestMaskNum = 0;
		$bestMask = array();
		$checked_masks = array(0, 1, 2, 3, 4, 5, 6, 7);
		if($this->const_QR_BARCODE_FIND_FROM_RANDOM !== false) {
			$howManuOut = 8 - ($this->const_QR_BARCODE_FIND_FROM_RANDOM % 9);
			for($i = 0; $i <  $howManuOut; ++$i) {
				$remPos = rand (0, count($checked_masks)-1);
				unset($checked_masks[$remPos]);
				$checked_masks = array_values($checked_masks);
			} //end for
		} //end if
		$bestMask = $frame;
		foreach($checked_masks as $u => $i) {
			$mask = array_fill(0, $width, str_repeat("\0", $width));
			$demerit = 0;
			$blacks = 0;
			$blacks  = $this->makeMaskNo($i, $width, $frame, $mask);
			$blacks += $this->writeFormatInformation($width, $mask, $i, $level);
			$blacks  = (int)(100 * $blacks / ($width * $width));
			$demerit = (int)((int)(abs($blacks - 50) / 5) * $this->const_QR_BARCODE_MASK_N4);
			$demerit += $this->evaluateSymbol($width, $mask);
			if($demerit < $minDemerit) {
				$minDemerit = $demerit;
				$bestMask = $mask;
				$bestMaskNum = $i;
			} //end if
		} //end foreach
		return $bestMask;
	} //END FUNCTION


	//-- QRsplit


	/**
	 * Return true if the character at specified position is a number
	 * @param $str (string) string
	 * @param $pos (int) characted position
	 * @return boolean true of false
	 */
	 private function isdigitat($str, $pos) {
		if($pos >= strlen($str)) {
			return false;
		} //end if
		return ((ord($str[$pos]) >= ord('0'))&&(ord($str[$pos]) <= ord('9')));
	} //END FUNCTION


	/**
	 * Return true if the character at specified position is an alphanumeric character
	 * @param $str (string) string
	 * @param $pos (int) characted position
	 * @return boolean true of false
	 */
	 private function isalnumat($str, $pos) {
		if($pos >= strlen($str)) {
			return false;
		} //end if
		return ($this->lookAnTable(ord($str[$pos])) >= 0);
	} //END FUNCTION


	/**
	 * identifyMode
	 * @param $pos (int)
	 * @return int mode
	 */
	 private function identifyMode($pos) {
		if($pos >= strlen($this->dataStr)) {
			return $this->const_QR_BARCODE_MODE_NL;
		} //end if
		$c = $this->dataStr[$pos];
		if($this->isdigitat($this->dataStr, $pos)) {
			return $this->const_QR_BARCODE_MODE_NM;
		} elseif($this->isalnumat($this->dataStr, $pos)) {
			return $this->const_QR_BARCODE_MODE_AN;
		} elseif($this->hint == $this->const_QR_BARCODE_MODE_KJ) {
			if($pos+1 < strlen($this->dataStr)) {
				$d = $this->dataStr[$pos+1];
				$word = (ord($c) << 8) | ord($d);
				if(($word >= 0x8140 && $word <= 0x9ffc) OR ($word >= 0xe040 && $word <= 0xebbf)) {
					return $this->const_QR_BARCODE_MODE_KJ;
				} //end if
			} //end if
		} //end if else
		return $this->const_QR_BARCODE_MODE_8B;
	} //END FUNCTION


	/**
	 * eatNum
	 * @return int run
	 */
	 private function eatNum() {
		$ln = $this->lengthIndicator($this->const_QR_BARCODE_MODE_NM, $this->version);
		$p = 0;
		while($this->isdigitat($this->dataStr, $p)) {
			$p++;
		} //end while
		$run = $p;
		$mode = $this->identifyMode($p);
		if($mode == $this->const_QR_BARCODE_MODE_8B) {
			$dif = $this->estimateBitsModeNum($run) + 4 + $ln
			+ $this->estimateBitsMode8(1)         // + 4 + l8
			- $this->estimateBitsMode8($run + 1); // - 4 - l8
			if($dif > 0) {
				return $this->eat8();
			} //end if
		} //end if
		if($mode == $this->const_QR_BARCODE_MODE_AN) {
			$dif = $this->estimateBitsModeNum($run) + 4 + $ln
			+ $this->estimateBitsModeAn(1)        // + 4 + la
			- $this->estimateBitsModeAn($run + 1);// - 4 - la
			if($dif > 0) {
				return $this->eatAn();
			} //end if
		} //end if
		$this->items = $this->appendNewInputItem($this->items, $this->const_QR_BARCODE_MODE_NM, $run, str_split($this->dataStr));
		return $run;
	} //END FUNCTION


	/**
	 * eatAn
	 * @return int run
	 */
	 private function eatAn() {
		$la = $this->lengthIndicator($this->const_QR_BARCODE_MODE_AN,  $this->version);
		$ln = $this->lengthIndicator($this->const_QR_BARCODE_MODE_NM, $this->version);
		$p =1 ;
		while($this->isalnumat($this->dataStr, $p)) {
			if($this->isdigitat($this->dataStr, $p)) {
				$q = $p;
				while($this->isdigitat($this->dataStr, $q)) {
					$q++;
				}
				$dif = $this->estimateBitsModeAn($p) // + 4 + la
				+ $this->estimateBitsModeNum($q - $p) + 4 + $ln
				- $this->estimateBitsModeAn($q); // - 4 - la
				if($dif < 0) {
					break;
				} else {
					$p = $q;
				} //end if else
			} else {
				$p++;
			} //end if else
		} //end while
		$run = $p;
		if(!$this->isalnumat($this->dataStr, $p)) {
			$dif = $this->estimateBitsModeAn($run) + 4 + $la
			+ $this->estimateBitsMode8(1) // + 4 + l8
			- $this->estimateBitsMode8($run + 1); // - 4 - l8
			if($dif > 0) {
				return $this->eat8();
			} //end if
		} //end if
		$this->items = $this->appendNewInputItem($this->items, $this->const_QR_BARCODE_MODE_AN, $run, str_split($this->dataStr));
		return $run;
	} //END FUNCTION


	/**
	 * eatKanji
	 * @return int run
	 */
	 private function eatKanji() {
		$p = 0;
		while($this->identifyMode($p) == $this->const_QR_BARCODE_MODE_KJ) {
			$p += 2;
		} //end while
		$this->items = $this->appendNewInputItem($this->items, $this->const_QR_BARCODE_MODE_KJ, $p, str_split($this->dataStr));
		return $run;
	} //END FUNCTION


	/**
	 * eat8
	 * @return int run
	 */
	 private function eat8() {
		$la = $this->lengthIndicator($this->const_QR_BARCODE_MODE_AN, $this->version);
		$ln = $this->lengthIndicator($this->const_QR_BARCODE_MODE_NM, $this->version);
		$p = 1;
		$dataStrLen = strlen($this->dataStr);
		while($p < $dataStrLen) {
			$mode = $this->identifyMode($p);
			if($mode == $this->const_QR_BARCODE_MODE_KJ) {
				break;
			} //end if
			if($mode == $this->const_QR_BARCODE_MODE_NM) {
				$q = $p;
				while($this->isdigitat($this->dataStr, $q)) {
					$q++;
				} //end while
				$dif = $this->estimateBitsMode8($p) // + 4 + l8
				+ $this->estimateBitsModeNum($q - $p) + 4 + $ln
				- $this->estimateBitsMode8($q); // - 4 - l8
				if($dif < 0) {
					break;
				} else {
					$p = $q;
				} //end if else
			} elseif($mode == $this->const_QR_BARCODE_MODE_AN) {
				$q = $p;
				while($this->isalnumat($this->dataStr, $q)) {
					$q++;
				} //end while
				$dif = $this->estimateBitsMode8($p)  // + 4 + l8
				+ $this->estimateBitsModeAn($q - $p) + 4 + $la
				- $this->estimateBitsMode8($q); // - 4 - l8
				if($dif < 0) {
					break;
				} else {
					$p = $q;
				} //end if else
			} else {
				$p++;
			} //end if else
		} //end while
		$run = $p;
		$this->items = $this->appendNewInputItem($this->items, $this->const_QR_BARCODE_MODE_8B, $run, str_split($this->dataStr));
		return $run;
	} //END FUNCTION


	/**
	 * splitString
	 * @return (int)
	 */
	 private function splitString() {
		while(strlen($this->dataStr) > 0) {
			$mode = $this->identifyMode(0);
			switch($mode) {
				case $this->const_QR_BARCODE_MODE_NM:
					$length = $this->eatNum();
					break;
				case $this->const_QR_BARCODE_MODE_AN:
					$length = $this->eatAn();
					break;
				case $this->const_QR_BARCODE_MODE_KJ:
					if($hint == $this->const_QR_BARCODE_MODE_KJ) {
						$length = $this->eatKanji();
					} else {
						$length = $this->eat8();
					} //end if else
					break;
				default:
					$length = $this->eat8();
					break;
			} //end switch
			if($length == 0) {
				return 0;
			} //end if
			if($length < 0) {
				return -1;
			} //end if
			$this->dataStr = substr($this->dataStr, $length);
		} //end while
		return 0;
	} //END FUNCTION


	/**
	 * toUpper
	 */
	 private function toUpper() {
		$stringLen = strlen($this->dataStr);
		$p = 0;
		while($p < $stringLen) {
			$mode = $this->identifyMode(substr($this->dataStr, $p), $this->hint);
			if($mode == $this->const_QR_BARCODE_MODE_KJ) {
				$p += 2;
			} else {
				if((ord($this->dataStr[$p]) >= ord('a')) AND (ord($this->dataStr[$p]) <= ord('z'))) {
					$this->dataStr[$p] = chr(ord($this->dataStr[$p]) - 32);
				} //end if
				$p++;
			} //end if else
		} //end while
		return $this->dataStr;
	} //END FUNCTION


	//-- QRinputItem


	/**
	 * newInputItem
	 * @param $mode (int)
	 * @param $size (int)
	 * @param $data (array)
	 * @param $bstream (array)
	 * @return array input item
	 */
	 private function newInputItem($mode, $size, $data, $bstream=null) {
		$setData = array_slice($data, 0, $size);
		if(count($setData) < $size) {
			$setData = array_merge($setData, array_fill(0, ($size - count($setData)), 0));
		} //end if
		if(!$this->check($mode, $size, $setData)) {
			return NULL;
		} //end if
		$inputitem = array();
		$inputitem['mode'] = $mode;
		$inputitem['size'] = $size;
		$inputitem['data'] = $setData;
		$inputitem['bstream'] = $bstream;
		return $inputitem;
	} //END FUNCTION


	/**
	 * encodeModeNum
	 * @param $inputitem (array)
	 * @param $version (int)
	 * @return array input item
	 */
	 private function encodeModeNum($inputitem, $version) {
		$words = (int)($inputitem['size'] / 3);
		$inputitem['bstream'] = array();
		$val = 0x1;
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, $val);
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], $this->lengthIndicator($this->const_QR_BARCODE_MODE_NM, $version), $inputitem['size']);
		for($i=0; $i < $words; ++$i) {
			$val  = (ord($inputitem['data'][$i*3  ]) - ord('0')) * 100;
			$val += (ord($inputitem['data'][$i*3+1]) - ord('0')) * 10;
			$val += (ord($inputitem['data'][$i*3+2]) - ord('0'));
			$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 10, $val);
		} //end for
		if($inputitem['size'] - $words * 3 == 1) {
			$val = ord($inputitem['data'][$words*3]) - ord('0');
			$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, $val);
		} elseif(($inputitem['size'] - ($words * 3)) == 2) {
			$val  = (ord($inputitem['data'][$words*3  ]) - ord('0')) * 10;
			$val += (ord($inputitem['data'][$words*3+1]) - ord('0'));
			$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 7, $val);
		} //end if else
		return $inputitem;
	} //END FUNCTION


	/**
	 * encodeModeAn
	 * @param $inputitem (array)
	 * @param $version (int)
	 * @return array input item
	 */
	 private function encodeModeAn($inputitem, $version) {
		$words = (int)($inputitem['size'] / 2);
		$inputitem['bstream'] = array();
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, 0x02);
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], $this->lengthIndicator($this->const_QR_BARCODE_MODE_AN, $version), $inputitem['size']);
		for($i=0; $i < $words; ++$i) {
			$val  = (int)($this->lookAnTable(ord($inputitem['data'][$i*2])) * 45);
			$val += (int)($this->lookAnTable(ord($inputitem['data'][($i*2)+1])));
			$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 11, $val);
		} //end for
		if($inputitem['size'] & 1) {
			$val = $this->lookAnTable(ord($inputitem['data'][($words * 2)]));
			$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 6, $val);
		} //end if
		return $inputitem;
	} //END FUNCTION


	/**
	 * encodeMode8
	 * @param $inputitem (array)
	 * @param $version (int)
	 * @return array input item
	 */
	 private function encodeMode8($inputitem, $version) {
		$inputitem['bstream'] = array();
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, 0x4);
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], $this->lengthIndicator($this->const_QR_BARCODE_MODE_8B, $version), $inputitem['size']);
		for($i=0; $i < $inputitem['size']; ++$i) {
			$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 8, ord($inputitem['data'][$i]));
		} //end for
		return $inputitem;
	} //END FUNCTION


	/**
	 * encodeModeKanji
	 * @param $inputitem (array)
	 * @param $version (int)
	 * @return array input item
	 */
	 private function encodeModeKanji($inputitem, $version) {
		$inputitem['bstream'] = array();
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, 0x8);
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], $this->lengthIndicator($this->const_QR_BARCODE_MODE_KJ, $version), (int)($inputitem['size'] / 2));
		for($i=0; $i<$inputitem['size']; $i+=2) {
			$val = (ord($inputitem['data'][$i]) << 8) | ord($inputitem['data'][$i+1]);
			if($val <= 0x9ffc) {
				$val -= 0x8140;
			} else {
				$val -= 0xc140;
			} //end if else
			$h = ($val >> 8) * 0xc0;
			$val = ($val & 0xff) + $h;
			$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 13, $val);
		} //end for
		return $inputitem;
	} //END FUNCTION


	/**
	 * encodeModeStructure
	 * @param $inputitem (array)
	 * @return array input item
	 */
	 private function encodeModeStructure($inputitem) {
		$inputitem['bstream'] = array();
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, 0x03);
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, ord($inputitem['data'][1]) - 1);
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 4, ord($inputitem['data'][0]) - 1);
		$inputitem['bstream'] = $this->appendNum($inputitem['bstream'], 8, ord($inputitem['data'][2]));
		return $inputitem;
	} //END FUNCTION


	/**
	 * encodeBitStream
	 * @param $inputitem (array)
	 * @param $version (int)
	 * @return array input item
	 */
	 private function encodeBitStream($inputitem, $version) {
		$inputitem['bstream'] = array();
		$words = $this->maximumWords($inputitem['mode'], $version);
		if($inputitem['size'] > $words) {
			$st1 = $this->newInputItem($inputitem['mode'], $words, $inputitem['data']);
			$st2 = $this->newInputItem($inputitem['mode'], $inputitem['size'] - $words, array_slice($inputitem['data'], $words));
			$st1 = $this->encodeBitStream($st1, $version);
			$st2 = $this->encodeBitStream($st2, $version);
			$inputitem['bstream'] = array();
			$inputitem['bstream'] = $this->appendBitstream($inputitem['bstream'], $st1['bstream']);
			$inputitem['bstream'] = $this->appendBitstream($inputitem['bstream'], $st2['bstream']);
		} else {
			switch($inputitem['mode']) {
				case $this->const_QR_BARCODE_MODE_NM:
					$inputitem = $this->encodeModeNum($inputitem, $version);
					break;
				case $this->const_QR_BARCODE_MODE_AN:
					$inputitem = $this->encodeModeAn($inputitem, $version);
					break;
				case $this->const_QR_BARCODE_MODE_8B:
					$inputitem = $this->encodeMode8($inputitem, $version);
					break;
				case $this->const_QR_BARCODE_MODE_KJ:
					$inputitem = $this->encodeModeKanji($inputitem, $version);
					break;
				case $this->const_QR_BARCODE_MODE_ST:
					$inputitem = $this->encodeModeStructure($inputitem);
					break;
				default:
					break;
			} //end switch
		} //end if else
		return $inputitem;
	} //END FUNCTION


	//-- QRinput


	/**
	 * Append data to an input object.
	 * The data is copied and appended to the input object.
	 * @param $items (arrray) input items
	 * @param $mode (int) encoding mode.
	 * @param $size (int) size of data (byte).
	 * @param $data (array) array of input data.
	 * @return items
	 *
	 */
	private function appendNewInputItem($items, $mode, $size, $data) {
		$newitem = $this->newInputItem($mode, $size, $data);
		if(!empty($newitem)) {
			$items[] = $newitem;
		} //end if
		return $items;
	} //END FUNCTION


	/**
	 * insertStructuredAppendHeader
	 * @param $items (array)
	 * @param $size (int)
	 * @param $index (int)
	 * @param $parity (int)
	 * @return array items
	 */
	 private function insertStructuredAppendHeader($items, $size, $index, $parity) {
		if($size > $this->const_QR_BARCODE_STRUCT_MAX_SYMBOLS) {
			return -1;
		} //end if
		if(($index <= 0) OR ($index > $this->const_QR_BARCODE_STRUCT_MAX_SYMBOLS)) {
			return -1;
		} //end if
		$buf = array($size, $index, $parity);
		$entry = $this->newInputItem($this->const_QR_BARCODE_MODE_ST, 3, buf);
		array_unshift($items, $entry);
		return $items;
	} //END FUNCTION


	/**
	 * calcParity
	 * @param $items (array)
	 * @return int parity
	 */
	 private function calcParity($items) {
		$parity = 0;
		foreach($items as $u => $item) {
			if($item['mode'] != $this->const_QR_BARCODE_MODE_ST) {
				for($i=$item['size']-1; $i>=0; --$i) {
					$parity ^= $item['data'][$i];
				} //end for
			} //end if
		} //end foreach
		return $parity;
	} //END FUNCTION


	/**
	 * checkModeNum
	 * @param $size (int)
	 * @param $data (array)
	 * @return boolean true or false
	 */
	 private function checkModeNum($size, $data) {
		for($i=0; $i<$size; ++$i) {
			if((ord($data[$i]) < ord('0')) OR (ord($data[$i]) > ord('9'))) {
				return false;
			} //end if
		} //end for
		return true;
	} //END FUNCTION


	/**
	 * Look up the alphabet-numeric convesion table (see JIS X0510:2004, pp.19).
	 * @param $c (int) character value
	 * @return value
	 */
	private function lookAnTable($c) {
		return (($c > 127)?-1:$this->anTable[$c]);
	} //END FUNCTION


	/**
	 * checkModeAn
	 * @param $size (int)
	 * @param $data (array)
	 * @return boolean true or false
	 */
	private function checkModeAn($size, $data) {
		for($i=0; $i<$size; ++$i) {
			if($this->lookAnTable(ord($data[$i])) == -1) {
				return false;
			} //end if
		} //end for
		return true;
	} //END FUNCTION


	/**
	 * estimateBitsModeNum
	 * @param $size (int)
	 * @return int number of bits
	 */
	private function estimateBitsModeNum($size) {
		$w = (int)($size / 3);
		$bits = ($w * 10);
		switch($size - ($w * 3)) {
			case 1:
				$bits += 4;
				break;
			case 2:
				$bits += 7;
				break;
		} //end switch
		return $bits;
	} //END FUNCTION


	/**
	 * estimateBitsModeAn
	 * @param $size (int)
	 * @return int number of bits
	 */
	private function estimateBitsModeAn($size) {
		$bits = (int)($size * 5.5); // (size / 2 ) * 11
		if($size & 1) {
			$bits += 6;
		} //end if
		return $bits;
	} //END FUNCTION


	/**
	 * estimateBitsMode8
	 * @param $size (int)
	 * @return int number of bits
	 */
	private function estimateBitsMode8($size) {
		return (int)($size * 8);
	} //END FUNCTION


	/**
	 * estimateBitsModeKanji
	 * @param $size (int)
	 * @return int number of bits
	 */
	private function estimateBitsModeKanji($size) {
		return (int)($size * 6.5); // (size / 2 ) * 13
	} //END FUNCTION


	/**
	 * checkModeKanji
	 * @param $size (int)
	 * @param $data (array)
	 * @return boolean true or false
	 */
	private function checkModeKanji($size, $data) {
		if($size & 1) {
			return false;
		} //end if
		for($i=0; $i<$size; $i+=2) {
			$val = (ord($data[$i]) << 8) | ord($data[$i+1]);
			if(($val < 0x8140) OR (($val > 0x9ffc) AND ($val < 0xe040)) OR ($val > 0xebbf)) {
				return false;
			} //end if
		} //end for
		return true;
	} //END FUNCTION


	/**
	 * Validate the input data.
	 * @param $mode (int) encoding mode.
	 * @param $size (int) size of data (byte).
	 * @param $data (array) data to validate
	 * @return boolean true in case of valid data, false otherwise
	 */
	private function check($mode, $size, $data) {
		if($size <= 0) {
			return false;
		} //end if
		switch($mode) {
			case $this->const_QR_BARCODE_MODE_NM:
				return $this->checkModeNum($size, $data);
				break;
			case $this->const_QR_BARCODE_MODE_AN:
				return $this->checkModeAn($size, $data);
				break;
			case $this->const_QR_BARCODE_MODE_KJ:
				return $this->checkModeKanji($size, $data);
				break;
			case $this->const_QR_BARCODE_MODE_8B:
				return true;
				break;
			case $this->const_QR_BARCODE_MODE_ST:
				return true;
				break;
			default:
				break;
		} //end switch
		return false;
	} //END FUNCTION


	/**
	 * estimateBitStreamSize
	 * @param $items (array)
	 * @param $version (int)
	 * @return int bits
	 */
	 private function estimateBitStreamSize($items, $version) {
		$bits = 0;
		if($version == 0) {
			$version = 1;
		} //end if
		foreach($items as $u => $item) {
			switch($item['mode']) {
				case $this->const_QR_BARCODE_MODE_NM:
					$bits = $this->estimateBitsModeNum($item['size']);
					break;
				case $this->const_QR_BARCODE_MODE_AN:
					$bits = $this->estimateBitsModeAn($item['size']);
					break;
				case $this->const_QR_BARCODE_MODE_8B:
					$bits = $this->estimateBitsMode8($item['size']);
					break;
				case $this->const_QR_BARCODE_MODE_KJ:
					$bits = $this->estimateBitsModeKanji($item['size']);
					break;
				case $this->const_QR_BARCODE_MODE_ST:
					return $this->const_QR_BARCODE_STRUCT_HEAD_BITS;
				default:
					return 0;
			} //end switch
			$l = $this->lengthIndicator($item['mode'], $version);
			$m = 1 << $l;
			$num = (int)(($item['size'] + $m - 1) / $m);
			$bits += $num * (4 + $l);
		} //end foreach
		return $bits;
	} //END FUNCTION


	/**
	 * estimateVersion
	 * @param $items (array)
	 * @return int version
	 */
	 private function estimateVersion($items) {
		$version = 0;
		$prev = 0;
		do {
			$prev = $version;
			$bits = $this->estimateBitStreamSize($items, $prev);
			$version = $this->getMinimumVersion((int)(($bits + 7) / 8), $this->level);
			if($version < 0) {
				return -1;
			} //end if
		} while ($version > $prev);
		return $version;
	} //END FUNCTION


	/**
	 * lengthOfCode
	 * @param $mode (int)
	 * @param $version (int)
	 * @param $bits (int)
	 * @return int size
	 */
	 private function lengthOfCode($mode, $version, $bits) {
		$payload = $bits - 4 - $this->lengthIndicator($mode, $version);
		switch($mode) {
			case $this->const_QR_BARCODE_MODE_NM:
				$chunks = (int)($payload / 10);
				$remain = $payload - $chunks * 10;
				$size = $chunks * 3;
				if($remain >= 7) {
					$size += 2;
				} elseif($remain >= 4) {
					$size += 1;
				} //end if else
				break;
			case $this->const_QR_BARCODE_MODE_AN:
				$chunks = (int)($payload / 11);
				$remain = $payload - $chunks * 11;
				$size = $chunks * 2;
				if($remain >= 6) {
					++$size;
				} //end if
				break;
			case $this->const_QR_BARCODE_MODE_8B:
				$size = (int)($payload / 8);
				break;
			case $this->const_QR_BARCODE_MODE_KJ:
				$size = (int)(($payload / 13) * 2);
				break;
			case $this->const_QR_BARCODE_MODE_ST:
				$size = (int)($payload / 8);
				break;
			default:
				$size = 0;
				break;
		} //end switch
		$maxsize = $this->maximumWords($mode, $version);
		if($size < 0) {
			$size = 0;
		} //end if
		if($size > $maxsize) {
			$size = $maxsize;
		} //end if
		return $size;
	} //END FUNCTION


	/**
	 * createBitStream
	 * @param $items (array)
	 * @return array of items and total bits
	 */
	 private function createBitStream($items) {
		$total = 0;
		foreach($items as $key => $item) {
			$items[$key] = $this->encodeBitStream($item, $this->version);
			$bits = count($items[$key]['bstream']);
			$total += $bits;
		} //end foreach
		return array($items, $total);
	} //END FUNCTION


	/**
	 * convertData
	 * @param $items (array)
	 * @return array items
	 */
	 private function convertData($items) {
		$ver = $this->estimateVersion($items);
		if($ver > $this->version) {
			$this->version = $ver;
		} //end if
		while(true) {
			$cbs = $this->createBitStream($items);
			$items = $cbs[0];
			$bits = $cbs[1];
			if($bits < 0) {
				return -1;
			} //end if
			$ver = $this->getMinimumVersion((int)(($bits + 7) / 8), $this->level);
			if($ver < 0) {
				return -1;
			} elseif($ver > $this->version) {
				$this->version = $ver;
			} else {
				break;
			} //end if else
		} //end while
		return $items;
	} //END FUNCTION


	/**
	 * Append Padding Bit to bitstream
	 * @param $bstream (array)
	 * @return array bitstream
	 */
	 private function appendPaddingBit($bstream) {
	 	if(is_null($bstream)) {
	 		return null;
	 	} //end if
		$bits = count($bstream);
		$maxwords = $this->getDataLength($this->version, $this->level);
		$maxbits = $maxwords * 8;
		if($maxbits == $bits) {
			return $bstream;
		} //end if
		if($maxbits - $bits < 5) {
			return $this->appendNum($bstream, $maxbits - $bits, 0);
		} //end if
		$bits += 4;
		$words = (int)(($bits + 7) / 8);
		$padding = array();
		$padding = $this->appendNum($padding, $words * 8 - $bits + 4, 0);
		$padlen = $maxwords - $words;
		if($padlen > 0) {
			$padbuf = array();
			for($i=0; $i<$padlen; ++$i) {
				$padbuf[$i] = ($i&1)?0x11:0xec;
			} //end for
			$padding = $this->appendBytes($padding, $padlen, $padbuf);
		} //end if
		return $this->appendBitstream($bstream, $padding);
	} //END FUNCTION


	/**
	 * mergeBitStream
	 * @param $items (array) items
	 * @return array bitstream
	 */
	 private function mergeBitStream($items) {
		$items = $this->convertData($items);
		if(!is_array($items)) {
			return null;
		} //end if
		$bstream = array();
		foreach($items as $u => $item) {
			$bstream = $this->appendBitstream($bstream, $item['bstream']);
		} //end foreach
		return $bstream;
	} //END FUNCTION


	/**
	 * Returns a stream of bits.
	 * @param $items (int)
	 * @return array padded merged byte stream
	 */
	private function getBitStream($items) {
		$bstream = $this->mergeBitStream($items);
		return $this->appendPaddingBit($bstream);
	} //END FUNCTION


	/**
	 * Pack all bit streams padding bits into a byte array.
	 * @param $items (int)
	 * @return array padded merged byte stream
	 */
	private function getByteStream($items) {
		$bstream = $this->getBitStream($items);
		return $this->bitstreamToByte($bstream);
	} //END FUNCTION


	//-- QRbitstream


	/**
	 * Return an array with zeros
	 * @param $setLength (int) array size
	 * @return array
	 */
	private function allocate($setLength) {
		return array_fill(0, $setLength, 0);
	} //END FUNCTION


	/**
	 * Return new bitstream from number
	 * @param $bits (int) number of bits
	 * @param $num (int) number
	 * @return array bitstream
	 */
	private function newFromNum($bits, $num) {
		$bstream = $this->allocate($bits);
		$mask = 1 << ($bits - 1);
		for($i=0; $i<$bits; ++$i) {
			if($num & $mask) {
				$bstream[$i] = 1;
			} else {
				$bstream[$i] = 0;
			} //end if else
			$mask = $mask >> 1;
		} //end for
		return $bstream;
	} //END FUNCTION


	/**
	 * Return new bitstream from bytes
	 * @param $size (int) size
	 * @param $data (array) bytes
	 * @return array bitstream
	 */
	private function newFromBytes($size, $data) {
		$bstream = $this->allocate($size * 8);
		$p=0;
		for($i=0; $i<$size; ++$i) {
			$mask = 0x80;
			for($j=0; $j<8; ++$j) {
				if($data[$i] & $mask) {
					$bstream[$p] = 1;
				} else {
					$bstream[$p] = 0;
				} //end if else
				$p++;
				$mask = $mask >> 1;
			} //end for
		} //end for
		return $bstream;
	} //END FUNCTION


	/**
	 * Append one bitstream to another
	 * @param $bitstream (array) original bitstream
	 * @param $append (array) bitstream to append
	 * @return array bitstream
	 */
	private function appendBitstream($bitstream, $append) {
		if((!is_array($append)) OR (count($append) == 0)) {
			return $bitstream;
		} //end if
		if(count($bitstream) == 0) {
			return $append;
		} //end if
		return array_values(array_merge($bitstream, $append));
	} //END FUNCTION


	/**
	 * Append one bitstream created from number to another
	 * @param $bitstream (array) original bitstream
	 * @param $bits (int) number of bits
	 * @param $num (int) number
	 * @return array bitstream
	 */
	private function appendNum($bitstream, $bits, $num) {
		if($bits == 0) {
			return 0;
		} //end if
		$b = $this->newFromNum($bits, $num);
		return $this->appendBitstream($bitstream, $b);
	} //END FUNCTION


	/**
	 * Append one bitstream created from bytes to another
	 * @param $bitstream (array) original bitstream
	 * @param $size (int) size
	 * @param $data (array) bytes
	 * @return array bitstream
	 */
	private function appendBytes($bitstream, $size, $data) {
		if($size == 0) {
			return 0;
		} //end if
		$b = $this->newFromBytes($size, $data);
		return $this->appendBitstream($bitstream, $b);
	} //END FUNCTION


	/**
	 * Convert bitstream to bytes
	 * @param $bstream (array) original bitstream
	 * @return array of bytes
	 */
	private function bitstreamToByte($bstream) {
		if(is_null($bstream)) {
	 		return null;
	 	} //end if
		$size = count($bstream);
		if($size == 0) {
			return array();
		} //end if
		$data = array_fill(0, (int)(($size + 7) / 8), 0);
		$bytes = (int)($size / 8);
		$p = 0;
		for($i=0; $i<$bytes; $i++) {
			$v = 0;
			for($j=0; $j<8; $j++) {
				$v = $v << 1;
				$v |= $bstream[$p];
				$p++;
			} //end for
			$data[$i] = $v;
		} //end for
		if($size & 7) {
			$v = 0;
			for($j=0; $j<($size & 7); $j++) {
				$v = $v << 1;
				$v |= $bstream[$p];
				$p++;
			} //end for
			$data[$bytes] = $v;
		} //end if
		return $data;
	} //END FUNCTION


	//-- QRspec


	/**
	 * Replace a value on the array at the specified position
	 * @param $srctab (array)
	 * @param $x (int) X position
	 * @param $y (int) Y position
	 * @param $repl (string) value to replace
	 * @param $replLen (int) length of the repl string
	 * @return array srctab
	 */
	private function qrstrset($srctab, $x, $y, $repl, $replLen=false) {
		$srctab[$y] = substr_replace($srctab[$y], ($replLen !== false)?substr($repl,0,$replLen):$repl, $x, ($replLen !== false)?$replLen:strlen($repl));
		return $srctab;
	} //END FUNCTION


	/**
	 * Return maximum data code length (bytes) for the version.
	 * @param $version (int) version
	 * @param $level (int) error correction level
	 * @return int maximum size (bytes)
	 */
	private function getDataLength($version, $level) {
		return $this->capacity[$version][$this->const_QR_BARCODE_CAP_WORDS] - $this->capacity[$version][$this->const_QR_BARCODE_CAP_ECC][$level];
	} //END FUNCTION


	/**
	 * Return maximum error correction code length (bytes) for the version.
	 * @param $version (int) version
	 * @param $level (int) error correction level
	 * @return int ECC size (bytes)
	 */
	private function getECCLength($version, $level){
		return $this->capacity[$version][$this->const_QR_BARCODE_CAP_ECC][$level];
	} //END FUNCTION


	/**
	 * Return the width of the symbol for the version.
	 * @param $version (int) version
	 * @return int width
	 */
	private function getWidth($version) {
		return $this->capacity[$version][$this->const_QR_BARCODE_CAP_WIDTH];
	} //END FUNCTION


	/**
	 * Return the numer of remainder bits.
	 * @param $version (int) version
	 * @return int number of remainder bits
	 */
	private function getRemainder($version) {
		return $this->capacity[$version][$this->const_QR_BARCODE_CAP_REMINDER];
	} //END FUNCTION


	/**
	 * Return a version number that satisfies the input code length.
	 * @param $size (int) input code length (bytes)
	 * @param $level (int) error correction level
	 * @return int version number
	 */
	private function getMinimumVersion($size, $level) {
		for($i = 1; $i <= $this->const_QR_BARCODE_SPEC_VERSION_MAX; ++$i) {
			$words = ($this->capacity[$i][$this->const_QR_BARCODE_CAP_WORDS] - $this->capacity[$i][$this->const_QR_BARCODE_CAP_ECC][$level]);
			if($words >= $size) {
				return $i;
			} //end if
		} //end for
		// the size of input data is greater than QR capacity, try to lover the error correction mode
		return -1;
	} //END FUNCTION


	/**
	 * Return the size of length indicator for the mode and version.
	 * @param $mode (int) encoding mode
	 * @param $version (int) version
	 * @return int the size of the appropriate length indicator (bits).
	 */
	private function lengthIndicator($mode, $version) {
		if($mode == $this->const_QR_BARCODE_MODE_ST) {
			return 0;
		} //end if
		if($version <= 9) {
			$l = 0;
		} elseif($version <= 26) {
			$l = 1;
		} else {
			$l = 2;
		} //end if else
		return $this->lengthTableBits[$mode][$l];
	} //END FUNCTION


	/**
	 * Return the maximum length for the mode and version.
	 * @param $mode (int) encoding mode
	 * @param $version (int) version
	 * @return int the maximum length (bytes)
	 */
	private function maximumWords($mode, $version) {
		if($mode == $this->const_QR_BARCODE_MODE_ST) {
			return 3;
		} //end if
		if($version <= 9) {
			$l = 0;
		} elseif($version <= 26) {
			$l = 1;
		} else {
			$l = 2;
		} //end if else
		$bits = $this->lengthTableBits[$mode][$l];
		$words = (1 << $bits) - 1;
		if($mode == $this->const_QR_BARCODE_MODE_KJ) {
			$words *= 2; // the number of bytes is required
		} //end if
		return $words;
	} //END FUNCTION


	/**
	 * Return an array of ECC specification.
	 * @param $version (int) version
	 * @param $level (int) error correction level
	 * @param $spec (array) an array of ECC specification contains as following: {# of type1 blocks, # of data code, # of ecc code, # of type2 blocks, # of data code}
	 * @return array spec
	 */
	private function getEccSpec($version, $level, $spec) {
		if(count($spec) < 5) {
			$spec = array(0, 0, 0, 0, 0);
		} //end if
		$b1 = $this->eccTable[$version][$level][0];
		$b2 = $this->eccTable[$version][$level][1];
		$data = $this->getDataLength($version, $level);
		$ecc = $this->getECCLength($version, $level);
		if($b2 == 0) {
			$spec[0] = $b1;
			$spec[1] = (int)($data / $b1);
			$spec[2] = (int)($ecc / $b1);
			$spec[3] = 0;
			$spec[4] = 0;
		} else {
			$spec[0] = $b1;
			$spec[1] = (int)($data / ($b1 + $b2));
			$spec[2] = (int)($ecc  / ($b1 + $b2));
			$spec[3] = $b2;
			$spec[4] = $spec[1] + 1;
		} //end if else
		return $spec;
	} //END FUNCTION


	/**
	 * Put an alignment marker.
	 * @param $frame (array) frame
	 * @param $ox (int) X center coordinate of the pattern
	 * @param $oy (int) Y center coordinate of the pattern
	 * @return array frame
	 */
	private function putAlignmentMarker($frame, $ox, $oy) {
		$finder = array(
			"\xa1\xa1\xa1\xa1\xa1",
			"\xa1\xa0\xa0\xa0\xa1",
			"\xa1\xa0\xa1\xa0\xa1",
			"\xa1\xa0\xa0\xa0\xa1",
			"\xa1\xa1\xa1\xa1\xa1"
			);
		$yStart = $oy - 2;
		$xStart = $ox - 2;
		for($y=0; $y < 5; $y++) {
			$frame = $this->qrstrset($frame, $xStart, $yStart+$y, $finder[$y]);
		} //end for
		return $frame;
	} //END FUNCTION


	/**
	 * Put an alignment pattern.
	 * @param $version (int) version
	 * @param $frame (array) frame
	 * @param $width (int) width
	 * @return array frame
	 */
	 private function putAlignmentPattern($version, $frame, $width) {
		if($version < 2) {
			return $frame;
		} //end if
		$d = $this->alignmentPattern[$version][1] - $this->alignmentPattern[$version][0];
		if($d < 0) {
			$w = 2;
		} else {
			$w = (int)(($width - $this->alignmentPattern[$version][0]) / $d + 2);
		} //end if else
		if($w * $w - 3 == 1) {
			$x = $this->alignmentPattern[$version][0];
			$y = $this->alignmentPattern[$version][0];
			$frame = $this->putAlignmentMarker($frame, $x, $y);
			return $frame;
		} //end if
		$cx = $this->alignmentPattern[$version][0];
		$wo = $w - 1;
		for($x=1; $x < $wo; ++$x) {
			$frame = $this->putAlignmentMarker($frame, 6, $cx);
			$frame = $this->putAlignmentMarker($frame, $cx,  6);
			$cx += $d;
		} //end for
		$cy = $this->alignmentPattern[$version][0];
		for($y=0; $y < $wo; ++$y) {
			$cx = $this->alignmentPattern[$version][0];
			for($x=0; $x < $wo; ++$x) {
				$frame = $this->putAlignmentMarker($frame, $cx, $cy);
				$cx += $d;
			} //end for
			$cy += $d;
		} //end for
		return $frame;
	} //END FUNCTION


	/**
	 * Return BCH encoded version information pattern that is used for the symbol of version 7 or greater. Use lower 18 bits.
	 * @param $version (int) version
	 * @return BCH encoded version information pattern
	 */
	private function getVersionPattern($version) {
		if(($version < 7) OR ($version > $this->const_QR_BARCODE_SPEC_VERSION_MAX)) {
			return 0;
		} //end if
		return $this->versionPattern[($version - 7)];
	} //END FUNCTION


	/**
	 * Return BCH encoded format information pattern.
	 * @param $mask (array)
	 * @param $level (int) error correction level
	 * @return BCH encoded format information pattern
	 */
	private function getFormatInfo($mask, $level) {
		if(($mask < 0) OR ($mask > 7)) {
			return 0;
		} //end if
		if(($level < 0) OR ($level > 3)) {
			return 0;
		} //end if
		return $this->formatInfo[$level][$mask];
	} //END FUNCTION


	/**
	 * Put a finder pattern.
	 * @param $frame (array) frame
	 * @param $ox (int) X center coordinate of the pattern
	 * @param $oy (int) Y center coordinate of the pattern
	 * @return array frame
	 */
	private function putFinderPattern($frame, $ox, $oy) {
		$finder = array(
		"\xc1\xc1\xc1\xc1\xc1\xc1\xc1",
		"\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
		"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
		"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
		"\xc1\xc0\xc1\xc1\xc1\xc0\xc1",
		"\xc1\xc0\xc0\xc0\xc0\xc0\xc1",
		"\xc1\xc1\xc1\xc1\xc1\xc1\xc1"
		);
		for($y=0; $y < 7; $y++) {
			$frame = $this->qrstrset($frame, $ox, ($oy + $y), $finder[$y]);
		} //end for
		return $frame;
	} //END FUNCTION


	/**
	 * Return a copy of initialized frame.
	 * @param $version (int) version
	 * @return Array of unsigned char.
	 */
	private function createFrame($version) {
		//--
		$width = $this->capacity[$version][$this->const_QR_BARCODE_CAP_WIDTH];
		$frameLine = str_repeat ("\0", $width);
		$frame = array_fill(0, $width, $frameLine);
		//-- Finder pattern
		$frame = $this->putFinderPattern($frame, 0, 0);
		$frame = $this->putFinderPattern($frame, $width - 7, 0);
		$frame = $this->putFinderPattern($frame, 0, $width - 7);
		//-- Separator
		$yOffset = $width - 7;
		for($y=0; $y < 7; ++$y) {
			$frame[$y][7] = "\xc0";
			$frame[$y][$width - 8] = "\xc0";
			$frame[$yOffset][7] = "\xc0";
			++$yOffset;
		} //end for
		$setPattern = str_repeat("\xc0", 8);
		$frame = $this->qrstrset($frame, 0, 7, $setPattern);
		$frame = $this->qrstrset($frame, $width-8, 7, $setPattern);
		$frame = $this->qrstrset($frame, 0, $width - 8, $setPattern);
		//-- Format info
		$setPattern = str_repeat("\x84", 9);
		$frame = $this->qrstrset($frame, 0, 8, $setPattern);
		$frame = $this->qrstrset($frame, $width - 8, 8, $setPattern, 8);
		$yOffset = $width - 8;
		for($y=0; $y < 8; ++$y,++$yOffset) {
			$frame[$y][8] = "\x84";
			$frame[$yOffset][8] = "\x84";
		} //end for
		//-- Timing pattern
		$wo = $width - 15;
		for($i=1; $i < $wo; ++$i) {
			$frame[6][7+$i] = chr(0x90 | ($i & 1));
			$frame[7+$i][6] = chr(0x90 | ($i & 1));
		} //end for
		//-- Alignment pattern
		$frame = $this->putAlignmentPattern($version, $frame, $width);
		//-- Version information
		if($version >= 7) {
			$vinf = $this->getVersionPattern($version);
			$v = $vinf;
			for($x=0; $x<6; ++$x) {
				for($y=0; $y<3; ++$y) {
					$frame[($width - 11)+$y][$x] = chr(0x88 | ($v & 1));
					$v = $v >> 1;
				} //end for
			} //end for
			$v = $vinf;
			for($y=0; $y<6; ++$y) {
				for($x=0; $x<3; ++$x) {
					$frame[$y][$x+($width - 11)] = chr(0x88 | ($v & 1));
					$v = $v >> 1;
				} //end for
			} //end for
		} //end if
		//-- and a little bit...
		$frame[$width - 8][8] = "\x81";
		//--
		return $frame;
		//--
	} //END FUNCTION


	/**
	 * Set new frame for the specified version.
	 * @param $version (int) version
	 * @return Array of unsigned char.
	 */
	private function newFrame($version) {
		if(($version < 1) OR ($version > $this->const_QR_BARCODE_SPEC_VERSION_MAX)) {
			return NULL;
		} //end if
		if(!isset($this->frames[$version])) {
			$this->frames[$version] = $this->createFrame($version);
		} //end if
		if(is_null($this->frames[$version])) {
			return NULL;
		} //end if
		return $this->frames[$version];
	} //END FUNCTION


	/**
	 * Return block number 0
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsBlockNum($spec) {
		return ($spec[0] + $spec[3]);
	} //END FUNCTION


	/**
	* Return block number 1
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsBlockNum1($spec) {
		return $spec[0];
	} //END FUNCTION


	/**
	 * Return data codes 1
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsDataCodes1($spec) {
		return $spec[1];
	} //END FUNCTION


	/**
	 * Return ecc codes 1
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsEccCodes1($spec) {
		return $spec[2];
	} //END FUNCTION


	/**
	 * Return block number 2
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsBlockNum2($spec) {
		return $spec[3];
	} //END FUNCTION


	/**
	 * Return data codes 2
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsDataCodes2($spec) {
		return $spec[4];
	} //END FUNCTION


	/**
	 * Return ecc codes 2
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsEccCodes2($spec) {
		return $spec[2];
	} //END FUNCTION


	/**
	 * Return data length
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsDataLength($spec) {
		return ($spec[0] * $spec[1]) + ($spec[3] * $spec[4]);
	} //END FUNCTION


	/**
	 * Return ecc length
	 * @param $spec (array)
	 * @return int value
	 */
	private function rsEccLength($spec) {
		return ($spec[0] + $spec[3]) * $spec[2];
	} //END FUNCTION


	//-- QRrs


	/**
	 * Initialize a Reed-Solomon codec and add it to existing rsitems
	 * @param $symsize (int) symbol size, bits
	 * @param $gfpoly (int)  Field generator polynomial coefficients
	 * @param $fcr (int)  first root of RS code generator polynomial, index form
	 * @param $prim (int)  primitive element to generate polynomial roots
	 * @param $nroots (int) RS code generator polynomial degree (number of roots)
	 * @param $pad (int)  padding bytes at front of shortened block
	 * @return array Array of RS values:<ul><li>mm = Bits per symbol;</li><li>nn = Symbols per block;</li><li>alpha_to = log lookup table array;</li><li>index_of = Antilog lookup table array;</li><li>genpoly = Generator polynomial array;</li><li>nroots = Number of generator;</li><li>roots = number of parity symbols;</li><li>fcr = First consecutive root, index form;</li><li>prim = Primitive element, index form;</li><li>iprim = prim-th root of 1, index form;</li><li>pad = Padding bytes in shortened block;</li><li>gfpoly</ul>.
	 */
	private function init_rs($symsize, $gfpoly, $fcr, $prim, $nroots, $pad) {
		foreach($this->rsitems as $u => $rs) {
			if(($rs['pad'] != $pad) OR ($rs['nroots'] != $nroots) OR ($rs['mm'] != $symsize) OR ($rs['gfpoly'] != $gfpoly) OR ($rs['fcr'] != $fcr) OR ($rs['prim'] != $prim)) {
				continue;
			} //end if
			return $rs;
		} //end foreach
		$rs = $this->init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad);
		array_unshift($this->rsitems, $rs);
		return $rs;
	} //END FUNCTION


	//-- QRrsItem


	/**
	 * modnn
	 * @param $rs (array) RS values
	 * @param $x (int) X position
	 * @return int X osition
	 */
	private function modnn($rs, $x) {
		while($x >= $rs['nn']) {
			$x -= $rs['nn'];
			$x = ($x >> $rs['mm']) + ($x & $rs['nn']);
		} //end while
		return $x;
	} //END FUNCTION


	/**
	 * Initialize a Reed-Solomon codec and returns an array of values.
	 * @param $symsize (int) symbol size, bits
	 * @param $gfpoly (int)  Field generator polynomial coefficients
	 * @param $fcr (int)  first root of RS code generator polynomial, index form
	 * @param $prim (int)  primitive element to generate polynomial roots
	 * @param $nroots (int) RS code generator polynomial degree (number of roots)
	 * @param $pad (int)  padding bytes at front of shortened block
	 * @return array Array of RS values:<ul><li>mm = Bits per symbol;</li><li>nn = Symbols per block;</li><li>alpha_to = log lookup table array;</li><li>index_of = Antilog lookup table array;</li><li>genpoly = Generator polynomial array;</li><li>nroots = Number of generator;</li><li>roots = number of parity symbols;</li><li>fcr = First consecutive root, index form;</li><li>prim = Primitive element, index form;</li><li>iprim = prim-th root of 1, index form;</li><li>pad = Padding bytes in shortened block;</li><li>gfpoly</ul>.
	 */
	private function init_rs_char($symsize, $gfpoly, $fcr, $prim, $nroots, $pad) {
		// Based on Reed solomon encoder by Phil Karn, KA9Q (GNU-LGPLv2)
		$rs = null;
		// Check parameter ranges
		if(($symsize < 0) OR ($symsize > 8)) {
			return $rs;
		} //end if
		if(($fcr < 0) OR ($fcr >= (1<<$symsize))) {
			return $rs;
		} //end if
		if(($prim <= 0) OR ($prim >= (1<<$symsize))) {
			return $rs;
		} //end if
		if(($nroots < 0) OR ($nroots >= (1<<$symsize))) {
			return $rs;
		} //end if
		if(($pad < 0) OR ($pad >= ((1<<$symsize) -1 - $nroots))) {
			return $rs;
		} //end if
		$rs = array();
		$rs['mm'] = $symsize;
		$rs['nn'] = (1 << $symsize) - 1;
		$rs['pad'] = $pad;
		$rs['alpha_to'] = array_fill(0, ($rs['nn'] + 1), 0);
		$rs['index_of'] = array_fill(0, ($rs['nn'] + 1), 0);
		// PHP style macro replacement ;)
		$NN =& $rs['nn'];
		$A0 =& $NN;
		// Generate Galois field lookup tables
		$rs['index_of'][0] = $A0; // log(zero) = -inf
		$rs['alpha_to'][$A0] = 0; // alpha**-inf = 0
		$sr = 1;
		for($i=0; $i<$rs['nn']; ++$i) {
			$rs['index_of'][$sr] = $i;
			$rs['alpha_to'][$i] = $sr;
			$sr <<= 1;
			if($sr & (1 << $symsize)) {
				$sr ^= $gfpoly;
			} //end if
			$sr &= $rs['nn'];
		} //end for
		if($sr != 1) {
			// field generator polynomial is not primitive!
			return NULL;
		} //end if
		// Form RS code generator polynomial from its roots
		$rs['genpoly'] = array_fill(0, ($nroots + 1), 0);
		$rs['fcr'] = $fcr;
		$rs['prim'] = $prim;
		$rs['nroots'] = $nroots;
		$rs['gfpoly'] = $gfpoly;
		// Find prim-th root of 1, used in decoding
		$fake_iterator = 0;
		for($iprim=1; ($iprim % $prim) != 0; $iprim += $rs['nn']) {
			$fake_iterator++; // intentional empty-body loop! (only fake iterator :: unixman)
		} //end for
		$rs['iprim'] = (int)($iprim / $prim);
		$rs['genpoly'][0] = 1;
		for($i = 0,$root=$fcr*$prim; $i < $nroots; $i++, $root += $prim) {
			$rs['genpoly'][$i+1] = 1;
			// Multiply rs->genpoly[] by  @**(root + x)
			for($j = $i; $j > 0; --$j) {
				if($rs['genpoly'][$j] != 0) {
					$rs['genpoly'][$j] = $rs['genpoly'][$j-1] ^ $rs['alpha_to'][$this->modnn($rs, $rs['index_of'][$rs['genpoly'][$j]] + $root)];
				} else {
					$rs['genpoly'][$j] = $rs['genpoly'][$j-1];
				} //end if else
			} //end for
			// rs->genpoly[0] can never be zero
			$rs['genpoly'][0] = $rs['alpha_to'][$this->modnn($rs, $rs['index_of'][$rs['genpoly'][0]] + $root)];
		} //end for
		// convert rs->genpoly[] to index form for quicker encoding
		for($i = 0; $i <= $nroots; ++$i) {
			$rs['genpoly'][$i] = $rs['index_of'][$rs['genpoly'][$i]];
		} //end for
		return $rs;
	} //END FUNCTION


	/**
	 * Encode a Reed-Solomon codec and returns the parity array
	 * @param $rs (array) RS values
	 * @param $data (array) data
	 * @param $parity (array) parity
	 * @return parity array
	 */
	 private function encode_rs_char($rs, $data, $parity) {
		$MM       =& $rs['mm']; // bits per symbol
		$NN       =& $rs['nn']; // the total number of symbols in a RS block
		$ALPHA_TO =& $rs['alpha_to']; // the address of an array of NN elements to convert Galois field elements in index (log) form to polynomial form
		$INDEX_OF =& $rs['index_of']; // the address of an array of NN elements to convert Galois field elements in polynomial form to index (log) form
		$GENPOLY  =& $rs['genpoly']; // an array of NROOTS+1 elements containing the generator polynomial in index form
		$NROOTS   =& $rs['nroots']; // the number of roots in the RS code generator polynomial, which is the same as the number of parity symbols in a block
		$FCR      =& $rs['fcr']; // first consecutive root, index form
		$PRIM     =& $rs['prim']; // primitive element, index form
		$IPRIM    =& $rs['iprim']; // prim-th root of 1, index form
		$PAD      =& $rs['pad']; // the number of pad symbols in a block
		$A0       =& $NN;
		$parity = array_fill(0, $NROOTS, 0);
		for($i=0; $i < ($NN - $NROOTS - $PAD); $i++) {
			$feedback = $INDEX_OF[$data[$i] ^ $parity[0]];
			if($feedback != $A0) {
				// feedback term is non-zero
				// This line is unnecessary when GENPOLY[NROOTS] is unity, as it must
				// always be for the polynomials constructed by init_rs()
				$feedback = $this->modnn($rs, $NN - $GENPOLY[$NROOTS] + $feedback);
				for($j=1; $j < $NROOTS; ++$j) {
					$parity[$j] ^= $ALPHA_TO[$this->modnn($rs, $feedback + $GENPOLY[($NROOTS - $j)])];
				} //end for
			} //end if
			// Shift
			array_shift($parity);
			if($feedback != $A0) {
				array_push($parity, $ALPHA_TO[$this->modnn($rs, $feedback + $GENPOLY[0])]);
			} else {
				array_push($parity, 0);
			} //end if else
		} //end for
		return $parity;
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

//============================================================
// BarCode 2D: DataMatrix (Semacode)
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create DataMatrix ECC 200 barcode arrays.
// DataMatrix (ISO/IEC 16022:2006) is a 2-D bar code.
// TECHNICAL DATA / FEATURES OF SEMACODE:
// * Encodable Character Set: 		UTF-8
// * Code Type: 					Matrix
// * Error Correction: 				Auto
// * Maximum Data Characters: 		3116 numeric, 2335 alphanumeric (ISO-8859-1), 1556 Binary / Bytes (UTF-8)
//============================================================
//
// This class is derived from the following projects:
//
// "TcPDF" / Barcodes 2D / 1.0.008 / 20140506
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class Smart BarCode 2D DataMatrix
 *
 * @access 		private
 * @internal
 *
 */
final class SmartBarcode2D_DataMatrix {

	// ->
	// v.160827


	const SEMACODE_ENCODE_ASCII = 0; 		// ASCII encoding: ASCII character 0 to 127 (1 byte per CW)
	const SEMACODE_ENCODE_C40 = 1; 			// C40 encoding: Upper-case alphanumeric (3/2 bytes per CW)
	const SEMACODE_ENCODE_TXT = 2; 			// TEXT encoding: Lower-case alphanumeric (3/2 bytes per CW)
	const SEMACODE_ENCODE_X12 = 3; 			// X12 encoding: ANSI X12 (3/2 byte per CW)
	const SEMACODE_ENCODE_EDF = 4; 			// EDIFACT encoding: ASCII character 32 to 94 (4/3 bytes per CW)
	const SEMACODE_ENCODE_B256 = 5; 		// BASE 256 encoding: ASCII character 0 to 255 (1 byte per CW)
	const SEMACODE_ENCODE_EXTASCII = 6; 	// ASCII extended encoding: ASCII character 128 to 255 (1/2 byte per CW)
	const SEMACODE_ENCODE_NUMASCII = 7; 	// ASCII number encoding: ASCII digits (2 bytes per CW)


	/**
	 * Barcode array to be returned which is readable by TCPDF.
	 * @private
	 */
	private $barcode_array = array();

	/**
	 * Store last used encoding for data codewords.
	 * @private
	 */
	private $last_enc = self::SEMACODE_ENCODE_ASCII;

	/**
	 * Table of Data Matrix ECC 200 Symbol Attributes:<ul>
	 * <li>total matrix rows (including finder pattern)</li>
	 * <li>total matrix cols (including finder pattern)</li>
	 * <li>total matrix rows (without finder pattern)</li>
	 * <li>total matrix cols (without finder pattern)</li>
	 * <li>region data rows (with finder pattern)</li>
	 * <li>region data col (with finder pattern)</li>
	 * <li>region data rows (without finder pattern)</li>
	 * <li>region data col (without finder pattern)</li>
	 * <li>horizontal regions</li>
	 * <li>vertical regions</li>
	 * <li>regions</li>
	 * <li>data codewords</li>
	 * <li>error codewords</li>
	 * <li>blocks</li>
	 * <li>data codewords per block</li>
	 * <li>error codewords per block</li>
	 * </ul>
	 * @private
	 */
	private $symbattr = array(
		// square form ---------------------------------------------------------------------------------------
		array(0x00a,0x00a,0x008,0x008,0x00a,0x00a,0x008,0x008,0x001,0x001,0x001,0x003,0x005,0x001,0x003,0x005), // 10x10
		array(0x00c,0x00c,0x00a,0x00a,0x00c,0x00c,0x00a,0x00a,0x001,0x001,0x001,0x005,0x007,0x001,0x005,0x007), // 12x12
		array(0x00e,0x00e,0x00c,0x00c,0x00e,0x00e,0x00c,0x00c,0x001,0x001,0x001,0x008,0x00a,0x001,0x008,0x00a), // 14x14
		array(0x010,0x010,0x00e,0x00e,0x010,0x010,0x00e,0x00e,0x001,0x001,0x001,0x00c,0x00c,0x001,0x00c,0x00c), // 16x16
		array(0x012,0x012,0x010,0x010,0x012,0x012,0x010,0x010,0x001,0x001,0x001,0x012,0x00e,0x001,0x012,0x00e), // 18x18
		array(0x014,0x014,0x012,0x012,0x014,0x014,0x012,0x012,0x001,0x001,0x001,0x016,0x012,0x001,0x016,0x012), // 20x20
		array(0x016,0x016,0x014,0x014,0x016,0x016,0x014,0x014,0x001,0x001,0x001,0x01e,0x014,0x001,0x01e,0x014), // 22x22
		array(0x018,0x018,0x016,0x016,0x018,0x018,0x016,0x016,0x001,0x001,0x001,0x024,0x018,0x001,0x024,0x018), // 24x24
		array(0x01a,0x01a,0x018,0x018,0x01a,0x01a,0x018,0x018,0x001,0x001,0x001,0x02c,0x01c,0x001,0x02c,0x01c), // 26x26
		array(0x020,0x020,0x01c,0x01c,0x010,0x010,0x00e,0x00e,0x002,0x002,0x004,0x03e,0x024,0x001,0x03e,0x024), // 32x32
		array(0x024,0x024,0x020,0x020,0x012,0x012,0x010,0x010,0x002,0x002,0x004,0x056,0x02a,0x001,0x056,0x02a), // 36x36
		array(0x028,0x028,0x024,0x024,0x014,0x014,0x012,0x012,0x002,0x002,0x004,0x072,0x030,0x001,0x072,0x030), // 40x40
		array(0x02c,0x02c,0x028,0x028,0x016,0x016,0x014,0x014,0x002,0x002,0x004,0x090,0x038,0x001,0x090,0x038), // 44x44
		array(0x030,0x030,0x02c,0x02c,0x018,0x018,0x016,0x016,0x002,0x002,0x004,0x0ae,0x044,0x001,0x0ae,0x044), // 48x48
		array(0x034,0x034,0x030,0x030,0x01a,0x01a,0x018,0x018,0x002,0x002,0x004,0x0cc,0x054,0x002,0x066,0x02a), // 52x52
		array(0x040,0x040,0x038,0x038,0x010,0x010,0x00e,0x00e,0x004,0x004,0x010,0x118,0x070,0x002,0x08c,0x038), // 64x64
		array(0x048,0x048,0x040,0x040,0x012,0x012,0x010,0x010,0x004,0x004,0x010,0x170,0x090,0x004,0x05c,0x024), // 72x72
		array(0x050,0x050,0x048,0x048,0x014,0x014,0x012,0x012,0x004,0x004,0x010,0x1c8,0x0c0,0x004,0x072,0x030), // 80x80
		array(0x058,0x058,0x050,0x050,0x016,0x016,0x014,0x014,0x004,0x004,0x010,0x240,0x0e0,0x004,0x090,0x038), // 88x88
		array(0x060,0x060,0x058,0x058,0x018,0x018,0x016,0x016,0x004,0x004,0x010,0x2b8,0x110,0x004,0x0ae,0x044), // 96x96
		array(0x068,0x068,0x060,0x060,0x01a,0x01a,0x018,0x018,0x004,0x004,0x010,0x330,0x150,0x006,0x088,0x038), // 104x104
		array(0x078,0x078,0x06c,0x06c,0x014,0x014,0x012,0x012,0x006,0x006,0x024,0x41a,0x198,0x006,0x0af,0x044), // 120x120
		array(0x084,0x084,0x078,0x078,0x016,0x016,0x014,0x014,0x006,0x006,0x024,0x518,0x1f0,0x008,0x0a3,0x03e), // 132x132
		array(0x090,0x090,0x084,0x084,0x018,0x018,0x016,0x016,0x006,0x006,0x024,0x616,0x26c,0x00a,0x09c,0x03e), // 144x144
		// rectangular form (currently unused) ---------------------------------------------------------------------------
		array(0x008,0x012,0x006,0x010,0x008,0x012,0x006,0x010,0x001,0x001,0x001,0x005,0x007,0x001,0x005,0x007), // 8x18
		array(0x008,0x020,0x006,0x01c,0x008,0x010,0x006,0x00e,0x001,0x002,0x002,0x00a,0x00b,0x001,0x00a,0x00b), // 8x32
		array(0x00c,0x01a,0x00a,0x018,0x00c,0x01a,0x00a,0x018,0x001,0x001,0x001,0x010,0x00e,0x001,0x010,0x00e), // 12x26
		array(0x00c,0x024,0x00a,0x020,0x00c,0x012,0x00a,0x010,0x001,0x002,0x002,0x00c,0x012,0x001,0x00c,0x012), // 12x36
		array(0x010,0x024,0x00e,0x020,0x010,0x012,0x00e,0x010,0x001,0x002,0x002,0x020,0x018,0x001,0x020,0x018), // 16x36
		array(0x010,0x030,0x00e,0x02c,0x010,0x018,0x00e,0x016,0x001,0x002,0x002,0x031,0x01c,0x001,0x031,0x01c)  // 16x48
	);

	/**
	 * Map encodation modes whit character sets.
	 * @private
	 */
	private $chset_id = array(self::SEMACODE_ENCODE_C40 => 'C40', self::SEMACODE_ENCODE_TXT => 'TXT', self::SEMACODE_ENCODE_X12 =>'X12');

	/**
	 * Basic set of characters for each encodation mode.
	 * @private
	 */
	private $chset = array(
		'C40' => array( // Basic set for C40 ----------------------------------------------------------------------------
			'S1'=>0x00,'S2'=>0x01,'S3'=>0x02,0x20=>0x03,0x30=>0x04,0x31=>0x05,0x32=>0x06,0x33=>0x07,0x34=>0x08,0x35=>0x09, //
			0x36=>0x0a,0x37=>0x0b,0x38=>0x0c,0x39=>0x0d,0x41=>0x0e,0x42=>0x0f,0x43=>0x10,0x44=>0x11,0x45=>0x12,0x46=>0x13, //
			0x47=>0x14,0x48=>0x15,0x49=>0x16,0x4a=>0x17,0x4b=>0x18,0x4c=>0x19,0x4d=>0x1a,0x4e=>0x1b,0x4f=>0x1c,0x50=>0x1d, //
			0x51=>0x1e,0x52=>0x1f,0x53=>0x20,0x54=>0x21,0x55=>0x22,0x56=>0x23,0x57=>0x24,0x58=>0x25,0x59=>0x26,0x5a=>0x27),//
		'TXT' => array( // Basic set for TEXT ---------------------------------------------------------------------------
			'S1'=>0x00,'S2'=>0x01,'S3'=>0x02,0x20=>0x03,0x30=>0x04,0x31=>0x05,0x32=>0x06,0x33=>0x07,0x34=>0x08,0x35=>0x09, //
			0x36=>0x0a,0x37=>0x0b,0x38=>0x0c,0x39=>0x0d,0x61=>0x0e,0x62=>0x0f,0x63=>0x10,0x64=>0x11,0x65=>0x12,0x66=>0x13, //
			0x67=>0x14,0x68=>0x15,0x69=>0x16,0x6a=>0x17,0x6b=>0x18,0x6c=>0x19,0x6d=>0x1a,0x6e=>0x1b,0x6f=>0x1c,0x70=>0x1d, //
			0x71=>0x1e,0x72=>0x1f,0x73=>0x20,0x74=>0x21,0x75=>0x22,0x76=>0x23,0x77=>0x24,0x78=>0x25,0x79=>0x26,0x7a=>0x27),//
		'SH1' => array( // Shift 1 set ----------------------------------------------------------------------------------
			0x00=>0x00,0x01=>0x01,0x02=>0x02,0x03=>0x03,0x04=>0x04,0x05=>0x05,0x06=>0x06,0x07=>0x07,0x08=>0x08,0x09=>0x09, //
			0x0a=>0x0a,0x0b=>0x0b,0x0c=>0x0c,0x0d=>0x0d,0x0e=>0x0e,0x0f=>0x0f,0x10=>0x10,0x11=>0x11,0x12=>0x12,0x13=>0x13, //
			0x14=>0x14,0x15=>0x15,0x16=>0x16,0x17=>0x17,0x18=>0x18,0x19=>0x19,0x1a=>0x1a,0x1b=>0x1b,0x1c=>0x1c,0x1d=>0x1d, //
			0x1e=>0x1e,0x1f=>0x1f),                                                                                        //
		'SH2' => array( // Shift 2 set ----------------------------------------------------------------------------------
			0x21=>0x00,0x22=>0x01,0x23=>0x02,0x24=>0x03,0x25=>0x04,0x26=>0x05,0x27=>0x06,0x28=>0x07,0x29=>0x08,0x2a=>0x09, //
			0x2b=>0x0a,0x2c=>0x0b,0x2d=>0x0c,0x2e=>0x0d,0x2f=>0x0e,0x3a=>0x0f,0x3b=>0x10,0x3c=>0x11,0x3d=>0x12,0x3e=>0x13, //
			0x3f=>0x14,0x40=>0x15,0x5b=>0x16,0x5c=>0x17,0x5d=>0x18,0x5e=>0x19,0x5f=>0x1a,'F1'=>0x1b,'US'=>0x1e),           //
		'S3C' => array( // Shift 3 set for C40 --------------------------------------------------------------------------
			0x60=>0x00,0x61=>0x01,0x62=>0x02,0x63=>0x03,0x64=>0x04,0x65=>0x05,0x66=>0x06,0x67=>0x07,0x68=>0x08,0x69=>0x09, //
			0x6a=>0x0a,0x6b=>0x0b,0x6c=>0x0c,0x6d=>0x0d,0x6e=>0x0e,0x6f=>0x0f,0x70=>0x10,0x71=>0x11,0x72=>0x12,0x73=>0x13, //
			0x74=>0x14,0x75=>0x15,0x76=>0x16,0x77=>0x17,0x78=>0x18,0x79=>0x19,0x7a=>0x1a,0x7b=>0x1b,0x7c=>0x1c,0x7d=>0x1d, //
			0x7e=>0x1e,0x7f=>0x1f),
		'S3T' => array( // Shift 3 set for TEXT -------------------------------------------------------------------------
			0x60=>0x00,0x41=>0x01,0x42=>0x02,0x43=>0x03,0x44=>0x04,0x45=>0x05,0x46=>0x06,0x47=>0x07,0x48=>0x08,0x49=>0x09, //
			0x4a=>0x0a,0x4b=>0x0b,0x4c=>0x0c,0x4d=>0x0d,0x4e=>0x0e,0x4f=>0x0f,0x50=>0x10,0x51=>0x11,0x52=>0x12,0x53=>0x13, //
			0x54=>0x14,0x55=>0x15,0x56=>0x16,0x57=>0x17,0x58=>0x18,0x59=>0x19,0x5a=>0x1a,0x7b=>0x1b,0x7c=>0x1c,0x7d=>0x1d, //
			0x7e=>0x1e,0x7f=>0x1f),                                                                                        //
		'X12' => array( // Set for X12 ----------------------------------------------------------------------------------
			0x0d=>0x00,0x2a=>0x01,0x3e=>0x02,0x20=>0x03,0x30=>0x04,0x31=>0x05,0x32=>0x06,0x33=>0x07,0x34=>0x08,0x35=>0x09, //
			0x36=>0x0a,0x37=>0x0b,0x38=>0x0c,0x39=>0x0d,0x41=>0x0e,0x42=>0x0f,0x43=>0x10,0x44=>0x11,0x45=>0x12,0x46=>0x13, //
			0x47=>0x14,0x48=>0x15,0x49=>0x16,0x4a=>0x17,0x4b=>0x18,0x4c=>0x19,0x4d=>0x1a,0x4e=>0x1b,0x4f=>0x1c,0x50=>0x1d, //
			0x51=>0x1e,0x52=>0x1f,0x53=>0x20,0x54=>0x21,0x55=>0x22,0x56=>0x23,0x57=>0x24,0x58=>0x25,0x59=>0x26,0x5a=>0x27) //
		);


	/**
	 * This is the class constructor.
	 * Creates a datamatrix object
	 * @param $code (string) Code to represent using Datamatrix.
	 * @public
	 */
	public function __construct($code) {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$code = (string) $code; // force string
		//--
		$this->barcode_array = array();
		$this->barcode_array['code'] = $code;
		//-- get data codewords
		$cw = $this->getHighLevelEncoding($code);
		// number of data codewords
		$nd = count($cw);
		// check size
		if($nd > 1558) {
			return false;
		} //end if
		//-- get minimum required matrix size.
		foreach($this->symbattr as $u => $params) {
			if($params[11] >= $nd) {
				break;
			} //end if
		} //end foreach
		//--
		if($params[11] < $nd) {
			//-- too much data
			return false;
			//--
		} elseif($params[11] > $nd) {
			//-- add padding
			if((($params[11] - $nd) > 1) AND ($cw[($nd - 1)] != 254)) {
				if($this->last_enc == self::SEMACODE_ENCODE_EDF) {
					//-- switch to ASCII encoding
					$cw[] = 124;
					++$nd;
					//--
				} elseif(($this->last_enc != self::SEMACODE_ENCODE_ASCII) AND ($this->last_enc != self::SEMACODE_ENCODE_B256)) {
					//-- switch to ASCII encoding
					$cw[] = 254;
					++$nd;
					//--
				} //end if else
			} //end if
			//--
			if($params[11] > $nd) {
				//-- add first pad
				$cw[] = 129;
				++$nd;
				//-- add remaining pads
				for($i = $nd; $i < $params[11]; ++$i) {
					$cw[] = $this->get253StateCodeword(129, $i);
				} //end for
				//--
			} //end if
			//--
		} //end if else
		//-- add error correction codewords
		$cw = $this->getErrorCorrection($cw, $params[13], $params[14], $params[15]);
		//-- initialize empty arrays
		$grid = array_fill(0, ($params[2] * $params[3]), 0);
		//-- get placement map
		$places = $this->getPlacementMap($params[2], $params[3]);
		//-- fill the grid with data
		$grid = array();
		$i = 0;
		//-- region data row max index
		$rdri = ($params[4] - 1);
		//-- region data column max index
		$rdci = ($params[5] - 1);
		//-- for each vertical region
		for($vr = 0; $vr < $params[9]; ++$vr) {
			//-- for each row on region
			for($r = 0; $r < $params[4]; ++$r) {
				//-- get row
				$row = (($vr * $params[4]) + $r);
				//-- for each horizontal region
				for($hr = 0; $hr < $params[8]; ++$hr) {
					//-- for each column on region
					for($c = 0; $c < $params[5]; ++$c) {
						//-- get column
						$col = (($hr * $params[5]) + $c);
						//-- braw bits by case
						if($r == 0) {
							//-- top finder pattern
							if($c % 2) {
								$grid[$row][$col] = 0;
							} else {
								$grid[$row][$col] = 1;
							} //end if else
							//--
						} elseif($r == $rdri) {
							//-- bottom finder pattern
							$grid[$row][$col] = 1;
							//--
						} elseif($c == 0) {
							//-- left finder pattern
							$grid[$row][$col] = 1;
							//--
						} elseif($c == $rdci) {
							//-- right finder pattern
							if($r % 2) {
								$grid[$row][$col] = 1;
							} else {
								$grid[$row][$col] = 0;
							} //end if else
							//--
						} else {
							//-- data bit
							if($places[$i] < 2) {
								//--
								$grid[$row][$col] = $places[$i];
								//--
							} else {
								//-- codeword ID
								$cw_id = (floor($places[$i] / 10) - 1);
								//-- codeword BIT mask
								$cw_bit = pow(2, (8 - ($places[$i] % 10)));
								$grid[$row][$col] = (($cw[$cw_id] & $cw_bit) == 0) ? 0 : 1;
								//--
							} //end if else
							//--
							++$i;
							//--
						} //end if else
						//--
					} //end for
					//--
				} //end for
				//--
			} //end for
			//--
		} //end for
		//--
		$this->barcode_array['num_rows'] = $params[0];
		$this->barcode_array['num_cols'] = $params[1];
		$this->barcode_array['bcode'] = $grid;
		//--
	} //END FUNCTION


	/**
	 * Returns a barcode array which is readable by TCPDF
	 * @return array barcode array readable by TCPDF;
	 * @public
	 */
	public function getBarcodeArray() {
		//--
		return (array) $this->barcode_array;
		//--
	} //END FUNCTION


	/**
	 * Product of two numbers in a Power-of-Two Galois Field
	 * @param $a (int) first number to multiply.
	 * @param $b (int) second number to multiply.
	 * @param $log (array) Log table.
	 * @param $alog (array) Anti-Log table.
	 * @param $gf (array) Number of Factors of the Reed-Solomon polynomial.
	 * @return int product
	 * @private
	 */
	private function getGFProduct($a, $b, $log, $alog, $gf) {
		//--
		if(($a == 0) OR ($b == 0)) {
			return 0;
		} //end if
		//--
		return ($alog[($log[$a] + $log[$b]) % ($gf - 1)]);
		//--
	} //END FUNCTION


	/**
	 * Add error correction codewords to data codewords array (ANNEX E).
	 * @param $wd (array) Array of datacodewords.
	 * @param $nb (int) Number of blocks.
	 * @param $nd (int) Number of data codewords per block.
	 * @param $nc (int) Number of correction codewords per block.
	 * @param $gf (int) numner of fields on log/antilog table (power of 2).
	 * @param $pp (int) The value of its prime modulus polynomial (301 for ECC200).
	 * @return array data codewords + error codewords
	 * @private
	 */
	private function getErrorCorrection($wd, $nb, $nd, $nc, $gf=256, $pp=301) {
		//-- generate the log ($log) and antilog ($alog) tables
		$log[0] = 0;
		$alog[0] = 1;
		for($i = 1; $i < $gf; ++$i) {
			$alog[$i] = ($alog[($i - 1)] * 2);
			if($alog[$i] >= $gf) {
				$alog[$i] ^= $pp;
			} //end if
			$log[$alog[$i]] = $i;
		} //end for
		ksort($log);
		//-- generate the polynomial coefficients (c)
		$c = array_fill(0, ($nc + 1), 0);
		$c[0] = 1;
		for($i = 1; $i <= $nc; ++$i) {
			$c[$i] = $c[($i-1)];
			for($j = ($i - 1); $j >= 1; --$j) {
				$c[$j] = $c[($j - 1)] ^ $this->getGFProduct($c[$j], $alog[$i], $log, $alog, $gf);
			} //end for
			$c[0] = $this->getGFProduct($c[0], $alog[$i], $log, $alog, $gf);
		} //end for
		ksort($c);
		//-- total number of data codewords
		$num_wd = ($nb * $nd);
		//-- total number of error codewords
		$num_we = ($nb * $nc);
		//-- for each block
		for($b = 0; $b < $nb; ++$b) {
			//-- create interleaved data block
			$block = array();
			for($n = $b; $n < $num_wd; $n += $nb) {
				$block[] = $wd[$n];
			} //end for
			//-- initialize error codewords
			$we = array_fill(0, ($nc + 1), 0);
			//-- calculate error correction codewords for this block
			for($i = 0; $i < $nd; ++$i) {
				$k = ($we[0] ^ $block[$i]);
				for($j = 0; $j < $nc; ++$j) {
					$we[$j] = ($we[($j + 1)] ^ $this->getGFProduct($k, $c[($nc - $j - 1)], $log, $alog, $gf));
				} //end for
			} //end for
			//-- add error codewords at the end of data codewords
			$j = 0;
			for($i = $b; $i < $num_we; $i += $nb) {
				$wd[($num_wd + $i)] = $we[$j];
				++$j;
			} //end for
			//--
		} //end for
		//-- reorder codewords
		ksort($wd);
		//--
		return $wd;
		//--
	} //END FUNCTION


	/**
	 * Return the 253-state codeword
	 * @param $cwpad (int) Pad codeword.
	 * @param $cwpos (int) Number of data codewords from the beginning of encoded data.
	 * @return pad codeword
	 * @private
	 */
	private function get253StateCodeword($cwpad, $cwpos) {
		//--
		$pad = ($cwpad + (((149 * $cwpos) % 253) + 1));
		//--
		if($pad > 254) {
			$pad -= 254;
		} //end if
		//--
		return $pad;
		//--
	} //END FUNCTION


	/**
	 * Return the 255-state codeword
	 * @param $cwpad (int) Pad codeword.
	 * @param $cwpos (int) Number of data codewords from the beginning of encoded data.
	 * @return pad codeword
	 * @private
	 */
	private function get255StateCodeword($cwpad, $cwpos) {
		//--
		$pad = ($cwpad + (((149 * $cwpos) % 255) + 1));
		//--
		if($pad > 255) {
			$pad -= 256;
		} //end if
		//--
		return $pad;
		//--
	} //END FUNCTION


	/**
	 * Returns true if the char belongs to the selected mode
	 * @param $chr (int) Character (byte) to check.
	 * @param $mode (int) Current encoding mode.
	 * @return boolean true if the char is of the selected mode.
	 * @private
	 */
	private function isCharMode($chr, $mode) {
		//--
		$status = false;
		//--
		switch($mode) {
			case self::SEMACODE_ENCODE_ASCII:  // ASCII character 0 to 127
				$status = (($chr >= 0) AND ($chr <= 127));
				break;
			case self::SEMACODE_ENCODE_C40:  // Upper-case alphanumeric
				$status = (($chr == 32) OR (($chr >= 48) AND ($chr <= 57)) OR (($chr >= 65) AND ($chr <= 90)));
				break;
			case self::SEMACODE_ENCODE_TXT:  // Lower-case alphanumeric
				$status = (($chr == 32) OR (($chr >= 48) AND ($chr <= 57)) OR (($chr >= 97) AND ($chr <= 122)));
				break;
			case self::SEMACODE_ENCODE_X12:  // ANSI X12
				$status = (($chr == 13) OR ($chr == 42) OR ($chr == 62));
				break;
			case self::SEMACODE_ENCODE_EDF:  // ASCII character 32 to 94
				$status = (($chr >= 32) AND ($chr <= 94));
				break;
			case self::SEMACODE_ENCODE_B256:  // Function character (FNC1, Structured Append, Reader Program, or Code Page)
				$status = (($chr == 232) OR ($chr == 233) OR ($chr == 234) OR ($chr == 241));
				break;
			case self::SEMACODE_ENCODE_EXTASCII:  // ASCII character 128 to 255
				$status = (($chr >= 128) AND ($chr <= 255));
				break;
			case self::SEMACODE_ENCODE_NUMASCII:  // ASCII digits
				$status = (($chr >= 48) AND ($chr <= 57));
				break;
		} //end switch
		//--
		return $status;
		//--
	} //END FUNCTION


	/**
	 * The look-ahead test scans the data to be encoded to find the best mode (Annex P - steps from J to S).
	 * @param $data (string) data to encode
	 * @param $pos (int) current position
	 * @param $mode (int) current encoding mode
	 * @return int encoding mode
	 * @private
	 */
	private function lookAheadTest($data, $pos, $mode) {
		//--
		$data_length = strlen($data);
		if($pos >= $data_length) {
			return $mode;
		} //end if
		$charscount = 0; // count processed chars
		//-- STEP J
		if($mode == self::SEMACODE_ENCODE_ASCII) {
			$numch = array(0, 1, 1, 1, 1, 1.25);
		} else {
			$numch = array(1, 2, 2, 2, 2, 2.25);
			$numch[$mode] = 0;
		} //end if else
		//--
		while(true) {
			//-- STEP K
			if(($pos + $charscount) == $data_length) {
				if($numch[self::SEMACODE_ENCODE_ASCII] <= ceil(min($numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256]))) {
					return self::SEMACODE_ENCODE_ASCII;
				} //end if
				if($numch[self::SEMACODE_ENCODE_B256] < ceil(min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF]))) {
					return self::SEMACODE_ENCODE_B256;
				} //end if
				if($numch[self::SEMACODE_ENCODE_EDF] < ceil(min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_B256]))) {
					return self::SEMACODE_ENCODE_EDF;
				} //end if
				if($numch[self::SEMACODE_ENCODE_TXT] < ceil(min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256]))) {
					return self::SEMACODE_ENCODE_TXT;
				} //end if
				if($numch[self::SEMACODE_ENCODE_X12] < ceil(min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256]))) {
					return self::SEMACODE_ENCODE_X12;
				} //end if
				return self::SEMACODE_ENCODE_C40;
			} //end while
			//-- get char
			$chr = ord($data[$pos + $charscount]);
			//--
			$charscount++;
			//-- STEP L
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_NUMASCII)) {
				$numch[self::SEMACODE_ENCODE_ASCII] += (1 / 2);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_ASCII] = ceil($numch[self::SEMACODE_ENCODE_ASCII]);
				$numch[self::SEMACODE_ENCODE_ASCII] += 2;
			} else {
				$numch[self::SEMACODE_ENCODE_ASCII] = ceil($numch[self::SEMACODE_ENCODE_ASCII]);
				$numch[self::SEMACODE_ENCODE_ASCII] += 1;
			} //end if else
			//-- STEP M
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_C40)) {
				$numch[self::SEMACODE_ENCODE_C40] += (2 / 3);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_C40] += (8 / 3);
			} else {
				$numch[self::SEMACODE_ENCODE_C40] += (4 / 3);
			} //end if else
			//-- STEP N
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_TXT)) {
				$numch[self::SEMACODE_ENCODE_TXT] += (2 / 3);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_TXT] += (8 / 3);
			} else {
				$numch[self::SEMACODE_ENCODE_TXT] += (4 / 3);
			} //end if else
			//-- STEP O
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_X12) OR $this->isCharMode($chr, self::SEMACODE_ENCODE_C40)) {
				$numch[self::SEMACODE_ENCODE_X12] += (2 / 3);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_X12] += (13 / 3);
			} else {
				$numch[self::SEMACODE_ENCODE_X12] += (10 / 3);
			} //end if else
			//-- STEP P
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_EDF)) {
				$numch[self::SEMACODE_ENCODE_EDF] += (3 / 4);
			} elseif($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
				$numch[self::SEMACODE_ENCODE_EDF] += (17 / 4);
			} else {
				$numch[self::SEMACODE_ENCODE_EDF] += (13 / 4);
			} //end if else
			//-- STEP Q
			if($this->isCharMode($chr, self::SEMACODE_ENCODE_B256)) {
				$numch[self::SEMACODE_ENCODE_B256] += 4;
			} else {
				$numch[self::SEMACODE_ENCODE_B256] += 1;
			} //end if else
			//-- STEP R
			if($charscount >= 4) {
				if(($numch[self::SEMACODE_ENCODE_ASCII] + 1) <= min($numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					return self::SEMACODE_ENCODE_ASCII;
				} //end if
				if((($numch[self::SEMACODE_ENCODE_B256] + 1) <= $numch[self::SEMACODE_ENCODE_ASCII]) OR (($numch[self::SEMACODE_ENCODE_B256] + 1) < min($numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF]))) {
					return self::SEMACODE_ENCODE_B256;
				} //end if
				if(($numch[self::SEMACODE_ENCODE_EDF] + 1) < min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_B256])) {
					return self::SEMACODE_ENCODE_EDF;
				} //end if
				if(($numch[self::SEMACODE_ENCODE_TXT] + 1) < min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_X12], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					return self::SEMACODE_ENCODE_TXT;
				} //end if
				if(($numch[self::SEMACODE_ENCODE_X12] + 1) < min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_C40], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					return self::SEMACODE_ENCODE_X12;
				} //end if
				if(($numch[self::SEMACODE_ENCODE_C40] + 1) < min($numch[self::SEMACODE_ENCODE_ASCII], $numch[self::SEMACODE_ENCODE_TXT], $numch[self::SEMACODE_ENCODE_EDF], $numch[self::SEMACODE_ENCODE_B256])) {
					if($numch[self::SEMACODE_ENCODE_C40] < $numch[self::SEMACODE_ENCODE_X12]) {
						return self::SEMACODE_ENCODE_C40;
					} //end if
					if($numch[self::SEMACODE_ENCODE_C40] == $numch[self::SEMACODE_ENCODE_X12]) {
						$k = ($pos + $charscount + 1);
						while ($k < $data_length) {
							$tmpchr = ord($data{$k});
							if($this->isCharMode($tmpchr, self::SEMACODE_ENCODE_X12)) {
								return self::SEMACODE_ENCODE_X12;
							} elseif(!($this->isCharMode($tmpchr, self::SEMACODE_ENCODE_X12) OR $this->isCharMode($tmpchr, self::SEMACODE_ENCODE_C40))) {
								break;
							} //end if else
							++$k;
						} //end while
						return self::SEMACODE_ENCODE_C40;
					} //end if
				} //end if
			} //end if
			//--
		} // end of while
		//--
	} //END FUNCTION


	/**
	 * Get the switching codeword to a new encoding mode (latch codeword)
	 * @param $mode (int) New encoding mode.
	 * @return (int) Switch codeword.
	 * @private
	 */
	private function getSwitchEncodingCodeword($mode) {
		//--
		switch($mode) {
			case self::SEMACODE_ENCODE_ASCII:  // ASCII character 0 to 127
				$cw = 254;
				if($this->last_enc == self::SEMACODE_ENCODE_EDF) {
					$cw = 124;
				} //end if
				break;
			case self::SEMACODE_ENCODE_C40:  // Upper-case alphanumeric
				$cw = 230;
				break;
			case self::SEMACODE_ENCODE_TXT:  // Lower-case alphanumeric
				$cw = 239;
				break;
			case self::SEMACODE_ENCODE_X12:  // ANSI X12
				$cw = 238;
				break;
			case self::SEMACODE_ENCODE_EDF:  // ASCII character 32 to 94
				$cw = 240;
				break;
			case self::SEMACODE_ENCODE_B256:  // Function character (FNC1, Structured Append, Reader Program, or Code Page)
				$cw = 231;
				break;
		} //end switch
		//--
		return $cw;
		//--
	} //END FUNCTION


	/**
	 * Choose the minimum matrix size and return the max number of data codewords.
	 * @param $numcw (int) Number of current codewords.
	 * @return number of data codewords in matrix
	 * @private
	 */
	private function getMaxDataCodewords($numcw) {
		//--
		foreach($this->symbattr as $key => $matrix) {
			if($matrix[11] >= $numcw) {
				return $matrix[11];
			} //end if
		} //end foreach
		//--
		return 0;
		//--
	} //END FUNCTION


	/**
	 * Get high level encoding using the minimum symbol data characters for ECC 200
	 * @param $data (string) data to encode
	 * @return array of codewords
	 * @private
	 */
	private function getHighLevelEncoding($data) {
		//-- STEP A. Start in ASCII encodation.
		$enc = self::SEMACODE_ENCODE_ASCII; // current encoding mode
		$pos = 0; // current position
		$cw = array(); // array of codewords to be returned
		$cw_num = 0; // number of data codewords
		$data_length = strlen($data); // number of chars
		//--
		while($pos < $data_length) {
			//-- set last used encoding
			$this->last_enc = $enc;
			switch($enc) {
				case self::SEMACODE_ENCODE_ASCII:  // STEP B. While in ASCII encodation
					//--
					if(($data_length > 1) AND ($pos < ($data_length - 1)) AND ($this->isCharMode(ord($data[$pos]), self::SEMACODE_ENCODE_NUMASCII) AND $this->isCharMode(ord($data[$pos + 1]), self::SEMACODE_ENCODE_NUMASCII))) {
						// 1. If the next data sequence is at least 2 consecutive digits, encode the next two digits as a double digit in ASCII mode.
						$cw[] = (intval(substr($data, $pos, 2)) + 130);
						++$cw_num;
						$pos += 2;
					} else {
						// 2. If the look-ahead test (starting at step J) indicates another mode, switch to that mode.
						$newenc = $this->lookAheadTest($data, $pos, $enc);
						if($newenc != $enc) {
							// switch to new encoding
							$enc = $newenc;
							$cw[] = $this->getSwitchEncodingCodeword($enc);
							++$cw_num;
						} else {
							// get new byte
							$chr = ord($data[$pos]);
							++$pos;
							if($this->isCharMode($chr, self::SEMACODE_ENCODE_EXTASCII)) {
								// 3. If the next data character is extended ASCII (greater than 127) encode it in ASCII mode first using the Upper Shift (value 235) character.
								$cw[] = 235;
								$cw[] = ($chr - 127);
								$cw_num += 2;
							} else {
								// 4. Otherwise process the next data character in ASCII encodation.
								$cw[] = ($chr + 1);
								++$cw_num;
							} //end if else
						} //end if else
					} //end if else
					//--
					break;
				case self::SEMACODE_ENCODE_C40 :   // Upper-case alphanumeric
				case self::SEMACODE_ENCODE_TXT :   // Lower-case alphanumeric
				case self::SEMACODE_ENCODE_X12 :   // ANSI X12
					//--
					$temp_cw = array();
					$p = 0;
					$epos = $pos;
					// get charset ID
					$set_id = $this->chset_id[$enc];
					// get basic charset for current encoding
					$charset = $this->chset[$set_id];
					do {
						//-- 2. process the next character in C40 encodation.
						$chr = ord($data[$epos]);
						++$epos;
						//-- check for extended character
						if($chr & 0x80) {
							if($enc == self::SEMACODE_ENCODE_X12) {
								return false;
							} //end if
							$chr = ($chr & 0x7f);
							$temp_cw[] = 1; // shift 2
							$temp_cw[] = 30; // upper shift
							$p += 2;
						} //end if
						if(isset($charset[$chr])) {
							$temp_cw[] = $charset[$chr];
							++$p;
						} else {
							if(isset($this->chset['SH1'][$chr])) {
								$temp_cw[] = 0; // shift 1
								$shiftset = $this->chset['SH1'];
							} elseif(isset($chr, $this->chset['SH2'][$chr])) {
								$temp_cw[] = 1; // shift 2
								$shiftset = $this->chset['SH2'];
							} elseif(($enc == self::SEMACODE_ENCODE_C40) AND isset($this->chset['S3C'][$chr])) {
								$temp_cw[] = 2; // shift 3
								$shiftset = $this->chset['S3C'];
							} elseif(($enc == self::SEMACODE_ENCODE_TXT) AND isset($this->chset['S3T'][$chr])) {
								$temp_cw[] = 2; // shift 3
								$shiftset = $this->chset['S3T'];
							} else {
								return false;
							} //end if else
							$temp_cw[] = $shiftset[$chr];
							$p += 2;
						} //end if else
						if($p >= 3) {
							$c1 = array_shift($temp_cw);
							$c2 = array_shift($temp_cw);
							$c3 = array_shift($temp_cw);
							$p -= 3;
							$tmp = ((1600 * $c1) + (40 * $c2) + $c3 + 1);
							$cw[] = ($tmp >> 8);
							$cw[] = ($tmp % 256);
							$cw_num += 2;
							$pos = $epos;
							// 1. If the C40 encoding is at the point of starting a new double symbol character and if the look-ahead test (starting at step J) indicates another mode, switch to that mode.
							$newenc = $this->lookAheadTest($data, $pos, $enc);
							if($newenc != $enc) {
								// switch to new encoding
								$enc = $newenc;
								if ($enc != self::SEMACODE_ENCODE_ASCII) {
									// set unlatch character
									$cw[] = $this->getSwitchEncodingCodeword(self::SEMACODE_ENCODE_ASCII);
									++$cw_num;
								}
								$cw[] = $this->getSwitchEncodingCodeword($enc);
								++$cw_num;
								$pos -= $p;
								$p = 0;
								break;
							} //end if
						} //end if
					} while(($p > 0) AND ($epos < $data_length));
					//-- process last data (if any)
					if($p > 0) {
						// get remaining number of data symbols
						$cwr = ($this->getMaxDataCodewords($cw_num) - $cw_num);
						if(($cwr == 1) AND ($p == 1)) {
							// d. If one symbol character remains and one C40 value (data character) remains to be encoded
							$c1 = array_shift($temp_cw);
							--$p;
							$cw[] = ($chr + 1);
							++$cw_num;
							$pos = $epos;
							$enc = self::SEMACODE_ENCODE_ASCII;
							$this->last_enc = $enc;
						} elseif(($cwr == 2) AND ($p == 1)) {
							// c. If two symbol characters remain and only one C40 value (data character) remains to be encoded
							$c1 = array_shift($temp_cw);
							--$p;
							$cw[] = 254;
							$cw[] = ($chr + 1);
							$cw_num += 2;
							$pos = $epos;
							$enc = self::SEMACODE_ENCODE_ASCII;
							$this->last_enc = $enc;
						} elseif(($cwr == 2) AND ($p == 2)) {
							// b. If two symbol characters remain and two C40 values remain to be encoded
							$c1 = array_shift($temp_cw);
							$c2 = array_shift($temp_cw);
							$p -= 2;
							$tmp = ((1600 * $c1) + (40 * $c2) + 1);
							$cw[] = ($tmp >> 8);
							$cw[] = ($tmp % 256);
							$cw_num += 2;
							$pos = $epos;
							$enc = self::SEMACODE_ENCODE_ASCII;
							$this->last_enc = $enc;
						} else {
							// switch to ASCII encoding
							if($enc != self::SEMACODE_ENCODE_ASCII) {
								$enc = self::SEMACODE_ENCODE_ASCII;
								$this->last_enc = $enc;
								$cw[] = $this->getSwitchEncodingCodeword($enc);
								++$cw_num;
								$pos = ($epos - $p);
							} //end if
						} //end if else
					} //end if
					//--
					break;
				case self::SEMACODE_ENCODE_EDF:  // F. While in EDIFACT (EDF) encodation
					//-- initialize temporary array with 0 length
					$temp_cw = array();
					$epos = $pos;
					$field_length = 0;
					$newenc = $enc;
					do {
						// 2. process the next character in EDIFACT encodation.
						$chr = ord($data[$epos]);
						if($this->isCharMode($chr, self::SEMACODE_ENCODE_EDF)) {
							++$epos;
							$temp_cw[] = $chr;
							++$field_length;
						} //end if
						if(($field_length == 4) OR ($epos == $data_length) OR !$this->isCharMode($chr, self::SEMACODE_ENCODE_EDF)) {
							if(($epos == $data_length) AND ($field_length < 3)) {
								$enc = self::SEMACODE_ENCODE_ASCII;
								$cw[] = $this->getSwitchEncodingCodeword($enc);
								++$cw_num;
								break;
							} //end if
							if($field_length < 4) {
								// set unlatch character
								$temp_cw[] = 0x1f;
								++$field_length;
								// fill empty characters
								for($i = $field_length; $i < 4; ++$i) {
									$temp_cw[] = 0;
								} //end for
								$enc = self::SEMACODE_ENCODE_ASCII;
								$this->last_enc = $enc;
							} //end if
							//-- encodes four data characters in three codewords
							$tcw = (($temp_cw[0] & 0x3F) << 2) + (($temp_cw[1] & 0x30) >> 4);
							if($tcw > 0) {
								$cw[] = $tcw;
								$cw_num++;
							} //end if
							$tcw= (($temp_cw[1] & 0x0F) << 4) + (($temp_cw[2] & 0x3C) >> 2);
							if($tcw > 0) {
								$cw[] = $tcw;
								$cw_num++;
							} //end if
							$tcw = (($temp_cw[2] & 0x03) << 6) + ($temp_cw[3] & 0x3F);
							if($tcw > 0) {
								$cw[] = $tcw;
								$cw_num++;
							} //end if
							$temp_cw = array();
							$pos = $epos;
							$field_length = 0;
							if($enc == self::SEMACODE_ENCODE_ASCII) {
								break; // exit from EDIFACT mode
							} //end if
						} //end if
					} while($epos < $data_length);
					//--
					break;
				case self::SEMACODE_ENCODE_B256: // G. While in Base 256 (B256) encodation
					//-- initialize temporary array with 0 length
					$temp_cw = array();
					$field_length = 0;
					while(($pos < $data_length) AND ($field_length <= 1555)) {
						$newenc = $this->lookAheadTest($data, $pos, $enc);
						if($newenc != $enc) {
							// 1. If the look-ahead test (starting at step J) indicates another mode, switch to that mode.
							$enc = $newenc;
							break; // exit from B256 mode
						} else {
							// 2. Otherwise, process the next character in Base 256 encodation.
							$chr = ord($data[$pos]);
							++$pos;
							$temp_cw[] = $chr;
							++$field_length;
						} //end if else
					} //end while
					//-- set field length
					if($field_length <= 249) {
						$cw[] = $this->get255StateCodeword($field_length, ($cw_num + 1));
						++$cw_num;
					} else {
						$cw[] = $this->get255StateCodeword((floor($field_length / 250) + 249), ($cw_num + 1));
						$cw[] = $this->get255StateCodeword(($field_length % 250), ($cw_num + 2));
						$cw_num += 2;
					} //end if else
					//--
					if(!empty($temp_cw)) {
						// add B256 field
						foreach($temp_cw as $p => $cht) {
							$cw[] = $this->get255StateCodeword($cht, ($cw_num + $p + 1));
						} //end foreach
					} //end if
					//--
					break;
			} // end switch
			//--
		} // end of while
		//--
		return $cw;
		//--
	} //END FUNCTION


	/**
	 * Places "chr+bit" with appropriate wrapping within array[].
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $row (int) Row number.
	 * @param $col (int) Column number.
	 * @param $chr (int) Char byte.
	 * @param $bit (int) Bit.
	 * @return array
	 * @private
	 */
	private function placeModule($marr, $nrow, $ncol, $row, $col, $chr, $bit) {
		//--
		if($row < 0) {
			$row += $nrow;
			$col += (4 - (($nrow + 4) % 8));
		} //end if
		//--
		if($col < 0) {
			$col += $ncol;
			$row += (4 - (($ncol + 4) % 8));
		} //end if
		//--
		$marr[(($row * $ncol) + $col)] = ((10 * $chr) + $bit);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of a utah-shaped symbol character.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $row (int) Row number.
	 * @param $col (int) Column number.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeUtah($marr, $nrow, $ncol, $row, $col, $chr) {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-2, $col-2, $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-2, $col-1, $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col-2, $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col-1, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row-1, $col,   $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row,   $col-2, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row,   $col-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, $row,   $col,   $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the first special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerA($marr, $nrow, $ncol, $chr) {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 1,       $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 2,       $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 2,       $ncol-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 3,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the second special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerB($marr, $nrow, $ncol, $chr) {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-3, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-2, 0,       $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-4, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-3, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the third special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerC($marr, $nrow, $ncol, $chr) {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-3, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-2, 0,       $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 2,       $ncol-1, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 3,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Places the 8 bits of the fourth special corner case.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $marr (array) Array of symbols.
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @param $chr (int) Char byte.
	 * @return array
	 * @private
	 */
	private function placeCornerD($marr, $nrow, $ncol, $chr) {
		//--
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, 0,       $chr, 1);
		$marr = $this->placeModule($marr, $nrow, $ncol, $nrow-1, $ncol-1, $chr, 2);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-3, $chr, 3);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-2, $chr, 4);
		$marr = $this->placeModule($marr, $nrow, $ncol, 0,       $ncol-1, $chr, 5);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-3, $chr, 6);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-2, $chr, 7);
		$marr = $this->placeModule($marr, $nrow, $ncol, 1,       $ncol-1, $chr, 8);
		//--
		return $marr;
		//--
	} //END FUNCTION


	/**
	 * Build a placement map.
	 * (Annex F - ECC 200 symbol character placement)
	 * @param $nrow (int) Number of rows.
	 * @param $ncol (int) Number of columns.
	 * @return array
	 * @private
	 */
	private function getPlacementMap($nrow, $ncol) {
		//-- initialize array with zeros
		$marr = array_fill(0, ($nrow * $ncol), 0);
		//-- set starting values
		$chr = 1;
		$row = 4;
		$col = 0;
		//--
		do {
			//-- repeatedly first check for one of the special corner cases, then
			if(($row == $nrow) AND ($col == 0)) {
				$marr = $this->placeCornerA($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			if(($row == ($nrow - 2)) AND ($col == 0) AND ($ncol % 4)) {
				$marr = $this->placeCornerB($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			if(($row == ($nrow - 2)) AND ($col == 0) AND (($ncol % 8) == 4)) {
				$marr = $this->placeCornerC($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			if(($row == ($nrow + 4)) AND ($col == 2) AND (!($ncol % 8))) {
				$marr = $this->placeCornerD($marr, $nrow, $ncol, $chr);
				++$chr;
			} //end if
			//-- sweep upward diagonally, inserting successive characters,
			do {
				if(($row < $nrow) AND ($col >= 0) AND (!$marr[(($row * $ncol) + $col)])) {
					$marr = $this->placeUtah($marr, $nrow, $ncol, $row, $col, $chr);
					++$chr;
				} //end if
				$row -= 2;
				$col += 2;
			} while (($row >= 0) AND ($col < $ncol));
			++$row;
			$col += 3;
			//-- & then sweep downward diagonally, inserting successive characters,...
			do {
				if(($row >= 0) AND ($col < $ncol) AND (!$marr[(($row * $ncol) + $col)])) {
					$marr = $this->placeUtah($marr, $nrow, $ncol, $row, $col, $chr);
					++$chr;
				} //end if
				$row += 2;
				$col -= 2;
			} while (($row < $nrow) AND ($col >= 0));
			$row += 3;
			++$col;
			//-- ... until the entire array is scanned
		} while (($row < $nrow) OR ($col < $ncol));
		//-- lastly, if the lower righthand corner is untouched, fill in fixed pattern
		if(!$marr[(($nrow * $ncol) - 1)]) {
			$marr[(($nrow * $ncol) - 1)] = 1;
			$marr[(($nrow * $ncol) - $ncol - 2)] = 1;
		} //end if
		//--
		return $marr;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


//============================================================
// BarCode 2D: PDF-417
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create PDF417 barcode arrays.
// PDF417 (ISO/IEC 15438:2006) is a 2-dimensional stacked barcode
// created by Symbol Technologies in 1991.
// It is one of the most popular 2D codes because of its ability
// to be read with slightly modified handheld laser or linear CCD
// scanners.
// TECHNICAL DATA / FEATURES OF PDF417:
// * Encodable Character Set: 		All 128 ASCII Characters (including extended)
// * Code Type: 					Continuous, Multi-Row
// * Symbol Height: 				3 - 90 Rows
// * Symbol Width: 					90X - 583X
// * Bidirectional Decoding: 		Yes
// * Error Correction Characters: 	2 - 512
// * Maximum Data Characters: 		2710 numeric, 1850 alphanumeric, 1108 binary / bytes (combined text, numbers and symbols)
// *** In practice, a PDF417 symbol takes about 4 x the area of a DataMatrix or QR Code ***
//============================================================
//
// This class is derived from the following projects:
//
// "TcPDF" / Barcodes 2D / 1.0.005 / 20140425
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class Smart BarCode 2D PDF417
 *
 * @access 		private
 * @internal
 *
 */
final class SmartBarcode2D_Pdf417 {

	// ->
	// v.160827


	const BCODE2D_PDF_417_ROW_HEIGHT = 4; // Row height respect X dimension of single module
	const BCODE2D_PDF_417_QUIET_HORIZ = 2; // Horizontal quiet zone in modules
	const BCODE2D_PDF_417_QUIET_VERT = 2; // Vertical quiet zone in modules


	/**
	 * Barcode array to be returned which is readable by TCPDF.
	 * @private
	 */
	private $barcode_array = array();

	/**
	 * Start pattern.
	 * @private
	 */
	private $start_pattern = '11111111010101000';

	/**
	 * Stop pattern.
	 * @private
	 */
	private $stop_pattern = '111111101000101001';

	/**
	 * Array of text Compaction Sub-Modes (values 0xFB - 0xFF are used for submode changers).
	 * @private
	 */
	private $textsubmodes = array(
		array(0x41,0x42,0x43,0x44,0x45,0x46,0x47,0x48,0x49,0x4a,0x4b,0x4c,0x4d,0x4e,0x4f,0x50,0x51,0x52,0x53,0x54,0x55,0x56,0x57,0x58,0x59,0x5a,0x20,0xFD,0xFE,0xFF), // Alpha
		array(0x61,0x62,0x63,0x64,0x65,0x66,0x67,0x68,0x69,0x6a,0x6b,0x6c,0x6d,0x6e,0x6f,0x70,0x71,0x72,0x73,0x74,0x75,0x76,0x77,0x78,0x79,0x7a,0x20,0xFD,0xFE,0xFF), // Lower
		array(0x30,0x31,0x32,0x33,0x34,0x35,0x36,0x37,0x38,0x39,0x26,0x0d,0x09,0x2c,0x3a,0x23,0x2d,0x2e,0x24,0x2f,0x2b,0x25,0x2a,0x3d,0x5e,0xFB,0x20,0xFD,0xFE,0xFF), // Mixed
		array(0x3b,0x3c,0x3e,0x40,0x5b,0x5c,0x5d,0x5f,0x60,0x7e,0x21,0x0d,0x09,0x2c,0x3a,0x0a,0x2d,0x2e,0x24,0x2f,0x22,0x7c,0x2a,0x28,0x29,0x3f,0x7b,0x7d,0x27,0xFF) // Puntuaction
	);

	/**
	 * Array of switching codes for Text Compaction Sub-Modes.
	 * @private
	 */
	private $textlatch = array(
		'01' => array(27), '02' => array(28), '03' => array(28,25), //
		'10' => array(28,28), '12' => array(28), '13' => array(28,25), //
		'20' => array(28), '21' => array(27), '23' => array(25), //
		'30' => array(29), '31' => array(29,27), '32' => array(29,28) //
	);

	/**
	 * Clusters of codewords (0, 3, 6)<br/>
	 * Values are hex equivalents of binary representation of bars (1 = bar, 0 = space).<br/>
	 * The codewords numbered from 900 to 928 have special meaning, some enable to switch between modes in order to optimise the code:<ul>
	 * <li>900 : Switch to "Text" mode</li>
	 * <li>901 : Switch to "Byte" mode</li>
	 * <li>902 : Switch to "Numeric" mode</li>
	 * <li>903 - 912 : Reserved</li>
	 * <li>913 : Switch to "Octet" only for the next codeword</li>
	 * <li>914 - 920 : Reserved</li>
	 * <li>921 : Initialization</li>
	 * <li>922 : Terminator codeword for Macro PDF control block</li>
	 * <li>923 : Sequence tag to identify the beginning of optional fields in the Macro PDF control block</li>
	 * <li>924 : Switch to "Byte" mode (If the total number of byte is multiple of 6)</li>
	 * <li>925 : Identifier for a user defined Extended Channel Interpretation (ECI)</li>
	 * <li>926 : Identifier for a general purpose ECI format</li>
	 * <li>927 : Identifier for an ECI of a character set or code page</li>
	 * <li>928 : Macro marker codeword to indicate the beginning of a Macro PDF Control Block</li>
	 * </ul>
	 * @private
	 */
	private $clusters = array(
		array( // cluster 0 -----------------------------------------------------------------------
			0x1d5c0,0x1eaf0,0x1f57c,0x1d4e0,0x1ea78,0x1f53e,0x1a8c0,0x1d470,0x1a860,0x15040, //  10
			0x1a830,0x15020,0x1adc0,0x1d6f0,0x1eb7c,0x1ace0,0x1d678,0x1eb3e,0x158c0,0x1ac70, //  20
			0x15860,0x15dc0,0x1aef0,0x1d77c,0x15ce0,0x1ae78,0x1d73e,0x15c70,0x1ae3c,0x15ef0, //  30
			0x1af7c,0x15e78,0x1af3e,0x15f7c,0x1f5fa,0x1d2e0,0x1e978,0x1f4be,0x1a4c0,0x1d270, //  40
			0x1e93c,0x1a460,0x1d238,0x14840,0x1a430,0x1d21c,0x14820,0x1a418,0x14810,0x1a6e0, //  50
			0x1d378,0x1e9be,0x14cc0,0x1a670,0x1d33c,0x14c60,0x1a638,0x1d31e,0x14c30,0x1a61c, //  60
			0x14ee0,0x1a778,0x1d3be,0x14e70,0x1a73c,0x14e38,0x1a71e,0x14f78,0x1a7be,0x14f3c, //  70
			0x14f1e,0x1a2c0,0x1d170,0x1e8bc,0x1a260,0x1d138,0x1e89e,0x14440,0x1a230,0x1d11c, //  80
			0x14420,0x1a218,0x14410,0x14408,0x146c0,0x1a370,0x1d1bc,0x14660,0x1a338,0x1d19e, //  90
			0x14630,0x1a31c,0x14618,0x1460c,0x14770,0x1a3bc,0x14738,0x1a39e,0x1471c,0x147bc, // 100
			0x1a160,0x1d0b8,0x1e85e,0x14240,0x1a130,0x1d09c,0x14220,0x1a118,0x1d08e,0x14210, // 110
			0x1a10c,0x14208,0x1a106,0x14360,0x1a1b8,0x1d0de,0x14330,0x1a19c,0x14318,0x1a18e, // 120
			0x1430c,0x14306,0x1a1de,0x1438e,0x14140,0x1a0b0,0x1d05c,0x14120,0x1a098,0x1d04e, // 130
			0x14110,0x1a08c,0x14108,0x1a086,0x14104,0x141b0,0x14198,0x1418c,0x140a0,0x1d02e, // 140
			0x1a04c,0x1a046,0x14082,0x1cae0,0x1e578,0x1f2be,0x194c0,0x1ca70,0x1e53c,0x19460, // 150
			0x1ca38,0x1e51e,0x12840,0x19430,0x12820,0x196e0,0x1cb78,0x1e5be,0x12cc0,0x19670, // 160
			0x1cb3c,0x12c60,0x19638,0x12c30,0x12c18,0x12ee0,0x19778,0x1cbbe,0x12e70,0x1973c, // 170
			0x12e38,0x12e1c,0x12f78,0x197be,0x12f3c,0x12fbe,0x1dac0,0x1ed70,0x1f6bc,0x1da60, // 180
			0x1ed38,0x1f69e,0x1b440,0x1da30,0x1ed1c,0x1b420,0x1da18,0x1ed0e,0x1b410,0x1da0c, // 190
			0x192c0,0x1c970,0x1e4bc,0x1b6c0,0x19260,0x1c938,0x1e49e,0x1b660,0x1db38,0x1ed9e, // 200
			0x16c40,0x12420,0x19218,0x1c90e,0x16c20,0x1b618,0x16c10,0x126c0,0x19370,0x1c9bc, // 210
			0x16ec0,0x12660,0x19338,0x1c99e,0x16e60,0x1b738,0x1db9e,0x16e30,0x12618,0x16e18, // 220
			0x12770,0x193bc,0x16f70,0x12738,0x1939e,0x16f38,0x1b79e,0x16f1c,0x127bc,0x16fbc, // 230
			0x1279e,0x16f9e,0x1d960,0x1ecb8,0x1f65e,0x1b240,0x1d930,0x1ec9c,0x1b220,0x1d918, // 240
			0x1ec8e,0x1b210,0x1d90c,0x1b208,0x1b204,0x19160,0x1c8b8,0x1e45e,0x1b360,0x19130, // 250
			0x1c89c,0x16640,0x12220,0x1d99c,0x1c88e,0x16620,0x12210,0x1910c,0x16610,0x1b30c, // 260
			0x19106,0x12204,0x12360,0x191b8,0x1c8de,0x16760,0x12330,0x1919c,0x16730,0x1b39c, // 270
			0x1918e,0x16718,0x1230c,0x12306,0x123b8,0x191de,0x167b8,0x1239c,0x1679c,0x1238e, // 280
			0x1678e,0x167de,0x1b140,0x1d8b0,0x1ec5c,0x1b120,0x1d898,0x1ec4e,0x1b110,0x1d88c, // 290
			0x1b108,0x1d886,0x1b104,0x1b102,0x12140,0x190b0,0x1c85c,0x16340,0x12120,0x19098, // 300
			0x1c84e,0x16320,0x1b198,0x1d8ce,0x16310,0x12108,0x19086,0x16308,0x1b186,0x16304, // 310
			0x121b0,0x190dc,0x163b0,0x12198,0x190ce,0x16398,0x1b1ce,0x1638c,0x12186,0x16386, // 320
			0x163dc,0x163ce,0x1b0a0,0x1d858,0x1ec2e,0x1b090,0x1d84c,0x1b088,0x1d846,0x1b084, // 330
			0x1b082,0x120a0,0x19058,0x1c82e,0x161a0,0x12090,0x1904c,0x16190,0x1b0cc,0x19046, // 340
			0x16188,0x12084,0x16184,0x12082,0x120d8,0x161d8,0x161cc,0x161c6,0x1d82c,0x1d826, // 350
			0x1b042,0x1902c,0x12048,0x160c8,0x160c4,0x160c2,0x18ac0,0x1c570,0x1e2bc,0x18a60, // 360
			0x1c538,0x11440,0x18a30,0x1c51c,0x11420,0x18a18,0x11410,0x11408,0x116c0,0x18b70, // 370
			0x1c5bc,0x11660,0x18b38,0x1c59e,0x11630,0x18b1c,0x11618,0x1160c,0x11770,0x18bbc, // 380
			0x11738,0x18b9e,0x1171c,0x117bc,0x1179e,0x1cd60,0x1e6b8,0x1f35e,0x19a40,0x1cd30, // 390
			0x1e69c,0x19a20,0x1cd18,0x1e68e,0x19a10,0x1cd0c,0x19a08,0x1cd06,0x18960,0x1c4b8, // 400
			0x1e25e,0x19b60,0x18930,0x1c49c,0x13640,0x11220,0x1cd9c,0x1c48e,0x13620,0x19b18, // 410
			0x1890c,0x13610,0x11208,0x13608,0x11360,0x189b8,0x1c4de,0x13760,0x11330,0x1cdde, // 420
			0x13730,0x19b9c,0x1898e,0x13718,0x1130c,0x1370c,0x113b8,0x189de,0x137b8,0x1139c, // 430
			0x1379c,0x1138e,0x113de,0x137de,0x1dd40,0x1eeb0,0x1f75c,0x1dd20,0x1ee98,0x1f74e, // 440
			0x1dd10,0x1ee8c,0x1dd08,0x1ee86,0x1dd04,0x19940,0x1ccb0,0x1e65c,0x1bb40,0x19920, // 450
			0x1eedc,0x1e64e,0x1bb20,0x1dd98,0x1eece,0x1bb10,0x19908,0x1cc86,0x1bb08,0x1dd86, // 460
			0x19902,0x11140,0x188b0,0x1c45c,0x13340,0x11120,0x18898,0x1c44e,0x17740,0x13320, // 470
			0x19998,0x1ccce,0x17720,0x1bb98,0x1ddce,0x18886,0x17710,0x13308,0x19986,0x17708, // 480
			0x11102,0x111b0,0x188dc,0x133b0,0x11198,0x188ce,0x177b0,0x13398,0x199ce,0x17798, // 490
			0x1bbce,0x11186,0x13386,0x111dc,0x133dc,0x111ce,0x177dc,0x133ce,0x1dca0,0x1ee58, // 500
			0x1f72e,0x1dc90,0x1ee4c,0x1dc88,0x1ee46,0x1dc84,0x1dc82,0x198a0,0x1cc58,0x1e62e, // 510
			0x1b9a0,0x19890,0x1ee6e,0x1b990,0x1dccc,0x1cc46,0x1b988,0x19884,0x1b984,0x19882, // 520
			0x1b982,0x110a0,0x18858,0x1c42e,0x131a0,0x11090,0x1884c,0x173a0,0x13190,0x198cc, // 530
			0x18846,0x17390,0x1b9cc,0x11084,0x17388,0x13184,0x11082,0x13182,0x110d8,0x1886e, // 540
			0x131d8,0x110cc,0x173d8,0x131cc,0x110c6,0x173cc,0x131c6,0x110ee,0x173ee,0x1dc50, // 550
			0x1ee2c,0x1dc48,0x1ee26,0x1dc44,0x1dc42,0x19850,0x1cc2c,0x1b8d0,0x19848,0x1cc26, // 560
			0x1b8c8,0x1dc66,0x1b8c4,0x19842,0x1b8c2,0x11050,0x1882c,0x130d0,0x11048,0x18826, // 570
			0x171d0,0x130c8,0x19866,0x171c8,0x1b8e6,0x11042,0x171c4,0x130c2,0x171c2,0x130ec, // 580
			0x171ec,0x171e6,0x1ee16,0x1dc22,0x1cc16,0x19824,0x19822,0x11028,0x13068,0x170e8, // 590
			0x11022,0x13062,0x18560,0x10a40,0x18530,0x10a20,0x18518,0x1c28e,0x10a10,0x1850c, // 600
			0x10a08,0x18506,0x10b60,0x185b8,0x1c2de,0x10b30,0x1859c,0x10b18,0x1858e,0x10b0c, // 610
			0x10b06,0x10bb8,0x185de,0x10b9c,0x10b8e,0x10bde,0x18d40,0x1c6b0,0x1e35c,0x18d20, // 620
			0x1c698,0x18d10,0x1c68c,0x18d08,0x1c686,0x18d04,0x10940,0x184b0,0x1c25c,0x11b40, // 630
			0x10920,0x1c6dc,0x1c24e,0x11b20,0x18d98,0x1c6ce,0x11b10,0x10908,0x18486,0x11b08, // 640
			0x18d86,0x10902,0x109b0,0x184dc,0x11bb0,0x10998,0x184ce,0x11b98,0x18dce,0x11b8c, // 650
			0x10986,0x109dc,0x11bdc,0x109ce,0x11bce,0x1cea0,0x1e758,0x1f3ae,0x1ce90,0x1e74c, // 660
			0x1ce88,0x1e746,0x1ce84,0x1ce82,0x18ca0,0x1c658,0x19da0,0x18c90,0x1c64c,0x19d90, // 670
			0x1cecc,0x1c646,0x19d88,0x18c84,0x19d84,0x18c82,0x19d82,0x108a0,0x18458,0x119a0, // 680
			0x10890,0x1c66e,0x13ba0,0x11990,0x18ccc,0x18446,0x13b90,0x19dcc,0x10884,0x13b88, // 690
			0x11984,0x10882,0x11982,0x108d8,0x1846e,0x119d8,0x108cc,0x13bd8,0x119cc,0x108c6, // 700
			0x13bcc,0x119c6,0x108ee,0x119ee,0x13bee,0x1ef50,0x1f7ac,0x1ef48,0x1f7a6,0x1ef44, // 710
			0x1ef42,0x1ce50,0x1e72c,0x1ded0,0x1ef6c,0x1e726,0x1dec8,0x1ef66,0x1dec4,0x1ce42, // 720
			0x1dec2,0x18c50,0x1c62c,0x19cd0,0x18c48,0x1c626,0x1bdd0,0x19cc8,0x1ce66,0x1bdc8, // 730
			0x1dee6,0x18c42,0x1bdc4,0x19cc2,0x1bdc2,0x10850,0x1842c,0x118d0,0x10848,0x18426, // 740
			0x139d0,0x118c8,0x18c66,0x17bd0,0x139c8,0x19ce6,0x10842,0x17bc8,0x1bde6,0x118c2, // 750
			0x17bc4,0x1086c,0x118ec,0x10866,0x139ec,0x118e6,0x17bec,0x139e6,0x17be6,0x1ef28, // 760
			0x1f796,0x1ef24,0x1ef22,0x1ce28,0x1e716,0x1de68,0x1ef36,0x1de64,0x1ce22,0x1de62, // 770
			0x18c28,0x1c616,0x19c68,0x18c24,0x1bce8,0x19c64,0x18c22,0x1bce4,0x19c62,0x1bce2, // 780
			0x10828,0x18416,0x11868,0x18c36,0x138e8,0x11864,0x10822,0x179e8,0x138e4,0x11862, // 790
			0x179e4,0x138e2,0x179e2,0x11876,0x179f6,0x1ef12,0x1de34,0x1de32,0x19c34,0x1bc74, // 800
			0x1bc72,0x11834,0x13874,0x178f4,0x178f2,0x10540,0x10520,0x18298,0x10510,0x10508, // 810
			0x10504,0x105b0,0x10598,0x1058c,0x10586,0x105dc,0x105ce,0x186a0,0x18690,0x1c34c, // 820
			0x18688,0x1c346,0x18684,0x18682,0x104a0,0x18258,0x10da0,0x186d8,0x1824c,0x10d90, // 830
			0x186cc,0x10d88,0x186c6,0x10d84,0x10482,0x10d82,0x104d8,0x1826e,0x10dd8,0x186ee, // 840
			0x10dcc,0x104c6,0x10dc6,0x104ee,0x10dee,0x1c750,0x1c748,0x1c744,0x1c742,0x18650, // 850
			0x18ed0,0x1c76c,0x1c326,0x18ec8,0x1c766,0x18ec4,0x18642,0x18ec2,0x10450,0x10cd0, // 860
			0x10448,0x18226,0x11dd0,0x10cc8,0x10444,0x11dc8,0x10cc4,0x10442,0x11dc4,0x10cc2, // 870
			0x1046c,0x10cec,0x10466,0x11dec,0x10ce6,0x11de6,0x1e7a8,0x1e7a4,0x1e7a2,0x1c728, // 880
			0x1cf68,0x1e7b6,0x1cf64,0x1c722,0x1cf62,0x18628,0x1c316,0x18e68,0x1c736,0x19ee8, // 890
			0x18e64,0x18622,0x19ee4,0x18e62,0x19ee2,0x10428,0x18216,0x10c68,0x18636,0x11ce8, // 900
			0x10c64,0x10422,0x13de8,0x11ce4,0x10c62,0x13de4,0x11ce2,0x10436,0x10c76,0x11cf6, // 910
			0x13df6,0x1f7d4,0x1f7d2,0x1e794,0x1efb4,0x1e792,0x1efb2,0x1c714,0x1cf34,0x1c712, // 920
			0x1df74,0x1cf32,0x1df72,0x18614,0x18e34,0x18612,0x19e74,0x18e32,0x1bef4),        // 929
		array( // cluster 3 -----------------------------------------------------------------------
			0x1f560,0x1fab8,0x1ea40,0x1f530,0x1fa9c,0x1ea20,0x1f518,0x1fa8e,0x1ea10,0x1f50c, //  10
			0x1ea08,0x1f506,0x1ea04,0x1eb60,0x1f5b8,0x1fade,0x1d640,0x1eb30,0x1f59c,0x1d620, //  20
			0x1eb18,0x1f58e,0x1d610,0x1eb0c,0x1d608,0x1eb06,0x1d604,0x1d760,0x1ebb8,0x1f5de, //  30
			0x1ae40,0x1d730,0x1eb9c,0x1ae20,0x1d718,0x1eb8e,0x1ae10,0x1d70c,0x1ae08,0x1d706, //  40
			0x1ae04,0x1af60,0x1d7b8,0x1ebde,0x15e40,0x1af30,0x1d79c,0x15e20,0x1af18,0x1d78e, //  50
			0x15e10,0x1af0c,0x15e08,0x1af06,0x15f60,0x1afb8,0x1d7de,0x15f30,0x1af9c,0x15f18, //  60
			0x1af8e,0x15f0c,0x15fb8,0x1afde,0x15f9c,0x15f8e,0x1e940,0x1f4b0,0x1fa5c,0x1e920, //  70
			0x1f498,0x1fa4e,0x1e910,0x1f48c,0x1e908,0x1f486,0x1e904,0x1e902,0x1d340,0x1e9b0, //  80
			0x1f4dc,0x1d320,0x1e998,0x1f4ce,0x1d310,0x1e98c,0x1d308,0x1e986,0x1d304,0x1d302, //  90
			0x1a740,0x1d3b0,0x1e9dc,0x1a720,0x1d398,0x1e9ce,0x1a710,0x1d38c,0x1a708,0x1d386, // 100
			0x1a704,0x1a702,0x14f40,0x1a7b0,0x1d3dc,0x14f20,0x1a798,0x1d3ce,0x14f10,0x1a78c, // 110
			0x14f08,0x1a786,0x14f04,0x14fb0,0x1a7dc,0x14f98,0x1a7ce,0x14f8c,0x14f86,0x14fdc, // 120
			0x14fce,0x1e8a0,0x1f458,0x1fa2e,0x1e890,0x1f44c,0x1e888,0x1f446,0x1e884,0x1e882, // 130
			0x1d1a0,0x1e8d8,0x1f46e,0x1d190,0x1e8cc,0x1d188,0x1e8c6,0x1d184,0x1d182,0x1a3a0, // 140
			0x1d1d8,0x1e8ee,0x1a390,0x1d1cc,0x1a388,0x1d1c6,0x1a384,0x1a382,0x147a0,0x1a3d8, // 150
			0x1d1ee,0x14790,0x1a3cc,0x14788,0x1a3c6,0x14784,0x14782,0x147d8,0x1a3ee,0x147cc, // 160
			0x147c6,0x147ee,0x1e850,0x1f42c,0x1e848,0x1f426,0x1e844,0x1e842,0x1d0d0,0x1e86c, // 170
			0x1d0c8,0x1e866,0x1d0c4,0x1d0c2,0x1a1d0,0x1d0ec,0x1a1c8,0x1d0e6,0x1a1c4,0x1a1c2, // 180
			0x143d0,0x1a1ec,0x143c8,0x1a1e6,0x143c4,0x143c2,0x143ec,0x143e6,0x1e828,0x1f416, // 190
			0x1e824,0x1e822,0x1d068,0x1e836,0x1d064,0x1d062,0x1a0e8,0x1d076,0x1a0e4,0x1a0e2, // 200
			0x141e8,0x1a0f6,0x141e4,0x141e2,0x1e814,0x1e812,0x1d034,0x1d032,0x1a074,0x1a072, // 210
			0x1e540,0x1f2b0,0x1f95c,0x1e520,0x1f298,0x1f94e,0x1e510,0x1f28c,0x1e508,0x1f286, // 220
			0x1e504,0x1e502,0x1cb40,0x1e5b0,0x1f2dc,0x1cb20,0x1e598,0x1f2ce,0x1cb10,0x1e58c, // 230
			0x1cb08,0x1e586,0x1cb04,0x1cb02,0x19740,0x1cbb0,0x1e5dc,0x19720,0x1cb98,0x1e5ce, // 240
			0x19710,0x1cb8c,0x19708,0x1cb86,0x19704,0x19702,0x12f40,0x197b0,0x1cbdc,0x12f20, // 250
			0x19798,0x1cbce,0x12f10,0x1978c,0x12f08,0x19786,0x12f04,0x12fb0,0x197dc,0x12f98, // 260
			0x197ce,0x12f8c,0x12f86,0x12fdc,0x12fce,0x1f6a0,0x1fb58,0x16bf0,0x1f690,0x1fb4c, // 270
			0x169f8,0x1f688,0x1fb46,0x168fc,0x1f684,0x1f682,0x1e4a0,0x1f258,0x1f92e,0x1eda0, // 280
			0x1e490,0x1fb6e,0x1ed90,0x1f6cc,0x1f246,0x1ed88,0x1e484,0x1ed84,0x1e482,0x1ed82, // 290
			0x1c9a0,0x1e4d8,0x1f26e,0x1dba0,0x1c990,0x1e4cc,0x1db90,0x1edcc,0x1e4c6,0x1db88, // 300
			0x1c984,0x1db84,0x1c982,0x1db82,0x193a0,0x1c9d8,0x1e4ee,0x1b7a0,0x19390,0x1c9cc, // 310
			0x1b790,0x1dbcc,0x1c9c6,0x1b788,0x19384,0x1b784,0x19382,0x1b782,0x127a0,0x193d8, // 320
			0x1c9ee,0x16fa0,0x12790,0x193cc,0x16f90,0x1b7cc,0x193c6,0x16f88,0x12784,0x16f84, // 330
			0x12782,0x127d8,0x193ee,0x16fd8,0x127cc,0x16fcc,0x127c6,0x16fc6,0x127ee,0x1f650, // 340
			0x1fb2c,0x165f8,0x1f648,0x1fb26,0x164fc,0x1f644,0x1647e,0x1f642,0x1e450,0x1f22c, // 350
			0x1ecd0,0x1e448,0x1f226,0x1ecc8,0x1f666,0x1ecc4,0x1e442,0x1ecc2,0x1c8d0,0x1e46c, // 360
			0x1d9d0,0x1c8c8,0x1e466,0x1d9c8,0x1ece6,0x1d9c4,0x1c8c2,0x1d9c2,0x191d0,0x1c8ec, // 370
			0x1b3d0,0x191c8,0x1c8e6,0x1b3c8,0x1d9e6,0x1b3c4,0x191c2,0x1b3c2,0x123d0,0x191ec, // 380
			0x167d0,0x123c8,0x191e6,0x167c8,0x1b3e6,0x167c4,0x123c2,0x167c2,0x123ec,0x167ec, // 390
			0x123e6,0x167e6,0x1f628,0x1fb16,0x162fc,0x1f624,0x1627e,0x1f622,0x1e428,0x1f216, // 400
			0x1ec68,0x1f636,0x1ec64,0x1e422,0x1ec62,0x1c868,0x1e436,0x1d8e8,0x1c864,0x1d8e4, // 410
			0x1c862,0x1d8e2,0x190e8,0x1c876,0x1b1e8,0x1d8f6,0x1b1e4,0x190e2,0x1b1e2,0x121e8, // 420
			0x190f6,0x163e8,0x121e4,0x163e4,0x121e2,0x163e2,0x121f6,0x163f6,0x1f614,0x1617e, // 430
			0x1f612,0x1e414,0x1ec34,0x1e412,0x1ec32,0x1c834,0x1d874,0x1c832,0x1d872,0x19074, // 440
			0x1b0f4,0x19072,0x1b0f2,0x120f4,0x161f4,0x120f2,0x161f2,0x1f60a,0x1e40a,0x1ec1a, // 450
			0x1c81a,0x1d83a,0x1903a,0x1b07a,0x1e2a0,0x1f158,0x1f8ae,0x1e290,0x1f14c,0x1e288, // 460
			0x1f146,0x1e284,0x1e282,0x1c5a0,0x1e2d8,0x1f16e,0x1c590,0x1e2cc,0x1c588,0x1e2c6, // 470
			0x1c584,0x1c582,0x18ba0,0x1c5d8,0x1e2ee,0x18b90,0x1c5cc,0x18b88,0x1c5c6,0x18b84, // 480
			0x18b82,0x117a0,0x18bd8,0x1c5ee,0x11790,0x18bcc,0x11788,0x18bc6,0x11784,0x11782, // 490
			0x117d8,0x18bee,0x117cc,0x117c6,0x117ee,0x1f350,0x1f9ac,0x135f8,0x1f348,0x1f9a6, // 500
			0x134fc,0x1f344,0x1347e,0x1f342,0x1e250,0x1f12c,0x1e6d0,0x1e248,0x1f126,0x1e6c8, // 510
			0x1f366,0x1e6c4,0x1e242,0x1e6c2,0x1c4d0,0x1e26c,0x1cdd0,0x1c4c8,0x1e266,0x1cdc8, // 520
			0x1e6e6,0x1cdc4,0x1c4c2,0x1cdc2,0x189d0,0x1c4ec,0x19bd0,0x189c8,0x1c4e6,0x19bc8, // 530
			0x1cde6,0x19bc4,0x189c2,0x19bc2,0x113d0,0x189ec,0x137d0,0x113c8,0x189e6,0x137c8, // 540
			0x19be6,0x137c4,0x113c2,0x137c2,0x113ec,0x137ec,0x113e6,0x137e6,0x1fba8,0x175f0, // 550
			0x1bafc,0x1fba4,0x174f8,0x1ba7e,0x1fba2,0x1747c,0x1743e,0x1f328,0x1f996,0x132fc, // 560
			0x1f768,0x1fbb6,0x176fc,0x1327e,0x1f764,0x1f322,0x1767e,0x1f762,0x1e228,0x1f116, // 570
			0x1e668,0x1e224,0x1eee8,0x1f776,0x1e222,0x1eee4,0x1e662,0x1eee2,0x1c468,0x1e236, // 580
			0x1cce8,0x1c464,0x1dde8,0x1cce4,0x1c462,0x1dde4,0x1cce2,0x1dde2,0x188e8,0x1c476, // 590
			0x199e8,0x188e4,0x1bbe8,0x199e4,0x188e2,0x1bbe4,0x199e2,0x1bbe2,0x111e8,0x188f6, // 600
			0x133e8,0x111e4,0x177e8,0x133e4,0x111e2,0x177e4,0x133e2,0x177e2,0x111f6,0x133f6, // 610
			0x1fb94,0x172f8,0x1b97e,0x1fb92,0x1727c,0x1723e,0x1f314,0x1317e,0x1f734,0x1f312, // 620
			0x1737e,0x1f732,0x1e214,0x1e634,0x1e212,0x1ee74,0x1e632,0x1ee72,0x1c434,0x1cc74, // 630
			0x1c432,0x1dcf4,0x1cc72,0x1dcf2,0x18874,0x198f4,0x18872,0x1b9f4,0x198f2,0x1b9f2, // 640
			0x110f4,0x131f4,0x110f2,0x173f4,0x131f2,0x173f2,0x1fb8a,0x1717c,0x1713e,0x1f30a, // 650
			0x1f71a,0x1e20a,0x1e61a,0x1ee3a,0x1c41a,0x1cc3a,0x1dc7a,0x1883a,0x1987a,0x1b8fa, // 660
			0x1107a,0x130fa,0x171fa,0x170be,0x1e150,0x1f0ac,0x1e148,0x1f0a6,0x1e144,0x1e142, // 670
			0x1c2d0,0x1e16c,0x1c2c8,0x1e166,0x1c2c4,0x1c2c2,0x185d0,0x1c2ec,0x185c8,0x1c2e6, // 680
			0x185c4,0x185c2,0x10bd0,0x185ec,0x10bc8,0x185e6,0x10bc4,0x10bc2,0x10bec,0x10be6, // 690
			0x1f1a8,0x1f8d6,0x11afc,0x1f1a4,0x11a7e,0x1f1a2,0x1e128,0x1f096,0x1e368,0x1e124, // 700
			0x1e364,0x1e122,0x1e362,0x1c268,0x1e136,0x1c6e8,0x1c264,0x1c6e4,0x1c262,0x1c6e2, // 710
			0x184e8,0x1c276,0x18de8,0x184e4,0x18de4,0x184e2,0x18de2,0x109e8,0x184f6,0x11be8, // 720
			0x109e4,0x11be4,0x109e2,0x11be2,0x109f6,0x11bf6,0x1f9d4,0x13af8,0x19d7e,0x1f9d2, // 730
			0x13a7c,0x13a3e,0x1f194,0x1197e,0x1f3b4,0x1f192,0x13b7e,0x1f3b2,0x1e114,0x1e334, // 740
			0x1e112,0x1e774,0x1e332,0x1e772,0x1c234,0x1c674,0x1c232,0x1cef4,0x1c672,0x1cef2, // 750
			0x18474,0x18cf4,0x18472,0x19df4,0x18cf2,0x19df2,0x108f4,0x119f4,0x108f2,0x13bf4, // 760
			0x119f2,0x13bf2,0x17af0,0x1bd7c,0x17a78,0x1bd3e,0x17a3c,0x17a1e,0x1f9ca,0x1397c, // 770
			0x1fbda,0x17b7c,0x1393e,0x17b3e,0x1f18a,0x1f39a,0x1f7ba,0x1e10a,0x1e31a,0x1e73a, // 780
			0x1ef7a,0x1c21a,0x1c63a,0x1ce7a,0x1defa,0x1843a,0x18c7a,0x19cfa,0x1bdfa,0x1087a, // 790
			0x118fa,0x139fa,0x17978,0x1bcbe,0x1793c,0x1791e,0x138be,0x179be,0x178bc,0x1789e, // 800
			0x1785e,0x1e0a8,0x1e0a4,0x1e0a2,0x1c168,0x1e0b6,0x1c164,0x1c162,0x182e8,0x1c176, // 810
			0x182e4,0x182e2,0x105e8,0x182f6,0x105e4,0x105e2,0x105f6,0x1f0d4,0x10d7e,0x1f0d2, // 820
			0x1e094,0x1e1b4,0x1e092,0x1e1b2,0x1c134,0x1c374,0x1c132,0x1c372,0x18274,0x186f4, // 830
			0x18272,0x186f2,0x104f4,0x10df4,0x104f2,0x10df2,0x1f8ea,0x11d7c,0x11d3e,0x1f0ca, // 840
			0x1f1da,0x1e08a,0x1e19a,0x1e3ba,0x1c11a,0x1c33a,0x1c77a,0x1823a,0x1867a,0x18efa, // 850
			0x1047a,0x10cfa,0x11dfa,0x13d78,0x19ebe,0x13d3c,0x13d1e,0x11cbe,0x13dbe,0x17d70, // 860
			0x1bebc,0x17d38,0x1be9e,0x17d1c,0x17d0e,0x13cbc,0x17dbc,0x13c9e,0x17d9e,0x17cb8, // 870
			0x1be5e,0x17c9c,0x17c8e,0x13c5e,0x17cde,0x17c5c,0x17c4e,0x17c2e,0x1c0b4,0x1c0b2, // 880
			0x18174,0x18172,0x102f4,0x102f2,0x1e0da,0x1c09a,0x1c1ba,0x1813a,0x1837a,0x1027a, // 890
			0x106fa,0x10ebe,0x11ebc,0x11e9e,0x13eb8,0x19f5e,0x13e9c,0x13e8e,0x11e5e,0x13ede, // 900
			0x17eb0,0x1bf5c,0x17e98,0x1bf4e,0x17e8c,0x17e86,0x13e5c,0x17edc,0x13e4e,0x17ece, // 910
			0x17e58,0x1bf2e,0x17e4c,0x17e46,0x13e2e,0x17e6e,0x17e2c,0x17e26,0x10f5e,0x11f5c, // 920
			0x11f4e,0x13f58,0x19fae,0x13f4c,0x13f46,0x11f2e,0x13f6e,0x13f2c,0x13f26),        // 929
		array( // cluster 6 -----------------------------------------------------------------------
			0x1abe0,0x1d5f8,0x153c0,0x1a9f0,0x1d4fc,0x151e0,0x1a8f8,0x1d47e,0x150f0,0x1a87c, //  10
			0x15078,0x1fad0,0x15be0,0x1adf8,0x1fac8,0x159f0,0x1acfc,0x1fac4,0x158f8,0x1ac7e, //  20
			0x1fac2,0x1587c,0x1f5d0,0x1faec,0x15df8,0x1f5c8,0x1fae6,0x15cfc,0x1f5c4,0x15c7e, //  30
			0x1f5c2,0x1ebd0,0x1f5ec,0x1ebc8,0x1f5e6,0x1ebc4,0x1ebc2,0x1d7d0,0x1ebec,0x1d7c8, //  40
			0x1ebe6,0x1d7c4,0x1d7c2,0x1afd0,0x1d7ec,0x1afc8,0x1d7e6,0x1afc4,0x14bc0,0x1a5f0, //  50
			0x1d2fc,0x149e0,0x1a4f8,0x1d27e,0x148f0,0x1a47c,0x14878,0x1a43e,0x1483c,0x1fa68, //  60
			0x14df0,0x1a6fc,0x1fa64,0x14cf8,0x1a67e,0x1fa62,0x14c7c,0x14c3e,0x1f4e8,0x1fa76, //  70
			0x14efc,0x1f4e4,0x14e7e,0x1f4e2,0x1e9e8,0x1f4f6,0x1e9e4,0x1e9e2,0x1d3e8,0x1e9f6, //  80
			0x1d3e4,0x1d3e2,0x1a7e8,0x1d3f6,0x1a7e4,0x1a7e2,0x145e0,0x1a2f8,0x1d17e,0x144f0, //  90
			0x1a27c,0x14478,0x1a23e,0x1443c,0x1441e,0x1fa34,0x146f8,0x1a37e,0x1fa32,0x1467c, // 100
			0x1463e,0x1f474,0x1477e,0x1f472,0x1e8f4,0x1e8f2,0x1d1f4,0x1d1f2,0x1a3f4,0x1a3f2, // 110
			0x142f0,0x1a17c,0x14278,0x1a13e,0x1423c,0x1421e,0x1fa1a,0x1437c,0x1433e,0x1f43a, // 120
			0x1e87a,0x1d0fa,0x14178,0x1a0be,0x1413c,0x1411e,0x141be,0x140bc,0x1409e,0x12bc0, // 130
			0x195f0,0x1cafc,0x129e0,0x194f8,0x1ca7e,0x128f0,0x1947c,0x12878,0x1943e,0x1283c, // 140
			0x1f968,0x12df0,0x196fc,0x1f964,0x12cf8,0x1967e,0x1f962,0x12c7c,0x12c3e,0x1f2e8, // 150
			0x1f976,0x12efc,0x1f2e4,0x12e7e,0x1f2e2,0x1e5e8,0x1f2f6,0x1e5e4,0x1e5e2,0x1cbe8, // 160
			0x1e5f6,0x1cbe4,0x1cbe2,0x197e8,0x1cbf6,0x197e4,0x197e2,0x1b5e0,0x1daf8,0x1ed7e, // 170
			0x169c0,0x1b4f0,0x1da7c,0x168e0,0x1b478,0x1da3e,0x16870,0x1b43c,0x16838,0x1b41e, // 180
			0x1681c,0x125e0,0x192f8,0x1c97e,0x16de0,0x124f0,0x1927c,0x16cf0,0x1b67c,0x1923e, // 190
			0x16c78,0x1243c,0x16c3c,0x1241e,0x16c1e,0x1f934,0x126f8,0x1937e,0x1fb74,0x1f932, // 200
			0x16ef8,0x1267c,0x1fb72,0x16e7c,0x1263e,0x16e3e,0x1f274,0x1277e,0x1f6f4,0x1f272, // 210
			0x16f7e,0x1f6f2,0x1e4f4,0x1edf4,0x1e4f2,0x1edf2,0x1c9f4,0x1dbf4,0x1c9f2,0x1dbf2, // 220
			0x193f4,0x193f2,0x165c0,0x1b2f0,0x1d97c,0x164e0,0x1b278,0x1d93e,0x16470,0x1b23c, // 230
			0x16438,0x1b21e,0x1641c,0x1640e,0x122f0,0x1917c,0x166f0,0x12278,0x1913e,0x16678, // 240
			0x1b33e,0x1663c,0x1221e,0x1661e,0x1f91a,0x1237c,0x1fb3a,0x1677c,0x1233e,0x1673e, // 250
			0x1f23a,0x1f67a,0x1e47a,0x1ecfa,0x1c8fa,0x1d9fa,0x191fa,0x162e0,0x1b178,0x1d8be, // 260
			0x16270,0x1b13c,0x16238,0x1b11e,0x1621c,0x1620e,0x12178,0x190be,0x16378,0x1213c, // 270
			0x1633c,0x1211e,0x1631e,0x121be,0x163be,0x16170,0x1b0bc,0x16138,0x1b09e,0x1611c, // 280
			0x1610e,0x120bc,0x161bc,0x1209e,0x1619e,0x160b8,0x1b05e,0x1609c,0x1608e,0x1205e, // 290
			0x160de,0x1605c,0x1604e,0x115e0,0x18af8,0x1c57e,0x114f0,0x18a7c,0x11478,0x18a3e, // 300
			0x1143c,0x1141e,0x1f8b4,0x116f8,0x18b7e,0x1f8b2,0x1167c,0x1163e,0x1f174,0x1177e, // 310
			0x1f172,0x1e2f4,0x1e2f2,0x1c5f4,0x1c5f2,0x18bf4,0x18bf2,0x135c0,0x19af0,0x1cd7c, // 320
			0x134e0,0x19a78,0x1cd3e,0x13470,0x19a3c,0x13438,0x19a1e,0x1341c,0x1340e,0x112f0, // 330
			0x1897c,0x136f0,0x11278,0x1893e,0x13678,0x19b3e,0x1363c,0x1121e,0x1361e,0x1f89a, // 340
			0x1137c,0x1f9ba,0x1377c,0x1133e,0x1373e,0x1f13a,0x1f37a,0x1e27a,0x1e6fa,0x1c4fa, // 350
			0x1cdfa,0x189fa,0x1bae0,0x1dd78,0x1eebe,0x174c0,0x1ba70,0x1dd3c,0x17460,0x1ba38, // 360
			0x1dd1e,0x17430,0x1ba1c,0x17418,0x1ba0e,0x1740c,0x132e0,0x19978,0x1ccbe,0x176e0, // 370
			0x13270,0x1993c,0x17670,0x1bb3c,0x1991e,0x17638,0x1321c,0x1761c,0x1320e,0x1760e, // 380
			0x11178,0x188be,0x13378,0x1113c,0x17778,0x1333c,0x1111e,0x1773c,0x1331e,0x1771e, // 390
			0x111be,0x133be,0x177be,0x172c0,0x1b970,0x1dcbc,0x17260,0x1b938,0x1dc9e,0x17230, // 400
			0x1b91c,0x17218,0x1b90e,0x1720c,0x17206,0x13170,0x198bc,0x17370,0x13138,0x1989e, // 410
			0x17338,0x1b99e,0x1731c,0x1310e,0x1730e,0x110bc,0x131bc,0x1109e,0x173bc,0x1319e, // 420
			0x1739e,0x17160,0x1b8b8,0x1dc5e,0x17130,0x1b89c,0x17118,0x1b88e,0x1710c,0x17106, // 430
			0x130b8,0x1985e,0x171b8,0x1309c,0x1719c,0x1308e,0x1718e,0x1105e,0x130de,0x171de, // 440
			0x170b0,0x1b85c,0x17098,0x1b84e,0x1708c,0x17086,0x1305c,0x170dc,0x1304e,0x170ce, // 450
			0x17058,0x1b82e,0x1704c,0x17046,0x1302e,0x1706e,0x1702c,0x17026,0x10af0,0x1857c, // 460
			0x10a78,0x1853e,0x10a3c,0x10a1e,0x10b7c,0x10b3e,0x1f0ba,0x1e17a,0x1c2fa,0x185fa, // 470
			0x11ae0,0x18d78,0x1c6be,0x11a70,0x18d3c,0x11a38,0x18d1e,0x11a1c,0x11a0e,0x10978, // 480
			0x184be,0x11b78,0x1093c,0x11b3c,0x1091e,0x11b1e,0x109be,0x11bbe,0x13ac0,0x19d70, // 490
			0x1cebc,0x13a60,0x19d38,0x1ce9e,0x13a30,0x19d1c,0x13a18,0x19d0e,0x13a0c,0x13a06, // 500
			0x11970,0x18cbc,0x13b70,0x11938,0x18c9e,0x13b38,0x1191c,0x13b1c,0x1190e,0x13b0e, // 510
			0x108bc,0x119bc,0x1089e,0x13bbc,0x1199e,0x13b9e,0x1bd60,0x1deb8,0x1ef5e,0x17a40, // 520
			0x1bd30,0x1de9c,0x17a20,0x1bd18,0x1de8e,0x17a10,0x1bd0c,0x17a08,0x1bd06,0x17a04, // 530
			0x13960,0x19cb8,0x1ce5e,0x17b60,0x13930,0x19c9c,0x17b30,0x1bd9c,0x19c8e,0x17b18, // 540
			0x1390c,0x17b0c,0x13906,0x17b06,0x118b8,0x18c5e,0x139b8,0x1189c,0x17bb8,0x1399c, // 550
			0x1188e,0x17b9c,0x1398e,0x17b8e,0x1085e,0x118de,0x139de,0x17bde,0x17940,0x1bcb0, // 560
			0x1de5c,0x17920,0x1bc98,0x1de4e,0x17910,0x1bc8c,0x17908,0x1bc86,0x17904,0x17902, // 570
			0x138b0,0x19c5c,0x179b0,0x13898,0x19c4e,0x17998,0x1bcce,0x1798c,0x13886,0x17986, // 580
			0x1185c,0x138dc,0x1184e,0x179dc,0x138ce,0x179ce,0x178a0,0x1bc58,0x1de2e,0x17890, // 590
			0x1bc4c,0x17888,0x1bc46,0x17884,0x17882,0x13858,0x19c2e,0x178d8,0x1384c,0x178cc, // 600
			0x13846,0x178c6,0x1182e,0x1386e,0x178ee,0x17850,0x1bc2c,0x17848,0x1bc26,0x17844, // 610
			0x17842,0x1382c,0x1786c,0x13826,0x17866,0x17828,0x1bc16,0x17824,0x17822,0x13816, // 620
			0x17836,0x10578,0x182be,0x1053c,0x1051e,0x105be,0x10d70,0x186bc,0x10d38,0x1869e, // 630
			0x10d1c,0x10d0e,0x104bc,0x10dbc,0x1049e,0x10d9e,0x11d60,0x18eb8,0x1c75e,0x11d30, // 640
			0x18e9c,0x11d18,0x18e8e,0x11d0c,0x11d06,0x10cb8,0x1865e,0x11db8,0x10c9c,0x11d9c, // 650
			0x10c8e,0x11d8e,0x1045e,0x10cde,0x11dde,0x13d40,0x19eb0,0x1cf5c,0x13d20,0x19e98, // 660
			0x1cf4e,0x13d10,0x19e8c,0x13d08,0x19e86,0x13d04,0x13d02,0x11cb0,0x18e5c,0x13db0, // 670
			0x11c98,0x18e4e,0x13d98,0x19ece,0x13d8c,0x11c86,0x13d86,0x10c5c,0x11cdc,0x10c4e, // 680
			0x13ddc,0x11cce,0x13dce,0x1bea0,0x1df58,0x1efae,0x1be90,0x1df4c,0x1be88,0x1df46, // 690
			0x1be84,0x1be82,0x13ca0,0x19e58,0x1cf2e,0x17da0,0x13c90,0x19e4c,0x17d90,0x1becc, // 700
			0x19e46,0x17d88,0x13c84,0x17d84,0x13c82,0x17d82,0x11c58,0x18e2e,0x13cd8,0x11c4c, // 710
			0x17dd8,0x13ccc,0x11c46,0x17dcc,0x13cc6,0x17dc6,0x10c2e,0x11c6e,0x13cee,0x17dee, // 720
			0x1be50,0x1df2c,0x1be48,0x1df26,0x1be44,0x1be42,0x13c50,0x19e2c,0x17cd0,0x13c48, // 730
			0x19e26,0x17cc8,0x1be66,0x17cc4,0x13c42,0x17cc2,0x11c2c,0x13c6c,0x11c26,0x17cec, // 740
			0x13c66,0x17ce6,0x1be28,0x1df16,0x1be24,0x1be22,0x13c28,0x19e16,0x17c68,0x13c24, // 750
			0x17c64,0x13c22,0x17c62,0x11c16,0x13c36,0x17c76,0x1be14,0x1be12,0x13c14,0x17c34, // 760
			0x13c12,0x17c32,0x102bc,0x1029e,0x106b8,0x1835e,0x1069c,0x1068e,0x1025e,0x106de, // 770
			0x10eb0,0x1875c,0x10e98,0x1874e,0x10e8c,0x10e86,0x1065c,0x10edc,0x1064e,0x10ece, // 780
			0x11ea0,0x18f58,0x1c7ae,0x11e90,0x18f4c,0x11e88,0x18f46,0x11e84,0x11e82,0x10e58, // 790
			0x1872e,0x11ed8,0x18f6e,0x11ecc,0x10e46,0x11ec6,0x1062e,0x10e6e,0x11eee,0x19f50, // 800
			0x1cfac,0x19f48,0x1cfa6,0x19f44,0x19f42,0x11e50,0x18f2c,0x13ed0,0x19f6c,0x18f26, // 810
			0x13ec8,0x11e44,0x13ec4,0x11e42,0x13ec2,0x10e2c,0x11e6c,0x10e26,0x13eec,0x11e66, // 820
			0x13ee6,0x1dfa8,0x1efd6,0x1dfa4,0x1dfa2,0x19f28,0x1cf96,0x1bf68,0x19f24,0x1bf64, // 830
			0x19f22,0x1bf62,0x11e28,0x18f16,0x13e68,0x11e24,0x17ee8,0x13e64,0x11e22,0x17ee4, // 840
			0x13e62,0x17ee2,0x10e16,0x11e36,0x13e76,0x17ef6,0x1df94,0x1df92,0x19f14,0x1bf34, // 850
			0x19f12,0x1bf32,0x11e14,0x13e34,0x11e12,0x17e74,0x13e32,0x17e72,0x1df8a,0x19f0a, // 860
			0x1bf1a,0x11e0a,0x13e1a,0x17e3a,0x1035c,0x1034e,0x10758,0x183ae,0x1074c,0x10746, // 870
			0x1032e,0x1076e,0x10f50,0x187ac,0x10f48,0x187a6,0x10f44,0x10f42,0x1072c,0x10f6c, // 880
			0x10726,0x10f66,0x18fa8,0x1c7d6,0x18fa4,0x18fa2,0x10f28,0x18796,0x11f68,0x18fb6, // 890
			0x11f64,0x10f22,0x11f62,0x10716,0x10f36,0x11f76,0x1cfd4,0x1cfd2,0x18f94,0x19fb4, // 900
			0x18f92,0x19fb2,0x10f14,0x11f34,0x10f12,0x13f74,0x11f32,0x13f72,0x1cfca,0x18f8a, // 910
			0x19f9a,0x10f0a,0x11f1a,0x13f3a,0x103ac,0x103a6,0x107a8,0x183d6,0x107a4,0x107a2, // 920
			0x10396,0x107b6,0x187d4,0x187d2,0x10794,0x10fb4,0x10792,0x10fb2,0x1c7ea)         // 929
	); // end of $clusters array

	/**
	 * Array of factors of the Reed-Solomon polynomial equations used for error correction; one sub array for each correction level (0-8).
	 * @private
	 */
	private $rsfactors = array(
		array( // ECL 0 (2 factors) -------------------------------------------------------------------------------
			0x01b,0x395),                                                                                    //   2
		array( // ECL 1 (4 factors) -------------------------------------------------------------------------------
			0x20a,0x238,0x2d3,0x329),                                                                        //   4
		array( // ECL 2 (8 factors) -------------------------------------------------------------------------------
			0x0ed,0x134,0x1b4,0x11c,0x286,0x28d,0x1ac,0x17b),                                                //   8
		array( // ECL 3 (16 factors) ------------------------------------------------------------------------------
			0x112,0x232,0x0e8,0x2f3,0x257,0x20c,0x321,0x084,0x127,0x074,0x1ba,0x1ac,0x127,0x02a,0x0b0,0x041),//  16
		array( // ECL 4 (32 factors) ------------------------------------------------------------------------------
			0x169,0x23f,0x39a,0x20d,0x0b0,0x24a,0x280,0x141,0x218,0x2e6,0x2a5,0x2e6,0x2af,0x11c,0x0c1,0x205, //  16
			0x111,0x1ee,0x107,0x093,0x251,0x320,0x23b,0x140,0x323,0x085,0x0e7,0x186,0x2ad,0x14a,0x03f,0x19a),//  32
		array( // ECL 5 (64 factors) ------------------------------------------------------------------------------
			0x21b,0x1a6,0x006,0x05d,0x35e,0x303,0x1c5,0x06a,0x262,0x11f,0x06b,0x1f9,0x2dd,0x36d,0x17d,0x264, //  16
			0x2d3,0x1dc,0x1ce,0x0ac,0x1ae,0x261,0x35a,0x336,0x21f,0x178,0x1ff,0x190,0x2a0,0x2fa,0x11b,0x0b8, //  32
			0x1b8,0x023,0x207,0x01f,0x1cc,0x252,0x0e1,0x217,0x205,0x160,0x25d,0x09e,0x28b,0x0c9,0x1e8,0x1f6, //  48
			0x288,0x2dd,0x2cd,0x053,0x194,0x061,0x118,0x303,0x348,0x275,0x004,0x17d,0x34b,0x26f,0x108,0x21f),//  64
		array( // ECL 6 (128 factors) -----------------------------------------------------------------------------
			0x209,0x136,0x360,0x223,0x35a,0x244,0x128,0x17b,0x035,0x30b,0x381,0x1bc,0x190,0x39d,0x2ed,0x19f, //  16
			0x336,0x05d,0x0d9,0x0d0,0x3a0,0x0f4,0x247,0x26c,0x0f6,0x094,0x1bf,0x277,0x124,0x38c,0x1ea,0x2c0, //  32
			0x204,0x102,0x1c9,0x38b,0x252,0x2d3,0x2a2,0x124,0x110,0x060,0x2ac,0x1b0,0x2ae,0x25e,0x35c,0x239, //  48
			0x0c1,0x0db,0x081,0x0ba,0x0ec,0x11f,0x0c0,0x307,0x116,0x0ad,0x028,0x17b,0x2c8,0x1cf,0x286,0x308, //  64
			0x0ab,0x1eb,0x129,0x2fb,0x09c,0x2dc,0x05f,0x10e,0x1bf,0x05a,0x1fb,0x030,0x0e4,0x335,0x328,0x382, //  80
			0x310,0x297,0x273,0x17a,0x17e,0x106,0x17c,0x25a,0x2f2,0x150,0x059,0x266,0x057,0x1b0,0x29e,0x268, //  96
			0x09d,0x176,0x0f2,0x2d6,0x258,0x10d,0x177,0x382,0x34d,0x1c6,0x162,0x082,0x32e,0x24b,0x324,0x022, // 112
			0x0d3,0x14a,0x21b,0x129,0x33b,0x361,0x025,0x205,0x342,0x13b,0x226,0x056,0x321,0x004,0x06c,0x21b),// 128
		array( // ECL 7 (256 factors) -----------------------------------------------------------------------------
			0x20c,0x37e,0x04b,0x2fe,0x372,0x359,0x04a,0x0cc,0x052,0x24a,0x2c4,0x0fa,0x389,0x312,0x08a,0x2d0, //  16
			0x35a,0x0c2,0x137,0x391,0x113,0x0be,0x177,0x352,0x1b6,0x2dd,0x0c2,0x118,0x0c9,0x118,0x33c,0x2f5, //  32
			0x2c6,0x32e,0x397,0x059,0x044,0x239,0x00b,0x0cc,0x31c,0x25d,0x21c,0x391,0x321,0x2bc,0x31f,0x089, //  48
			0x1b7,0x1a2,0x250,0x29c,0x161,0x35b,0x172,0x2b6,0x145,0x0f0,0x0d8,0x101,0x11c,0x225,0x0d1,0x374, //  64
			0x13b,0x046,0x149,0x319,0x1ea,0x112,0x36d,0x0a2,0x2ed,0x32c,0x2ac,0x1cd,0x14e,0x178,0x351,0x209, //  80
			0x133,0x123,0x323,0x2c8,0x013,0x166,0x18f,0x38c,0x067,0x1ff,0x033,0x008,0x205,0x0e1,0x121,0x1d6, //  96
			0x27d,0x2db,0x042,0x0ff,0x395,0x10d,0x1cf,0x33e,0x2da,0x1b1,0x350,0x249,0x088,0x21a,0x38a,0x05a, // 112
			0x002,0x122,0x2e7,0x0c7,0x28f,0x387,0x149,0x031,0x322,0x244,0x163,0x24c,0x0bc,0x1ce,0x00a,0x086, // 128
			0x274,0x140,0x1df,0x082,0x2e3,0x047,0x107,0x13e,0x176,0x259,0x0c0,0x25d,0x08e,0x2a1,0x2af,0x0ea, // 144
			0x2d2,0x180,0x0b1,0x2f0,0x25f,0x280,0x1c7,0x0c1,0x2b1,0x2c3,0x325,0x281,0x030,0x03c,0x2dc,0x26d, // 160
			0x37f,0x220,0x105,0x354,0x28f,0x135,0x2b9,0x2f3,0x2f4,0x03c,0x0e7,0x305,0x1b2,0x1a5,0x2d6,0x210, // 176
			0x1f7,0x076,0x031,0x31b,0x020,0x090,0x1f4,0x0ee,0x344,0x18a,0x118,0x236,0x13f,0x009,0x287,0x226, // 192
			0x049,0x392,0x156,0x07e,0x020,0x2a9,0x14b,0x318,0x26c,0x03c,0x261,0x1b9,0x0b4,0x317,0x37d,0x2f2, // 208
			0x25d,0x17f,0x0e4,0x2ed,0x2f8,0x0d5,0x036,0x129,0x086,0x036,0x342,0x12b,0x39a,0x0bf,0x38e,0x214, // 224
			0x261,0x33d,0x0bd,0x014,0x0a7,0x01d,0x368,0x1c1,0x053,0x192,0x029,0x290,0x1f9,0x243,0x1e1,0x0ad, // 240
			0x194,0x0fb,0x2b0,0x05f,0x1f1,0x22b,0x282,0x21f,0x133,0x09f,0x39c,0x22e,0x288,0x037,0x1f1,0x00a),// 256
		array( // ECL 8 (512 factors) -----------------------------------------------------------------------------
			0x160,0x04d,0x175,0x1f8,0x023,0x257,0x1ac,0x0cf,0x199,0x23e,0x076,0x1f2,0x11d,0x17c,0x15e,0x1ec, //  16
			0x0c5,0x109,0x398,0x09b,0x392,0x12b,0x0e5,0x283,0x126,0x367,0x132,0x058,0x057,0x0c1,0x160,0x30d, //  32
			0x34e,0x04b,0x147,0x208,0x1b3,0x21f,0x0cb,0x29a,0x0f9,0x15a,0x30d,0x26d,0x280,0x10c,0x31a,0x216, //  48
			0x21b,0x30d,0x198,0x186,0x284,0x066,0x1dc,0x1f3,0x122,0x278,0x221,0x025,0x35a,0x394,0x228,0x029, //  64
			0x21e,0x121,0x07a,0x110,0x17f,0x320,0x1e5,0x062,0x2f0,0x1d8,0x2f9,0x06b,0x310,0x35c,0x292,0x2e5, //  80
			0x122,0x0cc,0x2a9,0x197,0x357,0x055,0x063,0x03e,0x1e2,0x0b4,0x014,0x129,0x1c3,0x251,0x391,0x08e, //  96
			0x328,0x2ac,0x11f,0x218,0x231,0x04c,0x28d,0x383,0x2d9,0x237,0x2e8,0x186,0x201,0x0c0,0x204,0x102, // 112
			0x0f0,0x206,0x31a,0x18b,0x300,0x350,0x033,0x262,0x180,0x0a8,0x0be,0x33a,0x148,0x254,0x312,0x12f, // 128
			0x23a,0x17d,0x19f,0x281,0x09c,0x0ed,0x097,0x1ad,0x213,0x0cf,0x2a4,0x2c6,0x059,0x0a8,0x130,0x192, // 144
			0x028,0x2c4,0x23f,0x0a2,0x360,0x0e5,0x041,0x35d,0x349,0x200,0x0a4,0x1dd,0x0dd,0x05c,0x166,0x311, // 160
			0x120,0x165,0x352,0x344,0x33b,0x2e0,0x2c3,0x05e,0x008,0x1ee,0x072,0x209,0x002,0x1f3,0x353,0x21f, // 176
			0x098,0x2d9,0x303,0x05f,0x0f8,0x169,0x242,0x143,0x358,0x31d,0x121,0x033,0x2ac,0x1d2,0x215,0x334, // 192
			0x29d,0x02d,0x386,0x1c4,0x0a7,0x156,0x0f4,0x0ad,0x023,0x1cf,0x28b,0x033,0x2bb,0x24f,0x1c4,0x242, // 208
			0x025,0x07c,0x12a,0x14c,0x228,0x02b,0x1ab,0x077,0x296,0x309,0x1db,0x352,0x2fc,0x16c,0x242,0x38f, // 224
			0x11b,0x2c7,0x1d8,0x1a4,0x0f5,0x120,0x252,0x18a,0x1ff,0x147,0x24d,0x309,0x2bb,0x2b0,0x02b,0x198, // 240
			0x34a,0x17f,0x2d1,0x209,0x230,0x284,0x2ca,0x22f,0x03e,0x091,0x369,0x297,0x2c9,0x09f,0x2a0,0x2d9, // 256
			0x270,0x03b,0x0c1,0x1a1,0x09e,0x0d1,0x233,0x234,0x157,0x2b5,0x06d,0x260,0x233,0x16d,0x0b5,0x304, // 272
			0x2a5,0x136,0x0f8,0x161,0x2c4,0x19a,0x243,0x366,0x269,0x349,0x278,0x35c,0x121,0x218,0x023,0x309, // 288
			0x26a,0x24a,0x1a8,0x341,0x04d,0x255,0x15a,0x10d,0x2f5,0x278,0x2b7,0x2ef,0x14b,0x0f7,0x0b8,0x02d, // 304
			0x313,0x2a8,0x012,0x042,0x197,0x171,0x036,0x1ec,0x0e4,0x265,0x33e,0x39a,0x1b5,0x207,0x284,0x389, // 320
			0x315,0x1a4,0x131,0x1b9,0x0cf,0x12c,0x37c,0x33b,0x08d,0x219,0x17d,0x296,0x201,0x038,0x0fc,0x155, // 336
			0x0f2,0x31d,0x346,0x345,0x2d0,0x0e0,0x133,0x277,0x03d,0x057,0x230,0x136,0x2f4,0x299,0x18d,0x328, // 352
			0x353,0x135,0x1d9,0x31b,0x17a,0x01f,0x287,0x393,0x1cb,0x326,0x24e,0x2db,0x1a9,0x0d8,0x224,0x0f9, // 368
			0x141,0x371,0x2bb,0x217,0x2a1,0x30e,0x0d2,0x32f,0x389,0x12f,0x34b,0x39a,0x119,0x049,0x1d5,0x317, // 384
			0x294,0x0a2,0x1f2,0x134,0x09b,0x1a6,0x38b,0x331,0x0bb,0x03e,0x010,0x1a9,0x217,0x150,0x11e,0x1b5, // 400
			0x177,0x111,0x262,0x128,0x0b7,0x39b,0x074,0x29b,0x2ef,0x161,0x03e,0x16e,0x2b3,0x17b,0x2af,0x34a, // 416
			0x025,0x165,0x2d0,0x2e6,0x14a,0x005,0x027,0x39b,0x137,0x1a8,0x0f2,0x2ed,0x141,0x036,0x29d,0x13c, // 432
			0x156,0x12b,0x216,0x069,0x29b,0x1e8,0x280,0x2a0,0x240,0x21c,0x13c,0x1e6,0x2d1,0x262,0x02e,0x290, // 448
			0x1bf,0x0ab,0x268,0x1d0,0x0be,0x213,0x129,0x141,0x2fa,0x2f0,0x215,0x0af,0x086,0x00e,0x17d,0x1b1, // 464
			0x2cd,0x02d,0x06f,0x014,0x254,0x11c,0x2e0,0x08a,0x286,0x19b,0x36d,0x29d,0x08d,0x397,0x02d,0x30c, // 480
			0x197,0x0a4,0x14c,0x383,0x0a5,0x2d6,0x258,0x145,0x1f2,0x28f,0x165,0x2f0,0x300,0x0df,0x351,0x287, // 496
			0x03f,0x136,0x35f,0x0fb,0x16e,0x130,0x11a,0x2e2,0x2a3,0x19a,0x185,0x0f4,0x01f,0x079,0x12f,0x107) // 512
	);


	/**
	 * This is the class constructor.
	 * Creates a PDF417 object
	 * @param $code (string) code to represent using PDF417
	 * @param $aspectratio (float) the width to height of the symbol (excluding quiet zones)
	 * @param $ecl (int) error correction level (0-8); default -1 = automatic correction level
	 * @param $macro (array) information for macro block
	 * @public
	 */
	public function __construct($code, $aspectratio=1, $ecl=-1, $macro=array()) {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$code = (string) $code; // force string
		//--
		$aspectratio = (int) $aspectratio;
		if($aspectratio < 1) {
			$aspectratio = 1;
		} //end if
		if($aspectratio > 17) {
			$aspectratio = 17;
		} //end if
		//--
		$this->barcode_array = array();
		$barcode_array = array();
		$barcode_array['code'] = $code;
		//-- get the input sequence array
		$sequence = $this->getInputSequences($code);
		$codewords = array(); // array of code-words
		foreach($sequence as $u => $seq) {
			$cw = $this->getCompaction($seq[0], $seq[1], true);
			$codewords = array_merge($codewords, $cw);
		} //end foreach
		if($codewords[0] == 900) {
			array_shift($codewords); // Text Alpha is the default mode, so remove the first code
		} //end if
		//-- count number of codewords
		$numcw = count($codewords);
		if($numcw > 925) {
			return false; // reached maximum data codeword capacity
		} //end if
		//-- build macro control block codewords
		if(!empty($macro)) {
			$macrocw = array();
			//-- beginning of macro control block
			$macrocw[] = 928;
			//-- segment index
			$cw = $this->getCompaction(902, sprintf('%05d', $macro['segment_index']), false);
			$macrocw = array_merge($macrocw, $cw);
			//-- file ID
			$cw = $this->getCompaction(900, $macro['file_id'], false);
			$macrocw = array_merge($macrocw, $cw);
			//-- optional fields
			$optmodes = array(900,902,902,900,900,902,902);
			$optsize = array(-1,2,4,-1,-1,-1,2);
			foreach ($optmodes as $k => $omode) {
				if(isset($macro['option_'.$k])) {
					$macrocw[] = 923;
					$macrocw[] = $k;
					if($optsize[$k] == 2) {
						$macro['option_'.$k] = sprintf('%05d', $macro['option_'.$k]);
					} elseif($optsize[$k] == 4) {
						$macro['option_'.$k] = sprintf('%010d', $macro['option_'.$k]);
					} //end if else
					$cw = $this->getCompaction($omode, $macro['option_'.$k], false);
					$macrocw = array_merge($macrocw, $cw);
				} //end if
			} //end foreach
			if($macro['segment_index'] == ($macro['segment_total'] - 1)) {
				// end of control block
				$macrocw[] = 922;
			} //end if
			//-- update total codewords
			$numcw += count($macrocw);
			//--
		} //end if
		//-- unixman fix for PHP 7 (Bit Shift by Negative Number is not allowed)
		if($ecl < 0) {
			$ecl = 1;
		} //end if #end fix
		//-- set error correction level
		$ecl = $this->getErrorCorrectionLevel($ecl, $numcw);
		//-- number of codewords for error correction
		$errsize = (2 << $ecl);
		//-- calculate number of columns (number of codewords per row) and rows
		$nce = ($numcw + $errsize + 1);
		$cols = round((sqrt(4761 + (68 * $aspectratio * self::BCODE2D_PDF_417_ROW_HEIGHT * $nce)) - 69) / 34);
		//-- adjust cols
		if($cols < 1) {
			$cols = 1;
		} elseif($cols > 30) {
			$cols = 30;
		} //end if else
		//--
		$rows = ceil($nce / $cols);
		$size = ($cols * $rows);
		//-- adjust rows
		if(($rows < 3) OR ($rows > 90)) {
			if($rows < 3) {
				$rows = 3;
			} elseif($rows > 90) {
				$rows = 90;
			} //end if else
			$cols = ceil($size / $rows);
			$size = ($cols * $rows);
		} //end if
		if($size > 928) {
			//-- set dimensions to get maximum capacity
			if(abs($aspectratio - (17 * 29 / 32)) < abs($aspectratio - (17 * 16 / 58))) {
				$cols = 29;
				$rows = 32;
			} else {
				$cols = 16;
				$rows = 58;
			} //end if else
			//--
			$size = 928;
			//--
		} //end if
		//-- calculate padding
		$pad = ($size - $nce);
		if($pad > 0) {
			if(($size - $rows) == $nce) {
				--$rows;
				$size -= $rows;
			} else {
				$codewords = array_merge($codewords, array_fill(0, $pad, 900)); // add pading
			} //end if else
		} //end if
		//--
		if(!empty($macro)) {
			$codewords = array_merge($codewords, $macrocw); // add macro section
		} //end if
		//-- Symbol Length Descriptor (number of data codewords including Symbol Length Descriptor and pad codewords)
		$sld = $size - $errsize;
		//-- add symbol length description
		array_unshift($codewords, $sld);
		//-- calculate error correction
		$ecw = $this->getErrorCorrection($codewords, $ecl);
		//-- add error correction codewords
		$codewords = array_merge($codewords, $ecw);
		//-- add horizontal quiet zones to start and stop patterns
		$pstart = str_repeat('0', self::BCODE2D_PDF_417_QUIET_HORIZ).$this->start_pattern;
		$pstop = $this->stop_pattern.str_repeat('0', self::BCODE2D_PDF_417_QUIET_HORIZ);
		$barcode_array['num_rows'] = ($rows * self::BCODE2D_PDF_417_ROW_HEIGHT) + (2 * self::BCODE2D_PDF_417_QUIET_VERT);
		$barcode_array['num_cols'] = (($cols + 2) * 17) + 35 + (2 * self::BCODE2D_PDF_417_QUIET_HORIZ);
		$barcode_array['bcode'] = array();
		//-- build rows for vertical quiet zone
		if(self::BCODE2D_PDF_417_QUIET_VERT > 0) {
			$empty_row = array_fill(0, $barcode_array['num_cols'], 0);
			for($i = 0; $i < self::BCODE2D_PDF_417_QUIET_VERT; ++$i) {
				$barcode_array['bcode'][] = $empty_row; // add vertical quiet rows
			} //end for
		} //end if
		//--
		$k = 0; // codeword index
		$cid = 0; // initial cluster
		//-- for each row
		for($r = 0; $r < $rows; ++$r) {
			//-- row start code
			$row = $pstart;
			//--
			switch($cid) {
				case 0:
					$L = ((30 * intval($r / 3)) + intval(($rows - 1) / 3));
					break;
				case 1:
					$L = ((30 * intval($r / 3)) + ($ecl * 3) + (($rows - 1) % 3));
					break;
				case 2:
					$L = ((30 * intval($r / 3)) + ($cols - 1));
					break;
			} //end switch
			//-- left row indicator
			$row .= sprintf('%17b', $this->clusters[$cid][$L]);
			//-- for each column
			for($c = 0; $c < $cols; ++$c) {
				$row .= sprintf('%17b', $this->clusters[$cid][$codewords[$k]]);
				++$k;
			} //end for
			switch($cid) {
				case 0:
					$L = ((30 * intval($r / 3)) + ($cols - 1));
					break;
				case 1:
					$L = ((30 * intval($r / 3)) + intval(($rows - 1) / 3));
					break;
				case 2:
					$L = ((30 * intval($r / 3)) + ($ecl * 3) + (($rows - 1) % 3));
					break;
			} //end switch
			//-- right row indicator
			$row .= sprintf('%17b', $this->clusters[$cid][$L]);
			//-- row stop code
			$row .= $pstop;
			//-- convert the string to array
			$arow = preg_split('//', (string)$row, -1, PREG_SPLIT_NO_EMPTY);
			//-- duplicate row to get the desired height
			for($h = 0; $h < self::BCODE2D_PDF_417_ROW_HEIGHT; ++$h) {
				$barcode_array['bcode'][] = $arow;
			} //end for
			//--
			++$cid;
			//--
			if($cid > 2) {
				$cid = 0;
			} //end if
			//--
		} //end for
		//--
		if(self::BCODE2D_PDF_417_QUIET_VERT > 0) {
			for($i = 0; $i < self::BCODE2D_PDF_417_QUIET_VERT; ++$i) {
				$barcode_array['bcode'][] = $empty_row; // add vertical quiet rows
			} //end for
		} //end if
		//--
		$this->barcode_array = (array) $barcode_array;
		//--
	} //END FUNCTION


	/**
	 * Returns a barcode array which is readable by TCPDF
	 * @return array barcode array readable by TCPDF;
	 * @public
	 */
	public function getBarcodeArray() {
		//--
		return (array) $this->barcode_array;
		//--
	} //END FUNCTION


	/**
	 * Returns the error correction level (0-8) to be used
	 * @param $ecl (int) error correction level
	 * @param $numcw (int) number of data codewords
	 * @return int error correction level
	 * @private
	 */
	private function getErrorCorrectionLevel($ecl, $numcw) {
		//-- get maximum correction level
		$maxecl = 8; // starting error level
		$maxerrsize = (928 - $numcw); // available codewords for error
		while ($maxecl > 0) {
			$errsize = (2 << $ecl);
			if ($maxerrsize >= $errsize) {
				break;
			} //end if
			--$maxecl;
		} //end while
		//-- check for automatic levels
		if(($ecl < 0) OR ($ecl > 8)) {
			if($numcw < 41) {
				$ecl = 2;
			} elseif($numcw < 161) {
				$ecl = 3;
			} elseif($numcw < 321) {
				$ecl = 4;
			} elseif($numcw < 864) {
				$ecl = 5;
			} else {
				$ecl = $maxecl;
			} //end if else
		} //end if
		//--
		if($ecl > $maxecl) {
			$ecl = $maxecl;
		} //end if
		//--
		return $ecl;
		//--
	} //END FUNCTION


	/**
	 * Returns the error correction codewords
	 * @param $cw (array) array of codewords including Symbol Length Descriptor and pad
	 * @param $ecl (int) error correction level 0-8
	 * @return array of error correction codewords
	 * @private
	 */
	private function getErrorCorrection($cw, $ecl) {
		//-- get error correction coefficients
		$ecc = $this->rsfactors[$ecl];
		//-- number of error correction factors
		$eclsize = (2 << $ecl);
		//-- maximum index for $rsfactors[$ecl]
		$eclmaxid = ($eclsize - 1);
		//-- initialize array of error correction codewords
		$ecw = array_fill(0, $eclsize, 0);
		//-- for each data codeword
		foreach($cw as $k => $d) {
			$t1 = ($d + $ecw[$eclmaxid]) % 929;
			for ($j = $eclmaxid; $j > 0; --$j) {
				$t2 = ($t1 * $ecc[$j]) % 929;
				$t3 = 929 - $t2;
				$ecw[$j] = ($ecw[($j - 1)] + $t3) % 929;
			} //end for
			$t2 = ($t1 * $ecc[0]) % 929;
			$t3 = 929 - $t2;
			$ecw[0] = $t3 % 929;
		} //end foreach
		//--
		foreach($ecw as $j => $e) {
			if($e != 0) {
				$ecw[$j] = 929 - $e;
			} //end if
		} //end foreach
		//--
		$ecw = array_reverse($ecw);
		//--
		return $ecw;
		//--
	} //END FUNCTION


	/**
	 * Create array of sequences from input
	 * @param $code (string) code
	 * @return bidimensional array containing characters and classification
	 * @private
	 */
	private function getInputSequences($code) {
		//--
		$sequence_array = array(); // array to be returned
		//--
		$numseq = array();
		preg_match_all('/([0-9]{13,44})/', (string)$code, $numseq, PREG_OFFSET_CAPTURE); // get numeric sequences
		$numseq[1][] = array('', strlen($code));
		$offset = 0;
		foreach($numseq[1] as $u => $seq) {
			$seqlen = strlen($seq[0]);
			if($seq[1] > 0) {
				$prevseq = substr($code, $offset, ($seq[1] - $offset)); // extract text sequence before the number sequence
				$textseq = array();
				preg_match_all('/([\x09\x0a\x0d\x20-\x7e]{5,})/', (string)$prevseq, $textseq, PREG_OFFSET_CAPTURE); // get text sequences
				$textseq[1][] = array('', strlen($prevseq));
				$txtoffset = 0;
				foreach($textseq[1] as $u => $txtseq) {
					$txtseqlen = strlen($txtseq[0]);
					if($txtseq[1] > 0) {
						// extract byte sequence before the text sequence
						$prevtxtseq = substr($prevseq, $txtoffset, ($txtseq[1] - $txtoffset));
						if(strlen($prevtxtseq) > 0) {
							// add BYTE sequence
							if((strlen($prevtxtseq) == 1) AND ((count($sequence_array) > 0) AND ($sequence_array[(count($sequence_array) - 1)][0] == 900))) {
								$sequence_array[] = array(913, $prevtxtseq);
							} elseif((strlen($prevtxtseq) % 6) == 0) {
								$sequence_array[] = array(924, $prevtxtseq);
							} else {
								$sequence_array[] = array(901, $prevtxtseq);
							} //end if else
						} //end if
					} //end if
					if($txtseqlen > 0) {
						// add numeric sequence
						$sequence_array[] = array(900, $txtseq[0]);
					} //end if
					$txtoffset = $txtseq[1] + $txtseqlen;
				} //end foreach
			} //end if
			if($seqlen > 0) {
				// add numeric sequence
				$sequence_array[] = array(902, $seq[0]);
			} //end if
			$offset = $seq[1] + $seqlen;
		} //end foreach
		//--
		return $sequence_array;
		//--
	} //END FUNCTION


	/**
	 * Compact data by mode.
	 * @param $mode (int) compaction mode number
	 * @param $code (string) data to compact
	 * @param $addmode (boolean) if true add the mode codeword at first position
	 * @return array of codewords
	 * @private
	 */
	private function getCompaction($mode, $code, $addmode=true) {
		//--
		$cw = array(); // array of codewords to return
		//--
		switch($mode) {
			case 900: // Text Compaction mode latch
				//--
				$submode = 0; // default Alpha sub-mode
				$txtarr = array(); // array of characters and sub-mode switching characters
				$codelen = strlen($code);
				//--
				for($i = 0; $i < $codelen; ++$i) {
					$chval = ord($code{$i});
					if(($k = array_search($chval, $this->textsubmodes[$submode])) !== false) {
						// we are on the same sub-mode
						$txtarr[] = $k;
					} else {
						//-- the sub-mode is changed
						for($s = 0; $s < 4; ++$s) {
							//-- search new sub-mode
							if(($s != $submode) AND (($k = array_search($chval, $this->textsubmodes[$s])) !== false)) {
								//-- $s is the new submode
								if(((($i + 1) == $codelen) OR ((($i + 1) < $codelen) AND (array_search(ord($code{($i + 1)}), $this->textsubmodes[$submode]) !== false))) AND (($s == 3) OR (($s == 0) AND ($submode == 1)))) {
									//-- shift (temporary change only for this char)
									if($s == 3) {
										//-- shift to puntuaction
										$txtarr[] = 29;
									} else {
										//-- shift from lower to alpha
										$txtarr[] = 27;
									} //end if else
								} else {
									//-- latch
									$txtarr	= array_merge($txtarr, $this->textlatch[(string)$submode.$s]);
									//-- set new submode
									$submode = $s;
								} //end if else
								//-- add characted code to array
								$txtarr[] = $k;
								//--
								break;
								//--
							} //end if
							//--
						} //end for
						//--
					} //end if else
					//--
				} //end for
				//--
				$txtarrlen = count($txtarr);
				//--
				if(($txtarrlen % 2) != 0) {
					// add padding
					$txtarr[] = 29;
					++$txtarrlen;
				} //end if
				//-- calculate codewords
				for($i = 0; $i < $txtarrlen; $i += 2) {
					$cw[] = (30 * $txtarr[$i]) + $txtarr[($i + 1)];
				} //end for
				//--
				break;
			case 901:
			case 924: // Byte Compaction mode latch
				//--
				while(($codelen = strlen($code)) > 0) {
					if($codelen > 6) {
						$rest = substr($code, 6);
						$code = substr($code, 0, 6);
						$sublen = 6;
					} else {
						$rest = '';
						$sublen = strlen($code);
					} //end if else
					if($sublen == 6) {
						$t = bcmul((string)ord($code[0]), '1099511627776');
						$t = bcadd($t, bcmul((string)ord($code[1]), '4294967296'));
						$t = bcadd($t, bcmul((string)ord($code[2]), '16777216'));
						$t = bcadd($t, bcmul((string)ord($code[3]), '65536'));
						$t = bcadd($t, bcmul((string)ord($code[4]), '256'));
						$t = bcadd($t, (string)ord($code[5]));
						// tmp array for the 6 bytes block
						$cw6 = array();
						do {
							$d = bcmod($t, '900');
							$t = bcdiv($t, '900');
							// prepend the value to the beginning of the array
							array_unshift($cw6, $d);
						} while($t != '0');
						// append the result array at the end
						$cw = array_merge($cw, $cw6);
					} else {
						for($i = 0; $i < $sublen; ++$i) {
							$cw[] = ord($code{$i});
						} //end for
					} //end if else
					$code = $rest;
				} //end while
				//--
				break;
			case 902:  // Numeric Compaction mode latch
				//--
				while(($codelen = strlen($code)) > 0) {
					if($codelen > 44) {
						$rest = substr($code, 44);
						$code = substr($code, 0, 44);
					} else {
						$rest = '';
					} //end if else
					$t = '1'.$code;
					do {
						$d = bcmod($t, '900');
						$t = bcdiv($t, '900');
						array_unshift($cw, $d);
					} while($t != '0');
					$code = $rest;
				} //end while
				//--
				break;
			case 913:  // Byte Compaction mode shift
				//--
				$cw[] = ord($code);
				//--
				break;
		} //end switch
		//--
		if($addmode) {
			array_unshift($cw, $mode); // add the compaction mode codeword at the beginning
		} //end if
		//--
		return $cw;
		//--
	} //END FUNCTION


} //END CLASS

//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>