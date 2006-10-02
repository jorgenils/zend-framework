<?php
/**
 * Zend_Server_Reflection_Method
 */
require_once 'Zend/Server/Reflection/Method.php';

/**
 * Zend_Server_Reflection_Exception
 */
require_once 'Zend/Server/Reflection/Exception.php';

/**
 * Class/Object reflection
 *
 * Proxies calls to a ReflectionClass object, and decorates getMethods() by 
 * creating its own list of {@link Zend_Server_Reflection_Method}s.
 * 
 * @package Zend_Server
 * @subpackage Reflection
 * @version $Id$
 */
class Zend_Server_Reflection_Class
{
    /**
     * Optional configuration parameters; accessible via {@link __get} and 
     * {@link __set()}
     * @var array 
     */
    protected $_config = array();

    /**
     * Array of {@link Zend_Server_Reflection_Method}s
     * @var array 
     */
    protected $_methods = array();

    /**
     * ReflectionClass object
     * @var ReflectionClass
     */
    protected $_reflection;

    /**
     * Constructor
     *
     * Create array of dispatchable methods, each a 
     * {@link Zend_Server_Reflection_Method}. Sets reflection object property.
     * 
     * @param ReflectionClass $reflection 
     * @param string $namespace 
     * @param mixed $argv 
     * @return void
     */
    public function __construct(ReflectionClass $reflection, $namespace = '', $argv = false)
    {
        $this->_reflection = $reflection;

        foreach ($reflection->getMethods() as $method) {
            // Don't aggregate magic methods
            if ('__' == substr($method->getName(), 0, 2)) {
                continue;
            }

            if ($method->isPublic()) {
                // Get signatures and description
                $this->_methods[] = new Zend_Server_Reflection_Method($method, $namespace, $argv);
            }
        }
    }

    /**
     * Return array of dispatchable {@link Zend_Server_Reflection_Method}s.
     * 
     * @access public
     * @return array
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * Proxy reflection calls
     * 
     * @param string $method 
     * @param array $args 
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (method_exists($this->_reflection, $method)) {
            return call_user_func_array(array($this->_reflection, $method), $args);
        }

        throw new Zend_Server_Reflection_Exception('Invalid reflection method');
    }

    /**
     * Retrieve configuration parameters
     *
     * Values are retrieved by key from {@link $_config}. Returns null if no 
     * value found.
     * 
     * @param string $key 
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->_config[$key])) {
            return $this->_config[$key];
        }

        return null;
    }

    /**
     * Set configuration parameters
     *
     * Values are stored by $key in {@link $_config}.
     * 
     * @param string $key 
     * @param mixed $value 
     * @return void
     */
    public function __set($key, $value)
    {
        $this->_config[$key] = $value;
    }
}
