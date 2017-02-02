<?php
// [LIB - SmartFramework / Plugins / Smart BarCodes 1D]
// (c) 2006-2017 unix-world.org - all rights reserved
// v.2.3.7.6 r.2017.02.02 / smart.framework.v.2.3

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// BarCodes 1D: Code128, Code93, Code39, RMS
// DEPENDS: SmartFramework
//======================================================

//--
if(!defined('SMART_FRAMEWORK_BARCODE_1D_MODE')) {
	die('A required INIT constant has not been defined: SMART_FRAMEWORK_BARCODE_1D_MODE');
} //end if
//--

// [REGEX-SAFE-OK]

//============================================================
// BarCode 1D:
//		* Code128: 	128-A / 128-B / 128-C
//		* Code93: 	US93
//		* Code39: 	39 / 39+C / 39E / 39E+C
//		* RMS4CC: 	CBC / KIX
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create DataMatrix ECC 200 barcode arrays.
// DataMatrix (ISO/IEC 16022:2006) is a 2-D bar code.
//============================================================
//
// These classes are derived from the following projects:
//
// "TcPDF" / Barcodes 1D / 1.0.027 / 20141020
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartBarcode1D - Generates 1D BarCodes: 128 B, 93 E+, 39 E, KIX.
 *
 * @usage  		static object: Class::method() - This class provides only STATIC methods
 *
 * @depends 	SmartFramework
 * @version 	v.160827
 * @package 	Components:BarCodes
 *
 */
final class SmartBarcode1D {

	// ::


