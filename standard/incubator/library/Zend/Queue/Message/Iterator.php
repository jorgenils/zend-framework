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
 * @subpackage Message
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Queue.php 2000 2008-03-04 05:13:30Z jplock $
 */

/**
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Message
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Queue_Message_Iterator implements Iterator, Countable {

    /**
     * The data for the queue message
     *
     * @var array
     */
    protected $_data = array();

     /**
     * Connected is true if we have a reference to a live
     * Zend_Queue_Adapter_Abstract object.
     * This is false after the Message has been deserialized.
     *
     * @var boolean
     */
    protected $_connected = true;

    /**
     * Zend_Queue_Adapter_Abstract parent class or instance
     *
     * @var Zend_Queue_Adapter_Abstract
     */
    protected $_queue = null;

    /**
     * Name of the class of the Zend_Queue_Adapter_Abstract object.
     *
     * @var string
     */
    protected $_queueClass = null;

    /**
     * Zend_Queue_Message class name
     *
     * @var string
     */
    protected $_msgClass = 'Zend_Queue_Message';

     /**
     * Iterator pointer.
     *
     * @var integer
     */
    protected $_pointer = 0;

    /**
     * How many data rows there are.
     *
     * @var integer
     */
    protected $_count;

    /**
     * Collection of instantiated Zend_Queue_Message objects.
     *
     * @var array
     */
    protected $_messages = array();

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config=array())
    {
        if (isset($config['queue'])) {
            $this->_queue = $config['queue'];
            $this->_queueClass = get_class($this->_queue);
        }
        if (isset($config['msgClass'])) {
            $this->_msgClass = $config['msgClass'];
        }
        Zend_Loader::loadClass($this->_msgClass);
        if (isset($config['data'])) {
            $this->_data = $config['data'];
        }

        // set the count of messages
        $this->_count = count($this->_data);
    }

    /**
     * Store queue and data in serialized object
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_data', '_queueClass', '_msgClass', '_pointer', '_count', '_messages');
    }

    /**
     * Setup to do on wakeup.
     * A de-serialized Message should not be assumed to have access to a live
     * queue connection, so set _connected = false.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_connected = false;
    }

     /**
     * Returns the queue object, or null if this is disconnected message set
     *
     * @return Zend_Queue_Adapter_Abstract|null
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * Set the queue object, to re-establish a live connection
     * to the queue for a Message that has been de-serialized.
     *
     * @param  Zend_Queue_Adapter_Abstract $queue
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function setQueue(Zend_Queue_Adapter_Abstract $queue)
    {
        $this->_queue = $queue;
        $this->_connected = false;
        // @todo This works only if we have iterated through
        // the result set once to instantiate the rows.
        foreach ($this->_messages as $message) {
            $connected = $message->setQueue($queue);
            if ($connected == true) {
                $this->_connected = true;
            }
        }
        return $this->_connected;
    }

    /**
     * Query the class name of the Queue object for which this
     * Message was created.
     *
     * @return string
     */
    public function getQueueClass()
    {
        return $this->_queueClass;
    }

    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    public function rewind()
    {
        $this->_pointer = 0;
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return Zend_Queue_Message current element from the collection
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        // do we already have a message object for this position?
        if (empty($this->_messages[$this->_pointer])) {
            $this->_messages[$this->_pointer] = new $this->_msgClass(
                array(
                    'queue' => $this->_queue,
                    'data'  => $this->_data[$this->_pointer]
                )
            );
        }

        // return the messages object
        return $this->_messages[$this->_pointer];
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return int
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    public function next()
    {
        ++$this->_pointer;
    }

    /**
     * Check if there is a current element after calls to rewind() or next().
     * Used to check if we've iterated to the end of the collection.
     * Required by interface Iterator.
     *
     * @return bool False if there's nothing more to iterate over
     */
    public function valid()
    {
        return $this->_pointer < $this->_count;
    }

    /**
     * Returns the number of elements in the collection.
     *
     * Implements Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * Returns all data as an array.
     *
     * Updates the $_data property with current message object values.
     *
     * @return array
     */
    public function toArray()
    {
        // @todo This works only if we have iterated through
        // the result set once to instantiate the messages.
        foreach ($this->_messages as $i=>$messages) {
            $this->_data[$i] = $message->toArray();
        }
        return $this->_data;
    }
}