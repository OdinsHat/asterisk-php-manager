<?php

/**
 * This package is capable of interfacing with the open source Asterisk PBX via 
 * its built in Manager API.  This will allow you to execute manager commands
 * for administration and maintenance of the server.
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
 * Class for accessing the Asterisk Manager interface 
 * {@link http://www.voip-info.org/wiki/view/Asterisk+manager+API}
 * 
 * @category Net
 * @package  Net_AsteriskManager
 * @author   Doug Bromley <doug.bromley@gmail.com>
 * @license  New BSD License
 * @link     http://pear.php.net/pepr/pepr-proposal-show.php?id=543
 */
class Net_AsteriskManager
{
    /**
     * The Asterisk server which will recieve the manager commands 
     * @access public
     * @var string
     */
    public $server;

    /**
     * The port to use when connecting to the Asterisk server
     * @access public
     * @var integer
     */
    public $port = 5038;

    /**
     * The opened socket to the Asterisk server
     * @access private 
     * @var object
     */
    private $_socket;

    /**
     * Class constructor
     * 
     * @param string  $server   The server hostname or IP address
     * @param string  $username Username credential for the manager interface
     * @param string  $password The password for the manager interface
     * @param integer $port     The port the interface is listening on
     * 
     * @uses AsteriskManager::$server
     * @uses AsteriskManager::$port
     * @uses AsteriskManager::$username
     * @uses AsteriskManager::$password
     * @uses AsteriskManager::$_socket
     */
    function __construct($server, $port = 5038)
    {
        $this->server   = $server;
        $this->port     = $port;

        if ($this->_socket) {
            $this->close();
        }

        if ($this->_socket = fsockopen($this->server, $this->port)) {
            stream_set_timeout($this->_socket, 3);
        } else {
            $this->error = 'Could not establish connection';
            return false;
        }
    }

