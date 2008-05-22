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

require_once 'Zend/Validate/Equals.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_EqualsTest extends PHPUnit_Framework_TestCase
{
    public function testComparesToConstructorValue()
    {
        $eq = new Zend_Validate_Equals('test');
        $this->assertTrue($eq->isValid('test'));
        $this->assertFalse($eq->isValid('foo'));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));

        unset($eq);
        $eq = new Zend_Validate_Equals(123);
        $this->assertTrue($eq->isValid(123));
        $this->assertFalse($eq->isValid(1));
        $this->assertFalse($eq->isValid('foo'));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));

        unset($eq);
        $eq = new Zend_Validate_Equals('');
        $this->assertTrue($eq->isValid(''));
        $this->assertTrue($eq->isValid(0));
        $this->assertTrue($eq->isValid(false));
        $this->assertTrue($eq->isValid(null));
        $this->assertFalse($eq->isValid(1));
        $this->assertFalse($eq->isValid('foo'));
    }

    public function testComparesToConstructorValueStrictly()
    {
        $eq = new Zend_Validate_Equals('test', true);
        $this->assertTrue($eq->isValid('test'));
        $this->assertFalse($eq->isValid('foo'));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));

        unset($eq);
        $eq = new Zend_Validate_Equals(0, true);
        $this->assertTrue($eq->isValid(0));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));
        $this->assertFalse($eq->isValid(false));
        $this->assertFalse($eq->isValid(array()));

        unset($eq);
        $eq = new Zend_Validate_Equals('', true);
        $this->assertTrue($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));
        $this->assertFalse($eq->isValid(false));
        $this->assertFalse($eq->isValid(0));
        $this->assertFalse($eq->isValid(array()));
    }

    public function testComparesToStandardProperty()
    {
        $eq = new Zend_Validate_Equals;
        $eq->standard = 'test';
        $this->assertTrue($eq->isValid('test'));
        $this->assertFalse($eq->isValid('foo'));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));

        unset($eq);
        $eq = new Zend_Validate_Equals;
        $eq->standard = 123;
        $this->assertTrue($eq->isValid(123));
        $this->assertFalse($eq->isValid(1));
        $this->assertFalse($eq->isValid('foo'));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));

        unset($eq);
        $eq = new Zend_Validate_Equals;
        $eq->standard = '';
        $this->assertTrue($eq->isValid(''));
        $this->assertTrue($eq->isValid(0));
        $this->assertTrue($eq->isValid(false));
        $this->assertTrue($eq->isValid(null));
        $this->assertFalse($eq->isValid(1));
        $this->assertFalse($eq->isValid('foo'));
    }

    public function testComparesToStandardPropertyStrictly()
    {
        $eq = new Zend_Validate_Equals;
        $eq->strict = true;
        $eq->standard = 'test';
        $this->assertTrue($eq->isValid('test'));
        $this->assertFalse($eq->isValid('foo'));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));

        unset($eq);
        $eq = new Zend_Validate_Equals;
        $eq->strict = true;
        $eq->standard = 0;
        $this->assertTrue($eq->isValid(0));
        $this->assertFalse($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));
        $this->assertFalse($eq->isValid(false));
        $this->assertFalse($eq->isValid(array()));

        unset($eq);
        $eq = new Zend_Validate_Equals;
        $eq->strict = true;
        $eq->standard = '';
        $this->assertTrue($eq->isValid(''));
        $this->assertFalse($eq->isValid(null));
        $this->assertFalse($eq->isValid(false));
        $this->assertFalse($eq->isValid(0));
        $this->assertFalse($eq->isValid(array()));
    }
}
