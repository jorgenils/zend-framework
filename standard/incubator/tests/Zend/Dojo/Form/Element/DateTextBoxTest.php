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
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

// Call Zend_Dojo_Form_Element_DateTextBoxTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Dojo_Form_Element_DateTextBoxTest::main");
}

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

/** Zend_Dojo_Form_Element_DateTextBox */
require_once 'Zend/Dojo/Form/Element/DateTextBox.php';

/** Zend_View */
require_once 'Zend/View.php';

/** Zend_Registry */
require_once 'Zend/Registry.php';

/** Zend_Dojo_View_Helper_Dojo */
require_once 'Zend/Dojo/View/Helper/Dojo.php';

/**
 * Test class for Zend_Dojo_Form_Element_Dijit.
 *
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_Form_Element_DateTextBoxTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Zend_Dojo_Form_Element_DateTextBoxTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        Zend_Registry::_unsetInstance();
        Zend_Dojo_View_Helper_Dojo::setUseDeclarative();

        $this->view    = $this->getView();
        $this->element = $this->getElement();
        $this->element->setView($this->view);
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    public function tearDown()
    {
    }

    public function getView()
    {
        require_once 'Zend/View.php';
        $view = new Zend_View();
        $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        return $view;
    }

    public function getElement()
    {
        $element = new Zend_Dojo_Form_Element_DateTextBox(
            'foo',
            array(
                'value' => 'some text',
                'label' => 'DateTextBox',
                'class' => 'someclass',
                'style' => 'width: 100px;',
            )
        );
        return $element;
    }

    public function testAmPmAccessorsShouldProxyToDijitParams()
    {
        $this->assertFalse($this->element->getAmPm());
        $this->assertFalse(array_key_exists('am,pm', $this->element->dijitParams));
        $this->element->setAmPm(true);
        $this->assertTrue($this->element->getAmPm());
        $this->assertTrue(array_key_exists('am,pm', $this->element->dijitParams));
        $this->assertTrue($this->element->dijitParams['am,pm']);
    }

    public function testStrictAccessorsShouldProxyToDijitParams()
    {
        $this->assertFalse($this->element->getStrict());
        $this->assertFalse(array_key_exists('strict', $this->element->dijitParams));
        $this->element->setStrict(true);
        $this->assertTrue($this->element->getStrict());
        $this->assertTrue(array_key_exists('strict', $this->element->dijitParams));
        $this->assertTrue($this->element->dijitParams['strict']);
    }

    public function testLocaleAccessorsShouldProxyToDijitParams()
    {
        $this->assertNull($this->element->getLocale());
        $this->assertFalse(array_key_exists('locale', $this->element->dijitParams));
        $this->element->setLocale('en-US');
        $this->assertEquals('en-US', $this->element->getLocale());
        $this->assertEquals('en-US', $this->element->dijitParams['locale']);
    }

    public function testFormatLengthAccessorsShouldProxyToDijitParams()
    {
        $this->assertNull($this->element->getFormatLength());
        $this->assertFalse(array_key_exists('formatLength', $this->element->dijitParams));
        $this->element->setFormatLength('long');
        $this->assertEquals('long', $this->element->getFormatLength());
        $this->assertEquals('long', $this->element->dijitParams['formatLength']);
    }

    /**
     * @expectedException Zend_Form_Element_Exception
     */
    public function testFormatLengthMutatorShouldThrowExceptionWithInvalidFormatLength()
    {
        $this->element->setFormatLength('foobar');
    }

    public function testSelectorAccessorsShouldProxyToDijitParams()
    {
        $this->assertNull($this->element->getSelector());
        $this->assertFalse(array_key_exists('selector', $this->element->dijitParams));
        $this->element->setSelector('time');
        $this->assertEquals('time', $this->element->getSelector());
        $this->assertEquals('time', $this->element->dijitParams['selector']);
    }

    /**
     * @expectedException Zend_Form_Element_Exception
     */
    public function testSelectorMutatorShouldThrowExceptionWithInvalidSelector()
    {
        $this->element->setSelector('foobar');
    }

    public function testDatePatternAccessorsShouldProxyToDijitParams()
    {
        $this->assertNull($this->element->getDatePattern());
        $this->assertFalse(array_key_exists('datePattern', $this->element->dijitParams));
        $this->element->setDatePattern('EEE, MMM d, Y');
        $this->assertEquals('EEE, MMM d, Y', $this->element->getDatePattern());
        $this->assertEquals('EEE, MMM d, Y', $this->element->dijitParams['datePattern']);
    }

    public function testShouldRenderDateTextBoxDijit()
    {
        $html = $this->element->render();
        $this->assertContains('dojoType="dijit.form.DateTextBox"', $html);
    }
}

// Call Zend_Dojo_Form_Element_DateTextBoxTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_Dojo_Form_Element_DateTextBoxTest::main") {
    Zend_Dojo_Form_Element_DateTextBoxTest::main();
}
