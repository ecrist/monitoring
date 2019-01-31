# Overview

This is a small collection of Nagios and NagVis scripts and plugins I've 
written over the years.  This is non-exhaustive as some employers have not 
permitted me to share work done on the clock.

# Printer Utils

There are a couple components available for printer checks:
 * nagios/check_printer
   
   This is a Nagios check that provides perfdata.  This has been downloaded 
   over 300,000 times with feedback provided by many users of large and small 
   institutions over the years.  This should do a good job of detecting your
   printer and gathering data on all of its resources.

 * nagvis/gadgets/print_supply.php

   This provides a graph representation of the output from the most recent
   check_printer check through the NagVis framework/plugin.