	/**
	 * Function: Generate a 1D Barcode: 128 B, 93 E+, 39 E, KIX
	 *
	 * @param STRING 	$y_code 			The code for the BarCode Generator
	 * @param ENUM 		$y_type				The BarCode Type: 128 / 93 / 39 / KIX
	 * @param ENUM 		$y_format			The Barcode format: html, html-png, png, html-svg, svg
	 * @param INTEGER+ 	$y_size				The Scale-Size for Barcode (1..4)
	 * @param INTEGER+	$y_height			The Height in pixels for the Barcode
	 * @param HEXCOLOR	$y_color			The Hexadecimal Color for the Barcode Bars ; default is Black = #000000
	 * @param BOOLEAN	$y_display_text		If TRUE will display the Code below of BarCode Bars ; default is FALSE
	 * @param YES/NO	$y_cache			If YES will cache the Barcode to avoid on-the-fly generation ; default is set to NO
	 *
	 * @return MIXED	By Type Selection: 	HTML Code / PNG Image / SVG Code
	 *
	 */
	public static function getBarcode($y_code, $y_type, $y_format, $y_size, $y_height, $y_color='#000000', $y_display_text=false, $y_cache='no') {
		//--
		switch((string)$y_type) {
			case '128': // 128 B (Extended)
				$barcode_type = '128B';
				break;
			case '93': // 93 Extended +Checksum
				$barcode_type = '93E+';
				break;
			case '39': // 39 Extended
				$barcode_type = '39E';
				break;
			case 'KIX': // RMS KIX Variant (Extended) :: max 11 chars :: This needs a height that divides by 3
				$barcode_type = 'KIX';
				break;
			default:
				$barcode_type = '???';
				Smart::log_warning('ERROR: BarCodes1D - Invalid Type Selected for getBarcode');
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
				Smart::log_warning('ERROR: BarCodes1D - Invalid Mode Selected for getBarcode');
				return '';
		} //end switch
		//--

		//--
		if($y_display_text) {
			$barcode_show_text = 'TX';
		} else {
			$barcode_show_text = 'XX';
		} //end if else
		//--

		//--
		$memory_cache_url = 'memory://barcode-1d/'.$barcode_type.'/'.$barcode_format.'/'.$y_size.'/'.$y_height.'/'.$y_color.'/'.$barcode_show_text.'/'.$y_code;
		$realm = 'barcode-1d/';
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
			case '128B':
				$arr_barcode = (new SmartBarcode1D_128($y_code, 'B'))->getBarcodeArray();
				break;
			case '93E+':
				$arr_barcode = (new SmartBarcode1D_93($y_code, true, true))->getBarcodeArray();
				break;
			case '39E':
				$arr_barcode = (new SmartBarcode1D_39($y_code, true, false))->getBarcodeArray();
				break;
			case 'KIX':
				$arr_barcode = (new SmartBarcode1D_RMS4CC($y_code, 'KIX'))->getBarcodeArray();
				break;
			default:
				$arr_barcode = ''; // not to be an array for error detection
		} //end switch
		//--
		switch((string)$y_format) {
			case 'html':
				$out = '<!-- '.Smart::escape_html(strtoupper($barcode_type).' ('.$y_size.'/'.$y_height.'/'.$y_color.'/'.$barcode_show_text.') :: '.date('YmdHis')).' -->'.'<div title="'.Smart::escape_html($y_code).'">'.self::getBarcodeHTML($arr_barcode, $y_size, $y_height, $y_color, $y_display_text).'</div>'.'<!-- #END :: '.Smart::escape_html(strtoupper($barcode_type)).' -->';
				break;
			case 'html-png': // html img embedded png
				$out = '<!-- '.Smart::escape_html(strtoupper($barcode_type).' ('.$y_size.'/'.$y_height.'/'.$y_color.'/'.$barcode_show_text.') :: '.date('YmdHis')).' -->'.'<div title="'.Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLPNG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text).'</div>'.'<!-- #END :: '.Smart::escape_html(strtoupper($barcode_type)).' -->';
				break;
			case 'png': // raw png
				$out = self::getBarcodePNG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text); // needs header image/png on output
				break;
			case 'html-svg':
				$out = '<!-- '.Smart::escape_html(strtoupper($barcode_type).' ('.$y_size.'/'.$y_height.'/'.$y_color.'/'.$barcode_show_text.') :: '.date('YmdHis')).' -->'.'<div title="'.Smart::escape_html($y_code).'">'.self::getBarcodeEmbeddedHTMLSVG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text).'</div>'.'<!-- #END :: '.Smart::escape_html(strtoupper($barcode_type)).' -->';
				break;
			case 'svg':
				$out = self::getBarcodeSVG($arr_barcode, $y_size, $y_height, $y_color, $y_display_text); // needs header image/svg on output
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
	public static function getBarcodeHTML($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$microtime = microtime(true);
		//--
		$html = '';
		$html .= "\n".'<!-- Barcode1D / HTML -->';
		$html .= '<table border="0" cellspacing="0" cellpadding="0">';
		$html .= '<tr valign="top"><td align="center" style="font-size:1px; font-family="monospace">';
		$html .= '<table border="0" cellspacing="0" cellpadding="0" style="border-style:hidden; border-collapse:collapse;">';
		//-- print bars
		for($r=0; $r<$barcode_arr['maxh']; $r++) {
			//--
			$bh = round(($h / $barcode_arr['maxh']), 3);
			//--
			$html .= "\n".'<tr height="'.$bh.'" style="height:'.$bh.'px;">';
			//--
			for($c=0; $c<$barcode_arr['maxw']; $c++) {
				//--
				$bw = round(($barcode_arr['bcode'][$c]['w'] * $w), 3);
				//--
				if(($barcode_arr['bcode'][$c]['t']) AND ($r >= $barcode_arr['bcode'][$c]['p']) AND ($r < ($barcode_arr['bcode'][$c]['h'] + $barcode_arr['bcode'][$c]['p']))) {
					// draw a vertical bar
					$html .= '<td bgcolor="'.$color.'" width="'.$bw.'" height="'.$bh.'" style="font-size:1px;width:'.$bw.'px;height:'.$bh.'px;"></td>';
				} elseif($bw > 0) {
					$html .= '<td bgcolor="#FFFFFF" width="'.$bw.'" height="'.$bh.'" style="font-size:1px;width:'.$bw.'px;height:'.$bh.'px;"></td>';
				} //end if
				//--
			} //end for
			//--
			$html .= '</tr>';
			//--
		} //end for
		//--
		$html .= "\n".'</table>';
		$html .= '</td></tr>';
		if($display_text) {
			$html .= '<tr valign="top"><td align="center" style="font-size:10px; font-family="monospace">';
			$html .= '<font size="1" color="'.$color.'">'.Smart::escape_html(implode(' ', str_split(trim($barcode_arr['code'])))).'</font>';
			$html .= '</td></tr>';
		} //end if
		$html .= '</table>';
		$html .= '<!-- END :: Barcode1D ['.(microtime(true) - $microtime).'] -->'."\n";
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
	public static function getBarcodeEmbeddedHTMLSVG($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$microtime = microtime(true);
		//--
		$html = '';
		$html .= "\n".'<!-- Barcode1D / SVG -->';
		$html .= '<img src="data:image/svg+xml;base64,'.Smart::escape_html(base64_encode(self::getBarcodeSVG($barcode_arr, $w, $h, $color, $display_text))).'" alt="BarCode1D-SVG">';
		$html .= '<!-- END :: Barcode1D ['.(microtime(true) - $microtime).'] -->'."\n";
		//--
		return $html;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as SVG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodeSVG($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!is_array($barcode_arr)) {
			return '<svg width="100" height="10"><text x="0" y="10" fill="#FF0000" font-size="9" font-family="monospace">[ INVALID BARCODE ]</text></svg>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$svg = '';
		//--
		if($display_text) {
			$textheight = 11;
			$codetext = "\n".'<text x="'.round(round(($barcode_arr['maxw'] * $w), 3)/2).'" y="'.($h + $textheight - 1).'" fill="'.$color.'" text-anchor="middle" font-size="10" font-family="monospace">'.Smart::escape_html(implode(' ', str_split(trim((string)$barcode_arr['code'])))).'</text>';
		} else {
			$textheight = 0;
			$codetext = '';
		} //end if else
		//--
		$svg .= '<'.'?'.'xml version="1.0" encoding="UTF-8" standalone="no" '.'?'.'>'."\n";
		$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
		$svg .= '<svg width="'.round(($barcode_arr['maxw'] * $w), 3).'" height="'.($h + $textheight).'" version="1.1" xmlns="http://www.w3.org/2000/svg">'."\n";
		$svg .= "\t".'<desc>'.Smart::escape_html($barcode_arr['code']).'</desc>'."\n";
		$svg .= "\t".'<rect fill="#FFFFFF" x="0" y="0" width="'.round(($barcode_arr['maxw'] * $w), 3).'" height="'.($h + $textheight).'" />'."\n";
		$svg .= "\t".'<g id="bars" fill="'.$color.'" stroke="none">'."\n";
		//-- print bars
		$x = 0;
		//--
		foreach($barcode_arr['bcode'] as $k => $v) {
			//--
			$bw = round(($v['w'] * $w), 3);
			$bh = round(($v['h'] * $h / $barcode_arr['maxh']), 3);
			//--
			if($v['t']) {
				//--
				$y = round(($v['p'] * $h / $barcode_arr['maxh']), 3);
				//-- draw a vertical bar
				$svg .= "\t\t".'<rect x="'.$x.'" y="'.$y.'" width="'.$bw.'" height="'.$bh.'" />'."\n";
				//--
			} //end if
			//--
			$x += $bw;
			//--
		} //end foreach
		//--
		$svg .= "\t".'</g>'."\n";
		$svg .= $codetext;
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
	public static function getBarcodeEmbeddedHTMLPNG($barcode_arr, $w=2, $h=30, $color='#000000', $display_text=false) {
		//--
		if(!is_array($barcode_arr)) {
			return '<span style="background:#FAFAFA; color:#FF5500;"><font size="1">[ INVALID BARCODE ]</font></span>';
		} //end if
		$w = self::conformW($w);
		$h = self::conformH($h);
		//--
		$microtime = microtime(true);
		//--
		$html = '';
		$html .= "\n".'<!-- Barcode1D / PNG -->';
		$html .= '<img src="data:image/png;base64,'.Smart::escape_html(base64_encode(self::getBarcodePNG($barcode_arr, $w, $h, $color, $display_text))).'" alt="BarCode1D-PNG">';
		$html .= '<!-- END :: Barcode1D ['.(microtime(true) - $microtime).'] -->'."\n";
		//--
		return $html;
		//--
	} //END FUNCTION


	/**
	 * Function: Get BarCode as PNG
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	public static function getBarcodePNG($barcode_arr, $w=2, $h=30, $color=array(0,0,0), $display_text=false) {
		//--
		if(!is_array($color)) {
			$color = (string) $color;
			$color = trim(str_replace('#', '', $color));
			$color = array(hexdec(substr($color, 0, 2)), hexdec(substr($color, 2, 2)), hexdec(substr($color, 4, 2)));
		} //end if
		//--
		if(!is_array($barcode_arr)) {
			//--
			$width = 100;
			$height = 10;
			//--
			$png = @imagecreate($width, $height);
			$bgcol = @imagecolorallocate($png, 250, 250, 250);
			$fgcol = @imagecolorallocate($png, 255, 0, 0);
			@imagestring($png, 1, 1, 1, "[ INVALID BARCODE ]", $fgcol);
			//--
		} else {
			//--
			$w = self::conformW($w);
			$h = self::conformH($h);
			//-- calculate image size
			$width = ($barcode_arr['maxw'] * $w);
			$height = $h;
			//--
			//$codetext = implode(' ', str_split(trim((string)$barcode_arr['code'])));
			$codetext = trim($barcode_arr['code']);
			$fontnum = 2;
			$fontwidth = @imagefontwidth($fontnum);
			$fontheight = @imagefontheight($fontnum);
			$centerloc_text = (int) (($width) / 2) - (($fontwidth * strlen($codetext)) / 2);
			if($display_text) {
				$textheight = $fontheight;
			} else {
				$textheight = 0;
			} //end if else
			//--
			$png = @imagecreate($width, ($height + $textheight));
			$bgcol = @imagecolorallocate($png, 255, 255, 255);
			$fgcol = @imagecolorallocate($png, $color[0], $color[1], $color[2]);
			//-- print bars
			$x = 0;
			//-- for each row
			foreach($barcode_arr['bcode'] as $k => $v) {
				//--
				$bw = round(($v['w'] * $w), 3);
				$bh = round(($v['h'] * $h / $barcode_arr['maxh']), 3);
				//--
				if($v['t']) {
					//--
					$y = round(($v['p'] * $h / $barcode_arr['maxh']), 3);
					//--
					@imagefilledrectangle($png, $x, $y, ($x + $bw - 1), ($y + $bh - 1), $fgcol); // draw a vertical bar
					//--
				} //end if
				//--
				$x += $bw;
				//--
			} //end foreach
			//--
			if($display_text) {
				@imagestring($png, $fontnum, $centerloc_text, $height, $codetext, $fgcol);
			} //end if
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


	private static function conformW($w) {
		//-- w must be between 1 and 16
		$w = (int) $w;
		if($w < 1) {
			$w = 1;
		} //end if
		if($w > 16) {
			$w = 16;
		} //end if
		//--
		return $w;
		//--
	} //END FUNCTION


	private static function conformH($h) {
		//-- h must divide by 3
		$h = (int) $h;
		if($h < 9) {
			$h = 9;
		} //end if
		if($h > 243) {
			$h = 243;
		} //end if
		//--
		return $h;
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
// BarCode 1D:	Code128 128-A / 128-B / 128-C
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create Code128 1D barcodes.
// Very capable code, excellent density, high reliability
// Wide use world-wide
// TECHNICAL DATA / FEATURES OF Code128:
// * Encodable Character Set A: 	0..9 A..Z      SPACE ! " # $ % & \ ' ( ) * + , - . / : ; < = > ? @ [ ] ^ _
// * Encodable Character Set B: 	0..9 A..Z a..z SPACE ! " # $ % & \ ' ( ) * + , - . / : ; < = > ? @ [ ] ^ _ ` { | } ~
// * Encodable Character Set C:		0..9
// * Code Type: 					Linear, 1 type height bars
// * Error Correction: 				Checksum
// * Maximum Data Characters: 		48
//============================================================
//
// These class is derived from the following projects:
//
// "TcPDF" / Barcodes 1D / 1.0.027 / 20141020
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class Smart BarCode 1D 128
 *
 * @access 		private
 * @internal
 *
 */
final class SmartBarcode1D_128 {

	// ->
	// v.160827

	private $code = '';
	private $mode = '';


	public function __construct($code, $type='B') {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$this->code = (string) $code; // force string
		//--
		switch((string)$type) {
			case 'C':
				$this->mode = 'C';
				break;
			case 'A':
				$this->mode = 'A';
				break;
			case 'B':
			default:
				$this->mode = 'B';
		} //end switch
		//--
	} //END FUNCTION


	/**
	 * C128 barcodes.
	 * @param $code (string) code to represent.
	 * @param $type (string) barcode type: A, B, C or empty for automatic switch (AUTO mode)
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() { // barcode_c128()
		//--
		$code = $this->code;
		$type = $this->mode;
		//--
		$bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
		//--
		$chr = array(
			'212222', /* 00 */
			'222122', /* 01 */
			'222221', /* 02 */
			'121223', /* 03 */
			'121322', /* 04 */
			'131222', /* 05 */
			'122213', /* 06 */
			'122312', /* 07 */
			'132212', /* 08 */
			'221213', /* 09 */
			'221312', /* 10 */
			'231212', /* 11 */
			'112232', /* 12 */
			'122132', /* 13 */
			'122231', /* 14 */
			'113222', /* 15 */
			'123122', /* 16 */
			'123221', /* 17 */
			'223211', /* 18 */
			'221132', /* 19 */
			'221231', /* 20 */
			'213212', /* 21 */
			'223112', /* 22 */
			'312131', /* 23 */
			'311222', /* 24 */
			'321122', /* 25 */
			'321221', /* 26 */
			'312212', /* 27 */
			'322112', /* 28 */
			'322211', /* 29 */
			'212123', /* 30 */
			'212321', /* 31 */
			'232121', /* 32 */
			'111323', /* 33 */
			'131123', /* 34 */
			'131321', /* 35 */
			'112313', /* 36 */
			'132113', /* 37 */
			'132311', /* 38 */
			'211313', /* 39 */
			'231113', /* 40 */
			'231311', /* 41 */
			'112133', /* 42 */
			'112331', /* 43 */
			'132131', /* 44 */
			'113123', /* 45 */
			'113321', /* 46 */
			'133121', /* 47 */
			'313121', /* 48 */
			'211331', /* 49 */
			'231131', /* 50 */
			'213113', /* 51 */
			'213311', /* 52 */
			'213131', /* 53 */
			'311123', /* 54 */
			'311321', /* 55 */
			'331121', /* 56 */
			'312113', /* 57 */
			'312311', /* 58 */
			'332111', /* 59 */
			'314111', /* 60 */
			'221411', /* 61 */
			'431111', /* 62 */
			'111224', /* 63 */
			'111422', /* 64 */
			'121124', /* 65 */
			'121421', /* 66 */
			'141122', /* 67 */
			'141221', /* 68 */
			'112214', /* 69 */
			'112412', /* 70 */
			'122114', /* 71 */
			'122411', /* 72 */
			'142112', /* 73 */
			'142211', /* 74 */
			'241211', /* 75 */
			'221114', /* 76 */
			'413111', /* 77 */
			'241112', /* 78 */
			'134111', /* 79 */
			'111242', /* 80 */
			'121142', /* 81 */
			'121241', /* 82 */
			'114212', /* 83 */
			'124112', /* 84 */
			'124211', /* 85 */
			'411212', /* 86 */
			'421112', /* 87 */
			'421211', /* 88 */
			'212141', /* 89 */
			'214121', /* 90 */
			'412121', /* 91 */
			'111143', /* 92 */
			'111341', /* 93 */
			'131141', /* 94 */
			'114113', /* 95 */
			'114311', /* 96 */
			'411113', /* 97 */
			'411311', /* 98 */
			'113141', /* 99 */
			'114131', /* 100 */
			'311141', /* 101 */
			'411131', /* 102 */
			'211412', /* 103 START A */
			'211214', /* 104 START B */
			'211232', /* 105 START C */
			'233111', /* STOP */
			'200000'  /* END */
		);
		//-- ASCII characters for code A (ASCII 00 - 95)
		$keys_a = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
		$keys_a .= chr(0).chr(1).chr(2).chr(3).chr(4).chr(5).chr(6).chr(7).chr(8).chr(9);
		$keys_a .= chr(10).chr(11).chr(12).chr(13).chr(14).chr(15).chr(16).chr(17).chr(18).chr(19);
		$keys_a .= chr(20).chr(21).chr(22).chr(23).chr(24).chr(25).chr(26).chr(27).chr(28).chr(29);
		$keys_a .= chr(30).chr(31);
		//-- ASCII characters for code B (ASCII 32 - 127)
		$keys_b = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~'.chr(127);
		//-- special codes
		$fnc_a = array(241 => 102, 242 => 97, 243 => 96, 244 => 101);
		$fnc_b = array(241 => 102, 242 => 97, 243 => 96, 244 => 100);
		//-- array of symbols
		$code_data = array();
		//-- length of the code
		$len = strlen($code);
		//--
		switch(strtoupper($type)) {
			case 'A':  // MODE A
				$startid = 103;
				for($i = 0; $i < $len; ++$i) {
					$char = $code{$i};
					$char_id = ord($char);
					if(($char_id >= 241) AND ($char_id <= 244)) {
						$code_data[] = $fnc_a[$char_id];
					} elseif(($char_id >= 0) AND ($char_id <= 95)) {
						$code_data[] = strpos($keys_a, $char);
					} else {
						return false;
					} //end if else
				} //end for
				break;
			case 'C': // MODE C
				$startid = 105;
				if(ord($code[0]) == 241) {
					$code_data[] = 102;
					$code = substr($code, 1);
					--$len;
				} //end if
				if(($len % 2) != 0) {
					// the length must be even
					return false;
				} //end if
				for($i = 0; $i < $len; $i+=2) {
					$chrnum = $code{$i}.$code{$i+1};
					if(preg_match('/([0-9]{2})/', (string)$chrnum) > 0) {
						$code_data[] = intval($chrnum);
					} else {
						return false;
					} //end if else
				} //end for
				break;
			case 'B':  // MODE B
			default:
				$startid = 104;
				for($i = 0; $i < $len; ++$i) {
					$char = $code{$i};
					$char_id = ord($char);
					if(($char_id >= 241) AND ($char_id <= 244)) {
						$code_data[] = $fnc_b[$char_id];
					} elseif(($char_id >= 32) AND ($char_id <= 127)) {
						$code_data[] = strpos($keys_b, $char);
					} else {
						return false;
					} //end if else
				} //end for
		} //end switch
		//-- calculate check character
		$sum = $startid;
		foreach($code_data as $key => $val) {
			$sum += ($val * ($key + 1));
		} //end foreach
		//-- add check character
		$code_data[] = ($sum % 103);
		//-- add stop sequence
		$code_data[] = 106;
		$code_data[] = 107;
		//-- add start code at the beginning
		array_unshift($code_data, $startid);
		//--
		foreach($code_data as $u => $val) {
			$seq = $chr[$val];
			for($j = 0; $j < 6; ++$j) {
				if(($j % 2) == 0) {
					$t = true; // bar
				} else {
					$t = false; // space
				} //end if else
				$w = $seq{$j};
				$bararray['bcode'][] = array('t' => $t, 'w' => $w, 'h' => 1, 'p' => 0);
				$bararray['maxw'] += $w;
			} //end for
		} //end foreach
		//--
		return $bararray;
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
// BarCode 1D:	Code93 (USS-93)
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create Code93 1D barcodes.
// Code similar to Code 39 or 128, but more compact, high density
// TECHNICAL DATA / FEATURES OF Code93:
// * Encodable Character Set: 			0..9 A..Z - . $ / + % SPACE
// * Encodable Character Set Extended: 	0..9 A..Z a..z - . $ / + % SPACE @ # ! ? : ; , = < > " ' & ( ) [ ] \ ^ _ ` { } | ~
// * Code Type: 						Linear, 1 type height bars
// * Error Correction: 					Checksum
// * Maximum Data Characters: 			48
//============================================================
//
// These class is derived from the following projects:
//
// "TcPDF" / Barcodes 1D / 1.0.027 / 20141020
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class Smart BarCode 1D 93
 *
 * @access 		private
 * @internal
 *
 */
final class SmartBarcode1D_93 {

	// ->
	// v.160827

	private $code = '';
	private $extended = true;
	private $checksum = true;


	public function __construct($code, $extended=true, $checksum=true) {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$this->code = (string) $code; // force string
		//--
		$this->extended = true;
		//--
		$this->checksum = true;
		//--
	} //END FUNCTION


	/**
	 * CODE 93 - USS-93
	 * @param $code (string) code to represent.
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() {
		//--
		$code = $this->code;
		//--
		$bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
		//--
		$chr[48] = '131112'; // 0
		$chr[49] = '111213'; // 1
		$chr[50] = '111312'; // 2
		$chr[51] = '111411'; // 3
		$chr[52] = '121113'; // 4
		$chr[53] = '121212'; // 5
		$chr[54] = '121311'; // 6
		$chr[55] = '111114'; // 7
		$chr[56] = '131211'; // 8
		$chr[57] = '141111'; // 9
		$chr[65] = '211113'; // A
		$chr[66] = '211212'; // B
		$chr[67] = '211311'; // C
		$chr[68] = '221112'; // D
		$chr[69] = '221211'; // E
		$chr[70] = '231111'; // F
		$chr[71] = '112113'; // G
		$chr[72] = '112212'; // H
		$chr[73] = '112311'; // I
		$chr[74] = '122112'; // J
		$chr[75] = '132111'; // K
		$chr[76] = '111123'; // L
		$chr[77] = '111222'; // M
		$chr[78] = '111321'; // N
		$chr[79] = '121122'; // O
		$chr[80] = '131121'; // P
		$chr[81] = '212112'; // Q
		$chr[82] = '212211'; // R
		$chr[83] = '211122'; // S
		$chr[84] = '211221'; // T
		$chr[85] = '221121'; // U
		$chr[86] = '222111'; // V
		$chr[87] = '112122'; // W
		$chr[88] = '112221'; // X
		$chr[89] = '122121'; // Y
		$chr[90] = '123111'; // Z
		$chr[45] = '121131'; // -
		$chr[46] = '311112'; // .
		$chr[32] = '311211'; //
		$chr[36] = '321111'; // $
		$chr[47] = '112131'; // /
		$chr[43] = '113121'; // +
		$chr[37] = '211131'; // %
		$chr[128] = '121221'; // ($)
		$chr[129] = '311121'; // (/)
		$chr[130] = '122211'; // (+)
		$chr[131] = '312111'; // (%)
		$chr[42] = '111141'; // start-stop
		//--
		//$code = strtoupper($code); // this is useless in the extended mode
		//--
		$encode = array(
			chr(0) => chr(131).'U', chr(1) => chr(128).'A', chr(2) => chr(128).'B', chr(3) => chr(128).'C',
			chr(4) => chr(128).'D', chr(5) => chr(128).'E', chr(6) => chr(128).'F', chr(7) => chr(128).'G',
			chr(8) => chr(128).'H', chr(9) => chr(128).'I', chr(10) => chr(128).'J', chr(11) => chr(128).'K', // chr(11) => '£K' // fix by unixman
			chr(12) => chr(128).'L', chr(13) => chr(128).'M', chr(14) => chr(128).'N', chr(15) => chr(128).'O',
			chr(16) => chr(128).'P', chr(17) => chr(128).'Q', chr(18) => chr(128).'R', chr(19) => chr(128).'S',
			chr(20) => chr(128).'T', chr(21) => chr(128).'U', chr(22) => chr(128).'V', chr(23) => chr(128).'W',
			chr(24) => chr(128).'X', chr(25) => chr(128).'Y', chr(26) => chr(128).'Z', chr(27) => chr(131).'A',
			chr(28) => chr(131).'B', chr(29) => chr(131).'C', chr(30) => chr(131).'D', chr(31) => chr(131).'E',
			chr(32) => ' ', chr(33) => chr(129).'A', chr(34) => chr(129).'B', chr(35) => chr(129).'C',
			chr(36) => chr(129).'D', chr(37) => chr(129).'E', chr(38) => chr(129).'F', chr(39) => chr(129).'G',
			chr(40) => chr(129).'H', chr(41) => chr(129).'I', chr(42) => chr(129).'J', chr(43) => chr(129).'K',
			chr(44) => chr(129).'L', chr(45) => '-', chr(46) => '.', chr(47) => chr(129).'O',
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
			chr(56) => '8', chr(57) => '9', chr(58) => chr(129).'Z', chr(59) => chr(131).'F',
			chr(60) => chr(131).'G', chr(61) => chr(131).'H', chr(62) => chr(131).'I', chr(63) => chr(131).'J',
			chr(64) => chr(131).'V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => chr(131).'K',
			chr(92) => chr(131).'L', chr(93) => chr(131).'M', chr(94) => chr(131).'N', chr(95) => chr(131).'O',
			chr(96) => chr(131).'W', chr(97) => chr(130).'A', chr(98) => chr(130).'B', chr(99) => chr(130).'C',
			chr(100) => chr(130).'D', chr(101) => chr(130).'E', chr(102) => chr(130).'F', chr(103) => chr(130).'G',
			chr(104) => chr(130).'H', chr(105) => chr(130).'I', chr(106) => chr(130).'J', chr(107) => chr(130).'K',
			chr(108) => chr(130).'L', chr(109) => chr(130).'M', chr(110) => chr(130).'N', chr(111) => chr(130).'O',
			chr(112) => chr(130).'P', chr(113) => chr(130).'Q', chr(114) => chr(130).'R', chr(115) => chr(130).'S',
			chr(116) => chr(130).'T', chr(117) => chr(130).'U', chr(118) => chr(130).'V', chr(119) => chr(130).'W',
			chr(120) => chr(130).'X', chr(121) => chr(130).'Y', chr(122) => chr(130).'Z', chr(123) => chr(131).'P',
			chr(124) => chr(131).'Q', chr(125) => chr(131).'R', chr(126) => chr(131).'S', chr(127) => chr(131).'T');
		//--
		$code_ext = '';
		$clen = strlen($code);
		//--
		for($i = 0 ; $i < $clen; ++$i) {
			if(ord($code{$i}) > 127) {
				return false;
			} //end if
			$code_ext .= $encode[$code{$i}];
		} //end for
		//-- checksum
		$code_ext .= $this->checksum($code_ext);
		//--
		$code = '*'.$code_ext.'*'; // add start and stop chars as: *code*
		//--
		$k = 0;
		$clen = strlen($code);
		//--
		for($i = 0; $i < $clen; ++$i) {
			//--
			$char = ord($code{$i});
			//--
			if(!isset($chr[$char])) {
				return false; // invalid character
			} //end if
			//--
			for($j = 0; $j < 6; ++$j) {
				//--
				if(($j % 2) == 0) {
					$t = true; // bar
				} else {
					$t = false; // space
				} //end if else
				//--
				$w = $chr[$char]{$j};
				$bararray['bcode'][$k] = array('t' => $t, 'w' => $w, 'h' => 1, 'p' => 0);
				$bararray['maxw'] += $w;
				//--
				++$k;
				//--
			} //end for
			//--
		} //end for
		//--
		$bararray['bcode'][$k] = array('t' => true, 'w' => 1, 'h' => 1, 'p' => 0);
		$bararray['maxw'] += 1;
		//--
		++$k;
		//--
		return $bararray;
		//--
	} //END FUNCTION


	/**
	 * Calculate CODE 93 checksum (modulo 47).
	 * @param $code (string) code to represent.
	 * @return string checksum code.
	 * @private
	 */
	private function checksum($code) {
		//--
		$chars = array(
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%',
			'<', '=', '>', '?');
		//-- translate special characters
		$code = strtr($code, chr(128).chr(131).chr(129).chr(130), '<=>?');
		$len = strlen($code);
		//-- calculate check digit C
		$p = 1;
		$check = 0;
		for($i = ($len - 1); $i >= 0; --$i) {
			$k = array_keys($chars, $code{$i});
			$check += ($k[0] * $p);
			++$p;
			if($p > 20) {
				$p = 1;
			} //end if
		} //end for
		//--
		$check %= 47;
		$c = $chars[$check];
		$code .= $c;
		//-- calculate check digit K
		$p = 1;
		$check = 0;
		for($i = $len; $i >= 0; --$i) {
			$k = array_keys($chars, $code{$i});
			$check += ($k[0] * $p);
			++$p;
			if($p > 15) {
				$p = 1;
			} //end if
		} //end for
		//--
		$check %= 47;
		$k = $chars[$check];
		$checksum = $c.$k;
		//-- restore special characters
		$checksum = strtr($checksum, '<=>?', chr(128).chr(131).chr(129).chr(130));
		//--
		return $checksum;
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
// BarCode 1D:	Code39 / Code39+ / Code39E / Code39E+
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create Code39* 1D barcodes.
// Low density, General-purpose code in very wide use world-wide
// TECHNICAL DATA / FEATURES OF Code39:
// * Encodable Character Set: 			0..9 A..Z - . $ / + % SPACE
// * Encodable Character Set Extended: 	0..9 A..Z a..z - . $ / + % SPACE @ # ! ? : ; , = < > " ' & ( ) [ ] \ ^ _ ` { } | ~
// * Code Type: 						Linear, 1 type height bars
// * Error Correction: 					Checksum
// * Maximum Data Characters: 			48
//============================================================
//
// These class is derived from the following projects:
//
// "TcPDF" / Barcodes 1D / 1.0.027 / 20141020
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class Smart BarCode 1D 39
 *
 * @access 		private
 * @internal
 *
 */
final class SmartBarcode1D_39 {

	// ->
	// v.160827

	private $code = '';
	private $extended = true;
	private $checksum = false;


	// currently could not find a barcode reader to support code39 checksum so is disabled by default (this is a very rare used option in practice)
	public function __construct($code, $extended=true, $checksum=false) {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$this->code = (string) $code; // force string
		//--
		if($extended !== false) {
			$this->extended = true;
		} else {
			$this->extended = false;
		} //end if
		//--
		if($checksum === true) {
			$this->checksum = true;
		} else {
			$this->checksum = false;
		} //end if else
		//--
	} //END FUNCTION


	/**
	 * CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
	 * @param $code (string) code to represent.
	 * @param $checksum (boolean) if true add a checksum to the code.
	 * @param $extended (boolean) if true uses the extended mode.
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() {
		//--
		$code = $this->code;
		$checksum = $this->checksum;
		$extended = $this->extended;
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
		//--
		$chr['0'] = '111331311';
		$chr['1'] = '311311113';
		$chr['2'] = '113311113';
		$chr['3'] = '313311111';
		$chr['4'] = '111331113';
		$chr['5'] = '311331111';
		$chr['6'] = '113331111';
		$chr['7'] = '111311313';
		$chr['8'] = '311311311';
		$chr['9'] = '113311311';
		$chr['A'] = '311113113';
		$chr['B'] = '113113113';
		$chr['C'] = '313113111';
		$chr['D'] = '111133113';
		$chr['E'] = '311133111';
		$chr['F'] = '113133111';
		$chr['G'] = '111113313';
		$chr['H'] = '311113311';
		$chr['I'] = '113113311';
		$chr['J'] = '111133311';
		$chr['K'] = '311111133';
		$chr['L'] = '113111133';
		$chr['M'] = '313111131';
		$chr['N'] = '111131133';
		$chr['O'] = '311131131';
		$chr['P'] = '113131131';
		$chr['Q'] = '111111333';
		$chr['R'] = '311111331';
		$chr['S'] = '113111331';
		$chr['T'] = '111131331';
		$chr['U'] = '331111113';
		$chr['V'] = '133111113';
		$chr['W'] = '333111111';
		$chr['X'] = '131131113';
		$chr['Y'] = '331131111';
		$chr['Z'] = '133131111';
		$chr['-'] = '131111313';
		$chr['.'] = '331111311';
		$chr[' '] = '133111311';
		$chr['$'] = '131313111';
		$chr['/'] = '131311131';
		$chr['+'] = '131113131';
		$chr['%'] = '111313131';
		//--
		$chr['*'] = '131131311'; // this is a special character (start/stop) and should not be used in the code
		//--
		if($extended) {
			$code = $this->encode_extended($code); // extended mode
		} else {
			$code = strtoupper($code);
		} //end if
		//--
		if($code === false) {
			return false;
		} //end if
		//--
		if($checksum) {
			$code .= $this->checksum($code); // checksum
		} //end if
		//--
		$code = '*'.$code.'*'; // add start and stop chars as: *code*
		//--
		$k = 0;
		$clen = strlen($code);
		//--
		for($i = 0; $i < $clen; ++$i) {
			//--
			$char = $code{$i};
			//--
			if(!isset($chr[$char])) {
				return false; // invalid character
			} //end if
			//--
			for($j = 0; $j < 9; ++$j) {
				//--
				if(($j % 2) == 0) {
					$t = true; // bar
				} else {
					$t = false; // space
				} //end if else
				//--
				$w = $chr[$char]{$j};
				$bararray['bcode'][$k] = array('t' => $t, 'w' => $w, 'h' => 1, 'p' => 0);
				$bararray['maxw'] += $w;
				//--
				++$k;
				//--
			} //end for
			//-- intercharacter gap
			$bararray['bcode'][$k] = array('t' => false, 'w' => 1, 'h' => 1, 'p' => 0);
			$bararray['maxw'] += 1;
			//--
			++$k;
			//--
		} //end for
		//--
		return $bararray;
		//--
	} //END FUNCTION


	/**
	 * Calculate CODE 39 checksum (modulo 43).
	 * @param $code (string) code to represent.
	 * @return char checksum.
	 * @private
	 */
	private function checksum($code) {
		//--
		$chars = array(
			'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
			'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K',
			'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V',
			'W', 'X', 'Y', 'Z', '-', '.', ' ', '$', '/', '+', '%');
		//--
		$sum = 0;
		$clen = strlen($code);
		//--
		for($i = 0 ; $i < $clen; ++$i) {
			$k = array_keys($chars, $code{$i});
			$sum += $k[0];
		} //end for
		//--
		$j = ($sum % 43);
		//--
		return $chars[$j];
		//--
	} //END FUNCTION


	/**
	 * Encode a string to be used for CODE 39 Extended mode.
	 * @param $code (string) code to represent.
	 * @return encoded string.
	 * @private
	 */
	private function encode_extended($code) {
		//--
		$encode = array(
			chr(0) => '%U', chr(1) => '$A', chr(2) => '$B', chr(3) => '$C',
			chr(4) => '$D', chr(5) => '$E', chr(6) => '$F', chr(7) => '$G',
			chr(8) => '$H', chr(9) => '$I', chr(10) => '$J', chr(11) => '$K', // chr(11) => '£K' // fix by unixman
			chr(12) => '$L', chr(13) => '$M', chr(14) => '$N', chr(15) => '$O',
			chr(16) => '$P', chr(17) => '$Q', chr(18) => '$R', chr(19) => '$S',
			chr(20) => '$T', chr(21) => '$U', chr(22) => '$V', chr(23) => '$W',
			chr(24) => '$X', chr(25) => '$Y', chr(26) => '$Z', chr(27) => '%A',
			chr(28) => '%B', chr(29) => '%C', chr(30) => '%D', chr(31) => '%E',
			chr(32) => ' ', chr(33) => '/A', chr(34) => '/B', chr(35) => '/C',
			chr(36) => '/D', chr(37) => '/E', chr(38) => '/F', chr(39) => '/G',
			chr(40) => '/H', chr(41) => '/I', chr(42) => '/J', chr(43) => '/K',
			chr(44) => '/L', chr(45) => '-', chr(46) => '.', chr(47) => '/O',
			chr(48) => '0', chr(49) => '1', chr(50) => '2', chr(51) => '3',
			chr(52) => '4', chr(53) => '5', chr(54) => '6', chr(55) => '7',
			chr(56) => '8', chr(57) => '9', chr(58) => '/Z', chr(59) => '%F',
			chr(60) => '%G', chr(61) => '%H', chr(62) => '%I', chr(63) => '%J',
			chr(64) => '%V', chr(65) => 'A', chr(66) => 'B', chr(67) => 'C',
			chr(68) => 'D', chr(69) => 'E', chr(70) => 'F', chr(71) => 'G',
			chr(72) => 'H', chr(73) => 'I', chr(74) => 'J', chr(75) => 'K',
			chr(76) => 'L', chr(77) => 'M', chr(78) => 'N', chr(79) => 'O',
			chr(80) => 'P', chr(81) => 'Q', chr(82) => 'R', chr(83) => 'S',
			chr(84) => 'T', chr(85) => 'U', chr(86) => 'V', chr(87) => 'W',
			chr(88) => 'X', chr(89) => 'Y', chr(90) => 'Z', chr(91) => '%K',
			chr(92) => '%L', chr(93) => '%M', chr(94) => '%N', chr(95) => '%O',
			chr(96) => '%W', chr(97) => '+A', chr(98) => '+B', chr(99) => '+C',
			chr(100) => '+D', chr(101) => '+E', chr(102) => '+F', chr(103) => '+G',
			chr(104) => '+H', chr(105) => '+I', chr(106) => '+J', chr(107) => '+K',
			chr(108) => '+L', chr(109) => '+M', chr(110) => '+N', chr(111) => '+O',
			chr(112) => '+P', chr(113) => '+Q', chr(114) => '+R', chr(115) => '+S',
			chr(116) => '+T', chr(117) => '+U', chr(118) => '+V', chr(119) => '+W',
			chr(120) => '+X', chr(121) => '+Y', chr(122) => '+Z', chr(123) => '%P',
			chr(124) => '%Q', chr(125) => '%R', chr(126) => '%S', chr(127) => '%T');
		//--
		$code_ext = '';
		$clen = strlen($code);
		//--
		for($i = 0 ; $i < $clen; ++$i) {
			if(ord($code{$i}) > 127) {
				return false;
			} //end if
			$code_ext .= $encode[$code{$i}];
		} //end for
		//--
		return $code_ext;
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
// BarCode 1D:	RMS4CC (CBC / KIX)
// License: BSD
// (c) 2015 unix-world.org
//============================================================
// Class to create RMS 1D barcodes.
// RMS4CC (Royal Mail 4-state Customer Code)
// * CBC (Customer Bar Code)
// * KIX (Klant Index - Customer Index)
// RM4SCC is the name of the barcode symbology used by the Royal Mail but also other uses.
// TECHNICAL DATA / FEATURES OF RMS4CC:
// * Encodable Character Set: 		0..9 A..Z
// * Code Type: 					Linear, 3 types height bars
// * Error Correction: 				Checksum
// * Maximum Data Characters: 		CBC: 8 ; KIX: 11
//============================================================
//
// These class is derived from the following projects:
//
// "TcPDF" / Barcodes 1D / 1.0.027 / 20141020
// License: GNU-LGPL v3
// Copyright (C) 2010-2014  Nicola Asuni - Tecnick.com LTD
//
//============================================================


/**
 * Class Smart BarCode 1D RMS4CC
 *
 * @access 		private
 * @internal
 *
 */
final class SmartBarcode1D_RMS4CC {

	// ->
	// v.160827

	private $code = '';
	private $mode = '';


	public function __construct($code, $type='KIX') {
		//--
		if((is_null($code)) OR ($code == '\0') OR ((string)$code == '')) {
			return false;
		} //end if
		//--
		$this->code = (string) $code; // force string
		//--
		switch((string)$type) {
			case 'CBC':
				$this->mode = 'CBC'; // Customer Bar Code
				break;
			case 'KIX':
			default:
				$this->mode = 'KIX'; // Klant Index (Customer Index)
				break;
		} //end switch
		//--
	} //END FUNCTION


	/**
	 * RMS4CC - CBC - KIX
	 * @param $code (string) code to print
	 * @param $kix (boolean) if true prints the KIX variation (doesn't use the start and end symbols, and the checksum) - in this case the house number must be sufficed with an X and placed at the end of the code.
	 * @return array barcode representation.
	 */
	public function getBarcodeArray() { // barcode_rms4cc()
		//--
		$code = $this->code;
		//--
		$bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 3, 'bcode' => array());
		//--
		if((string)$this->mode == 'KIX') {
			$kix = true;
		} else {
			$kix = false;
		} //end if else
		//--
		$notkix = !$kix;
		// bar mode
		// 1 = pos 1, length 2
		// 2 = pos 1, length 3
		// 3 = pos 2, length 1
		// 4 = pos 2, length 2
		$barmode = array(
			'0' => array(3,3,2,2),
			'1' => array(3,4,1,2),
			'2' => array(3,4,2,1),
			'3' => array(4,3,1,2),
			'4' => array(4,3,2,1),
			'5' => array(4,4,1,1),
			'6' => array(3,1,4,2),
			'7' => array(3,2,3,2),
			'8' => array(3,2,4,1),
			'9' => array(4,1,3,2),
			'A' => array(4,1,4,1),
			'B' => array(4,2,3,1),
			'C' => array(3,1,2,4),
			'D' => array(3,2,1,4),
			'E' => array(3,2,2,3),
			'F' => array(4,1,1,4),
			'G' => array(4,1,2,3),
			'H' => array(4,2,1,3),
			'I' => array(1,3,4,2),
			'J' => array(1,4,3,2),
			'K' => array(1,4,4,1),
			'L' => array(2,3,3,2),
			'M' => array(2,3,4,1),
			'N' => array(2,4,3,1),
			'O' => array(1,3,2,4),
			'P' => array(1,4,1,4),
			'Q' => array(1,4,2,3),
			'R' => array(2,3,1,4),
			'S' => array(2,3,2,3),
			'T' => array(2,4,1,3),
			'U' => array(1,1,4,4),
			'V' => array(1,2,3,4),
			'W' => array(1,2,4,3),
			'X' => array(2,1,3,4),
			'Y' => array(2,1,4,3),
			'Z' => array(2,2,3,3)
		);
		//--
		$code = strtoupper($code);
		$len = strlen($code);
		//--
		if($notkix) {
			//-- table for checksum calculation (row,col)
			$checktable = array(
				'0' => array(1,1),
				'1' => array(1,2),
				'2' => array(1,3),
				'3' => array(1,4),
				'4' => array(1,5),
				'5' => array(1,0),
				'6' => array(2,1),
				'7' => array(2,2),
				'8' => array(2,3),
				'9' => array(2,4),
				'A' => array(2,5),
				'B' => array(2,0),
				'C' => array(3,1),
				'D' => array(3,2),
				'E' => array(3,3),
				'F' => array(3,4),
				'G' => array(3,5),
				'H' => array(3,0),
				'I' => array(4,1),
				'J' => array(4,2),
				'K' => array(4,3),
				'L' => array(4,4),
				'M' => array(4,5),
				'N' => array(4,0),
				'O' => array(5,1),
				'P' => array(5,2),
				'Q' => array(5,3),
				'R' => array(5,4),
				'S' => array(5,5),
				'T' => array(5,0),
				'U' => array(0,1),
				'V' => array(0,2),
				'W' => array(0,3),
				'X' => array(0,4),
				'Y' => array(0,5),
				'Z' => array(0,0)
			);
			//--
			$row = 0;
			$col = 0;
			//--
			for($i = 0; $i < $len; ++$i) {
				$row += $checktable[$code{$i}][0];
				$col += $checktable[$code{$i}][1];
			} //end for
			//--
			$row %= 6;
			$col %= 6;
			$chk = array_keys($checktable, array($row,$col));
			$code .= $chk[0];
			++$len;
			//--
		} //end if
		//--
		$k = 0;
		//--
		if($notkix) {
			//-- start bar
			$bararray['bcode'][$k++] = array('t' => 1, 'w' => 1, 'h' => 2, 'p' => 0);
			$bararray['bcode'][$k++] = array('t' => 0, 'w' => 1, 'h' => 2, 'p' => 0);
			$bararray['maxw'] += 2;
			//--
		} //end if
		//--
		for($i = 0; $i < $len; ++$i) {
			//--
			for($j = 0; $j < 4; ++$j) {
				//--
				switch($barmode[$code{$i}][$j]) {
					case 1:
						$p = 0;
						$h = 2;
						break;
					case 2:
						$p = 0;
						$h = 3;
						break;
					case 3:
						$p = 1;
						$h = 1;
						break;
					case 4:
						$p = 1;
						$h = 2;
						break;
				} //end switch
				//--
				$bararray['bcode'][$k++] = array('t' => 1, 'w' => 1, 'h' => $h, 'p' => $p);
				$bararray['bcode'][$k++] = array('t' => 0, 'w' => 1, 'h' => 2, 'p' => 0);
				$bararray['maxw'] += 2;
				//--
			} //end for
			//--
		} //end for
		//--
		if($notkix) {
			// stop bar
			$bararray['bcode'][$k++] = array('t' => 1, 'w' => 1, 'h' => 3, 'p' => 0);
			$bararray['maxw'] += 1;
		} //end if
		//--
		return $bararray;
		//--
	} //END FUNCTION


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>