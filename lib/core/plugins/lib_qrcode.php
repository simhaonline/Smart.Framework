<?php
// [LIB - Smart.Framework / Plugins / QRCode]
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.5.2')) {
	@http_response_code(500);
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Minimalist QRCode Implementation
// supports only binary encoding
//======================================================


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


//============================================================
// BarCode 2D: QRCode (binary only)
// License: BSD
// (c) 2019 unix-world.org
//============================================================
// TECHNICAL DATA / FEATURES OF QRCODE:
// * Encodable Character Set: 	UTF-8 + Binary
// * Code Type: 				Matrix
// * Error Correction Levels: 	L: 7% ; M: 15% ; Q: 25% ; H: 30%
// * Maximum Data Characters: 	2953 Binary (UTF-8)
//============================================================
// This class is derived from the following projects:
// barcode.php - Generate barcodes from a single PHP file. MIT license.
// Copyright (c) 2016-2018 Kreative Software.
//============================================================


/**
 * Class: QRCode - A minimalist QRCode Implementation (supports only binary encoding)
 * Creates a SVG QRCode
 *
 * @access 		PUBLIC
 * @depends 	classes: Smart
 * @version 	v.20191218
 * @package 	Plugins:Barcodes
 */
final class SmartQR2DBarcode {

	// ->


	private $mode = ''; // can be: L, M, Q, H
	private $type = 'QR';
	private $options = [
		'cs' => '#FFFFFF', // color of background
		'cm' => '#333333', // color of modules
		'sf' => 3,
		'wq' => 1, // spacing around qrcode
	//	'wm' => 1, // use 'sf' instead of this
		'ms' => 'r', // Module shape. One of: s for square, r for round, or x for X-shaped. Default is s
	];


	public function __construct($mode='') {
		//--
		switch((string)strtoupper((string)trim((string)$mode))) {
			case 'L':
				$this->mode = 'L';
				$this->type = 'QRL';
				break;
			case 'M':
				$this->mode = 'M';
				$this->type = 'QRM';
				break;
			case 'Q':
				$this->mode = 'Q';
				$this->type = 'QRQ';
				break;
			case 'H':
				$this->mode = 'H';
				$this->type = 'QRH';
				break;
			default:
				$this->mode = '';
				$this->type = 'QR';
		} //end switch
		//--
	} //END FUNCTION


	public function renderAsSVG($code, $options=[]) {
		//--
		$the_options = (array) $this->options;
		//--
		if(is_array($options)) {
			foreach($options as $key => $val) {
				if(array_key_exists((string)$key, (array)$this->options)) {
					$the_options[(string)$key] = $val;
				} //end if
			} //end foreach
		} //end if
		//--
		return (string) $this->render_as_svg((string)$this->type, (string)$code, (array)$the_options);
		//--
	} //END FUNCTION


	//===== PRIVATES


	private function render_as_svg($symbology, $data, $options) {
		//--
		if(!is_array($options)) {
			$options = array();
		} //end if
		$options['md'] = 1; // this is mandatory for QRCode with SVG
		//--
		list($code, $widths, $width, $height, $x, $y, $w, $h) = (array) $this->encode_and_calculate_size($symbology, $data, $options);
		//--
		$svg  = '<?xml version="1.0"?>';
		$svg .= '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="'.(int)$width.'" height="'.(int)$height.'" viewBox="0 0 '.(int)$width.' '.(int)$height.'">'."\n";
		$svg .= '<g>';
		$bgcolor = (isset($options['bc']) ? $options['bc'] : '#FFFFFF');
		if($bgcolor) {
			$svg .= '<rect x="0" y="0"';
			$svg .= ' width="'.(int)$width.'" height="'.(int)$height.'"';
			$svg .= ' fill="'.Smart::escape_html($bgcolor).'"/>';
		} //end if
		$colors = [
			($options['cs'] ? (string)$options['cs'] : '#FFFFFF'),
			($options['cm'] ? (string)$options['cm'] : '#000000')
		];
		$svg .= (string) $this->dispatch_render_svg(
			$code, $x, $y, $w, $h, $colors, $widths, $options
		);
		$svg .= '</g>'."\n".'</svg>';
		//--
		return (string) $svg;
		//--
	} //END FUNCTION


	private function encode_and_calculate_size($symbology, $data, $options) {
		//--
		$code = (array) $this->dispatch_encode($symbology, $data, $options);
		//--
		$widths = [
			(isset($options['wq']) ? (int)$options['wq'] : 1),
			(isset($options['wm']) ? (int)$options['wm'] : 1),
			(isset($options['ww']) ? (int)$options['ww'] : 3),
			(isset($options['wn']) ? (int)$options['wn'] : 1),
			(isset($options['w4']) ? (int)$options['w4'] : 1),
			(isset($options['w5']) ? (int)$options['w5'] : 1),
			(isset($options['w6']) ? (int)$options['w6'] : 1),
			(isset($options['w7']) ? (int)$options['w7'] : 1),
			(isset($options['w8']) ? (int)$options['w8'] : 1),
			(isset($options['w9']) ? (int)$options['w9'] : 1),
		];
		//--
		$size = $this->dispatch_calculate_size($code, $widths, $options);
		$dscale = ($code && isset($code['g']) && $code['g'] == 'm') ? 4 : 1;
		$scale = (isset($options['sf']) ? (float)$options['sf'] : $dscale);
		$scalex = (isset($options['sx']) ? (float)$options['sx'] : $scale);
		$scaley = (isset($options['sy']) ? (float)$options['sy'] : $scale);
		$dpadding = ($code && isset($code['g']) && $code['g'] == 'm') ? 0 : 10;
		$padding = (isset($options['p']) ? (int)$options['p'] : $dpadding);
		$vert = (isset($options['pv']) ? (int)$options['pv'] : $padding);
		$horiz = (isset($options['ph']) ? (int)$options['ph'] : $padding);
		$top = (isset($options['pt']) ? (int)$options['pt'] : $vert);
		$left = (isset($options['pl']) ? (int)$options['pl'] : $horiz);
		$right = (isset($options['pr']) ? (int)$options['pr'] : $horiz);
		$bottom = (isset($options['pb']) ? (int)$options['pb'] : $vert);
		$dwidth = ceil($size[0] * $scalex) + $left + $right;
		$dheight = ceil($size[1] * $scaley) + $top + $bottom;
		$iwidth = (isset($options['w']) ? (int)$options['w'] : $dwidth);
		$iheight = (isset($options['h']) ? (int)$options['h'] : $dheight);
		$swidth = $iwidth - $left - $right;
		$sheight = $iheight - $top - $bottom;
		//--
		return array(
			$code, $widths, $iwidth, $iheight,
			$left, $top, $swidth, $sheight
		);
		//--
	} //END FUNCTION


	//=== DISPATCH


	private function dispatch_encode($symbology, $data, $options) {
		//--
		switch(strtoupper((string)$symbology)) {
			case 'QRH':
				return (array) $this->qr_encode($data, 3);
				break;
			case 'QRQ':
				return (array) $this->qr_encode($data, 2);
				break;
			case 'QRM':
				return (array) $this->qr_encode($data, 1);
				break;
			case 'QRL':
				return (array) $this->qr_encode($data, 0);
				break;
			case 'QR':
			default:
				// fallback to default
		} //end switch
		//--
		return (array) $this->qr_encode($data, 0);
		//--
	} //END FUNCTION


	private function dispatch_calculate_size($code, $widths, $options) {
		//--
		if($code && isset($code['g']) && $code['g'] && ((string)$code['g'] == 'm')) {
			return (array) $this->matrix_calculate_size($code, $widths);
		} //end if
		//--
		return array(0, 0);
		//--
	} //END FUNCTION