    /**
     * Login into Asterisk Manager interface given the user credentials
     *
     * @param string $username The username to access the interface
     * @param string $password The password defined in manager interface of server
     * 
     * @return bool
     */
    function login($username, $password)
    {
        fputs($this->_socket, "Action: login\r\nUsername: {$username}\r\n
                               Secret: {$password}\r\n\r\n");
        $response = stream_get_contents($this->_socket);
        if (strpos($response, "Message: Authentication accepted") != false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Logout of the current manager session attached to $this::socket
     * 
     * @return bool
     */
    function logout()
    {
        if (!$this->_socket) { return false }
        
        fputs($this->_socket, "Action: Logoff\r\n\r\n");
        fclose($this->_socket);
        return true;
    }

    /**
     * Just kill the connection without logging off
     *
     * @return bool
     */
    function close()
    {
        if (!$this->_socket) { return false }

        return fclose($this->_socket);
    }

    /**
     * Send a command to the Asterisk CLI interface
     *
     * @param string $command Command to execute on server
     *
     * @return string|bool
     */
    function command($command)
    {
        if ($this->_socket) {
            fputs($this->_socket, "Action: Command\r\nCommand: $command\r\n\r\n");
            
            return fgets($this->_socket);
        }
        return false;
    }

    /**
     * A simple 'ping' command which the server responds with 'pong'
     *
     * @return bool
     */
    function ping()
    {
        if ($this->_socket) {
            fputs("Action: Ping\r\n\r\n");
            $response = stream_get_contents($this->_socket);
            if (strpos($reponse, "Pong") ===false) {
                $this->error = 'No pong received from server!';
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Make a call to an extension with a given channel acting as the originator
     *
     * @param string  $extension The number to dial
     * @param string  $channel   The channel where you wish to originate the call
     * @param string  $context   The context that the call will be dropped into 
     * @param string  $extension The extension to use on connection
     * @param string  $cid       The caller ID to use
     * @param integer $priority  The priority of this command
     * @param integer $timeout   Timeout in milliseconds before attempt dropped
     * @param array   $variables An array of variables to pass to Asterisk
     * @param string  $action_id A unique identifier for this command
     *
     * @return bool
     */
    function originateCall($extension, 
                           $channel, 
                           $context, 
                           $extension, 
                           $cid, 
                           $priority = 1, 
                           $timeout = 30000, 
                           $variables = null, 
                           $action_id = null)
    {
        if ($this->_socket) {
            $command = $this->_socket, "Action: Originate\r\nChannel: $channel\r\n
                Context: $context\r\nExten: $extension\r\nPriority: $priority\r\n
                Callerid: $cid\r\nTimeout: $timeout\r\n"

            if (count($variables > 0)) {
                $variables = implode('|', $variables);
                $command  .= "Variable: $variables\r\n";
            }

            if ($action_id) {
                $command .= "ActionID: $action_id\r\n";
            }
            fputs($this->_socket, $command."\r\n");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a list of queues and their status
     *
     * @return string|bool
     */
    function queues()
    {
        if ($this->_socket) {
            fputs($this->_socket, "Action: Queues\r\n\r\n");
            $response = stream_get_contents($this->_socket);
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Add a handset to a queue on the server
     * 
     * @param string  $queue   The name of the queue you wish to add the handset too
     * @param string  $handset The handset to add, e.g. SIP/234
     * @param integer $penalty Penalty
     * 
     * @return bool
     */
    function queueAdd($queue, $handset, $penalty)
    {
        if ($this->_socket) {
            $command = "Action: QueueAdd\r\nQueue: $queue\r\n
                        Interface: $handset\r\n";

            if ($penalty) {
                fputs($this->_socket, $command."Penalty: $penalty\r\n\r\n");
            } else {
                fputs($this->_socket, $command."\r\n");
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove a handset from the given queue
     * 
     * @param string $queue   The queue you wish to perform this action on
     * @param string $handset The handset you wish to remove (e.g. SIP/200)
     *
     * @return bool
     */
    function queueRemove($queue, $handset) 
    {
        if ($this->_socket) {
            fputs($this->_socket, "Action: QueueRemove\r\nQueue: $queue\r\n
                                   Interface: $handset\r\n\r\n");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Monitor(record) a channel to given file in given format
     *
     * @param string  $channel  Channel to monitor (e.g. SIP/234, ZAP/1)
     * @param string  $filename The filename to save to
     * @param string  $format   The format of the file (e.g. gsm, wav)
     * @param integer $mix      Boolean 1 or 0 on whether to mix
     *
     * @return bool
     */
    function monitor($channel, $filename, $format, $mix = null)
    {
        if ($this->_socket) {
            fputs($this->_socket, "Action: Monitor\r\nChannel: $channel\r\n
                                   File: $filename\r\nFormat: $format\r\n
                                   Mix: $mix\r\n\r\n");
            
            $response = stream_get_contents($this->_socket);

            if (strpos($response, "Success") === false) {
                $this->error = 'Failed to monitor channel';
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Stop monitoring a channel
     * 
     * @param string $channel The channel you wish to stop monitoring
     *
     * @return bool
     */
    function stopMonitor($channel)
    {
        if ($this->socket) {
            fputs($this->_socket, "Action: StopMonitor\r\n
                                   Channel: $channel\r\n\r\n");
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the status information for a channel
     *
     * @param string $channel The channel to query
     * 
     * @return string|string
     */
    function status($channel = null)
    {
        if ($this->_socket) {
            fputs($this->_socket, "Action: Status\r\nChannel: $channel\r\n\r\n");
            $response = stream_get_contents($this->_socket);
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Get a list of SIP peers and their status
     *
     * @return string|bool
     */
    function sipPeers()
    {
        if ($this->_socket) {
            fputs($this->_socket, "Action: Sippeers\r\n\r\n");
            $response = stream_get_contents($this->_socket);
            return $reponse;
        } else {
            return false;
        }
    }

    /**
     * Return a list of IAX peers and their status
     *
     * @return string|bool
     */
    function iaxPeers() 
    {
        if ($this->_socket) {
            fputs($this->_socket, "Action: IAXPeers\r\n\r\n");
            $response = stream_get_contents($this->_socket);
            return $reponse;
        } else {
            return false;
        }
    }
}

?>
