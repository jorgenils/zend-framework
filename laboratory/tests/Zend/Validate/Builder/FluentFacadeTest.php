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

require_once 'Zend/Validate/Abstract.php';
require_once 'Zend/Validate/Builder.php';
require_once 'Zend/Validate/Builder/FluentFacade.php';


/**
 * @category   Zend
 * @package    Zend_Validate_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Builder_FluentFacadeTest extends PHPUnit_Framework_TestCase
{
    public function testInitialStateIncludesBuilder()
    {
        $zvb = $this->getMock('Zend_Validate_Builder');
        $ff  = new Zend_Validate_Builder_FluentFacade($zvb);

        $this->assertEquals(array(), $ff->getOptions());
        $this->assertSame($zvb, $ff->getBuilder());
    }

    public function testFactoryAccessorWorks()
    {
        $zvb = $this->getMock('Zend_Validate_Builder');
        $ff  = new Zend_Validate_Builder_FluentFacade($zvb);

        $mockFactory = $this->getMock('Zend_Validate_Builder_ValidatorFactory');
        $ff->setFactory($mockFactory);

        $this->assertSame($mockFactory, $ff->getFactory());
    }

    public function testGetFactoryDefaultsToStockFactory()
    {
        $zvb = $this->getMock('Zend_Validate_Builder');
        $ff  = new Zend_Validate_Builder_FluentFacade($zvb);

        $vf = $ff->getFactory();
        $this->assertType('Zend_Validate_Builder_ValidatorFactory', $vf);
    }

    public function testFacadeProxiesValidateInterfaceToBuilder()
    {
        $testData = array('field'=>'data');

        $zvb = $this->getMock('Zend_Validate_Builder');
        $zvb->expects($this->once())
            ->method('isValid')
            ->with($this->equalTo($testData));
        $zvb->expects($this->once())
            ->method('getMessages');
        $zvb->expects($this->once())
            ->method('getErrors');

        $ff  = new Zend_Validate_Builder_FluentFacade($zvb);

        $ff->isValid($testData);
        $ff->getMessages();
        $ff->getErrors();
    }

    public function testMagicCallIsFactoryShortcut()
    {
        $zvb = $this->getMock('Zend_Validate_Builder');
        $ff  = new Zend_Validate_Builder_FluentFacade($zvb);

        $mockFactory = $this->getMock('Zend_Validate_Builder_ValidatorFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testVal'),
                           $this->equalTo(array('a', 'b', 'c')));

        $ff->setFactory($mockFactory);

        $ff->testVal('a', 'b', 'c');
    }

    public function testMagicGetValidatesGottenField()
    {
        $mockVal = $this->getMock('Zend_Validate_Abstract');

        $mockFactory = $this->getMock('Zend_Validate_Builder_ValidatorFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testVal'),
                           $this->equalTo(array('a', 'b', 'c')))
                    ->will($this->returnValue($mockVal));

        $zvb = $this->getMock('Zend_Validate_Builder');
        $zvb->expects($this->once())
            ->method('addFlags')
            ->with($this->isType('int'),
                   $this->equalTo(Zend_Validate_Builder::OPTIONAL));
        $zvb->expects($this->once())
            ->method('add')
            ->with($this->equalTo($mockVal),
                   $this->equalTo('valField'),
                   $this->isType('int'))
            ->will($this->returnValue(1));

        $ff = new Zend_Validate_Builder_FluentFacade($zvb);
        $ff->setFactory($mockFactory);

        $ff->valField->testVal('a', 'b', 'c')->optional();
    }

    public function testEachValidatesEachField()
    {
        $mockVal = $this->getMock('Zend_Validate_Abstract');

        $mockFactory = $this->getMock('Zend_Validate_Builder_ValidatorFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testVal'),
                           $this->equalTo(array('a', 'b', 'c')))
                    ->will($this->returnValue($mockVal));

        $zvb = $this->getMock('Zend_Validate_Builder');
        $zvb->expects($this->once())
            ->method('addFlags')
            ->with($this->isType('int'),
                   $this->equalTo(Zend_Validate_Builder::OPTIONAL));
        $zvb->expects($this->once())
            ->method('add')
            ->with($this->equalTo($mockVal),
                   $this->equalTo(array('valField1', 'valField2')),
                   $this->isType('int'))
            ->will($this->returnValue(1));

        $ff = new Zend_Validate_Builder_FluentFacade($zvb);
        $ff->setFactory($mockFactory);

        $ff->each('valField1', 'valField2')->testVal('a', 'b', 'c')->optional();
    }

    public function testGroupValidatesAndSetsPassGroupFlag()
    {
        $mockVal = $this->getMock('Zend_Validate_Abstract');

        $mockFactory = $this->getMock('Zend_Validate_Builder_ValidatorFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testVal'),
                           $this->equalTo(array('a', 'b', 'c')))
                    ->will($this->returnValue($mockVal));

        $zvb = $this->getMock('Zend_Validate_Builder');
        $zvb->expects($this->once())
            ->method('addFlags')
            ->with($this->isType('int'),
                   $this->equalTo(Zend_Validate_Builder::OPTIONAL));
        $zvb->expects($this->once())
            ->method('add')
            ->with($this->equalTo($mockVal),
                   $this->equalTo(array('valField1', 'valField2')),
                   $this->equalTo(Zend_Validate_Builder::PASS_GROUP))
            ->will($this->returnValue(1));

        $ff = new Zend_Validate_Builder_FluentFacade($zvb);
        $ff->setFactory($mockFactory);

        $ff->group('valField1', 'valField2')->testVal('a', 'b', 'c')->optional();
    }

    public function testGlobValidatesAndSetsPatternFlag()
    {
        $mockVal = $this->getMock('Zend_Validate_Abstract');

        $mockFactory = $this->getMock('Zend_Validate_Builder_ValidatorFactory');
        $mockFactory->expects($this->once())
                    ->method('create')
                    ->with($this->equalTo('testVal'),
                           $this->equalTo(array('a', 'b', 'c')))
                    ->will($this->returnValue($mockVal));

        $zvb = $this->getMock('Zend_Validate_Builder');
        $zvb->expects($this->once())
            ->method('addFlags')
            ->with($this->isType('int'),
                   $this->equalTo(Zend_Validate_Builder::OPTIONAL));
        $zvb->expects($this->once())
            ->method('add')
            ->with($this->equalTo($mockVal),
                   $this->equalTo('/.*/'),
                   $this->equalTo(Zend_Validate_Builder::PATTERN))
            ->will($this->returnValue(1));

        $ff = new Zend_Validate_Builder_FluentFacade($zvb);
        $ff->setFactory($mockFactory);

        $ff->glob('/.*/')->testVal('a', 'b', 'c')->optional();
    }
}