	private function dispatch_render_svg($code, $x, $y, $w, $h, $colors, $widths, $options) {
		//--
		if($code && isset($code['g']) && $code['g'] && ((string)$code['g'] == 'm')) {
			return (string) $this->matrix_render_svg($code, $x, $y, $w, $h, $colors, $widths, $options);
		} //end if
		//--
		return '';
		//--
	} //END FUNCTION


	//=== MATRIX BARCODE RENDERER


	private function matrix_calculate_size($code, $widths) {
		//--
		$width = ($code['q'][3] * $widths[0] + $code['s'][0] * $widths[1] + $code['q'][1] * $widths[0]);
		$height = ($code['q'][0] * $widths[0] + $code['s'][1] * $widths[1] + $code['q'][2] * $widths[0]);
		//--
		return array($width, $height);
		//--
	} //END FUNCTION


	private function matrix_render_svg($code, $x, $y, $w, $h, $colors, $widths, $options) {
		//--
		$shape = (isset($options['ms']) ? (string)strtolower((string)$options['ms']) : '');
		$density = (isset($options['md']) ? (float)$options['md'] : 1);
		//--
		list($width, $height) = (array) $this->matrix_calculate_size($code, $widths);
		if($width && $height) {
			$scale = min($w / $width, $h / $height);
			if ($scale > 1) $scale = floor($scale);
			$x = floor($x + ($w - $width * $scale) / 2);
			$y = floor($y + ($h - $height * $scale) / 2);
		} else {
			$scale = 1;
			$x = floor($x + $w / 2);
			$y = floor($y + $h / 2);
		} //end if else
		//--
		$tx = 'translate('.(int)$x.' '.(int)$y.')';
		if($scale > 1) {
			$tx .= ' scale('.(int)$scale.' '.(int)$scale.')';
		} //end if
		$svg = '<g transform="'.Smart::escape_html($tx).'">';
		$x = $code['q'][3] * $widths[0];
		$y = $code['q'][0] * $widths[0];
		$wh = $widths[1];
		foreach($code['b'] as $by => $row) {
			$y1 = $y + $by * $wh;
			foreach($row as $bx => $color) {
				$x1 = $x + $bx * $wh;
				$mc = $colors[$color];
				if($mc) {
					$svg .= (string) $this->matrix_dot_svg($x1, $y1, $wh, $wh, $mc, $shape, $density);
				} //end if
			} //end foreach
		} //end foreach
		//--
		$svg .= '</g>';
		//--
		return (string) $svg;
		//--
	} //END FUNCTION


	private function matrix_dot_svg($x, $y, $w, $h, $mc, $ms, $md) {
		//--
		switch((string)$ms) {
			case 'r':
				//--
				$cx = $x + $w / 2;
				$cy = $y + $h / 2;
				$rx = $w * $md / 2;
				$ry = $h * $md / 2;
				$svg  = '<ellipse cx="'.(float)$cx.'" cy="'.(float)$cy.'"';
				$svg .= ' rx="'.(float)$rx.'" ry="'.(float)$ry.'"';
				$svg .= ' fill="'.Smart::escape_html($mc).'"/>';
				break;
				//--
			case 'x':
				//--
				$x1 = $x + (1 - $md) * $w / 2;
				$y1 = $y + (1 - $md) * $h / 2;
				$x2 = $x + $w - (1 - $md) * $w / 2;
				$y2 = $y + $h - (1 - $md) * $h / 2;
				$svg  = '<line x1="'.(float)$x1.'" y1="'.(float)$y1.'"';
				$svg .= ' x2="'.(float)$x2.'" y2="'.(float)$y2.'"';
				$svg .= ' stroke="'.Smart::escape_html($mc).'"';
				$svg .= ' stroke-width="'.(float)((float)$md / 5).'"/>';
				$svg .= '<line x1="'.(float)$x1.'" y1="'.(float)$y2.'"';
				$svg .= ' x2="'.(float)$x2.'" y2="'.(float)$y1.'"';
				$svg .= ' stroke="'.Smart::escape_html($mc).'"';
				$svg .= ' stroke-width="'.(float)((float)$md / 5).'"/>';
				$svg = '<g>'.$svg.'</g>';
				break;
				//--
			default: // s
				//--
				$x += (1 - $md) * $w / 2;
				$y += (1 - $md) * $h / 2;
				$w *= $md;
				$h *= $md;
				$svg = '<rect x="'.(float)$x.'" y="'.(float)$y.'"';
				$svg .= ' width="'.(float)$w.'" height="'.(float)$h.'"';
				$svg .= ' fill="'.Smart::escape_html($mc).'"/>';
				//--
		} //end switch
		//--
		return (string) $svg;
		//--
	} //END FUNCTION


	//=== QR ENCODER


	private function qr_encode($data, $ecl) {
		//--
		list($mode, $vers, $ec, $data) = (array) $this->qr_encode_data($data, $ecl);
		$data = $this->qr_encode_ec($data, $ec, $vers);
		list($size, $mtx) = $this->qr_create_matrix($vers, $data);
		list($mask, $mtx) = $this->qr_apply_best_mask($mtx, $size);
		$mtx = $this->qr_finalize_matrix($mtx, $size, $ecl, $mask, $vers);
		//--
		return array(
			'g' => 'm',
			'q' => array(4, 4, 4, 4),
			's' => array($size, $size),
			'b' => $mtx
		);
		//--
	} //END FUNCTION


	private function qr_encode_data($data, $ecl) {
		//--
		$mode = 2; // support only binary
		//--
		$version = $this->qr_detect_version($data, $mode, $ecl);
		$version_group = (($version < 10) ? 0 : (($version < 27) ? 1 : 2));
		$ec_params = $this->qr_ec_params[($version - 1) * 4 + $ecl];
		//-- Don't cut off mid-character if exceeding capacity
		$max_chars = $this->qr_capacity[$version - 1][$ecl][$mode];
		if($mode == 3) {
			$max_chars <<= 1;
		} //end if
		$data = (string) substr($data, 0, $max_chars);
		//-- Convert from character level to bit level
		switch($mode) {
			case 0: // numeric
			case 1: // alphanumeric
			case 3: // kanji
			case 2: // binary
			default:
				$code = $this->qr_encode_binary($data, $version_group);
				break;
		} //end switch
		for($i=0; $i<4; $i++) {
			$code[] = 0;
		} //end for
		while(count($code) % 8) {
			$code[] = 0;
		} //end while
		//-- Convert from bit level to byte level
		$data = array();
		for($i=0, $n=count($code); $i<$n; $i+=8) {
			$byte = 0;
			if($code[$i + 0]) { $byte |= 0x80; }
			if($code[$i + 1]) { $byte |= 0x40; }
			if($code[$i + 2]) { $byte |= 0x20; }
			if($code[$i + 3]) { $byte |= 0x10; }
			if($code[$i + 4]) { $byte |= 0x08; }
			if($code[$i + 5]) { $byte |= 0x04; }
			if($code[$i + 6]) { $byte |= 0x02; }
			if($code[$i + 7]) { $byte |= 0x01; }
			$data[] = $byte;
		} //end for
		//--
		for($i=count($data), $a=1, $n=$ec_params[0]; $i<$n; $i++, $a^=1) {
			$data[] = $a ? 236 : 17;
		} //end for
		//--
		return array($mode, $version, $ec_params, $data);
		//--
	} //END FUNCTION


	private function qr_detect_version($data, $mode, $ecl) {
		//--
		$length = strlen((string)$data);
		//--
		for($v=0; $v<40; $v++) {
			if($length <= $this->qr_capacity[$v][$ecl][$mode]) {
				return (int) ($v + 1);
			} //end if
		} //end for
		//--
		return 40;
		//--
	} //END FUNCTION


