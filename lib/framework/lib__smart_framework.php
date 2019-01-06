<?php
// [LIB - Smart.Framework]
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//======================================================
// Smart-Framework v.3.7
//======================================================
// Requires PHP 5.6 or later
//======================================================
// this library should be loaded from app web root only
//======================================================

// [REGEX-SAFE-OK]

//--------------------------------------------------
define('SMART_FRAMEWORK_VERSION', 'smart.framework.v.3.7'); // required for framework to function
//--------------------------------------------------


//#####################################################################################
// LOAD FRAMEWORK LIBS 						!!! DO NOT CHANGE THE ORDER OF THE LIBS !!!
//#####################################################################################
//----------------------------------------------------
require('lib/framework/lib_unicode.php'); 		// smart unicode (support)
require('lib/framework/lib_smart.php'); 		// smart (base) core
require('lib/framework/lib_valid_parse.php');	// smart validators and parsers
require('lib/framework/lib_caching.php');		// smart cache (non-persistent + abstract persistent)
require('lib/framework/lib_translate.php');		// smart (text) translate
require('lib/framework/lib_crypto.php');		// smart crypto utils
require('lib/framework/lib_filesys.php');		// smart file system
require('lib/framework/lib_http_cli.php');		// smart http client
require('lib/framework/lib_templating.php');	// smart templating
require('lib/framework/lib_auth.php');			// smart authentication
require('lib/framework/lib_utils.php');			// smart utils
//----------------------------------------------------
//#####################################################################################


//=====================================================================================
//===================================================================================== INTERFACE START
//=====================================================================================


/**
 * Abstract Inteface Smart App Bootstrap
 * The extended object MUST NOT CONTAIN OTHER FUNCTIONS BECAUSE MAY NOT WORK as Expected !!!
 *
 * @access 		private
 * @internal
 *
 * @version 	v.170928
 *
 */
interface SmartInterfaceAppBootstrap {

	// :: INTERFACE


	//=====
	/**
	 * App Bootstrap Run :: This function is automatically called when App bootstraps.
	 * By example it can be used to connect to a database, install monitor or other operations.
	 * THIS MUST BE EXTENDED TO HANDLE THE REQUIRED CODE EXECUTION AT THE BOOTSTRAP RUN SEQUENCE
	 * RETURN: -
	 */
	public static function Run();
	//=====


	//=====
	/**
	 * App Bootstrap Authenticate :: This function must implement Authentication if any.
	 * IT MUST HANDLE OVERALL AUTHENTICATION (IF ANY) ...
	 * RETURN: -
	 */
	public static function Authenticate($area);
	//=====


} //END INTERFACE


//=====================================================================================
//===================================================================================== INTERFACE END
//=====================================================================================


//=====================================================================================
//===================================================================================== INTERFACE START
//=====================================================================================


/**
 * Abstract Inteface Smart App Info
 * The extended object MUST NOT CONTAIN OTHER FUNCTIONS BECAUSE MAY NOT WORK as Expected !!!
 *
 * @access 		private
 * @internal
 *
 * @version 	v.170928
 *
 */
interface SmartInterfaceAppInfo {

	// :: INTERFACE


	//=====
	/**
	 * Test if a specific App Template Exists
	 * RETURN: true or false
	 */
	public static function TestIfTemplateExists($y_template_name);
	//=====


	//=====
	/**
	 * Test if a specific App Module Exists
	 * RETURN: true or false
	 */
	public static function TestIfModuleExists($y_module_name);
	//=====


} //END INTERFACE


//=====================================================================================
//===================================================================================== INTERFACE END
//=====================================================================================



//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================


