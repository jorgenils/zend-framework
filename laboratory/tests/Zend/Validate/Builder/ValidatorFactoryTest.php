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
 * @package    Zend_Validate_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Validate/Builder/ValidatorFactory.php';


/**
 * @category   Zend
 * @package    Zend_Validate_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Builder_ValidatorFactoryTest extends PHPUnit_Framework_TestCase
{
    protected $filesDir;
    protected $vf;


    public function setUp()
    {
        $this->filesDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files';
        $this->vf       = new Zend_Validate_Builder_ValidatorFactory;
    }

    public function testInitialStateIncludesZendNamespace()
    {
        $expected = array('namespaces'=>array('Zend_Validate_'));
        $actual   = $this->vf->getOptions();
        
        $this->assertEquals($expected, $actual);
    }

    public function testNamespacesOptionAppends()
    {
        $this->vf->setOptions(array('namespaces'=>array('Test1', 'Test2')));

        $expected = array('namespaces'=>array('Test1', 'Test2', 'Zend_Validate_'));
        $actual   = $this->vf->getOptions();

        $this->assertEquals($expected, $actual);
    }

    public function testNamespaceLoadThrowsOnBadClass()
    {
        try {
            $this->vf->namespaceLoad('nonExistant');
            $this->fail('Failed to throw exception trying to load a non-existant class');
        } catch (Zend_Exception $e) {
            // Success
        }
    }

    public function testNamespaceLoadFindsClassInIncludePath()
    {
        // Find a class already in the include_path
        $className = $this->vf->namespaceLoad('stringLength', array('Zend_Validate_'));

        $this->assertEquals('Zend_Validate_StringLength', $className);
        $this->assertTrue(class_exists($className, false));
    }

    public function testNamespaceLoadFindsInOtherNamespceNotIncludePath()
    {
        // Find a class in another namespace, but not in the include_path
        $className = $this->vf->namespaceLoad('testClass1', array('Test'), $this->filesDir);

        $this->assertEquals('Test_TestClass1', $className);
        $this->assertTrue(class_exists($className, false));
    }

    public function testNamespaceLoadFindsInOtherNamespaceInIncludePath()
    {
        // Find a class in another namespace, and in the include_path
        $old = get_include_path();
        set_include_path($old . PATH_SEPARATOR . $this->filesDir);

        $className = $this->vf->namespaceLoad('testClass2', array('Test'));

        $this->assertEquals('Test_TestClass2', $className);
        $this->assertTrue(class_exists($className, false));

        set_include_path($old);
    }

    public function testCreatesFullyConstructedValidator()
    {
        $validator = $this->vf->create('between', array(5, 10, false));

        $this->assertType('Zend_Validate_Between', $validator);
        $this->assertEquals(5,  $validator->getMin());
        $this->assertEquals(10, $validator->getMax());
        $this->assertFalse($validator->getInclusive());
    }
}
