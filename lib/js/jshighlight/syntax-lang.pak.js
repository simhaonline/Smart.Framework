
// # JS Package: syntax-lang.pak.js :: #START# :: @ generated from lib/js/jshighlight/syntax/lang/*.js
// Included Files: lang/*.js #

// ### DO NOT EDIT THIS FILE AS IT WILL BE OVERWRITTEN EACH TIME THE INCLUDED SCRIPTS WILL CHANGE !!! ###

// === lang/cmake.js

/*
Language: CMake
Description: CMake is an open-source cross-platform system for build automation.
Author: Igor Kalnitsky <igor@kalnitsky.org>
Website: http://kalnitsky.org/
*/

// syntax/lang/cmake.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('cmake',
function(hljs) {
	return {
		aliases: ['cmake.in'],
		case_insensitive: true,
		keywords: {
			keyword:
				// scripting commands
				'break cmake_host_system_information cmake_minimum_required cmake_parse_arguments ' +
				'cmake_policy configure_file continue elseif else endforeach endfunction endif endmacro ' +
				'endwhile execute_process file find_file find_library find_package find_path ' +
				'find_program foreach function get_cmake_property get_directory_property ' +
				'get_filename_component get_property if include include_guard list macro ' +
				'mark_as_advanced math message option return separate_arguments ' +
				'set_directory_properties set_property set site_name string unset variable_watch while ' +
				// project commands
				'add_compile_definitions add_compile_options add_custom_command add_custom_target ' +
				'add_definitions add_dependencies add_executable add_library add_link_options ' +
				'add_subdirectory add_test aux_source_directory build_command create_test_sourcelist ' +
				'define_property enable_language enable_testing export fltk_wrap_ui ' +
				'get_source_file_property get_target_property get_test_property include_directories ' +
				'include_external_msproject include_regular_expression install link_directories ' +
				'link_libraries load_cache project qt_wrap_cpp qt_wrap_ui remove_definitions ' +
				'set_source_files_properties set_target_properties set_tests_properties source_group ' +
				'target_compile_definitions target_compile_features target_compile_options ' +
				'target_include_directories target_link_directories target_link_libraries ' +
				'target_link_options target_sources try_compile try_run ' +
				// CTest commands
				'ctest_build ctest_configure ctest_coverage ctest_empty_binary_directory ctest_memcheck ' +
				'ctest_read_custom_files ctest_run_script ctest_sleep ctest_start ctest_submit ' +
				'ctest_test ctest_update ctest_upload ' +
				// deprecated commands
				'build_name exec_program export_library_dependencies install_files install_programs ' +
				'install_targets load_command make_directory output_required_files remove ' +
				'subdir_depends subdirs use_mangled_mesa utility_source variable_requires write_file ' +
				'qt5_use_modules qt5_use_package qt5_wrap_cpp ' +
				// core keywords
				'on off true false and or not command policy target test exists is_newer_than ' +
				'is_directory is_symlink is_absolute matches less greater equal less_equal ' +
				'greater_equal strless strgreater strequal strless_equal strgreater_equal version_less ' +
				'version_greater version_equal version_less_equal version_greater_equal in_list defined'
		},
		contains: [
			{
				className: 'variable',
				begin: '\\${', end: '}'
			},
			hljs.HASH_COMMENT_MODE,
			hljs.QUOTE_STRING_MODE,
			hljs.NUMBER_MODE
		]
	};
}
);

// #END

// === lang/coffeescript.js

/*
Language: CoffeeScript
Author: Dmytrii Nagirniak <dnagir@gmail.com>
Contributors: Oleg Efimov <efimovov@gmail.com>, Cédric Néhémie <cedric.nehemie@gmail.com>
Description: CoffeeScript is a programming language that transcompiles to JavaScript. For info about language see http://coffeescript.org/
Category: common, scripting
*/