/**
 * Class: SmartAbstractAppController - Abstract Application Controller, provides the Abstract Definitions to create controllers in modules.
 *
 * <code>
 *
 * // Usage example: Create a new Index Controller (modules/my-module/my-controller.php)
 * // Will be accessible via: index.php?/page/my-module.my-controller (index.php?page=my-module.my-controller)
 * // or you can use short type as just: ?/page/my-module.my-controller (?page=my-module.my-controller)
 *
 * define('SMART_APP_MODULE_AREA', 'INDEX'); // this controller will run ONLY in index.php
 *
 * class SmartAppIndexController extends SmartAbstractAppController {
 *
 *     public function Run() {
 *
 *         $op = $this->RequestVarGet('op', '', 'string'); // get variable `op` from Request GET/POST
 *
 *         $this->PageViewSetCfg('template-path', 'my-template'); 		// will be using the template in the folder: etc/templates/my-template/
 *         $this->PageViewSetCfg('template-file', 'template-one.htm');	// will be using the template file: template-one.htm (located in: etc/templates/my-template/)
 *         //$this->PageViewSetCfg('template-file', 'template-modal.htm'); // or using the modal template
 *
 *         // the template `template-one.htm` contains several markers as): `title`, `left-column`, `main`, `right-column`, so we set them as:
 *         $this->PageViewSetVars([
 *             'title' => 'Hello World', // this marker is like <title>[####TITLE####]</title>
 *             'left-column' => 'Some content in the left column', // the marker will be put anywhere in the template html as: [####LEFT-COLUMN####]
 *             'main' => '<h1>Some content in the main area</h1>', // the `main` area must always be defined in a template as: [####MAIN####] ; when no template this variable will be redirected to the main output in the case of RAW pages (see the below example).
 *             'right-column' => 'Some content in the <b>right area</b>. Current Operation is: '.Smart::escape_html($op) // the marker will be put anywhere in the template html as: [####RIGHT-COLUMN####]
 *         ]);
 *
 *         // HINT - Escaping HTML:
 *         // is better to use: Smart::escape_html($var);
 *         // than htmlspecialchars($var, ENT_HTML401 | ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8', true);
 *         // if using htmlspecialchars($var); with no extra parameters is not safe for unicode environments
 *
 *         // HINT - Escaping JS (Safe exchanging variables between PHP and Javascript in HTML templates):
 *         // when you have to pass a javascript variable, in a marker like <script>my_js_var = '[####JS-VAR####]';</script>
 *         // use: Smart::escape_js('a value exchanged between PHP and Javascript in a safe mode');
 *
 *     } //END FUNCTION
 *
 * } //END CLASS
 *
 * //========================================================================================================
 *
 * // Another usage example: Create a new Admin Controller (modules/my-module/my-other-controller.php)
 * // Will be accessible via: admin.php?/page/my-module.my-other-controller (admin.php?page=my-module.my-other-controller)
 *
 * define('SMART_APP_MODULE_AREA', 'ADMIN'); // this controller will run ONLY in admin.php
 *
 * class SmartAppAdminController extends SmartAbstractAppController {
 *
 *     public function Run() {
 *
 *         $this->PageViewSetCfg('rawpage', true); // do a raw output, no templates are loaded (this example does a json output / an image or other non-html content ; can be used also for output of an image: jpg/gif/jpeg with the appropriate content headers)
 *
 *         $this->PageViewSetCfg('rawmime', 'text/javascript'); // set the content (mime) type ; this can also be for this example: 'application/json'
 *         //$this->PageViewSetCfg('rawdisp', 'inline'); // (optional, set the content disposition ; for pdf mime type you maybe would set this to 'attachment' instead on 'inline'
 *
 *         $this->PageViewSetVar(
 *             'main' => Smart::json_encode('Hello World, this is my json string') // this case have no marker template, but there is always a `main` output variable even when no template is used
 *         );
 *
 *     } //END FUNCTION
 *
 *     public function ShutDown() {
 *
 *         // This function is OPTIONAL in controllers and must be used only when needed as a destructor for both: SmartAppAdminController or SmartAppIndexController.
 *         // NOTICE: The PHP class destructor __destruct() have some bugs, is not always 100% safe.
 *         // See the PHP Bug #31570 for example (which is very old and not yet fixed ...).
 *         // thus, use always ShutDown() instead of __destruct() in all controllers when you need a destructor
 *
 *     } //END FUNCTION
 *
 * } //END CLASS
 *
 * </code>
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		needs to be extended as: SmartAppIndexController (as a controller of index.php) or SmartAppAdminController (as a controller of admin.php)
 *
 * @access 		PUBLIC
 * @depends 	-
 * @version 	v.181226
 * @package 	Application
 *
 */
abstract class SmartAbstractAppController { // {{{SYNC-ARRAY-MAKE-KEYS-LOWER}}}

	// -> ABSTRACT

	// It must NOT contain STATIC functions / Properties to avoid late state binding (self:: vs static::)

	//--
	private $directoutput;
	//--
	private $appenv;
	private $releasehash;
	private $modulearea;
	private $modulepath;
	private $modulename;
	private $module;
	private $action;
	private $controller;
	private $urlproto;
	private $urldomain;
	private $urlbasedomain;
	private $urlport;
	private $urlscript;
	private $urlpath;
	private $urladdr;
	private $urlpage;
	private $urlquery;
	private $urluri;
	private $uripath;
	private $lang;
	private $charset;
	private $timezone;
	//--
	private $pageheaders;
	private $pagesettings; 					// will allow keys just from $availsettings
	private $pageview; 						// will allow any key since they are markers
	private $availsettings = [ 				// list of allowed values for page settings ; used to validate the pagesettings keys by a fixed list: look in middlewares to see complete list
		'error', 'errhtml', 'redirect-url', // 		error message for return non 2xx/3xx codes ; optional error HTML message for return non 2xx/3xx codes ; redirect url for return 3xx codes
		'expires', 'modified',				// 		expires (int) in seconds from now ; last modification of the contents in seconds (int) timestamp: > 0 <= now
		'template-path', 'template-file',	// 		template path (@ for self module path or a relative path) ; template filename (ex: template.htm)
		'rawpage', 'rawmime', 'rawdisp',	// 		raw page (yes/no) ; raw mime (any valid mime type, ex: image/png) ; raw disposition (ex: inline / attachment / attachment; filename="somefile.pdf")
		'download-packet', 'download-key', 	// 		download packet ; download key
		'status-code'						// 		HTTP Status Code
	];
	//--


