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

require_once 'Zend/Validate/Array.php';
require_once 'Zend/Validate/Abstract.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_ArrayTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorSetsValidator()
    {
        $mock = $this->getMock('Zend_Validate_Abstract');

        $val  = new Zend_Validate_Array($mock);

        $this->assertSame($mock, $val->validator);
    }

    public function testValidatesEachElement()
    {
        $mock = $this->getMock('Zend_Validate_Abstract');
        $mock->expects($this->exactly(4))
             ->method('isValid')
             ->will($this->returnValue(true));

        $val  = new Zend_Validate_Array($mock);

        $testData = array(
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
            'field4' => 'value4',
        );

        $result = $val->isValid($testData);

        $this->assertTrue($result);
    }

    public function testStopsOnFirstInvalidValue()
    {
        $mock = $this->getMock('Zend_Validate_Abstract');
        $mock->expects($this->exactly(2))
             ->method('isValid')
             ->will($this->onConsecutiveCalls(
                    $this->returnValue(true),
                    $this->returnValue(false),
                    $this->returnValue(true),
                    $this->returnValue(true)));

        $val  = new Zend_Validate_Array($mock);

        $testData = array(
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
            'field4' => 'value4',
        );

        $result = $val->isValid($testData);

        $this->assertFalse($result);
    }

    public function testPassingScalarIsInvalid()
    {
        $mock = $this->getMock('Zend_Validate_Abstract');
        $val  = new Zend_Validate_Array($mock);

        $result = $val->isValid('scalar value');

        $this->assertFalse($result);
        $this->assertEquals(array('arrayNot'=>'\'scalar value\' is not an array'), $val->getMessages());
        $this->assertEquals('scalar value', $val->value);
    }

    public function testProxiesToValidatorForErrors()
    {
        require_once 'Zend/Validate/StringLength.php';

        $val = new Zend_Validate_Array(new Zend_Validate_StringLength(3, 6));

        $testData = array(
            'field1' => '1234567',
        );

        $result = $val->isValid($testData);

        $this->assertFalse($result);
        $this->assertEquals(array('stringLengthTooLong'=>"'1234567' is greater than 6 characters long"), $val->getMessages());
        $this->assertEquals(array('min', 'max'), $val->getMessageVariables());
        $this->assertEquals('1234567', $val->value);
    }
}