	private function qr_encode_binary($data, $version_group) {
		//--
		$code = array(0, 1, 0, 0);
		$length = strlen((string)$data);
		//--
		switch($version_group) {
			case 2:  // 27 - 40
			case 1:  // 10 - 26
				$code[] = $length & 0x8000;
				$code[] = $length & 0x4000;
				$code[] = $length & 0x2000;
				$code[] = $length & 0x1000;
				$code[] = $length & 0x0800;
				$code[] = $length & 0x0400;
				$code[] = $length & 0x0200;
				$code[] = $length & 0x0100;
			case 0:  // 1 - 9
				$code[] = $length & 0x0080;
				$code[] = $length & 0x0040;
				$code[] = $length & 0x0020;
				$code[] = $length & 0x0010;
				$code[] = $length & 0x0008;
				$code[] = $length & 0x0004;
				$code[] = $length & 0x0002;
				$code[] = $length & 0x0001;
		} //end switch
		//--
		for($i=0; $i<$length; $i++) {
			$ch = ord(substr($data, $i, 1));
			$code[] = $ch & 0x80;
			$code[] = $ch & 0x40;
			$code[] = $ch & 0x20;
			$code[] = $ch & 0x10;
			$code[] = $ch & 0x08;
			$code[] = $ch & 0x04;
			$code[] = $ch & 0x02;
			$code[] = $ch & 0x01;
		} //end for
		//--
		return (array) $code;
		//--
	} //END FUNCTION


	private function qr_encode_ec($data, $ec_params, $version) {
		//--
		$blocks = $this->qr_ec_split($data, $ec_params);
		$ec_blocks = array();
		//--
		for($i=0, $n=count($blocks); $i<$n; $i++) {
			$ec_blocks[] = $this->qr_ec_divide($blocks[$i], $ec_params);
		} //end for
		//--
		$data = $this->qr_ec_interleave($blocks);
		$ec_data = $this->qr_ec_interleave($ec_blocks);
		//--
		$code = array();
		//--
		foreach($data as $kk => $ch) {
			$code[] = $ch & 0x80;
			$code[] = $ch & 0x40;
			$code[] = $ch & 0x20;
			$code[] = $ch & 0x10;
			$code[] = $ch & 0x08;
			$code[] = $ch & 0x04;
			$code[] = $ch & 0x02;
			$code[] = $ch & 0x01;
		} //end foreach
		foreach($ec_data as $kk => $ch) {
			$code[] = $ch & 0x80;
			$code[] = $ch & 0x40;
			$code[] = $ch & 0x20;
			$code[] = $ch & 0x10;
			$code[] = $ch & 0x08;
			$code[] = $ch & 0x04;
			$code[] = $ch & 0x02;
			$code[] = $ch & 0x01;
		} //end foreach
		for($n=$this->qr_remainder_bits[$version - 1]; $n>0; $n--) {
			$code[] = 0;
		} //end for
		//--
		return (array) $code;
		//--
	} //END FUNCTION


	private function qr_ec_split($data, $ec_params) {
		//--
		$blocks = array();
		$offset = 0;
		//--
		for($i = $ec_params[2], $length = $ec_params[3]; $i > 0; $i--) {
			$blocks[] = array_slice($data, $offset, $length);
			$offset += $length;
		} //end for
		//--
		for($i = $ec_params[4], $length = $ec_params[5]; $i > 0; $i--) {
			$blocks[] = array_slice($data, $offset, $length);
			$offset += $length;
		} //end for
		//--
		return (array) $blocks;
		//--
	} //END FUNCTION


	private function qr_ec_divide($data, $ec_params) {
		//--
		$num_data = count($data);
		$num_error = $ec_params[1];
		$generator = $this->qr_ec_polynomials[$num_error];
		$message = $data;
		for($i = 0; $i < $num_error; $i++) {
			$message[] = 0;
		} //end for
		for($i = 0; $i < $num_data; $i++) {
			if($message[$i]) {
				$leadterm = $this->qr_log[$message[$i]];
				for($j = 0; $j <= $num_error; $j++) {
					$term = ($generator[$j] + $leadterm) % 255;
					$message[$i + $j] ^= $this->qr_exp[$term];
				} //end for
			} //end if
		} //end for
		//--
		return array_slice($message, $num_data, $num_error);
		//--
	} //END FUNCTION


	private function qr_ec_interleave($blocks) {
		//--
		$data = array();
		$num_blocks = count($blocks);
		//--
		for($offset = 0; true; $offset++) {
			$break = true;
			for($i = 0; $i < $num_blocks; $i++) {
				if(isset($blocks[$i][$offset])) {
					$data[] = $blocks[$i][$offset];
					$break = false;
				} //end if
			} //end for
			if($break) {
				break;
			} //end if
		} //end for
		//--
		return (array) $data;
		//--
	} //END FUNCTION


	private function qr_create_matrix($version, $data) {
		//--
		$size = $version * 4 + 17;
		$matrix = array();
		for($i = 0; $i < $size; $i++) {
			$row = array();
			for($j = 0; $j < $size; $j++) {
				$row[] = 0;
			} //end for
			$matrix[] = $row;
		} //end for
		//-- Finder patterns
		for($i = 0; $i < 8; $i++) {
			for($j = 0; $j < 8; $j++) {
				$m = (($i == 7 || $j == 7) ? 2 :
					(($i == 0 || $j == 0 || $i == 6 || $j == 6) ? 3 :
					(($i == 1 || $j == 1 || $i == 5 || $j == 5) ? 2 : 3)));
				$matrix[$i][$j] = $m;
				$matrix[$size - $i - 1][$j] = $m;
				$matrix[$i][$size - $j - 1] = $m;
			} //end for
		} //end for
		//-- Alignment patterns.
		if($version >= 2) {
			$alignment = $this->qr_alignment_patterns[$version - 2];
			foreach($alignment as $kk => $i) {
				foreach($alignment as $zz => $j) {
					if(!$matrix[$i][$j]) {
						for($ii = -2; $ii <= 2; $ii++) {
							for($jj = -2; $jj <= 2; $jj++) {
								$m = (max(abs($ii), abs($jj)) & 1) ^ 3;
								$matrix[$i + $ii][$j + $jj] = $m;
							} //end for
						} //end for
					} //end if
				} //end foreach
			} //end foreach
		} //end if
		//-- Timing patterns.
		for($i = $size - 9; $i >= 8; $i--) {
			$matrix[$i][6] = ($i & 1) ^ 3;
			$matrix[6][$i] = ($i & 1) ^ 3;
		} //end for
		//-- Dark module. Such an ominous name for such an innocuous thing.
		$matrix[$size - 8][8] = 3;
		//-- Format information area.
		for($i = 0; $i <= 8; $i++) {
			if(!$matrix[$i][8]) { $matrix[$i][8] = 1; }
			if(!$matrix[8][$i]) { $matrix[8][$i] = 1; }
			if($i && !$matrix[$size - $i][8]) { $matrix[$size - $i][8] = 1; }
			if($i && !$matrix[8][$size - $i]) { $matrix[8][$size - $i] = 1; }
		} //end for
		//-- Version information area.
		if($version >= 7) {
			for($i = 9; $i < 12; $i++) {
				for($j = 0; $j < 6; $j++) {
					$matrix[$size - $i][$j] = 1;
					$matrix[$j][$size - $i] = 1;
				} //end for
			} //end for
		} //end if
		//-- Data
		$col = $size - 1;
		$row = $size - 1;
		$dir = -1;
		$offset = 0;
		$length = count($data);
		while($col > 0 && $offset < $length) {
			if(!$matrix[$row][$col]) {
				$matrix[$row][$col] = $data[$offset] ? 5 : 4;
				$offset++;
			} //end if
			if(!$matrix[$row][$col - 1]) {
				$matrix[$row][$col - 1] = $data[$offset] ? 5 : 4;
				$offset++;
			} //end if
			$row += $dir;
			if($row < 0 || $row >= $size) {
				$dir = -$dir;
				$row += $dir;
				$col -= 2;
				if($col == 6) {
					$col--;
				} //end if
			} //end if
		} //end while
		//--
		return array($size, $matrix);
		//--
	} //END FUNCTION