// syntax/lang/coffeescript.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('coffeescript',
function(hljs) {
	var KEYWORDS = {
		keyword:
			// JS keywords
			'in if for while finally new do return else break catch instanceof throw try this ' +
			'switch continue typeof delete debugger super yield import export from as default await ' +
			// Coffee keywords
			'then unless until loop of by when and or is isnt not',
		literal:
			// JS literals
			'true false null undefined ' +
			// Coffee literals
			'yes no on off',
		built_in:
			'npm require console print module global window document'
	};
	var JS_IDENT_RE = '[A-Za-z$_][0-9A-Za-z$_]*';
	var SUBST = {
		className: 'subst',
		begin: /#\{/, end: /}/,
		keywords: KEYWORDS
	};
	var EXPRESSIONS = [
		hljs.BINARY_NUMBER_MODE,
		hljs.inherit(hljs.C_NUMBER_MODE, {starts: {end: '(\\s*/)?', relevance: 0}}), // a number tries to eat the following slash to prevent treating it as a regexp
		{
			className: 'string',
			variants: [
				{
					begin: /'''/, end: /'''/,
					contains: [hljs.BACKSLASH_ESCAPE]
				},
				{
					begin: /'/, end: /'/,
					contains: [hljs.BACKSLASH_ESCAPE]
				},
				{
					begin: /"""/, end: /"""/,
					contains: [hljs.BACKSLASH_ESCAPE, SUBST]
				},
				{
					begin: /"/, end: /"/,
					contains: [hljs.BACKSLASH_ESCAPE, SUBST]
				}
			]
		},
		{
			className: 'regexp',
			variants: [
				{
					begin: '///', end: '///',
					contains: [SUBST, hljs.HASH_COMMENT_MODE]
				},
				{
					begin: '//[gim]*',
					relevance: 0
				},
				{
					// regex can't start with space to parse x / 2 / 3 as two divisions
					// regex can't start with *, and it supports an "illegal" in the main mode
					begin: /\/(?![ *])(\\\/|.)*?\/[gim]*(?=\W|$)/
				}
			]
		},
		{
			begin: '@' + JS_IDENT_RE // relevance booster
		},
		{
			subLanguage: 'javascript',
			excludeBegin: true, excludeEnd: true,
			variants: [
				{
					begin: '```', end: '```',
				},
				{
					begin: '`', end: '`',
				}
			]
		}
	];
	SUBST.contains = EXPRESSIONS;

	var TITLE = hljs.inherit(hljs.TITLE_MODE, {begin: JS_IDENT_RE});
	var PARAMS_RE = '(\\(.*\\))?\\s*\\B[-=]>';
	var PARAMS = {
		className: 'params',
		begin: '\\([^\\(]', returnBegin: true,
		/* We need another contained nameless mode to not have every nested
		pair of parens to be called "params" */
		contains: [{
			begin: /\(/, end: /\)/,
			keywords: KEYWORDS,
			contains: ['self'].concat(EXPRESSIONS)
		}]
	};

	return {
		aliases: ['coffee', 'cson', 'iced'],
		keywords: KEYWORDS,
		illegal: /\/\*/,
		contains: EXPRESSIONS.concat([
			hljs.COMMENT('###', '###'),
			hljs.HASH_COMMENT_MODE,
			{
				className: 'function',
				begin: '^\\s*' + JS_IDENT_RE + '\\s*=\\s*' + PARAMS_RE, end: '[-=]>',
				returnBegin: true,
				contains: [TITLE, PARAMS]
			},
			{
				// anonymous function start
				begin: /[:\(,=]\s*/,
				relevance: 0,
				contains: [
					{
						className: 'function',
						begin: PARAMS_RE, end: '[-=]>',
						returnBegin: true,
						contains: [PARAMS]
					}
				]
			},
			{
				className: 'class',
				beginKeywords: 'class',
				end: '$',
				illegal: /[:="\[\]]/,
				contains: [
					{
						beginKeywords: 'extends',
						endsWithParent: true,
						illegal: /[:="\[\]]/,
						contains: [TITLE]
					},
					TITLE
				]
			},
			{
				begin: JS_IDENT_RE + ':', end: ':',
				returnBegin: true, returnEnd: true,
				relevance: 0
			}
		])
	};
}
);

// #END

// === lang/cpp.js

/*
Language: C++
Author: Ivan Sagalaev <maniac@softwaremaniacs.org>
Contributors: Evgeny Stepanischev <imbolk@gmail.com>, Zaven Muradyan <megalivoithos@gmail.com>, Roel Deckers <admin@codingcat.nl>, Sam Wu <samsam2310@gmail.com>, Jordi Petit <jordi.petit@gmail.com>, Pieter Vantorre <pietervantorre@gmail.com>, Google Inc. (David Benjamin) <davidben@google.com>
Category: common, system
*/

// syntax/lang/cpp.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('cpp',
function(hljs) {
	var CPP_PRIMITIVE_TYPES = {
		className: 'keyword',
		begin: '\\b[a-z\\d_]*_t\\b'
	};

	var STRINGS = {
		className: 'string',
		variants: [
			{
				begin: '(u8?|U|L)?"', end: '"',
				illegal: '\\n',
				contains: [hljs.BACKSLASH_ESCAPE]
			},
			{
				// TODO: This does not handle raw string literals with prefixes. Using
				// a single regex with backreferences would work (note to use *?
				// instead of * to make it non-greedy), but the mode.terminators
				// computation in highlight.js breaks the counting.
				begin: '(u8?|U|L)?R"\\(', end: '\\)"',
			},
			{
				begin: '\'\\\\?.', end: '\'',
				illegal: '.'
			}
		]
	};

	var NUMBERS = {
		className: 'number',
		variants: [
			{ begin: '\\b(0b[01\']+)' },
			{ begin: '(-?)\\b([\\d\']+(\\.[\\d\']*)?|\\.[\\d\']+)(u|U|l|L|ul|UL|f|F|b|B)' },
			{ begin: '(-?)(\\b0[xX][a-fA-F0-9\']+|(\\b[\\d\']+(\\.[\\d\']*)?|\\.[\\d\']+)([eE][-+]?[\\d\']+)?)' }
		],
		relevance: 0
	};

	var PREPROCESSOR =       {
		className: 'meta',
		begin: /#\s*[a-z]+\b/, end: /$/,
		keywords: {
			'meta-keyword':
				'if else elif endif define undef warning error line ' +
				'pragma ifdef ifndef include'
		},
		contains: [
			{
				begin: /\\\n/, relevance: 0
			},
			hljs.inherit(STRINGS, {className: 'meta-string'}),
			{
				className: 'meta-string',
				begin: /<[^\n>]*>/, end: /$/,
				illegal: '\\n',
			},
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE
		]
	};

	var FUNCTION_TITLE = hljs.IDENT_RE + '\\s*\\(';

	var CPP_KEYWORDS = {
		keyword: 'int float while private char catch import module export virtual operator sizeof ' +
			'dynamic_cast|10 typedef const_cast|10 const for static_cast|10 union namespace ' +
			'unsigned long volatile static protected bool template mutable if public friend ' +
			'do goto auto void enum else break extern using asm case typeid ' +
			'short reinterpret_cast|10 default double register explicit signed typename try this ' +
			'switch continue inline delete alignof constexpr decltype ' +
			'noexcept static_assert thread_local restrict _Bool complex _Complex _Imaginary ' +
			'atomic_bool atomic_char atomic_schar ' +
			'atomic_uchar atomic_short atomic_ushort atomic_int atomic_uint atomic_long atomic_ulong atomic_llong ' +
			'atomic_ullong new throw return ' +
			'and or not',
		built_in: 'std string cin cout cerr clog stdin stdout stderr stringstream istringstream ostringstream ' +
			'auto_ptr deque list queue stack vector map set bitset multiset multimap unordered_set ' +
			'unordered_map unordered_multiset unordered_multimap array shared_ptr abort abs acos ' +
			'asin atan2 atan calloc ceil cosh cos exit exp fabs floor fmod fprintf fputs free frexp ' +
			'fscanf isalnum isalpha iscntrl isdigit isgraph islower isprint ispunct isspace isupper ' +
			'isxdigit tolower toupper labs ldexp log10 log malloc realloc memchr memcmp memcpy memset modf pow ' +
			'printf putchar puts scanf sinh sin snprintf sprintf sqrt sscanf strcat strchr strcmp ' +
			'strcpy strcspn strlen strncat strncmp strncpy strpbrk strrchr strspn strstr tanh tan ' +
			'vfprintf vprintf vsprintf endl initializer_list unique_ptr',
		literal: 'true false nullptr NULL'
	};

	var EXPRESSION_CONTAINS = [
		CPP_PRIMITIVE_TYPES,
		hljs.C_LINE_COMMENT_MODE,
		hljs.C_BLOCK_COMMENT_MODE,
		NUMBERS,
		STRINGS
	];

	return {
		aliases: ['c', 'cc', 'h', 'c++', 'h++', 'hpp'],
		keywords: CPP_KEYWORDS,
		illegal: '</',
		contains: EXPRESSION_CONTAINS.concat([
			PREPROCESSOR,
			{
				begin: '\\b(deque|list|queue|stack|vector|map|set|bitset|multiset|multimap|unordered_map|unordered_set|unordered_multiset|unordered_multimap|array)\\s*<', end: '>',
				keywords: CPP_KEYWORDS,
				contains: ['self', CPP_PRIMITIVE_TYPES]
			},
			{
				begin: hljs.IDENT_RE + '::',
				keywords: CPP_KEYWORDS
			},
			{
				// This mode covers expression context where we can't expect a function
				// definition and shouldn't highlight anything that looks like one:
				// `return some()`, `else if()`, `(x*sum(1, 2))`
				variants: [
					{begin: /=/, end: /;/},
					{begin: /\(/, end: /\)/},
					{beginKeywords: 'new throw return else', end: /;/}
				],
				keywords: CPP_KEYWORDS,
				contains: EXPRESSION_CONTAINS.concat([
					{
						begin: /\(/, end: /\)/,
						keywords: CPP_KEYWORDS,
						contains: EXPRESSION_CONTAINS.concat(['self']),
						relevance: 0
					}
				]),
				relevance: 0
			},
			{
				className: 'function',
				begin: '(' + hljs.IDENT_RE + '[\\*&\\s]+)+' + FUNCTION_TITLE,
				returnBegin: true, end: /[{;=]/,
				excludeEnd: true,
				keywords: CPP_KEYWORDS,
				illegal: /[^\w\s\*&]/,
				contains: [
					{
						begin: FUNCTION_TITLE, returnBegin: true,
						contains: [hljs.TITLE_MODE],
						relevance: 0
					},
					{
						className: 'params',
						begin: /\(/, end: /\)/,
						keywords: CPP_KEYWORDS,
						relevance: 0,
						contains: [
							hljs.C_LINE_COMMENT_MODE,
							hljs.C_BLOCK_COMMENT_MODE,
							STRINGS,
							NUMBERS,
							CPP_PRIMITIVE_TYPES,
							// Count matching parentheses.
							{
								begin: /\(/, end: /\)/,
								keywords: CPP_KEYWORDS,
								relevance: 0,
								contains: [
									'self',
									hljs.C_LINE_COMMENT_MODE,
									hljs.C_BLOCK_COMMENT_MODE,
									STRINGS,
									NUMBERS,
									CPP_PRIMITIVE_TYPES
								]
							}
						]
					},
					hljs.C_LINE_COMMENT_MODE,
					hljs.C_BLOCK_COMMENT_MODE,
					PREPROCESSOR
				]
			},
			{
				className: 'class',
				beginKeywords: 'class struct', end: /[{;:]/,
				contains: [
					{begin: /</, end: />/, contains: ['self']}, // skip generic stuff
					hljs.TITLE_MODE
				]
			}
		]),
		exports: {
			preprocessor: PREPROCESSOR,
			strings: STRINGS,
			keywords: CPP_KEYWORDS
		}
	};
}
);

// #END

// === lang/go.js

/*
Language: Go
Author: Stephan Kountso aka StepLg <steplg@gmail.com>
Contributors: Evgeny Stepanischev <imbolk@gmail.com>
Description: Google go language (golang). For info about language see http://golang.org/
Category: system
*/

// syntax/lang/go.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('go',
function(hljs) {
	var GO_KEYWORDS = {
		keyword:
			'break default func interface select case map struct chan else goto package switch ' +
			'const fallthrough if range type continue for import return var go defer ' +
			'bool byte complex64 complex128 float32 float64 int8 int16 int32 int64 string uint8 ' +
			'uint16 uint32 uint64 int uint uintptr rune',
		literal:
			 'true false iota nil',
		built_in:
			'append cap close complex copy imag len make new panic print println real recover delete'
	};
	return {
		aliases: ['golang'],
		keywords: GO_KEYWORDS,
		illegal: '</',
		contains: [
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			{
				className: 'string',
				variants: [
					hljs.QUOTE_STRING_MODE,
					{begin: '\'', end: '[^\\\\]\''},
					{begin: '`', end: '`'},
				]
			},
			{
				className: 'number',
				variants: [
					{begin: hljs.C_NUMBER_RE + '[dflsi]', relevance: 1},
					hljs.C_NUMBER_MODE
				]
			},
			{
				begin: /:=/ // relevance booster
			},
			{
				className: 'function',
				beginKeywords: 'func', end: /\s*\{/, excludeEnd: true,
				contains: [
					hljs.TITLE_MODE,
					{
						className: 'params',
						begin: /\(/, end: /\)/,
						keywords: GO_KEYWORDS,
						illegal: /["']/
					}
				]
			}
		]
	};
}
);

// #END

// === lang/lua.js

/*
Language: Lua
Author: Andrew Fedorov <dmmdrs@mail.ru>
Category: scripting
*/

// syntax/lang/lua.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('lua',
function(hljs) {
	var OPENING_LONG_BRACKET = '\\[=*\\[';
	var CLOSING_LONG_BRACKET = '\\]=*\\]';
	var LONG_BRACKETS = {
		begin: OPENING_LONG_BRACKET, end: CLOSING_LONG_BRACKET,
		contains: ['self']
	};
	var COMMENTS = [
		hljs.COMMENT('--(?!' + OPENING_LONG_BRACKET + ')', '$'),
		hljs.COMMENT(
			'--' + OPENING_LONG_BRACKET,
			CLOSING_LONG_BRACKET,
			{
				contains: [LONG_BRACKETS],
				relevance: 10
			}
		)
	];
	return {
		lexemes: hljs.UNDERSCORE_IDENT_RE,
		keywords: {
			literal: "true false nil",
			keyword: "and break do else elseif end for goto if in local not or repeat return then until while",
			built_in:
				//Metatags and globals:
				'_G _ENV _VERSION __index __newindex __mode __call __metatable __tostring __len ' +
				'__gc __add __sub __mul __div __mod __pow __concat __unm __eq __lt __le assert ' +
				//Standard methods and properties:
				'collectgarbage dofile error getfenv getmetatable ipairs load loadfile loadstring' +
				'module next pairs pcall print rawequal rawget rawset require select setfenv' +
				'setmetatable tonumber tostring type unpack xpcall arg self' +
				//Library methods and properties (one line per library):
				'coroutine resume yield status wrap create running debug getupvalue ' +
				'debug sethook getmetatable gethook setmetatable setlocal traceback setfenv getinfo setupvalue getlocal getregistry getfenv ' +
				'io lines write close flush open output type read stderr stdin input stdout popen tmpfile ' +
				'math log max acos huge ldexp pi cos tanh pow deg tan cosh sinh random randomseed frexp ceil floor rad abs sqrt modf asin min mod fmod log10 atan2 exp sin atan ' +
				'os exit setlocale date getenv difftime remove time clock tmpname rename execute package preload loadlib loaded loaders cpath config path seeall ' +
				'string sub upper len gfind rep find match char dump gmatch reverse byte format gsub lower ' +
				'table setn insert getn foreachi maxn foreach concat sort remove'
		},
		contains: COMMENTS.concat([
			{
				className: 'function',
				beginKeywords: 'function', end: '\\)',
				contains: [
					hljs.inherit(hljs.TITLE_MODE, {begin: '([_a-zA-Z]\\w*\\.)*([_a-zA-Z]\\w*:)?[_a-zA-Z]\\w*'}),
					{
						className: 'params',
						begin: '\\(', endsWithParent: true,
						contains: COMMENTS
					}
				].concat(COMMENTS)
			},
			hljs.C_NUMBER_MODE,
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE,
			{
				className: 'string',
				begin: OPENING_LONG_BRACKET, end: CLOSING_LONG_BRACKET,
				contains: [LONG_BRACKETS],
				relevance: 5
			}
		])
	};
}
);

// #END

// === lang/makefile.js

/*
Language: Makefile
Author: Ivan Sagalaev <maniac@softwaremaniacs.org>
Contributors: Joël Porquet <joel@porquet.org>
Category: common
*/

// syntax/lang/makefile.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('makefile',
function(hljs) {
	/* Variables: simple (eg $(var)) and special (eg $@) */
	var VARIABLE = {
		className: 'variable',
		variants: [
			{
				begin: '\\$\\(' + hljs.UNDERSCORE_IDENT_RE + '\\)',
				contains: [hljs.BACKSLASH_ESCAPE],
			},
			{
				begin: /\$[@%<?\^\+\*]/
			},
		]
	};
	/* Quoted string with variables inside */
	var QUOTE_STRING = {
		className: 'string',
		begin: /"/, end: /"/,
		contains: [
			hljs.BACKSLASH_ESCAPE,
			VARIABLE,
		]
	};
	/* Function: $(func arg,...) */
	var FUNC = {
		className: 'variable',
		begin: /\$\([\w-]+\s/, end: /\)/,
		keywords: {
			built_in:
				'subst patsubst strip findstring filter filter-out sort ' +
				'word wordlist firstword lastword dir notdir suffix basename ' +
				'addsuffix addprefix join wildcard realpath abspath error warning ' +
				'shell origin flavor foreach if or and call eval file value',
		},
		contains: [
			VARIABLE,
		]
	};
	/* Variable assignment */
	var VAR_ASSIG = {
		begin: '^' + hljs.UNDERSCORE_IDENT_RE + '\\s*[:+?]?=',
		illegal: '\\n',
		returnBegin: true,
		contains: [
			{
				begin: '^' + hljs.UNDERSCORE_IDENT_RE, end: '[:+?]?=',
				excludeEnd: true,
			}
		]
	};
	/* Meta targets (.PHONY) */
	var META = {
		className: 'meta',
		begin: /^\.PHONY:/, end: /$/,
		keywords: {'meta-keyword': '.PHONY'},
		lexemes: /[\.\w]+/
	};
	/* Targets */
	var TARGET = {
		className: 'section',
		begin: /^[^\s]+:/, end: /$/,
		contains: [VARIABLE,]
	};
	return {
		aliases: ['mk', 'mak'],
		keywords:
			'define endef undefine ifdef ifndef ifeq ifneq else endif ' +
			'include -include sinclude override export unexport private vpath',
		lexemes: /[\w-]+/,
		contains: [
			hljs.HASH_COMMENT_MODE,
			VARIABLE,
			QUOTE_STRING,
			FUNC,
			VAR_ASSIG,
			META,
			TARGET,
		]
	};
}
);

// #END

// === lang/python.js

/*
Language: Python
Category: common
*/

// syntax/lang/python.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('python',
function(hljs) {
	var KEYWORDS = {
		keyword:
			'and elif is global as in if from raise for except finally print import pass return ' +
			'exec else break not with class assert yield try while continue del or def lambda ' +
			'async await nonlocal|10 None True False',
		built_in:
			'Ellipsis NotImplemented'
	};
	var PROMPT = {
		className: 'meta',  begin: /^(>>>|\.\.\.) /
	};
	var SUBST = {
		className: 'subst',
		begin: /\{/, end: /\}/,
		keywords: KEYWORDS,
		illegal: /#/
	};
	var STRING = {
		className: 'string',
		contains: [hljs.BACKSLASH_ESCAPE],
		variants: [
			{
				begin: /(u|b)?r?'''/, end: /'''/,
				contains: [hljs.BACKSLASH_ESCAPE, PROMPT],
				relevance: 10
			},
			{
				begin: /(u|b)?r?"""/, end: /"""/,
				contains: [hljs.BACKSLASH_ESCAPE, PROMPT],
				relevance: 10
			},
			{
				begin: /(fr|rf|f)'''/, end: /'''/,
				contains: [hljs.BACKSLASH_ESCAPE, PROMPT, SUBST]
			},
			{
				begin: /(fr|rf|f)"""/, end: /"""/,
				contains: [hljs.BACKSLASH_ESCAPE, PROMPT, SUBST]
			},
			{
				begin: /(u|r|ur)'/, end: /'/,
				relevance: 10
			},
			{
				begin: /(u|r|ur)"/, end: /"/,
				relevance: 10
			},
			{
				begin: /(b|br)'/, end: /'/
			},
			{
				begin: /(b|br)"/, end: /"/
			},
			{
				begin: /(fr|rf|f)'/, end: /'/,
				contains: [hljs.BACKSLASH_ESCAPE, SUBST]
			},
			{
				begin: /(fr|rf|f)"/, end: /"/,
				contains: [hljs.BACKSLASH_ESCAPE, SUBST]
			},
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE
		]
	};
	var NUMBER = {
		className: 'number', relevance: 0,
		variants: [
			{begin: hljs.BINARY_NUMBER_RE + '[lLjJ]?'},
			{begin: '\\b(0o[0-7]+)[lLjJ]?'},
			{begin: hljs.C_NUMBER_RE + '[lLjJ]?'}
		]
	};
	var PARAMS = {
		className: 'params',
		begin: /\(/, end: /\)/,
		contains: ['self', PROMPT, NUMBER, STRING]
	};
	SUBST.contains = [STRING, NUMBER, PROMPT];
	return {
		aliases: ['py', 'gyp'],
		keywords: KEYWORDS,
		illegal: /(<\/|->|\?)|=>/,
		contains: [
			PROMPT,
			NUMBER,
			STRING,
			hljs.HASH_COMMENT_MODE,
			{
				variants: [
					{className: 'function', beginKeywords: 'def'},
					{className: 'class', beginKeywords: 'class'}
				],
				end: /:/,
				illegal: /[${=;\n,]/,
				contains: [
					hljs.UNDERSCORE_TITLE_MODE,
					PARAMS,
					{
						begin: /->/, endsWithParent: true,
						keywords: 'None'
					}
				]
			},
			{
				className: 'meta',
				begin: /^[\t ]*@/, end: /$/
			},
			{
				begin: /\b(print|exec)\(/ // don’t highlight keywords-turned-functions in Python 3
			}
		]
	};
}
);

// #END

// === lang/ruby.js

/*
Language: Ruby
Author: Anton Kovalyov <anton@kovalyov.net>
Contributors: Peter Leonov <gojpeg@yandex.ru>, Vasily Polovnyov <vast@whiteants.net>, Loren Segal <lsegal@soen.ca>, Pascal Hurni <phi@ruby-reactive.org>, Cedric Sohrauer <sohrauer@googlemail.com>
Category: common
*/

// syntax/lang/ruby.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('ruby',
function(hljs) {
	var RUBY_METHOD_RE = '[a-zA-Z_]\\w*[!?=]?|[-+~]\\@|<<|>>|=~|===?|<=>|[<>]=?|\\*\\*|[-/+%^&*~`|]|\\[\\]=?';
	var RUBY_KEYWORDS = {
		keyword:
			'and then defined module in return redo if BEGIN retry end for self when ' +
			'next until do begin unless END rescue else break undef not super class case ' +
			'require yield alias while ensure elsif or include attr_reader attr_writer attr_accessor',
		literal:
			'true false nil'
	};
	var YARDOCTAG = {
		className: 'doctag',
		begin: '@[A-Za-z]+'
	};
	var IRB_OBJECT = {
		begin: '#<', end: '>'
	};
	var COMMENT_MODES = [
		hljs.COMMENT(
			'#',
			'$',
			{
				contains: [YARDOCTAG]
			}
		),
		hljs.COMMENT(
			'^\\=begin',
			'^\\=end',
			{
				contains: [YARDOCTAG],
				relevance: 10
			}
		),
		hljs.COMMENT('^__END__', '\\n$')
	];
	var SUBST = {
		className: 'subst',
		begin: '#\\{', end: '}',
		keywords: RUBY_KEYWORDS
	};
	var STRING = {
		className: 'string',
		contains: [hljs.BACKSLASH_ESCAPE, SUBST],
		variants: [
			{begin: /'/, end: /'/},
			{begin: /"/, end: /"/},
			{begin: /`/, end: /`/},
			{begin: '%[qQwWx]?\\(', end: '\\)'},
			{begin: '%[qQwWx]?\\[', end: '\\]'},
			{begin: '%[qQwWx]?{', end: '}'},
			{begin: '%[qQwWx]?<', end: '>'},
			{begin: '%[qQwWx]?/', end: '/'},
			{begin: '%[qQwWx]?%', end: '%'},
			{begin: '%[qQwWx]?-', end: '-'},
			{begin: '%[qQwWx]?\\|', end: '\\|'},
			{
				// \B in the beginning suppresses recognition of ?-sequences where ?
				// is the last character of a preceding identifier, as in: `func?4`
				begin: /\B\?(\\\d{1,3}|\\x[A-Fa-f0-9]{1,2}|\\u[A-Fa-f0-9]{4}|\\?\S)\b/
			},
			{
				begin: /<<(-?)\w+$/, end: /^\s*\w+$/,
			}
		]
	};
	var PARAMS = {
		className: 'params',
		begin: '\\(', end: '\\)', endsParent: true,
		keywords: RUBY_KEYWORDS
	};

	var RUBY_DEFAULT_CONTAINS = [
		STRING,
		IRB_OBJECT,
		{
			className: 'class',
			beginKeywords: 'class module', end: '$|;',
			illegal: /=/,
			contains: [
				hljs.inherit(hljs.TITLE_MODE, {begin: '[A-Za-z_]\\w*(::\\w+)*(\\?|\\!)?'}),
				{
					begin: '<\\s*',
					contains: [{
						begin: '(' + hljs.IDENT_RE + '::)?' + hljs.IDENT_RE
					}]
				}
			].concat(COMMENT_MODES)
		},
		{
			className: 'function',
			beginKeywords: 'def', end: '$|;',
			contains: [
				hljs.inherit(hljs.TITLE_MODE, {begin: RUBY_METHOD_RE}),
				PARAMS
			].concat(COMMENT_MODES)
		},
		{
			// swallow namespace qualifiers before symbols
			begin: hljs.IDENT_RE + '::'
		},
		{
			className: 'symbol',
			begin: hljs.UNDERSCORE_IDENT_RE + '(\\!|\\?)?:',
			relevance: 0
		},
		{
			className: 'symbol',
			begin: ':(?!\\s)',
			contains: [STRING, {begin: RUBY_METHOD_RE}],
			relevance: 0
		},
		{
			className: 'number',
			begin: '(\\b0[0-7_]+)|(\\b0x[0-9a-fA-F_]+)|(\\b[1-9][0-9_]*(\\.[0-9_]+)?)|[0_]\\b',
			relevance: 0
		},
		{
			begin: '(\\$\\W)|((\\$|\\@\\@?)(\\w+))' // variables
		},
		{
			className: 'params',
			begin: /\|/, end: /\|/,
			keywords: RUBY_KEYWORDS
		},
		{ // regexp container
			begin: '(' + hljs.RE_STARTERS_RE + '|unless)\\s*',
			keywords: 'unless',
			contains: [
				IRB_OBJECT,
				{
					className: 'regexp',
					contains: [hljs.BACKSLASH_ESCAPE, SUBST],
					illegal: /\n/,
					variants: [
						{begin: '/', end: '/[a-z]*'},
						{begin: '%r{', end: '}[a-z]*'},
						{begin: '%r\\(', end: '\\)[a-z]*'},
						{begin: '%r!', end: '![a-z]*'},
						{begin: '%r\\[', end: '\\][a-z]*'}
					]
				}
			].concat(COMMENT_MODES),
			relevance: 0
		}
	].concat(COMMENT_MODES);

	SUBST.contains = RUBY_DEFAULT_CONTAINS;
	PARAMS.contains = RUBY_DEFAULT_CONTAINS;

	var SIMPLE_PROMPT = "[>?]>";
	var DEFAULT_PROMPT = "[\\w#]+\\(\\w+\\):\\d+:\\d+>";
	var RVM_PROMPT = "(\\w+-)?\\d+\\.\\d+\\.\\d(p\\d+)?[^>]+>";

	var IRB_DEFAULT = [
		{
			begin: /^\s*=>/,
			starts: {
				end: '$', contains: RUBY_DEFAULT_CONTAINS
			}
		},
		{
			className: 'meta',
			begin: '^('+SIMPLE_PROMPT+"|"+DEFAULT_PROMPT+'|'+RVM_PROMPT+')',
			starts: {
				end: '$', contains: RUBY_DEFAULT_CONTAINS
			}
		}
	];

	return {
		aliases: ['rb', 'gemspec', 'podspec', 'thor', 'irb'],
		keywords: RUBY_KEYWORDS,
		illegal: /\/\*/,
		contains: COMMENT_MODES.concat(IRB_DEFAULT).concat(RUBY_DEFAULT_CONTAINS)
	};
}
);

// #END

// === lang/rust.js

/*
Language: Rust
Author: Andrey Vlasovskikh <andrey.vlasovskikh@gmail.com>
Contributors: Roman Shmatov <romanshmatov@gmail.com>, Kasper Andersen <kma_untrusted@protonmail.com>
Category: system
*/

// syntax/lang/rust.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('rust',
function(hljs) {
	var NUM_SUFFIX = '([ui](8|16|32|64|128|size)|f(32|64))\?';
	var KEYWORDS =
		'alignof as be box break const continue crate do else enum extern ' +
		'false fn for if impl in let loop match mod mut offsetof once priv ' +
		'proc pub pure ref return self Self sizeof static struct super trait true ' +
		'type typeof unsafe unsized use virtual while where yield move default';
	var BUILTINS =
		// functions
		'drop ' +
		// types
		'i8 i16 i32 i64 i128 isize ' +
		'u8 u16 u32 u64 u128 usize ' +
		'f32 f64 ' +
		'str char bool ' +
		'Box Option Result String Vec ' +
		// traits
		'Copy Send Sized Sync Drop Fn FnMut FnOnce ToOwned Clone Debug ' +
		'PartialEq PartialOrd Eq Ord AsRef AsMut Into From Default Iterator ' +
		'Extend IntoIterator DoubleEndedIterator ExactSizeIterator ' +
		'SliceConcatExt ToString ' +
		// macros
		'assert! assert_eq! bitflags! bytes! cfg! col! concat! concat_idents! ' +
		'debug_assert! debug_assert_eq! env! panic! file! format! format_args! ' +
		'include_bin! include_str! line! local_data_key! module_path! ' +
		'option_env! print! println! select! stringify! try! unimplemented! ' +
		'unreachable! vec! write! writeln! macro_rules! assert_ne! debug_assert_ne!';
	return {
		aliases: ['rs'],
		keywords: {
			keyword:
				KEYWORDS,
			literal:
				'true false Some None Ok Err',
			built_in:
				BUILTINS
		},
		lexemes: hljs.IDENT_RE + '!?',
		illegal: '</',
		contains: [
			hljs.C_LINE_COMMENT_MODE,
			hljs.COMMENT('/\\*', '\\*/', {contains: ['self']}),
			hljs.inherit(hljs.QUOTE_STRING_MODE, {begin: /b?"/, illegal: null}),
			{
				className: 'string',
				variants: [
					 { begin: /r(#*)"(.|\n)*?"\1(?!#)/ },
					 { begin: /b?'\\?(x\w{2}|u\w{4}|U\w{8}|.)'/ }
				]
			},
			{
				className: 'symbol',
				begin: /'[a-zA-Z_][a-zA-Z0-9_]*/
			},
			{
				className: 'number',
				variants: [
					{ begin: '\\b0b([01_]+)' + NUM_SUFFIX },
					{ begin: '\\b0o([0-7_]+)' + NUM_SUFFIX },
					{ begin: '\\b0x([A-Fa-f0-9_]+)' + NUM_SUFFIX },
					{ begin: '\\b(\\d[\\d_]*(\\.[0-9_]+)?([eE][+-]?[0-9_]+)?)' +
									 NUM_SUFFIX
					}
				],
				relevance: 0
			},
			{
				className: 'function',
				beginKeywords: 'fn', end: '(\\(|<)', excludeEnd: true,
				contains: [hljs.UNDERSCORE_TITLE_MODE]
			},
			{
				className: 'meta',
				begin: '#\\!?\\[', end: '\\]',
				contains: [
					{
						className: 'meta-string',
						begin: /"/, end: /"/
					}
				]
			},
			{
				className: 'class',
				beginKeywords: 'type', end: ';',
				contains: [
					hljs.inherit(hljs.UNDERSCORE_TITLE_MODE, {endsParent: true})
				],
				illegal: '\\S'
			},
			{
				className: 'class',
				beginKeywords: 'trait enum struct union', end: '{',
				contains: [
					hljs.inherit(hljs.UNDERSCORE_TITLE_MODE, {endsParent: true})
				],
				illegal: '[\\w\\d]'
			},
			{
				begin: hljs.IDENT_RE + '::',
				keywords: {built_in: BUILTINS}
			},
			{
				begin: '->'
			}
		]
	};
}
);

// #END

// === lang/tcl.js

/*
Language: Tcl
Author: Radek Liska <radekliska@gmail.com>
*/

// syntax/lang/tcl.js
// HighlightJs: v.9.13.1

hljs.registerLanguage('tcl',
function(hljs) {
	return {
		aliases: ['tk'],
		keywords: 'after append apply array auto_execok auto_import auto_load auto_mkindex ' +
			'auto_mkindex_old auto_qualify auto_reset bgerror binary break catch cd chan clock ' +
			'close concat continue dde dict encoding eof error eval exec exit expr fblocked ' +
			'fconfigure fcopy file fileevent filename flush for foreach format gets glob global ' +
			'history http if incr info interp join lappend|10 lassign|10 lindex|10 linsert|10 list ' +
			'llength|10 load lrange|10 lrepeat|10 lreplace|10 lreverse|10 lsearch|10 lset|10 lsort|10 '+
			'mathfunc mathop memory msgcat namespace open package parray pid pkg::create pkg_mkIndex '+
			'platform platform::shell proc puts pwd read refchan regexp registry regsub|10 rename '+
			'return safe scan seek set socket source split string subst switch tcl_endOfWord '+
			'tcl_findLibrary tcl_startOfNextWord tcl_startOfPreviousWord tcl_wordBreakAfter '+
			'tcl_wordBreakBefore tcltest tclvars tell time tm trace unknown unload unset update '+
			'uplevel upvar variable vwait while',
		contains: [
			hljs.COMMENT(';[ \\t]*#', '$'),
			hljs.COMMENT('^[ \\t]*#', '$'),
			{
				beginKeywords: 'proc',
				end: '[\\{]',
				excludeEnd: true,
				contains: [
					{
						className: 'title',
						begin: '[ \\t\\n\\r]+(::)?[a-zA-Z_]((::)?[a-zA-Z0-9_])*',
						end: '[ \\t\\n\\r]',
						endsWithParent: true,
						excludeEnd: true
					}
				]
			},
			{
				excludeEnd: true,
				variants: [
					{
						begin: '\\$(\\{)?(::)?[a-zA-Z_]((::)?[a-zA-Z0-9_])*\\(([a-zA-Z0-9_])*\\)',
						end: '[^a-zA-Z0-9_\\}\\$]'
					},
					{
						begin: '\\$(\\{)?(::)?[a-zA-Z_]((::)?[a-zA-Z0-9_])*',
						end: '(\\))?[^a-zA-Z0-9_\\}\\$]'
					}
				]
			},
			{
				className: 'string',
				contains: [hljs.BACKSLASH_ESCAPE],
				variants: [
					hljs.inherit(hljs.APOS_STRING_MODE, {illegal: null}),
					hljs.inherit(hljs.QUOTE_STRING_MODE, {illegal: null})
				]
			},
			{
				className: 'number',
				variants: [hljs.BINARY_NUMBER_MODE, hljs.C_NUMBER_MODE]
			}
		]
	}
}
);

// #END

// === lang/vala.js

/*
Language: Vala
Author: Antono Vasiljev <antono.vasiljev@gmail.com>
Description: Vala is a new programming language that aims to bring modern programming language features to GNOME developers without imposing any additional runtime requirements and without using a different ABI compared to applications and libraries written in C.
*/

// syntax/lang/vala.js
// HighlightJs: v.9.13.1.2 (fixed by unixman)

hljs.registerLanguage('vala',
function(hljs) {
	return {
		keywords: {
			keyword:
				// Value types
				'char uchar unichar int uint long ulong short ushort int8 int16 int32 int64 uint8 ' +
				'uint16 uint32 uint64 float double bool struct enum string void ' +
				// Reference types
				'weak unowned owned ' +
				// Modifiers
				'async signal static abstract interface override virtual delegate ' +
				// Control Structures
				'if while do for foreach else switch case break default return try catch ' +
				// Visibility
				'public private protected internal ' +
				// Other
				'using new this get set const stdout stdin stderr var',
			built_in:
				'DBus GLib CCode Gee Object Gtk Posix Soup Json WebKit SDL',
			literal:
				'false true null'
		},
		contains: [
			{
				className: 'class',
				beginKeywords: 'class interface namespace', end: '{', excludeEnd: true,
				illegal: '[^,:\\n\\s\\.]',
				contains: [
					hljs.UNDERSCORE_TITLE_MODE
				]
			},
			hljs.C_LINE_COMMENT_MODE,
			hljs.C_BLOCK_COMMENT_MODE,
			{
				className: 'string',
				begin: '"""', end: '"""',
				relevance: 5
			},
			hljs.APOS_STRING_MODE,
			hljs.QUOTE_STRING_MODE,
			hljs.C_NUMBER_MODE,
			{
				className: 'meta',
				begin: '^#', end: '$',
				relevance: 2
			}
		]
	};
}
);

// #END

// ===== [#]

// # JS Package: syntax-lang.pak.js :: #END#

