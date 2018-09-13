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
# $Id: check_gmirror.pl 319 2011-02-26 04:41:00Z ecrist $
# $HeadURL: https://www.secure-computing.net/svn/trunk/nagios/check_gmirror.pl $
use strict;
use warnings;

use lib "/usr/local/libexec/nagios";
use utils qw(%ERRORS);

my $result = `/sbin/gmirror status`;
$result =~ m/(DEGRADED|COMPLETE)/g;
my $status = $1;
my $rebuild = ($result =~ m/(\w\w\d \(\d\d?\%\))/);
$result =~ m/(\w\w\d \(\d\d?\%\))/;
my $progress = $1;


if ($status =~ /COMPLETE/){
	print "Raid Optimal\n";
	exit $ERRORS{'OK'};
}
elsif (($status =~ /DEGRADED/) and ($rebuild == 1)) {
	print "Raid Reconstructing: $progress\n";
	exit $ERRORS{'WARNING'};
}
else {
	print "Raid Critical!\n";
	exit $ERRORS{'CRITICAL'};
}
