/*
Language: Markers-TPL (Smart.Framework) v.20191010
Requires: xml.js
Author: unix-world.org
Description: Markers-TPL is a templating language for PHP and Javascript built into Smart.Framework
Category: template
*/

// syntax/tpl/markertpl.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('markertpl',
function(hljs) {

	var SYNTAX = 'if loop else';
	SYNTAX = SYNTAX + ' ' + SYNTAX.split(' ').map(function(t){return 'end' + t}).join(' ');

	var STPL = 'sub template';
	STPL = STPL + ' ' + STPL.split(' ').map(function(t){return 'end' + t}).join(' ');

	return {
		aliases: ['markerstpl','smartframeworktpl'],
		case_insensitive: true,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\[%%%%COMMENT%%%%\]/, /\[%%%%\/COMMENT%%%%\]/),
			{ // syntax: if, loop, brntspace
				className: 'meta',
				begin: /\[%%%%/, end: /%%%%\]/,
				contains: [
					{
						className: 'symbol',
						begin: /([\|\/a-zA-Z0-9_\-\.\:]+)((\^~|\^\*|~~|~\*|\$~|\$\*|\=\=|\!\=|\<\=|\<|\>\=|\>|\!%|%|@\=|@\!|@\+|@\-)([^\[\]]*);)?/, // {{{SYNC-TPL-EXPR-IF}}}
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

// #END
