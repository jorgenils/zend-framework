<?php
/**
 * Zend_Server_Reflection_Exception
 */
require_once 'Zend/Server/Reflection/Exception.php';

/**
 * Zend_Server_Reflection_Parameter
 */
require_once 'Zend/Server/Reflection/Parameter.php';

/**
 * Method/Function prototypes
 *
 * Contains accessors for the return value and all method arguments.
 * 
 * @package Zend_Server
 * @subpackage Reflection
 * @version $Id$
 */
class Zend_Server_Reflection_Prototype
{
    /**
     * Constructor
     * 
     * @param string $return 
     * @param array $params 
     * @return void
     */
    public function __construct($return = 'void', $params = null)
    {
        if (!is_string($return) && (null !== $return)) {
            throw new Zend_Server_Reflection_Exception('Invalid return type');
        }
        $this->_return = $return;

        if (!is_array($params) && (null !== $params)) {
            throw new Zend_Server_Reflection_Exception('Invalid parameters');
        }

        foreach ($params as $param) {
            if (!$param instanceof Zend_Server_Reflection_Parameter) {
                throw new Zend_Server_Reflection_Exception('One or more params are invalid');
            }
        }

        $this->_params = $params;
    }

    /**
     * Retrieve return type
     * 
     * @return string
     */
    public function getReturnType()
    {
        return $this->_return;
    }

    /**
     * Retrieve method parameters
     * 
     * @return array Array of {@link Zend_Server_Reflection_Parameter}s
     */
    public function getParameters()
    {
        return $this->_params;
    }
}