	private function qr_apply_best_mask($matrix, $size) {
		//--
		$best_mask = 0;
		$best_matrix = $this->qr_apply_mask($matrix, $size, $best_mask);
		$best_penalty = $this->qr_penalty($best_matrix, $size);
		for($test_mask = 1; $test_mask < 8; $test_mask++) {
			$test_matrix = $this->qr_apply_mask($matrix, $size, $test_mask);
			$test_penalty = $this->qr_penalty($test_matrix, $size);
			if($test_penalty < $best_penalty) {
				$best_mask = $test_mask;
				$best_matrix = $test_matrix;
				$best_penalty = $test_penalty;
			} //end if
		} //end for
		//--
		return array($best_mask, $best_matrix);
		//--
	} //END FUNCTION


	private function qr_apply_mask($matrix, $size, $mask) {
		//--
		for($i = 0; $i < $size; $i++) {
			for($j = 0; $j < $size; $j++) {
				if($matrix[$i][$j] >= 4) {
					if($this->qr_mask($mask, $i, $j)) {
						$matrix[$i][$j] ^= 1;
					} //end if
				} //end if
			} //end for
		} //end for
		//--
		return (array) $matrix;
		//--
	} //END FUNCTION


	private function qr_mask($mask, $r, $c) {
		//--
		switch($mask) {
			case 0: return !( ($r + $c) % 2 );
			case 1: return !( ($r     ) % 2 );
			case 2: return !( (     $c) % 3 );
			case 3: return !( ($r + $c) % 3 );
			case 4: return !( (floor(($r) / 2) + floor(($c) / 3)) % 2 );
			case 5: return !( ((($r * $c) % 2) + (($r * $c) % 3))     );
			case 6: return !( ((($r * $c) % 2) + (($r * $c) % 3)) % 2 );
			case 7: return !( ((($r + $c) % 2) + (($r * $c) % 3)) % 2 );
		} //end switch
		//--
		return null;
		//--
	} //END FUNCTION


	private function qr_penalty(&$matrix, $size) {
		//--
		$score  = $this->qr_penalty_1($matrix, $size);
		$score += $this->qr_penalty_2($matrix, $size);
		$score += $this->qr_penalty_3($matrix, $size);
		$score += $this->qr_penalty_4($matrix, $size);
		//--
		return $score;
		//--
	} //END FUNCTION


	private function qr_penalty_1(&$matrix, $size) {
		//--
		$score = 0;
		for($i = 0; $i < $size; $i++) {
			$rowvalue = 0;
			$rowcount = 0;
			$colvalue = 0;
			$colcount = 0;
			for($j = 0; $j < $size; $j++) {
				$rv = ($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) ? 1 : 0;
				$cv = ($matrix[$j][$i] == 5 || $matrix[$j][$i] == 3) ? 1 : 0;
				if($rv == $rowvalue) {
					$rowcount++;
				} else {
					if ($rowcount >= 5) $score += $rowcount - 2;
					$rowvalue = $rv;
					$rowcount = 1;
				} //end if else
				if($cv == $colvalue) {
					$colcount++;
				} else {
					if($colcount >= 5) {
						$score += $colcount - 2;
					} //end if
					$colvalue = $cv;
					$colcount = 1;
				} //end if else
			} //end for
			if($rowcount >= 5) {
				$score += $rowcount - 2;
			} //end if
			if($colcount >= 5) {
				$score += $colcount - 2;
			} //end if
		} //end for
		//--
		return $score;
		//--
	} //END FUNCTION


	private function qr_penalty_2(&$matrix, $size) {
		//--
		$score = 0;
		//--
		for($i = 1; $i < $size; $i++) {
			for($j = 1; $j < $size; $j++) {
				$v1 = $matrix[$i - 1][$j - 1];
				$v2 = $matrix[$i - 1][$j    ];
				$v3 = $matrix[$i    ][$j - 1];
				$v4 = $matrix[$i    ][$j    ];
				$v1 = ($v1 == 5 || $v1 == 3) ? 1 : 0;
				$v2 = ($v2 == 5 || $v2 == 3) ? 1 : 0;
				$v3 = ($v3 == 5 || $v3 == 3) ? 1 : 0;
				$v4 = ($v4 == 5 || $v4 == 3) ? 1 : 0;
				if($v1 == $v2 && $v2 == $v3 && $v3 == $v4) {
					$score += 3;
				} //end if
			} //end for
		} //end for
		//--
		return $score;
		//--
	} //END FUNCTION


	private function qr_penalty_3(&$matrix, $size) {
		//--
		$score = 0;
		//--
		for($i = 0; $i < $size; $i++) {
			$rowvalue = 0;
			$colvalue = 0;
			for($j = 0; $j < 11; $j++) {
				$rv = ($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) ? 1 : 0;
				$cv = ($matrix[$j][$i] == 5 || $matrix[$j][$i] == 3) ? 1 : 0;
				$rowvalue = (($rowvalue << 1) & 0x7FF) | $rv;
				$colvalue = (($colvalue << 1) & 0x7FF) | $cv;
			} //end for
			if($rowvalue == 0x5D0 || $rowvalue == 0x5D) { $score += 40; }
			if($colvalue == 0x5D0 || $colvalue == 0x5D) { $score += 40; }
			for($j = 11; $j < $size; $j++) {
				$rv = ($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) ? 1 : 0;
				$cv = ($matrix[$j][$i] == 5 || $matrix[$j][$i] == 3) ? 1 : 0;
				$rowvalue = (($rowvalue << 1) & 0x7FF) | $rv;
				$colvalue = (($colvalue << 1) & 0x7FF) | $cv;
				if($rowvalue == 0x5D0 || $rowvalue == 0x5D) { $score += 40; }
				if($colvalue == 0x5D0 || $colvalue == 0x5D) { $score += 40; }
			} //end for
		} //end for
		//--
		return $score;
		//--
	} //END FUNCTION


	private function qr_penalty_4(&$matrix, $size) {
		//--
		$dark = 0;
		for($i = 0; $i < $size; $i++) {
			for($j = 0; $j < $size; $j++) {
				if($matrix[$i][$j] == 5 || $matrix[$i][$j] == 3) {
					$dark++;
				} //end if
			} //end for
		} //end for
		$dark *= 20;
		$dark /= $size * $size;
		$a = abs(floor($dark) - 10);
		$b = abs(ceil($dark) - 10);
		//--
		return min($a, $b) * 10;
		//--
	} //END FUNCTION


	private function qr_finalize_matrix($matrix, $size, $ecl, $mask, $version) {
		//-- Format Info
		$format = $this->qr_format_info[$ecl * 8 + $mask];
		//--
		$matrix[8][0] = $format[0];
		$matrix[8][1] = $format[1];
		$matrix[8][2] = $format[2];
		$matrix[8][3] = $format[3];
		$matrix[8][4] = $format[4];
		$matrix[8][5] = $format[5];
		$matrix[8][7] = $format[6];
		$matrix[8][8] = $format[7];
		$matrix[7][8] = $format[8];
		$matrix[5][8] = $format[9];
		$matrix[4][8] = $format[10];
		$matrix[3][8] = $format[11];
		$matrix[2][8] = $format[12];
		$matrix[1][8] = $format[13];
		$matrix[0][8] = $format[14];
		$matrix[$size - 1][8] = $format[0];
		$matrix[$size - 2][8] = $format[1];
		$matrix[$size - 3][8] = $format[2];
		$matrix[$size - 4][8] = $format[3];
		$matrix[$size - 5][8] = $format[4];
		$matrix[$size - 6][8] = $format[5];
		$matrix[$size - 7][8] = $format[6];
		$matrix[8][$size - 8] = $format[7];
		$matrix[8][$size - 7] = $format[8];
		$matrix[8][$size - 6] = $format[9];
		$matrix[8][$size - 5] = $format[10];
		$matrix[8][$size - 4] = $format[11];
		$matrix[8][$size - 3] = $format[12];
		$matrix[8][$size - 2] = $format[13];
		$matrix[8][$size - 1] = $format[14];
		//-- Version Info
		if($version >= 7) {
			$version = $this->qr_version_info[$version - 7];
			for($i = 0; $i < 18; $i++) {
				$r = $size - 9 - ($i % 3);
				$c = 5 - floor($i / 3);
				$matrix[$r][$c] = $version[$i];
				$matrix[$c][$r] = $version[$i];
			} //end for
		} //end if
		//-- Patterns & Data
		for($i = 0; $i < $size; $i++) {
			for($j = 0; $j < $size; $j++) {
				$matrix[$i][$j] &= 1;
			} //end for
		} //end for
		//--
		return (array) $matrix;
		//--
	} //END FUNCTION


