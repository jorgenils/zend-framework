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
 * @package    UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Filter/Builder/FilterFactory.php';


/**
 * @category   Zend
 * @package    Zend_Filter_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Builder_FilterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultNamespaceHasZend()
    {
        $ff = new Zend_Filter_Builder_FilterFactory;

        $expected = array('namespaces'=>array('Zend_Filter_'));
        $actual   = $ff->getOptions();
        
        $this->assertEquals($expected, $actual);
    }

    public function testSetOptionsWorks()
    {
        $ff = new Zend_Filter_Builder_FilterFactory;

        $ff->setOptions(array('namespaces'=>array('Test1', 'Test2')));

        $expected = array('namespaces'=>array('Test1', 'Test2', 'Zend_Filter_'));
        $actual   = $ff->getOptions();

        $this->assertEquals($expected, $actual);
    }

    public function testNamespaceLoadThrowsOnBadClass()
    {
        $filesDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files';
        $ff       = new Zend_Filter_Builder_FilterFactory;

        try {
            $ff->namespaceLoad('nonExistant');
            $this->fail('Failed to throw exception trying to load a non-existant class');
        } catch (Zend_Exception $e) {
            // Success
        }
    }

    public function testNamespaceLoadFindsClassInIncludePath()
    {
        $filesDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files';
        $ff       = new Zend_Filter_Builder_FilterFactory;

        // Find a class already in the include_path
        $className = $ff->namespaceLoad('stringTrim', array('Zend_Filter_'));

        $this->assertEquals('Zend_Filter_StringTrim', $className);
        $this->assertTrue(class_exists($className, false));
    }

    public function testNamespaceUnderscoreIsOptional()
    {
        $filesDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files';
        $ff       = new Zend_Filter_Builder_FilterFactory;

        // Find a class already in the include_path
        $className = $ff->namespaceLoad('stringTrim', array('Zend_Filter'));

        $this->assertEquals('Zend_Filter_StringTrim', $className);
        $this->assertTrue(class_exists($className, false));
    }

    public function testNamespaceLoadFindsClassInNonDefaultNamespaceNotInIncludePath()
    {
        $filesDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files';
        $ff       = new Zend_Filter_Builder_FilterFactory;

        // Find a class in another namespace, but not in the include_path
        $className = $ff->namespaceLoad('testClass1', array('Test'), $filesDir);

        $this->assertEquals('Test_TestClass1', $className);
        $this->assertTrue(class_exists($className, false));
    }

    public function testNamespaceLoadFindsClassInNonDefaultNamespaceInIncludePath()
    {
        $filesDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files';
        $ff       = new Zend_Filter_Builder_FilterFactory;

        // Find a class in another namespace, and in the include_path
        $old = get_include_path();
        set_include_path($old . PATH_SEPARATOR . $filesDir);

        $className = $ff->namespaceLoad('testClass2', array('Test'));

        $this->assertEquals('Test_TestClass2', $className);
        $this->assertTrue(class_exists($className, false));

        set_include_path($old);
    }

    public function testPassesArgsToFilterCtor()
    {
        $ff = new Zend_Filter_Builder_FilterFactory;
        
        $filter = $ff->create('stripTags', array('a', 'href', true));

        $this->assertType('Zend_Filter_StripTags', $filter);
        $this->assertEquals(array('a'=>array()),  $filter->getTagsAllowed());
        $this->assertEquals(array('href'=>null),  $filter->getAttributesAllowed());
        $this->assertTrue($filter->commentsAllowed);
    }
}
