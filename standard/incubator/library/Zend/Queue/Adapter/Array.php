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
 * @version    $Id$
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
class Zend_Queue_Adapter_Array extends Zend_Queue_Adapter_Abstract implements Countable
{
    /**
     * @var array
     */
    private $_data = array();

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $data = &$this->_config['driver_options']['data'];
        if (isset($data) && is_array($data)) {
            $this->_data = $data;
        }
    }

    /**
     * Create a new queue
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visiblity timeout
     * @return boolean True
     */
    public function create($name, $timeout=null)
    {
        $this->_data[$name] = array();
        $this->setActiveQueue($name);
        return true;
    }

    /**
     * Delete a queue and all of it's messages
     *
     * @param  string  $name queue name
     * @return boolean True
     */
    public function delete($name)
    {
        unset($this->_data[$name]);
        return true;
    }

    /**
     * Delete a message from the queue
     *
     * @param  string  $handle
     * @return boolean True
     */
    public function deleteMessage($handle)
    {
        $queue = &$this->_data[$this->getActiveQueue()];

        foreach ($queue as $key=>&$msg) {
            if ($msg['handle'] == $handle) {
                unset($queue[$key]);
                return true;
                break;
            }
        }
        return false;
    }

    /**
     * Get an array of all available queues
     *
     * @return array
     * @throws Zend_Queue_Adapter_Abstract
     */
    public function getQueues()
    {
        return array_keys($this->_data);
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

        $queue = &$this->_data[$this->getActiveQueue()];
        $data = array();
        $start_time = microtime(true);

        $count = 0;
        foreach ($queue as $key=>&$msg) {

            if ($msg['handle'] === null || $msg['timeout']+$timeout < $start_time) {
                $msg['handle'] = md5(uniqid(rand(), true));
                $data[] = $msg;
                $msg['timeout'] = microtime(true);
                $count++;
            }

            if ($count >= $max_msgs) {
                break;
            }
        }

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

        if (is_array($message)) {
            foreach ($message as $msg) {
                $this->send($msg);
            }
        }
        else {
            $data = array(
                'message_id' => md5(uniqid(rand(), true)),
                'body'       => $message,
                'md5'        => md5($message),
                'handle'     => null,
                'created'    => time()
            );
            $this->_data[$name][] = $data;
        }

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
        return count($this->_data[$this->getActiveQueue()]);
    }
}