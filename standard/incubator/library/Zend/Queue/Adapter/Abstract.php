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
 * @version    $Id: Abstract.php 2006 2008-03-08 18:24:52Z jplock $
 */

/**
 * @see Zend_Queue
 */
require_once 'Zend/Queue.php';

/**
 * Class for connecting to queues performing common operations.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Queue_Adapter_Abstract
{

    /**
     * User-provided configuration
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Visibility Timeout (seconds)
     *
     * @var integer
     */
    protected $_timeout = 30;

    /**
     * Zend_Queue_Message class
     *
     * @var string
     */
    protected $_msgClass = 'Zend_Queue_Message';

    /**
     * Zend_Queue_Message_Iterator class
     *
     * @var string
     */
    protected $_msgsetClass = 'Zend_Queue_Message_Iterator';

    /**
     * Constructor.
     *
     * $config is an array of key/value pairs or an instance of Zend_Config
     * containing configuration options.  These options are common to most adapters:
     *
     * name           => (string) The name of the queue to use
     * timeout        => (int) Visibility timeout to use (default 30 seconds)
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * access_key     => (string) Amazon AWS Access Key
     * secret_key     => (string) Amazon AWS Secret Key
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     * port           => (string) The port of the database
     * persistent     => (boolean) Whether to use a persistent connection or not, defaults to false
     * protocol       => (string) The network protocol, defaults to TCPIP
     * caseFolding    => (int) style of case-alteration used for identifiers
     *
     * @param  array|Zend_Config $config An array or instance of Zend_Config having configuration data
     * @throws Zend_Queue_Adapter_Exception
     */
    public function __construct($config)
    {
        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            /*
             * Convert Zend_Config argument to a plain array.
             */

            /**
             * @see Zend_Config
             */
            require_once 'Zend/Config.php';
            if ($config instanceof Zend_Config) {
                $config = $config->toArray();
            }
            else {
                /**
                 * @see Zend_Queue_Exception
                 */
                require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception('Adapter parameters must be in an array or a Zend_Config object');
            }
        }

        // we need at least a queue name
        if (!array_key_exists('name', $config)) {
            /**
             * @see Zend_Queue_Adapter_Exception
             */
            require_once 'Zend/Queue/Adapter/Exception.php';
            throw new Zend_Queue_Adapter_Exception("Configuration array must have a key for 'name' that names the queue");
        }

        $options = array(
            Zend_Queue::TIMEOUT => $this->_timeout
        );
        $driverOptions = array();

        // normalize the config and merge it with the defaults
        if (array_key_exists('options', $config)) {
            // can't use array_merge() because keys might be integers
            foreach ((array)$config['options'] as $key => $value) {
                $options[$key] = $value;
            }
        }
        if (array_key_exists('driver_options', $config)) {
            // can't use array_merge() because keys might be integers
            foreach ((array)$config['driver_options'] as $key => $value) {
                $driverOptions[$key] = $value;
            }
        }
        $this->_config  = array_merge($this->_config, $config);
        $this->_config['options'] = $options;
        $this->_config['driver_options'] = $driverOptions;

        // obtain timeout property if there is one
        if (array_key_exists(Zend_Queue::TIMEOUT, $options)) {
            $this->_timeout = (int)$options[Zend_Queue::TIMEOUT];
        }
    }

    /**
     * Returns the configuration variables in this adapter.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Returns the active queue name
     *
     * @return string
     */
    public function getActiveQueue()
    {
        return $this->_config['name'];
    }

    /**
     * Sets the current active queue
     *
     * @param  string $name queue name
     * @return Zend_Queue_Adapter_Abstract Provides Fluent interface
     */
    public function setActiveQueue($name)
    {
        $this->_config['name'] = $name;
        return $this;
    }

    /**
     * Create a new queue
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visibility timeout
     * @return boolean
     */
    abstract public function create($name, $timeout=null);

    /**
     * Delete a queue and all of it's messages
     *
     * @param  string $name queue name
     * @return boolean
     */
    abstract public function delete($name);

    /**
     * Send a message to the queue
     *
     * @param  string $message Message to send to the active queue
     * @return string
     * @throws Zend_Queue_Adapter_Exception
     */
    abstract public function send($message);

    /**
     * Get messages in the queue
     *
     * @param  integer $max_msgs Maximum number of messages to return
     * @param  integer $timeout  Visibility timeout for these messages
     * @return array
     * @throws Zend_Queue_Adapter_Exception
     */
    abstract public function receive($max_msgs=null, $timeout=null);

    /**
     * Delete a message from the queue
     *
     * @param  string $handler
     * @return boolean
     */
    abstract public function deleteMessage($handler);

    /**
     * Get an array of all available queues
     *
     * @return array
     * @throws Zend_Queue_Adapter_Exception
     */
    abstract public function getQueues();

    /**
     * Return the approximate number of messages in the queue
     *
     * @return integer
     * @throws Zend_Queue_Adapter_Exception
     */
    public function count()
    {
    }
}