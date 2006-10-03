<?php
require_once 'Zend/Server/Reflection/Class.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/IncompleteTestError.php';

/**
 * Test case for Zend_Server_Reflection_Class
 *
 * @package ortus
 * @version $Id:$
 */
class Zend_Server_Reflection_ClassTest extends PHPUnit2_Framework_TestCase 
{
    /**
     * Zend_Server_Reflection_Class object
     * @var Zend_Server_Reflection_Class
     */
    protected $_obj;

    /**
     * Setup environment
     */
    public function setUp() 
    {
        // You may need to change this to:
        // $this->_obj = Zend_Server_Reflection_Class::getInstance();
        // $this->_obj = new Zend_Server_Reflection_Class();
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
     * - reflection: 
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
     * __call() test
     *
     * Call as method call 
     *
     * Expects:
     * - method: 
     * - args: 
     * 
     * Returns: mixed 
     */
    public function test__call()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }

    /**
     * __get() test
     *
     * Call as method call 
     *
     * Expects:
     * - key: 
     * 
     * Returns: mixed 
     */
    public function test__get()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }

    /**
     * __set() test
     *
     * Call as method call 
     *
     * Expects:
     * - key: 
     * - value: 
     * 
     * Returns: void 
     */
    public function test__set()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }

    /**
     * getMethods() test
     *
     * Call as method call 
     *
     * Returns: array 
     */
    public function testGetMethods()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }

    /**
     * getNamespace() test
     *
     * Call as method call 
     *
     * Returns: string 
     */
    public function testGetNamespace()
    {
        // Remove this line once the test has been written
        throw new PHPUnit2_Framework_IncompleteTestError('not implemented');
    }

    /**
     * setNamespace() test
     *
     * Call as method call 
     *
     * Expects:
     * - namespace: 
     * 
     * Returns: void 
     */
    public function testSetNamespace()
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
