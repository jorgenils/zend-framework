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

require_once 'Zend/Validate/AtLeastN.php';
require_once 'Zend/Validate/NotEmpty.php';


/**
 * @category   Zend
 * @package    Zend_Validate
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_AtLeastNTest extends PHPUnit_Framework_TestCase
{
    public function testThresholdMustBeAtLeast1()
    {
        try {
            $val = new Zend_Validate_AtLeastN(0, new Zend_Validate_NotEmpty);
            $this->fail('Failed to catch invalid constructor parameter');
        } catch (Zend_Validate_Exception $e) {
            // Success
        }
    }

    public function testValidWithAtLeastN()
    {
        $val = new Zend_Validate_AtLeastN(3, new Zend_Validate_NotEmpty);

        $this->assertTrue($val->isValid(array(1,1,0,0,0,0,1)));
        $this->assertTrue($val->isValid(array(1,0,0,0,0,1,1)));
        $this->assertTrue($val->isValid(array(1,0,0,1,0,0,1)));
        $this->assertTrue($val->isValid(array(1,1,1,0,0,0,0)));
        $this->assertTrue($val->isValid(array(0,0,0,0,1,1,1)));
        $this->assertTrue($val->isValid(array(0,0,1,1,1,0,0)));
        $this->assertTrue($val->isValid(array(1,1,1,1,1,0,0)));
        $this->assertTrue($val->isValid(array(1,1,1,1,1,1,1)));
    }

    public function testInvalidWithFewerThanN()
    {
        $val = new Zend_Validate_AtLeastN(3, new Zend_Validate_NotEmpty);

        $this->assertFalse($val->isValid(array(0,0,0,0,0,0,0)));
        $this->assertFalse($val->isValid(array(0,0,1,0,1,0,0)));
        $this->assertFalse($val->isValid(array(1,1,0,0,0,0,0)));
        $this->assertFalse($val->isValid(array(0,0,0,0,0,1,1)));
        $this->assertFalse($val->isValid(array(0,0,0,1,0,0,0)));
        $this->assertFalse($val->isValid(array(1,0,0,0,0,0,1)));
    }
}
