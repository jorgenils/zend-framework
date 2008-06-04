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
 * Class for using connecting to a Zend_Db-based queuing system
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Adapter_Db extends Zend_Queue_Adapter_Abstract implements Countable {

    /**
     * @var Zend_Queue_Adapter_Db_Queue
     */
    protected $_queue = null;

    /**
     * @var Zend_Queue_Adapter_Db_Message
     */
    protected $_message = null;

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
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'type' for the database type to use");
        }
        if (!array_key_exists('host', $options)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'host' for the host to use");
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
        if (!array_key_exists('dbname', $options)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'dbname' for the database to use");
        }

        /**
         * @see Zend_Db
         */
        require_once 'Zend/Db.php';
        $db = Zend_Db::factory($options['type'], $options);

        /**
         * @see Zend_Db_Table_Abstract
         */
        require_once 'Zend/Db/Table/Abstract.php';
        Zend_Db_Table_Abstract::setDefaultAdapter($db);

        /**
         * @see Zend_Queue_Adapter_Db_Queue
         */
        require_once 'Zend/Queue/Adapter/Db/Queue.php';
        $this->_queue = new Zend_Queue_Adapter_Db_Queue();

        /**
         * @see Zend_Queue_Adapter_Db_Message
         */
        require_once 'Zend/Queue/Adapter/Db/Message.php';
        $this->_message = new Zend_Queue_Adapter_Db_Message();
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
        $queue = $this->_queue->createRow();
        $queue->queue_name = $name;
        $queue->timeout = ($timeout === null) ? 30 : (int)$timeout;

        try {
            if ($id = $queue->save()) {
                $this->setActiveQueue($id);
                return true;
            }
        }
        catch (Exception $e) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($e->getMessage(), $e->getCode());
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
        $queue = $this->_queue->find($name)->current();
        if ($queue instanceof Zend_Db_Table_Row_Abstract) {
            try {
                $queue->delete();
            }
            catch (Exception $e) {
                /**
                 * @see Zend_Queue_Adapter_Exception
                 */
                require_once 'Zend/Queue/Adapter/Exception.php';
                throw new Zend_Queue_Adapter_Exception($e->getMessage(), $e->getCode());
            }
        }
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
        $info = $this->_message->info();
        $db = $this->_message->getAdapter();
        $where = $db->quoteInto('handle=?', $handle);
        if ($db->delete($info['name'], $where)) {
            return true;
        }
        return false;
    }

    /**
     * Get an array of all available queues
     *
     * @return array
     */
    public function getQueues()
    {
        $queues = array();
        foreach ($this->_queue->fetchAll() as $queue) {
            $queues[] = $queue->queue_name;
        }
        return $queues;
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

        $msg = $this->_message->createRow();
        $msg->queue_id = $this->getActiveQueue();
        $msg->body = $message;
        $msg->md5 = md5($message);

        try {
            $msg->save();
        }
        catch (Exception $e) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception($e->getMessage(), $e->getCode());
        }

        $config = array(
            'queue' => $this,
            'data'  => $msg->toArray()
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
        $info = $this->_message->info();
        $db = $this->_message->getAdapter();
        $query = $db->select();
        $query->from($info['name'], array(new Zend_Db_Expr('COUNT(*)')));
        $query->where('queue_id=?', $this->getActiveQueue());

        return $db->fetchOne($query);
    }
}