	//=== PRIVATE VARS


	/*  maximum encodable characters = $qr_capacity [ (version - 1) ]  */
	/*    [ (0 for L, 1 for M, 2 for Q, 3 for H)                    ]  */
	/*    [ (0 for numeric, 1 for alpha, 2 for binary, 3 for kanji) ]  */
	private $qr_capacity = array(
		array(array(  41,   25,   17,   10), array(  34,   20,   14,    8),
			  array(  27,   16,   11,    7), array(  17,   10,    7,    4)),
		array(array(  77,   47,   32,   20), array(  63,   38,   26,   16),
			  array(  48,   29,   20,   12), array(  34,   20,   14,    8)),
		array(array( 127,   77,   53,   32), array( 101,   61,   42,   26),
			  array(  77,   47,   32,   20), array(  58,   35,   24,   15)),
		array(array( 187,  114,   78,   48), array( 149,   90,   62,   38),
			  array( 111,   67,   46,   28), array(  82,   50,   34,   21)),
		array(array( 255,  154,  106,   65), array( 202,  122,   84,   52),
			  array( 144,   87,   60,   37), array( 106,   64,   44,   27)),
		array(array( 322,  195,  134,   82), array( 255,  154,  106,   65),
			  array( 178,  108,   74,   45), array( 139,   84,   58,   36)),
		array(array( 370,  224,  154,   95), array( 293,  178,  122,   75),
			  array( 207,  125,   86,   53), array( 154,   93,   64,   39)),
		array(array( 461,  279,  192,  118), array( 365,  221,  152,   93),
			  array( 259,  157,  108,   66), array( 202,  122,   84,   52)),
		array(array( 552,  335,  230,  141), array( 432,  262,  180,  111),
			  array( 312,  189,  130,   80), array( 235,  143,   98,   60)),
		array(array( 652,  395,  271,  167), array( 513,  311,  213,  131),
			  array( 364,  221,  151,   93), array( 288,  174,  119,   74)),
		array(array( 772,  468,  321,  198), array( 604,  366,  251,  155),
			  array( 427,  259,  177,  109), array( 331,  200,  137,   85)),
		array(array( 883,  535,  367,  226), array( 691,  419,  287,  177),
			  array( 489,  296,  203,  125), array( 374,  227,  155,   96)),
		array(array(1022,  619,  425,  262), array( 796,  483,  331,  204),
			  array( 580,  352,  241,  149), array( 427,  259,  177,  109)),
		array(array(1101,  667,  458,  282), array( 871,  528,  362,  223),
			  array( 621,  376,  258,  159), array( 468,  283,  194,  120)),
		array(array(1250,  758,  520,  320), array( 991,  600,  412,  254),
			  array( 703,  426,  292,  180), array( 530,  321,  220,  136)),
		array(array(1408,  854,  586,  361), array(1082,  656,  450,  277),
			  array( 775,  470,  322,  198), array( 602,  365,  250,  154)),
		array(array(1548,  938,  644,  397), array(1212,  734,  504,  310),
			  array( 876,  531,  364,  224), array( 674,  408,  280,  173)),
		array(array(1725, 1046,  718,  442), array(1346,  816,  560,  345),
			  array( 948,  574,  394,  243), array( 746,  452,  310,  191)),
		array(array(1903, 1153,  792,  488), array(1500,  909,  624,  384),
			  array(1063,  644,  442,  272), array( 813,  493,  338,  208)),
		array(array(2061, 1249,  858,  528), array(1600,  970,  666,  410),
			  array(1159,  702,  482,  297), array( 919,  557,  382,  235)),
		array(array(2232, 1352,  929,  572), array(1708, 1035,  711,  438),
			  array(1224,  742,  509,  314), array( 969,  587,  403,  248)),
		array(array(2409, 1460, 1003,  618), array(1872, 1134,  779,  480),
			  array(1358,  823,  565,  348), array(1056,  640,  439,  270)),
		array(array(2620, 1588, 1091,  672), array(2059, 1248,  857,  528),
			  array(1468,  890,  611,  376), array(1108,  672,  461,  284)),
		array(array(2812, 1704, 1171,  721), array(2188, 1326,  911,  561),
			  array(1588,  963,  661,  407), array(1228,  744,  511,  315)),
		array(array(3057, 1853, 1273,  784), array(2395, 1451,  997,  614),
			  array(1718, 1041,  715,  440), array(1286,  779,  535,  330)),
		array(array(3283, 1990, 1367,  842), array(2544, 1542, 1059,  652),
			  array(1804, 1094,  751,  462), array(1425,  864,  593,  365)),
		array(array(3517, 2132, 1465,  902), array(2701, 1637, 1125,  692),
			  array(1933, 1172,  805,  496), array(1501,  910,  625,  385)),
		array(array(3669, 2223, 1528,  940), array(2857, 1732, 1190,  732),
			  array(2085, 1263,  868,  534), array(1581,  958,  658,  405)),
		array(array(3909, 2369, 1628, 1002), array(3035, 1839, 1264,  778),
			  array(2181, 1322,  908,  559), array(1677, 1016,  698,  430)),
		array(array(4158, 2520, 1732, 1066), array(3289, 1994, 1370,  843),
			  array(2358, 1429,  982,  604), array(1782, 1080,  742,  457)),
		array(array(4417, 2677, 1840, 1132), array(3486, 2113, 1452,  894),
			  array(2473, 1499, 1030,  634), array(1897, 1150,  790,  486)),
		array(array(4686, 2840, 1952, 1201), array(3693, 2238, 1538,  947),
			  array(2670, 1618, 1112,  684), array(2022, 1226,  842,  518)),
		array(array(4965, 3009, 2068, 1273), array(3909, 2369, 1628, 1002),
			  array(2805, 1700, 1168,  719), array(2157, 1307,  898,  553)),
		array(array(5253, 3183, 2188, 1347), array(4134, 2506, 1722, 1060),
			  array(2949, 1787, 1228,  756), array(2301, 1394,  958,  590)),
		array(array(5529, 3351, 2303, 1417), array(4343, 2632, 1809, 1113),
			  array(3081, 1867, 1283,  790), array(2361, 1431,  983,  605)),
		array(array(5836, 3537, 2431, 1496), array(4588, 2780, 1911, 1176),
			  array(3244, 1966, 1351,  832), array(2524, 1530, 1051,  647)),
		array(array(6153, 3729, 2563, 1577), array(4775, 2894, 1989, 1224),
			  array(3417, 2071, 1423,  876), array(2625, 1591, 1093,  673)),
		array(array(6479, 3927, 2699, 1661), array(5039, 3054, 2099, 1292),
			  array(3599, 2181, 1499,  923), array(2735, 1658, 1139,  701)),
		array(array(6743, 4087, 2809, 1729), array(5313, 3220, 2213, 1362),
			  array(3791, 2298, 1579,  972), array(2927, 1774, 1219,  750)),
		array(array(7089, 4296, 2953, 1817), array(5596, 3391, 2331, 1435),
			  array(3993, 2420, 1663, 1024), array(3057, 1852, 1273,  784)),
	);


