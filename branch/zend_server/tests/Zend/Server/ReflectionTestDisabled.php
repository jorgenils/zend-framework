<?php
require_once 'Zend/Server/Reflection.php';
require_once 'PHPUnit2/Framework/TestCase.php';
require_once 'PHPUnit2/Framework/IncompleteTestError.php';

/**
 * Test case for Zend_Server_Reflection
 *
 * @version $Id$
 */
class Zend_Server_ReflectionTest extends PHPUnit2_Framework_TestCase 
{
    /**
     * Zend_Server_Reflection object
     * @var Zend_Server_Reflection
     */
    protected $_reflect;

    /**
     * Setup environment
     */
    public function setUp() 
    {
        // $this->_reflect = new Zend_Server_Reflection();
    }

    /**
     * Teardown environment
     */
    public function tearDown() 
    {
        unset($this->_reflect);
    }

    /**
     * reflectClass() test
     */
    public function testReflectClass()
    {
        $reflection = $this->_reflect->reflectClass('Zend_Server_Reflection_testClass');

        $this->assertTrue(isset($reflection['one']));
        $this->assertTrue(isset($reflection['two']));
        $this->assertTrue(!isset($reflection['__construct']));
        $this->assertTrue(!isset($reflection['_one']));

        $this->assertContains('Public one', $reflection['one']['description'], $reflection['one']['description']);
        $this->assertContains('Public two', $reflection['two']['description'], $reflection['two']['description']);

        $oneExpectedSig = array(
            array(
                'string',
                'string'
            ),
            array(
                'string',
                'string',
                'array'
            ),
        );
        $this->assertEquals($oneExpectedSig, $reflection['one']['signatures']);

        $twoExpectedSig = array(
            array(
                'boolean',
                'string',
                'string'
            ),
            array(
                'array',
                'string',
                'string'
            ),
        );
        $this->assertEquals($twoExpectedSig, $reflection['two']['signatures']);

        $this->assertTrue(!isset($reflection['one']['function']));
        $this->assertTrue(isset($reflection['one']['class']));
        $this->assertEquals('Zend_Server_Reflection_testClass', $reflection['one']['class']);
        $this->assertTrue(isset($reflection['one']['method']));
        $this->assertEquals('one', $reflection['one']['method']);

        $this->assertTrue(!isset($reflection['two']['function']));
        $this->assertTrue(isset($reflection['two']['class']));
        $this->assertEquals('Zend_Server_Reflection_testClass', $reflection['two']['class']);
        $this->assertTrue(isset($reflection['two']['method']));
        $this->assertEquals('two', $reflection['two']['method']);
    }

    /**
     * reflectClass() test; test namespaces
     */
    public function testReflectClass2()
    {
        $reflection = $this->_reflect->reflectClass('Zend_Server_Reflection_testClass', false, 'zsr');

        $this->assertTrue(isset($reflection['zsr.one']));
        $this->assertTrue(isset($reflection['zsr.two']));
    }

    /**
     * reflectFunction() test
     */
    public function testReflectFunction()
    {
        $expected = array(
            array(
                'boolean',
                'boolean',
                'string'
            ),
            array(
                'boolean',
                'boolean',
                'string',
                'string'
            ),
            array(
                'boolean',
                'boolean',
                'string',
                'string',
                'string'
            ),
            array(
                'boolean',
                'boolean',
                'string',
                'string',
                'struct'
            ),
            array(
                'boolean',
                'boolean',
                'string',
                'string',
                'false'
            ),
            array(
                'boolean',
                'boolean',
                'array'
            ),
            array(
                'boolean',
                'boolean',
                'array',
                'string'
            ),
            array(
                'boolean',
                'boolean',
                'array',
                'string',
                'string'
            ),
            array(
                'boolean',
                'boolean',
                'array',
                'string',
                'struct'
            ),
            array(
                'boolean',
                'boolean',
                'array',
                'string',
                'false'
            ),

            array(
                'array',
                'boolean',
                'string'
            ),
            array(
                'array',
                'boolean',
                'string',
                'string'
            ),
            array(
                'array',
                'boolean',
                'string',
                'string',
                'string'
            ),
            array(
                'array',
                'boolean',
                'string',
                'string',
                'struct'
            ),
            array(
                'array',
                'boolean',
                'string',
                'string',
                'false'
            ),
            array(
                'array',
                'boolean',
                'array'
            ),
            array(
                'array',
                'boolean',
                'array',
                'string'
            ),
            array(
                'array',
                'boolean',
                'array',
                'string',
                'string'
            ),
            array(
                'array',
                'boolean',
                'array',
                'string',
                'struct'
            ),
            array(
                'array',
                'boolean',
                'array',
                'string',
                'false'
            )
        );

        $generated = $this->_reflect->reflectFunction('Zend_Server_Reflection_testFunction');
        $this->assertTrue(isset($generated['Zend_Server_Reflection_testFunction']));
        $this->assertTrue(isset($generated['Zend_Server_Reflection_testFunction']['signatures']));
        $this->assertTrue(isset($generated['Zend_Server_Reflection_testFunction']['description']));
        $this->assertTrue(isset($generated['Zend_Server_Reflection_testFunction']['function']));
        $this->assertTrue(!isset($generated['Zend_Server_Reflection_testFunction']['class']));
        $this->assertTrue(!isset($generated['Zend_Server_Reflection_testFunction']['method']));
        $this->assertContains('Used to test reflectFunction', $generated['Zend_Server_Reflection_testFunction']['description']);
        $this->assertEquals('Zend_Server_Reflection_testFunction', $generated['Zend_Server_Reflection_testFunction']['function']);

        $signatures = $generated['Zend_Server_Reflection_testFunction']['signatures'];
        $this->assertEquals($expected, $signatures, var_export($generated['Zend_Server_Reflection_testFunction']['signatures'], 1));
    }

    /**
     * reflectFunction() test; test namespaces
     */
    public function testReflectFunction2()
    {
        $generated = $this->_reflect->reflectFunction('Zend_Server_Reflection_testFunction', false, 'zsr');
        $this->assertTrue(isset($generated['zsr.Zend_Server_Reflection_testFunction']));
    }
}

/**
 * Zend_Server_Reflection_testFunction 
 *
 * Used to test reflectFunction generation of signatures
 * 
 * @param boolean $arg1 
 * @param string|array $arg2 
 * @param string $arg3 Optional argument
 * @param string|struct|false $arg4 Optional argument
 * @return boolean|array
 */
function Zend_Server_Reflection_testFunction($arg1, $arg2, $arg3 = 'string', $arg4 = 'array')
{
}

/**
 * Zend_Server_Reflection_testClass -- test class reflection
 */
class Zend_Server_Reflection_testClass
{
    /**
     * Constructor
     *
     * This shouldn't be reflected
     * 
     * @param mixed $arg 
     * @return void
     */
    public function __construct($arg = null)
    {
    }

    /**
     * Public one 
     * 
     * @param string $arg1 
     * @param array $arg2 
     * @return string
     */
    public function one($arg1, $arg2 = null)
    {
    }

    /**
     * Protected _one
     *
     * Should not be reflected
     * 
     * @param string $arg1 
     * @param array $arg2 
     * @return string
     */
    protected function _one($arg1, $arg2 = null)
    {
    }

    /**
     * Public two 
     * 
     * @param string $arg1 
     * @param string $arg2 
     * @return boolean|array
     */
    public static function two($arg1, $arg2)
    {
    }
}
