<?php
// [APP - Request Handler / SmartFramework]
// (c) 2006-2016 unix-world.org - all rights reserved

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

// v.2.3.1.9 r.2016.04.29 / smart.framework.v.2.3

//======================================================
// Smart-Framework - App Request Handler
// DEPENDS: SmartFramework, SmartFrameworkRuntime
//======================================================
// This file can be customized per App ...
// DO NOT MODIFY ! IT IS CUSTOMIZED FOR: Smart.Framework
//======================================================

//##### WARNING: #####
// Changing the code below is on your own risk and may lead to severe disrupts in the execution of this software !
// This part registers the request variables in the right order (according with security standards: G=Get/P=Post from GPCS ; the C=Cookie or S=Server will not be processed here and must be used from PHP super-globals: $_COOKIE and $_SERVER)
//####################


//-- EXTRACT, FILTER AND REGISTER INPUT VARIABLES (GET, POST, COOKIE, SERVER)
if(!defined('SMART_FRAMEWORK_SEMANTIC_URL_DISABLE')) {
	SmartFrameworkRuntime::Parse_Semantic_URL();
} //end if
//--
SmartFrameworkRuntime::Extract_Filtered_Request_Get_Post_Vars((array)$_GET, 'GET'); 	// extract and filter $_GET
SmartFrameworkRuntime::Extract_Filtered_Request_Get_Post_Vars((array)$_POST, 'POST'); 	// extract and filter $_POST
SmartFrameworkRuntime::Lock_Request_Processing(); 										// prevent re-processing Request variables after they were processed 1st time (this is mandatory from security point of view)
//--
// $_COOKIE will not be processed, use $_COOKIE['cookie_name'] for any purpose
// $_SERVER will not be processed, use $_SERVER['some-key'] for any purpose
//--

// end of php code
?>