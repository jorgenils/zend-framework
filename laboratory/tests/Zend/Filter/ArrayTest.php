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
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Filter/Array.php';
require_once 'Zend/Filter/Int.php';


/**
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_ArrayTest extends PHPUnit_Framework_TestCase
{
    public function testRecursiveOptionDefaultsFalse()
    {
        $fi = new Zend_Filter_Int;
        $fa = new Zend_Filter_Array($fi);

        $this->assertFalse($fa->recursive);
    }

    public function testFiltersEachArrayElement()
    {
        $mock = $this->getMock('Zend_Filter_Int');
        $mock->expects($this->exactly(4))
             ->method('filter');

        $fa = new Zend_Filter_Array($mock);

        $testData = array(
            'field1' => '123',
            'field2' => 'abc123',
            'field3' => '0000321',
            'field4' => '0xABCD'
        );

        $actual = $fa->filter($testData);

        $this->assertType('array', $actual);
        $this->assertEquals(4, count($actual));
    }

    public function testFiltersNestedArraysWithRecursive()
    {
        $mock = $this->getMock('Zend_Filter_Int');
        $mock->expects($this->exactly(7))
             ->method('filter');

        $fa = new Zend_Filter_Array($mock, true);

        $this->assertTrue($fa->recursive);

        $testData = array(
            'field1' => '123',
            'field2' => array(
                'subfield1' => 'abc123',
                'subfield2' => array(
                    'subsubfield1' => 'notint',
                    'subsubfield2' => '1a2b3c',
                ),
                'subfield3' => '3.141592654',
            ),
            'field3' => '0000321',
            'field4' => '0xABCD'
        );

        $actual = $fa->filter($testData);

        $this->assertType('array', $actual);
        $this->assertEquals(4, count($actual));
        $this->assertType('array', $actual['field2']);
        $this->assertEquals(3, count($actual['field2']));
        $this->assertType('array', $actual['field2']['subfield2']);
        $this->assertEquals(2, count($actual['field2']['subfield2']));
    }
}
