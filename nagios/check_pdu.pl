#!/usr/bin/perl
#
# Copyright (c) 2007, Eric F Crist
#
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or 
# without modification, are permitted provided that the following 
# conditions are met:
#
# Redistributions of source code must retain the above copyright 
# notice, this list of conditions and the following disclaimer.
# 
# Redistributions in binary form must reproduce the above copyright 
# notice, this list of conditions and the following disclaimer in 
# the documentation and/or other materials provided with the distribution.
# 
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
# "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
# LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
# A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
# CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
# PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
# PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
# LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
# NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
# SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#
#use warnings;

use SNMP; # requires net-mgmt/p5-Net-SNMP
use lib "/usr/local/libexec/nagios";
use utils qw(%ERRORS);

my $usage = '
Usage: ${0} hostname snmp_community key min max

Connects via SNMP to a UPS or PDU and pulls Input/Output
voltages and frequencies, as well as current output (amps).

hostname is the name of the host youi\'re checking
snmp_community is the SNMP community string for authenticaiton
key, is the specific key you\'re requesting, from:
	inputf......Input Frequency
	inputv......Input Voltage
	outputf.....Output Frequency
	outputv.....Output Voltage
	outputc.....Output Current

This script outputs performance data compatible with Nagios.
$Id: check_pdu.pl 319 2011-02-26 04:41:00Z ecrist $
';

$ENV{'MIBS'} = "ALL";
$host = $ARGV[0]; die $usage unless defined $host;
$community = $ARGV[1]; die $usage unless defined $community;
$key = $ARGV[2]; die $usage unless defined $key;
$min = $ARGV[3]; die $usage unless defined $min;
$max = $ARGV[4]; die $usage unless defined $max;


$session = new SNMP::Session (DestHost => $host, Community => $community, Version => "2c");
$oids = new SNMP::VarList (['UPS-MIB::upsIdentManufacturer'],	#0
			   ['UPS-MIB::upsIdentModel'],		#1
			   ['UPS-MIB::upsInputVoltage'],	#2
			   ['UPS-MIB::upsInputFrequency'],	#3
			   ['UPS-MIB::upsOutputVoltage'],	#4
			   ['UPS-MIB::upsOutputFrequency'],	#5
			   ['UPS-MIB::upsOutputCurrent']);	#6

@status = $session->getnext($oids);
$manuf = $status[0];
$model = $status[1];
$inputv = $status[2];
$inputf = $status[3]/10;
$outputv = $status[4];
$outputf = $status[5]/10;
$outputc = $status[6]/10;

if (($min < $${key}) and ($${key} < $max)){
	print "NORMAL: $manuf($model) $$key | $key=$$key";
	exit $ERRORS{'OK'};
} else {
	print "CRITICAL: $manuf($model) $$key | $key=$$key";
	exit $ERRORS{'CRITICAL'};
}
