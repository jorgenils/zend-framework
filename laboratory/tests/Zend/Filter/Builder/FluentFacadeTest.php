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

require_once 'Zend/Filter/Int.php';
require_once 'Zend/Filter/Builder.php';
require_once 'Zend/Filter/Builder/FluentFacade.php';


/**
 * @category   Zend
 * @package    Zend_Filter_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Builder_FluentFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorCreateNewEmptyObject()
    {
        $zfb = $this->getMock('Zend_Filter_Builder');
        $ff  = new Zend_Filter_Builder_FluentFacade($zfb);

        $this->assertEquals(array(), $ff->getOptions());
        $this->assertSame($zfb, $ff->getBuilder());
    }

    public function testGetFactoryReturnsCorrectFactory()
    {
        $zfb = $this->getMock('Zend_Filter_Builder');
        $ff  = new Zend_Filter_Builder_FluentFacade($zfb);

        $origFactory = $ff->getFactory();
        $this->assertType('Zend_Filter_Builder_FilterFactory', $origFactory);

        $mockFactory = $this->getMock('Zend_Filter_Builder_FilterFactory');
        $ff->setFactory($mockFactory);

        $this->assertSame($mockFactory, $ff->getFactory());
        $this->assertNotSame($mockFactory, $origFactory);
    }

    public function testFacadeUsesFilterInterface()
    {
        $testData = array('field'=>'data');

        $zfb = $this->getMock('Zend_Filter_Builder');
        $zfb->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($testData));

        $ff  = new Zend_Filter_Builder_FluentFacade($zfb);

        $ff->filter($testData);
    }

    public function testMagicCallProxiesToFactory()
    {
        $zfb = $this->getMock('Zend_Filter_Builder');
        $ff  = new Zend_Filter_Builder_FluentFacade($zfb);

        $mockFactory = $this->getMock('Zend_Filter_Builder_FilterFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testFilter'),
                           $this->equalTo(array('a', 'b', 'c')));

        $ff->setFactory($mockFactory);

        $ff->testFilter('a', 'b', 'c');
    }

    public function testMagicGetAddsFilterForOneField()
    {
        $mockFilter  = $this->getMock('Zend_Filter_Int');

        $mockFactory = $this->getMock('Zend_Filter_Builder_FilterFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testFilter'),
                           $this->equalTo(array('a', 'b', 'c')))
                    ->will($this->returnValue($mockFilter));

        $zfb = $this->getMock('Zend_Filter_Builder');
        $zfb->expects($this->once())
            ->method('add')
            ->with($this->equalTo($mockFilter),
                   $this->equalTo('filterField'));

        $ff = new Zend_Filter_Builder_FluentFacade($zfb);
        $ff->setFactory($mockFactory);

        $ff->filterField->testFilter('a', 'b', 'c');
    }

    public function testEachAddsArrayOfFields()
    {
        $mockFilter  = $this->getMock('Zend_Filter_Int');

        $mockFactory = $this->getMock('Zend_Filter_Builder_FilterFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testFilter'),
                           $this->equalTo(array('a', 'b', 'c')))
                    ->will($this->returnValue($mockFilter));

        $zfb = $this->getMock('Zend_Filter_Builder');
        $zfb->expects($this->once())
            ->method('add')
            ->with($this->equalTo($mockFilter),
                   $this->equalTo(array('filterField1', 'filterField2')));

        $ff = new Zend_Filter_Builder_FluentFacade($zfb);
        $ff->setFactory($mockFactory);

        $ff->each('filterField1', 'filterField2')->testFilter('a', 'b', 'c');
    }

    public function testGlobAddsFieldPattern()
    {
        $mockFilter  = $this->getMock('Zend_Filter_Int');

        $mockFactory = $this->getMock('Zend_Filter_Builder_FilterFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testFilter'),
                           $this->equalTo(array('a', 'b', 'c')))
                    ->will($this->returnValue($mockFilter));

        $zfb = $this->getMock('Zend_Filter_Builder');
        $zfb->expects($this->once())
            ->method('add')
            ->with($this->equalTo($mockFilter),
                   $this->equalTo('/./'),
                   $this->equalTo(Zend_Filter_Builder::PATTERN));

        $ff = new Zend_Filter_Builder_FluentFacade($zfb);
        $ff->setFactory($mockFactory);

        $ff->glob('/./')->testFilter('a', 'b', 'c');
    }
}
