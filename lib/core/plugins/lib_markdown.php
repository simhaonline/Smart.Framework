<?php
// [LIB - SmartFramework / Plugins / Markdown Parser]
// (c) 2006-2016 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT SEPARATE EXECUTION WITH VERSION CHECK
if((!defined('SMART_FRAMEWORK_VERSION')) || ((string)SMART_FRAMEWORK_VERSION != 'smart.framework.v.2.3')) {
	die('Invalid Framework Version in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Markdown Parser - Output HTML5 Code
// DEPENDS: SmartFramework
//======================================================


// This class is based on Parsedown by Emanuil Rusev, License: MIT
// [REGEX-SAFE-OK]

/**
 * Class: SmartMarkdownToHTML - Exports Markdown Code to HTML Code.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 *
 * @depends 	SmartFramework
 * @version 	v.160204
 * @package 	Exporters
 *
 */
final class SmartMarkdownToHTML {

	//===================================

	const version = 'v.1.5.4-r.160204@smart'; // with fixes from 1.5.1 -> 1.5.4

	//===================================

	//--
	private $breaksEnabled = true; // add <br> for text on multiple lines
	private $markupEscaped = true; // allow html tags or not
	private $urlsLinked = false; // use URLs
	private $htmlEntitiesEscaped = false; // escape HTML Entities such as &nbsp; (this is useful and normally should not be disabled)
	//--
	private $BlockTypes = array(
		'#' => array('Header'),
		'*' => array('Rule', 'List'),
		'+' => array('List'),
		'-' => array('SetextHeader', 'Table', 'Rule', 'List'),
		'0' => array('List'),
		'1' => array('List'),
		'2' => array('List'),
		'3' => array('List'),
		'4' => array('List'),
		'5' => array('List'),
		'6' => array('List'),
		'7' => array('List'),
		'8' => array('List'),
		'9' => array('List'),
		':' => array('Table'),
		'<' => array('Comment', 'Markup'),
		'=' => array('SetextHeader'),
		'>' => array('Quote'),
		'[' => array('Reference'),
		'_' => array('Rule'),
		'`' => array('FencedCode'),
		'|' => array('Table'),
		'~' => array('FencedCode'),
	);
//	private $DefinitionTypes = array(
//		'[' => array('Reference'),
//	); // removed since v.1.5.4
	private $unmarkedBlockTypes = array(
		'Code',
	);
	private $InlineTypes = array(
		'"' => array('SpecialCharacter'),
		'!' => array('Image'),
		'&' => array('SpecialCharacter'),
		'*' => array('Emphasis'),
		':' => array('Url'),
		'<' => array('UrlTag', 'EmailTag', 'Markup', 'SpecialCharacter'),
		'>' => array('SpecialCharacter'),
		'[' => array('Link'),
		'_' => array('Emphasis'),
		'`' => array('Code'),
		'~' => array('Strikethrough'),
		'\\' => array('EscapeSequence'),
	);
	private $inlineMarkerList = '!"*_&[:<>`~\\';
	private $DefinitionData;
	private $specialCharacters = array(
		'\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!', '|',
	);
	private $StrongRegex = array(
		'*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*[*])+?)[*]{2}(?![*])/s',
		'_' => '/^__((?:\\\\_|[^_]|_[^_]*_)+?)__(?!_)/us',
	);
	private $EmRegex = array(
		'*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
		'_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
	);
	private $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?';
	private $voidElements = array(
		'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',
	);
	private $textLevelElements = array(
		'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
		'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
		'i', 'rp', 'del', 'code',          'strike', 'marquee',
		'q', 'rt', 'ins', 'font',          'strong',
		's', 'tt', 'sub', 'mark',
		'u', 'xm', 'sup', 'nobr',
				   'var', 'ruby',
				   'wbr', 'span',
						  'time',
	);
	//-- extra
	private $regexAttribute = '[ ]*{((?:[#\.@][_a-zA-Z0-9,\-\=\$\:;]+[ ]*)+)}';
	//--

	//===================================


	public function __construct($y_breaksEnabled=true, $y_markupEscaped=true, $y_urlsLinked=true, $y_htmlEntitiesEscaped=false) {
		//--
		$this->breaksEnabled 		= (bool) $y_breaksEnabled;
		$this->markupEscaped 		= (bool) $y_markupEscaped;
		$this->urlsLinked 			= (bool) $y_urlsLinked;
		$this->htmlEntitiesEscaped 	= (bool) $y_htmlEntitiesEscaped;
		//--
	} //END FUNCTION


	public function text($text) {
		//-- make sure no definitions are set
		$this->DefinitionData = array();
		//-- standardize line breaks
		$text = str_replace(array("\r\n", "\r"), "\n", $text);
		//-- remove surrounding line breaks
		$text = trim($text, "\n");
		//-- split text into lines
		$lines = explode("\n", $text);
		//-- iterate through lines to identify blocks
		$markup = $this->lines($lines);
		//-- trim line breaks
		$markup = trim($markup, "\n");
		//--
		$markup = $this->prepareHTML($markup);
		//--
		return (string) $markup;
		//--
	} //END FUNCTION


	//-- # Blocks

	private function prepareHTML($markup) {
		//--
		if($this->breaksEnabled) {
			$info_linebreaks = 'B:1';
		} else {
			$info_linebreaks = 'B:0';
		} //end if else
		if($this->markupEscaped) {
			$info_markup = 'M:0';
		} else {
			$info_markup = 'M:1';
		} //end if else
		if($this->urlsLinked) {
			$info_urls = 'L:1';
		} else {
			$info_urls = 'L:0';
		} //end if else
		if($this->htmlEntitiesEscaped) {
			$info_entities = 'E:0';
		} else {
			$info_entities = 'E:1';
		} //end if else
		//-- it always add tags ...
		return $markup = "\n".'<!--  HTML/Markdown # ( '.Smart::escape_html($info_linebreaks.' '.$info_markup.' '.$info_urls.' '.$info_entities.' T:'.date('ymdHi')).' )  -->'."\n".'<div id="markdown">'."\n".$markup."\n".'</div>'."\n".'<!--  # HTML/Markdown # '.Smart::escape_html((string)self::version).' #  -->'."\n"; // if parsed and contain HTML Tags, add div and comments
		//--
	} //END FUNCTION


	private function lines(array $lines) {
		//--
		$CurrentBlock = null;
		//--
		foreach($lines as $line) {
			//--
			if(chop($line) === '') {
				//--
				if(isset($CurrentBlock)) {
					$CurrentBlock['interrupted'] = true;
				} //end if
				//--
				continue;
				//--
			} //end if
			//--
			if(strpos($line, "\t") !== false) {
				//--
				$parts = explode("\t", $line);
				//--
				$line = $parts[0];
				//--
				unset($parts[0]);
				//--
				foreach($parts as $part) {
					//--
					$shortage = 4 - SmartUnicode::str_len($line) % 4;
					//--
					$line .= str_repeat(' ', $shortage);
					$line .= $part;
					//--
				} //end foreach
				//--
			} //end if
			//--
			$indent = 0;
			//--
			while(isset($line[$indent]) AND $line[$indent] === ' ') {
				$indent ++;
			} //end while
			//--
			$text = $indent > 0 ? SmartUnicode::sub_str($line, $indent) : $line;
			//--
			$Line = array('body' => $line, 'indent' => $indent, 'text' => $text);
			//--
			//if(isset($CurrentBlock['incomplete'])) {
			if(isset($CurrentBlock['continuable'])) { // fix from 1.5.4
				//--
				$Block = $this->{'block'.$CurrentBlock['type'].'Continue'}($Line, $CurrentBlock);
				//--
				if(isset($Block)) {
					//--
					$CurrentBlock = $Block;
					//--
					continue;
					//--
				} else {
					//--
					if(method_exists($this, 'block'.$CurrentBlock['type'].'Complete')) {
						//--
						$CurrentBlock = $this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);
						//--
					} //end if
					//--
					//unset($CurrentBlock['incomplete']); // fix from 1.5.4
					//--
				} //end if else
				//--
			} //end if
			//--
			$marker = $text[0];
			//--
			$blockTypes = $this->unmarkedBlockTypes;
			//--
			if(isset($this->BlockTypes[$marker])) {
				//--
				foreach($this->BlockTypes[$marker] as $blockType) {
					$blockTypes[] = $blockType;
				} //end foreach
				//--
			} //end if
			//--
			foreach($blockTypes as $blockType) {
				//--
				$Block = $this->{'block'.$blockType}($Line, $CurrentBlock);
				//--
				if(isset($Block)) {
					//--
					$Block['type'] = $blockType;
					//--
					if(!isset($Block['identified'])) {
						$Blocks[] = $CurrentBlock;
						$Block['identified'] = true;
					} //end if
					//--
					if(method_exists($this, 'block'.$blockType.'Continue')) {
						//$Block['incomplete'] = true;
						$Block['continuable'] = true; // fix from 1.5.4
					} //end if
					//--
					$CurrentBlock = $Block;
					//--
					continue 2;
					//--
				} //end if
				//--
			} //end foreach
			//--
			if(isset($CurrentBlock) AND !isset($CurrentBlock['type']) AND !isset($CurrentBlock['interrupted'])) {
				//--
				$CurrentBlock['element']['text'] .= "\n".$text;
				//--
			} else {
				//--
				$Blocks[] = $CurrentBlock;
				//--
				$CurrentBlock = $this->paragraph($Line);
				$CurrentBlock['identified'] = true;
				//--
			} //end if else
			//--
		} //end foreach
		//--
		//if(isset($CurrentBlock['incomplete']) AND method_exists($this, 'block'.$CurrentBlock['type'].'Complete')) {
		if(isset($CurrentBlock['continuable']) AND method_exists($this, 'block'.$CurrentBlock['type'].'Complete')) { // fix from 1.5.4
			//--
			$CurrentBlock = $this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);
			//--
		} //end if
		//--
		$Blocks[] = $CurrentBlock;
		//--
		unset($Blocks[0]);
		//--
		$markup = '';
		//--
		foreach($Blocks as $Block) {
			//--
			if(isset($Block['hidden'])) {
				continue;
			} //end if
			//--
			$markup .= "\n";
			$markup .= isset($Block['markup']) ? $Block['markup'] : $this->element($Block['element']);
			//--
		} //end foreach
		//--
		$markup .= "\n";
		//--
		return $markup;
		//--
	} //END FUNCTION


	//-- # Code


	private function blockCode($Line, $Block = null) {
		//--
		if(isset($Block) AND !isset($Block['type']) AND !isset($Block['interrupted'])) {
			return;
		} //end if
		//--
		if($Line['indent'] >= 4) {
			//--
			$text = SmartUnicode::sub_str($Line['body'], 4);
			//--
			$Block = array(
				'element' => array(
					'name' => 'div', // pre
					'handler' => 'element',
					'text' => array(
						'name' => 'pre', // code
						'text' => $text,
					),
				),
			);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockCodeContinue($Line, $Block) {
		//--
		if($Line['indent'] >= 4) {
			//--
			if(isset($Block['interrupted'])) {
				//--
				$Block['element']['text']['text'] .= "\n";
				//--
				unset($Block['interrupted']);
				//--
			} //end if
			//--
			$Block['element']['text']['text'] .= "\n";
			//--
			$text = SmartUnicode::sub_str($Line['body'], 4);
			//--
			$Block['element']['text']['text'] .= $text;
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockCodeComplete($Block) {
		//--
		$text = $Block['element']['text']['text'];
		$text = Smart::escape_html($text); // fix from: html special chars ENT_NOQUOTES UTF-8
		//--
		$Block['element']['text']['text'] = $text;
		//--
		return $Block;
		//--
	} //END FUNCTION


	//-- # Comment


	private function blockComment($Line) {
		//--
		if($this->markupEscaped) {
			return;
		} //end if
		//--
		if(isset($Line['text'][3]) AND $Line['text'][3] === '-' AND $Line['text'][2] === '-' AND $Line['text'][1] === '!') {
			//--
			$Block = array(
				'markup' => $Line['body'],
			);
			//--
			if(preg_match('/-->$/', $Line['text'])) {
				$Block['closed'] = true;
			} //end if
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockCommentContinue($Line, array $Block) {
		//--
		if(isset($Block['closed'])) {
			return;
		} //end if
		//--
		$Block['markup'] .= "\n" . $Line['body'];
		//--
		if(preg_match('/-->$/', $Line['text'])) {
			$Block['closed'] = true;
		} //end if
		//--
		return $Block;
		//--
	} //END FUNCTION


	//-- # Fenced Code


	private function blockFencedCode($Line) {
		//--
		//if(preg_match('/^(['.$Line['text'][0].']{3,})[ ]*([\w-]+)?[ ]*$/', $Line['text'], $matches)) {
		if(preg_match('/^['.$Line['text'][0].']{3,}[ ]*([\w-]+)?[ ]*$/', $Line['text'], $matches)) { // fix from 1.5.4
			//--
			$Element = array(
				'name' => 'code',
				'text' => '',
			);
			//--
			//if(isset($matches[2])) {
			if(isset($matches[1])) { // fix from 1.5.4
				//--
				//$class = 'language-'.$matches[2];
				$class = 'language-'.$matches[1]; // fix from 1.5.4
				//--
				$Element['attributes'] = array(
					'class' => $class,
				);
				//--
			} //end if
			//--
			$Block = array(
				'char' => $Line['text'][0],
				'element' => array(
					'name' => 'pre',
					'handler' => 'element',
					'text' => $Element,
				),
			);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockFencedCodeContinue($Line, $Block) {
		//--
		if(isset($Block['complete'])) {
			return;
		} //end if
		//--
		if(isset($Block['interrupted'])) {
			//--
			$Block['element']['text']['text'] .= "\n";
			//--
			unset($Block['interrupted']);
			//--
		} //end if
		//--
		if(preg_match('/^'.$Block['char'].'{3,}[ ]*$/', $Line['text'])) {
			//--
			$Block['element']['text']['text'] = SmartUnicode::sub_str($Block['element']['text']['text'], 1);
			$Block['complete'] = true;
			//--
			return $Block;
			//--
		} //end if
		//--
		$Block['element']['text']['text'] .= "\n".$Line['body'];;
		//--
		return $Block;
		//--
	} //END FUNCTION


	private function blockFencedCodeComplete($Block) {
		//--
		$text = $Block['element']['text']['text'];
		$text = Smart::escape_html($text); // fix from: html special chars ENT_NOQUOTES UTF-8
		//--
		$Block['element']['text']['text'] = $text;
		//--
		return $Block;
		//--
	} //END FUNCTION


	//-- # Header


	private function blockHeader($Line) {
		//--
		if(isset($Line['text'][1])) {
			//--
			$level = 1;
			//--
			while(isset($Line['text'][$level]) AND $Line['text'][$level] === '#') {
				$level ++;
			} //end while
			//--
			if($level > 6) {
				return;
			} //end if
			//--
			$text = trim($Line['text'], '# ');
			//--
			$Block = array(
				'element' => array(
					'name' => 'h' . min(6, $level),
					'text' => $text,
					'handler' => 'line',
				),
			);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # List


	private function blockList($Line) {
		//--
		list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]+[.]');
		//--
		if(preg_match('/^('.$pattern.'[ ]+)(.*)/', $Line['text'], $matches)) {
			//--
			$Block = array(
				'indent' => $Line['indent'],
				'pattern' => $pattern,
				'element' => array(
					'name' => $name,
					'handler' => 'elements',
				),
			);
			//--
			$Block['li'] = array(
				'name' => 'li',
				'handler' => 'li',
				'text' => array(
					$matches[2],
				),
			);
			//--
			$Block['element']['text'][]= & $Block['li'];
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockListContinue($Line, array $Block) {
		//--
		if($Block['indent'] === $Line['indent'] AND preg_match('/^'.$Block['pattern'].'(?:[ ]+(.*)|$)/', $Line['text'], $matches)) {
			//--
			if(isset($Block['interrupted'])) {
				//--
				$Block['li']['text'][]= '';
				//--
				unset($Block['interrupted']);
				//--
			} //end if
			//--
			unset($Block['li']);
			//--
			$text = isset($matches[1]) ? $matches[1] : '';
			//--
			$Block['li'] = array(
				'name' => 'li',
				'handler' => 'li',
				'text' => array(
					$text,
				),
			);
			//--
			$Block['element']['text'][]= & $Block['li'];
			//--
			return $Block;
			//--
		} //end if
		//--
		if($Line['text'][0] === '[' AND $this->blockReference($Line)) {
			return $Block;
		} //end if
		//--
		if(!isset($Block['interrupted'])) {
			//--
			$text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);
			//--
			$Block['li']['text'][]= $text;
			//--
			return $Block;
			//--
		} //end if
		//--
		if($Line['indent'] > 0) {
			//--
			$Block['li']['text'][]= '';
			//--
			$text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);
			//--
			$Block['li']['text'][]= $text;
			//--
			unset($Block['interrupted']);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # Quote


	private function blockQuote($Line) {
		//--
		if(preg_match('/^>[ ]?(.*)/', $Line['text'], $matches)) {
			//--
			$Block = array(
				'element' => array(
					'name' => 'blockquote',
					'handler' => 'lines',
					'text' => (array) $matches[1],
				),
			);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockQuoteContinue($Line, array $Block) {
		//--
		if($Line['text'][0] === '>' AND preg_match('/^>[ ]?(.*)/', $Line['text'], $matches)) {
			//--
			if(isset($Block['interrupted'])) {
				//--
				$Block['element']['text'][]= '';
				//--
				unset($Block['interrupted']);
				//--
			} //end if
			//--
			$Block['element']['text'][]= $matches[1];
			//--
			return $Block;
			//--
		} //end if
		//--
		if(!isset($Block['interrupted'])) {
			//--
			$Block['element']['text'][]= $Line['text'];
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # Rule


	private function blockRule($Line) {
		//--
		if(preg_match('/^(['.$Line['text'][0].'])([ ]*\1){2,}[ ]*$/', $Line['text'])) {
			//--
			$Block = array(
				'element' => array(
					'name' => 'hr'
				),
			);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # Setext


	private function blockSetextHeader($Line, array $Block = null) {
		//--
		if(!isset($Block) OR isset($Block['type']) OR isset($Block['interrupted'])) {
			return;
		} //end if
		//--
		if(chop($Line['text'], $Line['text'][0]) === '') {
			//--
			$Block['element']['name'] = $Line['text'][0] === '=' ? 'h1' : 'h2';
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # Markup


	private function blockMarkup($Line) {
		//--
		if($this->markupEscaped) {
			return;
		} //end if
		//--
		if(preg_match('/^<(\w*)(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*(\/)?>/', $Line['text'], $matches)) {
			//--
			//if(in_array($matches[1], $this->textLevelElements)) {
			if(in_array(strtolower($matches[1]), $this->textLevelElements)) { // fix from 1.5.4
				return;
			} //end if
			//--
			$Block = array(
				'name' => $matches[1],
				'depth' => 0,
				'markup' => $Line['text'],
			);
			//--
			$length = strlen($matches[0]);
			//--
			$remainder = SmartUnicode::sub_str($Line['text'], $length);
			//--
			if(trim($remainder) === '') {
				//--
				if(isset($matches[2]) OR in_array($matches[1], $this->voidElements)) {
					//--
					$Block['closed'] = true;
					$Block['void'] = true;
					//--
				} //end if
				//--
			} else {
				//--
				if(isset($matches[2]) OR in_array($matches[1], $this->voidElements)) {
					return;
				} //end if
				//--
				if(preg_match('/<\/'.$matches[1].'>[ ]*$/i', $remainder)) {
					$Block['closed'] = true;
				} //end if
				//--
			} //end if else
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockMarkupContinue($Line, array $Block) {
		//--
		if(isset($Block['closed'])) {
			return;
		} //end if
		//--
		if(preg_match('/^<'.$Block['name'].'(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*>/i', $Line['text'])) { // open
			$Block['depth'] ++;
		} //end if
		//--
		if(preg_match('/(.*?)<\/'.$Block['name'].'>[ ]*$/i', $Line['text'], $matches)) { // close
			//--
			if($Block['depth'] > 0) {
				$Block['depth'] --;
			} else {
				$Block['closed'] = true;
			} //end if else
			//--
		} //end if
		//--
		if(isset($Block['interrupted'])) {
			//--
			$Block['markup'] .= "\n";
			//--
			unset($Block['interrupted']);
			//--
		} //end if
		//--
		$Block['markup'] .= "\n".$Line['body'];
		//--
		return $Block;
		//--
	} //END FUNCTION


	//-- # Reference


	private function blockReference($Line) {
		//--
		if(preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*$/', $Line['text'], $matches)) {
			//--
			$id = strtolower($matches[1]);
			//--
			$Data = array(
				'url' => $matches[2],
				'title' => null,
			);
			//--
			if(isset($matches[3])) {
				$Data['title'] = $matches[3];
			} //end if
			//--
			$this->DefinitionData['Reference'][$id] = $Data;
			//--
			$Block = array(
				'hidden' => true,
			);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # Table


	private function blockTable($Line, array $Block = null) {
		//--
		if(!isset($Block) OR isset($Block['type']) OR isset($Block['interrupted'])) {
			return;
		} //end if
		//--
		if((strpos($Block['element']['text'], '|') !== false) AND (chop($Line['text'], ' -:|') === '')) {
			//--
			$alignments = array();
			//--
			$divider = $Line['text'];
			$divider = trim($divider);
			$divider = trim($divider, '|');
			//--
			$dividerCells = explode('|', $divider);
			//--
			foreach($dividerCells as $dividerCell) {
				//--
				$dividerCell = trim($dividerCell);
				//--
				if($dividerCell === '') {
					continue;
				} //end if
				//--
				$alignment = null;
				//--
				if($dividerCell[0] === ':') {
					$alignment = 'left';
				} //end if
				//--
				if(SmartUnicode::sub_str($dividerCell, - 1) === ':') {
					$alignment = $alignment === 'left' ? 'center' : 'right';
				} //end if
				//--
				$alignments[] = $alignment;
				//--
			} //end foreach
			//--
			$HeaderElements = array();
			//--
			$header = $Block['element']['text'];
			$header = trim($header);
			$header = trim($header, '|');
			//--
			$headerCells = explode('|', $header);
			//--
			foreach($headerCells as $index => $headerCell) {
				//--
				$headerCell = trim($headerCell);
				//--
				$HeaderElement = array(
					'name' => 'th',
					'handler' => 'line',
				);
				//-- unixman
				$matches = array();
				if(preg_match('/'.$this->regexAttribute.'/', $headerCell, $matches)) {
					if(!is_array($HeaderElement['attributes'])) {
						$HeaderElement['attributes'] = array();
					} //end if
					$HeaderElement['attributes'] += $this->parseAttributeData($matches[1]);
					$headerCell = trim(SmartUnicode::sub_str($headerCell, 0, (SmartUnicode::str_len($headerCell) - SmartUnicode::str_len($matches[1]) - 2)));
				} //end if
				//-- # end unixman
				$HeaderElement['text'] = $headerCell;
				//--
				if(isset($alignments[$index])) {
					//--
					$alignment = $alignments[$index];
					//--
					if(!is_array($HeaderElement['attributes'])) {
						$HeaderElement['attributes'] = array();
					} //end if
					$HeaderElement['attributes']['style'] = 'text-align: '.$alignment.';';
					//--
				} //end if
				//--
				$HeaderElements[] = $HeaderElement;
				//--
			} //end foreach
			//--
			$Block = array(
				'alignments' => $alignments,
				'identified' => true,
				'element' => array(
					'name' => 'table',
					'handler' => 'elements',
				),
			);
			//--
			$Block['element']['text'][]= array(
				'name' => 'thead',
				'handler' => 'elements',
			);
			//--
			$Block['element']['text'][]= array(
				'name' => 'tbody',
				'handler' => 'elements',
				'text' => array(),
			);
			//--
			$Block['element']['text'][0]['text'][]= array(
				'name' => 'tr',
				'handler' => 'elements',
				'text' => $HeaderElements,
			);
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function blockTableContinue($Line, array $Block) {
		//--
		if(isset($Block['interrupted'])) {
			return;
		} //end if
		//--
		if(($Line['text'][0] === '|') OR (strpos($Line['text'], '|'))) { // here strpos must not check with true/false, because the first character is already checked and must not be checked again
			//--
			$Elements = array();
			//--
			$row = $Line['text'];
			$row = trim($row);
			$row = trim($row, '|');
			//--
			preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/', $row, $matches);
			//--
			foreach($matches[0] as $index => $cell) {
				//--
				$cell = trim($cell);
				//--
				$Element = array(
					'name' => 'td',
					'handler' => 'line',
				);
				//-- unixman
				$matches = array();
				if(preg_match('/'.$this->regexAttribute.'/', $cell, $matches)) {
					if(!is_array($Element['attributes'])) {
						$Element['attributes'] = array();
					} //end if
					$Element['attributes'] += $this->parseAttributeData($matches[1]);
					$cell = trim(SmartUnicode::sub_str($cell, 0, (SmartUnicode::str_len($cell) - SmartUnicode::str_len($matches[1]) - 2)));
				} //end if
				//-- # end unixman
				$Element['text'] = $cell;
				//--
				if(isset($Block['alignments'][$index])) {
					if(!is_array($Element['attributes'])) {
						$Element['attributes'] = array();
					} //end if
					$Element['attributes']['style'] = 'text-align: '.$Block['alignments'][$index].';';
				} //end if
				//--
				$Elements[] = $Element;
				//--
			} //end foreach
			//--
			$Element = array(
				'name' => 'tr',
				'handler' => 'elements',
				'text' => $Elements,
			);
			//--
			$Block['element']['text'][1]['text'][]= $Element;
			//--
			return $Block;
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # ~


	private function paragraph($Line) {
		//--
		$Block = array(
			'element' => array(
				'name' => 'p',
				'text' => $Line['text'],
				'handler' => 'line',
			),
		);
		//--
		return $Block;
		//--
	} //END FUNCTION


	//-- # Inline Elements


	public function line($text) { // fixed from v.1.5.4
		/*
		//--
		$markup = '';
		//--
		$unexaminedText = $text;
		//--
		$markerPosition = 0;
		//--
		while($excerpt = strpbrk($unexaminedText, $this->inlineMarkerList)) {
			//--
			$marker = $excerpt[0];
			//--
			$markerPosition += SmartUnicode::str_pos($unexaminedText, $marker); // because it uses safe unicode strlen this is used to calculate string intervals and must be unicode safe strpos
			//--
			$Excerpt = array('text' => $excerpt, 'context' => $text);
			//--
			foreach($this->InlineTypes[$marker] as $inlineType) {
				//--
				$Inline = $this->{'inline'.$inlineType}($Excerpt);
				//--
				if(!isset($Inline)) {
					continue;
				} //end if
				//--
				if(isset($Inline['position']) AND $Inline['position'] > $markerPosition) { // position is ahead of marker
					continue;
				} //end if
				//--
				if(!isset($Inline['position'])) {
					$Inline['position'] = $markerPosition;
				} //end if
				//--
				$unmarkedText = SmartUnicode::sub_str($text, 0, $Inline['position']);
				//--
				$markup .= $this->unmarkedText($unmarkedText);
				$markup .= isset($Inline['markup']) ? $Inline['markup'] : $this->element($Inline['element']);
				//--
				$text = SmartUnicode::sub_str($text, $Inline['position'] + $Inline['extent']);
				//--
				$unexaminedText = $text;
				//--
				$markerPosition = 0;
				//--
				continue 2;
				//--
			} //end foreach
			//--
			$unexaminedText = SmartUnicode::sub_str($excerpt, 1);
			//--
			$markerPosition ++;
			//--
		} //end while
		//--
		$markup .= $this->unmarkedText($text);
		//--
		*/
		//--
		$markup = '';
		//--
		while($excerpt = strpbrk($text, $this->inlineMarkerList)) { // $excerpt is based on the first occurrence of a marker
			//--
			$marker = $excerpt[0];
			//--
			//$markerPosition = strpos($text, $marker);
			$markerPosition = SmartUnicode::str_pos($text, $marker); // fix by unixman :: because it uses safe unicode strlen this is used to calculate string intervals and must be unicode safe strpos
			//--
			$Excerpt = array('text' => $excerpt, 'context' => $text);
			//--
			foreach ($this->InlineTypes[$marker] as $inlineType) {
				//--
				$Inline = $this->{'inline'.$inlineType}($Excerpt);
				//--
				if(!isset($Inline)) {
					continue;
				} //end if
				//-- makes sure that the inline belongs to "our" marker
				if(isset($Inline['position']) and $Inline['position'] > $markerPosition) {
					continue;
				} //end if
				//-- sets a default inline position
				if(!isset($Inline['position'])) {
					$Inline['position'] = $markerPosition;
				} //end if
				//-- the text that comes before the inline
				//$unmarkedText = substr($text, 0, $Inline['position']);
				$unmarkedText = SmartUnicode::sub_str($text, 0, $Inline['position']); // fix by unixman
				//-- compile the unmarked text
				$markup .= $this->unmarkedText($unmarkedText);
				//-- compile the inline
				$markup .= isset($Inline['markup']) ? $Inline['markup'] : $this->element($Inline['element']);
				//-- remove the examined text
				//$text = substr($text, $Inline['position'] + $Inline['extent']);
				$text = SmartUnicode::sub_str($text, $Inline['position'] + $Inline['extent']); // fix by unixman
				//--
				continue 2;
				//--
			} //end foreach
			//-- the marker does not belong to an inline
			//$unmarkedText = substr($text, 0, $markerPosition + 1);
			$unmarkedText = SmartUnicode::sub_str($text, 0, $markerPosition + 1); // fix by unixman
			//--
			$markup .= $this->unmarkedText($unmarkedText);
			//--
			//$text = substr($text, $markerPosition + 1);
			$text = SmartUnicode::sub_str($text, $markerPosition + 1); // fix by unixman
			//--
		} //end while
		//--
		$markup .= $this->unmarkedText($text);
		//--
		return $markup;
		//--
	} //END FUNCTION


	//-- # ~


	private function inlineCode($Excerpt) {
		//--
		$marker = $Excerpt['text'][0];
		//--
		if(preg_match('/^('.$marker.'+)[ ]*(.+?)[ ]*(?<!'.$marker.')\1(?!'.$marker.')/s', $Excerpt['text'], $matches)) {
			//--
			$text = $matches[2];
			$text = Smart::escape_html($text); // fix from: html special chars ENT_NOQUOTES UTF-8
			$text = preg_replace("/[ ]*\n/", ' ', $text);
			//--
			return array(
				'extent' => strlen($matches[0]),
				'element' => array(
					'name' => 'code',
					'text' => $text,
				),
			);
			//--
		} //end if
		//--
	} //END FUNCTION


	private function inlineEmailTag($Excerpt) {
		//-- unixman
		if($this->urlsLinked !== true) {
			return;
		} //end if
		//-- #end unixman
		if((strpos($Excerpt['text'], '>') !== false) AND preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $Excerpt['text'], $matches)) {
			//--
			$url = $matches[1];
			//--
			if(!isset($matches[2])) {
				$url = 'mailto:' . $url;
			} //end if
			//--
			return array(
				'extent' => strlen($matches[0]),
				'element' => array(
					'name' => 'a',
					'text' => $matches[1],
					'attributes' => array(
						'href' => $url,
					),
				),
			);
			//--
		} //end if
		//--
	} //END FUNCTION


	private function inlineEmphasis($Excerpt) {
		//--
		if(!isset($Excerpt['text'][1])) {
			return;
		} //end if
		//--
		$marker = $Excerpt['text'][0];
		//--
		if($Excerpt['text'][1] === $marker AND preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches)) {
			$emphasis = 'b'; // 'strong';
		} elseif (preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches)) {
			$emphasis = 'i'; // 'em';
		} else {
			return;
		} //end if else
		//--
		return array(
			'extent' => strlen($matches[0]),
			'element' => array(
				'name' => $emphasis,
				'handler' => 'line',
				'text' => $matches[1],
			),
		);
		//--
	} //END FUNCTION


	private function inlineEscapeSequence($Excerpt) {
		//--
		if(isset($Excerpt['text'][1]) AND in_array($Excerpt['text'][1], $this->specialCharacters)) {
			return array(
				'markup' => $Excerpt['text'][1],
				'extent' => 2,
			);
		} //end if
		//--
	} //END FUNCTION


	private function inlineImage($Excerpt) {
		//--
		if(!isset($Excerpt['text'][1]) OR $Excerpt['text'][1] !== '[') {
			return;
		} //end if
		//--
		$Excerpt['text']= SmartUnicode::sub_str($Excerpt['text'], 1);
		//--
		$Link = $this->inlineLink($Excerpt);
		//--
		if($Link === null) {
			return;
		} //end if
		//--
		$Inline = array(
			'extent' => $Link['extent'] + 1,
			'element' => array(
				'name' => 'img',
				'attributes' => array(
					'src' => $Link['element']['attributes']['href'],
					'alt' => $Link['element']['text'],
				),
			),
		);
		//--
		$Inline['element']['attributes'] += $Link['element']['attributes'];
		//--
		unset($Inline['element']['attributes']['href']);
		//--
		return $Inline;
		//--
	} //END FUNCTION


	private function inlineLink($Excerpt) {
		//-- unixman
		if($this->urlsLinked !== true) {
			return;
		} //end if
		//-- #end unixman
		$Element = array(
			'name' => 'a',
			'handler' => 'line',
			'text' => null,
			'attributes' => array(
				'href' => null,
				'title' => null,
			),
		);
		//--
		$extent = 0;
		//--
		$remainder = $Excerpt['text'];
		//--
		if(preg_match('/\[((?:[^][]|(?R))*)\]/', $remainder, $matches)) {
			//--
			$Element['text'] = $matches[1];
			//--
			$extent += strlen($matches[0]);
			//--
			$remainder = SmartUnicode::sub_str($remainder, $extent);
			//--
		} else {
			//--
			return;
			//--
		} //end if else
		//--
		//if(preg_match('/^[(]((?:[^ ()]|[(][^ )]+[)])+)(?:[ ]+("[^"]+"|\'[^\']+\'))?[)]/', $remainder, $matches)) {
		if(preg_match('/^[(]((?:[^ ()]|[(][^ )]+[)])+)(?:[ ]+("[^"]*"|\'[^\']*\'))?[)]/', $remainder, $matches)) { // fix from 1.5.4
			//--
			$Element['attributes']['href'] = $matches[1];
			//--
			if(isset($matches[2])) {
				$Element['attributes']['title'] = SmartUnicode::sub_str($matches[2], 1, -1);
			} //end if
			//--
			$extent += strlen($matches[0]);
			//--
		} else {
			//--
			if(preg_match('/^\s*\[(.*?)\]/', $remainder, $matches)) {
				//--
				//$definition = $matches[1] ? $matches[1] : $Element['text'];
				$definition = strlen($matches[1]) ? $matches[1] : $Element['text']; // fix from 1.5.4
				$definition = strtolower($definition);
				//--
				$extent += strlen($matches[0]);
				//--
			} else {
				//--
				$definition = strtolower($Element['text']);
				//--
			} //end if else
			//--
			if(!isset($this->DefinitionData['Reference'][$definition])) {
				return;
			} //end if
			//--
			$Definition = $this->DefinitionData['Reference'][$definition];
			//--
			$Element['attributes']['href'] = $Definition['url'];
			$Element['attributes']['title'] = $Definition['title'];
			//--
		} //end if else
		//--
		$Element['attributes']['href'] = str_replace(array('&', '<'), array('&amp;', '&lt;'), $Element['attributes']['href']);
		//-- unixman (extra)
		$remainder = substr($Excerpt['text'], $Element['extent']);
		$matches = array();
		if(preg_match('/'.$this->regexAttribute.'/', $remainder, $matches)) {
			$Element['attributes'] += $this->parseAttributeData($matches[1]);
			$extent += strlen($matches[0]);
		} //end if
		//-- #end unixman
		return array(
			'extent' => $extent,
			'element' => $Element,
		);
		//--
	} //END FUNCTION


	private function inlineMarkup($Excerpt) {
		//--
		if($this->markupEscaped OR (strpos($Excerpt['text'], '>') === false)) {
			return;
		} //end if
		//--
		if(($Excerpt['text'][1] === '/') AND preg_match('/^<\/\w*[ ]*>/s', $Excerpt['text'], $matches)) {
			return array(
				'markup' => $matches[0],
				'extent' => strlen($matches[0]),
			);
		} //end if
		//--
		if(($Excerpt['text'][1] === '!') AND preg_match('/^<!---?[^>-](?:-?[^-])*-->/s', $Excerpt['text'], $matches)) {
			return array(
				'markup' => $matches[0],
				'extent' => strlen($matches[0]),
			);
		} //end if
		//--
		if(($Excerpt['text'][1] !== ' ') AND preg_match('/^<\w*(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*\/?>/s', $Excerpt['text'], $matches)) {
			return array(
				'markup' => $matches[0],
				'extent' => strlen($matches[0]),
			);
		} //end if
		//--
	} //END FUNCTION


	private function inlineSpecialCharacter($Excerpt) {
		//--
		if($Excerpt['text'][0] === '&' AND !preg_match('/^&#?\w+;/', $Excerpt['text'])) {
			return array(
				'markup' => '&amp;',
				'extent' => 1,
			);
		} //end if
		//--
		$SpecialCharacter = array('>' => 'gt', '<' => 'lt', '"' => 'quot');
		//-- #unixman fix
		if($this->markupEscaped) {
			if($this->htmlEntitiesEscaped) {
				$SpecialCharacter['&'] = 'amp';
			} //end if
		} //end if
		//-- #end unixman
		if(isset($SpecialCharacter[$Excerpt['text'][0]])) {
			return array(
				'markup' => '&'.$SpecialCharacter[$Excerpt['text'][0]].';',
				'extent' => 1,
			);
		} //end if
		//--
	} //END FUNCTION


	private function inlineStrikethrough($Excerpt) {
		//--
		if(!isset($Excerpt['text'][1])) {
			return;
		} //end if
		//--
		if($Excerpt['text'][1] === '~' AND preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $Excerpt['text'], $matches)) {
			return array(
				'extent' => strlen($matches[0]),
				'element' => array(
					'name' => 'del',
					'text' => $matches[1],
					'handler' => 'line',
				),
			);
		} //end if
		//--
	} //END FUNCTION


	private function inlineUrl($Excerpt) {
		//--
		if($this->urlsLinked !== true OR !isset($Excerpt['text'][2]) OR $Excerpt['text'][2] !== '/') {
			return;
		} //end if
		//--
		if(preg_match('/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui', $Excerpt['context'], $matches, PREG_OFFSET_CAPTURE)) {
			//--
			$Inline = array(
				'extent' => strlen($matches[0][0]),
				'position' => $matches[0][1],
				'element' => array(
					'name' => 'a',
					'text' => $matches[0][0],
					'attributes' => array(
						'href' => $matches[0][0],
					),
				),
			);
			//--
			return $Inline;
			//--
		} //end if
		//--
	} //END FUNCTION


	private function inlineUrlTag($Excerpt) {
		//-- unixman
		if($this->urlsLinked !== true) {
			return;
		} //end if
		//-- #end unixman
		if((strpos($Excerpt['text'], '>') !== false) AND preg_match('/^<(\w+:\/{2}[^ >]+)>/i', $Excerpt['text'], $matches)) {
			//--
			$url = str_replace(array('&', '<'), array('&amp;', '&lt;'), $matches[1]);
			//--
			return array(
				'extent' => strlen($matches[0]),
				'element' => array(
					'name' => 'a',
					'text' => $url,
					'attributes' => array(
						'href' => $url,
					),
				),
			);
			//--
		} //end if
		//--
	} //END FUNCTION


	//-- # ~

	private function unmarkedText($text) {
		//--
		if($this->breaksEnabled) {
			//--
			$text = preg_replace('/[ ]*\n/', "<br>\n", $text); // <br />
			//--
		} else {
			//--
			$text = preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/', "<br>\n", $text); // <br />
			$text = str_replace(" \n", "\n", $text);
			//--
		} //end if else
		//--
		return $text;
		//--
	} //END FUNCTION


	//-- # Handlers


	private function element(array $Element) {
		//--
		$markup = '<'.$Element['name'];
		//--
		if(isset($Element['attributes'])) {
			//--
			foreach($Element['attributes'] as $name => $value) {
				//--
				if($value === null) {
					continue;
				} //end if
				//--
				$markup .= ' '.$name.'="'.$value.'"';
				//--
			} //end foreach
			//--
		} //end if
		//--
		if(isset($Element['text'])) {
			//--
			$markup .= '>';
			//--
			if(isset($Element['handler'])) {
				//$markup .= $this->$Element['handler']($Element['text']);
				$markup .= $this->{$Element['handler']}($Element['text']); // fix from 1.5.4
			} else {
				$markup .= $Element['text'];
			} //end if else
			//--
			$markup .= '</'.$Element['name'].'>';
			//--
		} else {
			//--
			$markup .= '>'; // ' />'
			//--
		} //end if else
		//--
		return $markup;
		//--
	} //END FUNCTION


	private function elements(array $Elements) {
		//--
		$markup = '';
		//--
		foreach($Elements as $Element) {
			$markup .= "\n" . $this->element($Element);
		} //end foreach
		//--
		$markup .= "\n";
		//--
		return $markup;
		//--
	} //END FUNCTION


	private function li($lines) {
		//--
		$markup = $this->lines($lines);
		//--
		$trimmedMarkup = trim($markup);
		//--
		if(!in_array('', $lines) AND SmartUnicode::sub_str($trimmedMarkup, 0, 3) === '<p>') {
			//--
			$markup = $trimmedMarkup;
			$markup = SmartUnicode::sub_str($markup, 3);
			//--
			$position = SmartUnicode::str_pos($markup, '</p>');
			//--
			$markup = substr_replace($markup, '', $position, 4);
			//--
		} //end if
		//--
		return $markup;
		//--
	} //END FUNCTION


	// unixman, extra Attributes
	// Examples:
	//		[link](http://parsedown.org) {.primary9 #link .Upper-Case @data-smart=open,modal$700$300}
	//		![alt text](https://github.com/adam-p/markdown-here/raw/master/src/common/images/icon48.png "Logo Title Text 1") {@width=100 @style=box-shadow:$10px$10px$5px$#888888;}
	private function parseAttributeData($attributeString) {
		//--
		$Data = array();
		//--
		$attributes = preg_split('/[ ]+/', $attributeString, - 1, PREG_SPLIT_NO_EMPTY);
		//--
		$classes = array();
		foreach($attributes as $attribute) {
			//--
			if($attribute[0] === '@') { // @
				$tmp_arr = explode('=', $attribute);
				$Data[trim(SmartUnicode::sub_str(trim($tmp_arr[0]),1))] = trim(str_replace(array(',', '$'), array('.', ' '), trim($tmp_arr[1])));
			} elseif($attribute[0] === '#') { // #
				$Data['id'] = SmartUnicode::sub_str($attribute, 1);
			} else { // .
				$classes[]= SmartUnicode::sub_str($attribute, 1);
			} //end if else
			//--
		} //end foreach
		//--
		if(count($classes) > 0) {
			$Data['class'] = implode(' ', $classes);
		} //end if
		//--
		return $Data;
		//--
	} //END FUNCTION


} //END CLASS

/*
$markdown = new SmartMarkdownToHTML();
echo $markdown->text('Hello _SmartMarkdownToHTML_!'); // prints: <p>Hello <i>SmartMarkdownToHTML</i>!</p>
*/

//end of php code
?>