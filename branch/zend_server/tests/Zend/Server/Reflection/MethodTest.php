<?php
require_once 'Zend/Server/Reflection/Method.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/IncompleteTestError.php';

/**
 * Test case for Zend_Server_Reflection_Method
 *
 * @package ortus
 * @version $Id:$
 */
class Zend_Server_Reflection_MethodTest extends PHPUnit2_Framework_TestCase 
{
    /**
     * Zend_Server_Reflection_Method object
     * @var Zend_Server_Reflection_Method
     */
    protected $_obj;

    /**
     * Setup environment
     */
    public function setUp() 
    {
        // You may need to change this to:
        // $this->_obj = Zend_Server_Reflection_Method::getInstance();
        // $this->_obj = new Zend_Server_Reflection_Method();
    }

    /**
     * Teardown environment
     */
    public function tearDown() 
    {
        unset($this->_obj);
    }

    /**
     * __construct() test
     *
     * Call as method call 
     *
     * Expects:
     * - class: 
     * - r: 
     * - namespace: Optional; 
     * - argv: Optional; has default; 
     * 
     * Returns: void 
     */
    public function test__construct()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }

    /**
     * getDeclaringClass() test
     *
     * Call as method call 
     *
     * Returns: Zend_Server_Reflection_Class 
     */
    public function testGetDeclaringClass()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }

    /**
     * __wakeup() test
     *
     * Call as method call 
     *
     * Returns: void 
     */
    public function test__wakeup()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }


}
