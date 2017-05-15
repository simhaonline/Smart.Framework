/*
Language: Marker-TPL (Smart.Framework)
Requires: xml.js
Author: unix-world.org
Description: Marker-TPL is a templating language for PHP and Javascript built into Smart.Framework
Category: template
*/

hljs.registerLanguage('markertpl',
function(hljs) {

	var SYNTAX = 'if loop else';
	SYNTAX = SYNTAX + ' ' + SYNTAX.split(' ').map(function(t){return 'end' + t}).join(' ');

	var STPL = 'sub template';
	STPL = STPL + ' ' + STPL.split(' ').map(function(t){return 'end' + t}).join(' ');

	return {
		aliases: ['smartframeworktpl'],
		case_insensitive: true,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\[%%%%COMMENT%%%%\]/, /\[%%%%\/COMMENT%%%%\]/),
			{ // syntax: if loop
				className: 'meta',
				begin: /\[%%%%/, end: /%%%%\]/,
				contains: [
					{
						className: 'symbol',
						begin: /([\|\/a-zA-Z0-9_\-\.\:]+)((\^~|\^\*|~~|~\*|\$~|\$\*|\=\=|\!\=|\<\=|\<|\>|\>\=|%|\!%|@\=|@\!|@\+|@\-)([#a-zA-Z0-9_\-\.\|]*);)?/,
				keywords: SYNTAX,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					}
				]
			},
			{ // sub-template
				className: 'meta',
				begin: /\[@@@@/, end: /@@@@\]/,
				contains: [
					{
						className: 'symbol',
						begin: /([a-zA-Z0-9_\-\.\/\!\?\:]+)/,
						keywords: STPL,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					}
				]
			},
			{ // markers
				className: 'template-variable',
				begin: /\[####/, end: /####\]/,
				contains: [
					{
						className: 'keyword',
						begin: /[A-Za-z0-9_\-\.\|]+/,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					}
				]
			}
		]
	};
}
);
