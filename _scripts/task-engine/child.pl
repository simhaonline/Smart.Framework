#!/usr/bin/env perl

# [SmartFramework / Task Engine / Child]
# (c) 2006-2016 unix-world.org - all rights reserved
# r.160624

######################################## PERL MODULES

use strict;
use warnings;
use Cwd;
use Time::HiRes;

######################################## PARSE INI SETTINGS ### DO NOT EDIT THIS SCRIPT !!! ### USE child.ini to store all settings ###

# setting: the child task
my $task_script = "";

my %inisett = ();
my $cfname;
my $cfval;
open(CONF, "child.ini") || die "Failed to open config file child.ini";
while(<CONF>) {
	s/\r|\n//g;
	if (/^#/ || !/\S/) {
		next;
	}
	/^([^=]+)=(.*)$/;
	$cfname = $1;
	$cfval = $2;
	$cfname =~ s/^\s+//g;
	$cfname =~ s/\s+$//g;
	$cfval =~ s/^\s+//g;
	$cfval =~ s/\s+$//g;
	$inisett{$cfname} = $cfval;
}
close(CONF);

if($inisett{'ChildTask'} ne "") {
	$task_script = $inisett{'ChildTask'};
}

######################################## RUNTIME

# Terminal Colors
my $color_black = "\033[0m";
my $color_red = "\033[0;31m";
my $color_green = "\033[0;32m";
my $color_blue = "\033[0;34m";
my $color_cyan = "\033[0;36m";

# Child Usage Info
my $num_args = $#ARGV + 1;
if($num_args != 1) {
	system("echo -n \"$color_red\"");
	print "CHILD.ERR: Usage: script.pl TaskID\n";
	system("echo -n \"$color_black\"");
	exit;
}

# Test Task ID
my $the_task_id = "";
my $fArg = $ARGV[0];
$fArg =~ s/^\s+|\s+$//g; #trim
if($fArg =~ /^[_A-Za-z0-9\-]*$/) { # test if in A-Z a-z 0-9 _ -
	$the_task_id = $fArg;
}
if($the_task_id eq "") {
	system("echo -n \"$color_red\"");
	print "CHILD.ERR: TaskID is EMPTY or INVALID: [".$fArg."]\n";
	system("echo -n \"$color_black\"");
	exit;
}

# Test if semaphore exists
my $dir = getcwd;
my $the_child_pid = $dir."/child-semaphores/semaphore@".$the_task_id.".pid";
if(-e $the_child_pid) {
	system("echo -n \"$color_red\"");
	print "CHILD.ERR: Pid Semaphore already exist ... ".$the_task_id."\n";
	system("echo -n \"$color_black\"");
	exit;
}

# create the semaphore
system("echo ".$the_task_id." > ".$the_child_pid);
if(-e $the_child_pid) {
	#OK
} else {
	system("echo -n \"$color_red\"");
	print "CHILD.ERR: Cannot Lock the Semaphore ... ".$the_task_id."\n";
	system("echo -n \"$color_black\"");
	exit;
}

# Run Task script (if empty run a test only)
if($task_script eq "") {
	system("echo -n \"$color_red\"");
	print "CHILD.ERR: No Task Defined in child.ini";
	system("echo -n \"$color_black\"");
} else {
	system("echo -n \"$color_cyan\"");
	print "CHILD.TASK ".$the_task_id." [".$task_script."] :: Smart.Task.Engine // CHILD"."\n"; # RUN REAL TASK
	system("echo -n \"$color_black\"");
	system($task_script." ".$the_task_id);
	Time::HiRes::sleep(0.5);
}

# remove the semaphore if exists
if(-e $the_child_pid) {
	system("rm ".$the_child_pid);
	if(-e $the_child_pid) {
		system("echo -n \"$color_red\"");
		print "CHILD.ERR: Pid Semaphore could not be removed ... ".$the_task_id."\n";
		system("echo -n \"$color_black\"");
	} else {
		system("echo -n \"$color_green\"");
		print "CHILD.DONE ".$the_task_id."\n";
		system("echo -n \"$color_black\"");
	}
} else {
	system("echo -n \"$color_red\"");
	print "CHILD.ERR: Pid Semaphore does not exist for removal ... ".$the_task_id."\n";
	system("echo -n \"$color_black\"");
}

exit;

########################################

#END
