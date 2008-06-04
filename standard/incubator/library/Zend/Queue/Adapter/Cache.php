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
 * @version    $Id: Array.php 2002 2008-03-05 20:02:15Z jplock $
 */

/**
 * @see Zend_Queue_Adapter_Abstract
 */
require_once 'Zend/Queue/Adapter/Abstract.php';

/**
 * Class for using connecting to a Zend_Cache-based queuing system
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_Cache extends Zend_Queue_Adapter_Abstract implements Countable {

    const DEFAULT_QUEUE_KEY = 'zf-queues';
    const DEFAULT_CACHE_DIR = '/tmp/';

    /**
     * @var Zend_Cache
     */
    protected $_cache = null;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $options = &$this->_config['driver_options'];
        if (!array_key_exists('type', $options)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'type' for the cache type to use");
        }

        switch ($options['type']) {
            case 'Memcached':
                if (!array_key_exists('servers', $options)) {
                    /**
                     * @see Zend_Queue_Adapter_Exception
                     */
                    require_once 'Zend/Queue/Adapter/Exception.php';
                    throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'servers' for the servers to use");
                }
                break;
            case 'File':
                if (!array_key_exists('cache_dir', $options)) {
                    $options['cache_dir'] = self::DEFAULT_CACHE_DIR;
                }
                break;
            case 'Zend_Platform':
                break;
        }

        $frontendOptions = array(
            'lifetime'                => 7200, // cache lifetime of 2 hours
            'automatic_serialization' => true,
            'logging'                 => false,
            'write_control'           => true
        );

        /**
         * @see Zend_Cache
         */
        require_once 'Zend/Cache.php';
        $this->_cache = Zend_Cache::factory('Core', $options['type'], $frontendOptions, $options);
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
        if ($timeout === null) {
            $timeout = 30;
        }

        $queue = array(
            'name'    => $name,
            'timeout' => $timeout
        );

        $queues = $this->_cache->load(self::DEFAULT_QUEUE_KEY);
        if ($queues === false) {
            $queues = array($queue);
        }
        else if (is_array($queues)) {
            $queues[] = $queue;
        }
        else {
            $queues = array($queues);
            $queues[] = $queue;
        }

        $ret = $this->_cache->save($queues, self::DEFAULT_QUEUE_KEY, array(), 0);
        if ($ret) {
            $this->setActiveQueue($name);
            return true;
        }
        return false;
    }

    /**
     * Delete a queue and all of it's messages
     *
     * @param  string  $name queue name
     * @return boolean True
     */
    public function delete($name)
    {
        $queues = $this->_cache->load(self::DEFAULT_QUEUE_KEY);
        if (is_array($queues)) {
            foreach ($queues as $key=>&$queue) {
                if ($queue['name'] == $name) {
                    unset($queues[$key]);
                    break;
                }
            }
        }

        // Delete any messages in the queue
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($name));

        return $this->_cache->save($queues, self::DEFAULT_QUEUE_KEY, array(), 0);
    }

    /**
     * Delete a message from the queue
     *
     * @param  string  $handle
     * @return boolean
     */
    public function deleteMessage($handle)
    {
        return $this->_cache->remove($handle);
    }

    /**
     * Get an array of all available queues
     *
     * @return array
     */
    public function getQueues()
    {
        $queues = $this->_cache->load(self::DEFAULT_QUEUE_KEY);
        if ($queues === false) {
            return array();
        }

        $queue_names = array();
        foreach ($queues as $queue) {
            $queue_names[] = $queue['name'];
        }
        return $queue_names;
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

        $msgs = array();

        $info = $this->_message->info();

        $db = $this->_message->getAdapter();
        $query = $db->select();
        $query->from($info['name'], array('*'));
        $query->where('queue_id=?', $this->getActiveQueue());
        $query->where('handle IS NULL OR timeout+'.(int)$timeout.' < ?', microtime(true));
        $query->limit($max_msgs);

        foreach ($db->fetchAll($query) as $data) {
            try {
                $data['handle'] = md5(uniqid(rand(), true));
                $msgs[] = $data;

                $update = array(
                    'handle'  => $data['handle'],
                    'timeout' => microtime(true)
                );

                $where = $db->quoteInto('message_id=?', $data['message_id']);

                $db->update($info['name'], $update, $where);
            }
            catch (Exception $e) {
                /**
                 * @see Zend_Queue_Adapter_Exception
                 */
                require_once 'Zend/Queue/Adapter/Exception.php';
                throw new Zend_Queue_Adapter_Exception($e->getMessage(), $e->getCode());
            }
        }

        $config = array(
            'queue'    => $this,
            'data'     => $msgs,
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

        $data = array(
            'message_id' => md5(uniqid(rand(), true)),
            'handle'     => null,
            'body'       => $message,
            'md5'        => md5($message)
        );

        $this->_cache->save($data, $data['message_id'], array($name));

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
        return -1;
    }
}