<?php 
/**
 * The following is an example source file for the Asterisk Manager library
 * 
 * PHP version 5
 *
 * @category  Net
 * @package   Net_AsteriskManager
 * @author    Doug Bromley <doug.bromley@gmail.com>
 * @copyright 2008 Doug Bromley
 * @license   New BSD License
 * @link      http://www.straw-dogs.co.uk
 *
 ***
 * Copyright (c) 2008, Doug Bromley <doug.bromley@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * - Redistributions of source code must retain the above copyright notice, 
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, 
 *   this list of conditions and the following disclaimer in the documentation 
 *   and/or other materials provided with the distribution.
 * - Neither the name of the <ORGANIZATION> nor the names of its 
 *   contributors may be used to endorse or promote products derived from 
 *   this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR 
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR 
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, 
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
 * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ***
 *
 */

/**
 * Including the Asterisk Manager library
 */
require "../../AsteriskManager.php";


/**
 * The parameters for connecting to the server
 */
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

?>
