#!/usr/bin/env perl

##### Test Batch Task #####

use strict;
use warnings;
use Cwd;
use Time::HiRes;

use Term::ANSIColor;

my $clr_error = ['bold bright_white on_red'];
my $clr_warn = ['bold bright_white on_bright_red'];
my $clr_notice = ['bold black on_bright_cyan'];
my $clr_ok = ['bold bright_white on_green'];

my $num_args = $#ARGV + 1;
if($num_args != 1) {
	print colored($clr_warn, "TASK.ERR : TEST.Task STOP: The ID parameter is missing (script must have 1 parameter) ...");
	print "\n";
	exit;
}
my $id = $ARGV[0];

##### Task execution #####

sleep 5; # this sample task execution is to wait 5 seconds (it can be changed as below ... see commented code ...)
my $task_output = 200; # task result

#my $user = 'task';
#my $pass = '12345';
#my $url = "http://your-url/smart-framework/admin.php?/page/tasks.test/id/".$id;
#my $task_output = `curl -s -o /dev/null -w "%{http_code}" --get --connect-timeout 30 --max-time 600 --basic -u ${user}:${pass} --url "${url}"`;

if($task_output != 200) {
	if($task_output != 202) {
		print colored($clr_warn, "TASK.ERR # ${id} : TEST.Task COMPLETED: ${task_output}");
		print "\n";
	} else {
		print colored($clr_notice, "TASK.WARN # ${id} : TEST.Task COMPLETED: ${task_output}");
		print "\n";
	}
} else {
	print colored($clr_ok, "TASK.INF # ${id} : TEST.Task COMPLETED SUCCESSFUL: ${task_output}");
	print "\n";
}

##### End Task execution #####

exit 0

#END
