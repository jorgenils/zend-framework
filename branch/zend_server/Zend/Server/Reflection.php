<?php
/**
 * Zend_Server_Reflection_Exception
 */
require_once 'Zend/Server/Reflection/Exception.php';

/**
 * Zend_Server_Reflection_Method
 */
require_once 'Zend/Server/Reflection/Method.php';

/**
 * Reflection for determining method signatures to use with server classes
 * 
 * @package Zend_Server
 * @subpackage Reflection
 * @version $Id$
 */
class Zend_Server_Reflection
{
    /**
     * Perform class reflection to create dispatch signatures
     *
     * Creates dispatch prototypes for the public methods of a class or object. 
     * Each is keyed using the method name, prefixed with the namespace (if 
     * provided), and is a {@link Zend_Server_Reflection_Method} object.
     *
     * If extra arguments should be passed to the dispatchable method, these may 
     * be provided as an array to $argv.
     * 
     * @param string|object $class Class name or object
     * @param null|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the 
     * method name (used for the signature key). Primarily to avoid collisions, 
     * also for XmlRpc namespacing
     * @return array Array of Zend_Server_Reflection_Methods
     * @throws Zend_Server_Reflection_Exception
     */
    public static function reflectClass($class, $argv = false, $namespace = '')
    {
        if (is_object($class)) {
            $reflection = new ReflectionObject($class);
        } elseif (class_exists($class)) {
            $reflection = new ReflectionClass($class);
        } else {
            throw new Zend_Server_Reflection_Exception('Invalid class or object passed to attachClass()');
        }

        if ($argv && !is_array($argv)) {
            throw new Zend_Server_Reflection_Exception('Invalid argv argument passed to reflectClass');
        }

        $dispatchers = array();
        $dispatch    = array();
        $className   = $reflection->getName();
        foreach ($reflection->getMethods() as $method) {
            // Don't aggregate magic methods
            if (0 === strpos($method->getName(), '__')) {
                continue;
            }

            if ($method->isPublic()) {
                $methodName = $method->getName();

                // Skip magic methods
                if ('__' == substr($methodName, 0, 2)) {
                    continue;
                }

                // Get signatures and description
                $dispatchMethod = empty($namespace) ? $methodName : $namespace . '.' . $methodName;
                $dispatchers[$dispatchMethod] =  new Zend_Server_Reflection_Method($method, $dispatchMethod, $argv);
            }
        }

        return $dispatchers;
    }

    /**
     * Perform function reflection to create dispatch signatures
     *
     * Creates dispatch prototypes for a function. It is returned as an array 
     * with a key using the method name, prefixed with the namespace (if 
     * provided), and is a {@link Zend_Server_Reflection_Method} object.
     *
     * If extra arguments should be passed to the dispatchable function, these 
     * may be provided as an array to $argv.
     * 
     * @param string $function Function name
     * @param null|array $argv Optional arguments to be used during the method call
     * @param string $namespace Optional namespace with which to prefix the 
     * function name (used for the signature key). Primarily to avoid 
     * collisions, also for XmlRpc namespacing
     * @return array Array with a single Zend_Server_Reflection_Method
     * @throws Zend_Server_Reflection_Exception
     */
    public static function reflectFunction($function, $argv = false, $namespace = '')
    {
        if (!is_string($function) || !function_exists($function)) {
            throw new Zend_Server_Reflection_Exception('Invalid function "' . $function . '" passed to reflectFunction');
        }


        if ($argv && !is_array($argv)) {
            throw new Zend_Server_Reflection_Exception('Invalid argv argument passed to reflectClass');
        }

        $dispatchMethod = empty($namespace) ? $function : $namespace . '.' . $function;
        $dispatch = new Zend_Server_Reflection_Method(new ReflectionFunction($function), $dispatchMethod, $argv);

        return array($dispatchMethod => $dispatch);
    }
}
