<?php
// Call Zend_Form_Element_ButtonTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "Zend_Form_Element_ButtonTest::main");
}

require_once dirname(__FILE__) . '/../../../TestHelper.php';

require_once 'Zend/Form/Element/Button.php';
require_once 'Zend/Translate.php';

/**
 * Test class for Zend_Form_Element_Button
 */
class Zend_Form_Element_ButtonTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return void
     */
    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("Zend_Form_Element_ButtonTest");
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
        $this->element = new Zend_Form_Element_Button('foo');
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
        $view->addHelperPath(dirname(__FILE__) . '/../../../../library/Zend/View/Helper/');
        return $view;
    }

    public function testButtonElementSubclassesSubmitElement()
    {
        $this->assertTrue($this->element instanceof Zend_Form_Element_Submit);
    }

    public function testButtonElementSubclassesXhtmlElement()
    {
        $this->assertTrue($this->element instanceof Zend_Form_Element_Xhtml);
    }

    public function testButtonElementInstanceOfBaseElement()
    {
        $this->assertTrue($this->element instanceof Zend_Form_Element);
    }

    public function testHelperAttributeSetToFormButtonByDefault()
    {
        $this->assertEquals('formButton', $this->element->getAttrib('helper'));
    }

    public function testButtonElementUsesButtonHelperInViewHelperDecoratorByDefault()
    {
        $decorator = $this->element->getDecorator('viewHelper');
        $this->assertTrue($decorator instanceof Zend_Form_Decorator_ViewHelper);
        $decorator->setElement($this->element);
        $helper = $decorator->getHelper();
        $this->assertEquals('formButton', $helper);
    }

    public function testGetValueReturnsTranslatedValueIfTranslatorIsRegistered()
    {
        $translations = include dirname(__FILE__) . '/../_files/locale/array.php';
        $translate = new Zend_Translate('array', $translations, 'en');
        $this->element->setTranslator($translate)
                      ->setValue('submit');
        $test = $this->element->getValue();
        $this->assertEquals($translations['submit'], $test);
    }

    public function testTranslatedValueIsRendered()
    {
        $this->testGetValueReturnsTranslatedValueIfTranslatorIsRegistered();
        $this->element->setView($this->getView());
        $decorator = $this->element->getDecorator('ViewHelper');
        $decorator->setElement($this->element);
        $html = $decorator->render('');
        $this->assertRegexp('/<(input|button)[^>]*?value="Submit Button"/', $html, $html);
    }
}

// Call Zend_Form_Element_ButtonTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "Zend_Form_Element_ButtonTest::main") {
    Zend_Form_Element_ButtonTest::main();
}
