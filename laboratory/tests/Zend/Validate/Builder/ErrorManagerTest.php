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

require_once 'Zend/Validate/Builder/ErrorManager.php';
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Builder_ErrorManagerTest extends PHPUnit_Framework_TestCase
{
    public function testSetSingleMessageTemplate()
    {
        $em = new Zend_Validate_Builder_ErrorManager;

        $this->assertEquals(array(), $em->getTemplates());

        $em->setTemplate('field', 'valClass', 'reason', 'Some Message');
        
        $expected = array('field'=>array('valClass'=>array(
            'reason' => 'Some Message'
        )));

        $this->assertEquals($expected, $em->getTemplates());
    }

    public function testSetArrayOfTemplates()
    {
        $em = new Zend_Validate_Builder_ErrorManager;

        $this->assertEquals(array(), $em->getTemplates());

        $cfgArray = array('field1'=>array('valClass1'=>array('reason1'=>'Message1',
                                                             'reason2'=>'Message2'),
                                          'valClass2'=>array('reason1'=>'Message3',
                                                             'reason2'=>'Message4')),
                          'field2'=>array('valClass1'=>array('reason1'=>'Message5',
                                                             'reason2'=>'Message6'),
                                          'valClass2'=>array('reason1'=>'Message7',
                                                             'reason2'=>'Message8')));
        $em->setTemplates($cfgArray);

        $actual = $em->getTemplates();

        $this->assertEquals($cfgArray, $actual);
    }

    public function testGetTemplatesReturnsSelectedTempaltes()
    {
        $em = new Zend_Validate_Builder_ErrorManager;

        $em->setTemplate('field1', 'valClass1', 'reason1', 'Message1');
        $em->setTemplate('field1', 'valClass1', 'reason2', 'Message2');
        $em->setTemplate('field1', 'valClass2', 'reason1', 'Message3');
        $em->setTemplate('field1', 'valClass2', 'reason2', 'Message4');
        $em->setTemplate('field2', 'valClass1', 'reason1', 'Message5');
        $em->setTemplate('field2', 'valClass1', 'reason2', 'Message6');
        $em->setTemplate('field2', 'valClass2', 'reason1', 'Message7');
        $em->setTemplate('field2', 'valClass2', 'reason2', 'Message8');

        $expected = 'Message1';
        $this->assertEquals($expected, $em->getTemplates('field1', 'valClass1', 'reason1'));

        $expected = array('reason1'=>'Message3',
                          'reason2'=>'Message4');
        $this->assertEquals($expected, $em->getTemplates('field1', 'valClass2'));

        $expected = array('valClass1'=>array('reason1'=>'Message5',
                                             'reason2'=>'Message6'),
                          'valClass2'=>array('reason1'=>'Message7',
                                             'reason2'=>'Message8'));
        $this->assertEquals($expected, $em->getTemplates('field2'));

        $expected = array('field1'=>array('valClass1'=>array('reason1'=>'Message1',
                                                             'reason2'=>'Message2'),
                                          'valClass2'=>array('reason1'=>'Message3',
                                                             'reason2'=>'Message4')),
                          'field2'=>array('valClass1'=>array('reason1'=>'Message5',
                                                             'reason2'=>'Message6'),
                                          'valClass2'=>array('reason1'=>'Message7',
                                                             'reason2'=>'Message8')));
        $this->assertEquals($expected, $em->getTemplates());
    }

    public function testRaisesIntrinsicErrors()
    {
        $mockVal = $this->getMock('Zend_Validate_Abstract', array(), array(), 'MockVal');
        $mockVal->expects($this->once())
                ->method('getMessages')
                ->will($this->returnValue(array('mockReason' => 'Mock Error')));

        $em = new Zend_Validate_Builder_ErrorManager;
        $em->raise('testField', $mockVal);

        $expected = array('testField'=>array('MockVal'=>array('mockReason'=>'Mock Error')));
        $this->assertEquals($expected, $em->getMessages());
    }

    public function testTemplateVariablesGetSubstituted()
    {
        $em = new Zend_Validate_Builder_ErrorManager;
        $em->setTemplate('testField', 'MockVal1', 'reason', '%value% exceeds %foo%');

        $mockVal = $this->getMock('Zend_Validate_Abstract', array(), array(), 'MockVal1');
        $mockVal->expects($this->once())
                ->method('getMessages')
                ->will($this->returnValue(array('reason' => 'Default Default Msg')));
        $mockVal->expects($this->once())
                ->method('getMessageVariables')
                ->will($this->returnValue(array('foo')));

        // Properties the validator would have set in its isValid()
        $mockVal->value = 'bar';
        $mockVal->foo   = 'baz';

        $em->raise('testField', $mockVal);
        $actual = $em->getMessages();

        $expected = array('testField'=>array('MockVal1'=>array('reason'=>'bar exceeds baz')));
        $this->assertEquals($expected, $actual);
    }

    public function testHasMessagesFiltersErrors()
    {
        // Set up
        $em = new Zend_Validate_Builder_ErrorManager;

        $em->setTemplate('field1', 'valClass1', 'reason1', 'Message1');
        $em->setTemplate('field1', 'valClass1', 'reason2', 'Message2');
        $em->setTemplate('field1', 'valClass2', 'reason1', 'Message3');
        $em->setTemplate('field1', 'valClass2', 'reason2', 'Message4');
        $em->setTemplate('field2', 'valClass1', 'reason1', 'Message5');
        $em->setTemplate('field2', 'valClass1', 'reason2', 'Message6');
        $em->setTemplate('field2', 'valClass2', 'reason1', 'Message7');
        $em->setTemplate('field2', 'valClass2', 'reason2', 'Message8');
        $em->setTemplate('field3', 'valClass3', 'reason1', 'Message9');
        $em->setTemplate('field3', 'valClass3', 'reason2', 'Message10');
        $em->setTemplate('field3', 'valClass4', 'reason1', 'Message11');
        $em->setTemplate('field3', 'valClass4', 'reason2', 'Message12');

        $valClass1 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass1');
        $valClass1->expects($this->exactly(2))
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg')));

        $valClass2 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass2');
        $valClass2->expects($this->exactly(2))
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason2'=>'Default Msg')));

        $valClass3 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass3');
        $valClass3->expects($this->once())
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg', 'reason2'=>'Default Msg')));

        $valClass4 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass4');
        $valClass4->expects($this->once())
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg', 'reason2'=>'Default Msg')));

        $em->raise('field1', $valClass1);
        $em->raise('field1', $valClass2);
        $em->raise('field2', $valClass1);
        $em->raise('field2', $valClass2);
        $em->raise('field3', $valClass3);
        $em->raise('field3', $valClass4);


        // Now do actual tests
        $this->assertTrue($em->hasMessages('field1', 'valClass1', 'reason1'));
        $this->assertTrue($em->hasMessages('field1', 'valClass1'));
        $this->assertTrue($em->hasMessages('field1'));
        $this->assertTrue($em->hasMessages());

        $this->assertFalse($em->hasMessages('field1', 'valClass1', 'reason2'));
        $this->assertFalse($em->hasMessages('field2', 'valClass2', 'reason3'));
        $this->assertFalse($em->hasMessages('field2', 'valClass3'));
        $this->assertFalse($em->hasMessages('field4'));
    }

    public function testGetMessagesFiltersErrors()
    {
        // Set up
        $em = new Zend_Validate_Builder_ErrorManager;

        // I swear a previous version of PHPUnit (<3.2?) used to somehow clear 
        // out mock object class names so you re-use them between tests w/o 
        // errors...
        $em->setTemplate('field1', 'valClass5', 'reason1', 'Message1');
        $em->setTemplate('field1', 'valClass5', 'reason2', 'Message2');
        $em->setTemplate('field1', 'valClass6', 'reason1', 'Message3');
        $em->setTemplate('field1', 'valClass6', 'reason2', 'Message4');
        $em->setTemplate('field2', 'valClass5', 'reason1', 'Message5');
        $em->setTemplate('field2', 'valClass5', 'reason2', 'Message6');
        $em->setTemplate('field2', 'valClass6', 'reason1', 'Message7');
        $em->setTemplate('field2', 'valClass6', 'reason2', 'Message8');
        $em->setTemplate('field3', 'valClass7', 'reason1', 'Message9');
        $em->setTemplate('field3', 'valClass7', 'reason2', 'Message10');
        $em->setTemplate('field3', 'valClass8', 'reason1', 'Message11');
        $em->setTemplate('field3', 'valClass8', 'reason2', 'Message12');

        $valClass1 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass5');
        $valClass1->expects($this->exactly(2))
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg')));

        $valClass2 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass6');
        $valClass2->expects($this->exactly(2))
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason2'=>'Default Msg')));

        $valClass3 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass7');
        $valClass3->expects($this->once())
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg', 'reason2'=>'Default Msg')));

        $valClass4 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass8');
        $valClass4->expects($this->once())
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg', 'reason2'=>'Default Msg')));

        $em->raise('field1', $valClass1);
        $em->raise('field1', $valClass2);
        $em->raise('field2', $valClass1);
        $em->raise('field2', $valClass2);
        $em->raise('field3', $valClass3);
        $em->raise('field3', $valClass4);


        // Now do actual tests
        $expected = 'Message1';
        $this->assertEquals($expected, $em->getMessages('field1', 'valClass5', 'reason1'));

        $expected = array('reason1'=>'Message1');
        $this->assertEquals($expected, $em->getMessages('field1', 'valClass5'));

        $expected = array('valClass5'=>array('reason1'=>'Message1'),
                          'valClass6'=>array('reason2'=>'Message4'));
        $this->assertEquals($expected, $em->getMessages('field1'));

        $expected = array('field1'=>array('valClass5'=>array('reason1'=>'Message1'),
                                          'valClass6'=>array('reason2'=>'Message4')),
                          'field2'=>array('valClass5'=>array('reason1'=>'Message5'),
                                          'valClass6'=>array('reason2'=>'Message8')),
                          'field3'=>array('valClass7'=>array('reason1'=>'Message9',
                                                             'reason2'=>'Message10'),
                                          'valClass8'=>array('reason1'=>'Message11',
                                                             'reason2'=>'Message12')));
        $this->assertEquals($expected, $em->getMessages());

        $expected = array();
        $this->assertEquals($expected, $em->getMessages('field1', 'valClass5', 'reason2'));
        $this->assertEquals($expected, $em->getMessages('field2', 'valClass6', 'reason3'));
        $this->assertEquals($expected, $em->getMessages('field2', 'valClass7'));
        $this->assertEquals($expected, $em->getMessages('field4'));
    }

    public function testClearFiltersErrors()
    {
        // Set up
        $em = new Zend_Validate_Builder_ErrorManager;

        $em->setTemplate('field1', 'valClass9',  'reason1', 'Message1');
        $em->setTemplate('field1', 'valClass9',  'reason2', 'Message2');
        $em->setTemplate('field1', 'valClass10', 'reason1', 'Message3');
        $em->setTemplate('field1', 'valClass10', 'reason2', 'Message4');
        $em->setTemplate('field2', 'valClass9',  'reason1', 'Message5');
        $em->setTemplate('field2', 'valClass9',  'reason2', 'Message6');
        $em->setTemplate('field2', 'valClass10', 'reason1', 'Message7');
        $em->setTemplate('field2', 'valClass10', 'reason2', 'Message8');
        $em->setTemplate('field3', 'valClass11', 'reason1', 'Message9');
        $em->setTemplate('field3', 'valClass11', 'reason2', 'Message10');
        $em->setTemplate('field3', 'valClass12', 'reason1', 'Message11');
        $em->setTemplate('field3', 'valClass12', 'reason2', 'Message12');

        $valClass1 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass9');
        $valClass1->expects($this->exactly(2))
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg')));

        $valClass2 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass10');
        $valClass2->expects($this->exactly(2))
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason2'=>'Default Msg')));

        $valClass3 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass11');
        $valClass3->expects($this->once())
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg', 'reason2'=>'Default Msg')));

        $valClass4 = $this->getMock('Zend_Validate_Abstract', array(), array(), 'valClass12');
        $valClass4->expects($this->once())
                  ->method('getMessages')
                  ->will($this->returnValue(array('reason1'=>'Default Msg', 'reason2'=>'Default Msg')));

        $em->raise('field1', $valClass1);
        $em->raise('field1', $valClass2);
        $em->raise('field2', $valClass1);
        $em->raise('field2', $valClass2);
        $em->raise('field3', $valClass3);
        $em->raise('field3', $valClass4);


        // Now do actual tests
        $em->clear('field3', 'valClass11', 'reason1');
        $this->assertFalse($em->hasMessages('field3', 'valClass11', 'reason1'));
        $this->assertTrue ($em->hasMessages('field3', 'valClass11', 'reason2'));

        $em->clear('field3', 'valClass11');
        $this->assertFalse($em->hasMessages('field3', 'valClass11'));
        $this->assertTrue ($em->hasMessages('field3', 'valClass12'));

        $em->clear('field3');
        $this->assertFalse($em->hasMessages('field3'));
        $this->assertTrue ($em->hasMessages('field2'));

        $em->clear();
        $this->assertFalse($em->hasMessages());
    }
}