	//=====
	/**
	 * Class constructor.
	 * This is marked as FINAL and cannot be customized.
	 */
	final public function __construct($y_area, $y_module_path, $y_url_script, $y_url_path, $y_url_addr, $y_url_page, $y_controller) {
		//--
		if(defined('SMART_APP_MODULE_DIRECT_OUTPUT') AND (SMART_APP_MODULE_DIRECT_OUTPUT === true)) {
			$this->directoutput = true;
		} else {
			$this->directoutput = false;
		} //end if else
		//--
		$ctrl_arr = (array) explode('.', (string)$y_controller);
		//--
		$this->appenv 			= (string) (SMART_ERROR_HANDLER === 'log') ? 'prod' : 'dev'; 				// app environment: dev | prod :: {{{SYNC-APP-ENV-SETT}}}
		$this->releasehash 		= (string) SmartFrameworkRuntime::getAppReleaseHash(); 						// the release hash based on app framework version, framework release and modules version
		$this->modulearea 		= (string) $y_area; 														// index | admin
		$this->modulepath 		= (string) $y_module_path; 													// modules/mod-something/
		$this->modulename 		= (string) Smart::base_name($y_module_path); 								// mod-something
		$this->module 			= (string) $ctrl_arr[0]; 													// something
		$this->action 			= (string) $ctrl_arr[1]; 													// someaction
		$this->controller 		= (string) $y_controller; 													// something.someaction
		$this->urlproto 		= (string) SmartUtils::get_server_current_protocol(); 						// http:// | https://
		$this->urlbasedomain 	= (string) SmartUtils::get_server_current_basedomain_name();				// 127.0.0.1|localhost|dom.ext
		$this->urldomain 		= (string) SmartUtils::get_server_current_domain_name(); 					// 127.0.0.1|localhost|sdom.dom.ext
		$this->urlport 			= (string) SmartUtils::get_server_current_port(); 							// 80 | 443 | ...
		$this->urlscript 		= (string) $y_url_script; 													// index.php | admin.php
		$this->urlpath 			= (string) $y_url_path; 													// /frameworks/smart-framework/
		$this->urladdr 			= (string) $y_url_addr; 													// http(s)://127.0.0.1|localhost:8008/frameworks/smart-framework/
		$this->urlpage 			= (string) $y_url_page; 													// this may vary depending on semantic URL rule but can be equal with: something.someaction | someaction | something
		$this->urlquery 		= (string) $_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''; 	// the query URL if any ...
		$this->urluri 			= (string) SmartUtils::get_server_current_request_uri(); 					// the REQUEST_URI
		$this->uripath 			= (string) SmartUtils::get_server_current_request_path(); 					// the PATH_INFO
		$this->lang 			= (string) SmartTextTranslations::getLanguage(); 							// current language (ex: en)
		$this->charset 			= (string) SMART_FRAMEWORK_CHARSET;											// current charset (ex: UTF-8)
		$this->timezone 		= (string) SMART_FRAMEWORK_TIMEZONE; 										// current timezone (ex: UTC)
		//--
		$this->pageheaders 		= array();
		$this->pagesettings 	= array();
		$this->pageview 		= array();
		$this->availsettings 	= (array) $this->availsettings;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Class Destructor.
	 * This is marked as FINAL and cannot be customized.
	 * Use the ShutDown() function as destructor, it will be called after Run() safely prior to destruct this class.
	 *
	 * The class destructors are not safe in controller instances.
	 * See the comments from ShutDown() function in this class !
	 */
	final public function __destruct() {
		// This is not safe so we define it as final to avoid re-define later, see function ShutDown() below !!!
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Test if Debug is On
	 *
	 * If Debug is turned on, this area of Debug messages will be displayed in Modules section
	 *
	 * @return 	BOOLEAN					:: TRUE if Debug is ON, FALSE if not
	 */
	final public function IfDebug() {
		//--
		return (bool) SmartFrameworkRuntime::ifDebug();
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set Custom Debug Data
	 *
	 * If Debug is turned on, this area of Debug messages will be displayed in Modules section
	 *
	 * @param 	STRING 	$title 			:: A title for the debug message.
	 * @param 	MIXED 	$debug_msg 		:: The data for the debug message. Ex: STRING / ARRAY
	 *
	 * @return 	BOOLEAN					:: TRUE if successful, FALSE if not
	 */
	final public function SetDebugData($title, $debug_msg) {
		//--
		if(!$this->IfDebug()) {
			Smart::log_notice('Page Controller: '.$this->controller.' # NOTICE: Modules/SetDebugData must be set only if Modules/IfDebug() is TRUE ... else will slow down the execution. Consider to Add SetDebugData() in a context as if($this->IfDebug()){ $this->SetDebugData(\'Debug title\', \'A debug message ...\'); } ...');
			return false;
		} //end if
		//--
		if(is_array($debug_msg) OR is_object($debug_msg)) {
			$debug_msg = (string) print_r($debug_msg,1);
		} //end if
		//--
		SmartFrameworkRegistry::setDebugMsg('modules', (string)$this->modulename, [
			'title' => (string) $title,
			'data' => (string) $debug_msg
		]);
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get the value for a Controller parameter
	 *
	 * @param 	ENUM 		$param 		:: The selected parameter
	 * The valid param values are:
	 * 		app-env 					:: 		ex: dev | prod (based on SMART_ERROR_HANDLER)
	 * 		app-namespace 				:: 		ex: smartframework.default (the app namespace as defined in etc/init.php)
	 * 		app-domain 					:: 		ex: 127.0.0.1|localhost|sdom.dom.ext|dom.ext (the domain set in configs, that may differ by area: $configs['app']['index-domain'] | $configs['app']['admin-domain'])
	 * 		release-hash 				:: 		ex: 29bp3w (the release hash based on app framework version, framework release and modules version)
	 * 		module-area 				:: 		ex: index / admin
	 * 		module-name 				:: 		ex: mod-samples
	 * 		module-path 				:: 		ex: modules/mod-samples/
	 * 		module-view-path 			:: 		ex: modules/mod-samples/views/
	 * 		module-model-path 			:: 		ex: modules/mod-samples/models/
	 * 		module-lib-path 			:: 		ex: modules/mod-samples/libs/
	 * 		module-tpl-path 			:: 		ex: modules/mod-samples/templates/
	 * 		module-plugins-path 		:: 		ex: modules/mod-samples/plugins/
	 * 		module-translations-path 	:: 		ex: modules/mod-samples/translations/
	 * 		module 						:: 		ex: samples (1st part from controller, before .)
	 * 		action 						:: 		ex: test (2nd part from controller, after .)
	 * 		controller 					:: 		ex: samples.test
	 * 		url-proto 					:: 		ex: http | https (the current server protocol)
	 * 		url-proto-addr 				:: 		ex: http:// | https:// (the current server protocol address)
	 * 		url-basedomain 				:: 		ex: 127.0.0.1|localhost|dom.ext (the curent server base domain, or IP)
	 * 		url-domain 					:: 		ex: 127.0.0.1|localhost|sdom.dom.ext (the curent server domain, or IP)
	 * 		url-port 					:: 		ex: 80 | 443 | 8080 ... (the current server port)
	 * 		url-port-addr 				:: 		ex: '' | ''  | ':8080' ... (the current server port address ; empty for port 80 and 443 ; for the rest of ports will be :portnumber)
	 * 		url-script 					:: 		ex: index.php / admin.php
	 * 		url-path 					:: 		ex: /sites/smart-framework/
	 * 		url-addr 					:: 		ex: http(s)://127.0.0.1|localhost/sites/smart-framework/
	 * 		url-page 					:: 		ex: samples.test | test  (if samples is the default module) ; this is returning the URL page variable as is in the URL (it can be the same as 'controller' or if rewrite is used inside framework can vary
	 * 		url-query 					:: 		ex: ?page=test&ofs=10
	 * 		url-uri 					:: 		ex: /sites/smart-framework/index|admin.php{/some/path/}?page=test&ofs=10
	 * 		uri-path 					:: 		ex: {/some/path/}
	 *		lang 						:: 		ex: en
	 *		charset 					:: 		ex: UTF-8
	 * 		timezone 					:: 		ex: UTC
	 *
	 * @return 	STRING					:: The value for the selected parameter
	 */
	final public function ControllerGetParam($param) {
		//--
		$param = (string) strtolower((string)$param);
		//--
		$out = '';
		//--
		switch((string)$param) {
			case 'app-env':
				$out = $this->appenv;
				break;
			case 'app-domain':
				$out = Smart::get_from_config('app.'.$this->modulearea.'-domain');
				break;
			case 'app-namespace':
				$out = SMART_SOFTWARE_NAMESPACE;
				break;
			case 'app-realm':
				$out = ($this->modulearea === 'admin') ? 'ADM' : 'IDX';
				break;
			case 'release-hash':
				$out = $this->releasehash;
				break;
			case 'module-area':
				$out = $this->modulearea;
				break;
			case 'module-name':
				$out = $this->modulename;
				break;
			case 'module-path':
				$out = $this->modulepath;
				break;
			case 'module-view-path':
				$out = $this->modulepath.'views/';
				break;
			case 'module-model-path':
				$out = $this->modulepath.'models/';
				break;
			case 'module-lib-path':
				$out = $this->modulepath.'libs/';
				break;
			case 'module-tpl-path':
				$out = $this->modulepath.'templates/';
				break;
			case 'module-plugins-path':
				$out = $this->modulepath.'plugins/';
				break;
			case 'module-translations-path':
				$out = $this->modulepath.'translations/';
				break;
			case 'module':
				$out = $this->module;
				break;
			case 'action':
				$out = $this->action;
				break;
			case 'controller':
				$out = $this->controller;
				break;
			case 'url-proto':
				$out = ((string)$this->urlproto == 'https://') ? 'https' : 'http';
				break;
			case 'url-proto-addr':
				$out = $this->urlproto;
				break;
			case 'url-basedomain':
				$out = $this->urlbasedomain;
				break;
			case 'url-domain':
				$out = $this->urldomain;
				break;
			case 'url-port':
				$out = $this->urlport;
				break;
			case 'url-port-addr':
				$out = ((($this->urlport == 80) || ($this->urlport == 443)) ? '' : ':'.$this->urlport);
				break;
			case 'url-script':
				$out = $this->urlscript;
				break;
			case 'url-path':
				$out = $this->urlpath;
				break;
			case 'url-addr':
				$out = $this->urladdr;
				break;
			case 'url-page':
				$out = $this->urlpage;
				break;
			case 'url-query':
				$out = $this->urlquery;
				break;
			case 'url-uri':
				$out = $this->urluri;
				break;
			case 'uri-path':
				$out = $this->uripath;
				break;
			case 'lang':
				$out = $this->lang;
				break;
			case 'charset':
				$out = $this->charset;
				break;
			case 'timezone':
				$out = $this->timezone;
				break;
			default:
				Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / ControllerGetParam: Invalid Parameter: '.$param);
		} //end switch
		//--
		return (string) $out;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get the value for a Config parameter from the app $configs array
	 *
	 * @param 	ENUM 		$param 		:: The selected configuration parameter ; Examples: 'app.info-url' will get value from $configs['app']['info-url'] ; 'regional.decimal-separator' will get the value (string) from $configs['regional']['decimal-separator'] ; 'regional' will get the value (array) from $configs['regional']
	 *
	 * @return 	MIXED					:: The value for the selected parameter. If the Config parameter does not exists, will return an empty string
	 */
	final public function ConfigParamGet($param) {
		//--
		return Smart::get_from_config($param); // mixed
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get the HTTP Request Method (REQUEST_METHOD) in a controller
	 *
	 * @return 	STRING					:: The value of the REQUEST_METHOD HTTP Variable (from server-side)
	 */
	final public function RequestMethodGet() {
		//--
		return (string) SmartUtils::get_server_current_request_method(); // string
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get the Path Request Variable (PATH_INFO) in a controller
	 *
	 * @return 	STRING					:: The value of the PATH_INFO Request if Set or Empty String
	 */
	final public function RequestPathGet() {
		//--
		return (string) SmartFrameworkRegistry::getRequestPath(); // string
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get a Request Variable (GET/POST) in a controller
	 *
	 * @param 	STRING 		$key		:: The name (key) of the GET or POST variable (if the variable is set in both GET and POST, the GPC as set in PHP.INI sequence will overwrite the GET with POST, thus the POST value will be get).
	 * @param	MIXED		$defval		:: The default value (if a type is set must be the same type) of that variable in the case was not set in the Request (GET/POST). By default it is set to null.
	 * @param	ENUM		$type		:: The type of the variable ; Default is '' (no enforcing). This can be used to enforce a type for the variable as: ['enum', 'list', 'of', 'allowed', 'values'], 'array', 'string', 'boolean', 'integer', 'integer+', 'integer-', 'decimal1', 'decimal2', 'decimal3', 'decimal4', 'numeric'.
	 *
	 * @return 	MIXED					:: The value of the choosen Request (GET/POST) variable
	 */
	final public function RequestVarGet($key, $defval=null, $type='') { // {{{SYNC-REQUEST-DEF-PARAMS}}}
		//--
		return SmartFrameworkRegistry::getRequestVar($key, $defval, $type); // mixed
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get a Cookie Variable (COOKIES) in a controller
	 *
	 * @param 	STRING 		$name		:: The cookie name from COOKIES variable.
	 *
	 * @return 	MIXED					:: The value of the choosen Cookie variable or null if not set
	 */
	final public function CookieVarGet($name) {
		//--
		return SmartUtils::get_cookie($name); // mixed: null / string
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a Cookie Variable (COOKIES) in a controller
	 *
	 * @param 	STRING 		$name		:: The cookie name
	 * @param 	STRING 		$data		:: The cookie data
	 * @param 	STRING 		$expire		:: The cookie expire time (zero for session cookies)
	 * @param 	STRING 		$path		:: The cookie path ; default is /
	 * @param 	STRING 		$domain		:: The cookie domain ; default is @ (will get as it is set in SMART_FRAMEWORK_UNIQUE_ID_COOKIE_DOMAIN)
	 *
	 * @return 	BOOLEAN					:: TRUE if Set, FALSE if Not
	 */
	final public function CookieVarSet($name, $data, $expire, $path='/', $domain='@') {
		//--
		return (bool) SmartUtils::set_cookie($name, $data, $expire, $path, $domain);
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get all the current controller PageView Data: (Raw) Headers, Cfgs and Vars
	 * The general purpose of this function is to get all Page Data at once to export into persistent cache.
	 *
	 * @return 	ARRAY					:: an associative array as: [ heads => PageViewGetRawHeaders() ; cfgs => PageViewGetCfgs() ; vars => PageViewGetVars() ]
	 */
	final public function PageViewGetData() {
		//--
		return (array) [
			'heads' 	=> (array) $this->PageViewGetRawHeaders(),
			'cfgs' 		=> (array) $this->PageViewGetCfgs(),
			'vars' 		=> (array) $this->PageViewGetVars()
		];
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set all the current controller PageView Data: (Raw) Headers, Cfgs and Vars
	 * The general purpose of this function is to set all Page Data at once when imported from persistent cache.
	 *
	 * @param 	ARRAY 		$data		:: an associative array as: [ heads => PageViewGetRawHeaders() ; cfgs => PageViewGetCfgs() ; vars => PageViewGetVars() ]
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetData($data) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if(!is_array($data)) {
			return false; // $data must be array
		} //end if
		//--
		$data = (array) array_change_key_case((array)$data, CASE_LOWER); // make all keys lower
		//--
		if(!is_array($data['heads'])) {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetData: Invalid Heads');
			return false;
		} //end if
		if(!is_array($data['cfgs'])) {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetData: Invalid Cfgs');
			return false;
		} //end if
		if(!is_array($data['vars'])) {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetData: Invalid Vars');
			return false;
		} //end if
		//--
		if($this->PageViewSetRawHeaders($data['heads']) !== true) {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetData: Failed to Set Heads');
			return false;
		} //end if
		if($this->PageViewSetCfgs($data['cfgs']) !== true) {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetData: Failed to Set Cfgs');
			return false;
		} //end if
		if($this->PageViewSetVars($data['vars']) !== true) {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetData: Failed to Set Vars');
			return false;
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get all the current controller PageView Headers (Raw) that will be mapped to Raw HTTP Header(s)
	 *
	 * @return 	ARRAY					:: an associative array with all controller Page View (Raw) Headers currently set
	 */
	final public function PageViewGetRawHeaders() {
		//--
		return (array) $this->pageheaders;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a list with multiple values for RawHeaders into the current controller as PageView Headers (Raw) that will be mapped to Raw HTTP Header(s)
	 *
	 * @param 	ARRAY 		$entries	:: an associative array to be set with elements for each raw header entry [ 'Header Key 1' => 'Header Entry One', 'Header Key 2' => 'Header Entry Two', ..., 'Header Key n' => 'Header Entry N' ]
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetRawHeaders($entries) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if(!is_array($entries)) {
			return false; // $entries must be array
		} //end if
		//--
		$ok = true;
		//--
		foreach($entries as $key => $val) {
			$test = $this->PageViewSetRawHeader($key, $val);
			if($test !== true) {
				$ok = false;
			} //end if
		} //end foreach
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a single value for settings into the current controller as PageView Headers (Raw) that will be mapped to Raw HTTP Header(s)
	 *
	 * @param 	STRING 		$param		:: the header key 		(Ex: 'X-XSS-Protection')
	 * @param 	STRING 		$value		:: the header value 	(Ex: '1; mode=block')
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetRawHeader($param, $value) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if((is_array($param)) OR (is_object($param)) OR ((string)$param == '') OR (is_array($value)) OR (is_object($value))) {
			return false;
		} //end if
		//--
		$param = (string) Smart::normalize_spaces((string)$param); // safety
		$param = (string) preg_replace('/[^0-9a-zA-Z\-]/', '', (string)$param); // allow just A-Z a-z 0-9 -
		$param = (string) str_replace(' ', '', (string)$param); // remove any remaining spaces ... (to be sure)
		$param = (string) trim((string)$param); // trim
		if((string)$param == '') {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetRawHeader: Invalid Parameter: '.$param);
		} //end if
		//--
		$value = (string) Smart::normalize_spaces((string)$value); // safety
		$value = (string) trim((string)$value); // trim
		//--
		if((string)$param == '') {
			Smart::log_notice('Page Controller: '.$this->controller.' # SmartAbstractAppController / PageViewSetRawHeader: Empty Key / Parameter');
			return false;
		} //end if
		//--
		$this->pageheaders[(string)$param] = (string) $value; // IMPORTANT: Value can be empty (Ex: 'Expect:')
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Reset all variables for the current controller into PageView Headers (Raw) that will be mapped to Raw HTTP Header(s)
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewResetRawHeaders() {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		$this->pageheaders = array();
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get all the current controller PageView Settings (Cfgs)
	 *
	 * @return 	ARRAY					:: an associative array with all controller Page View Cfgs. (Settings) currently set
	 */
	final public function PageViewGetCfgs() {
		//--
		return (array) $this->pagesettings;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a list with multiple values for settings into the current controller as PageView Settings (Cfgs)
	 *
	 * @param 	ARRAY 		$params		:: an associative array to be set as [ 'param1' => 'value1', ..., 'param-n' => 'val-n' ]
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetCfgs($params) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if(!is_array($params)) {
			return false; // $params must be array
		} //end if
		//--
		$params = (array) array_change_key_case((array)$params, CASE_LOWER); // make all keys lower
		//--
		$ok = true;
		//--
		foreach($params as $key => $val) {
			$test = $this->PageViewSetCfg($key, $val);
			if($test !== true) {
				$ok = false;
			} //end if
		} //end foreach
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a single value for settings into the current controller as PageView Settings (Cfgs)
	 *
	 * @param 	STRING 		$param		:: the parameter to be set
	 * @param 	STRING 		$value		:: the value
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetCfg($param, $value) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if((is_array($param)) OR (is_object($param)) OR ((string)$param == '') OR (is_array($value)) OR (is_object($value))) {
			Smart::log_notice('Page Controller: '.$this->controller.' # '.__METHOD__.'(): Invalid Parameter Type: '.$param);
			return false;
		} //end if
		//--
		$param = (string) strtolower((string)$param);
		//--
		if(is_bool($value)) { // fix for bool
			if($value === true) {
				$value = 'yes'; // true
			} elseif($value === false) {
				$value = ''; // false
			} //end if else
		} //end if
		if(in_array((string)$param, (array)$this->availsettings)) {
			$this->pagesettings[(string)$param] = (string)$value;
		} else {
			Smart::log_notice('Page Controller: '.$this->controller.' # '.__METHOD__.'(): Invalid Parameter: '.$param);
			return false;
		} //end if else
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Reset all variables for the current controller into PageView Settings (Cfgs)
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewResetCfgs() {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		$this->pagesettings = array();
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get STATUS Code for a controller page
	 *
	 * @return 	ENUM 		$code		:: the HTTP Status Code: 2xx, 3xx, 4xx, 5xx, ... (consult middleware documentation to see what is supported) ; if an invalid error status code is used then 200 will be used instead
	 */
	final public function PageViewGetStatusCode() {
		//--
		$code = 200; // default
		//--
		if((int)$this->pagesettings['status-code'] > (int)$code) {
			$code = (int) $this->pagesettings['status-code'];
		} //end if
		//--
		return (int) $code;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set OK (2xx) STATUS Code for a controller page
	 *
	 * @param 	ENUM 		$code		:: the HTTP OK Status Code: 200, 202, 203, 208, ... (consult middleware documentation to see what is supported) ; if an invalid error status code is used then 200 will be used instead
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetOkStatus($code) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		$code = (int) $code;
		if(!in_array((int)$code, (array)SmartFrameworkRuntime::getHttpStatusCodesOK())) { // in the case that the http status code is n/a, use 200 instead
			Smart::log_notice('Page Controller: '.$this->controller.' # '.__METHOD__.'(): Invalid OK Status Code ('.$code.')');
			$code = 200;
		} //end if
		//--
		return (bool) $this->PageViewSetCfg(
			'status-code', (int)$code
		);
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set Error Status and Optional Message for a controller page
	 * The Controller should stop the execution after calling this function using 'return;' or ending the 'Run()' main function
	 *
	 * @param 	ENUM 			$code		:: the HTTP Error Status Code: 400, 403, 404, 500, 503, ... (consult middleware documentation to see what is supported) ; if an invalid error status code is used then 500 will be used instead
	 * @param 	STRING/ARRAY 	$msg 		:: The detailed message that will be displayed public near the status code ; can be string or array [ 0 => message ; 1 => htmlmessage ]
	 * @param 	BOOLEAN 		$logtype 	:: *Optional* ; Default is '' ; available values: '' | 'WARN' | 'NOTICE'
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetErrorStatus($code, $msg='', $logtype='') {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		$code = (int) $code;
		if(is_array($msg)) {
			$message = (string) $msg[0];
			$htmlmsg = (string) $msg[1];
		} else {
			$message = (string) $msg;
			$htmlmsg = '';
		} //end if else
		//--
		$out = true;
		if(!in_array((int)$code, (array)SmartFrameworkRuntime::getHttpStatusCodesERR())) { // in the case that the error status code is n/a, use 500 instead
			$out = false;
			$code = 500;
			Smart::log_notice('Page Controller: '.$this->controller.' # '.__METHOD__.'(): Invalid HTTP Error Status Code ('.$code.')');
		} else {
			$out = (bool) $this->PageViewSetCfgs([
				'status-code' 	=> (int) $code,
				'error' 		=> (string) $message,
				'errhtml' 		=> (string) $htmlmsg
			]);
		} //end if else
		//--
		switch((string)strtoupper((string)$logtype)) {
			case 'NOTICE':
				Smart::log_notice('Page Controller Log NOTICE # ('.$this->controller.'): [Status-Code:'.(int)$code.'] '.(string)$message);
				break;
			case 'WARN':
				Smart::log_warning('Page Controller Log WARNING # ('.$this->controller.'): [Status-Code:'.(int)$code.'] '.(string)$message);
				break;
			default:
				// no log
		} //end switch
		//--
		return (bool) $out;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set Redirect URL for a controller page
	 * The Controller should stop the execution after calling this function using 'return;' or ending the 'Run()' main function
	 *
	 * @param 	STRING 		$url 		:: The absolute URL to redirect the page to (Ex: http://some-domain.ext/some-page.html)
	 * @param 	ENUM 		$code		:: the HTTP Error Status Code: 301, 302, ... (consult middleware documentation to see what is supported) ; if an invalid error status code is used then 302 will be used instead
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetRedirectUrl($url, $code) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		$url = (string) $url;
		if((string)$url == '') {
			return false;
		} //end if
		//--
		$code = (int) $code;
		if(!in_array((int)$code, (array)SmartFrameworkRuntime::getHttpStatusCodesRDR())) { // in the case that the redirect status code is n/a, use 302 instead
			Smart::log_notice('Page Controller: '.$this->controller.' # '.__METHOD__.'(): Invalid Redirect Status Code ('.$code.')');
			$code = 302;
		} //end if
		//--
		return (bool) $this->PageViewSetCfgs([
			'status-code' 	=> (int) $code,
			'redirect-url' 	=> (string) $url
		]);
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get all the current controller PageView Vars
	 *
	 * @return 	ARRAY					:: an associative array with all the controller Page View variables currently set
	 */
	final public function PageViewGetVars() {
		//--
		return (array) $this->pageview;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a list with multiple values for variables into the current controller into PageView Vars
	 *
	 * @param 	ARRAY 		$params		:: an associative array to be set as [ 'variable1' => 'value1', ..., 'variable-n' => 'val-n' ]
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetVars($params) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if(!is_array($params)) {
			return false; // $params must be array
		} //end if
		//--
		$params = (array) array_change_key_case((array)$params, CASE_LOWER); // make all keys lower
		//--
		$ok = true;
		//--
		foreach($params as $key => $val) {
			$test = $this->PageViewSetVar($key, $val);
			if($test !== true) {
				$ok = false;
			} //end if
		} //end foreach
		//--
		return (bool) $ok;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a single value for the current controller into PageView Vars
	 *
	 * @param 	STRING 		$param		:: the variable to be set
	 * @param 	STRING 		$value		:: the value
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewSetVar($param, $value) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if((is_array($param)) OR (is_object($param)) OR ((string)$param == '') OR (is_array($value)) OR (is_object($value))) {
			return false;
		} //end if
		//--
		$param = (string) strtolower((string)$param);
		//--
		$this->pageview[(string)$param] = (string)$value; // set
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Prepend a single value to a variable for the current controller into PageView Vars
	 *
	 * @param 	STRING 		$param		:: the variable to prepend value to
	 * @param 	STRING 		$value		:: the value
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewPrependVar($param, $value) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if((is_array($param)) OR (is_object($param)) OR ((string)$param == '') OR (is_array($value)) OR (is_object($value))) {
			return false;
		} //end if
		//--
		$param = (string) strtolower((string)$param);
		$value = (string) $value;
		//--
		if(!array_key_exists((string)$param, $this->pageview)) {
			$this->pageview[(string)$param] = ''; // init
		} //end if
		$this->pageview[(string)$param] = (string) $value.$this->pageview[(string)$param]; // prepend
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Append a single value to a variable for the current controller into PageView Vars
	 *
	 * @param 	STRING 		$param		:: the variable to append value to
	 * @param 	STRING 		$value		:: the value
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewAppendVar($param, $value) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if((is_array($param)) OR (is_object($param)) OR ((string)$param == '') OR (is_array($value)) OR (is_object($value))) {
			return false;
		} //end if
		//--
		$param = (string) strtolower((string)$param);
		//--
		if(!array_key_exists((string)$param, $this->pageview)) {
			$this->pageview[(string)$param] = ''; // init
		} //end if
		$this->pageview[(string)$param] .= (string) $value; // append
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Reset all variables for the current controller into PageView Vars
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewResetVars() {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		$this->pageview = array();
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Reset a single variable value for the current controller into PageView Vars
	 *
	 * @param 	STRING 		$param		:: the variable to be reset (unset)
	 *
	 * @return 	BOOL					:: TRUE if OK, FALSE if not
	 */
	final public function PageViewResetVar($param) {
		//--
		if($this->directoutput === true) {
			Smart::log_warning('Page Controller: '.$this->controller.' # '.__METHOD__.'(): This method is not available for Direct Output Mode ...');
			return false;
		} //end if
		//--
		if((is_array($param)) OR (is_object($param))) {
			return false;
		} //end if
		//--
		$param = (string) strtolower((string)$param);
		//--
		if((string)$param != '') {
			$this->pageview[(string)$param] = '';
			unset($this->pageview[(string)$param]);
		} //end if
		//--
		return true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Test if the Page Cache (system) is active or not
	 * This is based on PersistentCache
	 *
	 * @return 	BOOL					:: TRUE if Active, FALSE if not
	 */
	final public function PageCacheisActive() {
		//--
		return (bool) SmartPersistentCache::isActive();
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Prepare a Page Cache SAFE Key or Realm
	 * This is based on PersistentCache
	 *
	 * @return 	STRING					:: The safe prepared Key or Realm
	 */
	final public function PageCacheSafeKey($y_key_or_realm) {
		//--
		return (string) SmartPersistentCache::safeKey((string)$y_key_or_realm);
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get a Page from the (Persistent) Cache
	 *
	 * @param 	STRING 		$storage_namespace		:: the cache storage namespace, used to group keys
	 * @param 	STRING 		$unique_key				:: the unique cache key
	 *
	 * @return 	MIXED								:: If the PersistentCache is active and value was set will return a single (STRING) or multiple (ARRAY) Page Settings / Page Values ; otherwise will return a NULL value.
	 */
	final public function PageGetFromCache($storage_namespace, $unique_key) {
		//--
		if(!SmartPersistentCache::isActive()) {
			return null;
		} //end if
		//--
		if(!SmartPersistentCache::keyExists((string)$storage_namespace, (string)$unique_key)) {
			return null;
		} //end if
		//--
		$cache = SmartPersistentCache::getKey(
			(string) $storage_namespace,
			(string) $unique_key
		);
		//--
		if(($cache === null) OR ((string)$cache == '')) {
			return null;
		} //end if
		//--
		return SmartPersistentCache::varUncompress($cache); // mixed (number / string / array)
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Set a Page into the (Persistent) Cache
	 *
	 * @param 	STRING 		$storage_namespace		:: the cache storage namespace, used to group keys
	 * @param 	STRING 		$unique_key				:: the unique cache key
	 * @param 	MIXED 		$content 				:: the cache content as a STRING or an ARRAY with Page Value(s) / Page Setting(s)
	 * @param 	INTEGER 	$expiration 			:: The page cache expiration in seconds ; 0 will not expire
	 *
	 * @return 	BOOL								:: TRUE if the PersistentCache is active and value was set ; FALSE in the rest of the cases
	 */
	final public function PageSetInCache($storage_namespace, $unique_key, $content, $expiration) {
		//--
		if($content === null) { // must allow empty array ; dissalow null as null is the returned result by get key if key not found
			return false;
		} //end if
		//--
		if(!SmartPersistentCache::isActive()) {
			return false;
		} //end if
		//--
		$cache = SmartPersistentCache::varCompress($content); // mixed (number / string / array)
		if((string)$cache == '') {
			return false;
		} //end if
		//--
		return SmartPersistentCache::setKey(
			(string) $storage_namespace,
			(string) $unique_key,
			(string) $cache,
			(int)    $expiration // expiration time in seconds
		);
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Unset a Page from the (Persistent) Cache
	 *
	 * @param 	STRING 		$storage_namespace		:: the cache storage namespace, used to group keys
	 * @param 	STRING 		$unique_key				:: the unique cache key
	 *
	 * @return 	BOOL								:: TRUE if the PersistentCache is active and value was unset ; FALSE in the rest of the cases
	 */
	final public function PageUnsetFromCache($storage_namespace, $unique_key) {
		//--
		return SmartPersistentCache::unsetKey(
			(string) $storage_namespace,
			(string) $unique_key
		);
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Force Instant Flush Output (ONLY for controllers that do a direct output ... SMART_APP_MODULE_DIRECT_OUTPUT must be defined and set to TRUE).
	 * This will do instant flush and also ob_flush if necessary and detected (for example when the output_buffering is enabled in PHP.INI).
	 * NOTICE: be careful using this function to avoid break intermediary output bufferings !!
	 *
	 * @access 		private
	 * @internal
	 *
	 * @return -						:: This function does not return anything
	 */
	final public function InstantFlush() {
		//--
		if($this->directoutput === true) { // OK
			//--
			$output_buffering_status = @ob_get_status();
			//-- type: 0 = PHP_OUTPUT_HANDLER_INTERNAL ; 1 = PHP_OUTPUT_HANDLER_USER
			if(is_array($output_buffering_status)) {
				if(((string)$output_buffering_status['type'] == '0') AND ($output_buffering_status['chunk_size'] > 0)) { // avoid to break user level output buffering(s), so enable this just for level zero (internal, if set in php.ini)
					@ob_flush();
				} //end if
			} //end if
			//--
			@flush();
			//--
		} else { // WARNING: N/A
			//--
			Smart::log_warning('Page Controller: '.$this->controller.' # Using the InstantFlush() in controllers that are not using direct output is not allowed as will break the middleware output chain ...');
			//--
		} //end if
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * The Controller Runtime - This function is required to be re-defined in all controllers
	 *
	 * @return 	INTEGER					:: *OPTIONAL* The HTTP Status Code: by default it does not return or it must returns 200 which is optional ; other supported HTTP Status Codes are: 202/203/208 (OK with notice/warning/error messages - used only for REST/APIs), 500 (Internal Error), 404 (Not Found), 403 (Forbidden), 401 (Authentication Required), 400 (Error) ; if the HTTP status code is not 200, an extra notification message can be set as: ##EXAMPLE: $this->PageViewSetCfg('error', 'Access to this page is restricted'); return 403; ## - to have also a detailed error message to be shown near the HTTP status code)
	 */
	abstract public function Run(); //END FUNCTION
	//=====


	//=====
	/**
	 * This is the pre Run() function
	 * This function will be called before Run()
	 *
	 */
	public function Initialize() {
		// *** optional*** can be redefined in a controller (as a pre-run init, if required) but is not mandatory ...
	} //END FUNCTION
	//=====


	//=====
	/**
	 * This is the post Run() function
	 * This function will be called after Run()
	 *
	 * This function is the (real) Controller Destructor - This function is optional and can be re-defined in controllers where a destructor is required.
	 * It will replace the class destructor __destruct() which is NOT SAFE in all cases (see php bug #31570).
	 * NOTICE:
	 * Sometimes __destruct() for classes is not 100% safe ; example: the PHP Bug #31570 (not fixed since a very long time).
	 * Instead of __destruct() use ShutDown() method for controllers in framework modules (which is always executed after Run() and is 100% safe).
	 * WARNING:
	 * Modifications for Page Settings or Page Variables are not allowed in this function, after Run() has been completed !
	 * If controller variables are modified after Run() has completed it can produce unexpected results ...
	 * EXAMPLE / SCENARIO:
	 * This function (by replacing __destruct()) can be used if you have to cleanup a temporary folder (tmp/) after Run().
	 * Because of the PHP Bug #31570, the __destruct() can't operate on relative paths and will produce wrong and unexpected results !!!
	 *
	 */
	public function ShutDown() {
		// *** optional*** can be redefined in a controller (as a post-run init, as safe destructor, if required) but is not mandatory ...
	} //END FUNCTION
	//=====


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>