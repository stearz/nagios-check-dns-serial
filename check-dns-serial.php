#!/usr/bin/env php
<?php

require('Net/DNS2.php');
 
$status_text = array( 0 => 'OK',
                      1 => 'Warning',
                      2 => 'Critical',
                      3 => 'Unknown');

$actual_status = 3;
$actual_errors = '';

if ($argc < 3)
{
  $actual_errors="Usage: ".$argv[0]." <ip-of-primary-dns> <domain.tld>";
  echo $status_text[$actual_status] . " - " . $actual_errors . "\n";
  exit($actual_status);
}

$primary = $argv[1];
$domain  = $argv[2];

$detecteddelta         = 0;
$warn_threshold_delta  = 5;
$crit_threshold_delta  = 10;

function fetch_records($lns,$ldomain,$ltype)
{
  if (!filter_var($lns, FILTER_VALIDATE_IP))
    $lns=gethostbyname($lns);

  $r = new Net_DNS2_Resolver(array('nameservers' => array($lns)));
  try
  {
    $lresult = $r->query($ldomain, $ltype, 'IN');
  }
  catch(Net_DNS2_Exception $e)
  {
    echo $status_text[$actual_status] . " - " . "::query() failed: ", $e->getMessage(), "\n";
    exit($actual_status);
  }
  return $lresult;
}

// get serial from SOA record of primary
$result=fetch_records($primary,$domain,'SOA');
foreach($result->answer as $rr)
{
  $serial=$rr->serial;
}

// get NS record from primary
$result=fetch_records($primary,$domain,'NS');
$list_of_ns=array();
foreach($result->answer as $rr)
{
  array_push($list_of_ns,$rr->nsdname);
}

// get serial from each nameserver that is referenced by an NS record
// and see if serial in SOA matches with the one of the primary
foreach($list_of_ns as $nstobechecked)
{
  $result=fetch_records($nstobechecked,$domain,'SOA');
  foreach($result->answer as $rr)
  {
    if ($serial != $rr->serial)
    {
       $detecteddelta   += abs($serial-($rr->serial));
       $actual_errors .= "$nstobechecked has ".$rr->serial." (should be $serial)\n";
    }
  }
}


// set return code and send output in a nagios friendly way
if ($detecteddelta >= $crit_threshold_delta)
  $actual_status = 2;
elseif ($detecteddelta >= $warn_threshold_delta)
  $actual_status = 1;
else
  $actual_status = 0;

echo "dns-serial-check: " . $status_text[$actual_status] . " - " . $actual_errors;
exit($actual_status);

?>