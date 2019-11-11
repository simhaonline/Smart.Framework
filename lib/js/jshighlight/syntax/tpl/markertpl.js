/*
Language: Markers-TPL (Smart.Framework) v.20191109
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

	return {
		aliases: ['markerstpl','smartframeworktpl'],
		case_insensitive: true,
		subLanguage: 'xml',
		contains: [
			hljs.COMMENT(/\[%%%COMMENT%%%\]/, /\[%%%\/COMMENT%%%\]/),
			{ // syntax: if, loop, specials (space, tab, new line, carriage return, left/right square bracket)
				className: 'meta',
				begin: /\[%%%/, end: /(\([0-9]+\))?%%%\]/,
				contains: [
					{ // {{{SYNC-TPL-EXPR-IF}}} {{{SYNC-TPL-EXPR-LOOP}}} {{{SYNC-TPL-EXPR-SPECIALS}}}
						className: 'symbol',
						begin: /([\/\|a-zA-Z0-9_\-\.\:]+)/,
						keywords: SYNTAX,

					},
					{ // {{{SYNC-TPL-EXPR-IF}}}
						className: 'tag',
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
						className: 'title',
						end: /([a-zA-Z0-9_\-\.\/\!\?%]+)/,
						starts: {
							endsWithParent: true,
							relevance: 0
						}
					}
				]
			},
			{ // markers
				className: 'template-variable',
				begin: /\[###/, end: /###\]/,
				contains: [
					{
						className: 'keyword',
						begin: /[A-Z0-9_\-\.]+/,
					//	returnEnd: true,
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
