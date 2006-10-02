<?php
/**
 * Zend_Server_Reflection_Exception
 */
require_once 'Zend/Server/Reflection/Exception.php';

/**
 * Parameter Reflection 
 *
 * Decorates a ReflectionParameter to allow setting the parameter type
 * 
 * @package Zend_Server
 * @subpackage Reflection
 * @version $Id$
 */
class Zend_Server_Reflection_Parameter
{
    /**
     * @var ReflectionParameter
     */
    protected $_reflection;

    /**
     * Parameter position
     * @var int 
     */
    protected $_position;

    /**
     * Parameter type
     * @var string 
     */
    protected $_type;

    /**
     * Constructor
     * 
     * @param ReflectionParameter $r 
     * @param string $type Parameter type
     */
    public function __construct(ReflectionParameter $r, $type = 'mixed')
    {
        $this->_reflection = $r;
        $this->setType($type);
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
     * Retrieve parameter type
     * 
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set parameter type
     * 
     * @param string|null $type
     * @return void
     */
    public function setType($type)
    {
        if (!is_string($type) && (null !== $type)) {
            throw new Zend_Server_Reflection_Exception('Invalid parameter type');
        }

        $this->_type = $type;
    }

    /**
     * Set parameter position
     * 
     * @param int $index 
     * @return void
     */
    public function setPosition($index)
    {
        $this->_position = (int) $index;
    }

    /**
     * Return parameter position
     * 
     * @return int
     */
    public function getPosition()
    {
        return $this->_position;
    }
}
