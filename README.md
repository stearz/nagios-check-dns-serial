# check-dns-serial.php

## Description
This PHP script has run quite some time as a nagios check plugin. It allows you to check if all nameservers that host a 
DNS zone are up-to-date and deliver the same serial in the SOA-record of the zone.
It was written 2014 by Stephan Schwarz (@stearz)

## Usage

    check-dns-serial.php <ip-of-primary-dns> <domain.tld>

## Exit codes
The script uses exit codes and an output that Nagios can understand

    0 - OK
    1 - Warning
    2 - Critical
    3 - Unknown

## Variables
The following threshold variables can be set inside the script to control when Nagios should raise a WARNING or a CRITICAL:
 
    # WARNING threshold if serials differ
    $warn_threshold_delta  = 5;
    
    # CRITICAL threshold if serials differ
    $crit_threshold_delta  = 10;

## Requirements
The script requires `Net/DNS2.php` (PHP Resolver library used to communicate with a DNS server).
Install it with `sudo apt-get install php-net-dns2` on Debian based systems. 
