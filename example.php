<?php 

include("asterisk.php");


/**
 * Instantiate Asterisk object and connect to server
 */
$ast = new AsteriskInterface('217.10.145.18', 'admin', 'pass');


/**
 * Monitoring
 * Begin monitoring channel to filename "test.gsm"
 * If it fails then echo Asterisk error
 */
$chan = 'SIP/868';
if (!$ast->monitor($chan, 'test', 'gsm', 1)) {
    echo $ast->error;
} else {
    // Recording for 5 seconds
    sleep(5);
    $ast->stopMonitor($chan);
}

/**
 * Queues
 * List queues then add and remove a handset from a queue
 */

// Print all the queues on the server
echo $ast->queues();

// Add the SIP handset 234 to a the applicants queue
$ast->queueAdd('applicants', 'SIP/234', 1);

// Take it out again
$ast->queueRemove('applicants', 'SIP/234');




?>
