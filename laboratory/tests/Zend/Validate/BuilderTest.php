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
 * @package    Zend_Validate
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Zend/Validate/Builder.php';
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_BuilderTest extends PHPUnit_Framework_TestCase
{
    protected $_testSet = array();


    public function setUp()
    {
        $this->_testSet = array(
            'test1' => 'A string',
            'test2' => 123,
            'test3' => 'some val',
            'test4' => 456,
            'test5' => 'more data',
            'test6' => 789,
            '_test' => '',
            'test_' => 'not empty',
        );
    }
        
    public function testInitialStateIsEmpty()
    {
        $zvb = new Zend_Validate_Builder;

        $this->assertTrue(empty($zvb->dataSet));
    }

    public function testReturnsDefaultErrorManager()
    {
        $zvb = new Zend_Validate_Builder;

        $em1 = $zvb->getErrorManager();
        $this->assertType('Zend_Validate_Builder_ErrorManager', $em1);
    }

    public function testSetErrorManagerOverridesDefault()
    {
        $zvb = new Zend_Validate_Builder;

        $em1 = $zvb->getErrorManager();
        $this->assertType('Zend_Validate_Builder_ErrorManager', $em1);

        $em2 = new Zend_Validate_Builder_ErrorManager;
        $this->assertNotSame($em1, $em2);

        $zvb->setErrorManager($em2);
        $em3 = $zvb->getErrorManager();
        $this->assertSame($em2, $em3);
    }

    public function testAddReturnsValidatorIndex()
    {
        $mockVal1 = $this->getMock('Zend_Validate_Abstract');

        $zvb = new Zend_Validate_Builder;

        $i0 = $zvb->add($mockVal1, 'test1');
        $i1 = $zvb->add($mockVal1, 'test3');
        $i2 = $zvb->add($mockVal1, 'test5');

        $this->assertEquals(0, $i0);
        $this->assertEquals(1, $i1);
        $this->assertEquals(2, $i2);
    }

    public function testGetFlagsGetsFlagsFromAdd()
    {
        $mockVal1 = $this->getMock('Zend_Validate_Abstract');

        $zvb = new Zend_Validate_Builder;
        $i0  = $zvb->add($mockVal1, 'test1');
        $i1  = $zvb->add($mockVal1, 'test2', Zend_Validate_Builder::OPTIONAL);

        $this->assertEquals(0, $zvb->getFlags($i0));
        $this->assertEquals(Zend_Validate_Builder::OPTIONAL, $zvb->getFlags($i1));
    }

    public function testSetFlagsOverwritesFlags()
    {
        $mockVal1 = $this->getMock('Zend_Validate_Abstract');

        $zvb = new Zend_Validate_Builder;
        $i0  = $zvb->add($mockVal1, 'test1');

        $zvb->setFlags($i0, Zend_Validate_Builder::OPTIONAL);
        $this->assertEquals(Zend_Validate_Builder::OPTIONAL, $zvb->getFlags($i0));

        $zvb->setFlags($i0, Zend_Validate_Builder::PATTERN);
        $this->assertEquals(Zend_Validate_Builder::PATTERN, $zvb->getFlags($i0));
    }

    public function testAddFlagsMergesFlags()
    {
        $mockVal1 = $this->getMock('Zend_Validate_Abstract');

        $zvb = new Zend_Validate_Builder;
        $i0  = $zvb->add($mockVal1, 'test1');

        $zvb->addFlags($i0, Zend_Validate_Builder::OPTIONAL);
        $this->assertEquals(Zend_Validate_Builder::OPTIONAL, $zvb->getFlags($i0));

        $zvb->addFlags($i0, Zend_Validate_Builder::PATTERN);
        $this->assertEquals(Zend_Validate_Builder::PATTERN |
                            Zend_Validate_Builder::OPTIONAL, $zvb->getFlags($i0));
    }

    public function testValidatesSpecificFields()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(2))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(2))
                 ->method('isValid');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, 'test1');
        $zvb->add($mockVal1, 'test2');

        $zvb->isValid($this->_testSet);
    }

    public function testValidatesWithArrayOfFields()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(2))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(2))
                 ->method('isValid');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, array('test1', 'test2'));

        $zvb->isValid($this->_testSet);
    }

    public function testValidatesFieldsMatchingPattern()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(6))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(6))
                 ->method('isValid');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, '/test[1-6]/', Zend_Validate_Builder::PATTERN);

        $zvb->isValid($this->_testSet);
    }

    public function testValidatesFieldGroup()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(1))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(1))
                 ->method('isValid')
                 ->with($this->equalTo(array(
                        'test1' => 'A string',
                        'test2' => 123,
                        'test3' => 'some val')));

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, array('test1', 'test2', 'test3'), Zend_Validate_Builder::PASS_GROUP);

        $zvb->isValid($this->_testSet);
    }

    public function testUsesMultipleValidators()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(6))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(2))
                 ->method('isValid');

        $mockVal2 = $this->getMock('Zend_Validate_Abstract');
        $mockVal2->expects($this->exactly(2))
                 ->method('isValid');

        $mockVal3 = $this->getMock('Zend_Validate_Abstract');
        $mockVal3->expects($this->exactly(2))
                 ->method('isValid');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, array('test1', 'test2'));
        $zvb->add($mockVal2, array('test3', 'test4'));
        $zvb->add($mockVal3, array('test5', 'test6'));

        $zvb->isValid($this->_testSet);
    }

    public function testValidationSkipsOptionals1()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(2))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(1))
                 ->method('isValid');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, '_test', Zend_Validate_Builder::OPTIONAL);
        $zvb->add($mockVal1, 'test_');

        $zvb->isValid($this->_testSet);
    }

    public function testValidationSkipsOptionals2()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(2))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(1))
                 ->method('isValid');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, array('_test', 'test_'), Zend_Validate_Builder::OPTIONAL);

        $zvb->isValid($this->_testSet);
    }

    public function testValidationSkipsOptionals3()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(2))
               ->method('clear');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(1))
                 ->method('isValid');

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $zvb->add($mockVal1, '/_test|test_/', Zend_Validate_Builder::OPTIONAL | Zend_Validate_Builder::PATTERN);

        $zvb->isValid($this->_testSet);
    }

    public function testValdationRaisesMessages()
    {
        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->exactly(7))
               ->method('clear');
        $mockEm->expects($this->exactly(4))
               ->method('raise');

        $mockVal1 = $this->getMock('Zend_Validate_Abstract');
        $mockVal1->expects($this->exactly(7))
                 ->method('isValid')
                 ->will($this->onConsecutiveCalls(
                        $this->returnValue(false),
                        $this->returnValue(true),
                        $this->returnValue(false),
                        $this->returnValue(true),
                        $this->returnValue(false),
                        $this->returnValue(true),
                        $this->returnValue(false)));

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);
        
        $zvb->add($mockVal1, 'test1');
        $zvb->add($mockVal1, 'test2');
        $zvb->add($mockVal1, array('test3', 'test4'));
        $zvb->add($mockVal1, '/test[56]/', Zend_Validate_Builder::PATTERN);
        $zvb->add($mockVal1, array('_test', 'test_'), Zend_Validate_Builder::PASS_GROUP);

        $valid = $zvb->isValid($this->_testSet);

        $this->assertFalse($valid);
    }

    public function testGetMessagesProxiesToErrorManager()
    {
        $expected = array('field'=>array('val'=>array('reason'=>'Message')));

        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->once())
               ->method('getMessages')
               ->will($this->returnValue($expected));

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $actual = $zvb->getMessages();

        $this->assertEquals($expected, $actual);
    }

    public function testGetErrorsReturnsOnlyErrorCodes()
    {
        $testMsgs = array('field'=>array('val'=>array('reason'=>'Message')));
        $expected = array('field'=>array('val'=>array('reason')));

        $mockEm = $this->getMock('Zend_Validate_Builder_ErrorManager');
        $mockEm->expects($this->once())
               ->method('getMessages')
               ->will($this->returnValue($testMsgs));

        $zvb = new Zend_Validate_Builder;
        $zvb->setErrorManager($mockEm);

        $actual = $zvb->getErrors();

        $this->assertEquals($expected, $actual);
    }
}