	/*  $qr_ec_params[                                              */
	/*    4 * (version - 1) + (0 for L, 1 for M, 2 for Q, 3 for H)  */
	/*  ] = array(                                                  */
	/*    total number of data codewords,                           */
	/*    number of error correction codewords per block,           */
	/*    number of blocks in first group,                          */
	/*    number of data codewords per block in first group,        */
	/*    number of blocks in second group,                         */
	/*    number of data codewords per block in second group        */
	/*  );                                                          */
	private $qr_ec_params = array(
		array(   19,  7,  1,  19,  0,   0 ),
		array(   16, 10,  1,  16,  0,   0 ),
		array(   13, 13,  1,  13,  0,   0 ),
		array(    9, 17,  1,   9,  0,   0 ),
		array(   34, 10,  1,  34,  0,   0 ),
		array(   28, 16,  1,  28,  0,   0 ),
		array(   22, 22,  1,  22,  0,   0 ),
		array(   16, 28,  1,  16,  0,   0 ),
		array(   55, 15,  1,  55,  0,   0 ),
		array(   44, 26,  1,  44,  0,   0 ),
		array(   34, 18,  2,  17,  0,   0 ),
		array(   26, 22,  2,  13,  0,   0 ),
		array(   80, 20,  1,  80,  0,   0 ),
		array(   64, 18,  2,  32,  0,   0 ),
		array(   48, 26,  2,  24,  0,   0 ),
		array(   36, 16,  4,   9,  0,   0 ),
		array(  108, 26,  1, 108,  0,   0 ),
		array(   86, 24,  2,  43,  0,   0 ),
		array(   62, 18,  2,  15,  2,  16 ),
		array(   46, 22,  2,  11,  2,  12 ),
		array(  136, 18,  2,  68,  0,   0 ),
		array(  108, 16,  4,  27,  0,   0 ),
		array(   76, 24,  4,  19,  0,   0 ),
		array(   60, 28,  4,  15,  0,   0 ),
		array(  156, 20,  2,  78,  0,   0 ),
		array(  124, 18,  4,  31,  0,   0 ),
		array(   88, 18,  2,  14,  4,  15 ),
		array(   66, 26,  4,  13,  1,  14 ),
		array(  194, 24,  2,  97,  0,   0 ),
		array(  154, 22,  2,  38,  2,  39 ),
		array(  110, 22,  4,  18,  2,  19 ),
		array(   86, 26,  4,  14,  2,  15 ),
		array(  232, 30,  2, 116,  0,   0 ),
		array(  182, 22,  3,  36,  2,  37 ),
		array(  132, 20,  4,  16,  4,  17 ),
		array(  100, 24,  4,  12,  4,  13 ),
		array(  274, 18,  2,  68,  2,  69 ),
		array(  216, 26,  4,  43,  1,  44 ),
		array(  154, 24,  6,  19,  2,  20 ),
		array(  122, 28,  6,  15,  2,  16 ),
		array(  324, 20,  4,  81,  0,   0 ),
		array(  254, 30,  1,  50,  4,  51 ),
		array(  180, 28,  4,  22,  4,  23 ),
		array(  140, 24,  3,  12,  8,  13 ),
		array(  370, 24,  2,  92,  2,  93 ),
		array(  290, 22,  6,  36,  2,  37 ),
		array(  206, 26,  4,  20,  6,  21 ),
		array(  158, 28,  7,  14,  4,  15 ),
		array(  428, 26,  4, 107,  0,   0 ),
		array(  334, 22,  8,  37,  1,  38 ),
		array(  244, 24,  8,  20,  4,  21 ),
		array(  180, 22, 12,  11,  4,  12 ),
		array(  461, 30,  3, 115,  1, 116 ),
		array(  365, 24,  4,  40,  5,  41 ),
		array(  261, 20, 11,  16,  5,  17 ),
		array(  197, 24, 11,  12,  5,  13 ),
		array(  523, 22,  5,  87,  1,  88 ),
		array(  415, 24,  5,  41,  5,  42 ),
		array(  295, 30,  5,  24,  7,  25 ),
		array(  223, 24, 11,  12,  7,  13 ),
		array(  589, 24,  5,  98,  1,  99 ),
		array(  453, 28,  7,  45,  3,  46 ),
		array(  325, 24, 15,  19,  2,  20 ),
		array(  253, 30,  3,  15, 13,  16 ),
		array(  647, 28,  1, 107,  5, 108 ),
		array(  507, 28, 10,  46,  1,  47 ),
		array(  367, 28,  1,  22, 15,  23 ),
		array(  283, 28,  2,  14, 17,  15 ),
		array(  721, 30,  5, 120,  1, 121 ),
		array(  563, 26,  9,  43,  4,  44 ),
		array(  397, 28, 17,  22,  1,  23 ),
		array(  313, 28,  2,  14, 19,  15 ),
		array(  795, 28,  3, 113,  4, 114 ),
		array(  627, 26,  3,  44, 11,  45 ),
		array(  445, 26, 17,  21,  4,  22 ),
		array(  341, 26,  9,  13, 16,  14 ),
		array(  861, 28,  3, 107,  5, 108 ),
		array(  669, 26,  3,  41, 13,  42 ),
		array(  485, 30, 15,  24,  5,  25 ),
		array(  385, 28, 15,  15, 10,  16 ),
		array(  932, 28,  4, 116,  4, 117 ),
		array(  714, 26, 17,  42,  0,   0 ),
		array(  512, 28, 17,  22,  6,  23 ),
		array(  406, 30, 19,  16,  6,  17 ),
		array( 1006, 28,  2, 111,  7, 112 ),
		array(  782, 28, 17,  46,  0,   0 ),
		array(  568, 30,  7,  24, 16,  25 ),
		array(  442, 24, 34,  13,  0,   0 ),
		array( 1094, 30,  4, 121,  5, 122 ),
		array(  860, 28,  4,  47, 14,  48 ),
		array(  614, 30, 11,  24, 14,  25 ),
		array(  464, 30, 16,  15, 14,  16 ),
		array( 1174, 30,  6, 117,  4, 118 ),
		array(  914, 28,  6,  45, 14,  46 ),
		array(  664, 30, 11,  24, 16,  25 ),
		array(  514, 30, 30,  16,  2,  17 ),
		array( 1276, 26,  8, 106,  4, 107 ),
		array( 1000, 28,  8,  47, 13,  48 ),
		array(  718, 30,  7,  24, 22,  25 ),
		array(  538, 30, 22,  15, 13,  16 ),
		array( 1370, 28, 10, 114,  2, 115 ),
		array( 1062, 28, 19,  46,  4,  47 ),
		array(  754, 28, 28,  22,  6,  23 ),
		array(  596, 30, 33,  16,  4,  17 ),
		array( 1468, 30,  8, 122,  4, 123 ),
		array( 1128, 28, 22,  45,  3,  46 ),
		array(  808, 30,  8,  23, 26,  24 ),
		array(  628, 30, 12,  15, 28,  16 ),
		array( 1531, 30,  3, 117, 10, 118 ),
		array( 1193, 28,  3,  45, 23,  46 ),
		array(  871, 30,  4,  24, 31,  25 ),
		array(  661, 30, 11,  15, 31,  16 ),
		array( 1631, 30,  7, 116,  7, 117 ),
		array( 1267, 28, 21,  45,  7,  46 ),
		array(  911, 30,  1,  23, 37,  24 ),
		array(  701, 30, 19,  15, 26,  16 ),
		array( 1735, 30,  5, 115, 10, 116 ),
		array( 1373, 28, 19,  47, 10,  48 ),
		array(  985, 30, 15,  24, 25,  25 ),
		array(  745, 30, 23,  15, 25,  16 ),
		array( 1843, 30, 13, 115,  3, 116 ),
		array( 1455, 28,  2,  46, 29,  47 ),
		array( 1033, 30, 42,  24,  1,  25 ),
		array(  793, 30, 23,  15, 28,  16 ),
		array( 1955, 30, 17, 115,  0,   0 ),
		array( 1541, 28, 10,  46, 23,  47 ),
		array( 1115, 30, 10,  24, 35,  25 ),
		array(  845, 30, 19,  15, 35,  16 ),
		array( 2071, 30, 17, 115,  1, 116 ),
		array( 1631, 28, 14,  46, 21,  47 ),
		array( 1171, 30, 29,  24, 19,  25 ),
		array(  901, 30, 11,  15, 46,  16 ),
		array( 2191, 30, 13, 115,  6, 116 ),
		array( 1725, 28, 14,  46, 23,  47 ),
		array( 1231, 30, 44,  24,  7,  25 ),
		array(  961, 30, 59,  16,  1,  17 ),
		array( 2306, 30, 12, 121,  7, 122 ),
		array( 1812, 28, 12,  47, 26,  48 ),
		array( 1286, 30, 39,  24, 14,  25 ),
		array(  986, 30, 22,  15, 41,  16 ),
		array( 2434, 30,  6, 121, 14, 122 ),
		array( 1914, 28,  6,  47, 34,  48 ),
		array( 1354, 30, 46,  24, 10,  25 ),
		array( 1054, 30,  2,  15, 64,  16 ),
		array( 2566, 30, 17, 122,  4, 123 ),
		array( 1992, 28, 29,  46, 14,  47 ),
		array( 1426, 30, 49,  24, 10,  25 ),
		array( 1096, 30, 24,  15, 46,  16 ),
		array( 2702, 30,  4, 122, 18, 123 ),
		array( 2102, 28, 13,  46, 32,  47 ),
		array( 1502, 30, 48,  24, 14,  25 ),
		array( 1142, 30, 42,  15, 32,  16 ),
		array( 2812, 30, 20, 117,  4, 118 ),
		array( 2216, 28, 40,  47,  7,  48 ),
		array( 1582, 30, 43,  24, 22,  25 ),
		array( 1222, 30, 10,  15, 67,  16 ),
		array( 2956, 30, 19, 118,  6, 119 ),
		array( 2334, 28, 18,  47, 31,  48 ),
		array( 1666, 30, 34,  24, 34,  25 ),
		array( 1276, 30, 20,  15, 61,  16 ),
	);


