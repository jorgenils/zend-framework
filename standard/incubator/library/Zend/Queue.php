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
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Queue.php 2005 2008-03-07 16:15:54Z jplock $
 */

/**
 * Class for connecting to queues performing common operations.
 *
 * @category   Zend
 * @package    Zend_Queue
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Queue {

    /**
     * Use the TIMEOUT constant in the config of a Zend_Queue_Adapter.
     */
    const TIMEOUT = 'timeout';

    /**
     * Factory for Zend_Queue_Adapter_Abstract classes.
     *
     * First argument may be a string containing the base of the adapter class
     * name, e.g. 'Amazon' corresponds to class Zend_Queue_Adapter_Amazon.  This
     * is case-insensitive.
     *
     * First argument may alternatively be an object of type Zend_Config.
     * The adapter class base name is read from the 'adapter' property.
     * The adapter config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the adapter constructor.
     *
     * If the first argument is of type Zend_Config, it is assumed to contain
     * all parameters, and the second argument is ignored.
     *
     * @param  mixed $adapter String name of base adapter class, or Zend_Config object.
     * @param  mixed $config  OPTIONAL; an array or Zend_Config object with adapter parameters.
     * @return Zend_Queue_Adapter_Abstract
     * @throws Zend_Queue_Exception
     */
    public static function factory($adapter, $config = array())
    {
        /*
         * Convert Zend_Config argument to plain string
         * adapter name and separate config object.
         */
        if ($adapter instanceof Zend_Config) {
            if (isset($adapter->params)) {
                $config = $adapter->params->toArray();
            }
            if (isset($adapter->adapter)) {
                $adapter = (string)$adapter->adapter;
            }
            else {
                $adapter = null;
            }
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($config)) {
            /**
             * @see Zend_Queue_Exception
             */
            require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Adapter parameters must be in an array or a Zend_Config object');
        }

        /*
         * Verify that an adapter name has been specified.
         */
        if (!is_string($adapter) || empty($adapter)) {
            /**
             * @see Zend_Queue_Exception
             */
            require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Adapter name must be specified in a string');
        }

        /*
         * Form full adapter class name
         */
        $adapterNamespace = 'Zend_Queue_Adapter';
        if (isset($config['adapterNamespace'])) {
            $adapterNamespace = $config['adapterNamespace'];
            unset($config['adapterNamespace']);
        }
        $adapterName = strtolower($adapterNamespace . '_' . $adapter);
        $adapterName = str_replace(' ', '_', ucwords(str_replace('_', ' ', $adapterName)));

        /**
         * @see Zend_Loader
         */
        require_once 'Zend/Loader.php';
        Zend_Loader::loadClass($adapterName);

        /*
         * Create an instance of the adapter class.
         * Pass the config to the adapter class constructor.
         */
        $queueAdapter = new $adapterName($config);

        /*
         * Verify that the object created is a descendent of the abstract adapter type.
         */
        if (! $queueAdapter instanceof Zend_Queue_Adapter_Abstract) {
            /**
             * @see Zend_Queue_Exception
             */
            require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception("Adapter class '$adapterName' does not extend Zend_Queue_Adapter_Abstract");
        }

        return $queueAdapter;
    }

}