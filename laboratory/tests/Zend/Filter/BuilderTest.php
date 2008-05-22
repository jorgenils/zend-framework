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

require_once 'Zend/Filter/Builder.php';
require_once 'Zend/Filter/Int.php';


/**
 * @category   Zend
 * @package    Zend_Filter
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_BuilderTest extends PHPUnit_Framework_TestCase
{
    protected $_testSet;


    public function setUp()
    {
        $this->_testSet = array(
            'test1' => 'A string',
            'test2' => 123,
            'test3' => 'some val',
            'test4' => 456,
            'test5' => 'more data',
            'test6' => 789,
        );
    }

    public function testInitialStateIsEmpty()
    {
        $zfb = new Zend_Filter_Builder;

        $this->assertTrue(empty($zfb->dataSet));
    }

    public function testAddReturnsFilterIndex()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');

        $zfb = new Zend_Filter_Builder;

        $i0 = $zfb->add($mockFilter1, 'test1');
        $i1 = $zfb->add($mockFilter1, 'test3');
        $i2 = $zfb->add($mockFilter1, 'test5');

        $this->assertEquals(0, $i0);
        $this->assertEquals(1, $i1);
        $this->assertEquals(2, $i2);
    }

    public function testGetFlagsGetsFlagsFromAdd()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');

        $zfb = new Zend_Filter_Builder;
        $i0  = $zfb->add($mockFilter1, 'test1');
        $i1  = $zfb->add($mockFilter1, 'test2', Zend_Filter_Builder::PATTERN);

        $this->assertEquals(0, $zfb->getFlags($i0));
        $this->assertEquals(Zend_Filter_Builder::PATTERN, $zfb->getFlags($i1));
    }

    public function testSetFlagsOverwritesFlags()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');

        $zfb = new Zend_Filter_Builder;
        $i0  = $zfb->add($mockFilter1, 'test1');

        $zfb->setFlags($i0, Zend_Filter_Builder::PATTERN);
        $this->assertEquals(Zend_Filter_Builder::PATTERN, $zfb->getFlags($i0));

        $zfb->setFlags($i0, 0);
        $this->assertEquals(0, $zfb->getFlags($i0));
    }

    /* Not relevant until there's more than one flag
    public function testAddFlagsMergesFlags()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');

        $zfb = new Zend_Filter_Builder;
        $i0  = $zfb->add($mockFilter1, 'test1');

        $zfb->addFlags($i0, Zend_Filter_Builder::PATTERN);
        $this->assertEquals(Zend_Filter_Builder::PATTERN, $zfb->getFlags($i0));

        $zfb->addFlags($i0, Zend_Filter_Builder::SOME_FLAG);
        $this->assertEquals(Zend_Filter_Builder::PATTERN |
                            Zend_Filter_Builder::SOME_FLAG, $zfb->getFlags($i0));
    }
    */

    public function testFiltersSpecificFields()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');
        $mockFilter1->expects($this->exactly(2))
                    ->method('filter');

        $zfb = new Zend_Filter_Builder;

        $zfb->add($mockFilter1, 'test1');
        $zfb->add($mockFilter1, 'test2');

        $actual = $zfb->filter($this->_testSet);

        $this->assertType('array', $actual);
        $this->assertEquals(6, count($actual));
    }

    public function testFiltersWithArrayOfFields()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');
        $mockFilter1->expects($this->exactly(2))
                    ->method('filter');

        $zfb = new Zend_Filter_Builder;

        $zfb->add($mockFilter1, array('test3','test4'));

        $actual = $zfb->filter($this->_testSet);

        $this->assertType('array', $actual);
        $this->assertEquals(6, count($actual));
    }

    public function testFiltersFieldsMatchingPattern()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');
        $mockFilter1->expects($this->exactly(2))
                    ->method('filter');

        $zfb = new Zend_Filter_Builder;

        $zfb->add($mockFilter1, '/test[5-6]/', Zend_Filter_Builder::PATTERN);

        $actual = $zfb->filter($this->_testSet);
        
        $this->assertType('array', $actual);
        $this->assertEquals(6, count($actual));
    }

    public function testUsesMultipleFilters()
    {
        $mockFilter1 = $this->getMock('Zend_Filter_Int');
        $mockFilter1->expects($this->exactly(3))
                    ->method('filter');

        $mockFilter2 = $this->getMock('Zend_Filter_Int');
        $mockFilter2->expects($this->exactly(3))
                    ->method('filter');

        $zfb = new Zend_Filter_Builder;

        $zfb->add($mockFilter1, array('test1', 'test3','test5'));
        $zfb->add($mockFilter2, array('test2', 'test4','test6'));

        $actual = $zfb->filter($this->_testSet);
        
        $this->assertType('array', $actual);
        $this->assertEquals(6, count($actual));
    }
}