	private $qr_ec_polynomials = array(
		7 => array(
			0, 87, 229, 146, 149, 238, 102, 21
		),
		10 => array(
			0, 251, 67, 46, 61, 118, 70, 64, 94, 32, 45
		),
		13 => array(
			0, 74, 152, 176, 100, 86, 100,
			106, 104, 130, 218, 206, 140, 78
		),
		15 => array(
			0, 8, 183, 61, 91, 202, 37, 51,
			58, 58, 237, 140, 124, 5, 99, 105
		),
		16 => array(
			0, 120, 104, 107, 109, 102, 161, 76, 3,
			91, 191, 147, 169, 182, 194, 225, 120
		),
		17 => array(
			0, 43, 139, 206, 78, 43, 239, 123, 206,
			214, 147, 24, 99, 150, 39, 243, 163, 136
		),
		18 => array(
			0, 215, 234, 158, 94, 184, 97, 118, 170, 79,
			187, 152, 148, 252, 179, 5, 98, 96, 153
		),
		20 => array(
			0, 17, 60, 79, 50, 61, 163, 26, 187, 202, 180,
			221, 225, 83, 239, 156, 164, 212, 212, 188, 190
		),
		22 => array(
			0, 210, 171, 247, 242, 93, 230, 14, 109, 221, 53, 200,
			74, 8, 172, 98, 80, 219, 134, 160, 105, 165, 231
		),
		24 => array(
			0, 229, 121, 135, 48, 211, 117, 251, 126, 159, 180, 169,
			152, 192, 226, 228, 218, 111, 0, 117, 232, 87, 96, 227, 21
		),
		26 => array(
			0, 173, 125, 158, 2, 103, 182, 118, 17,
			145, 201, 111, 28, 165, 53, 161, 21, 245,
			142, 13, 102, 48, 227, 153, 145, 218, 70
		),
		28 => array(
			0, 168, 223, 200, 104, 224, 234, 108, 180,
			110, 190, 195, 147, 205, 27, 232, 201, 21, 43,
			245, 87, 42, 195, 212, 119, 242, 37, 9, 123
		),
		30 => array(
			0, 41, 173, 145, 152, 216, 31, 179, 182, 50, 48,
			110, 86, 239, 96, 222, 125, 42, 173, 226, 193,
			224, 130, 156, 37, 251, 216, 238, 40, 192, 180
		),
	);


	private $qr_log = array(
		  0,   0,   1,  25,   2,  50,  26, 198,
		  3, 223,  51, 238,  27, 104, 199,  75,
		  4, 100, 224,  14,  52, 141, 239, 129,
		 28, 193, 105, 248, 200,   8,  76, 113,
		  5, 138, 101,  47, 225,  36,  15,  33,
		 53, 147, 142, 218, 240,  18, 130,  69,
		 29, 181, 194, 125, 106,  39, 249, 185,
		201, 154,   9, 120,  77, 228, 114, 166,
		  6, 191, 139,  98, 102, 221,  48, 253,
		226, 152,  37, 179,  16, 145,  34, 136,
		 54, 208, 148, 206, 143, 150, 219, 189,
		241, 210,  19,  92, 131,  56,  70,  64,
		 30,  66, 182, 163, 195,  72, 126, 110,
		107,  58,  40,  84, 250, 133, 186,  61,
		202,  94, 155, 159,  10,  21, 121,  43,
		 78, 212, 229, 172, 115, 243, 167,  87,
		  7, 112, 192, 247, 140, 128,  99,  13,
		103,  74, 222, 237,  49, 197, 254,  24,
		227, 165, 153, 119,  38, 184, 180, 124,
		 17,  68, 146, 217,  35,  32, 137,  46,
		 55,  63, 209,  91, 149, 188, 207, 205,
		144, 135, 151, 178, 220, 252, 190,  97,
		242,  86, 211, 171,  20,  42,  93, 158,
		132,  60,  57,  83,  71, 109,  65, 162,
		 31,  45,  67, 216, 183, 123, 164, 118,
		196,  23,  73, 236, 127,  12, 111, 246,
		108, 161,  59,  82,  41, 157,  85, 170,
		251,  96, 134, 177, 187, 204,  62,  90,
		203,  89,  95, 176, 156, 169, 160,  81,
		 11, 245,  22, 235, 122, 117,  44, 215,
		 79, 174, 213, 233, 230, 231, 173, 232,
		116, 214, 244, 234, 168,  80,  88, 175,
	);


	private $qr_exp = array(
		  1,   2,   4,   8,  16,  32,  64, 128,
		 29,  58, 116, 232, 205, 135,  19,  38,
		 76, 152,  45,  90, 180, 117, 234, 201,
		143,   3,   6,  12,  24,  48,  96, 192,
		157,  39,  78, 156,  37,  74, 148,  53,
		106, 212, 181, 119, 238, 193, 159,  35,
		 70, 140,   5,  10,  20,  40,  80, 160,
		 93, 186, 105, 210, 185, 111, 222, 161,
		 95, 190,  97, 194, 153,  47,  94, 188,
		101, 202, 137,  15,  30,  60, 120, 240,
		253, 231, 211, 187, 107, 214, 177, 127,
		254, 225, 223, 163,  91, 182, 113, 226,
		217, 175,  67, 134,  17,  34,  68, 136,
		 13,  26,  52, 104, 208, 189, 103, 206,
		129,  31,  62, 124, 248, 237, 199, 147,
		 59, 118, 236, 197, 151,  51, 102, 204,
		133,  23,  46,  92, 184, 109, 218, 169,
		 79, 158,  33,  66, 132,  21,  42,  84,
		168,  77, 154,  41,  82, 164,  85, 170,
		 73, 146,  57, 114, 228, 213, 183, 115,
		230, 209, 191,  99, 198, 145,  63, 126,
		252, 229, 215, 179, 123, 246, 241, 255,
		227, 219, 171,  75, 150,  49,  98, 196,
		149,  55, 110, 220, 165,  87, 174,  65,
		130,  25,  50, 100, 200, 141,   7,  14,
		 28,  56, 112, 224, 221, 167,  83, 166,
		 81, 162,  89, 178, 121, 242, 249, 239,
		195, 155,  43,  86, 172,  69, 138,   9,
		 18,  36,  72, 144,  61, 122, 244, 245,
		247, 243, 251, 235, 203, 139,  11,  22,
		 44,  88, 176, 125, 250, 233, 207, 131,
		 27,  54, 108, 216, 173,  71, 142,   1,
	);


