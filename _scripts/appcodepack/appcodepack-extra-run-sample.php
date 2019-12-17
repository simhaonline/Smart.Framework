<?php
// AppCodePack - a PHP, JS and CSS Optimizer / NetArchive Pack Upgrade Script
// (c) 2006-2019 unix-world.org - all rights reserved
// r.5.2.7 / smart.framework.v.5.2

//----------------------------------------------------- PREVENT EXECUTION BEFORE RUNTIME READY
if(!defined('APPCODEPACK_PROCESS_EXTRA_RUN')) { // this must be defined in the first line of the application
	throw new Exception('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------

//#####
// Sample AppCodePack Extra Script, v.20191217.1427
// CUSTOMIZE IT AS NEEDED and rename it to: appcodepack-extra-run.php ; It also need a corresponding: appcodepack-extra-run.inc.htm
//#####

//--
switch((string)APPCODEPACK_PROCESS_EXTRA_RUN) {
	case 'extra-test-ok':
		// emulate ok
		echo 'This is a test with OK result ...'; // the output is optional
		break;
	case 'extra-test-ok':
		echo 'This is an ERROR test ...'; // the output is optional
		throw new Exception('Failed ...'); // emulate error (throw is required for the error case)
		break;
		break;
	default:
		throw new Exception('Invalid Extra Task: '.APPCODEPACK_PROCESS_EXTRA_RUN);
} //end switch
//--


//end of php code
?>