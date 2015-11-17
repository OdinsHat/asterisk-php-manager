Asterisk PHP Manager
====================
<img src="https://cloud.githubusercontent.com/assets/1061673/11204113/72f5df0a-8cf3-11e5-8a7f-1ab01d7f9181.png" align="right" />

This was originally hosted at Google Prohect Code hosting but has been moved to GitHub to make it easier or people to do with as they please.

However, it is no longer actively developed by me (the original author).

The [Asterisk](http://www.asterisk.org) Manager PHP API enables a developer to control their Asterisk PBX system from a PHP application.  Allowing for call origination, monitoring, queue management, etc. Other examples include:

* Adding handset to a queue.
* Monitoring a channel.
* Originating a call.
* Getting server status.
* Closing channels.

This was originally developed to help integrate PHP-driven CRM systems in the lending industry into the Asterisk PBX.

Example
-------

```php
require "../../AsteriskManager.php";

$params = array('server' => '127.0.0.1', 'port' => '5038');

/**
 * Instantiate Asterisk object and connect to server
 */
$ast = new Net_AsteriskManager($params);

/**
 * Connect to server
 */
try {
    $ast->connect();
} catch (PEAR_Exception $e) {
    echo $e;
}

/**
 * Login to manager API
 */
try {
    $ast->login('user', 'pass');
} catch(PEAR_Exception $e) {
    echo $e;
}

/**
 * Monitoring
 * Begin monitoring channel to filename "test.gsm"
 * If it fails then echo Asterisk error
 */
$chan = 'SIP/868';

try {
    $ast->startMonitor($chan, 'test', 'gsm', 1);
}  catch (PEAR_Exception $e) {
    echo $e;
}

/**
 * Queues
 * List queues then add and remove a handset from a queue
 */

// Print all the queues on the server
try {
    echo $ast->getQueues();
} catch(PEAR_Exception $e) {
    echo $e;
}

// Add the SIP handset 234 to a the applicants queue
try {
    $ast->queueAdd('applicants', 'SIP/234', 1);
} catch(PEAR_Exception $e) {
    echo $e;
}

// Take it out again
try {
    $ast->queueRemove('applicants', 'SIP/234');
} catch (PEAR_Exception $e) {
    echo $e;
}
```