	private $qr_remainder_bits = array(
		0, 7, 7, 7, 7, 7, 0, 0, 0, 0, 0, 0, 0, 3, 3, 3, 3, 3, 3, 3,
		4, 4, 4, 4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 0, 0, 0, 0, 0, 0,
	);


	private $qr_alignment_patterns = array(
		array(6, 18),
		array(6, 22),
		array(6, 26),
		array(6, 30),
		array(6, 34),
		array(6, 22, 38),
		array(6, 24, 42),
		array(6, 26, 46),
		array(6, 28, 50),
		array(6, 30, 54),
		array(6, 32, 58),
		array(6, 34, 62),
		array(6, 26, 46, 66),
		array(6, 26, 48, 70),
		array(6, 26, 50, 74),
		array(6, 30, 54, 78),
		array(6, 30, 56, 82),
		array(6, 30, 58, 86),
		array(6, 34, 62, 90),
		array(6, 28, 50, 72,  94),
		array(6, 26, 50, 74,  98),
		array(6, 30, 54, 78, 102),
		array(6, 28, 54, 80, 106),
		array(6, 32, 58, 84, 110),
		array(6, 30, 58, 86, 114),
		array(6, 34, 62, 90, 118),
		array(6, 26, 50, 74,  98, 122),
		array(6, 30, 54, 78, 102, 126),
		array(6, 26, 52, 78, 104, 130),
		array(6, 30, 56, 82, 108, 134),
		array(6, 34, 60, 86, 112, 138),
		array(6, 30, 58, 86, 114, 142),
		array(6, 34, 62, 90, 118, 146),
		array(6, 30, 54, 78, 102, 126, 150),
		array(6, 24, 50, 76, 102, 128, 154),
		array(6, 28, 54, 80, 106, 132, 158),
		array(6, 32, 58, 84, 110, 136, 162),
		array(6, 26, 54, 82, 110, 138, 166),
		array(6, 30, 58, 86, 114, 142, 170),
	);


	/*  format info string = $qr_format_info[            */
	/*    (0 for L, 8 for M, 16 for Q, 24 for H) + mask  */
	/*  ];                                               */
	private $qr_format_info = array(
		array( 1, 1, 1, 0, 1, 1, 1, 1, 1, 0, 0, 0, 1, 0, 0 ),
		array( 1, 1, 1, 0, 0, 1, 0, 1, 1, 1, 1, 0, 0, 1, 1 ),
		array( 1, 1, 1, 1, 1, 0, 1, 1, 0, 1, 0, 1, 0, 1, 0 ),
		array( 1, 1, 1, 1, 0, 0, 0, 1, 0, 0, 1, 1, 1, 0, 1 ),
		array( 1, 1, 0, 0, 1, 1, 0, 0, 0, 1, 0, 1, 1, 1, 1 ),
		array( 1, 1, 0, 0, 0, 1, 1, 0, 0, 0, 1, 1, 0, 0, 0 ),
		array( 1, 1, 0, 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1 ),
		array( 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 1, 0, 1, 1, 0 ),
		array( 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0 ),
		array( 1, 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 1 ),
		array( 1, 0, 1, 1, 1, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0 ),
		array( 1, 0, 1, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1 ),
		array( 1, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 1, 0, 0, 1 ),
		array( 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 1, 1, 0 ),
		array( 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 1, 1, 1 ),
		array( 1, 0, 0, 1, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0 ),
		array( 0, 1, 1, 0, 1, 0, 1, 0, 1, 0, 1, 1, 1, 1, 1 ),
		array( 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0, 0, 0 ),
		array( 0, 1, 1, 1, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 1 ),
		array( 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0 ),
		array( 0, 1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, 0 ),
		array( 0, 1, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1 ),
		array( 0, 1, 0, 1, 1, 1, 0, 1, 1, 0, 1, 1, 0, 1, 0 ),
		array( 0, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 1, 0, 1 ),
		array( 0, 0, 1, 0, 1, 1, 0, 1, 0, 0, 0, 1, 0, 0, 1 ),
		array( 0, 0, 1, 0, 0, 1, 1, 1, 0, 1, 1, 1, 1, 1, 0 ),
		array( 0, 0, 1, 1, 1, 0, 0, 1, 1, 1, 0, 0, 1, 1, 1 ),
		array( 0, 0, 1, 1, 0, 0, 1, 1, 1, 0, 1, 0, 0, 0, 0 ),
		array( 0, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 0, 0, 1, 0 ),
		array( 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0, 1, 0, 1 ),
		array( 0, 0, 0, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 0, 0 ),
		array( 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 1, 1, 0, 1, 1 ),
	);


	/*  version info string = $qr_version_info[ (version - 7) ]  */
	private $qr_version_info = array(
		array( 0, 0, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0 ),
		array( 0, 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0 ),
		array( 0, 0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1 ),
		array( 0, 0, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 0, 1, 0, 0, 1, 1 ),
		array( 0, 0, 1, 0, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 0 ),
		array( 0, 0, 1, 1, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 0, 0, 1, 0 ),
		array( 0, 0, 1, 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 0, 1, 1, 1 ),
		array( 0, 0, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1 ),
		array( 0, 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0, 0 ),
		array( 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0 ),
		array( 0, 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1 ),
		array( 0, 1, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 1 ),
		array( 0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 0 ),
		array( 0, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 0, 1, 0, 0, 1, 1, 0 ),
		array( 0, 1, 0, 1, 0, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 0, 1, 1 ),
		array( 0, 1, 0, 1, 1, 0, 1, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, 1 ),
		array( 0, 1, 0, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 0, 0 ),
		array( 0, 1, 1, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 0, 0, 1, 0, 0 ),
		array( 0, 1, 1, 0, 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 0, 1 ),
		array( 0, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 1, 0, 1, 0, 1, 1 ),
		array( 0, 1, 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 0, 1, 1, 1, 0 ),
		array( 0, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0 ),
		array( 0, 1, 1, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 1, 1, 1, 1, 1 ),
		array( 0, 1, 1, 1, 1, 0, 1, 1, 0, 1, 0, 1, 1, 1, 0, 1, 0, 1 ),
		array( 0, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 1, 0, 0, 0, 0 ),
		array( 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 1, 1, 0, 1, 0, 1, 0, 1 ),
		array( 1, 0, 0, 0, 0, 1, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0, 0 ),
		array( 1, 0, 0, 0, 1, 0, 1, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1, 0 ),
		array( 1, 0, 0, 0, 1, 1, 0, 1, 1, 1, 1, 0, 0, 1, 1, 1, 1, 1 ),
		array( 1, 0, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 0, 0, 1, 0, 1, 1 ),
		array( 1, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0 ),
		array( 1, 0, 0, 1, 1, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 1, 0, 0 ),
		array( 1, 0, 0, 1, 1, 1, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1 ),
		array( 1, 0, 1, 0, 0, 0, 1, 1, 0, 0, 0, 1, 1, 0, 1, 0, 0, 1 ),
	);


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>