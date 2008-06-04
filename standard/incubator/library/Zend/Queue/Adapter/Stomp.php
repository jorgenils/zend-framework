<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Array.php 2005 2008-03-07 16:15:54Z jplock $
 */

/**
 * @see Zend_Queue_Adapter_Abstract
 */
require_once 'Zend/Queue/Adapter/Abstract.php';

/**
 * Class for using a standard PHP array as a queue
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_Stomp extends Zend_Queue_Adapter_Abstract implements Countable {

    const DEFAULT_SCHEME = 'tcp';
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 61613;

    /**
     * @var resource
     */
    private $_socket = array();

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $options = &$this->_config['driver_options'];
        if (!array_key_exists('scheme', $options)) {
            $options['scheme'] = self::DEFAULT_SCHEME;
        }
        if (!array_key_exists('host', $options)) {
            $options['host'] = self::DEFAULT_HOST;
        }
        if (!array_key_exists('port', $options)) {
            $options['port'] = self::DEFAULT_PORT;
        }
        if (!array_key_exists('username', $options)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'username' for the username to use");
        }
        if (!array_key_exists('password', $options)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'password' for the password to use");
        }

        $this->_socket = fsockopen($options['scheme'].'://'.$options['host'], $options['port'], $errno, $errstr);
        if (!$this->_socket) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Unable to connect to '".$options['scheme'].'://'.$options['host'].':'.$options['port']."'");
        }

        $headers = array(
            'login'    => $options['username'],
            'passcode' => $options['password']
        );
        $response = $this->_makeRequest('CONNECT', $headers);
        if ($response['command'] != 'CONNECTED') {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Unable to authenticate to '".$options['scheme'].'://'.$options['host'].':'.$options['port']."'");
        }
    }

    /**
     * Close the socket explicitly when destructed
     *
     * @return void
     */
    public function __destruct()
    {
        // Gracefully disconnect
        $this->_makeRequest('DISCONNECT');

        if (is_resource($this->_socket)) {
            fclose($this->_socket);
        }
    }

    /**
     * Create a new queue
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visiblity timeout
     * @return boolean False
     */
    public function create($name, $timeout=null)
    {
        // create() is not supported in this adapter
        return false;
    }

    /**
     * Delete a queue and all of it's messages
     *
     * @param  string  $name queue name
     * @return boolean False
     */
    public function delete($name)
    {
        // delete() is not supported in this adapter
        return false;
    }

    /**
     * Delete a message from the queue
     *
     * @param  string  $handle
     * @return boolean
     */
    public function deleteMessage($handle)
    {
        $headers = array(
            'message-id' => $handle
        );
        $response = $this->_makeRequest('ACK', $headers);
        return ($response['command'] != 'ERROR');
    }

    /**
     * Get an array of all available queues
     *
     * @return array
     * @throws Zend_Queue_Adapter_Abstract
     */
    public function getQueues()
    {
        // getQueues() is not supported in this adapter
        return array();
    }

    /**
     * Return the first element in the queue
     *
     * @param  integer $max_msgs
     * @param  integer $timeout
     * @return Zend_Queue_Message_Iterator
     */
    public function receive($max_msgs=null, $timeout=null)
    {
        // Default to returning only one message
        if ($max_msgs === null) {
            $max_msgs = 1;
        }
        if ($timeout === null) {
            $timeout = $this->_timeout;
        }

        $headers = array(
            'destination' => $this->getActiveQueue(),
            'ack'         => 'client'
        );
        $response = $this->_makeRequest('SUBSCRIBE', $headers);

        $data = array(
            array(
                'message_id' => $response['headers']['message-id'],
                'handle'     => $response['headers']['message-id'],
                'body'       => $response['body'],
                'md5'        => md5($response['body'])
            )
        );

        $config = array(
            'queue'    => $this,
            'data'     => $data,
            'msgClass' => $this->_msgClass
        );
        Zend_Loader::loadClass($this->_msgsetClass);
        return new $this->_msgsetClass($config);
    }

    /**
     * Push an element onto the end of the queue
     *
     * @param  mixed  $message message to send to the queue
     * @param  string $name    queue name
     * @return Zend_Queue_Message
     */
    public function send($message, $name=null)
    {
        if ($name !== null) {
            $this->setActiveQueue($name);
        }
        else {
            $name = $this->getActiveQueue();
        }

        $headers = array(
            'destination'    => $name,
            'content-length' => strlen($message)
        );
        $response = $this->_makeRequest('SEND', $headers, $message);

        $data = array(
            'message_id' => null,
            'body'       => $message,
            'md5'        => md5($message),
            'handle'     => null
        );

        $config = array(
            'queue' => $this,
            'data'  => $data
        );
        Zend_Loader::loadClass($this->_msgClass);
        return new $this->_msgClass($config);
    }

    /**
     * Returns the length of the queue
     *
     * @return integer
     */
    public function count()
    {
        // count() is not supported in this adapter
        return -1;
    }

    /**
     * Submit a request to the Stomp server
     *
     * @param  string $action
     * @param  array  $headers
     * @param  string $body
     * @return array|boolean Response Message
     * @throws Zend_Queue_Adapter_Exception
     */
    private function _makeRequest($action, $headers=array(), $body=null)
    {
        if (!is_resource($this->_socket)) {
            return false;
        }

        $request = $action."\n";
        if (is_array($headers) && !empty($headers)) {
            foreach ($headers as $key=>$value) {
                $request .= $key.': '.$value."\n";
            }
        }
        $request .= "\n";

        if ($body !== null) {
            $request .= $body."\n";
        }
        $request .= "\x00\n";

        $noop = "\x00\n";
        fwrite($this->_socket, $noop, strlen($noop));

        $bytes = fwrite($this->_socket, $request, strlen($request));
        if ($bytes === false || $bytes == 0) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception('No bytes written');
        }

        $response = '';
        $prev = '';
        while (!feof($this->_socket)) {
            $data = fread($this->_socket, 1);
            if ($data === false) {
                echo 'Reconnect';
            }
            else {
                $response .= $data;
                if (ord($data) == 10 && ord($prev) == 0) {
                    break;
                }
                $prev = $data;
            }
        }

        list($header, $body) = explode("\n\n", $response, 2);
        $header = explode("\n", $header);
        $headers = array();

        $command = null;
        foreach ($header as $key) {
            if (isset($command)) {
                list($name, $value) = explode(':', $key, 2);
                $headers[$name] = $value;
            }
            else {
                $command = $key;
            }
        }

        if ($command == 'ERROR') {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Abstract('An error has occured: '.$headers['message']);
        }

        $response = array(
            'command' => $command,
            'headers' => $headers,
            'body'    => $body
        );

        return $response;
    }
}