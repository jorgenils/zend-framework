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

require_once 'Zend/Validate/AllOrNone.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_AllOrNoneTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorSetsOtherFields()
    {
        $fields = array(
            'field1' => 'test1',
            'field2' => 'test2',
            'field3' => 'test3',
        );

        $val = new Zend_Validate_AllOrNone($fields);

        $this->assertEquals($fields, $val->otherFields);
    }

    public function testHavingAllFieldsIsValid()
    {
        $fields = array(
            'field1' => 'test1',
            'field2' => 'test2',
            'field3' => 'test3',
        );

        $val = new Zend_Validate_AllOrNone($fields);

        $this->assertTrue ($val->isValid('any value'));
        $this->assertFalse($val->isValid(''));

        $errors = $val->getMessages();
        $this->assertEquals(array('allOrNoneSome'), array_keys($errors));
    }

    public function testHavingNoFieldsIsValid()
    {
        $fields = array(
            'field1' => '',
            'field2' => '',
            'field3' => '',
        );

        $val = new Zend_Validate_AllOrNone($fields);

        $this->assertTrue ($val->isValid(''));
        $this->assertFalse($val->isValid('any value'));

        $errors = $val->getMessages();
        $this->assertEquals(array('allOrNoneSome'), array_keys($errors));
    }

    public function testHavingSomeFieldsIsBad()
    {
        $fields = array(
            'field1' => 'test1',
            'field2' => '',
            'field3' => 'test3',
        );

        $val = new Zend_Validate_AllOrNone($fields);

        $this->assertFalse($val->isValid(''));
        $this->assertFalse($val->isValid('any value'));

        $errors = $val->getMessages();
        $this->assertEquals(array('allOrNoneSome'), array_keys($errors));
    }
}
