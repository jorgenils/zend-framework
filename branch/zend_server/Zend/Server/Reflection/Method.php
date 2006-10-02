<?php
/**
 * Zend_Server_Reflection_Function_Abstract
 */
require_once 'Zend/Server/Reflection/Function/Abstract.php';

/**
 * Method Reflection 
 *
 * @uses Zend_Server_Reflection_Function_Abstract
 * @package Zend_Server
 * @subpackage Reflection
 * @version $Id$
 */
class Zend_Server_Reflection_Method extends Zend_Server_Reflection_Function_Abstract
{
    /**
     * Parent class name
     * @var string 
     */
    protected $_class;

    /**
     * Parent class reflection
     * @var Zend_Server_Reflection_Class 
     */
    protected $_classReflection;

    /**
     * Constructor
     * 
     * @param Zend_Server_Reflection_Class $class 
     * @param ReflectionMethod $r 
     * @param string $namespace 
     * @param array $argv 
     * @return void
     */
    public function __construct(Zend_Server_Reflection_Class $class, ReflectionMethod $r, $namespace = null, $argv = array())
    {
        $this->_classReflection = $class;
        $this->_reflection      = $r;

        // Determine namespace
        if (null !== $namespace) {
            $this->setNamespace($namespace);
        } elseif (null !== $class->getNamespace()) {
            $this->setNamespace($class->getNamespace());
        }

        // Determine arguments
        if (is_array($argv)) {
            $this->_argv = $argv;
        }

        // If method call, need to store some info on the class
        $this->_class = $class->getName();

        // Perform some introspection
        $this->_reflect();
    }

    /**
     * Return the reflection for the class that defines this method
     * 
     * @return Zend_Server_Reflection_Class
     */
    public function getDeclaringClass()
    {
        return $this->_classReflection;
    }

    /**
     * Wakeup from serialization
     *
     * Reflection needs explicit instantiation to work correctly. Re-instantiate 
     * reflection object on wakeup.
     * 
     * @return void
     */
    public function __wakeup()
    {
        $class = new ReflectionClass($this->_class);
        $this->_reflection = new ReflectionMethod($class->newInstance(), $this->getName());
    }

}
