/*
Language: Marker-TPL (Smart.Framework) v.20200609
Requires: xml.js
Author: unix-world.org
Description: Marker-TPL is a templating language for PHP and Javascript built into Smart.Framework
Category: template
*/

// syntax/tpl/markertpl.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('markertpl',
function(hljs) {

	var SYNTAX = 'IF LOOP ELSE';

	return {
		aliases: ['markertpl','markerstpl','smartframeworktpl'],
		case_insensitive: false,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\[%%%COMMENT%%%\]/, /\[%%%\/COMMENT%%%\]/),
			{ // syntax: if, loop, specials (space, tab, new line, carriage return, left/right square bracket)
				className: 'meta',
				begin: /\[%%%/, end: /(\([0-9]+\))?%%%\]/,
				contains: [
					{
						className: 'regexp',
						begin: /(\|[A-Z0-9\-]+)/ // {{{SYNC-TPL-EXPR-SPECIALS}}}
					},
					{ // {{{SYNC-TPL-EXPR-IF}}} {{{SYNC-TPL-EXPR-LOOP}}}
						className: 'symbol',
						begin: /([\/\|a-zA-Z0-9_\-\.\:]+)/,
						keywords: SYNTAX
					},
					{ // {{{SYNC-TPL-EXPR-IF}}}
						className: 'regexp', // tag
						end: /((@\=\=|@\!\=|@\<\=|@\<|@\>\=|@\>|\=\=|\!\=|\<\=|\<|\>\=|\>|\!%|%|\!\?|\?|\^~|\^\*|&~|&\*|\$~|\$\*)([^\[\]]*);)?/,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					}
				]
			},
			{ // sub-template
				className: 'meta',
				begin: /\[@@@/, end: /@@@\]/,
				contains: [
					{
						className: 'keyword',
						begin: /(SUB\-TEMPLATE\:){1}/,
					},
					{ // {{{SYNC-TPL-EXPR-SUBTPL}}}
						className: 'title', // 'regexp'
						begin: /([a-zA-Z0-9_\-\.\/\!\?%]+)/,
					//	returnEnd: true
					},
					{
						className: 'regexp', // 'symbol'
						end: /(\|[a-z0-9]+)*/,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					}
				]
			},
			{ // markers
				className: 'regexp', // 'template-variable'
				begin: /\[###/, end: /###\]/,
				contains: [
					{
						className: 'keyword',
						begin: /[A-Z0-9_\-\.]+/,
					//	returnEnd: true
					},
					{
						className: 'symbol',
						end: /(\|[a-z0-9]+)*/,
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
